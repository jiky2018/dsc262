<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Article;

class CategoryRepository
{
	public function all($cat_id = 0, $columns = array('*'), $size = 100)
	{
		if (is_array($cat_id)) {
			$field = key($cat_id);
			$value = $cat_id[$field];
			$model = \App\Models\ArticleCat::where($field, '=', $value)->where('parent_id', 0);
		}
		else {
			$model = \App\Models\ArticleCat::where('parent_id', $cat_id);
		}

		$category = $model->orderBy('sort_order')->orderBy('cat_id')->paginate($size, $columns)->toArray();

		foreach ($category['data'] as $key => $val) {
			$category['data'][$key]['child'] = $this->article_category_child($val['id']);
		}

		return $category;
	}

	public function article_category_child($parent_id)
	{
		$res = \App\Models\ArticleCat::where('parent_id', $parent_id)->get()->toArray();
		$arr = array();

		foreach ($res as $key => $row) {
			$arr[$key]['cat_id'] = $row['id'];
			$arr[$key]['cat_name'] = $row['cat_name'];
			$arr[$key]['url'] = url('article/index/index', array('cat_id' => $row['id']));
			$arr[$key]['child'] = $this->article_category_child($row['cat_id']);
		}

		return $arr;
	}

	public function detail($cat_id, $columns = array('*'))
	{
		if (is_array($cat_id)) {
			$field = key($cat_id);
			$value = $cat_id[$field];
			$model = \App\Models\ArticleCat::where($field, '=', $value)->first($columns);
		}
		else {
			$model = \App\Models\ArticleCat::find($cat_id, $columns);
		}

		return $model->toArray();
	}

	public function create(array $data)
	{
		return false;
	}

	public function update(array $data)
	{
		return false;
	}

	public function delete($id)
	{
		return false;
	}
}


?>
