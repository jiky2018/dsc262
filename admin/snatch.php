<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function get_snatchlist($ru_id)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'ga.act_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);
		$filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;
		$filter['rs_id'] = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);
		$adminru = get_admin_ru_id();

		if (0 < $adminru['rs_id']) {
			$filter['rs_id'] = $adminru['rs_id'];
		}

		$where = !empty($filter['keywords']) ? ' AND ga.act_name like \'%' . mysql_like_quote($filter['keywords']) . '%\'' : '';

		if (0 < $ru_id) {
			$where .= ' AND ga.user_id = \'' . $ru_id . '\' ';
		}

		if ($filter['review_status']) {
			$where .= ' AND ga.review_status = \'' . $filter['review_status'] . '\' ';
		}

		if ($filter['seller_list']) {
			$where .= ' AND ga.user_id > 0 ';
		}
		else {
			$where .= ' AND ga.user_id = 0 ';
		}

		$where .= get_rs_null_where('ga.user_id', $filter['rs_id']);
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
						$where .= ' AND ga.user_id = \'' . $filter['merchant_id'] . '\' ';
					}
					else if ($filter['store_search'] == 2) {
						$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
					}
					else if ($filter['store_search'] == 3) {
						$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
					}

					if (1 < $filter['store_search']) {
						$where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . (' WHERE msi.user_id = ga.user_id ' . $store_where . ') > 0 ');
					}
				}
				else {
					$where .= ' AND ga.user_id = 0';
				}
			}
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . ' WHERE ga.act_type =' . GAT_SNATCH . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT ga.act_id, ga.act_name AS snatch_name, ga.goods_name, ga.start_time, ga.end_time, ga.is_finished, ga.ext_info, ga.product_id, ga.user_id, ga.is_hot, review_status, review_content ' . ' FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . ' WHERE ga.act_type = ' . GAT_SNATCH . $where . (' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ') . $filter['start'] . ', ' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		$row[$key]['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['start_time']);
		$row[$key]['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['end_time']);
		$info = unserialize($row[$key]['ext_info']);
		unset($row[$key]['ext_info']);

		if ($info) {
			foreach ($info as $info_key => $info_val) {
				$row[$key][$info_key] = $info_val;
			}
		}

		$row[$key]['ru_name'] = get_shop_name($val['user_id'], 1);
	}

	$arr = array('snatchs' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_snatch_info($id)
{
	global $ecs;
	global $db;
	global $_CFG;
	$sql = 'SELECT act_id, act_name AS snatch_name, user_id, goods_id, product_id, goods_name, start_time, end_time, act_desc, act_promise, act_ensure, ext_info, is_hot, review_status, review_content' . ' FROM ' . $GLOBALS['ecs']->table('goods_activity') . (' WHERE act_id=\'' . $id . '\' AND act_type = ') . GAT_SNATCH;
	$snatch = $db->GetRow($sql);
	$snatch['start_time'] = local_date('Y-m-d H:i:s', $snatch['start_time']);
	$snatch['end_time'] = local_date('Y-m-d H:i:s', $snatch['end_time']);
	$row = unserialize($snatch['ext_info']);
	unset($snatch['ext_info']);

	if ($row) {
		foreach ($row as $key => $val) {
			$snatch[$key] = $val;
		}
	}

	return $snatch;
}

function get_snatch_detail()
{
	$filter['snatch_id'] = empty($_REQUEST['snatch_id']) ? 0 : intval($_REQUEST['snatch_id']);
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'bid_time' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$where = empty($filter['snatch_id']) ? '' : ' WHERE snatch_id=\'' . $filter['snatch_id'] . '\'';
	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('snatch_log') . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT s.log_id, u.user_name, s.bid_price, s.bid_time ' . ' FROM ' . $GLOBALS['ecs']->table('snatch_log') . ' AS s ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON s.user_id = u.user_id  ' . $where . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ', ' . $filter['page_size'];
	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		$row[$key]['bid_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['bid_time']);
	}

	$arr = array('bid' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('goods_activity'), $db, 'act_id', 'act_name');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('ur_here', $_LANG['02_snatch_list']);
	$smarty->assign('action_link', array('text' => $_LANG['snatch_add'], 'href' => 'snatch.php?act=add'));
	$snatchs = get_snatchlist($adminru['ru_id']);
	$smarty->assign('snatch_list', $snatchs['snatchs']);
	$smarty->assign('filter', $snatchs['filter']);
	$smarty->assign('record_count', $snatchs['record_count']);
	$smarty->assign('page_count', $snatchs['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($snatchs['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$smarty->assign('full_page', 1);
	self_seller(BASENAME($_SERVER['PHP_SELF']));
	assign_query_info();
	$smarty->display('snatch_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$snatchs = get_snatchlist($adminru['ru_id']);
	$smarty->assign('snatch_list', $snatchs['snatchs']);
	$smarty->assign('filter', $snatchs['filter']);
	$smarty->assign('record_count', $snatchs['record_count']);
	$smarty->assign('page_count', $snatchs['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($snatchs['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('snatch_list.dwt'), '', array('filter' => $snatchs['filter'], 'page_count' => $snatchs['page_count']));
}
else if ($_REQUEST['act'] == 'add') {
	admin_priv('snatch_manage');
	$start_time = local_date('Y-m-d H:i:s');
	$end_time = local_date('Y-m-d H:i:s', strtotime('+1 week'));
	$snatch = array('start_price' => '1.00', 'end_price' => '800.00', 'max_price' => '0', 'cost_points' => '1', 'start_time' => $start_time, 'end_time' => $end_time, 'option' => '<option value="0">' . $_LANG['make_option'] . '</option>');
	create_html_editor2('act_desc', 'act_desc', $snatch['act_desc']);
	create_html_editor2('act_promise', 'act_promise', $snatch['act_promise']);
	create_html_editor2('act_ensure', 'act_ensure', $snatch['act_ensure']);
	$smarty->assign('snatch', $snatch);
	$smarty->assign('ur_here', $_LANG['snatch_add']);
	$smarty->assign('action_link', array('text' => $_LANG['02_snatch_list'], 'href' => 'snatch.php?act=list'));
	$smarty->assign('form_action', 'insert');
	$smarty->assign('ru_id', $adminru['ru_id']);
	set_default_filter();
	assign_query_info();
	$smarty->display('snatch_info.dwt');
}
else if ($_REQUEST['act'] == 'insert') {
	admin_priv('snatch_manage');
	$sql = 'SELECT goods_name FROM ' . $ecs->table('goods') . (' WHERE goods_id = \'' . $_POST['goods_id'] . '\'');
	$_POST['goods_name'] = $db->GetOne($sql);

	if (empty($_POST['goods_name'])) {
		sys_msg($_LANG['no_goods'], 1);
		exit();
	}

	$sql = 'SELECT COUNT(*) ' . ' FROM ' . $ecs->table('goods_activity') . ' WHERE act_type=\'' . GAT_SNATCH . '\' AND act_name=\'' . $_POST['snatch_name'] . '\'';

	if ($db->getOne($sql)) {
		sys_msg(sprintf($_LANG['snatch_name_exist'], $_POST['snatch_name']), 1);
	}

	$act_desc = isset($_POST['act_desc']) ? $_POST['act_desc'] : '';
	$act_promise = isset($_POST['act_promise']) ? $_POST['act_promise'] : '';
	$dact_ensure = isset($_POST['act_ensure']) ? $_POST['act_ensure'] : '';
	$_POST['start_time'] = local_strtotime($_POST['start_time']);
	$_POST['end_time'] = local_strtotime($_POST['end_time']);

	if (empty($_POST['start_price'])) {
		$_POST['start_price'] = 0;
	}

	if (empty($_POST['end_price'])) {
		$_POST['end_price'] = 0;
	}

	if (empty($_POST['max_price'])) {
		$_POST['max_price'] = 0;
	}

	if (empty($_POST['cost_points'])) {
		$_POST['cost_points'] = 0;
	}

	if (isset($_POST['product_id']) && empty($_POST['product_id'])) {
		$_POST['product_id'] = 0;
	}

	$info = array('start_price' => $_POST['start_price'], 'end_price' => $_POST['end_price'], 'max_price' => $_POST['max_price'], 'cost_points' => $_POST['cost_points']);
	$record = array('act_name' => $_POST['snatch_name'], 'act_desc' => $_POST['desc'], 'act_type' => GAT_SNATCH, 'goods_id' => intval($_POST['goods_id']), 'goods_name' => $_POST['goods_name'], 'start_time' => $_POST['start_time'], 'end_time' => $_POST['end_time'], 'product_id' => intval($_POST['product_id']), 'is_hot' => $_POST['is_hot'], 'user_id' => $adminru['ru_id'], 'act_desc' => $act_desc, 'act_promise' => $act_promise, 'act_ensure' => $dact_ensure, 'review_status' => 3, 'is_finished' => 0, 'ext_info' => serialize($info));
	$db->AutoExecute($ecs->table('goods_activity'), $record, 'INSERT');
	admin_log($_POST['snatch_name'], 'add', 'snatch');
	$link[] = array('text' => $_LANG['back_list'], 'href' => 'snatch.php?act=list');
	$link[] = array('text' => $_LANG['continue_add'], 'href' => 'snatch.php?act=add');
	sys_msg($_LANG['add_succeed'], 0, $link);
}
else if ($_REQUEST['act'] == 'toggle_hot') {
	check_authz_json('auction');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	$exc->edit('is_hot = \'' . $val . '\'', $id);
	clear_cache_files();
	make_json_result($val);
}
else if ($_REQUEST['act'] == 'edit_snatch_name') {
	check_authz_json('snatch_manage');
	$id = intval($_POST['id']);
	$val = json_str_iconv(trim($_POST['val']));
	$sql = 'SELECT COUNT(*) ' . ' FROM ' . $ecs->table('goods_activity') . ' WHERE act_type=\'' . GAT_SNATCH . ('\' AND act_name=\'' . $val . '\' AND act_id <> \'' . $id . '\'');

	if ($db->getOne($sql)) {
		make_json_error(sprintf($_LANG['snatch_name_exist'], $val));
	}

	$exc->edit('act_name=\'' . $val . '\'', $id);
	make_json_result(stripslashes($val));
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('attr_manage');
	$id = intval($_GET['id']);
	$exc->drop($id);
	$url = 'snatch.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'edit') {
	admin_priv('snatch_manage');
	$act_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$snatch = get_snatch_info($act_id);
	$snatch['option'] = '<option value="' . $snatch['goods_id'] . '">' . $snatch['goods_name'] . '</option>';
	$smarty->assign('snatch', $snatch);
	$smarty->assign('ur_here', $_LANG['snatch_edit']);
	$smarty->assign('action_link', array('text' => $_LANG['02_snatch_list'], 'href' => 'snatch.php?act=list&' . list_link_postfix()));
	$smarty->assign('form_action', 'update');
	$smarty->assign('ru_id', $adminru['ru_id']);
	create_html_editor2('act_desc', 'act_desc', $snatch['act_desc']);
	create_html_editor2('act_promise', 'act_promise', $snatch['act_promise']);
	create_html_editor2('act_ensure', 'act_ensure', $snatch['act_ensure']);
	$smarty->assign('good_products_select', get_good_products_select($snatch['goods_id']));
	set_default_filter();
	assign_query_info();
	$smarty->display('snatch_info.dwt');
}
else if ($_REQUEST['act'] == 'update') {
	admin_priv('snatch_manage');
	$act_id = !empty($_POST['id']) ? intval($_POST['id']) : 0;
	$act_desc = isset($_POST['act_desc']) ? $_POST['act_desc'] : '';
	$act_promise = isset($_POST['act_promise']) ? $_POST['act_promise'] : '';
	$dact_ensure = isset($_POST['act_ensure']) ? $_POST['act_ensure'] : '';
	$_POST['goods_id'] = !empty($_POST['goods_id']) ? intval($_POST['goods_id']) : 0;
	$_POST['start_time'] = local_strtotime($_POST['start_time']);
	$_POST['end_time'] = local_strtotime($_POST['end_time']);

	if (empty($_POST['snatch_name'])) {
		$_POST['snatch_name'] = '';
	}

	if (empty($_POST['goods_id'])) {
		$_POST['goods_id'] = 0;
	}
	else {
		$_POST['goods_name'] = $db->getOne('SELECT goods_name FROM ' . $ecs->table('goods') . 'WHERE goods_id= \'' . $_POST['goods_id'] . '\'');
	}

	if (empty($_POST['start_price'])) {
		$_POST['start_price'] = 0;
	}

	if (empty($_POST['end_price'])) {
		$_POST['end_price'] = 0;
	}

	if (empty($_POST['max_price'])) {
		$_POST['max_price'] = 0;
	}

	if (empty($_POST['cost_points'])) {
		$_POST['cost_points'] = 0;
	}

	if (isset($_POST['product_id']) && empty($_POST['product_id'])) {
		$_POST['product_id'] = 0;
	}

	$sql = 'SELECT COUNT(*) ' . ' FROM ' . $ecs->table('goods_activity') . ' WHERE act_type=\'' . GAT_SNATCH . '\' AND act_name=\'' . $_POST['snatch_name'] . ('\' AND act_id <> \'' . $act_id . '\'');

	if ($db->getOne($sql)) {
		sys_msg(sprintf($_LANG['snatch_name_exist'], $_POST['snatch_name']), 1);
	}

	$info = array('start_price' => $_POST['start_price'], 'end_price' => $_POST['end_price'], 'max_price' => $_POST['max_price'], 'cost_points' => $_POST['cost_points']);
	$record = array('act_name' => $_POST['snatch_name'], 'goods_id' => $_POST['goods_id'], 'goods_name' => $_POST['goods_name'], 'start_time' => $_POST['start_time'], 'end_time' => $_POST['end_time'], 'act_desc' => $_POST['desc'], 'product_id' => $_POST['product_id'], 'is_hot' => $_POST['is_hot'], 'act_desc' => $act_desc, 'act_promise' => $act_promise, 'act_ensure' => $dact_ensure, 'ext_info' => serialize($info));

	if (isset($_POST['review_status'])) {
		$review_status = !empty($_POST['review_status']) ? intval($_POST['review_status']) : 1;
		$review_content = !empty($_POST['review_content']) ? addslashes(trim($_POST['review_content'])) : '';
		$record['review_status'] = $review_status;
		$record['review_content'] = $review_content;
	}

	$db->autoExecute($ecs->table('goods_activity'), $record, 'UPDATE', 'act_id = \'' . $act_id . '\' AND act_type = ' . GAT_SNATCH);
	admin_log($_POST['snatch_name'], 'edit', 'snatch');
	$link[] = array('text' => $_LANG['back_list'], 'href' => 'snatch.php?act=list&' . list_link_postfix());
	sys_msg($_LANG['edit_succeed'], 0, $link);
}
else if ($_REQUEST['act'] == 'view') {
	admin_priv('snatch_manage');
	$id = empty($_REQUEST['snatch_id']) ? 0 : intval($_REQUEST['snatch_id']);
	$bid_list = get_snatch_detail();
	$smarty->assign('bid_list', $bid_list['bid']);
	$smarty->assign('filter', $bid_list['filter']);
	$smarty->assign('record_count', $bid_list['record_count']);
	$smarty->assign('page_count', $bid_list['page_count']);
	$sort_flag = sort_flag($bid_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$smarty->assign('info', get_snatch_info($id));
	$smarty->assign('full_page', 1);
	$smarty->assign('result', get_snatch_result($id));
	$smarty->assign('ur_here', $_LANG['view_detail']);
	$smarty->assign('action_link', array('text' => $_LANG['02_snatch_list'], 'href' => 'snatch.php?act=list'));
	$smarty->display('snatch_view.dwt');
}
else if ($_REQUEST['act'] == 'query_bid') {
	$bid_list = get_snatch_detail();
	$smarty->assign('bid_list', $bid_list['bid']);
	$smarty->assign('filter', $bid_list['filter']);
	$smarty->assign('record_count', $bid_list['record_count']);
	$smarty->assign('page_count', $bid_list['page_count']);
	$sort_flag = sort_flag($bid_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('snatch_view.dwt'), '', array('filter' => $bid_list['filter'], 'page_count' => $bid_list['page_count']));
}
else if ($_REQUEST['act'] == 'batch') {
	admin_priv('snatch_manage');
	if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
		sys_msg('没有选择任何数据', 1);
	}

	$ids = !empty($_POST['checkboxes']) ? join(',', $_POST['checkboxes']) : 0;

	if (isset($_POST['type'])) {
		if ($_POST['type'] == 'batch_remove') {
			$sql = 'DELETE FROM ' . $ecs->table('goods_activity') . ' WHERE act_id ' . db_create_in($ids);

			if ($db->query($sql)) {
				clear_cache_files();
				$links[] = array('text' => $_LANG['back_list'], 'href' => 'snatch.php?act=list');
				sys_msg(sprintf($_LANG['batch_drop_success'], $del_count), 0, $links);
			}

			admin_log('', 'batch_remove', 'snatch_manage');
		}
		else if ($_POST['type'] == 'review_to') {
			$review_status = $_POST['review_status'];
			$review_content = !empty($_POST['review_content']) ? trim($_POST['review_content']) : '';
			$sql = 'UPDATE ' . $ecs->table('goods_activity') . (' SET review_status = \'' . $review_status . '\' ') . ' WHERE act_id ' . db_create_in($ids);

			if ($db->query($sql)) {
				$lnk[] = array('text' => $_LANG['back_list'], 'href' => 'snatch.php?act=list&seller_list=1&' . list_link_postfix());
				sys_msg('夺宝奇兵审核状态设置成功', 0, $lnk);
			}
		}
	}
}
else if ($_REQUEST['act'] == 'search_goods') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$filters = $json->decode($_GET['JSON']);
	$arr['goods'] = get_goods_list($filters);

	if (!empty($arr['goods'][0]['goods_id'])) {
		$arr['products'] = get_good_products($arr['goods'][0]['goods_id']);
	}

	make_json_result($arr);
}
else if ($_REQUEST['act'] == 'search_products') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$filters = $json->decode($_GET['JSON']);

	if (!empty($filters->goods_id)) {
		$arr['products'] = get_good_products($filters->goods_id);
	}

	make_json_result($arr);
}

?>
