<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Flow\Controllers;

class AjaxController extends \App\Modules\Base\Controllers\FrontendController
{
	public function __construct()
	{
		parent::__construct();
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		L(require LANG_PATH . C('shop.lang') . '/flow.php');
		$files = array('order');
		$this->load_helper($files);
	}

	public function actionselectshipping()
	{
		$result = array('error' => '', 'content' => '', 'need_insure' => 0);
		$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods = cart_goods($flow_type);
		if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
			$result['error'] = L('no_goods_in_cart');
		}
		else {
			$this->assign('config', C('shop'));
			$order = flow_order_info();
			$order['shipping_id'] = intval($_REQUEST['shipping']);
			$regions = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);
			$shipping_info = shipping_area_info($order['shipping_id'], $regions);
			$total = order_fee($order, $cart_goods, $consignee);
			$this->assign('total', $total);
			$this->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
			$this->assign('total_bonus', price_format(get_total_bonus(), false));

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}

			$result['cod_fee'] = $shipping_info['pay_fee'];

			if (strpos($result['cod_fee'], '%') === false) {
				$result['cod_fee'] = price_format($result['cod_fee'], false);
			}

			$result['need_insure'] = (0 < $shipping_info['insure']) && !empty($order['need_insure']) ? 1 : 0;
			$result['content'] = $this->fetch('lib_order_total.html');
		}

		exit(json_encode($result));
	}

	public function actionselectinsure()
	{
		$result = array('error' => '', 'content' => '', 'need_insure' => 0);
		$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods = cart_goods($flow_type);
		if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
			$result['error'] = L('no_goods_in_cart');
		}
		else {
			$this->assign('config', C('shop'));
			$order = flow_order_info();
			$order['need_insure'] = intval($_REQUEST['insure']);
			$_SESSION['flow_order'] = $order;
			$total = order_fee($order, $cart_goods, $consignee);
			$this->assign('total', $total);
			$this->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
			$this->assign('total_bonus', price_format(get_total_bonus(), false));

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}

			$result['content'] = $this->fetch('lib_order_total.html');
		}

		exit(json_encode($result));
	}

	public function actionselectpayment()
	{
		$result = array('error' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);
		$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods = cart_goods($flow_type);
		if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
			$result['error'] = L('no_goods_in_cart');
		}
		else {
			$this->assign('config', C('shop'));
			$order = flow_order_info();
			$order['pay_id'] = intval($_REQUEST['payment']);
			$payment_info = payment_info($order['pay_id']);
			$result['pay_code'] = $payment_info['pay_code'];
			$_SESSION['flow_order'] = $order;
			$total = order_fee($order, $cart_goods, $consignee);
			$this->assign('total', $total);
			$this->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
			$this->assign('total_bonus', price_format(get_total_bonus(), false));

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}

			$result['content'] = $this->fetch('lib_order_total.html');
		}

		exit(json_encode($result));
	}

	public function actionselectpack()
	{
		$result = array('error' => '', 'content' => '', 'need_insure' => 0);
		$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods = cart_goods($flow_type);
		if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
			$result['error'] = L('no_goods_in_cart');
		}
		else {
			$this->assign('config', C('shop'));
			$order = flow_order_info();
			$order['pack_id'] = intval($_REQUEST['pack']);
			$_SESSION['flow_order'] = $order;
			$total = order_fee($order, $cart_goods, $consignee);
			$this->assign('total', $total);
			$this->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
			$this->assign('total_bonus', price_format(get_total_bonus(), false));

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}

			$result['content'] = $this->fetch('lib_order_total.html');
		}

		exit(json_encode($result));
	}

	public function actionselectcard()
	{
		$result = array('error' => '', 'content' => '', 'need_insure' => 0);
		$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods = cart_goods($flow_type);
		if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
			$result['error'] = L('no_goods_in_cart');
		}
		else {
			$this->assign('config', C('shop'));
			$order = flow_order_info();
			$order['card_id'] = intval($_REQUEST['card']);
			$_SESSION['flow_order'] = $order;
			$total = order_fee($order, $cart_goods, $consignee);
			$this->assign('total', $total);
			$this->assign('total_integral', cart_amount(false, $flow_type) - $order['bonus'] - $total['integral_money']);
			$this->assign('total_bonus', price_format(get_total_bonus(), false));

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}

			$result['content'] = $this->fetch('lib_order_total.html');
		}

		exit(json_encode($result));
	}

	public function actionchangesurplus()
	{
		$surplus = floatval($_GET['surplus']);
		$user_info = user_info($_SESSION['user_id']);

		if (($user_info['user_money'] + $user_info['credit_line']) < $surplus) {
			$result['error'] = L('surplus_not_enough');
		}
		else {
			$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
			$this->assign('config', C('shop'));
			$consignee = get_consignee($_SESSION['user_id']);
			$cart_goods = cart_goods($flow_type);
			if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
				$result['error'] = L('no_goods_in_cart');
			}
			else {
				$order = flow_order_info();
				$order['surplus'] = $surplus;
				$total = order_fee($order, $cart_goods, $consignee);
				$this->assign('total', $total);

				if ($flow_type == CART_GROUP_BUY_GOODS) {
					$this->assign('is_group_buy', 1);
				}

				$result['content'] = $this->fetch('lib_order_total.html');
			}
		}

		exit(json_encode($result));
	}

	public function actionchangeintegral()
	{
		$points = floatval($_GET['points']);
		$user_info = user_info($_SESSION['user_id']);
		$order = flow_order_info();
		$flow_points = flow_available_points($_SESSION['cart_value']);
		$user_points = $user_info['pay_points'];

		if ($user_points < $points) {
			$result['error'] = L('integral_not_enough');
		}
		else if ($flow_points < $points) {
			$result['error'] = sprintf(L('integral_too_much'), $flow_points);
		}
		else {
			$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
			$order['integral'] = $points;
			$consignee = get_consignee($_SESSION['user_id']);
			$cart_goods = cart_goods($flow_type);
			if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
				$result['error'] = L('no_goods_in_cart');
			}
			else {
				$total = order_fee($order, $cart_goods, $consignee);
				$this->assign('total', $total);
				$this->assign('config', C('shop'));

				if ($flow_type == CART_GROUP_BUY_GOODS) {
					$this->assign('is_group_buy', 1);
				}

				$result['content'] = $this->fetch('lib_order_total.html');
				$result['error'] = '';
			}
		}

		exit(josn_encode($result));
	}

	public function actionchangebonus()
	{
		$result = array('error' => '', 'content' => '');
		$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods = cart_goods($flow_type);
		if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
			$result['error'] = L('no_goods_in_cart');
		}
		else {
			$this->assign('config', C('shop'));
			$order = flow_order_info();
			$bonus = bonus_info(intval($_GET['bonus']));
			if ((!empty($bonus) && ($bonus['user_id'] == $_SESSION['user_id'])) || ($_GET['bonus'] == 0)) {
				$order['bonus_id'] = intval($_GET['bonus']);
			}
			else {
				$order['bonus_id'] = 0;
				$result['error'] = L('invalid_bonus');
			}

			$total = order_fee($order, $cart_goods, $consignee);
			$this->assign('total', $total);

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}

			$result['content'] = $this->fetch('lib_order_total.html');
		}

		exit(json_encode($result));
	}

	public function actionchangeneedinv()
	{
		$result = array('error' => '', 'content' => '');
		$_GET['inv_type'] = !empty($_GET['inv_type']) ? json_str_iconv(urldecode($_GET['inv_type'])) : '';
		$_GET['invPayee'] = !empty($_GET['invPayee']) ? json_str_iconv(urldecode($_GET['invPayee'])) : '';
		$_GET['inv_content'] = !empty($_GET['inv_content']) ? json_str_iconv(urldecode($_GET['inv_content'])) : '';
		$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods = cart_goods($flow_type);
		if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
			$result['error'] = L('no_goods_in_cart');
			exit(json_encode($result));
		}
		else {
			$this->assign('config', C('shop'));
			$order = flow_order_info();
			if (isset($_GET['need_inv']) && (intval($_GET['need_inv']) == 1)) {
				$order['need_inv'] = 1;
				$order['inv_type'] = trim(stripslashes($_GET['inv_type']));
				$order['inv_payee'] = trim(stripslashes($_GET['inv_payee']));
				$order['inv_content'] = trim(stripslashes($_GET['inv_content']));
			}
			else {
				$order['need_inv'] = 0;
				$order['inv_type'] = '';
				$order['inv_payee'] = '';
				$order['inv_content'] = '';
			}

			$total = order_fee($order, $cart_goods, $consignee);
			$this->assign('total', $total);

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}

			exit($this->fetch('lib_order_total.html'));
		}
	}

	public function actionchangeoos()
	{
		$order = flow_order_info();
		$order['how_oos'] = intval($_GET['oos']);
		$_SESSION['flow_order'] = $order;
	}

	public function actionchecksurplus()
	{
		$result = array('error' => 0, 'msg' => '');
		$surplus = floatval($_GET['surplus']);
		$user_info = user_info($_SESSION['user_id']);

		if (($user_info['user_money'] + $user_info['credit_line']) < $surplus) {
			$result = array('error' => 1, 'msg' => L('surplus_not_enough'));
			exit(json_encode($result));
		}

		exit(json_encode($result));
	}

	public function actionCheckIntegral()
	{
		if (IS_AJAX) {
			$result = array('error' => 0, 'msg' => '');
			$points = floatval($_GET['integral']);
			$user_info = user_info($_SESSION['user_id']);
			$flow_points = flow_available_points($_SESSION['cart_value']);
			$user_points = $user_info['pay_points'];

			if (0 < $points) {
				if ($user_points < $points) {
					$result = array('error' => 1, 'msg' => L('integral_not_enough'));
					exit(json_encode($result));
				}

				if ($flow_points < $points) {
					$result = array('error' => 1, 'msg' => sprintf(L('integral_too_much'), $flow_points));
					exit(json_encode($result));
				}
			}

			exit(json_encode($result));
		}
	}

	public function actionCheckPayPaypwd()
	{
		if (IS_AJAX) {
			$result = array('error' => 0, 'msg' => '');
			$pay_paypwd = I('pay_paypwd', '', 'trim');

			if (!empty($pay_paypwd)) {
				$res = dao('users_paypwd')->field('pay_password, ec_salt')->where(array('user_id' => $_SESSION['user_id']))->find();
				$new_password = md5(md5($pay_paypwd) . $res['ec_salt']);

				if ($new_password != $res['pay_password']) {
					$result = array('error' => 1, 'msg' => '支付密码输入不正确');
					exit(json_encode($result));
				}

				exit(json_encode($result));
			}
			else {
				$result = array('error' => 1, 'msg' => '支付密码不能为空');
				exit(json_encode($result));
			}

			exit(json_encode($result));
		}
	}
}

?>
