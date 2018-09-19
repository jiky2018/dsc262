<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_CB', true);
include 'include/header.php';
$default_value['barcodeIdentifier'] = '';
$barcodeIdentifier = (isset($_POST['barcodeIdentifier']) ? $_POST['barcodeIdentifier'] : $default_value['barcodeIdentifier']);
registerImageKey('barcodeIdentifier', $barcodeIdentifier);
$default_value['serviceType'] = '';
$serviceType = (isset($_POST['serviceType']) ? $_POST['serviceType'] : $default_value['serviceType']);
registerImageKey('serviceType', $serviceType);
$default_value['mailerIdentifier'] = '';
$mailerIdentifier = (isset($_POST['mailerIdentifier']) ? $_POST['mailerIdentifier'] : $default_value['mailerIdentifier']);
registerImageKey('mailerIdentifier', $mailerIdentifier);
$default_value['serialNumber'] = '';
$serialNumber = (isset($_POST['serialNumber']) ? $_POST['serialNumber'] : $default_value['serialNumber']);
registerImageKey('serialNumber', $serialNumber);
registerImageKey('code', 'BCGintelligentmail');
$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
echo "\r\n<ul id=\"specificOptions\">\r\n    <li class=\"option\">\r\n        <div class=\"title\">\r\n            <label for=\"barcodeIdentifier\">Barcode Identifier</label>\r\n        </div>\r\n        <div class=\"value\">\r\n            ";
echo getInputTextHtml('barcodeIdentifier', $barcodeIdentifier, array('type' => 'text', 'maxlength' => 2, 'required' => 'required'));
echo "        </div>\r\n    </li>\r\n    <li class=\"option\">\r\n        <div class=\"title\">\r\n            <label for=\"serviceType\">Service Type</label>\r\n        </div>\r\n        <div class=\"value\">\r\n            ";
echo getInputTextHtml('serviceType', $serviceType, array('type' => 'text', 'maxlength' => 3, 'required' => 'required'));
echo "        </div>\r\n    </li>\r\n    <li class=\"option\">\r\n        <div class=\"title\">\r\n            <label for=\"mailerIdentifier\">Mailer Identifier</label>\r\n        </div>\r\n        <div class=\"value\">\r\n            ";
echo getInputTextHtml('mailerIdentifier', $mailerIdentifier, array('type' => 'text', 'maxlength' => 9, 'required' => 'required'));
echo "        </div>\r\n    </li>\r\n    <li class=\"option\">\r\n        <div class=\"title\">\r\n            <label for=\"serialNumber\">Serial Number</label>\r\n        </div>\r\n        <div class=\"value\">\r\n            ";
echo getInputTextHtml('serialNumber', $serialNumber, array('type' => 'text', 'maxlength' => 9, 'required' => 'required'));
echo "        </div>\r\n    </li>\r\n</ul>\r\n\r\n<div id=\"validCharacters\">\r\n    <h3>Valid Characters</h3>\r\n    ";

foreach ($characters as $character) {
	echo getButton($character);
}

echo "</div>\r\n\r\n<div id=\"explanation\">\r\n    <h3>Explanation</h3>\r\n    <ul>\r\n        <li>Used to encode enveloppe in USA.</li>\r\n        <li>\r\n            You can provide\r\n            <br />5 digits (ZIP Code)\r\n            <br />9 digits (ZIP+4 code)\r\n            <br />11 digits (ZIP+4 code+2 digits)\r\n        </li>\r\n        <li>Contains a barcode identifier, service type identifier, mailer id and serial number.</li>\r\n    </ul>\r\n</div>\r\n\r\n<script>\r\n(function(\$) {\r\n    \"use strict\";\r\n\r\n    \$(function() {\r\n        var thickness = \$(\"#thickness\")\r\n            .val(9)\r\n            .removeAttr(\"min step\")\r\n            .prop(\"disabled\", true);\r\n\r\n        \$(\"form\").on(\"submit\", function() {\r\n            thickness.prop(\"disabled\", false);\r\n        });\r\n    });\r\n})(jQuery);\r\n</script>\r\n\r\n";
include 'include/footer.php';

?>
