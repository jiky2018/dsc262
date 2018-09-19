<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode1D.php';
include_once 'BCGLabel.php';
class BCGupcext2 extends BCGBarcode1D
{
	protected $codeParity = array();

	public function __construct()
	{
		parent::__construct();
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$this->code = array('2100', '1110', '1011', '0300', '0021', '0120', '0003', '0201', '0102', '2001');
		$this->codeParity = array(
	array(0, 0),
	array(0, 1),
	array(1, 0),
	array(1, 1)
	);
	}

	public function draw($im)
	{
		$this->drawChar($im, '001', true);

		for ($i = 0; $i < 2; $i++) {
			$this->drawChar($im, self::inverse($this->findCode($this->text[$i]), $this->codeParity[intval($this->text) % 4][$i]), false);

			if ($i === 0) {
				$this->drawChar($im, '00', false);
			}
		}

		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}

	public function getDimension($w, $h)
	{
		$startlength = 4;
		$textlength = 2 * 7;
		$intercharlength = 2;
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
			throw new BCGParseException('upcext2', 'No data has been entered.');
		}

		for ($i = 0; $i < $c; $i++) {
			if (array_search($this->text[$i], $this->keys) === false) {
				throw new BCGParseException('upcext2', 'The character \'' . $this->text[$i] . '\' is not allowed.');
			}
		}

		if ($c !== 2) {
			throw new BCGParseException('upcext2', 'Must contain 2 digits.');
		}

		parent::validate();
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
