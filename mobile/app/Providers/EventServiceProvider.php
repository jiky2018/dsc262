<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Providers;

class EventServiceProvider extends \Laravel\Lumen\Providers\EventServiceProvider
{
	/**
     * The event listener mappings for the application.
     *
     * @var array
     */
	protected $listen = array(
		'App\\Events\\ExampleEvent' => array('App\\Listeners\\EventListener')
		);
}

?>
