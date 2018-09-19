<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function top_all($type = '')
{
	$sql = 'SELECT c.cat_id,c.cat_name,c.parent_id,c.is_show ' . 'FROM {pre}zc_category as c ' . 'WHERE c.parent_id = 0 AND c.is_show = 1 ORDER BY c.sort_order ASC, c.cat_id ASC';
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $row) {
		if ($row['is_show']) {
			$cat_arr[$row['cat_id']]['id'] = $row['cat_id'];
			$cat_arr[$row['cat_id']]['type'] = $type;
			$cat_arr[$row['cat_id']]['name'] = $row['cat_name'];

			if (isset($row['cat_id']) == isset($row['parent_id'])) {
				$cat_arr[$row['cat_id']]['cat_id'] = get_child_trees($row['cat_id']);
			}
		}
	}

	return $cat_arr;
}

function get_child_trees($tree_id = 0)
{
	$three_arr = array();
	$sql = 'SELECT count(*) FROM ' . "{pre}zc_category\r\n WHERE parent_id = '" . $tree_id . '\' AND is_show = 1 ';
	if ($GLOBALS['db']->getOne($sql) || ($tree_id == 0)) {
		$child_sql = 'SELECT c.cat_id, c.cat_name, c.parent_id, c.is_show ' . 'FROM {pre}zc_category as c ' . ' WHERE c.parent_id = \'' . $tree_id . '\' AND c.is_show = 1 GROUP BY c.cat_id ORDER BY c.sort_order ASC, c.cat_id ASC';
		$res = $GLOBALS['db']->getAll($child_sql);

		foreach ($res as $row) {
			if ($row['is_show']) {
				$three_arr[$row['cat_id']]['id'] = $row['cat_id'];
				$three_arr[$row['cat_id']]['name'] = $row['cat_name'];
			}

			if (isset($row['cat_id']) != NULL) {
				$three_arr[$row['cat_id']]['cat_id'] = get_child_trees($row['cat_id']);
			}
		}
	}

	return $three_arr;
}

function zc_goods_info($goods_id = 0)
{
	$sql = ' SELECT * FROM {pre}zc_project WHERE id = \'' . $goods_id . '\' ';
	$zhongchou = $GLOBALS['db']->getRow($sql);
	$zhongchou['title_img'] = get_zc_image_path($zhongchou['title_img']);
	$zhongchou['shenyu_time'] = ceil(($zhongchou['end_time'] - gmtime()) / 3600 / 24);
	$time = gmtime();
	if (($zhongchou['end_time'] < $time) && ($zhongchou['join_money'] <= $zhongchou['amount'])) {
		$zhongchou['zc_status'] = '项目失败';
		$zhongchou['result'] = 2;
		$zhongchou['shenyu_time'] = 0;
	}

	if (($zhongchou['end_time'] < $time) && ($zhongchou['amount'] <= $zhongchou['join_money'])) {
		$zhongchou['zc_status'] = '项目成功';
		$zhongchou['result'] = 2;
		$zhongchou['shenyu_time'] = 0;
	}

	$zhongchou['baifen_bi'] = round($zhongchou['join_money'] / $zhongchou['amount'], 2) * 100;
	return $zhongchou;
}

function zc_goods($goods_id = 0)
{
	$sql = ' SELECT `id`,`pid`,`limit`,`backer_num`,`price`,`shipping_fee`,`content`,`img`,`return_time`,`backer_list`,(`limit`-`backer_num`) as shenyu_ren FROM {pre}zc_goods WHERE pid = \'' . $goods_id . '\' ';
	$goods_arr = $GLOBALS['db']->getAll($sql);

	foreach ($goods_arr as $k => $goods) {
		$goods_arr[$k]['img'] = get_zc_image_path($goods['img']);

		if ($goods['limit'] < 0) {
			$goods_arr[$k]['wuxian'] = '无限额';
		}
	}

	return $goods_arr;
}

