<?php
header('Content-Type: image/png');
$khmerText = 'សួស្តី';
$englishText = 'Hello';

$svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="100">
  <style>
    @font-face { font-family: Battambang; src: url('fonts/khmer/Battambang-Regular.ttf'); }
    @font-face { font-family: NotoSans; src: url('fonts/latin/NotoSans-Regular.ttf'); }
    .kh { font-family: Battambang; font-size: 24px; }
    .en { font-family: NotoSans; font-size: 24px; }
  </style>
  <text x="10" y="40" class="kh">$khmerText</text>
  <text x="10" y="80" class="en">$englishText</text>
</svg>
SVG;

$img = new Imagick();
$img->readImageBlob($svg);
$img->setImageFormat('png');
$img->writeImage('test.png');
$img->destroy();