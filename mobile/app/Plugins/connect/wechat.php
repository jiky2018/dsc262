<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class wechat
{
	private $wechat = '';
	private $options = array();

	public function __construct($config)
	{
		$options = array('appid' => $config['app_id'], 'appsecret' => $config['app_secret']);
		$this->wechat = new \App\Extensions\Wechat($options);
	}

	public function redirect($callback_url, $state = 'wechat_oauth', $snsapi = 'snsapi_userinfo')
	{
		if (is_dir(APP_WECHAT_PATH) && is_wechat_browser() && isset($_COOKIE['wechat_ru_id'])) {
			$snsapi = 'snsapi_base';
			$state = 'repeat';
		}

		$_SESSION['state'] = $state;
		return $this->wechat->getOauthRedirect($callback_url, $state, $snsapi);
	}

	public function callback($callback_url, $code)
	{
		if (!empty($code)) {
			if ($_REQUEST['state'] != $_SESSION['state']) {
				return false;
			}

			$token = $this->wechat->getOauthAccessToken();
			$userinfo = $this->wechat->getOauthUserinfo($token['access_token'], $token['openid']);
			if (!empty($userinfo) && !empty($userinfo['unionid'])) {
				include 'emoji.php';
				$userinfo['nickname'] = strip_tags(emoji_unified_to_html($userinfo['nickname']));
				$_SESSION['openid'] = $userinfo['openid'];
				$_SESSION['nickname'] = $userinfo['nickname'];
				$_SESSION['headimgurl'] = $userinfo['headimgurl'];
				$data = array('unionid' => $userinfo['unionid'], 'nickname' => $userinfo['nickname'], 'sex' => $userinfo['sex'], 'headimgurl' => $userinfo['headimgurl'], 'city' => $userinfo['city'], 'province' => $userinfo['province'], 'country' => $userinfo['country']);
				if (is_dir(APP_WECHAT_PATH) && is_wechat_browser()) {
					update_wechat_unionid($userinfo);
				}

				return $data;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
}

defined('IN_ECTOUCH') || exit('Deny Access');
$payment_lang = LANG_PATH . C('shop.lang') . '/connect/' . basename(__FILE__);

if (file_exists($payment_lang)) {
	include_once $payment_lang;
	L($_LANG);
}

if (isset($set_modules) && $set_modules == true) {
	$i = isset($modules) ? count($modules) : 0;
	$modules[$i]['name'] = 'Wechat';
	$modules[$i]['type'] = 'wechat';
	$modules[$i]['className'] = 'wechat';
	$modules[$i]['author'] = 'ECTouch';
	$modules[$i]['qq'] = '800007167';
	$modules[$i]['email'] = 'support@ecmoban.com';
	$modules[$i]['website'] = 'http://open.weixin.qq.com';
	$modules[$i]['version'] = '2.0';
	$modules[$i]['date'] = '2017-03-22';
	$modules[$i]['config'] = array(
	array('type' => 'text', 'name' => 'app_id', 'value' => ''),
	array('type' => 'text', 'name' => 'app_secret', 'value' => '')
	);
	return NULL;
}

?>
