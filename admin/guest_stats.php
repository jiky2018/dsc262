<?php
//QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/lib_order.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/statistic.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

if ($_REQUEST['act'] == 'list') {
	admin_priv('client_flow_stats');
	$users = &init_users();
	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('users');
	$res = $db->getCol($sql);
	$user_num = $res[0];
	$total_fee = ' SUM(' . order_amount_field('o.') . ') AS turnover ';
	$sql = 'SELECT COUNT(DISTINCT user_id) FROM ' . $ecs->table('order_info') . ' WHERE user_id > 0 ' . order_query_sql('finished');
	$have_order_usernum = $db->getOne($sql);
	$user_all_order = array();
	$sql = 'SELECT COUNT(*) AS order_num, ' . $total_fee . 'FROM ' . $ecs->table('order_info') . ' as o ' . ' WHERE o.user_id > 0 ' . order_query_sql('finished', 'o.') . ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
	$user_all_order = $db->getRow($sql);
	$user_all_order['turnover'] = floatval($user_all_order['turnover']);
	$guest_all_order = array();
	$sql = 'SELECT COUNT(*) AS order_num, ' . $total_fee . 'FROM ' . $ecs->table('order_info') . 'as o' . ' WHERE o.user_id = 0 ' . order_query_sql('finished', 'o.') . ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
	$guest_all_order = $db->getRow($sql);
	$guest_order_amount = 0 < $guest_all_order['order_num'] ? floatval($guest_all_order['turnover'] / $guest_all_order['order_num']) : '0.00';
	$_GET['flag'] = isset($_GET['flag']) ? 'download' : '';

	if ($_GET['flag'] == 'download') {
		$filename = ecs_iconv(EC_CHARSET, 'GB2312', $_LANG['guest_statistics']);
		header('Content-type: application/vnd.ms-excel; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $filename . '.xls');
		$data = $_LANG['percent_buy_member'] . "\t\n";
		$data .= $_LANG['member_count'] . '	' . $_LANG['order_member_count'] . '	' . $_LANG['member_order_count'] . '	' . $_LANG['percent_buy_member'] . "\n";
		$data .= $user_num . '	' . $have_order_usernum . '	' . $user_all_order['order_num'] . '	' . sprintf('%0.2f', (0 < $user_num ? $have_order_usernum / $user_num : 0) * 100) . "\n\n";
		$data .= $_LANG['order_turnover_peruser'] . "\t\n";
		$data .= $_LANG['member_sum'] . '	' . $_LANG['average_member_order'] . '	' . $_LANG['m ember_order_sum'] . "\n";
		$ave_user_ordernum = 0 < $user_num ? sprintf('%0.2f', $user_all_order['order_num'] / $user_num) : 0;
		$ave_user_turnover = 0 < $user_num ? strip_tags(price_format($user_all_order['turnover'] / $user_num)) : 0;
		$data .= strip_tags(price_format($user_all_order['turnover'])) . '	' . $ave_user_ordernum . '	' . $ave_user_turnover . "\n\n";
		$data .= $_LANG['order_turnover_percus'] . "\t\n";
		$data .= $_LANG['guest_member_orderamount'] . '	' . $_LANG['guest_member_ordercount'] . '	' . $_LANG['guest_order_sum'] . "\n";
		$order_num = 0 < $guest_all_order['order_num'] ? strip_tags(price_format($guest_all_order['turnover'] / $guest_all_order['order_num'])) : 0;
		$data .= strip_tags(price_format($guest_all_order['turnover'])) . '	' . $guest_all_order['order_num'] . '	' . $order_num;
		echo ecs_iconv(EC_CHARSET, 'GB2312', $data) . '	';
		exit();
	}

	$user_num = !empty($user_num) ? $user_num : 1;
	$user_all_order['order_num'] = !empty($user_all_order['order_num']) ? $user_all_order['order_num'] : 0;
	$smarty->assign('user_num', $user_num);
	$smarty->assign('have_order_usernum', $have_order_usernum);
	$smarty->assign('user_order_turnover', $user_all_order['order_num']);
	$smarty->assign('user_all_turnover', price_format($user_all_order['turnover']));
	$smarty->assign('guest_all_turnover', price_format($guest_all_order['turnover']));
	$smarty->assign('guest_order_num', $guest_all_order['order_num']);
	$smarty->assign('one_user_order_unm', sprintf('%0.2f', $user_all_order['order_num'] / $user_num));
	$smarty->assign('ave_user_ordernum', 0 < $user_num ? sprintf('%0.2f', $user_all_order['order_num'] / $user_num) : 0);

	if ($user_all_order['order_num']) {
		$smarty->assign('ave_user_turnover', 0 < $user_num ? price_format($user_all_order['turnover'] / sprintf('%0.2f', $user_all_order['order_num'] / $user_num)) : 0);
	}
	else {
		$smarty->assign('ave_user_turnover', '无订单');
	}

	$smarty->assign('user_ratio', sprintf('%0.2f', (0 < $user_num ? $have_order_usernum / $user_num : 0) * 100));
	$smarty->assign('guest_order_amount', 0 < $guest_all_order['order_num'] ? price_format($guest_all_order['turnover'] / $guest_all_order['order_num']) : 0);
	$smarty->assign('all_order', $user_all_order);
	$smarty->assign('ur_here', $_LANG['report_guest']);
	$smarty->assign('lang', $_LANG);
	$smarty->assign('action_link', array('text' => $_LANG['down_guest_stats'], 'href' => 'guest_stats.php?flag=download'));
	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->display('guest_stats.dwt');
}

?>
