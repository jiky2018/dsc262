<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
echo "<style type=\"text/css\">\n<!--\ntable.tableau { text-align: left; }\ntable.tableau td { width: 15mm; font-family: courier; }\ntable.tableau th { width: 15mm; font-family: courier; }\n\n.ul1\n{\n    list-style-image: url(./res/puce2.gif);\n}\n.ul1 li\n{\n    color:#F19031;\n}\n.ul2\n{\n    list-style: square;\n}\n.ul2 li\n{\n    color:#31F190;\n}\n.ul3\n{\n    list-style: none;\n}\n.ul3 li\n{\n    color:#9031F1;\n}\n-->\n</style>\nExemple de liste avec puce personnalisée :<br>\n<table style=\"width: 100%;\" >\n    <tr>\n        <td style=\"width: 33%;\">\n            <ul class=\"ul1\">\n                <li>Votre ligne 1</li>\n                <li>Votre ligne 2</li>\n                <li>Votre ligne 3</li>\n            </ul>\n        </td>\n        <td style=\"width: 34%;\">\n            <ul class=\"ul2\">\n                <li>Votre ligne 1</li>\n                <li>Votre ligne 2</li>\n                <li>Votre ligne 3</li>\n            </ul>\n        </td>\n        <td style=\"width: 33%;\">\n            <ul class=\"ul3\">\n                <li>Votre ligne 1</li>\n                <li>Votre ligne 2</li>\n                <li>Votre ligne 3</li>\n            </ul>\n        </td>\n    </tr>\n</table>\nExemple de caracteres :<br>\n<table class=\"tableau\" >\n    <tr><th>0</th><th>a</th><th>e</th><th>i</th><th>o</th><th>u</th></tr>\n    <tr><th>1</th><td>&agrave;</td><td>&egrave;</td><td>&igrave;</td><td>&ograve;</td><td>&ugrave;</td></tr>\n    <tr><th>2</th><td>&aacute;</td><td>&eacute;</td><td>&iacute;</td><td>&oacute;</td><td>&uacute;</td></tr>\n    <tr><th>3</th><td>&acirc;</td><td>&ecirc;</td><td>&icirc;</td><td>&ocirc;</td><td>&ucirc;</td></tr>\n    <tr><th>4</th><td>&auml;</td><td>&euml;</td><td>&iuml;</td><td>&ouml;</td><td>&uuml;</td></tr>\n    <tr><th>5</th><td>&atilde;</td><td> </td><td> </td><td>&otilde;</td><td> </td></tr>\n    <tr><th>6</th><td>&aring;</td><td> </td><td> </td><td> </td><td> </td></tr>\n    <tr><th>7</th><td>&euro;</td><td>&laquo;</td><td> </td><td>&oslash;</td><td> </td></tr>\n</table>\n<br>\n";
$phrase = 'ceci est un exemple avec <b>du gras</b>, ';
$phrase .= '<i>de l\'italique</i>, ';
$phrase .= '<u>du souligné</u>, ';
$phrase .= '<u><i><b>et une image</b></i></u> : ';
$phrase .= '<img src=\'./res/logo.gif\' alt=\'logo\' style=\'width: 15mm\'>';
echo "Table :<br>\n<table style=\"border: solid 1px red; width: 105mm\">\n    <tr><td style=\"width: 100%; border: solid 1px green; text-align: left; \">";
echo $phrase;
echo "</td></tr>\n    <tr><td style=\"width: 100%; border: solid 1px green; text-align: center;\">";
echo $phrase;
echo "</td></tr>\n    <tr><td style=\"width: 100%; border: solid 1px green; text-align: right; \">";
echo $phrase;
echo "</td></tr>\n</table>\n<br>\nDiv :<br>\n<div style=\"width: 103mm; border: solid 1px green; text-align: left; margin: 1mm 0 1mm 0;padding: 1mm;\">";
echo $phrase;
echo "</div>\n<div style=\"width: 103mm; border: solid 1px green; text-align: center;margin: 1mm 0 1mm 0;padding: 1mm;\">";
echo $phrase;
echo "</div>\n<div style=\"width: 103mm; border: solid 1px green; text-align: right; margin: 1mm 0 1mm 0;padding: 1mm;\">";
echo $phrase;
echo '</div>';

?>
