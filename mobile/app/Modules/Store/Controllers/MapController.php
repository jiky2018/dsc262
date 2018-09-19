<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Store\Controllers;

class MapController extends \App\Modules\Base\Controllers\FrontendController
{
	public function __construct()
	{
		parent::__construct();
		L(require LANG_PATH . C('shop.lang') . '/other.php');
	}

	public function actionIndex()
	{
		if (IS_POST) {
			$lng = I('post.lng', 0);
			$lat = I('post.lat', 0);
			$sql = 'SELECT a.rz_shopname, b.shop_name, b.province, b.city, b.district, b.shop_address, b.longitude, b.latitude, ( 6371 * acos( cos( radians(' . $lat . ') ) * cos( radians( b.latitude ) ) * cos( radians( b.longitude ) - radians(' . $lng . ') ) + sin( radians(' . $lat . ') ) * sin( radians( b.latitude ) ) ) ) AS distance FROM {pre}seller_shopinfo as b LEFT JOIN {pre}merchants_shop_information as a ON a.user_id=b.ru_id  WHERE  a.is_street = 1 and a.merchants_audit = 1 ORDER BY distance  LIMIT 10';
			$seller_shopinfo = $this->model->query($sql);
			$list = array();
			$store = '';

			foreach ($seller_shopinfo as $key => $vo) {
				$province = get_region_name($vo['province']);
				$city = get_region_name($vo['city']);
				$district = get_region_name($vo['district']);
				$address = $province['region_name'] . $city['region_name'] . $district['region_name'] . $vo['shop_address'];
				$info = array('coord' => $vo['latitude'] . ',' . $vo['longitude'], 'title' => empty($vo['shop_name']) ? $vo['rz_shopname'] : $vo['shop_name'], 'addr' => $address);
				if (empty($vo['latitude']) || empty($vo['longitude'])) {
					continue;
				}

				$list[] = urldecode(str_replace('=', ':', http_build_query($info, '', ';')));
			}

			$store = implode('|', $list);

			if (empty($store)) {
				exit(json_encode(array('error' => 1, 'message' => '您的附近暂无商家哦')));
			}

			$url = 'http://apis.map.qq.com/tools/poimarker?type=0&marker=' . $store . '&key=' . C('shop.tengxun_key') . '&referer=ectouch';
			exit(json_encode(array('error' => 0, 'url' => $url)));
		}

		$this->assign('page_title', L('nearby_shop'));
		$this->display();
	}

	public function actionTest()
	{
		$seller_shopinfo = $this->model->table('seller_shopinfo')->select();

		foreach ($seller_shopinfo as $key => $vo) {
			$province = get_region_name($vo['province']);
			$city = get_region_name($vo['city']);
			$district = get_region_name($vo['district']);
			$address = $province['region_name'] . $city['region_name'] . $district['region_name'] . $vo['shop_address'];
			$result = \App\Extensions\Http::doGet('http://apis.map.qq.com/ws/geocoder/v1/?key=EVNBZ-UYICR-6DSW4-WSHCK-VEHYH-HWB66&address=' . $address);
			$data = json_decode($result, 1);

			if (!$data['status']) {
				$location = $data['result']['location'];
				$locat['longitude'] = $location['lng'];
				$locat['latitude'] = $location['lat'];
				$condition['id'] = $vo['id'];
				$this->model->table('seller_shopinfo')->data($locat)->where($condition)->save();
			}

			if ((($key + 1) % 5) == 0) {
				sleep(1);
			}
		}
	}
}

?>
