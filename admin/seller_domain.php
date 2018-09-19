<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function seller_domain_list()
{
	$result = get_filter();
	$adminru = get_admin_ru_id();

	if ($result === false) {
		$where = '';

		if (0 < $adminru['ru_id']) {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('seller_domain') . ' WHERE ru_id = ' . $adminru['ru_id'];
			$where = ' WHERE a.ru_id = ' . $adminru['ru_id'];
		}
		else {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('seller_domain');
		}

		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT a.* FROM' . $GLOBALS['ecs']->table('seller_domain') . ' as a ' . ' ' . $where . '  ORDER BY a.id ASC LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $key => $row) {
		$res[$key]['shop_name'] = get_shop_name($row['ru_id'], 1);
		$res[$key]['validity_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['validity_time']);
	}

	$arr = array('pzd_list' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('seller_domain'), $db, 'id', 'domain_name');

if ($_REQUEST['act'] == 'list') {
	assign_query_info();
	$smarty->assign('ur_here', $_LANG['seller_domain']);
	$adminru = get_admin_ru_id();
	$smarty->assign('ru_id', $adminru['ru_id']);
	$domain_list = seller_domain_list($adminru);
	$smarty->assign('pzd_list', $domain_list['pzd_list']);
	$smarty->assign('filter', $domain_list['filter']);
	$smarty->assign('record_count', $domain_list['record_count']);
	$smarty->assign('page_count', $domain_list['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->display('seller_domain.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	admin_priv('seller_dimain');
	assign_query_info();
	$smarty->assign('ur_here', $_LANG['seller_domain']);
	$domain_list = seller_domain_list();
	$adminru = get_admin_ru_id();
	$smarty->assign('ru_id', $adminru['ru_id']);
	$smarty->assign('pzd_list', $domain_list['pzd_list']);
	$smarty->assign('filter', $domain_list['filter']);
	$smarty->assign('record_count', $domain_list['record_count']);
	$smarty->assign('page_count', $domain_list['page_count']);
	make_json_result($smarty->fetch('seller_domain.dwt'), '', array('filter' => $domain_list['filter'], 'page_count' => $domain_list['page_count']));
}
else if ($_REQUEST['act'] == 'edit') {
	admin_priv('seller_dimain');
	$smarty->assign('ur_here', $_LANG['seller_domain_edit']);
	$smarty->assign('action_link', array('text' => $_LANG['seller_domain'], 'href' => 'seller_domain.php?act=list'));
	$id = (!empty($_REQUEST['id']) ? $_REQUEST['id'] : '0');
	$domain = $db->getRow('SELECT is_enable,validity_time, domain_name FROM ' . $ecs->table('seller_domain') . ' WHERE id = ' . $id);

	if ($domain['validity_time'] == 0) {
		$domain['validity_time'] = '';
	}
	else {
		$domain['validity_time'] = local_date('Y-m-d H:i', $domain['validity_time']);
	}

	$smarty->assign('domian', $domain);

	if ($_POST['sub']) {
		$domain_name = (!empty($_POST['domain_name']) ? $_POST['domain_name'] : '');
		$is_enable = (!empty($_POST['is_enable']) ? $_POST['is_enable'] : '0');
		$validity_time = (!empty($_POST['validity_time']) ? strtotime($_POST['validity_time']) : '0');
		$sql = ' UPDATE' . $ecs->table('seller_domain') . ' SET domain_name = \'' . $domain_name . '\' , is_enable= \'' . $is_enable . '\' ,validity_time=\'' . $validity_time . '\'  WHERE id =' . $id;

		if ($db->query($sql) == true) {
			$links = array(
				array('href' => 'seller_domain.php?act=list', 'text' => $_LANG['seller_domain'])
				);
			sys_msg($_LANG['domain_edit'], 0, $links);
		}
	}

	$smarty->display('seller_domain_info.dwt');
}
else if ($_REQUEST['act'] == 'is_enable') {
	admin_priv('seller_dimain');
	$id = intval($_REQUEST['id']);
	$sql = "SELECT id, is_enable\r\n            FROM " . $ecs->table('seller_domain') . "\r\n            WHERE id = '" . $id . '\'';
	$seller = $db->getRow($sql, true);

	if ($seller['id']) {
		$seller['is_enable'] = $seller['is_enable'] != 1 ? 1 : 0;
		$db->autoExecute($ecs->table('seller_domain'), $seller, '', 'id = \'' . $id . '\'');
		clear_cache_files();
		make_json_result($seller['is_enable']);
	}

	exit();
}
else if ($_REQUEST['act'] == 'remove') {
	$id = intval($_REQUEST['id']);
	check_authz_json('remove_back');
	$sql = 'DELETE FROM ' . $ecs->table('seller_domain') . ' WHERE id = \'' . $id . '\'';
	$db->query($sql);
	$url = 'seller_domain.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

?>
