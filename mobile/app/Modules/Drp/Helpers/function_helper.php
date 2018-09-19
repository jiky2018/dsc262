<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function drp()
{
	$drp_id = dao('drp_shop')->where('user_id=' . $_SESSION['user_id'])->getField('id');

	if (0 < $drp_id) {
		$drp_info = get_drp($drp_id, '1');

		if ($drp_info['open'] == 1) {
			$drp_info['cat_id'] = substr($drp_info['cat_id'], 0, -1);
			$_SESSION['drp_shop'] = $drp_info;
		}
	}
	else if (0 < $_GET['drp_id']) {
		$drp_info = get_drp($_GET['drp_id'], '1');

		if ($drp_info['open'] == 1) {
			$drp_info['cat_id'] = substr($drp_info['cat_id'], 0, -1);
			$_SESSION['drp_shop'] = $drp_info;
		}
	}
	else {
		if ($_SESSION['user_id'] && !$_SESSION['drp_shop']) {
			$drp_info = get_drp($_SESSION['user_id']);

			if ($drp_info['open'] == 1) {
				$drp_info['cat_id'] = substr($drp_info['cat_id'], 0, -1);
				$_SESSION['drp_shop'] = $drp_info;
			}
			else {
				$parent_id = dao('users')->where('user_id=' . $_SESSION['user_id'])->getField('parent_id');

				if ($parent_id) {
					$drp_info = get_drp($parent_id);

					if ($drp_info['open'] == 1) {
						$drp_info['cat_id'] = substr($drp_info['cat_id'], 0, -1);
						$_SESSION['drp_shop'] = $drp_info;
					}
				}
			}
		}
	}
}

function get_drp($user_id = 0, $is_drp = 0)
{
	if (empty($is_drp)) {
		$sql = 'SELECT * FROM {pre}drp_shop WHERE user_id = \'' . $user_id . '\'';
	}
	else {
		$sql = 'SELECT * FROM {pre}drp_shop WHERE id = \'' . $user_id . '\'';
	}

	$shopInfo = $GLOBALS['db']->getRow($sql);

	if (empty($shopInfo)) {
		return array();
	}

	$info = array();

	if (is_dir(APP_WECHAT_PATH)) {
		$info = dao('wechat_user')->field('nickname, headimgurl')->where(array('ect_uid' => $user_id))->find();

		if (!empty($info)) {
			$info['headimgurl'] = $info['headimgurl'];
			$info['nickname'] = $info['nickname'];
		}
		else {
			$user_name = dao('users')->where(array('user_id' => $user_id))->getField('user_name');
			$info['headimgurl'] = elixir('img/no_image.jpg');
			$info['nickname'] = $user_name;
		}
	}

	$info['drp_id'] = $shopInfo['id'];
	$info['shop_name'] = $shopInfo['shop_name'];
	$info['real_name'] = $shopInfo['real_name'];
	$info['audit'] = $shopInfo['audit'];
	$info['cat_id'] = $shopInfo['cat_id'];
	$info['shop_img'] = $shopInfo['shop_img'] ? __STATIC__ . '/data/attached/drp_logo/' . $shopInfo['shop_img'] : '';
	$info['user_id'] = $user_id;
	$info['create_time'] = local_date($GLOBALS['_CFG']['time_format'], $shopInfo['create_time']);
	$info['shop_money'] = $shopInfo['shop_money'];
	$sql = 'SELECT `value` FROM {pre}drp_config WHERE code = \'draw_money\'';
	$info['draw_money_value'] = $GLOBALS['db']->getOne($sql);
	return $info;
}

function drp_audit_status($user_id = 0)
{
	if ($user_id == 0) {
		return false;
	}

	$drp_shop = dao('drp_shop')->field('audit,status')->where(array('user_id' => $user_id))->find();
	if (($drp_shop['audit'] == 1) && ($drp_shop['status'] == 1)) {
		return $drp_shop;
	}
	else {
		return false;
	}
}

