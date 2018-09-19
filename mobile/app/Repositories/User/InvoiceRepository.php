<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\User;

class InvoiceRepository
{
	public function addInvoice($args)
	{
		$model = new \App\Models\UsersVatInvoicesInfo();

		foreach ($args as $k => $v) {
			$model->$k = $v;
		}

		$model->save();
		return $model->id;
	}

	public function updateInvoice($id, array $args)
	{
		$model = \App\Models\UsersVatInvoicesInfo::where('user_id', $args['user_id'])->where('id', $id)->first();

		if ($model === null) {
			return array();
		}

		foreach ($args as $k => $v) {
			$model->$k = $v;
		}

		return $model->save();
	}

	public function deleteInvoice($id, $uid)
	{
		return \App\Models\UsersVatInvoicesInfo::where('user_id', $uid)->where('id', $id)->delete();
	}

	public function find($uid)
	{
		$invoice = \App\Models\UsersVatInvoicesInfo::where('user_id', $uid)->first();
		return $invoice;
	}
}


?>