function zc_progress($goods_id = 0)
{
	$sql = ' SELECT `id`,`pid`,`progress`,`add_time`,img FROM {pre}zc_progress WHERE pid = \'' . $goods_id . '\' order by id DESC ';
	$goods_arr = $GLOBALS['db']->getAll($sql);

	foreach ($goods_arr as $k => $goods) {
		$goods_arr[$k]['add_time'] = date('Y年m月d日', $goods['add_time']);
		$goods['img'] = unserialize($goods['img']);

		if (!empty($goods['img'])) {
			foreach ($goods['img'] as $k2 => $v2) {
				$goods['img'][$k2] = get_zc_image_path($v2);
			}

			$goods_arr[$k]['img'] = $goods['img'];
		}
	}

	return $goods_arr;
}

function get_backer_list($goods_id = 0)
{
	$sql = ' SELECT oi.user_id,oi.add_time,zg.price,zg.content ' . ' FROM {pre}order_info as oi ' . ' LEFT JOIN {pre}zc_goods as zg on zg.id=oi.zc_goods_id ' . ' LEFT JOIN {pre}zc_project as zd on zd.id=zg.pid ' . ' WHERE oi.is_zc_order=1 AND oi.pay_status=2 AND zd.id = \'' . $goods_id . '\' ' . ' ORDER BY oi.order_id DESC ';
	$backer_list = $GLOBALS['db']->getAll($sql);

	foreach ($backer_list as $key => $val) {
		$backer_list[$key]['add_time'] = get_time_past($val['add_time'], gmtime());
		$user_nick = get_user_default($val['user_id']);
		$backer_list[$key]['user_name'] = encrypt_username($user_nick['nick_name']);
		$backer_list[$key]['user_picture'] = $user_nick['user_picture'];
	}

	return $backer_list;
}

function get_topic_list($goods_id = 0)
{
	$sql = ' SELECT * FROM {pre}zc_topic WHERE pid=\'' . $goods_id . '\' AND parent_topic_id=0 AND topic_status = 1 ' . ' ORDER BY topic_id DESC LIMIT 0,3';
	$topic_list = $GLOBALS['db']->getAll($sql);

	foreach ($topic_list as $key => $val) {
		$user_nick = get_user_default($val['user_id']);
		$topic_list[$key]['user_name'] = encrypt_username($user_nick['nick_name']);
		$topic_list[$key]['user_picture'] = $user_nick['user_picture'];
		$topic_list[$key]['time_past'] = get_time_past($val['add_time'], gmtime());
		$sql = ' select * from {pre}zc_topic where parent_topic_id=' . $val['topic_id'] . ' AND topic_status = 1 order by topic_id desc limit 5';
		$child_topic = $GLOBALS['db']->getAll($sql);

		if (0 < count($child_topic)) {
			foreach ($child_topic as $k => $v) {
				$user_nick = get_user_default($v['user_id']);
				$child_topic[$k]['user_name'] = encrypt_username($user_nick['nick_name']);
				$child_topic[$k]['user_picture'] = $user_nick['user_picture'];
				$child_topic[$k]['time_past'] = get_time_past($v['add_time'], gmtime());
			}
		}

		$topic_list[$key]['child_topic'] = $child_topic;
		$sql = ' select count(*) from ' . $GLOBALS['ecs']->table('zc_topic') . ' where parent_topic_id=' . $val['topic_id'] . ' AND topic_status = 1 order by topic_id desc ';
		$topic_list[$key]['child_topic_num'] = $GLOBALS['db']->getOne($sql);
	}

	return $topic_list;
}

