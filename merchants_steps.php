<?php
//QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
include_once 'includes/cls_json.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

require ROOT_PATH . '/includes/lib_area.php';
$brand_name = !isset($_REQUEST['searchBrandZhInput']) ? '' : htmlspecialchars(trim($_REQUEST['searchBrandZhInput']));
$brand_letter = !isset($_REQUEST['searchBrandEnInput']) ? '' : htmlspecialchars(trim($_REQUEST['searchBrandEnInput']));
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));
$step = isset($_REQUEST['step']) ? htmlspecialchars(trim($_REQUEST['step'])) : '';
$sid = isset($_REQUEST['sid']) ? intval($_REQUEST['sid']) : 1;
$pid_key = isset($_REQUEST['pid_key']) ? intval($_REQUEST['pid_key']) : 0;
$ec_shop_bid = isset($_REQUEST['ec_shop_bid']) ? intval($_REQUEST['ec_shop_bid']) : 0;
$brandView = isset($_REQUEST['brandView']) ? htmlspecialchars(trim($_REQUEST['brandView'])) : '';
$user_id = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$smarty->assign('helps', get_shop_help());
$brandId = isset($_REQUEST['brandId']) && !empty($_REQUEST['brandId']) ? intval($_REQUEST['brandId']) : 0;
$smarty->assign('brandId', $brandId);

if (empty($sid)) {
	$sid = 1;
}

if ($step == 'addChildCate') {
	$cat_id = isset($_REQUEST['cat_id']) ? trim($_REQUEST['cat_id']) : 0;
	$type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
	$json = new JSON();
	$result = array('error' => 0, 'message' => '', 'content' => '', 'cat_id' => '');

	if (0 < $user_id) {
		if ($type == 1) {
			$_POST['cateArr'] = strip_tags(urldecode($_POST['cateArr']));
			$_POST['cateArr'] = json_str_iconv($_POST['cateArr']);
			$cat = $json->decode($_POST['cateArr']);
			$catarr = $cat->cat_id;
		}

		$cate_list = get_first_cate_list($cat_id, $type, $catarr, $user_id);

		if (!$cat_id) {
			$cate_list = array();
		}

		$smarty->assign('cate_list', $cate_list);
		$smarty->assign('cat_id', $cat_id);
		$result['content'] = $smarty->fetch('library/merchants_cate_list.lbi');

		if ($type == 1) {
			$result['type'] = $type;
			$category_info = get_fine_category_info(0, $user_id);
			$smarty->assign('category_info', $category_info);
			$result['cate_checked'] = $smarty->fetch('library/merchants_cate_checked_list.lbi');
			$permanent_list = get_category_permanent_list($user_id);
			$smarty->assign('permanent_list', $permanent_list);
			$result['catePermanent'] = $smarty->fetch('library/merchants_steps_catePermanent.lbi');
		}
	}
	else {
		$result['error'] = 1;
		$result['message'] = $_LANG['login_again'];
	}

	exit($json->encode($result));
}
else if ($step == 'addChildCate_checked') {
	$json = new JSON();
	$result = array('error' => 0, 'message' => '', 'content' => '', 'cat_id' => '');

	if (0 < $user_id) {
		$_POST['cat_id'] = strip_tags(urldecode($_POST['cat_id']));
		$_POST['cat_id'] = json_str_iconv($_POST['cat_id']);
		$cat = $json->decode($_POST['cat_id']);
		$child_category = get_child_category($cat->cat_id);
		$category_info = get_fine_category_info($child_category['cat_id'], $user_id);
		$smarty->assign('category_info', $category_info);
		$result['content'] = $smarty->fetch('library/merchants_cate_checked_list.lbi');
		$permanent_list = get_category_permanent_list($user_id);
		$smarty->assign('permanent_list', $permanent_list);
		$result['catePermanent'] = $smarty->fetch('library/merchants_steps_catePermanent.lbi');
	}
	else {
		$result['error'] = 1;
		$result['message'] = $_LANG['login_again'];
	}

	exit($json->encode($result));
}
else if ($step == 'deleteChildCate_checked') {
	$ct_id = isset($_REQUEST['ct_id']) ? trim($_REQUEST['ct_id']) : '';
	$json = new JSON();
	$result = array('error' => 0, 'message' => '', 'content' => '', 'cat_id' => '');

	if (0 < $user_id) {
		$catParent = get_temporarydate_ctId_catParent($ct_id);

		if ($catParent['num'] == 1) {
			$sql = 'delete from ' . $ecs->table('merchants_dt_file') . ' where cat_id = \'' . $catParent['parent_id'] . '\'';
			$db->query($sql);
		}

		$sql = 'delete from ' . $ecs->table('merchants_category_temporarydate') . (' where ct_id = \'' . $ct_id . '\'');
		$db->query($sql);
		$category_info = get_fine_category_info(0, $user_id);
		$smarty->assign('category_info', $category_info);
		$result['content'] = $smarty->fetch('library/merchants_cate_checked_list.lbi');
		$permanent_list = get_category_permanent_list($user_id);
		$smarty->assign('permanent_list', $permanent_list);
		$result['catePermanent'] = $smarty->fetch('library/merchants_steps_catePermanent.lbi');
	}
	else {
		$result['error'] = 1;
		$result['message'] = $_LANG['login_again'];
	}

	exit($json->encode($result));
}
else if ($step == 'brandSearch_cn_en') {
	$json = new JSON();
	$result = array('err_msg' => '', 'err_no' => 0, 'content' => '');
	$type = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);
	$value = empty($_REQUEST['value']) ? '' : htmlspecialchars(trim($_REQUEST['value']));
	$brand_list = get_merchants_search_brand($value, $type);
	$smarty->assign('type', $type);
	$smarty->assign('brand_list', $brand_list);

	if ($brand_list) {
		$result['err_no'] = 1;
	}

	$result['type'] = $type;
	$result['content'] = $smarty->fetch('library/brank_type_search.lbi');
	exit($json->encode($result));
}
else if ($step == 'brandSearch_info') {
	$json = new JSON();
	$result = array('err_msg' => '', 'err_no' => 0, 'content' => '');
	$brand_id = empty($_REQUEST['brand_id']) ? 0 : intval($_REQUEST['brand_id']);
	$brand_type = empty($_REQUEST['brand_type']) ? '' : htmlspecialchars($_REQUEST['brand_type']);
	$submit = !isset($_REQUEST['submit']) ? '' : htmlspecialchars($_REQUEST['submit']);
	$result = get_merchants_search_brand($brand_id, 2, $brand_type, $brand_name, $brand_letter);

	if (!empty($submit)) {
		if ($result) {
			$result['brand_not'] = $_LANG['brand_in'];
			$result['err_no'] = 1;
		}
		else {
			$result['brand_not'] = $_LANG['brand_not'];
			$result['err_no'] = 0;
		}
	}

	$result['brand_type'] = $brand_type;
	exit($json->encode($result));
}

