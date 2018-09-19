<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Middleware;

class ExampleMiddleware
{
	public function handle($request, \Closure $next)
	{
		return $next($request);
	}
}


?>
