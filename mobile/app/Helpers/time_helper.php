<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
function gmtime()
{
	return time() - date('Z');
}

function server_timezone()
{
	if (function_exists('date_default_timezone_get')) {
		return date_default_timezone_get();
	}
	else {
		return date('Z') / 3600;
	}
}

function local_mktime($hour = NULL, $minute = NULL, $second = NULL, $month = NULL, $day = NULL, $year = NULL)
{
	$timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];
	$time = mktime($hour, $minute, $second, $month, $day, $year) - $timezone * 3600;
	return $time;
}

function local_date($format, $time = NULL)
{
	$timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];

	if ($time === NULL) {
		$time = gmtime();
	}
	else if ($time <= 0) {
		return '';
	}

	$time += $timezone * 3600;
	return date($format, $time);
}

function gmstr2time($str)
{
	$time = strtotime($str);

	if (0 < $time) {
		$time -= date('Z');
	}

	return $time;
}

function local_strtotime($str)
{
	$timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];
	$time = strtotime($str) - $timezone * 3600;
	return $time;
}

function local_gettime($timestamp = NULL)
{
	$tmp = local_getdate($timestamp);
	return $tmp[0];
}

function local_getdate($timestamp = NULL)
{
	$timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];

	if ($timestamp === NULL) {
		$timestamp = time();
	}

	$gmt = $timestamp - date('Z');
	$local_time = $gmt + $timezone * 3600;
	return getdate($local_time);
}

function get_status($starttime, $endtime)
{
	$nowtime = gmtime();
	if (!empty($starttime) && !empty($endtime)) {
		if ($nowtime < $starttime) {
			$result = 0;
		}
		else {
			if ($starttime < $nowtime && $nowtime < $endtime) {
				$result = 1;
			}
			else if ($endtime < $nowtime) {
				$result = 2;
			}
		}

		return $result;
	}

	return 0;
}


?>
