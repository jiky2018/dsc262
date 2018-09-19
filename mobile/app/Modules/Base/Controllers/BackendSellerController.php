<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Base\Controllers;

abstract class BackendSellerController extends FoundationController
{
	public function __construct()
	{
		parent::__construct();
		$helper_list = array('time', 'base', 'common', 'main', 'insert', 'goods', 'wechat');
		$this->load_helper($helper_list);
		$this->ecs = $GLOBALS['ecs'] = new \App\Libraries\Shop(C('DB_NAME'), C('DB_PREFIX'));
		$this->db = $GLOBALS['db'] = new \App\Libraries\Mysql();

		if (!defined('INIT_NO_USERS')) {
			session(array('name' => 'ECSCP_SELLER_ID'));
			session('[start]');
			$condition['sesskey'] = substr(cookie('ECSCP_SELLER_ID'), 0, 32);
			$session_seller = $this->model->table('sessions_data')->where($condition)->find();
			$_SESSION = unserialize($session_seller['data']);
			define('SESS_ID', substr($session_seller['sesskey'], 0, 32));
		}

		$GLOBALS['_CFG'] = load_ecsconfig();
		$GLOBALS['_CFG']['template'] = 'default';
		C('shop', $GLOBALS['_CFG']);
		$this->checkSellerLogin();
		L(require LANG_PATH . C('shop.lang') . '/common.php');
		L('copyright', sprintf(L('copyright'), date('Y')));
	}

	public function message($msg, $url = NULL, $type = '1', $seller = false, $waitSecond = 3)
	{
		if ($url == null) {
			$url = 'javascript:history.back();';
		}

		if ($type == '2') {
			$title = L('error_information');
		}
		else {
			$title = L('prompt_information');
		}

		$data['title'] = $title;
		$data['message'] = $msg;
		$data['type'] = $type;
		$data['url'] = $url;
		$data['second'] = $waitSecond;
		$this->assign('data', $data);
		$tpl = ($seller == true ? 'admin/seller_message' : 'admin/message');
		$this->display($tpl);
		exit();
	}

	private function checkSellerLogin()
	{
		$condition['user_id'] = isset($_SESSION['seller_id']) ? intval($_SESSION['seller_id']) : 0;
		$action_list = $this->model->table('admin_user')->where($condition)->getField('action_list');

		if (empty($action_list)) {
			redirect('../' . SELLER_PATH . '/privilege.php?act=login');
		}
	}

	public function seller_admin_priv($priv_str)
	{
		$condition['user_id'] = isset($_SESSION['seller_id']) ? intval($_SESSION['seller_id']) : 0;
		$action_list = $this->model->table('admin_user')->where($condition)->getField('action_list');

		if ($action_list == 'all') {
			return true;
		}

		if (strpos(',' . $action_list . ',', ',' . $priv_str . ',') === false) {
			$this->message(L('priv_error'), null, 2, true);
			return false;
		}
		else {
			return true;
		}
	}
}

?>
