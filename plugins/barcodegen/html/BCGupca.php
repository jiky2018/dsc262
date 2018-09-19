<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_CB', true);
include 'include/header.php';
registerImageKey('code', 'BCGupca');
$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
echo "\r\n<div id=\"validCharacters\">\r\n    <h3>Valid Characters</h3>\r\n    ";

foreach ($characters as $character) {
	echo getButton($character);
}

echo "</div>\r\n\r\n<div id=\"explanation\">\r\n    <h3>Explanation</h3>\r\n    <ul>\r\n        <li>Encoded as EAN-13.</li>\r\n        <li>Most common and well-known in the USA.</li>\r\n        <li>There is 1 number system (NS), 5 manufacturer code, 5 product code and 1 check digit.</li>\r\n        <li>\r\n            NS Description :\r\n            <br />0 = Regular UPC Code\r\n            <br />2 = Weight Items\r\n            <br />3 = Drug/Health Items\r\n            <br />4 = In-Store Use on Non-Food Items\r\n            <br />5 = Coupons\r\n            <br />7 = Regular UPC Code\r\n            <br />And other are Reserved.\r\n        </li>\r\n    </ul>\r\n</div>\r\n\r\n";
include 'include/footer.php';

?>
