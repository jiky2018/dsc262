<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Libraries;

class Smtp
{
	public $connection;
	public $recipients;
	public $headers;
	public $timeout;
	public $errors;
	public $status;
	public $body;
	public $from;
	public $host;
	public $port;
	public $helo;
	public $auth;
	public $user;
	public $pass;

	public function __construct($params = array())
	{
		if (!defined('CRLF')) {
			define('CRLF', "\r\n", true);
		}

		$this->timeout = 10;
		$this->status = SMTP_STATUS_NOT_CONNECTED;
		$this->host = 'localhost';
		$this->port = 25;
		$this->auth = false;
		$this->user = '';
		$this->pass = '';
		$this->errors = array();

		foreach ($params as $key => $value) {
			$this->$key = $value;
		}

		$this->helo = $this->host;
		$this->auth = '' == $this->user ? false : true;
	}

	public function connect($params = array())
	{
		if (!isset($this->status)) {
			$obj = new Smtp($params);

			if ($obj->connect()) {
				$obj->status = SMTP_STATUS_CONNECTED;
			}

			return $obj;
		}
		else {
			if (!empty($GLOBALS['_CFG']['smtp_ssl'])) {
				$this->host = 'ssl://' . $this->host;
			}

			$this->connection = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

			if ($this->connection === false) {
				$this->errors[] = 'Access is denied.';
				return false;
			}

			@socket_set_timeout($this->connection, 0, 250000);
			$greeting = $this->get_data();

			if (is_resource($this->connection)) {
				$this->status = 2;
				return $this->auth ? $this->ehlo() : $this->helo();
			}
			else {
				log_write($errstr, __FILE__, 95);
				$this->errors[] = 'Failed to connect to server: ' . $errstr;
				return false;
			}
		}
	}

	public function send($params = array())
	{
		foreach ($params as $key => $value) {
			$this->$key = $value;
		}

		if ($this->is_connected()) {
			if ($this->auth) {
				if (!$this->auth()) {
					return false;
				}
			}

			$this->mail($this->from);

			if (is_array($this->recipients)) {
				foreach ($this->recipients as $value) {
					$this->rcpt($value);
				}
			}
			else {
				$this->rcpt($this->recipients);
			}

			if (!$this->data()) {
				return false;
			}

			$headers = str_replace(CRLF . '.', CRLF . '..', trim(implode(CRLF, $this->headers)));
			$body = str_replace(CRLF . '.', CRLF . '..', $this->body);
			$body = (substr($body, 0, 1) == '.' ? '.' . $body : $body);
			$this->send_data($headers);
			$this->send_data('');
			$this->send_data($body);
			$this->send_data('.');
			return substr($this->get_data(), 0, 3) === '250';
		}
		else {
			$this->errors[] = 'Not connected!';
			return false;
		}
	}

	public function helo()
	{
		if (is_resource($this->connection) && $this->send_data('HELO ' . $this->helo) && (substr($error = $this->get_data(), 0, 3) === '250')) {
			return true;
		}
		else {
			$this->errors[] = 'HELO command failed, output: ' . trim(substr($error, 3));
			return false;
		}
	}

	public function ehlo()
	{
		if (is_resource($this->connection) && $this->send_data('EHLO ' . $this->helo) && (substr($error = $this->get_data(), 0, 3) === '250')) {
			return true;
		}
		else {
			$this->errors[] = 'EHLO command failed, output: ' . trim(substr($error, 3));
			return false;
		}
	}

	public function auth()
	{
		if (is_resource($this->connection) && $this->send_data('AUTH LOGIN') && (substr($error = $this->get_data(), 0, 3) === '334') && $this->send_data(base64_encode($this->user)) && (substr($error = $this->get_data(), 0, 3) === '334') && $this->send_data(base64_encode($this->pass)) && (substr($error = $this->get_data(), 0, 3) === '235')) {
			return true;
		}
		else {
			$this->errors[] = 'AUTH command failed: ' . trim(substr($error, 3));
			return false;
		}
	}

	public function mail($from)
	{
		if ($this->is_connected() && $this->send_data('MAIL FROM:<' . $from . '>') && (substr($this->get_data(), 0, 2) === '250')) {
			return true;
		}
		else {
			return false;
		}
	}

	public function rcpt($to)
	{
		if ($this->is_connected() && $this->send_data('RCPT TO:<' . $to . '>') && (substr($error = $this->get_data(), 0, 2) === '25')) {
			return true;
		}
		else {
			$this->errors[] = trim(substr($error, 3));
			return false;
		}
	}

	public function data()
	{
		if ($this->is_connected() && $this->send_data('DATA') && (substr($error = $this->get_data(), 0, 3) === '354')) {
			return true;
		}
		else {
			$this->errors[] = trim(substr($error, 3));
			return false;
		}
	}

	public function is_connected()
	{
		return is_resource($this->connection) && ($this->status === SMTP_STATUS_CONNECTED);
	}

	public function send_data($data)
	{
		if (is_resource($this->connection)) {
			return fwrite($this->connection, $data . CRLF, strlen($data) + 2);
		}
		else {
			return false;
		}
	}

	public function get_data()
	{
		$return = '';
		$line = '';

		if (is_resource($this->connection)) {
			while ((strpos($return, CRLF) === false) || ($line[3] !== ' ')) {
				$line = fgets($this->connection, 512);
				$return .= $line;
			}

			return trim($return);
		}
		else {
			return '';
		}
	}

	public function error_msg()
	{
		if (!empty($this->errors)) {
			$len = count($this->errors) - 1;
			return $this->errors[$len];
		}
		else {
			return '';
		}
	}
}

define('SMTP_STATUS_NOT_CONNECTED', 1, true);
define('SMTP_STATUS_CONNECTED', 2, true);

?>
