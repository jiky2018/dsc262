<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode1D.php';
define('CODE128_A', 1);
define('CODE128_B', 2);
define('CODE128_C', 3);
class BCGcode128 extends BCGBarcode1D
{
	const KEYA_FNC3 = 96;
	const KEYA_FNC2 = 97;
	const KEYA_SHIFT = 98;
	const KEYA_CODEC = 99;
	const KEYA_CODEB = 100;
	const KEYA_FNC4 = 101;
	const KEYA_FNC1 = 102;
	const KEYB_FNC3 = 96;
	const KEYB_FNC2 = 97;
	const KEYB_SHIFT = 98;
	const KEYB_CODEC = 99;
	const KEYB_FNC4 = 100;
	const KEYB_CODEA = 101;
	const KEYB_FNC1 = 102;
	const KEYC_CODEB = 100;
	const KEYC_CODEA = 101;
	const KEYC_FNC1 = 102;
	const KEY_STARTA = 103;
	const KEY_STARTB = 104;
	const KEY_STARTC = 105;
	const KEY_STOP = 106;

	protected $keysA;
	protected $keysB;
	protected $keysC;
	private $starting_text;
	private $indcheck;
	private $data;
	private $lastTable;
	private $tilde;
	private $shift;
	private $latch;
	private $fnc;
	private $METHOD;

	public function __construct($start = NULL)
	{
		parent::__construct();
		$this->keysA = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_';

		for ($i = 0; $i < 32; $i++) {
			$this->keysA .= chr($i);
		}

		$this->keysB = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~' . chr(127);
		$this->keysC = '0123456789';
		$this->code = array('101111', '111011', '111110', '010112', '010211', '020111', '011102', '011201', '021101', '110102', '110201', '120101', '001121', '011021', '011120', '002111', '012011', '012110', '112100', '110021', '110120', '102101', '112001', '201020', '200111', '210011', '210110', '201101', '211001', '211100', '101012', '101210', '121010', '000212', '020012', '020210', '001202', '021002', '021200', '100202', '120002', '120200', '001022', '001220', '021020', '002012', '002210', '022010', '202010', '100220', '120020', '102002', '102200', '102020', '200012', '200210', '220010', '201002', '201200', '221000', '203000', '110300', '320000', '000113', '000311', '010013', '010310', '030011', '030110', '001103', '001301', '011003', '011300', '031001', '031100', '130100', '110003', '302000', '130001', '023000', '000131', '010031', '010130', '003101', '013001', '013100', '300101', '310001', '310100', '101030', '103010', '301010', '000032', '000230', '020030', '003002', '003200', '300002', '300200', '002030', '003020', '200030', '300020', '100301', '100103', '100121', '122000');
		$this->setStart($start);
		$this->setTilde(true);
		$this->latch = array(
	array(NULL, self::KEYA_CODEB, self::KEYA_CODEC),
	array(self::KEYB_CODEA, NULL, self::KEYB_CODEC),
	array(self::KEYC_CODEA, self::KEYC_CODEB, NULL)
	);
		$this->shift = array(
	array(NULL, self::KEYA_SHIFT),
	array(self::KEYB_SHIFT, NULL)
	);
		$this->fnc = array(
	array(self::KEYA_FNC1, self::KEYA_FNC2, self::KEYA_FNC3, self::KEYA_FNC4),
	array(self::KEYB_FNC1, self::KEYB_FNC2, self::KEYB_FNC3, self::KEYB_FNC4),
	array(self::KEYC_FNC1, NULL, NULL, NULL)
	);
		$this->METHOD = array(CODE128_A => 'A', CODE128_B => 'B', CODE128_C => 'C');
	}

	public function setStart($table)
	{
		if (($table !== 'A') && ($table !== 'B') && ($table !== 'C') && ($table !== NULL)) {
			throw new BCGArgumentException('The starting table must be A, B, C or null.', 'table');
		}

		$this->starting_text = $table;
	}

	public function getTilde()
	{
		return $this->tilde;
	}

	public function setTilde($accept)
	{
		$this->tilde = (bool) $accept;
	}

	public function parse($text)
	{
		$this->setStartFromText($text);
		$this->text = '';
		$seq = '';
		$currentMode = $this->starting_text;

		if (!is_array($text)) {
			$seq = $this->getSequence($text, $currentMode);
			$this->text = $text;
		}
		else {
			reset($text);

			while (list($key1, $val1) = each($text)) {
				if (!is_array($val1)) {
					if (is_string($val1)) {
						$seq .= $this->getSequence($val1, $currentMode);
						$this->text .= $val1;
					}
					else {
						list($key2, $val2) = each($text);
						$seq .= $this->{'setParse' . $this->METHOD[$val1]}($val2, $currentMode);
						$this->text .= $val2;
					}
				}
				else {
					$value = (isset($val1[1]) ? $val1[1] : '');
					$seq .= $this->{'setParse' . $this->METHOD[$val1[0]]}($value, $currentMode);
					$this->text .= $value;
				}
			}
		}

		if ($seq !== '') {
			$bitstream = $this->createBinaryStream($this->text, $seq);
			$this->setData($bitstream);
		}

		$this->addDefaultLabel();
	}

