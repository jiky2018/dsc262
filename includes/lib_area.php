<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
if (!isset($_COOKIE['province'])) {
	$area_array = get_ip_area_name();

	if ($area_array['county_level'] == 2) {
		$date = array('region_id', 'parent_id', 'region_name');
		$where = 'region_name LIKE \'%' . $area_array['area_name'] . '%\' AND region_type = 2';
		$city_info = get_table_date('region', $where, $date, 1);
		$date = array('region_id', 'region_name');
		$where = 'region_id = \'' . $city_info[0]['parent_id'] . '\'';
		$province_info = get_table_date('region', $where, $date);
		$where = 'parent_id = \'' . $city_info[0]['region_id'] . '\' ORDER BY region_id ASC LIMIT 0, 1';
		$district_info = get_table_date('region', $where, $date, 1);
	}
	else if ($area_array['county_level'] == 1) {
		$area_name = $area_array['area_name'];
		$date = array('region_id', 'region_name');
		$where = 'region_name = \'' . $area_name . '\'';
		$province_info = get_table_date('region', $where, $date);
		$where = 'parent_id = \'' . $province_info['region_id'] . '\' ORDER BY region_id ASC LIMIT 0, 1';
		$city_info = get_table_date('region', $where, $date, 1);
		$where = 'parent_id = \'' . $city_info[0]['region_id'] . '\' ORDER BY region_id ASC LIMIT 0, 1';
		$district_info = get_table_date('region', $where, $date, 1);
	}
}

