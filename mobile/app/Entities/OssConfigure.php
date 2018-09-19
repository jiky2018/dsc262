<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class OssConfigure extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'oss_configure';
	public $timestamps = false;
	protected $fillable = array('bucket', 'keyid', 'keysecret', 'is_cname', 'endpoint', 'regional', 'is_use', 'is_delimg');
	protected $guarded = array();

	public function getBucket()
	{
		return $this->bucket;
	}

	public function getKeyid()
	{
		return $this->keyid;
	}

	public function getKeysecret()
	{
		return $this->keysecret;
	}

	public function getIsCname()
	{
		return $this->is_cname;
	}

	public function getEndpoint()
	{
		return $this->endpoint;
	}

	public function getRegional()
	{
		return $this->regional;
	}

	public function getIsUse()
	{
		return $this->is_use;
	}

	public function getIsDelimg()
	{
		return $this->is_delimg;
	}

	public function setBucket($value)
	{
		$this->bucket = $value;
		return $this;
	}

	public function setKeyid($value)
	{
		$this->keyid = $value;
		return $this;
	}

	public function setKeysecret($value)
	{
		$this->keysecret = $value;
		return $this;
	}

	public function setIsCname($value)
	{
		$this->is_cname = $value;
		return $this;
	}

	public function setEndpoint($value)
	{
		$this->endpoint = $value;
		return $this;
	}

	public function setRegional($value)
	{
		$this->regional = $value;
		return $this;
	}

	public function setIsUse($value)
	{
		$this->is_use = $value;
		return $this;
	}

	public function setIsDelimg($value)
	{
		$this->is_delimg = $value;
		return $this;
	}
}

?>
