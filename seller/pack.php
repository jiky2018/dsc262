<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function packs_list($ru_id)
{
	$where = ' WHERE 1';

	if (0 < $ru_id) {
		$where .= ' AND user_id = \'' . $ru_id . '\' ';
	}

	$result = get_filter();

	if ($result === false) {
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'pack_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('pack') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT pack_id, user_id, pack_name, pack_img, pack_fee, free_money, pack_desc' . ' FROM ' . $GLOBALS['ecs']->table('pack') . $where . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$packs_list = $GLOBALS['db']->getAll($sql);
	$count = count($packs_list);

	for ($i = 0; $i < $count; $i++) {
		$packs_list[$i]['ru_name'] = get_shop_name($packs_list[$i]['user_id'], 1);
	}

	$arr = array('packs_list' => $packs_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
include_once ROOT_PATH . 'includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);
$smarty->assign('menus', $_SESSION['menus']);
$exc = new exchange($ecs->table('pack'), $db, 'pack_id', 'pack_name');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$smarty->assign('controller', basename(PHP_SELF, '.php'));

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('ur_here', $_LANG['06_pack_list']);
	$smarty->assign('action_link', array('text' => $_LANG['pack_add'], 'href' => 'pack.php?act=add'));
	$smarty->assign('full_page', 1);
	$packs_list = packs_list($adminru['ru_id']);
	$smarty->assign('packs_list', $packs_list['packs_list']);
	$smarty->assign('filter', $packs_list['filter']);
	$smarty->assign('record_count', $packs_list['record_count']);
	$smarty->assign('page_count', $packs_list['page_count']);
	assign_query_info();
	$smarty->display('pack_list.htm');
}
else if ($_REQUEST['act'] == 'query') {
	$packs_list = packs_list($adminru['ru_id']);
	$smarty->assign('packs_list', $packs_list['packs_list']);
	$smarty->assign('filter', $packs_list['filter']);
	$smarty->assign('record_count', $packs_list['record_count']);
	$smarty->assign('page_count', $packs_list['page_count']);
	$sort_flag = sort_flag($packs_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('pack_list.htm'), '', array('filter' => $packs_list['filter'], 'page_count' => $packs_list['page_count']));
}

if ($_REQUEST['act'] == 'add') {
	admin_priv('pack');
	$pack['pack_fee'] = 0;
	$pack['free_money'] = 0;
	$smarty->assign('pack', $pack);
	$smarty->assign('ur_here', $_LANG['pack_add']);
	$smarty->assign('form_action', 'insert');
	$smarty->assign('action_link', array('text' => $_LANG['06_pack_list'], 'href' => 'pack.php?act=list'));
	assign_query_info();
	$smarty->display('pack_info.htm');
}

if ($_REQUEST['act'] == 'insert') {
	admin_priv('pack');
	$is_only = $exc->is_only('pack_name', $_POST['pack_name']);

	if (!$is_only) {
		sys_msg(sprintf($_LANG['packname_exist'], stripslashes($_POST['pack_name'])), 1);
	}

	if (!empty($_FILES['pack_img'])) {
		$upload_img = $image->upload_image($_FILES['pack_img'], 'packimg', $_POST['old_packimg']);

		if ($upload_img == false) {
			sys_msg($image->error_msg);
		}

		$img_name = basename($upload_img);
	}
	else {
		$img_name = '';
	}

	get_oss_add_file(array(DATA_DIR . '/packimg/' . $img_name));
	$sql = 'INSERT INTO ' . $ecs->table('pack') . "(pack_name, pack_fee, free_money, pack_desc, pack_img, user_id)\r\n            VALUES ('" . $_POST['pack_name'] . '\', \'' . $_POST['pack_fee'] . '\', \'' . $_POST['free_money'] . '\', \'' . $_POST['pack_desc'] . '\', \'' . $img_name . '\', \'' . $adminru['ru_id'] . '\')';
	$db->query($sql);
	$link[0]['text'] = $_LANG['back_list'];
	$link[0]['href'] = 'pack.php?act=list';
	$link[1]['text'] = $_LANG['continue_add'];
	$link[1]['href'] = 'pack.php?act=add';
	sys_msg($_POST['pack_name'] . $_LANG['packadd_succed'], 0, $link);
	admin_log($_POST['pack_name'], 'add', 'pack');
}

if ($_REQUEST['act'] == 'edit') {
	admin_priv('pack');
	$sql = 'SELECT pack_id, pack_name, pack_fee, free_money, pack_desc, pack_img FROM ' . $ecs->table('pack') . ' WHERE pack_id=\'' . $_REQUEST['id'] . '\'';
	$pack = $db->GetRow($sql);
	$smarty->assign('ur_here', $_LANG['pack_edit']);
	$smarty->assign('action_link', array('text' => $_LANG['06_pack_list'], 'href' => 'pack.php?act=list&' . list_link_postfix()));
	$smarty->assign('pack', $pack);
	$smarty->assign('form_action', 'update');
	$smarty->display('pack_info.htm');
}

if ($_REQUEST['act'] == 'update') {
	admin_priv('pack');

	if ($_POST['pack_name'] != $_POST['old_packname']) {
		$is_only = $exc->is_only('pack_name', $_POST['pack_name'], $_POST['id']);

		if (!$is_only) {
			sys_msg(sprintf($_LANG['packname_exist'], stripslashes($_POST['pack_name'])), 1);
		}
	}

	$param = 'pack_name = \'' . $_POST['pack_name'] . '\', pack_fee = \'' . $_POST['pack_fee'] . '\', free_money= \'' . $_POST['free_money'] . '\', pack_desc = \'' . $_POST['pack_desc'] . '\' ';

	if (!empty($_FILES['pack_img']['name'])) {
		$upload_img = $image->upload_image($_FILES['pack_img'], 'packimg', $_POST['old_packimg']);

		if ($upload_img == false) {
			sys_msg($image->error_msg);
		}

		$img_name = basename($upload_img);
	}
	else {
		$img_name = '';
	}

	get_oss_add_file(array(DATA_DIR . '/packimg/' . $img_name));

	if (!empty($img_name)) {
		$param .= ' ,pack_img = \'' . $img_name . '\' ';
	}

	if ($exc->edit($param, $_POST['id'])) {
		$link[0]['text'] = $_LANG['back_list'];
		$link[0]['href'] = 'pack.php?act=list&' . list_link_postfix();
		$note = sprintf($_LANG['packedit_succed'], $_POST['pack_name']);
		sys_msg($note, 0, $link);
		admin_log($_POST['pack_name'], 'edit', 'pack');
	}
	else {
		exit($db->error());
	}
}

if ($_REQUEST['act'] == 'drop_pack_img') {
	admin_priv('pack');
	$pack_id = (isset($_GET['id']) ? intval($_GET['id']) : 0);
	get_del_batch('', $pack_id, array('pack_img'), 'pack_id', 'pack', 0, DATA_DIR . '/packimg/');
	$sql = 'UPDATE ' . $ecs->table('pack') . ' SET pack_img = \'\' WHERE pack_id = \'' . $pack_id . '\'';
	$db->query($sql);
	$link = array(
		array('text' => $_LANG['pack_edit_lnk'], 'href' => 'pack.php?act=edit&id=' . $pack_id),
		array('text' => $_LANG['pack_list_lnk'], 'href' => 'pack.php?act=list')
		);
	sys_msg($_LANG['drop_pack_img_success'], 0, $link);
}

if ($_REQUEST['act'] == 'edit_name') {
	check_authz_json('pack');
	$id = intval($_POST['id']);
	$val = json_str_iconv(trim($_POST['val']));
	$pack_name = $exc->get_name($id);

	if (!$exc->is_only('pack_name', $val, $id)) {
		make_json_error(sprintf($_LANG['packname_exist'], $pack_name));
	}
	else {
		$exc->edit('pack_name=\'' . $val . '\'', $id);
		admin_log($val, 'edit', 'pack');
		make_json_result(stripslashes($val));
	}
}

if ($_REQUEST['act'] == 'edit_pack_fee') {
	check_authz_json('pack');
	$id = intval($_POST['id']);
	$val = floatval($_POST['val']);
	$pack_name = $exc->get_name($id);
	$exc->edit('pack_fee=\'' . $val . '\'', $id);
	admin_log(addslashes($pack_name), 'edit', 'pack');
	make_json_result(number_format($val, 2));
}

if ($_REQUEST['act'] == 'edit_free_money') {
	check_authz_json('pack');
	$id = intval($_POST['id']);
	$val = floatval($_POST['val']);
	$pack_name = $exc->get_name($id);
	$exc->edit('free_money=\'' . $val . '\'', $id);
	admin_log(addslashes($pack_name), 'edit', 'pack');
	make_json_result(number_format($val, 2));
}

if ($_REQUEST['act'] == 'remove') {
	check_authz_json('pack');
	$id = intval($_GET['id']);
	$name = $exc->get_name($id);
	get_del_batch('', $id, array('pack_img'), 'pack_id', 'pack', 0, DATA_DIR . '/packimg/');

	if ($exc->drop($id)) {
		admin_log(addslashes($name), 'remove', 'pack');
		$url = 'pack.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
		exit();
	}
	else {
		make_json_error($_LANG['packremove_falure']);
		return false;
	}
}

?>
