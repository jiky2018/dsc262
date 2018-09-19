<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function edit_profile($profile)
{
	if (empty($profile['user_id'])) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['not_login']);
		return false;
	}

	$cfg = array();
	$cfg['username'] = $GLOBALS['db']->getOne('SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id=\'' . $profile['user_id'] . '\'');

	if (isset($profile['sex'])) {
		$cfg['gender'] = intval($profile['sex']);
	}

	if (!empty($profile['email'])) {
		if (!is_email($profile['email'])) {
			$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_invalid'], $profile['email']));
			return false;
		}

		$cfg['email'] = $profile['email'];
	}

	if (!empty($profile['birthday'])) {
		$cfg['bday'] = $profile['birthday'];
	}

	if (!$GLOBALS['user']->edit_user($cfg)) {
		if ($GLOBALS['user']->error == ERR_EMAIL_EXISTS) {
			$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_exist'], $profile['email']));
		}
		else {
			$GLOBALS['err']->add('DB ERROR!');
		}

		return false;
	}

	$other_key_array = array('msn', 'qq', 'office_phone', 'home_phone', 'mobile_phone');

	foreach ($profile['other'] as $key => $val) {
		if (!in_array($key, $other_key_array)) {
			unset($profile['other'][$key]);
		}
		else {
			$profile['other'][$key] = htmlspecialchars(trim($val));
		}
	}

	if (!empty($profile['other'])) {
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('users'), $profile['other'], 'UPDATE', 'user_id = \'' . $profile['user_id'] . '\'');
	}

	return true;
}

function get_profile($user_id)
{
	global $user;
	$info = array();
	$infos = array();
	$sql = 'SELECT user_name, birthday, sex, question, answer, rank_points, pay_points,user_money, user_rank,' . ' msn, qq, office_phone, home_phone, mobile_phone, passwd_question, passwd_answer ' . 'FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $user_id . '\'');
	$infos = $GLOBALS['db']->getRow($sql);
	$infos['user_name'] = addslashes($infos['user_name']);
	$row = $user->get_profile_by_name($infos['user_name']);
	$_SESSION['email'] = $row['email'];

	if (0 < $infos['user_rank']) {
		$sql = 'SELECT rank_id, rank_name, discount FROM ' . $GLOBALS['ecs']->table('user_rank') . (' WHERE rank_id = \'' . $infos['user_rank'] . '\'');
	}
	else {
		$sql = 'SELECT rank_id, rank_name, discount, min_points' . ' FROM ' . $GLOBALS['ecs']->table('user_rank') . ' WHERE min_points<= ' . intval($infos['rank_points']) . ' ORDER BY min_points DESC';
	}

	if ($row = $GLOBALS['db']->getRow($sql)) {
		$info['rank_name'] = $row['rank_name'];
	}
	else {
		$info['rank_name'] = $GLOBALS['_LANG']['undifine_rank'];
	}

	$cur_date = date('Y-m-d H:i:s');
	$bonus = array();
	$sql = 'SELECT type_name, type_money ' . 'FROM ' . $GLOBALS['ecs']->table('bonus_type') . ' AS t1, ' . $GLOBALS['ecs']->table('user_bonus') . ' AS t2 ' . ('WHERE t1.type_id = t2.bonus_type_id AND t2.user_id = \'' . $user_id . '\' AND t1.use_start_date <= \'' . $cur_date . '\' ') . ('AND t1.use_end_date > \'' . $cur_date . '\' AND t2.order_id = 0');
	$bonus = $GLOBALS['db']->getAll($sql);

	if ($bonus) {
		$i = 0;

		for ($count = count($bonus); $i < $count; $i++) {
			$bonus[$i]['type_money'] = price_format($bonus[$i]['type_money'], false);
		}
	}

	$info['discount'] = $_SESSION['discount'] * 100 . '%';
	$info['email'] = $_SESSION['email'];
	$info['user_name'] = $_SESSION['user_name'];
	$info['rank_points'] = isset($infos['rank_points']) ? $infos['rank_points'] : '';
	$info['pay_points'] = isset($infos['pay_points']) ? $infos['pay_points'] : 0;
	$info['user_money'] = isset($infos['user_money']) ? $infos['user_money'] : 0;
	$info['sex'] = isset($infos['sex']) ? $infos['sex'] : 0;
	$info['birthday'] = isset($infos['birthday']) ? $infos['birthday'] : '';
	$info['question'] = isset($infos['question']) ? htmlspecialchars($infos['question']) : '';
	$info['user_money'] = price_format($info['user_money'], false);
	$info['pay_points'] = $info['pay_points'] . $GLOBALS['_CFG']['integral_name'];
	$info['bonus'] = $bonus;
	$info['qq'] = $infos['qq'];
	$info['msn'] = $infos['msn'];
	$info['office_phone'] = $infos['office_phone'];
	$info['home_phone'] = $infos['home_phone'];
	$info['mobile_phone'] = $infos['mobile_phone'];
	$info['passwd_question'] = $infos['passwd_question'];
	$info['passwd_answer'] = $infos['passwd_answer'];
	return $info;
}

function get_consignee_list($user_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_address') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 10');
	return $GLOBALS['db']->getAll($sql);
}

function add_bonus($user_id, $bouns_sn, $bonus_password = '')
{
	if (empty($user_id)) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['not_login']);
		return false;
	}

	$sql = 'SELECT bonus_id, bonus_sn, user_id, bonus_type_id FROM ' . $GLOBALS['ecs']->table('user_bonus') . (' WHERE bonus_sn = \'' . $bouns_sn . '\'') . ' AND bonus_password =\'' . $bonus_password . '\'';
	$row = $GLOBALS['db']->getRow($sql);

	if ($row) {
		if ($row['user_id'] == 0) {
			$sql = 'SELECT send_end_date, use_end_date ' . ' FROM ' . $GLOBALS['ecs']->table('bonus_type') . ' WHERE type_id = \'' . $row['bonus_type_id'] . '\'';
			$bonus_time = $GLOBALS['db']->getRow($sql);
			$now = gmtime();

			if ($bonus_time['use_end_date'] < $now) {
				$GLOBALS['err']->add($GLOBALS['_LANG']['bonus_use_expire']);
				return false;
			}

			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_bonus') . (' SET user_id = \'' . $user_id . '\' ') . ('WHERE bonus_id = \'' . $row['bonus_id'] . '\'');
			$result = $GLOBALS['db']->query($sql);

			if ($result) {
				return true;
			}
			else {
				return $GLOBALS['db']->errorMsg();
			}
		}
		else {
			if ($row['user_id'] == $user_id) {
				$GLOBALS['err']->add($GLOBALS['_LANG']['bonus_is_used']);
			}
			else {
				$GLOBALS['err']->add($GLOBALS['_LANG']['bonus_is_used_by_other']);
			}

			return false;
		}
	}
	else {
		$GLOBALS['err']->add($GLOBALS['_LANG']['bonus_not_exist']);
		return false;
	}
}

