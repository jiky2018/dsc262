<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function gift_get_goods($size, $page = 1)
{
	$page = isset($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$sql = 'SELECT config_goods_id,gift_id FROM ' . $GLOBALS['ecs']->table('user_gift_gard') . (' WHERE gift_sn=\'' . $_SESSION['gift_sn'] . '\' AND is_delete = 1');
	$config_goods = $GLOBALS['db']->getRow($sql);
	$config_goods_arr = explode(',', $config_goods['config_goods_id']);
	$sql = 'SELECT goods_id, goods_name, shop_price, goods_thumb FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id ' . db_create_in($config_goods_arr);
	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
	$arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
		$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$arr[$row['goods_id']]['shop_price'] = $row['shop_price'];
		$arr[$row['goods_id']]['goods_thumb'] = $row['goods_thumb'];
	}

	return $arr;
}

function get_gift_goods_count()
{
	$sql = 'SELECT config_goods_id FROM ' . $GLOBALS['ecs']->table('user_gift_gard') . (' WHERE gift_sn=\'' . $_SESSION['gift_sn'] . '\' AND is_delete = 1');
	$config_goods = $GLOBALS['db']->getRow($sql);
	$config_goods_arr = explode(',', $config_goods['config_goods_id']);
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . 'WHERE goods_id ' . db_create_in($config_goods_arr);
	return $GLOBALS['db']->getOne($sql);
}

