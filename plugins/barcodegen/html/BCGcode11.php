<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_CB', true);
include 'include/header.php';
registerImageKey('code', 'BCGcode11');
$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-');
echo "\r\n<div id=\"validCharacters\">\r\n    <h3>Valid Characters</h3>\r\n    ";

foreach ($characters as $character) {
	echo getButton($character);
}

echo "</div>\r\n\r\n<div id=\"explanation\">\r\n    <h3>Explanation</h3>\r\n    <ul>\r\n        <li>Known also as USD-8.</li>\r\n        <li>Code 11 was developed in 1977 as a high-density numeric symbology.</li>\r\n        <li>Used to identify telecommunications equipment.</li>\r\n        <li>Code 11 is a numeric symbology and its character set consists of 10 digital characters and the dash symbol (-).</li>\r\n        <li>There is a \"C\" check digit. If the length of encoded message is greater thant 10, a \"K\" check digit may be used.</li>\r\n    </ul>\r\n</div>\r\n\r\n";
include 'include/footer.php';

?>
