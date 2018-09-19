<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Base\Controllers;

abstract class FrontendController extends FoundationController
{
	public $province_id = 0;
	public $city_id = 0;
	public $district_id = 0;
	public $caching = false;
	public $custom = '';
	public $customs = '';

	public function __construct()
	{
		parent::__construct();
		$this->start();
		$this->ecjia_login();
	}

	private function ecjia_login()
	{
		if (isset($_GET['origin']) && $_GET['origin'] == 'app') {
			$openid = I('get.openid');
			$token = I('get.token');
			$sql = 'select cu.access_token,u.user_name from {pre}connect_user as cu LEFT JOIN {pre}users as u on cu.user_id = u.user_id where open_id = \'' . $openid . '\' ';
			$user = $this->db->getRow($sql);

			if ($token == $user['access_token']) {
				$GLOBALS['user']->set_session($user['user_name']);
				$GLOBALS['user']->set_cookie($user['user_name']);
				update_user_info();
				recalculate_price();
			}
		}
	}

	private function start()
	{
		$this->init();
		$this->init_user();
		$this->init_gzip();
		$this->init_assign();
		$this->init_area();
		$this->init_wechat();
		\Think\Hook::listen('frontend_init');
		$this->assign('lang', array_change_key_case(L()));
		$this->assign('charset', CHARSET);
	}

	private function init()
	{
		$helper_list = array('time', 'base', 'common', 'main', 'insert', 'goods', 'wechat');
		$this->load_helper($helper_list);
		$this->ecs = $GLOBALS['ecs'] = new \App\Libraries\Shop(C('DB_NAME'), C('DB_PREFIX'));
		$this->db = $GLOBALS['db'] = new \App\Libraries\Mysql();
		$this->err = $GLOBALS['err'] = new \App\Libraries\Error('message');
		$GLOBALS['_CFG'] = load_ecsconfig();
		$GLOBALS['_CFG']['template'] = 'default';

		if (0 < $GLOBALS['_CFG']['rewrite']) {
			C('URL_MODEL', 2);
		}

		$GLOBALS['_CFG']['rewrite'] = 0;
		C('shop', $GLOBALS['_CFG']);
		$app_config = MODULE_BASE_PATH . 'config/web.php';
		C('app', file_exists($app_config) ? require $app_config : array());
		L(require LANG_PATH . C('shop.lang') . '/common.php');
		$app_lang = MODULE_BASE_PATH . 'Language/' . C('shop.lang') . '/' . strtolower(MODULE_NAME) . '.php';
		L(file_exists($app_lang) ? require $app_lang : array());
		$app_lang = MODULE_BASE_PATH . 'Language/' . C('shop.lang') . '/' . strtolower(CONTROLLER_NAME) . '.php';
		L(file_exists($app_lang) ? require $app_lang : array());
		$this->load_helper('function', 'app');

		if (C('shop.shop_closed') == 1) {
			exit('<p>' . L('shop_closed') . '</p><p>' . C('close_comment') . '</p>');
		}

		if (C('shop.wap_config') == 0) {
			exit('<p>' . L('wap_config') . '</p><p>' . C('close_comment') . '</p>');
		}

		if (!defined('INIT_NO_USERS')) {
			session(array('name' => 'ECS_ID'));
			session('[start]');
			define('SESS_ID', session_id());
		}

		$helper_list = array('ecmoban', 'function');
		$this->load_helper($helper_list);
	}

