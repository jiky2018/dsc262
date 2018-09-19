<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Extensions;

class Pinyin
{
	public function output($str, $utf8 = true)
	{
		$pinyin = new \Overtrue\Pinyin\Pinyin();
		return $pinyin->convert($str);
	}
}


?>
