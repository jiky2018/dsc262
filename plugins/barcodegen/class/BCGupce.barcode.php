<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode.php';
include_once 'BCGBarcode1D.php';
include_once 'BCGLabel.php';
class BCGupce extends BCGBarcode1D
{
	protected $codeParity = array();
	protected $upce;
	protected $labelLeft;
	protected $labelCenter;
	protected $labelRight;

	public function __construct()
	{
		parent::__construct();
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$this->code = array('2100', '1110', '1011', '0300', '0021', '0120', '0003', '0201', '0102', '2001');
		$this->codeParity = array(
	array(
		array(1, 1, 1, 0, 0, 0),
		array(1, 1, 0, 1, 0, 0),
		array(1, 1, 0, 0, 1, 0),
		array(1, 1, 0, 0, 0, 1),
		array(1, 0, 1, 1, 0, 0),
		array(1, 0, 0, 1, 1, 0),
		array(1, 0, 0, 0, 1, 1),
		array(1, 0, 1, 0, 1, 0),
		array(1, 0, 1, 0, 0, 1),
		array(1, 0, 0, 1, 0, 1)
		),
	array(
		array(0, 0, 0, 1, 1, 1),
		array(0, 0, 1, 0, 1, 1),
		array(0, 0, 1, 1, 0, 1),
		array(0, 0, 1, 1, 1, 0),
		array(0, 1, 0, 0, 1, 1),
		array(0, 1, 1, 0, 0, 1),
		array(0, 1, 1, 1, 0, 0),
		array(0, 1, 0, 1, 0, 1),
		array(0, 1, 0, 1, 1, 0),
		array(0, 1, 1, 0, 1, 0)
		)
	);
	}

	public function draw($im)
	{
		$this->calculateChecksum();
		$this->drawChar($im, '000', true);
		$c = strlen($this->upce);

		for ($i = 0; $i < $c; $i++) {
			$this->drawChar($im, self::inverse($this->findCode($this->upce[$i]), $this->codeParity[intval($this->text[0])][$this->checksumValue][$i]), false);
		}

		$this->drawChar($im, '00000', false);
		$this->drawChar($im, '0', true);
		$this->text = $this->text[0] . $this->upce;
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);

