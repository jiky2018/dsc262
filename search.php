<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function is_not_null($value)
{
	if (is_array($value)) {
		return !empty($value['from']) || !empty($value['to']);
	}
	else {
		return !empty($value);
	}
}

function get_seachable_attributes($cat_id = 0)
{
	$attributes = array(
		'cate' => array(),
		'attr' => array()
		);
	$sql = 'SELECT t.cat_id, cat_name FROM ' . $GLOBALS['ecs']->table('goods_type') . ' AS t, ' . $GLOBALS['ecs']->table('attribute') . ' AS a' . ' WHERE t.cat_id = a.cat_id AND t.enabled = 1 AND a.attr_index > 0 ';
	$cat = $GLOBALS['db']->getAll($sql);

	if (!empty($cat)) {
		foreach ($cat as $val) {
			$attributes['cate'][$val['cat_id']] = $val['cat_name'];
		}

		$where = 0 < $cat_id ? ' AND a.cat_id = ' . $cat_id : ' AND a.cat_id = ' . $cat[0]['cat_id'];
		$sql = 'SELECT attr_id, attr_name, attr_input_type, attr_type, attr_values, attr_index, sort_order ' . ' FROM ' . $GLOBALS['ecs']->table('attribute') . ' AS a ' . ' WHERE a.attr_index > 0 ' . $where . ' ORDER BY cat_id, sort_order ASC';
		$res = $GLOBALS['db']->query($sql);

		while ($row = $GLOBALS['db']->FetchRow($res)) {
			if ($row['attr_index'] == 1 && $row['attr_input_type'] == 1) {
				$row['attr_values'] = str_replace("\r", '', $row['attr_values']);
				$options = explode("\n", $row['attr_values']);
				$attr_value = array();

				foreach ($options as $opt) {
					$attr_value[$opt] = $opt;
				}

				$attributes['attr'][] = array('id' => $row['attr_id'], 'attr' => $row['attr_name'], 'options' => $attr_value, 'type' => 3);
			}
			else {
				$attributes['attr'][] = array('id' => $row['attr_id'], 'attr' => $row['attr_name'], 'type' => $row['attr_index']);
			}
		}
	}

	return $attributes;
}

function get_con_where($ru_id, $cou_goods, $spec_cat)
{
	$cou_where = ' AND g.user_id = \'' . $ru_id . '\' ';

	if ($cou_goods) {
		$cou_where .= ' AND g.goods_id IN (' . $cou_goods . ') ';
	}
	else if ($spec_cat) {
		$cou_children = get_cou_children($spec_cat);

		if ($cou_children) {
			$cou_where .= ' AND g.cat_id IN (' . $cou_children . ') ';
		}
	}

	return $cou_where;
}

define('IN_ECS', true);

if (!function_exists('htmlspecialchars_decode')) {
	function htmlspecialchars_decode($string, $quote_style = ENT_COMPAT)
	{
		return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
	}
}

if (empty($_GET['encode'])) {
	$string = array_merge($_GET, $_POST);

	if (get_magic_quotes_gpc()) {
		require dirname(__FILE__) . '/includes/lib_base.php';
		$string = stripslashes_deep($string);
	}

	$string['search_encode_time'] = time();
	$string = str_replace('+', '%2b', base64_encode(serialize($string)));
	header('Location:search.php?encode=' . $string . "\n");
	exit();
}
else {
	$string = base64_decode(trim($_GET['encode']));

	if ($string !== false) {
		$string = unserialize($string);

		if ($string !== false) {
			if (!empty($string['search_encode_time'])) {
				if ($string['search_encode_time'] + 2 < time()) {
					define('INGORE_VISIT_STATS', true);
				}
			}
			else {
				define('INGORE_VISIT_STATS', true);
			}

			if (@$string['keywords'] == '优惠券') {
				header('location:http://' . str_replace(strrchr($_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'], '/'), '', $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '/coupons.php?act=coupons_index'));
			}
		}
		else {
			$string = array();
		}
	}
	else {
		$string = array();
	}
}

require dirname(__FILE__) . '/includes/init.php';
$_REQUEST = array_merge($_REQUEST, addslashes_deep($string));
get_request_filter();
require ROOT_PATH . '/includes/lib_area.php';
$area_info = get_area_info($province_id);
$area_id = $area_info['region_id'];
$where = 'regionId = \'' . $province_id . '\'';
$date = array('parent_id');
$region_id = get_table_date('region_warehouse', $where, $date, 2);
$_REQUEST['act'] = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : '';
$search_type = isset($_REQUEST['store_search_cmt']) && !empty($_REQUEST['store_search_cmt']) ? intval($_REQUEST['store_search_cmt']) : 0;
$_REQUEST['keywords'] = strip_tags($_REQUEST['keywords']);
$_REQUEST['keywords'] = !empty($_REQUEST['keywords']) ? addslashes_deep(trim($_REQUEST['keywords'])) : '';
$_REQUEST['category'] = !empty($_REQUEST['category']) ? intval($_REQUEST['category']) : 0;
$_REQUEST['goods_type'] = !empty($_REQUEST['goods_type']) ? intval($_REQUEST['goods_type']) : 0;
$_REQUEST['sc_ds'] = !empty($_REQUEST['sc_ds']) ? intval($_REQUEST['sc_ds']) : 0;
$_REQUEST['outstock'] = !empty($_REQUEST['outstock']) ? 1 : 0;
$get_price_max = isset($_REQUEST['price_max']) ? floatval($_REQUEST['price_max']) : 0;
$get_price_min = isset($_REQUEST['price_min']) ? floatval($_REQUEST['price_min']) : 0;
$_REQUEST['price_max'] = isset($_REQUEST['price_max']) && 0 < intval($_REQUEST['price_max']) ? intval($_REQUEST['price_max']) : 0;
$_REQUEST['price_min'] = isset($_REQUEST['price_min']) && 0 < intval($_REQUEST['price_min']) ? intval($_REQUEST['price_min']) : 0;
$price_min = $_REQUEST['price_min'];
$price_max = $_REQUEST['price_max'];
$_REQUEST['brand'] = isset($_REQUEST['brand']) ? $_REQUEST['brand'] : 0;
$brand = $ecs->get_explode_filter($_REQUEST['brand']);
$_REQUEST['use_cou'] = isset($_REQUEST['use_cou']) ? dsc_addslashes($_REQUEST['use_cou']) : 0;
$smarty->assign('filename', 'search');
$smarty->assign('search_type', $search_type);
$smarty->assign('search_keywords', stripslashes(htmlspecialchars_decode($_REQUEST['keywords'])));
$default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';
$order = isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC')) ? trim($_REQUEST['order']) : $default_sort_order_method;
$display = isset($_REQUEST['display']) && in_array(trim(strtolower($_REQUEST['display'])), array('list', 'grid', 'text')) ? trim($_REQUEST['display']) : (isset($_SESSION['display_search']) ? $_SESSION['display_search'] : 'list');
$_SESSION['display_search'] = $display;

if ($search_type == 1) {
	if ($display == 'list') {
		$default_sort_order_type = 'shop_id';
		$sort = isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array('shop_id', 'goods_number', 'sales_volume')) ? trim($_REQUEST['sort']) : $default_sort_order_type;
	}
	else {
		if ($display == 'grid' || $display == 'text') {
			$default_sort_order_type = $_CFG['sort_order_type'] == '0' ? 'goods_id' : ($_CFG['sort_order_type'] == '1' ? 'shop_price' : 'last_update');
			$sort = isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array('goods_id', 'shop_price', 'last_update', 'sales_volume')) ? trim($_REQUEST['sort']) : $default_sort_order_type;
		}
	}
}
else {
	$smarty->assign('province_row', get_region_info($province_id));
	$smarty->assign('city_row', get_region_info($city_id));
	$smarty->assign('district_row', get_region_info($district_id));
	$province_list = get_warehouse_province();
	$smarty->assign('province_list', $province_list);
	$city_list = get_region_city_county($province_id);
	$smarty->assign('city_list', $city_list);
	$district_list = get_region_city_county($city_id);
	$smarty->assign('district_list', $district_list);
	$smarty->assign('open_area_goods', $GLOBALS['_CFG']['open_area_goods']);
	$default_sort_order_type = $_CFG['sort_order_type'] == '0' ? 'goods_id' : ($_CFG['sort_order_type'] == '1' ? 'shop_price' : 'last_update');
	$sort = isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array('goods_id', 'shop_price', 'last_update', 'sales_volume', 'comments_number')) ? trim($_REQUEST['sort']) : $default_sort_order_type;
	$is_ship = isset($_REQUEST['is_ship']) && !empty($_REQUEST['is_ship']) ? addslashes_deep(trim($_REQUEST['is_ship'])) : '';
	$is_self = isset($_REQUEST['is_self']) && !empty($_REQUEST['is_self']) ? intval($_REQUEST['is_self']) : '';
	$have = isset($_REQUEST['have']) && !empty($_REQUEST['have']) ? intval($_REQUEST['have']) : '';
}

