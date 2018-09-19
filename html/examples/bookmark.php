<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
ob_start();
echo "<style type=\"text/css\">\n<!--\n    table.page_header {width: 100%; border: none; background-color: #DDDDFF; border-bottom: solid 1mm #AAAADD; padding: 2mm }\n    table.page_footer {width: 100%; border: none; background-color: #DDDDFF; border-top: solid 1mm #AAAADD; padding: 2mm}\n    h1 {color: #000033}\n    h2 {color: #000055}\n    h3 {color: #000077}\n\n    div.niveau\n    {\n        padding-left: 5mm;\n    }\n-->\n</style>\n<page backtop=\"14mm\" backbottom=\"14mm\" backleft=\"10mm\" backright=\"10mm\" style=\"font-size: 12pt\">\n    <page_header>\n        <table class=\"page_header\">\n            <tr>\n                <td style=\"width: 100%; text-align: left\">\n                    Exemple d'utilisation des bookmarks\n                </td>\n            </tr>\n        </table>\n    </page_header>\n    <page_footer>\n        <table class=\"page_footer\">\n            <tr>\n                <td style=\"width: 100%; text-align: right\">\n                    page [[page_cu]]/[[page_nb]]\n                </td>\n            </tr>\n        </table>\n    </page_footer>\n    <bookmark title=\"Sommaire\" level=\"0\" ></bookmark>\n</page>\n<page pageset=\"old\">\n    <bookmark title=\"Chapitre 1\" level=\"0\" ></bookmark><h1>Chapitre 1</h1>\n    <div class=\"niveau\">\n        Contenu du chapitre 1\n    </div>\n</page>\n<page pageset=\"old\">\n    <bookmark title=\"Chapitre 2\" level=\"0\" ></bookmark><h1>Chapitre 2</h1>\n    <div class=\"niveau\">\n        intro au chapitre 2\n        <bookmark title=\"Chapitre 2.1\" level=\"1\" ></bookmark><h2>Chapitre 2.1</h2>\n        <div class=\"niveau\">\n            Contenu du chapitre 2.1\n        </div>\n        <bookmark title=\"Chapitre 2.2\" level=\"1\" ></bookmark><h2>Chapitre 2.2</h2>\n        <div class=\"niveau\">\n            Contenu du chapitre 2.2\n        </div>\n        <bookmark title=\"Chapitre 2.3\" level=\"1\" ></bookmark><h2>Chapitre 2.3</h2>\n        <div class=\"niveau\">\n            Contenu du chapitre 2.3\n        </div>\n    </div>\n</page>\n<page pageset=\"old\">\n    <bookmark title=\"Chapitre 3\" level=\"0\" ></bookmark><h1>Chapitre 3</h1>\n    <div class=\"niveau\">\n        intro au chapitre 3\n        <bookmark title=\"Chapitre 3.1\" level=\"1\" ></bookmark><h2>Chapitre 3.1</h2>\n        <div class=\"niveau\">\n            Contenu du chapitre 3.1\n        </div>\n        <bookmark title=\"Chapitre 3.2\" level=\"1\" ></bookmark><h2>Chapitre 3.2</h2>\n        <div class=\"niveau\">\n            intro au chapitre 3.2\n            <bookmark title=\"Chapitre 3.2.1\" level=\"2\" ></bookmark><h3>Chapitre 3.2.1</h3>\n            <div class=\"niveau\">\n                Contenu du chapitre 3.2.1\n            </div>\n            <bookmark title=\"Chapitre 3.2.2\" level=\"2\" ></bookmark><h3>Chapitre 3.2.2</h3>\n            <div class=\"niveau\">\n                Contenu du chapitre 3.2.2\n            </div>\n        </div>\n    </div>\n</page>\n";
$content = ob_get_clean();
require_once dirname(__FILE__) . '/../html2pdf.class.php';

try {
	$html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
	$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
	$html2pdf->createIndex('Sommaire', 25, 12, false, true, 1);
	$html2pdf->Output('bookmark.pdf');
}
catch (HTML2PDF_exception $e) {
	echo $e;
	exit();
}

?>
