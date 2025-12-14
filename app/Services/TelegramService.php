<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\ApiModel\ApiUser;
use App\ApiModel\TelegramStartToken;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class TelegramService
{
    /**
     * Send a message to a Telegram user via chat_id
     */
    public static function sendMessageToUser(ApiUser $user, string $text, array $files = [])
    {
        if (!$user->telegram_chat_id) return false;

        $token = env('TELEGRAM_BOT_TOKEN');

        // -------------------------------
        // 1) Send text message first
        // -------------------------------
        Http::withoutVerifying()->post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $user->telegram_chat_id,
            'text'    => $text,
            'parse_mode' => 'HTML'
        ]);

        if (empty($files)) return true;

        // -------------------------------
        // 2) Separate files into images and documents
        // -------------------------------
        $images = [];
        $docs   = [];

        foreach ($files as $file) {
            $isUploaded = $file instanceof \Illuminate\Http\UploadedFile;
            $path = $isUploaded ? $file->getRealPath() : $file['path'];
            $name = $isUploaded ? $file->getClientOriginalName() : $file['name'];

            $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $images[] = ['path' => $path, 'name' => $name];
            } else {
                $docs[] = ['path' => $path, 'name' => $name];
            }
        }

        // -------------------------------
        // 3) Send images as media group
        // -------------------------------
        if (!empty($images)) {
            $media = [];
            $multipart = [];

            foreach ($images as $index => $img) {
                $media[] = [
                    'type' => 'photo',
                    'media' => 'attach://' . $img['name']
                ];

                $multipart[] = [
                    'name' => $img['name'],
                    'contents' => fopen($img['path'], 'r'),
                    'filename' => $img['name']
                ];
            }

            $multipart[] = ['name' => 'chat_id', 'contents' => $user->telegram_chat_id];
            $multipart[] = ['name' => 'media', 'contents' => json_encode($media, JSON_UNESCAPED_UNICODE)];

            Http::withoutVerifying()->asMultipart()
                ->post("https://api.telegram.org/bot{$token}/sendMediaGroup", $multipart);
        }

        // -------------------------------
        // 4) Send documents one by one
        // -------------------------------
        foreach ($docs as $doc) {
            $multipart = [
                [
                    'name' => 'document',
                    'contents' => fopen($doc['path'], 'r'),
                    'filename' => $doc['name']
                ],
                [
                    'name' => 'chat_id',
                    'contents' => $user->telegram_chat_id
                ]
            ];

            Http::withoutVerifying()->asMultipart()
                ->post("https://api.telegram.org/bot{$token}/sendDocument", $multipart);
        }

        return true;
    }

    /**
     * Optional: generate /start link for first-time users
     */
    public static function generateStartLink(int $userId, int $orderId): string
    {
        $token = \Str::random(32);

        TelegramStartToken::create([
            'token' => $token,
            'api_user_id' => $userId,
            'order_online_id' => $orderId,
            'expires_at' => now()->addDay(),
        ]);

        return "https://t.me/sysproasiabot?start={$token}";
    }

    public static function saveChatIdIfNotExist(ApiUser $user, string $chatId)
    {
        if (!$user->telegram_chat_id || $user->telegram_chat_id == 0) {
            $user->telegram_chat_id = $chatId;
            \Log::info('Saved chat id for user', ['user_id' => $user->id, 'chat_id' => $user->telegram_chat_id]);
            $user->save();
        }
    }

    public static function sendRawMessage($chatId, $text)
    {
        $token = env('TELEGRAM_BOT_TOKEN');

        Http::withoutVerifying()->post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text
        ]);
    }


    public function handle(Request $request)
    {
        $update = $request->all();

        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];       // ← THIS IS THE CHAT ID
            $chatType = $update['message']['chat']['type'];  // group, supergroup, private
            $text = $update['message']['text'] ?? '';

            Log::info("Telegram message received", [
                'chat_id' => $chatId,
                'chat_type' => $chatType,
                'text' => $text
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public static function sendImagesToGroup(array $files, string $caption = '')
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $groupChatId = '-1003265141698';

        if (empty($files)) {
            Log::warning("TelegramService: No images to send");
            return false;
        }

        $media = [];
        $multipart = [];

        foreach ($files as $index => $img) {
            if ($img instanceof \Illuminate\Http\UploadedFile) {
                $path = $img->getRealPath();
                $name = $img->getClientOriginalName();
            } elseif (is_array($img) && isset($img['path'], $img['name'])) {
                $path = $img['path'];
                $name = $img['name'];
            } else {
                continue;
            }

            $photo = [
                'type' => 'photo',
                'media' => 'attach://' . $name
            ];

            // Only first image can have caption
            if ($index === 0 && !empty($caption)) {
                $photo['caption'] = $caption;
                $photo['parse_mode'] = 'Markdown';
            }

            $media[] = $photo;

            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path, 'r'),
                'filename' => $name
            ];
        }

        $multipart[] = [
            'name' => 'chat_id',
            'contents' => $groupChatId
        ];

        $multipart[] = [
            'name' => 'media',
            'contents' => json_encode($media, JSON_UNESCAPED_UNICODE)
        ];

        $res = Http::withoutVerifying()
            ->asMultipart()
            ->post("https://api.telegram.org/bot{$token}/sendMediaGroup", $multipart);

        Log::info("TelegramService sendImagesToGroup response", [
            'telegram_response' => $res->json()
        ]);

        return true;
    }

    public static function sendPhotoToSecondGroup(string $imagePath, string $caption = '')
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $groupChatId = '-5047451233'; // SECOND GROUP

        if (!file_exists($imagePath)) {
            Log::error('TelegramService: Image not found', ['path' => $imagePath]);
            return false;
        }

        $response = Http::withoutVerifying()
            ->attach(
                'photo',
                fopen($imagePath, 'r'),
                basename($imagePath) // Telegram NEEDS a filename
            )
            ->post("https://api.telegram.org/bot{$token}/sendPhoto", [
                'chat_id' => $groupChatId,
                'caption' => $caption,
                'parse_mode' => 'Markdown'
            ]);

        Log::info('TelegramService sendPhotoToSecondGroup response', [
            'telegram_response' => $response->json()
        ]);

        return true;
    }

    public static function generateScanImage(string $invoiceNo, int $deliveryPersonId): array
    {
        $dir = public_path('scan_picked_up');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $fileName = "scan_{$invoiceNo}_" . time() . ".png";
        $path = $dir . '/' . $fileName;

        $img = imagecreatetruecolor(700, 350);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);

        imagefill($img, 0, 0, $white);

        imagestring($img, 5, 30, 40, "SCAN CONFIRMED", $black);
        imagestring($img, 5, 30, 100, "Invoice: {$invoiceNo}", $black);
        imagestring($img, 5, 30, 160, "Delivery Person ID: {$deliveryPersonId}", $black);
        imagestring($img, 4, 30, 220, "Time: " . now(), $black);

        imagepng($img, $path);
        imagedestroy($img);

        return [
            'path' => $path,
            'name' => $fileName
        ];
    }

    public static function sendScanImageToGroup(
        string $groupChatId,
        string $imagePath,
        string $caption = ''
    ) {
        $token = env('TELEGRAM_BOT_TOKEN');

        if (!file_exists($imagePath)) {
            Log::error('TelegramService: Image not found', ['path' => $imagePath]);
            return false;
        }

        $response = Http::withoutVerifying()
            ->attach(
                'photo',
                fopen($imagePath, 'r'),
                basename($imagePath)
            )
            ->post("https://api.telegram.org/bot{$token}/sendPhoto", [
                'chat_id' => $groupChatId,
                'caption' => $caption,
                'parse_mode' => 'Markdown'
            ]);

        Log::info('Telegram scan image sent', [
            'chat_id' => $groupChatId,
            'response' => $response->json()
        ]);

        return true;
    }

    public static function generateDeliveryLabelImage($transaction, $qrcode, $localtion): array
    {
        $dir = public_path('scan_picked_up');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $fileName = "label_{$transaction->invoice_no}_" . time() . ".png";
        $path = $dir . '/' . $fileName;

        // Render Blade HTML
        $html = view('sale_pos.receipts.delivery_label', compact('transaction', 'qrcode', 'localtion'))->render();

        // Convert HTML to Image
        Browsershot::html($html)
            ->setNodeBinary('/usr/bin/node')
            ->setNpmBinary('/usr/bin/npm')
            ->setChromePath('/snap/bin/chromium')
            ->addOption('--no-sandbox')
            ->addOption('--disable-setuid-sandbox')
            ->userDataDir('/tmp/chrome-user-data')
            ->windowSize(700, 400)
            ->save($path);



        return [
            'path' => $path,
            'name' => $fileName
        ];
    }
}