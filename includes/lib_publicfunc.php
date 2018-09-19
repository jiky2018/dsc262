<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_warehouse_area_attr_price_insert($warehouse_area, $goods_id, $goods_attr_id, $table)
{
	$arr = array();

	if (is_array($warehouse_area)) {
		for ($i = 0; $i < count($warehouse_area); $i++) {
			if (!empty($warehouse_area[$i])) {
				$parent = array('goods_id' => $goods_id, 'goods_attr_id' => $goods_attr_id);

				if ($table == 'warehouse_attr') {
					$where = ' AND warehouse_id = \'' . $warehouse_area[$i] . '\'';
					$parent['warehouse_id'] = $warehouse_area[$i];
					$parent['attr_price'] = $_POST['attr_price_' . $warehouse_area[$i]];
				}
				else if ($table == 'warehouse_area_attr') {
					$where = ' AND area_id = \'' . $warehouse_area[$i] . '\'';
					$parent['area_id'] = $warehouse_area[$i];
					$parent['attr_price'] = $_POST['attrPrice_' . $warehouse_area[$i]];
				}

				if ($goods_id) {
					$admin_id = get_admin_id();
					$where .= ' AND admin_id = \'' . $admin_id . '\'';
				}

				$sql = 'SELECT id FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE goods_id = \'' . $goods_id . '\' AND goods_attr_id = \'' . $goods_attr_id . '\' ' . $where);
				$id = $GLOBALS['db']->getOne($sql);

				if (0 < $id) {
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $parent, 'UPDATE', 'goods_id = \'' . $goods_id . '\' and goods_attr_id = \'' . $goods_attr_id . '\' ' . $where);
				}
				else {
					$parent['admin_id'] = $admin_id;
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $parent, 'INSERT');
				}
			}
		}
	}
	else if (is_array($goods_attr_id)) {
		for ($i = 0; $i < count($goods_attr_id); $i++) {
			if (!empty($goods_attr_id[$i])) {
				$parent = array('goods_id' => $goods_id, 'goods_attr_id' => $goods_attr_id[$i]);

				if ($table == 'warehouse_attr') {
					$where = ' AND warehouse_id = \'' . $warehouse_area . '\'';
					$parent['warehouse_id'] = $warehouse_area;
					$parent['attr_price'] = $_POST['attr_price_' . $goods_attr_id[$i]];
				}
				else if ($table == 'warehouse_area_attr') {
					$where = ' AND area_id = \'' . $warehouse_area . '\'';
					$parent['area_id'] = $warehouse_area;
					$parent['attr_price'] = $_POST['attrPrice_' . $goods_attr_id[$i]];
				}

				if ($goods_id) {
					$admin_id = get_admin_id();
					$where .= ' AND admin_id = \'' . $admin_id . '\'';
				}

				$sql = 'SELECT id FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE goods_id = \'' . $goods_id . '\' AND goods_attr_id = \'') . $goods_attr_id[$i] . ('\' ' . $where);
				$id = $GLOBALS['db']->getOne($sql);

				if (0 < $id) {
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $parent, 'UPDATE', 'goods_id = \'' . $goods_id . '\' and goods_attr_id = \'' . $goods_attr_id[$i] . ('\' ' . $where));
				}
				else {
					$parent['admin_id'] = $admin_id;
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $parent, 'INSERT');
				}
			}
		}
	}
}

function get_seller_grade_rank($ru_id)
{
	$sql = 'SELECT sg.goods_sun, sg.seller_temp, sg.favorable_rate, sg.give_integral, sg.rank_integral, sg.pay_integral FROM ' . $GLOBALS['ecs']->table('merchants_grade') . ' AS mg, ' . $GLOBALS['ecs']->table('seller_grade') . ' AS sg ' . (' WHERE mg.grade_id = sg.id AND ru_id = \'' . $ru_id . '\' LIMIT 1');
	$res = $GLOBALS['db']->getRow($sql);
	$res['favorable_rate'] = !empty($res['favorable_rate']) ? $res['favorable_rate'] / 100 : 1;
	$res['give_integral'] = !empty($res['give_integral']) ? $res['give_integral'] / 100 : 1;
	$res['rank_integral'] = !empty($res['rank_integral']) ? $res['rank_integral'] / 100 : 1;
	$res['pay_integral'] = !empty($res['pay_integral']) ? $res['pay_integral'] / 100 : 1;
	return $res;
}