function copy_array_column($input, $columnKey, $indexKey = NULL)
{
	$columnKeyIsNumber = (is_numeric($columnKey) ? true : false);
	$indexKeyIsNull = (is_null($indexKey) ? true : false);
	$indexKeyIsNumber = (is_numeric($indexKey) ? true : false);
	$result = array();

	foreach ((array) $input as $key => $row) {
		if ($columnKeyIsNumber) {
			$tmp = array_slice($row, $columnKey, 1);
			$tmp = (is_array($tmp) && !empty($tmp) ? current($tmp) : NULL);
		}
		else {
			$tmp = (isset($row[$columnKey]) ? $row[$columnKey] : NULL);
		}

		if (!$indexKeyIsNull) {
			if ($indexKeyIsNumber) {
				$key = array_slice($row, $indexKey, 1);
				$key = (is_array($key) && !empty($key) ? current($key) : NULL);
				$key = (is_null($key) ? 0 : $key);
			}
			else {
				$key = (isset($row[$indexKey]) ? $row[$indexKey] : 0);
			}
		}

		$result[$key] = $tmp;
	}

	return $result;
}

function drp_log_account_change($user_id, $shop_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '', $change_type)
{
	$sql = 'SELECT shop_money  ' . ' FROM ' . $GLOBALS['ecs']->table('drp_shop') . ' WHERE user_id = \'' . $user_id . '\'';
	$user_info = $GLOBALS['db']->getRow($sql);
	if (($user_info['shop_money'] == 0) && ($shop_money < 0)) {
		$shop_money = 0;
	}

	if (($user_info['shop_points'] == 0) && ($pay_points < 0)) {
		$pay_points = 0;
	}

	if ($change_type == ACT_TRANSFERRED) {
		$account_log = array('user_id' => $user_id, 'user_money' => 0 - $shop_money, 'frozen_money' => $frozen_money, 'rank_points' => 0 - $rank_points, 'pay_points' => 0 - $pay_points, 'change_time' => gmtime(), 'change_desc' => $change_desc, 'change_type' => $change_type);
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('account_log'), $account_log, 'INSERT');
	}

	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('drp_shop') . ' SET shop_money = shop_money + (\'' . $shop_money . '\'),' . ' shop_points = shop_points + (\'' . $pay_points . '\')' . ' WHERE user_id = \'' . $user_id . '\' LIMIT 1';
	$GLOBALS['db']->query($sql);

	if ($change_type == ACT_TRANSFERRED) {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET user_money = user_money - (\'' . $shop_money . '\'),' . ' frozen_money = frozen_money - (\'' . $frozen_money . '\'),' . ' rank_points = rank_points - (\'' . $rank_points . '\'),' . ' pay_points = pay_points - (\'' . $pay_points . '\')' . ' WHERE user_id = \'' . $user_id . '\' LIMIT 1';
		$GLOBALS['db']->query($sql);
	}
}

