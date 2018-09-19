<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Controllers;

class TradeController extends \App\Api\Foundation\Controller
{
	protected $trade;
	protected $tradeTransformer;

	public function __construct(\App\Repositories\Trade\TradeRepository $trade, \App\Api\V2\Trade\Transformer\TradeTransformer $tradeTransformer)
	{
		parent::__construct();
		$this->trade = $trade;
		$this->tradeTransformer = $tradeTransformer;
	}

	public function actionGet()
	{
	}
}

?>
