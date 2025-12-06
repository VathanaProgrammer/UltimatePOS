<!DOCTYPE html>
<html>

<head>
    <title>SOB - {{ $transaction->invoice_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px; /* smaller font */
            margin: 0;
            padding: 0;
        }

        .label {
            padding: 5px;
            width: 150px; /* narrower label */
            margin: 0 auto;
        }

        .header {
            font-weight: bold;
            text-align: center;
            font-size: 12px; /* smaller header */
            margin-bottom: 4px;
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
            border-bottom: 1px solid #000;
        }

        .company-info {
            display: flex;
            flex-direction: column;
        }

        .company-info div {
            margin-bottom: 1px;
        }

        .barcode-box img {
            width: 70px; /* smaller barcode */
            height: auto;
        }

        .customer-info {
            margin-top: 4px;
        }

        .customer-info .row {
            margin-bottom: 1px;
        }
    </style>
</head>

<body>
    <div class="label">
        <!-- Top row: company info + barcode -->
        <div class="top-row">
            <div class="company-info">
                <div><strong>Sender:</strong> SOB</div>
                <div><strong>Mobile:</strong> {{ $localtion->mobile ?? '0123456789' }}</div>
                <div><strong>Date:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:iA') }}</div>
            </div>
            <div class="barcode-box">
                @if (!empty($qrcode))
                    <img src="data:image/png;base64,{{ $qrcode }}" alt="barcode" />
                    <div style="text-align:right; font-size: 9px; margin-top: 1px;">
                    </div>
                @else
                    <span>Loading…</span>
                @endif
            </div>
        </div>

        <!-- Customer info at bottom -->
        <div class="customer-info">
            <div class="row"><strong>Receiver:</strong> {{ $transaction->contact?->name ?? '-' }}</div>
            <div class="row"><strong>Mobile:</strong> {{ $transaction->contact?->mobile ?? '-' }}</div>
            <div class="row"><strong>Address:</strong> {{ $transaction->contact?->address ?? '-' }}</div>
        </div>
    </div>
</body>

</html>
