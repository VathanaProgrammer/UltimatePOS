<?php

namespace App\Http\Controllers\Api;

use App\ApiModel\OnlineOrder;
use App\ApiModel\ApiCurrentUserAddress;
use App\ApiModel\OrderOnlineDetails;
use App\ApiModel\ApiUserAddress;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\NewOnlineOrderNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\ApiModel\TelegramStartToken;
use App\User;
use App\ApiModel\ApiUser;
use App\TelegramTemplate;
use App\Services\TelegramService;

class OrderController extends Controller
{
    public function test()
    {
        return response()->json($data = auth()->user()->notifications()->latest()->get()->toArray());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'api_user_id' => 'required|integer|exists:api_users,id',
            'address_type' => 'required|in:current,saved',
            'saved_address_id' => 'nullable|integer|exists:api_user_addresses,id',
            'address' => 'nullable|array',
            'address.label' => 'nullable|string|max:255',
            'address.phone' => 'nullable|string|max:20',
            'address.details' => 'nullable|string|max:500',
            'address.coordinates' => 'nullable|array',
            'address.short_address' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price_at_order' => 'required|numeric|min:0',
            'items.*.total_line' => 'required|numeric|min:0',
            'items.*.image_url' => 'nullable|string|max:255',
            'paymentMethod' => 'required|string|in:QR,Cash,Card',
            'total_qty' => 'required|integer|min:1',
            'total' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $orderData = [
                'api_user_id' => $data['api_user_id'],
                'payment' => $data['paymentMethod'],
                'total_qty' => $data['total_qty'],
                'total' => $data['total'],
                'address_type' => $data['address_type'],
            ];

            // Handle address
            if ($data['address_type'] === 'saved' && !empty($data['saved_address_id'])) {
                $savedAddress = ApiUserAddress::find($data['saved_address_id']);
                if (!$savedAddress->short_address) {
                    $savedAddress->short_address = $savedAddress->village . ', ' . $savedAddress->city . ', ' . $savedAddress->country;
                    $savedAddress->save();
                }
                $orderData['saved_address_id'] = $savedAddress->id;
            } elseif ($data['address_type'] === 'current' && isset($data['address'])) {
                $shortAddress = $data['address']['short_address']
                    ?? (($data['address']['details'] ?? '') . ', ' .
                        ($data['address']['coordinates']['lat'] ?? '') . ', ' .
                        ($data['address']['coordinates']['lng'] ?? ''));

                $currentAddress = ApiCurrentUserAddress::create([
                    'label' => $data['address']['label'] ?? 'Current Address',
                    'phone' => $data['address']['phone'] ?? null,
                    'details' => $data['address']['details'] ?? null,
                    'coordinates' => json_encode($data['address']['coordinates'] ?? []),
                    'short_address' => $shortAddress,
                ]);

                $orderData['current_address_id'] = $currentAddress->id;
            }

            // Create order
            $order = OnlineOrder::create($orderData);

            // Create order items
            foreach ($data['items'] as $item) {
                OrderOnlineDetails::create([
                    'order_online_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price_at_order' => $item['price_at_order'],
                    'total_line' => $item['total_line'],
                    'image_url' => $item['image_url'] ?? null,
                ]);
            }

            // --- SYSTEM NOTIFICATION ---
            $notificationData = [
                'msg' => "New Online Order #{$order->id} from {$order->api_user->contact->name}, total $" . number_format($order->total, 2),
                'link' => route('E_Commerce.sale_online.index') . '?open_order=' . $order->id,
                'icon_class' => 'fa fa-shopping-cart',
                'created_at' => now(),
            ];

            // 'link' => route('orders.show', ['order' => $order->id]),

            // Send notification 
            $admins = User::all();
            Notification::send($admins, new NewOnlineOrderNotification($notificationData));
            DB::commit();



            // --- TELEGRAM INTEGRATION ---
            $user = ApiUser::find($data['api_user_id']);

           if ($user->telegram_chat_id) {
    \Log::info('Saved chat id for user', ['user_id' => $user->id, 'chat_id' => $user->telegram_chat_id]);

    // Fetch template from DB
    $template = TelegramTemplate::where('name', 'new_order')->first();

    if ($template) {
        // Combine greeting, body, footer (strip HTML for Telegram)
        $messageText = trim($template->greeting) . "\n\n" .
                       trim(strip_tags($template->body)) . "\n\n" .
                       trim($template->footer);

        // Placeholders
        $placeholders = [
            'user_name'      => $order->api_user->contact->name ?? "Customer",
            'order_id'       => $order->id,
            'business_name'  => "SOB",
            'amount'         => number_format($order->total, 2),
            'business_phone' => "099923333",
        ];

foreach ($placeholders as $key => $value) {
    $variants = [
        "@{{$key}}",   // matches @{{order_id}}
        "{{$key}}",    // matches {{order_id}}
        "{".$key."}"   // matches {order_id} safely
    ];

    $messageText = str_replace($variants, $value, $messageText);
}


        // Send message
        TelegramService::sendMessageToUser($order->api_user, $messageText);
    }

    $telegramLink = "https://t.me/sysproasiabot";
}



            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $order->id,
                'telegram_start_link' => $telegramLink,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }
}