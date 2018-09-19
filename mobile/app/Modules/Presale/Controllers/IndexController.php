<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Presale\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	protected $user_id = 0;
	protected $region_id = 0;
	protected $preid = 0;
	protected $area_info = array();

	public function __construct()
	{
		parent::__construct();
		$this->user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
		$this->init_params();
	}

	public function actionIndex()
	{
		$this->assign('pre_nav_list', get_pre_nav());
		$pre_goods = get_pre_cat();
		$this->assign('pre_cat_goods', $pre_goods);
		$this->assign('pre_list_url', url('presale/index/list'));
		$this->assign('pre_new_url', url('presale/index/new'));
		$this->assign('page_title', '预售频道');
		$this->display();
	}

	public function actionList()
	{
		$page = I('request.page', 1, 'intval');
		$size = 10;

		if (IS_AJAX) {
			$default_sort_order_method = C('shop.sort_order_method') == '0' ? 'DESC' : 'ASC';
			$default_sort_order_type = C('shop.sort_order_type') == '0' ? 'act_id' : (C('shop.sort_order_type') == '1' ? 'shop_price' : 'start_time');
			$sort = isset($_REQUEST['sort']) && in_array(trim(strtolower(I('sort'))), array('shop_price', 'start_time', 'act_id')) ? I('sort', '', array('htmlspecialchars', 'trim')) : $default_sort_order_type;
			$order = isset($_REQUEST['order']) && in_array(trim(strtoupper(I('order'))), array('ASC', 'DESC')) ? I('order', '', array('htmlspecialchars', 'trim')) : $default_sort_order_method;
			$cat_id = json_str_iconv(I('cat_id', 0));
			$status = I('status', 0, 'intval');
			$keyword = compile_str(I('keyword'));
			$pre_goods = $this->get_pre_goods($cat_id, $status, $sort, $order, $page, $size, $keyword);
			exit(json_encode(array('list' => $pre_goods['list'], 'totalPage' => ceil($pre_goods['total'] / $size))));
		}

		$cat_id = I('get.id', 0);
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('presale_cat') . ' ORDER BY sort_order ASC ';
		$cat_res = $GLOBALS['db']->getAll($sql);
		$page_title = '';

		foreach ($cat_res as $key => $row) {
			if (stristr($cat_id, $row['cat_id'])) {
				$cat_res[$key]['selected'] = 1;
				$page_title .= $cat_res[$key]['cat_name'];
			}

			$cat_res[$key]['goods'] = get_cat_goods($row['cat_id']);
			$cat_res[$key]['count_goods'] = count(get_cat_goods($row['cat_id']));
			$cat_res[$key]['cat_url'] = url('presale/index/list');
		}

		$page_title .= '抢先订_预售频道';
		$this->assign('pre_cat', $cat_res);
		$this->assign('cat_id', $cat_id);
		$this->assign('page_title', $page_title);
		$this->display();
	}

	public function actionNew()
	{
		$page = I('request.page', 1, 'intval');
		$size = 20;

		if (IS_AJAX) {
			$default_sort_order_method = C('shop.sort_order_method') == '0' ? 'DESC' : 'ASC';
			$default_sort_order_type = C('shop.sort_order_type') == '0' ? 'act_id' : (C('shop.sort_order_type') == '1' ? 'shop_price' : 'start_time');
			$sort = isset($_REQUEST['sort']) && in_array(trim(strtolower(I('sort'))), array('shop_price', 'start_time', 'act_id')) ? I('sort', '', array('htmlspecialchars', 'trim')) : $default_sort_order_type;
			$order = isset($_REQUEST['order']) && in_array(trim(strtoupper(I('order'))), array('ASC', 'DESC')) ? I('order', '', array('htmlspecialchars', 'trim')) : $default_sort_order_method;
			$cat_id = I('cat_id', 0);
			$status = I('status', 0, 'intval');
			$keyword = compile_str(I('keyword'));
			$now = gmtime();

			if (0 < $cat_id) {
				$catid = str_replace(',', '\',\'', $cat_id);
				$where .= ' AND a.cat_id in (\'' . $catid . '\') ';
			}

			if ($status == 1) {
				$where .= ' AND a.start_time > ' . $now . ' ';
			}
			else if ($status == 2) {
				$where .= ' AND a.start_time < ' . $now . ' AND ' . $now . ' < a.end_time ';
			}
			else if ($status == 3) {
				$where .= ' AND ' . $now . ' > a.end_time ';
			}
			else if ($status == 0) {
				$where .= ' AND a.start_time < ' . $now . ' AND ' . $now . ' < a.end_time ';
			}

			if ($keyword) {
				$where .= ' AND g.goods_name like \'%' . $keyword . '%\' ';
			}

			$sql = 'SELECT a.*, g.goods_thumb, g.goods_img, g.goods_name, g.shop_price, g.market_price, g.sales_volume FROM ' . $GLOBALS['ecs']->table('presale_activity') . ' AS a' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON a.goods_id = g.goods_id ' . (' WHERE g.goods_id > 0 ' . $where . ' AND g.is_on_sale = 0 AND a.review_status = 3 ORDER BY a.end_time DESC,a.start_time DESC ');
			$res = $GLOBALS['db']->getAll($sql);

			foreach ($res as $key => $val) {
				$res[$key]['thumb'] = get_image_path($val['goods_thumb']);
				$res[$key]['goods_img'] = get_image_path($val['goods_img']);
				$res[$key]['url'] = build_uri('presale', array('r' => 'index/detail', 'id' => $val['act_id']));
			}

			$pre_goods = $this->get_pre_goods($cat_id, $status, $sort, $order, $page, $size, $keyword);
			exit(json_encode(array('list' => $res, 'totalPage' => ceil($pre_goods['total'] / $size))));
		}

		$cat_id = I('get.id', 0);
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('presale_cat') . ' ORDER BY sort_order ASC ';
		$cat_res = $GLOBALS['db']->getAll($sql);
		$page_title = '';
		$this->assign('pre_cat', $cat_res);
		$this->assign('cat_id', $cat_id);
		$this->assign('page_title', '预售新品');
		$this->display();
	}

	public function actionDetail()
	{
		$this->preid = I('id');

		if ($this->preid <= 0) {
			ecs_header("Location: ./\n");
		}

		$presale = presale_info($this->preid, 1, array(), $this->region_id, $this->area_info['region_id']);

		if (empty($presale)) {
			ecs_header("Location: ./\n");
			exit();
		}

		$now = gmtime();
		$presale['gmt_end_date'] = local_strtotime($presale['end_time']);
		$presale['gmt_start_date'] = local_strtotime($presale['start_time']);

		if ($now <= $presale['gmt_start_date']) {
			$presale['no_start'] = 1;
		}

		$this->assign('presale', $presale);
		$this->goods_id = $presale['goods_id'];
		$goods = get_goods_info($this->goods_id, $this->region_id, $this->area_info['region_id']);

		if (empty($goods)) {
			ecs_header("Location: ./\n");
			exit();
		}

		$sql = 'SELECT COUNT(*) as num FROM {pre}order_info WHERE extension_id = \'' . $this->preid . '\'';
		$res = $GLOBALS['db']->getOne($sql);

		if ($res) {
			$goods['sales_volume'] = $res;
		}
		else {
			$goods['sales_volume'] = 0;
		}

		if ($_SESSION['user_id']) {
			$where['user_id'] = $_SESSION['user_id'];
			$where['goods_id'] = $this->goods_id;
			$rs = $this->db->table('collect_goods')->where($where)->count();

			if (0 < $rs) {
				$this->assign('goods_collect', 1);
			}
		}

		$this->assign('goods', $goods);
		$this->assign('type', 0);
		$mc_all = ments_count_all($this->goods_id);
		$mc_one = ments_count_rank_num($this->goods_id, 1);
		$mc_two = ments_count_rank_num($this->goods_id, 2);
		$mc_three = ments_count_rank_num($this->goods_id, 3);
		$mc_four = ments_count_rank_num($this->goods_id, 4);
		$mc_five = ments_count_rank_num($this->goods_id, 5);
		$comment_all = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);

		if (0 < $goods['user_id']) {
			$merchants_goods_comment = get_merchants_goods_comment($goods['user_id']);
			$this->assign('merch_cmt', $merchants_goods_comment);
		}

		$this->assign('comment_all', $comment_all);
		$good_comment = get_good_comment($this->goods_id, 4, 1, 0, 1);
		$this->assign('good_comment', $good_comment);
		$this->assign('goods_id', $this->goods_id);
		$sql = 'select b.is_IM, a.ru_id,a.province, a.city, a.kf_type, a.kf_ww, a.kf_qq, a.meiqia, a.shop_name, a.kf_appkey from {pre}seller_shopinfo as a left join {pre}merchants_shop_information as b on a.ru_id=b.shop_id where ru_id=\'' . $goods['user_id'] . '\' ';
		$basic_info = $this->db->getRow($sql);
		$info_ww = $basic_info['kf_ww'] ? explode("\r\n", $basic_info['kf_ww']) : '';
		$info_qq = $basic_info['kf_qq'] ? explode("\r\n", $basic_info['kf_qq']) : '';
		$kf_ww = $info_ww ? $info_ww[0] : '';
		$kf_qq = $info_qq ? $info_qq[0] : '';
		$basic_ww = $kf_ww ? explode('|', $kf_ww) : '';
		$basic_qq = $kf_qq ? explode('|', $kf_qq) : '';
		$basic_info['kf_ww'] = $basic_ww ? $basic_ww[1] : '';
		$basic_info['kf_qq'] = $basic_qq ? $basic_qq[1] : '';
		if (($basic_info['is_IM'] == 1 || $basic_info['ru_id'] == 0) && !empty($basic_info['kf_appkey'])) {
			$basic_info['kf_appkey'] = $basic_info['kf_appkey'];
		}
		else {
			$basic_info['kf_appkey'] = '';
		}

		$basic_date = array('region_name');
		$basic_info['province'] = get_table_date('region', 'region_id = \'' . $basic_info['province'] . '\'', $basic_date, 2);
		$basic_info['city'] = get_table_date('region', 'region_id= \'' . $basic_info['city'] . '\'', $basic_date, 2) . '市';
		$this->assign('basic_info', $basic_info);
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

		$this->assign('default_spe', $default_spe);
		$this->assign('specification', $properties['spe']);
		$sql = 'SELECT * FROM {pre}goods_gallery WHERE goods_id = ' . $this->goods_id;
		$goods_img = $this->db->query($sql);

		foreach ($goods_img as $key => $val) {
			$goods_img[$key]['img_url'] = get_image_path($val['img_url']);
		}

		$this->assign('goods_img', $goods_img);
		$this->assign('province_row', get_region_name($this->province_id));
		$this->assign('city_row', get_region_name($this->city_id));
		$this->assign('district_row', get_region_name($this->district_id));
		$goods_region['country'] = 1;
		$goods_region['province'] = $this->province_id;
		$goods_region['city'] = $this->city_id;
		$goods_region['district'] = $this->district_id;
		$this->assign('goods_region', $goods_region);
		$this->assign('best_goods', get_recommend_goods('best', '', $this->region_id, $this->area_info['region_id'], $goods['user_id'], 1, 'presale'));
		$this->assign('new_goods', get_recommend_goods('new', '', $this->region_id, $this->area_info['region_id'], $goods['user_id'], 1, 'presale'));
		$this->assign('hot_goods', get_recommend_goods('hot', '', $this->region_id, $this->area_info['region_id'], $goods['user_id'], 1, 'presale'));
		$shop_info = get_merchants_shop_info('merchants_steps_fields', $goods['user_id']);
		$adress = get_license_comp_adress($shop_info['license_comp_adress']);
		$this->assign('shop_info', $shop_info);
		$this->assign('adress', $adress);
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
		$this->assign('goods_id', $this->goods_id);
		$warehouse_list = get_warehouse_list_goods();
		$this->assign('warehouse_list', $warehouse_list);
		$warehouse_name = get_warehouse_name_id($this->region_id);
		$this->assign('warehouse_name', $warehouse_name);
		$this->assign('region_id', $this->region_id);
		$this->assign('user_id', $_SESSION['user_id']);
		$this->assign('shop_price_type', $goods['model_price']);
		$this->assign('area_id', $this->area_info['region_id']);
		$area = array('region_id' => $this->region_id, 'province_id' => $this->province_id, 'city_id' => $this->city_id, 'district_id' => $this->district_id, 'goods_id' => $this->goods_id, 'user_id' => $_SESSION['user_id'], 'area_id' => $this->area_info['region_id'], 'merchant_id' => $goods['user_id']);
		$this->assign('area', $area);
		$this->assign('cfg', C('shop'));
		$position = assign_ur_here(0, $goods['goods_name']);
		$this->assign('page_title', $position['title']);
		$share_data = array('title' => '预售商品_' . $goods['goods_name'], 'desc' => $presale['act_name'], 'link' => '', 'img' => $goods['goods_img']);
		$this->assign('share_data', $this->get_wechat_share_content($share_data));
		$info = $this->db->table('goods')->field('goods_desc,desc_mobile')->where(array('goods_id' => $this->goods_id))->find();
		$properties = get_goods_properties($this->goods_id, $this->region_id, $this->area_info['region_id']);
		$sql = 'SELECT ld.goods_desc FROM {pre}link_desc_goodsid AS dg, {pre}link_goods_desc AS ld WHERE dg.goods_id = ' . $this->goods_id . '  AND dg.d_id = ld.id AND ld.review_status > 2';
		$link_desc = $this->db->getOne($sql);

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
			$goods_desc = $link_desc;
		}

		$goods_desc = preg_replace('/height\\="[0-9]+?"/', '', $goods_desc);
		$goods_desc = preg_replace('/width\\="[0-9]+?"/', '', $goods_desc);
		$goods_desc = preg_replace('/style=.+?[*|"]/i', '', $goods_desc);
		$this->assign('goods_desc', $goods_desc);
		$this->assign('properties', $properties['pro']);
		$this->display();
	}

	public function actionPrice()
	{
		$res = array('err_msg' => '', 'err_no' => 0, 'result' => '', 'qty' => 1);
		$attr = I('attr');
		$number = I('number', 1, 'intval');
		$this->goods_id = isset($_REQUEST['gid']) ? intval($_REQUEST['gid']) : 0;
		$this->preid = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$attr_id = !empty($attr) ? explode(',', $attr) : array();
		$warehouse_id = I('request.warehouse_id', 0, 'intval');
		$area_id = I('request.area_id', 0, 'intval');
		$onload = I('request.onload', '', array('htmlspecialchars', 'trim'));
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
			$attr_number = $products['product_number'];

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
			else if (empty($prod)) {
				$attr_number = $goods['goods_number'];
			}

			if (empty($prod)) {
				$res['bar_code'] = $goods['bar_code'];
			}
			else {
				$res['bar_code'] = $products['bar_code'];
			}

			$attr_number = !empty($attr_number) ? $attr_number : 0;
			$res['attr_number'] = $attr_number;
			$shop_price = get_final_price($this->goods_id, $number, true, $attr_id, $warehouse_id, $area_id);
			$res['shop_price'] = price_format($shop_price);
			$res['market_price'] = $goods['market_price'];
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

			$res['marketPrice_amount'] = price_format($spec_price + $goods['marketPrice']);
			$martetprice_amount = $spec_price + $goods['marketPrice'];
			$res['discount'] = round($shop_price / $martetprice_amount, 2) * 10;
			$presale = presale_info($this->preid, $number, $attr_id, $warehouse_id, $area_id);
			$res['formated_deposit'] = $presale['formated_deposit'];
			$res['formated_final_payment'] = $presale['formated_final_payment'];
			$res['formated_final_payment_new'] = price_format($shop_price - $presale['deposit']);
			$attr_info = get_attr_value($this->goods_id, $attr_id[0]);

			if (!empty($attr_info['attr_img_flie'])) {
				$res['attr_img'] = get_image_path($attr_info['attr_img_flie']);
			}
		}

		exit(json_encode($res));
	}

	public function actionBuy()
	{
		$this->check_login();
		$warehouse_id = I('request.warehouse_id', 0, 'intval');
		$area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
		$presale_id = I('request.presale_id', 0, 'intval');

		if ($presale_id <= 0) {
			ecs_header("Location: ./\n");
			exit();
		}

		$number = isset($_POST['number']) ? intval($_POST['number']) : 1;
		$number = $number < 1 ? 1 : $number;
		$presale = presale_info($presale_id, $number);

		if (empty($presale)) {
			ecs_header("Location: ./\n");
			exit();
		}

		if ($presale['status'] != GBS_UNDER_WAY) {
			show_message(L('presale_error_status'), '', '', 'error');
		}

		$goods = goods_info($presale['goods_id'], $warehouse_id, $area_id);

		if (empty($goods)) {
			ecs_header("Location: ./\n");
			exit();
		}

		if (0 < $goods['goods_number'] && $goods['goods_number'] - $presale['valid_goods'] < $number) {
			show_message(L('gb_error_goods_lacking'), '', '', 'error');
		}

		$specs = isset($_POST['goods_spec']) ? htmlspecialchars(trim($_POST['goods_spec'])) : '';

		if ($specs) {
			$_specs = explode(',', $specs);
			$product_info = get_products_info($goods['goods_id'], $_specs, $warehouse_id, $area_id);
		}

		empty($product_info) ? $product_info = array('product_number' => 0, 'product_id' => 0) : '';

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

		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . ' WHERE goods_id = \'' . $goods['goods_id'] . '\'' . $type_files . ' LIMIT 0, 1';
		$prod = $GLOBALS['db']->getRow($sql);

		if ($GLOBALS['_CFG']['use_storage'] == 1) {
			if ($prod && $product_info['product_number'] < $number) {
				show_message(L('gb_error_goods_lacking'), '', '', 'error');
			}
			else if ($goods['goods_number'] < $number) {
				show_message(L('gb_error_goods_lacking'), '', '', 'error');
			}
		}

		$attr_list = array();
		$sql = 'SELECT a.attr_name, g.attr_value ' . 'FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS g, ' . $GLOBALS['ecs']->table('attribute') . ' AS a ' . 'WHERE g.attr_id = a.attr_id ' . 'AND g.goods_attr_id ' . db_create_in($specs);
		$res = $GLOBALS['db']->query($sql);

		foreach ($res as $row) {
			$attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
		}

		$goods_attr = join(chr(13) . chr(10), $attr_list);
		clear_cart(CART_PRESALE_GOODS);
		$area_info = get_area_info($this->province_id);
		$area_id = $area_info['region_id'];
		$where = 'regionId = \'' . $this->province_id . '\'';
		$date = array('parent_id');
		$this->region_id = get_table_date('region_warehouse', $where, $date, 2);

		if (!empty($_SESSION['user_id'])) {
			$sess = '';
		}
		else {
			$sess = real_cart_mac_ip();
		}

		$nowTime = gmtime();
		$start_date = $goods['xiangou_start_date'];
		$end_date = $goods['xiangou_end_date'];
		if ($goods['is_xiangou'] == 1 && $start_date < $nowTime && $nowTime < $end_date) {
			if ($goods['xiangou_num'] <= $presale['total_goods']) {
				$result['message'] = '您已购买过' . $goods['goods_name'] . '，超过限购数量无法再购买';
				show_message($result['message'], '', '', 'error');
			}
			else if (0 < $goods['xiangou_num']) {
				if ($goods['is_xiangou'] == 1 && $goods['xiangou_num'] < $presale['total_goods'] + $number) {
					$result['message'] = '该' . $goods['goods_name'] . '商品已经累计超过限购数量';
					$number = $goods['xiangou_num'] - $presale['total_goods'];
				}
			}
		}

		if ($GLOBALS['_CFG']['add_shop_price'] == 0 && empty($goods['goods_price']) && empty($prod)) {
			$goods['goods_price'] = $shop_price;
		}

		if ($GLOBALS['_CFG']['add_shop_price'] == 1) {
			$goods['goods_price'] = $goods['goods_price'] + $product_info['product_price'];
		}
		else {
			$goods['goods_price'] = $product_info['product_price'];
		}

		$cart = array('user_id' => $_SESSION['user_id'], 'session_id' => $sess, 'goods_id' => $presale['goods_id'], 'product_id' => $product_info['product_id'], 'goods_sn' => addslashes($goods['goods_sn']), 'goods_name' => addslashes($goods['goods_name']), 'market_price' => $goods['market_price'], 'goods_price' => $goods['goods_price'], 'goods_number' => $number, 'goods_attr' => addslashes($goods_attr), 'goods_attr_id' => $specs, 'ru_id' => $goods['user_id'], 'warehouse_id' => $this->region_id, 'area_id' => $area_id, 'is_real' => $goods['is_real'], 'extension_code' => 'presale', 'parent_id' => 0, 'rec_type' => CART_PRESALE_GOODS, 'is_gift' => 0);
		$this->db->autoExecute($GLOBALS['ecs']->table('cart'), $cart, 'INSERT');
		$_SESSION['flow_type'] = CART_PRESALE_GOODS;
		$_SESSION['extension_code'] = 'presale';
		$_SESSION['cart_value'] = '';
		$_SESSION['extension_id'] = $presale['act_id'];
		$_SESSION['browse_trace'] = 'presale';
		$this->redirect('flow/index/index');
	}

	private function check_login()
	{
		if (!$_SESSION['user_id']) {
			$url = urlencode(__HOST__ . $_SERVER['REQUEST_URI']);

			if (IS_POST) {
				$url = urlencode($_SERVER['HTTP_REFERER']);
			}

			ecs_header('Location: ' . url('user/login/index', array('back_act' => $url)));
			exit();
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

	protected function get_pre_goods($cat_id, $status = 0, $sort = 'cat_id', $order = 'DESC', $page = 1, $size = 10, $keyword = '')
	{
		$now = gmtime();
		$where = '';

		if (0 < $cat_id) {
			$cat_id = str_replace(',', '\',\'', $cat_id);
			$where = 'AND a.cat_id in (\'' . $cat_id . '\') ';
		}

		if ($status == 1) {
			$where .= ' AND a.start_time > ' . $now . ' ';
		}
		else if ($status == 2) {
			$where .= ' AND a.start_time < ' . $now . ' AND ' . $now . ' < a.end_time ';
		}
		else if ($status == 3) {
			$where .= ' AND ' . $now . ' > a.end_time ';
		}

		if ($sort == 'shop_price') {
			$sort = 'g.' . $sort;
		}
		else {
			$sort = 'a.' . $sort;
		}

		if ($keyword) {
			$where .= ' AND g.goods_name like \'%' . $keyword . '%\' ';
		}

		$sql = 'SELECT COUNT(*) as total FROM ' . $GLOBALS['ecs']->table('presale_activity') . ' AS a ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON a.goods_id = g.goods_id ' . (' WHERE g.goods_id > 0 AND a.review_status = 3 and g.is_on_sale = 0 ' . $where);
		$total = $GLOBALS['db']->getOne($sql);
		$total ? $total : 0;
		$sql = 'SELECT a.*, g.goods_thumb, g.goods_img, g.goods_name, g.shop_price, g.market_price, g.sales_volume FROM ' . $GLOBALS['ecs']->table('presale_activity') . ' AS a ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON a.goods_id = g.goods_id ' . (' WHERE g.goods_id > 0 ' . $where . ' AND g.is_on_sale = 0 AND a.review_status > 2 ORDER BY ' . $sort . ' ' . $order . ' LIMIT ') . ($page - 1) * $size . (',  ' . $size);
		$res = $GLOBALS['db']->getAll($sql);

		foreach ($res as $key => $row) {
			$res[$key]['thumb'] = get_image_path($row['goods_thumb']);
			$res[$key]['goods_img'] = get_image_path($row['goods_img']);
			$res[$key]['url'] = build_uri('presale', array('r' => 'index/detail', 'id' => $row['act_id']));

			if ($now <= $row['start_time']) {
				$res[$key]['status'] = 1;
				$res[$key]['short_format_date'] = short_format_date($row['start_time']);
			}
			else if ($row['end_time'] < $now) {
				$res[$key]['status'] = 3;
			}
			else {
				$res[$key]['short_format_date'] = short_format_date($row['end_time']);
			}
		}

		return array('total' => $total, 'list' => $res);
	}
}

?>
