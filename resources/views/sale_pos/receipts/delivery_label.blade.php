<!DOCTYPE html>
<html>

<head>
    <title>SOB - {{ $transaction->invoice_no }}</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            display: flex;
            justify-content: center;
        }

        .label {
            width: 130px; /* SLIGHTLY BIGGER */
            padding: 3px;
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .sender-info {
            width: 80px; /* more space for text */
            line-height: 1.2;
        }

        .qr-box img {
            width: 55px; /* bigger QR */
            height: 55px;
        }

        .receiver-info {
            margin-top: 5px;
            line-height: 1.2;
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
            <strong>Address:</strong> {{ $transaction->contact?->address_line_1 ?? '-' }}
        </div>
    </div>
</body>

</html>
