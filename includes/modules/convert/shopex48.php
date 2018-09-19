<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class shopex48
{
	public $sdb;
	public $sprefix;
	public $sroot;
	public $troot;
	public $tdocroot;
	public $scharset;
	public $tcharset;

	public function shopex48(&$sdb, $sprefix, $sroot, $scharset = 'UTF8')
	{
		$this->sdb = $sdb;
		$this->sprefix = $sprefix;
		$this->sroot = $sroot;
		$this->troot = str_replace('/includes/modules/convert', '', str_replace('\\', '/', dirname(__FILE__)));
		$this->tdocroot = str_replace('/' . ADMIN_PATH, '', dirname(PHP_SELF));
		$this->scharset = $scharset;

		if (EC_CHARSET == 'utf-8') {
			$tcharset = 'UTF8';
		}
		else if (EC_CHARSET == 'gbk') {
			$tcharset = 'GB2312';
		}

		$this->tcharset = $tcharset;
	}

	public function required_tables()
	{
		return array($this->sprefix . 'goods');
	}

	public function required_dirs()
	{
		return array('/images/goods/', '/images/brand/', '/images/link/');
	}

	public function next_step($step)
	{
		$steps = array('' => 'step_file', 'step_file' => 'step_cat', 'step_cat' => 'step_brand', 'step_brand' => 'step_goods', 'step_goods' => 'step_users', 'step_users' => 'step_article', 'step_article' => 'step_order', 'step_order' => 'step_config', 'step_config' => '');
		return $steps[$step];
	}

	public function process($step)
	{
		$func = str_replace('step', 'process', $step);
		return $this->$func();
	}

	public function process_file()
	{
		$from = $this->sroot . '/images/brand/';
		$to = $this->troot . '/data/brandlogo/';
		copy_dirs($from, $to);
		$to = $this->troot . '/images/goods/';
		$from = $this->sroot . '/images/goods/';
		copy_dirs($from, $to);
		$from = $this->sroot . '/images/link/';
		$to = $this->troot . '/data/afficheimg/';
		copy_dirs($from, $to);
		return true;
	}

	public function process_cat()
	{
		global $db;
		global $ecs;
		truncate_table('category');
		truncate_table('goods_type');
		$sql = 'SELECT * FROM ' . $this->sprefix . 'goods_cat';
		$res = $this->sdb->query($sql);

		while ($row = $this->sdb->fetchRow($res)) {
			$cat = array();
			$cat['cat_id'] = $row['cat_id'];
			$cat['cat_name'] = $row['cat_name'];
			$cat['parent_id'] = $row['parent_id'];
			$cat['sort_order'] = $row['p_order'];

			if (!$db->autoExecute($ecs->table('category'), $cat, 'INSERT', '', 'SILENT')) {
			}
		}

		$sql = 'SELECT * FROM ' . $this->sprefix . 'goods_type';
		$res = $this->sdb->query($sql);

		while ($row = $this->sdb->fetchRow($res)) {
			$type = array();
			$type['cat_id'] = $row['prop_cat_id'];
			$type['cat_name'] = $row['name'];
			$type['enabled'] = '1';

			if (!$db->autoExecute($ecs->table('goods_type'), $type, 'INSERT', '', 'SILENT')) {
			}
		}

		return true;
	}

	public function process_brand()
	{
		global $db;
		global $ecs;
		truncate_table('brand');
		$sql = 'SELECT * FROM ' . $this->sprefix . 'brand';
		$res = $this->sdb->query($sql);

		while ($row = $this->sdb->fetchRow($res)) {
			$brand_logo = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['brand_logo']));
			$logoarr = explode('|', $brand_logo);

			if (strpos($logoarr[0], 'http') === 0) {
				$brand_url = $logoarr[0];
			}
			else {
				$logourl = explode('/', $logoarr[0], 3);
				$brand_url = $logourl[2];
			}

			$brand = array('brand_name' => $row['brand_name'], 'brand_desc' => '', 'site_url' => ecs_iconv($this->scharset, $this->tcharset, addslashes($row['brand_url'])), 'brand_logo' => $brand_url);

			if (!$db->autoExecute($ecs->table('brand'), $brand, 'INSERT', '', 'SILENT')) {
			}
		}

		return true;
	}

	public function process_goods()
	{
		global $db;
		global $ecs;
		truncate_table('goods');
		truncate_table('goods_cat');
		truncate_table('goods_attr');
		truncate_table('goods_gallery');
		truncate_table('link_goods');
		truncate_table('group_goods');
		$brand_list = array();
		$sql = 'SELECT brand_id, brand_name FROM ' . $ecs->table('brand');
		$res = $db->query($sql);

		while ($row = $db->fetchRow($res)) {
			$brand_list[$row['brand_name']] = $row['brand_id'];
		}

		$cat_type_list = array();
		$sql = 'SELECT cat_id, supplier_cat_id FROM ' . $this->sprefix . 'goods_cat';
		$res = $this->sdb->query($sql);

		while ($row = $this->sdb->fetchRow($res)) {
			$cat_type_list[$row['cat_id']] = $row['supplier_cat_id'];
		}

		$sql = 'SELECT * FROM ' . $this->sprefix . 'goods';
		$res = $this->sdb->query($sql);

		while ($row = $this->sdb->fetchRow($res)) {
			$goods = array();
			$goods['goods_id'] = $row['goods_id'];
			$goods['cat_id'] = $row['cat_id'];
			$goods['goods_sn'] = $row['bn'];
			$goods['goods_name'] = $row['name'];
			$goods['brand_id'] = trim($row['brand']) == '' ? '0' : $brand_list[ecs_iconv($this->scharset, $this->tcharset, addslashes($row['brand']))];
			$goods['goods_number'] = $row['store'];
			$goods['goods_weight'] = $row['weight'];
			$goods['market_price'] = $row['mktprice'];
			$goods['shop_price'] = $row['price'];
			$goods['promote_price'] = $row['name'];
			$goods['goods_brief'] = $row['brief'];
			$goods['goods_desc'] = $row['intro'];
			$goods['add_time'] = $row['uptime'];
			$big_pic = $row['big_pic'];
			$big_pic_arr = explode('|', $big_pic);
			$small_pic = $row['small_pic'];
			$small_pic_arr = explode('|', $small_pic);
			$goods['goods_img'] = $small_pic_arr[0];
			$goods['goods_thumb'] = $small_pic_arr[0];
			$goods['original_img'] = $small_pic_arr[0];
			$goods['last_update'] = gmtime();

			if (!$db->autoExecute($ecs->table('goods'), $goods, 'INSERT', '', 'SILENT')) {
			}

			$sql2 = 'SELECT * FROM ' . $this->sprefix . 'gimages';
			$result = $this->sdb->query($sql2);

			while ($row2 = $this->sdb->fetchRow($result)) {
				$goods_gallery = array();
				$goods_gallery['goods_id'] = $row2['goods_id'];
				$big_pic = $row2['big'];
				$big_pic_arr = explode('|', $big_pic);
				$goods_gallery['img_original'] = $big_pic_arr[0];
				$small_pic = $row2['small'];
				$small_pic_arr = explode('|', $small_pic);
				$goods_gallery['thumb_url'] = $small_pic_arr[0];
				$goods_gallery['img_url'] = $goods_gallery['thumb_url'];

				if (!$db->autoExecute($ecs->table('goods_gallery'), $goods_gallery, 'INSERT', '', 'SILENT')) {
				}
			}
		}

		return true;
	}

	public function process_users()
	{
		global $db;
		global $ecs;
		truncate_table('user_rank');
		truncate_table('users');
		truncate_table('user_address');
		truncate_table('user_bonus');
		truncate_table('member_price');
		truncate_table('user_account');
		$sql = 'SELECT * FROM ' . $this->sprefix . 'member_lv order by point desc';
		$res = $this->sdb->query($sql);
		$max_points = 50000;

		while ($row = $this->sdb->fetchRow($res)) {
			$user_rank = array();
			$user_rank['rank_id'] = $row['member'];
			$user_rank['rank_name'] = $row['name'];
			$user_rank['min_points'] = $row['point'];
			$user_rank['max_points'] = $max_points;
			$user_rank['discount'] = round($row['dis_count'] * 100);
			$user_rank['show_price'] = '1';
			$user_rank['special_rank'] = '0';

			if (!$db->autoExecute($ecs->table('user_rank'), $user_rank, 'INSERT', '', 'SILENT')) {
			}

			$max_points = $row['point'] - 1;
		}

		$sql = 'SELECT * FROM ' . $this->sprefix . 'members';
		$res = $this->sdb->query($sql);

		while ($row = $this->sdb->fetchRow($res)) {
			$user = array();
			$user['user_id'] = $row['member_id'];
			$user['email'] = $row['email'];
			$user['user_name'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['uname']));
			$user['password'] = $row['password'];
			$user['question'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['pw_question']));
			$user['answer'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['pw_answer']));
			$user['sex'] = $row['sex'];

			if (!empty($row['birthday'])) {
				$birthday = strtotime($row['birthday']);
				if (($birthday != -1) && ($birthday !== false)) {
					$user['birthday'] = date('Y-m-d', $birthday);
				}
			}

			$user['user_money'] = $row['advance'];
			$user['pay_points'] = $row['point'];
			$user['rank_points'] = $row['point'];
			$user['reg_time'] = $row['regtime'];
			$user['last_login'] = $row['regtime'];
			$user['last_ip'] = $row['reg_ip'];
			$user['visit_count'] = '1';
			$user['user_rank'] = '0';

			if (!$db->autoExecute($ecs->table('users'), $user, 'INSERT', '', 'SILENT')) {
			}
		}

		$sql = 'SELECT * FROM ' . $this->sprefix . 'member_addrs';
		$res = $this->sdb->query($sql);

		while ($row = $this->sdb->fetchRow($res)) {
			$address = array();
			$address['address_id'] = $row['addr_id'];
			$address['address_name'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['name']));
			$address['user_id'] = $row['member_id'];
			$address['consignee'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['name']));
			$address['address'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['addr']));
			$address['zipcode'] = $row['zip'];
			$address['tel'] = $row['tel'];
			$address['mobile'] = $row['mobile'];
			$address['country'] = $row['country'];
			$address['province'] = $row['province'];
			$address['city'] = $row['city'];

			if (!$db->autoExecute($ecs->table('user_address'), $address, 'INSERT', '', 'SILENT')) {
			}
		}

		$temp_arr = array();
		$sql = 'SELECT * FROM ' . $this->sprefix . 'goods_lv_price';
		$res = $this->sdb->query($sql);

		while ($row = $this->sdb->fetchRow($res)) {
			if ((0 < $row['goods_id']) && (0 < $row['level_id']) && !isset($temp_arr[$row['goods_id']][$row['level_id']])) {
				$temp_arr[$row['goods_id']][$row['level_id']] = true;
				$member_price = array();
				$member_price['goods_id'] = $row['goods_id'];
				$member_price['user_rank'] = $row['level_id'];
				$member_price['user_price'] = $row['price'];

				if (!$db->autoExecute($ecs->table('member_price'), $member_price, 'INSERT', '', 'SILENT')) {
				}
			}
		}

		unset($temp_arr);
		$sql = 'SELECT * FROM ' . $this->sprefix . 'advance_logs';
		$res = $this->sdb->query($sql);

		while ($row = $this->sdb->fetchRow($res)) {
			$user_account = array();
			$user_account['user_id'] = $row['member_id'];
			$user_account['admin_user'] = $row['memo'];
			$user_account['amount'] = $row['money'];
			$user_account['add_time'] = $row['mtime'];
			$user_account['paid_time'] = $row['mtime'];
			$user_account['admin_note'] = $row['message'];
			$user_account['payment'] = $row['paymethod'];
			$user_account['process_type'] = 0 <= $row['money'] ? SURPLUS_SAVE : SURPLUS_RETURN;
			$user_account['is_paid'] = '1';

			if (!$db->autoExecute($ecs->table('user_account'), $user_account, 'INSERT', '', 'SILENT')) {
			}
		}

		return true;
	}

	public function process_article()
	{
		global $db;
		global $ecs;
		truncate_table('friend_link');
		$sql = 'SELECT * FROM ' . $this->sprefix . 'articles';
		$res = $this->sdb->query($sql);

		while ($row = $this->sdb->fetchRow($res)) {
			$article = array();
			$article['article_id'] = $row['article_id'];
			$article['cat_id'] = $row['node_id'];
			$article['title'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['title']));
			$article['content'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['content']));
			$article['content'] = str_replace('pictures/newsimg/', 'images/upload/Image/', $article['content']);
			$article['article_type'] = '0';
			$article['is_open'] = $row['ifpub'];
			$article['add_time'] = $row['uptime'];

			if (!$db->autoExecute($ecs->table('article'), $article, 'INSERT', '', 'SILENT')) {
			}
		}

		$sql = 'SELECT * FROM ' . $this->sprefix . 'link';
		$res = $this->sdb->query($sql);

		while ($row = $this->sdb->fetchRow($res)) {
			$link = array();
			$link['link_id'] = $row['link_id'];
			$link['link_name'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['link_name']));
			$link['link_url'] = $row['href'];
			$link['show_order'] = '0';
			$link_logo = $row['image_url'];
			$logoarr = explode('|', $link_logo);
			$logourl = explode('/', $logoarr[0], 3);
			$link['link_logo'] = 'data/afficheimg/' . $logourl[2];

			if (!$db->autoExecute($ecs->table('friend_link'), $link, 'INSERT', '', 'SILENT')) {
			}
		}

		return true;
	}

	public function process_order()
	{
		global $db;
		global $ecs;
		truncate_table('order_info');
		truncate_table('order_goods');
		truncate_table('order_action');
		$sql = 'SELECT o.* FROM ' . $this->sprefix . 'orders AS o ';
		$res = $this->sdb->query($sql);

		while ($row = $this->sdb->fetchRow($res)) {
			$order = array();
			$order['order_sn'] = $row['order_id'];
			$order['user_id'] = $row['member_id'];
			$order['add_time'] = $row['createtime'];
			$order['consignee'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['consignee']));
			$order['address'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['ship_addr']));
			$order['zipcode'] = $row['ship_zip'];
			$order['tel'] = $row['ship_tel'];
			$order['mobile'] = $row['ship_mobile'];
			$order['email'] = $row['ship_email'];
			$order['postscript'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['memo']));
			$order['shipping_name'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['ship_name']));
			$order['pay_name'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['shipping']));
			$order['inv_payee'] = ecs_iconv($this->scharset, $this->tcharset, addslashes($row['tax_company']));
			$order['goods_amount'] = $row['total_amount'];
			$order['shipping_fee'] = $row['cost_freight'];
			$order['order_amount'] = $row['final_amount'];
			$order['pay_time'] = $row['paytime'];
			$order['shipping_time'] = $row['acttime'];

			if ($row['ordstate'] == '0') {
				$order['order_status'] = OS_UNCONFIRMED;
				$order['shipping_status'] = SS_UNSHIPPED;
			}
			else if ($row['ordstate'] == '1') {
				$order['order_status'] = OS_CONFIRMED;
				$order['shipping_status'] = SS_UNSHIPPED;
			}
			else if ($row['ordstate'] == '9') {
				$order['order_status'] = OS_INVALID;
				$order['shipping_status'] = SS_UNSHIPPED;
			}
			else {
				$order['order_status'] = OS_CONFIRMED;
				$order['shipping_status'] = SS_SHIPPED;
			}

			if ($row['pay_status'] == '1') {
				$order['pay_status'] = PS_PAYED;
			}
			else {
				$order['pay_status'] = PS_UNPAYED;
			}

			if ($row['userrecsts'] == '1') {
				if ($row['recsts'] == '1') {
					if ($order['shipping_status'] == SS_SHIPPED) {
						$order['shipping_status'] = SS_RECEIVED;
					}
				}
				else if ($row['recsts'] == '2') {
					$order['order_status'] = OS_CANCELED;
					$order['pay_status'] = PS_UNPAYED;
					$order['shipping_status'] = SS_UNSHIPPED;
				}
			}

			if (!$db->autoExecute($ecs->table('order_info'), $order, 'INSERT', '', 'SILENT')) {
			}
		}

		return true;
	}

	public function process_config()
	{
		global $ecs;
		global $db;
		$sql = 'SELECT * FROM ' . $this->sprefix . 'settings';
		$row = $this->sdb->getRow($sql);
		$store = $row['store'];
		$store_arr = unserialize($store);
		$config = array();
		$config['shop_address'] = $row['store'];
		$config['service_phone'] = $store_arr[2];

		foreach ($config as $code => $value) {
			$sql = 'UPDATE ' . $ecs->table('shop_config') . ' SET ' . 'value = \'' . $value . '\' ' . 'WHERE code = \'' . $code . '\' LIMIT 1';

			if (!$db->query($sql, 'SILENT')) {
			}
		}

		return true;
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

if (isset($set_modules) && ($set_modules == true)) {
	$i = (isset($modules) ? count($modules) : 0);
	$modules[$i]['code'] = basename(__FILE__, '.php');
	$modules[$i]['desc'] = 'shopex48_desc';
	$modules[$i]['author'] = 'ECSHOP R&D TEAM';
	return NULL;
}

?>
