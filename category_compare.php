<?php
//QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_area.php';
$area_info = get_area_info($province_id);
$area_id = $area_info['region_id'];
$where = 'regionId = \'' . $province_id . '\'';
$date = array('parent_id');
$region_id = get_table_date('region_warehouse', $where, $date, 2);
if (!empty($_REQUEST['goods']) && is_array($_REQUEST['goods']) && 1 < count($_REQUEST['goods'])) {
	$leftJoin = ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $region_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
	$compare = !empty($_REQUEST['compare']) ? $_REQUEST['compare'] : '';
	$highlight = !empty($_REQUEST['highlight']) ? $_REQUEST['highlight'] : '';
	$where = db_create_in($_REQUEST['goods'], 'id_value');
	$sql = 'SELECT id_value , AVG(comment_rank) AS cmt_rank, COUNT(*) AS cmt_count' . ' FROM ' . $ecs->table('comment') . (' WHERE ' . $where . ' AND comment_type = 0') . ' GROUP BY id_value ';
	$query = $db->query($sql);
	$cmt = array();

	while ($row = $db->fetch_array($query)) {
		$cmt[$row['id_value']] = $row;
	}

	$where = db_create_in($_REQUEST['goods'], 'g.goods_id');
	$sql = 'SELECT g.goods_id, g.goods_type, g.goods_name, g.user_id, g.brand_id, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . 'g.promote_start_date, g.promote_end_date, g.is_promote, ' . ' g.goods_weight, g.goods_thumb, g.goods_brief, ' . 'a.attr_name, v.attr_value, a.attr_id, a.attr_input_category, b.brand_name, ' . ('IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\') AS rank_price ') . 'FROM ' . $ecs->table('goods') . ' AS g ' . 'LEFT JOIN ' . $ecs->table('goods_attr') . ' AS v ON v.goods_id = g.goods_id ' . 'LEFT JOIN ' . $ecs->table('attribute') . ' AS a ON a.attr_id = v.attr_id ' . 'LEFT JOIN ' . $ecs->table('brand') . ' AS b ON g.brand_id = b.brand_id ' . $leftJoin . 'LEFT JOIN ' . $ecs->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ('WHERE g.is_delete = 0 AND ' . $where . ' ') . 'ORDER BY a.sort_order, a.attr_id, v.goods_attr_id';
	$res = $db->query($sql);
	$basic_arr = array();
	$ids = $_REQUEST['goods'];
	$attr_name = array();
	$type_id = 0;
	$param_goods_id = '';
	$g_count = count($_REQUEST['goods']);
	$new_goods_arr = array_unique($_REQUEST['goods']);
	$n_count = count($new_goods_arr);

	foreach ($_REQUEST['goods'] as $goods_id_val) {
		if (empty($goods_id_val) || $g_count != $n_count) {
			show_message($GLOBALS['_LANG']['prompt_page']);
		}

		$param_goods_id .= 'goods[]=' . $goods_id_val . '&amp;';
	}

	$param_goods_id = substr($param_goods_id, 0, -5);

	while ($row = $db->fetchRow($res)) {
		$goods_id = $row['goods_id'];

		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		if (0 < $promote_price) {
			$row['shop_price'] = $promote_price;
		}

		$type_id = $row['goods_type'];
		$goods_list[$goods_id]['goods_id'] = $goods_id;
		$goods_list[$goods_id]['url'] = build_uri('goods', array('gid' => $goods_id), $row['goods_name']);
		$goods_list[$goods_id]['goods_name'] = $row['goods_name'];
		$goods_list[$goods_id]['shop_price'] = price_format($row['shop_price']);
		$goods_list[$goods_id]['rank_price'] = price_format($row['rank_price']);
		$goods_list[$goods_id]['goods_weight'] = 0 < intval($row['goods_weight']) ? ceil($row['goods_weight']) . $_LANG['kilogram'] : ceil($row['goods_weight'] * 1000) . $_LANG['gram'];
		$goods_list[$goods_id]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$goods_list[$goods_id]['goods_brief'] = $row['goods_brief'];
		$goods_list[$goods_id]['brand_name'] = $row['brand_name'];
		$tmp = $ids;
		$key = array_search($goods_id, $tmp);
		if ($key !== NULL && $key !== false) {
			unset($tmp[$key]);
		}

		$goods_list[$goods_id]['ids'] = !empty($tmp) ? 'goods[]=' . implode('&amp;goods[]=', $tmp) : '';
		$basic_arr[$goods_id]['properties'][$row['attr_id']]['name'] = $row['attr_name'];

		if (!empty($basic_arr[$goods_id]['properties'][$row['attr_id']]['value'])) {
			$basic_arr[$goods_id]['properties'][$row['attr_id']]['value'] .= ',' . $row['attr_value'];
		}
		else {
			$basic_arr[$goods_id]['properties'][$row['attr_id']]['value'] = $row['attr_value'];
		}

		if (!isset($basic_arr[$goods_id]['comment_rank'])) {
			$basic_arr[$goods_id]['comment_rank'] = isset($cmt[$goods_id]) ? ceil($cmt[$goods_id]['cmt_rank']) : 0;
			$basic_arr[$goods_id]['comment_number'] = isset($cmt[$goods_id]) ? $cmt[$goods_id]['cmt_count'] : 0;
			$basic_arr[$goods_id]['comment_number'] = sprintf($_LANG['comment_num'], $basic_arr[$goods_id]['comment_number']);
		}
	}

	$sql = 'SELECT attr_id,attr_name, attr_input_category FROM ' . $ecs->table('attribute') . (' WHERE cat_id=\'' . $type_id . '\' ORDER BY sort_order, attr_id');
	$attribute = array();
	$query = $db->query($sql);

	while ($rt = $db->fetch_array($query)) {
		$attribute[$rt['attr_id']]['attr_id'] = $rt['attr_id'];
		$attribute[$rt['attr_id']]['attr_name'] = $rt['attr_name'];
		$attribute_basic[$rt['attr_id']] = $rt['attr_name'];
	}

	if ($highlight == 1) {
		foreach ($attribute as $key => $val) {
			$basic_gid_arr = array();
			$function_gid_arr = array();
			$hardware_gid_arr = array();
			$basic_gid = array();
			$function_gid = array();
			$hardware_gid = array();

			foreach ($basic_arr as $gid => $v) {
				$basic_gid_arr[] = str_replace(' ', '', $basic_arr[$gid]['properties'][$key]['value']);
				$basic_gid[] = $gid;
			}

			$basic_unique = array_unique($basic_gid_arr);

			if (!(count($basic_unique) == 1)) {
				$attribute[$key]['attr_highlight'] = 1;
			}
		}
	}

	if ($compare == 1) {
		foreach ($attribute as $key => $val) {
			$basic_gid_arr = array();
			$function_gid_arr = array();
			$hardware_gid_arr = array();
			$basic_gid = array();
			$function_gid = array();
			$hardware_gid = array();

			foreach ($basic_arr as $gid => $v) {
				$basic_gid_arr[] = str_replace(' ', '', $basic_arr[$gid]['properties'][$key]['value']);
				$basic_gid[] = $gid;
			}

			$basic_unique = array_unique($basic_gid_arr);

			if (count($basic_unique) == 1) {
				foreach ($basic_gid as $b_val) {
					unset($basic_arr[$b_val]['properties'][$key]);
				}

				unset($attribute[$key]);
			}
		}
	}

	$len = 4 - count($goods_list);
	$goods_count = array();

	for ($c = 1; $c <= $len; $c++) {
		$goods_count[] = $c;
	}

	$smarty->assign('goods_count', $goods_count);
	$smarty->assign('attribute', $attribute);
	$smarty->assign('goods_list', $goods_list);
	$smarty->assign('basic_arr', $basic_arr);
	$smarty->assign('is_compare', $compare);
	$smarty->assign('is_highlight', $highlight);
	$smarty->assign('ids', $param_goods_id);
}
else {
	show_message($_LANG['compare_no_goods']);
	exit();
}

assign_template();
$position = assign_ur_here(0, $_LANG['goods_compare']);
$smarty->assign('page_title', $position['title']);
$smarty->assign('ur_here', $position['ur_here']);
$smarty->assign('best_goods', get_recommend_goods('best'));
$categories_pro = get_category_tree_leve_one();
$smarty->assign('categories_pro', $categories_pro);
$smarty->assign('helps', get_shop_help());
assign_dynamic('compare');
$smarty->display('category_compare.dwt');

?>
