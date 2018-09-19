<?php
//zend by 商创网络 Q Q:123456    禁止倒卖 一经发现停止任何服务
function presale_list($ru_id)
{
	$result = get_filter();
	$where = '';

	if ($result === false) {
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'ga.act_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;
		$filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);
		$filter['rs_id'] = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);
		$adminru = get_admin_ru_id();

		if (0 < $adminru['rs_id']) {
			$filter['rs_id'] = $adminru['rs_id'];
		}

		$where = !empty($filter['keyword']) ? ' AND (ga.goods_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\')' : '';

		if ($filter['review_status']) {
			$where .= ' AND ga.review_status = \'' . $filter['review_status'] . '\' ';
		}

		$where .= get_rs_null_where('ga.user_id', $filter['rs_id']);
		$filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

		if (0 < $ru_id) {
			$where .= ' and ga.user_id = \'' . $ru_id . '\'';
		}

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
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('presale_activity') . ' AS ga ' . (' WHERE 1 ' . $where);
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT ga.* ' . 'FROM ' . $GLOBALS['ecs']->table('presale_activity') . ' AS ga ' . (' WHERE 1 ' . $where . ' ') . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
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
		$stat = presale_stat($row['act_id'], $row['deposit']);
		$arr = array_merge($row, $stat);
		$status = presale_status($arr);
		$arr['start_time'] = local_date($GLOBALS['_CFG']['date_format'], $arr['start_time']);
		$arr['end_time'] = local_date($GLOBALS['_CFG']['date_format'], $arr['end_time']);
		$arr['pay_start_time'] = local_date($GLOBALS['_CFG']['date_format'], $arr['pay_start_time']);
		$arr['pay_end_time'] = local_date($GLOBALS['_CFG']['date_format'], $arr['pay_end_time']);
		$arr['cur_status'] = $GLOBALS['_LANG']['gbs'][$status];
		$arr['act_name'] = !empty($arr['act_name']) ? $arr['act_name'] : $arr['goods_name'];
		$arr['shop_name'] = get_shop_name($row['user_id'], 1);
		$list[] = $arr;
	}

	$arr = array('item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function goods_presale($goods_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('presale_activity') . (' WHERE goods_id = \'' . $goods_id . '\' ') . ' AND start_time <= ' . gmtime() . ' AND end_time >= ' . gmtime() . ' LIMIT 1';
	return $GLOBALS['db']->getRow($sql);
}

function list_link($is_add = true)
{
	$href = 'presale.php?act=list';

	if (!$is_add) {
		$href .= '&' . list_link_postfix();
	}

	return array('href' => $href, 'text' => $GLOBALS['_LANG']['presale_list']);
}

function get_shop_price($goods_id)
{
	$sql = ' SELECT shop_price FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\' ');
	return $GLOBALS['db']->getOne($sql);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/lib_goods.php';
