<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Region\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	public function __construct()
	{
		parent::__construct();
		L(require LANG_PATH . C('shop.lang') . '/other.php');
	}

	public function actionIndex()
	{
		$type = I('get.type', 0, 'intval');
		$parent = I('get.parent', 0, 'intval');
		$user_id = I('get.user_id', 0, 'intval');
		$regions = get_regions($type, $parent);
		if (($type == 2) && !empty($regions)) {
			foreach ($regions as $k => $v) {
				$regions[$k]['district'] = get_regions(3, $v['region_id']);
			}
		}

		$arr['regions'] = $regions;
		$arr['type'] = $type;
		$arr['user_id'] = $user_id;

		if ($user_id) {
			$user_address = get_user_address_region($user_id);
			$user_address = explode(',', $user_address['region_address']);

			if (in_array($parent, $user_address)) {
				$arr['isRegion'] = 1;
			}
			else {
				$arr['isRegion'] = 88;
				$arr['message'] = L('input_dispatch_addr');
				$arr['province'] = $_COOKIE['province'];
				$arr['city'] = $_COOKIE['city'];
			}
		}

		if (empty($arr['regions'])) {
			$arr['empty_type'] = 1;
		}

		echo json_encode($arr);
	}

	public function actionSelectRegionChild()
	{
		if (IS_AJAX) {
			$result = array('error' => 0, 'message' => '', 'content' => '');
			clear_cache_files();
			$cat_id = I('get.cat_id', 0, 'intval');
			$province = I('get.province', 1, 'intval');
			$city = I('get.city', 0, 'intval');
			$district = I('get.district', 0, 'intval');
			$street = I('get.street', 0, 'intval');
			setcookie('province', $province, gmtime() + (3600 * 24 * 30));
			setcookie('city', $city, gmtime() + (3600 * 24 * 30));
			setcookie('district', $district, gmtime() + (3600 * 24 * 30));
			setcookie('street', $street, gmtime() + (3600 * 24 * 30));
			setcookie('regionId', $regionId, gmtime() + (3600 * 24 * 30));
			setcookie('type_province', 0, gmtime() + (3600 * 24 * 30));
			setcookie('type_city', 0, gmtime() + (3600 * 24 * 30));
			setcookie('type_district', 0, gmtime() + (3600 * 24 * 30));
			setcookie('type_street', 0, gmtime() + (3600 * 24 * 30));
			$result['cat_id'] = $cat_id;
			exit(json_encode($result));
		}
	}

	public function actionSelectDistrictList()
	{
		if (IS_AJAX) {
			$result = array('error' => 0, 'message' => '', 'content' => '', 'ra_id' => '', 'region_id' => '');
			$region_id = I('get.region_id', 0, 'intval');
			$type = I('get.type', 0, 'intval');
			$where = 'region_id = \'' . $region_id . '\'';
			$date = array('parent_id');
			$parent_id = get_table_date('region', $where, $date, 2);

			if ($type == 0) {
				cookie('province', $parent_id);
				cookie('city', $region_id);
				$where = 'parent_id = \'' . $region_id . '\' order by region_id asc limit 0, 1';
				$date = array('region_id', 'region_name');
				$district_list = get_table_date('region', $where, $date, 1);

				if (0 < count($district_list)) {
					cookie('district', $district_list[0]['region_id']);
				}
				else {
					cookie('district', 0);
				}

				cookie('type_province', 0);
				cookie('type_city', 0);
				cookie('type_district', 0);
			}
			else {
				$where = 'region_id = \'' . $parent_id . '\'';
				$date = array('parent_id');
				$province = get_table_date('region', $where, $date, 2);
				cookie('type_province', $province);
				cookie('type_city', $parent_id);
				cookie('type_district', $region_id);
			}

			exit(json_encode($result));
		}
	}

	public function actionAddress()
	{
		$pid = input('parent_id', 1, 'intval');
		$list = $this->model->table('region')->field('region_id,region_name')->where(array('parent_id' => $pid))->cache(true, 12 * 3600)->select();
		$res = array();

		foreach ($list as $key => $v) {
			$res[$key]['name'] = $v['region_name'];
			$res[$key]['id'] = $v['region_id'];
		}

		$addresslist = array('addressList' => $res);
		exit(json_encode($addresslist));
	}
}

?>
