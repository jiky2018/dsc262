<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class captcha
{
	/**
     * 背景图片所在目录
     *
     * @var string  $folder
     */
	public $folder = 'data/captcha';
	/**
     * 图片的文件类型
     *
     * @var string  $img_type
     */
	public $img_type = 'png';
	public $session_word = 'captcha_word';
	/**
     * 背景图片以及背景颜色
     *
     * 0 => 背景图片的文件名
     * 1 => Red, 2 => Green, 3 => Blue
     * @var array   $themes
     */
	public $themes_jpg = array(
		1 => array('captcha_bg1.jpg', 255, 255, 255),
		2 => array('captcha_bg2.jpg', 0, 0, 0),
		3 => array('captcha_bg3.jpg', 0, 0, 0),
		4 => array('captcha_bg4.jpg', 255, 255, 255),
		5 => array('captcha_bg5.jpg', 255, 255, 255)
		);
	public $themes_gif = array(
		1 => array('captcha_bg1.gif', 255, 255, 255),
		2 => array('captcha_bg2.gif', 0, 0, 0),
		3 => array('captcha_bg3.gif', 0, 0, 0),
		4 => array('captcha_bg4.gif', 255, 255, 255),
		5 => array('captcha_bg5.gif', 255, 255, 255)
		);
	/**
     * 图片的宽度
     *
     * @var integer $width
     */
	public $width = 130;
	/**
     * 图片的高度
     *
     * @var integer $height
     */
	public $height = 20;

	public function captcha($folder = '', $width = 145, $height = 20)
	{
		if (!empty($folder)) {
			$this->folder = $folder;
		}

		$this->width = $width;
		$this->height = $height;

		if ('4.3' <= PHP_VERSION) {
			return function_exists('imagecreatetruecolor') || function_exists('imagecreate');
		}
		else {
			return (0 < (imagetypes() & IMG_GIF)) || (0 < (imagetypes() & IMG_JPG));
		}
	}

	public function __construct($folder = '', $width = 145, $height = 20)
	{
		$this->captcha($folder, $width, $height);
	}

	public function check_word($word)
	{
		$recorded = (isset($_SESSION[$this->session_word]) ? base64_decode($_SESSION[$this->session_word]) : '');
		$given = $this->encrypts_word(strtoupper($word));
		return preg_match('/' . $given . '/', $recorded);
	}

	public function generate_image($word = false)
	{
		if (!$word) {
			$word = $this->generate_word();
		}

		$this->record_word($word);
		$letters = strlen($word);
		mt_srand((double) microtime() * 1000000);
		if (function_exists('imagecreatefromjpeg') && (0 < (imagetypes() & IMG_JPG))) {
			$theme = $this->themes_jpg[mt_rand(1, count($this->themes_jpg))];
		}
		else {
			$theme = $this->themes_gif[mt_rand(1, count($this->themes_gif))];
		}

		if (!file_exists($this->folder . $theme[0])) {
			return false;
		}
		else {
			$img_bg = (function_exists('imagecreatefromjpeg') && (0 < (imagetypes() & IMG_JPG)) ? imagecreatefromjpeg($this->folder . $theme[0]) : imagecreatefromgif($this->folder . $theme[0]));
			$bg_width = imagesx($img_bg);
			$bg_height = imagesy($img_bg);
			$img_org = (function_exists('imagecreatetruecolor') && ('4.3' <= PHP_VERSION) ? imagecreatetruecolor($this->width, $this->height) : imagecreate($this->width, $this->height));
			if (function_exists('imagecopyresampled') && ('4.3' <= PHP_VERSION)) {
				imagecopyresampled($img_org, $img_bg, 0, 0, 0, 0, $this->width, $this->height, $bg_width, $bg_height);
			}
			else {
				imagecopyresized($img_org, $img_bg, 0, 0, 0, 0, $this->width, $this->height, $bg_width, $bg_height);
			}

			imagedestroy($img_bg);
			$clr = imagecolorallocate($img_org, $theme[1], $theme[2], $theme[3]);
			$x = ($this->width - (imagefontwidth(5) * $letters)) / 2;
			$y = ($this->height - imagefontheight(5)) / 2;
			imagestring($img_org, 5, $x, $y, $word, $clr);
			header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
			header('Cache-Control: private, no-store, no-cache, must-revalidate');
			header('Cache-Control: post-check=0, pre-check=0, max-age=0', false);
			header('Pragma: no-cache');
			if (($this->img_type == 'jpeg') && function_exists('imagecreatefromjpeg')) {
				header('Content-type: image/jpeg');
				imageinterlace($img_org, 1);
				imagejpeg($img_org, false, 95);
			}
			else {
				header('Content-type: image/png');
				imagepng($img_org);
			}

			imagedestroy($img_org);
			return true;
		}
	}

	public function encrypts_word($word)
	{
		return substr(md5($word), 1, 10);
	}

	public function record_word($word)
	{
		$_SESSION[$this->session_word] = base64_encode($this->encrypts_word($word));
	}

	public function generate_word($length = 4)
	{
		$chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
		$i = 0;

		for ($count = strlen($chars); $i < $count; $i++) {
			$arr[$i] = $chars[$i];
		}

		mt_srand((double) microtime() * 1000000);
		shuffle($arr);
		return substr(implode('', $arr), 5, $length);
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