	public function draw($im)
	{
		$c = count($this->data);

		for ($i = 0; $i < $c; $i++) {
			$this->drawChar($im, $this->data[$i], true);
		}

		$this->drawChar($im, '1', true);
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}

	public function getDimension($w, $h)
	{
		$textlength = count($this->data) * 11;
		$endlength = 2;
		$w += $textlength + $endlength;
		$h += $this->thickness;
		return parent::getDimension($w, $h);
	}

	protected function validate()
	{
		$c = count($this->data);

		if ($c === 0) {
			throw new BCGParseException('code128', 'No data has been entered.');
		}

		parent::validate();
	}

	protected function calculateChecksum()
	{
		$this->checksumValue = $this->indcheck[0];
		$c = count($this->indcheck);

		for ($i = 1; $i < $c; $i++) {
			$this->checksumValue += $this->indcheck[$i] * $i;
		}

		$this->checksumValue = $this->checksumValue % 103;
	}

	protected function processChecksum()
	{
		if ($this->checksumValue === false) {
			$this->calculateChecksum();
		}

		if ($this->checksumValue !== false) {
			if ($this->lastTable === 'C') {
				return (string) $this->checksumValue;
			}

			return $this->{'keys' . $this->lastTable}[$this->checksumValue];
		}

		return false;
	}

	private function setStartFromText($text)
	{
		if ($this->starting_text === NULL) {
			if (is_array($text)) {
				if (is_array($text[0])) {
					$this->starting_text = $this->METHOD[$text[0][0]];
					return NULL;
				}
				else if (is_string($text[0])) {
					$text = $text[0];
				}
				else {
					$this->starting_text = $this->METHOD[$text[0]];
					return NULL;
				}
			}

			$tmp = preg_quote($this->keysC, '/');
			$length = strlen($text);
			if ((4 <= $length) && preg_match('/[' . $tmp . ']/', substr($text, 0, 4))) {
				$this->starting_text = 'C';
			}
			else {
				if ((0 < $length) && (strpos($this->keysB, $text[0]) !== false)) {
					$this->starting_text = 'B';
				}
				else {
					$this->starting_text = 'A';
				}
			}
		}
	}

	static private function extractTilde($text, $pos)
	{
		if ($text[$pos] === '~') {
			if (isset($text[$pos + 1])) {
				if ($text[$pos + 1] === '~') {
					return '~~';
				}
				else if ($text[$pos + 1] === 'F') {
					if (isset($text[$pos + 2])) {
						$v = intval($text[$pos + 2]);
						if ((1 <= $v) && ($v <= 4)) {
							return '~F' . $v;
						}
						else {
							throw new BCGParseException('code128', 'Bad ~F. You must provide a number from 1 to 4.');
						}
					}
					else {
						throw new BCGParseException('code128', 'Bad ~F. You must provide a number from 1 to 4.');
					}
				}
				else {
					throw new BCGParseException('code128', 'Wrong code after the ~.');
				}
			}
			else {
				throw new BCGParseException('code128', 'Wrong code after the ~.');
			}
		}
		else {
			throw new BCGParseException('code128', 'There is no ~ at this location.');
		}
	}

	private function getSequenceParsed($text, $currentMode)
	{
		if ($this->tilde) {
			$sequence = '';
			$previousPos = 0;

			while (($pos = strpos($text, '~', $previousPos)) !== false) {
				$tildeData = self::extractTilde($text, $pos);
				$simpleTilde = $tildeData === '~~';
				if ($simpleTilde && ($currentMode !== 'B')) {
					throw new BCGParseException('code128', 'The Table ' . $currentMode . ' doesn\'t contain the character ~.');
				}

				if (($tildeData !== '~F1') && ($currentMode === 'C')) {
					throw new BCGParseException('code128', 'The Table C doesn\'t contain the function ' . $tildeData . '.');
				}

				$length = $pos - $previousPos;

				if ($currentMode === 'C') {
					if (($length % 2) === 1) {
						throw new BCGParseException('code128', 'The text "' . $text . '" must have an even number of character to be encoded in Table C.');
					}
				}

				$sequence .= str_repeat('.', $length);
				$sequence .= '.';
				$sequence .= (!$simpleTilde ? 'F' : '');
				$previousPos = $pos + strlen($tildeData);
			}

			$length = strlen($text) - $previousPos;

			if ($currentMode === 'C') {
				if (($length % 2) === 1) {
					throw new BCGParseException('code128', 'The text "' . $text . '" must have an even number of character to be encoded in Table C.');
				}
			}

			$sequence .= str_repeat('.', $length);
			return $sequence;
		}
		else {
			return str_repeat('.', strlen($text));
		}
	}

