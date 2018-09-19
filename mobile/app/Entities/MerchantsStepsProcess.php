<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Entities;

class MerchantsStepsProcess extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'merchants_steps_process';
	public $timestamps = false;
	protected $fillable = array('process_steps', 'process_title', 'process_article', 'steps_sort', 'is_show', 'fields_next');
	protected $guarded = array();

	public function getProcessSteps()
	{
		return $this->process_steps;
	}

	public function getProcessTitle()
	{
		return $this->process_title;
	}

	public function getProcessArticle()
	{
		return $this->process_article;
	}

	public function getStepsSort()
	{
		return $this->steps_sort;
	}

	public function getIsShow()
	{
		return $this->is_show;
	}

	public function getFieldsNext()
	{
		return $this->fields_next;
	}

	public function setProcessSteps($value)
	{
		$this->process_steps = $value;
		return $this;
	}

	public function setProcessTitle($value)
	{
		$this->process_title = $value;
		return $this;
	}

	public function setProcessArticle($value)
	{
		$this->process_article = $value;
		return $this;
	}

	public function setStepsSort($value)
	{
		$this->steps_sort = $value;
		return $this;
	}

	public function setIsShow($value)
	{
		$this->is_show = $value;
		return $this;
	}

	public function setFieldsNext($value)
	{
		$this->fields_next = $value;
		return $this;
	}
}

?>
