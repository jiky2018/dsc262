<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\User\Controllers;

class OrderController extends \App\Modules\Base\Controllers\FrontendController
{
	public $user_id;

	public function __construct()
	{
		parent::__construct();
		$this->user_id = $_SESSION['user_id'];
		C('URL_MODEL', 0);
		$this->actionchecklogin();
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		L(require LANG_PATH . C('shop.lang') . '/flow.php');
		$files = array('order', 'clips', 'payment', 'transaction');
		$this->load_helper($files);
		$this->assign('team', is_dir(APP_TEAM_PATH) ? 1 : 0);
	}

	public function actionIndex()
	{
		$size = 10;
		$page = I('page', 1, 'intval');
		$status = I('status', 0, 'intval');

		if (IS_POST) {
			$order_list = get_user_orders($this->user_id, $size, $page, $status);
			exit(json_encode(array('order_list' => $order_list['list'], 'totalPage' => $order_list['totalpage'])));
		}

		$all_order = get_order_where_count($this->user_id, 0, '');
		$where_pay = ' AND oi.pay_status = ' . PS_UNPAYED . ' AND oi.order_status not in(' . OS_CANCELED . ',' . OS_INVALID . ',' . OS_RETURNED . ')';
		$pay_count = get_order_where_count($this->user_id, 0, $where_pay);
		$where_confirmed = ' AND oi.pay_status = ' . PS_PAYED . ' AND oi.order_status in (' . OS_CONFIRMED . ', ' . OS_SPLITED . ', ' . OS_SPLITING_PART . ') AND (oi.shipping_status >= ' . SS_UNSHIPPED . ' AND oi.shipping_status <> ' . SS_RECEIVED . ')';
		$confirmed_count = get_order_where_count($this->user_id, 0, $where_confirmed);
		$order_num = array('all_order' => $all_order, 'pay_count' => $pay_count, 'confirmed_count' => $confirmed_count);
		$this->assign('order_num', $order_num);
		$this->assign('status', $status);
		$this->assign('page_title', L('order_list_lnk'));
		$this->display();
	}

