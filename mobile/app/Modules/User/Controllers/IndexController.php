<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\User\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	public $user_id;

	public function __construct()
	{
		if (strtolower(ACTION_NAME) == 'addcomment') {
			$_SERVER['HTTP_USER_AGENT'] = 'AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';
		}

		parent::__construct();
		$this->user_id = $_SESSION['user_id'];
		$this->actionchecklogin();
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		L(require LANG_PATH . C('shop.lang') . '/flow.php');
		$this->assign('lang', array_change_key_case(L()));
		$files = array('clips', 'transaction', 'main');
		$this->load_helper($files);
		$this->assign('user_id', $this->user_id);
	}

	public function actionIndex()
	{
		$user_id = $this->user_id;
		$type = 0;
		$where_pay = ' AND oi.pay_status = ' . PS_UNPAYED . ' AND oi.order_status not in(' . OS_CANCELED . ',' . OS_INVALID . ',' . OS_RETURNED . ')';
		$pay_count = get_order_where_count($user_id, $type, $where_pay);
		$this->assign('pay_count', intval($pay_count));

		if (is_dir(APP_TEAM_PATH)) {
			$team_num = team_ongoing($user_id);
			$this->assign('team_num', intval($team_num));
		}

		$where_confirmed = ' AND oi.pay_status = ' . PS_PAYED . ' AND oi.order_status in (' . OS_CONFIRMED . ', ' . OS_SPLITED . ', ' . OS_SPLITING_PART . ') AND (oi.shipping_status >= ' . SS_UNSHIPPED . ' AND oi.shipping_status <> ' . SS_RECEIVED . ')';
		$confirmed_count = get_order_where_count($user_id, $type, $where_confirmed);
		$this->assign('confirmed_count', intval($confirmed_count));
		$sql = 'SELECT a.msg_id  FROM {pre}feedback AS a WHERE a.parent_id IN ' . ' (SELECT b.msg_id FROM {pre}feedback AS b WHERE b.user_id = \'' . $_SESSION['user_id'] . '\') ORDER BY a.msg_id DESC';
		$msg_ids = $this->db->getOne($sql);

		if (intval($cache_info) < intval($msg_ids)) {
			$cache_infos = 1;
		}

		$this->assign('cache_info', $cache_infos);
		$this->assign('admin_count', get_admin_feedback($_SESSION['user_id']));
		$this->assign('info', get_user_default($this->user_id));
		$this->assign('rank', get_rank_info());

		if ($rank = get_rank_info()) {
			$this->assign('rank', $rank);

			if (empty($rank)) {
				$this->assign('next_rank_name', sprintf(L('next_level'), $rank['next_rank'], $rank['next_rank_name']));
			}
		}

		$user_id = $_SESSION['user_id'];
		$c_sql = 'select count(*)  from ' . $GLOBALS['ecs']->table('coupons_user') . ('where is_use =0 and user_id = \'' . $user_id . '\'');
		$c_count = $GLOBALS['db']->getOne($c_sql);
		$this->assign('msg_list', msg_lists($this->user_id));
		$this->assign('goods_num', num_collection_goods($this->user_id));
		$this->assign('store_num', num_collection_store($this->user_id));
		$this->assign('bonus', my_bonus($this->user_id));
		$this->assign('couponses', $c_count);
		$this->assign('user_pay', pay_money($this->user_id));
		$this->assign('history', historys());
		$not_evaluated = get_user_order_comment_list($this->user_id, 1, 0);
		$this->assign('not_comment', intval($not_evaluated));
		$this->assign('page_title', L('user'));
		$return_count = get_count_return();
		$this->assign('return_count', $return_count);
		$this->assign('drp', is_dir(APP_DRP_PATH) ? 1 : 0);
		$this->assign('team', is_dir(APP_TEAM_PATH) ? 1 : 0);
		$this->assign('bargain', is_dir(APP_BARGAIN_PATH) ? 1 : 0);
		$this->assign('purchase', is_dir(APP_PURCHASE_PATH) ? 1 : 0);
		if ($GLOBALS['_CFG']['wholesale_user_rank'] == 0 && !$this->isSeller() || !is_dir(APP_DEMAND_PATH)) {
			$this->assign('demand', 0);
		}
		else {
			$this->assign('demand', 1);
		}

		$type = ' AND ga.is_finished= 0 ';
		$all_auction = get_all_auction($this->user_id, $type);

		if (cookie('all_auction') == 1) {
			$all_auction = '';
		}

		$this->assign('auction', $all_auction);
		$share = unserialize($GLOBALS['_CFG']['affiliate']);

		if ($share['on'] == 1) {
			$this->assign('share', '1');
		}

		$this->display();
	}

	public function actionHistory()
	{
		$arr = explode(',', $_COOKIE['ECS']['history_goods']);

		foreach ($arr as $key => $val) {
			$arry[$key] = explode('_', $val);
		}

		foreach ($arry as $v) {
			$a[] = $v[1];
		}

		array_multisort($a, SORT_DESC, $arry);

		foreach ($arry as $k => $v) {
			if ($v[0]) {
				$goods_list[$k] = dao('goods')->field('goods_id, goods_name, shop_price, goods_thumb')->where(array('goods_id' => $v[0]))->find();

				if ($goods_list[$k]['goods_id']) {
					$goods_list[$k]['goods_thumb'] = get_image_path($goods_list[$k]['goods_thumb']);
					$goods_list[$k]['shop_price'] = price_format($goods_list[$k]['shop_price']);
					$goods_list[$k]['url'] = url('goods/index/index', array('id' => $goods_list[$k]['goods_id']));
				}
			}
		}

		$key = 'goods_id';
		$goods_list = $this->arrayUnique($goods_list, $key);
		$count = count($goods_list);

		if (IS_AJAX) {
			$page = I('page', '1', 'intval');
			$offset = 10;
			$page_size = ceil($count / $offset);
			$limit = ' LIMIT ' . ($page - 1) * $offset . ',' . $offset;
			exit(json_encode(array('history' => $goods_list, 'totalPage' => $page_size, 'count' => $count)));
		}

		$this->assign('count', $count);
		$this->assign('page_title', L('history'));
		$this->display();
	}

	public function arrayUnique($arr, $key)
	{
		$tmp_arr = array();

		foreach ($arr as $k => $v) {
			if (in_array($v[$key], $tmp_arr)) {
				unset($arr[$k]);
			}
			else {
				$tmp_arr[$k] = $v[$key];
			}
		}

		return $arr;
	}

	public function actionClearHistory()
	{
		$status = input('status');
		if (IS_AJAX && $status == 1) {
			cookie('ECS[history_goods]', null);
			echo json_encode(array('y' => 1));
		}
	}

	public function actionchecklogin()
	{
		if ($_SESSION['user_id']) {
			$users = dao('users')->where(array('user_id' => $_SESSION['user_id']))->find();

			if (empty($users)) {
				$_SESSION['user_id'] = 0;
				$_SESSION['user_name'] = '';
				$_SESSION['email'] = '';
				$_SESSION['user_rank'] = 0;
				$_SESSION['discount'] = 1;
				$_SESSION['openid'] = '';
				$_SESSION['unionid'] = '';
			}
		}

		if (!$_SESSION['user_id']) {
			$back_act = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];
			$this->redirect('user/login/index', array('back_act' => urlencode($back_act)));
		}
	}

	public function actionProfile()
	{
		$sql = 'SELECT user_name,email, birthday, sex, question, answer, rank_points, pay_points,user_money, user_rank,' . ' msn, qq, office_phone, home_phone, mobile_phone, passwd_question, passwd_answer ' . ('FROM {pre}users WHERE user_id = \'' . $this->user_id . '\'');
		$infos = $this->db->getRow($sql);

		if ($infos['sex'] == 0) {
			$infos['sex'] = L('secrecy');
		}

		if ($infos['sex'] == 1) {
			$infos['sex'] = L('male');
		}

		if ($infos['sex'] == 2) {
			$infos['sex'] = L('female');
		}

		$this->assign('infos', $infos);
		$this->display();
	}

	public function actionEditPassword()
	{
		if (IS_POST) {
			$old_password = I('post.old_password');
			$new_passwords = I('post.new_password1');
			$new_password = I('post.new_password');

			if (empty($this->user_id)) {
				ecs_header('Location: ' . url('user/login/index'));
				exit();
			}

			if ($new_passwords !== $new_password) {
				show_message(L('confirm_password_invalid'), L('back_retry_answer'), url('user/index/edit_password'), 'warning');
			}

			$user_info = $this->users->get_profile_by_id($this->user_id);

			if (!$this->users->check_user($user_info['user_name'], $old_password)) {
				show_message(L('first_password_error'), L('back_retry_answer'), url('user/index/edit_password'), 'warning');
			}

			if (strlen($new_password) < 6) {
				show_message(L('password_shorter'), L('back_retry_answer'), url('user/index/edit_password'), 'warning');
			}

			if ($this->users->edit_user(array('username' => $user_info['user_name'], 'old_password' => $old_password, 'password' => $new_password), 0)) {
				$sql = 'UPDATE {pre}users SET `ec_salt`=\'0\' WHERE user_id= \'' . $this->user_id . '\'';
				$this->db->query($sql);
				unset($_SESSION['user_id']);
				unset($_SESSION['user_name']);
				$this->back_act = url('user/index/index');
				show_message(L('edit_profile_success'), L('back_login'), url('user/login/index', array('back_act' => $this->back_act)), 'success');
			}
		}

		$this->assign('page_title', L('edit_password'));
		if (isset($_SESSION['user_id']) && 0 < $_SESSION['user_id']) {
			$this->display();
		}
		else {
			ecs_header('Location: ' . url('user/index/edit_password'));
			exit();
		}
	}

	public function actionUpdate_mobile()
	{
		$result = array('error' => 0, 'message' => '');

		if (isset($_POST['mobile_phone'])) {
			$mobile_phone = $_POST['mobile_phone'];

			if ($mobile_phone == '') {
				$result['error'] = 1;
				$result['message'] = '未接收到值';
				exit(json_encode($result));
			}

			$sql = 'UPDATE {pre}users SET mobile_phone= \'' . $mobile_phone . '\' WHERE user_id=\'' . $this->user_id . '\'';
			$query = $this->db->query($sql);

			if ($query) {
				$result['error'] = 2;
				$result['sucess'] = $mobile_phone;
				$result['message'] = L('edit_sucsess');
				exit(json_encode($result));
			}
		}
	}

	public function actionCommentList()
	{
		$sign = isset($_REQUEST['sign']) ? intval($_REQUEST['sign']) : 0;
		$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
		$size = 10;
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('comment_img') . (' WHERE user_id=\'' . $_SESSION['user_id'] . '\' AND comment_id = 0');
		$GLOBALS['db']->query($sql);
		$record_count = get_user_order_comment_list($_SESSION['user_id'], 1, $sign);
		$pager = get_pager('user.php', array('act' => $action), $record_count, $page, $size);
		$comment_list = get_user_order_comment_list($_SESSION['user_id'], 0, $sign, 0, $size, $pager['start']);
		$this->assign('comment_list', $comment_list);
		$this->assign('pager', $pager);
		$this->assign('sign', $sign);
		$this->assign('sessid', SESS_ID);
		$this->assign('page_title', L('commentList'));
		$this->display();
	}

	public function actionAddComment()
	{
		if (IS_POST) {
			$user_id = $_SESSION['user_id'];
			$comment_id = I('comment_id', 0, 'intval');
			$rank = I('comment_rank', 5, 'intval');
			$rank_server = 5;
			$rank_delivery = 5;
			$content = I('content');
			$order_id = I('order_id', 0, 'intval');
			$goods_id = I('goods_id', 0, 'intval');
			$goods_tag = I('impression');
			$sign = I('sign', 0, 'intval');
			$rec_id = I('rec_id', 0, 'intval');
			$addtime = gmtime();
			$ip = real_ip();
			$desc_rank = I('desc_rank');
			$service_rank = I('service_rank');
			$delivery_rank = I('delivery_rank');
			$sender_rank = I('sender_rank');

			if (empty($content)) {
				show_message('评论内容不可为空', '返回', '', 'warning');
			}

			$condition = array('order_id' => $order_id, 'rec_id' => $rec_id, 'id_value' => $goods_id, 'user_id' => $_SESSION['user_id']);
			$count = $this->model->table('comment')->where($condition)->count();

			if (0 < $count) {
				show_message('已经评价过了', '', url('user/index/index'));
			}

			$condition = array('goods_id' => $goods_id);
			$ru_id = $this->model->table('goods')->where($condition)->getField('user_id');

			if (is_null($ru_id)) {
				show_message('缺少商家参数', '返回', '', 'warning');
			}

			$data = array('comment_type' => 0, 'id_value' => $goods_id, 'email' => $_SESSION['email'], 'user_name' => $_SESSION['user_name'], 'content' => $content, 'comment_rank' => $rank, 'comment_server' => $rank_server, 'comment_delivery' => $rank_delivery, 'add_time' => $addtime, 'ip_address' => $ip, 'status' => 1 - C('shop.comment_check'), 'parent_id' => 0, 'user_id' => $user_id, 'single_id' => 0, 'order_id' => $order_id, 'rec_id' => $rec_id, 'goods_tag' => $goods_tag, 'ru_id' => $ru_id);
			$comment_id = dao('comment')->add($data);

			if (0 < $comment_id) {
				$sql = 'UPDATE {pre}comment_img SET comment_id = \'' . $comment_id . '\' WHERE user_id = \'' . $_SESSION['user_id'] . '\' AND goods_id = \'' . $goods_id . '\' AND comment_id = 0 and order_id=\'' . $order_id . '\' and rec_id=\'' . $rec_id . '\'';
				$this->db->query($sql);
			}

			if (0 < $ru_id) {
				$sql = 'INSERT INTO {pre}comment_seller (user_id, ru_id, order_id, desc_rank, service_rank, delivery_rank, sender_rank, add_time )VALUES(\'' . $user_id . '\', \'' . $ru_id . '\', \'' . $order_id . '\', \' ' . $desc_rank . '\', \'' . $service_rank . '\', \'' . $delivery_rank . '\', \'' . $sender_rank . '\', \'' . $addtime . '\')';
				$result = $this->db->query($sql);

				if ($result) {
					$store_score = sprintf('%.2f', ($desc_rank + $service_rank + $delivery_rank) / 3);
					$sql = 'UPDATE {pre}merchants_shop_information SET store_score = \'' . $store_score . ('\' WHERE user_id = ' . $ru_id);
					$res = $this->db->query($sql);
				}
			}

			show_message('商品评论成功', '返回上一页', url('user/index/comment_list'), 'success');
		}

		$rec_id = I('rec_id', 0, 'intval');
		$sql = 'SELECT g.*, og.* FROM {pre}order_goods og LEFT JOIN {pre}goods g on og.goods_id = g.goods_id WHERE og.rec_id=\'' . $rec_id . '\'';
		$goods_info = $this->db->getRow($sql);

		if (empty($goods_info)) {
			show_message('评论商品数据不完整', '返回', '', 'warning');
		}

		$goods_info['shop_price'] = price_format($goods_info['shop_price']);
		$goods_info['goods_thumb'] = get_image_path($goods_info['goods_thumb']);
		$goods_info['goods_img'] = get_image_path($goods_info['goods_img']);
		$goods_info['original_img'] = get_image_path($goods_info['original_img']);
		$sql = 'SELECT id,comment_img FROM {pre}comment_img where rec_id = \'' . $rec_id . '\'';
		$img_list = $this->db->getAll($sql);
		$img = array();

		foreach ($img_list as $key => $val) {
			$img[$key]['img_id'] = $val['id'];
			$img[$key]['pic'] = get_image_path($val['comment_img']);
		}

		$sql = 'select sid from {pre}comment_seller WHERE order_id =' . $goods_info['order_id'];
		$sid = $this->db->getOne($sql);
		$sql = 'select rz_shopName from {pre}merchants_shop_information WHERE  user_id=' . $goods_info[ru_id];
		$shop_name = $this->db->getOne($sql);
		$this->assign('shop_name', $shop_name);
		$this->assign('sid', $sid);
		$this->assign('img', $img);
		$this->assign('order_id', $goods_info['order_id']);
		$this->assign('rec_id', $rec_id);
		$this->assign('goods_id', $goods_info['goods_id']);
		$this->assign('goods_info', $goods_info);
		$this->assign('page_title', '商品评论');
		$this->display();
	}

	public function actionAddCommentImg()
	{
		$img = $_FILES['myfile']['tmp_name'];
		list($width, $height, $type) = getimagesize($img);

		if (empty($img)) {
			return NULL;
		}

		$user_id = $_SESSION['user_id'];
		$goods_id = I('goods_id');
		$order_id = I('order_id');
		$rec_id = I('rec_id');
		$add_time = gmtime();

		if (empty($type)) {
			exit(json_encode(array('error' => 1, 'content' => '图片类型不正确')));
		}

		$img_num = dao('comment_img')->field('id')->where(array('user_id' => $user_id, 'order_id' => $order_id, 'goods_id' => $goods_id))->select();

		if (4 < count($img_num)) {
			exit(json_encode(array('error' => 1, 'content' => '图片上传过多')));
		}

		$result = $this->upload('data/cmt_img', false, 20, array(C('shop.thumb_width'), C('shop.thumb_height')));

		if (0 < $result['error']) {
			exit(json_encode(array('error' => 1, 'content' => $result['message'])));
		}

		$path = $result['url']['myfile']['url'];
		$sql = 'INSERT INTO {pre}comment_img (goods_id,user_id,comment_img,img_thumb,order_id,comment_id,rec_id)values(' . $goods_id . ',' . $user_id . ',\'' . $path . '\',\'' . $path . '\',' . $order_id . ',0,' . $rec_id . ')';
		$GLOBALS['db']->query($sql);
		$sql = 'SELECT id, comment_img FROM {pre}comment_img WHERE order_id = ' . $order_id . ' and user_id = ' . $user_id . ' and goods_id = ' . $goods_id;
		$res = $GLOBALS['db']->query($sql);
		$img = array();

		foreach ($res as $key => $val) {
			$img[$key]['img_id'] = $val['id'];
			$img[$key]['pic'] = get_image_path($val['comment_img']);
		}

		exit(json_encode($img));
	}

	public function actionClearCommentImg()
	{
		$id = I('id', 0, 'intval');
		$goods_id = I('goods_id', 0, 'intval');
		$sql = 'select comment_img from {pre}comment_img where id=' . $id;
		$result = array('error' => 0, 'content' => '');
		$img_list = $GLOBALS['db']->getAll($sql);

		foreach ($img_list as $key => $row) {
			get_oss_del_file(array($row['comment_img']));
			@unlink(get_image_path($row['comment_img']));
		}

		$sql = 'delete from {pre}comment_img where user_id = \'' . $_SESSION['user_id'] . ('\' and goods_id = \'' . $goods_id . '\'') . ' and id=' . $id;
		$GLOBALS['db']->query($sql);
		exit(json_encode($result));
	}

	public function actionUpdate_email()
	{
		$result = array('error' => 0, 'message' => '');

		if (isset($_POST['email'])) {
			$email = $_POST['email'];

			if ($email == '') {
				$result['error'] = 1;
				$result['message'] = '未接收到值';
				exit(json_encode($result));
			}

			$sql = 'UPDATE {pre}users SET email= \'' . $email . '\' WHERE user_id=\'' . $this->user_id . '\'';
			$query = $this->db->query($sql);

			if ($query) {
				$result['error'] = 2;
				$result['sucess'] = $mobile_phone;
				$result['message'] = L('edit_sucsess');
				exit(json_encode($result));
			}
		}
	}

	public function actionUpdate_sex()
	{
		$result = array('error' => 0, 'message' => '');

		if (isset($_POST['sex'])) {
			$sex = $_POST['sex'];

			if (sex == '') {
				$result['error'] = 1;
				$result['message'] = '未接收到值';
				exit(json_encode($result));
			}

			$sql = 'UPDATE {pre}users SET sex= \'' . $sex . '\' WHERE user_id=\'' . $this->user_id . '\'';
			$query = $this->db->query($sql);

			if ($query) {
				$result['error'] = 2;
				$result['message'] = L('edit_sucsess ');
				exit(json_encode($result));
			}
		}
	}

	public function actionAddressList()
	{
		$user_id = $this->user_id;

		if (0 < $_SESSION['user_id']) {
			$consignee_list = get_consignee_list($_SESSION['user_id']);
		}
		else if (isset($_SESSION['flow_consignee'])) {
			$consignee_list = array($_SESSION['flow_consignee']);
		}
		else {
			$consignee_list[] = array('country' => C('shop.shop_country'));
		}

		$this->assign('name_of_region', array(C('shop.name_of_region_1'), C('shop.name_of_region_2'), C('shop.name_of_region_3'), C('shop.name_of_region_4')));

		if ($consignee_list) {
			foreach ($consignee_list as $k => $v) {
				$address = '';

				if ($v['province']) {
					$res = get_region_name($v['province']);
					$address .= $res['region_name'];
				}

				if ($v['city']) {
					$ress = get_region_name($v['city']);
					$address .= $ress['region_name'];
				}

				if ($v['district']) {
					$resss = get_region_name($v['district']);
					$address .= $resss['region_name'];
				}

				if ($v['street']) {
					$resss = get_region_name($v['street']);
					$address .= $resss['region_name'];
				}

				$consignee_list[$k]['address'] = $address . ' ' . $v['address'];
			}
		}

		$province_list = array();
		$city_list = array();
		$district_list = array();

		foreach ($consignee_list as $region_id => $consignee) {
			$consignee['country'] = isset($consignee['country']) ? intval($consignee['country']) : 0;
			$consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
			$consignee['city'] = isset($consignee['city']) ? intval($consignee['city']) : 0;
			$province_list[$region_id] = get_regions(1, $consignee['country']);
			$city_list[$region_id] = get_regions(2, $consignee['province']);
			$district_list[$region_id] = get_regions(3, $consignee['city']);
		}

		$address_id = $this->db->getOne('SELECT address_id FROM {pre}users WHERE user_id=\'' . $user_id . '\'');
		$this->assign('address_id', $address_id);

		foreach ($consignee_list as $k => $v) {
			if ($v['address_id'] == $address_id) {
				$c[] = $v;
				unset($consignee_list[$k]);
			}
		}

		if (is_array($consignee_list) && is_array($c)) {
			$consignee_list = array_merge($c, $consignee_list);
		}

		$this->assign('consignee_list', $consignee_list);
		$this->assign('province_list', $province_list);
		$this->assign('city_list', $city_list);
		$this->assign('district_list', $district_list);
		$this->assign('page_title', '收货地址');
		$this->display();
	}

	public function actionAddAddress()
	{
		if (IS_POST) {
			$consignee = array('address_id' => I('address_id'), 'consignee' => I('consignee'), 'country' => 1, 'province' => I('province_region_id'), 'city' => I('city_region_id'), 'district' => I('district_region_id'), 'street' => I('town_region_id'), 'email' => I('email'), 'address' => I('address'), 'zipcode' => I('zipcode'), 'tel' => I('tel'), 'mobile' => I('mobile'), 'sign_building' => I('sign_building'), 'best_time' => I('best_time'), 'user_id' => $_SESSION['user_id']);

			if (empty($consignee['consignee'])) {
				exit(json_encode(array('status' => 'n', 'info' => L('msg_receiving_notnull'))));
			}

			if (is_mobile($consignee['mobile']) == false) {
				exit(json_encode(array('status' => 'n', 'info' => L('msg_mobile_format_error'))));
			}

			if (empty($consignee['province'])) {
				exit(json_encode(array('status' => 'n', 'info' => L('请选择地区'))));
			}

			if (empty($consignee['address'])) {
				exit(json_encode(array('status' => 'n', 'info' => L('msg_address_notnull'))));
			}

			$limit_address = $this->db->getOne('select count(address_id) from {pre}user_address where user_id = \'' . $consignee['user_id'] . '\'');

			if (10 < $limit_address) {
				exit(json_encode(array('status' => 'n', 'info' => sprintf(L('msg_save_address'), 10))));
			}

			if (0 < $_SESSION['user_id']) {
				save_consignee($consignee, ture);
			}

			$_SESSION['flow_consignee'] = stripslashes_deep($consignee);
			$back_act = url('user/index/address_list');
			if (isset($_SESSION['flow_consignee']) && empty($consignee['address_id'])) {
				exit(json_encode(array('status' => 'y', 'info' => L('success_address'), 'url' => $back_act)));
			}
			else {
				if (isset($_SESSION['flow_consignee']) && !empty($consignee['address_id'])) {
					exit(json_encode(array('status' => 'y', 'info' => L('edit_address'), 'url' => $back_act)));
				}
				else {
					exit(json_encode(array('status' => 'n', 'info' => L('error_address'))));
				}
			}
		}

		$this->assign('user_id', $_SESSION['user_id']);
		$this->assign('country_list', get_regions());
		$this->assign('shop_country', C('shop.shop_country'));
		$this->assign('shop_province_list', get_regions(1, C('shop.shop_country')));
		$this->assign('address_id', I('address_id'));
		$province_list = get_regions(1, C('shop.shop_country'));
		$this->assign('province_list', $province_list);
		$city_list = get_region_city_county($this->province_id);

		if ($city_list) {
			foreach ($city_list as $k => $v) {
				$city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
			}
		}

		$this->assign('city_list', $city_list);
		$district_list = get_region_city_county($this->city_id);
		$this->assign('district_list', $district_list);
		$address_id = I('request.address_id', 0, 'intval');

		if ($address_id) {
			$consignee_list = $this->db->getRow('SELECT * FROM {pre}user_address WHERE user_id = \'' . $_SESSION['user_id'] . '\' AND address_id = \'' . $address_id . '\' ');

			if (empty($consignee_list)) {
				exit(json_encode(array('status' => 'n', 'info' => L('no_address'))));
			}

			$province = get_region_name($consignee_list['province']);
			$city = get_region_name($consignee_list['city']);
			$district = get_region_name($consignee_list['district']);
			$town = get_region_name($consignee_list['street']);
			$consignee_list['province'] = $province['region_name'];
			$consignee_list['city'] = $city['region_name'];
			$consignee_list['district'] = $district['region_name'];
			$consignee_list['town'] = $town['region_name'];
			$consignee_list['province_id'] = $province['region_id'];
			$consignee_list['city_id'] = $city['region_id'];
			$consignee_list['district_id'] = $district['region_id'];
			$consignee_list['town_region_id'] = $town['region_id'];
			$city_list = get_region_city_county($province['region_id']);

			if ($city_list) {
				foreach ($city_list as $k => $v) {
					$city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
				}
			}

			$this->assign('city_list', $city_list);
			$this->assign('consignee_list', $consignee_list);
			$this->assign('page_title', '修改收货地址');
			$this->display();
		}
		else {
			$this->assign('page_title', '添加收货地址');
			$this->display();
		}
	}

	public function actionShowRegionName()
	{
		$error['province'] = get_region_name(I('province'));
		$error['city'] = get_region_name(I('city'));
		$error['district'] = get_region_name(I('district'));
		exit(json_encode($error));
	}

	public function actionDrop()
	{
		$id = I('address_id', 0, 'intval');

		if (drop_consignee($id)) {
			ecs_header('Location: ' . url('user/index/address_list'));
			exit();
		}
		else {
			show_message(L('del_address_false'));
		}
	}

	public function actionAjaxMakeAddress()
	{
		$user_id = $this->user_id;
		$address_id = input('address_id', 0, 'intval');

		if (!empty($address_id)) {
			$sql = 'UPDATE {pre}users SET address_id = \'' . $address_id . '\' WHERE user_id = \'' . $user_id . '\' ';
			$this->db->query($sql);
		}

		$res['address_id'] = $address_id;
		exit(json_encode($res));
	}

	public function actionCollectionList()
	{
		if (IS_AJAX) {
			$user_id = $this->user_id;
			$page = I('page', '1', 'intval');
			$offset = 10;
			$sql = 'SELECT count(rec_id) as max FROM {pre}collect_goods WHERE user_id=' . $user_id . ' ';
			$count = $this->db->getOne($sql);
			$page_size = ceil($count / $offset);
			$limit = ' LIMIT ' . ($page - 1) * $offset . ',' . $offset;
			$collection_goods = get_collection_goods($user_id, $count, $limit);
			$show = 0 < $count ? 1 : 0;
			exit(json_encode(array('goods_list' => $collection_goods['goods_list'], 'show' => $show, 'totalPage' => $page_size)));
		}

		$this->assign('paper', $collection_goods['paper']);
		$this->assign('record_count', $collection_goods['record_count']);
		$this->assign('size', $collection_goods['size']);
		$this->assign('page_title', '我的收藏');
		$this->display();
	}

	public function actionAddCollection()
	{
		$result = array('error' => 0, 'message' => '');
		$goods_id = intval($_GET['id']);
		if (!isset($this->user_id) || $this->user_id == 0) {
			$result['error'] = 2;
			$result['message'] = L('login_please');
			exit(json_encode($result));
		}
		else {
			$where['user_id'] = $this->user_id;
			$where['goods_id'] = $goods_id;
			$rs = $this->db->table('collect_goods')->where($where)->count();

			if (0 < $rs) {
				$this->db->table('collect_goods')->where($where)->delete();
				$result['error'] = 0;
				$result['message'] = L('collect_success');
				exit(json_encode($result));
			}
			else {
				$data['user_id'] = $this->user_id;
				$data['goods_id'] = $goods_id;
				$data['add_time'] = gmtime();

				if ($this->db->table('collect_goods')->data($data)->add() === false) {
					$result['error'] = 1;
					$result['message'] = M()->errorMsg();
					exit(json_encode($result));
				}
				else {
					$result['error'] = 0;
					$result['message'] = L('collect_success');
					exit(json_encode($result));
				}
			}
		}
	}

	public function actionDelCollection()
	{
		$user_id = $this->user_id;
		$collection_id = I('rec_id');
		$sql = 'SELECT count(*) FROM {pre}collect_goods WHERE rec_id=\'' . $collection_id . '\' AND user_id =\'' . $user_id . '\'';

		if (0 < $this->db->getOne($sql)) {
			$this->db->query('DELETE FROM {pre}collect_goods WHERE rec_id=\'' . $collection_id . '\' AND user_id =\'' . $user_id . '\'');
			ecs_header('Location: ' . url('user/index/collectionlist'));
			exit();
		}
	}

	public function actionHelp()
	{
		$sql = 'SELECT cat_id, cat_name FROM ' . $GLOBALS['ecs']->table('article_cat') . ' WHERE parent_id = 3  order by sort_order asc, cat_id asc';
		$articles = $this->db->query($sql);

		foreach ($articles as $key => $value) {
			$sql = 'SELECT article_id, title FROM ' . $GLOBALS['ecs']->table('article') . ' WHERE is_open = 1 and ' . get_article_children($value['cat_id']) . ' order by add_time desc ';
			$articles[$key]['list'] = $this->db->query($sql);
		}

		$this->assign('articles', $articles);
		$this->assign('page_title', '帮助手册');
		$this->display();
	}

	public function actionMessageList()
	{
		if (is_dir(dirname(ROOT_PATH) . '/kefu/')) {
			$this->redirect('chat/index/chatlist');
		}

		$sql = 'SELECT msg_id,msg_time  FROM {pre}feedback AS a WHERE a.parent_id IN ' . ' (SELECT msg_id FROM {pre}feedback AS b WHERE b.user_id = \'' . $_SESSION['user_id'] . '\') ORDER BY a.msg_id DESC LIMIT 1';
		$msg_ids = $this->db->getRow($sql);
		S('message_' . $_SESSION['user_id'], $msg_ids['msg_id']);
		$user_id = $this->user_id;
		$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
		$order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
		$info = get_user_default($user_id);
		$order_info = array();

		if ($order_id) {
			$sql = "SELECT COUNT(*) FROM {pre}feedback\r\n                     WHERE parent_id = 0 AND order_id = '" . $order_id . '\' AND user_id = \'' . $user_id . '\'';
			$order_info = $this->db->getRow('SELECT * FROM {pre}order_info  WHERE order_id = \'' . $order_id . '\' AND user_id = \'' . $user_id . '\'');
			$order_info['url'] = 'user.php?act=order_detail&order_id=' . $order_id;
		}
		else {
			$sql = "SELECT COUNT(*) FROM {pre}feedback\r\n                     WHERE parent_id = 0 AND user_id = '" . $user_id . '\' AND user_name = \'' . $_SESSION['user_name'] . '\' AND order_id=0';
		}

		$record_count = $this->db->getOne($sql);
		$act = array('act' => $action);

		if ($order_id != '') {
			$act['order_id'] = $order_id;
		}

		$pager = get_pager('user.php', $act, $record_count, $page, 5);
		$this->assign('info', $info);
		$message_list = get_message_list($user_id, $_SESSION['user_name'], $pager['size'], $pager['start'], $order_id);
		ksort($message_list);
		$this->assign('message_list', $message_list);
		$this->assign('pager', $pager);
		$this->assign('order_info', $order_info);
		$this->assign('page_title', '客户服务');
		$this->display();
	}

	public function actionAddMessage()
	{
		if (IS_POST) {
			$message = array('user_id' => $_SESSION['user_id'], 'user_name' => $_SESSION['user_name'], 'user_email' => $_SESSION['email'], 'msg_type' => isset($_POST['msg_type']) ? intval($_POST['msg_type']) : 0, 'msg_title' => isset($_POST['msg_title']) ? trim($_POST['msg_title']) : '', 'msg_time' => gmtime(), 'msg_content' => isset($_POST['msg_title']) ? trim($_POST['msg_title']) : '', 'order_id' => empty($_POST['order_id']) ? 0 : intval($_POST['order_id']), 'upload' => isset($_FILES['message_img']['error']) && $_FILES['message_img']['error'] == 0 || !isset($_FILES['message_img']['error']) && isset($_FILES['message_img']['tmp_name']) && $_FILES['message_img']['tmp_name'] != 'none' ? $_FILES['message_img'] : array());

			if (empty($_POST['msg_title'])) {
				show_message('请输入点内容吧');
			}

			if (addmg($message)) {
				ecs_header('Location: ' . url('user/index/messagelist'));
				exit();
			}
		}
	}

	public function actionStoreList()
	{
		if (IS_AJAX) {
			$page = I('page', '1', 'intval');
			$offset = 5;
			$sql = 'SELECT count(rec_id) as max FROM {pre}collect_store WHERE user_id=' . $this->user_id;
			$count = $this->db->getOne($sql);
			$page_size = ceil($count / $offset);
			$limit = ' LIMIT ' . ($page - 1) * $offset . ',' . $offset;
			$res = get_collection_store_list($this->user_id, $count, $limit);
			$show = 0 < $count ? 1 : 0;
			exit(json_encode(array('store_list' => $res['store_list'], 'show' => $show, 'totalPage' => $page_size)));
		}

		$this->assign('page_title', '我的关注');
		$this->display();
	}

	public function actionDelStore()
	{
		$user_id = $this->user_id;
		$collection_id = I('rec_id');

		if (0 < I('rec_id')) {
			$this->db->query('DELETE FROM {pre}collect_store WHERE rec_id=\'' . $collection_id . '\' AND user_id =\'' . $user_id . '\'');
			ecs_header('Location: ' . url('user/index/storelist'));
			exit();
		}
	}

	public function actionBookingList()
	{
		if (IS_POST) {
			$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
			$sql = 'SELECT COUNT(*) ' . 'FROM ' . $GLOBALS['ecs']->table('booking_goods') . ' AS bg, ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ('WHERE bg.goods_id = g.goods_id AND bg.user_id = \'' . $this->user_id . '\'');
			$record_count = $GLOBALS['db']->getOne($sql);
			$pager = get_pager('user.php', array('act' => $action), $record_count, $page);
			$booking_list = get_booking_list($this->user_id, $pager['size'], $pager['start']);
			exit(json_encode(array('list' => $booking_list, 'totalPage' => ceil($record_count / $pager['size']))));
		}

		$this->assign('page_title', '缺货登记');
		$this->display();
	}

	public function actionAddBooking()
	{
		if (IS_POST) {
			$booking = array('goods_id' => isset($_POST['id']) ? intval($_POST['id']) : 0, 'goods_amount' => isset($_POST['number']) ? intval($_POST['number']) : 0, 'desc' => isset($_POST['desc']) ? trim($_POST['desc']) : '', 'linkman' => isset($_POST['linkman']) ? trim($_POST['linkman']) : '', 'email' => isset($_POST['email']) ? trim($_POST['email']) : '', 'tel' => isset($_POST['tel']) ? trim($_POST['tel']) : '', 'booking_id' => isset($_POST['rec_id']) ? intval($_POST['rec_id']) : 0);
			$rec_id = get_booking_rec($this->user_id, $booking['goods_id']);

			if (0 < $rec_id) {
				show_message('商品已经登记过啦', '返回上一页', '', 'error');
			}

			if (add_booking($booking)) {
				show_message('添加缺货登记成功', '返回登记列表', url('booking_list'), 'info');
			}
			else {
				$GLOBALS['err']->show('返回登记列表', url('booking_list'));
			}

			return NULL;
		}

		$goods_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

		if ($goods_id == 0) {
			show_message($_LANG['no_goods_id'], $_LANG['back_page_up'], '', 'error');
		}

		$goods_attr = '';

		if ($_GET['spec'] != '') {
			$goods_attr_id = $_GET['spec'];
			$attr_list = array();
			$sql = 'SELECT a.attr_name, g.attr_value ' . 'FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS g, ' . $GLOBALS['ecs']->table('attribute') . ' AS a ' . 'WHERE g.attr_id = a.attr_id ' . 'AND g.goods_attr_id ' . db_create_in($goods_attr_id);
			$res = $GLOBALS['db']->query($sql);

			foreach ($res as $row) {
				$attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
			}

			$goods_attr = join(chr(13) . chr(10), $attr_list);
		}

		$this->assign('goods_attr', $goods_attr);
		$this->assign('info', get_goodsinfo($goods_id));
		$this->assign('page_title', '缺货登记');
		$this->display();
	}

	public function actionDelBooking()
	{
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if ($id == 0 || $this->user_id == 0) {
			exit(json_encode(array('status' => 0)));
		}

		$result = delete_booking($id, $this->user_id);

		if ($result) {
			exit(json_encode(array('status' => 1)));
		}
	}

	public function actionAffiliate()
	{
		$share = unserialize($GLOBALS['_CFG']['affiliate']);

		if ($share['on'] == 0) {
			$this->redirect('user/index/index');
		}

		$goodsid = I('request.goodsid', 0);

		if (empty($goodsid)) {
			$page = I('post.page', 1, 'intval');
			$size = 8;
			empty($share) && ($share = array());
			$affdb = array();
			$num = count($share['item']);
			$up_uid = '\'' . $this->user_id . '\'';
			$all_uid = '\'' . $this->user_id . '\'';

			for ($i = 1; $i <= $num; $i++) {
				$count = 0;

				if ($up_uid) {
					$sql = 'SELECT user_id FROM {pre}users WHERE parent_id IN(' . $up_uid . ')';
					$rs = $GLOBALS['db']->query($sql);
					empty($rs) && ($rs = array());
					$up_uid = '';

					foreach ($rs as $k => $v) {
						$up_uid .= $up_uid ? ',\'' . $v['user_id'] . '\'' : '\'' . $v['user_id'] . '\'';

						if ($i < $num) {
							$all_uid .= ', \'' . $v['user_id'] . '\'';
						}

						$count++;
					}
				}

				$affdb[$i]['num'] = $count;
				$affdb[$i]['point'] = $share['item'][$i - 1]['level_point'];
				$affdb[$i]['money'] = $share['item'][$i - 1]['level_money'];
				$this->assign('affdb', $affdb);
			}

			if (IS_AJAX) {
				$sqladd = '';
				$sqladd .= ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
				$sqladd .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og WHERE og.order_id = o.order_id LIMIT 1) = 0';

				if ($share['config']['separate_by'] == 0) {
					if (is_dir(APP_DRP_PATH)) {
						$sqlcount = 'SELECT count(*) as count FROM {pre}order_info o' . ' LEFT JOIN {pre}users u ON o.user_id = u.user_id' . ' LEFT JOIN {pre}affiliate_log a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND (u.parent_id IN (' . $all_uid . ') AND u.drp_parent_id = 0 AND o.is_separate = 0 OR a.user_id = \'' . $this->user_id . '\' AND o.is_separate > 0) ' . $sqladd);
						$sql = 'SELECT o.*, a.log_id, a.user_id as suid,  a.user_name as auser, a.money, a.point, a.separate_type FROM {pre}order_info o' . ' LEFT JOIN {pre}users u ON o.user_id = u.user_id' . ' LEFT JOIN {pre}affiliate_log a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND (u.parent_id IN (' . $all_uid . ') AND u.drp_parent_id = 0 AND o.is_separate = 0 OR a.user_id = \'' . $this->user_id . '\' AND o.is_separate > 0) ' . $sqladd) . ' ORDER BY order_id DESC';
					}
					else {
						$sqlcount = 'SELECT count(*) as count FROM {pre}order_info o' . ' LEFT JOIN {pre}users u ON o.user_id = u.user_id' . ' LEFT JOIN {pre}affiliate_log a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND (u.parent_id IN (' . $all_uid . ') AND o.is_separate = 0 OR a.user_id = \'' . $this->user_id . '\' AND o.is_separate > 0) ' . $sqladd);
						$sql = 'SELECT o.*, a.log_id, a.user_id as suid,  a.user_name as auser, a.money, a.point, a.separate_type FROM {pre}order_info o' . ' LEFT JOIN {pre}users u ON o.user_id = u.user_id' . ' LEFT JOIN {pre}affiliate_log a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND (u.parent_id IN (' . $all_uid . ') AND o.is_separate = 0 OR a.user_id = \'' . $this->user_id . '\' AND o.is_separate > 0) ' . $sqladd) . ' ORDER BY order_id DESC';
					}
				}
				else {
					$sqlcount = 'SELECT count(*) as count FROM {pre}order_info o' . ' LEFT JOIN {pre}users u ON o.user_id = u.user_id' . ' LEFT JOIN {pre}APP_DRP_PATHiliate_log a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND (o.parent_id = \'' . $this->user_id . '\' AND o.is_separate = 0 OR a.user_id = \'' . $this->user_id . '\' AND o.is_separate > 0) ' . $sqladd);
					$sql = 'SELECT o.*, a.log_id,a.user_id as suid, a.user_name as auser, a.money, a.point, a.separate_type,u.parent_id as up FROM {pre}order_info o' . ' LEFT JOIN {pre}users u ON o.user_id = u.user_id' . ' LEFT JOIN {pre}affiliate_log a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND (o.parent_id = \'' . $this->user_id . '\' AND o.is_separate = 0 OR a.user_id = \'' . $this->user_id . '\' AND o.is_separate > 0) ' . $sqladd) . ' ORDER BY order_id DESC';
				}

				$res = $this->model->query($sqlcount);
				$count = $res[0]['count'];
				$max_page = 0 < $count ? ceil($count / $size) : 1;

				if ($max_page < $page) {
					$page = $max_page;
				}

				$limit = ($page - 1) * $size . ',' . $size;
				$sql = $sql . ' LIMIT ' . $limit;
				$rt = $this->model->query($sql);

				if ($rt) {
					foreach ($rt as $k => $v) {
						if (!empty($v['suid'])) {
							if ($v['separate_type'] == -1 || $v['separate_type'] == -2) {
								$rt[$k]['is_separate'] = 3;
							}
						}

						$rt[$k]['order_sn'] = substr($v['order_sn'], 0, strlen($v['order_sn']) - 5) . '***' . substr($v['order_sn'], -2, 2);
						$rt[$k]['affiliate_type'] = $share['config']['separate_by'];
					}
				}
				else {
					$rt = array();
				}

				exit(json_encode(array('logdb' => $rt, 'totalPage' => ceil($count / $size))));
			}
		}
		else {
			$this->assign('userid', $this->user_id);
			$this->assign('goodsid', $goodsid);
			$types = array(1, 2, 3, 4, 5);
			$this->assign('types', $types);
			$goods = get_goods_info($goodsid);
			$goods['goods_img'] = get_image_path($goods['goods_img']);
			$goods['goods_thumb'] = get_image_path($goods['goods_thumb']);
			$goods['shop_price'] = price_format($goods['shop_price']);
			$this->assign('goods', $goods);
		}

		$type = $share['config']['expire_unit'];

		switch ($type) {
		case 'hour':
			$this->assign('expire_unit', '小时');
			break;

		case 'day':
			$this->assign('expire_unit', '天');
			break;

		case 'week':
			$this->assign('expire_unit', '周');
			break;
		}

		if ($share['config']['separate_by'] == 0) {
			$this->assign('separate_by', $share['config']['separate_by']);
			$this->assign('expire', $share['config']['expire']);
			$this->assign('level_register_all', $share['config']['level_register_all']);
			$this->assign('level_register_up', $share['config']['level_register_up']);
			$this->assign('level_money_all', $share['config']['level_money_all']);
			$this->assign('level_point_all', $share['config']['level_point_all']);
		}

		if ($share['config']['separate_by'] == 1) {
			$this->assign('separate_by', $share['config']['separate_by']);
			$this->assign('expire', $share['config']['expire']);
			$this->assign('level_money_all', $share['config']['level_money_all']);
			$this->assign('level_point_all', $share['config']['level_point_all']);
		}

		$url = url('/', '', true, true) . '?u=' . $this->user_id;
		$errorCorrectionLevel = 'M';
		$matrixPointSize = 8;
		$file = dirname(ROOT_PATH) . '/data/attached/qrcode/';

		if (!file_exists($file)) {
			make_dir($file, 511);
		}

		$qrcode_bg = ROOT_PATH . 'public/img/affiliate.jpg';
		$filename = $file . 'user_share_' . $this->user_id . $errorCorrectionLevel . $matrixPointSize . '.png';
		$share_img = $file . 'user_share_' . $this->user_id . '_bg.png';

		if (!file_exists($share_img)) {
			$code = \App\Extensions\QRcode::png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
			$qrcode_logo = $filename;
			$img = new \Think\Image();
			$bg_width = $img->open($qrcode_bg)->width();
			$bg_height = $img->open($qrcode_bg)->height();
			$logo_width = $img->open($qrcode_logo)->width();
			$img->open($qrcode_bg)->water($qrcode_logo, array(($bg_width - $logo_width) / 2, $bg_height / 2), 100)->save($share_img);

			if (C('shop.open_oss') == 1) {
				$image_name = $this->ossMirror($share_img, 'data/attached/qrcode/');
			}
		}

		$image_name = 'data/attached/qrcode/' . basename($share_img);
		$qrcode_url = get_image_path($image_name);
		if (is_dir(APP_WECHAT_PATH) && is_wechat_browser()) {
			$share_img = get_wechat_image_path('data/attached/qrcode/' . basename($share_img));
			$share_data = array('title' => '我的推荐', 'desc' => '推荐注册有好礼，马上加入我们_' . C('shop.shop_name'), 'link' => $url, 'img' => $share_img);
			$this->assign('share_data', $this->get_wechat_share_content($share_data));
		}

		$this->assign('ewm', $qrcode_url);
		$this->assign('domain', __HOST__);
		$this->assign('shopdesc', C('shop.shop_desc'));
		$this->assign('share', $share);
		$this->assign('page_title', '我的推荐');
		$this->display();
	}

	public function actionCreateQrcode()
	{
		$url = I('get.value');

		if ($url) {
			$errorCorrectionLevel = 'L';
			$matrixPointSize = 8;
			\App\Extensions\QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);
		}
	}

	public function actionReportList()
	{
		$sql = 'SELECT report_time FROM {pre}users WHERE user_id = \'' . $_SESSION['user_id'] . '\'';
		$report_time = $this->db->getOne($sql);
		$report_time = local_date('Y-m-d H:i:s', $report_time);

		if (IS_AJAX) {
			$size = 3;
			$page = I('page', 1, 'intval');
			$goods_report = get_goods_report_list($size, $page);
			exit(json_encode(array('order_list' => $goods_report['list'], 'totalPage' => $goods_report['totalPage'])));
		}

		$this->assign('page_title', '商品举报列表');
		$this->assign('report_time', $report_time);
		$this->display();
	}

	public function actionService()
	{
		$this->assign('page_title', '服务中心');
		$this->assign('report_time', $report_time);
		$this->display();
	}

	public function actionGoodsReport()
	{
		$report_id = I('report_id', 0, 'intval');
		$goods_id = I('id', 0, 'intval');
		$where = '';

		if (0 < $goods_id) {
			$new_time = gmtime();
			$sql = 'SELECT report_time FROM {pre}users WHERE user_id = \'' . $_SESSION['user_id'] . '\'';
			$report_time = $this->db->getOne($sql);

			if ($new_time < $report_time) {
				show_message(L('malice_report'), L('back_report_list'), url('user/index/report_list'));
			}

			$sql = 'SELECT count(*) FROM {pre}goods_report WHERE goods_id=\'' . $goods_id . '\' AND user_id = \'' . $_SESSION['user_id'] . '\' AND report_state = 0';
			$goods_report_count = $this->db->getOne($sql);

			if (0 < $goods_report_count) {
				show_message(L('repeat_report'));
			}

			$goods_info = goods_info($goods_id);

			if ($goods_info['is_on_sale'] == 0) {
				show_message(L('offgoods_report'));
			}

			$goods_info['goods_img'] = $goods_info['goods_thumb'];
			$goods_info['goods_thumb'] = get_image_path($goods_info['goods_thumb']);
			$goods_info['goods_price'] = price_format($goods_info['goods_price']);
			$this->assign('goods_info', $goods_info);
			$report_type = get_goods_report_type();
			$report_title = array();

			if ($report_type) {
				foreach ($report_type as $key => $val) {
					$report_title[$val['type_id']] = get_goods_report_title($val['type_id']);
				}
			}

			$this->assign('report_type', $report_type);
			$this->assign('report_title', $report_title);
			$this->assign('report_title_json', json_encode($report_title));
			$where .= ' AND goods_id = \'' . $goods_id . '\' AND report_id = 0';
		}
		else if (0 < $report_id) {
			$goods_report_info = array('goods_id' => 0, 'goods_name' => '', 'goods_thumb' => '');
			$sql = 'SELECT g.report_id , g.user_id , g.user_name , g.goods_id , g.goods_name , g.goods_image , g.title_id , g.type_id , ' . 'g.inform_content , g.add_time , g.report_state , g.handle_type , g.handle_message , g.handle_time , g.admin_id , ' . 'gt.type_name , gt.type_desc , ge.title_name FROM {pre}goods_report AS g ' . 'LEFT JOIN {pre}goods_report_type AS gt ON gt.type_id = g.type_id ' . 'LEFT JOIN {pre}goods_report_title AS ge ON ge.title_id=g.title_id ' . ('WHERE g.report_id = \'' . $report_id . '\' AND g.user_id = \'' . $_SESSION['user_id'] . ']\' LIMIT 1');
			$goods_report_info = $this->db->getRow($sql);
			$goods_info['goods_id'] = $goods_report_info['goods_id'];
			$goods_info['goods_name'] = $goods_report_info['goods_name'];
			$goods_info['goods_thumb'] = get_image_path($goods_report_info['goods_image']);
			$sql = 'SELECT user_id FROM {pre}goods WHERE goods_id = \'' . $goods_report_info['goods_id'] . '\' LIMIT 1';
			$basic_info = get_seller_shopinfo($GLOBALS['db']->getOne($sql));
			$goods_info['shop_name'] = $basic_info['shop_name'];
			$this->assign('goods_report_info', $goods_report_info);
			$where .= 'AND report_id = \'' . $report_id . '\' AND goods_id = \'' . $goods_report_info['goods_id'] . '\'';
		}

		$goods_info['url'] = build_uri('goods', array('gid' => $goods_info['goods_id']));
		$sql = 'SELECT img_id as id , goods_id, report_id,user_id,img_file as comment_img FROM {pre}goods_report_img WHERE user_id = \'' . $_SESSION['user_id'] . '\' ' . $where . ' ORDER BY  id DESC';
		$img_list = $this->db->getAll($sql);
		$img = array();

		foreach ($img_list as $key => $val) {
			$img[$key]['img_id'] = $val['id'];
			$img[$key]['pic'] = get_image_path($val['comment_img']);
		}

		$this->assign('img', $img);
		$this->assign('report_title_json', json_encode($report_title));
		$this->assign('report_id', $report_id);
		$this->assign('img_list', $img_list);
		$this->assign('goods_info', $goods_info);
		$this->assign('page_title', L('report_goods'));
		$this->display();
	}

	public function actionCheckReportState()
	{
		$report_id = I('report_id', 0, 'intval');
		$state = I('state', 0, 'intval');

		if (0 < $_SESSION['user_id']) {
			$sql = 'UPDATE {pre}goods_report SET report_state = \'' . $state . '\'  WHERE report_id = \'' . $report_id . '\'';
			$this->db->query($sql);
			$result['error'] = 0;
			exit(json_encode($result));
		}
	}

	public function actionImgReturn()
	{
		$img = $_FILES['myfile']['tmp_name'];
		list($width, $height, $type) = getimagesize($img);

		if (empty($img)) {
			return NULL;
		}

		$user_id = $_SESSION['user_id'];
		$goods_id = I('goods_id');

		if (empty($type)) {
			echo json_encode(array('error' => 1, 'content' => '图片类型不正确'));
			return NULL;
		}

		$result = $this->upload('data/report_img', false, 20, array(C('shop.thumb_width'), C('shop.thumb_height')));
		$path = $result['url']['myfile']['url'];
		$add_time = gmtime();
		$sql = 'INSERT INTO {pre}goods_report_img (goods_id,user_id,img_file,report_id)values(' . $goods_id . ',' . $user_id . ',\'' . $path . '\',0)';
		$GLOBALS['db']->query($sql);
		$sql = 'SELECT img_id, img_file FROM {pre}goods_report_img WHERE user_id = ' . $user_id . ' and goods_id = ' . $goods_id;
		$res = $GLOBALS['db']->query($sql);
		$img = array();

		foreach ($res as $key => $val) {
			$img[$key]['img_id'] = $val['img_id'];
			$img[$key]['pic'] = get_image_path($val['img_file']);
		}

		echo json_encode($img);
	}

	public function actionClearPictures()
	{
		$id = I('img_id', 0, 'intval');
		$rec_id = I('goods_id', 0, 'intval');
		$result = array('error' => 0, 'content' => '');
		$sql = 'select img_file from {pre}goods_report_img where user_id = \'' . $_SESSION['user_id'] . ('\' and goods_id = \'' . $rec_id . '\'') . ' and img_id=' . $id;
		$img_list = $GLOBALS['db']->getAll($sql);

		foreach ($img_list as $key => $row) {
			get_oss_del_file(array($row['img_file']));
			@unlink(get_image_path($row['img_file']));
		}

		$sql = 'delete from {pre}goods_report_img where user_id = \'' . $_SESSION['user_id'] . ('\' and goods_id = \'' . $rec_id . '\'') . ' and img_id=' . $id;
		$GLOBALS['db']->query($sql);
		echo json_encode($result);
	}

	public function actionGoodsReportSubmit()
	{
		$goods_id = I('goods_id', 0, 'intval');
		$goods_name = !empty($_REQUEST['goods_name']) ? trim($_REQUEST['goods_name']) : '';
		$goods_image = !empty($_REQUEST['goods_image']) ? trim($_REQUEST['goods_image']) : '';
		$title_id = I('title_id', 0, 'intval');
		$type_id = I('type_id', 0, 'intval');
		$inform_content = !empty($_REQUEST['inform_content']) ? trim($_REQUEST['inform_content']) : '';

		if ($title_id == 0) {
			show_message(L('title_null'));
		}
		else if ($type_id == 0) {
			show_message(L('type_null'));
		}
		else if ($inform_content == '') {
			show_message(L('inform_content_null'));
		}
		else {
			$time = gmtime();
			$other = array('user_id' => $_SESSION['user_id'], 'user_name' => $_SESSION['user_name'], 'goods_id' => $goods_id, 'goods_name' => $goods_name, 'goods_image' => $goods_image, 'title_id' => $title_id, 'type_id' => $type_id, 'inform_content' => $inform_content, 'add_time' => $time);
			$report_id = dao('goods_report')->add($other);

			if (0 < $report_id) {
				$sql = 'UPDATE {pre}goods_report_img SET report_id = \'' . $report_id . '\' WHERE user_id = \'' . $_SESSION['user_id'] . '\' AND goods_id = \'' . $goods_id . '\' AND report_id = 0';
				$this->db->query($sql);
			}

			show_message(L('report_success'), '', url('user/index/report_list'));
		}
	}

	public function actionInvForm()
	{
		$user_id = $_SESSION['user_id'];
		$id = I('id');

		if (IS_POST) {
			$content = array('id' => I('id'), 'company_name' => I('company_name'), 'user_id' => $user_id, 'tax_id' => I('tax_id'), 'company_address' => I('company_address'), 'company_telephone' => I('company_telephone'), 'bank_of_deposit' => I('bank_of_deposit'), 'bank_account' => I('bank_account'), 'consignee_name' => I('consignee_name'), 'consignee_mobile_phone' => I('consignee_mobile_phone'), 'consignee_address' => I('consignee_address'), 'country' => I('country'), 'province' => I('province'), 'city' => I('city'), 'district' => I('district'), 'audit_status' => 0);

			if (empty($content['consignee_address'])) {
				exit(json_encode(array('status' => 'n', 'info' => L('msg_receiving_notnull'))));
			}

			if (is_mobile($content['consignee_mobile_phone']) == false) {
				exit(json_encode(array('status' => 'n', 'info' => L('msg_mobile_format_error'))));
			}

			if (empty($content['province'])) {
				exit(json_encode(array('status' => 'n', 'info' => L('请选择地区'))));
			}

			$vat_id = $this->db->getOne(' SELECT id FROM {pre}users_vat_invoices_info  WHERE user_id = \'' . $user_id . '\' LIMIT 1 ');
			if ($vat_id && empty($id)) {
				exit(json_encode(array('status' => 'y', 'info' => '您已提交过增票资质申请，请勿重复提交！', 'url' => url('user/index/inv_info'))));
			}
			else if (empty($content['id'])) {
				$content['add_time'] = gmtime();
				dao('users_vat_invoices_info')->add($content);
				exit(json_encode(array('status' => 'y', 'info' => '您的增票资质已提交，等待审核。', 'url' => url('user/index/inv_info'))));
			}
			else {
				dao('users_vat_invoices_info')->where(array('id' => $id))->save($content);
				exit(json_encode(array('status' => 'y', 'info' => '您的增票资质已提交，等待审核。', 'url' => url('user/index/inv_info'))));
			}
		}

		if (!empty($id)) {
			$vat_info = $this->db->getRow(' SELECT * FROM {pre}users_vat_invoices_info  WHERE user_id = \'' . $user_id . '\' and id=' . $id);
			$province = get_region_name($vat_info['province']);
			$city = get_region_name($vat_info['city']);
			$district = get_region_name($vat_info['district']);
			$vat_info['province_name'] = $province['region_name'];
			$vat_info['city_name'] = $city['region_name'];
			$vat_info['district_name'] = $district['region_name'];
			$this->assign('vat_info', $vat_info);
		}

		$this->assign('id', $id);
		$this->assign('page_title', '增值发票');
		$this->display();
	}

	public function actionInvInfo()
	{
		$user_id = $_SESSION['user_id'];
		$vat_info = $this->db->getRow(' SELECT * FROM {pre}users_vat_invoices_info  WHERE user_id = \'' . $user_id . '\'');

		if (empty($vat_info)) {
			show_message('您还没有增票资质', '', url('user/index/inv_form'));
		}

		$province = get_region_name($vat_info['province']);
		$city = get_region_name($vat_info['city']);
		$district = get_region_name($vat_info['district']);
		$vat_info['province_name'] = $province['region_name'];
		$vat_info['city_name'] = $city['region_name'];
		$vat_info['district_name'] = $district['region_name'];
		$this->assign('vat_info', $vat_info);
		$this->assign('page_title', '增值票详情');
		$this->display();
	}

	public function actionDelInv()
	{
		$vat_id = I('vat_id', 0, 'intval');
		$sql = ' DELETE FROM {pre}users_vat_invoices_info WHERE id = \'' . $vat_id . '\' ';
		$this->db->query($sql);
		exit(json_encode(array('status' => 1)));
	}

	public function actionSetAuctionCookie()
	{
		cookie('all_auction', 1);
		exit(json_encode(array('status' => 1)));
	}

	private function isSeller()
	{
		$user_id = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
		$is_jurisdiction = 0;

		if (0 < $user_id) {
			$sql = 'SELECT id FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' WHERE ru_id = \'' . $user_id . '\'');

			if ($GLOBALS['db']->getOne($sql, true)) {
				$is_jurisdiction = 1;
			}

			$sql = 'SELECT fid FROM ' . $GLOBALS['ecs']->table('merchants_steps_fields') . (' WHERE user_id = \'' . $user_id . '\' AND company_type = \'厂商\'');
			$is_chang = $GLOBALS['db']->getOne($sql, true);

			if ($is_chang) {
				$is_jurisdiction = 0;
			}
		}

		return $is_jurisdiction;
	}
}

?>
