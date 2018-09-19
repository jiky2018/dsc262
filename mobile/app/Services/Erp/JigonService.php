<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services\Erp;

class JigonService
{
	/**
     * 签名
     * @var
     */
	private $app_secret = '';
	/**
     * 密钥
     * @var
     */
	private $app_key = '';
	/**
     * @var bool
     */
	private $app_debug = true;
	/**
     * 正式环境地址：http://api.biz.jioao.cn/gy_api2
     * @var string
     */
	private $domain = 'http://api.biz.jioao.cn/gy_api2/';
	/**
     * 测试环境地址1：http://api.test.jioao.cn/gy_api
     * 测试环境地址2：http://api.test.jioao.cn/gy_api2
     * @var string
     */
	private $testDomain = 'http://api.test.jioao.cn/gy_api2/';
	/**
     * 库存查询
     * @var string
     */
	private $queryInventory = 'apiGoods/queryInventory';
	/**
     * 添加订单
     * @var string
     */
	private $apiaddOrder = 'apiOrder/addOrderObjectMall';
	/**
     * 确认订单
     * @var string
     */
	private $confirmorder = 'api/apiPublicNotify';
	/**
     * 推送售后信息
     * @var string
     */
	private $apiAfterSales = 'apiAfterSales/saveApply';
	/**
     * 获取售后地址
     * @var string
     */
	private $apiStoreRefundAddress = 'apiAfterSales/storeRefundAddress';

	public function __construct()
	{
		$this->app_secret = C('shop.cloud_client_id');
		$this->app_key = C('shop.cloud_appkey');
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
		$data['appKey'] = $this->app_key;
		$dataStr = json_encode($data);
		$url = $this->domain . $graphUrl;

		if ($this->app_debug) {
		}

		return \App\Extensions\Http::doPost($url, $dataStr, 5, 'Content-Type:application/json');
	}

	public function query($productIds)
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
		return $this->request($this->queryInventory, $request);
	}

	public function push($order_request, $order)
	{
		if (empty($order_request)) {
			return false;
		}

		$data = json_encode($order_request);
		$data = base64_encode($data);
		$request = array('data' => $data);
		return $this->request($this->apiaddOrder, $request);
	}

	public function confirm($order)
	{
		if (empty($order)) {
			return false;
		}

		$data = json_encode($order);
		$data = base64_encode($data);
		$request = array('data' => $data);
		return $this->request($this->confirmorder, $request);
	}

	public function saveAfterSales($order_return_request)
	{
		if (empty($order_return_request)) {
			return false;
		}

		$data = json_encode($order_return_request);
		$data = base64_encode($data);
		$request = array('data' => $data);
		return $this->request($this->apiAfterSales, $request);
	}

	public function getAfterSalesAddress($store_addres)
	{
		if (empty($store_addres)) {
			return false;
		}

		$data = json_encode($store_addres);
		$data = base64_encode($data);
		$request = array('data' => $data);
		return $this->request($this->apiStoreRefundAddress, $request);
	}
}


?>
