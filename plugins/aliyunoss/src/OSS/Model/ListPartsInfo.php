<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Model;

class ListPartsInfo
{
	private $bucket = '';
	private $key = '';
	private $uploadId = '';
	private $nextPartNumberMarker = 0;
	private $maxParts = 0;
	private $isTruncated = '';
	private $listPart = array();

	public function __construct($bucket, $key, $uploadId, $nextPartNumberMarker, $maxParts, $isTruncated, array $listPart)
	{
		$this->bucket = $bucket;
		$this->key = $key;
		$this->uploadId = $uploadId;
		$this->nextPartNumberMarker = $nextPartNumberMarker;
		$this->maxParts = $maxParts;
		$this->isTruncated = $isTruncated;
		$this->listPart = $listPart;
	}

	public function getBucket()
	{
		return $this->bucket;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function getUploadId()
	{
		return $this->uploadId;
	}

	public function getNextPartNumberMarker()
	{
		return $this->nextPartNumberMarker;
	}

	public function getMaxParts()
	{
		return $this->maxParts;
	}

	public function getIsTruncated()
	{
		return $this->isTruncated;
	}

	public function getListPart()
	{
		return $this->listPart;
	}
}


?>
