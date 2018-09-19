<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Result;

class HeaderResult extends Result
{
	protected function parseDataFromResponse()
	{
		return empty($this->rawResponse->header) ? array() : $this->rawResponse->header;
	}
}

?>
