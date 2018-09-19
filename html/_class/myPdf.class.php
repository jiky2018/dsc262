<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
require_once dirname(__FILE__) . '/tcpdfConfig.php';
require_once dirname(__FILE__) . '/../_tcpdf_' . HTML2PDF_USED_TCPDF_VERSION . '/tcpdf.php';
class HTML2PDF_myPdf extends TCPDF
{
	const MY_ARC = 0.55228474979999997;
	const ARC_NB_SEGMENT = 8;

	protected $_footerParam = array();
	protected $_transf = array();
	protected $_myLastPageGroup;
	protected $_myLastPageGroupNb = 0;

	public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false)
	{
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache);
		$this->SetCreator(PDF_CREATOR);
		$this->SetAutoPageBreak(false, 0);
		$this->linestyleCap = '2 J';
		$this->setPrintHeader(false);
		$this->jpeg_quality = 90;
		$this->SetMyFooter();
		$this->cMargin = 0;
	}

	public function SetMyFooter($page = false, $date = false, $hour = false, $form = false)
	{
		$page = ($page ? true : false);
		$date = ($date ? true : false);
		$hour = ($hour ? true : false);
		$form = ($form ? true : false);
		$this->_footerParam = array('page' => $page, 'date' => $date, 'hour' => $hour, 'form' => $form);
	}

	public function Footer()
	{
		$txt = '';

		if ($this->_footerParam['form']) {
			$txt = HTML2PDF_locale::get('pdf05');
		}

		if ($this->_footerParam['date'] && $this->_footerParam['hour']) {
			$txt .= ($txt ? ' - ' : '') . HTML2PDF_locale::get('pdf03');
		}

		if ($this->_footerParam['date'] && !$this->_footerParam['hour']) {
			$txt .= ($txt ? ' - ' : '') . HTML2PDF_locale::get('pdf01');
		}

		if (!$this->_footerParam['date'] && $this->_footerParam['hour']) {
			$txt .= ($txt ? ' - ' : '') . HTML2PDF_locale::get('pdf02');
		}

		if ($this->_footerParam['page']) {
			$txt .= ($txt ? ' - ' : '') . HTML2PDF_locale::get('pdf04');
		}

		if (0 < strlen($txt)) {
			$toReplace = array('[[date_d]]' => date('d'), '[[date_m]]' => date('m'), '[[date_y]]' => date('Y'), '[[date_h]]' => date('H'), '[[date_i]]' => date('i'), '[[date_s]]' => date('s'), '[[page_cu]]' => $this->getMyNumPage(), '[[page_nb]]' => $this->getMyAliasNbPages());
			$txt = str_replace(array_keys($toReplace), array_values($toReplace), $txt);
			parent::SetY(-11);
			$this->SetFont('helvetica', 'I', 8);
			$this->Cell(0, 10, $txt, 0, 0, 'R');
		}
	}

	public function cloneFontFrom(&$pdf)
	{
		$this->fonts = &$pdf->getFonts();
		$this->FontFiles = &$pdf->getFontFiles();
		$this->diffs = &$pdf->getDiffs();
		$this->fontlist = &$pdf->getFontList();
		$this->numfonts = &$pdf->getNumFonts();
		$this->fontkeys = &$pdf->getFontKeys();
		$this->font_obj_ids = &$pdf->getFontObjIds();
		$this->annotation_fonts = &$pdf->getAnnotFonts();
	}

	public function& getFonts()
	{
		return $this->fonts;
	}

	public function& getFontFiles()
	{
		return $this->FontFiles;
	}

	public function& getDiffs()
	{
		return $this->diffs;
	}

	public function& getFontList()
	{
		return $this->fontlist;
	}

	public function& getNumFonts()
	{
		return $this->numfonts;
	}

	public function& getFontKeys()
	{
		return $this->fontkeys;
	}

	public function& getFontObjIds()
	{
		return $this->font_obj_ids;
	}

	public function& getAnnotFonts()
	{
		return $this->annotation_fonts;
	}

	public function isLoadedFont($fontKey)
	{
		if (isset($this->fonts[$fontKey])) {
			return true;
		}

		if (isset($this->CoreFonts[$fontKey])) {
			return true;
		}

		return false;
	}

	public function getWordSpacing()
	{
		return $this->ws;
	}

	public function setWordSpacing($ws = 0)
	{
		$this->ws = $ws;
		$this->_out(sprintf('%.3F Tw', $ws * $this->k));
	}

	public function clippingPathStart($x = NULL, $y = NULL, $w = NULL, $h = NULL, $cornerTL = NULL, $cornerTR = NULL, $cornerBL = NULL, $cornerBR = NULL)
	{
		$path = '';
		if (($x !== NULL) && ($y !== NULL) && ($w !== NULL) && ($h !== NULL)) {
			$x1 = $x * $this->k;
			$y1 = ($this->h - $y) * $this->k;
			$x2 = ($x + $w) * $this->k;
			$y2 = ($this->h - $y) * $this->k;
			$x3 = ($x + $w) * $this->k;
			$y3 = ($this->h - $y - $h) * $this->k;
			$x4 = $x * $this->k;
			$y4 = ($this->h - $y - $h) * $this->k;
			if ($cornerTL || $cornerTR || $cornerBL || $cornerBR) {
				if ($cornerTL) {
					$cornerTL[0] = $cornerTL[0] * $this->k;
					$cornerTL[1] = (0 - $cornerTL[1]) * $this->k;
				}

				if ($cornerTR) {
					$cornerTR[0] = $cornerTR[0] * $this->k;
					$cornerTR[1] = (0 - $cornerTR[1]) * $this->k;
				}

				if ($cornerBL) {
					$cornerBL[0] = $cornerBL[0] * $this->k;
					$cornerBL[1] = (0 - $cornerBL[1]) * $this->k;
				}

				if ($cornerBR) {
					$cornerBR[0] = $cornerBR[0] * $this->k;
					$cornerBR[1] = (0 - $cornerBR[1]) * $this->k;
				}

				if ($cornerTL) {
					$path .= sprintf('%.2F %.2F m ', $x1 + $cornerTL[0], $y1);
				}
				else {
					$path .= sprintf('%.2F %.2F m ', $x1, $y1);
				}

				if ($cornerTR) {
					$xt1 = ($x2 - $cornerTR[0]) + ($cornerTR[0] * self::MY_ARC);
					$yt1 = ($y2 + $cornerTR[1]) - $cornerTR[1];
					$xt2 = ($x2 - $cornerTR[0]) + $cornerTR[0];
					$yt2 = ($y2 + $cornerTR[1]) - ($cornerTR[1] * self::MY_ARC);
					$path .= sprintf('%.2F %.2F l ', $x2 - $cornerTR[0], $y2);
					$path .= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $x2, $y2 + $cornerTR[1]);
				}
				else {
					$path .= sprintf('%.2F %.2F l ', $x2, $y2);
				}

				if ($cornerBR) {
					$xt1 = ($x3 - $cornerBR[0]) + $cornerBR[0];
					$yt1 = ($y3 - $cornerBR[1]) + ($cornerBR[1] * self::MY_ARC);
					$xt2 = ($x3 - $cornerBR[0]) + ($cornerBR[0] * self::MY_ARC);
					$yt2 = ($y3 - $cornerBR[1]) + $cornerBR[1];
					$path .= sprintf('%.2F %.2F l ', $x3, $y3 - $cornerBR[1]);
					$path .= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $x3 - $cornerBR[0], $y3);
				}
				else {
					$path .= sprintf('%.2F %.2F l ', $x3, $y3);
				}

				if ($cornerBL) {
					$xt1 = ($x4 + $cornerBL[0]) - ($cornerBL[0] * self::MY_ARC);
					$yt1 = ($y4 - $cornerBL[1]) + $cornerBL[1];
					$xt2 = ($x4 + $cornerBL[0]) - $cornerBL[0];
					$yt2 = ($y4 - $cornerBL[1]) + ($cornerBL[1] * self::MY_ARC);
					$path .= sprintf('%.2F %.2F l ', $x4 + $cornerBL[0], $y4);
					$path .= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $x4, $y4 - $cornerBL[1]);
				}
				else {
					$path .= sprintf('%.2F %.2F l ', $x4, $y4);
				}

				if ($cornerTL) {
					$xt1 = ($x1 + $cornerTL[0]) - $cornerTL[0];
					$yt1 = ($y1 + $cornerTL[1]) - ($cornerTL[1] * self::MY_ARC);
					$xt2 = ($x1 + $cornerTL[0]) - ($cornerTL[0] * self::MY_ARC);
					$yt2 = ($y1 + $cornerTL[1]) - $cornerTL[1];
					$path .= sprintf('%.2F %.2F l ', $x1, $y1 + $cornerTL[1]);
					$path .= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $x1 + $cornerTL[0], $y1);
				}
			}
			else {
				$path .= sprintf('%.2F %.2F m ', $x1, $y1);
				$path .= sprintf('%.2F %.2F l ', $x2, $y2);
				$path .= sprintf('%.2F %.2F l ', $x3, $y3);
				$path .= sprintf('%.2F %.2F l ', $x4, $y4);
			}

			$path .= ' h W n';
		}

		$this->_out('q ' . $path . ' ');
	}

	public function clippingPathStop()
	{
		$this->_out(' Q');
	}

	public function drawCurve($ext1X, $ext1Y, $ext2X, $ext2Y, $int1X, $int1Y, $int2X, $int2Y, $cenX, $cenY)
	{
		$ext1X = $ext1X * $this->k;
		$ext2X = $ext2X * $this->k;
		$int1X = $int1X * $this->k;
		$int2X = $int2X * $this->k;
		$cenX = $cenX * $this->k;
		$ext1Y = ($this->h - $ext1Y) * $this->k;
		$ext2Y = ($this->h - $ext2Y) * $this->k;
		$int1Y = ($this->h - $int1Y) * $this->k;
		$int2Y = ($this->h - $int2Y) * $this->k;
		$cenY = ($this->h - $cenY) * $this->k;
		$path = '';

		if (($ext1X - $cenX) != 0) {
			$xt1 = $cenX + ($ext1X - $cenX);
			$yt1 = $cenY + (($ext2Y - $cenY) * self::MY_ARC);
			$xt2 = $cenX + (($ext1X - $cenX) * self::MY_ARC);
			$yt2 = $cenY + ($ext2Y - $cenY);
		}
		else {
			$xt1 = $cenX + (($ext2X - $cenX) * self::MY_ARC);
			$yt1 = $cenY + ($ext1Y - $cenY);
			$xt2 = $cenX + ($ext2X - $cenX);
			$yt2 = $cenY + (($ext1Y - $cenY) * self::MY_ARC);
		}

		$path .= sprintf('%.2F %.2F m ', $ext1X, $ext1Y);
		$path .= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $ext2X, $ext2Y);

		if (($int1X - $cenX) != 0) {
			$xt1 = $cenX + (($int1X - $cenX) * self::MY_ARC);
			$yt1 = $cenY + ($int2Y - $cenY);
			$xt2 = $cenX + ($int1X - $cenX);
			$yt2 = $cenY + (($int2Y - $cenY) * self::MY_ARC);
		}
		else {
			$xt1 = $cenX + ($int2X - $cenX);
			$yt1 = $cenY + (($int1Y - $cenY) * self::MY_ARC);
			$xt2 = $cenX + (($int2X - $cenX) * self::MY_ARC);
			$yt2 = $cenY + ($int1Y - $cenY);
		}

		$path .= sprintf('%.2F %.2F l ', $int2X, $int2Y);
		$path .= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $int1X, $int1Y);
		$this->_out($path . 'f');
	}

	public function drawCorner($ext1X, $ext1Y, $ext2X, $ext2Y, $intX, $intY, $cenX, $cenY)
	{
		$ext1X = $ext1X * $this->k;
		$ext2X = $ext2X * $this->k;
		$intX = $intX * $this->k;
		$cenX = $cenX * $this->k;
		$ext1Y = ($this->h - $ext1Y) * $this->k;
		$ext2Y = ($this->h - $ext2Y) * $this->k;
		$intY = ($this->h - $intY) * $this->k;
		$cenY = ($this->h - $cenY) * $this->k;
		$path = '';

		if (($ext1X - $cenX) != 0) {
			$xt1 = $cenX + ($ext1X - $cenX);
			$yt1 = $cenY + (($ext2Y - $cenY) * self::MY_ARC);
			$xt2 = $cenX + (($ext1X - $cenX) * self::MY_ARC);
			$yt2 = $cenY + ($ext2Y - $cenY);
		}
		else {
			$xt1 = $cenX + (($ext2X - $cenX) * self::MY_ARC);
			$yt1 = $cenY + ($ext1Y - $cenY);
			$xt2 = $cenX + ($ext2X - $cenX);
			$yt2 = $cenY + (($ext1Y - $cenY) * self::MY_ARC);
		}

		$path .= sprintf('%.2F %.2F m ', $ext1X, $ext1Y);
		$path .= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $ext2X, $ext2Y);
		$path .= sprintf('%.2F %.2F l ', $intX, $intY);
		$path .= sprintf('%.2F %.2F l ', $ext1X, $ext1Y);
		$this->_out($path . 'f');
	}

	public function startTransform()
	{
		$this->_out('q');
	}

	public function stopTransform()
	{
		$this->_out('Q');
	}

	public function setTranslate($xT, $yT)
	{
		$tm[0] = 1;
		$tm[1] = 0;
		$tm[2] = 0;
		$tm[3] = 1;
		$tm[4] = $xT * $this->k;
		$tm[5] = (0 - $yT) * $this->k;
		$this->_out(sprintf('%.3F %.3F %.3F %.3F %.3F %.3F cm', $tm[0], $tm[1], $tm[2], $tm[3], $tm[4], $tm[5]));
	}

	public function setRotation($angle, $xC = NULL, $yC = NULL)
	{
		if ($xC === NULL) {
			$xC = $this->x;
		}

		if ($yC === NULL) {
			$yC = $this->y;
		}

		$yC = ($this->h - $yC) * $this->k;
		$xC *= $this->k;
		$tm[0] = cos(deg2rad($angle));
		$tm[1] = sin(deg2rad($angle));
		$tm[2] = 0 - $tm[1];
		$tm[3] = $tm[0];
		$tm[4] = ($xC + ($tm[1] * $yC)) - ($tm[0] * $xC);
		$tm[5] = $yC - ($tm[0] * $yC) - ($tm[1] * $xC);
		$this->_out(sprintf('%.3F %.3F %.3F %.3F %.3F %.3F cm', $tm[0], $tm[1], $tm[2], $tm[3], $tm[4], $tm[5]));
	}

	public function SetX($x, $rtloff = false)
	{
		$this->x = $x;
	}

	public function SetY($y, $resetx = true, $rtloff = false)
	{
		if ($resetx) {
			$this->x = $this->lMargin;
		}

		$this->y = $y;
	}

	public function SetXY($x, $y, $rtloff = false)
	{
		$this->x = $x;
		$this->y = $y;
	}

	public function getK()
	{
		return $this->k;
	}

	public function getW()
	{
		return $this->w;
	}

	public function getH()
	{
		return $this->h;
	}

	public function getlMargin()
	{
		return $this->lMargin;
	}

	public function getrMargin()
	{
		return $this->rMargin;
	}

	public function gettMargin()
	{
		return $this->tMargin;
	}

	public function getbMargin()
	{
		return $this->bMargin;
	}

	public function setbMargin($v)
	{
		$this->bMargin = $v;
	}

	public function svgSetStyle($styles)
	{
		$style = '';

		if ($styles['fill']) {
			$this->setFillColorArray($styles['fill']);
			$style .= 'F';
		}

		if ($styles['stroke'] && $styles['stroke-width']) {
			$this->SetDrawColorArray($styles['stroke']);
			$this->SetLineWidth($styles['stroke-width']);
			$style .= 'D';
		}

		if ($styles['fill-opacity']) {
			$this->SetAlpha($styles['fill-opacity']);
		}

		return $style;
	}

	public function svgRect($x, $y, $w, $h, $style)
	{
		$x1 = $x;
		$x2 = $x + $w;
		$x3 = $x + $w;
		$x4 = $x;
		$y1 = $y;
		$y2 = $y;
		$y3 = $y + $h;
		$y4 = $y + $h;

		if ($style == 'F') {
			$op = 'f';
		}
		else {
			if (($style == 'FD') || ($style == 'DF')) {
				$op = 'B';
			}
			else {
				$op = 'S';
			}
		}

		$this->_Point($x1, $y1, true);
		$this->_Line($x2, $y2, true);
		$this->_Line($x3, $y3, true);
		$this->_Line($x4, $y4, true);
		$this->_Line($x1, $y1, true);
		$this->_out($op);
	}

	public function svgLine($x1, $y1, $x2, $y2)
	{
		$op = 'S';
		$this->_Point($x1, $y1, true);
		$this->_Line($x2, $y2, true);
		$this->_out($op);
	}

	public function svgEllipse($x0, $y0, $rx, $ry, $style)
	{
		if ($style == 'F') {
			$op = 'f';
		}
		else {
			if (($style == 'FD') || ($style == 'DF')) {
				$op = 'B';
			}
			else {
				$op = 'S';
			}
		}

		$this->_Arc($x0, $y0, $rx, $ry, 0, 2 * M_PI, true, true, true);
		$this->_out($op);
	}

	public function svgPolygone($actions, $style)
	{
		if ($style == 'F') {
			$op = 'f';
		}
		else {
			if (($style == 'FD') || ($style == 'DF')) {
				$op = 'B';
			}
			else {
				$op = 'S';
			}
		}

		$first = array('', 0, 0);
		$last = array(0, 0, 0, 0);

		foreach ($actions as $action) {
			switch ($action[0]) {
			case 'M':
			case 'm':
				$first = $action;
				$x = $action[1];
				$y = $action[2];
				$xc = $x;
				$yc = $y;
				$this->_Point($x, $y, true);
				break;

			case 'Z':
			case 'z':
				$x = $first[1];
				$y = $first[2];
				$xc = $x;
				$yc = $y;
				$this->_Line($x, $y, true);
				break;

			case 'L':
				$x = $action[1];
				$y = $action[2];
				$xc = $x;
				$yc = $y;
				$this->_Line($x, $y, true);
				break;

			case 'l':
				$x = $last[0] + $action[1];
				$y = $last[1] + $action[2];
				$xc = $x;
				$yc = $y;
				$this->_Line($x, $y, true);
				break;

			case 'H':
				$x = $action[1];
				$y = $last[1];
				$xc = $x;
				$yc = $y;
				$this->_Line($x, $y, true);
				break;

			case 'h':
				$x = $last[0] + $action[1];
				$y = $last[1];
				$xc = $x;
				$yc = $y;
				$this->_Line($x, $y, true);
				break;

			case 'V':
				$x = $last[0];
				$y = $action[1];
				$xc = $x;
				$yc = $y;
				$this->_Line($x, $y, true);
				break;

			case 'v':
				$x = $last[0];
				$y = $last[1] + $action[1];
				$xc = $x;
				$yc = $y;
				$this->_Line($x, $y, true);
				break;

			case 'A':
				$rx = $action[1];
				$ry = $action[2];
				$a = $action[3];
				$l = $action[4];
				$s = $action[5];
				$x1 = $last[0];
				$y1 = $last[1];
				$x2 = $action[6];
				$y2 = $action[7];
				$this->_Arc2($x1, $y1, $x2, $y2, $rx, $ry, $a, $l, $s, true);
				$x = $x2;
				$y = $y2;
				$xc = $x;
				$yc = $y;
				break;

			case 'a':
				$rx = $action[1];
				$ry = $action[2];
				$a = $action[3];
				$l = $action[4];
				$s = $action[5];
				$x1 = $last[0];
				$y1 = $last[1];
				$x2 = $last[0] + $action[6];
				$y2 = $last[1] + $action[7];
				$this->_Arc2($x1, $y1, $x2, $y2, $rx, $ry, $a, $l, $s, true);
				$x = $x2;
				$y = $y2;
				$xc = $x;
				$yc = $y;
				break;

			case 'C':
				$x1 = $action[1];
				$y1 = $action[2];
				$x2 = $action[3];
				$y2 = $action[4];
				$xf = $action[5];
				$yf = $action[6];
				$this->_Curve($x1, $y1, $x2, $y2, $xf, $yf, true);
				$x = $xf;
				$y = $yf;
				$xc = $x2;
				$yc = $y2;
				break;

			case 'c':
				$x1 = $last[0] + $action[1];
				$y1 = $last[1] + $action[2];
				$x2 = $last[0] + $action[3];
				$y2 = $last[1] + $action[4];
				$xf = $last[0] + $action[5];
				$yf = $last[1] + $action[6];
				$this->_Curve($x1, $y1, $x2, $y2, $xf, $yf, true);
				$x = $xf;
				$y = $yf;
				$xc = $x2;
				$yc = $y2;
				break;

			default:
				throw new HTML2PDF_exception(0, 'SVG Path Error : [' . $action[0] . '] unkown');
			}

			$last = array($x, $y, $xc, $yc);
		}

		$this->_out($op);
	}

	protected function _Point($x, $y, $trans = false)
	{
		if ($trans) {
			$this->ptTransform($x, $y);
		}

		$this->_out(sprintf('%.2F %.2F m', $x, $y));
	}

	protected function _Line($x, $y, $trans = false)
	{
		if ($trans) {
			$this->ptTransform($x, $y);
		}

		$this->_out(sprintf('%.2F %.2F l', $x, $y));
	}

	protected function _Curve($x1, $y1, $x2, $y2, $xf, $yf, $trans = false)
	{
		if ($trans) {
			$this->ptTransform($x1, $y1);
			$this->ptTransform($x2, $y2);
			$this->ptTransform($xf, $yf);
		}

		$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', $x1, $y1, $x2, $y2, $xf, $yf));
	}

	protected function _Arc($xc, $yc, $rx, $ry, $angleBegin, $angleEnd, $direction = true, $drawFirst = true, $trans = false)
	{
		if (!$direction) {
			$angleBegin += M_PI * 2;
		}

		$dt = ($angleEnd - $angleBegin) / self::ARC_NB_SEGMENT;
		$dtm = $dt / 3;
		$x0 = $xc;
		$y0 = $yc;
		$t1 = $angleBegin;
		$a0 = $x0 + ($rx * cos($t1));
		$b0 = $y0 + ($ry * sin($t1));
		$c0 = (0 - $rx) * sin($t1);
		$d0 = $ry * cos($t1);

		if ($drawFirst) {
			$this->_Point($a0, $b0, $trans);
		}

		for ($i = 1; $i <= self::ARC_NB_SEGMENT; $i++) {
			$t1 = ($i * $dt) + $angleBegin;
			$a1 = $x0 + ($rx * cos($t1));
			$b1 = $y0 + ($ry * sin($t1));
			$c1 = (0 - $rx) * sin($t1);
			$d1 = $ry * cos($t1);
			$this->_Curve($a0 + ($c0 * $dtm), $b0 + ($d0 * $dtm), $a1 - ($c1 * $dtm), $b1 - ($d1 * $dtm), $a1, $b1, $trans);
			$a0 = $a1;
			$b0 = $b1;
			$c0 = $c1;
			$d0 = $d1;
		}
	}

	protected function _Arc2($x1, $y1, $x2, $y2, $rx, $ry, $angle = 0, $l = 0, $s = 0, $trans = false)
	{
		$v = array();
		$v['x1'] = $x1;
		$v['y1'] = $y1;
		$v['x2'] = $x2;
		$v['y2'] = $y2;
		$v['rx'] = $rx;
		$v['ry'] = $ry;
		$v['xr1'] = ($v['x1'] * cos($angle)) - ($v['y1'] * sin($angle));
		$v['yr1'] = ($v['x1'] * sin($angle)) + ($v['y1'] * cos($angle));
		$v['xr2'] = ($v['x2'] * cos($angle)) - ($v['y2'] * sin($angle));
		$v['yr2'] = ($v['x2'] * sin($angle)) + ($v['y2'] * cos($angle));
		$v['Xr1'] = $v['xr1'] / $v['rx'];
		$v['Yr1'] = $v['yr1'] / $v['ry'];
		$v['Xr2'] = $v['xr2'] / $v['rx'];
		$v['Yr2'] = $v['yr2'] / $v['ry'];
		$v['dXr'] = $v['Xr2'] - $v['Xr1'];
		$v['dYr'] = $v['Yr2'] - $v['Yr1'];
		$v['D'] = ($v['dXr'] * $v['dXr']) + ($v['dYr'] * $v['dYr']);
		if (($v['D'] == 0) || (4 < $v['D'])) {
			$this->_Line($x2, $y2, $trans);
			return false;
		}

		$v['s1'] = array();
		$v['s1']['t'] = sqrt((4 - $v['D']) / $v['D']);
		$v['s1']['Xr'] = (($v['Xr1'] + $v['Xr2']) / 2) + (($v['s1']['t'] * ($v['Yr2'] - $v['Yr1'])) / 2);
		$v['s1']['Yr'] = (($v['Yr1'] + $v['Yr2']) / 2) + (($v['s1']['t'] * ($v['Xr1'] - $v['Xr2'])) / 2);
		$v['s1']['xr'] = $v['s1']['Xr'] * $v['rx'];
		$v['s1']['yr'] = $v['s1']['Yr'] * $v['ry'];
		$v['s1']['x'] = ($v['s1']['xr'] * cos($angle)) + ($v['s1']['yr'] * sin($angle));
		$v['s1']['y'] = ((0 - $v['s1']['xr']) * sin($angle)) + ($v['s1']['yr'] * cos($angle));
		$v['s1']['a1'] = atan2($v['y1'] - $v['s1']['y'], $v['x1'] - $v['s1']['x']);
		$v['s1']['a2'] = atan2($v['y2'] - $v['s1']['y'], $v['x2'] - $v['s1']['x']);

		if ($v['s1']['a2'] < $v['s1']['a1']) {
			$v['s1']['a1'] -= 2 * M_PI;
		}

		$v['s2'] = array();
		$v['s2']['t'] = 0 - $v['s1']['t'];
		$v['s2']['Xr'] = (($v['Xr1'] + $v['Xr2']) / 2) + (($v['s2']['t'] * ($v['Yr2'] - $v['Yr1'])) / 2);
		$v['s2']['Yr'] = (($v['Yr1'] + $v['Yr2']) / 2) + (($v['s2']['t'] * ($v['Xr1'] - $v['Xr2'])) / 2);
		$v['s2']['xr'] = $v['s2']['Xr'] * $v['rx'];
		$v['s2']['yr'] = $v['s2']['Yr'] * $v['ry'];
		$v['s2']['x'] = ($v['s2']['xr'] * cos($angle)) + ($v['s2']['yr'] * sin($angle));
		$v['s2']['y'] = ((0 - $v['s2']['xr']) * sin($angle)) + ($v['s2']['yr'] * cos($angle));
		$v['s2']['a1'] = atan2($v['y1'] - $v['s2']['y'], $v['x1'] - $v['s2']['x']);
		$v['s2']['a2'] = atan2($v['y2'] - $v['s2']['y'], $v['x2'] - $v['s2']['x']);

		if ($v['s2']['a2'] < $v['s2']['a1']) {
			$v['s2']['a1'] -= 2 * M_PI;
		}

		if (!$l) {
			if ($s) {
				$xc = $v['s2']['x'];
				$yc = $v['s2']['y'];
				$a1 = $v['s2']['a1'];
				$a2 = $v['s2']['a2'];
				$this->_Arc($xc, $yc, $rx, $ry, $a1, $a2, true, false, $trans);
			}
			else {
				$xc = $v['s1']['x'];
				$yc = $v['s1']['y'];
				$a1 = $v['s1']['a1'];
				$a2 = $v['s1']['a2'];
				$this->_Arc($xc, $yc, $rx, $ry, $a1, $a2, false, false, $trans);
			}
		}
		else if ($s) {
			$xc = $v['s1']['x'];
			$yc = $v['s1']['y'];
			$a1 = $v['s1']['a1'];
			$a2 = $v['s1']['a2'];
			$this->_Arc($xc, $yc, $rx, $ry, $a1, $a2, true, false, $trans);
		}
		else {
			$xc = $v['s2']['x'];
			$yc = $v['s2']['y'];
			$a1 = $v['s2']['a1'];
			$a2 = $v['s2']['a2'];
			$this->_Arc($xc, $yc, $rx, $ry, $a1, $a2, false, false, $trans);
		}
	}

	public function ptTransform(&$x, &$y, $trans = true)
	{
		$nb = count($this->_transf);

		if ($nb) {
			$m = $this->_transf[$nb - 1];
		}
		else {
			$m = array(1, 0, 0, 1, 0, 0);
		}

		list($x, $y) = array(($x * $m[0]) + ($y * $m[2]) + $m[4], ($x * $m[1]) + ($y * $m[3]) + $m[5]);

		if ($trans) {
			$x = $x * $this->k;
			$y = ($this->h - $y) * $this->k;
		}

		return true;
	}

	public function doTransform($n = NULL)
	{
		$nb = count($this->_transf);

		if ($nb) {
			$m = $this->_transf[$nb - 1];
		}
		else {
			$m = array(1, 0, 0, 1, 0, 0);
		}

		if (!$n) {
			$n = array(1, 0, 0, 1, 0, 0);
		}

		$this->_transf[] = array(($m[0] * $n[0]) + ($m[2] * $n[1]), ($m[1] * $n[0]) + ($m[3] * $n[1]), ($m[0] * $n[2]) + ($m[2] * $n[3]), ($m[1] * $n[2]) + ($m[3] * $n[3]), ($m[0] * $n[4]) + ($m[2] * $n[5]) + $m[4], ($m[1] * $n[4]) + ($m[3] * $n[5]) + $m[5]);
	}

	public function undoTransform()
	{
		array_pop($this->_transf);
	}

	public function myBarcode($code, $type, $x, $y, $w, $h, $labelFontsize, $color)
	{
		$style = array('position' => 'S', 'text' => $labelFontsize ? true : false, 'fgcolor' => $color, 'bgcolor' => false);
		$this->write1DBarcode($code, $type, $x, $y, $w, $h, '', $style, 'N');

		if ($labelFontsize) {
			$h += $labelFontsize;
		}

		return array($w, $h);
	}

	public function createIndex(&$obj, $titre = 'Index', $sizeTitle = 20, $sizeBookmark = 15, $bookmarkTitle = true, $displayPage = true, $page = NULL, $fontName = 'helvetica')
	{
		if ($bookmarkTitle) {
			$this->Bookmark($titre, 0, -1);
		}

		$this->SetFont($fontName, '', $sizeTitle);
		$this->Cell(0, 5, $titre, 0, 1, 'C');
		$this->SetFont($fontName, '', $sizeBookmark);
		$this->Ln(10);
		$size = sizeof($this->outlines);
		$pageCellSize = $this->GetStringWidth('p. ' . $this->outlines[$size - 1]['p']) + 2;

		for ($i = 0; $i < $size; $i++) {
			if (($this->h - $this->bMargin) <= $this->getY() + $this->FontSize) {
				$obj->_INDEX_NewPage($page);
				$this->SetFont($fontName, '', $sizeBookmark);
			}

			$level = $this->outlines[$i]['l'];

			if (0 < $level) {
				$this->Cell($level * 8);
			}

			$str = $this->outlines[$i]['t'];
			$strsize = $this->GetStringWidth($str);
			$availableSize = $this->w - $this->lMargin - $this->rMargin - $pageCellSize - ($level * 8) - 4;

			while ($availableSize <= $strsize) {
				$str = substr($str, 0, -1);
				$strsize = $this->GetStringWidth($str);
			}

			if ($displayPage) {
				$this->Cell($strsize + 2, $this->FontSize + 2, $str);
				$w = $this->w - $this->lMargin - $this->rMargin - $pageCellSize - ($level * 8) - ($strsize + 2);
				$nb = $w / $this->GetStringWidth('.');
				$dots = str_repeat('.', $nb);
				$this->Cell($w, $this->FontSize + 2, $dots, 0, 0, 'R');
				$this->Cell($pageCellSize, $this->FontSize + 2, 'p. ' . $this->outlines[$i]['p'], 0, 1, 'R');
			}
			else {
				$this->Cell($strsize + 2, $this->FontSize + 2, $str, 0, 1);
			}
		}
	}

	public function getMyAliasNbPages()
	{
		if ($this->_myLastPageGroupNb == 0) {
			return $this->getAliasNbPages();
		}
		else {
			$old = $this->currpagegroup;
			$this->currpagegroup = '{nb' . $this->_myLastPageGroupNb . '}';
			$new = $this->getPageGroupAlias();
			$this->currpagegroup = $old;
			return $new;
		}
	}

	public function getMyNumPage($page = NULL)
	{
		if ($page === NULL) {
			$page = $this->page;
		}

		if ($this->_myLastPageGroupNb == 0) {
			return $page;
		}
		else {
			return $page - $this->_myLastPageGroup;
		}
	}

	public function myStartPageGroup()
	{
		$this->_myLastPageGroup = $this->page - 1;
		$this->_myLastPageGroupNb++;
	}

	public function getMyLastPageGroup()
	{
		return $this->_myLastPageGroup;
	}

	public function setMyLastPageGroup($myLastPageGroup)
	{
		$this->_myLastPageGroup = $myLastPageGroup;
	}

	public function getMyLastPageGroupNb()
	{
		return $this->_myLastPageGroupNb;
	}

	public function setMyLastPageGroupNb($myLastPageGroupNb)
	{
		$this->_myLastPageGroupNb = $myLastPageGroupNb;
	}
}

?>
