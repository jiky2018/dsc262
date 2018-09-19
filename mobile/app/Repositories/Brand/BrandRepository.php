<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Brand;

class BrandRepository
{
	public function getAllBrands()
	{
		$brand_list = S('brand_list');

		if (!empty($brand_list)) {
			return $brand_list;
		}

		$res = \App\Models\Brand::select('brand_id', 'brand_name', 'brand_logo', 'brand_desc')->where('is_show', 1)->groupby('brand_id')->groupby('sort_order')->orderby('sort_order', 'ASC')->get()->toArray();
		$res = array_values($res);
		$arr = array();

		foreach ($res as $key => $row) {
			if ($key == 0) {
				$arr['top'][$row['brand_id']]['brand_id'] = $row['brand_id'];
				$arr['top'][$row['brand_id']]['brand_name'] = $row['brand_name'];
				$arr['top'][$row['brand_id']]['brand_logo'] = get_data_path($row['brand_logo'], 'brandlogo/');
				$arr['top'][$row['brand_id']]['goods_num'] = $this->goodsCountByBrand($row['brand_id']);
				$arr['top'][$row['brand_id']]['brand_desc'] = htmlspecialchars($row['brand_desc'], ENT_QUOTES);
			}
			else {
				if (0 < $key && $key < 4) {
					$arr['center'][$row['brand_id']]['brand_id'] = $row['brand_id'];
					$arr['center'][$row['brand_id']]['brand_name'] = $row['brand_name'];
					$arr['center'][$row['brand_id']]['brand_logo'] = get_data_path($row['brand_logo'], 'brandlogo/');
					$arr['center'][$row['brand_id']]['goods_num'] = $this->goodsCountByBrand($row['brand_id']);
					$arr['center'][$row['brand_id']]['brand_desc'] = htmlspecialchars($row['brand_desc'], ENT_QUOTES);
				}
				else {
					if (4 < $key && $key < 4) {
						$arr['list1'][$row['brand_id']]['brand_id'] = $row['brand_id'];
						$arr['list1'][$row['brand_id']]['brand_name'] = $row['brand_name'];
						$arr['list1'][$row['brand_id']]['brand_logo'] = get_data_path($row['brand_logo'], 'brandlogo/');
						$arr['list1'][$row['brand_id']]['goods_num'] = $this->goodsCountByBrand($row['brand_id']);
						$arr['list1'][$row['brand_id']]['brand_desc'] = htmlspecialchars($row['brand_desc'], ENT_QUOTES);
					}
					else {
						$arr['list2'][$row['brand_id']]['brand_id'] = $row['brand_id'];
						$arr['list2'][$row['brand_id']]['brand_name'] = $row['brand_name'];
						$arr['list2'][$row['brand_id']]['brand_logo'] = get_data_path($row['brand_logo'], 'brandlogo/');
						$arr['list2'][$row['brand_id']]['goods_num'] = $this->goodsCountByBrand($row['brand_id']);
						$arr['list2'][$row['brand_id']]['brand_desc'] = htmlspecialchars($row['brand_desc'], ENT_QUOTES);
					}
				}
			}
		}

		S('brand_list', $arr, array('expire' => 3600));
		return $arr;
	}

	public function getBrandDetail($id)
	{
		$brand = \App\Models\Brand::select('brand_id', 'brand_name')->where('is_show', 1)->where('is_delete', 0)->where('brand_id', $id)->first()->toArray();
		return $brand;
	}

	private function goodsCountByBrand($brand_id)
	{
		$goodsNum = \App\Models\Goods::select()->where('brand_id', $brand_id)->where('is_on_sale', 1)->where('is_alone_sale', 1)->where('is_delete', 0)->count();
		return $goodsNum;
	}
}


?>
