<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
if ($_REQUEST['act'] == 'merchants_first' || $_REQUEST['act'] == 'shop_top' || $_REQUEST['act'] == 'merchants_second') {
	$smarty->assign('action_type', 'index');
}
else {
	$smarty->assign('action_type', '');
}

$store_id = $_SESSION['stores_id'];
$ru_id = $GLOBALS['db']->getOne(' SELECT ru_id FROM ' . $GLOBALS['ecs']->table('offline_store') . (' WHERE id = \'' . $store_id . '\' '));

if ($_REQUEST['act'] == '') {
	header('location:goods.php?act=list');
	$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'stores_logo\'';
	$stores_logo = strstr($GLOBALS['db']->getOne($sql), 'images');
	$smarty->assign('stores_logo', $stores_logo);
	$smarty->display('index.dwt');
}
else if ($_REQUEST['act'] == 'upload_store_img') {
	$result = array('error' => 0, 'message' => '', 'content' => '');
	include_once ROOT_PATH . '/includes/cls_image.php';
	$image = new cls_image($_CFG['bgcolor']);

	if ($_FILES['img']['name']) {
		$dir = 'store_user';
		$img_name = $image->upload_image($_FILES['img'], $dir);

		if ($img_name) {
			$result['error'] = 1;
			$result['content'] = '../' . $img_name;
			$store_user_img = $GLOBALS['db']->getOne(' SELECT store_user_img FROM ' . $GLOBALS['ecs']->table('store_user') . (' WHERE id = \'' . $store_user_id . '\' '));
			@unlink('../' . $store_user_img);
			$sql = ' UPDATE ' . $GLOBALS['ecs']->table('store_user') . (' SET store_user_img = \'' . $img_name . '\' WHERE id = \'' . $store_user_id . '\' ');
			$GLOBALS['db']->query($sql);
		}
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'upload_stores_img') {
	$result = array('error' => 0, 'message' => '', 'content' => '');
	include_once ROOT_PATH . '/includes/cls_image.php';
	$image = new cls_image($_CFG['bgcolor']);

	if ($_FILES['stores_img']['name']) {
		$dir = 'offline_store';
		$img_name = $image->upload_image($_FILES['stores_img'], $dir);

		if ($img_name) {
			$result['error'] = 1;
			$result['content'] = '../' . $img_name;
			$stores_img = $GLOBALS['db']->getOne(' SELECT stores_img FROM ' . $GLOBALS['ecs']->table('offline_store') . (' WHERE id = \'' . $store_id . '\' '));
			@unlink('../' . $stores_img);
			$sql = ' UPDATE ' . $GLOBALS['ecs']->table('offline_store') . (' SET stores_img = \'' . $img_name . '\' WHERE id = \'' . $store_id . '\' ');
			$GLOBALS['db']->query($sql);
		}
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'clear_cache') {
	if (file_exists(ROOT_PATH . 'mobile/api/script/clear_cache.php')) {
		require_once ROOT_PATH . 'mobile/api/script/clear_cache.php';
	}

	clear_all_files('', STORES_PATH);
	sys_msg($_LANG['caches_cleared']);
}
else if ($_REQUEST['act'] == 'check_order') {
	$sql = 'SELECT order_id FROM ' . $GLOBALS['ecs']->table('store_back_order') . (' WHERE store_id = ' . $store_id);
	$back_order = $GLOBALS['db']->getAll($sql);
	$back_str = '';

	foreach ($back_order as $key => $val) {
		if ($key == 0) {
			$back_str .= $val['order_id'];
		}
		else {
			$back_str .= ',' . $val['order_id'];
		}
	}

	$where = '';
	$where .= ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi WHERE oi.main_order_id = o.order_id) = 0 ';
	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('store_order') . ' as o, ' . $GLOBALS['ecs']->table('order_info') . ' AS oi1 ' . ' WHERE (o.store_id = \'' . $_SESSION['stores_id'] . '\' OR (o.store_id = \'0\' AND o.is_grab_order = \'1\' AND FIND_IN_SET(\'' . $_SESSION['stores_id'] . '\',grab_store_list)))' . $where . (' AND oi1.order_id = o.order_id AND oi1.order_id NOT IN (' . $back_str . ')');
	$arr['new_orders'] = $db->getOne($sql);
	make_json_result('', '', $arr);
}

?>
