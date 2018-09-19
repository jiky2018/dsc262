<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Result;

class ListObjectsResult extends Result
{
	protected function parseDataFromResponse()
	{
		$xml = new \SimpleXMLElement($this->rawResponse->body);
		$encodingType = (isset($xml->EncodingType) ? strval($xml->EncodingType) : '');
		$objectList = $this->parseObjectList($xml, $encodingType);
		$prefixList = $this->parsePrefixList($xml, $encodingType);
		$bucketName = (isset($xml->Name) ? strval($xml->Name) : '');
		$prefix = (isset($xml->Prefix) ? strval($xml->Prefix) : '');
		$prefix = \OSS\Core\OssUtil::decodeKey($prefix, $encodingType);
		$marker = (isset($xml->Marker) ? strval($xml->Marker) : '');
		$marker = \OSS\Core\OssUtil::decodeKey($marker, $encodingType);
		$maxKeys = (isset($xml->MaxKeys) ? intval($xml->MaxKeys) : 0);
		$delimiter = (isset($xml->Delimiter) ? strval($xml->Delimiter) : '');
		$delimiter = \OSS\Core\OssUtil::decodeKey($delimiter, $encodingType);
		$isTruncated = (isset($xml->IsTruncated) ? strval($xml->IsTruncated) : '');
		$nextMarker = (isset($xml->NextMarker) ? strval($xml->NextMarker) : '');
		$nextMarker = \OSS\Core\OssUtil::decodeKey($nextMarker, $encodingType);
		return new \OSS\Model\ObjectListInfo($bucketName, $prefix, $marker, $nextMarker, $maxKeys, $delimiter, $isTruncated, $objectList, $prefixList);
	}

	private function parseObjectList($xml, $encodingType)
	{
		$retList = array();

		if (isset($xml->Contents)) {
			foreach ($xml->Contents as $content) {
				$key = (isset($content->Key) ? strval($content->Key) : '');
				$key = \OSS\Core\OssUtil::decodeKey($key, $encodingType);
				$lastModified = (isset($content->LastModified) ? strval($content->LastModified) : '');
				$eTag = (isset($content->ETag) ? strval($content->ETag) : '');
				$type = (isset($content->Type) ? strval($content->Type) : '');
				$size = (isset($content->Size) ? intval($content->Size) : 0);
				$storageClass = (isset($content->StorageClass) ? strval($content->StorageClass) : '');
				$retList[] = new \OSS\Model\ObjectInfo($key, $lastModified, $eTag, $type, $size, $storageClass);
			}
		}

		return $retList;
	}

	private function parsePrefixList($xml, $encodingType)
	{
		$retList = array();

		if (isset($xml->CommonPrefixes)) {
			foreach ($xml->CommonPrefixes as $commonPrefix) {
				$prefix = (isset($commonPrefix->Prefix) ? strval($commonPrefix->Prefix) : '');
				$prefix = \OSS\Core\OssUtil::decodeKey($prefix, $encodingType);
				$retList[] = new \OSS\Model\PrefixInfo($prefix);
			}
		}

		return $retList;
	}
}

?>