	public function actionDetail()
	{
		$order_id = I('order_id', 0, 'intval');
		$noTime = gmtime();
		$date = array('order_sn', 'order_status', 'shipping_status', 'pay_status', 'shipping_time', 'auto_delivery_time');
		$orderInfo = get_table_date('order_info', 'order_id = \'' . $order_id . '\' and user_id = \'' . $this->user_id . '\'', $date);

		if ($GLOBALS['_CFG']['open_delivery_time'] == 1) {
			if ($orderInfo['order_status'] == 5 && $orderInfo['shipping_status'] == 1 && $orderInfo['pay_status'] == 2) {
				$delivery_time = $orderInfo['shipping_time'] + 24 * 3600 * $orderInfo['auto_delivery_time'];

				if ($delivery_time < $noTime) {
					$sql = 'update {pre}order_info set order_status = \'' . OS_SPLITED . '\', shipping_status = \'' . SS_RECEIVED . '\', pay_status = \'' . PS_PAYED . ('\' where order_id = \'' . $order_id . '\'');
					$this->db->query($sql);
					$note = L('self_motion_goods');
					order_action($orderInfo['order_sn'], OS_SPLITED, SS_RECEIVED, PS_PAYED, $note, L('buyer'), 0, gmtime());
				}
			}
		}

		$order = get_order_detail($order_id, $this->user_id);

		if ($order === false) {
			$this->err->show(L('back_home_lnk'), './');
			exit();
		}

		$order['is_pay'] = $order['pay_status'];
		$sql = 'SELECT id, store_id,pick_code  FROM' . $this->ecs->table('store_order') . (' WHERE order_id = \'' . $order_id . '\'');
		$stores = $this->db->getRow($sql);

		if (!empty($stores)) {
			$order['store_id'] = $stores['store_id'];
			$order['pick_code'] = $stores['pick_code'];
			$sql = 'SELECT o.*,p.region_name as province,c.region_name as city,d.region_name as district FROM' . $this->ecs->table('offline_store') . ' AS o ' . 'LEFT JOIN ' . $this->ecs->table('region') . ' AS p ON p.region_id = o.province ' . 'LEFT JOIN ' . $this->ecs->table('region') . ' AS c ON c.region_id = o.city ' . 'LEFT JOIN ' . $this->ecs->table('region') . ' AS d ON d.region_id = o.district WHERE o.id = \'' . $order['store_id'] . '\'';
			$offline_store = $this->db->getRow($sql);
			$this->assign('offline_store', $offline_store);
			$this->assign('store_id', $stores['id']);
		}

		$ru_id = $this->db->getRow('SELECT ru_id FROM ' . $this->ecs->table('order_goods') . ' WHERE order_id = ' . $order['order_id']);

		if ($ru_id) {
			$order['shop_name'] = get_shop_name($ru_id['ru_id'], 1);
			$order['shopUrl'] = url('store/index/index', array('id' => $ru_id));
		}

		if ($order['extension_code'] != 'group_buy' && $order['extension_code'] != 'exchange_goods') {
			$this->assign('allow_to_cart', 1);
		}

		$goods_list = order_goods($order_id);
		$goods_count = 0;
		$package_goods_count = 0;
		$package_list_total = 0;

		foreach ($goods_list as $key => $value) {
			$goods_list[$key]['market_price'] = price_format($value['market_price'], false);
			$goods_list[$key]['goods_price'] = price_format($value['goods_price'], false);
			$goods_list[$key]['subtotal'] = price_format($value['subtotal'], false);

			if ($value['is_real'] == 0) {
				$goods_list[$key]['virtual_info'] = get_virtual_goods_info($value['rec_id']);
			}

			if ($value['extension_code'] == 'package_buy') {
				$package_goods_count++;

				foreach ($value['package_goods_list'] as $package_goods_val) {
					$package_list_total += $package_goods_val['rank_price'] * $package_goods_val['goods_number'];
				}

				$goods_list[$key]['package_list_total'] = $package_list_total;
				$goods_list[$key]['package_list_saving'] = $value['subtotal'] - $package_list_total;
				$goods_list[$key]['format_package_list_total'] = price_format($goods_list[$key]['package_list_total']);
				$goods_list[$key]['format_package_list_saving'] = price_format($goods_list[$key]['package_list_saving']);
			}
			else {
				$goods_count++;
			}
		}

		$delay = 0;
		if ($order['order_status'] == OS_SPLITED && $order['pay_status'] == PS_PAYED && $order['shipping_status'] == SS_SHIPPED) {
			$order_delay_day = C('shop.order_delay_day') * 86400;
			$auto_delivery_time = $order['auto_delivery_time'] * 86400;
			$shipping_time = $order['shipping_time'];

			if ($auto_delivery_time + $shipping_time - $noTime < $order_delay_day) {
				$map['review_status'] = array('neq', 1);
				$map['order_id'] = $order['order_id'];
				$num = dao('order_delayed')->where($map)->count('delayed_id');
				if (C('shop.open_order_delay') == 1 && $num < C('shop.order_delay_num')) {
					$delay = 1;
				}
			}
		}

		$delay_type = dao('order_delayed')->where(array('order_id' => $order['order_id']))->order('delayed_id DESC')->getField('review_status');

		if (isset($delay_type)) {
			if ($delay_type == 0) {
				$delay_type = '未审核';
			}

			if ($delay_type == 1) {
				$delay_type = '已审核';
			}

			if ($delay_type == 2) {
				$delay_type = '审核未通过';
			}
		}
		else {
			$delay_type = '未提交';
		}

		if (0 < $order['order_amount']) {
			if ($order['order_status'] == OS_UNCONFIRMED || $order['order_status'] == OS_CONFIRMED) {
				$user = user_info($order['user_id']);

				if (0 < $user['user_money'] + $user['credit_line']) {
					$this->assign('allow_edit_surplus', 1);
					$this->assign('max_surplus', sprintf(L('max_surplus'), $user['user_money']));
				}
			}
		}

		if (0 < $order['order_amount'] && ($order['pay_status'] == PS_UNPAYED || $order['pay_status'] == PS_PAYED_PART) && $order['shipping_status'] == SS_UNSHIPPED) {
			$payment_list = available_payment_list(false, 0, true);

			if (is_array($payment_list)) {
				foreach ($payment_list as $key => $payment) {
					if (substr($payment['pay_code'], 0, 4) == 'pay_') {
						unset($payment_list[$key]);
						continue;
					}

					if (!file_exists(ADDONS_PATH . 'payment/' . $payment['pay_code'] . '.php')) {
						unset($payment_list[$key]);
					}

					if ($payment['pay_id'] == $order['pay_id'] || $payment['pay_code'] == 'balance') {
						unset($payment_list[$key]);
					}

					if ($payment['pay_code'] == 'wxpay') {
						if (!is_dir(APP_WECHAT_PATH)) {
							unset($payment_list[$key]);
						}

						if (is_wechat_browser() == false && is_wxh5() == 0) {
							unset($payment_list[$key]);
						}
					}
				}
			}

			$this->assign('payment_list', $payment_list);
		}

		$os = L('os');
		$ps = L('ps');
		$ss = L('ss');

		if ($order['order_status'] == OS_UNCONFIRMED) {
			$order['handler'] = '<span class="box-flex text-right"></span><a class="btn-default box-flex cancel-order" type="button" href="javascript:;"  data-item="' . $order['order_id'] . '">' . L('cancel') . '</a>';
		}
		else if ($order['order_status'] == OS_SPLITED) {
			if ($order['shipping_status'] == SS_SHIPPED) {
				@$order['handler'] = '<span class="box-flex text-right"></span><a class="btn-submit received-order"  data-item="' . $order['order_id'] . '">' . L('received') . '</a>';
			}
			else if ($order['shipping_status'] == SS_RECEIVED) {
				@$order['handler'] = '<span class="order-checkout-text box">' . L('ss_received') . '</span>';
			}
			else if ($order['pay_status'] == PS_UNPAYED) {
				@$order['handler'] = '<span class="box-flex text-right"></span><a class="btn-submit" href="' . url('user/order/detail', array('order_id' => $order['order_id'])) . '" >' . L('pay_money') . '</a>';
			}
			else {
				$order['handler'] = '<span class="order-checkout-text box">' . $ss[$order['shipping_status']] . '</span>';
			}
		}
		else {
			if ($order['order_status'] == OS_CONFIRMED && $order['pay_status'] == PS_UNPAYED) {
				$order['handler'] = '<span class=\'box-flex\'></span><a class=\'btn-default box-flex\' type=\'button\' >' . $ps[$order['pay_status']] . '</a>';
			}
			else if ($order['pay_status'] == PS_PAYED_PART) {
				if ($order['extension_code'] == 'presale') {
					$result = presale_settle_status($order['extension_id']);

					if ($result['settle_status'] == 1) {
						$order['msg'] = '尾款支付时间:';
						$start_time = trim($result['start_time']);
						$end_time = trim($result['end_time']);
						@$order['handler'] = '<span class=box-flex text-right>' . $start_time . '至' . $end_time . '</span>';
					}

					if ($result['settle_status'] == 0) {
						$order['msg'] = '尾款支付时间:';
						$start_time = trim($result['start_time']);
						$end_time = trim($result['end_time']);
						$order['hidden_pay_button'] = 1;
						@$order['handler'] = '<span class=box-flex text-right>' . $start_time . '至' . $end_time . '</span>';
					}

					if ($result['settle_status'] == -1) {
						$order['hidden_pay_button'] = 1;
						$order['msg'] = '超出尾款支付时间';
						$end_time = trim($result['end_time']);
						@$order['handler'] = '<span class=\'box-flex text-right\'>' . $end_time . '</span>';
					}
				}
			}
			else {
				$order['handler'] = '<span class="order-checkout-text box">' . $os[$order['order_status']] . '</span>';
			}

			if ($order['order_status'] == OS_CANCELED) {
				$order['hidden_pay_button'] = 1;
			}
		}

		$order['order_status'] = $os[$order[order_status]];
		$order['pay_status'] = $ps[$order[pay_status]];
		$order['shipping_status'] = $ss[$order['shipping_status']];
		$order['c'] = get_region_name($order['country']);
		$order['detail_address'] .= $order['c']['region_name'];
		$order['p'] = get_region_name($order['province']);
		$order['detail_address'] .= $order['p']['region_name'];
		$order['cc'] = get_region_name($order['city']);
		$order['detail_address'] .= $order['cc']['region_name'];
		$order['dd'] = get_region_name($order['district']);
		$order['detail_address'] .= $order['dd']['region_name'];
		$order['detail_address'] .= $order['address'];
		$order['delay'] = $delay;
		$order['delay_type'] = $delay_type;
		$sql = 'SELECT * FROM ' . $this->ecs->table('shipping_point') . ' WHERE id IN (SELECT point_id FROM ' . $this->ecs->table('order_info') . (' WHERE order_id=\'' . $order_id . '\')');
		$order['point'] = $this->db->getRow($sql);

		if ($order['point']) {
			$order['point']['pickDate'] = $order['shipping_dateStr'];
		}

		if (is_dir(APP_TEAM_PATH)) {
			if (0 < $order['team_id']) {
				$failure = get_team_info($order['team_id'], $order['order_id']);
				$order['failure'] = $failure;
			}
		}

		$im_dialog = M()->query('SHOW TABLES LIKE "{pre}im_dialog"');
		$zkf = dao('seller_shopinfo')->field('kf_type, kf_qq, kf_ww, meiqia, kf_im_switch')->where(array('ru_id' => '0'))->find();
		if ($zkf['kf_im_switch'] == 1 && $im_dialog) {
			$kefu = url('chat/index/index');
		}
		else if ($zkf['kf_im_switch'] == 1) {
			$kefu = url('chat/yunwang/index');
		}
		else if ($zkf['meiqia']) {
			$kefu = 'javascript:meiqia_chat();';
		}
		else if ($zkf['kf_type'] == 1) {
			$kefu = 'http://www.taobao.com/webww/ww.php?ver=3&touid=' . preg_replace('/^[^\\-]*\\|/is', '', $zkf['kf_ww']) . '&siteid=cntaobao&status=1&charset=utf-8';
		}
		else {
			$kefu = 'http://wpa.qq.com/msgrd?v=3&uin=' . preg_replace('/^[^\\-]*\\|/is', '', $zkf['kf_qq']) . '&site=qq&menu=yes';
		}

		$this->assign('kefu', $kefu);
		$this->assign('order', $order);
		$this->assign('goods_list', $goods_list);
		$this->assign('goods_count', $goods_count);
		$this->assign('package_goods_count', $package_goods_count);
		$this->assign('page_title', L('order_detail'));
		$this->display();
	}

