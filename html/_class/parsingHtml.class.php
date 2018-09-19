<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class HTML2PDF_parsingHtml
{
	const HTML_TAB = '        ';

	protected $_html = '';
	protected $_num = 0;
	protected $_level = 0;
	protected $_encoding = '';
	public $code = array();

	public function __construct($encoding = 'UTF-8')
	{
		$this->_num = 0;
		$this->_level = array($this->_num);
		$this->_html = '';
		$this->code = array();
		$this->setEncoding($encoding);
	}

	public function setEncoding($encoding)
	{
		$this->_encoding = $encoding;
	}

	public function setHTML($html)
	{
		$html = preg_replace('/<!--(.*)-->/isU', '', $html);
		$this->_html = $html;
	}

	public function parse()
	{
		$parents = array();
		$tagPreIn = false;
		$tagPreBr = array(
			'name'  => 'br',
			'close' => false,
			'param' => array(
				'style' => array(),
				'num'   => 0
				)
			);
		$tagsNotClosed = array('br', 'hr', 'img', 'col', 'input', 'link', 'option', 'circle', 'ellipse', 'path', 'rect', 'line', 'polygon', 'polyline');
		$tmp = array();
		$this->_searchCode($tmp);
		$actions = array();

		foreach ($tmp as $part) {
			if ($part[0] == 'code') {
				$res = $this->_analiseCode($part[1]);

				if ($res) {
					$res['html_pos'] = $part[2];

					if (!in_array($res['name'], $tagsNotClosed)) {
						if ($res['close']) {
							if (count($parents) < 1) {
								throw new HTML2PDF_exception(3, $res['name'], $this->getHtmlErrorCode($res['html_pos']));
							}
							else if ($parents[count($parents) - 1] != $res['name']) {
								throw new HTML2PDF_exception(4, $parents, $this->getHtmlErrorCode($res['html_pos']));
							}
							else {
								unset($parents[count($parents) - 1]);
							}
						}
						else if ($res['autoclose']) {
							$actions[] = $res;
							$res['params'] = array();
							$res['close'] = true;
						}
						else {
							$parents[count($parents)] = $res['name'];
						}

						if ((($res['name'] == 'pre') || ($res['name'] == 'code')) && !$res['autoclose']) {
							$tagPreIn = !$res['close'];
						}
					}

					$actions[] = $res;
				}
				else {
					$part[0] = 'txt';
				}
			}

			if ($part[0] == 'txt') {
				if (!$tagPreIn) {
					$actions[] = array(
	'name'  => 'write',
	'close' => false,
	'param' => array('txt' => $this->_prepareTxt($part[1]))
	);
				}
				else {
					$part[1] = str_replace("\r", '', $part[1]);
					$part[1] = explode("\n", $part[1]);

					foreach ($part[1] as $k => $txt) {
						$txt = str_replace('	', self::HTML_TAB, $txt);
						$txt = str_replace(' ', '&nbsp;', $txt);

						if (0 < $k) {
							$actions[] = $tagPreBr;
						}

						$actions[] = array(
	'name'  => 'write',
	'close' => false,
	'param' => array('txt' => $this->_prepareTxt($txt, false))
	);
					}
				}
			}
		}

		$tagsToClean = array('page', 'page_header', 'page_footer', 'form', 'table', 'thead', 'tfoot', 'tr', 'td', 'th', 'br', 'div', 'hr', 'p', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'bookmark', 'fieldset', 'legend', 'draw', 'circle', 'ellipse', 'path', 'rect', 'line', 'g', 'polygon', 'polyline', 'option');
		$nb = count($actions);

		for ($k = 0; $k < $nb; $k++) {
			if ($actions[$k]['name'] == 'write') {
				if ((0 < $k) && in_array($actions[$k - 1]['name'], $tagsToClean)) {
					$actions[$k]['param']['txt'] = ltrim($actions[$k]['param']['txt']);
				}

				if (($k < ($nb - 1)) && in_array($actions[$k + 1]['name'], $tagsToClean)) {
					$actions[$k]['param']['txt'] = rtrim($actions[$k]['param']['txt']);
				}

				if (!strlen($actions[$k]['param']['txt'])) {
					unset($actions[$k]);
				}
			}
		}

		if (count($parents)) {
			throw new HTML2PDF_exception(5, $parents);
		}

		$this->code = array_values($actions);
	}

	protected function _prepareTxt($txt, $spaces = true)
	{
		if ($spaces) {
			$txt = preg_replace('/\\s+/is', ' ', $txt);
		}

		$txt = str_replace('&euro;', '€', $txt);
		$txt = html_entity_decode($txt, ENT_QUOTES, $this->_encoding);
		return $txt;
	}

	protected function _searchCode(&$tmp)
	{
		$tmp = array();
		$reg = '/(<[^>]+>)|([^<]+)+/isU';
		$str = '';
		$offset = 0;

		while (preg_match($reg, $this->_html, $parse, PREG_OFFSET_CAPTURE, $offset)) {
			if ($parse[1][0]) {
				if ($str !== '') {
					$tmp[] = array('txt', $str);
				}

				$tmp[] = array('code', trim($parse[1][0]), $offset);
				$str = '';
			}
			else {
				$str .= $parse[2][0];
			}

			$offset = $parse[0][1] + strlen($parse[0][0]);
			unset($parse);
		}

		if ($str != '') {
			$tmp[] = array('txt', $str);
		}

		unset($str);
	}

	protected function _analiseCode($code)
	{
		$tag = '<([\\/]{0,1})([_a-z0-9]+)([\\/>\\s]+)';

		if (!preg_match('/' . $tag . '/isU', $code, $match)) {
			return NULL;
		}

		$close = ($match[1] == '/' ? true : false);
		$autoclose = preg_match('/\\/>$/isU', $code);
		$name = strtolower($match[2]);
		$param = array();
		$param['style'] = '';

		if ($name == 'img') {
			$param['alt'] = '';
			$param['src'] = '';
		}

		if ($name == 'a') {
			$param['href'] = '';
		}

		$prop = '([a-zA-Z0-9_]+)=([^"\'\\s>]+)';
		preg_match_all('/' . $prop . '/is', $code, $match);

		for ($k = 0; $k < count($match[0]); $k++) {
			$param[trim(strtolower($match[1][$k]))] = trim($match[2][$k]);
		}

		$prop = '([a-zA-Z0-9_]+)=["]([^"]*)["]';
		preg_match_all('/' . $prop . '/is', $code, $match);

		for ($k = 0; $k < count($match[0]); $k++) {
			$param[trim(strtolower($match[1][$k]))] = trim($match[2][$k]);
		}

		$prop = '([a-zA-Z0-9_]+)=[\']([^\']*)[\']';
		preg_match_all('/' . $prop . '/is', $code, $match);

		for ($k = 0; $k < count($match[0]); $k++) {
			$param[trim(strtolower($match[1][$k]))] = trim($match[2][$k]);
		}

		$color = '#000000';
		$border = NULL;

		foreach ($param as $key => $val) {
			$key = strtolower($key);

			switch ($key) {
			case 'width':
				unset($param[$key]);
				$param['style'] .= 'width: ' . $val . 'px; ';
				break;

			case 'align':
				if ($name === 'img') {
					unset($param[$key]);
					$param['style'] .= 'float: ' . $val . '; ';
				}
				else if ($name !== 'table') {
					unset($param[$key]);
					$param['style'] .= 'text-align: ' . $val . '; ';
				}

				break;

			case 'valign':
				unset($param[$key]);
				$param['style'] .= 'vertical-align: ' . $val . '; ';
				break;

			case 'height':
				unset($param[$key]);
				$param['style'] .= 'height: ' . $val . 'px; ';
				break;

			case 'bgcolor':
				unset($param[$key]);
				$param['style'] .= 'background: ' . $val . '; ';
				break;

			case 'bordercolor':
				unset($param[$key]);
				$color = $val;
				break;

			case 'border':
				unset($param[$key]);

				if (preg_match('/^[0-9]+$/isU', $val)) {
					$val = $val . 'px';
				}

				$border = $val;
				break;

			case 'cellpadding':
			case 'cellspacing':
				if (preg_match('/^([0-9]+)$/isU', $val)) {
					$param[$key] = $val . 'px';
				}

				break;

			case 'colspan':
			case 'rowspan':
				$val = preg_replace('/[^0-9]/isU', '', $val);

				if (!$val) {
					$val = 1;
				}

				$param[$key] = $val;
				break;
			}
		}

		if ($border !== NULL) {
			if ($border) {
				$border = 'border: solid ' . $border . ' ' . $color;
			}
			else {
				$border = 'border: none';
			}

			$param['style'] .= $border . '; ';
			$param['border'] = $border;
		}

		$styles = explode(';', $param['style']);
		$param['style'] = array();

		foreach ($styles as $style) {
			$tmp = explode(':', $style);

			if (1 < count($tmp)) {
				$cod = $tmp[0];
				unset($tmp[0]);
				$tmp = implode(':', $tmp);
				$param['style'][trim(strtolower($cod))] = preg_replace('/[\\s]+/isU', ' ', trim($tmp));
			}
		}

		if (in_array($name, array('ul', 'ol', 'table')) && !$close) {
			$this->_num++;
			$this->_level[count($this->_level)] = $this->_num;
		}

		if (!isset($param['num'])) {
			$param['num'] = $this->_level[count($this->_level) - 1];
		}

		if (in_array($name, array('ul', 'ol', 'table')) && $close) {
			unset($this->_level[count($this->_level) - 1]);
		}

		if (isset($param['value'])) {
			$param['value'] = $this->_prepareTxt($param['value']);
		}

		if (isset($param['alt'])) {
			$param['alt'] = $this->_prepareTxt($param['alt']);
		}

		if (isset($param['title'])) {
			$param['title'] = $this->_prepareTxt($param['title']);
		}

		if (isset($param['class'])) {
			$param['class'] = $this->_prepareTxt($param['class']);
		}

		return array('name' => $name, 'close' => $close ? 1 : 0, 'autoclose' => $autoclose, 'param' => $param);
	}

	public function getLevel($k)
	{
		if (!isset($this->code[$k])) {
			return array();
		}

		$detect = $this->code[$k]['name'];

		if ($detect == 'write') {
			return array($this->code[$k]);
		}

		$level = 0;
		$end = false;
		$code = array();

		while (!$end) {
			$row = $this->code[$k];

			if ($row['name'] == 'write') {
				$code[] = $row;
			}
			else {
				$not = false;

				if ($row['name'] == $detect) {
					if ($level == 0) {
						$not = true;
					}

					$level += ($row['close'] ? -1 : 1);

					if ($level == 0) {
						$not = true;
						$end = true;
					}
				}

				if (!$not) {
					if (isset($row['style']['text-align'])) {
						unset($row['style']['text-align']);
					}

					$code[] = $row;
				}
			}

			if (isset($this->code[$k + 1])) {
				$k++;
			}
			else {
				$end = true;
			}
		}

		return $code;
	}

	public function getHtmlErrorCode($pos, $before = 30, $after = 40)
	{
		return substr($this->_html, $pos - $before, $before + $after);
	}
}


?>
