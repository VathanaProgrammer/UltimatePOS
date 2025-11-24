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

        // If no files → send normal message
        if (empty($files)) {
            try {
                $res = Http::withoutVerifying()
                    ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id' => $user->telegram_chat_id,
                        'text'    => $text,
                        'parse_mode' => 'HTML'
                    ]);

                Log::info("Telegram text message response", [
                    'status' => $res->status(),
                    'body'   => $res->body()
                ]);
            } catch (\Exception $e) {
                Log::error("Telegram send message error", ['error' => $e->getMessage()]);
            }

            return true;
        }

        // -------------------------------
        // ONE message with ALL FILES
        // sendMediaGroup
        // -------------------------------
        $media = [];
        $multipart = [];

        foreach ($files as $i => $file) {

            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $filename = $file->getClientOriginalName();
                $path = $file->getRealPath();
            } elseif (is_array($file) && isset($file['path'], $file['name'])) {
                $filename = $file['name'];
                $path = $file['path'];
            } else {
                continue;
            }

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);

            // Attach file
            $multipart[] = [
                'name'     => "file{$i}",
                'contents' => fopen($path, 'r'),
                'filename' => $filename,
            ];

            // Media entry
            $media[] = [
                'type'    => $isImage ? 'photo' : 'document',
                'media'   => "attach://file{$i}",
                'caption' => $i === 0 ? $text : null // caption on FIRST file only
            ];
        }

        // Add media info
        $multipart[] = [
            'name'     => 'media',
            'contents' => json_encode($media)
        ];

        // Add chat ID
        $multipart[] = [
            'name'     => 'chat_id',
            'contents' => $user->telegram_chat_id
        ];

        try {
            $res = Http::withoutVerifying()
                ->asMultipart()
                ->post("https://api.telegram.org/bot{$token}/sendMediaGroup", $multipart);

            Log::info("Telegram sendMediaGroup response", [
                'status' => $res->status(),
                'body'   => $res->body()
            ]);
        } catch (\Exception $e) {
            Log::error("Telegram sendMediaGroup failed", ['error' => $e->getMessage()]);
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
}