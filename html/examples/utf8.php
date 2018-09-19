<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
require_once dirname(__FILE__) . '/../html2pdf.class.php';
$content = file_get_contents(dirname(__FILE__) . '/../_tcpdf_' . HTML2PDF_USED_TCPDF_VERSION . '/cache/utf8test.txt');
$content = '<page style="font-family: freeserif"><br />' . nl2br($content) . '</page>';

try {
	$html2pdf = new HTML2PDF('P', 'A4', 'fr');
	$html2pdf->pdf->SetDisplayMode('real');
	$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
	$html2pdf->Output('utf8.pdf');
}
catch (HTML2PDF_exception $e) {
	echo $e;
	exit();
}

?>
