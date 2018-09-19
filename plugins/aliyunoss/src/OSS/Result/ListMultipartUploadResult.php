<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Result;

class ListMultipartUploadResult extends Result
{
	protected function parseDataFromResponse()
	{
		$content = $this->rawResponse->body;
		$xml = simplexml_load_string($content);
		$encodingType = (isset($xml->EncodingType) ? strval($xml->EncodingType) : '');
		$bucket = (isset($xml->Bucket) ? strval($xml->Bucket) : '');
		$keyMarker = (isset($xml->KeyMarker) ? strval($xml->KeyMarker) : '');
		$keyMarker = \OSS\Core\OssUtil::decodeKey($keyMarker, $encodingType);
		$uploadIdMarker = (isset($xml->UploadIdMarker) ? strval($xml->UploadIdMarker) : '');
		$nextKeyMarker = (isset($xml->NextKeyMarker) ? strval($xml->NextKeyMarker) : '');
		$nextKeyMarker = \OSS\Core\OssUtil::decodeKey($nextKeyMarker, $encodingType);
		$nextUploadIdMarker = (isset($xml->NextUploadIdMarker) ? strval($xml->NextUploadIdMarker) : '');
		$delimiter = (isset($xml->Delimiter) ? strval($xml->Delimiter) : '');
		$delimiter = \OSS\Core\OssUtil::decodeKey($delimiter, $encodingType);
		$prefix = (isset($xml->Prefix) ? strval($xml->Prefix) : '');
		$prefix = \OSS\Core\OssUtil::decodeKey($prefix, $encodingType);
		$maxUploads = (isset($xml->MaxUploads) ? intval($xml->MaxUploads) : 0);
		$isTruncated = (isset($xml->IsTruncated) ? strval($xml->IsTruncated) : '');
		$listUpload = array();

		if (isset($xml->Upload)) {
			foreach ($xml->Upload as $upload) {
				$key = (isset($upload->Key) ? strval($upload->Key) : '');
				$key = \OSS\Core\OssUtil::decodeKey($key, $encodingType);
				$uploadId = (isset($upload->UploadId) ? strval($upload->UploadId) : '');
				$initiated = (isset($upload->Initiated) ? strval($upload->Initiated) : '');
				$listUpload[] = new \OSS\Model\UploadInfo($key, $uploadId, $initiated);
			}
		}

		return new \OSS\Model\ListMultipartUploadInfo($bucket, $keyMarker, $uploadIdMarker, $nextKeyMarker, $nextUploadIdMarker, $delimiter, $prefix, $maxUploads, $isTruncated, $listUpload);
	}
}

?>
