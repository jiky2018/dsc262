<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class PHPMailer
{
	const STOP_MESSAGE = 0;
	const STOP_CONTINUE = 1;
	const STOP_CRITICAL = 2;
	const CRLF = "\r\n";

	/**
     * The PHPMailer Version number.
     * @type string
     */
	public $Version = '5.2.9';
	/**
     * Email priority.
     * Options: 1 = High, 3 = Normal, 5 = low.
     * @type integer
     */
	public $Priority = 3;
	/**
     * The character set of the message.
     * @type string
     */
	public $CharSet = 'iso-8859-1';
	/**
     * The MIME Content-type of the message.
     * @type string
     */
	public $ContentType = 'text/plain';
	/**
     * The message encoding.
     * Options: "8bit", "7bit", "binary", "base64", and "quoted-printable".
     * @type string
     */
	public $Encoding = '8bit';
	/**
     * Holds the most recent mailer error message.
     * @type string
     */
	public $ErrorInfo = '';
	/**
     * The From email address for the message.
     * @type string
     */
	public $From = 'root@localhost';
	/**
     * The From name of the message.
     * @type string
     */
	public $FromName = 'Root User';
	/**
     * The Sender email (Return-Path) of the message.
     * If not empty, will be sent via -f to sendmail or as 'MAIL FROM' in smtp mode.
     * @type string
     */
	public $Sender = '';
	/**
     * The Return-Path of the message.
     * If empty, it will be set to either From or Sender.
     * @type string
     * @deprecated Email senders should never set a return-path header;
     * it's the receiver's job (RFC5321 section 4.4), so this no longer does anything.
     * @link https://tools.ietf.org/html/rfc5321#section-4.4 RFC5321 reference
     */
	public $ReturnPath = '';
	/**
     * The Subject of the message.
     * @type string
     */
	public $Subject = '';
	/**
     * An HTML or plain text message body.
     * If HTML then call isHTML(true).
     * @type string
     */
	public $Body = '';
	/**
     * The plain-text message body.
     * This body can be read by mail clients that do not have HTML email
     * capability such as mutt & Eudora.
     * Clients that can read HTML will view the normal Body.
     * @type string
     */
	public $AltBody = '';
	/**
     * An iCal message part body.
     * Only supported in simple alt or alt_inline message types
     * To generate iCal events, use the bundled extras/EasyPeasyICS.php class or iCalcreator
     * @link http://sprain.ch/blog/downloads/php-class-easypeasyics-create-ical-files-with-php/
     * @link http://kigkonsult.se/iCalcreator/
     * @type string
     */
	public $Ical = '';
	/**
     * The complete compiled MIME message body.
     * @access protected
     * @type string
     */
	protected $MIMEBody = '';
	/**
     * The complete compiled MIME message headers.
     * @type string
     * @access protected
     */
	protected $MIMEHeader = '';
	/**
     * Extra headers that createHeader() doesn't fold in.
     * @type string
     * @access protected
     */
	protected $mailHeader = '';
	/**
     * Word-wrap the message body to this number of chars.
     * Set to 0 to not wrap. A useful value here is 78, for RFC2822 section 2.1.1 compliance.
     * @type integer
     */
	public $WordWrap = 0;
	/**
     * Which method to use to send mail.
     * Options: "mail", "sendmail", or "smtp".
     * @type string
     */
	public $Mailer = 'mail';
	/**
     * The path to the sendmail program.
     * @type string
     */
	public $Sendmail = '/usr/sbin/sendmail';
	/**
     * Whether mail() uses a fully sendmail-compatible MTA.
     * One which supports sendmail's "-oi -f" options.
     * @type boolean
     */
	public $UseSendmailOptions = true;
	/**
     * Path to PHPMailer plugins.
     * Useful if the SMTP class is not in the PHP include path.
     * @type string
     * @deprecated Should not be needed now there is an autoloader.
     */
	public $PluginDir = '';
	/**
     * The email address that a reading confirmation should be sent to.
     * @type string
     */
	public $ConfirmReadingTo = '';
	/**
     * The hostname to use in Message-Id and Received headers
     * and as default HELO string.
     * If empty, the value returned
     * by SERVER_NAME is used or 'localhost.localdomain'.
     * @type string
     */
	public $Hostname = '';
	/**
     * An ID to be used in the Message-Id header.
     * If empty, a unique id will be generated.
     * @type string
     */
	public $MessageID = '';
	/**
     * The message Date to be used in the Date header.
     * If empty, the current date will be added.
     * @type string
     */
	public $MessageDate = '';
	/**
     * SMTP hosts.
     * Either a single hostname or multiple semicolon-delimited hostnames.
     * You can also specify a different port
     * for each host by using this format: [hostname:port]
     * (e.g. "smtp1.example.com:25;smtp2.example.com").
     * You can also specify encryption type, for example:
     * (e.g. "tls://smtp1.example.com:587;ssl://smtp2.example.com:465").
     * Hosts will be tried in order.
     * @type string
     */
	public $Host = 'localhost';
	/**
     * The default SMTP server port.
     * @type integer
     * @TODO Why is this needed when the SMTP class takes care of it?
     */
	public $Port = 25;
	/**
     * The SMTP HELO of the message.
     * Default is $Hostname.
     * @type string
     * @see PHPMailer::$Hostname
     */
	public $Helo = '';
	/**
     * The secure connection prefix.
     * Options: "", "ssl" or "tls"
     * @type string
     */
	public $SMTPSecure = '';
	/**
     * Whether to use SMTP authentication.
     * Uses the Username and Password properties.
     * @type boolean
     * @see PHPMailer::$Username
     * @see PHPMailer::$Password
     */
	public $SMTPAuth = false;
	/**
     * SMTP username.
     * @type string
     */
	public $Username = '';
	/**
     * SMTP password.
     * @type string
     */
	public $Password = '';
	/**
     * SMTP auth type.
     * Options are LOGIN (default), PLAIN, NTLM, CRAM-MD5
     * @type string
     */
	public $AuthType = '';
	/**
     * SMTP realm.
     * Used for NTLM auth
     * @type string
     */
	public $Realm = '';
	/**
     * SMTP workstation.
     * Used for NTLM auth
     * @type string
     */
	public $Workstation = '';
	/**
     * The SMTP server timeout in seconds.
     * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2
     * @type integer
     */
	public $Timeout = 300;
	/**
     * SMTP class debug output mode.
     * Debug output level.
     * Options:
     * * `0` No output
     * * `1` Commands
     * * `2` Data and commands
     * * `3` As 2 plus connection status
     * * `4` Low-level data output
     * @type integer
     * @see SMTP::$do_debug
     */
	public $SMTPDebug = 0;
	/**
     * How to handle debug output.
     * Options:
     * * `echo` Output plain-text as-is, appropriate for CLI
     * * `html` Output escaped, line breaks converted to `<br>`, appropriate for browser output
     * * `error_log` Output to error log as configured in php.ini
     *
     * Alternatively, you can provide a callable expecting two params: a message string and the debug level:
     * <code>
     * $mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str";};
     * </code>
     * @type string|callable
     * @see SMTP::$Debugoutput
     */
	public $Debugoutput = 'echo';
	/**
     * Whether to keep SMTP connection open after each message.
     * If this is set to true then to close the connection
     * requires an explicit call to smtpClose().
     * @type boolean
     */
	public $SMTPKeepAlive = false;
	/**
     * Whether to split multiple to addresses into multiple messages
     * or send them all in one message.
     * @type boolean
     */
	public $SingleTo = false;
	/**
     * Storage for addresses when SingleTo is enabled.
     * @type array
     * @TODO This should really not be public
     */
	public $SingleToArray = array();
	/**
     * Whether to generate VERP addresses on send.
     * Only applicable when sending via SMTP.
     * @link http://en.wikipedia.org/wiki/Variable_envelope_return_path
     * @link http://www.postfix.org/VERP_README.html Postfix VERP info
     * @type boolean
     */
	public $do_verp = false;
	/**
     * Whether to allow sending messages with an empty body.
     * @type boolean
     */
	public $AllowEmpty = false;
	/**
     * The default line ending.
     * @note The default remains "\n". We force CRLF where we know
     *        it must be used via self::CRLF.
     * @type string
     */
	public $LE = "\n";
	/**
     * DKIM selector.
     * @type string
     */
	public $DKIM_selector = '';
	/**
     * DKIM Identity.
     * Usually the email address used as the source of the email
     * @type string
     */
	public $DKIM_identity = '';
	/**
     * DKIM passphrase.
     * Used if your key is encrypted.
     * @type string
     */
	public $DKIM_passphrase = '';
	/**
     * DKIM signing domain name.
     * @example 'example.com'
     * @type string
     */
	public $DKIM_domain = '';
	/**
     * DKIM private key file path.
     * @type string
     */
	public $DKIM_private = '';
	/**
     * Callback Action function name.
     *
     * The function that handles the result of the send email action.
     * It is called out by send() for each email sent.
     *
     * Value can be any php callable: http://www.php.net/is_callable
     *
     * Parameters:
     *   boolean $result        result of the send action
     *   string  $to            email address of the recipient
     *   string  $cc            cc email addresses
     *   string  $bcc           bcc email addresses
     *   string  $subject       the subject
     *   string  $body          the email body
     *   string  $from          email address of sender
     * @type string
     */
	public $action_function = '';
	/**
     * What to put in the X-Mailer header.
     * Options: An empty string for PHPMailer default, whitespace for none, or a string to use
     * @type string
     */
	public $XMailer = '';
	/**
     * An instance of the SMTP sender class.
     * @type SMTP
     * @access protected
     */
	protected $smtp;
	/**
     * The array of 'to' addresses.
     * @type array
     * @access protected
     */
	protected $to = array();
	/**
     * The array of 'cc' addresses.
     * @type array
     * @access protected
     */
	protected $cc = array();
	/**
     * The array of 'bcc' addresses.
     * @type array
     * @access protected
     */
	protected $bcc = array();
	/**
     * The array of reply-to names and addresses.
     * @type array
     * @access protected
     */
	protected $ReplyTo = array();
	/**
     * An array of all kinds of addresses.
     * Includes all of $to, $cc, $bcc, $replyto
     * @type array
     * @access protected
     */
	protected $all_recipients = array();
	/**
     * The array of attachments.
     * @type array
     * @access protected
     */
	protected $attachment = array();
	/**
     * The array of custom headers.
     * @type array
     * @access protected
     */
	protected $CustomHeader = array();
	/**
     * The most recent Message-ID (including angular brackets).
     * @type string
     * @access protected
     */
	protected $lastMessageID = '';
	/**
     * The message's MIME type.
     * @type string
     * @access protected
     */
	protected $message_type = '';
	/**
     * The array of MIME boundary strings.
     * @type array
     * @access protected
     */
	protected $boundary = array();
	/**
     * The array of available languages.
     * @type array
     * @access protected
     */
	protected $language = array();
	/**
     * The number of errors encountered.
     * @type integer
     * @access protected
     */
	protected $error_count = 0;
	/**
     * The S/MIME certificate file path.
     * @type string
     * @access protected
     */
	protected $sign_cert_file = '';
	/**
     * The S/MIME key file path.
     * @type string
     * @access protected
     */
	protected $sign_key_file = '';
	/**
     * The S/MIME password for the key.
     * Used only if the key is encrypted.
     * @type string
     * @access protected
     */
	protected $sign_key_pass = '';
	/**
     * Whether to throw exceptions for errors.
     * @type boolean
     * @access protected
     */
	protected $exceptions = false;

	public function __construct($exceptions = false)
	{
		$this->exceptions = (bool) $exceptions;
	}

	public function __destruct()
	{
		if ($this->Mailer == 'smtp') {
			$this->smtpClose();
		}
	}

	private function mailPassthru($to, $subject, $body, $header, $params)
	{
		if (ini_get('mbstring.func_overload') & 1) {
			$subject = $this->secureHeader($subject);
		}
		else {
			$subject = $this->encodeHeader($this->secureHeader($subject));
		}

		if (ini_get('safe_mode') || !$this->UseSendmailOptions) {
			$result = @mail($to, $subject, $body, $header);
		}
		else {
			$result = @mail($to, $subject, $body, $header, $params);
		}

		return $result;
	}

	protected function edebug($str)
	{
		if ($this->SMTPDebug <= 0) {
			return NULL;
		}

		if (!in_array($this->Debugoutput, array('error_log', 'html', 'echo')) && is_callable($this->Debugoutput)) {
			call_user_func($this->Debugoutput, $str, $this->SMTPDebug);
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

	public function isHTML($isHtml = true)
	{
		if ($isHtml) {
			$this->ContentType = 'text/html';
		}
		else {
			$this->ContentType = 'text/plain';
		}
	}

	public function isSMTP()
	{
		$this->Mailer = 'smtp';
	}

	public function isMail()
	{
		$this->Mailer = 'mail';
	}

	public function isSendmail()
	{
		$ini_sendmail_path = ini_get('sendmail_path');

		if (!stristr($ini_sendmail_path, 'sendmail')) {
			$this->Sendmail = '/usr/sbin/sendmail';
		}
		else {
			$this->Sendmail = $ini_sendmail_path;
		}

		$this->Mailer = 'sendmail';
	}

	public function isQmail()
	{
		$ini_sendmail_path = ini_get('sendmail_path');

		if (!stristr($ini_sendmail_path, 'qmail')) {
			$this->Sendmail = '/var/qmail/bin/qmail-inject';
		}
		else {
			$this->Sendmail = $ini_sendmail_path;
		}

		$this->Mailer = 'qmail';
	}

	public function addAddress($address, $name = '')
	{
		return $this->addAnAddress('to', $address, $name);
	}

	public function addCC($address, $name = '')
	{
		return $this->addAnAddress('cc', $address, $name);
	}

	public function addBCC($address, $name = '')
	{
		return $this->addAnAddress('bcc', $address, $name);
	}

	public function addReplyTo($address, $name = '')
	{
		return $this->addAnAddress('Reply-To', $address, $name);
	}

	protected function addAnAddress($kind, $address, $name = '')
	{
		if (!preg_match('/^(to|cc|bcc|Reply-To)$/', $kind)) {
			$this->setError($this->lang('Invalid recipient array') . ': ' . $kind);
			$this->edebug($this->lang('Invalid recipient array') . ': ' . $kind);

			if ($this->exceptions) {
				throw new phpmailerException('Invalid recipient array: ' . $kind);
			}

			return false;
		}

		$address = trim($address);
		$name = trim(preg_replace('/[\\r\\n]+/', '', $name));

		if (!$this->validateAddress($address)) {
			$this->setError($this->lang('invalid_address') . ': ' . $address);
			$this->edebug($this->lang('invalid_address') . ': ' . $address);

			if ($this->exceptions) {
				throw new phpmailerException($this->lang('invalid_address') . ': ' . $address);
			}

			return false;
		}

		if ($kind != 'Reply-To') {
			if (!isset($this->all_recipients[strtolower($address)])) {
				array_push($this->$kind, array($address, $name));
				$this->all_recipients[strtolower($address)] = true;
				return true;
			}
		}
		else if (!array_key_exists(strtolower($address), $this->ReplyTo)) {
			$this->ReplyTo[strtolower($address)] = array($address, $name);
			return true;
		}

		return false;
	}

	public function setFrom($address, $name = '', $auto = true)
	{
		$address = trim($address);
		$name = trim(preg_replace('/[\\r\\n]+/', '', $name));

		if (!$this->validateAddress($address)) {
			$this->setError($this->lang('invalid_address') . ': ' . $address);
			$this->edebug($this->lang('invalid_address') . ': ' . $address);

			if ($this->exceptions) {
				throw new phpmailerException($this->lang('invalid_address') . ': ' . $address);
			}

			return false;
		}

		$this->From = $address;
		$this->FromName = $name;

		if ($auto) {
			if (empty($this->Sender)) {
				$this->Sender = $address;
			}
		}

		return true;
	}

	public function getLastMessageID()
	{
		return $this->lastMessageID;
	}

	static public function validateAddress($address, $patternselect = 'auto')
	{
		if (!$patternselect || ($patternselect == 'auto')) {
			if (defined('PCRE_VERSION')) {
				if (0 <= version_compare(PCRE_VERSION, '8.0.3')) {
					$patternselect = 'pcre8';
				}
				else {
					$patternselect = 'pcre';
				}
			}
			else {
				if (function_exists('extension_loaded') && extension_loaded('pcre')) {
					$patternselect = 'pcre';
				}
				else if (0 <= version_compare(PHP_VERSION, '5.2.0')) {
					$patternselect = 'php';
				}
				else {
					$patternselect = 'noregex';
				}
			}
		}

		switch ($patternselect) {
		case 'pcre8':
			return (bool) preg_match('/^(?!(?>(?1)"?(?>\\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)"?(?>\\\\[ -~]|[^"])"?(?1)){65,}@)' . '((?>(?>(?>((?>(?>(?>\\x0D\\x0A)?[\\t ])+|(?>[\\t ]*\\x0D\\x0A)?[\\t ]+)?)(\\((?>(?2)' . '(?>[\\x01-\\x08\\x0B\\x0C\\x0E-\'*-\\[\\]-\\x7F]|\\\\[\\x00-\\x7F]|(?3)))*(?2)\\)))+(?2))|(?2))?)' . '([!#-\'*+\\/-9=?^-~-]+|"(?>(?2)(?>[\\x01-\\x08\\x0B\\x0C\\x0E-!#-\\[\\]-\\x7F]|\\\\[\\x00-\\x7F]))*' . '(?2)")(?>(?1)\\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)' . '(?>(?1)\\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}' . '|(?!(?:.*[a-f0-9][:\\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:' . '|(?!(?:.*[a-f0-9]:){6,})(?8)?::(?>((?6)(?>:(?6)){0,4}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}' . '|[1-9]?[0-9])(?>\\.(?9)){3}))\\])(?1)$/isD', $address);
		case 'pcre':
			return (bool) preg_match('/^(?!(?>"?(?>\\\\[ -~]|[^"])"?){255,})(?!(?>"?(?>\\\\[ -~]|[^"])"?){65,}@)(?>' . '[!#-\'*+\\/-9=?^-~-]+|"(?>(?>[\\x01-\\x08\\x0B\\x0C\\x0E-!#-\\[\\]-\\x7F]|\\\\[\\x00-\\xFF]))*")' . '(?>\\.(?>[!#-\'*+\\/-9=?^-~-]+|"(?>(?>[\\x01-\\x08\\x0B\\x0C\\x0E-!#-\\[\\]-\\x7F]|\\\\[\\x00-\\xFF]))*"))*' . '@(?>(?![a-z0-9-]{64,})(?>[a-z0-9](?>[a-z0-9-]*[a-z0-9])?)(?>\\.(?![a-z0-9-]{64,})' . '(?>[a-z0-9](?>[a-z0-9-]*[a-z0-9])?)){0,126}|\\[(?:(?>IPv6:(?>(?>[a-f0-9]{1,4})(?>:' . '[a-f0-9]{1,4}){7}|(?!(?:.*[a-f0-9][:\\]]){8,})(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,6})?' . '::(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,6})?))|(?>(?>IPv6:(?>[a-f0-9]{1,4}(?>:' . '[a-f0-9]{1,4}){5}:|(?!(?:.*[a-f0-9]:){6,})(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,4})?' . '::(?>(?:[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,4}):)?))?(?>25[0-5]|2[0-4][0-9]|1[0-9]{2}' . '|[1-9]?[0-9])(?>\\.(?>25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}))\\])$/isD', $address);
		case 'html5':
			return (bool) preg_match('/^[a-zA-Z0-9.!#$%&\'*+\\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}' . '[a-zA-Z0-9])?(?:\\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD', $address);
		case 'noregex':
			return (3 <= strlen($address)) && (1 <= strpos($address, '@')) && (strpos($address, '@') != (strlen($address) - 1));
		case 'php':
		default:
			return (bool) filter_var($address, FILTER_VALIDATE_EMAIL);
		}
	}

	public function send()
	{
		try {
			if (!$this->preSend()) {
				return false;
			}

			return $this->postSend();
		}
		catch (phpmailerException $exc) {
			$this->mailHeader = '';
			$this->setError($exc->getMessage());

			if ($this->exceptions) {
				throw $exc;
			}

			return false;
		}
	}

	public function preSend()
	{
		try {
			$this->mailHeader = '';

			if ((count($this->to) + count($this->cc) + count($this->bcc)) < 1) {
				throw new phpmailerException($this->lang('provide_address'), self::STOP_CRITICAL);
			}

			if (!empty($this->AltBody)) {
				$this->ContentType = 'multipart/alternative';
			}

			$this->error_count = 0;
			$this->setMessageType();
			if (!$this->AllowEmpty && empty($this->Body)) {
				throw new phpmailerException($this->lang('empty_message'), self::STOP_CRITICAL);
			}

			$this->MIMEHeader = $this->createHeader();
			$this->MIMEBody = $this->createBody();

			if ($this->Mailer == 'mail') {
				if (0 < count($this->to)) {
					$this->mailHeader .= $this->addrAppend('To', $this->to);
				}
				else {
					$this->mailHeader .= $this->headerLine('To', 'undisclosed-recipients:;');
				}

				$this->mailHeader .= $this->headerLine('Subject', $this->encodeHeader($this->secureHeader(trim($this->Subject))));
			}

			if (!empty($this->DKIM_domain) && !empty($this->DKIM_private) && !empty($this->DKIM_selector) && file_exists($this->DKIM_private)) {
				$header_dkim = $this->DKIM_Add($this->MIMEHeader . $this->mailHeader, $this->encodeHeader($this->secureHeader($this->Subject)), $this->MIMEBody);
				$this->MIMEHeader = rtrim($this->MIMEHeader, "\r\n ") . self::CRLF . str_replace("\r\n", "\n", $header_dkim) . self::CRLF;
			}

			return true;
		}
		catch (phpmailerException $exc) {
			$this->setError($exc->getMessage());

			if ($this->exceptions) {
				throw $exc;
			}

			return false;
		}
	}

	public function postSend()
	{
		try {
			switch ($this->Mailer) {
			case 'sendmail':
			case 'qmail':
				return $this->sendmailSend($this->MIMEHeader, $this->MIMEBody);
			case 'smtp':
				return $this->smtpSend($this->MIMEHeader, $this->MIMEBody);
			case 'mail':
				return $this->mailSend($this->MIMEHeader, $this->MIMEBody);
			default:
				$sendMethod = $this->Mailer . 'Send';

				if (method_exists($this, $sendMethod)) {
					return $this->$sendMethod($this->MIMEHeader, $this->MIMEBody);
				}

				return $this->mailSend($this->MIMEHeader, $this->MIMEBody);
			}
		}
		catch (phpmailerException $exc) {
			$this->setError($exc->getMessage());
			$this->edebug($exc->getMessage());

			if ($this->exceptions) {
				throw $exc;
			}
		}

		return false;
	}

	protected function sendmailSend($header, $body)
	{
		if ($this->Sender != '') {
			if ($this->Mailer == 'qmail') {
				$sendmail = sprintf('%s -f%s', escapeshellcmd($this->Sendmail), escapeshellarg($this->Sender));
			}
			else {
				$sendmail = sprintf('%s -oi -f%s -t', escapeshellcmd($this->Sendmail), escapeshellarg($this->Sender));
			}
		}
		else if ($this->Mailer == 'qmail') {
			$sendmail = sprintf('%s', escapeshellcmd($this->Sendmail));
		}
		else {
			$sendmail = sprintf('%s -oi -t', escapeshellcmd($this->Sendmail));
		}

		if ($this->SingleTo) {
			foreach ($this->SingleToArray as $toAddr) {
				if (!@$mail = popen($sendmail, 'w')) {
					throw new phpmailerException($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
				}

				fputs($mail, 'To: ' . $toAddr . "\n");
				fputs($mail, $header);
				fputs($mail, $body);
				$result = pclose($mail);
				$this->doCallback($result == 0, array($toAddr), $this->cc, $this->bcc, $this->Subject, $body, $this->From);

				if ($result != 0) {
					throw new phpmailerException($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
				}
			}
		}
		else {
			if (!@$mail = popen($sendmail, 'w')) {
				throw new phpmailerException($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
			}

			fputs($mail, $header);
			fputs($mail, $body);
			$result = pclose($mail);
			$this->doCallback($result == 0, $this->to, $this->cc, $this->bcc, $this->Subject, $body, $this->From);

			if ($result != 0) {
				throw new phpmailerException($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
			}
		}

		return true;
	}

	protected function mailSend($header, $body)
	{
		$toArr = array();

		foreach ($this->to as $toaddr) {
			$toArr[] = $this->addrFormat($toaddr);
		}

		$to = implode(', ', $toArr);

		if (empty($this->Sender)) {
			$params = ' ';
		}
		else {
			$params = sprintf('-f%s', $this->Sender);
		}

		if (($this->Sender != '') && !ini_get('safe_mode')) {
			$old_from = ini_get('sendmail_from');
			ini_set('sendmail_from', $this->Sender);
		}

		$result = false;
		if ($this->SingleTo && (1 < count($toArr))) {
			foreach ($toArr as $toAddr) {
				$result = $this->mailPassthru($toAddr, $this->Subject, $body, $header, $params);
				$this->doCallback($result, array($toAddr), $this->cc, $this->bcc, $this->Subject, $body, $this->From);
			}
		}
		else {
			$result = $this->mailPassthru($to, $this->Subject, $body, $header, $params);
			$this->doCallback($result, $this->to, $this->cc, $this->bcc, $this->Subject, $body, $this->From);
		}

		if (isset($old_from)) {
			ini_set('sendmail_from', $old_from);
		}

		if (!$result) {
			throw new phpmailerException($this->lang('instantiate'), self::STOP_CRITICAL);
		}

		return true;
	}

	public function getSMTPInstance()
	{
		if (!is_object($this->smtp)) {
			$this->smtp = new SMTP();
		}

		return $this->smtp;
	}

	protected function smtpSend($header, $body)
	{
		$bad_rcpt = array();

		if (!$this->smtpConnect()) {
			throw new phpmailerException($this->lang('smtp_connect_failed'), self::STOP_CRITICAL);
		}

		if ('' == $this->Sender) {
			$smtp_from = $this->From;
		}
		else {
			$smtp_from = $this->Sender;
		}

		if (!$this->smtp->mail($smtp_from)) {
			$this->setError($this->lang('from_failed') . $smtp_from . ' : ' . implode(',', $this->smtp->getError()));
			throw new phpmailerException($this->ErrorInfo, self::STOP_CRITICAL);
		}

		foreach ($this->to as $to) {
			if (!$this->smtp->recipient($to[0])) {
				$bad_rcpt[] = $to[0];
				$isSent = false;
			}
			else {
				$isSent = true;
			}

			$this->doCallback($isSent, array($to[0]), array(), array(), $this->Subject, $body, $this->From);
		}

		foreach ($this->cc as $cc) {
			if (!$this->smtp->recipient($cc[0])) {
				$bad_rcpt[] = $cc[0];
				$isSent = false;
			}
			else {
				$isSent = true;
			}

			$this->doCallback($isSent, array(), array($cc[0]), array(), $this->Subject, $body, $this->From);
		}

		foreach ($this->bcc as $bcc) {
			if (!$this->smtp->recipient($bcc[0])) {
				$bad_rcpt[] = $bcc[0];
				$isSent = false;
			}
			else {
				$isSent = true;
			}

			$this->doCallback($isSent, array(), array(), array($bcc[0]), $this->Subject, $body, $this->From);
		}

		if ((count($bad_rcpt) < count($this->all_recipients)) && !$this->smtp->data($header . $body)) {
			throw new phpmailerException($this->lang('data_not_accepted'), self::STOP_CRITICAL);
		}

		if ($this->SMTPKeepAlive) {
			$this->smtp->reset();
		}
		else {
			$this->smtp->quit();
			$this->smtp->close();
		}

		if (0 < count($bad_rcpt)) {
			throw new phpmailerException($this->lang('recipients_failed') . implode(', ', $bad_rcpt), self::STOP_CONTINUE);
		}

		return true;
	}

	public function smtpConnect($options = array())
	{
		if (is_null($this->smtp)) {
			$this->smtp = $this->getSMTPInstance();
		}

		if ($this->smtp->connected()) {
			return true;
		}

		$this->smtp->setTimeout($this->Timeout);
		$this->smtp->setDebugLevel($this->SMTPDebug);
		$this->smtp->setDebugOutput($this->Debugoutput);
		$this->smtp->setVerp($this->do_verp);
		$hosts = explode(';', $this->Host);
		$lastexception = NULL;

		foreach ($hosts as $hostentry) {
			$hostinfo = array();

			if (!preg_match('/^((ssl|tls):\\/\\/)*([a-zA-Z0-9\\.-]*):?([0-9]*)$/', trim($hostentry), $hostinfo)) {
				continue;
			}

			$prefix = '';
			$tls = $this->SMTPSecure == 'tls';
			if (($hostinfo[2] == 'ssl') || (($hostinfo[2] == '') && ($this->SMTPSecure == 'ssl'))) {
				$prefix = 'ssl://';
				$tls = false;
			}
			else if ($hostinfo[2] == 'tls') {
				$tls = true;
			}

			$host = $hostinfo[3];
			$port = $this->Port;
			$tport = (int) $hostinfo[4];
			if ((0 < $tport) && ($tport < 65536)) {
				$port = $tport;
			}

			if ($this->smtp->connect($prefix . $host, $port, $this->Timeout, $options)) {
				try {
					if ($this->Helo) {
						$hello = $this->Helo;
					}
					else {
						$hello = $this->serverHostname();
					}

					$this->smtp->hello($hello);

					if ($tls) {
						if (!$this->smtp->startTLS()) {
							throw new phpmailerException($this->lang('connect_host'));
						}

						$this->smtp->hello($hello);
					}

					if ($this->SMTPAuth) {
						if (!$this->smtp->authenticate($this->Username, $this->Password, $this->AuthType, $this->Realm, $this->Workstation)) {
							throw new phpmailerException($this->lang('authenticate'));
						}
					}

					return true;
				}
				catch (phpmailerException $exc) {
					$lastexception = $exc;
					$this->smtp->quit();
				}
			}
		}

		$this->smtp->close();
		if ($this->exceptions && !is_null($lastexception)) {
			throw $lastexception;
		}

		return false;
	}

	public function smtpClose()
	{
		if ($this->smtp !== NULL) {
			if ($this->smtp->connected()) {
				$this->smtp->quit();
				$this->smtp->close();
			}
		}
	}

	public function setLanguage($langcode = 'en', $lang_path = '')
	{
		$PHPMAILER_LANG = array('authenticate' => 'SMTP Error: Could not authenticate.', 'connect_host' => 'SMTP Error: Could not connect to SMTP host.', 'data_not_accepted' => 'SMTP Error: data not accepted.', 'empty_message' => 'Message body empty', 'encoding' => 'Unknown encoding: ', 'execute' => 'Could not execute: ', 'file_access' => 'Could not access file: ', 'file_open' => 'File Error: Could not open file: ', 'from_failed' => 'The following From address failed: ', 'instantiate' => 'Could not instantiate mail function.', 'invalid_address' => 'Invalid address', 'mailer_not_supported' => ' mailer is not supported.', 'provide_address' => 'You must provide at least one recipient email address.', 'recipients_failed' => 'SMTP Error: The following recipients failed: ', 'signing' => 'Signing Error: ', 'smtp_connect_failed' => 'SMTP connect() failed.', 'smtp_error' => 'SMTP server error: ', 'variable_set' => 'Cannot set or reset variable: ');

		if (empty($lang_path)) {
			$lang_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
		}

		$foundlang = true;
		$lang_file = $lang_path . 'phpmailer.lang-' . $langcode . '.php';

		if ($langcode != 'en') {
			if (!is_readable($lang_file)) {
				$foundlang = false;
			}
			else {
				$foundlang = include $lang_file;
			}
		}

		$this->language = $PHPMAILER_LANG;
		return (bool) $foundlang;
	}

	public function getTranslations()
	{
		return $this->language;
	}

	public function addrAppend($type, $addr)
	{
		$addresses = array();

		foreach ($addr as $address) {
			$addresses[] = $this->addrFormat($address);
		}

		return $type . ': ' . implode(', ', $addresses) . $this->LE;
	}

	public function addrFormat($addr)
	{
		if (empty($addr[1])) {
			return $this->secureHeader($addr[0]);
		}
		else {
			return $this->encodeHeader($this->secureHeader($addr[1]), 'phrase') . ' <' . $this->secureHeader($addr[0]) . '>';
		}
	}

	public function wrapText($message, $length, $qp_mode = false)
	{
		if ($qp_mode) {
			$soft_break = sprintf(' =%s', $this->LE);
		}
		else {
			$soft_break = $this->LE;
		}

		$is_utf8 = strtolower($this->CharSet) == 'utf-8';
		$lelen = strlen($this->LE);
		$crlflen = strlen(self::CRLF);
		$message = $this->fixEOL($message);

		if (substr($message, 0 - $lelen) == $this->LE) {
			$message = substr($message, 0, 0 - $lelen);
		}

		$lines = explode($this->LE, $message);
		$message = '';

		foreach ($lines as $line) {
			$words = explode(' ', $line);
			$buf = '';
			$firstword = true;

			foreach ($words as $word) {
				if ($qp_mode && ($length < strlen($word))) {
					$space_left = $length - strlen($buf) - $crlflen;

					if (!$firstword) {
						if (20 < $space_left) {
							$len = $space_left;

							if ($is_utf8) {
								$len = $this->utf8CharBoundary($word, $len);
							}
							else if (substr($word, $len - 1, 1) == '=') {
								$len--;
							}
							else if (substr($word, $len - 2, 1) == '=') {
								$len -= 2;
							}

							$part = substr($word, 0, $len);
							$word = substr($word, $len);
							$buf .= ' ' . $part;
							$message .= $buf . sprintf('=%s', self::CRLF);
						}
						else {
							$message .= $buf . $soft_break;
						}

						$buf = '';
					}

					while (0 < strlen($word)) {
						if ($length <= 0) {
							break;
						}

						$len = $length;

						if ($is_utf8) {
							$len = $this->utf8CharBoundary($word, $len);
						}
						else if (substr($word, $len - 1, 1) == '=') {
							$len--;
						}
						else if (substr($word, $len - 2, 1) == '=') {
							$len -= 2;
						}

						$part = substr($word, 0, $len);
						$word = substr($word, $len);

						if (0 < strlen($word)) {
							$message .= $part . sprintf('=%s', self::CRLF);
						}
						else {
							$buf = $part;
						}
					}
				}
				else {
					$buf_o = $buf;

					if (!$firstword) {
						$buf .= ' ';
					}

					$buf .= $word;
					if (($length < strlen($buf)) && ($buf_o != '')) {
						$message .= $buf_o . $soft_break;
						$buf = $word;
					}
				}

				$firstword = false;
			}

			$message .= $buf . self::CRLF;
		}

		return $message;
	}

	public function utf8CharBoundary($encodedText, $maxLength)
	{
		$foundSplitPos = false;
		$lookBack = 3;

		while (!$foundSplitPos) {
			$lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
			$encodedCharPos = strpos($lastChunk, '=');

			if (false !== $encodedCharPos) {
				$hex = substr($encodedText, ($maxLength - $lookBack) + $encodedCharPos + 1, 2);
				$dec = hexdec($hex);

				if ($dec < 128) {
					if (0 < $encodedCharPos) {
						$maxLength = $maxLength - $lookBack - $encodedCharPos;
					}

					$foundSplitPos = true;
				}
				else if (192 <= $dec) {
					$maxLength = $maxLength - $lookBack - $encodedCharPos;
					$foundSplitPos = true;
				}
				else if ($dec < 192) {
					$lookBack += 3;
				}
			}
			else {
				$foundSplitPos = true;
			}
		}

		return $maxLength;
	}

	public function setWordWrap()
	{
		if ($this->WordWrap < 1) {
			return NULL;
		}

		switch ($this->message_type) {
		case 'alt':
		case 'alt_inline':
		case 'alt_attach':
		case 'alt_inline_attach':
			$this->AltBody = $this->wrapText($this->AltBody, $this->WordWrap);
			break;

		default:
			$this->Body = $this->wrapText($this->Body, $this->WordWrap);
			break;
		}
	}

	public function createHeader()
	{
		$result = '';
		$uniq_id = md5(uniqid(time()));
		$this->boundary[1] = 'b1_' . $uniq_id;
		$this->boundary[2] = 'b2_' . $uniq_id;
		$this->boundary[3] = 'b3_' . $uniq_id;

		if ($this->MessageDate == '') {
			$this->MessageDate = self::rfcDate();
		}

		$result .= $this->headerLine('Date', $this->MessageDate);

		if ($this->SingleTo) {
			if ($this->Mailer != 'mail') {
				foreach ($this->to as $toaddr) {
					$this->SingleToArray[] = $this->addrFormat($toaddr);
				}
			}
		}
		else if (0 < count($this->to)) {
			if ($this->Mailer != 'mail') {
				$result .= $this->addrAppend('To', $this->to);
			}
		}
		else if (count($this->cc) == 0) {
			$result .= $this->headerLine('To', 'undisclosed-recipients:;');
		}

		$result .= $this->addrAppend('From', array(
	array(trim($this->From), $this->FromName)
	));

		if (0 < count($this->cc)) {
			$result .= $this->addrAppend('Cc', $this->cc);
		}

		if ((($this->Mailer == 'sendmail') || ($this->Mailer == 'qmail') || ($this->Mailer == 'mail')) && (0 < count($this->bcc))) {
			$result .= $this->addrAppend('Bcc', $this->bcc);
		}

		if (0 < count($this->ReplyTo)) {
			$result .= $this->addrAppend('Reply-To', $this->ReplyTo);
		}

		if ($this->Mailer != 'mail') {
			$result .= $this->headerLine('Subject', $this->encodeHeader($this->secureHeader($this->Subject)));
		}

		if ($this->MessageID != '') {
			$this->lastMessageID = $this->MessageID;
		}
		else {
			$this->lastMessageID = sprintf('<%s@%s>', $uniq_id, $this->ServerHostname());
		}

		$result .= $this->HeaderLine('Message-ID', $this->lastMessageID);
		$result .= $this->headerLine('X-Priority', $this->Priority);

		if ($this->XMailer == '') {
			$result .= $this->headerLine('X-Mailer', 'PHPMailer ' . $this->Version . ' (https://github.com/PHPMailer/PHPMailer/)');
		}
		else {
			$myXmailer = trim($this->XMailer);

			if ($myXmailer) {
				$result .= $this->headerLine('X-Mailer', $myXmailer);
			}
		}

		if ($this->ConfirmReadingTo != '') {
			$result .= $this->headerLine('Disposition-Notification-To', '<' . trim($this->ConfirmReadingTo) . '>');
		}

		foreach ($this->CustomHeader as $header) {
			$result .= $this->headerLine(trim($header[0]), $this->encodeHeader(trim($header[1])));
		}

		if (!$this->sign_key_file) {
			$result .= $this->headerLine('MIME-Version', '1.0');
			$result .= $this->getMailMIME();
		}

		return $result;
	}

	public function getMailMIME()
	{
		$result = '';
		$ismultipart = true;

		switch ($this->message_type) {
		case 'inline':
			$result .= $this->headerLine('Content-Type', 'multipart/related;');
			$result .= $this->textLine('	boundary="' . $this->boundary[1] . '"');
			break;

		case 'attach':
		case 'inline_attach':
		case 'alt_attach':
		case 'alt_inline_attach':
			$result .= $this->headerLine('Content-Type', 'multipart/mixed;');
			$result .= $this->textLine('	boundary="' . $this->boundary[1] . '"');
			break;

		case 'alt':
		case 'alt_inline':
			$result .= $this->headerLine('Content-Type', 'multipart/alternative;');
			$result .= $this->textLine('	boundary="' . $this->boundary[1] . '"');
			break;

		default:
			$result .= $this->textLine('Content-Type: ' . $this->ContentType . '; charset=' . $this->CharSet);
			$ismultipart = false;
			break;
		}

		if ($this->Encoding != '7bit') {
			if ($ismultipart) {
				if ($this->Encoding == '8bit') {
					$result .= $this->headerLine('Content-Transfer-Encoding', '8bit');
				}
			}
			else {
				$result .= $this->headerLine('Content-Transfer-Encoding', $this->Encoding);
			}
		}

		if ($this->Mailer != 'mail') {
			$result .= $this->LE;
		}

		return $result;
	}

	public function getSentMIMEMessage()
	{
		return $this->MIMEHeader . $this->mailHeader . self::CRLF . $this->MIMEBody;
	}

	public function createBody()
	{
		$body = '';

		if ($this->sign_key_file) {
			$body .= $this->getMailMIME() . $this->LE;
		}

		$this->setWordWrap();
		$bodyEncoding = $this->Encoding;
		$bodyCharSet = $this->CharSet;
		if (($bodyEncoding == '8bit') && !$this->has8bitChars($this->Body)) {
			$bodyEncoding = '7bit';
			$bodyCharSet = 'us-ascii';
		}

		$altBodyEncoding = $this->Encoding;
		$altBodyCharSet = $this->CharSet;
		if (($altBodyEncoding == '8bit') && !$this->has8bitChars($this->AltBody)) {
			$altBodyEncoding = '7bit';
			$altBodyCharSet = 'us-ascii';
		}

		switch ($this->message_type) {
		case 'inline':
			$body .= $this->getBoundary($this->boundary[1], $bodyCharSet, '', $bodyEncoding);
			$body .= $this->encodeString($this->Body, $bodyEncoding);
			$body .= $this->LE . $this->LE;
			$body .= $this->attachAll('inline', $this->boundary[1]);
			break;

		case 'attach':
			$body .= $this->getBoundary($this->boundary[1], $bodyCharSet, '', $bodyEncoding);
			$body .= $this->encodeString($this->Body, $bodyEncoding);
			$body .= $this->LE . $this->LE;
			$body .= $this->attachAll('attachment', $this->boundary[1]);
			break;

		case 'inline_attach':
			$body .= $this->textLine('--' . $this->boundary[1]);
			$body .= $this->headerLine('Content-Type', 'multipart/related;');
			$body .= $this->textLine('	boundary="' . $this->boundary[2] . '"');
			$body .= $this->LE;
			$body .= $this->getBoundary($this->boundary[2], $bodyCharSet, '', $bodyEncoding);
			$body .= $this->encodeString($this->Body, $bodyEncoding);
			$body .= $this->LE . $this->LE;
			$body .= $this->attachAll('inline', $this->boundary[2]);
			$body .= $this->LE;
			$body .= $this->attachAll('attachment', $this->boundary[1]);
			break;

		case 'alt':
			$body .= $this->getBoundary($this->boundary[1], $altBodyCharSet, 'text/plain', $altBodyEncoding);
			$body .= $this->encodeString($this->AltBody, $altBodyEncoding);
			$body .= $this->LE . $this->LE;
			$body .= $this->getBoundary($this->boundary[1], $bodyCharSet, 'text/html', $bodyEncoding);
			$body .= $this->encodeString($this->Body, $bodyEncoding);
			$body .= $this->LE . $this->LE;

			if (!empty($this->Ical)) {
				$body .= $this->getBoundary($this->boundary[1], '', 'text/calendar; method=REQUEST', '');
				$body .= $this->encodeString($this->Ical, $this->Encoding);
				$body .= $this->LE . $this->LE;
			}

			$body .= $this->endBoundary($this->boundary[1]);
			break;

		case 'alt_inline':
			$body .= $this->getBoundary($this->boundary[1], $altBodyCharSet, 'text/plain', $altBodyEncoding);
			$body .= $this->encodeString($this->AltBody, $altBodyEncoding);
			$body .= $this->LE . $this->LE;
			$body .= $this->textLine('--' . $this->boundary[1]);
			$body .= $this->headerLine('Content-Type', 'multipart/related;');
			$body .= $this->textLine('	boundary="' . $this->boundary[2] . '"');
			$body .= $this->LE;
			$body .= $this->getBoundary($this->boundary[2], $bodyCharSet, 'text/html', $bodyEncoding);
			$body .= $this->encodeString($this->Body, $bodyEncoding);
			$body .= $this->LE . $this->LE;
			$body .= $this->attachAll('inline', $this->boundary[2]);
			$body .= $this->LE;
			$body .= $this->endBoundary($this->boundary[1]);
			break;

		case 'alt_attach':
			$body .= $this->textLine('--' . $this->boundary[1]);
			$body .= $this->headerLine('Content-Type', 'multipart/alternative;');
			$body .= $this->textLine('	boundary="' . $this->boundary[2] . '"');
			$body .= $this->LE;
			$body .= $this->getBoundary($this->boundary[2], $altBodyCharSet, 'text/plain', $altBodyEncoding);
			$body .= $this->encodeString($this->AltBody, $altBodyEncoding);
			$body .= $this->LE . $this->LE;
			$body .= $this->getBoundary($this->boundary[2], $bodyCharSet, 'text/html', $bodyEncoding);
			$body .= $this->encodeString($this->Body, $bodyEncoding);
			$body .= $this->LE . $this->LE;
			$body .= $this->endBoundary($this->boundary[2]);
			$body .= $this->LE;
			$body .= $this->attachAll('attachment', $this->boundary[1]);
			break;

		case 'alt_inline_attach':
			$body .= $this->textLine('--' . $this->boundary[1]);
			$body .= $this->headerLine('Content-Type', 'multipart/alternative;');
			$body .= $this->textLine('	boundary="' . $this->boundary[2] . '"');
			$body .= $this->LE;
			$body .= $this->getBoundary($this->boundary[2], $altBodyCharSet, 'text/plain', $altBodyEncoding);
			$body .= $this->encodeString($this->AltBody, $altBodyEncoding);
			$body .= $this->LE . $this->LE;
			$body .= $this->textLine('--' . $this->boundary[2]);
			$body .= $this->headerLine('Content-Type', 'multipart/related;');
			$body .= $this->textLine('	boundary="' . $this->boundary[3] . '"');
			$body .= $this->LE;
			$body .= $this->getBoundary($this->boundary[3], $bodyCharSet, 'text/html', $bodyEncoding);
			$body .= $this->encodeString($this->Body, $bodyEncoding);
			$body .= $this->LE . $this->LE;
			$body .= $this->attachAll('inline', $this->boundary[3]);
			$body .= $this->LE;
			$body .= $this->endBoundary($this->boundary[2]);
			$body .= $this->LE;
			$body .= $this->attachAll('attachment', $this->boundary[1]);
			break;

		default:
			$body .= $this->encodeString($this->Body, $bodyEncoding);
			break;
		}

		if ($this->isError()) {
			$body = '';
		}
		else if ($this->sign_key_file) {
			try {
				if (!defined('PKCS7_TEXT')) {
					throw new phpmailerException($this->lang('signing') . ' OpenSSL extension missing.');
				}

				$file = tempnam(sys_get_temp_dir(), 'mail');

				if (false === file_put_contents($file, $body)) {
					throw new phpmailerException($this->lang('signing') . ' Could not write temp file');
				}

				$signed = tempnam(sys_get_temp_dir(), 'signed');

				if (@openssl_pkcs7_sign($file, $signed, 'file://' . realpath($this->sign_cert_file), array('file://' . realpath($this->sign_key_file), $this->sign_key_pass), NULL)) {
					@unlink($file);
					$body = file_get_contents($signed);
					@unlink($signed);
				}
				else {
					@unlink($file);
					@unlink($signed);
					throw new phpmailerException($this->lang('signing') . openssl_error_string());
				}
			}
			catch (phpmailerException $exc) {
				$body = '';

				if ($this->exceptions) {
					throw $exc;
				}
			}
		}

		return $body;
	}

	protected function getBoundary($boundary, $charSet, $contentType, $encoding)
	{
		$result = '';

		if ($charSet == '') {
			$charSet = $this->CharSet;
		}

		if ($contentType == '') {
			$contentType = $this->ContentType;
		}

		if ($encoding == '') {
			$encoding = $this->Encoding;
		}

		$result .= $this->textLine('--' . $boundary);
		$result .= sprintf('Content-Type: %s; charset=%s', $contentType, $charSet);
		$result .= $this->LE;

		if ($encoding != '7bit') {
			$result .= $this->headerLine('Content-Transfer-Encoding', $encoding);
		}

		$result .= $this->LE;
		return $result;
	}

	protected function endBoundary($boundary)
	{
		return $this->LE . '--' . $boundary . '--' . $this->LE;
	}

	protected function setMessageType()
	{
		$type = array();

		if ($this->alternativeExists()) {
			$type[] = 'alt';
		}

		if ($this->inlineImageExists()) {
			$type[] = 'inline';
		}

		if ($this->attachmentExists()) {
			$type[] = 'attach';
		}

		$this->message_type = implode('_', $type);

		if ($this->message_type == '') {
			$this->message_type = 'plain';
		}
	}

	public function headerLine($name, $value)
	{
		return $name . ': ' . $value . $this->LE;
	}

	public function textLine($value)
	{
		return $value . $this->LE;
	}

	public function addAttachment($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment')
	{
		try {
			if (!@is_file($path)) {
				throw new phpmailerException($this->lang('file_access') . $path, self::STOP_CONTINUE);
			}

			if ($type == '') {
				$type = self::filenameToType($path);
			}

			$filename = basename($path);

			if ($name == '') {
				$name = $filename;
			}

			$this->attachment[] = array($path, $filename, $name, $encoding, $type, false, $disposition, 0);
		}
		catch (phpmailerException $exc) {
			$this->setError($exc->getMessage());
			$this->edebug($exc->getMessage());

			if ($this->exceptions) {
				throw $exc;
			}

			return false;
		}

		return true;
	}

	public function getAttachments()
	{
		return $this->attachment;
	}

	protected function attachAll($disposition_type, $boundary)
	{
		$mime = array();
		$cidUniq = array();
		$incl = array();

		foreach ($this->attachment as $attachment) {
			if ($attachment[6] == $disposition_type) {
				$string = '';
				$path = '';
				$bString = $attachment[5];

				if ($bString) {
					$string = $attachment[0];
				}
				else {
					$path = $attachment[0];
				}

				$inclhash = md5(serialize($attachment));

				if (in_array($inclhash, $incl)) {
					continue;
				}

				$incl[] = $inclhash;
				$name = $attachment[2];
				$encoding = $attachment[3];
				$type = $attachment[4];
				$disposition = $attachment[6];
				$cid = $attachment[7];
				if (($disposition == 'inline') && isset($cidUniq[$cid])) {
					continue;
				}

				$cidUniq[$cid] = true;
				$mime[] = sprintf('--%s%s', $boundary, $this->LE);
				$mime[] = sprintf('Content-Type: %s; name="%s"%s', $type, $this->encodeHeader($this->secureHeader($name)), $this->LE);

				if ($encoding != '7bit') {
					$mime[] = sprintf('Content-Transfer-Encoding: %s%s', $encoding, $this->LE);
				}

				if ($disposition == 'inline') {
					$mime[] = sprintf('Content-ID: <%s>%s', $cid, $this->LE);
				}

				if (!empty($disposition)) {
					$encoded_name = $this->encodeHeader($this->secureHeader($name));

					if (preg_match('/[ \\(\\)<>@,;:\\"\\/\\[\\]\\?=]/', $encoded_name)) {
						$mime[] = sprintf('Content-Disposition: %s; filename="%s"%s', $disposition, $encoded_name, $this->LE . $this->LE);
					}
					else {
						$mime[] = sprintf('Content-Disposition: %s; filename=%s%s', $disposition, $encoded_name, $this->LE . $this->LE);
					}
				}
				else {
					$mime[] = $this->LE;
				}

				if ($bString) {
					$mime[] = $this->encodeString($string, $encoding);

					if ($this->isError()) {
						return '';
					}

					$mime[] = $this->LE . $this->LE;
				}
				else {
					$mime[] = $this->encodeFile($path, $encoding);

					if ($this->isError()) {
						return '';
					}

					$mime[] = $this->LE . $this->LE;
				}
			}
		}

		$mime[] = sprintf('--%s--%s', $boundary, $this->LE);
		return implode('', $mime);
	}

	protected function encodeFile($path, $encoding = 'base64')
	{
		try {
			if (!is_readable($path)) {
				throw new phpmailerException($this->lang('file_open') . $path, self::STOP_CONTINUE);
			}

			$magic_quotes = get_magic_quotes_runtime();

			if ($magic_quotes) {
				if (version_compare(PHP_VERSION, '5.3.0', '<')) {
					set_magic_quotes_runtime(false);
				}
				else {
					ini_set('magic_quotes_runtime', false);
				}
			}

			$file_buffer = file_get_contents($path);
			$file_buffer = $this->encodeString($file_buffer, $encoding);

			if ($magic_quotes) {
				if (version_compare(PHP_VERSION, '5.3.0', '<')) {
					set_magic_quotes_runtime($magic_quotes);
				}
				else {
					ini_set('magic_quotes_runtime', $magic_quotes);
				}
			}

			return $file_buffer;
		}
		catch (Exception $exc) {
			$this->setError($exc->getMessage());
			return '';
		}
	}

	public function encodeString($str, $encoding = 'base64')
	{
		$encoded = '';

		switch (strtolower($encoding)) {
		case 'base64':
			$encoded = chunk_split(base64_encode($str), 76, $this->LE);
			break;

		case '7bit':
		case '8bit':
			$encoded = $this->fixEOL($str);

			if (substr($encoded, 0 - strlen($this->LE)) != $this->LE) {
				$encoded .= $this->LE;
			}

			break;

		case 'binary':
			$encoded = $str;
			break;

		case 'quoted-printable':
			$encoded = $this->encodeQP($str);
			break;

		default:
			$this->setError($this->lang('encoding') . $encoding);
			break;
		}

		return $encoded;
	}

	public function encodeHeader($str, $position = 'text')
	{
		$matchcount = 0;

		switch (strtolower($position)) {
		case 'phrase':
			if (!preg_match('/[\\200-\\377]/', $str)) {
				$encoded = addcslashes($str, "\x00..\x1f\x7f\\\"");
				if (($str == $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\\/=?^_`{|}~ -]/', $str)) {
					return $encoded;
				}
				else {
					return '"' . $encoded . '"';
				}
			}

			$matchcount = preg_match_all('/[^\\040\\041\\043-\\133\\135-\\176]/', $str, $matches);
			break;

		case 'comment':
			$matchcount = preg_match_all('/[()"]/', $str, $matches);
		case 'text':
		default:
			$matchcount += preg_match_all('/[\\000-\\010\\013\\014\\016-\\037\\177-\\377]/', $str, $matches);
			break;
		}

		if ($matchcount == 0) {
			return $str;
		}

		$maxlen = 75 - 7 - strlen($this->CharSet);

		if ((strlen($str) / 3) < $matchcount) {
			$encoding = 'B';
			if (function_exists('mb_strlen') && $this->hasMultiBytes($str)) {
				$encoded = $this->base64EncodeWrapMB($str, "\n");
			}
			else {
				$encoded = base64_encode($str);
				$maxlen -= $maxlen % 4;
				$encoded = trim(chunk_split($encoded, $maxlen, "\n"));
			}
		}
		else {
			$encoding = 'Q';
			$encoded = $this->encodeQ($str, $position);
			$encoded = $this->wrapText($encoded, $maxlen, true);
			$encoded = str_replace('=' . self::CRLF, "\n", trim($encoded));
		}

		$encoded = preg_replace('/^(.*)$/m', ' =?' . $this->CharSet . '?' . $encoding . '?\\1?=', $encoded);
		$encoded = trim(str_replace("\n", $this->LE, $encoded));
		return $encoded;
	}

	public function hasMultiBytes($str)
	{
		if (function_exists('mb_strlen')) {
			return mb_strlen($str, $this->CharSet) < strlen($str);
		}
		else {
			return false;
		}
	}

	public function has8bitChars($text)
	{
		return (bool) preg_match('/[\\x80-\\xFF]/', $text);
	}

	public function base64EncodeWrapMB($str, $linebreak = NULL)
	{
		$start = '=?' . $this->CharSet . '?B?';
		$end = '?=';
		$encoded = '';

		if ($linebreak === NULL) {
			$linebreak = $this->LE;
		}

		$mb_length = mb_strlen($str, $this->CharSet);
		$length = 75 - strlen($start) - strlen($end);
		$ratio = $mb_length / strlen($str);
		$avgLength = floor($length * $ratio * 0.75);

		for ($i = 0; $i < $mb_length; $i += $offset) {
			$lookBack = 0;

			do {
				$offset = $avgLength - $lookBack;
				$chunk = mb_substr($str, $i, $offset, $this->CharSet);
				$chunk = base64_encode($chunk);
				$lookBack++;
			} while ($length < strlen($chunk));

			$encoded .= $chunk . $linebreak;
		}

		$encoded = substr($encoded, 0, 0 - strlen($linebreak));
		return $encoded;
	}

	public function encodeQP($string, $line_max = 76)
	{
		if (function_exists('quoted_printable_encode')) {
			return $this->fixEOL(quoted_printable_encode($string));
		}

		$string = str_replace(array('%20', '%0D%0A.', '%0D%0A', '%'), array(' ', "\r\n=2E", "\r\n", '='), rawurlencode($string));
		$string = preg_replace('/[^\\r\\n]{' . ($line_max - 3) . '}[^=\\r\\n]{2}/', "\$0=\r\n", $string);
		return $this->fixEOL($string);
	}

	public function encodeQPphp($string, $line_max = 76, $space_conv = false)
	{
		return $this->encodeQP($string, $line_max);
	}

	public function encodeQ($str, $position = 'text')
	{
		$pattern = '';
		$encoded = str_replace(array("\r", "\n"), '', $str);

		switch (strtolower($position)) {
		case 'phrase':
			$pattern = '^A-Za-z0-9!*+\\/ -';
			break;

		case 'comment':
			$pattern = '\\(\\)"';
		case 'text':
		default:
			$pattern = '\\000-\\011\\013\\014\\016-\\037\\075\\077\\137\\177-\\377' . $pattern;
			break;
		}

		$matches = array();

		if (preg_match_all('/[' . $pattern . ']/', $encoded, $matches)) {
			$eqkey = array_search('=', $matches[0]);

			if (false !== $eqkey) {
				unset($matches[0][$eqkey]);
				array_unshift($matches[0], '=');
			}

			foreach (array_unique($matches[0]) as $char) {
				$encoded = str_replace($char, '=' . sprintf('%02X', ord($char)), $encoded);
			}
		}

		return str_replace(' ', '_', $encoded);
	}

	public function addStringAttachment($string, $filename, $encoding = 'base64', $type = '', $disposition = 'attachment')
	{
		if ($type == '') {
			$type = self::filenameToType($filename);
		}

		$this->attachment[] = array($string, $filename, basename($filename), $encoding, $type, true, $disposition, 0);
	}

	public function addEmbeddedImage($path, $cid, $name = '', $encoding = 'base64', $type = '', $disposition = 'inline')
	{
		if (!@is_file($path)) {
			$this->setError($this->lang('file_access') . $path);
			return false;
		}

		if ($type == '') {
			$type = self::filenameToType($path);
		}

		$filename = basename($path);

		if ($name == '') {
			$name = $filename;
		}

		$this->attachment[] = array($path, $filename, $name, $encoding, $type, false, $disposition, $cid);
		return true;
	}

	public function addStringEmbeddedImage($string, $cid, $name = '', $encoding = 'base64', $type = '', $disposition = 'inline')
	{
		if ($type == '') {
			$type = self::filenameToType($name);
		}

		$this->attachment[] = array($string, $name, $name, $encoding, $type, true, $disposition, $cid);
		return true;
	}

	public function inlineImageExists()
	{
		foreach ($this->attachment as $attachment) {
			if ($attachment[6] == 'inline') {
				return true;
			}
		}

		return false;
	}

	public function attachmentExists()
	{
		foreach ($this->attachment as $attachment) {
			if ($attachment[6] == 'attachment') {
				return true;
			}
		}

		return false;
	}

	public function alternativeExists()
	{
		return !empty($this->AltBody);
	}

	public function clearAddresses()
	{
		foreach ($this->to as $to) {
			unset($this->all_recipients[strtolower($to[0])]);
		}

		$this->to = array();
	}

	public function clearCCs()
	{
		foreach ($this->cc as $cc) {
			unset($this->all_recipients[strtolower($cc[0])]);
		}

		$this->cc = array();
	}

	public function clearBCCs()
	{
		foreach ($this->bcc as $bcc) {
			unset($this->all_recipients[strtolower($bcc[0])]);
		}

		$this->bcc = array();
	}

	public function clearReplyTos()
	{
		$this->ReplyTo = array();
	}

	public function clearAllRecipients()
	{
		$this->to = array();
		$this->cc = array();
		$this->bcc = array();
		$this->all_recipients = array();
	}

	public function clearAttachments()
	{
		$this->attachment = array();
	}

	public function clearCustomHeaders()
	{
		$this->CustomHeader = array();
	}

	protected function setError($msg)
	{
		$this->error_count++;
		if (($this->Mailer == 'smtp') && !is_null($this->smtp)) {
			$lasterror = $this->smtp->getError();
			if (!empty($lasterror) && array_key_exists('smtp_msg', $lasterror)) {
				$msg .= '<p>' . $this->lang('smtp_error') . $lasterror['smtp_msg'] . "</p>\n";
			}
		}

		$this->ErrorInfo = $msg;
	}

	static public function rfcDate()
	{
		date_default_timezone_set(@date_default_timezone_get());
		return date('D, j M Y H:i:s O');
	}

	protected function serverHostname()
	{
		$result = 'localhost.localdomain';

		if (!empty($this->Hostname)) {
			$result = $this->Hostname;
		}
		else {
			if (isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER) && !empty($_SERVER['SERVER_NAME'])) {
				$result = $_SERVER['SERVER_NAME'];
			}
			else {
				if (function_exists('gethostname') && (gethostname() !== false)) {
					$result = gethostname();
				}
				else if (php_uname('n') !== false) {
					$result = php_uname('n');
				}
			}
		}

		return $result;
	}

	protected function lang($key)
	{
		if (count($this->language) < 1) {
			$this->setLanguage('en');
		}

		if (isset($this->language[$key])) {
			return $this->language[$key];
		}
		else {
			return 'Language string failed to load: ' . $key;
		}
	}

	public function isError()
	{
		return 0 < $this->error_count;
	}

	public function fixEOL($str)
	{
		$nstr = str_replace(array("\r\n", "\r"), "\n", $str);

		if ($this->LE !== "\n") {
			$nstr = str_replace("\n", $this->LE, $nstr);
		}

		return $nstr;
	}

	public function addCustomHeader($name, $value = NULL)
	{
		if ($value === NULL) {
			$this->CustomHeader[] = explode(':', $name, 2);
		}
		else {
			$this->CustomHeader[] = array($name, $value);
		}
	}

	public function msgHTML($message, $basedir = '', $advanced = false)
	{
		preg_match_all('/(src|background)=["\'](.*)["\']/Ui', $message, $images);

		if (isset($images[2])) {
			foreach ($images[2] as $imgindex => $url) {
				if (preg_match('#^data:(image[^;,]*)(;base64)?,#', $url, $match)) {
					$data = substr($url, strpos($url, ','));

					if ($match[2]) {
						$data = base64_decode($data);
					}
					else {
						$data = rawurldecode($data);
					}

					$cid = md5($url) . '@phpmailer.0';

					if ($this->addStringEmbeddedImage($data, $cid, '', 'base64', $match[1])) {
						$message = str_replace($images[0][$imgindex], $images[1][$imgindex] . '="cid:' . $cid . '"', $message);
					}
				}
				else if (!preg_match('#^[A-z]+://#', $url)) {
					$filename = basename($url);
					$directory = dirname($url);

					if ($directory == '.') {
						$directory = '';
					}

					$cid = md5($url) . '@phpmailer.0';
					if ((1 < strlen($basedir)) && (substr($basedir, -1) != '/')) {
						$basedir .= '/';
					}

					if ((1 < strlen($directory)) && (substr($directory, -1) != '/')) {
						$directory .= '/';
					}

					if ($this->addEmbeddedImage($basedir . $directory . $filename, $cid, $filename, 'base64', self::_mime_types((string) self::mb_pathinfo($filename, PATHINFO_EXTENSION)))) {
						$message = preg_replace('/' . $images[1][$imgindex] . '=["\']' . preg_quote($url, '/') . '["\']/Ui', $images[1][$imgindex] . '="cid:' . $cid . '"', $message);
					}
				}
			}
		}

		$this->isHTML(true);
		$this->Body = $this->normalizeBreaks($message);
		$this->AltBody = $this->normalizeBreaks($this->html2text($message, $advanced));

		if (empty($this->AltBody)) {
			$this->AltBody = 'To view this email message, open it in a program that understands HTML!' . self::CRLF . self::CRLF;
		}

		return $this->Body;
	}

	public function html2text($html, $advanced = false)
	{
		if (is_callable($advanced)) {
			return call_user_func($advanced, $html);
		}

		return html_entity_decode(trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\\/\\1>/si', '', $html))), ENT_QUOTES, $this->CharSet);
	}

	static public function _mime_types($ext = '')
	{
		$mimes = array('xl' => 'application/excel', 'js' => 'application/javascript', 'hqx' => 'application/mac-binhex40', 'cpt' => 'application/mac-compactpro', 'bin' => 'application/macbinary', 'doc' => 'application/msword', 'word' => 'application/msword', 'class' => 'application/octet-stream', 'dll' => 'application/octet-stream', 'dms' => 'application/octet-stream', 'exe' => 'application/octet-stream', 'lha' => 'application/octet-stream', 'lzh' => 'application/octet-stream', 'psd' => 'application/octet-stream', 'sea' => 'application/octet-stream', 'so' => 'application/octet-stream', 'oda' => 'application/oda', 'pdf' => 'application/pdf', 'ai' => 'application/postscript', 'eps' => 'application/postscript', 'ps' => 'application/postscript', 'smi' => 'application/smil', 'smil' => 'application/smil', 'mif' => 'application/vnd.mif', 'xls' => 'application/vnd.ms-excel', 'ppt' => 'application/vnd.ms-powerpoint', 'wbxml' => 'application/vnd.wap.wbxml', 'wmlc' => 'application/vnd.wap.wmlc', 'dcr' => 'application/x-director', 'dir' => 'application/x-director', 'dxr' => 'application/x-director', 'dvi' => 'application/x-dvi', 'gtar' => 'application/x-gtar', 'php3' => 'application/x-httpd-php', 'php4' => 'application/x-httpd-php', 'php' => 'application/x-httpd-php', 'phtml' => 'application/x-httpd-php', 'phps' => 'application/x-httpd-php-source', 'swf' => 'application/x-shockwave-flash', 'sit' => 'application/x-stuffit', 'tar' => 'application/x-tar', 'tgz' => 'application/x-tar', 'xht' => 'application/xhtml+xml', 'xhtml' => 'application/xhtml+xml', 'zip' => 'application/zip', 'mid' => 'audio/midi', 'midi' => 'audio/midi', 'mp2' => 'audio/mpeg', 'mp3' => 'audio/mpeg', 'mpga' => 'audio/mpeg', 'aif' => 'audio/x-aiff', 'aifc' => 'audio/x-aiff', 'aiff' => 'audio/x-aiff', 'ram' => 'audio/x-pn-realaudio', 'rm' => 'audio/x-pn-realaudio', 'rpm' => 'audio/x-pn-realaudio-plugin', 'ra' => 'audio/x-realaudio', 'wav' => 'audio/x-wav', 'bmp' => 'image/bmp', 'gif' => 'image/gif', 'jpeg' => 'image/jpeg', 'jpe' => 'image/jpeg', 'jpg' => 'image/jpeg', 'png' => 'image/png', 'tiff' => 'image/tiff', 'tif' => 'image/tiff', 'eml' => 'message/rfc822', 'css' => 'text/css', 'html' => 'text/html', 'htm' => 'text/html', 'shtml' => 'text/html', 'log' => 'text/plain', 'text' => 'text/plain', 'txt' => 'text/plain', 'rtx' => 'text/richtext', 'rtf' => 'text/rtf', 'vcf' => 'text/vcard', 'vcard' => 'text/vcard', 'xml' => 'text/xml', 'xsl' => 'text/xml', 'mpeg' => 'video/mpeg', 'mpe' => 'video/mpeg', 'mpg' => 'video/mpeg', 'mov' => 'video/quicktime', 'qt' => 'video/quicktime', 'rv' => 'video/vnd.rn-realvideo', 'avi' => 'video/x-msvideo', 'movie' => 'video/x-sgi-movie');

		if (array_key_exists(strtolower($ext), $mimes)) {
			return $mimes[strtolower($ext)];
		}

		return 'application/octet-stream';
	}

	static public function filenameToType($filename)
	{
		$qpos = strpos($filename, '?');

		if (false !== $qpos) {
			$filename = substr($filename, 0, $qpos);
		}

		$pathinfo = self::mb_pathinfo($filename);
		return self::_mime_types($pathinfo['extension']);
	}

	static public function mb_pathinfo($path, $options = NULL)
	{
		$ret = array('dirname' => '', 'basename' => '', 'extension' => '', 'filename' => '');
		$pathinfo = array();

		if (preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\\.([^\\.\\\\/]+?)|))[\\\\/\\.]*$%im', $path, $pathinfo)) {
			if (array_key_exists(1, $pathinfo)) {
				$ret['dirname'] = $pathinfo[1];
			}

			if (array_key_exists(2, $pathinfo)) {
				$ret['basename'] = $pathinfo[2];
			}

			if (array_key_exists(5, $pathinfo)) {
				$ret['extension'] = $pathinfo[5];
			}

			if (array_key_exists(3, $pathinfo)) {
				$ret['filename'] = $pathinfo[3];
			}
		}

		switch ($options) {
		case PATHINFO_DIRNAME:
		case 'dirname':
			return $ret['dirname'];
		case PATHINFO_BASENAME:
		case 'basename':
			return $ret['basename'];
		case PATHINFO_EXTENSION:
		case 'extension':
			return $ret['extension'];
		case PATHINFO_FILENAME:
		case 'filename':
			return $ret['filename'];
		default:
			return $ret;
		}
	}

	public function set($name, $value = '')
	{
		if (property_exists($this, $name)) {
			$this->$name = $value;
			return true;
		}
		else {
			$this->setError($this->lang('variable_set') . $name);
			return false;
		}
	}

	public function secureHeader($str)
	{
		return trim(str_replace(array("\r", "\n"), '', $str));
	}

	static public function normalizeBreaks($text, $breaktype = "\r\n")
	{
		return preg_replace('/(\\r\\n|\\r|\\n)/ms', $breaktype, $text);
	}

	public function sign($cert_filename, $key_filename, $key_pass)
	{
		$this->sign_cert_file = $cert_filename;
		$this->sign_key_file = $key_filename;
		$this->sign_key_pass = $key_pass;
	}

	public function DKIM_QP($txt)
	{
		$line = '';

		for ($i = 0; $i < strlen($txt); $i++) {
			$ord = ord($txt[$i]);
			if (((33 <= $ord) && ($ord <= 58)) || ($ord == 60) || ((62 <= $ord) && ($ord <= 126))) {
				$line .= $txt[$i];
			}
			else {
				$line .= '=' . sprintf('%02X', $ord);
			}
		}

		return $line;
	}

	public function DKIM_Sign($signHeader)
	{
		if (!defined('PKCS7_TEXT')) {
			if ($this->exceptions) {
				throw new phpmailerException($this->lang('signing') . ' OpenSSL extension missing.');
			}

			return '';
		}

		$privKeyStr = file_get_contents($this->DKIM_private);

		if ($this->DKIM_passphrase != '') {
			$privKey = openssl_pkey_get_private($privKeyStr, $this->DKIM_passphrase);
		}
		else {
			$privKey = $privKeyStr;
		}

		if (openssl_sign($signHeader, $signature, $privKey)) {
			return base64_encode($signature);
		}

		return '';
	}

	public function DKIM_HeaderC($signHeader)
	{
		$signHeader = preg_replace('/\\r\\n\\s+/', ' ', $signHeader);
		$lines = explode("\r\n", $signHeader);

		foreach ($lines as $key => $line) {
			list($heading, $value) = explode(':', $line, 2);
			$heading = strtolower($heading);
			$value = preg_replace('/\\s+/', ' ', $value);
			$lines[$key] = $heading . ':' . trim($value);
		}

		$signHeader = implode("\r\n", $lines);
		return $signHeader;
	}

	public function DKIM_BodyC($body)
	{
		if ($body == '') {
			return "\r\n";
		}

		$body = str_replace("\r\n", "\n", $body);
		$body = str_replace("\n", "\r\n", $body);

		while (substr($body, strlen($body) - 4, 4) == "\r\n\r\n") {
			$body = substr($body, 0, strlen($body) - 2);
		}

		return $body;
	}

	public function DKIM_Add($headers_line, $subject, $body)
	{
		$DKIMsignatureType = 'rsa-sha1';
		$DKIMcanonicalization = 'relaxed/simple';
		$DKIMquery = 'dns/txt';
		$DKIMtime = time();
		$subject_header = 'Subject: ' . $subject;
		$headers = explode($this->LE, $headers_line);
		$from_header = '';
		$to_header = '';
		$current = '';

		foreach ($headers as $header) {
			if (strpos($header, 'From:') === 0) {
				$from_header = $header;
				$current = 'from_header';
			}
			else if (strpos($header, 'To:') === 0) {
				$to_header = $header;
				$current = 'to_header';
			}
			else {
				if ($current && (strpos($header, ' =?') === 0)) {
					$current .= $header;
				}
				else {
					$current = '';
				}
			}
		}

		$from = str_replace('|', '=7C', $this->DKIM_QP($from_header));
		$to = str_replace('|', '=7C', $this->DKIM_QP($to_header));
		$subject = str_replace('|', '=7C', $this->DKIM_QP($subject_header));
		$body = $this->DKIM_BodyC($body);
		$DKIMlen = strlen($body);
		$DKIMb64 = base64_encode(pack('H*', sha1($body)));

		if ('' == $this->DKIM_identity) {
			$ident = '';
		}
		else {
			$ident = ' i=' . $this->DKIM_identity . ';';
		}

		$dkimhdrs = 'DKIM-Signature: v=1; a=' . $DKIMsignatureType . '; q=' . $DKIMquery . '; l=' . $DKIMlen . '; s=' . $this->DKIM_selector . ";\r\n" . '	t=' . $DKIMtime . '; c=' . $DKIMcanonicalization . ";\r\n" . "\th=From:To:Subject;\r\n" . '	d=' . $this->DKIM_domain . ';' . $ident . "\r\n" . '	z=' . $from . "\r\n" . '	|' . $to . "\r\n" . '	|' . $subject . ";\r\n" . '	bh=' . $DKIMb64 . ";\r\n" . '	b=';
		$toSign = $this->DKIM_HeaderC($from_header . "\r\n" . $to_header . "\r\n" . $subject_header . "\r\n" . $dkimhdrs);
		$signed = $this->DKIM_Sign($toSign);
		return $dkimhdrs . $signed . "\r\n";
	}

	public function getToAddresses()
	{
		return $this->to;
	}

	public function getCcAddresses()
	{
		return $this->cc;
	}

	public function getBccAddresses()
	{
		return $this->bcc;
	}

	public function getReplyToAddresses()
	{
		return $this->ReplyTo;
	}

	public function getAllRecipientAddresses()
	{
		return $this->all_recipients;
	}

	protected function doCallback($isSent, $to, $cc, $bcc, $subject, $body, $from)
	{
		if (!empty($this->action_function) && is_callable($this->action_function)) {
			$params = array($isSent, $to, $cc, $bcc, $subject, $body, $from);
			call_user_func_array($this->action_function, $params);
		}
	}
}

class phpmailerException extends Exception
{
	public function errorMessage()
	{
		$errorMsg = '<strong>' . $this->getMessage() . "</strong><br />\n";
		return $errorMsg;
	}
}

?>
