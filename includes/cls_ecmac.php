<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class cls_ecmac
{
	public $return_array = array();
	public $mac_addr;

	public function __construct($os_type)
	{
		$this->cls_ecmac($os_type);
	}

	public function cls_ecmac($os_type)
	{
		switch (strtolower($os_type)) {
		case 'linux':
			$this->forLinux();
			break;

		case 'solaris':
			break;

		case 'unix':
			break;

		case 'aix':
			break;

		default:
			$this->forWindows();
			break;
		}

		$temp_array = array();

		if ($this->return_array) {
			foreach ($this->return_array as $value) {
				if (preg_match('/[0-9a-f][0-9a-f][:-]' . '[0-9a-f][0-9a-f][:-]' . '[0-9a-f][0-9a-f][:-]' . '[0-9a-f][0-9a-f][:-]' . '[0-9a-f][0-9a-f][:-]' . '[0-9a-f][0-9a-f]/i', $value, $temp_array)) {
					$this->mac_addr = $temp_array[0];
					break;
				}
				else {
					$this->mac_addr = $os_type;
				}
			}

			unset($temp_array);
		}
	}

	public function __tostring()
	{
		return !empty($this->mac_addr) ? $this->mac_addr : '0';
	}

	public function forWindows()
	{
		@exec('ipconfig /all', $this->return_array);

		if ($this->return_array) {
			return $this->return_array;
		}
		else if (isset($_SERVER['WINDIR'])) {
			$ipconfig = $_SERVER['WINDIR'] . '\\system32\\ipconfig.exe';

			if (is_file($ipconfig)) {
				@exec($ipconfig . ' /all', $this->return_array);
			}
			else {
				@exec($_SERVER['WINDIR'] . '\\system\\ipconfig.exe /all', $this->return_array);
			}

			return $this->return_array;
		}
		else {
			return $this->return_array;
		}
	}

	public function forLinux()
	{
		if ($this->return_array) {
			@exec('ifconfig -a', $this->return_array);
			return $this->return_array;
		}
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
