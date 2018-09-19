<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_CB', true);
include 'include/header.php';
registerImageKey('code', 'BCGupcext5');
$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
echo "\r\n<div id=\"validCharacters\">\r\n    <h3>Valid Characters</h3>\r\n    ";

foreach ($characters as $character) {
	echo getButton($character);
}

echo "</div>\r\n\r\n<div id=\"explanation\">\r\n    <h3>Explanation</h3>\r\n    <ul>\r\n        <li>Extension for UPC-A, UPC-E, EAN-13 and EAN-8.</li>\r\n        <li>Used to encode suggested retail price.</li>\r\n        <li>If the first number is a 0, the price xx.xx is expressed in British Pounds. If it is a 5, it is expressed in US dollars.</li>\r\n        <li>\r\n            Special Code Description:\r\n            <br />90000: No suggested retail price\r\n            <br />99991: The item is a complementary of another one. Normally free\r\n            <br />99990: Used bh National Association of College Stores to mark \"used book\"\r\n            <br />90001 to 98999: Internal purposes for some publishers\r\n        </li>\r\n    </ul>\r\n</div>\r\n\r\n";
include 'include/footer.php';

?>
