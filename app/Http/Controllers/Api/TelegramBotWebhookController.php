<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;
use App\Services\TelegramService;
use App\ApiModel\ApiUser;
use App\ApiModel\TelegramStartToken;
use App\Models\OnlineOrder;

class TelegramBotWebhookController extends Controller
{
    public function webhook(Request $request)
    {   
       \Log::info('Telegram webhook received', ['payload' => $request->all()]);

        $update = $request->all();
        
        // Telegram always sends POST with "message"
        if (!isset($update['message'])) return response('ok', 200);

        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        // Handle /start <token>
        if (str_starts_with($text, '/start')) {

            $parts = explode(' ', $text, 2);
            $payload = $parts[1] ?? null;

            if (!$payload) return response('ok', 200);

            $token = TelegramStartToken::where('token', $payload)
                ->where('used', false)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })->first();

            if (!$token) return response('ok', 200);

            // Mark token used
            $token->used = true;
            $token->save();

            // Save user chat_id
            $user = null;

            if ($token->api_user_id) {
                $user = ApiUser::find($token->api_user_id);
                TelegramService::saveChatIdIfNotExist($user, $chatId);
            }

            // Send order confirmation
            if ($token->order_online_id) {
                $order = OnlineOrder::find($token->order_online_id);

                $name = $order->api_user->contact->name ?? 'Customer';

                TelegramService::sendMessageToUser(
                    $order->api_user,
                    "Hi {$name}! 👋 Your order #{$order->id} is confirmed. Total: $" . number_format($order->total, 2)
                );
            }

            return response('ok', 200);
        }

        return response('ok', 200);
    }
}