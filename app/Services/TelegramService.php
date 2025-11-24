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
        if (!$user->telegram_chat_id) {
            Log::warning("User {$user->id} has no Telegram chat_id");
            return false;
        }

        $token = env('TELEGRAM_BOT_TOKEN');

        // If no files → send a simple text message
        if (empty($files)) {
            Http::withoutVerifying()
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $user->telegram_chat_id,
                    'text'    => (string)$text,
                    'parse_mode' => 'HTML'
                ]);
            return true;
        }

        \Log::info("text info: ", ['$text: ' => $text]);

        // -------------------------
        // SEND MULTIPLE FILES AS ONE MEDIA GROUP
        // -------------------------
        $media = [];
        $multipart = [];

        // Make sure caption is always a string
        $caption = trim((string)$text);

        foreach ($files as $i => $file) {

            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $filename = $file->getClientOriginalName();
                $path     = $file->getRealPath();
            } elseif (is_array($file) && isset($file['path'], $file['name'])) {
                $filename = $file['name'];
                $path     = $file['path'];
            } else {
                continue;
            }

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

            // Attach file
            $multipart[] = [
                'name'     => "file{$i}",
                'contents' => fopen($path, 'r'),
                'filename' => $filename,
            ];

            // Media info
            $media[] = [
                'type'    => $isImage ? 'photo' : 'document',
                'media'   => "attach://file{$i}",
                'caption' => $i === 0 ? $caption : null  // ONLY FIRST FILE GET CAPTION
            ];
        }

        // Add chat ID
        $multipart[] = [
            'name'     => 'chat_id',
            'contents' => $user->telegram_chat_id
        ];

        // Add encoded media array
        $multipart[] = [
            'name'     => 'media',
            'contents' => json_encode($media, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ];

        // Send the media group
        $response = Http::withoutVerifying()
            ->asMultipart()
            ->post("https://api.telegram.org/bot{$token}/sendMediaGroup", $multipart);

        Log::info("Telegram sendMediaGroup response", [
            'status' => $response->status(),
            'body'   => $response->body()
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