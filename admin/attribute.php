<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_attrlist($ru_id = 0)
{
	$filter = array();
	$filter['goods_type'] = empty($_REQUEST['goods_type']) ? 0 : intval($_REQUEST['goods_type']);
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'attr_id' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);

	if (!empty($filter['goods_type'])) {
		$where = ' WHERE a.cat_id = \'' . $filter['goods_type'] . '\' ';
	}

	$where = (!empty($filter['goods_type']) ? ' WHERE a.cat_id = \'' . $filter['goods_type'] . '\' ' : '');

	if ($GLOBALS['_CFG']['attr_set_up'] == 0) {
		if (0 < $ru_id) {
			$where .= ' AND t.user_id = 0 ';
		}
	}
	else if ($GLOBALS['_CFG']['attr_set_up'] == 1) {
		if (0 < $ru_id) {
			$where = ' AND t.user_id = \'' . $ru_id . '\'';
		}
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('attribute') . ' AS a ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_type') . ' AS t ON a.cat_id = t.cat_id ' . ' ' . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT a.*, t.cat_name ' . ' FROM ' . $GLOBALS['ecs']->table('attribute') . ' AS a ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_type') . ' AS t ON a.cat_id = t.cat_id ' . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ' . ' LIMIT ' . $filter['start'] . ', ' . $filter['page_size'];
	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		$row[$key]['attr_input_type_desc'] = $GLOBALS['_LANG']['value_attr_input_type'][$val['attr_input_type']];
		$row[$key]['attr_values'] = str_replace("\n", ', ', $val['attr_values']);
	}

	$arr = array('item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$_REQUEST['act'] = trim($_REQUEST['act']);

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}

$exc = new exchange($ecs->table('attribute'), $db, 'attr_id', 'attr_name');
$adminru = get_admin_ru_id();

