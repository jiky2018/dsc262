<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
require_once dirname(__FILE__) . '/../html2pdf.class.php';
ob_start();
include dirname('__FILE__') . '/res/about.php';
$content = ob_get_clean();

try {
	$html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', array(0, 0, 0, 0));
	$html2pdf->pdf->SetDisplayMode('fullpage');
	$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
	$html2pdf->createIndex('Sommaire', 30, 12, false, true, 2);
	$html2pdf->Output('about.pdf');
}
catch (HTML2PDF_exception $e) {
	echo $e;
	exit();
}

?>
