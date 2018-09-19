<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_auto_goods()
{
	$where = '';

	if (!empty($_POST['goods_name'])) {
		$goods_name = trim($_POST['goods_name']);
		$where = ' WHERE g.title LIKE \'%' . $goods_name . '%\'';
		$filter['goods_name'] = $goods_name;
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('article') . ' g' . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$goodsdb = array();
	$filter = page_and_size($filter);
	$sql = 'SELECT g.*,a.starttime,a.endtime FROM ' . $GLOBALS['ecs']->table('article') . ' g LEFT JOIN ' . $GLOBALS['ecs']->table('auto_manage') . ' a ON g.article_id = a.item_id AND a.type=\'article\'' . $where . ' ORDER BY g. add_time DESC' . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
	$query = $GLOBALS['db']->query($sql);

	while ($rt = $GLOBALS['db']->fetch_array($query)) {
		if (!empty($rt['starttime'])) {
			$rt['starttime'] = local_date('Y-m-d', $rt['starttime']);
		}

		if (!empty($rt['endtime'])) {
			$rt['endtime'] = local_date('Y-m-d', $rt['endtime']);
		}

		$rt['goods_id'] = $rt['article_id'];
		$rt['goods_name'] = $rt['title'];

		if (0 < $rt['cat_id']) {
			$sql = 'SELECT cat_name FROM' . $GLOBALS['ecs']->table('article_cat') . ' WHERE cat_id = \'' . $rt['cat_id'] . '\'';
			$rt['cat_name'] = $GLOBALS['db']->getOne($sql);
		}

		$goodsdb[] = $rt;
	}

	$arr = array('goodsdb' => $goodsdb, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
admin_priv('article_auto');
$smarty->assign('thisfile', 'article_auto.php');

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('auto_type', 1);
	$goodsdb = get_auto_goods();
	$crons_enable = $db->getOne('SELECT enable FROM ' . $GLOBALS['ecs']->table('crons') . ' WHERE cron_code=\'ipdel\'');
	$smarty->assign('crons_enable', $crons_enable);
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', $_LANG['article_auto']);
	$smarty->assign('goodsdb', $goodsdb['goodsdb']);
	$smarty->assign('filter', $goodsdb['filter']);
	$smarty->assign('record_count', $goodsdb['record_count']);
	$smarty->assign('page_count', $goodsdb['page_count']);
	$smarty->assign('article_type', 1);
	assign_query_info();
	$smarty->display('goods_auto.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$goodsdb = get_auto_goods();
	$smarty->assign('goodsdb', $goodsdb['goodsdb']);
	$smarty->assign('filter', $goodsdb['filter']);
	$smarty->assign('record_count', $goodsdb['record_count']);
	$smarty->assign('page_count', $goodsdb['page_count']);
	$sort_flag = sort_flag($goodsdb['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('goods_auto.dwt'), '', array('filter' => $goodsdb['filter'], 'page_count' => $goodsdb['page_count']));
}
else if ($_REQUEST['act'] == 'del') {
	$goods_id = (int) $_REQUEST['goods_id'];
	$sql = 'DELETE FROM ' . $ecs->table('auto_manage') . ' WHERE item_id = \'' . $goods_id . '\' AND type = \'article\'';
	$db->query($sql);
	$links[] = array('text' => $_LANG['article_auto'], 'href' => 'article_auto.php?act=list');
	sys_msg($_LANG['edit_ok'], 0, $links);
}
else if ($_REQUEST['act'] == 'batch_start') {
	admin_priv('goods_auto');
	if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
		sys_msg($_LANG['no_select_goods'], 1);
	}

	if ($_POST['date'] == '0000-00-00') {
		$_POST['date'] = 0;
	}
	else {
		$_POST['date'] = local_strtotime(trim($_POST['date']));
	}

	foreach ($_POST['checkboxes'] as $id) {
		$db->autoReplace($ecs->table('auto_manage'), array('item_id' => $id, 'type' => 'article', 'starttime' => $_POST['date']), array('starttime' => (string) $_POST['date']));
	}

	$lnk[] = array('text' => $_LANG['back_list'], 'href' => 'article_auto.php?act=list');
	sys_msg($_LANG['batch_start_succeed'], 0, $lnk);
}
else if ($_REQUEST['act'] == 'batch_end') {
	admin_priv('goods_auto');
	if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
		sys_msg($_LANG['no_select_goods'], 1);
	}

	if ($_POST['date'] == '0000-00-00') {
		$_POST['date'] = 0;
	}
	else {
		$_POST['date'] = local_strtotime(trim($_POST['date']));
	}

	foreach ($_POST['checkboxes'] as $id) {
		$db->autoReplace($ecs->table('auto_manage'), array('item_id' => $id, 'type' => 'article', 'endtime' => $_POST['date']), array('endtime' => (string) $_POST['date']));
	}

	$lnk[] = array('text' => $_LANG['back_list'], 'href' => 'article_auto.php?act=list');
	sys_msg($_LANG['batch_end_succeed'], 0, $lnk);
}

?>
