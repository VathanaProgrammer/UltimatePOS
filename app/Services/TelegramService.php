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
    public static function sendMessageToUser(ApiUser $user, string $text, array $fileNames = [])
    {
        if (!$user->telegram_chat_id) {
            Log::warning("User {$user->id} has no Telegram chat_id");
            return false;
        }

        $token = env('TELEGRAM_BOT_TOKEN');

        // Log input
        Log::info("Sending Telegram message", [
            'user_id' => $user->id,
            'chat_id' => $user->telegram_chat_id,
            'text' => $text,
            'files' => $fileNames
        ]);

        // Send files if available
        foreach ($fileNames as $fileName) {
            $localPath = public_path('uploads/media/' . $fileName);

            if (!file_exists($localPath)) {
                Log::error("File not found for Telegram", ['file' => $localPath]);
                continue; // skip missing file
            }

            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif']);

            $endpoint = $isImage ? 'sendPhoto' : 'sendDocument';

            // Telegram requires multipart/form-data for local files
            $multipart = [
                [
                    'name'     => $isImage ? 'photo' : 'document',
                    'contents' => fopen($localPath, 'r'),
                    'filename' => basename($localPath)
                ],
                [
                    'name'     => 'chat_id',
                    'contents' => $user->telegram_chat_id
                ],
                [
                    'name'     => 'caption',
                    'contents' => $text
                ]
            ];

            try {
                $response = Http::withoutVerifying()
                    ->withHeaders(['Accept' => 'application/json'])
                    ->asMultipart()
                    ->post("https://api.telegram.org/bot{$token}/{$endpoint}", $multipart);

                Log::info("Telegram API response", [
                    'file' => $fileName,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            } catch (\Exception $e) {
                Log::error("Telegram send failed", ['file' => $fileName, 'error' => $e->getMessage()]);
            }
        }

        // Send text-only if no files
        if (empty($fileNames)) {
            try {
                $response = Http::withoutVerifying()
                    ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id' => $user->telegram_chat_id,
                        'text' => $text,
                        'parse_mode' => 'HTML'
                    ]);

                Log::info("Telegram API text-only response", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            } catch (\Exception $e) {
                Log::error("Telegram text-only send failed", ['error' => $e->getMessage()]);
            }
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