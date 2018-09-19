<?php
//zend by 商创网络 Q Q:123456    禁止倒卖 一经发现停止任何服务
function conflict_userlist()
{
	$filter['flag'] = empty($_REQUEST['flag']) ? 0 : intval($_REQUEST['flag']);
	$where = ' WHERE flag';

	if ($filter['flag']) {
		$where .= '=' . $filter['flag'];
	}
	else {
		$where .= '>' . 0;
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('users') . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT user_id, user_name, email, reg_time, flag, alias ' . ' FROM ' . $GLOBALS['ecs']->table('users') . $where . ' ORDER BY user_id ASC' . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
	$list = $GLOBALS['db']->getAll($sql);
	$list_count = count($list);

	for ($i = 0; $i < $list_count; $i++) {
		$list[$i]['reg_date'] = local_date($GLOBALS['_CFG']['date_format'], $list[$i]['reg_time']);
	}

	$arr = array('list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function save_integrate_config($code, $cfg)
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'integrate_code\'';

	if ($GLOBALS['db']->GetOne($sql) == 0) {
		$sql = 'INSERT INTO ' . $ecs->table('shop_config') . ' (code, value) ' . ('VALUES (\'integrate_code\', \'' . $code . '\')');
	}
	else {
		$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'integrate_code\'';

		if ($code != $GLOBALS['db']->getOne($sql)) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('shop_config') . ' SET value = \'\' WHERE code = \'points_rule\'';
			$GLOBALS['db']->query($sql);
		}

		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('shop_config') . (' SET value = \'' . $code . '\' WHERE code = \'integrate_code\'');
	}

	$GLOBALS['db']->query($sql);

	if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
		$cur_domain = $_SERVER['HTTP_X_FORWARDED_HOST'];
	}
	else if (isset($_SERVER['HTTP_HOST'])) {
		$cur_domain = $_SERVER['HTTP_HOST'];
	}
	else if (isset($_SERVER['SERVER_NAME'])) {
		$cur_domain = $_SERVER['SERVER_NAME'];
	}
	else if (isset($_SERVER['SERVER_ADDR'])) {
		$cur_domain = $_SERVER['SERVER_ADDR'];
	}

	$int_domain = str_replace(array('http://', 'https://'), array('', ''), $cfg['integrate_url']);

	if (strrpos($int_domain, '/')) {
		$int_domain = substr($int_domain, 0, strrpos($int_domain, '/'));
	}

	if ($cur_domain != $int_domain) {
		$same_domain = true;
		$domain = '';
		$cur_domain_arr = explode('.', $cur_domain);
		$int_domain_arr = explode('.', $int_domain);
		if (count($cur_domain_arr) != count($int_domain_arr) || $cur_domain_arr[0] == '' || $int_domain_arr[0] == '') {
			$same_domain = false;
		}
		else {
			$count = count($cur_domain_arr);

			for ($i = 1; $i < $count; $i++) {
				if ($cur_domain_arr[$i] != $int_domain_arr[$i]) {
					$domain = '';
					$same_domain = false;
					break;
				}
				else {
					$domain .= '.' . $cur_domain_arr[$i];
				}
			}
		}

		if ($same_domain == false) {
			$cfg['cookie_domain'] = '';
			$cfg['cookie_path'] = '/';
		}
		else {
			$cfg['cookie_domain'] = $domain;
			$cfg['cookie_path'] = '/';
		}
	}
	else {
		$cfg['cookie_domain'] = '';
		$cfg['cookie_path'] = '/';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'integrate_config\'';

	if ($GLOBALS['db']->GetOne($sql) == 0) {
		$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('shop_config') . ' (code, value) ' . 'VALUES (\'integrate_config\', \'' . serialize($cfg) . '\')';
	}
	else {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('shop_config') . ' SET value=\'' . serialize($cfg) . '\' ' . 'WHERE code=\'integrate_config\'';
	}

	$GLOBALS['db']->query($sql);
	clear_cache_files();
	return true;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ($_REQUEST['act'] == 'list') {
	$modules = read_modules('../includes/modules/integrates');

	for ($i = 0; $i < count($modules); $i++) {
		$modules[$i]['installed'] = $modules[$i]['code'] == $_CFG['integrate_code'] ? 1 : 0;
	}

	$allow_set_points = $_CFG['integrate_code'] == 'ecshop' ? 0 : 1;
	$smarty->assign('allow_set_points', $allow_set_points);
	$smarty->assign('ur_here', $_LANG['06_list_integrate']);
	$smarty->assign('modules', $modules);
	assign_query_info();
	$smarty->display('integrates_list.dwt');
}

if ($_REQUEST['act'] == 'install') {
	admin_priv('integrate_users', '');

	if ($_GET['code'] == 'ucenter') {
		$uc_client_dir = file_mode_info(ROOT_PATH . 'uc_client/data');

		if ($uc_client_dir === false) {
			sys_msg($_LANG['uc_client_not_exists'], 0);
		}

		if ($uc_client_dir < 7) {
			sys_msg($_LANG['uc_client_not_write'], 0);
		}
	}

	if ($_GET['code'] == 'ecshop') {
		$sql = 'UPDATE ' . $ecs->table('shop_config') . ' SET value = \'ecshop\' WHERE code = \'integrate_code\'';
		$db->query($sql);
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('shop_config') . ' SET value = \'\' WHERE code = \'points_rule\'';
		$GLOBALS['db']->query($sql);
		clear_cache_files();
		$links[0]['text'] = $_LANG['go_back'];
		$links[0]['href'] = 'integrate.php?act=list';
		sys_msg($_LANG['update_success'], 0, $links);
	}
	else {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET flag = 0, alias=\'\'' . ' WHERE flag > 0';
		$db->query($sql);
		$set_modules = true;
		include_once ROOT_PATH . 'includes/modules/integrates/' . $_GET['code'] . '.php';
		$set_modules = false;
		$cfg = $modules[0]['default'];
		$cfg['integrate_url'] = 'http://';
		assign_query_info();
		$smarty->assign('cfg', $cfg);
		$smarty->assign('save', 0);
		$smarty->assign('set_list', get_charset_list());
		$smarty->assign('ur_here', $_LANG['integrate_setup']);
		$smarty->assign('code', $_GET['code']);
		$smarty->display('integrates_setup.dwt');
	}
}

if ($_REQUEST['act'] == 'view_install_log') {
	$code = empty($_GET['code']) ? '' : trim($_GET['code']);
	if (empty($code) || file_exists(ROOT_PATH . DATA_DIR . '/integrate_' . $code . '_log.php')) {
		sys_msg($_LANG['lost_intall_log'], 1);
	}

	include ROOT_PATH . DATA_DIR . '/integrate_' . $code . '_log.php';
	if (isset($del_list) || isset($rename_list) || isset($ignore_list)) {
		if (isset($del_list)) {
			var_dump($del_list);
		}

		if (isset($rename_list)) {
			var_dump($rename_list);
		}

		if (isset($ignore_list)) {
			var_dump($ignore_list);
		}
	}
	else {
		sys_msg($_LANG['empty_intall_log'], 1);
	}
}

if ($_REQUEST['act'] == 'setup') {
	admin_priv('integrate_users', '');

	if ($_GET['code'] == 'ecshop') {
		sys_msg($_LANG['need_not_setup']);
	}
	else {
		$cfg = unserialize($_CFG['integrate_config']);
		assign_query_info();
		$smarty->assign('save', 1);
		$smarty->assign('set_list', get_charset_list());
		$smarty->assign('ur_here', $_LANG['integrate_setup']);
		$smarty->assign('code', $_GET['code']);
		$smarty->assign('cfg', $cfg);
		$smarty->display('integrates_setup.dwt');
	}
}

if ($_REQUEST['act'] == 'check_config') {
	$code = $_POST['code'];
	include_once ROOT_PATH . 'includes/modules/integrates/' . $code . '.php';
	$_POST['cfg']['quiet'] = 1;
	$cls_user = new $code($_POST['cfg']);

	if ($cls_user->error) {
		if ($cls_user->error == 1) {
			sys_msg($_LANG['error_db_msg']);
		}
		else if ($cls_user->error == 2) {
			sys_msg($_LANG['error_table_exist']);
		}
		else if ($cls_user->error == 1049) {
			sys_msg($_LANG['error_db_exist']);
		}
		else {
			sys_msg($cls_user->db->error());
		}
	}

	if ('4.1' <= $cls_user->db->version) {
		$sql = 'SHOW TABLE STATUS FROM `' . $cls_user->db_name . '` LIKE \'' . $cls_user->prefix . $cls_user->user_table . '\'';
		$row = $cls_user->db->getRow($sql);

		if (isset($row['Collation'])) {
			$db_charset = trim(substr($row['Collation'], 0, strpos($row['Collation'], '_')));

			if ($db_charset == 'latin1') {
				if (empty($_POST['cfg']['is_latin1'])) {
					sys_msg($_LANG['error_is_latin1'], NULL, NULL, false);
				}
			}
			else {
				$user_db_charset = $_POST['cfg']['db_charset'] == 'GB2312' ? 'GBK' : $_POST['cfg']['db_charset'];

				if (!empty($_POST['cfg']['is_latin1'])) {
					sys_msg($_LANG['error_not_latin1'], NULL, NULL, false);
				}

				if ($user_db_charset != strtoupper($db_charset)) {
					sys_msg(sprintf($_LANG['invalid_db_charset'], strtoupper($db_charset), $user_db_charset), NULL, NULL, false);
				}
			}
		}
	}

	$test_str = '测试中文字符';

	if ($_POST['cfg']['db_charset'] != 'UTF8') {
		$test_str = dsc_addslashes(ecs_iconv('UTF8', $_POST['cfg']['db_charset']));
	}

	$sql = 'SELECT ' . $cls_user->field_name . ' FROM ' . $cls_user->table($cls_user->user_table) . ' WHERE ' . $cls_user->field_name . (' = \'' . $test_str . '\'');
	$test = $cls_user->db->query($sql, 'SILENT');

	if (!$test) {
		sys_msg($_LANG['error_latin1'], NULL, NULL, false);
	}

	if (!empty($_POST['save'])) {
		if (save_integrate_config($code, $_POST['cfg'])) {
			sys_msg($_LANG['save_ok'], 0, array(
	array('text' => $_LANG['06_list_integrate'], 'href' => 'integrate.php?act=list')
	));
		}
		else {
			sys_msg($_LANG['save_error'], 0, array(
	array('text' => $_LANG['06_list_integrate'], 'href' => 'integrate.php?act=list')
	));
		}
	}

	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('users');
	$total = $db->getOne($sql);

	if ($total == 0) {
		save_integrate_config($_POST['code'], $_POST['cfg']);
		ecs_header("Location: integrate.php?act=complete\n");
		exit();
	}

	$_SESSION['cfg'] = $_POST['cfg'];
	$_SESSION['code'] = $code;
	$size = 100;
	$smarty->assign('ur_here', $_LANG['conflict_username_check']);
	$smarty->assign('domain', '@ecshop');
	$smarty->assign('lang_total', sprintf($_LANG['shop_user_total'], $total));
	$smarty->assign('size', $size);
	$smarty->display('integrates_check.htm');
}

if ($_REQUEST['act'] == 'save_uc_config') {
	$code = $_POST['code'];
	$cfg = unserialize($_CFG['integrate_config']);
	include_once ROOT_PATH . 'includes/modules/integrates/' . $code . '.php';
	$_POST['cfg']['quiet'] = 1;
	$cls_user = new $code($_POST['cfg']);

	if ($cls_user->error) {
		if ($cls_user->error == 1) {
			sys_msg($_LANG['error_db_msg']);
		}
		else if ($cls_user->error == 2) {
			sys_msg($_LANG['error_table_exist']);
		}
		else if ($cls_user->error == 1049) {
			sys_msg($_LANG['error_db_exist']);
		}
		else {
			sys_msg($cls_user->db->error());
		}
	}

	$cfg = array_merge($cfg, $_POST['cfg']);

	if (save_integrate_config($code, $cfg)) {
		sys_msg($_LANG['save_ok'], 0, array(
	array('text' => $_LANG['06_list_integrate'], 'href' => 'integrate.php?act=list')
	));
	}
	else {
		sys_msg($_LANG['save_error'], 0, array(
	array('text' => $_LANG['06_list_integrate'], 'href' => 'integrate.php?act=list')
	));
	}
}

if ($_REQUEST['act'] == 'save_uc_config_first') {
	$code = $_POST['code'];
	include_once ROOT_PATH . 'includes/modules/integrates/' . $code . '.php';
	$_POST['cfg']['quiet'] = 1;
	$cls_user = new $code($_POST['cfg']);

	if ($cls_user->error) {
		if ($cls_user->error == 1) {
			sys_msg($_LANG['error_db_msg']);
		}
		else if ($cls_user->error == 2) {
			sys_msg($_LANG['error_table_exist']);
		}
		else if ($cls_user->error == 1049) {
			sys_msg($_LANG['error_db_exist']);
		}
		else {
			sys_msg($cls_user->db->error());
		}
	}

	list($appauthkey, $appid, $ucdbhost, $ucdbname, $ucdbuser, $ucdbpw, $ucdbcharset, $uctablepre, $uccharset, $ucapi, $ucip) = explode('|', $_POST['ucconfig']);
	$uc_ip = !empty($ucip) ? $ucip : trim($_POST['uc_ip']);
	$uc_url = !empty($ucapi) ? $ucapi : trim($_POST['uc_url']);
	$cfg = array('uc_id' => $appid, 'uc_key' => $appauthkey, 'uc_url' => $uc_url, 'uc_ip' => $uc_ip, 'uc_connect' => 'mysql', 'uc_charset' => $uccharset, 'db_host' => $ucdbhost, 'db_user' => $ucdbuser, 'db_name' => $ucdbname, 'db_pass' => $ucdbpw, 'db_pre' => $uctablepre, 'db_charset' => $ucdbcharset);
	$cfg['uc_lang'] = $_LANG['uc_lang'];
	$_SESSION['cfg'] = $cfg;
	$_SESSION['code'] = $code;

	if (!empty($_POST['save'])) {
		if (save_integrate_config($code, $cfg)) {
			sys_msg($_LANG['save_ok'], 0, array(
	array('text' => $_LANG['06_list_integrate'], 'href' => 'integrate.php?act=list')
	));
		}
		else {
			sys_msg($_LANG['save_error'], 0, array(
	array('text' => $_LANG['06_list_integrate'], 'href' => 'integrate.php?act=list')
	));
		}
	}

	$query = $db->query('SHOW TABLE STATUS LIKE \'' . $GLOBALS['prefix'] . 'users' . '\'');
	$data = $db->fetch_array($query);

	if ($data['Auto_increment']) {
		$maxuid = $data['Auto_increment'] - 1;
	}
	else {
		$maxuid = 0;
	}

	save_integrate_config($code, $cfg);
	$smarty->assign('ur_here', $_LANG['ucenter_import_username']);
	$smarty->assign('user_startid_intro', sprintf($_LANG['user_startid_intro'], $maxuid, $maxuid));
	$smarty->display('integrates_uc_import.dwt');
}

if ($_REQUEST['act'] == 'check_user') {
	$code = $_SESSION['code'];
	include_once ROOT_PATH . 'includes/cls_json.php';
	include_once ROOT_PATH . 'includes/modules/integrates/' . $code . '.php';
	$cls_user = new $code($_SESSION['cfg']);
	$json = new JSON();
	$start = empty($_GET['start']) ? 0 : intval($_GET['start']);
	$size = empty($_GET['size']) ? 100 : intval($_GET['size']);
	$method = empty($_GET['method']) ? 1 : intval($_GET['method']);
	$domain = empty($_GET['domain']) ? '@ecshop' : trim($_GET['domain']);

	if ($size < 2) {
		$size = 2;
	}

	$_SESSION['domain'] = $domain;
	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('users');
	$total = $db->getOne($sql);
	$result = array('error' => 0, 'message' => '', 'start' => 0, 'size' => $size, 'content' => '', 'method' => $method, 'domain' => $domain, 'is_end' => 0);
	$sql = 'SELECT user_name FROM ' . $ecs->table('users') . (' LIMIT ' . $start . ', ' . $size);
	$user_list = $db->getCol($sql);
	$post_user_list = $cls_user->test_conflict($user_list);

	if ($post_user_list) {
		$post_user_list = addslashes_deep($post_user_list);

		if ($method == 2) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . (' SET flag = \'' . $method . '\', alias = CONCAT(user_name, \'' . $domain . '\') WHERE ') . db_create_in($post_user_list, 'user_name');
		}
		else {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . (' SET flag = \'' . $method . '\' WHERE ') . db_create_in($post_user_list, 'user_name');
		}

		$GLOBALS['db']->ping();
		$GLOBALS['db']->query($sql);

		if ($method == 2) {
			$count = count($post_user_list);
			$test_user_list = array();

			for ($i = 0; $i < $count; $i++) {
				$test_user_list[] = $post_user_list[$i] . $domain;
			}

			$error_user_list = $cls_user->test_conflict($test_user_list);

			if ($error_user_list) {
				$domain_len = 0 - str_len($domain);
				$count = count($error_user_list);

				for ($i = 0; $i < $count; $i++) {
					$error_user_list[$i] = substr($error_user_list[$i], 0, $domain_len);
				}

				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET flag = \'1\' WHERE ' . db_create_in($error_user_list, 'user_name');
			}

			$sql = 'SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE ' . db_create_in($test_user_list, 'user_name');
			$error_user_list = $GLOBALS['db']->getCol($sql);

			if ($error_user_list) {
				$domain_len = 0 - str_len($domain);
				$count = count($error_user_list);

				for ($i = 0; $i < $count; $i++) {
					$error_user_list[$i] = substr($error_user_list[$i], 0, $domain_len);
				}

				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET flag = \'1\' WHERE ' . db_create_in($error_user_list, 'user_name');
			}
		}
	}

	if ($start + $size < $total) {
		$result['start'] = $start + $size;
		$result['content'] = sprintf($_LANG['notice'], $result['start'], $total);
	}
	else {
		$start = $total;
		$result['content'] = $_LANG['check_complete'];
		$result['is_end'] = 1;
		$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('users') . ' WHERE flag > 0 ';

		if (0 < $db->getOne($sql)) {
			$result['href'] = 'integrate.php?act=modify';
		}
		else {
			$result['href'] = 'integrate.php?act=sync';
		}
	}

	exit($json->encode($result));
}

if ($_REQUEST['act'] == 'import_user') {
	$cfg = $_SESSION['cfg'];
	include_once ROOT_PATH . 'includes/cls_json.php';
	$ucdb = new cls_mysql($cfg['db_host'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_name'], $cfg['db_charset']);
	$json = new JSON();
	$result = array('error' => 0, 'message' => '');
	$query = $db->query('SHOW TABLE STATUS LIKE \'' . $GLOBALS['prefix'] . 'users' . '\'');
	$data = $db->fetch_array($query);

	if ($data['Auto_increment']) {
		$maxuid = $data['Auto_increment'] - 1;
	}
	else {
		$maxuid = 0;
	}

	$merge_method = intval($_POST['merge']);
	$merge_uid = array();
	$uc_uid = array();
	$repeat_user = array();
	$query = $db->query('SELECT * FROM ' . $ecs->table('users') . ' ORDER BY `user_id` ASC');

	while ($data = $db->fetch_array($query)) {
		$salt = rand(100000, 999999);
		$password = md5($data['password'] . $salt);
		$data['username'] = addslashes($data['user_name']);
		$data['username'] = addslashes($data['username']);
		$lastuid = $data['user_id'] + $maxuid;
		$uc_userinfo = $ucdb->getRow('SELECT `uid`, `password`, `salt` FROM ' . $cfg['db_pre'] . ('members WHERE `username`=\'' . $data['username'] . '\''));

		if (!$uc_userinfo) {
			$ucdb->query('INSERT LOW_PRIORITY INTO ' . $cfg['db_pre'] . ('members SET uid=\'' . $lastuid . '\', username=\'' . $data['username'] . '\', password=\'' . $password . '\', email=\'' . $data['email'] . '\', regip=\'' . $data['regip'] . '\', regdate=\'' . $data['regdate'] . '\', salt=\'' . $salt . '\''), 'SILENT');
			$ucdb->query('INSERT LOW_PRIORITY INTO ' . $cfg['db_pre'] . ('memberfields SET uid=\'' . $lastuid . '\''), 'SILENT');
		}
		else {
			if ($merge_method == 1) {
				if (md5($data['password'] . $uc_userinfo['salt']) == $uc_userinfo['password']) {
					$merge_uid[] = $data['user_id'];
					$uc_uid[] = array('user_id' => $data['user_id'], 'uid' => $uc_userinfo['uid']);
					continue;
				}
			}

			$ucdb->query('REPLACE INTO ' . $cfg['db_pre'] . 'mergemembers SET appid=\'' . UC_APPID . ('\', username=\'' . $data['username'] . '\''), 'SILENT');
			$repeat_user[] = $data;
		}
	}

	$ucdb->query('ALTER TABLE ' . $cfg['db_pre'] . 'members AUTO_INCREMENT=' . ($lastuid + 1), 'SILENT');
	$up_user_table = array('account_log', 'affiliate_log', 'booking_goods', 'collect_goods', 'comment', 'feedback', 'order_info', 'snatch_log', 'tag', 'users', 'user_account', 'user_address', 'user_bonus', 'reg_extend_info', 'user_feed', 'delivery_order', 'back_order');
	$truncate_user_table = array('cart', 'sessions', 'sessions_data');

	if (!empty($merge_uid)) {
		$merge_uid = implode(',', $merge_uid);
	}
	else {
		$merge_uid = 0;
	}

	foreach ($up_user_table as $table) {
		$db->query('UPDATE ' . $ecs->table($table) . (' SET `user_id`=`user_id`+ ' . $maxuid . ' ORDER BY `user_id` DESC'));

		foreach ($uc_uid as $uid) {
			$db->query('UPDATE ' . $ecs->table($table) . ' SET `user_id`=\'' . $uid['uid'] . '\' WHERE `user_id`=\'' . ($uid['user_id'] + $maxuid) . '\'');
		}
	}

	foreach ($truncate_user_table as $table) {
		$db->query('TRUNCATE TABLE ' . $ecs->table($table));
	}

	if (!empty($repeat_user)) {
		write_static_file_cache('repeat_user', $json->encode($repeat_user), 'php', ROOT_PATH . 'data/');
	}

	$result['error'] = 0;
	$result['message'] = $_LANG['import_user_success'];
	exit($json->encode($result));
}

if ($_REQUEST['act'] == 'modify') {
	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('users') . ' WHERE flag = 1';

	if (0 < $db->getOne($sql)) {
		$_REQUEST['flag'] = 1;
		$smarty->assign('default_flag', 1);
	}
	else {
		$_REQUEST['flag'] = 0;
		$smarty->assign('default_flag', 0);
	}

	$flags = array($_LANG['all_user'], $_LANG['error_user'], $_LANG['rename_user'], $_LANG['delete_user'], $_LANG['ignore_user']);
	$smarty->assign('flags', $flags);
	$arr = conflict_userlist();
	$smarty->assign('ur_here', $_LANG['conflict_username_modify']);
	$smarty->assign('domain', '@ecshop');
	$smarty->assign('list', $arr['list']);
	$smarty->assign('filter', $arr['filter']);
	$smarty->assign('record_count', $arr['record_count']);
	$smarty->assign('page_count', $arr['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->display('integrates_modify.htm');
}

if ($_REQUEST['act'] == 'query') {
	$arr = conflict_userlist();
	$smarty->assign('list', $arr['list']);
	$smarty->assign('filter', $arr['filter']);
	$smarty->assign('record_count', $arr['record_count']);
	$smarty->assign('page_count', $arr['page_count']);
	$smarty->assign('full_page', 0);
	make_json_result($smarty->fetch('integrates_modify.htm'), '', array('filter' => $arr['filter'], 'page_count' => $arr['page_count']));
}

if ($_REQUEST['act'] == 'act_modify') {
	$alias = array();

	foreach ($_POST['opt'] as $user_id => $val) {
		if ($val = 2) {
			$alias[] = $_POST['alias'][$user_id];
		}
	}

	if ($alias) {
		$sql = 'SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE ' . db_create_in($alias, 'user_name');
		$ecs_error_list = $db->getCol($sql);
		$code = $_SESSION['code'];
		include_once ROOT_PATH . 'includes/modules/integrates/' . $code . '.php';
		$cls_user = new $code($_SESSION['cfg']);
		$bbs_error_list = $cls_user->test_conflict($alias);
		$error_list = array_unique(array_merge($ecs_error_list, $bbs_error_list));

		if ($error_list) {
			foreach ($_POST['opt'] as $user_id => $val) {
				if ($val = 2) {
					if (in_array($_POST['alias'][$user_id], $error_list)) {
						$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . (' SET flag = 1,  alias=\'\' WHERE user_id = \'' . $user_id . '\'');
					}
					else {
						$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET flag = 2, alias = \'' . $_POST['alias'][$user_id] . '\'' . (' WHERE user_id = \'' . $user_id . '\'');
					}

					$db->query($sql);
				}
			}
		}
		else {
			foreach ($_POST['opt'] as $user_id => $val) {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET flag = 2, alias = \'' . $_POST['alias'][$user_id] . '\'' . (' WHERE user_id = \'' . $user_id . '\'');
				$db->query($sql);
			}
		}
	}

	foreach ($_POST['opt'] as $user_id => $val) {
		if ($val == 3 || $val == 4) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . (' SET flag=\'' . $val . '\' WHERE user_id=\'' . $user_id . '\'');
			$db->query($sql);
		}
	}

	ecs_header('Location: integrate.php?act=modify');
	exit();
}

if ($_REQUEST['act'] == 'sync') {
	$size = 100;
	$total = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('users'));
	$task_del = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('users') . ' WHERE flag = 3');
	$task_rename = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('users') . ' WHERE flag = 2');
	$task_ignore = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('users') . ' WHERE flag = 4');
	$task_sync = $total - $task_del - $task_ignore;
	$_SESSION['task'] = array(
	'del'    => array('total' => $task_del, 'start' => 0),
	'rename' => array('total' => $task_rename, 'start' => 0),
	'sync'   => array('total' => $task_sync, 'start' => 0)
	);
	$del_list = '';
	$rename_list = '';
	$ignore_list = '';
	$tasks = array();

	if (0 < $task_del) {
		$tasks[] = array('task_name' => sprintf($_LANG['task_del'], $task_del), 'task_status' => '<span id="task_del">' . $_LANG['task_uncomplete'] . '<span>');
		$sql = 'SELECT user_name FROM ' . $ecs->table('users') . ' WHERE flag = 2';
		$del_list = $db->getCol($sql);
	}

	if (0 < $task_rename) {
		$tasks[] = array('task_name' => sprintf($_LANG['task_rename'], $task_rename), 'task_status' => '<span id="task_rename">' . $_LANG['task_uncomplete'] . '</span>');
		$sql = 'SELECT user_name, alias FROM ' . $ecs->table('users') . ' WHERE flag = 3';
		$rename_list = $db->getAll($sql);
	}

	if (0 < $task_ignore) {
		$sql = 'SELECT user_name FROM ' . $ecs->table('users') . ' WHERE flag = 4';
		$ignore_list = $db->getCol($sql);
	}

	if (0 < $task_sync) {
		$tasks[] = array('task_name' => sprintf($_LANG['task_sync'], $task_sync), 'task_status' => '<span id="task_sync">' . $_LANG['task_uncomplete'] . '</span>');
	}

	$tasks[] = array('task_name' => $_LANG['task_save'], 'task_status' => '<span id="task_save">' . $_LANG['task_uncomplete'] . '</span>');
	$fp = @fopen(ROOT_PATH . DATA_DIR . '/integrate_' . $_SESSION['code'] . '_log.php', 'wb');
	$log = '';

	if (isset($del_list)) {
		$log .= '$del_list=' . var_export($del_list, true) . ';';
	}

	if (isset($rename_list)) {
		$log .= '$rename_list=' . var_export($rename_list, true) . ';';
	}

	if (isset($ignore_list)) {
		$log .= '$ignore_list=' . var_export($ignore_list, true) . ';';
	}

	fwrite($fp, $log);
	fclose($fp);
	$smarty->assign('tasks', $tasks);
	$smarty->assign('ur_here', $_LANG['user_sync']);
	$smarty->assign('size', $size);
	$smarty->display('integrates_sync.htm');
}

