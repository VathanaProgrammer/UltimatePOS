<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transaction;
use App\TransactionSellLine;

class TransactionDetails extends Controller
{
    //
    public function getTransactionDetails($id)
    {
        $transaction = Transaction::with(['contact'])->findOrFail($id);

        $products = TransactionSellLine::with(['product', 'variation'])
            ->where('transaction_id', $id)
            ->get()
            ->map(function ($line) {
                return [
                    'product_name' => $line->product->name ?? '',
                    'variation_name' => $line->variation->name ?? '',
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
    }
}