<?php
// Full working scan image generator

require __DIR__ . '/vendor/autoload.php'; // Make sure Composer autoload is included
use SimpleSoftwareIO\QrCode\Facades\QrCode;

// Example data
$invoiceNo = 'INV12345';
$deliveryPersonId = 1;
$contact = (object)[
    'name' => 'សៀង វឌ្ឍនា',
    'mobile' => '0123456789',
    'address_line_1' => 'Phnom Penh',
    'address_line_2' => 'Cambodia'
];
$location = (object)[
    'mobile' => '0987654321'
];

// Image settings
$imgWidth = 350;
$imgHeight = 250;
$img = imagecreatetruecolor($imgWidth, $imgHeight);

// Colors
$white = imagecolorallocate($img, 255, 255, 255);
$black = imagecolorallocate($img, 0, 0, 0);
imagefill($img, 0, 0, $white);

// Font paths
$khmerFont = __DIR__ . '/public/fonts/khmer/NotoSansKhmer-Regular.ttf';
$latinFont = __DIR__ . '/public/fonts/latin/NotoSans-Regular.ttf';

// Helper function to draw text with Khmer detection
function drawText($img, $text, $x, $y, $size = 12) {
    global $khmerFont, $latinFont;
    $firstChar = mb_substr($text, 0, 1, 'UTF-8');
    $font = preg_match('/[\x{1780}-\x{17FF}]/u', $firstChar) ? $khmerFont : $latinFont;
    imagettftext($img, $size, 0, $x, $y, imagecolorallocate($img, 0, 0, 0), $font, $text);
}

// 🟢 DRAW TEXT
drawText($img, "SOB", 10, 20, 14);
drawText($img, "Mobile: {$location->mobile}", 10, 40, 12);
drawText($img, date('d/m/Y H:iA'), 10, 60, 12);

drawText($img, "SCAN CONFIRMED", 10, 90, 14);
drawText($img, "Invoice: {$invoiceNo}", 10, 110, 12);
drawText($img, "Delivery ID: {$deliveryPersonId}", 10, 130, 12);

drawText($img, "Receiver: {$contact->name}", 10, 160, 12);
drawText($img, "Mobile: {$contact->mobile}", 10, 180, 12);
$receiverAddress = $contact->address_line_1 . ', ' . $contact->address_line_2;
drawText($img, "Address: {$receiverAddress}", 10, 200, 12);

// 🟢 GENERATE QR CODE
$qrFile = __DIR__ . '/qr_tmp.png';
QrCode::format('png')->size(120)->margin(0)->generate($invoiceNo, $qrFile);
$qrImg = imagecreatefrompng($qrFile);
imagecopy($img, $qrImg, $imgWidth - 130, 10, 0, 0, imagesx($qrImg), imagesy($qrImg));
imagedestroy($qrImg);
unlink($qrFile);

// 🟢 OUTPUT IMAGE TO BROWSER
header('Content-Type: image/png');
imagepng($img);
imagedestroy($img);
exit;