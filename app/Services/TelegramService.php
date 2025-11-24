<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\ApiModel\ApiUser;
use App\ApiModel\TelegramStartToken;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Send a message to a Telegram user via chat_id
     */
    public static function sendMessageToUser(ApiUser $user, string $text, array $files = [])
    {
        if (!$user->telegram_chat_id) return false;

        $token = env('TELEGRAM_BOT_TOKEN');

        // 1) Send text first
        Http::withoutVerifying()->post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $user->telegram_chat_id,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);

        // 2) No files → stop
        if (empty($files)) return true;

        $media = [];
        $multipart = [];

        foreach ($files as $i => $file) {

            // MUST be UploadedFile
            if (!$file instanceof \Illuminate\Http\UploadedFile) {
                Log::error("Invalid file input", ['file' => $file]);
                continue;
            }

            $name = $file->getClientOriginalName();
            $path = $file->getRealPath();

            $ext = strtolower($file->getClientOriginalExtension());
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

            $type = $isImage ? "photo" : "document";

            $media[] = [
                "type"  => $type,
                "media" => "attach://file{$i}"
            ];

            $multipart[] = [
                "name"     => "file{$i}",
                "contents" => fopen($path, "r"),
                "filename" => $name
            ];
        }

        // add chat_id + media json
        $multipart[] = [
            'name' => 'chat_id',
            'contents' => $user->telegram_chat_id
        ];

        $multipart[] = [
            'name' => 'media',
            'contents' => json_encode($media)
        ];

        $response = Http::withoutVerifying()
            ->asMultipart()
            ->post("https://api.telegram.org/bot{$token}/sendMediaGroup", $multipart);

        Log::info("Telegram sendMediaGroup response", [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

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
}