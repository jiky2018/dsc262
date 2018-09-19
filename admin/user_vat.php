<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function vat_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['audit_status'] = empty($_REQUEST['audit_status']) ? '' : intval($_REQUEST['audit_status']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$where = ' WHERE 1 ';
		$where .= (!empty($filter['keyword']) ? ' AND (company_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\')' : '');
		$where .= (!empty($filter['audit_status']) ? ' AND audit_status = \'' . $filter['audit_status'] . '\' ' : '');
		$sql = ' SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('users_vat_invoices_info') . ' AS t ' . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('users_vat_invoices_info') . ' ' . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$arr = array();
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$row['add_time'] = local_date('Y-m-d H:i:s', $row['add_time']);

		switch ($row['audit_status']) {
		case 0:
			$row['audit_status'] = '未审核';
			break;

		case 1:
			$row['audit_status'] = '审核通过';
			break;

		case 2:
			$row['audit_status'] = '审核未通过';
			break;
		}

		$arr[] = $row;
	}

	$arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function vat_info()
{
	$id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('users_vat_invoices_info') . ' WHERE id = \'' . $id . '\' ';
	$row = $GLOBALS['db']->getRow($sql);
	return $row;
}

function get_vat_consignee_list($vat_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('users_vat_invoices_info') . ' WHERE id = \'' . $vat_id . '\'';
	$res = $GLOBALS['db']->getRow($sql);
	$arr = array();
	$arr['region'] = user_vat_consignee_region($res['id']);
	return $arr;
}

function user_vat_consignee_region($id)
{
	$sql = 'SELECT concat(IFNULL(p.region_name, \'\'), ' . 'IFNULL(t.region_name, \'\'), IFNULL(d.region_name, \'\') )' . 'FROM ' . $GLOBALS['ecs']->table('users_vat_invoices_info') . ' AS u ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS p ON u.province = p.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS t ON u.city = t.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS d ON u.district = d.region_id ' . 'WHERE u.id = \'' . $id . '\'';
	$address = $GLOBALS['db']->getOne($sql);
	return $address;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$exc = new exchange($ecs->table('users_vat_invoices_info'), $db, 'id', 'user_id');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

if ($_REQUEST['act'] == 'list') {
	admin_priv('user_vat_manage');
	$smarty->assign('ur_here', $_LANG['vat_audit_list']);
	$smarty->assign('full_page', 1);
	$list = vat_list();
	$smarty->assign('vat_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('vat_list.dwt');
}

if ($_REQUEST['act'] == 'query') {
	admin_priv('user_vat_manage');
	$list = vat_list();
	$smarty->assign('vat_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('vat_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

if ($_REQUEST['act'] == 'view') {
	admin_priv('user_vat_manage');
	$smarty->assign('ur_here', $_LANG['vat_view']);
	$info = vat_info();
	$new_vat_consignee_list = get_vat_consignee_list($_REQUEST['id']);
	$smarty->assign('new_vat_consignee_list', $new_vat_consignee_list);
	$smarty->assign('vat_info', $info);
	$smarty->assign('form_act', 'update');
	$smarty->assign('action_link', array('href' => 'user_vat.php?act=list', 'text' => $_LANG['vat_audit_list']));
	assign_query_info();
	$smarty->display('vat_info.dwt');
}

if ($_REQUEST['act'] == 'update') {
	admin_priv('user_vat_manage');
	$id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$audit_status = (isset($_REQUEST['audit_status']) ? intval($_REQUEST['audit_status']) : 0);
	$sql = ' UPDATE ' . $ecs->table('users_vat_invoices_info') . ' SET audit_status = \'' . $audit_status . '\' WHERE id = \'' . $id . '\' ';

	if ($db->query($sql)) {
		$link[0]['text'] = $_LANG['back_list'];
		$link[0]['href'] = 'user_vat.php?act=list';
		sys_msg('审核资质成功！', 0, $link);
	}
}

if ($_REQUEST['act'] == 'remove') {
	admin_priv('user_vat_manage');
	$id = intval($_GET['id']);
	$exc->drop($id);
	$url = 'user_vat.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

?>
