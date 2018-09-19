<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

$cron_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/cron/auto_sms.php';

if (file_exists($cron_lang)) {
	global $_LANG;
	include_once $cron_lang;
}

if (isset($set_modules) && ($set_modules == true)) {
	$i = (isset($modules) ? count($modules) : 0);
	$modules[$i]['code'] = basename(__FILE__, '.php');
	$modules[$i]['desc'] = 'auto_sms_desc';
	$modules[$i]['author'] = 'ECMOBAN TEAM';
	$modules[$i]['website'] = 'http://www.ecmoban.com';
	$modules[$i]['version'] = '1.0.0';
	$modules[$i]['config'] = array(
	array('name' => 'auto_sms_count', 'type' => 'select', 'value' => '10')
	);
	return NULL;
}

$where = ' where 1 ';
$sort = ' order by item_id DESC ';
$limit = (!empty($cron['auto_sms_count']) ? $cron['auto_sms_count'] : 5);
$user_id = (empty($_SESSION['user_id']) ? 0 : $_SESSION['user_id']);
$adminru = get_admin_ru_id();

if (!empty($user_id)) {
	$where .= ' and user_id= ' . $user_id;
}

if ((0 < $user_id) || $adminru) {
	$sql = ' select * from ' . $GLOBALS['ecs']->table('auto_sms') . $where . $sort . ' LIMIT ' . $limit;
	$item_list = $GLOBALS['db']->getAll($sql);

	if (0 < count($item_list)) {
		foreach ($item_list as $key => $val) {
			$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $val['order_id'] . '\' ';
			$row = $GLOBALS['db']->getRow($sql);

			if ($val['ru_id'] == 0) {
				$sms_shop_mobile = $_CFG['sms_shop_mobile'];
				$service_email = $_CFG['service_email'];
				$shop_name = $GLOBALS['_CFG']['shop_name'];
			}
			else {
				$sql = 'SELECT mobile FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = \'' . $val['ru_id'] . '\'';
				$sms_shop_mobile = $GLOBALS['db']->getOne($sql);
				$sql = 'SELECT seller_email FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = \'' . $val['ru_id'] . '\'';
				$service_email = $GLOBALS['db']->getOne($sql);
				$seller_name = get_shop_name($val['ru_id'], 1);
				$shop_name = $seller_name;
			}

			if (($_CFG['sms_order_placed'] == '1') && ($sms_shop_mobile != '') && ($val['item_type'] == 1)) {
				$order_region = get_flow_user_region($row['order_id']);
				$pt_smsParams = array('shop_name' => $shop_name, 'shopname' => $shop_name, 'order_sn' => $row['order_sn'], 'ordersn' => $row['order_sn'], 'consignee' => $row['consignee'], 'order_region' => $order_region, 'orderregion' => $order_region, 'address' => $row['address'], 'order_mobile' => $row['mobile'], 'ordermobile' => $row['mobile'], 'mobile_phone' => $sms_shop_mobile, 'mobilephone' => $sms_shop_mobile);

				if ($GLOBALS['_CFG']['sms_type'] == 0) {
					huyi_sms($smsParams, 'sms_order_placed');
				}
				else if (1 <= $GLOBALS['_CFG']['sms_type']) {
					$result = sms_ali($smsParams, 'sms_order_placed');
					$resp = $GLOBALS['ecs']->ali_yu($result);
				}
			}

			if (((($val['ru_id'] == 0) && ($_CFG['send_service_email'] == '1')) || ((0 < $val['ru_id']) && ($_CFG['seller_email'] == '1'))) && ($service_email != '') && ($val['item_type'] == 2)) {
				$sql = ' select * from ' . $GLOBALS['ecs']->table('order_goods') . ' where order_id=\'' . $val['order_id'] . '\' ';
				$cart_goods = $GLOBALS['db']->getAll($sql);
				$tpl = get_mail_template('remind_of_new_order');
				$smarty->assign('order', $row);
				$smarty->assign('goods_list', $cart_goods);
				$smarty->assign('shop_name', $_CFG['shop_name']);
				$smarty->assign('send_date', date($_CFG['time_format']));
				$content = $smarty->fetch('str:' . $tpl['template_content']);

				if (send_mail($_CFG['shop_name'], $service_email, $tpl['template_subject'], $content, $tpl['is_html'])) {
					$sql = ' delete from ' . $GLOBALS['ecs']->table('auto_sms') . ' where item_id=\'' . $val['item_id'] . '\' ';
					$GLOBALS['db']->query($sql);
				}
			}
		}
	}
}

?>
