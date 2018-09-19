<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Model;

class ListMultipartUploadInfo
{
	private $bucket = '';
	private $keyMarker = '';
	private $uploadIdMarker = '';
	private $nextKeyMarker = '';
	private $nextUploadIdMarker = '';
	private $delimiter = '';
	private $prefix = '';
	private $maxUploads = 0;
	private $isTruncated = 'false';
	private $uploads = array();

	public function __construct($bucket, $keyMarker, $uploadIdMarker, $nextKeyMarker, $nextUploadIdMarker, $delimiter, $prefix, $maxUploads, $isTruncated, array $uploads)
	{
		$this->bucket = $bucket;
		$this->keyMarker = $keyMarker;
		$this->uploadIdMarker = $uploadIdMarker;
		$this->nextKeyMarker = $nextKeyMarker;
		$this->nextUploadIdMarker = $nextUploadIdMarker;
		$this->delimiter = $delimiter;
		$this->prefix = $prefix;
		$this->maxUploads = $maxUploads;
		$this->isTruncated = $isTruncated;
		$this->uploads = $uploads;
	}

	public function getBucket()
	{
		return $this->bucket;
	}

	public function getKeyMarker()
	{
		return $this->keyMarker;
	}

	public function getUploadIdMarker()
	{
		return $this->uploadIdMarker;
	}

	public function getNextKeyMarker()
	{
		return $this->nextKeyMarker;
	}

	public function getNextUploadIdMarker()
	{
		return $this->nextUploadIdMarker;
	}

	public function getDelimiter()
	{
		return $this->delimiter;
	}

	public function getPrefix()
	{
		return $this->prefix;
	}

	public function getMaxUploads()
	{
		return $this->maxUploads;
	}

	public function getIsTruncated()
	{
		return $this->isTruncated;
	}

	public function getUploads()
	{
		return $this->uploads;
	}
}


?>