function check_gift_login($gift_sn, $gift_pwd, $remember = NULL)
{
	if (empty($gift_pwd) || empty($gift_sn)) {
		return false;
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('user_gift_gard') . (' WHERE gift_sn = \'' . $gift_sn . '\' AND goods_id = 0 AND is_delete = 1');

	if (!$GLOBALS['db']->getOne($sql)) {
		$_SESSION['gift_sn'] = '';
		show_message($GLOBALS['_LANG']['gift_gard_used'], $GLOBALS['_LANG']['gift_gard_login'], 'gift_gard.php', 'error');
		return false;
	}

	$sql = 'SELECT ' . 'gift_gard_id, gift_id' . ' FROM ' . $GLOBALS['ecs']->table('user_gift_gard') . (' WHERE gift_sn = \'' . $gift_sn . '\' AND gift_password = \'' . $gift_pwd . '\' AND is_delete = 1');
	$result = $GLOBALS['db']->getRow($sql);

	if (empty($result)) {
		$_SESSION['gift_sn'] = '';
		show_message($GLOBALS['_LANG']['password_error'], $GLOBALS['_LANG']['back_gift_login'], 'gift_gard.php?act=gift_login', 'error');
		return false;
	}

	$sql = 'SELECT gift_end_date, gift_start_date FROM ' . $GLOBALS['ecs']->table('gift_gard_type') . ' WHERE review_status = 3 AND gift_id = \'' . $result['gift_id'] . '\' LIMIT 1';
	$row = $GLOBALS['db']->getRow($sql);

	if ($row) {
		$time = gmtime();

		if ($row['gift_end_date'] <= $time) {
			$_SESSION['gift_sn'] = '';
			show_message($GLOBALS['_LANG']['gift_gard_overdue_time'] . local_date('Y-m-d H:i:s', $row['gift_end_date']), $GLOBALS['_LANG']['back_gift_login'], 'gift_gard.php?act=gift_login', 'error');
			return false;
		}
		else if ($time <= $row['gift_start_date']) {
			$_SESSION['gift_sn'] = '';
			show_message($GLOBALS['_LANG']['gift_gard_Use_time'] . local_date('Y-m-d H:i:s', $row['gift_start_date']), $GLOBALS['_LANG']['back_gift_login'], 'gift_gard.php?act=gift_login', 'error');
			return false;
		}
	}
	else {
		$_SESSION['gift_sn'] = '';
		show_message($GLOBALS['_LANG']['not_gift_gard'], $GLOBALS['_LANG']['back_gift_login'], 'gift_gard.php?act=gift_login', 'error');
		return false;
	}

	if ($result) {
		clear_all_files();
		$_SESSION['gift_id'] = $result['gift_id'];
		$_SESSION['gift_sn'] = $gift_sn;
		$time = time() + 3600 * 24 * 15;
		setcookie('gift_sn', $gift_sn, $time);
		return true;
	}
	else {
		$_SESSION['gift_sn'] = '';
		return false;
	}
}

function get_regions_log($type = 0, $parent = 0)
{
	$sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE region_type = \'' . $type . '\' AND parent_id = \'' . $parent . '\'');
	return $GLOBALS['db']->GetAll($sql);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

if (!defined('THEME_EXTENSION')) {
	$categories_pro = get_category_tree_leve_one();
	$smarty->assign('categories_pro', $categories_pro);
}

require ROOT_PATH . '/includes/lib_area.php';
$area_info = get_area_info($province_id);
$area_id = $area_info['region_id'];
$where = 'regionId = \'' . $province_id . '\'';
$date = array('parent_id');
$region_id = get_table_date('region_warehouse', $where, $date, 2);
$user_id = empty($_SESSION['user_id']) ? 0 : $_SESSION['user_id'];

if (empty($_REQUEST['act'])) {
	if (isset($_SESSION['gift_sn']) && $_SESSION['gift_sn']) {
		$_REQUEST['act'] = 'list';
	}
	else {
		$_REQUEST['act'] = 'gift_login';
	}
}

if ($_REQUEST['act'] == 'gift_login') {
	assign_template();
	$cat_id = isset($_REQUEST['cat_id']) && 0 < intval($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$position = assign_ur_here('gift_gard');
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $position['ur_here']);
	$captcha = intval($_CFG['captcha']);
	if ($captcha & CAPTCHA_LOGIN && (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha & CAPTCHA_LOGIN_FAIL && 2 < $_SESSION['login_fail']) && 0 < gd_version()) {
		$GLOBALS['smarty']->assign('enabled_captcha', 1);
		$GLOBALS['smarty']->assign('rand', mt_rand());
	}

	$smarty->assign('helps', get_shop_help());
	$smarty->display('gift_gard_login.dwt');
}

if ($_REQUEST['act'] == 'check_gift') {
	if (!$user_id) {
		ecs_header("Location: user.php\n");
		exit();
	}

	$gift_card = isset($_POST['gift_card']) ? trim($_POST['gift_card']) : '';
	$gift_pwd = isset($_POST['gift_pwd']) ? trim($_POST['gift_pwd']) : '';
	$captcha_str = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';

	if (isset($_POST['captcha'])) {
		if (empty($captcha_str)) {
			show_message($_LANG['cmt_lang']['captcha_not_null'], $_LANG['relogin_lnk'], 'javascript:history.go(-1);', 'error');
		}

		if ($captcha_str & CAPTCHA_LOGIN && (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha_str & CAPTCHA_LOGIN_FAIL && 2 < $_SESSION['login_fail']) && 0 < gd_version()) {
			$verify = new Verify();
			$captcha_code = $verify->check($captcha_str, 'captcha_login');

			if (!$captcha_code) {
				show_message($_LANG['invalid_captcha'], $_LANG['relogin_lnk'], 'javascript:history.go(-1);', 'error');
			}
		}
	}

	if (check_gift_login($gift_card, $gift_pwd)) {
		ecs_header("Location: gift_gard.php?act=list\n");
		exit();
	}
	else {
		show_message($_LANG['gift_gard_error'], $_LANG['relogin_lnk'], 'gift_gard.php', 'error');
	}
}

if ($_REQUEST['act'] == 'exit_gift') {
	$time = time() - 3600;
	setcookie('gift_sn', '', $time);
	$_SESSION['gift_sn'] = NULL;
	ecs_header("Location: index.php\n");
	exit();
}

if ($_REQUEST['act'] == 'list') {
	$page = isset($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$size = isset($_CFG['page_size']) && 0 < intval($_CFG['page_size']) ? intval($_CFG['page_size']) : 10;
	$gift_id = isset($_SESSION['gift_id']) && 0 < intval($_SESSION['gift_id']) ? intval($_SESSION['gift_id']) : 0;
	$gift_sn = isset($_SESSION['gift_sn']) && empty($_SESSION['gift_sn']) ? '' : addslashes($_SESSION['gift_sn']);
	$default_display_type = $_CFG['show_order_type'] == '0' ? 'list' : ($_CFG['show_order_type'] == '1' ? 'grid' : 'text');
	$default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';
	$default_sort_order_type = $_CFG['sort_order_type'] == '0' ? 'gift_id' : 'gift_id';
	$sort = isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array('gift_gard_id')) ? trim($_REQUEST['sort']) : $default_sort_order_type;
	$order = isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC')) ? trim($_REQUEST['order']) : $default_sort_order_method;
	$display = isset($_REQUEST['display']) && in_array(trim(strtolower($_REQUEST['display'])), array('list', 'grid', 'text')) ? trim($_REQUEST['display']) : (isset($_COOKIE['ECS']['display']) ? $_COOKIE['ECS']['display'] : $default_display_type);
	$display = in_array($display, array('list', 'grid', 'text')) ? $display : 'text';
	$cache_id = sprintf('%X', crc32($cat_id . '-' . $display . '-' . $sort . '-' . $order . '-' . $page . '-' . $size . '-' . $_SESSION['user_rank'] . '-' . $_CFG['lang']));

	if (!$smarty->is_cached('gift_gard_list.dwt', $cache_id)) {
		$children = get_children($cat_id);
		$cat_select = array('cat_name', 'keywords', 'cat_desc', 'style', 'grade', 'filter_attr', 'parent_id');
		$cat = get_cat_info($cat_id, $cat_select);

		if (!empty($cat)) {
			$smarty->assign('keywords', htmlspecialchars($cat['keywords']));
			$smarty->assign('description', htmlspecialchars($cat['cat_desc']));
		}

		assign_template();
		$position = assign_ur_here('gift_gard');
		$smarty->assign('page_title', $position['title']);
		$smarty->assign('ur_here', $position['ur_here']);
		$smarty->assign('helps', get_shop_help());
		$history_goods = get_history_goods($goods_id, $region_id, $area_id);
		$smarty->assign('history_goods', $history_goods);
		$smarty->assign('country_list', get_regions());
		$smarty->assign('shop_country', $_CFG['shop_country']);
		$smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));
		$count = get_gift_goods_count();
		$max_page = 0 < $count ? ceil($count / $size) : 1;

		if ($max_page < $page) {
			$page = $max_page;
		}

		$goodslist = gift_get_goods($size, $page);
		$sql = 'SELECT gift_id FROM ' . $ecs->table('user_gift_gard') . (' WHERE gift_sn=\'' . $gift_sn . '\'');
		$gift = $db->getRow($sql);
		$sql = 'SELECT gift_menory FROM ' . $ecs->table('gift_gard_type') . ' WHERE gift_id = \'' . $gift['gift_id'] . '\'';
		$gift_menory = $db->getRow($sql);
		$smarty->assign('gift_menory', $gift_menory['gift_menory']);
		$smarty->assign('gift_sn', $_SESSION['gift_sn']);
		$smarty->assign('goods_list', $goodslist);
		$smarty->assign('category', $cat_id);
		$smarty->assign('integral_max', $integral_max);
		$smarty->assign('integral_min', $integral_min);
		assign_pager('gift_gard', $gift_id, $count, $size, $sort, $order, $page, '', '');
		assign_dynamic('gift_gard_list');
	}

	$smarty->display('gift_gard_list.dwt', $cache_id);
}
else if ($_REQUEST['act'] == 'take_view') {
	$goods_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
	$gift_sn = isset($_SESSION['gift_sn']) && empty($_SESSION['gift_sn']) ? '' : addslashes($_SESSION['gift_sn']);

	if ($gift_sn) {
		$pwd = $db->getRow('SELECT * FROM ' . $ecs->table('user_gift_gard') . (' WHERE gift_sn =\'' . $gift_sn . '\' AND is_delete = 1'));

		if (check_gift_login($gift_sn, $pwd['gift_password'])) {
			$_SESSION['gift_sn'] = '';
			ecs_header("Location: gift_gard.php?act=gift_login\n");
			exit();
		}
	}

	if (empty($goods_id)) {
		ecs_header("Location: gift_gard.php?act=list\n");
		exit();
	}

	include_once 'includes/lib_transaction.php';
	assign_template();
	$smarty->assign('country_list', get_regions());
	$smarty->assign('shop_country', $_CFG['shop_country']);
	$smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));
	$smarty->assign('goods_id', $goods_id);
	$position = assign_ur_here('gift_gard');
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $position['ur_here']);
	$smarty->assign('helps', get_shop_help());
	$smarty->display('take_view.dwt');
}
else if ($_REQUEST['act'] == 'check_take') {
	$goods_id = empty($_POST['goods_id']) ? 0 : intval($_POST['goods_id']);
	$gift_sn = isset($_SESSION['gift_sn']) && empty($_SESSION['gift_sn']) ? '' : dsc_addslashes($_SESSION['gift_sn']);

	if ($gift_sn) {
		$pwd = $db->getRow('SELECT * FROM ' . $ecs->table('user_gift_gard') . (' WHERE gift_sn = \'' . $gift_sn . '\' AND is_delete = 1'));

		if (!check_gift_login($_SESSION['gift_sn'], $pwd['gift_password'])) {
			$_SESSION['gift_sn'] = '';
			show_message($_LANG['gift_gard_used'], $_LANG['gift_gard_login'], 'gift_gard.php', 'error');
			exit();
		}
	}
	else {
		show_message($_LANG['gift_gard_overdue'], $_LANG['back_Last'], 'gift_gard.php', 'error');
	}

	$sql = 'SELECT gift_menory FROM ' . $ecs->table('gift_gard_type') . ' WHERE gift_id=\'' . $pwd['gift_id'] . '\'';
	$gift_type = $db->getRow($sql);

	if (empty($goods_id)) {
		ecs_header("Location: gift_gard.php?act=list\n");
		exit();
	}

	$user_time = gmtime();
	$country = empty($_POST['country']) ? 0 : intval($_POST['country']);
	$country = $db->getRow('SELECT region_name FROM ' . $ecs->table('region') . (' WHERE region_id=\'' . $country . '\' LIMIT 1'));
	$province = empty($_POST['province']) ? 0 : intval($_POST['province']);
	$province = $db->getRow('SELECT region_name FROM ' . $ecs->table('region') . (' WHERE region_id=\'' . $province . '\' LIMIT 1'));
	$city = empty($_POST['city']) ? 0 : intval($_POST['city']);
	$city = $db->getRow('SELECT region_name FROM ' . $ecs->table('region') . (' WHERE region_id=\'' . $city . '\' LIMIT 1'));
	$district = empty($_POST['district']) ? 0 : intval($_POST['district']);
	$city = $db->getRow('SELECT region_name FROM ' . $ecs->table('region') . (' WHERE region_id=\'' . $district . '\' LIMIT 1'));
	$street = empty($_POST['street']) ? 0 : intval($_POST['street']);
	$street = $db->getRow('SELECT region_name FROM ' . $ecs->table('region') . (' WHERE region_id=\'' . $street . '\' LIMIT 1'));
	$desc_address = empty($_POST['address']) ? '' : dsc_addslashes(trim($_POST['address']));
	$consignee = empty($_POST['consignee']) ? '' : dsc_addslashes(trim($_POST['consignee']));
	$mobile = empty($_POST['mobile']) ? '' : dsc_addslashes(trim($_POST['mobile']));
	$shipping_time = empty($_POST['shipping_time']) ? '' : dsc_addslashes(trim($_POST['shipping_time']));
	$address = '[' . $country['region_name'] . ' ' . $province['region_name'] . ' ' . $city['region_name'] . ' ' . $district['region_name'] . ' ' . ' ' . $street['region_name'] . '] ' . $desc_address;
	if (empty($country) || empty($province) || empty($city) || empty($district) || empty($desc_address) || empty($consignee) || empty($mobile)) {
		show_message($_LANG['delivery_Prompt'], $_LANG['delivery_again'], 'gift_gard.php', 'error');
	}

	$sql = 'UPDATE ' . $ecs->table('user_gift_gard') . (' SET user_id=\'' . $user_id . '\', goods_id=\'' . $goods_id . '\', user_time=\'' . $user_time . '\', address=\'' . $address . '\', consignee_name=\'' . $consignee . '\', mobile=\'' . $mobile . '\', shipping_time=\'' . $shipping_time . '\', status=\'1\'  WHERE gift_sn=\'' . $_SESSION['gift_sn'] . '\'');

	if ($db->query($sql)) {
		$_SESSION['gift_sn'] = '';
		show_message($_LANG['delivery_Success'], $_LANG['my_delivery'], 'user.php?act=take_list', 'success');
	}
	else {
		show_message($_LANG['delivery_fail'], $_LANG['delivery_again'], 'gift_gard.php', 'error');
	}
}
else if ($_REQUEST['act'] == 'edit_Consignee') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('message' => '', 'result' => '', 'qty' => 1);
	$address_id = isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0;
	$goods_id = isset($_REQUEST['goodsId']) ? intval($_REQUEST['goodsId']) : 0;

	if ($address_id == 0) {
		$consignee['country'] = 1;
		$consignee['province'] = 0;
		$consignee['city'] = 0;
	}

	$consignee = get_update_flow_Consignee($address_id);
	$smarty->assign('consignee', $consignee);
	$smarty->assign('country_list', get_regions());
	$smarty->assign('please_select', '请选择');
	$province_list = get_regions_log(1, $consignee['country']);
	$city_list = get_regions_log(2, $consignee['province']);
	$district_list = get_regions_log(3, $consignee['city']);
	$street_list = get_regions_log(4, $consignee['district']);
	$smarty->assign('province_list', $province_list);
	$smarty->assign('city_list', $city_list);
	$smarty->assign('district_list', $district_list);
	$smarty->assign('street_list', $street_list);
	$smarty->assign('goods_id', $goods_id);

	if ($_SESSION['user_id'] <= 0) {
		$result['error'] = 2;
		$result['message'] = $_LANG['lang_crowd_not_login'];
	}
	else {
		$result['error'] = 0;
		$result['content'] = $smarty->fetch('library/consignee_gift.lbi');
	}

	exit($json->encode($result));
}

?>
