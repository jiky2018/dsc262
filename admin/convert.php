<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function check_files_readable($dirname)
{
	if ($dh = opendir($dirname)) {
		while (($file = readdir($dh)) !== false) {
			if ((filetype($dirname . $file) == 'file') && (strtolower($file) != 'thumbs.db')) {
				if (file_mode_info($dirname . $file) & (1 != 1)) {
					return $dirname . $file;
				}
			}
		}

		closedir($dh);
	}

	return true;
}

function copy_files($from_dir, $to_dir, $file_prefix = '')
{
	if ($dh = opendir($from_dir)) {
		while (($file = readdir($dh)) !== false) {
			if ((filetype($from_dir . $file) == 'file') && (strtolower($file) != 'thumbs.db')) {
				if (!copy($from_dir . $file, $to_dir . $file_prefix . $file)) {
					return $from_dir . $file;
				}
			}
		}

		closedir($dh);
	}

	return true;
}

function copy_dirs($from_dir, $to_dir, $file_prefix = '')
{
	$result = true;

	if (!is_dir($from_dir)) {
		exit('It\'s not a dir');
	}

	if (!is_dir($to_dir)) {
		if (!mkdir($to_dir, 448)) {
			exit('can\'t mkdir');
		}
	}

	$handle = opendir($from_dir);

	while (($file = readdir($handle)) !== false) {
		if (($file != '.') && ($file != '..')) {
			$src = $from_dir . DIRECTORY_SEPARATOR . $file;
			$dtn = $to_dir . DIRECTORY_SEPARATOR . $file_prefix . $file;

			if (is_dir($src)) {
				copy_dirs($src, $dtn);
			}
			else if (!copy($src, $dtn)) {
				$result = false;
				break;
			}
		}
	}

	closedir($handle);
	return $result;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ($_REQUEST['act'] == 'main') {
	admin_priv('convert');
	$modules = read_modules('../includes/modules/convert');

	for ($i = 0; $i < count($modules); $i++) {
		$code = $modules[$i]['code'];
		$lang_file = ROOT_PATH . 'languages/' . $_CFG['lang'] . '/convert/' . $code . '.php';

		if (file_exists($lang_file)) {
			include_once $lang_file;
		}

		$modules[$i]['desc'] = $_LANG[$modules[$i]['desc']];
	}

	$smarty->assign('module_list', $modules);
	$def_val = array('host' => $db_host, 'db' => '', 'user' => $db_user, 'pass' => $db_pass, 'prefix' => 'sdb_', 'path' => '');
	$smarty->assign('def_val', $def_val);
	$smarty->assign('charset_list', get_charset_list());
	$smarty->assign('ur_here', $_LANG['convert']);
	assign_query_info();
	$smarty->display('convert_main.dwt');
}
else if ($_REQUEST['act'] == 'check') {
	check_authz_json('convert');
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$config['host'] = empty($_POST['host']) ? '' : trim($_POST['host']);
	$config['db'] = empty($_POST['db']) ? '' : trim($_POST['db']);
	$config['user'] = empty($_POST['user']) ? '' : trim($_POST['user']);
	$config['pass'] = empty($_POST['pass']) ? '' : trim($_POST['pass']);
	$config['prefix'] = empty($_POST['prefix']) ? '' : trim($_POST['prefix']);
	$config['code'] = empty($_POST['selModule']) ? '' : trim($_POST['selModule']);
	$config['path'] = empty($_POST['path']) ? '' : trim($_POST['path']);
	$config['charset'] = empty($_POST['selCharset']) ? '' : trim($_POST['selCharset']);
	$sdb = new cls_mysql($config['host'], $config['user'], $config['pass'], $config['db']);
	$sprefix = $config['prefix'];
	$config['path'] = rtrim(str_replace('\\', '/', $config['path']), '/');
	include_once ROOT_PATH . 'includes/modules/convert/' . $config['code'] . '.php';
	$convert = new $config['code']($sdb, $sprefix, $config['path']);
	$required_table_list = $convert->required_tables();
	$sql = 'SHOW TABLES';
	$table_list = $sdb->getCol($sql);
	$diff_arr = array_diff($required_table_list, $table_list);

	if ($diff_arr) {
		sys_msg($_LANG['table_error'], 1);
	}

	$img_dir = ROOT_PATH . IMAGE_DIR . '/' . date('Ym') . '/';

	if (!file_exists($img_dir)) {
		make_dir($img_dir);
	}

	$to_dir_list = array(ROOT_PATH . IMAGE_DIR . '/upload/', $img_dir, ROOT_PATH . DATA_DIR . '/afficheimg/', ROOT_PATH . 'cert/');

	foreach ($to_dir_list as $to_dir) {
		if (!file_exists($to_dir) || !is_dir($to_dir)) {
			sys_msg($_LANG['dir_error'], 1);
		}

		if (file_mode_info($to_dir) & (4 != 4)) {
			sys_msg($_LANG['dir_not_writable'], 1);
		}
	}

	$_SESSION['convert_config'] = $config;
	include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/convert/' . $config['code'] . '.php';
	$step = $convert->next_step('');
	sys_msg($_LANG[$step]);
}
else if ($_REQUEST['act'] == 'process') {
	set_time_limit(0);
	check_authz_json('convert');
	$step = json_str_iconv($_POST['step']);
	$config = $_SESSION['convert_config'];
	$sdb = new cls_mysql($config->host, $config->user, $config->pass, $config->db);
	$sdb->set_mysql_charset($config->charset);
	include_once ROOT_PATH . 'includes/modules/convert/' . $config->code . '.php';
	$convert = new $config->code($sdb, $config->prefix, $config->path, $config->charset);
	include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/convert/' . $config->code . '.php';
	$result = $convert->process($step);

	if ($result !== true) {
		make_json_error($result);
	}

	$step = $convert->next_step($step);
	make_json_result($step, empty($_LANG[$step]) ? '' : $_LANG[$step]);
}

?>
