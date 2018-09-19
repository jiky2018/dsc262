<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function group_buy_list($ru_id)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
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

		$where = !empty($filter['keyword']) ? ' AND (ga.goods_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\')' : '';

		if (0 < $ru_id) {
			$where .= ' and ga.user_id = \'' . $ru_id . '\'';
		}

		if ($filter['review_status']) {
			$where .= ' AND ga.review_status = \'' . $filter['review_status'] . '\' ';
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

		$where .= !empty($filter['seller_list']) ? ' AND ga.user_id > 0 ' : ' AND ga.user_id = 0 ';
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . ' WHERE ga.act_type = \'' . GAT_GROUP_BUY . ('\' ' . $where);
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT ga.* ' . 'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . ' WHERE ga.act_type = \'' . GAT_GROUP_BUY . ('\' ' . $where . ' ') . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
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
		$stat = group_buy_stat($row['act_id'], $ext_info['deposit']);
		$arr = array_merge($row, $stat, $ext_info);
		$price_ladder = $arr['price_ladder'];
		if (!is_array($price_ladder) || empty($price_ladder)) {
			$price_ladder = array(
				array('amount' => 0, 'price' => 0)
				);
		}
		else {
			foreach ($price_ladder as $key => $amount_price) {
				$price_ladder[$key]['formated_price'] = price_format($amount_price['price']);
			}
		}

		$cur_price = $price_ladder[0]['price'];
		$cur_amount = $stat['valid_goods'];

		foreach ($price_ladder as $amount_price) {
			if ($amount_price['amount'] <= $cur_amount) {
				$cur_price = $amount_price['price'];
			}
			else {
				break;
			}
		}

		$arr['cur_price'] = $cur_price;
		$status = group_buy_status($arr);
		$arr['start_time'] = local_date($GLOBALS['_CFG']['date_format'], $arr['start_time']);
		$arr['end_time'] = local_date($GLOBALS['_CFG']['date_format'], $arr['end_time']);
		$arr['cur_status'] = $GLOBALS['_LANG']['gbs'][$status];
		$arr['user_name'] = get_shop_name($arr['user_id'], 1);
		$list[] = $arr;
	}

	$arr = array('item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function goods_group_buy($goods_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('goods_activity') . (' WHERE goods_id = \'' . $goods_id . '\' ') . ' AND act_type = \'' . GAT_GROUP_BUY . '\'' . ' AND start_time <= ' . gmtime() . ' AND end_time >= ' . gmtime();
	return $GLOBALS['db']->getRow($sql);
}

