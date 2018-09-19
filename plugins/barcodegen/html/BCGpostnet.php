<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_CB', true);
include 'include/header.php';
registerImageKey('code', 'BCGpostnet');
$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
echo "\r\n<div id=\"validCharacters\">\r\n    <h3>Valid Characters</h3>\r\n    ";

foreach ($characters as $character) {
	echo getButton($character);
}

echo "</div>\r\n\r\n<div id=\"explanation\">\r\n    <h3>Explanation</h3>\r\n    <ul>\r\n        <li>Used to encode enveloppe in USA.</li>\r\n        <li>\r\n            You can provide\r\n            <br />5 digits (ZIP Code)\r\n            <br />9 digits (ZIP+4 code)\r\n            <br />11 digits (ZIP+4 code+2 digits)\r\n            <br />(Those 2 digits are taken from your address. If your address is 6453, the code will be 53.)\r\n        </li>\r\n    </ul>\r\n</div>\r\n\r\n<script>\r\n(function(\$) {\r\n    \"use strict\";\r\n\r\n    \$(function() {\r\n        var thickness = \$(\"#thickness\")\r\n            .val(9)\r\n            .removeAttr(\"min step\")\r\n            .prop(\"disabled\", true);\r\n\r\n        \$(\"form\").on(\"submit\", function() {\r\n            thickness.prop(\"disabled\", false);\r\n        });\r\n    });\r\n})(jQuery);\r\n</script>\r\n\r\n";
include 'include/footer.php';

?>
