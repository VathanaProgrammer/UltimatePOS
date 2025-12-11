<!DOCTYPE html>
<html>

<head>
    <title>SOB - {{ $transaction->invoice_no }}</title>
    <meta charset="utf-8">
<style>
    @page {
        margin: 0;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        font-size: 9px; /* was 8px */
        display: flex;
        justify-content: center;
    }

    .label {
        width: 175px;
        padding: 3px;
    }

    .top-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .sender-info {
        width: 85px; /* tiny bump for text growth */
        line-height: 1.25; /* slightly more breathing room */
    }

    .qr-box img {
        width: 62px; /* scaled up just a bit */
        height: 62px;
    }

    .receiver-info {
        margin-top: 6px;
        line-height: 1.25;
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