function get_account_log_list($ru_id, $type = 0)
{
	require_once ROOT_PATH . 'includes/lib_order.php';
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = !isset($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['order_sn'] = !isset($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
		$filter['out_up'] = !isset($_REQUEST['out_up']) ? 0 : intval($_REQUEST['out_up']);
		$filter['log_type'] = !isset($_REQUEST['log_type']) ? 0 : intval($_REQUEST['log_type']);
		$filter['handler'] = !isset($_REQUEST['handler']) ? 0 : intval($_REQUEST['handler']);
		$filter['rawals'] = !isset($_REQUEST['rawals']) ? 0 : intval($_REQUEST['rawals']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'sal.log_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['act_type'] = !isset($_REQUEST['act_type']) ? 'detail' : $_REQUEST['act_type'];
		$filter['ru_id'] = !isset($_REQUEST['ru_id']) ? $ru_id : intval($_REQUEST['ru_id']);
		$ex_where = ' WHERE 1 ';

		if ($filter['order_sn']) {
			$ex_where .= ' AND (sal.apply_sn = \'' . $filter['order_sn'] . '\'';
			$ex_where .= ' OR ';
			$ex_where .= ' (SELECT order_sn FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi WHERE sal.order_id = oi.order_id LIMIT 1) = \'' . $filter['order_sn'] . '\')';
		}

		if ($filter['out_up']) {
			if ($filter['out_up'] != 4) {
				if ($filter['out_up'] == 3) {
					$ex_where .= ' AND sal.log_type = \'' . $filter['out_up'] . '\'';
				}

				$ex_where .= ' AND (sal.log_type > \'' . $filter['out_up'] . '\' OR sal.log_type =  \'' . $filter['out_up'] . '\')';
			}
			else {
				$ex_where .= ' AND sal.log_type = \'' . $filter['out_up'] . '\'';
			}
		}

		if ($filter['rawals'] == 1) {
			$type = array(1);
		}

		if ($filter['handler']) {
			if ($filter['handler'] == 1) {
				$ex_where .= ' AND sal.is_paid = 1';
			}
			else {
				$ex_where .= ' AND sal.is_paid = 0';
			}
		}

		if ($filter['log_type']) {
			$ex_where .= ' AND sal.log_type = \'' . $filter['log_type'] . '\'';
		}

		$filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_where = '';
		$store_search_where = '';

		if ($filter['store_search'] != 0) {
			if ($ru_id == 0) {
				if ($_REQUEST['store_type']) {
					$store_search_where = 'AND mis.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
				}

				if ($filter['store_search'] == 1) {
					$ex_where .= ' AND mis.user_id = \'' . $filter['merchant_id'] . '\' ';
				}
				else if ($filter['store_search'] == 2) {
					$store_where .= ' AND mis.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
				}
				else if ($filter['store_search'] == 3) {
					$store_where .= ' AND mis.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
				}

				if (1 < $filter['store_search']) {
					$ex_where .= ' AND mis.user_id > 0 ' . $store_where . ' ';
				}
			}
		}

		$type = implode(',', $type);

		if ($filter['ru_id']) {
			$ex_where .= ' AND sal.ru_id = \'' . $filter['ru_id'] . '\'';
		}

		$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('seller_account_log') . ' AS sal ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS mis ON sal.ru_id = mis.user_id ' . (' ' . $ex_where . ' AND sal.log_type IN(' . $type . ')');
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT sal.* FROM ' . $GLOBALS['ecs']->table('seller_account_log') . ' AS sal ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS mis ON sal.ru_id = mis.user_id ' . (' ' . $ex_where . ' AND sal.log_type IN(' . $type . ')') . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	for ($i = 0; $i < count($res); $i++) {
		$res[$i]['shop_name'] = get_shop_name($res[$i]['ru_id'], 1);
		$order = order_info($res[$i]['order_id']);
		$res[$i]['order_sn'] = !empty($order['order_sn']) ? '【订单】' . $order['order_sn'] : $res[$i]['apply_sn'];
		$res[$i]['amount'] = price_format($res[$i]['amount'], false);
		$res[$i]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $res[$i]['add_time']);
		$res[$i]['payment_info'] = payment_info($res[$i]['pay_id']);
		$res[$i]['apply_sn'] = sprintf($_LANG['01_apply_sn'], $res[$i]['apply_sn']);
	}

	$arr = array('log_list' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_account_log_info($log_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('seller_account_log') . (' WHERE log_id = \'' . $log_id . '\' LIMIT 1');
	$res = $GLOBALS['db']->getRow($sql);

	if ($res) {
		$res['shop_name'] = get_shop_name($res['ru_id'], 1);
		$res['payment_info'] = payment_info($res['pay_id']);
		$info = get_seller_shopinfo($res['ru_id'], array('seller_money', 'frozen_money'));
		$res['seller_money'] = $info['seller_money'];
		$res['seller_frozen'] = $info['frozen_money'];
	}

	return $res;
}

function get_seller_category()
{
	$sql = 'SELECT c.*, (SELECT c2.cat_name FROM ' . $GLOBALS['ecs']->table('category') . ' AS c2 WHERE c2.cat_id = c.parent_id LIMIT 1) AS parent_name ' . ' FROM ' . $GLOBALS['ecs']->table('merchants_category') . ' AS mc,' . $GLOBALS['ecs']->table('category') . ' AS c ' . ' WHERE 1 AND mc.cat_id = c.cat_id';
	$res = $GLOBALS['db']->getAll($sql);
	$chid_level = 0;
	$level = 1;
	$arr = array();

	if ($res) {
		foreach ($res as $key => $row) {
			$arr[$key]['cat_id'] = $row['cat_id'];
			$arr[$key]['cat_name'] = $row['cat_name'];
			$arr[$key]['parent_id'] = $row['parent_id'];
			$arr[$key]['keywords'] = $row['keywords'];
			$arr[$key]['cat_desc'] = $row['cat_desc'];
			$arr[$key]['sort_order'] = $row['sort_order'];
			$arr[$key]['measure_unit'] = $row['measure_unit'];
			$arr[$key]['show_in_nav'] = $row['show_in_nav'];
			$arr[$key]['style'] = $row['style'];
			$arr[$key]['grade'] = $row['grade'];
			$arr[$key]['filter_attr'] = $row['filter_attr'];
			$arr[$key]['is_top_style'] = $row['is_top_style'];
			$arr[$key]['top_style_tpl'] = $row['top_style_tpl'];
			$arr[$key]['cat_icon'] = $row['cat_icon'];
			$arr[$key]['is_top_show'] = $row['is_top_show'];
			$arr[$key]['category_links'] = $row['category_links'];
			$arr[$key]['category_topic'] = $row['category_topic'];
			$arr[$key]['pinyin_keyword'] = $row['pinyin_keyword'];
			$arr[$key]['cat_alias_name'] = $row['cat_alias_name'];
			$arr[$key]['template_file'] = $row['template_file'];
			$arr[$key]['parent_name'] = $row['parent_name'];
			$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('merchants_category') . ' WHERE cat_id = \'' . $row['parent_id'] . '\'';

			if ($GLOBALS['db']->getOne($sql, true)) {
				$cat_level = get_seller_cat_level($row['parent_id']);

				if ($cat_level['parent_id'] != 0) {
					$chid = get_seller_cat_level($cat_level['parent_id']);

					if ($chid) {
						$chid_level += 1;
					}
				}

				$arr[$key]['level'] = $level + $chid_level;
			}
			else {
				$arr[$key]['level'] = 0;
			}

			$cat_level = array('一', '二', '三', '四', '五', '六', '气', '八', '九', '十');
			$arr[$key]['belongs'] = $cat_level[$arr[$key]['level']] . '级';

			if ($arr[$key]['level'] == 0) {
				$row['parent_id'] = 0;
			}
		}
	}

	return $arr;
}

function get_seller_cat_level($parent_id = 0, $level = 1)
{
	$sql = 'SELECT c.cat_id, c.cat_name, c.parent_id FROM ' . $GLOBALS['ecs']->table('merchants_category') . ' AS mc,' . $GLOBALS['ecs']->table('category') . ' AS c' . (' WHERE mc.cat_id = c.cat_id AND c.cat_id = \'' . $parent_id . '\' LIMIT 1');
	$row = $GLOBALS['db']->getRow($sql);
	return $row;
}

function get_seller_select_category($cat_id = 0, $relation = 0, $self = true, $user_id = 0)
{
	static $cat_list = array();
	$cat_list[] = intval($cat_id);

	if ($user_id) {
		$where = ' AND user_id = \'' . $user_id . '\'';
	}

	if ($relation == 0) {
		return $cat_list;
	}
	else if ($relation == 1) {
		$sql = ' select parent_id from ' . $GLOBALS['ecs']->table('merchants_category') . ' where cat_id=\'' . $cat_id . ('\' ' . $where);
		$parent_id = $GLOBALS['db']->getOne($sql);

		if (!empty($parent_id)) {
			get_seller_select_category($parent_id, $relation, $self, $user_id);
		}

		if ($self == false) {
			unset($cat_list[0]);
		}

		$cat_list[] = 0;
		return array_reverse(array_unique($cat_list));
	}
	else if ($relation == 2) {
		$sql = ' select cat_id from ' . $GLOBALS['ecs']->table('merchants_category') . ' where parent_id=\'' . $cat_id . ('\' ' . $where);
		$child_id = $GLOBALS['db']->getCol($sql);

		if (!empty($child_id)) {
			foreach ($child_id as $key => $val) {
				get_seller_select_category($val, $relation, $self, $user_id);
			}
		}

		if ($self == false) {
			unset($cat_list[0]);
		}

		return $cat_list;
	}
}

function get_seller_category_list($cat_id = 0, $relation = 0, $user_id = 0)
{
	$where = '';

	if ($user_id) {
		$where .= ' AND user_id = \'' . $user_id . '\'';
	}

	if ($relation == 0) {
		$parent_id = $GLOBALS['db']->getOne(' SELECT parent_id FROM ' . $GLOBALS['ecs']->table('merchants_category') . (' WHERE cat_id = \'' . $cat_id . '\' ' . $where));
	}
	else if ($relation == 1) {
		$parent_id = $GLOBALS['db']->getOne(' SELECT parent_id FROM ' . $GLOBALS['ecs']->table('merchants_category') . (' WHERE cat_id = \'' . $cat_id . '\' ' . $where));
	}
	else if ($relation == 2) {
		$parent_id = $cat_id;
	}

	$parent_id = empty($parent_id) ? 0 : $parent_id;
	$category_list = $GLOBALS['db']->getAll(' SELECT cat_id, cat_name FROM ' . $GLOBALS['ecs']->table('merchants_category') . (' WHERE parent_id = \'' . $parent_id . '\' ' . $where));

	foreach ($category_list as $key => $val) {
		if ($cat_id == $val['cat_id']) {
			$is_selected = 1;
		}
		else {
			$is_selected = 0;
		}

		$category_list[$key]['is_selected'] = $is_selected;
	}

	return $category_list;
}

function set_default_filter($goods_id = 0, $cat_id = 0, $user_id = 0, $cat_type_show = 0, $table = 'category')
{
	if ($cat_id) {
		$parent_cat_list = get_select_category($cat_id, 1, true, $user_id, $table);
		$filter_category_navigation = get_array_category_info($parent_cat_list, $table);
		$GLOBALS['smarty']->assign('filter_category_navigation', $filter_category_navigation);
	}

	if ($user_id) {
		$seller_shop_cat = seller_shop_cat($user_id);
	}
	else {
		$seller_shop_cat = array();
	}

	$GLOBALS['smarty']->assign('table', $table);
	$GLOBALS['smarty']->assign('filter_category_list', get_category_list($cat_id, 0, $seller_shop_cat, $user_id, 2, $table));
	$GLOBALS['smarty']->assign('filter_brand_list', search_brand_list($goods_id, $user_id));
	$GLOBALS['smarty']->assign('cat_type_show', $cat_type_show);
	return true;
}

function set_seller_default_filter($goods_id = 0, $cat_id = 0, $user_id = 0)
{
	if (0 < $cat_id) {
		$seller_parent_cat_list = get_seller_select_category($cat_id, 1, true, $user_id);
		$seller_filter_category_navigation = get_seller_array_category_info($seller_parent_cat_list);
		$GLOBALS['smarty']->assign('seller_filter_category_navigation', $seller_filter_category_navigation);
	}

	$GLOBALS['smarty']->assign('seller_filter_category_list', get_seller_category_list($cat_id, 0, $user_id));
	$GLOBALS['smarty']->assign('seller_cat_type_show', 1);
	return true;
}

function get_seller_every_category($cat_id = 0)
{
	$parent_cat_list = get_seller_category_array($cat_id, 1, true);
	$filter_category_navigation = get_seller_array_category_info($parent_cat_list);
	$cat_nav = '';

	if ($filter_category_navigation) {
		foreach ($filter_category_navigation as $key => $val) {
			if ($key == 0) {
				$cat_nav .= $val['cat_name'];
			}
			else if (0 < $key) {
				$cat_nav .= ' > ' . $val['cat_name'];
			}
		}
	}

	return $cat_nav;
}

function get_seller_category_array($cat_id = 0, $relation = 0, $self = true)
{
	$cat_list[] = intval($cat_id);

	if ($relation == 0) {
		return $cat_list;
	}
	else if ($relation == 1) {
		do {
			$sql = ' select parent_id from ' . $GLOBALS['ecs']->table('merchants_category') . ' where cat_id=\'' . $cat_id . '\' ';
			$parent_id = $GLOBALS['db']->getOne($sql);

			if (!empty($parent_id)) {
				$cat_list[] = $parent_id;
				$cat_id = $parent_id;
			}
		} while (!empty($parent_id));

		if ($self == false) {
			unset($cat_list[0]);
		}

		$cat_list[] = 0;
		return array_reverse(array_unique($cat_list));
	}
	else if ($relation == 2) {
	}
}

function get_seller_array_category_info($arr = array())
{
	if ($arr) {
		$sql = ' SELECT cat_id, cat_name FROM ' . $GLOBALS['ecs']->table('merchants_category') . ' WHERE cat_id ' . db_create_in($arr);
		return $GLOBALS['db']->getAll($sql);
	}
	else {
		return false;
	}
}

function seller_shop_cat($user_id = 0)
{
	$seller_shop_cat = '';

	if ($user_id) {
		$sql = 'SELECT user_shopMain_category FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . (' WHERE user_id = \'' . $user_id . '\'');
		$seller_shop_cat = $GLOBALS['db']->getOne($sql, true);
	}

	$arr = array();
	$arr['parent'] = '';

	if ($seller_shop_cat) {
		$seller_shop_cat = explode('-', $seller_shop_cat);

		foreach ($seller_shop_cat as $key => $row) {
			if ($row) {
				$cat = explode(':', $row);
				$arr[$key]['cat_id'] = $cat[0];
				$arr[$key]['cat_tree'] = $cat[1];
				$arr['parent'] .= $cat[0] . ',';

				if ($cat[1]) {
					$arr['parent'] .= $cat[1] . ',';
				}
			}
		}
	}

	$arr['parent'] = substr($arr['parent'], 0, -1);
	return $arr;
}

function get_seller_cat_info($cat_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('merchants_category') . (' WHERE cat_id = \'' . $cat_id . '\' LIMIT 1');
	$row = $GLOBALS['db']->getRow($sql);

	if ($row) {
		$row['is_show_merchants'] = $row['is_show'];
	}

	return $row;
}

function get_admin_goods_info($goods_id = 0, $select = array(), $table = 'goods')
{
	if ($select && is_array($select)) {
		$select = implode(',', $select);
	}
	else {
		$select = '*';
	}

	$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE goods_id = \'' . $goods_id . '\' LIMIT 1');
	$row = $GLOBALS['db']->getRow($sql);

	if ($row) {
		if (isset($row['user_cat']) && !empty($row['user_cat'])) {
			$cat_info = get_seller_cat_info($row['user_cat']);
			$row['user_cat_name'] = $cat_info['cat_name'];
		}

		if (isset($row['user_id'])) {
			$row['shop_name'] = get_shop_name($row['user_id'], 1);
		}
	}

	return $row;
}

function get_every_category($cat_id = 0, $table = 'category')
{
	$parent_cat_list = get_category_array($cat_id, 1, true, $table);
	$filter_category_navigation = get_array_category_info($parent_cat_list, $table);
	$cat_nav = '';

	if ($filter_category_navigation) {
		foreach ($filter_category_navigation as $key => $val) {
			if ($table == 'goods_type_cat') {
				$cat_nav = $val['cat_name'];
			}
			else if ($key == 0) {
				$cat_nav .= $val['cat_name'];
			}
			else if (0 < $key) {
				$cat_nav .= ' > ' . $val['cat_name'];
			}
		}
	}

	return $cat_nav;
}

function get_category_array($cat_id = 0, $relation = 0, $self = true, $table = 'category')
{
	$cat_list[] = intval($cat_id);

	if ($relation == 0) {
		return $cat_list;
	}
	else if ($relation == 1) {
		do {
			$sql = ' SELECT parent_id FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE cat_id=\'' . $cat_id . '\' ');
			$parent_id = $GLOBALS['db']->getOne($sql);

			if (!empty($parent_id)) {
				$cat_list[] = $parent_id;
				$cat_id = $parent_id;
			}
		} while (!empty($parent_id));

		if ($self == false) {
			unset($cat_list[0]);
		}

		$cat_list[] = 0;
		return array_reverse(array_unique($cat_list));
	}
	else if ($relation == 2) {
	}
}

function get_array_category_info($arr = array(), $table = 'category')
{
	if ($arr) {
		$arr = get_del_str_comma($arr);
		$select = '';

		if ($table == 'category') {
			$select = ' , cate_description, cate_title, cate_keywords ';
		}

		$sql = ' SELECT cat_id, cat_name ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE cat_id ' . db_create_in($arr);
		$category_list = $GLOBALS['db']->getAll($sql);

		foreach ($category_list as $key => $val) {
			$category_list[$key]['url'] = build_uri($table, array('cid' => $val['cat_id']), $val['cat_name']);
		}

		return $category_list;
	}
	else {
		return false;
	}
}

function get_add_edit_goods_cat_list($goods_id = 0, $cat_id = 0, $table = 'category', $sin_prefix = '', $user_id = 0, $seller_shop_cat = array())
{
	if (empty($sin_prefix)) {
		$select_category_rel = '';
		$select_category_rel .= insert_select_category(0, 0, 0, 'cat_id1', 1, $table, $seller_shop_cat);
		$GLOBALS['smarty']->assign($sin_prefix . 'select_category_rel', $select_category_rel);
	}

	if (empty($sin_prefix)) {
		$select_category_pak = '';
		$select_category_pak .= insert_select_category(0, 0, 0, 'cat_id2', 1, $table, $seller_shop_cat);
		$GLOBALS['smarty']->assign($sin_prefix . 'select_category_pak', $select_category_pak);
	}

	if ($_REQUEST['act'] == 'add') {
		$select_category_html = '';

		if ($sin_prefix) {
			$select_category_html .= insert_seller_select_category(0, 0, 0, 'user_cat', 0, $table, array(), $user_id);
		}
		else {
			$select_category_html .= insert_select_category(0, 0, 0, 'cat_id', 0, $table, $seller_shop_cat);
		}

		$GLOBALS['smarty']->assign($sin_prefix . 'select_category_html', $select_category_html);
	}
	else {
		if ($_REQUEST['act'] == 'edit' || $_REQUEST['act'] == 'copy') {
			$goods = get_admin_goods_info($goods_id, array('cat_id', 'user_cat'));
			$select_category_html = '';

			if ($sin_prefix) {
				$parent_cat_list = get_seller_select_category($cat_id, 1, true, $user_id);
				$cat_id = $goods['user_cat'];
			}
			else {
				$parent_cat_list = get_select_category($cat_id, 1, true);
				$cat_id = $goods['cat_id'];
			}

			for ($i = 0; $i < count($parent_cat_list); $i++) {
				if ($sin_prefix) {
					$select_category_html .= insert_seller_select_category(pos($parent_cat_list), next($parent_cat_list), $i, 'user_cat', 0, $table, array(), $user_id);
				}
				else {
					$select_category_html .= insert_select_category(pos($parent_cat_list), next($parent_cat_list), $i, 'cat_id', 0, $table, $seller_shop_cat);
				}
			}

			$GLOBALS['smarty']->assign($sin_prefix . 'select_category_html', $select_category_html);
			$parent_and_rank = empty($cat_id) ? '0_0' : $cat_id . '_' . (count($parent_cat_list) - 2);
			$GLOBALS['smarty']->assign($sin_prefix . 'parent_and_rank', $parent_and_rank);
		}
	}
}

