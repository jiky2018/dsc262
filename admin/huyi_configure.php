<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function note_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$where = ' WHERE 1 ';
		$filter['record_count'] = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('alidayu_configure') . $where);
		$filter = page_and_size($filter);
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('alidayu_configure') . $where . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$note_list = $GLOBALS['db']->getAll($sql);
	$arr = array('note_list' => $note_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_test_sms_params($send_time, $test)
{
	$smsParams['mobile_phone'] = $GLOBALS['_CFG']['sms_shop_mobile'];

	switch ($send_time) {
	case 'sms_order_placed':
		$smsParams['user_name'] = $test['user_name'];
		$smsParams['order_sn'] = $test['order_sn'];
		$smsParams['consignee'] = $test['consignee'];
		break;

	case 'sms_order_payed':
		$smsParams['shop_name'] = $test['shop_name'];
		$smsParams['order_sn'] = $test['order_sn'];
		$smsParams['consignee'] = $test['consignee'];
		$smsParams['order_region'] = $test['order_region'];
		$smsParams['address'] = $test['address'];
		$smsParams['order_mobile'] = $test['order_mobile'];
		break;

	case 'sms_order_shipped':
		$smsParams['shop_name'] = $test['shop_name'];
		$smsParams['order_sn'] = $test['order_sn'];
		$smsParams['consignee'] = $test['consignee'];
		$smsParams['user_name'] = $test['user_name'];
		$smsParams['invoice_no'] = $test['invoice_no'];
		break;

	case 'store_order_code':
		$smsParams['user_name'] = $test['user_name'];
		$smsParams['order_sn'] = $test['order_sn'];
		$smsParams['store_address'] = $test['store_address'];
		$smsParams['code'] = $test['code'];
		break;

	case 'sms_signin':
		$smsParams['code'] = $test['code'];
		$smsParams['product'] = $test['product'];
		break;

	case 'sms_find_signin':
		$smsParams['code'] = $test['code'];
		break;

	case 'sms_code':
		$smsParams['code'] = $test['code'];
		break;

	case 'sms_price_notic':
		$smsParams['user_name'] = $test['user_name'];
		$smsParams['goods_sn'] = $test['goods_sn'];
		break;

	case 'sms_seller_signin':
		$smsParams['seller_name'] = $test['seller_name'];
		$smsParams['login_name'] = $test['login_name'];
		$smsParams['password'] = $test['password'];
		break;

	default:
		$smsParams = array();
	}

	return $smsParams;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/data/sms_templates.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

admin_priv('sms_setting');
$smarty->assign('menu_select', array('action' => '01_system', 'current' => 'huyi_configure'));

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('action_link', array('text' => $_LANG['alidayu_add'], 'href' => 'huyi_configure.php?act=add'));
	$smarty->assign('action_link2', array('text2' => $_LANG['alidayu_set'], 'href' => 'huyi_configure.php?act=set_up'));
	$smarty->assign('ur_here', $_LANG['huyi_configure']);
	$smarty->assign('form_act', 'insert');
	$note_list = note_list();

	foreach ($note_list['note_list'] as $k => $v) {
		$note_list['note_list'][$k]['send_time'] = array_search($v['send_time'], $send_time);
		$note_list['note_list'][$k]['add_time'] = local_date('Y-m-d H:i:s', $v['add_time']);
	}

	$smarty->assign('note_list', $note_list['note_list']);
	$smarty->assign('filter', $note_list['filter']);
	$smarty->assign('record_count', $note_list['record_count']);
	$smarty->assign('page_count', $note_list['page_count']);
	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->display('huyi_configure_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$note_list = note_list();

	foreach ($note_list['note_list'] as $k => $v) {
		$note_list['note_list'][$k]['send_time'] = array_search($v['send_time'], $send_time);
		$note_list['note_list'][$k]['add_time'] = local_date('Y-m-d H:i:s', $v['add_time']);
	}

	$smarty->assign('note_list', $note_list['note_list']);
	$smarty->assign('filter', $note_list['filter']);
	$smarty->assign('record_count', $note_list['record_count']);
	$smarty->assign('page_count', $note_list['page_count']);
	$sort_flag = sort_flag($note_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('huyi_configure_list.dwt'), '', array('filter' => $note_list['filter'], 'page_count' => $note_list['page_count']));
}

if ($_REQUEST['act'] == 'add') {
	$smarty->assign('action_link', array('text' => $_LANG['alidayu_list'], 'href' => 'huyi_configure.php?act=list'));
	$smarty->assign('ur_here', $_LANG['huyi_configure']);
	$smarty->assign('form_act', 'insert');
	$smarty->assign('send_time', $send_time);
	assign_query_info();
	$smarty->display('huyi_configure_info.dwt');
}

if ($_REQUEST['act'] == 'set_update') {
	$other['value'] = !empty($_REQUEST['alayu_type']) ? $_REQUEST['alayu_type'] : 0;
	$db->autoExecute($ecs->table('shop_config'), $other, 'UPDATE', 'code = \'alidayu_type\'');
	$href = 'huyi_configure.php?act=list';
	clear_all_files();
	load_config();
	$link[] = array('text' => $_LANG['go_back'], 'href' => $href);
	sys_msg('', 0, $link);
}

if ($_REQUEST['act'] == 'edit') {
	$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
	$smarty->assign('action_link', array('text' => $_LANG['alidayu_list'], 'href' => 'huyi_configure.php?act=list'));
	$date = array('*');
	$where = 'id = \'' . $id . '\'';
	$note_info = get_table_date('alidayu_configure', $where, $date);
	$smarty->assign('note', $note_info);
	$smarty->assign('send_time', $send_time);
	$smarty->assign('ur_here', $_LANG['huyi_configure']);
	$smarty->assign('form_act', 'update');
	assign_query_info();
	$smarty->display('huyi_configure_info.dwt');
}
else {
	if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
		$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
		$signature = empty($_POST['signature']) ? 0 : intval($_POST['signature']);
		$other['temp_content'] = empty($_POST['temp_content']) ? '' : trim($_POST['temp_content']);
		$other['send_time'] = empty($_POST['send_time']) ? '' : trim($_POST['send_time']);
		$other['add_time'] = gmtime();

		if ($id) {
			$db->autoExecute($ecs->table('alidayu_configure'), $other, 'UPDATE', 'id = \'' . $id . '\'');
			$href = 'huyi_configure.php?act=edit&id=' . $id;
			$lang_name = $_LANG['edit_success'];
		}
		else {
			$db->autoExecute($ecs->table('alidayu_configure'), $other);
			$href = 'huyi_configure.php?act=list';
			$lang_name = $_LANG['add_success'];
		}

		$link[] = array('text' => $_LANG['go_back'], 'href' => $href);
		sys_msg(sprintf($lang_name, htmlspecialchars(stripslashes($other['temp_id']))), 0, $link);
	}
	else if ($_REQUEST['act'] == 'remove') {
		$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
		$sql = 'SELECT temp_id FROM ' . $GLOBALS['ecs']->table('alidayu_configure') . (' WHERE id = \'' . $id . '\'');
		$temp_id = $GLOBALS['db']->getOne($sql);
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('alidayu_configure') . (' WHERE id = \'' . $id . '\'');
		$GLOBALS['db']->query($sql);
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'huyi_configure.php?act=list');
		sys_msg(sprintf($_LANG['remove_success'], $temp_id), 0, $link);
	}
}

