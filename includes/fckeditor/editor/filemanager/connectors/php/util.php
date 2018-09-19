<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function RemoveFromStart($sourceString, $charToRemove)
{
	$sPattern = '|^' . $charToRemove . '+|';
	return preg_replace($sPattern, '', $sourceString);
}

function RemoveFromEnd($sourceString, $charToRemove)
{
	$sPattern = '|' . $charToRemove . '+$|';
	return preg_replace($sPattern, '', $sourceString);
}

function FindBadUtf8($string)
{
	$regex = '([\\x00-\\x7F]' . '|[\\xC2-\\xDF][\\x80-\\xBF]' . '|\\xE0[\\xA0-\\xBF][\\x80-\\xBF]' . '|[\\xE1-\\xEC\\xEE\\xEF][\\x80-\\xBF]{2}' . '|\\xED[\\x80-\\x9F][\\x80-\\xBF]' . '|\\xF0[\\x90-\\xBF][\\x80-\\xBF]{2}' . '|[\\xF1-\\xF3][\\x80-\\xBF]{3}' . '|\\xF4[\\x80-\\x8F][\\x80-\\xBF]{2}' . '|(.{1}))';

	while (preg_match('/' . $regex . '/S', $string, $matches)) {
		if (isset($matches[2])) {
			return true;
		}

		$string = substr($string, strlen($matches[0]));
	}

	return false;
}

function ConvertToXmlAttribute($value)
{
	if (defined('PHP_OS')) {
		$os = PHP_OS;
	}
	else {
		$os = php_uname();
	}

	if ((strtoupper(substr($os, 0, 3)) === 'WIN') || findbadutf8($value)) {
		return utf8_encode(htmlspecialchars($value));
	}
	else {
		return htmlspecialchars($value);
	}
}

function IsHtmlExtension($ext, $htmlExtensions)
{
	if (!$htmlExtensions || !is_array($htmlExtensions)) {
		return false;
	}

	$lcaseHtmlExtensions = array();

	foreach ($htmlExtensions as $key => $val) {
		$lcaseHtmlExtensions[$key] = strtolower($val);
	}

	return in_array($ext, $lcaseHtmlExtensions);
}

function DetectHtml($filePath)
{
	$fp = @fopen($filePath, 'rb');
	if (($fp === false) || !flock($fp, LOCK_SH)) {
		return -1;
	}

	$chunk = fread($fp, 1024);
	flock($fp, LOCK_UN);
	fclose($fp);
	$chunk = strtolower($chunk);

	if (!$chunk) {
		return false;
	}

	$chunk = trim($chunk);

	if (preg_match('/<!DOCTYPE\\W*X?HTML/sim', $chunk)) {
		return true;
	}

	$tags = array('<body', '<head', '<html', '<img', '<pre', '<script', '<table', '<title');

	foreach ($tags as $tag) {
		if (false !== strpos($chunk, $tag)) {
			return true;
		}
	}

	if (preg_match('!type\\s*=\\s*[\'"]?\\s*(?:\\w*/)?(?:ecma|java)!sim', $chunk)) {
		return true;
	}

	if (preg_match('!(?:href|src|data)\\s*=\\s*[\'"]?\\s*(?:ecma|java)script:!sim', $chunk)) {
		return true;
	}

	if (preg_match('!url\\s*\\(\\s*[\'"]?\\s*(?:ecma|java)script:!sim', $chunk)) {
		return true;
	}

	return false;
}

function IsImageValid($filePath, $extension)
{
	if (!@is_readable($filePath)) {
		return -1;
	}

	$imageCheckExtensions = array('gif', 'jpeg', 'jpg', 'png', 'swf', 'psd', 'bmp', 'iff');

	if (function_exists('version_compare')) {
		$sCurrentVersion = phpversion();

		if (0 <= version_compare($sCurrentVersion, '4.2.0')) {
			$imageCheckExtensions[] = 'tiff';
			$imageCheckExtensions[] = 'tif';
		}

		if (0 <= version_compare($sCurrentVersion, '4.3.0')) {
			$imageCheckExtensions[] = 'swc';
		}

		if (0 <= version_compare($sCurrentVersion, '4.3.2')) {
			$imageCheckExtensions[] = 'jpc';
			$imageCheckExtensions[] = 'jp2';
			$imageCheckExtensions[] = 'jpx';
			$imageCheckExtensions[] = 'jb2';
			$imageCheckExtensions[] = 'xbm';
			$imageCheckExtensions[] = 'wbmp';
		}
	}

	if (!in_array($extension, $imageCheckExtensions)) {
		return true;
	}

	if (@getimagesize($filePath) === false) {
		return false;
	}

	return true;
}


?>
