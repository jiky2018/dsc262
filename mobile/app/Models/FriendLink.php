<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class FriendLink extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'friend_link';
	protected $primaryKey = 'link_id';
	public $timestamps = false;
	protected $fillable = array('link_name', 'link_url', 'link_logo', 'show_order');
	protected $guarded = array();

	public function getLinkName()
	{
		return $this->link_name;
	}

	public function getLinkUrl()
	{
		return $this->link_url;
	}

	public function getLinkLogo()
	{
		return $this->link_logo;
	}

	public function getShowOrder()
	{
		return $this->show_order;
	}

	public function setLinkName($value)
	{
		$this->link_name = $value;
		return $this;
	}

	public function setLinkUrl($value)
	{
		$this->link_url = $value;
		return $this;
	}

	public function setLinkLogo($value)
	{
		$this->link_logo = $value;
		return $this;
	}

	public function setShowOrder($value)
	{
		$this->show_order = $value;
		return $this;
	}
}

?>
