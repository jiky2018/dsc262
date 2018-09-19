<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class SellerQrcode extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'seller_qrcode';
	protected $primaryKey = 'qrcode_id';
	public $timestamps = false;
	protected $fillable = array('ru_id', 'qrcode_thumb');
	protected $guarded = array();

	public function getRuId()
	{
		return $this->ru_id;
	}

	public function getQrcodeThumb()
	{
		return $this->qrcode_thumb;
	}

	public function setRuId($value)
	{
		$this->ru_id = $value;
		return $this;
	}

	public function setQrcodeThumb($value)
	{
		$this->qrcode_thumb = $value;
		return $this;
	}
}

?>
