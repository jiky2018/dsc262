<?php
//QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Channels\Sms\Driver;

class Dscsms
{
	/**
     * 短信类配置
     * @var array
     */
	protected $config = array('app_key' => '', 'app_secret' => '');
	/**
     * @var objcet 短信对象
     */
	protected $sms_api = 'https://cloud.ecjia.com/sites/api/?url=sms/send';
	protected $content;
	protected $phones = array();
	protected $errorInfo;

	public function __construct($config = array())
	{
		$this->config = array_merge($this->config, $config);
	}

	public function setSms($title, $content)
	{
		$sql = 'SELECT * FROM {pre}alidayu_configure WHERE send_time = \'' . $title . '\'';
		$msg = $GLOBALS['db']->getRow($sql);
		preg_match_all('/\\$\\{(.*?)\\}/', $msg['temp_content'], $matches);

		foreach ($matches[1] as $vo) {
			$msg['temp_content'] = str_replace('${' . $vo . '}', $content[$vo], $msg['temp_content']);
		}

		$this->content = $msg['temp_content'];
		return $this;
	}

	public function sendSms($to)
	{
		$sendTo = explode(',', $to);

		foreach ($sendTo as $add) {
			if (is_mobile($add)) {
				$this->addPhone($add);
			}
		}

		if (!$this->send()) {
			$return = false;
		}
		else {
			$return = true;
		}

		return $return;
	}

	private function addPhone($add)
	{
		array_push($this->phones, $add);
	}

	private function send()
	{
		foreach ($this->phones as $mobile) {
			$post_data = array('app_key' => $this->config['app_key'], 'app_secret' => $this->config['app_secret'], 'mobile' => $mobile, 'content' => $this->content);
			$res = \App\Extensions\Http::doPost($this->sms_api, $post_data);
			$data = json_decode($res, true);

			if ($data['status']['succeed']) {
				return true;
			}
			else {
				$this->errorInfo = $data['status']['error_desc'];
				logResult($this->errorInfo, 'sms');
				return false;
			}
		}
	}

	public function getError()
	{
		return $this->errorInfo;
	}

	public function __destruct()
	{
		unset($this->sms);
	}
}


?>
