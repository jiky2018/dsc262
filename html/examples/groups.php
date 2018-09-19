<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
ob_start();
echo "<style type=\"text/css\">\n<!--\n    table.page_header {width: 100%; border: none; background-color: #DDDDFF; border-bottom: solid 1mm #AAAADD; padding: 2mm }\n    table.page_footer {width: 100%; border: none; background-color: #DDDDFF; border-top: solid 1mm #AAAADD; padding: 2mm}\n-->\n</style>\n<page backtop=\"14mm\" backbottom=\"14mm\" backleft=\"10mm\" backright=\"10mm\" pagegroup=\"new\">\n    <page_header>\n        <table class=\"page_header\">\n            <tr>\n                <td style=\"width: 100%; text-align: left\">\n                    Exemple d'utilisation des groupes de pages\n                </td>\n            </tr>\n        </table>\n    </page_header>\n    <page_footer>\n        <table class=\"page_footer\">\n            <tr>\n                <td style=\"width: 100%; text-align: right\">\n                    page [[page_cu]]/[[page_nb]]\n                </td>\n            </tr>\n        </table>\n    </page_footer>\n    Ceci est la page 1 du groupe 1\n</page>\n<page pageset=\"old\">\n    Ceci est la page 2 du groupe 1\n</page>\n<page pageset=\"old\">\n    Ceci est la page 3 du groupe 1\n</page>\n";

for ($k = 2; $k < 5; $k++) {
	echo "<page pageset=\"old\" pagegroup=\"new\">\n    Ceci est la page 1 du groupe ";
	echo $k;
	echo "</page>\n<page pageset=\"old\">\n    Ceci est la page 2 du groupe ";
	echo $k;
	echo "</page>\n";
}

$content = ob_get_clean();
require_once dirname(__FILE__) . '/../html2pdf.class.php';

try {
	$html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
	$html2pdf->pdf->SetDisplayMode('fullpage');
	$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
	$html2pdf->Output('groups.pdf');
}
catch (HTML2PDF_exception $e) {
	echo $e;
	exit();
}

?>