function list_link($is_add = true)
{
	$href = 'group_buy.php?act=list';

	if (!$is_add) {
		$href .= '&' . list_link_postfix();
	}

	return array('href' => $href, 'text' => $GLOBALS['_LANG']['group_buy_list']);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/lib_goods.php';
require_once ROOT_PATH . 'includes/lib_order.php';
admin_priv('group_by');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', $_LANG['group_buy_list']);
	$smarty->assign('action_link', array('href' => 'group_buy.php?act=add', 'text' => $_LANG['add_group_buy']));
	$list = group_buy_list($adminru['ru_id']);
	$smarty->assign('group_buy_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	self_seller(BASENAME($_SERVER['PHP_SELF']));
	assign_query_info();
	$smarty->display('group_buy_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$list = group_buy_list($adminru['ru_id']);
	$smarty->assign('group_buy_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('group_buy_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		if ($_REQUEST['act'] == 'add') {
			$group_buy = array(
				'act_id'       => 0,
				'start_time'   => date('Y-m-d H:i:s', time() + 86400),
				'end_time'     => date('Y-m-d H:i:s', time() + 4 * 86400),
				'price_ladder' => array(
					array('amount' => 0, 'price' => 0)
					)
				);
		}
		else {
			$group_buy_id = intval($_REQUEST['id']);

			if ($group_buy_id <= 0) {
				exit('invalid param');
			}

			$group_buy = group_buy_info($group_buy_id, 0, 'seller');
		}

		$smarty->assign('group_buy', $group_buy);
		$smarty->assign('ur_here', $_LANG['add_group_buy']);
		$smarty->assign('action_link', list_link($_REQUEST['act'] == 'add'));
		$smarty->assign('ru_id', $adminru['ru_id']);

		if ($_REQUEST['act'] == 'edit') {
			$smarty->assign('form_action', 'update');
		}
		else {
			$smarty->assign('form_action', 'insert');
		}

		set_default_filter();
		assign_query_info();
		$smarty->display('group_buy_info.dwt');
	}
	else if ($_REQUEST['act'] == 'insert_update') {
		$group_buy_id = intval($_POST['act_id']);
		if (isset($_POST['finish']) || isset($_POST['succeed']) || isset($_POST['fail']) || isset($_POST['mail'])) {
			if ($group_buy_id <= 0) {
				sys_msg($_LANG['error_group_buy'], 1);
			}

			$group_buy = group_buy_info($group_buy_id, 0, 'seller');

			if (empty($group_buy)) {
				sys_msg($_LANG['error_group_buy'], 1);
			}
		}

		if (isset($_POST['finish'])) {
			if ($group_buy['status'] != GBS_UNDER_WAY) {
				sys_msg($_LANG['error_status'], 1);
			}

			$sql = 'UPDATE ' . $ecs->table('goods_activity') . ' SET end_time = \'' . gmtime() . '\' ' . ('WHERE act_id = \'' . $group_buy_id . '\' LIMIT 1');
			$db->query($sql);
			clear_cache_files();
			$links = array(
				array('href' => 'group_buy.php?act=list', 'text' => $_LANG['back_list'])
				);
			sys_msg($_LANG['edit_success'], 0, $links);
		}
		else if (isset($_POST['succeed'])) {
			if ($group_buy['status'] != GBS_FINISHED) {
				sys_msg($_LANG['error_status'], 1);
			}

			if (0 < $group_buy['total_order']) {
				$sql = 'SELECT order_id ' . 'FROM ' . $ecs->table('order_info') . ' WHERE extension_code = \'group_buy\' ' . ('AND extension_id = \'' . $group_buy_id . '\' ') . 'AND (order_status = \'' . OS_CONFIRMED . '\' or order_status = \'' . OS_UNCONFIRMED . '\')';
				$order_id_list = $db->getCol($sql);
				$final_price = $group_buy['trans_price'];
				$sql = 'UPDATE ' . $ecs->table('order_goods') . (' SET goods_price = \'' . $final_price . '\' ') . 'WHERE order_id ' . db_create_in($order_id_list);
				$db->query($sql);
				$sql = 'SELECT order_id, SUM(goods_number * goods_price) AS goods_amount ' . 'FROM ' . $ecs->table('order_goods') . ' WHERE order_id ' . db_create_in($order_id_list) . ' GROUP BY order_id';
				$res = $db->query($sql);

				while ($row = $db->fetchRow($res)) {
					$order_id = $row['order_id'];
					$goods_amount = floatval($row['goods_amount']);
					$order = order_info($order_id);

					if ($group_buy['deposit'] <= $order['surplus'] + $order['money_paid']) {
						$order['goods_amount'] = $goods_amount;

						if (0 < $order['insure_fee']) {
							$shipping = shipping_info($order['shipping_id']);
							$order['insure_fee'] = shipping_insure_fee($shipping['shipping_code'], $goods_amount, $shipping['insure']);
						}

						$order['order_amount'] = $order['goods_amount'] + $order['shipping_fee'] + $order['insure_fee'] + $order['pack_fee'] + $order['card_fee'] - $order['money_paid'] - $order['surplus'];

						if (0 < $order['order_amount']) {
							$order['pay_fee'] = pay_fee($order['pay_id'], $order['order_amount']);
						}
						else {
							$order['pay_fee'] = 0;
						}

						$order['order_amount'] += $order['pay_fee'];

						if (0 < $order['order_amount']) {
							$order['pay_status'] = PS_UNPAYED;
							$order['pay_time'] = 0;
						}
						else {
							$order['pay_status'] = PS_PAYED;
							$order['pay_time'] = gmtime();
						}

						if ($order['order_amount'] < 0) {
						}

						$order['order_status'] = OS_CONFIRMED;
						$order['confirm_time'] = gmtime();
						$order['add_time'] = gmtime();
						$order = addslashes_deep($order);
						update_order($order_id, $order);
					}
					else {
						$order['order_status'] = OS_CANCELED;
						$order['to_buyer'] = $_LANG['cancel_order_reason'];
						$order['pay_status'] = PS_UNPAYED;
						$order['pay_time'] = 0;
						$money = $order['surplus'] + $order['money_paid'];

						if (0 < $money) {
							$order['surplus'] = 0;
							$order['money_paid'] = 0;
							$order['order_amount'] = $money;
							order_refund($order, 1, $_LANG['cancel_order_reason'] . ':' . $order['order_sn']);
						}

						$order = addslashes_deep($order);
						update_order($order['order_id'], $order);
					}
				}
			}

			$sql = 'UPDATE ' . $ecs->table('goods_activity') . ' SET is_finished = \'' . GBS_SUCCEED . '\' ' . ('WHERE act_id = \'' . $group_buy_id . '\' LIMIT 1');
			$db->query($sql);
			clear_cache_files();
			$links = array(
				array('href' => 'group_buy.php?act=list', 'text' => $_LANG['back_list'])
				);
			sys_msg($_LANG['edit_success'], 0, $links);
		}
		else if (isset($_POST['fail'])) {
			if ($group_buy['status'] != GBS_FINISHED) {
				sys_msg($_LANG['error_status'], 1);
			}

			if (0 < $group_buy['valid_order']) {
				$sql = 'SELECT * ' . 'FROM ' . $ecs->table('order_info') . ' WHERE extension_code = \'group_buy\' ' . ('AND extension_id = \'' . $group_buy_id . '\' ') . 'AND (order_status = \'' . OS_CONFIRMED . '\' OR order_status = \'' . OS_UNCONFIRMED . '\') ';
				$res = $db->query($sql);

				while ($order = $db->fetchRow($res)) {
					$order['order_status'] = OS_CANCELED;
					$order['to_buyer'] = $_LANG['cancel_order_reason'];
					$order['pay_status'] = PS_UNPAYED;
					$order['pay_time'] = 0;
					$money = $order['surplus'] + $order['money_paid'];

					if (0 < $money) {
						$order['surplus'] = 0;
						$order['money_paid'] = 0;
						$order['order_amount'] = $money;
						order_refund($order, 1, $_LANG['cancel_order_reason'] . ':' . $order['order_sn'], $money);
					}

					$order = addslashes_deep($order);
					update_order($order['order_id'], $order);
				}
			}

			$sql = 'UPDATE ' . $ecs->table('goods_activity') . ' SET is_finished = \'' . GBS_FAIL . '\', ' . ('act_desc = \'' . $_POST['act_desc'] . '\' ') . ('WHERE act_id = \'' . $group_buy_id . '\' LIMIT 1');
			$db->query($sql);
			clear_cache_files();
			$links = array(
				array('href' => 'group_buy.php?act=list', 'text' => $_LANG['back_list'])
				);
			sys_msg($_LANG['edit_success'], 0, $links);
		}
		else if (isset($_POST['mail'])) {
			if ($group_buy['status'] != GBS_SUCCEED) {
				sys_msg($_LANG['error_status'], 1);
			}

			$tpl = get_mail_template('group_buy');
			$count = 0;
			$send_count = 0;
			$sql = 'SELECT o.consignee, o.add_time, g.goods_number, o.order_sn, ' . 'o.order_amount, o.order_id, o.email ' . 'FROM ' . $ecs->table('order_info') . ' AS o, ' . $ecs->table('order_goods') . ' AS g ' . 'WHERE o.order_id = g.order_id ' . 'AND o.extension_code = \'group_buy\' ' . ('AND o.extension_id = \'' . $group_buy_id . '\' ') . 'AND o.order_status = \'' . OS_CONFIRMED . '\'';
			$res = $db->query($sql);

			while ($order = $db->fetchRow($res)) {
				$smarty->assign('consignee', $order['consignee']);
				$smarty->assign('add_time', local_date($_CFG['time_format'], $order['add_time']));
				$smarty->assign('goods_name', $group_buy['goods_name']);
				$smarty->assign('goods_number', $order['goods_number']);
				$smarty->assign('order_sn', $order['order_sn']);
				$smarty->assign('order_amount', price_format($order['order_amount']));
				$smarty->assign('shop_url', $ecs->url() . 'user.php?act=order_detail&order_id=' . $order['order_id']);
				$smarty->assign('shop_name', $_CFG['shop_name']);
				$smarty->assign('send_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
				$content = $smarty->fetch('str:' . $tpl['template_content']);

				if (send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
					$send_count++;
				}

				$count++;
			}

			sys_msg(sprintf($_LANG['mail_result'], $count, $send_count));
		}
		else {
			$goods_id = intval($_POST['goods_id']);

			if ($goods_id <= 0) {
				sys_msg($_LANG['error_goods_null']);
			}

			$info = goods_group_buy($goods_id);
			if ($info && $info['act_id'] != $group_buy_id) {
				sys_msg($_LANG['error_goods_exist']);
			}

			$goods_name = $db->getOne('SELECT goods_name FROM ' . $ecs->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\''));
			$act_name = empty($_POST['act_name']) ? $goods_name : sub_str($_POST['act_name'], 0, 255, false);
			$deposit = floatval($_POST['deposit']);

			if ($deposit < 0) {
				$deposit = 0;
			}

			$restrict_amount = intval($_POST['restrict_amount']);

			if ($restrict_amount < 0) {
				$restrict_amount = 0;
			}

			$gift_integral = intval($_POST['gift_integral']);

			if ($gift_integral < 0) {
				$gift_integral = 0;
			}

			$price_ladder = array();
			$count = count($_POST['ladder_amount']);

			for ($i = $count - 1; 0 <= $i; $i--) {
				$amount = intval($_POST['ladder_amount'][$i]);

				if ($amount <= 0) {
					continue;
				}

				$price = round(floatval($_POST['ladder_price'][$i]), 2);

				if ($price <= 0) {
					continue;
				}

				$price_ladder[$amount] = array('amount' => $amount, 'price' => $price);
			}

			if ($deposit == 0) {
				if (!in_array(1, $_POST['ladder_amount'])) {
					$amount = 1;
					$price = $db->getOne('SELECT market_price FROM ' . $ecs->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\''));
					$price_ladder[$amount] = array('amount' => $amount, 'price' => $price);
				}
			}

			if (count($price_ladder) < 1) {
				sys_msg($_LANG['error_price_ladder']);
			}

			$amount_list = array_keys($price_ladder);
			if (0 < $restrict_amount && $restrict_amount < max($amount_list)) {
				sys_msg($_LANG['error_restrict_amount']);
			}

			ksort($price_ladder);
			$price_ladder = array_values($price_ladder);
			$start_time = local_strtotime($_POST['start_time']);
			$end_time = local_strtotime($_POST['end_time']);

			if ($end_time <= $start_time) {
				sys_msg($_LANG['invalid_time']);
			}

			$is_hot = isset($_REQUEST['is_hot']) ? $_REQUEST['is_hot'] : 0;
			$is_new = isset($_REQUEST['is_new']) ? $_REQUEST['is_new'] : 0;
			$group_buy = array('act_name' => $act_name, 'act_desc' => $_POST['act_desc'], 'act_type' => GAT_GROUP_BUY, 'goods_id' => $goods_id, 'goods_name' => $goods_name, 'start_time' => $start_time, 'end_time' => $end_time, 'review_status' => 3, 'is_hot' => $is_hot, 'is_new' => $is_new, 'ext_info' => serialize(array('price_ladder' => $price_ladder, 'restrict_amount' => $restrict_amount, 'gift_integral' => $gift_integral, 'deposit' => $deposit)));
			clear_cache_files();

			if (0 < $group_buy_id) {
				if (isset($_POST['review_status'])) {
					$review_status = !empty($_POST['review_status']) ? intval($_POST['review_status']) : 1;
					$review_content = !empty($_POST['review_content']) ? addslashes(trim($_POST['review_content'])) : '';
					$group_buy['review_status'] = $review_status;
					$group_buy['review_content'] = $review_content;
				}

				$db->autoExecute($ecs->table('goods_activity'), $group_buy, 'UPDATE', 'act_id = \'' . $group_buy_id . '\' AND act_type = ' . GAT_GROUP_BUY);
				admin_log(addslashes($goods_name) . '[' . $group_buy_id . ']', 'edit', 'group_buy');
				$links = array(
					array('href' => 'group_buy.php?act=list&' . list_link_postfix(), 'text' => $_LANG['back_list'])
					);
				sys_msg($_LANG['edit_success'], 0, $links);
			}
			else {
				$group_buy['user_id'] = $adminru['ru_id'];
				$db->autoExecute($ecs->table('goods_activity'), $group_buy, 'INSERT');
				admin_log(addslashes($goods_name), 'add', 'group_buy');
				$links = array(
					array('href' => 'group_buy.php?act=add', 'text' => $_LANG['continue_add']),
					array('href' => 'group_buy.php?act=list', 'text' => $_LANG['back_list'])
					);
				sys_msg($_LANG['add_success'], 0, $links);
			}
		}
	}
	else if ($_REQUEST['act'] == 'batch') {
		check_authz_json('group_by');
		if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
			sys_msg('没有选择任何数据', 1);
		}

		$ids = !empty($_POST['checkboxes']) ? join(',', $_POST['checkboxes']) : 0;
		$del_count = count($_POST['checkboxes']);

		if (isset($_POST['type'])) {
			if ($_POST['type'] == 'batch_remove') {
				$group_buy = group_buy_info($id, 0, 'seller');

				if ($group_buy['valid_order'] <= 0) {
					$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' WHERE act_id ' . db_create_in($ids);

					if ($db->query($sql)) {
						clear_cache_files();
						admin_log('', 'remove', 'group_buy');
						$links[] = array('text' => $_LANG['back_list'], 'href' => 'group_buy.php?act=list');
						sys_msg(sprintf($_LANG['batch_drop_success'], $del_count), 0, $links);
					}
				}
			}
			else if ($_POST['type'] == 'review_to') {
				$review_status = $_POST['review_status'];
				$review_content = !empty($_POST['review_content']) ? trim($_POST['review_content']) : '';
				$sql = 'UPDATE ' . $ecs->table('goods_activity') . (' SET review_status = \'' . $review_status . '\' ') . ' WHERE act_id ' . db_create_in($ids);

				if ($db->query($sql)) {
					$lnk[] = array('text' => $_LANG['back_list'], 'href' => 'group_buy.php?act=list&seller_list=1&' . list_link_postfix());
					sys_msg('团购审核状态设置成功', 0, $lnk);
				}
			}
		}
	}
	else if ($_REQUEST['act'] == 'group_goods') {
		check_authz_json('group_by');
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$filter = $json->decode($_GET['JSON']);
		$arr = get_goods_info($filter->goods_id);
		make_json_result($arr);
	}
	else if ($_REQUEST['act'] == 'search_goods') {
		check_authz_json('group_by');
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$filter = $json->decode($_GET['JSON']);
		$arr = get_goods_list($filter);
		make_json_result($arr);
	}
	else if ($_REQUEST['act'] == 'edit_deposit') {
		check_authz_json('group_by');
		$id = intval($_POST['id']);
		$val = floatval($_POST['val']);
		$sql = 'SELECT ext_info FROM ' . $ecs->table('goods_activity') . (' WHERE act_id = \'' . $id . '\' AND act_type = \'') . GAT_GROUP_BUY . '\'';
		$ext_info = unserialize($db->getOne($sql));
		$ext_info['deposit'] = $val;
		$sql = 'UPDATE ' . $ecs->table('goods_activity') . ' SET ext_info = \'' . serialize($ext_info) . '\'' . (' WHERE act_id = \'' . $id . '\'');
		$db->query($sql);
		clear_cache_files();
		make_json_result(number_format($val, 2));
	}
	else if ($_REQUEST['act'] == 'edit_restrict_amount') {
		check_authz_json('group_by');
		$id = intval($_POST['id']);
		$val = intval($_POST['val']);
		$sql = 'SELECT ext_info FROM ' . $ecs->table('goods_activity') . (' WHERE act_id = \'' . $id . '\' AND act_type = \'') . GAT_GROUP_BUY . '\'';
		$ext_info = unserialize($db->getOne($sql));
		$ext_info['restrict_amount'] = $val;
		$sql = 'UPDATE ' . $ecs->table('goods_activity') . ' SET ext_info = \'' . serialize($ext_info) . '\'' . (' WHERE act_id = \'' . $id . '\'');
		$db->query($sql);
		clear_cache_files();
		make_json_result($val);
	}
	else if ($_REQUEST['act'] == 'remove') {
		check_authz_json('group_by');
		$id = intval($_GET['id']);
		$group_buy = group_buy_info($id, 0, 'seller');

		if (0 < $group_buy['valid_order']) {
			make_json_error($_LANG['error_exist_order']);
		}

		$sql = 'DELETE FROM ' . $ecs->table('goods_activity') . (' WHERE act_id = \'' . $id . '\' LIMIT 1');
		$db->query($sql);
		admin_log(addslashes($group_buy['goods_name']) . '[' . $id . ']', 'remove', 'group_buy');
		clear_cache_files();
		$url = 'group_buy.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
		exit();
	}
}

?>
