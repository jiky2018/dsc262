<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Region;

class RegionRepository
{
	public function getRegionName($regionId)
	{
		$regionName = \App\Models\Region::where('region_id', $regionId)->pluck('region_name')->toArray();

		if (empty($regionName)) {
			return '';
		}

		return $regionName[0];
	}

	public function regionListByType($type)
	{
		$reginList = \Illuminate\Support\Facades\Cache::get('region_list_' . $type);

		if (empty($reginList)) {
			$reginList = \App\Models\Region::where('region_type', $type)->get()->toArray();
			\Illuminate\Support\Facades\Cache::put('region_list_' . $type, $reginList, 60);
		}

		return $reginList;
	}

	public function getRegionTypeById($regionId)
	{
		$regionType = \App\Models\Region::where('region_id', $regionId)->pluck('region_type')->toArray();

		if (empty($regionType)) {
			return '';
		}

		return $regionType[0];
	}

	public function getRegionByParentId($regionId = 1)
	{
		$regionList = \App\Models\Region::where('parent_id', $regionId)->get()->toArray();
		return $regionList;
	}

	public function getRegionAll($regionId = 1)
	{
		$regionList = \App\Models\Region::where('parent_id', $regionId['id'])->get()->toArray();

		foreach ($regionList as $key => $value) {
			$regionList[$key]['region'] = \App\Models\Region::where('parent_id', $value['region_id'])->get()->toArray();

			foreach ($regionList[$key]['region'] as $k => $v) {
				$regionList[$key]['region'][$k]['region'] = \App\Models\Region::where('parent_id', $v['region_id'])->get()->toArray();
			}
		}

		return $regionList;
	}
}


?>
