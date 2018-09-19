<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\User\Controllers;

class AccountController extends \App\Modules\Base\Controllers\FrontendController
{
	/**
     * 用户id
     * @var
     */
	public $user_id;

	public function __construct()
	{
		parent::__construct();
		$this->user_id = $_SESSION['user_id'];
		$this->actionchecklogin();
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		L(require LANG_PATH . C('shop.lang') . '/flow.php');
		$files = array('order', 'clips', 'payment', 'transaction');
		$this->load_helper($files);
	}

	public function actionIndex()
	{
		$surplus_amount = get_user_surplus($this->user_id);
		$this->assign('surplus_amount', $surplus_amount ? $surplus_amount : 0);
		$frozen_money = get_user_frozen($this->user_id);
		$this->assign('frozen_money', $frozen_money ? $frozen_money : 0);
		$this->assign('record_count', my_bonus($this->user_id));
		$sql = ' SELECT COUNT(*) AS num, SUM(card_money) AS money FROM {pre}value_card WHERE user_id = \'' . $this->user_id . '\' ';
		$vc = $this->db->getRow($sql);
		$vc['money'] = price_format($vc['money']);
		$this->assign('value_card', $vc);
		$pay_points = $this->db->getOne('SELECT  pay_points FROM {pre}users WHERE user_id=\'' . $this->user_id . '\'');
		$this->assign('pay_points', $pay_points ? $pay_points : 0);
		$this->assign('page_title', L('label_user_surplus'));
		$this->display();
	}

	public function actionPayPoints()
	{
		if (IS_AJAX) {
			$account_type = 'pay_points';
			$page = I('page', 1, 'intval');
			$size = 10;
			$log_list = get_user_accountlog_count($this->user_id, $account_type, $page, $size);
			exit(json_encode(array('list' => $log_list, 'totalPage' => $log_list['totalPage'])));
		}

		$this->assign('page_title', L('user_pay_points'));
		$this->display();
	}

	public function actionDetail()
	{
		if (IS_AJAX) {
			$account_type = 'user_money';
			$page = I('page', 1, 'intval');
			$size = 10;
			$log_list = get_user_accountlog_count($this->user_id, $account_type, $page, $size);
			exit(json_encode(array('list' => $log_list, 'totalPage' => $log_list['totalPage'])));
		}

		$this->assign('page_title', L('account_detail'));
		$this->display();
	}

	public function actionDeposit()
	{
		$surplus_id = isset($_GET['id']) ? intval($_GET['id']) : 2;
		$account = get_surplus_info($surplus_id);
		$payment_list = get_online_payment_list(false);

		foreach ($payment_list as $key => $val) {
			if (!file_exists(ADDONS_PATH . 'payment/' . $val['pay_code'] . '.php')) {
				unset($payment_list[$key]);
			}

			if ($val['pay_code'] == 'onlinepay') {
				unset($payment_list[$key]);
			}
		}

		$this->assign('payment', $payment_list);
		$this->assign('order', $account);
		$this->assign('process_type', $surplus_id);
		$this->assign('page_title', L('account_user_charge'));
		$this->display();
	}

	public function actionAccountRaply()
	{
		$surplus_amount = get_user_surplus($this->user_id);

		if (empty($surplus_amount)) {
			$surplus_amount = 0;
		}

		$sql = 'SELECT * FROM {pre}users_real WHERE review_status = 1 AND user_id=' . $this->user_id;
		$result = $this->db->getRow($sql);

		if (!$result) {
			ecs_header('Location: ' . url('user/profile/realname'));
		}

		$bank = array(
			array('bank_name' => $result['bank_name'], 'bank_card' => substr($result['bank_card'], 0, 4) . '******' . substr($result['bank_card'], -4), 'bank_region' => $result['bank_name'], 'bank_user_name' => $result['real_name'], 'bank_card_org' => $result['bank_card'], 'bank_mobile' => $result['bank_mobile'])
			);
		$this->assign('bank', $bank);
		$this->assign('surplus_amount', price_format($surplus_amount, false));
		$this->assign('page_title', L('label_user_surplus'));
		$this->assign('deposit_fee', C('shop.deposit_fee'));
		$this->display();
	}

