<!DOCTYPE html>
<html>

<head>
    <title>SOB - {{ $transaction->invoice_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .label {
            padding: 5px;
            width: 150px;
            margin: 0 auto;
        }

        .header {
            font-weight: bold;
            text-align: center;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .company-info,
        .customer-info,
        .qr-code {
            margin-bottom: 4px;
        }

        .company-info div,
        .customer-info .row {
            margin-bottom: 1px;
        }

        .qr-code {
            text-align: center;
            margin-top: 6px;
            border-top: 1px solid #000;
            padding-top: 4px;
        }

        .qr-code svg,
        .qr-code img {
            width: 80px;
            height: 80px;
        }
    </style>
</head>

<body>
    <div class="label">
        <!-- Company Info on top -->
        <div class="company-info">
            <div><strong>Sender:</strong> SOB</div>
            <div><strong>Mobile:</strong> {{ $localtion->mobile ?? '0123456789' }}</div>
            <div><strong>Date:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:iA') }}</div>
        </div>

        <!-- Customer Info -->
        <div class="customer-info">
            <div class="row"><strong>Receiver:</strong> {{ $transaction->contact?->name ?? '-' }}</div>
            <div class="row"><strong>Mobile:</strong> {{ $transaction->contact?->mobile ?? '-' }}</div>
            <div class="row"><strong>Address:</strong> {{ $transaction->contact?->address ?? '-' }}</div>
        </div>

        <!-- QR Code at the bottom -->
        <div class="qr-code">
            @if (!empty($qrcode))
                {!! $qrcode !!}
            @else
                <span>Loading…</span>
            @endif
        </div>
    </div>
</body>

</html>
