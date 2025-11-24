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

        // If no files → send normal text message
        if (empty($files)) {
            Http::withoutVerifying()->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $user->telegram_chat_id,
                'text'    => $text,
                'parse_mode' => 'HTML'
            ]);
            return true;
        }

        // ========= MULTIPLE FILES → USE MEDIA GROUP =========
        $media = [];

        foreach ($files as $index => $file) {

            $isUploaded = is_array($file);

            $path = $isUploaded ? $file['path'] : $file->getRealPath();
            $name = $isUploaded ? $file['name'] : $file->getClientOriginalName();

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            $type = $isImage ? 'photo' : 'document';

            $item = [
                'type' => $type,
                'media' => 'attach://' . $name
            ];

            // caption ONLY ON FIRST ITEM
            if ($index === 0) {
                $item['caption'] = $text;
            }

            $media[] = $item;

            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path, 'r'),
                'filename' => $name
            ];
        }

        $multipart[] = [
            'name' => 'chat_id',
            'contents' => $user->telegram_chat_id
        ];

        $multipart[] = [
            'name' => 'media',
            'contents' => json_encode($media, JSON_UNESCAPED_UNICODE)
        ];

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