<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class BCGDrawing
{
	const IMG_FORMAT_PNG = 1;
	const IMG_FORMAT_JPEG = 2;
	const IMG_FORMAT_GIF = 3;
	const IMG_FORMAT_WBMP = 4;

	private $w;
	private $h;
	private $color;
	private $filename;
	private $im;
	private $barcode;
	private $dpi;
	private $rotateDegree;

	public function __construct($filename = NULL, BCGColor $color)
	{
		$this->im = NULL;
		$this->setFilename($filename);
		$this->color = $color;
		$this->dpi = NULL;
		$this->rotateDegree = 0;
	}

	public function __destruct()
	{
		$this->destroy();
	}

	public function getFilename()
	{
		return $this->filename;
	}

	public function setFilename($filename)
	{
		$this->filename = $filename;
	}

	public function get_im()
	{
		return $this->im;
	}

	public function set_im($im)
	{
		$this->im = $im;
	}

	public function getBarcode()
	{
		return $this->barcode;
	}

	public function setBarcode(BCGBarcode $barcode)
	{
		$this->barcode = $barcode;
	}

	public function getDPI()
	{
		return $this->dpi;
	}

	public function setDPI($dpi)
	{
		$this->dpi = $dpi;
	}

	public function getRotationAngle()
	{
		return $this->rotateDegree;
	}

	public function setRotationAngle($degree)
	{
		$this->rotateDegree = (double) $degree;
	}

	public function draw()
	{
		$size = $this->barcode->getDimension(0, 0);
		$this->w = max(1, $size[0]);
		$this->h = max(1, $size[1]);
		$this->init();
		$this->barcode->draw($this->im);
	}

	public function finish($image_style = self::IMG_FORMAT_PNG, $quality = 100)
	{
		$drawer = NULL;
		$im = $this->im;

		if (0 < $this->rotateDegree) {
			if (function_exists('imagerotate')) {
				$im = imagerotate($this->im, 360 - $this->rotateDegree, $this->color->allocate($this->im));
			}
			else {
				throw new BCGDrawException('The method imagerotate doesn\'t exist on your server. Do not use any rotation.');
			}
		}

		if ($image_style === self::IMG_FORMAT_PNG) {
			$drawer = new BCGDrawPNG($im);
			$drawer->setFilename($this->filename);
			$drawer->setDPI($this->dpi);
		}
		else if ($image_style === self::IMG_FORMAT_JPEG) {
			$drawer = new BCGDrawJPG($im);
			$drawer->setFilename($this->filename);
			$drawer->setDPI($this->dpi);
			$drawer->setQuality($quality);
		}
		else if ($image_style === self::IMG_FORMAT_GIF) {
			if (($this->filename === NULL) || ($this->filename === '')) {
				imagegif($im);
			}
			else {
				imagegif($im, $this->filename);
			}
		}
		else if ($image_style === self::IMG_FORMAT_WBMP) {
			imagewbmp($im, $this->filename);
		}

		if ($drawer !== NULL) {
			$drawer->draw();
		}
	}

	public function drawException($exception)
	{
		$this->w = 1;
		$this->h = 1;
		$this->init();
		$w = imagesx($this->im);
		$h = imagesy($this->im);
		$text = 'Error: ' . $exception->getMessage();
		$width = imagefontwidth(2) * strlen($text);
		$height = imagefontheight(2);
		if (($w < $width) || ($h < $height)) {
			$width = max($w, $width);
			$height = max($h, $height);
			$newimg = imagecreatetruecolor($width, $height);
			imagefill($newimg, 0, 0, imagecolorat($this->im, 0, 0));
			imagecopy($newimg, $this->im, 0, 0, 0, 0, $w, $h);
			$this->im = $newimg;
		}

		$black = new BCGColor('black');
		imagestring($this->im, 2, 0, 0, $text, $black->allocate($this->im));
	}

	public function destroy()
	{
		@imagedestroy($this->im);
	}

	private function init()
	{
		if ($this->im === NULL) {
			$this->im = imagecreatetruecolor($this->w, $this->h) or die('Can\'t Initialize the GD Libraty');
			imagefilledrectangle($this->im, 0, 0, $this->w - 1, $this->h - 1, $this->color->allocate($this->im));
		}
	}
}

include_once 'BCGBarcode.php';
include_once 'BCGColor.php';
include_once 'BCGDrawException.php';
include_once 'drawer/BCGDrawJPG.php';
include_once 'drawer/BCGDrawPNG.php';

?>
