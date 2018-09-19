<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class Card extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'card';
	protected $primaryKey = 'card_id';
	public $timestamps = false;
	protected $fillable = array('card_name', 'user_id', 'card_img', 'card_fee', 'free_money', 'card_desc');
	protected $guarded = array();

	public function getCardName()
	{
		return $this->card_name;
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getCardImg()
	{
		return $this->card_img;
	}

	public function getCardFee()
	{
		return $this->card_fee;
	}

	public function getFreeMoney()
	{
		return $this->free_money;
	}

	public function getCardDesc()
	{
		return $this->card_desc;
	}

	public function setCardName($value)
	{
		$this->card_name = $value;
		return $this;
	}

	public function setUserId($value)
	{
		$this->user_id = $value;
		return $this;
	}

	public function setCardImg($value)
	{
		$this->card_img = $value;
		return $this;
	}

	public function setCardFee($value)
	{
		$this->card_fee = $value;
		return $this;
	}

	public function setFreeMoney($value)
	{
		$this->free_money = $value;
		return $this;
	}

	public function setCardDesc($value)
	{
		$this->card_desc = $value;
		return $this;
	}
}

?>
