<!DOCTYPE html>
<html>

<head>
    <title>SOB - {{ $transaction->invoice_no }}</title>
    <meta charset="utf-8">

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .label {
            padding: 5px;
            width: 250px;
            margin: 0 auto;
        }

        /* Top row: sender info + QR on right */
        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .sender-info {
            font-size: 11px;
            line-height: 1.2;
            max-width: 150px;
        }

        .qr-box img {
            width: 60px;
            height: 60px;
        }

        .customer-info {
            margin-top: 10px;
            line-height: 1.3;
        }

        .customer-info div {
            margin-bottom: 2px;
        }
    </style>
</head>

<body>
    <div class="label">

        <!-- Top row -->
        <div class="top-row">
            <div class="sender-info">
                <strong>Sender: SOB</strong><br>
                Mobile: {{ $localtion->mobile ?? '0123456789' }}<br>
                Date: {{ \Carbon\Carbon::now()->format('d/m/Y H:iA') }}
            </div>

            <div class="qr-box">
                <img src="data:image/png;base64,{{ $qrcode }}" alt="QR Code">
            </div>
        </div>

        <!-- Receiver info section -->
        <div class="customer-info">
            <div><strong>Receiver:</strong> {{ $transaction->contact?->name ?? '-' }}</div>
            <div><strong>Mobile:</strong> {{ $transaction->contact?->mobile ?? '-' }}</div>
            <div><strong>Address:</strong> {{ $transaction->contact?->address ?? '-' }}</div>
        </div>

    </div>
</body>

</html>
