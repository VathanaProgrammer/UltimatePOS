<!DOCTYPE html>
<html>

<head>
    <title>SOB - {{ $transaction->invoice_no }}</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9.5px;
            display: flex;
            justify-content: center;
        }

        .label {
            width: 145px;
            /* SLIGHTLY BIGGER */
            padding: 3px;
        }

        .top-row {
            display: flex;
            justify-content: flex-start;
            /* items stay close together */
            gap: 5px;
            /* add a small gap between sender & QR */
            align-items: flex-start;
        }

        .sender-info {
            width: 80px;
            /* more space for text */
            line-height: 1.2;
        }

        .qr-box img {
            width: 55px;
            /* bigger QR */
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
            <strong>Receiver:</strong> <span
                style="font-weight: normal;">{{ $transaction->contact?->name ?? '-' }}</span><br>
            <strong>Mobile:</strong> <span
                style="font-weight: normal;">{{ $transaction->contact?->mobile ?? '-' }}</span><br>
            <strong>Address:</strong>
            @php
                $address = '-';
                if (!empty($transaction->shipping_address)) {
                    $address = $transaction->shipping_address;
                } elseif ($transaction->contact) {
                    $line1 = $transaction->contact->address_line_1 ?? '';
                    $line2 = $transaction->contact->address_line_2 ?? '';
                    if ($line1 && $line2) {
                        $address = $line1 . ', ' . $line2;
                    } else {
                        $address = $line1 ?: ($line2 ?: '-');
                    }
                }
            @endphp
            <span style="font-weight: normal;">{{ $address }}</span>

        </div>
    </div>
</body>

</html>
