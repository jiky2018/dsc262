<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Model;

class ObjectInfo
{
	private $key = '';
	private $lastModified = '';
	private $eTag = '';
	private $type = '';
	private $size = 0;
	private $storageClass = '';

	public function __construct($key, $lastModified, $eTag, $type, $size, $storageClass)
	{
		$this->key = $key;
		$this->lastModified = $lastModified;
		$this->eTag = $eTag;
		$this->type = $type;
		$this->size = $size;
		$this->storageClass = $storageClass;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function getLastModified()
	{
		return $this->lastModified;
	}

	public function getETag()
	{
		return $this->eTag;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getSize()
	{
		return $this->size;
	}

	public function getStorageClass()
	{
		return $this->storageClass;
	}
}


?>