if ($_REQUEST['act'] == 'task') {
	if (empty($_GET['size']) || $_GET['size'] < 0) {
		$size = 100;
	}
	else {
		$size = intval($_GET['size']);
	}

	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('message' => '', 'error' => 0, 'content' => '', 'id' => '', 'end' => 0, 'size' => $size);

	if ($_SESSION['task']['del']['start'] < $_SESSION['task']['del']['total']) {
		$arr = $db->getCol('SELECT user_name FROM ' . $ecs->table('users') . ' WHERE flag = 3 LIMIT ' . $_SESSION['task']['del']['start'] . ',' . $result['size']);
		$db->query('DELETE FROM ' . $ecs->table('users') . ' WHERE ' . db_create_in($arr, 'user_name'));
		$result['id'] = 'task_del';

		if ($_SESSION['task']['del']['total'] <= $_SESSION['task']['del']['start'] + $result['size']) {
			$_SESSION['task']['del']['start'] = $_SESSION['task']['del']['total'];
			$result['content'] = $_LANG['task_complete'];
		}
		else {
			$_SESSION['task']['del']['start'] += $result['size'];
			$result['content'] = sprintf($_LANG['task_run'], $_SESSION['task']['del']['start'], $_SESSION['task']['del']['total']);
		}

		exit($json->encode($result));
	}
	else if ($_SESSION['task']['rename']['start'] < $_SESSION['task']['rename']['total']) {
		$arr = $db->getCol('SELECT user_name FROM ' . $ecs->table('users') . ' WHERE flag = 2 LIMIT ' . $_SESSION['task']['del']['start'] . ',' . $result['size']);
		$db->query('UPDATE ' . $ecs->table('users') . ' SET user_name=alias, alias=\'\' WHERE ' . db_create_in($arr, 'user_name'));
		$result['id'] = 'task_rename';

		if ($_SESSION['task']['rename']['total'] <= $_SESSION['task']['rename']['start'] + $result['size']) {
			$_SESSION['task']['rename']['start'] = $_SESSION['task']['rename']['total'];
			$result['content'] = $_LANG['task_complete'];
		}
		else {
			$_SESSION['task']['rename']['start'] += $result['size'];
			$result['content'] = sprintf($_LANG['task_run'], $_SESSION['task']['rename']['start'], $_SESSION['task']['rename']['total']);
		}

		exit($json->encode($result));
	}
	else if ($_SESSION['task']['sync']['start'] < $_SESSION['task']['sync']['total']) {
		$code = $_SESSION['code'];
		include_once ROOT_PATH . 'includes/modules/integrates/' . $code . '.php';
		$cls_user = new $code($_SESSION['cfg']);
		$cls_user->need_sync = false;
		$sql = 'SELECT user_name, password, email, sex, birthday, reg_time ' . 'FROM ' . $ecs->table('users') . ' LIMIT ' . $_SESSION['task']['del']['start'] . ',' . $result['size'];
		$arr = $db->getAll($sql);

		foreach ($arr as $user) {
			@$cls_user->add_user($user['user_name'], '', $user['email'], $user['sex'], $user['birthday'], $user['reg_time'], $user['password']);
		}

		$result['id'] = 'task_sync';

		if ($_SESSION['task']['sync']['total'] <= $_SESSION['task']['sync']['start'] + $result['size']) {
			$_SESSION['task']['sync']['start'] = $_SESSION['task']['sync']['total'];
			$result['content'] = $_LANG['task_complete'];
		}
		else {
			$_SESSION['task']['sync']['start'] += $result['size'];
			$result['content'] = sprintf($_LANG['task_run'], $_SESSION['task']['sync']['start'], $_SESSION['task']['sync']['total']);
		}

		exit($json->encode($result));
	}
	else {
		$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('shop_config') . ' WHERE code = \'integrate_code\'';

		if ($db->GetOne($sql) == 0) {
			$sql = 'INSERT INTO ' . $ecs->table('shop_config') . ' (code, value) ' . ('VALUES (\'integrate_code\', \'' . $_SESSION['code'] . '\')');
		}
		else {
			$sql = 'UPDATE ' . $ecs->table('shop_config') . (' SET value = \'' . $_SESSION['code'] . '\' WHERE code = \'integrate_code\'');
		}

		$db->query($sql);
		save_integrate_config($_SESSION['code'], $_SESSION['cfg']);
		$result['content'] = $_LANG['task_complete'];
		$result['id'] = 'task_save';
		$result['end'] = 1;
		unset($_SESSION['cfg']);
		unset($_SESSION['code']);
		unset($_SESSION['task']);
		unset($_SESSION['domain']);
		$sql = 'UPDATE ' . $ecs->table('users') . ' set flag = 0, alias = \'\' WHERE flag > 0';
		$db->query($sql);
		exit($json->encode($result));
	}
}

