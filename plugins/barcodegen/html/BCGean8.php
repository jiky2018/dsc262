<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_CB', true);
include 'include/header.php';
registerImageKey('code', 'BCGean8');
$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
echo "\r\n<div id=\"validCharacters\">\r\n    <h3>Valid Characters</h3>\r\n    ";

foreach ($characters as $character) {
	echo getButton($character);
}

echo "</div>\r\n\r\n<div id=\"explanation\">\r\n    <h3>Explanation</h3>\r\n    <ul>\r\n        <li>EAN-8 is a short version of EAN-13.</li>\r\n        <li>Composed by 7 digits and 1 check digit.</li>\r\n        <li>There is no conversion available between EAN-8 and EAN-13.</li>\r\n    </ul>\r\n</div>\r\n\r\n";
include 'include/footer.php';

?>
