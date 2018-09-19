<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class AdminAction extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'admin_action';
	protected $primaryKey = 'action_id';
	public $timestamps = false;
	protected $fillable = array('parent_id', 'action_code', 'relevance', 'seller_show');
	protected $guarded = array();

	public function getParentId()
	{
		return $this->parent_id;
	}

	public function getActionCode()
	{
		return $this->action_code;
	}

	public function getRelevance()
	{
		return $this->relevance;
	}

	public function getSellerShow()
	{
		return $this->seller_show;
	}

	public function setParentId($value)
	{
		$this->parent_id = $value;
		return $this;
	}

	public function setActionCode($value)
	{
		$this->action_code = $value;
		return $this;
	}

	public function setRelevance($value)
	{
		$this->relevance = $value;
		return $this;
	}

	public function setSellerShow($value)
	{
		$this->seller_show = $value;
		return $this;
	}
}

?>
