<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\User;

class AccountRepository
{
	const SURPLUS_SAVE = 0;
	const SURPLUS_RETURN = 1;

	public function accountList($userId, $page = 1, $size = 10)
	{
		$start = ($page - 1) * $size;
		return \App\Models\AccountLog::where('user_id', $userId)->offset($start)->limit($size)->get()->toArray();
	}

	public function accountLogList($userId, $page = 1, $size = 10)
	{
		$start = ($page - 1) * $size;
		return \App\Models\UserAccount::where('user_id', $userId)->wherein('process_type', array(0, 1))->offset($start)->limit($size)->get()->toArray();
	}

	public function deposit($arr)
	{
		$model = new \App\Models\UserAccount();

		foreach ($arr as $k => $v) {
			$model->$k = $v;
		}

		return $model->save();
	}

	public function getDepositInfo($id)
	{
		$model = \App\Models\UserAccount::where('id', $id)->first();

		if ($model === null) {
			return array();
		}

		return $model->toArray();
	}

	public function logAccountChange($user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '', $change_type = 99, $uid)
	{
		$flag = 0;

		if ($member = \App\Models\Users::where('user_id', $uid)->first()) {
			$member->user_money += $user_money;
			$member->frozen_money += $frozen_money;
			$member->rank_points += $rank_points;
			$member->pay_points += $pay_points;
			$flag = $member->save();
		}

		if ($flag) {
			$model = new \App\Models\AccountLog();
			$model->user_id = $uid;
			$model->pay_points = $pay_points;
			$model->change_desc = $change_desc;
			$model->user_money = $user_money;
			$model->rank_points = $rank_points;
			$model->frozen_money = $frozen_money;
			$model->change_type = $change_type;
			$model->change_time = gmtime();

			if ($model->save()) {
				return true;
			}
		}

		return false;
	}
}


?>
