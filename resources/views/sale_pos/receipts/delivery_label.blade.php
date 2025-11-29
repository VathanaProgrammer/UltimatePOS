<!DOCTYPE html>
<html>

<head>
    <title>SOB - {{ $transaction->invoice_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
        }

        .label {
            padding: 15px;
            width: 450px;
            margin: 0 auto;
        }

        .header {
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
            font-size: 18px;
        }

        .footer {
            margin-top: 10px;
            font-size: 14px;
        }

        .footer div {
            margin-bottom: 4px;
        }

        .barcode {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="label">
        <!-- Header -->
        <div class="header">SOB</div>

        <!-- Footer details -->
        <div class="footer">
            <div><strong>Date:</strong> {{ $transaction->created_at->format('d/m/Y h:i A') }}</div>
            <div><strong>Phone:</strong> {{ $transaction->contact?->mobile ?? '-' }}</div>
            <div><strong>Address:</strong> {{ $transaction->contact?->address ?? '-' }}</div>
        </div>
        <div class="barcode" style="text-align:center; margin-top:10px;">
            @if (!empty($barcode))
                <img src="data:image/png;base64,{{ $barcode }}" alt="barcode" />
            @else
                <span>Barcode loading…</span>
            @endif
        </div>

    </div>
</body>

</html>