function zc_comment_list($id, $size, $page)
{
	if (empty($id)) {
		return false;
	}

	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('zc_topic') . ' WHERE pid = \'' . $id . '\'  and topic_status = 1 and parent_topic_id = 0  ORDER BY topic_id DESC ';
	$comment = $GLOBALS['db']->getAll($sql);
	$total = (is_array($comment) ? count($comment) : 0);
	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
	$arr = array();

	if ($res) {
		foreach ($res as $key => $row) {
			$arr[$row['topic_id']]['topic_id'] = $row['topic_id'];
			$arr[$row['topic_id']]['user_id'] = $row['user_id'];
			$user_nick = get_user_default($row['user_id']);
			$arr[$row['topic_id']]['username'] = encrypt_username($user_nick['nick_name']);
			$arr[$row['topic_id']]['user_picture'] = $user_nick['user_picture'];
			$arr[$row['topic_id']]['topic_content'] = str_replace('\\r\\n', '<br />', htmlspecialchars($row['topic_content']));
			$arr[$row['topic_id']]['add_time'] = get_time_past($row['add_time'], gmtime());
			$arr[$row['topic_id']]['url'] = U('crowd_funding/index/topic', array('id' => $row['pid'], 'topic_id' => $row['topic_id'], 'action' => 'comment'));
			$sql = ' select * from {pre}zc_topic where parent_topic_id=' . $row['topic_id'] . ' AND topic_status = 1 order by topic_id desc';
			$child_topic = $GLOBALS['db']->getAll($sql);

			if (0 < count($child_topic)) {
				foreach ($child_topic as $k => $v) {
					$user_nick = get_user_default($v['user_id']);
					$child_topic[$k]['user_name'] = encrypt_username($user_nick['nick_name']);
					$child_topic[$k]['user_picture'] = $user_nick['user_picture'];
					$child_topic[$k]['time_past'] = get_time_past($v['add_time'], gmtime());
				}
			}

			$arr[$row['topic_id']]['child_topic'] = $child_topic;
		}
	}

	return array('list' => array_values($arr), 'totalpage' => ceil($total / $size));
}

function get_time_past($time = 0, $now = 0)
{
	$time_past = '';

	if ($time <= $now) {
		$diff = $now - $time;
		if ((0 < $diff) && ($diff <= 60)) {
			$time_past = '刚刚';
		}
		else {
			if ((60 < $diff) && ($diff <= 3600)) {
				$time_past = floor($diff / 60) . '分钟前';
			}
			else {
				if ((3600 < $diff) && ($diff <= 86400)) {
					$time_past = floor($diff / 3600) . '小时前';
				}
				else {
					if ((86400 < $diff) && ($diff <= 2592000)) {
						$time_past = floor($diff / 86400) . '天前';
					}
					else {
						if ((2592000 < $diff) && ($diff <= 31536000)) {
							$time_past = floor($diff / 2592000) . '月前';
						}
						else if (31536000 < $diff) {
							$time_past = floor($diff / 31536000) . '年前';
						}
					}
				}
			}
		}
	}
	else {
		$time_past = '时间不合法';
	}

	return $time_past;
}

function zc_check_consignee_info($consignee)
{
	$res = !empty($consignee['consignee']) && (!empty($consignee['tel']) || !empty($consignee['mobile']));

	if ($res) {
		if (empty($consignee['province'])) {
			$pro = get_regions(1, $consignee['country']);
			$res = empty($pro);
		}
		else if (empty($consignee['city'])) {
			$city = get_regions(2, $consignee['province']);
			$res = empty($city);
		}
	}

	return $res;
}

function zc_cart_goods($pid, $id, $number)
{
	$sql = 'SELECT zp.id, zp.title, zp.amount, zp.join_money, zp.title_img ,zp.start_time, zp.end_time, zp.join_num, g.price,g.limit,g.backer_num, g.content, g.shipping_fee FROM {pre}zc_project as zp left join  {pre}zc_goods as g on g.pid = zp.id  WHERE g.pid = \'' . $pid . '\' and g.id = \'' . $id . '\'';
	$zhongchou = $GLOBALS['db']->getRow($sql);
	$zhongchou['title_img'] = get_zc_image_path($zhongchou['title_img']);

	if (gmtime() < $zhongchou['start_time']) {
		$zhongchou['zc_status'] = 0;
	}
	else if ($zhongchou['end_time'] < gmtime()) {
		$zhongchou['zc_status'] = 2;
	}
	else {
		$zhongchou['zc_status'] = 1;
	}

	if (($zhongchou['join_money'] < $zhongchou['amount']) && ($zhongchou['zc_status'] == 2)) {
		$zhongchou['result'] = 1;
	}
	else {
		if (($zhongchou['amount'] < $zhongchou['join_money']) && ($zhongchou['zc_status'] == 2)) {
			$zhongchou['result'] = 2;
		}
		else {
			$zhongchou['result'] = 0;
		}
	}

	$zhongchou['formated_subtotal'] = price_format($zhongchou['price'] * $number, false);
	$zhongchou['number'] = $number;
	$zhongchou['baifen_bi'] = round($zhongchou['join_money'] / $zhongchou['amount'], 2) * 100;
	$zhongchou['shenyu_time'] = ceil(($zhongchou['end_time'] - gmtime()) / 3600 / 24);
	$zhongchou['zw_end_time'] = date('Y年m月d日', $zhongchou['end_time']);
	$zhongchou['star_time'] = date('Y/m/d', $zhongchou['start_time']);
	$zhongchou['end_time'] = date('Y/m/d/s/u', $zhongchou['end_time']);
	return $zhongchou;
}

