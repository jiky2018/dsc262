<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode1D.php';
class BCGpostnet extends BCGBarcode1D
{
	public function __construct()
	{
		parent::__construct();
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$this->code = array('11000', '00011', '00101', '00110', '01001', '01010', '01100', '10001', '10010', '10100');
		$this->setThickness(9);
	}

	public function draw($im)
	{
		$checksum = 0;
		$c = strlen($this->text);

		for ($i = 0; $i < $c; $i++) {
			$checksum += intval($this->text[$i]);
		}

		$checksum = 10 - ($checksum % 10);
		$this->drawChar($im, '1');

		for ($i = 0; $i < $c; $i++) {
			$this->drawChar($im, $this->findCode($this->text[$i]));
		}

		$this->drawChar($im, $this->findCode($checksum));
		$this->drawChar($im, '1');
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}

	public function getDimension($w, $h)
	{
		$c = strlen($this->text);
		$startlength = 3;
		$textlength = $c * 5 * 3;
		$checksumlength = 5 * 3;
		$endlength = 3;
		$removelength = -1.5600000000000001;
		$w += $startlength + $textlength + $checksumlength + $endlength + $removelength;
		$h += $this->thickness;
		return parent::getDimension($w, $h);
	}

	protected function validate()
	{
		$c = strlen($this->text);

		if ($c === 0) {
			throw new BCGParseException('postnet', 'No data has been entered.');
		}

		for ($i = 0; $i < $c; $i++) {
			if (array_search($this->text[$i], $this->keys) === false) {
				throw new BCGParseException('postnet', 'The character \'' . $this->text[$i] . '\' is not allowed.');
			}
		}

		if (($c !== 5) && ($c !== 9) && ($c !== 11)) {
			throw new BCGParseException('postnet', 'Must contain 5, 9, or 11 characters.');
		}

		parent::validate();
	}

	protected function drawChar($im, $code, $startBar = true)
	{
		$c = strlen($code);

		for ($i = 0; $i < $c; $i++) {
			if ($code[$i] === '0') {
				$posY = $this->thickness - ($this->thickness / 2.5);
			}
			else {
				$posY = 0;
			}

			$this->drawFilledRectangle($im, $this->positionX, $posY, $this->positionX + 0.44, $this->thickness - 1, BCGBarcode::COLOR_FG);
			$this->positionX += 3;
		}
	}
}

?>
