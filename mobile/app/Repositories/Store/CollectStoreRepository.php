<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Store;

class CollectStoreRepository
{
	public function findByUserId($uid)
	{
		$list = \App\Models\CollectStore::select('ru_id')->where('user_id', $uid)->get()->toArray();
		return $list;
	}

	public function findOne($ruId, $uid)
	{
		$cg = \App\Models\CollectStore::where('ru_id', $ruId)->where('user_id', $uid)->first();

		if ($cg === null) {
			return array();
		}

		return $cg->toArray();
	}

	public function addCollectStore($ruId, $uid)
	{
		$model = new \App\Models\CollectStore();
		$model->user_id = $uid;
		$model->ru_id = $ruId;
		$model->add_time = gmtime();
		$model->is_attention = 0;
		return $model->save();
	}

	public function deleteCollectStore($ruId, $uid)
	{
		return \App\Models\CollectStore::where('ru_id', $ruId)->where('user_id', $uid)->delete();
	}
}


?>