function get_goods($goods, $warehouse_id = 0, $area_id = 0, $size = 10, $page = 1, $status = 0, $type, $sort, $order, $cat_id)
{
	if ($type == 0) {
		$where = 'g.is_on_sale = 1 AND g.dis_commission >0 AND g.is_distribution = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ';

		if (0 < $cat_id) {
			$where .= ' AND ' . get_children($cat_id);
		}
		else {
			if ($status == 2) {
				$where .= ' AND g.is_new = 1 ';
			}

			if ($status == 1) {
				$where .= ' ';
			}

			if ($status == 3) {
				$time = gmtime();
				$where .= ' AND g.promote_price > 0 AND g.promote_start_date <= \'' . $time . '\' AND g.promote_end_date >= \'' . $time . '\' ';
			}
		}
	}
	else {
		$where = 'g.is_on_sale = 1 AND g.dis_commission >0 AND g.is_distribution = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ';

		if (0 < $cat_id) {
			$where .= ' ' . $goods . ' ';
		}
		else {
			if ($status == 2) {
				$where .= ' AND g.is_new = 1 ' . $goods . ' ';
			}

			if ($status == 1) {
				$where .= ' ' . $goods . ' ';
			}

			if ($status == 3) {
				$time = gmtime();
				$where .= ' AND g.promote_price > 0 AND g.promote_start_date <= \'' . $time . '\' AND g.promote_end_date >= \'' . $time . '\' ' . $goods . ' ';
			}
		}
	}

	$leftJoin = '';
	$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ';

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$where .= ' and lag.region_id = \'' . $area_id . '\' ';
	}

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$where .= ' AND g.review_status > 2 ';
	}

	$ischeck = get_drp_config_commission();

	if ($sort == 'last_update') {
		$sort = 'g.last_update';
	}

	$sql = 'SELECT g.goods_id,g.dis_commission, g.user_id, g.goods_name, ' . $shop_price . ' g.goods_name_style, g.comments_number,g.sales_volume,g.market_price, g.is_new, g.is_best, g.is_hot, ' . ' IF(g.model_price < 1, g.goods_number, IF(g.model_price < 2, wg.region_number, wag.region_number)) AS goods_number, ' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, g.model_price, ' . 'IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\') AS shop_price, ' . 'IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)) as promote_price, g.goods_type, ' . 'g.promote_start_date, g.promote_end_date, g.is_promote, g.goods_brief, g.goods_thumb , g.goods_img ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . 'WHERE ' . $where . ' ORDER BY ' . $sort . ' ' . $order;
	$total_query = $GLOBALS['db']->query($sql);
	$total = (is_array($total_query) ? count($total_query) : 0);
	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
	$arr = array();

	foreach ($res as $row) {
		$arr[$row['goods_id']]['org_price'] = $row['org_price'];
		$arr[$row['goods_id']]['model_price'] = $row['model_price'];
		$arr[$row['goods_id']]['commission'] = $ischeck;
		$arr[$row['goods_id']]['dis_commission'] = $row['dis_commission'];
		$arr[$row['goods_id']]['warehouse_price'] = $row['warehouse_price'];
		$arr[$row['goods_id']]['warehouse_promote_price'] = $row['warehouse_promote_price'];
		$arr[$row['goods_id']]['region_price'] = $row['region_price'];
		$arr[$row['goods_id']]['region_promote_price'] = $row['region_promote_price'];

		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$watermark_img = '';

		if ($promote_price != 0) {
			$watermark_img = 'watermark_promote_small';
		}
		else if ($row['is_new'] != 0) {
			$watermark_img = 'watermark_new_small';
		}
		else if ($row['is_best'] != 0) {
			$watermark_img = 'watermark_best_small';
		}
		else if ($row['is_hot'] != 0) {
			$watermark_img = 'watermark_hot_small';
		}

		if ($watermark_img != '') {
			$arr[$row['goods_id']]['watermark_img'] = $watermark_img;
		}

		$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];

		if ($display == 'grid') {
			$arr[$row['goods_id']]['goods_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		}
		else {
			$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
		}

		$arr[$row['goods_id']]['name'] = $row['goods_name'];
		$arr[$row['goods_id']]['goods_brief'] = $row['goods_brief'];
		$arr[$row['goods_id']]['sales_volume'] = $row['sales_volume'];
		$arr[$row['goods_id']]['comments_number'] = $row['comments_number'];
		$arr[$row['goods_id']]['is_promote'] = $row['is_promote'];

		if (0 < $row['market_price']) {
			$discount_arr = get_discount($row['goods_id']);
		}

		$arr[$row['goods_id']]['zhekou'] = $discount_arr['discount'];
		$arr[$row['goods_id']]['jiesheng'] = $discount_arr['jiesheng'];
		$arr[$row['goods_id']]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
		$goods_id = $row['goods_id'];
		$count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' where id_value =\'' . $goods_id . '\' AND status = 1 AND parent_id = 0');
		$arr[$row['goods_id']]['review_count'] = $count;
		$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
		$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$arr[$row['goods_id']]['type'] = $row['goods_type'];
		$arr[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_thumb']);
		$arr[$row['goods_id']]['goods_img'] = get_image_path($row['goods_img']);
		$arr[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);

		if ($row['model_attr'] == 1) {
			$table_products = 'products_warehouse';
			$type_files = ' and warehouse_id = \'' . $warehouse_id . '\'';
		}
		else if ($row['model_attr'] == 2) {
			$table_products = 'products_area';
			$type_files = ' and area_id = \'' . $area_id . '\'';
		}
		else {
			$table_products = 'products';
			$type_files = '';
		}

		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . ' WHERE goods_id = \'' . $row['goods_id'] . '\'' . $type_files . ' LIMIT 0, 1';
		$arr[$row['goods_id']]['prod'] = $GLOBALS['db']->getRow($sql);

		if (empty($prod)) {
			$arr[$row['goods_id']]['prod'] = 1;
		}
		else {
			$arr[$row['goods_id']]['prod'] = 0;
		}

		$arr[$row['goods_id']]['goods_number'] = $row['goods_number'];
		$sql = 'select * from ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' where ru_id=\'' . $row['user_id'] . '\'';
		$basic_info = $GLOBALS['db']->getRow($sql);
		$arr[$row['goods_id']]['kf_type'] = $basic_info['kf_type'];
		$arr[$row['goods_id']]['kf_ww'] = $basic_info['kf_ww'];
		$arr[$row['goods_id']]['kf_qq'] = $basic_info['kf_qq'];
		$arr[$row['goods_id']]['rz_shopName'] = get_shop_name($row['user_id'], 1);
		$arr[$row['goods_id']]['user_id'] = $row['user_id'];
		$arr[$row['goods_id']]['store_url'] = build_uri('merchants_store', array('urid' => $row['user_id']), $arr[$row['goods_id']]['rz_shopName']);
		$arr[$row['goods_id']]['count'] = selled_count($row['goods_id']);
		$mc_all = ments_count_all($row['goods_id']);
		$mc_one = ments_count_rank_num($row['goods_id'], 1);
		$mc_two = ments_count_rank_num($row['goods_id'], 2);
		$mc_three = ments_count_rank_num($row['goods_id'], 3);
		$mc_four = ments_count_rank_num($row['goods_id'], 4);
		$mc_five = ments_count_rank_num($row['goods_id'], 5);
		$arr[$row['goods_id']]['zconments'] = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);
	}

	return array('list' => array_values($arr), 'totalpage' => ceil($total / $size));
}

