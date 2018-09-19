<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function area_product_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = isset($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'region_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$ex_where = ' where 1 and region_type = 1';

		if ($filter['keywords']) {
			$ex_where .= ' AND region_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\'';
		}

		$filter['record_count'] = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('region_warehouse') . $ex_where);
		$filter = page_and_size($filter);
		$sql = 'SELECT region_id, region_name ' . ' FROM ' . $GLOBALS['ecs']->table('region_warehouse') . $ex_where;
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$area_list = $GLOBALS['db']->getAll($sql);
	$count = count($area_list);

	for ($i = 0; $i < $count; $i++) {
		$area_list[$i]['region_name'] = $area_list[$i]['region_name'];
	}

	foreach ($area_list as $k => $v) {
		$area_list[$k]['attr_typeNum'] = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('products_area') . ' WHERE area_id=\'' . $v['region_id'] . '\' ');
	}

	$arr = array('area_list' => $area_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_goods_specifications_list($goods_id)
{
	$where = '';
	$admin_id = get_admin_id();

	if (empty($goods_id)) {
		if ($admin_id) {
			$where .= ' AND admin_id = \'' . $admin_id . '\'';
		}
		else {
			return array();
		}
	}

	$sql = "SELECT g.goods_attr_id, g.attr_value, g.attr_id, a.attr_name\r\n            FROM " . $GLOBALS['ecs']->table('goods_attr') . " AS g\r\n                LEFT JOIN " . $GLOBALS['ecs']->table('attribute') . " AS a\r\n                    ON a.attr_id = g.attr_id\r\n            WHERE goods_id = '" . $goods_id . "'\r\n            AND a.attr_type = 1" . $where . ' ORDER BY a.sort_order, a.attr_id, g.goods_attr_id';
	$results = $GLOBALS['db']->getAll($sql);
	return $results;
}

function product_area_list($goods_id, $conditions = '', $area_id)
{
	$param_str = '-' . $goods_id;
	$result = get_filter($param_str);

	if ($result === false) {
		$day = getdate();
		$today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
		$filter['goods_id'] = $goods_id;
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'product_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);
		$filter['page_count'] = isset($filter['page_count']) ? $filter['page_count'] : 1;
		$where = '';

		if (!empty($filter['keyword'])) {
			$where .= ' AND (product_sn LIKE \'%' . $filter['keyword'] . '%\')';
		}

		$where .= $conditions;
		$where .= ' and area_id = \'' . $area_id . '\'';
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('products_area') . ' AS p WHERE goods_id = ' . $goods_id . ' ' . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$sql = "SELECT product_id, goods_id, goods_attr, product_sn, product_price, product_number, bar_code\r\n                FROM " . $GLOBALS['ecs']->table('products_area') . " AS g\r\n                WHERE goods_id = " . $goods_id . ' ' . $where . "\r\n                ORDER BY " . $filter['sort_by'] . ' ' . $filter['sort_order'];
		$filter['keyword'] = stripslashes($filter['keyword']);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);
	$goods_attr = product_goods_attr_list($goods_id);

	foreach ($row as $key => $value) {
		$_goods_attr_array = explode('|', $value['goods_attr']);

		if (is_array($_goods_attr_array)) {
			$_temp = '';

			foreach ($_goods_attr_array as $_goods_attr_value) {
				$_temp[] = $goods_attr[$_goods_attr_value];
			}

			$row[$key]['goods_attr'] = $_temp;
		}
	}

	return array('product' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function product_goods_attr_list($goods_id)
{
	if (empty($goods_id)) {
		return array();
	}

	$sql = 'SELECT goods_attr_id, attr_value FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE goods_id = \'' . $goods_id . '\'';
	$results = $GLOBALS['db']->getAll($sql);
	$return_arr = array();

	foreach ($results as $value) {
		$return_arr[$value['goods_attr_id']] = $value['attr_value'];
	}

	return $return_arr;
}

function get_product_warehouse_info($product_id, $filed = '')
{
	$return_array = array();

	if (empty($product_id)) {
		return $return_array;
	}

	$filed = trim($filed);

	if (empty($filed)) {
		$filed = '*';
	}

	$sql = 'SELECT ' . $filed . ' FROM  ' . $GLOBALS['ecs']->table('products') . ' WHERE product_id = \'' . $product_id . '\'';
	$return_array = $GLOBALS['db']->getRow($sql);
	return $return_array;
}

function check_product_area_sn_exist($product_sn, $product_id = 0, $ru_id = 0, $type = 0)
{
	$product_sn = trim($product_sn);
	$product_id = intval($product_id);

	if (strlen($product_sn) == 0) {
		return true;
	}

	if ($type == 1) {
		$sql = 'SELECT g.goods_id FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g WHERE g.bar_code=\'' . $product_sn . '\' AND g.user_id = \'' . $ru_id . '\'';

		if ($GLOBALS['db']->getOne($sql)) {
			return true;
		}
	}
	else {
		$sql = 'SELECT g.goods_id FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g WHERE g.goods_sn=\'' . $product_sn . '\' AND g.user_id = \'' . $ru_id . '\'';

		if ($GLOBALS['db']->getOne($sql)) {
			return true;
		}
	}

	$where = ' AND (SELECT g.user_id FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g WHERE g.goods_id = p.goods_id LIMIT 1) = \'' . $ru_id . '\'';

	if (empty($product_id)) {
		if ($type == 1) {
			$sql = 'SELECT p.product_id FROM ' . $GLOBALS['ecs']->table('products_area') . ' AS p ' . "\r\n                    WHERE p.bar_code = '" . $product_sn . '\'' . $where;
		}
		else {
			$sql = 'SELECT p.product_id FROM ' . $GLOBALS['ecs']->table('products_area') . ' AS p ' . "\r\n                    WHERE p.product_sn = '" . $product_sn . '\'' . $where;
		}
	}
	else if ($type == 1) {
		$sql = 'SELECT p.product_id FROM ' . $GLOBALS['ecs']->table('products_area') . ' AS p ' . "\r\n                    WHERE p.bar_code = '" . $product_sn . "'\r\n                    AND p.product_id <> '" . $product_id . '\'' . $where;
	}
	else {
		$sql = 'SELECT p.product_id FROM ' . $GLOBALS['ecs']->table('products_area') . ' AS p ' . "\r\n                    WHERE p.product_sn = '" . $product_sn . "'\r\n                    AND p.product_id <> '" . $product_id . '\'' . $where;
	}

	$res = $GLOBALS['db']->getOne($sql);

	if (empty($res)) {
		return false;
	}
	else {
		return true;
	}
}

function product_warehouse_number_count($goods_id, $conditions = '', $area_id = 0)
{
	if (empty($goods_id)) {
		return -1;
	}

	$sql = "SELECT product_number\r\n            FROM " . $GLOBALS['ecs']->table('products_area') . "\r\n            WHERE goods_id = '" . $goods_id . '\' and area_id = \'' . $area_id . "'\r\n            " . $conditions;
	$nums = $GLOBALS['db']->getOne($sql);
	$nums = (empty($nums) ? 0 : $nums);
	return $nums;
}

function update_warehouse_goods($goods_id, $field, $value)
{
	if ($goods_id) {
		clear_cache_files();
		$date = array('model_attr');
		$where = 'goods_id = \'' . $goods_id . '\'';
		$model_attr = get_table_date('goods', $where, $date, 2);

		if ($model_attr == 1) {
			$table = 'warehouse_goods';
			$field = 'region_number';
		}
		else if ($model_attr == 2) {
			$table = 'warehouse_area_goods';
			$field = 'region_number';
		}
		else {
			$table = 'goods';
		}

		$sql = 'UPDATE ' . $GLOBALS['ecs']->table($table) . ' SET ' . $field . ' = \'' . $value . '\' , last_update = \'' . gmtime() . '\' ' . 'WHERE goods_id ' . db_create_in($goods_id);
		return $GLOBALS['db']->query($sql);
	}
	else {
		return false;
	}
}

function handle_goods_attr($goods_id, $id_list, $is_spec_list, $value_price_list)
{
	$goods_attr_id = array();

	foreach ($id_list as $key => $id) {
		$is_spec = $is_spec_list[$key];

		if ($is_spec == 'false') {
			$value = $value_price_list[$key];
			$price = '';
		}
		else {
			$value_list = array();
			$price_list = array();

			if ($value_price_list[$key]) {
				$vp_list = explode(chr(13), $value_price_list[$key]);

				foreach ($vp_list as $v_p) {
					$arr = explode(chr(9), $v_p);
					$value_list[] = $arr[0];
					$price_list[] = $arr[1];
				}
			}

			$value = join(chr(13), $value_list);
			$price = join(chr(13), $price_list);
		}

		$sql = 'SELECT goods_attr_id FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE goods_id = \'' . $goods_id . '\' AND attr_id = \'' . $id . '\' AND attr_value = \'' . $value . '\' LIMIT 0, 1';
		$result_id = $GLOBALS['db']->getOne($sql);

		if (!empty($result_id)) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods_attr') . "\r\n                    SET attr_value = '" . $value . "'\r\n                    WHERE goods_id = '" . $goods_id . "'\r\n                    AND attr_id = '" . $id . "'\r\n                    AND goods_attr_id = '" . $result_id . '\'';
			$goods_attr_id[$id] = $result_id;
		}
		else {
			$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('goods_attr') . ' (goods_id, attr_id, attr_value, attr_price) ' . 'VALUES (\'' . $goods_id . '\', \'' . $id . '\', \'' . $value . '\', \'' . $price . '\')';
		}

		$GLOBALS['db']->query($sql);

		if ($goods_attr_id[$id] == '') {
			$goods_attr_id[$id] = $GLOBALS['db']->insert_id();
		}
	}

	return $goods_attr_id;
}

function check_goods_attr_exist($goods_attr, $goods_id, $product_id = 0, $area_id = 0)
{
	$goods_id = intval($goods_id);
	if ((strlen($goods_attr) == 0) || empty($goods_id)) {
		return true;
	}

	if (empty($product_id)) {
		$sql = 'SELECT product_id FROM ' . $GLOBALS['ecs']->table('products_area') . "\r\n                WHERE goods_attr = '" . $goods_attr . "'\r\n                AND goods_id = '" . $goods_id . '\' and area_id = \'' . $area_id . '\'';
	}
	else {
		$sql = 'SELECT product_id FROM ' . $GLOBALS['ecs']->table('products_area') . "\r\n                WHERE goods_attr = '" . $goods_attr . "'\r\n                AND goods_id = '" . $goods_id . "'\r\n                AND product_id <> '" . $product_id . '\' and area_id = \'' . $area_id . '\'';
	}

	$res = $GLOBALS['db']->getOne($sql);

	if (empty($res)) {
		return false;
	}
	else {
		return true;
	}
}

function check_goods_sn_exist($goods_sn, $goods_id = 0)
{
	$goods_sn = trim($goods_sn);
	$goods_id = intval($goods_id);

	if (strlen($goods_sn) == 0) {
		return true;
	}

	if (empty($goods_id)) {
		$sql = 'SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . "\r\n                WHERE goods_sn = '" . $goods_sn . '\'';
	}
	else {
		$sql = 'SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . "\r\n                WHERE goods_sn = '" . $goods_sn . "'\r\n                AND goods_id <> '" . $goods_id . '\'';
	}

	$res = $GLOBALS['db']->getOne($sql);

	if (empty($res)) {
		return false;
	}
	else {
		return true;
	}
}

function check_product_sn_exist($product_sn, $product_id = 0)
{
	$product_sn = trim($product_sn);
	$product_id = intval($product_id);

	if (strlen($product_sn) == 0) {
		return true;
	}

	$sql = 'SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . 'WHERE goods_sn=\'' . $product_sn . '\'';

	if ($GLOBALS['db']->getOne($sql)) {
		return true;
	}

	if (empty($product_id)) {
		$sql = 'SELECT product_id FROM ' . $GLOBALS['ecs']->table('products_area') . "\r\n                WHERE product_sn = '" . $product_sn . '\'';
	}
	else {
		$sql = 'SELECT product_id FROM ' . $GLOBALS['ecs']->table('products_area') . "\r\n                WHERE product_sn = '" . $product_sn . "'\r\n                AND product_id <> '" . $product_id . '\'';
	}

	$res = $GLOBALS['db']->getOne($sql);

	if (empty($res)) {
		return false;
	}
	else {
		return true;
	}
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ($_REQUEST['act'] == 'warehouse_list') {
	admin_priv('goods_manage');
	$smarty->assign('ur_here');
	$goods_id = (isset($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : 0);
	$date = array('goods_name');
	$where = 'goods_id = \'' . $goods_id . '\'';
	$goods_name = get_table_date('goods', $where, $date, 2);
	$smarty->assign('ur_here', $goods_name);
	$smarty->assign('action_link', array('text' => '商品列表', 'href' => 'goods.php?act=list'));
	$area_list = area_product_list($goods_id);
	$_SESSION['warehouse_goods_id'] = $goods_id;
	$smarty->assign('goods_id', $goods_id);
	$smarty->assign('area_list', $area_list['area_list']);
	$smarty->assign('full_page', 1);
	$smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');
	assign_query_info();
	$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => '01_goods_list'));
	$smarty->display('goods_area_attr_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$area_list = area_product_list();
	$smarty->assign('goods_id', $_SESSION['warehouse_goods_id']);
	$smarty->assign('area_list', $area_list['area_list']);
	$smarty->assign('filter', $area_list['filter']);
	$smarty->assign('record_count', $area_list['record_count']);
	$smarty->assign('page_count', $area_list['page_count']);
	$sort_flag = sort_flag($area_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('goods_area_attr_list.dwt'), '', array('filter' => $area_list['filter'], 'page_count' => $area_list['page_count']));
}
else if ($_REQUEST['act'] == 'product_list') {
	admin_priv('goods_manage');
	$goods_id = (isset($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : 0);
	$area_id = (isset($_REQUEST['area_id']) ? $_REQUEST['area_id'] : 0);
	$_SESSION['product_area'] = $area_id;

	if (empty($goods_id)) {
		$link[] = array('href' => 'goods.php?act=list', 'text' => $_LANG['cannot_found_goods']);
		sys_msg($_LANG['cannot_found_goods'], 1, $link);
	}
	else {
		$goods_id = intval($goods_id);
	}

	$sql = 'SELECT goods_sn, goods_name, goods_type, shop_price, model_attr FROM ' . $ecs->table('goods') . ' WHERE goods_id = \'' . $goods_id . '\'';
	$goods = $db->getRow($sql);

	if (empty($goods)) {
		$link[] = array('href' => 'goods.php?act=list', 'text' => $_LANG['01_goods_list']);
		sys_msg($_LANG['cannot_found_goods'], 1, $link);
	}

	$smarty->assign('sn', sprintf($_LANG['good_goods_sn'], $goods['goods_sn']));
	$smarty->assign('price', sprintf($_LANG['good_shop_price'], $goods['shop_price']));
	$smarty->assign('goods_name', sprintf($_LANG['products_title'], $goods['goods_name']));
	$smarty->assign('goods_sn', sprintf($_LANG['products_title_2'], $goods['goods_sn']));
	$smarty->assign('model_attr', $goods['model_attr']);
	$region_name = get_table_date('region_warehouse', 'region_id = \'' . $area_id . '\'', array('region_name'), 2);
	$smarty->assign('region_name', $region_name);
	$smarty->assign('area_id', $area_id);
	$attribute = get_goods_specifications_list($goods_id);

	if (empty($attribute)) {
		$link[] = array('href' => 'goods.php?act=edit&goods_id=' . $goods_id, 'text' => $_LANG['edit_goods']);
		sys_msg($_LANG['not_exist_goods_attr'], 1, $link);
	}

	foreach ($attribute as $attribute_value) {
		$_attribute[$attribute_value['attr_id']]['attr_values'][] = $attribute_value['attr_value'];
		$_attribute[$attribute_value['attr_id']]['attr_id'] = $attribute_value['attr_id'];
		$_attribute[$attribute_value['attr_id']]['attr_name'] = $attribute_value['attr_name'];
	}

	$attribute_count = count($_attribute);
	$smarty->assign('attribute_count', $attribute_count);
	$smarty->assign('attribute_count_3', $attribute_count + 3);
	$smarty->assign('attribute', $_attribute);
	$smarty->assign('product_sn', $goods['goods_sn'] . '_');
	$smarty->assign('product_number', $_CFG['default_storage']);
	$product = product_area_list($goods_id, '', $area_id);
	$smarty->assign('ur_here', $_LANG['18_product_list']);
	$smarty->assign('action_link', array('href' => 'goods.php?act=list', 'text' => $_LANG['01_goods_list']));
	$smarty->assign('product_list', $product['product']);
	$smarty->assign('product_null', empty($product['product']) ? 0 : 1);
	$smarty->assign('use_storage', empty($_CFG['use_storage']) ? 0 : 1);
	$smarty->assign('goods_id', $goods_id);
	$smarty->assign('filter', $product['filter']);
	$smarty->assign('full_page', 1);
	$smarty->assign('product_php', 'goods_area_attr.php');
	$smarty->assign('batch_php', 'goods_produts_area_batch.php');
	assign_query_info();
	$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => '01_goods_list'));
	$smarty->display('product_info.dwt');
}
else if ($_REQUEST['act'] == 'product_query') {
	$area_id = (isset($_REQUEST['area_id']) ? $_REQUEST['area_id'] : 0);

	if (empty($_REQUEST['goods_id'])) {
		make_json_error($_LANG['sys']['wrong'] . $_LANG['cannot_found_goods']);
	}
	else {
		$goods_id = intval($_REQUEST['goods_id']);
	}

	$sql = 'SELECT goods_sn, goods_name, goods_type, shop_price FROM ' . $ecs->table('goods') . ' WHERE goods_id = \'' . $goods_id . '\'';
	$goods = $db->getRow($sql);

	if (empty($goods)) {
		make_json_error($_LANG['sys']['wrong'] . $_LANG['cannot_found_goods']);
	}

	$smarty->assign('sn', sprintf($_LANG['good_goods_sn'], $goods['goods_sn']));
	$smarty->assign('price', sprintf($_LANG['good_shop_price'], $goods['shop_price']));
	$smarty->assign('goods_name', sprintf($_LANG['products_title'], $goods['goods_name']));
	$smarty->assign('goods_sn', sprintf($_LANG['products_title_2'], $goods['goods_sn']));
	$region_name = get_table_date('region_warehouse', 'region_id = \'' . $area_id . '\'', array('region_name'), 2);
	$smarty->assign('region_name', $region_name);
	$smarty->assign('warehouse_id', $warehouse_id);
	$attribute = get_goods_specifications_list($goods_id);

	if (empty($attribute)) {
		make_json_error($_LANG['sys']['wrong'] . $_LANG['cannot_found_goods']);
	}

	foreach ($attribute as $attribute_value) {
		$_attribute[$attribute_value['attr_id']]['attr_values'][] = $attribute_value['attr_value'];
		$_attribute[$attribute_value['attr_id']]['attr_id'] = $attribute_value['attr_id'];
		$_attribute[$attribute_value['attr_id']]['attr_name'] = $attribute_value['attr_name'];
	}

	$attribute_count = count($_attribute);
	$smarty->assign('attribute_count', $attribute_count);
	$smarty->assign('attribute', $_attribute);
	$smarty->assign('attribute_count_3', $attribute_count + 10);
	$smarty->assign('product_sn', $goods['goods_sn'] . '_');
	$smarty->assign('product_number', $_CFG['default_storage']);
	$product = product_area_list($goods_id, '', $area_id);
	$smarty->assign('ur_here', $_LANG['18_product_list']);
	$smarty->assign('action_link', array('href' => 'goods.php?act=list', 'text' => $_LANG['01_goods_list']));
	$smarty->assign('product_list', $product['product']);
	$smarty->assign('use_storage', empty($_CFG['use_storage']) ? 0 : 1);
	$smarty->assign('goods_id', $goods_id);
	$smarty->assign('filter', $product['filter']);
	$smarty->assign('product_php', 'goods_area_attr.php');
	$sort_flag = sort_flag($product['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('product_info.dwt'), '', array('filter' => $product['filter'], 'page_count' => $product['page_count']));
}
else if ($_REQUEST['act'] == 'edit_product_sn') {
	check_authz_json('goods_manage');
	$product_id = intval($_REQUEST['id']);
	$product_sn = json_str_iconv(trim($_POST['val']));
	$product_sn = ($_LANG['n_a'] == $product_sn ? '' : $product_sn);

	if (check_product_area_sn_exist($product_sn, $product_id, $adminru['ru_id'])) {
		make_json_error($_LANG['sys']['wrong'] . $_LANG['exist_same_product_sn']);
	}

	$sql = 'UPDATE ' . $ecs->table('products_area') . ' SET product_sn = \'' . $product_sn . '\' WHERE product_id = \'' . $product_id . '\'';
	$result = $db->query($sql);

	if ($result) {
		clear_cache_files();
		make_json_result($product_sn);
	}
}
else if ($_REQUEST['act'] == 'edit_bar_code') {
	check_authz_json('goods_manage');
	$product_id = intval($_REQUEST['id']);
	$bar_code = json_str_iconv(trim($_POST['val']));

	if (check_product_area_sn_exist($bar_code, $product_id, $adminru['ru_id'], 1)) {
		make_json_error($_LANG['sys']['wrong'] . $_LANG['exist_same_bar_code']);
	}

	$sql = 'UPDATE ' . $ecs->table('products_area') . ' SET bar_code = \'' . $bar_code . '\' WHERE product_id = \'' . $product_id . '\'';
	$result = $db->query($sql);

	if ($result) {
		clear_cache_files();
		make_json_result($bar_code);
	}
}
else if ($_REQUEST['act'] == 'edit_product_number') {
	check_authz_json('goods_manage');
	$product_id = intval($_POST['id']);
	$product_number = intval($_POST['val']);
	$product = get_product_area_info($product_id, 'product_number, area_id, goods_id');

	if ($product['product_number'] != $product_number) {
		if ($product_number < $product['product_number']) {
			$number = $product['product_number'] - $product_number;
			$number = '- ' . $number;
			$log_use_storage = 10;
		}
		else {
			$number = $product_number - $product['product_number'];
			$number = '+ ' . $number;
			$log_use_storage = 11;
		}

		$goods = get_admin_goods_info($product['goods_id']);
		$logs_other = array('goods_id' => $product['goods_id'], 'order_id' => 0, 'use_storage' => $log_use_storage, 'admin_id' => $_SESSION['seller_id'], 'number' => $number, 'model_inventory' => $goods['model_inventory'], 'model_attr' => $goods['model_attr'], 'product_id' => $product_id, 'warehouse_id' => 0, 'area_id' => $product['area_id'], 'add_time' => gmtime());
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('goods_inventory_logs'), $logs_other, 'INSERT');
	}

	$sql = 'UPDATE ' . $ecs->table('products_area') . ' SET product_number = \'' . $product_number . '\' WHERE product_id = \'' . $product_id . '\'';
	$result = $db->query($sql);

	if ($result) {
		clear_cache_files();
		make_json_result($product_number);
	}
}
else if ($_REQUEST['act'] == 'product_remove') {
	check_authz_json('remove_back');
	$product_id = intval($_REQUEST['id']);

	if (empty($product_id)) {
		make_json_error($_LANG['product_id_null']);
	}
	else {
		$product_id = intval($product_id);
	}

	$product = get_product_warehouse_info($product_id, 'product_number, goods_id');
	$sql = 'DELETE FROM ' . $ecs->table('products_area') . ' WHERE product_id = \'' . $product_id . '\'';
	$result = $db->query($sql);

	if ($result) {
		$url = 'goods_warehouse_attr.php?act=product_query&warehouse_id=' . $_SESSION['product_area'] . '&' . str_replace('act=product_remove', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
		exit();
	}
}
else if ($_REQUEST['act'] == 'product_add_execute') {
	admin_priv('goods_manage');
	$product['goods_id'] = intval($_POST['goods_id']);
	$product['attr'] = $_POST['attr'];
	$product['product_sn'] = $_POST['product_sn'];
	$product['bar_code'] = $_POST['bar_code'];
	$product['product_price'] = $_POST['product_price'];
	$product['product_number'] = $_POST['product_number'];
	$product['area_id'] = $_POST['area_id'];

	if (empty($product['goods_id'])) {
		sys_msg($_LANG['sys']['wrong'] . $_LANG['cannot_found_goods'], 1, array(), false);
	}

	$insert = true;

	if (0 < product_warehouse_number_count($product['goods_id'], '', $product['area_id'])) {
		$insert = false;
	}

	$sql = 'SELECT goods_sn, goods_name, goods_type, shop_price, model_inventory, model_attr FROM ' . $ecs->table('goods') . ' WHERE goods_id = \'' . $product['goods_id'] . '\'';
	$goods = $db->getRow($sql);

	if (empty($goods)) {
		sys_msg($_LANG['sys']['wrong'] . $_LANG['cannot_found_goods'], 1, array(), false);
	}

	foreach ($product['product_sn'] as $key => $value) {
		$product['product_number'][$key] = empty($product['product_number'][$key]) ? (empty($_CFG['use_storage']) ? 0 : $_CFG['default_storage']) : trim($product['product_number'][$key]);

		foreach ($product['attr'] as $attr_key => $attr_value) {
			if (empty($attr_value[$key])) {
				continue 2;
			}

			$is_spec_list[$attr_key] = 'true';
			$value_price_list[$attr_key] = $attr_value[$key] . chr(9) . '';
			$id_list[$attr_key] = $attr_key;
		}

		$goods_attr_id = handle_goods_attr($product['goods_id'], $id_list, $is_spec_list, $value_price_list);
		$goods_attr = sort_goods_attr_id_array($goods_attr_id);
		$goods_attr = implode('|', $goods_attr['sort']);

		if (check_goods_attr_exist($goods_attr, $product['goods_id'], 0, $product['area_id'])) {
			continue;
		}

		if (!empty($value)) {
			if (check_goods_sn_exist($value)) {
				continue;
			}

			if (check_product_sn_exist($value)) {
				continue;
			}
		}

		$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('products_area') . ' (goods_id, goods_attr, product_sn, bar_code, product_price, product_number, area_id)  VALUES (\'' . $product['goods_id'] . '\', \'' . $goods_attr . '\', \'' . $value . '\', \'' . $product['bar_code'][$key] . '\', \'' . $product['product_price'][$key] . '\', \'' . $product['product_number'][$key] . '\', \'' . $product['area_id'] . '\')';

		if (!$GLOBALS['db']->query($sql)) {
			continue;
		}

		$number = '+ ' . $product['product_number'][$key];

		if ($product['product_number'][$key]) {
			$logs_other = array('goods_id' => $product['goods_id'], 'order_id' => 0, 'use_storage' => 9, 'admin_id' => $_SESSION['seller_id'], 'number' => $number, 'model_inventory' => $goods['model_inventory'], 'model_attr' => $goods['model_attr'], 'product_id' => $GLOBALS['db']->insert_id(), 'warehouse_id' => 0, 'area_id' => $product['area_id'], 'add_time' => gmtime());
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('goods_inventory_logs'), $logs_other, 'INSERT');
		}

		if (empty($value)) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('products_area') . "\r\n                    SET product_sn = '" . $goods['goods_sn'] . 'g_p' . $GLOBALS['db']->insert_id() . "'\r\n                    WHERE product_id = '" . $GLOBALS['db']->insert_id() . '\'';
			$GLOBALS['db']->query($sql);
		}

		$product_count = product_warehouse_number_count($product['goods_id'], '', $product['area_id']);

		if (update_warehouse_goods($product['goods_id'], 'goods_number', $product_count)) {
			admin_log($product['goods_id'], 'update', 'goods');
		}
	}

	clear_cache_files();

	if ($insert) {
		$link[] = array('href' => 'goods.php?act=add', 'text' => $_LANG['02_goods_add']);
		$link[] = array('href' => 'goods.php?act=list', 'text' => $_LANG['01_goods_list']);
		$link[] = array('href' => 'goods_area_attr.php?act=product_list&goods_id=' . $product['goods_id'] . '&area_id=' . $product['area_id'], 'text' => $_LANG['18_product_list']);
	}
	else {
		$link[] = array('href' => 'goods.php?act=list&uselastfilter=1', 'text' => $_LANG['01_goods_list']);
		$link[] = array('href' => 'goods.php?act=edit&goods_id=' . $product['goods_id'], 'text' => $_LANG['edit_goods']);
		$link[] = array('href' => 'goods_area_attr.php?act=product_list&goods_id=' . $product['goods_id'] . '&area_id=' . $product['area_id'], 'text' => $_LANG['18_product_list']);
	}

	sys_msg($_LANG['save_products'], 0, $link);
}

?>
