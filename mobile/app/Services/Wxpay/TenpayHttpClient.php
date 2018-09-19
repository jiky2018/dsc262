<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services\Wxpay;

class TenpayHttpClient
{
	public $reqContent;
	public $reqBody;
	public $resContent;
	public $method;
	public $certFile;
	public $certPasswd;
	public $certType;
	public $caFile;
	public $errInfo;
	public $timeOut;
	public $responseCode;

	public function __construct()
	{
		$this->reqContent = '';
		$this->resContent = '';
		$this->method = 'post';
		$this->certFile = '';
		$this->certPasswd = '';
		$this->certType = 'PEM';
		$this->caFile = '';
		$this->errInfo = '';
		$this->timeOut = 120;
		$this->responseCode = 0;
	}

	public function setReqContent($reqContent)
	{
		$this->reqContent = $reqContent;
	}

	public function setReqBody($body)
	{
		$this->reqBody = $body;
	}

	public function getResContent()
	{
		return $this->resContent;
	}

	public function setMethod($method)
	{
		$this->method = $method;
	}

	public function getErrInfo()
	{
		return $this->errInfo;
	}

	public function setCertInfo($certFile, $certPasswd, $certType = 'PEM')
	{
		$this->certFile = $certFile;
		$this->certPasswd = $certPasswd;
		$this->certType = $certType;
	}

	public function setCaInfo($caFile)
	{
		$this->caFile = $caFile;
	}

	public function setTimeOut($timeOut)
	{
		$this->timeOut = $timeOut;
	}

	public function call()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeOut);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		if (strtolower($this->method) == 'post') {
			curl_setopt($ch, CURLOPT_URL, $this->reqContent);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->reqBody);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->reqBody);
		}
		else {
			curl_setopt($ch, CURLOPT_URL, $this->reqContent);
		}

		if ($this->certFile != '') {
			curl_setopt($ch, CURLOPT_SSLCERT, $this->certFile);
			curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->certPasswd);
			curl_setopt($ch, CURLOPT_SSLCERTTYPE, $this->certType);
		}

		if ($this->caFile != '') {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($ch, CURLOPT_CAINFO, $this->caFile);
		}
		else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		$res = curl_exec($ch);
		$this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($res == null) {
			$this->errInfo = 'call http err :' . curl_errno($ch) . ' - ' . curl_error($ch);
			curl_close($ch);
			return false;
		}
		else if ($this->responseCode != '200') {
			$this->errInfo = 'call http err httpcode=' . $this->responseCode;
			curl_close($ch);
			return false;
		}

		curl_close($ch);
		$this->resContent = $res;
		return true;
	}

	public function getResponseCode()
	{
		return $this->responseCode;
	}
}


?>
