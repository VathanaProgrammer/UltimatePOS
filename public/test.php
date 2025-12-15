<?php
header('Content-Type: image/png');

$img = new Imagick();
$img->newImage(400, 100, new ImagickPixel('white'));
$img->setImageFormat('png');

$draw = new ImagickDraw();

// Khmer text
$khmerText = 'សួស្តី';
$khmerFont = '/var/www/html/UltimatePOS/public/fonts/khmer/Battambang-Regular.ttf';
$draw->setFont($khmerFont);
$draw->setFontSize(24);
$draw->setFillColor(new ImagickPixel('black'));
$img->annotateImage($draw, 10, 40, 0, $khmerText);

// English text
$englishText = 'Hello';
$englishFont = '/var/www/html/UltimatePOS/public/fonts/latin/NotoSans-Regular.ttf';
$draw->setFont($englishFont);
$draw->setFontSize(24);
$img->annotateImage($draw, 10, 80, 0, $englishText);

echo $img->getImageBlob();
$img->destroy();