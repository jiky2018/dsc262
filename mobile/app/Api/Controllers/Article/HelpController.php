<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Controllers\Article;

class HelpController extends \App\Api\Controllers\Controller
{
	/**
     * @var CategoryRepository
     */
	protected $category;
	/**
     * @var ArticleRepository
     */
	protected $article;

	public function __construct(\App\Repositories\Article\CategoryRepository $category, \App\Repositories\Article\ArticleRepository $article)
	{
		$this->category = $category;
		$this->article = $article;
	}

	public function actionList(array $args)
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
}

?>