if ($_REQUEST['act'] == 'loat_template') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$tpl = !empty($_REQUEST['tpl']) ? trim($_REQUEST['tpl']) : '';

	if ($id) {
		$dayu = get_table_date('alidayu_configure', 'id=\'' . $id . '\'', array('temp_content'));
		$content = $dayu['temp_content'];
	}
	else {
		$content = $template[$tpl];
	}

	$result['content'] = $content;
	$result['tpl'] = $tpl;
	$json = new JSON();
	exit($json->encode($result));
}

if ($_REQUEST['act'] == 'sms_template') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$res = array('error' => 0, 'message' => '', 'content' => '');
	$set_sign = isset($_REQUEST['set_sign']) && !empty($_REQUEST['set_sign']) ? trim($_REQUEST['set_sign']) : '';
	$temp_id = isset($_REQUEST['temp_id']) && !empty($_REQUEST['temp_id']) ? trim($_REQUEST['temp_id']) : '';
	$temp_content = isset($_REQUEST['temp_content']) && !empty($_REQUEST['temp_content']) ? trim($_REQUEST['temp_content']) : '';
	$send_time = isset($_REQUEST['send_time']) && !empty($_REQUEST['send_time']) ? trim($_REQUEST['send_time']) : '';
	$sms_type = isset($_REQUEST['sms_type']) && !empty($_REQUEST['sms_type']) ? intval($_REQUEST['sms_type']) : 0;

	if ($GLOBALS['_CFG']['sms_shop_mobile']) {
		$smsParams = get_test_sms_params($send_time, $test);

		if ($sms_type == 1) {
			$data = array('set_sign' => $set_sign, 'temp_id' => $temp_id, 'temp_content' => $temp_content);
			$result = sms_ali($smsParams, $send_time, $data);
			$resp = $GLOBALS['ecs']->ali_yu($result);
			if (isset($resp->code) && $resp->code == 15) {
				$res['error'] = 2;
			}
		}
		else {
			$huyi = huyi_sms($smsParams, $send_time, $temp_content);

			if ($huyi === false) {
				$res['error'] = 2;
			}
		}
	}
	else {
		$res['error'] = 1;
	}

	$res['set_sign'] = $set_sign;
	$res['temp_id'] = $temp_id;
	$res['temp_content'] = $temp_content;
	$res['send_time'] = $send_time;
	$json = new JSON();
	exit($json->encode($res));
}

?>
