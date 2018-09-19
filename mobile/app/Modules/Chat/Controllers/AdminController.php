<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Chat\Controllers;

class AdminController extends \App\Modules\Base\Controllers\FrontendController
{
	protected $config = array();

	public function _initialize()
	{
		$this->config = load_config(ROOT_PATH . 'config/chat.php');
		$sessionList = array('addreply', 'removereply', 'insertuserreply');
		if (empty($_POST) || in_array(strtolower(ACTION_NAME), $sessionList)) {
			session('[start]');
		}
	}

	public function actionIndex()
	{
		if (is_mobile_browser() && IS_GET) {
			$this->redirect('chat/adminp/mobile');
		}

		$signInData = $this->userCheck();
		$admin = $signInData['admin'];
		$service = $signInData['service'];

		if (empty($signInData['service'])) {
			$this->redirect('login/index');
		}

		if ($service['chat_status'] == 1 && !session('kefu_id')) {
			$this->error('客服已登录', 'login/index');
		}

		$waitMessageArr = \App\Modules\Chat\Models\Kefu::getWait($admin['ru_id']);
		$waitMessage = $waitMessageArr['waitMessage'];
		$this->assign('total_wait', $waitMessageArr['total']);
		$this->assign('wait_message_list', json_encode($waitMessageArr['waitMessageDataList']));
		$messageList = \App\Modules\Chat\Models\Kefu::getChatLog($service);
		$this->assign('message_list', $messageList);
		$this->assign('message_list_json', json_encode($messageList));
		$reply = \App\Modules\Chat\Models\Kefu::getReply($service['id'], 1);
		$this->assign('reply', json_encode($reply));
		$reply = \App\Modules\Chat\Models\Kefu::getReply($service['id'], 2);
		$this->assign('take_reply', json_encode($reply));
		$reply = \App\Modules\Chat\Models\Kefu::getReply($service['id'], 3);
		$this->assign('leave_reply', json_encode($reply));

		if (empty($this->config['listen_route'])) {
			$listen_route = $this->getServerIp();
		}
		else {
			$listen_route = $this->config['listen_route'];
		}

		if (empty($this->config['port'])) {
			$this->error('socket端口号未配置');
		}

		$this->assign('mouse_img', __ROOT__ . '/public/assets/chat/images/mouse.png');
		$this->assign('root_path', rtrim(dirname(__ROOT__), '/'));
		$this->assign('listen_route', $listen_route);
		$this->assign('port', $this->config['port']);
		$this->assign('user_id', $service['id']);
		$this->assign('store_id', $admin['ru_id']);
		$this->assign('nick_name', $service['nick_name']);
		$this->assign('wait_message', $waitMessage);
		$this->assign('image_path', __ROOT__ . '/public/assets/chat/images/');
		$storeInfo = \App\Modules\Chat\Models\Kefu::getStoreInfo($admin['ru_id']);
		$this->assign('avatar', $storeInfo['logo_thumb']);
		$this->assign('user_name', $storeInfo['shop_name']);
		$this->assign('is_ssl', is_ssl());
		$serviceList = \App\Modules\Chat\Models\Kefu::getServiceList($admin['ru_id'], $service['id']);
		$this->assign('service_list', $serviceList);
		$_GET['id'] = $service['id'];
		$_GET['status'] = 1;
		$this->actionChangeLogin();
		$this->display('index');
	}

	private function userCheck()
	{
		$cookie = cookie('ECSCP');
		if (isset($cookie['kefu_id']) && !empty($cookie['kefu_id'])) {
			$service = \App\Modules\Chat\Models\Kefu::getServiceById($cookie['kefu_id']);
			$adminId = $service['user_id'];

			if (!empty($adminId)) {
				$admin = \App\Modules\Chat\Models\Kefu::getAdmin($adminId);
				$token = md5($admin['password'] . C('hash_code'));

				if ($token == $cookie['kefu_token']) {
					return array('admin' => $admin, 'service' => $service);
				}
			}
		}

		$kefuId = session('kefu_id');
		$adminId = session('kefu_admin_id');
		$admin = \App\Modules\Chat\Models\Kefu::getAdmin($adminId);
		$service = \App\Modules\Chat\Models\Kefu::getService($adminId);

		if ($service['id'] != $kefuId) {
			return false;
		}

		return array('admin' => $admin, 'service' => $service);
	}

	public function actionHistory()
	{
		$uid = I('uid', 0, 'intval');
		$tid = I('tid', 0, 'intval');
		$page = I('page', 0, 'intval');
		$keyword = I('keyword', '');
		$time = I('time', '');
		$data = \App\Modules\Chat\Models\Kefu::getHistory($uid, $tid, $keyword, $time, $page);
		$this->ajaxReturn($data);
	}

	public function actionSearchhistory()
	{
		$mid = I('mid', 0, 'intval');
		$data = \App\Modules\Chat\Models\Kefu::getSearchHistory($mid);
		$this->ajaxReturn($data);
	}

