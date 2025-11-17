<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TelegramService;
use App\ApiModel\ApiUser;
use App\ApiModel\TelegramStartToken;
use App\ApiModel\OnlineOrder;
use App\TelegramTemplate;

class TelegramBotWebhookController extends Controller
{
    public function webhook(Request $request)
    {
        \Log::info('Telegram webhook received', ['payload' => $request->all()]);

        $update = $request->all();
        
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

            $token->used = true;
            $token->save();

            $user = null;

            if ($token->api_user_id) {
                $user = ApiUser::find($token->api_user_id);
                TelegramService::saveChatIdIfNotExist($user, $chatId);
            }

            // Send order confirmation using database template
            if ($token->order_online_id) {
                $order = OnlineOrder::find($token->order_online_id);

                $name = $order->api_user->contact->name ?? 'Customer';

                // Fetch the template (assuming template name is 'order_confirmation')
                $template = TelegramTemplate::where('name', 'new_order')->first();

                if ($template) {
                    // Combine greeting, body, footer with spacing
                    $messageText = trim($template->greeting) . "\n\n" .
                                   trim($template->body) . "\n\n" .
                                   trim($template->footer);

                    // Replace placeholders
                    $placeholders = [
                        'user_name' => $name,
                        'order_id' => $order->id,
                        'business_name' => "SOB",
                        'amount' => number_format($order->total, 2),
                        'business_phone' => "099923333",
                    ];
                    
                    foreach ($placeholders as $key => $value) {
                        $messageText = str_replace("@{{ $key }}", $value, $messageText);
                    }

                    // Send message
                    TelegramService::sendMessageToUser($order->api_user, $messageText);
                }
            }

            return response('ok', 200);
        }

        return response('ok', 200);
    }
}