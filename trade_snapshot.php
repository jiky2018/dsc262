<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_area.php';
get_request_filter();
$user_id = (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);
$action = (isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default');

if ($action == 'trade') {
	assign_template();
	$tradeId = (isset($_REQUEST['tradeId']) ? intval($_REQUEST['tradeId']) : 0);
	$snapshot = (isset($_REQUEST['snapshot']) ? true : false);
	$sql = ' SELECT * FROM ' . $ecs->table('trade_snapshot') . ' WHERE trade_id = \'' . $tradeId . '\' ';
	$row = $db->getRow($sql);
	$row['snapshot_time'] = local_date('Y-m-d H:i:s', $row['snapshot_time']);

	if (0 < $row['ru_id']) {
		$merchants_goods_comment = get_merchants_goods_comment($row['ru_id']);
		$smarty->assign('merch_cmt', $merchants_goods_comment);
	}
/*jinmengwangluo    2.1.7.2.2.9.8.8.9.2*/
	$shop_information = get_shop_name($row['ru_id']);
	$shop_information['kf_tel'] = $db->getOne('SELECT kf_tel FROM ' . $ecs->table('seller_shopinfo') . 'WHERE ru_id = \'' . $row['ru_id'] . '\'');

	if ($row['ru_id'] == 0) {
		if ($db->getOne('SELECT kf_im_switch FROM ' . $ecs->table('seller_shopinfo') . 'WHERE ru_id = 0')) {
			$shop_information['is_dsc'] = true;
		}
		else {
			$shop_information['is_dsc'] = false;
		}
	}
	else {
		$shop_information['is_dsc'] = false;
	}

	$smarty->assign('shop_information', $shop_information);
	$smarty->assign('page_title', $row['goods_name']);
	$smarty->assign('helps', get_shop_help());
	$smarty->assign('snapshot', $snapshot);
	$smarty->assign('goods', $row);
	$smarty->display('trade_snapshot.dwt');
}

?>
