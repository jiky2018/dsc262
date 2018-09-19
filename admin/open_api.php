<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function open_api_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$where = ' WHERE 1 ';
		$filter['record_count'] = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('open_api') . $where);
		$filter = page_and_size($filter);
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('open_api') . $where . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$open_api_list = $GLOBALS['db']->getAll($sql);
	$count = count($open_api_list);

	for ($i = 0; $i < $count; $i++) {
		$open_api_list[$i]['add_time'] = local_date('Y-m-d H:i:s', $open_api_list[$i]['add_time']);
	}

	$arr = array('open_api_list' => $open_api_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_api_data($api_data = array(), $action_code)
{
	for ($i = 0; $i < count($api_data); $i++) {
		for ($j = 0; $j < count($api_data[$i]['list']); $j++) {
			$api_data[$i]['list'][$j]['is_check'] = 0;

			if ($action_code) {
				if (in_array($api_data[$i]['list'][$j]['val'], $action_code)) {
					$api_data[$i]['list'][$j]['is_check'] = 1;
				}
			}
		}
	}

	return $api_data;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . DATA_DIR . '/api_list.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

admin_priv('open_api');
$smarty->assign('menu_select', array('action' => '01_system', 'current' => 'open_api'));

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('action_link', array('text' => $_LANG['02_openapi_add'], 'href' => 'open_api.php?act=add'));
	$smarty->assign('ur_here', $_LANG['open_api']);
	$smarty->assign('form_act', 'insert');
	$open_api_list = open_api_list();
	$smarty->assign('open_api_list', $open_api_list['open_api_list']);
	$smarty->assign('filter', $open_api_list['filter']);
	$smarty->assign('record_count', $open_api_list['record_count']);
	$smarty->assign('page_count', $open_api_list['page_count']);
	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->display('openapi_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$open_api_list = open_api_list();
	$smarty->assign('open_api_list', $open_api_list['open_api_list']);
	$smarty->assign('filter', $open_api_list['filter']);
	$smarty->assign('record_count', $open_api_list['record_count']);
	$smarty->assign('page_count', $open_api_list['page_count']);
	$sort_flag = sort_flag($open_api_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('openapi_list.dwt'), '', array('filter' => $open_api_list['filter'], 'page_count' => $open_api_list['page_count']));
}

if ($_REQUEST['act'] == 'add') {
	$smarty->assign('action_link', array('text' => $_LANG['01_openapi_list'], 'href' => 'open_api.php?act=list'));
	$smarty->assign('ur_here', $_LANG['open_api']);
	$smarty->assign('form_act', 'insert');
	$smarty->assign('api_list', $api_data);
	assign_query_info();
	$smarty->display('openapi_info.dwt');
}

if ($_REQUEST['act'] == 'edit') {
	$id = (empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']));
	$smarty->assign('action_link', array('text' => $_LANG['01_openapi_list'], 'href' => 'open_api.php?act=list'));
	$date = array('*');
	$where = 'id = \'' . $id . '\'';
	$api = get_table_date('open_api', $where, $date);
	$smarty->assign('api', $api);
	$action_code = (isset($api['action_code']) && !empty($api['action_code']) ? explode(',', $api['action_code']) : '');
	$smarty->assign('ur_here', $_LANG['open_api']);
	$smarty->assign('form_act', 'update');
	$api_data = get_api_data($api_data, $action_code);
	$smarty->assign('api_list', $api_data);
	assign_query_info();
	$smarty->display('openapi_info.dwt');
}
else {
	if (($_REQUEST['act'] == 'insert') || ($_REQUEST['act'] == 'update')) {
		$id = (empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']));
		$other['name'] = empty($_POST['name']) ? '' : trim($_POST['name']);
		$other['app_key'] = empty($_POST['app_key']) ? '' : trim($_POST['app_key']);
		$other['is_open'] = empty($_POST['is_open']) ? 0 : intval($_POST['is_open']);
		$other['action_code'] = empty($_POST['action_code']) ? '' : implode(',', $_POST['action_code']);

		if ($id) {
			$db->autoExecute($ecs->table('open_api'), $other, 'UPDATE', 'id = \'' . $id . '\'');
			$href = 'open_api.php?act=edit&id=' . $id;
			$lang_name = $_LANG['edit_success'];
		}
		else {
			$other['add_time'] = gmtime();
			$db->autoExecute($ecs->table('open_api'), $other);
			$href = 'open_api.php?act=list';
			$lang_name = $_LANG['add_success'];
		}

		$link[] = array('text' => $_LANG['go_back'], 'href' => $href);
		sys_msg(sprintf($lang_name, htmlspecialchars(stripslashes($other['name']))), 0, $link);
	}
	else if ($_REQUEST['act'] == 'batch_remove') {
		if (isset($_REQUEST['checkboxes'])) {
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('open_api') . ' WHERE id ' . db_create_in($_REQUEST['checkboxes']);
			$GLOBALS['db']->query($sql);
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'open_api.php?act=list');
			sys_msg($_LANG['remove_success'], 0, $link);
		}
		else {
			$lnk[] = array('text' => $_LANG['go_back'], 'href' => 'open_api.php?act=list');
			sys_msg($_LANG['no_select_user'], 0, $lnk);
		}
	}
	else if ($_REQUEST['act'] == 'remove') {
		$id = (empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']));
		$sql = 'SELECT name FROM ' . $GLOBALS['ecs']->table('open_api') . ' WHERE id = \'' . $id . '\'';
		$name = $GLOBALS['db']->getOne($sql);
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('open_api') . ' WHERE id = \'' . $id . '\'';
		$GLOBALS['db']->query($sql);
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'open_api.php?act=list');
		sys_msg(sprintf($_LANG['remove_success'], $name), 0, $link);
	}
	else if ($_REQUEST['act'] == 'app_key') {
		check_authz_json('open_api');
		$name = (empty($_REQUEST['name']) ? '' : trim($_REQUEST['name']));
		$result = array('error' => 0, 'message' => '', 'content' => '');
		$guid = sc_guid();
		$result['app_key'] = $guid;
		exit(json_encode($result));
	}
}

?>
