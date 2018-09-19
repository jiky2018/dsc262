<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class MerchantsDtFile extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_dt_file';
	protected $primaryKey = 'dtf_id';
	public $timestamps = false;
	protected $fillable = array('cat_id', 'dt_id', 'user_id', 'permanent_file', 'permanent_date', 'cate_title_permanent');
	protected $guarded = array();

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function getDtId()
	{
		return $this->dt_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getPermanentFile()
	{
		return $this->permanent_file;
	}

	public function getPermanentDate()
	{
		return $this->permanent_date;
	}

	public function getCateTitlePermanent()
	{
		return $this->cate_title_permanent;
	}

	public function setCatId($value)
	{
		$this->cat_id = $value;
		return $this;
	}

	public function setDtId($value)
	{
		$this->dt_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setPermanentFile($value)
	{
		$this->permanent_file = $value;
		return $this;
	}

	public function setPermanentDate($value)
	{
		$this->permanent_date = $value;
		return $this;
	}

	public function setCateTitlePermanent($value)
	{
		$this->cate_title_permanent = $value;
		return $this;
	}
}

?>