function get_order_number($user_id, $status = '0')
{
	$leftjoin = '';
	$where = ' where 1';

	if (0 < $user_id) {
		$where .= ' and o.user_id=\'' . $user_id . '\' ';
	}

	switch ($status) {
	case 'NOT_CONFIRM':
		$where .= ' and order_status=0 ';
		break;

	case 'NOT_PAID':
		$where .= ' and pay_status=0 and shipping_status=0 and order_status!=2';
		break;

	case 'NOT_SEND':
		$where .= ' and pay_status=2 and shipping_status=0 ';
		break;

	case 'NOT_RECEIVE':
		$where .= ' and pay_status=2 and shipping_status=1 and order_status=5 ';
		break;

	case 'NOT_PICKUP':
		$where .= ' and shipping_id=8 and pay_status=2 and order_status<>5 ';
		break;

	case 'NOT_COMMENT':
		$leftjoin .= ' left join ' . $GLOBALS['ecs']->table('comment') . ' as com on o.order_id<>com.order_id ';
		$where .= ' and o.pay_status=2 and o.shipping_status=2 and o.order_status=5 ';
		break;

	case 'FINISHED':
		$where .= ' and pay_status=2 and shipping_status=2 and order_status=5 ';
		break;

	default:
		break;
	}

	$sql = 'select count( DISTINCT o.order_id) from ' . $GLOBALS['ecs']->table('order_info') . ' as o ' . $leftjoin . $where;
	return $GLOBALS['db']->getOne($sql);
}

