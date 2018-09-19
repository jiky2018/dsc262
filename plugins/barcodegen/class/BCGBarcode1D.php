<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGArgumentException.php';
include_once 'BCGBarcode.php';
include_once 'BCGFontPhp.php';
include_once 'BCGFontFile.php';
include_once 'BCGLabel.php';
abstract class BCGBarcode1D extends BCGBarcode
{
	const SIZE_SPACING_FONT = 5;
	const AUTO_LABEL = '##!!AUTO_LABEL!!##';

	protected $thickness;
	protected $keys;
	protected $code;
	protected $positionX;
	protected $font;
	protected $text;
	protected $checksumValue;
	protected $displayChecksum;
	protected $label;
	protected $defaultLabel;

	protected function __construct()
	{
		parent::__construct();
		$this->setThickness(30);
		$this->defaultLabel = new BCGLabel();
		$this->defaultLabel->setPosition(BCGLabel::POSITION_BOTTOM);
		$this->setLabel(self::AUTO_LABEL);
		$this->setFont(new BCGFontPhp(5));
		$this->text = '';
		$this->checksumValue = false;
		$this->positionX = 0;
	}

	public function getThickness()
	{
		return $this->thickness;
	}

	public function setThickness($thickness)
	{
		$thickness = intval($thickness);

		if ($thickness <= 0) {
			throw new BCGArgumentException('The thickness must be larger than 0.', 'thickness');
		}

		$this->thickness = $thickness;
	}

	public function getLabel()
	{
		$label = $this->label;

		if ($this->label === self::AUTO_LABEL) {
			$label = $this->text;
			if (($this->displayChecksum === true) && (($checksum = $this->processChecksum()) !== false)) {
				$label .= $checksum;
			}
		}

		return $label;
	}

	public function setLabel($label)
	{
		$this->label = $label;
	}

	public function getFont()
	{
		return $this->font;
	}

	public function setFont($font)
	{
		if (is_int($font)) {
			if ($font === 0) {
				$font = NULL;
			}
			else {
				$font = new BCGFontPhp($font);
			}
		}

		$this->font = $font;
	}

	public function parse($text)
	{
		$this->text = $text;
		$this->checksumValue = false;
		$this->validate();
		parent::parse($text);
		$this->addDefaultLabel();
	}

	public function getChecksum()
	{
		return $this->processChecksum();
	}

	public function setDisplayChecksum($displayChecksum)
	{
		$this->displayChecksum = (bool) $displayChecksum;
	}

	protected function addDefaultLabel()
	{
		$label = $this->getLabel();
		$font = $this->font;
		if (($label !== NULL) && ($label !== '') && ($font !== NULL) && ($this->defaultLabel !== NULL)) {
			$this->defaultLabel->setText($label);
			$this->defaultLabel->setFont($font);
			$this->addLabel($this->defaultLabel);
		}
	}

	protected function validate()
	{
	}

	protected function findIndex($var)
	{
		return array_search($var, $this->keys);
	}

	protected function findCode($var)
	{
		return $this->code[$this->findIndex($var)];
	}

	protected function drawChar($im, $code, $startBar = true)
	{
		$colors = array(BCGBarcode::COLOR_FG, BCGBarcode::COLOR_BG);
		$currentColor = ($startBar ? 0 : 1);
		$c = strlen($code);

		for ($i = 0; $i < $c; $i++) {
			for ($j = 0; $j < (intval($code[$i]) + 1); $j++) {
				$this->drawSingleBar($im, $colors[$currentColor]);
				$this->nextX();
			}

			$currentColor = ($currentColor + 1) % 2;
		}
	}

	protected function drawSingleBar($im, $color)
	{
		$this->drawFilledRectangle($im, $this->positionX, 0, $this->positionX, $this->thickness - 1, $color);
	}

	protected function nextX()
	{
		$this->positionX++;
	}

	protected function calculateChecksum()
	{
		$this->checksumValue = false;
	}

	protected function processChecksum()
	{
		return false;
	}
}

?>
