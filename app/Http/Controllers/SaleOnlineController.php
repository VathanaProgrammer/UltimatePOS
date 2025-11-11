<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

use App\ApiModel\OnlineOrder;

class SaleOnlineController extends Controller
{
    public function index()
    {
        // Optional: pass locations/customers for filters
        return view('E_Commerce.sale_online.index');
    }

    // make sure Carbon is imported at the top

    public function getData(Request $request)
    {
        $orders = OnlineOrder::with(['api_user.contact', 'savedAddress'])
            ->when($request->location_id, fn($q) => $q->where('location_id', $request->location_id))
            ->when($request->customer_id, fn($q) => $q->where('api_user_id', $request->customer_id))
            ->when($request->status, fn($q) => $q->where('stauts', $request->status))
            ->when($request->shipping_status, fn($q) => $q->where('shipping_status', $request->shipping_status))
            ->when(
                $request->start_date && $request->end_date,
                fn($q) =>
                $q->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59'])
            );

        return DataTables::of($orders)
            ->addColumn('customer_name', fn($order) => optional($order->api_user->contact)->name ?? 'N/A')
            ->addColumn('mobile', fn($order) => optional($order->api_user->contact)->mobile ?? ($order->current_phone ?? 'N/A'))
            ->addColumn('address', function ($order) {
                if ($order->saved_address_id && $order->savedAddress) {
                    return $order->savedAddress->details;
                }
                return trim(
                    ($order->current_house_number ?? '') . ' ' .
                        ($order->current_road ?? '') . ' ' .
                        ($order->current_village ?? '') . ' ' .
                        ($order->current_town ?? '') . ' ' .
                        ($order->current_city ?? '') . ' ' .
                        ($order->current_state ?? '') . ' ' .
                        ($order->current_postcode ?? '') . ' ' .
                        ($order->current_country ?? '')
                );
            })
            ->addColumn('stauts', function ($order) {
                // yellow background and white text
                return '<span class="px-1 py-0 rounded" style="background-color: #f59e0b; color: white; padding-top: 2px; padding-bottom: 2px;">' . $order->stauts . '</span>';
            })
            ->addColumn('shipping_status', function ($order) {
                // same styling for shipping status
                return '<span class="px-1 rounded" style="background-color: #f59e0b; color: white; padding-top: 2px; padding-bottom: 2px;">' . ($order->status ?? 'pending') . '</span>';
            })
            ->addColumn('total_qty', fn($order) => $order->total_qty)
            ->addColumn('total', fn($order) => $order->total)
            ->addColumn('created_at', function ($order) {
                // Format datetime using Carbon
                return Carbon::parse($order->created_at)->format('d/m/Y h:i A');
            })
            ->addColumn('action', fn($order) => '<button class="btn btn-sm btn-primary view-order" data-id="' . $order->id . '">View</button>')
            ->rawColumns(['stauts', 'shipping_status', 'action'])
            ->make(true);
    }
}
