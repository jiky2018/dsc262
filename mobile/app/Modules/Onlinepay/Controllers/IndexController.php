<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Onlinepay\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	public function __construct()
	{
		parent::__construct();
		$files = array('order', 'clips', 'transaction');
		$this->load_helper($files);
		$this->check_login();

		if (!empty($_SESSION['user_id'])) {
			$this->sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
			$this->a_sess = ' a.user_id = \'' . $_SESSION['user_id'] . '\' ';
			$this->b_sess = ' b.user_id = \'' . $_SESSION['user_id'] . '\' ';
			$this->c_sess = ' c.user_id = \'' . $_SESSION['user_id'] . '\' ';
			$this->sess_ip = '';
		}
		else {
			$this->sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
			$this->a_sess = ' a.session_id = \'' . real_cart_mac_ip() . '\' ';
			$this->b_sess = ' b.session_id = \'' . real_cart_mac_ip() . '\' ';
			$this->c_sess = ' c.session_id = \'' . real_cart_mac_ip() . '\' ';
			$this->sess_ip = real_cart_mac_ip();
		}
	}

	public function actionIndex()
	{
		$order_sn = input('order_sn', '', array('trim', 'html_in'));
		$order_id = dao('order_info')->field('order_id')->where(array('order_sn' => $order_sn, 'user_id' => $_SESSION['user_id']))->find();

		if (empty($order_id)) {
			show_message('非法操作', '', url('/'), 'warning');
		}

		$payment_list = available_payment_list(0, 0);

		if (isset($payment_list)) {
			foreach ($payment_list as $key => $payment) {
				if (substr($payment['pay_code'], 0, 4) == 'pay_') {
					unset($payment_list[$key]);
					continue;
				}

				if ($payment['is_online'] != 1) {
					unset($payment_list[$key]);
				}

				if ($payment['pay_code'] == 'cod') {
					unset($payment_list[$key]);
				}

				if ($payment['is_cod'] == '1') {
					$payment_list[$key]['format_pay_fee'] = '<span id="ECS_CODFEE">' . $payment['format_pay_fee'] . '</span>';
				}

				if (!file_exists(ADDONS_PATH . 'payment/' . $payment['pay_code'] . '.php')) {
					unset($payment_list[$key]);
				}

				if ($payment['pay_code'] == 'balance') {
					unset($payment_list[$key]);
				}

				if ($payment['pay_code'] == 'wxpay') {
					if (!is_dir(APP_WECHAT_PATH)) {
						unset($payment_list[$key]);
					}

					if (is_wechat_browser() == false && is_wxh5() == 0) {
						unset($payment_list[$key]);
					}
				}
			}
		}

		if (empty($payment_list)) {
			show_message('请安装在线支付方式', '', url('user/order/index'), 'warning');
		}

		$order = $this->db->getRow('SELECT * FROM {pre}order_info WHERE order_id=\'' . $order_id['order_id'] . '\' LIMIT 1');
		$order['log_id'] = $GLOBALS['db']->getOne(' SELECT log_id FROM ' . $GLOBALS['ecs']->table('pay_log') . ' WHERE order_id = \'' . $order_id['order_id'] . '\' LIMIT 1 ');

		if (0 < $order['order_amount']) {
			$onlinepay_pay_id = $this->db->getOne('SELECT pay_id FROM {pre}payment WHERE pay_code=\'onlinepay\'');
			$order_pay_enabled = $this->db->getOne('SELECT enabled FROM {pre}payment WHERE pay_code=' . $order['pay_id']);
			if ($order_pay_enabled == 0 || $order['pay_id'] == $onlinepay_pay_id) {
				$default_payment = reset($payment_list);
				$order['pay_id'] = $default_payment['pay_id'];
			}
		}
		else {
			show_message('非法操作', '', url('/'), 'warning');
		}

		if (!empty($order['pay_id'])) {
			$payment = payment_info($order['pay_id']);
			$sql = 'UPDATE {pre}order_info set pay_id=\'' . $order['pay_id'] . '\',pay_name=\'' . $payment['pay_name'] . '\' WHERE order_id = \'' . $order['order_id'] . '\'';
			$this->db->query($sql);
			$sql = 'SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE main_order_id = \'' . $order_id['order_id'] . '\'');
			$child_order_id_arr = $GLOBALS['db']->getAll($sql);
			if ($order['main_order_id'] == 0 && 0 < count($child_order_id_arr) && 0 < $order['order_id']) {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET pay_id = \'' . $order['pay_id'] . '\', ' . ' pay_name = \'' . $payment['pay_name'] . '\'' . ('WHERE main_order_id = \'' . $order['order_id'] . '\'');
				$GLOBALS['db']->query($sql);
			}
		}
		else {
			show_message('非法操作', '', url('user/order/index'), 'warning');
		}

		include_once ADDONS_PATH . 'payment/' . $payment['pay_code'] . '.php';
		$pay_obj = new $payment['pay_code']();
		$order['pay_desc'] = $payment['pay_desc'];
		$pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));
		$this->assign('pay_online', $pay_online);
		$order['order_amount'] = price_format($order['order_amount']);
		$this->assign('order', $order);
		$this->assign('payment_list', $payment_list);
		$this->assign('page_title', '收银台');
		$this->display();
	}

	public function actionChangePayment()
	{
		$payment_id = I('pay_id', 0, 'intval');
		$order_id = I('order_id', 0, 'intval');

		if (empty($payment_id)) {
			show_message('非法操作', '', url('/'), 'warning');
		}

		if (IS_AJAX) {
			$payment = payment_info($payment_id);
			$order = $this->db->getRow('SELECT * FROM {pre}order_info WHERE order_id=\'' . $order_id . '\' LIMIT 1');
			$order['log_id'] = $GLOBALS['db']->getOne(' SELECT log_id FROM ' . $GLOBALS['ecs']->table('pay_log') . ' WHERE order_id = \'' . $order_id . '\' LIMIT 1 ');
			$sql = 'UPDATE {pre}order_info set pay_id=\'' . $payment_id . '\',pay_name=\'' . $payment['pay_name'] . '\' WHERE order_id = \'' . $order_id . '\'';
			$this->db->query($sql);
			$sql = 'SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE main_order_id = \'' . $order_id . '\'');
			$child_order_id_arr = $GLOBALS['db']->getAll($sql);
			if ($order['main_order_id'] == 0 && 0 < count($child_order_id_arr) && 0 < $order_id) {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET pay_id = \'' . $payment_id . '\', ' . ' pay_name = \'' . $payment['pay_name'] . '\', ' . ('WHERE main_order_id = \'' . $order_id . '\'');
				$GLOBALS['db']->query($sql);
			}

			include_once ADDONS_PATH . 'payment/' . $payment['pay_code'] . '.php';
			$pay_obj = new $payment['pay_code']();
			$order['pay_desc'] = $payment['pay_desc'];
			$pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));
			exit($pay_online);
		}
	}

	public function check_login()
	{
		if (!$_SESSION['user_id']) {
			$back_act = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];

			if (IS_AJAX) {
				$this->ajaxReturn(array('error' => 1, 'message' => L('yet_login'), 'url' => url('user/login/index', array('back_act' => urlencode($back_act)))));
			}

			$this->redirect('user/login/index', array('back_act' => urlencode($back_act)));
		}
	}
}

?>
