<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function fromat_coupons($cou_data)
{
	$time = gmtime();

	foreach ($cou_data as $k => $v) {
		if (!isset($v['cou_surplus'])) {
			$cou_data[$k]['cou_surplus'] = 100;
		}

		if (!empty($v['cou_goods'])) {
			$cou_data[$k]['cou_goods_name'] = $GLOBALS['db']->getAll('SELECT goods_id,goods_name,goods_thumb FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id IN(' . $v['cou_goods'] . ')');
		}

		if (!empty($v['cou_ok_user'])) {
			$cou_data[$k]['cou_ok_user_name'] = $GLOBALS['db']->getOne('SELECT group_concat(rank_name)  FROM ' . $GLOBALS['ecs']->table('user_rank') . ' WHERE rank_id IN(' . $v['cou_ok_user'] . ')');
		}

		$cou_data[$k]['store_name'] = sprintf(L('use_limit'), get_shop_name($v['ru_id'], 1));
		$cou_data[$k]['cou_start_time_format'] = local_date('Y/m/d', $v['cou_start_time']);
		$cou_data[$k]['cou_end_time_format'] = local_date('Y/m/d', $v['cou_end_time']);

		if ($v['cou_end_time'] < $time) {
			$cou_data[$k]['is_overdue'] = 1;
		}
		else {
			$cou_data[$k]['is_overdue'] = 0;
		}

		$cou_data[$k]['cou_type_name'] = $v['cou_type'] == 3 ? L('vouchers_all') : ($v['cou_type'] == 4 ? L('vouchers_user') : ($v['cou_type'] == 5 ? L('vouchers_shipping') : L('unknown')));

		if ($_SESSION['user_id']) {
			$r = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('coupons_user') . ' WHERE cou_id=\'' . $v['cou_id'] . '\' AND user_id =\'' . $_SESSION['user_id'] . '\'');

			if ($v['cou_user_num'] <= $r) {
				$cou_data[$k]['cou_is_receive'] = 1;
			}
			else {
				$cou_data[$k]['cou_is_receive'] = 0;
			}
		}
	}

	return $cou_data;
}

function get_coupons_list($num = 10, $page = 1, $status = 0)
{
	$time = gmtime();
	$where = '1';

	if ($status == 0) {
		$where .= ' AND c.cou_type = 3 ';
	}
	else if ($status == 1) {
		$where .= ' AND c.cou_type = 4 ';
	}
	else if ($status == 2) {
		$where .= ' AND c.cou_type = 5 ';
	}

	$sql = 'SELECT COUNT(c.cou_id) FROM ' . $GLOBALS['ecs']->table('coupons') . (' c WHERE c.review_status = 3 AND c.cou_type NOT IN(1,2) AND c.cou_end_time > ' . $time . ' AND ' . $time . ' > c.cou_start_time AND ') . $where;
	$total = $GLOBALS['db']->getOne($sql);
	$start = ($page - 1) * $num;
	$sql = 'SELECT c.*,cu.user_id,cu.is_use FROM ' . $GLOBALS['ecs']->table('coupons') . ' c LEFT JOIN ' . $GLOBALS['ecs']->table('coupons_user') . (' cu ON c.cou_id=cu.cou_id WHERE c.review_status = 3 AND c.cou_type  NOT IN(1,2) AND c.cou_end_time > ' . $time . ' AND ' . $where . ' GROUP BY c.cou_id  ORDER BY c.cou_id DESC limit ') . $start . ' , ' . $num . '';
	$cou_data = $GLOBALS['db']->getAll($sql);

	foreach ($cou_data as $k => $v) {
		$cou_data[$k]['begintime'] = local_date('Y-m-d', $v['cou_start_time']);
		$cou_data[$k]['endtime'] = local_date('Y-m-d', $v['cou_end_time']);
		$cou_data[$k]['img'] = 'images/coupons_default.png';
		$cou_data[$k]['store_name'] = sprintf(L('use_limit'), get_shop_name($v['ru_id'], 1));
		$cou_data[$k]['cou_type_name'] = $v['cou_type'] == 3 ? L('vouchers_all') : ($v['cou_type'] == 4 ? L('vouchers_user') : ($v['cou_type'] == 5 ? L('vouchers_shipping') : L('unknown')));

		if (0 < $_SESSION['user_id']) {
			$is_use = dao('coupons_user')->where(array('cou_id' => $v['cou_id'], 'user_id' => $_SESSION['user_id']))->getField('is_use');
			$cou_data[$k]['is_use'] = empty($is_use) ? 0 : $is_use;
		}

		$cou_data[$k]['is_overdue'] = $v['cou_end_time'] < gmtime() ? 1 : 0;

		if ($_SESSION['user_id']) {
			$user_num = dao('coupons_user')->where(array('cou_id' => $v['cou_id'], 'user_id' => $_SESSION['user_id']))->count();
			if (0 < $user_num && $v['cou_user_num'] <= $user_num) {
				$cou_data[$k]['cou_is_receive'] = 1;
			}
			else {
				$cou_data[$k]['cou_is_receive'] = 0;
			}
		}

		$cou_num = dao('coupons_user')->where(array('cou_id' => $v['cou_id']))->count();
		$cou_data[$k]['enable_ling'] = !empty($cou_num) && $v['cou_total'] <= $cou_num ? 1 : 0;
	}

	return array('tab' => $cou_data, 'totalpage' => ceil($total / $num));
}

