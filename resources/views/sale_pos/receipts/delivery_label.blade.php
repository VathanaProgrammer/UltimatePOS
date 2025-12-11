<!DOCTYPE html>
<html>

<head>
    <title>SOB - {{ $transaction->invoice_no }}</title>
    <meta charset="utf-8">
<style>
    @page {
        margin: 0;
        size: 50mm 50mm; /* match small label */
    }

    body {
        margin: 0;
        padding: 0;
        width: 400px;
        font-family: Arial, sans-serif;
        font-size: 13px;
        display: flex;
        justify-content: center;
    }

    .label {
        width: 400px;
        padding: 10px;
    }

    .top-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .sender-info {
        width: 90px;
        line-height: 1.3;
    }

    .qr-box img {
        width: 70px;
        height: 70px;
    }

    .receiver-info {
        margin-top: 6px;
        line-height: 1.3;
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
