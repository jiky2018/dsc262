<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class ClusterTopClient extends TopClient
{
	static private $dnsconfig;
	static private $syncDate = 0;
	static private $applicationVar;
	static private $cfgDuration = 10;

	public function __construct($appkey = '', $secretKey = '')
	{
		ClusterTopClient::$applicationVar = new ApplicationVar();
		$this->appkey = $appkey;
		$this->secretKey = $secretKey;
		$saveConfig = ClusterTopClient::$applicationVar->getValue();

		if ($saveConfig) {
			$tmpConfig = $saveConfig['dnsconfig'];
			ClusterTopClient::$dnsconfig = $this->object_to_array($tmpConfig);
			unset($tmpConfig);
			ClusterTopClient::$syncDate = $saveConfig['syncDate'];

			if (!ClusterTopClient::$syncDate) {
				ClusterTopClient::$syncDate = 0;
			}
		}
	}

	public function __destruct()
	{
		if (ClusterTopClient::$dnsconfig && ClusterTopClient::$syncDate) {
			ClusterTopClient::$applicationVar->setValue('dnsconfig', ClusterTopClient::$dnsconfig);
			ClusterTopClient::$applicationVar->setValue('syncDate', ClusterTopClient::$syncDate);
			ClusterTopClient::$applicationVar->write();
		}
	}

	public function execute($request = NULL, $session = NULL, $bestUrl = NULL)
	{
		$currentDate = date('U');
		$syncDuration = $this->getDnsConfigSyncDuration();
		$bestUrl = $this->getBestVipUrl($this->gatewayUrl, $request->getApiMethodName(), $session);

		if (($syncDuration * 60) < ($currentDate - ClusterTopClient::$syncDate)) {
/* [31m * TODO SEPARATE[0m */
			$httpdns = new HttpdnsGetRequest();
			ClusterTopClient::$dnsconfig = json_decode(parent::execute($httpdns, NULL, $bestUrl)->result, true);
			$syncDate = date('U');
			ClusterTopClient::$syncDate = $syncDate;
		}

		return parent::execute($request, $session, $bestUrl);
	}

	private function getDnsConfigSyncDuration()
	{
		if (ClusterTopClient::$cfgDuration) {
			return ClusterTopClient::$cfgDuration;
		}

		if (!ClusterTopClient::$dnsconfig) {
			return ClusterTopClient::$cfgDuration;
		}

		$config = json_encode(ClusterTopClient::$dnsconfig);

		if (!$config) {
			return ClusterTopClient::$cfgDuration;
		}

		$config = ClusterTopClient::$dnsconfig['config'];
		$duration = $config['interval'];
		ClusterTopClient::$cfgDuration = $duration;
		return ClusterTopClient::$cfgDuration;
	}

	private function getBestVipUrl($url, $apiname = NULL, $session = NULL)
	{
		$config = ClusterTopClient::$dnsconfig['config'];
		$degrade = $config['degrade'];

		if (strcmp($degrade, 'true') == 0) {
			return $url;
		}

		$currentEnv = $this->getEnvByApiName($apiname, $session);
		$vip = $this->getVipByEnv($url, $currentEnv);

		if ($vip) {
			return $vip;
		}

		return $url;
	}

	private function getVipByEnv($comUrl, $currentEnv)
	{
		$urlSchema = parse_url($comUrl);

		if (!$urlSchema) {
			return NULL;
		}

		if (!ClusterTopClient::$dnsconfig['env']) {
			return NULL;
		}

		if (!array_key_exists($currentEnv, ClusterTopClient::$dnsconfig['env'])) {
			return NULL;
		}

		$hostList = ClusterTopClient::$dnsconfig['env'][$currentEnv];

		if (!$hostList) {
			return NULL;
		}

		$vipList = NULL;

		foreach ($hostList as $key => $value) {
			if ((strcmp($key, $urlSchema['host']) == 0) && (strcmp($value['proto'], $urlSchema['scheme']) == 0)) {
				$vipList = $value;
				break;
			}
		}

		$vip = $this->getRandomWeightElement($vipList['vip']);

		if ($vip) {
			return $urlSchema['scheme'] . '://' . $vip . $urlSchema['path'];
		}

		return NULL;
	}

	private function getEnvByApiName($apiName, $session = '')
	{
		$apiCfgArray = ClusterTopClient::$dnsconfig['api'];

		if ($apiCfgArray) {
			if (array_key_exists($apiName, $apiCfgArray)) {
				$apiCfg = $apiCfgArray[$apiName];
				$userFlag = $apiCfg['user'];
				$flag = $this->getUserFlag($session);
				if ($userFlag && $flag) {
					return $this->getEnvBySessionFlag($userFlag, $flag);
				}
				else {
					return $this->getRandomWeightElement($apiCfg['rule']);
				}
			}
		}

		return $this->getDeafultEnv();
	}

	private function getUserFlag($session)
	{
		if ($session && (5 < strlen($session))) {
			if (($session[0] == '6') || ($session[0] == '7')) {
				return $session[strlen($session) - 1];
			}
			else {
				if (($session[0] == '5') || ($session[0] == '8')) {
					return $session[5];
				}
			}
		}

		return NULL;
	}

	private function getEnvBySessionFlag($targetConfig, $flag)
	{
		if ($flag) {
			$userConf = ClusterTopClient::$dnsconfig['user'];
			$cfgArry = $userConf[$targetConfig];

			foreach ($cfgArry as $key => $value) {
				if (in_array($flag, $value)) {
					return $key;
				}
			}
		}
		else {
			return NULL;
		}
	}

	private function getRandomWeightElement($elements)
	{
		$totalWeight = 0;

		if ($elements) {
			foreach ($elements as $ele) {
				$weight = $this->getElementWeight($ele);
				$r = $this->randomFloat() * ($weight + $totalWeight);

				if ($totalWeight <= $r) {
					$selected = $ele;
				}

				$totalWeight += $weight;
			}

			if ($selected) {
				return $this->getElementValue($selected);
			}
		}

		return NULL;
	}

	private function getElementWeight($ele)
	{
		$params = explode('|', $ele);
		return floatval($params[1]);
	}

	private function getElementValue($ele)
	{
		$params = explode('|', $ele);
		return $params[0];
	}

	private function getDeafultEnv()
	{
		return ClusterTopClient::$dnsconfig['config']['def_env'];
	}

	static private function startsWith($haystack, $needle)
	{
		return ($needle === '') || (strpos($haystack, $needle) === 0);
	}

	private function object_to_array($obj)
	{
		$_arr = (is_object($obj) ? get_object_vars($obj) : $obj);

		foreach ($_arr as $key => $val) {
			$val = (is_array($val) || is_object($val) ? $this->object_to_array($val) : $val);
			$arr[$key] = $val;
		}

		return $arr;
	}

	private function randomFloat($min = 0, $max = 1)
	{
		return $min + ((mt_rand() / mt_getrandmax()) * ($max - $min));
	}
}

?>