	public function actionChangeMessageStatus()
	{
		$serviceId = (int) $_SESSION['kefu_id'];
		$customId = I('id', 0, 'intval');

		if (empty($serviceId)) {
			$this->ajaxReturn(array('error' => 1, 'msg' => '没有客服'));
		}

		\App\Modules\Chat\Models\Kefu::changeMessageStatus($serviceId, $customId);
	}

	public function actionGetGoods()
	{
		$gid = empty($_POST['gid']) ? 0 : intval($_POST['gid']);

		if ($gid == 0) {
			$this->ajaxReturn(array('error' => 1, 'content' => 'invalid params'));
		}

		$data = \App\Modules\Chat\Models\Kefu::getGoods($gid);
		$this->ajaxReturn($data);
	}

	public function actionGetStore()
	{
		$sid = empty($_POST['sid']) ? 0 : intval($_POST['sid']);

		if ($sid == 0) {
			$this->ajaxReturn(array('error' => 1, 'content' => 'invalid params'));
		}

		$data = \App\Modules\Chat\Models\Kefu::getStoreInfo($sid);
		$this->ajaxReturn($data);
	}

	public function actionAddReply()
	{
		$content = I('content');
		$customerId = $_SESSION['kefu_id'];
		$customer = M('im_configure');
		$data['ser_id'] = $customerId;
		$data['type'] = 1;
		$data['content'] = addslashes($content);
		$data['is_on'] = 0;
		$id = $customer->add($data);
		$this->ajaxReturn(array('error' => 0, 'id' => $id));
	}

	public function actionRemoveReply()
	{
		$id = I('id', 0, 'intval');
		$customerId = $_SESSION['kefu_id'];
		$customer = M('im_configure');
		$customer->where('id=' . $id . ' and ser_id=' . $customerId)->delete();
		$this->ajaxReturn(array('error' => 0));
	}

	public function actionChangeStatus()
	{
		$status = I('status');
		$customerId = $_SESSION['kefu_id'];
		$customer = M('im_service');
		$data['chat_status'] = $status;
		$id = $customer->where('id=' . $customerId)->save($data);
		$this->ajaxReturn(array('error' => 0, 'id' => $id));
	}

	public function actionInsertUserReply()
	{
		$mid = I('mid', 0, 'intval');
		$content = I('content', '', array('htmlspecialchars', 'addslashes'));
		$customerId = $_SESSION['kefu_id'];
		$configure = M('im_configure');
		$res = $configure->where('id=' . $mid)->find();
		$data['ser_id'] = $customerId;
		$data['type'] = 2;
		$data['content'] = trim($content);

		if (!empty($res)) {
			$res = $configure->where('id=' . $mid)->save($data);
		}
		else {
			$mid = $configure->data($data)->add();
		}

		$this->ajaxReturn(array('error' => 0, 'mid' => $mid));
	}

	public function actionTakeUserReply()
	{
		$id = I('id', 0, 'intval');
		$status = I('status', 0, 'intval');

		if (empty($id)) {
			$this->ajaxReturn(array('error' => 1, 'msg' => '请先编辑接入回复'));
		}

		$configure = M('im_configure');
		$data['is_on'] = $status;
		$id = $configure->where('id=' . $id)->save($data);
		$this->ajaxReturn(array('error' => 0, 'id' => $id));
	}

	public function actionInsertUserLeaveReply()
	{
		$mid = I('mid', 0, 'intval');
		$content = I('content', '', array('htmlspecialchars', 'addslashes'));
		$customerId = $_SESSION['kefu_id'];
		$configure = M('im_configure');
		$res = $configure->where('id=' . $mid)->find();
		$data['ser_id'] = $customerId;
		$data['type'] = 3;
		$data['content'] = trim($content);

		if (!empty($res)) {
			$res = $configure->where('id=' . $mid)->save($data);
		}
		else {
			$mid = $configure->data($data)->add();
		}

		$this->ajaxReturn(array('error' => 0, 'mid' => $mid));
	}

	public function actionUserLeaveReply()
	{
		$id = I('id', 0, 'intval');
		$status = I('status', 0, 'intval');

		if (empty($id)) {
			$this->ajaxReturn(array('error' => 1, 'msg' => '请先编辑离开回复'));
		}

		$configure = M('im_configure');
		$data['is_on'] = $status;
		$id = $configure->where('id=' . $id)->save($data);
		$this->ajaxReturn(array('error' => 0, 'id' => $id));
	}

	public function actionDialogInfo()
	{
		$uid = I('uid', 0, 'intval');
		$cid = I('cid', 0, 'intval');
		$dialog = \App\Modules\Chat\Models\Kefu::getRecentDialog($uid, $cid);
		$user = \App\Modules\Chat\Models\Kefu::userInfo($dialog['customer_id']);
		$dialogInfo = array('customer_id' => $dialog['customer_id'], 'avatar' => $user['avatar'], 'name' => $user['user_name'], 'services_id' => $uid, 'goods' => 0 < $dialog['goods_id'] ? \App\Modules\Chat\Models\Kefu::getGoods($dialog['goods_id']) : '', 'store_id' => $dialog['store_id'], 'start_time' => $dialog['start_time'], 'origin' => $dialog['origin'] == 1 ? 'PC' : 'H5');
		$this->ajaxReturn($dialogInfo);
	}

