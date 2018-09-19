<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Api\Foundation;

class Validation
{
	static private $dataType = array('integer', 'string');

	static public function createValidation()
	{
		return M();
	}

	static public function transPattern($pattern)
	{
		if (!is_array($pattern)) {
			exit(json_encode(array('code' => 1, 'msg' => '验证格式不正确，以数组组合规则')));
		}

		$rules = array();

		foreach ($pattern as $k => $v) {
			if (!is_string($k) || !is_string($v)) {
				exit(json_encode(array('code' => 1, 'msg' => '验证规则格式不正确，规则应为字符串')));
			}

			$rules = array_merge($rules, self::explodePattern($k, $v));
		}

		return $rules;
	}

	static private function explodePattern($name, $pattern)
	{
		$patterns = explode('|', $pattern);
		array_slice($patterns, 0, 3);
		$rules = array();
		$ruleRequires = self::ruleRequires($patterns);

		foreach ($patterns as $p) {
			$errorMsg = $name . self::errorMsg($p);
			$ruleContent = self::generageRule($p);

			if (is_array($ruleContent)) {
				if (count($ruleContent) == 2) {
					$rules[] = array($name, $ruleContent[1], $errorMsg, $ruleRequires, $ruleContent[0]);
				}
				else if (count($ruleContent) == 3) {
					$rules[] = array(
	$name,
	$ruleContent[0],
	$errorMsg,
	$ruleRequires,
	$ruleContent[1],
	3,
	array($ruleContent[2])
	);
				}
			}
			else if (is_string($ruleContent)) {
				$rules[] = array($name, $ruleContent, $errorMsg, $ruleRequires);
			}
		}

		return $rules;
	}

	static private function generageRule($pattern)
	{
		$rule = '';

		if ($pattern == 'required') {
			$rule = 'require';
		}
		else if (strpos($pattern, ':')) {
			$rule = self::attachRequires($pattern);
		}
		else {
			if (!strstr($pattern, ':') && in_array($pattern, self::$dataType)) {
				$rule[0] = 'App\\Api\\Foundation\\Validation::verifyType';
				$rule[1] = 'function';
				$rule[2] = $pattern;
			}
		}

		return $rule;
	}

	static private function errorMsg($rule)
	{
		if ($rule == 'required') {
			$errorMsg = ': is required';
		}
		else {
			if (strstr($rule, 'min') || strstr($rule, 'max')) {
				$errorMsg = ': is out of range';
			}
			else {
				$errorMsg = ': datatype is wrong';
			}
		}

		return $errorMsg;
	}

	static private function ruleRequires($patterns)
	{
		if (in_array('required', $patterns)) {
			return 1;
		}

		return 2;
	}

	static private function attachRequires($pattern)
	{
		$patterns = explode(':', $pattern);
		$return = array();

		switch ($patterns[0]) {
		case 'in':
			$return[0] = 'in';
			$return[1] = explode(',', $patterns[1]);
			break;

		case 'min':
			$return[0] = 'min';
			$return[1] = explode(',', $patterns[1]);
			break;

		case 'max':
			$return[0] = 'max';
			$return[1] = explode(',', $patterns[1]);
			break;
		}

		return $return;
	}

	static public function verifyType($str, $type)
	{
		if ($type === 'integer') {
			if ($str === '0') {
				return true;
			}

			$originLen = strlen($str);
			$str = (int) $str;
			$parLen = strlen($str);
			if (empty($str) || ($originLen !== $parLen)) {
				return false;
			}
		}

		return $type === gettype($str);
	}
}


?>