function get_user_orders($user_id, $num = 10, $page = 1, $status = 0)
{
	$where = '';

	if ($status == 1) {
		$where = 'and oi.pay_status in(' . PS_UNPAYED . ',' . PS_PAYED_PART . ') and oi.order_status not in(' . OS_CANCELED . ',' . OS_INVALID . ',' . OS_RETURNED . ')';
	}
	else if ($status == 2) {
		$where .= ' AND oi.pay_status = ' . PS_PAYED . ' AND oi.order_status in (' . OS_CONFIRMED . ', ' . OS_SPLITED . ', ' . OS_SPLITING_PART . ') AND (oi.shipping_status >= ' . SS_UNSHIPPED . ' AND oi.shipping_status <> ' . SS_RECEIVED . ')';
		$cache_info = S('message_' . $_SESSION['user_id']);
	}

	$select = ' (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' AS c WHERE c.comment_type = 0 AND c.id_value = og.goods_id AND c.order_id = oi.order_id AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\') AS sign1, ') . '(SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment_img') . ' AS ci, ' . $GLOBALS['ecs']->table('comment') . ' AS c' . (' WHERE c.comment_type = 0 AND c.id_value = og.goods_id AND c.order_id = oi.order_id AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\' AND ci.comment_id = c.comment_id )  AS sign2, ');
	$total_arr = $GLOBALS['db']->getAll('SELECT oi.order_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi' . ' left join ' . $GLOBALS['ecs']->table('order_goods') . ' as og on oi.order_id = og.order_id' . (' WHERE oi.user_id = \'' . $user_id . '\' and oi.is_delete = \'0\' and oi.is_zc_order=0 ') . $where . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = oi.order_id) = 0 ' . ' group by oi.order_id ORDER BY oi.add_time DESC');
	$total = is_array($total_arr) ? count($total_arr) : 0;
	$start = ($page - 1) * $num;
	$arr = array();

	if (is_dir(APP_TEAM_PATH)) {
		$sql = 'SELECT og.ru_id, oi.main_order_id, oi.consignee,oi.pay_name, oi.order_id, oi.order_sn,oi.pay_time,oi.order_status, oi.shipping_status, oi.pay_status, oi.add_time, oi.shipping_time, oi.auto_delivery_time, oi.sign_time,oi.team_id,oi.extension_code, ' . $select . '(oi.goods_amount + oi.shipping_fee + oi.insure_fee + oi.pay_fee + oi.pack_fee + oi.card_fee + oi.tax - oi.discount - oi.coupons) AS total_fee, og.goods_id, ' . 'oi.invoice_no, oi.shipping_name, oi.tel, oi.email, oi.address, oi.province, oi.city, oi.district ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi' . ' left join ' . $GLOBALS['ecs']->table('order_goods') . ' as og on oi.order_id = og.order_id' . (' WHERE oi.user_id = \'' . $user_id . '\' and oi.is_delete = \'0\' and oi.is_zc_order=0  ') . $where . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = oi.order_id) = 0 ' . (' group by oi.order_id ORDER BY oi.add_time DESC LIMIT ' . $start . ', ' . $num);
	}
	else {
		$sql = 'SELECT og.ru_id, oi.main_order_id, oi.consignee,oi.pay_name, oi.order_id, oi.order_sn,oi.pay_time,oi.order_status, oi.shipping_status, oi.pay_status, oi.add_time, oi.shipping_time, oi.auto_delivery_time, oi.sign_time,oi.extension_code, ' . $select . '(oi.goods_amount + oi.shipping_fee + oi.insure_fee + oi.pay_fee + oi.pack_fee + oi.card_fee + oi.tax - oi.discount - oi.coupons) AS total_fee, og.goods_id, ' . 'oi.invoice_no, oi.shipping_name, oi.tel, oi.email, oi.address, oi.province, oi.city, oi.district ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi' . ' left join ' . $GLOBALS['ecs']->table('order_goods') . ' as og on oi.order_id = og.order_id' . (' WHERE oi.user_id = \'' . $user_id . '\' and oi.is_delete = \'0\' and oi.is_zc_order=0  ') . $where . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = oi.order_id) = 0 ' . (' group by oi.order_id ORDER BY oi.add_time DESC LIMIT ' . $start . ', ' . $num);
	}

	$res = $GLOBALS['db']->query($sql);
	$noTime = gmtime();
	$os = L('os');
	$ps = L('ps');
	$ss = L('ss');
	$sign_time = C('shop.sign');

	foreach ($res as $key => $row) {
		$date = array('order_status', 'shipping_status', 'pay_status', 'shipping_time', 'auto_delivery_time');
		$orderInfo = get_table_date('order_info', 'order_id = \'' . $row['order_id'] . ('\' and user_id = \'' . $user_id . '\''), $date);

		if ($GLOBALS['_CFG']['open_delivery_time'] == 1) {
			if ($orderInfo['order_status'] == 5 && $orderInfo['shipping_status'] == 1 && $orderInfo['pay_status'] == 2) {
				$delivery_time = $orderInfo['shipping_time'] + 24 * 3600 * $orderInfo['auto_delivery_time'];

				if ($delivery_time < $noTime) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET order_status = \'' . OS_SPLITED . '\', shipping_status = \'' . SS_RECEIVED . '\', pay_status = \'' . PS_PAYED . '\' WHERE order_id = \'' . $row['order_id'] . '\'';
					$GLOBALS['db']->query($sql);
					$note = L('self_motion_goods');
					order_action($orderInfo['order_sn'], OS_SPLITED, SS_RECEIVED, PS_PAYED, $note, L('buyer'), 0, gmtime());
				}
			}
		}

		if ($row['order_status'] == OS_UNCONFIRMED) {
			$row['handler'] = '<a class="btn-default-new br-5 cancel-order" data-item="' . $row['order_id'] . '" href="javascript:;" >' . L('cancel') . '</a>';
			$row['online_pay'] = url('onlinepay/index/index', array('order_sn' => $row[order_sn]));
		}
		else if ($row['order_status'] == OS_SPLITED) {
			if ($row['shipping_status'] == SS_SHIPPED) {
				@$row['handler'] = '<a class="btn-default-new br-5 received-order"  data-item-received="' . $row['order_id'] . '">' . L('received') . '</a>';
			}
			else if ($row['shipping_status'] == SS_RECEIVED) {
				@$row['handler'] = '<a class="btn-default-new br-5">' . L('ss_received') . '</a>';
			}
			else if ($row['pay_status'] == PS_UNPAYED) {
				@$row['handler'] = '<a class="btn-default-new br-5" href="' . url('user/order/detail', array('order_id' => $row['order_id'])) . '" >' . L('pay_money') . '</a>';
			}
			else {
				@$row['handler'] = '<a  class="btn-default-new br-5" href="' . url('user/order/detail', array('order_id' => $row['order_id'])) . '">' . L('view_order') . '</a>';
			}
		}
		else {
			$row['handler'] = '<a class="btn-default-new br-5">' . $os[$row['order_status']] . '</a>';
		}

		if ($row['order_status'] == OS_CONFIRMED && $row['shipping_status'] == SS_RECEIVED && $row['pay_status'] == PS_UNPAYED) {
			$row['handler_return'] = url('user/order/goodsorder', array('order_id' => $row['order_id']));
		}

		if ($row['order_status'] == OS_SPLITED && $row['shipping_status'] == SS_RECEIVED && $row['pay_status'] == PS_UNPAYED) {
			$row['handler_return'] = url('user/order/goodsorder', array('order_id' => $row['order_id']));
		}

		if (0 < $sign_time) {
			$day = ($noTime - $row['pay_time']) / 3600 / 24;
			if ($row['order_status'] != OS_CANCELED && $row['pay_status'] == PS_PAYED) {
				if ($day < $sign_time) {
					$row['handler_return'] = url('user/refound/index', array('order_id' => $row['order_id']));
				}
				else {
					@$row['handler_return'] = '';
				}
			}
		}

		if ($row[order_status] == 2 || $row[order_status] == 5 && $row[shipping_status] == 2 && $row[pay_status] == 2) {
			$row['order_del'] = 1;
		}

		$row['user_order'] = $row['order_status'];
		$row['user_shipping'] = $row['shipping_status'];
		$row['user_pay'] = $row['pay_status'];
		if ($row['user_order'] == OS_SPLITED && $row['user_shipping'] == SS_RECEIVED && $row['user_pay'] == PS_PAYED) {
			$row['delete_yes'] = 1;
		}
		else {
			if (($row['user_order'] == OS_CONFIRMED || $row['user_order'] == OS_UNCONFIRMED || $row['user_order'] == OS_CANCELED) && $row['user_shipping'] == SS_UNSHIPPED && $row['user_pay'] == PS_UNPAYED) {
				$row['delete_yes'] = 1;
			}
			else {
				if ($row['user_order'] == OS_INVALID && $row['user_pay'] == PS_PAYED_PART && $row['user_shipping'] == SS_UNSHIPPED) {
					$row['delete_yes'] = 1;
				}
				else {
					$row['delete_yes'] = 0;
				}
			}
		}

		if ($row['sign1'] == 0) {
			$row['sign'] = 0;
		}
		else {
			if (0 < $row['sign1'] && $row['sign2'] == 0) {
				$row['sign'] = 1;
			}
			else {
				if (0 < $row['sign1'] && 0 < $row['sign2']) {
					$row['sign'] = 2;
				}
			}
		}

		$delay = 0;
		if ($row['order_status'] == OS_SPLITED && $row['pay_status'] == PS_PAYED && $row['shipping_status'] == SS_SHIPPED) {
			$order_delay_day = C('shop.order_delay_day') * 86400;
			$auto_delivery_time = $row['auto_delivery_time'] * 86400;
			$shipping_time = $row['shipping_time'];

			if ($auto_delivery_time + $shipping_time - $noTime < $order_delay_day) {
				$map['review_status'] = array('neq', 1);
				$map['order_id'] = $row['order_id'];
				$num = dao('order_delayed')->where($map)->count('delayed_id');
				if (C('shop.open_order_delay') == 1 && $num < C('shop.order_delay_num')) {
					$delay = 1;
				}
			}
		}

		$row['shipping_status'] = $row['shipping_status'] == SS_SHIPPED_ING ? SS_PREPARING : $row['shipping_status'];
		$row['order_status'] = $os[$row[order_status]] . ',' . $ps[$row[pay_status]] . ',' . $ss[$row['shipping_status']];
		$br = '';
		$order_over = 0;
		if ($row['user_order'] == OS_SPLITED && $row['user_shipping'] == SS_RECEIVED && $row['user_pay'] == PS_PAYED) {
			$order_over = 1;
			$row['order_status'] = L('ss_received');
			$sign_url = '';

			if (0 < $row['sign']) {
				$sign = '&sign=' . $row['sign'];
				$sign_url = url('user/index/comment_list');
			}
			else {
				$sign = '';
				$sign_url = url('user/index/comment_list');
				$row['handler'] = '<a href="' . $sign_url . '" class="btn-submit1  n-return-btn br-5">晒单评价</a>';
			}
		}
		else {
			if ($row['user_order'] == OS_CANCELED && $row['user_shipping'] == SS_UNSHIPPED && $row['user_pay'] == PS_UNPAYED) {
				$order_over = 1;
				$row['handler'] = '';
			}
			else {
				if ($row['user_order'] == OS_SPLITED && $row['user_shipping'] == SS_SHIPPED) {
					$row['handler'] = $row['handler'];
					$br = '<br/>';
				}
				else {
					if (!($row['user_order'] == OS_UNCONFIRMED && $row['user_shipping'] == SS_UNSHIPPED && $row['user_pay'] == PS_UNPAYED)) {
						$row['handler'] = '';
					}
					else {
						$br = '<br/>';
					}
				}
			}
		}

		$sql = 'SELECT store_id  FROM ' . $GLOBALS['ecs']->table('store_order') . ' WHERE order_id = \'' . $row['order_id'] . '\'';
		$store_id = $GLOBALS['db']->getOne($sql);
		if (0 < $store_id && $row['shipping_status'] == SS_SHIPPED && $row['pay_status'] == PS_PAYED) {
			@$row['handler'] = '<a class="btn-default-new br-5 min-btn" href="' . url('user/order/affirmreceived', array('order_id' => $row['order_id'])) . '" onclick="if (!confirm(\'' . L('confirm_received') . '\')) return false;">' . L('received') . '</a>';
		}

		$ru_id = $row['ru_id'];
		$row['order_goods'] = get_order_goods_toInfo($row['order_id']);
		$order_id = $row['order_id'];
		$date = array('order_id');
		$order_child = count(get_table_date('order_info', 'main_order_id=\'' . $order_id . '\'', $date, 1));
		$row[$key]['order_child'] = $order_child;
		$sql = 'select order_id from ' . $GLOBALS['ecs']->table('order_info') . ' where main_order_id = \'' . $row['main_order_id'] . '\' and main_order_id > 0';
		$order_count = count($GLOBALS['db']->getAll($sql));
		$sql = 'select kf_type, kf_ww, kf_qq  from ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' where ru_id=\'' . $ru_id . '\'');
		$basic_info = $GLOBALS['db']->getRow($sql);
		$sql = 'select invoice_no, shipping_name, update_time from ' . $GLOBALS['ecs']->table('delivery_order') . ' where order_id = \'' . $row['order_id'] . '\'';
		$delivery = $GLOBALS['db']->getRow($sql);
		$province = get_order_region_name($row['province']);
		$city = get_order_region_name($row['city']);
		$district = get_order_region_name($row['district']);
		$district_name = !empty($district['region_name']) ? $district['region_name'] : '';
		$address_detail = $province['region_name'] . '&nbsp;' . $city['region_name'] . '市' . '&nbsp;' . $district_name;
		$delivery['delivery_time'] = local_date($GLOBALS['_CFG']['time_format'], $delivery['update_time']);
		$arr[] = array('order_id' => $row['order_id'], 'order_sn' => $row['order_sn'], 'order_time' => local_date($GLOBALS['_CFG']['time_format'], $row['add_time']), 'order_status' => $row['order_status'], 'order_del' => $row['order_del'], 'online_pay' => $row['online_pay'], 'status' => $row['status'], 'status_number' => $status_number, 'consignee' => $row['consignee'], 'main_order_id' => $row['main_order_id'], 'user_name' => get_shop_name($ru_id, 1), 'order_goods' => $row['order_goods'], 'order_goods_num' => count($row['order_goods']), 'order_child' => $order_child, 'no_picture' => $GLOBALS['_CFG']['no_picture'], 'order_child' => $order_child, 'delete_yes' => $row['delete_yes'], 'invoice_no' => $row['invoice_no'], 'shipping_name' => $row['shipping_name'], 'email' => $row['email'], 'address_detail' => $row['address_detail'], 'address' => $row['address'], 'address_detail' => $address_detail, 'tel' => $row['tel'], 'delivery_time' => $delivery['delivery_time'], 'order_count' => $order_count, 'kf_type' => $basic_info['kf_type'], 'kf_ww' => $basic_info['kf_ww'], 'kf_qq' => $basic_info['kf_qq'], 'total_fee' => price_format($row['total_fee'], false), 'handler_return' => $row['handler_return'], 'pay_status' => $row['pay_status'], 'handler' => $row['handler'], 'team_id' => $row['team_id'], 'extension_code' => $row['extension_code'], 'order_url' => url('user/order/detail', array('order_id' => $row['order_id'])), 'delay' => $delay);
	}

	$order_list = array('list' => $arr, 'totalpage' => ceil($total / $num));
	return $order_list;
}

