<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_adslist($ru_id)
{
	$filter = array();
	$filter['keyword'] = !empty($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
	if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
		$filter['keyword'] = json_str_iconv($filter['keyword']);
	}

	$filter['adName'] = empty($_REQUEST['adName']) ? '' : trim($_REQUEST['adName']);
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'ad.ad_id' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$filter['pid'] = empty($_REQUEST['pid']) ? 0 : intval($_REQUEST['pid']);
	$where = 'WHERE 1 ';

	if (!empty($filter['pid'])) {
		$where .= ' AND ad.position_id = \'' . $filter['pid'] . '\' ';
	}

	if (!empty($filter['keyword'])) {
		$where .= ' AND (p.position_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\'' . ')';
	}

	if (!empty($filter['adName'])) {
		$where .= ' AND (ad.ad_name LIKE \'%' . mysql_like_quote($filter['adName']) . '%\'' . ')';
	}

	if (0 < $ru_id) {
		$where .= ' and (p.user_id = \'' . $ru_id . '\' or (p.is_public = 1 and ad.public_ruid = \'' . $ru_id . '\')) ';
	}

	$where .= ' AND p.theme = \'' . $GLOBALS['_CFG']['template'] . '\'';
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
				$where .= ' AND p.user_id = \'' . $filter['merchant_id'] . '\' ';
			}
			else if ($filter['store_search'] == 2) {
				$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
			}
			else if ($filter['store_search'] == 3) {
				$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
			}

			if (1 < $filter['store_search']) {
				$where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . (' WHERE msi.user_id = p.user_id ' . $store_where . ') > 0 ');
			}
		}
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('ad') . ' AS ad ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('ad_position') . ' AS p ON p.position_id = ad.position_id ' . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$arr = array();
	$sql = 'SELECT ad.*, COUNT(o.order_id) AS ad_stats, p.position_name, p.user_id ' . 'FROM ' . $GLOBALS['ecs']->table('ad') . 'AS ad ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('ad_position') . ' AS p ON p.position_id = ad.position_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . (' AS o ON o.from_ad = ad.ad_id ' . $where . ' ') . 'GROUP BY ad.ad_id ' . 'ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$idx = 0;

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$rows['type'] = $rows['media_type'] == 0 ? $GLOBALS['_LANG']['ad_img'] : '';
		$rows['type'] .= $rows['media_type'] == 1 ? $GLOBALS['_LANG']['ad_flash'] : '';
		$rows['type'] .= $rows['media_type'] == 2 ? $GLOBALS['_LANG']['ad_html'] : '';
		$rows['type'] .= $rows['media_type'] == 3 ? $GLOBALS['_LANG']['ad_text'] : '';
		$rows['start_date'] = local_date($GLOBALS['_CFG']['time_format'], $rows['start_time']);
		$rows['end_date'] = local_date($GLOBALS['_CFG']['time_format'], $rows['end_time']);

		if ($rows['public_ruid'] == 0) {
			$user_id = $rows['user_id'];
		}
		else {
			$user_id = $rows['public_ruid'];
		}

		$rows['user_name'] = get_shop_name($user_id, 1);
		$rows['ad_code'] = get_image_path($rows['ad_id'], DATA_DIR . '/afficheimg/' . $rows['ad_code']);
		$arr[$idx] = $rows;
		$idx++;
	}

	return array('ads' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_ad_model($pid)
{
	$ad_arr = array('ad_type' => 0, 'ad_model_init' => '', 'ad_model' => '', 'ad_model_structure' => '', 'cat_id' => '');
	$init_model = array('[num_id]', '[cat_id]');
	$sql = ' select * from ' . $GLOBALS['ecs']->table('ad_position') . ' where position_id=\'' . $pid . '\' limit 1';
	$position_info = $GLOBALS['db']->getRow($sql);

	if (!empty($position_info['position_model'])) {
		$ad_model = $position_info['position_model'];
		$ad_model_structure = array();
		$i = 0;

		foreach ($init_model as $model) {
			if (strpos($ad_model, $model)) {
				if ($model == '[num_id]') {
					$ad_arr['ad_type'] = 1;
				}

				if ($model == '[cat_id]') {
					$ad_arr['ad_type'] = 2;
				}

				$ad_model_structure[$i] = str_replace(array('[', ']'), array('', ''), $model);
				$i++;
				$ad_model = str_replace(array('_' . $model . '_', '_' . $model, $model . '_', $model), array('', '', '', ''), $ad_model);
			}
		}

		if (0 < $ad_arr['ad_type']) {
			$ad_arr['ad_model_init'] = $position_info['position_model'];
			$ad_arr['ad_model'] = $ad_model;
			$ad_arr['ad_model_structure'] = $init_model;
		}

		if (in_array('cat_id', $ad_model_structure) && in_array('num_id', $ad_model_structure)) {
			$ad_arr['ad_type'] = 3;
			$sql = ' select ad_name from ' . $GLOBALS['ecs']->table('ad') . ' where ad_name like \'%' . $ad_model . '%\'';
			$ad_exist = $GLOBALS['db']->getAll($sql);

			if (!empty($ad_exist)) {
				$ad_arr['ad_type'] = 4;
				$ad_all = array();

				foreach ($ad_exist as $key => $val) {
					$ad_deal = explode('_', str_replace($ad_model, '', $val['ad_name']));

					for ($j = 0; $j < count($ad_model_structure); $j++) {
						$ad_all[$key][$ad_model_structure[$j]] = $ad_deal[$j];
					}
				}

				foreach ($ad_all as $key => $val) {
					$ad_arr['cat_id'][$val['cat_id']]['num_id'][] = $val['num_id'];
				}

				foreach ($ad_arr['cat_id'] as $key => $val) {
					$ad_arr['cat_id'][$key]['next'] = NULL;

					for ($p = 1; $p < 9999; $p++) {
						if (!in_array($p, $val['num_id'])) {
							$ad_arr['cat_id'][$key]['next'] = $p;
							break;
						}
					}
				}
			}
		}
	}

	return $ad_arr;
}

function getCatList($catId = 0)
{
	$catList = array();
	$where = ' where 1 ';

	if (empty($catId)) {
		$where .= ' and parent_id=0 ';
	}
	else {
		$where .= ' and parent_id= ' . $catId;
	}

	$sql = ' select cat_id,cat_name from ' . $GLOBALS['ecs']->table('category') . $where;
	$catList = $GLOBALS['db']->getAll($sql);
	return $catList;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
include_once ROOT_PATH . 'includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table('ad'), $db, 'ad_id', 'ad_name');
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('action_type', 'ads');

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$adminru = get_admin_ru_id();
$ruCat = '';

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$smarty->assign('menu_select', array('action' => '05_banner', 'current' => 'ad_list'));

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('primary_cat', $_LANG['05_banner']);
	$urlPid = empty($_REQUEST['pid']) ? '' : '&pid=' . trim($_REQUEST['pid']);
	$smarty->assign('ur_here', $_LANG['ad_list']);

	if (!empty($urlPid)) {
		$smarty->assign('action_link', array('text' => $_LANG['ads_add'], 'href' => 'ads.php?act=add' . $urlPid, 'class' => 'icon-plus'));
	}

	$smarty->assign('full_page', 1);
	$where = ' WHERE 1 ';

	if (0 < $adminru['ru_id']) {
		$where .= ' AND is_public = 1 ';
	}

	$sql = 'SELECT position_id, position_name, ad_width, ad_height ' . 'FROM ' . $GLOBALS['ecs']->table('ad_position') . $where;
	$position_list = $GLOBALS['db']->getAll($sql);
	$smarty->assign('position_list', $position_list);
	$ads_list = get_adslist($adminru['ru_id']);
	$page_count_arr = seller_page($ads_list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('current', 'ads');
	$smarty->assign('ads_list', $ads_list['ads']);
	$smarty->assign('filter', $ads_list['filter']);
	$smarty->assign('record_count', $ads_list['record_count']);
	$smarty->assign('page_count', $ads_list['page_count']);
	$smarty->assign('pid', $ads_list['filter']['pid']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($ads_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('ads_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$ads_list = get_adslist($adminru['ru_id']);
	$page_count_arr = seller_page($ads_list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('ads_list', $ads_list['ads']);
	$smarty->assign('filter', $ads_list['filter']);
	$smarty->assign('record_count', $ads_list['record_count']);
	$smarty->assign('page_count', $ads_list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($ads_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$smarty->assign('current', 'ads');
	make_json_result($smarty->fetch('ads_list.dwt'), '', array('filter' => $ads_list['filter'], 'page_count' => $ads_list['page_count']));
}
else if ($_REQUEST['act'] == 'add') {
	admin_priv('ad_manage');
	$smarty->assign('primary_cat', $_LANG['05_banner']);
	$pid = empty($_REQUEST['pid']) ? '' : trim($_REQUEST['pid']);

	if (!empty($pid)) {
		$_SESSION['pid'] = $pid;
		$catFirst = getCatList();
		$smarty->assign('catFirst', $catFirst);
		$ad_model = json_encode(get_ad_model($pid));
		$smarty->assign('ad_model', $ad_model);
	}

	$ad_link = empty($_GET['ad_link']) ? '' : trim($_GET['ad_link']);
	$ad_name = empty($_GET['ad_name']) ? '' : trim($_GET['ad_name']);
	$start_time = local_date($GLOBALS['_CFG']['time_format']);
	$end_time = local_date($GLOBALS['_CFG']['time_format'], gmtime() + 3600 * 24 * 30);
	$smarty->assign('ads', array('ad_link' => $ad_link, 'ad_name' => $ad_name, 'start_time' => $start_time, 'end_time' => $end_time, 'enabled' => 1, 'position_id' => $pid));
	$smarty->assign('ur_here', $_LANG['ads_add']);
	$smarty->assign('action_link', array('href' => 'ads.php?act=list' . '&pid=' . $pid, 'text' => $_LANG['ad_list'], 'class' => 'icon-reply'));
	$smarty->assign('position_list', get_position_list());
	$smarty->assign('form_act', 'insert');
	$smarty->assign('action', 'add');
	$smarty->assign('cfg_lang', $_CFG['lang']);
	set_default_filter(0, 0, $adminru['ru_id']);
	assign_query_info();
	$smarty->assign('current', 'ads');
	$smarty->display('ads_info.dwt');
}
else if ($_REQUEST['act'] == 'insert') {
	admin_priv('ad_manage');
	$id = !empty($_POST['id']) ? intval($_POST['id']) : 0;
	$type = !empty($_POST['type']) ? intval($_POST['type']) : 0;
	$ad_name = !empty($_POST['ad_name']) ? trim($_POST['ad_name']) : '';
	$link_color = !empty($_POST['link_color']) ? trim($_POST['link_color']) : '';
	$is_new = !empty($_POST['is_new']) ? intval($_POST['is_new']) : 0;
	$is_hot = !empty($_POST['is_hot']) ? intval($_POST['is_hot']) : 0;
	$is_best = !empty($_POST['is_best']) ? intval($_POST['is_best']) : 0;
	$ad_type = !empty($_POST['ad_type']) ? intval($_POST['ad_type']) : 0;
	$goods_name = !empty($_POST['goods_name']) ? trim($_POST['goods_name']) : 0;

	if ($_POST['media_type'] == '0') {
		$ad_link = !empty($_POST['ad_link']) ? trim($_POST['ad_link']) : '';
	}
	else {
		$ad_link = !empty($_POST['ad_link2']) ? trim($_POST['ad_link2']) : '';
	}

	$start_time = local_strtotime($_POST['start_time']);
	$end_time = local_strtotime($_POST['end_time']);
	$template = $GLOBALS['_CFG']['template'];
	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('ad') . ' AS a, ' . $ecs->table('ad_position') . ' AS p ' . (' WHERE a.ad_name = \'' . $ad_name . '\' AND a.position_id = p.position_id AND p.theme = \'' . $template . '\'');

	if (0 < $db->getOne($sql)) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['ad_name_exist'], 0, $link);
	}

	if ($_POST['media_type'] == '0') {
		if (isset($_FILES['ad_img']['error']) && $_FILES['ad_img']['error'] == 0 || !isset($_FILES['ad_img']['error']) && isset($_FILES['ad_img']['tmp_name']) && $_FILES['ad_img']['tmp_name'] != 'none') {
			$ad_code = basename($image->upload_image($_FILES['ad_img'], 'afficheimg'));
		}

		if (!empty($_POST['img_url'])) {
			$ad_code = $_POST['img_url'];
		}

		if ((isset($_FILES['ad_img']['error']) && 0 < $_FILES['ad_img']['error'] || !isset($_FILES['ad_img']['error']) && isset($_FILES['ad_img']['tmp_name']) && $_FILES['ad_img']['tmp_name'] == 'none') && empty($_POST['img_url'])) {
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
			sys_msg($_LANG['js_languages']['ad_photo_empty'], 0, $link);
		}
	}
	else if ($_POST['media_type'] == '1') {
		if (isset($_FILES['upfile_flash']['error']) && $_FILES['upfile_flash']['error'] == 0 || !isset($_FILES['upfile_flash']['error']) && isset($_FILES['ad_img']['tmp_name']) && $_FILES['upfile_flash']['tmp_name'] != 'none') {
			if ($_FILES['upfile_flash']['type'] != 'application/x-shockwave-flash') {
				$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
				sys_msg($_LANG['upfile_flash_type'], 0, $link);
			}

			$urlstr = date('Ymd');

			for ($i = 0; $i < 6; $i++) {
				$urlstr .= chr(mt_rand(97, 122));
			}

			$source_file = $_FILES['upfile_flash']['tmp_name'];
			$target = ROOT_PATH . DATA_DIR . '/afficheimg/';
			$file_name = $urlstr . '.swf';

			if (!move_upload_file($source_file, $target . $file_name)) {
				$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
				sys_msg($_LANG['upfile_error'], 0, $link);
			}
			else {
				$ad_code = $file_name;
			}
		}
		else if (!empty($_POST['flash_url'])) {
			if (substr(strtolower($_POST['flash_url']), strlen($_POST['flash_url']) - 4) != '.swf') {
				$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
				sys_msg($_LANG['upfile_flash_type'], 0, $link);
			}

			$ad_code = $_POST['flash_url'];
		}

		if ((isset($_FILES['upfile_flash']['error']) && 0 < $_FILES['upfile_flash']['error'] || !isset($_FILES['upfile_flash']['error']) && isset($_FILES['upfile_flash']['tmp_name']) && $_FILES['upfile_flash']['tmp_name'] == 'none') && empty($_POST['flash_url'])) {
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
			sys_msg($_LANG['js_languages']['ad_flash_empty'], 0, $link);
		}
	}
	else if ($_POST['media_type'] == '2') {
		if (!empty($_POST['ad_code'])) {
			$ad_code = $_POST['ad_code'];
		}
		else {
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
			sys_msg($_LANG['js_languages']['ad_code_empty'], 0, $link);
		}
	}
	else if ($_POST['media_type'] == '3') {
		if (!empty($_POST['ad_text'])) {
			$ad_code = $_POST['ad_text'];
		}
		else {
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
			sys_msg($_LANG['js_languages']['ad_text_empty'], 0, $link);
		}
	}

	get_oss_add_file(array(DATA_DIR . '/afficheimg/' . $ad_code));
	$public_ruid = $adminru['ru_id'];
	$sql = 'INSERT INTO ' . $ecs->table('ad') . (" (position_id,media_type,ad_name,is_new,is_hot,is_best,public_ruid,ad_link,ad_code,start_time,end_time,link_man,link_email,link_phone,click_count,enabled, link_color, ad_type, goods_name)\r\n    VALUES ('" . $_POST['position_id'] . "',\r\n            '" . $_POST['media_type'] . "',\r\n            '" . $ad_name . "',\r\n            '" . $is_new . "',\r\n            '" . $is_hot . "',\r\n            '" . $is_best . "',\r\n            '" . $public_ruid . "',\r\n            '" . $ad_link . "',\r\n            '" . $ad_code . "',\r\n            '" . $start_time . "',\r\n            '" . $end_time . "',\r\n            '" . $_POST['link_man'] . "',\r\n            '" . $_POST['link_email'] . "',\r\n            '" . $_POST['link_phone'] . "',\r\n            '0',\r\n            '1',\r\n            '" . $link_color . "',\r\n            '" . $ad_type . "',\r\n            '" . $goods_name . '\')');
	$db->query($sql);
	admin_log($_POST['ad_name'], 'add', 'ads');
	clear_cache_files();
	$link[1]['text'] = $_LANG['back_ads_list'];
	$link[1]['href'] = 'ads.php?act=list' . '&pid=' . $_POST['position_id'];
	$link[2]['text'] = $_LANG['continue_add_ad'];
	$link[2]['href'] = 'ads.php?act=add' . '&pid=' . $_POST['position_id'];
	sys_msg($_LANG['add'] . '&nbsp;' . $_POST['ad_name'] . '&nbsp;' . $_LANG['attradd_succed'], 0, $link);
}
else if ($_REQUEST['act'] == 'edit') {
	admin_priv('ad_manage');
	$smarty->assign('primary_cat', $_LANG['05_banner']);
	$sql = 'SELECT * FROM ' . $ecs->table('ad') . ' WHERE ad_id=\'' . intval($_REQUEST['id']) . '\'';
	$ads_arr = $db->getRow($sql);
	$pid = empty($ads_arr['position_id']) ? '' : trim($ads_arr['position_id']);
	$tab_menu = array();
	$tab_menu[] = array('curr' => 1, 'text' => $_LANG['ads_edit'], 'href' => 'javascript:;');
	$smarty->assign('tab_menu', $tab_menu);

	if (!empty($pid)) {
		$catFirst = getCatList();
		$smarty->assign('catFirst', $catFirst);
		$ad_model = json_encode(get_ad_model($pid));
		$smarty->assign('ad_model', $ad_model);
	}

	$ads_arr['ad_name'] = htmlspecialchars($ads_arr['ad_name']);
	$ads_arr['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $ads_arr['start_time']);
	$ads_arr['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $ads_arr['end_time']);

	if ($ads_arr['media_type'] == '0') {
		if (strpos($ads_arr['ad_code'], 'http://') === false && strpos($ads_arr['ad_code'], 'https://') === false) {
			$src = '../' . DATA_DIR . '/afficheimg/' . $ads_arr['ad_code'];
			$smarty->assign('img_src', $src);
		}
		else {
			$src = $ads_arr['ad_code'];
			$smarty->assign('url_src', $src);
		}
	}

	if ($ads_arr['media_type'] == '1') {
		if (strpos($ads_arr['ad_code'], 'http://') === false && strpos($ads_arr['ad_code'], 'https://') === false) {
			$src = '../' . DATA_DIR . '/afficheimg/' . $ads_arr['ad_code'];
			$smarty->assign('flash_url', $src);
		}
		else {
			$src = $ads_arr['ad_code'];
			$smarty->assign('flash_url', $src);
		}

		$smarty->assign('src', $src);
	}

	if ($ads_arr['media_type'] == 0) {
		$smarty->assign('media_type', $_LANG['ad_img']);
	}
	else if ($ads_arr['media_type'] == 1) {
		$smarty->assign('media_type', $_LANG['ad_flash']);
	}
	else if ($ads_arr['media_type'] == 2) {
		$smarty->assign('media_type', $_LANG['ad_html']);
	}
	else if ($ads_arr['media_type'] == 3) {
		$smarty->assign('media_type', $_LANG['ad_text']);
	}

	$smarty->assign('ur_here', $_LANG['ads_edit']);
	$smarty->assign('action_link', array('href' => 'ads.php?act=list' . '&pid=' . $pid, 'text' => $_LANG['ad_list'], 'class' => 'icon-reply'));
	$smarty->assign('form_act', 'update');
	$smarty->assign('action', 'edit');
	$smarty->assign('position_list', get_position_list());
	$smarty->assign('ads', $ads_arr);
	set_seller_default_filter(0, 0, $adminru['ru_id']);
	assign_query_info();
	$smarty->assign('current', 'ads');
	$smarty->display('ads_info.dwt');
}
else if ($_REQUEST['act'] == 'update') {
	admin_priv('ad_manage');
	$id = !empty($_POST['id']) ? intval($_POST['id']) : 0;
	$type = !empty($_POST['media_type']) ? intval($_POST['media_type']) : 0;
	$is_new = !empty($_POST['is_new']) ? intval($_POST['is_new']) : 0;
	$is_hot = !empty($_POST['is_hot']) ? intval($_POST['is_hot']) : 0;
	$is_best = !empty($_POST['is_best']) ? intval($_POST['is_best']) : 0;
	$_POST['ad_name'] = !empty($_POST['ad_name']) ? trim($_POST['ad_name']) : '';
	$link_color = !empty($_POST['link_color']) ? trim($_POST['link_color']) : '';
	$ad_type = !empty($_POST['ad_type']) ? intval($_POST['ad_type']) : 0;
	$goods_name = !empty($_POST['goods_name']) ? trim($_POST['goods_name']) : 0;

	if ($_POST['media_type'] == '0') {
		$ad_link = !empty($_POST['ad_link']) ? trim($_POST['ad_link']) : '';
	}
	else {
		$ad_link = !empty($_POST['ad_link2']) ? trim($_POST['ad_link2']) : '';
	}

	$start_time = local_strtotime($_POST['start_time']);
	$end_time = local_strtotime($_POST['end_time']);

	if ($type == 0) {
		if (isset($_FILES['ad_img']['error']) && $_FILES['ad_img']['error'] == 0 || !isset($_FILES['ad_img']['error']) && isset($_FILES['ad_img']['tmp_name']) && $_FILES['ad_img']['tmp_name'] != 'none') {
			$img_up_info = basename($image->upload_image($_FILES['ad_img'], 'afficheimg'));
			$ad_code = 'ad_code = \'' . $img_up_info . '\'' . ',';
		}
		else {
			$ad_code = '';
		}

		if (!empty($_POST['img_url'])) {
			$ad_code = 'ad_code = \'' . $_POST['img_url'] . '\', ';
		}
	}
	else if ($type == 1) {
		if (isset($_FILES['upfile_flash']['error']) && $_FILES['upfile_flash']['error'] == 0 || !isset($_FILES['upfile_flash']['error']) && isset($_FILES['upfile_flash']['tmp_name']) && $_FILES['upfile_flash']['tmp_name'] != 'none') {
			if ($_FILES['upfile_flash']['type'] != 'application/x-shockwave-flash') {
				$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
				sys_msg($_LANG['upfile_flash_type'], 0, $link);
			}

			$urlstr = date('Ymd');

			for ($i = 0; $i < 6; $i++) {
				$urlstr .= chr(mt_rand(97, 122));
			}

			$source_file = $_FILES['upfile_flash']['tmp_name'];
			$target = ROOT_PATH . DATA_DIR . '/afficheimg/';
			$file_name = $urlstr . '.swf';

			if (!move_upload_file($source_file, $target . $file_name)) {
				$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
				sys_msg($_LANG['upfile_error'], 0, $link);
			}
			else {
				$ad_code = 'ad_code = \'' . $file_name . '\', ';
			}
		}
		else if (!empty($_POST['flash_url'])) {
			if (substr(strtolower($_POST['flash_url']), strlen($_POST['flash_url']) - 4) != '.swf') {
				$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
				sys_msg($_LANG['upfile_flash_type'], 0, $link);
			}

			$ad_code = 'ad_code = \'' . $_POST['flash_url'] . '\', ';
		}
		else {
			$ad_code = '';
		}
	}
	else if ($type == 2) {
		$ad_code = 'ad_code = \'' . $_POST['ad_code'] . '\', ';
	}

	if ($type == 3) {
		$ad_code = 'ad_code = \'' . $_POST['ad_text'] . '\', ';
	}

	$template = $GLOBALS['_CFG']['template'];
	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('ad') . ' AS a, ' . $ecs->table('ad_position') . ' AS p ' . (' WHERE a.ad_id <> \'' . $id . '\' AND a.ad_name =\'' . $_POST['ad_name'] . '\' AND a.position_id = p.position_id AND p.theme = \'' . $template . '\'');

	if (0 < $db->getOne($sql)) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'ads.php?act=edit&id=' . $id);
		sys_msg($_LANG['ad_name_exist'], 0, $link);
		exit();
	}

	$ad_code = str_replace('../' . DATA_DIR . '/afficheimg/', '', $ad_code);
	get_oss_add_file(array(DATA_DIR . '/afficheimg/' . $ad_code));
	$sql = 'UPDATE ' . $ecs->table('ad') . ' SET ' . ('position_id = \'' . $_POST['position_id'] . '\', ') . ('ad_name     = \'' . $_POST['ad_name'] . '\', ') . ('ad_link     = \'' . $ad_link . '\', ') . ('link_color  = \'' . $link_color . '\', ') . ('is_new     = \'' . $is_new . '\', ') . ('is_hot     = \'' . $is_hot . '\', ') . ('is_best     = \'' . $is_best . '\', ') . $ad_code . ('start_time  = \'' . $start_time . '\', ') . ('end_time    = \'' . $end_time . '\', ') . ('link_man    = \'' . $_POST['link_man'] . '\', ') . ('link_email  = \'' . $_POST['link_email'] . '\', ') . ('link_phone  = \'' . $_POST['link_phone'] . '\', ') . ('enabled     = \'' . $_POST['enabled'] . '\', ') . ('ad_type  = \'' . $ad_type . '\', ') . ('goods_name  = \'' . $goods_name . '\' ') . ('WHERE ad_id = \'' . $id . '\'');
	$db->query($sql);
	admin_log($_POST['ad_name'], 'edit', 'ads');
	clear_cache_files();
	$href[] = array('text' => $_LANG['back_ads_list'], 'href' => 'ads.php?act=list' . '&pid=' . $_POST['position_id']);
	sys_msg($_LANG['edit'] . ' ' . $_POST['ad_name'] . ' ' . $_LANG['attradd_succed'], 0, $href);
}
else if ($_REQUEST['act'] == 'add_js') {
	admin_priv('ad_manage');
	$smarty->assign('primary_cat', $_LANG['05_banner']);
	$lang_list = array('UTF8' => $_LANG['charset']['utf8'], 'GB2312' => $_LANG['charset']['zh_cn'], 'BIG5' => $_LANG['charset']['zh_tw']);
	$js_code = '<script type=' . '"' . 'text/javascript' . '"';
	$js_code .= ' src=' . '"' . $ecs->seller_url() . 'affiche.php?act=js&type=' . $_REQUEST['type'] . '&ad_id=' . intval($_REQUEST['id']) . '"' . '></script>';
	$site_url = $ecs->seller_url() . 'affiche.php?act=js&type=' . $_REQUEST['type'] . '&ad_id=' . intval($_REQUEST['id']);
	$smarty->assign('ur_here', $_LANG['add_js_code']);
	$smarty->assign('action_link', array('href' => 'ads.php?act=list', 'text' => $_LANG['ad_list']));
	$smarty->assign('url', $site_url);
	$smarty->assign('js_code', $js_code);
	$smarty->assign('lang_list', $lang_list);
	assign_query_info();
	$smarty->assign('current', 'ads');
	$smarty->display('ads_js.dwt');
}
else if ($_REQUEST['act'] == 'edit_ad_name') {
	check_authz_json('ad_manage');
	$id = intval($_POST['id']);
	$ad_name = json_str_iconv(trim($_POST['val']));
	$template = $GLOBALS['_CFG']['template'];
	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('ad') . ' AS a, ' . $ecs->table('ad_position') . ' AS p ' . (' WHERE a.ad_id <> \'' . $id . '\' AND a.ad_name =\'' . $ad_name . '\' AND a.position_id = p.position_id AND p.theme = \'' . $template . '\'');

	if (0 < $db->getOne($sql)) {
		$res = 1;
	}
	else {
		$res = 0;
	}

	if ($res) {
		make_json_error(sprintf($_LANG['ad_name_exist'], $ad_name));
	}
	else if ($exc->edit('ad_name = \'' . $ad_name . '\'', $id)) {
		admin_log($ad_name, 'edit', 'ads');
		make_json_result(stripslashes($ad_name));
	}
	else {
		make_json_error($db->error());
	}
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('ad_manage');
	$id = intval($_GET['id']);
	$img = $exc->get_name($id, 'ad_code');
	$exc->drop($id);
	if (strpos($img, 'http://') === false && strpos($img, 'https://') === false) {
		$img_name = basename($img);
		get_oss_del_file(array(DATA_DIR . '/afficheimg/' . $img_name));
		@unlink(ROOT_PATH . DATA_DIR . '/afficheimg/' . $img_name);
	}

	admin_log('', 'remove', 'ads');
	$url = 'ads.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'getCatList') {
	$catId = empty($_REQUEST['catId']) ? '' : trim($_REQUEST['catId']);
	$catList = getCatList($catId);
	exit(json_encode($catList));
}

?>
