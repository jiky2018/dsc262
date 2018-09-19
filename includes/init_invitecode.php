<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_adv($type, $id)
{
	$sql = 'select ap.ad_width,ap.ad_height,ad.ad_name,ad.ad_code,ad.ad_link,ad.media_type from ' . $GLOBALS['ecs']->table('ad_position') . ' as ap left join ' . $GLOBALS['ecs']->table('ad') . ' as ad on ad.position_id = ap.position_id where ad.ad_name=\'' . $type . '_' . $id . '\' and (ad.media_type=0 OR ad.media_type=3) and UNIX_TIMESTAMP()>ad.start_time and UNIX_TIMESTAMP()<ad.end_time and ad.enabled=1';
	$row = $GLOBALS['db']->getRow($sql);

	if ($row) {
		if ($row['media_type'] == 0) {
			$src = ((strpos($row['ad_code'], 'http://') === false) && (strpos($row['ad_code'], 'https://') === false) ? DATA_DIR . '/afficheimg/' . $row['ad_code'] : $row['ad_code']);
			return '<a href=\'' . $row['ad_link'] . "'\r\n                target='_blank'><img src='" . $src . '\' width=\'' . $row['ad_width'] . '\' height=\'' . $row['ad_height'] . "'\r\n                border='0' /></a>";
		}
		else {
			return '<a href=\'' . $row['ad_link'] . "'\r\n                target='_blank'>" . htmlspecialchars($row['ad_code']) . '</a>';
		}
	}
	else {
		return '';
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

error_reporting(32767);

if (__FILE__ == '') {
	exit('Fatal error code: 0');
}

define('ROOT_PATH', str_replace('includes/init_invitecode.php', '', str_replace('\\', '/', __FILE__)));
@ini_set('memory_limit', '64M');
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

require ROOT_PATH . 'data/config.php';

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
require ROOT_PATH . 'includes/cls_error.php';
require ROOT_PATH . 'includes/lib_time.php';
require ROOT_PATH . 'includes/lib_base.php';
require ROOT_PATH . 'includes/lib_common.php';
require ROOT_PATH . 'includes/lib_main.php';
require ROOT_PATH . 'includes/lib_insert.php';
require ROOT_PATH . 'includes/lib_goods.php';
require ROOT_PATH . 'includes/lib_article.php';
require ROOT_PATH . '/includes/cls_captcha_verify.php';
require ROOT_PATH . 'includes/cls_pinyin.php';
require ROOT_PATH . 'includes/lib_scws.php';
require ROOT_PATH . 'includes/lib_ecmoban.php';
require ROOT_PATH . 'includes/Http.class.php';
require ROOT_PATH . 'includes/lib_ecmobanFunc.php';
require ROOT_PATH . 'includes/lib_seller_store.php';
require ROOT_PATH . 'includes/lib_ipCity.php';

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
define('DATA_DIR', $ecs->data_dir());
define('IMAGE_DIR', $ecs->image_dir());
require ROOT_PATH . 'includes/cls_mysql.php';
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db->set_disable_cache_tables(array($ecs->table('sessions'), $ecs->table('sessions_data'), $ecs->table('cart')));
$db_host = $db_user = $db_pass = $db_name = NULL;
$err = new ecs_error('message.dwt');
$_CFG = load_config();
require ROOT_PATH . 'languages/' . $_CFG['lang'] . '/common.php';

if ($_CFG['shop_closed'] == 1) {
	header('Content-type: text/html; charset=' . EC_CHARSET);
	exit('<div style="margin: 150px; text-align: center; font-size: 14px"><p>' . $_LANG['shop_closed'] . '</p><p>' . $_CFG['close_comment'] . '</p></div>');
}

if (is_spider()) {
	if (!defined('INIT_NO_USERS')) {
		define('INIT_NO_USERS', true);

		if ($_CFG['integrate_code'] == 'ucenter') {
			$user = &init_users();
		}
	}

	$_SESSION = array();
	$_SESSION['user_id'] = 0;
	$_SESSION['user_name'] = '';
	$_SESSION['email'] = '';
	$_SESSION['user_rank'] = 0;
	$_SESSION['discount'] = 1;
}

if (!defined('INIT_NO_USERS')) {
	include ROOT_PATH . 'includes/cls_session.php';
	$sess = new cls_session($db, $ecs->table('sessions'), $ecs->table('sessions_data'));
	define('SESS_ID', $sess->get_session_id());
}

if (isset($_SERVER['PHP_SELF'])) {
	$_SERVER['PHP_SELF'] = htmlspecialchars($_SERVER['PHP_SELF']);
}

if (!defined('INIT_NO_SMARTY')) {
	header('Cache-control: private');
	header('Content-type: text/html; charset=' . EC_CHARSET);
	require ROOT_PATH . 'includes/cls_template.php';
	$smarty = new cls_template();
	$smarty->cache_lifetime = $_CFG['cache_time'];
	$smarty->template_dir = ROOT_PATH . 'themes/' . $_CFG['template'];
	$smarty->cache_dir = ROOT_PATH . 'temp/caches';
	$smarty->compile_dir = ROOT_PATH . 'temp/compiled';

	if ((DEBUG_MODE & 2) == 2) {
		$smarty->direct_output = true;
		$smarty->force_compile = true;
	}
	else {
		$smarty->direct_output = false;
		$smarty->force_compile = false;
	}

	$smarty->assign('lang', $_LANG);
	$smarty->assign('ecs_charset', EC_CHARSET);

	if (!empty($_CFG['stylename'])) {
		$smarty->assign('ecs_css_path', 'themes/' . $_CFG['template'] . '/style_' . $_CFG['stylename'] . '.css');
	}
	else {
		$smarty->assign('ecs_css_path', 'themes/' . $_CFG['template'] . '/style.css');
	}

	$smarty->assign('ecs_css_suggest', 'themes/' . $_CFG['template'] . '/suggest.css');
}

if (!defined('INIT_NO_USERS')) {
	$user = &init_users();

	if (!isset($_SESSION['user_id'])) {
		$site_name = (isset($_GET['from']) ? htmlspecialchars($_GET['from']) : addslashes($_LANG['self_site']));
		$from_ad = (!empty($_GET['ad_id']) ? intval($_GET['ad_id']) : 0);
		$_SESSION['from_ad'] = $from_ad;
		$_SESSION['referer'] = stripslashes($site_name);
		unset($site_name);

		if (!defined('INGORE_VISIT_STATS')) {
			visit_stats();
		}
	}

	if (empty($_SESSION['user_id'])) {
		if ($user->get_cookie()) {
			if (0 < $_SESSION['user_id']) {
				update_user_info();
			}
		}
		else {
			$_SESSION['user_id'] = 0;
			$_SESSION['user_name'] = '';
			$_SESSION['email'] = '';
			$_SESSION['user_rank'] = 0;
			$_SESSION['discount'] = 1;

			if (!isset($_SESSION['login_fail'])) {
				$_SESSION['login_fail'] = 0;
			}
		}
	}

	if (isset($_GET['u'])) {
		set_affiliate();
	}

	if (!empty($_COOKIE['ECS']['user_id']) && !empty($_COOKIE['ECS']['password'])) {
		$sql = 'SELECT user_id, user_name, password ' . ' FROM ' . $ecs->table('users') . ' WHERE user_id = \'' . intval($_COOKIE['ECS']['user_id']) . '\' AND password = \'' . $_COOKIE['ECS']['password'] . '\'';
		$row = $db->GetRow($sql);

		if (!$row) {
			$time = time() - 3600;
			setcookie('ECS[user_id]', '', $time, '/');
			setcookie('ECS[password]', '', $time, '/');
		}
		else {
			$_SESSION['user_id'] = $row['user_id'];
			$_SESSION['user_name'] = $row['user_name'];
			update_user_info();
		}
	}

	if (isset($smarty)) {
		$smarty->assign('ecs_session', $_SESSION);
	}
}

if ((DEBUG_MODE & 1) == 1) {
	error_reporting(32767);
}
else {
	error_reporting(32767 ^ (8 | 2));
}

if ((DEBUG_MODE & 4) == 4) {
	include ROOT_PATH . 'includes/lib.debug.php';
}

if (!defined('INIT_NO_SMARTY') && gzip_enabled()) {
	ob_start('ob_gzhandler');
}
else {
	ob_start();
}

?>
