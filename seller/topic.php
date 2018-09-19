<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function get_topic_list()
{
	$adminru = get_admin_ru_id();
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 't.topic_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);
		$where = '1';
		$where .= !empty($filter['keywords']) ? ' AND t.title like \'%' . mysql_like_quote($filter['keywords']) . '%\'' : '';

		if (0 < $adminru['ru_id']) {
			$where .= ' AND t.user_id = \'' . $adminru['ru_id'] . '\' ';
		}

		if ($filter['review_status']) {
			$where .= ' AND t.review_status = \'' . $filter['review_status'] . '\' ';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('topic') . ' AS t ' . (' WHERE ' . $where);
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT t.* FROM ' . $GLOBALS['ecs']->table('topic') . ' AS t ' . (' WHERE ' . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$query = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$res = array();

	while ($topic = $GLOBALS['db']->fetch_array($query)) {
		$topic['start_time'] = local_date('Y-m-d H:i:s', $topic['start_time']);
		$topic['end_time'] = local_date('Y-m-d H:i:s', $topic['end_time']);
		$topic['url'] = $GLOBALS['ecs']->seller_url() . 'topic.php?topic_id=' . $topic['topic_id'];
		$topic['ru_name'] = get_shop_name($topic['user_id'], 1);
		$res[] = $topic;
	}

	$arr = array('item' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function list_link($is_add = true, $text = '')
{
	$href = 'topic.php?act=list';

	if (!$is_add) {
		$href .= '&' . list_link_postfix();
	}

	if ($text == '') {
		$text = $GLOBALS['_LANG']['topic_list'];
	}

	return array('href' => $href, 'text' => $text, 'class' => 'icon-reply');
}

function get_toppic_width_height()
{
	$width_height = array();
	$file_path = ROOT_PATH . 'themes/' . $GLOBALS['_CFG']['template'] . '/topic.dwt';
	if (!file_exists($file_path) || !is_readable($file_path)) {
		return $width_height;
	}

	$string = file_get_contents($file_path);
	$pattern_width = '/var\\s*topic_width\\s*=\\s*"(\\d+)";/';
	$pattern_height = '/var\\s*topic_height\\s*=\\s*"(\\d+)";/';
	preg_match($pattern_width, $string, $width);
	preg_match($pattern_height, $string, $height);

	if (isset($width[1])) {
		$width_height['pic']['width'] = $width[1];
	}

	if (isset($height[1])) {
		$width_height['pic']['height'] = $height[1];
	}

	unset($width);
	unset($height);
	$pattern_width = '/TitlePicWidth:\\s{1}(\\d+)/';
	$pattern_height = '/TitlePicHeight:\\s{1}(\\d+)/';
	preg_match($pattern_width, $string, $width);
	preg_match($pattern_height, $string, $height);

	if (isset($width[1])) {
		$width_height['title_pic']['width'] = $width[1];
	}

	if (isset($height[1])) {
		$width_height['title_pic']['height'] = $height[1];
	}

	return $width_height;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_visual.php';
$adminru = get_admin_ru_id();
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('action_type', 'bonus');

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$smarty->assign('controller', basename(PHP_SELF, '.php'));
$topic_style_color = array('008080', '008000', 'ffa500', 'ff0000', 'ffff00', '9acd32', 'ffd700');
$allow_suffix = array('gif', 'jpg', 'png', 'jpeg', 'bmp', 'swf');
$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '09_topic'));

if ($_REQUEST['act'] == 'list') {
	admin_priv('topic_manage');
	$smarty->assign('ur_here', $_LANG['09_topic']);
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('full_page', 1);
	$list = get_topic_list();
	$tab_menu = array();
	$tab_menu[] = array('curr' => 1, 'text' => $_LANG['09_topic'], 'href' => 'topic.php?act=list');
	$smarty->assign('tab_menu', $tab_menu);
	$page_count_arr = seller_page($list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('topic_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->assign('action_link', array('text' => $_LANG['topic_add'], 'href' => 'topic.php?act=add', 'class' => 'icon-plus'));
	$smarty->display('topic_list.dwt');
}

if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
	admin_priv('topic_manage');
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('menu_select', array('action' => '02_promotion', 'current' => '09_topic'));
	$isadd = $_REQUEST['act'] == 'add';
	$smarty->assign('isadd', $isadd);
	$topic_id = empty($_REQUEST['topic_id']) ? 0 : intval($_REQUEST['topic_id']);
	include_once ROOT_PATH . 'includes/fckeditor/fckeditor.php';
	$smarty->assign('ur_here', $_LANG['09_topic']);
	$smarty->assign('action_link', list_link($isadd));
	set_default_filter(0, 0, $adminru['ru_id']);
	$smarty->assign('filter_brand_list', search_brand_list());
	$smarty->assign('cfg_lang', $_CFG['lang']);
	$smarty->assign('topic_style_color', $topic_style_color);
	$width_height = get_toppic_width_height();
	if (isset($width_height['pic']['width']) && isset($width_height['pic']['height'])) {
		$smarty->assign('width_height', sprintf($_LANG['tips_width_height'], $width_height['pic']['width'] . 'px', $width_height['pic']['height'] . 'px'));
	}

	if (isset($width_height['title_pic']['width']) && isset($width_height['title_pic']['height'])) {
		$smarty->assign('title_width_height', sprintf($_LANG['tips_title_width_height'], $width_height['title_pic']['width'] . 'px', $width_height['title_pic']['height'] . 'px'));
	}

	if (!$isadd) {
		$sql = 'SELECT * FROM ' . $ecs->table('topic') . (' WHERE topic_id = \'' . $topic_id . '\' LIMIT 1');
		$topic = $db->getRow($sql);
		$topic['start_time'] = local_date('Y-m-d H:i:s', $topic['start_time']);
		$topic['end_time'] = local_date('Y-m-d H:i:s', $topic['end_time']);
		$smarty->assign('topic', $topic);
		$smarty->assign('act', 'update');

		if ($topic['user_id'] != $adminru['ru_id']) {
			$Loaction = 'topic.php?act=list';
			ecs_header('Location: ' . $Loaction . "\n");
			exit();
		}
	}
	else {
		$topic = array('title' => '', 'topic_type' => 0, 'url' => 'http://');
		$topic['start_time'] = date('Y-m-d H:i:s', time() + 86400);
		$topic['end_time'] = date('Y-m-d H:i:s', time() + 4 * 86400);
		$smarty->assign('topic', $topic);
		$smarty->assign('act', 'insert');
	}

	$smarty->display('topic_edit.dwt');
}
else {
	if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
		admin_priv('topic_manage');
		$is_insert = $_REQUEST['act'] == 'insert';
		$topic_id = empty($_POST['topic_id']) ? 0 : intval($_POST['topic_id']);
		$topic_type = empty($_POST['topic_type']) ? 0 : intval($_POST['topic_type']);
		$start_time = local_strtotime($_POST['start_time']);
		$end_time = local_strtotime($_POST['end_time']);
		$keywords = $_POST['keywords'];
		$description = $_POST['description'];
		$record = array('title' => $_POST[topic_name], 'start_time' => $start_time, 'end_time' => $end_time, 'keywords' => $keywords, 'description' => $description);

		if ($is_insert) {
			$record['user_id'] = $adminru['ru_id'];
			$db->AutoExecute($ecs->table('topic'), $record, 'INSERT');
		}
		else {
			$record['review_status'] = 1;
			$db->AutoExecute($ecs->table('topic'), $record, 'UPDATE', 'topic_id = \'' . $topic_id . '\'');
		}

		clear_cache_files();
		$links[] = array('href' => 'topic.php', 'text' => $_LANG['back_list']);
		sys_msg($_LANG['succed'], 0, $links);
	}
	else if ($_REQUEST['act'] == 'visual') {
		$topic_id = !isset($_REQUEST['topic_id']) && empty($_REQUEST['topic_id']) ? 0 : intval($_REQUEST['topic_id']);
		get_down_topictemplates($topic_id, $adminru['ru_id']);
		$arr['tem'] = 'topic_' . $topic_id;
		$des = ROOT_PATH . 'data/topic' . '/topic_' . $adminru['ru_id'] . '/' . $arr['tem'];

		if (file_exists($des . '/temp/pc_page.php')) {
			$filename = $des . '/temp/pc_page.php';
			$is_temp = 1;
		}
		else {
			$filename = $des . '/pc_page.php';
		}

		$arr['out'] = get_html_file($filename);
		$sql = 'SELECT user_id FROM ' . $ecs->table('topic') . (' WHERE topic_id = \'' . $topic_id . '\' LIMIT 1');
		$topic = $db->getRow($sql);

		if ($topic['user_id'] != $adminru['ru_id']) {
			$Loaction = 'topic.php?act=list';
			ecs_header('Location: ' . $Loaction . "\n");
			exit();
		}

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();

			if ($arr['out']) {
				$desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $arr['out']);
				$arr['out'] = $desc_preg['goods_desc'];
			}
		}

		if (defined('THEME_EXTENSION')) {
			$theme_extension = 1;
		}
		else {
			$theme_extension = 0;
		}

		$smarty->assign('theme_extension', $theme_extension);
		$domain = $GLOBALS['ecs']->seller_url();
		$head = getleft_attr('head', $adminru['ru_id'], $arr['tem']);
		$content = getleft_attr('content', $adminru['ru_id'], $arr['tem']);
		$smarty->assign('head', $head);
		$smarty->assign('content', $content);
		$smarty->assign('pc_page', $arr);
		$smarty->assign('domain', $domain);
		$smarty->assign('topic_id', $topic_id);
		$smarty->assign('topic_type', 'topic_type');
		$smarty->assign('vis_section', 'vis_seller_topic');
		$record['review_status'] = 1;
		$db->AutoExecute($ecs->table('topic'), $record, 'UPDATE', 'topic_id = \'' . $topic_id . '\'');
		$smarty->display('visual_editing.dwt');
	}
	else if ($_REQUEST['act'] == 'delete') {
		admin_priv('topic_manage');
		get_del_batch($_POST['checkboxes'], intval($_GET['id']), array('topic_img', 'title_pic'), 'topic_id', 'topic', 1);
		$sql = 'DELETE FROM ' . $ecs->table('topic') . ' WHERE ';

		if (!empty($_POST['checkboxes'])) {
			$is_use = 0;

			foreach ($_POST['checkboxes'] as $v) {
				$sql_v = 'SELECT * FROM ' . $ecs->table('topic') . (' WHERE topic_id = \'' . $v . '\' LIMIT 1');
				$topic = $db->getRow($sql_v);

				if ($topic['user_id'] != $adminru['ru_id']) {
					$is_use = 1;
					break;
				}
			}

			if ($is_use == 0) {
				$sql .= db_create_in($_POST['checkboxes'], 'topic_id');
			}

			foreach ($_POST['checkboxes'] as $v) {
				if (0 < $v) {
					$suffix = 'topic_' . $v;
					$dir = ROOT_PATH . 'data/topic/topic_' . $adminru['ru_id'] . '/' . $suffix;
					$rmdir = del_DirAndFile($dir);
				}
			}
		}
		else if (!empty($_GET['id'])) {
			$_GET['id'] = intval($_GET['id']);
			$sql_v = 'SELECT * FROM ' . $ecs->table('topic') . ' WHERE topic_id = \'' . $_GET['id'] . '\' LIMIT 1';
			$topic = $db->getRow($sql_v);

			if ($topic['user_id'] != $adminru['ru_id']) {
				exit();
			}

			$sql .= 'topic_id = \'' . $_GET['id'] . '\'';
			$suffix = 'topic_' . $_GET['id'];
			$dir = ROOT_PATH . 'data/topic/topic_' . $adminru['ru_id'] . '/' . $suffix;
			$rmdir = del_DirAndFile($dir);
		}
		else {
			exit();
		}

		$db->query($sql);
		clear_cache_files();

		if (!empty($_REQUEST['is_ajax'])) {
			$url = 'topic.php?act=query&' . str_replace('act=delete', '', $_SERVER['QUERY_STRING']);
			ecs_header('Location: ' . $url . "\n");
			exit();
		}

		$links[] = array('href' => 'topic.php', 'text' => $_LANG['back_list']);
		sys_msg($_LANG['succed'], 0, $links);
	}
	else if ($_REQUEST['act'] == 'query') {
		$topic_list = get_topic_list();
		$page_count_arr = seller_page($topic_list, $_REQUEST['page']);
		$smarty->assign('page_count_arr', $page_count_arr);
		$smarty->assign('topic_list', $topic_list['item']);
		$smarty->assign('filter', $topic_list['filter']);
		$smarty->assign('record_count', $topic_list['record_count']);
		$smarty->assign('page_count', $topic_list['page_count']);
		$smarty->assign('use_storage', empty($_CFG['use_storage']) ? 0 : 1);
		$sort_flag = sort_flag($topic_list['filter']);
		$smarty->assign($sort_flag['tag'], $sort_flag['img']);
		$tpl = 'topic_list.dwt';
		make_json_result($smarty->fetch($tpl), '', array('filter' => $topic_list['filter'], 'page_count' => $topic_list['page_count']));
	}
	else if ($_REQUEST['act'] == 'get_hearder_body') {
		require ROOT_PATH . '/includes/cls_json.php';
		$json = new JSON();
		$result = array('error' => '', 'message' => '');
		$smarty->assign('topic_type', 'topic_type');
		$smarty->assign('hearder_body', 1);
		$result['content'] = $GLOBALS['smarty']->fetch('library/pc_page.lbi');
		exit(json_encode($result));
	}
	else if ($_REQUEST['act'] == 'backmodal') {
		require ROOT_PATH . '/includes/cls_json.php';
		$json = new JSON();
		$result = array('error' => '', 'message' => '');
		$code = isset($_REQUEST['suffix']) ? trim($_REQUEST['suffix']) : '';
		$topic_type = isset($_REQUEST['topic_type']) ? trim($_REQUEST['topic_type']) : '';

		if ($topic_type == 'topic_type') {
			$dir = ROOT_PATH . 'data/topic/topic_' . $adminru['ru_id'] . '/' . $code . '/temp';
		}
		else {
			$dir = ROOT_PATH . 'data/seller_templates/seller_tem_' . $adminru['ru_id'] . '/' . $code . '/temp';
		}

		if (!empty($code)) {
			del_DirAndFile($dir);
			$result['error'] = 0;
		}

		exit(json_encode($result));
	}
}

?>
