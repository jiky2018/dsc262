<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
$px = (isset($_GET['px']) ? $_GET['px'] : 0);
$px = preg_replace('/[^0-9]/isU', '', $px);
$py = (isset($_GET['py']) ? $_GET['py'] : 0);
$py = preg_replace('/[^0-9]/isU', '', $py);

if ($px < 1) {
	$px = 5;
}

if ($py < 1) {
	$py = 5;
}

if (20 < $px) {
	$px = 20;
}

if (20 < $py) {
	$py = 20;
}

$width = 100;
$height = 100;
$im = imagecreatetruecolor($width, $height);

for ($y = 0; $y < $height; $y += $py) {
	for ($x = 0; $x < $width; $x += $px) {
		$c = imagecolorallocate($im, 200 - $x, 100 + $y, (100 + $x) - $y);
		imagefilledrectangle($im, $x, $y, $x + $px, $y + $py, $c);
	}
}

header('Content-type: image/png');
imagepng($im);
imagedestroy($im);

?>
