<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_order_print_setting($ru_id)
{
	$filter = array();
	$filter['keyword'] = !empty($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
	if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
		$filter['keyword'] = json_str_iconv($filter['keyword']);
	}

	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'ops.sort_order' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);
	$where = 'WHERE 1 ';

	if (!empty($filter['keyword'])) {
		$where .= ' AND (ops.specification LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\'' . ' OR ops.printer LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\'' . ')';
	}

	if (0 < $ru_id) {
		$where .= ' AND ops.ru_id = \'' . $ru_id . '\' ';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_print_setting') . ' AS ops ' . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$arr = array();
	$sql = 'SELECT ops.* FROM ' . $GLOBALS['ecs']->table('order_print_setting') . 'AS ops ' . $where . 'ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$arr[] = $rows;
	}

	return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$adminru = get_admin_ru_id();

if (empty($_REQUEST['act'])) {
	exit('Error');
}
else if ($_REQUEST['act'] == 'kdniao_print') {
	require_once ROOT_PATH . 'includes/lib_order.php';
	require_once ROOT_PATH . 'includes/lib_goods.php';
	require_once ROOT_PATH . '/plugins/tpApi/Kdniao.class.php';
	$order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
	$order_sn = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
	$order_ids = array();

	if (!empty($order_id)) {
		$order_ids[] = $order_id;
	}

	if (!empty($order_sn)) {
		$sql = ' SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_sn ' . db_create_in($order_sn);
		$ids = $GLOBALS['db']->getCol($sql);
		$order_ids = array_merge($order_ids, $ids);
	}

	$link[] = array('text' => '关闭窗口', 'href' => 'javascript:window.close()');

	if (empty($order_ids)) {
		sys_msg('没有选择订单', 1, $link);
	}

	$sql = ' SELECT shipping_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id ' . db_create_in($order_ids);
	$shipping_ids = $GLOBALS['db']->getCol($sql);
	$shipping_ids = array_unique($shipping_ids);

	if (1 < count($shipping_ids)) {
		sys_msg('请选择快递方式相同的订单进行批量打印', 1, $link);
	}

	$batch_html = array();
	$batch_error = array();
	if ($order_ids && $order_ids[0]) {
		$order_info = order_info($order_ids[0]);
		$shipping_info = get_shipping_info($order_info['shipping_id'], $adminru['ru_id']);
		$shipping_spec = get_shipping_spec($shipping_info['shipping_code']);
		$GLOBALS['smarty']->assign('shipping_info', $shipping_info);
		$GLOBALS['smarty']->assign('shipping_spec', $shipping_spec);

		foreach ($order_ids as $order_id) {
			$result = get_kdniao_print_content($order_id, $shipping_spec, $shipping_info);

			if ($result['ResultCode'] != '100') {
				$batch_error[] = '订单（' . $order_id . '）：电子面单下单失败：' . $result['Reason'];
				continue;
			}

			if (!empty($result['PrintTemplate'])) {
				$batch_html[] = $result['PrintTemplate'];
			}
			else {
				$batch_error[] = '订单（' . $order_id . '）：无打印模板';
				continue;
			}

			if (isset($result['Order']['LogisticCode'])) {
				$sql = ' UPDATE ' . $GLOBALS['ecs']->table('order_info') . (' SET invoice_no = \'' . $result['Order']['LogisticCode'] . '\' WHERE order_id = \'' . $order_id . '\' ');
				$GLOBALS['db']->query($sql);
			}
		}
	}

	$smarty->assign('batch_html', $batch_html);
	$smarty->assign('batch_error', implode(',', $batch_error));
	$smarty->assign('kdniao_printer', get_table_date('seller_shopinfo', 'ru_id=\'' . $adminru['ru_id'] . '\'', array('kdniao_printer'), 2));
	$smarty->display('kdniao_print.dwt');
}