function get_admin_user_info($id = 0)
{
	$sql = 'SELECT u.user_id, u.email, u.user_name, u.user_money, u.mobile_phone, u.pay_points, nick_name' . ' FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . (' WHERE u.user_id = \'' . $id . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function get_dialog_goods_attr_type($attr_id = 0, $goods_id = 0)
{
	$sql = 'SELECT goods_attr_id, attr_id, attr_value FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE attr_id = \'' . $attr_id . '\' AND goods_id = \'' . $goods_id . '\' ORDER BY attr_sort');
	$res = $GLOBALS['db']->getAll($sql);

	if ($res) {
		foreach ($res as $key => $row) {
			if ($goods_id) {
				$res[$key]['is_selected'] = 1;
			}
			else {
				$res[$key]['is_selected'] = 0;
			}
		}
	}

	return $res;
}

function seller_grade_list()
{
	$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE merchants_audit = 1 ORDER BY user_id ASC';
	return $GLOBALS['db']->getAll($sql);
}

function get_pin_regions()
{
	$arr = array();
	$letters = range('A', 'Z');
	$pin_regions = read_static_cache('pin_regions', '/data/sc_file/');

	if ($pin_regions !== false) {
		foreach ($letters as $key => $row) {
			foreach ($pin_regions as $pk => $prow) {
				if ($row == $prow['initial']) {
					$arr[$row][$pk] = $prow;
				}
			}

			if ($arr[$row]) {
				$arr[$row] = get_array_sort($arr[$row], 'region_id');
			}
		}
	}

	ksort($arr);
	return $arr;
}

function get_updel_goods_attr($goods_id = 0)
{
	$admin_id = get_admin_id();

	if ($admin_id) {
		if ($goods_id) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods_attr') . (' SET goods_id = \'' . $goods_id . '\' WHERE admin_id = \'' . $admin_id . '\' AND goods_id = 0');
		}
		else {
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE admin_id = \'' . $admin_id . '\' AND goods_id = 0');
		}

		$GLOBALS['db']->query($sql);
	}
}

function get_goods_attr_nameId($goods_id = 0, $attr_id = 0, $attr_value = '', $select = 'goods_attr_id', $type = 0)
{
	if ($type == 1) {
		$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE goods_id = \'' . $goods_id . '\' AND goods_attr_id = \'' . $attr_id . '\'');
	}
	else {
		$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE goods_id = \'' . $goods_id . '\' AND attr_id = \'' . $attr_id . '\' ') . (' AND attr_value = \'' . $attr_value . '\'');
	}

	return $GLOBALS['db']->getOne($sql);
}

function get_seller_region($region = array(), $ru_id = 0)
{
	if ($region) {
		$sql = 'SELECT concat(IFNULL(p.region_name, \'\'), \'\', IFNULL(t.region_name, \'\'), \'\', IFNULL(d.region_name, \'\')) AS region ' . 'FROM ' . $GLOBALS['ecs']->table('region') . ' AS p, ' . $GLOBALS['ecs']->table('region') . ' AS t, ' . $GLOBALS['ecs']->table('region') . ' AS d ' . 'WHERE p.region_id = \'' . $region['province'] . '\' AND t.region_id = \'' . $region['city'] . '\' AND d.region_id = \'' . $region['district'] . '\'';
	}
	else {
		$sql = 'SELECT concat(IFNULL(p.region_name, \'\'), \'\', IFNULL(t.region_name, \'\'), \'\', IFNULL(d.region_name, \'\'), \'\', IFNULL(s.region_name, \'\')) AS region ' . 'FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS ss ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS p ON ss.province = p.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS t ON ss.city = t.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS d ON ss.district = d.region_id ' . ('WHERE ss.ru_id = \'' . $ru_id . '\'');
	}

	return $GLOBALS['db']->getOne($sql);
}

function get_goods_unset_attr($goods_id = 0, $attr_arr = array())
{
	$arr = array();

	if ($attr_arr) {
		$where_select = array();

		if (empty($goods_id)) {
			$admin_id = get_admin_id();
			$where_select['admin_id'] = $admin_id;
		}

		$where_select['goods_id'] = $goods_id;

		foreach ($attr_arr as $key => $row) {
			if ($row) {
				$where_select['attr_value'] = $row[0];
				$attr_info = get_goods_attr_id($where_select, array('ga.goods_id', 'ga.attr_value', 'a.attr_id', 'a.attr_type'), 2, 1);
				if ($attr_info && $row[0] == $attr_info['attr_value']) {
					unset($row);
				}
				else {
					$arr[$key] = $row;
				}
			}
		}
	}

	return $arr;
}

function get_goods_transport_info($tid, $table = 'goods_transport')
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE tid = \'' . $tid . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function get_goods_extend($goods_id)
{
	$extend_sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('goods_extend') . (' WHERE goods_id = \'' . $goods_id . '\'');
	return $GLOBALS['db']->getRow($extend_sql);
}