	public function actionAccount()
	{
		if (I('surplus_type') == 1) {
			$real_id = $this->db->table('users_real')->where(array('user_id' => $this->user_id))->find();
			$buyer_cash = dao('shop_config')->where(array('code' => 'buyer_cash'))->getField('value');

			if (empty($real_id)) {
				show_message(L('user_real'));
			}
		}

		$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

		if ($amount <= 0) {
			show_message(L('amount_gt_zero'));
		}

		if (I('surplus_type') == 1 && $amont < $buyer_cash) {
			show_message(L('amount_gt_little'));
		}

		if (I('surplus_type') == 0) {
			$buyer_recharge = dao('shop_config')->where(array('code' => 'buyer_recharge'))->getField('value');

			if ($amount < $buyer_recharge) {
				show_message(L('amount_gt_pay'));
			}
		}

		$surplus = array('user_id' => $this->user_id, 'rec_id' => !empty($_POST['rec_id']) ? intval($_POST['rec_id']) : 0, 'process_type' => isset($_POST['surplus_type']) ? intval($_POST['surplus_type']) : 0, 'payment_id' => isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0, 'user_note' => isset($_POST['user_note']) ? trim($_POST['user_note']) : '', 'amount' => $amount);

		if ($surplus['process_type'] == 1) {
			if (C('shop.sms_signin')) {
				if ($_POST['mobile'] != $_SESSION['sms_mobile'] || $_POST['mobile_code'] != $_SESSION['sms_mobile_code']) {
					show_message(L('mobile_code_fail'), L('back_input_code'), '', 'error');
				}
			}

			$sur_amount = get_user_surplus($this->user_id);

			if ($sur_amount < $amount) {
				$content = L('surplus_amount_error');
				show_message($content, L('back_page_up'), '', 'info');
			}

			if (empty($_POST['bank_number']) || empty($_POST['real_name'])) {
				$content = L('account_withdraw_deposit');
				show_message($content, L('account_submit_information'), '', 'warning');
			}

			$deposit_fee = !!C('shop.deposit_fee') ? intval(C('shop.deposit_fee')) : 0;
			$deposit_money = 0;

			if (0 < $deposit_fee) {
				$deposit_money = $amount * $deposit_fee / 100;
			}

			if ($sur_amount < $amount + $deposit_money) {
				$amount = $amount - $deposit_money;
			}

			$surplus['deposit_fee'] = '-' . $deposit_money;
			$frozen_money = $amount + $deposit_money;
			$amount = '-' . $amount;
			$surplus['payment'] = '';
			$surplus['rec_id'] = insert_user_account($surplus, $amount);

			if (0 < $surplus['rec_id']) {
				$user_account_fields = array('user_id' => $surplus['user_id'], 'account_id' => $surplus['rec_id'], 'bank_number' => !empty($_POST['bank_number']) ? trim($_POST['bank_number']) : '', 'real_name' => !empty($_POST['real_name']) ? trim($_POST['real_name']) : '');
				insert_user_account_fields($user_account_fields);
				log_account_change($this->user_id, $amount, $frozen_money, 0, 0, '【' . L('application_withdrawal') . '】' . $surplus['user_note'], ACT_ADJUSTING, 0, $surplus['deposit_fee']);
				unset($_SESSION['sms_mobile']);
				unset($_SESSION['sms_mobile_code']);
				$content = L('surplus_appl_submit');
				show_message($content, L('back_account_log'), url('log'), 'info');
			}
			else {
				$content = L('process_false');
				show_message($content, L('back_page_up'), '', 'info');
			}
		}
		else {
			if ($surplus['payment_id'] <= 0) {
				show_message(L('select_payment_pls'));
			}

			$payment_info = array();
			$payment_info = payment_info($surplus['payment_id']);
			$surplus['payment'] = $payment_info['pay_name'];

			if (0 < $surplus['rec_id']) {
				$surplus['rec_id'] = update_user_account($surplus);
			}
			else {
				$surplus['rec_id'] = insert_user_account($surplus, $amount);
			}

			$payment = unserialize_config($payment_info['pay_config']);
			$order = array();
			$order['order_sn'] = $surplus['rec_id'];
			$order['user_name'] = $_SESSION['user_name'];
			$order['surplus_amount'] = $amount;
			$payment_info['pay_fee'] = pay_fee($surplus['payment_id'], $order['surplus_amount'], 0);
			$order['order_amount'] = $amount + $payment_info['pay_fee'];
			$order['log_id'] = insert_pay_log($surplus['rec_id'], $order['order_amount'], $type = PAY_SURPLUS, 0);

			if (!file_exists(ADDONS_PATH . 'payment/' . $payment_info['pay_code'] . '.php')) {
				unset($payment_info['pay_code']);
				ecs_header('Location: ' . url('user/account/log'));
			}
			else {
				include_once ADDONS_PATH . 'payment/' . $payment_info['pay_code'] . '.php';
				$pay_obj = new $payment_info['pay_code']();
				$payment_info['pay_button'] = $pay_obj->get_code($order, $payment);
				$pay_fee = !empty($payment_info['pay_fee']) ? price_format($payment_info['pay_fee'], false) : 0;
				$this->assign('payment', $payment_info);
				$this->assign('pay_fee', $pay_fee);
				$this->assign('amount', price_format($amount, false));
				$this->assign('order', $order);
				$this->assign('type', 1);
				$this->assign('page_title', L('account_charge'));
				$this->assign('but', $payment_info['pay_button']);
				$this->display();
			}
		}
	}

