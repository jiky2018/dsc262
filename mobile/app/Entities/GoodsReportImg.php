<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class GoodsReportImg extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_report_img';
	protected $primaryKey = 'img_id';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'report_id', 'user_id', 'img_file');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getReportId()
	{
		return $this->report_id;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getImgFile()
	{
		return $this->img_file;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setReportId($value)
	{
		$this->report_id = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setImgFile($value)
	{
		$this->img_file = $value;
		return $this;
	}
}

?>
