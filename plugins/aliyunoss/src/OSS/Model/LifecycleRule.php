<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Model;

class LifecycleRule
{
	const LIFECYCLE_STATUS_ENABLED = 'Enabled';
	const LIFECYCLE_STATUS_DISABLED = 'Disabled';

	private $id;
	private $prefix;
	private $status;
	private $actions = array();

	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function getPrefix()
	{
		return $this->prefix;
	}

	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setStatus($status)
	{
		$this->status = $status;
	}

	public function getActions()
	{
		return $this->actions;
	}

	public function setActions($actions)
	{
		$this->actions = $actions;
	}

	public function __construct($id, $prefix, $status, $actions)
	{
		$this->id = $id;
		$this->prefix = $prefix;
		$this->status = $status;
		$this->actions = $actions;
	}

	public function appendToXml(&$xmlRule)
	{
		$xmlRule->addChild('ID', $this->id);
		$xmlRule->addChild('Prefix', $this->prefix);
		$xmlRule->addChild('Status', $this->status);

		foreach ($this->actions as $action) {
			$action->appendToXml($xmlRule);
		}
	}
}


?>
