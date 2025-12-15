<?php
header('Content-Type: image/png');

$img = new Imagick();
$img->newImage(400, 100, new ImagickPixel('white'));
$img->setImageFormat('png');

$draw = new ImagickDraw();
$draw->setFont('/var/www/html/UltimatePOS/public/fonts/khmer/Battambang-Regular.ttf');
$draw->setFontSize(30);
$draw->setFillColor('black');

// Use pango markup
$img->annotateImage($draw, 10, 50, 0, "សួស្តី"); // works better if Imagick compiled with Pango
$img->writeImage('khmer_test.png');
$img->destroy();