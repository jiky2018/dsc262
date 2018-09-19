<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('action_type', 'index');
$adminru = get_admin_ru_id();
$sql = 'select seller_theme from ' . $ecs->table('seller_shopinfo') . ' where ru_id=\'' . $adminru['ru_id'] . '\'';
$seller_theme = $db->getOne($sql);

if ($_REQUEST['act'] == 'first') {
	$smarty->assign('primary_cat', $_LANG['19_merchants_store']);
	admin_priv('seller_store_other');
	$sql = 'select * from ' . $ecs->table('seller_shopbg') . ' where ru_id=\'' . $adminru['ru_id'] . '\' and seller_theme=\'' . $seller_theme . '\'';
	$seller_shopbg = $db->getRow($sql);
	$action = 'add';

	if ($seller_shopbg) {
		$action = 'update';
	}

	if ($seller_shopbg['bgimg']) {
		$seller_shopbg['bgimg'] = '../' . $seller_shopbg['bgimg'];
	}

	$smarty->assign('shop_bg', $seller_shopbg);
	$smarty->assign('data_op', $action);
	assign_query_info();
	$smarty->assign('current', 'seller_shop_bg');
	$smarty->assign('ur_here', '设置店铺背景');
	$smarty->display('seller_shopbg.dwt');
}
else if ($_REQUEST['act'] == 'second') {
	$bgrepeat = (empty($_POST['bgrepeat']) ? 'no-repeat' : trim($_POST['bgrepeat']));
	$bgcolor = (empty($_POST['bgcolor']) ? '' : trim($_POST['bgcolor']));
	$show_img = (empty($_POST['show_img']) ? '0' : intval($_POST['show_img']));
	$is_custom = (empty($_POST['is_custom']) ? '0' : intval($_POST['is_custom']));
	$data_op = (empty($_POST['data_op']) ? '' : trim($_POST['data_op']));
	$shop_bg = array('ru_id' => $adminru['ru_id'], 'seller_theme' => $seller_theme, 'bgrepeat' => $bgrepeat, 'bgcolor' => $bgcolor, 'show_img' => $show_img, 'is_custom' => $is_custom);
	$allow_file_types = '|GIF|JPG|PNG|BMP|';

	if ($_FILES['bgimg']) {
		$file = $_FILES['bgimg'];
		if ((isset($file['error']) && ($file['error'] == 0)) || (!isset($file['error']) && ($file['tmp_name'] != 'none'))) {
			if (!check_file_type($file['tmp_name'], $file['name'], $allow_file_types)) {
				sys_msg(sprintf($_LANG['msg_invalid_file'], $file['name']));
			}
			else {
				$ext = array_pop(explode('.', $file['name']));
				$file_name = '../seller_imgs/seller_bg_img/seller_bg_' . $seller_theme . '_' . $adminru['ru_id'] . '.' . $ext;

				if (move_upload_file($file['tmp_name'], $file_name)) {
					$shop_bg['bgimg'] = str_replace('../', '', $file_name);
				}
				else {
					sys_msg(sprintf($_LANG['msg_upload_failed'], $file['name'], 'seller_imgs/seller_' . $adminru['ru_id']));
				}
			}
		}
	}

	get_oss_add_file(array($shop_bg['bgimg']));

	if ($data_op == 'add') {
		$res = $db->autoExecute($ecs->table('seller_shopbg'), $shop_bg, 'INSERT');
		$lnk[] = array('text' => $_LANG['go_back'], 'href' => 'seller_shop_bg.php?act=first');
		sys_msg('编辑店铺背景成功', 0, $lnk);
	}
	else {
		$db->autoExecute($ecs->table('seller_shopbg'), $shop_bg, 'update', 'ru_id=\'' . $adminru['ru_id'] . '\' and seller_theme=\'' . $seller_theme . '\'');
		$lnk[] = array('text' => $_LANG['go_back'], 'href' => 'seller_shop_bg.php?act=first');
		sys_msg('更新店铺背景成功', 0, $lnk);
	}
}

?>
