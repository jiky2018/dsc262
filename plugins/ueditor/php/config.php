<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
header('Content-Type: text/html; charset=utf-8');
error_reporting(1 | 2);
define('IN_ECS', true);
define('ROOT_PATH', preg_replace('/plugins(.*)/i', '', str_replace('\\', '/', __FILE__)));

if (isset($_SERVER['PHP_SELF'])) {
	define('PHP_SELF', $_SERVER['PHP_SELF']);
}
else {
	define('PHP_SELF', $_SERVER['SCRIPT_NAME']);
}

$root_path = preg_replace('/plugins(.*)/i', '', PHP_SELF);
$root_path_relative = '../../../';
require ROOT_PATH . 'data/config.php';
require ROOT_PATH . 'includes/lib_base.php';
require ROOT_PATH . 'includes/cls_mysql.php';
require ROOT_PATH . 'includes/cls_ecshop.php';
require ROOT_PATH . 'includes/cls_session.php';
require ROOT_PATH . 'includes/lib_common.php';
require ROOT_PATH . 'includes/lib_oss.php';
require ROOT_PATH . 'includes/Http.class.php';
require ROOT_PATH . 'includes/lib_ecmoban.php';
$sel_config = get_shop_config_val('open_memcached');

if ($sel_config['open_memcached'] == 1) {
	require ROOT_PATH . 'includes/cls_cache.php';
	require ROOT_PATH . 'data/cache_config.php';
	$cache = new cls_cache($cache_config);
}

$ecs = new ECS($db_name, $prefix);
define('DATA_DIR', $ecs->data_dir());
define('IMAGE_DIR', $ecs->image_dir());
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$enable = true;
$sess = new cls_session($db, $ecs->table('sessions'), $ecs->table('sessions_data'), 'ECSCP_ID');
$_CFG = load_config();
require 'Uploader.class.php';

?>
