<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function drop_link_goods($goods_id, $article_id)
{
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('goods_article') . (' WHERE goods_id = \'' . $goods_id . '\' AND article_id = \'' . $article_id . '\' LIMIT 1');
	$GLOBALS['db']->query($sql);
	create_result(true, '', $goods_id);
}

function get_article_goods($article_id)
{
	$list = array();
	$sql = 'SELECT g.goods_id, g.goods_name' . ' FROM ' . $GLOBALS['ecs']->table('goods_article') . ' AS ga' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = ga.goods_id' . (' WHERE ga.article_id = \'' . $article_id . '\'');
	$list = $GLOBALS['db']->getAll($sql);
	return $list;
}

function get_articleslist()
{
	$result = get_filter();

	if ($result === false) {
		$filter = array();
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['cat_id'] = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'a.article_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$where = '';

		if (!empty($filter['keyword'])) {
			$where = ' AND a.title LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\'';
		}

		if ($filter['cat_id']) {
			$where .= ' AND a.' . get_article_children($filter['cat_id']);
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('article') . ' AS a ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('article_cat') . ' AS ac ON ac.cat_id = a.cat_id ' . 'WHERE 1 ' . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT a.* , ac.cat_name ' . 'FROM ' . $GLOBALS['ecs']->table('article') . ' AS a ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('article_cat') . ' AS ac ON ac.cat_id = a.cat_id ' . 'WHERE 1 ' . $where . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
		$filter['keyword'] = stripslashes($filter['keyword']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$arr = array();
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$rows['date'] = local_date($GLOBALS['_CFG']['time_format'], $rows['add_time']);
		$arr[] = $rows;
	}

	return array('arr' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function upload_article_file($upload)
{
	if (!make_dir('../' . DATA_DIR . '/article')) {
		return false;
	}

	$filename = cls_image::random_filename() . substr($upload['name'], strpos($upload['name'], '.'));
	$path = ROOT_PATH . DATA_DIR . '/article/' . $filename;

	if (move_upload_file($upload['tmp_name'], $path)) {
		return DATA_DIR . '/article/' . $filename;
	}
	else {
		return false;
	}
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/fckeditor/fckeditor.php';
require_once ROOT_PATH . 'includes/cls_image.php';
$exc = new exchange($ecs->table('article'), $db, 'article_id', 'title');
$allow_file_types = '|GIF|JPG|PNG|BMP|SWF|DOC|XLS|PPT|MID|WAV|ZIP|RAR|PDF|CHM|RM|TXT|';

if ($_REQUEST['act'] == 'list') {
	$cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
	$filter = array();
	$smarty->assign('cat_select', article_cat_list_new(0));
	$smarty->assign('ur_here', $_LANG['03_article_list']);
	$smarty->assign('action_link', array('text' => $_LANG['article_add'], 'href' => 'article.php?act=add'));

	if (0 < $cat_id) {
		$sql = 'SELECT parent_id FROM' . $ecs->table('article_cat') . (' WHERE cat_id = \'' . $cat_id . '\'');
		$parent_id = $db->getOne($sql);
		$back_url = 'articlecat.php?act=list&cat_id=' . $parent_id;
		$smarty->assign('back_url', $back_url);
	}

	$smarty->assign('full_page', 1);
	$smarty->assign('filter', $filter);
	$article_list = get_articleslist();
	$smarty->assign('article_list', $article_list['arr']);
	$smarty->assign('filter', $article_list['filter']);
	$smarty->assign('record_count', $article_list['record_count']);
	$smarty->assign('page_count', $article_list['page_count']);
	$sort_flag = sort_flag($article_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('article_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	check_authz_json('article_manage');
	$article_list = get_articleslist();
	$smarty->assign('article_list', $article_list['arr']);
	$smarty->assign('filter', $article_list['filter']);
	$smarty->assign('record_count', $article_list['record_count']);
	$smarty->assign('page_count', $article_list['page_count']);
	$sort_flag = sort_flag($article_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('article_list.dwt'), '', array('filter' => $article_list['filter'], 'page_count' => $article_list['page_count']));
}

if ($_REQUEST['act'] == 'add') {
	admin_priv('article_manage');
	create_html_editor('FCKeditor1');
	$article = array();
	$article['is_open'] = 1;
	set_default_filter();
	$sql = 'DELETE FROM ' . $ecs->table('goods_article') . ' WHERE article_id = 0';
	$db->query($sql);
	$smarty->assign('filter_category_list', get_category_list());

	if (isset($_GET['id'])) {
		$smarty->assign('cur_id', $_GET['id']);
	}

	$smarty->assign('article', $article);
	$smarty->assign('cat_select', article_cat_list_new(0));
	$smarty->assign('ur_here', $_LANG['article_add']);
	$smarty->assign('action_link', array('text' => $_LANG['03_article_list'], 'href' => 'article.php?act=list'));
	$smarty->assign('form_action', 'insert');
	assign_query_info();
	$smarty->display('article_info.dwt');
}

if ($_REQUEST['act'] == 'insert') {
	admin_priv('article_manage');
	$target_select = !empty($_REQUEST['target_select']) ? $_REQUEST['target_select'] : array();
	$is_only = $exc->is_only('title', $_POST['title'], 0, ' cat_id =\'' . $_POST['article_cat'] . '\'');

	if (!$is_only) {
		sys_msg(sprintf($_LANG['title_exist'], stripslashes($_POST['title'])), 1);
	}

	$file_url = '';
	if (isset($_FILES['file']['error']) && $_FILES['file']['error'] == 0 || !isset($_FILES['file']['error']) && isset($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'] != 'none') {
		if (!check_file_type($_FILES['file']['tmp_name'], $_FILES['file']['name'], $allow_file_types)) {
			sys_msg($_LANG['invalid_file']);
		}

		$res = upload_article_file($_FILES['file']);

		if ($res != false) {
			$file_url = $res;
		}
	}

	if ($file_url == '') {
		$file_url = $_POST['file_url'];
	}
	else {
		get_oss_add_file(array($file_url));
	}

	if ($file_url == '') {
		$open_type = 0;
	}
	else {
		$open_type = $_POST['FCKeditor1'] == '' ? 1 : 2;
	}

	$add_time = gmtime();

	if (empty($_POST['cat_id'])) {
		$_POST['cat_id'] = 0;
	}

	$sort_order = !empty($_REQUEST['sort_order']) ? intval($_REQUEST['sort_order']) : 50;
	$sql = 'INSERT INTO ' . $ecs->table('article') . '(title, cat_id, article_type, is_open, author, ' . 'author_email, keywords, content, add_time, file_url, open_type, link, description,sort_order) ' . ('VALUES (\'' . $_POST['title'] . '\', \'' . $_POST['article_cat'] . '\', \'' . $_POST['article_type'] . '\', \'' . $_POST['is_open'] . '\', ') . ('\'' . $_POST['author'] . '\', \'' . $_POST['author_email'] . '\', \'' . $_POST['keywords'] . '\', \'' . $_POST['FCKeditor1'] . '\', ') . ('\'' . $add_time . '\', \'' . $file_url . '\', \'' . $open_type . '\', \'' . $_POST['link_url'] . '\', \'' . $_POST['description'] . '\',\'' . $sort_order . '\')');
	$db->query($sql);
	$article_id = $db->insert_id();

	if (!empty($target_select)) {
		foreach ($target_select as $k => $val) {
			$sql = 'INSERT INTO ' . $ecs->table('goods_article') . ' (goods_id, article_id) ' . ('VALUES (\'' . $val . '\', \'' . $article_id . '\')');
			$db->query($sql);
		}
	}

	$link[0]['text'] = $_LANG['continue_add'];
	$link[0]['href'] = 'article.php?act=add';
	$link[1]['text'] = $_LANG['back_list'];
	$link[1]['href'] = 'article.php?act=list';
	admin_log($_POST['title'], 'add', 'article');
	clear_cache_files();
	sys_msg($_LANG['articleadd_succeed'], 0, $link);
}

if ($_REQUEST['act'] == 'edit') {
	admin_priv('article_manage');
	$sql = 'SELECT * FROM ' . $ecs->table('article') . (' WHERE article_id=\'' . $_REQUEST['id'] . '\'');
	$article = $db->GetRow($sql);
	$article['file_url'] = get_image_path(0, $article['file_url']);

	if ($GLOBALS['_CFG']['open_oss'] == 1) {
		$bucket_info = get_bucket_info();

		if ($article['content']) {
			$desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $article['content']);
			$article['content'] = $desc_preg['goods_desc'];
		}
	}

	create_html_editor('FCKeditor1', $article['content']);
	$goods_list = get_article_goods($_REQUEST['id']);
	$smarty->assign('goods_list', $goods_list);

	if (0 < $article['cat_id']) {
		$cat_name = $db->getOne('SELECT cat_name FROM' . $ecs->table('article_cat') . ' WHERE cat_id = \'' . $article['cat_id'] . '\'');
		$smarty->assign('cat_name', $cat_name);
	}

	$smarty->assign('article', $article);
	$smarty->assign('cat_select', article_cat_list_new(0));
	$smarty->assign('ur_here', $_LANG['article_edit']);
	$smarty->assign('action_link', array('text' => $_LANG['03_article_list'], 'href' => 'article.php?act=list&' . list_link_postfix()));
	$smarty->assign('form_action', 'update');
	set_default_filter();
	assign_query_info();
	$smarty->display('article_info.dwt');
}

if ($_REQUEST['act'] == 'update') {
	admin_priv('article_manage');
	$_POST['id'] = isset($_POST['id']) ? intval($_POST['id']) : 0;
	$target_select = !empty($_REQUEST['target_select']) ? $_REQUEST['target_select'] : array();
	$is_only = $exc->is_only('title', $_POST['title'], $_POST['id'], 'cat_id = \'' . $_POST['article_cat'] . '\'');

	if (!$is_only) {
		sys_msg(sprintf($_LANG['title_exist'], stripslashes($_POST['title'])), 1);
	}

	if (empty($_POST['cat_id'])) {
		$_POST['cat_id'] = 0;
	}

	$file_url = '';
	if (empty($_FILES['file']['error']) || !isset($_FILES['file']['error']) && isset($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'] != 'none') {
		if (!check_file_type($_FILES['file']['tmp_name'], $_FILES['file']['name'], $allow_file_types)) {
			sys_msg($_LANG['invalid_file']);
		}

		$res = upload_article_file($_FILES['file']);

		if ($res != false) {
			$file_url = $res;
		}
	}

	if ($file_url == '') {
		$file_url = $_POST['file_url'];
	}
	else {
		get_oss_add_file(array($file_url));
	}

	if ($file_url == '') {
		$open_type = 0;
	}
	else {
		$open_type = $_POST['FCKeditor1'] == '' ? 1 : 2;
	}

	$sql = 'SELECT file_url FROM ' . $ecs->table('article') . (' WHERE article_id = \'' . $_POST['id'] . '\'');
	$old_url = $db->getOne($sql);
	if ($file_url != '' && $old_url != '' && $old_url != $file_url && strpos($old_url, 'http://') === false && strpos($old_url, 'https://') === false) {
		@unlink(ROOT_PATH . $old_url);
	}

	if ($file_url == '') {
		$file_url = $old_url;
	}

	$sort_order = !empty($_REQUEST['sort_order']) ? intval($_REQUEST['sort_order']) : 50;

	if ($exc->edit('title=\'' . $_POST['title'] . '\', cat_id=\'' . $_POST['article_cat'] . '\', article_type=\'' . $_POST['article_type'] . '\', is_open=\'' . $_POST['is_open'] . '\', author=\'' . $_POST['author'] . '\', author_email=\'' . $_POST['author_email'] . '\', keywords =\'' . $_POST['keywords'] . '\', file_url =\'' . $file_url . '\', open_type=\'' . $open_type . '\', content=\'' . $_POST['FCKeditor1'] . '\', link=\'' . $_POST['link_url'] . '\', description = \'' . $_POST['description'] . '\',sort_order = \'' . $sort_order . '\' ', $_POST['id'])) {
		$sql = 'SELECT goods_id FROM' . $ecs->table('goods_article') . ' WHERE article_id = \'' . $_POST['id'] . '\'';
		$goods_id = $db->getAll($sql);

		if (!empty($target_select)) {
			$goods_ids = arr_foreach($goods_id);

			foreach ($target_select as $k => $val) {
				if (!in_array($val, $goods_ids)) {
					$sql = 'INSERT INTO ' . $ecs->table('goods_article') . ' (goods_id, article_id) ' . ('VALUES (\'' . $val . '\', \'') . $_POST['id'] . '\')';
					$db->query($sql);
				}
			}

			$sql = 'DELETE FROM' . $ecs->table('goods_article') . ' WHERE article_id = \'' . $_POST['id'] . '\' AND goods_id NOT ' . db_create_in($target_select);
			$db->query($sql);
		}

		$link[0]['text'] = $_LANG['back_list'];
		$link[0]['href'] = 'article.php?act=list&' . list_link_postfix();
		$link[1]['text'] = '返回分类列表';
		$link[1]['href'] = 'article.php?act=list&cat_id=' . $_POST['article_cat'];
		$note = sprintf($_LANG['articleedit_succeed'], stripslashes($_POST['title']));
		admin_log($_POST['title'], 'edit', 'article');
		clear_cache_files();
		sys_msg($note, 0, $link);
	}
	else {
		exit($db->error());
	}
}
else if ($_REQUEST['act'] == 'toggle_show') {
	check_authz_json('article_manage');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	$exc->edit('is_open = \'' . $val . '\'', $id);
	clear_cache_files();
	make_json_result($val);
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('article_manage');
	$id = intval($_GET['id']);
	$sql = 'SELECT file_url FROM ' . $ecs->table('article') . (' WHERE article_id = \'' . $id . '\'');
	$old_url = $db->getOne($sql);
	if ($old_url != '' && strpos($old_url, 'http://') === false && strpos($old_url, 'https://') === false) {
		get_oss_del_file(array($old_url));
		@unlink(ROOT_PATH . $old_url);
	}

	$name = $exc->get_name($id);

	if ($exc->drop($id)) {
		$db->query('DELETE FROM ' . $ecs->table('comment') . ' WHERE ' . ('comment_type = 1 AND id_value = ' . $id));
		admin_log(addslashes($name), 'remove', 'article');
		clear_cache_files();
	}

	$url = 'article.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'del_file') {
	$article_id = isset($_GET['article_id']) ? intval($_GET['article_id']) : 0;
	$sql = 'SELECT file_url FROM ' . $ecs->table('article') . (' WHERE article_id = \'' . $article_id . '\'');
	$old_url = $db->getOne($sql);
	if ($old_url != '' && strpos($old_url, 'http://') === false && strpos($old_url, 'https://') === false) {
		@unlink(ROOT_PATH . $old_url);
	}

	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('article') . (' SET file_url = \'\' WHERE article_id = \'' . $article_id . '\'');
	$GLOBALS['db']->query($sql);
	clear_all_files();
	$links[] = array('text' => '返回编辑文章内容', 'href' => 'article.php?act=edit&id=' . $article_id);
	sys_msg('删除上传文件成功！', 0, $links);
}
else if ($_REQUEST['act'] == 'add_link_goods') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	check_authz_json('article_manage');
	$add_ids = $json->decode($_GET['add_ids']);
	$args = $json->decode($_GET['JSON']);
	$article_id = $args[0];

	if ($article_id == 0) {
		$article_id = $db->getOne('SELECT MAX(article_id)+1 AS article_id FROM ' . $ecs->table('article'));
	}

	foreach ($add_ids as $key => $val) {
		$sql = 'INSERT INTO ' . $ecs->table('goods_article') . ' (goods_id, article_id) ' . ('VALUES (\'' . $val . '\', \'' . $article_id . '\')');
		$db->query($sql, 'SILENT') || make_json_error($db->error());
	}

	$arr = get_article_goods($article_id);
	$opt = array();

	foreach ($arr as $key => $val) {
		$opt[] = array('value' => $val['goods_id'], 'text' => $val['goods_name'], 'data' => '');
	}

	make_json_result($opt);
}
else if ($_REQUEST['act'] == 'drop_link_goods') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	check_authz_json('article_manage');
	$drop_goods = $json->decode($_GET['drop_ids']);
	$arguments = $json->decode($_GET['JSON']);
	$article_id = $arguments[0];

	if ($article_id == 0) {
		$article_id = $db->getOne('SELECT MAX(article_id)+1 AS article_id FROM ' . $ecs->table('article'));
	}

	$sql = 'DELETE FROM ' . $ecs->table('goods_article') . (' WHERE article_id = \'' . $article_id . '\' AND goods_id ') . db_create_in($drop_goods);
	$db->query($sql, 'SILENT') || make_json_error($db->error());
	$arr = get_article_goods($article_id);
	$opt = array();

	foreach ($arr as $key => $val) {
		$opt[] = array('value' => $val['goods_id'], 'text' => $val['goods_name'], 'data' => '');
	}

	make_json_result($opt);
}

if ($_REQUEST['act'] == 'get_goods_list') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$filters = $json->decode($_GET['JSON']);
	$arr = get_goods_list($filters);
	$opt = array();

	foreach ($arr as $key => $val) {
		$opt[] = array('value' => $val['goods_id'], 'text' => $val['goods_name'], 'data' => $val['shop_price']);
	}

	make_json_result($opt);
}
else if ($_REQUEST['act'] == 'batch') {
	if (isset($_POST['type'])) {
		if ($_POST['type'] == 'button_remove') {
			admin_priv('article_manage');
			if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
				sys_msg($_LANG['no_select_article'], 1);
			}

			$sql = 'SELECT file_url FROM ' . $ecs->table('article') . ' WHERE article_id ' . db_create_in(join(',', $_POST['checkboxes'])) . ' AND file_url <> \'\'';
			$res = $db->query($sql);

			while ($row = $db->fetchRow($res)) {
				$old_url = $row['file_url'];
				if (strpos($old_url, 'http://') === false && strpos($old_url, 'https://') === false) {
					get_oss_del_file(array($old_url));
					@unlink(ROOT_PATH . $old_url);
				}
			}

			foreach ($_POST['checkboxes'] as $key => $id) {
				if ($exc->drop($id)) {
					$name = $exc->get_name($id);
					admin_log(addslashes($name), 'remove', 'article');
				}
			}
		}

		if ($_POST['type'] == 'button_hide') {
			check_authz_json('article_manage');
			if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
				sys_msg($_LANG['no_select_article'], 1);
			}

			foreach ($_POST['checkboxes'] as $key => $id) {
				$exc->edit('is_open = \'0\'', $id);
			}
		}

		if ($_POST['type'] == 'button_show') {
			check_authz_json('article_manage');
			if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
				sys_msg($_LANG['no_select_article'], 1);
			}

			foreach ($_POST['checkboxes'] as $key => $id) {
				$exc->edit('is_open = \'1\'', $id);
			}
		}

		if ($_POST['type'] == 'move_to') {
			check_authz_json('article_manage');
			if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
				sys_msg($_LANG['no_select_article'], 1);
			}

			if (!$_POST['target_cat']) {
				sys_msg($_LANG['no_select_act'], 1);
			}

			foreach ($_POST['checkboxes'] as $key => $id) {
				$exc->edit('cat_id = \'' . $_POST['target_cat'] . '\'', $id);
			}
		}
	}

	clear_cache_files();
	$lnk[] = array('text' => $_LANG['back_list'], 'href' => 'article.php?act=list');
	sys_msg($_LANG['batch_handle_ok'], 0, $lnk);
}
else if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('article_manage');
	$id = intval($_POST['id']);
	$order = json_str_iconv(trim($_POST['val']));

	if ($exc->edit('sort_order = \'' . $order . '\'', $id)) {
		clear_cache_files();
		make_json_result(stripslashes($order));
	}
	else {
		make_json_error($db->error());
	}
}

?>