function cancel_order($order_id, $user_id = 0)
{
	$sql = 'SELECT user_id, order_id, order_sn , surplus , integral , bonus_id, order_status, shipping_status, pay_status FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\'');
	$order = $GLOBALS['db']->getRow($sql);

	if (empty($order)) {
		$GLOBALS['err']->add(L('order_exist'));
		return false;
	}

	if (0 < $user_id && $order['user_id'] != $user_id) {
		$GLOBALS['err']->add(L('no_priv'));
		return false;
	}

	if ($order['order_status'] != OS_UNCONFIRMED && $order['order_status'] != OS_CONFIRMED) {
		$GLOBALS['err']->add(L('current_os_not_unconfirmed'));
		return false;
	}

	if ($order['order_status'] == OS_CONFIRMED) {
		$GLOBALS['err']->add(L('current_os_already_confirmed'));
		return false;
	}

	if ($order['shipping_status'] != SS_UNSHIPPED) {
		$GLOBALS['err']->add(L('current_ss_not_cancel'));
		return false;
	}

	if ($order['pay_status'] != PS_UNPAYED) {
		$GLOBALS['err']->add(L('current_ps_not_cancel'));
		return false;
	}

	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET order_status = \'' . OS_CANCELED . ('\' WHERE order_id = \'' . $order_id . '\'');

	if ($GLOBALS['db']->query($sql)) {
		order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED, L('buyer_cancel'), L('buyer'));
		if (0 < $order['user_id'] && 0 < $order['surplus']) {
			$change_desc = sprintf(L('return_surplus_on_cancel'), $order['order_sn']);
			log_account_change($order['user_id'], $order['surplus'], 0, 0, 0, $change_desc);
		}

		if (0 < $order['user_id'] && 0 < $order['integral']) {
			$change_desc = sprintf(L('return_integral_on_cancel'), $order['order_sn']);
			log_account_change($order['user_id'], 0, 0, 0, $order['integral'], $change_desc);
		}

		if (0 < $order['user_id'] && 0 < $order['bonus_id']) {
			change_user_bonus($order['bonus_id'], $order['order_id'], false);
		}

		if ($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == SDT_PLACE) {
			change_order_goods_storage($order['order_id'], false, 1, 3);
		}

		$arr = array('bonus_id' => 0, 'bonus' => 0, 'integral' => 0, 'integral_money' => 0, 'surplus' => 0);
		update_order($order['order_id'], $arr);
		return true;
	}
	else {
		exit($GLOBALS['db']->errorMsg());
	}
}

