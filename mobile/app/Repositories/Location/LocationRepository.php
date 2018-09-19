<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Location;

class LocationRepository
{
	public function Index($region_id = 0)
	{
		if (0 < $region_id) {
			$list = \App\Models\Region::select('region.region_name', 'region.region_id')->where('region_id', $region_id)->get()->toArray();

			foreach ($list as $key => $v) {
				$list2[$key] = \App\Models\Region::select('region.region_name', 'region.region_id')->where('parent_id', $v['region_id'])->get()->toArray();

				foreach ($list2 as $key2 => $val) {
					$list2[$key2] = \App\Models\Region::select('region.region_name', 'region.region_id')->where('parent_id', $v['region_id'])->get()->toArray();
				}

				$list[$key]['tree'] = $list2;
				return $list;
			}
		}

		$list = \App\Models\Region::select('region.region_name', 'region.region_id')->where('region_type', 2)->get()->toArray();
		return $list;
	}

	public function SetCity($data = array())
	{
		$_SESSION['recent_city_history'][$data['region_id']] = $data['region_name'];
	}

	public function Info($region_id = 0, $region_type = '')
	{
		$city = RegionWarehouse::select('region_warehouse.region_name')->where('region_id', $region_type)->get()->toArray();
		$area = \App\Models\MerchantsRegionArea::from('merchants_region_area as mra')->select('mra.*')->leftjoin('merchants_region_info as mri', 'mra.ra_id', '=', 'mri.ra_id')->where('mri.region_id', $region_id)->get()->toArray();
		$msg['region_name'] = $city[0]['region_name'];
		$msg['ra_id'] = $area[0]['ra_id'];
		$msg['ra_name'] = $area[0]['ra_name'];
		$msg['region_id'] = $region_id;
		return $msg;
	}

	public function contrast($region_name)
	{
		$name = \App\Models\Region::select('region.region_name', 'region.region_id', 'region.parent_id')->where('region_name', 'like', '%' . $region_name . '%')->where('region_type', 2)->get()->toArray();
		setcookie('lbs_city_name', $name[0]['region_name'] . '市');
		setcookie('lbs_city', $name[0]['region_id']);
		setcookie('province', $name[0]['parent_id']);
		setcookie('city', $name[0]['region_id']);
		return $name;
	}
}


?>
