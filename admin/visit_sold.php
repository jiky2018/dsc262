<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function click_sold_info($ru_id, $cat_id, $brand_id, $show_num)
{
	global $db;
	global $ecs;
	$where = ' WHERE o.order_id = og.order_id AND g.goods_id = og.goods_id ' . $ruCat . order_query_sql('finished', 'o.');
	$limit = ' LIMIT ' . $show_num;

	if (0 < $ru_id) {
		$where .= ' and g.user_id = \'' . $ru_id . '\'';
	}

	if (0 < $cat_id) {
		$where .= ' AND ' . get_children($cat_id);
	}

	if (0 < $brand_id) {
		$where .= ' AND g.brand_id = \'' . $brand_id . '\' ';
	}

	$where .= $ruCat;
	$arr = array();
	$sql = 'SELECT og.goods_id, g.goods_sn, g.goods_name, g.click_count,  COUNT(og.goods_id) AS sold_times, og.ru_id ' . ' FROM ' . $ecs->table('goods') . ' AS g, ' . $ecs->table('order_goods') . ' AS og, ' . $ecs->table('order_info') . ' AS o ' . $where . ' GROUP BY og.goods_id ORDER BY g.click_count DESC ' . $limit;
	$res = $db->query($sql);
	$click_sold_info = $GLOBALS['db']->getAll($sql);

	foreach ($click_sold_info as $key => $item) {
		$key = $key + 1;
		$arr[$key] = $item;

		if ($item['click_count'] <= 0) {
			$arr[$key]['scale'] = 0;
		}
		else {
			$arr[$key]['scale'] = sprintf('%0.2f', ($item['sold_times'] / $item['click_count']) * 100) . '%';
		}

		$arr[$key]['ru_name'] = get_shop_name($item['ru_id'], 1);
	}

	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/lib_order.php';
require_once '../languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/statistic.php';
$smarty->assign('lang', $_LANG);
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

admin_priv('client_flow_stats');
if (($_REQUEST['act'] == 'list') || ($_REQUEST['act'] == 'download')) {
	$cat_id = (!empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0);
	$brand_id = (!empty($_REQUEST['brand_id']) ? intval($_REQUEST['brand_id']) : 0);
	$show_num = (!empty($_REQUEST['show_num']) ? intval($_REQUEST['show_num']) : 15);
	$click_sold_info = click_sold_info($adminru['ru_id'], $cat_id, $brand_id, $show_num);

	if ($_REQUEST['act'] == 'download') {
		$filename = 'visit_sold';
		header('Content-type: application/vnd.ms-excel; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $filename . '.xls');
		$data = $_LANG['visit_buy'] . "\t\n";
		$data .= $_LANG['order_by'] . '	' . $_LANG['goods_name'] . '	' . $_LANG['goods_steps_name'] . '	' . $_LANG['fav_exponential'] . '	' . $_LANG['buy_times'] . '	' . $_LANG['visit_buy'] . "\n";

		foreach ($click_sold_info as $k => $row) {
			$data .= $k . '	' . $row['goods_name'] . '	' . $row['ru_name'] . '	' . $row['click_count'] . '	' . $row['sold_times'] . '	' . $row['scale'] . "\n";
		}

		echo ecs_iconv(EC_CHARSET, 'GB2312', $data);
		exit();
	}

	$smarty->assign('ur_here', $_LANG['visit_buy_per']);
	$smarty->assign('show_num', $show_num);

	if (0 < $brand_id) {
		$sql = 'SELECT brand_name FROM' . $ecs->table('brand') . ' WHERE brand_id = \'' . $brand_id . '\'';
		$brand_name = $db->getOne($sql);
		$smarty->assign('brand_name', $brand_name);
	}

	$smarty->assign('brand_id', $brand_id);
	$smarty->assign('click_sold_info', $click_sold_info);
	$smarty->assign('filter_category_list', get_category_list($cat_id));
	$smarty->assign('filter_brand_list', search_brand_list());

	if (0 < $cat_id) {
		$parent_cat_list = get_select_category($cat_id, 1, true);
		$filter_category_navigation = get_array_category_info($parent_cat_list);
		$smarty->assign('filter_category_navigation', $filter_category_navigation);

		if (!empty($filter_category_navigation)) {
			$cat_val = '';

			foreach ($filter_category_navigation as $k => $v) {
				$cat_val .= $v['cat_name'] . '>';
			}
		}

		if ($cat_val) {
			$cat_val = substr($cat_val, 0, -1);
			$smarty->assign('cat_val', $cat_val);
		}
	}

	$filename = 'visit_sold';
	$smarty->assign('action_link', array('text' => $_LANG['download_visit_buy'], 'href' => 'visit_sold.php?act=download&show_num=' . $show_num . '&cat_id=' . $cat_id . '&brand_id=' . $brand_id . '&show_num=' . $show_num));
	assign_query_info();
	$smarty->display('visit_sold.dwt');
}

?>
