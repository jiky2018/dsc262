<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace OSS;

class OssClient
{
	const OSS_LIFECYCLE_EXPIRATION = 'Expiration';
	const OSS_LIFECYCLE_TIMING_DAYS = 'Days';
	const OSS_LIFECYCLE_TIMING_DATE = 'Date';
	const OSS_BUCKET = 'bucket';
	const OSS_OBJECT = 'object';
	const OSS_HEADERS = \OSS\Core\OssUtil::OSS_HEADERS;
	const OSS_METHOD = 'method';
	const OSS_QUERY = 'query';
	const OSS_BASENAME = 'basename';
	const OSS_MAX_KEYS = 'max-keys';
	const OSS_UPLOAD_ID = 'uploadId';
	const OSS_PART_NUM = 'partNumber';
	const OSS_CNAME_COMP = 'comp';
	const OSS_MAX_KEYS_VALUE = 100;
	const OSS_MAX_OBJECT_GROUP_VALUE = \OSS\Core\OssUtil::OSS_MAX_OBJECT_GROUP_VALUE;
	const OSS_MAX_PART_SIZE = \OSS\Core\OssUtil::OSS_MAX_PART_SIZE;
	const OSS_MID_PART_SIZE = \OSS\Core\OssUtil::OSS_MID_PART_SIZE;
	const OSS_MIN_PART_SIZE = \OSS\Core\OssUtil::OSS_MIN_PART_SIZE;
	const OSS_FILE_SLICE_SIZE = 8192;
	const OSS_PREFIX = 'prefix';
	const OSS_DELIMITER = 'delimiter';
	const OSS_MARKER = 'marker';
	const OSS_CONTENT_MD5 = 'Content-Md5';
	const OSS_SELF_CONTENT_MD5 = 'x-oss-meta-md5';
	const OSS_CONTENT_TYPE = 'Content-Type';
	const OSS_CONTENT_LENGTH = 'Content-Length';
	const OSS_IF_MODIFIED_SINCE = 'If-Modified-Since';
	const OSS_IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
	const OSS_IF_MATCH = 'If-Match';
	const OSS_IF_NONE_MATCH = 'If-None-Match';
	const OSS_CACHE_CONTROL = 'Cache-Control';
	const OSS_EXPIRES = 'Expires';
	const OSS_PREAUTH = 'preauth';
	const OSS_CONTENT_COING = 'Content-Coding';
	const OSS_CONTENT_DISPOSTION = 'Content-Disposition';
	const OSS_RANGE = 'range';
	const OSS_ETAG = 'etag';
	const OSS_LAST_MODIFIED = 'lastmodified';
	const OS_CONTENT_RANGE = 'Content-Range';
	const OSS_CONTENT = \OSS\Core\OssUtil::OSS_CONTENT;
	const OSS_BODY = 'body';
	const OSS_LENGTH = \OSS\Core\OssUtil::OSS_LENGTH;
	const OSS_HOST = 'Host';
	const OSS_DATE = 'Date';
	const OSS_AUTHORIZATION = 'Authorization';
	const OSS_FILE_DOWNLOAD = 'fileDownload';
	const OSS_FILE_UPLOAD = 'fileUpload';
	const OSS_PART_SIZE = 'partSize';
	const OSS_SEEK_TO = 'seekTo';
	const OSS_SIZE = 'size';
	const OSS_QUERY_STRING = 'query_string';
	const OSS_SUB_RESOURCE = 'sub_resource';
	const OSS_DEFAULT_PREFIX = 'x-oss-';
	const OSS_CHECK_MD5 = 'checkmd5';
	const DEFAULT_CONTENT_TYPE = 'application/octet-stream';
	const OSS_URL_ACCESS_KEY_ID = 'OSSAccessKeyId';
	const OSS_URL_EXPIRES = 'Expires';
	const OSS_URL_SIGNATURE = 'Signature';
	const OSS_HTTP_GET = 'GET';
	const OSS_HTTP_PUT = 'PUT';
	const OSS_HTTP_HEAD = 'HEAD';
	const OSS_HTTP_POST = 'POST';
	const OSS_HTTP_DELETE = 'DELETE';
	const OSS_HTTP_OPTIONS = 'OPTIONS';
	const OSS_ACL = 'x-oss-acl';
	const OSS_OBJECT_ACL = 'x-oss-object-acl';
	const OSS_OBJECT_GROUP = 'x-oss-file-group';
	const OSS_MULTI_PART = 'uploads';
	const OSS_MULTI_DELETE = 'delete';
	const OSS_OBJECT_COPY_SOURCE = 'x-oss-copy-source';
	const OSS_OBJECT_COPY_SOURCE_RANGE = 'x-oss-copy-source-range';
	const OSS_SECURITY_TOKEN = 'x-oss-security-token';
	const OSS_ACL_TYPE_PRIVATE = 'private';
	const OSS_ACL_TYPE_PUBLIC_READ = 'public-read';
	const OSS_ACL_TYPE_PUBLIC_READ_WRITE = 'public-read-write';
	const OSS_ENCODING_TYPE = 'encoding-type';
	const OSS_ENCODING_TYPE_URL = 'url';
	const OSS_HOST_TYPE_NORMAL = 'normal';
	const OSS_HOST_TYPE_IP = 'ip';
	const OSS_HOST_TYPE_SPECIAL = 'special';
	const OSS_HOST_TYPE_CNAME = 'cname';
	const OSS_NAME = 'aliyun-sdk-php';
	const OSS_VERSION = '2.0.5';
	const OSS_BUILD = '20160126';
	const OSS_AUTHOR = '';
	const OSS_OPTIONS_ORIGIN = 'Origin';
	const OSS_OPTIONS_REQUEST_METHOD = 'Access-Control-Request-Method';
	const OSS_OPTIONS_REQUEST_HEADERS = 'Access-Control-Request-Headers';