if ($user_id <= 0) {
	show_message($_LANG['steps_UserLogin'], $_LANG['UserLogin'], 'user.php');
	exit();
}

$sql = 'SELECT steps_audit FROM ' . $ecs->table('merchants_shop_information') . (' WHERE user_id = \'' . $user_id . '\'');
$steps_audit = $db->getOne($sql);

if ($steps_audit == 1) {
	assign_template();
	$position = assign_ur_here();
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $position['ur_here']);
	$step = 'stepSubmit';
	$smarty->assign('pid_key', 0);
	$smarty->assign('step', $step);
	$sql = 'SELECT shoprz_brandName, shopNameSuffix, shop_class_keyWords, hopeLoginName, merchants_audit, merchants_message, steps_audit FROM ' . $ecs->table('merchants_shop_information') . (' WHERE user_id = \'' . $user_id . '\'');
	$shop_info = $db->getRow($sql);
	$shop_info['rz_shopName'] = str_replace('|', '', $shop_info['rz_shopName']);
	$shop_info['shop_name'] = get_shop_name($user_id, 1);
	$smarty->assign('shop_info', $shop_info);
	$smarty->display('merchants_steps.dwt');
	exit();
}

if ($_REQUEST['del'] == 'deleteBrand') {
	$sql = 'DELETE FROM ' . $ecs->table('merchants_shop_brand') . (' WHERE bid = \'' . $ec_shop_bid . '\'');
	$db->query($sql);
}

$b_fid = isset($_REQUEST['del_bFid']) ? intval($_REQUEST['del_bFid']) : 0;

if (0 < $b_fid) {
	$sql = 'delete from ' . $ecs->table('merchants_shop_brandfile') . (' where b_fid = \'' . $b_fid . '\'');
	$db->query($sql);
}

