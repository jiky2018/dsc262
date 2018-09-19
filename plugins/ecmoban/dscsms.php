<?php
//QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class dscsms
{
	private $app_key = '';
	private $app_secret = '';
	private $action = 'send';
	private $protocolType = 'https';
	private $domain = 'cloud.ecjia.com';
	private $graphUrl = '/sites/api/?url=sms/send';
	private $getMethod = 'GET';

	public function __construct($app_key = '', $app_secret = '')
	{
		$this->app_key = $app_key;
		$this->app_secret = $app_secret;
	}

	public function getUrl()
	{
		return $this->protocolType . '://' . $this->domain . $this->graphUrl;
	}

	public function composeData($apiParams = array())
	{
		if (!empty($apiParams['TemplateContent']) && !empty($apiParams['TemplateParam'])) {
			$TemplateParam = json_decode($apiParams['TemplateParam'], true);
			preg_match_all('/\\{(.+?)\\}/', $apiParams['TemplateContent'], $match);

			foreach ($match[1] as $key => $val) {
				$apiParams['TemplateContent'] = str_replace('${' . $val . '}', $TemplateParam[$val], $apiParams['TemplateContent']);
			}
		}

		$sendParams = array('app_key' => $this->app_key, 'app_secret' => $this->app_secret, 'mobile' => $apiParams['PhoneNumbers'], 'content' => $apiParams['TemplateContent']);
		return $sendParams;
	}

	public function composeUrl($apiParams = array())
	{
		$sendParams = composeData($apiParams);
		$requestUrl = $this->getUrl();

		foreach ($sendParams as $apiParamKey => $apiParamValue) {
			$requestUrl .= '&' . ($apiParamKey . '=') . urlencode($apiParamValue);
		}

		return substr($requestUrl, 0, -1);
	}

	public function send($url, $data)
	{
		$http = new Http();
		if (isset($data) && is_array($data)) {
			$resp = $http->doPost($url, $data);
		}
		else {
			$resp = $http->doGet($url);
		}

		$resp = json_decode($resp, true);

		if ($resp['status']['succeed'] == 1) {
			return true;
		}
		else {
			$errorInfo = $resp['status']['error_desc'];
			return $errorInfo;
		}
	}

	private function logResult($word = '')
	{
		$word = is_array($word) ? var_export($word, true) : $word;
		$fp = fopen(ROOT_PATH . 'sms/dscsms_log.txt', 'a');
		flock($fp, LOCK_EX);
		fwrite($fp, '执行日期：' . date('Y-m-d H:i:s', time()) . "\n" . $word . "\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
