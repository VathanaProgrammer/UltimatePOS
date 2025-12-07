<!DOCTYPE html>
<html>

<head>
    <title>SOB - {{ $transaction->invoice_no }}</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 7px;
        }

        /* Top: sender info on left, QR on right */
        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .sender-info {
            width: 60px; /* leave room for QR */
            line-height: 1.1;
        }

        .qr-box img {
            width: 35px;
            height: 35px;
        }

        .receiver-info {
            margin-top: 4px;
            line-height: 1.1;
        }
    </style>
</head>

<body>
    <div class="label">

        <!-- Top Section -->
        <div class="top-row">
            <div class="sender-info">
                <strong>SOB</strong><br>
                Mobile: {{ $localtion->mobile ?? '0123456789' }}<br>
                {{ \Carbon\Carbon::now()->format('d/m/Y H:iA') }}
            </div>

            <div class="qr-box">
                <img src="data:image/png;base64,{{ $qrcode }}" alt="QR">
            </div>
        </div>

        <!-- Bottom: Receiver -->
        <div class="receiver-info">
            <strong>Receiver:</strong> {{ $transaction->contact?->name ?? '-' }}<br>
            <strong>Mobile:</strong> {{ $transaction->contact?->mobile ?? '-' }}<br>
            <strong>Address:</strong> {{ $transaction->contact?->address ?? '-' }}
        </div>

    </div>
</body>

</html>