	private function setParseA($text, &$currentMode)
	{
		$tmp = preg_quote($this->keysA, '/');

		if ($this->tilde) {
			$tmp .= '~';
		}

		$match = array();

		if (preg_match('/[^' . $tmp . ']/', $text, $match) === 1) {
			throw new BCGParseException('code128', 'The text "' . $text . '" can\'t be parsed with the Table A. The character "' . $match[0] . '" is not allowed.');
		}
		else {
			$latch = ($currentMode === 'A' ? '' : '0');
			$currentMode = 'A';
			return $latch . $this->getSequenceParsed($text, $currentMode);
		}
	}

	private function setParseB($text, &$currentMode)
	{
		$tmp = preg_quote($this->keysB, '/');
		$match = array();

		if (preg_match('/[^' . $tmp . ']/', $text, $match) === 1) {
			throw new BCGParseException('code128', 'The text "' . $text . '" can\'t be parsed with the Table B. The character "' . $match[0] . '" is not allowed.');
		}
		else {
			$latch = ($currentMode === 'B' ? '' : '1');
			$currentMode = 'B';
			return $latch . $this->getSequenceParsed($text, $currentMode);
		}
	}

	private function setParseC($text, &$currentMode)
	{
		$tmp = preg_quote($this->keysC, '/');

		if ($this->tilde) {
			$tmp .= '~F';
		}

		$match = array();

		if (preg_match('/[^' . $tmp . ']/', $text, $match) === 1) {
			throw new BCGParseException('code128', 'The text "' . $text . '" can\'t be parsed with the Table C. The character "' . $match[0] . '" is not allowed.');
		}
		else {
			$latch = ($currentMode === 'C' ? '' : '2');
			$currentMode = 'C';
			return $latch . $this->getSequenceParsed($text, $currentMode);
		}
	}

	private function getSequence($text, &$starting_text)
	{
		$e = 10000;
		$latLen = array(
			array(0, 1, 1),
			array(1, 0, 1),
			array(1, 1, 0)
			);
		$shftLen = array(
			array($e, 1, $e),
			array(1, $e, $e),
			array($e, $e, $e)
			);
		$charSiz = array(2, 2, 1);
		$startA = $e;
		$startB = $e;
		$startC = $e;

		if ($starting_text === 'A') {
			$startA = 0;
		}

		if ($starting_text === 'B') {
			$startB = 0;
		}

		if ($starting_text === 'C') {
			$startC = 0;
		}

		$curLen = array($startA, $startB, $startC);
		$curSeq = array(NULL, NULL, NULL);
		$nextNumber = false;
		$x = 0;
		$xLen = strlen($text);

		for ($x = 0; $x < $xLen; $x++) {
			$input = $text[$x];

			for ($i = 0; $i < 3; $i++) {
				for ($j = 0; $j < 3; $j++) {
					if (($curLen[$i] + $latLen[$i][$j]) < $curLen[$j]) {
						$curLen[$j] = $curLen[$i] + $latLen[$i][$j];
						$curSeq[$j] = $curSeq[$i] . $j;
					}
				}
			}

			$nxtLen = array($e, $e, $e);
			$nxtSeq = array();
			$flag = false;
			$posArray = array();
			if ($this->tilde && ($input === '~')) {
				$tildeData = self::extractTilde($text, $x);

				if ($tildeData === '~~') {
					$posArray[] = 1;
					$x++;
				}
				else if (substr($tildeData, 0, 2) === '~F') {
					$v = intval($tildeData[2]);
					$posArray[] = 0;
					$posArray[] = 1;

					if ($v === 1) {
						$posArray[] = 2;
					}

					$x += 2;
					$flag = true;
				}
			}
			else {
				$pos = strpos($this->keysA, $input);

				if ($pos !== false) {
					$posArray[] = 0;
				}

				$pos = strpos($this->keysB, $input);

				if ($pos !== false) {
					$posArray[] = 1;
				}

				$pos = strpos($this->keysC, $input);
				if ($nextNumber || (($pos !== false) && isset($text[$x + 1]) && (strpos($this->keysC, $text[$x + 1]) !== false))) {
					$nextNumber = !$nextNumber;
					$posArray[] = 2;
				}
			}

			$c = count($posArray);

			for ($i = 0; $i < $c; $i++) {
				if (($curLen[$posArray[$i]] + $charSiz[$posArray[$i]]) < $nxtLen[$posArray[$i]]) {
					$nxtLen[$posArray[$i]] = $curLen[$posArray[$i]] + $charSiz[$posArray[$i]];
					$nxtSeq[$posArray[$i]] = $curSeq[$posArray[$i]] . '.';
				}

				for ($j = 0; $j < 2; $j++) {
					if ($j === $posArray[$i]) {
						continue;
					}

					if (($curLen[$j] + $shftLen[$j][$posArray[$i]] + $charSiz[$posArray[$i]]) < $nxtLen[$j]) {
						$nxtLen[$j] = $curLen[$j] + $shftLen[$j][$posArray[$i]] + $charSiz[$posArray[$i]];
						$nxtSeq[$j] = $curSeq[$j] . chr($posArray[$i] + 65) . '.';
					}
				}
			}

			if ($c === 0) {
				throw new BCGParseException('code128', 'Character ' . $input . ' not supported.');
			}

			if ($flag) {
				for ($i = 0; $i < 5; $i++) {
					if (isset($nxtSeq[$i])) {
						$nxtSeq[$i] .= 'F';
					}
				}
			}

			for ($i = 0; $i < 3; $i++) {
				$curLen[$i] = $nxtLen[$i];

				if (isset($nxtSeq[$i])) {
					$curSeq[$i] = $nxtSeq[$i];
				}
			}
		}

		$m = $e;
		$k = -1;

		for ($i = 0; $i < 3; $i++) {
			if ($curLen[$i] < $m) {
				$k = $i;
				$m = $curLen[$i];
			}
		}

		if ($k === -1) {
			return '';
		}

		return $curSeq[$k];
	}

