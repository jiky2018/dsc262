<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_pzd_list()
{
	$result = get_filter();

	if ($result === false) {
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('seller_grade') . ' WHERE is_open = 1';
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT * FROM' . $GLOBALS['ecs']->table('seller_grade') . ' WHERE is_open = 1  ORDER BY id ASC LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $k => $v) {
		if ($v['entry_criteria']) {
			$entry_criteria = unserialize($v['entry_criteria']);
			$criteria = '';

			foreach ($entry_criteria as $key => $val) {
				$sql = 'SELECT criteria_name FROM' . $GLOBALS['ecs']->table('entry_criteria') . ' WHERE id = \'' . $val . '\'';
				$criteria_name = $GLOBALS['db']->getOne($sql);

				if ($criteria_name) {
					$entry_criteria[$key] = $criteria_name;
				}
			}

			$row[$k]['entry_criteria'] = implode(' , ', $entry_criteria);
		}
	}

	$arr = array('pzd_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_entry_criteria($entry_criteria = '')
{
	$entry_criteria = unserialize($entry_criteria);
	$rel = '';

	if (!empty($entry_criteria)) {
		$sql = ' SELECT id,criteria_name FROM' . $GLOBALS['ecs']->table('entry_criteria') . ' WHERE id ' . db_create_in($entry_criteria);
		$rel = $GLOBALS['db']->getAll($sql);

		foreach ($rel as $k => $v) {
			$child = $GLOBALS['db']->getAll(' SELECT * FROM' . $GLOBALS['ecs']->table('entry_criteria') . ' WHERE parent_id = \'' . $v['id'] . '\'');

			foreach ($child as $key => $val) {
				if (($val['type'] == 'select') && ($val['option_value'] != '')) {
					$child[$key]['option_value'] = explode(',', $val['option_value']);
				}

				$rel['count_charge'] += $val['charge'];
			}

			$rel[$k]['child'] = $child;
		}
	}

	return $rel;
}

function upload_apply_file($image_files = array(), $file_id = array(), $url = array())
{
	foreach ($file_id as $v) {
		$flag = false;

		if (isset($image_files['error'])) {
			if ($image_files['error'][$v] == 0) {
				$flag = true;
			}
		}
		else {
			if (($image_files['tmp_name'][$v] != 'none') && $image_files['tmp_name'][$v]) {
				$flag = true;
			}
		}

		if ($flag) {
			$upload = array('name' => $image_files['name'][$v], 'type' => $image_files['type'][$v], 'tmp_name' => $image_files['tmp_name'][$v], 'size' => $image_files['size'][$v]);

			if (isset($image_files['error'])) {
				$upload['error'] = $image_files['error'][$v];
			}

			$img_original = $GLOBALS['image']->upload_image($upload);

			if ($img_original === false) {
				sys_msg($GLOBALS['image']->error_msg(), 1, array(), false);
			}

			$img_url[$v] = $img_original;

			if (!empty($url[$v])) {
				@unlink(ROOT_PATH . $url[$v]);
			}
		}
	}

	if (!empty($img_url)) {
		return $img_url;
	}
	else {
		return false;
	}
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
include_once ROOT_PATH . '/includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);
require ROOT_PATH . 'includes/lib_order.php';
include_once ROOT_PATH . 'includes/lib_payment.php';
include_once ROOT_PATH . 'includes/lib_clips.php';
$exc = new exchange($ecs->table('seller_grade'), $db, 'id', 'grade_name');
$adminru = get_admin_ru_id();
get_invalid_apply();

if ($_REQUEST['act'] == 'list') {
	admin_priv('seller_store_other');
	$smarty->assign('ur_here', $_LANG['09_merchants_upgrade']);

	if (0 < $adminru['ru_id']) {
		$smarty->assign('action_link', array('text' => $_LANG['seller_upgrade_list'], 'href' => 'seller_apply.php?act=list&ru_id=' . $adminru['ru_id']));
	}

	$seller_grader = get_seller_grade($adminru['ru_id']);
	$smarty->assign('grade_id', $seller_grader['grade_id']);
	$seller_garde = get_pzd_list();
	$smarty->assign('garde_list', $seller_garde['pzd_list']);
	$smarty->assign('filter', $seller_garde['filter']);
	$smarty->assign('record_count', $seller_garde['record_count']);
	$smarty->assign('page_count', $seller_garde['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->display('merchants_upgrade.htm');
}
else if ($_REQUEST['act'] == 'query') {
	admin_priv('seller_store_other');
	$seller_grader = get_seller_grade($adminru['ru_id']);
	$smarty->assign('grade_id', $seller_grader['grade_id']);
	$seller_garde = get_pzd_list();
	$smarty->assign('garde_list', $seller_garde['pzd_list']);
	$smarty->assign('filter', $seller_garde['filter']);
	$smarty->assign('record_count', $seller_garde['record_count']);
	$smarty->assign('page_count', $seller_garde['page_count']);
	make_json_result($smarty->fetch('merchants_upgrade.htm'), '', array('filter' => $seller_garde['filter'], 'page_count' => $seller_garde['page_count']));
}
else {
	if (($_REQUEST['act'] == 'application_grade') || ($_REQUEST['act'] == 'edit')) {
		admin_priv('seller_store_other');
		$smarty->assign('ur_here', $_LANG['application_grade']);
		$smarty->assign('action_link', array('text' => $_LANG['09_merchants_upgrade'], 'href' => 'merchants_upgrade.php?act=list'));
		$grade_id = (!empty($_REQUEST['grade_id']) ? intval($_REQUEST['grade_id']) : 0);
		$smarty->assign('grade_id', $grade_id);
		$smarty->assign('act', $_REQUEST['act']);

		if ($_REQUEST['act'] == 'edit') {
			$apply_id = (!empty($_REQUEST['apply_id']) ? intval($_REQUEST['apply_id']) : 0);
			$seller_apply_info = $db->getRow('SELECT * FROM' . $ecs->table('seller_apply_info') . ' WHERE apply_id = \'' . $apply_id . '\' LIMIT 1');
			$apply_criteria = unserialize($seller_apply_info['entry_criteria']);
			if ((0 < $seller_apply_info['pay_id']) && ($seller_apply_info['is_paid'] == 0) && ($seller_apply_info['pay_status'] == 0)) {
				include_once ROOT_PATH . 'includes/lib_payment.php';
				include_once ROOT_PATH . 'includes/lib_clips.php';
				$payment_info = array();
				$payment_info = payment_info($seller_apply_info['pay_id']);

				if ($payment_info === false) {
					$seller_apply_info['pay_online'] = '';
				}
				else if (substr($payment_info['pay_code'], 0, 4) == 'pay_') {
					$seller_apply_info['pay_online'] = '';
				}
				else {
					$payment = unserialize_config($payment_info['pay_config']);
					$apply['log_id'] = get_paylog_id($seller_apply_info['allpy_id'], $pay_type = PAY_APPLYGRADE);
					$amount = $seller_apply_info['total_amount'];
					$apply['order_sn'] = $seller_apply_info['apply_sn'];
					$apply['user_id'] = $seller_apply_info['ru_id'];
					$apply['surplus_amount'] = $amount;
					$payment_info['pay_fee'] = pay_fee($pay_id, $apply['surplus_amount'], 0);
					$apply['order_amount'] = $amount + $payment_info['pay_fee'];
					include_once ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php';
					$pay_obj = new $payment_info['pay_code']();
					$seller_apply_info['pay_online'] = $pay_obj->get_code($apply, $payment);
				}
			}

			$smarty->assign('apply_criteria', $apply_criteria);
			$smarty->assign('seller_apply_info', $seller_apply_info);
		}
		else {
			$sql = 'SELECT apply_id FROM ' . $ecs->table('seller_apply_info') . ' WHERE ru_id = \'' . $adminru['ru_id'] . '\' AND apply_status != 2 AND is_paid = 0 LIMIT 1';

			if ($db->getRow($sql)) {
				sys_msg($_LANG['invalid_apply']);
			}
		}

		$seller_grade = get_seller_grade($adminru['ru_id']);

		if ($seller_grade) {
			$seller_grade['end_time'] = (date('Y', $seller_grade['add_time']) + $seller_grade['year_num']) . '-' . date('m-d H:i:s', $seller_grade['add_time']);
			$seller_grade['addtime'] = date('Y-m-d H:i:s', $seller_grade['add_time']);

			if (0 < $seller_grade['amount']) {
				$rest = (gmtime() - $seller_grade['add_time']) / (strtotime($seller_grade['end_time']) - $seller_grade['add_time']);
				$seller_grade['refund_price'] = round($seller_grade['amount'] - ($seller_grade['amount'] * $rest), 2);
			}

			$smarty->assign('seller_grade', $seller_grade);
		}

		$entry_criteria = $db->getOne('SELECT entry_criteria FROM ' . $ecs->table('seller_grade') . ' WHERE id = \'' . $grade_id . '\'');
		$entry_criteriat_info = get_entry_criteria($entry_criteria);
		$smarty->assign('entry_criteriat_info', $entry_criteriat_info);
		$pay = available_payment_list(0);
		$smarty->assign('pay', $pay);
		$smarty->display('merchants_application_grade.htm');
	}
	else {
		if (($_REQUEST['act'] == 'insert_submit') || ($_REQUEST['act'] == 'update_submit')) {
			admin_priv('seller_store_other');
			$grade_id = (!empty($_REQUEST['grade_id']) ? intval($_REQUEST['grade_id']) : 0);
			$pay_id = (!empty($_REQUEST['pay_id']) ? intval($_REQUEST['pay_id']) : 0);
			$entry_criteria = (!empty($_REQUEST['value']) ? $_REQUEST['value'] : array());
			$file_id = (!empty($_REQUEST['file_id']) ? $_REQUEST['file_id'] : array());
			$fee_num = (!empty($_REQUEST['fee_num']) ? intval($_REQUEST['fee_num']) : 1);
			$all_count_charge = (!empty($_REQUEST['all_count_charge']) ? round($_REQUEST['all_count_charge'], 2) : 0);
			$refund_price = (!empty($_REQUEST['refund_price']) ? $_REQUEST['refund_price'] : 0);
			$file_url = (!empty($_REQUEST['file_url']) ? $_REQUEST['file_url'] : array());
			$apply_info = array();
			$back_price = 0;
			$payable_amount = 0;

			if (0 < $refund_price) {
				if ($_CFG['apply_options'] == 1) {
					if ($all_count_charge < $refund_price) {
						$payable_amount = 0;
						$back_price = $refund_price - $all_count_charge;
					}
					else {
						$payable_amount = $all_count_charge - $refund_price;
					}
				}
				else if ($_CFG['apply_options'] == 2) {
					if ($all_count_charge < $refund_price) {
						$payable_amount = 0;
						$back_price = 0;
					}
					else {
						$payable_amount = $all_count_charge - $refund_price;
					}
				}
			}
			else {
				$payable_amount = $all_count_charge;
			}

			$payment_info = array();
			$payment_info = payment_info($pay_id);
			$payment_info['pay_fee'] = pay_fee($pay_id, $payable_amount, 0);
			$apply_info['order_amount'] = $payable_amount + $payment_info['pay_fee'];
			$php_maxsize = ini_get('upload_max_filesize');
			$htm_maxsize = '2M';

			if ($_FILES['value']) {
				foreach ($_FILES['value']['error'] as $key => $value) {
					if ($value == 0) {
						if (!$image->check_img_type($_FILES['value']['type'][$key])) {
							$result['error'] = '1';
							$result['massege'] = sprintf($_LANG['invalid_img_val'], $key + 1);
						}
						else {
							$goods_pre = 1;
						}
					}
					else if ($value == 1) {
						$result['error'] = '1';
						$result['massege'] = sprintf($_LANG['img_url_too_big'], $key + 1, $php_maxsize);
					}
					else if ($_FILES['img_url']['error'] == 2) {
						$result['error'] = '1';
						$result['massege'] = sprintf($_LANG['img_url_too_big'], $key + 1, $htm_maxsize);
					}
				}

				if ($goods_pre == 1) {
					$res = upload_apply_file($_FILES['value'], $file_id, $file_url);

					if ($res != false) {
						$img_url = $res;
					}
				}
			}

			if ($img_url) {
				$valus = serialize($entry_criteria + $img_url);
			}
			else {
				$valus = serialize($entry_criteria);
			}

			if ($_REQUEST['act'] == 'insert_submit') {
				$apply_sn = get_order_sn();
				$time = gmtime();
				$key = '(`ru_id`,`grade_id`,`apply_sn`,`total_amount`,`pay_fee`,`fee_num`,`entry_criteria`,`add_time`,`pay_id`,`refund_price`,`back_price`,`payable_amount`)';
				$value = '(\'' . $adminru['ru_id'] . '\',\'' . $grade_id . '\',\'' . $apply_sn . '\',\'' . $all_count_charge . '\',\'' . $payment_info['pay_fee'] . '\',\'' . $fee_num . '\',\'' . $valus . '\',\'' . $time . '\',\'' . $pay_id . '\',\'' . $refund_price . '\',\'' . $back_price . '\',\'' . $payable_amount . '\')';
				$sql = 'INSERT INTO' . $ecs->table('seller_apply_info') . $key . ' VALUES' . $value;
				$db->query($sql);
				$apply_id = $db->insert_id();
				$apply_info['log_id'] = insert_pay_log($apply_id, $apply_info['order_amount'], $type = PAY_APPLYGRADE, 0);
			}
			else {
				$apply_sn = (!empty($_REQUEST['apply_sn']) ? $_REQUEST['apply_sn'] : 0);
				$apply_id = (!empty($_REQUEST['apply_id']) ? intval($_REQUEST['apply_id']) : 0);
				$sql = 'UPDATE' . $ecs->table('seller_apply_info') . ' SET payable_amount = \'' . $payable_amount . '\', back_price = \'' . $back_price . '\', total_amount = \'' . $all_count_charge . '\',pay_fee=\'' . $payment_info['pay_fee'] . '\',fee_num = \'' . $fee_num . '\',entry_criteria=\'' . $valus . '\',pay_id=\'' . $pay_id . '\' WHERE apply_id = \'' . $apply_id . '\' AND apply_sn = \'' . $apply_sn . '\'';
				$db->query($sql);
				$apply_info['log_id'] = get_paylog_id($apply_id, $pay_type = PAY_APPLYGRADE);
			}

			if ((0 < $pay_id) && (0 < $payable_amount)) {
				$payment = unserialize_config($payment_info['pay_config']);
				$apply_info['order_sn'] = $apply_sn;
				$apply_info['user_id'] = $adminru['ru_id'];
				$apply_info['surplus_amount'] = $payable_amount;
				include_once ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php';
				$pay_obj = new $payment_info['pay_code']();
				$payment_info['pay_button'] = $pay_obj->get_code($apply_info, $payment);
				$smarty->assign('payment', $payment_info);
				$smarty->assign('pay_fee', price_format($payment_info['pay_fee'], false));
				$smarty->assign('amount', price_format($payable_amount, false));
				$smarty->assign('order', $apply_info);
				$smarty->display('seller_done.htm');
			}
			else {
				sys_msg($_LANG['success']);
			}
		}
	}
}

?>
