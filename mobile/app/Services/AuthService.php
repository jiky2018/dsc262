<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services;

class AuthService
{
	private $request;
	private $userRepository;
	private $WechatUserRepository;
	private $WxappConfigRepository;

	public function __construct(\App\Repositories\User\UserRepository $userRepository, \App\Repositories\Wechat\WechatUserRepository $WechatUserRepository, \App\Repositories\Wechat\WxappConfigRepository $WxappConfigRepository)
	{
		$this->userRepository = $userRepository;
		$this->WechatUserRepository = $WechatUserRepository;
		$this->WxappConfigRepository = $WxappConfigRepository;
	}

	public function loginMiddleWare(array $request)
	{
		$this->request = $request['userinfo'];
		$result = $this->wxLogin($request);
		if (isset($result['token']) && isset($result['unionid'])) {
			return $result;
		}

		return false;
	}

	private function wxLogin($req)
	{
		$userInfo = $req['userinfo'];
		$config = array('appid' => $this->WxappConfigRepository->getWxappConfigByCode('wx_appid'), 'secret' => $this->WxappConfigRepository->getWxappConfigByCode('wx_appsecret'));
		$wxapp = new \App\Extensions\Wxapp($config);
		$token = $wxapp->getAccessToken();
		$response = $wxapp->getOauthOrization($req['code']);
		$pc = new \App\Api\Support\WXBizDataCrypt($config['appid'], $response['session_key']);
		$errCode = $pc->decryptData($userInfo['encryptedData'], $userInfo['iv'], $data);

		if ($errCode == 0) {
			print($data . "\n");
		}
		else {
			print($errCode . "\n");
		}

		$data = get_object_vars(json_decode($data));

		if (!isset($data['unionId'])) {
			if ($wxapp->errCode == '40029') {
				$wxapp->log($wxapp->errMsg);
			}

			return false;
		}

		$connectUser = $this->userRepository->getConnectUser($data['unionId']);
		$args['unionid'] = $data['unionId'];
		$args['openid'] = $data['openId'];
		$args['nickname'] = isset($userInfo['userInfo']['nickName']) ? $userInfo['userInfo']['nickName'] : '';
		$args['sex'] = isset($userInfo['userInfo']['gender']) ? $userInfo['userInfo']['gender'] : '';
		$args['province'] = isset($userInfo['userInfo']['province']) ? $userInfo['userInfo']['province'] : '';
		$args['city'] = isset($userInfo['userInfo']['city']) ? $userInfo['userInfo']['city'] : '';
		$args['country'] = isset($userInfo['userInfo']['country']) ? $userInfo['userInfo']['country'] : '';
		$args['headimgurl'] = isset($userInfo['userInfo']['avatarUrl']) ? $userInfo['userInfo']['avatarUrl'] : '';

		if (empty($connectUser)) {
			$result = $this->createUser($args);

			if ($result['error_code'] == 0) {
				$args['user_id'] = $result['user_id'];
				if ($args['user_id'] && $args['unionid']) {
					$this->creatConnectUser($args);
					$this->creatWechatUser($args);
				}
			}
		}

		$args['user_id'] = !empty($args['user_id']) ? $args['user_id'] : $connectUser['user_id'];
		$this->updateUser($args);
		$this->connectUserUpdate($args);
		$this->wechatUserUpdate($args);
		$token = \App\Api\Foundation\Token::encode(array('uid' => $args['user_id']));
		return array('token' => $token, 'openid' => $args['openid'], 'unionid' => $args['unionid']);
	}

	public function createUser($args)
	{
		$username = 'wx' . substr(md5($args['unionid']), -5) . substr(time(), 0, 4) . mt_rand(1000, 9999);
		$newUser = array('user_name' => $username, 'password' => $this->generatePassword(mt_rand(100000, 999999)), 'email' => $username . '@qq.com');
		$extends = array('nick_name' => $args['nickname'], 'sex' => $args['sex'], 'user_picture' => $args['headimgurl'], 'reg_time' => gmtime());

		if (!\App\Models\Users::where(array('user_name' => $username))->first()) {
			$model = new \App\Models\Users();
			$data = array_merge($newUser, $extends);
			$model->fill($data);

			if ($model->save()) {
				$token = \App\Api\Foundation\Token::encode(array('uid' => $model->user_id));
				return array('error_code' => 0, 'token' => $token, 'user_id' => $model->user_id);
			}
			else {
				return array('error_code' => 1, 'msg' => '创建用户失败');
			}
		}
		else {
			return array('error_code' => 1, 'msg' => '用户已存在');
		}
	}

