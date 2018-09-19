<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Drp\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	private $user_id = 0;

	public function __construct()
	{
		parent::__construct();
		$this->checkLogin();
		$files = array('order', 'clips', 'payment', 'transaction');
		$this->load_helper($files);
		$this->assign('custom', C(custom));
	}

	public function actionIndex()
	{
		$sql = 'SELECT id,shop_name FROM {pre}drp_shop WHERE user_id=' . $_SESSION['user_id'];
		$drp = $this->db->getRow($sql);
		$drp_id = $drp['id'];
		$shop_name = $drp['shop_name'];
		$isbuy = $drp['isbuy'];
		$sql = 'SELECT value FROM {pre}drp_config WHERE code=\'isbuy\'';
		$code = $this->db->getOne($sql);

		if ($code == 1) {
			$sql = 'SELECT value FROM {pre}drp_config WHERE code=\'buy_money\'';
			$value = $this->db->getOne($sql);
		}

		if (empty($drp_id) && empty($code)) {
			ecs_header('Location: ' . url('drp/index/register'));
		}

		if (empty($drp_id) && ($code != '') && (0 < $value)) {
			ecs_header('Location: ' . url('drp/index/purchase'));
		}

		if (empty($drp_id) && ($code != '') && empty($value)) {
			ecs_header('Location: ' . url('drp/index/register'));
		}

		if (!empty($drp_id)) {
			ecs_header('Location: ' . url('drp/user/index'));
		}

		if (!empty($drp_id) && empty($shop_name)) {
			ecs_header('Location: ' . url('drp/user/shopconfig'));
		}
	}

	public function actionRegister()
	{
		$buy_money = dao('drp_config')->field('value')->where(array('code' => 'is_buy_money'))->find();

		if ($buy_money['value'] == 1) {
			$buy = dao('drp_config')->field('value')->where(array('code' => 'buy'))->find();
			$sql = 'select sum(goods_amount) as money from {pre}order_info where pay_status= 2 and user_id = ' . $_SESSION['user_id'];
			$money = $this->model->getOne($sql);

			if ($money < $buy['value']) {
				show_message('您的累计消费金额未达到开店要求，再接再厉', '返回商城', url('user/index/index'), 'warning');
			}
		}

		$sql = 'SELECT id FROM {pre}drp_shop WHERE user_id=' . $_SESSION['user_id'];
		$drp_id = $this->db->getRow($sql);

		if (empty($drp_id)) {
			if (IS_POST) {
				$data['shop_name'] = I('shop_name');
				$data['real_name'] = I('real_name');
				$data['mobile'] = I('mobile');
				$data['qq'] = I('qq');

				if (empty($data['mobile'])) {
					show_message(L('mobile_notnull'));
				}

				if (is_mobile($data['mobile']) == false) {
					show_message(L('msg_mobile_format_error'));
				}

				if (empty($data['shop_name'])) {
					show_message(L('msg_shop_name_notnull'));
				}

				if (empty($data['real_name'])) {
					show_message(L('msg_name_notnull'));
				}

				if (empty($data['mobile'])) {
					show_message(L('msg_contact_way_notnull'));
				}

				$data['create_time'] = gmtime();
				$sql = 'SELECT value FROM {pre}drp_config WHERE code=\'ischeck\'';
				$ischeck = $this->db->getOne($sql);

				if ($ischeck == 1) {
					$data['audit'] = 0;
					$data['status'] = 1;
				}

				if ($ischeck == 0) {
					$data['audit'] = 1;
					$data['status'] = 1;
				}

				$data['type'] = 0;
				$data['user_id'] = $_SESSION['user_id'];

				if ($this->model->table('drp_shop')->data($data)->add()) {
					ecs_header('Location: ' . url('drp/index/finish'));
				}
				else {
					show_message(L('add_error'));
				}
			}
		}
		else {
			ecs_header('Location: ' . url('drp/user/index'));
		}

		$sql = 'SELECT value FROM {pre}drp_config WHERE code=\'notice\'';
		$notic = $this->db->getOne($sql);
		$notice = $this->htmlout($notic);
		$this->assign('notice', nl2br($notice));
		$this->assign('page_title', L('open_shop_process'));
		$this->display();
	}

	public function actionCategory()
	{
		$this->checkShopName();

		if (IS_AJAX) {
			$page = I('page', '1', 'intval');
			$offset = 20;
			$sql = 'SELECT count(goods_id) as max FROM {pre}goods WHERE dis_commission>0 and is_distribution=1 ';
			$count = $this->db->getOne($sql);
			$page_size = ceil($count / $offset);
			$limit = ' LIMIT ' . (($page - 1) * $offset) . ',' . $offset;
			$collection_goods = $this->get_drp_goods($count, $limit);
			$show = (0 < $count ? 1 : 0);
			exit(json_encode(array('goods_list' => $collection_goods['goods_list'], 'totalPage' => $page_size)));
		}

		if (IS_POST) {
			$cateArr = I('post.cate');
			$cat_id = '';

			if (empty($cateArr)) {
				show_message(L('category_not_null'));
			}

			$data['cat_id'] = $cateArr;
			$where['user_id'] = $_SESSION['user_id'];
			$this->model->table('drp_shop')->data($data)->where($where)->save();
			redirect(url('drp/index/finish'));
		}

		$sql = 'SELECT value FROM {pre}drp_config WHERE code=\'notice\'';
		$notic = $this->db->getOne($sql);
		$notice = $this->htmlout($notic);
		$this->assign('notice', nl2br($notice));
		$this->assign('page_title', L('open_shop_process'));
		$this->display();
	}

	public function get_drp_goods($record_count, $limit)
	{
		$sql = 'select * from {pre}goods where dis_commission>0 and is_distribution=1 ORDER BY goods_id desc ' . $limit;
		$res = $GLOBALS['db']->getAll($sql);
		$goods_list = array();

		foreach ($res as $key => $row) {
			$goods_list[$key]['goods_id'] = $row['goods_id'];
			$goods_list[$key]['goods_name'] = $row['goods_name'];
			$goods_list[$key]['goods_thumb'] = get_image_path($row['goods_thumb']);
			$goods_list[$key]['shop_price'] = price_format($row['shop_price']);
		}

		$arr = array('goods_list' => $goods_list, 'record_count' => $record_count, 'paper' => $paper, 'size' => $size);
		return $arr;
	}

	public function actionFinish()
	{
		$shop = $this->model->table('drp_shop')->field('shop_name, mobile, create_time')->where(array('user_id' => $_SESSION['user_id']))->find();

		if (is_dir(APP_WECHAT_PATH)) {
			$user_id = $_SESSION['user_id'];
			$where = array('user_id' => $user_id, 'is_buy' => 1);
			$shop = dao('drp_shop')->field('shop_name, mobile, create_time')->where($where)->find();

			if (!empty($shop)) {
				$pushData = array(
					'keyword1' => array('value' => $shop['shop_name'], 'color' => '#173177'),
					'keyword2' => array('value' => $shop['mobile'], 'color' => '#173177'),
					'keyword3' => array('value' => date('Y-m-d', $shop['create_time']), 'color' => '#173177')
					);
				$url = __HOST__ . url('drp/index/register');
				push_template('OPENTM207126233', $pushData, $url, $user_id);
			}
		}

		$sql = 'SELECT value FROM {pre}drp_config WHERE code=\'novice\'';
		$novice = $this->db->getOne($sql);
		$novice = $this->htmlout($novice);
		$this->assign('novice', nl2br($novice));
		$this->assign('shop', $shop);
		$this->assign('page_title', L('set_up_shop'));
		$this->display();
	}

	public function actionPurchase()
	{
		$sql = 'SELECT value FROM {pre}drp_config WHERE code=\'isbuy\'';
		$code = $this->db->getOne($sql);

		if ($code != 1) {
			ecs_header('Location: ' . url('drp/index/index'));
		}

		$sql = 'SELECT value FROM {pre}drp_config WHERE `code`=\'buy_money\'';
		$price = $this->db->getOne($sql);
		$this->assign('price', price_format($price));
		$sql = 'SELECT value FROM {pre}drp_config WHERE `code`=\'novice\'';
		$novice = $this->db->getOne($sql);
		$novice = $this->htmlout($novice);
		$this->assign('novice', nl2br($novice));
		$this->assign('page_title', L('distribution_application'));
		$this->display();
	}

	public function actionPurchasePay()
	{
		$sql = 'SELECT value FROM {pre}drp_config WHERE `code`=\'buy_money\'';
		$price = $this->db->getOne($sql);
		$this->assign('price', price_format($price));
		$payment_list = get_online_payment_list(false);

		if (isset($payment_list)) {
			foreach ($payment_list as $key => $payment) {
				if (substr($payment['pay_code'], 0, 4) == 'pay_') {
					unset($payment_list[$key]);
					continue;
				}

				if ($payment['is_cod'] == '1') {
					$payment_list[$key]['format_pay_fee'] = '<span id="ECS_CODFEE">' . $payment['format_pay_fee'] . '</span>';
				}

				if (($payment['pay_code'] == 'yeepayszx') && (300 < $total['amount'])) {
					unset($payment_list[$key]);
				}

				if ($payment['pay_code'] == 'balance') {
					if ($_SESSION['user_id'] == 0) {
						unset($payment_list[$key]);
					}
					else if ($_SESSION['flow_order']['pay_id'] == $payment['pay_id']) {
						$this->assign('disable_surplus', 1);
					}
				}

				if (!file_exists(ADDONS_PATH . 'payment/' . $payment['pay_code'] . '.php')) {
					unset($payment_list[$key]);
				}
			}
		}

		if (IS_AJAX) {
			$pay_id = I('pay_id');
			$payment = payment_info($pay_id);
			$order = array();
			$order['order_sn'] = $_SESSION['user_id'];
			$order['user_name'] = $_SESSION['user_name'];
			$payment['pay_fee'] = pay_fee($pay_id, $price, 0);
			$order['order_amount'] = $price + $payment['pay_fee'];
			$order['log_id'] = insert_pay_log($order['order_sn'], $order['order_amount'], $type = PAY_REGISTERED, 0);
			$order['pay_code'] = $payment['pay_code'];

			if (0 < $order['order_amount']) {
				include_once ADDONS_PATH . 'payment/' . $payment['pay_code'] . '.php';
				$pay_obj = new $payment['pay_code']();
				$pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));
				exit($pay_online);
			}
		}

		$this->assign('payment_list', $payment_list);
		$this->assign('page_title', L('distribution_application'));
		$this->display();
	}

	private function checkLogin()
	{
		$this->user_id = $_SESSION['user_id'];

		if (!$this->user_id) {
			$url = urlencode(__HOST__ . $_SERVER['REQUEST_URI']);

			if (IS_POST) {
				$url = urlencode($_SERVER['HTTP_REFERER']);
			}

			ecs_header('Location: ' . url('user/login/index', array('back_act' => $url)));
			exit();
		}
	}

	private function htmlOut($str)
	{
		if (function_exists('htmlspecialchars_decode')) {
			$str = htmlspecialchars_decode($str);
		}
		else {
			$str = html_entity_decode($str);
		}

		$str = stripslashes($str);
		return $str;
	}

	private function checkShopName()
	{
		$sql = 'SELECT shop_name FROM {pre}drp_shop WHERE user_id=' . $_SESSION['user_id'];
		$shop_name = $this->db->getOne($sql);

		if (empty($shop_name)) {
			ecs_header('Location:' . url('drp/index/register'));
		}
	}

	private function checkShopCategory()
	{
		$sql = 'SELECT cat_id FROM {pre}drp_shop WHERE user_id=' . $_SESSION['user_id'];
		$cat_id = $this->db->getOne($sql);

		if (empty($cat_id)) {
			ecs_header('Location:' . url('drp/index/category'));
		}
	}

	public function actionShopNotice()
	{
		$sql = 'SELECT value FROM {pre}drp_config WHERE `code`=\'notice\'';
		$novice = $this->db->getOne($sql);
		$novice = $this->htmlout($novice);
		$this->assign('novice', nl2br($novice));
		$this->display();
	}
}

?>
