<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_CB', true);
include 'include/header.php';
registerImageKey('code', 'BCGisbn');
$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
echo "\r\n<div id=\"validCharacters\">\r\n    <h3>Valid Characters</h3>\r\n    ";

foreach ($characters as $character) {
	echo getButton($character);
}

echo "</div>\r\n\r\n<div id=\"explanation\">\r\n    <h3>Explanation</h3>\r\n    <ul>\r\n        <li>ISBN stands for International Standard Book Number.</li>\r\n        <li>ISBN type is based on EAN-13.</li>\r\n        <li>Previously, all ISBN were in EAN-10 format. EAN-13 uses the same encoding but may contain different data in the ISBN number.</li>\r\n        <li>Composed by a GS1 prefix (for ISBN-13), a group identifier, a publisher code, an item number and a check digit.</li>\r\n    </ul>\r\n</div>\r\n\r\n";
include 'include/footer.php';

?>
