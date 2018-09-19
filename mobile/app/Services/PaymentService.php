<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services;

class PaymentService
{
	public $payList;
	private $orderRepository;
	private $shopConfigRepository;
	private $accountRepository;
	private $shopService;
	private $WxappConfigRepository;
	private $teamRepository;
	private $authService;
	private $flowService;

	public function __construct(\App\Repositories\Order\OrderRepository $orderRepository, \App\Repositories\ShopConfig\ShopConfigRepository $shopConfigRepository, \App\Repositories\User\AccountRepository $accountRepository, ShopService $shopService, \App\Repositories\Wechat\WxappConfigRepository $WxappConfigRepository, AuthService $authService, FlowService $flowService, \App\Repositories\Team\TeamRepository $teamRepository)
	{
		$this->orderRepository = $orderRepository;
		$this->shopConfigRepository = $shopConfigRepository;
		$this->accountRepository = $accountRepository;
		$this->shopService = $shopService;
		$this->WxappConfigRepository = $WxappConfigRepository;
		$this->authService = $authService;
		$this->flowService = $flowService;
		$this->teamRepository = $teamRepository;
		$this->payList = array('order' => 'order.pay', 'account' => 'account.pay');
	}

	public function payment($args)
	{
		$shopName = $this->shopConfigRepository->getShopConfigByCode('shop_name');
		$order = $this->orderRepository->find($args['id']);
		$orderGoods = $this->orderRepository->getOrderGoods($args['id']);
		$ruName = $this->shopService->getShopName($orderGoods[0]['ru_id']);

		switch ($args['code']) {
		case $this->payList['order']:
			$new = array('open_id' => $args['open_id'], 'body' => $ruName . '-订单编号' . $order['order_sn'], 'out_trade_no' => $order['order_sn'], 'total_fee' => $order['order_amount'] * 100);
			break;

		case $this->payList['account']:
			$account = $this->accountRepository->getDepositInfo($args['id']);
			$new = array('open_id' => $args['open_id'], 'body' => $shopName . '-订单编号' . $order['order_sn'], 'out_trade_no' => date('Ymd') . 'A' . str_pad($account['id'], 6, '0', STR_PAD_LEFT), 'total_fee' => $account['amount'] * 100);
			break;

		default:
			$new = array('open_id' => $args['open_id'], 'body' => $shopName . '-订单编号' . $order['order_sn'], 'out_trade_no' => 'out_trade_no', 'total_fee' => 'total_fee');
			break;
		}

		return $this->WxPay($new);
	}

	private function WxPay($args)
	{
		$wxpay = new Wxpay\WxPay();
		$code = 'wxpay';
		$config = array('app_id' => $this->WxappConfigRepository->getWxappConfigByCode('wx_appid'), 'app_secret' => $this->WxappConfigRepository->getWxappConfigByCode('wx_appsecret'), 'mch_key' => $this->WxappConfigRepository->getWxappConfigByCode('wx_mch_key'), 'mch_id' => $this->WxappConfigRepository->getWxappConfigByCode('wx_mch_id'));
		$wxpay->init($config['app_id'], $config['app_secret'], $config['mch_key']);
		$nonce_str = 'ibuaiVcKdpRxkhJA';
		$time_stamp = (string) gmtime();
		$inputParams = array('appid' => $config['app_id'], 'mch_id' => $config['mch_id'], 'openid' => $args['open_id'], 'device_info' => '1000', 'nonce_str' => $nonce_str, 'body' => $args['body'], 'attach' => $args['body'], 'out_trade_no' => $args['out_trade_no'], 'total_fee' => $args['total_fee'], 'spbill_create_ip' => app('request')->getClientIp(), 'notify_url' => \Illuminate\Support\Facades\URL::to('api/wx/payment/notify', array('code', $code)), 'trade_type' => 'JSAPI');
		$inputParams['sign'] = $wxpay->createMd5Sign($inputParams);
		$prepayid = $wxpay->sendPrepay($inputParams);
		$pack = 'prepay_id=' . $prepayid;
		$prePayParams = array('appId' => $config['app_id'], 'timeStamp' => $time_stamp, 'package' => $pack, 'nonceStr' => $nonce_str, 'signType' => 'MD5');
		$sign = $wxpay->createMd5Sign($prePayParams);
		$body = array('appid' => $config['app_id'], 'mch_id' => $config['mch_id'], 'prepay_id' => $prepayid, 'nonce_str' => $nonce_str, 'timestamp' => $time_stamp, 'packages' => $pack, 'sign' => $sign);
		return array('wxpay' => $body);
	}

	public function notify($args)
	{
		$uid = $args['uid'];
		$orderId = $args['id'];
		$form_id = $args['form_id'];
		$idsArr = array();
		$order = $this->orderRepository->find($orderId);
		if (empty($order['user_id']) || $order['user_id'] != $uid) {
			return array('code' => 1, 'msg' => '不是你的订单');
		}

		array_unshift($idsArr, $orderId);

		if ($order['main_order_id'] == 0) {
			$ids = $this->orderRepository->getChildOrder($order['order_id']);

			if ($ids) {
				$idsArr = array_column($ids, 'order_id');
			}
		}

		$res = $this->orderRepository->orderPay($uid, $idsArr);

		if ($res === 0) {
			return array('code' => 1, 'msg' => '没有任何修改');
		}

		$this->flowService->cloudConfirmOrder($order['order_id']);
		if ($order['extension_code'] == 'team_buy' && 0 < $order['team_id']) {
			$team_info = $this->teamRepository->teamIsFailure($order['team_id']);
			$team_count = $this->teamRepository->surplusNum($order['team_id']);

			if ($team_info['team_num'] <= $team_count) {
				$this->teamRepository->updateTeamLogStatua($order['team_id']);
			}

			$limit_num = $team_info['limit_num'] + 1;
			$this->teamRepository->updateTeamLimitNum($team_info['id'], $team_info['goods_id'], $limit_num);
			$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
			$timeFormat = $shopconfig->getShopConfigByCode('time_format');
			$end_time = $team_info['start_time'] + $team_info['validity_time'] * 3600;

			if (0 < $order['team_parent_id']) {
				$pushData = array(
					'keyword1' => array('value' => $team_info['goods_name'], 'color' => '#000000'),
					'keyword2' => array('value' => $team_info['team_num'], 'color' => '#000000'),
					'keyword3' => array('value' => local_date($timeFormat, $end_time), 'color' => '#000000'),
					'keyword4' => array('value' => price_format($team_info['team_price'], true), 'color' => '#000000')
					);
				$url = 'pages/group/wait?objectId=' . $order['team_id'] . '&user_id=' . $order['user_id'];
				$this->authService->wxappPushTemplate('AT0541', $pushData, $url, $order['user_id'], $form_id);
			}
			else {
				$pushData = array(
					'keyword1' => array('value' => $team_info['goods_name'], 'color' => '#000000'),
					'keyword2' => array('value' => price_format($team_info['team_price'], true), 'color' => '#000000'),
					'keyword3' => array('value' => local_date($timeFormat, $end_time), 'color' => '#000000')
					);
				$url = 'pages/group/wait?objectId=' . $order['team_id'] . '&user_id=' . $order['user_id'];
				$this->authService->wxappPushTemplate('AT0933', $pushData, $url, $order['user_id'], $form_id);
			}
		}

		return array('code' => 0, 'res' => $res, 'extension_code' => $order['extension_code'], 'team_id' => $order['team_id'], 'user_id' => $order['user_id']);
	}
}


?>
