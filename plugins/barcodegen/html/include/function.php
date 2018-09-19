<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function registerImageKey($key, $value)
{
	global $imageKeys;
	$imageKeys[$key] = $value;
}

function getImageKeys()
{
	global $imageKeys;
	return $imageKeys;
}

function getElementHtml($tag, $attributes, $content = false)
{
	$code = '<' . $tag;

	foreach ($attributes as $attribute => $value) {
		$code .= ' ' . $attribute . '="' . htmlentities(stripslashes($value), ENT_COMPAT) . '"';
	}

	if (($content === false) || ($content === NULL)) {
		$code .= ' />';
	}
	else {
		$code .= '>' . $content . '</' . $tag . '>';
	}

	return $code;
}

function getInputTextHtml($name, $currentValue, $attributes = array())
{
	$defaultAttributes = array('id' => $name, 'name' => $name);
	$finalAttributes = array_merge($defaultAttributes, $attributes);

	if ($currentValue !== NULL) {
		$finalAttributes['value'] = $currentValue;
	}

	return getelementhtml('input', $finalAttributes, false);
}

function getOptionGroup($options, $currentValue)
{
	$content = '';

	foreach ($options as $optionKey => $optionValue) {
		if (is_array($optionValue)) {
			$content .= '<optgroup label="' . $optionKey . '">' . getOptionGroup($optionValue, $currentValue) . '</optgroup>';
		}
		else {
			$optionAttributes = array();

			if ($currentValue == $optionKey) {
				$optionAttributes['selected'] = 'selected';
			}

			$content .= getOptionHtml($optionKey, $optionValue, $optionAttributes);
		}
	}

	return $content;
}

function getOptionHtml($value, $content, $attributes = array())
{
	$defaultAttributes = array('value' => $value);
	$finalAttributes = array_merge($defaultAttributes, $attributes);
	return getelementhtml('option', $finalAttributes, $content);
}

function getSelectHtml($name, $currentValue, $options, $attributes = array())
{
	$defaultAttributes = array('size' => 1, 'id' => $name, 'name' => $name);
	$finalAttributes = array_merge($defaultAttributes, $attributes);
	$content = getoptiongroup($options, $currentValue);
	return getelementhtml('select', $finalAttributes, $content);
}

function getCheckboxHtml($name, $currentValue, $attributes = array())
{
	$defaultAttributes = array('type' => 'checkbox', 'id' => $name, 'name' => $name, 'value' => isset($attributes['value']) ? $attributes['value'] : 'On');
	$finalAttributes = array_merge($defaultAttributes, $attributes);

	if ($currentValue == $finalAttributes['value']) {
		$finalAttributes['checked'] = 'checked';
	}

	return getelementhtml('input', $finalAttributes, false);
}

function getButton($value, $output = NULL)
{
	$escaped = false;
	$finalValue = ($value[0] === '&' ? $value : htmlentities($value));

	if ($output === NULL) {
		$output = $value;
	}
	else {
		$escaped = true;
	}

	$code = '<input type="button" value="' . $finalValue . '" data-output="' . $output . '"' . ($escaped ? ' data-escaped="true"' : '') . ' />';
	return $code;
}

function listfonts($folder)
{
	$array = array();

	if (($handle = opendir($folder)) !== false) {
		while (($file = readdir($handle)) !== false) {
			if (substr($file, -4, 4) === '.ttf') {
				$array[$file] = $file;
			}
		}
	}

	closedir($handle);
	array_unshift($array, 'No Label');
	return $array;
}

function listbarcodes()
{
	include_once 'barcode.php';
	$availableBarcodes = array();

	foreach ($supportedBarcodes as $file => $title) {
		if (file_exists($file)) {
			$availableBarcodes[$file] = $title;
		}
	}

	return $availableBarcodes;
}

function findValueFromKey($haystack, $needle)
{
	foreach ($haystack as $key => $value) {
		if (strcasecmp($key, $needle) === 0) {
			return $value;
		}
	}

	return NULL;
}

function convertText($text)
{
	$text = stripslashes($text);

	if (function_exists('mb_convert_encoding')) {
		$text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
	}

	return $text;
}

if (!defined('IN_CB')) {
	exit('You are not allowed to access to this page.');
}

$imageKeys = array();

?>