function get_goods_gallery_album($type = 0, $id = 0, $select = array(), $id_name = 'album_id', $order = '')
{
	$where = 1;

	if ($id) {
		$where .= ' AND ' . $id_name . ' = \'' . $id . '\'';
	}

	if ($select && is_array($select)) {
		$select = implode(',', $select);
	}
	else {
		$select = '*';
	}

	if ($type == 2) {
		$where .= ' LIMIT 1';
	}

	$sql = 'SELECT ' . $select . ' FROM' . $GLOBALS['ecs']->table('gallery_album') . (' WHERE ' . $where . ' ' . $order);

	if ($type == 1) {
		$album_list = $GLOBALS['db']->getAll($sql);

		if ($album_list) {
			foreach ($album_list as $key => $row) {
				$album_list[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
			}
		}
	}
	else if ($type == 2) {
		$album_list = $GLOBALS['db']->getRow($sql);
	}
	else {
		$album_list = $GLOBALS['db']->getOne($sql, true);
	}

	return $album_list;
}

function gallery_pic_album($type = 0, $id = 0, $select = array(), $id_name = 'pic_id', $order = '')
{
	$where = 1;

	if ($id) {
		$where .= ' AND ' . $id_name . ' = \'' . $id . '\'';
	}

	if ($select && is_array($select)) {
		$select = implode(',', $select);
	}
	else {
		$select = '*';
	}

	if ($type == 2) {
		$where .= ' LIMIT 1';
	}

	$sql = 'SELECT ' . $select . ' FROM' . $GLOBALS['ecs']->table('pic_album') . (' WHERE ' . $where . ' ' . $order);

	if ($type == 1) {
		$pic_list = $GLOBALS['db']->getAll($sql);

		if ($pic_list) {
			foreach ($pic_list as $key => $row) {
				$pic_list[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
			}
		}
	}
	else if ($type == 2) {
		$pic_list = $GLOBALS['db']->getRow($sql);
	}
	else {
		$pic_list = $GLOBALS['db']->getOne($sql, true);
	}

	return $pic_list;
}

function seller_page($list, $nowpage, $show = '10')
{
	$arr = array();

	if ($list['page_count'] < $show) {
		$show = $list['page_count'];
	}

	if ($show % 2 == 0) {
		$begin = $nowpage - ceil($show / 2);
		$end = $nowpage + floor($show / 2);
	}
	else {
		$begin = $nowpage - floor($show / 2);
		$end = $nowpage + ceil($show / 2);
	}

	if (1 < $show) {
		if (ceil($show / 2) + 1 < $nowpage && $nowpage <= $list['page_count'] - ceil($show / 2)) {
			for ($i = $begin; $i < $end; $i++) {
				$arr[$i] = $i;
			}
		}
		else {
			if (ceil($show / 2) + 1 < $nowpage && $list['page_count'] - ($show - 1) < $nowpage) {
				for ($i = $list['page_count'] - ($show - 1); $i <= $list['page_count']; $i++) {
					$arr[$i] = $i;
				}
			}
			else {
				for ($i = 1; $i <= $show; $i++) {
					$arr[$i] = $i;
				}
			}
		}
	}
	else {
		$arr[1] = 1;
	}

	return $arr;
}

function get_choose_cat($ids)
{
	$sql = ' SELECT cat_id, cat_name FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE cat_id ' . db_create_in($ids);
	return $GLOBALS['db']->getAll($sql);
}

function get_choose_goods($ids)
{
	$sql = ' SELECT goods_id, goods_name FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id ' . db_create_in($ids);
	return $GLOBALS['db']->getAll($sql);
}

function get_areaRegion_info_list($ra_id)
{
	if (0 < $ra_id) {
		$where_raId = ' AND mr.ra_id = \'' . $ra_id . '\'';
	}

	$sql = 'SELECT rw.region_id, rw.region_name FROM ' . $GLOBALS['ecs']->table('merchants_region_info') . ' AS mr ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS r ON mr.region_id = r.region_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('region_warehouse') . ' AS rw ON r.region_id = rw.regionId' . ' where 1' . $where_raId;
	return $GLOBALS['db']->getAll($sql);
}

function get_table_info($table = '', $where = 1, $select = array())
{
	$res = array();

	if ($table) {
		if ($select && is_array($select)) {
			$select = implode(',', $select);
		}
		else {
			$select = empty($select) ? '*' : $select;
		}

		$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE ' . $where . ' LIMIT 1');
		$res = $GLOBALS['db']->getRow($sql);
	}

	return $res;
}

function get_order_drp_money($total_fee = 0, $ru_id = 0, $order_id = 0, $order = array())
{
	$should_amount = 0;
	$where = '';

	if ($order_id) {
		$where .= ' AND oi.order_id = \'' . $order_id . '\'';
	}
	else {
		$where .= order_query_sql('confirm_take', 'oi.');
		$where .= ' AND og.ru_id = \'' . $ru_id . '\'';
		$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi2 WHERE oi2.main_order_id = oi.order_id LIMIT 1) = 0';
	}

	$res = array();
	$sql = 'SELECT SUM(og.drp_money) AS drp_money FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON og.order_id = oi.order_id ' . ' WHERE 1 ' . $where;
	$order_drp = $GLOBALS['db']->getRow($sql);

	if ($order_id) {
		$goods_rate = get_alone_goods_rate($order_id, 0, $order);
		$res['rate_activity'] = isset($goods_rate['rate_activity']) ? $goods_rate['rate_activity'] : 0;

		if ($goods_rate) {
			$total_fee = $total_fee - $goods_rate['total_fee'];

			if ($goods_rate['total_fee']) {
				if ($total_fee < 0) {
					$total_fee = 0;
				}
			}
		}

		$should_amount = $goods_rate['should_amount'];
	}

	if (0 < $total_fee) {
		$res['total_fee'] = $total_fee - $order_drp['drp_money'];
	}
	else {
		$res['total_fee'] = 0;
	}

	$res['drp_money'] = $order_drp['drp_money'];
	$res['should_amount'] = $should_amount;
	return $res;
}

function get_seller_account_log()
{
	$result = get_filter();

	if ($result === false) {
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'sal.change_time' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['user_id'] = empty($_REQUEST['ru_id']) ? 0 : intval($_REQUEST['ru_id']);
		$adminru = get_admin_ru_id();
		$ex_where = ' WHERE mis.merchants_audit = 1';

		if (0 < $adminru['ru_id']) {
			$ex_where .= ' AND sal.user_id="' . $adminru['ru_id'] . '" ';
		}
		else if (0 < $filter['user_id']) {
			$ex_where .= ' AND sal.user_id="' . $filter['user_id'] . '" ';
		}
		else if ($filter['keywords']) {
			$sql = 'SELECT user_id FROM' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE(rz_shopName LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\' OR shop_class_keyWords LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\') ';
			$user_id = $GLOBALS['db']->getOne($sql);
			$ex_where .= ' AND sal.user_id="' . $user_id . '" ';
		}

		$filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_where = '';
		$store_search_where = '';

		if ($filter['store_search'] != 0) {
			if ($ru_id == 0) {
				if ($_REQUEST['store_type']) {
					$store_search_where = 'AND mis.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
				}

				if ($filter['store_search'] == 1) {
					$ex_where .= ' AND mis.user_id = \'' . $filter['merchant_id'] . '\' ';
				}
				else if ($filter['store_search'] == 2) {
					$store_where .= ' AND mis.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
				}
				else if ($filter['store_search'] == 3) {
					$store_where .= ' AND mis.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
				}

				if (1 < $filter['store_search']) {
					$ex_where .= ' AND mis.user_id > 0 ' . $store_where . ' ';
				}
			}
		}

		$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('merchants_account_log') . ' AS sal ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS mis ON sal.user_id = mis.user_id ' . (' ' . $ex_where);
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT sal.* FROM ' . $GLOBALS['ecs']->table('merchants_account_log') . ' AS sal ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS mis ON sal.user_id = mis.user_id ' . (' ' . $ex_where . ' ') . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	for ($i = 0; $i < count($res); $i++) {
		$res[$i]['shop_name'] = get_shop_name($res[$i]['user_id'], 1);
		$res[$i]['change_time'] = local_date($GLOBALS['_CFG']['time_format'], $res[$i]['change_time']);
	}

	$arr = array('log_list' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_del_update_goods_null($goods_id = 0, $type = 0)
{
	$admin_id = get_admin_id();
	$table_list = array('products', 'products_warehouse', 'products_area', 'goods_attr', 'warehouse_attr', 'warehouse_area_attr');

	foreach ($table_list as $key => $table) {
		if ($type) {
			$other['goods_id'] = $goods_id;
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $other, 'UPDATE', 'goods_id = 0 AND admin_id = \'' . $admin_id . '\'');
		}
		else {
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE goods_id = 0 AND admin_id = \'' . $admin_id . '\'');
			$GLOBALS['db']->query($sql);
		}
	}
}

function get_del_edit_goods_img($goods_id, $table = 'goods')
{
	$sql = 'SELECT goods_thumb, goods_img, original_img ' . ' FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE goods_id = \'' . $goods_id . '\' LIMIT 1');
	$row = $GLOBALS['db']->getRow($sql);

	if ($row) {
		if ($result['data']['goods_thumb'] && $row['goods_thumb'] != $result['data']['goods_thumb'] && strpos($row['goods_thumb'], 'data/gallery_album') === false) {
			dsc_unlink(ROOT_PATH . $row['goods_thumb']);
			$arr_img[] = $row['goods_thumb'];
		}

		if ($result['data']['goods_img'] && $row['goods_img'] != $result['data']['goods_img'] && strpos($row['goods_img'], 'data/gallery_album') === false) {
			dsc_unlink(ROOT_PATH . $row['goods_img']);
			$arr_img[] = $row['goods_img'];
		}

		if ($result['data']['original_img'] && $row['original_img'] != $result['data']['original_img'] && strpos($k['original_img'], 'data/gallery_album') === false) {
			dsc_unlink(ROOT_PATH . $row['original_img']);
			$arr_img[] = $row['original_img'];
		}

		get_oss_del_file($arr_img);
	}
}

function get_del_goodsimg_null()
{
	$admin_id = get_admin_id();

	if (isset($_SESSION['goods'][$admin_id])) {
		foreach ($_SESSION['goods'][$admin_id] as $key => $row) {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE original_img = \'' . $row['original_img'] . '\' OR goods_img = \'' . $row['goods_img'] . '\' OR goods_thumb = \'' . $row['goods_thumb'] . '\'';
			$count = $GLOBALS['db']->getOne($sql);
			if ($key == 0 && !$count) {
				if ($row['original_img'] && strpos($row['original_img'], 'data/gallery_album') === false) {
					dsc_unlink(ROOT_PATH . $row['original_img']);
					$arr_img[] = $row['original_img'];
				}

				if ($row['goods_img'] && strpos($row['goods_img'], 'data/gallery_album') === false) {
					dsc_unlink(ROOT_PATH . $row['goods_img']);
					$arr_img[] = $row['goods_img'];
				}

				if ($row['goods_thumb'] && strpos($row['goods_thumb'], 'data/gallery_album') === false) {
					dsc_unlink(ROOT_PATH . $row['goods_thumb']);
					$arr_img[] = $row['goods_thumb'];
				}

				get_oss_del_file($arr_img);
			}
		}

		unset($_SESSION['goods'][$admin_id]);
	}
	else {
		if (isset($_SESSION['goods']) && empty($_SESSION['goods'])) {
			unset($_SESSION['goods']);
		}
	}
}

function get_del_goods_gallery()
{
	$admin_id = get_admin_id();
	if (isset($_SESSION['thumb_img_id' . $admin_id]) && $_SESSION['thumb_img_id' . $admin_id]) {
		$res = $GLOBALS['db']->getAll(' SELECT img_url,thumb_url,img_original FROM' . $GLOBALS['ecs']->table('goods_gallery') . ' WHERE goods_id = 0 AND img_id' . db_create_in($_SESSION['thumb_img_id' . $admin_id]));

		if (!empty($res)) {
			foreach ($res as $k) {
				if ($k['img_url'] && strpos($k['img_url'], 'data/gallery_album') === false) {
					dsc_unlink(ROOT_PATH . $k['img_url']);
					$arr_img[] = $row['img_url'];
				}

				if ($k['thumb_url'] && strpos($k['thumb_url'], 'data/gallery_album') === false) {
					dsc_unlink(ROOT_PATH . $k['thumb_url']);
					$arr_img[] = $row['thumb_url'];
				}

				if ($k['img_original'] && strpos($k['img_original'], 'data/gallery_album') === false) {
					dsc_unlink(ROOT_PATH . $k['img_original']);
					$arr_img[] = $row['img_original'];
				}

				get_oss_del_file($arr_img);
			}
		}

		$GLOBALS['db']->query('DELETE FROM' . $GLOBALS['ecs']->table('goods_gallery') . ' WHERE goods_id = 0 AND img_id' . db_create_in($_SESSION['thumb_img_id' . $admin_id]));
		unset($_SESSION['thumb_img_id' . $admin_id]);
	}
}

function get_del_goods_video()
{
	$admin_id = get_admin_id();
	if (isset($_SESSION['goods_video'][$admin_id]) && $_SESSION['goods_video'][$admin_id]) {
		if ($_SESSION['goods_video'][$admin_id]) {
			foreach ($_SESSION['goods_video'][$admin_id] as $key => $val) {
				dsc_unlink($val);
				$arr[] = $val;
			}

			get_oss_del_file($arr);
		}

		unset($_SESSION['goods_video'][$admin_id]);
	}
}

function get_return_money($left_money = 0, $rigth_money = 0)
{
	$money = $left_money;

	if ($left_money <= 0) {
		if (!(strpos($left_money, '-') === false)) {
			$new_frozen_money = substr($left_money, 1);

			if ($rigth_money) {
				if ($rigth_money <= 0 || $rigth_money < $new_frozen_money) {
					$money = 0;
				}
			}
		}
	}

	return $money;
}

function get_up_settings($groups = '')
{
	global $db;
	global $ecs;
	global $_LANG;
	$where = ' AND parent_id > 0';

	if (!empty($groups)) {
		$where .= ' AND shop_group = \'' . $groups . '\'';
	}

	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('shop_config') . (' WHERE type<>\'hidden\' ' . $where . ' ORDER BY parent_id, sort_order, id');
	$item_list = $GLOBALS['db']->getAll($sql);
	$code_arr = array('shop_logo', 'no_picture', 'watermark', 'shop_slagon', 'wap_logo', 'two_code_logo', 'ectouch_qrcode', 'ecjia_qrcode', 'index_down_logo', 'site_commitment', 'user_login_logo', 'login_logo_pic', 'business_logo');

	foreach ($item_list as $key => $item) {
		$pid = $item['parent_id'];
		$item['name'] = isset($_LANG['cfg_name'][$item['code']]) ? $_LANG['cfg_name'][$item['code']] : $item['code'];
		$item['desc'] = isset($_LANG['cfg_desc'][$item['code']]) ? $_LANG['cfg_desc'][$item['code']] : '';

		if ($item['code'] == 'sms_shop_mobile') {
			$item['url'] = 1;
		}

		if ($item['store_range']) {
			$item['store_options'] = explode(',', $item['store_range']);

			foreach ($item['store_options'] as $k => $v) {
				$item['display_options'][$k] = isset($_LANG['cfg_range'][$item['code']][$v]) ? $_LANG['cfg_range'][$item['code']][$v] : $v;
			}

			if ($item['code'] == 'sms_type') {
				krsort($item['store_options']);
				krsort($item['display_options']);
			}
		}

		if ($item) {
			if ($item['type'] == 'file' && in_array($item['code'], $code_arr) && $item['value']) {
				$item['del_img'] = 1;

				if (strpos($item['value'], '../') === false) {
					$item['value'] = '../' . $item['value'];
				}
			}
			else {
				$item['del_img'] = 0;
			}
		}

		$group_list[] = $item;
	}

	return $group_list;
}

function get_type_cat_arr($cat_id = 0, $type = 0, $arr = 0, $ru_id = '')
{
	$adminru = get_admin_ru_id();
	$where = '';

	if ($cat_id == 0) {
		if (is_numeric($ru_id) && $GLOBALS['_CFG']['attr_set_up'] == 1) {
			$where = ' AND user_id = \'' . $ru_id . '\' AND suppliers_id = 0 ';
		}
		else {
			if ($ru_id == '' && $GLOBALS['_CFG']['attr_set_up'] == 1) {
				if (0 < $adminru['suppliers_id']) {
					$where = ' AND user_id = 0 AND suppliers_id = \'' . $adminru['suppliers_id'] . '\' ';
				}
				else {
					$where = ' AND user_id = \'' . $adminru['ru_id'] . '\' AND suppliers_id = 0';
				}
			}
			else if ($GLOBALS['_CFG']['attr_set_up'] == 0) {
				$where = ' AND user_id = 0  AND suppliers_id = 0 ';
			}
		}
	}

	if ($type == 2) {
		$sql = 'SELECT level,cat_id,parent_id FROM' . $GLOBALS['ecs']->table('goods_type_cat') . ('WHERE cat_id = \'' . $cat_id . '\' ' . $where . ' LIMIT 1');
		$cat_list = $GLOBALS[db]->getRow($sql);
	}
	else {
		$sql = 'SELECT cat_id ,cat_name ,level FROM' . $GLOBALS['ecs']->table('goods_type_cat') . ('WHERE parent_id = \'' . $cat_id . '\' ' . $where);
		$cat_list = $GLOBALS[db]->getAll($sql);
	}

	if ($type == 1) {
		$cat_string = $cat_id . ',';

		if (!empty($cat_list)) {
			foreach ($cat_list as $k => $v) {
				$cat_string .= get_type_cat_arr($v['cat_id'], 1);
			}
		}

		if ($arr == 1) {
			$cat_string = substr($cat_string, 0, strlen($cat_string) - 1);
		}

		return $cat_string;
	}
	else if ($type == 2) {
		if (0 < $cat_list['parent_id']) {
			$sql = 'SELECT cat_id,parent_id,cat_name,sort_order,level FROM' . $GLOBALS['ecs']->table('goods_type_cat') . 'WHERE parent_id = \'' . $cat_list['parent_id'] . ('\' ' . $where);
			$cat_tree = $GLOBALS['db']->getAll($sql);
			return array('checked_id' => $cat_list['parent_id'], 'arr' => $cat_tree);
		}
		else {
			return array('checked_id' => $cat_id, 'arr' => '');
		}
	}
	else {
		return $cat_list;
	}
}

function getCatNun($cat_keys = array())
{
	$adminru = get_admin_ru_id();
	$where = '';

	if (0 < $adminru['suppliers_id']) {
		$where = ' AND user_id = 0 AND suppliers_id = \'' . $adminru['suppliers_id'] . '\' ';
	}
	else if (0 < $adminru['ru_id']) {
		$where = ' AND user_id = \'' . $adminru['ru_id'] . '\' AND suppliers_id = 0 ';
	}

	if (!empty($cat_keys)) {
		$sql = 'SELECT COUNT(*) FROM' . $GLOBALS['ecs']->table('goods_type') . ('WHERE c_id in(' . $cat_keys . ') ' . $where);
		$count = $GLOBALS[db]->getOne($sql);
	}
	else {
		$count = 0;
	}

	return $count;
}

function get_typecat($level = 1)
{
	$adminru = get_admin_ru_id();
	$result = get_filter();

	if ($result === false) {
		$filter['parent_id'] = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
		$filter['level'] = $level;
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;
		$where = ' WHERE level =  \'' . $filter['level'] . '\'';

		if (0 < $adminru['suppliers_id']) {
			$where .= ' AND user_id = 0 AND suppliers_id = \'' . $adminru['suppliers_id'] . '\'';
		}
		else {
			if (0 < $adminru['ru_id'] && $GLOBALS['_CFG']['attr_set_up'] == 1) {
				$where .= ' AND user_id = \'' . $adminru['ru_id'] . '\'';
			}
			else if ($GLOBALS['_CFG']['attr_set_up'] == 0) {
				$where .= ' AND user_id = 0 ';
			}
		}

		if ($filter['keywords']) {
			$where .= ' AND cat_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\' ';
		}

		if (0 < $filter['parent_id']) {
			$where .= ' AND parent_id = \'' . $filter['parent_id'] . '\' ';
		}

		if ($adminru['ru_id'] == 0) {
			$where .= !empty($filter['seller_list']) ? ' AND user_id > 0 ' : ' AND user_id = 0 ';
		}

		$sql = 'SELECT COUNT(*) FROM' . $GLOBALS['ecs']->table('goods_type_cat') . $where . ' GROUP BY cat_id';
		$filter['record_count'] = count($GLOBALS['db']->getAll($sql));
		$filter = page_and_size($filter);
		$sql = 'SELECT cat_id,parent_id,cat_name,sort_order,user_id,level FROM' . $GLOBALS['ecs']->table('goods_type_cat') . $where . ' GROUP BY cat_id' . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$all = $GLOBALS['db']->getAll($sql);

	foreach ($all as $key => $val) {
		$all[$key]['shop_name'] = get_shop_name($val['user_id'], 1);
		$all[$key]['parent_name'] = $GLOBALS[db]->getOne('SELECT cat_name FROM' . $GLOBALS[ecs]->table('goods_type_cat') . 'WHERE cat_id = \'' . $val['parent_id'] . '\'');
		$cat_keys = get_type_cat_arr($val['cat_id'], 1, 1);
		$all[$key]['type_num'] = getcatnun($cat_keys);
	}

	return array('type' => $all, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_setting_groups($groups = '')
{
	global $_LANG;
	$group_list = array();
	$list = get_up_settings($groups);

	if ($list) {
		foreach ($list as $key => $val) {
			$group_list[$val['parent_id']]['vars'][] = $val;
		}
	}

	if ($group_list) {
		foreach ($group_list as $key => $val) {
			$data = get_table_date('shop_config', 'id=\'' . $key . '\'', array('*'));
			$data['name'] = isset($_LANG['cfg_name'][$data['code']]) ? $_LANG['cfg_name'][$data['code']] : $data['code'];
			$data['desc'] = isset($_LANG['cfg_desc'][$data['code']]) ? $_LANG['cfg_desc'][$data['code']] : '';
			$data = array_merge($data, $val);
			$group_list[$key] = $data;
		}
	}

	return $group_list;
}

function get_seller_shopinfo_changelog($ru_id = 0)
{
	$diff_data = array();
	$changelog = get_table_date('seller_shopinfo_changelog', 'ru_id=\'' . $ru_id . '\'', array('data_key', 'data_value'), 1);

	foreach ($changelog as $key => $val) {
		$diff_data[$val['data_key']] = $val['data_value'];
	}

	return $diff_data;
}

function create_ueditor_editor($input_name, $input_value = '', $input_height = 486, $type = 0)
{
	global $smarty;
	$FCKeditor = '<input type="hidden" id="' . $input_name . '" name="' . $input_name . '" value="' . htmlspecialchars($input_value) . '" /><iframe id="' . $input_name . '_frame" src="../plugins/seller_ueditor/ecmobanEditor.php?item=' . $input_name . '" width="100%" height="' . $input_height . '" frameborder="0" scrolling="no"></iframe>';

	if ($type == 1) {
		return $FCKeditor;
	}
	else {
		$smarty->assign('FCKeditor', $FCKeditor);
	}
}

function get_goods_warehouse_area_list($goods_id = 0, $model = 0, $warehouse_id = 0)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['goods_id'] = !isset($_REQUEST['goods_id']) ? $goods_id : intval($_REQUEST['goods_id']);
		$filter['warehouse_id'] = !isset($_REQUEST['warehouse_id']) ? $warehouse_id : intval($_REQUEST['warehouse_id']);
		$filter['model'] = !isset($_REQUEST['model']) ? $model : intval($_REQUEST['model']);
		$filter['region_sn'] = !isset($_REQUEST['region_sn']) ? '' : addslashes(trim($_REQUEST['region_sn']));
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? ' rw.region_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$where = ' 1 ';
		$sql_where = '';
		$select = '';
		$leftJion = '';

		if ($filter['model'] == 1) {
			$where .= ' AND rw.region_type = 0';
			$leftJion = ', ' . $GLOBALS['ecs']->table('warehouse_goods') . ' AS wg ';
			$sql_where .= ' AND rw.region_id = wg.region_id AND wg.goods_id = \'' . $filter['goods_id'] . '\'';
			$select .= ', wg.w_id, wg.region_sn, wg.region_number, wg.warehouse_price, wg.warehouse_promote_price';

			if ($filter['region_sn']) {
				$where .= ' AND wg.region_sn = \'' . $filter['region_sn'] . '\'';
			}
		}
		else if ($filter['model'] == 2) {
			if ($filter['warehouse_id']) {
				$where .= ' AND rw.parent_id = \'' . $filter['warehouse_id'] . '\'';
			}

			$where .= ' AND rw.region_type = 1';
			$select .= ', (SELECT rw2.region_name FROM ' . $GLOBALS['ecs']->table('region_warehouse') . ' AS rw2 WHERE rw2.region_id = rw.parent_id) AS warehouse_name ';
			$leftJion = ', ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' AS wag ';
			$sql_where .= ' AND rw.region_id = wag.region_id AND wag.goods_id = \'' . $filter['goods_id'] . '\'';
			$select .= ', wag.a_id, wag.region_sn, wag.city_id, wag.region_number, wag.region_price, wag.region_promote_price';

			if ($filter['region_sn']) {
				$where .= ' AND wag.region_sn = \'' . $filter['region_sn'] . '\'';
			}
		}

		$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('region_warehouse') . ' AS rw ' . $leftJion . (' WHERE ' . $where . ' ' . $sql_where);
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter, 1);
		$sql = 'SELECT rw.* ' . $select . ' FROM ' . $GLOBALS['ecs']->table('region_warehouse') . ' AS rw ' . $leftJion . (' WHERE ' . $where . ' ' . $sql_where . ' ORDER BY ') . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->getAll($sql);

	if ($res) {
		foreach ($res as $key => $row) {
			if (isset($row['city_id'])) {
				$res[$key]['city_name'] = $GLOBALS['db']->getOne('select region_name from ' . $GLOBALS['ecs']->table('region_warehouse') . ' where region_id = \'' . $row['city_id'] . '\'');
			}
		}
	}

	$arr = array('list' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count'], 'query' => 'goods_wa_query');
	return $arr;
}

function get_goods_model($goods_id)
{
	$sql = 'SELECT goods_id, goods_sn, model_attr, user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\' LIMIT 1');
	return $GLOBALS['db']->getRow($sql);
}

function get_goods_product_list($goods_id = 0, $model = 0, $warehouse_id = 0, $area_id = 0, $is_pagging = true, $city_id = 0)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['product_sn'] = !isset($_REQUEST['product_sn']) ? '' : addslashes(trim($_REQUEST['product_sn']));
		$filter['goods_id'] = !isset($_REQUEST['goods_id']) ? $goods_id : intval($_REQUEST['goods_id']);
		$filter['model'] = !isset($_REQUEST['model']) ? $model : intval($_REQUEST['model']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'product_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$where = '';

		if ($filter['model'] == 1) {
			$filter['warehouse_id'] = !isset($_REQUEST['warehouse_id']) ? $warehouse_id : intval($_REQUEST['warehouse_id']);
			$table = 'products_warehouse';
			$where .= ' AND warehouse_id = \'' . $filter['warehouse_id'] . '\'';
		}
		else if ($filter['model'] == 2) {
			$filter['area_id'] = isset($_REQUEST['area_id']) && !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : $area_id;
			$filter['city_id'] = isset($_REQUEST['city_id']) && !empty($_REQUEST['city_id']) ? intval($_REQUEST['city_id']) : $city_id;
			$table = 'products_area';
			$where .= ' AND area_id = \'' . $filter['area_id'] . '\' AND city_id = \'' . $filter['city_id'] . '\'';
		}
		else {
			$table = 'products';
		}

		if ($filter['product_sn']) {
			$where .= ' AND product_sn = \'' . $filter['product_sn'] . '\'';
		}

		$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE goods_id = \'' . $filter['goods_id'] . ('\' ' . $where);
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter, 1);
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE goods_id = \'' . $filter['goods_id'] . ('\' ' . $where . ' ORDER BY ') . $filter['sort_by'] . ' ' . $filter['sort_order'];

		if ($is_pagging) {
			$sql .= ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		}

		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->getAll($sql);

	for ($i = 0; $i < count($res); $i++) {
		$goods_attr_id = str_replace('|', ',', $res[$i]['goods_attr']);
		$sql = 'SELECT attr_value FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE goods_id = \'' . $res[$i]['goods_id'] . ('\' AND goods_attr_id IN(' . $goods_attr_id . ')');
		$attr_value = $GLOBALS['db']->getAll($sql);
		$res[$i]['attr_value'] = get_goods_attr_value($attr_value);
	}

	if ($is_pagging) {
		$arr = array('product_list' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count'], 'query' => 'sku_query');
	}
	else {
		return $res;
	}

	return $arr;
}

function get_goods_attr_value($attr_value)
{
	$str = '';

	if ($attr_value) {
		foreach ($attr_value as $key => $val) {
			$str .= '【' . $val['attr_value'] . '】';
		}
	}

	return $str;
}

function goods_parse_url($url)
{
	$parse_url = @parse_url($url);
	return !empty($parse_url['scheme']) && !empty($parse_url['host']);
}

function get_area_goods($goods_id)
{
	$sql = 'select rw.region_id, rw.region_name from ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag' . ' left join ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw on lag.region_id = rw.region_id' . (' where lag.goods_id = \'' . $goods_id . '\'');
	return $GLOBALS['db']->getAll($sql);
}

function my_array_merge($array1, $array2)
{
	$new_array = $array1;

	foreach ($array2 as $key => $val) {
		$new_array[$key] = $val;
	}

	return $new_array;
}

function get_goods_change_logs($goods_id)
{
	include_once ROOT_PATH . 'includes/lib_order.php';
	$result = get_filter();

	if ($result === false) {
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['start_time'] = empty($_REQUEST['start_time']) ? '' : trim($_REQUEST['start_time']);
		$filter['end_time'] = empty($_REQUEST['end_time']) ? '' : trim($_REQUEST['end_time']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'gcl.log_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['goodsId'] = empty($_REQUEST['goodsId']) ? 0 : intval($_REQUEST['goodsId']);
		$filter['operation_type'] = !isset($_REQUEST['operation_type']) ? -1 : intval($_REQUEST['operation_type']);
		$where = ' WHERE 1 ';

		if (!empty($goods_id)) {
			$where .= ' AND gcl.goods_id = ' . $goods_id . ' ';
		}
		else {
			return false;
		}

		if (!empty($filter['keyword'])) {
			$where .= ' AND g.goods_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\'';
		}

		if (!empty($filter['start_time']) || !empty($filter['end_time'])) {
			$filter['start_time'] = local_strtotime($filter['start_time']);
			$filter['end_time'] = local_strtotime($filter['end_time']);
			$where .= ' AND gcl.handle_time > \'' . $filter['start_time'] . '\' AND gcl.handle_time < \'' . $filter['end_time'] . '\'';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods_change_log') . ' as gcl ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' as g ON gcl.goods_id = g.goods_id' . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$list = array();
		$sql = 'SELECT gcl.*, au.user_name AS admin_name FROM ' . $GLOBALS['ecs']->table('goods_change_log') . ' as gcl ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' as g ON gcl.goods_id = g.goods_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('admin_user') . ' as au ON gcl.user_id = au.user_id ' . $where . ' GROUP BY gcl.log_id ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
		$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
		$filter['keyword'] = stripslashes($filter['keyword']);
		set_filter($filter, $sql, $param_str);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$rows['shop_price'] = price_format($rows['shop_price']);
		$rows['shipping_fee'] = price_format($rows['shipping_fee']);
		$rows['handle_time'] = local_date($GLOBALS['_CFG']['time_format'], $rows['handle_time']);
		$list[] = $rows;
	}

	return array('list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function is_distribution($ru_id)
{
	$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'is_distribution');

	if ($field['bool']) {
		$sql = ' SELECT is_distribution FROM ' . $GLOBALS['ecs']->table('merchants_steps_fields') . (' WHERE user_id = \'' . $ru_id . '\' ');
		$one = $GLOBALS['db']->getOne($sql);

		if ($one == '是') {
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}

function is_mer($goods_id)
{
	$sql = ' SELECT user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\' ');
	$one = $GLOBALS['db']->getOne($sql, true);

	if ($one == 0) {
		return false;
	}
	else {
		return $one;
	}
}

function get_areaRegion_list()
{
	$sql = 'select ra_id, ra_name from ' . $GLOBALS['ecs']->table('merchants_region_area') . ' where 1 order by ra_sort asc';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['ra_id'] = $row['ra_id'];
		$arr[$key]['ra_name'] = $row['ra_name'];
		$arr[$key]['area'] = get_arearegion_info_list($row['ra_id']);
	}

	return $arr;
}

function update_goods_stock($goods_id, $value, $warehouse_id = 0)
{
	if ($goods_id) {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('warehouse_goods') . ("\r\n                SET region_number = region_number + " . $value . ",\r\n                    last_update = '") . gmtime() . ("'\r\n                WHERE goods_id = '" . $goods_id . '\' and region_id = \'' . $warehouse_id . '\'');
		$result = $GLOBALS['db']->query($sql);
		clear_cache_files();
		return $result;
	}
	else {
		return false;
	}
}

function handle_volume_price($goods_id, $is_volume, $number_list, $price_list, $id_list)
{
	if ($is_volume) {
		foreach ($price_list as $key => $price) {
			$volume_number = $number_list[$key];
			$volume_id = isset($id_list[$key]) && !empty($id_list[$key]) ? $id_list[$key] : 0;

			if (!empty($price)) {
				if ($volume_id) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('volume_price') . (' SET volume_number = \'' . $volume_number . '\', volume_price = \'' . $price . '\' WHERE id = \'' . $volume_id . '\'');
					$GLOBALS['db']->query($sql);
				}
				else {
					$sql = 'SELECT id FROM ' . $GLOBALS['ecs']->table('volume_price') . (' WHERE goods_id = \'' . $goods_id . '\' AND (volume_price = \'' . $price . '\' OR volume_number = \'' . $volume_number . '\')');

					if (!$GLOBALS['db']->getOne($sql)) {
						$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('volume_price') . ' (price_type, goods_id, volume_number, volume_price) ' . ('VALUES (\'1\', \'' . $goods_id . '\', \'' . $volume_number . '\', \'' . $price . '\')');
						$GLOBALS['db']->query($sql);
					}
				}
			}
		}
	}
	else {
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('volume_price') . (' WHERE price_type = \'1\' AND goods_id = \'' . $goods_id . '\'');
		$GLOBALS['db']->query($sql);
	}
}

function get_store_id($order_id = 0)
{
	$sql = 'SELECT store_id FROM' . $GLOBALS['ecs']->table('store_order') . ' WHERE order_id = \'' . $order_id . '\'';
	return $GLOBALS['db']->getOne($sql);
}

function return_integral_rank($ret_id = 0, $user_id = 0, $order_sn = 0, $rec_id = 0, $refound_pay_points = 0)
{
	$sql = ' SELECT IF(g.give_integral != -1,g.give_integral*o.return_number,org.goods_price*o.return_number) as give_integral , IF(g.rank_integral != -1,g.rank_integral*o.return_number,org.goods_price*o.return_number) as rank_integral FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_return') . ' AS ord ON ord.goods_id = g.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . (' AS org ON org.rec_id = \'' . $rec_id . '\' ') . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_return_extend') . (' AS o ON o.ret_id = ord.ret_id  WHERE o.ret_id = \'' . $ret_id . '\'');
	$return_integral = $GLOBALS['db']->getRow($sql);
	$gave_custom_points = $return_integral['give_integral'];

	if (!empty($return_integral)) {
		log_account_change($user_id, 0, 0, '-' . $return_integral['rank_integral'], '-' . $gave_custom_points, sprintf($GLOBALS['_LANG']['return_order_gift_integral'], $order_sn), ACT_OTHER, 1);
		return NULL;
	}
}

function get_warehouse_area_goods($warehouse_id, $goods_id, $table)
{
	$sql = 'SELECT region_number FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE region_id = \'' . $warehouse_id . '\' AND goods_id = \'' . $goods_id . '\'');
	return $GLOBALS['db']->getOne($sql);
}

