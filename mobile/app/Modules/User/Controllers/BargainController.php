<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\User\Controllers;

class BargainController extends \App\Modules\Base\Controllers\FrontendController
{
	public function __construct()
	{
		parent::__construct();
		$this->user_id = $_SESSION['user_id'];
		$this->actionchecklogin();
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		L(require LANG_PATH . C('shop.lang') . '/flow.php');
		$files = array('clips', 'transaction', 'main');
		$this->load_helper($files);
	}

	public function actionIndex()
	{
		if (IS_AJAX) {
			$size = 10;
			$page = I('page', 1, 'intval');
			$bargain_buy = bargain_buy_list($this->user_id, $size, $page);
			exit(json_encode(array('list' => $bargain_buy['list'], 'totalPage' => $bargain_buy['totalpage'])));
		}

		$this->assign('page_title', '我的砍价活动');
		$this->display();
	}

	public function actionchecklogin()
	{
		if (!$this->user_id) {
			$url = urlencode(__HOST__ . $_SERVER['REQUEST_URI']);

			if (IS_POST) {
				$url = urlencode($_SERVER['HTTP_REFERER']);
			}

			ecs_header('Location: ' . url('user/login/index', array('back_act' => $url)));
			exit();
		}
	}
}

?>