function affirm_received($order_id, $user_id = 0)
{
	$sql = 'SELECT order_id, user_id, order_sn , order_status, shipping_status, pay_status, ' . 'order_amount, goods_amount, tax, shipping_fee, insure_fee, pay_fee, pack_fee, card_fee, ' . 'bonus, integral_money, coupons, discount, money_paid, surplus, confirm_take_time ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\'');
	$order = $GLOBALS['db']->getRow($sql);
	if (0 < $user_id && $order['user_id'] != $user_id) {
		$GLOBALS['err']->add(L('no_priv'));
		return false;
	}
	else if ($order['shipping_status'] == SS_RECEIVED) {
		$GLOBALS['err']->add(L('order_already_received'));
		return false;
	}
	else if ($order['shipping_status'] != SS_SHIPPED) {
		$GLOBALS['err']->add(L('order_invalid'));
		return false;
	}
	else {
		$confirm_take_time = gmtime();
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET shipping_status = \'' . SS_RECEIVED . ('\', confirm_take_time = \'' . $confirm_take_time . '\' WHERE order_id = \'' . $order_id . '\'');

		if ($GLOBALS['db']->query($sql)) {
			order_action($order['order_sn'], $order['order_status'], SS_RECEIVED, $order['pay_status'], '', L('buyer'), 0, $confirm_take_time);
			$seller_id = $GLOBALS['db']->getOne('SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\''), true);
			$value_card = $GLOBALS['db']->getOne('SELECT use_val FROM ' . $GLOBALS['ecs']->table('value_card_record') . (' WHERE order_id = \'' . $order_id . '\''), true);
			$return_amount = get_order_return_amount($order_id);
			$other = array('user_id' => $order['user_id'], 'seller_id' => $seller_id, 'order_id' => $order['order_id'], 'order_sn' => $order['order_sn'], 'order_status' => $order['order_status'], 'shipping_status' => SS_RECEIVED, 'pay_status' => $order['pay_status'], 'order_amount' => $order['order_amount'], 'return_amount' => $return_amount, 'goods_amount' => $order['goods_amount'], 'tax' => $order['tax'], 'shipping_fee' => $order['shipping_fee'], 'insure_fee' => $order['insure_fee'], 'pay_fee' => $order['pay_fee'], 'pack_fee' => $order['pack_fee'], 'card_fee' => $order['card_fee'], 'bonus' => $order['bonus'], 'integral_money' => $order['integral_money'], 'coupons' => $order['coupons'], 'discount' => $order['discount'], 'value_card' => $value_card, 'money_paid' => $order['money_paid'], 'surplus' => $order['surplus'], 'confirm_take_time' => $confirm_take_time);

			if ($seller_id) {
				get_order_bill_log($other);
			}

			return true;
		}
		else {
			exit($GLOBALS['db']->errorMsg());
		}
	}
}

function save_consignee($consignee, $default = false)
{
	if (0 < $consignee['address_id']) {
		$res = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('user_address'), $consignee, 'UPDATE', 'address_id = ' . $consignee['address_id'] . ' AND `user_id`= \'' . $_SESSION['user_id'] . '\'');
	}
	else {
		unset($consignee['address_id']);
		$address_id = $GLOBALS['db']->table('user_address')->data($consignee)->add();
		$count = dao('user_address')->where(array('user_id' => $consignee[user_id]))->count();

		if ($count == 1) {
			dao('users')->data(array('address_id' => $address_id))->where(array('user_id' => $consignee[user_id]))->save();
		}

		$consignee['address_id'] = $address_id;
	}

	if ($default) {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . (' SET address_id = \'' . $consignee['address_id'] . '\' WHERE user_id = \'' . $_SESSION['user_id'] . '\'');
		$res = $GLOBALS['db']->query($sql);
	}

	return $res !== false;
}

function drop_consignee($id)
{
	$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('user_address') . (' WHERE address_id = \'' . $id . '\'');
	$uid = $GLOBALS['db']->getOne($sql);

	if ($uid != $_SESSION['user_id']) {
		return false;
	}
	else {
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('user_address') . (' WHERE address_id = \'' . $id . '\'');
		$res = $GLOBALS['db']->query($sql);
		return $res;
	}
}

function update_address($address)
{
	$address_id = intval($address['address_id']);
	unset($address['address_id']);

	if (0 < $address_id) {
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('user_address'), $address, 'UPDATE', 'address_id = ' . $address_id . ' AND user_id = ' . $address['user_id']);
	}
	else {
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('user_address'), $address, 'INSERT');
		$address_id = $GLOBALS['db']->insert_id();
	}

	if (isset($address['defalut']) && 0 < $address['default'] && isset($address['user_id'])) {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET address_id = \'' . $address_id . '\' ' . ' WHERE user_id = \'' . $address['user_id'] . '\'';
		$GLOBALS['db']->query($sql);
	}

	return true;
}

