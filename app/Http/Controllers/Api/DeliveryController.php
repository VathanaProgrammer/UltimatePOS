<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Services\TelegramService;

class DeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['jwt.delivery']);
    }

    public function getOrders()
    {
        try {
            $orders = DB::table('transactions as t')
                ->leftJoin('contacts as c', 'c.id', '=', 't.contact_id')
                ->select(
                    DB::raw("COALESCE(c.first_name, c.last_name, c.name) as customer_name"),
                    'c.mobile as phone',
                    't.shipping_address as address',
                    't.invoice_no as order_no',
                    't.final_total as cod_amount',
                    't.shipping_status'
                )
                ->orderBy('t.transaction_date', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'orders' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function decryptQr(Request $request)
    {
        $qrText = $request->input('qr_text');

        try {
            $transaction_id = Crypt::decryptString($qrText);

            $transaction = DB::table('transactions')
                ->where('id', $transaction_id)
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => 0,
                    'msg' => 'Transaction not found'
                ]);
            }

            $contact = DB::table('contacts')
                ->where('id', $transaction->contact_id)
                ->first();

            $data = [
                'id' => $transaction->id,
                'order_no' => $transaction->invoice_no,
                'customer_name' => $contact ? $contact->name : 'Unknown',
                'address' => $transaction->shipping_address ?? ($contact ? $contact->address_line_1 : ''),
                'cod_amount' => $transaction->final_total,
            ];

            return response()->json([
                'success' => 1,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'success' => 0,
                'msg' => 'Invalid QR code'
            ]);
        }
    }

    public function assignDeliveryPerson(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $deliveryPersonId = $request->input('delivery_person');

        if (!$transactionId || !$deliveryPersonId) {
            return response()->json([
                'success' => 0,
                'msg' => 'Transaction ID and Delivery Person ID are required'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // 1. Fetch transaction first
            $transaction = DB::table('transactions')->where('invoice_no', $transactionId)->first();

            if (!$transaction) {
                DB::rollBack();
                return response()->json([
                    'success' => 0,
                    'msg' => 'Transaction not found'
                ], 404);
            }

            // 2. Check if already assigned
            if ($transaction->delivery_person !== null && $transaction->delivery_person != '') {
                return response()->json([
                    'success' => 0,
                    'msg' => 'Delivery person already assigned',
                    'data' => 'transaction id: ' . $transactionId . ' and delivery person: ' . $deliveryPersonId
                ]);
            }

            // 3. Update
            DB::table('transactions')
                ->where('invoice_no', $transactionId)
                ->update([
                    'delivery_person' => $deliveryPersonId,
                    'shipping_status' => 'shipped',
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => 1,
                'msg' => 'Delivery person assigned successfully',
                'data' => 'transaction id: ' . $transactionId . ' and delivery person: ' . $deliveryPersonId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return response()->json([
                'success' => 0,
                'msg' => 'Error while assigning delivery person',
                'data' => 'transaction id: ' . $transactionId . ' and delivery person: ' . $deliveryPersonId
            ], 500);
        }
    }

    public function save(Request $request)
    {
        $groupId = '-5083476540'; // your group ID

        $text =
            "📦 *Drop Off Completed*\n\n" .
            "👤 *Customer:* {$request->name}\n" .
            "📞 *Phone:* {$request->phone}\n" .
            "📍 *Address:* {$request->address_detail}\n" .
            "🧭 Lat: {$request->latitude}\n" .
            "🧭 Lon: {$request->longitude}\n";

        // ---- send message ----
        TelegramService::sendImagesToGroup($request->file('photos'));
        return [
            'success' => 1,
            'msg' => 'Saved + sent to Telegram'
        ];
    }
}