function zc_get_order_shipping_fee($goods, $consignee, $shipping)
{
	$consignee = (isset($_SESSION['flow_consignee']) ? $_SESSION['flow_consignee'] : $consignee);
	$region = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);

	if ($shipping) {
		$sql = 'SELECT s.shipping_id, s.shipping_code, s.shipping_name, ' . 's.shipping_desc, s.insure, s.support_cod, a.configure ' . 'FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s, ' . $GLOBALS['ecs']->table('shipping_area') . ' AS a, ' . $GLOBALS['ecs']->table('area_region') . ' AS r ' . 'WHERE r.region_id ' . db_create_in($region) . ' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1 AND a.ru_id = \'' . $ru_id . '\' and s.shipping_id = \'' . $shipping . '\' ORDER BY s.shipping_order';
		$shipping_list = $GLOBALS['db']->getAll($sql);

		foreach ($shipping_list as $key => $val) {
			if ($GLOBALS['_CFG']['freight_model'] == 0) {
				$shipping_cfg = unserialize_config($val['configure']);
				$shipping_fee = (($shipping_count == 0) && ($cart_weight_price['free_shipping'] == 1) ? 0 : shipping_fee($val['shipping_code'], unserialize($val['configure']), $cart_weight_price['weight'], $goods['price'], $goods['number']));
				$shipping_list[$key]['free_money'] = price_format($shipping_cfg['free_money'], false);
			}
			else if ($GLOBALS['_CFG']['freight_model'] == 1) {
				$shipping_cfg = unserialize_config($val['configure']);
				$shipping_fee = goods_shipping_fee($val['shipping_code'], unserialize($val['configure']), 0, $goods['price'], $goods['number']);
			}

			return $shipping_fee;
		}
	}
}

