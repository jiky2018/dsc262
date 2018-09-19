<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function appendParam($returnStr, $paramId, $paramValue)
{
	if ($returnStr != '') {
		if ($paramValue != '') {
			$returnStr .= '&' . $paramId . '=' . $paramValue;
		}
	}
	else if ($paramValue != '') {
		$returnStr = $paramId . '=' . $paramValue;
	}

	return $returnStr;
}

$_GET = get_request_filter($_GET, 2);
$key = '65ZS4C5WYKKLLGJN';
$version = addslashes($_GET['version']);
$signType = addslashes($_GET['signType']);
$merchantMbrCode = addslashes($_GET['merchantMbrCode']);
$requestId = addslashes($_GET['requestId']);
$userId = addslashes($_GET['userId']);
$userEmail = addslashes($_GET['userEmail']);
$userName = addslashes($_GET['userName']);
$orgName = addslashes($_GET['orgName']);
$ext1 = addslashes($_GET['ext1']);
$ext2 = addslashes($_GET['ext2']);
$applyResult = addslashes($_GET['applyResult']);
$errorCode = addslashes($_GET['errorCode']);
$signMsg = addslashes($_GET['signMsg']);
$$signMsgVal = '';
$signMsgVal = appendparam($signMsgVal, 'version', $version);
$signMsgVal = appendparam($signMsgVal, 'signType', $signType);
$signMsgVal = appendparam($signMsgVal, 'merchantMbrCode', $merchantMbrCode);
$signMsgVal = appendparam($signMsgVal, 'requestId', $requestId);
$signMsgVal = appendparam($signMsgVal, 'userId', $userId);
$signMsgVal = appendparam($signMsgVal, 'userEmail', $userEmail);
$signMsgVal = appendparam($signMsgVal, 'userName', urlencode($userName));
$signMsgVal = appendparam($signMsgVal, 'orgName', urlencode($orgName));
$signMsgVal = appendparam($signMsgVal, 'ext1', urlencode($ext1));
$signMsgVal = appendparam($signMsgVal, 'ext2', urlencode($ext2));
$signMsgVal = appendparam($signMsgVal, 'applyResult', $applyResult);
$signMsgVal = appendparam($signMsgVal, 'errorCode', $errorCode);
$signMsgVal = appendparam($signMsgVal, 'key', $key);
$mysignMsg = strtoupper(md5($signMsgVal));

if ($mysignMsg == $signMsg) {
	$status = '1';
	$signMsgVal = '';
	$signMsgVal = appendparam($signMsgVal, 'version', $version);
	$signMsgVal = appendparam($signMsgVal, 'signType', $signType);
	$signMsgVal = appendparam($signMsgVal, 'merchantMbrCode', $merchantMbrCode);
	$signMsgVal = appendparam($signMsgVal, 'requestId', $requestId);
	$signMsgVal = appendparam($signMsgVal, 'userId', $userId);
	$signMsgVal = appendparam($signMsgVal, 'status', $status);
	$reParam = $signMsgVal;
	$signMsgVal = appendparam($signMsgVal, 'key', key);
	$signMsg = strtoupper(md5($signMsgVal));
	$reParam .= '&signMsg=' . $signMsg;
	echo $reParam;
}
else {
	echo '验证错误';
}

?>
