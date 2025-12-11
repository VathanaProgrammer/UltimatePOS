<!DOCTYPE html>
<html>
<head>
    <title>SOB - {{ $transaction->invoice_no }}</title>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0;
            size: 58mm auto; /* full roll width */
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
        }

        .label {
            width: 100%;
            padding: 2px;
            box-sizing: border-box;
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .sender-info {
            width: calc(100% - 250px - 4px); /* bigger QR, adjust text width */
            font-size: 35px; /* big text */
            line-height: 1.4;
        }

        .qr-box img {
            width: 250px; /* bigger QR as requested */
            height: 250px;
        }

        .receiver-info {
            margin-top: 12px;
            font-size: 35px; /* bigger text */
            line-height: 1.5;
            width: 100%;
        }

        strong {
            font-size: 24px; /* bold headings */
        }
    </style>
</head>
<body>
    <div class="label">
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

        <div class="receiver-info">
            <strong>Receiver:</strong> {{ $transaction->contact?->name ?? '-' }}<br>
            <strong>Mobile:</strong> {{ $transaction->contact?->mobile ?? '-' }}<br>
            <strong>Address:</strong> {{ $transaction->contact?->address ?? '-' }}
        </div>
    </div>
</body>
</html>
