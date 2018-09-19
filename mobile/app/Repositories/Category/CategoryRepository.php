<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Category;

class CategoryRepository
{
	public function getAllCategorys()
	{
		$category_list = \Illuminate\Support\Facades\Cache::get('category_list');

		if (empty($category_list)) {
			$category_list = $this->getTree(0);
			\Illuminate\Support\Facades\Cache::put('category_list', $category_list, 60);
		}

		return $category_list;
	}

	public function getCategoryGetGoods($id)
	{
		$goods = \App\Models\Goods::select('goods_id', 'goods_sn', 'goods_name')->where('cat_id', $id)->get()->toArray();
		return $goods;
	}

	private function getTree($tree_id = 0, $top = 0)
	{
		$three_arr = array();
		$count = \App\Models\Category::where('parent_id', $tree_id)->where('is_show', 1)->count();
		if (0 < $count || $tree_id == 0) {
			$res = \App\Models\Category::select('cat_id', 'cat_name', 'touch_icon', 'parent_id', 'cat_alias_name', 'is_show')->where('parent_id', $tree_id)->where('is_show', 1)->with(array('goods' => function($query) {
				$query->select('goods_id', 'cat_id', 'goods_thumb')->where('is_on_sale', 1)->where('is_delete', 0)->orderby('sort_order', 'ASC')->orderby('goods_id', 'DESC');
			}))->orderby('sort_order', 'ASC')->orderby('cat_id', 'ASC')->get()->toArray();

			foreach ($res as $k => $row) {
				if ($row['is_show']) {
					$three_arr[$k]['id'] = $row['cat_id'];
					$three_arr[$k]['name'] = $row['cat_alias_name'] ? $row['cat_alias_name'] : $row['cat_name'];

					if (isset($row['goods'][0]['goods_thumb'])) {
						$three_arr[$k]['cat_img'] = !empty($row['touch_icon']) ? get_image_path($row['touch_icon']) : get_image_path($row['goods'][0]['goods_thumb']);
					}
					else {
						$three_arr[$k]['cat_img'] = !empty($row['touch_icon']) ? get_image_path($row['touch_icon']) : get_image_path();
					}

					$three_arr[$k]['haschild'] = 0;
				}

				if (isset($row['cat_id'])) {
					$child_tree = $this->getTree($row['cat_id']);

					if ($child_tree) {
						$three_arr[$k]['cat_id'] = $child_tree;
						$three_arr[$k]['haschild'] = 1;
					}
				}
			}
		}

		return $three_arr;
	}
}


?>
