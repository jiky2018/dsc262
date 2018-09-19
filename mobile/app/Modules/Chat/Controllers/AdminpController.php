<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Chat\Controllers;

class AdminpController extends \App\Modules\Base\Controllers\FrontendController
{
	protected $config = array();
	private $user;

	public function _initialize()
	{
		$this->config = load_config(ROOT_PATH . 'config/chat.php');
	}

	public function actionMobile()
	{
		$protocol = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off' ? 'https://' : 'http://';

		if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
			$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
		}
		else if (isset($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
		}
		else {
			if (isset($_SERVER['SERVER_PORT'])) {
				$port = ':' . $_SERVER['SERVER_PORT'];
				if (':80' == $port && 'http://' == $protocol || ':443' == $port && 'https://' == $protocol) {
					$port = '';
				}
			}
			else {
				$port = '';
			}

			if (isset($_SERVER['SERVER_NAME'])) {
				$host = $_SERVER['SERVER_NAME'] . $port;
			}
			else if (isset($_SERVER['SERVER_ADDR'])) {
				$host = $_SERVER['SERVER_ADDR'] . $port;
			}
		}

		$domain = $protocol . $host . __ROOT__ . '/';
		$this->assign('domain', $domain);
		$this->display('mobile');
	}

	public function actionIndex()
	{
		$this->userInfo();
		$result['code'] = 0;
		$service = array('id' => $this->user['service_id']);

		if (empty($this->user['service_id'])) {
			$result['code'] = 1;
			$result['message'] = '该账号没有客服权限';
			$this->ajaxReturn($result);
		}

		if ($service['chat_status'] == 1) {
			$result['code'] = 1;
			$result['message'] = '客服已登录';
			$this->ajaxReturn($result);
		}

		$messageList = \App\Modules\Chat\Models\Kefu::getChatLog($service);
		$result['message_list'] = $messageList;
		$id = $service['id'];
		$status = 1;
		$status = in_array($status, array(0, 1)) ? $status : 0;
		$data['chat_status'] = $status;
		M('im_service')->where('id=' . $id . '  AND status = 1')->save($data);
		$this->ajaxReturn($result);
	}

	public function actionInitInfo()
	{
		$this->userInfo();
		$result = array(
			'code'    => 0,
			'message' => '',
			'data'    => array()
			);
		$listen_route = $this->config['listen_route'];

		if (empty($this->config['port'])) {
			$result['code'] = 1;
			$result['message'] = 'socket端口号未配置';
			$this->ajaxReturn($result);
		}

		$result['data']['listen_route'] = $listen_route;
		$result['data']['port'] = $this->config['port'];
		$storeId = $this->getStoreIdByServiceId($this->user['service_id']);
		$storeInfo = \App\Modules\Chat\Models\Kefu::getStoreInfo($storeId);
		$result['data']['avatar'] = $storeInfo['logo_thumb'];
		$result['data']['user_name'] = $storeInfo['shop_name'];
		$service = \App\Modules\Chat\Models\Kefu::getServiceById($this->user['service_id']);
		$result['data']['nick_name'] = $service['nick_name'];
		$result['data']['user_id'] = $this->user['service_id'];
		$result['data']['store_id'] = $storeId;
		$result['data']['is_ssl'] = is_ssl();
		$this->ajaxReturn($result);
	}

	public function actionVisit()
	{
		$this->userInfo();
		$serviceId = $this->user['service_id'];
		$storeId = $this->getStoreIdByServiceId($serviceId);
		$waitMessageArr = \App\Modules\Chat\Models\Kefu::getWait($storeId);
		if (count($waitMessageArr['waitMessage']) === 1 && empty($waitMessageArr['waitMessage'][0]['id'])) {
			$waitMessageArr['waitMessage'] = array();
		}

		$result = array('code' => 0, 'message_list' => $waitMessageArr['waitMessageDataList'], 'visit_list' => $waitMessageArr['waitMessage'], 'total' => $waitMessageArr['total']);
		$this->ajaxReturn($result);
	}

