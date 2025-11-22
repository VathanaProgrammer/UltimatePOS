<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\ApiModel\ApiUser;
use App\ApiModel\TelegramStartToken;

class TelegramService
{
    /**
     * Send a message to a Telegram user via chat_id
     */
    public static function sendMessageToUser(ApiUser $user, string $text, array $fileUrls = [])
    {
        if (!$user->telegram_chat_id) return false;

        $token = env('TELEGRAM_BOT_TOKEN');

        if (!empty($fileUrls)) {
            foreach ($fileUrls as $filePath) {
                $localPath = public_path(parse_url($filePath, PHP_URL_PATH));
                $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                $endpoint = $isImage ? 'sendPhoto' : 'sendDocument';

                $payload = ['chat_id' => $user->telegram_chat_id];

                if ($isImage) {
                    $payload['caption'] = $text;
                    $payload['photo'] = file_exists($localPath) ? curl_file_create($localPath) : $filePath;
                } else {
                    $payload['caption'] = $text;
                    $payload['document'] = file_exists($localPath) ? curl_file_create($localPath) : $filePath;
                }

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$token}/{$endpoint}");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_exec($ch);
                curl_close($ch);
            }
        } else {
            // Send text only
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$token}/sendMessage");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'chat_id' => $user->telegram_chat_id,
                'text' => $text,
            ]);
            curl_exec($ch);
            curl_close($ch);
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