if ($_REQUEST['act'] == 'setup_ucenter') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	include_once ROOT_PATH . 'includes/cls_transport.php';
	$json = new JSON();
	$result = array('error' => 0, 'message' => '');
	$app_type = 'ECSHOP';
	$app_name = $db->getOne('SELECT value FROM ' . $ecs->table('shop_config') . ' WHERE code = \'shop_name\'');
	$app_url = $GLOBALS['ecs']->url();
	$app_charset = EC_CHARSET;
	$app_dbcharset = strtolower(str_replace('-', '', EC_CHARSET));
	$ucapi = !empty($_POST['ucapi']) ? trim($_POST['ucapi']) : '';
	$ucip = !empty($_POST['ucip']) ? trim($_POST['ucip']) : '';
	$dns_error = false;

	if (!$ucip) {
		$temp = @parse_url($ucapi);
		$ucip = gethostbyname($temp['host']);
		if (ip2long($ucip) == -1 || ip2long($ucip) === false) {
			$ucip = '';
			$dns_error = true;
		}
	}

	if ($dns_error) {
		$result['error'] = 2;
		$result['message'] = '';
		exit($json->encode($result));
	}

	$ucfounderpw = trim($_POST['ucfounderpw']);
	$app_tagtemplates = 'apptagtemplates[template]=' . urlencode('<a href="{url}" target="_blank">{goods_name}</a>') . '&' . 'apptagtemplates[fields][goods_name]=' . urlencode($_LANG['tagtemplates_goodsname']) . '&' . 'apptagtemplates[fields][uid]=' . urlencode($_LANG['tagtemplates_uid']) . '&' . 'apptagtemplates[fields][username]=' . urlencode($_LANG['tagtemplates_username']) . '&' . 'apptagtemplates[fields][dateline]=' . urlencode($_LANG['tagtemplates_dateline']) . '&' . 'apptagtemplates[fields][url]=' . urlencode($_LANG['tagtemplates_url']) . '&' . 'apptagtemplates[fields][image]=' . urlencode($_LANG['tagtemplates_image']) . '&' . 'apptagtemplates[fields][goods_price]=' . urlencode($_LANG['tagtemplates_price']);
	$postdata = 'm=app&a=add&ucfounder=&ucfounderpw=' . urlencode($ucfounderpw) . '&apptype=' . urlencode($app_type) . '&appname=' . urlencode($app_name) . '&appurl=' . urlencode($app_url) . '&appip=&appcharset=' . $app_charset . '&appdbcharset=' . $app_dbcharset . '&apptagtemplates=' . $app_tagtemplates;
	$t = new transport();
	$ucconfig = $t->request($ucapi . '/index.php', $postdata);
	$ucconfig = $ucconfig['body'];

	if (empty($ucconfig)) {
		$result['error'] = 1;
		$result['message'] = $_LANG['uc_msg_verify_failur'];
	}
	else if ($ucconfig == '-1') {
		$result['error'] = 1;
		$result['message'] = $_LANG['uc_msg_password_wrong'];
	}
	else {
		list($appauthkey, $appid) = explode('|', $ucconfig);
		if (empty($appauthkey) || empty($appid)) {
			$result['error'] = 1;
			$result['message'] = $_LANG['uc_msg_data_error'];
		}
		else {
			$result['error'] = 0;
			$result['message'] = $ucconfig;
		}
	}

	exit($json->encode($result));
}

