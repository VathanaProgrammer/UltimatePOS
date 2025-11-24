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
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $images[] = ['path'=>$path, 'name'=>$name];
        } else {
            $docs[] = ['path'=>$path, 'name'=>$name];
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

        $multipart[] = ['name'=>'chat_id','contents'=>$user->telegram_chat_id];
        $multipart[] = ['name'=>'media','contents'=>json_encode($media, JSON_UNESCAPED_UNICODE)];

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
}