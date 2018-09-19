<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_CB', true);
include 'include/header.php';
$default_value['checksum'] = '';
$checksum = (isset($_POST['checksum']) ? $_POST['checksum'] : $default_value['checksum']);
registerImageKey('checksum', $checksum);
registerImageKey('code', 'BCGi25');
$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
echo "\r\n<ul id=\"specificOptions\">\r\n    <li class=\"option\">\r\n        <div class=\"title\">\r\n            <label for=\"checksum\">Checksum</label>\r\n        </div>\r\n        <div class=\"value\">\r\n            ";
echo getCheckboxHtml('checksum', $checksum, array('value' => 1));
echo "        </div>\r\n    </li>\r\n</ul>\r\n\r\n<div id=\"validCharacters\">\r\n    <h3>Valid Characters</h3>\r\n    ";

foreach ($characters as $character) {
	echo getButton($character);
}

echo "</div>\r\n\r\n<div id=\"explanation\">\r\n    <h3>Explanation</h3>\r\n    <ul>\r\n        <li>Interleaved 2 of 5 is based on Standard 2 of 5 symbology.</li>\r\n        <li>There is an optional checksum.</li>\r\n    </ul>\r\n</div>\r\n\r\n";
include 'include/footer.php';

?>
