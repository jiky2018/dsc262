<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
abstract class BCGBarcode
{
	const COLOR_BG = 0;
	const COLOR_FG = 1;

	protected $colorFg;
	protected $colorBg;
	protected $scale;
	protected $offsetX;
	protected $offsetY;
	protected $labels = array();
	protected $pushLabel = array(0, 0);

	protected function __construct()
	{
		$this->setOffsetX(0);
		$this->setOffsetY(0);
		$this->setForegroundColor(0);
		$this->setBackgroundColor(16777215);
		$this->setScale(1);
	}

	public function parse($text)
	{
	}

	public function getForegroundColor()
	{
		return $this->colorFg;
	}

	public function setForegroundColor($code)
	{
		if ($code instanceof BCGColor) {
			$this->colorFg = $code;
		}
		else {
			$this->colorFg = new BCGColor($code);
		}
	}

	public function getBackgroundColor()
	{
		return $this->colorBg;
	}

	public function setBackgroundColor($code)
	{
		if ($code instanceof BCGColor) {
			$this->colorBg = $code;
		}
		else {
			$this->colorBg = new BCGColor($code);
		}

		foreach ($this->labels as $label) {
			$label->setBackgroundColor($this->colorBg);
		}
	}

	public function setColor($fg, $bg)
	{
		$this->setForegroundColor($fg);
		$this->setBackgroundColor($bg);
	}

	public function getScale()
	{
		return $this->scale;
	}

	public function setScale($scale)
	{
		$scale = intval($scale);

		if ($scale <= 0) {
			throw new BCGArgumentException('The scale must be larger than 0.', 'scale');
		}

		$this->scale = $scale;
	}

	abstract public function draw($im);

	public function getDimension($w, $h)
	{
		$labels = $this->getBiggestLabels(false);
		$pixelsAround = array(0, 0, 0, 0);

		if (isset($labels[BCGLabel::POSITION_TOP])) {
			$dimension = $labels[BCGLabel::POSITION_TOP]->getDimension();
			$pixelsAround[0] += $dimension[1];
		}

		if (isset($labels[BCGLabel::POSITION_RIGHT])) {
			$dimension = $labels[BCGLabel::POSITION_RIGHT]->getDimension();
			$pixelsAround[1] += $dimension[0];
		}

		if (isset($labels[BCGLabel::POSITION_BOTTOM])) {
			$dimension = $labels[BCGLabel::POSITION_BOTTOM]->getDimension();
			$pixelsAround[2] += $dimension[1];
		}

		if (isset($labels[BCGLabel::POSITION_LEFT])) {
			$dimension = $labels[BCGLabel::POSITION_LEFT]->getDimension();
			$pixelsAround[3] += $dimension[0];
		}

		$finalW = ($w + $this->offsetX) * $this->scale;
		$finalH = ($h + $this->offsetY) * $this->scale;
		$reversedLabels = $this->getBiggestLabels(true);

		foreach ($reversedLabels as $label) {
			$dimension = $label->getDimension();
			$alignment = $label->getAlignment();
			if (($label->getPosition() === BCGLabel::POSITION_LEFT) || ($label->getPosition() === BCGLabel::POSITION_RIGHT)) {
				if ($alignment === BCGLabel::ALIGN_TOP) {
					$pixelsAround[2] = max($pixelsAround[2], $dimension[1] - $finalH);
				}
				else if ($alignment === BCGLabel::ALIGN_CENTER) {
					$temp = ceil(($dimension[1] - $finalH) / 2);
					$pixelsAround[0] = max($pixelsAround[0], $temp);
					$pixelsAround[2] = max($pixelsAround[2], $temp);
				}
				else if ($alignment === BCGLabel::ALIGN_BOTTOM) {
					$pixelsAround[0] = max($pixelsAround[0], $dimension[1] - $finalH);
				}
			}
			else if ($alignment === BCGLabel::ALIGN_LEFT) {
				$pixelsAround[1] = max($pixelsAround[1], $dimension[0] - $finalW);
			}
			else if ($alignment === BCGLabel::ALIGN_CENTER) {
				$temp = ceil(($dimension[0] - $finalW) / 2);
				$pixelsAround[1] = max($pixelsAround[1], $temp);
				$pixelsAround[3] = max($pixelsAround[3], $temp);
			}
			else if ($alignment === BCGLabel::ALIGN_RIGHT) {
				$pixelsAround[3] = max($pixelsAround[3], $dimension[0] - $finalW);
			}
		}

		$this->pushLabel[0] = $pixelsAround[3];
		$this->pushLabel[1] = $pixelsAround[0];
		$finalW = (($w + $this->offsetX) * $this->scale) + $pixelsAround[1] + $pixelsAround[3];
		$finalH = (($h + $this->offsetY) * $this->scale) + $pixelsAround[0] + $pixelsAround[2];
		return array($finalW, $finalH);
	}

	public function getOffsetX()
	{
		return $this->offsetX;
	}

	public function setOffsetX($offsetX)
	{
		$offsetX = intval($offsetX);

		if ($offsetX < 0) {
			throw new BCGArgumentException('The offset X must be 0 or larger.', 'offsetX');
		}

		$this->offsetX = $offsetX;
	}

	public function getOffsetY()
	{
		return $this->offsetY;
	}

	public function setOffsetY($offsetY)
	{
		$offsetY = intval($offsetY);

		if ($offsetY < 0) {
			throw new BCGArgumentException('The offset Y must be 0 or larger.', 'offsetY');
		}

		$this->offsetY = $offsetY;
	}

	public function addLabel(BCGLabel $label)
	{
		$label->setBackgroundColor($this->colorBg);
		$this->labels[] = $label;
	}