function zc_get_ru_shippng_info($cart_goods, $consignee = '')
{
	if (!empty($_SESSION['user_id'])) {
		$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
	}
	else {
		$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
	}

	$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
	$order = flow_order_info();
	$seller_shipping = get_seller_shipping_type($ru_id);
	$shipping_id = $seller_shipping['shipping_id'];
	$consignee = (isset($_SESSION['flow_consignee']) ? $_SESSION['flow_consignee'] : $consignee);
	$region = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);
	$insure_disabled = true;
	$cod_disabled = true;
	$sql = 'SELECT s.shipping_id, s.shipping_code, s.shipping_name, ' . 's.shipping_desc, s.insure, s.support_cod, a.configure ' . 'FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s, ' . $GLOBALS['ecs']->table('shipping_area') . ' AS a, ' . $GLOBALS['ecs']->table('area_region') . ' AS r ' . 'WHERE r.region_id ' . db_create_in($region) . ' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1 AND a.ru_id = \'' . $ru_id . '\' ORDER BY s.shipping_order';
	$shipping_list = $GLOBALS['db']->getAll($sql);

	foreach ($shipping_list as $key => $val) {
		if ($GLOBALS['_CFG']['freight_model'] == 0) {
			$shipping_cfg = unserialize_config($val['configure']);
			$shipping_fee = (($shipping_count == 0) && ($cart_weight_price['free_shipping'] == 1) ? 0 : shipping_fee($val['shipping_code'], unserialize($val['configure']), $cart_weight_price['weight'], $cart_goods['price'], $cart_goods['number']));
			$shipping_list[$key]['free_money'] = price_format($shipping_cfg['free_money'], false);
		}
		else if ($GLOBALS['_CFG']['freight_model'] == 1) {
			$goods_region = array('country' => $region[0], 'province' => $region[1], 'city' => $region[2], 'district' => $region[3]);
			$shipping_cfg = unserialize_config($val['configure']);
			$shipping_fee = goods_shipping_fee($val['shipping_code'], unserialize($val['configure']), 0, $cart_goods['price'], $cart_goods['number']);
			$free_money = (isset($shipping_cfg['free_money']) ? $shipping_cfg['free_money'] : 0);
			$shipping_list[$key]['free_money'] = price_format(shipping_cfg, false);
		}

		$shipping_list[$key]['shipping_id'] = $val['shipping_id'];
		$shipping_list[$key]['shipping_name'] = $val['shipping_name'];
		$shipping_list[$key]['shipping_code'] = $val['shipping_code'];
		$shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);
		$shipping_list[$key]['shipping_fee'] = $shipping_fee;
		$shipping_list[$key]['insure_formated'] = strpos($val['insure'], '%') === false ? price_format($val['insure'], false) : $val['insure'];

		if ($val['shipping_id'] == $order['shipping_id']) {
			$insure_disabled = $val['insure'] == 0;
			$cod_disabled = $val['support_cod'] == 0;
		}

		$shipping_list[$key]['default'] = 0;

		if ($shipping_id == $val['shipping_id']) {
			$shipping_list[$key]['default'] = 1;
		}

		$shipping_list[$key]['insure_disabled'] = $insure_disabled;
		$shipping_list[$key]['cod_disabled'] = $cod_disabled;
	}

	$shipping_type = array();

	foreach ($shipping_list as $key => $val) {
		$shipping_type[$val['shipping_code']][] = $key;
	}

	foreach ($shipping_type as $key => $val) {
		if (1 < count($val)) {
			for ($i = 1; $i < count($val); $i++) {
				unset($shipping_list[$val[$i]]);
			}
		}
	}

	return $shipping_list;
}

