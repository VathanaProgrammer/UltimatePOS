<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware(['jwt.delivery', 'api:api_delivery']);
    }

    public function getOrders()
    {
        try {
            $orders = DB::table('transactions as t')
                ->leftJoin('contacts as c', 'c.id', '=', 't.contact_id')
                ->select(
                    'c.first_name as customer_name',
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
}