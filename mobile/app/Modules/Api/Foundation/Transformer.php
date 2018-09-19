<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Api\Foundation;

abstract class Transformer
{
	public function transformCollection(array $map)
	{
		return array_map(array($this, 'transform'), $map);
	}

	abstract public function transform(array $map);
}


?>
