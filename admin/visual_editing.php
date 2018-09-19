<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_visual.php';
admin_priv('10_visual_editing');
$adminru = get_admin_ru_id();
$smarty->assign('ru_id', $adminru['ru_id']);
$allow_file_types = '|PNG|JPG|GIF|GPEG|';

if ($_REQUEST['act'] == 'templates') {
	$smarty->assign('ur_here', $_LANG['10_seller_template']);
	$smarty->assign('action_link', array('text' => '返回', 'href' => 'merchants_users_list.php?act=list'));
	$id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$smarty->assign('users', get_table_date('merchants_shop_information', 'user_id=\'' . $id . '\'', array('user_id', 'hopeLoginName', 'merchants_audit')));
	$smarty->assign('menu_select', array('action' => 'seller_shopinfo', 'current' => 'templates', 'action' => 'allot'));
	$sql = 'SELECT seller_templates FROM' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id=' . $id;
	$default_tem = $GLOBALS['db']->getOne($sql);
	$available_templates = array();
	$dir = ROOT_PATH . 'data/seller_templates/seller_tem_' . $id . '/';

	if (file_exists($dir)) {
		$template_dir = @opendir($dir);

		while ($file = readdir($template_dir)) {
			if (($file != '.') && ($file != '..') && ($file != '.svn') && ($file != 'index.htm')) {
				$available_templates[] = get_seller_template_info($file, $id);
			}
		}

		$available_templates = get_array_sort($available_templates, 'sort');
		@closedir($template_dir);
	}

	if (!empty($available_templates)) {
		$smarty->assign('available_templates', $available_templates);
		$smarty->assign('ru_id', $id);
		$smarty->assign('default_tem', $default_tem);
		$smarty->display('templates.dwt');
	}
	else {
		$link[0]['text'] = '返回列表';
		$link[0]['href'] = 'merchants_users_list.php?act=list';
		sys_msg('该商家暂无模板', 1, $link);
	}
}
else if ($_REQUEST['act'] == 'template_information') {
	$smarty->assign('ur_here', '模板信息');
	$id = (isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0);
	$tem = (isset($_REQUEST['tem']) ? addslashes($_REQUEST['tem']) : '');
	$smarty->assign('action_link', array('text' => '返回', 'href' => 'visual_editing.php?act=templates&&id=' . $id));

	if ($tem) {
		$smarty->assign('template', get_seller_template_info($tem, $id));
	}

	$smarty->assign('tem', $tem);
	$smarty->assign('ru_id', $id);
	$smarty->display('template_information.dwt');
}
else if ($_REQUEST['act'] == 'edit_information') {
	$id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$tem = (isset($_REQUEST['tem']) ? addslashes($_REQUEST['tem']) : '');
	$name = (isset($_REQUEST['name']) ? 'tpl name：' . addslashes($_REQUEST['name']) : 'tpl name：');
	$version = (isset($_REQUEST['version']) ? 'version：' . addslashes($_REQUEST['version']) : 'version：');
	$author = (isset($_REQUEST['author']) ? 'author：' . addslashes($_REQUEST['author']) : 'author：');
	$author_url = (isset($_REQUEST['author_url']) ? 'author url：' . $_REQUEST['author_url'] : 'author url：');
	$description = (isset($_REQUEST['description']) ? 'description：' . addslashes($_REQUEST['description']) : 'description：');
	$file_url = '';
	$file_dir = '../data/seller_templates/seller_tem_' . $id . '/' . $tem;

	if (!is_dir($file_dir)) {
		mkdir($file_dir, 511, true);
	}

	if ((isset($_FILES['ten_file']['error']) && ($_FILES['ten_file']['error'] == 0)) || (!isset($_FILES['ten_file']['error']) && isset($_FILES['ten_file']['tmp_name']) && ($_FILES['ten_file']['tmp_name'] != 'none'))) {
		if (!check_file_type($_FILES['ten_file']['tmp_name'], $_FILES['ten_file']['name'], $allow_file_types)) {
			sys_msg('图片格式不正确');
		}

		$ext = array_pop(explode('.', $_FILES['ten_file']['name']));
		$file_name = $file_dir . '/screenshot' . '.' . $ext;

		if (move_upload_file($_FILES['ten_file']['tmp_name'], $file_name)) {
			$file_url = $file_name;
		}
	}

	if ($file_url == '') {
		$file_url = $_POST['textfile'];
	}

	if ((isset($_FILES['big_file']['error']) && ($_FILES['big_file']['error'] == 0)) || (!isset($_FILES['big_file']['error']) && isset($_FILES['big_file']['tmp_name']) && ($_FILES['big_file']['tmp_name'] != 'none'))) {
		if (!check_file_type($_FILES['big_file']['tmp_name'], $_FILES['big_file']['name'], $allow_file_types)) {
			sys_msg('图片格式不正确');
		}

		$ext = array_pop(explode('.', $_FILES['big_file']['name']));
		$file_name = $file_dir . '/template' . '.' . $ext;

		if (move_upload_file($_FILES['big_file']['tmp_name'], $file_name)) {
			$big_file = $file_name;
		}
	}

	$end = '------tpl_info------------';
	$tab = "\n";
	$html = $end . $tab . $name . $tab . 'tpl url：' . $file_url . $tab . $description . $tab . $version . $tab . $author . $tab . $author_url . $tab . $end;
	$html = write_static_file_cache('tpl_info', iconv('UTF-8', 'GB2312', $html), 'txt', $file_dir . '/');

	if ($html === false) {
		sys_msg('\' . ' . $file_dir . ' . \'/tpl_info.txt没有写入权限，请修改权限');
	}
	else {
		$link[0]['text'] = '返回列表';
		$link[0]['href'] = 'visual_editing.php?act=templates&id=' . $id;
		sys_msg('修改成功', 0, $link);
	}
}
else if ($_REQUEST['act'] == 'removeTemplate') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'content' => '', 'url' => '');
	$code = (isset($_REQUEST['code']) ? addslashes($_REQUEST['code']) : '');
	$ru_id = (isset($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : 0);
	$dir = ROOT_PATH . 'data/seller_templates/seller_tem_' . $ru_id . '/' . $code;
	$rmdir = del_DirAndFile($dir);

	if ($rmdir == true) {
		$result['error'] = 0;
		$result['url'] = 'visual_editing.php?act=templates&id=' . $ru_id;
	}
	else {
		$result['error'] = 1;
		$result['content'] = '系统出错，请重试！';
	}

	exit(json_encode($result));
}

?>
