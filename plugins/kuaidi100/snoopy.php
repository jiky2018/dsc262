<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class Snoopy
{
	public $host = 'www.php.net';
	public $port = 80;
	public $proxy_host = '';
	public $proxy_port = '';
	public $proxy_user = '';
	public $proxy_pass = '';
	public $agent = 'Snoopy v1.2.4';
	public $referer = '';
	public $cookies = array();
	public $rawheaders = array();
	public $maxredirs = 5;
	public $lastredirectaddr = '';
	public $offsiteok = true;
	public $maxframes = 0;
	public $expandlinks = true;
	public $passcookies = true;
	public $user = '';
	public $pass = '';
	public $accept = 'image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, */*';
	public $results = '';
	public $error = '';
	public $response_code = '';
	public $headers = array();
	public $maxlength = 500000;
	public $read_timeout = 0;
	public $timed_out = false;
	public $status = 0;
	public $temp_dir = '/tmp';
	public $curl_path = '/usr/local/bin/curl';
	public $_maxlinelen = 4096;
	public $_httpmethod = 'GET';
	public $_httpversion = 'HTTP/1.0';
	public $_submit_method = 'POST';
	public $_submit_type = 'application/x-www-form-urlencoded';
	public $_mime_boundary = '';
	public $_redirectaddr = false;
	public $_redirectdepth = 0;
	public $_frameurls = array();
	public $_framedepth = 0;
	public $_isproxy = false;
	public $_fp_timeout = 30;

	public function fetch($URI)
	{
		$URI_PARTS = parse_url($URI);

		if (!empty($URI_PARTS['user'])) {
			$this->user = $URI_PARTS['user'];
		}

		if (!empty($URI_PARTS['pass'])) {
			$this->pass = $URI_PARTS['pass'];
		}

		if (empty($URI_PARTS['query'])) {
			$URI_PARTS['query'] = '';
		}

		if (empty($URI_PARTS['path'])) {
			$URI_PARTS['path'] = '';
		}

		switch (strtolower($URI_PARTS['scheme'])) {
		case 'http':
			$this->host = $URI_PARTS['host'];

			if (!empty($URI_PARTS['port'])) {
				$this->port = $URI_PARTS['port'];
			}

			if ($this->_connect($fp)) {
				if ($this->_isproxy) {
					$this->_httprequest($URI, $fp, $URI, $this->_httpmethod);
				}
				else {
					$path = $URI_PARTS['path'] . ($URI_PARTS['query'] ? '?' . $URI_PARTS['query'] : '');
					$this->_httprequest($path, $fp, $URI, $this->_httpmethod);
				}

				$this->_disconnect($fp);

				if ($this->_redirectaddr) {
					if ($this->_redirectdepth < $this->maxredirs) {
						if (preg_match('|^http://' . preg_quote($this->host) . '|i', $this->_redirectaddr) || $this->offsiteok) {
							$this->_redirectdepth++;
							$this->lastredirectaddr = $this->_redirectaddr;
							$this->fetch($this->_redirectaddr);
						}
					}
				}

				if (($this->_framedepth < $this->maxframes) && (0 < count($this->_frameurls))) {
					$frameurls = $this->_frameurls;
					$this->_frameurls = array();

					while (list(, $frameurl) = each($frameurls)) {
						if ($this->_framedepth < $this->maxframes) {
							$this->fetch($frameurl);
							$this->_framedepth++;
						}
						else {
							break;
						}
					}
				}
			}
			else {
				return false;
			}

			return true;
			break;

		case 'https':
			if (!$this->curl_path) {
				return false;
			}

			if (function_exists('is_executable')) {
				if (!is_executable($this->curl_path)) {
					return false;
				}
			}

			$this->host = $URI_PARTS['host'];

			if (!empty($URI_PARTS['port'])) {
				$this->port = $URI_PARTS['port'];
			}

			if ($this->_isproxy) {
				$this->_httpsrequest($URI, $URI, $this->_httpmethod);
			}
			else {
				$path = $URI_PARTS['path'] . ($URI_PARTS['query'] ? '?' . $URI_PARTS['query'] : '');
				$this->_httpsrequest($path, $URI, $this->_httpmethod);
			}

			if ($this->_redirectaddr) {
				if ($this->_redirectdepth < $this->maxredirs) {
					if (preg_match('|^http://' . preg_quote($this->host) . '|i', $this->_redirectaddr) || $this->offsiteok) {
						$this->_redirectdepth++;
						$this->lastredirectaddr = $this->_redirectaddr;
						$this->fetch($this->_redirectaddr);
					}
				}
			}

			if (($this->_framedepth < $this->maxframes) && (0 < count($this->_frameurls))) {
				$frameurls = $this->_frameurls;
				$this->_frameurls = array();

				while (list(, $frameurl) = each($frameurls)) {
					if ($this->_framedepth < $this->maxframes) {
						$this->fetch($frameurl);
						$this->_framedepth++;
					}
					else {
						break;
					}
				}
			}

			return true;
			break;

		default:
			$this->error = 'Invalid protocol "' . $URI_PARTS['scheme'] . '"\\n';
			return false;
			break;
		}

		return true;
	}

	public function submit($URI, $formvars = '', $formfiles = '')
	{
		unset($postdata);
		$postdata = $this->_prepare_post_body($formvars, $formfiles);
		$URI_PARTS = parse_url($URI);

		if (!empty($URI_PARTS['user'])) {
			$this->user = $URI_PARTS['user'];
		}

		if (!empty($URI_PARTS['pass'])) {
			$this->pass = $URI_PARTS['pass'];
		}

		if (empty($URI_PARTS['query'])) {
			$URI_PARTS['query'] = '';
		}

		if (empty($URI_PARTS['path'])) {
			$URI_PARTS['path'] = '';
		}

		switch (strtolower($URI_PARTS['scheme'])) {
		case 'http':
			$this->host = $URI_PARTS['host'];

			if (!empty($URI_PARTS['port'])) {
				$this->port = $URI_PARTS['port'];
			}

			if ($this->_connect($fp)) {
				if ($this->_isproxy) {
					$this->_httprequest($URI, $fp, $URI, $this->_submit_method, $this->_submit_type, $postdata);
				}
				else {
					$path = $URI_PARTS['path'] . ($URI_PARTS['query'] ? '?' . $URI_PARTS['query'] : '');
					$this->_httprequest($path, $fp, $URI, $this->_submit_method, $this->_submit_type, $postdata);
				}

				$this->_disconnect($fp);

				if ($this->_redirectaddr) {
					if ($this->_redirectdepth < $this->maxredirs) {
						if (!preg_match('|^' . $URI_PARTS['scheme'] . '://|', $this->_redirectaddr)) {
							$this->_redirectaddr = $this->_expandlinks($this->_redirectaddr, $URI_PARTS['scheme'] . '://' . $URI_PARTS['host']);
						}

						if (preg_match('|^http://' . preg_quote($this->host) . '|i', $this->_redirectaddr) || $this->offsiteok) {
							$this->_redirectdepth++;
							$this->lastredirectaddr = $this->_redirectaddr;

							if (0 < strpos($this->_redirectaddr, '?')) {
								$this->fetch($this->_redirectaddr);
							}
							else {
								$this->submit($this->_redirectaddr, $formvars, $formfiles);
							}
						}
					}
				}

				if (($this->_framedepth < $this->maxframes) && (0 < count($this->_frameurls))) {
					$frameurls = $this->_frameurls;
					$this->_frameurls = array();

					while (list(, $frameurl) = each($frameurls)) {
						if ($this->_framedepth < $this->maxframes) {
							$this->fetch($frameurl);
							$this->_framedepth++;
						}
						else {
							break;
						}
					}
				}
			}
			else {
				return false;
			}

			return true;
			break;

		case 'https':
			if (!$this->curl_path) {
				return false;
			}

			if (function_exists('is_executable')) {
				if (!is_executable($this->curl_path)) {
					return false;
				}
			}

			$this->host = $URI_PARTS['host'];

			if (!empty($URI_PARTS['port'])) {
				$this->port = $URI_PARTS['port'];
			}

			if ($this->_isproxy) {
				$this->_httpsrequest($URI, $URI, $this->_submit_method, $this->_submit_type, $postdata);
			}
			else {
				$path = $URI_PARTS['path'] . ($URI_PARTS['query'] ? '?' . $URI_PARTS['query'] : '');
				$this->_httpsrequest($path, $URI, $this->_submit_method, $this->_submit_type, $postdata);
			}

			if ($this->_redirectaddr) {
				if ($this->_redirectdepth < $this->maxredirs) {
					if (!preg_match('|^' . $URI_PARTS['scheme'] . '://|', $this->_redirectaddr)) {
						$this->_redirectaddr = $this->_expandlinks($this->_redirectaddr, $URI_PARTS['scheme'] . '://' . $URI_PARTS['host']);
					}

					if (preg_match('|^http://' . preg_quote($this->host) . '|i', $this->_redirectaddr) || $this->offsiteok) {
						$this->_redirectdepth++;
						$this->lastredirectaddr = $this->_redirectaddr;

						if (0 < strpos($this->_redirectaddr, '?')) {
							$this->fetch($this->_redirectaddr);
						}
						else {
							$this->submit($this->_redirectaddr, $formvars, $formfiles);
						}
					}
				}
			}

			if (($this->_framedepth < $this->maxframes) && (0 < count($this->_frameurls))) {
				$frameurls = $this->_frameurls;
				$this->_frameurls = array();

				while (list(, $frameurl) = each($frameurls)) {
					if ($this->_framedepth < $this->maxframes) {
						$this->fetch($frameurl);
						$this->_framedepth++;
					}
					else {
						break;
					}
				}
			}

			return true;
			break;

		default:
			$this->error = 'Invalid protocol "' . $URI_PARTS['scheme'] . '"\\n';
			return false;
			break;
		}

		return true;
	}

	public function fetchlinks($URI)
	{
		if ($this->fetch($URI)) {
			if ($this->lastredirectaddr) {
				$URI = $this->lastredirectaddr;
			}

			if (is_array($this->results)) {
				for ($x = 0; $x < count($this->results); $x++) {
					$this->results[$x] = $this->_striplinks($this->results[$x]);
				}
			}
			else {
				$this->results = $this->_striplinks($this->results);
			}

			if ($this->expandlinks) {
				$this->results = $this->_expandlinks($this->results, $URI);
			}

			return true;
		}
		else {
			return false;
		}
	}

	public function fetchform($URI)
	{
		if ($this->fetch($URI)) {
			if (is_array($this->results)) {
				for ($x = 0; $x < count($this->results); $x++) {
					$this->results[$x] = $this->_stripform($this->results[$x]);
				}
			}
			else {
				$this->results = $this->_stripform($this->results);
			}

			return true;
		}
		else {
			return false;
		}
	}

	public function fetchtext($URI)
	{
		if ($this->fetch($URI)) {
			if (is_array($this->results)) {
				for ($x = 0; $x < count($this->results); $x++) {
					$this->results[$x] = $this->_striptext($this->results[$x]);
				}
			}
			else {
				$this->results = $this->_striptext($this->results);
			}

			return true;
		}
		else {
			return false;
		}
	}

	public function submitlinks($URI, $formvars = '', $formfiles = '')
	{
		if ($this->submit($URI, $formvars, $formfiles)) {
			if ($this->lastredirectaddr) {
				$URI = $this->lastredirectaddr;
			}

			if (is_array($this->results)) {
				for ($x = 0; $x < count($this->results); $x++) {
					$this->results[$x] = $this->_striplinks($this->results[$x]);

					if ($this->expandlinks) {
						$this->results[$x] = $this->_expandlinks($this->results[$x], $URI);
					}
				}
			}
			else {
				$this->results = $this->_striplinks($this->results);

				if ($this->expandlinks) {
					$this->results = $this->_expandlinks($this->results, $URI);
				}
			}

			return true;
		}
		else {
			return false;
		}
	}

	public function submittext($URI, $formvars = '', $formfiles = '')
	{
		if ($this->submit($URI, $formvars, $formfiles)) {
			if ($this->lastredirectaddr) {
				$URI = $this->lastredirectaddr;
			}

			if (is_array($this->results)) {
				for ($x = 0; $x < count($this->results); $x++) {
					$this->results[$x] = $this->_striptext($this->results[$x]);

					if ($this->expandlinks) {
						$this->results[$x] = $this->_expandlinks($this->results[$x], $URI);
					}
				}
			}
			else {
				$this->results = $this->_striptext($this->results);

				if ($this->expandlinks) {
					$this->results = $this->_expandlinks($this->results, $URI);
				}
			}

			return true;
		}
		else {
			return false;
		}
	}

	public function set_submit_multipart()
	{
		$this->_submit_type = 'multipart/form-data';
	}

	public function set_submit_normal()
	{
		$this->_submit_type = 'application/x-www-form-urlencoded';
	}

	public function _striplinks($document)
	{
		preg_match_all("'<\\s*a\\s.*?href\\s*=\\s*\t\t\t# find <a href=\r\n\t\t\t\t\t\t([\"\\'])?\t\t\t\t\t# find single or double quote\r\n\t\t\t\t\t\t(?(1) (.*?)\\1 | ([^\\s\\>]+))\t\t# if quote found, match up to next matching\r\n\t\t\t\t\t\t\t\t\t\t\t\t\t# quote, otherwise match up to next space\r\n\t\t\t\t\t\t'isx", $document, $links);

		while (list($key, $val) = each($links[2])) {
			if (!empty($val)) {
				$match[] = $val;
			}
		}

		while (list($key, $val) = each($links[3])) {
			if (!empty($val)) {
				$match[] = $val;
			}
		}

		return $match;
	}

	public function _stripform($document)
	{
		preg_match_all("'<\\/?(FORM|INPUT|SELECT|TEXTAREA|(OPTION))[^<>]*>(?(2)(.*(?=<\\/?(option|select)[^<>]*>[\r\n]*)|(?=[\r\n]*))|(?=[\r\n]*))'Usi", $document, $elements);
		$match = implode("\r\n", $elements[0]);
		return $match;
	}

	public function _striptext($document)
	{
		$search = array('\'<script[^>]*?>.*?</script>\'si', '\'<[\\/\\!]*?[^<>]*?>\'si', "'([\r\n])[\\s]+'", '\'&(quot|#34|#034|#x22);\'i', '\'&(amp|#38|#038|#x26);\'i', '\'&(lt|#60|#060|#x3c);\'i', '\'&(gt|#62|#062|#x3e);\'i', '\'&(nbsp|#160|#xa0);\'i', '\'&(iexcl|#161);\'i', '\'&(cent|#162);\'i', '\'&(pound|#163);\'i', '\'&(copy|#169);\'i', '\'&(reg|#174);\'i', '\'&(deg|#176);\'i', '\'&(#39|#039|#x27);\'', '\'&(euro|#8364);\'i', '\'&a(uml|UML);\'', '\'&o(uml|UML);\'', '\'&u(uml|UML);\'', '\'&A(uml|UML);\'', '\'&O(uml|UML);\'', '\'&U(uml|UML);\'', '\'&szlig;\'i');
		$replace = array('', '', '\\1', '"', '&', '<', '>', ' ', chr(161), chr(162), chr(163), chr(169), chr(174), chr(176), chr(39), chr(128), "\xe4", "\xf6", "\xfc", "\xc4", "\xd6", "\xdc", "\xdf");
		$text = preg_replace($search, $replace, $document);
		return $text;
	}

	public function _expandlinks($links, $URI)
	{
		preg_match('/^[^\\?]+/', $URI, $match);
		$match = preg_replace('|/[^\\/\\.]+\\.[^\\/\\.]+$|', '', $match[0]);
		$match = preg_replace('|/$|', '', $match);
		$match_part = parse_url($match);
		$match_root = $match_part['scheme'] . '://' . $match_part['host'];
		$search = array('|^http://' . preg_quote($this->host) . '|i', '|^(\\/)|i', '|^(?!http://)(?!mailto:)|i', '|/\\./|', '|/[^\\/]+/\\.\\./|');
		$replace = array('', $match_root . '/', $match . '/', '/', '/');
		$expandedLinks = preg_replace($search, $replace, $links);
		return $expandedLinks;
	}

	public function _httprequest($url, $fp, $URI, $http_method, $content_type = '', $body = '')
	{
		$cookie_headers = '';
		if ($this->passcookies && $this->_redirectaddr) {
			$this->setcookies();
		}

		$URI_PARTS = parse_url($URI);

		if (empty($url)) {
			$url = '/';
		}

		$headers = $http_method . ' ' . $url . ' ' . $this->_httpversion . "\r\n";

		if (!empty($this->agent)) {
			$headers .= 'User-Agent: ' . $this->agent . "\r\n";
		}

		if (!empty($this->host) && !isset($this->rawheaders['Host'])) {
			$headers .= 'Host: ' . $this->host;

			if (!empty($this->port)) {
				$headers .= ':' . $this->port;
			}

			$headers .= "\r\n";
		}

		if (!empty($this->accept)) {
			$headers .= 'Accept: ' . $this->accept . "\r\n";
		}

		if (!empty($this->referer)) {
			$headers .= 'Referer: ' . $this->referer . "\r\n";
		}

		if (!empty($this->cookies)) {
			if (!is_array($this->cookies)) {
				$this->cookies = (array) $this->cookies;
			}

			reset($this->cookies);

			if (0 < count($this->cookies)) {
				$cookie_headers .= 'Cookie: ';

				foreach ($this->cookies as $cookieKey => $cookieVal) {
					$cookie_headers .= $cookieKey . '=' . urlencode($cookieVal) . '; ';
				}

				$headers .= substr($cookie_headers, 0, -2) . "\r\n";
			}
		}

		if (!empty($this->rawheaders)) {
			if (!is_array($this->rawheaders)) {
				$this->rawheaders = (array) $this->rawheaders;
			}

			while (list($headerKey, $headerVal) = each($this->rawheaders)) {
				$headers .= $headerKey . ': ' . $headerVal . "\r\n";
			}
		}

		if (!empty($content_type)) {
			$headers .= 'Content-type: ' . $content_type;

			if ($content_type == 'multipart/form-data') {
				$headers .= '; boundary=' . $this->_mime_boundary;
			}

			$headers .= "\r\n";
		}

		if (!empty($body)) {
			$headers .= 'Content-length: ' . strlen($body) . "\r\n";
		}

		if (!empty($this->user) || !empty($this->pass)) {
			$headers .= 'Authorization: Basic ' . base64_encode($this->user . ':' . $this->pass) . "\r\n";
		}

		if (!empty($this->proxy_user)) {
			$headers .= 'Proxy-Authorization: ' . 'Basic ' . base64_encode($this->proxy_user . ':' . $this->proxy_pass) . "\r\n";
		}

		$headers .= "\r\n";

		if (0 < $this->read_timeout) {
			socket_set_timeout($fp, $this->read_timeout);
		}

		$this->timed_out = false;
		fwrite($fp, $headers . $body, strlen($headers . $body));
		$this->_redirectaddr = false;
		unset($this->headers);

		while ($currentHeader = fgets($fp, $this->_maxlinelen)) {
			if ((0 < $this->read_timeout) && $this->_check_timeout($fp)) {
				$this->status = -100;
				return false;
			}

			if ($currentHeader == "\r\n") {
				break;
			}

			if (preg_match('/^(Location:|URI:)/i', $currentHeader)) {
				preg_match('/^(Location:|URI:)[ ]+(.*)/i', chop($currentHeader), $matches);

				if (!preg_match('|\\:\\/\\/|', $matches[2])) {
					$this->_redirectaddr = $URI_PARTS['scheme'] . '://' . $this->host . ':' . $this->port;

					if (!preg_match('|^/|', $matches[2])) {
						$this->_redirectaddr .= '/' . $matches[2];
					}
					else {
						$this->_redirectaddr .= $matches[2];
					}
				}
				else {
					$this->_redirectaddr = $matches[2];
				}
			}

			if (preg_match('|^HTTP/|', $currentHeader)) {
				if (preg_match('|^HTTP/[^\\s]*\\s(.*?)\\s|', $currentHeader, $status)) {
					$this->status = $status[1];
				}

				$this->response_code = $currentHeader;
			}

			$this->headers[] = $currentHeader;
		}

		$results = '';

		do {
			$_data = fread($fp, $this->maxlength);

			if (strlen($_data) == 0) {
				break;
			}

			$results .= $_data;
		} while (true);

		if ((0 < $this->read_timeout) && $this->_check_timeout($fp)) {
			$this->status = -100;
			return false;
		}

		if (preg_match('\'<meta[\\s]*http-equiv[^>]*?content[\\s]*=[\\s]*["\\\']?\\d+;[\\s]*URL[\\s]*=[\\s]*([^"\\\']*?)["\\\']?>\'i', $results, $match)) {
			$this->_redirectaddr = $this->_expandlinks($match[1], $URI);
		}

		if (($this->_framedepth < $this->maxframes) && preg_match_all('\'<frame\\s+.*src[\\s]*=[\\\'"]?([^\\\'"\\>]+)\'i', $results, $match)) {
			$this->results[] = $results;

			for ($x = 0; $x < count($match[1]); $x++) {
				$this->_frameurls[] = $this->_expandlinks($match[1][$x], $URI_PARTS['scheme'] . '://' . $this->host);
			}
		}
		else if (is_array($this->results)) {
			$this->results[] = $results;
		}
		else {
			$this->results = $results;
		}

		return true;
	}

	public function _httpsrequest($url, $URI, $http_method, $content_type = '', $body = '')
	{
		if ($this->passcookies && $this->_redirectaddr) {
			$this->setcookies();
		}

		$headers = array();
		$URI_PARTS = parse_url($URI);

		if (empty($url)) {
			$url = '/';
		}

		if (!empty($this->agent)) {
			$headers[] = 'User-Agent: ' . $this->agent;
		}

		if (!empty($this->host)) {
			if (!empty($this->port)) {
				$headers[] = 'Host: ' . $this->host . ':' . $this->port;
			}
			else {
				$headers[] = 'Host: ' . $this->host;
			}
		}

		if (!empty($this->accept)) {
			$headers[] = 'Accept: ' . $this->accept;
		}

		if (!empty($this->referer)) {
			$headers[] = 'Referer: ' . $this->referer;
		}

		if (!empty($this->cookies)) {
			if (!is_array($this->cookies)) {
				$this->cookies = (array) $this->cookies;
			}

			reset($this->cookies);

			if (0 < count($this->cookies)) {
				$cookie_str = 'Cookie: ';

				foreach ($this->cookies as $cookieKey => $cookieVal) {
					$cookie_str .= $cookieKey . '=' . urlencode($cookieVal) . '; ';
				}

				$headers[] = substr($cookie_str, 0, -2);
			}
		}

		if (!empty($this->rawheaders)) {
			if (!is_array($this->rawheaders)) {
				$this->rawheaders = (array) $this->rawheaders;
			}

			while (list($headerKey, $headerVal) = each($this->rawheaders)) {
				$headers[] = $headerKey . ': ' . $headerVal;
			}
		}

		if (!empty($content_type)) {
			if ($content_type == 'multipart/form-data') {
				$headers[] = 'Content-type: ' . $content_type . '; boundary=' . $this->_mime_boundary;
			}
			else {
				$headers[] = 'Content-type: ' . $content_type;
			}
		}

		if (!empty($body)) {
			$headers[] = 'Content-length: ' . strlen($body);
		}

		if (!empty($this->user) || !empty($this->pass)) {
			$headers[] = 'Authorization: BASIC ' . base64_encode($this->user . ':' . $this->pass);
		}

		for ($curr_header = 0; $curr_header < count($headers); $curr_header++) {
			$safer_header = strtr($headers[$curr_header], '"', ' ');
			$cmdline_params .= ' -H "' . $safer_header . '"';
		}

		if (!empty($body)) {
			$cmdline_params .= ' -d "' . $body . '"';
		}

		if (0 < $this->read_timeout) {
			$cmdline_params .= ' -m ' . $this->read_timeout;
		}

		$headerfile = tempnam($temp_dir, 'sno');
		exec($this->curl_path . ' -k -D "' . $headerfile . '"' . $cmdline_params . ' "' . escapeshellcmd($URI) . '"', $results, $return);

		if ($return) {
			$this->error = 'Error: cURL could not retrieve the document, error ' . $return . '.';
			return false;
		}

		$results = implode("\r\n", $results);
		$result_headers = file($headerfile);
		$this->_redirectaddr = false;
		unset($this->headers);

		for ($currentHeader = 0; $currentHeader < count($result_headers); $currentHeader++) {
			if (preg_match('/^(Location: |URI: )/i', $result_headers[$currentHeader])) {
				preg_match('/^(Location: |URI:)\\s+(.*)/', chop($result_headers[$currentHeader]), $matches);

				if (!preg_match('|\\:\\/\\/|', $matches[2])) {
					$this->_redirectaddr = $URI_PARTS['scheme'] . '://' . $this->host . ':' . $this->port;

					if (!preg_match('|^/|', $matches[2])) {
						$this->_redirectaddr .= '/' . $matches[2];
					}
					else {
						$this->_redirectaddr .= $matches[2];
					}
				}
				else {
					$this->_redirectaddr = $matches[2];
				}
			}

			if (preg_match('|^HTTP/|', $result_headers[$currentHeader])) {
				$this->response_code = $result_headers[$currentHeader];
			}

			$this->headers[] = $result_headers[$currentHeader];
		}

		if (preg_match('\'<meta[\\s]*http-equiv[^>]*?content[\\s]*=[\\s]*["\\\']?\\d+;[\\s]*URL[\\s]*=[\\s]*([^"\\\']*?)["\\\']?>\'i', $results, $match)) {
			$this->_redirectaddr = $this->_expandlinks($match[1], $URI);
		}

		if (($this->_framedepth < $this->maxframes) && preg_match_all('\'<frame\\s+.*src[\\s]*=[\\\'"]?([^\\\'"\\>]+)\'i', $results, $match)) {
			$this->results[] = $results;

			for ($x = 0; $x < count($match[1]); $x++) {
				$this->_frameurls[] = $this->_expandlinks($match[1][$x], $URI_PARTS['scheme'] . '://' . $this->host);
			}
		}
		else if (is_array($this->results)) {
			$this->results[] = $results;
		}
		else {
			$this->results = $results;
		}

		unlink($headerfile);
		return true;
	}

	public function setcookies()
	{
		for ($x = 0; $x < count($this->headers); $x++) {
			if (preg_match('/^set-cookie:[\\s]+([^=]+)=([^;]+)/i', $this->headers[$x], $match)) {
				$this->cookies[$match[1]] = urldecode($match[2]);
			}
		}
	}

	public function _check_timeout($fp)
	{
		if (0 < $this->read_timeout) {
			$fp_status = socket_get_status($fp);

			if ($fp_status['timed_out']) {
				$this->timed_out = true;
				return true;
			}
		}

		return false;
	}

	public function _connect(&$fp)
	{
		if (!empty($this->proxy_host) && !empty($this->proxy_port)) {
			$this->_isproxy = true;
			$host = $this->proxy_host;
			$port = $this->proxy_port;
		}
		else {
			$host = $this->host;
			$port = $this->port;
		}

		$this->status = 0;

		if ($fp = fsockopen($host, $port, $errno, $errstr, $this->_fp_timeout)) {
			return true;
		}
		else {
			$this->status = $errno;

			switch ($errno) {
			case -3:
				$this->error = 'socket creation failed (-3)';
			case -4:
				$this->error = 'dns lookup failure (-4)';
			case -5:
				$this->error = 'connection refused or timed out (-5)';
			default:
				$this->error = 'connection failed (' . $errno . ')';
			}

			return false;
		}
	}

	public function _disconnect($fp)
	{
		return fclose($fp);
	}

	public function _prepare_post_body($formvars, $formfiles)
	{
		settype($formvars, 'array');
		settype($formfiles, 'array');
		$postdata = '';
		if ((count($formvars) == 0) && (count($formfiles) == 0)) {
			return NULL;
		}

		switch ($this->_submit_type) {
		case 'application/x-www-form-urlencoded':
			reset($formvars);

			while (list($key, $val) = each($formvars)) {
				if (is_array($val) || is_object($val)) {
					while (list($cur_key, $cur_val) = each($val)) {
						$postdata .= urlencode($key) . '[]=' . urlencode($cur_val) . '&';
					}
				}
				else {
					$postdata .= urlencode($key) . '=' . urlencode($val) . '&';
				}
			}

			break;

		case 'multipart/form-data':
			$this->_mime_boundary = 'Snoopy' . md5(uniqid(microtime()));
			reset($formvars);

			while (list($key, $val) = each($formvars)) {
				if (is_array($val) || is_object($val)) {
					while (list($cur_key, $cur_val) = each($val)) {
						$postdata .= '--' . $this->_mime_boundary . "\r\n";
						$postdata .= 'Content-Disposition: form-data; name="' . $key . "\\[\\]\"\r\n\r\n";
						$postdata .= $cur_val . "\r\n";
					}
				}
				else {
					$postdata .= '--' . $this->_mime_boundary . "\r\n";
					$postdata .= 'Content-Disposition: form-data; name="' . $key . "\"\r\n\r\n";
					$postdata .= $val . "\r\n";
				}
			}

			reset($formfiles);

			while (list($field_name, $file_names) = each($formfiles)) {
				settype($file_names, 'array');

				while (list(, $file_name) = each($file_names)) {
					if (!is_readable($file_name)) {
						continue;
					}

					$fp = fopen($file_name, 'r');
					$file_content = fread($fp, filesize($file_name));
					fclose($fp);
					$base_name = basename($file_name);
					$postdata .= '--' . $this->_mime_boundary . "\r\n";
					$postdata .= 'Content-Disposition: form-data; name="' . $field_name . '"; filename="' . $base_name . "\"\r\n\r\n";
					$postdata .= $file_content . "\r\n";
				}
			}

			$postdata .= '--' . $this->_mime_boundary . "--\r\n";
			break;
		}

		return $postdata;
	}
}


?>
