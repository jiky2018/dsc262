<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class RequestCheckUtil
{
	static public function checkNotNull($value, $fieldName)
	{
		if (self::checkEmpty($value)) {
			throw new Exception('client-check-error:Missing Required Arguments: ' . $fieldName, 40);
		}
	}

	static public function checkMaxLength($value, $maxLength, $fieldName)
	{
		if (!self::checkEmpty($value) && ($maxLength < mb_strlen($value, 'UTF-8'))) {
			throw new Exception('client-check-error:Invalid Arguments:the length of ' . $fieldName . ' can not be larger than ' . $maxLength . '.', 41);
		}
	}

	static public function checkMaxListSize($value, $maxSize, $fieldName)
	{
		if (self::checkEmpty($value)) {
			return NULL;
		}

		$list = preg_split('/,/', $value);

		if ($maxSize < count($list)) {
			throw new Exception('client-check-error:Invalid Arguments:the listsize(the string split by ",") of ' . $fieldName . ' must be less than ' . $maxSize . ' .', 41);
		}
	}

	static public function checkMaxValue($value, $maxValue, $fieldName)
	{
		if (self::checkEmpty($value)) {
			return NULL;
		}

		self::checkNumeric($value, $fieldName);

		if ($maxValue < $value) {
			throw new Exception('client-check-error:Invalid Arguments:the value of ' . $fieldName . ' can not be larger than ' . $maxValue . ' .', 41);
		}
	}

	static public function checkMinValue($value, $minValue, $fieldName)
	{
		if (self::checkEmpty($value)) {
			return NULL;
		}

		self::checkNumeric($value, $fieldName);

		if ($value < $minValue) {
			throw new Exception('client-check-error:Invalid Arguments:the value of ' . $fieldName . ' can not be less than ' . $minValue . ' .', 41);
		}
	}

	static protected function checkNumeric($value, $fieldName)
	{
		if (!is_numeric($value)) {
			throw new Exception('client-check-error:Invalid Arguments:the value of ' . $fieldName . ' is not number : ' . $value . ' .', 41);
		}
	}

	static public function checkEmpty($value)
	{
		if (!isset($value)) {
			return true;
		}

		if ($value === NULL) {
			return true;
		}

		if (is_array($value) && (count($value) == 0)) {
			return true;
		}

		if (is_string($value) && (trim($value) === '')) {
			return true;
		}

		return false;
	}
}


?>
