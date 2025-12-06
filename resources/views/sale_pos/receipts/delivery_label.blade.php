<!DOCTYPE html>
<html>

<head>
    <title>SOB - {{ $transaction->invoice_no }}</title>
    <meta charset="utf-8">
    <!-- Optional Bootstrap for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
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

        .header {
            font-weight: bold;
            text-align: center;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .company-info, .customer-info {
            margin-bottom: 4px;
        }

        .company-info div, .customer-info .row {
            margin-bottom: 1px;
        }

        .qr-code {
            text-align: center;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <div class="label">
        <div class="header">
            <strong>Sender: SOB</strong><br>
            Mobile: {{ $localtion->mobile ?? '0123456789' }}<br>
            Date: {{ \Carbon\Carbon::now()->format('d/m/Y H:iA') }}
        </div>

        <div class="qr-code">
            {!! QrCode::size(400)->generate(\Illuminate\Support\Facades\Crypt::encryptString($transaction->id)) !!}
        </div>

        <div class="customer-info">
            <div class="row"><strong>Receiver:</strong> {{ $transaction->contact?->name ?? '-' }}</div>
            <div class="row"><strong>Mobile:</strong> {{ $transaction->contact?->mobile ?? '-' }}</div>
            <div class="row"><strong>Address:</strong> {{ $transaction->contact?->address ?? '-' }}</div>
        </div>
    </div>
</body>

</html>
