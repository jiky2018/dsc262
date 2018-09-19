<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGcode39.barcode.php';
class BCGcode39extended extends BCGcode39
{
	const EXTENDED_1 = 39;
	const EXTENDED_2 = 40;
	const EXTENDED_3 = 41;
	const EXTENDED_4 = 42;

	protected $indcheck;
	protected $data;

	public function __construct()
	{
		parent::__construct();
		$this->keys[self::EXTENDED_1] = '($)';
		$this->keys[self::EXTENDED_2] = '(/)';
		$this->keys[self::EXTENDED_3] = '(+)';
		$this->keys[self::EXTENDED_4] = '(%)';
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
					throw new BCGParseException('code39extended', 'The character \'' . $this->text[$i] . '\' is not allowed.');
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

		if ($this->checksum === true) {
			$this->drawChar($im, $this->code[$this->checksumValue % 43], true);
		}

		$this->drawChar($im, $this->code[$this->ending], true);
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}

	public function getDimension($w, $h)
	{
		$textlength = 13 * count($this->data);
		$startlength = 13;
		$checksumlength = 0;

		if ($this->checksum === true) {
			$checksumlength = 13;
		}

		$endlength = 13;
		$w += $startlength + $textlength + $checksumlength + $endlength;
		$h += $this->thickness;
		return BCGBarcode1D::getDimension($w, $h);
	}

	protected function validate()
	{
		$c = count($this->data);

		if ($c === 0) {
			throw new BCGParseException('code39extended', 'No data has been entered.');
		}

		parent::validate();
	}

	protected function calculateChecksum()
	{
		$this->checksumValue = 0;
		$c = count($this->indcheck);

		for ($i = 0; $i < $c; $i++) {
			$this->checksumValue += $this->indcheck[$i];
		}

		$this->checksumValue = $this->checksumValue % 43;
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
