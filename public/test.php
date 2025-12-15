<?php
header('Content-Type: image/png');

$img = imagecreatetruecolor(400, 100);
$white = imagecolorallocate($img, 255, 255, 255);
$black = imagecolorallocate($img, 0, 0, 0);
imagefill($img, 0, 0, $white);

$font = __DIR__ . '/fonts/khmer/NotoSansKhmer-Regular.ttf';
$text = 'សួស្តី';

if (!file_exists($font)) die("Font not found: $font");

imagettftext($img, 20, 0, 10, 50, $black, $font, $text);

imagepng($img);
imagedestroy($img);