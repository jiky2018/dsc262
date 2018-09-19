<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Coupont\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	protected $size = 10;

	public function __construct()
	{
		parent::__construct();
		$this->assign('lang', array_change_key_case(L()));
		$files = array('clips', 'transaction', 'main');
		$this->load_helper($files);
	}

	public function actionIndex()
	{
		$page = I('page', 1, 'intval');
		$status = I('status', 0, 'intval');

		if (IS_AJAX) {
			$coupons_list = get_coupons_list($this->size, $page, $status);
			exit(json_encode(array('coupons_list' => $coupons_list, 'totalPage' => $coupons_list['totalpage'])));
		}

		$this->assign('status', $status);
		$this->assign('page_title', '好券集市');
		$this->display();
	}

	public function actionCouponsGoods()
	{
		$page = I('page', 1, 'intval');

		if (IS_AJAX) {
			$coupons_list = get_coupons_goods_list($this->size, $page);
			exit(json_encode(array('coupons_list' => $coupons_list, 'totalPage' => $coupons_list['totalpage'])));
		}

		$this->assign('page_title', '任务集市');
		$this->display();
	}

	public function actiongetCoupon()
	{
		$cou_id = I('cou_id', '', 'intval');
		$uid = $_SESSION['user_id'];
		$ticket = 1;
		$time = gmtime();

		if (IS_AJAX) {
			if (empty($_SESSION['user_id'])) {
				exit(json_encode(array('msg' => '请登录', 'error' => '1')));
			}

			$rank = $_SESSION['user_rank'];
			$sql_cou = 'select cou_type,cou_ok_user from {pre}coupons where cou_id = \'' . $cou_id . '\'';
			$rest = $this->db->getRow($sql_cou);
			$type = $rest['cou_type'];
			$cou_rank = $rest['cou_ok_user'];
			$ranks = explode(',', $cou_rank);
			if ($type == 2 || $type == 4 && $ranks != 0) {
				if (in_array($rank, $ranks)) {
					$this->getCoups($cou_id, $uid, $ticket);
				}
				else {
					exit(json_encode(array('msg' => '非预定会员不可领取', 'error' => 5)));
				}
			}
			else {
				$this->getCoups($cou_id, $uid, $ticket);
			}
		}
	}

	protected function getCoups($cou_id, $uid, $ticket)
	{
		$time = gmtime();
		$sql = 'SELECT c.*,c.cou_total-COUNT(cu.cou_id) cou_surplus FROM {pre}coupons c LEFT JOIN {pre}coupons_user cu ON c.cou_id=cu.cou_id GROUP BY c.cou_id  HAVING cou_surplus>0 AND c.review_status = 3 AND c.cou_id=\'' . $cou_id . ('\' AND c.cou_end_time>' . $time . ' limit 1');
		$total = $this->db->getRow($sql);

		if (!empty($total)) {
			$sql = 'select count(cou_id) as num from {pre}coupons_user where user_id = \'' . $uid . '\' and  cou_id = \'' . $cou_id . '\'';
			$num = $this->db->getOne($sql);
			$sql = 'select cou_user_num, cou_money from {pre}coupons where cou_id = \'' . $cou_id . '\' LIMIT 1';
			$res = $this->db->getRow($sql);
			if ($res && $num < $res['cou_user_num']) {
				$sql3 = 'INSERT INTO {pre}coupons_user (`user_id`,`cou_id`, `cou_money`,`uc_sn`) VALUES (' . $uid . ', ' . $cou_id . ', \'' . $res['cou_money'] . ('\', ' . $time . ' ) ');

				if ($GLOBALS['db']->query($sql3)) {
					exit(json_encode(array('msg' => '领取成功！感谢您的参与，祝您购物愉快', 'error' => 2)));
				}
			}
			else {
				exit(json_encode(array('msg' => '领取失败,您已经领取过该券了!每人限领取' . $res['cou_user_num'] . '张', 'error' => 3)));
			}
		}
		else {
			exit(json_encode(array('msg' => '优惠券已领完', 'error' => 4)));
		}
	}

	private function check_login()
	{
		$without = array('AddPackageToCart');
		if (!$_SESSION['user_id'] && !in_array(ACTION_NAME, $without)) {
			ecs_header('Location: ' . url('user/login/index'));
		}
	}
}

?>
