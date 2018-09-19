<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class cloud
{
	private $app_secret = EBusinessID;
	private $AppKey = AppKey;
	private $domain = ReqURL;
	private $getMethod = 'POST';
	private $graphUrl = '';
	private $queryInventory = 'apiGoods/queryInventory';
	private $apiaddOrder = 'apiOrder/addOrderObjectMall';
	private $confirmorder = 'api/apiPublicNotify';
	private $apiAfterSales = 'apiAfterSales/saveApply';
	private $apiStoreRefundAddress = 'apiAfterSales/storeRefundAddress';

	public function __construct()
	{
	}

	public function request($graphUrl, $data)
	{
		if (!$this->domain) {
			return false;
		}
		else if (!$graphUrl) {
			return false;
		}
		else if (!$data) {
			return false;
		}

		$sign = MD5($data['data'] . $this->app_secret);
		$data['sign'] = strtoupper($sign);
		$data['appKey'] = $this->AppKey;
		$url = $this->domain . $graphUrl;
		$http = new Http();
		return $http->doPost($url, $data, 5, 'Content-Type:application/json', 'json');
	}

	public function queryInventoryNum($productIds)
	{
		if (!$productIds) {
			return false;
		}

		foreach ($productIds as $k => $v) {
			$productIds[$k] = intval($v);
		}

		$data = array();
		$data['productIds'] = $productIds;
		$data = json_encode($data);
		$data = base64_encode($data);
		$request = array('appId' => 0, 'baUserId' => 0, 'data' => $data, 'goodsId' => 0, 'roleId' => 0, 'storeId' => 0, 'userId' => 0);
		$requ = $this->request($this->queryInventory, $request);
		return $requ;
	}

	public function addOrderMall($order_request, $order)
	{
		if (empty($order_request)) {
			return false;
		}

		$data = json_encode($order_request);
		$data = base64_encode($data);
		$request = array('data' => $data);
		$requ = $this->request($this->apiaddOrder, $request);
		return $requ;
	}

	public function confirmorder($order)
	{
		if (empty($order)) {
			return false;
		}

		$data = json_encode($order);
		$data = base64_encode($data);
		$request = array('data' => $data);
		$requ = $this->request($this->confirmorder, $request);
		return $requ;
	}

	public function apiAfterSales($order_return_request)
	{
		if (empty($order_return_request)) {
			return false;
		}

		$data = json_encode($order_return_request);
		$data = base64_encode($data);
		$request = array('data' => $data);
		$requ = $this->request($this->apiAfterSales, $request);
		return $requ;
	}

	public function getStoreRefundAddress($store_addres)
	{
		if (empty($store_addres)) {
			return false;
		}

		$data = json_encode($store_addres);
		$data = base64_encode($data);
		$request = array('data' => $data);
		$requ = $this->request($this->apiStoreRefundAddress, $request);
		return $requ;
	}
}

defined('EBusinessID') || define('EBusinessID', $GLOBALS['_CFG']['cloud_client_id']);
defined('AppKey') || define('AppKey', $GLOBALS['_CFG']['cloud_appkey']);
defined('ReqURL') || define('ReqURL', 'http://api.biz.jioao.cn/gy_api2/');

?>
