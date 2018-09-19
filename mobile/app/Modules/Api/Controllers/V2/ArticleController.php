<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Api\Controllers\V2;

class ArticleController extends \App\Modules\Api\Foundation\Controller
{
	/**
     * @var ArticleRepository
     */
	protected $article;
	/**
     * @var CategoryRepository
     */
	protected $category;
	/**
     * @var ArticleTransformer
     */
	protected $articleTransformer;

	public function __construct(\App\Repositories\Article\ArticleRepository $article, \App\Repositories\Article\CategoryRepository $category, \App\Modules\Api\Transformers\ArticleTransformer $articleTransformer)
	{
		parent::__construct();
		$this->article = $article;
		$this->category = $category;
		$this->articleTransformer = $articleTransformer;
	}

	public function actionCategory($id = NULL)
	{
		if (is_null($id)) {
			$data = $this->category->all();
		}
		else {
			$data = $this->category->detail($id);
		}

		$this->resp($data);
	}

	public function actionList(array $args)
	{
		$result = $this->article->all($args['id']);
		$data = $this->articleTransformer->transformCollection($result['data']);
		$this->apiReturn($data);
	}

	public function actionGet(array $args)
	{
		$result = $this->article->detail($args['id']);
		$result = $this->articleTransformer->transform($result);
		return $result;
	}

	public function actionAbout()
	{
		$data = $this->article->all(2);
		return $this->resp($data);
	}

	public function actionHelp()
	{
		$help = S('shop_help');

		if (!$help) {
			$help = array();
			$intro = $this->category->detail(array('cat_type' => INFO_CAT), array('cat_id', 'cat_name'));
			$intro['list'] = $this->article->all($intro['id'], array('title'));
			$help[] = $intro;
			$list = $this->category->all(array('cat_type' => HELP_CAT), array('cat_id', 'cat_name'));

			foreach ($list['data'] as $key => $item) {
				$item['list'] = $this->article->all($item['id'], array('title'));
				$help[] = $item;
			}

			S('shop_help', $help);
		}

		return $help;
	}

	public function actionAgreement()
	{
		$data = $this->article->detail(array('cat_id' => '-1'));
		return $this->resp($data);
	}
}

?>