function lib_get_del_edit_goods_img($goods_id)
{
	$sql = 'SELECT goods_thumb, goods_img, original_img ' . ' FROM ' . $GLOBALS['ecs']->table('goods_lib') . (' WHERE goods_id = \'' . $goods_id . '\' LIMIT 1');
	$row = $GLOBALS['db']->getRow($sql);

	if ($row) {
		if ($result['data']['goods_thumb'] && $row['goods_thumb'] != $result['data']['goods_thumb'] && strpos($row['goods_thumb'], 'data/gallery_album') === false) {
			dsc_unlink(ROOT_PATH . $row['goods_thumb']);
			$arr_img[] = $row['goods_thumb'];
		}

		if ($result['data']['goods_img'] && $row['goods_img'] != $result['data']['goods_img'] && strpos($row['goods_img'], 'data/gallery_album') === false) {
			dsc_unlink(ROOT_PATH . $row['goods_img']);
			$arr_img[] = $row['goods_img'];
		}

		if ($result['data']['original_img'] && $row['original_img'] != $result['data']['original_img'] && strpos($k['original_img'], 'data/gallery_album') === false) {
			dsc_unlink(ROOT_PATH . $row['original_img']);
			$arr_img[] = $row['original_img'];
		}

		get_oss_del_file($arr_img);
	}
}

