<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Chat\Models;

class Kefu extends \Think\Model
{
	static public $pre = '{pre}';

	static public function getAdmin($admin = 0)
	{
		if (empty($admin)) {
			return NULL;
		}

		$adminUser = \App\Models\AdminUser::where('user_id', $admin)->with(array('Service' => function($query) {
			$query->addSelect('id', 'user_id', 'login_time')->where('status', 1);
		}))->first()->toArray();
		return $adminUser;
	}

	static public function getService($id)
	{
		$service = \App\Models\ImService::where('user_id', $id)->where('status', 1)->select('id', 'nick_name', 'chat_status')->first();

		if ($service) {
			return $service->toArray();
		}
	}

	static public function getServiceById($id)
	{
		$service = \App\Models\ImService::where('id', $id)->where('status', 1)->select('id', 'nick_name', 'chat_status', 'user_id')->first();

		if ($service) {
			return $service->toArray();
		}
	}

	static public function getServiceList($ruId, $sid)
	{
		if (empty($ruId) && $ruId !== 0) {
			return NULL;
		}

		$adminUser = \App\Models\AdminUser::where('ru_id', $ruId)->select(array('user_id'))->with(array('Service' => function($query) {
			$query->where('status', 1);
		}))->get()->toArray();

		foreach ($adminUser as $k => $v) {
			if ($v['service']['id'] == $sid) {
				unset($adminUser[$k]);
				continue;
			}
		}

		$adminUser = array_map(function($v) {
			if (!empty($v['service'])) {
				$v['id'] = $v['service']['id'];
				$v['name'] = $v['service']['nick_name'];
			}

			unset($v['service']);
			unset($v['user_id']);
			return $v;
		}, $adminUser);
		return $adminUser;
	}

	static public function getWait($ru_id = 0)
	{
		$waitMessage = \App\Models\ImDialog::select('id', 'customer_id', 'services_id', 'origin', 'goods_id', 'store_id', 'start_time')->where('services_id', 0)->where('store_id', $ru_id)->where('status', 1)->orderby('start_time', 'DESC')->groupby('customer_id')->get()->toArray();
		$total = 0;
		$waitMessageDataList = array();

		foreach ($waitMessage as $k => $v) {
			$waitMessage[$k]['add_time'] = date('Y-m-d H:i:s', $v['start_time']);
			$waitMessage[$k]['origin'] = $v['origin'] == 1 ? 'PC' : 'H5';
			$res = \App\Models\ImMessage::where('from_user_id', $v['customer_id'])->where('to_user_id', 0)->where('status', 1)->orderby('add_time', 'desc')->get()->toArray();

			if (empty($res)) {
				unset($waitMessage[$k]);
				continue;
			}

			$message = array();

			foreach ($res as $rk => $rv) {
				array_push($message, htmlspecialchars_decode($rv['message']));
			}

			$waitMessageDataList[$v['customer_id']] = array_reverse($message);
			$temp = $res[count($res) - 1];
			unset($res);
			$res = $temp;
			$waitMessage[$k]['num'] = \App\Models\ImMessage::where('from_user_id', $v['customer_id'])->where('to_user_id', 0)->where('status', 1)->orderby('add_time', 'desc')->count();
			$total += $waitMessage[$k]['num'];
			$waitMessage[$k]['fid'] = $res['from_user_id'];
			$waitMessage[$k]['message'] = htmlspecialchars_decode($res['message']);
			$waitMessage[$k]['message_list'] = $message;
			$waitMessage[$k]['dialog_id'] = $res['dialog_id'];
			$res = \App\Models\Users::where('user_id', $v['customer_id'])->select('user_name', 'user_picture', 'nick_name')->first();

			if (!empty($res)) {
				$res = $res->toArray();
			}

			$waitMessage[$k]['user_name'] = !empty($res['nick_name']) ? $res['nick_name'] : $res['user_name'];
			$waitMessage[$k]['avatar'] = self::format_pic($res['user_picture']);
		}

		if (empty($waitMessage)) {
			$waitMessage[0] = array('id' => '', 'message' => '', 'goods_id' => '', 'store_id' => '', 'user_name' => '', 'avatar' => '');
		}

		return array('waitMessage' => $waitMessage, 'waitMessageDataList' => $waitMessageDataList, 'total' => $total);
	}

