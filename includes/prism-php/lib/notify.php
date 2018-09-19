<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class prism_notify
{
	const TextFrame = 1;
	const BinaryFrame = 2;
	const CloseFrame = 8;
	const PingFrame = 9;
	const PongFrame = 9;
	const action_publish = 1;
	const action_ack = 3;
	const action_consume = 2;

	private $client;
	private $connected = false;
	private $sock;
	private $_frames = array();
	private $messages = array();
	private $last_buf = '';
	private $consuming = false;

	public function __construct(&$client)
	{
		$this->client = $client;
		$this->client->register_handler(101, array($this, 'handle_upgrade'));
		$this->connect();
	}

	public function pub($routing_key, $message, $ctype = 'text/plain')
	{
		$size_routing_key = strlen($routing_key);
		$size_message = strlen($message);
		$size_ctype = strlen($ctype);
		$this->send(self::action_publish, pack('na*Na*na*', $size_routing_key, $routing_key, $size_message, $message, $size_ctype, $ctype));
	}

	public function close()
	{
		if ($this->connected) {
			fwrite($this->sock, $this->encode(self::CloseFrame));
			fclose($this->sock);
			$this->connected = false;
		}
	}

	private function consume()
	{
		if (!$this->consuming) {
			$this->send(self::action_consume);
			$this->consuming = true;
		}
	}

	private function send($type, $message = '')
	{
		if (!$this->connected || !@fwrite($this->sock, $this->encode(self::BinaryFrame, pack('ca*', $type, $message)))) {
			$this->connected = false;
			throw new prism_exception('websocket is not connected');
		}
	}

	public function ack($tid)
	{
		return $this->send(prism_notify::action_ack, $tid);
	}

	public function get()
	{
		if (!$this->consuming) {
			$this->consume();
		}

		while (!isset($this->messages[0])) {
			$this->recv_message();
		}

		return new prism_message($this, array_shift($this->messages));
	}

	public function handle_upgrade($c, $sock)
	{
		$this->client->log('connected');
		$this->connected = true;
		$this->consuming = false;
		$this->sock = $sock;
		register_shutdown_function(array($this, 'close'));
	}

	private function connect()
	{
		$headers = array('Upgrade' => 'websocket', 'Sec-Websocket-Key' => $this->wskey(), 'Sec-WebSocket-Version' => 13, 'Sec-WebSocket-Protocol' => 'chat', 'Origin' => $this->client->base_url . '/platform/notify', 'Connection' => 'Upgrade');
		$error = $this->client->get('platform/notify', array(), $headers);

		if ($error) {
			if (is_object($error)) {
				$error = $error->message;
			}

			$this->client->log('websocket handshake error: ' . $error);
		}
	}

	private function recv_message()
	{
		$raw = fread($this->sock, 8192);
		$raw = $this->last_buf . $raw;
		$this->last_buf = '';
		$i = 0;

		while ($raw) {
			$i++;
			$len = ord($raw[1]) & ~128;
			$data = substr($raw, 2);

			if ($len == 126) {
				$arr = unpack('n', $data);
				$len = array_pop($arr);
				$data = substr($data, 2);
			}
			else if ($len == 127) {
				list(, $h, $l) = unpack('N2', $data);
				$len = $l + ($h * 4294967296);
				$data = substr($data, 8);
			}

			if ($len <= strlen($data)) {
				array_push($this->messages, substr($data, 0, $len));
				$raw = substr($data, $len);
			}
			else {
				$this->last_buf = $raw;
				return $i;
			}
		}

		return $i;
	}

	private function encode($type, $data = '')
	{
		$b1 = 128 | ($type & 15);
		$length = strlen($data);

		if ($length <= 125) {
			$header = pack('CC', $b1, 128 + $length);
		}
		else {
			if ((125 < $length) && ($length < 65536)) {
				$header = pack('CCn', $b1, 128 + 126, $length);
			}
			else if (65536 <= $length) {
				$header = pack('CCN', $b1, 128 + 127, $length);
			}
		}

		$key = 0;
		$key = pack('N', rand(0, pow(255, 4) - 1));
		$header .= $key;
		return $header . $this->rotMask($data, $key);
	}

	private function wskey()
	{
		return base64_encode(time());
	}

	private function rotMask($data, $key, $offset = 0)
	{
		$res = '';

		for ($i = 0; $i < strlen($data); $i++) {
			$j = ($i + $offset) % 4;
			$res .= chr(ord($data[$i]) ^ ord($key[$j]));
		}

		return $res;
	}
}

class prism_command
{
	public $type;
	public $data;

	public function __construct($type, $data = '')
	{
		$this->type = $type;
		$this->data = $data;
	}

	public function __toString()
	{
		return json_encode(array('type' => &$this->type, 'data' => &$this->data));
	}
}

class prism_message
{
	public $body;
	public $content_type;
	private $conn;
	private $raw;
	private $tid;

	public function __construct($conn, $raw)
	{
		$this->raw = $raw;
		$this->data = json_decode($raw);
		$this->conn = &$conn;

		if ($this->data) {
			$this->body = &$this->data->body;
			$this->tid = &$this->data->tag;
			$this->content_type = $this->data->type;
		}
		else {
			$this->body = &$this->raw;
		}
	}

	public function ack()
	{
		return $this->conn->ack($this->tid);
	}

	public function __toString()
	{
		return (string) $this->body;
	}
}

class prism_exception extends Exception
{}

?>
