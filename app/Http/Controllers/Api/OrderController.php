<?php
namespace App\Http\Controllers\Api;

use App\ApiModel\OnlineOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ApiModel\OrderOnlineDetails;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'api_user_id' => 'required|integer|exists:api_users,id',
            'address' => 'required|array',
            'address.type' => 'required|in:current,saved',
            'address.label' => 'required|string|max:255',
            'address.house_number' => 'nullable|string|max:50',
            'address.road' => 'nullable|string|max:255',
            'address.village' => 'nullable|string|max:255',
            'address.town' => 'nullable|string|max:255',
            'address.city' => 'nullable|string|max:255',
            'address.state' => 'nullable|string|max:255',
            'address.postcode' => 'nullable|string|max:20',
            'address.country' => 'nullable|string|max:100',
            'address.country_code' => 'nullable|string|max:10',
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
            $address = $data['address'];
            $orderData = [
                'api_user_id' => $data['api_user_id'],
                'payment' => $data['paymentMethod'],
                'total_qty' => $data['total_qty'],
                'total' => $data['total'],
                'address_type' => $address['type'],
            ];

            // If saved address, store the foreign key
            if ($address['type'] === 'saved' && isset($address['id'])) {
                $orderData['saved_address_id'] = $address['id'];
            } else {
                // Current address, store details in separate columns
                $orderData = array_merge($orderData, [
                    'current_house_number' => $address['house_number'] ?? null,
                    'current_road' => $address['road'] ?? null,
                    'current_neighbourhood' => $address['neighbourhood'] ?? null,
                    'current_village' => $address['village'] ?? null,
                    'current_town' => $address['town'] ?? null,
                    'current_city' => $address['city'] ?? null,
                    'current_state' => $address['state'] ?? null,
                    'current_postcode' => $address['postcode'] ?? null,
                    'current_country' => $address['country'] ?? 'Cambodia',
                    'current_country_code' => $address['country_code'] ?? 'KH',
                ]);
            }

            $order = OnlineOrder::create($orderData);

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

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $order->id,
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
