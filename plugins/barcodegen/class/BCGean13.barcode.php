<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode.php';
include_once 'BCGBarcode1D.php';
include_once 'BCGLabel.php';
class BCGean13 extends BCGBarcode1D
{
	protected $codeParity = array();
	protected $labelLeft;
	protected $labelCenter1;
	protected $labelCenter2;
	protected $alignLabel;

	public function __construct()
	{
		parent::__construct();
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$this->code = array('2100', '1110', '1011', '0300', '0021', '0120', '0003', '0201', '0102', '2001');
		$this->codeParity = array(
	array(0, 0, 0, 0, 0),
	array(0, 1, 0, 1, 1),
	array(0, 1, 1, 0, 1),
	array(0, 1, 1, 1, 0),
	array(1, 0, 0, 1, 1),
	array(1, 1, 0, 0, 1),
	array(1, 1, 1, 0, 0),
	array(1, 0, 1, 0, 1),
	array(1, 0, 1, 1, 0),
	array(1, 1, 0, 1, 0)
	);
		$this->alignDefaultLabel(true);
	}

	public function alignDefaultLabel($align)
	{
		$this->alignLabel = (bool) $align;
	}

	public function draw($im)
	{
		$this->drawBars($im);
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);

		if ($this->isDefaultEanLabelEnabled()) {
			$dimension = $this->labelCenter1->getDimension();
			$this->drawExtendedBars($im, $dimension[1] - 2);
		}
	}

	public function getDimension($w, $h)
	{
		$startlength = 3;
		$centerlength = 5;
		$textlength = 12 * 7;
		$endlength = 3;
		$w += $startlength + $centerlength + $textlength + $endlength;
		$h += $this->thickness;
		return parent::getDimension($w, $h);
	}

	protected function addDefaultLabel()
	{
		if ($this->isDefaultEanLabelEnabled()) {
			$this->processChecksum();
			$label = $this->getLabel();
			$font = $this->font;
			$this->labelLeft = new BCGLabel(substr($label, 0, 1), $font, BCGLabel::POSITION_LEFT, BCGLabel::ALIGN_BOTTOM);
			$this->labelLeft->setSpacing(4 * $this->scale);
			$this->labelCenter1 = new BCGLabel(substr($label, 1, 6), $font, BCGLabel::POSITION_BOTTOM, BCGLabel::ALIGN_LEFT);
			$labelCenter1Dimension = $this->labelCenter1->getDimension();
			$this->labelCenter1->setOffset(((($this->scale * 44) - $labelCenter1Dimension[0]) / 2) + ($this->scale * 2));
			$this->labelCenter2 = new BCGLabel(substr($label, 7, 5) . $this->keys[$this->checksumValue], $font, BCGLabel::POSITION_BOTTOM, BCGLabel::ALIGN_LEFT);
			$this->labelCenter2->setOffset(((($this->scale * 44) - $labelCenter1Dimension[0]) / 2) + ($this->scale * 48));

			if ($this->alignLabel) {
				$labelDimension = $this->labelCenter1->getDimension();
				$this->labelLeft->setOffset($labelDimension[1]);
			}
			else {
				$labelDimension = $this->labelLeft->getDimension();
				$this->labelLeft->setOffset($labelDimension[1] / 2);
			}

			$this->addLabel($this->labelLeft);
			$this->addLabel($this->labelCenter1);
			$this->addLabel($this->labelCenter2);
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
			throw new BCGParseException('ean13', 'No data has been entered.');
		}

		$this->checkCharsAllowed();
		$this->checkCorrectLength();
		parent::validate();
	}

	protected function checkCharsAllowed()
	{
		$c = strlen($this->text);

		for ($i = 0; $i < $c; $i++) {
			if (array_search($this->text[$i], $this->keys) === false) {
				throw new BCGParseException('ean13', 'The character \'' . $this->text[$i] . '\' is not allowed.');
			}
		}
	}

	protected function checkCorrectLength()
	{
		$c = strlen($this->text);

		if ($c === 13) {
			$this->text = substr($this->text, 0, 12);
		}
		else if ($c !== 12) {
			throw new BCGParseException('ean13', 'Must contain 12 digits, the 13th digit is automatically added.');
		}
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

	protected function drawBars($im)
	{
		$this->calculateChecksum();
		$temp_text = $this->text . $this->keys[$this->checksumValue];
		$this->drawChar($im, '000', true);
		$this->drawChar($im, $this->findCode($temp_text[1]), false);

		for ($i = 0; $i < 5; $i++) {
			$this->drawChar($im, self::inverse($this->findCode($temp_text[$i + 2]), $this->codeParity[(int) $temp_text[0]][$i]), false);
		}

		$this->drawChar($im, '00000', false);

		for ($i = 7; $i < 13; $i++) {
			$this->drawChar($im, $this->findCode($temp_text[$i]), true);
		}

		$this->drawChar($im, '000', true);
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
		$this->positionX += 44;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 2;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 44;
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
