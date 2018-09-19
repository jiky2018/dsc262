<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class HttpdnsGetRequest
{
	private $apiParas = array();

	public function getApiMethodName()
	{
		return 'taobao.httpdns.get';
	}

	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function check()
	{
	}

	public function putOtherTextParam($key, $value)
	{
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}


?>
