<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MerchantsShopBrandfile extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_shop_brandfile';
	protected $primaryKey = 'b_fid';
	public $timestamps = false;
	protected $fillable = array('bid', 'qualificationNameInput', 'qualificationImg', 'expiredDateInput', 'expiredDate_permanent');
	protected $guarded = array();

	public function getBid()
	{
		return $this->bid;
	}

	public function getQualificationNameInput()
	{
		return $this->qualificationNameInput;
	}

	public function getQualificationImg()
	{
		return $this->qualificationImg;
	}

	public function getExpiredDateInput()
	{
		return $this->expiredDateInput;
	}

	public function getExpiredDatePermanent()
	{
		return $this->expiredDate_permanent;
	}

	public function setBid($value)
	{
		$this->bid = $value;
		return $this;
	}

	public function setQualificationNameInput($value)
	{
		$this->qualificationNameInput = $value;
		return $this;
	}

	public function setQualificationImg($value)
	{
		$this->qualificationImg = $value;
		return $this;
	}

	public function setExpiredDateInput($value)
	{
		$this->expiredDateInput = $value;
		return $this;
	}

	public function setExpiredDatePermanent($value)
	{
		$this->expiredDate_permanent = $value;
		return $this;
	}
}

?>
