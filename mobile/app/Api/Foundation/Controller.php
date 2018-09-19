<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Foundation;

class Controller extends \App\Modules\Base\Controllers\Frontend
{
	protected function apiReturn($data, $code = 0)
	{
		return array('code' => $code, 'data' => $data);
	}

	protected function validate($args, $pattern)
	{
		$validator = Validation::createValidation();
		$rules = Validation::transPattern($pattern);

		if ($validator->validate($rules)->create($args) === false) {
			return $validator->getError();
		}
		else {
			return true;
		}
	}
}

?>
