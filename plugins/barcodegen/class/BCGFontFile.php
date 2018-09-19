<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGArgumentException.php';
include_once 'BCGFont.php';
include_once 'BCGColor.php';
class BCGFontFile implements BCGFont
{
	const PHP_BOX_FIX = 0;

	private $path;
	private $size;
	private $text = '';
	private $foregroundColor;
	private $rotationAngle;
	private $box;
	private $boxFix;

	public function __construct($fontPath, $size)
	{
		if (!file_exists($fontPath)) {
			throw new BCGArgumentException('The font path is incorrect.', 'fontPath');
		}

		$this->path = $fontPath;
		$this->size = $size;
		$this->foregroundColor = new BCGColor('black');
		$this->setRotationAngle(0);
		$this->setBoxFix(self::PHP_BOX_FIX);
	}

	public function getText()
	{
		return $this->text;
	}

	public function setText($text)
	{
		$this->text = $text;
		$this->box = NULL;
	}

	public function getRotationAngle()
	{
		return (360 - $this->rotationAngle) % 360;
	}

	public function setRotationAngle($rotationAngle)
	{
		$this->rotationAngle = (int) $rotationAngle;
		if (($this->rotationAngle !== 90) && ($this->rotationAngle !== 180) && ($this->rotationAngle !== 270)) {
			$this->rotationAngle = 0;
		}

		$this->rotationAngle = (360 - $this->rotationAngle) % 360;
		$this->box = NULL;
	}

	public function getBackgroundColor()
	{
	}

	public function setBackgroundColor($backgroundColor)
	{
	}

	public function getForegroundColor()
	{
		return $this->foregroundColor;
	}

	public function setForegroundColor($foregroundColor)
	{
		$this->foregroundColor = $foregroundColor;
	}

	public function getBoxFix()
	{
		return $this->boxFix;
	}

	public function setBoxFix($value)
	{
		$this->boxFix = intval($value);
	}

	public function getDimension()
	{
		$w = 0;
		$h = 0;
		$box = $this->getBox();

		if ($box !== NULL) {
			$minX = min(array($box[0], $box[2], $box[4], $box[6]));
			$maxX = max(array($box[0], $box[2], $box[4], $box[6]));
			$minY = min(array($box[1], $box[3], $box[5], $box[7]));
			$maxY = max(array($box[1], $box[3], $box[5], $box[7]));
			$w = $maxX - $minX;
			$h = $maxY - $minY;
		}

		$rotationAngle = $this->getRotationAngle();
		if (($rotationAngle === 90) || ($rotationAngle === 270)) {
			return array($h + self::PHP_BOX_FIX, $w);
		}
		else {
			return array($w + self::PHP_BOX_FIX, $h);
		}
	}

	public function draw($im, $x, $y)
	{
		$drawingPosition = $this->getDrawingPosition($x, $y);
		imagettftext($im, $this->size, $this->rotationAngle, $drawingPosition[0], $drawingPosition[1], $this->foregroundColor->allocate($im), $this->path, $this->text);
	}

	private function getDrawingPosition($x, $y)
	{
		$dimension = $this->getDimension();
		$box = $this->getBox();
		$rotationAngle = $this->getRotationAngle();

		if ($rotationAngle === 0) {
			$y += abs(min($box[5], $box[7]));
		}
		else if ($rotationAngle === 90) {
			$x += abs(min($box[5], $box[7]));
			$y += $dimension[1];
		}
		else if ($rotationAngle === 180) {
			$x += $dimension[0];
			$y += abs(max($box[1], $box[3]));
		}
		else if ($rotationAngle === 270) {
			$x += abs(max($box[1], $box[3]));
		}

		return array($x, $y);
	}

	private function getBox()
	{
		if ($this->box === NULL) {
			$gd = imagecreate(1, 1);
			$this->box = imagettftext($gd, $this->size, 0, 0, 0, 0, $this->path, $this->text);
		}

		return $this->box;
	}
}

?>
