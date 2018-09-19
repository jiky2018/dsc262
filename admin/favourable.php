<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function favourable_list($ru_id, $rs_id = 0)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['is_going'] = empty($_REQUEST['is_going']) ? 0 : 1;
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'fa.act_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['use_type'] = empty($_REQUEST['use_type']) ? 0 : intval($_REQUEST['use_type']);
		$filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;
		$filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);

		if ($_CFG['region_store_enabled']) {
			$filter['rs_id'] = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);
			$adminru = get_admin_ru_id();

			if (0 < $adminru['rs_id']) {
				$filter['rs_id'] = $adminru['rs_id'];
			}
		}

		$where = '';
		$leftjoin = '';
		$select = '';

		if ($filter['use_type'] == 1) {
			$where .= ' AND fa.user_id = 0 AND fa.userFav_type = 0';
		}
		else if ($filter['use_type'] == 2) {
			$where .= ' AND fa.user_id > 0 AND fa.userFav_type = 0';
		}
		else if ($filter['use_type'] == 3) {
			$where .= ' AND fa.userFav_type = 1';
		}
		else if ($filter['use_type'] == 4) {
			$where .= ' AND fa.user_id = \'' . $ru_id . '\' AND fa.userFav_type = 0';
		}
		else if (0 < $ru_id) {
			$where .= ' AND (fa.user_id = \'' . $ru_id . '\' OR fa.userFav_type = 1)';
		}

		if ($rs_id) {
			$rs_mer = get_rs_mer($rs_id);

			if ($rs_mer) {
				$where .= ' AND (fa.rs_id = \'' . $rs_id . '\' OR fa.user_id ' . db_create_in($rs_mer) . ' ) ';
			}
			else {
				$where .= ' AND fa.rs_id = \'' . $rs_id . '\' ';
			}
		}

		if ($filter['review_status']) {
			$where .= ' AND fa.review_status = \'' . $filter['review_status'] . '\' ';
		}

		$filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_where = '';
		$store_search_where = '';

		if ($filter['store_search'] != 0) {
			if ($ru_id == 0) {
				if ($_REQUEST['store_type']) {
					$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
				}

				if ($filter['store_search'] == 1) {
					$where .= ' AND fa.user_id = \'' . $filter['merchant_id'] . '\' ';
				}
				else if ($filter['store_search'] == 2) {
					$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
				}
				else if ($filter['store_search'] == 3) {
					$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
				}

				if (1 < $filter['store_search']) {
					$where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . (' WHERE msi.user_id = fa.user_id ' . $store_where . ') > 0 ');
				}
			}
		}

		$where .= !empty($filter['seller_list']) ? ' AND fa.user_id > 0 ' : ' AND fa.user_id = 0 ';

		if (!empty($filter['keyword'])) {
			$where .= ' AND fa.act_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\'';
		}

		if ($filter['is_going']) {
			$now = gmtime();
			$where .= ' AND fa.start_time <= \'' . $now . '\' AND fa.end_time >= \'' . $now . '\' ';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('favourable_activity') . ' AS fa ' . (' WHERE 1 ' . $where);
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT fa.* ' . 'FROM ' . $GLOBALS['ecs']->table('favourable_activity') . ' AS fa ' . (' WHERE 1 ' . $where . ' ') . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
		$filter['keyword'] = stripslashes($filter['keyword']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->query($sql);
	$list = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$row['start_time'] = local_date('Y-m-d H:i:s', $row['start_time']);
		$row['end_time'] = local_date('Y-m-d H:i:s', $row['end_time']);
		$row['user_name'] = get_shop_name($row['user_id'], 1);

		if ($row['rs_id']) {
			$row['rs_name'] = get_rs_name($row['rs_id']);
		}

		$list[] = $row;
	}

	return array('item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_act_range_ext($act_range, $act_id)
{
	if (0 < $act_range) {
		$a_range = ' AND act_range = \'' . $act_range . '\' ';
	}

	$now = gmtime();
	$user_id = $GLOBALS['db']->getOne('SELECT ru_id FROM ' . $GLOBALS['ecs']->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['admin_id'] . '\' '));
	$sql = 'SELECT act_range_ext ' . 'FROM ' . $GLOBALS['ecs']->table('favourable_activity') . (' WHERE start_time <= \'' . $now . '\' AND end_time >= \'' . $now . '\' ') . $a_range . (' AND act_id <> \'' . $act_id . '\' AND user_id = \'' . $user_id . '\' ');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr = array_merge($arr, explode(',', $row['act_range_ext']));
	}

	return array_unique($arr);
}

