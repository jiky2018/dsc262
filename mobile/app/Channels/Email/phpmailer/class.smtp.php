<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class SMTP
{
	const VERSION = '5.2.9';
	const CRLF = "\r\n";
	const DEFAULT_SMTP_PORT = 25;
	const MAX_LINE_LENGTH = 998;
	const DEBUG_OFF = 0;
	const DEBUG_CLIENT = 1;
	const DEBUG_SERVER = 2;
	const DEBUG_CONNECTION = 3;
	const DEBUG_LOWLEVEL = 4;

	/**
     * The PHPMailer SMTP Version number.
     * @type string
     * @deprecated Use the `VERSION` constant instead
     * @see SMTP::VERSION
     */
	public $Version = '5.2.9';
	/**
     * SMTP server port number.
     * @type integer
     * @deprecated This is only ever used as a default value, so use the `DEFAULT_SMTP_PORT` constant instead
     * @see SMTP::DEFAULT_SMTP_PORT
     */
	public $SMTP_PORT = 25;
	/**
     * SMTP reply line ending.
     * @type string
     * @deprecated Use the `CRLF` constant instead
     * @see SMTP::CRLF
     */
	public $CRLF = "\r\n";
	/**
     * Debug output level.
     * Options:
     * * self::DEBUG_OFF (`0`) No debug output, default
     * * self::DEBUG_CLIENT (`1`) Client commands
     * * self::DEBUG_SERVER (`2`) Client commands and server responses
     * * self::DEBUG_CONNECTION (`3`) As DEBUG_SERVER plus connection status
     * * self::DEBUG_LOWLEVEL (`4`) Low-level data output, all messages
     * @type integer
     */
	public $do_debug = self::DEBUG_OFF;
	/**
     * How to handle debug output.
     * Options:
     * * `echo` Output plain-text as-is, appropriate for CLI
     * * `html` Output escaped, line breaks converted to `<br>`, appropriate for browser output
     * * `error_log` Output to error log as configured in php.ini
     *
     * Alternatively, you can provide a callable expecting two params: a message string and the debug level:
     * <code>
     * $smtp->Debugoutput = function($str, $level) {echo "debug level $level; message: $str";};
     * </code>
     * @type string|callable
     */
	public $Debugoutput = 'echo';
	/**
     * Whether to use VERP.
     * @link http://en.wikipedia.org/wiki/Variable_envelope_return_path
     * @link http://www.postfix.org/VERP_README.html Info on VERP
     * @type boolean
     */
	public $do_verp = false;
	/**
     * The timeout value for connection, in seconds.
     * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2
     * This needs to be quite high to function correctly with hosts using greetdelay as an anti-spam measure.
     * @link http://tools.ietf.org/html/rfc2821#section-4.5.3.2
     * @type integer
     */
	public $Timeout = 300;
	/**
     * How long to wait for commands to complete, in seconds.
     * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2
     * @type integer
     */
	public $Timelimit = 300;
	/**
     * The socket for the server connection.
     * @type resource
     */
	protected $smtp_conn;
	/**
     * Error message, if any, for the last call.
     * @type array
     */
	protected $error = array();
	/**
     * The reply the server sent to us for HELO.
     * If null, no HELO string has yet been received.
     * @type string|null
     */
	protected $helo_rply;
	/**
     * The set of SMTP extensions sent in reply to EHLO command.
     * Indexes of the array are extension names.
     * Value at index 'HELO' or 'EHLO' (according to command that was sent)
     * represents the server name. In case of HELO it is the only element of the array.
     * Other values can be boolean TRUE or an array containing extension options.
     * If null, no HELO/EHLO string has yet been received.
     * @type array|null
     */
	protected $server_caps;
	/**
     * The most recent reply received from the server.
     * @type string
     */
	protected $last_reply = '';

	protected function edebug($str, $level = 0)
	{
		if ($this->do_debug < $level) {
			return NULL;
		}

		if (!in_array($this->Debugoutput, array('error_log', 'html', 'echo')) && is_callable($this->Debugoutput)) {
			call_user_func($this->Debugoutput, $str, $this->do_debug);
			return NULL;
		}

		switch ($this->Debugoutput) {
		case 'error_log':
			error_log($str);
			break;

		case 'html':
			echo htmlentities(preg_replace('/[\\r\\n]+/', '', $str), ENT_QUOTES, 'UTF-8') . "<br>\n";
			break;

		case 'echo':
		default:
			$str = preg_replace('/(\\r\\n|\\r|\\n)/ms', "\n", $str);
			echo gmdate('Y-m-d H:i:s') . '	' . str_replace("\n", "\n                   \t                  ", trim($str)) . "\n";
		}
	}

	public function connect($host, $port = NULL, $timeout = 30, $options = array())
	{
		static $streamok;

		if (is_null($streamok)) {
			$streamok = function_exists('stream_socket_client');
		}

		$this->error = array();

		if ($this->connected()) {
			$this->error = array('error' => 'Already connected to a server');
			return false;
		}

		if (empty($port)) {
			$port = self::DEFAULT_SMTP_PORT;
		}

		$this->edebug('Connection: opening to ' . $host . ':' . $port . ', t=' . $timeout . ', opt=' . var_export($options, true), self::DEBUG_CONNECTION);
		$errno = 0;
		$errstr = '';

		if ($streamok) {
			$socket_context = stream_context_create($options);
			$this->smtp_conn = @stream_socket_client($host . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $socket_context);
		}
		else {
			$this->edebug('Connection: stream_socket_client not available, falling back to fsockopen', self::DEBUG_CONNECTION);
			$this->smtp_conn = fsockopen($host, $port, $errno, $errstr, $timeout);
		}

		if (!is_resource($this->smtp_conn)) {
			$this->error = array('error' => 'Failed to connect to server', 'errno' => $errno, 'errstr' => $errstr);
			$this->edebug('SMTP ERROR: ' . $this->error['error'] . ': ' . $errstr . ' (' . $errno . ')', self::DEBUG_CLIENT);
			return false;
		}

		$this->edebug('Connection: opened', self::DEBUG_CONNECTION);

		if (substr(PHP_OS, 0, 3) != 'WIN') {
			$max = ini_get('max_execution_time');
			if (($max != 0) && ($max < $timeout)) {
				@set_time_limit($timeout);
			}

			stream_set_timeout($this->smtp_conn, $timeout, 0);
		}

		$announce = $this->get_lines();
		$this->edebug('SERVER -> CLIENT: ' . $announce, self::DEBUG_SERVER);
		return true;
	}

	public function startTLS()
	{
		if (!$this->sendCommand('STARTTLS', 'STARTTLS', 220)) {
			return false;
		}

		if (!stream_socket_enable_crypto($this->smtp_conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
			return false;
		}

		return true;
	}

	public function authenticate($username, $password, $authtype = NULL, $realm = '', $workstation = '')
	{
		if (!$this->server_caps) {
			$this->error = array('error' => 'Authentication is not allowed before HELO/EHLO');
			return false;
		}

		if (array_key_exists('EHLO', $this->server_caps)) {
			if (!array_key_exists('AUTH', $this->server_caps)) {
				$this->error = array('error' => 'Authentication is not allowed at this stage');
				return false;
			}

			self::edebug('Auth method requested: ' . ($authtype ? $authtype : 'UNKNOWN'), self::DEBUG_LOWLEVEL);
			self::edebug('Auth methods available on the server: ' . implode(',', $this->server_caps['AUTH']), self::DEBUG_LOWLEVEL);

			if (empty($authtype)) {
				foreach (array('LOGIN', 'CRAM-MD5', 'NTLM', 'PLAIN') as $method) {
					if (in_array($method, $this->server_caps['AUTH'])) {
						$authtype = $method;
						break;
					}
				}

				if (empty($authtype)) {
					$this->error = array('error' => 'No supported authentication methods found');
					return false;
				}

				self::edebug('Auth method selected: ' . $authtype, self::DEBUG_LOWLEVEL);
			}

			if (!in_array($authtype, $this->server_caps['AUTH'])) {
				$this->error = array('error' => 'The requested authentication method "' . $authtype . '" is not supported by the server');
				return false;
			}
		}
		else if (empty($authtype)) {
			$authtype = 'LOGIN';
		}

		switch ($authtype) {
		case 'PLAIN':
			if (!$this->sendCommand('AUTH', 'AUTH PLAIN', 334)) {
				return false;
			}

			if (!$this->sendCommand('User & Password', base64_encode("\x00" . $username . "\x00" . $password), 235)) {
				return false;
			}

			break;

		case 'LOGIN':
			if (!$this->sendCommand('AUTH', 'AUTH LOGIN', 334)) {
				return false;
			}

			if (!$this->sendCommand('Username', base64_encode($username), 334)) {
				return false;
			}

			if (!$this->sendCommand('Password', base64_encode($password), 235)) {
				return false;
			}

			break;

		case 'NTLM':
			require_once 'extras/ntlm_sasl_client.php';
			$temp = new stdClass();
			$ntlm_client = new ntlm_sasl_client_class();

			if (!$ntlm_client->Initialize($temp)) {
				$this->error = array('error' => $temp->error);
				$this->edebug('You need to enable some modules in your php.ini file: ' . $this->error['error'], self::DEBUG_CLIENT);
				return false;
			}

			$msg1 = $ntlm_client->TypeMsg1($realm, $workstation);

			if (!$this->sendCommand('AUTH NTLM', 'AUTH NTLM ' . base64_encode($msg1), 334)) {
				return false;
			}

			$challenge = substr($this->last_reply, 3);
			$challenge = base64_decode($challenge);
			$ntlm_res = $ntlm_client->NTLMResponse(substr($challenge, 24, 8), $password);
			$msg3 = $ntlm_client->TypeMsg3($ntlm_res, $username, $realm, $workstation);
			return $this->sendCommand('Username', base64_encode($msg3), 235);
		case 'CRAM-MD5':
			if (!$this->sendCommand('AUTH CRAM-MD5', 'AUTH CRAM-MD5', 334)) {
				return false;
			}

			$challenge = base64_decode(substr($this->last_reply, 4));
			$response = $username . ' ' . $this->hmac($challenge, $password);
			return $this->sendCommand('Username', base64_encode($response), 235);
		default:
			$this->error = array('error' => 'Authentication method "' . $authtype . '" is not supported');
			return false;
		}

		return true;
	}

	protected function hmac($data, $key)
	{
		if (function_exists('hash_hmac')) {
			return hash_hmac('md5', $data, $key);
		}

		$bytelen = 64;

		if ($bytelen < strlen($key)) {
			$key = pack('H*', md5($key));
		}

		$key = str_pad($key, $bytelen, chr(0));
		$ipad = str_pad('', $bytelen, chr(54));
		$opad = str_pad('', $bytelen, chr(92));
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;
		return md5($k_opad . pack('H*', md5($k_ipad . $data)));
	}

	public function connected()
	{
		if (is_resource($this->smtp_conn)) {
			$sock_status = stream_get_meta_data($this->smtp_conn);

			if ($sock_status['eof']) {
				$this->edebug('SMTP NOTICE: EOF caught while checking if connected', self::DEBUG_CLIENT);
				$this->close();
				return false;
			}

			return true;
		}

		return false;
	}

	public function close()
	{
		$this->error = array();
		$this->server_caps = NULL;
		$this->helo_rply = NULL;

		if (is_resource($this->smtp_conn)) {
			fclose($this->smtp_conn);
			$this->smtp_conn = NULL;
			$this->edebug('Connection: closed', self::DEBUG_CONNECTION);
		}
	}

	public function data($msg_data)
	{
		if (!$this->sendCommand('DATA', 'DATA', 354)) {
			return false;
		}

		$lines = explode("\n", str_replace(array("\r\n", "\r"), "\n", $msg_data));
		$field = substr($lines[0], 0, strpos($lines[0], ':'));
		$in_headers = false;
		if (!empty($field) && (strpos($field, ' ') === false)) {
			$in_headers = true;
		}

		foreach ($lines as $line) {
			$lines_out = array();
			if ($in_headers && ($line == '')) {
				$in_headers = false;
			}

			while (isset($line[self::MAX_LINE_LENGTH])) {
				$pos = strrpos(substr($line, 0, self::MAX_LINE_LENGTH), ' ');

				if (!$pos) {
					$pos = self::MAX_LINE_LENGTH - 1;
					$lines_out[] = substr($line, 0, $pos);
					$line = substr($line, $pos);
				}
				else {
					$lines_out[] = substr($line, 0, $pos);
					$line = substr($line, $pos + 1);
				}

				if ($in_headers) {
					$line = '	' . $line;
				}
			}

			$lines_out[] = $line;

			foreach ($lines_out as $line_out) {
				if (!empty($line_out) && ($line_out[0] == '.')) {
					$line_out = '.' . $line_out;
				}

				$this->client_send($line_out . self::CRLF);
			}
		}

		$savetimelimit = $this->Timelimit;
		$this->Timelimit = $this->Timelimit * 2;
		$result = $this->sendCommand('DATA END', '.', 250);
		$this->Timelimit = $savetimelimit;
		return $result;
	}

	public function hello($host = '')
	{
		return (bool) ($this->sendHello('EHLO', $host) || $this->sendHello('HELO', $host));
	}

	protected function sendHello($hello, $host)
	{
		$noerror = $this->sendCommand($hello, $hello . ' ' . $host, 250);
		$this->helo_rply = $this->last_reply;

		if ($noerror) {
			$this->parseHelloFields($hello);
		}
		else {
			$this->server_caps = NULL;
		}

		return $noerror;
	}

	protected function parseHelloFields($type)
	{
		$this->server_caps = array();
		$lines = explode("\n", $this->last_reply);

		foreach ($lines as $n => $s) {
			$s = trim(substr($s, 4));

			if (!$s) {
				continue;
			}

			$fields = explode(' ', $s);

			if ($fields) {
				if (!$n) {
					$name = $type;
					$fields = $fields[0];
				}
				else {
					$name = array_shift($fields);

					if ($name == 'SIZE') {
						$fields = ($fields ? $fields[0] : 0);
					}
				}

				$this->server_caps[$name] = $fields ? $fields : true;
			}
		}
	}

	public function mail($from)
	{
		$useVerp = ($this->do_verp ? ' XVERP' : '');
		return $this->sendCommand('MAIL FROM', 'MAIL FROM:<' . $from . '>' . $useVerp, 250);
	}

	public function quit($close_on_error = true)
	{
		$noerror = $this->sendCommand('QUIT', 'QUIT', 221);
		$err = $this->error;
		if ($noerror || $close_on_error) {
			$this->close();
			$this->error = $err;
		}

		return $noerror;
	}

	public function recipient($toaddr)
	{
		return $this->sendCommand('RCPT TO', 'RCPT TO:<' . $toaddr . '>', array(250, 251));
	}

	public function reset()
	{
		return $this->sendCommand('RSET', 'RSET', 250);
	}

	protected function sendCommand($command, $commandstring, $expect)
	{
		if (!$this->connected()) {
			$this->error = array('error' => 'Called ' . $command . ' without being connected');
			return false;
		}

		$this->client_send($commandstring . self::CRLF);
		$this->last_reply = $this->get_lines();
		$matches = array();

		if (preg_match('/^([0-9]{3})[ -](?:([0-9]\\.[0-9]\\.[0-9]) )?/', $this->last_reply, $matches)) {
			$code = $matches[1];
			$code_ex = (2 < count($matches) ? $matches[2] : NULL);
			$detail = preg_replace('/' . $code . '[ -]' . ($code_ex ? str_replace('.', '\\.', $code_ex) . ' ' : '') . '/m', '', $this->last_reply);
		}
		else {
			$code = substr($this->last_reply, 0, 3);
			$code_ex = NULL;
			$detail = substr($this->last_reply, 4);
		}

		$this->edebug('SERVER -> CLIENT: ' . $this->last_reply, self::DEBUG_SERVER);

		if (!in_array($code, (array) $expect)) {
			$this->error = array('error' => $command . ' command failed', 'smtp_code' => $code, 'smtp_code_ex' => $code_ex, 'detail' => $detail);
			$this->edebug('SMTP ERROR: ' . $this->error['error'] . ': ' . $this->last_reply, self::DEBUG_CLIENT);
			return false;
		}

		$this->error = array();
		return true;
	}

	public function sendAndMail($from)
	{
		return $this->sendCommand('SAML', 'SAML FROM:' . $from, 250);
	}

	public function verify($name)
	{
		return $this->sendCommand('VRFY', 'VRFY ' . $name, array(250, 251));
	}

	public function noop()
	{
		return $this->sendCommand('NOOP', 'NOOP', 250);
	}

	public function turn()
	{
		$this->error = array('error' => 'The SMTP TURN command is not implemented');
		$this->edebug('SMTP NOTICE: ' . $this->error['error'], self::DEBUG_CLIENT);
		return false;
	}

	public function client_send($data)
	{
		$this->edebug('CLIENT -> SERVER: ' . $data, self::DEBUG_CLIENT);
		return fwrite($this->smtp_conn, $data);
	}

	public function getError()
	{
		return $this->error;
	}

	public function getServerExtList()
	{
		return $this->server_caps;
	}

	public function getServerExt($name)
	{
		if (!$this->server_caps) {
			$this->error = array('No HELO/EHLO was sent');
			return NULL;
		}

		if (!array_key_exists($name, $this->server_caps)) {
			if ($name == 'HELO') {
				return $this->server_caps['EHLO'];
			}

			if (($name == 'EHLO') || array_key_exists('EHLO', $this->server_caps)) {
				return false;
			}

			$this->error = array('HELO handshake was used. Client knows nothing about server extensions');
			return NULL;
		}

		return $this->server_caps[$name];
	}

	public function getLastReply()
	{
		return $this->last_reply;
	}

	protected function get_lines()
	{
		if (!is_resource($this->smtp_conn)) {
			return '';
		}

		$data = '';
		$endtime = 0;
		stream_set_timeout($this->smtp_conn, $this->Timeout);

		if (0 < $this->Timelimit) {
			$endtime = time() + $this->Timelimit;
		}

		while (is_resource($this->smtp_conn) && !feof($this->smtp_conn)) {
			$str = @fgets($this->smtp_conn, 515);
			$this->edebug('SMTP -> get_lines(): $data was "' . $data . '"', self::DEBUG_LOWLEVEL);
			$this->edebug('SMTP -> get_lines(): $str is "' . $str . '"', self::DEBUG_LOWLEVEL);
			$data .= $str;
			$this->edebug('SMTP -> get_lines(): $data is "' . $data . '"', self::DEBUG_LOWLEVEL);
			if (isset($str[3]) && ($str[3] == ' ')) {
				break;
			}

			$info = stream_get_meta_data($this->smtp_conn);

			if ($info['timed_out']) {
				$this->edebug('SMTP -> get_lines(): timed-out (' . $this->Timeout . ' sec)', self::DEBUG_LOWLEVEL);
				break;
			}

			if ($endtime && ($endtime < time())) {
				$this->edebug('SMTP -> get_lines(): timelimit reached (' . $this->Timelimit . ' sec)', self::DEBUG_LOWLEVEL);
				break;
			}
		}

		return $data;
	}

	public function setVerp($enabled = false)
	{
		$this->do_verp = $enabled;
	}

	public function getVerp()
	{
		return $this->do_verp;
	}

	public function setDebugOutput($method = 'echo')
	{
		$this->Debugoutput = $method;
	}

	public function getDebugOutput()
	{
		return $this->Debugoutput;
	}

	public function setDebugLevel($level = 0)
	{
		$this->do_debug = $level;
	}

	public function getDebugLevel()
	{
		return $this->do_debug;
	}

	public function setTimeout($timeout = 0)
	{
		$this->Timeout = $timeout;
	}

	public function getTimeout()
	{
		return $this->Timeout;
	}
}


?>
