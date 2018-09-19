<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode1D.php';
class BCGcode11 extends BCGBarcode1D
{
	public function __construct()
	{
		parent::__construct();
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-');
		$this->code = array('000010', '100010', '010010', '110000', '001010', '101000', '011000', '000110', '100100', '100000', '001000');
	}

	public function draw($im)
	{
		$this->drawChar($im, '001100', true);
		$c = strlen($this->text);

		for ($i = 0; $i < $c; $i++) {
			$this->drawChar($im, $this->findCode($this->text[$i]), true);
		}

		$this->calculateChecksum();
		$c = count($this->checksumValue);

		for ($i = 0; $i < $c; $i++) {
			$this->drawChar($im, $this->code[$this->checksumValue[$i]], true);
		}

		$this->drawChar($im, '00110', true);
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}

	public function getDimension($w, $h)
	{
		$startlength = 8;
		$textlength = 0;
		$c = strlen($this->text);

		for ($i = 0; $i < $c; $i++) {
			$textlength += $this->getIndexLength($this->findIndex($this->text[$i]));
		}

		$checksumlength = 0;
		$this->calculateChecksum();
		$c = count($this->checksumValue);

		for ($i = 0; $i < $c; $i++) {
			$checksumlength += $this->getIndexLength($this->checksumValue[$i]);
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
			throw new BCGParseException('code11', 'No data has been entered.');
		}

		for ($i = 0; $i < $c; $i++) {
			if (array_search($this->text[$i], $this->keys) === false) {
				throw new BCGParseException('code11', 'The character \'' . $this->text[$i] . '\' is not allowed.');
			}
		}

		parent::validate();
	}

	protected function calculateChecksum()
	{
		$sequence_multiplier = array(10, 9);
		$temp_text = $this->text;
		$this->checksumValue = array();

		for ($z = 0; $z < 2; $z++) {
			$c = strlen($temp_text);
			if (($c <= 10) && ($z === 1)) {
				break;
			}

			$checksum = 0;
			$i = $c;

			for ($j = 0; 0 < $i; $i--, $j++) {
				$multiplier = $i % $sequence_multiplier[$z];

				if ($multiplier === 0) {
					$multiplier = $sequence_multiplier[$z];
				}

				$checksum += $this->findIndex($temp_text[$j]) * $multiplier;
			}

			$this->checksumValue[$z] = $checksum % 11;
			$temp_text .= $this->keys[$this->checksumValue[$z]];
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

	private function getIndexLength($index)
	{
		$length = 0;

		if ($index !== false) {
			$length += 6;
			$length += substr_count($this->code[$index], '1');
		}

		return $length;
	}
}

?>
