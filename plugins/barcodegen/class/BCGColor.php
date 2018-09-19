<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class BCGColor
{
	protected $r;
	protected $g;
	protected $b;
	protected $transparent;

	public function __construct()
	{
		$args = func_get_args();
		$c = count($args);

		if ($c === 3) {
			$this->r = intval($args[0]);
			$this->g = intval($args[1]);
			$this->b = intval($args[2]);
		}
		else if ($c === 1) {
			if (is_string($args[0]) && (strlen($args[0]) === 7) && ($args[0][0] === '#')) {
				$this->r = intval(substr($args[0], 1, 2), 16);
				$this->g = intval(substr($args[0], 3, 2), 16);
				$this->b = intval(substr($args[0], 5, 2), 16);
			}
			else {
				if (is_string($args[0])) {
					$args[0] = self::getColor($args[0]);
				}

				$args[0] = intval($args[0]);
				$this->r = ($args[0] & 16711680) >> 16;
				$this->g = ($args[0] & 65280) >> 8;
				$this->b = $args[0] & 255;
			}
		}
		else {
			$this->r = $this->g = $this->b = 0;
		}
	}

	public function setTransparent($transparent)
	{
		$this->transparent = $transparent;
	}

	public function r()
	{
		return $this->r;
	}

	public function g()
	{
		return $this->g;
	}

	public function b()
	{
		return $this->b;
	}

	public function allocate(&$im)
	{
		$allocated = imagecolorallocate($im, $this->r, $this->g, $this->b);

		if ($this->transparent) {
			return imagecolortransparent($im, $allocated);
		}
		else {
			return $allocated;
		}
	}

	static public function getColor($code, $default = 'white')
	{
		switch (strtolower($code)) {
		case '':
		case 'white':
			return 16777215;
		case 'black':
			return 0;
		case 'maroon':
			return 8388608;
		case 'red':
			return 16711680;
		case 'orange':
			return 16753920;
		case 'yellow':
			return 16776960;
		case 'olive':
			return 8421376;
		case 'purple':
			return 8388736;
		case 'fuchsia':
			return 16711935;
		case 'lime':
			return 65280;
		case 'green':
			return 32768;
		case 'navy':
			return 128;
		case 'blue':
			return 255;
		case 'aqua':
			return 65535;
		case 'teal':
			return 32896;
		case 'silver':
			return 12632256;
		case 'gray':
			return 8421504;
		default:
			return self::getColor($default, 'white');
		}
	}
}


?>
