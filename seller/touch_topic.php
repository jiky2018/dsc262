<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function get_topic_list()
{
	$adminru = get_admin_ru_id();
	$ruCat = '';

	if (0 < $adminru['ru_id']) {
		$ruCat = ' where user_id = \'' . $adminru['ru_id'] . '\'';
	}

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

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('touch_topic') . ' AS t ' . (' WHERE ' . $where);
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT t.* FROM ' . $GLOBALS['ecs']->table('touch_topic') . ' AS t ' . (' WHERE ' . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order']);
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
	$href = 'touch_topic.php?act=list';

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

function get_url_image($url)
{
	$ext = strtolower(end(explode('.', $url)));
	if ($ext != 'gif' && $ext != 'jpg' && $ext != 'png' && $ext != 'bmp' && $ext != 'jpeg') {
		return $url;
	}

	$name = date('Ymd');

	for ($i = 0; $i < 6; $i++) {
		$name .= chr(mt_rand(97, 122));
	}

	$name .= '.' . $ext;
	$target = ROOT_PATH . DATA_DIR . '/afficheimg/' . $name;
	$tmp_file = DATA_DIR . '/afficheimg/' . $name;
	$filename = ROOT_PATH . $tmp_file;
	$img = file_get_contents($url);
	$fp = @fopen($filename, 'a');
	fwrite($fp, $img);
	fclose($fp);
	return $tmp_file;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
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
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['09_topic'], 'href' => 'topic.php?act=list');
	$tab_menu[] = array('curr' => 1, 'text' => '手机专题', 'href' => 'touch_topic.php?act=list');
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
	$smarty->assign('action_link', array('text' => $_LANG['topic_add'], 'href' => 'touch_topic.php?act=add'));
	$smarty->display('touch_topic_list.dwt');
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
		$sql = 'SELECT * FROM ' . $ecs->table('touch_topic') . (' WHERE topic_id = \'' . $topic_id . '\'');
		$topic = $db->getRow($sql);
		$topic['start_time'] = local_date('Y-m-d H:i:s', $topic['start_time']);
		$topic['end_time'] = local_date('Y-m-d H:i:s', $topic['end_time']);
		$topic['topic_data'] = array();
		$topic_data = @unserialize($topic['data']);

		if ($topic_data) {
			foreach ($topic_data as $key => $val) {
				foreach ($val as $k => $v) {
					$goods_info = explode('|', $v);
					$topic['topic_data'][$key][$k] = array('value' => $goods_info[1], 'text' => $goods_info[0]);
				}
			}
		}

		create_html_editor('topic_intro', $topic['intro']);
		require ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$topic['data'] = addcslashes($topic['data'], '\'');
		$topic['data'] = $json->encode(@unserialize($topic['data']));
		$topic['data'] = addcslashes($topic['data'], '\'');
		if (empty($topic['topic_img']) && empty($topic['htmls'])) {
			$topic['topic_type'] = 0;
		}
		else if ($topic['htmls'] != '') {
			$topic['topic_type'] = 2;
		}
		else if (preg_match('/.swf$/i', $topic['topic_img'])) {
			$topic['topic_type'] = 1;
		}
		else {
			$topic['topic_type'] = '';
		}

		$smarty->assign('topic', $topic);
		$smarty->assign('act', 'update');
	}
	else {
		$topic = array('title' => '', 'topic_type' => 0, 'url' => 'http://');
		$topic['start_time'] = date('Y-m-d H:i:s', time() + 86400);
		$topic['end_time'] = date('Y-m-d H:i:s', time() + 4 * 86400);
		$smarty->assign('topic', $topic);
		create_html_editor('topic_intro');
		$smarty->assign('act', 'insert');
	}

	$smarty->display('touch_topic_edit.dwt');
}
else {
	if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
		admin_priv('topic_manage');
		$is_insert = $_REQUEST['act'] == 'insert';
		$topic_id = empty($_POST['topic_id']) ? 0 : intval($_POST['topic_id']);
		$topic_type = empty($_POST['topic_type']) ? 0 : intval($_POST['topic_type']);

		switch ($topic_type) {
		case '0':
		case '1':
			if ($_FILES['topic_img']['name'] && 0 < $_FILES['topic_img']['size']) {
				if (!get_file_suffix($_FILES['topic_img']['name'], $allow_suffix)) {
					sys_msg($_LANG['invalid_type']);
				}

				$name = date('Ymd');

				for ($i = 0; $i < 6; $i++) {
					$name .= chr(mt_rand(97, 122));
				}

				$topic_img_ex = explode('.', $_FILES['topic_img']['name']);
				$name .= '.' . end($topic_img_ex);
				$target = ROOT_PATH . DATA_DIR . '/afficheimg/' . $name;

				if (move_upload_file($_FILES['topic_img']['tmp_name'], $target)) {
					$topic_img = DATA_DIR . '/afficheimg/' . $name;
				}
			}
			else if (!empty($_REQUEST['url'])) {
				if (strstr($_REQUEST['url'], 'http') && !strstr($_REQUEST['url'], $_SERVER['SERVER_NAME'])) {
					$topic_img = get_url_image($_REQUEST['url']);
				}
				else {
					sys_msg($_LANG['web_url_no']);
				}
			}

			unset($name);
			unset($target);
			$topic_img = empty($topic_img) ? $_POST['img_url'] : $topic_img;
			$htmls = '';
			break;

		case '2':
			$htmls = $_POST['htmls'];
			$topic_img = '';
			break;
		}

		if ($_FILES['title_pic']['name'] && 0 < $_FILES['title_pic']['size']) {
			if (!get_file_suffix($_FILES['title_pic']['name'], $allow_suffix)) {
				sys_msg($_LANG['invalid_type']);
			}

			$name = date('Ymd');

			for ($i = 0; $i < 6; $i++) {
				$name .= chr(mt_rand(97, 122));
			}

			$title_pic_ex = explode('.', $_FILES['title_pic']['name']);
			$name .= '.' . end($title_pic_ex);
			$target = ROOT_PATH . DATA_DIR . '/afficheimg/' . $name;

			if (move_upload_file($_FILES['title_pic']['tmp_name'], $target)) {
				$title_pic = DATA_DIR . '/afficheimg/' . $name;
			}
		}
		else if (!empty($_REQUEST['title_url'])) {
			if (strstr($_REQUEST['title_url'], 'http') && !strstr($_REQUEST['title_url'], $_SERVER['SERVER_NAME'])) {
				$title_pic = get_url_image($_REQUEST['title_url']);
			}
			else {
				sys_msg($_LANG['web_url_no']);
			}
		}

		unset($name);
		unset($target);
		$title_pic = empty($title_pic) ? $_POST['title_img_url'] : $title_pic;
		require ROOT_PATH . 'includes/cls_json.php';
		$start_time = local_strtotime($_POST['start_time']);
		$end_time = local_strtotime($_POST['end_time']);
		get_oss_add_file(array($topic_img, $title_pic));
		$json = new JSON();
		$tmp_data = $json->decode($_POST['topic_data']);
		$data = serialize($tmp_data);
		$base_style = $_POST['base_style'];
		$keywords = $_POST['keywords'];
		$description = $_POST['description'];
		$record = array('title' => $_POST['topic_name'], 'start_time' => $start_time, 'end_time' => $end_time, 'data' => $data, 'intro' => $_POST['topic_intro'], 'template' => $_POST['topic_template_file'], 'css' => $_POST['topic_css'], 'base_style' => $base_style, 'htmls' => $htmls, 'keywords' => $keywords, 'description' => $description);

		if ($topic_img) {
			$record['topic_img'] = $topic_img;
		}

		if ($title_pic) {
			$record['title_pic'] = $title_pic;
		}

		if ($is_insert) {
			$record['user_id'] = $adminru['ru_id'];
			$db->autoExecute($ecs->table('touch_topic'), $record, 'INSERT');
		}
		else {
			if (isset($_POST['review_status'])) {
				$review_status = !empty($_POST['review_status']) ? intval($_POST['review_status']) : 1;
				$review_content = !empty($_POST['review_content']) ? addslashes(trim($_POST['review_content'])) : '';
				$record['review_status'] = $review_status;
				$record['review_content'] = $review_content;
			}

			$db->autoExecute($ecs->table('touch_topic'), $record, 'UPDATE', 'topic_id = \'' . $topic_id . '\'');
		}

		clear_cache_files();
		$links[] = array('href' => 'touch_topic.php', 'text' => $_LANG['back_list']);
		sys_msg($_LANG['succed'], 0, $links);
	}
	else if ($_REQUEST['act'] == 'get_goods_list') {
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$filters = $json->decode($_GET['JSON']);
		$arr = get_goods_list($filters);
		$opt = array();

		foreach ($arr as $key => $val) {
			$opt[] = array('value' => $val['goods_id'], 'text' => $val['goods_name']);
		}

		make_json_result($opt);
	}
	else if ($_REQUEST['act'] == 'delete') {
		admin_priv('topic_manage');
		get_del_batch($_POST['checkboxes'], intval($_GET['id']), array('topic_img', 'title_pic'), 'topic_id', 'touch_topic', 1);
		$sql = 'DELETE FROM ' . $ecs->table('touch_topic') . ' WHERE ';

		if (!empty($_POST['checkboxes'])) {
			$sql .= db_create_in($_POST['checkboxes'], 'topic_id');
		}
		else if (!empty($_GET['id'])) {
			$_GET['id'] = intval($_GET['id']);
			$sql .= 'topic_id = \'' . $_GET['id'] . '\'';
		}
		else {
			exit();
		}

		$db->query($sql);
		clear_cache_files();

		if (!empty($_REQUEST['is_ajax'])) {
			$url = 'touch_topic.php?act=query&' . str_replace('act=delete', '', $_SERVER['QUERY_STRING']);
			ecs_header('Location: ' . $url . "\n");
			exit();
		}

		$links[] = array('href' => 'touch_topic.php', 'text' => $_LANG['back_list']);
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
		$tpl = 'touch_topic_list.dwt';
		make_json_result($smarty->fetch($tpl), '', array('filter' => $topic_list['filter'], 'page_count' => $topic_list['page_count']));
	}
}

?>
