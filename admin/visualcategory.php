<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_visual.php';
admin_priv('visualcategory');

if ($_REQUEST['act'] == 'list') {
	$available_templates = array();
	$dir = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'] . '/';

	if (file_exists($dir)) {
		$template_dir = @opendir($dir);

		while ($file = readdir($template_dir)) {
			if (($file != '.') && ($file != '..') && ($file != '.svn') && ($file != 'index.htm')) {
				$available_templates[] = get_seller_template_info2($file, 0, $GLOBALS['_CFG']['template']);
			}
		}

		$available_templates = get_array_sort($available_templates, 'sort');
		@closedir($template_dir);
	}
	//print_r($available_templates);exit;
	$template_type = 'category_top';
	/*$sql = 'SELECT value FROM' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code= \'hometheme\' AND store_range = \'' . $GLOBALS['_CFG']['template'] . '\'';
	$default_tem = $GLOBALS['db']->getOne($sql);
	$smarty->assign('default_tem', $default_tem);*/
	$smarty->assign('template_type', $template_type);
	$smarty->assign('available_templates', $available_templates);
	$smarty->assign('full_page', 1);
	$smarty->display('visualcategory_list.dwt');
}
else if ($_REQUEST['act'] == 'visual') {
	$des = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'];

	$code = (isset($_REQUEST['code']) && !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '');
	$templates =(!empty($_REQUEST['templates']) ? trim($_REQUEST['templates']) : '');
	$smarty->assign('templates', $templates);
	/*if (empty($code)) {
		$sql = 'SELECT value FROM' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code= \'hometheme\' AND store_range = \'' . $GLOBALS['_CFG']['template'] . '\'';
		$code = $GLOBALS['db']->getOne($sql, true);
	}*/
	
	get_down_categorytemplates($code);
	
	if (!file_exists($des . '/' . $code . '/nav_html.php') && !file_exists($des . '/' . $code . '/temp/nav_html.php')) {
		$sql = 'SELECT id, name, ifshow, vieworder, opennew, url, type ' . ' FROM ' . $GLOBALS['ecs']->table('nav') . 'WHERE type = \'middle\'';
		$navigator = $db->getAll($sql);
		$smarty->assign('navigator', $navigator);
	}

	$filename = '';
	$is_temp = 0;

	if (file_exists($des . '/' . $code . '/temp/pc_page.php')) {
		$filename = $des . '/' . $code . '/temp/pc_page.php';
		$is_temp = 1;
	}
	else {
		$filename = $des . '/' . $code . '/pc_page.php';
	}
	
	$arr['tem'] = $code;
	$arr['out'] = get_html_file($filename);

	$replace_data = array('http://localhost/ecmoban_dsc2.0.5_20170518/', 'http://localhost/ecmoban_dsc2.2.6_20170727/', 'http://localhost/ecmoban_dsc2.3/');
	$arr['out'] = str_replace($replace_data, $ecs->url(), $arr['out']);
	$content = getleft_attr('content', 0, $arr['tem'], $GLOBALS['_CFG']['template']);
	$bonusadv = getleft_attr('bonusadv', 0, $arr['tem'], $GLOBALS['_CFG']['template']);
	$bonusadv['img_file'] = get_image_path(0, $bonusadv['img_file'], true, '', '', true);
	$smarty->assign('content', $content);
	$smarty->assign('bonusadv', $bonusadv);
	
	$smarty->assign('pc_page', $arr);
	$smarty->assign('is_temp', $is_temp);
	$smarty->assign('shop_name', $_CFG['shop_name']);
	$smarty->assign('home', 'home');
	$smarty->assign('vis_section', 'vis_home');
	$smarty->display('visualcategory.dwt');
}
else if ($_REQUEST['act'] == 'file_put_visual') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('suffix' => '', 'error' => '');
	$temp = (isset($_REQUEST['temp']) ? intval($_REQUEST['temp']) : 0);
	$content = (isset($_REQUEST['content']) ? unescape($_REQUEST['content']) : '');
	$content = (!empty($content) ? stripslashes($content) : '');
	$content_html = (isset($_REQUEST['content_html']) ? unescape($_REQUEST['content_html']) : '');
	$content_html = (!empty($content_html) ? stripslashes($content_html) : '');
	$des = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'];
	$suffix = (!empty($_REQUEST['suffix']) ? addslashes($_REQUEST['suffix']) : get_new_dirName(0, $des));
	$pc_page_name = 'pc_page.php';
	
	if ($temp == 1) {
		$pc_html_name = 'nav_html.php';
	}
	else if ($temp == 2) {
		$pc_html_name = 'topBanner.php';
	}
	else {
		$pc_html_name = 'pc_html.php';
	}

	$create_html = create_html($content_html, $adminru['ru_id'], $pc_html_name, $suffix, 4);
	$create = create_html($content, $adminru['ru_id'], $pc_page_name, $suffix, 4);
	$result['error'] = 0;
	$result['suffix'] = $suffix;
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'edit_information') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('suffix' => '', 'error' => '');
	$allow_file_types = '|GIF|JPG|PNG|';
	include_once ROOT_PATH . '/includes/cls_image.php';
	$image = new cls_image($_CFG['bgcolor']);
	$check = (!empty($_REQUEST['check']) ? intval($_REQUEST['check']) : 0);
	$tem = (isset($_REQUEST['tem']) ? addslashes($_REQUEST['tem']) : '');
	$name = (isset($_REQUEST['name']) ? 'tpl name：' . addslashes($_REQUEST['name']) : 'tpl name：');
	$version = (isset($_REQUEST['version']) ? 'version：' . addslashes($_REQUEST['version']) : 'version：');
	$author = (isset($_REQUEST['author']) ? 'author：' . addslashes($_REQUEST['author']) : 'author：');
	$author_url = (isset($_REQUEST['author_url']) ? 'author_uri：' . $_REQUEST['author_url'] : 'author_uri：');
	$description = (isset($_REQUEST['description']) ? 'description：' . addslashes($_REQUEST['description']) : 'description：');
	$template_type = (!empty($_REQUEST['template_type']) ? trim($_REQUEST['template_type']) : '');
	$temp_id = (!empty($_REQUEST['temp_id']) ? intval($_REQUEST['temp_id']) : 0);
	$temp_mode = (!empty($_REQUEST['temp_mode']) ? intval($_REQUEST['temp_mode']) : 0);
	$temp_cost = (!empty($_REQUEST['temp_cost']) ? trim($_REQUEST['temp_cost']) : 0);
	$temp_cost = floatval($temp_cost);

	if ($template_type == 'seller') {
		$des = ROOT_PATH . 'data/seller_templates/seller_tem';
	}
	else {
		$des = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'];
	}
	
	if ($tem == '') {
		$tem = get_new_dirName(0, $des);
		$code_dir = $des . '/' . $tem;

		if (!is_dir($code_dir)) {
			make_dir($code_dir);
		}
	}

	$file_url = '';
	$format = array('png', 'gif', 'jpg');
	$file_dir = $des . '/' . $tem;

	if (!is_dir($file_dir)) {
		make_dir($file_dir);
	}
	
	if ((isset($_FILES['ten_file']['error']) && ($_FILES['ten_file']['error'] == 0)) || (!isset($_FILES['ten_file']['error']) && isset($_FILES['ten_file']['tmp_name']) && ($_FILES['ten_file']['tmp_name'] != 'none'))) {
		if (!check_file_type($_FILES['ten_file']['tmp_name'], $_FILES['ten_file']['name'], $allow_file_types)) {
			$result['error'] = 1;
			$result['message'] = '图片格式不正确';
			exit(json_encode($result));
		}

		if ($_FILES['ten_file']['name']) {
			$ext_cover = explode('.', $_FILES['ten_file']['name']);
			$ext_cover = array_pop($ext_cover);
		}
		else {
			$ext_cover = '';
		}

		$file_name = $file_dir . '/screenshot' . '.' . $ext_cover;

		if (move_upload_file($_FILES['ten_file']['tmp_name'], $file_name)) {
			$file_url = $file_name;
		}
	}

	if ($file_url == '') {
		$file_url = $_POST['big_file_textfile'];
	}

	if ((isset($_FILES['big_file']['error']) && ($_FILES['big_file']['error'] == 0)) || (!isset($_FILES['big_file']['error']) && isset($_FILES['big_file']['tmp_name']) && ($_FILES['big_file']['tmp_name'] != 'none'))) {
		if (!check_file_type($_FILES['big_file']['tmp_name'], $_FILES['big_file']['name'], $allow_file_types)) {
			$result['error'] = 1;
			$result['message'] = '图片格式不正确';
			exit(json_encode($result));
		}

		if ($_FILES['big_file']['name']) {
			$ext_big = explode('.', $_FILES['big_file']['name']);
			$ext_big = array_pop($ext_big);
		}
		else {
			$ext_big = '';
		}

		$file_name = $file_dir . '/template' . '.' . $ext_big;

		if (move_upload_file($_FILES['big_file']['tmp_name'], $file_name)) {
			$big_file = $file_name;
		}
	}

	$template_dir_img = @opendir($file_dir);

	while ($file = readdir($template_dir_img)) {
		foreach ($format as $val) {
			if (($val != $ext_cover) && ($ext_cover != '')) {
				if (file_exists($file_dir . '/screenshot.' . $val)) {
					@unlink($file_dir . '/screenshot.' . $val);
				}
			}

			if (($val != $ext_big) && ($ext_big != '')) {
				if (file_exists($file_dir . '/template.' . $val)) {
					@unlink($file_dir . '/template.' . $val);
				}
			}
		}
	}
	
	@closedir($template_dir_img);
	$end = '------tpl_info------------';
	$tab = "\n";
	$html = $end . $tab . $name . $tab . 'tpl url：' . $file_url . $tab . $description . $tab . $version . $tab . $author . $tab . $author_url . $tab . $end;
	
	$file = $file_dir .'/tpl_info.txt';
	 
	if($name != ''){
		$myfile = fopen($file, 'w+') or die("Unable to open file!");
		fwrite($myfile, iconv('UTF-8', 'GB2312', $html));
		
		if ($check == 1) {
			$seller_dir = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'] . '/';
			$template_dir = @opendir($seller_dir);

			while ($file = readdir($template_dir)) {
				if (($file != '.') && ($file != '..') && ($file != '.svn') && ($file != 'index.htm')) {
					$available_templates[] = get_seller_template_info2($file, 0, $GLOBALS['_CFG']['template']);
				}
			}

			$available_templates = get_array_sort($available_templates, 'sort');
			@closedir($template_dir);
			$smarty->assign('available_templates', $available_templates);
			
			$smarty->assign('temp', 'categoryTemplates');
			$result['content'] = $GLOBALS['smarty']->fetch('library/dialog.lbi');
		}
		$result['error'] = 0;
		
	}else{
		$result['error'] = 1;
		$result['message'] = $file_dir . '/tpl_info.txt没有写入权限，请修改权限';
	}
	
	//$html = write_static_file_cache('tpl_info', iconv('UTF-8', 'GB2312', $html), 'txt', $file_dir . '/');
	
	/*if ($html === false) {
		$result['error'] = 1;
		$result['message'] = $file_dir . '/tpl_info.txt没有写入权限，请修改权限';
	}
	else {
		if ($check == 1) {
			$seller_dir = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'] . '/';
			$template_dir = @opendir($seller_dir);

			while ($file = readdir($template_dir)) {
				if (($file != '.') && ($file != '..') && ($file != '.svn') && ($file != 'index.htm')) {
					$available_templates[] = get_seller_template_info2($file, 0, $GLOBALS['_CFG']['template']);
				}
			}

			$available_templates = get_array_sort($available_templates, 'sort');
			@closedir($template_dir);
			$smarty->assign('available_templates', $available_templates);
			
			$smarty->assign('temp', 'categoryTemplates');
			$result['content'] = $GLOBALS['smarty']->fetch('library/dialog.lbi');
		}
		$result['error'] = 0;
	}*/

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'removeTemplate') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'content' => '', 'url' => '');
	$code = (isset($_REQUEST['code']) ? addslashes($_REQUEST['code']) : '');
	$template_type = (!empty($_REQUEST['template_type']) ? trim($_REQUEST['template_type']) : '');
	$temp_id = (!empty($_REQUEST['temp_id']) ? intval($_REQUEST['temp_id']) : 0);
	$theme = $GLOBALS['_CFG']['template'];
	
	$dir = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $code;

	$rmdir = del_DirAndFile($dir);
  
	if ($rmdir == true) {
		$sql = 'DELETE FROM' . $ecs->table('templates_left') . 'WHERE seller_templates = \'' . $code . '\' AND theme = \'' . $theme . '\'';
		$db->query($sql);
		$result['error'] = 0;
  
		if ($template_type == 'seller') {
			$sql = 'DELETE FROM' . $ecs->table('template_mall') . 'WHERE temp_code = \'' . $code . '\' AND temp_id = \'' . $temp_id . '\'';
			$db->query($sql);
		}
		else {
			$seller_dir = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'] . '/';
			$template_dir = @opendir($seller_dir);
  
			while ($file = readdir($template_dir)) {
				if (($file != '.') && ($file != '..') && ($file != '.svn') && ($file != 'index.htm')) {
					$available_templates[] = get_seller_template_info2($file, 0, $GLOBALS['_CFG']['template']);
				}
			}
  
			$available_templates = get_array_sort($available_templates, 'sort');
			@closedir($template_dir);
			$smarty->assign('available_templates', $available_templates);

			$smarty->assign('temp', 'categoryTemplates');
			$result['content'] = $GLOBALS['smarty']->fetch('library/dialog.lbi');
		}
	}
	else {
		$result['error'] = 1;
		$result['content'] = '系统出错，请重试！';
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'setupTemplate') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'content' => '', 'url' => '');
	$code = (isset($_REQUEST['code']) ? trim($_REQUEST['code']) : '');
	$dir = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $code;
	if (file_exists($dir) && $code) {
		$sql = 'UPDATE' . $ecs->table('shop_config') . 'SET value=\'' . $code . '\',store_range = \'' . $GLOBALS['_CFG']['template'] . '\'  WHERE code = \'hometheme\'';
		$db->query($sql);
		$result['error'] = 0;
	}
	else {
		$result['error'] = 1;
		$result['message'] = '改模板不存在，请检查';
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'export_tem') {
	$checkboxes = (!empty($_REQUEST['checkboxes']) ? $_REQUEST['checkboxes'] : array());
	$template_type = (!empty($_REQUEST['template_type']) ? trim($_REQUEST['template_type']) : '');

	if (!empty($checkboxes)) {
		include_once 'includes/cls_phpzip.php';
		$zip = new PHPZip();

		if ($template_type == 'seller') {
			$dir = ROOT_PATH . 'data/seller_templates/seller_tem' . '/';
		}
		else {
			$dir = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'] . '/';
		}

		$dir_zip = $dir;
		$file_mune = array();

		foreach ($checkboxes as $v) {
			if ($v) {
				$addfiletozip = $zip->get_filelist($dir_zip . $v);

				foreach ($addfiletozip as $k => $val) {
					if ($v) {
						$addfiletozip[$k] = $v . '/' . $val;
					}
				}

				$file_mune = array_merge($file_mune, $addfiletozip);
			}
		}

		foreach ($file_mune as $v) {
			if (file_exists($dir . '/' . $v)) {
				$zip->add_file(file_get_contents($dir . '/' . $v), $v);
			}
		}

		header('Cache-Control: max-age=0');
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename=templates_list.zip');
		header('Content-Type: application/zip');
		header('Content-Transfer-Encoding: binary');
		header('Content-Type: application/unknown');
		exit($zip->file());
	}
	else {
		$link[0]['text'] = '返回列表';
		$link[0]['href'] = 'visualcategory.php?act=list';
		sys_msg('请选择导出的模板', 1, $link);
	}
}
else if ($_REQUEST['act'] == 'model_delete') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'message' => '');
	$code = (isset($_REQUEST['suffix']) ? trim($_REQUEST['suffix']) : '');
	$dir = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $code;
	if (empty($code) && file_exists($dir)) {
		$result['error'] = 1;
		$result['message'] = '改模板不存在，请刷新重试';
	}
	else {
		if (file_exists($dir . '/topBanner.php')) {
			unlink($dir . '/topBanner.php');
		}

		$result['error'] = 0;
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'downloadModal') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'message' => '');
	$code = (isset($_REQUEST['suffix']) ? trim($_REQUEST['suffix']) : '');
	$adminpath = (isset($_REQUEST['adminpath']) ? trim($_REQUEST['adminpath']) : '');

	if ($adminpath == 'admin') {
		$dir = ROOT_PATH . 'data/seller_templates/seller_tem' . '/' . $code . '/temp';
		$file = ROOT_PATH . 'data/seller_templates/seller_tem' . '/' . $code;
	}
	else {
		$dir = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $code . '/temp';
		$file = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $code;
	}

	if (!empty($code)) {
		if (!is_dir($dir)) {
			make_dir($dir);
		}
		recurse_copy($dir, $file, 1);
		del_DirAndFile($dir);
		$result['error'] = 0;
	}

	if (!isset($GLOBALS['_CFG']['open_oss'])) {
		$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'open_oss\'';
		$is_oss = $GLOBALS['db']->getOne($sql, true);
	}
	else {
		$is_oss = $GLOBALS['_CFG']['open_oss'];
	}

	if (!isset($GLOBALS['_CFG']['server_model'])) {
		$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'server_model\'';
		$server_model = $GLOBALS['db']->getOne($sql, true);
	}
	else {
		$server_model = $GLOBALS['_CFG']['server_model'];
	}

	if ($is_oss && $server_model) {
		$dir = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $code . '/';
		$path = 'data/category_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $code . '/';
		$file_list = get_recursive_file_oss($dir, $path, true);
		get_oss_add_file($file_list);
		dsc_unlink(ROOT_PATH . 'data/sc_file/hometemplates/' . $code . '.php');
		$id_data = read_static_cache('urlip_list', '/data/sc_file/');

		if ($pin_region_list !== false) {
			del_visual_templates($id_data, $code);
		}
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'backmodal') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'message' => '');
	$code = (isset($_REQUEST['suffix']) ? trim($_REQUEST['suffix']) : '');
	$dir = ROOT_PATH . 'data/category_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $code . '/temp';

	if (!empty($code)) {
		del_DirAndFile($dir);
		$result['error'] = 0;
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'bonusAdv') {
	require ROOT_PATH . '/includes/cls_json.php';
	include_once ROOT_PATH . '/includes/cls_image.php';
	$image = new cls_image($_CFG['bgcolor']);
	$json = new JSON();
	$result = array('error' => '', 'message' => '');
	$suffix = (isset($_REQUEST['suffix']) ? trim($_REQUEST['suffix']) : '');
	$adv_url = (!empty($_REQUEST['adv_url']) ? trim($_REQUEST['adv_url']) : '');
	$allow_file_types = '|GIF|JPG|PNG|';
	$oss_img_url = '';
	$bgtype = 'bonusadv';
	$theme = $GLOBALS['_CFG']['template'];

	if ($_FILES['advfile']) {
		if ((isset($_FILES['advfile']['error']) && ($_FILES['advfile']['error'] == 0)) || (!isset($_FILES['advfile']['error']) && ($_FILES['advfile']['tmp_name'] != 'none'))) {
			if (!check_file_type($_FILES['advfile']['tmp_name'], $_FILES['advfile']['name'], $allow_file_types)) {
				$result['error'] = 1;
				$result['prompt'] = '请上传正确格式图片（' . $allow_file_types）;
				exit(json_encode($result));
			}
			else {
				$ext_name = explode('.', $_FILES['advfile']['name']);
				$ext = array_pop($ext_name);
				$file_dir = '../data/category_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $suffix . '/images/bonusadv';

				if (!is_dir($file_dir)) {
					make_dir($file_dir);
				}

				$file_name = $file_dir . '/bonusadv_' . gmtime() . '.' . $ext;

				if (move_upload_file($_FILES['advfile']['tmp_name'], $file_name)) {
					$oss_img_url = str_replace('../', '', $file_name);
					get_oss_add_file(array($oss_img_url));
				}
			}
		}
	}

	$sql = 'SELECT id ,img_file FROM' . $ecs->table('templates_left') . ' WHERE ru_id = 0 AND seller_templates = \'' . $suffix . '\' AND type = \'' . $bgtype . '\' AND theme = \'' . $theme . '\' LIMIT 1';
	$templates_left = $db->getRow($sql);

	if (0 < $templates_left['id']) {
		$fileurl = '';

		if ($oss_img_url != '') {
			if ($templates_left['img_file'] != '') {
				@unlink('../' . $templates_left['img_file']);
				get_oss_del_file(array($templates_left['img_file']));
			}

			$fileurl = ',img_file = \'' . $oss_img_url . '\'';
		}

		$sql = 'UPDATE' . $ecs->table('templates_left') . ' SET fileurl = \'' . $adv_url . '\' ' . $fileurl . ' WHERE ru_id = 0 AND seller_templates = \'' . $suffix . '\' AND id=\'' . $templates_left['id'] . '\' AND type = \'' . $bgtype . '\' AND theme = \'' . $theme . '\'';
		$db->query($sql);
	}
	else {
		$sql = 'INSERT INTO' . $ecs->table('templates_left') . ' (`ru_id`,`seller_templates`,`img_file`,`type`,`theme`,`fileurl`) VALUES (0,\'' . $suffix . '\',\'' . $oss_img_url . '\',\'' . $bgtype . '\',\'' . $theme . '\',\'' . $adv_url . '\')';
		$db->query($sql);
	}

	$result['file'] = '';

	if (!empty($oss_img_url)) {
		$result['file'] = get_image_path(0, $oss_img_url, true);
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'delete_adv') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'message' => '');
	$suffix = (isset($_REQUEST['suffix']) ? trim($_REQUEST['suffix']) : '');
	$bgtype = 'bonusadv';
	$theme = $GLOBALS['_CFG']['template'];
	$sql = 'SELECT id ,img_file FROM' . $ecs->table('templates_left') . ' WHERE ru_id = 0 AND seller_templates = \'' . $suffix . '\' AND type = \'' . $bgtype . '\' AND theme = \'' . $theme . '\' LIMIT 1';
	$templates_left = $db->getRow($sql);

	if ($templates_left['img_file'] != '') {
		@unlink('../' . $templates_left['img_file']);
		get_oss_del_file(array($templates_left['img_file']));
	}

	$sql = 'DELETE FROM' . $GLOBALS['ecs']->table('templates_left') . 'WHERE ru_id = 0 AND seller_templates = \'' . $suffix . '\' AND type = \'' . $bgtype . '\' AND theme = \'' . $theme . '\'';
	$db->query($sql);
	exit(json_encode($result));
}

?>
