<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\User;

class UserRepository
{
	public function userInfo($uid)
	{
		$user = \App\Models\Users::where('user_id', $uid)->select('user_id as id', 'user_name', 'nick_name', 'sex', 'birthday', 'user_money', 'frozen_money', 'pay_points', 'rank_points', 'address_id', 'qq', 'mobile_phone', 'user_picture')->first();

		if ($user === null) {
			return array();
		}

		return $user->toArray();
	}

	public function userFunds($uid)
	{
		$user = \App\Models\Users::where('user_id', $uid)->select('user_money', 'frozen_money', 'pay_points')->first();

		if ($user === null) {
			return array();
		}

		$bonus_count = \App\Models\UserBonus::where('user_id', $uid)->count();
		$coupons_count = \App\Models\CouponsUser::where('user_id', $uid)->count();
		$store_count = \App\Models\CollectStore::where('user_id', $uid)->count();
		$goods_count = \App\Models\CollectGoods::where('user_id', $uid)->count();
		$result = array();
		$result['user_money'] = $user['user_money'];
		$result['pay_points'] = $user['pay_points'];
		$result['bonus_count'] = $bonus_count;
		$result['coupons_count'] = $coupons_count;
		$result['store_count'] = $store_count;
		$result['goods_count'] = $goods_count;
		return $result;
	}

	public function renewUser($res)
	{
		$model = new \App\Models\Users();
		$model = \App\Models\Users::where('user_id', $res['user_id'])->first();

		if ($model === null) {
			return array();
		}

		$model->fill($res);
		return $model->save();
	}

	public function getConnectUser($unionid)
	{
		$connectUser = \App\Models\Users::select('users.user_id')->leftjoin('connect_user', 'connect_user.user_id', '=', 'users.user_id')->where('connect_user.open_id', $unionid)->first();

		if ($connectUser === null) {
			return array();
		}

		return $connectUser->toArray();
	}

	public function addConnectUser($res)
	{
		$model = new \App\Models\ConnectUser();
		$model->fill($res);
		$model->save();
		return $model->id;
	}

	public function updateConnnectUser($res)
	{
		$model = new \App\Models\ConnectUser();
		$model = \App\Models\ConnectUser::where('open_id', $res['open_id'])->first();

		if ($model === null) {
			return array();
		}

		$model->fill($res);
		return $model->save();
	}

	public function setDefaultAddress($id, $uid)
	{
		$model = \App\Models\Users::where('user_id', $uid)->first();

		if ($model == null) {
			return false;
		}

		$model->address_id = $id;
		return $model->save();
	}

	public function getUserOpenid($uid)
	{
		$list = \App\Models\WechatUser::from('wechat_user as wu')->select('wu.openid')->leftjoin('connect_user as cu', 'cu.open_id', '=', 'wu.unionid')->where('cu.user_id', $uid)->first();

		if ($list === null) {
			return array();
		}

		return $list->toArray();
	}
}


?>
