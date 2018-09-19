<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
ob_start();
$num = 'CMD01-' . date('ymd');
$nom = 'DUPONT Alphonse';
$date = '01/01/2012';
echo "<style type=\"text/css\">\n<!--\n    div.zone { border: none; border-radius: 6mm; background: #FFFFFF; border-collapse: collapse; padding:3mm; font-size: 2.7mm;}\n    h1 { padding: 0; margin: 0; color: #DD0000; font-size: 7mm; }\n    h2 { padding: 0; margin: 0; color: #222222; font-size: 5mm; position: relative; }\n-->\n</style>\n<page format=\"100x200\" orientation=\"L\" backcolor=\"#AAAACC\" style=\"font: arial;\">\n    <div style=\"rotate: 90; position: absolute; width: 100mm; height: 4mm; left: 195mm; top: 0; font-style: italic; font-weight: normal; text-align: center; font-size: 2.5mm;\">\n        Ceci est votre e-ticket à présenter au contrôle d'accès -\n        billet généré par <a href=\"http://html2pdf.fr/\" style=\"color: #222222; text-decoration: none;\">html2pdf</a>\n    </div>\n    <table style=\"width: 99%;border: none;\" cellspacing=\"4mm\" cellpadding=\"0\">\n        <tr>\n            <td colspan=\"2\" style=\"width: 100%\">\n                <div class=\"zone\" style=\"height: 34mm;position: relative;font-size: 5mm;\">\n                    <div style=\"position: absolute; right: 3mm; top: 3mm; text-align: right; font-size: 4mm; \">\n                        <b>";
echo $nom;
echo "</b><br>\n                    </div>\n                    <div style=\"position: absolute; right: 3mm; bottom: 3mm; text-align: right; font-size: 4mm; \">\n                        <b>1</b> place <b>plein tarif</b><br>\n                        Prix unitaire TTC : <b>45,00&euro;</b><br>\n                        N° commande : <b>";
echo $num;
echo "</b><br>\n                        Date d'achat : <b>";
echo date('d/m/Y à H:i:s');
echo "</b><br>\n                    </div>\n                    <h1>Billet soirée spécial HTML2PDF</h1>\n                    &nbsp;&nbsp;&nbsp;&nbsp;<b>Valable le ";
echo $date;
echo " à 20h30</b><br>\n                    <img src=\"./res/logo.gif\" alt=\"logo\" style=\"margin-top: 3mm; margin-left: 20mm\">\n                </div>\n            </td>\n        </tr>\n        <tr>\n            <td style=\"width: 25%;\">\n                <div class=\"zone\" style=\"height: 40mm;vertical-align: middle;text-align: center;\">\n                    <qrcode value=\"";
echo $num . "\n" . $nom . "\n" . $date;
echo "\" ec=\"Q\" style=\"width: 37mm; border: none;\" ></qrcode>\n                </div>\n            </td>\n            <td style=\"width: 75%\">\n                <div class=\"zone\" style=\"height: 40mm;vertical-align: middle; text-align: justify\">\n                    <b>Conditions d'utilisation du billet</b><br>\n                    Le billet est soumis aux conditions générales de vente que vous avez\n                    acceptées avant l'achat du billet. Le billet d'entrée est uniquement\n                    valable s'il est imprimé sur du papier A4 blanc, vierge recto et verso.\n                    L'entrée est soumise au contrôle de la validité de votre billet. Une bonne\n                    qualité d'impression est nécessaire. Les billets partiellement imprimés,\n                    souillés, endommagés ou illisibles ne seront pas acceptés et seront\n                    considérés comme non valables. En cas d'incident ou de mauvaise qualité\n                    d'impression, vous devez imprimer à nouveau votre fichier. Pour vérifier\n                    la bonne qualité de l'impression, assurez-vous que les informations écrites\n                    sur le billet, ainsi que les pictogrammes (code à barres 2D) sont bien\n                    lisibles. Ce titre doit être conservé jusqu'à la fin de la manifestation.\n                    Une pièce d'identité pourra être demandée conjointement à ce billet. En\n                    cas de non respect de l'ensemble des règles précisées ci-dessus, ce billet\n                    sera considéré comme non valable.<br>\n                    <br>\n                    <i>Ce billet est reconnu électroniquement lors de votre\n                    arrivée sur site. A ce titre, il ne doit être ni dupliqué, ni photocopié.\n                    Toute reproduction est frauduleuse et inutile.</i>\n                </div>\n            </td>\n        </tr>\n    </table>\n</page>\n";
$content = ob_get_clean();
require_once dirname(__FILE__) . '/../html2pdf.class.php';

try {
	$html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
	$html2pdf->pdf->SetDisplayMode('fullpage');
	$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
	$html2pdf->Output('ticket.pdf');
}
catch (HTML2PDF_exception $e) {
	echo $e;
	exit();
}

?>
