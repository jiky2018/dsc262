<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
namespace App\Channels\Sms\Driver;

class Alidayu
{
	/**
     * 短信类配置
     * @var array
     */
	protected $config = array('ali_appkey' => '', 'ali_secretkey' => '');
	/**
     * @var objcet 短信对象
     */
	protected $content = array();
	protected $phones = array();
	protected $errorInfo = '';

	public function __construct($config = array())
	{
		$this->config = array_merge($this->config, $config);
	}

	public function setSms($title, $content)
	{
		$sql = 'SELECT * FROM {pre}alidayu_configure WHERE send_time = \'' . $title . '\'';
		$msg = $GLOBALS['db']->getRow($sql);

		foreach ($content as $key => $vo) {
			settype($content[$key], 'string');
		}

		$this->content = array('sms_type' => 'normal', 'sms_free_sign_name' => $msg['set_sign'], 'sms_template_code' => $msg['temp_id'], 'sms_param' => json_encode($content));
		return $this;
	}

	public function sendSms($to)
	{
		$sendTo = explode(',', $to);

		foreach ($sendTo as $add) {
			if (is_mobile($add)) {
				array_push($this->phones, $add);
			}
		}

		if ($this->phones) {
			foreach ($this->phones as $mobile) {
				return $this->send($mobile);
			}
		}

		return false;
	}

	public function send($mobile)
	{
		require_once dirname(ROOT_PATH) . '/plugins/aliyunyu/TopSdk.php';
		$c = new \TopClient();
		$c->appkey = $this->config['ali_appkey'];
		$c->secretKey = $this->config['ali_secretkey'];
		$c->format = 'json';
		$req = new \AlibabaAliqinFcSmsNumSendRequest();
		$req->setSmsType($this->content['sms_type']);
		$req->setSmsFreeSignName($this->content['sms_free_sign_name']);
		$req->setSmsParam($this->content['sms_param']);
		$req->setRecNum($mobile);
		$req->setSmsTemplateCode($this->content['sms_template_code']);
		$resp = $c->execute($req);

		if ($resp->code == 0) {
			return true;
		}
		else if ($resp->sub_msg) {
			$this->errorInfo = $resp->sub_msg;
		}
		else {
			$this->errorInfo = $resp->msg;
		}

		logResult($this->errorInfo, 'sms');
		return false;
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
