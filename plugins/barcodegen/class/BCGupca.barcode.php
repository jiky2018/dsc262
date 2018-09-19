<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGParseException.php';
include_once 'BCGBarcode.php';
include_once 'BCGean13.barcode.php';
include_once 'BCGLabel.php';
class BCGupca extends BCGean13
{
	protected $labelRight;

	public function __construct()
	{
		parent::__construct();
	}

	public function draw($im)
	{
		$this->text = '0' . $this->text;
		parent::draw($im);
		$this->text = substr($this->text, 1);
	}

	protected function drawExtendedBars($im, $plus)
	{
		$temp_text = $this->text . $this->keys[$this->checksumValue];
		$rememberX = $this->positionX;
		$rememberH = $this->thickness;
		$this->thickness = $this->thickness + intval($plus / $this->scale);
		$this->positionX = 0;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 2;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 1;
		$temp_value = $this->findCode($temp_text[1]);
		$this->drawChar($im, $temp_value, false);
		$this->positionX += 36;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 2;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 37;
		$temp_value = $this->findCode($temp_text[12]);
		$this->drawChar($im, $temp_value, true);
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX += 2;
		$this->drawSingleBar($im, BCGBarcode::COLOR_FG);
		$this->positionX = $rememberX;
		$this->thickness = $rememberH;
	}

	protected function addDefaultLabel()
	{
		if ($this->isDefaultEanLabelEnabled()) {
			$this->processChecksum();
			$label = $this->getLabel();
			$font = $this->font;
			$this->labelLeft = new BCGLabel(substr($label, 0, 1), $font, BCGLabel::POSITION_LEFT, BCGLabel::ALIGN_BOTTOM);
			$this->labelLeft->setSpacing(4 * $this->scale);
			$this->labelCenter1 = new BCGLabel(substr($label, 1, 5), $font, BCGLabel::POSITION_BOTTOM, BCGLabel::ALIGN_LEFT);
			$labelCenter1Dimension = $this->labelCenter1->getDimension();
			$this->labelCenter1->setOffset(((($this->scale * 44) - $labelCenter1Dimension[0]) / 2) + ($this->scale * 6));
			$this->labelCenter2 = new BCGLabel(substr($label, 6, 5), $font, BCGLabel::POSITION_BOTTOM, BCGLabel::ALIGN_LEFT);
			$this->labelCenter2->setOffset(((($this->scale * 44) - $labelCenter1Dimension[0]) / 2) + ($this->scale * 45));
			$this->labelRight = new BCGLabel($this->keys[$this->checksumValue], $font, BCGLabel::POSITION_RIGHT, BCGLabel::ALIGN_BOTTOM);
			$this->labelRight->setSpacing(4 * $this->scale);

			if ($this->alignLabel) {
				$labelDimension = $this->labelCenter1->getDimension();
				$this->labelLeft->setOffset($labelDimension[1]);
				$this->labelRight->setOffset($labelDimension[1]);
			}
			else {
				$labelDimension = $this->labelLeft->getDimension();
				$this->labelLeft->setOffset($labelDimension[1] / 2);
				$labelDimension = $this->labelLeft->getDimension();
				$this->labelRight->setOffset($labelDimension[1] / 2);
			}

			$this->addLabel($this->labelLeft);
			$this->addLabel($this->labelCenter1);
			$this->addLabel($this->labelCenter2);
			$this->addLabel($this->labelRight);
		}
	}

	protected function checkCorrectLength()
	{
		$c = strlen($this->text);

		if ($c === 12) {
			$this->text = substr($this->text, 0, 11);
		}
		else if ($c !== 11) {
			throw new BCGParseException('upca', 'Must contain 11 digits, the 12th digit is automatically added.');
		}
	}
}

?>