if ($_REQUEST['act'] == 'select_regionChild') {
	include_once 'includes/cls_json.php';
	$result = array('error' => 0, 'message' => '', 'content' => '', 'ra_id' => '', 'region_id' => '');
	$json = new JSON();
	$_POST['region'] = strip_tags(urldecode($_POST['region']));
	$_POST['region'] = json_str_iconv($_POST['region']);
	$region = $json->decode($_POST['region']);
	$where = 'parent_id = \'' . $region->region_id . '\'';
	$date = array('region_id', 'region_name');
	$city_list = get_table_date('region', $where, $date, 1);
	$result['city_list'] = 0;

	if ($region->type == 0) {
		if (empty($city_list)) {
			setcookie('province', $region->region_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		}
	}
	else if ($region->type == 1) {
		setcookie('type_province', $region->region_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	}
	else if ($region->type == 2) {
		setcookie('type_city', $region->region_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	}

	if (empty($city_list)) {
		$result['city_list'] = 1;
		setcookie('province', $_COOKIE['type_province'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('city', $_COOKIE['type_city'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	}

	setcookie('ra_id', $region->ra_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$smarty->assign('city_list', $city_list);
	$smarty->assign('type', $region->type);
	$smarty->assign('city_top', $_COOKIE['city']);
	$smarty->assign('district_top', $_COOKIE['district']);
	$result['ra_id'] = $region->ra_id;
	$result['type'] = $region->type;
	$result['region_id'] = $region->region_id;
	$result['content'] = $smarty->fetch('library/merchants_city_list.lbi');
	clear_all_files();
	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'select_district_list') {
	include_once 'includes/cls_json.php';
	$result = array('error' => 0, 'message' => '', 'content' => '', 'ra_id' => '', 'region_id' => '');
	$json = new JSON();
	$_POST['region'] = strip_tags(urldecode($_POST['region']));
	$_POST['region'] = json_str_iconv($_POST['region']);
	$region = $json->decode($_POST['region']);
	$where = 'region_id = \'' . $region->region_id . '\'';
	$date = array('parent_id');
	$province = get_table_date('region', $where, $date, 2);
	$where = 'parent_id = \'' . $region->region_id . '\' ORDER BY region_id ASC LIMIT 0, 1';
	$date = array('region_id', 'region_name');
	$district_list = get_table_date('region', $where, $date, 1);

	if ($region->type == 0) {
		setcookie('province', $province, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('city', $region->region_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$district_id = 0;

		if ($district_list) {
			$district_id = $district_list[0]['region_id'];
		}

		setcookie('district', $district_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$street_list = 0;
		$street_id = 0;

		if ($district_id) {
			$sql = 'SELECT region_id FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE parent_id = \'' . $district_id . '\'');
			$street_info = $GLOBALS['db']->getCol($sql);

			if ($street_info) {
				$street_id = $street_info[0];
				$street_list = implode(',', $street_info);
			}
		}

		setcookie('street', $street_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('street_area', $street_list, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$region_top = get_warehouse_goods_region($province);
		setcookie('area_region', $region_top['region_id'], gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('type_province', 0, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('type_city', 0, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		setcookie('type_district', 0, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	}
	else {
		setcookie('type_district', $region->region_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	}

	clear_all_files();
	exit($json->encode($result));
}

$type_province = isset($_COOKIE['type_province']) ? $_COOKIE['type_province'] : 0;
$type_city = isset($_COOKIE['type_city']) ? $_COOKIE['type_city'] : 0;
$type_district = isset($_COOKIE['type_district']) ? $_COOKIE['type_district'] : 0;

if ($type_city) {
	$city_district_list = get_isHas_area($type_city);

	if (!$city_district_list) {
		setcookie('type_district', 0, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$_COOKIE['type_district'] = 0;
	}
}

if ($type_province) {
	$provinceT_list = get_isHas_area($type_province);

	if ($provinceT_list) {
		$cityT_list = get_isHas_area($type_city, 1);

		if ($city_district_list) {
			$districtT_list = get_isHas_area($type_district, 1);
			if ($cityT_list['parent_id'] == $type_province && $type_city == $districtT_list['parent_id']) {
				$_COOKIE['province'] = $type_province;

				if (0 < $type_city) {
					$_COOKIE['city'] = $type_city;
				}

				if (0 < $type_district) {
					$_COOKIE['district'] = $type_district;
				}
			}
		}
		else if ($cityT_list['parent_id'] == $type_province) {
			$_COOKIE['province'] = $type_province;

			if (0 < $type_city) {
				$_COOKIE['city'] = $type_city;
			}

			if (0 < $type_district) {
				$_COOKIE['district'] = $type_district;
			}
		}
	}
}

$province_id = isset($_COOKIE['province']) ? $_COOKIE['province'] : $province_info['region_id'];
$city_id = isset($_COOKIE['city']) ? $_COOKIE['city'] : $city_info[0]['region_id'];
$district_id = isset($_COOKIE['district']) ? $_COOKIE['district'] : $district_info[0]['region_id'];

if ($province_id) {
	$warehouse_date = array('region_id', 'region_name');
	$warehouse_where = 'regionId = \'' . $province_id . '\'';
	$warehouse_province = get_table_date('region_warehouse', $warehouse_where, $warehouse_date);

	if (!$warehouse_province) {
		$sellerInfo = get_shop_info_content();
		$province_id = $sellerInfo['province'];
		$city_id = $sellerInfo['city'];
		$district_id = $sellerInfo['district'];
	}
}

$street_list = 0;
$street_id = 0;
if (!isset($_COOKIE['street']) && !isset($_COOKIE['street_area'])) {
	$sql = 'SELECT region_id FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE parent_id = \'' . $district_id . '\'');
	$street_info = $GLOBALS['db']->getCol($sql);

	if ($street_info) {
		$street_id = $street_info[0];
		$street_list = implode(',', $street_info);
	}
}

$street_id = isset($_COOKIE['street']) ? $_COOKIE['street'] : $street_id;
$street_list = isset($_COOKIE['street_area']) ? $_COOKIE['street_area'] : $street_list;
setcookie('province', $province_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
setcookie('city', $city_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
setcookie('district', $district_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
setcookie('street', $street_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
setcookie('street_area', $street_list, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
$where = 'region_id = \'' . $city_id . '\'';
$date = array('region_name');
$region_name = get_table_date('region', $where, $date, 2);
$smarty->assign('region_name', $region_name);
$pin_region_list = read_static_cache('pin_regions', '/data/sc_file/');

if ($pin_region_list === false) {
	$pin_region_list = array();
}

ksort($pin_region_list);
$smarty->assign('pin_region_list', $pin_region_list);
$smarty->assign('area_phpName', 'index.php');
$smarty->assign('province', $province_id);
$smarty->assign('ra_id', $_COOKIE['ra_id']);
$smarty->assign('city_top', $city_id);
$smarty->assign('district_top', $district_id);
$selectLocate = 0;

if (isset($_COOKIE['province'])) {
	$selectLocate = 1;
}

$smarty->assign('selectLocate', $selectLocate);
$basic_info = get_seller_shopinfo();

if ($basic_info['kf_ww']) {
	$kf_ww = array_filter(preg_split('/\\s+/', $basic_info['kf_ww']));
	$kf_ww = explode('|', $kf_ww[0]);

	if (!empty($kf_ww[1])) {
		$basic_info['kf_ww'] = $kf_ww[1];
	}
	else {
		$basic_info['kf_ww'] = '';
	}
}
else {
	$basic_info['kf_ww'] = '';
}

if ($basic_info['kf_qq']) {
	$kf_qq = array_filter(preg_split('/\\s+/', $basic_info['kf_qq']));
	$kf_qq = explode('|', $kf_qq[0]);

	if (!empty($kf_qq[1])) {
		$basic_info['kf_qq'] = $kf_qq[1];
	}
	else {
		$basic_info['kf_qq'] = '';
	}
}
else {
	$basic_info['kf_qq'] = '';
}

$smarty->assign('basic_info', $basic_info);
$user_id = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$smarty->assign('user_id', $user_id);

?>
