<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function favourable_list($user_rank)
{
	$used_list = cart_favourable();
	$favourable_list = array();
	$user_rank = ',' . $user_rank . ',';
	$now = gmtime();
	$sql = 'SELECT * ' . 'FROM {pre}favourable_activity' . ' WHERE CONCAT(\',\', user_rank, \',\') LIKE \'%' . $user_rank . '%\'' . ' AND start_time <= \'' . $now . '\' AND end_time >= \'' . $now . '\'' . ' AND review_status = 3 ' . ' ORDER BY sort_order';
	$res = $GLOBALS['db']->query($sql);
	$favourable_list = array();

	if ($res) {
		foreach ($res as $favourable) {
			$favourable['start_time'] = local_date(C('shop.time_format'), $favourable['start_time']);
			$favourable['end_time'] = local_date(C('shop.time_format'), $favourable['end_time']);
			$favourable['formated_min_amount'] = price_format($favourable['min_amount'], false);
			$favourable['formated_max_amount'] = price_format($favourable['max_amount'], false);
			$favourable['gift'] = unserialize($favourable['gift']);

			foreach ($favourable['gift'] as $key => $value) {
				$favourable['gift'][$key]['formated_price'] = price_format($value['price'], false);
				$sql = 'SELECT COUNT(*) FROM {pre}goods WHERE is_on_sale = 1 AND goods_id = ' . $value['id'];
				$is_sale = $GLOBALS['db']->getOne($sql);

				if (!$is_sale) {
					unset($favourable['gift'][$key]);
				}
			}

			$favourable['act_range_desc'] = act_range_desc($favourable);
			$favourable['act_type_desc'] = sprintf($GLOBALS['_LANG']['fat_ext'][$favourable['act_type']], $favourable['act_type_ext']);
			$favourable['available'] = favourable_available($favourable);

			if ($favourable['available']) {
				$favourable['available'] = !favourable_used($favourable, $used_list);
			}

			$favourable_list[] = $favourable;
		}
	}

	return $favourable_list;
}

function favourable_available($favourable)
{
	$user_rank = $_SESSION['user_rank'];

	if (strpos(',' . $favourable['user_rank'] . ',', ',' . $user_rank . ',') === false) {
		return false;
	}

	$amount = cart_favourable_amount($favourable);
	return ($favourable['min_amount'] <= $amount) && (($amount <= $favourable['max_amount']) || ($favourable['max_amount'] == 0));
}

function cart_favourable_amount($favourable)
{
	if (!empty($_SESSION['user_id'])) {
		$c_sess = ' c.user_id = \'' . $_SESSION['user_id'] . '\' ';
	}
	else {
		$c_sess = ' c.session_id = \'' . real_cart_mac_ip() . '\' ';
	}

	$fav_where = '';

	if ($favourable['userFav_type'] == 0) {
		$fav_where = ' AND g.user_id = \'' . $favourable['user_id'] . '\' ';
	}

	$sql = 'SELECT SUM(c.goods_price * c.goods_number) ' . 'FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c, ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'WHERE c.goods_id = g.goods_id ' . 'AND ' . $c_sess . ' AND c.rec_type = \'' . CART_GENERAL_GOODS . '\' ' . 'AND c.is_gift = 0 ' . 'AND c.goods_id > 0 ' . $fav_where;

	if ($favourable['act_range'] == FAR_ALL) {
	}
	else if ($favourable['act_range'] == FAR_CATEGORY) {
		$id_list = array();
		$cat_list = explode(',', $favourable['act_range_ext']);

		foreach ($cat_list as $id) {
			$id_list = array_merge($id_list, array_keys(cat_list(intval($id), 0)));
		}

		$sql .= 'AND g.cat_id ' . db_create_in($id_list);
	}
	else if ($favourable['act_range'] == FAR_BRAND) {
		$id_list = explode(',', $favourable['act_range_ext']);
		$sql .= 'AND g.brand_id ' . db_create_in($id_list);
	}
	else {
		$id_list = explode(',', $favourable['act_range_ext']);
		$sql .= 'AND g.goods_id ' . db_create_in($id_list);
	}

	return $GLOBALS['db']->getOne($sql);
}

function act_range_desc($favourable)
{
	if ($favourable['act_range'] == FAR_BRAND) {
		$sql = 'SELECT brand_name FROM ' . $GLOBALS['ecs']->table('brand') . ' WHERE brand_id ' . db_create_in($favourable['act_range_ext']);
		return join(',', $GLOBALS['db']->getCol($sql));
	}
	else if ($favourable['act_range'] == FAR_CATEGORY) {
		$sql = 'SELECT cat_name FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE cat_id ' . db_create_in($favourable['act_range_ext']);
		return join(',', $GLOBALS['db']->getCol($sql));
	}
	else if ($favourable['act_range'] == FAR_GOODS) {
		$sql = 'SELECT goods_name FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id ' . db_create_in($favourable['act_range_ext']);
		return join(',', $GLOBALS['db']->getCol($sql));
	}
	else {
		return '';
	}
}

function cart_favourable()
{
	if (!empty($_SESSION['user_id'])) {
		$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
	}
	else {
		$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
	}

	$sql = 'SELECT is_gift, COUNT(*) AS num ' . 'FROM {pre}cart  WHERE ' . $sess_id . ' AND rec_type = \'' . CART_GENERAL_GOODS . '\'' . ' AND is_gift > 0' . ' GROUP BY is_gift';
	$res = $GLOBALS['db']->getAll($sql);
	$list = array();

	if ($res) {
		foreach ($res as $row) {
			$list[$row['is_gift']] = $row['num'];
		}
	}

	return $list;
}

