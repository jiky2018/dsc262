<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function INPUT_I($name, $default = '', $filter = NULL, $datas = NULL)
{
	static $_PUT;

	if (strpos($name, '/')) {
		list($name, $type) = explode('/', $name, 2);
	}

	if (strpos($name, '.')) {
		list($method, $name) = explode('.', $name, 2);
	}
	else {
		$method = 'param';
	}

	switch (strtolower($method)) {
	case 'get':
		$input = &$_GET;
		break;

	case 'post':
		$input = &$_POST;
		break;

	case 'put':
		if (is_null($_PUT)) {
			parse_str(file_get_contents('php://input'), $_PUT);
		}

		$input = $_PUT;
		break;

	case 'param':
		switch ($_SERVER['REQUEST_METHOD']) {
		case 'POST':
			$input = $_POST;
			break;

		case 'PUT':
			if (is_null($_PUT)) {
				parse_str(file_get_contents('php://input'), $_PUT);
			}

			$input = $_PUT;
			break;

		default:
			$input = $_GET;
		}

		break;

	case 'path':
		$input = array();

		if (!empty($_SERVER['PATH_INFO'])) {
			$depr = '/';
			$input = explode($depr, trim($_SERVER['PATH_INFO'], $depr));
		}

		break;

	case 'request':
		$input = &$_REQUEST;
		break;

	case 'session':
		$input = &$_SESSION;
		break;

	case 'cookie':
		$input = &$_COOKIE;
		break;

	case 'server':
		$input = &$_SERVER;
		break;

	case 'globals':
		$input = &$GLOBALS;
		break;

	case 'data':
		$input = &$datas;
		break;

	default:
		return NULL;
	}

	if ('' == $name) {
		$data = $input;
		$filters = (isset($filter) ? $filter : 'htmlspecialchars');

		if ($filters) {
			if (is_string($filters)) {
				$filters = explode(',', $filters);
			}

			foreach ($filters as $filter) {
				$data = array_map_recursive($filter, $data);
			}
		}
	}
	else if (isset($input[$name])) {
		$data = $input[$name];
		$filters = (isset($filter) ? $filter : 'htmlspecialchars');

		if ($filters) {
			if (is_string($filters)) {
				if (0 === strpos($filters, '/')) {
					if (1 !== preg_match($filters, (string) $data)) {
						return isset($default) ? $default : NULL;
					}
				}
				else {
					$filters = explode(',', $filters);
				}
			}
			else if (is_int($filters)) {
				$filters = array($filters);
			}

			if (is_array($filters)) {
				foreach ($filters as $filter) {
					if (function_exists($filter)) {
						$data = (is_array($data) ? array_map_recursive($filter, $data) : $filter($data));
					}
					else {
						$data = filter_var($data, is_int($filter) ? $filter : filter_id($filter));

						if (false === $data) {
							return isset($default) ? $default : NULL;
						}
					}
				}
			}
		}

		if (!empty($type)) {
			switch (strtolower($type)) {
			case 'a':
				$data = (array) $data;
				break;

			case 'd':
				$data = (int) $data;
				break;

			case 'f':
				$data = (double) $data;
				break;

			case 'b':
				$data = (bool) $data;
				break;

			case 's':
			default:
				$data = (string) $data;
			}
		}
	}
	else {
		$data = (isset($default) ? $default : NULL);
	}

	return $data;
}

function array_map_recursive($filter, $data)
{
	$result = array();

	foreach ($data as $key => $val) {
		$result[$key] = is_array($val) ? array_map_recursive($filter, $val) : call_user_func($filter, $val);
	}

	return $result;
}


?>
