<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Bonus\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	public function __construct()
	{
		parent::__construct();
		$files = array('clips', 'transaction', 'main');
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		L(require LANG_PATH . C('shop.lang') . '/flow.php');
		$this->assign('lang', array_change_key_case(L()));
		$this->load_helper($files);
	}

	public function actionIndex()
	{
		$size = 5;
		$page = I('page', 1, 'intval');
		$status = 4;

		if (IS_AJAX) {
			$bonus_list = get_bonus_list($size, $page, $status);
			exit(json_encode(array('bonus_list' => $bonus_list, 'totalPage' => $bonus_list['totalpage'])));
		}

		$this->assign('status', $status);
		$this->assign('page_title', '红包列表');
		$this->display();
	}

	public function actionGetBonus()
	{
		$type_id = I('bonus_id', '', 'intval');

		if (IS_AJAX) {
			if (empty($_SESSION['user_id'])) {
				exit(json_encode(array('msg' => '请登录', 'error' => '1')));
			}

			$sql = ' SELECT bonus_id FROM ' . $GLOBALS['ecs']->table('user_bonus') . (' WHERE bonus_type_id = \'' . $type_id . '\' AND user_id = \'' . $_SESSION['user_id'] . '\' LIMIT 1 ');
			$exist = $GLOBALS['db']->getOne($sql);

			if (!empty($exist)) {
				exit(json_encode(array('msg' => L('already_got'), 'error' => '2')));
			}
			else {
				$sql = ' SELECT bonus_id FROM {pre}user_bonus WHERE bonus_type_id = \'' . $type_id . '\' AND user_id = 0 LIMIT 1 ';
				$bonus_id = $this->db->getOne($sql);

				if (empty($bonus_id)) {
					exit(json_encode(array('msg' => L('no_bonus'), 'error' => '2')));
				}
				else {
					$data = array('user_id' => $_SESSION['user_id'], 'bind_time' => gmtime());
					$this->db->autoExecute($GLOBALS['ecs']->table('user_bonus'), $data, 'UPDATE', 'bonus_id   = \'' . $bonus_id . '\'');
					exit(json_encode(array('msg' => L('get_success'), 'error' => '2')));
				}
			}
		}
	}
}

?>