function get_drp_config_commission()
{
	$sql = 'SELECT value  FROM ' . $GLOBALS['ecs']->table('drp_config') . ' WHERE code = \'commission\' ';
	$ischeck = $GLOBALS['db']->getOne($sql);
	return $ischeck;
}

function drp_get_child_tree($tree_id = 0, $shop_id = 0)
{
	$three_arr = array();
	$where = '';
	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE parent_id = \'' . $tree_id . '\' AND is_show = 1' . $where;
	if ($GLOBALS['db']->getOne($sql) || ($tree_id == 0)) {
		$child_sql = 'SELECT c.cat_id, c.cat_name, c.parent_id, c.cat_alias_name, c.is_show, (SELECT goods_thumb FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE cat_id = c.cat_id AND is_on_sale = 1 AND is_delete = 0 ORDER BY sort_order ASC, goods_id DESC limit 1 ) as goods_thumb ' . ' FROM ' . $GLOBALS['ecs']->table('category') . ' c' . ' WHERE c.parent_id = \'' . $tree_id . '\' AND c.is_show = 1 ' . $where . ' ORDER BY c.sort_order ASC, c.cat_id ASC';
		$res = $GLOBALS['db']->getAll($child_sql);

		if (empty($shop_id)) {
			$type = drp_type($_SESSION['user_id']);

			if ($type == 1) {
				$catid = drp_type_cat($_SESSION['user_id'], $type);

				foreach ($catid as $key => $vo) {
					$cat_id[$key] = $vo['cat_id'];
				}
			}
		}

		foreach ($res as $k => $row) {
			if ($row['is_show']) {
				$three_arr[$k]['id'] = $row['cat_id'];
				$three_arr[$k]['name'] = $row['cat_alias_name'] ? $row['cat_alias_name'] : $row['cat_name'];
				$three_arr[$k]['url'] = url('drp/user/drpgoodslist/', array('id' => $row['cat_id']));
				$three_arr[$k]['shop_url'] = url('drp/shop/index/', array('cat_id' => $row['cat_id'], 'id' => $shop_id));
				$three_arr[$k]['cat_img'] = get_image_path($row['goods_thumb']);
				$three_arr[$k]['haschild'] = 0;

				if ($cat_id) {
					if (in_array($row['cat_id'], $cat_id)) {
						$three_arr[$k]['is_drp'] = 1;
					}
					else {
						$three_arr[$k]['is_drp'] = 0;
					}
				}
			}

			if (0 < $tree_id) {
				if (isset($row['cat_id'])) {
					$child_tree = drp_get_child_tree($row['cat_id'], $shop_id);

					if ($child_tree) {
						$three_arr[$k]['cat_id'] = $child_tree;
						$three_arr[$k]['haschild'] = 1;
					}
				}
			}
		}
	}

	return $three_arr;
}

