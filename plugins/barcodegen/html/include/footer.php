<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
if (!defined('IN_CB')) {
	exit('You are not allowed to access to this page.');
}

echo "\r\n            <div class=\"output\">\r\n                <section class=\"output\">\r\n                    <h3>Output</h3>\r\n                    ";
$finalRequest = '';

foreach (getImageKeys() as $key => $value) {
	$finalRequest .= '&' . $key . '=' . urlencode($value);
}

if (0 < strlen($finalRequest)) {
	$finalRequest[0] = '?';
}

echo "                    <div id=\"imageOutput\">\r\n                        ";

if ($imageKeys['text'] !== '') {
	echo '<img src="image.php';
	echo $finalRequest;
	echo '" alt="Barcode Image" />';
}
else {
	echo 'Fill the form to generate a barcode.';
}

echo "                    </div>\r\n                </section>\r\n            </div>\r\n        </form>\r\n\r\n        <div class=\"footer\">\r\n            <footer>\r\n            All Rights Reserved &copy; ";
date_default_timezone_set('UTC');
echo date('Y');
echo " <a href=\"http://www.barcodephp.com\" target=\"_blank\">Barcode Generator</a>\r\n            <br />";
echo $code;
echo ' PHP5-v';
echo $codeVersion;
echo "            </footer>\r\n        </div>\r\n    </body>\r\n</html>";

?>
