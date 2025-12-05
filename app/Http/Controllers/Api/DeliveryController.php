<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class DeliveryController extends Controller
{
    //
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
                    // Use COALESCE to get the first non-null value
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
            $json = Crypt::decryptString($qrText);
            $data = json_decode($json, true);

            if (!$data || !isset($data['transaction_id'])) {
                return response()->json(['success' => 0, 'msg' => 'Invalid QR code']);
            }

            return response()->json(['success' => 1, 'data' => $data]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(['success' => 0, 'msg' => 'Invalid QR code']);
        }
    }
}