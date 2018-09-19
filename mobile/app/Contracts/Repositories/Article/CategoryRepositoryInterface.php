<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Contracts\Repositories\Article;

interface CategoryRepositoryInterface
{
	public function all($cat_id, $columns, $offset);

	public function detail($cat_id, $columns);

	public function create(array $data);

	public function update(array $data);

	public function delete($id);
}


?>
