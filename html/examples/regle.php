<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
ob_start();
echo "<style type=\"text/css\">\n<!--\n    table\n    {\n        padding: 0;\n        margin: 0;\n        border: none;\n        border-right: solid 0.2mm black;\n    }\n    td\n    {\n        padding: 0;\n        margin: 0;\n        border: none;\n    }\n\n    img\n    {\n        width: 10mm;\n    }\n-->\n</style>\n<page>\n<table cellpadding=\"0\" cellspacing=\"0\"><tr>\n";

for ($k = 0; $k < 28; $k++) {
	echo '<td><img src="./res/regle.png" alt="" ><br>' . $k . '</td>';
}

echo "</tr></table>\n</page>\n";
$content = ob_get_clean();
require_once dirname(__FILE__) . '/../html2pdf.class.php';

try {
	$html2pdf = new HTML2PDF('L', 'A4', 'fr', true, 'UTF-8', 10);
	$html2pdf->pdf->SetDisplayMode('fullpage');
	$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
	$html2pdf->Output('regle.pdf');
}
catch (HTML2PDF_exception $e) {
	echo $e;
	exit();
}

?>