function lib_get_del_goodsimg_null()
{
	$admin_id = get_admin_id();

	if (isset($_SESSION['goods_lib'][$admin_id])) {
		foreach ($_SESSION['goods_lib'][$admin_id] as $key => $row) {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods_lib') . ' WHERE original_img = \'' . $row['original_img'] . '\' OR goods_img = \'' . $row['goods_img'] . '\' OR goods_thumb = \'' . $row['goods_thumb'] . '\'';
			$count = $GLOBALS['db']->getOne($sql);
			if ($key == 0 && !$count) {
				if ($row['original_img'] && strpos($row['original_img'], 'data/gallery_album') === false) {
					dsc_unlink(ROOT_PATH . $row['original_img']);
					$arr_img[] = $row['original_img'];
				}

				if ($row['goods_img'] && strpos($row['goods_img'], 'data/gallery_album') === false) {
					dsc_unlink(ROOT_PATH . $row['goods_img']);
					$arr_img[] = $row['goods_img'];
				}

				if ($row['goods_thumb'] && strpos($row['goods_thumb'], 'data/gallery_album') === false) {
					dsc_unlink(ROOT_PATH . $row['goods_thumb']);
					$arr_img[] = $row['goods_thumb'];
				}

				get_oss_del_file($arr_img);
			}
		}

		unset($_SESSION['goods_lib'][$admin_id]);
	}
	else {
		if (isset($_SESSION['goods_lib']) && empty($_SESSION['goods_lib'])) {
			unset($_SESSION['goods_lib']);
		}
	}
}

function lib_get_del_goods_gallery()
{
	$admin_id = get_admin_id();
	if (isset($_SESSION['thumb_img_id' . $_SESSION['admin_id']]) && $_SESSION['thumb_img_id' . $admin_id]) {
		$res = $GLOBALS['db']->getAll(' SELECT img_url,thumb_url,img_original FROM' . $GLOBALS['ecs']->table('goods_lib_gallery') . ' WHERE goods_id = 0 AND img_id' . db_create_in($_SESSION['thumb_img_id' . $admin_id]));

		if (!empty($res)) {
			foreach ($res as $k) {
				if ($k['img_url'] && strpos($k['img_url'], 'data/gallery_album') === false) {
					dsc_unlink(ROOT_PATH . $k['img_url']);
					$arr_img[] = $row['img_url'];
				}

				if ($k['thumb_url'] && strpos($k['thumb_url'], 'data/gallery_album') === false) {
					dsc_unlink(ROOT_PATH . $k['thumb_url']);
					$arr_img[] = $row['thumb_url'];
				}

				if ($k['img_original'] && strpos($k['img_original'], 'data/gallery_album') === false) {
					dsc_unlink(ROOT_PATH . $k['img_original']);
					$arr_img[] = $row['img_original'];
				}

				get_oss_del_file($arr_img);
			}
		}

		$GLOBALS['db']->query('DELETE FROM' . $GLOBALS['ecs']->table('goods_lib_gallery') . ' WHERE goods_id = 0 AND img_id' . db_create_in($_SESSION['thumb_img_id' . $admin_id]));
		unset($_SESSION['thumb_img_id' . $admin_id]);
	}
}

function gallery_cat_list($album_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true, $seller_id = 0)
{
	static $res;
	$adminru = get_admin_ru_id();
	$where = 'WHERE 1 ';

	if ($res === NULL) {
		if (0 < $adminru['suppliers_id']) {
			$where .= ' AND g.suppliers_id=\'' . $adminru['suppliers_id'] . '\'';
		}
		else {
			if (0 < $adminru['ru_id']) {
				$seller_id = $adminru['ru_id'];
			}

			$where .= ' AND g.ru_id=\'' . $seller_id . '\' AND g.suppliers_id = 0 ';
		}

		$where .= ' AND g.ru_id=\'' . $seller_id . '\'';
		$sql = 'SELECT g.album_id, g.album_mame, g.parent_album_id, g.sort_order, COUNT(a.parent_album_id) AS has_children ' . 'FROM ' . $GLOBALS['ecs']->table('gallery_album') . ' AS g ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('gallery_album') . ' AS a ON a.parent_album_id = g.album_id ' . $where . 'GROUP BY g.album_id ' . 'ORDER BY g.parent_album_id, g.sort_order ASC';
		$res = $GLOBALS['db']->getAll($sql);
	}

	if (empty($res) == true) {
		return $re_type ? '' : array();
	}

	$options = gallery_cat_options($album_id, $res);
	$children_level = 99999;

	if ($is_show_all == false) {
		foreach ($options as $key => $val) {
			if ($children_level < $val['level']) {
				unset($options[$key]);
			}
			else {
				$children_level = 99999;
			}
		}
	}

	if (0 < $level) {
		if ($album_id == 0) {
			$end_level = $level;
		}
		else {
			$first_item = reset($options);
			$end_level = $first_item['level'] + $level;
		}

		foreach ($options as $key => $val) {
			if ($end_level <= $val['level']) {
				unset($options[$key]);
			}
		}
	}

	if ($re_type == true) {
		$select = '';

		foreach ($options as $var) {
			$select .= '<option value="' . $var['album_id'] . '" ';
			$select .= $selected == $var['album_id'] ? 'selected=\'ture\'' : '';
			$select .= '>';

			if (0 < $var['level']) {
				$select .= str_repeat('&nbsp;', $var['level'] * 4);
			}

			$select .= htmlspecialchars(addslashes($var['cat_name']), ENT_QUOTES) . '</option>';
		}

		return $select;
	}
	else {
		return $options;
	}
}

function gallery_cat_options($spec_cat_id, $arr)
{
	static $cat_options = array();

	if (isset($cat_options[$spec_cat_id])) {
		return $cat_options[$spec_cat_id];
	}

	$i = 0;

	if (!isset($cat_options[0])) {
		$level = $last_cat_id = 0;
		$options = $cat_id_array = $level_array = array();

		while (!empty($arr)) {
			foreach ($arr as $key => $value) {
				$album_id = $value['album_id'];
				if ($level == 0 && $last_cat_id == 0) {
					if (0 < $value['parent_album_id']) {
						break;
					}

					$options[$album_id] = $value;
					$options[$album_id]['level'] = $level;
					$options[$album_id]['id'] = $album_id;
					$options[$album_id]['name'] = $value['album_mame'];
					unset($arr[$key]);

					if ($value['has_children'] == 0) {
						continue;
					}

					$last_cat_id = $album_id;
					$cat_id_array = array($album_id);
					$level_array[$last_cat_id] = ++$level;
					continue;
				}

				if ($value['parent_album_id'] == $last_cat_id) {
					$options[$album_id] = $value;
					$options[$album_id]['level'] = $level;
					$options[$album_id]['id'] = $album_id;
					$options[$album_id]['name'] = $value['album_mame'];
					unset($arr[$key]);

					if (0 < $value['has_children']) {
						if (end($cat_id_array) != $last_cat_id) {
							$cat_id_array[] = $last_cat_id;
						}

						$last_cat_id = $album_id;
						$cat_id_array[] = $album_id;
						$level_array[$last_cat_id] = ++$level;
					}
				}
				else if ($last_cat_id < $value['parent_album_id']) {
					break;
				}
			}

			$count = count($cat_id_array);

			if (1 < $count) {
				$last_cat_id = array_pop($cat_id_array);
			}
			else if ($count == 1) {
				if ($last_cat_id != end($cat_id_array)) {
					$last_cat_id = end($cat_id_array);
				}
				else {
					$level = 0;
					$last_cat_id = 0;
					$cat_id_array = array();
					continue;
				}
			}

			if ($last_cat_id && isset($level_array[$last_cat_id])) {
				$level = $level_array[$last_cat_id];
			}
			else {
				$level = 0;
			}
		}

		$cat_options[0] = $options;
	}
	else {
		$options = $cat_options[0];
	}

	if (!$spec_cat_id) {
		return $options;
	}
	else {
		if (empty($options[$spec_cat_id])) {
			return array();
		}

		$spec_cat_id_level = $options[$spec_cat_id]['level'];

		foreach ($options as $key => $value) {
			if ($key != $spec_cat_id) {
				unset($options[$key]);
			}
			else {
				break;
			}
		}

		$spec_cat_id_array = array();

		foreach ($options as $key => $value) {
			if ($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id || $value['level'] < $spec_cat_id_level) {
				continue;
			}
			else {
				$spec_cat_id_array[$key] = $value;
			}
		}

		$cat_options[$spec_cat_id] = $spec_cat_id_array;
		return $spec_cat_id_array;
	}
}

