<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Repositories\Wechat;

class WechatUserRepository
{
	public function all($columns = array('*'))
	{
	}

	public function paginate($perPage = 15, $columns = array('*'))
	{
	}

	public function create(array $data)
	{
	}

	public function update(array $data, $id)
	{
	}

	public function delete($id)
	{
	}

	public function find($id, $columns = array('*'))
	{
	}

	public function findBy($field, $value, $columns = array('*'))
	{
	}

	public function getWechatConfig($ru_id = 0)
	{
		if (0 < $ru_id) {
			$where = array('ru_id' => $ru_id);
		}
		else {
			$where = array('default_wx' => 1);
		}

		$wechat = \App\Models\Wechat::where($where)->select('id', 'name', 'orgid', 'weixin', 'token', 'appid', 'appsecret', 'type', 'status')->first();

		if ($wechat === null) {
			return array();
		}

		return $wechat->toArray();
	}

	public function addWechatUser($res)
	{
		$model = new \App\Models\WechatUser();
		$wechat = $this->getWechatConfig();
		$res['wechat_id'] = $wechat['id'];
		$res['from'] = 3;
		$model->fill($res);
		$model->save();
		return $model->uid;
	}

	public function updateWechatUser($res)
	{
		$model = new \App\Models\WechatUser();
		$wechat = $this->getWechatConfig();
		$model = \App\Models\WechatUser::where('unionid', $res['unionid'])->where('wechat_id', $wechat['id'])->first();

		if ($model === null) {
			return array();
		}

		$model->fill($res);
		return $model->save();
	}

	public function getWechatUserInfo($unionid)
	{
		$wechat = $this->getWechatConfig();
		$wechatuser = \App\Models\WechatUser::where('unionid', $unionid)->where('wechat_id', $wechat['id'])->select('subscribe', 'openid', 'nickname', 'sex', 'city', 'country', 'province', 'headimgurl', 'unionid', 'ect_uid')->first();

		if ($wechatuser === null) {
			return array();
		}

		return $wechatuser->toArray();
	}
}


?>
