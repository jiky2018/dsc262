<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Model;

class BucketInfo
{
	/**
     * bucket所在的region
     *
     * @var string
     */
	private $location;
	/**
     * bucket的名称
     *
     * @var string
     */
	private $name;
	/**
     * bucket的创建事件
     *
     * @var string
     */
	private $createDate;

	public function __construct($location, $name, $createDate)
	{
		$this->location = $location;
		$this->name = $name;
		$this->createDate = $createDate;
	}

	public function getLocation()
	{
		return $this->location;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getCreateDate()
	{
		return $this->createDate;
	}
}


?>
