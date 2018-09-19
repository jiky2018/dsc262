<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
if (defined('WEBSITE')) {
	global $_LANG;
	$_LANG['help']['APP_KEY'] = '在 open.weibo.com 里申请的 App Key';
	$_LANG['help']['APP_SECRET'] = '请注意填写，最长的就填此处';
	$_LANG['help']['weibo_version'] = '勾选使用 Oauth2.0版本 验证方法 , 否则采用 1.0a 版本验证方案';
	$_LANG['APP_KEY'] = 'App Key';
	$_LANG['APP_SECRET'] = 'App Secret';
	$_LANG['weibo_version'] = '是否使用Oauth2.0版本';
	$i = isset($web) ? count($web) : 0;
	$web[$i]['name'] = '新浪微博';
	$web[$i]['type'] = 'weibo';
	$web[$i]['author'] = '`Dream`';
	$web[$i]['className'] = 'weibo';
	$web[$i]['qq'] = '0000210';
	$web[$i]['email'] = '0000210@ecmoban.com';
	$web[$i]['website'] = 'http://open.weibo.com';
	$web[$i]['version'] = '2.7v';
	$web[$i]['date'] = '2013-11-5';
	$web[$i]['config'] = array(
	array('type' => 'text', 'name' => 'APP_KEY', 'value' => ''),
	array('type' => 'text', 'name' => 'APP_SECRET', 'value' => '')
	);
}

if (!defined('WEBSITE')) {
	include_once dirname(__FILE__) . '/oath2.class.php';
	class website extends oath2
	{
		public function __construct()
		{
			$this->app_key = APP_KEY;
			$this->app_secret = APP_SECRET;
			$this->tokenURL = 'https://api.weibo.com/oauth2/access_token';
			$this->authorizeURL = 'https://api.weibo.com/oauth2/authorize';
			$this->display = 'popup';
			$this->userURL = 'https://api.weibo.com/2/users/show.json';
			$this->meth = 'POST';
		}

		public function sign(&$p)
		{
			$this->meth = 'GET';
			$this->id_format($p['uid']);
		}

		public function message($info)
		{
			$arr = array();
			$arr['user_id'] = $info['id'];
			$arr['name'] = empty($info['screen_name']) ? $info['name'] : $info['screen_name'];
			$arr['location'] = $info['location'];
			$arr['sex'] = $info['gender'] == 'm' ? 1 : 0;
			$arr['img'] = empty($info['avatar_large']) ? '' : $info['avatar_large'];
			$arr['lang'] = $info['lang'];
			$arr['info'] = $info;
			return $arr;
		}

		public function is_error($result)
		{
			$msg = json_decode($result, true);

			if (empty($msg['error_code'])) {
				return $msg;
			}

			if (is_array($msg)) {
				$this->add_error($msg['error_code'], $msg['error'], $msg['request'] . ' - ' . (isset($msg['error_description']) ? $msg['error_description'] : '') . ' - ' . $str);
			}

			return false;
		}

		public function id_format(&$id)
		{
			if (is_float($id)) {
				$id = number_format($id, 0, '', '');
			}
			else if (is_string($id)) {
				$id = trim($id);
			}
		}
	}
}

?>
