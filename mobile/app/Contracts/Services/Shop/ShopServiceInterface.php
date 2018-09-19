<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Contracts\Services\Shop;

interface ShopServiceInterface
{
	public function create();

	public function get($id);

	public function update();

	public function getStatus();

	public function search($attributes);

	public function createAddress();

	public function getAddress();

	public function updateAddress();

	public function deleteAddress();

	public function searchAddress();

	public function createStore();

	public function getStore();

	public function updateStore();

	public function deleteStore();

	public function searchStore();

	public function storeSetting();

	public function getStoreGoods();

	public function updateStoreGoods();

	public function uploadMaterials();
}


?>
