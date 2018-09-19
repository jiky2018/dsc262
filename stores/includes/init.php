<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

define('ECS_ADMIN', true);
error_reporting(32767);

if (__FILE__ == '') {
	exit('Fatal error code: 0');
}

@ini_set('memory_limit', '1024M');
@ini_set('session.cache_expire', 180);
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_cookies', 1);
@ini_set('session.auto_start', 0);
@ini_set('display_errors', 1);

if (DIRECTORY_SEPARATOR == '\\') {
	@ini_set('include_path', '.;' . ROOT_PATH);
}
else {
	@ini_set('include_path', '.:' . ROOT_PATH);
}

require '../vendor/autoload.php';

if (file_exists('../data/config.php')) {
	include '../data/config.php';
}
else {
	include '../includes/config.php';
}

require '../data/database.php';

if (!defined('STORES_PATH')) {
	define('STORES_PATH', 'stores');
}

define('ROOT_PATH', str_replace(STORES_PATH . '/includes/init.php', '', str_replace('\\', '/', __FILE__)));

if (defined('DEBUG_MODE') == false) {
	define('DEBUG_MODE', 0);
}

if (('5.1' <= PHP_VERSION) && !empty($timezone)) {
	date_default_timezone_set($timezone);
}

if (isset($_SERVER['PHP_SELF']) && !empty($_SERVER['PHP_SELF'])) {
	define('PHP_SELF', $_SERVER['PHP_SELF']);
}
else {
	define('PHP_SELF', $_SERVER['SCRIPT_NAME']);
}

require ROOT_PATH . 'includes/Http.class.php';
require ROOT_PATH . 'includes/inc_constant.php';
require ROOT_PATH . 'includes/cls_ecshop.php';
require ROOT_PATH . 'includes/cls_error.php';
require ROOT_PATH . 'includes/lib_time.php';
require ROOT_PATH . 'includes/lib_base.php';
require ROOT_PATH . 'includes/lib_common.php';
require ROOT_PATH . 'includes/lib_input.php';
require ROOT_PATH . 'includes/cls_pinyin.php';
require ROOT_PATH . 'includes/lib_scws.php';
require ROOT_PATH . STORES_PATH . '/includes/lib_main.php';
require ROOT_PATH . STORES_PATH . '/includes/cls_exchange.php';
require ROOT_PATH . 'includes/lib_ecmoban.php';
require ROOT_PATH . 'includes/lib_ecmobanFunc.php';
require ROOT_PATH . 'includes/lib_oss.php';
require ROOT_PATH . 'data/sms_config.php';

if (!get_magic_quotes_gpc()) {
	if (!empty($_GET)) {
		$_GET = addslashes_deep($_GET);
	}

	if (!empty($_POST)) {
		$_POST = addslashes_deep($_POST);
	}

	$_COOKIE = addslashes_deep($_COOKIE);
	$_REQUEST = addslashes_deep($_REQUEST);
}

if (strpos(PHP_SELF, '.php/') !== false) {
	ecs_header('Location:' . substr(PHP_SELF, 0, strpos(PHP_SELF, '.php/') + 4) . "\n");
	exit();
}

$ecs = new ECS($db_name, $prefix);
define('DATA_DIR', $ecs->data_dir());
define('IMAGE_DIR', $ecs->image_dir());
require ROOT_PATH . 'includes/cls_mysql.php';
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db_host = $db_user = $db_pass = $db_name = NULL;
$err = new ecs_error('message.htm');
require ROOT_PATH . 'includes/cls_session.php';
$sess = new cls_session($db, $ecs->table('sessions'), $ecs->table('sessions_data'), 'ECSCP_SELLER_ID');

if (!isset($_REQUEST['act'])) {
	$_REQUEST['act'] = '';
}
else {
	if ((($_REQUEST['act'] == 'login') || ($_REQUEST['act'] == 'logout') || ($_REQUEST['act'] == 'signin')) && (strpos(PHP_SELF, '/privilege.php') === false)) {
		$_REQUEST['act'] = '';
	}
	else {
		if ((($_REQUEST['act'] == 'forget_pwd') || ($_REQUEST['act'] == 'reset_pwd') || ($_REQUEST['act'] == 'get_pwd')) && (strpos(PHP_SELF, '/get_password.php') === false)) {
			$_REQUEST['act'] = '';
		}
	}
}

$sel_config = get_shop_config_val('open_memcached');

