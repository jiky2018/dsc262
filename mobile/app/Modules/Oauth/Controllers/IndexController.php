<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Oauth\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	public function __construct()
	{
		parent::__construct();
		L(require LANG_PATH . C('shop.lang') . '/other.php');
		$this->assign('lang', array_change_key_case(L()));
		$this->load_helper('passport');
	}

	public function actionIndex()
	{
		$type = I('get.type', '', array('trim', 'html_in'));
		$refer = I('get.refer', '', array('trim', 'html_in'));
		$back_url = I('get.back_url', '', array('htmlspecialchars', 'urldecode'));
		$back_url = strip_tags(html_out($back_url));
		$user_id = input('get.user_id', 0, 'intval');
		$file = ADDONS_PATH . 'connect/' . $type . '.php';

		if (file_exists($file)) {
			include_once $file;
		}
		else {
			show_message(L('msg_plug_notapply'), L('msg_go_back'), url('user/login/index'));
		}

		$url = url('/', array(), false, true);
		$url = rtrim($url, 'index.php');

		if (0 < $user_id) {
			$param = array('m' => 'oauth', 'type' => $type, 'user_id' => $user_id, 'back_url' => empty($back_url) ? url('user/index/index') : $back_url);
		}
		else {
			$param = array('m' => 'oauth', 'type' => $type, 'refer' => $refer, 'back_url' => empty($back_url) ? url('user/index/index') : $back_url);
		}

		$url .= 'index.php?' . http_build_query($param, '', '&');
		$config = $this->getOauthConfig($type);

		if (!$config) {
			show_message(L('msg_plug_notapply'), L('msg_go_back'), url('user/login/index'));
		}

		$obj = new $type($config);
		if (isset($_GET['code']) && $_GET['code'] != '') {
			if ($res = $obj->callback($url, $_GET['code'])) {
				$back_url = strip_tags(html_out($back_url));
				$param = get_url_query($back_url);

				if (isset($param['u'])) {
					$up_uid = get_affiliate();
					$res['parent_id'] = !empty($param['u']) && $param['u'] == $up_uid ? intval($param['u']) : 0;
					$res['drp_parent_id'] = !empty($param['u']) && $param['u'] == $up_uid ? intval($param['u']) : 0;
				}

				if (isset($param['d'])) {
					$up_drpid = get_drp_affiliate();
					$res['drp_parent_id'] = !empty($param['d']) && $param['d'] == $up_drpid ? intval($param['d']) : 0;
					$res['parent_id'] = !empty($param['d']) && $param['d'] == $up_drpid ? intval($param['d']) : 0;
				}

				session('unionid', $res['unionid']);
				$_SESSION['oauth_info'] = $res;
				if (isset($_SESSION['user_id']) && 0 < $user_id && $_SESSION['user_id'] == $user_id && !empty($res['unionid'])) {
					$back_url = empty($back_url) ? url('user/profile/account_safe') : $back_url;

					if ($this->UserBind($res, $user_id, $type) === true) {
						redirect($back_url);
					}
					else {
						show_message(L('msg_account_bound'), L('msg_rebound'), $back_url, 'error');
					}
				}
				else {
					if ($this->oauthLogin($res, $type) === true) {
						redirect($back_url);
					}

					if (!empty($_SESSION['unionid']) && isset($_SESSION['unionid']) || $res['unionid']) {
						if (!empty($refer) && $refer == 'user') {
							$this->redirect('oauth/index/bindregister', array('type' => $type, 'back_url' => $back_url));
						}

						if ($this->UpdateWechatUser($res, $type) === true) {
							redirect($back_url);
						}
					}
					else {
						show_message(L('msg_author_register_error'), L('msg_go_back'), url('user/login/index'), 'error');
					}
				}
			}
			else {
				show_message(L('msg_authoriza_error'), L('msg_go_back'), url('user/login/index'), 'error');
			}

			return NULL;
		}

		$url = $obj->redirect($url);
		redirect($url);
	}

	public function actionBindRegister()
	{
		if (IS_POST) {
			$mobile = input('mobile', '', array('trim', 'html_in'));
			$sms_code = input('mobile_code', '', array('trim', 'html_in'));
			$type = input('type', '', array('trim', 'html_in'));
			$back_url = input('back_url', '', array('htmlspecialchars', 'urldecode'));
			$back_url = empty($back_url) ? url('user/index/index') : $back_url;
			$back_url = strip_tags(html_out($back_url));

			if (empty($mobile)) {
				exit(json_encode(array('status' => 'n', 'info' => L('mobile_notnull'))));
			}

			if (is_mobile($mobile) == false) {
				exit(json_encode(array('status' => 'n', 'info' => L('mobile_format_error'))));
			}

			if (C('shop.sms_signin') == 1) {
				if ($mobile != $_SESSION['sms_mobile'] || $sms_code != $_SESSION['sms_mobile_code']) {
					exit(json_encode(array('status' => 'n', 'info' => L('mobile_auth_code_error'))));
				}
			}

			$res = $_SESSION['oauth_info'];
			$res['mobile_phone'] = $mobile;
			$userinfo = get_connect_user($res['unionid']);

			if (!empty($userinfo)) {
				if (empty($userinfo['mobile_phone'])) {
					$user_data = array('mobile_phone' => $res['mobile_phone']);
					dao('users')->data($user_data)->where(array('user_id' => $userinfo['user_id']))->save();
				}

				$this->doLogin($userinfo['user_name']);
				exit(json_encode(array('status' => 'y', 'info' => L('正在登录...'), 'url' => $back_url)));
			}
			else {
				$map['mobile_phone'] = $mobile;
				$map['user_name'] = $mobile;
				$map['_logic'] = 'OR';
				$user_connect = dao('users')->alias('u')->join(C('DB_PREFIX') . 'connect_user cu on u.user_id = cu.user_id')->field('u.user_id, u.user_name, u.mobile_phone')->where($map)->find();

				if (!empty($user_connect)) {
					exit(json_encode(array('status' => 'n', 'info' => L('该手机号已被注册或绑定'), 'url' => $back_url)));
				}

				$condition['mobile_phone'] = $mobile;
				$condition['user_name'] = $mobile;
				$condition['_logic'] = 'OR';
				$users = dao('users')->field('user_id, user_name, mobile_phone')->where($condition)->find();

				if (!empty($users)) {
					if (C('shop.sms_signin') == 1 && $mobile == $_SESSION['sms_mobile'] && $sms_code == $_SESSION['sms_mobile_code']) {
						$res['user_id'] = $users['user_id'];
						update_connnect_user($res, $type);
						$userinfo = get_connect_user($res['unionid']);

						if (!empty($userinfo)) {
							$this->doLogin($userinfo['user_name']);
							exit(json_encode(array('status' => 'y', 'info' => L('验证成功'), 'url' => $back_url)));
						}
					}
					else {
						exit(json_encode(array('status' => 'n', 'info' => L('change_mobile'))));
					}
				}

				$result = $this->doRegister($res, $type);

				if ($result == true) {
					exit(json_encode(array('status' => 'y', 'info' => L('验证成功'), 'url' => $back_url)));
				}
				else {
					exit(json_encode(array('status' => 'n', 'info' => L('验证失败'), 'url' => $back_url)));
				}
			}

			return NULL;
		}

		$type = input('type', '', array('trim', 'html_in'));
		$back_url = input('back_url', '', array('htmlspecialchars', 'urldecode'));
		$back_url = empty($back_url) ? url('user/index/index') : $back_url;
		$back_url = strip_tags(html_out($back_url));
		$oauth_info = $_SESSION['oauth_info'];

		if (empty($oauth_info)) {
			show_message(L('请先授权登录'), L('msg_go_back'), url('user/login/index'), 'error');
		}

		$this->assign('oauth_info', $oauth_info);
		$this->assign('type', $type);
		$this->assign('back_url', $back_url);
		$this->assign('sms_signin', C('shop.sms_signin'));
		$this->assign('page_title', L('验证手机号'));
		$this->display();
	}

	protected function UserBind($res, $user_id, $type)
	{
		$users = dao('users')->field('user_id, user_name')->where(array('user_id' => $user_id))->find();
		if ($users && !empty($res['unionid'])) {
			$connect_user_id = dao('connect_user')->where(array('open_id' => $res['unionid'], 'connect_code' => 'sns_' . $type))->getField('user_id');
			if (0 < $connect_user_id && $connect_user_id != $users['user_id']) {
				return false;
			}

			$res['user_id'] = $users['user_id'];
			update_connnect_user($res, $type);
			if (is_dir(APP_WECHAT_PATH) && is_wechat_browser() && $type == 'wechat') {
				$res['openid'] = session('openid');
				update_wechat_user($res, 1);
			}

			$this->doLogin($users['user_name']);
			return true;
		}
		else {
			return false;
		}
	}

	protected function getOauthConfig($type)
	{
		$sql = 'SELECT auth_config FROM {pre}touch_auth WHERE `type` = \'' . $type . '\' AND `status` = 1';
		$info = $this->db->getRow($sql);

		if ($info) {
			$res = unserialize($info['auth_config']);
			$config = array();

			foreach ($res as $key => $value) {
				$config[$value['name']] = $value['value'];
			}

			return $config;
		}

		return false;
	}

	protected function oauthLogin($res, $type)
	{
		$older_user = dao('users')->field('user_name, user_id')->where(array('aite_id' => $type . '_' . $res['unionid']))->find();

		if (!empty($older_user)) {
			dao('users')->data(array('aite_id' => ''))->where(array('user_id' => $older_user['user_id']))->save();
			$res['user_id'] = $older_user['user_id'];
			update_connnect_user($res, $type);
		}

		$userinfo = get_connect_user($res['unionid']);

		if ($userinfo) {
			if ($userinfo && empty($userinfo['mobile_phone'])) {
				$this->redirect('oauth/index/bindregister', array('type' => $type));
				exit();
			}

			$this->doLogin($userinfo['user_name']);
			$res['user_id'] = !empty($userinfo['user_id']) ? $userinfo['user_id'] : $_SESSION['user_id'];
			update_connnect_user($res, $type);
			if (is_dir(APP_WECHAT_PATH) && is_wechat_browser() && $type == 'wechat') {
				$res['openid'] = session('openid');
				update_wechat_user($res, 1);
			}

			return true;
		}
		else {
			return false;
		}
	}

	protected function doLogin($username)
	{
		$this->users->set_session($username);
		$this->users->set_cookie($username);
		update_user_info();
		recalculate_price();
	}

	protected function doRegister($res, $type = '')
	{
		$username = get_wechat_username($res['unionid'], $type);
		$password = mt_rand(100000, 999999);
		$email = $username . '@qq.com';
		$extends = array('nick_name' => !empty($res['nickname']) ? $res['nickname'] : '', 'sex' => !empty($res['sex']) ? $res['sex'] : 0, 'user_picture' => !empty($res['headimgurl']) ? $res['headimgurl'] : '', 'mobile_phone' => !empty($res['mobile_phone']) ? $res['mobile_phone'] : '');

		if (is_dir(APP_WECHAT_PATH)) {
			$wechat_user = dao('wechat_user')->field('drp_parent_id, parent_id')->where(array('unionid' => $res['unionid']))->find();

			if (!empty($wechat_user)) {
				if (is_dir(APP_DRP_PATH)) {
					$res['drp_parent_id'] = 0 < $wechat_user['drp_parent_id'] ? $wechat_user['drp_parent_id'] : 0;
				}

				$res['parent_id'] = 0 < $wechat_user['parent_id'] ? $wechat_user['parent_id'] : 0;
			}
		}

		if (is_dir(APP_DRP_PATH)) {
			$extends['drp_parent_id'] = 0 < $res['drp_parent_id'] ? $res['drp_parent_id'] : 0;
		}

		$extends['parent_id'] = 0 < $res['parent_id'] ? $res['parent_id'] : 0;
		$userinfo = get_connect_user($res['unionid']);

		if (empty($userinfo)) {
			if (register($username, $password, $email, $extends) !== false) {
				$res['user_id'] = session('user_id');
				update_connnect_user($res, $type);
				if (is_dir(APP_WECHAT_PATH) && is_wechat_browser() && $type == 'wechat') {
					$res['openid'] = session('openid');
					update_wechat_user($res);
					$this->sendBonus();
				}

				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	public function UpdateWechatUser($res, $type = '')
	{
		if (is_dir(APP_WECHAT_PATH) && is_wechat_browser() && $type == 'wechat') {
			$res['openid'] = session('openid');
			update_wechat_user($res);
		}

		return true;
	}

	protected function sendBonus()
	{
		$wxinfo = dao('wechat')->field('id, token, appid, appsecret, encodingaeskey')->where(array('default_wx' => 1, 'status' => 1))->find();

		if ($wxinfo) {
			$rs = $this->db->query('SELECT name, keywords, command, config FROM {pre}wechat_extend WHERE command = \'bonus\' and enable = 1 and wechat_id = ' . $wxinfo['id'] . ' ORDER BY id ASC');
			$addons = reset($rs);
			$file = APP_PATH . 'Wechat/Plugins/' . ucfirst($addons['command']) . '/' . ucfirst($addons['command']) . '.php';

			if (file_exists($file)) {
				require_once $file;
				$new_command = '\\App\\Modules\\Wechat\\Plugins\\' . ucfirst($addons['command']) . '\\' . ucfirst($addons['command']);
				$wechat = new $new_command();
				$data = $wechat->returnData($_SESSION['openid'], $addons);

				if (!empty($data)) {
					$config['token'] = $wxinfo['token'];
					$config['appid'] = $wxinfo['appid'];
					$config['appsecret'] = $wxinfo['appsecret'];
					$config['encodingaeskey'] = $wxinfo['encodingaeskey'];
					$weObj = new \App\Extensions\Wechat($config);
					$weObj->sendCustomMessage($data['content']);
				}
			}
		}
	}

	public function actionMergeUsers()
	{
		if ($_SESSION['user_id']) {
			if (IS_POST) {
				$username = I('username', '', array('trim', 'html_in'));
				$form = new \App\Extensions\Form();

				if ($form->isMobile($username, 1)) {
					$user_name = dao('users')->field('user_name')->where(array('mobile_phone' => $username))->find();
					$username = $user_name['user_name'];
				}

				if ($form->isEmail($username, 1)) {
					$user_name = dao('users')->field('user_name')->where(array('email' => $username))->find();
					$username = $user_name['user_name'];
				}

				$password = I('password', '', array('htmlspecialchars', 'trim'));
				$back_url = I('back_url', '', 'urldecode');
				if (!$form->isEmpty($username, 1) || !$form->isEmpty($password, 1)) {
					show_message(L('msg_input_namepwd'), L('msg_go_back'), '', 'error');
				}

				$from_user_id = $_SESSION['user_id'];
				$new_user_id = $this->users->check_user($username, $password);

				if (0 < $new_user_id) {
					$from_connect_user = dao('connect_user')->field('user_id')->where(array('user_id' => $from_user_id))->select();

					if (!empty($from_connect_user)) {
						foreach ($from_connect_user as $key => $value) {
							dao('connect_user')->where('user_id = ' . $value['user_id'])->setField('user_id', $new_user_id);
						}
					}

					if (is_dir(APP_WECHAT_PATH)) {
						$from_wechat_user = dao('wechat_user')->field('ect_uid')->where(array('ect_uid' => $from_user_id))->find();

						if (!empty($from_wechat_user)) {
							dao('wechat_user')->where('ect_uid = ' . $from_wechat_user['ect_uid'])->setField('ect_uid', $new_user_id);
						}
					}

					$res = merge_user($from_user_id, $new_user_id);

					if ($res == true) {
						$this->users->logout();
						$back_url = empty($back_url) ? url('user/index/index') : $back_url;
						show_message(L('logout'), array(L('back_up_page'), '返回首页'), array($back_url, url('/')), 'success');
					}

					return NULL;
				}
				else {
					show_message(L('msg_account_bound_fail'), L('msg_rebound'), '', 'error');
				}

				return NULL;
			}

			$back_url = I('back_url', '', array('htmlspecialchars', 'urldecode'));
			$this->assign('back_url', $back_url);
			$this->assign('page_title', '重新绑定帐号');
			$this->display();
		}
		else {
			show_message('请登录', L('msg_go_back'), url('user/login/index'), 'error');
		}
	}
}

?>
