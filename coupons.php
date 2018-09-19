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
			$v['cou_goods'] = get_del_str_comma($v['cou_goods']);
			$cou_goods_arr = $GLOBALS['db']->getAll('SELECT goods_id,goods_name,goods_thumb FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id ' . db_create_in($v['cou_goods']));

			if (!empty($cou_goods_arr)) {
				foreach ($cou_goods_arr as $g_key => $g_val) {
					if ($g_val['goods_thumb']) {
						$cou_goods_arr[$g_key]['goods_thumb'] = get_image_path($v['cou_id'], $g_val['goods_thumb']);
					}
				}
			}

			$cou_data[$k]['cou_goods_name'] = $cou_goods_arr;
		}

		if (!empty($v['cou_ok_user'])) {
			$cou_data[$k]['cou_ok_user_name'] = $GLOBALS['db']->getOne('SELECT group_concat(rank_name)  FROM ' . $GLOBALS['ecs']->table('user_rank') . ' WHERE rank_id IN(' . $v['cou_ok_user'] . ')');
		}

		$cou_data[$k]['store_name'] = sprintf($GLOBALS['_LANG']['use_limit'], get_shop_name($v['ru_id'], 1));
		$cou_data[$k]['cou_start_time_format'] = local_date('Y/m/d', $v['cou_start_time']);
		$cou_data[$k]['cou_end_time_format'] = local_date('Y/m/d', $v['cou_end_time']);

		if ($v['cou_end_time'] < $time) {
			$cou_data[$k]['is_overdue'] = 1;
		}
		else {
			$cou_data[$k]['is_overdue'] = 0;
		}

		$cou_data[$k]['cou_type_name'] = $v['cou_type'] == 3 ? $GLOBALS['_LANG']['vouchers_all'] : ($v['cou_type'] == 4 ? $GLOBALS['_LANG']['vouchers_user'] : ($v['cou_type'] == 5 ? $GLOBALS['_LANG']['vouchers_shipping'] : $GLOBALS['_LANG']['unknown']));

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

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . 'includes/cls_json.php';
get_request_filter();
require ROOT_PATH . '/includes/lib_area.php';
$area_info = get_area_info($province_id);
$area_id = $area_info['region_id'];
$where = 'regionId = \'' . $province_id . '\'';
$date = array('parent_id');
$region_id = get_table_date('region_warehouse', $where, $date, 2);
$user_id = !empty($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
assign_template();
assign_ur_here();
$smarty->assign('helps', get_shop_help());
$categories_pro = get_category_tree_leve_one();
$smarty->assign('categories_pro', $categories_pro);
$smarty->assign('navigator_list', get_navigator($ctype, $catlist));
$time = gmtime();

if ($_REQUEST['act'] == 'coupons_index') {
	for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
		$coupons_index .= '\'coupons_index' . $i . ',';
	}

	$smarty->assign('coupons_index', $coupons_index);
	$time = gmtime();
	$sql = 'SELECT c.cou_id,FLOOR((c.cou_total-COUNT(cu.cou_id))/c.cou_total*100) cou_surplus FROM ' . $ecs->table('coupons_user') . ' cu LEFT JOIN ' . $ecs->table('coupons') . (' c ON c.cou_id=cu.cou_id  WHERE c.review_status = 3 AND c.cou_type NOT IN(1,2) AND c.cou_end_time>' . $time . ' GROUP BY c.cou_id ORDER BY c.cou_id DESC limit 6');
	$cou_surplus = $db->getAll($sql);
	$sql = 'SELECT c.*,cu.user_id,cu.is_use FROM ' . $ecs->table('coupons') . ' c LEFT JOIN ' . $ecs->table('coupons_user') . (' cu ON c.cou_id=cu.cou_id WHERE c.review_status = 3 AND c.cou_type  NOT IN(1,2,5) AND c.cou_end_time>' . $time . ' GROUP BY c.cou_id ORDER BY c.cou_id DESC limit 6');
	$cou_data = $db->getAll($sql);

	foreach ($cou_data as $k => $v) {
		foreach ($cou_surplus as $m => $n) {
			if ($v['cou_id'] == $n['cou_id']) {
				$cou_data[$k]['cou_surplus'] = $n['cou_surplus'];
			}
		}
	}

	$cou_data = fromat_coupons($cou_data);
	$seckill = $cou_data;

	foreach ($seckill as $k => $v) {
		if ($v['cou_goods']) {
			$sort_arr[] = $v['cou_order'];
		}
		else {
			$seckill[$k]['cou_goods_name'][0]['goods_thumb'] = 'images/coupons_default.png';
		}
	}

	array_multisort($sort_arr, SORT_DESC, $seckill);
	$seckill = array_slice($seckill, 0, 4);
	$sql = 'SELECT * FROM ' . $ecs->table('coupons') . (' where review_status = 3 AND cou_type  IN(2) AND cou_end_time>' . $time . ' limit 4');
	$cou_goods = $db->getAll($sql);

	foreach ($cou_goods as $k => $v) {
		if ($v['cou_ok_goods']) {
			$v['cou_ok_goods'] = get_del_str_comma($v['cou_ok_goods']);
			$cou_goods_arr = $db->getAll('SELECT goods_id,goods_name,goods_thumb FROM ' . $ecs->table('goods') . ' WHERE goods_id ' . db_create_in($v['cou_ok_goods']));

			if (!empty($cou_goods_arr)) {
				foreach ($cou_goods_arr as $g_key => $g_val) {
					if ($g_val['goods_thumb']) {
						$cou_goods_arr[$g_key]['goods_thumb'] = get_image_path($v['cou_id'], $g_val['goods_thumb']);
					}
				}
			}

			$cou_goods[$k]['cou_ok_goods_name'] = $cou_goods_arr;
		}
		else {
			$cou_goods[$k]['cou_ok_goods_name'][0]['goods_thumb'] = get_image_path($v['cou_id'], 'images/coupons_default.png');
		}

		$cou_goods[$k]['store_name'] = sprintf($GLOBALS['_LANG']['use_limit'], get_shop_name($v['ru_id'], 1));
		$cou_goods[$k]['cou_end_time_format'] = local_date('Y-m-d H:i:s', $v['cou_end_time']);
	}

	$sql = 'SELECT * FROM ' . $ecs->table('coupons') . (' where review_status = 3 AND cou_type  IN(5) AND cou_end_time>' . $time . ' limit 4');
	$cou_shipping = $db->getAll($sql);

	foreach ($cou_shipping as $k => $v) {
		foreach ($cou_surplus as $m => $n) {
			if ($v['cou_id'] == $n['cou_id']) {
				$cou_shipping[$k]['cou_surplus'] = $n['cou_surplus'];
			}
		}
	}

	$cou_shipping = fromat_coupons($cou_shipping);

	if ($_SESSION['user_id']) {
		foreach ($cou_data as $k => $v) {
			$cou_data[$k]['is_use'] = $db->getOne('SELECT is_use FROM' . $ecs->table('coupons_user') . 'WHERE cou_id=\'' . $v['cou_id'] . '\' AND user_id=\'' . $_SESSION['user_id'] . '\' ORDER BY uc_id DESC LIMIT 1');
		}

		foreach ($cou_shipping as $k => $v) {
			$cou_shipping[$k]['is_use'] = $db->getOne('SELECT is_use FROM' . $ecs->table('coupons_user') . 'WHERE cou_id=\'' . $v['cou_id'] . '\' AND user_id=\'' . $_SESSION['user_id'] . '\' ORDER BY uc_id DESC LIMIT 1');
		}
	}

	$smarty->assign('cou_shipping', $cou_shipping);
	$smarty->assign('seckill', $seckill);
	$smarty->assign('cou_goods', $cou_goods);
	$smarty->assign('cou_data', $cou_data);
	$smarty->assign('page_title', $_LANG['page_title_Coupon']);
	$smarty->display('coupons_index.dwt');
}
else if ($_REQUEST['act'] == 'coupons_list') {
	$field_arr = array('cou_end_time', 'cou_money');
	$order_field = !in_array($_REQUEST['field'], $field_arr) ? 'c.cou_id' : 'c.' . addslashes($_REQUEST['field']);

	if (!empty($_REQUEST['type'])) {
		if ($_REQUEST['type'] == 'all') {
			$where = ' AND cou_type = 3 ';
		}
		else if ($_REQUEST['type'] == 'member') {
			$where = ' AND cou_type = 4 ';
		}
		else if ($_REQUEST['type'] == 'shipping') {
			$where = ' AND cou_type = 5 ';
		}
		else {
			$where = ' ';
		}
	}
	else {
		$where = ' ';
	}

	$time = gmtime();
	$sql = 'SELECT c.cou_id,FLOOR((c.cou_total-COUNT(cu.cou_id))/c.cou_total*100) cou_surplus FROM ' . $ecs->table('coupons_user') . ' cu LEFT JOIN ' . $ecs->table('coupons') . (' c ON c.cou_id=cu.cou_id  WHERE c.review_status = 3 AND c.cou_type NOT IN(1,2) AND c.cou_end_time>' . $time . ' GROUP BY c.cou_id limit 6');
	$cou_surplus = $db->getAll($sql);
	$sql = 'SELECT COUNT(c.cou_id) FROM ' . $ecs->table('coupons') . (' c  WHERE c.review_status = 3 AND c.cou_type  NOT IN(1,2) AND c.cou_end_time>' . $time . ' ' . $where . ' ');
	$cou_row_total = $db->getOne($sql);
	$row_num = 12;
	$page_total = ceil($cou_row_total / $row_num);
	$page = empty($_REQUEST['p']) || $page_total < $_REQUEST['p'] ? 1 : $_REQUEST['p'];
	$offset = ($page - 1) * $row_num;
	$sql = 'SELECT c.*,cu.user_id,cu.is_use FROM ' . $ecs->table('coupons') . ' c LEFT JOIN ' . $ecs->table('coupons_user') . (' cu ON c.cou_id=cu.cou_id WHERE c.review_status = 3 AND c.cou_type  NOT IN(1,2) AND c.cou_end_time>' . $time . ' ' . $where . '  GROUP BY c.cou_id  ORDER BY ' . $order_field . ' DESC limit ') . $offset . ' , ' . $row_num . '';
	$cou_data = $db->getAll($sql);

	foreach ($cou_data as $k => $v) {
		foreach ($cou_surplus as $m => $n) {
			if ($v['cou_id'] == $n['cou_id']) {
				$cou_data[$k]['cou_surplus'] = $n['cou_surplus'];
			}
		}
	}

	$cou_data = fromat_coupons($cou_data);

	if ($_SESSION['user_id']) {
		foreach ($cou_data as $k => $v) {
			$cou_data[$k]['is_use'] = $db->getOne('SELECT is_use FROM' . $ecs->table('coupons_user') . 'WHERE cou_id=\'' . $v['cou_id'] . '\' AND user_id=\'' . $_SESSION['user_id'] . '\' ORDER BY uc_id DESC LIMIT 1');
		}
	}

	for ($i = 1; $i <= $page_total; $i++) {
		$page_total2[] = $i;
	}

	$page_url = strstr($_SERVER['QUERY_STRING'], '&p', true) ? strstr($_SERVER['QUERY_STRING'], '&p', true) : $_SERVER['QUERY_STRING'];
	$smarty->assign('page_total2', $page_total2);
	$smarty->assign('page_total', $page_total);
	$smarty->assign('page', $page);
	$smarty->assign('prev_page', $page == 1 ? 1 : $page - 1);
	$smarty->assign('next_page', $page == $page_total ? $page_total : $page + 1);
	$smarty->assign('page_url', $page_url);
	$smarty->assign('cou_data', $cou_data);
	$smarty->assign('page_title', '领券中心-好券集市');
	$smarty->display('coupons_list.dwt');
}
else if ($_REQUEST['act'] == 'coupons_goods') {
	$time = gmtime();
	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('coupons') . (' WHERE review_status = 3 AND cou_type IN(2) AND cou_end_time>' . $time);
	$cou_row_total = $db->getOne($sql);
	$row_num = 10;
	$page_total = ceil($cou_row_total / $row_num);
	$page = empty($_REQUEST['p']) || $page_total < $_REQUEST['p'] ? 1 : $_REQUEST['p'];
	$offset = ($page - 1) * $row_num;
	$sql = 'SELECT * FROM ' . $ecs->table('coupons') . ' WHERE review_status = 3 AND cou_type IN(2) limit ' . $offset . ' , ' . $row_num . '';
	$cou_goods = $db->getAll($sql);

	foreach ($cou_goods as $k => $v) {
		if ($v['cou_ok_goods']) {
			$v['cou_ok_goods'] = get_del_str_comma($v['cou_ok_goods']);
			$cou_goods_arr = $db->getAll('SELECT goods_id,goods_name,goods_thumb FROM ' . $ecs->table('goods') . ' WHERE goods_id ' . db_create_in($v['cou_ok_goods']));

			if (!empty($cou_goods_arr)) {
				foreach ($cou_goods_arr as $g_key => $g_val) {
					if ($g_val['goods_thumb']) {
						$cou_goods_arr[$g_key]['goods_thumb'] = get_image_path($v['cou_id'], $g_val['goods_thumb']);
					}
				}
			}

			$cou_goods[$k]['cou_ok_goods_name'] = $cou_goods_arr;
		}
		else {
			$cou_goods[$k]['cou_ok_goods_name'][0]['goods_thumb'] = get_image_path($v['cou_id'], 'images/coupons_default.png');
		}

		$cou_goods[$k]['cou_end_time_format'] = local_date('Y-m-d H:i:s', $v['cou_end_time']);

		if ($v['cou_end_time'] < $time) {
			$cou_goods[$k]['is_overtime'] = 0;
		}
		else {
			$cou_goods[$k]['is_overtime'] = 1;
		}

		$cou_goods[$k]['store_name'] = sprintf($GLOBALS['_LANG']['use_limit'], get_shop_name($v['ru_id'], 1));
	}

	for ($i = 1; $i <= $page_total; $i++) {
		$page_total2[] = $i;
	}

	$page_url = strstr($_SERVER['QUERY_STRING'], '&p', true) ? strstr($_SERVER['QUERY_STRING'], '&p', true) : $_SERVER['QUERY_STRING'];
	$smarty->assign('page_total2', $page_total2);
	$smarty->assign('page_total', $page_total);
	$smarty->assign('page', $page);
	$smarty->assign('prev_page', $page == 1 ? 1 : $page - 1);
	$smarty->assign('next_page', $page == $page_total ? $page_total : $page + 1);
	$smarty->assign('page_url', $page_url);
	$smarty->assign('cou_goods', $cou_goods);
	$smarty->assign('page_title', $_LANG['Coupon_redemption_task']);
	$smarty->display('coupons_goods.dwt');
}
else if ($_REQUEST['act'] == 'coupons_receive') {
	$cou_id = !empty($_REQUEST['cou_id']) ? intval($_REQUEST['cou_id']) : 0;
	$result['is_over'] = 0;
	$sql = 'SELECT c.*,c.cou_total-COUNT(cu.cou_id) cou_surplus FROM ' . $ecs->table('coupons') . ' c LEFT JOIN ' . $ecs->table('coupons_user') . (' cu ON c.cou_id = cu.cou_id GROUP BY c.cou_id  HAVING cou_surplus > 0 AND  c.cou_id = \'' . $cou_id . '\' AND c.review_status = 3 AND c.cou_end_time > ' . $time . ' LIMIT 1');
	$cou_data = $db->getRow($sql);

	if (!$cou_data) {
		exit(json_encode(array('status' => 'error', 'msg' => $_LANG['lang_coupons_receive_failure'])));
	}

	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('coupons_user') . (' WHERE user_id = \'' . $user_id . '\' AND cou_id = \'' . $cou_id . '\'');
	$cou_user_num = $db->getOne($sql);

	if ($cou_data['cou_user_num'] <= $cou_user_num) {
		exit(json_encode(array('status' => 'error', 'msg' => sprintf($_LANG['lang_coupons_user_receive'], $cou_data['cou_user_num']))));
	}
	else {
		$result['is_over'] = 1;
	}

	if (strpos(',' . $cou_data['cou_ok_user'] . ',', ',' . $_SESSION['user_rank'] . ',') === false && $cou_data['cou_type'] != 3) {
		$rank_name = $db->getOne('SELECT GROUP_CONCAT(rank_name) FROM ' . $ecs->table('user_rank') . ' WHERE rank_id IN(' . $cou_data['cou_ok_user'] . ')');
		exit(json_encode(array('status' => 'error', 'msg' => sprintf($_LANG['lang_coupons_user_rank'], $rank_name))));
	}

	$uc_sn = $time . rand(10, 99);
	$sql = 'INSERT INTO ' . $ecs->table('coupons_user') . (' (`user_id`,`cou_id`,`cou_money`,`uc_sn`) VALUES (' . $user_id . ',' . $cou_id . ', \'') . $cou_data['cou_money'] . ('\',\'' . $uc_sn . '\') ');

	if ($db->query($sql)) {
		$sql = 'SELECT c.cou_id,FLOOR((c.cou_total-COUNT(cu.cou_id))/c.cou_total*100) cou_surplus FROM ' . $ecs->table('coupons_user') . ' cu LEFT JOIN ' . $ecs->table('coupons') . (' c ON c.cou_id=cu.cou_id  WHERE c.cou_type NOT IN(1,2,5) AND c.cou_end_time>' . $time . ' GROUP BY c.cou_id ORDER BY c.cou_id DESC limit 6');
		$cou_surplus = $db->getAll($sql);
		$sql = 'SELECT c.*,cu.user_id,cu.is_use FROM ' . $ecs->table('coupons') . ' c LEFT JOIN ' . $ecs->table('coupons_user') . (' cu ON c.cou_id=cu.cou_id WHERE c.cou_type  NOT IN(1,2,5) AND c.cou_end_time>' . $time . ' GROUP BY c.cou_id ORDER BY c.cou_id DESC limit 6');
		$cou_data = $db->getAll($sql);

		foreach ($cou_data as $k => $v) {
			foreach ($cou_surplus as $m => $n) {
				if ($v['cou_id'] == $n['cou_id']) {
					$cou_data[$k]['cou_surplus'] = $n['cou_surplus'];
				}
			}
		}

		$cou_data = fromat_coupons($cou_data);
		$seckill = $cou_data;

		foreach ($seckill as $k => $v) {
			if ($v['cou_goods']) {
				$sort_arr[] = $v['cou_order'];
			}
			else {
				$seckill[$k]['cou_goods_name'][0]['goods_thumb'] = get_image_path('images/coupons_default.png');
			}
		}

		array_multisort($sort_arr, SORT_DESC, $seckill);
		$seckill = array_slice($seckill, 0, 4);
		$sql = 'SELECT * FROM ' . $ecs->table('coupons') . (' where review_status = 3 AND cou_type  IN(5) AND cou_end_time>' . $time . ' limit 4');
		$cou_shipping = $db->getAll($sql);

		foreach ($cou_shipping as $k => $v) {
			foreach ($cou_surplus as $m => $n) {
				if ($v['cou_id'] == $n['cou_id']) {
					$cou_shipping[$k]['cou_surplus'] = $n['cou_surplus'];
				}
			}
		}

		$cou_shipping = fromat_coupons($cou_shipping);

		if ($_SESSION['user_id']) {
			foreach ($cou_data as $k => $v) {
				$cou_data[$k]['is_use'] = $db->getOne('SELECT is_use FROM' . $ecs->table('coupons_user') . 'WHERE cou_id=\'' . $v['cou_id'] . '\' AND user_id=\'' . $_SESSION['user_id'] . '\' ORDER BY uc_id DESC LIMIT 1');
			}

			foreach ($cou_shipping as $k => $v) {
				$cou_shipping[$k]['is_use'] = $db->getOne('SELECT is_use FROM' . $ecs->table('coupons_user') . 'WHERE cou_id=\'' . $v['cou_id'] . '\' AND user_id=\'' . $_SESSION['user_id'] . '\' ORDER BY uc_id DESC LIMIT 1');
			}
		}

		$GLOBALS['smarty']->assign('seckill', $seckill);
		$result['content_kill'] = $GLOBALS['smarty']->fetch('library/coupons_seckill.lbi');
		$cou_data = fromat_coupons($cou_data);
		$GLOBALS['smarty']->assign('cou_data', $cou_data);
		$result['content'] = $GLOBALS['smarty']->fetch('library/coupons_data.lbi');
		$cou_data = $cou_shipping;
		$GLOBALS['smarty']->assign('cou_data', $cou_data);
		$result['content_shipping'] = $GLOBALS['smarty']->fetch('library/coupons_data.lbi');
		exit(json_encode(array('status' => 'ok', 'msg' => $_LANG['lang_coupons_receive_succeed'], 'content' => $result['content'], 'content_kill' => $result['content_kill'])));
	}
}

