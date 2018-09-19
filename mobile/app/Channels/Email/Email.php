<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Channels\Email;

class Email
{
	/**
     * 邮件类配置
     * @var array
     */
	protected $config = array('smtp_host' => 'smtp.qq.com', 'smtp_port' => '465', 'smtp_ssl' => false, 'smtp_username' => '', 'smtp_password' => '', 'smtp_from_to' => '', 'smtp_from_name' => 'ectouch');
	/**
     * @var objcet 邮件对象
     */
	protected $mail;

	public function __construct($config = array())
	{
		$this->config = array_merge($this->config, $config);
		$this->mail = new \PHPMailer();
		$this->mail->isSMTP();
		$this->mail->Host = $this->config['smtp_host'];
		$this->mail->SMTPAuth = true;
		$this->mail->Username = $this->config['smtp_username'];
		$this->mail->Password = $this->config['smtp_password'];
		$this->mail->Port = $this->config['port'];
		$this->mail->setFrom($this->config['smtp_from_to'], $this->config['smtp_from_name']);
		$this->mail->isHTML(true);
	}

	public function setCc($cc)
	{
		$ccs = explode(',', $cc);

		foreach ($ccs as $cc) {
			if (preg_match('/^([_a-z0-9-]+)(\\.[_a-z0-9-]+)*@([a-z0-9-]+)(\\.[a-z0-9-]+)*(\\.[a-z]{2,4})$/', $cc)) {
				$this->mail->addCC($cc);
			}
		}

		return $this;
	}

	public function setBcc($bcc)
	{
		$bccs = explode(',', $bcc);

		foreach ($bccs as $bcc) {
			if (preg_match('/^([_a-z0-9-]+)(\\.[_a-z0-9-]+)*@([a-z0-9-]+)(\\.[a-z0-9-]+)*(\\.[a-z]{2,4})$/', $bcc)) {
				$this->mail->addBCC($bcc);
			}
		}

		return $this;
	}

	public function addAttachment($file)
	{
		foreach ($file as $attachment) {
			if (empty($attachment['path'])) {
				continue;
			}

			$filename = (isset($attachment['name']) ? $attachment['name'] : substr(strrchr($attachment['path'], '/'), 1));
			$this->mail->addAttachment($attachment['path'], $filename);
		}
	}

	public function setMail($title, $body)
	{
		$this->mail->Subject = $title;
		$this->mail->Body = $body;
		return $this;
	}

	public function sendMail($to)
	{
		$sendTo = explode(',', $to);

		foreach ($sendTo as $add) {
			if (preg_match('/^([_a-z0-9-]+)(\\.[_a-z0-9-]+)*@([a-z0-9-]+)(\\.[a-z0-9-]+)*(\\.[a-z]{2,4})$/', $add)) {
				$this->mail->addAddress($add);
			}
		}

		if (!$this->mail->Send()) {
			$return = false;
		}
		else {
			$return = true;
		}

		return $return;
	}

	public function getError()
	{
		return $this->mail->ErrorInfo;
	}

	public function __destruct()
	{
		$this->mail->SmtpClose();
		$this->mail = null;
	}
}

require_once __DIR__ . '/phpmailer/class.phpmailer.php';
require_once __DIR__ . '/phpmailer/class.smtp.php';

?>
