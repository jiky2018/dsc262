<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class ecs_error
{
	public $_message = array();
	public $_template = '';
	public $error_no = 0;

	public function __construct($tpl)
	{
		$this->ecs_error($tpl);
	}

	public function ecs_error($tpl)
	{
		$this->_template = $tpl;
	}

	public function add($msg, $errno = 1)
	{
		if (is_array($msg)) {
			$this->_message = array_merge($this->_message, $msg);
		}
		else {
			$this->_message[] = $msg;
		}

		$this->error_no = $errno;
	}

	public function clean()
	{
		$this->_message = array();
		$this->error_no = 0;
	}

	public function get_all()
	{
		return $this->_message;
	}

	public function last_message()
	{
		return array_slice($this->_message, -1);
	}

	public function show($link = '', $href = '')
	{
		if (0 < $this->error_no) {
			$message = array();
			$link = (empty($link) ? $GLOBALS['_LANG']['back_up_page'] : $link);
			$href = (empty($href) ? 'javascript:history.back();' : $href);
			$message['url_info'][$link] = $href;
			$message['back_url'] = $href;

			foreach ($this->_message as $msg) {
				$message['content'] = '<div>' . htmlspecialchars($msg) . '</div>';
			}

			if (isset($GLOBALS['smarty'])) {
				assign_template();
				$GLOBALS['smarty']->assign('helps', get_shop_help());
				$GLOBALS['smarty']->assign('auto_redirect', true);
				$GLOBALS['smarty']->assign('message', $message);
				$GLOBALS['smarty']->display($this->_template);
			}
			else {
				exit($message['content']);
			}

			exit();
		}
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
