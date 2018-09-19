<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class Brand extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'brand';
	protected $primaryKey = 'brand_id';
	public $timestamps = false;
	protected $fillable = array('brand_name', 'brand_letter', 'brand_first_char', 'brand_logo', 'index_img', 'brand_bg', 'brand_desc', 'site_url', 'sort_order', 'is_show', 'is_delete', 'audit_status', 'add_time');
	protected $guarded = array();

	public function getBrandName()
	{
		return $this->brand_name;
	}

	public function getBrandLetter()
	{
		return $this->brand_letter;
	}

	public function getBrandFirstChar()
	{
		return $this->brand_first_char;
	}

	public function getBrandLogo()
	{
		return $this->brand_logo;
	}

	public function getIndexImg()
	{
		return $this->index_img;
	}

	public function getBrandBg()
	{
		return $this->brand_bg;
	}

	public function getBrandDesc()
	{
		return $this->brand_desc;
	}

	public function getSiteUrl()
	{
		return $this->site_url;
	}

	public function getSortOrder()
	{
		return $this->sort_order;
	}

	public function getIsShow()
	{
		return $this->is_show;
	}

	public function getIsDelete()
	{
		return $this->is_delete;
	}

	public function getAuditStatus()
	{
		return $this->audit_status;
	}

	public function getAddTime()
	{
		return $this->add_time;
	}

	public function setBrandName($value)
	{
		$this->brand_name = $value;
		return $this;
	}

	public function setBrandLetter($value)
	{
		$this->brand_letter = $value;
		return $this;
	}

	public function setBrandFirstChar($value)
	{
		$this->brand_first_char = $value;
		return $this;
	}

	public function setBrandLogo($value)
	{
		$this->brand_logo = $value;
		return $this;
	}

	public function setIndexImg($value)
	{
		$this->index_img = $value;
		return $this;
	}

	public function setBrandBg($value)
	{
		$this->brand_bg = $value;
		return $this;
	}

	public function setBrandDesc($value)
	{
		$this->brand_desc = $value;
		return $this;
	}

	public function setSiteUrl($value)
	{
		$this->site_url = $value;
		return $this;
	}

	public function setSortOrder($value)
	{
		$this->sort_order = $value;
		return $this;
	}

	public function setIsShow($value)
	{
		$this->is_show = $value;
		return $this;
	}

	public function setIsDelete($value)
	{
		$this->is_delete = $value;
		return $this;
	}

	public function setAuditStatus($value)
	{
		$this->audit_status = $value;
		return $this;
	}

	public function setAddTime($value)
	{
		$this->add_time = $value;
		return $this;
	}
}

?>