	private function createBinaryStream($text, $seq)
	{
		$c = strlen($seq);
		$data = array();
		$indcheck = array();
		$currentEncoding = 0;

		if ($this->starting_text === 'A') {
			$currentEncoding = 0;
			$indcheck[] = self::KEY_STARTA;
			$this->lastTable = 'A';
		}
		else if ($this->starting_text === 'B') {
			$currentEncoding = 1;
			$indcheck[] = self::KEY_STARTB;
			$this->lastTable = 'B';
		}
		else if ($this->starting_text === 'C') {
			$currentEncoding = 2;
			$indcheck[] = self::KEY_STARTC;
			$this->lastTable = 'C';
		}

		$data[] = $this->code[103 + $currentEncoding];
		$temporaryEncoding = -1;
		$i = 0;

		for ($counter = 0; $i < $c; $i++) {
			$input = $seq[$i];
			$inputI = intval($input);

			if ($input === '.') {
				$this->encodeChar($data, $currentEncoding, $seq, $text, $i, $counter, $indcheck);

				if ($temporaryEncoding !== -1) {
					$currentEncoding = $temporaryEncoding;
					$temporaryEncoding = -1;
				}
			}
			else {
				if (('A' <= $input) && ($input <= 'B')) {
					$encoding = ord($input) - 65;
					$shift = $this->shift[$currentEncoding][$encoding];
					$indcheck[] = $shift;
					$data[] = $this->code[$shift];

					if ($temporaryEncoding === -1) {
						$temporaryEncoding = $currentEncoding;
					}

					$currentEncoding = $encoding;
				}
				else {
					if ((0 <= $inputI) && ($inputI < 3)) {
						$temporaryEncoding = -1;
						$latch = $this->latch[$currentEncoding][$inputI];

						if ($latch !== NULL) {
							$indcheck[] = $latch;
							$this->lastTable = chr(65 + $inputI);
							$data[] = $this->code[$latch];
							$currentEncoding = $inputI;
						}
					}
				}
			}
		}

		return array($indcheck, $data);
	}

	private function encodeChar(&$data, $encoding, $seq, $text, &$i, &$counter, &$indcheck)
	{
		if (isset($seq[$i + 1]) && ($seq[$i + 1] === 'F')) {
			if ($text[$counter + 1] === 'F') {
				$number = $text[$counter + 2];
				$fnc = $this->fnc[$encoding][$number - 1];
				$indcheck[] = $fnc;
				$data[] = $this->code[$fnc];
				$counter += 2;
			}

			$i++;
		}
		else if ($encoding === 2) {
			$code = (int) substr($text, $counter, 2);
			$indcheck[] = $code;
			$data[] = $this->code[$code];
			$counter++;
			$i++;
		}
		else {
			$keys = ($encoding === 0 ? $this->keysA : $this->keysB);
			$pos = strpos($keys, $text[$counter]);
			$indcheck[] = $pos;
			$data[] = $this->code[$pos];
		}

		$counter++;
	}

	private function setData($data)
	{
		$this->indcheck = $data[0];
		$this->data = $data[1];
		$this->calculateChecksum();
		$this->data[] = $this->code[$this->checksumValue];
		$this->data[] = $this->code[self::KEY_STOP];
	}
}

?>
