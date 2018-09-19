<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Chat\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	protected $config = array();

	public function _initialize()
	{
		$this->config = load_config(ROOT_PATH . 'config/chat.php');
	}

	public function actionIndex()
	{
		$shop_id = I('ru_id', 0, 'intval');
		$goods_id = I('goods_id', 0, 'intval');
		$rootUrl = dirname(__ROOT__);
		$this->load_helper('code');
		$token = I('token');
		$dbhash = md5(rtrim(dirname(ROOT_PATH), '/') . '/' . C('DB_HOST') . ':' . C('DB_PORT') . C('DB_USER') . C('DB_PWD') . C('DB_NAME'));
		$user_token = unserialize(base64_decode($token));

		if ($user_token['hash'] === md5($user_token['user_name'] . date('YmdH') . $dbhash)) {
			$this->users->set_session($user_token['user_name']);
			$this->users->set_cookie($user_token['user_name']);
			update_user_info();
			recalculate_price();
		}

		$uid = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

		if (empty($uid)) {
			$this->redirect('user/login/index');
		}

		$user = dao('users')->field('user_id, email, user_name, nick_name, user_picture')->where(array('user_id' => $uid))->find();

		if (empty($user)) {
			$this->redirect('/', 3, '没有用户');
		}

		if (empty($user['user_picture'])) {
			$user['avatar'] = __PUBLIC__ . '/assets/chat/images/avatar.png';
		}
		else if (strpos($user['user_picture'], 'http') !== false) {
			$user['avatar'] = $user['user_picture'];
		}
		else {
			$user['avatar'] = rtrim($rootUrl, '/') . '/' . $user['user_picture'];
		}

		$user['user_name'] = !empty($user['nick_name']) ? $user['nick_name'] : $user['user_name'];
		$this->assign('user', $user);

		if ($goods_id) {
			$goods = dao('goods')->field('goods_id, goods_name, shop_price, goods_thumb, user_id')->where(array('goods_id' => $goods_id))->find();

			if (!empty($goods)) {
				$goods['goods_thumb'] = get_image_path($goods['goods_thumb']);
			}

			$this->assign('goods', $goods);
		}

		$shop_id = isset($goods['user_id']) && !empty($goods['user_id']) ? $goods['user_id'] : $shop_id;
		$dialog = dao('im_dialog')->field('id, goods_id, customer_id, services_id, store_id, status')->where(array('customer_id' => $uid))->where(array('services_id <> 0'))->order('start_time desc')->find();
		if (!empty($dialog) && $dialog['store_id'] == $shop_id) {
			$this->assign('status', $dialog['status']);
			$this->assign('services_id', $dialog['services_id']);
		}

		$shopinfo = get_shop_name($shop_id, 2);
		$shopinfo['ru_id'] = $shop_id;
		$shopinfo['shop_name'] = get_shop_name($shop_id, 1);
		$shopinfo['logo_thumb'] = get_image_path(str_replace('../', '', $shopinfo['logo_thumb']));
		$this->assign('shopinfo', $shopinfo);

		if (empty($this->config['listen_route'])) {
			$listen_route = $this->getServerIp();
		}
		else {
			$listen_route = $this->config['listen_route'];
		}

		if (empty($this->config['port'])) {
			show_message('socket端口号未配置');
		}

		$this->assign('listen_route', $listen_route);
		$this->assign('port', $this->config['port']);
		$sql = 'UPDATE ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_message m ' . ' LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_dialog d ON d.id = m.dialog_id' . ' SET m.status = 0' . ' WHERE m.status = 1 AND m.to_user_id = ' . $uid . ' AND d.store_id = ' . $shop_id;
		$this->db->query($sql);

		if (0 < $shop_id) {
			$sql = 'SELECT * FROM {pre}merchants_shop_information as a JOIN {pre}seller_shopinfo as b ON a.user_id = b.ru_id WHERE user_id = ' . $shop_id;
			$data = $this->db->getRow($sql);
			$sql = 'SELECT count(user_id) as a FROM {pre}collect_store WHERE ru_id = ' . $data['user_id'];
			$follow = $this->db->getOne($sql);
			$info = $this->shopdata($data);
			$info['count_gaze'] = intval($follow);
		}
		else {
			$sql = 'SELECT shop_address, kf_tel FROM {pre}seller_shopinfo WHERE ru_id = 0';
			$data = $this->db->getRow($sql);
			$info = array('shop_name' => $shopinfo['shop_name'], 'shop_desc' => $shopinfo['shop_name'], 'shop_start' => '', 'shop_address' => $data['shop_address'], 'shop_tel' => $data['kf_tel']);
		}

		$this->assign('shop_info', $info);
		$this->assign('title', '在线客服 - ' . $shopinfo['shop_name']);
		$this->display('index.' . (is_mobile_browser() ? 'mobile' : 'desktop'));
	}

	public function actionOrderList()
	{
		$result = array('code' => 0, 'msg' => '', 'order_list' => '');
		$ruId = I('uid', 0, 'intval');
		$uid = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
		$start = I('start', 0, 'intval');
		$num = I('num', 10, 'intval');

		if (empty($uid)) {
			$result['code'] = 1;
			$result['msg'] = '参数错误';
			return $result;
		}

		$sql = 'SELECT oi.order_sn, (oi.goods_amount + oi.shipping_fee + oi.insure_fee + oi.pay_fee + oi.pack_fee + oi.card_fee + oi.tax - oi.discount) as order_amount, oi.add_time as order_time, g.goods_id, g.goods_name, g.goods_thumb FROM {pre}order_info oi';
		$sql .= ' LEFT JOIN {pre}order_goods og ON oi.order_id = og.order_id';
		$sql .= ' LEFT JOIN {pre}goods g ON g.goods_id = og.goods_id';
		$sql .= ' WHERE oi.user_id = ' . $uid . ' AND g.user_id = ' . $ruId . ' ORDER BY oi.order_id DESC LIMIT ' . $start . ', ' . $num;
		$goodsList = $this->db->getAll($sql);

		if (dirname(__ROOT__) != '/') {
			$rootPath = dirname(__ROOT__);
		}
		else {
			$rootPath = '';
		}

		foreach ($goodsList as $k => $v) {
			$goodsList[$k]['goods_thumb'] = get_image_path($v['goods_thumb']);
			$goodsList[$k]['order_amount'] = price_format($v['order_amount'], true);
			$goodsList[$k]['order_time'] = date('Y年m月d日', $v['order_time']);
			$goodsList[$k]['goods_url'] = $rootPath . '/goods.php?id=' . $v['goods_id'];
		}

		$result['order_list'] = $goodsList;
		return $this->ajaxReturn($result);
	}

	private function shopdata($data = array())
	{
		$user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;

		if (empty($user_id)) {
			return false;
		}

		$shop_expiredatestart = strtotime($data['shop_expiredatestart']);
		$info['count_goods'] = $this->sql('user_id =' . $user_id . '   AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0' . $this->review_goods);
		$info['count_goods_new'] = $this->sql('is_new = 1 AND user_id=' . $user_id . '   AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0' . $this->review_goods);
		$info['count_goods_promote'] = $this->sql('is_promote = 1 AND user_id=' . $user_id . '   AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0' . $this->review_goods);
		$info['count_bonus'] = $this->sql($user_id, '');
		$info['bonus_all'] = $this->sql($user_id, '', 1);
		$info['shop_id'] = $data['shop_id'];
		$info['ru_id'] = $data['user_id'];
		$info['shop_logo'] = get_image_path(ltrim($data['logo_thumb'], '../'));
		$info['street_thumb'] = get_image_path(ltrim($data['street_thumb'], '../'));
		$info['shop_name'] = get_shop_name($data['user_id'], 1);
		$info['shop_desc'] = $data['shop_name'];
		$info['shop_start'] = date('Y年m月d日', $shop_expiredatestart);
		$info['shop_address'] = $data['shop_address'];
		$info['shop_flash'] = get_image_path($data['street_thumb']);
		$info['shop_wangwang'] = $this->dokf($data['kf_ww']);
		$info['shop_qq'] = $this->dokf($data['kf_qq']);
		$info['shop_tel'] = $data['kf_tel'];
		$info['is_im'] = $data['is_im'];
		$info['self_run'] = $data['self_run'];
		$info['meiqia'] = $data['meiqia'];
		$info['kf_appkey'] = $data['kf_appkey'];

		if (0 < $data['user_id']) {
			$merchants_goods_comment = get_merchants_goods_comment($data['user_id']);
		}

		if (0 < $_SESSION['user_id']) {
			$sql = 'SELECT rec_id FROM {pre}collect_store WHERE user_id = ' . $_SESSION['user_id'] . ' AND ru_id = ' . $data['shop_id'];
			$status = $this->db->getOne($sql);
			$status = 0 < $status ? 'active' : '';
		}

		$info['commentrank'] = $merchants_goods_comment['cmt']['commentRank']['zconments']['score'] . '分';
		$info['commentserver'] = $merchants_goods_comment['cmt']['commentServer']['zconments']['score'] . '分';
		$info['commentdelivery'] = $merchants_goods_comment['cmt']['commentDelivery']['zconments']['score'] . '分';
		$info['commentrank_font'] = $this->font($merchants_goods_comment['cmt']['commentRank']['zconments']['score']);
		$info['commentserver_font'] = $this->font($merchants_goods_comment['cmt']['commentServer']['zconments']['score']);
		$info['commentdelivery_font'] = $this->font($merchants_goods_comment['cmt']['commentDelivery']['zconments']['score']);
		$info['gaze_status'] = $status;
		return $info;
	}

	public function actionChatList()
	{
		$uid = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

		if (empty($uid)) {
			$this->error('请先登录');
		}

		$sql = 'SELECT d.id as dialog_id, s.id as service_id, u.user_id as admin_id, u.ru_id, i.logo_thumb, i.shop_name FROM ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_dialog d ';
		$sql .= ' LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_service s ON s.id = d.services_id';
		$sql .= ' LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'admin_user u ON u.user_id = s.user_id';
		$sql .= ' LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'seller_shopinfo i ON i.ru_id = u.ru_id';
		$sql .= ' WHERE  d.customer_id = ' . $uid . ' GROUP BY services_id ';
		$serId = $this->db->getAll($sql);
		$store = array();

		foreach ($serId as $k => $v) {
			if ($v['ru_id'] == '') {
				continue;
			}

			$store[$v['ru_id']][$v['service_id']] = $v;
			$store[$v['ru_id']]['logo_thumb'] = get_image_path(ltrim($v['logo_thumb'], '../'));
			$store[$v['ru_id']]['shop_name'] = $v['shop_name'];
		}

		$storeMessage = array();

		foreach ($store as $k => $v) {
			$storeMessage[$k]['ru_id'] = $k;
			$storeMessage[$k]['thumb'] = $v['logo_thumb'];
			$storeMessage[$k]['shop_name'] = $v['shop_name'];
			unset($v['logo_thumb']);
			unset($v['shop_name']);
			$serviceId = implode(',', array_keys($v));

			if (empty($serviceId)) {
				continue;
			}

			$sql = 'SELECT count(*) FROM ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_message WHERE (from_user_id in (' . $serviceId . ')  AND to_user_id =' . $uid . ') AND status = 1';
			$storeMessage[$k]['count'] = $this->db->getOne($sql);
			$sql = 'SELECT message, from_unixtime(add_time) as add_time, from_user_id, to_user_id, user_type FROM ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_message WHERE (from_user_id in (' . $serviceId . ')  AND to_user_id =' . $uid . ') OR (to_user_id in (' . $serviceId . ')  AND from_user_id =' . $uid . ')  ORDER BY add_time DESC limit 1';
			$res = $this->db->getRow($sql);
			$storeMessage[$k]['message'] = htmlspecialchars_decode($res['message']);
			$storeMessage[$k]['add_time'] = $res['add_time'];
			$storeMessage[$k]['service_id'] = $res['user_type'] == 2 ? $res['to_user_id'] : $res['from_user_id'];
		}

		if (IS_AJAX) {
			$this->ajaxReturn($storeMessage);
		}
		else {
			$this->assign('message', $storeMessage);
			$this->display();
		}
	}

	public function actionSingleChatList()
	{
		$uid = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : I('uid');
		$store_id = I('store_id', 0, 'intval');
		$rootUrl = dirname(__ROOT__);
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

		$serArr = $this->getServiceIdByRuId($store_id);
		$serArr = implode(',', $serArr);
		$sql = 'SELECT id, IF(from_user_id = ' . $uid . ", to_user_id, from_user_id) as service_id, message, user_type, from_user_id, to_user_id,\r\n from_unixtime(add_time) as add_time, status FROM " . \App\Modules\Chat\Models\Kefu::$pre . 'im_message WHERE ((from_user_id = ' . $uid . ' AND to_user_id IN (' . $serArr . ')) OR (to_user_id = ' . $uid . ' AND from_user_id IN (' . $serArr . '))) AND to_user_id <> 0 ORDER BY add_time DESC, id DESC';
		$default = I('default', 0, 'intval');
		$start = ($page - 1) * $size;

		if ($default == 1) {
			$start += $default_size;
		}

		if (1 < $page) {
			$start -= $size;
		}

		$sql .= ' limit ' . $start . ', ' . $size;
		$services = $this->db->getAll($sql);

		foreach ($services as $k => $v) {
			if ($v['user_type'] == 1) {
				$sql = 'SELECT s.nick_name, i.logo_thumb FROM ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_service s' . ' LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'admin_user u ON s.user_id = u.user_id' . ' LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'seller_shopinfo i ON i.ru_id = u.ru_id' . ' WHERE s.id = ' . $v['from_user_id'];
				$nickName = $this->db->getRow($sql);
				$services[$k]['name'] = get_shop_name($store_id, 1);
				$services[$k]['avatar'] = $this->formatImage($nickName['logo_thumb']);
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

		if (strtolower(ACTION_NAME) == 'servicechatdata') {
			return $services;
		}

		$this->ajaxReturn(json_encode($services));
	}

	private function getServiceIdByRuId($store_id)
	{
		$sql = 'SELECT s.id FROM ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_service' . ' s' . ' LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'admin_user' . ' u ON s.user_id = u.user_id' . (' WHERE u.ru_id = ' . $store_id);
		$serArr = $this->db->getCol($sql);
		return $serArr;
	}

	public function actionServiceChatData()
	{
		$uid = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : I('uid');
		$serviceId = I('id', 0, 'intval');
		$goodsId = I('goods_id', 0, 'intval');
		$sql = 'SELECT u.ru_id FROM ' . \App\Modules\Chat\Models\Kefu::$pre . 'admin_user u LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_service s ON u.user_id = s.user_id WHERE s.id = ' . $serviceId;
		$res = $this->db->getRow($sql);
		if (empty($res) || empty($res['ru_id'])) {
			$res['ru_id'] = 0;
		}

		$_GET['store_id'] = $res['ru_id'];
		$services = $this->actionSingleChatList();
		$sql = 'UPDATE ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_message m ' . ' LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_dialog d ON d.id = m.dialog_id' . ' SET m.status = 0' . ' WHERE m.status = 1 AND m.to_user_id = ' . $uid . ' AND d.store_id = ' . $res['ru_id'];
		$this->db->query($sql);

		if ($serviceId == 0) {
			$sql = 'SELECT goods_thumb, goods_sn, goods_name, goods_id FROM ' . \App\Modules\Chat\Models\Kefu::$pre . 'goods  WHERE goods_id = ' . $goodsId;
			$goods = $this->db->getRow($sql);
		}
		else {
			$sql = 'SELECT g.goods_thumb, g.goods_sn, g.goods_name, g.goods_id FROM ' . \App\Modules\Chat\Models\Kefu::$pre . 'im_dialog d';
			$sql .= ' LEFT JOIN ' . \App\Modules\Chat\Models\Kefu::$pre . 'goods g ON d.goods_id = g.goods_id';
			$sql .= ' WHERE d.customer_id = ' . $uid . ' AND d.services_id = ' . $serviceId;
			$sql .= ' ORDER BY d.id DESC LIMIT 1';
			$goods = $this->db->getRow($sql);
		}

		if (dirname(__ROOT__) != '/') {
			$rootPath = dirname(__ROOT__);
		}
		else {
			$rootPath = '';
		}

		if (!empty($goods)) {
			$goods['goods_thumb'] = get_image_path($goods['goods_thumb']);
			$goods['goods_url'] = $rootPath . '/goods.php?id=' . $goods['goods_id'];
		}

		$this->ajaxReturn(array('goods' => $goods, 'chat' => $services));
	}

	public function getServerIp()
	{
		if (isset($_SERVER)) {
			if ($_SERVER['SERVER_ADDR']) {
				$server_ip = $_SERVER['SERVER_ADDR'];
			}
			else {
				$server_ip = $_SERVER['LOCAL_ADDR'];
			}
		}
		else {
			$server_ip = getenv('SERVER_ADDR');
		}

		return $server_ip;
	}

	public function formatImage($pic = '')
	{
		return __PUBLIC__ . '/assets/chat/images/service.png';
	}

	public function actionSendImage()
	{
		$uid = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

		if (empty($uid)) {
			$arr = array('code' => 100, 'msg' => '请先登录');
			$this->ajaxReturn($arr);
		}

		$path = 'images/upload/images/' . date('Ymd');
		$result = $this->upload($path, true, 2);

		if ($result['error'] == 0) {
			$arr = array(
				'code' => 0,
				'msg'  => '上传成功',
				'data' => array('src' => get_image_path($result['url']), 'title' => '')
				);
			$this->ajaxReturn($arr);
		}
	}
}

?>
