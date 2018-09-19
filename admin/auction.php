<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function auction_list($ru_id)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['is_going'] = empty($_REQUEST['is_going']) ? 0 : 1;
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'ga.act_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;
		$filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);
		$filter['rs_id'] = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);
		$adminru = get_admin_ru_id();

		if (0 < $adminru['rs_id']) {
			$filter['rs_id'] = $adminru['rs_id'];
		}

		$where = '';

		if (!empty($filter['keyword'])) {
			$where .= ' AND (ga.goods_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\' OR ga.act_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\') ';
		}

		if ($filter['is_going']) {
			$now = gmtime();
			$where .= ' AND ga.is_finished = 0 AND ga.start_time <= \'' . $now . '\' AND ga.end_time >= \'' . $now . '\' ';
		}

		if (0 < $ru_id) {
			$where .= ' and ga.user_id = \'' . $ru_id . '\'';
		}

		$where .= get_rs_null_where('ga.user_id', $filter['rs_id']);

		if ($filter['review_status']) {
			$where .= ' AND ga.review_status = \'' . $filter['review_status'] . '\' ';
		}

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

		$where .= !empty($filter['seller_list']) ? ' AND ga.user_id > 0 ' : ' AND ga.user_id = 0 ';
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . ' WHERE ga.act_type = \'' . GAT_AUCTION . ('\' ' . $where);
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT ga.* ' . 'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . ' WHERE ga.act_type = \'' . GAT_AUCTION . ('\' ' . $where . ' ') . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
		$filter['keyword'] = stripslashes($filter['keyword']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->query($sql);
	$list = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$ext_info = unserialize($row['ext_info']);
		$arr = array_merge($row, $ext_info);
		$arr['start_time'] = local_date('Y-m-d H:i:s', $arr['start_time']);
		$arr['end_time'] = local_date('Y-m-d H:i:s', $arr['end_time']);
		$arr['ru_name'] = get_shop_name($arr['user_id'], 1);
		$arr['status_no'] = auction_status($row);
		$arr['status'] = $GLOBALS['_LANG']['auction_status'][$arr['status_no']];
		$list[] = $arr;
	}

	$arr = array('item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function list_link($is_add = true, $text = '')
{
	$href = 'auction.php?act=list';

	if (!$is_add) {
		$href .= '&' . list_link_postfix();
	}

	if ($text == '') {
		$text = $GLOBALS['_LANG']['auction_list'];
	}

	return array('href' => $href, 'text' => $text);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . 'includes/lib_goods.php';
$exc = new exchange($ecs->table('goods_activity'), $db, 'act_id', 'act_name');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

if ($_REQUEST['act'] == 'list') {
	admin_priv('auction');
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', $_LANG['auction_list']);
	$smarty->assign('action_link', array('href' => 'auction.php?act=add', 'text' => $_LANG['add_auction']));
	$list = auction_list($adminru['ru_id']);
	$smarty->assign('auction_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	self_seller(BASENAME($_SERVER['PHP_SELF']));
	assign_query_info();
	$smarty->display('auction_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$list = auction_list($adminru['ru_id']);
	$smarty->assign('auction_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('auction_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('auction');
	$id = intval($_GET['id']);
	$auction = auction_info($id, false, 'seller');

	if (empty($auction)) {
		make_json_error($_LANG['auction_not_exist']);
	}

	if (0 < $auction['bid_user_count']) {
		make_json_error($_LANG['auction_cannot_remove']);
	}

	$name = $auction['act_name'];
	$exc->drop($id);
	admin_log($name, 'remove', 'auction');
	clear_cache_files();
	$url = 'auction.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'batch') {
	check_authz_json('auction');
	if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
		sys_msg('没有选择任何数据', 1);
	}

	$ids = !empty($_POST['checkboxes']) ? $_POST['checkboxes'] : 0;

	if (isset($_POST['type'])) {
		if ($_POST['type'] == 'batch_remove') {
			$sql = 'SELECT DISTINCT act_id FROM ' . $ecs->table('auction_log') . ' WHERE act_id ' . db_create_in($ids);
			$ids = array_diff($ids, $db->getCol($sql));

			if (!empty($ids)) {
				$sql = 'DELETE FROM ' . $ecs->table('goods_activity') . ' WHERE act_id ' . db_create_in($ids) . ' AND act_type = \'' . GAT_AUCTION . '\'';
				$db->query($sql);
				admin_log('', 'batch_remove', 'auction');
				clear_cache_files();
			}

			$links[] = array('text' => $_LANG['back_auction_list'], 'href' => 'auction.php?act=list&' . list_link_postfix());
			sys_msg($_LANG['batch_drop_ok'], 0, $links);
		}
		else if ($_POST['type'] == 'review_to') {
			$review_status = $_POST['review_status'];
			$review_content = !empty($_POST['review_content']) ? trim($_POST['review_content']) : '';
			$sql = 'UPDATE ' . $ecs->table('goods_activity') . (' SET review_status = \'' . $review_status . '\' ') . ' WHERE act_id ' . db_create_in($ids);

			if ($db->query($sql)) {
				$lnk[] = array('text' => $_LANG['back_auction_list'], 'href' => 'auction.php?act=list&seller_list=1&' . list_link_postfix());
				sys_msg('拍卖活动审核状态设置成功', 0, $lnk);
			}
		}
	}
}
else if ($_REQUEST['act'] == 'view_log') {
	admin_priv('auction');

	if (empty($_GET['id'])) {
		sys_msg('invalid param');
	}

	$id = intval($_GET['id']);
	$auction = auction_info($id, false, 'seller');

	if (empty($auction)) {
		sys_msg($_LANG['auction_not_exist']);
	}

	$smarty->assign('auction', $auction);
	$smarty->assign('auction_log', auction_log($id));
	$smarty->assign('ur_here', $_LANG['auction_log']);
	$smarty->assign('action_link', array('href' => 'auction.php?act=list&' . list_link_postfix(), 'text' => $_LANG['auction_list']));
	assign_query_info();
	$smarty->display('auction_log.dwt');
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		admin_priv('auction');
		$is_add = $_REQUEST['act'] == 'add';
		$smarty->assign('form_action', $is_add ? 'insert' : 'update');
		$standard_time = date('Y-m-d H:i:s', strtotime('-1 day'));
		$smarty->assign('standard_time', $standard_time);

		if ($is_add) {
			$auction = array('act_id' => 0, 'act_name' => '', 'act_desc' => '', 'act_promise' => '', 'act_ensure' => '', 'goods_id' => 0, 'product_id' => 0, 'goods_name' => $_LANG['pls_search_goods'], 'start_time' => date('Y-m-d H:i:s', time() + 86400), 'end_time' => date('Y-m-d H:i:s', time() + 4 * 86400), 'deposit' => 0, 'start_price' => 0, 'end_price' => 0, 'is_hot' => 0, 'amplitude' => 0);
		}
		else {
			if (empty($_GET['id'])) {
				sys_msg('invalid param');
			}

			$id = intval($_GET['id']);
			$auction = auction_info($id, true, 'seller');

			if (empty($auction)) {
				sys_msg($_LANG['auction_not_exist']);
			}

			$auction['status'] = $_LANG['auction_status'][$auction['status_no']];
			$smarty->assign('bid_user_count', sprintf($_LANG['bid_user_count'], $auction['bid_user_count']));
		}

		create_html_editor2('act_desc', 'act_desc', $auction['act_desc']);
		create_html_editor2('act_promise', 'act_promise', $auction['act_promise']);
		create_html_editor2('act_ensure', 'act_ensure', $auction['act_ensure']);
		$smarty->assign('auction', $auction);
		set_default_filter();
		$smarty->assign('cfg_lang', $_CFG['lang']);
		$smarty->assign('good_products_select', get_good_products_select($auction['goods_id']));

		if ($is_add) {
			$smarty->assign('ur_here', $_LANG['add_auction']);
		}
		else {
			$smarty->assign('ur_here', $_LANG['edit_auction']);
		}

		$smarty->assign('action_link', list_link($is_add));
		$smarty->assign('ru_id', $adminru['ru_id']);
		assign_query_info();
		$smarty->display('auction_info.dwt');
	}
	else {
		if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
			admin_priv('auction');
			$preg = '/<script[\\s\\S]*?<\\/script>/i';
			$act_desc = isset($_POST['act_desc']) ? preg_replace($preg, '', stripslashes(trim($_POST['act_desc']))) : '';
			$act_promise = isset($_POST['act_promise']) ? preg_replace($preg, '', stripslashes(trim($_POST['act_promise']))) : '';
			$dact_ensure = isset($_POST['act_ensure']) ? preg_replace($preg, '', stripslashes(trim($_POST['act_ensure']))) : '';
			$is_add = $_REQUEST['act'] == 'insert';
			$goods_id = intval($_POST['goods_id']);

			if ($goods_id <= 0) {
				sys_msg($_LANG['pls_select_goods']);
			}

			$sql = 'SELECT goods_name FROM ' . $ecs->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\'');
			$row = $db->getRow($sql);

			if (empty($row)) {
				sys_msg($_LANG['goods_not_exist']);
			}

			$goods_name = $row['goods_name'];
			$adminru = get_admin_ru_id();
			$auction = array('act_id' => intval($_POST['id']), 'act_name' => empty($_POST['act_name']) ? $goods_name : sub_str($_POST['act_name'], 255, false), 'act_desc' => $act_desc, 'act_promise' => $act_promise, 'act_ensure' => $dact_ensure, 'act_type' => GAT_AUCTION, 'goods_id' => $goods_id, 'product_id' => empty($_POST['product_id']) ? 0 : $_POST['product_id'], 'goods_name' => $goods_name, 'start_time' => local_strtotime($_POST['start_time']), 'end_time' => local_strtotime($_POST['end_time']), 'review_status' => 3, 'ext_info' => serialize(array('deposit' => round(floatval($_POST['deposit']), 2), 'start_price' => round(floatval($_POST['start_price']), 2), 'end_price' => empty($_POST['no_top']) ? round(floatval($_POST['end_price']), 2) : 0, 'amplitude' => round(floatval($_POST['amplitude']), 2), 'no_top' => !empty($_POST['no_top']) ? intval($_POST['no_top']) : 0, 'is_hot' => !empty($_POST['is_hot']) ? intval($_POST['is_hot']) : 0)));

			if ($is_add) {
				$auction['user_id'] = $adminru['ru_id'];
				$auction['is_finished'] = 0;
				$db->autoExecute($ecs->table('goods_activity'), $auction, 'INSERT');
				$auction['act_id'] = $db->insert_id();
			}
			else {
				if (isset($_POST['review_status'])) {
					$review_status = !empty($_POST['review_status']) ? intval($_POST['review_status']) : 1;
					$review_content = !empty($_POST['review_content']) ? addslashes(trim($_POST['review_content'])) : '';
					$auction['review_status'] = $review_status;
					$auction['review_content'] = $review_content;
				}

				$db->autoExecute($ecs->table('goods_activity'), $auction, 'UPDATE', 'act_id = \'' . $auction['act_id'] . '\'');
			}

			if ($is_add) {
				admin_log($auction['act_name'], 'add', 'auction');
			}
			else {
				admin_log($auction['act_name'], 'edit', 'auction');
			}

			clear_cache_files();

			if ($is_add) {
				$links = array(
					array('href' => 'auction.php?act=add', 'text' => $_LANG['continue_add_auction']),
					array('href' => 'auction.php?act=list', 'text' => $_LANG['back_auction_list'])
					);
				sys_msg($_LANG['add_auction_ok'], 0, $links);
			}
			else {
				$links = array(
					array('href' => 'auction.php?act=list&' . list_link_postfix(), 'text' => $_LANG['back_auction_list'])
					);
				sys_msg($_LANG['edit_auction_ok'], 0, $links);
			}
		}
		else if ($_REQUEST['act'] == 'toggle_hot') {
			check_authz_json('auction');
			$id = intval($_POST['id']);
			$val = intval($_POST['val']);
			$exc->edit('is_hot = \'' . $val . '\'', $id);
			clear_cache_files();
			make_json_result($val);
		}
		else if ($_REQUEST['act'] == 'settle_money') {
			admin_priv('auction');

			if (empty($_POST['id'])) {
				sys_msg('invalid param');
			}

			$id = intval($_POST['id']);
			$auction = auction_info($id, false, 'seller');

			if (empty($auction)) {
				sys_msg($_LANG['auction_not_exist']);
			}

			$is_order = 0;
			if ($auction['status_no'] == SETTLED && 0 < $auction['order_count']) {
				$is_order = 1;
			}

			if ($auction['status_no'] != FINISHED && $is_order == 0) {
				sys_msg($_LANG['invalid_status']);
			}

			if ($auction['deposit'] <= 0) {
				sys_msg($_LANG['no_deposit']);
			}

			$exc->edit('is_finished = 2', $id);

			if (isset($_POST['unfreeze'])) {
				log_account_change($auction['last_bid']['bid_user'], $auction['deposit'], -1 * $auction['deposit'], 0, 0, sprintf($_LANG['unfreeze_auction_deposit'], $auction['act_name']));
			}
			else {
				log_account_change($auction['last_bid']['bid_user'], 0, -1 * $auction['deposit'], 0, 0, sprintf($_LANG['deduct_auction_deposit'], $auction['act_name']));
			}

			admin_log($auction['act_name'], 'edit', 'auction');
			clear_cache_files();
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'auction.php?act=edit&id=' . $id);
			sys_msg($_LANG['settle_deposit_ok'], 0, $link);
		}
		else if ($_REQUEST['act'] == 'search_goods') {
			check_authz_json('auction');
			include_once ROOT_PATH . 'includes/cls_json.php';
			$json = new JSON();
			$filter = $json->decode($_GET['JSON']);
			$arr['goods'] = get_goods_list($filter);

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
	}
}

?>
