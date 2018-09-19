<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
namespace App\Modules\Merchants\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	public $user_id;

	public function __construct()
	{
		parent::__construct();
		$this->user_id = $_SESSION['user_id'];
		$this->actionchecklogin();
		L(require LANG_PATH . C('shop.lang') . '/merchants.php');
		$files = array('clips', 'transaction', 'main');
		$this->load_helper($files);
		$this->sid = 1;
	}

	public function actionIndex()
	{
		$shop = $this->model->table('merchants_shop_information')->where(array('user_id' => $this->user_id))->find();

		if ($shop) {
			ecs_header('Location: ' . url('merchants/index/audit'));
		}

		if (IS_POST) {
			if (I('agree') == 1) {
				$data['agreement'] = I('agree');
			}
			else {
				show_message('请同意用户协议', '', '', 'error');
			}

			$data['contactName'] = I('contactName');
			$data['contactPhone'] = I('contactPhone');
			$data['license_adress'] = I('license_adress');
			$data['company_located'] = I('province_region_id') . ',' . I('city_region_id') . ',' . I('district_region_id');

			if ($data['contactPhone']) {
				$preg = is_mobile($data['contactPhone']);

				if ($preg === false) {
					show_message(L('mobile_not_null'));
				}
			}

			if (empty($data['contactName'])) {
				show_message(L('msg_shop_owner_notnull'));
			}

			$data['user_id'] = $this->user_id;

			if ($this->model->table('merchants_steps_fields')->data($data)->add()) {
				ecs_header('Location: ' . url('merchants/index/shop'));
			}
			else {
				show_message(L('add_error'));
			}
		}

		$this->assign('page_title', L('business_information'));
		$this->display();
	}

	public function actionShop()
	{
		if (IS_POST) {
			$data = I('');

			if (empty($data['rz_shopName'])) {
				show_message(L('msg_shop_name_notnull'));
			}

			if (empty($data['hopeLoginName'])) {
				show_message(L('msg_login_shop_name_notnull'));
			}

			$data['user_id'] = $this->user_id;

			if ($this->model->table('merchants_shop_information')->data($data)->add()) {
				ecs_header('Location: ' . url('merchants/index/audit'));
			}
			else {
				show_message(L('add_error'));
			}
		}

		$parent_id = 0;
		$sql = 'select cat_id, cat_name from {pre}category where parent_id = \'' . $parent_id . '\'';
		$category = $this->db->getAll($sql);
		$this->assign('category', $category);
		$this->assign('page_title', L('store_information'));
		$this->display();
	}

	public function actionAudit()
	{
		$shop = $this->model->table('merchants_shop_information')->field('merchants_audit,merchants_message')->where(array('user_id' => $this->user_id))->find();
		$this->assign('shop', $shop);
		$this->assign('img', elixir('img/shenqing-loding.gif'));
		$this->assign('page_title', L('review_the_status'));
		$this->display();
	}

	public function actionGuide()
	{
		$sql = 'select process_title, process_article from {pre}merchants_steps_process where process_steps = \'' . $this->sid . '\'';
		$row = $this->db->getRow($sql);

		if (0 < $row['process_article']) {
			$row['article_centent'] = $this->db->getOne('select content from {pre}article where article_id = \'' . $row['process_article'] . '\'');
		}

		$this->assign('row', $row);
		$this->assign('page_title', L('instructions'));
		$this->display();
	}

	public function actionchecklogin()
	{
		if (!$this->user_id) {
			$url = urlencode(__HOST__ . $_SERVER['REQUEST_URI']);

			if (IS_POST) {
				$url = urlencode($_SERVER['HTTP_REFERER']);
			}

			ecs_header('Location: ' . U('user/login/index', array('back_act' => $url)));
			exit();
		}
	}
}

?>
