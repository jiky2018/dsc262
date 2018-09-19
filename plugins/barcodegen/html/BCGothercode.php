<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_CB', true);
include 'include/header.php';
$default_value['label'] = '';
$label = (isset($_POST['label']) ? $_POST['label'] : $default_value['label']);
registerImageKey('label', $label);
registerImageKey('code', 'BCGothercode');
$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
echo "\r\n<ul id=\"specificOptions\">\r\n    <li class=\"option\">\r\n        <div class=\"title\">\r\n            <label for=\"label\">Label</label>\r\n        </div>\r\n        <div class=\"value\">\r\n            ";
echo getInputTextHtml('label', $label);
echo "        </div>\r\n    </li>\r\n</ul>\r\n\r\n<div id=\"validCharacters\">\r\n    <h3>Valid Characters</h3>\r\n    ";

foreach ($characters as $character) {
	echo getButton($character);
}

echo "</div>\r\n\r\n<div id=\"explanation\">\r\n    <h3>Explanation</h3>\r\n    <ul>\r\n        <li>Enter width of each bars with one characters. Begin by a bar.</li>\r\n        <li>10523: Will do 2px bar, 1px space, 6px bar, 3px space, 4px bar.</li>\r\n    </ul>\r\n</div>\r\n\r\n";
include 'include/footer.php';

?>
