<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function bargain_get_product_info_by_attr($bargain_id = 0, $goods_id = 0, $attr_arr = array(), $goods_model = 0, $region_id = 0)
{
	if (!empty($attr_arr)) {
		$where = '';

		if ($goods_model == 1) {
			$table = 'products_warehouse';
			$where .= ' AND warehouse_id = \'' . $region_id . '\' ';
		}
		else if ($goods_model == 2) {
			$table = 'products_area';
			$where .= ' AND area_id = \'' . $region_id . '\' ';
		}
		else {
			$table = 'products';
		}

		$where_select = array('goods_id' => $goods_id);

		if (empty($goods_id)) {
			$admin_id = get_admin_id();
			$where_select['admin_id'] = $admin_id;
		}

		$attr = array();

		foreach ($attr_arr as $key => $val) {
			$where_select['attr_value'] = $val;
			$goods_attr_id = get_goods_attr_id($where_select, array('ga.goods_attr_id'), 1);

			if ($goods_attr_id) {
				$attr[] = $goods_attr_id;
			}
		}

		$set = '';

		foreach ($attr as $key => $val) {
			$set .= ' AND FIND_IN_SET(\'' . $val . '\', REPLACE(goods_attr, \'|\', \',\')) ';
		}

		$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE 1 ' . $set . ' AND goods_id = \'' . $goods_id . '\' ') . $where . ' LIMIT 1 ';
		$product_info = $GLOBALS['db']->getRow($sql);

		if (0 < $bargain_id) {
			$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('activity_goods_attr') . (' WHERE bargain_id = \'' . $bargain_id . '\'  AND goods_id = \'' . $goods_id . '\' and product_id = \'') . $product_info['product_id'] . '\'  LIMIT 1 ';
			$attr_info = $GLOBALS['db']->getRow($sql);
			$product_info['goods_attr_id'] = $attr_info['id'];
			$product_info['target_price'] = $attr_info['target_price'];
		}

		return $product_info;
	}
	else {
		return false;
	}
}

