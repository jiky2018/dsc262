<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
if (defined('WEBSITE')) {
	global $_LANG;
	$_LANG['help']['APP_KEY'] = '在支付宝中申请的 APP ID';
	$_LANG['help']['APP_SECRET'] = '在支付宝中申请的 KEY';
	$_LANG['APP_KEY'] = 'Partner ID';
	$_LANG['APP_SECRET'] = '安全校验码';
	$i = (isset($web) ? count($web) : 0);
	$web[$i]['name'] = '支付宝';
	$web[$i]['type'] = 'alipay';
	$web[$i]['className'] = 'alipay';
	$web[$i]['author'] = '`Dream`';
	$web[$i]['qq'] = '0000210';
	$web[$i]['email'] = '0000210@ecmoban.com';
	$web[$i]['website'] = 'http://open.alipay.com';
	$web[$i]['version'] = '2.7v';
	$web[$i]['date'] = '2013-11-5';
	$web[$i]['config'] = array(
	array('type' => 'text', 'name' => 'APP_KEY', 'value' => ''),
	array('type' => 'text', 'name' => 'APP_SECRET', 'value' => '')
	);
}

if (!defined('WEBSITE')) {
	include 'oath2.class.php';
	class website extends oath2
	{
		public $partner = '';
		public $key = '';
		public $alipay_url = 'https://mapi.alipay.com/gateway.do?';
		public $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do';
		public $input_charset = 'utf-8';
		public $transport = 'http';
		public $sign_type = 'MD5';
		public $token;
		public $error_msg;
		public $parameter = array();

		public function __construct()
		{
			$this->website();
		}

		public function website()
		{
			$this->partner = APP_KEY;
			$this->key = APP_SECRET;
			$this->parameter = array('_input_charset' => trim(strtolower($this->input_charset)), 'partner' => trim($this->partner), 'return_url' => '', 'service' => 'alipay.auth.authorize', 'target_service' => 'user.auth.quick.login');
		}

		public function login($return_url)
		{
			$url = (0 < strpos($return_url, '?') ? $return_url . '&time=' . time() : $return_url . '?time=' . time());
			$this->parameter['return_url'] = $url;
			return $this->getUrl();
		}

		public function getAccessToken()
		{
			$mysign['is_success'] = $_REQUEST['is_success'];
			$mysign['notify_id'] = $_REQUEST['notify_id'];
			$mysign['real_name'] = $_REQUEST['real_name'];
			$mysign['token'] = $_REQUEST['token'];
			$mysign['user_id'] = $_REQUEST['user_id'];
			$mysign['sign'] = $_REQUEST['sign'];
			$mysign['sign_type'] = $_REQUEST['sign_type'];
			$mysign = $this->getMysign($mysign);
			$responseTxt = 'true';

			if (!empty($_REQUEST['notify_id'])) {
				$p = array();
				$p['partner'] = $this->partner;
				$p['notify_id'] = $_REQUEST['notify_id'];
				$responseTxt = $this->OAthou($this->http_verify_url, $p);
			}

			if (preg_match('/true$/i', $responseTxt) && ($mysign == $_REQUEST['sign'])) {
				return $_REQUEST;
			}

			$this->error('J102', '验证sign错误');
			return false;
		}

		public function setAccessToken($token)
		{
			$this->token = $token;
			return true;
		}

		public function getMessage()
		{
			$ret = array();
			$ret['name'] = $this->token['real_name'];
			$ret['sex'] = 0;
			$ret['user_id'] = $this->token['user_id'];
			$ret['img'] = '';
			$ret['rank_id'] = RANK_ID;
			if (defined('EC_CHARSET') && (EC_CHARSET == 'gbk')) {
				$info = $this->togbk($info);
			}

			return $ret;
		}

		public function getUrl()
		{
			$para_filter = $this->paraFilter($this->parameter);
			$para_sort = $this->argSort($para_filter);
			$mysign = $this->buildMysign($para_sort, $this->key, $this->sign_type);
			$para_filter['return_url'] = urlencode($para_filter['return_url']);
			$para_filter['sign'] = $mysign;
			$para_filter['sign_type'] = $this->sign_type;
			return $this->alipay_url . $this->createLinkstring($para_filter);
		}

		public function paraFilter($para)
		{
			$para_filter = array();

			while (list($key, $val) = each($para)) {
				if (($key == 'sign') || ($key == 'sign_type') || ($val == '')) {
					continue;
				}
				else {
					$para_filter[$key] = $para[$key];
				}
			}

			return $para_filter;
		}

		public function sign($prestr, $sign_type = 'MD5')
		{
			$sign = '';

			if ($sign_type == 'MD5') {
				$sign = md5($prestr);
			}

			return $sign;
		}

		public function buildMysign($sort_para, $key, $sign_type = 'MD5')
		{
			$prestr = $this->createLinkstring($sort_para);
			$prestr = $prestr . $key;
			$mysgin = $this->sign($prestr, $sign_type);
			return $mysgin;
		}

		public function argSort($para)
		{
			ksort($para);
			reset($para);
			return $para;
		}

		public function createLinkstring($para)
		{
			$arg = '';

			while (list($key, $val) = each($para)) {
				$arg .= $key . '=' . $val . '&';
			}

			$arg = substr($arg, 0, count($arg) - 2);

			if (get_magic_quotes_gpc()) {
				$arg = stripslashes($arg);
			}

			return $arg;
		}

		public function getMysign($para_temp)
		{
			$para_filter = $this->paraFilter($para_temp);
			$para_sort = $this->argSort($para_filter);
			$mysign = $this->buildMysign($para_sort, $this->key, $this->sign_type);
			return $mysign;
		}

		public function OAthou($url, $meth = array())
		{
			return $this->http($url, 'POST', $meth);
		}

		public function error($code, $message, $string = '')
		{
			$this->add_error($code, $message, $string);
		}
	}
}

?>
