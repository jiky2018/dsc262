<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGArgumentException.php';
include_once 'BCGBarcode1D.php';
class BCGmsi extends BCGBarcode1D
{
	private $checksum;

	public function __construct()
	{
		parent::__construct();
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$this->code = array('01010101', '01010110', '01011001', '01011010', '01100101', '01100110', '01101001', '01101010', '10010101', '10010110');
		$this->setChecksum(0);
	}

	public function setChecksum($checksum)
	{
		$checksum = intval($checksum);
		if (($checksum < 0) && (2 < $checksum)) {
			throw new BCGArgumentException('The checksum must be between 0 and 2 included.', 'checksum');
		}

		$this->checksum = $checksum;
	}

	public function draw($im)
	{
		$this->calculateChecksum();
		$this->drawChar($im, '10', true);
		$c = strlen($this->text);

		for ($i = 0; $i < $c; $i++) {
			$this->drawChar($im, $this->findCode($this->text[$i]), true);
		}

		$c = count($this->checksumValue);

		for ($i = 0; $i < $c; $i++) {
			$this->drawChar($im, $this->findCode($this->checksumValue[$i]), true);
		}

		$this->drawChar($im, '010', true);
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}

	public function getDimension($w, $h)
	{
		$textlength = 12 * strlen($this->text);
		$startlength = 3;
		$checksumlength = $this->checksum * 12;
		$endlength = 4;
		$w += $startlength + $textlength + $checksumlength + $endlength;
		$h += $this->thickness;
		return parent::getDimension($w, $h);
	}

	protected function validate()
	{
		$c = strlen($this->text);

		if ($c === 0) {
			throw new BCGParseException('msi', 'No data has been entered.');
		}

		for ($i = 0; $i < $c; $i++) {
			if (array_search($this->text[$i], $this->keys) === false) {
				throw new BCGParseException('msi', 'The character \'' . $this->text[$i] . '\' is not allowed.');
			}
		}
	}

	protected function calculateChecksum()
	{
		$last_text = $this->text;
		$this->checksumValue = array();

		for ($i = 0; $i < $this->checksum; $i++) {
			$new_text = '';
			$new_number = 0;
			$c = strlen($last_text);

			if (($c % 2) === 0) {
				$starting = 1;
			}
			else {
				$starting = 0;
			}

			for ($j = $starting; $j < $c; $j += 2) {
				$new_text .= $last_text[$j];
			}

			$new_text = strval(intval($new_text) * 2);
			$c2 = strlen($new_text);

			for ($j = 0; $j < $c2; $j++) {
				$new_number += intval($new_text[$j]);
			}

			for ($j = ($starting === 0 ? 1 : 0); $j < $c; $j += 2) {
				$new_number += intval($last_text[$j]);
			}

			$new_number = (10 - ($new_number % 10)) % 10;
			$this->checksumValue[] = $new_number;
			$last_text .= $new_number;
		}
	}

	protected function processChecksum()
	{
		if ($this->checksumValue === false) {
			$this->calculateChecksum();
		}

		if ($this->checksumValue !== false) {
			$ret = '';
			$c = count($this->checksumValue);

			for ($i = 0; $i < $c; $i++) {
				$ret .= $this->keys[$this->checksumValue[$i]];
			}

			return $ret;
		}

		return false;
	}
}

?>