function bargain_goods_list($ru_id)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		$filter['is_audit'] = empty($_REQUEST['is_audit']) ? '' : trim($_REQUEST['is_audit']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'bg.id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$where = ' g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.review_status>2 and bg.is_delete = 0 ';
		$where .= !empty($filter['keyword']) ? ' AND (g.goods_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\') ' : '';

		if (!empty($filter['is_audit'])) {
			if ($filter['is_audit'] == 3) {
				$where .= ' AND bg.is_audit = 0 ';
			}
			else {
				$where .= ' AND bg.is_audit = \'' . $filter['is_audit'] . '\' ';
			}
		}
		else {
			$where .= '';
		}

		if (0 < $ru_id) {
			$where .= ' and g.user_id = \'' . $ru_id . '\'';
		}

		$filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_where = '';
		$store_search_where = '';

		if (-1 < $filter['store_search']) {
			if ($ru_id == 0) {
				if (0 < $filter['store_search']) {
					if ($_REQUEST['store_type']) {
						$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
					}

					if ($filter['store_search'] == 1) {
						$where .= ' AND g.user_id = \'' . $filter['merchant_id'] . '\' ';
					}
					else if ($filter['store_search'] == 2) {
						$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
					}
					else if ($filter['store_search'] == 3) {
						$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
					}

					if (1 < $filter['store_search']) {
						$where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . (' WHERE msi.user_id = ga.user_id ' . $store_where . ') > 0 ');
					}
				}
				else {
					$where .= ' AND g.user_id = 0';
				}
			}
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('bargain_goods') . ' AS bg left join ' . $GLOBALS['ecs']->table('goods') . ' as g  ON bg.goods_id = g.goods_id ' . (' WHERE ' . $where);
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT bg.*,g.user_id,g.goods_sn,g.goods_name,g.shop_price,g.goods_number,g.goods_img,g.goods_thumb ' . 'FROM ' . $GLOBALS['ecs']->table('bargain_goods') . ' AS bg ' . ' left join ' . $GLOBALS['ecs']->table('goods') . ' as g on bg.goods_id=g.goods_id' . (' WHERE  ' . $where . ' ') . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
		$filter['keyword'] = stripslashes($filter['keyword']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->query($sql);
	$time = gmtime();
	$list = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$arr = array_merge($row);
		$arr['goods_name'] = $arr['goods_name'];
		$arr['user_name'] = get_shop_name($arr['user_id'], 1);
		$arr['shop_price'] = price_format($arr['shop_price']);
		$target_price = get_bargain_target_price($arr['id']);

		if ($target_price) {
			$arr['target_price'] = price_format($target_price);
		}
		else {
			$arr['target_price'] = price_format($arr['target_price']);
		}

		$arr['goods_number'] = $arr['goods_number'];
		$arr['sales_volume'] = $arr['sales_volume'];
		$arr['goods_img'] = get_image_path($arr['goods_img']);
		$arr['goods_thumb'] = get_image_path($arr['goods_thumb']);

		if (0 < $arr['status']) {
			$status = '活动已关闭';
		}
		else if ($arr['end_time'] <= $time) {
			$status = '活动已过期';
		}
		else {
			$status = '活动进行中';
		}

		$arr['is_status'] = $status;
		$arr['status'] = $arr['status'];

		if ($arr['is_audit'] == 1) {
			$is_audit = '审核未通过';
		}
		else if ($arr['is_audit'] == 2) {
			$is_audit = '审核已通过';
		}
		else {
			$is_audit = '未审核';
		}

		$arr['is_audit'] = $is_audit;
		$arr['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $arr['start_time']);
		$arr['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $arr['end_time']);
		$list[] = $arr;
	}

	$arr = array('item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function bargain_goods_info($id)
{
	$sql = 'SELECT bg.*,g.user_id,g.goods_sn,g.goods_name,g.shop_price,g.market_price,g.goods_number,g.goods_img,g.goods_thumb ' . 'FROM ' . $GLOBALS['ecs']->table('bargain_goods') . ' AS bg ' . ' left join ' . $GLOBALS['ecs']->table('goods') . ' as g on bg.goods_id=g.goods_id' . (' WHERE bg.id = \'' . $id . '\' ') . ' LIMIT 1 ';
	$goods = $GLOBALS['db']->getRow($sql);
	$goods['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $goods['start_time']);
	$goods['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $goods['end_time']);
	return $goods;
}

function bargain_log_list($bargain_id)
{
	$result = get_filter();

	if ($result === false) {
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'bs.add_time' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('bargain_statistics_log') . ' as bs LEFT JOIN ' . $GLOBALS['ecs']->table('bargain_goods') . ' bg ON bs.bargain_id = bg.id  where bg.id =\'' . $bargain_id . '\'';
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT bg.goods_id,bg.target_price,bg.start_time,bg.end_time,bs.id,bs.bargain_id, bs.goods_attr_id,bs.user_id,bs.final_price,bs.add_time,bs.count_num,bs.status  FROM ' . $GLOBALS['ecs']->table('bargain_statistics_log') . ' as bs LEFT JOIN  ' . $GLOBALS['ecs']->table('bargain_goods') . ' as bg ON bs.bargain_id = bg.id  WHERE bg.id =\'' . $bargain_id . '\' ' . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
		$filter['keyword'] = stripslashes($filter['keyword']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->query($sql);
	$list = array();
	$time = gmtime();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$arr = array_merge($row);
		$arr['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $arr['add_time']);
		var_dump($arr['goods_attr_id']);

		if ($arr['goods_attr_id']) {
			var_dump('66666');
			$spec = explode(',', $arr['goods_attr_id']);
			$target_price = bargain_target_price($arr['bargain_id'], $arr['goods_id'], $spec);
			$arr['target_price'] = price_format($target_price);
		}
		else {
			$arr['target_price'] = price_format($arr['target_price']);
		}

		$arr['final_price'] = price_format($arr['final_price']);
		$user_nick = bargain_user_default($arr['user_id']);
		$arr['user_name'] = $user_nick['nick_name'];
		$arr['count_num'] = $arr['count_num'];

		if ($arr['status'] == 1) {
			$arr['status'] = '活动成功';
		}
		else {
			if ($arr['status'] != 1 && $arr['start_time'] <= $time && $time <= $arr['end_time']) {
				$arr['status'] = '活动进行中';
			}
			else {
				if ($arr['status'] != 1 && $arr['end_time'] < $time) {
					$arr['status'] = '活动失败';
				}
			}
		}

		$list[] = $arr;
	}

	$arr = array('item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function bargain_statistics_list($id = 0)
{
	$result = get_filter();

	if ($result === false) {
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'desc' : trim($_REQUEST['sort_order']);
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('bargain_statistics') . '  where bs_id =\'' . $id . '\'';
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT *  FROM ' . $GLOBALS['ecs']->table('bargain_statistics') . ' WHERE bs_id =\'' . $id . '\' ' . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->query($sql);
	$list = array();
	$time = gmtime();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$arr = array_merge($row);
		$arr['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $arr['add_time']);
		$arr['subtract_price'] = price_format($arr['subtract_price']);
		$user_nick = bargain_user_default($arr['user_id']);
		$arr['user_name'] = $user_nick['nick_name'];
		$list[] = $arr;
	}

	$arr = array('item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_bargain_target_price($bargain_id = 0)
{
	$sql = 'SELECT min(target_price) as target_price FROM ' . $GLOBALS['ecs']->table('activity_goods_attr') . (' WHERE bargain_id = ' . $bargain_id . ' ');
	$bargain = $GLOBALS['db']->getOne($sql);
	return $bargain;
}

function bargain_target_price($bargain_id = 0, $goods_id = 0, $spec = array(), $warehouse_id = 0, $area_id = 0)
{
	if (!empty($spec)) {
		if (is_array($spec)) {
			foreach ($spec as $key => $val) {
				$spec[$key] = addslashes($val);
			}
		}
		else {
			$spec = addslashes($spec);
		}

		$model_attr = bargain_get_model_attr($goods_id);
		$attr['price'] = 0;

		if ($GLOBALS['_CFG']['goods_attr_price'] == 1) {
			$spec = implode('|', $spec);
			$where = 'goods_id = \'' . $goods_id . '\'';

			if ($model_attr == 1) {
				$table = 'products_warehouse';
				$where .= ' AND warehouse_id = \'' . $warehouse_id . '\' AND goods_attr = \'' . $spec . '\'';
			}
			else if ($model_attr == 2) {
				$table = 'products_area';
				$area_id = $warehouse_area['area_id'];
				$where .= ' AND area_id = \'' . $area_id . '\' AND goods_attr = \'' . $spec . '\'';
			}
			else {
				$table = 'products';
				$where .= ' AND goods_attr = \'' . $spec . '\'';
			}

			$sql = 'SELECT product_id FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE ' . $where);
			$product_id = $GLOBALS['db']->getOne($sql);

			if ($product_id) {
				$sql = 'SELECT target_price FROM ' . $GLOBALS['ecs']->table('activity_goods_attr') . (' WHERE bargain_id = \'' . $bargain_id . '\' and goods_id = \'' . $goods_id . '\' and product_id = \'' . $product_id . '\' ');
				$price = $GLOBALS['db']->getOne($sql);
			}
		}
	}
	else {
		$price = 0;
	}

	return floatval($price);
}

function bargain_get_model_attr($goods_id)
{
	$sql = 'select model_attr from ' . $GLOBALS['ecs']->table('goods') . (' where goods_id=\'' . $goods_id . '\'');
	$model_attr = $GLOBALS['db']->getOne($sql);
	return $model_attr;
}

function bargain_user_default($user_id = 0)
{
	if (is_dir(APP_WECHAT_PATH)) {
		$sql = 'SELECT u.user_name, u.nick_name, u.user_picture, wu.headimgurl, wu.nickname FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('wechat_user') . ' AS wu ON wu.ect_uid = u.user_id ' . (' WHERE u.user_id = \'' . $user_id . '\' ');
	}
	else {
		$sql = 'SELECT user_name, nick_name , user_picture FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $user_id . '\' ');
	}

	$result = $GLOBALS['db']->getRow($sql);
	$user['nick_name'] = !empty($result['nickname']) ? $result['nickname'] : (!empty($result['nick_name']) ? $result['nick_name'] : $result['user_name']);
	$user['user_picture'] = !empty($result['headimgurl']) ? $result['headimgurl'] : $result['user_picture'];
	return $user;
}

function list_link($is_add = true)
{
	$href = 'bargain.php?act=list';

	if (!$is_add) {
		$href .= '&' . list_link_postfix();
	}

	return array('href' => $href, 'text' => '砍价商品列表', 'class' => 'icon-reply');
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/lib_goods.php';
require_once ROOT_PATH . 'includes/lib_order.php';
require_once ROOT_PATH . SELLER_PATH . '/includes/lib_comment.php';
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('action_type', 'team');
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

$smarty->assign('controller', basename(PHP_SELF, '.php'));

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', '砍价商品列表');
	$smarty->assign('action_link', array('href' => 'bargain.php?act=add', 'text' => '添加砍价商品', 'class' => 'icon-plus'));
	$list = bargain_goods_list($adminru['ru_id']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('bargain_goods_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('bargain_goods_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$list = bargain_goods_list($adminru['ru_id']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('bargain_goods_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('bargain_goods_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		$smarty->assign('primary_cat', $_LANG['02_promotion']);
		$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '19_bargain'));
		set_default_filter(0, 0, $adminru['ru_id']);
		$smarty->assign('filter_brand_list', search_brand_list());
		$goods['tc_id'] = 0;

		if ($_REQUEST['act'] == 'add') {
			$goods = array('start_time' => date('Y-m-d H:i:s', time()), 'end_time' => date('Y-m-d H:i:s', time() + 4 * 86400), 'min_price' => 0, 'max_price' => 10);
		}
		else {
			$id = intval($_REQUEST['id']);

			if ($id <= 0) {
				exit('invalid param');
			}

			$goods = bargain_goods_info($id);
		}

		$smarty->assign('goods', $goods);
		$select_category_html = '';
		$select_category_html .= insert_select_category(0, 0, 0, 'category', 1);
		$smarty->assign('select_category_html', $select_category_html);

		if ($_REQUEST['act'] == 'edit') {
			$smarty->assign('ur_here', '修改砍价商品');
		}
		else {
			$smarty->assign('ur_here', '添加砍价商品');
		}

		$smarty->assign('action_link', list_link($_REQUEST['act'] == 'add'));
		$smarty->assign('brand_list', get_brand_list());
		$smarty->assign('ru_id', $adminru['ru_id']);
		assign_query_info();
		$smarty->display('bargain_goods_info.dwt');
	}
	else if ($_REQUEST['act'] == 'insert_update') {
		$id = $_REQUEST['id'];
		$goods = $_REQUEST['data'];
		$goods['start_time'] = local_strtotime($goods['start_time']);
		$goods['end_time'] = local_strtotime($goods['end_time']);
		$goods['goods_id'] = intval($_POST['goods_id']);
		$target_price = $_REQUEST['target_price'];
		$product_id = $_REQUEST['product_id'];
		$activity_goods_attr = $_REQUEST['bargain_id'];

		if ($goods['goods_id'] <= 0) {
			sys_msg('请添加砍价商品', 0, $links);
		}

		if ($goods['end_time'] <= $goods['start_time']) {
			sys_msg('开始时间不能大于结束时间', 0, $links);
		}

		$adminru = get_admin_ru_id();
		clear_cache_files();

		if (0 < $id) {
			$db->autoExecute($ecs->table('bargain_goods'), $goods, 'UPDATE', 'id = \'' . $id . '\'');

			if ($product_id) {
				foreach ($product_id as $key => $value) {
					$attr_data['target_price'] = $target_price[$key];
					$db->autoExecute($ecs->table('activity_goods_attr'), $attr_data, 'UPDATE', 'id = \'' . $activity_goods_attr[$key] . '\'');
				}
			}

			$links = array(
				array('href' => 'bargain.php?act=list&' . list_link_postfix(), 'text' => '返回砍价列表')
				);
			sys_msg('修改成功', 0, $links);
		}
		else {
			if ($goods['end_time'] <= $goods['start_time']) {
				sys_msg('开始时间不能大于结束时间', 0, $links);
			}

			$sql = 'SELECT count(goods_id) as num  FROM ' . $GLOBALS['ecs']->table('bargain_goods') . ' WHERE goods_id = \'' . $goods['goods_id'] . '\' and status = \'0\' ';
			$res = $GLOBALS['db']->getRow($sql);

			if (1 <= $res['num']) {
				$links = array(
					array('href' => 'bargain.php?act=add', 'text' => '继续添加砍价商品')
					);
				sys_msg('该砍价商品活动结束之前，不可添加新的活动', 0, $links);
			}

			$db->autoExecute($ecs->table('bargain_goods'), $goods, 'INSERT');
			$bargain_id = $db->insert_id();

			if ($bargain_id) {
				if ($product_id) {
					foreach ($product_id as $key => $value) {
						$attr_data['bargain_id'] = $bargain_id;
						$attr_data['goods_id'] = $goods['goods_id'];
						$attr_data['product_id'] = $value;
						$attr_data['target_price'] = $target_price[$key];
						$attr_data['type'] = 'bargain';
						$db->autoExecute($ecs->table('activity_goods_attr'), $attr_data, 'INSERT');
					}
				}
			}

			$links = array(
				array('href' => 'bargain.php?act=add', 'text' => '继续添加砍价商品'),
				array('href' => 'bargain.php?act=list', 'text' => '返回砍价砍价列表')
				);
			sys_msg('砍价商品添加成功', 0, $links);
		}
	}
	else if ($_REQUEST['act'] == 'remove') {
		check_authz_json('bargain_manage');
		$id = intval($_GET['id']);
		$sql = 'UPDATE ' . $ecs->table('bargain_goods') . ' SET is_delete = 1 ' . (' WHERE id =\'' . $id . '\' LIMIT 1 ');
		$db->query($sql);
		clear_cache_files();
		$url = 'bargain.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
		exit();
	}
	else if ($_REQUEST['act'] == 'remove_down') {
		check_authz_json('team_manage');
		$id = intval($_GET['id']);
		$sql = 'UPDATE ' . $ecs->table('bargain_goods') . ' SET status = 1 ' . (' WHERE id =\'' . $id . '\' LIMIT 1 ');
		$db->query($sql);
		clear_cache_files();
		$url = 'bargain.php?act=query&' . str_replace('act=remove_down', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
		exit();
	}
}

if ($_REQUEST['act'] == 'bargain_log') {
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', '活动详情');
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '19_bargain'));
	$tab_menu = array();
	$tab_menu[] = array('curr' => 0, 'text' => '砍价商品列表', 'href' => 'bargain.php?act=list');
	$tab_menu[] = array('curr' => 1, 'text' => '活动详情', 'href' => 'bargain.php?act=bargain_log');
	$smarty->assign('tab_menu', $tab_menu);
	$bargain_id = $_REQUEST['id'];
	$list = bargain_log_list($bargain_id);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('bargain_log_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('bargain_log_list.dwt');
}
else if ($_REQUEST['act'] == 'bargain_log_query') {
	$list = bargain_log_list($adminru['ru_id']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('bargain_log_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('bargain_log_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

if ($_REQUEST['act'] == 'bargain_statistics_list') {
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', '亲友帮');
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '19_bargain'));
	$tab_menu = array();
	$tab_menu[] = array('curr' => 0, 'text' => '砍价商品列表', 'href' => 'bargain.php?act=list');
	$tab_menu[] = array('curr' => 1, 'text' => '亲友帮', 'href' => 'bargain.php?act=bargain_statistics_list');
	$smarty->assign('tab_menu', $tab_menu);
	$id = $_REQUEST['id'];
	$list = bargain_statistics_list($id);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('bargain_statistics_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('bargain_statistics_list.dwt');
}
else if ($_REQUEST['act'] == 'bargain_query') {
	$list = bargain_statistics_list($adminru['ru_id']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('bargain_statistics_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('bargain_statistics_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else if ($_REQUEST['act'] == 'group_goods') {
	check_authz_json('team_manage');
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$filter = $json->decode($_GET['JSON']);
	$arr = get_goods_info($filter->goods_id);
	make_json_result($arr);
}
else if ($_REQUEST['act'] == 'search_goods') {
	check_authz_json('bargain_manage');
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$filter = $json->decode($_GET['JSON']);
	$arr = get_goods_list($filter);
	make_json_result($arr);
}
else if ($_REQUEST['act'] == 'goods_info') {
	check_authz_json('bargain_manage');
	$ru_id = $adminru['ru_id'];
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
	$where = '  is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 AND review_status>2 and goods_id = ' . $goods_id . ' ';
	$sql = 'SELECT shop_price,goods_type,model_attr FROM ' . $GLOBALS['ecs']->table('goods') . ('  where ' . $where . ' ');
	$row = $GLOBALS['db']->getRow($sql);
	$goods_type = $row['goods_type'];
	$goods_model = $row['model_attr'];
	$sql = ' SELECT a.attr_id, a.attr_name, a.attr_input_type, a.attr_type, a.attr_values ' . ' FROM ' . $GLOBALS['ecs']->table('attribute') . ' AS a ' . ' WHERE a.cat_id = ' . intval($goods_type) . ' AND a.cat_id <> 0 ' . ' ORDER BY a.sort_order, a.attr_type, a.attr_id ';
	$attribute_list = $GLOBALS['db']->getAll($sql);
	$attr_where = '';
	$sql = ' SELECT v.attr_id, v.attr_value, v.attr_price, v.attr_sort, v.attr_checked ' . ' FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS v ' . (' WHERE goods_id = \'' . $goods_id . '\' ORDER BY v.attr_sort, v.goods_attr_id');
	$attr_list = $GLOBALS['db']->getAll($sql);

	foreach ($attribute_list as $key => $val) {
		$is_selected = 0;
		$this_value = '';

		if (0 < $val['attr_type']) {
			if ($val['attr_values']) {
				$attr_values = preg_replace("/\r\n/", ',', $val['attr_values']);
				$attr_values = explode(',', $attr_values);
			}
			else {
				$sql = 'SELECT attr_value FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE goods_id = \'' . $goods_id . '\' AND attr_id = \'') . $val['attr_id'] . '\' ORDER BY attr_sort, goods_attr_id';
				$attr_values = $GLOBALS['db']->getAll($sql);
				$attribute_list[$key]['attr_values'] = get_attr_values_arr($attr_values);
				$attr_values = $attribute_list[$key]['attr_values'];
			}

			$attr_values_arr = array();

			for ($i = 0; $i < count($attr_values); $i++) {
				$goods_attr = $GLOBALS['db']->getRow('SELECT goods_attr_id, attr_price, attr_sort FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE goods_id = \'' . $goods_id . '\' AND attr_value = \'') . $attr_values[$i] . '\' AND attr_id = \'' . $val['attr_id'] . '\' LIMIT 1');
				$attr_values_arr[$i] = array('is_selected' => 0, 'goods_attr_id' => $goods_attr['goods_attr_id'], 'attr_value' => $attr_values[$i], 'attr_price' => $goods_attr['attr_price'], 'attr_sort' => $goods_attr['attr_sort']);
			}

			$attribute_list[$key]['attr_values_arr'] = $attr_values_arr;
		}

		foreach ($attr_list as $k => $v) {
			if ($val['attr_id'] == $v['attr_id']) {
				$is_selected = 1;

				if ($val['attr_type'] == 0) {
					$this_value = $v['attr_value'];
				}
				else {
					foreach ($attribute_list[$key]['attr_values_arr'] as $a => $b) {
						if ($goods_id) {
							if ($b['attr_value'] == $v['attr_value']) {
								$attribute_list[$key]['attr_values_arr'][$a]['is_selected'] = 1;
							}
						}
						else if ($b['attr_value'] == $v['attr_value']) {
							$attribute_list[$key]['attr_values_arr'][$a]['is_selected'] = 1;
							break;
						}
					}
				}
			}
		}

		$attribute_list[$key]['is_selected'] = $is_selected;
		$attribute_list[$key]['this_value'] = $this_value;

		if ($val['attr_input_type'] == 1) {
			$attribute_list[$key]['attr_values'] = preg_split('/\\r\\n/', $val['attr_values']);
		}
	}

	$attribute_list = get_new_goods_attr($attribute_list);
	$GLOBALS['smarty']->assign('goods_id', $goods_id);
	$GLOBALS['smarty']->assign('goods_model', $goods_model);
	$GLOBALS['smarty']->assign('attribute_list', $attribute_list);
	$goods_attribute = $GLOBALS['smarty']->fetch('library/bargain_goods_attribute.lbi');
	$goods_attr_gallery = '';
	$attr_spec = $attribute_list['spec'];

	if ($attr_spec) {
		$arr['is_spec'] = 1;
	}
	else {
		$arr['is_spec'] = 0;
	}

	$result['goods_attribute'] = $goods_attribute;
	$result['goods_id'] = $goods_id;
	$result['shop_price'] = $row['shop_price'];
	exit(json_encode($result));
}
else {
	if ($_REQUEST['act'] == 'set_attribute_table' || $_REQUEST['act'] == 'goods_attribute_query') {
		check_authz_json('goods_manage');
		$goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
		$bargain_id = empty($_REQUEST['bargain_id']) ? 0 : intval($_REQUEST['bargain_id']);
		$goods_type = empty($_REQUEST['goods_type']) ? 0 : intval($_REQUEST['goods_type']);
		$attr_id_arr = empty($_REQUEST['attr_id']) ? array() : explode(',', $_REQUEST['attr_id']);
		$attr_value_arr = empty($_REQUEST['attr_value']) ? array() : explode(',', $_REQUEST['attr_value']);
		$goods_model = empty($_REQUEST['goods_model']) ? 0 : intval($_REQUEST['goods_model']);
		$region_id = empty($_REQUEST['region_id']) ? 0 : intval($_REQUEST['region_id']);
		$result = array('error' => 0, 'message' => '', 'content' => '');
		$group_attr = array('goods_id' => $goods_id, 'goods_type' => $goods_type, 'attr_id' => empty($attr_id_arr) ? '' : implode(',', $attr_id_arr), 'attr_value' => empty($attr_value_arr) ? '' : implode(',', $attr_value_arr), 'goods_model' => $goods_model, 'region_id' => $region_id);
		$result['group_attr'] = json_encode($group_attr);

		if ($goods_model == 0) {
			$model_name = '';
		}
		else if ($goods_model == 1) {
			$model_name = '仓库';
		}
		else if ($goods_model == 2) {
			$model_name = '地区';
		}

		$region_name = $GLOBALS['db']->getOne(' SELECT region_name FROM ' . $GLOBALS['ecs']->table('region_warehouse') . (' WHERE region_id =\'' . $region_id . '\' '));
		$smarty->assign('region_name', $region_name);
		$smarty->assign('goods_model', $goods_model);
		$smarty->assign('model_name', $model_name);
		$goods_info = $GLOBALS['db']->getRow(' SELECT market_price, shop_price, model_attr FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\' '));
		$smarty->assign('goods_info', $goods_info);

		foreach ($attr_id_arr as $key => $val) {
			$attr_arr[$val][] = $attr_value_arr[$key];
		}

		$attr_spec = array();
		$attribute_array = array();

		if (0 < count($attr_arr)) {
			$i = 0;

			foreach ($attr_arr as $key => $val) {
				$sql = 'SELECT attr_name, attr_type FROM ' . $GLOBALS['ecs']->table('attribute') . (' WHERE attr_id =\'' . $key . '\' LIMIT 1');
				$attr_info = $GLOBALS['db']->getRow($sql);
				$attribute_array[$i]['attr_id'] = $key;
				$attribute_array[$i]['attr_name'] = $attr_info['attr_name'];
				$attribute_array[$i]['attr_value'] = $val;
				$attr_values_arr = array();

				foreach ($val as $k => $v) {
					$data = get_goods_attr_id(array('attr_id' => $key, 'attr_value' => $v, 'goods_id' => $goods_id), array('ga.*, a.attr_type'), array(1, 2), 1);

					if (!$data) {
						$sql = 'SELECT MAX(goods_attr_id) AS goods_attr_id FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE 1 ';
						$max_goods_attr_id = $GLOBALS['db']->getOne($sql);
						$attr_sort = $max_goods_attr_id + 1;
						$sql = ' INSERT INTO ' . $GLOBALS['ecs']->table('goods_attr') . ' (goods_id, attr_id, attr_value, attr_sort, admin_id) ' . ' VALUES ' . (' (\'' . $goods_id . '\', \'' . $key . '\', \'' . $v . '\', \'' . $attr_sort . '\', \'') . $_SESSION['seller_id'] . '\') ';
						$GLOBALS['db']->query($sql);
						$data['goods_attr_id'] = $GLOBALS['db']->insert_id();
						$data['attr_type'] = $attr_info['attr_type'];
						$data['attr_sort'] = $attr_sort;
					}

					$data['attr_id'] = $key;
					$data['attr_value'] = $v;
					$data['is_selected'] = 1;
					$attr_values_arr[] = $data;
				}

				$attr_spec[$i] = $attribute_array[$i];
				$attr_spec[$i]['attr_values_arr'] = $attr_values_arr;
				$attribute_array[$i]['attr_values_arr'] = $attr_values_arr;

				if ($attr_info['attr_type'] == 2) {
					unset($attribute_array[$i]);
				}

				$i++;
			}

			$new_attribute_array = array();

			foreach ($attribute_array as $key => $val) {
				$new_attribute_array[] = $val;
			}

			$attribute_array = $new_attribute_array;
			$attr_arr = get_goods_unset_attr($goods_id, $attr_arr);

			if (count($attr_arr) == 1) {
				foreach (reset($attr_arr) as $key => $val) {
					$attr_group[][] = $val;
				}
			}
			else {
				$attr_group = attr_group($attr_arr);
			}

			foreach ($attr_group as $key => $val) {
				$group = array();
				$product_info = bargain_get_product_info_by_attr($bargain_id, $goods_id, $val, $goods_model, $region_id);

				if (!empty($product_info)) {
					$group = $product_info;
				}

				foreach ($val as $k => $v) {
					$group['attr_info'][$k]['attr_id'] = $attribute_array[$k]['attr_id'];
					$group['attr_info'][$k]['attr_value'] = $v;
				}

				$attr_group[$key] = $group;
			}

			$smarty->assign('attr_group', $attr_group);
			$smarty->assign('attribute_array', $attribute_array);
		}

		$smarty->assign('group_attr', $result['group_attr']);
		$smarty->assign('goods_attr_price', $GLOBALS['_CFG']['goods_attr_price']);
		$GLOBALS['smarty']->assign('goods_id', $goods_id);
		$GLOBALS['smarty']->assign('goods_type', $goods_type);
		$result['content'] = $smarty->fetch('library/bargain_attribute_table.lbi');
		exit(json_encode($result));
	}
}

?>