	static public $OSS_ACL_TYPES = array(self::OSS_ACL_TYPE_PRIVATE, self::OSS_ACL_TYPE_PUBLIC_READ, self::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
	private $useSSL = false;
	private $maxRetries = 3;
	private $redirects = 0;
	private $hostType = self::OSS_HOST_TYPE_NORMAL;
	private $requestUrl;
	private $accessKeyId;
	private $accessKeySecret;
	private $hostname;
	private $securityToken;
	private $enableStsInUrl = false;
	private $timeout = 0;
	private $connectTimeout = 0;

	public function __construct($accessKeyId, $accessKeySecret, $endpoint, $isCName = false, $securityToken = NULL)
	{
		if (empty($accessKeyId)) {
			throw new Core\OssException('access key id is empty');
		}

		if (empty($accessKeySecret)) {
			throw new Core\OssException('access key secret is empty');
		}

		if (empty($endpoint)) {
			throw new Core\OssException('endpoint is empty');
		}

		$this->hostname = $this->checkEndpoint($endpoint, $isCName);
		$this->accessKeyId = $accessKeyId;
		$this->accessKeySecret = $accessKeySecret;
		$this->securityToken = $securityToken;
		self::checkEnv();
	}

	public function listBuckets($options = NULL)
	{
		if ($this->hostType === self::OSS_HOST_TYPE_CNAME) {
			throw new Core\OssException('operation is not permitted with CName host');
		}

		$this->precheckOptions($options);
		$options[self::OSS_BUCKET] = '';
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$response = $this->auth($options);
		$result = new Result\ListBucketsResult($response);
		return $result->getData();
	}

	public function createBucket($bucket, $acl = self::OSS_ACL_TYPE_PRIVATE, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_HEADERS] = array(self::OSS_ACL => $acl);
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function deleteBucket($bucket, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
		$options[self::OSS_OBJECT] = '/';
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function doesBucketExist($bucket)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'acl';
		$response = $this->auth($options);
		$result = new Result\ExistResult($response);
		return $result->getData();
	}

	public function getBucketAcl($bucket, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'acl';
		$response = $this->auth($options);
		$result = new Result\AclResult($response);
		return $result->getData();
	}

	public function putBucketAcl($bucket, $acl, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_HEADERS] = array(self::OSS_ACL => $acl);
		$options[self::OSS_SUB_RESOURCE] = 'acl';
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function getObjectAcl($bucket, $object)
	{
		$options = array();
		$this->precheckCommon($bucket, $object, $options, true);
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_SUB_RESOURCE] = 'acl';
		$response = $this->auth($options);
		$result = new Result\AclResult($response);
		return $result->getData();
	}

	public function putObjectAcl($bucket, $object, $acl)
	{
		$this->precheckCommon($bucket, $object, $options, true);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_HEADERS] = array(self::OSS_OBJECT_ACL => $acl);
		$options[self::OSS_SUB_RESOURCE] = 'acl';
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function getBucketLogging($bucket, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'logging';
		$response = $this->auth($options);
		$result = new Result\GetLoggingResult($response);
		return $result->getData();
	}