	public function actionChatList()
	{
		$this->userInfo();
		$serviceId = $this->user['service_id'];
		$userId = I('user_id', 0, 'intval');
		$rootUrl = dirname(__ROOT__);
		$storeId = $this->getStoreIdByServiceId($serviceId);
		$page = I('page', 1, 'intval');

		if (6 < $page) {
			$this->ajaxReturn(json_encode(array('error' => 1, 'content' => '没有更多了')));
		}

		$default_size = 3;
		$size = 10;
		$type = I('type', 0, 'intval');

		if ($type === 'default') {
			$page = 1;
			$size = $default_size;
		}

		$serArr = $this->getServiceIdByRuId($storeId);
		$serArr = implode(',', $serArr);
		$sql = 'SELECT id, IF(from_user_id = ' . $userId . ", to_user_id, from_user_id) as service_id, message, user_type, from_user_id, to_user_id, dialog_id,\r\n from_unixtime(add_time) as add_time, status FROM " . \App\Modules\Chat\Models\Kefu::$pre . 'im_message WHERE ((from_user_id = ' . $userId . ' AND to_user_id IN (' . $serArr . ')) OR (to_user_id = ' . $userId . ' AND from_user_id IN (' . $serArr . '))) AND to_user_id <> 0 ORDER BY add_time DESC, id DESC';
		$default = I('default', 0, 'intval');
		$start = ($page - 1) * $size;

		if ($default == 1) {
			$start += $default_size;
		}

		$sql .= ' limit ' . $start . ', ' . $size;
		$services = $this->db->getAll($sql);

		foreach ($services as $k => $v) {
			if ($v['user_type'] == 1) {
				$sql = 'SELECT s.nick_name, i.logo_thumb FROM ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_service s' . ' LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'admin_user u ON s.user_id = u.user_id' . ' LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'seller_shopinfo i ON i.ru_id = u.ru_id' . ' WHERE s.id = ' . $v['from_user_id'];
				$nickName = $this->db->getRow($sql);
				$services[$k]['name'] = get_shop_name($storeId, 1);

				if (strpos($nickName['logo_thumb'], 'http') !== false) {
					$services[$k]['avatar'] = $nickName['logo_thumb'];
				}
				else if (empty($nickName['logo_thumb'])) {
					$services[$k]['avatar'] = __PUBLIC__ . '/assets/chat/images/service.png';
				}
				else {
					$services[$k]['avatar'] = $nickName['logo_thumb'];
				}
			}
			else if ($v['user_type'] == 2) {
				$users = get_wechat_user_info($v['from_user_id']);
				$services[$k]['name'] = $users['nick_name'];

				if (empty($users['user_picture'])) {
					$services[$k]['avatar'] = __PUBLIC__ . '/assets/chat/images/avatar.png';
				}
				else if (strpos($users['user_picture'], 'http') !== false) {
					$services[$k]['avatar'] = $users['user_picture'];
				}
				else {
					$services[$k]['avatar'] = rtrim($rootUrl, '/') . '/' . $users['user_picture'];
				}
			}

			$services[$k]['message'] = htmlspecialchars_decode($v['message']);
			$services[$k]['time'] = $v['add_time'];
			$services[$k]['id'] = $v['id'];
		}

		$did = $services[0]['dialog_id'];
		$result['goods'] = '';

		if (!empty($did)) {
			$sql = 'SELECT g.goods_id, goods_name, goods_thumb, shop_price as goods_price FROM ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_dialog d';
			$sql .= ' LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'goods g on d.goods_id = g.goods_id';
			$sql .= ' WHERE d.id = ' . $did;
			$goods = $this->db->getRow($sql);
			$goods['goods_price'] = price_format($goods['goods_price'], true);
			$goods['goods_thumb'] = get_image_path($goods['goods_thumb']);
			$goods['goods_url'] = rtrim($rootUrl, '/') . '/goods.php?id=' . $goods['goods_id'];

			if (empty($goods['goods_id'])) {
				$result['goods'] = null;
			}
			else {
				$result['goods'] = $goods;
			}
		}

		$result['code'] = 0;
		$result['message_list'] = $services;
		$this->ajaxReturn($result);
	}

