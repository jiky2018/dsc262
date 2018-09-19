<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Channels\Send;

class EmailDriver implements SendInterface
{
	protected $config = array('smtp_host' => 'smtp.qq.com', 'smtp_port' => '465', 'smtp_ssl' => false, 'smtp_username' => '', 'smtp_password' => '', 'smtp_from_to' => '', 'smtp_from_name' => 'ECTouch');
	protected $mail;

	public function __construct($config = array())
	{
		$this->config = array_merge($this->config, $config);
		$this->mail = new \App\Channels\Email\Email($this->config);
	}

	public function push($to, $title, $content, $data = array())
	{
		return $this->mail->setMail($title, $content)->sendMail($to);
	}

	public function getError()
	{
		return $this->mail->getError();
	}
}

?>
