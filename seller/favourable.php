<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function favourable_list($ru_id)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['is_going'] = empty($_REQUEST['is_going']) ? 0 : 1;
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'fa.act_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['use_type'] = empty($_REQUEST['use_type']) ? 0 : intval($_REQUEST['use_type']);
		$filter['fav_dateout'] = empty($_REQUEST['fav_dateout']) ? 0 : intval($_REQUEST['fav_dateout']);
		$filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);
		$where = '';

		if ($filter['use_type'] == 1) {
			$where .= ' AND fa.user_id = 0 AND fa.userFav_type = 0';
		}
		else if ($filter['use_type'] == 2) {
			$where .= ' AND fa.user_id > 0 AND fa.userFav_type = 0';
		}
		else if ($filter['use_type'] == 3) {
			$where .= ' AND fa.userFav_type = 1';
		}
		else if ($filter['use_type'] == 4) {
			$where .= ' AND fa.user_id = \'' . $ru_id . '\' AND fa.userFav_type = 0';
		}
		else if (0 < $ru_id) {
			$where .= ' AND (fa.user_id = \'' . $ru_id . '\' OR fa.userFav_type = 1)';
		}

		if ($filter['review_status']) {
			$where .= ' AND fa.review_status = \'' . $filter['review_status'] . '\' ';
		}

		if (0 < $filter['fav_dateout']) {
			$firstSecToday = 24 * 60 * 60 * 2;
			$time = gmtime();
			$where .= 'AND (end_time - \'' . $time . '\') < \'' . $firstSecToday . '\' AND (end_time - \'' . $time . '\') > 0';
		}

		$filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_where = '';
		$store_search_where = '';

		if ($filter['store_search'] != 0) {
			if ($ru_id == 0) {
				if ($_REQUEST['store_type']) {
					$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
				}

				if ($filter['store_search'] == 1) {
					$where .= ' AND fa.user_id = \'' . $filter['merchant_id'] . '\' ';
				}
				else if ($filter['store_search'] == 2) {
					$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
				}
				else if ($filter['store_search'] == 3) {
					$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
				}

				if (1 < $filter['store_search']) {
					$where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . (' WHERE msi.user_id = fa.user_id ' . $store_where . ') > 0 ');
				}
			}
		}

		if (!empty($filter['keyword'])) {
			$where .= ' AND fa.act_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\'';
		}

		if ($filter['is_going']) {
			$now = gmtime();
			$where .= ' AND fa.start_time <= \'' . $now . '\' AND fa.end_time >= \'' . $now . '\' ';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('favourable_activity') . ' AS fa ' . (' WHERE 1 ' . $where);
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT fa.* ' . 'FROM ' . $GLOBALS['ecs']->table('favourable_activity') . ' AS fa ' . (' WHERE 1 ' . $where . ' ') . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
		$filter['keyword'] = stripslashes($filter['keyword']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->query($sql);
	$list = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$row['start_time'] = local_date('Y-m-d H:i:s', $row['start_time']);
		$row['end_time'] = local_date('Y-m-d H:i:s', $row['end_time']);
		$row['user_name'] = get_shop_name($row['user_id'], 1);
		$list[] = $row;
	}

	return array('item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_user_cat_list($ru_id)
{
	$sql = 'SELECT user_shopMain_category FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . (' WHERE user_id = \'' . $ru_id . '\'');
	$user_cat = $GLOBALS['db']->getOne($sql);
	$arr = $new_arr = array();

	if (!empty($user_cat)) {
		$user_cat = explode('-', $user_cat);

		foreach ($user_cat as $key => $row) {
			$arr[$key] = explode(':', $row);
		}

		foreach ($arr as $key => $row) {
			foreach ($row as $ck => $rows) {
				if (0 < $ck) {
					$arr[$key][$ck] = explode(',', $rows);
				}
			}
		}

		$arr = get_level_three_cat1($arr);
		$arr = arr_foreach($arr);
		$arr = array_unique($arr);

		foreach ($arr as $key => $row) {
			$new_arr[$key]['id'] = $row;
			$new_arr[$key]['name'] = $GLOBALS['db']->getOne('SELECT cat_name as name FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE cat_id = \'' . $row . '\''));
		}

		$new_arr = get_array_sort($new_arr, 'id');
		return $new_arr;
	}
}

