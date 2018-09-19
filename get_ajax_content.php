<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

require ROOT_PATH . '/includes/lib_area.php';
require ROOT_PATH . '/includes/cls_json.php';
require ROOT_PATH . '/includes/lib_clips.php';
include_once ROOT_PATH . 'includes/lib_transaction.php';
require_once ROOT_PATH . 'includes/lib_ecmoban.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
assign_template();
$warehouse_other = array('province_id' => $province_id, 'city_id' => $city_id);
$warehouse_area_info = get_warehouse_area_info($warehouse_other);
$region_id = $warehouse_area_info['region_id'];
$area_id = $warehouse_area_info['area_id'];
$area_city = $warehouse_area_info['city_id'];
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : '';
$tpl = !empty($_REQUEST['tpl']) ? trim($_REQUEST['tpl']) : 1;
$smarty->assign('tpl', $tpl);

if ($act == 'get_content') {
	require_once ROOT_PATH . 'includes/lib_order.php';
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$json = new JSON();
	$data_type = !empty($_POST['data_type']) ? trim($_POST['data_type']) : '';

	switch ($data_type) {
	case 'cart_list':
		$cart_info = insert_cart_info(2);
		$result['cart_num'] = $cart_info['number'];
		$GLOBALS['smarty']->assign('cart_info', $cart_info);
		$result['content'] = $GLOBALS['smarty']->fetch('library/right_float_cart_info.lbi');
		break;

	case 'mpbtn_total':
		$user_info = get_user_default($_SESSION['user_id']);
		$bonus = get_user_bouns_new_list($_SESSION['user_id'], $page, 0, 'bouns_available_gotoPage', 0, 7);

		if ($user_info) {
			foreach ($bonus['available_list'] as $key => $val) {
				$bonus['available_list'][$key]['use_startdate'] = substr($val['use_startdate'], 0, 10);
				$bonus['available_list'][$key]['use_enddate'] = substr($val['use_enddate'], 0, 10);
			}

			$user_info['bouns_list'] = $bonus['available_list'];
			$user_info['bouns_num'] = count($bonus['available_list']);
		}

		$GLOBALS['smarty']->assign('user_info', $user_info);
		$result['content'] = $GLOBALS['smarty']->fetch('library/right_float_total_info.lbi');
		break;

	case 'mpbtn_history':
		$history_info = insert_history_info(10);
		$GLOBALS['smarty']->assign('history_info', $history_info);
		$result['content'] = $GLOBALS['smarty']->fetch('library/right_float_histroy_info.lbi');
		break;

	case 'mpbtn_collection':
		$operat = !empty($_POST['type']) ? trim($_POST['type']) : '';
		$collect_id = !empty($_POST['collection_id']) ? intval($_POST['collection_id']) : 0;
		if (!empty($operat) && $operat == 'del' && 0 < $collect_id) {
			$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('collect_goods') . (' WHERE rec_id=\'' . $collect_id . '\' AND user_id =\'') . $_SESSION['user_id'] . '\'');
		}

		$collection_goods = get_collection_goods($_SESSION['user_id'], 10, 1, 'collection_goods_gotoPage');
		$smarty->assign('goods_list', $collection_goods['goods_list']);
		$collection_store = get_collection_store($_SESSION['user_id'], 5, 1, 'collection_store_gotoPage');
		$smarty->assign('store_list', $collection_store['store_list']);
		$result['content'] = $GLOBALS['smarty']->fetch('library/right_float_collection_info.lbi');
		break;

	case 'mpbtn_order':
		$order_list = get_user_orders($_SESSION['user_id'], 5, 1, 0, '', '', 0, 5);
		$smarty->assign('order_list', $order_list['order_list']);
		$result['content'] = $GLOBALS['smarty']->fetch('library/right_float_order_info.lbi');
		break;

	case 'mpbtn_coupons':
		$goods_id = intval($_GET['goods_id']);
		$user_id = $_SESSION['user_id'];
		$sql = 'SELECT user_id FROM ' . $ecs->table('goods') . ' WHERE goods_id=' . $goods_id;
		$ru_id = $db->getOne($sql);
		$goods_coupons = get_goods_coupons_list($goods_id);

		if ($user_id) {
			$user_coupons = get_user_coupons_list($user_id);
		}
		else {
			$user_coupons = array();
		}

		foreach ($user_coupons as $k => $v) {
			$user_coupons[$k]['cou_start_time'] = local_date('Y-m-d', $v['cou_start_time']);
			$user_coupons[$k]['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
			$user_coupons[$k]['cou_type'] = $v['cou_type'] == 3 ? $_LANG['vouchers_all'] : ($v['cou_type'] == 4 ? $_LANG['vouchers_user'] : ($v['cou_type'] == 2 ? $_LANG['vouchers_shoping'] : ($v['cou_type'] == 1 ? $_LANG['vouchers_login'] : ($v['cou_type'] == 5 ? $_LANG['vouchers_free'] : $_LANG['unknown']))));
			if ((strpos(',' . $v['cou_goods'] . ',', ',' . $goods_id . ',') !== false || $v['cou_goods'] == 0) && $v['ru_id'] == $ru_id) {
				$user_coupons[$k]['keyong'] = 1;
			}

			if ($v['is_use'] == 1) {
				unset($user_coupons[$k]);
			}

			$arr[] = $user_coupons[$k]['cou_id'];
		}

		$arr = array_filter($arr);

		foreach ($goods_coupons as $k => $v) {
			if (!in_array($v['cou_id'], $arr)) {
				$v['cou_start_time'] = local_date('Y-m-d', $v['cou_start_time']);
				$v['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
				$kelingqu_coupons[] = $v;
			}
		}

		$smarty->assign('user_coupons', array_slice($user_coupons, 0, 4));
		$smarty->assign('kelingqu_coupons', array_slice($kelingqu_coupons, 0, 4));
		$result['content'] = $GLOBALS['smarty']->fetch('library/right_float_yhq_info.lbi');
		exit($json->encode($result));
		break;

	case 'mpbtn_yhq':
		$goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
		$ru_id = 0;

		if (0 < $goods_id) {
			$sql = 'SELECT user_id FROM ' . $ecs->table('goods') . ' WHERE goods_id=' . $goods_id;
			$ru_id = $db->getOne($sql);
		}

		$goods_coupons = get_coupons_type_info2('3,4');
		$user_id = $_SESSION['user_id'];

		if ($user_id) {
			$user_coupons = get_user_coupons_list($user_id);
		}
		else {
			$user_coupons = array();
		}

		foreach ($user_coupons as $k => $v) {
			$user_coupons[$k]['cou_type_name'] = $v['cou_type'];
			$user_coupons[$k]['cou_start_time'] = local_date('Y-m-d', $v['cou_start_time']);
			$user_coupons[$k]['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
			$user_coupons[$k]['cou_type'] = $v['cou_type'] == 3 ? $_LANG['vouchers_all'] : ($v['cou_type'] == 4 ? $_LANG['vouchers_user'] : ($v['cou_type'] == 2 ? $_LANG['vouchers_shoping'] : ($v['cou_type'] == 1 ? $_LANG['vouchers_login'] : ($v['cou_type'] == 5 ? $_LANG['vouchers_free'] : $_LANG['unknown']))));

			if ($v['is_use'] == 1) {
				unset($user_coupons[$k]);
			}

			$arr[] = $user_coupons[$k]['cou_id'];
		}

		$arr = array_filter($arr);

		foreach ($goods_coupons as $k => $v) {
			if (!in_array($v['cou_id'], $arr)) {
				$v['cou_start_time'] = local_date('Y-m-d', $v['cou_start_time']);
				$v['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);

				if (0 < $ru_id) {
					if ($v['ru_id'] == $ru_id || $v['ru_id'] == 0) {
						$kelingqu_coupons[] = $v;
					}
				}
				else {
					$kelingqu_coupons[] = $v;
				}
			}
		}

		$smarty->assign('user_coupons', array_slice($user_coupons, 0, 4));
		$smarty->assign('kelingqu_coupons', array_slice($kelingqu_coupons, 0, 4));
		$result['content'] = $GLOBALS['smarty']->fetch('library/right_float_yhq_info.lbi');
		exit($json->encode($result));
		break;

	default:
		break;
	}

	exit($json->encode($result));
}
else if ($act == 'get_login_dialog') {
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$back_act = !empty($_POST['back_act']) ? trim($_POST['back_act']) : '';
	$json = new JSON();
	$dsc_token = get_dsc_token();
	$smarty->assign('dsc_token', $dsc_token);
	$captcha = intval($_CFG['captcha']);
	if ($captcha & CAPTCHA_LOGIN && (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha & CAPTCHA_LOGIN_FAIL && 2 < $_SESSION['login_fail']) && 0 < gd_version()) {
		$GLOBALS['smarty']->assign('enabled_captcha', 1);
		$GLOBALS['smarty']->assign('rand', mt_rand());
	}

	$website_dir = ROOT_PATH . 'includes/website/config/';
	$website_list = get_dir_file_list($website_dir, 1, '_');

	for ($i = 0; $i < count($website_list); $i++) {
		if ($website_list[$i]['file'] == 'index.htm' || $website_list[$i]['file'] == 'index.html') {
			unset($website_list[$i]);
		}
		else {
			$website_list[$i]['back_act'] = $back_act;
		}
	}

	$count = !empty($website_list) ? count($website_list) : 0;

	if (file_exists(ROOT_PATH . 'wechat_oauth.php')) {
		$website_list[$count]['web_type'] = 'weixin';
		$website_list[$count]['back_act'] = $back_act;
	}

	$smarty->assign('website_list', $website_list);
	$smarty->assign('back_act', $back_act);
	$smarty->assign('user_lang', $_LANG);
	$result['content'] = $GLOBALS['smarty']->fetch('library/login_dialog_body.lbi');
	exit($json->encode($result));
}
else if ($act == 'get_store_list') {
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : '';
	$spec_arr = isset($_REQUEST['spec_arr']) ? $_REQUEST['spec_arr'] : '';
	$json = new JSON();
	$smarty->assign('provinces', get_regions(1, 1));
	$smarty->assign('goods_id', $goods_id);
	$sql = 'SELECT o.id,o.stores_name,s.goods_id,o.stores_address,o.stores_traffic_line,o.ru_id ,p.region_name as province ,s.goods_number ,' . 'c.region_name as city ,d.region_name as district FROM ' . $ecs->table('offline_store') . ' AS o ' . 'LEFT JOIN ' . $ecs->table('store_goods') . ' AS s ON o.id = s.store_id ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS p ON p.region_id = o.province ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS c ON c.region_id = o.city ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS d ON d.region_id = o.district ' . ('WHERE o.is_confirm=1 AND s.goods_id =\'' . $goods_id . '\'  GROUP BY o.id');
	$store_list = $db->getAll($sql);
	$is_spec = explode(',', $spec_arr);

	if (!empty($store_list)) {
		foreach ($store_list as $k => $v) {
			if (is_spec($is_spec) == true) {
				$products = get_warehouse_id_attr_number($v['goods_id'], $spec_arr, $v['ru_id'], 0, 0, '', $v['id']);
				$v['goods_number'] = $products['product_number'];

				if ($products['product_number'] == 0) {
					unset($store_list[$k]);
				}
			}
		}
	}

	$smarty->assign('store_list', $store_list);
	$result['content'] = $GLOBALS['smarty']->fetch('library/store_list_body.lbi');
	exit($json->encode($result));
}
else {
	if ($act == 'storePick' || $act == 'storeSelect' || $act == 'replaceStore') {
		require ROOT_PATH . '/includes/lib_area.php';
		$json = new JSON();
		$result = array('error' => 0, 'message' => '', 'content' => '');
		$goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
		$spec_arr = isset($_REQUEST['spec_arr']) ? $_REQUEST['spec_arr'] : '';
		$ru_id = !empty($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : 0;
		$store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
		$area_position_list = get_goods_user_area_position($ru_id, $city_id, $spec_arr, $goods_id, 0, 0, 0, $store_id);
		$smarty->assign('area_position_list', $area_position_list);

		if ($act == 'storePick') {
			$take_time = date('Y-m-d H:i:s', strtotime('+1 day'));
			$now_time = date('Y-m-d H:i:s', gmtime());
			$smarty->assign('now_time', $now_time);
			$smarty->assign('take_time', $take_time);
			$result['content'] = $GLOBALS['smarty']->fetch('library/goods_store_pick.lbi');
		}
		else if ($act == 'replaceStore') {
			$smarty->assign('temp', $act);
			$result['content'] = $GLOBALS['smarty']->fetch('library/store_select_shop.lbi');
		}
		else {
			$smarty->assign('province_name', get_shop_address($province_id));
			$smarty->assign('city_name', get_shop_address($city_id));
			$smarty->assign('district_name', get_shop_address($district_id));
			$provinces = get_regions(1, 1);

			foreach ($provinces as $k => $v) {
				if (0 < count(get_goods_user_area_position($ru_id, 0, $spec_arr, $goods_id, $v['region_id'], 0, 1))) {
					$provinces[$k]['store_count'] = 1;
				}
				else {
					$provinces[$k]['store_count'] = 0;
				}
			}

			$smarty->assign('provinces', $provinces);
			$result['content'] = $GLOBALS['smarty']->fetch('library/goods_lately_store_pick.lbi');
		}

		exit($json->encode($result));
	}
	else {
		if ($act == 'getstoreRegion' || $act == 'get_parent_regions') {
			$json = new JSON();
			$result = array('error' => 0, 'message' => '', 'content' => '');
			$region_id = !empty($_REQUEST['value']) ? intval($_REQUEST['value']) : 0;
			$level = !empty($_REQUEST['level']) ? intval($_REQUEST['level']) : 1;

			if ($act == 'getstoreRegion') {
				$level = $level + 1;
			}

			$ru_id = !empty($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : 0;
			$spec_arr = isset($_REQUEST['spec_arr']) ? $_REQUEST['spec_arr'] : '';
			$goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;

			if ($act == 'get_parent_regions') {
				$region = get_parent_regions($region_id);
			}
			else {
				$region = get_regions($level, $region_id);
			}

			$html = '';

			foreach ($region as $k => $v) {
				if (0 < $v['region_id']) {
					switch ($level) {
					case 1:
						$type = count(get_goods_user_area_position($ru_id, 0, $spec_arr, $goods_id, $v['region_id'], 0, 1));
						break;

					case 2:
						$type = count(get_goods_user_area_position($ru_id, $v['region_id'], $spec_arr, $goods_id, 0, 0, 1));
						break;

					case 3:
						$type = count(get_goods_user_area_position($ru_id, 0, $spec_arr, $goods_id, 0, $v['region_id'], 1));
						break;
					}

					$store_count = '';

					if (0 < $type) {
						$store_count = '<i></i>';
					}

					$html .= '<a href="javascript:void(0);" data-level="' . $level . '" data-id="' . $v['region_id'] . '" data-name="' . $v['region_name'] . '" class="city-item">' . $v['region_name'] . $store_count . '</a>';
				}
			}

			$result['html'] = $html;
			exit($json->encode($result));
		}
		else if ($act == 'checked_store_info') {
			$result = array('error' => 0, 'message' => '', 'content' => '');
			$json = new JSON();
			$cart_value = isset($_REQUEST['cart_value']) ? addslashes($_REQUEST['cart_value']) : '';
			$end_time = isset($_REQUEST['end_time']) ? addslashes($_REQUEST['end_time']) : '';
			$store_mobile = isset($_REQUEST['store_mobile']) ? addslashes($_REQUEST['store_mobile']) : '';

			if ($store_mobile == '') {
				$result['error'] = 1;
				$result['message'] = $_LANG['store_take_mobile'];
			}
			else {
				$sql = 'UPDATE' . $ecs->table('cart') . 'SET `take_time` = \'' . $end_time . '\',`store_mobile` = \'' . $store_mobile . '\' WHERE rec_id in(' . $cart_value . ')';
				$db->query($sql);
			}

			exit($json->encode($result));
		}
		else if ($act == 'to_pay_card') {
			require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
			$result = array('error' => 0, 'message' => '', 'content' => '');
			$vid = !empty($_REQUEST['vid']) ? intval($_REQUEST['vid']) : '';
			$json = new JSON();
			$smarty->assign('vid', $vid);
			$result['content'] = $GLOBALS['smarty']->fetch('library/to_pay_body.lbi');
			exit($json->encode($result));
		}
		else if ($act == 'remove_bind') {
			require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
			$result = array('error' => 0, 'message' => '', 'content' => '');
			$vid = !empty($_REQUEST['vid']) ? intval($_REQUEST['vid']) : '';
			$json = new JSON();
			$smarty->assign('user_info', get_user_info());

			if (0 < intval($_CFG['sms_signin'])) {
				$sms_security_code = $_SESSION['sms_security_code'] = rand(1000, 9999);
				$smarty->assign('sms_security_code', $sms_security_code);
			}

			$smarty->assign('vid', $vid);
			$result['content'] = $GLOBALS['smarty']->fetch('library/remove_bind_body.lbi');
			exit($json->encode($result));
		}
		else {
			if ($act == 'get_cat_top_list' && $tpl == 1) {
				$region_id = isset($_REQUEST['region_id']) && !empty($_REQUEST['region_id']) ? intval($_REQUEST['region_id']) : 0;
				$area_id = isset($_REQUEST['area_id']) && !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
				$area_city = isset($_REQUEST['area_city']) && !empty($_REQUEST['area_city']) ? intval($_REQUEST['area_city']) : 0;
				$cat_id = isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
				$prent_id = isset($_REQUEST['prent_id']) && !empty($_REQUEST['prent_id']) ? intval($_REQUEST['prent_id']) : 0;
				$rome_key = intval($_REQUEST['rome_key']) + 1;
				$result = array('error' => 0, 'content' => '');
				$json = new JSON();

				if (0 < $cat_id) {
					if ($categories_child = read_static_cache('cat_top_cache' . $prent_id)) {
						$one_cate_child = $categories_child[$cat_id];
					}
					else {
						$categories_child = get_parent_cat_tree($prent_id);
						$one_cate_child = $categories_child[$cat_id];
					}

					if ($one_cate_child) {
						$one_cate_child['brands'] = get_brands($cat_id, 'brand', 10);

						foreach ($one_cate_child['cat_id'] as $k => $v) {
							$child_children = get_children($v['id']);
							$childcate_goods_list = get_category_recommend_goods('best', $child_children, 0, 0, 0, '', $region_id, $area_id, $area_city, 10);

							if ($childcate_goods_list) {
								$one_cate_child['cat_id'][$k]['goods_list'] = $childcate_goods_list;
							}
						}

						$cat_top_floor_ad = '';

						for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
							$cat_top_floor_ad .= '\'cat_top_floor_ad' . $i . ',';
						}

						if (defined('THEME_EXTENSION')) {
							$cat_top_floor_ad_right = '';

							for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
								$cat_top_floor_ad_right .= '\'cat_top_floor_ad_right' . $i . ',';
							}

							$floor_ad_arr_right = array('ad_arr' => $cat_top_floor_ad_right, 'id' => $cat_id);
							$cat_top_floor_ad_right = insert_get_adv_child($floor_ad_arr_right);
							$smarty->assign('cat_top_floor_ad_right', $cat_top_floor_ad_right);
						}

						$floor_ad_arr = array('ad_arr' => $cat_top_floor_ad, 'id' => $cat_id);
						$cat_top_floor_ad = insert_get_adv_child($floor_ad_arr);
						$smarty->assign('rome_number', $rome_key);
						$smarty->assign('cat_top_floor_ad', $cat_top_floor_ad);
						$smarty->assign('one_cate_child', $one_cate_child);
						$result['content'] = html_entity_decode($smarty->fetch('library/load_category_top.lbi'));
					}
					else {
						$result['error'] = 1;
					}
				}

				if (count($categories_child) == $rome_key) {
					$result['maxindex'] = true;
				}

				$result['cat_id'] = $cat_id;
				$result['rome_key'] = $rome_key;
				exit($json->encode($result));
			}
			else if ($act == 'get_index_goods_cat') {
				$rome_key = intval($_REQUEST['rome_key']);
				$result = array('error' => 0, 'content' => '', 'maxindex' => 0);
				$json = new JSON();

				if (!read_static_cache('index_goods_cat_cache')) {
					$sql = 'SELECT filename,region,theme,id, number, type, sort_order, floor_tpl FROM ' . $GLOBALS['ecs']->table('template') . ' WHERE filename = \'index\' AND type =1 AND theme=\'' . $GLOBALS['_CFG']['template'] . '\' AND remarks=\'\' order by sort_order asc';
					$template = $GLOBALS['db']->getAll($sql);
					write_static_cache('index_goods_cat_cache', $template);
				}
				else {
					$template = read_static_cache('index_goods_cat_cache');
				}

				if ($rome_key <= count($template) - 1) {
					$row = $template[$rome_key];
					$brand_ids = insert_get_floor_content($row);
					$brands_theme2 = get_floor_brand($brand_ids);
					$brands_theme2 = get_brands_theme2($brands_theme2);
					$smarty->assign('brands_theme2', $brands_theme2);
					$region_store_enabled = '';

					if ($_CFG['region_store_enabled'] == 1) {
						$region_store_enabled = '_' . $_COOKIE['city'];
					}

					$goods_cat = read_static_cache('index_goods_cat' . $rome_key . '_' . $_SESSION['user_rank'] . $region_store_enabled);

					if ($goods_cat === false) {
						$goods_cat = assign_cat_goods($row['id'], $row['number'], 'web', '', 'cat', $region_id, $area_id, $area_city, $rome_key);
						write_static_cache('index_goods_cat' . $rome_key . '_' . $_SESSION['user_rank'] . $region_store_enabled, $goods_cat);
					}

					if ($goods_cat) {
						$get_adv = insert_get_adv(array('logo_name' => $goods_cat['floor_banner']));
						$smarty->assign('get_adv', $get_adv);
						$cat_goods_banner = '';
						$cat_goods_hot = '';
						$cat_goods_ad_left = '';
						$cat_goods_ad_right = '';

						for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
							$cat_goods_banner .= '\'cat_goods_banner' . $i . ',';
							$cat_goods_hot .= '\'cat_goods_hot' . $i . ',';
							$cat_goods_ad_left .= '\'cat_goods_ad_left' . $i . ',';
							$cat_goods_ad_right .= '\'cat_goods_ad_right' . $i . ',';
						}

						$cat_goods_hot = insert_get_adv_child(array('ad_arr' => $cat_goods_hot, 'id' => $goods_cat['id'], 'warehouse_id' => $region_id, 'area_id' => $area_id, 'area_city' => $area_city));
						$goods_cat['floor_style_tpl'] = isset($row['floor_tpl']) ? intval($row['floor_tpl']) : 0;
						$cat_goods_banner = insert_get_adv_child(array('ad_arr' => $cat_goods_banner, 'id' => $goods_cat['id'], 'warehouse_id' => $region_id, 'area_id' => $area_id, 'area_city' => $area_city, 'floor_style_tpl' => $goods_cat['floor_style_tpl']));
						$cat_goods_ad_left = insert_get_adv_child(array('ad_arr' => $cat_goods_ad_left, 'id' => $goods_cat['id'], 'warehouse_id' => $region_id, 'area_id' => $area_id, 'area_city' => $area_city, 'floor_style_tpl' => $goods_cat['floor_style_tpl']));
						$cat_goods_ad_right = insert_get_adv_child(array('ad_arr' => $cat_goods_ad_right, 'id' => $goods_cat['id'], 'warehouse_id' => $region_id, 'area_id' => $area_id, 'area_city' => $area_city, 'floor_style_tpl' => $goods_cat['floor_style_tpl']));
						$smarty->assign('cat_goods_banner', $cat_goods_banner);
						$smarty->assign('cat_goods_hot', $cat_goods_hot);
						$smarty->assign('cat_goods_ad_left', $cat_goods_ad_left);
						$smarty->assign('cat_goods_ad_right', $cat_goods_ad_right);
						$smarty->assign('goods_cat', $goods_cat);
						$result['content'] = html_entity_decode($smarty->fetch('library/load_cat_goods.lbi'));
					}
					else {
						$result['error'] = 1;
					}

					if ($rome_key == count($template) - 1) {
						$result['maxindex'] = 1;
					}
				}
				else {
					$result['error'] = 1;
				}

				exit($json->encode($result));
			}
			else if ($act == 'getCategotyParentTree') {
				$cat_id = isset($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
				$defa = isset($_REQUEST['defa']) ? intval($_REQUEST['defa']) : 0;
				$result = array('error' => 0, 'content' => '');
				$json = new JSON();
				$file = 'parent_style_brands' . $cat_id;
				$brands = read_static_cache($file);

				if ($brands === false) {
					$brands = get_brands($cat_id);
					write_static_cache($file, $brands);
				}

				$smarty->assign('brands', $brands);
				$smarty->assign('defa', $defa);
				$result['cat_id'] = $cat_id;
				$result['brands_content'] = $GLOBALS['smarty']->fetch('library/category_parent_brands.lbi');
				exit($json->encode($result));
			}
			else {
				if ($act == 'get_cat_top_list' && $tpl == 2) {
					$region_id = isset($_REQUEST['region_id']) && !empty($_REQUEST['region_id']) ? intval($_REQUEST['region_id']) : 0;
					$area_id = isset($_REQUEST['area_id']) && !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
					$area_city = isset($_REQUEST['area_city']) && !empty($_REQUEST['area_city']) ? intval($_REQUEST['area_city']) : 0;
					$cat_id = isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
					$prent_id = isset($_REQUEST['prent_id']) && !empty($_REQUEST['prent_id']) ? intval($_REQUEST['prent_id']) : 0;
					$rome_key = intval($_REQUEST['rome_key']) + 1;
					$result = array('error' => 0, 'content' => '');
					$json = new JSON();

					if (0 < $cat_id) {
						if ($categories_child = read_static_cache('cat_top_cache' . $prent_id)) {
							$one_cate_child = $categories_child[$cat_id];
						}
						else {
							$categories_child = get_parent_cat_tree($prent_id);
							$one_cate_child = $categories_child[$cat_id];
						}

						if ($one_cate_child) {
							$one_cate_child['brands'] = get_brands($cat_id, 'brand', 10);

							foreach ($one_cate_child['cat_id'] as $k => $v) {
								$child_children = get_children($v['id']);
								$childcate_goods_list = get_category_recommend_goods('best', $child_children, 0, 0, 0, '', $region_id, $area_id, $area_city, 10);

								if ($childcate_goods_list) {
									$one_cate_child['cat_id'][$k]['goods_list'] = $childcate_goods_list;
								}
							}

							$childcate_goods_hot = get_category_recommend_goods('hot', get_children($cat_id), 0, 0, 0, '', $region_id, $area_id, $area_city, 1);
							$one_cate_child['goods_hot'] = $childcate_goods_hot;
							$top_style_elec_left = '';

							for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
								$top_style_elec_left .= '\'top_style_elec_left' . $i . ',';
							}

							$floor_ad_arr = array('ad_arr' => $top_style_elec_left, 'id' => $cat_id);
							$top_style_elec_left = insert_get_adv_child($floor_ad_arr);
							$top_style_elec_row = '\'top_style_elec_row,';
							$floor_ad_arr = array('ad_arr' => $top_style_elec_row, 'id' => $cat_id);
							$top_style_elec_row = insert_get_adv_child($floor_ad_arr);
							$class_num = array('on', '', 'last');
							$smarty->assign('class_num', $class_num);
							$smarty->assign('rome_number', $rome_key);
							$smarty->assign('top_style_elec_left', $top_style_elec_left);
							$smarty->assign('top_style_elec_row', $top_style_elec_row);
							$smarty->assign('one_cate_child', $one_cate_child);
							$result['content'] = html_entity_decode($smarty->fetch('library/load_category_top.lbi'));
						}
						else {
							$result['error'] = 1;
						}
					}

					if (count($categories_child) == $rome_key) {
						$result['maxindex'] = true;
					}

					$result['cat_id'] = $cat_id;
					$result['rome_key'] = $rome_key;
					exit($json->encode($result));
				}
				else {
					if ($act == 'get_cat_top_list' && $tpl == 3) {
						$region_id = isset($_REQUEST['region_id']) && !empty($_REQUEST['region_id']) ? intval($_REQUEST['region_id']) : 0;
						$area_id = isset($_REQUEST['area_id']) && !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
						$area_city = isset($_REQUEST['area_city']) && !empty($_REQUEST['area_city']) ? intval($_REQUEST['area_city']) : 0;
						$cat_id = isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
						$prent_id = isset($_REQUEST['prent_id']) && !empty($_REQUEST['prent_id']) ? intval($_REQUEST['prent_id']) : 0;
						$rome_key = intval($_REQUEST['rome_key']) + 1;
						$result = array('error' => 0, 'content' => '');
						$json = new JSON();

						if (0 < $cat_id) {
							if ($categories_child = read_static_cache('cat_top_cache' . $prent_id)) {
								$one_cate_child = $categories_child[$cat_id];
							}
							else {
								$categories_child = get_parent_cat_tree($prent_id);
								$one_cate_child = $categories_child[$cat_id];
							}

							if ($one_cate_child) {
								$one_cate_child['brands'] = get_brands($cat_id, 'brand', 10);

								foreach ($one_cate_child['cat_id'] as $k => $v) {
									$child_children = get_children($v['id']);
									$childcate_goods_list = get_category_recommend_goods('best', $child_children, 0, 0, 0, '', $region_id, $area_id, $area_city, 10);

									if ($childcate_goods_list) {
										$one_cate_child['cat_id'][$k]['goods_list'] = $childcate_goods_list;
									}
								}

								$childcate_goods_hot = get_category_recommend_goods('hot', get_children($cat_id), 0, 0, 0, '', $region_id, $area_id, $area_city, 4);
								$one_cate_child['goods_hot'] = $childcate_goods_hot;
								$top_style_food_left = '';

								for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
									$top_style_food_left .= '\'top_style_food_left' . $i . ',';
								}

								$floor_ad_arr = array('ad_arr' => $top_style_food_left, 'id' => $cat_id);
								$top_style_food_left = insert_get_adv_child($floor_ad_arr);
								$top_style_food_row = '\'top_style_food_row,';
								$floor_ad_arr = array('ad_arr' => $top_style_food_row, 'id' => $cat_id);
								$top_style_food_row = insert_get_adv_child($floor_ad_arr);
								$class_num = array('on', '', 'last');
								$smarty->assign('class_num', $class_num);
								$smarty->assign('rome_number', $rome_key);
								$smarty->assign('top_style_food_left', $top_style_food_left);
								$smarty->assign('top_style_food_row', $top_style_food_row);
								$smarty->assign('one_cate_child', $one_cate_child);
								$result['content'] = html_entity_decode($smarty->fetch('library/load_category_top.lbi'));
							}
							else {
								$result['error'] = 1;
							}
						}

						if (count($categories_child) == $rome_key) {
							$result['maxindex'] = true;
						}

						$result['cat_id'] = $cat_id;
						$result['rome_key'] = $rome_key;
						exit($json->encode($result));
					}
					else if ($act == 'changeShow') {
						$region_id = isset($_REQUEST['region_id']) && !empty($_REQUEST['region_id']) ? intval($_REQUEST['region_id']) : 0;
						$area_id = isset($_REQUEST['area_id']) && !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
						$area_city = isset($_REQUEST['area_city']) && !empty($_REQUEST['area_city']) ? intval($_REQUEST['area_city']) : 0;
						$cat_id = isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
						$prent_id = isset($_REQUEST['prent_id']) && !empty($_REQUEST['prent_id']) ? intval($_REQUEST['prent_id']) : 0;
						$type = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);
						$tpl = empty($_REQUEST['tpl']) ? 0 : intval($_REQUEST['tpl']);
						$rome_key = intval($_REQUEST['rome_key']) + 1;
						$smarty->assign('type', $type);
						$smarty->assign('tpl', $tpl);
						$result = array('error' => 0, 'content' => '');
						$json = new JSON();

						if ($type == 1) {
							$child_children = get_children($cat_id);
							$havealook = get_category_recommend_goods('rand', $child_children, 0, 0, 0, '', $region_id, $area_city, $area_id, 6);
							$smarty->assign('havealook', $havealook);
							$result['page'] = $GLOBALS['smarty']->fetch('library/have_a_look.lbi');
						}
						else if ($type == 3) {
							if (0 < $cat_id) {
								if ($tpl == 2) {
									$child_children = get_children($cat_id);
									$goods_list = get_category_recommend_goods('rand', $child_children, 0, 0, 0, '', $region_id, $area_city, $area_id, 8);
									$smarty->assign('goods_list', $goods_list);
									$result['page'] = html_entity_decode($smarty->fetch('library/cat_goods_change.lbi'));
								}
								else if ($tpl == 3) {
									$child_children = get_children($cat_id);
									$goods_list = get_category_recommend_goods('rand', $child_children, 0, 0, 0, '', $region_id, $area_city, $area_id, 6);
									$smarty->assign('goods_list', $goods_list);
									$result['page'] = html_entity_decode($smarty->fetch('library/cat_goods_change.lbi'));
								}
							}
							else {
								$result['error'] = 1;
							}
						}

						exit($json->encode($result));
					}
					else if ($act == 'floor_cat_content') {
						$result = array('error' => 0, 'content' => '');
						$json = new JSON();
						get_request_filter();
						$goods_ids = isset($_REQUEST['goods_ids']) && !empty($_REQUEST['goods_ids']) ? addslashes($_REQUEST['goods_ids']) : 0;
						$cat_id = isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
						$floor_num = isset($_REQUEST['floor_num']) && !empty($_REQUEST['floor_num']) ? intval($_REQUEST['floor_num']) : 0;
						$warehouse_id = isset($_REQUEST['warehouse_id']) && !empty($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
						$area_id = isset($_REQUEST['area_id']) && !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
						$area_city = isset($_REQUEST['area_city']) && !empty($_REQUEST['area_city']) ? intval($_REQUEST['area_city']) : 0;
						$seller_id = isset($_REQUEST['seller_id']) && !empty($_REQUEST['seller_id']) ? intval($_REQUEST['seller_id']) : 0;
						$floorcat = isset($_REQUEST['floorcat']) && !empty($_REQUEST['floorcat']) ? intval($_REQUEST['floorcat']) : 0;
						$result['cat_id'] = $cat_id;

						if ($_CFG['region_store_enabled'] == 1) {
							$region_store_enabled = '_' . $_COOKIE['city'];
						}

						$file = 'floor_cat_content_' . $cat_id . '_' . $_SESSION['user_rank'] . '_' . $floor_num . '_' . $seller_id . $region_store_enabled;

						if (empty($goods_ids)) {
							$goods_list = read_static_cache($file);
						}
						else {
							$goods_list = false;
						}

						if ($goods_list === false) {
							$goods_list = get_floor_ajax_goods($cat_id, $floor_num, $warehouse_id, $area_id, $area_city, $goods_ids, $seller_id);

							if (empty($goods_ids)) {
								write_static_cache($file, $goods_list);
							}
						}

						$smarty->assign('goods_list', $goods_list);
						$temp = 'floor_temp';

						if ($floorcat == 1) {
							$temp = 'floor_temp_expand';
						}

						$smarty->assign('temp', $temp);

						if ($floorcat == 2) {
							$result['content'] = $goods_list;
						}
						else {
							$defaultnumber = $floor_num - count($goods_list);
							$defaultgoods = array();

							if (0 < $defaultnumber) {
								$defaultgoods = range(1, $defaultnumber);
							}

							$smarty->assign('defaultgoods', $defaultgoods);
							$result['content'] = $GLOBALS['smarty']->fetch('library/floor_cat_content.lbi');
						}

						exit($json->encode($result));
					}
					else if ($act == 'cart_info') {
						$result = array('error' => 0, 'content' => '');
						$json = new JSON();
						$result['content'] = insert_cart_info(4);
						exit($json->encode($result));
					}
					else if ($act == 'ajax_store_collect') {
						if (defined('THEME_EXTENSION')) {
							clear_cache_files('store_street');
						}

						$result = array('error' => 0, 'content' => '');
						$json = new JSON();
						$ru_id = empty($_REQUEST['ru_id']) ? 0 : intval($_REQUEST['ru_id']);
						$type = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);

						if ($type == 1) {
							$sql = 'DELETE FROM ' . $ecs->table('collect_store') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\' AND ru_id = \'' . $ru_id . '\' ';
							$db->query($sql);
						}
						else {
							$sql = 'SELECT rec_id FROM ' . $ecs->table('collect_store') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\' AND ru_id = \'' . $ru_id . '\' ';
							$rec_id = $db->getOne($sql);

							if (0 < $rec_id) {
								$result['error'] = 1;
							}
							else {
								$is_attention = 1;
								$sql = 'INSERT INTO ' . $ecs->table('collect_store') . '(`user_id`, `ru_id`, `add_time`, `is_attention`)VALUES(\'' . $_SESSION['user_id'] . ('\', \'' . $ru_id . '\', \'') . gmtime() . ('\', \'' . $is_attention . '\')');
								$db->query($sql);
							}
						}

						$result['type'] = $type;
						exit($json->encode($result));
					}
					else if ($act == 'ajax_change_brands') {
						$result = array('error' => 0, 'content' => '');
						$json = new JSON();
						$temp = !empty($_REQUEST['temp']) ? trim($_REQUEST['temp']) : '';
						$limit = ' LIMIT 17';

						if ($temp == 'backup_festival_1') {
							$limit = ' LIMIT 29';
						}

						$sql = 'SELECT b.* FROM ' . $GLOBALS['ecs']->table('brand') . ' AS b LEFT JOIN ' . $GLOBALS['ecs']->table('brand_extend') . ' AS be ON b.brand_id = be.brand_id WHERE b.is_show = 1 ORDER BY RAND() ' . $limit;
						$val = '';
						$recommend_brands = $GLOBALS['db']->getAll($sql);

						foreach ($recommend_brands as $key => $val) {
							$recommend_brands[$key]['brand_logo'] = empty($val['brand_logo']) ? str_replace(array('../'), '', $GLOBALS['_CFG']['no_brand']) : DATA_DIR . '/brandlogo/' . $val['brand_logo'];
							if ($val['site_url'] && 8 < strlen($val['site_url'])) {
								$recommend_brands[$key]['url'] = $val['site_url'];
							}
							else {
								$recommend_brands[$key]['url'] = build_uri('brandn', array('bid' => $val['brand_id']), $val['brand_name']);
							}

							if (defined('THEME_EXTENSION')) {
								$recommend_brands[$key]['collect_count'] = get_collect_brand_user_count($val['brand_id']);
								$recommend_brands[$key]['is_collect'] = get_collect_user_brand($val['brand_id']);
							}

							if ($GLOBALS['_CFG']['open_oss'] == 1 && $val['brand_logo']) {
								$bucket_info = get_bucket_info();
								$recommend_brands[$key]['brand_logo'] = $bucket_info['endpoint'] . DATA_DIR . '/brandlogo/' . $val['brand_logo'];
							}
						}

						if (0 < count($recommend_brands)) {
							$need_cache = $GLOBALS['smarty']->caching;
							$need_compile = $GLOBALS['smarty']->force_compile;
							$GLOBALS['smarty']->caching = false;
							$GLOBALS['smarty']->force_compile = true;
							$GLOBALS['smarty']->assign('recommend_brands', $recommend_brands);
							$GLOBALS['smarty']->assign('temp', $temp);
							$result['content'] = $GLOBALS['smarty']->fetch('library/index_brand_street.lbi');
							$GLOBALS['smarty']->caching = $need_cache;
							$GLOBALS['smarty']->force_compile = $need_compile;
						}

						exit($json->encode($result));
					}
					else if ($act == 'view_logistics_info') {
						$result = array('error' => 0, 'content' => '');
						$json = new JSON();
						$order_id = empty($_REQUEST['order_id']) ? '' : intval($_REQUEST['order_id']);
						$invoice_no = empty($_REQUEST['invoice_no']) ? '' : trim($_REQUEST['invoice_no']);
						$result['expressid'] = get_table_date('order_info', 'order_id=\'' . $order_id . '\'', array('shipping_name'), 2);
						$result['expressno'] = $invoice_no;
						exit($json->encode($result));
					}
					else if ($act == 'insert_header_region') {
						$result = array('error' => 0, 'content' => '');
						$json = new JSON();
						$GLOBALS['smarty']->assign('is_insert', 1);
						$result['content'] = insert_header_region();
						exit($json->encode($result));
					}
				}
			}
		}
	}
}

?>