function get_order_detail($order_id, $user_id = 0)
{
	include_once BASE_PATH . 'Helpers/order_helper.php';
	$order_id = intval($order_id);

	if ($order_id <= 0) {
		$GLOBALS['err']->add(L('invalid_order_id'));
		return false;
	}

	$order = order_info($order_id);
	if (0 < $user_id && $user_id != $order['user_id']) {
		$GLOBALS['err']->add(L('no_priv'));
		return false;
	}

	if (!empty($order['invoice_no'])) {
		$shipping_code = $GLOBALS['db']->GetOne('SELECT shipping_code FROM ' . $GLOBALS['ecs']->table('shipping') . (' WHERE shipping_id = \'' . $order['shipping_id'] . '\''));
		$plugin = ADDONS_PATH . 'shipping/' . $shipping_code . '.php';

		if (file_exists($plugin)) {
			include_once $plugin;
			$shipping = new $shipping_code();
			$order['invoice_no'] = $shipping->query($order['invoice_no']);
			$order['tracking'] = $shipping->api($order['invoice_no']);
		}
	}

	if ($order['order_status'] == OS_UNCONFIRMED) {
		$order['allow_update_address'] = 1;
	}
	else {
		$order['allow_update_address'] = 0;
	}

	$order['exist_real_goods'] = exist_real_goods($order_id);
	if ($order['order_status'] != OS_CANCELED && $order['pay_status'] == PS_UNPAYED || $order['pay_status'] == PS_PAYED_PART && ($order['order_status'] == OS_UNCONFIRMED || $order['order_status'] == OS_CONFIRMED)) {
		$order['pay_online'] = url('onlinepay/index/index', array('order_sn' => $order[order_sn]));
	}
	else {
		$order['pay_online'] = '';
	}

	$order['shipping_id'] == -1 && $order['shipping_name'] = $GLOBALS['_LANG']['shipping_not_need'];
	$order['how_oos_name'] = $order['how_oos'];
	$order['how_surplus_name'] = $order['how_surplus'];

	if ($order['pay_status'] != PS_UNPAYED) {
		$virtual_goods = get_virtual_goods($order_id, true);
		$virtual_card = array();

		foreach ($virtual_goods as $code => $goods_list) {
			if ($code == 'virtual_card') {
				foreach ($goods_list as $goods) {
					if ($info = virtual_card_result($order['order_sn'], $goods)) {
						$virtual_card[] = array('goods_id' => $goods['goods_id'], 'goods_name' => $goods['goods_name'], 'info' => $info);
					}
				}
			}

			if ($code == 'package_buy') {
				foreach ($goods_list as $goods) {
					$sql = 'SELECT g.goods_id FROM ' . $GLOBALS['ecs']->table('package_goods') . ' AS pg, ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'WHERE pg.goods_id = g.goods_id AND pg.package_id = \'' . $goods['goods_id'] . '\' AND extension_code = \'virtual_card\'';
					$vcard_arr = $GLOBALS['db']->getAll($sql);

					foreach ($vcard_arr as $val) {
						if ($info = virtual_card_result($order['order_sn'], $val)) {
							$virtual_card[] = array('goods_id' => $goods['goods_id'], 'goods_name' => $goods['goods_name'], 'info' => $info);
						}
					}
				}
			}
		}

		$var_card = deleteRepeat($virtual_card);
		$GLOBALS['smarty']->assign('virtual_card', $var_card);
	}

	if (0 < $order['confirm_time'] && ($order['order_status'] == OS_CONFIRMED || $order['order_status'] == OS_SPLITED || $order['order_status'] == OS_SPLITING_PART)) {
		$order['confirm_time'] = sprintf($GLOBALS['_LANG']['confirm_time'], local_date($GLOBALS['_CFG']['time_format'], $order['confirm_time']));
	}
	else {
		$order['confirm_time'] = '';
	}

	if (0 < $order['pay_time'] && $order['pay_status'] != PS_UNPAYED) {
		$order['pay_time'] = sprintf($GLOBALS['_LANG']['pay_time'], local_date($GLOBALS['_CFG']['time_format'], $order['pay_time']));
	}
	else {
		$order['pay_time'] = '';
	}

	if (0 < $order['shipping_time'] && in_array($order['shipping_status'], array(SS_SHIPPED, SS_RECEIVED))) {
		$order['shipping_time'] = sprintf($GLOBALS['_LANG']['shipping_time'], local_date($GLOBALS['_CFG']['time_format'], $order['shipping_time']));
	}
	else {
		$order['shipping_time'] = '';
	}

	$order['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $order['add_time']);
	$sql = 'SELECT cu.uc_id, c.cou_money, cu.cou_id, c.cou_type FROM ' . $GLOBALS['ecs']->table('coupons_user') . ' as cu' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('coupons') . ' as c' . ' ON cu.cou_id = c.cou_id' . ' WHERE cu.order_id = ' . $order['order_id'] . ' AND cu.user_id = \'' . $_SESSION['user_id'] . '\' ';
	$coupons = $GLOBALS['db']->query($sql);

	foreach ($coupons as $key => $val) {
		$coupons[$key]['cou_money'] = price_format($val['cou_money'], 1);
	}

	$order['coupons'] = $coupons;
	return $order;
}

function get_user_merge($user_id)
{
	include_once BASE_PATH . 'Helpers/order_helper.php';
	$sql = 'SELECT order_sn FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE user_id  = \'' . $user_id . '\' ') . order_query_sql('unprocessed') . 'AND extension_code = \'\' ' . ' ORDER BY add_time DESC';
	$list = $GLOBALS['db']->GetCol($sql);
	$merge = array();

	foreach ($list as $val) {
		$merge[$val] = $val;
	}

	return $merge;
}