$sql = 'select fid from ' . $ecs->table('merchants_steps_fields') . (' where user_id = \'' . $user_id . '\'');
$fid = $db->getOne($sql);
if ($fid <= 0 && ($_REQUEST['step'] == 'stepTwo' || $_REQUEST['step'] == 'stepThree' || $_REQUEST['step'] == 'stepSubmit')) {
	ecs_header("Location: merchants.php\n");
	exit();
}
else if (0 < $fid) {
	if ($step != 'stepThree' && $step != 'stepSubmit') {
		$step = 'stepTwo';
	}
}

if (!empty($step) && $step == 'stepTwo') {
	$sid = 2;
}
else {
	if (!empty($step) && $step == 'stepThree') {
		$sid = 3;
	}
	else {
		if (!empty($step) && $step == 'stepSubmit') {
			$sid = 4;
			$sql = 'select shoprz_brandName, shopNameSuffix, shop_class_keyWords, hopeLoginName, merchants_audit, steps_audit from ' . $ecs->table('merchants_shop_information') . (' where user_id = \'' . $user_id . '\'');
			$shop_info = $db->getRow($sql);
			$shop_info['rz_shopName'] = str_replace('|', '', $shop_info['rz_shopName']);
			$smarty->assign('shop_info', $shop_info);
		}
	}
}

if (!$smarty->is_cached('merchants_steps.dwt')) {
	assign_template();
	$position = assign_ur_here();
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $position['ur_here']);
	$smarty->assign('step', $step);
	$smarty->assign('sid', $sid);
	if (1 < $sid && $sid < 4) {
		$sql = 'delete from ' . $ecs->table('merchants_category_temporarydate') . (' where user_id = \'' . $user_id . '\' and is_add = 0');
		$db->query($sql);
		$consignee['country'] = 1;
		$consignee['province'] = 0;
		$consignee['city'] = 0;
		$country_list = get_regions_steps();
		$province_list = get_regions_steps(1, $consignee['country']);
		$city_list = get_regions_steps(2, $consignee['province']);
		$district_list = get_regions_steps(3, $consignee['city']);
		$sn = 0;
		$smarty->assign('country_list', $country_list);
		$smarty->assign('province_list', $province_list);
		$smarty->assign('city_list', $city_list);
		$smarty->assign('district_list', $district_list);
		$smarty->assign('consignee', $consignee);
		$smarty->assign('sn', $sn);
		$process_list = get_root_steps_process_list($sid);
		$process = $process_list[$pid_key];

		if (!$process_list) {
			$Location = 'merchants_steps.php?step=stepThree&pid_key=' . $pid_key;
			ecs_header('Location: ' . $Location . "\n");
			exit();
		}

		if ($process['process_title'] == '添加品牌') {
			$smarty->assign('b_pidKey', $pid_key);
			$smarty->assign('ec_shop_bid', $ec_shop_bid);

			if ($brandView == 'brandView') {
				$smarty->assign('pid_key', $pid_key + 1);
			}
			else {
				$smarty->assign('pid_key', $pid_key + 2);
			}

			if ($step == 'stepThree' && $pid_key == 2) {
				$smarty->assign('brandKey', $pid_key + 1);
			}
		}
		else if ($process['process_title'] == '新增品牌') {
			$smarty->assign('pid_key', $pid_key - 1);
		}
		else {
			$smarty->assign('pid_key', $pid_key + 1);
		}

		$smarty->assign('process', $process);
		$smarty->assign('brandView', $brandView);
		$smarty->assign('choose_process', $GLOBALS['_CFG']['choose_process']);

		if (0 < $process['id']) {
			$category_info = get_fine_category_info(0, $user_id);
			$smarty->assign('category_info', $category_info);
			$smarty->assign('category_count', count($category_info));
			$permanent_list = get_category_permanent_list($user_id);
			$smarty->assign('permanent_list', $permanent_list);
			$steps_title = get_root_merchants_steps_title($process['id'], $user_id);
			$smarty->assign('steps_title', $steps_title);
		}
	}
	else if ($sid == 1) {
		$merchants_steps = get_root_directory_steps($sid);
		$smarty->assign('steps', $merchants_steps);
	}

	assign_dynamic('merchants_steps');
}

$ec_brandFirstChar = !empty($brand_letter) ? strtoupper(substr($brand_letter, 0, 1)) : '';
$smarty->assign('brand_name', $brand_name);
$smarty->assign('brand_letter', $brand_letter);
$smarty->assign('ec_brandFirstChar', $ec_brandFirstChar);
$smarty->display('merchants_steps.dwt');

?>
