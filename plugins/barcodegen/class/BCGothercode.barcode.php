<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode1D.php';
class BCGothercode extends BCGBarcode1D
{
	public function __construct()
	{
		parent::__construct();
		$this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
	}

	public function draw($im)
	{
		$this->drawChar($im, $this->text, true);
		$this->drawText($im, 0, 0, $this->positionX, $this->thickness);
	}

	public function getLabel()
	{
		$label = $this->label;

		if ($this->label === BCGBarcode1D::AUTO_LABEL) {
			$label = '';
		}

		return $label;
	}

	public function getDimension($w, $h)
	{
		$array = str_split($this->text, 1);
		$textlength = array_sum($array) + count($array);
		$w += $textlength;
		$h += $this->thickness;
		return parent::getDimension($w, $h);
	}

	protected function validate()
	{
		$c = strlen($this->text);

		if ($c === 0) {
			throw new BCGParseException('othercode', 'No data has been entered.');
		}

		for ($i = 0; $i < $c; $i++) {
			if (array_search($this->text[$i], $this->keys) === false) {
				throw new BCGParseException('othercode', 'The character \'' . $this->text[$i] . '\' is not allowed.');
			}
		}

		parent::validate();
	}
}

?>
