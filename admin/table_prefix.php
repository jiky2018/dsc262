<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function write_static_cache($cache_name, $caches, $cache_file_path = '', $type = 0, $url_data = array())
{
	if ((DEBUG_MODE & 2) == 2) {
		return false;
	}

	$sel_config = get_shop_config_val('open_memcached');

	if ($sel_config['open_memcached'] == 1) {
		$GLOBALS['cache']->set('static_caches_' . $cache_name, $caches);
	}
	else {
		if (!empty($cache_file_path)) {
			$cache_file_path = ROOT_PATH . $cache_file_path . $cache_name . '.php';
		}
		else {
			$cache_file_path = ROOT_PATH . '/temp/static_caches/' . $cache_name . '.php';
		}

		$content = "<?php\r\n";

		if ($type == 1) {
			$content .= '$url_data = ' . var_export($url_data, true) . ";\r\n";
			$content .= $caches . "\r\n";
		}
		else {
			$content .= '$data = ' . var_export($caches, true) . ";\r\n";
		}

		$content .= '?>';
		file_put_contents($cache_file_path, $content, LOCK_EX);
	}
}

function read_static_cache($cache_name, $cache_file_path = '')
{
	$data = '';

	if ((DEBUG_MODE & 2) == 2) {
		return false;
	}

	static $result = array();

	if (!empty($result[$cache_name])) {
		return $result[$cache_name];
	}

	$sel_config = get_shop_config_val('open_memcached');

	if ($sel_config['open_memcached'] == 1) {
		$result[$cache_name] = $GLOBALS['cache']->get('static_caches_' . $cache_name);
		return $result[$cache_name];
	}
	else {
		if (!empty($cache_file_path)) {
			$cache_file_path = ROOT_PATH . $cache_file_path . $cache_name . '.php';
		}
		else {
			$cache_file_path = ROOT_PATH . '/temp/static_caches/' . $cache_name . '.php';
		}

		if (file_exists($cache_file_path)) {
			include_once $cache_file_path;
			$result[$cache_name] = $data;
			return $result[$cache_name];
		}
		else {
			return false;
		}
	}
}

function assign_query_info()
{
	if ($GLOBALS['db']->queryTime == '') {
		$query_time = 0;
	}
	else if ('5.0.0' <= PHP_VERSION) {
		$query_time = number_format(microtime(true) - $GLOBALS['db']->queryTime, 6);
	}
	else {
		list($now_usec, $now_sec) = explode(' ', microtime());
		list($start_usec, $start_sec) = explode(' ', $GLOBALS['db']->queryTime);
		$query_time = number_format($now_sec - $start_sec + ($now_usec - $start_usec), 6);
	}

	$GLOBALS['smarty']->assign('query_info', sprintf($GLOBALS['_LANG']['query_info'], $GLOBALS['db']->queryCount, $query_time));
	if ($GLOBALS['_LANG']['memory_info'] && function_exists('memory_get_usage')) {
		$GLOBALS['smarty']->assign('memory_info', sprintf($GLOBALS['_LANG']['memory_info'], memory_get_usage() / 1048576));
	}

	$gzip_enabled = gzip_enabled() ? $GLOBALS['_LANG']['gzip_enabled'] : $GLOBALS['_LANG']['gzip_disabled'];
	$GLOBALS['smarty']->assign('gzip_enabled', $gzip_enabled);
}

function gzip_enabled()
{
	static $enabled_gzip;

	if ($enabled_gzip === NULL) {
		$enabled_gzip = $GLOBALS['_CFG']['enable_gzip'] && function_exists('ob_gzhandler');
	}

	return $enabled_gzip;
}

function get_shop_config_val($val = '')
{
	$sel_config = array();

	if (defined('CACHE_MEMCACHED')) {
		$sel_config['open_memcached'] = CACHE_MEMCACHED;
	}
	else {
		$sel_config['open_memcached'] = 0;
	}

	return $sel_config;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init_table.php';

if ($_REQUEST['act'] == 'edit') {
	$smarty->assign('prefix', $prefix);
	$smarty->assign('ur_here', $_LANG['05_table_prefix']);
	assign_query_info();
	$smarty->display('table_prefix.dwt');
}
else if ($_REQUEST['act'] == 'update') {
	$new_prefix = !empty($_REQUEST['new_prefix']) ? trim($_REQUEST['new_prefix']) : '';
	$smarty->assign('ur_here', $_LANG['05_table_prefix']);
	$db_name = $db->dbname;
	$sql = 'SELECT CONCAT( \'ALTER TABLE \', table_name, \' RENAME TO \', replace(table_name,\'' . $prefix . '\',\'' . $new_prefix . '\'),\';\') AS prefix FROM information_schema.tables WHERE TABLE_SCHEMA = \'' . $db_name . '\' and table_name LIKE \'' . $prefix . '%\';';
	$res = $db->getAll($sql);
	$list = array();

	foreach ($res as $k => $v) {
		$list[$k]['prefix'] = $prefix;
		$list[$k]['new_prefix'] = $new_prefix;
		$list[$k]['edit_table'] = $v['prefix'];
	}

	if ($list) {
		write_static_cache('table_prefix', $list, '/data/sc_file/');
	}

	$table_list = read_static_cache('table_prefix', '/data/sc_file/');

	if ($table_list !== false) {
		$table_list = $ecs->page_array(1, 1, $table_list);
		$smarty->assign('record_count', $table_list['filter']['record_count']);
	}

	$smarty->assign('page', 1);
	assign_query_info();
	$smarty->display('table_list.dwt');
}
else if ($_REQUEST['act'] == 'ajax_update') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$page_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 1;
	$table_list = read_static_cache('table_prefix', '/data/sc_file/');
	@set_time_limit(300);

	if ($table_list !== false) {
		$table_list = $ecs->page_array($page_size, $page, $table_list);
	}

	$result['list'] = $table_list['list'][0];
	$result['page'] = $table_list['filter']['page'] + 1;
	$result['page_size'] = $table_list['filter']['page_size'];
	$result['record_count'] = $table_list['filter']['record_count'];
	$result['page_count'] = $table_list['filter']['page_count'];
	$result['is_stop'] = 1;

	if ($table_list['filter']['page_count'] < $page) {
		$result['is_stop'] = 0;
	}
	else {
		$db->query($table_list['list'][0]['edit_table']);
		$result['filter_page'] = $table_list['filter']['page'];
	}

	exit($json->encode($result));
}

?>
