<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
namespace App\Modules\Api\Controllers;

class CategoryController extends \App\Modules\Api\Foundation\Controller
{
	private $categoryService;

	public function __construct(\App\Services\CategoryService $categoryService)
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
