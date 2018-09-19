<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Core;

class OssUtil
{
	const OSS_CONTENT = 'content';
	const OSS_LENGTH = 'length';
	const OSS_HEADERS = 'headers';
	const OSS_MAX_OBJECT_GROUP_VALUE = 1000;
	const OSS_MAX_PART_SIZE = 5368709120;
	const OSS_MID_PART_SIZE = 10485760;
	const OSS_MIN_PART_SIZE = 102400;

	static public function toQueryString($options = array())
	{
		$temp = array();
		uksort($options, 'strnatcasecmp');

		foreach ($options as $key => $value) {
			if (is_string($key) && !is_array($value)) {
				$temp[] = rawurlencode($key) . '=' . rawurlencode($value);
			}
		}

		return implode('&', $temp);
	}

	static public function sReplace($subject)
	{
		$search = array('<', '>', '&', '\'', '"');
		$replace = array('&lt;', '&gt;', '&amp;', '&apos;', '&quot;');
		return str_replace($search, $replace, $subject);
	}

	static public function chkChinese($str)
	{
		return preg_match('/[\\x80-\\xff]./', $str);
	}

	static public function isGb2312($str)
	{
		for ($i = 0; $i < strlen($str); $i++) {
			$v = ord($str[$i]);

			if (127 < $v) {
				if ((228 <= $v) && ($v <= 233)) {
					if ((strlen($str) - 1) <= $i + 2) {
						return true;
					}

					$v1 = ord($str[$i + 1]);
					$v2 = ord($str[$i + 2]);
					if ((128 <= $v1) && ($v1 <= 191) && (128 <= $v2) && ($v2 <= 191)) {
						return false;
					}
					else {
						return true;
					}
				}
			}
		}

		return false;
	}

	static public function checkChar($str, $gbk = true)
	{
		for ($i = 0; $i < strlen($str); $i++) {
			$v = ord($str[$i]);

			if (127 < $v) {
				if ((228 <= $v) && ($v <= 233)) {
					if ((strlen($str) - 1) <= $i + 2) {
						return $gbk ? true : FALSE;
					}

					$v1 = ord($str[$i + 1]);
					$v2 = ord($str[$i + 2]);

					if ($gbk) {
						return (128 <= $v1) && ($v1 <= 191) && (128 <= $v2) && ($v2 <= 191) ? FALSE : TRUE;
					}
					else {
						return (128 <= $v1) && ($v1 <= 191) && (128 <= $v2) && ($v2 <= 191) ? TRUE : FALSE;
					}
				}
			}
		}

		return $gbk ? TRUE : FALSE;
	}

	static public function validateBucket($bucket)
	{
		$pattern = '/^[a-z0-9][a-z0-9-]{2,62}$/';

		if (!preg_match($pattern, $bucket)) {
			return false;
		}

		return true;
	}

	static public function validateObject($object)
	{
		$pattern = '/^.{1,1023}$/';
		if (empty($object) || !preg_match($pattern, $object) || self::startsWith($object, '/') || self::startsWith($object, '\\')) {
			return false;
		}

		return true;
	}

	static public function startsWith($str, $findMe)
	{
		if (strpos($str, $findMe) === 0) {
			return true;
		}
		else {
			return false;
		}
	}

	static public function validateOptions($options)
	{
		if (($options != NULL) && !is_array($options)) {
			throw new OssException($options . ':' . 'option must be array');
		}
	}

	static public function validateContent($content)
	{
		if (empty($content)) {
			throw new OssException('http body content is invalid');
		}
	}

	static public function throwOssExceptionWithMessageIfEmpty($name, $errMsg)
	{
		if (empty($name)) {
			throw new OssException($errMsg);
		}
	}

	static public function generateFile($filename, $size)
	{
		if (file_exists($filename) && ($size == filesize($filename))) {
			echo $filename . ' already exists, no need to create again. ';
			return NULL;
		}

		$part_size = 1 * 1024 * 1024;
		$fp = fopen($filename, 'w');
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ\';';
		$charactersLength = strlen($characters);

		if ($fp) {
			while (0 < $size) {
				if ($size < $part_size) {
					$write_size = $size;
				}
				else {
					$write_size = $part_size;
				}

				$size -= $write_size;
				$a = $characters[rand(0, $charactersLength - 1)];
				$content = str_repeat($a, $write_size);
				$flag = fwrite($fp, $content);

				if (!$flag) {
					echo 'write to ' . $filename . ' failed. <br>';
					break;
				}
			}
		}
		else {
			echo 'open ' . $filename . ' failed. <br>';
		}

		fclose($fp);
	}

