<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/cls_sms.php';
$action = (isset($_REQUEST['act']) ? $_REQUEST['act'] : 'display_my_info');
$sms = new sms();

switch ($action) {
case 'display_send_ui':
	admin_priv('sms_send');
	$smarty->display('sms_register_ui.htm');
	break;

case 'send_sms':
	$send_num = (isset($_POST['send_num']) ? $_POST['send_num'] : '');

	if (isset($send_num)) {
		$phone = $send_num . ',';
	}

	$send_rank = (isset($_POST['send_rank']) ? $_POST['send_rank'] : 0);

	if ($send_rank != 0) {
		$rank_array = explode('_', $send_rank);

		if ($rank_array[0] == 1) {
			$sql = 'SELECT mobile_phone FROM ' . $ecs->table('users') . 'WHERE mobile_phone <>\'\' ';
			$row = $db->query($sql);

			while ($rank_rs = $db->fetch_array($row)) {
				$value[] = $rank_rs['mobile_phone'];
			}
		}
		else {
			$rank_sql = 'SELECT * FROM ' . $ecs->table('user_rank') . ' WHERE rank_id = \'' . $rank_array[1] . '\'';
			$rank_row = $db->getRow($rank_sql);

			if ($rank_row['special_rank'] == 1) {
				$sql = 'SELECT mobile_phone FROM ' . $ecs->table('users') . ' WHERE mobile_phone <>\'\' AND user_rank = \'' . $rank_array[1] . '\'';
			}
			else {
				$sql = 'SELECT mobile_phone FROM ' . $ecs->table('users') . 'WHERE mobile_phone <>\'\' AND rank_points > ' . $rank_row['min_points'] . ' AND rank_points < ' . $rank_row['max_points'] . ' ';
			}

			$row = $db->query($sql);

			while ($rank_rs = $db->fetch_array($row)) {
				$value[] = $rank_rs['mobile_phone'];
			}
		}

		if (isset($value)) {
			$phone .= implode(',', $value);
		}
	}

	$msg = (isset($_POST['msg']) ? $_POST['msg'] : '');
	$send_date = (isset($_POST['send_date']) ? $_POST['send_date'] : '');
	$result = $sms->send($phone, $msg, $send_date, $send_num = 13);
	$link[] = array('text' => $_LANG['back'] . $_LANG['03_sms_send'], 'href' => 'sms.php?act=display_send_ui');

	if ($result === true) {
		sys_msg($_LANG['send_ok'], 0, $link);
	}
	else {
		@$error_detail = $_LANG['server_errors'][$sms->errors['server_errors']['error_no']] . $_LANG['api_errors']['send'][$sms->errors['api_errors']['error_no']];
		sys_msg($_LANG['send_error'] . $error_detail, 1, $link);
	}

	break;
}

?>
