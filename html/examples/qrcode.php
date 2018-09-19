<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
ob_start();
$msg = "Le site de html2pdf\r\nhttp://html2pdf.fr/";
echo "<page backtop=\"10mm\" >\n    <page_header>\n        <table style=\"width: 100%; border: solid 1px black;\">\n            <tr>\n                <td style=\"text-align: left;    width: 50%\">html2pdf</td>\n                <td style=\"text-align: right;    width: 50%\">Exemples de QRcode</td>\n            </tr>\n        </table>\n    </page_header>\n    <h1>Exemples de QRcode</h1>\n    <h3>Message avec Correction d'erreur L, M, Q, H (valeur par défaut : H)</h3>\n    <qrcode value=\"";
echo $msg;
echo "\" ec=\"L\" style=\"width: 30mm;\"></qrcode>\n    <qrcode value=\"";
echo $msg;
echo "\" ec=\"M\" style=\"width: 30mm;\"></qrcode>\n    <qrcode value=\"";
echo $msg;
echo "\" ec=\"Q\" style=\"width: 30mm;\"></qrcode>\n    <qrcode value=\"";
echo $msg;
echo "\" ec=\"H\" style=\"width: 30mm;\"></qrcode>\n    <br>\n    <h3>Message avec différentes largeurs</h3>\n    <qrcode value=\"";
echo $msg;
echo "\" style=\"width: 20mm;\"></qrcode>\n    <qrcode value=\"";
echo $msg;
echo "\" style=\"width: 30mm;\"></qrcode>\n    <qrcode value=\"";
echo $msg;
echo "\" style=\"width: 40mm;\"></qrcode>\n    <qrcode value=\"";
echo $msg;
echo "\" style=\"width: 50mm;\"></qrcode>\n    <br>\n    <h3>Message de différentes couleurs</h3>\n    <qrcode value=\"";
echo $msg;
echo "\" style=\"width: 40mm; background-color: white; color: black;\"></qrcode>\n    <qrcode value=\"";
echo $msg;
echo "\" style=\"width: 40mm; background-color: yellow; color: red\"></qrcode>\n    <qrcode value=\"";
echo $msg;
echo "\" style=\"width: 40mm; background-color: #FFCCFF; color: #003300\"></qrcode>\n    <qrcode value=\"";
echo $msg;
echo "\" style=\"width: 40mm; background-color: #CCFFFF; color: #003333\"></qrcode>\n    <br>\n    <h3>Message sans border</h3>\n    <qrcode value=\"";
echo $msg;
echo "\" style=\"border: none; width: 40mm;\"></qrcode>\n    <br>\n</page>\n";
$content = ob_get_clean();
require_once dirname(__FILE__) . '/../html2pdf.class.php';

try {
	$html2pdf = new HTML2PDF('P', 'A4', 'fr');
	$html2pdf->pdf->SetDisplayMode('fullpage');
	$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
	$html2pdf->Output('qrcode.pdf');
}
catch (HTML2PDF_exception $e) {
	echo $e;
	exit();
}

?>