function get_range_goods($act_range, $act_range_ext_list, $create_in, $user_id = 0)
{
	if (empty($act_range_ext_list)) {
		return array();
	}

	switch ($act_range) {
	case FAR_CATEGORY:
		$id_list = array();

		foreach ($act_range_ext_list as $id) {
			$cat_keys = get_array_keys_cat(intval($id));
			$id_list = array_merge($id_list, $cat_keys);
		}

		break;

	case FAR_BRAND:
		$id_list = $act_range_ext_list;
		break;

	case FAR_GOODS:
		$id_list = $act_range_ext_list;
		break;

	default:
		break;
	}

	$sql = 'SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE user_id = \'' . $user_id . '\' AND ') . db_create_in($id_list, $create_in);
	$res = $GLOBALS['db']->query($sql);
	$arr_goods_id = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$arr_goods_id[] = $row['goods_id'];
	}

	return $arr_goods_id;
}

function get_rs_list()
{
	$pin = new pin();
	$sql = ' SELECT rs.rs_name, rs.rs_id FROM ' . $GLOBALS['ecs']->table('region_store') . ' AS rs ';
	$res = $GLOBALS['db']->getAll($sql);

	if ($res) {
		foreach ($res as $k => $val) {
			$res[$k]['rs_name'] = $val['rs_name'];
			$res[$k]['letter'] = strtoupper(substr($pin->Pinyin($val['rs_name'], EC_CHARSET), 0, 1));
		}
	}

	return $res;
}

function getRsList($where = '', $search = '', $leftjoin = '')
{
	$sql = ' SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('region_store') . ' AS rs ' . $leftjoin . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$where .= ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
	$sql = ' SELECT rs.rs_name, rs.rs_id FROM ' . $GLOBALS['ecs']->table('region_store') . ' AS rs ' . $leftjoin . $where;
	$rs_list = $GLOBALS['db']->getAll($sql);
	$filter['page_arr'] = seller_page($filter, $filter['page']);
	return array('list' => $rs_list, 'filter' => $filter);
}

function get_merchants_list($rs_id = 0)
{
	$sql = ' SELECT m.shop_id, m.user_id FROM ' . $GLOBALS['ecs']->table('region_store') . ' rs LEFT JOIN ' . $GLOBALS['ecs']->table('rs_region') . ' AS rr ON rs.rs_id = rr.rs_id LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . (' AS m ON rr.region_id = m.region_id WHERE rs.rs_id = \'' . $rs_id . '\' ');
	$res = $GLOBALS['db']->getAll($sql);

	if ($res) {
		foreach ($res as $k => $val) {
			$res[$k]['shop_name'] = get_shop_name($val['user_id'], 1);
		}
	}

	return $res;
}

function getMerList($where = '', $search = '', $leftjoin = '')
{
	$sql = ' SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('region_store') . ' rs LEFT JOIN ' . $GLOBALS['ecs']->table('rs_region') . ' AS rr ON rs.rs_id = rr.rs_id LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS m ON rr.region_id = m.region_id ' . $leftjoin . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$where .= ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
	$sql = ' SELECT m.shop_id, m.user_id FROM ' . $GLOBALS['ecs']->table('region_store') . ' rs LEFT JOIN ' . $GLOBALS['ecs']->table('rs_region') . ' AS rr ON rs.rs_id = rr.rs_id LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS m ON rr.region_id = m.region_id ' . $leftjoin . $where;
	$mer_list = $GLOBALS['db']->getAll($sql);
	$filter['page_arr'] = seller_page($filter, $filter['page']);
	return array('list' => $mer_list, 'filter' => $filter);
}

function get_rs_name($rs_id)
{
	$sql = ' SELECT rs_name FROM ' . $GLOBALS['ecs']->table('region_store') . (' WHERE rs_id = \'' . $rs_id . '\' ');
	return $GLOBALS['db']->getOne($sql);
}