function gallery_child_cat_list($parent_id)
{
	$adminru = get_admin_ru_id();
	$where = '';

	if (0 < $adminru['ru_id']) {
		$where = ' AND ru_id = \'' . $adminru['ru_id'] . '\' ';
	}

	$sql = 'SELECT album_id, album_mame, parent_album_id, sort_order, album_cover, album_desc, add_time FROM' . $GLOBALS['ecs']->table('gallery_album') . (' WHERE parent_album_id = \'' . $parent_id . '\' ') . $where . 'ORDER BY sort_order ASC';
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $k => $v) {
		if (isset($v['album_cover']) && $v['album_cover']) {
			$res[$k]['album_cover'] = get_image_path($v['album_id'], $v['album_cover']);
		}

		if (!empty($res[$k]['album_cover']) && (strpos($res[$k]['album_cover'], 'http://') === false && strpos($res[$k]['album_cover'], 'https://') === false)) {
			$res[$k]['album_cover'] = $GLOBALS['ecs']->url() . $res[$k]['album_cover'];
		}

		$res[$k]['album_mame'] = $v['album_mame'];
		$res[$k]['album_id'] = $v['album_id'];
	}

	return $res;
}

function gallery_child_cat_num($parent_id)
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('gallery_album') . (' WHERE parent_album_id = \'' . $parent_id . '\'');
	return $GLOBALS['db']->getOne($sql);
}

function export_csv_pro($data = array(), $filename = '', $prev = array(), $next = array())
{
	if (empty($filename)) {
		$filename = date('YmdHis') . '.csv';
	}
	else {
		$filename = $filename . '.csv';
	}

	if (!empty($prev)) {
		$prev = implode(',', $prev) . "\n";
	}
	else {
		$prev = '';
	}

	if (!empty($next)) {
		$next = implode(',', $next) . "\n";
	}
	else {
		$next = '';
	}

	header('Content-type:text/csv');
	header('Content-Disposition:attachment;filename=' . $filename);
	header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
	header('Expires:0');
	header('Pragma:public');

	if (empty($data)) {
		echo idel('没有符合您要求的数据！^_^');
	}
	else {
		$result = '';

		foreach ($data as $key => $value) {
			foreach ($value as $abc => $cba) {
				$value[$abc] = idel(str_replace(',', '', $cba));
			}

			$result .= implode(',', $value) . "\n";
		}

		echo $prev . $result . $next;
	}
}

function idel($strInput)
{
	$strInput = strip_tags($strInput);
	return $strInput;
}

function get_print_order_info($order_id = 0, $order_type = 'order')
{
	require_once ROOT_PATH . 'includes/lib_order.php';
	require_once ROOT_PATH . 'includes/lib_goods.php';
	require_once ROOT_PATH . 'includes/lib_wholesale.php';

	if ($order_type == 'order') {
		$order_info = order_info($order_id);
		$goods_list = order_goods($order_id);
	}
	else {
		$order_info = wholesale_order_info($order_id);
		$goods_list = get_wholesale_order_goods_toInfo($order_id);
		$order_info['total_fee'] = $order_info['order_amount'];
		$order_info['formated_total_fee'] = price_format($order_info['total_fee']);

		if ($order_info['pay_status'] == 2) {
			$order_info['money_paid'] = $order_info['order_amount'];
			$order_info['formated_money_paid'] = price_format($order_info['money_paid']);
			$order_info['formated_order_amount'] = price_format(0);
		}
	}

	if (isset($order_info['order_id'])) {
		if ($order_type == 'order') {
			$order_info['complete_address'] = get_goods_region_name($order_info['province']) . '-' . get_goods_region_name($order_info['city']) . '-' . get_goods_region_name($order_info['district']) . ' ' . $order_info['address'];
		}
		else {
			$order_info['complete_address'] = $order_info['address'];
		}

		$number_amount = 0;

		if ($goods_list) {
			foreach ($goods_list as $key => $val) {
				$goods_list[$key]['format_goods_price'] = price_format($val['goods_price']);
				$goods_list[$key]['format_goods_amount'] = price_format($val['goods_price'] * $val['goods_number']);

				if ($order_type == 'order') {
					$products = get_warehouse_id_attr_number($val['goods_id'], $val['goods_attr_id'], $val['ru_id'], $val['warehouse_id'], $val['area_id'], $val['model_attr']);
				}

				if (isset($products) && isset($products['bar_code'])) {
					$bar_code = $products['bar_code'];
				}
				else {
					$bar_code = get_table_date('goods', 'goods_id = \'' . $val['goods_id'] . '\'', array('bar_code'), 2);
				}

				$goods_list[$key]['bar_code'] = $bar_code;
				$number_amount += $val['goods_number'];
			}
		}

		$order_info['number_amount'] = $number_amount;
		$order_info['goods_list'] = $goods_list;
	}

	return $order_info;
}

function get_kdniao_print_content($order_id = 0, $shipping_spec = array(), $shipping_info = array())
{
	$order = order_info($order_id);
	$adminru = get_admin_ru_id();
	$shopinfo = get_table_date('seller_shopinfo', 'ru_id=\'' . $adminru['ru_id'] . '\'', array('*'));
	$result = read_static_cache($order['order_sn'], '/data/kdniao_eorder/');
	if ($result === false || $result !== false && $result['ResultCode'] != '100') {
		$Goodsquantity = 0;
		$GoodsWeight = 0;
		$GoodsName = array();
		$order_goods = order_goods($order_id);

		foreach ($order_goods as $goods) {
			$Goodsquantity += $goods['goods_number'];
			$GoodsWeight += $goods['goodsweight'];
			$cat_id = get_table_date('goods', 'goods_id=\'' . $goods['goods_id'] . '\'', array('cat_id'), 2);
			$cat_name = get_table_date('category', 'cat_id=\'' . $cat_id . '\'', array('cat_name'), 2);

			if ($cat_name) {
				$GoodsName[] = $cat_name . '×' . $goods['goods_number'];
			}
		}

		$eorder = array();
		$eorder['ShipperCode'] = isset($shipping_spec['kdniao_code']) ? $shipping_spec['kdniao_code'] : '';
		$eorder['OrderCode'] = $order['order_sn'];
		$eorder['PayType'] = isset($shipping_info['pay_type']) ? $shipping_info['pay_type'] : 1;
		$eorder['ExpType'] = 1;
		$eorder['IsReturnPrintTemplate'] = 1;
		$eorder['CustomerName'] = isset($shipping_info['customer_name']) ? $shipping_info['customer_name'] : '';
		$eorder['CustomerPwd'] = isset($shipping_info['customer_pwd']) ? $shipping_info['customer_pwd'] : '';
		$eorder['MonthCode'] = isset($shipping_info['month_code']) ? $shipping_info['month_code'] : '';
		$eorder['SendSite'] = isset($shipping_info['send_site']) ? $shipping_info['send_site'] : '';
		$eorder['TemplateSize'] = isset($shipping_info['template_size']) ? $shipping_info['template_size'] : '';
		$sender = array();
		$sender['Name'] = $shopinfo['shop_name'];
		$sender['Mobile'] = $shopinfo['mobile'];
		$sender['Tel'] = $shopinfo['kf_tel'];
		$sender['PostCode'] = '000000';
		$sender['ProvinceName'] = get_goods_region_name($shopinfo['province']);
		$sender['CityName'] = get_goods_region_name($shopinfo['city']);
		$sender['ExpAreaName'] = get_goods_region_name($shopinfo['district']);
		$sender['Address'] = $shopinfo['shop_address'];
		$receiver = array();
		$receiver['Name'] = $order['consignee'];
		$receiver['Mobile'] = $order['mobile'];
		$receiver['Tel'] = $order['tel'];

		if (!empty($order['zipcode'])) {
			$receiver['PostCode'] = trim($order['zipcode']);
		}
		else {
			$receiver['PostCode'] = '000000';
		}

		$receiver['ProvinceName'] = get_goods_region_name($order['province']);
		$receiver['CityName'] = get_goods_region_name($order['city']);
		$receiver['ExpAreaName'] = get_goods_region_name($order['district']);
		$receiver['Address'] = $order['address'];
		$commodityOne = array();
		$commodityOne['GoodsName'] = empty($GoodsName) ? '其他' : implode(', ', $GoodsName);
		$commodityOne['Goodsquantity'] = empty($Goodsquantity) ? 1 : $Goodsquantity;
		$commodityOne['GoodsWeight'] = empty($GoodsWeight) ? 1 : $GoodsWeight;
		$commodity = array();
		$commodity[] = $commodityOne;
		$eorder['Sender'] = $sender;
		$eorder['Receiver'] = $receiver;
		$eorder['Commodity'] = $commodity;
		$jsonParam = json_encode($eorder, JSON_UNESCAPED_UNICODE);
		$kdniao = Kdniao::getInstance($GLOBALS['_CFG']['kdniao_client_id'], $GLOBALS['_CFG']['kdniao_appkey']);
		$jsonResult = $kdniao->submitEOrder($jsonParam);
		$result = json_decode($jsonResult, true);

		if (!empty($result)) {
			write_static_cache($order['order_sn'], $result, '/data/kdniao_eorder/');
		}
	}

	return $result;
}

function get_print_type($ru_id = 0)
{
	$print_type = get_table_date('seller_shopinfo', 'ru_id=\'' . $ru_id . '\'', array('print_type'), 2);
	return $print_type;
}

function get_shipping_spec($shipping_code = '')
{
	$set_modules = true;
	include ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php';
	$shipping_spec = $modules[0];
	return $shipping_spec;
}

function get_shipping_conf($shipping_id = 0, $ru_id = 0)
{
	$shipping_conf = get_table_date('kdniao_eorder_config', 'shipping_id=\'' . $shipping_id . '\' and ru_id=\'' . $ru_id . '\'', array('*'));
	return $shipping_conf;
}

function get_shipping_info($shipping_id = 0, $ru_id = 0)
{
	if ($GLOBALS['_CFG']['kdniao_account_use'] == 0) {
		$ru_id = 0;
	}

	if ($ru_id == 0) {
		$data = array('*');
	}
	else {
		$data = array('shipping_id', 'shipping_code', 'shipping_name');
	}

	$shipping_info = get_table_date('shipping', 'shipping_id=\'' . $shipping_id . '\'', $data);
	$shipping_conf = get_shipping_conf($shipping_id, $ru_id);
	if (!empty($shipping_conf) && is_array($shipping_conf)) {
		$shipping_info = array_merge($shipping_info, $shipping_conf);
	}

	return $shipping_info;
}

function get_kdniao_customer_account($shipping_id = 0, $ru_id = 0)
{
	$data = get_table_date('kdniao_customer_account', 'shipping_id=\'' . $shipping_id . '\' and ru_id=\'' . $ru_id . '\'', array('*'));
	return $data;
}

function get_seller_brand()
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('merchants_shop_brand') . ' WHERE 1';
	$res = $GLOBALS['db']->getAll($sql);

	if ($res) {
		foreach ($res as $key => $row) {
			$res[$key]['seller_name'] = get_shop_name($row['user_id'], 1);
			$sql = 'SELECT b.brand_id, b.brand_name FROM ' . $GLOBALS['ecs']->table('brand') . ' AS b,' . $GLOBALS['ecs']->table('link_brand') . ' AS lb' . ' WHERE b.brand_id = lb.brand_id AND lb.bid = \'' . $row['bid'] . '\' LIMIT 1';
			$brand = $GLOBALS['db']->getRow($sql);

			if ($brand) {
				$res[$key]['brand_id'] = $brand['brand_id'];
				$res[$key]['brand_name'] = $brand['brand_name'];
				$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE brand_id = \'' . $row['bid'] . '\' AND user_id = \'' . $row['user_id'] . '\'';
				$res[$key]['goods_count'] = $GLOBALS['db']->getOne($sql);
			}
			else {
				unset($res[$key]);
			}
		}
	}

	return $res;
}

