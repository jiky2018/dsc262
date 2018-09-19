<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_goodstype($ru_id)
{
	$where = ' WHERE 1 ';

	if ($GLOBALS['_CFG']['attr_set_up'] == 0) {
		if (0 < $ru_id) {
			$where .= ' AND t.user_id = 0 ';
		}
	}
	else if ($GLOBALS['_CFG']['attr_set_up'] == 1) {
		if (0 < $ru_id) {
			$where .= ' AND t.user_id = \'' . $ru_id . '\'';
		}
	}

	$result = get_filter();

	if ($result === false) {
		if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
			$_REQUEST['keyword'] = json_str_iconv($_REQUEST['keyword']);
		}

		$filter['cat_id'] = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : -1;
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'cat_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);
		$filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? intval($_REQUEST['seller_list']) : 0;

		if (0 < $filter['cat_id']) {
			$cat_keys = get_type_cat_arr($filter['cat_id'], 1, 1);
			$where .= ' AND t.c_id in (' . $cat_keys . ') ';
		}

		if ($filter['keyword']) {
			$where .= ' AND t.cat_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\' ';
		}

		if (-1 < $filter['merchant_id']) {
			$where .= ' AND t.user_id = \'' . $filter['merchant_id'] . '\' ';
		}

		if ($filter['seller_list'] == 2) {
			$where .= ' AND t.user_id = 0  AND t.suppliers_id > 0 ';
		}
		else {
			$where .= !empty($filter['seller_list']) ? ' AND t.user_id > 0 AND t.suppliers_id = 0 ' : ' AND t.user_id = 0 AND t.suppliers_id = 0 ';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods_type') . ' AS t ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' AS a ON a.cat_id=t.cat_id ' . $where . 'GROUP BY t.cat_id ';
		$filter['record_count'] = count($GLOBALS['db']->getAll($sql));
		$filter = page_and_size($filter);
		$sql = 'SELECT t.*, COUNT(a.cat_id) AS attr_count ,gt.cat_name as gt_cat_name ' . 'FROM ' . $GLOBALS['ecs']->table('goods_type') . ' AS t ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' AS a ON a.cat_id=t.cat_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods_type_cat') . ' AS gt ON gt.cat_id=t.c_id ' . $where . 'GROUP BY t.cat_id ' . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . 'LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$all = $GLOBALS['db']->getAll($sql);

	foreach ($all as $key => $val) {
		$all[$key]['attr_group'] = strtr($val['attr_group'], array("\r" => '', "\n" => ', '));

		if (0 < $val['suppliers_id']) {
			$all[$key]['user_name'] = get_table_date('suppliers', 'suppliers_id=\'' . $val['suppliers_id'] . '\'', array('suppliers_name'), 2);
		}
		else {
			$all[$key]['user_name'] = get_shop_name($val['user_id'], 1);
		}
	}

	return array('type' => $all, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_goodstype_info($cat_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('goods_type') . (' WHERE cat_id=\'' . $cat_id . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function update_attribute_group($cat_id, $old_group, $new_group)
{
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('attribute') . (' SET attr_group=\'' . $new_group . '\' WHERE cat_id=\'' . $cat_id . '\' AND attr_group=\'' . $old_group . '\'');
	$GLOBALS['db']->query($sql);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('goods_type'), $db, 'cat_id', 'cat_name');
$exc_cat = new exchange($ecs->table('goods_type_cat'), $db, 'cat_id', 'cat_name');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

if ($_REQUEST['act'] == 'manage') {
	assign_query_info();
	$smarty->assign('ur_here', $_LANG['08_goods_type']);
	$smarty->assign('full_page', 1);
	$good_type_list = get_goodstype($adminru['ru_id']);
	$good_in_type = '';
	$smarty->assign('goods_type_arr', $good_type_list['type']);
	$smarty->assign('filter', $good_type_list['filter']);
	$smarty->assign('record_count', $good_type_list['record_count']);
	$smarty->assign('page_count', $good_type_list['page_count']);
	$query = $db->query('SELECT a.cat_id FROM ' . $ecs->table('attribute') . ' AS a RIGHT JOIN ' . $ecs->table('goods_attr') . ' AS g ON g.attr_id = a.attr_id GROUP BY a.cat_id ORDER BY a.sort_order, a.attr_id, g.goods_attr_id');

	while ($row = $db->fetchRow($query)) {
		$good_in_type[$row['cat_id']] = 1;
	}

	$smarty->assign('good_in_type', $good_in_type);

	if ($GLOBALS['_CFG']['attr_set_up'] == 0) {
		if ($adminru['ru_id'] == 0) {
			$smarty->assign('action_link', array('text' => $_LANG['new_goods_type'], 'href' => 'goods_type.php?act=add'));
			$smarty->assign('attr_set_up', 1);
		}
		else {
			$smarty->assign('attr_set_up', 0);
		}
	}
	else if ($GLOBALS['_CFG']['attr_set_up'] == 1) {
		$smarty->assign('action_link', array('text' => $_LANG['new_goods_type'], 'href' => 'goods_type.php?act=add'));
		$smarty->assign('attr_set_up', 1);
	}

	$smarty->assign('action_link1', array('text' => $_LANG['type_cart'], 'href' => 'goods_type.php?act=cat_list'));
	$smarty->assign('action_link2', array('text' => $_LANG['08_goods_type'], 'href' => 'goods_type.php?act=manage'));
	$smarty->assign('act_type', $_REQUEST['act']);
	self_seller(BASENAME($_SERVER['PHP_SELF']), 'manage');
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$smarty->display('goods_type.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$good_type_list = get_goodstype($adminru['ru_id']);

	if ($GLOBALS['_CFG']['attr_set_up'] == 0) {
		if ($adminru['ru_id'] == 0) {
			$smarty->assign('attr_set_up', 1);
		}
		else {
			$smarty->assign('attr_set_up', 0);
		}
	}
	else if ($GLOBALS['_CFG']['attr_set_up'] == 1) {
		$smarty->assign('attr_set_up', 1);
	}

	$smarty->assign('goods_type_arr', $good_type_list['type']);
	$smarty->assign('filter', $good_type_list['filter']);
	$smarty->assign('record_count', $good_type_list['record_count']);
	$smarty->assign('page_count', $good_type_list['page_count']);
	make_json_result($smarty->fetch('goods_type.dwt'), '', array('filter' => $good_type_list['filter'], 'page_count' => $good_type_list['page_count']));
}
else if ($_REQUEST['act'] == 'cat_list') {
	assign_query_info();
	admin_priv('goods_type');
	$smarty->assign('ur_here', $_LANG['type_cart']);
	$smarty->assign('full_page', 1);
	$level = empty($_REQUEST['level']) ? 1 : intval($_REQUEST['level']) + 1;
	$good_type_cat = get_typecat($level);
	$smarty->assign('goods_type_arr', $good_type_cat['type']);
	$smarty->assign('filter', $good_type_cat['filter']);
	$smarty->assign('record_count', $good_type_cat['record_count']);
	$smarty->assign('page_count', $good_type_cat['page_count']);
	$smarty->assign('action_link', array('text' => $_LANG['type_cart_add'], 'href' => 'goods_type.php?act=cat_add'));
	$smarty->assign('action_link1', array('text' => $_LANG['type_cart'], 'href' => 'goods_type.php?act=cat_list'));
	$smarty->assign('action_link2', array('text' => $_LANG['08_goods_type'], 'href' => 'goods_type.php?act=manage'));
	$smarty->assign('act_type', $_REQUEST['act']);
	$smarty->assign('level', $level);
	self_seller(BASENAME($_SERVER['PHP_SELF']), 'cat_list');
	$smarty->display('goods_type_cat.dwt');
}
else if ($_REQUEST['act'] == 'cat_list_query') {
	check_authz_json('goods_type');
	$level = empty($_REQUEST['level']) ? 1 : intval($_REQUEST['level']);
	$good_type_cat = get_typecat($level);
	$smarty->assign('goods_type_arr', $good_type_cat['type']);
	$smarty->assign('filter', $good_type_cat['filter']);
	$smarty->assign('record_count', $good_type_cat['record_count']);
	$smarty->assign('page_count', $good_type_cat['page_count']);
	$smarty->assign('level', $level);
	make_json_result($smarty->fetch('goods_type_cat.dwt'), '', array('filter' => $good_type_cat['filter'], 'page_count' => $good_type_cat['page_count']));
}
else {
	if ($_REQUEST['act'] == 'cat_add' || $_REQUEST['act'] == 'cat_edit') {
		admin_priv('goods_type');

		if ($_REQUEST['act'] == 'cat_add') {
			$smarty->assign('ur_here', $_LANG['type_cart_add']);
			$smarty->assign('form_act', 'cat_insert');
		}
		else {
			$smarty->assign('ur_here', $_LANG['type_cart_edit']);
			$smarty->assign('form_act', 'cat_update');
		}

		$smarty->assign('action_link', array('text' => $_LANG['type_cart'], 'href' => 'goods_type.php?act=cat_list'));
		$cat_id = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
		$parent = get_every_category($cat_id, 'goods_type_cat');
		$smarty->assign('parent', $parent);

		if (0 < $cat_id) {
			$sql = 'SELECT cat_id ,cat_name ,parent_id ,sort_order ,level,user_id FROM' . $ecs->table('goods_type_cat') . ('WHERE cat_id = \'' . $cat_id . '\' LIMIT 1');
			$type_cat = $db->getRow($sql);
			$cat_tree = get_type_cat_arr($type_cat['parent_id'], 2);
			$smarty->assign('cat_tree', $cat_tree);
			$smarty->assign('type_cat', $type_cat);
			$ru_id = $type_cat['user_id'];
		}
		else {
			$ru_id = 0;
		}

		$cat_level = get_type_cat_arr(0, 0, 0, $ru_id);
		$smarty->assign('cat_level', $cat_level);
		$smarty->display('goods_type_cat_info.dwt');
	}
	else {
		if ($_REQUEST['act'] == 'cat_insert' || $_REQUEST['act'] == 'cat_update') {
			$cat_name = !empty($_REQUEST['cat_name']) ? trim($_REQUEST['cat_name']) : '';
			$parent_id = !empty($_REQUEST['attr_parent_id']) ? intval($_REQUEST['attr_parent_id']) : 0;
			$sort_order = !empty($_REQUEST['sort_order']) ? intval($_REQUEST['sort_order']) : 50;
			$cat_id = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;

			if (0 < $parent_id) {
				$sql = 'SELECT level FROM' . $ecs->table('goods_type_cat') . (' WHERE cat_id = \'' . $parent_id . '\' LIMIT 1');
				$level = $db->getOne($sql) + 1;
			}
			else {
				$level = 1;
			}

			$cat_info = array('cat_name' => $cat_name, 'parent_id' => $parent_id, 'level' => $level, 'sort_order' => $sort_order);
			$where = ' user_id = \'' . $adminru['ru_id'] . '\'';

			if ($_REQUEST['act'] == 'cat_insert') {
				$is_only = $exc_cat->is_only('cat_name', $cat_name, 0, $where);

				if (!$is_only) {
					sys_msg(sprintf($_LANG['exist_cat'], stripslashes($cat_name)), 1);
				}

				$db->autoExecute($ecs->table('goods_type_cat'), $cat_info, 'INSERT');
				$link[0]['text'] = $_LANG['continue_add'];
				$link[0]['href'] = 'goods_type.php?act=cat_add';
				$link[1]['text'] = $_LANG['back_list'];
				$link[1]['href'] = 'goods_type.php?act=cat_list';
				sys_msg($_LANG['add_succeed'], 0, $link);
			}
			else {
				$is_only = $exc_cat->is_only('cat_name', $cat_name, $cat_id, $where);

				if (!$is_only) {
					sys_msg(sprintf($_LANG['exist_cat'], stripslashes($cat_name)), 1);
				}

				$db->autoExecute($ecs->table('goods_type_cat'), $cat_info, 'UPDATE', 'cat_id = \'' . $cat_id . '\'');
				$link[0]['text'] = $_LANG['back_list'];
				$link[0]['href'] = 'goods_type.php?act=cat_list';
				sys_msg($_LANG['edit_succeed'], 0, $link);
			}
		}
		else if ($_REQUEST['act'] == 'remove_cat') {
			check_authz_json('goods_type');
			$id = intval($_GET['id']);
			$sql = 'SELECT COUNT(*) FROM' . $ecs->table('goods_type_cat') . ('WHERE parent_id = \'' . $id . '\'');
			$cat_count = $db->getOne($sql);
			$sql = 'SELECT COUNT(*) FROM' . $GLOBALS['ecs']->table('goods_type') . ('WHERE c_id = \'' . $id . '\'');
			$type_count = $GLOBALS[db]->getOne($sql);
			if (0 < $cat_count || 0 < $type_count) {
				make_json_error($_LANG['remove_prompt']);
			}
			else {
				$exc_cat->drop($id);
			}

			$url = 'goods_type.php?act=cat_list_query&' . str_replace('act=remove_cat', '', $_SERVER['QUERY_STRING']);
			ecs_header('Location: ' . $url . "\n");
			exit();
		}
	}
}

if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('goods_type');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	$exc_cat->edit('sort_order = \'' . $val . '\'', $id);
	clear_cache_files();
	make_json_result($val);
}
else if ($_REQUEST['act'] == 'edit_type_name') {
	check_authz_json('goods_type');
	$type_id = !empty($_POST['id']) ? intval($_POST['id']) : 0;
	$type_name = !empty($_POST['val']) ? json_str_iconv(trim($_POST['val'])) : '';
	$is_only = $exc->is_only('cat_name', $type_name, $type_id);

	if ($is_only) {
		$exc->edit('cat_name=\'' . $type_name . '\'', $type_id);
		admin_log($type_name, 'edit', 'goods_type');
		make_json_result(stripslashes($type_name));
	}
	else {
		make_json_error($_LANG['repeat_type_name']);
	}
}
else if ($_REQUEST['act'] == 'toggle_enabled') {
	check_authz_json('goods_type');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	$exc->edit('enabled=\'' . $val . '\'', $id);
	make_json_result($val);
}
else if ($_REQUEST['act'] == 'add') {
	admin_priv('goods_type');

	if ($GLOBALS['_CFG']['attr_set_up'] == 0) {
		if (0 < $adminru['ru_id']) {
			$links = array(
				array('href' => 'goods_type.php?act=manage', 'text' => $_LANG['back_list'])
				);
			sys_msg('暂时没有添加属性权限', 0, $links);
			exit();
		}
	}

	$cat_level = get_type_cat_arr();
	$smarty->assign('ur_here', $_LANG['new_goods_type']);
	$smarty->assign('action_link', array('href' => 'goods_type.php?act=manage', 'text' => $_LANG['goods_type_list']));
	$smarty->assign('action', 'add');
	$smarty->assign('form_act', 'insert');
	$smarty->assign('goods_type', array('enabled' => 1));
	$smarty->assign('cat_level', $cat_level);
	assign_query_info();
	$smarty->display('goods_type_info.dwt');
}
else if ($_REQUEST['act'] == 'insert') {
	$parent_id = !empty($_REQUEST['attr_parent_id']) ? intval($_REQUEST['attr_parent_id']) : 0;
	$goods_type['cat_name'] = sub_str($_POST['cat_name'], 60);
	$goods_type['attr_group'] = sub_str($_POST['attr_group'], 255);
	$goods_type['enabled'] = intval($_POST['enabled']);
	$goods_type['c_id'] = $parent_id;
	$goods_type['user_id'] = $adminru['ru_id'];

	if ($db->autoExecute($ecs->table('goods_type'), $goods_type) !== false) {
		$links = array(
			array('href' => 'goods_type.php?act=manage', 'text' => $_LANG['back_list'])
			);
		sys_msg($_LANG['add_goodstype_success'], 0, $links);
	}
	else {
		sys_msg($_LANG['add_goodstype_failed'], 1);
	}
}
else if ($_REQUEST['act'] == 'edit') {
	$goods_type = get_goodstype_info(intval($_GET['cat_id']));

	if (empty($goods_type)) {
		sys_msg($_LANG['cannot_found_goodstype'], 1);
	}

	admin_priv('goods_type');

	if ($GLOBALS['_CFG']['attr_set_up'] == 0) {
		if (0 < $adminru['ru_id']) {
			$links = array(
				array('href' => 'goods_type.php?act=manage', 'text' => $_LANG['back_list'])
				);
			sys_msg('暂时没有添加属性权限', 0, $links);
			exit();
		}
	}

	$cat_level = get_type_cat_arr(0, 0, 0, $goods_type['user_id']);
	$smarty->assign('cat_level', $cat_level);
	$cat_tree = get_type_cat_arr($goods_type['c_id'], 2, 0, $goods_type['user_id']);
	$cat_tree1 = array('checked_id' => $cat_tree['checked_id']);

	if (0 < $cat_tree['checked_id']) {
		$cat_tree1 = get_type_cat_arr($cat_tree['checked_id'], 2, 0, $goods_type['user_id']);
	}

	$smarty->assign('cat_tree', $cat_tree);
	$smarty->assign('cat_tree1', $cat_tree1);
	$smarty->assign('ur_here', $_LANG['edit_goods_type']);
	$smarty->assign('action_link', array('href' => 'goods_type.php?act=manage', 'text' => $_LANG['goods_type_list']));
	$smarty->assign('action', 'add');
	$smarty->assign('form_act', 'update');
	$smarty->assign('goods_type', $goods_type);
	assign_query_info();
	$smarty->display('goods_type_info.dwt');
}
else if ($_REQUEST['act'] == 'update') {
	$parent_id = !empty($_REQUEST['attr_parent_id']) ? intval($_REQUEST['attr_parent_id']) : 0;
	$goods_type['c_id'] = $parent_id;
	$goods_type['cat_name'] = sub_str($_POST['cat_name'], 60);
	$goods_type['attr_group'] = sub_str($_POST['attr_group'], 255);
	$goods_type['enabled'] = intval($_POST['enabled']);
	$cat_id = intval($_POST['cat_id']);
	$old_groups = get_attr_groups($cat_id);

	if ($db->autoExecute($ecs->table('goods_type'), $goods_type, 'UPDATE', 'cat_id=\'' . $cat_id . '\'') !== false) {
		$new_groups = explode("\n", str_replace("\r", '', $goods_type['attr_group']));

		foreach ($old_groups as $key => $val) {
			$found = array_search($val, $new_groups);
			if ($found === NULL || $found === false) {
				update_attribute_group($cat_id, $key, 0);
			}
			else if ($key != $found) {
				update_attribute_group($cat_id, $key, $found);
			}
		}

		$links = array(
			array('href' => 'goods_type.php?act=manage', 'text' => $_LANG['back_list'])
			);
		sys_msg($_LANG['edit_goodstype_success'], 0, $links);
	}
	else {
		sys_msg($_LANG['edit_goodstype_failed'], 1);
	}
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('goods_type');
	$id = intval($_GET['id']);
	$name = $exc->get_name($id);

	if ($exc->drop($id)) {
		admin_log(addslashes($name), 'remove', 'goods_type');
		$sql = 'SELECT attr_id FROM ' . $ecs->table('attribute') . (' WHERE cat_id = \'' . $id . '\'');
		$arr = $db->getCol($sql);
		$GLOBALS['db']->query('DELETE FROM ' . $ecs->table('attribute') . ' WHERE attr_id ' . db_create_in($arr));
		$GLOBALS['db']->query('DELETE FROM ' . $ecs->table('goods_attr') . ' WHERE attr_id ' . db_create_in($arr));
		$url = 'goods_type.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
		exit();
	}
	else {
		make_json_error($_LANG['remove_failed']);
	}
}
else if ($_REQUEST['act'] == 'get_childcat') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('content' => '', 'error' => '');
	$cat_id = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$level = !empty($_REQUEST['level']) ? intval($_REQUEST['level']) + 1 : 0;
	$type = !empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
	$typeCat = !empty($_REQUEST['typeCat']) ? intval($_REQUEST['typeCat']) : 0;
	$child_cat = get_type_cat_arr($cat_id);

	if (!empty($child_cat)) {
		$result['error'] = 0;
		$smarty->assign('child_cat', $child_cat);
		$smarty->assign('level', $level);
		$smarty->assign('type', $type);
		$smarty->assign('typeCat', $typeCat);
		$result['content'] = $smarty->fetch('library/type_cat.lbi');
	}
	else {
		$result['error'] = 1;
	}

	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'get_childtype') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('content' => '', 'error' => '');
	$goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
	$cat_id = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$typeCat = !empty($_REQUEST['typeCat']) ? intval($_REQUEST['typeCat']) : 0;
	$where = 'WHERE 1 ';

	if (0 < $goods_id) {
		$sql = 'SELECT user_id FROM' . $ecs->table('goods') . ('WHERE goods_id = \'' . $goods_id . '\' LIMIT 1');
		$user_id = $db->getOne($sql);

		if (0 < $user_id) {
			$where .= ' AND user_id = \'' . $user_id . '\'';
		}
	}
	else {
		$where .= ' AND user_id = \'' . $adminru['ru_id'] . '\' ';
	}

	if (0 < $cat_id) {
		$cat_keys = get_type_cat_arr($cat_id, 1, 1);
		$where .= ' AND c_id in (' . $cat_keys . ') AND c_id != 0 ';
	}

	$sql = 'SELECT cat_id,cat_name FROM' . $ecs->table('goods_type') . $where;
	$type_list = $db->getAll($sql);
	$result['error'] = 0;
	$smarty->assign('goods_type_list', $type_list);
	$smarty->assign('type_html', 1);
	$smarty->assign('typeCat', $typeCat);
	$result['content'] = $smarty->fetch('library/type_cat.lbi');
	exit($json->encode($result));
}

?>
