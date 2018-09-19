<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MerchantsDocumenttitle extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_documenttitle';
	protected $primaryKey = 'dt_id';
	public $timestamps = false;
	protected $fillable = array('dt_title', 'cat_id');
	protected $guarded = array();

	public function getDtTitle()
	{
		return $this->dt_title;
	}

	public function getCatId()
	{
		return $this->cat_id;
	}

	public function setDtTitle($value)
	{
		$this->dt_title = $value;
		return $this;
	}

	public function setCatId($value)
	{
		$this->cat_id = $value;
		return $this;
	}
}

?>
