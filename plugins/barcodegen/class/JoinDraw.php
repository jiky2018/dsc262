<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class JoinDraw
{
	const ALIGN_RIGHT = 0;
	const ALIGN_BOTTOM = 0;
	const ALIGN_LEFT = 1;
	const ALIGN_TOP = 1;
	const ALIGN_CENTER = 2;
	const POSITION_RIGHT = 0;
	const POSITION_BOTTOM = 1;
	const POSITION_LEFT = 2;
	const POSITION_TOP = 3;

	private $image1;
	private $image2;
	private $alignement;
	private $position;
	private $space;
	private $im;

	public function __construct($image1, $image2, $background, $space = 10, $position = self::POSITION_RIGHT, $alignment = self::ALIGN_TOP)
	{
		if ($image1 instanceof BCGDrawing) {
			$this->image1 = $image1->get_im();
		}
		else {
			$this->image1 = $image1;
		}

		if ($image2 instanceof BCGDrawing) {
			$this->image2 = $image2->get_im();
		}
		else {
			$this->image2 = $image2;
		}

		$this->background = $background;
		$this->space = (int) $space;
		$this->position = (int) $position;
		$this->alignment = (int) $alignment;
		$this->createIm();
	}

	public function __destruct()
	{
		imagedestroy($this->im);
	}

	private function findPosition($size1, $size2, $alignment)
	{
		$rsize1 = max($size1, $size2);
		$rsize2 = min($size1, $size2);

		if ($alignment === self::ALIGN_LEFT) {
			return 0;
		}
		else if ($alignment === self::ALIGN_CENTER) {
			return ($rsize1 / 2) - ($rsize2 / 2);
		}
		else {
			return $rsize1 - $rsize2;
		}
	}

	private function changeAlignment($alignment)
	{
		if ($alignment === 0) {
			return 1;
		}
		else if ($alignment === 1) {
			return 0;
		}
		else {
			return 2;
		}
	}

	private function createIm()
	{
		$w1 = imagesx($this->image1);
		$w2 = imagesx($this->image2);
		$h1 = imagesy($this->image1);
		$h2 = imagesy($this->image2);
		if (($this->position === self::POSITION_LEFT) || ($this->position === self::POSITION_RIGHT)) {
			$w = $w1 + $w2 + $this->space;
			$h = max($h1, $h2);
		}
		else {
			$w = max($w1, $w2);
			$h = $h1 + $h2 + $this->space;
		}

		$this->im = imagecreatetruecolor($w, $h);
		imagefill($this->im, 0, 0, $this->background->allocate($this->im));

		if ($this->position === self::POSITION_TOP) {
			if ($w2 < $w1) {
				$posX1 = 0;
				$posX2 = $this->findPosition($w1, $w2, $this->alignment);
			}
			else {
				$a = $this->changeAlignment($this->alignment);
				$posX1 = $this->findPosition($w1, $w2, $a);
				$posX2 = 0;
			}

			$posY2 = 0;
			$posY1 = $h2 + $this->space;
		}
		else if ($this->position === self::POSITION_LEFT) {
			if ($w2 < $w1) {
				$posY1 = 0;
				$posY2 = $this->findPosition($h1, $h2, $this->alignment);
			}
			else {
				$a = $this->changeAlignment($this->alignment);
				$posY2 = 0;
				$posY1 = $this->findPosition($h1, $h2, $a);
			}

			$posX2 = 0;
			$posX1 = $w2 + $this->space;
		}
		else if ($this->position === self::POSITION_BOTTOM) {
			if ($w2 < $w1) {
				$posX2 = $this->findPosition($w1, $w2, $this->alignment);
				$posX1 = 0;
			}
			else {
				$a = $this->changeAlignment($this->alignment);
				$posX2 = 0;
				$posX1 = $this->findPosition($w1, $w2, $a);
			}

			$posY1 = 0;
			$posY2 = $h1 + $this->space;
		}
		else {
			if ($w2 < $w1) {
				$posY2 = $this->findPosition($h1, $h2, $this->alignment);
				$posY1 = 0;
			}
			else {
				$a = $this->changeAlignment($this->alignment);
				$posY2 = 0;
				$posY1 = $this->findPosition($h1, $h2, $a);
			}

			$posX1 = 0;
			$posX2 = $w1 + $this->space;
		}

		imagecopy($this->im, $this->image1, $posX1, $posY1, 0, 0, $w1, $h1);
		imagecopy($this->im, $this->image2, $posX2, $posY2, 0, 0, $w2, $h2);
	}

	public function get_im()
	{
		return $this->im;
	}
}


?>
