<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function comment_seller_list()
{
	$filter['keywords'] = empty($_REQUEST['keywords']) ? 0 : trim($_REQUEST['keywords']);
	if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
		$filter['keywords'] = json_str_iconv($filter['keywords']);
	}

	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$where = '1';
	$where .= (!empty($filter['keywords']) ? ' AND (u.user_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\') ' : '');
	$filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
	$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
	$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
	$store_where = '';
	$store_search_where = '';

	if (-1 < $filter['store_search']) {
		if ($ru_id == 0) {
			if (0 < $filter['store_search']) {
				if ($_REQUEST['store_type']) {
					$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
				}

				if ($filter['store_search'] == 1) {
					$where .= ' AND s.ru_id = \'' . $filter['merchant_id'] . '\' ';
				}
				else if ($filter['store_search'] == 2) {
					$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
				}
				else if ($filter['store_search'] == 3) {
					$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
				}

				if (1 < $filter['store_search']) {
					$where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . ' WHERE msi.user_id = s.ru_id ' . $store_where . ') > 0 ';
				}
			}
			else {
				$where .= ' AND s.ru_id = 0';
			}
		}
	}

	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment_seller') . ' AS s ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id = s.user_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS sp ON sp.ru_id = s.ru_id ' . 'WHERE ' . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$arr = array();
	$sql = 'SELECT s.*,o.order_sn,u.user_name FROM ' . $GLOBALS['ecs']->table('comment_seller') . ' AS s ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS o ON o.order_id = s.order_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS sp ON sp.ru_id = s.ru_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id = s.user_id ' . 'WHERE ' . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ' . 'LIMIT ' . $filter['start'] . ', ' . $filter['page_size'];
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
		$row['ru_name'] = get_shop_name($row['ru_id'], 1);
		$arr[] = $row;
	}

	$filter['keywords'] = stripslashes($filter['keywords']);
	$arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('comment_seller'), $db, 'sid', 'order_id');

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

if ($_REQUEST['act'] == 'list') {
	admin_priv('comment_seller');
	$smarty->assign('menu_select', array('action' => '17_merchants', 'current' => '13_comment_seller_rank'));
	$smarty->assign('action_link', array('href' => 'comment_seller.php?act=baseline', 'text' => $_LANG['seller_industry_baseline']));
	$smarty->assign('ur_here', $_LANG['comment_seller_rank']);
	$smarty->assign('full_page', 1);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$list = comment_seller_list();
	$smarty->assign('rank_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('comment_seller_rank.dwt');
}

if ($_REQUEST['act'] == 'query') {
	admin_priv('comment_seller');
	$list = comment_seller_list();
	$smarty->assign('rank_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('comment_seller_rank.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else if ($_REQUEST['act'] == 'baseline') {
	admin_priv('comment_seller');
	$smarty->assign('menu_select', array('action' => '17_merchants', 'current' => '13_comment_seller_rank'));
	$smarty->assign('action_link', array('href' => 'comment_seller.php?act=list'));
	$smarty->assign('ur_here', '商品 - ' . $_LANG['seller_industry_baseline']);
	$sql = 'SELECT goods, service, shipping FROM ' . $GLOBALS['ecs']->table('comment_baseline') . ' WHERE 1';
	$baseline = $GLOBALS['db']->getRow($sql);
	$smarty->assign('baseline', $baseline);
	$smarty->assign('form_action', 'insert_update');
	assign_query_info();
	$smarty->display('comment_baseline.dwt');
}
else if ($_REQUEST['act'] == 'insert_update') {
	admin_priv('comment_seller');
	$other['goods'] = !empty($_REQUEST['goods_baseline']) ? trim($_REQUEST['goods_baseline']) : '';
	$other['service'] = !empty($_REQUEST['service_baseline']) ? trim($_REQUEST['service_baseline']) : '';
	$other['shipping'] = !empty($_REQUEST['shipping_baseline']) ? trim($_REQUEST['shipping_baseline']) : '';
	$sql = 'SELECT id FROM ' . $GLOBALS['ecs']->table('comment_baseline') . ' WHERE 1';
	$res = $GLOBALS['db']->getOne($sql, true);

	if ($res) {
		$db->autoExecute($ecs->table('comment_baseline'), $other, 'UPDATE', ' 1 ');
	}
	else {
		$db->autoExecute($ecs->table('comment_baseline'), $other, 'INSERT');
	}

	$link[] = array('text' => $_LANG['go_back'], 'href' => 'comment_seller.php?act=baseline');
	sys_msg($_LANG['success'], 0, $link);
	assign_query_info();
	$smarty->display('comment_baseline.dwt');
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('comment_seller');
	$id = intval($_GET['id']);

	if ($exc->drop($id)) {
		admin_log($id, 'remove', 'comment_seller');
		clear_cache_files();
	}

	$url = 'comment_seller.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

?>