if ($_REQUEST['act'] == 'complete') {
	sys_msg($_LANG['sync_ok'], 0, array(
	array('text' => $_LANG['06_list_integrate'], 'href' => 'integrate.php?act=list')
	));
}

if ($_REQUEST['act'] == 'points_set') {
	$rule_index = empty($_GET['rule_index']) ? '' : trim($_GET['rule_index']);
	$user = &init_users();
	$points = $user->get_points_name();

	if (empty($points)) {
		sys_msg($_LANG['no_points'], 0, array(
	array('text' => $_LANG['06_list_integrate'], 'href' => 'integrate.php?act=list')
	));
	}
	else if ($points == 'ucenter') {
		sys_msg($_LANG['uc_points'], 0, array(
	array('text' => $_LANG['uc_set_credits'], 'href' => UC_API, 'target' => '_blank')
	), false);
	}

	$rule = array();

	if ($_CFG['points_rule']) {
		$rule = unserialize($_CFG['points_rule']);
	}

	$points_key = array_keys($points);
	$count = count($points_key);
	$select_rule = array();
	$exist_rule = array();

	for ($i = 0; $i < $count; $i++) {
		if (!isset($rule[TO_P . $points_key[$i]])) {
			$select_rule[TO_P . $points_key[$i]] = $_LANG['bbs'] . $points[$points_key[$i]]['title'] . '->' . $_LANG['shop_pay_points'];
		}
		else {
			$exist_rule[TO_P . $points_key[$i]] = $_LANG['bbs'] . $points[$points_key[$i]]['title'] . '->' . $_LANG['shop_pay_points'];
		}
	}

	for ($i = 0; $i < $count; $i++) {
		if (!isset($rule[TO_R . $points_key[$i]])) {
			$select_rule[TO_R . $points_key[$i]] = $_LANG['bbs'] . $points[$points_key[$i]]['title'] . '->' . $_LANG['shop_rank_points'];
		}
		else {
			$exist_rule[TO_R . $points_key[$i]] = $_LANG['bbs'] . $points[$points_key[$i]]['title'] . '->' . $_LANG['shop_rank_points'];
		}
	}

	for ($i = 0; $i < $count; $i++) {
		if (!isset($rule[FROM_P . $points_key[$i]])) {
			$select_rule[FROM_P . $points_key[$i]] = $_LANG['shop_pay_points'] . '->' . $_LANG['bbs'] . $points[$points_key[$i]]['title'];
		}
		else {
			$exist_rule[FROM_P . $points_key[$i]] = $_LANG['shop_pay_points'] . '->' . $_LANG['bbs'] . $points[$points_key[$i]]['title'];
		}
	}

	for ($i = 0; $i < $count; $i++) {
		if (!isset($rule[FROM_R . $points_key[$i]])) {
			$select_rule[FROM_R . $points_key[$i]] = $_LANG['shop_rank_points'] . '->' . $_LANG['bbs'] . $points[$points_key[$i]]['title'];
		}
		else {
			$exist_rule[FROM_R . $points_key[$i]] = $_LANG['shop_rank_points'] . '->' . $_LANG['bbs'] . $points[$points_key[$i]]['title'];
		}
	}

	if ($rule_index && isset($rule[$rule_index]) || empty($select_rule)) {
		$allow_add = 0;
	}
	else {
		$allow_add = 1;
	}

	if ($rule_index && isset($rule[$rule_index])) {
		list($from_val, $to_val) = explode(':', $rule[$rule_index]);
		$select_rule[$rule_index] = $exist_rule[$rule_index];
		$smarty->assign('from_val', $from_val);
		$smarty->assign('to_val', $to_val);
	}

	$smarty->assign('rule_index', $rule_index);
	$smarty->assign('allow_add', $allow_add);
	$smarty->assign('select_rule', $select_rule);
	$smarty->assign('exist_rule', $exist_rule);
	$smarty->assign('rule_list', $rule);
	$smarty->assign('integral_name', $_CFG['integral_name']);
	$smarty->assign('full_page', 1);
	$smarty->assign('points', $points);
	$smarty->display('integrates_points.htm');
}

