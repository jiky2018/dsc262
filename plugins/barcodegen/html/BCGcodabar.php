<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_CB', true);
include 'include/header.php';
registerImageKey('code', 'BCGcodabar');
$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-', '$', ':', '/', '.', '+', 'A', 'B', 'C', 'D');
echo "\r\n<div id=\"validCharacters\">\r\n    <h3>Valid Characters</h3>\r\n    ";

foreach ($characters as $character) {
	echo getButton($character);
}

echo "</div>\r\n\r\n<div id=\"explanation\">\r\n    <h3>Explanation</h3>\r\n    <ul>\r\n        <li>Known also as Ames Code, NW-7, Monarch, 2 of 7, Rationalized Codabar.</li>\r\n        <li>Codabar was developed in 1972 by Pitney Bowes, Inc.</li>\r\n        <li>This symbology is useful to encode digital information. It is a self-checking code, there is no check digit.</li>\r\n        <li>Codabar is used by blood bank, photo labs, library, FedEx...</li>\r\n        <li>Coding can be with an unspecified length composed by numbers, plus and minus sign, colon, slash, dot, dollar.</li>\r\n    </ul>\r\n</div>\r\n\r\n";
include 'include/footer.php';

?>
