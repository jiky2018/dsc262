<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode1D.php';
include_once 'BCGLabel.php';
class BCGupcext5 extends BCGBarcode1D
{
	protected $codeParity = array();

	public function __construct()
	{
		parent::__construct();
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$this->code = array('2100', '1110', '1011', '0300', '0021', '0120', '0003', '0201', '0102', '2001');
		$this->codeParity = array(
	array(1, 1, 0, 0, 0),
	array(1, 0, 1, 0, 0),
	array(1, 0, 0, 1, 0),
	array(1, 0, 0, 0, 1),
	array(0, 1, 1, 0, 0),
	array(0, 0, 1, 1, 0),
	array(0, 0, 0, 1, 1),
	array(0, 1, 0, 1, 0),
	array(0, 1, 0, 0, 1),
	array(0, 0, 1, 0, 1)
	);
	}

	public function draw($im)
	{
		$this->calculateChecksum();
		$this->drawChar($im, '001', true);

		for ($i = 0; $i < 5; $i++) {
			$this->drawChar($im, self::inverse($this->findCode($this->text[$i]), $this->codeParity[$this->checksumValue][$i]), false);

			if ($i < 4) {
				$this->drawChar($im, '00', false);
			}
		}

		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}

	public function getDimension($w, $h)
	{
		$startlength = 4;
		$textlength = 5 * 7;
		$intercharlength = 2 * 4;
		$w += $startlength + $textlength + $intercharlength;
		$h += $this->thickness;
		return parent::getDimension($w, $h);
	}

	protected function addDefaultLabel()
	{
		parent::addDefaultLabel();

		if ($this->defaultLabel !== NULL) {
			$this->defaultLabel->setPosition(BCGLabel::POSITION_TOP);
		}
	}

	protected function validate()
	{
		$c = strlen($this->text);

		if ($c === 0) {
			throw new BCGParseException('upcext5', 'No data has been entered.');
		}

		for ($i = 0; $i < $c; $i++) {
			if (array_search($this->text[$i], $this->keys) === false) {
				throw new BCGParseException('upcext5', 'The character \'' . $this->text[$i] . '\' is not allowed.');
			}
		}

		if ($c !== 5) {
			throw new BCGParseException('upcext5', 'Must contain 5 digits.');
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
				$multiplier = 9;
				$odd = true;
			}

			if (!isset($this->keys[$this->text[$i - 1]])) {
				return NULL;
			}

			$this->checksumValue += $this->keys[$this->text[$i - 1]] * $multiplier;
		}

		$this->checksumValue = $this->checksumValue % 10;
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

	static private function inverse($text, $inverse = 1)
	{
		if ($inverse === 1) {
			$text = strrev($text);
		}

		return $text;
	}
}

?>
