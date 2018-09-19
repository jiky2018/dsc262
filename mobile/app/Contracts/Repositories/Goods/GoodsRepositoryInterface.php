<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Contracts\Repositories\Goods;

interface GoodsRepositoryInterface
{
	public function create(array $data);

	public function get($id);

	public function update(array $data);

	public function delete($id);

	public function search(array $data);

	public function sku($id);

	public function skuAdd();
}


?>
