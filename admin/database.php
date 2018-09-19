<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function sql_import($sql_file)
{
	$db_ver = $GLOBALS['db']->version();
	$sql_str = array_filter(file($sql_file), 'remove_comment');
	$sql_str = str_replace("\r", '', implode('', $sql_str));
	$ret = explode(";\n", $sql_str);
	$ret_count = count($ret);

	if ('4.1' < $db_ver) {
		for ($i = 0; $i < $ret_count; $i++) {
			$ret[$i] = trim($ret[$i], " \r\n;");

			if (!empty($ret[$i])) {
				if ((strpos($ret[$i], 'CREATE TABLE') !== false) && (strpos($ret[$i], 'DEFAULT CHARSET=' . str_replace('-', '', EC_CHARSET)) === false)) {
					$ret[$i] = $ret[$i] . 'DEFAULT CHARSET=' . str_replace('-', '', EC_CHARSET);
				}

				$GLOBALS['db']->query($ret[$i]);
			}
		}
	}
	else {
		for ($i = 0; $i < $ret_count; $i++) {
			$ret[$i] = trim($ret[$i], " \r\n;");
			if ((strpos($ret[$i], 'CREATE TABLE') !== false) && (strpos($ret[$i], 'DEFAULT CHARSET=' . str_replace('-', '', EC_CHARSET)) !== false)) {
				$ret[$i] = str_replace('DEFAULT CHARSET=' . str_replace('-', '', EC_CHARSET), '', $ret[$i]);
			}

			if (!empty($ret[$i])) {
				$GLOBALS['db']->query($ret[$i]);
			}
		}
	}

	return true;
}

function num_bitunit($num)
{
	$bitunit = array(' B', ' KB', ' MB', ' GB');
	$key = 0;

	for ($count = count($bitunit); $key < $count; $key++) {
		if ((pow(2, 10 * $key) - 1) <= $num) {
			$num_bitunit_str = (ceil(($num / pow(2, 10 * $key)) * 100) / 100) . ' ' . $bitunit[$key];
		}
	}

	return $num_bitunit_str;
}

