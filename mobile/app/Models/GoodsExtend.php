<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class GoodsExtend extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_extend';
	protected $primaryKey = 'extend_id';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'is_reality', 'is_return', 'is_fast', 'width', 'height', 'depth', 'origincountry', 'originplace', 'assemblycountry', 'barcodetype', 'catena', 'isbasicunit', 'packagetype', 'grossweight', 'netweight', 'netcontent', 'licensenum', 'healthpermitnum');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getIsReality()
	{
		return $this->is_reality;
	}

	public function getIsReturn()
	{
		return $this->is_return;
	}

	public function getIsFast()
	{
		return $this->is_fast;
	}

	public function getWidth()
	{
		return $this->width;
	}

	public function getHeight()
	{
		return $this->height;
	}

	public function getDepth()
	{
		return $this->depth;
	}

	public function getOrigincountry()
	{
		return $this->origincountry;
	}

	public function getOriginplace()
	{
		return $this->originplace;
	}

	public function getAssemblycountry()
	{
		return $this->assemblycountry;
	}

	public function getBarcodetype()
	{
		return $this->barcodetype;
	}

	public function getCatena()
	{
		return $this->catena;
	}

	public function getIsbasicunit()
	{
		return $this->isbasicunit;
	}

	public function getPackagetype()
	{
		return $this->packagetype;
	}

	public function getGrossweight()
	{
		return $this->grossweight;
	}

	public function getNetweight()
	{
		return $this->netweight;
	}

	public function getNetcontent()
	{
		return $this->netcontent;
	}

	public function getLicensenum()
	{
		return $this->licensenum;
	}

	public function getHealthpermitnum()
	{
		return $this->healthpermitnum;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setIsReality($value)
	{
		$this->is_reality = $value;
		return $this;
	}

	public function setIsReturn($value)
	{
		$this->is_return = $value;
		return $this;
	}

	public function setIsFast($value)
	{
		$this->is_fast = $value;
		return $this;
	}

	public function setWidth($value)
	{
		$this->width = $value;
		return $this;
	}

	public function setHeight($value)
	{
		$this->height = $value;
		return $this;
	}

	public function setDepth($value)
	{
		$this->depth = $value;
		return $this;
	}

	public function setOrigincountry($value)
	{
		$this->origincountry = $value;
		return $this;
	}

	public function setOriginplace($value)
	{
		$this->originplace = $value;
		return $this;
	}

	public function setAssemblycountry($value)
	{
		$this->assemblycountry = $value;
		return $this;
	}

	public function setBarcodetype($value)
	{
		$this->barcodetype = $value;
		return $this;
	}

	public function setCatena($value)
	{
		$this->catena = $value;
		return $this;
	}

	public function setIsbasicunit($value)
	{
		$this->isbasicunit = $value;
		return $this;
	}

	public function setPackagetype($value)
	{
		$this->packagetype = $value;
		return $this;
	}

	public function setGrossweight($value)
	{
		$this->grossweight = $value;
		return $this;
	}

	public function setNetweight($value)
	{
		$this->netweight = $value;
		return $this;
	}

	public function setNetcontent($value)
	{
		$this->netcontent = $value;
		return $this;
	}

	public function setLicensenum($value)
	{
		$this->licensenum = $value;
		return $this;
	}

	public function setHealthpermitnum($value)
	{
		$this->healthpermitnum = $value;
		return $this;
	}
}

?>