function get_coupons_goods_list($num = 10, $page = 1)
{
	$time = gmtime();
	$sql = 'SELECT COUNT(c.cou_id) FROM ' . $GLOBALS['ecs']->table('coupons') . (' c WHERE c.review_status = 3 AND c.cou_type = 2 AND c.cou_end_time > ' . $time . ' AND ' . $time . ' > c.cou_start_time');
	$total = $GLOBALS['db']->getOne($sql);
	$start = ($page - 1) * $num;
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('coupons') . (' c  WHERE c.review_status = 3 AND c.cou_type = 2 AND c.cou_end_time > ' . $time . '  GROUP BY c.cou_id  ORDER BY c.cou_id DESC limit ') . $start . ' , ' . $num . '';
	$cou_goods = $GLOBALS['db']->getAll($sql);

	foreach ($cou_goods as $k => $v) {
		$cou_goods[$k]['begintime'] = local_date('Y-m-d', $v['cou_start_time']);
		$cou_goods[$k]['endtime'] = local_date('Y-m-d', $v['cou_end_time']);
		$cou_goods[$k]['store_name'] = sprintf(L('use_limit'), get_shop_name($v['ru_id'], 1));
		$cou_goods[$k]['cou_type_name'] = $v['cou_type'] == 2 ? L('vouchers_shoping') : '';

		if ($v['cou_ok_goods']) {
			$cou_goods[$k]['cou_ok_goods_name'] = $GLOBALS['db']->getAll('SELECT goods_id,goods_name,goods_thumb FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id IN(' . $v['cou_ok_goods'] . ')');
		}
		else {
			$cou_goods[$k]['cou_ok_goods_name'][0]['goods_thumb'] = 'images/coupons_default.png';
		}

		$cou_goods[$k]['is_overdue'] = $v['cou_end_time'] < gmtime() ? 1 : 0;

		if ($_SESSION['user_id']) {
			$user_num = dao('coupons_user')->where(array('cou_id' => $v['cou_id'], 'user_id' => $_SESSION['user_id']))->count();
			if (0 < $user_num && $v['cou_user_num'] <= $user_num) {
				$cou_goods[$k]['cou_is_receive'] = 1;
			}
			else {
				$cou_goods[$k]['cou_is_receive'] = 0;
			}
		}

		$cou_num = dao('coupons_user')->where(array('cou_id' => $v['cou_id']))->count();
		$cou_goods[$k]['enable_ling'] = !empty($cou_num) && $v['cou_total'] <= $cou_num ? 1 : 0;
	}

	return array('tab' => $cou_goods, 'totalpage' => ceil($total / $num));
}


?>
