<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_collection_goods($user_id, $record_count, $page, $pageFunc, $size = 10)
{
	require_once 'includes/cls_pager.php';

	if (!isset($_COOKIE['province'])) {
		$area_array = get_ip_area_name();

		if ($area_array['county_level'] == 2) {
			$date = array('region_id', 'parent_id', 'region_name');
			$ip_where = 'region_name = \'' . $area_array['area_name'] . '\' AND region_type = 2';
			$city_info = get_table_date('region', $ip_where, $date, 1);
			$date = array('region_id', 'region_name');
			$ip_where = 'region_id = \'' . $city_info[0]['parent_id'] . '\'';
			$province_info = get_table_date('region', $ip_where, $date);
			$ip_where = 'parent_id = \'' . $city_info[0]['region_id'] . '\' order by region_id asc limit 0, 1';
			$district_info = get_table_date('region', $ip_where, $date, 1);
		}
		else if ($area_array['county_level'] == 1) {
			$area_name = $area_array['area_name'];
			$date = array('region_id', 'region_name');
			$ip_where = 'region_name = \'' . $area_name . '\'';
			$province_info = get_table_date('region', $ip_where, $date);
			$ip_where = 'parent_id = \'' . $province_info['region_id'] . '\' order by region_id asc limit 0, 1';
			$city_info = get_table_date('region', $ip_where, $date, 1);
			$ip_where = 'parent_id = \'' . $city_info[0]['region_id'] . '\' order by region_id asc limit 0, 1';
			$district_info = get_table_date('region', $ip_where, $date, 1);
		}
	}

	$province_id = isset($_COOKIE['province']) ? $_COOKIE['province'] : $province_info['region_id'];
	$city_id = isset($_COOKIE['city']) ? $_COOKIE['city'] : $city_info[0]['region_id'];
	$district_id = isset($_COOKIE['district']) ? $_COOKIE['district'] : $district_info[0]['region_id'];
	setcookie('province', $province_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('city', $city_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('district', $district_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$area_info = get_area_info($province_id);
	$area_id = $area_info['region_id'];
	$region_where = 'regionId = \'' . $province_id . '\'';
	$date = array('parent_id');
	$region_id = get_table_date('region_warehouse', $region_where, $date, 2);
	$leftJoin = '';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $region_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
	$collection = new Pager($record_count, $size, '', 0, 0, $page, $pageFunc, 1);
	$limit = $collection->limit;
	$pager = $collection->fpage(array(0, 4, 5, 6, 9));
	$sql = 'SELECT g.goods_thumb, g.user_id, g.goods_id, g.goods_name, g.market_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . 'g.promote_start_date, g.promote_end_date, g.model_attr, c.rec_id, c.is_attention, c.add_time, g.product_price, g.product_promote_price ' . ' FROM ' . $GLOBALS['ecs']->table('collect_goods') . ' AS c' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'ON g.goods_id = c.goods_id ' . $leftJoin . ' LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . (' WHERE g.goods_id = c.goods_id AND c.user_id = \'' . $user_id . '\' ORDER BY c.rec_id DESC ') . $limit;
	$res = $GLOBALS['db']->getAll($sql);
	$goods_list = array();

	foreach ($res as $key => $row) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$goods_list[$row['goods_id']]['rec_id'] = $row['rec_id'];
		$goods_list[$row['goods_id']]['is_attention'] = $row['is_attention'];
		$goods_list[$row['goods_id']]['goods_id'] = $row['goods_id'];
		$goods_list[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$goods_list[$row['goods_id']]['market_price'] = price_format($row['market_price']);
		$goods_list[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$goods_list[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$goods_list[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$goods_list[$row['goods_id']]['goods_thumb'] = get_image_path($arrrow['goods_id'], $row['goods_thumb'], true);
		$goods_list[$row['goods_id']]['add_time'] = local_date('Y-m-d H:i:s', $row['add_time']);
		$mc_all = ments_count_all($row['goods_id']);
		$mc_one = ments_count_rank_num($row['goods_id'], 1);
		$mc_two = ments_count_rank_num($row['goods_id'], 2);
		$mc_three = ments_count_rank_num($row['goods_id'], 3);
		$mc_four = ments_count_rank_num($row['goods_id'], 4);
		$mc_five = ments_count_rank_num($row['goods_id'], 5);
		$goods_list[$row['goods_id']]['zconments'] = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);
	}

	$arr = array('goods_list' => $goods_list, 'record_count' => $record_count, 'pager' => $pager, 'size' => $size);
	return $arr;
}

function get_collection_brands($user_id, $record_count, $page, $pageFunc, $size = 5)
{
	require_once 'includes/cls_pager.php';

	if (!isset($_COOKIE['province'])) {
		$area_array = get_ip_area_name();

		if ($area_array['county_level'] == 2) {
			$date = array('region_id', 'parent_id', 'region_name');
			$ip_where = 'region_name = \'' . $area_array['area_name'] . '\' AND region_type = 2';
			$city_info = get_table_date('region', $ip_where, $date, 1);
			$date = array('region_id', 'region_name');
			$ip_where = 'region_id = \'' . $city_info[0]['parent_id'] . '\'';
			$province_info = get_table_date('region', $ip_where, $date);
			$ip_where = 'parent_id = \'' . $city_info[0]['region_id'] . '\' order by region_id asc limit 0, 1';
			$district_info = get_table_date('region', $ip_where, $date, 1);
		}
		else if ($area_array['county_level'] == 1) {
			$area_name = $area_array['area_name'];
			$date = array('region_id', 'region_name');
			$ip_where = 'region_name = \'' . $area_name . '\'';
			$province_info = get_table_date('region', $ip_where, $date);
			$ip_where = 'parent_id = \'' . $province_info['region_id'] . '\' order by region_id asc limit 0, 1';
			$city_info = get_table_date('region', $ip_where, $date, 1);
			$ip_where = 'parent_id = \'' . $city_info[0]['region_id'] . '\' order by region_id asc limit 0, 1';
			$district_info = get_table_date('region', $ip_where, $date, 1);
		}
	}

	$province_id = isset($_COOKIE['province']) ? $_COOKIE['province'] : $province_info['region_id'];
	$city_id = isset($_COOKIE['city']) ? $_COOKIE['city'] : $city_info[0]['region_id'];
	$district_id = isset($_COOKIE['district']) ? $_COOKIE['district'] : $district_info[0]['region_id'];
	setcookie('province', $province_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('city', $city_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('district', $district_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$area_info = get_area_info($province_id);
	$area_id = $area_info['region_id'];
	$region_where = 'regionId = \'' . $province_id . '\'';
	$date = array('parent_id');
	$region_id = get_table_date('region_warehouse', $region_where, $date, 2);
	$leftJoin = '';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $region_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
	$collection = new Pager($record_count, $size, '', 0, 0, $page, $pageFunc, 1);
	$limit = $collection->limit;
	$pager = $collection->fpage(array(0, 4, 5, 6, 9));
	$sql = 'SELECT cb.*, b.brand_name, b.brand_logo FROM ' . $GLOBALS['ecs']->table('collect_brand') . ' AS cb, ' . $GLOBALS['ecs']->table('brand') . ' AS b ' . (' WHERE cb.brand_id = b.brand_id AND cb.user_id = \'' . $user_id . '\' ORDER BY rec_id DESC ') . $limit;
	$res = $GLOBALS['db']->getAll($sql);
	$brand_list = array();

	foreach ($res as $key => $row) {
		$brand_list[$row['rec_id']]['rec_id'] = $row['rec_id'];
		$brand_list[$row['rec_id']]['brand_id'] = $row['brand_id'];
		$brand_list[$row['rec_id']]['brand_name'] = $row['brand_name'];
		$brand_list[$row['rec_id']]['url'] = build_uri('brandn', array('bid' => $row['brand_id'], 'act' => 'index'), $row['goods_name']);
		$brand_list[$row['rec_id']]['brand_logo'] = $row['brand_logo'];
		$brand_list[$row['rec_id']]['add_time'] = local_date('Y-m-d H:i:s', $row['add_time']);
		$brand_list[$row['rec_id']]['ru_id'] = $row['ru_id'];
		$brand_list[$row['rec_id']]['collect_count'] = get_collect_brand_user_count($row['brand_id']);
		$brand_list[$row['rec_id']]['is_collect'] = get_collect_user_brand($row['brand_id']);
		$brand_id = $row['brand_id'];
		$self = empty($row['ru_id']) ? 1 : 0;
		$cat = 0;
		$goods_size = 10;
		$goods_page = 1;
		$sort = 'sales_volume';
		$order = 'DESC';
		$brand_list[$row['rec_id']]['brand_goods'] = brand_get_goods($brand_id, $mbid, $cat, $goods_size, $goods_page, $sort, $order, $region_id, $area_info['region_id'], $act, $ship, $price_min, $price_max, $self);
	}

	$arr = array('brand_list' => $brand_list, 'record_count' => $record_count, 'pager' => $pager, 'size' => $size);
	return $arr;
}

function brand_get_goods($brand_id, $mbid = 0, $cate, $size, $page, $sort, $order, $warehouse_id = 0, $area_id = 0, $act = '', $ship = '', $price_min = 0, $price_max = 0, $self = 0)
{
	$cate_where = 0 < $cate ? 'AND ' . get_children($cate) : '';
	$leftJoin = '';

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$cate_where .= ' and lag.region_id = \'' . $area_id . '\' ';
	}

	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$cate_where .= ' AND g.review_status > 2 ';
	}

	$tag_where = '';

	if ($ship == 1) {
		$tag_where .= ' AND g.is_shipping = 1 ';
	}

	if ($self == 1) {
		$tag_where .= ' AND g.user_id = 0 ';
	}

	if ($price_min) {
		$tag_where .= ' AND g.shop_price >= ' . $price_min . ' ';
	}

	if ($price_max) {
		$tag_where .= ' AND g.shop_price <= ' . $price_max . ' ';
	}

	if ($sort == 'last_update') {
		$sort = 'g.last_update';
	}

	$tag_where .= 'AND g.brand_id = \'' . $brand_id . '\'';
	$sql = 'SELECT g.goods_id, g.user_id, g.goods_name, g.market_price, g.shop_price AS org_price,g.sales_volume, g.model_price, g.model_attr, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . 'g.promote_start_date, g.promote_end_date, g.is_promote, g.goods_brief, g.goods_thumb , g.goods_img ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ('WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . $cate_where . ' ' . $tag_where . ' ') . ('ORDER BY ' . $sort . ' ' . $order);
	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
	$arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];

		if ($GLOBALS['display'] == 'grid') {
			$arr[$row['goods_id']]['goods_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		}
		else {
			$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
		}

		$arr[$row['goods_id']]['sales_volume'] = $row['sales_volume'];
		$arr[$row['goods_id']]['is_promote'] = $row['is_promote'];
		$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
		$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$arr[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$arr[$row['goods_id']]['goods_brief'] = $row['goods_brief'];
		$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$arr[$row['goods_id']]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
		$arr[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$sql = 'select * from ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' where ru_id=\'' . $row['user_id'] . '\'';
		$basic_info = $GLOBALS['db']->getRow($sql);
		$arr[$row['goods_id']]['kf_type'] = $basic_info['kf_type'];

		if ($basic_info['kf_ww']) {
			$kf_ww = array_filter(preg_split('/\\s+/', $basic_info['kf_ww']));
			$kf_ww = explode('|', $kf_ww[0]);

			if (!empty($kf_ww[1])) {
				$arr[$row['goods_id']]['kf_ww'] = $kf_ww[1];
			}
			else {
				$arr[$row['goods_id']]['kf_ww'] = '';
			}
		}
		else {
			$arr[$row['goods_id']]['kf_ww'] = '';
		}

		if ($basic_info['kf_qq']) {
			$kf_qq = array_filter(preg_split('/\\s+/', $basic_info['kf_qq']));
			$kf_qq = explode('|', $kf_qq[0]);

			if (!empty($kf_qq[1])) {
				$arr[$row['goods_id']]['kf_qq'] = $kf_qq[1];
			}
			else {
				$arr[$row['goods_id']]['kf_qq'] = '';
			}
		}
		else {
			$arr[$row['goods_id']]['kf_qq'] = '';
		}

		$arr[$row['goods_id']]['rz_shopName'] = get_shop_name($row['user_id'], 1);
		$build_uri = array('urid' => $row['user_id'], 'append' => $arr[$row['goods_id']]['rz_shopName']);
		$domain_url = get_seller_domain_url($row['user_id'], $build_uri);
		$arr[$row['goods_id']]['store_url'] = $domain_url['domain_name'];
		$goods_id = $row['goods_id'];
		$count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' where id_value =\'' . $goods_id . '\' AND status = 1 AND parent_id = 0'));
		$arr[$row['goods_id']]['review_count'] = $count;
		$mc_all = ments_count_all($row['goods_id']);
		$mc_one = ments_count_rank_num($row['goods_id'], 1);
		$mc_two = ments_count_rank_num($row['goods_id'], 2);
		$mc_three = ments_count_rank_num($row['goods_id'], 3);
		$mc_four = ments_count_rank_num($row['goods_id'], 4);
		$mc_five = ments_count_rank_num($row['goods_id'], 5);
		$arr[$row['goods_id']]['zconments'] = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);
	}

	return $arr;
}

function get_collection_store($user_id, $record_count, $page, $pageFunc, $size = 5)
{
	require_once 'includes/cls_pager.php';

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

	$province_id = isset($_COOKIE['province']) ? $_COOKIE['province'] : $province_info['region_id'];
	$city_id = isset($_COOKIE['city']) ? $_COOKIE['city'] : $city_info[0]['region_id'];
	$district_id = isset($_COOKIE['district']) ? $_COOKIE['district'] : $district_info[0]['region_id'];
	setcookie('province', $province_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('city', $city_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	setcookie('district', $district_id, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
	$area_info = get_area_info($province_id);
	$area_id = $area_info['region_id'];
	$region_where = 'regionId = \'' . $province_id . '\'';
	$date = array('parent_id');
	$region_id = get_table_date('region_warehouse', $region_where, $date, 2);
	$collection = new Pager($record_count, $size, '', 0, 0, $page, $pageFunc, 1);
	$limit = $collection->limit;
	$pager = $collection->fpage(array(0, 4, 5, 6, 9));
	$sql = 'SELECT m.shoprz_brandName, m.shopNameSuffix, m.shop_id, s.shop_logo, c.ru_id, c.add_time, s.kf_type, s.kf_ww, s.kf_qq, s.kf_tel, brand_thumb  FROM ' . $GLOBALS['ecs']->table('collect_store') . ' as c, ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' as s, ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as m ' . (' WHERE c.ru_id = s.ru_id AND s.ru_id = m.user_id AND c.user_id = \'' . $user_id . '\' order by m.shop_id DESC ') . $limit;
	$res = $GLOBALS['db']->getAll($sql);
	$store_list = array();

	foreach ($res as $key => $row) {
		$store_list[$key]['shop_id'] = $row['shop_id'];
		$store_list[$key]['store_name'] = get_shop_name($row['ru_id'], 1);
		$store_list[$key]['shop_logo'] = str_replace('../', '', $row['shop_logo']);
		$store_list[$key]['count_store'] = $GLOBALS['db']->getOne('SELECT count(*) FROM ' . $GLOBALS['ecs']->table('collect_store') . ' WHERE ru_id = \'' . $row['ru_id'] . '\'');
		$store_list[$key]['add_time'] = local_date('Y-m-d', $row['add_time']);
		$store_list[$key]['kf_type'] = $row['kf_type'];
		$store_list[$key]['kf_tel'] = $row['kf_tel'];

		if ($GLOBALS['_CFG']['customer_service'] == 0) {
			$ru_id = 0;
		}
		else {
			$ru_id = $row['ru_id'];
		}

		$shop_information = get_shop_name($ru_id);
		$store_list[$key]['is_IM'] = $shop_information['is_IM'];

		if ($ru_id == 0) {
			if ($GLOBALS['db']->getOne('SELECT kf_im_switch FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = 0', true)) {
				$store_list[$key]['is_dsc'] = true;
			}
			else {
				$store_list[$key]['is_dsc'] = false;
			}
		}
		else {
			$store_list[$key]['is_dsc'] = false;
		}

		if ($row['kf_qq']) {
			$kf_qq = array_filter(preg_split('/\\s+/', $row['kf_qq']));
			$kf_qq = explode('|', $kf_qq[0]);

			if (!empty($kf_qq[1])) {
				$store_list[$key]['kf_qq'] = $kf_qq[1];
			}
			else {
				$store_list[$key]['kf_qq'] = '';
			}
		}
		else {
			$store_list[$key]['kf_qq'] = '';
		}

		if ($row['kf_ww']) {
			$kf_ww = array_filter(preg_split('/\\s+/', $row['kf_ww']));
			$kf_ww = explode('|', $kf_ww[0]);

			if (!empty($kf_ww[1])) {
				$store_list[$key]['kf_ww'] = $kf_ww[1];
			}
			else {
				$store_list[$key]['kf_ww'] = '';
			}
		}
		else {
			$store_list[$key]['kf_ww'] = '';
		}

		$store_list[$key]['ru_id'] = $row['ru_id'];
		$store_list[$key]['brand_thumb'] = $row['brand_thumb'];
		$build_uri = array('urid' => $row['ru_id'], 'append' => $store_list[$key]['store_name']);
		$domain_url = get_seller_domain_url($row['ru_id'], $build_uri);
		$store_list[$key]['url'] = $domain_url['domain_name'];
		$store_list[$key]['merch_cmt'] = get_merchants_goods_comment($row['ru_id']);
		$store_list[$key]['hot_goods'] = get_user_store_goods_list($row['ru_id'], $region_id, $area_id, 'store_hot');
		$store_list[$key]['new_goods'] = get_user_store_goods_list($row['ru_id'], $region_id, $area_id, 'store_new');
		$store_list[$key]['common_goods'] = get_user_store_goods_list($row['ru_id'], $region_id, $area_id, '');
		$store_list[$key]['new_goods_count'] = count($store_list[$key]['new_goods']);
	}

	$arr = array('store_list' => $store_list, 'record_count' => $record_count, 'pager' => $pager, 'size' => $size);
	return $arr;
}

function get_user_store_goods_list($user_id, $region_id, $area_id, $type = '', $sort = 'last_update', $order = 'DESC', $limit = 'LIMIT 0,10')
{
	$leftJoin = '';
	$where = '';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $region_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$where .= ' AND g.review_status > 2 ';
	}

	if ($type == 'store_hot') {
		$type = ' AND store_hot = 1';
	}
	else if ($type == 'store_new') {
		$type = ' AND store_new = 1';
	}
	else {
		$type = '';
	}

	$sql = 'SELECT g.goods_thumb, g.goods_id, g.goods_name, g.market_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . 'g.promote_start_date, g.promote_end_date, g.model_attr, g.product_price, g.product_promote_price ' . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . ' LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . (' WHERE g.user_id = \'' . $user_id . '\' ') . $type . (' ' . $where . ' ORDER BY g.') . $sort . (' ' . $order . ' ') . $limit;
	$res = $GLOBALS['db']->getAll($sql);
	$goods_list = array();

	foreach ($res as $key => $row) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$goods_list[$row['goods_id']]['goods_id'] = $row['goods_id'];
		$goods_list[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$goods_list[$row['goods_id']]['market_price'] = price_format($row['market_price']);
		$goods_list[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$goods_list[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$goods_list[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$goods_list[$row['goods_id']]['goods_thumb'] = get_image_path($arrrow['goods_id'], $row['goods_thumb'], true);
		$mc_all = ments_count_all($row['goods_id']);
		$mc_one = ments_count_rank_num($row['goods_id'], 1);
		$mc_two = ments_count_rank_num($row['goods_id'], 2);
		$mc_three = ments_count_rank_num($row['goods_id'], 3);
		$mc_four = ments_count_rank_num($row['goods_id'], 4);
		$mc_five = ments_count_rank_num($row['goods_id'], 5);
		$goods_list[$row['goods_id']]['zconments'] = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);
	}

	return $goods_list;
}

function get_booking_rec($user_id, $goods_id)
{
	$sql = 'SELECT COUNT(*) ' . 'FROM ' . $GLOBALS['ecs']->table('booking_goods') . ('WHERE user_id = \'' . $user_id . '\' AND goods_id = \'' . $goods_id . '\' AND is_dispose = 0');
	return $GLOBALS['db']->getOne($sql);
}

function get_message_list($user_id, $user_name, $num, $start, $order_id = 0, $is_order = 0)
{
	$left_join = '';
	$msg = array();

	if ($is_order) {
		$sql = 'SELECT f.*,oi.order_sn FROM ' . $GLOBALS['ecs']->table('feedback') . ' AS f';
		$left_join .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ON f.order_id = oi.order_id ';
	}
	else {
		$sql = 'SELECT f.* FROM ' . $GLOBALS['ecs']->table('feedback') . ' AS f';
	}

	if ($order_id) {
		$sql .= $left_join . (' WHERE f.parent_id = 0 AND f.msg_status = 0 AND f.order_id = \'' . $order_id . '\' AND f.user_id = \'' . $user_id . '\' ORDER BY f.msg_time DESC');
	}
	else {
		if ($is_order) {
			$where = ' AND f.order_id > 0 ';
		}
		else {
			$where = ' AND f.order_id = 0 ';
		}

		$sql .= $left_join . (' WHERE f.parent_id = 0 AND f.msg_status = 0 AND f.user_id = \'' . $user_id . '\' AND f.user_name = \'') . $_SESSION['user_name'] . ('\' ' . $where . ' ORDER BY f.msg_time DESC');
	}

	$res = $GLOBALS['db']->SelectLimit($sql, $num, $start);

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$reply = array();
		$sql = 'SELECT user_name, user_email, msg_time, msg_content' . ' FROM ' . $GLOBALS['ecs']->table('feedback') . ' WHERE parent_id = \'' . $rows['msg_id'] . '\'';
		$reply = $GLOBALS['db']->getRow($sql);

		if ($reply) {
			$msg[$rows['msg_id']]['re_user_name'] = $reply['user_name'];
			$msg[$rows['msg_id']]['re_user_email'] = $reply['user_email'];
			$msg[$rows['msg_id']]['re_msg_time'] = local_date($GLOBALS['_CFG']['time_format'], $reply['msg_time']);
			$msg[$rows['msg_id']]['re_msg_content'] = nl2br(htmlspecialchars($reply['msg_content']));
		}

		$msg[$rows['msg_id']]['msg_content'] = nl2br(htmlspecialchars($rows['msg_content']));
		$msg[$rows['msg_id']]['msg_time'] = local_date($GLOBALS['_CFG']['time_format'], $rows['msg_time']);
		$msg[$rows['msg_id']]['msg_type'] = nl2br(htmlspecialchars($rows['msg_type']));
		$msg[$rows['msg_id']]['msg_title'] = nl2br(htmlspecialchars($rows['msg_title']));
		$message_type = pathinfo($rows['message_img'], PATHINFO_EXTENSION);

		if (in_array($message_type, array('gif', 'jpg', 'png'))) {
			$msg[$rows['msg_id']]['message_type'] = 1;
		}

		$msg[$rows['msg_id']]['message_img'] = $rows['message_img'];
		$msg[$rows['msg_id']]['order_id'] = $rows['order_id'];
		$msg[$rows['msg_id']]['order_sn'] = isset($rows['order_sn']) ? $rows['order_sn'] : '';
	}

	return $msg;
}

function add_message($message)
{
	$upload_size_limit = $GLOBALS['_CFG']['upload_size_limit'] == '-1' ? ini_get('upload_max_filesize') : $GLOBALS['_CFG']['upload_size_limit'];
	$status = 1 - $GLOBALS['_CFG']['message_check'];
	$last_char = strtolower($upload_size_limit[strlen($upload_size_limit) - 1]);

	switch ($last_char) {
	case 'm':
		$upload_size_limit *= 1024 * 1024;
		break;

	case 'k':
		$upload_size_limit *= 1024;
		break;
	}

	if ($message['upload']) {
		if ($upload_size_limit < $_FILES['message_img']['size'] / 1024) {
			$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['upload_file_limit'], $upload_size_limit));
			return false;
		}

		$img_name = upload_file($_FILES['message_img'], 'feedbackimg');

		if ($img_name === false) {
			return false;
		}
	}
	else {
		$img_name = '';
	}

	if (empty($message['msg_title'])) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['msg_title_empty']);
		return false;
	}

	$message['msg_area'] = isset($message['msg_area']) ? intval($message['msg_area']) : 0;
	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('feedback') . ' (msg_id, parent_id, user_id, user_name, user_email, msg_title, msg_type, msg_status,  msg_content, msg_time, message_img, order_id, msg_area)' . (' VALUES (NULL, 0, \'' . $message['user_id'] . '\', \'' . $message['user_name'] . '\', \'' . $message['user_email'] . '\', ') . (' \'' . $message['msg_title'] . '\', \'' . $message['msg_type'] . '\', \'' . $status . '\', \'' . $message['msg_content'] . '\', \'') . gmtime() . ('\', \'' . $img_name . '\', \'' . $message['order_id'] . '\', \'' . $message['msg_area'] . '\')');
	$GLOBALS['db']->query($sql);
	return true;
}

function get_user_tags($user_id = 0)
{
	if (empty($user_id)) {
		$GLOBALS['error_no'] = 1;
		return false;
	}

	$tags = get_tags(0, $user_id);

	if (!empty($tags)) {
		color_tag($tags);
	}

	return $tags;
}

function delete_tag($tag_words, $user_id)
{
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('tag') . (' WHERE tag_words = \'' . $tag_words . '\' AND user_id = \'' . $user_id . '\'');
	return $GLOBALS['db']->query($sql);
}

function get_booking_list($user_id, $num, $start)
{
	$booking = array();
	$sql = 'SELECT bg.rec_id, bg.goods_id, bg.goods_number, bg.booking_time, bg.dispose_note, g.goods_name, g.goods_thumb ' . 'FROM ' . $GLOBALS['ecs']->table('booking_goods') . ' AS bg , ' . $GLOBALS['ecs']->table('goods') . ' AS g' . (' WHERE bg.goods_id = g.goods_id AND bg.user_id = \'' . $user_id . '\' ORDER BY bg.booking_time DESC');
	$res = $GLOBALS['db']->SelectLimit($sql, $num, $start);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if (empty($row['dispose_note'])) {
			$row['dispose_note'] = 'N/A';
		}

		$booking[] = array('rec_id' => $row['rec_id'], 'goods_name' => $row['goods_name'], 'goods_number' => $row['goods_number'], 'goods_thumb' => get_image_path($row['goods_id'], $row['goods_thumb'], true), 'booking_time' => local_date($GLOBALS['_CFG']['date_format'], $row['booking_time']), 'dispose_note' => $row['dispose_note'], 'url' => build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']));
	}

	return $booking;
}

function get_goodsinfo($goods_id)
{
	$info = array();
	$sql = 'SELECT goods_name FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\'');
	$info['goods_name'] = $GLOBALS['db']->getOne($sql);
	$info['goods_number'] = 1;
	$info['id'] = $goods_id;

	if (!empty($_SESSION['user_id'])) {
		$row = array();
		$sql = 'SELECT ua.consignee, ua.email, ua.tel, ua.mobile ' . 'FROM ' . $GLOBALS['ecs']->table('user_address') . ' AS ua, ' . $GLOBALS['ecs']->table('users') . ' AS u' . (' WHERE u.address_id = ua.address_id AND u.user_id = \'' . $_SESSION['user_id'] . '\'');
		$row = $GLOBALS['db']->getRow($sql);
		$info['consignee'] = empty($row['consignee']) ? '' : $row['consignee'];
		$info['email'] = empty($row['email']) ? '' : $row['email'];
		$info['tel'] = empty($row['mobile']) ? (empty($row['tel']) ? '' : $row['tel']) : $row['mobile'];
	}

	return $info;
}

function delete_booking($booking_id, $user_id)
{
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('booking_goods') . (' WHERE rec_id = \'' . $booking_id . '\' AND user_id = \'' . $user_id . '\'');
	return $GLOBALS['db']->query($sql);
}

function add_booking($booking)
{
	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('booking_goods') . (' VALUES (\'\', \'' . $_SESSION['user_id'] . '\', \'' . $booking['email'] . '\', \'' . $booking['linkman'] . '\', ') . ('\'' . $booking['tel'] . '\', \'' . $booking['goods_id'] . '\', \'' . $booking['desc'] . '\', ') . ('\'' . $booking['goods_amount'] . '\', \'') . gmtime() . '\', 0, \'\', 0, \'\')';
	$GLOBALS['db']->query($sql) || exit($GLOBALS['db']->errorMsg());
	return $GLOBALS['db']->insert_id();
}

function insert_user_account($surplus, $amount)
{
	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('user_account') . ' (user_id, admin_user, amount, add_time, paid_time, admin_note, user_note, process_type, payment, is_paid,deposit_fee)' . (' VALUES (\'' . $surplus['user_id'] . '\', \'\', \'' . $amount . '\', \'') . gmtime() . ('\', 0, \'\', \'' . $surplus['user_note'] . '\', \'' . $surplus['process_type'] . '\', \'' . $surplus['payment'] . '\', 0,\'' . $surplus['deposit_fee'] . '\')');
	$GLOBALS['db']->query($sql);
	return $GLOBALS['db']->insert_id();
}

function insert_user_account_fields($user_account_fields)
{
	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('user_account_fields') . ' (user_id, account_id,bank_number, real_name)' . (' VALUES (\'' . $user_account_fields['user_id'] . '\',\'' . $user_account_fields['account_id'] . '\', \'' . $user_account_fields['bank_number'] . '\',\'' . $user_account_fields['real_name'] . '\')');
	$GLOBALS['db']->query($sql);
}

function update_user_account($surplus)
{
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_account') . ' SET ' . ('amount     = \'' . $surplus['amount'] . '\', ') . ('user_note  = \'' . $surplus['user_note'] . '\', ') . ('payment    = \'' . $surplus['payment'] . '\' ') . ('WHERE id   = \'' . $surplus['rec_id'] . '\'');
	$GLOBALS['db']->query($sql);
	return $surplus['rec_id'];
}

function insert_pay_log($id, $amount, $type = PAY_SURPLUS, $is_paid = 0)
{
	if ($id) {
		$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('pay_log') . ' (order_id, order_amount, order_type, is_paid)' . (' VALUES  (\'' . $id . '\', \'' . $amount . '\', \'' . $type . '\', \'' . $is_paid . '\')');
		$GLOBALS['db']->query($sql);
		$log_id = $GLOBALS['db']->insert_id();
	}
	else {
		$log_id = 0;
	}

	return $log_id;
}

function get_paylog_id($surplus_id, $pay_type = PAY_SURPLUS)
{
	$sql = 'SELECT log_id FROM' . $GLOBALS['ecs']->table('pay_log') . (' WHERE order_id = \'' . $surplus_id . '\' AND order_type = \'' . $pay_type . '\' AND is_paid = 0');
	return $GLOBALS['db']->getOne($sql);
}

function get_surplus_info($surplus_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_account') . (' WHERE id = \'' . $surplus_id . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function get_online_payment_list($include_balance = true)
{
	$sql = 'SELECT pay_id, pay_code, pay_name, pay_fee, pay_desc ' . 'FROM ' . $GLOBALS['ecs']->table('payment') . ' WHERE enabled = 1 AND is_cod <> 1';

	if (!$include_balance) {
		$sql .= ' AND pay_code not in(\'balance\',\'chunsejinrong\',\'onlinepay\')';
	}

	$modules = $GLOBALS['db']->getAll($sql);
	include_once ROOT_PATH . 'includes/lib_compositor.php';
	$arr = array();

	foreach ($modules as $key => $row) {
		$pay_code = substr($row['pay_code'], 0, 4);

		if ($pay_code != 'pay_') {
			$arr[$key]['pay_id'] = $row['pay_id'];
			$arr[$key]['pay_code'] = $row['pay_code'];
			$arr[$key]['pay_name'] = $row['pay_name'];
			$arr[$key]['pay_fee'] = $row['pay_fee'];
			$arr[$key]['pay_desc'] = $row['pay_desc'];
		}
	}

	return $arr;
}

function get_account_log($user_id, $num, $start)
{
	$account_log = array();
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_account') . (' WHERE user_id = \'' . $user_id . '\'') . ' AND process_type ' . db_create_in(array(SURPLUS_SAVE, SURPLUS_RETURN)) . ' ORDER BY add_time DESC';
	$res = $GLOBALS['db']->selectLimit($sql, $num, $start);

	if ($res) {
		while ($rows = $GLOBALS['db']->fetchRow($res)) {
			$rows['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $rows['add_time']);
			$rows['admin_note'] = nl2br(htmlspecialchars($rows['admin_note']));
			$rows['short_admin_note'] = '' < $rows['admin_note'] ? sub_str($rows['admin_note'], 30) : 'N/A';
			$rows['user_note'] = nl2br(htmlspecialchars($rows['user_note']));
			$rows['short_user_note'] = '' < $rows['user_note'] ? sub_str($rows['user_note'], 30) : 'N/A';
			$rows['pay_status'] = $rows['is_paid'] == 0 ? $GLOBALS['_LANG']['un_confirmed'] : $GLOBALS['_LANG']['is_confirmed'];
			$rows['amount'] = price_format(abs($rows['amount']), false);

			if ($rows['process_type'] == 0) {
				$rows['type'] = $GLOBALS['_LANG']['surplus_type_0'];
			}
			else {
				$rows['type'] = $GLOBALS['_LANG']['surplus_type_1'];
			}

			if (0 < $rows['pay_id']) {
				$pid = $rows['pay_id'];
			}
			else {
				$sql = 'SELECT pay_id FROM ' . $GLOBALS['ecs']->table('payment') . ' WHERE pay_name = \'' . $rows['payment'] . '\' AND enabled = 1';
				$pid = $GLOBALS['db']->getOne($sql, true);
			}

			if ($rows['is_paid'] == 0 && $rows['process_type'] == 0) {
				$rows['handle'] = '<a href="user.php?act=pay&id=' . $rows['id'] . '&pid=' . $pid . '" class="ftx-01">' . $GLOBALS['_LANG']['pay'] . '</a>';
			}

			$account_log[] = $rows;
		}

		return $account_log;
	}
	else {
		return false;
	}
}

function get_user_account_log($id, $user_id = 0)
{
	$user_id = empty($user_id) ? $_SESSION['user_id'] : $user_id;
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_account') . (' WHERE id = \'' . $id . '\' AND user_id = \'' . $user_id . '\' LIMIT 1');
	$rows = $GLOBALS['db']->getRow($sql);

	if ($rows) {
		$rows['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $rows['add_time']);
		$rows['admin_note'] = nl2br(htmlspecialchars($rows['admin_note']));
		$rows['short_admin_note'] = '' < $rows['admin_note'] ? sub_str($rows['admin_note'], 30) : 'N/A';
		$rows['user_note'] = nl2br(htmlspecialchars($rows['user_note']));
		$rows['short_user_note'] = '' < $rows['user_note'] ? sub_str($rows['user_note'], 30) : 'N/A';
		$rows['pay_status'] = $rows['is_paid'] == 0 ? $GLOBALS['_LANG']['un_confirmed'] : $GLOBALS['_LANG']['is_confirmed'];
		$rows['amount'] = price_format(abs($rows['amount']), false);

		if ($rows['process_type'] == 0) {
			$rows['type'] = $GLOBALS['_LANG']['surplus_type_0'];
		}
		else {
			$rows['type'] = $GLOBALS['_LANG']['surplus_type_1'];
		}

		if (0 < $rows['pay_id']) {
			$pid = $rows['pay_id'];
		}
		else {
			$sql = 'SELECT pay_id FROM ' . $GLOBALS['ecs']->table('payment') . ' WHERE pay_name = \'' . $rows['payment'] . '\' AND enabled = 1';
			$pid = $GLOBALS['db']->getOne($sql, true);
		}

		if ($rows['is_paid'] == 0 && $rows['process_type'] == 0) {
			$rows['handle'] = '<a href="user.php?act=pay&id=' . $rows['id'] . '&pid=' . $pid . '" class="ftx-01">' . $GLOBALS['_LANG']['pay'] . '</a>';
		}
	}

	return $rows;
}

function del_user_account($rec_id, $user_id)
{
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('user_account') . (' WHERE is_paid = 0 AND id = \'' . $rec_id . '\' AND user_id = \'' . $user_id . '\'');
	return $GLOBALS['db']->query($sql);
}

function del_user_account_fields($acount_id, $user_id)
{
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('user_account_fields') . (' WHERE account_id = \'' . $acount_id . '\' AND user_id = \'' . $user_id . '\'');
	return $GLOBALS['db']->query($sql);
}

function get_user_surplus($user_id)
{
	$sql = 'SELECT user_money FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $user_id . '\'');
	$res = $GLOBALS['db']->getOne($sql);
	return $res;
}

function add_tag($id, $tag)
{
	if (empty($tag)) {
		return NULL;
	}

	$arr = explode(',', $tag);

	foreach ($arr as $val) {
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('tag') . ' WHERE user_id = \'' . $_SESSION['user_id'] . ('\' AND goods_id = \'' . $id . '\' AND tag_words = \'' . $val . '\'');

		if ($GLOBALS['db']->getOne($sql) == 0) {
			$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('tag') . ' (user_id, goods_id, tag_words) ' . 'VALUES (\'' . $_SESSION['user_id'] . ('\', \'' . $id . '\', \'' . $val . '\')');
			$GLOBALS['db']->query($sql);
		}
	}
}

function color_tag(&$tags)
{
	$tagmark = array(
		array('color' => '#666666', 'size' => '0.8em', 'ifbold' => 1),
		array('color' => '#333333', 'size' => '0.9em', 'ifbold' => 0),
		array('color' => '#006699', 'size' => '1.0em', 'ifbold' => 1),
		array('color' => '#CC9900', 'size' => '1.1em', 'ifbold' => 0),
		array('color' => '#666633', 'size' => '1.2em', 'ifbold' => 1),
		array('color' => '#993300', 'size' => '1.3em', 'ifbold' => 0),
		array('color' => '#669933', 'size' => '1.4em', 'ifbold' => 1),
		array('color' => '#3366FF', 'size' => '1.5em', 'ifbold' => 0),
		array('color' => '#197B30', 'size' => '1.6em', 'ifbold' => 1)
		);
	$maxlevel = count($tagmark);
	$tcount = $scount = array();

	foreach ($tags as $val) {
		$tcount[] = $val['tag_count'];
	}

	$tcount = array_unique($tcount);
	sort($tcount);
	$tempcount = count($tcount);
	$per = $tempcount <= $maxlevel ? 1 : $maxlevel / ($tempcount - 1);

	foreach ($tcount as $key => $val) {
		$lvl = floor($per * $key);
		$scount[$val] = $lvl;
	}

	$rewrite = 0 < intval($GLOBALS['_CFG']['rewrite']);

	foreach ($tags as $key => $val) {
		$lvl = $scount[$val['tag_count']];
		$tags[$key]['color'] = $tagmark[$lvl]['color'];
		$tags[$key]['size'] = $tagmark[$lvl]['size'];
		$tags[$key]['bold'] = $tagmark[$lvl]['ifbold'];

		if ($rewrite) {
			if (strtolower(EC_CHARSET) !== 'utf-8') {
				$tags[$key]['url'] = 'tag-' . urlencode(urlencode($val['tag_words'])) . '.html';
			}
			else {
				$tags[$key]['url'] = 'tag-' . urlencode($val['tag_words']) . '.html';
			}
		}
		else {
			$tags[$key]['url'] = 'search.php?keywords=' . urlencode($val['tag_words']);
		}
	}

	shuffle($tags);
}

function get_user_prompt($user_id)
{
	$prompt = array();
	$now = gmtime();
	$sql = 'SELECT act_id, goods_name, end_time ' . 'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' WHERE act_type = \'' . GAT_SNATCH . '\' AND review_status = 3' . (' AND (is_finished = 1 OR (is_finished = 0 AND end_time <= \'' . $now . '\'))');
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$act_id = $row['act_id'];
		$result = get_snatch_result($act_id);
		if (isset($result['order_count']) && $result['order_count'] == 0 && $result['user_id'] == $user_id) {
			$prompt[] = array('text' => sprintf($GLOBALS['_LANG']['your_snatch'], $row['goods_name'], $row['act_id']), 'add_time' => $row['end_time']);
		}

		if (isset($auction['last_bid']) && $auction['last_bid']['bid_user'] == $user_id && $auction['order_count'] == 0) {
			$prompt[] = array('text' => sprintf($GLOBALS['_LANG']['your_auction'], $row['goods_name'], $row['act_id']), 'add_time' => $row['end_time']);
		}
	}

	$sql = 'SELECT act_id, goods_name, end_time ' . 'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' WHERE act_type = \'' . GAT_AUCTION . '\' AND review_status = 3' . (' AND (is_finished = 1 OR (is_finished = 0 AND end_time <= \'' . $now . '\'))');
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$act_id = $row['act_id'];
		$auction = auction_info($act_id);
		if (isset($auction['last_bid']) && $auction['last_bid']['bid_user'] == $user_id && $auction['order_count'] == 0) {
			$prompt[] = array('text' => sprintf($GLOBALS['_LANG']['your_auction'], $row['goods_name'], $row['act_id']), 'add_time' => $row['end_time']);
		}
	}

	$cmp = function($a, $b) {
		if ($a['add_time'] == $b['add_time']) {
			return 0;
		}

		return $a['add_time'] < $b['add_time'] ? 1 : -1;
	};
	usort($prompt, $cmp);

	foreach ($prompt as $key => $val) {
		$prompt[$key]['formated_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['add_time']);
	}

	return $prompt;
}

function get_comment_list($user_id, $page_size, $start)
{
	$sql = 'SELECT c.*, g.goods_name AS cmt_name, r.content AS reply_content, r.add_time AS reply_time ' . ' FROM ' . $GLOBALS['ecs']->table('comment') . ' AS c ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('comment') . ' AS r ' . ' ON r.parent_id = c.comment_id AND r.parent_id > 0 AND r.single_id = 0 AND r.dis_id = 0 ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ' ON c.comment_type=0 AND c.id_value = g.goods_id ' . (' WHERE c.user_id=\'' . $user_id . '\'');
	$res = $GLOBALS['db']->SelectLimit($sql, $page_size, $start);
	$comments = array();
	$to_article = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$row['formated_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);

		if ($row['reply_time']) {
			$row['formated_reply_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['reply_time']);
		}

		if ($row['comment_type'] == 1) {
			$to_article[] = $row['id_value'];
		}

		$row['goods_url'] = build_uri('goods', array('gid' => $row['id_value']), $row['goods_name']);
		$comments[] = $row;
	}

	if ($to_article) {
		$sql = 'SELECT article_id , title FROM ' . $GLOBALS['ecs']->table('article') . ' WHERE ' . db_create_in($to_article, 'article_id');
		$arr = $GLOBALS['db']->getAll($sql);
		$to_cmt_name = array();

		foreach ($arr as $row) {
			$to_cmt_name[$row['article_id']] = $row['title'];
		}

		foreach ($comments as $key => $row) {
			if ($row['comment_type'] == 1) {
				$comments[$key]['cmt_name'] = isset($to_cmt_name[$row['id_value']]) ? $to_cmt_name[$row['id_value']] : '';
			}
		}
	}

	return $comments;
}

function get_user_order_comment_list($user_id, $type = 0, $sign = 0, $order_id = 0, $size = 0, $start = 0)
{
	$where = ' AND oi.order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) . '  AND oi.shipping_status = \'' . SS_RECEIVED . '\' AND oi.pay_status ' . db_create_in(array(PS_PAYED, PS_PAYING));
	$sql = 'SELECT GROUP_CONCAT(rec_id) AS rec_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\'');
	$rec = $GLOBALS['db']->getRow($sql);

	if (0 < $order_id) {
		$where .= ' AND og.rec_id IN(' . $rec['rec_id'] . ') ';
	}

	if ($sign == 0) {
		$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' AS c WHERE c.comment_type = 0 AND c.id_value = g.goods_id AND c.rec_id = og.rec_id AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\') = 0 ');
	}
	else if ($sign == 1) {
		$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' AS c WHERE c.comment_type = 0 AND c.id_value = g.goods_id AND c.rec_id = og.rec_id AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\') > 0 ');
		$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment_img') . ' AS ci, ' . $GLOBALS['ecs']->table('comment') . ' AS c' . (' WHERE c.comment_type = 0 AND c.id_value = g.goods_id AND ci.rec_id = og.rec_id AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\' AND ci.comment_id = c.comment_id ) = 0 ');
	}
	else if ($sign == 2) {
		$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' AS c WHERE c.comment_type = 0 AND c.id_value = g.goods_id AND c.rec_id = og.rec_id AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\') > 0 ');
		$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment_img') . ' AS ci, ' . $GLOBALS['ecs']->table('comment') . ' AS c' . (' WHERE c.comment_type = 0 AND c.id_value = g.goods_id AND ci.rec_id = og.rec_id AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\' AND ci.comment_id = c.comment_id ) > 0 ');
	}

	if ($type == 1) {
		$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ON og.order_id = oi.order_id ' . 'LEFT JOIN  ' . $GLOBALS['ecs']->table('goods') . ' AS g ON og.goods_id = g.goods_id ' . ('WHERE og.goods_id = g.goods_id AND oi.user_id = \'' . $user_id . '\' ' . $where . ' ORDER BY oi.add_time DESC');
		$arr = $GLOBALS['db']->getOne($sql);
	}
	else {
		$sql = 'SELECT og.rec_id, og.order_id, og.goods_id, og.goods_name, oi.add_time, g.goods_thumb, g.goods_product_tag, og.ru_id,oi.order_sn,og.goods_number,og.goods_price,og.goods_attr FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ON og.order_id = oi.order_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON og.goods_id = g.goods_id ' . ('WHERE og.goods_id = g.goods_id AND oi.user_id = \'' . $user_id . '\' ' . $where . ' ORDER BY oi.add_time DESC');

		if (0 < $size) {
			$res = $GLOBALS['db']->SelectLimit($sql, $size, $start);
		}
		else {
			$res = $GLOBALS['db']->query($sql);
		}

		$arr = array();

		while ($row = $GLOBALS['db']->fetchRow($res)) {
			$row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
			$row['goods_thumb'] = get_image_path($goods_id, $row['goods_thumb'], true);
			$row['impression_list'] = !empty($row['goods_product_tag']) ? explode(',', $row['goods_product_tag']) : array();
			$row['goods_url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
			$row['goods_price'] = price_format($row['goods_price']);
			$row['comment'] = get_order_goods_comment($row['goods_id'], $row['rec_id'], $user_id);
			$arr[] = $row;
		}
	}

	return $arr;
}

function get_order_goods_comment($goods_id, $rec_id, $user_id)
{
	$sql = 'SELECT c.comment_id, c.comment_rank, c.content, c.id_value, c.order_id, c.rec_id, c.user_id, c.goods_tag FROM ' . $GLOBALS['ecs']->table('comment') . (' AS c WHERE c.comment_type = 0 AND c.id_value = \'' . $goods_id . '\' AND c.rec_id = \'' . $rec_id . '\' AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\'');
	$res = $GLOBALS['db']->getRow($sql);
	$res['content'] = nl2br(str_replace('\\n', '<br />', htmlspecialchars($res['content'])));
	$res['goods_tag'] = !empty($res['goods_tag']) ? explode(',', $res['goods_tag']) : array();
	$img_list = get_img_list($goods_id, $res['comment_id']);
	$res['comment_id'] = isset($res['comment_id']) && !empty($res['comment_id']) ? $res['comment_id'] : 0;
	$res['img_list'] = $img_list;
	return $res;
}

function get_bind_oath_info($user_id, $identity_type = 'qq')
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('users_auth') . (' WHERE user_id = \'' . $user_id . '\' AND identity_type = \'' . $identity_type . '\' LIMIT 1');
	return $GLOBALS['db']->getRow($sql);
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
