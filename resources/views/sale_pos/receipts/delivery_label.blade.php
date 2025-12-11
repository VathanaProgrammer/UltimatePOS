<!DOCTYPE html>
<html>
<head>
    <title>SOB - {{ $transaction->invoice_no }}</title>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0;
            size: 58mm auto; /* width of the thermal roll, height adjusts automatically */
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
        }

        .label {
            width: 100%; /* fill full printable width */
            padding: 5px;
            box-sizing: border-box;
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .sender-info {
            width: 65%; /* take most space for text */
            font-size: 16px;
            line-height: 1.4;
        }

        .qr-box img {
            width: 30%; /* QR takes remaining space proportionally */
            height: auto;
            max-height: 100px; /* limit height */
        }

        .receiver-info {
            margin-top: 10px;
            font-size: 16px;
            line-height: 1.4;
            width: 100%;
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
