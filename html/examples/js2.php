<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
ob_start();
echo "<page>\n    <h1>Test de JavaScript 2</h1><br>\n    <br>\n    Normalement une alerte devrait apparaitre, indiquant \"coucou\"\n</page>\n";
$content = ob_get_clean();
require_once dirname(__FILE__) . '/../html2pdf.class.php';

try {
	$html2pdf = new HTML2PDF('P', 'A4', 'fr');
	$html2pdf->pdf->IncludeJS('app.alert(\'coucou\');');
	$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
	$html2pdf->Output('js2.pdf');
}
catch (HTML2PDF_exception $e) {
	echo $e;
	exit();
}

?>
