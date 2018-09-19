<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Controllers\Wx;

class CategoryController extends \App\Api\Controllers\Controller
{
	private $categoryService;

	public function __construct(\App\Services\Category\CategoryService $categoryService)
	{
		$this->categoryService = $categoryService;
	}

	public function index()
	{
		$list = $this->categoryService->categoryList();
		return $this->apiReturn($list);
	}
}

?>
