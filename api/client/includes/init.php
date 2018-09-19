<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
error_reporting(7);

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

define('CLIENT_PATH', substr(__FILE__, 0, -17));
define('ROOT_PATH', substr(__FILE__, 0, -28));
$php_self = (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);

if ('/' == substr($php_self, -1)) {
	$php_self .= 'index.php';
}

define('PHP_SELF', $php_self);
require ROOT_PATH . 'data/config.php';
require ROOT_PATH . 'includes/lib_common.php';
require ROOT_PATH . 'includes/lib_ecmoban.php';
require ROOT_PATH . 'includes/Http.class.php';
require ROOT_PATH . 'includes/lib_ecmobanFunc.php';
require ROOT_PATH . 'includes/lib_seller_store.php';
require ROOT_PATH . 'includes/lib_ipCity.php';
require ROOT_PATH . 'includes/cls_ecmac.php';
require ROOT_PATH . 'includes/cls_mysql.php';

if (!function_exists('addslashes_deep')) {
	require ROOT_PATH . 'includes/lib_base.php';
}

require CLIENT_PATH . 'includes/lib_api.php';
require CLIENT_PATH . 'includes/lib_struct.php';
require ROOT_PATH . 'includes/cls_json.php';

if (!get_magic_quotes_gpc()) {
	$_COOKIE = addslashes_deep($_COOKIE);
}

if (!defined('EC_CHARSET')) {
	define('EC_CHARSET', 'utf-8');
}

$json = new JSON();
parse_json($json, $_POST['Json']);
require ROOT_PATH . 'includes/inc_constant.php';
require ROOT_PATH . 'includes/cls_ecshop.php';
require ROOT_PATH . 'includes/lib_time.php';
require ROOT_PATH . 'includes/lib_main.php';
require ROOT_PATH . 'includes/lib_insert.php';
require ROOT_PATH . 'includes/lib_goods.php';
$ecs = new ECS($db_name, $prefix);
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db->set_disable_cache_tables(array($ecs->table('sessions'), $ecs->table('sessions_data'), $ecs->table('cart')));
$db_host = $db_user = $db_pass = $db_name = NULL;
$_CFG = load_config();
require ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/common.php';
require ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/log_action.php';
include ROOT_PATH . 'includes/cls_session.php';
$sess = new cls_session($db, $ecs->table('sessions'), $ecs->table('sessions_data'), 'CL_ECSCP_ID');
define('SESS_ID', $sess->get_session_id());
if ((!isset($_SESSION['admin_id']) || (intval($_SESSION['admin_id']) <= 0)) && ($_POST['Action'] != 'UserLogin')) {
	client_show_message(110);
}

if ($_CFG['shop_closed'] == 1) {
	client_show_message(105);
}

?>
