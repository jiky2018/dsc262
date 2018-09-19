<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function zc_initiator_list($conditions = '')
{
	$result = get_filter();

	if ($result === false) {
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$where = ' WHERE 1=1 ';

		if (!empty($filter['keyword'])) {
			$where .= ' AND name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\' ';
		}

		$where .= $conditions;
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('zc_initiator') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT `id`, `name`, `img`, `company`, `intro`, `describe`, `rank` ' . ' FROM ' . $GLOBALS['ecs']->table('zc_initiator') . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ' . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keyword'] = stripslashes($filter['keyword']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);
	return array('zc_initiator' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function zc_rank_logo_list()
{
	$sql = 'SELECT `id`, `logo_name`, `img`, `logo_intro` FROM ' . $GLOBALS['ecs']->table('zc_rank_logo');
	$row = $GLOBALS['db']->getAll($sql);
	return $row;
}

function get_rank_logo($id)
{
	$sql = ' SELECT img FROM ' . $GLOBALS['ecs']->table('zc_rank_logo') . ' WHERE id = \'' . $id . '\' ';
	$row = $GLOBALS['db']->getRow($sql);
	return $row;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require dirname(__FILE__) . '/includes/lib_goods.php';
include_once ROOT_PATH . '/includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$smarty->assign('act', $_REQUEST['act']);

if ($_REQUEST['act'] == 'list') {
	admin_priv('zc_initiator_manage');
	$cat_id = (empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']));
	$smarty->assign('ur_here', $_LANG['03_project_initiator']);
	$action_link = array('href' => 'zc_initiator.php?act=rank_logo', 'text' => $_LANG['rank_logo_manage']);
	$action_link2 = array('href' => 'zc_initiator.php?act=add', 'text' => $_LANG['add_zc_initiator']);
	$smarty->assign('action_link2', $action_link2);
	$smarty->assign('action_link', $action_link);
	$list = zc_initiator_list();

	foreach ($list['zc_initiator'] as $k => $v) {
		$logo = explode(',', $v['rank']);

		if ($logo) {
			foreach ($logo as $val) {
				$list['zc_initiator'][$k]['logo'][] = get_rank_logo($val);
			}
		}
	}

	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('initiator', $list['zc_initiator']);
	$smarty->display('zc_initiator_list.dwt');
}

if ($_REQUEST['act'] == 'query') {
	$list = zc_initiator_list();

	foreach ($list['zc_initiator'] as $k => $v) {
		$logo = explode(',', $v['rank']);

		if ($logo) {
			foreach ($logo as $val) {
				$list['zc_initiator'][$k]['logo'][] = get_rank_logo($val);
			}
		}
	}

	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$smarty->assign('initiator', $list['zc_initiator']);
	make_json_result($smarty->fetch('zc_initiator_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else {
	if (($_REQUEST['act'] == 'add') || ($_REQUEST['act'] == 'edit')) {
		admin_priv('zc_initiator_manage');

		if ($_REQUEST['act'] == 'add') {
			$smarty->assign('ur_here', $_LANG['add_zc_initiator']);
		}

		if ($_REQUEST['act'] == 'edit') {
			$smarty->assign('ur_here', $_LANG['edit_zc_initiator']);
		}

		$action_link = array('href' => 'zc_initiator.php?act=list', 'text' => $_LANG['03_project_initiator']);
		$smarty->assign('action_link', $action_link);

		if ($_GET['id']) {
			$id = $_GET['id'];
			$sql = ' SELECT * FROM ' . $ecs->table('zc_initiator') . ' WHERE id = \'' . $id . '\' ';
			$result = $db->getRow($sql);
			$logo_sql = ' SELECT id, logo_name FROM ' . $ecs->table('zc_rank_logo');
			$res = $db->getAll($logo_sql);
			$smarty->assign('logo', $res);
			$smarty->assign('state', 'update');
			$smarty->assign('result', $result);
			$smarty->display('zc_initiator_info.dwt');
		}
		else {
			$sql = ' SELECT id, logo_name FROM ' . $ecs->table('zc_rank_logo');
			$res = $db->getAll($sql);
			$smarty->assign('logo', $res);
			$smarty->assign('state', 'insert');
			$smarty->display('zc_initiator_info.dwt');
		}
	}
	else if ($_REQUEST['act'] == 'insert') {
		admin_priv('zc_initiator_manage');
		$name = (!empty($_POST['name']) ? trim($_POST['name']) : '');
		$company = (!empty($_POST['company']) ? trim($_POST['company']) : '');
		$intro = (!empty($_POST['intro']) ? trim($_POST['intro']) : '');
		$describe = (!empty($_POST['describe']) ? trim($_POST['describe']) : '');
		$logo = (!empty($_POST['logo']) ? intval($_POST['logo']) : 0);
		$sql = ' SELECT id FROM ' . $ecs->table('zc_initiator') . ' WHERE name = \'' . $name . '\' ';
		$is_exist = $db->getOne($sql);

		if ($is_exist) {
			$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
			$links[0]['href'] = 'javascript:history.go(-1)';
			sys_msg($_LANG['name_repeat'], 1, $links);
			exit();
		}

		$img = '';
		$dir = 'initiator_image';
		$img = $image->upload_image($_FILES['img'], $dir);
		$sql = ' INSERT INTO' . $ecs->table('zc_initiator') . '(`id`,`name`,`company`,`img`,`intro`,`describe`,`rank`) ' . ' VALUES (\'\',\'' . $name . '\',\'' . $company . '\',\'' . $img . '\',\'' . $intro . '\',\'' . $describe . '\',\'' . $logo . '\') ';
		$insert = $db->query($sql);

		if ($insert) {
			$links[0]['text'] = $_LANG['go_list'];
			$links[0]['href'] = 'zc_initiator.php?act=list';
			sys_msg($_LANG['add_succeed'], 0, $links);
		}
		else {
			$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
			$links[0]['href'] = 'javascript:history.go(-1)';
			sys_msg($_LANG['add_failure'], 1, $links);
		}
	}
	else if ($_REQUEST['act'] == 'update') {
		admin_priv('zc_initiator_manage');
		$id = (!empty($_POST['init_id']) ? trim($_POST['init_id']) : 0);
		$name = (!empty($_POST['name']) ? trim($_POST['name']) : '');
		$company = (!empty($_POST['company']) ? trim($_POST['company']) : '');
		$intro = (!empty($_POST['intro']) ? trim($_POST['intro']) : '');
		$describe = (!empty($_POST['describe']) ? trim($_POST['describe']) : '');
		$logo = (!empty($_POST['logo']) ? intval($_POST['logo']) : 0);
		$sql = ' SELECT id FROM ' . $ecs->table('zc_initiator') . ' WHERE name = \'' . $name . '\' and id <> ' . $id . ' ';
		$is_exist = $db->getOne($sql);

		if ($is_exist) {
			$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
			$links[0]['href'] = 'javascript:history.go(-1)';
			sys_msg($_LANG['name_repeat'], 1, $links);
			exit();
		}

		$img = '';
		$dir = 'initiator_image';

		if (!empty($_FILES['img']['name'])) {
			$img = $image->upload_image($_FILES['img'], $dir);
		}

		$sql = 'SELECT img ' . ' FROM ' . $ecs->table('zc_initiator') . ' WHERE id = \'' . $id . '\'';
		$row = $db->getRow($sql);
		if (($img != '') && $row['img']) {
			@unlink(ROOT_PATH . $row['img']);
		}

		$sql = ' UPDATE ' . $ecs->table('zc_initiator') . ' SET ' . ' `name`=\'' . $name . '\', ' . ' `company`=\'' . $company . '\', ' . ' `intro`=\'' . $intro . '\', ' . ' `describe`=\'' . $describe . '\', ';

		if ($img) {
			$sql .= ' `img`=\'' . $img . '\', ';
		}

		$sql .= ' `rank`=\'' . $logo . '\' WHERE id=\'' . $id . '\' ';
		$update = $db->query($sql);

		if ($update) {
			$links[0]['text'] = $_LANG['go_list'];
			$links[0]['href'] = 'zc_initiator.php?act=list';
			sys_msg($_LANG['edit_success'], 0, $links);
		}
		else {
			$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
			$links[0]['href'] = 'javascript:history.go(-1)';
			sys_msg($_LANG['edit_fail'], 1, $links);
		}
	}
	else if ($_REQUEST['act'] == 'del') {
		admin_priv('zc_initiator_manage');
		$id = $_GET['id'];
		$sql = ' SELECT count(*) FROM ' . $ecs->table('zc_initiator') . ' WHERE id = \'' . $id . '\' ';
		$res = $db->getOne($sql);
		$sql = 'SELECT img ' . ' FROM ' . $ecs->table('zc_initiator') . ' WHERE id = \'' . $id . '\'';
		$row = $db->getRow($sql);
		@unlink(ROOT_PATH . $row['img']);
		$sql = ' DELETE FROM ' . $ecs->table('zc_initiator') . ' WHERE id = \'' . $id . '\' ';
		$db->query($sql);
		Header('Location:zc_initiator.php?act=list');
	}
}

if ($_REQUEST['act'] == 'rank_logo') {
	admin_priv('zc_initiator_manage');
	$smarty->assign('ur_here', $_LANG['rank_logo_manage']);
	$action_link = array('href' => 'zc_initiator.php?act=list', 'text' => $_LANG['03_project_initiator']);
	$action_link2 = array('href' => 'zc_initiator.php?act=add_rank_logo', 'text' => $_LANG['add_rank_logo']);
	$smarty->assign('action_link', $action_link);
	$smarty->assign('action_link2', $action_link2);
	$list = zc_rank_logo_list();
	$smarty->assign('arr_zc', $list);
	$smarty->assign('full_page', 1);
	$smarty->display('zc_rank_logo_list.dwt');
}
else {
	if (($_REQUEST['act'] == 'add_rank_logo') || ($_REQUEST['act'] == 'edit_rank_logo')) {
		admin_priv('zc_initiator_manage');

		if ($_REQUEST['act'] == 'add_rank_logo') {
			$smarty->assign('ur_here', $_LANG['add_rank_logo']);
		}

		if ($_REQUEST['act'] == 'edit_rank_logo') {
			$smarty->assign('ur_here', $_LANG['edit_rank_logo']);
		}

		$action_link = array('href' => 'zc_initiator.php?act=rank_logo', 'text' => $_LANG['rank_logo_manage']);
		$smarty->assign('action_link', $action_link);

		if ($_GET['id']) {
			$id = $_GET['id'];
			$sql = ' SELECT * FROM ' . $ecs->table('zc_rank_logo') . ' WHERE id = \'' . $id . '\' ';
			$result = $db->getRow($sql);
			$smarty->assign('logo_id', $id);
			$smarty->assign('state', 'update_rank');
			$smarty->assign('result', $result);
			$smarty->display('zc_rank_logo_info.dwt');
		}
		else {
			$smarty->assign('state', 'insert_rank');
			$smarty->display('zc_rank_logo_info.dwt');
		}
	}
	else if ($_REQUEST['act'] == 'insert_rank') {
		admin_priv('zc_initiator_manage');
		$logo_name = (!empty($_POST['logo_name']) ? trim($_POST['logo_name']) : '');
		$intro = (!empty($_POST['intro']) ? trim($_POST['intro']) : '');
		$sql = ' SELECT id FROM ' . $ecs->table('zc_rank_logo') . ' WHERE logo_name = \'' . $logo_name . '\' ';
		$is_exist = $db->getOne($sql);

		if ($is_exist) {
			$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
			$links[0]['href'] = 'javascript:history.go(-1)';
			sys_msg($_LANG['name_repeat'], 1, $links);
			exit();
		}

		$img = '';
		$dir = 'rank_image';
		$img = $image->upload_image($_FILES['img'], $dir);
		$sql = ' INSERT INTO' . $ecs->table('zc_rank_logo') . '(`id`,`logo_name`,`img`,`logo_intro`) ' . ' VALUES (\'\',\'' . $logo_name . '\',\'' . $img . '\',\'' . $intro . '\') ';
		$insert = $db->query($sql);

		if ($insert) {
			$links[0]['text'] = $_LANG['go_list'];
			$links[0]['href'] = 'zc_initiator.php?act=rank_logo';
			sys_msg($_LANG['add_succeed'], 0, $links);
		}
		else {
			$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
			$links[0]['href'] = 'javascript:history.go(-1)';
			sys_msg($_LANG['add_failure'], 1, $links);
		}
	}
	else if ($_REQUEST['act'] == 'update_rank') {
		admin_priv('zc_initiator_manage');
		$id = (!empty($_POST['logo_id']) ? trim($_POST['logo_id']) : 0);
		$logo_name = (!empty($_POST['logo_name']) ? trim($_POST['logo_name']) : '');
		$intro = (!empty($_POST['intro']) ? trim($_POST['intro']) : '');
		$img = '';
		$dir = 'rank_image';

		if (!empty($_FILES['img']['name'])) {
			$img = $image->upload_image($_FILES['img'], $dir);
		}

		$sql = 'SELECT img ' . ' FROM ' . $ecs->table('zc_rank_logo') . ' WHERE id = \'' . $id . '\'';
		$row = $db->getRow($sql);
		if (($img != '') && $row['img']) {
			@unlink(ROOT_PATH . $row['img']);
		}

		$sql = ' UPDATE ' . $ecs->table('zc_rank_logo') . ' SET ' . ' `logo_name`=\'' . $logo_name . '\', ';

		if ($img) {
			$sql .= ' `img`=\'' . $img . '\', ';
		}

		$sql .= ' `logo_intro`=\'' . $intro . '\' WHERE id=\'' . $id . '\' ';
		$update = $db->query($sql);

		if ($update) {
			$links[0]['text'] = $_LANG['go_list'];
			$links[0]['href'] = 'zc_initiator.php?act=rank_logo';
			sys_msg($_LANG['edit_success'], 0, $links);
		}
		else {
			$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
			$links[0]['href'] = 'javascript:history.go(-1)';
			sys_msg($_LANG['edit_fail'], 1, $links);
		}
	}
	else if ($_REQUEST['act'] == 'del_rank_logo') {
		admin_priv('zc_initiator_manage');
		$id = $_GET['id'];
		$sql = 'SELECT img ' . ' FROM ' . $ecs->table('zc_rank_logo') . ' WHERE id = \'' . $id . '\'';
		$row = $db->getRow($sql);
		@unlink(ROOT_PATH . $row['img']);
		$sql = ' DELETE FROM ' . $ecs->table('zc_rank_logo') . ' WHERE id = \'' . $id . '\' ';
		$db->query($sql);
		Header('Location:zc_initiator.php?act=rank_logo');
	}
}

?>