	public function actionApplyReturnList()
	{
		if (isset($_REQUEST['rec_id'])) {
			$recr_id = intval($_REQUEST['rec_id']);
		}
		else {
			exit('invalid parameter');
		}

		$_REQUEST['order_id'] = intval($_REQUEST['order_id']);
		$sql = ' SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $_REQUEST['order_id'] . '\' AND shipping_status > 0 ';
		$return_allowable = $GLOBALS['db']->getOne($sql);
		$this->assign('return_allowable', $return_allowable);
		$goods_info = rec_goods($recr_id);
		$this->assign('goods', $goods_info);
		$this->display();
	}

	public function actionDelay()
	{
		$order_id = I('order_id');
		$time = gmtime();

		if (IS_AJAX) {
			$map['review_status'] = array('neq', 1);
			$map['order_id'] = $order_id;
			$num = dao('order_delayed')->where($map)->count();

			if ($num < 1) {
				$delay_num = dao('order_delayed')->where(array('order_id' => $order_id))->count();

				if ($delay_num < C('shop.order_delay_num')) {
					dao('order_delayed')->add(array('order_id' => $order_id, 'apply_time' => $time));
					exit(json_encode(array('y' => 1, 'msg' => '申请成功')));
				}
				else {
					exit(json_encode(array('n' => 1, 'msg' => '申请次数过多')));
				}
			}
			else {
				exit(json_encode(array('n' => 1, 'msg' => '有未审核的申请')));
			}
		}
		else {
			show_message(L('msg_unfilled_or_receive'));
		}
	}