function zc_order_fee($order, $goods, $consignee)
{
	$consignee = (isset($_SESSION['flow_consignee']) ? $_SESSION['flow_consignee'] : $consignee);
	$region = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);
	$total = array('real_goods_count' => 0, 'gift_amount' => 0, 'goods_price' => 0, 'market_price' => 0, 'discount' => 0, 'pack_fee' => 0, 'card_fee' => 0, 'shipping_fee' => 0, 'shipping_insure' => 0, 'integral_money' => 0, 'bonus' => 0, 'surplus' => 0, 'cod_fee' => 0, 'pay_fee' => 0, 'tax' => 0, 'presale_price' => 0);
	$weight = 0;
	$arr = array();
	$cat_goods = array($goods);

	foreach ($cat_goods as $val) {
		$total['goods_price'] += $val['price'] * $val['number'];
	}

	$total['saving'] = $total['market_price'] - $total['goods_price'];
	$total['save_rate'] = $total['market_price'] ? round(($total['saving'] * 100) / $total['market_price']) . '%' : 0;
	$total['goods_price_formated'] = price_format($total['goods_price'], false);
	$total['market_price_formated'] = price_format($total['market_price'], false);
	$total['saving_formated'] = price_format($total['saving'], false);
	if (!empty($order['need_inv']) && ($order['inv_type'] != '')) {
		$rate = 0;

		foreach ($GLOBALS['_CFG']['invoice_type']['type'] as $key => $type) {
			if ($type == $order['inv_type']) {
				$rate = floatval($GLOBALS['_CFG']['invoice_type']['rate'][$key]) / 100;
				break;
			}
		}

		if (0 < $rate) {
			$total['tax'] = $rate * $total['goods_price'];
		}
	}

	$total['tax_formated'] = price_format($total['tax'], false);

	if (!empty($order['pack_id'])) {
		$total['pack_fee'] = pack_fee($order['pack_id'], $total['goods_price']);
	}

	$total['pack_fee_formated'] = price_format($total['pack_fee'], false);
	$total['shipping_fee'] = $goods['shipping_fee'] ? $goods['shipping_fee'] : 0;
	$total['shipping_fee_formated'] = price_format($total['shipping_fee'], false);
	$total['shipping_insure_formated'] = price_format($total['shipping_insure'], false);
	$total['amount'] = ($total['goods_price'] - $total['discount']) + $total['tax'] + $total['pack_fee'] + $total['card_fee'] + $total['shipping_fee'] + $total['shipping_insure'] + $total['cod_fee'];
	$use_bonus = min($total['bonus'], $max_amount);

	if (isset($total['bonus_kill'])) {
		$use_bonus_kill = min($total['bonus_kill'], $max_amount);
		$total['amount'] -= $price = number_format($total['bonus_kill'], 2, '.', '');
	}

	$total['bonus'] = $use_bonus;
	$total['bonus_formated'] = price_format($total['bonus'], false);
	$total['amount'] -= $use_bonus;
	$max_amount -= $use_bonus;
	$order['surplus'] = 0 < $order['surplus'] ? $order['surplus'] : 0;

	if (0 < $total['amount']) {
		if (isset($order['surplus']) && ($total['amount'] < $order['surplus'])) {
			$order['surplus'] = $total['amount'];
			$total['amount'] = 0;
		}
		else {
			$total['amount'] -= floatval($order['surplus']);
		}
	}
	else {
		$order['surplus'] = 0;
		$total['amount'] = 0;
	}

	$total['surplus'] = $order['surplus'];
	$total['surplus_formated'] = price_format($order['surplus'], false);
	$order['integral'] = 0 < $order['integral'] ? $order['integral'] : 0;
	if ((0 < $total['amount']) && (0 < $max_amount) && (0 < $order['integral'])) {
		$integral_money = value_of_integral($order['integral']);
		$use_integral = min($total['amount'], $max_amount, $integral_money);
		$total['amount'] -= $use_integral;
		$total['integral_money'] = $use_integral;
		$order['integral'] = integral_of_value($use_integral);
	}
	else {
		$total['integral_money'] = 0;
		$order['integral'] = 0;
	}

	$total['integral'] = $order['integral'];
	$total['integral_formated'] = price_format($total['integral_money'], false);
	$_SESSION['flow_order'] = $order;
	$se_flow_type = (isset($_SESSION['flow_type']) ? $_SESSION['flow_type'] : '');
	if (!empty($order['pay_id']) && ((0 < $total['real_goods_count']) || ($se_flow_type != CART_EXCHANGE_GOODS))) {
		$total['pay_fee'] = pay_fee($order['pay_id'], $total['amount'], $shipping_cod_fee);
	}

	$total['pay_fee_formated'] = price_format($total['pay_fee'], false);
	$total['amount'] += $total['pay_fee'];
	$total['amount_formated'] = price_format($total['amount'], false);

	if ($order['extension_code'] == 'group_buy') {
		$total['will_get_integral'] = $group_buy['gift_integral'];
	}
	else if ($order['extension_code'] == 'exchange_goods') {
		$total['will_get_integral'] = 0;
	}
	else {
		$total['will_get_integral'] = get_give_integral($goods, $cart_value);
	}

	$total['will_get_bonus'] = $order['extension_code'] == 'exchange_goods' ? 0 : price_format(get_total_bonus(), false);
	$total['formated_goods_price'] = price_format($total['goods_price'], false);
	$total['formated_market_price'] = price_format($total['market_price'], false);
	$total['formated_saving'] = price_format($total['saving'], false);

	if ($order['extension_code'] == 'exchange_goods') {
		$sql = 'SELECT SUM(eg.exchange_integral * c.goods_number) ' . 'FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c,' . $GLOBALS['ecs']->table('exchange_goods') . 'AS eg ' . 'WHERE c.goods_id = eg.goods_id AND ' . $c_sess . '  AND c.rec_type = \'' . CART_EXCHANGE_GOODS . '\' ' . '  AND c.is_gift = 0 AND c.goods_id > 0 AND eg.review_status = 3 ' . 'GROUP BY eg.goods_id';
		$exchange_integral = $GLOBALS['db']->getOne($sql);
		$total['exchange_integral'] = $exchange_integral;
	}

	return $total;
}


?>
