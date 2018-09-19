<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_regions_log($type = 0, $parent = 0)
{
	$sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE region_type = \'' . $type . '\' AND parent_id = \'' . $parent . '\'';
	return $GLOBALS['db']->GetAll($sql);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . 'includes/lib_order.php';

if (!empty($_SESSION['user_id'])) {
	$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
}
else {
	$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
}

$smarty->assign('user_id', $_SESSION['user_id']);

if ($_REQUEST['step'] == 'edit_Consignee') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('message' => '', 'result' => '', 'qty' => 1);
	$address_id = (isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0);

	if ($address_id == 0) {
		$consignee['country'] = 1;
		$consignee['province'] = 0;
		$consignee['city'] = 0;
		$smarty->assign('province_list', $province_list);
	}

	$consignee = get_update_flow_Consignee($address_id);
	$smarty->assign('consignee', $consignee);
	$smarty->assign('country_list', get_regions());
	$smarty->assign('please_select', $_LANG['please_select']);
	$province_list = get_regions_log(1, $consignee['country']);
	$city_list = get_regions_log(2, $consignee['province']);
	$district_list = get_regions_log(3, $consignee['city']);
	$smarty->assign('province_list', $province_list);
	$smarty->assign('city_list', $city_list);
	$smarty->assign('district_list', $district_list);
	get_goods_flow_type($_SESSION['cart_value']);

	if ($_SESSION['user_id'] <= 0) {
		$result['error'] = 2;
		$result['message'] = '您尚未登录，请登录您的账号！';
	}
	else {
		$result['error'] = 0;
		$result['content'] = $smarty->fetch('library/consignee_new.lbi');
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'insert_Consignee') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$result = array('message' => '', 'result' => '', 'error' => 0);
	$_REQUEST['csg'] = isset($_REQUEST['csg']) ? json_str_iconv($_REQUEST['csg']) : '';
	$csg = $json->decode($_REQUEST['csg']);
	$consignee = array('address_id' => empty($csg->address_id) ? 0 : intval($csg->address_id), 'consignee' => empty($csg->consignee) ? '' : compile_str(trim($csg->consignee)), 'country' => empty($csg->country) ? '' : intval($csg->country), 'province' => empty($csg->province) ? '' : intval($csg->province), 'city' => empty($csg->city) ? '' : intval($csg->city), 'district' => empty($csg->district) ? '' : intval($csg->district), 'email' => empty($csg->email) ? '' : compile_str($csg->email), 'address' => empty($csg->address) ? '' : compile_str($csg->address), 'zipcode' => empty($csg->zipcode) ? '' : compile_str(make_semiangle(trim($csg->zipcode))), 'tel' => empty($csg->tel) ? '' : compile_str(make_semiangle(trim($csg->tel))), 'mobile' => empty($csg->mobile) ? '' : compile_str(make_semiangle(trim($csg->mobile))), 'sign_building' => empty($csg->sign_building) ? '' : compile_str($csg->sign_building), 'best_time' => empty($csg->best_time) ? '' : compile_str($csg->best_time));

	if ($result['error'] == 0) {
		if (0 < $_SESSION['user_id']) {
			include_once ROOT_PATH . 'includes/lib_transaction.php';

			if (0 < $consignee['address_id']) {
				$addressId = ' and address_id <> \'' . $consignee['address_id'] . '\' ';
			}

			$sql = 'select count(*) from ' . $ecs->table('user_address') . ' where consignee = \'' . $consignee['consignee'] . '\' AND user_id = \'' . $_SESSION['user_id'] . '\'' . $addressId;
			$row = $db->getOne($sql);

			if (0 < $row) {
				$result['error'] = 4;
				$result['message'] = $_LANG['Distribution_exists'];
			}
			else {
				$result['error'] = 0;
				$consignee['user_id'] = $_SESSION['user_id'];
				$saveConsignee = save_consignee($consignee, true);
				$sql = 'select address_id from ' . $GLOBALS['ecs']->table('users') . ' where user_id = \'' . $_SESSION['user_id'] . '\'';
				$user_address_id = $GLOBALS['db']->getOne($sql);

				if (0 < $user_address_id) {
					$consignee['address_id'] = $user_address_id;
				}

				$sql = 'select count(*) from ' . $GLOBALS['ecs']->table('user_address') . ' where user_id = \'' . $_SESSION['user_id'] . '\'';
				$count = $GLOBALS['db']->getOne($sql);

				if ($_CFG['auditStatus'] == 1) {
					if ($count <= $_CFG['auditCount']) {
						$result['message'] = '';
					}
					else if ($saveConsignee['update'] == false) {
						if (0 < $consignee['address_id']) {
							$result['message'] = $_LANG['edit_success_one'];
						}
						else {
							$result['message'] = $_LANG['add_success_one'];
						}
					}
					else {
						$result['message'] = '';
					}
				}
				else if (0 < $consignee['address_id']) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET address_id = \'' . $consignee['address_id'] . '\' ' . ' WHERE user_id = \'' . $consignee['user_id'] . '\'';
					$GLOBALS['db']->query($sql);
					$_SESSION['flow_consignee'] = $consignee;
					$result['message'] = $_LANG['edit_success_two'];
				}
				else {
					$result['message'] = $_LANG['add_success_two'];
				}
			}

			$user_address = get_order_user_address_list($_SESSION['user_id']);
			$smarty->assign('user_address', $user_address);
			$smarty->assign('consignee', $consignee);
			$result['content'] = $smarty->fetch('library/consignee_flow.lbi');
		}
		else {
			$result['error'] = 2;
			$result['message'] = $_LANG['lang_crowd_not_login'];
		}
	}

	exit($json->encode($result));
}
else if ($_REQUEST['step'] == 'delete_Consignee') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('message' => '', 'result' => '', 'qty' => 1);
	$result['error'] = 0;
	$address_id = (isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0);
	$sql = 'delete from ' . $ecs->table('user_address') . ' where address_id = \'' . $address_id . '\'';
	$db->query($sql);
	$consignee = $_SESSION['flow_consignee'];
	$smarty->assign('consignee', $consignee);
	$user_address = get_order_user_address_list($_SESSION['user_id']);
	$smarty->assign('user_address', $user_address);
	$result['content'] = $smarty->fetch('library/consignee_flow.lbi');
	exit($json->encode($result));
}

?>
