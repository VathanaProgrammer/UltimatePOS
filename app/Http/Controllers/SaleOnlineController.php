<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Utils\TransactionUtil;
use App\Utils\BusinessUtil;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\ApiModel\ApiUser;
use App\Contact;
use App\ApiModel\OnlineOrder;

class SaleOnlineController extends Controller
{
    protected $businessUtil;

    protected $transactionUtil;
    public function __construct(TransactionUtil $transactionUtil, BusinessUtil $businessUtil)
    {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
    }

    public function index()
    {
        // Optional: pass locations/customers for filters

        $customers = ApiUser::join("contacts", 'api_users.contact_id', '=', 'contacts.id')
            ->pluck('contacts.name', 'contacts.id');

        return view('E_Commerce.sale_online.index', compact('customers'));
    }

    // make sure Carbon is imported at the top

    public function getData(Request $request)
    {
        try {
            $orders = OnlineOrder::with([
                'api_user.contact',
                'savedAddress',
                'currentAddress' => fn($q) => $q->latest(),
                'order_online_details.product'
            ])

                // CUSTOMER FILTER
                ->when(
                    $request->customer_id,
                    fn($q) =>
                    $q->where('api_user_id', $request->customer_id)
                )

                // ORDER STATUS FILTER
                ->when(
                    $request->status,
                    fn($q) =>
                    $q->where('stauts', $request->status)
                )

                // SHIPPING STATUS FILTER
                ->when(
                    $request->shipping_status,
                    fn($q) =>
                    $q->where('shipping_status', $request->shipping_status)
                )

                // DATE RANGE FILTER
                ->when(
                    $request->start_date && $request->end_date,
                    fn($q) => $q->whereBetween('created_at', [
                        $request->start_date . ' 00:00:00',
                        $request->end_date   . ' 23:59:59'
                    ])
                )

                // 🔥 MAIN SEARCH FILTER
                ->when($request->search_text, function ($q) use ($request) {
                    $keyword = '%' . $request->search_text . '%';

                    $q->where(function ($q) use ($keyword) {
                        $q->where('id', 'like', $keyword)
                            ->orWhere('total', 'like', $keyword)
                            ->orWhereHas(
                                'api_user.contact',
                                fn($q) =>
                                $q->where('name', 'like', $keyword)
                                    ->orWhere('mobile', 'like', $keyword)
                            )
                            ->orWhereHas(
                                'savedAddress',
                                fn($q) =>
                                $q->where('short_address', 'like', $keyword)
                                    ->orWhere('details', 'like', $keyword)
                            )
                            ->orWhereHas(
                                'currentAddress',
                                fn($q) =>
                                $q->where('short_address', 'like', $keyword)
                                    ->orWhere('details', 'like', $keyword)
                            )
                            ->orWhereHas(
                                'order_online_details.product',
                                fn($q) =>
                                $q->where('name', 'like', $keyword)
                            );
                    });
                });

            return DataTables::of($orders)
                ->addColumn(
                    'customer_name',
                    fn($order) =>
                    optional($order->api_user->contact)->name ?? 'N/A'
                )
                ->addColumn(
                    'mobile',
                    fn($order) =>
                    optional($order->api_user->contact)->mobile
                        ?? ($order->currentAddress->phone ?? 'N/A')
                )
                ->addColumn('address', function ($order) {
                    if ($order->saved_address_id && $order->savedAddress) {
                        return $order->savedAddress->short_address ?? $order->savedAddress->details;
                    }
                    if ($order->current_address_id) {
                        $current = $order->currentAddress()->latest()->first();
                        return $current->short_address ?? $current->details ?? 'N/A';
                    }
                    return 'N/A';
                })
                ->addColumn(
                    'stauts',
                    fn($order) =>
                    '<span class="px-1 py-0 rounded" style="background-color:#f59e0b;color:white;">'
                        . $order->stauts .
                        '</span>'
                )
                ->addColumn(
                    'shipping_status',
                    fn($order) =>
                    '<span class="px-1 py-0 rounded" style="background-color:#2563eb;color:white;">'
                        . ($order->shipping_status ?? 'pending') .
                        '</span>'
                )
                ->addColumn('details', function ($order) {
                    return $order->order_online_details->map(function ($d) {
                        return [
                            'name'       => $d->product->name ?? $d->product_name,
                            'price'      => $d->price_at_order,
                            'qty'        => $d->qty,
                            'total_line' => $d->total_line,
                            'image'      => $d->image_url
                                ? asset('uploads/img/' . $d->image_url)
                                : asset('img/default.png'),
                        ];
                    })->toArray();
                })

                ->addColumn('total_qty', fn($order) => $order->total_qty)
                ->addColumn('total', fn($order) => '$' . number_format($order->total, 2))
                ->addColumn(
                    'created_at',
                    fn($order) =>
                    Carbon::parse($order->created_at)->format('d/m/Y h:i A')
                )
                ->addColumn(
                    'action',
                    fn($order) =>
                    '<button class="btn btn-sm btn-primary view-order" data-id="' . $order->id . '">View</button>'
                )
                ->rawColumns(['stauts', 'shipping_status', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in SaleOnlineController@getData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Something went wrong. Check logs.'], 500);
        }
    }


    public function create_sell_from_online(Request $request)
    {
        DB::beginTransaction();
        try {
            \Log::info('create_sale_order_from_online:start', ['request' => $request->all()]);

            // Load order
            $order = OnlineOrder::with([
                'api_user.contact',
                'savedAddress',
                'currentAddress' => fn($q) => $q->latest(),
                'order_online_details.product',
            ])->find($request->order_id);

            \Log::info('Order loaded', ['order_id' => $request->order_id, 'order_found' => (bool)$order]);

            if (!$order) {
                \Log::warning('Order not found', ['order_id' => $request->order_id]);
                return response()->json(['success' => false, 'error' => 'Order not found'], 404);
            }

            // Determine shipping address
            if ($order->saved_address_id && $order->savedAddress) {
                $shipping_address = $order->savedAddress->details ?? $order->savedAddress->short_address;
            } elseif ($order->current_address_id && $order->currentAddress) {
                $current = $order->currentAddress()->latest()->first();
                $shipping_address = $current->details ?? $current->short_address ?? null;
            } else {
                $shipping_address = null;
            }
            \Log::info('Shipping address determined', ['shipping_address' => $shipping_address]);

            $now = Carbon::now()->toDateTimeString();
            $authId = auth()->id();
            $business_id = auth()->user()->business_id;

            // --- Invoice generation (custom "S-year-month-seq") ---
            $year = Carbon::now()->format('Y');
            $month = Carbon::now()->format('m');
            $prefix = 'S-' . $year . '-' . $month . '-'; // e.g. S-2025-11-

            \Log::info('Invoice base', ['prefix' => $prefix]);

            // Find the last invoice for the current prefix
            $lastInvoice = DB::table('transactions')
                ->where('business_id', $business_id)
                ->where('type', 'sell')
                ->where('invoice_no', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            \Log::info('Last invoice query result', ['lastInvoice' => $lastInvoice ? $lastInvoice->invoice_no : null]);

            $nextSequence = 1;
            if ($lastInvoice && !empty($lastInvoice->invoice_no)) {
                // Extract sequence after last hyphen
                $parts = explode('-', $lastInvoice->invoice_no);
                $lastPart = end($parts);
                // cast safely
                $lastNum = intval(preg_replace('/\D/', '', $lastPart));
                $nextSequence = $lastNum + 1;
            }

            // build next invoice (with zero padding 4)
            $nextNumber = str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
            $invoice_no = $prefix . $nextNumber;

            // Safety: check for collisions and increment until unique (race-safe-ish in transaction)
            $attempts = 0;
            while (DB::table('transactions')->where('business_id', $business_id)->where('invoice_no', $invoice_no)->exists()) {
                $attempts++;
                $nextSequence++;
                $nextNumber = str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
                $invoice_no = $prefix . $nextNumber;
                if ($attempts > 50) {
                    // Something wrong — avoid infinite loop
                    throw new \Exception('Unable to generate unique invoice number after 50 attempts');
                }
            }

            \Log::info('Invoice assigned', ['invoice_no' => $invoice_no]);

            // --- Build transaction (sell) with as many fields filled as possible ---
            $transaction = [
                'business_id' => $business_id,
                'location_id' => $order->location_id ?? 1,
                'is_kitchen_order' => $order->is_kitchen_order ?? 0,
                'res_table_id' => $order->res_table_id ?? null,
                'res_waiter_id' => $order->res_waiter_id ?? null,
                'res_order_status' => $order->res_order_status ?? null,

                // SELL-specific fields
                'type' => 'sell',
                'sub_type' => null,
                'status' => 'final',           // final (completed sell)
                'sub_status' => null,
                'is_quotation' => 0,
                'payment_status' => 'paid',    // mark paid as requested
                'adjustment_type' => 'normal',

                'contact_id' => optional($order->api_user->contact)->id,
                'customer_group_id' => $order->customer_group_id ?? null,

                'invoice_no' => $invoice_no,
                'ref_no' => $order->ref_no ?? null,
                'source' => $order->source ?? 'online',
                'subscription_no' => $order->subscription_no ?? null,
                'subscription_repeat_on' => $order->subscription_repeat_on ?? null,
                'transaction_date' => $order->transaction_date ?? $now,

                'total_before_tax' => $order->total_before_tax ?? $order->total ?? 0,
                'tax_id' => $order->tax_id ?? null,
                'tax_amount' => $order->tax_amount ?? 0,

                'discount_type' => $order->discount_type ?? null,
                'discount_amount' => $order->discount_amount ?? 0,

                'rp_redeemed' => $order->rp_redeemed ?? 0,
                'rp_redeemed_amount' => $order->rp_redeemed_amount ?? 0,

                'shipping_details' => optional($order->savedAddress)->short_address
                    ?? optional($order->currentAddress()->latest()->first())->short_address ?? '',
                'shipping_address' => $shipping_address ?? '',
                'delivery_date' => $order->delivery_date ?? $now,
                'shipping_status' => $order->shipping_status ?? 'ordered',
                'delivered_to' => $order->delivered_to ?? optional($order->api_user->contact)->name ?? '',
                'delivery_person' => $order->delivery_person ?? null,
                'shipping_charges' => $order->shipping_charges ?? 0,

                'shipping_custom_field_1' => $order->shipping_custom_field_1 ?? null,
                'shipping_custom_field_2' => $order->shipping_custom_field_2 ?? null,
                'shipping_custom_field_3' => $order->shipping_custom_field_3 ?? null,
                'shipping_custom_field_4' => $order->shipping_custom_field_4 ?? null,
                'shipping_custom_field_5' => $order->shipping_custom_field_5 ?? null,

                'additional_notes' => $order->additional_notes ?? '',
                'staff_note' => $order->staff_note ?? '',

                'is_export' => 0,
                'export_custom_fields_info' => $order->export_custom_fields_info ?? null,

                'round_off_amount' => $order->round_off_amount ?? 0,

                'additional_expense_key_1' => $order->additional_expense_key_1 ?? null,
                'additional_expense_value_1' => $order->additional_expense_value_1 ?? 0,
                'additional_expense_key_2' => $order->additional_expense_key_2 ?? null,
                'additional_expense_value_2' => $order->additional_expense_value_2 ?? 0,
                'additional_expense_key_3' => $order->additional_expense_key_3 ?? null,
                'additional_expense_value_3' => $order->additional_expense_value_3 ?? 0,
                'additional_expense_key_4' => $order->additional_expense_key_4 ?? null,
                'additional_expense_value_4' => $order->additional_expense_value_4 ?? 0,

                'final_total' => $order->final_total ?? $order->total ?? 0,

                'expense_category_id' => $order->expense_category_id ?? null,
                'expense_sub_category_id' => $order->expense_sub_category_id ?? null,
                'expense_for' => $order->expense_for ?? null,
                'commission_agent' => $order->commission_agent ?? null,
                'document' => $order->document ?? null,

                'is_direct_sale' => $order->is_direct_sale ?? 1,
                'is_suspend' => $order->is_suspend ?? 0,
                'exchange_rate' => $order->exchange_rate ?? 1.00,
                'total_amount_recovered' => $order->total_amount_recovered ?? null,
                'transfer_parent_id' => $order->transfer_parent_id ?? null,
                'return_parent_id' => $order->return_parent_id ?? null,
                'opening_stock_product_id' => $order->opening_stock_product_id ?? null,

                'created_by' => $authId,
                'purchase_requisition_ids' => $order->purchase_requisition_ids ?? null,
                'prefer_payment_method' => $order->prefer_payment_method ?? ($order->payment_method ?? 'cash'),
                'prefer_payment_account' => $order->prefer_payment_account ?? null,
                'sales_order_ids' => $order->sales_order_ids ?? null,
                'purchase_order_ids' => $order->purchase_order_ids ?? null,

                'custom_field_1' => $order->custom_field_1 ?? null,
                'custom_field_2' => $order->custom_field_2 ?? null,
                'custom_field_3' => $order->custom_field_3 ?? null,
                'custom_field_4' => $order->custom_field_4 ?? null,

                'import_batch' => $order->import_batch ?? null,
                'import_time' => $order->import_time ?? null,

                'types_of_service_id' => $order->types_of_service_id ?? null,
                'packing_charge' => $order->packing_charge ?? 0,
                'packing_charge_type' => $order->packing_charge_type ?? null,

                'service_custom_field_1' => $order->service_custom_field_1 ?? null,
                'service_custom_field_2' => $order->service_custom_field_2 ?? null,
                'service_custom_field_3' => $order->service_custom_field_3 ?? null,
                'service_custom_field_4' => $order->service_custom_field_4 ?? null,
                'service_custom_field_5' => $order->service_custom_field_5 ?? null,
                'service_custom_field_6' => $order->service_custom_field_6 ?? null,

                'is_created_from_api' => $order->is_created_from_api ?? 0,
                'rp_earned' => $order->rp_earned ?? 0,
                'order_addresses' => $order->order_addresses ?? null,

                'is_recurring' => $order->is_recurring ?? 0,
                'recur_interval' => $order->recur_interval ?? null,
                'recur_interval_type' => $order->recur_interval_type ?? null,
                'recur_repetitions' => $order->recur_repetitions ?? null,
                'recur_stopped_on' => $order->recur_stopped_on ?? null,
                'recur_parent_id' => $order->recur_parent_id ?? null,

                'invoice_token' => $order->invoice_token ?? null,
                'pay_term_number' => $order->pay_term_number ?? null,
                'pay_term_type' => $order->pay_term_type ?? null,
                'selling_price_group_id' => $order->selling_price_group_id ?? null,

                'created_at' => $now,
                'updated_at' => $now,
            ];

            \Log::info('Transaction prepared', ['transaction' => $transaction]);

            // Insert transaction
            $transaction_id = DB::table('transactions')->insertGetId($transaction);
            \Log::info('Transaction inserted', ['transaction_id' => $transaction_id]);

            // Insert sell lines
            foreach ($order->order_online_details as $detail) {
                $variation = DB::table('variations')
                    ->where('product_id', $detail->product_id)
                    ->value('id');

                $sellLine = [
                    'transaction_id' => $transaction_id,
                    'product_id' => $detail->product_id,
                    'variation_id' => $variation ?? 0,
                    'quantity' => $detail->qty,
                    'secondary_unit_quantity' => 0,
                    'quantity_returned' => 0,
                    'unit_price_before_discount' => $detail->price_at_order,
                    'unit_price' => $detail->price_at_order,
                    'line_discount_type' => $detail->line_discount_type ?? 'fixed',
                    'line_discount_amount' => $detail->line_discount_amount ?? 0,
                    'unit_price_inc_tax' => $detail->price_at_order + ($detail->tax_amount ?? 0),
                    'item_tax' => $detail->tax_amount ?? 0,
                    'tax_id' => $detail->tax_id ?? null,
                    'discount_id' => null,
                    'lot_no_line_id' => null,
                    'sell_line_note' => $detail->sell_line_note ?? '',
                    'so_line_id' => null,
                    'so_quantity_invoiced' => $detail->qty ?? 0,
                    'res_service_staff_id' => null,
                    'res_line_order_status' => $detail->res_line_order_status ?? null,
                    'parent_sell_line_id' => null,
                    'children_type' => $detail->children_type ?? '',
                    'sub_unit_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                DB::table('transaction_sell_lines')->insert($sellLine);
                \Log::info('Sell line inserted', ['sell_line' => $sellLine]);
            }

            // Insert payment (full)
            $paymentData = [
                'transaction_id' => $transaction_id,
                'business_id' => $business_id,
                'is_return' => 0,
                'amount' => $order->total ?? $transaction['final_total'],
                'method' => $order->payment_method ?? ($transaction['prefer_payment_method'] ?? 'cash'),
                'payment_type' => 'full',
                'transaction_no' => $order->ref_no ?? null,
                'card_transaction_number' => $order->card_transaction_number ?? null,
                'card_number' => $order->card_number ?? null,
                'card_type' => $order->card_type ?? null,
                'card_holder_name' => $order->card_holder_name ?? null,
                'card_month' => $order->card_month ?? null,
                'card_year' => $order->card_year ?? null,
                'card_security' => $order->card_security ?? null,
                'cheque_number' => $order->cheque_number ?? null,
                'bank_account_number' => $order->bank_account_number ?? null,
                'paid_on' => $now,
                'created_by' => $authId,
                'paid_through_link' => 0,
                'gateway' => null,
                'is_advance' => 0,
                'payment_for' => optional($order->api_user->contact)->id,
                'parent_id' => null,
                'note' => $order->payment_note ?? null,
                'document' => null,
                'payment_ref_no' => null,
                'account_id' => $order->account_id ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            DB::table('transaction_payments')->insert($paymentData);
            \Log::info('Payment inserted', ['payment' => $paymentData]);

            // Activity log (with attributes showing important fields)
            $activityProps = [
                'attributes' => [
                    'type' => $transaction['type'],
                    'status' => $transaction['status'],
                    'invoice_no' => $invoice_no,
                    'final_total' => $transaction['final_total'],
                    'shipping_status' => $transaction['shipping_status'],
                ],
                'from_online_order' => $order->id,
            ];

            DB::table('activity_log')->insert([
                'log_name' => 'default',
                'description' => 'added',
                'subject_id' => $transaction_id,
                'subject_type' => 'App\\Transaction',
                'event' => null,
                'business_id' => $business_id,
                'causer_id' => $authId,
                'causer_type' => 'App\\User',
                'properties' => json_encode($activityProps),
                'batch_uuid' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            \Log::info('Activity log inserted', ['subject_id' => $transaction_id, 'properties' => $activityProps]);

            DB::commit();
            \Log::info('create_sale_order_from_online:success', ['transaction_id' => $transaction_id, 'invoice_no' => $invoice_no]);

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction_id,
                'invoice_no' => $invoice_no
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('create_sale_order_from_online:error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}