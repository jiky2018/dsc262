<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Goods\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	protected $user_id = 0;
	protected $goods_id = 0;
	protected $region_id = 0;
	protected $area_info = array();

	public function __construct()
	{
		parent::__construct();
		L(require LANG_PATH . C('shop.lang') . '/goods.php');
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		$this->size = 10;
		$this->goods_id = I('id', 0, 'intval');

		if ($this->goods_id == 0) {
			ecs_header("Location: ./\n");
			exit();
		}

		$this->user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
		$this->assign('goods_id', $this->goods_id);
		$this->init_params();
		$this->assign('custom', C(custom));
	}

	public function actionIndex()
	{
		$pid = I('request.pid', 0, 'intval');

		if (!empty($_SESSION['user_id'])) {
			$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
		}
		else {
			$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
		}

		if ($this->area_info['region_id'] == null) {
			$this->area_info['region_id'] = 0;
		}

		$goods = get_goods_info($this->goods_id, $this->region_id, $this->area_info['region_id']);
		if ($goods === false || !isset($goods['goods_name'])) {
			ecs_header("Location: ./\n");
			exit();
		}

		if (is_dir(APP_DRP_PATH)) {
			$isdrp = $this->model->table('drp_config')->field('value')->where(array('code' => 'isdrp'))->find();
			$sql = 'SELECT id FROM {pre}drp_shop WHERE audit=1 AND status=1 AND user_id=' . $this->user_id;
			$drp = $this->db->getOne($sql);
			$this->assign('drp', $drp);
			$this->assign('isdrp', $isdrp['value']);
			if (0 < $goods['dis_commission'] && $goods['is_distribution'] == 1 && !empty($this->user_id) && $_GET['d'] != $this->user_id && 0 < $drp) {
				$this->redirect('goods/index/index', array('id' => $this->goods_id, 'd' => $this->user_id));
			}
		}

		$share_type = 0;
		if (isset($isdrp) && $isdrp['value'] == 1 && isset($drp) && 0 < $drp && $goods['is_distribution'] == 1) {
			$share_type = 1;
		}

		$this->assign('share_type', $share_type);
		$is_reality = get_goods_extends($this->goods_id);
		$this->assign('is_reality', $is_reality);
		$this->assign('id', $this->goods_id);
		$this->assign('type', 0);
		$this->assign('cfg', C('shop'));
		$this->assign('promotion', get_promotion_info($this->goods_id, $goods['user_id']));
		$this->assign('promotion_info', get_promotion_info('', $goods['user_id']));
		$start_date = $goods['xiangou_start_date'];
		$end_date = $goods['xiangou_end_date'];
		$nowTime = gmtime();
		if ($start_date < $nowTime && $nowTime < $end_date) {
			$xiangou = 1;
		}
		else {
			$xiangou = 0;
		}

		$order_goods = get_for_purchasing_goods($start_date, $end_date, $this->goods_id, $this->user_id);
		$this->assign('xiangou', $xiangou);
		$this->assign('orderG_number', $order_goods['goods_number']);
		$shop_info = get_merchants_shop_info('merchants_steps_fields', $goods['user_id']);
		$adress = get_license_comp_adress($shop_info['license_comp_adress']);
		$this->assign('shop_info', $shop_info);
		$this->assign('adress', $adress);
		$volume_price_list = get_volume_price_list($goods['goods_id'], '1');
		$this->assign('volume_price_list', $volume_price_list);
		$province_list = get_warehouse_province();
		$this->assign('province_list', $province_list);
		$city_list = get_region_city_county($this->province_id);

		if ($city_list) {
			foreach ($city_list as $k => $v) {
				$city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
			}
		}

		$this->assign('city_list', $city_list);
		$district_list = get_region_city_county($this->city_id);
		$this->assign('district_list', $district_list);
		$warehouse_list = get_warehouse_list_goods();
		$this->assign('warehouse_list', $warehouse_list);
		$warehouse_name = get_warehouse_name_id($this->region_id);
		$this->assign('warehouse_name', $warehouse_name);
		$this->assign('region_id', $this->region_id);
		$this->assign('user_id', $_SESSION['user_id']);
		$this->assign('shop_price_type', $goods['model_price']);
		$this->assign('area_id', $this->area_info['region_id']);
		$shop_price = $goods['shop_price'] ? $goods['shop_price'] : 0;
		$linked_goods = get_linked_goods($this->goods_id, $this->region_id, $this->area_info['region_id']);
		$history_goods = get_history_goods($this->goods_id, $this->region_id, $this->area_info['region_id']);
		$goods['goods_style_name'] = add_style($goods['goods_name'], $goods['goods_name_style']);

		if (0 < $goods['bonus_type_id']) {
			$time = gmtime();
			$sql = 'SELECT type_money FROM {pre}bonus_type' . (' WHERE type_id = \'' . $goods['bonus_type_id'] . '\' ') . ' AND send_type = \'' . SEND_BY_GOODS . '\' ' . (' AND send_start_date <= \'' . $time . '\'') . (' AND send_end_date >= \'' . $time . '\'');
			$goods['bonus_money'] = floatval($this->db->getOne($sql));

			if (0 < $goods['bonus_money']) {
				$goods['bonus_money'] = price_format($goods['bonus_money']);
			}
		}

		$goods['store_count'] = 0;
		$sql = 'SELECT COUNT(*) FROM {pre}offline_store AS o LEFT JOIN {pre}store_goods AS s ON o.id = s.store_id WHERE s.goods_id = \'' . $this->goods_id . '\' AND o.is_confirm = 1 ';
		$store_goods = $this->db->getOne($sql);

		if (0 < $store_goods) {
			$goods['store_count'] = 1;
			$store_id = getStoreIdByGoodsId($this->goods_id);
			$store = dao('offline_store')->field('id, stores_name')->where(array('id' => $store_id))->find();
			$store['take_time'] = local_date('Y-m-d H:i:s', gmtime() + 3600 * 24);
			$store['store_mobile'] = dao('users')->where(array('user_id' => $_SESSION['user_id']))->getField('mobile_phone');
			$this->assign('store', $store);
		}

		if (!empty($goods['goods_video'])) {
			$goods['goods_video'] = get_image_path($goods['goods_video']);
		}

		$this->assign('goods', $goods);
		$this->assign('goods_id', $goods['goods_id']);
		$this->assign('promote_end_time', $goods['gmt_end_time']);
		$this->assign('categories', get_categories_tree($goods['cat_id']));
		$position = assign_ur_here($goods['cat_id'], $goods['goods_name']);
		$seo = get_seo_words('goods');

		foreach ($seo as $key => $value) {
			$seo[$key] = html_in(str_replace(array('{sitename}', '{key}', '{name}', '{description}'), array(C('shop.shop_name'), $goods['keywords'], $goods['goods_name'], $goods['goods_brief']), $value));
		}

		$page_title = !empty($seo['title']) ? $seo['title'] : $position['title'];
		$keywords = !empty($seo['keywords']) ? $seo['keywords'] : (!empty($goods['keywords']) ? $goods['keywords'] : C('shop.shop_keywords'));
		$description = !empty($seo['description']) ? $seo['description'] : (!empty($goods['goods_brief']) ? $goods['goods_brief'] : C('shop.shop_desc'));
		$share_data = array('title' => $page_title, 'desc' => $description, 'link' => '', 'img' => $goods['goods_img']);
		$this->assign('share_data', $this->get_wechat_share_content($share_data));
		$this->assign('page_title', $page_title);
		$this->assign('keywords', $keywords);
		$this->assign('description', $description);
		$properties = get_goods_properties($this->goods_id, $this->region_id, $this->area_info['region_id']);
		$this->assign('properties', $properties['pro']);
		$default_spe = '';

		if ($properties['spe']) {
			foreach ($properties['spe'] as $k => $v) {
				if ($v['attr_type'] == 1) {
					if (0 < $v['is_checked']) {
						foreach ($v['values'] as $key => $val) {
							$default_spe .= $val['checked'] ? $val['label'] . '、' : '';
						}
					}
					else {
						foreach ($v['values'] as $key => $val) {
							if ($key == 0) {
								$default_spe .= $val['label'] . '、';
							}
						}
					}
				}
			}
		}

		if (empty($properties['spe'])) {
			$add_type = 0;
		}
		else {
			$add_type = 1;
		}

		$this->assign('add_type', $add_type);
		$this->assign('default_spe', $default_spe);
		$this->assign('specification', $properties['spe']);
		$this->assign('attribute_linked', get_same_attribute_goods($properties));
		$this->assign('related_goods', $linked_goods);
		$this->assign('rank_prices', get_user_rank_prices($this->goods_id, $shop_price));
		$this->assign('pictures', get_goods_gallery($this->goods_id));
		$this->assign('bought_goods', get_also_bought($this->goods_id));
		$this->assign('goods_rank', get_goods_rank($this->goods_id));
		$fittings_list = get_goods_fittings(array($this->goods_id), $this->region_id, $this->area_info['region_id']);

		if (is_array($fittings_list)) {
			foreach ($fittings_list as $vo) {
				$fittings_index[$vo['group_id']] = $vo['group_id'];
			}
		}

		$this->assign('fittings', $fittings_list);
		$package_goods_list = get_package_goods_list($goods['goods_id']);
		$this->assign('package_goods_list', $package_goods_list);
		assign_dynamic('goods');
		$volume_price_list = get_volume_price_list($goods['goods_id'], '1');
		$this->assign('volume_price_list', $volume_price_list);
		$this->assign('sales_count', get_goods_sales($this->goods_id));
		$region = array(1, $this->province_id, $this->city_id, $this->district_id, $this->town_region_id);
		$shippingFee = goodsShippingFee($this->goods_id, $this->region_id, $this->area_info['region_id'], $region);
		$this->assign('shippingFee', $shippingFee);
		$this->assign('is_shipping', $goods['is_shipping']);

		if ($_SESSION['user_id']) {
			$where['user_id'] = $_SESSION['user_id'];
			$where['goods_id'] = $this->goods_id;
			$rs = $this->db->table('collect_goods')->where($where)->count();

			if (0 < $rs) {
				$this->assign('goods_collect', 1);
			}
		}

		$this->db->query('UPDATE ' . $this->ecs->table('goods') . (' SET click_count = click_count + 1 WHERE goods_id = \'' . $this->goods_id . '\''));

		if (!empty($_COOKIE['ECS']['history_goods'])) {
			$history = explode(',', $_COOKIE['ECS']['history_goods']);
			array_unshift($history, $this->goods_id . '_' . $nowTime);
			$history = array_unique($history);

			while (C('shop.history_number') < count($history)) {
				array_pop($history);
			}

			cookie('ECS[history_goods]', implode(',', $history));
		}
		else {
			cookie('ECS[history_goods]', $this->goods_id);
		}

		$this->assign('province_row', get_region_name($this->province_id));
		$this->assign('city_row', get_region_name($this->city_id));
		$this->assign('district_row', get_region_name($this->district_id));
		$this->assign('town_row', get_region_name($this->town_region_id));
		$goods_region['country'] = 1;
		$goods_region['province'] = $this->province_id;
		$goods_region['city'] = $this->city_id;
		$goods_region['district'] = $this->district_id;
		$goods_region['town_region_id'] = $this->town_region_id;
		$this->assign('goods_region', $goods_region);
		$date = array('shipping_code');
		$where = 'shipping_id = \'' . $goods['default_shipping'] . '\'';
		$shipping_code = get_table_date('shipping', $where, $date, 2);
		$cart_num = cart_number();
		$this->assign('cart_num', $cart_num);
		$area_position_list = get_goods_user_area_position($goods['user_id'], $this->city_id);
		$this->assign('area_position_list', $area_position_list);
		$mc_all = ments_count_all($this->goods_id);
		$mc_one = ments_count_rank_num($this->goods_id, 1);
		$mc_two = ments_count_rank_num($this->goods_id, 2);
		$mc_three = ments_count_rank_num($this->goods_id, 3);
		$mc_four = ments_count_rank_num($this->goods_id, 4);
		$mc_five = ments_count_rank_num($this->goods_id, 5);
		$comment_all = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);

		if (0 < $goods['user_id']) {
			$merchants_goods_comment = get_merchants_goods_comment($goods['user_id']);
			$merchants_goods_comment['commentRank'] = $this->font($merchants_goods_comment['cmt']['commentRank']['zconments']['goodReview']);
			$merchants_goods_comment['commentServer'] = $this->font($merchants_goods_comment['cmt']['commentServer']['zconments']['goodReview']);
			$merchants_goods_comment['commentDelivery'] = $this->font($merchants_goods_comment['cmt']['commentDelivery']['zconments']['goodReview']);
			$this->assign('merch_cmt', $merchants_goods_comment);
		}

		$this->assign('comment_all', $comment_all);
		$good_comment = get_good_comment($this->goods_id, 4, 1, 0, 1);
		$this->assign('good_comment', $good_comment);
		$sql = 'SELECT count(*) FROM ' . $this->ecs->table('collect_store') . ' WHERE ru_id = ' . $goods['user_id'];
		$collect_number = $this->db->getOne($sql);
		$this->assign('collect_number', $collect_number ? $collect_number : 0);
		$sql = 'select a.kf_im_switch, b.is_IM,a.ru_id,a.province, a.city, a.kf_type, a.kf_ww, a.kf_qq, a.meiqia, a.shop_name, a.kf_appkey,kf_secretkey from {pre}seller_shopinfo as a left join {pre}merchants_shop_information as b on a.ru_id=b.user_id where a.ru_id=\'' . $goods['user_id'] . '\' ';
		$basic_info = $this->db->getRow($sql);
		$info_ww = $basic_info['kf_ww'] ? explode("\r\n", $basic_info['kf_ww']) : '';
		$info_qq = $basic_info['kf_qq'] ? explode("\r\n", $basic_info['kf_qq']) : '';
		$kf_ww = $info_ww ? $info_ww[0] : '';
		$kf_qq = $info_qq ? $info_qq[0] : '';
		$basic_ww = $kf_ww ? explode('|', $kf_ww) : '';
		$basic_qq = $kf_qq ? explode('|', $kf_qq) : '';
		$basic_info['kf_ww'] = $basic_ww ? $basic_ww[1] : '';
		$basic_info['kf_qq'] = $basic_qq ? $basic_qq[1] : '';
		$customer_service = dao('shop_config')->where(array('code' => 'customer_service'))->getField('value');
		$zkf = dao('seller_shopinfo')->field('kf_type, kf_qq, kf_ww')->where(array('ru_id' => '0'))->find();
		$this->assign('customer_service', $customer_service);

		if ($customer_service == 0) {
			$basic_info['kf_ww'] = preg_replace('/^[^\\-]*\\|/is', '', $zkf['kf_ww']);
			$basic_info['kf_qq'] = preg_replace('/^[^\\-]*\\|/is', '', $zkf['kf_qq']);
		}

		if ($goods['user_id'] == 0) {
			if ($this->db->getOne('SELECT kf_im_switch FROM {pre}seller_shopinfo WHERE ru_id = 0')) {
				$basic_info['is_dsc'] = true;
			}
			else {
				$basic_info['is_dsc'] = false;
			}
		}
		else {
			$basic_info['is_dsc'] = false;
		}

		$basic_date = array('region_name');
		$basic_info['province'] = !empty($basic_info['province']) ? get_table_date('region', 'region_id = \'' . $basic_info['province'] . '\'', $basic_date, 2) : '';
		$basic_info['city'] = !empty($basic_info['city']) ? get_table_date('region', 'region_id= \'' . $basic_info['city'] . '\'', $basic_date, 2) . '市' : '';

		if (!empty($basic_info['meiqia'])) {
			$userinfo = get_user_default($_SESSION['user_id']);
			$this->assign('userinfo', $userinfo);
			$this->assign('meiqia_id', $basic_info['meiqia']);
		}

		$this->assign('basic_info', $basic_info);
		$shipping_list = warehouse_shipping_list($goods, $this->region_id, 1, $goods_region);
		$this->assign('shipping_list', $shipping_list);
		$_SESSION['goods_equal'] = '';
		$this->db->query('delete from ' . $this->ecs->table('cart_combo') . (' WHERE (parent_id = 0 and goods_id = \'' . $this->goods_id . '\' or parent_id = \'' . $this->goods_id . '\') and ') . $sess_id);
		$new_goods = get_recommend_goods('new', '', $this->region_id, $this->area_info['region_id'], $goods['user_id']);
		$this->assign('new_goods', $new_goods);
		$link_goods = get_linked_goods($this->goods_id, $this->region_id, $this->area_info['region_id']);
		$this->assign('link_goods', $link_goods);
		$time = gmtime();
		$sql = 'SELECT * FROM {pre}coupons WHERE (`cou_type` = 3 OR `cou_type` = 4 ) AND `cou_end_time` >' . $time . ' AND (( instr(`cou_goods`, ' . $this->goods_id . ') ) or (`cou_goods`=0)) AND  review_status = 3 and ru_id=' . $goods['user_id'];
		$coupont = $this->db->getALl($sql);

		foreach ($coupont as $key => $value) {
			$coupont[$key]['cou_end_time'] = local_date('Y.m.d', $value['cou_end_time']);
			$coupont[$key]['cou_start_time'] = local_date('Y.m.d', $value['cou_start_time']);

			if (0 < $_SESSION['user_id']) {
				$user_num = dao('coupons_user')->where(array('cou_id' => $value['cou_id'], 'user_id' => $_SESSION['user_id']))->count();
				if (0 < $user_num && $value['cou_user_num'] <= $user_num) {
					$coupont[$key]['cou_is_receive'] = 1;
				}
				else {
					$coupont[$key]['cou_is_receive'] = 0;
				}
			}

			$cou_num = dao('coupons_user')->where(array('cou_id' => $value['cou_id']))->count();
			$coupont[$key]['enable_ling'] = !empty($cou_num) && $value['cou_total'] <= $cou_num ? 1 : 0;
		}

		$this->assign('coupons_list', $coupont);
		$kf_im_switch = dao('seller_shopinfo')->where(array('ru_id' => '0'))->getField('kf_im_switch');
		$im_dialog = M()->query('SHOW TABLES LIKE "{pre}im_dialog"');
		if ($kf_im_switch == 1 && $im_dialog) {
			$this->assign('kefu', $kf_im_switch);
		}

		if (!empty($goods['desc_mobile'])) {
			if (C('shop.open_oss') == 1) {
				$bucket_info = get_bucket_info();
				$bucket_info['endpoint'] = empty($bucket_info['endpoint']) ? $bucket_info['outside_site'] : $bucket_info['endpoint'];
				$desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $goods['desc_mobile'], 'desc_mobile');
				$goods_desc = preg_replace('/<div[^>]*(tools)[^>]*>(.*?)<\\/div>(.*?)<\\/div>/is', '', $desc_preg['desc_mobile']);
			}
			else {
				$goods_desc = preg_replace('/<div[^>]*(tools)[^>]*>(.*?)<\\/div>(.*?)<\\/div>/is', '', $goods['desc_mobile']);
			}
		}

		if (empty($goods['desc_mobile']) && !empty($goods['goods_desc'])) {
			if (C('shop.open_oss') == 1) {
				$bucket_info = get_bucket_info();
				$bucket_info['endpoint'] = empty($bucket_info['endpoint']) ? $bucket_info['outside_site'] : $bucket_info['endpoint'];
				$goods_desc = str_replace(array('src="/images/upload', 'src="images/upload'), 'src="' . $bucket_info['endpoint'] . 'images/upload', $goods['goods_desc']);
			}
			else {
				$goods_desc = str_replace(array('src="/images/upload', 'src="images/upload'), 'src="' . __STATIC__ . '/images/upload', $goods['goods_desc']);
			}
		}

		if (empty($goods['desc_mobile']) && empty($goods['goods_desc'])) {
			$sql = 'SELECT ld.goods_desc FROM {pre}link_desc_goodsid AS dg, {pre}link_goods_desc AS ld WHERE dg.goods_id = ' . $this->goods_id . '  AND dg.d_id = ld.id AND ld.review_status > 2';
			$goods_desc = $this->db->getOne($sql);
		}

		if (!empty($goods_desc)) {
			$goods_desc = preg_replace('/<img(.*?)src=/i', '<img${1}class="lazy" src=', $goods_desc);
		}

		$this->assign('goods_desc', $goods_desc);
		$this->display();
	}

	public function actionNewGoods()
	{
		$goods = get_goods_info($this->goods_id, $this->region_id, $this->area_info['region_id']);
		$goods['pic'] = dao('goods_gallery')->field('img_url')->where(array('goods_id' => $this->goods_id))->select();

		foreach ($goods['pic'] as $key => $value) {
			$goods['pic'][$key]['img_url'] = get_image_path($value['img_url']);
		}

		$properties = get_goods_properties($this->goods_id, $this->region_id, $this->area_info['region_id']);

		foreach ($properties['pro'][''] as $k => $v) {
			$spec[$k]['name'] = $v['name'];
			$spec[$k]['value'] = $v['value'];
		}

		$spec = array_values($spec);

		foreach ($properties['spe'] as $key => $val) {
			$attr = $val['attr_type'];
		}

		$share_data = array('title' => $goods['goods_name'], 'desc' => $goods['goods_brief'], 'link' => '', 'img' => $goods['goods_img']);
		$cart_num = cart_number();
		$this->response(array('error' => 0, 'goods' => $goods, 'cart_num' => $cart_num, 'spec' => $spec, 'properties' => $properties['spe'], 'share_data' => $this->get_wechat_share_content($share_data), 'type' => $attr));
	}

	public function actionInfo()
	{
		$info = $this->db->table('goods')->field('goods_desc,desc_mobile')->where(array('goods_id' => $this->goods_id))->find();
		$properties = get_goods_properties($this->goods_id, $this->region_id, $this->area_info['region_id']);

		if (!empty($info['desc_mobile'])) {
			if (C('shop.open_oss') == 1) {
				$bucket_info = get_bucket_info();
				$bucket_info['endpoint'] = empty($bucket_info['endpoint']) ? $bucket_info['outside_site'] : $bucket_info['endpoint'];
				$desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $info['desc_mobile'], 'desc_mobile');
				$goods_desc = preg_replace('/<div[^>]*(tools)[^>]*>(.*?)<\\/div>(.*?)<\\/div>/is', '', $desc_preg['desc_mobile']);
			}
			else {
				$goods_desc = preg_replace('/<div[^>]*(tools)[^>]*>(.*?)<\\/div>(.*?)<\\/div>/is', '', $info['desc_mobile']);
			}
		}

		if (empty($info['desc_mobile']) && !empty($info['goods_desc'])) {
			if (C('shop.open_oss') == 1) {
				$bucket_info = get_bucket_info();
				$bucket_info['endpoint'] = empty($bucket_info['endpoint']) ? $bucket_info['outside_site'] : $bucket_info['endpoint'];
				$goods_desc = str_replace(array('src="/images/upload', 'src="images/upload'), 'src="' . $bucket_info['endpoint'] . 'images/upload', $info['goods_desc']);
			}
			else {
				$goods_desc = str_replace(array('src="/images/upload', 'src="images/upload'), 'src="' . __STATIC__ . '/images/upload', $info['goods_desc']);
			}
		}

		if (empty($info['desc_mobile']) && empty($info['goods_desc'])) {
			$sql = 'SELECT ld.goods_desc FROM {pre}link_desc_goodsid AS dg, {pre}link_goods_desc AS ld WHERE dg.goods_id = ' . $this->goods_id . '  AND dg.d_id = ld.id AND ld.review_status > 2';
			$link_desc = $this->db->getOne($sql);
			$goods_desc = $link_desc;
		}

		if (!empty($goods_desc)) {
			$goods_desc = preg_replace('/height\\="[0-9]+?"/', '', $goods_desc);
			$goods_desc = preg_replace('/width\\="[0-9]+?"/', '', $goods_desc);
			$goods_desc = preg_replace('/style=.+?[*|"]/i', '', $goods_desc);
			$goods_desc = preg_replace('/<img(.*?)src=/i', '<img${1}class="lazy" src=', $goods_desc);
		}

		$this->assign('goods_desc', $goods_desc);
		$this->assign('properties', $properties['pro']);
		$this->assign('page_title', L('goods_detail'));
		$this->display();
	}

	public function actionComment($rank = '')
	{
		if (IS_AJAX) {
			$rank = I('rank', 'all', array('htmlspecialchars', 'trim'));
			$page = I('page', 0, 'intval');
			$start = 0 < $page ? ($page - 1) * $this->size : 1;
			$arr = get_good_comment_as($this->goods_id, $rank, 1, $start, $this->size);
			$comments = $arr['arr'];
			$totalPage = $arr['max'];

			if ($rank == 'img') {
				foreach ($comments as $key => $val) {
					if ($val['comment_img'] == '') {
						unset($comments[$key]);
					}
				}

				$totalPage = $arr['img_max'];
			}

			$reset = 0 < $start ? 0 : 1;
			exit(json_encode(array('comments' => $comments, 'rank' => $rank, 'reset' => $reset, 'totalPage' => $totalPage, 'top' => 1)));
		}

		if ($rank == 'img') {
			$rank = $rank;
		}
		else {
			$rank = I('rank', 'all', array('htmlspecialchars', 'trim'));
		}

		$is_distribution = 0;

		if (is_dir(APP_DRP_PATH)) {
			$is_distribution = dao('goods')->where(array('goods_id' => $this->goods_id))->getField('is_distribution');
			$isdrp = $this->model->table('drp_config')->field('value')->where(array('code' => 'isdrp'))->find();
			$sql = 'SELECT id FROM {pre}drp_shop WHERE audit=1 AND status=1 AND user_id=' . $this->user_id;
			$drp = $this->db->getOne($sql);
		}

		$share_type = 0;
		if (isset($isdrp) && $isdrp['value'] == 1 && isset($drp) && 0 < $drp && $is_distribution == 1) {
			$share_type = 1;
		}

		$this->assign('share_type', $share_type);
		$this->assign('rank', $rank);
		$this->assign('comment_count', commentCol($this->goods_id));
		$this->assign('goods_id', $this->goods_id);
		$this->assign('page_title', L('goods_comment'));
		$this->display('comment');
	}

	public function actionInfoimg()
	{
		$rank = 'img';
		$this->actionComment($rank);
	}

	public function actionSharing()
	{
		if (IS_AJAX) {
			$back_act = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];

			if (empty($_SESSION['user_id'])) {
				exit(json_encode(array('error' => 2, 'back_act' => urlencode($back_act))));
			}

			$share_type = input('type', 0, 'intval');
			$user_id = $_SESSION['user_id'];

			if ($share_type == 1) {
				$goods_url = url('goods/index/index', array('id' => $this->goods_id, 'd' => $user_id), false, true);
			}
			else {
				$goods_url = url('goods/index/index', array('id' => $this->goods_id, 'u' => $user_id), false, true);
			}

			$goods = get_goods_info($this->goods_id, $this->region_id, $this->area_info['region_id']);

			if (empty($goods)) {
				exit(json_encode(array('error' => 1)));
			}

			$users = get_user_default($user_id);

			if (empty($users)) {
				exit(json_encode(array('error' => 1)));
			}

			$file = dirname(ROOT_PATH) . '/data/attached/goods_share/';

			if (!file_exists($file)) {
				make_dir($file, 511);
			}

			$avatar_file = dirname(ROOT_PATH) . '/data/attached/avatar/';

			if (!file_exists($avatar_file)) {
				make_dir($avatar_file, 511);
			}

			$goods_thumb_file = $file . 'goods_thumb/';

			if (!file_exists($goods_thumb_file)) {
				make_dir($goods_thumb_file, 511);
			}

			$bgImg = $file . 'goods_bg.png';
			$pictures_one = get_one_goods_gallery($this->goods_id);
			$goods_img = isset($pictures_one['img_url']) ? $pictures_one['img_url'] : $goods['goods_img'];
			$goods_thumb = $goods_thumb_file . $this->goods_id . '_' . basename($goods_img);
			$user_picture = basename($users['user_picture']);
			$avatar_name = isset($user_picture) && !empty($user_picture) ? $user_picture : md5($users['user_picture']) . '.png';
			$avatar = $avatar_file . 'avatar_' . $user_id . '_' . $avatar_name;
			$qrcode = $file . 'goods_qrcode_' . $share_type . '_' . $this->goods_id . '_' . $user_id . '.png';
			$outImg = $file . 'goods_share_' . $share_type . '_' . $this->goods_id . '_' . $user_id . '.png';
			if ($goods_url && $goods_img) {
				$qrCode = new \Endroid\QrCode\QrCode($goods_url);
				$img = new \Think\Image();

				if (!file_exists($avatar)) {
					$headimg = \App\Extensions\Http::doGet($users['user_picture']);
					$avatar_open = $avatar;
					file_put_contents($avatar_open, $headimg);

					if ($headimg === false) {
						if (strtolower(substr($users['user_picture'], 0, 4)) == 'http' && strpos($users['user_picture'], __STATIC__) !== false) {
							$avatar_open = str_replace(__STATIC__, dirname(ROOT_PATH), $users['user_picture']);
						}
						else {
							$avatar_open = dirname(ROOT_PATH) . $users['user_picture'];
						}
					}

					$img->open($avatar_open)->thumb(60, 60, \Think\Image::IMAGE_THUMB_FILLED)->save($avatar);
				}

				$qrCode->setSize(257)->setMargin(15);
				$qrCode->setLogoPath($avatar)->setLogoWidth(60);
				$qrCode->writeFile($qrcode);
				$bg_width = $img->open($bgImg)->width();
				$bg_height = $img->open($bgImg)->height();
				$goods_title = strip_tags(html_out($goods['goods_name']));
				$goods_title_first = sub_str($goods_title, 20, true, 0);
				$len = goods_name_strlen($goods_title_first);
				if (!empty($len) && 0 < $len) {
					$sub_len = 20 + $len / 2;
					$goods_title_first = sub_str($goods_title, $sub_len, true, 0);
				}

				$goods_price = price_format($goods['shop_price']);

				if (!file_exists($goods_thumb)) {
					if (C('shop.open_oss') == 1 || strtolower(substr($goods_img, 0, 4)) == 'http' && strpos($goods_img, __STATIC__) === false) {
						$goodsimg = \App\Extensions\Http::doGet($goods_img);
						$goodsthumb = $goods_thumb;
						file_put_contents($goodsthumb, $goodsimg);
					}
					else {
						$goodsthumb = str_replace(__STATIC__, dirname(ROOT_PATH), $goods_img);
					}

					$img->open($goodsthumb)->thumb($bg_width, $bg_width, \Think\Image::IMAGE_THUMB_FILLED)->save($goods_thumb);
				}

				$fonts_path = dirname(ROOT_PATH) . '/data/attached/fonts/msyh.ttf';
				$font_color = '#555555';
				$img->open($bgImg)->water($goods_thumb, array(0, 0), 100)->text($goods_title_first, $fonts_path, 20, $font_color, array(40, $bg_width + 20))->save($outImg);
				$img->open($outImg)->text($goods_price, $fonts_path, 28, '#EC5151', array(40, $bg_width + 70))->save($outImg);
				$nickname = sub_str($users['nick_name'], 10, false);

				if ($share_type == 1) {
					$text_description = sprintf(L('share_drp_desc'), $nickname, C('shop.shop_name'));
				}
				else {
					$text_description = sprintf(L('share_desc'), $nickname);
				}

				$qr_left = 50;
				$qr_top = $bg_width + 130;
				$logo_width = $img->open($qrcode)->width();
				$text_left = $bg_width / 8;
				$text_top = $qr_top + $logo_width + 45;
				$img->open($outImg)->water($qrcode, array($qr_left, $qr_top), 100)->text($text_description, $fonts_path, 18, '#999999', array($text_left, $text_top))->save($outImg);

				if (C('shop.open_oss') == 1) {
					$image_name = $this->ossMirror($outImg, 'data/attached/goods_share/');
				}
			}

			$image_name = 'data/attached/goods_share/' . basename($outImg);
			$outImg = get_image_path($image_name);
			exit(json_encode(array('error' => 0, 'share_img' => $outImg . '?v=' . time())));
		}
	}

	public function actionPrice()
	{
		$res = array('err_msg' => '', 'result' => '', 'qty' => 1);
		$attr = I('attr');
		$number = I('number', 1, 'intval');
		$attr_id = !empty($attr) ? explode(',', $attr) : array();
		$warehouse_id = I('request.warehouse_id', 0, 'intval');
		$area_id = I('request.area_id', 0, 'intval');
		$onload = I('request.onload', '', array('htmlspecialchars', 'trim'));
		$goods_attr = isset($_REQUEST['goods_attr']) ? explode(',', $_REQUEST['goods_attr']) : array();
		$attr_ajax = get_goods_attr_ajax($this->goods_id, $goods_attr, $attr_id);
		$goods = get_goods_info($this->goods_id, $warehouse_id, $area_id);

		if ($this->goods_id == 0) {
			$res['err_msg'] = L('err_change_attr');
			$res['err_no'] = 1;
		}
		else {
			if ($number == 0) {
				$res['qty'] = $number = 1;
			}
			else {
				$res['qty'] = $number;
			}

			$products = get_warehouse_id_attr_number($this->goods_id, $_REQUEST['attr'], $goods['user_id'], $warehouse_id, $area_id);
			$attr_number = isset($products['product_number']) ? $products['product_number'] : 0;
			$product_promote_price = isset($products['product_promote_price']) ? $products['product_promote_price'] : 0;

			if ($goods['model_attr'] == 1) {
				$table_products = 'products_warehouse';
				$type_files = ' and warehouse_id = \'' . $warehouse_id . '\'';
			}
			else if ($goods['model_attr'] == 2) {
				$table_products = 'products_area';
				$type_files = ' and area_id = \'' . $area_id . '\'';
			}
			else {
				$table_products = 'products';
				$type_files = '';
			}

			$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . (' WHERE goods_id = \'' . $this->goods_id . '\'') . $type_files . ' LIMIT 0, 1';
			$prod = $GLOBALS['db']->getRow($sql);

			if (0 < $goods['cloud_id']) {
				$sql = 'SELECT product_number,cloud_product_id FROM' . $GLOBALS['ecs']->table('products') . 'WHERE product_id = \'' . $products['product_id'] . '\'';
				$product = $GLOBALS['db']->getRow($sql);
				$attr_number = get_jigon_products_stock($product);
			}
			else if ($goods['goods_type'] == 0) {
				$attr_number = $goods['goods_number'];
			}
			else {
				if (empty($prod)) {
					$attr_number = $goods['goods_number'];
				}

				if (!empty($prod) && $GLOBALS['_CFG']['add_shop_price'] == 0 && $onload == 'onload') {
					if (empty($attr_number)) {
						$attr_number = $goods['goods_number'];
					}
				}
			}

			$attr_number = !empty($attr_number) ? $attr_number : 0;
			$res['attr_number'] = $attr_number;
			$res['limit_number'] = $attr_number < $number ? ($attr_number ? $attr_number : 1) : $number;
			$shop_price = get_final_price($this->goods_id, $number, true, $attr_id, $warehouse_id, $area_id);
			$res['shop_price'] = price_format($shop_price);
			$res['market_price'] = $goods['market_price'];
			$res['show_goods'] = 0;
			if ($goods_attr && $GLOBALS['_CFG']['add_shop_price'] == 0) {
				if (count($goods_attr) == count($attr_ajax['attr_id'])) {
					$res['show_goods'] = 1;
				}
			}

			$spec_price = get_final_price($this->goods_id, $number, true, $attr_id, $warehouse_id, $area_id, 1, 0, 0, $res['show_goods'], $product_promote_price);
			if ($GLOBALS['_CFG']['add_shop_price'] == 0 && empty($spec_price) && empty($prod)) {
				$spec_price = $shop_price;
			}

			if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
				$shop_price = $spec_price;
				$res['result'] = price_format($spec_price);
			}
			else {
				$res['result'] = price_format($shop_price);
			}

			$res['spec_price'] = price_format($spec_price);
			$martetprice_amount = $spec_price + $goods['marketPrice'];
			$res['marketPrice_amount'] = price_format($spec_price + $goods['marketPrice']);
			$res['discount'] = round($shop_price / $martetprice_amount * 10, 1);
			$res['result'] = price_format($shop_price);

			if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
				$goods['marketPrice'] = isset($products['product_market_price']) && !empty($products['product_market_price']) ? $products['product_market_price'] : $goods['marketPrice'];
				$res['result_market'] = price_format($goods['marketPrice']);
			}
			else {
				$res['result_market'] = price_format($goods['marketPrice'] + $spec_price);
			}
		}

		$goods_fittings = get_goods_fittings_info($this->goods_id, $warehouse_id, $area_id, '', 1);
		$fittings_list = get_goods_fittings(array($this->goods_id), $warehouse_id, $area_id);

		if ($fittings_list) {
			if (is_array($fittings_list)) {
				foreach ($fittings_list as $vo) {
					$fittings_index[$vo['group_id']] = $vo['group_id'];
				}
			}

			ksort($fittings_index);
			$merge_fittings = get_merge_fittings_array($fittings_index, $fittings_list);
			$fitts = get_fittings_array_list($merge_fittings, $goods_fittings);

			for ($i = 0; $i < count($fitts); $i++) {
				$fittings_interval = $fitts[$i]['fittings_interval'];
				$res['fittings_interval'][$i]['fittings_minMax'] = price_format($fittings_interval['fittings_min']) . '-' . number_format($fittings_interval['fittings_max'], 2, '.', '');
				$res['fittings_interval'][$i]['market_minMax'] = price_format($fittings_interval['market_min']) . '-' . number_format($fittings_interval['market_max'], 2, '.', '');

				if ($fittings_interval['save_minPrice'] == $fittings_interval['save_maxPrice']) {
					$res['fittings_interval'][$i]['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']);
				}
				else {
					$res['fittings_interval'][$i]['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']) . '-' . number_format($fittings_interval['save_maxPrice'], 2, '.', '');
				}

				$res['fittings_interval'][$i]['groupId'] = $fittings_interval['groupId'];
			}
		}

		if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
			$area_list = get_goods_link_area_list($this->goods_id, $goods['user_id']);

			if ($area_list['goods_area']) {
				if (!in_array($area_id, $area_list['goods_area'])) {
					$res['err_no'] = 2;
				}
			}
			else {
				$res['err_no'] = 2;
			}
		}

		$attr_info = get_attr_value($this->goods_id, $attr_id[0]);

		if (!empty($attr_info['attr_img_flie'])) {
			$res['attr_img'] = get_image_path($attr_info['attr_img_flie']);
		}

		$area_position_list = get_goods_user_area_position($goods['user_id'], $this->city_id, $_REQUEST['attr'], $this->goods_id, 0, 0, 1, 0, '');

		if (0 < count($area_position_list)) {
			$res['store_type'] = 1;
		}
		else {
			$res['store_type'] = 0;
		}

		$res['onload'] = $onload;
		exit(json_encode($res));
	}

	public function actionInWarehouse()
	{
		if (IS_AJAX) {
			$res = array('err_msg' => '', 'result' => '', 'qty' => 1, 'goods_id' => 0);
			$pid = I('get.pid', 0, 'intval');
			$goods_id = I('get.id', 0, 'intval');
			if (empty($pid) || empty($goods_id)) {
				exit(json_encode($res));
			}

			cookie('region_id', $pid);
			cookie('regionid', $pid);
			$area_region = 0;
			cookie('area_region', $area_region);
			$res['goods_id'] = $goods_id;
			exit(json_encode($res));
		}
	}

	public function actionInstock()
	{
		if (IS_AJAX) {
			$res = array('err_msg' => '', 'result' => '', 'qty' => 1);
			clear_cache_files();
			$goods_id = $this->goods_id;
			$province = I('get.province', 1, 'intval');
			$city = I('get.city', 0, 'intval');
			$district = I('get.district', 0, 'intval');
			$street = I('get.street', 0, 'intval');
			$d_null = I('get.d_null', 0, 'intval');
			$user_id = I('get.user_id', 0, 'intval');
			$user_address = get_user_address_region($user_id);
			$user_address = explode(',', $user_address['region_address']);
			setcookie('province', $province, gmtime() + 3600 * 24 * 30);
			setcookie('city', $city, gmtime() + 3600 * 24 * 30);
			setcookie('district', $district, gmtime() + 3600 * 24 * 30);
			setcookie('street', $street, gmtime() + 3600 * 24 * 30);
			$regionId = 0;
			setcookie('regionId', $regionId, gmtime() + 3600 * 24 * 30);
			setcookie('type_province', 0, gmtime() + 3600 * 24 * 30);
			setcookie('type_city', 0, gmtime() + 3600 * 24 * 30);
			setcookie('type_district', 0, gmtime() + 3600 * 24 * 30);
			setcookie('type_street', 0, gmtime() + 3600 * 24 * 30);
			$res['d_null'] = $d_null;

			if ($d_null == 0) {
				if (in_array($district, $user_address)) {
					$res['isRegion'] = 1;
				}
				else {
					$res['message'] = L('write_address');
					$res['isRegion'] = 88;
				}
			}
			else {
				setcookie('district', '', gmtime() + 3600 * 24 * 30);
			}

			$res['goods_id'] = $goods_id;
			exit(json_encode($res));
		}
	}

	public function actionAddCollection()
	{
		$result = array('error' => 0, 'message' => '');
		if (!isset($this->user_id) || $this->user_id == 0) {
			$result['error'] = 2;
			$result['message'] = L('login_please');
			exit(json_encode($result));
		}
		else {
			$where['user_id'] = $this->user_id;
			$where['goods_id'] = $this->goods_id;
			$rs = $this->db->table('collect_goods')->where($where)->count();

			if (0 < $rs) {
				$rs = $this->db->table('collect_goods')->where($where)->delete();

				if (!$rs) {
					$result['error'] = 1;
					$result['message'] = M()->errorMsg();
					exit(json_encode($result));
				}
				else {
					$result['error'] = 0;
					$result['message'] = L('collect_success');
					exit(json_encode($result));
				}
			}
			else {
				$data['user_id'] = $this->user_id;
				$data['goods_id'] = $this->goods_id;
				$data['add_time'] = gmtime();

				if ($this->db->table('collect_goods')->data($data)->add() === false) {
					$result['error'] = 1;
					$result['message'] = M()->errorMsg();
					exit(json_encode($result));
				}
				else {
					$result['error'] = 0;
					$result['message'] = L('collect_success');
					exit(json_encode($result));
				}
			}
		}
	}

	private function init_params()
	{
		if (!isset($_COOKIE['province'])) {
			$area_array = get_ip_area_name();

			if ($area_array['county_level'] == 2) {
				$date = array('region_id', 'parent_id', 'region_name');
				$where = 'region_name = \'' . $area_array['area_name'] . '\' AND region_type = 2';
				$city_info = get_table_date('region', $where, $date, 1);
				$date = array('region_id', 'region_name');
				$where = 'region_id = \'' . $city_info[0]['parent_id'] . '\'';
				$province_info = get_table_date('region', $where, $date);
				$where = 'parent_id = \'' . $city_info[0]['region_id'] . '\' order by region_id asc limit 0, 1';
				$district_info = get_table_date('region', $where, $date, 1);
				$where = 'parent_id = \'' . $district_info[0]['region_id'] . '\' order by region_id asc limit 0, 1';
				$town_info = get_table_date('region', $where, $date, 1);
			}
			else if ($area_array['county_level'] == 1) {
				$area_name = $area_array['area_name'];
				$date = array('region_id', 'region_name');
				$where = 'region_name = \'' . $area_name . '\'';
				$province_info = get_table_date('region', $where, $date);
				$where = 'parent_id = \'' . $province_info['region_id'] . '\' order by region_id asc limit 0, 1';
				$city_info = get_table_date('region', $where, $date, 1);
				$where = 'parent_id = \'' . $city_info[0]['region_id'] . '\' order by region_id asc limit 0, 1';
				$district_info = get_table_date('region', $where, $date, 1);
				$where = 'parent_id = \'' . $district_info[0]['region_id'] . '\' order by region_id asc limit 0, 1';
				$town_info = get_table_date('region', $where, $date, 1);
			}
		}

		$order_area = get_user_order_area($this->user_id);
		$user_area = get_user_area_reg($this->user_id);
		if ($order_area['province'] && 0 < $this->user_id) {
			$this->province_id = $order_area['province'];
			$this->city_id = $order_area['city'];
			$this->district_id = $order_area['district'];
			$this->town_region_id = $order_area['street'];
		}
		else {
			if (0 < $user_area['province']) {
				$this->province_id = $user_area['province'];
				cookie('province', $user_area['province']);
				$this->region_id = get_province_id_warehouse($this->province_id);
			}
			else {
				$sql = 'select region_name from ' . $this->ecs->table('region_warehouse') . ' where regionId = \'' . $province_info['region_id'] . '\'';
				$warehouse_name = $this->db->getOne($sql);
				$this->province_id = $province_info['region_id'];
				$cangku_name = $warehouse_name;
				$this->region_id = get_warehouse_name_id(0, $cangku_name);
			}

			if (0 < $user_area['city']) {
				$this->city_id = $user_area['city'];
				cookie('city', $user_area['city']);
			}
			else {
				$this->city_id = $city_info[0]['region_id'];
			}

			if (0 < $user_area['district']) {
				$this->district_id = $user_area['district'];
				cookie('district', $user_area['district']);
			}
			else {
				$this->district_id = $district_info[0]['region_id'];
			}

			if (0 < $user_area['street']) {
				$this->town_region_id = $user_area['street'];
				cookie('town_region_id', $user_area['street']);
			}
			else {
				$this->town_region_id = $town_info[0]['region_id'];
			}
		}

		$this->province_id = isset($_COOKIE['province']) ? $_COOKIE['province'] : $this->province_id;
		$child_num = get_region_child_num($this->province_id);

		if (0 < $child_num) {
			$this->city_id = isset($_COOKIE['city']) ? $_COOKIE['city'] : $this->city_id;
		}
		else {
			$this->city_id = '';
		}

		$child_num = get_region_child_num($this->city_id);

		if (0 < $child_num) {
			$this->district_id = isset($_COOKIE['district']) ? $_COOKIE['district'] : $this->district_id;
		}
		else {
			$this->district_id = '';
		}

		$child_num = get_region_child_num($this->district_id);

		if (0 < $child_num) {
			$this->town_region_id = isset($_COOKIE['street']) ? $_COOKIE['street'] : $this->town_region_id;
		}
		else {
			$this->town_region_id = '';
		}

		$this->region_id = !isset($_COOKIE['region_id']) ? $this->region_id : $_COOKIE['region_id'];
		$goods_warehouse = get_warehouse_goods_region($this->province_id);

		if ($goods_warehouse) {
			$this->regionId = $goods_warehouse['region_id'];
			if ($_COOKIE['region_id'] && $_COOKIE['regionid']) {
				$gw = 0;
			}
			else {
				$gw = 1;
			}
		}

		if ($gw) {
			$this->region_id = $this->regionId;
			cookie('area_region', $this->region_id);
		}

		cookie('goodsId', $this->goods_id);
		$sellerInfo = get_seller_info_area();

		if (empty($this->province_id)) {
			$this->province_id = $sellerInfo['province'];
			$this->city_id = $sellerInfo['city'];
			$this->district_id = 0;
			cookie('province', $this->province_id);
			cookie('city', $this->city_id);
			cookie('district', $this->district_id);
			cookie('stree', $this->town_region_id);
			$this->region_id = get_warehouse_goods_region($this->province_id);
		}

		$this->area_info = get_area_info($this->province_id);
	}

	public function actionCheckDrp()
	{
		if (IS_AJAX) {
			$shop_num = $this->model->table('drp_shop')->where(array('user_id' => $this->user_id))->count();

			if ($shop_num == 1) {
				exit(json_encode(array('code' => 1)));
			}
			else {
				exit(json_encode(array('code' => 0)));
			}
		}
	}

	public function font($key)
	{
		if (4 < $key) {
			return L('height');
		}
		else if (3 < $key) {
			return L('middle');
		}
		else {
			return L('low');
		}
	}
}

?>
