<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Services\TelegramService;
use SebastianBergmann\Type\TrueType;

class DeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['jwt.delivery']);
    }

    public function getOrders()
    {
        try {
            $deliveryId = auth()->id(); // current delivery user ID

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
                ->where('t.delivery_person', $deliveryId) // only orders for this delivery person
                ->whereNotIn('t.shipping_status', ['cancelled', 'delivered']) // exclude
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

            // --- CHECK STATUS ---
            $status = strtolower($transaction->shipping_status ?? '');

            if ($status === 'delivered') {
                return response()->json([
                    'success' => 0,
                    'msg' => 'This order is already delivered.'
                ]);
            }

            if ($status === 'cancelled') {
                return response()->json([
                    'success' => 0,
                    'msg' => 'This order is cancelled.'
                ]);
            }
            // --------------------

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
                    'msg' => 'Transaction_not_found'
                ], 404);
            }

            // 2. Check if already assigned
            if ($transaction->delivery_person !== null && $transaction->delivery_person != '') {
                return response()->json([
                    'success' => 0,
                    'msg' => 'Delivery_person_already_assigned',
                    'data' => 'transaction id: ' . $transactionId . ' and delivery person: ' . $deliveryPersonId
                ]);
            }

            // 3. Update
            DB::table('transactions')
                ->where('invoice_no', $transactionId)
                ->update([
                    'delivery_person' => $deliveryPersonId,
                    'shipping_status' => 'pick-up',
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
        DB::beginTransaction(); // start transaction
        try {
            $groupId = '-5083476540'; // your Telegram group ID
            $invoice = addcslashes($request->invoice_no, '_*[]()~`>#+-=|{}.!/');

            $text =
                "📦 *Drop Off Completed*\n\n" .
                "👤 *Customer:* {$request->name}\n" .
                "📞 *Phone:* {$request->phone}\n" .
                "📍 *Address:* {$request->address_detail}\n" .
                "🧭 Lat: {$request->latitude}\n" .
                "# invoice_no: {$invoice}\n" .
                "🧭 Lon: {$request->longitude}\n" .
                "User id from token: " . auth()->user()->id . "\n" .
                "User id from token: " . auth()->id();

            // Fetch transaction
            $transaction = DB::table("transactions")
                ->where("invoice_no", $request->invoice_no)
                ->first();

            if (!$transaction) {
                return [
                    'success' => 0,
                    'msg' => 'Transaction not found!'
                ];
            }

            // Update delivery_person if empty/null and set shipping_status to delivered
            DB::table('transactions')
                ->where('invoice_no', $request->invoice_no)
                ->update([
                    'shipping_status' => 'delivered',
                    'delivery_person' => $transaction->delivery_person ?: auth()->id(),
                    'updated_at' => now()
                ]);


            TelegramService::sendImagesToGroup($request->file('photos'));

            DB::commit();

            return [
                'success' => 1,
                'msg' => 'Saved, marked as delivered, and sent to Telegram',
                'data' => $text
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return [
                'success' => 0,
                'msg' => 'Failed to save or update',
                'error' => $e->getMessage()
            ];
        }
    }

    public function save_comment(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'invoice_no' => 'required|string',  // string because invoice_no may contain letters
            'comment' => 'required|string|max:225',
        ]);

        try {
            DB::beginTransaction();

            // Create a new comment
            DB::table('delivery_comments')->insert([
                'invoice_no' => $validated['invoice_no'],
                'user_id' => auth()->id(),      // get current logged-in user ID
                'comment' => $validated['comment'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'Comment saved successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'Failed to save comment: ' . $e->getMessage()
            ], 500);
        }
    }
}