	private function init_user()
	{
		if (!defined('INIT_NO_USERS')) {
			$GLOBALS['user'] = $this->users = init_users();

			if (!isset($_SESSION['user_id'])) {
				$site_name = isset($_GET['from']) ? htmlspecialchars($_GET['from']) : addslashes(L('self_site'));
				$from_ad = !empty($_GET['ad_id']) ? intval($_GET['ad_id']) : 0;
				$wechat_from = array('timeline', 'groupmessage', 'singlemessage');

				if (in_array($site_name, $wechat_from)) {
					$site_name = addslashes(L('self_site'));
				}

				$_SESSION['from_ad'] = $from_ad;
				$_SESSION['referer'] = stripslashes($site_name);
				unset($site_name);

				if (!defined('INGORE_VISIT_STATS')) {
					visit_stats();
				}
			}

			if (empty($_SESSION['user_id'])) {
				if ($this->users->get_cookie()) {
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

			if (isset($_GET['d'])) {
				set_drp_affiliate();
			}

			$wechat_ru_id = isset($_GET['wechat_ru_id']) ? intval($_GET['wechat_ru_id']) : 0;
			set_ru_id($wechat_ru_id);
			if (!empty($_COOKIE['ECS']['user_id']) && !empty($_COOKIE['ECS']['password'])) {
				$condition = array('user_id' => intval($_COOKIE['ECS']['user_id']), 'password' => $_COOKIE['ECS']['password']);
				$row = $this->db->table('users')->where($condition)->find();

				if (!$row) {
					$time = time() - 3600;
					cookie('ECS[user_id]', '');
					cookie('ECS[password]', '');
				}
				else {
					$_SESSION['user_id'] = $row['user_id'];
					$_SESSION['user_name'] = $row['user_name'];
					update_user_info();
				}
			}

			if (isset($this->tpl)) {
				$this->tpl->assign('ecs_session', $_SESSION);
			}
		}
	}

	private function init_assign()
	{
		$search_keywords = C('shop.search_keywords');
		$hot_keywords = array();

		if ($search_keywords) {
			$hot_keywords = explode(',', $search_keywords);
		}

		$this->assign('hot_keywords', $hot_keywords);
		$history = '';

		if (!empty($_COOKIE['ECS']['keywords'])) {
			$history = explode(',', $_COOKIE['ECS']['keywords']);
			$history = array_unique($history);
		}

		$this->assign('history_keywords', $history);
	}

	public function init_wechat()
	{
		$wechat_ru_id = get_ru_id();

		if (0 < $wechat_ru_id) {
			$wechat = '\\App\\Modules\\Wechat\\Controllers\\IndexController';
			$wechat::snsapi_base($wechat_ru_id);
		}

		$this->init_oauth();
		$is_wechat = is_wechat_browser() && is_dir(APP_WECHAT_PATH) ? 1 : 0;
		$this->assign('is_wechat', $is_wechat);
		$share_data = $this->get_wechat_share_content();
		$this->assign('share_data', $share_data);
	}

	public function init_area()
	{
		$city_district_list = get_isHas_area($_COOKIE['type_city']);

		if (!$city_district_list) {
			cookie('type_district', 0);
			$_COOKIE['type_district'] = 0;
		}

		$provinceT_list = get_isHas_area($_COOKIE['type_province']);
		$cityT_list = get_isHas_area($_COOKIE['type_city'], 1);
		$districtT_list = get_isHas_area($_COOKIE['type_district'], 1);
		if (0 < $_COOKIE['type_province'] && $provinceT_list) {
			if ($city_district_list) {
				if ($cityT_list['parent_id'] == $_COOKIE['type_province'] && $_COOKIE['type_city'] == $districtT_list['parent_id']) {
					$_COOKIE['province'] = $_COOKIE['type_province'];

					if (0 < $_COOKIE['type_city']) {
						$_COOKIE['city'] = $_COOKIE['type_city'];
					}

					if (0 < $_COOKIE['type_district']) {
						$_COOKIE['district'] = $_COOKIE['type_district'];
					}
				}
			}
			else if ($cityT_list['parent_id'] == $_COOKIE['type_province']) {
				$_COOKIE['province'] = $_COOKIE['type_province'];

				if (0 < $_COOKIE['type_city']) {
					$_COOKIE['city'] = $_COOKIE['type_city'];
				}

				if (0 < $_COOKIE['type_district']) {
					$_COOKIE['district'] = $_COOKIE['type_district'];
				}
			}
		}

		$this->province_id = isset($_COOKIE['province']) ? $_COOKIE['province'] : 0;
		$this->city_id = isset($_COOKIE['city']) ? $_COOKIE['city'] : 0;
		$this->district_id = isset($_COOKIE['district']) ? $_COOKIE['district'] : 0;
		$warehouse_date = array('region_id', 'region_name');
		$warehouse_where = 'regionId = \'' . $this->province_id . '\'';
		$warehouse_province = get_table_date('region_warehouse', $warehouse_where, $warehouse_date);
		$sellerInfo = get_seller_info_area();

		if (!$warehouse_province) {
			$this->province_id = $sellerInfo['province'];
			$this->city_id = $sellerInfo['city'];
			$this->district_id = $sellerInfo['district'];
		}

		cookie('province', $this->province_id);
		cookie('city', $this->city_id);
		cookie('district', $this->district_id);
	}

	private function init_gzip()
	{
		if (!defined('INIT_NO_SMARTY') && gzip_enabled()) {
			ob_start('ob_gzhandler');
		}
		else {
			ob_start();
		}
	}

	protected function init_oauth()
	{
		$module_name = strtolower(MODULE_NAME);
		if (is_wechat_browser() && empty($_SESSION['unionid']) && $module_name != 'oauth') {
			$sql = 'SELECT `auth_config` FROM' . $GLOBALS['ecs']->table('touch_auth') . ' WHERE `type` = \'wechat\' AND `status` = 1';
			$auth_config = $GLOBALS['db']->getOne($sql);

			if ($auth_config) {
				$res = unserialize($auth_config);
				$config = array();

				foreach ($res as $key => $value) {
					$config[$value['name']] = $value['value'];
				}

				$HTTP_REFERER = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
				if (isset($HTTP_REFERER) && !empty($HTTP_REFERER) && strpos($HTTP_REFERER, 'mp.weixinbridge.com/mp/wapredirect?url') !== false) {
					$param = get_url_query($HTTP_REFERER);
				}

				$HTTP_REFERER = isset($param) && !empty($param['url']) ? $param['url'] : $HTTP_REFERER;
				$back_url = isset($HTTP_REFERER) && !empty($HTTP_REFERER) ? $HTTP_REFERER : __HOST__ . $_SERVER['REQUEST_URI'];
				$this->redirect('oauth/index/index', array('type' => 'wechat', 'back_url' => urlencode(html_in($back_url))));
			}
		}
	}

	public function get_current_url()
	{
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
		$u = I('get.u', 0, 'intval');
		if (!empty($u) && !empty($_SESSION['user_id']) && $u != $_SESSION['user_id']) {
			$uri = url_set_value($uri, 'u', $_SESSION['user_id']);
		}

		$d = I('get.d', 0, 'intval');
		if (!empty($d) && !empty($_SESSION['user_id']) && $d != $_SESSION['user_id']) {
			$uri = url_set_value($uri, 'd', $_SESSION['user_id']);
		}

		return __HOST__ . $uri;
	}

	public function get_wechat_share_content($share_data = array())
	{
		if (!empty($share_data['img'])) {
			$share_img = strtolower(substr($share_data['img'], 0, 4)) == 'http' ? $share_data['img'] : __HOST__ . $share_data['img'];
		}
		else {
			$share_img = elixir('img/wxsdk.png', true);
		}

		$module_title = strtolower(MODULE_NAME) . '_sdk_title';
		$module_title = L($module_title) ? C('shop.shop_name') . '_' . L($module_title) : C('shop.shop_name');
		$data = array('title' => !empty($share_data['title']) ? $share_data['title'] : $module_title, 'desc' => !empty($share_data['desc']) ? str_replace(array(' ', '　', '	', "\n", "\r"), '', html_in($share_data['desc'])) : C('shop.shop_desc'), 'link' => !empty($share_data['link']) ? $share_data['link'] : $this->get_current_url(), 'img' => $share_img);
		return $data;
	}
}

?>
