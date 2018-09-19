<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Http\Proxy;

class ShippingProxy
{
	/**
     * @var string
     */
	private $queryExpressUrl = 'https://m.kuaidi100.com/query?type=%s&postid=%s';

	public function getExpress($com = '', $num = '')
	{
		$url = sprintf($this->queryExpressUrl, $com, $num);
		$response = \App\Extensions\Http::doGet($url, 5, $this->defaultHeader($com, $num));
		$result = json_decode($response, true);

		if ($result['message'] === 'ok') {
			return array('error' => 0, 'data' => $result['data']);
		}
		else {
			return array('error' => 403, 'data' => $result['message']);
		}
	}

	public function defaultHeader($com = '', $nu = '')
	{
		$header = "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.%d Safari/537.%d\r\n";
		$header .= "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
		$header .= "Accept-Language: zh-cn,zh;q=0.5\r\n";
		$header .= "Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n";
		$header .= "Host: m.kuaidi100.com\r\n";
		$header .= 'Referer: https://m.kuaidi100.com/result.jsp?com=' . $com . '&nu=' . $nu . "\r\n";
		$header .= "X-Requested-With: XMLHttpRequest\r\n";
		return sprintf($header, time(), time() + rand(1000, 9999));
	}
}


?>