	public function updateUser($args)
	{
		$data = array('user_id' => $args['user_id'], 'nick_name' => $args['nickname'], 'sex' => $args['sex'], 'user_picture' => $args['headimgurl']);
		$res = $this->userRepository->renewUser($data);
		return $res;
	}

	public function creatConnectUser($args, $type = 'wechat')
	{
		$profile = array('nickname' => $args['nickname'], 'sex' => $args['sex'], 'province' => $args['province'], 'city' => $args['city'], 'country' => $args['country'], 'headimgurl' => $args['headimgurl']);
		$data = array('connect_code' => 'sns_' . $type, 'user_id' => $args['user_id'], 'open_id' => $args['unionid'], 'profile' => serialize($profile), 'create_at' => gmtime());
		$res = $this->userRepository->addConnectUser($data);
		return $res;
	}

	public function connectUserUpdate($args, $type = 'wechat')
	{
		$profile = array('nickname' => $args['nickname'], 'sex' => $args['sex'], 'province' => $args['province'], 'city' => $args['city'], 'country' => $args['country'], 'headimgurl' => $args['headimgurl']);
		$data = array('connect_code' => 'sns_' . $type, 'user_id' => $args['user_id'], 'open_id' => $args['unionid'], 'profile' => serialize($profile));
		$res = $this->userRepository->updateConnnectUser($data);
		return $res;
	}

	public function creatWechatUser($args)
	{
		$data = array('nickname' => $args['nickname'], 'sex' => $args['sex'], 'city' => $args['city'], 'country' => isset($args['country']) ? $args['country'] : '', 'province' => $args['province'], 'language' => isset($args['language']) ? $args['language'] : '', 'headimgurl' => $args['headimgurl'], 'remark' => isset($args['remark']) ? $args['remark'] : '', 'openid' => $args['openid'], 'unionid' => $args['unionid'], 'ect_uid' => $args['user_id']);
		$res = $this->WechatUserRepository->addWechatUser($data);
		return $res;
	}

	public function wechatUserUpdate($args)
	{
		$data = array('nickname' => $args['nickname'], 'sex' => $args['sex'], 'city' => $args['city'], 'country' => isset($args['country']) ? $args['country'] : '', 'province' => $args['province'], 'language' => isset($args['language']) ? $args['language'] : '', 'headimgurl' => $args['headimgurl'], 'remark' => isset($args['remark']) ? $args['remark'] : '', 'openid' => $args['openid'], 'unionid' => $args['unionid']);
		$res = $this->WechatUserRepository->updateWechatUser($data);
		return $res;
	}

	public function generatePassword($password, $salt = false)
	{
		if ($salt) {
			return md5(md5($password) . $salt);
		}

		return md5($password);
	}

	public function authorization()
	{
		$token = $_SERVER[strtoupper('HTTP_X_' . app('config')->get('app.name') . '_Authorization')];

		if (empty($token)) {
			return array('error' => 1, 'msg' => strtolower('header parameter `x-' . app('config')->get('app.name') . '-authorization` is required'));
		}

		if ($payload = \App\Api\Foundation\Token::decode($token)) {
			if (is_object($payload) && property_exists($payload, 'uid')) {
				return $payload->uid;
			}
		}

		if ($payload == 10002) {
			return array('error' => 1, 'msg' => 'token-expired');
		}

		return array('error' => 1, 'msg' => 'token-illegal');
	}

	public function wxappPushTemplate($code = '', $content = array(), $url = '', $uid = 0, $form_id)
	{
		$config = array('appid' => $this->WxappConfigRepository->getWxappConfigByCode('wx_appid'), 'secret' => $this->WxappConfigRepository->getWxappConfigByCode('wx_appsecret'));
		$wxapp = new \App\Extensions\Wxapp($config);
		$template = $this->WxappConfigRepository->getTemplateInfo($code);

		if ($template['status'] == 1) {
			$user = $this->userRepository->getUserOpenid($uid);
			$openid = $user['openid'];
			$data['touser'] = $openid;
			$data['template_id'] = $template['wx_template_id'];
			$data['page'] = $url;
			$data['FORMID'] = $form_id;
			$data['data'] = $content;
			$data['color'] = '#FF0000';
			$data['emphasis_keyword'] = '';
			$result = $wxapp->sendTemplateMessage($data);

			if (empty($result)) {
				return false;
			}
			else {
				return true;
			}
		}
		else {
			return false;
		}
	}
}


?>
