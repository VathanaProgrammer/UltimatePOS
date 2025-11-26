<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transaction;
use App\TransactionSellLine;
use Illuminate\Support\Facades\Log;

class TransactionDetails extends Controller
{

    public function getTransactionDetails($id)
    {
        try {
            $transaction = Transaction::with(['contact'])->findOrFail($id);

            $products = TransactionSellLine::with(['product'])
                ->where('transaction_id', $id)
                ->get()
                ->map(function ($line) {
                    return [
                        'product_name' => $line->product->name ?? '',
                        'unit_price' => $line->unit_price,
                        'quantity' => $line->quantity,
                        'total_line' => $line->unit_price * $line->quantity,
                        'image' => $line->product->image ?? '',
                    ];
                });

            return response()->json([
                'invoice_no' => $transaction->invoice_no,
                'date' => $transaction->transaction_date,
                'customer_name' => $transaction->contact->name ?? '',
                'total' => $transaction->final_total,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            // Log the error message and stack trace
            Log::error('Error in getTransactionDetails: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            // Return JSON error for the frontend
            return response()->json([
                'error' => 'Something went wrong while fetching transaction details.'
            ], 500);
        }
    }
}