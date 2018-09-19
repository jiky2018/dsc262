<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
namespace App\Modules\OfflineStore\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	private $goods_id = 0;
	private $page = 1;
	private $size = 10;

	public function __construct()
	{
		parent::__construct();
		$this->size = I('request.size', 10);
		$this->page = I('request.page', 1, 'intval');
	}

	public function actionStoreList()
	{
		if (IS_AJAX) {
			$province = I('province', 0, 'intval');
			$city = I('city', 0, 'intval');
			$district = I('district', 0, 'intval');
			$street = I('street', 0, 'intval');
			$type = I('type', '', 'trim');
			$where = '1';

			if (0 < $province) {
				$where .= ' AND o.province = ' . $province;
			}

			if (0 < $city) {
				$where .= ' AND o.city = ' . $city;
			}

			if (0 < $district) {
				$where .= ' AND o.district = ' . $district;
			}

			if (0 < $street) {
				$where .= ' AND o.street = ' . $street;
			}

			$goods_id = I('request.goods_id', 0, 'intval');
			$spec_arr = isset($_REQUEST['spec_arr']) ? $_REQUEST['spec_arr'] : '';
			$userId = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
			$store_id = getStoreIdByGoodsId($goods_id);
			$sql = 'SELECT o.id,o.stores_name,s.goods_id,o.stores_address,o.stores_traffic_line,o.ru_id ,p.region_name as province ,s.goods_number ,o.stores_address, o.stores_tel, o.stores_opening_hours, ' . 'c.region_name as city ,d.region_name as district FROM {pre}offline_store AS o ' . 'LEFT JOIN {pre}store_goods  AS s ON o.id = s.store_id ' . 'LEFT JOIN {pre}region AS p ON p.region_id = o.province ' . 'LEFT JOIN {pre}region AS c ON c.region_id = o.city ' . 'LEFT JOIN {pre}region AS d ON d.region_id = o.district ' . ('WHERE ' . $where . '  AND o.is_confirm = 1 AND s.goods_id =\'') . $goods_id . '\' GROUP BY o.id';
			$stores = $GLOBALS['db']->query($sql);
			$total = is_array($stores) ? count($stores) : 0;
			$store_list = $GLOBALS['db']->selectLimit($sql, $this->size, ($this->page - 1) * $this->size);
			$backurl = url('goods/index/index', array('goods_id' => $goods_id), false, true);
			$is_spec = explode(',', $spec_arr);

			if (!empty($store_list)) {
				foreach ($store_list as $k => $v) {
					$store_list[$k]['map_url'] = url('offline_store/index/map', array('address' => $v['province'] . $v['city'] . $v['district'] . trim($v['stores_address']), 'backurl' => urlencode($backurl)));

					if ($v['id'] == $store_id) {
						$store_list[$k]['checked'] = 1;
					}

					if (is_spec($is_spec) == true) {
						$products = get_warehouse_id_attr_number($v['goods_id'], $spec_arr, $v['ru_id'], 0, 0, '', $v['id']);
						$v['goods_number'] = $products['product_number'];

						if ($products['product_number'] == 0) {
							unset($store_list[$k]);
						}
					}
				}
			}

			exit(json_encode(array('store_list' => $store_list, 'totalPage' => ceil($total / $this->size))));
		}
	}

	public function actionOfflineStoreDetail()
	{
		$this->actionCheckLogin();
		$storeId = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$order_id = I('order_id', 0, 'intval');
		$pay_status = dao('order_info')->where(array('order_id' => $order_id))->getField('pay_status');

		if ($pay_status != PS_PAYED) {
			show_message('订单支付完成，才能查看提货码！');
		}

		$sql = 'SELECT store_id,pick_code,take_time  FROM' . $this->ecs->table('store_order') . (' WHERE id = \'' . $storeId . '\'');
		$stores = $this->db->getRow($sql);
		$this->assign('store', $stores);

		if (0 < $stores['store_id']) {
			$sql = 'SELECT o.*,p.region_name as province,c.region_name as city,d.region_name as district FROM' . $this->ecs->table('offline_store') . ' AS o ' . 'LEFT JOIN ' . $this->ecs->table('region') . ' AS p ON p.region_id = o.province ' . 'LEFT JOIN ' . $this->ecs->table('region') . ' AS c ON c.region_id = o.city ' . 'LEFT JOIN ' . $this->ecs->table('region') . ' AS d ON d.region_id = o.district WHERE o.id = \'' . $stores['store_id'] . '\'';
			$offline_store = $this->db->getRow($sql);

			if ($offline_store) {
				$offline_store['stores_img'] = get_image_path($offline_store['stores_img']);
			}

			$this->assign('offline_store', $offline_store);
		}

		$this->assign('page_title', '订单提货码');
		$this->display();
	}

	public function actionCreateQrcode()
	{
		$value = I('get.value', '');
		$errorCorrectionLevel = 'M';
		$matrixPointSize = 8;
		@\App\Extensions\QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize, 2);
	}

	public function actionMap()
	{
		$address = input('get.address', '', 'trim');
		$backurl = input('get.backurl', '', 'urldecode');

		if (empty($address)) {
			$address = C('shop.shop_address');
		}

		$backurl = !empty($backurl) ? $backurl : url('/');
		$url = 'http://apis.map.qq.com/tools/routeplan/eword=' . $address . '?referer=myapp&key=' . C('shop.tengxun_key') . '&back=1&backurl=' . $backurl;
		redirect($url);
	}

	public function actionCheckLogin()
	{
		$back_act = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];

		if (IS_AJAX) {
			if (empty($_SESSION['user_id'])) {
				exit(json_encode(array('error' => '1', 'url' => url('user/login/index', array('back_act' => urlencode($back_act))))));
			}
			else {
				exit(json_encode(array('error' => 0)));
			}
		}
		else if (empty($_SESSION['user_id'])) {
			$this->redirect('user/login/index', array('back_act' => urlencode($back_act)));
		}
	}
}

?>
