<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGArgumentException.php';
include_once 'BCGean13.barcode.php';
class BCGisbn extends BCGean13
{
	const GS1_AUTO = 0;
	const GS1_PREFIX978 = 1;
	const GS1_PREFIX979 = 2;

	private $gs1;

	public function __construct($gs1 = self::GS1_AUTO)
	{
		parent::__construct();
		$this->setGS1($gs1);
	}

	protected function addDefaultLabel()
	{
		if ($this->isDefaultEanLabelEnabled()) {
			$isbn = $this->createISBNText();
			$font = $this->font;
			$topLabel = new BCGLabel($isbn, $font, BCGLabel::POSITION_TOP, BCGLabel::ALIGN_CENTER);
			$this->addLabel($topLabel);
		}

		parent::addDefaultLabel();
	}

	public function setGS1($gs1)
	{
		$gs1 = (int) $gs1;
		if (($gs1 !== self::GS1_AUTO) && ($gs1 !== self::GS1_PREFIX978) && ($gs1 !== self::GS1_PREFIX979)) {
			throw new BCGArgumentException('The GS1 argument must be BCGisbn::GS1_AUTO, BCGisbn::GS1_PREFIX978, or BCGisbn::GS1_PREFIX979', 'gs1');
		}

		$this->gs1 = $gs1;
	}

	protected function checkCharsAllowed()
	{
		$c = strlen($this->text);

		if ($c === 10) {
			if ((array_search($this->text[9], $this->keys) === false) && ($this->text[9] !== 'X')) {
				throw new BCGParseException('isbn', 'The character \'' . $this->text[9] . '\' is not allowed.');
			}

			$this->text = substr($this->text, 0, 9);
		}

		return parent::checkCharsAllowed();
	}

	protected function checkCorrectLength()
	{
		$c = strlen($this->text);

		if ($c === 13) {
			$this->text = substr($this->text, 0, 12);
		}
		else {
			if (($c === 9) || ($c === 10)) {
				if ($c === 10) {
					if ((array_search($this->text[9], $this->keys) === false) && ($this->text[9] !== 'X')) {
						throw new BCGParseException('isbn', 'The character \'' . $this->text[9] . '\' is not allowed.');
					}

					$this->text = substr($this->text, 0, 9);
				}

				if (($this->gs1 === self::GS1_AUTO) || ($this->gs1 === self::GS1_PREFIX978)) {
					$this->text = '978' . $this->text;
				}
				else if ($this->gs1 === self::GS1_PREFIX979) {
					$this->text = '979' . $this->text;
				}
			}
			else if ($c !== 12) {
				throw new BCGParseException('isbn', 'The code parsed must be 9, 10, 12, or 13 digits long.');
			}
		}
	}

	private function createISBNText()
	{
		$isbn = '';

		if (!empty($this->text)) {
			$c = strlen($this->text);
			if (($c === 12) || ($c === 13)) {
				$lastCharacter = '';

				if ($c === 13) {
					$lastCharacter = $this->text[12];
					$this->text = substr($this->text, 0, 12);
				}

				$checksum = $this->processChecksum();
				$isbn = 'ISBN ' . substr($this->text, 0, 3) . '-' . substr($this->text, 3, 9) . '-' . $checksum;

				if ($c === 13) {
					$this->text .= $lastCharacter;
				}
			}
			else {
				if (($c === 9) || ($c === 10)) {
					$checksum = 0;

					for ($i = 10; 2 <= $i; $i--) {
						$checksum += $this->text[10 - $i] * $i;
					}

					$checksum = 11 - ($checksum % 11);

					if ($checksum === 10) {
						$checksum = 'X';
					}

					$isbn = 'ISBN ' . substr($this->text, 0, 9) . '-' . $checksum;
				}
			}
		}

		return $isbn;
	}
}

?>