if ($_REQUEST['act'] == 'edit_points') {
	$rule_index = empty($_REQUEST['rule_index']) ? '' : trim($_REQUEST['rule_index']);
	$rule = array();

	if ($_CFG['points_rule']) {
		$rule = unserialize($_CFG['points_rule']);
	}

	if (isset($_POST['from_val']) && isset($_POST['to_val'])) {
		$from_val = empty($_POST['from_val']) ? 0 : intval($_POST['from_val']);
		$to_val = empty($_POST['to_val']) ? 1 : intval($_POST['to_val']);
		$old_rule_index = empty($_POST['old_rule_index']) ? '' : trim($_POST['old_rule_index']);
		if (empty($old_rule_index) || $old_rule_index == $rule_index) {
			$rule[$rule_index] = $from_val . ':' . $to_val;
		}
		else {
			$tmp_rule = array();

			foreach ($rule as $key => $val) {
				if ($key == $old_rule_index) {
					$tmp_rule[$rule_index] = $from_val . ':' . $to_val;
				}
				else {
					$tmp_rule[$key] = $val;
				}
			}

			$rule = $tmp_rule;
		}
	}
	else {
		unset($rule[$rule_index]);
	}

	$sql = 'UPDATE ' . $ecs->table('shop_config') . ' SET value =\'' . serialize($rule) . '\' WHERE code=\'points_rule\'';
	$db->query($sql);
	clear_cache_files();
	ecs_header("Location: integrate.php?act=points_set\n");
	exit();
}

