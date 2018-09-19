<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Category\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	protected $cat_id = 0;
	protected $page = 1;
	protected $size = 10;
	protected $brand = 0;
	protected $price_min = 0;
	protected $price_max = 0;
	protected $keyword = '';
	protected $keywords = '';
	protected $intro = '';
	protected $filter_attr = 0;
	protected $sort = 'last_update';
	protected $order = 'ASC';
	protected $display;
	protected $ext;
	protected $children;
	protected $region_id;
	protected $area_id;
	protected $ubrand;
	protected $isself = 0;
	protected $cat = array();
	protected $hasgoods = 0;
	protected $promotion = 0;
	protected $cou_id = 0;

	public function __construct()
	{
		parent::__construct();
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
		header('Access-Control-Allow-Headers: X-HTTP-Method-Override, Content-Type, x-requested-with, Authorization');
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		$this->assign('lang', array_change_key_case(L()));
		$this->cat_id = I('request.id', 0, 'intval');
	}

	public function actionIndex()
	{
		uaredirect(__PC__ . '/categoryall.php');
		$category = S('category0');

		if (!$category) {
			$category = get_child_tree(0);
			S('category0', $category);
		}

		$this->assign('cat_id', $this->cat_id);
		$this->assign('category', $category);
		$this->assign('page_title', L('all_category'));

		if (C('shop.wap_category') == '1') {
			$this->display('simple');
		}
		else {
			$this->display();
		}
	}

	public function actionSimple()
	{
		$category = get_child_tree_new(0);
		$this->response(array('error' => 0, 'category' => $category));
	}

	public function actionChildcategory()
	{
		if (IS_AJAX) {
			if (empty($this->cat_id)) {
				exit(json_encode(array('code' => 1, 'message' => '请选择分类')));
			}

			if (APP_DEBUG) {
				$category = get_child_tree($this->cat_id);
			}
			else {
				$category = S('categorys' . $this->cat_id);

				if ($category === false) {
					$category = get_child_tree($this->cat_id);
					S('category' . $this->cat_id, $category);
				}
			}

			exit(json_encode(array('category' => $category)));
		}
	}

	public function actionProducts()
	{
		$this->init_params();

		if (IS_AJAX) {
			$cache_id = md5(serialize($_REQUEST));
			$goodslist = S($cache_id);
			$this->ext = I('get.ext', '', array('htmlspecialchars', 'trim'));

			if ($goodslist === false) {
				$goodslist = category_get_goods($this->keywords, $this->children, $this->intro, $this->brand, $this->price_min, $this->price_max, $this->ext, $this->size, $this->page, $this->sort, $this->order, $this->region_id, $this->area_id, $this->ubrand, $this->hasgoods, $this->promotion, $this->cou_id);

				foreach ($goodslist['list'] as $key => $val) {
					$arr = get_goods_properties($val['goods_id'], $this->region_id, $this->area_id);
					$goodslist['list'][$key]['spe'] = $arr['spe'];
					$goodslist['list'][$key]['goods_name'] = add_highlight($val['goods_name'], $this->keyword);
				}

				S($cache_id, $goodslist);
			}

			exit(json_encode(array('list' => $goodslist['list'], 'totalPage' => $goodslist['totalpage'])));
		}

		$cat_info = get_cat_info($this->cat_id);
		if (empty($cat_info) && !isset($_REQUEST['keyword']) && !isset($_GET['intro'])) {
			$this->redirect('/');
		}

		$province_id = isset($_COOKIE['province']) ? $_COOKIE['province'] : 0;
		$city_id = isset($_COOKIE['city']) ? $_COOKIE['city'] : 0;
		$district_id = isset($_COOKIE['district']) ? $_COOKIE['district'] : 0;
		$user_id = $_SESSION['user_id'] ? intval($_SESSION['user_id']) : 0;
		$province_list = get_warehouse_province();
		$this->assign('province_list', $province_list);
		$city_list = get_region_city_county($province_id);

		if ($city_list) {
			foreach ($city_list as $k => $v) {
				$city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
			}
		}

		$hasdistrict = get_isHas_area($city_id);
		$district_row = array();

		if ($hasdistrict) {
			$district_row = get_region_name($district_id);
		}

		$cat_goods = get_cagtegory_goods($this->keywords, $this->cat_id, $this->sort, $this->order, 1);
		$cat_goods = reset($cat_goods);
		$seo = get_seo_words('category', $this->cat_id);

		foreach ($seo as $key => $value) {
			$seo[$key] = html_in(str_replace(array('{sitename}', '{key}', '{name}', '{description}'), array(C('shop.shop_name'), $cat_info['keywords'], $cat_info['cat_name'], $cat_info['cat_desc']), $value));
		}

		$page_title = !empty($seo['title']) ? $seo['title'] : $cat_info['cat_name'];
		$meta_keywords = !empty($seo['keywords']) ? $seo['keywords'] : (!empty($cat_info['keywords']) ? $cat_info['keywords'] : C('shop.shop_keywords'));
		$description = !empty($seo['description']) ? $seo['description'] : (!empty($cat_info['cat_desc']) ? $cat_info['cat_desc'] : C('shop.shop_desc'));

		if (!empty($this->keyword)) {
			$page_title = '搜索商品_' . $this->keyword;
			$description = isset($cat_goods['goods_name']) ? $cat_goods['goods_name'] : C('shop.shop_desc');
		}
		else {
			$this->assign('keywords', $meta_keywords);
		}

		$share_img = !empty($cat_goods['goods_img']) ? $cat_goods['goods_img'] : $cat_info['cat_icon'];
		$share_data = array('title' => $page_title, 'desc' => $description, 'link' => '', 'img' => $share_img);
		$this->assign('share_data', $this->get_wechat_share_content($share_data));
		$this->assign('page_title', $page_title);
		$this->assign('description', $description);
		$this->assign('province_id', $province_id);
		$this->assign('city_id', $city_id);
		$this->assign('district_id', $district_id);
		$this->assign('province_row', get_region_name($province_id));
		$this->assign('city_row', get_region_name($city_id));
		$this->assign('district_row', $district_row);
		$this->assign('city_list', $city_list);
		$this->assign('user_id', $user_id);
		$this->assign('cat_id', $this->cat_id);
		$this->assign('area_id', $this->area_id);
		$this->assign('warehouse_id', $this->region_id);
		$this->display('products');
	}

	public function actionAttr()
	{
		if (IS_POST) {
			$this->init_params();
			$warehouse_id = $this->region_id;
			$area_id = $this->area_id;
			$goods_id = input('goods_id', 0, 'intval');
			$attr = get_goods_properties($goods_id, $warehouse_id, $area_id);
			$this->response(array('error' => 0, 'attr' => $attr['spe']));
		}
	}

	public function actionCartId()
	{
		if (IS_POST) {
			$goods_id = input('goods_id', 0, 'intval');
			$user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

			if ($user_id == 0) {
				$session_id = real_cart_mac_ip();
			}
			else {
				$session_id = '';
			}

			$cart_id = dao('cart')->field('rec_id, goods_number')->where(array('goods_id' => $goods_id, 'user_id' => $user_id, 'session_id' => $session_id))->find();
			$this->response(array('error' => 0, 'attr' => $attr['spe']));
		}
	}

	public function actionAttrNum()
	{
		if (IS_POST) {
			$this->init_params();
			$warehouse_id = $this->region_id;
			$area_id = $this->area_id;
			$goods_id = input('goods_id', 0, 'intval');
			$attr = input('attr');
			$goods_attr = input('goods_attr');
			$attr = get_goods_properties($goods_id, $warehouse_id, $area_id);
			$products = get_warehouse_id_attr_number($goods_id, $attr, $goods_attr, $warehouse_id, $area_id);
			$attr_number = $products['product_number'];
			$this->response(array('error' => 0, 'attr_number' => $attr_number));
		}
	}

	public function actionGoods()
	{
		if (IS_POST) {
			$this->init_params();
			$cat = input('cat_id', 0, 'intval');
			$brand = input('brand_id', 0, 'intval');
			$type = input('type');
			$warehouse_id = $this->region_id;
			$area_id = $this->area_id;
			$pageSize = input('pageSize', 10, 'intval');
			$currentPage = input('currentPage', 1, 'intval');

			if ($cat == 0) {
				$this->children = 0;
			}
			else {
				$this->children = get_children($cat);
			}

			$goodslist = category_get_goods('', $this->children, $type, $this->brand, $this->price_min, $this->price_max, $this->ext, $pageSize, $currentPage, $this->sort, $this->order, $this->region_id, $this->area_id, $this->ubrand, $this->hasgoods, $this->promotion);

			foreach ($goodslist['list'] as $key => $val) {
				$attr = get_goods_properties($val['goods_id'], $warehouse_id, $area_id);

				if ($attr['spe']) {
					$goodslist['list'][$key]['attr_type'] = 1;
				}
				else {
					$goodslist['list'][$key]['attr_type'] = 0;
				}
			}

			$this->response(array('error' => 0, 'goodslist' => $goodslist['list']));
		}
	}

	public function actionSearch()
	{
		$type_select = input('type_select');
		$keywords = input('keyword', '', array('htmlspecialchars', 'addslashes'));

		if ($type_select == 1) {
			$this->redirect('store/index/index', array('where' => $keywords, 'type' => 2));
		}

		$this->actionProducts();
	}

	public function actionClearHistory()
	{
		if (IS_AJAX) {
			cookie('ECS[keywords]', null);
			echo json_encode(array('status' => 1));
		}
		else {
			echo json_encode(array('status' => 0));
		}
	}

	protected function init_params()
	{
		$keyword = I('request.keyword', '', array('htmlspecialchars', 'addslashes'));
		$this->keyword = $keyword;

		if (!empty($keyword)) {
			$scws = new \App\Extensions\Scws4();
			$keyword_segmentation = $scws->segmentate($keyword, true);
			$keywordArr = explode(',', $keyword_segmentation);
			$type_select = input('type_select');
			if (!IS_AJAX && $type_select == 1) {
				$this->redirect('store/index/index', array('type' => 2, 'where' => $keyword));
			}

			$this->keywords = 'AND (';
			$addAll = array();

			foreach ($keywordArr as $keywordKey => $keywordVal) {
				if (0 < $keywordKey) {
					$this->keywords .= ' AND ';
				}

				$val = mysql_like_quote(trim($keywordVal));
				$this->keywords .= '(goods_name LIKE \'%' . $val . '%\' OR goods_sn LIKE \'%' . $val . '%\' OR keywords LIKE \'%' . $val . '%\')';
				$valArr[] = $val;
				$data = array('date' => local_date('Y-m-d'), 'searchengine' => 'ECTouch', 'keyword' => addslashes(str_replace('%', '', $val)), 'count' => 1);
				$condition['date'] = local_date('Y-m-d');
				$condition['searchengine'] = 'ECTouch';
				$condition['keyword'] = addslashes(str_replace('%', '', $val));
				$set = $this->db->table('keywords')->where($condition)->find();

				if (!empty($set)) {
					$data['count'] = $set['count'] + 1;
				}

				$addAll[] = $data;
			}

			$this->db->addAll($addAll, array('table' => $this->ecs->table('keywords')), true);
			$this->keywords .= ')';
			$goods_ids = array();
			$valArrWhere = ' 1';

			foreach ($valArr as $v) {
				$valArrWhere .= ' OR tag_words LIKE \'%' . $v . '%\' ';
			}

			$sql = 'SELECT DISTINCT goods_id FROM ' . $this->ecs->table('tag') . ' WHERE ' . $valArrWhere;

			if (!empty($tag_id)) {
				$this->keywords .= ' OR g.goods_id in (' . $sql . ') ';
			}

			$history = '';

			if (!empty($_COOKIE['ECS']['keywords'])) {
				$history = explode(',', $_COOKIE['ECS']['keywords']);
				array_unshift($history, $keyword);
				$history = array_unique($history);
				cookie('ECS[keywords]', implode(',', $history));
			}
			else {
				cookie('ECS[keywords]', $keyword);
			}

			$this->assign('history_keywords', $history);
		}

		$filter_attr_str = I('request.filter_attr', '', array('trim', 'html_in'));
		$filter_attr_str = trim(urldecode($filter_attr_str));
		$filter_attr_str = preg_match('/^[\\d,\\.]+$/', $filter_attr_str) ? $filter_attr_str : '';
		$this->filter_attr = empty($filter_attr_str) ? '' : explode('.', $filter_attr_str);
		$this->size = I('request.size', 10);
		$this->size = 100 < $this->size ? 100 : $this->size;
		$asyn_last = I('request.last', 0, 'intval') + 1;
		$this->page = I('request.page', 1, 'intval');
		$this->brand = I('request.brand', '', array('trim', 'html_in'));
		$this->intro = I('request.intro');
		$this->price_min = I('request.price_min', 0, 'intval');
		$this->price_max = I('request.price_max', 0, 'intval');
		$this->isself = I('request.isself', 0, 'intval');
		$this->ship = I('request.ship', 0, 'intval');
		$this->hasgoods = I('request.hasgoods', 0, 'intval');
		$this->promotion = I('request.promotion', 0, 'intval');
		$default_display_type = C('shop.show_order_type') == '0' ? 'list' : (C('shop.show_order_type') == '1' ? 'grid' : 'text');
		$default_sort_order_type = C('shop.sort_order_type') == '0' ? 'goods_id' : (C('shop.sort_order_type') == '1' ? 'shop_price' : 'last_update');
		$default_sort_order_method = C('shop.sort_order_method') == '0' ? 'desc' : 'asc';
		$sort_array = array('goods_id', 'shop_price', 'last_update', 'sales_volume');
		$order_array = array('asc', 'desc');
		$display_array = array('list', 'grid', 'text');
		$goods_sort = I('request.sort');
		$goods_order = I('request.order');
		$goods_display = I('request.display');
		$this->sort = in_array($goods_sort, $sort_array) ? $goods_sort : $default_sort_order_type;
		$this->order = in_array($goods_order, $order_array) ? $goods_order : $default_sort_order_method;
		$this->display = in_array($goods_display, $display_array) ? $goods_display : (isset($_COOKIE['display']) ? $_COOKIE['display'] : $default_display_type);
		cookie('display', $this->display);
		$sql = 'select parent_id from ' . $this->ecs->table('category') . (' where cat_id = \'' . $this->cat_id . '\'');
		$parent_id = $this->db->getOne($sql);
		$sql = 'select parent_id from ' . $this->ecs->table('category') . (' where cat_id = \'' . $parent_id . '\'');
		$parentCat = $this->db->getOne($sql);
		$province_id = isset($_COOKIE['province']) ? $_COOKIE['province'] : 0;
		$area_info = get_area_info($province_id);
		$this->area_id = $area_info['region_id'];
		$where = 'regionId = \'' . $province_id . '\'';
		$date = array('parent_id');
		$region_id = get_table_date('region_warehouse', $where, $date, 2);
		$this->region_id = isset($_COOKIE['region_id']) ? $_COOKIE['region_id'] : $region_id;

		if ($this->cat_id == 0) {
			$this->children = 0;
		}
		else {
			$this->children = get_children($this->cat_id);
		}

		$this->cat = get_cat_info($this->cat_id);
		if ($this->cat['grade'] == 0 && $this->cat['parent_id'] != 0) {
			$this->cat['grade'] = get_parent_grade($this->cat_id);
		}

		$leftJoin = '';
		$tag_where = '';

		if (C('shop.open_area_goods') == 1) {
			$leftJoin .= ' left join ' . $this->ecs->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
			$tag_where = ' and lag.region_id = \'' . $this->area_id . '\' ';
		}

		if (1 < $this->cat['grade']) {
			$mm_shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr ';
			$leftJoin .= ' left join ' . $this->ecs->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $region_id . '\' ');
			$leftJoin .= ' left join ' . $this->ecs->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $this->area_id . '\' ');
			$sql = 'SELECT min(IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price))) AS min, ' . ' max(IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price))) as max ' . ' FROM ' . $this->ecs->table('goods') . ' AS g ' . $leftJoin . (' WHERE (' . $this->children . ' OR ') . get_extension_goods($this->children) . ') AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1' . $tag_where;
			$row = $this->db->getRow($sql);
			$price_grade = 0.0001;

			for ($i = -2; $i <= log10($row['max']); $i++) {
				$price_grade *= 10;
			}

			$dx = ceil(($row['max'] - $row['min']) / $this->cat['grade'] / $price_grade) * $price_grade;

			if ($dx == 0) {
				$dx = $price_grade;
			}

			for ($i = 1; $dx * $i < $row['min']; $i++) {
			}

			for ($j = 1; $dx * ($i - 1) + $price_grade * $j < $row['min']; $j++) {
			}

			for ($row['min'] = $dx * ($i - 1) + $price_grade * ($j - 1); $dx * $i <= $row['max']; $i++) {
			}

			$row['max'] = $dx * $i + $price_grade * ($j - 1);
			$sql = 'SELECT (FLOOR((IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) - ' . $row['min'] . ') / ' . $dx . ')) AS sn, COUNT(*) AS goods_num  ' . ' FROM ' . $this->ecs->table('goods') . ' AS g ' . $leftJoin . (' WHERE (' . $this->children . ' OR ') . get_extension_goods($this->children) . ') AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1' . ' GROUP BY sn ';
			$price_grade = $this->db->getAll($sql);

			foreach ($price_grade as $key => $val) {
				if ($val['sn'] != '') {
					$temp_key = $key;
					$price_grade[$temp_key]['goods_num'] = $val['goods_num'];
					$price_grade[$temp_key]['start'] = $row['min'] + round($dx * $val['sn']);
					$price_grade[$temp_key]['end'] = $row['min'] + round($dx * ($val['sn'] + 1));
					$price_grade[$temp_key]['price_range'] = $price_grade[$temp_key]['start'] . '&nbsp;-&nbsp;' . $price_grade[$temp_key]['end'];
					$price_grade[$temp_key]['formated_start'] = price_format($price_grade[$temp_key]['start']);
					$price_grade[$temp_key]['formated_end'] = price_format($price_grade[$temp_key]['end']);
					$price_grade[$temp_key]['url'] = build_uri('category', array('id' => $this->cat_id, 'bid' => $this->brand, 'price_min' => $price_grade[$temp_key]['start'], 'price_max' => $price_grade[$temp_key]['end'], 'filter_attr' => $filter_attr_str), $this->cat['cat_name']);
					if (isset($_REQUEST['price_min']) && $price_grade[$temp_key]['start'] == $this->price_min && $price_grade[$temp_key]['end'] == $this->price_max) {
						$price_grade[$temp_key]['selected'] = 1;
					}
					else {
						$price_grade[$temp_key]['selected'] = 0;
					}
				}
			}

			$this->assign('price_grade', $price_grade);
		}

		if (empty($row)) {
			$row['min'] = 0;
			$row['max'] = 10000;
		}

		$this->assign('price_range', $row);

		if (1000 < $row['min']) {
			$range_step = 100;
		}
		else {
			$range_step = 10;
		}

		$this->assign('range_step', $range_step);
		$brand_tag_where = '';
		$brand_leftJoin = '';

		if (C('shop.open_area_goods') == 1) {
			$brand_select = ' , ( SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('link_area_goods') . (' as lag WHERE lag.goods_id = g.goods_id AND lag.region_id = \'' . $this->area_id . '\' LIMIT 1) AS area_goods_num ');
			$where_having = ' AND area_goods_num > 0 ';
		}

		if (C('shop.review_goods') == 1) {
			$brand_tag_where .= ' AND g.review_status > 2 ';
		}

		$sql = 'SELECT b.brand_id, b.brand_name, b.brand_logo, COUNT(*) AS goods_num ' . $brand_select . 'FROM ' . $GLOBALS['ecs']->table('brand') . 'AS b ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . (' AS g ON g.brand_id = b.brand_id AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . $brand_tag_where . ' ') . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_cat') . ' AS gc ON g.goods_id = gc.goods_id ' . (' WHERE ' . $this->children . ' OR ') . 'gc.cat_id ' . db_create_in(array_unique(array_merge(array($this->cat_id), array_keys(cat_list($this->cat_id, 0))))) . ' AND b.is_show = 1 ' . ('GROUP BY b.brand_id HAVING goods_num > 0 ' . $where_having . ' ORDER BY b.sort_order, b.brand_id ASC');
		$brands = $GLOBALS['db']->getAll($sql);
		$brands_selected = explode(',', $this->brand);

		foreach ($brands as $key => $val) {
			$temp_key = $key + 1;
			$brands[$temp_key]['brand_id'] = $val['brand_id'];
			$brands[$temp_key]['brand_name'] = $val['brand_name'];
			$brands[$temp_key]['url'] = url('products', array('id' => $this->cat_id, 'brand' => $val['brand_id'], 'price_min' => $this->price_min, 'price_max' => $this->price_max, 'filter_attr' => $this->filter_attr));

			if (in_array($val['brand_id'], $brands_selected)) {
				$brands[$temp_key]['selected'] = 1;
			}
			else {
				$brands[$temp_key]['selected'] = 0;
			}
		}

		unset($brands[0]);
		$brands[0]['brand_id'] = 0;
		$brands[0]['brand_name'] = L('all_attribute');
		$brands[0]['url'] = url('products', array('cid' => $this->cat_id, 'brand' => 0, 'price_min' => $this->price_min, 'price_max' => $this->price_max, 'filter_attr' => $this->filter_attr));
		$brands[0]['selected'] = empty($this->brand) ? 1 : 0;
		ksort($brands);
		$this->assign('brands', $brands);

		if (!empty($this->brand)) {
			$sql = 'SELECT brand_name FROM ' . $this->ecs->table('brand') . ' WHERE brand_id ' . db_create_in($this->brand);
			$brand_name_arr = $this->db->getCol($sql);
			$brand_name = implode('、', $brand_name_arr);
		}
		else {
			$brand_name = L('all_attribute');
		}

		$this->assign('brand_name', $brand_name);
		$this->ubrand = I('request.ubrand', 0, 'intval');
		$this->assign('ubrand', $this->ubrand);
		$this->ext = '';

		if ($this->isself == 1) {
			$this->ext .= ' AND (g.user_id = 0 or msi.self_run = 1) ';
		}

		if ($this->ship == 1) {
			$this->ext .= ' AND g.is_shipping = 1 ';
		}

		if (0 < $this->cat['filter_attr']) {
			$this->cat['filter_attr'] = get_del_str_comma($this->cat['filter_attr'], 0);
			$this->cat_filter_attr = explode(',', $this->cat['filter_attr']);
			$all_attr_list = array();

			foreach ($this->cat_filter_attr as $key => $value) {
				$sql = 'SELECT a.attr_id, a.attr_name, a.attr_cat_type FROM ' . $this->ecs->table('attribute') . ' AS a, ' . $this->ecs->table('goods_attr') . ' AS ga left join  ' . $this->ecs->table('goods') . ' AS g on g.goods_id = ga.goods_id ' . $leftJoin . (' WHERE (' . $this->children . ' OR ') . get_extension_goods($this->children) . (') AND a.attr_id = ga.attr_id AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND a.attr_id=\'' . $value . '\'') . $tag_where;
				$attributeInfo = $this->db->getRow($sql);

				if ($attributeInfo) {
					$all_attr_list[$key]['filter_attr_name'] = $attributeInfo['attr_name'];
					$all_attr_list[$key]['attr_cat_type'] = $attributeInfo['attr_cat_type'];
					$all_attr_list[$key]['filter_attr_id'] = $value;
					$sql = 'SELECT a.attr_id, MIN(a.goods_attr_id ) AS goods_id, a.attr_value AS attr_value, a.color_value FROM ' . $this->ecs->table('goods_attr') . ' AS a, ' . $this->ecs->table('goods') . ' AS g' . (' WHERE (' . $this->children . ' OR ') . get_extension_goods($this->children) . ') AND g.goods_id = a.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 ' . (' AND a.attr_id=\'' . $value . '\' ') . ' GROUP BY a.attr_value';
					$attr_list = $this->db->getAll($sql);
					$temp_arrt_url_arr = array();

					for ($i = 0; $i < count($this->cat_filter_attr); $i++) {
						$temp_arrt_url_arr[$i] = !empty($this->filter_attr[$i]) ? $this->filter_attr[$i] : 0;
					}

					$temp_arrt_url_arr[$key] = 0;
					$temp_arrt_url = implode('.', $temp_arrt_url_arr);
					$all_attr_list[$key]['attr_list'][0]['attr_id'] = 0;
					$all_attr_list[$key]['attr_list'][0]['attr_value'] = L('all_attribute');
					$all_attr_list[$key]['attr_list'][0]['url'] = url('products', array('id' => $this->cat_id, 'brand' => $this->brand, 'price_min' => $this->price_min, 'price_max' => $this->price_max, 'filter_attr' => $temp_arrt_url));
					$all_attr_list[$key]['attr_list'][0]['filter_attr'] = $temp_arrt_url;
					$all_attr_list[$key]['attr_list'][0]['selected'] = empty($this->filter_attr[$key]) ? 1 : 0;
					$all_attr_list[$key]['select_attr_name'] = L('all_attribute');

					foreach ($attr_list as $k => $v) {
						$temp_key = $k + 1;
						$temp_arrt_url_arr[$key] = $v['goods_id'];
						$temp_arrt_url = implode('.', $temp_arrt_url_arr);
						$all_attr_list[$key]['attr_list'][$temp_key]['attr_value'] = $v['attr_value'];
						$all_attr_list[$key]['attr_list'][$temp_key]['attr_id'] = $v['goods_id'];
						$all_attr_list[$key]['attr_list'][$temp_key]['url'] = url('products', array('id' => $this->cat_id, 'brand' => $this->brand, 'price_min' => $this->price_min, 'price_max' => $this->price_max, 'filter_attr' => $temp_arrt_url));
						$all_attr_list[$key]['attr_list'][$temp_key]['filter_attr'] = $temp_arrt_url;

						if (!empty($this->filter_attr[$key])) {
							$all_attr_list[$key]['attr_list'][$temp_key]['selected'] = 0;
							if (!stripos($this->filter_attr[$key], ',') && $this->filter_attr[$key] == $v['goods_id']) {
								$all_attr_list[$key]['attr_list'][$temp_key]['selected'] = 1;
								$all_attr_list[$key]['select_attr'] .= !empty($v['attr_value']) ? $v['attr_value'] . '、' : '';
							}

							if (stripos($this->filter_attr[$key], ',')) {
								$color_arr = explode(',', $this->filter_attr[$key]);
								$all_attr_list[$key]['select_attr_name'] = '';

								for ($i = 0; $i < count($color_arr); $i++) {
									if ($color_arr[$i] == $v['goods_id']) {
										$all_attr_list[$key]['attr_list'][$temp_key]['selected'] = 1;
										$all_attr_list[$key]['select_attr'] .= !empty($v['attr_value']) ? $v['attr_value'] . '、' : '';
									}
								}
							}

							$all_attr_list[$key]['select_attr_name'] = !empty($all_attr_list[$key]['select_attr']) ? rtrim($all_attr_list[$key]['select_attr'], '、') : L('all_attribute');
						}
					}
				}
			}

			$this->assign('filter_attr_list', $all_attr_list);

			if (!empty($this->filter_attr)) {
				$ext_sql = 'SELECT DISTINCT(b.goods_id) as dis FROM ' . $this->ecs->table('goods_attr') . ' AS a, ' . $this->ecs->table('goods_attr') . ' AS b ' . 'WHERE ';
				$ext_group_goods = array();

				foreach ($this->filter_attr as $k => $v) {
					unset($ext_group_goods);
					if (!empty($v) && isset($this->cat_filter_attr[$k])) {
						$sql = $ext_sql . 'b.attr_value = a.attr_value AND b.attr_id = ' . $this->cat_filter_attr[$k] . ' AND a.goods_attr_id ' . db_create_in($v);
						$res = $this->db->query($sql);

						foreach ($res as $value) {
							$ext_group_goods[] = $value['dis'];
						}

						if ($ext_group_goods) {
							$this->ext .= ' AND ' . db_create_in($ext_group_goods, 'g.goods_id');
						}
					}
				}
			}
		}

		$this->cou_id = I('request.cou_id', 0, 'intval');
		$this->assign('cou_id', $this->cou_id);
		$this->assign('show_marketprice', C('shop.show_marketprice'));
		$this->assign('category', $this->cat_id);
		$this->assign('brand_id', $this->brand);
		$this->assign('price_min', $this->price_min);
		$this->assign('price_max', $this->price_max);
		$this->assign('isself', $this->isself);
		$this->assign('filter_attr', $filter_attr_str);
		$this->assign('ext', $this->ext);
		$this->assign('parent_id', $parent_id);
		$this->assign('parentCat', $parentCat);
		$this->assign('region_id', $this->region_id);
		$this->assign('area_id', $this->area_id);
		$this->assign('page', $this->page);
		$this->assign('size', $this->size);
		$this->assign('sort', $this->sort);
		$this->assign('order', $this->order);
		$this->assign('keyword', $keyword);
		$this->assign('intro', $this->intro);
		$this->assign('hasgoods', $this->hasgoods);
		$this->assign('promotion', $this->promotion);
		$this->assign('display', $this->display);
	}
}

?>
