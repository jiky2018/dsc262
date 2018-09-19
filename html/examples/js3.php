<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
ob_start();
echo "<page>\n    <h1>Test de JavaScript 3</h1><br>\n    <br>\n    Normalement une valeur devrait vous être demandée, puis affichée\n</page>\n";
$content = ob_get_clean();
$script = "\nvar rep = app.response('Donnez votre nom');\napp.alert('Vous vous appelez '+rep);\n";
require_once dirname(__FILE__) . '/../html2pdf.class.php';

try {
	$html2pdf = new HTML2PDF('P', 'A4', 'fr');
	$html2pdf->pdf->IncludeJS($script);
	$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
	$html2pdf->Output('js3.pdf');
}
catch (HTML2PDF_exception $e) {
	echo $e;
	exit();
}

?>
