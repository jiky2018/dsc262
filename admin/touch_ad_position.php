<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function ad_position_list($ru_id, $tc_type = '')
{
	$where = ' WHERE 1';

	if (0 < $ru_id) {
		$where .= ' and (p.user_id = \'' . $ru_id . '\' or p.is_public = 1) ';
	}
	
	if($tc_type == 1){
		$where .= ' and tc_type = 1';
	}else{
		$where .= ' and tc_type != 1';
	}

	$filter = array();
	$filter['keyword'] = !empty($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
	if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
		$filter['keyword'] = json_str_iconv($filter['keyword']);
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
				$where .= ' AND p.user_id = \'' . $filter['merchant_id'] . '\' ';
			}
			else if ($filter['store_search'] == 2) {
				$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
			}
			else if ($filter['store_search'] == 3) {
				$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
			}

			if (1 < $filter['store_search']) {
				$where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . (' WHERE msi.user_id = p.user_id ' . $store_where . ') > 0 ');
			}
		}
	}

	if (!empty($filter['keyword'])) {
		$where .= ' AND (p.position_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\'' . ')';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('touch_ad_position') . ' AS p ' . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$arr = array();
	$sql = 'SELECT p.* FROM ' . $GLOBALS['ecs']->table('touch_ad_position') . ' AS p ' . $where . ' ORDER BY p.position_id DESC';
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$idx = 0;

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$position_desc = !empty($rows['position_desc']) ? sub_str($rows['position_desc'], 50, true) : '';
		$rows['position_desc'] = nl2br(htmlspecialchars($position_desc));
		$rows['user_name'] = get_shop_name($rows['user_id'], 1);
		$arr[$idx] = $rows;
		$idx++;
	}

	return array('position' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_team_list()
{
	$sql = 'select id,name from ' . $GLOBALS['ecs']->table('team_category') . ' where parent_id=0 and status = 1 ';
	$res = $GLOBALS['db']->getAll($sql);
	return $res;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/ads.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$smarty->assign('lang', $_LANG);
$exc = new exchange($ecs->table('touch_ad_position'), $db, 'position_id', 'position_name');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

if ($_REQUEST['act'] == 'list') {
	$tc_type = (isset($_REQUEST['tc_type']) ? intval($_REQUEST['tc_type']) : '');
	$smarty->assign('ur_here', $_LANG['04_touch_ad_position']);
	$smarty->assign('action_link', array('text' => $_LANG['position_add'], 'href' => 'touch_ad_position.php?act=add&tc_type='.$tc_type));
	$smarty->assign('full_page', 1);
	$position_list = ad_position_list($adminru['ru_id'],$tc_type);
	if($tc_type == 1){
		$smarty->assign('ad_type', 5);
	}
	$smarty->assign('tc_type', $tc_type);
	$smarty->assign('position_list', $position_list['position']);
	$smarty->assign('filter', $position_list['filter']);
	$smarty->assign('record_count', $position_list['record_count']);
	$smarty->assign('page_count', $position_list['page_count']);
	$smarty->assign('type', '1');
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	assign_query_info();
	$smarty->display('touch_ad_position_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$position_list = ad_position_list($adminru['ru_id']);
	$smarty->assign('position_list', $position_list['position']);
	$smarty->assign('filter', $position_list['filter']);
	$smarty->assign('record_count', $position_list['record_count']);
	$smarty->assign('page_count', $position_list['page_count']);
	$smarty->assign('type', '1');
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	make_json_result($smarty->fetch('ad_position_list.dwt'), '', array('filter' => $position_list['filter'], 'page_count' => $position_list['page_count']));
}
else if ($_REQUEST['act'] == 'add') {
	admin_priv('touch_ad_position');
	$team_list = get_team_list();
	$smarty->assign('tc_type', $_REQUEST['tc_type']);
	$smarty->assign('team_list', $team_list);
	$smarty->assign('ur_here', $_LANG['position_add']);
	$smarty->assign('form_act', 'insert');
	$smarty->assign('action_link', array('href' => 'touch_ad_position.php?act=list&tc_type='.$_REQUEST['tc_type'], 'text' => $_LANG['ad_position']));
	$smarty->assign('posit_arr', array('position_style' => '{foreach $ads as $ad}' . "\n" . '<div class="swiper-slide">{$ad}</div>' . "\n" . '{/foreach}'));
	$smarty->assign('type', '1');
	assign_query_info();
	$smarty->display('touch_ad_position_info.dwt');
}
else if ($_REQUEST['act'] == 'insert') {
	admin_priv('touch_ad_position');
	$position_name = !empty($_POST['position_name']) ? trim($_POST['position_name']) : '';
	$position_desc = !empty($_POST['position_desc']) ? nl2br(htmlspecialchars($_POST['position_desc'])) : '';
	$ad_width = !empty($_POST['ad_width']) ? intval($_POST['ad_width']) : 0;
	$ad_height = !empty($_POST['ad_height']) ? intval($_POST['ad_height']) : 0;
	$is_public = isset($_POST['is_public']) ? intval($_POST['is_public']) : 0;
	$tc_id = !empty($_POST['tc_id']) ? intval($_POST['tc_id']) : 0;
	$tc_type = !empty($_POST['tc_type']) ? trim($_POST['tc_type']) : '';
	$ad_type = !empty($_POST['ad_type']) ? trim($_POST['ad_type']) : '';
	$template = $GLOBALS['_CFG']['template'];
	$where = ' theme = \'' . $template . '\'';

	if ($exc->num('position_name', $position_name, 0, $where) == 0) {
		$sql = 'INSERT INTO ' . $ecs->table('touch_ad_position') . ' (position_name, ad_width, ad_height, position_desc, position_style, user_id, is_public, theme, tc_id, tc_type, ad_type) ' . ('VALUES (\'' . $position_name . '\', \'' . $ad_width . '\', \'' . $ad_height . '\', \'' . $position_desc . '\', \'' . $_POST['position_style'] . '\', \'' . $adminru['ru_id'] . '\', \'' . $is_public . '\', \'' . $template . '\', \'' . $tc_id . '\', \'' . $tc_type . '\', \'' . $ad_type . '\')');
		$db->query($sql);
		admin_log($position_name, 'add', 'ads_position');
		$link[0]['text'] = $_LANG['ads_add'];
		$link[0]['href'] = 'touch_ads.php?act=add&tc_type='.$tc_type;
		$link[1]['text'] = $_LANG['continue_add_position'];
		$link[1]['href'] = 'touch_ad_position.php?act=add&tc_type='.$tc_type;
		$link[2]['text'] = $_LANG['back_position_list'];
		$link[2]['href'] = 'touch_ad_position.php?act=list&tc_type='.$_REQUEST['tc_type'];
		sys_msg($_LANG['add'] . '&nbsp;' . stripslashes($position_name) . '&nbsp;' . $_LANG['attradd_succed'], 0, $link);
	}
	else {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['posit_name_exist'], 0, $link);
	}
}
else if ($_REQUEST['act'] == 'edit') {
	admin_priv('touch_ad_position');
	$team_list = get_team_list();
	$smarty->assign('team_list', $team_list);
	$smarty->assign('tc_type', $_REQUEST['tc_type']);
	$id = !empty($_GET['id']) ? intval($_GET['id']) : 0;
	$sql = 'SELECT * FROM ' . $ecs->table('touch_ad_position') . (' WHERE position_id=\'' . $id . '\'');
	$posit_arr = $db->getRow($sql);
	$smarty->assign('ur_here', $_LANG['position_edit']);
	$smarty->assign('action_link', array('href' => 'touch_ad_position.php?act=list&tc_type='.$_REQUEST['tc_type'], 'text' => $_LANG['ad_position']));
	$smarty->assign('posit_arr', $posit_arr);
	$smarty->assign('form_act', 'update');
	$smarty->assign('type', '1');
	assign_query_info();
	$smarty->display('touch_ad_position_info.dwt');
}
else if ($_REQUEST['act'] == 'update') {
	admin_priv('touch_ad_position');
	$position_name = !empty($_POST['position_name']) ? trim($_POST['position_name']) : '';
	$position_desc = !empty($_POST['position_desc']) ? nl2br(htmlspecialchars($_POST['position_desc'])) : '';
	$ad_width = !empty($_POST['ad_width']) ? intval($_POST['ad_width']) : 0;
	$ad_height = !empty($_POST['ad_height']) ? intval($_POST['ad_height']) : 0;
	$position_id = !empty($_POST['id']) ? intval($_POST['id']) : 0;
	$is_public = isset($_POST['is_public']) ? intval($_POST['is_public']) : 0;
	$tc_id = !empty($_POST['tc_id']) ? intval($_POST['tc_id']) : 0;
	$tc_type = !empty($_POST['tc_type']) ? trim($_POST['tc_type']) : '';
	$ad_type = !empty($_POST['ad_type']) ? trim($_POST['ad_type']) : '';
	$template = $GLOBALS['_CFG']['template'];
	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('touch_ad_position') . (' WHERE position_name = \'' . $position_name . '\' AND theme = \'' . $template . '\' AND position_id <> \'' . $position_id . '\'');

	if ($db->getOne($sql) == 0) {
		$sql = 'UPDATE ' . $ecs->table('touch_ad_position') . ' SET ' . ('position_name    = \'' . $position_name . '\', ') . ('ad_width         = \'' . $ad_width . '\', ') . ('ad_height        = \'' . $ad_height . '\', ') . ('position_desc    = \'' . $position_desc . '\', ') . ('is_public        = \'' . $is_public . '\', ') . ('position_style   = \'' . $_POST['position_style'] . '\', ') . ('tc_id            = \'' . $tc_id . '\', ') . ('tc_type          = \'' . $tc_type . '\', ') . ('ad_type          = \'' . $ad_type . '\' ') . ('WHERE position_id = \'' . $position_id . '\'');

		if ($db->query($sql)) {
			admin_log($position_name, 'edit', 'ads_position');
			clear_cache_files();
			$link[] = array('text' => $_LANG['back_position_list'], 'href' => 'touch_ad_position.php?act=list&tc_type='.$_REQUEST['tc_type']);
			sys_msg($_LANG['edit'] . ' ' . stripslashes($position_name) . ' ' . $_LANG['attradd_succed'], 0, $link);
		}
	}
	else {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['posit_name_exist'], 0, $link);
	}
}
else if ($_REQUEST['act'] == 'edit_position_name') {
	check_authz_json('ad_manage');
	$id = intval($_POST['id']);
	$position_name = json_str_iconv(trim($_POST['val']));
	$template = $GLOBALS['_CFG']['template'];

	if ($exc->num('position_name', $position_name, $id, 'theme=\'' . $template . '\'') != 0) {
		make_json_error(sprintf($_LANG['posit_name_exist'], $position_name));
	}
	else if ($exc->edit('position_name = \'' . $position_name . '\', theme=\'' . $template . '\'', $id)) {
		admin_log($position_name, 'edit', 'ads_position');
		make_json_result(stripslashes($position_name));
	}
	else {
		make_json_result(sprintf($_LANG['brandedit_fail'], $position_name));
	}
}
else if ($_REQUEST['act'] == 'edit_ad_width') {
	check_authz_json('ad_manage');
	$id = intval($_POST['id']);
	$ad_width = json_str_iconv(trim($_POST['val']));

	if (!preg_match('/^[\\.0-9]+$/', $ad_width)) {
		make_json_error($_LANG['width_number']);
	}

	if (1024000 < $ad_width || $ad_width < 1) {
		make_json_error($_LANG['width_value']);
	}

	if ($exc->edit('ad_width = \'' . $ad_width . '\'', $id)) {
		clear_cache_files();
		admin_log($ad_width, 'edit', 'ads_position');
		make_json_result(stripslashes($ad_width));
	}
	else {
		make_json_error($db->error());
	}
}
else if ($_REQUEST['act'] == 'edit_ad_height') {
	check_authz_json('ad_manage');
	$id = intval($_POST['id']);
	$ad_height = json_str_iconv(trim($_POST['val']));

	if (!preg_match('/^[\\.0-9]+$/', $ad_height)) {
		make_json_error($_LANG['height_number']);
	}

	if (1024000 < $ad_height || $ad_height < 1) {
		make_json_error($_LANG['height_value']);
	}

	if ($exc->edit('ad_height = \'' . $ad_height . '\'', $id)) {
		clear_cache_files();
		admin_log($ad_height, 'edit', 'ads_position');
		make_json_result(stripslashes($ad_height));
	}
	else {
		make_json_error($db->error());
	}
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('ad_manage');
	$id = intval($_GET['id']);
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('touch_ad') . (' WHERE position_id = \'' . $id . '\'');

	if (0 < $db->getOne($sql)) {
		make_json_error($_LANG['not_del_adposit']);
	}
	else {
		$exc->drop($id);
		admin_log('', 'remove', 'ads_position');
	}

	$url = 'touch_ad_position.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

?>
