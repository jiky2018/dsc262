<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
if (!isset($_COOKIE['province'])) {
	$area_array = get_ip_area_name();

	if ($area_array['county_level'] == 2) {
		$date = array('region_id', 'parent_id', 'region_name');
		$where = 'region_name = \'' . $area_array['area_name'] . '\' AND region_type = 2';
		$city_info = get_table_date('region', $where, $date, 1);
		$date = array('region_id', 'region_name');
		$where = 'region_id = \'' . $city_info[0]['parent_id'] . '\'';
		$province_info = get_table_date('region', $where, $date);
		$where = 'parent_id = \'' . $city_info[0]['region_id'] . '\' order by region_id asc limit 0, 1';
		$district_info = get_table_date('region', $where, $date, 1);
	}
	else if ($area_array['county_level'] == 1) {
		$area_name = $area_array['area_name'];
		$date = array('region_id', 'region_name');
		$where = 'region_name = \'' . $area_name . '\'';
		$province_info = get_table_date('region', $where, $date);
		$where = 'parent_id = \'' . $province_info['region_id'] . '\' order by region_id asc limit 0, 1';
		$city_info = get_table_date('region', $where, $date, 1);
		$where = 'parent_id = \'' . $city_info[0]['region_id'] . '\' order by region_id asc limit 0, 1';
		$district_info = get_table_date('region', $where, $date, 1);
	}
}

if ($_REQUEST['act'] == 'select_regionChild') {
	$result = array('error' => 0, 'message' => '', 'content' => '', 'ra_id' => '', 'region_id' => '');
	$json = new \App\Libraries\JSON();
	$_POST['region'] = strip_tags(urldecode($_POST['region']));
	$_POST['region'] = json_str_iconv($_POST['region']);
	$region = $json->decode($_POST['region']);
	$where = 'parent_id = \'' . $region->region_id . '\'';
	$date = array('region_id', 'region_name');
	$city_list = get_table_date('region', $where, $date, 1);
	$result['city_list'] = 0;

	if ($region->type == 0) {
		if (empty($city_list)) {
			cookie('province', $region->region_id);
		}
	}
	else if ($region->type == 1) {
		cookie('type_province', $region->region_id);
	}
	else if ($region->type == 2) {
		cookie('type_city', $region->region_id);
	}

	if (empty($city_list)) {
		$result['city_list'] = 1;
		cookie('province', $_COOKIE['type_province']);
		cookie('city', $_COOKIE['type_city']);
	}

	cookie('ra_id', $region->ra_id);
	$GLOBALS['smarty']->assign('city_list', $city_list);
	$GLOBALS['smarty']->assign('type', $region->type);
	$GLOBALS['smarty']->assign('city_top', $_COOKIE['city']);
	$GLOBALS['smarty']->assign('district_top', $_COOKIE['district']);
	$result['ra_id'] = $region->ra_id;
	$result['type'] = $region->type;
	$result['region_id'] = $region->region_id;
	clear_all_files();
	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'select_district_list') {
	$result = array('error' => 0, 'message' => '', 'content' => '', 'ra_id' => '', 'region_id' => '');
	$json = new \App\Libraries\JSON();
	$_POST['region'] = strip_tags(urldecode($_POST['region']));
	$_POST['region'] = json_str_iconv($_POST['region']);
	$region = $json->decode($_POST['region']);
	$where = 'region_id = \'' . $region->region_id . '\'';
	$date = array('parent_id');
	$province = get_table_date('region', $where, $date, 2);
	$where = 'parent_id = \'' . $region->region_id . '\' order by region_id asc limit 0, 1';
	$date = array('region_id', 'region_name');
	$district_list = get_table_date('region', $where, $date, 1);

	if ($region->type == 0) {
		cookie('province', $province);
		cookie('city', $region->region_id);

		if (0 < count($district_list)) {
			cookie('district', $district_list[0]['region_id']);
		}
		else {
			cookie('district', 0);
		}

		cookie('type_province', 0);
		cookie('type_city', 0);
		cookie('type_district', 0);
	}
	else {
		cookie('type_district', $region->region_id);
	}

	clear_all_files();
	exit($json->encode($result));
}

