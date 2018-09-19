<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_affiliate()
{
	$config = unserialize($GLOBALS['_CFG']['affiliate']);
	empty($config) && ($config = array());
	return $config;
}

function put_affiliate($config)
{
	$temp = serialize($config);
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('shop_config') . 'SET  value = \'' . $temp . '\'' . 'WHERE code = \'affiliate\'';
	$GLOBALS['db']->query($sql);
	clear_all_files();
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
admin_priv('affiliate');
$config = get_affiliate();

if ($_REQUEST['act'] == 'list') {
	assign_query_info();

	if (empty($_REQUEST['is_ajax'])) {
		$smarty->assign('full_page', 1);
	}

	$smarty->assign('ur_here', $_LANG['affiliate']);
	$smarty->assign('config', $config);
	$smarty->display('affiliate.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$smarty->assign('ur_here', $_LANG['affiliate']);
	$smarty->assign('config', $config);
	make_json_result($smarty->fetch('affiliate.dwt'), '', NULL);
}
else if ($_REQUEST['act'] == 'add') {
	if (count($config['item']) < 5) {
		$_POST['level_point'] = (double) $_POST['level_point'];
		$_POST['level_money'] = (double) $_POST['level_money'];
		$maxpoint = $maxmoney = 100;

		foreach ($config['item'] as $key => $val) {
			$maxpoint -= $val['level_point'];
			$maxmoney -= $val['level_money'];
		}

		($maxpoint < $_POST['level_point']) && $_POST['level_point'] = $maxpoint;
		($maxmoney < $_POST['level_money']) && $_POST['level_money'] = $maxmoney;
		if (!empty($_POST['level_point']) && (strpos($_POST['level_point'], '%') === false)) {
			$_POST['level_point'] .= '%';
		}

		if (!empty($_POST['level_money']) && (strpos($_POST['level_money'], '%') === false)) {
			$_POST['level_money'] .= '%';
		}

		$items = array('level_point' => $_POST['level_point'], 'level_money' => $_POST['level_money']);
		$links[] = array('text' => $_LANG['affiliate'], 'href' => 'affiliate.php?act=list');
		$config['item'][] = $items;
		$config['on'] = 1;
		$config['config']['separate_by'] = 0;
		put_affiliate($config);
	}
	else {
		make_json_error($_LANG['level_error']);
	}

	ecs_header("Location: affiliate.php?act=query\n");
	exit();
}
else if ($_REQUEST['act'] == 'updata') {
	$separate_by = (intval($_POST['separate_by']) == 1 ? 1 : 0);
	$_POST['expire'] = (double) $_POST['expire'];
	$_POST['level_point_all'] = (double) $_POST['level_point_all'];
	$_POST['level_money_all'] = (double) $_POST['level_money_all'];
	(100 < $_POST['level_money_all']) && $_POST['level_money_all'] = 100;
	(100 < $_POST['level_point_all']) && $_POST['level_point_all'] = 100;
	if (!empty($_POST['level_point_all']) && (strpos($_POST['level_point_all'], '%') === false)) {
		$_POST['level_point_all'] .= '%';
	}

	if (!empty($_POST['level_money_all']) && (strpos($_POST['level_money_all'], '%') === false)) {
		$_POST['level_money_all'] .= '%';
	}

	$_POST['level_register_all'] = intval($_POST['level_register_all']);
	$_POST['level_register_up'] = intval($_POST['level_register_up']);
	$temp = array();
	$temp['config'] = array('expire' => $_POST['expire'], 'expire_unit' => $_POST['expire_unit'], 'separate_by' => $separate_by, 'level_point_all' => $_POST['level_point_all'], 'level_money_all' => $_POST['level_money_all'], 'level_register_all' => $_POST['level_register_all'], 'level_register_up' => $_POST['level_register_up']);
	$temp['item'] = $config['item'];
	$temp['on'] = 1;
	put_affiliate($temp);
	$links[] = array('text' => $_LANG['affiliate'], 'href' => 'affiliate.php?act=list');
	sys_msg($_LANG['edit_ok'], 0, $links);
}
else if ($_REQUEST['act'] == 'on') {
	$on = (intval($_POST['on']) == 1 ? 1 : 0);
	$config['on'] = $on;
	put_affiliate($config);
	$links[] = array('text' => $_LANG['affiliate'], 'href' => 'affiliate.php?act=list');
	sys_msg($_LANG['edit_ok'], 0, $links);
}
else if ($_REQUEST['act'] == 'edit_point') {
	$key = trim($_POST['id']) - 1;
	$val = (double) trim($_POST['val']);
	$maxpoint = 100;

	foreach ($config['item'] as $k => $v) {
		if ($k != $key) {
			$maxpoint -= $v['level_point'];
		}
	}

	($maxpoint < $val) && ($val = $maxpoint);
	if (!empty($val) && (strpos($val, '%') === false)) {
		$val .= '%';
	}

	$config['item'][$key]['level_point'] = $val;
	$config['on'] = 1;
	put_affiliate($config);
	make_json_result(stripcslashes($val));
}
else if ($_REQUEST['act'] == 'edit_money') {
	$key = trim($_POST['id']) - 1;
	$val = (double) trim($_POST['val']);
	$maxmoney = 100;

	foreach ($config['item'] as $k => $v) {
		if ($k != $key) {
			$maxmoney -= $v['level_money'];
		}
	}

	($maxmoney < $val) && ($val = $maxmoney);
	if (!empty($val) && (strpos($val, '%') === false)) {
		$val .= '%';
	}

	$config['item'][$key]['level_money'] = $val;
	$config['on'] = 1;
	put_affiliate($config);
	make_json_result(stripcslashes($val));
}
else if ($_REQUEST['act'] == 'del') {
	$key = trim($_GET['id']) - 1;
	unset($config['item'][$key]);
	$temp = array();

	foreach ($config['item'] as $key => $val) {
		$temp[] = $val;
	}

	$config['item'] = $temp;
	$config['on'] = 1;
	$config['config']['separate_by'] = 0;
	put_affiliate($config);
	ecs_header("Location: affiliate.php?act=list\n");
	exit();
}

?>