	public function actionLog()
	{
		$sql = 'SELECT COUNT(*) FROM {pre}user_account  WHERE user_id = \'' . $this->user_id . '\'  AND process_type ' . db_create_in(array(SURPLUS_SAVE, SURPLUS_RETURN));
		$record_count = $this->db->getOne($sql);

		if (IS_AJAX) {
			$page = I('page', 1, 'intval');
			$offset = 10;
			$page_size = ceil($record_count / $offset);
			$limit = ' LIMIT ' . ($page - 1) * $offset . ',' . $offset;
			$log_list = get_account_log($this->user_id, '', '', '', $count, $limit);
			exit(json_encode(array('log_list' => $log_list['log_list'], 'totalPage' => $page_size, 'count' => $count)));
		}

		$this->assign('page_title', L('account_apply_record'));
		$this->display();
	}

	public function actionAccountDetail()
	{
		$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
		$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
		$log_detail = get_account_log($this->user_id, $pager['size'], $pager['start'], $id);
		$account_log = $log_detail['log_list'];

		if (!$account_log) {
			$this->redirect('user/account/log');
		}

		foreach ($account_log as $key => $val) {
			$account_log[$key]['pay_fee'] = empty($val['pay_fee']) ? price_format(0) : price_format($val['pay_fee']);
		}

		$this->assign('surplus_amount', price_format($surplus_amount, false));
		$this->assign('account_log', $account_log);
		$this->assign('pager', $pager);
		$this->assign('page_title', L('account_details'));
		$this->display('account');
	}

	public function actionCancel()
	{
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if ($id == 0 || $this->user_id == 0) {
			ecs_header('Location: ' . url('user/account/log'));
			exit();
		}

		$result = del_user_account($id, $this->user_id);

		if ($result) {
			ecs_header('Location: ' . url('user/account/log'));
			exit();
		}
	}

