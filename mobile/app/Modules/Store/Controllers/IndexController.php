<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Store\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	protected $region_id = 0;
	protected $area_info = array();
	protected $user_id = 0;
	protected $review_goods;
	protected $lat;
	protected $lng;

	public function __construct()
	{
		parent::__construct();
		L(require LANG_PATH . C('shop.lang') . '/other.php');
		$this->user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
		$this->init_params();
		$this->review_goods = $GLOBALS['_CFG']['review_goods'] == 1 ? ' and review_status > 2 ' : '';
		$this->assign('area_id', $this->area_info['region_id']);
		$this->assign('warehouse_id', $this->region_id);
	}

	public function actionIndex()
	{
		$keywords = I('get.where', '');
		$keywords = str_replace(',jia,', '+', $keywords);
		$keywords = mysql_like_quote(trim($keywords));
		$type = I('get.type', '');
		$this->assign('keywords', $keywords);
		$this->assign('type', $type);

		if (IS_AJAX) {
			$condition = ' 1=1 ';
			$keywords = I('keywords');

			if ($keywords) {
				$type = I('type', '');

				if (!empty($type)) {
					if ($type == 1) {
						$condition .= ' AND a.user_shopMain_category LIKE \'%' . $keywords . ':%\'';
					}
					else if ($type == 2) {
						if (empty($_SESSION['keywordwhere']) || $_SESSION['keywordwhere'] != $keywords) {
							if (!empty($keywords)) {
								if (!empty($_COOKIE['ECS']['keywords'])) {
									$history = explode(',', $_COOKIE['ECS']['keywords']);
									array_unshift($history, $keywords);
									$history = array_unique($history);
									cookie('ECS[keywords]', implode(',', $history));
								}
								else {
									cookie('ECS[keywords]', $keywords);
								}
							}

							$_SESSION['keywordwhere'] = $keywords;
						}

						$condition .= ' AND a.rz_shopName LIKE \'%' . $keywords . '%\'';
					}
				}
			}

			$cat_id = I('post.cat_id', 0);
			$store_user = get_cat_store_list($cat_id);
			$city = I('post.city_id', 0);
			$lat = I('lat', 0);
			$lng = I('lng', 0);
			$order = I('order');
			$sort = I('sort', 'DESC');
			$page = I('page', 1);
			if ($cat_id && $store_user) {
				$condition .= ' AND a.user_id in (' . $store_user . ')';
			}

			if (!empty($city)) {
				$condition .= ' AND b.city =' . $city;
			}

			if ($order == 'sort') {
				$order = ' a.sort_order ';
			}

			$order .= ' ' . $sort;
			$offset = 5;
			$limit = ' limit ' . ($page - 1) * $offset . ',' . $offset;
			$count = 'SELECT count(*) as count FROM {pre}merchants_shop_information as a LEFT JOIN {pre}seller_shopinfo as b ON a.user_id = b.ru_id  WHERE ' . $condition . ' and a.is_street = 1 and a.merchants_audit = 1';
			if ($lat == 0 && $lng == 0) {
				$sql = 'SELECT * FROM {pre}merchants_shop_information as a LEFT JOIN {pre}seller_shopinfo as b ON a.user_id = b.ru_id  WHERE ' . $condition . ' and a.is_street = 1 and a.merchants_audit = 1 order by a.sort_order, a.shop_id ' . $sort . $limit;
			}

			if (!empty($lat) && !empty($lng)) {
				$sql = 'SELECT b.*,a.*,( 6371 * acos( cos( radians(' . $lat . ') ) * cos( radians( b.latitude ) ) * cos( radians( b.longitude ) - radians(' . $lng . ') ) + sin( radians(' . $lat . ') ) * sin( radians( b.latitude ) ) )) AS distance FROM {pre}merchants_shop_information as a LEFT JOIN {pre}seller_shopinfo as b ON a.user_id = b.ru_id WHERE ' . $condition . ' and a.is_street = 1 and a.merchants_audit = 1 order by ' . $order . $limit;
			}

			$user_store_count = dao('collect_store')->where(array('user_id' => $_SESSION['user_id']))->count();
			$cache_id = md5($sql . $user_store_count);
			$result = S($cache_id);

			if ($result === false) {
				$counts = $this->db->getOne($count);
				$store_list = $this->db->getAll($sql);

				foreach ($store_list as $key) {
					if (0 < $key['user_id']) {
						$merchants_goods_comment = get_merchants_goods_comment($key['user_id']);
					}

					$gaze = $this->model->table('collect_store')->where(array('ru_id' => $key['user_id']))->count('user_id');
					$sql = 'SELECT * FROM {pre}goods WHERE user_id = ' . $key['user_id'] . ' AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 ' . $this->review_goods . ' ORDER BY goods_id desc LIMIT 4';
					$goods = $this->db->getAll($sql);

					if (0 < $_SESSION['user_id']) {
						$sql = 'SELECT rec_id FROM {pre}collect_store WHERE user_id = ' . $_SESSION['user_id'] . ' AND ru_id=' . $key['user_id'];
						$status = $this->db->getOne($sql);
						$status = 0 < $status ? 'active' : '';
					}

					$goods = 0 < count($goods) ? $goods : 0;
					$goodsarr = array();

					if ($goods) {
						foreach ($goods as $gkey) {
							$goodsarr[] = get_goods_info($gkey['goods_id'], $this->region_id, $this->area_info['region_id']);
						}
					}
					else {
						$goodsarr = 0;
					}

					$distance = round($key['distance'], 3);
					$info[] = array('shop_id' => $key['shop_id'], 'url' => build_uri('store', array('stid' => $key['user_id'])), 'user_id' => $key['user_id'], 'shop_name' => get_shop_name($key['user_id'], 1), 'self_run' => $key['self_run'], 'shop_logo' => get_image_path(ltrim($key['logo_thumb'], '../')), 'commentrank' => $merchants_goods_comment['cmt']['commentRank']['zconments']['score'], 'commentrank_bg' => $this->cmt($merchants_goods_comment['cmt']['commentRank']['zconments']['score']), 'commentserver' => $merchants_goods_comment['cmt']['commentServer']['zconments']['score'], 'commentserver_bg' => $this->cmt($merchants_goods_comment['cmt']['commentServer']['zconments']['score']), 'commentdelivery' => $merchants_goods_comment['cmt']['commentDelivery']['zconments']['score'], 'commentdelivery_bg' => $this->cmt($merchants_goods_comment['cmt']['commentDelivery']['zconments']['score']), 'commentrank_font' => $this->font($merchants_goods_comment['cmt']['commentRank']['zconments']['score']), 'commentrank_box' => $this->boxbg($merchants_goods_comment['cmt']['commentRank']['zconments']['score']), 'commentserver_font' => $this->font($merchants_goods_comment['cmt']['commentServer']['zconments']['score']), 'commentserver_box' => $this->boxbg($merchants_goods_comment['cmt']['commentServer']['zconments']['score']), 'commentdelivery_font' => $this->font($merchants_goods_comment['cmt']['commentDelivery']['zconments']['score']), 'commentdelivery_box' => $this->boxbg($merchants_goods_comment['cmt']['commentDelivery']['zconments']['score']), 'gaze_number' => $gaze, 'gaze_status' => $status, 'goods' => $goodsarr, 'title' => $title = 0 < count($goods) ? '爆款商品' : '', 'distance' => $distance);
				}

				$result = array('list' => $info, 'totalPage' => ceil($counts / $offset));
				S($cache_id, $result);
			}

			exit(json_encode($result));
			return NULL;
		}

		$category = $this->db->getAll('SELECT cat_id, cat_name, cat_alias_name,touch_icon FROM {pre}category WHERE parent_id = 0 and is_show = 1 ORDER BY sort_order ASC, cat_id ASC');

		foreach ($category as $key => $val) {
			$category[$key]['cat_alias_name'] = empty($val['cat_alias_name']) ? $val['cat_name'] : $val['cat_alias_name'];
			$category[$key]['touch_icon'] = get_image_path($val['touch_icon']);
		}

		$this->assign('category', $category);
		$province = $this->model->table('region')->where(array('parent_id' => 1))->select();
		$this->assign('province', $province);
		$this->assign('page_title', L('shop_street'));
		$this->display();
	}

	public function actionRegion()
	{
		$id = I('city');
		$city = $this->model->table('region')->where(array('parent_id' => $id))->select();
		exit(json_encode(array('list' => $city, 'html' => 1)));
	}

	public function actionAddCollect()
	{
		$shopid = I('shopid', 0, 'intval');
		if (!empty($shopid) && 0 < $_SESSION['user_id']) {
			$status = $this->db->getRow('SELECT user_id, rec_id FROM {pre}collect_store WHERE ru_id = ' . $shopid . ' AND user_id=' . $_SESSION['user_id']);

			if (0 < count($status)) {
				$this->db->query('DELETE FROM {pre}collect_store WHERE rec_id = ' . $status['rec_id']);
				exit(json_encode(array('error' => 2, 'msg' => L('cancel_attention'))));
			}
			else {
				$this->db->query('INSERT INTO {pre}collect_store (user_id, ru_id, add_time, is_attention) VALUES (' . $_SESSION['user_id'] . (',\'' . $shopid . '\',') . time() . ',1)');
				exit(json_encode(array('error' => 1, 'msg' => L('attentioned'))));
			}
		}
		else {
			exit(json_encode(array('error' => 0, 'msg' => L('please_login'))));
		}
	}

	public function actionShopInfo()
	{
		$userid = I('id', 0, 'intval');
		$sql = 'SELECT * FROM {pre}merchants_shop_information as a JOIN {pre}seller_shopinfo as b ON a.user_id=b.ru_id  WHERE user_id=' . $userid;
		$data = $this->db->getRow($sql);
		if (empty($userid) || $data['user_id'] != $userid) {
			ecs_header('Location: ' . url('store/index/index'));
		}

		if (0 < $_SESSION['user_id']) {
			$sql = 'SELECT rec_id FROM {pre}collect_store WHERE user_id=' . $_SESSION['user_id'] . ' AND ru_id=' . $data['user_id'];
			$status = $this->db->getOne($sql);
			$status = 0 < $status ? 'active' : '';
		}

		$sql = 'SELECT count(user_id) as a FROM {pre}collect_store WHERE ru_id = ' . $data['user_id'];
		$follow = $this->db->getOne($sql);
		$follow = empty($follow) ? 0 : $follow;
		$cat = get_user_store_category($data['user_id']);
		$cat = array_slice($cat, 0, 8);
		$sql = 'SELECT goods_id FROM {pre}goods WHERE user_id = ' . $data['user_id'] . ' and is_on_sale = 1 and is_alone_sale = 1 and is_delete = 0 ' . $this->review_goods . ' order by sort_order, goods_id DESC LIMIT 6';
		$list = $this->db->getAll($sql);
		$sql = 'SELECT img_url FROM {pre}seller_shopslide WHERE ru_id = ' . $data['user_id'] . ' AND is_show = 1';
		$flash = $this->db->getRow($sql);
		$flash['img_url'] = stripos($flash['img_url'], '../') === false ? '../' . $flash['img_url'] : $flash['img_url'];

		if ($list) {
			foreach ($list as $key => $val) {
				$list[$key] = get_goods_info($val['goods_id'], $this->region_id, $this->area_info['region_id']);
			}
		}

		$info = $this->shopdata($data);

		if ($info === false) {
			$this->redirect('store/index/index');
		}

		$info['shop_category'] = $cat;
		$info['count_gaze'] = $follow;
		$info['gaze_status'] = $status;
		$info['goods_list'] = $list;
		$this->assign('info', $info);
		$seo = get_seo_words('shop');

		foreach ($seo as $key => $value) {
			$seo[$key] = html_in(str_replace(array('{sitename}', '{key}', '{shopname}', '{description}'), array(C('shop.shop_name'), $data['shop_keyword'], $data['shop_title'], $data['street_desc']), $value));
		}

		$page_title = !empty($seo['title']) ? $seo['title'] : $data['shop_name'];
		$keywords = !empty($seo['keywords']) ? $seo['keywords'] : C('shop.shop_keywords');
		$description = !empty($seo['description']) ? $seo['description'] : (!empty($data['street_desc']) ? $data['street_desc'] : C('shop.shop_desc'));
		$share_data = array('title' => $page_title, 'desc' => $description, 'link' => '', 'img' => get_wechat_image_path(ltrim($data['logo_thumb'], '../')));
		$this->assign('share_data', $this->get_wechat_share_content($share_data));
		$this->assign('page_title', $page_title);
		$this->assign('keywords', $keywords);
		$this->assign('description', $description);
		$path = dirname(ROOT_PATH) . '/kefu/';

		if (is_dir($path)) {
			$this->assign('has_kefu', 1);
		}

		$this->display();
	}

	public function actionProList()
	{
		$type = I('type', '');
		$ru_id = I('ru_id', '');
		$keyword = I('keyword', '');
		$bid = I('bid', '');
		$cat_id = I('cat_id', '');
		$bigcat = I('bigcat', '');
		$whereinfo = I('where', '');
		$order = I('order', 'desc');
		$sort = I('sort', '1');

		if ($sort == 1) {
			$sort = 'g.sort_order, g.goods_id';
		}

		if ($sort == 3) {
			$sort = 'g.sales_volume';
		}

		if ($sort == 4) {
			$sort = 'shop_price';
		}

		if ($cat_id == 0) {
			$children = '';
		}
		else {
			$children = get_children($cat_id, 0, 0, 'merchants_category', 'g.user_cat');
		}

		if (IS_AJAX) {
			$size = I('size', 6);
			$page = I('post.page', 1, 'intval');
			$list = store_get_goods($children, $bid, 0, 0, '', $size, $page, $sort, $order, $ru_id, $this->region_id, $this->area_info['region_id'], $keyword, $type);
			$maxpage = store_get_goods($children, $bid, 0, 0, '', 0, 0, $sort, $order, $ru_id, $this->region_id, $this->area_info['region_id'], $keyword, $type);

			if (!empty($_COOKIE['ECS']['keywords'])) {
				$history = explode(',', $_COOKIE['ECS']['keywords']);
				array_unshift($history, $keyword);
				$history = array_unique($history);
				cookie('ECS[keywords]', implode(',', $history));
			}
			else {
				cookie('ECS[keywords]', $keyword);
			}

			$show = empty($list) ? 0 : 1;
			exit(json_encode(array('list' => $list, 'totalPage' => ceil($maxpage / $size), 'show' => $show)));
		}

		$sql = 'SELECT bid, bank_name_letter, brandName FROM {pre}merchants_shop_brand WHERE user_id =' . $ru_id;
		$brand = $this->db->getAll($sql);
		$category = get_user_store_category($ru_id);
		$page = empty($page) ? 0 : $page;
		$this->assign('category', $category);
		$this->assign('bigcat', $bigcat);
		$this->assign('brand', $brand);
		$this->assign('page', $page);
		$this->assign('type', $type);
		$this->assign('ru_id', $ru_id);
		$this->assign('cat_id', $cat_id);
		$this->assign('keyword', $keyword);
		$this->assign('bid', $bid);
		$this->assign('where', '');
		$this->assign('page_title', '店铺商品');
		$this->display();
	}

	protected function shopdata($data = array())
	{
		$user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;

		if (empty($user_id)) {
			return false;
		}

		$shop_expiredatestart = strtotime($data['shop_expiredatestart']);
		$info['count_goods'] = $this->sql('user_id =' . $user_id . '   AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0' . $this->review_goods);
		$info['count_goods_new'] = $this->sql('store_new = 1 AND user_id=' . $user_id . '   AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0' . $this->review_goods);
		$info['count_goods_promote'] = $this->sql('is_promote = 1 AND user_id=' . $user_id . '   AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0' . $this->review_goods);
		$info['count_bonus'] = $this->sql($user_id, '');
		$info['bonus_all'] = $this->sql($user_id, '', 1);
		$info['shop_id'] = $data['shop_id'];
		$info['ru_id'] = $data['user_id'];
		$info['shop_logo'] = get_image_path(ltrim($data['logo_thumb'], '../'));
		$info['street_thumb'] = get_image_path(ltrim($data['street_thumb'], '../'));
		$info['shop_name'] = get_shop_name($data['user_id'], 1);
		$info['shop_desc'] = $data['shop_name'];
		$info['shop_start'] = date('Y年m月d日', $shop_expiredatestart);
		$info['shop_address'] = $data['shop_address'];
		$info['shop_flash'] = get_image_path($data['street_thumb']);
		$info['shop_wangwang'] = $this->dokf($data['kf_ww']);
		$info['shop_qq'] = $this->dokf($data['kf_qq']);
		$info['shop_tel'] = $data['kf_tel'];
		$info['is_im'] = $data['is_im'];
		$info['self_run'] = $data['self_run'];
		$info['meiqia'] = $data['meiqia'];
		$info['kf_appkey'] = $data['kf_appkey'];

		if (0 < $data['user_id']) {
			$merchants_goods_comment = get_merchants_goods_comment($data['user_id']);
		}

		if (0 < $_SESSION['user_id']) {
			$sql = 'SELECT rec_id FROM {pre}collect_store WHERE user_id = ' . $_SESSION['user_id'] . ' AND ru_id = ' . $data['shop_id'];
			$status = $this->db->getOne($sql);
			$status = 0 < $status ? 'active' : '';
		}

		$info['commentrank'] = $merchants_goods_comment['cmt']['commentRank']['zconments']['score'] . '分';
		$info['commentserver'] = $merchants_goods_comment['cmt']['commentServer']['zconments']['score'] . '分';
		$info['commentdelivery'] = $merchants_goods_comment['cmt']['commentDelivery']['zconments']['score'] . '分';
		$info['commentrank_font'] = $this->font($merchants_goods_comment['cmt']['commentRank']['zconments']['score']);
		$info['commentserver_font'] = $this->font($merchants_goods_comment['cmt']['commentServer']['zconments']['score']);
		$info['commentdelivery_font'] = $this->font($merchants_goods_comment['cmt']['commentDelivery']['zconments']['score']);
		$info['gaze_status'] = $status;
		return $info;
	}

	public function sql($where, $type = 1, $data = '')
	{
		$time = gmtime();

		if ($type == 1) {
			$sql = 'SELECT goods_id FROM {pre}goods WHERE ' . $where;
			$info = $this->db->getAll($sql);
			return count($info);
		}
		else {
			$sql = 'SELECT * FROM {pre}coupons WHERE (`cou_type` = 3 OR `cou_type` = 4 ) AND `cou_end_time` > ' . $time . ' AND (( instr(`cou_ok_user`, ' . $_SESSION['user_rank'] . ') ) or (`cou_ok_user`=0)) AND review_status = 3 AND ru_id=' . $where;
			$info = $this->db->getAll($sql);

			if ($data == '') {
				return count($info);
			}
			else {
				foreach ($info as $key => $val) {
					$info[$key]['cou_man'] = intval($val['cou_man']);
					$info[$key]['cou_money'] = intval($val['cou_money']);
				}

				return $info;
			}
		}
	}

	public function actionShopAbout()
	{
		$ru_id = I('ru_id', '0', 'intval');
		$sql = 'SELECT * FROM {pre}merchants_shop_information as a JOIN {pre}seller_shopinfo as b ON a.user_id = b.ru_id WHERE user_id = ' . $ru_id;
		$data = $this->db->getRow($sql);
		$sql = 'SELECT count(user_id) as a FROM {pre}collect_store WHERE ru_id = ' . $data['user_id'];
		$follow = $this->db->getOne($sql);
		$info = $this->shopdata($data);
		$info['shoprz_brandName'] = $data['shoprz_brandname'];
		$grade_info = get_seller_grade($ru_id);
		$info['grade_img'] = get_image_path($grade_info['grade_img']);
		$info['grade_name'] = $grade_info['grade_name'];
		$info['street_desc'] = $data['street_desc'];
		$info['count_gaze'] = intval($follow);
		$info['lat'] = $data['latitude'];
		$info['long'] = $data['longitude'];
		$url = url('shop_info', array('id' => $ru_id), true, true);
		$errorCorrectionLevel = 'M';
		$matrixPointSize = 8;
		$file = dirname(ROOT_PATH) . '/data/attached/shop_qrcode/';

		if (!file_exists($file)) {
			make_dir($file, 511);
		}

		$filename = $file . 'shop_qrcode_' . $ru_id . $errorCorrectionLevel . $matrixPointSize . '.png';

		if (!file_exists($filename)) {
			$code = \App\Extensions\QRcode::png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
		}

		if (C('shop.open_oss') == 1) {
			$image_name = $this->ossMirror($filename, 'data/attached/shop_qrcode/');
		}
		else {
			$image_name = 'data/attached/shop_qrcode/' . basename($filename);
		}

		$qrcode_url = get_image_path($image_name);
		$info['code'] = $qrcode_url;
		$path = dirname(ROOT_PATH) . '/kefu/';

		if (is_dir($path)) {
			$this->assign('has_kefu', 1);
		}

		$this->assign('info', $info);
		$this->assign('page_title', $info['shop_name']);
		$this->display();
	}

	protected function dokf($kf)
	{
		if ($kf) {
			$kf_tmp = array_filter(preg_split('/\\s+/', $kf));
			$kf_tmp = explode('|', $kf_tmp[0]);

			if (!empty($kf_tmp[1])) {
				$res = $kf_tmp[1];
			}
			else {
				$res = '';
			}
		}
		else {
			$res = '';
		}

		return $res;
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

	public function cmt($num)
	{
		if (4 <= $num) {
			$str = 't-first';
		}
		else if (3 < $num) {
			$str = 't-center';
		}
		else {
			$str = 't-low';
		}

		return $str;
	}

	public function boxbg($num)
	{
		if (4 <= $num) {
			$str = '';
		}
		else if (3 < $num) {
			$str = 'em-p-center';
		}
		else {
			$str = 'em-p-low';
		}

		return $str;
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
			}
		}

		$order_area = get_user_order_area($this->user_id);
		$user_area = get_user_area_reg($this->user_id);
		if ($order_area['province'] && 0 < $this->user_id) {
			$this->province_id = $order_area['province'];
			$this->city_id = $order_area['city'];
			$this->district_id = $order_area['district'];
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
			$this->region_id = get_warehouse_goods_region($this->province_id);
		}

		$this->area_info = get_area_info($this->province_id);
	}
}

?>