function cmp_favourable($a, $b)
{
	if ($a['available'] == $b['available']) {
		if ($a['sort_order'] == $b['sort_order']) {
			return 0;
		}
		else {
			return $a['sort_order'] < $b['sort_order'] ? -1 : 1;
		}
	}
	else {
		return $a['available'] ? -1 : 1;
	}
}

function sess()
{
	if (!empty($_SESSION['user_id'])) {
		$info['sess_id'] = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
		$info['a_sess'] = ' a.user_id = \'' . $_SESSION['user_id'] . '\' ';
		$info['b_sess'] = ' b.user_id = \'' . $_SESSION['user_id'] . '\' ';
		$info['c_sess'] = ' c.user_id = \'' . $_SESSION['user_id'] . '\' ';
		$info['sess_cart'] = '';
	}
	else {
		$info['sess_id'] = ' session_id = \'' . real_cart_mac_ip() . '\' ';
		$info['a_sess'] = ' a.session_id = \'' . real_cart_mac_ip() . '\' ';
		$info['b_sess'] = ' b.session_id = \'' . real_cart_mac_ip() . '\' ';
		$info['c_sess'] = ' c.session_id = \'' . real_cart_mac_ip() . '\' ';
		$info['sess_cart'] = real_cart_mac_ip();
	}

	return $info;
}

function flow_drop_cart_goods($id)
{
	$sess = sess();
	$sql = 'SELECT * FROM {pre}cart WHERE rec_id = \'' . $id . '\'';
	$row = $GLOBALS['db']->getRow($sql);
	flow_clear_cart_alone();

	if ($row) {
		if ($row['extension_code'] == 'package_buy') {
			$sql = 'DELETE FROM {pre}cart WHERE ' . $sess['sess_id'] . ' AND rec_id = \'' . $id . '\' LIMIT 1';
		}
		else {
			if (($row['parent_id'] == 0) && ($row['is_gift'] == 0)) {
				$sql = "SELECT c.rec_id\r\n\t\t\t\tFROM {pre}cart AS c, {pre}group_goods AS gg, {pre}goods AS g\r\n\t\t\t\tWHERE gg.parent_id = '" . $row['goods_id'] . "'\r\n\t\t\t\tAND c.goods_id = gg.goods_id\r\n\t\t\t\tAND c.parent_id = '" . $row['goods_id'] . "'\r\n\t\t\t\tAND c.extension_code <> 'package_buy'\r\n\t\t\t\tAND gg.goods_id = g.goods_id\r\n\t\t\t\tAND g.is_alone_sale = 0";
				$res = $GLOBALS['db']->getAll($sql);
				$_del_str = $id . ',';

				foreach ($res as $id_alone_sale_goods) {
					$_del_str .= $id_alone_sale_goods['rec_id'] . ',';
				}

				$_del_str = trim($_del_str, ',');
				$sql = 'DELETE FROM {pre}cart WHERE ' . $sess['sess_id'] . ' AND (rec_id IN (' . $_del_str . ') OR parent_id = \'' . $row['goods_id'] . '\' OR is_gift <> 0)';
			}
			else {
				$sql = 'DELETE FROM {pre}cart WHERE ' . $sess['sess_id'] . ' AND rec_id = \'' . $id . '\' LIMIT 1';
			}
		}

		$result = $GLOBALS['db']->query($sql);
	}

	return $result ? $result : false;
}

function flow_clear_cart_alone()
{
	$sess = sess();
	$sql = "SELECT c.rec_id, gg.parent_id\r\n\t\tFROM {pre}cart AS c\r\n\t\tLEFT JOIN {pre}group_goods AS gg ON c.goods_id = gg.goods_id\r\n\t\tLEFT JOIN {pre}goods AS g ON c.goods_id = g.goods_id\r\n\t\tWHERE " . $sess['c_sess'] . "\r\n\t\tAND c.extension_code <> 'package_buy'\r\n\t\tAND gg.parent_id > 0\r\n\t\tAND g.is_alone_sale = 0";
	$res = $GLOBALS['db']->query($sql);
	$rec_id = array();

	foreach ($res as $row) {
		$rec_id[$row['rec_id']][] = $row['parent_id'];
	}

	if (empty($rec_id)) {
		return NULL;
	}

	$sql = "SELECT DISTINCT goods_id\r\n\t\tFROM {pre}cart WHERE " . $sess['sess_id'] . "\r\n\t\tAND extension_code <> 'package_buy'";
	$res = $GLOBALS['db']->query($sql);
	$cart_good = array();

	foreach ($res as $row) {
		$cart_good[] = $row['goods_id'];
	}

	if (empty($cart_good)) {
		return NULL;
	}

	$del_rec_id = '';

	foreach ($rec_id as $key => $value) {
		foreach ($value as $v) {
			if (in_array($v, $cart_good)) {
				continue 2;
			}
		}

		$del_rec_id = $key . ',';
	}

	$del_rec_id = trim($del_rec_id, ',');

	if ($del_rec_id == '') {
		return NULL;
	}

	$sql = 'DELETE FROM {pre}cart WHERE ' . $sess['sess_id'] . "\r\n    AND rec_id IN (" . $del_rec_id . ')';
	$GLOBALS['db']->query($sql);
}

function favourable_used($favourable, $cart_favourable)
{
	if ($favourable['act_type'] == FAT_GOODS) {
		return isset($cart_favourable[$favourable['act_id']]) && ($favourable['act_type_ext'] <= $cart_favourable[$favourable['act_id']]) && (0 < $favourable['act_type_ext']);
	}
	else {
		return isset($cart_favourable[$favourable['act_id']]);
	}
}


?>
