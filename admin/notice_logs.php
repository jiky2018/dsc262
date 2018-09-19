<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function get_notice_logs($ru_id)
{
	$filter = array();
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;
	$where = ' WHERE 1 ';

	if (0 < $ru_id) {
		$where .= ' AND g.user_id = \'' . $ru_id . '\'';
	}

	$where .= !empty($filter['seller_list']) ? ' AND g.user_id > 0 ' : ' AND g.user_id = 0 ';
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('notice_log') . ' as nl, ' . $GLOBALS['ecs']->table('goods') . ' as g ' . $where . ' AND nl.goods_id = g.goods_id ';
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$list = array();
	$sql = 'SELECT nl.*, g.user_id, g.goods_name FROM ' . $GLOBALS['ecs']->table('notice_log') . ' as nl, ' . $GLOBALS['ecs']->table('goods') . ' as g ' . $where . ' AND nl.goods_id = g.goods_id ' . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$rows['send_time'] = local_date($GLOBALS['_CFG']['time_format'], $rows['send_time']);
		$rows['shop_name'] = get_shop_name($rows['user_id'], 1);
		$list[] = $rows;
	}

	return array('list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => 'notice_logs'));

if ($_REQUEST['act'] == 'list') {
	admin_priv('notice_logs');
	$user_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$admin_ip = !empty($_REQUEST['ip']) ? $_REQUEST['ip'] : '';
	$log_date = !empty($_REQUEST['log_date']) ? $_REQUEST['log_date'] : '';
	$smarty->assign('ur_here', '降价通知日志');
	$smarty->assign('ip_list', $ip_list);
	$smarty->assign('full_page', 1);
	$log_list = get_notice_logs($adminru['ru_id']);
	$smarty->assign('log_list', $log_list['list']);
	$smarty->assign('filter', $log_list['filter']);
	$smarty->assign('record_count', $log_list['record_count']);
	$smarty->assign('page_count', $log_list['page_count']);
	$sort_flag = sort_flag($log_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	self_seller(BASENAME($_SERVER['PHP_SELF']));
	assign_query_info();
	$smarty->display('notice_logs.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$log_list = get_notice_logs($adminru['ru_id']);
	$smarty->assign('log_list', $log_list['list']);
	$smarty->assign('filter', $log_list['filter']);
	$smarty->assign('record_count', $log_list['record_count']);
	$smarty->assign('page_count', $log_list['page_count']);
	$sort_flag = sort_flag($log_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('notice_logs.dwt'), '', array('filter' => $log_list['filter'], 'page_count' => $log_list['page_count']));
}

if ($_REQUEST['act'] == 'batch_drop') {
	admin_priv('notice_logs');
	$drop_type_date = isset($_POST['drop_type_date']) ? $_POST['drop_type_date'] : '';

	if ($drop_type_date) {
		if ($_POST['log_date'] == '0') {
			ecs_header("Location: notice_logs.php?act=list\n");
			exit();
		}
		else if ('0' < $_POST['log_date']) {
			$where = ' WHERE 1 ';

			switch ($_POST['log_date']) {
			case '1':
				$a_week = gmtime() - 3600 * 24 * 7;
				$where .= ' AND send_time <= \'' . $a_week . '\'';
				break;

			case '2':
				$a_month = gmtime() - 3600 * 24 * 30;
				$where .= ' AND send_time <= \'' . $a_month . '\'';
				break;

			case '3':
				$three_month = gmtime() - 3600 * 24 * 90;
				$where .= ' AND send_time <= \'' . $three_month . '\'';
				break;

			case '4':
				$half_year = gmtime() - 3600 * 24 * 180;
				$where .= ' AND send_time <= \'' . $half_year . '\'';
				break;

			case '5':
				$a_year = gmtime() - 3600 * 24 * 365;
				$where .= ' AND send_time <= \'' . $a_year . '\'';
				break;
			}

			$sql = 'DELETE FROM ' . $ecs->table('notice_log') . $where;
			$res = $db->query($sql);

			if ($res) {
				admin_log('', 'remove', 'noticelog');
				$link[] = array('text' => $_LANG['back_list'], 'href' => 'notice_logs.php?act=list');
				sys_msg($_LANG['drop_sueeccud'], 1, $link);
			}
		}
	}
	else {
		$count = 0;

		foreach ($_POST['checkboxes'] as $key => $id) {
			$sql = 'DELETE FROM ' . $ecs->table('notice_log') . (' WHERE id = \'' . $id . '\'');
			$result = $db->query($sql);
			$count++;
		}

		if ($result) {
			admin_log('', 'remove', 'noticelog');
			$link[] = array('text' => $_LANG['back_list'], 'href' => 'notice_logs.php?act=list');
			sys_msg(sprintf($_LANG['batch_drop_success'], $count), 0, $link);
		}
	}
}

?>