	static public function getMd5SumForFile($filename, $from_pos, $to_pos)
	{
		$content_md5 = '';

		if (self::OSS_MAX_PART_SIZE < ($to_pos - $from_pos)) {
			return $content_md5;
		}

		$filesize = filesize($filename);
		if (($filesize <= $from_pos) || ($filesize <= $to_pos) || ($from_pos < 0) || ($to_pos < 0)) {
			return $content_md5;
		}

		$total_length = ($to_pos - $from_pos) + 1;
		$buffer = 8192;
		$left_length = $total_length;

		if (!file_exists($filename)) {
			return $content_md5;
		}

		if (false === ($fh = fopen($filename, 'rb'))) {
			return $content_md5;
		}

		fseek($fh, $from_pos);
		$data = '';

		while (!feof($fh)) {
			if ($buffer <= $left_length) {
				$read_length = $buffer;
			}
			else {
				$read_length = $left_length;
			}

			if ($read_length <= 0) {
				break;
			}
			else {
				$data .= fread($fh, $read_length);
				$left_length = $left_length - $read_length;
			}
		}

		fclose($fh);
		$content_md5 = base64_encode(md5($data, true));
		return $content_md5;
	}

	static public function isWin()
	{
		return strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';
	}

	static public function encodePath($file_path)
	{
		if (self::chkChinese($file_path) && self::isWin()) {
			$file_path = iconv('utf-8', 'gbk', $file_path);
		}

		return $file_path;
	}

	static public function isIPFormat($endpoint)
	{
		$ip_array = explode(':', $endpoint);
		$hostname = $ip_array[0];
		$ret = filter_var($hostname, FILTER_VALIDATE_IP);

		if (!$ret) {
			return false;
		}
		else {
			return true;
		}
	}

	static public function createDeleteObjectsXmlBody($objects, $quiet)
	{
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Delete></Delete>');
		$xml->addChild('Quiet', $quiet);

		foreach ($objects as $object) {
			$sub_object = $xml->addChild('Object');
			$object = OssUtil::sReplace($object);
			$sub_object->addChild('Key', $object);
		}

		return $xml->asXML();
	}

	static public function createCompleteMultipartUploadXmlBody($listParts)
	{
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><CompleteMultipartUpload></CompleteMultipartUpload>');

		foreach ($listParts as $node) {
			$part = $xml->addChild('Part');
			$part->addChild('PartNumber', $node['PartNumber']);
			$part->addChild('ETag', $node['ETag']);
		}

		return $xml->asXML();
	}

	static public function readDir($dir, $exclude = '.|..|.svn|.git', $recursive = false)
	{
		$file_list_array = array();
		$base_path = $dir;
		$exclude_array = explode('|', $exclude);
		$exclude_array = array_unique(array_merge($exclude_array, array('.', '..')));

		if ($recursive) {
			foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $new_file) {
				if ($new_file->isDir()) {
					continue;
				}

				$object = str_replace($base_path, '', $new_file);

				if (!in_array(strtolower($object), $exclude_array)) {
					$object = ltrim($object, '/');

					if (is_file($new_file)) {
						$key = md5($new_file . $object, false);
						$file_list_array[$key] = array('path' => $new_file, 'file' => $object);
					}
				}
			}
		}
		else if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if (!in_array(strtolower($file), $exclude_array)) {
					$new_file = $dir . '/' . $file;
					$object = $file;
					$object = ltrim($object, '/');

					if (is_file($new_file)) {
						$key = md5($new_file . $object, false);
						$file_list_array[$key] = array('path' => $new_file, 'file' => $object);
					}
				}
			}

			closedir($handle);
		}

		return $file_list_array;
	}

	static public function decodeKey($key, $encoding)
	{
		if ($encoding == '') {
			return $key;
		}

		if ($encoding == 'url') {
			return rawurldecode($key);
		}
		else {
			throw new OssException('Unrecognized encoding type: ' . $encoding);
		}
	}
}


?>
