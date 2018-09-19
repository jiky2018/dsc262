<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Controllers\Article;

class ArticleController extends \App\Api\Controllers\Controller
{
	/**
     * @var ArticleRepository
     */
	protected $article;
	/**
     * @var ArticleTransformer
     */
	protected $articleTransformer;

	public function __construct(\App\Repositories\Article\ArticleRepository $article, \App\Api\Transformers\ArticleTransformer $articleTransformer)
	{
		$this->article = $article;
		$this->articleTransformer = $articleTransformer;
	}

	public function actionList(array $args)
	{
		$result = $this->article->all($args['id']);
		$result['data'] = $this->articleTransformer->transformCollection($result['data']);
		return $result;
	}

	public function actionGet(array $args)
	{
		$result = $this->article->detail($args['id']);
		$result = $this->articleTransformer->transform($result);
		return $result;
	}

	public function actionAgreement(array $args)
	{
		return $this->article->detail(array('cat_id' => '-1'));
	}
}

?>