	public function actionCloseDialog()
	{
		$uid = I('uid', 0, 'intval');
		$tid = I('tid', 0, 'intval');
		\App\Modules\Chat\Models\Kefu::closeWindow($uid, $tid);
	}

	public function actionCreatedialog()
	{
		$uid = I('uid', 0, 'intval');
		$fid = I('fid', 0, 'intval');
		$cid = I('cid', 0, 'intval');
		$dialog = \App\Modules\Chat\Models\Kefu::getRecentDialog($fid, $cid);
		\App\Modules\Chat\Models\Kefu::addDialog(array('customer_id' => $dialog['customer_id'], 'services_id' => $uid, 'goods_id' => $dialog['goods_id'], 'store_id' => $dialog['store_id'], 'start_time' => $dialog['start_time'], 'origin' => $dialog['origin']));
	}

	public function actionCloseOldDialog()
	{
		$expire = 600;
		$array = \App\Modules\Chat\Models\Kefu::closeOldWindow($expire);
		echo json_encode($array);
	}

	public function actionChangeLogin()
	{
		$id = I('id', 0, 'intval');
		$status = I('status', 0, 'intval');
		$status = in_array($status, array(0, 1)) ? $status : 0;
		$data['chat_status'] = $status;
		M('im_service')->where('id=' . $id . '  AND status = 1')->save($data);
	}

	public function actionStorageMessage()
	{
		$data = I();
		$fromId = empty($data['from_id']) ? 0 : intval($data['from_id']);
		$toId = empty($data['to_id']) ? 0 : intval($data['to_id']);
		$goodsId = empty($data['goods_id']) ? 0 : intval($data['goods_id']);
		$storeId = empty($data['store_id']) ? 0 : intval($data['store_id']);
		$status = $data['status'] === 0 || $data['status'] === '0' ? 0 : 1;
		$origin = empty($data['origin']) || $data['origin'] == 'PC' ? 1 : 2;

		if ($fromId == 0) {
			return NULL;
		}

		$user_type = $data['user_type'] == 'service' ? 2 : 1;
		$dialogData = array('customer_id' => $data['user_type'] == 'service' ? $data['from_id'] : $data['to_id'], 'services_id' => $data['user_type'] == 'service' ? $data['to_id'] : $data['from_id'], 'goods_id' => $goodsId, 'store_id' => $storeId, 'start_time' => time(), 'end_time' => '', 'origin' => $origin);
		$dialogId = \App\Modules\Chat\Models\Kefu::isDialog($dialogData);

		if (!$dialogId) {
			$dialogId = \App\Modules\Chat\Models\Kefu::addDialog($dialogData);
		}

		$data['message'] = strip_tags(trim($data['message']));
		$d = array('from_user_id' => $fromId, 'to_user_id' => $toId, 'message' => strip_tags(trim($data['message'])), 'add_time' => time(), 'user_type' => $user_type, 'dialog_id' => $dialogId, 'status' => $status);
		$res = M('im_message')->data($d)->add();

		if (!$res) {
			logResult('storage_message:' . json_encode($data));
		}
	}

	public function actionChangeMsgInfo()
	{
		$cusId = I('cus_id', 0, 'intval');
		$serId = I('ser_id', 0, 'intval');
		\App\Modules\Chat\Models\Kefu::updateDialog($cusId, $serId);
	}

	public function actionChangeNewMsgInfo()
	{
		$cusId = I('cus_id', 0, 'intval');
		$serId = I('ser_id', 0, 'intval');
		\App\Modules\Chat\Models\Kefu::updateNewDialog($cusId, $serId);
	}

	public function actionGetreply()
	{
		$serviceId = I('service_id', 0, 'intval');
		$content = \App\Modules\Chat\Models\Kefu::getServiceReply($serviceId);

		if (empty($content)) {
			$content = '您好';
		}

		echo $content;
	}

	private function getServerIp()
	{
		if (isset($_SERVER)) {
			if ($_SERVER['SERVER_ADDR']) {
				$server_ip = $_SERVER['SERVER_ADDR'];
			}
			else {
				$server_ip = $_SERVER['LOCAL_ADDR'];
			}
		}
		else {
			$server_ip = getenv('SERVER_ADDR');
		}

		return $server_ip;
	}

	public function actionUploadImage()
	{
		$this->load_helper('common');
		$path = 'images/upload/images/' . date('Ymd');
		$result = $this->upload($path, true, 2);

		if ($result['error'] == 0) {
			$arr = array(
				'code' => 0,
				'msg'  => '上传成功',
				'data' => array('src' => get_image_path($result['url']), 'title' => '')
				);
			$this->ajaxReturn($arr);
		}
	}

	public function actionTransMessage()
	{
		$message = I('message', '', array('html_in', 'trim'));

		if (empty($message)) {
			$this->ajaxReturn(array('error' => 1, 'content' => 'invalid params'));
		}

		$data = \App\Modules\Chat\Models\Kefu::format_msg($message);
		$this->ajaxReturn($data);
	}
}

?>
