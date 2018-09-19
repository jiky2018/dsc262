<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_seckill_list($ru_id)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'sec_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$where = ' WHERE 1 ';
		$where .= empty($_REQUEST['sec_id']) ? '' : ' AND sec_id = \'' . trim($_REQUEST['sec_id']) . '\' ';
		$where .= !empty($filter['keywords']) ? ' AND acti_title like \'%' . mysql_like_quote($filter['keywords']) . '%\'' : '';
		$where .= empty($_REQUEST['review_status']) ? '' : ' AND review_status = \'' . intval($_REQUEST['review_status']) . '\' ';

		if ($ru_id) {
			$where .= ' AND ru_id = \'' . $ru_id . '\' ';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('seckill') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT sec_id, begin_time, acti_title, is_putaway, acti_time, review_status ' . ' FROM ' . $GLOBALS['ecs']->table('seckill') . $where . (' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ') . $filter['start'] . ', ' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		$time = time();
		$row[$key]['begin_time'] = local_date('Y-m-d H:i:s', $val['begin_time']);
		$row[$key]['acti_time'] = local_date('Y-m-d H:i:s', $val['acti_time']);
		$start_time = local_strtotime($row[$key]['begin_time']);
		$end_time = local_strtotime($row[$key]['acti_time']);

		if ($end_time < $time) {
			$row[$key]['time'] = '活动结束';
		}
		else {
			if ($time < $end_time && $start_time < $time) {
				$row[$key]['time'] = '活动进行中';
			}
			else {
				$row[$key]['time'] = '活动未开始';
			}
		}
	}

	$arr = array('seckill' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_seckill_info()
{
	$sql = ' SELECT sec_id, begin_time, acti_title, acti_time, is_putaway, ru_id, review_status FROM ' . $GLOBALS['ecs']->table('seckill') . ' WHERE sec_id = \'' . intval($_REQUEST['sec_id']) . '\' ';
	$arr = $GLOBALS['db']->getRow($sql);
	$arr['begin_time'] = local_date('Y-m-d', $arr['begin_time']);
	$arr['acti_time'] = local_date('Y-m-d', $arr['acti_time']);
	return $arr;
}

function get_time_bucket_list()
{
	$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('seckill_time_bucket');
	$res = $GLOBALS['db']->getAll($sql);
	return $res;
}

function sec_object_to_array($obj)
{
	$_arr = is_object($obj) ? get_object_vars($obj) : $obj;

	if ($_arr) {
		foreach ($_arr as $key => $val) {
			$val = is_array($val) || is_object($val) ? object_to_array($val) : $val;
			$arr[$key] = $val;
		}
	}
	else {
		$arr = array();
	}

	return $arr;
}

function getGoodslist($where = '', $sort = '', $search = '', $leftjoin = '')
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftjoin . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$where .= $sort . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
	$sql = 'SELECT g.promote_start_date, g.promote_end_date, g.promote_price, g.goods_name, g.goods_id, g.goods_thumb, g.shop_price, g.market_price, g.original_img ' . $search . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftjoin . $where;
	$goods_list = $GLOBALS['db']->getAll($sql);
	$filter['page_arr'] = seller_page($filter, $filter['page']);
	return array('list' => $goods_list, 'filter' => $filter);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/cls_json.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('action_type', 'seckill');
$exc = new exchange($ecs->table('seckill'), $db, 'sec_id', 'acti_title');
$exc_sg = new exchange($ecs->table('seckill_goods'), $db, 'id', 'sec_id');
$smarty->assign('controller', basename(PHP_SELF, '.php'));

if ($_REQUEST['act'] == 'list') {
	admin_priv('seckill_manage');
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '03_seckill_list'));
	$smarty->assign('full_page', 1);
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('ur_here', $_LANG['03_seckill_list']);
	$smarty->assign('action_link', array('href' => 'seckill.php?act=add', 'text' => $_LANG['seckill_add'], 'class' => 'icon-plus'));
	$list = get_seckill_list($adminru['ru_id']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('seckill_list', $list['seckill']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('seckill_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$list = get_seckill_list($adminru['ru_id']);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('seckill_list', $list['seckill']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('seckill_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else if ($_REQUEST['act'] == 'sg_query') {
	require_once 'includes/lib_goods.php';
	$sec_id = empty($_REQUEST['sec_id']) ? 0 : intval($_REQUEST['sec_id']);
	$tb_id = empty($_REQUEST['tb_id']) ? 0 : intval($_REQUEST['tb_id']);
	$list = get_add_seckill_goods($sec_id, $tb_id);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('cat_goods', $list['cat_goods']);
	$smarty->assign('seckill_goods', $list['seckill_goods']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	make_json_result($smarty->fetch('seckill_set_goods_info.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('seckill_manage');
	$sec_id = intval($_REQUEST['id']);

	if ($sec_id) {
		$res = $exc->drop($sec_id);

		if ($res) {
			$db->query(' DELETE FROM ' . $ecs->table('seckill_goods') . (' WHERE sec_id=\'' . $sec_id . '\' '));
		}
	}

	$url = 'seckill.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'sg_remove') {
	$id = intval($_REQUEST['id']);
	$sql = ' SELECT sec_id, tb_id FROM ' . $ecs->table('seckill_goods') . (' WHERE id = \'' . $id . '\' ');
	$res = $db->getRow($sql);
	$sec_id = $res['sec_id'];
	$tb_id = $res['tb_id'];

	if ($id) {
		$res = $exc_sg->drop($id);
	}

	$url = 'seckill.php?act=sg_query&sec_id=' . $sec_id . '&tb_id=' . $tb_id . str_replace('act=sg_remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		admin_priv('seckill_manage');
		$smarty->assign('primary_cat', $_LANG['02_promotion']);
		$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '03_seckill_list'));
		$smarty->assign('action_link', array('href' => 'seckill.php?act=list', 'text' => $_LANG['seckill_list'], 'class' => 'icon-reply'));
		$sec_id = !empty($_GET['sec_id']) ? intval($_GET['sec_id']) : 1;

		if ($_REQUEST['act'] == 'add') {
			$smarty->assign('ur_here', $_LANG['seckill_add']);
			$smarty->assign('form_act', 'insert');
			$tomorrow = local_strtotime('+1 days');
			$next_week = local_strtotime('+8 days');
			$seckill_arr['begin_time'] = local_date('Y-m-d', $tomorrow);
			$seckill_arr['acti_time'] = local_date('Y-m-d', $next_week);
		}
		else {
			$smarty->assign('ur_here', $_LANG['seckill_edit']);
			$smarty->assign('form_act', 'update');
			$seckill_arr = get_seckill_info();
		}

		$smarty->assign('sec', $seckill_arr);
		assign_query_info();
		$smarty->display('seckill_info.dwt');
	}
	else {
		if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
			admin_priv('seckill_manage');
			$sec_id = empty($_REQUEST['sec_id']) ? '' : intval($_REQUEST['sec_id']);
			$acti_title = $_REQUEST['acti_title'] ? trim($_REQUEST['acti_title']) : '';
			$begin_time = local_strtotime($_REQUEST['begin_time']);
			$acti_time = local_strtotime($_REQUEST['acti_time']);
			$is_putaway = empty($_REQUEST['is_putaway']) ? 0 : intval($_REQUEST['is_putaway']);
			$add_time = gmtime();
			$ru_id = $adminru['ru_id'];
			$review_status = 1;

			if ($_REQUEST['act'] == 'insert') {
				$is_only = $exc->is_only('acti_title', $_REQUEST['acti_title'], 0);

				if (!$is_only) {
					sys_msg(sprintf($_LANG['title_exist'], stripslashes($_REQUEST['acti_title'])), 1);
				}

				$sql = 'INSERT INTO ' . $ecs->table('seckill') . (" (acti_title, begin_time, acti_time, is_putaway, add_time, ru_id, review_status)\r\n\t\tVALUES ('" . $acti_title . '\', \'' . $begin_time . '\', \'' . $acti_time . '\', \'' . $is_putaway . '\', \'' . $add_time . '\', \'' . $ru_id . '\', ' . $review_status . ')');

				if ($db->query($sql)) {
					$link[0]['text'] = $_LANG['back_list'];
					$link[0]['href'] = 'seckill.php?act=list';
					sys_msg($_LANG['add'] . '&nbsp;' . $_POST['acti_title'] . '&nbsp;' . $_LANG['attradd_succed'], 0, $link);
				}
				else {
					sys_msg($_LANG['add'] . '&nbsp;' . $_POST['acti_title'] . '&nbsp;' . $_LANG['attradd_failed'], 1);
				}
			}
			else {
				$is_only = $exc->is_only('acti_title', $_POST['acti_title'], 0, 'sec_id != \'' . $sec_id . '\'');

				if (!$is_only) {
					sys_msg(sprintf($_LANG['title_exist'], stripslashes($_REQUEST['acti_title'])), 1);
				}

				$sql = 'UPDATE ' . $ecs->table('seckill') . ' SET ' . (' acti_title       = \'' . $acti_title . '\', ') . (' begin_time       = \'' . $begin_time . '\', ') . (' acti_time        = \'' . $acti_time . '\', ') . (' is_putaway       = \'' . $is_putaway . '\', ') . (' review_status    = \'' . $review_status . '\' ') . (' WHERE sec_id     = \'' . $sec_id . '\'');
				$db->query($sql);
				clear_cache_files();
				$link[0]['text'] = $_LANG['back_list'];
				$link[0]['href'] = 'seckill.php?act=list';
				sys_msg($_LANG['edit'] . '&nbsp;' . $_POST['acti_title'] . '&nbsp;' . $_LANG['attradd_succed'], 0, $link);
			}
		}
		else if ($_REQUEST['act'] == 'toggle_putaway') {
			$id = intval($_REQUEST['id']);
			$val = intval($_REQUEST['val']);
			$sql = 'UPDATE ' . $ecs->table('seckill') . (' SET is_putaway = \'' . $val . '\' WHERE sec_id = \'' . $id . '\'');
			$result = $db->query($sql);

			if ($result) {
				clear_cache_files();
				make_json_result($val);
			}
		}
	}
}

if ($_REQUEST['act'] == 'set_goods') {
	admin_priv('seckill_manage');
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '03_seckill_list'));
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('ur_here', $_LANG['03_seckill_list']);
	$sec_id = !empty($_GET['sec_id']) ? intval($_GET['sec_id']) : 0;
	$smarty->assign('ur_here', $_LANG['set_seckill_goods']);
	$smarty->assign('action_link', array('text' => $_LANG['seckill_list'], 'href' => 'seckill.php?act=list', 'class' => 'icon-reply'));
	$list = get_time_bucket_list();
	$smarty->assign('sec_id', $sec_id);
	$smarty->assign('time_bucket', $list);
	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->display('seckill_set_goods.dwt');
}

if ($_REQUEST['act'] == 'add_goods') {
	admin_priv('seckill_manage');
	require_once 'includes/lib_goods.php';
	$sec_id = !empty($_GET['sec_id']) ? intval($_GET['sec_id']) : 0;
	$tb_id = !empty($_GET['tb_id']) ? intval($_GET['tb_id']) : 0;
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '03_seckill_list'));
	$smarty->assign('action_link', array('text' => $_LANG['seckill_time_bucket'], 'href' => 'seckill.php?act=set_goods&sec_id=' . $sec_id));
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('ur_here', $_LANG['03_seckill_list']);
	set_default_filter();
	assign_query_info();
	$list = get_add_seckill_goods($sec_id, $tb_id);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('cat_goods', $list['cat_goods']);
	$smarty->assign('seckill_goods', $list['seckill_goods']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$smarty->assign('ur_here', $_LANG['seckill_goods_info']);
	$smarty->assign('sec_id', $sec_id);
	$smarty->assign('tb_id', $tb_id);
	$smarty->assign('full_page', 1);
	$smarty->display('seckill_set_goods_info.dwt');
}
else if ($_REQUEST['act'] == 'sg_remove') {
	$id = intval($_REQUEST['id']);
	$sql = ' SELECT sec_id, tb_id FROM ' . $ecs->table('seckill_goods') . (' WHERE id = \'' . $id . '\' ');
	$res = $db->getRow($sql);
	$sec_id = $res['sec_id'];
	$tb_id = $res['tb_id'];

	if ($id) {
		$res = $exc_sg->drop($id);
	}

	$url = 'seckill.php?act=sg_query&sec_id=' . $sec_id . '&tb_id=' . $tb_id . str_replace('act=sg_remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'goods_info') {
	$json = new JSON();
	$result = array('content' => '', 'mode' => '');
	$cat_id = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$goods_type = isset($_REQUEST['goods_type']) ? intval($_REQUEST['goods_type']) : 0;
	$_REQUEST['spec_attr'] = strip_tags(urldecode($_REQUEST['spec_attr']));
	$_REQUEST['spec_attr'] = json_str_iconv($_REQUEST['spec_attr']);
	$_REQUEST['spec_attr'] = !empty($_REQUEST['spec_attr']) ? stripslashes($_REQUEST['spec_attr']) : '';

	if (!empty($_REQUEST['spec_attr'])) {
		$spec_attr = $json->decode(stripslashes($_REQUEST['spec_attr']));
		$spec_attr = sec_object_to_array($spec_attr);
	}

	$spec_attr['is_title'] = isset($spec_attr['is_title']) ? $spec_attr['is_title'] : 0;
	$spec_attr['itemsLayout'] = isset($spec_attr['itemsLayout']) ? $spec_attr['itemsLayout'] : 'row4';
	$result['mode'] = isset($_REQUEST['mode']) ? addslashes($_REQUEST['mode']) : '';
	$result['diff'] = isset($_REQUEST['diff']) ? intval($_REQUEST['diff']) : 0;
	$lift = isset($_REQUEST['lift']) ? trim($_REQUEST['lift']) : '';

	if ($spec_attr['goods_ids']) {
		$goods_info = explode(',', $spec_attr['goods_ids']);

		foreach ($goods_info as $k => $v) {
			if (!$v) {
				unset($goods_info[$k]);
			}
		}

		if (!empty($goods_info)) {
			$where = ' WHERE g.is_on_sale=1 AND g.is_delete=0 AND g.goods_id' . db_create_in($goods_info);

			if ($GLOBALS['_CFG']['review_goods'] == 1) {
				$where .= ' AND g.review_status > 2 ';
			}

			$sql = 'SELECT g.goods_name,g.goods_id,g.goods_thumb,g.original_img,g.shop_price FROM ' . $ecs->table('goods') . ' AS g ' . $where;
			$goods_list = $db->getAll($sql);

			foreach ($goods_list as $k => $v) {
				$goods_list[$k]['shop_price'] = price_format($v['shop_price']);
			}

			$smarty->assign('goods_list', $goods_list);
			$smarty->assign('goods_count', count($goods_list));
		}
	}

	set_default_filter(0, 0, $adminru['ru_id']);
	$smarty->assign('parent_category', get_every_category($cat_id));
	$smarty->assign('select_category_html', $select_category_html);
	$smarty->assign('brand_list', get_brand_list());
	$smarty->assign('arr', $spec_attr);
	$smarty->assign('goods_type', $goods_type);
	$smarty->assign('mode', $result['mode']);
	$smarty->assign('cat_id', $cat_id);
	$smarty->assign('lift', $lift);
	$result['content'] = $GLOBALS['smarty']->fetch('library/add_seckill_goods.lbi');
	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'changedgoods') {
	require ROOT_PATH . '/includes/lib_goods.php';
	$json = new JSON();
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$spec_attr = array();
	$result['lift'] = isset($_REQUEST['lift']) ? trim($_REQUEST['lift']) : '';
	$result['spec_attr'] = !empty($_REQUEST['spec_attr']) ? stripslashes($_REQUEST['spec_attr']) : '';

	if ($_REQUEST['spec_attr']) {
		$_REQUEST['spec_attr'] = strip_tags(urldecode($_REQUEST['spec_attr']));
		$_REQUEST['spec_attr'] = json_str_iconv($_REQUEST['spec_attr']);

		if (!empty($_REQUEST['spec_attr'])) {
			$spec_attr = $json->decode($_REQUEST['spec_attr']);
			$spec_attr = object_to_array($spec_attr);
		}
	}

	$sort_order = isset($_REQUEST['sort_order']) ? $_REQUEST['sort_order'] : 1;
	$cat_id = isset($_REQUEST['cat_id']) ? explode('_', $_REQUEST['cat_id']) : array();
	$brand_id = isset($_REQUEST['brand_id']) ? intval($_REQUEST['brand_id']) : 0;
	$sec_id = isset($_REQUEST['sec_id']) ? intval($_REQUEST['sec_id']) : 0;
	$tb_id = isset($_REQUEST['tb_id']) ? intval($_REQUEST['tb_id']) : 0;
	$keyword = isset($_REQUEST['keyword']) ? addslashes($_REQUEST['keyword']) : '';
	$goodsAttr = isset($spec_attr['goods_ids']) ? explode(',', $spec_attr['goods_ids']) : '';
	$goods_ids = isset($_REQUEST['goods_ids']) ? explode(',', $_REQUEST['goods_ids']) : '';
	$result['goods_ids'] = !empty($goodsAttr) ? $goodsAttr : $goods_ids;
	$result['cat_desc'] = isset($spec_attr['cat_desc']) ? addslashes($spec_attr['cat_desc']) : '';
	$result['cat_name'] = isset($spec_attr['cat_name']) ? addslashes($spec_attr['cat_name']) : '';
	$result['align'] = isset($spec_attr['align']) ? addslashes($spec_attr['align']) : '';
	$result['is_title'] = isset($spec_attr['is_title']) ? intval($spec_attr['is_title']) : 0;
	$result['itemsLayout'] = isset($spec_attr['itemsLayout']) ? addslashes($spec_attr['itemsLayout']) : '';
	$result['diff'] = isset($_REQUEST['diff']) ? intval($_REQUEST['diff']) : 0;
	$type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
	$temp = isset($_REQUEST['temp']) ? $_REQUEST['temp'] : 'goods_list';
	$resetRrl = isset($_REQUEST['resetRrl']) ? intval($_REQUEST['resetRrl']) : 0;
	$result['mode'] = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
	$smarty->assign('temp', $temp);
	$where = 'WHERE g.is_on_sale=1 AND g.is_delete=0 ';

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$where .= ' AND g.review_status > 2 ';
	}

	if (0 < $cat_id[0]) {
		$where .= ' AND ' . get_children($cat_id[0]);
	}

	if (0 < $brand_id) {
		$where .= ' AND g.brand_id = \'' . $brand_id . '\'';
	}

	if ($keyword) {
		$where .= ' AND g.goods_name  LIKE \'%' . $keyword . '%\'';
	}

	if ($result['goods_ids'] && $type == '0') {
		$where .= ' AND g.goods_id' . db_create_in($result['goods_ids']);
	}

	$where .= ' AND g.user_id = \'' . $adminru['ru_id'] . '\' ';
	$sort = '';

	switch ($sort_order) {
	case '1':
		$sort .= ' ORDER BY g.add_time ASC';
		break;

	case '2':
		$sort .= ' ORDER BY g.add_time DESC';
		break;

	case '3':
		$sort .= ' ORDER BY g.sort_order ASC';
		break;

	case '4':
		$sort .= ' ORDER BY g.sort_order DESC';
		break;

	case '5':
		$sort .= ' ORDER BY g.goods_name ASC';
		break;

	case '6':
		$sort .= ' ORDER BY g.goods_name DESC';
		break;
	}

	if ($type == 1) {
		$list = getGoodslist($where, $sort);
		$goods_list = $list['list'];
		$filter = $list['filter'];
		$filter['cat_id'] = $cat_id[0];
		$filter['sort_order'] = $sort_order;
		$filter['keyword'] = $keyword;
		$smarty->assign('filter', $filter);
	}
	else {
		$sql = 'SELECT g.promote_start_date, g.promote_end_date, g.promote_price, g.goods_name, g.goods_id, g.goods_thumb, g.shop_price, g.market_price, g.original_img FROM ' . $ecs->table('goods') . ' AS g ' . $where . $sort;
		$goods_list = $db->getAll($sql);
	}

	if (!empty($goods_list)) {
		foreach ($goods_list as $k => $v) {
			$goods_list[$k]['goods_thumb'] = get_image_path($v['goods_id'], $v['goods_thumb']);
			$goods_list[$k]['original_img'] = get_image_path($v['goods_id'], $v['original_img']);
			$goods_list[$k]['url'] = build_uri('goods', array('gid' => $v['goods_id']), $v['goods_name']);
			$goods_list[$k]['shop_price'] = price_format($v['shop_price']);

			if (0 < $v['promote_price']) {
				$goods_list[$k]['promote_price'] = bargain_price($v['promote_price'], $v['promote_start_date'], $v['promote_end_date']);
			}
			else {
				$goods_list[$k]['promote_price'] = 0;
			}

			if (0 < $v['goods_id'] && in_array($v['goods_id'], $result['goods_ids']) && !empty($result['goods_ids'])) {
				$goods_list[$k]['is_selected'] = 1;
			}
		}
	}

	$smarty->assign('is_title', $result['is_title']);
	$smarty->assign('goods_list', $goods_list);
	$smarty->assign('goods_count', count($goods_list));
	$smarty->assign('attr', $spec_attr);
	$result['content'] = $GLOBALS['smarty']->fetch('library/seckill_goods_list.lbi');
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'edit_sec_price') {
	check_authz_json('seckill_manage');
	$id = intval($_POST['id']);
	$sec_price = floatval($_POST['val']);

	if ($exc_sg->edit('sec_price = \'' . $sec_price . '\'', $id)) {
		clear_cache_files();
		make_json_result($sec_price);
	}
}
else if ($_REQUEST['act'] == 'edit_sec_num') {
	check_authz_json('seckill_manage');
	$id = intval($_POST['id']);
	$sec_num = intval($_POST['val']);

	if ($exc_sg->edit('sec_num = \'' . $sec_num . '\'', $id)) {
		clear_cache_files();
		make_json_result($sec_num);
	}
}
else if ($_REQUEST['act'] == 'edit_sec_limit') {
	check_authz_json('seckill_manage');
	$id = intval($_POST['id']);
	$sec_limit = intval($_POST['val']);

	if ($exc_sg->edit('sec_limit = \'' . $sec_limit . '\'', $id)) {
		clear_cache_files();
		make_json_result($sec_limit);
	}
}

?>