if ($_REQUEST['act'] == 'save_points') {
	$keys = array_keys($_POST);
	$cfg = array();

	foreach ($keys as $key) {
		if (is_array($_POST[$key])) {
			$cfg[$key]['bbs_points'] = empty($_POST[$key]['bbs_points']) ? 0 : intval($_POST[$key]['bbs_points']);
			$cfg[$key]['fee_points'] = empty($_POST[$key]['fee_points']) ? 0 : intval($_POST[$key]['fee_points']);
			$cfg[$key]['pay_points'] = empty($_POST[$key]['pay_points']) ? 0 : intval($_POST[$key]['pay_points']);
			$cfg[$key]['rank_points'] = empty($_POST[$key]['rank_points']) ? 0 : intval($_POST[$key]['rank_points']);
		}
	}

	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('shop_config') . ' WHERE code=\'points_set\'';

	if ($db->getOne($sql) == 0) {
		$sql = 'INSERT INTO ' . $ecs->table('shop_config') . ' (parent_id, type, code, value) VALUES (6, \'hidden\', \'points_set\', \'' . serialize($cfg) . '\')';
	}
	else {
		$sql = 'UPDATE ' . $ecs->table('shop_config') . ' SET value =\'' . serialize($cfg) . '\' WHERE code=\'points_set\'';
	}

	$db->query($sql);
	clear_cache_files();
	sys_msg($_LANG['save_ok'], 0, array(
	array('text' => $_LANG['06_list_integrate'], 'href' => 'integrate.php?act=list')
	));
}

?>