function get_level_three_cat1($arr)
{
	$new_arr = array();

	foreach ($arr as $key => $row) {
		$new_arr[$key]['cat'] = $row[0];
		$new_arr[$key]['cat_child'] = $row[1];
		$new_arr[$key]['cat_child_three'] = get_level_three_cat2($row[1]);
	}

	foreach ($new_arr as $key => $row) {
		$new_arr[$key] = array_values($row);
	}

	return $new_arr;
}

function get_level_three_cat2($arr)
{
	$new_arr = array();

	foreach ($arr as $key => $row) {
		$new_arr[$key] = get_cat_list_three($row);
	}

	$new_arr = arr_foreach($new_arr);
	return $new_arr;
}

function get_cat_list_three($arr)
{
	$res = $GLOBALS['db']->getAll('SELECT cat_id FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE parent_id = \'' . $arr . '\''));
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key] = $row['cat_id'];
	}

	return $arr;
}

function get_user_cat_search($ru_id, $keyword = '', $arr = array())
{
	$sql = 'SELECT mc.cat_id as id, c.cat_name as name FROM ' . $GLOBALS['ecs']->table('merchants_category') . ' as mc, ' . $GLOBALS['ecs']->table('category') . ' as c ' . (' WHERE mc.cat_id = c.cat_id AND user_id = \'' . $ru_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array_values($arr);

	if ($res) {
		$arr = array_merge($arr, $res);
	}

	$new_arr = array();

	if (!empty($keyword)) {
		foreach ($arr as $key => $row) {
			$pos = strpos($row['name'], $keyword);

			if ($pos === false) {
				unset($row);
			}
			else {
				$new_arr[$key] = $row;
			}
		}
	}
	else {
		$new_arr = $arr;
	}

	return $new_arr;
}

function get_act_range_ext($act_range, $act_id)
{
	if (0 < $act_range) {
		$a_range = ' AND act_range = \'' . $act_range . '\' ';
	}

	$now = gmtime();
	$user_id = $GLOBALS['db']->getOne('SELECT ru_id FROM ' . $GLOBALS['ecs']->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['seller_id'] . '\' '));
	$sql = 'SELECT act_range_ext ' . 'FROM ' . $GLOBALS['ecs']->table('favourable_activity') . (' WHERE start_time <= \'' . $now . '\' AND end_time >= \'' . $now . '\' ') . $a_range . (' AND act_id <> \'' . $act_id . '\' AND user_id = \'' . $user_id . '\' ');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr = array_merge($arr, explode(',', $row['act_range_ext']));
	}

	return array_unique($arr);
}

function get_range_goods($act_range, $act_range_ext_list, $create_in, $user_id = 0)
{
	if (empty($act_range_ext_list)) {
		return array();
	}

	switch ($act_range) {
	case FAR_CATEGORY:
		$id_list = array();

		foreach ($act_range_ext_list as $id) {
			$cat_keys = get_array_keys_cat(intval($id));
			$id_list = array_merge($id_list, $cat_keys);
		}

		break;

	case FAR_BRAND:
		$id_list = $act_range_ext_list;
		break;

	case FAR_GOODS:
		$id_list = $act_range_ext_list;
		break;

	default:
		break;
	}

	$sql = 'SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE user_id = \'' . $user_id . '\' AND ') . db_create_in($id_list, $create_in);
	$res = $GLOBALS['db']->query($sql);
	$arr_goods_id = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$arr_goods_id[] = $row['goods_id'];
	}

	return $arr_goods_id;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . 'includes/lib_goods.php';
include_once ROOT_PATH . '/includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('action_type', 'bonus');
$exc = new exchange($ecs->table('favourable_activity'), $db, 'act_id', 'act_name');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$smarty->assign('controller', basename(PHP_SELF, '.php'));