	public function actionOrderTracking()
	{
		$order_id = I('order_id', 0, 'intval');
		$order = get_order_detail($order_id, $this->user_id);

		if ($order === false) {
			$this->err->show(L('back_home_lnk'), './');
			exit();
		}

		if ($order['invoice_no']) {
			preg_match('/^<a.*href="(.*?)">/is', $order['invoice_no'], $url);

			if ($url[1]) {
				redirect($url[1]);
			}
		}

		show_message(L('msg_unfilled_or_receive'), L('user_center'), url('user/index/index'));
	}

	public function actionAffirmReceived()
	{
		$user_id = $this->user_id;
		$order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;

		if (affirm_received($order_id, $user_id)) {
			exit(json_encode(array('y' => 1)));
		}
		else {
			show_message(L('msg_unfilled_or_receive'));
		}
	}

	public function actionDelOrder()
	{
		$order_id = I('order_id');

		if (IS_AJAX) {
			$sql = 'UPDATE {pre}order_info SET `is_delete`=1 where order_id=' . $order_id;
			$this->db->query($sql);
			exit(json_encode(array('y' => 1)));
		}
	}

	public function actionCancel()
	{
		$order_id = I('order_id', 0, 'intval');

		if (IS_AJAX) {
			if (cancel_order($order_id, $this->user_id)) {
				exit(json_encode(array('y' => 1)));
			}
			else {
				exit(json_encode(array('n' => 1)));
			}
		}
	}

