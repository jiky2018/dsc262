<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Api\Transformers;

class ArticleTransformer
{
	public function transform(\App\Models\Article $article)
	{
		return array('id' => $article->article_id, 'title' => $article->title);
	}
}


?>