	static public function userInfo($uid)
	{
		$res = \App\Models\Users::where('user_id', $uid)->select('user_name', 'user_picture', 'nick_name')->first();

		if (!empty($res)) {
			$res = $res->toArray();
			$res['avatar'] = self::format_pic($res['user_picture']);
			$res['user_name'] = !empty($res['nick_name']) ? $res['nick_name'] : $res['user_name'];
		}
		else {
			$res['user_name'] = '';
			$res['avatar'] = '';
		}

		return $res;
	}

	static public function getChatLog($service)
	{
		$messageList = \App\Models\ImDialog::select('id', 'customer_id', 'services_id', 'origin', 'goods_id', 'store_id', 'status')->where('services_id', $service['id'])->orderby('start_time', 'DESC')->get()->toArray();
		$temp = array();

		foreach ($messageList as $k => $v) {
			if (in_array($v['customer_id'], $temp)) {
				unset($messageList[$k]);
				continue;
			}

			$temp[] = $v['customer_id'];
		}

		$rootPath = rtrim(dirname(__ROOT__), '/');

		foreach ($messageList as $k => $v) {
			$where = '((from_user_id = ' . $v['services_id'] . ' AND to_user_id = ' . $v['customer_id'] . ') OR (from_user_id = ' . $v['customer_id'] . ' AND to_user_id = ' . $v['services_id'] . '))';
			$res = M('im_message')->where($where)->order('add_time DESC')->field('message, add_time, user_type, status')->find();
			$messageList[$k]['message'] = htmlspecialchars_decode($res['message']);
			$messageList[$k]['add_time'] = date('Y-m-d H:i:s', $res['add_time']);
			$messageList[$k]['origin'] = $v['origin'] == 1 ? 'PC' : 'H5';
			$messageList[$k]['user_type'] = $res['user_type'];
			$messageList[$k]['status'] = $v['status'] == 1 ? '未结束' : '结束';
			$messageList[$k]['goods']['goods_name'] = '';
			$messageList[$k]['goods']['shop_price'] = '';
			$messageList[$k]['goods']['goods_thumb'] = '';
			$res = \App\Models\ImMessage::where('dialog_id', $v['id'])->where('status', 1)->select('message')->orderby('add_time', 'DESC')->get()->toArray();

			if (!empty($res)) {
				$temp = array();

				foreach ($res as $msg) {
					$temp[] = htmlspecialchars_decode($msg['message']);
				}

				$messageList[$k]['message'] = $temp;
				$messageList[$k]['message_sum'] = count($temp);
			}

			if (0 < $messageList[$k]['goods_id']) {
				$res = \App\Models\Goods::where('goods_id', $v['goods_id'])->select('goods_name', 'goods_thumb', 'shop_price')->first();

				if (!empty($res)) {
					$res = $res->toArray();
					$messageList[$k]['goods']['goods_id'] = $v['goods_id'];
					$messageList[$k]['goods']['goods_name'] = $res['goods_name'];
					$messageList[$k]['goods']['shop_price'] = '￥' . $res['shop_price'];
					$messageList[$k]['goods']['url'] = $rootPath . '/goods.php?id=' . $v['goods_id'];
					$messageList[$k]['goods']['goods_thumb'] = self::format_goods_pic($res['goods_thumb']);
				}
			}

			$res = \App\Models\Users::where('user_id', $v['customer_id'])->select('user_name', 'user_picture', 'nick_name')->first();

			if (!empty($res)) {
				$res = $res->toArray();
				$messageList[$k]['user_name'] = !empty($res['nick_name']) ? $res['nick_name'] : $res['user_name'];
				$messageList[$k]['user_picture'] = self::format_pic($res['user_picture']);
			}

			if (empty($res['user_name'])) {
				unset($messageList[$k]);
			}
		}

		if (empty($messageList)) {
			$messageList[0] = array('id' => '', 'customer_id' => '', 'services_id' => '', 'origin' => '', 'goods_id' => '', 'store_id' => '', 'message' => '', 'add_time' => '', 'user_name' => '', 'user_picture' => '');
		}

		return $messageList;
	}

