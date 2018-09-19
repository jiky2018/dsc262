<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
if (!defined('__CLASS_HTML2PDF__')) {
	define('__CLASS_HTML2PDF__', '4.03');
	define('HTML2PDF_USED_TCPDF_VERSION', '5.0.002');
	require_once dirname(__FILE__) . '/_class/exception.class.php';
	require_once dirname(__FILE__) . '/_class/locale.class.php';
	require_once dirname(__FILE__) . '/_class/myPdf.class.php';
	require_once dirname(__FILE__) . '/_class/parsingHtml.class.php';
	require_once dirname(__FILE__) . '/_class/parsingCss.class.php';
	class HTML2PDF
	{
		/**
         * HTML2PDF_myPdf object, extends from TCPDF
         * @var HTML2PDF_myPdf
         */
		public $pdf;
		/**
         * CSS parsing
         * @var HTML2PDF_parsingCss
         */
		public $parsingCss;
		/**
         * HTML parsing
         * @var HTML2PDF_parsingHtml
         */
		public $parsingHtml;
		protected $_langue = 'fr';
		protected $_orientation = 'P';
		protected $_format = 'A4';
		protected $_encoding = '';
		protected $_unicode = true;
		protected $_testTdInOnepage = true;
		protected $_testIsImage = true;
		protected $_testIsDeprecated = false;
		protected $_parsePos = 0;
		protected $_tempPos = 0;
		protected $_page = 0;
		protected $_subHtml;
		protected $_subPart = false;
		protected $_subHEADER = array();
		protected $_subFOOTER = array();
		protected $_subSTATES = array();
		protected $_isSubPart = false;
		protected $_isInThead = false;
		protected $_isInTfoot = false;
		protected $_isInOverflow = false;
		protected $_isInFooter = false;
		protected $_isInDraw;
		protected $_isAfterFloat = false;
		protected $_isInForm = false;
		protected $_isInLink = '';
		protected $_isInParagraph = false;
		protected $_isForOneLine = false;
		protected $_maxX = 0;
		protected $_maxY = 0;
		protected $_maxE = 0;
		protected $_maxH = 0;
		protected $_maxSave = array();
		protected $_currentH = 0;
		protected $_defaultLeft = 0;
		protected $_defaultTop = 0;
		protected $_defaultRight = 0;
		protected $_defaultBottom = 0;
		protected $_defaultFont;
		protected $_margeLeft = 0;
		protected $_margeTop = 0;
		protected $_margeRight = 0;
		protected $_margeBottom = 0;
		protected $_marges = array();
		protected $_pageMarges = array();
		protected $_background = array();
		protected $_firstPage = true;
		protected $_defList = array();
		protected $_lstAnchor = array();
		protected $_lstField = array();
		protected $_lstSelect = array();
		protected $_previousCall;
		protected $_debugActif = false;
		protected $_debugOkUsage = false;
		protected $_debugOkPeak = false;
		protected $_debugLevel = 0;
		protected $_debugStartTime = 0;
		protected $_debugLastTime = 0;
		static protected $_subobj;
		static protected $_tables = array();

		public function __construct($orientation = 'P', $format = 'A4', $langue = 'fr', $unicode = true, $encoding = 'UTF-8', $marges = array(5, 5, 5, 8))
		{
			$this->_page = 0;
			$this->_firstPage = true;
			$this->_orientation = $orientation;
			$this->_format = $format;
			$this->_langue = strtolower($langue);
			$this->_unicode = $unicode;
			$this->_encoding = $encoding;
			HTML2PDF_locale::load($this->_langue);
			$this->pdf = new HTML2PDF_myPdf($orientation, 'mm', $format, $unicode, $encoding);
			$this->parsingCss = new HTML2PDF_parsingCss($this->pdf);
			$this->parsingCss->fontSet();
			$this->_defList = array();
			$this->setTestTdInOnePage(true);
			$this->setTestIsImage(true);
			$this->setTestIsDeprecated(true);
			$this->setDefaultFont(NULL);
			$this->parsingHtml = new HTML2PDF_parsingHtml($this->_encoding);
			$this->_subHtml = NULL;
			$this->_subPart = false;

			if (!is_array($marges)) {
				$marges = array($marges, $marges, $marges, $marges);
			}

			$this->_setDefaultMargins($marges[0], $marges[1], $marges[2], $marges[3]);
			$this->_setMargins();
			$this->_marges = array();
			$this->_lstField = array();
			return $this;
		}

		public function __destruct()
		{
		}

		public function __clone()
		{
			$this->pdf = clone $this->pdf;
			$this->parsingHtml = clone $this->parsingHtml;
			$this->parsingCss = clone $this->parsingCss;
			$this->parsingCss->setPdfParent($this->pdf);
		}

		public function setModeDebug()
		{
			$time = microtime(true);
			$this->_debugActif = true;
			$this->_debugOkUsage = function_exists('memory_get_usage');
			$this->_debugOkPeak = function_exists('memory_get_peak_usage');
			$this->_debugStartTime = $time;
			$this->_debugLastTime = $time;
			$this->_DEBUG_stepline('step', 'time', 'delta', 'memory', 'peak');
			$this->_DEBUG_add('Init debug');
			return $this;
		}

		public function setTestTdInOnePage($mode = true)
		{
			$this->_testTdInOnepage = $mode ? true : false;
			return $this;
		}

		public function setTestIsImage($mode = true)
		{
			$this->_testIsImage = $mode ? true : false;
			return $this;
		}

		public function setTestIsDeprecated($mode = true)
		{
			$this->_testIsDeprecated = $mode ? true : false;
			return $this;
		}

		public function setDefaultFont($default = NULL)
		{
			$this->_defaultFont = $default;
			$this->parsingCss->setDefaultFont($default);
			return $this;
		}

		public function addFont($family, $style = '', $file = '')
		{
			$this->pdf->AddFont($family, $style, $file);
			return $this;
		}

		public function createIndex($titre = 'Index', $sizeTitle = 20, $sizeBookmark = 15, $bookmarkTitle = true, $displayPage = true, $onPage = NULL, $fontName = 'helvetica')
		{
			$oldPage = $this->_INDEX_NewPage($onPage);
			$this->pdf->createIndex($this, $titre, $sizeTitle, $sizeBookmark, $bookmarkTitle, $displayPage, $onPage, $fontName);

			if ($oldPage) {
				$this->pdf->setPage($oldPage);
			}
		}

		protected function _cleanUp()
		{
			HTML2PDF::$_subobj = NULL;
			HTML2PDF::$_tables = array();
		}

		public function Output($name = '', $dest = false)
		{
			$this->_cleanUp();

			if ($this->_debugActif) {
				$this->_DEBUG_add('Before output');
				$this->pdf->Close();
				exit();
			}

			if ($dest === false) {
				$dest = 'I';
			}

			if ($dest === true) {
				$dest = 'S';
			}

			if ($dest === '') {
				$dest = 'I';
			}

			if ($name == '') {
				$name = 'document.pdf';
			}

			$dest = strtoupper($dest);

			if (!in_array($dest, array('I', 'D', 'F', 'S', 'FI', 'FD'))) {
				$dest = 'I';
			}

			if (strtolower(substr($name, -4)) != '.pdf') {
				throw new HTML2PDF_exception(0, 'The output document name "' . $name . '" is not a PDF name');
			}

			return $this->pdf->Output($name, $dest);
		}

		public function writeHTML($html, $debugVue = false)
		{
			if (preg_match('/<body/isU', $html)) {
				$html = $this->getHtmlFromPage($html);
			}

			$html = str_replace('[[date_y]]', date('Y'), $html);
			$html = str_replace('[[date_m]]', date('m'), $html);
			$html = str_replace('[[date_d]]', date('d'), $html);
			$html = str_replace('[[date_h]]', date('H'), $html);
			$html = str_replace('[[date_i]]', date('i'), $html);
			$html = str_replace('[[date_s]]', date('s'), $html);

			if ($debugVue) {
				return $this->_vueHTML($html);
			}

			$this->parsingCss->readStyle($html);
			$this->parsingHtml->setHTML($html);
			$this->parsingHtml->parse();
			$this->_makeHTMLcode();
		}

		public function getHtmlFromPage($html)
		{
			$html = str_replace('<BODY', '<body', $html);
			$html = str_replace('</BODY', '</body', $html);
			$res = explode('<body', $html);

			if (count($res) < 2) {
				return $html;
			}

			$content = '<page' . $res[1];
			$content = explode('</body', $content);
			$content = $content[0] . '</page>';
			preg_match_all('/<link([^>]*)>/isU', $html, $match);

			foreach ($match[0] as $src) {
				$content = $src . '</link>' . $content;
			}

			preg_match_all('/<style[^>]*>(.*)<\\/style[^>]*>/isU', $html, $match);

			foreach ($match[0] as $src) {
				$content = $src . $content;
			}

			return $content;
		}

		public function initSubHtml($format, $orientation, $marge, $page, $defLIST, $myLastPageGroup, $myLastPageGroupNb)
		{
			$this->_isSubPart = true;
			$this->parsingCss->setOnlyLeft();
			$this->_setNewPage($format, $orientation, NULL, NULL, $myLastPageGroup !== NULL);
			$this->_saveMargin(0, 0, $marge);
			$this->_defList = $defLIST;
			$this->_page = $page;
			$this->pdf->setMyLastPageGroup($myLastPageGroup);
			$this->pdf->setMyLastPageGroupNb($myLastPageGroupNb);
			$this->pdf->setXY(0, 0);
			$this->parsingCss->fontSet();
		}

		protected function _vueHTML($content)
		{
			$content = preg_replace('/<page_header([^>]*)>/isU', '<hr>' . HTML2PDF_locale::get('vue01') . ' : $1<hr><div$1>', $content);
			$content = preg_replace('/<page_footer([^>]*)>/isU', '<hr>' . HTML2PDF_locale::get('vue02') . ' : $1<hr><div$1>', $content);
			$content = preg_replace('/<page([^>]*)>/isU', '<hr>' . HTML2PDF_locale::get('vue03') . ' : $1<hr><div$1>', $content);
			$content = preg_replace('/<\\/page([^>]*)>/isU', '</div><hr>', $content);
			$content = preg_replace('/<bookmark([^>]*)>/isU', '<hr>bookmark : $1<hr>', $content);
			$content = preg_replace('/<\\/bookmark([^>]*)>/isU', '', $content);
			$content = preg_replace('/<barcode([^>]*)>/isU', '<hr>barcode : $1<hr>', $content);
			$content = preg_replace('/<\\/barcode([^>]*)>/isU', '', $content);
			$content = preg_replace('/<qrcode([^>]*)>/isU', '<hr>qrcode : $1<hr>', $content);
			$content = preg_replace('/<\\/qrcode([^>]*)>/isU', '', $content);
			echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n<html>\n    <head>\n        <title>" . HTML2PDF_locale::get('vue04') . " HTML</title>\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . $this->_encoding . "\" >\n    </head>\n    <body style=\"padding: 10px; font-size: 10pt;font-family:    Verdana;\">\n" . $content . "\n    </body>\n</html>";
			exit();
		}

		protected function _setDefaultMargins($left, $top, $right = NULL, $bottom = NULL)
		{
			if ($right === NULL) {
				$right = $left;
			}

			if ($bottom === NULL) {
				$bottom = 8;
			}

			$this->_defaultLeft = $this->parsingCss->ConvertToMM($left . 'mm');
			$this->_defaultTop = $this->parsingCss->ConvertToMM($top . 'mm');
			$this->_defaultRight = $this->parsingCss->ConvertToMM($right . 'mm');
			$this->_defaultBottom = $this->parsingCss->ConvertToMM($bottom . 'mm');
		}

		protected function _setNewPage($format = NULL, $orientation = '', $background = NULL, $curr = NULL, $resetPageNumber = false)
		{
			$this->_firstPage = false;
			$this->_format = $format ? $format : $this->_format;
			$this->_orientation = $orientation ? $orientation : $this->_orientation;
			$this->_background = $background !== NULL ? $background : $this->_background;
			$this->_maxY = 0;
			$this->_maxX = 0;
			$this->_maxH = 0;
			$this->_maxE = 0;
			$this->pdf->SetMargins($this->_defaultLeft, $this->_defaultTop, $this->_defaultRight);

			if ($resetPageNumber) {
				$this->pdf->startPageGroup();
			}

			$this->pdf->AddPage($this->_orientation, $this->_format);

			if ($resetPageNumber) {
				$this->pdf->myStartPageGroup();
			}

			$this->_page++;
			if (!$this->_subPart && !$this->_isSubPart) {
				if (is_array($this->_background)) {
					if (isset($this->_background['color']) && $this->_background['color']) {
						$this->pdf->setFillColorArray($this->_background['color']);
						$this->pdf->Rect(0, 0, $this->pdf->getW(), $this->pdf->getH(), 'F');
					}

					if (isset($this->_background['img']) && $this->_background['img']) {
						$this->pdf->Image($this->_background['img'], $this->_background['posX'], $this->_background['posY'], $this->_background['width']);
					}
				}

				$this->_setPageHeader();
				$this->_setPageFooter();
			}

			$this->_setMargins();
			$this->pdf->setY($this->_margeTop);
			$this->_setNewPositionForNewLine($curr);
			$this->_maxH = 0;
		}

		protected function _setMargins()
		{
			$this->_margeLeft = $this->_defaultLeft + (isset($this->_background['left']) ? $this->_background['left'] : 0);
			$this->_margeRight = $this->_defaultRight + (isset($this->_background['right']) ? $this->_background['right'] : 0);
			$this->_margeTop = $this->_defaultTop + (isset($this->_background['top']) ? $this->_background['top'] : 0);
			$this->_margeBottom = $this->_defaultBottom + (isset($this->_background['bottom']) ? $this->_background['bottom'] : 0);
			$this->pdf->SetMargins($this->_margeLeft, $this->_margeTop, $this->_margeRight);
			$this->pdf->SetAutoPageBreak(false, $this->_margeBottom);
			$this->_pageMarges = array();

			if ($this->_isInParagraph !== false) {
				$this->_pageMarges[floor($this->_margeTop * 100)] = array($this->_isInParagraph[0], $this->pdf->getW() - $this->_isInParagraph[1]);
			}
			else {
				$this->_pageMarges[floor($this->_margeTop * 100)] = array($this->_margeLeft, $this->pdf->getW() - $this->_margeRight);
			}
		}

		protected function _DEBUG_add($name, $level = NULL)
		{
			if ($level === true) {
				$this->_debugLevel++;
			}

			$name = str_repeat('  ', $this->_debugLevel) . $name . ($level === true ? ' Begin' : ($level === false ? ' End' : ''));
			$time = microtime(true);
			$usage = ($this->_debugOkUsage ? memory_get_usage() : 0);
			$peak = ($this->_debugOkPeak ? memory_get_peak_usage() : 0);
			$this->_DEBUG_stepline($name, number_format(($time - $this->_debugStartTime) * 1000, 1, '.', ' ') . ' ms', number_format(($time - $this->_debugLastTime) * 1000, 1, '.', ' ') . ' ms', number_format($usage / 1024, 1, '.', ' ') . ' Ko', number_format($peak / 1024, 1, '.', ' ') . ' Ko');
			$this->_debugLastTime = $time;

			if ($level === false) {
				$this->_debugLevel--;
			}

			return $this;
		}

		protected function _DEBUG_stepline($name, $timeTotal, $timeStep, $memoryUsage, $memoryPeak)
		{
			$txt = str_pad($name, 30, ' ', STR_PAD_RIGHT) . str_pad($timeTotal, 12, ' ', STR_PAD_LEFT) . str_pad($timeStep, 12, ' ', STR_PAD_LEFT) . str_pad($memoryUsage, 15, ' ', STR_PAD_LEFT) . str_pad($memoryPeak, 15, ' ', STR_PAD_LEFT);
			echo '<pre style="padding:0; margin:0">' . $txt . '</pre>';
		}

		protected function _getMargins($y)
		{
			$y = floor($y * 100);
			$x = array($this->pdf->getlMargin(), $this->pdf->getW() - $this->pdf->getrMargin());

			foreach ($this->_pageMarges as $mY => $mX) {
				if ($mY <= $y) {
					$x = $mX;
				}
			}

			return $x;
		}

		protected function _addMargins($float, $xLeft, $yTop, $xRight, $yBottom)
		{
			$oldTop = $this->_getMargins($yTop);
			$oldBottom = $this->_getMargins($yBottom);
			if (($float == 'left') && ($oldTop[0] < $xRight)) {
				$oldTop[0] = $xRight;
			}

			if (($float == 'right') && ($xLeft < $oldTop[1])) {
				$oldTop[1] = $xLeft;
			}

			$yTop = floor($yTop * 100);
			$yBottom = floor($yBottom * 100);

			foreach ($this->_pageMarges as $mY => $mX) {
				if ($mY < $yTop) {
					continue;
				}

				if ($yBottom < $mY) {
					break;
				}

				if (($float == 'left') && ($this->_pageMarges[$mY][0] < $xRight)) {
					unset($this->_pageMarges[$mY]);
				}

				if (($float == 'right') && ($xLeft < $this->_pageMarges[$mY][1])) {
					unset($this->_pageMarges[$mY]);
				}
			}

			$this->_pageMarges[$yTop] = $oldTop;
			$this->_pageMarges[$yBottom] = $oldBottom;
			ksort($this->_pageMarges);
			$this->_isAfterFloat = true;
		}

		protected function _saveMargin($ml, $mt, $mr)
		{
			$this->_marges[] = array('l' => $this->pdf->getlMargin(), 't' => $this->pdf->gettMargin(), 'r' => $this->pdf->getrMargin(), 'page' => $this->_pageMarges);
			$this->pdf->SetMargins($ml, $mt, $mr);
			$this->_pageMarges = array();
			$this->_pageMarges[floor($mt * 100)] = array($ml, $this->pdf->getW() - $mr);
		}

		protected function _loadMargin()
		{
			$old = array_pop($this->_marges);

			if ($old) {
				$ml = $old['l'];
				$mt = $old['t'];
				$mr = $old['r'];
				$mP = $old['page'];
			}
			else {
				$ml = $this->_margeLeft;
				$mt = 0;
				$mr = $this->_margeRight;
				$mP = array(
					$mt => array($ml, $this->pdf->getW() - $mr)
					);
			}

			$this->pdf->SetMargins($ml, $mt, $mr);
			$this->_pageMarges = $mP;
		}

		protected function _saveMax()
		{
			$this->_maxSave[] = array($this->_maxX, $this->_maxY, $this->_maxH, $this->_maxE);
		}

		protected function _loadMax()
		{
			$old = array_pop($this->_maxSave);

			if ($old) {
				$this->_maxX = $old[0];
				$this->_maxY = $old[1];
				$this->_maxH = $old[2];
				$this->_maxE = $old[3];
			}
			else {
				$this->_maxX = 0;
				$this->_maxY = 0;
				$this->_maxH = 0;
				$this->_maxE = 0;
			}
		}

		protected function _setPageHeader()
		{
			if (!count($this->_subHEADER)) {
				return false;
			}

			$oldParsePos = $this->_parsePos;
			$oldParseCode = $this->parsingHtml->code;
			$this->_parsePos = 0;
			$this->parsingHtml->code = $this->_subHEADER;
			$this->_makeHTMLcode();
			$this->_parsePos = $oldParsePos;
			$this->parsingHtml->code = $oldParseCode;
		}

		protected function _setPageFooter()
		{
			if (!count($this->_subFOOTER)) {
				return false;
			}

			$oldParsePos = $this->_parsePos;
			$oldParseCode = $this->parsingHtml->code;
			$this->_parsePos = 0;
			$this->parsingHtml->code = $this->_subFOOTER;
			$this->_isInFooter = true;
			$this->_makeHTMLcode();
			$this->_isInFooter = false;
			$this->_parsePos = $oldParsePos;
			$this->parsingHtml->code = $oldParseCode;
		}

		protected function _setNewLine($h, $curr = NULL)
		{
			$this->pdf->Ln($h);
			$this->_setNewPositionForNewLine($curr);
		}

		protected function _setNewPositionForNewLine($curr = NULL)
		{
			list($lx, $rx) = $this->_getMargins($this->pdf->getY());
			$this->pdf->setX($lx);
			$wMax = $rx - $lx;
			$this->_currentH = 0;
			if ($this->_subPart || $this->_isSubPart || $this->_isForOneLine) {
				$this->pdf->setWordSpacing(0);
				return NULL;
			}

			$sub = NULL;
			$this->_createSubHTML($sub);
			$sub->_saveMargin(0, 0, $sub->pdf->getW() - $wMax);
			$sub->_isForOneLine = true;
			$sub->_parsePos = $this->_parsePos;
			$sub->parsingHtml->code = $this->parsingHtml->code;
			if (($curr !== NULL) && ($sub->parsingHtml->code[$this->_parsePos]['name'] == 'write')) {
				$txt = $sub->parsingHtml->code[$this->_parsePos]['param']['txt'];
				$txt = str_replace('[[page_cu]]', $sub->pdf->getMyNumPage($this->_page), $txt);
				$sub->parsingHtml->code[$this->_parsePos]['param']['txt'] = substr($txt, $curr + 1);
			}
			else {
				$sub->_parsePos++;
			}

			$res = NULL;
			$sub->_parsePos;

			for (; $sub->_parsePos < count($sub->parsingHtml->code); $sub->_parsePos++) {
				$action = $sub->parsingHtml->code[$sub->_parsePos];
				$res = $sub->_executeAction($action);

				if (!$res) {
					break;
				}
			}

			$w = $sub->_maxX;
			$h = $sub->_maxH;
			$e = ($res === NULL ? $sub->_maxE : 0);
			$this->_destroySubHTML($sub);

			if ($this->parsingCss->value['text-align'] == 'center') {
				$this->pdf->setX(((($rx + $this->pdf->getX()) - $w) * 0.5) - 0.01);
			}
			else if ($this->parsingCss->value['text-align'] == 'right') {
				$this->pdf->setX($rx - $w - 0.01);
			}
			else {
				$this->pdf->setX($lx);
			}

			$this->_currentH = $h;
			if (($this->parsingCss->value['text-align'] == 'justify') && (1 < $e)) {
				$this->pdf->setWordSpacing(($wMax - $w) / ($e - 1));
			}
			else {
				$this->pdf->setWordSpacing(0);
			}
		}

		protected function _prepareSubObj()
		{
			$pdf = NULL;
			HTML2PDF::$_subobj = new HTML2PDF($this->_orientation, $this->_format, $this->_langue, $this->_unicode, $this->_encoding, array($this->_defaultLeft, $this->_defaultTop, $this->_defaultRight, $this->_defaultBottom));
			HTML2PDF::$_subobj->setTestTdInOnePage($this->_testTdInOnepage);
			HTML2PDF::$_subobj->setTestIsImage($this->_testIsImage);
			HTML2PDF::$_subobj->setTestIsDeprecated($this->_testIsDeprecated);
			HTML2PDF::$_subobj->setDefaultFont($this->_defaultFont);
			HTML2PDF::$_subobj->parsingCss->css = &$this->parsingCss->css;
			HTML2PDF::$_subobj->parsingCss->cssKeys = &$this->parsingCss->cssKeys;
			HTML2PDF::$_subobj->pdf->cloneFontFrom($this->pdf);
			HTML2PDF::$_subobj->parsingCss->setPdfParent($pdf);
		}

		protected function _createSubHTML(&$subHtml, $cellmargin = 0)
		{
			if (HTML2PDF::$_subobj === NULL) {
				$this->_prepareSubObj();
			}

			if ($this->parsingCss->value['width']) {
				$marge = $cellmargin * 2;
				$marge += $this->parsingCss->value['padding']['l'] + $this->parsingCss->value['padding']['r'];
				$marge += $this->parsingCss->value['border']['l']['width'] + $this->parsingCss->value['border']['r']['width'];
				$marge = ($this->pdf->getW() - $this->parsingCss->value['width']) + $marge;
			}
			else {
				$marge = $this->_margeLeft + $this->_margeRight;
			}

			HTML2PDF::$_subobj->pdf->getPage();
			$subHtml = clone HTML2PDF::$_subobj;
			$subHtml->parsingCss->table = $this->parsingCss->table;
			$subHtml->parsingCss->value = $this->parsingCss->value;
			$subHtml->initSubHtml($this->_format, $this->_orientation, $marge, $this->_page, $this->_defList, $this->pdf->getMyLastPageGroup(), $this->pdf->getMyLastPageGroupNb());
		}

		protected function _destroySubHTML(&$subHtml)
		{
			unset($subHtml);
			$subHtml = NULL;
		}

		protected function _listeArab2Rom($nbArabic)
		{
			$nbBaseTen = array('I', 'X', 'C', 'M');
			$nbBaseFive = array('V', 'L', 'D');
			$nbRoman = '';

			if ($nbArabic < 1) {
				return $nbArabic;
			}

			if (3999 < $nbArabic) {
				return $nbArabic;
			}

			for ($i = 3; 0 <= $i; $i--) {
				$chiffre = floor($nbArabic / pow(10, $i));

				if (1 <= $chiffre) {
					$nbArabic = $nbArabic - ($chiffre * pow(10, $i));

					if ($chiffre <= 3) {
						for ($j = $chiffre; 1 <= $j; $j--) {
							$nbRoman = $nbRoman . $nbBaseTen[$i];
						}
					}
					else if ($chiffre == 9) {
						$nbRoman = $nbRoman . $nbBaseTen[$i] . $nbBaseTen[$i + 1];
					}
					else if ($chiffre == 4) {
						$nbRoman = $nbRoman . $nbBaseTen[$i] . $nbBaseFive[$i];
					}
					else {
						$nbRoman = $nbRoman . $nbBaseFive[$i];

						for ($j = $chiffre - 5; 1 <= $j; $j--) {
							$nbRoman = $nbRoman . $nbBaseTen[$i];
						}
					}
				}
			}

			return $nbRoman;
		}

		protected function _listeAddLi()
		{
			$this->_defList[count($this->_defList) - 1]['nb']++;
		}

		protected function _listeGetWidth()
		{
			return '7mm';
		}

		protected function _listeGetPadding()
		{
			return '1mm';
		}

		protected function _listeGetLi()
		{
			$im = $this->_defList[count($this->_defList) - 1]['img'];
			$st = $this->_defList[count($this->_defList) - 1]['style'];
			$nb = $this->_defList[count($this->_defList) - 1]['nb'];
			$up = substr($st, 0, 6) == 'upper-';

			if ($im) {
				return array(false, false, $im);
			}

			switch ($st) {
			case 'none':
				return array('helvetica', true, ' ');
			case 'upper-alpha':
			case 'lower-alpha':
				$str = '';

				while (26 < $nb) {
					$str = chr(96 + ($nb % 26)) . $str;
					$nb = floor($nb / 26);
				}

				$str = chr(96 + $nb) . $str;
				return array('helvetica', false, ($up ? strtoupper($str) : $str) . '.');
			case 'upper-roman':
			case 'lower-roman':
				$str = $this->_listeArab2Rom($nb);
				return array('helvetica', false, ($up ? strtoupper($str) : $str) . '.');
			case 'decimal':
				return array('helvetica', false, $nb . '.');
			case 'square':
				return array('zapfdingbats', true, chr(110));
			case 'circle':
				return array('zapfdingbats', true, chr(109));
			case 'disc':
			default:
				return array('zapfdingbats', true, chr(108));
			}
		}

		protected function _listeAddLevel($type = 'ul', $style = '', $img = NULL)
		{
			if ($img) {
				if (preg_match('/^url\\(([^)]+)\\)$/isU', trim($img), $match)) {
					$img = $match[1];
				}
				else {
					$img = NULL;
				}
			}
			else {
				$img = NULL;
			}

			if (!in_array($type, array('ul', 'ol'))) {
				$type = 'ul';
			}

			if (!in_array($style, array('lower-alpha', 'upper-alpha', 'upper-roman', 'lower-roman', 'decimal', 'square', 'circle', 'disc', 'none'))) {
				$style = '';
			}

			if (!$style) {
				if ($type == 'ul') {
					$style = 'disc';
				}
				else {
					$style = 'decimal';
				}
			}

			$this->_defList[count($this->_defList)] = array('style' => $style, 'nb' => 0, 'img' => $img);
		}

		protected function _listeDelLevel()
		{
			if (count($this->_defList)) {
				unset($this->_defList[count($this->_defList) - 1]);
				$this->_defList = array_values($this->_defList);
			}
		}

		protected function _makeHTMLcode()
		{
			for ($this->_parsePos = 0; $this->_parsePos < count($this->parsingHtml->code); $this->_parsePos++) {
				$action = $this->parsingHtml->code[$this->_parsePos];
				if (in_array($action['name'], array('table', 'ul', 'ol')) && !$action['close']) {
					$this->_subPart = true;
					$tagOpen = $action['name'];
					$this->_tempPos = $this->_parsePos;

					while (isset($this->parsingHtml->code[$this->_tempPos]) && !(($this->parsingHtml->code[$this->_tempPos]['name'] == $tagOpen) && $this->parsingHtml->code[$this->_tempPos]['close'])) {
						$this->_executeAction($this->parsingHtml->code[$this->_tempPos]);
						$this->_tempPos++;
					}

					if (isset($this->parsingHtml->code[$this->_tempPos])) {
						$this->_executeAction($this->parsingHtml->code[$this->_tempPos]);
					}

					$this->_subPart = false;
				}

				$this->_executeAction($action);
			}
		}

		protected function _executeAction($action)
		{
			$fnc = ($action['close'] ? '_tag_close_' : '_tag_open_') . strtoupper($action['name']);
			$param = $action['param'];
			if (($fnc != '_tag_open_PAGE') && $this->_firstPage) {
				$this->_setNewPage();
			}

			if (!is_callable(array(&$this, $fnc))) {
				throw new HTML2PDF_exception(1, strtoupper($action['name']), $this->parsingHtml->getHtmlErrorCode($action['html_pos']));
			}

			$res = $this->$fnc($param);
			$this->_previousCall = $fnc;
			return $res;
		}

		protected function _getElementY($h)
		{
			if ($this->_subPart || $this->_isSubPart || !$this->_currentH || ($this->_currentH < $h)) {
				return 0;
			}

			return ($this->_currentH - $h) * 0.80000000000000004;
		}

		protected function _makeBreakLine($h, $curr = NULL)
		{
			if ($h) {
				if ((($this->pdf->getY() + $h) < ($this->pdf->getH() - $this->pdf->getbMargin())) || $this->_isInOverflow || $this->_isInFooter) {
					$this->_setNewLine($h, $curr);
				}
				else {
					$this->_setNewPage(NULL, '', NULL, $curr);
				}
			}
			else {
				$this->_setNewPositionForNewLine($curr);
			}

			$this->_maxH = 0;
			$this->_maxE = 0;
		}

		protected function _drawImage($src, $subLi = false)
		{
			$infos = @getimagesize($src);

			if (count($infos) < 2) {
				if ($this->_testIsImage) {
					throw new HTML2PDF_exception(6, $src);
				}

				$src = NULL;
				$infos = array(16, 16);
			}

			$imageWidth = $infos[0] / $this->pdf->getK();
			$imageHeight = $infos[1] / $this->pdf->getK();
			if ($this->parsingCss->value['width'] && $this->parsingCss->value['height']) {
				$w = $this->parsingCss->value['width'];
				$h = $this->parsingCss->value['height'];
			}
			else if ($this->parsingCss->value['width']) {
				$w = $this->parsingCss->value['width'];
				$h = ($imageHeight * $w) / $imageWidth;
			}
			else if ($this->parsingCss->value['height']) {
				$h = $this->parsingCss->value['height'];
				$w = ($imageWidth * $h) / $imageHeight;
			}
			else {
				$w = (72 / 96) * $imageWidth;
				$h = (72 / 96) * $imageHeight;
			}

			$float = $this->parsingCss->getFloat();
			if ($float && $this->_maxH) {
				if (!$this->_tag_open_BR(array())) {
					return false;
				}
			}

			$x = $this->pdf->getX();
			$y = $this->pdf->getY();
			if (!$float && (($this->pdf->getW() - $this->pdf->getrMargin()) < ($x + $w)) && $this->_maxH) {
				if ($this->_isForOneLine) {
					return false;
				}

				$hnl = max($this->_maxH, $this->parsingCss->getLineHeight());
				$this->_setNewLine($hnl);
				$x = $this->pdf->getX();
				$y = $this->pdf->getY();
			}

			if ((($this->pdf->getH() - $this->pdf->getbMargin()) < ($y + $h)) && !$this->_isInOverflow) {
				$this->_setNewPage();
				$x = $this->pdf->getX();
				$y = $this->pdf->getY();
			}

			$hT = 0.80000000000000004 * $this->parsingCss->value['font-size'];
			if ($subLi && ($h < $hT)) {
				$y += $hT - $h;
			}

			$yc = $y - $this->parsingCss->value['margin']['t'];
			$old = $this->parsingCss->getOldValues();

			if ($old['width']) {
				$parentWidth = $old['width'];
				$parentX = $x;
			}
			else {
				$parentWidth = $this->pdf->getW() - $this->pdf->getlMargin() - $this->pdf->getrMargin();
				$parentX = $this->pdf->getlMargin();
			}

			if ($float) {
				list($lx, $rx) = $this->_getMargins($yc);
				$parentX = $lx;
				$parentWidth = $rx - $lx;
			}

			if (($w < $parentWidth) && ($float != 'left')) {
				if (($float == 'right') || ($this->parsingCss->value['text-align'] == 'li_right')) {
					$x = ($parentX + $parentWidth) - $w - $this->parsingCss->value['margin']['r'] - $this->parsingCss->value['margin']['l'];
				}
			}

			if (!$this->_subPart && !$this->_isSubPart) {
				if ($src) {
					$this->pdf->Image($src, $x, $y, $w, $h, '', $this->_isInLink);
				}
				else {
					$this->pdf->setFillColorArray(array(240, 220, 220));
					$this->pdf->Rect($x, $y, $w, $h, 'F');
				}
			}

			$x -= $this->parsingCss->value['margin']['l'];
			$y -= $this->parsingCss->value['margin']['t'];
			$w += $this->parsingCss->value['margin']['l'] + $this->parsingCss->value['margin']['r'];
			$h += $this->parsingCss->value['margin']['t'] + $this->parsingCss->value['margin']['b'];

			if ($float == 'left') {
				$this->_maxX = max($this->_maxX, $x + $w);
				$this->_maxY = max($this->_maxY, $y + $h);
				$this->_addMargins($float, $x, $y, $x + $w, $y + $h);
				list($lx, $rx) = $this->_getMargins($yc);
				$this->pdf->setXY($lx, $yc);
			}
			else if ($float == 'right') {
				$this->_maxY = max($this->_maxY, $y + $h);
				$this->_addMargins($float, $x, $y, $x + $w, $y + $h);
				list($lx, $rx) = $this->_getMargins($yc);
				$this->pdf->setXY($lx, $yc);
			}
			else {
				$this->pdf->setX($x + $w);
				$this->_maxX = max($this->_maxX, $x + $w);
				$this->_maxY = max($this->_maxY, $y + $h);
				$this->_maxH = max($this->_maxH, $h);
			}

			return true;
		}

		protected function _drawRectangle($x, $y, $w, $h, $border, $padding, $margin, $background)
		{
			if ($this->_subPart || $this->_isSubPart || ($h === NULL)) {
				return false;
			}

			$x += $margin;
			$y += $margin;
			$w -= $margin * 2;
			$h -= $margin * 2;
			$outTL = $border['radius']['tl'];
			$outTR = $border['radius']['tr'];
			$outBR = $border['radius']['br'];
			$outBL = $border['radius']['bl'];
			$outTL = ($outTL[0] && $outTL[1] ? $outTL : NULL);
			$outTR = ($outTR[0] && $outTR[1] ? $outTR : NULL);
			$outBR = ($outBR[0] && $outBR[1] ? $outBR : NULL);
			$outBL = ($outBL[0] && $outBL[1] ? $outBL : NULL);
			$inTL = $outTL;
			$inTR = $outTR;
			$inBR = $outBR;
			$inBL = $outBL;

			if (is_array($inTL)) {
				$inTL[0] -= $border['l']['width'];
				$inTL[1] -= $border['t']['width'];
			}

			if (is_array($inTR)) {
				$inTR[0] -= $border['r']['width'];
				$inTR[1] -= $border['t']['width'];
			}

			if (is_array($inBR)) {
				$inBR[0] -= $border['r']['width'];
				$inBR[1] -= $border['b']['width'];
			}

			if (is_array($inBL)) {
				$inBL[0] -= $border['l']['width'];
				$inBL[1] -= $border['b']['width'];
			}

			if (($inTL[0] <= 0) || ($inTL[1] <= 0)) {
				$inTL = NULL;
			}

			if (($inTR[0] <= 0) || ($inTR[1] <= 0)) {
				$inTR = NULL;
			}

			if (($inBR[0] <= 0) || ($inBR[1] <= 0)) {
				$inBR = NULL;
			}

			if (($inBL[0] <= 0) || ($inBL[1] <= 0)) {
				$inBL = NULL;
			}

			$pdfStyle = '';

			if ($background['color']) {
				$this->pdf->setFillColorArray($background['color']);
				$pdfStyle .= 'F';
			}

			if ($pdfStyle) {
				$this->pdf->clippingPathStart($x, $y, $w, $h, $outTL, $outTR, $outBL, $outBR);
				$this->pdf->Rect($x, $y, $w, $h, $pdfStyle);
				$this->pdf->clippingPathStop();
			}

			if ($background['image']) {
				$iName = $background['image'];
				$iPosition = ($background['position'] !== NULL ? $background['position'] : array(0, 0));
				$iRepeat = ($background['repeat'] !== NULL ? $background['repeat'] : array(true, true));
				$bX = $x;
				$bY = $y;
				$bW = $w;
				$bH = $h;

				if ($border['b']['width']) {
					$bH -= $border['b']['width'];
				}

				if ($border['l']['width']) {
					$bW -= $border['l']['width'];
					$bX += $border['l']['width'];
				}

				if ($border['t']['width']) {
					$bH -= $border['t']['width'];
					$bY += $border['t']['width'];
				}

				if ($border['r']['width']) {
					$bW -= $border['r']['width'];
				}

				$imageInfos = @getimagesize($iName);

				if (count($imageInfos) < 2) {
					if ($this->_testIsImage) {
						throw new HTML2PDF_exception(6, $iName);
					}
				}
				else {
					$imageWidth = ((72 / 96) * $imageInfos[0]) / $this->pdf->getK();
					$imageHeight = ((72 / 96) * $imageInfos[1]) / $this->pdf->getK();

					if ($iRepeat[0]) {
						$iPosition[0] = $bX;
					}
					else if (preg_match('/^([-]?[0-9\\.]+)%/isU', $iPosition[0], $match)) {
						$iPosition[0] = $bX + (($match[1] * ($bW - $imageWidth)) / 100);
					}
					else {
						$iPosition[0] = $bX + $iPosition[0];
					}

					if ($iRepeat[1]) {
						$iPosition[1] = $bY;
					}
					else if (preg_match('/^([-]?[0-9\\.]+)%/isU', $iPosition[1], $match)) {
						$iPosition[1] = $bY + (($match[1] * ($bH - $imageHeight)) / 100);
					}
					else {
						$iPosition[1] = $bY + $iPosition[1];
					}

					$imageXmin = $bX;
					$imageXmax = $bX + $bW;
					$imageYmin = $bY;
					$imageYmax = $bY + $bH;
					if (!$iRepeat[0] && !$iRepeat[1]) {
						$imageXmin = $iPosition[0];
						$imageXmax = $iPosition[0] + $imageWidth;
						$imageYmin = $iPosition[1];
						$imageYmax = $iPosition[1] + $imageHeight;
					}
					else {
						if ($iRepeat[0] && !$iRepeat[1]) {
							$imageYmin = $iPosition[1];
							$imageYmax = $iPosition[1] + $imageHeight;
						}
						else {
							if (!$iRepeat[0] && $iRepeat[1]) {
								$imageXmin = $iPosition[0];
								$imageXmax = $iPosition[0] + $imageWidth;
							}
						}
					}

					$this->pdf->clippingPathStart($bX, $bY, $bW, $bH, $inTL, $inTR, $inBL, $inBR);

					for ($iY = $imageYmin; $iY < $imageYmax; $iY += $imageHeight) {
						for ($iX = $imageXmin; $iX < $imageXmax; $iX += $imageWidth) {
							$cX = NULL;
							$cY = NULL;
							$cW = $imageWidth;
							$cH = $imageHeight;

							if (($imageYmax - $iY) < $imageHeight) {
								$cX = $iX;
								$cY = $iY;
								$cH = $imageYmax - $iY;
							}

							if (($imageXmax - $iX) < $imageWidth) {
								$cX = $iX;
								$cY = $iY;
								$cW = $imageXmax - $iX;
							}

							$this->pdf->Image($iName, $iX, $iY, $imageWidth, $imageHeight, '', '');
						}
					}

					$this->pdf->clippingPathStop();
				}
			}

			$loose = 0.01;
			$x -= $loose;
			$y -= $loose;
			$w += 2 * $loose;
			$h += 2 * $loose;

			if ($border['l']['width']) {
				$border['l']['width'] += 2 * $loose;
			}

			if ($border['t']['width']) {
				$border['t']['width'] += 2 * $loose;
			}

			if ($border['r']['width']) {
				$border['r']['width'] += 2 * $loose;
			}

			if ($border['b']['width']) {
				$border['b']['width'] += 2 * $loose;
			}

			$testBl = $border['l']['width'] && ($border['l']['color'][0] !== NULL);
			$testBt = $border['t']['width'] && ($border['t']['color'][0] !== NULL);
			$testBr = $border['r']['width'] && ($border['r']['color'][0] !== NULL);
			$testBb = $border['b']['width'] && ($border['b']['color'][0] !== NULL);
			if (is_array($outBL) && ($testBb || $testBl)) {
				if ($inBL) {
					$courbe = array();
					$courbe[] = $x + $outBL[0];
					$courbe[] = $y + $h;
					$courbe[] = $x;
					$courbe[] = ($y + $h) - $outBL[1];
					$courbe[] = $x + $outBL[0];
					$courbe[] = ($y + $h) - $border['b']['width'];
					$courbe[] = $x + $border['l']['width'];
					$courbe[] = ($y + $h) - $outBL[1];
					$courbe[] = $x + $outBL[0];
					$courbe[] = ($y + $h) - $outBL[1];
				}
				else {
					$courbe = array();
					$courbe[] = $x + $outBL[0];
					$courbe[] = $y + $h;
					$courbe[] = $x;
					$courbe[] = ($y + $h) - $outBL[1];
					$courbe[] = $x + $border['l']['width'];
					$courbe[] = ($y + $h) - $border['b']['width'];
					$courbe[] = $x + $outBL[0];
					$courbe[] = ($y + $h) - $outBL[1];
				}

				$this->_drawCurve($courbe, $border['l']['color']);
			}

			if (is_array($outTL) && ($testBt || $testBl)) {
				if ($inTL) {
					$courbe = array();
					$courbe[] = $x;
					$courbe[] = $y + $outTL[1];
					$courbe[] = $x + $outTL[0];
					$courbe[] = $y;
					$courbe[] = $x + $border['l']['width'];
					$courbe[] = $y + $outTL[1];
					$courbe[] = $x + $outTL[0];
					$courbe[] = $y + $border['t']['width'];
					$courbe[] = $x + $outTL[0];
					$courbe[] = $y + $outTL[1];
				}
				else {
					$courbe = array();
					$courbe[] = $x;
					$courbe[] = $y + $outTL[1];
					$courbe[] = $x + $outTL[0];
					$courbe[] = $y;
					$courbe[] = $x + $border['l']['width'];
					$courbe[] = $y + $border['t']['width'];
					$courbe[] = $x + $outTL[0];
					$courbe[] = $y + $outTL[1];
				}

				$this->_drawCurve($courbe, $border['t']['color']);
			}

			if (is_array($outTR) && ($testBt || $testBr)) {
				if ($inTR) {
					$courbe = array();
					$courbe[] = ($x + $w) - $outTR[0];
					$courbe[] = $y;
					$courbe[] = $x + $w;
					$courbe[] = $y + $outTR[1];
					$courbe[] = ($x + $w) - $outTR[0];
					$courbe[] = $y + $border['t']['width'];
					$courbe[] = ($x + $w) - $border['r']['width'];
					$courbe[] = $y + $outTR[1];
					$courbe[] = ($x + $w) - $outTR[0];
					$courbe[] = $y + $outTR[1];
				}
				else {
					$courbe = array();
					$courbe[] = ($x + $w) - $outTR[0];
					$courbe[] = $y;
					$courbe[] = $x + $w;
					$courbe[] = $y + $outTR[1];
					$courbe[] = ($x + $w) - $border['r']['width'];
					$courbe[] = $y + $border['t']['width'];
					$courbe[] = ($x + $w) - $outTR[0];
					$courbe[] = $y + $outTR[1];
				}

				$this->_drawCurve($courbe, $border['r']['color']);
			}

			if (is_array($outBR) && ($testBb || $testBr)) {
				if ($inBR) {
					$courbe = array();
					$courbe[] = $x + $w;
					$courbe[] = ($y + $h) - $outBR[1];
					$courbe[] = ($x + $w) - $outBR[0];
					$courbe[] = $y + $h;
					$courbe[] = ($x + $w) - $border['r']['width'];
					$courbe[] = ($y + $h) - $outBR[1];
					$courbe[] = ($x + $w) - $outBR[0];
					$courbe[] = ($y + $h) - $border['b']['width'];
					$courbe[] = ($x + $w) - $outBR[0];
					$courbe[] = ($y + $h) - $outBR[1];
				}
				else {
					$courbe = array();
					$courbe[] = $x + $w;
					$courbe[] = ($y + $h) - $outBR[1];
					$courbe[] = ($x + $w) - $outBR[0];
					$courbe[] = $y + $h;
					$courbe[] = ($x + $w) - $border['r']['width'];
					$courbe[] = ($y + $h) - $border['b']['width'];
					$courbe[] = ($x + $w) - $outBR[0];
					$courbe[] = ($y + $h) - $outBR[1];
				}

				$this->_drawCurve($courbe, $border['b']['color']);
			}

			if ($testBl) {
				$pt = array();
				$pt[] = $x;
				$pt[] = $y + $h;
				$pt[] = $x;
				$pt[] = ($y + $h) - $border['b']['width'];
				$pt[] = $x;
				$pt[] = $y + $border['t']['width'];
				$pt[] = $x;
				$pt[] = $y;
				$pt[] = $x + $border['l']['width'];
				$pt[] = $y + $border['t']['width'];
				$pt[] = $x + $border['l']['width'];
				$pt[] = ($y + $h) - $border['b']['width'];
				$bord = 3;

				if (is_array($outBL)) {
					$bord -= 1;
					$pt[3] -= $outBL[1] - $border['b']['width'];

					if ($inBL) {
						$pt[11] -= $inBL[1];
					}

					unset($pt[0]);
					unset($pt[1]);
				}

				if (is_array($outTL)) {
					$bord -= 2;
					$pt[5] += $outTL[1] - $border['t']['width'];

					if ($inTL) {
						$pt[9] += $inTL[1];
					}

					unset($pt[6]);
					unset($pt[7]);
				}

				$pt = array_values($pt);
				$this->_drawLine($pt, $border['l']['color'], $border['l']['type'], $border['l']['width'], $bord);
			}

			if ($testBt) {
				$pt = array();
				$pt[] = $x;
				$pt[] = $y;
				$pt[] = $x + $border['l']['width'];
				$pt[] = $y;
				$pt[] = ($x + $w) - $border['r']['width'];
				$pt[] = $y;
				$pt[] = $x + $w;
				$pt[] = $y;
				$pt[] = ($x + $w) - $border['r']['width'];
				$pt[] = $y + $border['t']['width'];
				$pt[] = $x + $border['l']['width'];
				$pt[] = $y + $border['t']['width'];
				$bord = 3;

				if (is_array($outTL)) {
					$bord -= 1;
					$pt[2] += $outTL[0] - $border['l']['width'];

					if ($inTL) {
						$pt[10] += $inTL[0];
					}

					unset($pt[0]);
					unset($pt[1]);
				}

				if (is_array($outTR)) {
					$bord -= 2;
					$pt[4] -= $outTR[0] - $border['r']['width'];

					if ($inTR) {
						$pt[8] -= $inTR[0];
					}

					unset($pt[6]);
					unset($pt[7]);
				}

				$pt = array_values($pt);
				$this->_drawLine($pt, $border['t']['color'], $border['t']['type'], $border['t']['width'], $bord);
			}

			if ($testBr) {
				$pt = array();
				$pt[] = $x + $w;
				$pt[] = $y;
				$pt[] = $x + $w;
				$pt[] = $y + $border['t']['width'];
				$pt[] = $x + $w;
				$pt[] = ($y + $h) - $border['b']['width'];
				$pt[] = $x + $w;
				$pt[] = $y + $h;
				$pt[] = ($x + $w) - $border['r']['width'];
				$pt[] = ($y + $h) - $border['b']['width'];
				$pt[] = ($x + $w) - $border['r']['width'];
				$pt[] = $y + $border['t']['width'];
				$bord = 3;

				if (is_array($outTR)) {
					$bord -= 1;
					$pt[3] += $outTR[1] - $border['t']['width'];

					if ($inTR) {
						$pt[11] += $inTR[1];
					}

					unset($pt[0]);
					unset($pt[1]);
				}

				if (is_array($outBR)) {
					$bord -= 2;
					$pt[5] -= $outBR[1] - $border['b']['width'];

					if ($inBR) {
						$pt[9] -= $inBR[1];
					}

					unset($pt[6]);
					unset($pt[7]);
				}

				$pt = array_values($pt);
				$this->_drawLine($pt, $border['r']['color'], $border['r']['type'], $border['r']['width'], $bord);
			}

			if ($testBb) {
				$pt = array();
				$pt[] = $x + $w;
				$pt[] = $y + $h;
				$pt[] = ($x + $w) - $border['r']['width'];
				$pt[] = $y + $h;
				$pt[] = $x + $border['l']['width'];
				$pt[] = $y + $h;
				$pt[] = $x;
				$pt[] = $y + $h;
				$pt[] = $x + $border['l']['width'];
				$pt[] = ($y + $h) - $border['b']['width'];
				$pt[] = ($x + $w) - $border['r']['width'];
				$pt[] = ($y + $h) - $border['b']['width'];
				$bord = 3;

				if (is_array($outBL)) {
					$bord -= 2;
					$pt[4] += $outBL[0] - $border['l']['width'];

					if ($inBL) {
						$pt[8] += $inBL[0];
					}

					unset($pt[6]);
					unset($pt[7]);
				}

				if (is_array($outBR)) {
					$bord -= 1;
					$pt[2] -= $outBR[0] - $border['r']['width'];

					if ($inBR) {
						$pt[10] -= $inBR[0];
					}

					unset($pt[0]);
					unset($pt[1]);
				}

				$pt = array_values($pt);
				$this->_drawLine($pt, $border['b']['color'], $border['b']['type'], $border['b']['width'], $bord);
			}

			if ($background['color']) {
				$this->pdf->setFillColorArray($background['color']);
			}

			return true;
		}

		protected function _drawCurve($pt, $color)
		{
			$this->pdf->setFillColorArray($color);

			if (count($pt) == 10) {
				$this->pdf->drawCurve($pt[0], $pt[1], $pt[2], $pt[3], $pt[4], $pt[5], $pt[6], $pt[7], $pt[8], $pt[9]);
			}
			else {
				$this->pdf->drawCorner($pt[0], $pt[1], $pt[2], $pt[3], $pt[4], $pt[5], $pt[6], $pt[7]);
			}
		}

		protected function _drawLine($pt, $color, $type, $width, $radius = 3)
		{
			$this->pdf->setFillColorArray($color);
			if (($type == 'dashed') || ($type == 'dotted')) {
				if ($radius == 1) {
					$tmp = array();
					$tmp[] = $pt[0];
					$tmp[] = $pt[1];
					$tmp[] = $pt[2];
					$tmp[] = $pt[3];
					$tmp[] = $pt[8];
					$tmp[] = $pt[9];
					$this->pdf->Polygon($tmp, 'F');
					$tmp = array();
					$tmp[] = $pt[2];
					$tmp[] = $pt[3];
					$tmp[] = $pt[4];
					$tmp[] = $pt[5];
					$tmp[] = $pt[6];
					$tmp[] = $pt[7];
					$tmp[] = $pt[8];
					$tmp[] = $pt[9];
					$pt = $tmp;
				}
				else if ($radius == 2) {
					$tmp = array();
					$tmp[] = $pt[2];
					$tmp[] = $pt[3];
					$tmp[] = $pt[4];
					$tmp[] = $pt[5];
					$tmp[] = $pt[6];
					$tmp[] = $pt[7];
					$this->pdf->Polygon($tmp, 'F');
					$tmp = array();
					$tmp[] = $pt[0];
					$tmp[] = $pt[1];
					$tmp[] = $pt[2];
					$tmp[] = $pt[3];
					$tmp[] = $pt[6];
					$tmp[] = $pt[7];
					$tmp[] = $pt[8];
					$tmp[] = $pt[9];
					$pt = $tmp;
				}
				else if ($radius == 3) {
					$tmp = array();
					$tmp[] = $pt[0];
					$tmp[] = $pt[1];
					$tmp[] = $pt[2];
					$tmp[] = $pt[3];
					$tmp[] = $pt[10];
					$tmp[] = $pt[11];
					$this->pdf->Polygon($tmp, 'F');
					$tmp = array();
					$tmp[] = $pt[4];
					$tmp[] = $pt[5];
					$tmp[] = $pt[6];
					$tmp[] = $pt[7];
					$tmp[] = $pt[8];
					$tmp[] = $pt[9];
					$this->pdf->Polygon($tmp, 'F');
					$tmp = array();
					$tmp[] = $pt[2];
					$tmp[] = $pt[3];
					$tmp[] = $pt[4];
					$tmp[] = $pt[5];
					$tmp[] = $pt[8];
					$tmp[] = $pt[9];
					$tmp[] = $pt[10];
					$tmp[] = $pt[11];
					$pt = $tmp;
				}

				if ($pt[2] == $pt[0]) {
					$l = abs(($pt[3] - $pt[1]) * 0.5);
					$px = 0;
					$py = $width;
					$x1 = $pt[0];
					$y1 = ($pt[3] + $pt[1]) * 0.5;
					$x2 = $pt[6];
					$y2 = ($pt[7] + $pt[5]) * 0.5;
				}
				else {
					$l = abs(($pt[2] - $pt[0]) * 0.5);
					$px = $width;
					$py = 0;
					$x1 = ($pt[2] + $pt[0]) * 0.5;
					$y1 = $pt[1];
					$x2 = ($pt[6] + $pt[4]) * 0.5;
					$y2 = $pt[7];
				}

				if ($type == 'dashed') {
					$px = $px * 3;
					$py = $py * 3;
				}

				$mode = ($l / ($px + $py)) < 0.5;

				for ($i = 0; 0 < ($l - (($px + $py) * ($i - 0.5))); $i++) {
					if (($i % 2) == $mode) {
						$j = $i - 0.5;
						$lx1 = $px * $j;

						if ($lx1 < (0 - $l)) {
							$lx1 = 0 - $l;
						}

						$ly1 = $py * $j;

						if ($ly1 < (0 - $l)) {
							$ly1 = 0 - $l;
						}

						$lx2 = $px * ($j + 1);

						if ($l < $lx2) {
							$lx2 = $l;
						}

						$ly2 = $py * ($j + 1);

						if ($l < $ly2) {
							$ly2 = $l;
						}

						$tmp = array();
						$tmp[] = $x1 + $lx1;
						$tmp[] = $y1 + $ly1;
						$tmp[] = $x1 + $lx2;
						$tmp[] = $y1 + $ly2;
						$tmp[] = $x2 + $lx2;
						$tmp[] = $y2 + $ly2;
						$tmp[] = $x2 + $lx1;
						$tmp[] = $y2 + $ly1;
						$this->pdf->Polygon($tmp, 'F');

						if (0 < $j) {
							$tmp = array();
							$tmp[] = $x1 - $lx1;
							$tmp[] = $y1 - $ly1;
							$tmp[] = $x1 - $lx2;
							$tmp[] = $y1 - $ly2;
							$tmp[] = $x2 - $lx2;
							$tmp[] = $y2 - $ly2;
							$tmp[] = $x2 - $lx1;
							$tmp[] = $y2 - $ly1;
							$this->pdf->Polygon($tmp, 'F');
						}
					}
				}
			}
			else if ($type == 'double') {
				$pt1 = $pt;
				$pt2 = $pt;

				if (count($pt) == 12) {
					$pt1[0] = (($pt[0] - $pt[10]) * 0.33000000000000002) + $pt[10];
					$pt1[1] = (($pt[1] - $pt[11]) * 0.33000000000000002) + $pt[11];
					$pt1[2] = (($pt[2] - $pt[10]) * 0.33000000000000002) + $pt[10];
					$pt1[3] = (($pt[3] - $pt[11]) * 0.33000000000000002) + $pt[11];
					$pt1[4] = (($pt[4] - $pt[8]) * 0.33000000000000002) + $pt[8];
					$pt1[5] = (($pt[5] - $pt[9]) * 0.33000000000000002) + $pt[9];
					$pt1[6] = (($pt[6] - $pt[8]) * 0.33000000000000002) + $pt[8];
					$pt1[7] = (($pt[7] - $pt[9]) * 0.33000000000000002) + $pt[9];
					$pt2[10] = (($pt[10] - $pt[0]) * 0.33000000000000002) + $pt[0];
					$pt2[11] = (($pt[11] - $pt[1]) * 0.33000000000000002) + $pt[1];
					$pt2[2] = (($pt[2] - $pt[0]) * 0.33000000000000002) + $pt[0];
					$pt2[3] = (($pt[3] - $pt[1]) * 0.33000000000000002) + $pt[1];
					$pt2[4] = (($pt[4] - $pt[6]) * 0.33000000000000002) + $pt[6];
					$pt2[5] = (($pt[5] - $pt[7]) * 0.33000000000000002) + $pt[7];
					$pt2[8] = (($pt[8] - $pt[6]) * 0.33000000000000002) + $pt[6];
					$pt2[9] = (($pt[9] - $pt[7]) * 0.33000000000000002) + $pt[7];
				}
				else {
					$pt1[0] = (($pt[0] - $pt[6]) * 0.33000000000000002) + $pt[6];
					$pt1[1] = (($pt[1] - $pt[7]) * 0.33000000000000002) + $pt[7];
					$pt1[2] = (($pt[2] - $pt[4]) * 0.33000000000000002) + $pt[4];
					$pt1[3] = (($pt[3] - $pt[5]) * 0.33000000000000002) + $pt[5];
					$pt2[6] = (($pt[6] - $pt[0]) * 0.33000000000000002) + $pt[0];
					$pt2[7] = (($pt[7] - $pt[1]) * 0.33000000000000002) + $pt[1];
					$pt2[4] = (($pt[4] - $pt[2]) * 0.33000000000000002) + $pt[2];
					$pt2[5] = (($pt[5] - $pt[3]) * 0.33000000000000002) + $pt[3];
				}

				$this->pdf->Polygon($pt1, 'F');
				$this->pdf->Polygon($pt2, 'F');
			}
			else if ($type == 'solid') {
				$this->pdf->Polygon($pt, 'F');
			}
		}

		protected function _prepareTransform($transform)
		{
			if (!$transform) {
				return NULL;
			}

			if (!preg_match_all('/([a-z]+)\\(([^\\)]*)\\)/isU', $transform, $match)) {
				return NULL;
			}

			$actions = array();

			for ($k = 0; $k < count($match[0]); $k++) {
				$name = strtolower($match[1][$k]);
				$val = explode(',', trim($match[2][$k]));

				foreach ($val as $i => $j) {
					$val[$i] = trim($j);
				}

				switch ($name) {
				case 'scale':
					if (!isset($val[0])) {
						$val[0] = 1;
					}
					else {
						$val[0] = 1 * $val[0];
					}

					if (!isset($val[1])) {
						$val[1] = $val[0];
					}
					else {
						$val[1] = 1 * $val[1];
					}

					$actions[] = array($val[0], 0, 0, $val[1], 0, 0);
					break;

				case 'translate':
					if (!isset($val[0])) {
						$val[0] = 0;
					}
					else {
						$val[0] = $this->parsingCss->ConvertToMM($val[0], $this->_isInDraw['w']);
					}

					if (!isset($val[1])) {
						$val[1] = 0;
					}
					else {
						$val[1] = $this->parsingCss->ConvertToMM($val[1], $this->_isInDraw['h']);
					}

					$actions[] = array(1, 0, 0, 1, $val[0], $val[1]);
					break;

				case 'rotate':
					if (!isset($val[0])) {
						$val[0] = 0;
					}
					else {
						$val[0] = ($val[0] * M_PI) / 180;
					}

					if (!isset($val[1])) {
						$val[1] = 0;
					}
					else {
						$val[1] = $this->parsingCss->ConvertToMM($val[1], $this->_isInDraw['w']);
					}

					if (!isset($val[2])) {
						$val[2] = 0;
					}
					else {
						$val[2] = $this->parsingCss->ConvertToMM($val[2], $this->_isInDraw['h']);
					}

					if ($val[1] || $val[2]) {
						$actions[] = array(1, 0, 0, 1, 0 - $val[1], 0 - $val[2]);
					}

					$actions[] = array(cos($val[0]), sin($val[0]), 0 - sin($val[0]), cos($val[0]), 0, 0);
					if ($val[1] || $val[2]) {
						$actions[] = array(1, 0, 0, 1, $val[1], $val[2]);
					}

					break;

				case 'skewx':
					if (!isset($val[0])) {
						$val[0] = 0;
					}
					else {
						$val[0] = ($val[0] * M_PI) / 180;
					}

					$actions[] = array(1, 0, tan($val[0]), 1, 0, 0);
					break;

				case 'skewy':
					if (!isset($val[0])) {
						$val[0] = 0;
					}
					else {
						$val[0] = ($val[0] * M_PI) / 180;
					}

					$actions[] = array(1, tan($val[0]), 0, 1, 0, 0);
					break;

				case 'matrix':
					if (!isset($val[0])) {
						$val[0] = 0;
					}
					else {
						$val[0] = $val[0] * 1;
					}

					if (!isset($val[1])) {
						$val[1] = 0;
					}
					else {
						$val[1] = $val[1] * 1;
					}

					if (!isset($val[2])) {
						$val[2] = 0;
					}
					else {
						$val[2] = $val[2] * 1;
					}

					if (!isset($val[3])) {
						$val[3] = 0;
					}
					else {
						$val[3] = $val[3] * 1;
					}

					if (!isset($val[4])) {
						$val[4] = 0;
					}
					else {
						$val[4] = $this->parsingCss->ConvertToMM($val[4], $this->_isInDraw['w']);
					}

					if (!isset($val[5])) {
						$val[5] = 0;
					}
					else {
						$val[5] = $this->parsingCss->ConvertToMM($val[5], $this->_isInDraw['h']);
					}

					$actions[] = $val;
					break;
				}
			}

			if (!$actions) {
				return NULL;
			}

			$m = $actions[0];
			unset($actions[0]);

			foreach ($actions as $n) {
				$m = array(($m[0] * $n[0]) + ($m[2] * $n[1]), ($m[1] * $n[0]) + ($m[3] * $n[1]), ($m[0] * $n[2]) + ($m[2] * $n[3]), ($m[1] * $n[2]) + ($m[3] * $n[3]), ($m[0] * $n[4]) + ($m[2] * $n[5]) + $m[4], ($m[1] * $n[4]) + ($m[3] * $n[5]) + $m[5]);
			}

			return $m;
		}

		protected function _calculateTableCellSize(&$cases, &$corr)
		{
			if (!isset($corr[0])) {
				return true;
			}

			$sw = array();

			for ($x = 0; $x < count($corr[0]); $x++) {
				$m = 0;

				for ($y = 0; $y < count($corr); $y++) {
					if (isset($corr[$y][$x]) && is_array($corr[$y][$x]) && ($corr[$y][$x][2] == 1)) {
						$m = max($m, $cases[$corr[$y][$x][1]][$corr[$y][$x][0]]['w']);
					}
				}

				$sw[$x] = $m;
			}

			for ($x = 0; $x < count($corr[0]); $x++) {
				for ($y = 0; $y < count($corr); $y++) {
					if (isset($corr[$y][$x]) && is_array($corr[$y][$x]) && (1 < $corr[$y][$x][2])) {
						$s = 0;

						for ($i = 0; $i < $corr[$y][$x][2]; $i++) {
							$s += $sw[$x + $i];
						}

						if ((0 < $s) && ($s < $cases[$corr[$y][$x][1]][$corr[$y][$x][0]]['w'])) {
							for ($i = 0; $i < $corr[$y][$x][2]; $i++) {
								$sw[$x + $i] = ($sw[$x + $i] / $s) * $cases[$corr[$y][$x][1]][$corr[$y][$x][0]]['w'];
							}
						}
					}
				}
			}

			for ($x = 0; $x < count($corr[0]); $x++) {
				for ($y = 0; $y < count($corr); $y++) {
					if (isset($corr[$y][$x]) && is_array($corr[$y][$x])) {
						if ($corr[$y][$x][2] == 1) {
							$cases[$corr[$y][$x][1]][$corr[$y][$x][0]]['w'] = $sw[$x];
						}
						else {
							$s = 0;

							for ($i = 0; $i < $corr[$y][$x][2]; $i++) {
								$s += $sw[$x + $i];
							}

							$cases[$corr[$y][$x][1]][$corr[$y][$x][0]]['w'] = $s;
						}
					}
				}
			}

			$sh = array();

			for ($y = 0; $y < count($corr); $y++) {
				$m = 0;

				for ($x = 0; $x < count($corr[0]); $x++) {
					if (isset($corr[$y][$x]) && is_array($corr[$y][$x]) && ($corr[$y][$x][3] == 1)) {
						$m = max($m, $cases[$corr[$y][$x][1]][$corr[$y][$x][0]]['h']);
					}
				}

				$sh[$y] = $m;
			}

			for ($y = 0; $y < count($corr); $y++) {
				for ($x = 0; $x < count($corr[0]); $x++) {
					if (isset($corr[$y][$x]) && is_array($corr[$y][$x]) && (1 < $corr[$y][$x][3])) {
						$s = 0;

						for ($i = 0; $i < $corr[$y][$x][3]; $i++) {
							$s += $sh[$y + $i];
						}

						if ((0 < $s) && ($s < $cases[$corr[$y][$x][1]][$corr[$y][$x][0]]['h'])) {
							for ($i = 0; $i < $corr[$y][$x][3]; $i++) {
								$sh[$y + $i] = ($sh[$y + $i] / $s) * $cases[$corr[$y][$x][1]][$corr[$y][$x][0]]['h'];
							}
						}
					}
				}
			}

			for ($y = 0; $y < count($corr); $y++) {
				for ($x = 0; $x < count($corr[0]); $x++) {
					if (isset($corr[$y][$x]) && is_array($corr[$y][$x])) {
						if ($corr[$y][$x][3] == 1) {
							$cases[$corr[$y][$x][1]][$corr[$y][$x][0]]['h'] = $sh[$y];
						}
						else {
							$s = 0;

							for ($i = 0; $i < $corr[$y][$x][3]; $i++) {
								$s += $sh[$y + $i];
							}

							$cases[$corr[$y][$x][1]][$corr[$y][$x][0]]['h'] = $s;

							for ($j = 1; $j < $corr[$y][$x][3]; $j++) {
								$tx = $x + 1;

								for ($ty = $y + $j; isset($corr[$ty][$tx]) && !is_array($corr[$ty][$tx]); $tx++) {
								}

								if (isset($corr[$ty][$tx])) {
									$cases[$corr[$ty][$tx][1]][$corr[$ty][$tx][0]]['dw'] += $cases[$corr[$y][$x][1]][$corr[$y][$x][0]]['w'];
								}
							}
						}
					}
				}
			}
		}

		protected function _tag_open_PAGE($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			if ($this->_debugActif) {
				$this->_DEBUG_add('PAGE ' . ($this->_page + 1), true);
			}

			$newPageSet = !isset($param['pageset']) || ($param['pageset'] != 'old');
			$resetPageNumber = isset($param['pagegroup']) && ($param['pagegroup'] == 'new');
			$this->_maxH = 0;

			if ($newPageSet) {
				$this->_subHEADER = array();
				$this->_subFOOTER = array();
				$orientation = '';

				if (isset($param['orientation'])) {
					$param['orientation'] = strtolower($param['orientation']);

					if ($param['orientation'] == 'p') {
						$orientation = 'P';
					}

					if ($param['orientation'] == 'portrait') {
						$orientation = 'P';
					}

					if ($param['orientation'] == 'l') {
						$orientation = 'L';
					}

					if ($param['orientation'] == 'paysage') {
						$orientation = 'L';
					}

					if ($param['orientation'] == 'landscape') {
						$orientation = 'L';
					}
				}

				$format = NULL;

				if (isset($param['format'])) {
					$format = strtolower($param['format']);

					if (preg_match('/^([0-9]+)x([0-9]+)$/isU', $format, $match)) {
						$format = array(intval($match[1]), intval($match[2]));
					}
				}

				$background = array();

				if (isset($param['backimg'])) {
					$background['img'] = isset($param['backimg']) ? $param['backimg'] : '';
					$background['posX'] = isset($param['backimgx']) ? $param['backimgx'] : 'center';
					$background['posY'] = isset($param['backimgy']) ? $param['backimgy'] : 'middle';
					$background['width'] = isset($param['backimgw']) ? $param['backimgw'] : '100%';
					$background['img'] = str_replace('&amp;', '&', $background['img']);

					if ($background['posX'] == 'left') {
						$background['posX'] = '0%';
					}

					if ($background['posX'] == 'center') {
						$background['posX'] = '50%';
					}

					if ($background['posX'] == 'right') {
						$background['posX'] = '100%';
					}

					if ($background['posY'] == 'top') {
						$background['posY'] = '0%';
					}

					if ($background['posY'] == 'middle') {
						$background['posY'] = '50%';
					}

					if ($background['posY'] == 'bottom') {
						$background['posY'] = '100%';
					}

					if ($background['img']) {
						$infos = @getimagesize($background['img']);

						if (1 < count($infos)) {
							$imageWidth = $this->parsingCss->ConvertToMM($background['width'], $this->pdf->getW());
							$imageHeight = ($imageWidth * $infos[1]) / $infos[0];
							$background['width'] = $imageWidth;
							$background['posX'] = $this->parsingCss->ConvertToMM($background['posX'], $this->pdf->getW() - $imageWidth);
							$background['posY'] = $this->parsingCss->ConvertToMM($background['posY'], $this->pdf->getH() - $imageHeight);
						}
						else {
							$background = array();
						}
					}
					else {
						$background = array();
					}
				}

				$background['top'] = isset($param['backtop']) ? $param['backtop'] : '0';
				$background['bottom'] = isset($param['backbottom']) ? $param['backbottom'] : '0';
				$background['left'] = isset($param['backleft']) ? $param['backleft'] : '0';
				$background['right'] = isset($param['backright']) ? $param['backright'] : '0';

				if (preg_match('/^([0-9]*)$/isU', $background['top'])) {
					$background['top'] .= 'mm';
				}

				if (preg_match('/^([0-9]*)$/isU', $background['bottom'])) {
					$background['bottom'] .= 'mm';
				}

				if (preg_match('/^([0-9]*)$/isU', $background['left'])) {
					$background['left'] .= 'mm';
				}

				if (preg_match('/^([0-9]*)$/isU', $background['right'])) {
					$background['right'] .= 'mm';
				}

				$background['top'] = $this->parsingCss->ConvertToMM($background['top'], $this->pdf->getH());
				$background['bottom'] = $this->parsingCss->ConvertToMM($background['bottom'], $this->pdf->getH());
				$background['left'] = $this->parsingCss->ConvertToMM($background['left'], $this->pdf->getW());
				$background['right'] = $this->parsingCss->ConvertToMM($background['right'], $this->pdf->getW());
				$res = false;
				$background['color'] = isset($param['backcolor']) ? $this->parsingCss->convertToColor($param['backcolor'], $res) : NULL;

				if (!$res) {
					$background['color'] = NULL;
				}

				$this->parsingCss->save();
				$this->parsingCss->analyse('PAGE', $param);
				$this->parsingCss->setPosition();
				$this->parsingCss->fontSet();
				$this->_setNewPage($format, $orientation, $background, NULL, $resetPageNumber);

				if (isset($param['footer'])) {
					$lst = explode(';', $param['footer']);

					foreach ($lst as $key => $val) {
						$lst[$key] = trim(strtolower($val));
					}

					$page = in_array('page', $lst);
					$date = in_array('date', $lst);
					$hour = in_array('heure', $lst);
					$form = in_array('form', $lst);
				}
				else {
					$page = NULL;
					$date = NULL;
					$hour = NULL;
					$form = NULL;
				}

				$this->pdf->SetMyFooter($page, $date, $hour, $form);
			}
			else {
				$this->parsingCss->save();
				$this->parsingCss->analyse('PAGE', $param);
				$this->parsingCss->setPosition();
				$this->parsingCss->fontSet();
				$this->_setNewPage(NULL, NULL, NULL, NULL, $resetPageNumber);
			}

			return true;
		}

		protected function _tag_close_PAGE($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_maxH = 0;
			$this->parsingCss->load();
			$this->parsingCss->fontSet();

			if ($this->_debugActif) {
				$this->_DEBUG_add('PAGE ' . $this->_page, false);
			}

			return true;
		}

		protected function _tag_open_PAGE_HEADER($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_subHEADER = array();
			$this->_parsePos;

			for (; $this->_parsePos < count($this->parsingHtml->code); $this->_parsePos++) {
				$action = $this->parsingHtml->code[$this->_parsePos];

				if ($action['name'] == 'page_header') {
					$action['name'] = 'page_header_sub';
				}

				$this->_subHEADER[] = $action;
				if ((strtolower($action['name']) == 'page_header_sub') && $action['close']) {
					break;
				}
			}

			$this->_setPageHeader();
			return true;
		}

		protected function _tag_open_PAGE_FOOTER($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_subFOOTER = array();
			$this->_parsePos;

			for (; $this->_parsePos < count($this->parsingHtml->code); $this->_parsePos++) {
				$action = $this->parsingHtml->code[$this->_parsePos];

				if ($action['name'] == 'page_footer') {
					$action['name'] = 'page_footer_sub';
				}

				$this->_subFOOTER[] = $action;
				if ((strtolower($action['name']) == 'page_footer_sub') && $action['close']) {
					break;
				}
			}

			$this->_setPageFooter();
			return true;
		}

		protected function _tag_open_PAGE_HEADER_SUB($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_subSTATES = array();
			$this->_subSTATES['x'] = $this->pdf->getX();
			$this->_subSTATES['y'] = $this->pdf->getY();
			$this->_subSTATES['s'] = $this->parsingCss->value;
			$this->_subSTATES['t'] = $this->parsingCss->table;
			$this->_subSTATES['ml'] = $this->_margeLeft;
			$this->_subSTATES['mr'] = $this->_margeRight;
			$this->_subSTATES['mt'] = $this->_margeTop;
			$this->_subSTATES['mb'] = $this->_margeBottom;
			$this->_subSTATES['mp'] = $this->_pageMarges;
			$this->_pageMarges = array();
			$this->_margeLeft = $this->_defaultLeft;
			$this->_margeRight = $this->_defaultRight;
			$this->_margeTop = $this->_defaultTop;
			$this->_margeBottom = $this->_defaultBottom;
			$this->pdf->SetMargins($this->_margeLeft, $this->_margeTop, $this->_margeRight);
			$this->pdf->SetAutoPageBreak(false, $this->_margeBottom);
			$this->pdf->setXY($this->_defaultLeft, $this->_defaultTop);
			$this->parsingCss->initStyle();
			$this->parsingCss->resetStyle();
			$this->parsingCss->value['width'] = $this->pdf->getW() - $this->_defaultLeft - $this->_defaultRight;
			$this->parsingCss->table = array();
			$this->parsingCss->save();
			$this->parsingCss->analyse('page_header_sub', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			$this->_setNewPositionForNewLine();
			return true;
		}

		protected function _tag_close_PAGE_HEADER_SUB($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->parsingCss->load();
			$this->parsingCss->value = $this->_subSTATES['s'];
			$this->parsingCss->table = $this->_subSTATES['t'];
			$this->_pageMarges = $this->_subSTATES['mp'];
			$this->_margeLeft = $this->_subSTATES['ml'];
			$this->_margeRight = $this->_subSTATES['mr'];
			$this->_margeTop = $this->_subSTATES['mt'];
			$this->_margeBottom = $this->_subSTATES['mb'];
			$this->pdf->SetMargins($this->_margeLeft, $this->_margeTop, $this->_margeRight);
			$this->pdf->setbMargin($this->_margeBottom);
			$this->pdf->SetAutoPageBreak(false, $this->_margeBottom);
			$this->pdf->setXY($this->_subSTATES['x'], $this->_subSTATES['y']);
			$this->parsingCss->fontSet();
			$this->_maxH = 0;
			return true;
		}

		protected function _tag_open_PAGE_FOOTER_SUB($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_subSTATES = array();
			$this->_subSTATES['x'] = $this->pdf->getX();
			$this->_subSTATES['y'] = $this->pdf->getY();
			$this->_subSTATES['s'] = $this->parsingCss->value;
			$this->_subSTATES['t'] = $this->parsingCss->table;
			$this->_subSTATES['ml'] = $this->_margeLeft;
			$this->_subSTATES['mr'] = $this->_margeRight;
			$this->_subSTATES['mt'] = $this->_margeTop;
			$this->_subSTATES['mb'] = $this->_margeBottom;
			$this->_subSTATES['mp'] = $this->_pageMarges;
			$this->_pageMarges = array();
			$this->_margeLeft = $this->_defaultLeft;
			$this->_margeRight = $this->_defaultRight;
			$this->_margeTop = $this->_defaultTop;
			$this->_margeBottom = $this->_defaultBottom;
			$this->pdf->SetMargins($this->_margeLeft, $this->_margeTop, $this->_margeRight);
			$this->pdf->SetAutoPageBreak(false, $this->_margeBottom);
			$this->pdf->setXY($this->_defaultLeft, $this->_defaultTop);
			$this->parsingCss->initStyle();
			$this->parsingCss->resetStyle();
			$this->parsingCss->value['width'] = $this->pdf->getW() - $this->_defaultLeft - $this->_defaultRight;
			$this->parsingCss->table = array();
			$sub = NULL;
			$this->_createSubHTML($sub);
			$sub->parsingHtml->code = $this->parsingHtml->getLevel($this->_parsePos);
			$sub->_makeHTMLcode();
			$this->pdf->setY($this->pdf->getH() - $sub->_maxY - $this->_defaultBottom - 0.01);
			$this->_destroySubHTML($sub);
			$this->parsingCss->save();
			$this->parsingCss->analyse('page_footer_sub', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			$this->_setNewPositionForNewLine();
			return true;
		}

		protected function _tag_close_PAGE_FOOTER_SUB($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->parsingCss->load();
			$this->parsingCss->value = $this->_subSTATES['s'];
			$this->parsingCss->table = $this->_subSTATES['t'];
			$this->_pageMarges = $this->_subSTATES['mp'];
			$this->_margeLeft = $this->_subSTATES['ml'];
			$this->_margeRight = $this->_subSTATES['mr'];
			$this->_margeTop = $this->_subSTATES['mt'];
			$this->_margeBottom = $this->_subSTATES['mb'];
			$this->pdf->SetMargins($this->_margeLeft, $this->_margeTop, $this->_margeRight);
			$this->pdf->SetAutoPageBreak(false, $this->_margeBottom);
			$this->pdf->setXY($this->_subSTATES['x'], $this->_subSTATES['y']);
			$this->parsingCss->fontSet();
			$this->_maxH = 0;
			return true;
		}

		protected function _tag_open_NOBREAK($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_maxH = 0;
			$sub = NULL;
			$this->_createSubHTML($sub);
			$sub->parsingHtml->code = $this->parsingHtml->getLevel($this->_parsePos);
			$sub->_makeHTMLcode();
			$y = $this->pdf->getY();
			if (($sub->_maxY < ($this->pdf->getH() - $this->pdf->gettMargin() - $this->pdf->getbMargin())) && (($this->pdf->getH() - $this->pdf->getbMargin()) <= $y + $sub->_maxY)) {
				$this->_setNewPage();
			}

			$this->_destroySubHTML($sub);
			return true;
		}

		protected function _tag_close_NOBREAK($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_maxH = 0;
			return true;
		}

		protected function _tag_open_DIV($param, $other = 'div')
		{
			if ($this->_isForOneLine) {
				return false;
			}

			if ($this->_debugActif) {
				$this->_DEBUG_add(strtoupper($other), true);
			}

			$this->parsingCss->save();
			$this->parsingCss->analyse($other, $param);
			$this->parsingCss->fontSet();

			if (in_array($other, array('fieldset', 'legend'))) {
				if (isset($param['moveTop'])) {
					$this->parsingCss->value['margin']['t'] += $param['moveTop'];
				}

				if (isset($param['moveLeft'])) {
					$this->parsingCss->value['margin']['l'] += $param['moveLeft'];
				}

				if (isset($param['moveDown'])) {
					$this->parsingCss->value['margin']['b'] += $param['moveDown'];
				}
			}

			$alignObject = NULL;

			if ($this->parsingCss->value['margin-auto']) {
				$alignObject = 'center';
			}

			$marge = array();
			$marge['l'] = $this->parsingCss->value['border']['l']['width'] + $this->parsingCss->value['padding']['l'] + 0.029999999999999999;
			$marge['r'] = $this->parsingCss->value['border']['r']['width'] + $this->parsingCss->value['padding']['r'] + 0.029999999999999999;
			$marge['t'] = $this->parsingCss->value['border']['t']['width'] + $this->parsingCss->value['padding']['t'] + 0.029999999999999999;
			$marge['b'] = $this->parsingCss->value['border']['b']['width'] + $this->parsingCss->value['padding']['b'] + 0.029999999999999999;
			$level = $this->parsingHtml->getLevel($this->_parsePos);
			$w = 0;
			$h = 0;

			if (count($level)) {
				$sub = NULL;
				$this->_createSubHTML($sub);
				$sub->parsingHtml->code = $level;
				$sub->_makeHTMLcode();
				$w = $sub->_maxX;
				$h = $sub->_maxY;
				$this->_destroySubHTML($sub);
			}

			$wReel = $w;
			$hReel = $h;
			$w += $marge['l'] + $marge['r'] + 0.001;
			$h += $marge['t'] + $marge['b'] + 0.001;

			if ($this->parsingCss->value['overflow'] == 'hidden') {
				$overW = max($w, $this->parsingCss->value['width']);
				$overH = max($h, $this->parsingCss->value['height']);
				$overflow = true;
				$this->parsingCss->value['old_maxX'] = $this->_maxX;
				$this->parsingCss->value['old_maxY'] = $this->_maxY;
				$this->parsingCss->value['old_maxH'] = $this->_maxH;
				$this->parsingCss->value['old_overflow'] = $this->_isInOverflow;
				$this->_isInOverflow = true;
			}
			else {
				$overW = NULL;
				$overH = NULL;
				$overflow = false;
				$this->parsingCss->value['width'] = max($w, $this->parsingCss->value['width']);
				$this->parsingCss->value['height'] = max($h, $this->parsingCss->value['height']);
			}

			switch ($this->parsingCss->value['rotate']) {
			case 90:
				$tmp = $overH;
				$overH = $overW;
				$overW = $tmp;
				$tmp = $hReel;
				$hReel = $wReel;
				$wReel = $tmp;
				unset($tmp);
				$w = $this->parsingCss->value['height'];
				$h = $this->parsingCss->value['width'];
				$tX = 0 - $h;
				$tY = 0;
				break;

			case 180:
				$w = $this->parsingCss->value['width'];
				$h = $this->parsingCss->value['height'];
				$tX = 0 - $w;
				$tY = 0 - $h;
				break;

			case 270:
				$tmp = $overH;
				$overH = $overW;
				$overW = $tmp;
				$tmp = $hReel;
				$hReel = $wReel;
				$wReel = $tmp;
				unset($tmp);
				$w = $this->parsingCss->value['height'];
				$h = $this->parsingCss->value['width'];
				$tX = 0;
				$tY = 0 - $w;
				break;

			default:
				$w = $this->parsingCss->value['width'];
				$h = $this->parsingCss->value['height'];
				$tX = 0;
				$tY = 0;
				break;
			}

			if (!$this->parsingCss->value['position']) {
				if (($w < ($this->pdf->getW() - $this->pdf->getlMargin() - $this->pdf->getrMargin())) && (($this->pdf->getW() - $this->pdf->getrMargin()) <= $this->pdf->getX() + $w)) {
					$this->_tag_open_BR(array());
				}

				if (($h < ($this->pdf->getH() - $this->pdf->gettMargin() - $this->pdf->getbMargin())) && (($this->pdf->getH() - $this->pdf->getbMargin()) <= $this->pdf->getY() + $h) && !$this->_isInOverflow) {
					$this->_setNewPage();
				}

				$old = $this->parsingCss->getOldValues();
				$parentWidth = ($old['width'] ? $old['width'] : $this->pdf->getW() - $this->pdf->getlMargin() - $this->pdf->getrMargin());

				if ($w < $parentWidth) {
					if ($alignObject == 'center') {
						$this->pdf->setX($this->pdf->getX() + (($parentWidth - $w) * 0.5));
					}
					else if ($alignObject == 'right') {
						$this->pdf->setX(($this->pdf->getX() + $parentWidth) - $w);
					}
				}

				$this->parsingCss->setPosition();
			}
			else {
				$old = $this->parsingCss->getOldValues();
				$parentWidth = ($old['width'] ? $old['width'] : $this->pdf->getW() - $this->pdf->getlMargin() - $this->pdf->getrMargin());

				if ($w < $parentWidth) {
					if ($alignObject == 'center') {
						$this->pdf->setX($this->pdf->getX() + (($parentWidth - $w) * 0.5));
					}
					else if ($alignObject == 'right') {
						$this->pdf->setX(($this->pdf->getX() + $parentWidth) - $w);
					}
				}

				$this->parsingCss->setPosition();
				$this->_saveMax();
				$this->_maxX = 0;
				$this->_maxY = 0;
				$this->_maxH = 0;
				$this->_maxE = 0;
			}

			if ($this->parsingCss->value['rotate']) {
				$this->pdf->startTransform();
				$this->pdf->setRotation($this->parsingCss->value['rotate']);
				$this->pdf->setTranslate($tX, $tY);
			}

			$this->_drawRectangle($this->parsingCss->value['x'], $this->parsingCss->value['y'], $this->parsingCss->value['width'], $this->parsingCss->value['height'], $this->parsingCss->value['border'], $this->parsingCss->value['padding'], 0, $this->parsingCss->value['background']);
			$marge = array();
			$marge['l'] = $this->parsingCss->value['border']['l']['width'] + $this->parsingCss->value['padding']['l'] + 0.029999999999999999;
			$marge['r'] = $this->parsingCss->value['border']['r']['width'] + $this->parsingCss->value['padding']['r'] + 0.029999999999999999;
			$marge['t'] = $this->parsingCss->value['border']['t']['width'] + $this->parsingCss->value['padding']['t'] + 0.029999999999999999;
			$marge['b'] = $this->parsingCss->value['border']['b']['width'] + $this->parsingCss->value['padding']['b'] + 0.029999999999999999;
			$this->parsingCss->value['width'] -= $marge['l'] + $marge['r'];
			$this->parsingCss->value['height'] -= $marge['t'] + $marge['b'];
			$xCorr = 0;
			$yCorr = 0;
			if (!$this->_subPart && !$this->_isSubPart) {
				switch ($this->parsingCss->value['text-align']) {
				case 'right':
					$xCorr = $this->parsingCss->value['width'] - $wReel;
					break;

				case 'center':
					$xCorr = ($this->parsingCss->value['width'] - $wReel) * 0.5;
					break;
				}

				if (0 < $xCorr) {
					$xCorr = 0;
				}

				switch ($this->parsingCss->value['vertical-align']) {
				case 'bottom':
					$yCorr = $this->parsingCss->value['height'] - $hReel;
					break;

				case 'middle':
					$yCorr = ($this->parsingCss->value['height'] - $hReel) * 0.5;
					break;
				}
			}

			if ($overflow) {
				$overW -= $marge['l'] + $marge['r'];
				$overH -= $marge['t'] + $marge['b'];
				$this->pdf->clippingPathStart($this->parsingCss->value['x'] + $marge['l'], $this->parsingCss->value['y'] + $marge['t'], $this->parsingCss->value['width'], $this->parsingCss->value['height']);
				$this->parsingCss->value['x'] += $xCorr;
				$mL = $this->parsingCss->value['x'] + $marge['l'];
				$mR = $this->pdf->getW() - $mL - $overW;
			}
			else {
				$mL = $this->parsingCss->value['x'] + $marge['l'];
				$mR = $this->pdf->getW() - $mL - $this->parsingCss->value['width'];
			}

			$x = $this->parsingCss->value['x'] + $marge['l'];
			$y = $this->parsingCss->value['y'] + $marge['t'] + $yCorr;
			$this->_saveMargin($mL, 0, $mR);
			$this->pdf->setXY($x, $y);
			$this->_setNewPositionForNewLine();
			return true;
		}

		protected function _tag_open_BLOCKQUOTE($param)
		{
			return $this->_tag_open_DIV($param, 'blockquote');
		}

		protected function _tag_open_LEGEND($param)
		{
			return $this->_tag_open_DIV($param, 'legend');
		}

		protected function _tag_open_FIELDSET($param)
		{
			$this->parsingCss->save();
			$this->parsingCss->analyse('fieldset', $param);

			for ($tempPos = $this->_parsePos + 1; $tempPos < count($this->parsingHtml->code); $tempPos++) {
				$action = $this->parsingHtml->code[$tempPos];

				if ($action['name'] == 'fieldset') {
					break;
				}

				if (($action['name'] == 'legend') && !$action['close']) {
					$legendOpenPos = $tempPos;
					$sub = NULL;
					$this->_createSubHTML($sub);
					$sub->parsingHtml->code = $this->parsingHtml->getLevel($tempPos - 1);
					$res = NULL;

					for ($sub->_parsePos = 0; $sub->_parsePos < count($sub->parsingHtml->code); $sub->_parsePos++) {
						$action = $sub->parsingHtml->code[$sub->_parsePos];
						$sub->_executeAction($action);
						if (($action['name'] == 'legend') && $action['close']) {
							break;
						}
					}

					$legendH = $sub->_maxY;
					$this->_destroySubHTML($sub);
					$move = $this->parsingCss->value['padding']['t'] + $this->parsingCss->value['border']['t']['width'] + 0.029999999999999999;
					$param['moveTop'] = $legendH / 2;
					$this->parsingHtml->code[$legendOpenPos]['param']['moveTop'] = 0 - (($legendH / 2) + $move);
					$this->parsingHtml->code[$legendOpenPos]['param']['moveLeft'] = 2 - $this->parsingCss->value['border']['l']['width'] - $this->parsingCss->value['padding']['l'];
					$this->parsingHtml->code[$legendOpenPos]['param']['moveDown'] = $move;
					break;
				}
			}

			$this->parsingCss->load();
			return $this->_tag_open_DIV($param, 'fieldset');
		}

		protected function _tag_close_DIV($param, $other = 'div')
		{
			if ($this->_isForOneLine) {
				return false;
			}

			if ($this->parsingCss->value['overflow'] == 'hidden') {
				$this->_maxX = $this->parsingCss->value['old_maxX'];
				$this->_maxY = $this->parsingCss->value['old_maxY'];
				$this->_maxH = $this->parsingCss->value['old_maxH'];
				$this->_isInOverflow = $this->parsingCss->value['old_overflow'];
				$this->pdf->clippingPathStop();
			}

			if ($this->parsingCss->value['rotate']) {
				$this->pdf->stopTransform();
			}

			$marge = array();
			$marge['l'] = $this->parsingCss->value['border']['l']['width'] + $this->parsingCss->value['padding']['l'] + 0.029999999999999999;
			$marge['r'] = $this->parsingCss->value['border']['r']['width'] + $this->parsingCss->value['padding']['r'] + 0.029999999999999999;
			$marge['t'] = $this->parsingCss->value['border']['t']['width'] + $this->parsingCss->value['padding']['t'] + 0.029999999999999999;
			$marge['b'] = $this->parsingCss->value['border']['b']['width'] + $this->parsingCss->value['padding']['b'] + 0.029999999999999999;
			$x = $this->parsingCss->value['x'];
			$y = $this->parsingCss->value['y'];
			$w = $this->parsingCss->value['width'] + $marge['l'] + $marge['r'] + $this->parsingCss->value['margin']['r'];
			$h = $this->parsingCss->value['height'] + $marge['t'] + $marge['b'] + $this->parsingCss->value['margin']['b'];

			switch ($this->parsingCss->value['rotate']) {
			case 90:
				$t = $w;
				$w = $h;
				$h = $t;
				break;

			case 270:
				$t = $w;
				$w = $h;
				$h = $t;
				break;

			default:
				break;
			}

			if ($this->parsingCss->value['position'] != 'absolute') {
				$this->pdf->setXY($x + $w, $y);
				$this->_maxX = max($this->_maxX, $x + $w);
				$this->_maxY = max($this->_maxY, $y + $h);
				$this->_maxH = max($this->_maxH, $h);
			}
			else {
				$this->pdf->setXY($this->parsingCss->value['xc'], $this->parsingCss->value['yc']);
				$this->_loadMax();
			}

			$block = ($this->parsingCss->value['display'] != 'inline') && ($this->parsingCss->value['position'] != 'absolute');
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			$this->_loadMargin();

			if ($block) {
				$this->_tag_open_BR(array());
			}

			if ($this->_debugActif) {
				$this->_DEBUG_add(strtoupper($other), false);
			}

			return true;
		}

		protected function _tag_close_BLOCKQUOTE($param)
		{
			return $this->_tag_close_DIV($param, 'blockquote');
		}

		protected function _tag_close_FIELDSET($param)
		{
			return $this->_tag_close_DIV($param, 'fieldset');
		}

		protected function _tag_close_LEGEND($param)
		{
			return $this->_tag_close_DIV($param, 'legend');
		}

		protected function _tag_open_BARCODE($param)
		{
			$lstBarcode = array();
			$lstBarcode['UPC_A'] = 'UPCA';
			$lstBarcode['CODE39'] = 'C39';

			if (!isset($param['type'])) {
				$param['type'] = 'C39';
			}

			if (!isset($param['value'])) {
				$param['value'] = 0;
			}

			if (!isset($param['label'])) {
				$param['label'] = 'label';
			}

			if (!isset($param['style']['color'])) {
				$param['style']['color'] = '#000000';
			}

			if ($this->_testIsDeprecated && (isset($param['bar_h']) || isset($param['bar_w']))) {
				throw new HTML2PDF_exception(9, array('BARCODE', 'bar_h, bar_w'));
			}

			$param['type'] = strtoupper($param['type']);

			if (isset($lstBarcode[$param['type']])) {
				$param['type'] = $lstBarcode[$param['type']];
			}

			$this->parsingCss->save();
			$this->parsingCss->analyse('barcode', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			$x = $this->pdf->getX();
			$y = $this->pdf->getY();
			$w = $this->parsingCss->value['width'];

			if (!$w) {
				$w = $this->parsingCss->ConvertToMM('50mm');
			}

			$h = $this->parsingCss->value['height'];

			if (!$h) {
				$h = $this->parsingCss->ConvertToMM('10mm');
			}

			$txt = ($param['label'] !== 'none' ? $this->parsingCss->value['font-size'] : false);
			$c = $this->parsingCss->value['color'];
			$infos = $this->pdf->myBarcode($param['value'], $param['type'], $x, $y, $w, $h, $txt, $c);
			$this->_maxX = max($this->_maxX, $x + $infos[0]);
			$this->_maxY = max($this->_maxY, $y + $infos[1]);
			$this->_maxH = max($this->_maxH, $infos[1]);
			$this->_maxE++;
			$this->pdf->setXY($x + $infos[0], $y);
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_BARCODE($param)
		{
			return true;
		}

		protected function _tag_open_QRCODE($param)
		{
			if ($this->_testIsDeprecated && (isset($param['size']) || isset($param['noborder']))) {
				throw new HTML2PDF_exception(9, array('QRCODE', 'size, noborder'));
			}

			if ($this->_debugActif) {
				$this->_DEBUG_add('QRCODE');
			}

			if (!isset($param['value'])) {
				$param['value'] = '';
			}

			if (!isset($param['ec'])) {
				$param['ec'] = 'H';
			}

			if (!isset($param['style']['color'])) {
				$param['style']['color'] = '#000000';
			}

			if (!isset($param['style']['background-color'])) {
				$param['style']['background-color'] = '#FFFFFF';
			}

			if (isset($param['style']['border'])) {
				$borders = $param['style']['border'] != 'none';
				unset($param['style']['border']);
			}
			else {
				$borders = true;
			}

			if ($param['value'] === '') {
				return true;
			}

			if (!in_array($param['ec'], array('L', 'M', 'Q', 'H'))) {
				$param['ec'] = 'H';
			}

			$this->parsingCss->save();
			$this->parsingCss->analyse('qrcode', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			$x = $this->pdf->getX();
			$y = $this->pdf->getY();
			$w = $this->parsingCss->value['width'];
			$h = $this->parsingCss->value['height'];
			$size = max($w, $h);

			if (!$size) {
				$size = $this->parsingCss->ConvertToMM('50mm');
			}

			$style = array('fgcolor' => $this->parsingCss->value['color'], 'bgcolor' => $this->parsingCss->value['background']['color']);

			if ($borders) {
				$style['border'] = true;
				$style['padding'] = 'auto';
			}
			else {
				$style['border'] = false;
				$style['padding'] = 0;
			}

			if (!$this->_subPart && !$this->_isSubPart) {
				$this->pdf->write2DBarcode($param['value'], 'QRCODE,' . $param['ec'], $x, $y, $size, $size, $style);
			}

			$this->_maxX = max($this->_maxX, $x + $size);
			$this->_maxY = max($this->_maxY, $y + $size);
			$this->_maxH = max($this->_maxH, $size);
			$this->_maxE++;
			$this->pdf->setX($x + $size);
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_QRCODE($param)
		{
			return true;
		}

		protected function _tag_open_BOOKMARK($param)
		{
			$titre = (isset($param['title']) ? trim($param['title']) : '');
			$level = (isset($param['level']) ? floor($param['level']) : 0);

			if ($level < 0) {
				$level = 0;
			}

			if ($titre) {
				$this->pdf->Bookmark($titre, $level, -1);
			}

			return true;
		}

		protected function _tag_close_BOOKMARK($param)
		{
			return true;
		}

		protected function _tag_open_WRITE($param)
		{
			$fill = ($this->parsingCss->value['background']['color'] !== NULL) && ($this->parsingCss->value['background']['image'] === NULL);

			if (in_array($this->parsingCss->value['id_tag'], array('fieldset', 'legend', 'div', 'table', 'tr', 'td', 'th'))) {
				$fill = false;
			}

			$txt = $param['txt'];

			if ($this->_isAfterFloat) {
				$txt = ltrim($txt);
				$this->_isAfterFloat = false;
			}

			$txt = str_replace('[[page_nb]]', $this->pdf->getMyAliasNbPages(), $txt);
			$txt = str_replace('[[page_cu]]', $this->pdf->getMyNumPage($this->_page), $txt);

			if ($this->parsingCss->value['text-transform'] != 'none') {
				if ($this->parsingCss->value['text-transform'] == 'capitalize') {
					$txt = ucwords($txt);
				}
				else if ($this->parsingCss->value['text-transform'] == 'uppercase') {
					$txt = strtoupper($txt);
				}
				else if ($this->parsingCss->value['text-transform'] == 'lowercase') {
					$txt = strtolower($txt);
				}
			}

			$h = 1.0800000000000001 * $this->parsingCss->value['font-size'];
			$dh = $h * $this->parsingCss->value['mini-decal'];
			$lh = $this->parsingCss->getLineHeight();
			$align = 'L';

			if ($this->parsingCss->value['text-align'] == 'li_right') {
				$w = $this->parsingCss->value['width'];
				$align = 'R';
			}

			$w = 0;
			$words = explode(' ', $txt);

			foreach ($words as $k => $word) {
				$words[$k] = array($word, $this->pdf->GetStringWidth($word));
				$w += $words[$k][1];
			}

			$space = $this->pdf->GetStringWidth(' ');
			$w += $space * (count($words) - 1);
			$currPos = 0;
			$maxX = 0;
			$x = $this->pdf->getX();
			$y = $this->pdf->getY();
			$dy = $this->_getElementY($lh);
			list($left, $right) = $this->_getMargins($y);
			$nb = 0;

			while (($right < ($x + $w)) && ($x < ($right + $space)) && count($words)) {
				$i = 0;
				$old = array('', 0);
				$str = $words[0];
				$add = false;

				while (($x + $str[1]) < $right) {
					$i++;
					$add = true;
					array_shift($words);
					$old = $str;

					if (!count($words)) {
						break;
					}

					$str[0] .= ' ' . $words[0][0];
					$str[1] += $space + $words[0][1];
				}

				$str = $old;
				if (($i == 0) && ($right <= $left + $words[0][1])) {
					$str = $words[0];
					array_shift($words);
					$i++;
					$add = true;
				}

				$currPos += ($currPos ? 1 : 0) + strlen($str[0]);
				$wc = ($align == 'L' ? $str[1] : $this->parsingCss->value['width']);

				if (($right - $left) < $wc) {
					$wc = $right - $left;
				}

				if (strlen($str[0])) {
					$this->pdf->setXY($this->pdf->getX(), $y + $dh + $dy);
					$this->pdf->Cell($wc, $h, $str[0], 0, 0, $align, $fill, $this->_isInLink);
					$this->pdf->setXY($this->pdf->getX(), $y);
				}

				$this->_maxH = max($this->_maxH, $lh);
				$maxX = max($maxX, $this->pdf->getX());
				$w -= $str[1];
				$y = $this->pdf->getY();
				$x = $this->pdf->getX();
				$dy = $this->_getElementY($lh);

				if (count($words)) {
					if ($add) {
						$w -= $space;
					}

					if (!$add && ($words[0][0] === '')) {
						array_shift($words);
					}

					if ($this->_isForOneLine) {
						$this->_maxE += $i;
						$this->_maxX = max($this->_maxX, $maxX);
						return NULL;
					}

					$this->_tag_open_BR(array('style' => ''), $currPos);
					$y = $this->pdf->getY();
					$x = $this->pdf->getX();
					$dy = $this->_getElementY($lh);

					if (($this->pdf->getH() - $this->pdf->getbMargin()) <= $y + $h) {
						if (!$this->_isInOverflow && !$this->_isInFooter) {
							$this->_setNewPage(NULL, '', NULL, $currPos);
							$y = $this->pdf->getY();
							$x = $this->pdf->getX();
							$dy = $this->_getElementY($lh);
						}
					}

					$nb++;

					if (10000 < $nb) {
						$txt = '';

						foreach ($words as $k => $word) {
							$txt .= ($k ? ' ' : '') . $word[0];
						}

						throw new HTML2PDF_exception(2, array($txt, $right - $left, $w));
					}

					list($left, $right) = $this->_getMargins($y);
				}
			}

			if (count($words)) {
				$txt = '';

				foreach ($words as $k => $word) {
					$txt .= ($k ? ' ' : '') . $word[0];
				}

				$w += $this->pdf->getWordSpacing() * count($words);
				$this->pdf->setXY($this->pdf->getX(), $y + $dh + $dy);
				$this->pdf->Cell($align == 'L' ? $w : $this->parsingCss->value['width'], $h, $txt, 0, 0, $align, $fill, $this->_isInLink);
				$this->pdf->setXY($this->pdf->getX(), $y);
				$this->_maxH = max($this->_maxH, $lh);
				$this->_maxE += count($words);
			}

			$maxX = max($maxX, $this->pdf->getX());
			$maxY = $this->pdf->getY() + $h;
			$this->_maxX = max($this->_maxX, $maxX);
			$this->_maxY = max($this->_maxY, $maxY);
			return true;
		}

		protected function _tag_open_BR($param, $curr = NULL)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$h = max($this->_maxH, $this->parsingCss->getLineHeight());

			if ($this->_maxH == 0) {
				$this->_maxY = max($this->_maxY, $this->pdf->getY() + $h);
			}

			$this->_makeBreakLine($h, $curr);
			$this->_maxH = 0;
			$this->_maxE = 0;
			return true;
		}

		protected function _tag_open_HR($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$oldAlign = $this->parsingCss->value['text-align'];
			$this->parsingCss->value['text-align'] = 'left';

			if ($this->_maxH) {
				$this->_tag_open_BR($param);
			}

			$fontSize = $this->parsingCss->value['font-size'];
			$this->parsingCss->value['font-size'] = $fontSize * 0.5;
			$this->_tag_open_BR($param);
			$this->parsingCss->value['font-size'] = 0;
			$param['style']['width'] = '100%';
			$this->parsingCss->save();
			$this->parsingCss->value['height'] = $this->parsingCss->ConvertToMM('1mm');
			$this->parsingCss->analyse('hr', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			$h = $this->parsingCss->value['height'];

			if ($h) {
				$h -= $this->parsingCss->value['border']['t']['width'] + $this->parsingCss->value['border']['b']['width'];
			}

			if ($h <= 0) {
				$h = $this->parsingCss->value['border']['t']['width'] + $this->parsingCss->value['border']['b']['width'];
			}

			$this->_drawRectangle($this->pdf->getX(), $this->pdf->getY(), $this->parsingCss->value['width'], $h, $this->parsingCss->value['border'], 0, 0, $this->parsingCss->value['background']);
			$this->_maxH = $h;
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			$this->_tag_open_BR($param);
			$this->parsingCss->value['font-size'] = $fontSize * 0.5;
			$this->_tag_open_BR($param);
			$this->parsingCss->value['font-size'] = $fontSize;
			$this->parsingCss->value['text-align'] = $oldAlign;
			$this->_setNewPositionForNewLine();
			return true;
		}

		protected function _tag_open_B($param, $other = 'b')
		{
			$this->parsingCss->save();
			$this->parsingCss->value['font-bold'] = true;
			$this->parsingCss->analyse($other, $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_STRONG($param)
		{
			return $this->_tag_open_B($param, 'strong');
		}

		protected function _tag_close_B($param)
		{
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_STRONG($param)
		{
			return $this->_tag_close_B($param);
		}

		protected function _tag_open_I($param, $other = 'i')
		{
			$this->parsingCss->save();
			$this->parsingCss->value['font-italic'] = true;
			$this->parsingCss->analyse($other, $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_ADDRESS($param)
		{
			return $this->_tag_open_I($param, 'address');
		}

		protected function _tag_open_CITE($param)
		{
			return $this->_tag_open_I($param, 'cite');
		}

		protected function _tag_open_EM($param)
		{
			return $this->_tag_open_I($param, 'em');
		}

		protected function _tag_open_SAMP($param)
		{
			return $this->_tag_open_I($param, 'samp');
		}

		protected function _tag_close_I($param)
		{
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_ADDRESS($param)
		{
			return $this->_tag_close_I($param);
		}

		protected function _tag_close_CITE($param)
		{
			return $this->_tag_close_I($param);
		}

		protected function _tag_close_EM($param)
		{
			return $this->_tag_close_I($param);
		}

		protected function _tag_close_SAMP($param)
		{
			return $this->_tag_close_I($param);
		}

		protected function _tag_open_S($param, $other = 's')
		{
			$this->parsingCss->save();
			$this->parsingCss->value['font-linethrough'] = true;
			$this->parsingCss->analyse($other, $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_DEL($param)
		{
			return $this->_tag_open_S($param, 'del');
		}

		protected function _tag_close_S($param)
		{
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_DEL($param)
		{
			return $this->_tag_close_S($param);
		}

		protected function _tag_open_U($param, $other = 'u')
		{
			$this->parsingCss->save();
			$this->parsingCss->value['font-underline'] = true;
			$this->parsingCss->analyse($other, $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_INS($param)
		{
			return $this->_tag_open_U($param, 'ins');
		}

		protected function _tag_close_U($param)
		{
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_INS($param)
		{
			return $this->_tag_close_U($param);
		}

		protected function _tag_open_A($param)
		{
			$this->_isInLink = str_replace('&amp;', '&', isset($param['href']) ? $param['href'] : '');

			if (isset($param['name'])) {
				$name = $param['name'];

				if (!isset($this->_lstAnchor[$name])) {
					$this->_lstAnchor[$name] = array($this->pdf->AddLink(), false);
				}

				if (!$this->_lstAnchor[$name][1]) {
					$this->_lstAnchor[$name][1] = true;
					$this->pdf->SetLink($this->_lstAnchor[$name][0], -1, -1);
				}
			}

			if (preg_match('/^#([^#]+)$/isU', $this->_isInLink, $match)) {
				$name = $match[1];

				if (!isset($this->_lstAnchor[$name])) {
					$this->_lstAnchor[$name] = array($this->pdf->AddLink(), false);
				}

				$this->_isInLink = $this->_lstAnchor[$name][0];
			}

			$this->parsingCss->save();
			$this->parsingCss->value['font-underline'] = true;
			$this->parsingCss->value['color'] = array(20, 20, 250);
			$this->parsingCss->analyse('a', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_A($param)
		{
			$this->_isInLink = '';
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_H1($param, $other = 'h1')
		{
			if ($this->_isForOneLine) {
				return false;
			}

			if ($this->_maxH) {
				$this->_tag_open_BR(array());
			}

			$this->parsingCss->save();
			$this->parsingCss->value['font-bold'] = true;
			$size = array('h1' => '28px', 'h2' => '24px', 'h3' => '20px', 'h4' => '16px', 'h5' => '12px', 'h6' => '9px');
			$this->parsingCss->value['margin']['l'] = 0;
			$this->parsingCss->value['margin']['r'] = 0;
			$this->parsingCss->value['margin']['t'] = $this->parsingCss->ConvertToMM('16px');
			$this->parsingCss->value['margin']['b'] = $this->parsingCss->ConvertToMM('16px');
			$this->parsingCss->value['font-size'] = $this->parsingCss->ConvertToMM($size[$other]);
			$this->parsingCss->analyse($other, $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			$this->_setNewPositionForNewLine();
			return true;
		}

		protected function _tag_open_H2($param)
		{
			return $this->_tag_open_H1($param, 'h2');
		}

		protected function _tag_open_H3($param)
		{
			return $this->_tag_open_H1($param, 'h3');
		}

		protected function _tag_open_H4($param)
		{
			return $this->_tag_open_H1($param, 'h4');
		}

		protected function _tag_open_H5($param)
		{
			return $this->_tag_open_H1($param, 'h5');
		}

		protected function _tag_open_H6($param)
		{
			return $this->_tag_open_H1($param, 'h6');
		}

		protected function _tag_close_H1($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_maxH += $this->parsingCss->value['margin']['b'];
			$h = max($this->_maxH, $this->parsingCss->getLineHeight());
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			$this->_makeBreakLine($h);
			$this->_maxH = 0;
			$this->_maxY = max($this->_maxY, $this->pdf->getY());
			return true;
		}

		protected function _tag_close_H2($param)
		{
			return $this->_tag_close_H1($param);
		}

		protected function _tag_close_H3($param)
		{
			return $this->_tag_close_H1($param);
		}

		protected function _tag_close_H4($param)
		{
			return $this->_tag_close_H1($param);
		}

		protected function _tag_close_H5($param)
		{
			return $this->_tag_close_H1($param);
		}

		protected function _tag_close_H6($param)
		{
			return $this->_tag_close_H1($param);
		}

		protected function _tag_open_SPAN($param, $other = 'span')
		{
			$this->parsingCss->save();
			$this->parsingCss->analyse($other, $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_FONT($param)
		{
			return $this->_tag_open_SPAN($param, 'font');
		}

		protected function _tag_open_LABEL($param)
		{
			return $this->_tag_open_SPAN($param, 'label');
		}

		protected function _tag_close_SPAN($param)
		{
			$this->parsingCss->restorePosition();
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_FONT($param)
		{
			return $this->_tag_close_SPAN($param);
		}

		protected function _tag_close_LABEL($param)
		{
			return $this->_tag_close_SPAN($param);
		}

		protected function _tag_open_P($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			if (!in_array($this->_previousCall, array('_tag_close_P', '_tag_close_UL'))) {
				if ($this->_maxH) {
					$this->_tag_open_BR(array());
				}
			}

			$this->parsingCss->save();
			$this->parsingCss->analyse('p', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			$this->pdf->setXY($this->pdf->getX() - $this->parsingCss->value['margin']['l'], $this->pdf->getY() - $this->parsingCss->value['margin']['t']);
			list($mL, $mR) = $this->_getMargins($this->pdf->getY());
			$mR = $this->pdf->getW() - $mR;
			$mL += $this->parsingCss->value['margin']['l'] + $this->parsingCss->value['padding']['l'];
			$mR += $this->parsingCss->value['margin']['r'] + $this->parsingCss->value['padding']['r'];
			$this->_saveMargin($mL, 0, $mR);

			if (0 < $this->parsingCss->value['text-indent']) {
				$y = $this->pdf->getY() + $this->parsingCss->value['margin']['t'] + $this->parsingCss->value['padding']['t'];
				$this->_pageMarges[floor($y * 100)] = array($mL + $this->parsingCss->value['text-indent'], $this->pdf->getW() - $mR);
				$y += $this->parsingCss->getLineHeight() * 0.10000000000000001;
				$this->_pageMarges[floor($y * 100)] = array($mL, $this->pdf->getW() - $mR);
			}

			$this->_makeBreakLine($this->parsingCss->value['margin']['t'] + $this->parsingCss->value['padding']['t']);
			$this->_isInParagraph = array($mL, $mR);
			return true;
		}

		protected function _tag_close_P($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			if ($this->_maxH) {
				$this->_tag_open_BR(array());
			}

			$this->_isInParagraph = false;
			$this->_loadMargin();
			$h = $this->parsingCss->value['margin']['b'] + $this->parsingCss->value['padding']['b'];
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			$this->_makeBreakLine($h);
			return true;
		}

		protected function _tag_open_PRE($param, $other = 'pre')
		{
			if (($other == 'pre') && $this->_maxH) {
				$this->_tag_open_BR(array());
			}

			$this->parsingCss->save();
			$this->parsingCss->value['font-family'] = 'courier';
			$this->parsingCss->analyse($other, $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();

			if ($other == 'pre') {
				return $this->_tag_open_DIV($param, $other);
			}

			return true;
		}

		protected function _tag_open_CODE($param)
		{
			return $this->_tag_open_PRE($param, 'code');
		}

		protected function _tag_close_PRE($param, $other = 'pre')
		{
			if ($other == 'pre') {
				if ($this->_isForOneLine) {
					return false;
				}

				$this->_tag_close_DIV($param, $other);
				$this->_tag_open_BR(array());
			}

			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_CODE($param)
		{
			return $this->_tag_close_PRE($param, 'code');
		}

		protected function _tag_open_BIG($param)
		{
			$this->parsingCss->save();
			$this->parsingCss->value['mini-decal'] -= $this->parsingCss->value['mini-size'] * 0.12;
			$this->parsingCss->value['mini-size'] *= 1.2;
			$this->parsingCss->analyse('big', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_BIG($param)
		{
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_SMALL($param)
		{
			$this->parsingCss->save();
			$this->parsingCss->value['mini-decal'] += $this->parsingCss->value['mini-size'] * 0.050000000000000003;
			$this->parsingCss->value['mini-size'] *= 0.81999999999999995;
			$this->parsingCss->analyse('small', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_SMALL($param)
		{
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_SUP($param)
		{
			$this->parsingCss->save();
			$this->parsingCss->value['mini-decal'] -= $this->parsingCss->value['mini-size'] * 0.14999999999999999;
			$this->parsingCss->value['mini-size'] *= 0.75;
			$this->parsingCss->analyse('sup', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_SUP($param)
		{
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_SUB($param)
		{
			$this->parsingCss->save();
			$this->parsingCss->value['mini-decal'] += $this->parsingCss->value['mini-size'] * 0.14999999999999999;
			$this->parsingCss->value['mini-size'] *= 0.75;
			$this->parsingCss->analyse('sub', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_SUB($param)
		{
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_UL($param, $other = 'ul')
		{
			if ($this->_isForOneLine) {
				return false;
			}

			if (!in_array($this->_previousCall, array('_tag_close_P', '_tag_close_UL'))) {
				if ($this->_maxH) {
					$this->_tag_open_BR(array());
				}

				if (!count($this->_defList)) {
					$this->_tag_open_BR(array());
				}
			}

			if (!isset($param['style']['width'])) {
				$param['allwidth'] = true;
			}

			$param['cellspacing'] = 0;
			$this->_tag_open_TABLE($param, $other);
			$this->_listeAddLevel($other, $this->parsingCss->value['list-style-type'], $this->parsingCss->value['list-style-image']);
			return true;
		}

		protected function _tag_open_OL($param)
		{
			return $this->_tag_open_UL($param, 'ol');
		}

		protected function _tag_close_UL($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_tag_close_TABLE($param);
			$this->_listeDelLevel();

			if (!$this->_subPart) {
				if (!count($this->_defList)) {
					$this->_tag_open_BR(array());
				}
			}

			return true;
		}

		protected function _tag_close_OL($param)
		{
			return $this->_tag_close_UL($param);
		}

		protected function _tag_open_LI($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_listeAddLi();

			if (!isset($param['style']['width'])) {
				$param['style']['width'] = '100%';
			}

			$paramPUCE = $param;
			$inf = $this->_listeGetLi();

			if ($inf[0]) {
				$paramPUCE['style']['font-family'] = $inf[0];
				$paramPUCE['style']['text-align'] = 'li_right';
				$paramPUCE['style']['vertical-align'] = 'top';
				$paramPUCE['style']['width'] = $this->_listeGetWidth();
				$paramPUCE['style']['padding-right'] = $this->_listeGetPadding();
				$paramPUCE['txt'] = $inf[2];
			}
			else {
				$paramPUCE['style']['text-align'] = 'li_right';
				$paramPUCE['style']['vertical-align'] = 'top';
				$paramPUCE['style']['width'] = $this->_listeGetWidth();
				$paramPUCE['style']['padding-right'] = $this->_listeGetPadding();
				$paramPUCE['src'] = $inf[2];
				$paramPUCE['sub_li'] = true;
			}

			$this->_tag_open_TR($param, 'li');
			$this->parsingCss->save();

			if ($inf[1]) {
				$this->parsingCss->value['mini-decal'] += $this->parsingCss->value['mini-size'] * 0.044999999999999998;
				$this->parsingCss->value['mini-size'] *= 0.75;
			}

			if ($this->_subPart) {
				$tmpPos = $this->_tempPos;
				$tmpLst1 = $this->parsingHtml->code[$tmpPos + 1];
				$tmpLst2 = $this->parsingHtml->code[$tmpPos + 2];
				$this->parsingHtml->code[$tmpPos + 1] = array();
				$this->parsingHtml->code[$tmpPos + 1]['name'] = isset($paramPUCE['src']) ? 'img' : 'write';
				$this->parsingHtml->code[$tmpPos + 1]['param'] = $paramPUCE;
				unset($this->parsingHtml->code[$tmpPos + 1]['param']['style']['width']);
				$this->parsingHtml->code[$tmpPos + 1]['close'] = 0;
				$this->parsingHtml->code[$tmpPos + 2] = array();
				$this->parsingHtml->code[$tmpPos + 2]['name'] = 'li';
				$this->parsingHtml->code[$tmpPos + 2]['param'] = $paramPUCE;
				$this->parsingHtml->code[$tmpPos + 2]['close'] = 1;
				$this->_tag_open_TD($paramPUCE, 'li_sub');
				$this->_tag_close_TD($param);
				$this->_tempPos = $tmpPos;
				$this->parsingHtml->code[$tmpPos + 1] = $tmpLst1;
				$this->parsingHtml->code[$tmpPos + 2] = $tmpLst2;
			}
			else {
				$this->_tag_open_TD($paramPUCE, 'li_sub');
				unset($paramPUCE['style']['width']);

				if (isset($paramPUCE['src'])) {
					$this->_tag_open_IMG($paramPUCE);
				}
				else {
					$this->_tag_open_WRITE($paramPUCE);
				}

				$this->_tag_close_TD($paramPUCE);
			}

			$this->parsingCss->load();
			$this->_tag_open_TD($param, 'li');
			return true;
		}

		protected function _tag_close_LI($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_tag_close_TD($param);
			$this->_tag_close_TR($param);
			return true;
		}

		protected function _tag_open_TBODY($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->parsingCss->save();
			$this->parsingCss->analyse('tbody', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_TBODY($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_THEAD($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->parsingCss->save();
			$this->parsingCss->analyse('thead', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();

			if ($this->_subPart) {
				HTML2PDF::$_tables[$param['num']]['thead']['tr'][0] = HTML2PDF::$_tables[$param['num']]['tr_curr'];
				HTML2PDF::$_tables[$param['num']]['thead']['code'] = array();

				for ($pos = $this->_tempPos; $pos < count($this->parsingHtml->code); $pos++) {
					$action = $this->parsingHtml->code[$pos];

					if (strtolower($action['name']) == 'thead') {
						$action['name'] = 'thead_sub';
					}

					HTML2PDF::$_tables[$param['num']]['thead']['code'][] = $action;
					if ((strtolower($action['name']) == 'thead_sub') && $action['close']) {
						break;
					}
				}
			}
			else {
				$level = $this->parsingHtml->getLevel($this->_parsePos);
				$this->_parsePos += count($level);
				HTML2PDF::$_tables[$param['num']]['tr_curr'] += count(HTML2PDF::$_tables[$param['num']]['thead']['tr']);
			}

			return true;
		}

		protected function _tag_close_THEAD($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->parsingCss->load();
			$this->parsingCss->fontSet();

			if ($this->_subPart) {
				$min = HTML2PDF::$_tables[$param['num']]['thead']['tr'][0];
				$max = HTML2PDF::$_tables[$param['num']]['tr_curr'] - 1;
				HTML2PDF::$_tables[$param['num']]['thead']['tr'] = range($min, $max);
			}

			return true;
		}

		protected function _tag_open_TFOOT($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->parsingCss->save();
			$this->parsingCss->analyse('tfoot', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();

			if ($this->_subPart) {
				HTML2PDF::$_tables[$param['num']]['tfoot']['tr'][0] = HTML2PDF::$_tables[$param['num']]['tr_curr'];
				HTML2PDF::$_tables[$param['num']]['tfoot']['code'] = array();

				for ($pos = $this->_tempPos; $pos < count($this->parsingHtml->code); $pos++) {
					$action = $this->parsingHtml->code[$pos];

					if (strtolower($action['name']) == 'tfoot') {
						$action['name'] = 'tfoot_sub';
					}

					HTML2PDF::$_tables[$param['num']]['tfoot']['code'][] = $action;
					if ((strtolower($action['name']) == 'tfoot_sub') && $action['close']) {
						break;
					}
				}
			}
			else {
				$level = $this->parsingHtml->getLevel($this->_parsePos);
				$this->_parsePos += count($level);
				HTML2PDF::$_tables[$param['num']]['tr_curr'] += count(HTML2PDF::$_tables[$param['num']]['tfoot']['tr']);
			}

			return true;
		}

		protected function _tag_close_TFOOT($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->parsingCss->load();
			$this->parsingCss->fontSet();

			if ($this->_subPart) {
				$min = HTML2PDF::$_tables[$param['num']]['tfoot']['tr'][0];
				$max = HTML2PDF::$_tables[$param['num']]['tr_curr'] - 1;
				HTML2PDF::$_tables[$param['num']]['tfoot']['tr'] = range($min, $max);
			}

			return true;
		}

		protected function _tag_open_THEAD_SUB($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->parsingCss->save();
			$this->parsingCss->analyse('thead', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_THEAD_SUB($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_TFOOT_SUB($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->parsingCss->save();
			$this->parsingCss->analyse('tfoot', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_close_TFOOT_SUB($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_FORM($param)
		{
			$this->parsingCss->save();
			$this->parsingCss->analyse('form', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			$this->pdf->setFormDefaultProp(array(
	'lineWidth'   => 1,
	'borderStyle' => 'solid',
	'fillColor'   => array(220, 220, 255),
	'strokeColor' => array(128, 128, 200)
	));
			$this->_isInForm = isset($param['action']) ? $param['action'] : '';
			return true;
		}

		protected function _tag_close_FORM($param)
		{
			$this->_isInForm = false;
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_TABLE($param, $other = 'table')
		{
			if ($this->_maxH) {
				if ($this->_isForOneLine) {
					return false;
				}

				$this->_tag_open_BR(array());
			}

			if ($this->_isForOneLine) {
				$this->_maxX = $this->pdf->getW() - $this->pdf->getlMargin() - $this->pdf->getrMargin();
				return false;
			}

			$this->_maxH = 0;
			$alignObject = (isset($param['align']) ? strtolower($param['align']) : 'left');

			if (isset($param['align'])) {
				unset($param['align']);
			}

			if (!in_array($alignObject, array('left', 'center', 'right'))) {
				$alignObject = 'left';
			}

			$this->parsingCss->save();
			$this->parsingCss->analyse($other, $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();

			if ($this->parsingCss->value['margin-auto']) {
				$alignObject = 'center';
			}

			$collapse = false;

			if ($other == 'table') {
				$collapse = (isset($this->parsingCss->value['border']['collapse']) ? $this->parsingCss->value['border']['collapse'] : false);
			}

			if ($collapse) {
				$param['style']['border'] = 'none';
				$param['cellspacing'] = 0;
				$none = $this->parsingCss->readBorder('none');
				$this->parsingCss->value['border']['t'] = $none;
				$this->parsingCss->value['border']['r'] = $none;
				$this->parsingCss->value['border']['b'] = $none;
				$this->parsingCss->value['border']['l'] = $none;
			}

			if ($this->_subPart) {
				if ($this->_debugActif) {
					$this->_DEBUG_add('Table n' . $param['num'], true);
				}

				HTML2PDF::$_tables[$param['num']] = array();
				HTML2PDF::$_tables[$param['num']]['border'] = isset($param['border']) ? $this->parsingCss->readBorder($param['border']) : NULL;
				HTML2PDF::$_tables[$param['num']]['cellpadding'] = $this->parsingCss->ConvertToMM(isset($param['cellpadding']) ? $param['cellpadding'] : '1px');
				HTML2PDF::$_tables[$param['num']]['cellspacing'] = $this->parsingCss->ConvertToMM(isset($param['cellspacing']) ? $param['cellspacing'] : '2px');
				HTML2PDF::$_tables[$param['num']]['cases'] = array();
				HTML2PDF::$_tables[$param['num']]['corr'] = array();
				HTML2PDF::$_tables[$param['num']]['corr_x'] = 0;
				HTML2PDF::$_tables[$param['num']]['corr_y'] = 0;
				HTML2PDF::$_tables[$param['num']]['td_curr'] = 0;
				HTML2PDF::$_tables[$param['num']]['tr_curr'] = 0;
				HTML2PDF::$_tables[$param['num']]['curr_x'] = $this->pdf->getX();
				HTML2PDF::$_tables[$param['num']]['curr_y'] = $this->pdf->getY();
				HTML2PDF::$_tables[$param['num']]['width'] = 0;
				HTML2PDF::$_tables[$param['num']]['height'] = 0;
				HTML2PDF::$_tables[$param['num']]['align'] = $alignObject;
				HTML2PDF::$_tables[$param['num']]['marge'] = array();
				HTML2PDF::$_tables[$param['num']]['marge']['t'] = $this->parsingCss->value['padding']['t'] + $this->parsingCss->value['border']['t']['width'] + (HTML2PDF::$_tables[$param['num']]['cellspacing'] * 0.5);
				HTML2PDF::$_tables[$param['num']]['marge']['r'] = $this->parsingCss->value['padding']['r'] + $this->parsingCss->value['border']['r']['width'] + (HTML2PDF::$_tables[$param['num']]['cellspacing'] * 0.5);
				HTML2PDF::$_tables[$param['num']]['marge']['b'] = $this->parsingCss->value['padding']['b'] + $this->parsingCss->value['border']['b']['width'] + (HTML2PDF::$_tables[$param['num']]['cellspacing'] * 0.5);
				HTML2PDF::$_tables[$param['num']]['marge']['l'] = $this->parsingCss->value['padding']['l'] + $this->parsingCss->value['border']['l']['width'] + (HTML2PDF::$_tables[$param['num']]['cellspacing'] * 0.5);
				HTML2PDF::$_tables[$param['num']]['page'] = 0;
				HTML2PDF::$_tables[$param['num']]['new_page'] = true;
				HTML2PDF::$_tables[$param['num']]['style_value'] = NULL;
				HTML2PDF::$_tables[$param['num']]['thead'] = array();
				HTML2PDF::$_tables[$param['num']]['tfoot'] = array();
				HTML2PDF::$_tables[$param['num']]['thead']['tr'] = array();
				HTML2PDF::$_tables[$param['num']]['tfoot']['tr'] = array();
				HTML2PDF::$_tables[$param['num']]['thead']['height'] = 0;
				HTML2PDF::$_tables[$param['num']]['tfoot']['height'] = 0;
				HTML2PDF::$_tables[$param['num']]['thead']['code'] = array();
				HTML2PDF::$_tables[$param['num']]['tfoot']['code'] = array();
				HTML2PDF::$_tables[$param['num']]['cols'] = array();
				$this->_saveMargin($this->pdf->getlMargin(), $this->pdf->gettMargin(), $this->pdf->getrMargin());
				$this->parsingCss->value['width'] -= HTML2PDF::$_tables[$param['num']]['marge']['l'] + HTML2PDF::$_tables[$param['num']]['marge']['r'];
			}
			else {
				HTML2PDF::$_tables[$param['num']]['page'] = 0;
				HTML2PDF::$_tables[$param['num']]['td_curr'] = 0;
				HTML2PDF::$_tables[$param['num']]['tr_curr'] = 0;
				HTML2PDF::$_tables[$param['num']]['td_x'] = HTML2PDF::$_tables[$param['num']]['marge']['l'] + HTML2PDF::$_tables[$param['num']]['curr_x'];
				HTML2PDF::$_tables[$param['num']]['td_y'] = HTML2PDF::$_tables[$param['num']]['marge']['t'] + HTML2PDF::$_tables[$param['num']]['curr_y'];
				$this->_drawRectangle(HTML2PDF::$_tables[$param['num']]['curr_x'], HTML2PDF::$_tables[$param['num']]['curr_y'], HTML2PDF::$_tables[$param['num']]['width'], isset(HTML2PDF::$_tables[$param['num']]['height'][0]) ? HTML2PDF::$_tables[$param['num']]['height'][0] : NULL, $this->parsingCss->value['border'], $this->parsingCss->value['padding'], 0, $this->parsingCss->value['background']);
				HTML2PDF::$_tables[$param['num']]['style_value'] = $this->parsingCss->value;
			}

			return true;
		}

		protected function _tag_close_TABLE($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_maxH = 0;

			if ($this->_subPart) {
				$this->_calculateTableCellSize(HTML2PDF::$_tables[$param['num']]['cases'], HTML2PDF::$_tables[$param['num']]['corr']);
				$lst = array('thead', 'tfoot');

				foreach ($lst as $mode) {
					HTML2PDF::$_tables[$param['num']][$mode]['height'] = 0;

					foreach (HTML2PDF::$_tables[$param['num']][$mode]['tr'] as $tr) {
						$h = 0;

						for ($i = 0; $i < count(HTML2PDF::$_tables[$param['num']]['cases'][$tr]); $i++) {
							if (HTML2PDF::$_tables[$param['num']]['cases'][$tr][$i]['rowspan'] == 1) {
								$h = max($h, HTML2PDF::$_tables[$param['num']]['cases'][$tr][$i]['h']);
							}
						}

						HTML2PDF::$_tables[$param['num']][$mode]['height'] += $h;
					}
				}

				HTML2PDF::$_tables[$param['num']]['width'] = HTML2PDF::$_tables[$param['num']]['marge']['l'] + HTML2PDF::$_tables[$param['num']]['marge']['r'];

				if (isset(HTML2PDF::$_tables[$param['num']]['cases'][0])) {
					foreach (HTML2PDF::$_tables[$param['num']]['cases'][0] as $case) {
						HTML2PDF::$_tables[$param['num']]['width'] += $case['w'];
					}
				}

				$old = $this->parsingCss->getOldValues();
				$parentWidth = ($old['width'] ? $old['width'] : $this->pdf->getW() - $this->pdf->getlMargin() - $this->pdf->getrMargin());
				$x = HTML2PDF::$_tables[$param['num']]['curr_x'];
				$w = HTML2PDF::$_tables[$param['num']]['width'];

				if ($w < $parentWidth) {
					if (HTML2PDF::$_tables[$param['num']]['align'] == 'center') {
						$x = $x + (($parentWidth - $w) * 0.5);
					}
					else if (HTML2PDF::$_tables[$param['num']]['align'] == 'right') {
						$x = ($x + $parentWidth) - $w;
					}

					HTML2PDF::$_tables[$param['num']]['curr_x'] = $x;
				}

				HTML2PDF::$_tables[$param['num']]['height'] = array();
				$h0 = HTML2PDF::$_tables[$param['num']]['marge']['t'] + HTML2PDF::$_tables[$param['num']]['marge']['b'];
				$h0 += HTML2PDF::$_tables[$param['num']]['thead']['height'] + HTML2PDF::$_tables[$param['num']]['tfoot']['height'];
				$max = $this->pdf->getH() - $this->pdf->getbMargin();
				$y = HTML2PDF::$_tables[$param['num']]['curr_y'];
				$height = $h0;

				for ($k = 0; $k < count(HTML2PDF::$_tables[$param['num']]['cases']); $k++) {
					if (in_array($k, HTML2PDF::$_tables[$param['num']]['thead']['tr'])) {
						continue;
					}

					if (in_array($k, HTML2PDF::$_tables[$param['num']]['tfoot']['tr'])) {
						continue;
					}

					$th = 0;
					$h = 0;

					for ($i = 0; $i < count(HTML2PDF::$_tables[$param['num']]['cases'][$k]); $i++) {
						$h = max($h, HTML2PDF::$_tables[$param['num']]['cases'][$k][$i]['h']);

						if (HTML2PDF::$_tables[$param['num']]['cases'][$k][$i]['rowspan'] == 1) {
							$th = max($th, HTML2PDF::$_tables[$param['num']]['cases'][$k][$i]['h']);
						}
					}

					if ($max < ($y + $h + $height)) {
						if ($height == $h0) {
							$height = NULL;
						}

						HTML2PDF::$_tables[$param['num']]['height'][] = $height;
						$height = $h0;
						$y = $this->_margeTop;
					}

					$height += $th;
				}

				if (($height != $h0) || ($k == 0)) {
					HTML2PDF::$_tables[$param['num']]['height'][] = $height;
				}
			}
			else {
				if (count(HTML2PDF::$_tables[$param['num']]['tfoot']['code'])) {
					$tmpTR = HTML2PDF::$_tables[$param['num']]['tr_curr'];
					$tmpTD = HTML2PDF::$_tables[$param['num']]['td_curr'];
					$oldParsePos = $this->_parsePos;
					$oldParseCode = $this->parsingHtml->code;
					HTML2PDF::$_tables[$param['num']]['tr_curr'] = HTML2PDF::$_tables[$param['num']]['tfoot']['tr'][0];
					HTML2PDF::$_tables[$param['num']]['td_curr'] = 0;
					$this->_parsePos = 0;
					$this->parsingHtml->code = HTML2PDF::$_tables[$param['num']]['tfoot']['code'];
					$this->_isInTfoot = true;
					$this->_makeHTMLcode();
					$this->_isInTfoot = false;
					$this->_parsePos = $oldParsePos;
					$this->parsingHtml->code = $oldParseCode;
					HTML2PDF::$_tables[$param['num']]['tr_curr'] = $tmpTR;
					HTML2PDF::$_tables[$param['num']]['td_curr'] = $tmpTD;
				}

				$x = HTML2PDF::$_tables[$param['num']]['curr_x'] + HTML2PDF::$_tables[$param['num']]['width'];

				if (1 < count(HTML2PDF::$_tables[$param['num']]['height'])) {
					$y = $this->_margeTop + HTML2PDF::$_tables[$param['num']]['height'][count(HTML2PDF::$_tables[$param['num']]['height']) - 1];
				}
				else if (count(HTML2PDF::$_tables[$param['num']]['height']) == 1) {
					$y = HTML2PDF::$_tables[$param['num']]['curr_y'] + HTML2PDF::$_tables[$param['num']]['height'][count(HTML2PDF::$_tables[$param['num']]['height']) - 1];
				}
				else {
					$y = HTML2PDF::$_tables[$param['num']]['curr_y'];
				}

				$this->_maxX = max($this->_maxX, $x);
				$this->_maxY = max($this->_maxY, $y);
				$this->pdf->setXY($this->pdf->getlMargin(), $y);
				$this->_loadMargin();

				if ($this->_debugActif) {
					$this->_DEBUG_add('Table ' . $param['num'], false);
				}
			}

			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_COL($param)
		{
			$span = (isset($param['span']) ? $param['span'] : 1);

			for ($k = 0; $k < $span; $k++) {
				HTML2PDF::$_tables[$param['num']]['cols'][] = $param;
			}
		}

		protected function _tag_close_COL($param)
		{
			return true;
		}

		protected function _tag_open_TR($param, $other = 'tr')
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_maxH = 0;
			$this->parsingCss->save();
			$this->parsingCss->analyse($other, $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			HTML2PDF::$_tables[$param['num']]['tr_curr']++;
			HTML2PDF::$_tables[$param['num']]['td_curr'] = 0;

			if (!$this->_subPart) {
				$ty = NULL;

				for ($ii = 0; $ii < count(HTML2PDF::$_tables[$param['num']]['cases'][HTML2PDF::$_tables[$param['num']]['tr_curr'] - 1]); $ii++) {
					$ty = max($ty, HTML2PDF::$_tables[$param['num']]['cases'][HTML2PDF::$_tables[$param['num']]['tr_curr'] - 1][$ii]['h']);
				}

				$hfoot = HTML2PDF::$_tables[$param['num']]['tfoot']['height'];
				if (!$this->_isInTfoot && (($this->pdf->getH() - $this->pdf->getbMargin()) < (HTML2PDF::$_tables[$param['num']]['td_y'] + HTML2PDF::$_tables[$param['num']]['marge']['b'] + $ty + $hfoot))) {
					if (count(HTML2PDF::$_tables[$param['num']]['tfoot']['code'])) {
						$tmpTR = HTML2PDF::$_tables[$param['num']]['tr_curr'];
						$tmpTD = HTML2PDF::$_tables[$param['num']]['td_curr'];
						$oldParsePos = $this->_parsePos;
						$oldParseCode = $this->parsingHtml->code;
						HTML2PDF::$_tables[$param['num']]['tr_curr'] = HTML2PDF::$_tables[$param['num']]['tfoot']['tr'][0];
						HTML2PDF::$_tables[$param['num']]['td_curr'] = 0;
						$this->_parsePos = 0;
						$this->parsingHtml->code = HTML2PDF::$_tables[$param['num']]['tfoot']['code'];
						$this->_isInTfoot = true;
						$this->_makeHTMLcode();
						$this->_isInTfoot = false;
						$this->_parsePos = $oldParsePos;
						$this->parsingHtml->code = $oldParseCode;
						HTML2PDF::$_tables[$param['num']]['tr_curr'] = $tmpTR;
						HTML2PDF::$_tables[$param['num']]['td_curr'] = $tmpTD;
					}

					HTML2PDF::$_tables[$param['num']]['new_page'] = true;
					$this->_setNewPage();
					HTML2PDF::$_tables[$param['num']]['page']++;
					HTML2PDF::$_tables[$param['num']]['curr_y'] = $this->pdf->getY();
					HTML2PDF::$_tables[$param['num']]['td_y'] = HTML2PDF::$_tables[$param['num']]['curr_y'] + HTML2PDF::$_tables[$param['num']]['marge']['t'];

					if (isset(HTML2PDF::$_tables[$param['num']]['height'][HTML2PDF::$_tables[$param['num']]['page']])) {
						$old = $this->parsingCss->value;
						$this->parsingCss->value = HTML2PDF::$_tables[$param['num']]['style_value'];
						$this->_drawRectangle(HTML2PDF::$_tables[$param['num']]['curr_x'], HTML2PDF::$_tables[$param['num']]['curr_y'], HTML2PDF::$_tables[$param['num']]['width'], HTML2PDF::$_tables[$param['num']]['height'][HTML2PDF::$_tables[$param['num']]['page']], $this->parsingCss->value['border'], $this->parsingCss->value['padding'], HTML2PDF::$_tables[$param['num']]['cellspacing'] * 0.5, $this->parsingCss->value['background']);
						$this->parsingCss->value = $old;
					}
				}

				if (HTML2PDF::$_tables[$param['num']]['new_page'] && count(HTML2PDF::$_tables[$param['num']]['thead']['code'])) {
					HTML2PDF::$_tables[$param['num']]['new_page'] = false;
					$tmpTR = HTML2PDF::$_tables[$param['num']]['tr_curr'];
					$tmpTD = HTML2PDF::$_tables[$param['num']]['td_curr'];
					$oldParsePos = $this->_parsePos;
					$oldParseCode = $this->parsingHtml->code;
					HTML2PDF::$_tables[$param['num']]['tr_curr'] = HTML2PDF::$_tables[$param['num']]['thead']['tr'][0];
					HTML2PDF::$_tables[$param['num']]['td_curr'] = 0;
					$this->_parsePos = 0;
					$this->parsingHtml->code = HTML2PDF::$_tables[$param['num']]['thead']['code'];
					$this->_isInThead = true;
					$this->_makeHTMLcode();
					$this->_isInThead = false;
					$this->_parsePos = $oldParsePos;
					$this->parsingHtml->code = $oldParseCode;
					HTML2PDF::$_tables[$param['num']]['tr_curr'] = $tmpTR;
					HTML2PDF::$_tables[$param['num']]['td_curr'] = $tmpTD;
					HTML2PDF::$_tables[$param['num']]['new_page'] = true;
				}
			}
			else {
				HTML2PDF::$_tables[$param['num']]['cases'][HTML2PDF::$_tables[$param['num']]['tr_curr'] - 1] = array();

				if (!isset(HTML2PDF::$_tables[$param['num']]['corr'][HTML2PDF::$_tables[$param['num']]['corr_y']])) {
					HTML2PDF::$_tables[$param['num']]['corr'][HTML2PDF::$_tables[$param['num']]['corr_y']] = array();
				}

				HTML2PDF::$_tables[$param['num']]['corr_x'] = 0;

				while (isset(HTML2PDF::$_tables[$param['num']]['corr'][HTML2PDF::$_tables[$param['num']]['corr_y']][HTML2PDF::$_tables[$param['num']]['corr_x']])) {
					HTML2PDF::$_tables[$param['num']]['corr_x']++;
				}
			}

			return true;
		}

		protected function _tag_close_TR($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_maxH = 0;
			$this->parsingCss->load();
			$this->parsingCss->fontSet();

			if (!$this->_subPart) {
				$ty = NULL;

				for ($ii = 0; $ii < count(HTML2PDF::$_tables[$param['num']]['cases'][HTML2PDF::$_tables[$param['num']]['tr_curr'] - 1]); $ii++) {
					if (HTML2PDF::$_tables[$param['num']]['cases'][HTML2PDF::$_tables[$param['num']]['tr_curr'] - 1][$ii]['rowspan'] == 1) {
						$ty = HTML2PDF::$_tables[$param['num']]['cases'][HTML2PDF::$_tables[$param['num']]['tr_curr'] - 1][$ii]['h'];
					}
				}

				HTML2PDF::$_tables[$param['num']]['td_x'] = HTML2PDF::$_tables[$param['num']]['curr_x'] + HTML2PDF::$_tables[$param['num']]['marge']['l'];
				HTML2PDF::$_tables[$param['num']]['td_y'] += $ty;
				HTML2PDF::$_tables[$param['num']]['new_page'] = false;
			}
			else {
				HTML2PDF::$_tables[$param['num']]['corr_y']++;
			}

			return true;
		}

		protected function _tag_open_TD($param, $other = 'td')
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_maxH = 0;
			$param['cellpadding'] = HTML2PDF::$_tables[$param['num']]['cellpadding'] . 'mm';
			$param['cellspacing'] = HTML2PDF::$_tables[$param['num']]['cellspacing'] . 'mm';

			if ($other == 'li') {
				$specialLi = true;
			}
			else {
				$specialLi = false;

				if ($other == 'li_sub') {
					$param['style']['border'] = 'none';
					$param['style']['background-color'] = 'transparent';
					$param['style']['background-image'] = 'none';
					$param['style']['background-position'] = '';
					$param['style']['background-repeat'] = '';
					$other = 'li';
				}
			}

			$x = HTML2PDF::$_tables[$param['num']]['td_curr'];
			$y = HTML2PDF::$_tables[$param['num']]['tr_curr'] - 1;
			$colspan = (isset($param['colspan']) ? $param['colspan'] : 1);
			$rowspan = (isset($param['rowspan']) ? $param['rowspan'] : 1);
			$collapse = false;

			if (in_array($other, array('td', 'th'))) {
				$numCol = (isset(HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['Xr']) ? HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['Xr'] : HTML2PDF::$_tables[$param['num']]['corr_x']);

				if (isset(HTML2PDF::$_tables[$param['num']]['cols'][$numCol])) {
					$colParam = HTML2PDF::$_tables[$param['num']]['cols'][$numCol];
					$colParam['style']['width'] = array();

					for ($k = 0; $k < $colspan; $k++) {
						if (isset(HTML2PDF::$_tables[$param['num']]['cols'][$numCol + $k]['style']['width'])) {
							$colParam['style']['width'][] = HTML2PDF::$_tables[$param['num']]['cols'][$numCol + $k]['style']['width'];
						}
					}

					$total = '';
					$last = $this->parsingCss->getLastWidth();

					if (count($colParam['style']['width'])) {
						$total = $colParam['style']['width'][0];
						unset($colParam['style']['width'][0]);

						foreach ($colParam['style']['width'] as $width) {
							if ((substr($total, -1) == '%') && (substr($width, -1) == '%')) {
								$total = (str_replace('%', '', $total) + str_replace('%', '', $width)) . '%';
							}
							else {
								$total = ($this->parsingCss->ConvertToMM($total, $last) + $this->parsingCss->ConvertToMM($width, $last)) . 'mm';
							}
						}
					}

					if ($total) {
						$colParam['style']['width'] = $total;
					}
					else {
						unset($colParam['style']['width']);
					}

					$param['style'] = array_merge($colParam['style'], $param['style']);

					if (isset($colParam['class'])) {
						$param['class'] = $colParam['class'] . (isset($param['class']) ? ' ' . $param['class'] : '');
					}
				}

				$collapse = (isset($this->parsingCss->value['border']['collapse']) ? $this->parsingCss->value['border']['collapse'] : false);
			}

			$this->parsingCss->save();
			$legacy = NULL;

			if (in_array($other, array('td', 'th'))) {
				$legacy = array();
				$old = $this->parsingCss->getLastValue('background');
				if ($old && ($old['color'] || $old['image'])) {
					$legacy['background'] = $old;
				}

				if (HTML2PDF::$_tables[$param['num']]['border']) {
					$legacy['border'] = array();
					$legacy['border']['l'] = HTML2PDF::$_tables[$param['num']]['border'];
					$legacy['border']['t'] = HTML2PDF::$_tables[$param['num']]['border'];
					$legacy['border']['r'] = HTML2PDF::$_tables[$param['num']]['border'];
					$legacy['border']['b'] = HTML2PDF::$_tables[$param['num']]['border'];
				}
			}

			$return = $this->parsingCss->analyse($other, $param, $legacy);

			if ($specialLi) {
				$this->parsingCss->value['width'] -= $this->parsingCss->ConvertToMM($this->_listeGetWidth());
				$this->parsingCss->value['width'] -= $this->parsingCss->ConvertToMM($this->_listeGetPadding());
			}

			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();

			if ($collapse) {
				if (!$this->_subPart) {
					if (((1 < HTML2PDF::$_tables[$param['num']]['tr_curr']) && !HTML2PDF::$_tables[$param['num']]['new_page']) || (!$this->_isInThead && count(HTML2PDF::$_tables[$param['num']]['thead']['code']))) {
						$this->parsingCss->value['border']['t'] = $this->parsingCss->readBorder('none');
					}
				}

				if (0 < HTML2PDF::$_tables[$param['num']]['td_curr']) {
					if (!$return) {
						$this->parsingCss->value['width'] += $this->parsingCss->value['border']['l']['width'];
					}

					$this->parsingCss->value['border']['l'] = $this->parsingCss->readBorder('none');
				}
			}

			$marge = array();
			$marge['t'] = $this->parsingCss->value['padding']['t'] + (0.5 * HTML2PDF::$_tables[$param['num']]['cellspacing']) + $this->parsingCss->value['border']['t']['width'];
			$marge['r'] = $this->parsingCss->value['padding']['r'] + (0.5 * HTML2PDF::$_tables[$param['num']]['cellspacing']) + $this->parsingCss->value['border']['r']['width'];
			$marge['b'] = $this->parsingCss->value['padding']['b'] + (0.5 * HTML2PDF::$_tables[$param['num']]['cellspacing']) + $this->parsingCss->value['border']['b']['width'];
			$marge['l'] = $this->parsingCss->value['padding']['l'] + (0.5 * HTML2PDF::$_tables[$param['num']]['cellspacing']) + $this->parsingCss->value['border']['l']['width'];

			if ($this->_subPart) {
				HTML2PDF::$_tables[$param['num']]['td_curr']++;
				HTML2PDF::$_tables[$param['num']]['cases'][$y][$x] = array();
				HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['w'] = 0;
				HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['h'] = 0;
				HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['dw'] = 0;
				HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['colspan'] = $colspan;
				HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['rowspan'] = $rowspan;
				HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['Xr'] = HTML2PDF::$_tables[$param['num']]['corr_x'];
				HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['Yr'] = HTML2PDF::$_tables[$param['num']]['corr_y'];

				for ($j = 0; $j < $rowspan; $j++) {
					for ($i = 0; $i < $colspan; $i++) {
						HTML2PDF::$_tables[$param['num']]['corr'][HTML2PDF::$_tables[$param['num']]['corr_y'] + $j][HTML2PDF::$_tables[$param['num']]['corr_x'] + $i] = 0 < ($i + $j) ? '' : array($x, $y, $colspan, $rowspan);
					}
				}

				HTML2PDF::$_tables[$param['num']]['corr_x'] += $colspan;

				while (isset(HTML2PDF::$_tables[$param['num']]['corr'][HTML2PDF::$_tables[$param['num']]['corr_y']][HTML2PDF::$_tables[$param['num']]['corr_x']])) {
					HTML2PDF::$_tables[$param['num']]['corr_x']++;
				}

				$level = $this->parsingHtml->getLevel($this->_tempPos);
				$this->_createSubHTML($this->_subHtml);
				$this->_subHtml->parsingHtml->code = $level;
				$this->_subHtml->_makeHTMLcode();
				$this->_tempPos += count($level);
			}
			else {
				HTML2PDF::$_tables[$param['num']]['td_curr']++;
				HTML2PDF::$_tables[$param['num']]['td_x'] += HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['dw'];
				$this->_drawRectangle(HTML2PDF::$_tables[$param['num']]['td_x'], HTML2PDF::$_tables[$param['num']]['td_y'], HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['w'], HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['h'], $this->parsingCss->value['border'], $this->parsingCss->value['padding'], HTML2PDF::$_tables[$param['num']]['cellspacing'] * 0.5, $this->parsingCss->value['background']);
				$this->parsingCss->value['width'] = HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['w'] - $marge['l'] - $marge['r'];
				$mL = HTML2PDF::$_tables[$param['num']]['td_x'] + $marge['l'];
				$mR = $this->pdf->getW() - $mL - $this->parsingCss->value['width'];
				$this->_saveMargin($mL, 0, $mR);
				$hCorr = HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['h'];
				$hReel = HTML2PDF::$_tables[$param['num']]['cases'][$y][$x]['real_h'];

				switch ($this->parsingCss->value['vertical-align']) {
				case 'bottom':
					$yCorr = $hCorr - $hReel;
					break;

				case 'middle':
					$yCorr = ($hCorr - $hReel) * 0.5;
					break;

				case 'top':
				default:
					$yCorr = 0;
					break;
				}

				$x = HTML2PDF::$_tables[$param['num']]['td_x'] + $marge['l'];
				$y = HTML2PDF::$_tables[$param['num']]['td_y'] + $marge['t'] + $yCorr;
				$this->pdf->setXY($x, $y);
				$this->_setNewPositionForNewLine();
			}

			return true;
		}

		protected function _tag_close_TD($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_maxH = 0;
			$marge = array();
			$marge['t'] = $this->parsingCss->value['padding']['t'] + (0.5 * HTML2PDF::$_tables[$param['num']]['cellspacing']) + $this->parsingCss->value['border']['t']['width'];
			$marge['r'] = $this->parsingCss->value['padding']['r'] + (0.5 * HTML2PDF::$_tables[$param['num']]['cellspacing']) + $this->parsingCss->value['border']['r']['width'];
			$marge['b'] = $this->parsingCss->value['padding']['b'] + (0.5 * HTML2PDF::$_tables[$param['num']]['cellspacing']) + $this->parsingCss->value['border']['b']['width'];
			$marge['l'] = $this->parsingCss->value['padding']['l'] + (0.5 * HTML2PDF::$_tables[$param['num']]['cellspacing']) + $this->parsingCss->value['border']['l']['width'];
			$marge['t'] += 0.001;
			$marge['r'] += 0.001;
			$marge['b'] += 0.001;
			$marge['l'] += 0.001;

			if ($this->_subPart) {
				if ($this->_testTdInOnepage && (1 < $this->_subHtml->pdf->getPage())) {
					throw new HTML2PDF_exception(7);
				}

				$w0 = $this->_subHtml->_maxX + $marge['l'] + $marge['r'];
				$h0 = $this->_subHtml->_maxY + $marge['t'] + $marge['b'];
				$w2 = $this->parsingCss->value['width'] + $marge['l'] + $marge['r'];
				$h2 = $this->parsingCss->value['height'] + $marge['t'] + $marge['b'];
				HTML2PDF::$_tables[$param['num']]['cases'][HTML2PDF::$_tables[$param['num']]['tr_curr'] - 1][HTML2PDF::$_tables[$param['num']]['td_curr'] - 1]['w'] = max(array($w0, $w2));
				HTML2PDF::$_tables[$param['num']]['cases'][HTML2PDF::$_tables[$param['num']]['tr_curr'] - 1][HTML2PDF::$_tables[$param['num']]['td_curr'] - 1]['h'] = max(array($h0, $h2));
				HTML2PDF::$_tables[$param['num']]['cases'][HTML2PDF::$_tables[$param['num']]['tr_curr'] - 1][HTML2PDF::$_tables[$param['num']]['td_curr'] - 1]['real_w'] = $w0;
				HTML2PDF::$_tables[$param['num']]['cases'][HTML2PDF::$_tables[$param['num']]['tr_curr'] - 1][HTML2PDF::$_tables[$param['num']]['td_curr'] - 1]['real_h'] = $h0;
				$this->_destroySubHTML($this->_subHtml);
			}
			else {
				$this->_loadMargin();
				HTML2PDF::$_tables[$param['num']]['td_x'] += HTML2PDF::$_tables[$param['num']]['cases'][HTML2PDF::$_tables[$param['num']]['tr_curr'] - 1][HTML2PDF::$_tables[$param['num']]['td_curr'] - 1]['w'];
			}

			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_TH($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->parsingCss->save();
			$this->parsingCss->value['font-bold'] = true;
			$this->_tag_open_TD($param, 'th');
			return true;
		}

		protected function _tag_close_TH($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->_tag_close_TD($param);
			$this->parsingCss->load();
			return true;
		}

		protected function _tag_open_IMG($param)
		{
			$src = str_replace('&amp;', '&', $param['src']);
			$this->parsingCss->save();
			$this->parsingCss->value['width'] = 0;
			$this->parsingCss->value['height'] = 0;
			$this->parsingCss->value['border'] = array(
	'type'  => 'none',
	'width' => 0,
	'color' => array(0, 0, 0)
	);
			$this->parsingCss->value['background'] = array('color' => NULL, 'image' => NULL, 'position' => NULL, 'repeat' => NULL);
			$this->parsingCss->analyse('img', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			$res = $this->_drawImage($src, isset($param['sub_li']));

			if (!$res) {
				return $res;
			}

			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			$this->_maxE++;
			return true;
		}

		protected function _tag_open_SELECT($param)
		{
			if (!isset($param['name'])) {
				$param['name'] = 'champs_pdf_' . (count($this->_lstField) + 1);
			}

			$param['name'] = strtolower($param['name']);

			if (isset($this->_lstField[$param['name']])) {
				$this->_lstField[$param['name']]++;
			}
			else {
				$this->_lstField[$param['name']] = 1;
			}

			$this->parsingCss->save();
			$this->parsingCss->analyse('select', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			$this->_lstSelect = array();
			$this->_lstSelect['name'] = $param['name'];
			$this->_lstSelect['multi'] = isset($param['multiple']) ? true : false;
			$this->_lstSelect['size'] = isset($param['size']) ? $param['size'] : 1;
			$this->_lstSelect['options'] = array();
			if ($this->_lstSelect['multi'] && ($this->_lstSelect['size'] < 3)) {
				$this->_lstSelect['size'] = 3;
			}

			return true;
		}

		protected function _tag_open_OPTION($param)
		{
			$level = $this->parsingHtml->getLevel($this->_parsePos);
			$this->_parsePos += count($level);
			$value = (isset($param['value']) ? $param['value'] : 'aut_tag_open_opt_' . (count($this->_lstSelect) + 1));
			$this->_lstSelect['options'][$value] = isset($level[0]['param']['txt']) ? $level[0]['param']['txt'] : '';
			return true;
		}

		protected function _tag_close_OPTION($param)
		{
			return true;
		}

		protected function _tag_close_SELECT()
		{
			$x = $this->pdf->getX();
			$y = $this->pdf->getY();
			$f = 1.0800000000000001 * $this->parsingCss->value['font-size'];
			$w = $this->parsingCss->value['width'];

			if (!$w) {
				$w = 50;
			}

			$h = ($f * 1.0700000000000001 * $this->_lstSelect['size']) + 1;
			$prop = $this->parsingCss->getFormStyle();

			if ($this->_lstSelect['multi']) {
				$prop['multipleSelection'] = 'true';
			}

			if (1 < $this->_lstSelect['size']) {
				$this->pdf->ListBox($this->_lstSelect['name'], $w, $h, $this->_lstSelect['options'], $prop);
			}
			else {
				$this->pdf->ComboBox($this->_lstSelect['name'], $w, $h, $this->_lstSelect['options'], $prop);
			}

			$this->_maxX = max($this->_maxX, $x + $w);
			$this->_maxY = max($this->_maxY, $y + $h);
			$this->_maxH = max($this->_maxH, $h);
			$this->_maxE++;
			$this->pdf->setX($x + $w);
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			$this->_lstSelect = array();
			return true;
		}

		protected function _tag_open_TEXTAREA($param)
		{
			if (!isset($param['name'])) {
				$param['name'] = 'champs_pdf_' . (count($this->_lstField) + 1);
			}

			$param['name'] = strtolower($param['name']);

			if (isset($this->_lstField[$param['name']])) {
				$this->_lstField[$param['name']]++;
			}
			else {
				$this->_lstField[$param['name']] = 1;
			}

			$this->parsingCss->save();
			$this->parsingCss->analyse('textarea', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			$x = $this->pdf->getX();
			$y = $this->pdf->getY();
			$fx = 0.65000000000000002 * $this->parsingCss->value['font-size'];
			$fy = 1.0800000000000001 * $this->parsingCss->value['font-size'];
			$level = $this->parsingHtml->getLevel($this->_parsePos);
			$this->_parsePos += count($level);
			$w = ($fx * (isset($param['cols']) ? $param['cols'] : 22)) + 1;
			$h = ($fy * 1.0700000000000001 * (isset($param['rows']) ? $param['rows'] : 3)) + 3;
			$prop = $this->parsingCss->getFormStyle();
			$prop['multiline'] = true;
			$prop['value'] = isset($level[0]['param']['txt']) ? $level[0]['param']['txt'] : '';
			$this->pdf->TextField($param['name'], $w, $h, $prop, array(), $x, $y);
			$this->_maxX = max($this->_maxX, $x + $w);
			$this->_maxY = max($this->_maxY, $y + $h);
			$this->_maxH = max($this->_maxH, $h);
			$this->_maxE++;
			$this->pdf->setX($x + $w);
			return true;
		}

		protected function _tag_close_TEXTAREA()
		{
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_INPUT($param)
		{
			if (!isset($param['name'])) {
				$param['name'] = 'champs_pdf_' . (count($this->_lstField) + 1);
			}

			if (!isset($param['value'])) {
				$param['value'] = '';
			}

			if (!isset($param['type'])) {
				$param['type'] = 'text';
			}

			$param['name'] = strtolower($param['name']);
			$param['type'] = strtolower($param['type']);

			if (!in_array($param['type'], array('text', 'checkbox', 'radio', 'hidden', 'submit', 'reset', 'button'))) {
				$param['type'] = 'text';
			}

			if (isset($this->_lstField[$param['name']])) {
				$this->_lstField[$param['name']]++;
			}
			else {
				$this->_lstField[$param['name']] = 1;
			}

			$this->parsingCss->save();
			$this->parsingCss->analyse('input', $param);
			$this->parsingCss->setPosition();
			$this->parsingCss->fontSet();
			$name = $param['name'];
			$x = $this->pdf->getX();
			$y = $this->pdf->getY();
			$f = 1.0800000000000001 * $this->parsingCss->value['font-size'];
			$prop = $this->parsingCss->getFormStyle();

			switch ($param['type']) {
			case 'checkbox':
				$w = 3;
				$h = $w;

				if ($h < $f) {
					$y += ($f - $h) * 0.5;
				}

				$checked = isset($param['checked']) && ($param['checked'] == 'checked');
				$this->pdf->CheckBox($name, $w, $checked, $prop, array(), $param['value'] ? $param['value'] : 'Yes', $x, $y);
				break;

			case 'radio':
				$w = 3;
				$h = $w;

				if ($h < $f) {
					$y += ($f - $h) * 0.5;
				}

				$checked = isset($param['checked']) && ($param['checked'] == 'checked');
				$this->pdf->RadioButton($name, $w, $prop, array(), $param['value'] ? $param['value'] : 'On', $checked, $x, $y);
				break;

			case 'hidden':
				$w = 0;
				$h = 0;
				$prop['value'] = $param['value'];
				$this->pdf->TextField($name, $w, $h, $prop, array(), $x, $y);
				break;

			case 'text':
				$w = $this->parsingCss->value['width'];

				if (!$w) {
					$w = 40;
				}

				$h = $f * 1.3;
				$prop['value'] = $param['value'];
				$this->pdf->TextField($name, $w, $h, $prop, array(), $x, $y);
				break;

			case 'submit':
				$w = $this->parsingCss->value['width'];

				if (!$w) {
					$w = 40;
				}

				$h = $this->parsingCss->value['height'];

				if (!$h) {
					$h = $f * 1.3;
				}

				$action = array(
					'S'     => 'SubmitForm',
					'F'     => $this->_isInForm,
					'Flags' => array('ExportFormat')
					);
				$this->pdf->Button($name, $w, $h, $param['value'], $action, $prop, array(), $x, $y);
				break;

			case 'reset':
				$w = $this->parsingCss->value['width'];

				if (!$w) {
					$w = 40;
				}

				$h = $this->parsingCss->value['height'];

				if (!$h) {
					$h = $f * 1.3;
				}

				$action = array('S' => 'ResetForm');
				$this->pdf->Button($name, $w, $h, $param['value'], $action, $prop, array(), $x, $y);
				break;

			case 'button':
				$w = $this->parsingCss->value['width'];

				if (!$w) {
					$w = 40;
				}

				$h = $this->parsingCss->value['height'];

				if (!$h) {
					$h = $f * 1.3;
				}

				$action = (isset($param['onclick']) ? $param['onclick'] : '');
				$this->pdf->Button($name, $w, $h, $param['value'], $action, $prop, array(), $x, $y);
				break;

			default:
				$w = 0;
				$h = 0;
				break;
			}

			$this->_maxX = max($this->_maxX, $x + $w);
			$this->_maxY = max($this->_maxY, $y + $h);
			$this->_maxH = max($this->_maxH, $h);
			$this->_maxE++;
			$this->pdf->setX($x + $w);
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			return true;
		}

		protected function _tag_open_DRAW($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			if ($this->_debugActif) {
				$this->_DEBUG_add('DRAW', true);
			}

			$this->parsingCss->save();
			$this->parsingCss->analyse('draw', $param);
			$this->parsingCss->fontSet();
			$alignObject = NULL;

			if ($this->parsingCss->value['margin-auto']) {
				$alignObject = 'center';
			}

			$overW = $this->parsingCss->value['width'];
			$overH = $this->parsingCss->value['height'];
			$this->parsingCss->value['old_maxX'] = $this->_maxX;
			$this->parsingCss->value['old_maxY'] = $this->_maxY;
			$this->parsingCss->value['old_maxH'] = $this->_maxH;
			$w = $this->parsingCss->value['width'];
			$h = $this->parsingCss->value['height'];

			if (!$this->parsingCss->value['position']) {
				if (($w < ($this->pdf->getW() - $this->pdf->getlMargin() - $this->pdf->getrMargin())) && (($this->pdf->getW() - $this->pdf->getrMargin()) <= $this->pdf->getX() + $w)) {
					$this->_tag_open_BR(array());
				}

				if (($h < ($this->pdf->getH() - $this->pdf->gettMargin() - $this->pdf->getbMargin())) && (($this->pdf->getH() - $this->pdf->getbMargin()) <= $this->pdf->getY() + $h) && !$this->_isInOverflow) {
					$this->_setNewPage();
				}

				$old = $this->parsingCss->getOldValues();
				$parentWidth = ($old['width'] ? $old['width'] : $this->pdf->getW() - $this->pdf->getlMargin() - $this->pdf->getrMargin());

				if ($w < $parentWidth) {
					if ($alignObject == 'center') {
						$this->pdf->setX($this->pdf->getX() + (($parentWidth - $w) * 0.5));
					}
					else if ($alignObject == 'right') {
						$this->pdf->setX(($this->pdf->getX() + $parentWidth) - $w);
					}
				}

				$this->parsingCss->setPosition();
			}
			else {
				$old = $this->parsingCss->getOldValues();
				$parentWidth = ($old['width'] ? $old['width'] : $this->pdf->getW() - $this->pdf->getlMargin() - $this->pdf->getrMargin());

				if ($w < $parentWidth) {
					if ($alignObject == 'center') {
						$this->pdf->setX($this->pdf->getX() + (($parentWidth - $w) * 0.5));
					}
					else if ($alignObject == 'right') {
						$this->pdf->setX(($this->pdf->getX() + $parentWidth) - $w);
					}
				}

				$this->parsingCss->setPosition();
				$this->_saveMax();
				$this->_maxX = 0;
				$this->_maxY = 0;
				$this->_maxH = 0;
				$this->_maxE = 0;
			}

			$this->_drawRectangle($this->parsingCss->value['x'], $this->parsingCss->value['y'], $this->parsingCss->value['width'], $this->parsingCss->value['height'], $this->parsingCss->value['border'], $this->parsingCss->value['padding'], 0, $this->parsingCss->value['background']);
			$marge = array();
			$marge['l'] = $this->parsingCss->value['border']['l']['width'];
			$marge['r'] = $this->parsingCss->value['border']['r']['width'];
			$marge['t'] = $this->parsingCss->value['border']['t']['width'];
			$marge['b'] = $this->parsingCss->value['border']['b']['width'];
			$this->parsingCss->value['width'] -= $marge['l'] + $marge['r'];
			$this->parsingCss->value['height'] -= $marge['t'] + $marge['b'];
			$overW -= $marge['l'] + $marge['r'];
			$overH -= $marge['t'] + $marge['b'];
			$this->pdf->clippingPathStart($this->parsingCss->value['x'] + $marge['l'], $this->parsingCss->value['y'] + $marge['t'], $this->parsingCss->value['width'], $this->parsingCss->value['height']);
			$mL = $this->parsingCss->value['x'] + $marge['l'];
			$mR = $this->pdf->getW() - $mL - $overW;
			$x = $this->parsingCss->value['x'] + $marge['l'];
			$y = $this->parsingCss->value['y'] + $marge['t'];
			$this->_saveMargin($mL, 0, $mR);
			$this->pdf->setXY($x, $y);
			$this->_isInDraw = array('x' => $x, 'y' => $y, 'w' => $overW, 'h' => $overH);
			$this->pdf->doTransform(array(1, 0, 0, 1, $x, $y));
			$this->pdf->SetAlpha(1);
			return true;
		}

		protected function _tag_close_DRAW($param)
		{
			if ($this->_isForOneLine) {
				return false;
			}

			$this->pdf->SetAlpha(1);
			$this->pdf->undoTransform();
			$this->pdf->clippingPathStop();
			$this->_maxX = $this->parsingCss->value['old_maxX'];
			$this->_maxY = $this->parsingCss->value['old_maxY'];
			$this->_maxH = $this->parsingCss->value['old_maxH'];
			$marge = array();
			$marge['l'] = $this->parsingCss->value['border']['l']['width'];
			$marge['r'] = $this->parsingCss->value['border']['r']['width'];
			$marge['t'] = $this->parsingCss->value['border']['t']['width'];
			$marge['b'] = $this->parsingCss->value['border']['b']['width'];
			$x = $this->parsingCss->value['x'];
			$y = $this->parsingCss->value['y'];
			$w = $this->parsingCss->value['width'] + $marge['l'] + $marge['r'];
			$h = $this->parsingCss->value['height'] + $marge['t'] + $marge['b'];

			if ($this->parsingCss->value['position'] != 'absolute') {
				$this->pdf->setXY($x + $w, $y);
				$this->_maxX = max($this->_maxX, $x + $w);
				$this->_maxY = max($this->_maxY, $y + $h);
				$this->_maxH = max($this->_maxH, $h);
				$this->_maxE++;
			}
			else {
				$this->pdf->setXY($this->parsingCss->value['xc'], $this->parsingCss->value['yc']);
				$this->_loadMax();
			}

			$block = ($this->parsingCss->value['display'] != 'inline') && ($this->parsingCss->value['position'] != 'absolute');
			$this->parsingCss->load();
			$this->parsingCss->fontSet();
			$this->_loadMargin();

			if ($block) {
				$this->_tag_open_BR(array());
			}

			if ($this->_debugActif) {
				$this->_DEBUG_add('DRAW', false);
			}

			$this->_isInDraw = NULL;
			return true;
		}

		protected function _tag_open_LINE($param)
		{
			if (!$this->_isInDraw) {
				throw new HTML2PDF_exception(8, 'LINE');
			}

			$this->pdf->doTransform(isset($param['transform']) ? $this->_prepareTransform($param['transform']) : NULL);
			$this->parsingCss->save();
			$styles = $this->parsingCss->getSvgStyle('path', $param);
			$styles['fill'] = NULL;
			$style = $this->pdf->svgSetStyle($styles);
			$x1 = (isset($param['x1']) ? $this->parsingCss->ConvertToMM($param['x1'], $this->_isInDraw['w']) : 0);
			$y1 = (isset($param['y1']) ? $this->parsingCss->ConvertToMM($param['y1'], $this->_isInDraw['h']) : 0);
			$x2 = (isset($param['x2']) ? $this->parsingCss->ConvertToMM($param['x2'], $this->_isInDraw['w']) : 0);
			$y2 = (isset($param['y2']) ? $this->parsingCss->ConvertToMM($param['y2'], $this->_isInDraw['h']) : 0);
			$this->pdf->svgLine($x1, $y1, $x2, $y2);
			$this->pdf->undoTransform();
			$this->parsingCss->load();
		}

		protected function _tag_open_RECT($param)
		{
			if (!$this->_isInDraw) {
				throw new HTML2PDF_exception(8, 'RECT');
			}

			$this->pdf->doTransform(isset($param['transform']) ? $this->_prepareTransform($param['transform']) : NULL);
			$this->parsingCss->save();
			$styles = $this->parsingCss->getSvgStyle('path', $param);
			$style = $this->pdf->svgSetStyle($styles);
			$x = (isset($param['x']) ? $this->parsingCss->ConvertToMM($param['x'], $this->_isInDraw['w']) : 0);
			$y = (isset($param['y']) ? $this->parsingCss->ConvertToMM($param['y'], $this->_isInDraw['h']) : 0);
			$w = (isset($param['w']) ? $this->parsingCss->ConvertToMM($param['w'], $this->_isInDraw['w']) : 0);
			$h = (isset($param['h']) ? $this->parsingCss->ConvertToMM($param['h'], $this->_isInDraw['h']) : 0);
			$this->pdf->svgRect($x, $y, $w, $h, $style);
			$this->pdf->undoTransform();
			$this->parsingCss->load();
		}

		protected function _tag_open_CIRCLE($param)
		{
			if (!$this->_isInDraw) {
				throw new HTML2PDF_exception(8, 'CIRCLE');
			}

			$this->pdf->doTransform(isset($param['transform']) ? $this->_prepareTransform($param['transform']) : NULL);
			$this->parsingCss->save();
			$styles = $this->parsingCss->getSvgStyle('path', $param);
			$style = $this->pdf->svgSetStyle($styles);
			$cx = (isset($param['cx']) ? $this->parsingCss->ConvertToMM($param['cx'], $this->_isInDraw['w']) : 0);
			$cy = (isset($param['cy']) ? $this->parsingCss->ConvertToMM($param['cy'], $this->_isInDraw['h']) : 0);
			$r = (isset($param['r']) ? $this->parsingCss->ConvertToMM($param['r'], $this->_isInDraw['w']) : 0);
			$this->pdf->svgEllipse($cx, $cy, $r, $r, $style);
			$this->pdf->undoTransform();
			$this->parsingCss->load();
		}

		protected function _tag_open_ELLIPSE($param)
		{
			if (!$this->_isInDraw) {
				throw new HTML2PDF_exception(8, 'ELLIPSE');
			}

			$this->pdf->doTransform(isset($param['transform']) ? $this->_prepareTransform($param['transform']) : NULL);
			$this->parsingCss->save();
			$styles = $this->parsingCss->getSvgStyle('path', $param);
			$style = $this->pdf->svgSetStyle($styles);
			$cx = (isset($param['cx']) ? $this->parsingCss->ConvertToMM($param['cx'], $this->_isInDraw['w']) : 0);
			$cy = (isset($param['cy']) ? $this->parsingCss->ConvertToMM($param['cy'], $this->_isInDraw['h']) : 0);
			$rx = (isset($param['ry']) ? $this->parsingCss->ConvertToMM($param['rx'], $this->_isInDraw['w']) : 0);
			$ry = (isset($param['rx']) ? $this->parsingCss->ConvertToMM($param['ry'], $this->_isInDraw['h']) : 0);
			$this->pdf->svgEllipse($cx, $cy, $rx, $ry, $style);
			$this->pdf->undoTransform();
			$this->parsingCss->load();
		}

		protected function _tag_open_POLYLINE($param)
		{
			if (!$this->_isInDraw) {
				throw new HTML2PDF_exception(8, 'POLYGON');
			}

			$this->pdf->doTransform(isset($param['transform']) ? $this->_prepareTransform($param['transform']) : NULL);
			$this->parsingCss->save();
			$styles = $this->parsingCss->getSvgStyle('path', $param);
			$style = $this->pdf->svgSetStyle($styles);
			$path = (isset($param['points']) ? $param['points'] : NULL);

			if ($path) {
				$path = str_replace(',', ' ', $path);
				$path = preg_replace('/[\\s]+/', ' ', trim($path));
				$path = explode(' ', $path);

				foreach ($path as $k => $v) {
					$path[$k] = trim($v);

					if ($path[$k] === '') {
						unset($path[$k]);
					}
				}

				$path = array_values($path);
				$actions = array();

				for ($k = 0; $k < count($path); $k += 2) {
					$actions[] = array($k ? 'L' : 'M', $this->parsingCss->ConvertToMM($path[$k + 0], $this->_isInDraw['w']), $this->parsingCss->ConvertToMM($path[$k + 1], $this->_isInDraw['h']));
				}

				$this->pdf->svgPolygone($actions, $style);
			}

			$this->pdf->undoTransform();
			$this->parsingCss->load();
		}

		protected function _tag_open_POLYGON($param)
		{
			if (!$this->_isInDraw) {
				throw new HTML2PDF_exception(8, 'POLYGON');
			}

			$this->pdf->doTransform(isset($param['transform']) ? $this->_prepareTransform($param['transform']) : NULL);
			$this->parsingCss->save();
			$styles = $this->parsingCss->getSvgStyle('path', $param);
			$style = $this->pdf->svgSetStyle($styles);
			$path = (isset($param['points']) ? $param['points'] : NULL);

			if ($path) {
				$path = str_replace(',', ' ', $path);
				$path = preg_replace('/[\\s]+/', ' ', trim($path));
				$path = explode(' ', $path);

				foreach ($path as $k => $v) {
					$path[$k] = trim($v);

					if ($path[$k] === '') {
						unset($path[$k]);
					}
				}

				$path = array_values($path);
				$actions = array();

				for ($k = 0; $k < count($path); $k += 2) {
					$actions[] = array($k ? 'L' : 'M', $this->parsingCss->ConvertToMM($path[$k + 0], $this->_isInDraw['w']), $this->parsingCss->ConvertToMM($path[$k + 1], $this->_isInDraw['h']));
				}

				$actions[] = array('z');
				$this->pdf->svgPolygone($actions, $style);
			}

			$this->pdf->undoTransform();
			$this->parsingCss->load();
		}

		protected function _tag_open_PATH($param)
		{
			if (!$this->_isInDraw) {
				throw new HTML2PDF_exception(8, 'PATH');
			}

			$this->pdf->doTransform(isset($param['transform']) ? $this->_prepareTransform($param['transform']) : NULL);
			$this->parsingCss->save();
			$styles = $this->parsingCss->getSvgStyle('path', $param);
			$style = $this->pdf->svgSetStyle($styles);
			$path = (isset($param['d']) ? $param['d'] : NULL);

			if ($path) {
				$path = str_replace(',', ' ', $path);
				$path = preg_replace('/([a-zA-Z])([0-9\\.\\-])/', '$1 $2', $path);
				$path = preg_replace('/([0-9\\.])([a-zA-Z])/', '$1 $2', $path);
				$path = preg_replace('/[\\s]+/', ' ', trim($path));
				$path = preg_replace('/ ([a-z]{2})/', '$1', $path);
				$path = explode(' ', $path);

				foreach ($path as $k => $v) {
					$path[$k] = trim($v);

					if ($path[$k] === '') {
						unset($path[$k]);
					}
				}

				$path = array_values($path);
				$actions = array();
				$action = array();
				$lastAction = NULL;

				for ($k = 0; $k < count($path); ) {
					if (in_array($lastAction, array('z', 'Z'))) {
						$lastAction = NULL;
					}

					if (preg_match('/^[a-z]+$/i', $path[$k]) || ($lastAction === NULL)) {
						$lastAction = $path[$k];
						$k++;
					}

					$action = array();
					$action[] = $lastAction;

					switch ($lastAction) {
					case 'C':
					case 'c':
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 0], $this->_isInDraw['w']);
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 1], $this->_isInDraw['h']);
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 2], $this->_isInDraw['w']);
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 3], $this->_isInDraw['h']);
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 4], $this->_isInDraw['w']);
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 5], $this->_isInDraw['h']);
						$k += 6;
						break;

					case 'Q':
					case 'S':
					case 'q':
					case 's':
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 0], $this->_isInDraw['w']);
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 1], $this->_isInDraw['h']);
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 2], $this->_isInDraw['w']);
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 3], $this->_isInDraw['h']);
						$k += 4;
						break;

					case 'A':
					case 'a':
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 0], $this->_isInDraw['w']);
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 1], $this->_isInDraw['h']);
						$action[] = 1 * $path[$k + 2];
						$action[] = $path[$k + 3] == '1' ? 1 : 0;
						$action[] = $path[$k + 4] == '1' ? 1 : 0;
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 5], $this->_isInDraw['w']);
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 6], $this->_isInDraw['h']);
						$k += 7;
						break;

					case 'M':
					case 'L':
					case 'T':
					case 'm':
					case 'l':
					case 't':
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 0], $this->_isInDraw['w']);
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 1], $this->_isInDraw['h']);
						$k += 2;
						break;

					case 'H':
					case 'h':
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 0], $this->_isInDraw['w']);
						$k += 1;
						break;

					case 'V':
					case 'v':
						$action[] = $this->parsingCss->ConvertToMM($path[$k + 0], $this->_isInDraw['h']);
						$k += 1;
						break;

					case 'z':
					case 'Z':
					default:
						break;
					}

					$actions[] = $action;
				}

				$this->pdf->svgPolygone($actions, $style);
			}

			$this->pdf->undoTransform();
			$this->parsingCss->load();
		}

		protected function _tag_open_G($param)
		{
			if (!$this->_isInDraw) {
				throw new HTML2PDF_exception(8, 'G');
			}

			$this->pdf->doTransform(isset($param['transform']) ? $this->_prepareTransform($param['transform']) : NULL);
			$this->parsingCss->save();
			$styles = $this->parsingCss->getSvgStyle('path', $param);
			$style = $this->pdf->svgSetStyle($styles);
		}

		protected function _tag_close_G($param)
		{
			$this->pdf->undoTransform();
			$this->parsingCss->load();
		}

		public function _INDEX_NewPage(&$page)
		{
			if ($page) {
				$oldPage = $this->pdf->getPage();
				$this->pdf->setPage($page);
				$this->pdf->setXY($this->_margeLeft, $this->_margeTop);
				$this->_maxH = 0;
				$page++;
				return $oldPage;
			}
			else {
				$this->_setNewPage();
				return NULL;
			}
		}
	}
}

?>
