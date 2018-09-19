<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_site_root_url()
{
	return 'http://' . $_SERVER['HTTP_HOST'] . str_replace('/' . SELLER_PATH . '/order.php', '', PHP_SELF);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ($_REQUEST['act'] == 'print_batch') {
	$checkboxes = (!empty($_REQUEST['checkboxes']) ? trim($_REQUEST['checkboxes']) : '');

	if (get_print_type($adminru['ru_id'])) {
		$url = 'tp_api.php?act=kdniao_print&order_sn=' . $checkboxes;
		ecs_header('Location: ' . $url . "\n");
		exit();
	}

	if ($checkboxes) {
		$smarty->assign('checkboxes', $checkboxes);
		$smarty->display('print_batch.dwt');
	}
}
else if ($_REQUEST['act'] == 'ajax_print') {
	require ROOT_PATH . '/includes/cls_json.php';
	require_once ROOT_PATH . 'includes/lib_order.php';
	require ROOT_PATH . '/includes/lib_visual.php';
	$data = array('error' => 0, 'message' => '', 'content' => '');
	$adminru = get_admin_ru_id();
	$order_sn = (isset($_REQUEST['order_sn']) ? trim($_REQUEST['order_sn']) : '');
	$data['order_sn'] = $order_sn;

	if ($order_sn) {
		$order_sn = trim($_REQUEST['order_sn']);
		$order = order_info(0, $order_sn);
		$smarty->assign('order', $order);

		if (empty($order)) {
			$data['error'] = 1;
			$data['message'] = '打印订单不存在，请查证';
			exit(json_encode($data));
		}
		else {
			$order['invoice_no'] = ($order['shipping_status'] == SS_UNSHIPPED) || ($order['shipping_status'] == SS_PREPARING) ? $_LANG['ss'][SS_UNSHIPPED] : $order['invoice_no'];
			$sql = 'select shop_name,country,province,city,district,shop_address,kf_tel from ' . $ecs->table('seller_shopinfo') . ' where ru_id=\'' . $adminru['ru_id'] . '\'';
			$store = $db->getRow($sql);
			$store['shop_name'] = get_shop_name($order['ru_id'], 1);
			$region_array = array();
			$region = $db->getAll('SELECT region_id, region_name FROM ' . $ecs->table('region'));

			if (!empty($region)) {
				foreach ($region as $region_data) {
					$region_array[$region_data['region_id']] = $region_data['region_name'];
				}
			}

			$smarty->assign('shop_name', $store['shop_name']);
			$smarty->assign('order_id', $order_id);
			$smarty->assign('province', $region_array[$store['province']]);
			$smarty->assign('city', $region_array[$store['city']]);
			$smarty->assign('district', $region_array[$store['district']]);
			$smarty->assign('shop_address', $store['shop_address']);
			$smarty->assign('service_phone', $store['kf_tel']);
			$shipping = $db->getRow('SELECT * FROM ' . $ecs->table('shipping_tpl') . ' WHERE shipping_id = \'' . $order['shipping_id'] . '\' and ru_id=\'' . $order['ru_id'] . '\'');

			if ($shipping['print_model'] == 2) {
				$shipping['print_bg'] = empty($shipping['print_bg']) ? '' : get_site_root_url() . $shipping['print_bg'];

				if (!empty($shipping['print_bg'])) {
					$_size = @getimagesize($shipping['print_bg']);

					if ($_size != false) {
						$shipping['print_bg_size'] = array('width' => $_size[0], 'height' => $_size[1]);
					}
				}

				if (empty($shipping['print_bg_size'])) {
					$shipping['print_bg_size'] = array('width' => '1024', 'height' => '600');
				}

				$lable_box = array();
				$lable_box['t_shop_country'] = $region_array[$store['country']];
				$lable_box['t_shop_city'] = $region_array[$store['city']];
				$lable_box['t_shop_province'] = $region_array[$store['province']];
				$sql = 'select og.ru_id from ' . $GLOBALS['ecs']->table('order_info') . ' as oi ' . ',' . $GLOBALS['ecs']->table('order_goods') . ' as og ' . ' where oi.order_id = og.order_id and oi.order_id = \'' . $order['order_id'] . '\' group by oi.order_id';
				$ru_id = $GLOBALS['db']->getOne($sql);

				if (0 < $ru_id) {
					$sql = 'select shoprz_brandName, shopNameSuffix from ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' where user_id = \'' . $ru_id . '\'';
					$shop_info = $GLOBALS['db']->getRow($sql);
					$lable_box['t_shop_name'] = $shop_info['shoprz_brandName'] . $shop_info['shopNameSuffix'];
				}
				else {
					$lable_box['t_shop_name'] = $_CFG['shop_name'];
				}

				$lable_box['t_shop_district'] = $region_array[$store['district']];
				$lable_box['t_shop_tel'] = $store['kf_tel'];
				$lable_box['t_shop_address'] = $store['shop_address'];
				$lable_box['t_customer_country'] = $region_array[$order['country']];
				$lable_box['t_customer_province'] = $region_array[$order['province']];
				$lable_box['t_customer_city'] = $region_array[$order['city']];
				$lable_box['t_customer_district'] = $region_array[$order['district']];
				$lable_box['t_customer_tel'] = $order['tel'];
				$lable_box['t_customer_mobel'] = $order['mobile'];
				$lable_box['t_customer_post'] = $order['zipcode'];
				$lable_box['t_customer_address'] = $order['address'];
				$lable_box['t_customer_name'] = $order['consignee'];
				$gmtime_utc_temp = gmtime();
				$lable_box['t_year'] = date('Y', $gmtime_utc_temp);
				$lable_box['t_months'] = date('m', $gmtime_utc_temp);
				$lable_box['t_day'] = date('d', $gmtime_utc_temp);
				$lable_box['t_order_no'] = $order['order_sn'];
				$lable_box['t_order_postscript'] = $order['postscript'];
				$lable_box['t_order_best_time'] = $order['best_time'];
				$lable_box['t_pigeon'] = '√';
				$lable_box['t_custom_content'] = '';
				$temp_config_lable = explode('||,||', $shipping['config_lable']);

				if (!is_array($temp_config_lable)) {
					$temp_config_lable[] = $shipping['config_lable'];
				}

				foreach ($temp_config_lable as $temp_key => $temp_lable) {
					$temp_info = explode(',', $temp_lable);

					if (is_array($temp_info)) {
						$temp_info[1] = $lable_box[$temp_info[0]];
					}

					$temp_config_lable[$temp_key] = implode(',', $temp_info);
				}

				$shipping['config_lable'] = implode('||,||', $temp_config_lable);
				$data['shipping'] = $shipping;
				$data['error'] = 0;
				$data['print_model'] = 2;
				exit(json_encode($data));
			}
			else if (!empty($shipping['shipping_print'])) {
				$data['error'] = 0;
				$simulation_print = 'simulation_print.html';
				$create_html = create_html($shipping['shipping_print'], $adminru['ru_id'], $simulation_print, '', 2);
				$dir = ROOT_PATH . 'data/' . $simulation_print;
				$data['content'] = $GLOBALS['smarty']->fetch($dir);
				exit(json_encode($data));
			}
			else {
				$shipping_code = $db->getOne('SELECT shipping_code FROM ' . $ecs->table('shipping') . ' WHERE shipping_id=\'' . $order['shipping_id'] . '\'');

				if ($order['referer'] == 'mobile') {
					$shipping_code = str_replace('ship_', '', $shipping_code);
					exit(json_encode($data));
				}

				if ($shipping_code) {
					include_once ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php';
				}

				if (!empty($_LANG['shipping_print'])) {
					$data['error'] = 0;
					$simulation_print = 'simulation_print.html';
					$create_html = create_html($_LANG['shipping_print'], $adminru['ru_id'], $simulation_print, '', 2);
					$dir = ROOT_PATH . 'data/' . $simulation_print;
					$data['content'] = $GLOBALS['smarty']->fetch($dir);
					exit(json_encode($data));
				}
				else {
					$data['error'] = 1;
					$data['message'] = '很抱歉,目前您还没有设置打印快递单模板.不能进行打印';
					exit(json_encode($data));
				}
			}
		}
	}
	else {
		$data['error'] = 1;
		$data['message'] = '请选择打印订单';
		exit(json_encode($data));
	}
}

?>
