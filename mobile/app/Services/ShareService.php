<?php
//zend by QQ:123456  商创-网络  禁止倒卖 一经发现停止任何服务
namespace App\Services;

class ShareService
{
	private $ShareRepository;
	private $userRepository;
	private $WxappConfigRepository;

	public function __construct(\App\Repositories\User\UserRepository $userRepository, \App\Repositories\Share\ShareRepository $shareRepository, \App\Repositories\Wechat\WxappConfigRepository $WxappConfigRepository)
	{
		$this->userRepository = $userRepository;
		$this->shareRepository = $shareRepository;
		$this->WxappConfigRepository = $WxappConfigRepository;
	}

	public function Share($uid, $path = '/', $width = 430, $type = 'index')
	{
		$app_name = $this->WxappConfigRepository->getWxappConfig();
		$shop_name = $app_name[0]['wx_appname'];
		$userInfo = $this->userRepository->userInfo($uid);
		$result = $this->get_wxcode($path, $width);
		$rootPath = dirname(base_path());
		$imgDir = $rootPath . '/data/gallery_album/ewm/';

		if (!is_dir($imgDir)) {
			mkdir($imgDir);
		}

		$qrcode = $imgDir . $type . '_' . $uid . '.png';
		file_put_contents($qrcode, $result);
		$rootPath = app('request')->root();
		$rootPath = dirname(dirname($rootPath)) . '/';
		$image_name = $rootPath . 'data/gallery_album/ewm/' . basename($qrcode);
		$share = array('name' => $userInfo['nick_name'], 'id' => $userInfo['id'], 'pic' => get_image_path($userInfo['user_picture']), 'shop_name' => $shop_name, 'image_name' => get_image_path($image_name));
		return $share;
	}

	private function get_wxcode($path, $width)
	{
		$config = array('appid' => $this->WxappConfigRepository->getWxappConfigByCode('wx_appid'), 'secret' => $this->WxappConfigRepository->getWxappConfigByCode('wx_appsecret'));
		$wxapp = new \App\Extensions\Wxapp($config);
		$result = $wxapp->getWxaCode($path, $width, true);

		if (empty($result)) {
			return false;
		}

		return $result;
	}
}


?>
