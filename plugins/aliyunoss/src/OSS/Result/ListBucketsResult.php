<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS\Result;

class ListBucketsResult extends Result
{
	protected function parseDataFromResponse()
	{
		$bucketList = array();
		$content = $this->rawResponse->body;
		$xml = new \SimpleXMLElement($content);
		if (isset($xml->Buckets) && isset($xml->Buckets->Bucket)) {
			foreach ($xml->Buckets->Bucket as $bucket) {
				$bucketInfo = new \OSS\Model\BucketInfo(strval($bucket->Location), strval($bucket->Name), strval($bucket->CreationDate));
				$bucketList[] = $bucketInfo;
			}
		}

		return new \OSS\Model\BucketListInfo($bucketList);
	}
}

?>