$city_district_list = get_isHas_area($_COOKIE['type_city']);

if (!$city_district_list) {
	cookie('type_district', 0);
	$_COOKIE['type_district'] = 0;
}

$provinceT_list = get_isHas_area($_COOKIE['type_province']);
$cityT_list = get_isHas_area($_COOKIE['type_city'], 1);
$districtT_list = get_isHas_area($_COOKIE['type_district'], 1);
if (0 < $_COOKIE['type_province'] && $provinceT_list) {
	if ($city_district_list) {
		if ($cityT_list['parent_id'] == $_COOKIE['type_province'] && $_COOKIE['type_city'] == $districtT_list['parent_id']) {
			$_COOKIE['province'] = $_COOKIE['type_province'];

			if (0 < $_COOKIE['type_city']) {
				$_COOKIE['city'] = $_COOKIE['type_city'];
			}

			if (0 < $_COOKIE['type_district']) {
				$_COOKIE['district'] = $_COOKIE['type_district'];
			}
		}
	}
	else if ($cityT_list['parent_id'] == $_COOKIE['type_province']) {
		$_COOKIE['province'] = $_COOKIE['type_province'];

		if (0 < $_COOKIE['type_city']) {
			$_COOKIE['city'] = $_COOKIE['type_city'];
		}

		if (0 < $_COOKIE['type_district']) {
			$_COOKIE['district'] = $_COOKIE['type_district'];
		}
	}
}

$province_id = isset($_COOKIE['province']) ? $_COOKIE['province'] : $province_info['region_id'];
$city_id = isset($_COOKIE['city']) ? $_COOKIE['city'] : $city_info[0]['region_id'];
$district_id = isset($_COOKIE['district']) ? $_COOKIE['district'] : $district_info[0]['region_id'];
$warehouse_date = array('region_id', 'region_name');
$warehouse_where = 'regionId = \'' . $province_id . '\'';
$warehouse_province = get_table_date('region_warehouse', $warehouse_where, $warehouse_date);
$sellerInfo = get_seller_info_area();

if (!$warehouse_province) {
	$province_id = $sellerInfo['province'];
	$city_id = $sellerInfo['city'];
	$district_id = $sellerInfo['district'];
}

cookie('province', $province_id);
cookie('city', $city_id);
cookie('district', $district_id);
$where = 'region_id = \'' . $city_id . '\'';
$date = array('region_name');
$region_name = get_table_date('region', $where, $date, 2);
$GLOBALS['smarty']->assign('region_name', $region_name);
$where = 'parent_id = \'' . $_COOKIE['province'] . '\' order by region_id asc';
$date = array('region_id', 'region_name');
$city_list = get_table_date('region', $where, $date, 1);
$GLOBALS['smarty']->assign('city_list', $city_list);
$city_cache_data = read_static_cache('pin_regions', '/data/sc_file/');

if ($city_cache_data === false) {
	$city_region_list = get_city_region();
	$pin_region_list = $city_region_list;
}
else {
	$pin_region_list = $city_cache_data;
}

ksort($pin_region_list);
$GLOBALS['smarty']->assign('pin_region_list', $pin_region_list);
$GLOBALS['smarty']->assign('area_phpName', 'index.php');
$GLOBALS['smarty']->assign('province', $province_id);
$GLOBALS['smarty']->assign('ra_id', $_COOKIE['ra_id']);
$GLOBALS['smarty']->assign('city_top', $city_id);
$GLOBALS['smarty']->assign('district_top', $district_id);
$selectLocate = 0;

if (isset($_COOKIE['province'])) {
	$selectLocate = 1;
}

$GLOBALS['smarty']->assign('selectLocate', $selectLocate);
$sql = 'select kf_qq, kf_ww, kf_type, kf_tel from ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' where ru_id = 0 LIMIT 1';
$basic_info = $GLOBALS['db']->getRow($sql);
$GLOBALS['smarty']->assign('basic_info', $basic_info);
$GLOBALS['smarty']->assign('user_id', $_SESSION['user_id']);

?>
