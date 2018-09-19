<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class prism_provider
{
	public $url = '';
	public $interface = '';
	public $sandbox_url = '';
	public $resource_content_types = 'text/json';
	public $prefix = '';
	public $summary = '';
	public $apis;
	public $mode = 'params';
	public $param_name = 'method';
	public $models = array();
	public $auto_config_url = '';
	public $config_values = array();
	public $global_params = array();
	private $_validation;
	private $_handler_object;

	public function __construct($url = '')
	{
		$this->url = $url;
	}

	public function add($id, $api)
	{
		$api->set_id($id);
		$this->apis[$id] = $api;
	}

	public function add_config($name, $desc = '', $default = '', $is_secret = false)
	{
		$this->config_values[] = array('name' => $name, 'desc' => $desc, 'default' => $default, 'is_secret' => $is_secret);
	}

	public function add_global_params($name, $desc = '', $default = '', $is_secret = false)
	{
		$this->config_values[] = array('name' => $name, 'param_type' => $param_type, 'value_type' => $value_type, 'format' => $format);
	}

	public function set_url($url)
	{
		$this->url = $url;
	}

	public function set_validation($validation)
	{
		if (is_string($validation)) {
			$reflect = new ReflectionClass($validation);
			$args = func_get_args();
			array_shift($args);
			$validation = $reflect->newInstanceArgs($args);
		}

		if (is_object($validation) && $a instanceof prism_validation) {
			$this->config_values = $validation->get_config();
			$this->global_params = $validation->get_global_params();
			$this->_validatio = &$validation;
			return true;
		}

		return false;
	}

	public function get_json()
	{
		return json_encode($this);
	}

	public function output_json()
	{
		$this->_json_header();
		echo $this->get_json();
	}

	public function handler($handler_object)
	{
		$this->_handler_object = $handler_object;
	}

	public function dispatch($dispatch_key)
	{
		if ($this->validation) {
			$this->validation->validate();
		}

		$params = $_REQUEST;

		if ($this->mode == 'params') {
			$api = $this->apis[$dispatch_key];
		}

		if ($api) {
			$id = $api->get_id();

			if ($this->_handler_object) {
				$this->_handler_object;

				if (is_callable(array($this->_handler_object, $id))) {
					$result = $this->_handler_object->$id($params);
					$this->_response($result);
				}
			}

			if (!$called) {
				$func = $api->get_handler();

				if (is_callable($func)) {
					$result = $func($params);
					$this->_response($result);
				}
			}
		}
	}

	private function _json_header()
	{
		header('Content-Type: text/json;charset=utf8');
	}

	private function _response($data)
	{
		header('');
		echo json_encode($data);
	}
}

class prism_api
{
	public $path = '';
	public $method = array('POST');
	public $summary = '';
	public $notes = '';
	public $require_oauth = '';
	public $backend_timeout_second = '';
	public $params = array();
	public $response = '';
	public $exception = array();
	private $id = '';
	private $handle_func;

	public function __construct($params)
	{
		$this->params = func_get_args();
		$this->id = array_shift($this->params);
	}

	public function add_exception($code, $message, $http_code)
	{
		$this->exception[] = array('code' => $code, 'message' => $message, 'http_code' => $http_code);
	}

	public function handler($handle_func)
	{
		$this->handle_func = $handle_func;
		return $this;
	}

	public function get_handler()
	{
		return $this->handle_func;
	}

	public function set_id($id)
	{
		$this->id = $id;
	}

	public function get_id()
	{
		return $this->id;
	}
}

class prism_params
{
	public $name;
	public $desc;
	public $required;
	public $type;
	public $param_type;

	public function __construct($name, $desc = '', $required = false, $type = 'string', $param_type = 'request')
	{
		$this->name = $name;
		$this->desc = $desc;
		$this->required = $required;
		$this->type = $type;
		$this->param_type = $param_type;
	}
}

interface prism_validation
{
	public function get_config();

	public function get_global_params();

	public function validate();
}

function prism_api()
{
	$reflect = new ReflectionClass('prism_api');
	return $reflect->newInstanceArgs(func_get_args());
}

function prism_params()
{
	$reflect = new ReflectionClass('prism_params');
	return $reflect->newInstanceArgs(func_get_args());
}

class prism_sign_validation implements prism_validation
{
	private $f_get_secret_by_key;

	public function __construct($func_get_secret_by_key)
	{
		$this->get_secret_by_key = $func_get_secret_by_key;
	}

	public function get_config()
	{
	}

	public function get_global_params()
	{
	}

	public function validate()
	{
	}
}

?>
