<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_CB', true);
include 'include/header.php';
registerImageKey('code', 'BCGupce');
$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
echo "\r\n<div id=\"validCharacters\">\r\n    <h3>Valid Characters</h3>\r\n    ";

foreach ($characters as $character) {
	echo getButton($character);
}

echo "</div>\r\n\r\n<div id=\"explanation\">\r\n    <h3>Explanation</h3>\r\n    <ul>\r\n        <li>Short version of UPC symbol, 8 characters.</li>\r\n        <li>It is a conversion of an UPC-A for small package.</li>\r\n        <li>You can provide directly an UPC-A (11 chars) or UPC-E (6 chars) code.</li>\r\n        <li>UPC-E contain a system number and a check digit.</li>\r\n    </ul>\r\n</div>\r\n\r\n";
include 'include/footer.php';

?>
