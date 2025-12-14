<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendScanToTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $invoiceNo,
        public int $deliveryPersonId
    ) {}

    public function handle()
    {
        $image = TelegramService::generateScanImage(
            $this->invoiceNo,
            $this->deliveryPersonId
        );

        // 👇 THIS GROUP ONLY (YOUR SCAN GROUP)
        TelegramService::sendScanImageToGroup(
            '-5047451233', // SCAN GROUP ID
            $image['path'],
            "📦 *Scanned*\nInvoice: {$this->invoiceNo}"
        );

        @unlink($image['path']);
    }
}