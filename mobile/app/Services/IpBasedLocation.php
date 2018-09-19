<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services;

class IpBasedLocation
{
	private $config;

	public function __construct(&$data = array())
	{
		$this->config = C('shop');
		$ip_type = $this->config['ip_type'] + 1;

		switch ($ip_type) {
		case '1':
			$area_name = $this->sian($data);
			break;

		case '2':
			$area_name = $this->taobao($data);
			break;

		case '3':
			$area_name = $this->tencent($data);
			break;
		}

		$area_name = str_replace(array('省', '市', '\''), '', $area_name);

		if (strstr($area_name, '香港')) {
			$area_name = '香港';
		}
		else if (strstr($area_name, '澳门')) {
			$area_name = '澳门';
		}
		else if (strstr($area_name, '内蒙古')) {
			$area_name = '内蒙古';
		}
		else if (strstr($area_name, '宁夏')) {
			$area_name = '宁夏';
		}
		else if (strstr($area_name, '新疆')) {
			$area_name = '新疆';
		}
		else if (strstr($area_name, '西藏')) {
			$area_name = '西藏';
		}
		else if (strstr($area_name, '广西')) {
			$area_name = '广西';
		}

		$data['city'] = $area_name;
	}

	private function taobao($data)
	{
		$url = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $data['ip'];
		$data = \App\Extensions\Http::doGet($url);
		$str = json_decode($data, true);
		if (!is_array($str) || ($data['ip'] == '127.0.0.1')) {
			if (!empty($this->config['shop_city'])) {
				$ip_city = get_shop_address($this->config['shop_city']);
				$str = array(
					'data' => array('city' => $ip_city, 'county' => '')
					);
			}
			else if (!empty($this->config['shop_province'])) {
				$ip_province = get_shop_address($this->config['shop_province']);
				$str = array(
					'data' => array('city' => '', 'county' => '', 'region' => $ip_province)
					);
			}
			else {
				$str = array(
					'data' => array('region' => '上海', 'city' => '', 'county' => '')
					);
			}
		}

		if (!empty($str['data']['county'])) {
			$region = $str['data']['county'];
		}
		else if (!empty($str['data']['city'])) {
			$region = $str['data']['city'];
		}
		else {
			$region = $str['data']['region'];
		}

		return $region;
	}

	private function sian($data)
	{
		$url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=' . $data['ip'];
		$data = \App\Extensions\Http::doGet($url);
		$str = json_decode($data, true);
		if (!is_array($str) || ($data['ip'] == '127.0.0.1')) {
			if (!empty($this->config['shop_city'])) {
				$ip_city = get_shop_address($this->config['shop_city']);
				$str = array('city' => $ip_city, 'province' => '');
			}
			else if (!empty($this->config['shop_province'])) {
				$ip_province = get_shop_address($this->config['shop_province']);
				$str = array('city' => '', 'province' => $ip_province);
			}
			else {
				$str = array('city' => '上海');
			}
		}

		if (!empty($str['city'])) {
			$region = $str['city'];
		}
		else {
			$region = $str['province'];
		}

		return $region;
	}

	private function tencent($data)
	{
		$url = 'http://apis.map.qq.com/ws/location/v1/ip?ip=' . $data['ip'] . '&key=' . $this->config['tengxun_key'];
		$data = \App\Extensions\Http::doGet($url);
		$str = json_decode($data, true);
		if (!is_array($str) || ($data['ip'] == '127.0.0.1')) {
			if (empty($str['result']['ad_info']['city']) && empty($str['result']['ad_info']['province'])) {
				if (!empty($this->config['shop_city'])) {
					$ip_city = get_shop_address($this->config['shop_city']);
					$str['result']['ad_info'] = array('city' => $ip_city, 'province' => '');
				}
				else if (!empty($this->config['shop_province'])) {
					$ip_province = get_shop_address($this->config['shop_province']);
					$str['result']['ad_info'] = array('city' => '', 'province' => $ip_province);
				}
				else {
					$str['result']['ad_info'] = array('city' => '上海');
				}
			}
		}

		if (!empty($str['result']['ad_info']['city'])) {
			$region = $str['result']['ad_info']['city'];
		}
		else {
			$region = $str['result']['ad_info']['province'];
		}

		return $region;
	}
}


?>