	public function removeLabel(BCGLabel $label)
	{
		$remove = -1;
		$c = count($this->labels);

		for ($i = 0; $i < $c; $i++) {
			if ($this->labels[$i] === $label) {
				$remove = $i;
				break;
			}
		}

		if (-1 < $remove) {
			array_splice($this->labels, $remove, 1);
		}
	}

	public function clearLabels()
	{
		$this->labels = array();
	}

	protected function drawText($im, $x1, $y1, $x2, $y2)
	{
		foreach ($this->labels as $label) {
			$label->draw($im, (($x1 + $this->offsetX) * $this->scale) + $this->pushLabel[0], (($y1 + $this->offsetY) * $this->scale) + $this->pushLabel[1], (($x2 + $this->offsetX) * $this->scale) + $this->pushLabel[0], (($y2 + $this->offsetY) * $this->scale) + $this->pushLabel[1]);
		}
	}

	protected function drawPixel($im, $x, $y, $color = self::COLOR_FG)
	{
		$xR = (($x + $this->offsetX) * $this->scale) + $this->pushLabel[0];
		$yR = (($y + $this->offsetY) * $this->scale) + $this->pushLabel[1];
		imagefilledrectangle($im, $xR, $yR, ($xR + $this->scale) - 1, ($yR + $this->scale) - 1, $this->getColor($im, $color));
	}

	protected function drawRectangle($im, $x1, $y1, $x2, $y2, $color = self::COLOR_FG)
	{
		if ($this->scale === 1) {
			imagefilledrectangle($im, $x1 + $this->offsetX + $this->pushLabel[0], $y1 + $this->offsetY + $this->pushLabel[1], $x2 + $this->offsetX + $this->pushLabel[0], $y2 + $this->offsetY + $this->pushLabel[1], $this->getColor($im, $color));
		}
		else {
			imagefilledrectangle($im, (($x1 + $this->offsetX) * $this->scale) + $this->pushLabel[0], (($y1 + $this->offsetY) * $this->scale) + $this->pushLabel[1], ((($x2 + $this->offsetX) * $this->scale) + $this->pushLabel[0] + $this->scale) - 1, ((($y1 + $this->offsetY) * $this->scale) + $this->pushLabel[1] + $this->scale) - 1, $this->getColor($im, $color));
			imagefilledrectangle($im, (($x1 + $this->offsetX) * $this->scale) + $this->pushLabel[0], (($y1 + $this->offsetY) * $this->scale) + $this->pushLabel[1], ((($x1 + $this->offsetX) * $this->scale) + $this->pushLabel[0] + $this->scale) - 1, ((($y2 + $this->offsetY) * $this->scale) + $this->pushLabel[1] + $this->scale) - 1, $this->getColor($im, $color));
			imagefilledrectangle($im, (($x2 + $this->offsetX) * $this->scale) + $this->pushLabel[0], (($y1 + $this->offsetY) * $this->scale) + $this->pushLabel[1], ((($x2 + $this->offsetX) * $this->scale) + $this->pushLabel[0] + $this->scale) - 1, ((($y2 + $this->offsetY) * $this->scale) + $this->pushLabel[1] + $this->scale) - 1, $this->getColor($im, $color));
			imagefilledrectangle($im, (($x1 + $this->offsetX) * $this->scale) + $this->pushLabel[0], (($y2 + $this->offsetY) * $this->scale) + $this->pushLabel[1], ((($x2 + $this->offsetX) * $this->scale) + $this->pushLabel[0] + $this->scale) - 1, ((($y2 + $this->offsetY) * $this->scale) + $this->pushLabel[1] + $this->scale) - 1, $this->getColor($im, $color));
		}
	}

	protected function drawFilledRectangle($im, $x1, $y1, $x2, $y2, $color = self::COLOR_FG)
	{
		if ($x2 < $x1) {
			$x1 ^= $x2 ^= $x1 ^= $x2;
		}

		if ($y2 < $y1) {
			$y1 ^= $y2 ^= $y1 ^= $y2;
		}

		imagefilledrectangle($im, (($x1 + $this->offsetX) * $this->scale) + $this->pushLabel[0], (($y1 + $this->offsetY) * $this->scale) + $this->pushLabel[1], ((($x2 + $this->offsetX) * $this->scale) + $this->pushLabel[0] + $this->scale) - 1, ((($y2 + $this->offsetY) * $this->scale) + $this->pushLabel[1] + $this->scale) - 1, $this->getColor($im, $color));
	}

	protected function getColor($im, $color)
	{
		if ($color === self::COLOR_BG) {
			return $this->colorBg->allocate($im);
		}
		else {
			return $this->colorFg->allocate($im);
		}
	}

	private function getBiggestLabels($reversed = false)
	{
		$searchLR = ($reversed ? 1 : 0);
		$searchTB = ($reversed ? 0 : 1);
		$labels = array();

		foreach ($this->labels as $label) {
			$position = $label->getPosition();

			if (isset($labels[$position])) {
				$savedDimension = $labels[$position]->getDimension();
				$dimension = $label->getDimension();
				if (($position === BCGLabel::POSITION_LEFT) || ($position === BCGLabel::POSITION_RIGHT)) {
					if ($savedDimension[$searchLR] < $dimension[$searchLR]) {
						$labels[$position] = $label;
					}
				}
				else if ($savedDimension[$searchTB] < $dimension[$searchTB]) {
					$labels[$position] = $label;
				}
			}
			else {
				$labels[$position] = $label;
			}
		}

		return $labels;
	}
}

include_once 'BCGColor.php';
include_once 'BCGLabel.php';
include_once 'BCGArgumentException.php';
include_once 'BCGDrawException.php';

?>
