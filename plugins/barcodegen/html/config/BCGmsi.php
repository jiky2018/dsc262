<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function customSetup($barcode, $get)
{
	if (isset($get['checksum'])) {
		$barcode->setChecksum($get['checksum'] === '1' ? true : false);
	}
}

$classFile = 'BCGmsi.barcode.php';
$className = 'BCGmsi';
$baseClassFile = 'BCGBarcode1D.php';
$codeVersion = '5.2.0';

?>
