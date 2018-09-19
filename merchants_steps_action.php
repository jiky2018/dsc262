<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));
$user_id = $_SESSION['user_id'];
$step = (isset($_REQUEST['step']) ? htmlspecialchars(trim($_REQUEST['step'])) : '');
$sid = (isset($_REQUEST['sid']) ? intval($_REQUEST['sid']) : 1);
$agreement = (isset($_REQUEST['agreement']) ? intval($_REQUEST['agreement']) : 0);
$pid_key = (isset($_REQUEST['pid_key']) ? intval($_REQUEST['pid_key']) : 0);
$brandView = (isset($_REQUEST['brandView']) ? htmlspecialchars(trim($_REQUEST['brandView'])) : '');
$brandId = (isset($_REQUEST['brandId']) ? intval($_REQUEST['brandId']) : 0);
$search_brandType = (isset($_REQUEST['search_brandType']) ? htmlspecialchars($_REQUEST['search_brandType']) : '');
$searchBrandZhInput = (isset($_REQUEST['searchBrandZhInput']) ? htmlspecialchars(trim($_REQUEST['searchBrandZhInput'])) : '');
$searchBrandZhInput = (!empty($searchBrandZhInput) ? addslashes($searchBrandZhInput) : '');
$searchBrandEnInput = (isset($_REQUEST['searchBrandEnInput']) ? htmlspecialchars(trim($_REQUEST['searchBrandEnInput'])) : '');
$searchBrandEnInput = (!empty($searchBrandEnInput) ? addslashes($searchBrandEnInput) : '');

if ($user_id <= 0) {
	show_message($_LANG['steps_UserLogin'], $_LANG['UserLogin'], 'user.php');
	exit();
}

$sql = 'select agreement from ' . $ecs->table('merchants_steps_fields') . ' where user_id = \'' . $user_id . '\'';
$sf_agreement = $db->getOne($sql);

if ($sf_agreement != 1) {
	if ($agreement == 1) {
		$parent = array('user_id' => $user_id, 'agreement' => $agreement);
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_steps_fields'), $parent, 'INSERT');
	}
}
else {
	$shopTime_term = (isset($_REQUEST['shopTime_term']) ? intval($_REQUEST['shopTime_term']) : 0);
	if (($pid_key == 2) && ($step == 'stepTwo')) {
		$parent = array('shopTime_term' => $shopTime_term);
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_steps_fields'), $parent, 'UPDATE', 'user_id = \'' . $user_id . '\'');
	}

	$process_list = get_root_steps_process_list($sid);
	$process = $process_list[$pid_key];
	$noWkey = $pid_key - 1;
	$noWprocess = $process_list[$noWkey];
	$form = get_steps_title_insert_form($noWprocess['id']);
	$parent = get_setps_form_insert_date($form['formName']);
	$parent['site_process'] = !empty($parent['site_process']) ? addslashes($parent['site_process']) : $parent['site_process'];
	$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_steps_fields'), $parent, 'UPDATE', 'user_id = \'' . $user_id . '\'');

	if ($step == 'stepTwo') {
		if (!is_array($process)) {
			$step = 'stepThree';
			$pid_key = 0;
			$sid = $sid + 1;
		}
		else {
			$step = 'stepTwo';
			$pid_key = $pid_key;
		}
	}
	else if ($step == 'stepThree') {
		if (!is_array($process)) {
			$ec_rz_shopName = (isset($_REQUEST['ec_rz_shopName']) ? trim($_REQUEST['ec_rz_shopName']) : '');
			$ec_hopeLoginName = (isset($_REQUEST['ec_hopeLoginName']) ? trim($_REQUEST['ec_hopeLoginName']) : '');
			$sql = 'select user_id from ' . $ecs->table('merchants_shop_information') . ' where rz_shopName = \'' . $ec_rz_shopName . '\' AND user_id <> \'' . $_SESSION['user_id'] . '\'';

			if ($db->getOne($sql)) {
				show_message($_LANG['Settled_Prompt'], $_LANG['Return_last_step'], 'merchants_steps.php?step=' . $step . '&pid_key=' . $noWkey);
				exit();
			}
			else {
				$sql = 'update ' . $ecs->table('merchants_shop_information') . ' set steps_audit = 1' . ' where user_id = \'' . $_SESSION['user_id'] . '\'';
				$db->query($sql);
				$step = 'stepSubmit';
				$pid_key = 0;
			}

			$sql = 'select user_id from ' . $ecs->table('admin_user') . ' where user_name = \'' . $ec_hopeLoginName . '\' AND ru_id <> \'' . $_SESSION['user_id'] . '\'';

			if ($db->getOne($sql)) {
				show_message($_LANG['Settled_Prompt_name'], $_LANG['Return_last_step'], 'merchants_steps.php?step=' . $step . '&pid_key=' . $noWkey);
				exit();
			}
			else {
				$sql = 'update ' . $ecs->table('merchants_shop_information') . ' set steps_audit = 1' . ' where user_id = \'' . $_SESSION['user_id'] . '\'';
				$db->query($sql);
				$step = 'stepSubmit';
				$pid_key = 0;
			}
		}
	}
}

if (empty($step)) {
	$step = 'stepOne';
}

$act = '';

if ($brandView == 'brandView') {
	$pid_key -= 1;
}
else if ($brandView == 'add_brand') {
	if (0 < $brandId) {
		$act .= '&brandId=' . $brandId . '&search_brandType=' . $search_brandType;
	}

	if ($searchBrandZhInput != '') {
		$act .= '&searchBrandZhInput=' . $searchBrandZhInput;
	}

	if ($searchBrandEnInput != '') {
		$act .= '&searchBrandEnInput=' . $searchBrandEnInput;
	}

	$act .= '&brandView=brandView';
}

$steps_site = 'merchants_steps.php?step=' . $step . '&pid_key=' . $pid_key . $act;
$sql = ' select site_process from ' . $ecs->table('merchants_steps_fields') . ' where user_id = \'' . $user_id . '\'';
$site_process = $db->getOne($sql);
$strpos = strpos($site_process, $steps_site);

if ($strpos === false) {
	if (!empty($site_process)) {
		$site_process .= ',' . $steps_site;
	}
	else {
		$site_process = $steps_site;
	}

	$sql = 'update ' . $ecs->table('merchants_steps_fields') . ' set steps_site = \'' . $steps_site . '\', site_process = \'' . $site_process . '\' where user_id = \'' . $user_id . '\'';
	$db->query($sql);
}

ecs_header('Location: ' . $steps_site . "\n");
exit();

?>
