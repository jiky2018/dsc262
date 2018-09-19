<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function customSetup($barcode, $get)
{
	if (isset($get['start'])) {
		$barcode->setStart($get['start'] === 'NULL' ? NULL : $get['start']);
	}
}

$classFile = 'BCGcode128.barcode.php';
$className = 'BCGcode128';
$baseClassFile = 'BCGBarcode1D.php';
$codeVersion = '5.2.0';

?>
