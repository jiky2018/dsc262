<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Qrpay\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	private $pay_code = '';
	private $qrpay_id = 0;
	private $qrpay_info = '';

	public function __construct()
	{
		parent::__construct();
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
		header('Access-Control-Allow-Headers: X-HTTP-Method-Override, Content-Type, x-requested-with, Authorization');
		$helper_list = array('order');
		$this->load_helper($helper_list);
		$this->pay_code = IsWeixinOrAlipay();
		$this->qrpay_id = input('id', 0, 'intval');
	}

	public function actionIndex()
	{
		$this->qrpay_info = get_qrpay_info($this->qrpay_id);

		if ($this->pay_code == 'wxpay') {
			$_SESSION['openid_base'] = isset($_SESSION['openid_base']) && !empty($_SESSION['openid_base']) ? $_SESSION['openid_base'] : $this->getOpenid();
		}

		if (IS_AJAX) {
			if (!empty($this->qrpay_info)) {
				if ($this->qrpay_info['type'] == 1) {
					$pay_amount = $this->qrpay_info['amount'];
				}
				else {
					$pay_amount = 0;
				}

				if (0 < $this->qrpay_info['ru_id']) {
					$shop_name = dao('merchants_shop_information')->alias('a')->join(C('DB_PREFIX') . 'seller_shopinfo b on a.user_id = b.ru_id')->where(array('ru_id' => $this->qrpay_info['ru_id'], 'b.shop_close' => 1))->getField('shop_title');
				}
				else {
					$shop_name = C('shop.shop_name');
				}

				$detail = array(
					'seller' => $shop_name,
					'qrcode' => array('type' => $this->qrpay_info['type'], 'amount' => $pay_amount, 'qrpay_name' => $this->qrpay_info['qrpay_name'])
					);
				$this->response($detail);
			}
			else {
				$this->response(array('error' => 1, 'message' => '收款码不存在'));
			}
		}

		if (empty($this->qrpay_info)) {
			show_message('收款码不存在', L('msg_go_back'), '');
		}

		$this->assign('qrpay_info', $this->qrpay_info);
		$this->display();
	}

	public function actionPay()
	{
		if (IS_AJAX) {
			$self_amount = input('amount', 0, 'floatval');

			if ($self_amount <= 0) {
				$this->response(array('error' => 1, 'message' => '请输入支付金额'));
			}

			$this->qrpay_info = get_qrpay_info($this->qrpay_id);

			if (!empty($this->qrpay_info)) {
				if ($this->qrpay_info['type'] == 1) {
					$pay_amount = $this->qrpay_info['amount'];
				}
				else {
					$pay_amount = $self_amount;
				}

				if (0 < $this->qrpay_info['ru_id']) {
					$shop_name = dao('merchants_shop_information')->alias('a')->join(C('DB_PREFIX') . 'seller_shopinfo b on a.user_id = b.ru_id')->where(array('ru_id' => $this->qrpay_info['ru_id'], 'b.shop_close' => 1))->getField('shop_title');
				}
				else {
					$shop_name = C('shop.shop_name');
				}

				$order = array();
				$order['pay_order_sn'] = get_order_sn();

				if (0 < $pay_amount) {
					$discount_fee = do_discount_fee($this->qrpay_info['id'], $pay_amount);
					$pay_amount = $pay_amount - $discount_fee;
					$pay_amount = number_format($pay_amount, 2, '.', '');
				}

				$order['pay_amount'] = $pay_amount;
				$order['pay_user_id'] = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
				$order['openid'] = isset($_SESSION['openid_base']) ? $_SESSION['openid_base'] : '';
				$order['add_time'] = gmtime();
				$order['qrpay_id'] = $this->qrpay_info['id'];
				$order['pay_desc'] = isset($discount_fee) && 0 < $discount_fee ? get_discounts_name($this->qrpay_info['discount_id']) : '';
				$order['ru_id'] = !empty($this->qrpay_info['ru_id']) ? $this->qrpay_info['ru_id'] : 0;
				$order['payment_code'] = $this->pay_code;
				$error_no = 0;

				do {
					$order['pay_order_sn'] = get_order_sn();
					$new_order = $this->db->filter_field('qrpay_log', $order);

					try {
						$new_order_id = dao('qrpay_log')->data($new_order)->add();
					}
					catch (\Exception $e) {
						$error_no = (int) substr($e->getMessage(), 0, 4);
					}

					if (0 < $error_no && $error_no != 1062) {
						exit($e->getMessage());
					}
				} while ($error_no == 1062);

				$order['id'] = $new_order_id;
				$payment = get_payment_info($this->pay_code);
				if ($payment && 0 < $pay_amount) {
					$payData = $this->getPayData($order);

					try {
						$trade_type = $this->pay_code == 'wxpay' ? \Payment\Config::WX_CHANNEL_PUB : \Payment\Config::ALI_CHANNEL_WAP;
						$ret = \Payment\Client\Charge::run($trade_type, $this->getConfig(), $payData);
						$ret = $this->pay_code == 'wxpay' ? json_encode($ret, JSON_UNESCAPED_UNICODE) : $ret;
					}
					catch (\Payment\Common\PayException $e) {
						exit($e->getMessage());
					}
				}

				$detail = array(
					'seller'  => $shop_name,
					'qrcode'  => array('type' => $this->qrpay_info['type'], 'amount' => $pay_amount, 'qrpay_name' => $this->qrpay_info['qrpay_name']),
					'paycode' => $this->pay_code,
					'payment' => $ret
					);
				$this->response($detail);
			}
			else {
				$this->response(array('error' => 1, 'message' => '收款码不存在'));
			}
		}
	}

	public function actionCallback()
	{
		$msg_type = 2;
		$payment = get_payment_info($this->pay_code);

		if ($payment === false) {
			$msg = L('pay_disabled');
		}
		else if (!empty($_GET)) {
			try {
				if ($this->pay_code == 'alipay') {
					$order = array();
					list($order['pay_order_sn'], $order['id']) = explode('Q', $_GET['out_trade_no']);
					$res = $this->query($order);

					if ($res === true) {
						$msg = L('pay_success');
						$msg_type = 0;
					}
					else {
						$msg = L('pay_fail');
						$msg_type = 1;
					}
				}
				else if ($this->pay_code == 'wxpay') {
					$status = input('get.status', 0, 'intval');

					if ($status == 1) {
						$msg = L('pay_success');
						$msg_type = 0;
					}
					else {
						$msg = L('pay_fail');
						$msg_type = 1;
					}
				}
			}
			catch (\Payment\Common\PayException $e) {
				logResult($e->getMessage());
			}
		}
		else {
			$msg = L('pay_fail');
			$msg_type = 1;
		}

		$id = isset($order['id']) ? dao('qrpay_log')->where(array('id' => $order['id']))->getField('qrpay_id') : input('get.id', 0, 'intval');
		$this->assign('id', $id);
		$this->assign('message', $msg);
		$this->assign('msg_type', $msg_type);
		$this->assign('page_title', L('pay_status'));
		$this->display();
	}

	public function actionNotify()
	{
		$this->pay_code = str_replace('_qrpay', '', input('get.code'));

		if (isset($_GET['code'])) {
			unset($_GET['code']);
		}

		$config = $this->getConfig();
		$config['notify_url'] = preg_replace('/\\/public\\/notify/', '', $config['notify_url'], 1);

		if (isset($config['return_url'])) {
			$config['return_url'] = preg_replace('/\\/public\\/notify/', '', $config['return_url'], 1);
		}

		try {
			$callback = new OrderPaidNotify();
			$trade_type = $this->pay_code == 'wxpay' ? \Payment\Config::WX_CHARGE : \Payment\Config::ALI_CHARGE;
			$ret = \Payment\Client\Notify::run($trade_type, $config, $callback);
			exit($ret);
		}
		catch (\Payment\Common\PayException $e) {
			logResult($e->getMessage());
			exit('fail');
		}
	}

	public function query($order)
	{
		$data = array('out_trade_no' => $order['pay_order_sn'] . 'Q' . $order['id']);

		try {
			$trade_type = $this->pay_code == 'wxpay' ? \Payment\Config::WX_CHARGE : \Payment\Config::ALI_CHARGE;
			$ret = \Payment\Client\Query::run($trade_type, $this->getConfig(), $data);

			if ($ret['response']['trade_state'] === \Payment\Config::TRADE_STATUS_SUCC) {
				qrpay_order_paid($order['id'], 1);
				return true;
			}
		}
		catch (\Payment\Common\PayException $e) {
			logResult($e->getMessage());
		}

		return false;
	}

	private function getPayData($order)
	{
		$payData = array();

		if ($this->pay_code == 'alipay') {
			$payData = array('body' => $order['pay_order_sn'], 'subject' => !empty($order['pay_desc']) ? '【' . $order['pay_desc'] . '】' . $order['pay_order_sn'] : $order['pay_order_sn'], 'order_no' => $order['pay_order_sn'] . 'Q' . $order['id'], 'timeout_express' => time() + 3600 * 24, 'amount' => $order['pay_amount'], 'return_param' => 'qr' . $order['id'], 'client_ip' => $this->get_client_ip(), 'goods_type' => 1, 'store_id' => '');
		}

		if ($this->pay_code == 'wxpay') {
			$payData = array('body' => $order['pay_order_sn'], 'subject' => !empty($order['pay_desc']) ? '【' . $order['pay_desc'] . '】' . $order['pay_order_sn'] : $order['pay_order_sn'], 'order_no' => $order['pay_order_sn'] . 'Q' . $order['id'], 'timeout_express' => time() + 3600 * 24, 'amount' => $order['pay_amount'], 'return_param' => 'qr' . $order['id'], 'client_ip' => $this->get_client_ip(), 'openid' => $order['openid']);
		}

		return $payData;
	}

	protected function getConfig()
	{
		$payment = get_payment_info($this->pay_code);
		$config = array();

		if ($this->pay_code == 'alipay') {
			$config = array('use_sandbox' => (bool) $payment['use_sandbox'], 'partner' => $payment['alipay_partner'], 'app_id' => $payment['app_id'], 'sign_type' => $payment['sign_type'], 'ali_public_key' => $payment['ali_public_key'], 'rsa_private_key' => $payment['rsa_private_key'], 'notify_url' => __URL__ . '/public/notify/' . $this->pay_code . '_qrpay.php', 'return_url' => url('qrpay/index/callback', array('id' => $this->qrpay_id), 0, true), 'return_raw' => false);
		}

		if ($this->pay_code == 'wxpay') {
			$config = array('use_sandbox' => (bool) $payment['use_sandbox'], 'app_id' => $payment['wxpay_appid'], 'mch_id' => $payment['wxpay_mchid'], 'md5_key' => $payment['wxpay_key'], 'sign_type' => 'MD5', 'fee_type' => 'CNY', 'notify_url' => __URL__ . '/public/notify/' . $this->pay_code . '_qrpay.php', 'return_raw' => false);
		}

		return $config;
	}

	private function get_client_ip()
	{
		if ($_SERVER['REMOTE_ADDR']) {
			$cip = $_SERVER['REMOTE_ADDR'];
		}
		else if (getenv('REMOTE_ADDR')) {
			$cip = getenv('REMOTE_ADDR');
		}
		else if (getenv('HTTP_CLIENT_IP')) {
			$cip = getenv('HTTP_CLIENT_IP');
		}
		else {
			$cip = '127.0.0.1';
		}

		return $cip;
	}

	protected function getOpenid()
	{
		if (empty($_SESSION['openid_base'])) {
			$payment = get_payment_info($this->pay_code);
			$options = array('appid' => $payment['wxpay_appid'], 'appsecret' => $payment['wxpay_appsecret']);
			$obj = new \App\Extensions\Wechat($options);
			if (isset($_GET['code']) && $_GET['state'] == 'qrrepeat') {
				$token = $obj->getOauthAccessToken();
				$_SESSION['openid_base'] = $token['openid'];
				return $_SESSION['openid_base'];
			}

			$callback = __HOST__ . $_SERVER['REQUEST_URI'];
			$url = $obj->getOauthRedirect($callback, 'qrrepeat', 'snsapi_base');
			redirect($url);
		}
	}

	protected function checklogin()
	{
		if (empty($_SESSION['user_id'])) {
			$back_act = __HOST__ . $_SERVER['REQUEST_URI'];
			$this->redirect('user/login/index', array('back_act' => urlencode($back_act)));
		}
	}
}
class OrderPaidNotify implements \Payment\Notify\PayNotifyInterface
{
	public function notifyProcess(array $data)
	{
		$out_trade_no = explode('Q', $data['order_no']);
		$log_id = $out_trade_no[1];
		qrpay_order_paid($log_id, 1);
		update_trade_data($log_id, $data);
		insert_seller_account_log($log_id);
		return true;
	}
}

?>
