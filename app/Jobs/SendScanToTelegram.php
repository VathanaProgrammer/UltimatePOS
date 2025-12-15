<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
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
        $localtion = DB::table('business_locations')
            ->where('id', $transaction->location_id)
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
            $transaction?->contact,
            $localtion
        );

        TelegramService::sendScanImageToGroup(
            '-5047451233',
            $image['path'],
            "📦 Scanned\nInvoice: {$this->invoiceNo}\n"
        );
    }
}