if ($_REQUEST['act'] == 'list') {
	admin_priv('favourable');
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '12_favourable'));
	$smarty->assign('full_page', 1);
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('ur_here', $_LANG['favourable_list']);
	$smarty->assign('action_link', array('href' => 'favourable.php?act=add', 'text' => $_LANG['add_favourable'], 'class' => 'icon-plus'));
	$list = favourable_list($adminru['ru_id']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('favourable_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('favourable_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$list = favourable_list($adminru['ru_id']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('favourable_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('favourable_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('favourable');
	$id = intval($_GET['id']);
	$favourable = favourable_info($id, 'seller');
	if ($favourable['user_id'] != $adminru['ru_id'] && $favourable['userFav_type'] == 0) {
		$url = 'favourable.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
		exit();
	}

	if (empty($favourable)) {
		make_json_error($_LANG['favourable_not_exist']);
	}

	$name = $favourable['act_name'];
	get_del_batch('', $id, array('activity_thumb'), 'act_id', 'favourable_activity', 1);
	$exc->drop($id);
	admin_log($name, 'remove', 'favourable');
	clear_cache_files();
	$url = 'favourable.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'batch') {
	if (empty($_POST['checkboxes'])) {
		sys_msg($_LANG['no_record_selected']);
	}
	else {
		admin_priv('favourable');
		$ids = $_POST['checkboxes'];

		if (isset($_POST['drop'])) {
			get_del_batch($ids, '', array('activity_thumb'), 'act_id', 'favourable_activity', 1);
			$sql = 'DELETE FROM ' . $ecs->table('favourable_activity') . ' WHERE act_id ' . db_create_in($ids);
			$db->query($sql);
			admin_log('', 'batch_remove', 'favourable');
			clear_cache_files();
			$links[] = array('text' => $_LANG['back_favourable_list'], 'href' => 'favourable.php?act=list&' . list_link_postfix());
			sys_msg($_LANG['batch_drop_ok']);
		}
	}
}
else if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('favourable');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	$sql = 'UPDATE ' . $ecs->table('favourable_activity') . (' SET sort_order = \'' . $val . '\'') . (' WHERE act_id = \'' . $id . '\' LIMIT 1');
	$db->query($sql);
	make_json_result($val);
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		admin_priv('favourable');
		$smarty->assign('primary_cat', $_LANG['02_promotion']);
		$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '12_favourable'));
		$is_add = $_REQUEST['act'] == 'add';
		$smarty->assign('form_action', $is_add ? 'insert' : 'update');

		if ($is_add) {
			$ru_id = $adminru['ru_id'];
			$favourable = array(
				'act_id'        => 0,
				'act_name'      => '',
				'start_time'    => date('Y-m-d H:i:s', time() + 86400),
				'end_time'      => date('Y-m-d H:i:s', time() + 4 * 86400),
				'user_rank'     => '',
				'act_range'     => FAR_ALL,
				'act_range_ext' => '',
				'min_amount'    => 0,
				'max_amount'    => 0,
				'act_type'      => FAT_GOODS,
				'act_type_ext'  => 0,
				'user_id'       => $ru_id,
				'gift'          => array()
				);
		}
		else {
			if (empty($_GET['id'])) {
				sys_msg('invalid param');
			}

			$id = intval($_GET['id']);
			$favourable = favourable_info($id, 'seller');

			if (empty($favourable)) {
				sys_msg($_LANG['favourable_not_exist']);
			}

			if ($favourable['user_id'] != $adminru['ru_id'] && $favourable['userFav_type'] == 0) {
				$Loaction = 'favourable.php?act=list';
				ecs_header('Location: ' . $Loaction . "\n");
				exit();
			}

			$ru_id = $favourable['user_id'];
		}

		$smarty->assign('favourable', $favourable);
		$user_rank_list = array();
		$user_rank_list[] = array('rank_id' => 0, 'rank_name' => $_LANG['not_user'], 'checked' => strpos(',' . $favourable['user_rank'] . ',', ',0,') !== false);
		$sql = 'SELECT rank_id, rank_name FROM ' . $ecs->table('user_rank');
		$res = $db->query($sql);

		while ($row = $db->fetchRow($res)) {
			$row['checked'] = strpos(',' . $favourable['user_rank'] . ',', ',' . $row['rank_id'] . ',') !== false;
			$user_rank_list[] = $row;
		}

		$smarty->assign('user_rank_list', $user_rank_list);
		$act_range_ext = array();
		if ($favourable['act_range'] != FAR_ALL && !empty($favourable['act_range_ext'])) {
			if ($favourable['act_range'] == FAR_CATEGORY) {
				$sql = 'SELECT cat_id AS id, cat_name AS name FROM ' . $ecs->table('category') . ' WHERE cat_id ' . db_create_in($favourable['act_range_ext']);
			}
			else if ($favourable['act_range'] == FAR_BRAND) {
				$sql = 'SELECT brand_id AS id, brand_name AS name FROM ' . $ecs->table('brand') . ' WHERE brand_id ' . db_create_in($favourable['act_range_ext']);
			}
			else {
				$sql = 'SELECT goods_id AS id, goods_name AS name FROM ' . $ecs->table('goods') . ' WHERE goods_id ' . db_create_in($favourable['act_range_ext']);
			}

			$act_range_ext = $db->getAll($sql);
		}

		$smarty->assign('act_range_ext', $act_range_ext);
		$smarty->assign('cfg_lang', $_CFG['lang']);

		if ($is_add) {
			$smarty->assign('ur_here', $_LANG['add_favourable']);
		}
		else {
			$smarty->assign('ur_here', $_LANG['edit_favourable']);
		}

		$href = 'favourable.php?act=list';

		if (!$is_add) {
			$href .= '&' . list_link_postfix();
		}

		$smarty->assign('action_link', array('href' => $href, 'text' => $_LANG['favourable_list'], 'class' => 'icon-reply'));
		assign_query_info();
		$smarty->display('favourable_info.dwt');
	}
	else {
		if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
			admin_priv('favourable');
			$ru_id = isset($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : 0;
			$is_add = $_REQUEST['act'] == 'insert';
			$now = gmtime();
			$act_id = intval($_POST['id']);
			$act_range = intval($_POST['act_range']);
			$act_range_ext = isset($_POST['act_range_ext']) && !empty($_POST['act_range_ext']) ? implode(',', $_POST['act_range_ext']) : '';

			if ($is_add) {
				$favourable_info['user_id'] = $ru_id;
			}
			else {
				$favourable_info = get_table_info('favourable_activity', 'act_id = \'' . $act_id . '\'', array('user_id', 'review_status'));
			}

			$act_range_ext_cat = get_act_range_ext(FAR_CATEGORY, $act_id);
			$goods_list_cat = get_range_goods(FAR_CATEGORY, $act_range_ext_cat, 'cat_id', $favourable_info['user_id']);
			$act_range_ext_brand = get_act_range_ext(FAR_BRAND, $act_id);
			$goods_list_brand = get_range_goods(FAR_BRAND, $act_range_ext_brand, 'brand_id', $favourable_info['user_id']);
			$act_range_ext_goods = get_act_range_ext(FAR_GOODS, $act_id);
			$goods_list_goods = get_range_goods(FAR_GOODS, $act_range_ext_goods, 'goods_id', $favourable_info['user_id']);

			switch ($act_range) {
			case 0:
				$where = '';

				if ($act_id) {
					$where .= ' AND act_id <> \'' . $act_id . '\'';
				}

				$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('favourable_activity') . ' WHERE user_id = \'' . $favourable_info['user_id'] . ('\' AND start_time <= \'' . $now . '\' AND end_time >= \'' . $now . '\' ' . $where);
				$num = $GLOBALS['db']->getOne($sql);

				if ($num) {
					sys_msg('商品已经参与其他优惠活动', 1);
				}

				break;

			case 1:
				$goods_list_cat_new = get_range_goods(FAR_CATEGORY, $_POST['act_range_ext'], 'cat_id');
				$arr = array_intersect($goods_list_cat, $goods_list_cat_new);
				$arr1 = array_intersect($goods_list_brand, $goods_list_cat_new);
				$arr2 = array_intersect($goods_list_goods, $goods_list_cat_new);
				if ($arr || $arr1 || $arr2) {
					sys_msg('分类商品已经参与其他优惠活动', 1);
				}

				break;

			case 2:
				$goods_list_brand_new = get_range_goods(FAR_BRAND, $_POST['act_range_ext'], 'brand_id');
				$arr = array_intersect($goods_list_cat, $goods_list_brand_new);
				$arr1 = array_intersect($goods_list_brand, $goods_list_brand_new);
				$arr2 = array_intersect($goods_list_brand_new, $goods_list_goods);
				if ($arr || $arr1 || $arr2) {
					sys_msg('品牌商品已经参与其他优惠活动', 1);
				}

				break;

			case 3:
				$goods_list_goods_new = get_range_goods(FAR_GOODS, $_POST['act_range_ext'], 'goods_id');
				$arr = array_intersect($goods_list_cat, $goods_list_goods_new);
				$arr1 = array_intersect($goods_list_brand, $goods_list_goods_new);
				$arr2 = array_intersect($goods_list_goods, $goods_list_goods_new);
				if ($arr || $arr1 || $arr2) {
					sys_msg('商品已经参与其他优惠活动', 1);
				}

				break;

			default:
				break;
			}

			$act_name = sub_str($_POST['act_name'], 255, false);

			if (!$exc->is_only('act_name', $act_name, intval($_POST['id']))) {
				sys_msg($_LANG['act_name_exists']);
			}

			if (!isset($_POST['user_rank'])) {
				sys_msg($_LANG['pls_set_user_rank']);
			}

			if (0 < intval($_POST['act_range']) && !isset($_POST['act_range_ext'])) {
				sys_msg($_LANG['pls_set_act_range']);
			}

			$min_amount = 0 <= floatval($_POST['min_amount']) ? floatval($_POST['min_amount']) : 0;
			$max_amount = 0 <= floatval($_POST['max_amount']) ? floatval($_POST['max_amount']) : 0;
			if (0 < $max_amount && $max_amount < $min_amount) {
				sys_msg($_LANG['amount_error']);
			}

			$gift = array();
			if (intval($_POST['act_type']) == FAT_GOODS && isset($_POST['gift_id'])) {
				foreach ($_POST['gift_id'] as $key => $id) {
					$gift[] = array('id' => $id, 'name' => $_POST['gift_name'][$key], 'price' => $_POST['gift_price'][$key]);
				}
			}

			$favourable = array('act_id' => intval($_POST['id']), 'act_name' => $act_name, 'start_time' => local_strtotime($_POST['start_time']), 'end_time' => local_strtotime($_POST['end_time']), 'user_rank' => isset($_POST['user_rank']) ? join(',', $_POST['user_rank']) : '0', 'act_range' => intval($_POST['act_range']), 'act_range_ext' => $act_range_ext, 'min_amount' => floatval($_POST['min_amount']), 'max_amount' => floatval($_POST['max_amount']), 'act_type' => intval($_POST['act_type']), 'act_type_ext' => floatval($_POST['act_type_ext']), 'gift' => serialize($gift), 'userFav_type' => intval($_POST['userFav_type']));

			if ($favourable['act_type'] == FAT_GOODS) {
				$favourable['act_type_ext'] = round($favourable['act_type_ext']);
			}

			$activity_thumb = $image->upload_image($_FILES['activity_thumb'], 'activity_thumb');
			get_oss_add_file(array($activity_thumb));

			if ($is_add) {
				$favourable['user_id'] = $adminru['ru_id'];
				$favourable['activity_thumb'] = $activity_thumb;
				$db->autoExecute($ecs->table('favourable_activity'), $favourable, 'INSERT');
				$favourable['act_id'] = $db->insert_id();
			}
			else {
				$favourable['review_status'] = 1;

				if (!empty($activity_thumb)) {
					$favourable['activity_thumb'] = $activity_thumb;
				}

				$db->autoExecute($ecs->table('favourable_activity'), $favourable, 'UPDATE', 'act_id = \'' . $favourable['act_id'] . '\'');
			}

			if ($is_add) {
				admin_log($favourable['act_name'], 'add', 'favourable');
			}
			else {
				admin_log($favourable['act_name'], 'edit', 'favourable');
			}

			clear_cache_files();

			if ($is_add) {
				$links = array(
					array('href' => 'favourable.php?act=add', 'text' => $_LANG['continue_add_favourable']),
					array('href' => 'favourable.php?act=list', 'text' => $_LANG['back_favourable_list'])
					);
				sys_msg($_LANG['add_favourable_ok'], 0, $links);
			}
			else {
				$links = array(
					array('href' => 'favourable.php?act=edit&id=' . $favourable['act_id'] . '&ru_id=' . $ru_id, 'text' => $_LANG['edit_favourable']),
					array('href' => 'favourable.php?act=list&' . list_link_postfix(), 'text' => $_LANG['back_favourable_list'])
					);
				sys_msg($_LANG['edit_favourable_ok'], 0, $links);
			}
		}
		else if ($_REQUEST['act'] == 'drop_thumb') {
			admin_priv('brand_manage');
			$act_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$ru_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
			get_del_batch('', $act_id, array('activity_thumb'), 'act_id', 'favourable_activity', 1);
			$sql = 'UPDATE ' . $ecs->table('favourable_activity') . (' SET activity_thumb = \'\' WHERE act_id = \'' . $act_id . '\'');
			$db->query($sql);
			$link = array(
				array('text' => $_LANG['edit_favourable'], 'href' => 'favourable.php?act=edit&id=' . $act_id . '&ru_id=' . $ru_id),
				array('text' => $_LANG['favourable_list'], 'href' => 'favourable.php?act=list')
				);
			sys_msg($_LANG['drop_activity_thumb_success'], 0, $link);
		}
		else if ($_REQUEST['act'] == 'search') {
			check_authz_json('favourable');
			include_once ROOT_PATH . 'includes/cls_json.php';
			$json = new JSON();
			$filter = $json->decode($_GET['JSON']);
			$filter->keyword = json_str_iconv($filter->keyword);
			$ru_id = $filter->ru_id;
			$where = '';

			if ($ru_id == 0) {
				$where .= ' LIMIT 50';
			}

			if ($filter->act_range == FAR_ALL) {
				$arr[0] = array('id' => 0, 'name' => $_LANG['js_languages']['all_need_not_search']);
			}
			else if ($filter->act_range == FAR_CATEGORY) {
				$arr = get_user_cat_list($ru_id);
				$arr = get_user_cat_search($ru_id, $filter->keyword, $arr);
				$arr = array_values($arr);
			}
			else if ($filter->act_range == FAR_BRAND) {
				$sql = 'SELECT brand_id AS id, brand_name AS name FROM ' . $ecs->table('brand') . ' WHERE brand_name LIKE \'%' . mysql_like_quote($filter->keyword) . '%\'' . $where;
				$arr = $db->getAll($sql);

				if ($arr) {
					foreach ($arr as $key => $row) {
						if ($ru_id) {
							$arr[$key]['is_brand'] = get_seller_brand_count($row['id'], $ru_id);
						}
						else {
							$arr[$key]['is_brand'] = 1;
						}

						if (!(0 < $arr[$key]['is_brand'])) {
							unset($arr[$key]);
						}
					}

					$arr = array_values($arr);
				}
			}
			else {
				$sql = 'SELECT goods_id AS id, goods_name AS name FROM ' . $ecs->table('goods') . ' WHERE (goods_name LIKE \'%' . mysql_like_quote($filter->keyword) . '%\'' . ' OR goods_sn LIKE \'%' . mysql_like_quote($filter->keyword) . ('%\')  AND user_id = \'' . $ru_id . '\' LIMIT 50');
				$arr = $db->getAll($sql);
			}

			if (empty($arr)) {
				$arr = array(
					array('id' => 0, 'name' => $_LANG['search_result_empty'])
					);
			}

			make_json_result($arr);
		}
	}
}

?>
