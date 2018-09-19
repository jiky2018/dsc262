<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Exceptions;

class Handler extends \Laravel\Lumen\Exceptions\Handler
{
	/**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
	protected $dontReport = array('Illuminate\\Auth\\Access\\AuthorizationException', 'Symfony\\Component\\HttpKernel\\Exception\\HttpException', 'Illuminate\\Database\\Eloquent\\ModelNotFoundException', 'Illuminate\\Validation\\ValidationException');

	public function report(\Exception $e)
	{
		parent::report($e);
	}

	public function render($request, \Exception $e)
	{
		return parent::render($request, $e);
	}
}

?>