	public function putBucketLogging($bucket, $targetBucket, $targetPrefix, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$this->precheckBucket($targetBucket, 'targetbucket is not allowed empty');
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'logging';
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';
		$loggingConfig = new Model\LoggingConfig($targetBucket, $targetPrefix);
		$options[self::OSS_CONTENT] = $loggingConfig->serializeToXml();
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function deleteBucketLogging($bucket, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'logging';
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function putBucketWebsite($bucket, $websiteConfig, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'website';
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';
		$options[self::OSS_CONTENT] = $websiteConfig->serializeToXml();
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function getBucketWebsite($bucket, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'website';
		$response = $this->auth($options);
		$result = new Result\GetWebsiteResult($response);
		return $result->getData();
	}

	public function deleteBucketWebsite($bucket, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'website';
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function putBucketCors($bucket, $corsConfig, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'cors';
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';
		$options[self::OSS_CONTENT] = $corsConfig->serializeToXml();
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function getBucketCors($bucket, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'cors';
		$response = $this->auth($options);
		$result = new Result\GetCorsResult($response, 'getBucketCors');
		return $result->getData();
	}

	public function deleteBucketCors($bucket, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'cors';
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function addBucketCname($bucket, $cname, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_POST;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'cname';
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';
		$cnameConfig = new Model\CnameConfig();
		$cnameConfig->addCname($cname);
		$options[self::OSS_CONTENT] = $cnameConfig->serializeToXml();
		$options[self::OSS_CNAME_COMP] = 'add';
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function getBucketCname($bucket, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'cname';
		$response = $this->auth($options);
		$result = new Result\GetCnameResult($response);
		return $result->getData();
	}

	public function deleteBucketCname($bucket, $cname, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_POST;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'cname';
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';
		$cnameConfig = new Model\CnameConfig();
		$cnameConfig->addCname($cname);
		$options[self::OSS_CONTENT] = $cnameConfig->serializeToXml();
		$options[self::OSS_CNAME_COMP] = 'delete';
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function optionsObject($bucket, $object, $origin, $request_method, $request_headers, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_OPTIONS;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_HEADERS] = array(self::OSS_OPTIONS_ORIGIN => $origin, self::OSS_OPTIONS_REQUEST_HEADERS => $request_headers, self::OSS_OPTIONS_REQUEST_METHOD => $request_method);
		$response = $this->auth($options);
		$result = new Result\HeaderResult($response);
		return $result->getData();
	}

	public function putBucketLifecycle($bucket, $lifecycleConfig, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'lifecycle';
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';
		$options[self::OSS_CONTENT] = $lifecycleConfig->serializeToXml();
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function getBucketLifecycle($bucket, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'lifecycle';
		$response = $this->auth($options);
		$result = new Result\GetLifecycleResult($response);
		return $result->getData();
	}

	public function deleteBucketLifecycle($bucket, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'lifecycle';
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function putBucketReferer($bucket, $refererConfig, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'referer';
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';
		$options[self::OSS_CONTENT] = $refererConfig->serializeToXml();
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function getBucketReferer($bucket, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'referer';
		$response = $this->auth($options);
		$result = new Result\GetRefererResult($response);
		return $result->getData();
	}

	public function listObjects($bucket, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_HEADERS] = array(self::OSS_DELIMITER => isset($options[self::OSS_DELIMITER]) ? $options[self::OSS_DELIMITER] : '/', self::OSS_PREFIX => isset($options[self::OSS_PREFIX]) ? $options[self::OSS_PREFIX] : '', self::OSS_MAX_KEYS => isset($options[self::OSS_MAX_KEYS]) ? $options[self::OSS_MAX_KEYS] : self::OSS_MAX_KEYS_VALUE, self::OSS_MARKER => isset($options[self::OSS_MARKER]) ? $options[self::OSS_MARKER] : '');
		$query = (isset($options[self::OSS_QUERY_STRING]) ? $options[self::OSS_QUERY_STRING] : array());
		$options[self::OSS_QUERY_STRING] = array_merge($query, array(self::OSS_ENCODING_TYPE => self::OSS_ENCODING_TYPE_URL));
		$response = $this->auth($options);
		$result = new Result\ListObjectsResult($response);
		return $result->getData();
	}

	public function createObjectDir($bucket, $object, $options = NULL)
	{
		$this->precheckCommon($bucket, $object, $options);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = $object . '/';
		$options[self::OSS_CONTENT_LENGTH] = array(self::OSS_CONTENT_LENGTH => 0);
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function putObject($bucket, $object, $content, $options = NULL)
	{
		$this->precheckCommon($bucket, $object, $options);
		Core\OssUtil::validateContent($content);
		$options[self::OSS_CONTENT] = $content;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = $object;

		if (!isset($options[self::OSS_LENGTH])) {
			$options[self::OSS_CONTENT_LENGTH] = strlen($options[self::OSS_CONTENT]);
		}
		else {
			$options[self::OSS_CONTENT_LENGTH] = $options[self::OSS_LENGTH];
		}

		if (!isset($options[self::OSS_CONTENT_TYPE])) {
			$options[self::OSS_CONTENT_TYPE] = $this->getMimeType($object);
		}

		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function uploadFile($bucket, $object, $file, $options = NULL)
	{
		$this->precheckCommon($bucket, $object, $options);
		Core\OssUtil::throwOssExceptionWithMessageIfEmpty($file, 'file path is invalid');
		$file = Core\OssUtil::encodePath($file);

		if (!file_exists($file)) {
			throw new Core\OssException($file . ' file does not exist');
		}

		$options[self::OSS_FILE_UPLOAD] = $file;
		$file_size = filesize($options[self::OSS_FILE_UPLOAD]);
		$is_check_md5 = $this->isCheckMD5($options);

		if ($is_check_md5) {
			$content_md5 = base64_encode(md5_file($options[self::OSS_FILE_UPLOAD], true));
			$options[self::OSS_CONTENT_MD5] = $content_md5;
		}

		if (!isset($options[self::OSS_CONTENT_TYPE])) {
			$options[self::OSS_CONTENT_TYPE] = $this->getMimeType($object, $file);
		}

		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_CONTENT_LENGTH] = $file_size;
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		$arr = array('is_ok' => $result->isOk(), 'data' => $result->getData());
		return $arr;
	}

	public function copyObject($fromBucket, $fromObject, $toBucket, $toObject, $options = NULL)
	{
		$this->precheckCommon($fromBucket, $fromObject, $options);
		$this->precheckCommon($toBucket, $toObject, $options);
		$options[self::OSS_BUCKET] = $toBucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = $toObject;

		if (isset($options[self::OSS_HEADERS])) {
			$options[self::OSS_HEADERS][self::OSS_OBJECT_COPY_SOURCE] = '/' . $fromBucket . '/' . $fromObject;
		}
		else {
			$options[self::OSS_HEADERS] = array(self::OSS_OBJECT_COPY_SOURCE => '/' . $fromBucket . '/' . $fromObject);
		}

		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function getObjectMeta($bucket, $object, $options = NULL)
	{
		$this->precheckCommon($bucket, $object, $options);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_HEAD;
		$options[self::OSS_OBJECT] = $object;
		$response = $this->auth($options);
		$result = new Result\HeaderResult($response);
		return $result->getData();
	}

	public function deleteObject($bucket, $object, $options = NULL)
	{
		$this->precheckCommon($bucket, $object, $options);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
		$options[self::OSS_OBJECT] = $object;
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function deleteObjects($bucket, $objects, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		if (!is_array($objects) || !$objects) {
			throw new Core\OssException('objects must be array');
		}

		$options[self::OSS_METHOD] = self::OSS_HTTP_POST;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'delete';
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';
		$quiet = 'false';

		if (isset($options['quiet'])) {
			if (is_bool($options['quiet'])) {
				$quiet = ($options['quiet'] ? 'true' : 'false');
			}
			else if (is_string($options['quiet'])) {
				$quiet = ($options['quiet'] === 'true' ? 'true' : 'false');
			}
		}

		$xmlBody = Core\OssUtil::createDeleteObjectsXmlBody($objects, $quiet);
		$options[self::OSS_CONTENT] = $xmlBody;
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function getObject($bucket, $object, $options = NULL)
	{
		$this->precheckCommon($bucket, $object, $options);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = $object;

		if (isset($options[self::OSS_LAST_MODIFIED])) {
			$options[self::OSS_HEADERS][self::OSS_IF_MODIFIED_SINCE] = $options[self::OSS_LAST_MODIFIED];
			unset($options[self::OSS_LAST_MODIFIED]);
		}

		if (isset($options[self::OSS_ETAG])) {
			$options[self::OSS_HEADERS][self::OSS_IF_NONE_MATCH] = $options[self::OSS_ETAG];
			unset($options[self::OSS_ETAG]);
		}

		if (isset($options[self::OSS_RANGE])) {
			$range = $options[self::OSS_RANGE];
			$options[self::OSS_HEADERS][self::OSS_RANGE] = 'bytes=' . $range;
			unset($options[self::OSS_RANGE]);
		}

		$response = $this->auth($options);
		$result = new Result\BodyResult($response);
		return $result->getData();
	}

	public function doesObjectExist($bucket, $object, $options = NULL)
	{
		$this->precheckCommon($bucket, $object, $options);
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_HEAD;
		$options[self::OSS_OBJECT] = $object;
		$response = $this->auth($options);
		$result = new Result\ExistResult($response);
		return $result->getData();
	}

	private function computePartSize($partSize)
	{
		$partSize = (int) $partSize;

		if ($partSize <= self::OSS_MIN_PART_SIZE) {
			$partSize = self::OSS_MIN_PART_SIZE;
		}
		else if (self::OSS_MAX_PART_SIZE < $partSize) {
			$partSize = self::OSS_MAX_PART_SIZE;
		}

		return $partSize;
	}

	public function generateMultiuploadParts($file_size, $partSize = 5242880)
	{
		$i = 0;
		$size_count = $file_size;
		$values = array();
		$partSize = $this->computePartSize($partSize);

		while (0 < $size_count) {
			$size_count -= $partSize;
			$values[] = array(self::OSS_SEEK_TO => $partSize * $i, self::OSS_LENGTH => 0 < $size_count ? $partSize : $size_count + $partSize);
			$i++;
		}

		return $values;
	}

	public function initiateMultipartUpload($bucket, $object, $options = NULL)
	{
		$this->precheckCommon($bucket, $object, $options);
		$options[self::OSS_METHOD] = self::OSS_HTTP_POST;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_SUB_RESOURCE] = 'uploads';
		$options[self::OSS_CONTENT] = '';

		if (!isset($options[self::OSS_CONTENT_TYPE])) {
			$options[self::OSS_CONTENT_TYPE] = $this->getMimeType($object);
		}

		if (!isset($options[self::OSS_HEADERS])) {
			$options[self::OSS_HEADERS] = array();
		}

		$response = $this->auth($options);
		$result = new Result\InitiateMultipartUploadResult($response);
		return $result->getData();
	}

	public function uploadPart($bucket, $object, $uploadId, $options = NULL)
	{
		$this->precheckCommon($bucket, $object, $options);
		$this->precheckParam($options, self::OSS_FILE_UPLOAD, 'uploadPart');
		$this->precheckParam($options, self::OSS_PART_NUM, 'uploadPart');
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_UPLOAD_ID] = $uploadId;

		if (isset($options[self::OSS_LENGTH])) {
			$options[self::OSS_CONTENT_LENGTH] = $options[self::OSS_LENGTH];
		}

		$response = $this->auth($options);
		$result = new Result\UploadPartResult($response);
		return $result->getData();
	}

	public function listParts($bucket, $object, $uploadId, $options = NULL)
	{
		$this->precheckCommon($bucket, $object, $options);
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_UPLOAD_ID] = $uploadId;
		$options[self::OSS_QUERY_STRING] = array();

		foreach (array('max-parts', 'part-number-marker') as $param) {
			if (isset($options[$param])) {
				$options[self::OSS_QUERY_STRING][$param] = $options[$param];
				unset($options[$param]);
			}
		}

		$response = $this->auth($options);
		$result = new Result\ListPartsResult($response);
		return $result->getData();
	}

	public function abortMultipartUpload($bucket, $object, $uploadId, $options = NULL)
	{
		$this->precheckCommon($bucket, $object, $options);
		$options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_UPLOAD_ID] = $uploadId;
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function completeMultipartUpload($bucket, $object, $uploadId, $listParts, $options = NULL)
	{
		$this->precheckCommon($bucket, $object, $options);
		$options[self::OSS_METHOD] = self::OSS_HTTP_POST;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_UPLOAD_ID] = $uploadId;
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';

		if (!is_array($listParts)) {
			throw new Core\OssException('listParts must be array type');
		}

		$options[self::OSS_CONTENT] = Core\OssUtil::createCompleteMultipartUploadXmlBody($listParts);
		$response = $this->auth($options);
		$result = new Result\PutSetDeleteResult($response);
		return $result->getData();
	}

	public function listMultipartUploads($bucket, $options = NULL)
	{
		$this->precheckCommon($bucket, NULL, $options, false);
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'uploads';

		foreach (array('delimiter', 'key-marker', 'max-uploads', 'prefix', 'upload-id-marker') as $param) {
			if (isset($options[$param])) {
				$options[self::OSS_QUERY_STRING][$param] = $options[$param];
				unset($options[$param]);
			}
		}

		$query = (isset($options[self::OSS_QUERY_STRING]) ? $options[self::OSS_QUERY_STRING] : array());
		$options[self::OSS_QUERY_STRING] = array_merge($query, array(self::OSS_ENCODING_TYPE => self::OSS_ENCODING_TYPE_URL));
		$response = $this->auth($options);
		$result = new Result\ListMultipartUploadResult($response);
		return $result->getData();
	}

	public function uploadPartCopy($fromBucket, $fromObject, $toBucket, $toObject, $partNumber, $uploadId, $options = NULL)
	{
		$this->precheckCommon($fromBucket, $fromObject, $options);
		$this->precheckCommon($toBucket, $toObject, $options);
		$start_range = '0';

		if (isset($options['start'])) {
			$start_range = $options['start'];
		}

		$end_range = '';

		if (isset($options['end'])) {
			$end_range = $options['end'];
		}

		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_BUCKET] = $toBucket;
		$options[self::OSS_OBJECT] = $toObject;
		$options[self::OSS_PART_NUM] = $partNumber;
		$options[self::OSS_UPLOAD_ID] = $uploadId;

		if (!isset($options[self::OSS_HEADERS])) {
			$options[self::OSS_HEADERS] = array();
		}

		$options[self::OSS_HEADERS][self::OSS_OBJECT_COPY_SOURCE] = '/' . $fromBucket . '/' . $fromObject;
		$options[self::OSS_HEADERS][self::OSS_OBJECT_COPY_SOURCE_RANGE] = 'bytes=' . $start_range . '-' . $end_range;
		$response = $this->auth($options);
		$result = new Result\UploadPartResult($response);
		return $result->getData();
	}

	public function multiuploadFile($bucket, $object, $file, $options = NULL)
	{
		$this->precheckCommon($bucket, $object, $options);

		if (isset($options[self::OSS_LENGTH])) {
			$options[self::OSS_CONTENT_LENGTH] = $options[self::OSS_LENGTH];
			unset($options[self::OSS_LENGTH]);
		}

		if (empty($file)) {
			throw new Core\OssException('parameter invalid, file is empty');
		}

		$uploadFile = Core\OssUtil::encodePath($file);

		if (!isset($options[self::OSS_CONTENT_TYPE])) {
			$options[self::OSS_CONTENT_TYPE] = $this->getMimeType($object, $uploadFile);
		}

		$upload_position = (isset($options[self::OSS_SEEK_TO]) ? (int) $options[self::OSS_SEEK_TO] : 0);

		if (isset($options[self::OSS_CONTENT_LENGTH])) {
			$upload_file_size = (int) $options[self::OSS_CONTENT_LENGTH];
		}
		else {
			$upload_file_size = filesize($uploadFile);

			if ($upload_file_size !== false) {
				$upload_file_size -= $upload_position;
			}
		}

		if (($upload_position === false) || !isset($upload_file_size) || ($upload_file_size === false) || ($upload_file_size < 0)) {
			throw new Core\OssException('The size of `fileUpload` cannot be determined in ' . 'multiuploadFile' . '().');
		}

		if (isset($options[self::OSS_PART_SIZE])) {
			$options[self::OSS_PART_SIZE] = $this->computePartSize($options[self::OSS_PART_SIZE]);
		}
		else {
			$options[self::OSS_PART_SIZE] = self::OSS_MID_PART_SIZE;
		}

		$is_check_md5 = $this->isCheckMD5($options);
		if (($upload_file_size < $options[self::OSS_PART_SIZE]) && !isset($options[self::OSS_UPLOAD_ID])) {
			return $this->uploadFile($bucket, $object, $uploadFile, $options);
		}

		if (isset($options[self::OSS_UPLOAD_ID])) {
			$uploadId = $options[self::OSS_UPLOAD_ID];
		}
		else {
			$uploadId = $this->initiateMultipartUpload($bucket, $object, $options);
		}

		$pieces = $this->generateMultiuploadParts($upload_file_size, (int) $options[self::OSS_PART_SIZE]);
		$response_upload_part = array();

		foreach ($pieces as $i => $piece) {
			$from_pos = $upload_position + (int) $piece[self::OSS_SEEK_TO];
			$to_pos = ((int) $piece[self::OSS_LENGTH] + $from_pos) - 1;
			$up_options = array(self::OSS_FILE_UPLOAD => $uploadFile, self::OSS_PART_NUM => $i + 1, self::OSS_SEEK_TO => $from_pos, self::OSS_LENGTH => ($to_pos - $from_pos) + 1, self::OSS_CHECK_MD5 => $is_check_md5);

			if ($is_check_md5) {
				$content_md5 = Core\OssUtil::getMd5SumForFile($uploadFile, $from_pos, $to_pos);
				$up_options[self::OSS_CONTENT_MD5] = $content_md5;
			}

			$response_upload_part[] = $this->uploadPart($bucket, $object, $uploadId, $up_options);
		}

		$uploadParts = array();

		foreach ($response_upload_part as $i => $etag) {
			$uploadParts[] = array('PartNumber' => $i + 1, 'ETag' => $etag);
		}

		return $this->completeMultipartUpload($bucket, $object, $uploadId, $uploadParts);
	}

	public function uploadDir($bucket, $prefix, $localDirectory, $exclude = '.|..|.svn|.git', $recursive = false, $checkMd5 = true)
	{
		$retArray = array(
			'succeededList' => array(),
			'failedList'    => array()
			);

		if (empty($bucket)) {
			throw new Core\OssException('parameter error, bucket is empty');
		}

		if (!is_string($prefix)) {
			throw new Core\OssException('parameter error, prefix is not string');
		}

		if (empty($localDirectory)) {
			throw new Core\OssException('parameter error, localDirectory is empty');
		}

		$directory = $localDirectory;
		$directory = Core\OssUtil::encodePath($directory);

		if (!is_dir($directory)) {
			throw new Core\OssException('parameter error: ' . $directory . ' is not a directory, please check it');
		}

		$file_list_array = Core\OssUtil::readDir($directory, $exclude, $recursive);

		if (!$file_list_array) {
			throw new Core\OssException($directory . ' is empty...');
		}

		foreach ($file_list_array as $k => $item) {
			if (is_dir($item['path'])) {
				continue;
			}

			$options = array(self::OSS_PART_SIZE => self::OSS_MIN_PART_SIZE, self::OSS_CHECK_MD5 => $checkMd5);
			$realObject = (!empty($prefix) ? $prefix . '/' : '') . $item['file'];

			try {
				$this->multiuploadFile($bucket, $realObject, $item['path'], $options);
				$retArray['succeededList'][] = $realObject;
			}
			catch (Core\OssException $e) {
				$retArray['failedList'][$realObject] = $e->getMessage();
			}
		}

		return $retArray;
	}

	public function signUrl($bucket, $object, $timeout = 60, $method = self::OSS_HTTP_GET, $options = NULL)
	{
		$this->precheckCommon($bucket, $object, $options);
		if ((self::OSS_HTTP_GET !== $method) && (self::OSS_HTTP_PUT !== $method)) {
			throw new Core\OssException('method is invalid');
		}

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_METHOD] = $method;

		if (!isset($options[self::OSS_CONTENT_TYPE])) {
			$options[self::OSS_CONTENT_TYPE] = '';
		}

		$timeout = time() + $timeout;
		$options[self::OSS_PREAUTH] = $timeout;
		$options[self::OSS_DATE] = $timeout;
		$this->setSignStsInUrl(true);
		return $this->auth($options);
	}

	private function precheckOptions(&$options)
	{
		Core\OssUtil::validateOptions($options);

		if (!$options) {
			$options = array();
		}
	}

	private function precheckBucket($bucket, $errMsg = 'bucket is not allowed empty')
	{
		Core\OssUtil::throwOssExceptionWithMessageIfEmpty($bucket, $errMsg);
	}

	private function precheckObject($object)
	{
		Core\OssUtil::throwOssExceptionWithMessageIfEmpty($object, 'object name is empty');
	}

	private function precheckCommon($bucket, $object, &$options, $isCheckObject = true)
	{
		if ($isCheckObject) {
			$this->precheckObject($object);
		}

		$this->precheckOptions($options);
		$this->precheckBucket($bucket);
	}

	private function precheckParam($options, $param, $funcName)
	{
		if (!isset($options[$param])) {
			throw new Core\OssException('The `' . $param . '` options is required in ' . $funcName . '().');
		}
	}

	private function isCheckMD5($options)
	{
		return $this->getValue($options, self::OSS_CHECK_MD5, false, true, true);
	}

	private function getValue($options, $key, $default = NULL, $isCheckEmpty = false, $isCheckBool = false)
	{
		$value = $default;

		if (isset($options[$key])) {
			if ($isCheckEmpty) {
				if (!empty($options[$key])) {
					$value = $options[$key];
				}
			}
			else {
				$value = $options[$key];
			}

			unset($options[$key]);
		}

		if ($isCheckBool) {
			if (($value !== true) && ($value !== false)) {
				$value = false;
			}
		}

		return $value;
	}

	private function getMimeType($object, $file = NULL)
	{
		if (!is_null($file)) {
			$type = Core\MimeTypes::getMimetype($file);

			if (!is_null($type)) {
				return $type;
			}
		}

		$type = Core\MimeTypes::getMimetype($object);

		if (!is_null($type)) {
			return $type;
		}

		return self::DEFAULT_CONTENT_TYPE;
	}

	private function auth($options)
	{
		Core\OssUtil::validateOptions($options);
		$this->authPrecheckBucket($options);
		$this->authPrecheckObject($options);
		$this->authPrecheckObjectEncoding($options);
		$this->authPrecheckAcl($options);
		$scheme = ($this->useSSL ? 'https://' : 'http://');
		$hostname = $this->generateHostname($options);
		$string_to_sign = '';
		$headers = $this->generateHeaders($options, $hostname);
		$signable_query_string_params = $this->generateSignableQueryStringParam($options);
		$signable_query_string = Core\OssUtil::toQueryString($signable_query_string_params);
		$resource_uri = $this->generateResourceUri($options);
		$conjunction = '?';
		$non_signable_resource = '';

		if (isset($options[self::OSS_SUB_RESOURCE])) {
			$conjunction = '&';
		}

		if ($signable_query_string !== '') {
			$signable_query_string = $conjunction . $signable_query_string;
			$conjunction = '&';
		}

		$query_string = $this->generateQueryString($options);

		if ($query_string !== '') {
			$non_signable_resource .= $conjunction . $query_string;
			$conjunction = '&';
		}

		$this->requestUrl = $scheme . $hostname . $resource_uri . $signable_query_string . $non_signable_resource;
		$request = new Http\RequestCore($this->requestUrl);
		$request->set_useragent($this->generateUserAgent());

		if (isset($options[self::OSS_FILE_UPLOAD])) {
			if (is_resource($options[self::OSS_FILE_UPLOAD])) {
				$length = null;

				if (isset($options[self::OSS_CONTENT_LENGTH])) {
					$length = $options[self::OSS_CONTENT_LENGTH];
				}
				else if (isset($options[self::OSS_SEEK_TO])) {
					$stats = fstat($options[self::OSS_FILE_UPLOAD]);
					if ($stats && (0 <= $stats[self::OSS_SIZE])) {
						$length = $stats[self::OSS_SIZE] - (int) $options[self::OSS_SEEK_TO];
					}
				}

				$request->set_read_stream($options[self::OSS_FILE_UPLOAD], $length);
			}
			else {
				$request->set_read_file($options[self::OSS_FILE_UPLOAD]);
				$length = $request->read_stream_size;

				if (isset($options[self::OSS_CONTENT_LENGTH])) {
					$length = $options[self::OSS_CONTENT_LENGTH];
				}
				else {
					if (isset($options[self::OSS_SEEK_TO]) && isset($length)) {
						$length -= (int) $options[self::OSS_SEEK_TO];
					}
				}

				$request->set_read_stream_size($length);
			}
		}

		if (isset($options[self::OSS_SEEK_TO])) {
			$request->set_seek_position((int) $options[self::OSS_SEEK_TO]);
		}

		if (isset($options[self::OSS_FILE_DOWNLOAD])) {
			if (is_resource($options[self::OSS_FILE_DOWNLOAD])) {
				$request->set_write_stream($options[self::OSS_FILE_DOWNLOAD]);
			}
			else {
				$request->set_write_file($options[self::OSS_FILE_DOWNLOAD]);
			}
		}

		if (isset($options[self::OSS_METHOD])) {
			$request->set_method($options[self::OSS_METHOD]);
			$string_to_sign .= $options[self::OSS_METHOD] . "\n";
		}

		if (isset($options[self::OSS_CONTENT])) {
			$request->set_body($options[self::OSS_CONTENT]);

			if ($headers[self::OSS_CONTENT_TYPE] === 'application/x-www-form-urlencoded') {
				$headers[self::OSS_CONTENT_TYPE] = 'application/octet-stream';
			}

			$headers[self::OSS_CONTENT_LENGTH] = strlen($options[self::OSS_CONTENT]);
			$headers[self::OSS_CONTENT_MD5] = base64_encode(md5($options[self::OSS_CONTENT], true));
		}

		uksort($headers, 'strnatcasecmp');

		foreach ($headers as $header_key => $header_value) {
			$header_value = str_replace(array("\r", "\n"), '', $header_value);

			if ($header_value !== '') {
				$request->add_header($header_key, $header_value);
			}

			if ((strtolower($header_key) === 'content-md5') || (strtolower($header_key) === 'content-type') || (strtolower($header_key) === 'date') || (isset($options['self::OSS_PREAUTH']) && (0 < (int) $options['self::OSS_PREAUTH']))) {
				$string_to_sign .= $header_value . "\n";
			}
			else if (substr(strtolower($header_key), 0, 6) === self::OSS_DEFAULT_PREFIX) {
				$string_to_sign .= strtolower($header_key) . ':' . $header_value . "\n";
			}
		}

		$signable_resource = $this->generateSignableResource($options);
		$string_to_sign .= rawurldecode($signable_resource) . urldecode($signable_query_string);
		$signature = base64_encode(hash_hmac('sha1', $string_to_sign, $this->accessKeySecret, true));
		$request->add_header('Authorization', 'OSS ' . $this->accessKeyId . ':' . $signature);
		if (isset($options[self::OSS_PREAUTH]) && (0 < (int) $options[self::OSS_PREAUTH])) {
			$signed_url = $this->requestUrl . $conjunction . self::OSS_URL_ACCESS_KEY_ID . '=' . rawurlencode($this->accessKeyId) . '&' . self::OSS_URL_EXPIRES . '=' . $options[self::OSS_PREAUTH] . '&' . self::OSS_URL_SIGNATURE . '=' . rawurlencode($signature);
			return $signed_url;
		}
		else if (isset($options[self::OSS_PREAUTH])) {
			return $this->requestUrl;
		}

		if ($this->timeout !== 0) {
			$request->timeout = $this->timeout;
		}

		if ($this->connectTimeout !== 0) {
			$request->connect_timeout = $this->connectTimeout;
		}

		try {
			$request->send_request();
		}
		catch (Http\RequestCore_Exception $e) {
			throw new Core\OssException('RequestCoreException: ' . $e->getMessage());
		}

		$response_header = $request->get_response_header();
		$response_header['oss-request-url'] = $this->requestUrl;
		$response_header['oss-redirects'] = $this->redirects;
		$response_header['oss-stringtosign'] = $string_to_sign;
		$response_header['oss-requestheaders'] = $request->request_headers;
		$data = new Http\ResponseCore($response_header, $request->get_response_body(), $request->get_response_code());

		if ((int) $request->get_response_code() === 500) {
			if ($this->redirects <= $this->maxRetries) {
				$delay = (int) (pow(4, $this->redirects) * 100000);
				usleep($delay);
				$this->redirects++;
				$data = $this->auth($options);
			}
		}

		$this->redirects = 0;
		return $data;
	}

	public function setMaxTries($maxRetries = 3)
	{
		$this->maxRetries = $maxRetries;
	}

	public function getMaxRetries()
	{
		return $this->maxRetries;
	}

	public function setSignStsInUrl($enable)
	{
		$this->enableStsInUrl = $enable;
	}

	public function isUseSSL()
	{
		return $this->useSSL;
	}

	public function setUseSSL($useSSL)
	{
		$this->useSSL = $useSSL;
	}

	private function authPrecheckBucket($options)
	{
		if (!(('/' == $options[self::OSS_OBJECT]) && ('' == $options[self::OSS_BUCKET]) && ('GET' == $options[self::OSS_METHOD])) && !Core\OssUtil::validateBucket($options[self::OSS_BUCKET])) {
			throw new Core\OssException('"' . $options[self::OSS_BUCKET] . '"' . 'bucket name is invalid');
		}
	}

	private function authPrecheckObject($options)
	{
		if (isset($options[self::OSS_OBJECT]) && ($options[self::OSS_OBJECT] === '/')) {
			return NULL;
		}

		if (isset($options[self::OSS_OBJECT]) && !Core\OssUtil::validateObject($options[self::OSS_OBJECT])) {
			throw new Core\OssException('"' . $options[self::OSS_OBJECT] . '"' . ' object name is invalid');
		}
	}

	private function authPrecheckObjectEncoding(&$options)
	{
		$tmp_object = $options[self::OSS_OBJECT];

		try {
			if (Core\OssUtil::isGb2312($options[self::OSS_OBJECT])) {
				$options[self::OSS_OBJECT] = iconv('GB2312', 'UTF-8//IGNORE', $options[self::OSS_OBJECT]);
			}
			else if (Core\OssUtil::checkChar($options[self::OSS_OBJECT], true)) {
				$options[self::OSS_OBJECT] = iconv('GBK', 'UTF-8//IGNORE', $options[self::OSS_OBJECT]);
			}
		}
		catch (\Exception $e) {
			try {
				$tmp_object = iconv(mb_detect_encoding($tmp_object), 'UTF-8', $tmp_object);
			}
			catch (\Exception $e) {
			}
		}

		$options[self::OSS_OBJECT] = $tmp_object;
	}

	private function authPrecheckAcl($options)
	{
		if (isset($options[self::OSS_HEADERS][self::OSS_ACL]) && !empty($options[self::OSS_HEADERS][self::OSS_ACL])) {
			if (!in_array(strtolower($options[self::OSS_HEADERS][self::OSS_ACL]), self::$OSS_ACL_TYPES)) {
				throw new Core\OssException($options[self::OSS_HEADERS][self::OSS_ACL] . ':' . 'acl is invalid(private,public-read,public-read-write)');
			}
		}
	}

	private function generateHostname($options)
	{
		if ($this->hostType === self::OSS_HOST_TYPE_IP) {
			$hostname = $this->hostname;
		}
		else if ($this->hostType === self::OSS_HOST_TYPE_CNAME) {
			$hostname = $this->hostname;
		}
		else {
			$hostname = ($options[self::OSS_BUCKET] == '' ? $this->hostname : $options[self::OSS_BUCKET] . '.' . $this->hostname);
		}

		return $hostname;
	}

	private function generateResourceUri($options)
	{
		$resource_uri = '';
		if (isset($options[self::OSS_BUCKET]) && ('' !== $options[self::OSS_BUCKET])) {
			if ($this->hostType === self::OSS_HOST_TYPE_IP) {
				$resource_uri = '/' . $options[self::OSS_BUCKET];
			}
		}

		if (isset($options[self::OSS_OBJECT]) && ('/' !== $options[self::OSS_OBJECT])) {
			$resource_uri .= '/' . str_replace(array('%2F', '%25'), array('/', '%'), rawurlencode($options[self::OSS_OBJECT]));
		}

		$conjunction = '?';

		if (isset($options[self::OSS_SUB_RESOURCE])) {
			$resource_uri .= $conjunction . $options[self::OSS_SUB_RESOURCE];
		}

		return $resource_uri;
	}

	private function generateSignableQueryStringParam($options)
	{
		$signableQueryStringParams = array();
		$signableList = array(self::OSS_PART_NUM, 'response-content-type', 'response-content-language', 'response-cache-control', 'response-content-encoding', 'response-expires', 'response-content-disposition', self::OSS_UPLOAD_ID, self::OSS_CNAME_COMP);

		foreach ($signableList as $item) {
			if (isset($options[$item])) {
				$signableQueryStringParams[$item] = $options[$item];
			}
		}

		if ($this->enableStsInUrl && !is_null($this->securityToken)) {
			$signableQueryStringParams['security-token'] = $this->securityToken;
		}

		return $signableQueryStringParams;
	}

	private function generateSignableResource($options)
	{
		$signableResource = '';
		$signableResource .= '/';
		if (isset($options[self::OSS_BUCKET]) && ('' !== $options[self::OSS_BUCKET])) {
			$signableResource .= $options[self::OSS_BUCKET];

			if ($options[self::OSS_OBJECT] == '/') {
				if ($this->hostType !== self::OSS_HOST_TYPE_IP) {
					$signableResource .= '/';
				}
			}
		}

		if (isset($options[self::OSS_OBJECT]) && ('/' !== $options[self::OSS_OBJECT])) {
			$signableResource .= '/' . str_replace(array('%2F', '%25'), array('/', '%'), rawurlencode($options[self::OSS_OBJECT]));
		}

		if (isset($options[self::OSS_SUB_RESOURCE])) {
			$signableResource .= '?' . $options[self::OSS_SUB_RESOURCE];
		}

		return $signableResource;
	}

	private function generateQueryString($options)
	{
		$queryStringParams = array();

		if (isset($options[self::OSS_QUERY_STRING])) {
			$queryStringParams = array_merge($queryStringParams, $options[self::OSS_QUERY_STRING]);
		}

		return Core\OssUtil::toQueryString($queryStringParams);
	}

	private function generateHeaders($options, $hostname)
	{
		$headers = array(self::OSS_CONTENT_MD5 => '', self::OSS_CONTENT_TYPE => isset($options[self::OSS_CONTENT_TYPE]) ? $options[self::OSS_CONTENT_TYPE] : self::DEFAULT_CONTENT_TYPE, self::OSS_DATE => isset($options[self::OSS_DATE]) ? $options[self::OSS_DATE] : gmdate('D, d M Y H:i:s \\G\\M\\T'), self::OSS_HOST => $hostname);

		if (isset($options[self::OSS_CONTENT_MD5])) {
			$headers[self::OSS_CONTENT_MD5] = $options[self::OSS_CONTENT_MD5];
		}

		if (!is_null($this->securityToken) && !$this->enableStsInUrl) {
			$headers[self::OSS_SECURITY_TOKEN] = $this->securityToken;
		}

		if (isset($options[self::OSS_HEADERS])) {
			$headers = array_merge($headers, $options[self::OSS_HEADERS]);
		}

		return $headers;
	}

	private function generateUserAgent()
	{
		return self::OSS_NAME . '/' . self::OSS_VERSION . ' (' . php_uname('s') . '/' . php_uname('r') . '/' . php_uname('m') . ';' . PHP_VERSION . ')';
	}

	private function checkEndpoint($endpoint, $isCName)
	{
		$ret_endpoint = null;

		if (strpos($endpoint, 'http://') === 0) {
			$ret_endpoint = substr($endpoint, strlen('http://'));
		}
		else if (strpos($endpoint, 'https://') === 0) {
			$ret_endpoint = substr($endpoint, strlen('https://'));
			$this->useSSL = true;
		}
		else {
			$ret_endpoint = $endpoint;
		}

		if ($isCName) {
			$this->hostType = self::OSS_HOST_TYPE_CNAME;
		}
		else if (Core\OssUtil::isIPFormat($ret_endpoint)) {
			$this->hostType = self::OSS_HOST_TYPE_IP;
		}
		else {
			$this->hostType = self::OSS_HOST_TYPE_NORMAL;
		}

		return $ret_endpoint;
	}

	static public function checkEnv()
	{
		if (function_exists('get_loaded_extensions')) {
			$enabled_extension = array('curl');
			$extensions = get_loaded_extensions();

			if ($extensions) {
				foreach ($enabled_extension as $item) {
					if (!in_array($item, $extensions)) {
						throw new Core\OssException('Extension {' . $item . '} is not installed or not enabled, please check your php env.');
					}
				}
			}
			else {
				throw new Core\OssException('function get_loaded_extensions not found.');
			}
		}
		else {
			throw new Core\OssException('Function get_loaded_extensions has been disabled, please check php config.');
		}
	}

	public function setTimeout($timeout)
	{
		$this->timeout = $timeout;
	}

	public function setConnectTimeout($connectTimeout)
	{
		$this->connectTimeout = $connectTimeout;
	}
}


?>
