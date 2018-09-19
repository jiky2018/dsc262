<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function addslashes_deep($value)
{
	if (empty($value)) {
		return $value;
	}
	else {
		return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
	}
}

function load_config()
{
	$arr = array();
	$data = read_static_cache('shop_config');

	if ($data === false) {
		$lang_array = array('zh_cn', 'zh_tw', 'en_us');
		if (empty($arr['lang']) || !in_array($arr['lang'], $lang_array)) {
			$arr['lang'] = 'zh_cn';
		}

		write_static_cache('shop_config', $arr);
	}
	else {
		$arr = $data;
	}

	return $arr;
}

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

if (file_exists('../data/config.php')) {
	include '../data/config.php';
}
else {
	include '../includes/config.php';
}

if (!defined('ADMIN_PATH')) {
	define('ADMIN_PATH', 'admin');
}

define('ROOT_PATH', str_replace(ADMIN_PATH . '/includes/init_table.php', '', str_replace('\\', '/', __FILE__)));

if (defined('DEBUG_MODE') == false) {
	define('DEBUG_MODE', 0);
}

if ('5.1' <= PHP_VERSION && !empty($timezone)) {
	date_default_timezone_set($timezone);
}

if (isset($_SERVER['PHP_SELF']) && !empty($_SERVER['PHP_SELF'])) {
	define('PHP_SELF', $_SERVER['PHP_SELF']);
}
else {
	define('PHP_SELF', $_SERVER['SCRIPT_NAME']);
}

require ROOT_PATH . 'includes/inc_constant.php';
require ROOT_PATH . 'includes/cls_error.php';
require ROOT_PATH . 'includes/cls_ecshop.php';

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
$err = new ecs_error('message.dwt');

if (!isset($_REQUEST['act'])) {
	$_REQUEST['act'] = '';
}
else {
	if (($_REQUEST['act'] == 'login' || $_REQUEST['act'] == 'logout' || $_REQUEST['act'] == 'signin') && strpos(PHP_SELF, '/privilege.php') === false) {
		$_REQUEST['act'] = '';
	}
	else {
		if (($_REQUEST['act'] == 'forget_pwd' || $_REQUEST['act'] == 'reset_pwd' || $_REQUEST['act'] == 'get_pwd') && strpos(PHP_SELF, '/get_password.php') === false) {
			$_REQUEST['act'] = '';
		}
	}
}

$_CFG = load_config();

if ($_REQUEST['act'] == 'captcha') {
	require ROOT_PATH . '/includes/cls_captcha_verify.php';
	$code_config = array('imageW' => '120', 'imageH' => '36', 'fontSize' => '18', 'length' => '4', 'useNoise' => false);
	$code_config['seKey'] = 'admin_login';
	$img = new Verify($code_config);
	$img->entry();
	exit();
}

require ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/common.php';
require ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/log_action.php';

if (file_exists(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/' . basename(PHP_SELF))) {
	include ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/' . basename(PHP_SELF);
}

if (!file_exists('../temp/caches')) {
	@mkdir('../temp/caches', 511);
	@chmod('../temp/caches', 511);
}

if (!file_exists('../temp/compiled/admin')) {
	@mkdir('../temp/compiled/admin', 511);
	@chmod('../temp/compiled/admin', 511);
}

clearstatcache();

if (!isset($_CFG['dsc_version'])) {
	$_CFG['dsc_version'] = 'v1.4';
}

if (preg_replace('/(?:\\.|\\s+)[a-z]*$/i', '', $_CFG['dsc_version']) != preg_replace('/(?:\\.|\\s+)[a-z]*$/i', '', VERSION) && file_exists('../upgrade/index.php')) {
	ecs_header("Location: ../upgrade/index.php\n");
	exit();
}

require ROOT_PATH . 'includes/cls_template.php';
$smarty = new cls_template();
$smarty->template_dir = ROOT_PATH . ADMIN_PATH . '/templates';
$smarty->compile_dir = ROOT_PATH . 'temp/compiled/admin';

if ((DEBUG_MODE & 2) == 2) {
	$smarty->force_compile = true;
}

$smarty->assign('lang', $_LANG);
$smarty->assign('help_open', $_CFG['help_open']);
$smarty->assign('cat_belongs', $_CFG['cat_belongs']);
$smarty->assign('brand_belongs', $_CFG['brand_belongs']);

if (isset($_CFG['enable_order_check'])) {
	$smarty->assign('enable_order_check', $_CFG['enable_order_check']);
}
else {
	$smarty->assign('enable_order_check', 0);
}

$smarty->assign('token', $_CFG['token']);
if ($_REQUEST['act'] != 'login' && $_REQUEST['act'] != 'signin' && $_REQUEST['act'] != 'forget_pwd' && $_REQUEST['act'] != 'reset_pwd' && $_REQUEST['act'] != 'check_order') {
	$admin_path = preg_replace('/:\\d+/', '', $ecs->url()) . ADMIN_PATH;
	if (!empty($_SERVER['HTTP_REFERER']) && strpos(preg_replace('/:\\d+/', '', $_SERVER['HTTP_REFERER']), $admin_path) === false) {
		if (!empty($_REQUEST['is_ajax'])) {
			make_json_error($_LANG['priv_error']);
		}
		else {
			ecs_header("Location: privilege.php?act=login\n");
		}

		exit();
	}
}

if ($_REQUEST['act'] == 'phpinfo' && function_exists('phpinfo')) {
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

?>