	static public function changeMessageStatus($serviceId, $customId)
	{
		\App\Models\ImMessage::where(array('from_user_id' => $serviceId, 'to_user_id' => $customId))->orWhere(array('to_user_id' => $serviceId, 'from_user_id' => $customId))->update(array('status' => 0));
	}

	static public function getHistory($uid, $tid, $keyword = '', $time = '', $page = 1, $size = 20)
	{
		$start = ($page - 1) * $size;
		$where = '((from_user_id = ' . $uid . ' AND to_user_id = ' . $tid . ') OR (from_user_id = ' . $tid . ' AND to_user_id = ' . $uid . '))';

		if (!empty($keyword)) {
			$where .= ' AND (message like \'%' . $keyword . '%\')';
		}

		if (!empty($time)) {
			$nowtime = strtotime($time);
			$tomotime = $nowtime + 3600 * 24;
			$where .= ' AND (add_time > ' . $nowtime . ' AND add_time < ' . $tomotime . ')';
		}

		$count = $model = M('im_message')->where($where)->count();
		$list = M('im_message')->where($where)->order('add_time DESC, id DESC')->field('id, message, add_time, from_user_id, user_type')->limit($start, $size);
		$list = $list->select();

		foreach ($list as $k => $v) {
			if ($v['user_type'] == 1) {
				$res = \App\Models\ImService::where('id', $v['from_user_id'])->pluck('nick_name')->toArray();
				$list[$k]['from_user_name'] = $res[0];
				$list[$k]['from_user_id'] = $v['from_user_id'];
			}
			else if ($v['user_type'] == 2) {
				$res = \App\Models\Users::where('user_id', $v['from_user_id'])->select('user_name', 'nick_name')->first();

				if ($res) {
					$users = $res->toArray();
				}

				$list[$k]['from_user_name'] = !empty($users) && !empty($users['nick_name']) ? $users['nick_name'] : $users['user_name'];
				$list[$k]['from_user_id'] = $v['from_user_id'];
			}

			$list[$k]['message'] = htmlspecialchars_decode($v['message']);
			$list[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
		}

		return array('list' => $list, 'total' => ceil($count / $size));
	}

	static public function getSearchHistory($mid)
	{
		$message = \App\Models\ImMessage::where('id', $mid)->select('from_user_id', 'to_user_id')->first()->toArray();
		$where = ' id < ' . $mid . ' AND ((from_user_id = ' . $message['from_user_id'] . ' AND to_user_id = ' . $message['to_user_id'] . ') OR (from_user_id = ' . $message['to_user_id'] . ' AND to_user_id = ' . $message['from_user_id'] . '))';
		$list = M('im_message')->where($where)->select();

		foreach ($list as $k => $v) {
			if ($v['user_type'] == 1) {
				$res = \App\Models\ImService::where('id', $v['from_user_id'])->pluck('nick_name')->toArray();
				$list[$k]['from_user_name'] = $res[0];
				$list[$k]['from_user_id'] = $v['from_user_id'];
			}
			else if ($v['user_type'] == 2) {
				$res = \App\Models\Users::where('user_id', $v['from_user_id'])->select('user_name', 'nick_name')->first();

				if ($res) {
					$users = $res->toArray();
				}

				$list[$k]['from_user_name'] = !empty($users) && !empty($users['nick_name']) ? $users['nick_name'] : $users['user_name'];
				$list[$k]['from_user_id'] = $v['from_user_id'];
			}

			$list[$k]['message'] = htmlspecialchars_decode($v['message']);
			$list[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);

			if ($mid == $v['id']) {
				$list[$k]['current'] = 1;
			}
		}

		return array('list' => $list);
	}

	static public function getReply($id, $type)
	{
		$reply = \App\Models\ImConfigure::where('ser_id', $id)->where('type', $type)->select('id', 'type', 'is_on', 'content');

		if ($type == 1) {
			$reply = $reply->get();
		}
		else {
			if ($type == 2 || $type == 3) {
				$reply = $reply->first();
			}
		}

		if ($reply) {
			return $reply->toArray();
		}
	}

	static public function isDialog($data)
	{
		$dialog = \App\Models\ImDialog::where('customer_id', $data['customer_id'])->where('services_id', $data['services_id'])->where('goods_id', $data['goods_id'])->where('store_id', $data['store_id'])->orderby('id', 'DESC')->where('status', 1)->limit(1)->get();
		$dialog = $dialog[0];

		if (!empty($dialog)) {
			$id = $dialog->id;
			$dialog->end_time = time();
			$dialog->save();
			return $id;
		}

		return false;
	}

	static public function addDialog($data)
	{
		$dialog = new \App\Models\ImDialog();
		$dialog->customer_id = $data['customer_id'];
		$dialog->services_id = $data['services_id'];
		$dialog->goods_id = $data['goods_id'];
		$dialog->store_id = $data['store_id'];
		$dialog->start_time = $data['start_time'];
		$dialog->origin = $data['origin'];
		$dialog->save();
		return $dialog->id;
	}

	static public function getRecentDialog($fid, $cid)
	{
		$dialog = \App\Models\ImDialog::where('customer_id', $cid)->where('services_id', $fid)->orderby('id', 'DESC')->limit(1)->get();
		$dialog = $dialog[0];

		if ($dialog) {
			return $dialog->toArray();
		}
	}

	static public function updateDialog($cusId, $serId)
	{
		\App\Models\ImMessage::where('from_user_id', $cusId)->where('to_user_id', '')->where('user_type', 2)->update(array('to_user_id' => $serId, 'status' => 0));
		$dialog = \App\Models\ImDialog::where('customer_id', $cusId)->where('services_id', 0)->get();

		foreach ($dialog as $item) {
			$item->services_id = $serId;
			$item->end_time = time();
			$item->save();
		}
	}

	static public function updateNewDialog($cusId, $serId)
	{
		$ImMessage = \App\Models\ImMessage::where('from_user_id', $cusId)->where('user_type', 2)->orderby('add_time', 'DESC')->limit(1)->get();
		$ImMessage = $ImMessage[0];

		if (!empty($ImMessage)) {
			$ImMessage->to_user_id = $serId;
			$ImMessage->status = 0;
			$ImMessage->save();
		}

		$dialog = \App\Models\ImDialog::where('customer_id', $cusId)->get();

		foreach ($dialog as $item) {
			$item->services_id = $serId;
			$item->end_time = time();
			$item->save();
		}
	}

	static public function closeWindow($uid, $tid)
	{
		\App\Models\ImDialog::where('customer_id', $tid)->where('services_id', $uid)->orderby('start_time', 'DESC')->update(array('end_time' => time(), 'status' => 2));
	}

	static public function closeOldWindow($expire)
	{
		$dialog = \App\Models\ImDialog::where('end_time', '<', time() - $expire)->where('status', 1)->where('end_time', '>', 0)->where('services_id', '<>', 0)->distinct()->orderby('start_time', 'DESC')->get();
		$temp = array();

		foreach ($dialog as $k => $v) {
			$v->status = 2;
			$v->end_time = time();
			$v->save();

			if (isset($temp[$v->customer_id])) {
				continue;
			}

			$temp[$v->customer_id] = array('cid' => $v->customer_id, 'ssid' => $v->services_id, 'sid' => $v->store_id);
		}

		return $temp;
	}

	static public function getGoods($gid)
	{
		$goods = \App\Models\Goods::select('goods_id', 'goods_name', 'goods_thumb', 'shop_price')->where('goods_id', $gid)->first()->toArray();
		$goods['goods_thumb'] = self::format_goods_pic($goods['goods_thumb']);
		return $goods;
	}

	static public function getStoreInfo($sid)
	{
		$store = \App\Models\SellerShopinfo::select('shop_name', 'logo_thumb')->where('ru_id', $sid)->first();

		if (!empty($store)) {
			$store = $store->toArray();

			if (empty($store['logo_thumb'])) {
				$store['logo_thumb'] = self::format_pic('', 'service');
			}

			return $store;
		}

		return array('shop_name' => '', 'logo_thumb' => self::format_pic('', 'service'));
	}

	static public function getServiceReply($serviceId)
	{
		$conf = \App\Models\ImConfigure::where('ser_id', $serviceId)->where('is_on', 1)->where('type', 2)->first();

		if (!empty($conf)) {
			return $conf->content;
		}
	}

	static public function format_goods_pic($pic)
	{
		$rootPath = rtrim(dirname(__ROOT__), '/');

		if (empty($pic)) {
			return rtrim(__ROOT__, '/') . '/public/img/no_image.jpg';
		}

		if (strpos($pic, 'http') !== false) {
			return $pic;
		}

		return $rootPath . '/' . $pic;
	}

	static public function format_pic($pic, $who = '')
	{
		$rootPath = rtrim(dirname(__ROOT__), '/');

		if (strpos($pic, 'http') !== false) {
			return $pic;
		}

		if (empty($pic)) {
			if ($who == 'service') {
				$pic = 'service.png';
			}
			else {
				$pic = 'avatar.png';
			}

			return __ROOT__ . '/public/assets/chat/images/' . $pic;
		}

		return $rootPath . '/' . $pic;
	}

	static public function upload($savePath = '', $hasOne = false, $size = 2, $thumb = false)
	{
		$config = array(
			'maxSize'  => $size * 1024 * 1024,
			'rootPath' => dirname(ROOT_PATH) . '/',
			'savePath' => rtrim($savePath, '/') . '/',
			'exts'     => array('jpg', 'gif', 'png', 'jpeg', 'bmp', 'mp3', 'amr', 'mp4'),
			'autoSub'  => false,
			'thumb'    => $thumb
			);
		$up = new \Think\Upload($config);
		$result = $up->upload();

		if (!$result) {
			return array('error' => 1, 'message' => $up->getError());
		}
		else {
			$res = array('error' => 0);

			if ($hasOne) {
				$info = reset($result);
				$res['url'] = $info['savepath'] . $info['savename'];
			}
			else {
				foreach ($result as $k => $v) {
					$result[$k]['url'] = $v['savepath'] . $v['savename'];
				}

				$res['url'] = $result;
			}

			return $res;
		}
	}

	static public function format_msg($text)
	{
		$text = htmlspecialchars_decode($text);
		$reg = '/http(s?):\\/\\/(?:[A-za-z0-9-]+\\.)+[A-za-z]{2,4}(:\\d+)?(?:[\\/\\?#][\\/=\\?%\\-&~`@[\\]\\\':+!\\.#\\w]*)?+/i';
		preg_match_all($reg, $text, $links);

		if (!empty($links[0])) {
			foreach ($links[0] as $url) {
				if (preg_match('/^(http|https)/is', $url)) {
					if (preg_match('/(goods|goods.php)/i', $url)) {
						$param = self::get_url_queryl($url);
						if (isset($param) && !empty($param['id'])) {
							$goods_id = $param['id'];
						}

						$goods_info = array();

						if (!empty($goods_id)) {
							$res = \App\Models\Goods::where('goods_id', $goods_id)->select('goods_name', 'goods_thumb', 'shop_price')->first();

							if (!empty($res)) {
								$goods_info = $res->toArray();
							}
						}

						if (!empty($goods_info)) {
							$shop_price = '￥' . $goods_info['shop_price'];
							$goods_img = self::format_pic($goods_info['goods_thumb']);
							$goods_name = sub_str($goods_info['goods_name'], 50);
							$replace = '<div class="new_message_list" >' . '<img src="' . $goods_img . '" >' . '<a href="' . $url . '" target="_blank" >' . '<div class="left_goods_info">' . '<h4>' . $goods_name . '</h4>' . '<span>' . $shop_price . '</span>' . '</div>' . '</a>' . '</div>';
						}
						else {
							$replace = '<a href="' . $url . '" target="_blank">' . $url . '</a>';
						}
					}
					else if (preg_match('/(.jpg|.png|.gif)/i', $url)) {
						$replace = $url;
					}
					else {
						$replace = '<a href="' . $url . '" target="_blank">' . $url . '</a>';
					}
				}

				$text = str_replace($url, $replace, $text);
			}
		}

		return $text;
	}

	static protected function get_url_queryl($url = '')
	{
		$info = parse_url($url);

		if (false == strpos($url, '?')) {
			if (isset($info['path'])) {
				parse_str($info['path'], $params);
			}
		}
		else if (isset($info['query'])) {
			parse_str($info['query'], $params);
		}

		return $params;
	}
}

?>
