<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class Http
{
	static public $way = 0;

	static public function setWay($way)
	{
		self::$way = intval($way);
	}

	static public function getSupport()
	{
		if (isset(self::$way) && in_array(self::$way, array(1, 2, 3))) {
			return self::$way;
		}

		if (function_exists('curl_init')) {
			return 1;
		}
		else if (function_exists('fsockopen')) {
			return 2;
		}
		else if (function_exists('file_get_contents')) {
			return 3;
		}
		else {
			return 0;
		}
	}

	static public function doGet($url, $timeout = 5, $header = '')
	{
		if (empty($url) || empty($timeout)) {
			return false;
		}

		if (!preg_match('/^(http|https)/is', $url)) {
			$url = 'http://' . $url;
		}

		$code = self::getSupport();

		switch ($code) {
		case 1:
			return self::curlGet($url, $timeout, $header);
			break;

		case 2:
			return self::socketGet($url, $timeout, $header);
			break;

		case 3:
			return self::phpGet($url, $timeout, $header);
			break;

		default:
			return false;
		}
	}

	static public function doPost($url, $post_data = array(), $timeout = 5, $header = '', $data_type = '')
	{
		if (empty($url) || empty($post_data) || empty($timeout)) {
			return false;
		}

		if (!preg_match('/^(http|https)/is', $url)) {
			$url = 'http://' . $url;
		}

		$code = self::getSupport();

		switch ($code) {
		case 1:
			return self::curlPost($url, $post_data, $timeout, $header, $data_type);
			break;

		case 2:
			return self::socketPost($url, $post_data, $timeout, $header, $data_type);
			break;

		case 3:
			return self::phpPost($url, $post_data, $timeout, $header, $data_type);
			break;

		default:
			return false;
		}
	}

	static public function curlGet($url, $timeout = 5, $header = '')
	{
		$header = (empty($header) ? self::defaultHeader() : $header);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	static public function curlPost($url, $post_data = array(), $timeout = 5, $header = '', $data_type = '')
	{
		$header = (empty($header) ? '' : $header);

		if ($data_type == 'json') {
			$post_string = json_encode($post_data);
		}
		else if (is_array($post_data)) {
			$post_string = http_build_query($post_data);
		}
		else {
			$post_string = $post_data;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	static public function socketGet($url, $timeout = 5, $header = '')
	{
		$header = (empty($header) ? self::defaultHeader() : $header);
		$url2 = parse_url($url);
		$url2['path'] = isset($url2['path']) ? $url2['path'] : '/';
		$url2['port'] = isset($url2['port']) ? $url2['port'] : 80;
		$url2['query'] = isset($url2['query']) ? '?' . $url2['query'] : '';
		$host_ip = @gethostbyname($url2['host']);

		if (($fsock = fsockopen($host_ip, $url2['port'], $errno, $errstr, $timeout)) < 0) {
			return false;
		}

		$request = $url2['path'] . $url2['query'];
		$in = 'GET ' . $request . " HTTP/1.0\r\n";

		if (false === strpos($header, 'Host:')) {
			$in .= 'Host: ' . $url2['host'] . "\r\n";
		}

		$in .= $header;
		$in .= "Connection: Close\r\n\r\n";

		if (!@fwrite($fsock, $in, strlen($in))) {
			@fclose($fsock);
			return false;
		}

		return self::GetHttpContent($fsock);
	}

	static public function socketPost($url, $post_data = array(), $timeout = 5, $header = '', $data_type = '')
	{
		$header = (empty($header) ? self::defaultHeader() : $header);

		if ($data_type == 'json') {
			$post_string = json_encode($post_data);
		}
		else {
			$post_string = http_build_query($post_data);
		}

		$url2 = parse_url($url);
		$url2['path'] = $url2['path'] == '' ? '/' : $url2['path'];
		$url2['port'] = $url2['port'] == '' ? 80 : $url2['port'];
		$host_ip = @gethostbyname($url2['host']);
		$fsock_timeout = $timeout;

		if (($fsock = fsockopen($host_ip, $url2['port'], $errno, $errstr, $fsock_timeout)) < 0) {
			return false;
		}

		$request = $url2['path'] . ($url2['query'] ? '?' . $url2['query'] : '');
		$in = 'POST ' . $request . " HTTP/1.0\r\n";
		$in .= 'Host: ' . $url2['host'] . "\r\n";
		$in .= $header;
		$in .= "Content-type: application/x-www-form-urlencoded\r\n";
		$in .= 'Content-Length: ' . strlen($post_string) . "\r\n";
		$in .= "Connection: Close\r\n\r\n";
		$in .= $post_string . "\r\n\r\n";
		unset($post_string);

		if (!@fwrite($fsock, $in, strlen($in))) {
			@fclose($fsock);
			return false;
		}

		return self::GetHttpContent($fsock);
	}

	static public function phpGet($url, $timeout = 5, $header = '')
	{
		$header = (empty($header) ? self::defaultHeader() : $header);
		$opts = array(
			'http' => array('protocol_version' => '1.0', 'method' => 'GET', 'timeout' => $timeout, 'header' => $header)
			);
		$context = stream_context_create($opts);
		return @file_get_contents($url, false, $context);
	}

	static public function phpPost($url, $post_data = array(), $timeout = 5, $header = '', $data_type = '')
	{
		$header = (empty($header) ? self::defaultHeader() : $header);

		if ($data_type == 'json') {
			$post_string = json_encode($post_data);
		}
		else {
			$post_string = http_build_query($post_data);
		}

		$header .= 'Content-length: ' . strlen($post_string);
		$opts = array(
			'http' => array('protocol_version' => '1.0', 'method' => 'POST', 'timeout' => $timeout, 'header' => $header, 'content' => $post_string)
			);
		$context = stream_context_create($opts);
		return @file_get_contents($url, false, $context);
	}

	static public function defaultHeader()
	{
		$header = "User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12\r\n";
		$header .= "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
		$header .= "Accept-language: zh-cn,zh;q=0.5\r\n";
		$header .= "Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n";
		return $header;
	}

	static private function GetHttpContent($fsock = NULL)
	{
		$out = NULL;

		while ($buff = @fgets($fsock, 2048)) {
			$out .= $buff;
		}

		fclose($fsock);
		$pos = strpos($out, "\r\n\r\n");
		$head = substr($out, 0, $pos);
		$status = substr($head, 0, strpos($head, "\r\n"));
		$body = substr($out, $pos + 4, strlen($out) - ($pos + 4));

		if (preg_match('/^HTTP\\/\\d\\.\\d\\s([\\d]+)\\s.*$/', $status, $matches)) {
			if ((intval($matches[1]) / 100) == 2) {
				return $body;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	static public function download($filename, $showname = '', $expire = 1800)
	{
		if (file_exists($filename) && is_file($filename)) {
			$length = filesize($filename);
		}
		else {
			exit('下载文件不存在！');
		}

		$type = mime_content_type($filename);
		header('Pragma: public');
		header('Cache-control: max-age=' . $expire);
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expire) . 'GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . 'GMT');
		header('Content-Disposition: attachment; filename=' . $showname);
		header('Content-Length: ' . $length);
		header('Content-type: ' . $type);
		header('Content-Encoding: none');
		header('Content-Transfer-Encoding: binary');
		readfile($filename);
		return true;
	}
}

if (!defined('IN_ECS')) {
	exit('Deny Access');
}

if (!function_exists('mime_content_type')) {
	function mime_content_type($filename)
	{
		static $contentType = array('ai' => 'application/postscript', 'aif' => 'audio/x-aiff', 'aifc' => 'audio/x-aiff', 'aiff' => 'audio/x-aiff', 'asc' => 'application/pgp', 'asf' => 'video/x-ms-asf', 'asx' => 'video/x-ms-asf', 'au' => 'audio/basic', 'avi' => 'video/x-msvideo', 'bcpio' => 'application/x-bcpio', 'bin' => 'application/octet-stream', 'bmp' => 'image/bmp', 'c' => 'text/plain', 'cc' => 'text/plain', 'cs' => 'text/plain', 'cpp' => 'text/x-c++src', 'cxx' => 'text/x-c++src', 'cdf' => 'application/x-netcdf', 'class' => 'application/octet-stream', 'com' => 'application/octet-stream', 'cpio' => 'application/x-cpio', 'cpt' => 'application/mac-compactpro', 'csh' => 'application/x-csh', 'css' => 'text/css', 'csv' => 'text/comma-separated-values', 'dcr' => 'application/x-director', 'diff' => 'text/diff', 'dir' => 'application/x-director', 'dll' => 'application/octet-stream', 'dms' => 'application/octet-stream', 'doc' => 'application/msword', 'dot' => 'application/msword', 'dvi' => 'application/x-dvi', 'dxr' => 'application/x-director', 'eps' => 'application/postscript', 'etx' => 'text/x-setext', 'exe' => 'application/octet-stream', 'ez' => 'application/andrew-inset', 'gif' => 'image/gif', 'gtar' => 'application/x-gtar', 'gz' => 'application/x-gzip', 'h' => 'text/plain', 'h++' => 'text/plain', 'hh' => 'text/plain', 'hpp' => 'text/plain', 'hxx' => 'text/plain', 'hdf' => 'application/x-hdf', 'hqx' => 'application/mac-binhex40', 'htm' => 'text/html', 'html' => 'text/html', 'ice' => 'x-conference/x-cooltalk', 'ics' => 'text/calendar', 'ief' => 'image/ief', 'ifb' => 'text/calendar', 'iges' => 'model/iges', 'igs' => 'model/iges', 'jar' => 'application/x-jar', 'java' => 'text/x-java-source', 'jpe' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'js' => 'application/x-javascript', 'kar' => 'audio/midi', 'latex' => 'application/x-latex', 'lha' => 'application/octet-stream', 'log' => 'text/plain', 'lzh' => 'application/octet-stream', 'm3u' => 'audio/x-mpegurl', 'man' => 'application/x-troff-man', 'me' => 'application/x-troff-me', 'mesh' => 'model/mesh', 'mid' => 'audio/midi', 'midi' => 'audio/midi', 'mif' => 'application/vnd.mif', 'mov' => 'video/quicktime', 'movie' => 'video/x-sgi-movie', 'mp2' => 'audio/mpeg', 'mp3' => 'audio/mpeg', 'mpe' => 'video/mpeg', 'mpeg' => 'video/mpeg', 'mpg' => 'video/mpeg', 'mpga' => 'audio/mpeg', 'ms' => 'application/x-troff-ms', 'msh' => 'model/mesh', 'mxu' => 'video/vnd.mpegurl', 'nc' => 'application/x-netcdf', 'oda' => 'application/oda', 'patch' => 'text/diff', 'pbm' => 'image/x-portable-bitmap', 'pdb' => 'chemical/x-pdb', 'pdf' => 'application/pdf', 'pgm' => 'image/x-portable-graymap', 'pgn' => 'application/x-chess-pgn', 'pgp' => 'application/pgp', 'php' => 'application/x-httpd-php', 'php3' => 'application/x-httpd-php3', 'pl' => 'application/x-perl', 'pm' => 'application/x-perl', 'png' => 'image/png', 'pnm' => 'image/x-portable-anymap', 'po' => 'text/plain', 'ppm' => 'image/x-portable-pixmap', 'ppt' => 'application/vnd.ms-powerpoint', 'ps' => 'application/postscript', 'qt' => 'video/quicktime', 'ra' => 'audio/x-realaudio', 'rar' => 'application/octet-stream', 'ram' => 'audio/x-pn-realaudio', 'ras' => 'image/x-cmu-raster', 'rgb' => 'image/x-rgb', 'rm' => 'audio/x-pn-realaudio', 'roff' => 'application/x-troff', 'rpm' => 'audio/x-pn-realaudio-plugin', 'rtf' => 'text/rtf', 'rtx' => 'text/richtext', 'sgm' => 'text/sgml', 'sgml' => 'text/sgml', 'sh' => 'application/x-sh', 'shar' => 'application/x-shar', 'shtml' => 'text/html', 'silo' => 'model/mesh', 'sit' => 'application/x-stuffit', 'skd' => 'application/x-koan', 'skm' => 'application/x-koan', 'skp' => 'application/x-koan', 'skt' => 'application/x-koan', 'smi' => 'application/smil', 'smil' => 'application/smil', 'snd' => 'audio/basic', 'so' => 'application/octet-stream', 'spl' => 'application/x-futuresplash', 'src' => 'application/x-wais-source', 'stc' => 'application/vnd.sun.xml.calc.template', 'std' => 'application/vnd.sun.xml.draw.template', 'sti' => 'application/vnd.sun.xml.impress.template', 'stw' => 'application/vnd.sun.xml.writer.template', 'sv4cpio' => 'application/x-sv4cpio', 'sv4crc' => 'application/x-sv4crc', 'swf' => 'application/x-shockwave-flash', 'sxc' => 'application/vnd.sun.xml.calc', 'sxd' => 'application/vnd.sun.xml.draw', 'sxg' => 'application/vnd.sun.xml.writer.global', 'sxi' => 'application/vnd.sun.xml.impress', 'sxm' => 'application/vnd.sun.xml.math', 'sxw' => 'application/vnd.sun.xml.writer', 't' => 'application/x-troff', 'tar' => 'application/x-tar', 'tcl' => 'application/x-tcl', 'tex' => 'application/x-tex', 'texi' => 'application/x-texinfo', 'texinfo' => 'application/x-texinfo', 'tgz' => 'application/x-gtar', 'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'tr' => 'application/x-troff', 'tsv' => 'text/tab-separated-values', 'txt' => 'text/plain', 'ustar' => 'application/x-ustar', 'vbs' => 'text/plain', 'vcd' => 'application/x-cdlink', 'vcf' => 'text/x-vcard', 'vcs' => 'text/calendar', 'vfb' => 'text/calendar', 'vrml' => 'model/vrml', 'vsd' => 'application/vnd.visio', 'wav' => 'audio/x-wav', 'wax' => 'audio/x-ms-wax', 'wbmp' => 'image/vnd.wap.wbmp', 'wbxml' => 'application/vnd.wap.wbxml', 'wm' => 'video/x-ms-wm', 'wma' => 'audio/x-ms-wma', 'wmd' => 'application/x-ms-wmd', 'wml' => 'text/vnd.wap.wml', 'wmlc' => 'application/vnd.wap.wmlc', 'wmls' => 'text/vnd.wap.wmlscript', 'wmlsc' => 'application/vnd.wap.wmlscriptc', 'wmv' => 'video/x-ms-wmv', 'wmx' => 'video/x-ms-wmx', 'wmz' => 'application/x-ms-wmz', 'wrl' => 'model/vrml', 'wvx' => 'video/x-ms-wvx', 'xbm' => 'image/x-xbitmap', 'xht' => 'application/xhtml+xml', 'xhtml' => 'application/xhtml+xml', 'xls' => 'application/vnd.ms-excel', 'xlt' => 'application/vnd.ms-excel', 'xml' => 'application/xml', 'xpm' => 'image/x-xpixmap', 'xsl' => 'text/xml', 'xwd' => 'image/x-xwindowdump', 'xyz' => 'chemical/x-xyz', 'z' => 'application/x-compress', 'zip' => 'application/zip');
		$type = strtolower(substr(strrchr($filename, '.'), 1));

		if (isset($contentType[$type])) {
			$mime = $contentType[$type];
		}
		else {
			$mime = 'application/octet-stream';
		}

		return $mime;
	}
}

if (!function_exists('image_type_to_extension')) {
	function image_type_to_extension($imagetype)
	{
		if (empty($imagetype)) {
			return false;
		}

		switch ($imagetype) {
		case IMAGETYPE_GIF:
			return '.gif';
		case IMAGETYPE_JPEG:
			return '.jpg';
		case IMAGETYPE_PNG:
			return '.png';
		case IMAGETYPE_SWF:
			return '.swf';
		case IMAGETYPE_PSD:
			return '.psd';
		case IMAGETYPE_BMP:
			return '.bmp';
		case IMAGETYPE_TIFF_II:
			return '.tiff';
		case IMAGETYPE_TIFF_MM:
			return '.tiff';
		case IMAGETYPE_JPC:
			return '.jpc';
		case IMAGETYPE_JP2:
			return '.jp2';
		case IMAGETYPE_JPX:
			return '.jpf';
		case IMAGETYPE_JB2:
			return '.jb2';
		case IMAGETYPE_SWC:
			return '.swc';
		case IMAGETYPE_IFF:
			return '.aiff';
		case IMAGETYPE_WBMP:
			return '.wbmp';
		case IMAGETYPE_XBM:
			return '.xbm';
		default:
			return false;
		}
	}
}

?>