$page = !empty($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$size = !empty($_CFG['page_size']) && 0 < intval($_CFG['page_size']) ? intval($_CFG['page_size']) : 10;
$smarty->assign('category_load_type', $_CFG['category_load_type']);
$smarty->assign('query_string', $_SERVER['QUERY_STRING']);
if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'load_more_goods') {
	$goods_num = empty($_REQUEST['goods_num']) ? 0 : intval($_REQUEST['goods_num']);
	$best_num = empty($_REQUEST['best_num']) ? 0 : intval($_REQUEST['best_num']);
	$goods_floor = floor($goods_num / 4 * 5 / 4 - $best_num);

	if ($goods_floor < 0) {
		$best_size = $_REQUEST['best_num'];
	}
	else {
		$best_size = $goods_floor + 2;
	}
}
else {
	$best_num = 0;
	$best_size = 6;
}

$cat_keys = get_array_keys_cat();

if ($_REQUEST['act'] == 'advanced_search') {
	$goods_type = !empty($_REQUEST['goods_type']) ? intval($_REQUEST['goods_type']) : 0;
	$attributes = get_seachable_attributes($goods_type);
	$smarty->assign('goods_type_selected', $goods_type);
	$smarty->assign('goods_type_list', $attributes['cate']);
	$smarty->assign('goods_attributes', $attributes['attr']);
	assign_template();
	assign_dynamic('search');
	$position = assign_ur_here(0, $_LANG['advanced_search']);
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $position['ur_here']);
	$categories_pro = get_category_tree_leve_one();
	$smarty->assign('categories_pro', $categories_pro);
	$smarty->assign('helps', get_shop_help());
	$smarty->assign('action', 'form');
	$smarty->assign('use_storage', $_CFG['use_storage']);

	if ($search_type == 0) {
		$smarty->assign('best_goods', get_recommend_goods('best', '', $region_id, $area_info['region_id'], 0, 1));
		$smarty->display('search.dwt');
	}
	else if ($search_type == 1) {
		$smarty->display('merchants_shop_list.dwt');
	}

	exit();
}
else {
	if ($search_type == 0) {
		$ur_here = '搜索商品';
	}
	else if ($search_type == 1) {
		$ur_here = '搜索店铺';
	}

	assign_template();
	assign_dynamic('search');
	$position = assign_ur_here(0, $ur_here . ($_REQUEST['keywords'] ? '_' . $_REQUEST['keywords'] : ''));
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $position['ur_here']);
	$smarty->assign('intromode', $intromode);
	$smarty->assign('helps', get_shop_help());
	$smarty->assign('region_id', $region_id);
	$smarty->assign('area_id', $area_id);
	$search_id = 0;

	if ($search_type == 0) {
		$cou_id = isset($_REQUEST['cou_id']) && !empty($_REQUEST['cou_id']) ? intval($_REQUEST['cou_id']) : 0;
		$keywords = '';
		$tag_where = '';
		$act_name = '';
		$leftJoin = '';
		$shop_price = '';

		if ($brand) {
			$tag_where .= ' AND g.brand_id ' . db_create_in($brand);
		}

		$insert_keyword = '';

		if (!empty($_REQUEST['keywords'])) {
			$arr = array();
			$insert_keyword = trim($_REQUEST['keywords']);
			$base_insert_keyword = base64_encode($insert_keyword);
			$pin = new pin();
			$pinyin = $pin->Pinyin($insert_keyword, 'UTF8');

			if ($page == 1) {
				$addtime = local_date('Y-m-d', gmtime());
				$sql = 'INSERT INTO ' . $ecs->table('search_keyword') . ('(keyword, pinyin, is_on, count, addtime, pinyin_keyword)VALUES(\'' . $base_insert_keyword . '\', \'\', \'0\', \'1\', \'' . $addtime . '\', \'') . base64_encode($pinyin) . '\')';
				$db->query($sql);
				$search_id = $db->insert_id();
			}

			$scws_res = scws($_REQUEST['keywords'], 20);

			if ($scws_res) {
				$arr = explode(',', $scws_res);
			}

			$arr1[] = $insert_keyword;
			if ($arr1 && is_array($arr)) {
				$arr = array_merge($arr1, $arr);
			}

			$arr_keyword = $arr;
			$operator = ' OR ';
			$arr = !empty($arr) ? array_unique($arr) : array();
			$keywords = 'AND (';
			$goods_ids = array();

			foreach ($arr as $key => $val) {
				$val = !empty($val) ? dsc_addslashes($val) : '';

				if ($val) {
					if (0 < $key && $key < count($arr) && 1 < count($arr)) {
						$keywords .= $operator;
					}

					$val = mysql_like_quote(trim($val));
					$sc_dsad = $_REQUEST['sc_ds'] ? ' OR goods_desc LIKE \'%' . $val . '%\'' : '';
					$keywords .= '(g.goods_name LIKE \'%' . $val . '%\' OR g.goods_sn = \'' . $val . '\' OR g.keywords LIKE \'%' . $val . '%\' ' . $sc_dsad . ')';
					$sql = 'SELECT DISTINCT goods_id FROM ' . $ecs->table('tag') . (' WHERE tag_words LIKE \'%' . $val . '%\' ');
					$res = $db->query($sql);

					while ($row = $db->FetchRow($res)) {
						$goods_ids[] = $row['goods_id'];
					}

					$db->autoReplace($ecs->table('keywords'), array('date' => local_date('Y-m-d'), 'searchengine' => 'DSC_B2B2C', 'keyword' => addslashes(str_replace('%', '', $val)), 'count' => 1), array('count' => 1));
				}
				else {
					show_message('无法搜索到商品，请重新输入！');
				}
			}

			$keywords .= ')';
			$act_name = ' OR (';

			foreach ($arr as $key => $val) {
				$val = !empty($val) ? dsc_addslashes($val) : '';

				if ($val) {
					if (0 < $key && $key < count($arr) && 1 < count($arr)) {
						$act_name .= $operator;
					}

					$val = mysql_like_quote(trim($val));
					$act_name .= '(pa.goods_name LIKE \'%' . $val . '%\')';
				}
				else {
					show_message('无法搜索到商品，请重新输入！');
				}
			}

			$act_name .= ')';
			$goods_ids = !empty($goods_ids) ? array_unique($goods_ids) : array();

			if (!empty($goods_ids)) {
				$tag_where .= 'OR g.goods_id ' . db_create_in($goods_ids);
			}
		}

		$filter_attr_str = isset($_REQUEST['filter_attr']) ? addslashes(trim($_REQUEST['filter_attr'])) : '0';
		$filter_attr_str = trim(urldecode($filter_attr_str));
		$filter_attr_str = preg_match('/^[\\d,\\.]+$/', $filter_attr_str) ? $filter_attr_str : '';
		$filter_attr = empty($filter_attr_str) ? '' : explode('.', $filter_attr_str);

		if (!empty($brand)) {
			$sql = 'SELECT brand_name FROM ' . $GLOBALS['ecs']->table('brand') . (' WHERE brand_id = \'' . $brand . '\' LIMIT 1');
			$brand_name = $db->getOne($sql);
		}
		else {
			$brand_name = '';
		}

		$cat_id = 0;
		$children = get_children($cat_id);

		if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
			$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('link_area_goods') . ' AS lag ON g.goods_id = lag.goods_id ';
			$tag_where .= ' AND lag.region_id = \'' . $area_id . '\' ';
		}

		if ($GLOBALS['_CFG']['review_goods'] == 1) {
			$tag_where .= ' AND g.review_status > 2 ';
		}

		$cat['grade'] = 6;

		if (1 < $cat['grade']) {
			$sql = 'SELECT min(IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price))) AS min, ' . ' max(IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price))) as max ' . ' FROM ' . $ecs->table('goods') . ' AS g ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_goods') . (' AS wg ON g.goods_id = wg.goods_id AND wg.region_id = \'' . $region_id . '\' ') . ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' AS wag ON g.goods_id = wag.goods_id AND wag.region_id = \'' . $area_id . '\' ') . $leftJoin . (' WHERE (' . $children . ' OR ') . get_extension_goods($children) . ') AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_show = 1 AND g.is_alone_sale = 1 ' . $keywords . $tag_where;
			$row = $db->getRow($sql);
			$price_grade = 0.0001;

			for ($i = -2; $i <= log10($row['max']); $i++) {
				$price_grade *= 10;
			}

			$dx = ceil(($row['max'] - $row['min']) / $cat['grade'] / $price_grade) * $price_grade;

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
			$sql = 'SELECT (FLOOR((IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) - ' . $row['min'] . ') / ' . $dx . ')) AS sn, COUNT(*) AS goods_num  ' . ' FROM ' . $ecs->table('goods') . ' AS g ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_goods') . (' AS wg ON g.goods_id = wg.goods_id AND wg.region_id = \'' . $region_id . '\' ') . ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' AS wag ON g.goods_id = wag.goods_id AND wag.region_id = \'' . $area_id . '\' ') . $leftJoin . (' WHERE (' . $children . ' OR ') . get_extension_goods($children) . ') AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_show = 1 AND g.is_alone_sale = 1 ' . $keywords . $price_grade_where . $tag_where . ' GROUP BY sn ';
			$price_grade = $db->getAll($sql);

			foreach ($price_grade as $key => $val) {
				if ($val['sn'] != '') {
					$temp_key = $key;
					$price_grade[$temp_key]['goods_num'] = $val['goods_num'];
					$price_grade[$temp_key]['start'] = $row['min'] + round($dx * $val['sn']);
					$price_grade[$temp_key]['end'] = $row['min'] + round($dx * ($val['sn'] + 1));
					$price_grade[$temp_key]['price_range'] = $price_grade[$temp_key]['start'] . '&nbsp;-&nbsp;' . $price_grade[$temp_key]['end'];
					$price_grade[$temp_key]['formated_start'] = price_format($price_grade[$temp_key]['start']);
					$price_grade[$temp_key]['formated_end'] = price_format($price_grade[$temp_key]['end']);
					$price_grade[$temp_key]['url'] = $brands[$temp_key]['url'] = build_uri('search', array('bid' => $brand, 'chkw' => $_REQUEST['keywords'], 'price_min' => $price_grade[$temp_key]['start'], 'price_max' => $price_grade[$temp_key]['end'], 'filter_attr' => $filter_attr_str, 'cou_id' => $cou_id), $ur_here);
					if (isset($_REQUEST['price_min']) && $price_grade[$temp_key]['start'] == $price_min && $price_grade[$temp_key]['end'] == $price_max) {
						$price_grade[$temp_key]['selected'] = 1;
					}
					else {
						$price_grade[$temp_key]['selected'] = 0;
					}
				}
			}

			if ($price_min == 0 && $price_max == 0) {
				$smarty->assign('price_grade', $price_grade);
			}
		}

		$where_having = '';
		$brand_select = '';
		$brand_tag_where = '';

		if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
			$brand_select = ' , ( SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('link_area_goods') . (' as lag WHERE lag.goods_id = g.goods_id AND lag.region_id = \'' . $area_id . '\' LIMIT 1) AS area_goods_num ');
			$where_having = ' AND area_goods_num > 0 ';
		}

		if ($GLOBALS['_CFG']['review_goods'] == 1) {
			$brand_tag_where .= ' AND g.review_status > 2 ';
		}

		$sql = 'SELECT b.brand_id, b.brand_name, b.brand_logo, COUNT(*) AS goods_num ' . $brand_select . 'FROM ' . $GLOBALS['ecs']->table('brand') . 'AS b ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . (' AS g ON g.brand_id = b.brand_id AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . $brand_tag_where . ' ' . $keywords . ' ') . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_cat') . ' AS gc ON g.goods_id = gc.goods_id ' . (' WHERE ' . $children . ' OR ') . 'gc.cat_id ' . db_create_in(array_unique(array_merge(array($cat_id), $cat_keys))) . ' AND b.is_show = 1 ' . ('GROUP BY b.brand_id HAVING goods_num > 0 ' . $where_having . ' ORDER BY b.sort_order, b.brand_id ASC');
		$brands_list = $GLOBALS['db']->getAll($sql);
		$pin = new pin();
		$brands = array();

		foreach ($brands_list as $key => $val) {
			$temp_key = $key;
			$brands[$temp_key]['brand_id'] = $val['brand_id'];
			$brands[$temp_key]['brand_name'] = $val['brand_name'];
			$bdimg_path = 'data/brandlogo/';
			$bd_logo = $val['brand_logo'] ? $val['brand_logo'] : '';

			if (empty($bd_logo)) {
				$brands[$temp_key]['brand_logo'] = '';
			}
			else {
				$brands[$temp_key]['brand_logo'] = $bdimg_path . $bd_logo;
			}

			$brands[$temp_key]['brand_letters'] = strtoupper(substr($pin->Pinyin($val['brand_name'], 'UTF8'), 0, 1));
			if ($GLOBALS['_CFG']['open_oss'] == 1 && $brands[$temp_key]['brand_logo']) {
				$bucket_info = get_bucket_info();
				$brands[$temp_key]['brand_logo'] = $bucket_info['endpoint'] . $brands[$temp_key]['brand_logo'];
			}

			$brands[$temp_key]['url'] = build_uri('search', array('bid' => $val['brand_id'], 'chkw' => $_REQUEST['keywords'], 'price_min' => $price_min, 'price_max' => $price_max, 'filter_attr' => $filter_attr_str, 'cou_id' => $cou_id), $val['brand_name']);
			if (!strpos($brand, ',') && $brand == $brands_list[$key]['brand_id']) {
				$brands[$temp_key]['selected'] = 1;
			}

			if (stripos($brand, ',')) {
				$brand2 = explode(',', $brand);

				for ($i = 0; $i < $brand2[$i]; $i++) {
					if ($brand2[$i] == $brands_list[$key]['brand_id']) {
						$brands[$temp_key]['selected'] = 1;
					}
				}
			}
		}

		$ubrand = isset($_REQUEST['ubrand']) ? intval($_REQUEST['ubrand']) : 0;
		$smarty->assign('ubrand', $ubrand);
		$letter = range('A', 'Z');
		$smarty->assign('letter', $letter);

		if ($brands) {
			$smarty->assign('brands', $brands);
		}

		$get_bd = array();
		$bd = '';
		$br_url = '';

		foreach ($brands as $key => $value) {
			if (isset($value['selected']) && value['selected'] == 1) {
				$bd .= $value['brand_name'] . ',';
				$get_bd[$key]['brand_id'] = $value['brand_id'];
				$brand_id = 'brand=' . $get_bd[$key]['brand_id'];

				if (stripos($value['url'], $brand_id)) {
					$get_bd[$key]['url'] = str_replace($brand_id, 'brand=0', $value['url']);
				}

				$br_url = $get_bd[$key]['url'];
			}
		}

		$get_brand['br_url'] = $br_url;
		$get_brand['bd'] = substr($bd, 0, -1);
		$smarty->assign('get_bd', $get_brand);
		$g_price = array();

		for ($i = 0; $i < count($price_grade); $i++) {
			if (isset($price_grade[$i]['selected']) && $price_grade[$i]['selected'] == 1) {
				$g_price[$i]['price_range'] = $price_grade[$i]['price_range'];
				$g_price[$i]['url'] = $price_grade[$i]['url'];
				$p_url = $g_price[$i]['url'];
				$p_a = $get_price_min;
				$p_b = $get_price_max;
				if (stripos($p_url, $p_a) && stripos($p_url, $p_b)) {
					if ($p_b < $p_a) {
						$price = array($p_a, $p_b);
						$p_a = $price[1];
						$p_b = $price[0];
					}

					if (0 < $p_a && 0 < $p_b) {
						$g_price[$i]['url'] = str_replace($p_b, 0, str_replace($p_a, 0, $p_url));
					}
					else {
						if ($p_a == 0 && 0 < $p_b) {
							$g_price[$i]['url'] = str_replace($p_b, 0, $p_url);
						}
					}
				}

				break;
			}
		}

		if (empty($g_price) && (0 < $price_min || 0 < $price_max)) {
			if ($price_max < $price_min) {
				$price = array($price_min, $price_max);
				$price_min = $price[1];
				$price_max = $price[0];
			}

			$parray = array();
			$parray['purl'] = build_uri('search', array('bid' => $brand, 'chkw' => $_REQUEST['keywords'], 'price_min' => 0, 'price_max' => 0, 'filter_attr' => $filter_attr_str, 'cou_id' => $cou_id), $ur_here);
			$parray['min_max'] = $price_min . ' - ' . $price_max;
			$smarty->assign('parray', $parray);
		}

		$smarty->assign('g_price', $g_price);
		$search_filter_attr = '';

		if (0 < $search_filter_attr) {
			$cat_filter_attr = explode(',', $search_filter_attr);
			$all_attr_list = array();
			$attributeInfo = array();

			foreach ($cat_filter_attr as $key => $value) {
				$sql = 'SELECT a.attr_id, a.attr_name, a.attr_cat_type FROM ' . $ecs->table('attribute') . ' AS a ' . (' WHERE a.attr_id = \'' . $value . '\' LIMIT 1');
				$attributeInfo = $db->getRow($sql);

				if ($attributeInfo) {
					$all_attr_list[$key]['filter_attr_name'] = $attributeInfo['attr_name'];
					$all_attr_list[$key]['attr_cat_type'] = $attributeInfo['attr_cat_type'];
					$all_attr_list[$key]['filter_attr_id'] = $value;
					$sql = 'SELECT a.attr_id, MIN(a.goods_attr_id ) AS goods_id, a.attr_value AS attr_value, a.color_value FROM ' . $ecs->table('goods_attr') . ' AS a, ' . $ecs->table('goods') . ' AS g' . (' WHERE (' . $children . ' OR ') . get_extension_goods($children) . ') AND g.goods_id = a.goods_id AND g.is_delete = 0 AND g.is_show = 1 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 ' . (' AND a.attr_id = \'' . $value . '\' ') . $tag_where . ' GROUP BY a.attr_value';
					$attr_list = $db->getAll($sql);
					$temp_arrt_url_arr = array();

					for ($i = 0; $i < count($cat_filter_attr); $i++) {
						$temp_arrt_url_arr[$i] = !empty($filter_attr[$i]) ? $filter_attr[$i] : 0;
					}

					foreach ($attr_list as $k => $v) {
						$temp_key = $k;
						$temp_arrt_url_arr[$key] = $v['goods_id'];
						$temp_arrt_url = implode('.', $temp_arrt_url_arr);

						if (!empty($v['color_value'])) {
							$arr_color2['c_value'] = $v['attr_value'];
							$arr_color2['c_url'] = '#' . $v['color_value'];
							$v['attr_value'] = $arr_color2;
						}

						$all_attr_list[$key]['attr_list'][$temp_key]['attr_value'] = $v['attr_value'];
						$all_attr_list[$key]['attr_list'][$temp_key]['goods_id'] = $v['goods_id'];
						$all_attr_list[$key]['attr_list'][$temp_key]['url'] = build_uri('category', array('cid' => $cat_id, 'bid' => $brand, 'price_min' => $price_min, 'price_max' => $price_max, 'filter_attr' => $temp_arrt_url), $cat['cat_name']);

						if (!empty($filter_attr[$key])) {
							if (!stripos($filter_attr[$key], ',') && $filter_attr[$key] == $v['goods_id']) {
								$all_attr_list[$key]['attr_list'][$temp_key]['selected'] = 1;
							}

							if (stripos($filter_attr[$key], ',')) {
								$color_arr = explode(',', $filter_attr[$key]);

								for ($i = 0; $i < count($color_arr); $i++) {
									if ($color_arr[$i] == $v['goods_id']) {
										$all_attr_list[$key]['attr_list'][$temp_key]['selected'] = 1;
									}
								}
							}
						}
					}
				}
			}

			$color_list = array();

			for ($i = 0; $i < count($all_attr_list) + 1; $i++) {
				if ($all_attr_list[$i]['attr_cat_type'] == 1) {
					for ($k = 0; $k < count($all_attr_list[$i]['attr_list']); $k++) {
						$array_color = $all_attr_list[$i]['attr_list'];

						if (count($array_color[$k]['attr_value']) == 1) {
							$array['c_value'] = $array_color[$k]['attr_value'];
							$array['c_url'] = '#FFFFFF';
							$all_attr_list[$i]['attr_list'][$k]['attr_value'] = $array;
						}
					}

					$color_list = $all_attr_list[$i];
					unset($all_attr_list[$i]);
				}
			}

			$c_array = array();
			$k = '';

			for ($i = 0; $i < count($color_list['attr_list']); $i++) {
				if ($color_list['attr_list'][$i]['selected'] == 1) {
					$c_array[$i]['filter_attr_name'] = $color_list['filter_attr_name'];
					$c_array[$i]['attr_list']['attr_value'] = $color_list['attr_list'][$i]['attr_value']['c_value'];
					$c_array[$i]['attr_list']['goods_id'] = $color_list['attr_list'][$i]['goods_id'];
					$color_id = $c_array[$i]['attr_list']['goods_id'];
					$k .= $c_array[$i]['attr_list']['attr_value'] . ',';
					$color_url = $color_list['attr_list'][$i]['url'];

					if (strpos($color_url, $color_id)) {
						$c_array[$i]['attr_list']['url'] = str_replace($color_id, 0, $color_url);
					}

					$c_url = $c_array[$i]['attr_list']['url'];
				}
			}

			$c_array = array();
			$c_array['filter_attr_name'] = $color_list['filter_attr_name'];
			$c_array['attr_value'] = substr($k, 0, -1);
			$c_array['url'] = $c_url;
			$g_array = array();

			for ($i = 0; $i < count($all_attr_list) + 3; $i++) {
				$k = '';

				for ($j = 0; $j < count($all_attr_list[$i]['attr_list']); $j++) {
					if ($all_attr_list[$i]['attr_list'][$j]['selected'] == 1) {
						$g_array[$i]['filter_attr_name'] = $all_attr_list[$i]['filter_attr_name'];
						$g_array[$i]['attr_list']['value'] = $all_attr_list[$i]['attr_list'][$j]['attr_value'];
						$g_array[$i]['attr_list']['goods_id'] = $all_attr_list[$i]['attr_list'][$j]['goods_id'];
						$g_url = $all_attr_list[$i]['attr_list'][$j]['url'];
						$sid = $g_array[$i]['attr_list']['goods_id'];

						if (strpos($g_url, $sid)) {
							$g_array[$i]['attr_list']['url'] = str_replace($sid, 0, $g_url);
						}

						$k .= $all_attr_list[$i]['attr_list'][$j]['attr_value'] . ',';
						$g_array[$i]['g_name'] = substr($k, 0, -1);
						$g_array[$i]['g_url'] = $g_array[$i]['attr_list']['url'];
					}
				}
			}

			$smarty->assign('c_array', $c_array);
			$smarty->assign('g_array', $g_array);
			$smarty->assign('color_search', $color_list);
			$smarty->assign('filter_attr_list', $all_attr_list);
		}

		$action = '';
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'form') {
			$adv_value['keywords'] = htmlspecialchars(stripcslashes($_REQUEST['keywords']));
			$adv_value['brand'] = $_REQUEST['brand'];
			$adv_value['price_min'] = $_REQUEST['price_min'];
			$adv_value['price_max'] = $_REQUEST['price_max'];
			$adv_value['category'] = $_REQUEST['category'];
			$attributes = get_seachable_attributes($_REQUEST['goods_type']);

			foreach ($attributes['attr'] as $key => $val) {
				if (!empty($_REQUEST['attr'][$val['id']])) {
					if ($val['type'] == 2) {
						$attributes['attr'][$key]['value']['from'] = !empty($_REQUEST['attr'][$val['id']]['from']) ? htmlspecialchars(stripcslashes(trim($_REQUEST['attr'][$val['id']]['from']))) : '';
						$attributes['attr'][$key]['value']['to'] = !empty($_REQUEST['attr'][$val['id']]['to']) ? htmlspecialchars(stripcslashes(trim($_REQUEST['attr'][$val['id']]['to']))) : '';
					}
					else {
						$attributes['attr'][$key]['value'] = !empty($_REQUEST['attr'][$val['id']]) ? htmlspecialchars(stripcslashes(trim($_REQUEST['attr'][$val['id']]))) : '';
					}
				}
			}

			if ($_REQUEST['sc_ds']) {
				$smarty->assign('scck', 'checked');
			}

			$smarty->assign('adv_val', $adv_value);
			$smarty->assign('goods_type_list', $attributes['cate']);
			$smarty->assign('goods_attributes', $attributes['attr']);
			$smarty->assign('goods_type_selected', $_REQUEST['goods_type']);
			$smarty->assign('action', 'form');
			$smarty->assign('use_storage', $_CFG['use_storage']);
			$action = 'form';
		}

		$category = !empty($_REQUEST['category']) ? intval($_REQUEST['category']) : 0;
		$categories = '';

		if (!empty($category)) {
			$children = get_children($category);
			$categories = ' AND ' . $children;
		}

		$outstock = !empty($_REQUEST['outstock']) ? ' AND g.goods_number > 0 ' : '';
		$where_price_min = $_REQUEST['price_min'] != 0 ? ' AND g.shop_price * ' . $_SESSION['discount'] . ' >= \'' . $_REQUEST['price_min'] . '\'' : '';
		$where_price_max = $_REQUEST['price_max'] != 0 || $_REQUEST['price_min'] < 0 ? ' AND g.shop_price * ' . $_SESSION['discount'] . ' <= \'' . $_REQUEST['price_max'] . '\'' : '';
		$intromode = '';

		if (!empty($_REQUEST['intro'])) {
			switch ($_REQUEST['intro']) {
			case 'best':
				$intro = ' AND g.is_best = 1';
				$intromode = 'best';
				$ur_here = $_LANG['best_goods'];
				break;

			case 'new':
				$intro = ' AND g.is_new = 1';
				$intromode = 'new';
				$ur_here = $_LANG['new_goods'];
				break;

			case 'hot':
				$intro = ' AND g.is_hot = 1';
				$intromode = 'hot';
				$ur_here = $_LANG['hot_goods'];
				break;

			case 'promotion':
				$time = gmtime();
				$intro = ' AND g.promote_price > 0 AND g.promote_start_date <= \'' . $time . '\' AND g.promote_end_date >= \'' . $time . '\'';
				$intromode = 'promotion';
				$ur_here = $_LANG['promotion_goods'];
				break;

			default:
				$intro = '';
			}
		}
		else {
			$intro = '';
		}

		if (empty($ur_here)) {
			$ur_here = $_LANG['search_goods'];
		}

		$attr_in = '';
		$attr_num = 0;
		$attr_url = '';
		$attr_arg = array();

		if (!empty($_REQUEST['attr'])) {
			$sql = 'SELECT goods_id, COUNT(*) AS num FROM ' . $ecs->table('goods_attr') . ' WHERE 0 ';

			foreach ($_REQUEST['attr'] as $key => $val) {
				if (is_not_null($val) && is_numeric($key)) {
					$attr_num++;
					$sql .= ' OR (1 ';

					if (is_array($val)) {
						$sql .= ' AND attr_id = \'' . $key . '\'';

						if (!empty($val['from'])) {
							$sql .= is_numeric($val['from']) ? ' AND attr_value >= ' . floatval($val['from']) : ' AND attr_value >= \'' . $val['from'] . '\'';
							$attr_arg['attr[' . $key . '][from]'] = $val['from'];
							$attr_url .= '&amp;attr[' . $key . '][from]=' . $val['from'];
						}

						if (!empty($val['to'])) {
							$sql .= is_numeric($val['to']) ? ' AND attr_value <= ' . floatval($val['to']) : ' AND attr_value <= \'' . $val['to'] . '\'';
							$attr_arg['attr[' . $key . '][to]'] = $val['to'];
							$attr_url .= '&amp;attr[' . $key . '][to]=' . $val['to'];
						}
					}
					else {
						$sql .= isset($_REQUEST['pickout']) ? ' AND attr_id = \'' . $key . '\' AND attr_value = \'' . $val . '\' ' : ' AND attr_id = \'' . $key . '\' AND attr_value LIKE \'%' . mysql_like_quote($val) . '%\' ';
						$attr_url .= '&amp;attr[' . $key . ']=' . $val;
						$attr_arg['attr[' . $key . ']'] = $val;
					}

					$sql .= ')';
				}
			}

			if (0 < $attr_num) {
				$sql .= ' GROUP BY goods_id HAVING num = \'' . $attr_num . '\'';
				$row = $db->getCol($sql);

				if (count($row)) {
					$attr_in = ' AND ' . db_create_in($row, 'g.goods_id');
				}
				else {
					$attr_in = ' AND 0 ';
				}
			}
		}
		else if (isset($_REQUEST['pickout'])) {
			$sql = 'SELECT DISTINCT(goods_id) FROM ' . $ecs->table('goods_attr');
			$col = $db->getCol($sql);

			if (!empty($col)) {
				$attr_in = ' AND ' . db_create_in($col, 'g.goods_id');
			}
		}

		if ($is_ship == 'is_shipping') {
			$tag_where .= ' AND g.is_shipping = 1 ';
		}

		if ($is_self == 1) {
			$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi on msi.user_id = g.user_id ';
			$tag_where .= ' AND (g.user_id = 0 OR msi.self_run = 1) ';
		}

		$tag_left = '';

		if ($have == 1) {
			$tag_left .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_goods') . (' AS wg ON g.goods_id = wg.goods_id AND wg.region_id = \'' . $region_id . '\' ');
			$tag_left .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' AS wag ON g.goods_id = wag.goods_id AND wag.region_id = \'' . $area_id . '\' ');
			$tag_where .= ' AND IF(g.model_price < 1, g.goods_number, IF(g.model_price < 2, wg.region_number, wag.region_number)) > 0 ';
		}

		$cou_goods_where = '';
		$user_cou = isset($_REQUEST['user_cou']) && !empty($_REQUEST['user_cou']) ? intval($_REQUEST['user_cou']) : 0;

		if ($cou_id) {
			$cou_data = $db->getRow('SELECT * FROM ' . $ecs->table('coupons') . (' WHERE cou_id = \'' . $cou_id . '\''));
			$cou_page_data = '&cou_id=' . $cou_id . '&use_cou=' . $user_cou;
			if ($cou_data['cou_type'] == 2 && empty($user_cou)) {
				if (isset($_REQUEST['use_cou'])) {
					$cou_goods_where = get_con_where($cou_data['ru_id'], $cou_data['cou_goods'], $cou_data['spec_cat']);
				}
				else {
					$cou_goods_where = ' AND g.user_id =\'' . $cou_data['ru_id'] . '\' ';

					if ($cou_data['cou_ok_goods']) {
						$cou_goods_where .= ' AND g.goods_id IN (' . $cou_data['cou_ok_goods'] . ') ';
					}
					else if ($cou_data['cou_ok_cat']) {
						$cou_children = get_cou_children($cou_data['cou_ok_cat']);

						if ($cou_children) {
							$cou_goods_where .= ' AND g.cat_id IN (' . $cou_children . ') ';
						}
					}
				}
			}
			else {
				$cou_goods_where = get_con_where($cou_data['ru_id'], $cou_data['cou_goods'], $cou_data['spec_cat']);
			}

			$smarty->assign('cou_id', $cou_id);
		}

		$spec_goods_ids = isset($_REQUEST['goods_ids']) ? $_REQUEST['goods_ids'] : '';

		if ($spec_goods_ids) {
			$tag_where .= ' AND ' . db_create_in($spec_goods_ids, 'g.goods_id');
		}

		$leftJoin .= 'LEFT JOIN ' . $GLOBALS['ecs']->table('presale_activity') . ' AS pa ON pa.goods_id = g.goods_id ';
		$tag_where .= get_rs_where($_COOKIE['city']);
		$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('goods') . ' AS g ' . $leftJoin . $tag_left . ('WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 ' . $attr_in . ' ') . 'AND (( 1 ' . $categories . $keywords . $where_price_min . $where_price_max . $intro . $outstock . ' ) ' . $tag_where . ' ) ' . $act_name . (' ' . $cou_goods_where . ' ');
		$count = $db->getOne($sql);

		if ($page == 1) {
			$sql = 'UPDATE' . $ecs->table('search_keyword') . ('SET result_count = \'' . $count . '\' WHERE keyword_id = \'' . $search_id . '\'');
			$db->query($sql);
		}

		$max_page = 0 < $count ? ceil($count / $size) : 1;

		if ($max_page < $page) {
			$page = $max_page;
		}

		if ($is_self == 1) {
			$shop_price .= ' msi.self_run, ';
		}

		if ($sort == 'goods_id') {
			$goods_sort = 'IF(goods_sort > 0, goods_sort + g.sort_order, g.sort_order) ' . $order . ', g.goods_id ';
		}
		else {
			$goods_sort = 'g.' . $sort;
		}

		$sql = 'SELECT pa.act_id, pa.act_name, pa.start_time, pa.end_time, ' . '(SELECT ' . 'IF((iw.goods_number + iw.user_number + iw.goods_comment_number + iw.merchants_comment_number + iw.user_attention_number) > iw.return_number, (iw.goods_number + iw.user_number + iw.goods_comment_number + iw.merchants_comment_number + iw.user_attention_number - iw.return_number), 0) ' . ' AS goods_sort FROM ' . $GLOBALS['ecs']->table('intelligent_weight') . ' AS iw WHERE iw.goods_id = g.goods_id LIMIT 1) AS goods_sort, ' . 'g.goods_id, g.is_shipping, g.user_id, g.goods_name, g.shop_price,g.market_price, g.is_new, g.comments_number, g.sales_volume, g.is_best, g.is_hot,g.store_new, g.store_best, g.store_hot, ' . $shop_price . ' g.model_price, g.model_attr, ' . 'IF((SELECT COUNT(*) FROM ' . $ecs->table('goods') . (' AS gf WHERE gf.goods_id = g.goods_id AND goods_name LIKE \'%' . $insert_keyword . '%\') > 0, 0, 1) AS goods_fen, ') . 'IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\') AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . ' IF(g.model_price < 1, g.goods_number, IF(g.model_price < 2, wg.region_number, wag.region_number)) AS goods_number, ' . 'g.promote_start_date, g.promote_end_date, g.is_promote, g.goods_thumb, g.goods_img, g.goods_brief, g.goods_type, g.product_price, g.product_promote_price ' . 'FROM ' . $ecs->table('goods') . ' AS g ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_goods') . (' AS wg ON g.goods_id = wg.goods_id AND wg.region_id = \'' . $region_id . '\' ') . ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' AS wag ON g.goods_id = wag.goods_id AND wag.region_id = \'' . $area_id . '\' ') . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ('WHERE (g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_show = 1 AND g.is_alone_sale = 1 ' . $attr_in . ' ' . $cou_goods_where . ' ') . 'AND (( 1 ' . $categories . $keywords . $where_price_min . $where_price_max . $intro . $outstock . ' ) ' . $tag_where . ' ) ) ' . $act_name . $tag_where . $where_price_min . $where_price_max . (' GROUP BY g.goods_id ORDER BY goods_fen, ' . $goods_sort . ' ' . $order);
		if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'load_more_goods') {
			$start = intval($_REQUEST['goods_num']);
		}
		else {
			$start = ($page - 1) * $size;
		}

		$res = $db->SelectLimit($sql, $size, $start);
		$region = array(1, $province_id, $city_id, $district_id);
		$idx = 0;
		$arr = array();
		$now = gmtime();

		if ($arr_keyword) {
			$arr_keyword = array_values($arr_keyword);
			$built_key = '<font style=\'color:#ec5151;\'></font>';

			foreach ($arr_keyword as $key => $val_keyword) {
				if (strpos($built_key, $val_keyword) !== false) {
					unset($arr_keyword[$key]);
				}
			}
		}

		while ($row = $db->FetchRow($res)) {
			$shop_info = get_shop_name($row['user_id'], 3);
			$arr[$idx]['rz_shopName'] = $shop_info['shop_name'];
			$arr[$idx]['goods_fen'] = $row['goods_fen'];

			if (0 < $row['promote_price']) {
				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			}
			else {
				$promote_price = 0;
			}

			if ($row['act_id']) {
				$arr[$idx]['presale'] = '预售';
				$arr[$idx]['act_id'] = $row['act_id'];
				$arr[$idx]['act_name'] = $row['act_name'];
				$arr[$idx]['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
				$arr[$idx]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
				$arr[$idx]['purl'] = build_uri('presale', array('act' => 'view', 'presaleid' => $row['act_id']), $row['goods_name']);
				$build_uri = array('urid' => $row['ru_id'], 'append' => $arr[$idx]['rz_shopName']);
				$domain_url = get_seller_domain_url($row['user_id'], $build_uri);
				$arr[$row['shop_id']]['pshop_url'] = $domain_url['domain_name'];
				$arr[$idx]['start_time_date'] = local_date('Y-m-d H:i:s', $row['start_time']);
				$arr[$idx]['end_time_date'] = local_date('Y-m-d H:i:s', $row['end_time']);
				$act_name_keyword = '<span>' . $row['act_name'] . '</span>';

				foreach ($arr_keyword as $key => $val_keyword) {
					$act_name_keyword = preg_replace('/(>.*)(' . $val_keyword . ')(.*<)/Ui', '$1<font style=\'color:#ec5151;\'>' . $val_keyword . '</font>$3', $act_name_keyword);
				}

				$arr[$idx]['act_name_keyword'] = $act_name_keyword;

				if ($now <= $row['start_time']) {
					$arr[$idx]['no_start'] = 1;
				}

				if ($row['end_time'] <= $now) {
					$arr[$idx]['already_over'] = 1;
				}
			}

			$watermark_img = '';

			if ($promote_price != 0) {
				$watermark_img = 'watermark_promote_small';
			}
			else if ($row['is_new'] != 0) {
				$watermark_img = 'watermark_new_small';
			}
			else if ($row['is_best'] != 0) {
				$watermark_img = 'watermark_best_small';
			}
			else if ($row['is_hot'] != 0) {
				$watermark_img = 'watermark_hot_small';
			}

			if ($watermark_img != '') {
				$arr[$idx]['watermark_img'] = $watermark_img;
			}

			$arr[$idx]['goods_id'] = $row['goods_id'];

			if ($row['model_attr'] == 1) {
				$table_products = 'products_warehouse';
				$type_files = ' and warehouse_id = \'' . $region_id . '\'';
			}
			else if ($row['model_attr'] == 2) {
				$table_products = 'products_area';
				$type_files = ' and area_id = \'' . $area_id . '\'';
			}
			else {
				$table_products = 'products';
				$type_files = '';
			}

			$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table($table_products) . ' WHERE goods_id = \'' . $row['goods_id'] . '\'' . $type_files . ' LIMIT 0, 1';
			$arr[$idx]['prod'] = $GLOBALS['db']->getRow($sql);

			if (empty($prod)) {
				$arr[$idx]['prod'] = 1;
			}
			else {
				$arr[$idx]['prod'] = 0;
			}

			if ($display == 'grid') {
				$goods_name_keyword = sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']);
				$goods_name_keyword = '<span>' . $goods_name . '</span>';

				foreach ($arr_keyword as $key => $val_keyword) {
					if (preg_match('/(>.*)(' . $val_keyword . ')(.*<)/Ui', $goods_name_keyword)) {
						$goods_name_keyword = preg_replace('/(>.*)(' . $val_keyword . ')(.*<)/Ui', '$1<font style=\'color:#ec5151;\'>' . $val_keyword . '</font>$3', $goods_name);
					}
				}

				$arr[$idx]['goods_name_keyword'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? $goods_name_keyword : $goods_name_keyword;
				$arr[$idx]['goods_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? $row['goods_name'] : $row['goods_name'];
			}
			else {
				$goods_name_keyword = '<span>' . $row['goods_name'] . '</span>';

				foreach ($arr_keyword as $key => $val_keyword) {
					if (preg_match('/(>.*)(' . $val_keyword . ')(.*<)/Ui', $goods_name_keyword)) {
						$goods_name_keyword = preg_replace('/(>.*)(' . $val_keyword . ')(.*<)/Ui', '$1<font style=\'color:#ec5151;\'>' . $val_keyword . '</font>$3', $goods_name_keyword);
					}
				}

				$arr[$idx]['goods_name_keyword'] = $goods_name_keyword;
				$arr[$idx]['goods_name'] = $row['goods_name'];
			}

			$arr[$idx]['goods_number'] = $row['goods_number'];
			$arr[$idx]['type'] = $row['goods_type'];
			$arr[$idx]['is_promote'] = $row['is_promote'];
			$arr[$idx]['sales_volume'] = $row['sales_volume'];
			$arr[$idx]['market_price'] = price_format($row['market_price']);
			$arr[$idx]['shop_price'] = price_format($row['shop_price']);
			$arr[$idx]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
			$arr[$idx]['goods_brief'] = $row['goods_brief'];
			$arr[$idx]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$arr[$idx]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
			$arr[$idx]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
			$arr[$idx]['is_shipping'] = $row['is_shipping'];
			$mc_all = ments_count_all($row['goods_id']);
			$mc_one = ments_count_rank_num($row['goods_id'], 1);
			$mc_two = ments_count_rank_num($row['goods_id'], 2);
			$mc_three = ments_count_rank_num($row['goods_id'], 3);
			$mc_four = ments_count_rank_num($row['goods_id'], 4);
			$mc_five = ments_count_rank_num($row['goods_id'], 5);
			$arr[$idx]['zconments'] = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);
			$arr[$idx]['review_count'] = $arr[$idx]['zconments']['allmen'];
			$arr[$idx]['pictures'] = get_goods_gallery($row['goods_id'], 6);

			if ($GLOBALS['_CFG']['customer_service'] == 0) {
				$seller_id = 0;
				$shop_information = get_shop_name($seller_id);
			}
			else {
				$seller_id = $row['user_id'];
				$shop_information = $shop_info['shop_information'];
			}

			$arr[$idx]['is_IM'] = $shop_information['is_IM'];

			if ($seller_id == 0) {
				if ($GLOBALS['db']->getOne('SELECT kf_im_switch FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . 'WHERE ru_id = 0', true)) {
					$arr[$idx]['is_dsc'] = true;
				}
				else {
					$arr[$idx]['is_dsc'] = false;
				}
			}
			else {
				$arr[$idx]['is_dsc'] = false;
			}

			$build_uri = array('urid' => $row['user_id'], 'append' => $arr[$idx]['rz_shopName']);
			$domain_url = get_seller_domain_url($row['user_id'], $build_uri);
			$arr[$idx]['store_url'] = $domain_url['domain_name'];
			$arr[$idx]['is_new'] = $row['is_new'];
			$arr[$idx]['is_best'] = $row['is_best'];
			$arr[$idx]['is_hot'] = $row['is_hot'];
			$arr[$idx]['user_id'] = $row['user_id'];
			$arr[$idx]['self_run'] = $shop_info['shopinfo']['self_run'];
			$sql = 'select * from ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' where ru_id=\'' . $row['user_id'] . '\'';
			$basic_info = $GLOBALS['db']->getRow($sql);
			$arr[$idx]['kf_type'] = $basic_info['kf_type'];

			if ($basic_info['kf_qq']) {
				$kf_qq = array_filter(preg_split('/\\s+/', $basic_info['kf_qq']));
				$kf_qq = explode('|', $kf_qq[0]);

				if (!empty($kf_qq[1])) {
					$arr[$idx]['kf_qq'] = $kf_qq[1];
				}
				else {
					$arr[$idx]['kf_qq'] = '';
				}
			}
			else {
				$arr[$idx]['kf_qq'] = '';
			}

			if ($basic_info['kf_ww']) {
				$kf_ww = array_filter(preg_split('/\\s+/', $basic_info['kf_ww']));
				$kf_ww = explode('|', $kf_ww[0]);

				if (!empty($kf_ww[1])) {
					$arr[$idx]['kf_ww'] = $kf_ww[1];
				}
				else {
					$arr[$idx]['kf_ww'] = '';
				}
			}
			else {
				$arr[$idx]['kf_ww'] = '';
			}

			if (!defined('THEME_EXTENSION')) {
				$shippingFee = goodsShippingFee($row['goods_id'], $region_id, $area_info['region_id'], $region);
				$arr[$idx]['shipping_fee_formated'] = $shippingFee['shipping_fee_formated'];
			}

			$arr[$idx]['is_collect'] = get_collect_user_goods($row['goods_id']);
			$idx++;
		}

		if ($display == 'grid') {
			if (count($arr) % 2 != 0) {
				$arr[] = array();
			}
		}

		$smarty->assign('goods_list', $arr);
		$smarty->assign('category', $category);
		$smarty->assign('keywords', htmlspecialchars(stripslashes($_REQUEST['keywords'])));
		$smarty->assign('brand', $_REQUEST['brand']);
		$smarty->assign('price_min', $price_min);
		$smarty->assign('price_max', $price_max);
		$smarty->assign('outstock', $_REQUEST['outstock']);
		if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'load_more_goods') {
			$smarty->assign('model', intval($_REQUEST['model']));
			$result = array('error' => 0, 'message' => '', 'cat_goods' => '', 'best_goods' => '');
			$result['cat_goods'] = html_entity_decode($smarty->fetch('library/more_goods.lbi'));
			$result['best_goods'] = html_entity_decode($smarty->fetch('library/more_goods_best.lbi'));
			exit(json_encode($result));
		}

		$url_format = 'search.php?category=' . $category . '&amp;keywords=' . urlencode(stripslashes($_REQUEST['keywords'])) . '&amp;brand=' . $_REQUEST['brand'] . '&amp;action=' . $action . '&amp;goods_type=' . $_REQUEST['goods_type'] . '&amp;sc_ds=' . $_REQUEST['sc_ds'] . $cou_page_data;

		if (!empty($intromode)) {
			$url_format .= '&amp;intro=' . $intromode;
		}

		if (isset($_REQUEST['pickout'])) {
			$url_format .= '&amp;pickout=1';
		}

		$url_format .= '&amp;price_min=' . $_REQUEST['price_min'] . '&amp;price_max=' . $_REQUEST['price_max'] . ('&amp;sort=' . $sort);
		$url_format .= $attr_url . '&amp;order=' . $order . '&amp;page=';
		$pager['search'] = array('keywords' => stripslashes(trim($_REQUEST['keywords'])), 'category' => $category, 'store_search_cmt' => $search_type, 'brand' => $_REQUEST['brand'], 'sort' => $sort, 'order' => $order, 'price_min' => $_REQUEST['price_min'], 'price_max' => $_REQUEST['price_max'], 'action' => $action, 'intro' => empty($intromode) ? '' : trim($intromode), 'goods_type' => $_REQUEST['goods_type'], 'sc_ds' => $_REQUEST['sc_ds'], 'outstock' => $_REQUEST['outstock'], 'is_ship' => $is_ship, 'self_support' => $is_self, 'have' => $have, 'use_cou' => $_REQUEST['use_cou'], 'cou_id' => $cou_id);
		$pager['search'] = array_merge($pager['search'], $attr_arg);
		$pager = get_pager('search.php', $pager['search'], $count, $page, $size);
		$pager['display'] = $display;
		$smarty->assign('url_format', $url_format);
		$smarty->assign('pager', $pager);
		$categories_pro = get_category_tree_leve_one();
		$smarty->assign('categories_pro', $categories_pro);

		for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
			$search_left_ad .= '\'search_left_ad' . $i . ',';
			$search_right_ad .= '\'search_right_ad' . $i . ',';
		}

		$smarty->assign('search_left_ad', $search_left_ad);
		$smarty->assign('search_right_ad', $search_right_ad);
		$smarty->assign('best_goods', get_recommend_goods('best', '', $region_id, $area_info['region_id'], 0, 1));
		$smarty->assign('guess_goods', get_guess_goods($_SESSION['user_id'], 1, 1, 7, $region_id, $area_info['region_id']));
		$cur_url = get_return_self_url();
		$smarty->assign('cur_url', $cur_url);
		$smarty->assign('script_name', 'search');
		$smarty->display('search.dwt');
	}
	else if ($search_type == 1) {
		$keywords = htmlspecialchars(stripcslashes($_REQUEST['keywords']));

		if ($display == 'list') {
			$count = get_store_shop_count($keywords, $sort, 0, 0, 0, '', 1);
			$shop_count = $count;
			$size = 10;
			$store_shop_list = get_store_shop_list(1, $keywords, $count, $size, $page, $sort, $order, $region_id, $area_id);
			$smarty->assign('store_shop_list', $store_shop_list['shop_list']);
			$smarty->assign('pager', $store_shop_list['pager']);
		}
		else {
			if ($display == 'grid' || $display == 'text') {
				if ($display == 'text') {
					$size = 21;
				}
				else {
					$size = 20;
				}

				$shop_goods_list = get_store_shop_goods_list($keywords, $size, $page, $sort, $order, $region_id, $area_id);
				$smarty->assign('shop_goods_list', $shop_goods_list);
				$count = get_store_shop_goods_count($keywords, $sort);
				$shop_count = $count;
			}
		}

		if ($display == 'grid' || $display == 'text') {
			if (defined('THEME_EXTENSION')) {
				$categories_pro = get_category_tree_leve_one();
				$smarty->assign('categories_pro', $categories_pro);
			}

			$url_format = 'search.php?category=0&amp;keywords=' . urlencode(stripslashes($_REQUEST['keywords']));
			$url_format .= '&amp;sort=' . $sort;
			$url_format .= '&amp;order=' . $order . '&amp;page=';
			$pager['search'] = array('keywords' => stripslashes(trim($_REQUEST['keywords'])), 'category' => 0, 'store_search_cmt' => $search_type, 'sort' => $sort, 'order' => $order);
			$pager = get_pager('search.php', $pager['search'], $count, $page, $size);
			$pager['display'] = $display;
			$smarty->assign('url_format', $url_format);
			$smarty->assign('count', $count);
			$smarty->assign('page', $page);
			$smarty->assign('pager', $pager);
		}

		$smarty->assign('size', $size);
		$smarty->assign('count', $count);
		$smarty->assign('display', $display);
		$smarty->assign('sort', $sort);

		for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
			$recommend_merchants .= '\'recommend_merchants' . $i . ',';
		}

		$smarty->assign('recommend_merchants', $recommend_merchants);

		if (defined('THEME_EXTENSION')) {
			$smarty->assign('guess_store', get_guess_store($_SESSION['user_id'], 4));
		}
		else {
			$store_best_list = get_shop_goods_count_list(0, $region_id, $area_id, 1, 'store_best');
			$smarty->assign('store_best_list', $store_best_list);
		}

		$smarty->assign('shop_count', $shop_count);
		$cur_url = get_return_self_url();
		$smarty->assign('cur_url', $cur_url);
		$smarty->assign('script_name', 'merchants_shop');
		$smarty->display('merchants_shop_list.dwt');
	}
}

?>
