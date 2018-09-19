<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode.php';
include_once 'BCGBarcode1D.php';
include_once 'BCGLabel.php';
class BCGean8 extends BCGBarcode1D
{
	protected $labelLeft;
	protected $labelRight;

	public function __construct()
	{
		parent::__construct();
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$this->code = array('2100', '1110', '1011', '0300', '0021', '0120', '0003', '0201', '0102', '2001');
	}

	public function draw($im)
	{
		$this->calculateChecksum();
		$temp_text = $this->text . $this->keys[$this->checksumValue];
		$this->drawChar($im, '000', true);

		for ($i = 0; $i < 4; $i++) {
			$this->drawChar($im, $this->findCode($temp_text[$i]), false);
		}

		$this->drawChar($im, '00000', false);

		for ($i = 4; $i < 8; $i++) {
			$this->drawChar($im, $this->findCode($temp_text[$i]), true);
		}

		$this->drawChar($im, '000', true);
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);

		if ($this->isDefaultEanLabelEnabled()) {
			$dimension = $this->labelRight->getDimension();
			$this->drawExtendedBars($im, $dimension[1] - 2);
		}
	}

	public function getDimension($w, $h)
	{
		$startlength = 3;
		$centerlength = 5;
		$textlength = 8 * 7;
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
			$this->labelLeft = new BCGLabel(substr($label, 0, 4), $font, BCGLabel::POSITION_BOTTOM, BCGLabel::ALIGN_LEFT);
			$labelLeftDimension = $this->labelLeft->getDimension();
			$this->labelLeft->setOffset(((($this->scale * 30) - $labelLeftDimension[0]) / 2) + ($this->scale * 2));
			$this->labelRight = new BCGLabel(substr($label, 4, 3) . $this->keys[$this->checksumValue], $font, BCGLabel::POSITION_BOTTOM, BCGLabel::ALIGN_LEFT);
			$labelRightDimension = $this->labelRight->getDimension();
			$this->labelRight->setOffset(((($this->scale * 30) - $labelRightDimension[0]) / 2) + ($this->scale * 34));
			$this->addLabel($this->labelLeft);
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
			throw new BCGParseException('ean8', 'No data has been entered.');
		}

		for ($i = 0; $i < $c; $i++) {
			if (array_search($this->text[$i], $this->keys) === false) {
				throw new BCGParseException('ean8', 'The character \'' . $this->text[$i] . '\' is not allowed.');
			}
		}

		if ($c === 8) {
			$this->text = substr($this->text, 0, 7);
		}
		else if ($c !== 7) {
			throw new BCGParseException('ean8', 'Must contain 7 digits, the 8th digit is automatically added.');
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

	private function drawExtendedBars($im, $plus)
	{
		$rememberX = $this->positionX;
		$rememberH = $this->thickness;
		$this->thickness = $this->thickness + intval($plus / $this->scale);
		$this->positionX = 0;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 2;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 30;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 2;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 30;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 2;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX = $rememberX;
		$this->thickness = $rememberH;
	}
}

?>
