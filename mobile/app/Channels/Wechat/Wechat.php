<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Channels\Wechat;

class Wechat
{
	/**
     * 微信通配置
     * @var array
     */
	protected $config = array('token' => '', 'appid' => '', 'appsecret' => '');
	/**
     * @var objcet 微信对象
     */
	protected $wechat;

	public function __construct($config = array())
	{
		$this->config = array_merge($this->config, $config);
	}

	public function setData($to, $title, $content, $data)
	{
		$openid = $this->get_openid($to);
		$sql = 'SELECT title,content FROM {pre}wechat_template WHERE code = \'' . $title . '\' and status = 1 ';
		$template = $GLOBALS['db']->getRow($sql);
		if ($openid && $template['title']) {
			$content['first'] = !empty($content['first']) ? $content['first'] : array('value' => $template['title'], 'color' => '#173177');
			$content['remark'] = !empty($template['content']) ? array('value' => $template['content'], 'color' => '#FF0000') : $content['remark'];
			$rs['code'] = $title;
			$rs['openid'] = $openid;
			$rs['data'] = serialize($content);
			$rs['url'] = $data['url'];
			$rs['wechat_id'] = $data['wechat_id'];
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('wechat_template_log'), $rs, 'INSERT');
		}

		return $this;
	}

	public function send($to = 0, $title = '')
	{
		$openid = $this->get_openid($to);
		$sql = 'SELECT d.code, d.openid, d.data, d.url, d.wechat_id, t.template_id FROM {pre}wechat_template_log d LEFT JOIN {pre}wechat_template t ON d.code = t.code WHERE d.status = 0 and d.openid = \'' . $openid . '\'  and d.code = \'' . $title . '\' ORDER BY d.id DESC';
		$result = $GLOBALS['db']->getRow($sql);

		if ($result) {
			$data['touser'] = $result['openid'];
			$data['template_id'] = $result['template_id'];
			$data['url'] = $result['url'];
			$data['topcolor'] = '#FF0000';
			$data['data'] = unserialize($result['data']);
			$weObj = new \App\Extensions\Wechat($this->config);
			$rs = $weObj->sendTemplateMessage($data);

			if (empty($rs)) {
				return false;
			}

			$sql = 'UPDATE {pre}wechat_template_log SET msgid = \'' . $rs['msgid'] . '\' WHERE code = \'' . $result['code'] . '\' AND openid = \'' . $result['openid'] . '\' AND wechat_id = \'' . $result['wechat_id'] . '\' ';
			$GLOBALS['db']->query($sql);
			return true;
		}

		return false;
	}

	private function get_openid($to = 0)
	{
		if (isset($_COOKIE['wechat_ru_id'])) {
			$openid = isset($_COOKIE['seller_openid']) ? $_COOKIE['seller_openid'] : $_SESSION['seller_openid'];
		}
		else if ($to) {
			$sql = 'SELECT wu.openid FROM {pre}wechat_user as wu LEFT JOIN {pre}connect_user as cu ON cu.open_id = wu.unionid WHERE cu.user_id = \'' . $to . '\'';
			$openid = $GLOBALS['db']->getOne($sql);
		}

		return $openid;
	}

	public function getError()
	{
		return $this->errorInfo;
	}

	public function __destruct()
	{
		unset($this->wechat);
	}
}


?>
