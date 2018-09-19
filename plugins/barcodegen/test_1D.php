<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
require_once 'class/BCGFontFile.php';
require_once 'class/BCGColor.php';
require_once 'class/BCGDrawing.php';
require_once 'class/BCGcode39.barcode.php';
$font = new BCGFontFile('./font/Arial.ttf', 18);
$text = (isset($_GET['text']) ? $_GET['text'] : 'HELLO');
$color_black = new BCGColor(0, 0, 0);
$color_white = new BCGColor(255, 255, 255);
$drawException = NULL;

try {
	$code = new BCGcode39();
	$code->setScale(2);
	$code->setThickness(30);
	$code->setForegroundColor($color_black);
	$code->setBackgroundColor($color_white);
	$code->setFont($font);
	$code->parse($text);
}
catch (Exception $exception) {
	$drawException = $exception;
}

$drawing = new BCGDrawing('', $color_white);

if ($drawException) {
	$drawing->drawException($drawException);
}
else {
	$drawing->setBarcode($code);
	$drawing->draw();
}

header('Content-Type: image/png');
header('Content-Disposition: inline; filename="barcode.png"');
$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);

?>