	public function actionBonus()
	{
		if (IS_AJAX) {
			$page = I('page', 0, 'intval');
			$size = I('size', 0, 'intval');
			$type = I('type', 0, 'intval');
			$num = get_user_conut_bonus($this->user_id);
			$result['bonus_list'] = get_user_bouns_list($this->user_id, $type, $size, ($page - 1) * $size);
			$result['totalPage'] = ceil($num / $size);
			exit(json_encode($result));
		}

		$bonus1 = get_user_bouns_list($this->user_id, 0, 15, 0);
		$bonus2 = get_user_bouns_list($this->user_id, 1, 15, 0);
		$bonus3 = get_user_bouns_list($this->user_id, 2, 15, 0);
		$status['one'] = count($bonus1);
		$status['two'] = count($bonus2);
		$status['three'] = count($bonus3);
		$this->assign('status', $status);
		$this->assign('page_title', L('account_discount_list'));
		$this->display();
	}

	public function actionCoupont()
	{
		$size = 10;
		$page = I('page', 1, 'intval');
		$status = I('status', 0, 'intval');

		if (IS_AJAX) {
			$coupons_list = get_coupons_lists($size, $page, $status);
			exit(json_encode(array('coupons_list' => $coupons_list, 'totalPage' => $coupons_list['totalpage'])));
		}

		$this->assign('status', $status);
		$this->assign('page_title', L('coupont_list'));
		$this->display();
	}

	public function actionAddbonus()
	{
		if (IS_POST) {
			$bouns_sn = input('bonus_sn', 0, 'intval');
			$bouns_password = input('bouns_password', '', array('htmlspecialchars', 'trim'));

			if (empty($bouns_sn)) {
				show_message('红包口令不能为空', L('back_up_page'), url('user/account/bonus'));
			}

			if (empty($bouns_password)) {
				show_message('红包密码不能为空', L('back_up_page'), url('user/account/bonus'));
			}

			if (add_bonus($this->user_id, $bouns_sn, $bouns_password)) {
				show_message(L('add_bonus_sucess'), L('back_up_page'), url('user/account/bonus'), 'info');
			}
			else {
				show_message(L('add_bonus_false'), L('back_up_page'), url('user/account/bonus'));
			}
		}

		$this->assign('page_title', L('add_bonus'));
		$this->display();
	}

