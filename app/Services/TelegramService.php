<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\ApiModel\ApiUser;
use App\ApiModel\TelegramStartToken;
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

    public static function sendImagesToGroup(array $files)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $groupChatId = '-5083476540';

        // Filter only real uploaded images
        $images = [];

        foreach ($files as $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $ext = strtolower($file->getClientOriginalExtension());

                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $images[] = [
                        'path' => $file->getRealPath(),
                        'name' => $file->getClientOriginalName()
                    ];
                }
            }
        }

        if (empty($images)) {
            Log::warning("TelegramService: No images to send");
            return false;
        }

        // ----- Build media group -----
        $media = [];
        $multipart = [];

        foreach ($images as $img) {
            $media[] = [
                'type' => 'photo',
                'media' => 'attach://' . $img['name']
            ];

            // Attach image file
            $multipart[] = [
                'name' => $img['name'],
                'contents' => fopen($img['path'], 'r'),
                'filename' => $img['name']
            ];
        }

        // Chat ID + Media group JSON
        $multipart[] = [
            'name' => 'chat_id',
            'contents' => $groupChatId
        ];

        $multipart[] = [
            'name' => 'media',
            'contents' => json_encode($media, JSON_UNESCAPED_UNICODE)
        ];

        // ----- SEND TO TELEGRAM -----
        $res = Http::withoutVerifying()
            ->asMultipart()
            ->post("https://api.telegram.org/bot{$token}/sendMediaGroup", $multipart);

        Log::info("TelegramService sendImagesToGroup response", [
            'telegram_response' => $res->json()
        ]);

        return true;
    }
}