if ($_REQUEST['act'] == 'coupons_info') {
	assign_template();
	$position = assign_ur_here();
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $position['ur_here']);
	$smarty->assign('feed_url', $_CFG['rewrite'] == 1 ? 'feed.xml' : 'feed.php');
	$smarty->assign('helps', get_shop_help());
	$cou_id = !empty($_GET['id']) ? intval($_GET['id']) : 0;
	$cou_info = $db->getRow('SELECT * FROM ' . $ecs->table('coupons') . (' WHERE cou_id = \'' . $cou_id . '\' AND cou_type IN (3,4,5) '));

	if ($cou_info) {
		$cou_info['cou_start_date'] = local_date('Y-m-d H:i:s', $cou_info['cou_start_time']);
		$cou_info['cou_end_date'] = local_date('Y-m-d H:i:s', $cou_info['cou_end_time']);
		$cou_info['type_money_formatted'] = price_format($cou_info['cou_money']);
		$cou_info['min_goods_amount_formatted'] = price_format($cou_info['cou_man']);
		$cou_info['shop_name'] = get_shop_name($cou_info['ru_id'], 1);

		if ($cou_info['cou_type'] == 5) {
			$cou_region_list = get_cou_region_list($cou_info['cou_id']);
			$cou_info['region_name'] = $cou_region_list['free_value_name'];
		}

		$smarty->assign('cou_info', $cou_info);
	}

	if ($_SESSION['user_id']) {
		$sql = ' SELECT COUNT(uc_id) AS user_num, cou_id FROM ' . $GLOBALS['ecs']->table('coupons_user') . (' WHERE cou_id = \'' . $cou_id . '\' AND user_id = \'' . $_SESSION['user_id'] . '\' LIMIT 1 ');
		$res = $GLOBALS['db']->getRow($sql);

		if ($res['cou_id']) {
			$sql = ' SELECT cou_user_num FROM ' . $GLOBALS['ecs']->table('coupons') . (' WHERE cou_id = \'' . $res['cou_id'] . '\' ');
			$num = $GLOBALS['db']->getOne($sql);

			if ($num <= $res['user_num']) {
				$smarty->assign('exist', true);
			}
		}
	}

	$sql = ' SELECT COUNT(cou_id) FROM ' . $GLOBALS['ecs']->table('coupons') . (' WHERE cou_id = \'' . $cou_id . '\' AND (SELECT COUNT(uc_id) FROM ') . $GLOBALS['ecs']->table('coupons_user') . (' WHERE cou_id = \'' . $cou_id . '\') < cou_total  LIMIT 1 ');
	$left = $GLOBALS['db']->getOne($sql);
	$smarty->assign('left', $left);
	$smarty->display('coupons.dwt');
}

?>