	public function actionGoodsInfo()
	{
		$rootUrl = dirname(__ROOT__);
		$this->userInfo();
		$gid = I('gid', 0, 'intval');

		if ($gid == 0) {
			$this->ajaxReturn(array('error' => 1, 'content' => 'invalid params'));
		}

		$data = \App\Modules\Chat\Models\Kefu::getGoods($gid);
		$data['goods_url'] = rtrim($rootUrl, '/') . '/goods.php?id=' . $gid;
		$data['goods_thumb'] = get_image_path($data['goods_thumb']);
		$data['goods_price'] = price_format($data['shop_price'], true);
		unset($data['shop_price']);
		$result = array('code' => 0, 'goods_info' => $data);
		$this->ajaxReturn($result);
	}

	public function actionServiceInfo()
	{
		$this->userInfo();
		$result = array('code' => 0, 'message' => '', 'data' => '');
		$id = $this->user['service_id'];
		$service = \App\Modules\Chat\Models\Kefu::getServiceById($id);

		if (empty($service)) {
			$result['code'] = 1;
			$result['message'] = '客服信息错误';
			$this->ajaxReturn($result);
		}

		$admin = \App\Modules\Chat\Models\Kefu::getAdmin($service['user_id']);

		if (empty($admin)) {
			$result['code'] = 1;
			$result['message'] = '管理员信息错误';
			$this->ajaxReturn($result);
		}

		$store = \App\Modules\Chat\Models\Kefu::getStoreInfo($admin['ru_id']);
		$result['data'] = array('nick_name' => $service['nick_name'], 'user_name' => $admin['user_name'], 'service_avatar' => $store['logo_thumb']);
		$this->ajaxReturn($result);
	}

	private function getStoreIdByServiceId($serviceId)
	{
		$sql = 'SELECT u.ru_id FROM ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_service' . ' s' . ' LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'admin_user' . ' u ON s.user_id = u.user_id' . (' WHERE s.id = ' . $serviceId);
		$ruId = $this->db->getOne($sql);
		return $ruId;
	}

	private function getServiceIdByRuId($storeId)
	{
		$sql = 'SELECT s.id FROM ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_service' . ' s' . ' LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'admin_user' . ' u ON s.user_id = u.user_id' . (' WHERE u.ru_id = ' . $storeId);
		$serArr = $this->db->getCol($sql);
		return $serArr;
	}

	public function actionLogout()
	{
		$this->userInfo();
		$result = array('code' => 0, 'message' => '退出成功');
		$id = $this->user['service_id'];

		if (empty($id)) {
			$result['code'] = 1;
			$result['message'] = '验证失败';
			$this->ajaxReturn($result);
		}

		$this->logoutStatus();
		$this->ajaxReturn($result);
	}

	private function logoutStatus()
	{
		$id = $this->user['service_id'];
		$data['chat_status'] = 0;
		M('im_service')->where('id=' . $id . '  AND status = 1')->save($data);
	}

	private function userInfo()
	{
		$result = array('code' => 0);
		$token = $_SERVER['HTTP_TOKEN'];
		$data = $this->tokenDecode($token);

		if ($data) {
			$userId = base64_decode(hex2bin($data['id']));
			$expire = $data['expire'];
			$time = local_gettime();

			if ($expire < $time) {
				$result['code'] = 1;
				$result['message'] = '用户登录已失效';
				$user = array('service_id' => $userId);
				$this->user = $user;
				$this->logoutStatus();
				$this->ajaxReturn($result);
			}

			$hash = $data['hash'];

			if (C('DB_HOST') . C('DB_USER') . C('DB_PWD') . C('DB_NAME') == $hash) {
				$result['code'] = 1;
				$result['message'] = '验证未通过';
				$this->ajaxReturn($result);
			}

			$user = array('service_id' => $userId);
			$this->user = $user;
		}
		else {
			$result['code'] = 1;
			$result['message'] = '验证未通过';
			$this->ajaxReturn($result);
		}
	}

	private function tokenDecode($token)
	{
		try {
			$data = json_decode(base64_decode(unserialize($token)), 1);

			if (!is_array($data)) {
				return false;
			}

			return $data;
		}
		catch (\Exception $e) {
			return false;
		}
	}
}

?>
