<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function check_file_in_array($arr, &$err_msg)
{
	$read = true;
	$writen = true;
	$modify = true;

	foreach ($arr as $val) {
		$mark = file_mode_info(ROOT_PATH . $val);

		if (($mark & 1) < 1) {
			$read = false;
			$err_msg['r'][] = $val;
		}

		if (($mark & 2) < 1) {
			$writen = false;
			$err_msg['w'][] = $val;
		}

		if (($mark & 4) < 1) {
			$modify = false;
			$err_msg['m'][] = $val;
		}
	}

	$mark = 0;

	if ($read) {
		$mark ^= 1;
	}

	if ($writen) {
		$mark ^= 2;
	}

	if ($modify) {
		$mark ^= 4;
	}

	return $mark;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ($_REQUEST['act'] == 'check') {
	admin_priv('file_priv');
	$goods_img_dir = array();
	$folder = opendir(ROOT_PATH . 'images');

	while ($dir = readdir($folder)) {
		if (is_dir(ROOT_PATH . IMAGE_DIR . '/' . $dir) && preg_match('/^[0-9]{6}$/', $dir)) {
			$goods_img_dir[] = IMAGE_DIR . '/' . $dir;
		}
	}

	closedir($folder);
	$dir[] = ADMIN_PATH;
	$dir[] = 'cert';
	$dir_subdir['images'][] = IMAGE_DIR;
	$dir_subdir['images'][] = IMAGE_DIR . '/upload';
	$dir_subdir['images'][] = IMAGE_DIR . '/upload/Image';
	$dir_subdir['images'][] = IMAGE_DIR . '/upload/File';
	$dir_subdir['images'][] = IMAGE_DIR . '/upload/Flash';
	$dir_subdir['images'][] = IMAGE_DIR . '/upload/Media';
	$dir_subdir['data'][] = DATA_DIR;
	$dir_subdir['data'][] = DATA_DIR . '/afficheimg';
	$dir_subdir['data'][] = DATA_DIR . '/brandlogo';
	$dir_subdir['data'][] = DATA_DIR . '/cardimg';
	$dir_subdir['data'][] = DATA_DIR . '/feedbackimg';
	$dir_subdir['data'][] = DATA_DIR . '/packimg';
	$dir_subdir['data'][] = DATA_DIR . '/sqldata';
	$dir_subdir['temp'][] = 'temp';
	$dir_subdir['temp'][] = 'temp/backup';
	$dir_subdir['temp'][] = 'temp/caches';
	$dir_subdir['temp'][] = 'temp/compiled';
	$dir_subdir['temp'][] = 'temp/compiled/admin';
	$dir_subdir['temp'][] = 'temp/query_caches';
	$dir_subdir['temp'][] = 'temp/static_caches';

	foreach ($goods_img_dir as $val) {
		$dir_subdir['images'][] = $val;
	}

	$tpl = 'themes/' . $_CFG['template'] . '/';
	$list = array();

	foreach ($dir as $val) {
		$mark = file_mode_info(ROOT_PATH . $val);
		$list[] = array('item' => $val . $_LANG['dir'], 'r' => $mark & 1, 'w' => $mark & 2, 'm' => $mark & 4);
	}

	$keys = array_unique(array_keys($dir_subdir));

	foreach ($keys as $key) {
		$err_msg = array();
		$mark = check_file_in_array($dir_subdir[$key], $err_msg);
		$list[] = array('item' => $key . $_LANG['dir_subdir'], 'r' => $mark & 1, 'w' => $mark & 2, 'm' => $mark & 4, 'err_msg' => $err_msg);
	}

	$dwt = @opendir(ROOT_PATH . $tpl);
	$tpl_file = array();

	while ($file = readdir($dwt)) {
		if (is_file(ROOT_PATH . $tpl . $file) && (0 < strrpos($file, '.dwt'))) {
			$tpl_file[] = $tpl . $file;
		}
	}

	@closedir($dwt);
	$lib = @opendir(ROOT_PATH . $tpl . 'library/');

	while ($file = readdir($lib)) {
		if (is_file(ROOT_PATH . $tpl . 'library/' . $file) && (0 < strrpos($file, '.lbi'))) {
			$tpl_file[] = $tpl . 'library/' . $file;
		}
	}

	@closedir($lib);
	$err_msg = array();
	$mark = check_file_in_array($tpl_file, $err_msg);
	$list[] = array('item' => $tpl . $_LANG['tpl_file'], 'r' => $mark & 1, 'w' => $mark & 2, 'm' => $mark & 4, 'err_msg' => $err_msg);
	$tpl_list = array();
	$tpl_dirs[] = 'temp/caches';
	$tpl_dirs[] = 'temp/compiled';
	$tpl_dirs[] = 'temp/compiled/admin';

	foreach ($goods_img_dir as $val) {
		$tpl_dirs[] = $val;
	}

	foreach ($tpl_dirs as $dir) {
		$mask = file_mode_info(ROOT_PATH . $dir);

		if (0 < ($mask & 4)) {
			if (($mask & 8) < 1) {
				$tpl_list[] = $dir;
			}
		}
	}

	$tpl_msg = implode(', ', $tpl_list);
	$smarty->assign('ur_here', $_LANG['check_file_priv']);
	$smarty->assign('list', $list);
	$smarty->assign('tpl_msg', $tpl_msg);
	$smarty->display('file_priv.dwt');
}

?>
