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
            width: 165px;
            padding: 3px;
        }

        .top-row {
            display: flex;
            justify-content: flex-start;
            gap: 5px;
            align-items: flex-start;
        }

        .sender-info {
            width: 70px;
            line-height: 1.2;
        }

        .qr-box img {
            width: 50px;
            height: 50px;
        }

        .receiver-info {
            margin-top: 5px;
            line-height: 1.1;
        }

        .receiver-info span {
            font-weight: normal;
            display: inline; /* inline so name & mobile stay on same line */
            word-wrap: break-word;
            max-width: 140px;
        }

        .receiver-info .line {
            display: block; /* each line block except the first line */
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
            <span>
                <strong>Receiver:</strong> {{ $transaction->contact?->name ?? '-' }} &nbsp;|&nbsp;
                {{ $transaction->contact?->mobile ?? '-' }}
            </span>
            <span class="line">
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
                <span style="font-weight: 700;">{{ $address }}</span>
            </span>
        </div>
    </div>
</body>

</html>
