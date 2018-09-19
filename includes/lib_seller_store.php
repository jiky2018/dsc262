<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_store_header($merchant_id, $store_theme)
{
	$sql = 'select content, shop_color, headbg_img, headtype from ' . $GLOBALS['ecs']->table('seller_shopheader') . ' where ru_id=\'' . $merchant_id . '\' and seller_theme=\'' . $store_theme . '\'';
	$shopheader = $GLOBALS['db']->getRow($sql);
	$content = $shopheader['content'];

	if ($content == '<p><br/></p>') {
		$content = '';
	}

	$content = htmlspecialchars_decode($content);
	$shopheader['content'] = $content;

	if (!empty($shopheader['headbg_img'])) {
		$shopheader['headbg_img'] = str_replace('../', '', $shopheader['headbg_img']);

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();
			$shopheader['headbg_img'] = $bucket_info['endpoint'] . $shopheader['headbg_img'];
		}
	}

	return $shopheader;
}

function get_store_banner_list($ru_id = 0, $store_theme)
{
	$sql = 'select id, img_url, img_link, slide_type from ' . $GLOBALS['ecs']->table('seller_shopslide') . ' where ru_id = \'' . $ru_id . '\' and is_show = 1 and seller_theme=\'' . $store_theme . '\' order by img_order ASC';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$key += 1;
		$arr[$key]['img_url'] = str_replace('../', '', $row['img_url']);

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();
			$arr[$key]['img_url'] = $bucket_info['endpoint'] . $arr[$key]['img_url'];
		}
		else {
			$arr[$key]['img_url'] = $GLOBALS['_CFG']['site_domain'] . $arr[$key]['img_url'];
		}

		$arr[$key]['img_link'] = $row['img_link'];
		$arr[$key]['slide_type'] = $row['slide_type'];
	}

	return $arr;
}

function get_store_win_list($ru_id = 0, $warehouse_id, $area_id, $seller_theme)
{
	$sql = 'select win_type, win_goods_type, win_order, win_goods, win_color, win_name, win_custom from ' . $GLOBALS['ecs']->table('seller_shopwindow') . ' where ru_id = \'' . $ru_id . '\' and is_show = 1 and seller_theme=\'' . $seller_theme . '\' order by win_order ASC';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['win_type'] = $row['win_type'];
		$arr[$key]['win_color'] = $row['win_color'];
		$arr[$key]['win_name'] = $row['win_name'];
		$arr[$key]['win_order'] = $row['win_order'];

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();

			if ($row['win_custom']) {
				$desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $row['win_custom'], 'win_custom');
				$row['win_custom'] = $desc_preg['win_custom'];
			}
		}

		$arr[$key]['win_custom'] = htmlspecialchars_decode($row['win_custom']);
		$arr[$key]['win_goods_type'] = $row['win_goods_type'];

		if (!empty($row['win_goods'])) {
			$arr[$key]['goods_list'] = get_win_goods_list($ru_id, $row['win_goods'], $warehouse_id, $area_id, $seller_theme);
		}
	}

	return $arr;
}

function get_win_goods_list($ru_id, $win_goods, $warehouse_id, $area_id)
{
	$where = '1';
	$leftJoin = '';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ';
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ';
	$where .= ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0';

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$where .= ' AND g.review_status > 2 ';
	}

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$where .= ' and lag.region_id = \'' . $area_id . '\' ';
	}

	$sql = 'SELECT g.goods_id, g.user_id, g.goods_name, ' . $shop_price . ' g.goods_name_style, g.comments_number,g.sales_volume,g.market_price, g.is_new, g.is_best, g.is_hot, ' . ' IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) AS org_price, g.model_price, ' . 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ' . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, g.goods_type, ' . 'g.promote_start_date, g.promote_end_date, g.is_promote, g.goods_brief, g.goods_thumb , g.goods_img ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . ' WHERE ' . $where . ' AND g.goods_id in(' . $win_goods . ') AND g.user_id = \'' . $ru_id . '\' GROUP BY g.goods_id';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$key += 1;

		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$arr[$key]['goods_id'] = $row['goods_id'];
		$arr[$key]['goods_name'] = $row['goods_name'];
		$arr[$key]['market_price'] = price_format($row['market_price']);
		$arr[$key]['shop_price'] = price_format($row['shop_price']);
		$arr[$key]['type'] = $row['goods_type'];
		$arr[$key]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$arr[$key]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$arr[$key]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
		$arr[$key]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
	}

	return $arr;
}

function get_store_bg($merchant_id, $seller_theme)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('seller_shopbg') . ' WHERE ru_id = \'' . $merchant_id . '\' AND seller_theme = \'' . $seller_theme . '\'';
	$res = $GLOBALS['db']->getRow($sql);

	if ($GLOBALS['_CFG']['open_oss'] == 1) {
		$bucket_info = get_bucket_info();
		$res['bgimg'] = $bucket_info['endpoint'] . $res['bgimg'];
	}

	return $res;
}

function get_merchant_cat($ru_id)
{
	$shopMain_category = get_seller_mainshop_cat($ru_id);
	$cat_list = get_category_child_tree($shopMain_category, $ru_id, 1);
	return $cat_list;
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
