<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Models;

class GoodsArticle extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'goods_article';
	public $timestamps = false;
	protected $fillable = array('goods_id', 'article_id', 'admin_id');
	protected $guarded = array();

	public function getGoodsId()
	{
		return $this->goods_id;
	}

	public function getArticleId()
	{
		return $this->article_id;
	}

	public function getAdminId()
	{
		return $this->admin_id;
	}

	public function setGoodsId($value)
	{
		$this->goods_id = $value;
		return $this;
	}

	public function setArticleId($value)
	{
		$this->article_id = $value;
		return $this;
	}

	public function setAdminId($value)
	{
		$this->admin_id = $value;
		return $this;
	}
}

?>
