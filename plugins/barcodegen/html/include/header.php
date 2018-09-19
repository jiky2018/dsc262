<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
if (!defined('IN_CB')) {
	exit('You are not allowed to access to this page.');
}

if (version_compare(phpversion(), '5.0.0', '>=') !== true) {
	exit('Sorry, but you have to run this script with PHP5... You currently have the version <b>' . phpversion() . '</b>.');
}

if (!function_exists('imagecreate')) {
	exit('Sorry, make sure you have the GD extension installed before running this script.');
}

include_once 'function.php';
$system_temp_array = explode('/', $_SERVER['PHP_SELF']);
$filename = $system_temp_array[count($system_temp_array) - 1];
$system_temp_array2 = explode('.', $filename);
$availableBarcodes = listBarcodes();
$barcodeName = findValueFromKey($availableBarcodes, $filename);
$code = $system_temp_array2[0];

if (file_exists('config' . DIRECTORY_SEPARATOR . $code . '.php')) {
	include_once 'config' . DIRECTORY_SEPARATOR . $code . '.php';
}

echo "<!DOCTYPE html>\r\n<html>\r\n    <head>\r\n        <title>";
echo $barcodeName;
echo " - Barcode Generator</title>\r\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n        <link type=\"text/css\" rel=\"stylesheet\" href=\"style.css\" />\r\n        <link rel=\"shortcut icon\" href=\"favicon.ico\" />\r\n        <script src=\"jquery-1.7.2.min.js\"></script>\r\n        <script src=\"barcode.js\"></script>\r\n    </head>\r\n    <body class=\"";
echo $code;
echo "\">\r\n\r\n";
$default_value = array();
$default_value['filetype'] = 'PNG';
$default_value['dpi'] = 72;
$default_value['scale'] = isset($defaultScale) ? $defaultScale : 1;
$default_value['rotation'] = 0;
$default_value['font_family'] = 'Arial.ttf';
$default_value['font_size'] = 8;
$default_value['text'] = '';
$default_value['a1'] = '';
$default_value['a2'] = '';
$default_value['a3'] = '';
$filetype = (isset($_POST['filetype']) ? $_POST['filetype'] : $default_value['filetype']);
$dpi = (isset($_POST['dpi']) ? $_POST['dpi'] : $default_value['dpi']);
$scale = intval(isset($_POST['scale']) ? $_POST['scale'] : $default_value['scale']);
$rotation = intval(isset($_POST['rotation']) ? $_POST['rotation'] : $default_value['rotation']);
$font_family = (isset($_POST['font_family']) ? $_POST['font_family'] : $default_value['font_family']);
$font_size = intval(isset($_POST['font_size']) ? $_POST['font_size'] : $default_value['font_size']);
$text = (isset($_POST['text']) ? $_POST['text'] : $default_value['text']);
registerImageKey('filetype', $filetype);
registerImageKey('dpi', $dpi);
registerImageKey('scale', $scale);
registerImageKey('rotation', $rotation);
registerImageKey('font_family', $font_family);
registerImageKey('font_size', $font_size);
registerImageKey('text', stripslashes($text));
$text = convertText($text);
echo "\r\n<div class=\"header\">\r\n    <header>\r\n        <img class=\"logo\" src=\"logo.png\" alt=\"Barcode Generator\" />\r\n        <nav>\r\n            <label for=\"type\">Symbology</label>\r\n            ";
echo getSelectHtml('type', $filename, $availableBarcodes);
echo "            <a class=\"info explanation\" href=\"#\"><img src=\"info.gif\" alt=\"Explanation\" /></a>\r\n        </nav>\r\n    </header>\r\n</div>\r\n\r\n<form action=\"";
echo $_SERVER['REQUEST_URI'];
echo "\" method=\"post\">\r\n    <h1>Barcode Generator</h1>\r\n    <h2>";
echo $barcodeName;
echo "</h2>\r\n    <div class=\"configurations\">\r\n        <section class=\"configurations\">\r\n            <h3>Configurations</h3>\r\n            <table>\r\n                <colgroup>\r\n                    <col class=\"col1\" />\r\n                    <col class=\"col2\" />\r\n                </colgroup>\r\n                <tbody>\r\n                    <tr>\r\n                        <td><label for=\"filetype\">File type</label></td>\r\n                        <td>";
echo getSelectHtml('filetype', $filetype, array('PNG' => 'PNG - Portable Network Graphics', 'JPEG' => 'JPEG - Joint Photographic Experts Group', 'GIF' => 'GIF - Graphics Interchange Format'));
echo "</td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td><label for=\"dpi\">DPI</label></td>\r\n                        <td>";
echo getInputTextHtml('dpi', $dpi, array('type' => 'number', 'min' => 72, 'max' => 300, 'required' => 'required'));
echo " <span id=\"dpiUnavailable\">DPI is available only for PNG and JPEG.</span></td>\r\n                    </tr>\r\n";
if (isset($baseClassFile) && file_exists('include' . DIRECTORY_SEPARATOR . $baseClassFile)) {
	include_once 'include' . DIRECTORY_SEPARATOR . $baseClassFile;
}

echo "                    <tr>\r\n                        <td><label for=\"scale\">Scale</label></td>\r\n                        <td>";
echo getInputTextHtml('scale', $scale, array('type' => 'number', 'min' => 1, 'max' => 4, 'required' => 'required'));
echo "</td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td><label for=\"rotation\">Rotation</label></td>\r\n                        <td>";
echo getSelectHtml('rotation', $rotation, array(0 => 'No rotation', 90 => '90&deg; clockwise', 180 => '180&deg; clockwise', 270 => '270&deg; clockwise'));
echo "</td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td><label for=\"font_family\">Font</label></td>\r\n                        <td>";
echo getSelectHtml('font_family', $font_family, listfonts('../font'));
echo ' ';
echo getInputTextHtml('font_size', $font_size, array('type' => 'number', 'min' => 1, 'max' => 30));
echo "</td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td><label for=\"text\">Data</label></td>\r\n                        <td>\r\n                            <div class=\"generate\" style=\"float: left\">";
echo getInputTextHtml('text', $text, array('type' => 'text', 'required' => 'required'));
echo " <input type=\"submit\" value=\"Generate\" /></div>\r\n                            <div class=\"possiblechars\" style=\"float: right; position: relative;\"><a href=\"#\" class=\"info characters\"><img src=\"info.gif\" alt=\"Help\" /></a></div>\r\n                        </td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n        </section>\r\n    </div>";

?>
