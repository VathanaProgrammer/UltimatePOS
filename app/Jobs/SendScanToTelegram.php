<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use App\Transaction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendScanToTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $transactionId,
        public int $deliveryPersonId
    ) {}

    public function handle()
    {
        // Fetch transaction with relations
        $transaction = Transaction::with(['contact', 'location', 'products'])
            ->find($this->transactionId);

        if (!$transaction) {
            \Log::error("Transaction not found for Telegram label", [
                'transaction_id' => $this->transactionId,
                'delivery_person_id' => $this->deliveryPersonId
            ]);
            return;
        }

        // Fetch location
        $localtion = DB::table('business_locations')
            ->where('id', $transaction->location_id)
            ->first();

        // Generate QR
        $qrText = (string) $transaction->id;
        $qrcode = base64_encode(
            \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(400)
                ->errorCorrection('L')
                ->margin(0)
                ->generate($qrText)
        );

        // Generate delivery label image
        $image = TelegramService::generateDeliveryLabelImage(
            $transaction,
            $qrcode,
            $localtion,  // Pass delivery person to overlay text if needed
        );

        // Send to Telegram
        TelegramService::sendScanImageToGroup(
            '-5047451233', // your scan group
            $image['path'],
            "📦 *Scanned*\nInvoice: {$transaction->invoice_no}\nDelivery Person ID: {$this->deliveryPersonId}"
        );

        // Optional: remove file after sending
        @unlink($image['path']);
    }
}