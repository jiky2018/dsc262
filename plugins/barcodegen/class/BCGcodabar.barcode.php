<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode1D.php';
class BCGcodabar extends BCGBarcode1D
{
	public function __construct()
	{
		parent::__construct();
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-', '$', ':', '/', '.', '+', 'A', 'B', 'C', 'D');
		$this->code = array('00000110', '00001100', '00010010', '11000000', '00100100', '10000100', '01000010', '01001000', '01100000', '10010000', '00011000', '00110000', '10001010', '10100010', '10101000', '00111110', '00110100', '01010010', '00010110', '00011100');
	}

	public function parse($text)
	{
		parent::parse(strtoupper($text));
	}

	public function draw($im)
	{
		$c = strlen($this->text);

		for ($i = 0; $i < $c; $i++) {
			$this->drawChar($im, $this->findCode($this->text[$i]), true);
		}

		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}

	public function getDimension($w, $h)
	{
		$textLength = 0;
		$c = strlen($this->text);

		for ($i = 0; $i < $c; $i++) {
			$index = $this->findIndex($this->text[$i]);

			if ($index !== false) {
				$textLength += 8;
				$textLength += substr_count($this->code[$index], '1');
			}
		}

		$w += $textLength;
		$h += $this->thickness;
		return parent::getDimension($w, $h);
	}

	protected function validate()
	{
		$c = strlen($this->text);

		if ($c === 0) {
			throw new BCGParseException('codabar', 'No data has been entered.');
		}

		for ($i = 0; $i < $c; $i++) {
			if (array_search($this->text[$i], $this->keys) === false) {
				throw new BCGParseException('codabar', 'The character \'' . $this->text[$i] . '\' is not allowed.');
			}
		}

		if (($c == 0) || (($this->text[0] !== 'A') && ($this->text[0] !== 'B') && ($this->text[0] !== 'C') && ($this->text[0] !== 'D'))) {
			throw new BCGParseException('codabar', 'The text must start by the character A, B, C, or D.');
		}

		$c2 = $c - 1;
		if (($c2 === 0) || (($this->text[$c2] !== 'A') && ($this->text[$c2] !== 'B') && ($this->text[$c2] !== 'C') && ($this->text[$c2] !== 'D'))) {
			throw new BCGParseException('codabar', 'The text must end by the character A, B, C, or D.');
		}

		parent::validate();
	}
}

?>