	public function actionchecklogin()
	{
		if (!$this->user_id) {
			$url = urlencode(__HOST__ . $_SERVER['REQUEST_URI']);

			if (IS_POST) {
				$url = urlencode($_SERVER['HTTP_REFERER']);
			}

			ecs_header('Location: ' . url('user/login/index', array('back_act' => $url)));
			exit();
		}
	}

	public function Get_Order_Where_Count($user_id = 0, $show_type = 0, $where = '')
	{
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi' . (' WHERE oi.user_id = \'' . $user_id . '\' and oi.is_delete = \'' . $show_type . '\'') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi_2 where oi_2.main_order_id = oi.order_id) = 0 ' . $where;
		return $GLOBALS['db']->getOne($sql);
	}

	public function actionChangePayment()
	{
		$pay_id = intval($_POST['pay_id']);
		$order_id = intval($_POST['order_id']);
		if ($pay_id <= 0 || $order_id <= 0) {
			$this->redirect('index/index');
		}

		$payment_info = payment_info($pay_id);

		if (empty($payment_info)) {
			$this->redirect('index/index');
		}

		$order = order_info($order_id);
		if (empty($order) || $_SESSION['user_id'] != $order['user_id']) {
			$this->redirect('index/index');
		}

		if (($order['pay_status'] == PS_UNPAYED || $order['pay_status'] == PS_PAYED_PART) && $order['shipping_status'] == SS_UNSHIPPED && 0 < $order['goods_amount'] && $order['pay_id'] != $pay_id) {
			$order_amount = $order['order_amount'] - $order['pay_fee'];
			$pay_fee = pay_fee($pay_id, $order_amount);
			$order_amount += $pay_fee;
			$data['pay_id'] = $pay_id;
			$data['pay_name'] = $payment_info['pay_name'];
			$data['pay_fee'] = $pay_fee;
			$data['order_amount'] = $order_amount;
			$where['order_id'] = $order_id;
			$this->model->table('order_info')->data($data)->where($where)->save();
		}

		$this->redirect('detail', array('order_id' => $order_id));
	}

	public function actionSurplusPay()
	{
		$order_id = I('post.order_id', '', 'intval');
		$type = I('get.type');

		if ($order_id <= 0) {
			ecs_header("Location: ./\n");
			exit();
		}

		$order = order_info($order_id);
		if ($type == 'presale' && $order['pay_status'] == PS_PAYED_PART) {
			$result = presale_settle_status($order['extension_id']);
			if ($result['settle_status'] == 0 || $result['settle_status'] == -1) {
				ecs_header("Location: ./\n");
				exit();
			}
		}

		if ($_SESSION['user_id'] != $order['user_id']) {
			ecs_header("Location: ./\n");
			exit();
		}

		if ($order['pay_status'] != PS_UNPAYED || $order['order_amount'] <= 0) {
			if ($order['pay_status'] != PS_PAYED_PART) {
				$GLOBALS['err']->add(L('error_order_is_paid'));
				$GLOBALS['err']->show(L('order_detail'), url('user/order/detail', array('order_id' => $order_id)));
			}
		}

		$surplus = floatval($_POST['surplus']);

		if ($surplus <= 0) {
			$GLOBALS['err']->add(L('error_surplus_invalid'));
			$GLOBALS['err']->show(L('order_detail'), url('user/order/detail', array('order_id' => $order_id)));
		}

		$user_info = user_info($_SESSION['user_id']);

		if ($user_info['user_money'] < $order['order_amount']) {
			show_message(L('balance_not_enough'), L('back_up_page'), url('user/order/detail', array('order_id' => $order_id)));
		}

		if ($order['order_amount'] < $surplus) {
			$surplus = $order['order_amount'];
		}

		$order['surplus'] += $surplus;
		$order['order_amount'] -= $surplus;

		if (0 < $order['order_amount']) {
			$cod_fee = 0;

			if (0 < $order['shipping_id']) {
				$regions = array($order['country'], $order['province'], $order['city'], $order['district']);
				$shipping = shipping_area_info($order['shipping_id'], $regions);

				if ($shipping['support_cod'] == '1') {
					$cod_fee = $shipping['pay_fee'];
				}
			}

			$pay_fee = 0;

			if (0 < $order['pay_id']) {
				$pay_fee = pay_fee($order['pay_id'], $order['order_amount'], $cod_fee);
			}

			$order['pay_fee'] = $pay_fee;
			$order['order_amount'] += $pay_fee;
		}

		if ($order['order_amount'] == 0) {
			$amount = $order['goods_amount'] + $order['shipping_fee'];
			$paid = $order['money_paid'] + $order['surplus'];
			if ($_POST['pay_status'] == 'presale' && $paid < $amount) {
				$order['pay_status'] = PS_PAYED_PART;
				$order['order_amount'] = $amount - $paid;
			}
			else {
				$order['pay_status'] = PS_PAYED;
			}

			if ($order['order_status'] == OS_UNCONFIRMED) {
				$order['order_status'] = OS_CONFIRMED;
				$order['confirm_time'] = gmtime();
			}

			$order['pay_time'] = gmtime();
		}

		$order = addslashes_deep($order);
		update_order($order_id, $order);
		$is_update_sale = is_update_sale($order['order_id']);
		if (C('shop.sales_volume_time') == SALES_PAY && $is_update_sale == 0) {
			get_goods_sale($order['order_id']);
		}

		$change_desc = sprintf(L('pay_order_by_surplus'), $order['order_sn']);
		log_account_change($order['user_id'], -1 * $surplus, 0, 0, 0, $change_desc);

		if (is_dir(APP_WECHAT_PATH)) {
			$users = pay_money($this->user_id);
			$pushData = array(
				'keyword1' => array('value' => $order['pay_time'], 'color' => '#173177'),
				'keyword2' => array('value' => '消费扣减', 'color' => '#173177'),
				'keyword3' => array('value' => $surplus, 'color' => '#173177'),
				'keyword4' => array('value' => $users['user_money'], 'color' => '#173177'),
				'remark'   => array('value' => '详情请点击进入会员中心-资金管理页面查询!', 'color' => '#173177')
				);
			$url = __HOST__ . url('user/account/index');
			push_template('OPENTM401833445', $pushData, $url);
		}

		$this->redirect('user/order/detail', array('order_id' => $order_id));
		exit();
	}

	public function actionComplaintList()
	{
		if (IS_AJAX) {
			$size = 10;
			$page = I('page', 1, 'intval');
			$status = I('status', '0', 'intval');
			$time = gmtime();
			$dealy_time = 15 * 86400;
			$where_zc_order = ' AND oi.is_zc_order = 0 ';
			$where_confirmed = '';

			if ($status == 0) {
				$where_confirmed = ' AND oi.order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) . '  ' . 'AND IF(oi.pay_status = ' . PS_PAYED . ', IF(oi.shipping_status = ' . SS_RECEIVED . ', oi.shipping_status = \'' . SS_RECEIVED . ('\' AND (\'' . $time . '\'- oi.confirm_take_time) < \'' . $dealy_time . '\', ') . db_create_in(array(SS_RECEIVED), 'oi.shipping_status', 'NOT') . ') ';
				$where_confirmed .= 'AND oi.pay_status ' . db_create_in(array(PS_PAYED)) . ', IF(oi.shipping_status = ' . SS_RECEIVED . ', ' . db_create_in(array(SS_RECEIVED), 'oi.shipping_status') . (' AND (\'' . $time . '\'- oi.confirm_take_time) < \'' . $dealy_time . '\', ') . db_create_in(array(SS_UNSHIPPED), 'oi.shipping_status', 'NOT') . '))' . $where_zc_order;
			}

			$complaint_list = get_complaint_list($size, $page, $where_confirmed, $status);
			exit(json_encode(array('order_list' => $complaint_list['list'], 'totalPage' => $complaint_list['totalPage'])));
		}

		$this->assign('status', $status);
		$this->assign('page_title', '待申请列表');
		$this->display();
	}

	public function actionComplaintApply()
	{
		$complaint_id = I('complaint_id', 0, 'intval');
		$order_id = I('order_id', 0, 'intval');
		$this->assign('complaint_id', $complaint_id);
		$this->assign('order_id', $order_id);
		$where = '';

		if (0 < $complaint_id) {
			$complaint_info = get_complaint_info($complaint_id);
			$order_id = $complaint_info['order_id'];

			if (1 < $complaint_info['complaint_state']) {
				$talk_list = checkTalkView($complaint_id, 'user');
				$this->assign('talk_list', $talk_list);
			}

			$where = ' AND complaint_id = \'' . $complaint_id . '\'';
			$this->assign('complaint_info', $complaint_info);
		}
		else {
			$where = ' AND complaint_id = 0';
			$complaint_title = get_complaint_title();
			$this->assign('complaint_title', $complaint_title);
		}

		$orders = order_info($order_id);
		$orders['order_goods'] = get_order_goods_toInfo($order_id);
		$orders['shop_name'] = get_shop_name($orders['ru_id'], 1);
		$this->assign('orders_goods', $orders['order_goods']);
		$sql = 'SELECT img_id as id , order_id, complaint_id,user_id,img_file as comment_img FROM {pre}complaint_img WHERE user_id = \'' . $_SESSION['user_id'] . ']\' AND order_id = \'' . $order_id . '\' ' . $where . ' ORDER BY  id DESC';
		$img_list = $this->db->getAll($sql);
		$img = array();

		foreach ($img_list as $key => $val) {
			$img[$key]['img_id'] = $val['id'];
			$img[$key]['pic'] = get_image_path($val['comment_img']);
		}

		$this->assign('img', $img);
		$this->assign('page_title', '交易投诉详情');
		$this->display();
	}

	public function actionComplaintSubmit()
	{
		$order_id = I('order_id', 0, 'intval');
		$title_id = I('title_id', 0, 'intval');
		$complaint_content = !empty($_REQUEST['complaint_content']) ? trim($_REQUEST['complaint_content']) : '';
		$sql = 'SELECT COUNT(*) FROM {pre}complaint WHERE order_id = \'' . $order_id . '\'';
		$complaint_count = $this->db->getOne($sql);

		if (0 < $complaint_count) {
			show_message(L('complaint_reprat'));
		}

		if ($title_id == 0) {
			show_message(L('complaint_title_null'));
		}
		else if ($complaint_content == '') {
			show_message(L('complaint_content_null'));
		}
		else {
			$sql = 'SELECT og.ru_id,oi.order_sn FROM {pre}order_info  AS oi LEFT JOIN {pre}order_goods AS og ON og.order_id = oi.order_id  WHERE oi.order_id = \'' . $order_id . '\' LIMIT 1';
			$order_info = $this->db->getRow($sql);
			$shop_name = get_shop_name($order_info['ru_id'], 1);
			$time = gmtime();
			$other = array('user_id' => $_SESSION['user_id'], 'user_name' => $_SESSION['user_name'], 'order_id' => $order_id, 'shop_name' => $shop_name, 'order_sn' => $order_info['order_sn'], 'ru_id' => $order_info['ru_id'], 'title_id' => $title_id, 'add_time' => $time, 'complaint_content' => $complaint_content);
			$complaint_id = dao('complaint')->add($other);

			if (0 < $complaint_id) {
				$sql = 'UPDATE {pre}complaint_img SET complaint_id = \'' . $complaint_id . '\' WHERE user_id = \'' . $_SESSION['user_id'] . '\' AND order_id = \'' . $order_id . '\' AND complaint_id = 0';
				$this->db->query($sql);
			}

			show_message(L('complaint_success'), L('back_complaint_list'), url('order/complaint_list'));
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
		$order_id = I('order_id');

		if (empty($type)) {
			echo json_encode(array('error' => 1, 'content' => '图片类型不正确'));
			return NULL;
		}

		$result = $this->upload('data/complaint_img', false, 2, array(600, 600));
		$path = $result['url']['myfile']['url'];
		$add_time = gmtime();
		$sql = 'INSERT INTO {pre}complaint_img (order_id,user_id,img_file,complaint_id)values(' . $order_id . ',' . $user_id . ',\'' . $path . '\',0)';
		$GLOBALS['db']->query($sql);
		$sql = 'SELECT img_id, img_file FROM {pre}complaint_img WHERE user_id = ' . $user_id . ' and order_id = ' . $order_id;
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
		$id = I('id', 0, 'intval');
		$rec_id = I('order_id', 0, 'intval');
		$result = array('error' => 0, 'content' => '');
		$sql = 'select img_file from {pre}complaint_img where user_id = \'' . $_SESSION['user_id'] . ('\' and order_id = \'' . $rec_id . '\'') . ' and img_id=' . $id;
		$img_list = $GLOBALS['db']->getAll($sql);

		foreach ($img_list as $key => $row) {
			get_oss_del_file(array($row['img_file']));
			@unlink(get_image_path($row['img_file']));
		}

		$sql = 'delete from {pre}complaint_img where user_id = \'' . $_SESSION['user_id'] . ('\' and order_id = \'' . $rec_id . '\'') . ' and img_id=' . $id;
		$GLOBALS['db']->query($sql);
		echo json_encode($result);
	}

	public function actionTalkRelease()
	{
		$talk_id = !empty($_REQUEST['talk_id']) ? intval($_REQUEST['talk_id']) : 0;
		$complaint_id = !empty($_REQUEST['complaint_id']) ? intval($_REQUEST['complaint_id']) : 0;
		$talk_content = !empty($_REQUEST['talk_content']) ? trim($_REQUEST['talk_content']) : '';
		$type = !empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;

		if ($type == 0) {
			$complaint_talk = array('complaint_id' => $complaint_id, 'talk_member_id' => $_SESSION['user_id'], 'talk_member_name' => $_SESSION['user_name'], 'talk_member_type' => 1, 'talk_content' => $talk_content, 'talk_time' => gmtime(), 'view_state' => 'user');
			$sql = 'INSERT INTO' . $this->ecs->table('complaint_talk') . ' (`complaint_id`,`talk_member_id`,`talk_member_name`,`talk_member_type`,`talk_content`,`talk_time`,`view_state`) VALUES' . (' (\'' . $complaint_id . '\',\'' . $_SESSION['user_id'] . '\',\'' . $_SESSION['user_name'] . '\',1,\'' . $talk_content . '\',\'' . $complaint_talk['talk_time'] . '\',\'user\')');
			$this->db->query($sql);
		}

		$talk_list = checkTalkView($complaint_id, 'user');
		$this->assign('talk_list', $talk_list);
		$result['content'] = $this->fetch('talklist');
		exit(json_encode($result));
	}

	public function actionDelCompalint()
	{
		$complaint_id = I('compalint_id', 0, 'intval');

		if (0 < $_SESSION['user_id']) {
			del_complaint_img($complaint_id);
			del_complaint_img($complaint_id, 'appeal_img');
			del_complaint_talk($complaint_id);
			$sql = 'DELETE FROM {pre}complaint WHERE complaint_id = \'' . $complaint_id . '\'';
			$this->db->query($sql);
			exit(json_encode(array('y' => 1)));
		}
	}

	public function actionArbitration()
	{
		$complaint_id = !empty($_REQUEST['complaint_id']) ? intval($_REQUEST['complaint_id']) : 0;
		$complaint_state = !empty($_REQUEST['complaint_state']) ? intval($_REQUEST['complaint_state']) : 3;
		$set = '';

		if ($complaint_state == 4) {
			$set = ',end_handle_messg=\'买家自行关闭\'';
		}

		$sql = 'UPDATE {pre}complaint SET complaint_state = \'' . $complaint_state . '\' ' . $set . ' WHERE complaint_id = \'' . $complaint_id . '\'';
		$this->db->query($sql);
		show_message(L('apply_success'), '', url('user/order/complaint_list'));
	}
}

?>
