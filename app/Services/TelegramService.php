<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\ApiModel\ApiUser;
use App\ApiModel\TelegramStartToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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


    private static function drawText($img, $text, $x, $y, $size = 12)
    {
        // Choose font based on first character
        $firstChar = mb_substr($text, 0, 1, 'UTF-8');
        $khmerFont = public_path('fonts/khmer/NotoSansKhmer-Regular.ttf');
        $latinFont = public_path('fonts/latin/NotoSans-Regular.ttf');

        // Use Khmer font if first char is Khmer, else Latin
        $font = preg_match('/[\x{1780}-\x{17FF}]/u', $firstChar) ? $khmerFont : $latinFont;

        $black = imagecolorallocate($img, 0, 0, 0);

        // Draw text
        imagettftext($img, $size, 0, $x, $y, $black, $font, $text);
    }

    public static function generateScanImage(string $invoiceNo, int $deliveryPersonId, $contact = null, $location = null): array
    {
        $dir = public_path('/scan_picked_up');
        if (!file_exists($dir)) mkdir($dir, 0755, true);

        $fileName = "scan_{$invoiceNo}_" . time() . ".png";
        $path = $dir . '/' . $fileName;

        $imgWidth = 350;
        $imgHeight = 250;
        $img = imagecreatetruecolor($imgWidth, $imgHeight);

        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);

        // 🟢 SENDER
        $senderMobile = $location?->mobile ?? '0123456789';
        self::drawText($img, "SOB", 10, 20, 14);
        self::drawText($img, "Mobile: {$senderMobile}", 10, 40, 12);
        self::drawText($img, now()->format('d/m/Y H:iA'), 10, 60, 12);

        // 🟢 INVOICE
        self::drawText($img, "SCAN CONFIRMED", 10, 90, 14);
        self::drawText($img, "Invoice: {$invoiceNo}", 10, 110, 12);
        self::drawText($img, "Delivery ID: {$deliveryPersonId}", 10, 130, 12);

        // 🟢 RECEIVER
        $receiverName = $contact?->name ?? '-';
        $receiverMobile = $contact?->mobile ?? '-';
        $receiverAddress = $contact
            ? ($contact->address_line_1 && $contact->address_line_2
                ? $contact->address_line_1 . ', ' . $contact->address_line_2
                : ($contact->address_line_1 ?? $contact->address_line_2 ?? '-'))
            : '-';

        self::drawText($img, "Receiver: {$receiverName}", 10, 160, 12);
        self::drawText($img, "Mobile: {$receiverMobile}", 10, 180, 12);
        self::drawText($img, "Address: {$receiverAddress}", 10, 200, 12);

        // 🟢 QR CODE
        $qrText = (string) $invoiceNo;
        $qrFile = $dir . '/qr_' . time() . '.png';

        QrCode::format('png')->size(120)->margin(0)->generate($qrText, $qrFile);
        $qrImg = imagecreatefrompng($qrFile);
        imagecopy($img, $qrImg, $imgWidth - 130, 10, 0, 0, imagesx($qrImg), imagesy($qrImg));
        imagedestroy($qrImg);
        unlink($qrFile);

        imagepng($img, $path);
        imagedestroy($img);

        return ['path' => $path, 'name' => $fileName];
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
}