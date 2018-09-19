<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode1D.php';
class BCGcode39 extends BCGBarcode1D
{
	protected $starting;
	protected $ending;
	protected $checksum;

	public function __construct()
	{
		parent::__construct();
		$this->starting = $this->ending = 43;
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%', '*');
		$this->code = array('0001101000', '1001000010', '0011000010', '1011000000', '0001100010', '1001100000', '0011100000', '0001001010', '1001001000', '0011001000', '1000010010', '0010010010', '1010010000', '0000110010', '1000110000', '0010110000', '0000011010', '1000011000', '0010011000', '0000111000', '1000000110', '0010000110', '1010000100', '0000100110', '1000100100', '0010100100', '0000001110', '1000001100', '0010001100', '0000101100', '1100000010', '0110000010', '1110000000', '0100100010', '1100100000', '0110100000', '0100001010', '1100001000', '0110001000', '0101010000', '0101000100', '0100010100', '0001010100', '0100101000');
		$this->setChecksum(false);
	}

	public function setChecksum($checksum)
	{
		$this->checksum = (bool) $checksum;
	}

	public function parse($text)
	{
		parent::parse(strtoupper($text));
	}

	public function draw($im)
	{
		$this->drawChar($im, $this->code[$this->starting], true);
		$c = strlen($this->text);

		for ($i = 0; $i < $c; $i++) {
			$this->drawChar($im, $this->findCode($this->text[$i]), true);
		}

		if ($this->checksum === true) {
			$this->calculateChecksum();
			$this->drawChar($im, $this->code[$this->checksumValue % 43], true);
		}

		$this->drawChar($im, $this->code[$this->ending], true);
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}

	public function getDimension($w, $h)
	{
		$textlength = 13 * strlen($this->text);
		$startlength = 13;
		$checksumlength = 0;

		if ($this->checksum === true) {
			$checksumlength = 13;
		}

		$endlength = 13;
		$w += $startlength + $textlength + $checksumlength + $endlength;
		$h += $this->thickness;
		return parent::getDimension($w, $h);
	}

	protected function validate()
	{
		$c = strlen($this->text);

		if ($c === 0) {
			throw new BCGParseException('code39', 'No data has been entered.');
		}

		for ($i = 0; $i < $c; $i++) {
			if (array_search($this->text[$i], $this->keys) === false) {
				throw new BCGParseException('code39', 'The character \'' . $this->text[$i] . '\' is not allowed.');
			}
		}

		if (strpos($this->text, '*') !== false) {
			throw new BCGParseException('code39', 'The character \'*\' is not allowed.');
		}

		parent::validate();
	}

	protected function calculateChecksum()
	{
		$this->checksumValue = 0;
		$c = strlen($this->text);

		for ($i = 0; $i < $c; $i++) {
			$this->checksumValue += $this->findIndex($this->text[$i]);
		}

		$this->checksumValue = $this->checksumValue % 43;
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