function get_statistical_data($start_date = 0, $end_date = 0, $type = 'order')
{
	$data = array();
	$adminru = get_admin_ru_id();
	$timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];
	$time_diff = $timezone * 3600;
	$date_start = $start_date;
	$date_end = $end_date;
	$day_num = ceil($date_end - $date_start) / 86400;
	$no_main_order = ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi2 WHERE oi2.main_order_id = oi.order_id) = 0 ';

	if (0 < $adminru['rs_id']) {
		$filed = ' (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og ' . ' WHERE og.order_id = oi.order_id LIMIT 1) ';
		$where_date .= get_rs_null_where($filed, $adminru['rs_id']);
	}
	else {
		$where_date .= ' AND (SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og WHERE oi.order_id = og.order_id LIMIT 1) = \'' . $adminru['ru_id'] . '\'';
	}

	$sql = 'SELECT DATE_FORMAT(FROM_UNIXTIME(oi.add_time + ' . $time_diff . '),"%y-%m-%d") AS day,COUNT(*) AS count,SUM(oi.money_paid) AS money, SUM(oi.money_paid)+SUM(oi.surplus) AS superman FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi' . ' WHERE oi.add_time BETWEEN ' . $date_start . ' AND ' . $date_end . $no_main_order . $where_date . ' AND oi.supplier_id = 0 GROUP BY day ORDER BY day ASC ';
	$result = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($result)) {
		$orders_series_data[$row['day']] = intval($row['count']);
		$sales_series_data[$row['day']] = floatval($row['money']);
		$sales_series_data[$row['day']] = floatval($row['superman']);
	}

	for ($i = 1; $i <= $day_num; $i++) {
		$day = local_date('y-m-d', local_strtotime(' - ' . ($day_num - $i) . ' days'));

		if (empty($orders_series_data[$day])) {
			$orders_series_data[$day] = 0;
			$sales_series_data[$day] = 0;
		}

		$day = local_date('m-d', local_strtotime($day));
		$orders_xAxis_data[] = $day;
		$sales_xAxis_data[] = $day;
	}

	$title = array('text' => '', 'subtext' => '');
	$toolbox = array(
		'show'    => true,
		'orient'  => 'vertical',
		'x'       => 'right',
		'y'       => '60',
		'feature' => array(
			'magicType'   => array(
				'show' => true,
				'type' => array('line', 'bar')
				),
			'saveAsImage' => array('show' => true)
			)
		);
	$tooltip = array(
		'trigger'     => 'axis',
		'axisPointer' => array(
			'lineStyle' => array('color' => '#6cbd40')
			)
		);
	$xAxis = array(
		'type'        => 'category',
		'boundaryGap' => false,
		'axisLine'    => array(
			'lineStyle' => array('color' => '#ccc', 'width' => 0)
			),
		'data'        => array()
		);
	$yAxis = array(
		'type'      => 'value',
		'axisLine'  => array(
			'lineStyle' => array('color' => '#ccc', 'width' => 0)
			),
		'axisLabel' => array('formatter' => '')
		);
	$series = array(
		array(
			'name'      => '',
			'type'      => 'line',
			'itemStyle' => array(
				'normal' => array(
					'color'     => '#6cbd40',
					'lineStyle' => array('color' => '#6cbd40')
					)
				),
			'data'      => array(),
			'markPoint' => array(
				'itemStyle' => array(
					'normal' => array('color' => '#6cbd40')
					),
				'data'      => array(
					array('type' => 'max', 'name' => '最大值'),
					array('type' => 'min', 'name' => '最小值')
					)
				)
			),
		array(
			'type'      => 'force',
			'name'      => '',
			'draggable' => false,
			'nodes'     => array('draggable' => false)
			)
		);
	$calculable = true;
	$legend = array(
		'data' => array()
		);

	if ($type == 'order') {
		$title['text'] = '订单数量';
		$xAxis['data'] = $orders_xAxis_data;
		$yAxis['formatter'] = '{value}个';
		ksort($orders_series_data);
		$series[0]['name'] = '订单个数';
		$series[0]['data'] = array_values($orders_series_data);
	}

	if ($type == 'sale') {
		$title['text'] = '销售额';
		$xAxis['data'] = $sales_xAxis_data;
		$yAxis['formatter'] = '{value}元';
		ksort($sales_series_data);
		$series[0]['name'] = '销售额';
		$series[0]['data'] = array_values($sales_series_data);
	}

	$data['title'] = $title;
	$data['series'] = $series;
	$data['tooltip'] = $tooltip;
	$data['legend'] = $legend;
	$data['toolbox'] = $toolbox;
	$data['calculable'] = $calculable;
	$data['xAxis'] = $xAxis;
	$data['yAxis'] = $yAxis;
	$data['xy_file'] = get_dir_file_list();
	return $data;
}

function verific_pic($file = '', $ru_id = 0)
{
	$return = false;

	if ($file) {
		$where = ' WHERE 1';
		$where .= ' AND (g.goods_desc LIKE \'%' . $file . '%\' OR g.desc_mobile LIKE \'%' . $file . '%\' OR ga.img_url LIKE \'%' . $file . '%\' OR ga.thumb_url LIKE \'%' . $file . '%\' OR ga.img_original LIKE \'%' . $file . '%\')';
		$sql = 'SELECT COUNT(*) FROM' . $GLOBALS['ecs']->table('goods') . 'AS g LEFT JOIN ' . $GLOBALS['ecs']->table('goods_gallery') . 'AS ga ON ga.goods_id = g.goods_id ' . $where;

		if (0 < $GLOBALS['db']->getOne($sql)) {
			$return = true;
		}
	}

	return $return;
}

function get_the_national($region_name = '中国')
{
	$sql = 'SELECT r2.region_id, r2.region_name, r2.parent_id FROM ' . $GLOBALS['ecs']->table('region') . ' AS r1' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS r2 ON r2.parent_id = r1.region_id AND r2.region_type = 1' . (' WHERE r1.region_type = 0 AND r1.region_name = \'' . $region_name . '\'');
	$regions = $GLOBALS['db']->getAll($sql);
	return $regions;
}

function get_transport_shipping_list($tid = 0, $ru_id = 0)
{
	$sql = 'SELECT g.tid, g.shipping_id, s.shipping_name FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods_transport_tpl') . ' as g ON g.shipping_id = s.shipping_id' . (' WHERE user_id = \'' . $ru_id . '\' AND tid = \'' . $tid . '\' GROUP BY g.shipping_id');
	$shipping_tpl = $GLOBALS['db']->getAll($sql);

	if ($shipping_tpl) {
		foreach ($shipping_tpl as $k => $v) {
			if ($v['shipping_id']) {
				$shipping_tpl[$k]['area_list'] = get_transport_shipping_area_list($v['tid'], $v['shipping_id']);
				$shipping_tpl[$k]['area_count'] = count($shipping_tpl[$k]['area_list']);
			}
		}
	}

	return $shipping_tpl;
}

function get_transport_shipping_area_list($tid = 0, $shipping_id = 0)
{
	$sql = 'SELECT g.* FROM ' . $GLOBALS['ecs']->table('goods_transport_tpl') . ' AS g ' . ('WHERE g.tid = \'' . $tid . '\' AND g.shipping_id = \'' . $shipping_id . '\' ORDER BY g.id');
	$area_list = $GLOBALS['db']->getAll($sql);

	if ($area_list) {
		foreach ($area_list as $k => $v) {
			if ($v['region_id']) {
				$area_list[$k]['region_list'] = get_area_list($v['region_id']);
			}
		}
	}

	return $area_list;
}

function get_link_goods_desc_list($ru_id = 0)
{
	$result = get_filter();

	if ($result === false) {
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['type'] = isset($_REQUEST['type']) && !empty($_REQUEST['type']) ? dsc_addslashes($_REQUEST['type']) : '';
		$filter['ru_id'] = $ru_id;

		if ($filter['type']) {
			$where = 'AND ru_id > 0';
		}
		else {
			$where = 'AND ru_id = \'' . $filter['ru_id'] . '\'';
		}

		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);

		if (0 < $page) {
			$filter['page'] = $page;
		}

		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('link_goods_desc') . (' WHERE 1 ' . $where);
		$record_count = $GLOBALS['db']->getOne($sql);
		$filter['record_count'] = $record_count;
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('link_goods_desc') . (' WHERE 1 ' . $where) . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->getAll($sql);
	$arr = array('desc_list' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_rs_goods_where($field = '', $rs_id = 0)
{
	$where = '';
	if (0 < $rs_id && $field != '') {
		$region_id = get_table_date('rs_region', 'rs_id=\'' . $rs_id . '\'', array('region_id'), 2);

		if (!empty($region_id)) {
			$sql = ' SELECT user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . (' WHERE region_id =\'' . $region_id . '\' ');
			$user_ids = $GLOBALS['db']->getCol($sql);

			if (!empty($user_ids)) {
				$where = ' AND (' . $field . ' ' . db_create_in($user_ids) . (' OR ' . $field . ' = 0 ) ');
			}
			else {
				$where = ' AND ' . $field . ' = 0 ';
			}
		}
		else {
			$where = ' AND ' . $field . ' = 0 ';
		}
	}

	return $where;
}

function get_rs_null_where($field = '', $rs_id = 0)
{
	$where = '';
	if (0 < $rs_id && $field != '') {
		$region_id = get_table_date('rs_region', 'rs_id=\'' . $rs_id . '\'', array('region_id'), 2);

		if (!empty($region_id)) {
			$sql = ' SELECT user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . (' WHERE region_id =\'' . $region_id . '\' ');
			$user_ids = $GLOBALS['db']->getCol($sql);

			if (!empty($user_ids)) {
				$where .= ' AND ' . $field . ' ' . db_create_in($user_ids);
			}
			else {
				$where .= ' AND ' . $field . ' IS NULL ';
			}
		}
		else {
			$where .= ' AND ' . $field . ' IS NULL ';
		}
	}

	return $where;
}

function get_cat_child($cat_id = 0, $arr = array())
{
	$arr[] = $cat_id;
	$sql = 'SELECT album_id FROM' . $GLOBALS['ecs']->table('gallery_album') . ('WHERE parent_album_id = \'' . $cat_id . '\'');
	$cat_list = $GLOBALS['db']->getCol($sql);

	if (!empty($cat_list)) {
		foreach ($cat_list as $v) {
			$arr = get_cat_child($v, $arr);
		}
	}

	return $arr;
}

function get_order_delayed_list($ru_id = 0)
{
	$adminru = get_admin_ru_id();
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['review_status'] = empty($_REQUEST['review_status']) ? '' : trim($_REQUEST['review_status']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
			$filter['review_status'] = json_str_iconv($filter['review_status']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'delayed_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$ex_where = ' WHERE 1 ';

		if ($filter['keywords']) {
			$ex_where .= ' AND u.user_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\'';
		}

		$filter['review_status'] = $filter['review_status'] == -1 ? '' : intval($filter['review_status']);

		if ($filter['review_status']) {
			$ex_where .= ' AND od.review_status = \'' . $filter['review_status'] . '\'';
		}

		if (0 < $adminru['ru_id']) {
			$ex_where .= ' AND (SELECT og.ru_id FROM' . $GLOBALS['ecs']->table('order_goods') . ' AS og WHERE og.order_id = od.order_id) = \'' . $adminru['ru_id'] . '\'';
		}

		$ex_where .= '';
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_delayed') . 'AS od ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ON oi.order_id = od.order_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id = oi.user_id ' . $ex_where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT od.*,oi.order_sn, u.user_name ' . ' FROM ' . $GLOBALS['ecs']->table('order_delayed') . 'as od ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ON od.order_id = oi.order_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id = oi.user_id ' . $ex_where . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$order_delayed_list = $GLOBALS['db']->getAll($sql);

	foreach ($order_delayed_list as $key => $value) {
		switch ($value['review_status']) {
		case 0:
			$order_delayed_list[$key]['review_status_info'] = '未审核';
			break;

		case 1:
			$order_delayed_list[$key]['review_status_info'] = '审核通过';
			break;

		case 2:
			$order_delayed_list[$key]['review_status_info'] = '审核未通过';
			break;

		default:
			break;
		}

		$order_delayed_list[$key]['review_admin_user'] = '--';

		if ($value['review_admin']) {
			$sql = 'SELECT user_name FROM ' . $GLOBALS['ecs']->table('admin_user') . (' WHERE user_id = \'' . $value['review_admin'] . '\'');
			$review_admin_user = $GLOBALS['db']->getOne($sql);
			$order_delayed_list[$key]['review_admin_user'] = $review_admin_user;
		}
	}

	$arr = array('order_delay_list' => $order_delayed_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
