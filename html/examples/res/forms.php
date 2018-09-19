<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
$url = $_SERVER['REQUEST_URI'];

if (substr($url, 0, 7) !== 'http://') {
	$url = 'http://' . $_SERVER['HTTP_HOST'];
	if (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] != 80)) {
		$url .= ':' . $_SERVER['SERVER_PORT'];
	}

	$url .= $_SERVER['REQUEST_URI'];
}

echo "<style type=\"text/css\">\nli\n{ font-size: 10pt; }\n\ninput, textarea, select\n{\n    border: dashed 1mm red;\n    background: #FCC;\n    color: #400;\n    text-align: left;\n    font-size: 11pt;\n}\n</style>\n<page footer=\"form\">\n    <h1>Test de formulaire</h1><br>\n    <br>\n    <form action=\"";
echo $url;
echo "\">\n        <input type=\"hidden\" name=\"test\" value=\"1\">\n        Vous utilisez cette librairie dans le cadre :\n        <ul style=\"list-style: none\">\n            <li><input type=\"checkbox\" name=\"cadre_boulot\" checked=\"checked\"> du boulot</li>\n            <li><input type=\"checkbox\" name=\"cadre_perso\" > perso</li>\n        </ul>\n        Vous êtes :\n        <ul style=\"list-style: none\">\n            <li><input type=\"radio\" name=\"sexe\" value=\"homme\" checked=\"checked\"> un homme</li>\n            <li><input type=\"radio\" name=\"sexe\" value=\"femme\"> une femme</li>\n        </ul>\n        Vous avez :\n        <select name=\"age\" >\n            <option value=\"15\">moins de 15 ans</option>\n            <option value=\"20\">entre 15 et 20 ans</option>\n            <option value=\"25\">entre 20 et 25 ans</option>\n            <option value=\"30\">entre 25 et 30 ans</option>\n            <option value=\"40\">plus de 30 ans</option>\n        </select><br>\n        <br>\n        Vous aimez :\n        <select name=\"aime[]\" size=\"5\" multiple=\"multiple\">\n            <option value=\"ch1\">l'informatique</option>\n            <option value=\"ch2\">le cinéma</option>\n            <option value=\"ch3\">le sport</option>\n            <option value=\"ch4\">la littérature</option>\n            <option value=\"ch5\">autre</option>\n        </select><br>\n        <br>\n        Votre phrase fétiche : <input type=\"text\" name=\"phrase\" value=\"cette lib est géniale !!!\" style=\"width: 100mm\"><br>\n        <br>\n        Un commentaire ?<br>\n        <textarea name=\"comment\" rows=\"3\" cols=\"30\">rien de particulier</textarea><br>\n        <br>\n        <input type=\"reset\" name=\"btn_reset\" value=\"Initialiser\">\n        <input type=\"button\" name=\"btn_print\" value=\"Imprimer\" onclick=\"print(true);\">\n        <input type=\"submit\" name=\"btn_submit\" value=\"Envoyer\">\n    </form>\n</page>";

?>
