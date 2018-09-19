<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Model;

class LifecycleAction
{
	private $action;
	private $timeSpec;
	private $timeValue;

	public function __construct($action, $timeSpec, $timeValue)
	{
		$this->action = $action;
		$this->timeSpec = $timeSpec;
		$this->timeValue = $timeValue;
	}

	public function getAction()
	{
		return $this->action;
	}

	public function setAction($action)
	{
		$this->action = $action;
	}

	public function getTimeSpec()
	{
		return $this->timeSpec;
	}

	public function setTimeSpec($timeSpec)
	{
		$this->timeSpec = $timeSpec;
	}

	public function getTimeValue()
	{
		return $this->timeValue;
	}

	public function setTimeValue($timeValue)
	{
		$this->timeValue = $timeValue;
	}

	public function appendToXml(&$xmlRule)
	{
		$xmlAction = $xmlRule->addChild($this->action);
		$xmlAction->addChild($this->timeSpec, $this->timeValue);
	}
}


?>
