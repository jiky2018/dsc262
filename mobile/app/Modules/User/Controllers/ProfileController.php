<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\User\Controllers;

class ProfileController extends \App\Modules\Base\Controllers\FrontendController
{
	public $user_id;
	public $email;
	public $mobile;
	public $sex;

	public function __construct()
	{
		parent::__construct();
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		$file = array('passport', 'clips');
		$this->load_helper($file);
		$this->user_id = $_SESSION['user_id'];
		$this->actionchecklogin();
		$this->assign('lang', array_change_key_case(L()));
	}

	public function actionIndex()
	{
		$this->parameter();
		$sql = 'SELECT user_id,user_name,sex FROM {pre}users WHERE user_id = ' . $this->user_id;
		$user_info = $this->db->getRow($sql);
		$ecjiaBrowse = 0;
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);

		if (strpos($agent, 'ecjia') !== false) {
			$ecjiaBrowse = 1;
		}

		$user_real = dao('users_real')->where(array('user_id' => $this->user_id, 'user_type' => 0))->count();
		$this->assign('user_real', $user_real);
		$this->assign('user_info', $user_info);
		$this->assign('user_name', $user_info['user_name']);
		$this->assign('user_sex', $user_info['sex']);
		$this->assign('page_title', L('profile'));
		$this->assign('in_ecjia', $ecjiaBrowse);
		$this->display();
	}

	public function actionChangeHeader()
	{
		$result = $this->upload('data/images_user', false, 20, array(C('shop.thumb_width'), C('shop.thumb_height')));
		$imagePath = '';

		if ($result['error'] <= 0) {
			$imagePath = get_image_path($result['url']['img']['url']);
			$sql = 'UPDATE {pre}users SET user_picture = \'' . $imagePath . '\' WHERE user_id = ' . $this->user_id;
			$update = $this->db->query($sql);

			if (!$update) {
				$data = array('error' => 0, 'msg' => '头像替换失败');
			}
			else {
				$sql = 'SELECT user_picture FROM {pre}users WHERE user_id = ' . $this->user_id;
				$users = $this->db->getRow($sql);
				$data = array('error' => 0, 'msg' => '头像替换成功', 'path' => $imagePath);
			}
		}
		else {
			$data = array('error' => 1, 'msg' => '头像替换失败');
		}

		$this->ajaxReturn($data);
	}

	public function actionEditProfile()
	{
		$this->parameter();

		if (IS_POST) {
			$data = array();
			$sex = I('sex');
			$nick_name = I('nick_name');
			$birthday = I('birthday');

			if ($sex) {
				$data = array('sex' => $sex);
			}

			if ($nick_name) {
				$data = array('nick_name' => $nick_name);
			}

			if ($birthday) {
				$data = array('birthday' => $birthday);
			}

			dao('users')->data($data)->where(array('user_id' => $this->user_id))->save();
			$data['sex'] = $data['sex'] == 1 ? L('male') : L('female');
			exit(json_encode($data));
		}
	}

	public function actionUserEditMobile()
	{
		$this->parameter();
		$sql = 'SELECT user_id,user_name,mobile_phone FROM {pre}users WHERE user_id = ' . $this->user_id;
		$user_info = $this->db->getRow($sql);

		if (IS_POST) {
			$mobile = I('mobile');
			$sms_code = I('sms_code');

			if (empty($mobile)) {
				show_message(L('msg_input_mobile'), '返回上一页', '', 'error');
			}

			if (is_mobile($mobile) == false) {
				show_message(L('msg_mobile_format_error'), '返回上一页', '', 'error');
			}

			if (C('shop.sms_signin') == 1) {
				if ($mobile != $_SESSION['sms_mobile']) {
					show_message('手机号不正确', '返回上一页', '', 'error');
				}

				if ($sms_code != $_SESSION['sms_mobile_code']) {
					show_message(L('msg_auth_code_error'), '返回上一页', '', 'error');
				}
			}

			$sql = 'SELECT user_id FROM {pre}users WHERE mobile_phone=\'' . $mobile . '\'AND user_id !=' . $_SESSION['user_id'];
			$mobile_phone = $this->db->getOne($sql);
			$sql = 'SELECT user_id FROM {pre}users WHERE user_name=\'' . $mobile . '\'AND user_id !=' . $_SESSION['user_id'];
			$user_name = $this->db->getOne($sql);
			if (!empty($mobile_phone) || !empty($user_name)) {
				show_message(L('msg_mobile_exist'), '返回上一页', '', 'error');
			}

			if (!empty($user_info)) {
				$sql = 'UPDATE {pre}users SET mobile_phone = \'' . $mobile . '\' WHERE user_id = \'' . $this->user_id . '\'';
				$this->db->query($sql);
				unset($_SESSION['sms_mobile']);
				unset($_SESSION['sms_mobile_code']);
				show_message('验证成功', '', url('user/profile/accountsafe'), 'success');
			}
		}

		$this->assign('mobile', $user_info['mobile_phone']);
		$this->assign('sms_signin', C('shop.sms_signin'));
		$this->assign('page_title', L('edit_mobile'));
		$this->display();
	}

	public function actionUserEditEmail()
	{
		$this->parameter();

		if (IS_POST) {
			$email = I('email');
			$type = I('type', '', array('htmlspecialchars', 'trim'));

			if (empty($email)) {
				show_message('邮箱不能为空', '返回上一页', '', 'error');
			}

			if (is_email($email) == false) {
				show_message('邮箱格式不正确，请重新填写', '返回上一页', '', 'error');
			}

			if (C('shop.sms_signin') == 1 && $type == 'phone') {
				$sms_code = I('sms_code');
				$mobile = dao('users')->where(array('user_id' => $this->user_id))->getField('mobile_phone');

				if (empty($mobile)) {
					show_message('请先填写并验证手机号', '返回上一页', url('user/profile/user_edit_mobile'), 'error');
				}

				if ($mobile != $_SESSION['sms_mobile']) {
					show_message('手机号不正确', '返回上一页', '', 'error');
				}

				if ($sms_code != $_SESSION['sms_mobile_code']) {
					show_message(L('msg_auth_code_error'), '返回上一页', '', 'error');
				}

				unset($_SESSION['sms_mobile']);
				unset($_SESSION['sms_mobile_code']);
			}
			else {
				$sms_email_code = I('sms_email_code');

				if (empty($sms_email_code)) {
					show_message('邮箱验证码不能为空', '返回上一页', '', 'error');
				}

				if ($email != $_SESSION['sms_email']) {
					show_message('发送邮箱不正确', '返回上一页', '', 'error');
				}

				if ($sms_email_code != $_SESSION['sms_email_code']) {
					show_message('邮箱验证码填写不正确', '返回上一页', '', 'error');
				}

				unset($_SESSION['sms_email']);
				unset($_SESSION['sms_email_code']);
			}

			$sql = 'SELECT user_id FROM {pre}users WHERE email = \'' . $email . '\' AND user_id !=' . $this->user_id;
			$other_email = $this->db->getOne($sql);
			$sql = 'SELECT user_id FROM {pre}users WHERE user_name = \'' . $email . '\' AND user_id !=' . $this->user_id;
			$user_email = $this->db->getOne($sql);
			if (!empty($user_email) || !empty($other_email)) {
				show_message(L('msg_email_registered'), '返回上一页', '', 'error');
			}

			if (!empty($email)) {
				$data = array('email' => $email, 'is_validated' => 1);
				dao('users')->data($data)->where(array('user_id' => $this->user_id))->save();
				show_message('修改成功', '', url('user/profile/accountsafe'), 'success');
			}
		}

		$type = I('type', 'email', array('htmlspecialchars', 'trim'));
		$sql = 'SELECT user_id, email, mobile_phone FROM {pre}users WHERE user_id = ' . $this->user_id;
		$user_info = $this->db->getRow($sql);
		$_SESSION['hash_code'] = md5($user_info['email'] . rand(1000, 9999));

		if ($type == 'phone') {
			$this->assign('change_email', 1);
		}

		$this->assign('type', $type);
		$this->assign('hash_code', $_SESSION['hash_code']);
		$this->assign('user_info', $user_info);
		$this->assign('sms_signin', C('shop.sms_signin'));
		$this->assign('page_title', L('edit_email'));
		$this->display();
	}

	public function actionSendSms()
	{
		if (IS_POST) {
			$result = array('error' => 0, 'content' => '');
			$type = I('post.type');
			$hash_code = I('post.hash_code');

			if ($hash_code != $_SESSION['hash_code']) {
				$result['error'] = 1;
				$result['content'] = '发送有误';
				exit(json_encode($result));
			}

			if ($type == 'email') {
				$email = I('post.email');
				$code = rand(1000, 9999);
				$sql = 'SELECT user_id, user_name, email FROM {pre}users WHERE user_id = ' . $this->user_id;
				$user_info = $this->db->getRow($sql);

				if (!empty($user_info)) {
					if (is_email($email) == false) {
						$result['error'] = 1;
						$result['content'] = '邮箱格式错误';
						exit(json_encode($result));
					}

					if (!C('shop.smtp_user')) {
						$result['error'] = 1;
						$result['content'] = '邮件服务器未配置';
						exit(json_encode($result));
					}

					if (send_pwd_email($user_info['user_id'], $user_info['user_name'], $email, $code)) {
						$_SESSION['sms_email'] = $email;
						$_SESSION['sms_email_code'] = $code;
						$result['error'] = 0;
						$result['content'] = L('send_success');
						exit(json_encode($result));
					}
					else {
						$result['error'] = 1;
						$result['content'] = L('fail_send_password');
						exit(json_encode($result));
					}
				}
				else {
					$result['error'] = 1;
					$result['content'] = '用户不存在';
					exit(json_encode($result));
				}
			}
			else if ($type == 'phone') {
				$mobile = I('post.mobile');
				$code = rand(1000, 9999);

				if (is_mobile($mobile) == false) {
					$result['error'] = 1;
					$result['content'] = '手机号码格式错误';
					exit(json_encode($result));
				}

				$message = array('code' => $code);

				if (send_sms($mobile, 'sms_code', $message) === true) {
					$_SESSION['sms_mobile'] = $mobile;
					$_SESSION['sms_mobile_code'] = $code;
					$result['error'] = 0;
					$result['content'] = '短信发送成功';
					exit(json_encode($result));
				}
				else {
					$result['error'] = 1;
					$result['content'] = '短信发送失败';
					exit(json_encode($result));
				}
			}
			else {
				$result['error'] = 1;
				$result['content'] = '操作有误';
				exit(json_encode($result));
			}
		}
	}

	private function parameter()
	{
		$this->user_id = $_SESSION['user_id'];

		if (empty($this->user_id)) {
			ecs_header("Location: ./\n");
		}

		$this->mobile = I('mobile');
		$this->sex = I('sex');
		$this->email = I('email');
		$this->postbox = I('postbox');
		$this->assign('info', get_user_default($this->user_id));
	}

	public function actionchecklogin()
	{
		if (!$this->user_id) {
			$back_act = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];
			$this->redirect('user/login/index', array('back_act' => urlencode($back_act)));
		}
	}

	public function actionAccountSafe()
	{
		$this->parameter();
		$is_validated = dao('users')->where(array('user_id' => $this->user_id))->getField('is_validated');
		$this->assign('is_validated', $is_validated);
		$users_paypwd = dao('users_paypwd')->where(array('user_id' => $this->user_id))->count();
		$this->assign('users_paypwd', $users_paypwd);
		$is_connect_user = is_connect_user($this->user_id);
		$this->assign('is_connect_user', $is_connect_user);
		$this->assign('page_title', '账户安全');
		$this->display();
	}

	public function actionUserEditPaypwd()
	{
		if (IS_POST) {
			$paypwd_id = I('paypwd_id', 0, 'intval');
			$pay_paypwd = I('pay_paypwd', '', array('htmlspecialchars', 'trim'));
			$pay_online = I('pay_online', 0, 'intval');
			$user_surplus = I('user_surplus', 0, 'intval');
			$user_point = I('user_point', 0, 'intval');
			$baitiao = I('baitiao', 0, 'intval');
			$gift_card = I('gift_card', 0, 'intval');
			$type = I('type', '', array('htmlspecialchars', 'trim'));
			if (C('shop.sms_signin') == 1 && $type == 'phone') {
				$sms_code = I('sms_code');
				$mobile = dao('users')->where(array('user_id' => $this->user_id))->getField('mobile_phone');

				if (empty($mobile)) {
					show_message('请先填写并验证手机号', '返回上一页', url('user/profile/user_edit_mobile'), 'error');
				}

				if ($mobile != $_SESSION['sms_mobile']) {
					show_message('手机号不正确', '返回上一页', '', 'error');
				}

				if ($sms_code != $_SESSION['sms_mobile_code']) {
					show_message(L('msg_auth_code_error'), '返回上一页', '', 'error');
				}

				unset($_SESSION['sms_mobile']);
				unset($_SESSION['sms_mobile_code']);
			}
			else {
				$sms_email_code = I('sms_email_code');
				$users = dao('users')->field('email, is_validated')->where(array('user_id' => $this->user_id))->find();

				if (empty($users['email'])) {
					show_message('请先填写并验证邮箱', '返回上一页', url('user/profile/user_edit_email'), 'error');
				}

				if ($users['email'] != $_SESSION['sms_email']) {
					show_message('发送邮箱不正确', '返回上一页', '', 'error');
				}

				if ($sms_email_code != $_SESSION['sms_email_code']) {
					show_message('邮箱验证码填写不正确', '返回上一页', '', 'error');
				}

				unset($_SESSION['sms_email']);
				unset($_SESSION['sms_email_code']);
			}

			if (empty($pay_paypwd)) {
				show_message('支付密码不能为空', '返回上一页', '', 'error');
			}

			$data = array('user_id' => $this->user_id, 'pay_online' => $pay_online, 'user_surplus' => $user_surplus, 'user_point' => $user_point, 'baitiao' => $baitiao, 'gift_card' => $gift_card);
			$ec_salt = rand(1, 9999);
			$new_password = md5(md5($pay_paypwd) . $ec_salt);
			$data['pay_password'] = $new_password;
			$data['ec_salt'] = $ec_salt;

			if ($paypwd_id) {
				$old_pay_paypwd = I('old_pay_paypwd', '', array('htmlspecialchars', 'trim'));
				$res = dao('users_paypwd')->field('pay_password,ec_salt')->where(array('paypwd_id' => $paypwd_id))->find();
				$new_password = md5(md5($old_pay_paypwd) . $res['ec_salt']);

				if ($new_password != $res['pay_password']) {
					show_message('原支付密码不正确', '返回上一页', '', 'error');
				}

				dao('users_paypwd')->data($data)->where(array('paypwd_id' => $paypwd_id))->save();
				show_message('修改成功', '', url('user/profile/accountsafe'), 'success');
			}
			else {
				dao('users_paypwd')->data($data)->add();
				show_message('启用成功', '', url('user/profile/accountsafe'), 'success');
			}
		}

		$users_paypwd = dao('users_paypwd')->field('paypwd_id, pay_online, user_surplus,user_point')->where(array('user_id' => $this->user_id))->find();

		if (!empty($users_paypwd)) {
			$page_title = '修改支付密码';
		}
		else {
			$page_title = '启用支付密码';
		}

		$users_paypwd['user_surplus'] = !empty($users_paypwd['user_surplus']) ? $users_paypwd['user_surplus'] : 1;
		$this->assign('users_paypwd', $users_paypwd);
		$type = I('type', 'email', array('htmlspecialchars', 'trim'));

		if ($type == 'phone') {
			$this->assign('change_email', 1);
		}

		$this->assign('type', $type);
		$user_info = dao('users')->field('mobile_phone, email, is_validated')->where(array('user_id' => $this->user_id))->find();
		$this->assign('user_info', $user_info);
		$_SESSION['hash_code'] = md5($user_info['email'] . rand(1000, 9999));
		$this->assign('hash_code', $_SESSION['hash_code']);
		$this->assign('sms_signin', C('shop.sms_signin'));
		$this->assign('page_title', $page_title);
		$this->display();
	}

	public function actionRealname()
	{
		if (IS_POST) {
			$step = I('step', '', array('htmlspecialchars', 'trim'));
			$real_id = I('real_id', 0, 'intval');
			$real_user = I('post.data', '', array('htmlspecialchars', 'trim'));
			$real_user['user_id'] = $this->user_id;
			$real_user['bank_mobile'] = I('mobile_phone', '');

			if (empty($real_user['real_name'])) {
				exit(json_encode(array('status' => 1, 'msg' => '真实姓名不可为空')));
			}

			if (empty($real_user['self_num'])) {
				exit(json_encode(array('status' => 1, 'msg' => '身份证号不可为空')));
			}

			if (empty($real_user['bank_name'])) {
				exit(json_encode(array('status' => 1, 'msg' => '银行不可为空')));
			}

			if (empty($real_user['bank_card'])) {
				exit(json_encode(array('status' => 1, 'msg' => '银行卡号不可为空')));
			}

			if (empty($real_user['bank_mobile'])) {
				exit(json_encode(array('status' => 1, 'msg' => '手机号不可为空')));
			}

			$form = new \App\Extensions\Form();

			if (!$form->isMobile($real_user['bank_mobile'], 1)) {
				exit(json_encode(array('status' => 1, 'msg' => '手机号码格式不正确')));
			}

			if (strpos($real_user['self_num'], '*') == false) {
				if (!$form->isCreditNo($real_user['self_num'], 1)) {
					exit(json_encode(array('status' => 1, 'msg' => '身份证号码格式不正确')));
				}
			}

			if (strpos($real_user['bank_card'], '*') == false) {
				if (is_numeric($real_user['bank_card']) == false) {
					exit(json_encode(array('status' => 1, 'msg' => '银行卡号格式不正确')));
				}
			}

			$mobile_code = I('mobile_code', '');

			if (!empty($real_user['bank_mobile'])) {
				if (!empty($mobile_code)) {
					if ($real_user['bank_mobile'] != $_SESSION['sms_mobile'] || $mobile_code != $_SESSION['sms_mobile_code']) {
						exit(json_encode(array('status' => 1, 'msg' => '手机号或短信验证码错误')));
					}
				}
				else {
					exit(json_encode(array('status' => 1, 'msg' => '短信验证码不可为空')));
				}
			}

			$count_user = dao('users_real')->where(array('user_id' => $this->user_id, 'user_type' => 0))->count();
			if ($count_user == 0 && $step == 'first') {
				if (empty($_FILES['front_of_id_card']['name']) || empty($_FILES['reverse_of_id_card']['name'])) {
					exit(json_encode(array('status' => 1, 'msg' => '身份证正反面不可为空')));
				}
			}

			if (!empty($_FILES['front_of_id_card']['name']) || !empty($_FILES['reverse_of_id_card']['name'])) {
				$result = $this->upload('data/idcard', false, 20, array(C('shop.thumb_width'), C('shop.thumb_height')));

				if (0 < $result['error']) {
					exit(json_encode(array('status' => 1, 'msg' => $result['message'])));
				}

				if (!empty($_FILES['front_of_id_card']['name'])) {
					$real_user['front_of_id_card'] = $result['url']['front_of_id_card']['url'];
				}

				if (!empty($_FILES['reverse_of_id_card']['name'])) {
					$real_user['reverse_of_id_card'] = $result['url']['reverse_of_id_card']['url'];
				}
			}

			$row = dao('users_real')->where(array('user_id' => $this->user_id, 'user_type' => 0))->find();
			if ($count_user == 1 && $step == 'edit') {
				$file = dirname(ROOT_PATH);
				if ($real_user['front_of_id_card'] != '' && $row['front_of_id_card']) {
					@unlink($file . '/' . $row['front_of_id_card']);
				}

				if ($real_user['reverse_of_id_card'] != '' && $row['reverse_of_id_card']) {
					@unlink($file . '/' . $row['reverse_of_id_card']);
				}

				$real_user['review_status'] = 0;
			}

			if ($real_id && $count_user == 1) {
				if (strpos($real_user['self_num'], '*') == true) {
					unset($real_user['self_num']);
				}

				if (strpos($real_user['bank_card'], '*') == true) {
					unset($real_user['bank_card']);
				}

				dao('users_real')->data($real_user)->where(array('real_id' => $real_id, 'user_id' => $this->user_id))->save();
				exit(json_encode(array('status' => 0, 'msg' => '修改成功')));
			}
			else {
				if ($count_user == 0 && $step == 'first') {
					$real_user['add_time'] = gmtime();
					dao('users_real')->data($real_user)->where(array('user_id' => $this->user_id))->add();
					exit(json_encode(array('status' => 0, 'msg' => '实名认证申请成功，请等待管理员审核！')));
				}
				else {
					exit(json_encode(array('status' => 1, 'msg' => '您已经实名认证过了，不需要重复认证')));
				}
			}
		}

		$step = I('step', '', array('htmlspecialchars', 'trim'));
		$count_user = dao('users_real')->where(array('user_id' => $this->user_id, 'user_type' => 0))->count();
		if ($count_user == 1 && $step != 'edit') {
			$this->redirect('user/profile/realnameok');
		}

		$step = $count_user == 0 ? 'first' : 'edit';
		$real_user = dao('users_real')->where(array('user_id' => $this->user_id, 'user_type' => 0))->find();

		if (!empty($real_user)) {
			if (empty($real_user['bank_mobile'])) {
				$mobile_phone = dao('users')->where(array('user_id' => $this->user_id))->getField('mobile_phone');

				if ($mobile_phone) {
					$real_user['bank_mobile'] = $mobile_phone;
				}
			}

			$real_user['self_num'] = string_to_star($real_user['self_num'], 4);
			$real_user['bank_card'] = string_to_star($real_user['bank_card'], 4);
			$real_user['front_of_id_card'] = get_image_path($real_user['front_of_id_card']);
			$real_user['reverse_of_id_card'] = get_image_path($real_user['reverse_of_id_card']);
		}

		$this->assign('real_user', $real_user);
		$this->assign('step', $step);
		$this->assign('page_title', '实名认证');
		$this->display();
	}

	public function actionRealnameSend()
	{
		if (IS_AJAX) {
			$_SESSION['sms_mobile'] = I('mobile');
			$_SESSION['sms_mobile_code'] = rand(1000, 9999);
			$form = new \App\Extensions\Form();

			if (!$form->isMobile($_SESSION['sms_mobile'], 1)) {
				$result['error'] = 1;
				$result['content'] = '手机号码格式不正确';
				exit(json_encode($result));
			}

			$message = array('code' => $_SESSION['sms_mobile_code']);
			$send_result = send_sms($_SESSION['sms_mobile'], 'sms_code', $message);

			if ($send_result === true) {
				$result['error'] = 0;
				$result['content'] = '发送短信成功';
			}
			else {
				$result['error'] = 1;
				$result['content'] = '发送短信失败';
			}

			exit(json_encode($result));
		}
	}

	public function actionRealnameOk()
	{
		$real_user = dao('users_real')->where(array('user_id' => $this->user_id, 'user_type' => 0))->find();

		if (!$real_user) {
			$this->redirect('user/profile/realname');
		}

		$real_user['validate_time'] = local_date('Y-m-d H:i:s', $real_user['add_time']);
		$real_user['self_num'] = string_to_star($real_user['self_num'], 4);
		$real_user['bank_card'] = string_to_star($real_user['bank_card'], 4);
		$real_user['front_of_id_card'] = get_image_path($real_user['front_of_id_card']);
		$real_user['reverse_of_id_card'] = get_image_path($real_user['reverse_of_id_card']);
		$this->assign('real_user', $real_user);
		$this->assign('page_title', '实名认证信息');
		$this->display();
	}

	public function actionAccountBind()
	{
		if (IS_AJAX) {
			$json_result = array('error' => 0, 'msg' => '', 'url' => '');
			$id = I('id', 0, 'intval');

			if ($id) {
				$sql = 'SELECT cu.user_id, cu.open_id, u.mobile_phone FROM {pre}connect_user cu, {pre}users u WHERE u.user_id = cu.user_id AND u.user_id = \'' . $this->user_id . '\' and cu.id = \'' . $id . '\' ';
				$users = $this->db->getRow($sql);

				if (!empty($users)) {
					if (!empty($users['mobile_phone'])) {
						$connect_where = array('user_id' => $this->user_id, 'open_id' => $users['open_id']);
						dao('connect_user')->where($connect_where)->delete();
						$auth_where = array('user_id' => $this->user_id, 'identifier' => $users['open_id']);
						dao('users_auth')->where($auth_where)->delete();
						unset($_SESSION['openid']);
						unset($_SESSION['unionid']);
						$json_result = array('error' => 0, 'msg' => '解绑成功');
						exit(json_encode($json_result));
					}
					else {
						$json_result = array('error' => 1, 'msg' => '请先填写并验证手机号', 'url' => url('user/profile/user_edit_mobile'));
						exit(json_encode($json_result));
					}
				}
				else {
					$json_result = array('error' => 1, 'msg' => '帐号未绑定', 'url' => url('user/profile/account_safe'));
					exit(json_encode($json_result));
				}
			}
		}

		$sql = 'SELECT cu.id, cu.user_id, cu.connect_code, cu.create_at FROM {pre}connect_user cu, {pre}users u WHERE u.user_id = cu.user_id AND u.user_id = \'' . $this->user_id . '\' ';
		$connect_user = $GLOBALS['db']->getAll($sql);
		$oauth_list = dao('touch_auth')->field('type, status')->order('sort asc, id asc')->select();
		$list = array();

		foreach ($oauth_list as $key => $vo) {
			$list[$vo['type']]['type'] = $vo['type'];
			$list[$vo['type']]['install'] = $vo['status'];
			if ($vo['type'] == 'wechat' && !is_wechat_browser()) {
				unset($list[$vo['type']]);
			}
		}

		foreach ($connect_user as $key => $value) {
			$type = substr($value['connect_code'], 4);
			$list[$type]['user_id'] = $value['user_id'];
			$list[$type]['id'] = $value['id'];
		}

		$back_url = __HOST__ . $_SERVER['REQUEST_URI'];
		$this->assign('back_url', $back_url);
		$this->assign('user_id', $this->user_id);
		$this->assign('list', $list);
		$this->assign('page_title', '授权管理');
		$this->display();
	}

	public function actionAccountRelation()
	{
		if (IS_POST) {
			$username = I('username', '', array('htmlspecialchars', 'trim'));
			$verify_code = I('verify');
			$password = I('password', '', array('htmlspecialchars', 'trim'));
			$form = new \App\Extensions\Form();
			$verify = new \Think\Verify();

			if (!$form->isEmpty($username, 1)) {
				$json_result = array('status' => 'n', 'info' => '用户名不能为空');
				exit(json_encode($json_result));
			}

			if (!$form->isEmpty($password, 1)) {
				$json_result = array('status' => 'n', 'info' => '密码不能为空');
				exit(json_encode($json_result));
			}

			if ($form->isMobile($username, 1)) {
				$condition['mobile_phone'] = $username;
				$condition['user_name'] = $username;
				$condition['_logic'] = 'OR';
				$username = dao('users')->where($condition)->getField('user_name');
			}

			if (!$verify->check($verify_code)) {
				$json_result = array('status' => 'n', 'info' => '图片验证码不正确');
				exit(json_encode($json_result));
			}

			$bind_user_id = $this->users->check_user($username, $password);
			if (0 < $bind_user_id && !empty($_SESSION['unionid'])) {
				$connect_user_id = dao('connect_user')->where(array('user_id' => $bind_user_id, 'connect_code' => 'sns_wechat'))->count();
				if ($connect_user_id == 0 && $bind_user_id != $this->user_id) {
					$res = dao('connect_user')->data(array('user_id' => $bind_user_id))->where(array('user_id' => $this->user_id, 'connect_code' => 'sns_wechat'))->save();
					if (!empty($username) && $res) {
						unset($_SESSION['user_id']);
						unset($_SESSION['user_name']);
						$this->users->set_session($username);
						$this->users->set_cookie($username);
						update_user_info();
						recalculate_price();
					}

					$json_result = array('status' => 'y', 'info' => '已关联账号' . $username, 'url' => url('user/index/index'));
					exit(json_encode($json_result));
				}
				else {
					$json_result = array('status' => 'n', 'info' => '该账号已被关联！');
					exit(json_encode($json_result));
				}
			}
			else {
				$json_result = array('status' => 'n', 'info' => '账号不存在或密码错误，请重新输入！');
				exit(json_encode($json_result));
			}
		}

		if (!empty($_SESSION['unionid'])) {
			$wechat_id = dao('wechat')->where(array('default_wx' => 1, 'status' => 1))->getField('id');
			$main_user_id = dao('wechat_user')->where(array('unionid' => $_SESSION['unionid'], 'wechat_id' => $wechat_id))->getField('ect_uid');
			$main_user_info = get_users($main_user_id);

			if (!empty($main_user_info)) {
				$main_user_info['user_name'] = $main_user_info['user_name'] . '(系统默认分配账号)';
				$main_user_info['user_picture'] = dao('wechat_user')->where(array('unionid' => $_SESSION['unionid'], 'wechat_id' => $wechat_id))->getField('headimgurl');
			}

			$this->assign('main_user_info', $main_user_info);
			$relation_user_info = get_connect_user($_SESSION['unionid']);
			$relation_users = get_users($relation_user_info['user_id']);

			if (!empty($relation_users)) {
				$relation_user_info['user_picture'] = $relation_users['user_picture'];
				$relation_user_info['mobile_phone'] = $relation_users['mobile_phone'];
			}

			$this->assign('relation_user_info', $relation_user_info);
			$now_user_info = get_users($this->user_id);
			if (!empty($main_user_info) && !empty($now_user_info) && $main_user_info['user_id'] == $now_user_info['user_id']) {
				$now_user_info['user_name'] = $main_user_info['user_name'];
				$now_user_info['user_picture'] = $main_user_info['user_picture'];
			}

			$this->assign('now_user_info', $now_user_info);
			if (!empty($main_user_info) && !empty($relation_user_info) && $main_user_info['user_id'] == $relation_user_info['user_id']) {
				$is_relation = 0;
			}
			else {
				if (!empty($main_user_info) && !empty($relation_user_info) && $main_user_info['user_id'] != $relation_user_info['user_id']) {
					$is_relation = 1;
				}
			}

			$this->assign('is_relation', $is_relation);
			if (!empty($main_user_info) && !empty($now_user_info) && $main_user_info['user_id'] == $now_user_info['user_id']) {
				$is_remove_relation = 1;
			}
			else {
				$is_remove_relation = 0;
			}

			$this->assign('is_remove_relation', $is_remove_relation);
			if (!empty($main_user_info) && !empty($relation_user_info) && $main_user_info['user_id'] != $relation_user_info['user_id']) {
				$is_change_login = 1;
			}
			else {
				$is_change_login = 0;
			}

			if ($is_change_login == 1) {
				if ($now_user_info && $relation_user_info && $now_user_info['user_id'] != $relation_user_info['user_id']) {
					$this->assign('change_user_info', $relation_user_info);
				}
				else {
					$this->assign('change_user_info', $main_user_info);
				}
			}

			$this->assign('is_change_login', $is_change_login);
		}
		else {
			$back_url = __HOST__ . $_SERVER['REQUEST_URI'];
			$this->redirect('oauth/index/index', array('type' => 'wechat', 'back_url' => urlencode($back_url)));
		}

		$this->assign('page_title', '账号关联');
		$this->display();
	}

	public function actionRemoveRelation()
	{
		if (IS_AJAX) {
			$json_result = array('error' => 0, 'msg' => '', 'url' => '');
			$relation_user_id = I('relation_user_id', 0, 'intval');

			if (!empty($relation_user_id)) {
				if (1 < $_SESSION['relation_times']) {
					$json_result = array('error' => 1, 'msg' => '请不要频繁操作！每次登录只能解除关联一次');
					exit(json_encode($json_result));
				}

				$wechat_id = dao('wechat')->where(array('default_wx' => 1, 'status' => 1))->getField('id');
				$main_user_id = dao('wechat_user')->where(array('unionid' => $_SESSION['unionid'], 'wechat_id' => $wechat_id))->getField('ect_uid');
				$userinfo = dao('users')->field('user_name')->where(array('user_id' => $main_user_id))->find();

				if (!empty($userinfo)) {
					$data = array('user_id' => $main_user_id);
					dao('connect_user')->data($data)->where(array('user_id' => $relation_user_id, 'connect_code' => 'sns_wechat'))->save();
					unset($_SESSION['user_id']);
					unset($_SESSION['user_name']);
					$this->users->set_session($userinfo['user_name']);
					$this->users->set_cookie($userinfo['user_name']);
					update_user_info();
					recalculate_price();
					$_SESSION['relation_times']++;
					$json_result = array('error' => 0, 'msg' => '解除关联成功', 'url' => url('user/profile/index'));
					exit(json_encode($json_result));
				}
				else {
					$json_result = array('error' => 1, 'msg' => '账号不存在');
					exit(json_encode($json_result));
				}
			}

			$json_result = array('error' => 1, 'msg' => '错误');
			exit(json_encode($json_result));
		}
	}

	public function actionChangeLogin()
	{
		if (IS_AJAX) {
			$json_result = array('error' => 0, 'msg' => '', 'url' => '');
			$change_user_id = I('change_user_id', 0, 'intval');

			if (!empty($change_user_id)) {
				$userinfo = dao('users')->field('user_name')->where(array('user_id' => $change_user_id))->find();

				if (!empty($userinfo)) {
					unset($_SESSION['user_id']);
					unset($_SESSION['user_name']);
					$this->users->set_session($userinfo['user_name']);
					$this->users->set_cookie($userinfo['user_name']);
					update_user_info();
					recalculate_price();
					$json_result = array('error' => 0, 'msg' => '切换登录成功', 'url' => url('user/index/index'));
					exit(json_encode($json_result));
				}
				else {
					$json_result = array('error' => 1, 'msg' => '账号不存在');
					exit(json_encode($json_result));
				}
			}

			$json_result = array('error' => 1, 'msg' => '错误');
			exit(json_encode($json_result));
		}
	}
}

?>
