<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode1D.php';
class BCGs25 extends BCGBarcode1D
{
	private $checksum;

	public function __construct()
	{
		parent::__construct();
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$this->code = array('0000202000', '2000000020', '0020000020', '2020000000', '0000200020', '2000200000', '0020200000', '0000002020', '2000002000', '0020002000');
		$this->setChecksum(false);
	}

	public function setChecksum($checksum)
	{
		$this->checksum = (bool) $checksum;
	}

	public function draw($im)
	{
		$temp_text = $this->text;

		if ($this->checksum === true) {
			$this->calculateChecksum();
			$temp_text .= $this->keys[$this->checksumValue];
		}

		$this->drawChar($im, '101000', true);
		$c = strlen($temp_text);

		for ($i = 0; $i < $c; $i++) {
			$this->drawChar($im, $this->findCode($temp_text[$i]), true);
		}

		$this->drawChar($im, '10001', true);
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}

	public function getDimension($w, $h)
	{
		$c = strlen($this->text);
		$startlength = 8;
		$textlength = $c * 14;
		$checksumlength = 0;

		if (($c % 2) !== 0) {
			$checksumlength = 14;
		}

		$endlength = 7;
		$w += $startlength + $textlength + $checksumlength + $endlength;
		$h += $this->thickness;
		return parent::getDimension($w, $h);
	}

	protected function validate()
	{
		$c = strlen($this->text);

		if ($c === 0) {
			throw new BCGParseException('s25', 'No data has been entered.');
		}

		for ($i = 0; $i < $c; $i++) {
			if (array_search($this->text[$i], $this->keys) === false) {
				throw new BCGParseException('s25', 'The character \'' . $this->text[$i] . '\' is not allowed.');
			}
		}

		if ((($c % 2) !== 0) && ($this->checksum === false)) {
			throw new BCGParseException('s25', 's25 must contain an even amount of digits if checksum is false.');
		}
		else {
			if ((($c % 2) === 0) && ($this->checksum === true)) {
				throw new BCGParseException('s25', 's25 must contain an odd amount of digits if checksum is true.');
			}
		}

		parent::validate();
	}

	protected function calculateChecksum()
	{
		$even = true;
		$this->checksumValue = 0;
		$c = strlen($this->text);

		for ($i = $c; 0 < $i; $i--) {
			if ($even === true) {
				$multiplier = 3;
				$even = false;
			}
			else {
				$multiplier = 1;
				$even = true;
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
}

?>