	public function actionExchange()
	{
		$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
		$account_type = 'pay_points';
		$sql = 'SELECT COUNT(*) FROM {pre}account_log  WHERE user_id = \'' . $this->user_id . '\'  AND ' . $account_type . ' <> 0 ';
		$record_count = $this->db->getOne($sql);
		$pager = get_pager(url('user/account/exchange'), array(), $record_count, $page);
		$pay_points = $this->db->getOne('SELECT  pay_points FROM {pre}users WHERE user_id=\'' . $this->user_id . '\'');

		if (empty($pay_points)) {
			$pay_points = 0;
		}

		$account_log = array();
		$sql = 'SELECT * FROM {pre}account_log  WHERE user_id = \'' . $this->user_id . '\'  AND ' . $account_type . ' <> 0   ORDER BY log_id DESC';
		$res = $GLOBALS['db']->selectLimit($sql, $pager['size'], $pager['start']);

		foreach ($res as $row) {
			$row['change_time'] = local_date(C('shop.date_format'), $row['change_time']);
			$row['type'] = 0 < $row[$account_type] ? L('account_inc') : L('account_dec');
			$row['user_money'] = price_format(abs($row['user_money']), false);
			$row['frozen_money'] = price_format(abs($row['frozen_money']), false);
			$row['rank_points'] = abs($row['rank_points']);
			$row['pay_points'] = abs($row['pay_points']);
			$row['short_change_desc'] = sub_str($row['change_desc'], 60);
			$row['amount'] = $row[$account_type];
			$account_log[] = $row;
		}

		$this->assign('pay_points', $pay_points);
		$this->assign('account_log', $account_log);
		$this->assign('pager', $pager);
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

	public function actionPay()
	{
		$surplus_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$payment_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

		if ($surplus_id == 0) {
			ecs_header('Location: ' . url('user/account_log'));
			exit();
		}

		if ($payment_id == 0) {
			ecs_header('Location: ' . url('user/account_deposit', array('id' => $surplus_id)));
			exit();
		}

		$order = array();
		$order = get_surplus_info($surplus_id);
		$payment_info = array();
		$payment_info = payment_info($payment_id);

		if (!empty($payment_info)) {
			$payment = unserialize_config($payment_info['pay_config']);
			$order['order_sn'] = $surplus_id;
			$order['log_id'] = get_paylog_id($surplus_id, $pay_type = PAY_SURPLUS);
			$order['user_name'] = $_SESSION['user_name'];
			$order['surplus_amount'] = $order['amount'];
			$payment_info['pay_fee'] = pay_fee($payment_id, $order['surplus_amount'], 0);
			$order['order_amount'] = $order['surplus_amount'] + $payment_info['pay_fee'];
			$order_amount = $this->db->getOne('SELECT order_amount FROM {pre}pay_log WHERE log_id = \'' . $order['log_id'] . '\'');
			$this->db->getOne('SELECT COUNT(*) FROM {pre}order_goods WHERE order_id=\'' . $order['order_id'] . '\'AND is_real = 1');

			if ($order_amount != $order['order_amount']) {
				$this->db->query('UPDATE {pre}pay_log SET order_amount = \'' . $order['order_amount'] . '\' WHERE log_id = \'' . $order['log_id'] . '\'');
			}

			if (!file_exists(ADDONS_PATH . 'payment/' . $payment_info['pay_code'] . '.php')) {
				unset($payment_info['pay_code']);
			}
			else {
				include_once ADDONS_PATH . 'payment/' . $payment_info['pay_code'] . '.php';
				$pay_obj = new $payment_info['pay_code']();
				$payment_info['pay_button'] = $pay_obj->get_code($order, $payment);
			}
		}
	}

	public function actionCardList()
	{
		if (IS_AJAX) {
			$id = I('id');

			if (empty($id)) {
				exit();
			}

			$this->model->table('user_bank')->where(array('id' => $id))->delete();
			exit();
		}

		$card_list = get_card_list($this->user_id);
		$this->assign('card_list', $card_list);
		$this->assign('page_title', L('account_card_list'));
		$this->display();
	}

	public function actionAddCard()
	{
		if (IS_POST) {
			$bank_card = I('bank_card', '');
			$pre = '/^\\d*$/';

			if (!preg_match($pre, $bank_card)) {
				show_message('请输入正确的卡号');
			}

			$bank_region = I('bank_region', '');
			$bank_name = I('bank_name', '');
			$bank_user_name = I('bank_user_name', '');
			$user_id = $this->user_id;

			if ($this->user_id < 0) {
				show_message('请重新登录');
			}

			$sql = "INSERT INTO {pre}user_bank (bank_name,bank_region,bank_card,bank_user_name,user_id)\r\n                    value('" . $bank_name . '\',\'' . $bank_region . '\',' . $bank_card . ',\'' . $bank_user_name . '\',' . $user_id . ')';

			if ($this->db->query($sql)) {
				show_message(L('account_add_success'), L('account_back_list'), url('card_list'), 'success');
			}
			else {
				show_message(L('account_add_error'), L('account_add_continue'), url('add_card'), 'fail');
			}
		}

		$this->assign('page_title', L('account_add_card'));
		$this->display();
	}

	public function actionValueCard()
	{
		if (IS_AJAX) {
			$this->size = 4;
			$page = I('page', 1, 'intval');
			$bind_vc = get_user_bind_vc_list($this->user_id, $page, 0, '', 1, $this->size);
			exit(json_encode(array('list' => $bind_vc, 'totalPage' => $bind_vc['totalPage'])));
		}

		$this->assign('page_title', L('vc_list'));
		$this->display();
	}

	public function actionValueCardInfo()
	{
		$vid = I('vid', '', 'intval');
		$info = value_cart_info($vid);

		if ($info['user_id'] != $this->user_id) {
			ecs_header('Location: ' . url('user/account/value_card'));
			exit();
		}

		if (IS_AJAX) {
			$this->size = 5;
			$page = I('page', 1, 'intval');
			$value_card_info = value_card_use_info($vid, $page, $this->size);
			exit(json_encode(array('list' => $value_card_info, 'totalPage' => $value_card_info['totalPage'])));
		}

		if ($info['is_rec'] == 1) {
			$pay_url = url('user/account/pay_value_card', array('vid' => $vid));
			$this->assign('pay_url', $pay_url);
		}

		$this->assign('page_title', L('vc_info'));
		$this->assign('vid', $vid);
		$this->display();
	}

	public function actionAddValueCard()
	{
		if (IS_POST) {
			$value_card_sn = trim(I('post.value_card_sn'));
			$password = compile_str(I('post.password'));

			if (0 < gd_version()) {
				if (empty($_POST['captcha'])) {
					exit(json_encode(array('status' => 'n', 'info' => L('invalid_captcha'))));
				}

				$validator = new \Think\Verify();

				if (!$validator->check($_POST['captcha'])) {
					exit(json_encode(array('status' => 'n', 'info' => L('invalid_captcha'))));
				}
			}

			$result = add_value_card($this->user_id, $value_card_sn, $password);

			if ($result == 1) {
				exit(json_encode(array('status' => 'n', 'info' => L('vc_use_expire'))));
			}

			if ($result == 2) {
				exit(json_encode(array('status' => 'n', 'info' => L('vc_is_used'))));
			}

			if ($result == 3) {
				exit(json_encode(array('status' => 'n', 'info' => L('vc_is_used_by_other'))));
			}

			if ($result == 4) {
				exit(json_encode(array('status' => 'n', 'info' => L('vc_not_exist'))));
			}

			if ($result == 5) {
				exit(json_encode(array('status' => 'n', 'info' => L('vc_limit_expire'))));
			}

			if ($result == 0) {
				exit(json_encode(array('status' => 'y', 'info' => L('add_value_card_sucess'), 'url' => url('user/account/value_card'))));
			}
		}

		$this->assign('page_title', L('add_vc'));
		$this->display();
	}

	public function actionPayValueCard()
	{
		$vid = I('vid', '', 'intval');

		if (empty($vid)) {
			exit(json_encode(array('status' => 'y', 'url' => url('user/account/value_card'))));
		}

		if (IS_POST) {
			$pay_card_sn = trim(I('post.pay_card_sn'));
			$password = compile_str(I('post.password'));
			$vid = I('post.vid');

			if (0 < gd_version()) {
				if (empty($_POST['captcha'])) {
					exit(json_encode(array('status' => 'n', 'info' => L('invalid_captcha'))));
				}

				$validator = new \Think\Verify();

				if (!$validator->check($_POST['captcha'])) {
					exit(json_encode(array('status' => 'n', 'info' => L('invalid_captcha'))));
				}
			}

			$result = use_pay_card($this->user_id, $vid, $pay_card_sn, $password);

			if ($result == 0) {
				exit(json_encode(array('status' => 'y', 'info' => L('use_pay_card_sucess'), 'url' => url('user/account/value_card_info', array('vid' => $vid)))));
			}

			if ($result == 1) {
				exit(json_encode(array('status' => 'n', 'info' => L('pc_not_exist'))));
			}

			if ($result == 2) {
				exit(json_encode(array('status' => 'n', 'info' => L('pc_is_used'))));
			}

			if ($result == 3) {
				exit(json_encode(array('status' => 'n', 'info' => L('vc_use_expire'))));
			}
		}

		$this->assign('vid', $vid);
		$this->assign('page_title', L('pay_vc'));
		$this->display();
	}
}

?>