		if ($this->isDefaultEanLabelEnabled()) {
			$dimension = $this->labelCenter->getDimension();
			$this->drawExtendedBars($im, $dimension[1] - 2);
		}
	}

	public function getDimension($w, $h)
	{
		$startlength = 3;
		$centerlength = 5;
		$textlength = 6 * 7;
		$endlength = 1;
		$w += $startlength + $centerlength + $textlength + $endlength;
		$h += $this->thickness;
		return parent::getDimension($w, $h);
	}

	protected function addDefaultLabel()
	{
		if ($this->isDefaultEanLabelEnabled()) {
			$this->processChecksum();
			$font = $this->font;
			$this->labelLeft = new BCGLabel(substr($this->text, 0, 1), $font, BCGLabel::POSITION_LEFT, BCGLabel::ALIGN_BOTTOM);
			$labelLeftDimension = $this->labelLeft->getDimension();
			$this->labelLeft->setSpacing(8);
			$this->labelLeft->setOffset($labelLeftDimension[1] / 2);
			$this->labelCenter = new BCGLabel($this->upce, $font, BCGLabel::POSITION_BOTTOM, BCGLabel::ALIGN_LEFT);
			$labelCenterDimension = $this->labelCenter->getDimension();
			$this->labelCenter->setOffset(((($this->scale * 46) - $labelCenterDimension[0]) / 2) + ($this->scale * 2));
			$this->labelRight = new BCGLabel($this->keys[$this->checksumValue], $font, BCGLabel::POSITION_RIGHT, BCGLabel::ALIGN_BOTTOM);
			$labelRightDimension = $this->labelRight->getDimension();
			$this->labelRight->setSpacing(8);
			$this->labelRight->setOffset($labelRightDimension[1] / 2);
			$this->addLabel($this->labelLeft);
			$this->addLabel($this->labelCenter);
			$this->addLabel($this->labelRight);
		}
	}

	protected function isDefaultEanLabelEnabled()
	{
		$label = $this->getLabel();
		$font = $this->font;
		return ($label !== NULL) && ($label !== '') && ($font !== NULL) && ($this->defaultLabel !== NULL);
	}

	protected function validate()
	{
		$c = strlen($this->text);

		if ($c === 0) {
			throw new BCGParseException('upce', 'No data has been entered.');
		}

		for ($i = 0; $i < $c; $i++) {
			if (array_search($this->text[$i], $this->keys) === false) {
				throw new BCGParseException('upce', 'The character \'' . $this->text[$i] . '\' is not allowed.');
			}
		}

		if (($c !== 11) && ($c !== 6)) {
			throw new BCGParseException('upce', 'You must provide a UPC-A (11 characters) or a UPC-E (6 characters).');
		}
		else {
			if (($this->text[0] !== '0') && ($this->text[0] !== '1') && ($c !== 6)) {
				throw new BCGParseException('upce', 'UPC-A must start with 0 or 1 to be converted to UPC-E.');
			}
		}

		$this->upce = '';

		if ($c !== 6) {
			$temp1 = substr($this->text, 3, 3);
			if (($temp1 === '000') || ($temp1 === '100') || ($temp1 === '200')) {
				if (substr($this->text, 6, 2) === '00') {
					$this->upce = substr($this->text, 1, 2) . substr($this->text, 8, 3) . substr($this->text, 3, 1);
				}
			}
			else if (substr($this->text, 4, 2) === '00') {
				if (substr($this->text, 6, 3) === '000') {
					$this->upce = substr($this->text, 1, 3) . substr($this->text, 9, 2) . '3';
				}
			}
			else if (substr($this->text, 5, 1) === '0') {
				if (substr($this->text, 6, 4) === '0000') {
					$this->upce = substr($this->text, 1, 4) . substr($this->text, 10, 1) . '4';
				}
			}
			else {
				$temp2 = intval(substr($this->text, 10, 1));
				if ((substr($this->text, 6, 4) === '0000') && (5 <= $temp2) && ($temp2 <= 9)) {
					$this->upce = substr($this->text, 1, 5) . substr($this->text, 10, 1);
				}
			}
		}
		else {
			$this->upce = $this->text;
		}

		if ($this->upce === '') {
			throw new BCGParseException('upce', 'Your UPC-A can\'t be converted to UPC-E.');
		}

		if ($c === 6) {
			$upca = '';
			if (($this->text[5] === '0') || ($this->text[5] === '1') || ($this->text[5] === '2')) {
				$upca = substr($this->text, 0, 2) . $this->text[5] . '0000' . substr($this->text, 2, 3);
			}
			else if ($this->text[5] === '3') {
				$upca = substr($this->text, 0, 3) . '00000' . substr($this->text, 3, 2);
			}
			else if ($this->text[5] === '4') {
				$upca = substr($this->text, 0, 4) . '00000' . $this->text[4];
			}
			else {
				$upca = substr($this->text, 0, 5) . '0000' . $this->text[5];
			}

			$this->text = '0' . $upca;
		}

		parent::validate();
	}

	protected function calculateChecksum()
	{
		$odd = true;
		$this->checksumValue = 0;
		$c = strlen($this->text);

		for ($i = $c; 0 < $i; $i--) {
			if ($odd === true) {
				$multiplier = 3;
				$odd = false;
			}
			else {
				$multiplier = 1;
				$odd = true;
			}

			if (!isset($this->keys[$this->text[$i - 1]])) {
				return NULL;
			}

			$this->checksumValue += $this->keys[$this->text[$i - 1]] * $multiplier;
		}

		$this->checksumValue = (10 - ($this->checksumValue % 10)) % 10;
	}

	protected function processChecksum()
	{
		if ($this->checksumValue === false) {
			$this->calculateChecksum();
		}

		if ($this->checksumValue !== false) {
			return $this->keys[$this->checksumValue];
		}

		return false;
	}

	protected function drawExtendedBars($im, $plus)
	{
		$rememberX = $this->positionX;
		$rememberH = $this->thickness;
		$this->thickness = $this->thickness + intval($plus / $this->scale);
		$this->positionX = 0;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 2;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 46;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 2;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX = $rememberX;
		$this->thickness = $rememberH;
	}

	static private function inverse($text, $inverse = 1)
	{
		if ($inverse === 1) {
			$text = strrev($text);
		}

		return $text;
	}
}

?>
