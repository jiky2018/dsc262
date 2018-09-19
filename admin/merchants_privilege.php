<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'allot';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

if ($_REQUEST['act'] == 'allot') {
	include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/priv_action.php';
	admin_priv('users_merchants_priv');
	$smarty->assign('menu_select', array('action' => '17_merchants', 'current' => '03_users_merchants_priv'));
	$sql = 'SELECT id FROM' . $ecs->table('seller_grade') . 'WHERE is_default = 1';
	$default_grade_id = $db->getOne($sql);
	$grade_id = !empty($_REQUEST['grade_id']) ? $_REQUEST['grade_id'] : $default_grade_id;
	$smarty->assign('grade_id', $grade_id);
	$sql = 'SELECT grade_name , id FROM' . $ecs->table('seller_grade');
	$seller_grade = $db->getAll($sql);
	$smarty->assign('seller_grade', $seller_grade);
	$priv_str = $db->getOne('SELECT action_list FROM ' . $ecs->table('merchants_privilege') . (' WHERE grade_id=\'' . $grade_id . '\''));
	$sql_query = 'SELECT action_id, parent_id, action_code,relevance FROM ' . $ecs->table('admin_action') . ' WHERE parent_id = 0 AND seller_show = 1';
	$res = $db->query($sql_query);

	while ($rows = $db->FetchRow($res)) {
		if (!file_exists(MOBILE_WECHAT) && $rows['action_code'] == 'wechat') {
			continue;
		}

		if (!file_exists(MOBILE_DRP) && $rows['action_code'] == 'drp') {
			continue;
		}

		if (!wxapp_enabled() && $rows['action_code'] == 'wxapp') {
			continue;
		}

		if (!file_exists(MOBILE_TEAM) && $rows['action_code'] == 'team') {
			continue;
		}

		if (!file_exists(MOBILE_BARGAIN) && $rows['action_code'] == 'bargain_manage') {
			continue;
		}

		$priv_arr[$rows['action_id']] = $rows;
	}

	if ($priv_arr) {
		$db_create_in = array_keys($priv_arr);
	}
	else {
		$db_create_in = '';
	}

	$sql = 'SELECT action_id, parent_id, action_code,relevance FROM ' . $ecs->table('admin_action') . ' WHERE parent_id ' . db_create_in($db_create_in) . ' AND seller_show = 1';
	$result = $db->query($sql);

	while ($priv = $db->FetchRow($result)) {
		$priv_arr[$priv['parent_id']]['priv'][$priv['action_code']] = $priv;
	}

	if ($priv_arr) {
		foreach ($priv_arr as $action_id => $action_group) {
			if ($action_group['priv']) {
				$priv = @array_keys($action_group['priv']);
				$priv_arr[$action_id]['priv_list'] = join(',', $priv);

				if (!empty($action_group['priv'])) {
					foreach ($action_group['priv'] as $key => $val) {
						$priv_arr[$action_id]['priv'][$key]['cando'] = strpos($priv_str, $val['action_code']) !== false || $priv_str == 'all' ? 1 : 0;
					}
				}
			}
		}
	}
	else {
		$priv_arr = array();
	}

	$smarty->assign('lang', $_LANG);
	$smarty->assign('ur_here', $_LANG['allot_priv']);
	$smarty->assign('priv_arr', $priv_arr);
	$smarty->assign('form_act', 'update_allot');
	assign_query_info();
	$smarty->display('merchants_privilege_allot.dwt');
}
else if ($_REQUEST['act'] == 'update_allot') {
	admin_priv('users_merchants_priv');
	$initialize_allot = isset($_POST['initialize_allot']) ? intval($_POST['initialize_allot']) : 0;
	$grade_id = !empty($_REQUEST['grade_id']) ? $_REQUEST['grade_id'] : 0;

	if ($_POST['action_code']) {
		$act_list = implode(',', $_POST['action_code']);
		$sql = 'SELECT action_list FROM ' . $GLOBALS['ecs']->table('merchants_privilege') . (' WHERE grade_id = \'' . $grade_id . '\'');
		$action_list = $db->getOne($sql);

		if ($action_list) {
			$sql = 'UPDATE ' . $ecs->table('merchants_privilege') . (' SET action_list = \'' . $act_list . '\' WHERE grade_id = \'' . $grade_id . '\'');
			$db->query($sql);
		}
		else {
			$sql = 'INSERT INTO ' . $ecs->table('merchants_privilege') . (' (`action_list`,`grade_id`) VALUES (\'' . $act_list . '\',\'' . $grade_id . '\')');
			$db->query($sql);
		}

		if ($initialize_allot == 1) {
			$grade_ru = arr_foreach($db->getAll(' SELECT ru_id FROM ' . $ecs->table('merchants_grade') . (' WHERE grade_id = \'' . $grade_id . '\'')));
			$sql = 'UPDATE ' . $ecs->table('admin_user') . (' SET action_list = \'' . $act_list . '\' WHERE 1 AND suppliers_id = 0 AND ru_id > 0 AND ru_id ') . db_create_in($grade_ru);
			$db->query($sql);
		}
	}

	$link[] = array('text' => $_LANG['go_back'], 'href' => 'merchants_privilege.php?act=allot&grade_id=' . $grade_id);
	sys_msg($_LANG['action_succeed'], 0, $link);
}

?>