if ($_REQUEST['act'] == 'list') {
	$goods_type = (isset($_GET['goods_type']) ? intval($_GET['goods_type']) : 0);
	$smarty->assign('ur_here', $_LANG['09_attribute_list']);
	$smarty->assign('goods_type', $goods_type);
	$smarty->assign('goods_type_list', goods_type_list($goods_type));
	$smarty->assign('full_page', 1);
	$list = get_attrlist($adminru['ru_id']);
	$smarty->assign('attr_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);

	if ($GLOBALS['_CFG']['attr_set_up'] == 0) {
		if ($adminru['ru_id'] == 0) {
			$smarty->assign('action_link', array('href' => 'attribute.php?act=add&goods_type=' . $goods_type, 'text' => $_LANG['10_attribute_add']));
			$smarty->assign('attr_set_up', 1);
		}
		else {
			$smarty->assign('attr_set_up', 0);
		}
	}
	else if ($GLOBALS['_CFG']['attr_set_up'] == 1) {
		$smarty->assign('action_link', array('href' => 'attribute.php?act=add&goods_type=' . $goods_type, 'text' => $_LANG['10_attribute_add']));
		$smarty->assign('attr_set_up', 1);
	}

	assign_query_info();
	$smarty->display('attribute_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$list = get_attrlist($adminru['ru_id']);
	$smarty->assign('attr_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);

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

	make_json_result($smarty->fetch('attribute_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else {
	if (($_REQUEST['act'] == 'add') || ($_REQUEST['act'] == 'edit')) {
		admin_priv('attr_manage');
		$attr_id = (isset($_REQUEST['attr_id']) && !empty($_REQUEST['attr_id']) ? intval($_REQUEST['attr_id']) : 0);
		$is_add = $_REQUEST['act'] == 'add';
		$smarty->assign('form_act', $is_add ? 'insert' : 'update');

		if ($is_add) {
			$add_edit_cenetent = '暂时没有添加属性权限';
			$goods_type = (isset($_GET['goods_type']) ? intval($_GET['goods_type']) : 0);
			$attr = array('attr_id' => 0, 'cat_id' => $goods_type, 'attr_cat_type' => 0, 'attr_name' => '', 'attr_input_type' => 0, 'attr_index' => 0, 'attr_values' => '', 'attr_type' => 0, 'is_linked' => 0);
		}
		else {
			$add_edit_cenetent = '暂时没有编辑属性权限';
			$sql = 'SELECT * FROM ' . $ecs->table('attribute') . ' WHERE attr_id = \'' . $attr_id . '\'';
			$attr = $db->getRow($sql);
		}

		if ($GLOBALS['_CFG']['attr_set_up'] == 0) {
			if (0 < $adminru['ru_id']) {
				$links = array(
					array('href' => 'goods_type.php?act=manage', 'text' => $_LANG['back_list'])
					);
				sys_msg($add_edit_cenetent, 0, $links);
				exit();
			}
		}

		$smarty->assign('attr', $attr);
		$smarty->assign('attr_groups', get_attr_groups($attr['cat_id']));
		$smarty->assign('goods_type_list', goods_type_list($attr['cat_id']));
		$smarty->assign('ur_here', $is_add ? $_LANG['10_attribute_add'] : $_LANG['52_attribute_add']);
		$smarty->assign('action_link', array('href' => 'attribute.php?act=list&goods_type=' . $attr['cat_id'], 'text' => $_LANG['09_attribute_list']));
		assign_query_info();
		$smarty->display('attribute_info.dwt');
	}
	else {
		if (($_REQUEST['act'] == 'insert') || ($_REQUEST['act'] == 'update')) {
			admin_priv('attr_manage');
			$is_insert = $_REQUEST['act'] == 'insert';
			$cat_id = (isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0);
			$sort_order = (isset($_POST['sort_order']) && !empty($_POST['sort_order']) ? $_POST['sort_order'] : 0);
			$exclude = (empty($_POST['attr_id']) ? 0 : intval($_POST['attr_id']));

			if (!$exc->is_only('attr_name', $_POST['attr_name'], $exclude, ' cat_id = \'' . $cat_id . '\'')) {
				sys_msg($_LANG['name_exist'], 1);
			}

			$attr = array('cat_id' => $cat_id, 'attr_name' => $_POST['attr_name'], 'attr_cat_type' => $_POST['attr_cat_type'], 'attr_index' => $_POST['attr_index'], 'sort_order' => $sort_order, 'attr_input_type' => $_POST['attr_input_type'], 'is_linked' => $_POST['is_linked'], 'attr_values' => isset($_POST['attr_values']) ? $_POST['attr_values'] : '', 'attr_type' => empty($_POST['attr_type']) ? '0' : intval($_POST['attr_type']), 'attr_group' => isset($_POST['attr_group']) ? intval($_POST['attr_group']) : 0);

			if ($is_insert) {
				$db->autoExecute($ecs->table('attribute'), $attr, 'INSERT');
				$attr_id = $db->insert_id();
				$sql = 'SELECT MAX(sort_order) AS sort FROM ' . $ecs->table('attribute') . ' WHERE cat_id = \'' . $cat_id . '\'';
				$sort = $db->getOne($sql);
				if (empty($attr['sort_order']) && !empty($sort)) {
					$attr = array('sort_order' => $attr_id);
					$db->autoExecute($ecs->table('attribute'), $attr, 'UPDATE', 'attr_id = \'' . $attr_id . '\'');
				}

				admin_log($_POST['attr_name'], 'add', 'attribute');
				$links = array(
					array('text' => $_LANG['add_next'], 'href' => '?act=add&goods_type=' . $cat_id),
					array('text' => $_LANG['back_list'], 'href' => '?act=list&goods_type=' . $cat_id)
					);
				sys_msg(sprintf($_LANG['add_ok'], $attr['attr_name']), 0, $links);
			}
			else {
				$db->autoExecute($ecs->table('attribute'), $attr, 'UPDATE', 'attr_id = \'' . $exclude . '\'');
				admin_log($_POST['attr_name'], 'edit', 'attribute');
				$links = array(
					array('text' => $_LANG['back_list'], 'href' => '?act=list&amp;goods_type=' . $cat_id . '')
					);
				sys_msg(sprintf($_LANG['edit_ok'], $attr['attr_name']), 0, $links);
			}
		}
	}
}

if ($_REQUEST['act'] == 'set_gcolor') {
	$attr_id = (empty($_REQUEST['attr_id']) ? 0 : intval($_REQUEST['attr_id']));
	$sql = 'SELECT color_values, cat_id, attr_input_type, attr_values FROM ' . $ecs->table('attribute') . ' WHERE attr_id = \'' . $attr_id . '\' LIMIT 1';
	$attribute = $db->getRow($sql);
	$attribute['attr_input_type'] = isset($attribute['attr_input_type']) ? $attribute['attr_input_type'] : 0;
	$attr_values = (isset($attribute['attr_values']) && !empty($attribute['attr_values']) ? $attribute['attr_values'] : '');
	$color_values = (isset($attribute['color_values']) && !empty($attribute['color_values']) ? $attribute['color_values'] : '');
	$cat_id = (isset($attribute['cat_id']) && !empty($attribute['cat_id']) ? intval($attribute['cat_id']) : 0);
	$list = array();
	$list2 = array();

	if (!empty($color_values)) {
		if ($attribute['attr_input_type'] == 1) {
			$list = explode("\n", trim($color_values));
		}
		else {
			$sql = 'SELECT GROUP_CONCAT(ga.attr_value) AS attr_values FROM ' . $GLOBALS['ecs']->table('attribute') . ' AS a, ' . $GLOBALS['ecs']->table('goods_attr') . ' AS ga ' . ' WHERE a.attr_id = ga.attr_id AND a.attr_id = \'' . $attr_id . '\'';
			$attribute_info = $GLOBALS['db']->getRow($sql);

			if ($attribute_info) {
				$list = explode(',', trim($attribute_info['attr_values']));
			}
		}

		if (empty($attr_valuess) && !empty($color_values)) {
			$list = explode("\n", trim($color_values));
		}

		for ($i = 0; $i < count($list); $i++) {
			if (!stripos($list[$i], '_#')) {
				$list[$i] = trim($list[$i]) . '_#FFFFFF';
			}

			$color = explode('_', $list[$i]);
			$list2[$i] = $color;
		}
	}

	$attr_values = get_add_attr_values($attr_id, 1, $list2, $attribute['attr_input_type']);
	$smarty->assign('attr_values', $attr_values);
	$smarty->assign('attr_id', $attr_id);
	$smarty->assign('cat_id', $cat_id);
	$smarty->assign('ur_here', $_LANG['set_gcolor']);
	$smarty->assign('form_act', 'gcolor_insert');
	$smarty->assign('action_link', array('href' => 'attribute.php?act=list&goods_type=' . $cat_id, 'text' => $_LANG['back_list']));
	assign_query_info();
	$smarty->display('set_gcolor.dwt');
}

if ($_REQUEST['act'] == 'gcolor_insert') {
	$attr_id = (empty($_REQUEST['attr_id']) ? 0 : intval($_REQUEST['attr_id']));
	$cat_id = (empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']));
	unset($_POST['attr_id']);
	unset($_POST['cat_id']);
	unset($_POST['act']);
	$str = '';

	foreach ($_POST as $key_c => $value_c) {
		if (empty($value_c)) {
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'attribute.php?act=set_gcolor&attr_id=' . $attr_id);
			sys_msg('请选择颜色', 0, $link);
			exit();
		}
	}

	foreach ($_POST as $k => $v) {
		if (strpos($v, '#') !== false) {
			$v = substr($v, 1);
		}

		$sql = 'UPDATE ' . $ecs->table('goods_attr') . ' SET color_value = \'' . $v . '\'' . ' WHERE attr_id = \'' . $attr_id . '\' AND attr_value = \'' . $k . '\'';
		$db->query($sql);
	}

	foreach ($_POST as $k => $v) {
		if (strpos($v, '#') !== false) {
			$v = substr($v, 1);
		}

		$str .= $k . '_#' . $v . "\n";
	}

	$str = strtoupper(trim($str));
	$update_color = 'UPDATE ' . $ecs->table('attribute') . ' SET `color_values` = \'' . $str . '\' WHERE `attr_id` = \'' . $attr_id . '\'';
	$db->query($update_color);
	$link[] = array('text' => $_LANG['back_list'], 'href' => 'attribute.php?act=list&goods_type=' . $cat_id);
	$link[] = array('text' => $_LANG['edit_attr'], 'href' => 'attribute.php?act=edit&attr_id=' . $attr_id);
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'attribute.php?act=set_gcolor&attr_id=' . $attr_id);
	sys_msg('属颜色修改成功', 0, $link);
}
else if ($_REQUEST['act'] == 'add_img') {
	admin_priv('attr_manage');
	$attr_id = (isset($_REQUEST['attr_id']) ? intval($_REQUEST['attr_id']) : 0);
	$attr_name = (isset($_REQUEST['attr_name']) ? $_REQUEST['attr_name'] : '');
	$attr_values = get_add_attr_values($attr_id);
	$smarty->assign('attr_values', $attr_values);
	$smarty->assign('attr_name', $attr_name);
	$smarty->assign('attr_id', $attr_id);
	$smarty->assign('ur_here', '编辑属性图片');
	$smarty->assign('action_link2', array('href' => 'attribute.php?act=edit&attr_id=' . $attr_id, 'text' => $_LANG['go_back']));
	$smarty->assign('action_link', array('href' => 'attribute.php?act=list', 'text' => $_LANG['09_attribute_list']));
	$smarty->assign('form_act', 'insert_img');
	assign_query_info();
	$smarty->display('attribute_img.dwt');
}
else if ($_REQUEST['act'] == 'insert_img') {
	admin_priv('attr_manage');
	$attr_id = (isset($_REQUEST['attr_id']) ? intval($_REQUEST['attr_id']) : 0);
	$attr_name = (isset($_REQUEST['attr_name']) ? $_REQUEST['attr_name'] : 0);
	$attr_values = get_add_attr_values($attr_id);
	get_attrimg_insert_update($attr_id, $attr_values);
	$link[0] = array('text' => $_LANG['go_back'], 'href' => 'attribute.php?act=add_img&attr_id=' . $attr_id . '&attr_name=' . $attr_name);
	$link[1] = array('text' => '返回该属性', 'href' => 'attribute.php?act=edit&attr_id=' . $attr_id);
	$link[2] = array('text' => $_LANG['09_attribute_list'], 'href' => 'attribute.php?act=list');
	sys_msg('编辑成功', 0, $link);
}
else if ($_REQUEST['act'] == 'batch') {
	admin_priv('attr_manage');

	if (isset($_POST['checkboxes'])) {
		$count = count($_POST['checkboxes']);
		$ids = (isset($_POST['checkboxes']) ? join(',', $_POST['checkboxes']) : 0);
		$sql = 'DELETE FROM ' . $ecs->table('attribute') . ' WHERE attr_id ' . db_create_in($ids);
		$db->query($sql);
		$sql = 'DELETE FROM ' . $ecs->table('goods_attr') . ' WHERE attr_id ' . db_create_in($ids);
		$db->query($sql);
		admin_log('', 'batch_remove', 'attribute');
		clear_cache_files();
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'attribute.php?act=list');
		sys_msg(sprintf($_LANG['drop_ok'], $count), 0, $link);
	}
	else {
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'attribute.php?act=list');
		sys_msg($_LANG['no_select_arrt'], 0, $link);
	}
}
else if ($_REQUEST['act'] == 'edit_attr_name') {
	check_authz_json('attr_manage');
	$id = intval($_POST['id']);
	$val = json_str_iconv(trim($_POST['val']));
	$cat_id = $exc->get_name($id, 'cat_id');

	if (!$exc->is_only('attr_name', $val, $id, ' cat_id = \'' . $cat_id . '\'')) {
		make_json_error($_LANG['name_exist']);
	}

	$exc->edit('attr_name=\'' . $val . '\'', $id);
	admin_log($val, 'edit', 'attribute');
	make_json_result(stripslashes($val));
}
else if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('attr_manage');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	$exc->edit('sort_order=\'' . $val . '\'', $id);
	admin_log(addslashes($exc->get_name($id)), 'edit', 'attribute');
	clear_all_files();
	make_json_result(stripslashes($val));
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('attr_manage');
	$id = intval($_GET['id']);
	$db->query('DELETE FROM ' . $ecs->table('attribute') . ' WHERE attr_id=\'' . $id . '\'');
	$db->query('DELETE FROM ' . $ecs->table('goods_attr') . ' WHERE attr_id=\'' . $id . '\'');
	$url = 'attribute.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'get_attr_num') {
	check_authz_json('attr_manage');
	$id = intval($_GET['attr_id']);
	$sql = 'SELECT COUNT(*) ' . ' FROM ' . $ecs->table('goods_attr') . ' AS a, ' . $ecs->table('goods') . ' AS g ' . ' WHERE g.goods_id = a.goods_id AND g.is_delete = 0 AND attr_id = \'' . $id . '\' ';
	$goods_num = $db->getOne($sql);

	if (0 < $goods_num) {
		$drop_confirm = sprintf($_LANG['notice_drop_confirm'], $goods_num);
	}
	else {
		$drop_confirm = $_LANG['drop_confirm'];
	}

	make_json_result(array('attr_id' => $id, 'drop_confirm' => $drop_confirm));
}
else if ($_REQUEST['act'] == 'get_attr_groups') {
	check_authz_json('attr_manage');
	$cat_id = intval($_GET['cat_id']);
	$groups = get_attr_groups($cat_id);
	make_json_result($groups);
}

?>
