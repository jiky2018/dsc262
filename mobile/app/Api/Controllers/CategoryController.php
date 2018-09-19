<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Controllers;

class CategoryController extends \App\Api\Foundation\Controller
{
	/** @var  $category */
	protected $category;

	public function __construct(\App\Services\CategoryService $category)
	{
		parent::__construct();
		$this->category = $category;
	}

	public function categoryList()
	{
		$data = $this->category->categoryList();
		return $this->apiReturn($data);
	}

	public function categoryDetail(\Illuminate\Http\Request $request)
	{
		$pattern = array('id' => 'required|integer');
		$this->validate($request, $pattern);
		$data = $this->category->categoryDetail($request->get('id'));
		return $this->apiReturn($data);
	}
}

?>
