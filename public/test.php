<?php
$img = imagecreatetruecolor(200, 80);
$white = imagecolorallocate($img, 255, 255, 255);
$black = imagecolorallocate($img, 0, 0, 0);
imagefill($img, 0, 0, $white);

$font = '/var/www/html/UltimatePOS/public/fonts/khmer/NotoSansKhmer-Regular.ttf';
$text = 'សួស្តី'; // Khmer text

// Check if file exists and readable
if (!file_exists($font)) {
    die("Font file does NOT exist at $font");
}
if (!is_readable($font)) {
    die("Font file is NOT readable by PHP at $font");
}

// Render text
$result = imagettftext($img, 20, 0, 10, 50, $black, $font, $text);
if ($result === false) {
    die("Failed to render text. Check font or GD FreeType support.");
}

imagepng($img, 'test.png');
imagedestroy($img);

echo $text;
echo "Done";