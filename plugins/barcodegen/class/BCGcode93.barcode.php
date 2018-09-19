<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode1D.php';
class BCGcode93 extends BCGBarcode1D
{
	const EXTENDED_1 = 43;
	const EXTENDED_2 = 44;
	const EXTENDED_3 = 45;
	const EXTENDED_4 = 46;

	private $starting;
	private $ending;
	private $indcheck;
	private $data;

	public function __construct()
	{
		parent::__construct();
		$this->starting = $this->ending = 47;
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%', '($)', '(%)', '(/)', '(+)', '(*)');
		$this->code = array('020001', '000102', '000201', '000300', '010002', '010101', '010200', '000003', '020100', '030000', '100002', '100101', '100200', '110001', '110100', '120000', '001002', '001101', '001200', '011001', '021000', '000012', '000111', '000210', '010011', '020010', '101001', '101100', '100011', '100110', '110010', '111000', '001011', '001110', '011010', '012000', '010020', '200001', '200100', '210000', '001020', '002010', '100020', '010110', '201000', '200010', '011100', '000030');
	}

	public function parse($text)
	{
		$this->text = $text;
		$data = array();
		$indcheck = array();
		$c = strlen($this->text);

		for ($i = 0; $i < $c; $i++) {
			$pos = array_search($this->text[$i], $this->keys);

			if ($pos === false) {
				$extended = self::getExtendedVersion($this->text[$i]);

				if ($extended === false) {
					throw new BCGParseException('code93', 'The character \'' . $this->text[$i] . '\' is not allowed.');
				}
				else {
					$extc = strlen($extended);

					for ($j = 0; $j < $extc; $j++) {
						$v = $extended[$j];

						if ($v === '$') {
							$indcheck[] = self::EXTENDED_1;
							$data[] = $this->code[self::EXTENDED_1];
						}
						else if ($v === '%') {
							$indcheck[] = self::EXTENDED_2;
							$data[] = $this->code[self::EXTENDED_2];
						}
						else if ($v === '/') {
							$indcheck[] = self::EXTENDED_3;
							$data[] = $this->code[self::EXTENDED_3];
						}
						else if ($v === '+') {
							$indcheck[] = self::EXTENDED_4;
							$data[] = $this->code[self::EXTENDED_4];
						}
						else {
							$pos2 = array_search($v, $this->keys);
							$indcheck[] = $pos2;
							$data[] = $this->code[$pos2];
						}
					}
				}
			}
			else {
				$indcheck[] = $pos;
				$data[] = $this->code[$pos];
			}
		}

		$this->setData(array($indcheck, $data));
		$this->addDefaultLabel();
	}

	public function draw($im)
	{
		$this->drawChar($im, $this->code[$this->starting], true);
		$c = count($this->data);

		for ($i = 0; $i < $c; $i++) {
			$this->drawChar($im, $this->data[$i], true);
		}

		$c = count($this->checksumValue);

		for ($i = 0; $i < $c; $i++) {
			$this->drawChar($im, $this->code[$this->checksumValue[$i]], true);
		}

		$this->drawChar($im, $this->code[$this->ending], true);
		$this->drawChar($im, '0', true);
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}

	public function getDimension($w, $h)
	{
		$startlength = 9;
		$textlength = 9 * count($this->data);
		$checksumlength = 2 * 9;
		$endlength = 9 + 1;
		$w += $startlength + $textlength + $checksumlength + $endlength;
		$h += $this->thickness;
		return parent::getDimension($w, $h);
	}

	protected function validate()
	{
		$c = count($this->data);

		if ($c === 0) {
			throw new BCGParseException('code93', 'No data has been entered.');
		}

		parent::validate();
	}

	protected function calculateChecksum()
	{
		$sequence_multiplier = array(20, 15);
		$this->checksumValue = array();
		$indcheck = $this->indcheck;

		for ($z = 0; $z < 2; $z++) {
			$checksum = 0;
			$i = count($indcheck);

			for ($j = 0; 0 < $i; $i--, $j++) {
				$multiplier = $i % $sequence_multiplier[$z];

				if ($multiplier === 0) {
					$multiplier = $sequence_multiplier[$z];
				}

				$checksum += $indcheck[$j] * $multiplier;
			}

			$this->checksumValue[$z] = $checksum % 47;
			$indcheck[] = $this->checksumValue[$z];
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

	private function setData($data)
	{
		$this->indcheck = $data[0];
		$this->data = $data[1];
		$this->calculateChecksum();
	}

	static private function getExtendedVersion($char)
	{
		$o = ord($char);

		if ($o === 0) {
			return '%U';
		}
		else {
			if ((1 <= $o) && ($o <= 26)) {
				return '$' . chr($o + 64);
			}
			else {
				if (((33 <= $o) && ($o <= 44)) || ($o === 47) || ($o === 48)) {
					return '/' . chr($o + 32);
				}
				else {
					if ((97 <= $o) && ($o <= 122)) {
						return '+' . chr($o - 32);
					}
					else {
						if ((27 <= $o) && ($o <= 31)) {
							return '%' . chr($o + 38);
						}
						else {
							if ((59 <= $o) && ($o <= 63)) {
								return '%' . chr($o + 11);
							}
							else {
								if ((91 <= $o) && ($o <= 95)) {
									return '%' . chr($o - 16);
								}
								else {
									if ((123 <= $o) && ($o <= 127)) {
										return '%' . chr($o - 43);
									}
									else if ($o === 64) {
										return '%V';
									}
									else if ($o === 96) {
										return '%W';
									}
									else if (127 < $o) {
										return false;
									}
									else {
										return $char;
									}
								}
							}
						}
					}
				}
			}
		}
	}
}

?>
