<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
$generate = isset($_GET['make_pdf']);
$nom = (isset($_GET['nom']) ? $_GET['nom'] : 'inconnu');
$nom = substr(preg_replace('/[^a-zA-Z0-9]/isU', '', $nom), 0, 26);

if ($generate) {
	ob_start();
}
else {
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n<html>\n    <head>\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" >\n        <title>Exemple d'auto génération de PDF</title>\n    </head>\n    <body>\n";
}

$url = dirname($_SERVER['REQUEST_URI']) . '/res/exemple09.png.php?px=5&amp;py=20';

if (substr($url, 0, 7) !== 'http://') {
	$url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
}

echo "<br>\nCeci est un exemple de génération de PDF via un bouton :)<br>\n<br>\n<img src=\"";
echo $url;
echo "\" alt=\"image_php\" ><br>\n<br>\n";

if ($generate) {
	echo 'Bonjour <b>';
	echo $nom;
	echo "</b>, ton nom peut s'écrire : <br>\n<barcode type=\"C39\" value=\"";
	echo strtoupper($nom);
	echo "\" style=\"color: #770000\" ></barcode><hr>\n<br>\n";
}

echo "<br>\n";

if ($generate) {
	$content = ob_get_clean();
	require_once dirname(__FILE__) . '/../html2pdf.class.php';

	try {
		$html2pdf = new HTML2PDF('P', 'A4', 'fr');
		$html2pdf->writeHTML($content);
		$html2pdf->Output('exemple09.pdf');
		exit();
	}
	catch (HTML2PDF_exception $e) {
		echo $e;
		exit();
	}
}

echo "        <form method=\"get\" action=\"\">\n            <input type=\"hidden\" name=\"make_pdf\" value=\"\">\n            Ton nom : <input type=\"text\" name=\"nom\" value=\"\"> -\n            <input type=\"submit\" value=\"Generer le PDF\" >\n        </form>\n    </body>\n</html>";

?>