function parent_name($parent_id = 0)
{
	$sql = 'SELECT user_name  FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $parent_id . '\' ';
	$res = $GLOBALS['db']->getRow($sql);
	return $res['user_name'];
}

function drp_type($user_id = 0)
{
	$sql = 'SELECT type  FROM ' . $GLOBALS['ecs']->table('drp_shop') . ' WHERE user_id = \'' . $user_id . '\' ';
	$res = $GLOBALS['db']->getRow($sql);
	return $res['type'];
}

function drp_type_goods($user_id = 0, $type = 0)
{
	$sql = 'SELECT goods_id FROM ' . $GLOBALS['ecs']->table('drp_type') . ' WHERE user_id = ' . $user_id . ' and type = ' . $type;
	$catid = $GLOBALS['db']->getAll($sql);
	return $catid;
}

function drp_type_cat($user_id = 0, $type = 0)
{
	$sql = 'SELECT cat_id FROM ' . $GLOBALS['ecs']->table('drp_type') . ' WHERE user_id = ' . $user_id . ' and type = ' . $type;
	$catid = $GLOBALS['db']->getAll($sql);
	return $catid;
}

function drp_transfer_log_change($user_id = 0, $amount = 0)
{
	$time = gmtime();
	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('drp_transfer_log') . ' (user_id, money,add_time)' . ' VALUES (\'' . $user_id . '\',\'' . $amount . '\', \'' . $time . '\')';
	$GLOBALS['db']->query($sql);
}

function drp_credit_info_all()
{
	$sql = 'SELECT id,credit_name,min_money,max_money FROM ' . $GLOBALS['ecs']->table('drp_user_credit') . '  ORDER BY min_money ASC LIMIT 3';
	$credit_info_all = $GLOBALS['db']->getAll($sql);
	return $credit_info_all;
}

function drp_rank_info($user_id = 0)
{
	$drp_info = dao('drp_shop')->field('credit_id')->where(array('user_id' => $user_id))->find();

	if (0 < $drp_info['credit_id']) {
		$rank_info = dao('drp_user_credit')->field('id,credit_name,min_money,max_money')->where(array('id' => $drp_info['credit_id']))->find();
	}
	else {
		$sql = 'SELECT sum(goods_price) as totals FROM ' . $GLOBALS['ecs']->table('order_goods') . ' o' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('drp_affiliate_log') . ' a ON o.order_id = a.order_id' . ' WHERE a.separate_type != -1 and a.user_id = ' . $user_id;
		$goods_price = $GLOBALS['db']->getOne($sql);
		$goods_price = ($goods_price ? $goods_price : 0);
		$sql = 'SELECT id,credit_name,min_money,max_money FROM ' . $GLOBALS['ecs']->table('drp_user_credit') . ' WHERE min_money <= \'' . $goods_price . '\' and max_money >\'' . $goods_price . '\'  ORDER BY min_money ASC LIMIT 1';
		$rank_info = $GLOBALS['db']->getRow($sql);
	}

	return $rank_info;
}

function is_only($field = '', $value, $id = 0, $where = '')
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('drp_user_credit') . ' WHERE ' . $field . ' = \'' . $value . '\'';
	$sql .= (empty($id) ? '' : ' AND id' . ' <> \'' . $id . '\'');
	$sql .= (empty($where) ? '' : ' AND ' . $where);
	return $GLOBALS['db']->getOne($sql) == 0;
}

function get_qrcode_config()
{
	$config = array();
	$config = dao('drp_config')->where(array('code' => 'qrcode'))->getField('value');

	if (!empty($config)) {
		$config = unserialize($config);
		$config['backbround'] = !empty($config['backbround']) ? $config['backbround'] : 'data/attached/qrcode/drp_bg.png';
		$config['qr_position']['left'] = isset($config['qr_position']['left']) ? $config['qr_position']['left'] : '170';
		$config['qr_position']['top'] = isset($config['qr_position']['top']) ? $config['qr_position']['top'] : '300';
		$config['av_position']['left'] = isset($config['av_position']['left']) ? $config['av_position']['left'] : '100';
		$config['av_position']['top'] = isset($config['av_position']['top']) ? $config['av_position']['top'] : '24';
		$config['text']['color'] = !empty($config['text']['color']) ? $config['text']['color'] : '#000000';
	}

	return $config;
}


?>
