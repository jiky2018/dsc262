<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Model;

class BucketListInfo
{
	/**
     * BucketInfo信息列表
     *
     * @var array
     */
	private $bucketList = array();

	public function __construct(array $bucketList)
	{
		$this->bucketList = $bucketList;
	}

	public function getBucketList()
	{
		return $this->bucketList;
	}
}


?>
