<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class HTML2PDF_parsingCss
{
	/**
     * reference to the pdf object
     * @var TCPDF
     */
	protected $_pdf;
	protected $_htmlColor = array();
	protected $_onlyLeft = false;
	protected $_defaultFont;
	public $value = array();
	public $css = array();
	public $cssKeys = array();
	public $table = array();

	public function __construct(&$pdf)
	{
		$this->_init();
		$this->setPdfParent($pdf);
	}

	public function setPdfParent(&$pdf)
	{
		$this->_pdf = &$pdf;
	}

	public function setOnlyLeft()
	{
		$this->value['text-align'] = 'left';
		$this->_onlyLeft = true;
	}

	public function getOldValues()
	{
		return isset($this->table[count($this->table) - 1]) ? $this->table[count($this->table) - 1] : $this->value;
	}

	public function setDefaultFont($default = NULL)
	{
		$old = $this->_defaultFont;
		$this->_defaultFont = $default;

		if ($default) {
			$this->value['font-family'] = $default;
		}

		return $old;
	}

	protected function _init()
	{
		require K_PATH_MAIN . 'htmlcolors.php';
		$this->_htmlColor = $webcolor;
		$this->table = array();
		$this->value = array();
		$this->initStyle();
		$this->resetStyle();
	}

	public function initStyle()
	{
		$this->value['id_tag'] = 'body';
		$this->value['id_name'] = NULL;
		$this->value['id_id'] = NULL;
		$this->value['id_class'] = NULL;
		$this->value['id_lst'] = array('*');
		$this->value['mini-size'] = 1;
		$this->value['mini-decal'] = 0;
		$this->value['font-family'] = 'Arial';
		$this->value['font-bold'] = false;
		$this->value['font-italic'] = false;
		$this->value['font-underline'] = false;
		$this->value['font-overline'] = false;
		$this->value['font-linethrough'] = false;
		$this->value['text-transform'] = 'none';
		$this->value['font-size'] = $this->convertToMM('10pt');
		$this->value['text-indent'] = 0;
		$this->value['text-align'] = 'left';
		$this->value['vertical-align'] = 'middle';
		$this->value['line-height'] = 'normal';
		$this->value['position'] = NULL;
		$this->value['x'] = NULL;
		$this->value['y'] = NULL;
		$this->value['width'] = 0;
		$this->value['height'] = 0;
		$this->value['top'] = NULL;
		$this->value['right'] = NULL;
		$this->value['bottom'] = NULL;
		$this->value['left'] = NULL;
		$this->value['float'] = NULL;
		$this->value['display'] = NULL;
		$this->value['rotate'] = NULL;
		$this->value['overflow'] = 'visible';
		$this->value['color'] = array(0, 0, 0);
		$this->value['background'] = array('color' => NULL, 'image' => NULL, 'position' => NULL, 'repeat' => NULL);
		$this->value['border'] = array();
		$this->value['padding'] = array();
		$this->value['margin'] = array();
		$this->value['margin-auto'] = false;
		$this->value['list-style-type'] = '';
		$this->value['list-style-image'] = '';
		$this->value['xc'] = NULL;
		$this->value['yc'] = NULL;
	}

	public function resetStyle($tagName = '')
	{
		$border = $this->readBorder('solid 1px #000000');
		$units = array('1px' => $this->convertToMM('1px'), '5px' => $this->convertToMM('5px'));
		$collapse = (isset($this->value['border']['collapse']) ? $this->value['border']['collapse'] : false);

		if (!in_array($tagName, array('tr', 'td', 'th', 'thead', 'tbody', 'tfoot'))) {
			$collapse = false;
		}

		$this->value['position'] = NULL;
		$this->value['x'] = NULL;
		$this->value['y'] = NULL;
		$this->value['width'] = 0;
		$this->value['height'] = 0;
		$this->value['top'] = NULL;
		$this->value['right'] = NULL;
		$this->value['bottom'] = NULL;
		$this->value['left'] = NULL;
		$this->value['float'] = NULL;
		$this->value['display'] = NULL;
		$this->value['rotate'] = NULL;
		$this->value['overflow'] = 'visible';
		$this->value['background'] = array('color' => NULL, 'image' => NULL, 'position' => NULL, 'repeat' => NULL);
		$this->value['border'] = array(
	't'        => $this->readBorder('none'),
	'r'        => $this->readBorder('none'),
	'b'        => $this->readBorder('none'),
	'l'        => $this->readBorder('none'),
	'radius'   => array(
		'tl' => array(0, 0),
		'tr' => array(0, 0),
		'br' => array(0, 0),
		'bl' => array(0, 0)
		),
	'collapse' => $collapse
	);

		if (!in_array($tagName, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'))) {
			$this->value['margin'] = array('t' => 0, 'r' => 0, 'b' => 0, 'l' => 0);
		}

		if (in_array($tagName, array('input', 'select', 'textarea'))) {
			$this->value['border']['t'] = NULL;
			$this->value['border']['r'] = NULL;
			$this->value['border']['b'] = NULL;
			$this->value['border']['l'] = NULL;
		}

		if ($tagName == 'p') {
			$this->value['margin']['t'] = NULL;
			$this->value['margin']['b'] = NULL;
		}

		if ($tagName == 'blockquote') {
			$this->value['margin']['t'] = 3;
			$this->value['margin']['r'] = 3;
			$this->value['margin']['b'] = 3;
			$this->value['margin']['l'] = 6;
		}

		$this->value['margin-auto'] = false;

		if (in_array($tagName, array('blockquote', 'div', 'fieldset'))) {
			$this->value['vertical-align'] = 'top';
		}

		if (in_array($tagName, array('fieldset', 'legend'))) {
			$this->value['border'] = array(
	't'        => $border,
	'r'        => $border,
	'b'        => $border,
	'l'        => $border,
	'radius'   => array(
		'tl' => array($units['5px'], $units['5px']),
		'tr' => array($units['5px'], $units['5px']),
		'br' => array($units['5px'], $units['5px']),
		'bl' => array($units['5px'], $units['5px'])
		),
	'collapse' => false
	);
		}

		if (in_array($tagName, array('ul', 'li'))) {
			$this->value['list-style-type'] = '';
			$this->value['list-style-image'] = '';
		}

		if (!in_array($tagName, array('tr', 'td'))) {
			$this->value['padding'] = array('t' => 0, 'r' => 0, 'b' => 0, 'l' => 0);
		}
		else {
			$this->value['padding'] = array('t' => $units['1px'], 'r' => $units['1px'], 'b' => $units['1px'], 'l' => $units['1px']);
		}

		if ($tagName == 'hr') {
			$this->value['border'] = array(
	't'        => $border,
	'r'        => $border,
	'b'        => $border,
	'l'        => $border,
	'radius'   => array(
		'tl' => array(0, 0),
		'tr' => array(0, 0),
		'br' => array(0, 0),
		'bl' => array(0, 0)
		),
	'collapse' => false
	);
			$this->convertBackground('#FFFFFF', $this->value['background']);
		}

		$this->value['xc'] = NULL;
		$this->value['yc'] = NULL;
	}

	public function fontSet()
	{
		$family = strtolower($this->value['font-family']);
		$b = ($this->value['font-bold'] ? 'B' : '');
		$i = ($this->value['font-italic'] ? 'I' : '');
		$u = ($this->value['font-underline'] ? 'U' : '');
		$d = ($this->value['font-linethrough'] ? 'D' : '');
		$o = ($this->value['font-overline'] ? 'O' : '');
		$style = $b . $i;

		if ($this->_defaultFont) {
			if ($family == 'arial') {
				$family = 'helvetica';
			}
			else {
				if (($family == 'symbol') || ($family == 'zapfdingbats')) {
					$style = '';
				}
			}

			$fontkey = $family . $style;

			if (!$this->_pdf->isLoadedFont($fontkey)) {
				$family = $this->_defaultFont;
			}
		}

		if ($family == 'arial') {
			$family = 'helvetica';
		}
		else {
			if (($family == 'symbol') || ($family == 'zapfdingbats')) {
				$style = '';
			}
		}

		$style .= $u . $d . $o;
		$size = $this->value['font-size'];
		$size = (72 * $size) / 25.399999999999999;
		$this->_pdf->SetFont($family, $style, $this->value['mini-size'] * $size);
		$this->_pdf->setTextColorArray($this->value['color']);

		if ($this->value['background']['color']) {
			$this->_pdf->setFillColorArray($this->value['background']['color']);
		}
		else {
			$this->_pdf->setFillColor(255);
		}
	}

	public function save()
	{
		array_push($this->table, $this->value);
	}

	public function load()
	{
		if (count($this->table)) {
			$this->value = array_pop($this->table);
		}
	}

	public function restorePosition()
	{
		if ($this->value['y'] == $this->_pdf->getY()) {
			$this->_pdf->setY($this->value['yc'], false);
		}
	}

	public function setPosition()
	{
		$currentX = $this->_pdf->getX();
		$currentY = $this->_pdf->getY();
		$this->value['xc'] = $currentX;
		$this->value['yc'] = $currentY;
		if (($this->value['position'] == 'relative') || ($this->value['position'] == 'absolute')) {
			if ($this->value['right'] !== NULL) {
				$x = $this->getLastWidth(true) - $this->value['right'] - $this->value['width'];

				if ($this->value['margin']['r']) {
					$x -= $this->value['margin']['r'];
				}
			}
			else {
				$x = $this->value['left'];

				if ($this->value['margin']['l']) {
					$x += $this->value['margin']['l'];
				}
			}

			if ($this->value['bottom'] !== NULL) {
				$y = $this->getLastHeight(true) - $this->value['bottom'] - $this->value['height'];

				if ($this->value['margin']['b']) {
					$y -= $this->value['margin']['b'];
				}
			}
			else {
				$y = $this->value['top'];

				if ($this->value['margin']['t']) {
					$y += $this->value['margin']['t'];
				}
			}

			if ($this->value['position'] == 'relative') {
				$this->value['x'] = $currentX + $x;
				$this->value['y'] = $currentY + $y;
			}
			else {
				$this->value['x'] = $this->_getLastAbsoluteX() + $x;
				$this->value['y'] = $this->_getLastAbsoluteY() + $y;
			}
		}
		else {
			$this->value['x'] = $currentX;
			$this->value['y'] = $currentY;

			if ($this->value['margin']['l']) {
				$this->value['x'] += $this->value['margin']['l'];
			}

			if ($this->value['margin']['t']) {
				$this->value['y'] += $this->value['margin']['t'];
			}
		}

		$this->_pdf->setXY($this->value['x'], $this->value['y']);
	}

	public function getFormStyle()
	{
		$prop = array();
		$prop['alignment'] = $this->value['text-align'];
		if (isset($this->value['background']['color']) && is_array($this->value['background']['color'])) {
			$prop['fillColor'] = $this->value['background']['color'];
		}

		if (isset($this->value['border']['t']['color'])) {
			$prop['strokeColor'] = $this->value['border']['t']['color'];
		}

		if (isset($this->value['border']['t']['width'])) {
			$prop['lineWidth'] = $this->value['border']['t']['width'];
		}

		if (isset($this->value['border']['t']['type'])) {
			$prop['borderStyle'] = $this->value['border']['t']['type'];
		}

		if (!empty($this->value['color'])) {
			$prop['textColor'] = $this->value['color'];
		}

		if (!empty($this->value['font-size'])) {
			$prop['textSize'] = $this->value['font-size'];
		}

		return $prop;
	}

	public function getSvgStyle($tagName, &$param)
	{
		$tagName = strtolower($tagName);
		$id = (isset($param['id']) ? strtolower(trim($param['id'])) : NULL);

		if (!$id) {
			$id = NULL;
		}

		$name = (isset($param['name']) ? strtolower(trim($param['name'])) : NULL);

		if (!$name) {
			$name = NULL;
		}

		$class = array();
		$tmp = (isset($param['class']) ? strtolower(trim($param['class'])) : '');
		$tmp = explode(' ', $tmp);

		foreach ($tmp as $k => $v) {
			$v = trim($v);

			if ($v) {
				$class[] = $v;
			}
		}

		$this->value['id_tag'] = $tagName;
		$this->value['id_name'] = $name;
		$this->value['id_id'] = $id;
		$this->value['id_class'] = $class;
		$this->value['id_lst'] = array();
		$this->value['id_lst'][] = '*';
		$this->value['id_lst'][] = $tagName;

		if (!isset($this->value['svg'])) {
			$this->value['svg'] = array('stroke' => NULL, 'stroke-width' => $this->convertToMM('1pt'), 'fill' => NULL, 'fill-opacity' => NULL);
		}

		if (count($class)) {
			foreach ($class as $v) {
				$this->value['id_lst'][] = '*.' . $v;
				$this->value['id_lst'][] = '.' . $v;
				$this->value['id_lst'][] = $tagName . '.' . $v;
			}
		}

		if ($id) {
			$this->value['id_lst'][] = '*#' . $id;
			$this->value['id_lst'][] = '#' . $id;
			$this->value['id_lst'][] = $tagName . '#' . $id;
		}

		$styles = $this->_getFromCSS();
		$styles = array_merge($styles, $param['style']);

		if (isset($styles['stroke'])) {
			$this->value['svg']['stroke'] = $this->convertToColor($styles['stroke'], $res);
		}

		if (isset($styles['stroke-width'])) {
			$this->value['svg']['stroke-width'] = $this->convertToMM($styles['stroke-width']);
		}

		if (isset($styles['fill'])) {
			$this->value['svg']['fill'] = $this->convertToColor($styles['fill'], $res);
		}

		if (isset($styles['fill-opacity'])) {
			$this->value['svg']['fill-opacity'] = 1 * $styles['fill-opacity'];
		}

		return $this->value['svg'];
	}

	public function analyse($tagName, &$param, $legacy = NULL)
	{
		$tagName = strtolower($tagName);
		$id = (isset($param['id']) ? strtolower(trim($param['id'])) : NULL);

		if (!$id) {
			$id = NULL;
		}

		$name = (isset($param['name']) ? strtolower(trim($param['name'])) : NULL);

		if (!$name) {
			$name = NULL;
		}

		$class = array();
		$tmp = (isset($param['class']) ? strtolower(trim($param['class'])) : '');
		$tmp = explode(' ', $tmp);

		foreach ($tmp as $k => $v) {
			$v = trim($v);

			if ($v) {
				$class[] = $v;
			}
		}

		$this->value['id_tag'] = $tagName;
		$this->value['id_name'] = $name;
		$this->value['id_id'] = $id;
		$this->value['id_class'] = $class;
		$this->value['id_lst'] = array();
		$this->value['id_lst'][] = '*';
		$this->value['id_lst'][] = $tagName;

		if (count($class)) {
			foreach ($class as $v) {
				$this->value['id_lst'][] = '*.' . $v;
				$this->value['id_lst'][] = '.' . $v;
				$this->value['id_lst'][] = $tagName . '.' . $v;
			}
		}

		if ($id) {
			$this->value['id_lst'][] = '*#' . $id;
			$this->value['id_lst'][] = '#' . $id;
			$this->value['id_lst'][] = $tagName . '#' . $id;
		}

		$styles = $this->_getFromCSS();
		$styles = array_merge($styles, $param['style']);
		if (isset($param['allwidth']) && !isset($styles['width'])) {
			$styles['width'] = '100%';
		}

		$this->resetStyle($tagName);

		if ($legacy) {
			foreach ($legacy as $legacyName => $legacyValue) {
				if (is_array($legacyValue)) {
					foreach ($legacyValue as $legacy2Name => $legacy2Value) {
						$this->value[$legacyName][$legacy2Name] = $legacy2Value;
					}
				}
				else {
					$this->value[$legacyName] = $legacyValue;
				}
			}
		}

		$correctWidth = false;
		$noWidth = true;

		foreach ($styles as $nom => $val) {
			switch ($nom) {
			case 'font-family':
				$val = explode(',', $val);
				$val = trim($val[0]);

				if ($val) {
					$this->value['font-family'] = $val;
				}

				break;

			case 'font-weight':
				$this->value['font-bold'] = $val == 'bold';
				break;

			case 'font-style':
				$this->value['font-italic'] = $val == 'italic';
				break;

			case 'text-decoration':
				$val = explode(' ', $val);
				$this->value['font-underline'] = in_array('underline', $val);
				$this->value['font-overline'] = in_array('overline', $val);
				$this->value['font-linethrough'] = in_array('line-through', $val);
				break;

			case 'text-indent':
				$this->value['text-indent'] = $this->convertToMM($val);
				break;

			case 'text-transform':
				if (!in_array($val, array('none', 'capitalize', 'uppercase', 'lowercase'))) {
					$val = 'none';
				}

				$this->value['text-transform'] = $val;
				break;

			case 'font-size':
				$val = $this->convertToMM($val, $this->value['font-size']);

				if ($val) {
					$this->value['font-size'] = $val;
				}

				break;

			case 'color':
				$res = NULL;
				$this->value['color'] = $this->convertToColor($val, $res);

				if ($tagName == 'hr') {
					$this->value['border']['l']['color'] = $this->value['color'];
					$this->value['border']['t']['color'] = $this->value['color'];
					$this->value['border']['r']['color'] = $this->value['color'];
					$this->value['border']['b']['color'] = $this->value['color'];
				}

				break;

			case 'text-align':
				$val = strtolower($val);

				if (!in_array($val, array('left', 'right', 'center', 'justify', 'li_right'))) {
					$val = 'left';
				}

				$this->value['text-align'] = $val;
				break;

			case 'vertical-align':
				$this->value['vertical-align'] = $val;
				break;

			case 'width':
				$this->value['width'] = $this->convertToMM($val, $this->getLastWidth());
				if ($this->value['width'] && (substr($val, -1) == '%')) {
					$correctWidth = true;
				}

				$noWidth = false;
				break;

			case 'height':
				$this->value['height'] = $this->convertToMM($val, $this->getLastHeight());
				break;

			case 'line-height':
				if (preg_match('/^[0-9\\.]+$/isU', $val)) {
					$val = floor($val * 100) . '%';
				}

				$this->value['line-height'] = $val;
				break;

			case 'rotate':
				if (!in_array($val, array(0, -90, 90, 180, 270, -180, -270))) {
					$val = NULL;
				}

				if ($val < 0) {
					$val += 360;
				}

				$this->value['rotate'] = $val;
				break;

			case 'overflow':
				if (!in_array($val, array('visible', 'hidden'))) {
					$val = 'visible';
				}

				$this->value['overflow'] = $val;
				break;

			case 'padding':
				$val = explode(' ', $val);

				foreach ($val as $k => $v) {
					$v = trim($v);

					if ($v != '') {
						$val[$k] = $v;
					}
					else {
						unset($val[$k]);
					}
				}

				$val = array_values($val);
				$this->_duplicateBorder($val);
				$this->value['padding']['t'] = $this->convertToMM($val[0], 0);
				$this->value['padding']['r'] = $this->convertToMM($val[1], 0);
				$this->value['padding']['b'] = $this->convertToMM($val[2], 0);
				$this->value['padding']['l'] = $this->convertToMM($val[3], 0);
				break;

			case 'padding-top':
				$this->value['padding']['t'] = $this->convertToMM($val, 0);
				break;

			case 'padding-right':
				$this->value['padding']['r'] = $this->convertToMM($val, 0);
				break;

			case 'padding-bottom':
				$this->value['padding']['b'] = $this->convertToMM($val, 0);
				break;

			case 'padding-left':
				$this->value['padding']['l'] = $this->convertToMM($val, 0);
				break;

			case 'margin':
				if ($val == 'auto') {
					$this->value['margin-auto'] = true;
					break;
				}

				$val = explode(' ', $val);

				foreach ($val as $k => $v) {
					$v = trim($v);

					if ($v != '') {
						$val[$k] = $v;
					}
					else {
						unset($val[$k]);
					}
				}

				$val = array_values($val);
				$this->_duplicateBorder($val);
				$this->value['margin']['t'] = $this->convertToMM($val[0], 0);
				$this->value['margin']['r'] = $this->convertToMM($val[1], 0);
				$this->value['margin']['b'] = $this->convertToMM($val[2], 0);
				$this->value['margin']['l'] = $this->convertToMM($val[3], 0);
				break;

			case 'margin-top':
				$this->value['margin']['t'] = $this->convertToMM($val, 0);
				break;

			case 'margin-right':
				$this->value['margin']['r'] = $this->convertToMM($val, 0);
				break;

			case 'margin-bottom':
				$this->value['margin']['b'] = $this->convertToMM($val, 0);
				break;

			case 'margin-left':
				$this->value['margin']['l'] = $this->convertToMM($val, 0);
				break;

			case 'border':
				$val = $this->readBorder($val);
				$this->value['border']['t'] = $val;
				$this->value['border']['r'] = $val;
				$this->value['border']['b'] = $val;
				$this->value['border']['l'] = $val;
				break;

			case 'border-style':
				$val = explode(' ', $val);

				foreach ($val as $valK => $valV) {
					if (!in_array($valV, array('solid', 'dotted', 'dashed'))) {
						$val[$valK] = NULL;
					}
				}

				$this->_duplicateBorder($val);

				if ($val[0]) {
					$this->value['border']['t']['type'] = $val[0];
				}

				if ($val[1]) {
					$this->value['border']['r']['type'] = $val[1];
				}

				if ($val[2]) {
					$this->value['border']['b']['type'] = $val[2];
				}

				if ($val[3]) {
					$this->value['border']['l']['type'] = $val[3];
				}

				break;

			case 'border-top-style':
				if (in_array($val, array('solid', 'dotted', 'dashed'))) {
					$this->value['border']['t']['type'] = $val;
				}

				break;

			case 'border-right-style':
				if (in_array($val, array('solid', 'dotted', 'dashed'))) {
					$this->value['border']['r']['type'] = $val;
				}

				break;

			case 'border-bottom-style':
				if (in_array($val, array('solid', 'dotted', 'dashed'))) {
					$this->value['border']['b']['type'] = $val;
				}

				break;

			case 'border-left-style':
				if (in_array($val, array('solid', 'dotted', 'dashed'))) {
					$this->value['border']['l']['type'] = $val;
				}

				break;

			case 'border-color':
				$res = false;
				$val = preg_replace('/,[\\s]+/', ',', $val);
				$val = explode(' ', $val);

				foreach ($val as $valK => $valV) {
					$val[$valK] = $this->convertToColor($valV, $res);

					if (!$res) {
						$val[$valK] = NULL;
					}
				}

				$this->_duplicateBorder($val);

				if (is_array($val[0])) {
					$this->value['border']['t']['color'] = $val[0];
				}

				if (is_array($val[1])) {
					$this->value['border']['r']['color'] = $val[1];
				}

				if (is_array($val[2])) {
					$this->value['border']['b']['color'] = $val[2];
				}

				if (is_array($val[3])) {
					$this->value['border']['l']['color'] = $val[3];
				}

				break;

			case 'border-top-color':
				$res = false;
				$val = $this->convertToColor($val, $res);

				if ($res) {
					$this->value['border']['t']['color'] = $val;
				}

				break;

			case 'border-right-color':
				$res = false;
				$val = $this->convertToColor($val, $res);

				if ($res) {
					$this->value['border']['r']['color'] = $val;
				}

				break;

			case 'border-bottom-color':
				$res = false;
				$val = $this->convertToColor($val, $res);

				if ($res) {
					$this->value['border']['b']['color'] = $val;
				}

				break;

			case 'border-left-color':
				$res = false;
				$val = $this->convertToColor($val, $res);

				if ($res) {
					$this->value['border']['l']['color'] = $val;
				}

				break;

			case 'border-width':
				$val = explode(' ', $val);

				foreach ($val as $valK => $valV) {
					$val[$valK] = $this->convertToMM($valV, 0);
				}

				$this->_duplicateBorder($val);

				if ($val[0]) {
					$this->value['border']['t']['width'] = $val[0];
				}

				if ($val[1]) {
					$this->value['border']['r']['width'] = $val[1];
				}

				if ($val[2]) {
					$this->value['border']['b']['width'] = $val[2];
				}

				if ($val[3]) {
					$this->value['border']['l']['width'] = $val[3];
				}

				break;

			case 'border-top-width':
				$val = $this->convertToMM($val, 0);

				if ($val) {
					$this->value['border']['t']['width'] = $val;
				}

				break;

			case 'border-right-width':
				$val = $this->convertToMM($val, 0);

				if ($val) {
					$this->value['border']['r']['width'] = $val;
				}

				break;

			case 'border-bottom-width':
				$val = $this->convertToMM($val, 0);

				if ($val) {
					$this->value['border']['b']['width'] = $val;
				}

				break;

			case 'border-left-width':
				$val = $this->convertToMM($val, 0);

				if ($val) {
					$this->value['border']['l']['width'] = $val;
				}

				break;

			case 'border-collapse':
				if ($tagName == 'table') {
					$this->value['border']['collapse'] = $val == 'collapse';
				}

				break;

			case 'border-radius':
				$val = explode('/', $val);

				if (2 < count($val)) {
					break;
				}

				$valH = $this->convertToRadius(trim($val[0]));
				if ((count($valH) < 1) || (4 < count($valH))) {
					break;
				}

				if (!isset($valH[1])) {
					$valH[1] = $valH[0];
				}

				if (!isset($valH[2])) {
					$valH = array($valH[0], $valH[0], $valH[1], $valH[1]);
				}

				if (!isset($valH[3])) {
					$valH[3] = $valH[1];
				}

				if (isset($val[1])) {
					$valV = $this->convertToRadius(trim($val[1]));
					if ((count($valV) < 1) || (4 < count($valV))) {
						break;
					}

					if (!isset($valV[1])) {
						$valV[1] = $valV[0];
					}

					if (!isset($valV[2])) {
						$valV = array($valV[0], $valV[0], $valV[1], $valV[1]);
					}

					if (!isset($valV[3])) {
						$valV[3] = $valV[1];
					}
				}
				else {
					$valV = $valH;
				}

				$this->value['border']['radius'] = array(
	'tl' => array($valH[0], $valV[0]),
	'tr' => array($valH[1], $valV[1]),
	'br' => array($valH[2], $valV[2]),
	'bl' => array($valH[3], $valV[3])
	);
				break;

			case 'border-top-left-radius':
				$val = $this->convertToRadius($val);
				if ((count($val) < 1) || (2 < count($val))) {
					break;
				}

				$this->value['border']['radius']['tl'] = array($val[0], isset($val[1]) ? $val[1] : $val[0]);
				break;

			case 'border-top-right-radius':
				$val = $this->convertToRadius($val);
				if ((count($val) < 1) || (2 < count($val))) {
					break;
				}

				$this->value['border']['radius']['tr'] = array($val[0], isset($val[1]) ? $val[1] : $val[0]);
				break;

			case 'border-bottom-right-radius':
				$val = $this->convertToRadius($val);
				if ((count($val) < 1) || (2 < count($val))) {
					break;
				}

				$this->value['border']['radius']['br'] = array($val[0], isset($val[1]) ? $val[1] : $val[0]);
				break;

			case 'border-bottom-left-radius':
				$val = $this->convertToRadius($val);
				if ((count($val) < 1) || (2 < count($val))) {
					break;
				}

				$this->value['border']['radius']['bl'] = array($val[0], isset($val[1]) ? $val[1] : $val[0]);
				break;

			case 'border-top':
				$this->value['border']['t'] = $this->readBorder($val);
				break;

			case 'border-right':
				$this->value['border']['r'] = $this->readBorder($val);
				break;

			case 'border-bottom':
				$this->value['border']['b'] = $this->readBorder($val);
				break;

			case 'border-left':
				$this->value['border']['l'] = $this->readBorder($val);
				break;

			case 'background-color':
				$this->value['background']['color'] = $this->convertBackgroundColor($val);
				break;

			case 'background-image':
				$this->value['background']['image'] = $this->convertBackgroundImage($val);
				break;

			case 'background-position':
				$res = NULL;
				$this->value['background']['position'] = $this->convertBackgroundPosition($val, $res);
				break;

			case 'background-repeat':
				$this->value['background']['repeat'] = $this->convertBackgroundRepeat($val);
				break;

			case 'background':
				$this->convertBackground($val, $this->value['background']);
				break;

			case 'position':
				if ($val == 'absolute') {
					$this->value['position'] = 'absolute';
				}
				else if ($val == 'relative') {
					$this->value['position'] = 'relative';
				}
				else {
					$this->value['position'] = NULL;
				}

				break;

			case 'float':
				if ($val == 'left') {
					$this->value['float'] = 'left';
				}
				else if ($val == 'right') {
					$this->value['float'] = 'right';
				}
				else {
					$this->value['float'] = NULL;
				}

				break;

			case 'display':
				if ($val == 'inline') {
					$this->value['display'] = 'inline';
				}
				else if ($val == 'block') {
					$this->value['display'] = 'block';
				}
				else if ($val == 'none') {
					$this->value['display'] = 'none';
				}
				else {
					$this->value['display'] = NULL;
				}

				break;

			case 'top':
			case 'bottom':
			case 'left':
			case 'right':
				$this->value[$nom] = $val;
				break;

			case 'list-style':
			case 'list-style-type':
			case 'list-style-image':
				if ($nom == 'list-style') {
					$nom = 'list-style-type';
				}

				$this->value[$nom] = $val;
				break;

			default:
				break;
			}
		}

		$return = true;

		if ($this->value['margin']['t'] === NULL) {
			$this->value['margin']['t'] = $this->value['font-size'];
		}

		if ($this->value['margin']['b'] === NULL) {
			$this->value['margin']['b'] = $this->value['font-size'];
		}

		if ($this->_onlyLeft) {
			$this->value['text-align'] = 'left';
		}

		if ($noWidth && in_array($tagName, array('div', 'blockquote', 'fieldset')) && ($this->value['position'] != 'absolute')) {
			$this->value['width'] = $this->getLastWidth();
			$this->value['width'] -= $this->value['margin']['l'] + $this->value['margin']['r'];
		}
		else if ($correctWidth) {
			if (!in_array($tagName, array('table', 'div', 'blockquote', 'fieldset', 'hr'))) {
				$this->value['width'] -= $this->value['padding']['l'] + $this->value['padding']['r'];
				$this->value['width'] -= $this->value['border']['l']['width'] + $this->value['border']['r']['width'];
			}

			if (in_array($tagName, array('th', 'td'))) {
				$this->value['width'] -= $this->convertToMM(isset($param['cellspacing']) ? $param['cellspacing'] : '2px');
				$return = false;
			}

			if ($this->value['width'] < 0) {
				$this->value['width'] = 0;
			}
		}
		else if ($this->value['width']) {
			if ($this->value['border']['l']['width']) {
				$this->value['width'] += $this->value['border']['l']['width'];
			}

			if ($this->value['border']['r']['width']) {
				$this->value['width'] += $this->value['border']['r']['width'];
			}

			if ($this->value['padding']['l']) {
				$this->value['width'] += $this->value['padding']['l'];
			}

			if ($this->value['padding']['r']) {
				$this->value['width'] += $this->value['padding']['r'];
			}
		}

		if ($this->value['height']) {
			if ($this->value['border']['b']['width']) {
				$this->value['height'] += $this->value['border']['b']['width'];
			}

			if ($this->value['border']['t']['width']) {
				$this->value['height'] += $this->value['border']['t']['width'];
			}

			if ($this->value['padding']['b']) {
				$this->value['height'] += $this->value['padding']['b'];
			}

			if ($this->value['padding']['t']) {
				$this->value['height'] += $this->value['padding']['t'];
			}
		}

		if ($this->value['top'] != NULL) {
			$this->value['top'] = $this->convertToMM($this->value['top'], $this->getLastHeight(true));
		}

		if ($this->value['bottom'] != NULL) {
			$this->value['bottom'] = $this->convertToMM($this->value['bottom'], $this->getLastHeight(true));
		}

		if ($this->value['left'] != NULL) {
			$this->value['left'] = $this->convertToMM($this->value['left'], $this->getLastWidth(true));
		}

		if ($this->value['right'] != NULL) {
			$this->value['right'] = $this->convertToMM($this->value['right'], $this->getLastWidth(true));
		}

		if ($this->value['top'] && $this->value['bottom'] && $this->value['height']) {
			$this->value['bottom'] = NULL;
		}

		if ($this->value['left'] && $this->value['right'] && $this->value['width']) {
			$this->value['right'] = NULL;
		}

		return $return;
	}

	public function getLineHeight()
	{
		$val = $this->value['line-height'];

		if ($val == 'normal') {
			$val = '108%';
		}

		return $this->convertToMM($val, $this->value['font-size']);
	}

	public function getLastWidth($mode = false)
	{
		for ($k = count($this->table) - 1; 0 <= $k; $k--) {
			if ($this->table[$k]['width']) {
				$w = $this->table[$k]['width'];

				if ($mode) {
					$w += $this->table[$k]['border']['l']['width'] + $this->table[$k]['padding']['l'] + 0.02;
					$w += $this->table[$k]['border']['r']['width'] + $this->table[$k]['padding']['r'] + 0.02;
				}

				return $w;
			}
		}

		return $this->_pdf->getW() - $this->_pdf->getlMargin() - $this->_pdf->getrMargin();
	}

	public function getLastHeight($mode = false)
	{
		for ($k = count($this->table) - 1; 0 <= $k; $k--) {
			if ($this->table[$k]['height']) {
				$h = $this->table[$k]['height'];

				if ($mode) {
					$h += $this->table[$k]['border']['t']['width'] + $this->table[$k]['padding']['t'] + 0.02;
					$h += $this->table[$k]['border']['b']['width'] + $this->table[$k]['padding']['b'] + 0.02;
				}

				return $h;
			}
		}

		return $this->_pdf->getH() - $this->_pdf->gettMargin() - $this->_pdf->getbMargin();
	}

	public function getFloat()
	{
		if ($this->value['float'] == 'left') {
			return 'left';
		}

		if ($this->value['float'] == 'right') {
			return 'right';
		}

		return NULL;
	}

	public function getLastValue($key)
	{
		$nb = count($this->table);

		if (0 < $nb) {
			return $this->table[$nb - 1][$key];
		}
		else {
			return NULL;
		}
	}

	protected function _getLastAbsoluteX()
	{
		for ($k = count($this->table) - 1; 0 <= $k; $k--) {
			if ($this->table[$k]['x'] && $this->table[$k]['position']) {
				return $this->table[$k]['x'];
			}
		}

		return $this->_pdf->getlMargin();
	}

	protected function _getLastAbsoluteY()
	{
		for ($k = count($this->table) - 1; 0 <= $k; $k--) {
			if ($this->table[$k]['y'] && $this->table[$k]['position']) {
				return $this->table[$k]['y'];
			}
		}

		return $this->_pdf->gettMargin();
	}

	protected function _getFromCSS()
	{
		$styles = array();
		$getit = array();
		$lst = array();
		$lst[] = $this->value['id_lst'];

		for ($i = count($this->table) - 1; 0 <= $i; $i--) {
			$lst[] = $this->table[$i]['id_lst'];
		}

		foreach ($this->cssKeys as $key => $num) {
			if ($this->_getReccursiveStyle($key, $lst)) {
				$getit[$key] = $num;
			}
		}

		if (count($getit)) {
			asort($getit);

			foreach ($getit as $key => $val) {
				$styles = array_merge($styles, $this->css[$key]);
			}
		}

		return $styles;
	}

	protected function _getReccursiveStyle($key, $lst, $next = NULL)
	{
		if ($next !== NULL) {
			if ($next) {
				$key = trim(substr($key, 0, 0 - strlen($next)));
			}

			array_shift($lst);

			if (!count($lst)) {
				return false;
			}
		}

		foreach ($lst[0] as $name) {
			if ($key == $name) {
				return true;
			}

			if ((substr($key, 0 - strlen(' ' . $name)) == (' ' . $name)) && $this->_getReccursiveStyle($key, $lst, $name)) {
				return true;
			}
		}

		if (($next !== NULL) && $this->_getReccursiveStyle($key, $lst, '')) {
			return true;
		}

		return false;
	}

	public function readBorder($css)
	{
		$none = array(
			'type'  => 'none',
			'width' => 0,
			'color' => array(0, 0, 0)
			);
		$type = 'solid';
		$width = $this->convertToMM('1pt');
		$color = array(0, 0, 0);
		$css = explode(' ', $css);

		foreach ($css as $k => $v) {
			$v = trim($v);

			if ($v) {
				$css[$k] = $v;
			}
			else {
				unset($css[$k]);
			}
		}

		$css = array_values($css);
		$res = NULL;

		foreach ($css as $value) {
			if (($value == 'none') || ($value == 'hidden')) {
				return $none;
			}

			$tmp = $this->convertToMM($value);

			if ($tmp !== NULL) {
				$width = $tmp;
			}
			else if (in_array($value, array('solid', 'dotted', 'dashed', 'double'))) {
				$type = $value;
			}
			else {
				$tmp = $this->convertToColor($value, $res);

				if ($res) {
					$color = $tmp;
				}
			}
		}

		if (!$width) {
			return $none;
		}

		return array('type' => $type, 'width' => $width, 'color' => $color);
	}

	protected function _duplicateBorder(&$val)
	{
		if (count($val) == 1) {
			$val[1] = $val[0];
			$val[2] = $val[0];
			$val[3] = $val[0];
		}
		else if (count($val) == 2) {
			$val[2] = $val[0];
			$val[3] = $val[1];
		}
		else if (count($val) == 3) {
			$val[3] = $val[1];
		}
	}

	public function convertBackground($css, &$value)
	{
		$text = '/url\\(([^)]*)\\)/isU';

		if (preg_match($text, $css, $match)) {
			$value['image'] = $this->convertBackgroundImage($match[0]);
			$css = preg_replace($text, '', $css);
			$css = preg_replace('/[\\s]+/', ' ', $css);
		}

		$css = preg_replace('/,[\\s]+/', ',', $css);
		$css = explode(' ', $css);
		$pos = '';

		foreach ($css as $val) {
			$ok = false;
			$color = $this->convertToColor($val, $ok);

			if ($ok) {
				$value['color'] = $color;
			}
			else if ($val == 'transparent') {
				$value['color'] = NULL;
			}
			else {
				$repeat = $this->convertBackgroundRepeat($val);

				if ($repeat) {
					$value['repeat'] = $repeat;
				}
				else {
					$pos .= ($pos ? ' ' : '') . $val;
				}
			}
		}

		if ($pos) {
			$pos = $this->convertBackgroundPosition($pos, $ok);

			if ($ok) {
				$value['position'] = $pos;
			}
		}
	}

	public function convertBackgroundColor($css)
	{
		$res = NULL;

		if ($css == 'transparent') {
			return NULL;
		}
		else {
			return $this->convertToColor($css, $res);
		}
	}

	public function convertBackgroundImage($css)
	{
		if ($css == 'none') {
			return NULL;
		}
		else if (preg_match('/^url\\(([^)]*)\\)$/isU', $css, $match)) {
			return $match[1];
		}
		else {
			return NULL;
		}
	}

	public function convertBackgroundPosition($css, &$res)
	{
		$res = false;
		$css = explode(' ', $css);

		if (count($css) < 2) {
			if (!$css[0]) {
				return NULL;
			}

			$css[1] = 'center';
		}

		if (2 < count($css)) {
			return NULL;
		}

		$x = 0;
		$y = 0;
		$res = true;

		if ($css[0] == 'left') {
			$x = '0%';
		}
		else if ($css[0] == 'center') {
			$x = '50%';
		}
		else if ($css[0] == 'right') {
			$x = '100%';
		}
		else if ($css[0] == 'top') {
			$y = '0%';
		}
		else if ($css[0] == 'bottom') {
			$y = '100%';
		}
		else if (preg_match('/^[-]?[0-9\\.]+%$/isU', $css[0])) {
			$x = $css[0];
		}
		else if ($this->convertToMM($css[0])) {
			$x = $this->convertToMM($css[0]);
		}
		else {
			$res = false;
		}

		if ($css[1] == 'left') {
			$x = '0%';
		}
		else if ($css[1] == 'right') {
			$x = '100%';
		}
		else if ($css[1] == 'top') {
			$y = '0%';
		}
		else if ($css[1] == 'center') {
			$y = '50%';
		}
		else if ($css[1] == 'bottom') {
			$y = '100%';
		}
		else if (preg_match('/^[-]?[0-9\\.]+%$/isU', $css[1])) {
			$y = $css[1];
		}
		else if ($this->convertToMM($css[1])) {
			$y = $this->convertToMM($css[1]);
		}
		else {
			$res = false;
		}

		return array($x, $y);
	}

	public function convertBackgroundRepeat($css)
	{
		switch ($css) {
		case 'repeat':
			return array(true, true);
		case 'repeat-x':
			return array(true, false);
		case 'repeat-y':
			return array(false, true);
		case 'no-repeat':
			return array(false, false);
		}

		return NULL;
	}

	public function convertToMM($css, $old = 0)
	{
		$css = trim($css);

		if (preg_match('/^[0-9\\.\\-]+$/isU', $css)) {
			$css .= 'px';
		}

		if (preg_match('/^[0-9\\.\\-]+px$/isU', $css)) {
			$css = (25.399999999999999 / 96) * str_replace('px', '', $css);
		}
		else if (preg_match('/^[0-9\\.\\-]+pt$/isU', $css)) {
			$css = (25.399999999999999 / 72) * str_replace('pt', '', $css);
		}
		else if (preg_match('/^[0-9\\.\\-]+in$/isU', $css)) {
			$css = 25.399999999999999 * str_replace('in', '', $css);
		}
		else if (preg_match('/^[0-9\\.\\-]+mm$/isU', $css)) {
			$css = 1 * str_replace('mm', '', $css);
		}
		else if (preg_match('/^[0-9\\.\\-]+%$/isU', $css)) {
			$css = (1 * $old * str_replace('%', '', $css)) / 100;
		}
		else {
			$css = NULL;
		}

		return $css;
	}

	public function convertToRadius($css)
	{
		$css = explode(' ', $css);

		foreach ($css as $k => $v) {
			$v = trim($v);

			if ($v) {
				$v = $this->convertToMM($v, 0);

				if ($v !== NULL) {
					$css[$k] = $v;
				}
				else {
					unset($css[$k]);
				}
			}
			else {
				unset($css[$k]);
			}
		}

		return array_values($css);
	}

	public function convertToColor($css, &$res)
	{
		$css = trim($css);
		$res = true;

		if (strtolower($css) == 'transparent') {
			return array(NULL, NULL, NULL);
		}

		if (isset($this->_htmlColor[strtolower($css)])) {
			$css = $this->_htmlColor[strtolower($css)];
			$r = floatVal(hexdec(substr($css, 0, 2)));
			$v = floatVal(hexdec(substr($css, 2, 2)));
			$b = floatVal(hexdec(substr($css, 4, 2)));
			return array($r, $v, $b);
		}

		if (preg_match('/^#[0-9A-Fa-f]{6}$/isU', $css)) {
			$r = floatVal(hexdec(substr($css, 1, 2)));
			$v = floatVal(hexdec(substr($css, 3, 2)));
			$b = floatVal(hexdec(substr($css, 5, 2)));
			return array($r, $v, $b);
		}

		if (preg_match('/^#[0-9A-F]{3}$/isU', $css)) {
			$r = floatVal(hexdec(substr($css, 1, 1) . substr($css, 1, 1)));
			$v = floatVal(hexdec(substr($css, 2, 1) . substr($css, 2, 1)));
			$b = floatVal(hexdec(substr($css, 3, 1) . substr($css, 3, 1)));
			return array($r, $v, $b);
		}

		if (preg_match('/rgb\\([\\s]*([0-9%\\.]+)[\\s]*,[\\s]*([0-9%\\.]+)[\\s]*,[\\s]*([0-9%\\.]+)[\\s]*\\)/isU', $css, $match)) {
			$r = $this->_convertSubColor($match[1]);
			$v = $this->_convertSubColor($match[2]);
			$b = $this->_convertSubColor($match[3]);
			return array($r * 255, $v * 255, $b * 255);
		}

		if (preg_match('/cmyk\\([\\s]*([0-9%\\.]+)[\\s]*,[\\s]*([0-9%\\.]+)[\\s]*,[\\s]*([0-9%\\.]+)[\\s]*,[\\s]*([0-9%\\.]+)[\\s]*\\)/isU', $css, $match)) {
			$c = $this->_convertSubColor($match[1]);
			$m = $this->_convertSubColor($match[2]);
			$y = $this->_convertSubColor($match[3]);
			$k = $this->_convertSubColor($match[4]);
			return array($c * 100, $m * 100, $y * 100, $k * 100);
		}

		$res = false;
		return array(0, 0, 0);
	}

	protected function _convertSubColor($c)
	{
		if (substr($c, -1) == '%') {
			$c = floatVal(substr($c, 0, -1)) / 100;
		}
		else {
			$c = floatVal($c);

			if (1 < $c) {
				$c = $c / 255;
			}
		}

		return $c;
	}

	protected function _analyseStyle(&$code)
	{
		$code = preg_replace('/[\\s]+/', ' ', $code);
		$code = preg_replace('/\\/\\*.*?\\*\\//s', '', $code);
		preg_match_all('/([^{}]+){([^}]*)}/isU', $code, $match);

		for ($k = 0; $k < count($match[0]); $k++) {
			$names = strtolower(trim($match[1][$k]));
			$styles = trim($match[2][$k]);
			$styles = explode(';', $styles);
			$css = array();

			foreach ($styles as $style) {
				$tmp = explode(':', $style);

				if (1 < count($tmp)) {
					$cod = $tmp[0];
					unset($tmp[0]);
					$tmp = implode(':', $tmp);
					$css[trim(strtolower($cod))] = trim($tmp);
				}
			}

			$names = explode(',', $names);

			foreach ($names as $name) {
				$name = trim($name);

				if (strpos($name, ':') !== false) {
					continue;
				}

				if (!isset($this->css[$name])) {
					$this->css[$name] = $css;
				}
				else {
					$this->css[$name] = array_merge($this->css[$name], $css);
				}
			}
		}

		$this->cssKeys = array_flip(array_keys($this->css));
	}

	public function readStyle(&$html)
	{
		$style = ' ';
		preg_match_all('/<link([^>]*)>/isU', $html, $match);
		$html = preg_replace('/<link[^>]*>/isU', '', $html);
		$html = preg_replace('/<\\/link[^>]*>/isU', '', $html);

		foreach ($match[1] as $code) {
			$tmp = array();
			$prop = '([a-zA-Z0-9_]+)=([^"\'\\s>]+)';
			preg_match_all('/' . $prop . '/is', $code, $match);

			for ($k = 0; $k < count($match[0]); $k++) {
				$tmp[trim(strtolower($match[1][$k]))] = trim($match[2][$k]);
			}

			$prop = '([a-zA-Z0-9_]+)=["]([^"]*)["]';
			preg_match_all('/' . $prop . '/is', $code, $match);

			for ($k = 0; $k < count($match[0]); $k++) {
				$tmp[trim(strtolower($match[1][$k]))] = trim($match[2][$k]);
			}

			$prop = '([a-zA-Z0-9_]+)=[\']([^\']*)[\']';
			preg_match_all('/' . $prop . '/is', $code, $match);

			for ($k = 0; $k < count($match[0]); $k++) {
				$tmp[trim(strtolower($match[1][$k]))] = trim($match[2][$k]);
			}

			if (isset($tmp['type']) && (strtolower($tmp['type']) == 'text/css') && isset($tmp['href'])) {
				$url = $tmp['href'];
				$content = @file_get_contents($url);

				if (strpos($url, 'http://') !== false) {
					$url = str_replace('http://', '', $url);
					$url = explode('/', $url);
					$urlMain = 'http://' . $url[0] . '/';
					$urlSelf = $url;
					unset($urlSelf[count($urlSelf) - 1]);
					$urlSelf = 'http://' . implode('/', $urlSelf) . '/';
					$content = preg_replace('/url\\(([^\\\\][^)]*)\\)/isU', 'url(' . $urlSelf . '$1)', $content);
					$content = preg_replace('/url\\((\\\\[^)]*)\\)/isU', 'url(' . $urlMain . '$1)', $content);
				}

				$style .= $content . "\n";
			}
		}

		preg_match_all('/<style[^>]*>(.*)<\\/style[^>]*>/isU', $html, $match);
		$html = preg_replace('/<style[^>]*>(.*)<\\/style[^>]*>/isU', '', $html);

		foreach ($match[1] as $code) {
			$code = str_replace('<!--', '', $code);
			$code = str_replace('-->', '', $code);
			$style .= $code . "\n";
		}

		$this->_analyseStyle($style);
	}
}


?>
