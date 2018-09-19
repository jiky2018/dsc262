<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
namespace App\Channels\Sms\Driver;

class Ihuyi
{
	/**
     * 短信类配置
     * @var array
     */
	protected $config = array('sms_name' => '', 'sms_password' => '');
	/**
     * @var objcet 短信对象
     */
	protected $sms_api = 'http://106.ihuyi.com/webservice/sms.php?method=Submit';
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
			$post_data = array('account' => $this->config['sms_name'], 'password' => $this->config['sms_password'], 'mobile' => $mobile, 'content' => $this->content);
			$res = \App\Extensions\Http::doPost($this->sms_api, $post_data);
			$data = $this->xmlToArray($res);

			if ($data['SubmitResult']['code'] == 2) {
				return true;
			}
			else {
				$this->errorInfo = $data['SubmitResult']['msg'];
				logResult($this->errorInfo, 'sms');
				return false;
			}
		}
	}

	private function xmlToArray($xml)
	{
		$reg = '/<(\\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/';

		if (preg_match_all($reg, $xml, $matches)) {
			$count = count($matches[0]);

			for ($i = 0; $i < $count; $i++) {
				$subxml = $matches[2][$i];
				$key = $matches[1][$i];

				if (preg_match($reg, $subxml)) {
					$arr[$key] = $this->xmlToArray($subxml);
				}
				else {
					$arr[$key] = $subxml;
				}
			}
		}

		return $arr;
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
