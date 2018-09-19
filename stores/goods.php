<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function cat_list_one_new($cat_id = 0, $cat_level = 0)
{
	if (0 < $cat_id) {
		$arr = cat_list($cat_id);

		foreach ($arr as $key => $value) {
			if ($key == $cat_id) {
				unset($arr[$cat_id]);
			}
		}

		$str = '';

		if ($arr) {
			$cat_level++;
			$str .= '<div id="cat_id' . $cat_level . '" class="imitate_select w150 ml10"><div class="cite">分类</div><ul>';
			$str .= '<li><a href="javascript:;" data-value="-1" data-level="' . $cat_level . '" class="ftx-01">全部分类</a></li>';

			foreach ($arr as $key1 => $value1) {
				$str .= '<li><a href="javascript:;" data-value="' . $value1['cat_id'] . '" data-level="' . $cat_level . '" class="ftx-01">' . $value1['cat_name'] . '</a></li>';
			}

			$str .= '</ul><input type="hidden" value="" id="cat_id_val' . $cat_level . '"></div>';
		}

		return $str;
	}
}

function store_goods_list($ru_id = 0, $store_id = 0)
{
	$filter = array();
	$filter['keyword'] = !empty($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
	$filter['cat_id'] = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : -1;
	$filter['brand_id'] = !empty($_REQUEST['brand_id']) ? intval($_REQUEST['brand_id']) : -1;
	$filter['goods_type'] = !empty($_REQUEST['goods_type']) ? intval($_REQUEST['goods_type']) : -1;
	if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
		$filter['keyword'] = json_str_iconv($filter['keyword']);
	}

	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'g.goods_id' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$where = ' WHERE 1 AND g.is_delete = 0 AND g.is_real = 1 ';

	if ($filter['cat_id'] != -1) {
		$where .= ' AND (' . get_children($filter['cat_id']) . ')';
	}

	if ($filter['brand_id'] != -1) {
		$where .= ' AND (g.brand_id=\'' . $filter['brand_id'] . '\')';
	}

	if (!empty($filter['keyword'])) {
		$where .= ' AND (g.goods_sn LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\' OR g.goods_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\'' . ')';
	}

	if (0 < $ru_id) {
		$where .= ' and g.user_id = \'' . $ru_id . '\' ';
	}
	else {
		$where .= ' and g.user_id = \'0\' ';
	}

	if ($filter['goods_type'] != -1) {
		$goods_ids = get_number_goods_id($store_id);

		if ($filter['goods_type'] == 1) {
			$where .= ' AND g.goods_id in (' . $goods_ids . ')';
		}
		else if ($filter['goods_type'] == 2) {
			$where .= ' AND g.goods_id not in (' . $goods_ids . ')';
		}
	}

	$sql = ' SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$arr = array();
	$sql = 'SELECT g.* ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $where . 'ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$http = $GLOBALS['ecs']->http();
	$idx = 0;

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$rows['have_goods_attr'] = have_goods_attr($rows['goods_id']);
		$rows['formated_shop_price'] = price_format($rows['shop_price']);
		$rows['store_goods_number'] = get_store_goods_number($rows['goods_id'], $store_id);
		$rows['goods_thumb'] = get_image_path($rows['goods_id'], $rows['goods_thumb'], true);
		$arr[$idx] = $rows;
		$idx++;
	}

	return array('goods_list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_store_goods_number($goods_id = 0, $store_id = 0)
{
	if (0 < $store_id && 0 < $goods_id) {
		$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('store_goods') . (' WHERE store_id = \'' . $store_id . '\' AND goods_id = \'' . $goods_id . '\' LIMIT 1 ');
		$goods_info = $GLOBALS['db']->getRow($sql);
		$product_number = get_store_product_amount($goods_id, $store_id);
		if ($goods_info || $product_number) {
			if ($product_number != false) {
				return $product_number;
			}
			else {
				return $goods_info['goods_number'];
			}
		}
		else {
			return 0;
		}
	}
	else {
		return 0;
	}
}

function have_goods_attr($goods_id = 0)
{
	$attr_num = get_goods_attr_id(array('goods_id' => $goods_id), array('ga.goods_attr_id'), 1);
	return $attr_num;
}

function have_goods_products($goods_id = 0, $store_id = 0)
{
	$sql = ' SELECT product_id FROM ' . $GLOBALS['ecs']->table('store_products') . (' WHERE goods_id = \'' . $goods_id . '\' AND store_id = \'' . $store_id . '\' ');
	$have_data = $GLOBALS['db']->getOne($sql, true);
	return $have_data;
}

function get_number_goods_id($store_id)
{
	$sql = ' SELECT goods_id FROM ' . $GLOBALS['ecs']->table('store_goods') . (' WHERE store_id = \'' . $store_id . '\' AND goods_number > 0  ');
	$store_goods = $GLOBALS['db']->getAll($sql);
	$sql = ' SELECT goods_id FROM ' . $GLOBALS['ecs']->table('store_products') . (' WHERE store_id = \'' . $store_id . '\' AND product_number > 0  ');
	$products_goods = $GLOBALS['db']->getAll($sql);
	$store_goods_arr = array();
	$products_goods_arr = array();

	if ($store_goods) {
		$store_goods_arr = arr_foreach($store_goods);
	}

	if ($products_goods) {
		$products_goods_arr = arr_foreach($products_goods);
	}

	$arr = '';
	if (!empty($store_goods_arr) && empty($products_goods_arr)) {
		$arr = implode(',', $store_goods_arr);
	}
	else {
		if (empty($store_goods_arr) && !empty($products_goods_arr)) {
			$arr = implode(',', $products_goods_arr);
		}
		else {
			if (!empty($store_goods_arr) && !empty($products_goods_arr)) {
				$arr = implode(',', array_unique(array_merge($store_goods_arr, $products_goods_arr)));
			}
		}
	}

	if ($arr == '') {
		$arr = 0;
	}

	return $arr;
}

function get_store_product_amount($goods_id = 0, $store_id = 0)
{
	if (have_goods_products($goods_id, $store_id)) {
		$sql = ' SELECT SUM(product_number) FROM ' . $GLOBALS['ecs']->table('store_products') . (' WHERE goods_id = \'' . $goods_id . '\' AND store_id = \'' . $store_id . '\' ');
		$product_number = $GLOBALS['db']->getOne($sql);
		return $product_number;
	}
	else {
		return false;
	}
}

function get_default_store_goods_number($goods_id = 0, $store_id = 0)
{
	$sql = ' SELECT goods_number FROM ' . $GLOBALS['ecs']->table('store_goods') . (' WHERE store_id = \'' . $store_id . '\' AND goods_id = \'' . $goods_id . '\' LIMIT 1 ');
	$goods_number = $GLOBALS['db']->getOne($sql);
	return $goods_number;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . '/' . STORES_PATH . '/includes/lib_goods.php';
$exc = new exchange($ecs->table('goods'), $db, 'goods_id', 'goods_name');
$adminru = get_store_ru_id();

if ($adminru == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$smarty->assign('review_goods', $GLOBALS['_CFG']['review_goods']);
$store_id = isset($_SESSION['stores_id']) ? intval($_SESSION['stores_id']) : 0;
$ru_id = $GLOBALS['db']->getOne(' SELECT ru_id FROM ' . $GLOBALS['ecs']->table('offline_store') . (' WHERE id = \'' . $store_id . '\''), true);
$smarty->assign('app', 'goods');
$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'stores_logo\'';
$stores_logo = strstr($GLOBALS['db']->getOne($sql), 'images');
$smarty->assign('stores_logo', $stores_logo);

if ($_REQUEST['act'] == 'list') {
	store_priv('goods_manage');
	$list = store_goods_list($ru_id, $store_id);
	$smarty->assign('goods_list', $list['goods_list']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->assign('brand_list', get_brand_list(0, 0, $adminru));
	set_default_filter(0, 0, $adminru);
	$smarty->assign('page_title', $_LANG['store_goods']);
	$smarty->display('goods_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$list = store_goods_list($ru_id, $store_id);
	$smarty->assign('goods_list', $list['goods_list']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('goods_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else if ($_REQUEST['act'] == 'info') {
	store_priv('goods_manage');

	if (empty($_GET['goods_id'])) {
		$link[] = array('href' => 'goods.php?act=list', 'text' => $_LANG['cannot_found_goods']);
		sys_msg($_LANG['cannot_found_goods'], 1, $link);
	}
	else {
		$goods_id = intval($_GET['goods_id']);
	}

	$sql = 'SELECT goods_sn, goods_name, goods_type, shop_price, model_attr, goods_thumb FROM ' . $ecs->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\'');
	$goods = $db->getRow($sql);
	$http = $GLOBALS['ecs']->http();

	if (isset($goods['goods_thumb'])) {
		$goods['goods_thumb'] = get_image_path($goods['goods_id'], $goods['goods_thumb'], true);
	}

	if (empty($goods)) {
		$link[] = array('href' => 'goods.php?act=list', 'text' => $_LANG['01_goods_list']);
		sys_msg($_LANG['cannot_found_goods'], 1, $link);
	}

	$smarty->assign('goods', $goods);
	$smarty->assign('sn', sprintf($_LANG['good_goods_sn'], $goods['goods_sn']));
	$smarty->assign('price', sprintf($_LANG['good_shop_price'], $goods['shop_price']));
	$smarty->assign('goods_name', sprintf($_LANG['products_title'], $goods['goods_name']));
	$smarty->assign('goods_sn', sprintf($_LANG['products_title_2'], $goods['goods_sn']));
	$smarty->assign('model_attr', $goods['model_attr']);
	$have_goods_attr = have_goods_attr($goods_id);
	$smarty->assign('have_goods_attr', $have_goods_attr);

	if ($have_goods_attr) {
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
		$product = product_list($goods_id, ' AND store_id = \'' . $store_id . '\' ');
		$smarty->assign('ur_here', $_LANG['18_product_list']);
		$smarty->assign('action_link', array('href' => 'goods.php?act=list', 'text' => $_LANG['01_goods_list']));
		$smarty->assign('product_list', $product['product']);
		$smarty->assign('product_null', empty($product['product']) ? 0 : 1);
		$smarty->assign('use_storage', empty($_CFG['use_storage']) ? 0 : 1);
		$smarty->assign('filter', $product['filter']);
		$smarty->assign('more_count', $product['filter']['record_count'] + 1);
		$smarty->assign('product_php', 'goods.php');
	}

	$smarty->assign('goods_number', get_default_store_goods_number($goods_id, $store_id));
	$smarty->assign('full_page', 1);
	$smarty->assign('page', intval($_REQUEST['page']));
	$smarty->assign('goods_id', $goods_id);
	assign_query_info();
	$smarty->assign('page_title', $_LANG['set_inventory']);
	$smarty->display('goods_info.dwt');
}
else if ($_REQUEST['act'] == 'check_products_goods_sn') {
	$goods_id = intval($_REQUEST['goods_id']);
	$goods_sn = json_str_iconv(trim($_REQUEST['goods_sn']));
	$products_sn = explode('||', $goods_sn);

	if (!is_array($products_sn)) {
		make_json_result('');
	}
	else {
		foreach ($products_sn as $val) {
			if (empty($val)) {
				continue;
			}

			if (is_array($int_arry)) {
				if (in_array($val, $int_arry)) {
					make_json_error($val . $_LANG['goods_sn_exists']);
				}
			}

			$int_arry[] = $val;

			if (!$exc->is_only('goods_sn', $val, '0')) {
				make_json_error($val . $_LANG['goods_sn_exists']);
			}

			$sql = 'SELECT goods_id FROM ' . $ecs->table('store_products') . ('WHERE product_sn=\'' . $val . '\'');

			if ($db->getOne($sql)) {
				make_json_error($val . $_LANG['goods_sn_exists']);
			}
		}
	}

	make_json_result('');
}
else if ($_REQUEST['act'] == 'product_add_execute') {
	store_priv('goods_manage');
	$goods_id = intval($_POST['goods_id']);
	$page = intval($_POST['page']);
	$goods_number = empty($_POST['goods_number']) ? 0 : intval($_POST['goods_number']);
	$have_goods_attr = have_goods_attr($goods_id);
	$where = ' AND goods_id = \'' . $goods_id . '\' AND store_id = \'' . $store_id . '\' ';
	$sql = ' SELECT id FROM ' . $GLOBALS['ecs']->table('store_goods') . ' WHERE 1 ' . $where;
	$have_data = $GLOBALS['db']->getOne($sql);

	if ($have_data) {
		$sql = ' UPDATE ' . $GLOBALS['ecs']->table('store_goods') . (' SET goods_number = \'' . $goods_number . '\' WHERE 1 ') . $where;
		$GLOBALS['db']->query($sql);
	}
	else {
		$sql = ' INSERT INTO ' . $GLOBALS['ecs']->table('store_goods') . ' (id, goods_id, store_id, ru_id, goods_number) VALUES ' . (' (NULL, \'' . $goods_id . '\', \'' . $store_id . '\', \'' . $ru_id . '\', \'' . $goods_number . '\') ');
		$GLOBALS['db']->query($sql);
	}

	if ($have_goods_attr) {
		$product['goods_id'] = intval($_POST['goods_id']);
		$product['attr'] = $_POST['attr'];
		$product['product_sn'] = $_POST['product_sn'];
		$product['product_number'] = $_POST['product_number'];

		if (empty($product['goods_id'])) {
			make_json_response('', 0, $_LANG['sys']['wrong'] . $_LANG['cannot_found_goods']);
		}

		$sql = 'SELECT goods_sn, goods_name, goods_type, shop_price FROM ' . $ecs->table('goods') . ' WHERE goods_id = \'' . $product['goods_id'] . '\'';
		$goods = $db->getRow($sql);

		if (empty($goods)) {
			make_json_response('', 0, $_LANG['sys']['wrong'] . $_LANG['cannot_found_goods']);
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

			if (check_goods_attr_exist($goods_attr, $product['goods_id'])) {
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

			$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('store_products') . ' (goods_id, goods_attr, product_sn, product_number, ru_id, store_id)  VALUES (\'' . $product['goods_id'] . ('\', \'' . $goods_attr . '\', \'' . $value . '\', \'') . $product['product_number'][$key] . ('\', \'' . $ru_id . '\', \'' . $store_id . '\' )');

			if (!$GLOBALS['db']->query($sql)) {
				continue;
			}

			if (empty($value)) {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('store_products') . "\r\n\t\t\t\t\t\tSET product_sn = '" . $goods['goods_sn'] . 'g_p' . $GLOBALS['db']->insert_id() . "'\r\n\t\t\t\t\t\tWHERE product_id = '" . $GLOBALS['db']->insert_id() . '\'';
				$GLOBALS['db']->query($sql);
			}
		}

		clear_cache_files();
	}

	make_json_response('', 1, $_LANG['edit_succeed'], array('url' => 'goods.php?act=list&page=' . $page, 'page' => $page));
}
else if ($_REQUEST['act'] == 'product_remove') {
	store_priv('goods_manage');
	$id_val = $_REQUEST['id'];
	$id_val = explode(',', $id_val);
	$product_id = intval($id_val[0]);
	$warehouse_id = intval($id_val[1]);

	if (empty($product_id)) {
		make_json_error($_LANG['product_id_null']);
	}
	else {
		$product_id = intval($product_id);
	}

	$product = get_product_info($product_id, 'product_number, goods_id');
	$sql = 'DELETE FROM ' . $ecs->table('store_products') . (' WHERE product_id = \'' . $product_id . '\'');
	$result = $db->query($sql);

	if ($result) {
		$url = 'goods.php?act=product_query&' . str_replace('act=product_remove', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
		exit();
	}
}
else if ($_REQUEST['act'] == 'product_query') {
	if (empty($_REQUEST['goods_id'])) {
		make_json_error($_LANG['sys']['wrong'] . $_LANG['cannot_found_goods']);
	}
	else {
		$goods_id = intval($_REQUEST['goods_id']);
	}

	$have_goods_attr = have_goods_attr($goods_id);
	$smarty->assign('have_goods_attr', $have_goods_attr);

	if ($have_goods_attr) {
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
		$product = product_list($goods_id, ' AND store_id = \'' . $store_id . '\' ');
		$smarty->assign('ur_here', $_LANG['18_product_list']);
		$smarty->assign('action_link', array('href' => 'goods.php?act=list', 'text' => $_LANG['01_goods_list']));
		$smarty->assign('product_list', $product['product']);
		$smarty->assign('product_null', empty($product['product']) ? 0 : 1);
		$smarty->assign('use_storage', empty($_CFG['use_storage']) ? 0 : 1);
		$smarty->assign('filter', $product['filter']);
		$smarty->assign('more_count', $product['filter']['record_count'] + 1);
		$smarty->assign('product_php', 'goods.php');
	}

	$sort_flag = sort_flag($product['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('goods_info.dwt'), '', array('filter' => $product['filter'], 'page_count' => $product['page_count']));
}
else if ($_REQUEST['act'] == 'edit_product_sn') {
	$product_id = intval($_REQUEST['id']);
	$product_sn = json_str_iconv(trim($_POST['val']));
	$product_sn = $_LANG['n_a'] == $product_sn ? '' : $product_sn;

	if (check_product_sn_exist($product_sn, $product_id, $adminru['ru_id'], $store_id)) {
		make_json_error($_LANG['sys']['wrong'] . $_LANG['exist_same_product_sn']);
	}

	$sql = 'UPDATE ' . $ecs->table('store_products') . (' SET product_sn = \'' . $product_sn . '\' WHERE product_id = \'' . $product_id . '\'');
	$result = $db->query($sql);

	if ($result) {
		clear_cache_files();
		make_json_result($product_sn);
	}
}
else if ($_REQUEST['act'] == 'edit_product_number') {
	$product_id = intval($_POST['id']);
	$product_number = intval($_POST['val']);
	$product = get_product_info($product_id, 'product_number, goods_id');
	$sql = 'UPDATE ' . $ecs->table('store_products') . (' SET product_number = \'' . $product_number . '\' WHERE product_id = \'' . $product_id . '\'');
	$result = $db->query($sql);

	if ($result) {
		clear_cache_files();
		make_json_result($product_number);
	}
}
else if ($_REQUEST['act'] == 'sel_cat_goodslist') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$res = array('error' => 0, 'message' => '', 'cat_level' => 0, 'content' => '');
	$cat_id = !empty($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
	$cat_level = !empty($_GET['cat_level']) ? intval($_GET['cat_level']) : 0;

	if (0 < $cat_id) {
		$arr = cat_list_one_new($cat_id, $cat_level);
	}

	$res['content'] = $arr;
	$res['parent_id'] = $cat_id;
	$res['cat_level'] = $cat_level;
	echo $json->encode($res);
	exit();
}
else if ($_REQUEST['act'] == 'batch_goods_number') {
	$checkboxes = !empty($_REQUEST['checkboxes']) ? $_REQUEST['checkboxes'] : '';
	$page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 0;

	if (!empty($checkboxes)) {
		foreach ($checkboxes as $v) {
			$sql = 'SELECT goods_id,goods_sn FROM' . $ecs->table('goods') . (' WHERE goods_id = \'' . $v . '\'');
			$goods = $db->getRow($sql);

			if (0 < $goods['goods_id']) {
				$sql = 'DELETE FROM' . $ecs->table('store_goods') . ('WHERE goods_id = \'' . $v . '\' AND store_id = \'' . $store_id . '\'');
				$db->query($sql);
				$sql = 'DELETE FROM' . $ecs->table('store_products') . ('WHERE goods_id = \'' . $v . '\' AND store_id = \'' . $store_id . '\'');
				$db->query($sql);
				$sql = 'INSERT INTO' . $ecs->table('store_goods') . ('(`goods_id`,`store_id`,`ru_id`,`goods_number`,`extend_goods_number`) SELECT \'' . $v . '\',\'' . $store_id . '\',\'' . $ru_id . '\',goods_number,\'\' FROM') . $ecs->table('goods') . ('WHERE goods_id = \'' . $v . '\'');
				$db->query($sql);
				$sql = 'SELECT * FROM' . $ecs->table('products') . ('WHERE goods_id = \'' . $v . '\'');
				$products = $db->getAll($sql);

				if (!empty($products)) {
					foreach ($products as $key => $val) {
						$sql = 'INSERT INTO' . $ecs->table('store_products') . ('(`goods_id`,`store_id`,`ru_id`,`product_number`,`product_sn`,`goods_attr`) VALUES (\'' . $v . '\',\'' . $store_id . '\',\'' . $ru_id . '\',\'') . $val['product_number'] . '\',\'\',\'' . $val['goods_attr'] . '\')';
						$db->query($sql);
						$product_id = $GLOBALS['db']->insert_id();
						$sql = 'UPDATE ' . $GLOBALS['ecs']->table('store_products') . "\r\n                                                        SET product_sn = '" . $goods['goods_sn'] . 'g_p' . $product_id . ("'\r\n                                                        WHERE product_id = '" . $product_id . '\'');
						$GLOBALS['db']->query($sql);
					}
				}
			}
			else {
				continue;
			}
		}

		make_json_response('', 1, '同步成功！', array('url' => 'goods.php?act=list&page=' . $page, 'page' => $page));
	}
	else {
		make_json_response('', 2, '请选择商品！', array('url' => 'goods.php?act=list&page=' . $page, 'page' => $page));
	}
}

?>
