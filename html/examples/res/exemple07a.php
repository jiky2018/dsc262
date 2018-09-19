<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
echo "<style type=\"text/css\">\n<!--\ntable { vertical-align: top; }\ntr    { vertical-align: top; }\ntd    { vertical-align: top; }\n}\n-->\n</style>\n<page backcolor=\"#FEFEFE\" backimg=\"./res/bas_page.png\" backimgx=\"center\" backimgy=\"bottom\" backimgw=\"100%\" backtop=\"0\" backbottom=\"30mm\" footer=\"date;heure;page\" style=\"font-size: 12pt\">\n    <bookmark title=\"Lettre\" level=\"0\" ></bookmark>\n    <table cellspacing=\"0\" style=\"width: 100%; text-align: center; font-size: 14px\">\n        <tr>\n            <td style=\"width: 75%;\">\n            </td>\n            <td style=\"width: 25%; color: #444444;\">\n                <img style=\"width: 100%;\" src=\"./res/logo.gif\" alt=\"Logo\"><br>\n                RELATION CLIENT\n            </td>\n        </tr>\n    </table>\n    <br>\n    <br>\n    <table cellspacing=\"0\" style=\"width: 100%; text-align: left; font-size: 11pt;\">\n        <tr>\n            <td style=\"width:50%;\"></td>\n            <td style=\"width:14%; \">Client :</td>\n            <td style=\"width:36%\">M. Albert Dupont</td>\n        </tr>\n        <tr>\n            <td style=\"width:50%;\"></td>\n            <td style=\"width:14%; \">Adresse :</td>\n            <td style=\"width:36%\">\n                Résidence perdue<br>\n                1, rue sans nom<br>\n                00 000 - Pas de Ville<br>\n            </td>\n        </tr>\n        <tr>\n            <td style=\"width:50%;\"></td>\n            <td style=\"width:14%; \">Email :</td>\n            <td style=\"width:36%\">nomail@domain.com</td>\n        </tr>\n        <tr>\n            <td style=\"width:50%;\"></td>\n            <td style=\"width:14%; \">Tel :</td>\n            <td style=\"width:36%\">33 (0) 1 00 00 00 00</td>\n        </tr>\n    </table>\n    <br>\n    <br>\n    <table cellspacing=\"0\" style=\"width: 100%; text-align: left;font-size: 10pt\">\n        <tr>\n            <td style=\"width:50%;\"></td>\n            <td style=\"width:50%; \">Spipu Ville, le ";
echo date('d/m/Y');
echo "</td>\n        </tr>\n    </table>\n    <br>\n    <i>\n        <b><u>Objet </u>: &laquo; Bon de Retour &raquo;</b><br>\n        Compte client : 00C4520100A<br>\n        Référence du Dossier : 71326<br>\n    </i>\n    <br>\n    <br>\n    Madame, Monsieur, Cher Client,<br>\n    <br>\n    <br>\n    Nous souhaitons vous informer que le dossier <b>71326</b> concernant un &laquo; Bon de Retour &raquo; pour les articles suivants a été accepté.<br>\n    <br>\n    <table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;\">\n        <tr>\n            <th style=\"width: 12%\">Produit</th>\n            <th style=\"width: 52%\">Désignation</th>\n            <th style=\"width: 13%\">Prix Unitaire</th>\n            <th style=\"width: 10%\">Quantité</th>\n            <th style=\"width: 13%\">Prix Net</th>\n        </tr>\n    </table>\n";
$nb = rand(5, 11);
$produits = array();
$total = 0;

for ($k = 0; $k < $nb; $k++) {
	$num = rand(100000, 999999);
	$nom = 'le produit n°' . rand(1, 100);
	$qua = rand(1, 20);
	$prix = rand(100, 9999) / 100;
	$total += $prix * $qua;
	$produits[] = array($num, $nom, $qua, $prix, rand(0, $qua));
	echo "    <table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; background: #F7F7F7; text-align: center; font-size: 10pt;\">\n        <tr>\n            <td style=\"width: 12%; text-align: left\">";
	echo $num;
	echo "</td>\n            <td style=\"width: 52%; text-align: left\">";
	echo $nom;
	echo "</td>\n            <td style=\"width: 13%; text-align: right\">";
	echo number_format($prix, 2, ',', ' ');
	echo " &euro;</td>\n            <td style=\"width: 10%\">";
	echo $qua;
	echo "</td>\n            <td style=\"width: 13%; text-align: right;\">";
	echo number_format($prix * $qua, 2, ',', ' ');
	echo " &euro;</td>\n        </tr>\n    </table>\n";
}

echo "    <table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;\">\n        <tr>\n            <th style=\"width: 87%; text-align: right;\">Total : </th>\n            <th style=\"width: 13%; text-align: right;\">";
echo number_format($total, 2, ',', ' ');
echo " &euro;</th>\n        </tr>\n    </table>\n    <br>\n    Cette reprise concerne la quantité et les matériels dont la référence figure sur le <a href=\"#document_reprise\">document de reprise joint</a>.<br>\n    Nous vous demandons de nous retourner ces produits en parfait état et dans leur emballage d'origine.<br>\n    <br>\n    Nous vous demandons également de coller impérativement l'autorisation de reprise jointe, sur le colis à reprendre afin de faciliter le traitement à l'entrepôt.<br>\n    <br>\n    Notre Service Clients ne manquera pas de revenir vers vous dès que l'avoir de ces matériels sera établi.<br>\n    <nobreak>\n        <br>\n        Dans cette attente, nous vous prions de recevoir, Madame, Monsieur, Cher Client, nos meilleures salutations.<br>\n        <br>\n        <table cellspacing=\"0\" style=\"width: 100%; text-align: left;\">\n            <tr>\n                <td style=\"width:50%;\"></td>\n                <td style=\"width:50%; \">\n                    Mle Jesuis CELIBATAIRE<br>\n                    Service Relation Client<br>\n                    Tel : 33 (0) 1 00 00 00 00<br>\n                    Email : on_va@chez.moi<br>\n                </td>\n            </tr>\n        </table>\n    </nobreak>\n</page>";

?>
