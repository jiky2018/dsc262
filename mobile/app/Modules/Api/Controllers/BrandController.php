<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Api\Controllers;

class BrandController extends \App\Modules\Api\Foundation\Controller
{
	/** @var  $brand */
	protected $brand;
	/** @var $brandTransformer */
	protected $brandTransformer;

	public function __construct(\App\Repositories\Brand\BrandRepository $brand, \App\Modules\Api\Transformers\BrandTransformer $brandTransformer)
	{
		parent::__construct();
		$this->brand = $brand;
		$this->brandTransformer = $brandTransformer;
	}

	public function actionList()
	{
		$data = $this->brand->getAllBrands();
		$this->apiReturn($data);
	}

	public function actionGet($id)
	{
		$data = $this->brand->getBrandDetail($id);
		$this->apiReturn($data);
	}
}

?>