if ($_REQUEST['act'] == 'order_print_setting') {
	admin_priv('order_print_setting');
	$smarty->assign('primary_cat', $_LANG['19_merchants_store']);
	$smarty->assign('menu_select', array('action' => '19_merchants_store', 'current' => 'order_print_setting'));
	$smarty->assign('ur_here', $_LANG['order_print_setting']);
	$smarty->assign('action_link', array('text' => $_LANG['order_print_setting_add'], 'href' => 'tp_api.php?act=order_print_setting_add'));
	$smarty->assign('full_page', 1);
	$print_setting = get_order_print_setting($adminru['ru_id']);
	$smarty->assign('print_setting', $print_setting['list']);
	$smarty->assign('filter', $print_setting['filter']);
	$smarty->assign('record_count', $print_setting['record_count']);
	$smarty->assign('page_count', $print_setting['page_count']);
	$sort_flag = sort_flag($print_setting['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('order_print_setting.dwt');
}
else if ($_REQUEST['act'] == 'order_print_setting_query') {
	check_authz_json('order_print_setting');
	$print_setting = get_order_print_setting($adminru['ru_id']);
	$smarty->assign('print_setting', $print_setting['list']);
	$smarty->assign('filter', $print_setting['filter']);
	$smarty->assign('record_count', $print_setting['record_count']);
	$smarty->assign('page_count', $print_setting['page_count']);
	$sort_flag = sort_flag($print_setting['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('order_print_setting.dwt'), '', array('filter' => $print_setting['filter'], 'page_count' => $print_setting['page_count']));
}
else if ($_REQUEST['act'] == 'order_print_setting_remove') {
	check_authz_json('order_print_setting');
	$id = intval($_GET['id']);
	$exc = new exchange($ecs->table('order_print_setting'), $db, 'id', 'ru_id');
	$exc->drop($id);
	$url = 'tp_api.php?act=order_print_setting_query&' . str_replace('act=order_print_setting_remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'edit_order_printer') {
	check_authz_json('order_print_setting');
	$id = intval($_POST['id']);
	$val = trim($_POST['val']);
	$sql = ' UPDATE ' . $ecs->table('order_print_setting') . (' SET printer = \'' . $val . '\' WHERE ru_id = \'' . $adminru['ru_id'] . '\' AND id = \'' . $id . '\' ');
	$db->query($sql);
	make_json_result($val);
}
else if ($_REQUEST['act'] == 'edit_print_width') {
	check_authz_json('order_print_setting');
	$id = intval($_POST['id']);
	$val = trim($_POST['val']);
	$sql = ' UPDATE ' . $ecs->table('order_print_setting') . (' SET width = \'' . $val . '\' WHERE ru_id = \'' . $adminru['ru_id'] . '\' AND id = \'' . $id . '\' ');
	$db->query($sql);
	make_json_result($val);
}
else if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('order_print_setting');
	$id = intval($_POST['id']);
	$val = trim($_POST['val']);
	$sql = ' UPDATE ' . $ecs->table('order_print_setting') . (' SET sort_order = \'' . $val . '\' WHERE ru_id = \'' . $adminru['ru_id'] . '\' AND id = \'' . $id . '\' ');
	$db->query($sql);
	make_json_result($val);
}
else if ($_REQUEST['act'] == 'toggle_order_is_default') {
	check_authz_json('order_print_setting');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	$sql = ' UPDATE ' . $ecs->table('order_print_setting') . (' SET is_default = \'' . $val . '\' WHERE ru_id = \'' . $adminru['ru_id'] . '\' AND id = \'' . $id . '\' ');
	$db->query($sql);

	if ($val) {
		$sql = ' UPDATE ' . $ecs->table('order_print_setting') . (' SET is_default = \'0\' WHERE ru_id = \'' . $adminru['ru_id'] . '\' AND id <> \'' . $id . '\' ');
		$db->query($sql);
	}

	make_json_result($val);
}
else {
	if ($_REQUEST['act'] == 'order_print_setting_add' || $_REQUEST['act'] == 'order_print_setting_edit') {
		admin_priv('order_print_setting');
		$smarty->assign('primary_cat', $_LANG['19_merchants_store']);
		$smarty->assign('menu_select', array('action' => '19_merchants_store', 'current' => 'order_print_setting'));
		$id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$print_size = get_table_date('order_print_size', '1', array('*'), 1);
		$smarty->assign('print_size', $print_size);

		if (0 < $id) {
			$print_setting = get_table_date('order_print_setting', 'id=\'' . $id . '\'', array('*'));
			$smarty->assign('print_setting', $print_setting);
			$smarty->assign('ur_here', $_LANG['order_print_setting_edit']);
			$smarty->assign('form_action', 'order_print_setting_update');
		}
		else {
			$smarty->assign('ur_here', $_LANG['order_print_setting_add']);
			$smarty->assign('form_action', 'order_print_setting_insert');
		}

		$smarty->assign('action_link', array('text' => $_LANG['order_print_setting'], 'href' => 'tp_api.php?act=order_print_setting'));
		assign_query_info();
		$smarty->display('order_print_setting_info.dwt');
	}
	else {
		if ($_REQUEST['act'] == 'order_print_setting_insert' || $_REQUEST['act'] == 'order_print_setting_update') {
			admin_priv('order_print_setting');
			$data = array();
			$id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
			$data['ru_id'] = $adminru['ru_id'];
			$data['is_default'] = !empty($_REQUEST['is_default']) ? intval($_REQUEST['is_default']) : 0;
			$data['specification'] = !empty($_REQUEST['specification']) ? trim($_REQUEST['specification']) : '';
			$data['printer'] = !empty($_REQUEST['printer']) ? trim($_REQUEST['printer']) : '';
			$data['width'] = !empty($_REQUEST['width']) ? intval($_REQUEST['width']) : 0;

			if (empty($data['width'])) {
				$print_size = get_table_date('order_print_size', 'specification=\'' . $data['specification'] . '\'', array('height', 'width'));
				$data['width'] = $print_size['width'];
			}

			$sql = ' SELECT id FROM ' . $ecs->table('order_print_setting') . (' WHERE ru_id = \'' . $adminru['ru_id'] . '\' AND specification = \'' . $data['specification'] . '\' AND id <> \'' . $id . '\' LIMIT 1 ');
			$is_only = $db->getOne($sql);

			if (!empty($is_only)) {
				sys_msg($_LANG['specification_exist'], 1);
			}

			if (0 < $id) {
				$db->autoExecute($ecs->table('order_print_setting'), $data, 'UPDATE', 'id = \'' . $id . '\'');
				$msg = $_LANG['edit_success'];
			}
			else {
				$db->autoExecute($ecs->table('order_print_setting'), $data, 'INSERT');
				$id = $db->insert_id();
				$msg = $_LANG['add_success'];
			}

			if ($data['is_default']) {
				$db->autoExecute($ecs->table('order_print_setting'), array('is_default' => 0), 'UPDATE', 'id <> \'' . $id . '\'');
			}

			$link[] = array('text' => $_LANG['back_list'], 'href' => 'tp_api.php?act=order_print_setting');
			sys_msg($msg, 0, $link);
		}
		else if ($_REQUEST['act'] == 'order_print') {
			admin_priv('order_view');
			$print_specification = get_table_date('order_print_setting', 'ru_id=\'' . $adminru['ru_id'] . '\' AND is_default=\'1\'', array('specification'), 2);

			if (empty($print_specification)) {
				$print_specification = get_table_date('order_print_setting', 'ru_id=\'' . $adminru['ru_id'] . '\' ORDER BY sort_order, id', array('specification'), 2);
			}

			$print_size_info = get_table_date('order_print_size', 'specification=\'' . $print_specification . '\'', array('*'));
			$print_size_list = get_table_date('order_print_setting', 'ru_id=\'' . $adminru['ru_id'] . '\' ORDER BY sort_order, id', array('*'), 1);
			$print_spec_info = get_table_date('order_print_setting', 'specification=\'' . $print_specification . '\'', array('*'));

			if (empty($print_size_list)) {
				$link[] = array('text' => $_LANG['back_set'], 'href' => 'tp_api.php?act=order_print_setting');
				sys_msg($_LANG['no_print_setting'], 1, $link);
			}

			$smarty->assign('print_specification', $print_specification);
			$smarty->assign('print_size_info', $print_size_info);
			$smarty->assign('print_size_list', $print_size_list);
			$smarty->assign('print_spec_info', $print_spec_info);
			$order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
			$order_sn = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
			$order_type = empty($_REQUEST['order_type']) ? 'order' : trim($_REQUEST['order_type']);
			$action_id = get_table_date('admin_action', 'action_code=\'supply_and_demand\'', array('action_id'), 2);
			if ($order_type == 'order' || empty($action_id)) {
				$table = $GLOBALS['ecs']->table('order_info');
			}
			else {
				$table = $GLOBALS['ecs']->table('wholesale_order_info');
			}

			$order_ids = array();

			if (!empty($order_id)) {
				$order_ids[] = $order_id;
			}

			if (!empty($order_sn)) {
				$sql = ' SELECT order_id FROM ' . $table . ' WHERE order_sn ' . db_create_in($order_sn);
				$ids = $GLOBALS['db']->getCol($sql);
				$order_ids = array_merge($order_ids, $ids);
			}

			$web_url = $ecs->url();
			$smarty->assign('web_url', $web_url);
			$smarty->assign('order_type', $order_type);
			$smarty->assign('shop_url', $ecs->seller_url());
			$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'order_print_logo\'';
			$order_print_logo = strstr($GLOBALS['db']->getOne($sql), 'images');
			$smarty->assign('order_print_logo', $ecs->seller_url() . ADMIN_PATH . '/' . $order_print_logo);
			$part_html = array();

			foreach ($order_ids as $order_id) {
				$order_info = get_print_order_info($order_id, $order_type);
				$smarty->assign('order_info', $order_info);
				$smarty->assign('order_sn', $order_info['order_sn']);
				$part_html[] = $smarty->fetch('library/order_print_part.lbi');
			}

			$smarty->assign('part_html', $part_html);
			assign_query_info();
			$smarty->display('order_print.dwt');
		}
		else if ($_REQUEST['act'] == 'change_order_print') {
			check_authz_json('order_view');
			$print_specification = empty($_REQUEST['specification']) ? '' : trim($_REQUEST['specification']);
			$print_size_info = get_table_date('order_print_size', 'specification=\'' . $print_specification . '\'', array('*'));
			$print_size_list = get_table_date('order_print_setting', 'ru_id=\'' . $adminru['ru_id'] . '\' ORDER BY sort_order, id', array('*'), 1);
			$print_spec_info = get_table_date('order_print_setting', 'specification=\'' . $print_specification . '\'', array('*'));
			$smarty->assign('print_specification', $print_specification);
			$smarty->assign('print_size_info', $print_size_info);
			$smarty->assign('print_size_list', $print_size_list);
			$smarty->assign('print_spec_info', $print_spec_info);
			$order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
			$order_sn = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
			$order_type = empty($_REQUEST['order_type']) ? 'order' : trim($_REQUEST['order_type']);
			$action_id = get_table_date('admin_action', 'action_code=\'supply_and_demand\'', array('action_id'), 2);
			if ($order_type == 'order' || empty($action_id)) {
				$table = $GLOBALS['ecs']->table('order_info');
			}
			else {
				$table = $GLOBALS['ecs']->table('wholesale_order_info');
			}

			$order_ids = array();

			if (!empty($order_id)) {
				$order_ids[] = $order_id;
			}

			if (!empty($order_sn)) {
				$sql = ' SELECT order_id FROM ' . $table . ' WHERE order_sn ' . db_create_in($order_sn);
				$ids = $GLOBALS['db']->getCol($sql);
				$order_ids = array_merge($order_ids, $ids);
			}

			$web_url = $ecs->url();
			$smarty->assign('web_url', $web_url);
			$smarty->assign('order_type', $order_type);
			$smarty->assign('shop_url', $ecs->seller_url());
			$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'order_print_logo\'';
			$order_print_logo = strstr($GLOBALS['db']->getOne($sql), 'images');
			$smarty->assign('order_print_logo', $ecs->seller_url() . ADMIN_PATH . '/' . $order_print_logo);
			$part_html = array();

			foreach ($order_ids as $order_id) {
				$order_info = get_print_order_info($order_id, $order_type);
				$smarty->assign('order_info', $order_info);
				$smarty->assign('order_sn', $order_info['order_sn']);
				$part_html[] = $smarty->fetch('library/order_print_part.lbi');
			}

			$smarty->assign('part_html', $part_html);
			$content = $smarty->fetch('library/order_print.lbi');
			make_json_result($content);
		}
	}
}

?>
