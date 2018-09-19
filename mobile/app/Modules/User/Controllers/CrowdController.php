<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\User\Controllers;

class CrowdController extends \App\Modules\Base\Controllers\FrontendController
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
		$this->assign('info', get_user_default($this->user_id));
		$this->assign('rank', get_rank_info());

		if ($rank = get_rank_info()) {
			$this->assign('rank', $rank);

			if (empty($rank)) {
				$this->assign('next_rank_name', sprintf(L('next_level'), $rank['next_rank'], $rank['next_rank_name']));
			}
		}

		$best_list = zc_best_list();
		$this->assign('best_list', $best_list);
		$this->assign('page_title', L('crowd_user'));
		$this->display();
	}

	public function actionOrder()
	{
		$this->status = I('request.status') ? intval(I('request.status')) : 1;

		if (IS_AJAX) {
			$size = 10;
			$page = I('page', 1, 'intval');
			$order_list = zc_get_user_orders($this->user_id, $size, $page, $this->status);
			exit(json_encode(array('list' => $order_list['list'], 'totalPage' => $order_list['totalpage'])));
		}

		$this->assign('status', $this->status);
		$this->assign('page_title', L('crowd_order'));
		$this->display();
	}

	public function actionDetail()
	{
		$order_id = I('order_id', 0, 'intval');
		$order = zc_get_order_detail($order_id, $this->user_id);

		if ($order === false) {
			$this->err->show(L('back_home_lnk'), './');
			exit();
		}

		$os = L('os');
		$ps = L('ps');
		$ss = L('ss');

		if ($order['order_status'] == OS_UNCONFIRMED) {
			$order['handler'] = '<span class="box-flex text-right"></span><a class="btn-default box-flex" type="button" href="' . url('user/crowd/cancel', array('order_id' => $order['order_id'])) . '" onclick="if (!confirm(\'' . L('confirm_cancel') . '\')) return false;">' . L('cancel') . '</a>';
		}
		else if ($order['order_status'] == OS_SPLITED) {
			if ($order['shipping_status'] == SS_SHIPPED) {
				@$order['handler'] = '<a class="btn-submit" href="' . url('user/crowd/affirmreceived', array('order_id' => $order['order_id'])) . '" onclick="if (!confirm(\'' . L('confirm_received') . '\')) return false;">' . L('received') . '</a>';
			}
			else if ($order['shipping_status'] == SS_RECEIVED) {
				@$order['handler'] = '<span class="order-checkout-text box">' . L('ss_received') . '</span>';
			}
			else if ($order['pay_status'] == PS_UNPAYED) {
				@$order['handler'] = '<span class="box-flex text-right"></span><a class="btn-submit" href="' . url('user/order/detail', array('order_id' => $order['order_id'])) . '" >' . L('pay_money') . '</a>';
			}
			else {
				$order['handler'] = '<span class="order-checkout-text box">' . $ss[$order['shipping_status']] . '</span>';
			}
		}
		else {
			if ($order['order_status'] == OS_CONFIRMED && $order['pay_status'] == PS_UNPAYED) {
				$order['handler'] = '<span class="box-flex text-right"></span><a class="btn-default box-flex" type="button" >' . $ps[$order['pay_status']] . '</a>';
			}
			else if ($order['pay_status'] == PS_PAYED_PART) {
				if ($order['extension_code'] == 'presale') {
					$result = presale_settle_status($order['extension_id']);

					if ($result['settle_status'] == 1) {
						$msg = sprintf(L('presale_tip_1'), $result['start_time'], $result['end_time']);
						@$order['handler'] = '<span class=\\"box-flex text-right\\">' . $msg . '</span>';
					}

					if ($result['settle_status'] == 0) {
						$msg = sprintf(L('presale_tip_1'), $result['start_time'], $result['end_time']);
						$order['hidden_pay_button'] = 1;
						@$order['handler'] = '<span class=\\"box-flex text-right\\">' . $msg . '</span>';
					}

					if ($result['settle_status'] == -1) {
						$order['hidden_pay_button'] = 1;
						$msg = sprintf(L('presale_tip_2'), $result['end_time']);
						@$order['handler'] = '<span class=\\"box-flex text-right\\">' . $msg . '</span>';
					}
				}
			}
			else {
				$order['handler'] = $order['handler'] = '<span class="order-checkout-text box">' . $os[$order['order_status']] . '</span>';
			}
		}

		$order['c'] = get_region_name($order['country']);
		$order['detail_address'] .= $order['c']['region_name'];
		$order['p'] = get_region_name($order['province']);
		$order['detail_address'] .= $order['p']['region_name'];
		$order['cc'] = get_region_name($order['city']);
		$order['detail_address'] .= $order['cc']['region_name'];
		$order['dd'] = get_region_name($order['district']);
		$order['detail_address'] .= $order['dd']['region_name'];
		$order['detail_address'] .= $order['address'];
		$this->assign('order', $order);
		$this->assign('page_title', L('crowd_order_detail'));
		$this->display();
	}

	public function actionAffirmReceived()
	{
		$user_id = $this->user_id;
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

		if (affirm_received($order_id, $user_id)) {
			ecs_header('Location: ' . url('user/crowd/order'));
			exit();
		}
		else {
			show_message('还未发货或者已收货');
		}
	}

	public function actionCancel()
	{
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

		if (zc_cancel_order($order_id, $this->user_id)) {
			ecs_header('Location: ' . url('user/crowd/order'));
			exit();
		}
		else {
			$this->err->show(L('order_list_lnk'), url('user/crowd/order'));
		}
	}

	public function actionFocus()
	{
		$this->type = I('request.type') ? intval(I('request.type')) : 1;
		$zc_focus = zc_focus_list($this->user_id, $this->type);
		$this->assign('zc_focus', $zc_focus);
		$this->assign('type', $this->type);
		$this->assign('page_title', L('crowd_focus'));
		$this->display();
	}

	public function actionCrowdbuy()
	{
		$this->type = I('request.type') ? intval(I('request.type')) : 1;

		if (IS_AJAX) {
			$size = 10;
			$page = I('page', 1, 'intval');
			$crowd_buy = crowd_buy_list($this->user_id, $size, $page, $this->type);
			exit(json_encode(array('list' => $crowd_buy['list'], 'totalPage' => $crowd_buy['totalpage'])));
		}

		$this->assign('type', $this->type);
		$this->assign('page_title', L('crowd_buy'));
		$this->display();
	}

	public function actionAddComment()
	{
		if (IS_POST) {
			$user_id = $_SESSION['user_id'];
			$topic_content = I('content');
			$order_id = I('order_id', 0, 'intval');
			$pid = I('goods_id', 0, 'intval');
			$addtime = gmtime();

			if (empty($topic_content)) {
				show_message('评论内容不可为空', '返回', '', 'warning');
			}

			$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('zc_topic') . ('(topic_status,topic_content,pid,add_time,user_id)VALUES(\'1\',\'' . $topic_content . ' \', \'' . $pid . '\', \' ' . $addtime . '\', \'' . $user_id . '\')');
			$GLOBALS['db']->query($sql);
			show_message('商品评论成功', '返回上一页', url('user/crowd/order'), 'success');
		}

		$order_id = I('order_id', 0, 'intval');
		$sql = 'select zp.id,zp.title,zp.title_img,zg.content,zg.price,oi.order_id from ' . $this->ecs->table('zc_goods') . ' as zg left join ' . $this->ecs->table('zc_project') . " as zp on zg.pid=zp.id\r\n\t\t\tleft join " . $this->ecs->table('order_info') . ('as oi on zg.id=oi.zc_goods_id where oi.order_id=\'' . $order_id . '\' and oi.is_zc_order=1 ');
		$goods_info = $this->db->getRow($sql);

		if (empty($goods_info)) {
			show_message('评论商品数据不完整', '返回', '', 'warning');
		}

		$goods_info['title_img'] = get_zc_image_path($goods_info['title_img']);
		$goods_info['price'] = price_format($goods_info['price']);
		$this->assign('order_id', $order_id);
		$this->assign('goods_info', $goods_info);
		$this->assign('page_title', L('crowd_comment'));
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
