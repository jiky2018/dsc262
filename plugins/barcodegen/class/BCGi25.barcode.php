<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode1D.php';
class BCGi25 extends BCGBarcode1D
{
	private $checksum;
	private $ratio;

	public function __construct()
	{
		parent::__construct();
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$this->code = array('00110', '10001', '01001', '11000', '00101', '10100', '01100', '00011', '10010', '01010');
		$this->setChecksum(false);
		$this->setRatio(2);
	}

	public function setChecksum($checksum)
	{
		$this->checksum = (bool) $checksum;
	}

	public function setRatio($ratio)
	{
		$this->ratio = $ratio;
	}

	public function draw($im)
	{
		$temp_text = $this->text;

		if ($this->checksum === true) {
			$this->calculateChecksum();
			$temp_text .= $this->keys[$this->checksumValue];
		}

		$this->drawChar($im, '0000', true);
		$c = strlen($temp_text);

		for ($i = 0; $i < $c; $i += 2) {
			$temp_bar = '';
			$c2 = strlen($this->findCode($temp_text[$i]));

			for ($j = 0; $j < $c2; $j++) {
				$temp_bar .= substr($this->findCode($temp_text[$i]), $j, 1);
				$temp_bar .= substr($this->findCode($temp_text[$i + 1]), $j, 1);
			}

			$this->drawChar($im, $this->changeBars($temp_bar), true);
		}

		$this->drawChar($im, $this->changeBars('100'), true);
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}

	public function getDimension($w, $h)
	{
		$textlength = (3 + (($this->ratio + 1) * 2)) * strlen($this->text);
		$startlength = 4;
		$checksumlength = 0;

		if ($this->checksum === true) {
			$checksumlength = 3 + (($this->ratio + 1) * 2);
		}

		$endlength = 2 + $this->ratio + 1;
		$w += $startlength + $textlength + $checksumlength + $endlength;
		$h += $this->thickness;
		return parent::getDimension($w, $h);
	}

	protected function validate()
	{
		$c = strlen($this->text);

		if ($c === 0) {
			throw new BCGParseException('i25', 'No data has been entered.');
		}

		for ($i = 0; $i < $c; $i++) {
			if (array_search($this->text[$i], $this->keys) === false) {
				throw new BCGParseException('i25', 'The character \'' . $this->text[$i] . '\' is not allowed.');
			}
		}

		if ((($c % 2) !== 0) && ($this->checksum === false)) {
			throw new BCGParseException('i25', 'i25 must contain an even amount of digits if checksum is false.');
		}
		else {
			if ((($c % 2) === 0) && ($this->checksum === true)) {
				throw new BCGParseException('i25', 'i25 must contain an odd amount of digits if checksum is true.');
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

	private function changeBars($in)
	{
		if (1 < $this->ratio) {
			$c = strlen($in);

			for ($i = 0; $i < $c; $i++) {
				$in[$i] = $in[$i] === '1' ? $this->ratio : $in[$i];
			}
		}

		return $in;
	}
}

?>
