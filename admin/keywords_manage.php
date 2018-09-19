<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_keywords_list()
{
	$filter = array();
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'addtime' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);
	$sql = 'SELECT *' . ' FROM ' . $GLOBALS['ecs']->table('search_keyword') . ' GROUP BY keyword ' . ' ORDER by addtime DESC';
	$search_keyword = $GLOBALS['db']->getAll($sql);

	if ($search_keyword) {
		$filter['record_count'] = count(array_unique_fb($search_keyword));
	}

	$filter = page_and_size($filter);
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$list = array();

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		if (empty($rows['keyword'])) {
			continue;
		}

		$sql_month = 'select COUNT(*) from ' . $GLOBALS['ecs']->table('search_keyword') . (' where keyword=\'' . $rows['keyword'] . '\' AND date_sub(curdate(), INTERVAL 30 DAY) <= date(addtime)');
		$rows['month_count'] = $GLOBALS['db']->getOne($sql_month);
		$sql_week = 'select COUNT(*) from ' . $GLOBALS['ecs']->table('search_keyword') . (' where keyword=\'' . $rows['keyword'] . '\' AND date_sub(curdate(), INTERVAL 7 DAY) <= date(addtime);');
		$rows['week_count'] = $GLOBALS['db']->getOne($sql_week);
		$sql_day = 'select COUNT(*) from ' . $GLOBALS['ecs']->table('search_keyword') . (' where keyword=\'' . $rows['keyword'] . '\' AND to_days(addtime) = to_days(now())');
		$rows['day_count'] = $GLOBALS['db']->getOne($sql_day);
		$rows['result_count'] = $GLOBALS['db']->getOne('SELECT result_count FROM ' . $GLOBALS['ecs']->table('search_keyword') . (' WHERE keyword=\'' . $rows['keyword'] . '\' ORDER BY keyword_id DESC LIMIT 1'));
		$sql_day = 'select COUNT(*) from ' . $GLOBALS['ecs']->table('search_keyword') . (' where keyword=\'' . $rows['keyword'] . '\'');
		$rows['count'] = $GLOBALS['db']->getOne($sql_day);

		if (is_base64($rows['keyword'])) {
			$rows['keyword'] = base64_decode($rows['keyword']);
		}

		$rows['keyword'] = $rows['keyword'];
		$list[] = $rows;
	}

	if ($list) {
		$lists = array_unique_fb($list);
		$lists = dimensional_array_sort($lists, $filter['sort_by'], $filter['sort_order']);
	}

	return array('list' => $lists, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function array_unique_fb($array2D)
{
	foreach ($array2D as $k => $v) {
		$temp[$v['keyword']] = $v;
	}

	return $temp;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
include_once ROOT_PATH . 'includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table('friend_link'), $db, 'link_id', 'link_name');

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

admin_priv('shop_config');

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('ur_here', $_LANG['list_link']);
	$smarty->assign('full_page', 1);
	$keywords_list = get_keywords_list();
	$smarty->assign('keywords_list', $keywords_list['list']);
	$smarty->assign('filter', $keywords_list['filter']);
	$smarty->assign('record_count', $keywords_list['record_count'] ? $keywords_list['record_count'] : 0);
	$smarty->assign('page_count', $keywords_list['page_count']);
	$sort_flag = sort_flag($links_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('keywords_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$keywords_list = get_keywords_list();
	$smarty->assign('keywords_list', $keywords_list['list']);
	$smarty->assign('filter', $keywords_list['filter']);
	$smarty->assign('record_count', $keywords_list['record_count'] ? $keywords_list['record_count'] : 0);
	$smarty->assign('page_count', $keywords_list['page_count']);
	$sort_flag = sort_flag($keywords_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('keywords_list.dwt'), '', array('filter' => $keywords_list['filter'], 'page_count' => $keywords_list['page_count']));
}
else if ($_REQUEST['act'] == 'add') {
	admin_priv('friendlink');
	$smarty->assign('ur_here', $_LANG['add_link']);
	$smarty->assign('action_link', array('href' => 'friend_link.php?act=list', 'text' => $_LANG['list_link']));
	$smarty->assign('action', 'add');
	$smarty->assign('form_act', 'insert');
	assign_query_info();
	$smarty->display('link_info.dwt');
}
else if ($_REQUEST['act'] == 'insert') {
	$link_logo = '';
	$show_order = !empty($_POST['show_order']) ? intval($_POST['show_order']) : 0;
	$link_name = !empty($_POST['link_name']) ? sub_str(trim($_POST['link_name']), 250, false) : '';

	if ($exc->num('link_name', $link_name) == 0) {
		if (isset($_FILES['link_img']['error']) && $_FILES['link_img']['error'] == 0 || !isset($_FILES['link_img']['error']) && isset($_FILES['link_img']['tmp_name']) && $_FILES['link_img']['tmp_name'] != 'none') {
			$img_up_info = @basename($image->upload_image($_FILES['link_img'], 'afficheimg'));
			$link_logo = DATA_DIR . '/afficheimg/' . $img_up_info;
		}

		if (!empty($_POST['url_logo'])) {
			if (strpos($_POST['url_logo'], 'http://') === false && strpos($_POST['url_logo'], 'https://') === false) {
				$link_logo = 'http://' . trim($_POST['url_logo']);
			}
			else {
				$link_logo = trim($_POST['url_logo']);
			}
		}

		if ((isset($_FILES['upfile_flash']['error']) && 0 < $_FILES['upfile_flash']['error'] || !isset($_FILES['upfile_flash']['error']) && isset($_FILES['upfile_flash']['tmp_name']) && $_FILES['upfile_flash']['tmp_name'] == 'none') && empty($_POST['url_logo'])) {
			$link_logo = '';
		}

		if (strpos($_POST['link_url'], 'http://') === false && strpos($_POST['link_url'], 'https://') === false) {
			$link_url = 'http://' . trim($_POST['link_url']);
		}
		else {
			$link_url = trim($_POST['link_url']);
		}

		$sql = 'INSERT INTO ' . $ecs->table('friend_link') . ' (link_name, link_url, link_logo, show_order) ' . ('VALUES (\'' . $link_name . '\', \'' . $link_url . '\', \'' . $link_logo . '\', \'' . $show_order . '\')');
		$db->query($sql);
		admin_log($_POST['link_name'], 'add', 'friendlink');
		clear_cache_files();
		$link[0]['text'] = $_LANG['continue_add'];
		$link[0]['href'] = 'friend_link.php?act=add';
		$link[1]['text'] = $_LANG['back_list'];
		$link[1]['href'] = 'friend_link.php?act=list';
		sys_msg($_LANG['add'] . '&nbsp;' . stripcslashes($_POST['link_name']) . ' ' . $_LANG['attradd_succed'], 0, $link);
	}
	else {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['link_name_exist'], 0, $link);
	}
}
else if ($_REQUEST['act'] == 'edit') {
	admin_priv('friendlink');
	$sql = 'SELECT link_id, link_name, link_url, link_logo, show_order ' . 'FROM ' . $ecs->table('friend_link') . ' WHERE link_id = \'' . intval($_REQUEST['id']) . '\'';
	$link_arr = $db->getRow($sql);

	if (!empty($link_arr['link_logo'])) {
		$type = 'img';
		$link_logo = $link_arr['link_logo'];
	}
	else {
		$type = 'chara';
		$link_logo = '';
	}

	$link_arr['link_name'] = sub_str($link_arr['link_name'], 250, false);
	$smarty->assign('ur_here', $_LANG['edit_link']);
	$smarty->assign('action_link', array('href' => 'friend_link.php?act=list&' . list_link_postfix(), 'text' => $_LANG['list_link']));
	$smarty->assign('form_act', 'update');
	$smarty->assign('action', 'edit');
	$smarty->assign('type', $type);
	$smarty->assign('link_logo', $link_logo);
	$smarty->assign('link_arr', $link_arr);
	assign_query_info();
	$smarty->display('link_info.dwt');
}
else if ($_REQUEST['act'] == 'update') {
	$id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$show_order = !empty($_POST['show_order']) ? intval($_POST['show_order']) : 0;
	$link_name = !empty($_POST['link_name']) ? trim($_POST['link_name']) : '';
	if (isset($_FILES['link_img']['error']) && $_FILES['link_img']['error'] == 0 || !isset($_FILES['link_img']['error']) && isset($_FILES['link_img']['tmp_name']) && $_FILES['link_img']['tmp_name'] != 'none') {
		$img_up_info = @basename($image->upload_image($_FILES['link_img'], 'afficheimg'));
		$link_logo = ', link_logo = ' . '\'' . DATA_DIR . '/afficheimg/' . $img_up_info . '\'';
	}
	else if (!empty($_POST['url_logo'])) {
		$link_logo = ', link_logo = \'' . $_POST['url_logo'] . '\'';
	}
	else {
		$link_logo = ', link_logo = \'\'';
	}

	if (!empty($img_up_info)) {
		$old_logo = $db->getOne('SELECT link_logo FROM ' . $ecs->table('friend_link') . (' WHERE link_id = \'' . $id . '\''));
		if (strpos($old_logo, 'http://') === false && strpos($old_logo, 'https://') === false) {
			$img_name = basename($old_logo);
			@unlink(ROOT_PATH . DATA_DIR . '/afficheimg/' . $img_name);
		}
	}

	if (strpos($_POST['link_url'], 'http://') === false && strpos($_POST['link_url'], 'https://') === false) {
		$link_url = 'http://' . trim($_POST['link_url']);
	}
	else {
		$link_url = trim($_POST['link_url']);
	}

	$sql = 'UPDATE ' . $ecs->table('friend_link') . ' SET ' . ('link_name = \'' . $link_name . '\', ') . ('link_url = \'' . $link_url . '\' ') . $link_logo . ',' . ('show_order = \'' . $show_order . '\' ') . ('WHERE link_id = \'' . $id . '\'');
	$db->query($sql);
	admin_log($_POST['link_name'], 'edit', 'friendlink');
	clear_cache_files();
	$link[0]['text'] = $_LANG['back_list'];
	$link[0]['href'] = 'friend_link.php?act=list&' . list_link_postfix();
	sys_msg($_LANG['edit'] . '&nbsp;' . stripcslashes($_POST['link_name']) . '&nbsp;' . $_LANG['attradd_succed'], 0, $link);
}
else if ($_REQUEST['act'] == 'edit_link_name') {
	check_authz_json('friendlink');
	$id = intval($_POST['id']);
	$link_name = json_str_iconv(trim($_POST['val']));

	if ($exc->num('link_name', $link_name, $id) != 0) {
		make_json_error(sprintf($_LANG['link_name_exist'], $link_name));
	}
	else if ($exc->edit('link_name = \'' . $link_name . '\'', $id)) {
		admin_log($link_name, 'edit', 'friendlink');
		clear_cache_files();
		make_json_result(stripslashes($link_name));
	}
	else {
		make_json_error($db->error());
	}
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('friendlink');
	$id = intval($_GET['id']);
	$link_logo = $exc->get_name($id, 'link_logo');
	if (strpos($link_logo, 'http://') === false && strpos($link_logo, 'https://') === false) {
		$img_name = basename($link_logo);
		@unlink(ROOT_PATH . DATA_DIR . '/afficheimg/' . $img_name);
	}

	$exc->drop($id);
	clear_cache_files();
	admin_log('', 'remove', 'friendlink');
	$url = 'friend_link.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'edit_show_order') {
	check_authz_json('friendlink');
	$id = intval($_POST['id']);
	$order = json_str_iconv(trim($_POST['val']));

	if (!preg_match('/^[0-9]+$/', $order)) {
		make_json_error(sprintf($_LANG['enter_int'], $order));
	}
	else if ($exc->edit('show_order = \'' . $order . '\'', $id)) {
		clear_cache_files();
		make_json_result(stripslashes($order));
	}
}

?>
