<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_shop_license()
{
	$sql = "SELECT code, value\r\n            FROM " . $GLOBALS['ecs']->table('shop_config') . "\r\n            WHERE code IN ('certificate_id', 'token', 'certi')\r\n            LIMIT 0,3";
	$license_info = $GLOBALS['db']->getAll($sql);
	$license_info = is_array($license_info) ? $license_info : array();
	$license = array();

	foreach ($license_info as $value) {
		$license[$value['code']] = $value['value'];
	}

	return $license;
}

function make_shopex_ac($post_params, $token)
{
	if (!is_array($post_params)) {
		return NULL;
	}

	ksort($post_params);
	$str = '';

	foreach ($post_params as $key => $value) {
		if ($key != 'certi_ac') {
			$str .= $value;
		}
	}

	return md5($str . $token);
}

function exchange_shop_license($certi, $license, $use_lib = 0)
{
	if (!is_array($certi)) {
		return array();
	}

	$params = '';

	foreach ($certi as $key => $value) {
		$params .= '&' . $key . '=' . $value;
	}

	$params = trim($params, '&');
	$transport = new \App\Libraries\Transport();
	$request = $transport->request($license['certi'], $params, 'POST');
	$request_str = json_str_iconv($request['body']);

	if (empty($use_lib)) {
		$json = new \App\Libraries\JSON();
		$request_arr = $json->decode($request_str, 1);
	}
	else {
		$request_arr = json_decode($request_str, 1);
	}

	return $request_arr;
}

function process_login_license($cert_auth)
{
	if (!is_array($cert_auth)) {
		return array();
	}

	$cert_auth['auth_str'] = trim($cert_auth['auth_str']);

	if (!empty($cert_auth['auth_str'])) {
		$cert_auth['auth_str'] = $GLOBALS['_LANG']['license_' . $cert_auth['auth_str']];
	}

	$cert_auth['auth_type'] = trim($cert_auth['auth_type']);

	if (!empty($cert_auth['auth_type'])) {
		$cert_auth['auth_type'] = $GLOBALS['_LANG']['license_' . $cert_auth['auth_type']];
	}

	return $cert_auth;
}

function license_login($certi_added = '')
{
	$certi['certi_app'] = '';
	$certi['app_id'] = 'ecshop_b2c';
	$certi['app_instance_id'] = '';
	$certi['version'] = LICENSE_VERSION;
	$certi['shop_version'] = VERSION;
	$certi['certi_url'] = sprintf($GLOBALS['ecs']->url());
	$certi['certi_session'] = session_id();
	$certi['certi_validate_url'] = sprintf($GLOBALS['ecs']->url() . 'certi.php');
	$certi['format'] = 'json';
	$certi['certificate_id'] = '';
	$certi_back['succ'] = 'succ';
	$certi_back['fail'] = 'fail';
	$return_array = array();

	if (is_array($certi_added)) {
		foreach ($certi_added as $key => $value) {
			$certi[$key] = $value;
		}
	}

	$license = get_shop_license();
	if (!empty($license['certificate_id']) && !empty($license['token']) && !empty($license['certi'])) {
		$certi['certi_app'] = 'certi.login';
		$certi['app_instance_id'] = 'cert_auth';
		$certi['certificate_id'] = $license['certificate_id'];
		$certi['certi_ac'] = make_shopex_ac($certi, $license['token']);
		$request_arr = exchange_shop_license($certi, $license);
		if (is_array($request_arr) && $request_arr['res'] == $certi_back['succ']) {
			$return_array['flag'] = 'login_succ';
			$return_array['request'] = $request_arr;
		}
		else {
			if (is_array($request_arr) && $request_arr['res'] == $certi_back['fail']) {
				$return_array['flag'] = 'login_fail';
				$return_array['request'] = $request_arr;
			}
			else {
				$return_array['flag'] = 'login_ping_fail';
				$return_array['request'] = array('res' => 'fail');
			}
		}
	}
	else {
		$return_array['flag'] = 'login_param_fail';
		$return_array['request'] = array('res' => 'fail');
	}

	return $return_array;
}

function license_reg($certi_added = '')
{
	$certi['certi_app'] = '';
	$certi['app_id'] = 'ecshop_b2c';
	$certi['app_instance_id'] = '';
	$certi['version'] = LICENSE_VERSION;
	$certi['shop_version'] = VERSION;
	$certi['certi_url'] = sprintf($GLOBALS['ecs']->url());
	$certi['certi_session'] = session_id();
	$certi['certi_validate_url'] = sprintf($GLOBALS['ecs']->url() . 'certi.php');
	$certi['format'] = 'json';
	$certi['certificate_id'] = '';
	$certi_back['succ'] = 'succ';
	$certi_back['fail'] = 'fail';
	$return_array = array();

	if (is_array($certi_added)) {
		foreach ($certi_added as $key => $value) {
			$certi[$key] = $value;
		}
	}

	$license = get_shop_license();
	$certi['certi_app'] = 'certi.reg';
	$certi['certi_ac'] = make_shopex_ac($certi, '');
	unset($certi['certificate_id']);
	$request_arr = exchange_shop_license($certi, $license);
	if (is_array($request_arr) && $request_arr['res'] == $certi_back['succ']) {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('shop_config') . "\r\n                SET value = '" . $request_arr['info']['certificate_id'] . '\' WHERE code = \'certificate_id\'';
		$GLOBALS['db']->query($sql);
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('shop_config') . "\r\n                SET value = '" . $request_arr['info']['token'] . '\' WHERE code = \'token\'';
		$GLOBALS['db']->query($sql);
		$return_array['flag'] = 'reg_succ';
		$return_array['request'] = $request_arr;
		clear_cache_files();
	}
	else {
		if (is_array($request_arr) && $request_arr['res'] == $certi_back['fail']) {
			$return_array['flag'] = 'reg_fail';
			$return_array['request'] = $request_arr;
		}
		else {
			$return_array['flag'] = 'reg_ping_fail';
			$return_array['request'] = array('res' => 'fail');
		}
	}

	return $return_array;
}


?>
