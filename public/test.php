<?php
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Imagick;
use ImagickDraw;
use ImagickPixel;


$khmerFont = realpath(public_path('fonts/khmer/NotoSansKhmer-Regular.ttf'));
$text = "សួស្ដី"; // Khmer text

$draw = new ImagickDraw();
$draw->setFont($khmerFont);
$draw->setFontSize(14);
$draw->setFillColor(new ImagickPixel('black'));
$draw->setGravity(Imagick::GRAVITY_NORTHWEST);

$img->annotateImage($draw, 10, 20, 0, $text);
$draw->destroy();