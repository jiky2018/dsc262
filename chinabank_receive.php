<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . 'includes/lib_payment.php';
require ROOT_PATH . 'includes/lib_order.php';
$key = '';
$payment = $db->getOne('SELECT pay_config FROM ' . $ecs->table('payment') . ' WHERE pay_code = \'chinabank\' AND enabled = 1');

if (!empty($payment)) {
	$payment = unserialize($payment);

	foreach ($payment as $k => $v) {
		if ($v['name'] == 'chinabank_key') {
			$key = $v['value'];
		}
	}
}
else {
	exit('error');
}

$v_oid = trim($_POST['v_oid']);
$v_pmode = trim($_POST['v_pmode']);
$v_pstatus = trim($_POST['v_pstatus']);
$v_pstring = trim($_POST['v_pstring']);
$v_amount = trim($_POST['v_amount']);
$v_moneytype = trim($_POST['v_moneytype']);
$remark1 = trim($_POST['remark1']);
$remark2 = trim($_POST['remark2']);
$v_md5str = trim($_POST['v_md5str']);
$md5string = strtoupper(md5($v_oid . $v_pstatus . $v_amount . $v_moneytype . $key));

if ($v_md5str == $md5string) {
	if ($v_pstatus == '20') {
		if ($remark1 == 'voucher') {
			$v_oid = get_order_id_by_sn($v_oid, 'true');
		}
		else {
			$v_oid = get_order_id_by_sn($v_oid);
		}

		order_paid($v_oid);
	}

	echo 'ok';
}
else {
	echo 'error';
}

?>
