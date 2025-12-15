<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Transaction;

class SendScanToTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $invoiceNo,
        public int $deliveryPersonId
    ) {}

    public function handle()
    {
           $transaction = Transaction::with(['contact', 'location'])
        ->where('invoice_no', $this->invoiceNo)
        ->first();

    if (!$transaction) {
        \Log::error('Transaction not found', [
            'invoice' => $this->invoiceNo
        ]);
        return;
    }

    $image = TelegramService::generateScanImage(
        $this->invoiceNo,
        $this->deliveryPersonId,
        $transaction->contact,                 // 👈 OBJECT
        $transaction->location?->mobile ?? '-'  // 👈 STRING
    );

    TelegramService::sendScanImageToGroup(
        '-5047451233',
        $image['path'],
        "📦 Scanned\nInvoice: {$this->invoiceNo}\n}"
    );
    }
}