function remove_comment($var)
{
	return substr($var, 0, 2) != '--';
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . ADMIN_PATH . '/includes/cls_sql_dump.php';
$cls_sql_dump = new cls_sql_dump();
@ini_set('memory_limit', '512M');

if ($_REQUEST['act'] == 'backup') {
	$tables = $db->GetCol('SHOW TABLES LIKE \'' . mysql_like_quote($ecs->prefix) . '%\'');
	$allow_max_size = return_bytes(@ini_get('upload_max_filesize'));
	$allow_max_size = $allow_max_size / 1024;
	$path = ROOT_PATH . DATA_DIR . '/sqldata';
	$mask = file_mode_info($path);

	if ($mask === false) {
		$warning = sprintf($_LANG['dir_not_exist'], $path);
		$smarty->assign('warning', $warning);
	}
	else if ($mask != 15) {
		$warning = sprintf($_LANG['dir_priv'], $path) . '<br/>';

		if (($mask & 1) < 1) {
			$warning .= $_LANG['cannot_read'] . '&nbsp;&nbsp;';
		}

		if (($mask & 2) < 1) {
			$warning .= $_LANG['cannot_write'] . '&nbsp;&nbsp;';
		}

		if (($mask & 4) < 1) {
			$warning .= $_LANG['cannot_add'] . '&nbsp;&nbsp;';
		}

		if (($mask & 8) < 1) {
			$warning .= $_LANG['cannot_modify'];
		}

		$smarty->assign('warning', $warning);
	}

	assign_query_info();
	$smarty->assign('action_link', array('text' => $_LANG['restore'], 'href' => 'database.php?act=restore'));
	$smarty->assign('tables', $tables);
	$smarty->assign('vol_size', $allow_max_size);
	$smarty->assign('sql_name', $cls_sql_dump->get_random_name() . '.sql');
	$smarty->assign('ur_here', $_LANG['02_db_manage']);
	$smarty->display('db_backup.dwt');
}

if ($_REQUEST['act'] == 'restore') {
	admin_priv('db_renew');
	$list = array();
	$path = ROOT_PATH . DATA_DIR . '/sqldata/';
	$mask = file_mode_info($path);

	if ($mask === false) {
		$warning = sprintf($_LANG['dir_not_exist'], $path);
		$smarty->assign('warning', $warning);
	}
	else if (($mask & 1) < 1) {
		$warning = $path . '&nbsp;&nbsp;' . $_LANG['cannot_read'];
		$smarty->assign('warning', $warning);
	}
	else {
		$real_list = array();
		$folder = opendir($path);

		while ($file = readdir($folder)) {
			if (strpos($file, '.sql') !== false) {
				$real_list[] = $file;
			}
		}

		natsort($real_list);
		$match = array();

		foreach ($real_list as $file) {
			if (preg_match('/_([0-9])+\\.sql$/', $file, $match)) {
				if ($match[1] == 1) {
					$mark = 1;
				}
				else {
					$mark = 2;
				}
			}
			else {
				$mark = 0;
			}

			$file_size = filesize($path . $file);
			$info = $cls_sql_dump->get_head($path . $file);
			$list[] = array('name' => $file, 'ver' => $info['ecs_ver'], 'add_time' => $info['date'], 'vol' => $info['vol'], 'file_size' => num_bitunit($file_size), 'mark' => $mark);
		}
	}

	assign_query_info();
	$smarty->assign('action_link', array('text' => $_LANG['02_db_manage'], 'href' => 'database.php?act=backup'));
	$smarty->assign('ur_here', $_LANG['restore']);
	$smarty->assign('list', $list);
	$smarty->display('db_restore.dwt');
}

if ($_REQUEST['act'] == 'dumpsql') {
	$token = trim($_REQUEST['token']);

	if ($token != $_CFG['token']) {
		sys_msg($_LANG['backup_failure'], 1);
	}

	admin_priv('db_backup');
	$path = ROOT_PATH . DATA_DIR . '/sqldata';
	$mask = file_mode_info($path);

	if ($mask === false) {
		$warning = sprintf($_LANG['dir_not_exist'], $path);
		sys_msg($warning, 1);
	}
	else if ($mask != 15) {
		$warning = sprintf($_LANG['dir_priv'], $path);

		if (($mask & 1) < 1) {
			$warning .= $_LANG['cannot_read'];
		}

		if (($mask & 2) < 1) {
			$warning .= $_LANG['cannot_write'];
		}

		if (($mask & 4) < 1) {
			$warning .= $_LANG['cannot_add'];
		}

		if (($mask & 8) < 1) {
			$warning .= $_LANG['cannot_modify'];
		}

		sys_msg($warning, 1);
	}

	@set_time_limit(300);
	$dump = new cls_sql_dump($db);
	$run_log = ROOT_PATH . DATA_DIR . '/sqldata/run.log';

	if (empty($_REQUEST['sql_file_name'])) {
		$sql_file_name = $dump->get_random_name();
	}
	else {
		$sql_file_name = str_replace('0xa', '', trim($_REQUEST['sql_file_name']));
		$pos = strpos($sql_file_name, '.sql');

		if ($pos !== false) {
			$sql_file_name = substr($sql_file_name, 0, $pos);
		}
	}

	$max_size = (empty($_REQUEST['vol_size']) ? 0 : intval($_REQUEST['vol_size']));
	$vol = (empty($_REQUEST['vol']) ? 1 : intval($_REQUEST['vol']));
	$is_short = (empty($_REQUEST['ext_insert']) ? false : true);
	$dump->is_short = $is_short;
	$allow_max_size = intval(@ini_get('upload_max_filesize'));
	if ((0 < $allow_max_size) && (($allow_max_size * 1024) < $max_size)) {
		$max_size = $allow_max_size * 1024;
	}

	if (0 < $max_size) {
		$dump->max_size = $max_size * 1024;
	}

	$type = (empty($_POST['type']) ? '' : trim($_POST['type']));
	$tables = array();

	switch ($type) {
	case 'full':
		$except = array($ecs->prefix . 'sessions', $ecs->prefix . 'sessions_data');
		$temp = $db->GetCol('SHOW TABLES LIKE \'' . mysql_like_quote($ecs->prefix) . '%\'');

		foreach ($temp as $table) {
			if (in_array($table, $except)) {
				continue;
			}

			$tables[$table] = -1;
		}

		$dump->put_tables_list($run_log, $tables);
		break;

	case 'stand':
		$temp = array('admin_user', 'area_region', 'article', 'article_cat', 'attribute', 'brand', 'cart', 'category', 'comment', 'goods', 'goods_attr', 'goods_cat', 'goods_gallery', 'goods_type', 'group_goods', 'link_goods', 'member_price', 'order_action', 'order_goods', 'order_info', 'payment', 'region', 'shipping', 'shipping_area', 'shop_config', 'user_address', 'user_bonus', 'user_rank', 'users', 'virtual_card');

		foreach ($temp as $table) {
			$tables[$ecs->prefix . $table] = -1;
		}

		$dump->put_tables_list($run_log, $tables);
		break;

	case 'min':
		$temp = array('attribute', 'brand', 'cart', 'category', 'goods', 'goods_attr', 'goods_cat', 'goods_gallery', 'goods_type', 'group_goods', 'link_goods', 'member_price', 'order_action', 'order_goods', 'order_info', 'shop_config', 'user_address', 'user_bonus', 'user_rank', 'users', 'virtual_card');

		foreach ($temp as $table) {
			$tables[$ecs->prefix . $table] = -1;
		}

		$dump->put_tables_list($run_log, $tables);
		break;

	case 'custom':
		foreach ($_POST['customtables'] as $table) {
			$tables[$table] = -1;
		}

		$dump->put_tables_list($run_log, $tables);
		break;
	}

	$tables = $dump->dump_table($run_log, $vol);

	if ($tables === false) {
		exit($dump->errorMsg());
	}

	if (empty($tables)) {
		if (1 < $vol) {
			if (!@file_put_contents(ROOT_PATH . DATA_DIR . '/sqldata/' . $sql_file_name . '_' . $vol . '.sql', $dump->dump_sql)) {
				sys_msg(sprintf($_LANG['fail_write_file'], $sql_file_name . '_' . $vol . '.sql'), 1, array(
	array('text' => $_LANG['02_db_manage'], 'href' => 'database.php?act=backup')
	), false);
			}

			$list = array();

			for ($i = 1; $i <= $vol; $i++) {
				$list[] = array('name' => $sql_file_name . '_' . $i . '.sql', 'href' => '../' . DATA_DIR . '/sqldata/' . $sql_file_name . '_' . $i . '.sql');
			}

			$smarty->assign('list', $list);
			$smarty->assign('title', $_LANG['backup_success']);
			$smarty->display('sql_dump_msg.dwt');
		}
		else {
			if (!@file_put_contents(ROOT_PATH . DATA_DIR . '/sqldata/' . $sql_file_name . '.sql', $dump->dump_sql)) {
				sys_msg(sprintf($_LANG['fail_write_file'], $sql_file_name . '_' . $vol . '.sql'), 1, array(
	array('text' => $_LANG['02_db_manage'], 'href' => 'database.php?act=backup')
	), false);
			}

			$smarty->assign('list', array(
	array('name' => $sql_file_name . '.sql', 'href' => '../' . DATA_DIR . '/sqldata/' . $sql_file_name . '.sql')
	));
			$smarty->assign('title', $_LANG['backup_success']);
			$smarty->display('sql_dump_msg.dwt');
		}
	}
	else {
		if (!@file_put_contents(ROOT_PATH . DATA_DIR . '/sqldata/' . $sql_file_name . '_' . $vol . '.sql', $dump->dump_sql)) {
			sys_msg(sprintf($_LANG['fail_write_file'], $sql_file_name . '_' . $vol . '.sql'), 1, array(
	array('text' => $_LANG['02_db_manage'], 'href' => 'database.php?act=backup')
	), false);
		}

		$lnk = 'database.php?act=dumpsql&token=' . $_CFG['token'] . '&sql_file_name=' . $sql_file_name . '&vol_size=' . $max_size . '&vol=' . ($vol + 1);
		$smarty->assign('title', sprintf($_LANG['backup_title'], '#' . $vol));
		$smarty->assign('auto_redirect', 1);
		$smarty->assign('auto_link', $lnk);
		$smarty->display('sql_dump_msg.dwt');
	}
}

if ($_REQUEST['act'] == 'remove') {
	admin_priv('db_backup');

	if (isset($_POST['file'])) {
		$m_file = array();
		$s_file = array();
		$path = ROOT_PATH . DATA_DIR . '/sqldata/';

		foreach ($_POST['file'] as $file) {
			if (preg_match('/_[0-9]+\\.sql$/', $file)) {
				$m_file[] = substr($file, 0, strrpos($file, '_'));
			}
			else {
				$s_file[] = $file;
			}
		}

		if ($m_file) {
			$m_file = array_unique($m_file);
			$real_file = array();
			$folder = opendir($path);

			while ($file = readdir($folder)) {
				if (preg_match('/_[0-9]+\\.sql$/', $file) && is_file($path . $file)) {
					$real_file[] = $file;
				}
			}

			foreach ($real_file as $file) {
				$short_file = substr($file, 0, strrpos($file, '_'));

				if (in_array($short_file, $m_file)) {
					@unlink($path . $file);
				}
			}
		}

		if ($s_file) {
			foreach ($s_file as $file) {
				@unlink($path . $file);
			}
		}
	}

	sys_msg($_LANG['remove_success'], 0, array(
	array('text' => $_LANG['restore'], 'href' => 'database.php?act=restore')
	));
}

if ($_REQUEST['act'] == 'import') {
	admin_priv('db_renew');
	$is_confirm = (empty($_GET['confirm']) ? false : true);
	$file_name = (empty($_GET['file_name']) ? '' : trim($_GET['file_name']));
	$path = ROOT_PATH . DATA_DIR . '/sqldata/';
	@set_time_limit(300);

	if (preg_match('/_[0-9]+\\.sql$/', $file_name)) {
		if ($is_confirm == false) {
			sys_msg($_LANG['confirm_import'], 1, array(
	array('text' => $_LANG['also_continue'], 'href' => 'database.php?act=import&confirm=1&file_name=' . $file_name)
	), false);
		}

		$short_name = substr($file_name, 0, strrpos($file_name, '_'));
		$real_file = array();
		$folder = opendir($path);

		while ($file = readdir($folder)) {
			if (is_file($path . $file) && preg_match('/_[0-9]+\\.sql$/', $file)) {
				$real_file[] = $file;
			}
		}

		$post_list = array();

		foreach ($real_file as $file) {
			$tmp_name = substr($file, 0, strrpos($file, '_'));

			if ($tmp_name == $short_name) {
				$post_list[] = $file;
			}
		}

		natsort($post_list);

		foreach ($post_list as $file) {
			$info = $cls_sql_dump->get_head($path . $file);

			if ($info['ecs_ver'] != VERSION) {
				sys_msg(sprintf($_LANG['version_error'], VERSION, $sql_info['ecs_ver']));
			}

			if (!sql_import($path . $file)) {
				sys_msg($_LANG['sqlfile_error'], 1);
			}
		}

		clear_cache_files();
		sys_msg($_LANG['restore_success'], 0, array(
	array('text' => $_LANG['restore'], 'href' => 'database.php?act=restore')
	));
	}
	else {
		$info = $cls_sql_dump->get_head($path . $file_name);

		if ($info['ecs_ver'] != VERSION) {
			sys_msg(sprintf($_LANG['version_error'], VERSION, $sql_info['ecs_ver']));
		}

		if (sql_import($path . $file_name)) {
			clear_cache_files();
			admin_log($_LANG['backup_time'] . $info['date'], 'restore', 'db_backup');
			sys_msg($_LANG['restore_success'], 0, array(
	array('text' => $_LANG['restore'], 'href' => 'database.php?act=restore')
	));
		}
		else {
			sys_msg($_LANG['sqlfile_error'], 1);
		}
	}
}

if ($_REQUEST['act'] == 'upload_sql') {
	admin_priv('db_renew');
	$sql_file = ROOT_PATH . DATA_DIR . '/upload_database_bak.sql';

	if (empty($_GET['mysql_ver_confirm'])) {
		if (empty($_FILES['sqlfile'])) {
			sys_msg($_LANG['empty_upload'], 1);
		}

		$file = $_FILES['sqlfile'];
		if ((isset($file['error']) && (0 < $file['error'])) || (!isset($file['error']) && ($file['tmp_name'] == 'none'))) {
			sys_msg($_LANG['fail_upload'], 1);
		}

		if ($file['type'] == 'application/x-zip-compressed') {
			sys_msg($_LANG['not_support_zip_format'], 1);
		}

		if (!preg_match('/\\.sql$/i', $file['name'])) {
			sys_msg($_LANG['not_sql_file'], 1);
		}

		@unlink($sql_file);

		if (!move_upload_file($file['tmp_name'], $sql_file)) {
			sys_msg($_LANG['fail_upload_move'], 1);
		}
	}

	$sql_info = $cls_sql_dump->get_head($sql_file);

	if (empty($sql_info['ecs_ver'])) {
		sys_msg($_LANG['unrecognize_version'], 1);
	}
	else if ($sql_info['ecs_ver'] != VERSION) {
		sys_msg(sprintf($_LANG['version_error'], VERSION, $sql_info['ecs_ver']));
	}

	if (empty($_GET['mysql_ver_confirm'])) {
		if (empty($sql_info['mysql_ver'])) {
			sys_msg($_LANG['unrecognize_mysql_version']);
		}
		else {
			$mysql_ver_arr = $db->version();

			if ($sql_info['mysql_ver'] != $mysql_ver_arr) {
				$lnk = array();
				$lnk[] = array('text' => $_LANG['confirm_ver'], 'href' => 'database.php?act=upload_sql&mysql_ver_confirm=1');
				$lnk[] = array('text' => $_LANG['unconfirm_ver'], 'href' => 'database.php?act=restore');
				sys_msg(sprintf($_LANG['mysql_version_error'], $mysql_ver_arr, $sql_info['mysql_ver']), 0, $lnk, false);
			}
		}
	}

	@set_time_limit(300);

	if (sql_import($sql_file)) {
		clear_all_files();
		@unlink($sql_file);
		sys_msg($_LANG['restore_success'], 0, array());
	}
	else {
		@unlink($sql_file);
		sys_msg($_LANG['sqlfile_error'], 1);
	}
}

if ($_REQUEST['act'] == 'optimize') {
	admin_priv('db_backup');
	$db_ver_arr = $db->version();
	$db_ver = $db_ver_arr;
	$ret = $db->query('SHOW TABLE STATUS LIKE \'' . mysql_like_quote($ecs->prefix) . '%\'');
	$num = 0;
	$list = array();

	while ($row = $db->fetchRow($ret)) {
		if (strpos($row['Name'], '_session') !== false) {
			$res['Msg_text'] = 'Ignore';
			$row['Data_free'] = 'Ignore';
		}
		else {
			$res = $db->GetRow('CHECK TABLE ' . $row['Name']);
			$num += $row['Data_free'];
		}

		$type = ('4.1' <= $db_ver ? $row['Engine'] : $row['Type']);
		$charset = ('4.1' <= $db_ver ? $row['Collation'] : 'N/A');
		$list[] = array('table' => $row['Name'], 'type' => $type, 'rec_num' => $row['Rows'], 'rec_size' => sprintf(' %.2f KB', $row['Data_length'] / 1024), 'rec_index' => $row['Index_length'], 'rec_chip' => $row['Data_free'], 'status' => $res['Msg_text'], 'charset' => $charset);
	}

	unset($ret);
	assign_query_info();
	$smarty->assign('list', $list);
	$smarty->assign('num', $num);
	$smarty->assign('ur_here', $_LANG['03_db_optimize']);
	$smarty->display('optimize.dwt');
}

if ($_REQUEST['act'] == 'run_optimize') {
	admin_priv('db_backup');
	$tables = $db->getCol('SHOW TABLES LIKE \'' . mysql_like_quote($ecs->prefix) . '%\'');

	foreach ($tables as $table) {
		if ($row = $db->getRow('OPTIMIZE TABLE ' . $table)) {
			if (($row['Msg_type'] == 'error') && (strpos($row['Msg_text'], 'repair') !== false)) {
				$db->query('REPAIR TABLE ' . $table);
			}
		}
	}

	sys_msg(sprintf($_LANG['optimize_ok'], $_POST['num']), 0, array(
	array('text' => $_LANG['go_back'], 'href' => 'database.php?act=optimize')
	));
}

?>