function merge_user_order($from_order, $to_order, $user_id = 0)
{
	if (0 < $user_id) {
		if (0 < strlen($to_order)) {
			$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_sn = \'' . $to_order . '\'');
			$order_user = $GLOBALS['db']->getOne($sql);

			if ($order_user != $user_id) {
				$GLOBALS['err']->add($GLOBALS['_LANG']['no_priv']);
			}
		}
		else {
			$GLOBALS['err']->add($GLOBALS['_LANG']['order_sn_empty']);
			return false;
		}
	}

	$result = merge_order($from_order, $to_order);

	if ($result === true) {
		return true;
	}
	else {
		$GLOBALS['err']->add($result);
		return false;
	}
}

function return_to_cart($order_id)
{
	$basic_number = array();
	$sql = 'SELECT goods_id, product_id,goods_number, goods_attr, parent_id, goods_attr_id' . ' FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' AND is_gift = 0 AND extension_code <> \'package_buy\'') . ' ORDER BY parent_id ASC';
	$res = $GLOBALS['db']->query($sql);
	$time = gmtime();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$sql = 'SELECT goods_sn, goods_name, goods_number, market_price, ' . ('IF(is_promote = 1 AND \'' . $time . '\' BETWEEN promote_start_date AND promote_end_date, promote_price, shop_price) AS goods_price,') . 'is_real, extension_code, is_alone_sale, goods_type ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $row['goods_id'] . '\' ') . ' AND is_delete = 0 LIMIT 1';
		$goods = $GLOBALS['db']->getRow($sql);

		if (empty($goods)) {
			continue;
		}

		if ($row['product_id']) {
			$order_goods_product_id = $row['product_id'];
			$sql = 'SELECT product_number from ' . $GLOBALS['ecs']->table('products') . ('where product_id=\'' . $order_goods_product_id . '\'');
			$product_number = $GLOBALS['db']->getOne($sql);
		}

		if ($GLOBALS['_CFG']['use_storage'] == 1 && ($row['product_id'] ? $product_number < $row['goods_number'] : $goods['goods_number'] < $row['goods_number'])) {
			if ($goods['goods_number'] == 0 || $product_number === 0) {
				continue;
			}
			else if ($row['product_id']) {
				$row['goods_number'] = $product_number;
			}
			else {
				$row['goods_number'] = $goods['goods_number'];
			}
		}

		$sql = 'SELECT goods_number FROM' . $GLOBALS['ecs']->table('cart') . ' ' . 'WHERE session_id = \'' . SESS_ID . '\' ' . 'AND goods_id = \'' . $row['goods_id'] . '\' ' . 'AND rec_type = \'' . CART_GENERAL_GOODS . '\' AND stages_qishu=\'-1\' AND store_id = 0 LIMIT 1';
		$temp_number = $GLOBALS['db']->getOne($sql);
		$row['goods_number'] += $temp_number;
		$attr_array = empty($row['goods_attr_id']) ? array() : explode(',', $row['goods_attr_id']);
		$goods['goods_price'] = get_final_price($row['goods_id'], $row['goods_number'], true, $attr_array);
		$return_goods = array('goods_id' => $row['goods_id'], 'goods_sn' => addslashes($goods['goods_sn']), 'goods_name' => addslashes($goods['goods_name']), 'market_price' => $goods['market_price'], 'goods_price' => $goods['goods_price'], 'goods_number' => $row['goods_number'], 'goods_attr' => empty($row['goods_attr']) ? '' : addslashes($row['goods_attr']), 'goods_attr_id' => empty($row['goods_attr_id']) ? '' : addslashes($row['goods_attr_id']), 'is_real' => $goods['is_real'], 'extension_code' => addslashes($goods['extension_code']), 'parent_id' => '0', 'is_gift' => '0', 'rec_type' => CART_GENERAL_GOODS);

		if (0 < $row['parent_id']) {
			$sql = 'SELECT goods_id ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $row['parent_id'] . '\' ') . ' AND is_delete = 0 AND is_on_sale = 1 AND is_alone_sale = 1 LIMIT 1';
			$parent = $GLOBALS['db']->getRow($sql);

			if ($parent) {
				$sql = 'SELECT goods_price ' . 'FROM ' . $GLOBALS['ecs']->table('group_goods') . (' WHERE parent_id = \'' . $row['parent_id'] . '\' ') . (' AND goods_id = \'' . $row['goods_id'] . '\' LIMIT 1');
				$fitting_price = $GLOBALS['db']->getOne($sql);

				if ($fitting_price) {
					$return_goods['parent_id'] = $row['parent_id'];
					$return_goods['goods_price'] = $fitting_price;
					$return_goods['goods_number'] = $basic_number[$row['parent_id']];
				}
			}
		}
		else {
			$basic_number[$row['goods_id']] = $row['goods_number'];
		}

		$sql = 'SELECT goods_id ' . 'FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE session_id = \'' . SESS_ID . '\' ' . (' AND goods_id = \'' . $return_goods['goods_id'] . '\' ') . (' AND goods_attr = \'' . $return_goods['goods_attr'] . '\' ') . (' AND parent_id = \'' . $return_goods['parent_id'] . '\' ') . ' AND is_gift = 0 ' . ' AND rec_type = \'' . CART_GENERAL_GOODS . '\' AND stages_qishu=\'-1\' AND store_id = 0 ';
		$cart_goods = $GLOBALS['db']->getOne($sql);

		if (empty($cart_goods)) {
			$return_goods['session_id'] = SESS_ID;
			$return_goods['user_id'] = $_SESSION['user_id'];
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $return_goods, 'INSERT');
		}
		else {
			$condition = 0 < $return_goods['goods_price'] ? ',goods_price = \'' . $return_goods['goods_price'] . '\' ' : '';
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('cart') . ' SET ' . 'goods_number = \'' . $return_goods['goods_number'] . '\' ' . $condition . 'WHERE session_id = \'' . SESS_ID . '\' ' . 'AND goods_id = \'' . $return_goods['goods_id'] . '\' ' . 'AND rec_type = \'' . CART_GENERAL_GOODS . '\' LIMIT 1';
			$GLOBALS['db']->query($sql);
		}
	}

	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE session_id = \'' . SESS_ID . '\' AND is_gift = 1';
	$GLOBALS['db']->query($sql);
	return true;
}

