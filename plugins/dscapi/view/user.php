<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
$user_id = isset($_REQUEST['user_id']) ? $base->get_intval($_REQUEST['user_id']) : -1;
$user_name = isset($_REQUEST['user_name']) ? $base->get_addslashes($_REQUEST['user_name']) : -1;

if (isset($_REQUEST['mobile'])) {
	$mobile = isset($_REQUEST['mobile']) ? $base->get_addslashes($_REQUEST['mobile']) : -1;
}
else if (isset($_REQUEST['mobile_phone'])) {
	$mobile = isset($_REQUEST['mobile_phone']) ? $base->get_addslashes($_REQUEST['mobile_phone']) : -1;
}

$rank_id = isset($_REQUEST['rank_id']) ? $base->get_intval($_REQUEST['rank_id']) : -1;
$address_id = isset($_REQUEST['address_id']) ? $base->get_intval($_REQUEST['address_id']) : -1;
$val = array('user_id' => $user_id, 'user_name' => $user_name, 'mobile' => $mobile, 'rank_id' => $rank_id, 'address_id' => $address_id, 'user_select' => $data, 'page_size' => $page_size, 'page' => $page, 'sort_by' => $sort_by, 'sort_order' => $sort_order, 'format' => $format);
$user = new \app\controller\user($val);

switch ($method) {
case 'dsc.user.list.get':
	$table = array('users' => 'users');
	$result = $user->get_user_list($table);
	exit($result);
	break;

case 'dsc.user.info.get':
	$table = array('users' => 'users');
	$result = $user->get_user_info($table);
	exit($result);
	break;

case 'dsc.user.insert.post':
	$table = array('users' => 'users');
	$result = $user->get_user_insert($table);
	exit($result);
	break;

case 'dsc.user.update.post':
	$table = array('users' => 'users');
	$result = $user->get_user_update($table);
	exit($result);
	break;

case 'dsc.user.del.get':
	$table = array('users' => 'users');
	$result = $user->get_user_delete($table);
	exit($result);
	break;

case 'dsc.user.rank.list.get':
	$table = array('rank' => 'user_rank');
	$result = $user->get_user_rank_list($table);
	exit($result);
	break;

case 'dsc.user.rank.info.get':
	$table = array('rank' => 'user_rank');
	$result = $user->get_user_rank_info($table);
	exit($result);
	break;

case 'dsc.user.rank.insert.post':
	$table = array('rank' => 'user_rank');
	$result = $user->get_user_rank_insert($table);
	exit($result);
	break;

case 'dsc.user.rank.update.post':
	$table = array('rank' => 'user_rank');
	$result = $user->get_user_rank_update($table);
	exit($result);
	break;

case 'dsc.user.rank.del.get':
	$table = array('rank' => 'user_rank');
	$result = $user->get_user_rank_delete($table);
	exit($result);
	break;

case 'dsc.user.address.list.get':
	$table = array('address' => 'user_address');
	$result = $user->get_user_address_list($table);
	exit($result);
	break;

case 'dsc.user.address.info.get':
	$table = array('address' => 'user_address');
	$result = $user->get_user_address_info($table);
	exit($result);
	break;

case 'dsc.user.address.insert.post':
	$table = array('address' => 'user_address');
	$result = $user->get_user_address_insert($table);
	exit($result);
	break;

case 'dsc.user.address.update.post':
	$table = array('rank' => 'user_rank');
	$result = $user->get_user_address_update($table);
	exit($result);
	break;

case 'dsc.user.address.del.get':
	$table = array('address' => 'user_address');
	$result = $user->get_user_address_delete($table);
	exit($result);
	break;

default:
	echo '非法接口连接';
	break;
}

?>
