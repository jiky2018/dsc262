<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Channels\Sms;

class Sms
{
	protected $config = array('sms_name' => '', 'sms_password' => '');
	protected $sms;
	/**
     * 驱动对象
     * @var array
     */
	static protected $objArr = array();

	public function __construct($config = array())
	{
		$this->config = array_merge($this->config, $config);
	}

	public function __call($method, $args)
	{
		$sms_type = $this->config['sms_type'];

		if (!isset(self::$objArr[$sms_type])) {
			$smsDriver = 'App\\Channels\\Sms' . '\\Driver\\' . ucfirst($this->config['sms_type']);

			if (!class_exists($smsDriver)) {
				throw new \Exception('Sms Driver \'' . $smsDriver . '\' not found\'', 500);
			}

			self::$objArr[$sms_type] = new $smsDriver($this->config[$sms_type]);
		}

		return call_user_func_array(array(self::$objArr[$sms_type], $method), $args);
	}
}


?>