function get_rs_mer($rs_id = 0)
{
	$sql = ' SELECT m.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS m LEFT JOIN ' . $GLOBALS['ecs']->table('rs_region') . ' AS rr ON rr.region_id = m.region_id LEFT JOIN ' . $GLOBALS['ecs']->table('region_store') . (' AS rs ON rs.rs_id = rr.rs_id WHERE rs.rs_id = \'' . $rs_id . '\' ');
	return $GLOBALS['db']->getCol($sql);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . 'includes/lib_goods.php';
include_once ROOT_PATH . '/includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table('favourable_activity'), $db, 'act_id', 'act_name');
$adminru = get_admin_ru_id();
$admin_rs_id = $adminru['rs_id'];

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

if ($adminru['rs_id']) {
	$smarty->assign('is_rs', true);
}
else {
	$smarty->assign('is_rs', false);
}

if ($_REQUEST['act'] == 'list') {
	admin_priv('favourable');
	$smarty->assign('ur_here', $_LANG['favourable_list']);
	$smarty->assign('action_link', array('href' => 'favourable.php?act=add', 'text' => $_LANG['add_favourable']));
	$list = favourable_list($adminru['ru_id'], $admin_rs_id);
	$smarty->assign('favourable_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$smarty->assign('full_page', 1);
	$smarty->assign('region_store_list', get_region_store_list());
	self_seller(BASENAME($_SERVER['PHP_SELF']));
	assign_query_info();
	$smarty->display('favourable_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$list = favourable_list($adminru['ru_id'], $admin_rs_id);
	$smarty->assign('favourable_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('favourable_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('favourable');
	$id = intval($_GET['id']);
	$favourable = favourable_info($id, 'seller');

	if (empty($favourable)) {
		make_json_error($_LANG['favourable_not_exist']);
	}

	$name = $favourable['act_name'];
	get_del_batch('', $id, array('activity_thumb'), 'act_id', 'favourable_activity', 1);
	$exc->drop($id);
	admin_log($name, 'remove', 'favourable');
	clear_cache_files();
	$url = 'favourable.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'batch') {
	check_authz_json('favourable');
	if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
		sys_msg('没有选择任何数据', 1);
	}

	$ids = !empty($_POST['checkboxes']) ? $_POST['checkboxes'] : 0;

	if (isset($_POST['type'])) {
		if ($_POST['type'] == 'batch_remove') {
			get_del_batch($ids, '', array('activity_thumb'), 'act_id', 'favourable_activity', 1);
			$sql = 'DELETE FROM ' . $ecs->table('favourable_activity') . ' WHERE act_id ' . db_create_in($ids);
			$db->query($sql);
			admin_log('', 'batch_remove', 'favourable');
			clear_cache_files();
			$links[] = array('text' => $_LANG['back_favourable_list'], 'href' => 'favourable.php?act=list&' . list_link_postfix());
			sys_msg($_LANG['batch_drop_ok'], 0, $links);
		}
		else if ($_POST['type'] == 'review_to') {
			$review_status = $_POST['review_status'];
			$review_content = !empty($_POST['review_content']) ? trim($_POST['review_content']) : '';
			$sql = 'UPDATE ' . $ecs->table('favourable_activity') . (' SET review_status = \'' . $review_status . '\' ') . ' WHERE act_id ' . db_create_in($ids);

			if ($db->query($sql)) {
				$lnk[] = array('text' => $_LANG['back_favourable_list'], 'href' => 'favourable.php?act=list&seller_list=1&' . list_link_postfix());
				sys_msg('优惠活动审核状态设置成功', 0, $lnk);
			}
		}
	}
}
else if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('favourable');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	$sql = 'UPDATE ' . $ecs->table('favourable_activity') . (' SET sort_order = \'' . $val . '\'') . (' WHERE act_id = \'' . $id . '\' LIMIT 1');
	$db->query($sql);
	make_json_result($val);
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		admin_priv('favourable');
		$is_add = $_REQUEST['act'] == 'add';
		$smarty->assign('form_action', $is_add ? 'insert' : 'update');

		if ($is_add) {
			$ru_id = $adminru['ru_id'];
			$favourable = array(
				'act_id'        => 0,
				'act_name'      => '',
				'start_time'    => date('Y-m-d H:i:s', time() + 86400),
				'end_time'      => date('Y-m-d H:i:s', time() + 4 * 86400),
				'user_rank'     => '',
				'act_range'     => FAR_ALL,
				'act_range_ext' => '',
				'min_amount'    => 0,
				'max_amount'    => 0,
				'act_type'      => FAT_GOODS,
				'act_type_ext'  => 0,
				'user_id'       => $ru_id,
				'gift'          => array()
				);
		}
		else {
			if (empty($_GET['id'])) {
				sys_msg('invalid param');
			}

			$id = intval($_GET['id']);
			$favourable = favourable_info($id, 'seller');

			if (empty($favourable)) {
				sys_msg($_LANG['favourable_not_exist']);
			}
			else {
				if ($favourable['rs_id'] && $admin_rs_id) {
					$favourable['can_not_audit'] = 1;
				}
			}

			$ru_id = $favourable['user_id'];
		}

		$smarty->assign('favourable', $favourable);
		$user_rank_list = array();
		$user_rank_list[] = array('rank_id' => 0, 'rank_name' => $_LANG['not_user'], 'checked' => strpos(',' . $favourable['user_rank'] . ',', ',0,') !== false);
		$sql = 'SELECT rank_id, rank_name FROM ' . $ecs->table('user_rank');
		$res = $db->query($sql);

		while ($row = $db->fetchRow($res)) {
			$row['checked'] = strpos(',' . $favourable['user_rank'] . ',', ',' . $row['rank_id'] . ',') !== false;
			$user_rank_list[] = $row;
		}

		$smarty->assign('user_rank_list', $user_rank_list);
		$act_range_ext = array();
		if ($favourable['act_range'] != FAR_ALL && !empty($favourable['act_range_ext'])) {
			if ($favourable['act_range'] == FAR_CATEGORY) {
				$sql = 'SELECT cat_id AS id, cat_name AS name FROM ' . $ecs->table('category') . ' WHERE cat_id ' . db_create_in($favourable['act_range_ext']);
			}
			else if ($favourable['act_range'] == FAR_BRAND) {
				$sql = 'SELECT brand_id AS id, brand_name AS name FROM ' . $ecs->table('brand') . ' WHERE brand_id ' . db_create_in($favourable['act_range_ext']);
			}
			else {
				$sql = 'SELECT goods_id AS id, goods_name AS name FROM ' . $ecs->table('goods') . ' WHERE goods_id ' . db_create_in($favourable['act_range_ext']);
			}

			$act_range_ext = $db->getAll($sql);
		}

		$smarty->assign('act_range_ext', $act_range_ext);
		$smarty->assign('cfg_lang', $_CFG['lang']);
		$smarty->assign('region_store_enabled', $_CFG['region_store_enabled']);

		if ($is_add) {
			$smarty->assign('ur_here', $_LANG['add_favourable']);
		}
		else {
			$smarty->assign('ur_here', $_LANG['edit_favourable']);
		}

		$href = 'favourable.php?act=list';

		if (!$is_add) {
			$href .= '&' . list_link_postfix();
		}

		$smarty->assign('action_link', array('href' => $href, 'text' => $_LANG['favourable_list']));
		assign_query_info();
		$smarty->display('favourable_info.dwt');
	}
	else {
		if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
			admin_priv('favourable');
			$ru_id = isset($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : 0;

			if ($_POST['userFav_type']) {
				$userFav_type_ext = '';
			}
			else {
				$userFav_type_ext = empty($_POST['ext_ids']) ? '' : trim($_POST['ext_ids']);
			}

			$is_add = $_REQUEST['act'] == 'insert';
			$now = gmtime();
			$act_id = intval($_POST['id']);
			$act_range = intval($_POST['act_range']);
			$act_range_ext = isset($_POST['act_range_ext']) && !empty($_POST['act_range_ext']) ? implode(',', $_POST['act_range_ext']) : '';

			if ($is_add) {
				$favourable_info['user_id'] = $ru_id;
			}
			else if ($_CFG['region_store_enabled']) {
				$favourable_info = get_table_info('favourable_activity', 'act_id = \'' . $act_id . '\'', array('user_id', 'rs_id', 'userFav_type', 'userFav_type_ext', 'review_status'));
			}
			else {
				$favourable_info = get_table_info('favourable_activity', 'act_id = \'' . $act_id . '\'', array('user_id', 'review_status'));
			}

			$act_range_ext_cat = get_act_range_ext(FAR_CATEGORY, $act_id);
			$goods_list_cat = get_range_goods(FAR_CATEGORY, $act_range_ext_cat, 'cat_id', $favourable_info['user_id']);
			$act_range_ext_brand = get_act_range_ext(FAR_BRAND, $act_id);
			$goods_list_brand = get_range_goods(FAR_BRAND, $act_range_ext_brand, 'brand_id', $favourable_info['user_id']);
			$act_range_ext_goods = get_act_range_ext(FAR_GOODS, $act_id);
			$goods_list_goods = get_range_goods(FAR_GOODS, $act_range_ext_goods, 'goods_id', $favourable_info['user_id']);

			if ($_CFG['region_store_enabled']) {
				if ($is_add) {
					$rs_id = $adminru['rs_id'];
				}
				else {
					$rs_id = $favourable_info['rs_id'];
				}

				$mer_ids = get_favourable_merchants(intval($_POST['userFav_type']), $userFav_type_ext, $rs_id, 1);
				if (!$mer_ids && $userFav_type_ext) {
					sys_msg($_LANG['rs_no_merchants_notice'], 1);
				}
			}

			switch ($act_range) {
			case 0:
				$where = '';

				if ($act_id) {
					$where .= ' AND act_id <> \'' . $act_id . '\'';
				}

				if ($_CFG['region_store_enabled']) {
					if ($_POST['userFav_type'] == 0 && $userFav_type_ext) {
						$sql = ' SELECT userFav_type_ext, userFav_type, rs_id FROM ' . $GLOBALS['ecs']->table('favourable_activity') . ' WHERE user_id = \'' . $favourable_info['user_id'] . ('\' AND start_time <= \'' . $now . '\' AND end_time >= \'' . $now . '\' ' . $where);
						$rows = $GLOBALS['db']->getAll($sql);

						if ($rows) {
							foreach ($rows as $val) {
								$arr = get_favourable_merchants($val['userFav_type'], $val['userFav_type_ext'], $val['rs_id'], 1);
								$res = @array_intersect($mer_ids, $arr);

								if ($res) {
									sys_msg($_LANG['lab_act_range_desc'][0], 1);
								}
							}
						}
					}
					else {
						$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('favourable_activity') . ' WHERE user_id = \'' . $favourable_info['user_id'] . ('\' AND start_time <= \'' . $now . '\' AND end_time >= \'' . $now . '\' ' . $where);
						$num = $GLOBALS['db']->getOne($sql);

						if ($num) {
							sys_msg($_LANG['lab_act_range_desc'][0], 1);
						}
					}
				}
				else {
					$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('favourable_activity') . ' WHERE user_id = \'' . $favourable_info['user_id'] . ('\' AND start_time <= \'' . $now . '\' AND end_time >= \'' . $now . '\' ' . $where);
					$num = $GLOBALS['db']->getOne($sql);

					if ($num) {
						sys_msg($_LANG['lab_act_range_desc'][0], 1);
					}
				}

				break;

			case 1:
				$goods_list_cat_new = get_range_goods(FAR_CATEGORY, $_POST['act_range_ext'], 'cat_id');
				$arr = array_intersect($goods_list_cat, $goods_list_cat_new);
				$arr1 = array_intersect($goods_list_brand, $goods_list_cat_new);
				$arr2 = array_intersect($goods_list_goods, $goods_list_cat_new);
				if ($arr || $arr1 || $arr2) {
					sys_msg($_LANG['lab_act_range_desc'][1], 1);
				}

				break;

			case 2:
				$goods_list_brand_new = get_range_goods(FAR_BRAND, $_POST['act_range_ext'], 'brand_id');
				$arr = array_intersect($goods_list_cat, $goods_list_brand_new);
				$arr1 = array_intersect($goods_list_brand, $goods_list_brand_new);
				$arr2 = array_intersect($goods_list_brand_new, $goods_list_goods);
				if ($arr || $arr1 || $arr2) {
					sys_msg($_LANG['lab_act_range_desc'][2], 1);
				}

				break;

			case 3:
				$goods_list_goods_new = get_range_goods(FAR_GOODS, $_POST['act_range_ext'], 'goods_id');
				$arr = array_intersect($goods_list_cat, $goods_list_goods_new);
				$arr1 = array_intersect($goods_list_brand, $goods_list_goods_new);
				$arr2 = array_intersect($goods_list_goods, $goods_list_goods_new);
				if ($arr || $arr1 || $arr2) {
					sys_msg($_LANG['lab_act_range_desc'][0], 1);
				}

				break;

			default:
				break;
			}

			$act_name = sub_str($_POST['act_name'], 255, false);

			if (!$exc->is_only('act_name', $act_name, intval($_POST['id']))) {
				sys_msg($_LANG['act_name_exists'], 1);
			}

			if (!isset($_POST['user_rank'])) {
				sys_msg($_LANG['pls_set_user_rank'], 1);
			}

			if (0 < intval($_POST['act_range']) && !isset($_POST['act_range_ext'])) {
				sys_msg($_LANG['pls_set_act_range'], 1);
			}

			$min_amount = 0 <= floatval($_POST['min_amount']) ? floatval($_POST['min_amount']) : 0;
			$max_amount = 0 <= floatval($_POST['max_amount']) ? floatval($_POST['max_amount']) : 0;
			if (0 < $max_amount && $max_amount < $min_amount) {
				sys_msg($_LANG['amount_error'], 1);
			}

			$gift = array();
			if (intval($_POST['act_type']) == FAT_GOODS && isset($_POST['gift_id'])) {
				foreach ($_POST['gift_id'] as $key => $id) {
					$gift[] = array('id' => $id, 'name' => $_POST['gift_name'][$key], 'price' => $_POST['gift_price'][$key]);
				}
			}

			$favourable = array('act_id' => intval($_POST['id']), 'act_name' => $act_name, 'start_time' => local_strtotime($_POST['start_time']), 'end_time' => local_strtotime($_POST['end_time']), 'user_rank' => isset($_POST['user_rank']) ? join(',', $_POST['user_rank']) : '0', 'act_range' => intval($_POST['act_range']), 'act_range_ext' => $act_range_ext, 'min_amount' => floatval($_POST['min_amount']), 'max_amount' => floatval($_POST['max_amount']), 'act_type' => intval($_POST['act_type']), 'act_type_ext' => floatval($_POST['act_type_ext']), 'gift' => serialize($gift), 'review_status' => empty($admin_rs_id) ? 3 : 1);
			$favourable['userFav_type'] = intval($_POST['userFav_type']);
			$favourable['userFav_type_ext'] = $userFav_type_ext;

			if ($_CFG['region_store_enabled']) {
				if ($adminru['rs_id'] && $is_add) {
					$favourable['rs_id'] = $adminru['rs_id'];
				}
			}

			if ($favourable['act_type'] == FAT_GOODS) {
				$favourable['act_type_ext'] = round($favourable['act_type_ext']);
			}

			$activity_thumb = $image->upload_image($_FILES['activity_thumb'], 'activity_thumb');
			get_oss_add_file(array($activity_thumb));

			if ($is_add) {
				$favourable['user_id'] = $adminru['ru_id'];
				$favourable['activity_thumb'] = $activity_thumb;
				$db->autoExecute($ecs->table('favourable_activity'), $favourable, 'INSERT');
				$favourable['act_id'] = $db->insert_id();
			}
			else {
				if (isset($_POST['review_status'])) {
					$review_status = !empty($_POST['review_status']) ? intval($_POST['review_status']) : 1;
					$review_content = !empty($_POST['review_content']) ? addslashes(trim($_POST['review_content'])) : '';
					$favourable['review_status'] = $review_status;
					$favourable['review_content'] = $review_content;
				}

				if (!empty($activity_thumb)) {
					$favourable['activity_thumb'] = $activity_thumb;
				}

				$db->autoExecute($ecs->table('favourable_activity'), $favourable, 'UPDATE', 'act_id = \'' . $favourable['act_id'] . '\'');
			}

			if ($is_add) {
				admin_log($favourable['act_name'], 'add', 'favourable');
			}
			else {
				admin_log($favourable['act_name'], 'edit', 'favourable');
			}

			clear_cache_files();

			if ($is_add) {
				$links = array(
					array('href' => 'favourable.php?act=add', 'text' => $_LANG['continue_add_favourable']),
					array('href' => 'favourable.php?act=list', 'text' => $_LANG['back_favourable_list'])
					);
				sys_msg($_LANG['add_favourable_ok'], 0, $links);
			}
			else {
				$links = array(
					array('href' => 'favourable.php?act=edit&id=' . $favourable['act_id'] . '&ru_id=' . $ru_id, 'text' => $_LANG['edit_favourable']),
					array('href' => 'favourable.php?act=list&' . list_link_postfix(), 'text' => $_LANG['back_favourable_list'])
					);
				sys_msg($_LANG['edit_favourable_ok'], 0, $links);
			}
		}
		else if ($_REQUEST['act'] == 'drop_thumb') {
			admin_priv('brand_manage');
			$act_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$ru_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
			get_del_batch('', $act_id, array('activity_thumb'), 'act_id', 'favourable_activity', 1);
			$sql = 'UPDATE ' . $ecs->table('favourable_activity') . (' SET activity_thumb = \'\' WHERE act_id = \'' . $act_id . '\'');
			$db->query($sql);
			$link = array(
				array('text' => $_LANG['edit_favourable'], 'href' => 'favourable.php?act=edit&id=' . $act_id . '&ru_id=' . $ru_id),
				array('text' => $_LANG['favourable_list'], 'href' => 'favourable.php?act=list')
				);
			sys_msg($_LANG['drop_activity_thumb_success'], 0, $link);
		}
		else if ($_REQUEST['act'] == 'search') {
			check_authz_json('favourable');
			include_once ROOT_PATH . 'includes/cls_json.php';
			$json = new JSON();
			$filter = $json->decode($_GET['JSON']);
			$filter->keyword = json_str_iconv($filter->keyword);
			$ru_id = $filter->ru_id;
			$where = '';

			if ($ru_id == 0) {
				$where .= ' LIMIT 50';
			}

			if ($filter->act_range == FAR_ALL) {
				$arr[0] = array('id' => 0, 'name' => $_LANG['js_languages']['all_need_not_search']);
			}
			else if ($filter->act_range == FAR_CATEGORY) {
				if ($ru_id) {
					$arr = get_user_cat_list($ru_id);
					$arr = get_user_cat_search($ru_id, $filter->keyword, $arr);
					$arr = array_values($arr);
				}
				else {
					$sql = 'SELECT c.cat_id AS id, c.cat_name AS name FROM ' . $ecs->table('category') . ' AS c ' . ' WHERE c.cat_name LIKE \'%' . mysql_like_quote($filter->keyword) . '%\'' . $where;
					$arr = $db->getAll($sql);
				}
			}
			else if ($filter->act_range == FAR_BRAND) {
				$sql = 'SELECT brand_id AS id, brand_name AS name FROM ' . $ecs->table('brand') . ' WHERE brand_name LIKE \'%' . mysql_like_quote($filter->keyword) . '%\'' . $where;
				$arr = $db->getAll($sql);

				if ($arr) {
					foreach ($arr as $key => $row) {
						if ($ru_id) {
							$arr[$key]['is_brand'] = get_seller_brand_count($row['id'], $ru_id);
						}
						else {
							$arr[$key]['is_brand'] = 1;
						}

						if (!(0 < $arr[$key]['is_brand'])) {
							unset($arr[$key]);
						}
					}

					$arr = array_values($arr);
				}
			}
			else {
				$sql = 'SELECT goods_id AS id, goods_name AS name FROM ' . $ecs->table('goods') . ' WHERE (goods_name LIKE \'%' . mysql_like_quote($filter->keyword) . '%\'' . ' OR goods_sn LIKE \'%' . mysql_like_quote($filter->keyword) . ('%\')  AND user_id = \'' . $ru_id . '\' LIMIT 50');
				$arr = $db->getAll($sql);
			}

			if (empty($arr)) {
				$arr = array(
					array('id' => 0, 'name' => $_LANG['search_result_empty'])
					);
			}

			make_json_result($arr);
		}
		else if ($_REQUEST['act'] == 'set_use_range') {
			require_once ROOT_PATH . '/includes/cls_json.php';
			$json = new JSON();
			$result = array('content' => '', 'mode' => '');
			$rs_id = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);
			$admin_rs_id = empty($rs_id) ? $admin_rs_id : $rs_id;

			if ($admin_rs_id) {
				$range_list = get_merchants_list($admin_rs_id);
				$smarty->assign('is_rs', $admin_rs_id);
			}
			else {
				$range_list = get_rs_list();
			}

			$smarty->assign('range_list', $range_list);
			$result['content'] = $GLOBALS['smarty']->fetch('library/favourable_select_range.lbi');
			exit($json->encode($result));
		}
		else if ($_REQUEST['act'] == 'changedrs') {
			require_once ROOT_PATH . '/includes/cls_json.php';
			$json = new JSON();
			$keyword = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
			$result = array('error' => 0, 'message' => '', 'content' => '');
			$rs_ids = isset($_REQUEST['rs_ids']) ? explode(',', $_REQUEST['rs_ids']) : '';
			$type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
			$where = ' WHERE 1 ';

			if ($keyword) {
				$where .= ' AND rs.rs_name LIKE \'%' . $keyword . '%\'';
			}

			if ($rs_ids && $type == '0') {
				$where .= ' AND rs.rs_id ' . db_create_in($rs_ids);
			}

			if ($type == 1) {
				$list = getRsList($where);
				$rs_list = $list['list'];
				$filter = $list['filter'];
				$filter['keyword'] = $keyword;
				$smarty->assign('filter', $filter);
			}
			else {
				$sql = ' SELECT rs.rs_name, rs.rs_id FROM ' . $ecs->table('region_store') . ' AS rs ' . $where;
				$rs_list = $GLOBALS['db']->getAll($sql);
			}

			if (!empty($rs_list)) {
				foreach ($rs_list as $k => $v) {
					if (0 < $v['rs_id'] && in_array($v['rs_id'], $rs_ids) && !empty($rs_ids)) {
						$rs_list[$k]['is_selected'] = 1;
					}
				}
			}

			$smarty->assign('goods_count', count($goods_list));
			$smarty->assign('rs_list', $rs_list);
			$result['content'] = $GLOBALS['smarty']->fetch('library/region_store_list.lbi');
			exit(json_encode($result));
		}
		else if ($_REQUEST['act'] == 'changedmer') {
			require_once ROOT_PATH . '/includes/cls_json.php';
			$json = new JSON();
			$keyword = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
			$result = array('error' => 0, 'message' => '', 'content' => '');
			$mer_ids = isset($_REQUEST['mer_ids']) ? explode(',', $_REQUEST['mer_ids']) : '';
			$type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
			$rs_id = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);
			$admin_rs_id = empty($rs_id) || $admin_rs_id ? $admin_rs_id : $rs_id;
			$where = ' WHERE 1 ';
			$leftjoin = '';

			if ($keyword) {
				$leftjoin = ' LEFT JOIN ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS ss ON ss.ru_id = m.user_id ';
				$where .= ' AND ss.shop_name LIKE \'%' . $keyword . '%\'';
			}

			if ($mer_ids && $type == '0') {
				$where .= ' AND m.user_id ' . db_create_in($mer_ids);
			}

			if ($admin_rs_id) {
				$where .= ' AND rs.rs_id = \'' . $admin_rs_id . '\' ';
			}

			if ($type == 1) {
				$list = getMerList($where, '', $leftjoin);
				$mer_list = $list['list'];
				$filter = $list['filter'];
				$filter['keyword'] = $keyword;
				$smarty->assign('filter', $filter);
			}
			else {
				$sql = ' SELECT m.shop_id, m.user_id FROM ' . $GLOBALS['ecs']->table('region_store') . ' rs LEFT JOIN ' . $GLOBALS['ecs']->table('rs_region') . ' AS rr ON rs.rs_id = rr.rs_id LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS m ON rr.region_id = m.region_id ' . $leftjoin . $where;
				$mer_list = $GLOBALS['db']->getAll($sql);
			}

			if (!empty($mer_list)) {
				foreach ($mer_list as $k => $v) {
					$mer_list[$k]['shop_name'] = get_shop_name($v['user_id'], 1);
					if (0 < $v['user_id'] && in_array($v['user_id'], $mer_ids) && !empty($mer_ids)) {
						$mer_list[$k]['is_selected'] = 1;
					}
				}
			}

			$smarty->assign('goods_count', count($goods_list));
			$smarty->assign('mer_list', $mer_list);
			$result['content'] = $GLOBALS['smarty']->fetch('library/rs_mer_list.lbi');
			exit(json_encode($result));
		}
		else if ($_REQUEST['act'] == 'marketing_center') {
			require_once ROOT_PATH . '/includes/cls_json.php';
			$json = new JSON();
			$smarty->assign('ur_here', $_LANG['02_marketing_center']);
			$smarty->display('marketing_center.dwt');
		}
	}
}

?>