require_once ROOT_PATH . 'includes/lib_order.php';
require_once ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php';
admin_priv('presale');
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
	$smarty->assign('ur_here', $_LANG['presale_list']);
	$smarty->assign('action_link', array('href' => 'presale.php?act=add', 'text' => $_LANG['add_presale']));

	if ($adminru['ru_id'] == 0) {
		$smarty->assign('presale_cat_link', array('href' => 'presale_cat.php?act=list', 'text' => '预售分类列表'));
	}

	$list = presale_list($adminru['ru_id']);
	$smarty->assign('presale_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	self_seller(BASENAME($_SERVER['PHP_SELF']));
	assign_query_info();
	$smarty->display('presale_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$list = presale_list($adminru['ru_id']);
	$smarty->assign('presale_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('presale_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		if ($_REQUEST['act'] == 'add') {
			$presale = array('cat_id' => 0, 'act_desc' => '', 'start_time' => date('Y-m-d H:i:s', time() + 86400), 'end_time' => date('Y-m-d H:i:s', time() + 4 * 86400), 'pay_start_time' => date('Y-m-d H:i:s', time() + 4 * 86400 + 1), 'pay_end_time' => date('Y-m-d H:i:s', time() + 6 * 86400));
			$smarty->assign('ur_here', $_LANG['add_presale']);
			$smarty->assign('form_action', 'insert');
		}
		else {
			$presale_id = intval($_REQUEST['id']);

			if ($presale_id <= 0) {
				exit('invalid param');
			}

			$presale = presale_info($presale_id, 0, 0, 'seller');
			$smarty->assign('ur_here', '编辑预售活动');
			$smarty->assign('form_action', 'update');
		}

		$smarty->assign('presale', $presale);
		create_html_editor2('act_desc', 'act_desc', $presale['act_desc']);
		$smarty->assign('action_link', list_link($_REQUEST['act'] == 'add'));
		$cat_select = presale_cat_list(0, $presale['cat_id'], false, 0, true, '', 1);

		foreach ($cat_select as $k => $v) {
			if ($v['level']) {
				$level = '';

				for ($i = 0; $i < $v['level']; $i++) {
					$level .= '&nbsp;&nbsp;&nbsp;&nbsp;';
				}

				$cat_select[$k]['name'] = $level . $v['name'];
			}
		}

		$smarty->assign('cat_select', $cat_select);
		set_default_filter();
		$smarty->assign('ru_id', $presale['ru_id']);
		assign_query_info();
		$smarty->display('presale_info.dwt');
	}
	else if ($_REQUEST['act'] == 'insert_update') {
		$presale_id = intval($_POST['act_id']);
		if (isset($_POST['finish']) || isset($_POST['succeed']) || isset($_POST['fail']) || isset($_POST['mail'])) {
			if ($presale_id <= 0) {
				sys_msg($_LANG['error_presale'], 1);
			}

			$presale = presale_info($presale_id, 0, 0, 'seller');

			if (empty($presale)) {
				sys_msg($_LANG['error_presale'], 1);
			}
		}

		if (isset($_POST['finish'])) {
			if ($presale['status'] != GBS_UNDER_WAY) {
				sys_msg($_LANG['error_status'], 1);
			}

			$sql = 'UPDATE ' . $ecs->table('presale_activity') . ' SET end_time = \'' . gmtime() . '\' ' . ('WHERE act_id = \'' . $presale_id . '\' LIMIT 1');
			$db->query($sql);
			clear_cache_files();
			$links = array(
				array('href' => 'presale.php?act=list', 'text' => $_LANG['back_list'])
				);
			sys_msg($_LANG['edit_success'], 0, $links);
		}
		else if (isset($_POST['succeed'])) {
			if ($presale['status'] != GBS_FINISHED) {
				sys_msg($_LANG['error_status'], 1);
			}

			if (0 < $presale['total_order']) {
				$sql = 'SELECT order_id ' . 'FROM ' . $ecs->table('order_info') . ' WHERE extension_code = \'presale\' ' . ('AND extension_id = \'' . $presale_id . '\' ') . 'AND (order_status = \'' . OS_CONFIRMED . '\' or order_status = \'' . OS_UNCONFIRMED . '\')';
				$order_id_list = $db->getCol($sql);
				$final_price = $presale['trans_price'];
				$sql = 'UPDATE ' . $ecs->table('order_goods') . (' SET goods_price = \'' . $final_price . '\' ') . 'WHERE order_id ' . db_create_in($order_id_list);
				$db->query($sql);
				$sql = 'SELECT order_id, SUM(goods_number * goods_price) AS goods_amount ' . 'FROM ' . $ecs->table('order_goods') . ' WHERE order_id ' . db_create_in($order_id_list) . ' GROUP BY order_id';
				$res = $db->query($sql);

				while ($row = $db->fetchRow($res)) {
					$order_id = $row['order_id'];
					$goods_amount = floatval($row['goods_amount']);
					$order = order_info($order_id);

					if ($presale['deposit'] <= $order['surplus'] + $order['money_paid']) {
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

			$sql = 'UPDATE ' . $ecs->table('presale_activity') . ' SET is_finished = \'' . GBS_SUCCEED . '\' ' . ('WHERE act_id = \'' . $presale_id . '\' LIMIT 1');
			$db->query($sql);
			clear_cache_files();
			$links = array(
				array('href' => 'presale.php?act=list', 'text' => $_LANG['back_list'])
				);
			sys_msg($_LANG['edit_success'], 0, $links);
		}
		else if (isset($_POST['fail'])) {
			if ($presale['status'] != GBS_FINISHED) {
				sys_msg($_LANG['error_status'], 1);
			}

			if (0 < $presale['valid_order']) {
				$sql = 'SELECT * ' . 'FROM ' . $ecs->table('order_info') . ' WHERE extension_code = \'presale\' ' . ('AND extension_id = \'' . $presale_id . '\' ') . 'AND (order_status = \'' . OS_CONFIRMED . '\' OR order_status = \'' . OS_UNCONFIRMED . '\') ';
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

			$sql = 'UPDATE ' . $ecs->table('presale_activity') . ' SET is_finished = \'' . GBS_FAIL . '\', ' . ('act_desc = \'' . $_POST['act_desc'] . '\' ') . ('WHERE act_id = \'' . $presale_id . '\' LIMIT 1');
			$db->query($sql);
			clear_cache_files();
			$links = array(
				array('href' => 'presale.php?act=list', 'text' => $_LANG['back_list'])
				);
			sys_msg($_LANG['edit_success'], 0, $links);
		}
		else if (isset($_POST['mail'])) {
			if ($presale['status'] != GBS_SUCCEED) {
				sys_msg($_LANG['error_status'], 1);
			}

			$tpl = get_mail_template('presale');
			$count = 0;
			$send_count = 0;
			$sql = 'SELECT o.consignee, o.add_time, g.goods_number, o.order_sn, ' . 'o.order_amount, o.order_id, o.email ' . 'FROM ' . $ecs->table('order_info') . ' AS o, ' . $ecs->table('order_goods') . ' AS g ' . 'WHERE o.order_id = g.order_id ' . 'AND o.extension_code = \'presale\' ' . ('AND o.extension_id = \'' . $presale_id . '\' ') . 'AND o.order_status = \'' . OS_CONFIRMED . '\'';
			$res = $db->query($sql);

			while ($order = $db->fetchRow($res)) {
				$smarty->assign('consignee', $order['consignee']);
				$smarty->assign('add_time', local_date($_CFG['time_format'], $order['add_time']));
				$smarty->assign('goods_name', $presale['goods_name']);
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

			$info = goods_presale($goods_id);
			if ($info && $info['act_id'] != $presale_id) {
				sys_msg($_LANG['error_goods_exist']);
			}

			$goods_name = $db->getOne('SELECT goods_name FROM ' . $ecs->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\''));
			$act_name = empty($_POST['act_name']) ? $goods_name : sub_str($_POST['act_name'], 0, 255, false);
			$deposit = floatval($_POST['deposit']);

			if ($deposit < 0) {
				$deposit = 0;
			}

			$start_time = local_strtotime($_POST['start_time']);
			$end_time = local_strtotime($_POST['end_time']);
			$pay_start_time = local_strtotime($_POST['pay_start_time']);
			$pay_end_time = local_strtotime($_POST['pay_end_time']);
			if ($end_time <= $start_time || $pay_end_time <= $pay_start_time || $pay_start_time < $end_time) {
				sys_msg($_LANG['invalid_time']);
			}

			$presale = array('act_name' => $act_name, 'act_desc' => $_POST['act_desc'], 'cat_id' => intval($_POST['cat_id']), 'goods_id' => $goods_id, 'goods_name' => $goods_name, 'start_time' => $start_time, 'end_time' => $end_time, 'pay_start_time' => $pay_start_time, 'pay_end_time' => $pay_end_time, 'deposit' => $deposit);
			clear_cache_files();

			if (0 < $presale_id) {
				if (isset($_POST['review_status'])) {
					$review_status = !empty($_POST['review_status']) ? intval($_POST['review_status']) : 1;
					$review_content = !empty($_POST['review_content']) ? addslashes(trim($_POST['review_content'])) : '';
					$presale['review_status'] = $review_status;
					$presale['review_content'] = $review_content;
				}

				$db->autoExecute($ecs->table('presale_activity'), $presale, 'UPDATE', 'act_id = \'' . $presale_id . '\'');
				admin_log(addslashes($goods_name) . '[' . $presale_id . ']', 'edit', 'presale');
				$links = array(
					array('href' => 'presale.php?act=list&' . list_link_postfix(), 'text' => $_LANG['back_list'])
					);
				sys_msg($_LANG['edit_success'], 0, $links);
			}
			else {
				$presale['review_status'] = 3;
				$presale['user_id'] = $adminru['ru_id'];
				$db->autoExecute($ecs->table('presale_activity'), $presale, 'INSERT');
				admin_log(addslashes($goods_name), 'add', 'presale');
				$links = array(
					array('href' => 'presale.php?act=add', 'text' => $_LANG['continue_add']),
					array('href' => 'presale.php?act=list', 'text' => $_LANG['back_list'])
					);
				sys_msg($_LANG['add_success'], 0, $links);
			}
		}
	}
	else if ($_REQUEST['act'] == 'batch') {
		check_authz_json('presale');
		if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
			sys_msg('没有选择任何数据', 1);
		}

		$ids = !empty($_POST['checkboxes']) ? $_POST['checkboxes'] : 0;

		if (isset($_POST['type'])) {
			if ($_POST['type'] == 'batch_remove') {
				$del_count = 0;

				foreach ($ids as $key => $id) {
					$presale = presale_info($id, 0, 0, 'seller');

					if ($presale['valid_order'] <= 0) {
						$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('presale_activity') . (' WHERE act_id = \'' . $id . '\' LIMIT 1');
						$GLOBALS['db']->query($sql, 'SILENT');
						admin_log(addslashes($presale['goods_name']) . '[' . $id . ']', 'remove', 'presale');
						$del_count++;
					}
				}

				if (0 < $del_count) {
					clear_cache_files();
				}

				$links[] = array('text' => $_LANG['back_list'], 'href' => 'presale.php?act=list');
				sys_msg(sprintf($_LANG['batch_drop_success'], $del_count), 0, $links);
			}
			else if ($_POST['type'] == 'review_to') {
				$review_status = $_POST['review_status'];
				$sql = 'UPDATE ' . $ecs->table('presale_activity') . (' SET review_status = \'' . $review_status . '\' ') . ' WHERE act_id ' . db_create_in($ids);

				if ($db->query($sql)) {
					$lnk[] = array('text' => $_LANG['back_list'], 'href' => 'presale.php?act=list&seller_list=1&' . list_link_postfix());
					sys_msg('积分商品审核状态设置成功', 0, $lnk);
				}
			}
		}
	}
	else if ($_REQUEST['act'] == 'search_goods') {
		check_authz_json('presale');
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$filter = $json->decode($_GET['JSON']);
		$arr = get_goods_list($filter);
		make_json_result($arr);
	}
	else if ($_REQUEST['act'] == 'get_price') {
		check_authz_json('presale');
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$goods_id = $json->decode($_GET['goods_id']);
		$shop_price = get_shop_price($goods_id);
		make_json_result($shop_price);
	}
	else if ($_REQUEST['act'] == 'edit_deposit') {
		check_authz_json('presale');
		$id = intval($_POST['id']);
		$val = floatval($_POST['val']);
		$sql = 'UPDATE ' . $ecs->table('presale_activity') . ' SET deposit = \'' . $val . '\'' . (' WHERE act_id = \'' . $id . '\'');
		$db->query($sql);
		clear_cache_files();
		make_json_result(number_format($val, 2));
	}
	else if ($_REQUEST['act'] == 'remove') {
		check_authz_json('presale');
		$id = intval($_GET['id']);
		$presale = presale_info($id, 0, 0, 'seller');

		if (0 < $presale['valid_order']) {
			make_json_error($_LANG['error_exist_order']);
		}

		$sql = 'DELETE FROM ' . $ecs->table('presale_activity') . (' WHERE act_id = \'' . $id . '\' LIMIT 1');
		$db->query($sql);
		admin_log(addslashes($presale['goods_name']) . '[' . $id . ']', 'remove', 'presale');
		clear_cache_files();
		$url = 'presale.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
		exit();
	}
}

?>
