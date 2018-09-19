<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_CB', true);
include 'include/header.php';
$default_value['checksum'] = '';
$checksum = (isset($_POST['checksum']) ? $_POST['checksum'] : $default_value['checksum']);
registerImageKey('checksum', $checksum);
registerImageKey('code', 'BCGcode39extended');
$vals = array();

for ($i = 0; $i <= 127; $i++) {
	$vals[] = '%' . sprintf('%02X', $i);
}

$characters = array('NUL', 'SOH', 'STX', 'ETX', 'EOT', 'ENQ', 'ACK', 'BEL', 'BS', 'TAB', 'LF', 'VT', 'FF', 'CR', 'SO', 'SI', 'DLE', 'DC1', 'DC2', 'DC3', 'DC4', 'NAK', 'SYN', 'ETB', 'CAN', 'EM', 'SUB', 'ESC', 'FS', 'GS', 'RS', 'US', '&nbsp;', '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '<', '=', '>', '?', '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', '\\', ']', '^', '_', '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '{', '|', '}', '~', 'DEL');
echo "\r\n<ul id=\"specificOptions\">\r\n    <li class=\"option\">\r\n        <div class=\"title\">\r\n            <label for=\"checksum\">Checksum</label>\r\n        </div>\r\n        <div class=\"value\">\r\n            ";
echo getCheckboxHtml('checksum', $checksum, array('value' => 1));
echo "        </div>\r\n    </li>\r\n</ul>\r\n\r\n<div id=\"validCharacters\">\r\n    <h3>Valid Characters</h3>\r\n    ";
$c = count($characters);

for ($i = 0; $i < $c; $i++) {
	echo getButton($characters[$i], $vals[$i]);
}

echo "</div>\r\n\r\n<div id=\"explanation\">\r\n    <h3>Explanation</h3>\r\n    <ul>\r\n        <li>Supports the ASCII 0 to 127.</li>\r\n        <li>This mode is \"optional\" for Code 39, you have to specify your reader that you have extended code.</li>\r\n        <li>Your browser may not be able to write the special characters (NUL, SOH, etc.) but you can write them with the code.</li>\r\n    </ul>\r\n</div>\r\n\r\n";
include 'include/footer.php';

?>
