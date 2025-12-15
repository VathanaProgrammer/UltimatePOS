<?php
$img = imagecreatetruecolor(200, 80);
$white = imagecolorallocate($img, 255, 255, 255);
$black = imagecolorallocate($img, 0, 0, 0);
imagefill($img, 0, 0, $white);

$font = '/var/www/html/UltimatePOS/public/fonts/khmer/NotoSansKhmer-Regular.ttf';
$text = 'សួស្តី'; // Some Khmer text

imagettftext($img, 20, 0, 10, 50, $black, $font, $text);
imagepng($img, 'test.png');
imagedestroy($img);
echo "Done";