<?php
$font = '/var/www/html/UltimatePOS/public/fonts/khmer/Battambang-Regular.ttf';
$text = 'សួស្តី';

// Create image
$im = imagecreatetruecolor(400, 100);
$white = imagecolorallocate($im, 255, 255, 255);
$black = imagecolorallocate($im, 0, 0, 0);
imagefill($im, 0, 0, $white);

// Render text (requires PHP >=8.1 with HarfBuzz support)
imagettftext($im, 24, 0, 10, 50, $black, $font, $text);

header('Content-Type: image/png');
imagepng($im);
imagedestroy($im);