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
            text-align: center;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .section-title {
            font-weight: bold;
            margin-top: 10px;
            text-decoration: underline;
        }

        .row {
            margin-bottom: 5px;
        }

        /* New layout: left details + right barcode */
        .content-box {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .info-box {
            width: 65%;
        }

        .barcode-box {
            width: 35%;
            text-align: right;
        }

        .barcode-box img {
            width: 120px; /* Smaller barcode */
            height: auto;
        }
    </style>
</head>

<body>
    <div class="label">

        <div class="content-box">

            <!-- LEFT SIDE: SENDER + RECEIVER INFO -->
            <div class="info-box">

                <!-- Sender Info -->
                <div class="row"><strong>Sender:</strong> SOB</div>
                <div class="row"><strong>Mobile:</strong> {{ $localtion->mobile ?? '0123456789' }}</div>

                <!-- Receiver Info -->
                <div class="row"><strong>Receiver:</strong> {{ $transaction->contact?->name ?? '-' }}</div>
                <div class="row"><strong>Mobile:</strong> {{ $transaction->contact?->mobile ?? '-' }}</div>
                <div class="row"><strong>Address:</strong> {{ $transaction->contact?->address ?? '-' }}</div>

            </div>

            <!-- RIGHT SIDE: BARCODE -->
            <div class="barcode-box">
                @if (!empty($barcode))
                    <img src="data:image/png;base64,{{ $barcode }}" alt="barcode" />
                    <div style="margin-top:5px; text-align:right;">
                        <strong>{{ $transaction->invoice_no }}</strong>
                    </div>
                @else
                    <span>Loading…</span>
                @endif
            </div>

        </div>

    </div>
</body>

</html>
