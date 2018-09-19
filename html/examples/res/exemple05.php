<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
echo "<style type=\"text/css\">\n<!--\ntable\n{\n    width:  100%;\n    border: solid 1px #5544DD;\n}\n\nth\n{\n    text-align: center;\n    border: solid 1px #113300;\n    background: #EEFFEE;\n}\n\ntd\n{\n    text-align: left;\n    border: solid 1px #55DD44;\n}\n\ntd.col1\n{\n    border: solid 1px red;\n    text-align: right;\n}\n\n-->\n</style>\n<span style=\"font-size: 20px; font-weight: bold\">Démonstration des retour à la ligne automatique, ainsi que des sauts de page automatique<br></span>\n<br>\n<br>\n<table>\n    <col style=\"width: 5%\" class=\"col1\">\n    <col style=\"width: 25%\">\n    <col style=\"width: 30%\">\n    <col style=\"width: 40%\">\n    <thead>\n        <tr>\n            <th rowspan=\"2\">n°</th>\n            <th colspan=\"3\" style=\"font-size: 16px;\">\n                Titre du tableau\n            </th>\n        </tr>\n        <tr>\n            <th>Colonne 1</th>\n            <th>Colonne 2</th>\n            <th>Colonne 3</th>\n        </tr>\n    </thead>\n";

for ($k = 0; $k < 50; $k++) {
	echo "    <tr>\n        <td>";
	echo $k;
	echo "</td>\n        <td>test de texte assez long pour engendrer des retours à la ligne automatique...</td>\n        <td>test de texte assez long pour engendrer des retours à la ligne automatique...</td>\n        <td>test de texte assez long pour engendrer des retours à la ligne automatique...</td>\n    </tr>\n";
}

echo "    <tfoot>\n        <tr>\n            <th colspan=\"4\" style=\"font-size: 16px;\">\n                bas du tableau\n            </th>\n        </tr>\n    </tfoot>\n</table>\nCool non ?<br>";

?>
