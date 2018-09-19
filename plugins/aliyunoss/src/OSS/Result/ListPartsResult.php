<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Result;

class ListPartsResult extends Result
{
	protected function parseDataFromResponse()
	{
		$content = $this->rawResponse->body;
		$xml = simplexml_load_string($content);
		$bucket = (isset($xml->Bucket) ? strval($xml->Bucket) : '');
		$key = (isset($xml->Key) ? strval($xml->Key) : '');
		$uploadId = (isset($xml->UploadId) ? strval($xml->UploadId) : '');
		$nextPartNumberMarker = (isset($xml->NextPartNumberMarker) ? intval($xml->NextPartNumberMarker) : '');
		$maxParts = (isset($xml->MaxParts) ? intval($xml->MaxParts) : '');
		$isTruncated = (isset($xml->IsTruncated) ? strval($xml->IsTruncated) : '');
		$partList = array();

		if (isset($xml->Part)) {
			foreach ($xml->Part as $part) {
				$partNumber = (isset($part->PartNumber) ? intval($part->PartNumber) : '');
				$lastModified = (isset($part->LastModified) ? strval($part->LastModified) : '');
				$eTag = (isset($part->ETag) ? strval($part->ETag) : '');
				$size = (isset($part->Size) ? intval($part->Size) : '');
				$partList[] = new \OSS\Model\PartInfo($partNumber, $lastModified, $eTag, $size);
			}
		}

		return new \OSS\Model\ListPartsInfo($bucket, $key, $uploadId, $nextPartNumberMarker, $maxParts, $isTruncated, $partList);
	}
}

?>
