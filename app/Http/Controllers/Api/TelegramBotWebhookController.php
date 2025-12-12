<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TelegramService;
use App\ApiModel\ApiUser;
use App\ApiModel\TelegramStartToken;
use App\ApiModel\OnlineOrder;
use App\TelegramTemplate;
use Illuminate\Support\Facades\DB;

class TelegramBotWebhookController extends Controller
{
    // public function webhook(Request $request)
    // {
    //     \Log::info('Telegram webhook received', ['payload' => $request->all()]);

    //     $update = $request->all();

    //     if (!isset($update['message'])) return response('ok', 200);

    //     $message = $update['message'];
    //     $chatId = $message['chat']['id'];
    //     $text = $message['text'] ?? '';

    //     // Handle /start <token>
    //     if (str_starts_with($text, '/start')) {
    //         $parts = explode(' ', $text, 2);
    //         $payload = $parts[1] ?? null;

    //         if (!$payload) return response('ok', 200);

    //         $token = TelegramStartToken::where('token', $payload)
    //             ->where('used', false)
    //             ->where(function ($q) {
    //                 $q->whereNull('expires_at')
    //                   ->orWhere('expires_at', '>', now());
    //             })->first();

    //         if (!$token) return response('ok', 200);

    //         $token->used = true;
    //         $token->save();

    //         $user = null;

    //         if ($token->api_user_id) {
    //             $user = ApiUser::find($token->api_user_id);
    //             TelegramService::saveChatIdIfNotExist($user, $chatId);
    //         }

    //         // Send order confirmation using database template
    //         if ($token->order_online_id) {
    //             $order = OnlineOrder::find($token->order_online_id);

    //             $name = $order->api_user->contact->name ?? 'Customer';

    //             // Fetch the template (assuming template name is 'order_confirmation')
    //             // Fetch the template (e.g., 'new_order')
    //             $template = TelegramTemplate::where('name', 'new_order')->first();

    //             if ($template) {
    //                 // Strip HTML from body for Telegram
    //                 $bodyText = strip_tags($template->body);

    //                 // Combine greeting, body, footer
    //                 $messageText = trim($template->greeting) . "\n\n" .
    //                             trim($bodyText) . "\n\n" .
    //                             trim($template->footer);

    //                 // Replace placeholders
    //                 $placeholders = [
    //                     'user_name'      => $order->api_user->contact->name ?? $name,
    //                     'order_id'       => $order->id,
    //                     'business_name'  => "SOB",
    //                     'amount'         => number_format($order->total, 2),
    //                     'business_phone' => "099923333",
    //                 ];

    //                 foreach ($placeholders as $key => $value) {
    //                     $messageText = str_replace("{".$key."}", $value, $messageText);
    //                 }
    //                 // Send message
    //                 TelegramService::sendMessageToUser($order->api_user, $messageText);
    //             }

    //         }

    //         return response('ok', 200);
    //     }

    //     return response('ok', 200);
    // }

    public function webhook(Request $request)
    {
        \Log::info('Telegram webhook received', ['payload' => $request->all()]);

        $update = $request->all();

        if (!isset($update['message'])) return response('ok', 200);

        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        // -----------------------------------------------------
        //  HANDLE /start <token> (Your original logic)
        // -----------------------------------------------------
        if (str_starts_with($text, '/start')) {
            $parts = explode(' ', $text, 2);
            $payload = $parts[1] ?? null;

            if (!$payload) {
                // [NEW] Ask for phone
                $this->askForPhoneNumber($chatId);
                return response('ok', 200);
            }

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

                $template = TelegramTemplate::where('name', 'new_order')->first();

                if ($template) {
                    $bodyText = strip_tags($template->body);

                    $messageText = trim($template->greeting) . "\n\n" .
                        trim($bodyText) . "\n\n" .
                        trim($template->footer);

                    $placeholders = [
                        'user_name'      => $order->api_user->contact->name ?? $name,
                        'order_id'       => $order->id,
                        'business_name'  => "SOB",
                        'amount'         => number_format($order->total, 2),
                        'business_phone' => "099923333",
                    ];

                    foreach ($placeholders as $key => $value) {
                        $messageText = str_replace("{" . $key . "}", $value, $messageText);
                    }

                    TelegramService::sendMessageToUser($order->api_user, $messageText);
                }
            }

            // [NEW] After start link, still ask for phone if user is new
            if (!$user) {
                $this->askForPhoneNumber($chatId);
            }

            return response('ok', 200);
        }

        // -----------------------------------------------------
        //  [NEW] HANDLE USER SENDING PHONE NUMBER
        // -----------------------------------------------------
        if (isset($message['contact'])) {
            return $this->registerUserFromTelegramContact($message['contact'], $chatId);
        }

        return response('ok', 200);
    }

    private function askForPhoneNumber($chatId)
    {
        $token = env('TELEGRAM_BOT_TOKEN');

        Http::withoutVerifying()->post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => "Please share your phone number to complete registration.",
            'reply_markup' => [
                "keyboard" => [
                    [
                        [
                            "text" => "Share Phone Number",
                            "request_contact" => true
                        ]
                    ]
                ],
                "one_time_keyboard" => true,
                "resize_keyboard" => true
            ]
        ]);
    }

    private function registerUserFromTelegramContact($contact, $chatId)
    {
        $phone = $contact['phone_number'];
        $name  = $contact['first_name'] ?? 'Telegram User';

        // Prevent duplicate phone numbers
        $exists = DB::table('contacts')
            ->where('mobile', $phone)
            ->exists();

        if ($exists) {
            TelegramService::sendRawMessage($chatId, "This phone number is already registered.");
            return response('ok', 200);
        }

        DB::beginTransaction();
        try {
            // Create Contact
            $contactId = DB::table('contacts')->insertGetId([
                'name' => $name,
                'mobile' => $phone,
                'telegram_chat_id' => $chatId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create api_user
            $apiUser = ApiUser::create([
                'contact_id' => $contactId,
                'name' => $name,
                'mobile' => $phone,
                'telegram_chat_id' => $chatId,
            ]);

            DB::commit();

            TelegramService::sendRawMessage($chatId, "Registration completed. Thank you!");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Registration failed", ['error' => $e->getMessage()]);
            TelegramService::sendRawMessage($chatId, "An error occurred. Please try again.");
        }

        return response('ok', 200);
    }
}