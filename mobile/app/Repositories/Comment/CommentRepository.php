<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Comment;

class CommentRepository
{
	public function orderAppraiseAdd($args)
	{
		$commemt = new \App\Models\Comment();

		foreach ($args as $k => $v) {
			$commemt->$k = $v;
		}

		return $commemt->save();
	}
}


?>