if ($sel_config['open_memcached'] == 1) {
	require ROOT_PATH . 'includes/cls_cache.php';
	require ROOT_PATH . 'data/cache_config.php';
	$cache = new cls_cache($cache_config);
}

$_CFG = load_config();
$_CFG['editing_tools'] = 'seller_ueditor';

if ($_REQUEST['act'] == 'captcha') {
	require ROOT_PATH . '/includes/cls_captcha_verify.php';
	$code_config = array('imageW' => '120', 'imageH' => '36', 'fontSize' => '18', 'length' => '4', 'useNoise' => false);
	$code_config['seKey'] = 'admin_login';
	$img = new Verify($code_config);
	$img->entry();
	exit();
}

require ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/common_merchants.php';
require ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/common_stores.php';
require ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/log_action.php';

if (file_exists(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/' . basename(PHP_SELF))) {
	include ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/' . basename(PHP_SELF);
}

if (!file_exists('../temp/caches')) {
	@mkdir('../temp/caches', 511);
	@chmod('../temp/caches', 511);
}

if (!file_exists('../temp/compiled/' . STORES_PATH)) {
	@mkdir('../temp/compiled/' . STORES_PATH, 511);
	@chmod('../temp/compiled/' . STORES_PATH, 511);
}

clearstatcache();

if (!isset($_CFG['dsc_version'])) {
	$_CFG['dsc_version'] = 'v1.0';
}

if ((preg_replace('/(?:\\.|\\s+)[a-z]*$/i', '', $_CFG['dsc_version']) != preg_replace('/(?:\\.|\\s+)[a-z]*$/i', '', VERSION)) && file_exists('../upgrade/index.php')) {
}

require ROOT_PATH . 'includes/cls_template.php';
$smarty = new cls_template();
$smarty->template_dir = ROOT_PATH . STORES_PATH . '/templates';
$smarty->compile_dir = ROOT_PATH . 'temp/compiled/' . STORES_PATH;

if ((DEBUG_MODE & 2) == 2) {
	$smarty->force_compile = true;
}

$smarty->assign('lang', $_LANG);
$smarty->assign('help_open', $_CFG['help_open']);

if (isset($_CFG['enable_order_check'])) {
	$smarty->assign('enable_order_check', $_CFG['enable_order_check']);
}
else {
	$smarty->assign('enable_order_check', 0);
}

if (isset($_GET['ent_id']) && isset($_GET['ent_ac']) && isset($_GET['ent_sign']) && isset($_GET['ent_email'])) {
	$ent_id = addslashes(trim($_GET['ent_id']));
	$ent_ac = addslashes(trim($_GET['ent_ac']));
	$ent_sign = addslashes(trim($_GET['ent_sign']));
	$ent_email = addslashes(trim($_GET['ent_email']));
	$certificate_id = addslashes(trim($_CFG['certificate_id']));
	$domain_url = $ecs->url();
	$token = addslashes($_GET['token']);

	if ($token == md5(md5($_CFG['token']) . $domain_url . ADMIN_PATH)) {
		require ROOT_PATH . 'includes/cls_transport.php';
		$t = new transport('-1', 5);
		$apiget = 'act=ent_sign&ent_id= ' . $ent_id . ' & certificate_id=' . $certificate_id;
		$t->request('http://cloud.ecmoban.com/api.php', $apiget);
		$db->query('UPDATE ' . $ecs->table('shop_config') . ' SET value = "' . $ent_id . '" WHERE code = "ent_id"');
		$db->query('UPDATE ' . $ecs->table('shop_config') . ' SET value = "' . $ent_ac . '" WHERE code = "ent_ac"');
		$db->query('UPDATE ' . $ecs->table('shop_config') . ' SET value = "' . $ent_sign . '" WHERE code = "ent_sign"');
		$db->query('UPDATE ' . $ecs->table('shop_config') . ' SET value = "' . $ent_email . '" WHERE code = "ent_email"');
		clear_cache_files();
		ecs_header("Location: ./index.php\n");
	}
}

if ((!isset($_SESSION['store_user_id']) || (intval($_SESSION['store_user_id']) <= 0)) && ($_REQUEST['act'] != 'login') && ($_REQUEST['act'] != 'signin') && ($_REQUEST['act'] != 'check_user_name') && ($_REQUEST['act'] != 'check_user_password') && ($_REQUEST['act'] != 'operate') && ($_REQUEST['act'] != 'forget_pwd') && ($_REQUEST['act'] != 'reset_pwd') && ($_REQUEST['act'] != 'check_order')) {
	if (!empty($_COOKIE['ECSCP']['store_user_id']) && !empty($_COOKIE['ECSCP']['stores_pass'])) {
		$sql = 'SELECT id, stores_user, stores_pwd ' . ' FROM ' . $ecs->table('store_user') . ' WHERE id = \'' . intval($_COOKIE['ECSCP']['store_user_id']) . '\'';
		$row = $db->GetRow($sql);

		if (!$row) {
			setcookie($_COOKIE['ECSCP']['store_user_id'], '', 1);
			setcookie($_COOKIE['ECSCP']['stores_pass'], '', 1);

			if (!empty($_REQUEST['is_ajax'])) {
				make_json_error($_LANG['priv_error']);
			}
			else {
				ecs_header("Location: privilege.php?act=login\n");
			}

			exit();
		}
		else if (md5($row['password'] . $_CFG['hash_code']) == $_COOKIE['ECSCP']['stores_pass']) {
			!isset($row['last_time']) && $row['last_time'] = '';
			set_admin_session($row['id'], $row['stores_user']);
		}
		else {
			setcookie($_COOKIE['ECSCP']['store_user_id'], '', 1);
			setcookie($_COOKIE['ECSCP']['stores_pass'], '', 1);

			if (!empty($_REQUEST['is_ajax'])) {
				make_json_error($_LANG['priv_error']);
			}
			else {
				ecs_header("Location: privilege.php?act=login\n");
			}

			exit();
		}
	}
	else {
		if (!empty($_REQUEST['is_ajax'])) {
			make_json_error($_LANG['priv_error']);
		}
		else {
			ecs_header("Location: privilege.php?act=login\n");
		}

		exit();
	}
}

$smarty->assign('token', $_CFG['token']);
if (($_REQUEST['act'] != 'login') && ($_REQUEST['act'] != 'signin') && ($_REQUEST['act'] != 'forget_pwd') && ($_REQUEST['act'] != 'reset_pwd') && ($_REQUEST['act'] != 'check_order')) {
	$admin_path = preg_replace('/:\\d+/', '', $ecs->stores_url()) . STORES_PATH;
	if (!empty($_SERVER['HTTP_REFERER']) && (strpos(preg_replace('/:\\d+/', '', $_SERVER['HTTP_REFERER']), $admin_path) === false)) {
		if (!empty($_REQUEST['is_ajax'])) {
			make_json_error($_LANG['priv_error']);
		}
		else {
			ecs_header("Location: privilege.php?act=login\n");
		}

		exit();
	}
}

if (isset($_SESSION['stores_name'])) {
	$admin_sql = 'select id from ' . $GLOBALS['ecs']->table('store_user') . ' where stores_user = \'' . $_SESSION['stores_name'] . '\'';
	$uid = $GLOBALS['db']->getOne($admin_sql);
	$uname = '';
	if ((0 < $_SESSION['store_user_id']) && ($_SESSION['store_user_id'] != $uid)) {
		$admin_sql = 'select stores_user from ' . $GLOBALS['ecs']->table('store_user') . ' where id = \'' . $_SESSION['store_user_id'] . '\'';
		$uname = $GLOBALS['db']->getOne($admin_sql);
		$_SESSION['stores_name'] = $uname;
	}
}

if (($_REQUEST['act'] == 'phpinfo') && function_exists('phpinfo')) {
	phpinfo();
	exit();
}

header('content-type: text/html; charset=' . EC_CHARSET);
header('Expires: Fri, 14 Mar 1980 20:53:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

if ((DEBUG_MODE & 1) == 1) {
	error_reporting(32767);
}
else {
	error_reporting(32767 ^ 8);
}

if ((DEBUG_MODE & 4) == 4) {
	include ROOT_PATH . 'includes/lib.debug.php';
}

if (isset($_SESSION['admin_ru_id']) && $_SESSION['admin_ru_id']) {
	$smarty->assign('ru_id', $_SESSION['admin_ru_id']);
}

if (!empty($_SESSION['store_user_id'])) {
	$store_user_id = $_SESSION['store_user_id'];
	$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('store_user') . ' WHERE id = \'' . $store_user_id . '\' ';
	$store_user_info = $GLOBALS['db']->getRow($sql);
	$smarty->assign('store_user_info', $store_user_info);
}

?>