function save_order_address($address, $user_id)
{
	$GLOBALS['err']->clean();
	empty($address['consignee']) && $GLOBALS['err']->add($GLOBALS['_LANG']['consigness_empty']);
	empty($address['address']) && $GLOBALS['err']->add($GLOBALS['_LANG']['address_empty']);
	$address['order_id'] == 0 && $GLOBALS['err']->add($GLOBALS['_LANG']['order_id_empty']);

	if (empty($address['email'])) {
		$GLOBALS['err']->add($GLOBALS['email_empty']);
	}
	else if (!is_email($address['email'])) {
		$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_invalid'], $address['email']));
	}

	if (0 < $GLOBALS['err']->error_no) {
		return false;
	}

	$sql = 'SELECT user_id, order_status FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $address['order_id'] . '\'';
	$row = $GLOBALS['db']->getRow($sql);

	if ($row) {
		if (0 < $user_id && $user_id != $row['user_id']) {
			$GLOBALS['err']->add($GLOBALS['_LANG']['no_priv']);
			return false;
		}

		if ($row['order_status'] != OS_UNCONFIRMED) {
			$GLOBALS['err']->add($GLOBALS['_LANG']['require_unconfirmed']);
			return false;
		}

		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $address, 'UPDATE', 'order_id = \'' . $address['order_id'] . '\'');
		return true;
	}
	else {
		$GLOBALS['err']->add($GLOBALS['_LANG']['order_exist']);
		return false;
	}
}

function get_user_bouns_list($user_id, $type = 0, $num = 10, $start = 0)
{
	$cur_date = gmtime();

	if ($type == 0) {
		$where .= ' AND u.used_time = \'\' ';
	}
	else {
		$where .= '';
	}

	$sql = 'SELECT u.bonus_sn, u.order_id, b.user_id, b.type_name, b.type_money, b.min_goods_amount, b.use_start_date, b.use_end_date, b.usebonus_type ' . ' FROM ' . $GLOBALS['ecs']->table('user_bonus') . ' AS u ,' . $GLOBALS['ecs']->table('bonus_type') . ' AS b ' . ' WHERE  u.bonus_type_id = b.type_id ' . (' ' . $where . ' ') . ' AND u.user_id = \'' . $user_id . '\'';

	if ($type == 0) {
		$sql .= ' AND b.use_end_date >' . $cur_date;
	}
	else if ($type == 1) {
		$sql .= ' AND u.order_id <> 0';
	}
	else if ($type == 2) {
		$sql .= ' AND b.use_end_date <' . $cur_date;
	}

	$res = $GLOBALS['db']->selectLimit($sql, $num, $start);
	$arr = array();

	foreach ($res as $row) {
		if (empty($row['order_id'])) {
			if ($cur_date < $row['use_start_date']) {
				$row['status'] = L('not_start');

				if ($row['use_start_date'] - $cur_date < 60 * 60 * 24 * 2) {
					$row['near_time'] = 1;
				}

				$row['bonus_status'] = 2;
			}
			else if ($row['use_end_date'] < $cur_date) {
				$row['status'] = L('overdue');
				$row['bonus_status'] = 3;
			}
			else {
				$row['status'] = L('not_use');
				$row['bonus_status'] = 0;
			}
		}
		else {
			$row['status'] = L('had_use');
			$row['order_url'] = url('user/account/detail', array('order_id' => $row['order_id']));
			$row['bonus_status'] = 1;
		}

		$row['use_startdate'] = local_date(C('shop.date_format'), $row['use_start_date']);
		$row['use_enddate'] = local_date(C('shop.date_format'), $row['use_end_date']);

		if ($row['usebonus_type'] == 0) {
			$row['shop_name'] = sprintf(L('use_limit'), get_shop_name($row['user_id'], 1));
		}
		else {
			$row['shop_name'] = '全场通用';
		}

		$arr[] = $row;
	}

	return $arr;
}

function get_user_conut_bonus($user_id)
{
	$sql = 'SELECT  count(*) as num ' . ' FROM ' . $GLOBALS['ecs']->table('user_bonus') . ' AS u ,' . $GLOBALS['ecs']->table('bonus_type') . ' AS b ' . ' WHERE  u.bonus_type_id = b.type_id AND u.user_id = \'' . $user_id . '\'';
	$res = $GLOBALS['db']->getRow($sql);
	return $res['num'];
}

function get_user_group_buy($user_id, $num = 10, $start = 0)
{
	return true;
}

function get_group_buy_detail($user_id, $group_buy_id)
{
	return true;
}

function deleteRepeat($array)
{
	$_card_sn_record = array();

	foreach ($array as $_k => $_v) {
		foreach ($_v['info'] as $__k => $__v) {
			if (in_array($__v['card_sn'], $_card_sn_record)) {
				unset($array[$_k]['info'][$__k]);
			}
			else {
				array_push($_card_sn_record, $__v['card_sn']);
			}
		}
	}

	return $array;
}

function get_order_where_count($user_id = 0, $type = 0, $where = '')
{
	$sql = 'SELECT COUNT(*) as num FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi' . (' WHERE oi.user_id = \'' . $user_id . '\' and oi.is_delete = \'0\' and oi.is_zc_order= \'0\' ') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi_2 where oi_2.main_order_id = oi.order_id) = 0 ' . $where;
	$res = $GLOBALS['db']->getRow($sql);
	return $res['num'];
}

function get_card_list($user_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_bank') . (' WHERE user_id=\'' . $user_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $key => $val) {
		$res[$key]['bank_card'] = substr($val['bank_card'], -4);
	}

	return $res;
}

function get_goods_cause($goods_cause, $chargeoff_status = -1, $is_settlement = 0)
{
	$arr = array();
	$lang = L('order_return_type');

	if ($goods_cause) {
		$goods_cause = explode(',', $goods_cause);

		foreach ($goods_cause as $key => $row) {
			if (0 < $chargeoff_status || $is_settlement == 1) {
				if (!in_array($row, array(1, 3))) {
					$arr[$key]['cause'] = $row;
					$arr[$key]['lang'] = $lang[$row];

					if ($key == 0) {
						$arr[$key]['is_checked'] = 1;
					}
					else {
						$arr[$key]['is_checked'] = 0;
					}
				}
			}
			else {
				$arr[$key]['cause'] = $row;
				$arr[$key]['lang'] = $lang[$row];

				if ($key == 0) {
					$arr[$key]['is_checked'] = 1;
				}
				else {
					$arr[$key]['is_checked'] = 0;
				}
			}
		}
	}

	return $arr;
}


?>
