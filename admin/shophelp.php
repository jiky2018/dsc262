<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_shophelp_list()
{
	$list = array();
	$sql = 'SELECT cat_id, cat_name, sort_order' . ' FROM ' . $GLOBALS['ecs']->table('article_cat') . ' WHERE cat_type = 0 ORDER BY sort_order';
	$res = $GLOBALS['db']->query($sql);

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('article') . ' WHERE cat_id = \'' . $rows['cat_id'] . '\'';
		$rows['num'] = $GLOBALS['db']->getOne($sql);
		$list[] = $rows;
	}

	return $list;
}

function shophelp_article_list($cat_id)
{
	$list = array();
	$sql = 'SELECT article_id, title, article_type , add_time' . ' FROM ' . $GLOBALS['ecs']->table('article') . ' WHERE cat_id = \'' . $cat_id . '\' ORDER BY article_type DESC';
	$res = $GLOBALS['db']->query($sql);

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$rows['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $rows['add_time']);
		$list[] = $rows;
	}

	return $list;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/fckeditor/fckeditor.php';
$exc_article = new exchange($ecs->table('article'), $db, 'article_id', 'title');
$exc_cat = new exchange($ecs->table('article_cat'), $db, 'cat_id', 'cat_name');

if ($_REQUEST['act'] == 'list_cat') {
	$smarty->assign('action_link', array('text' => $_LANG['article_add'], 'href' => 'shophelp.php?act=add'));
	$smarty->assign('ur_here', $_LANG['cat_list']);
	$smarty->assign('full_page', 1);
	$smarty->assign('list', get_shophelp_list());
	assign_query_info();
	$smarty->display('shophelp_cat_list.htm');
}

if ($_REQUEST['act'] == 'list_article') {
	$cat_id = (isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0);
	$smarty->assign('ur_here', $_LANG['article_list']);
	$smarty->assign('action_link', array('text' => $_LANG['article_add'], 'href' => 'shophelp.php?act=add&cat_id=' . $cat_id));
	$smarty->assign('full_page', 1);
	$smarty->assign('cat', article_cat_list($cat_id, true, 'cat_id', 0, 'onchange="location.href=\'?act=list_article&cat_id=\'+this.value"'));
	$smarty->assign('list', shophelp_article_list($cat_id));
	assign_query_info();
	$smarty->display('shophelp_article_list.htm');
}
else if ($_REQUEST['act'] == 'query_art') {
	$cat_id = intval($_GET['cat']);
	$smarty->assign('list', shophelp_article_list($cat_id));
	make_json_result($smarty->fetch('shophelp_article_list.htm'));
}
else if ($_REQUEST['act'] == 'query') {
	$smarty->assign('list', get_shophelp_list());
	make_json_result($smarty->fetch('shophelp_cat_list.htm'));
}

if ($_REQUEST['act'] == 'add') {
	admin_priv('shophelp_manage');
	$cat_id = (isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0);
	create_html_editor('FCKeditor1');

	if (empty($cat_id)) {
		$selected = 0;
	}
	else {
		$selected = $cat_id;
	}

	$cat_list = article_cat_list($selected, true, 'cat_id', 0);
	$cat_list = str_replace('select please', $_LANG['select_plz'], $cat_list);
	$smarty->assign('cat_list', $cat_list);
	$smarty->assign('ur_here', $_LANG['article_add']);
	$smarty->assign('action_link', array('text' => $_LANG['cat_list'], 'href' => 'shophelp.php?act=list_cat'));
	$smarty->assign('form_action', 'insert');
	$smarty->display('shophelp_info.htm');
}

if ($_REQUEST['act'] == 'insert') {
	admin_priv('shophelp_manage');
	$title = (isset($_POST['title']) && !empty($_POST['title']) ? addslashes($_POST['title']) : '');
	$cat_id = (isset($_POST['cat_id']) && !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : 0);
	$article_type = (isset($_POST['article_type']) && !empty($_POST['article_type']) ? intval($_POST['article_type']) : 0);
	$editor = (isset($_POST['FCKeditor1']) && !empty($_POST['FCKeditor1']) ? addslashes($_POST['FCKeditor1']) : '');
	$exc_article->is_only('title', $_POST['title'], $_LANG['title_exist']);
	$add_time = gmtime();
	$sql = 'INSERT INTO ' . $ecs->table('article') . '(title, cat_id, article_type, content, add_time, author) VALUES(\'' . $title . '\', \'' . $cat_id . '\', \'' . $article_type . '\',\'' . $editor . '\',\'' . $add_time . '\', \'_SHOPHELP\' )';
	$db->query($sql);
	$link[0]['text'] = $_LANG['back_list'];
	$link[0]['href'] = 'shophelp.php?act=list_article&cat_id=' . $cat_id;
	$link[1]['text'] = $_LANG['continue_add'];
	$link[1]['href'] = 'shophelp.php?act=add&cat_id=' . $cat_id;
	clear_cache_files();
	admin_log($_POST['title'], 'add', 'shophelp');
	sys_msg($_LANG['articleadd_succeed'], 0, $link);
}

if ($_REQUEST['act'] == 'edit') {
	admin_priv('shophelp_manage');
	$article_id = (isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : 0);
	$sql = 'SELECT article_id,title, cat_id, article_type, is_open, author, author_email, keywords, content FROM ' . $ecs->table('article') . ' WHERE article_id=\'' . $article_id . '\'';
	$article = $db->GetRow($sql);
	create_html_editor('FCKeditor1', $article['content']);
	$smarty->assign('cat_list', article_cat_list($article['cat_id'], true, 'cat_id', 0));
	$smarty->assign('ur_here', $_LANG['article_add']);
	$smarty->assign('action_link', array('text' => $_LANG['article_list'], 'href' => 'shophelp.php?act=list_article&cat_id=' . $article['cat_id']));
	$smarty->assign('article', $article);
	$smarty->assign('form_action', 'update');
	assign_query_info();
	$smarty->display('shophelp_info.htm');
}

if ($_REQUEST['act'] == 'update') {
	admin_priv('shophelp_manage');
	$id = (isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : 0);
	$title = (isset($_POST['title']) && !empty($_POST['title']) ? addslashes($_POST['title']) : '');
	$old_title = (isset($_POST['old_title']) && !empty($_POST['title']) ? addslashes($_POST['old_title']) : '');
	$cat_id = (isset($_POST['cat_id']) && !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : 0);
	$article_type = (isset($_POST['article_type']) && !empty($_POST['article_type']) ? intval($_POST['article_type']) : 0);
	$editor = (isset($_POST['FCKeditor1']) && !empty($_POST['FCKeditor1']) ? addslashes($_POST['FCKeditor1']) : '');

	if ($title != $old_title) {
		$exc_article->is_only('title', $title, $_LANG['articlename_exist'], $id);
	}

	if ($exc_article->edit('title = \'' . $title . '\', cat_id = \'' . $cat_id . '\', article_type = \'' . $article_type . '\', content = \'' . $editor . '\'', $id)) {
		clear_cache_files();
		$link[0]['text'] = $_LANG['back_list'];
		$link[0]['href'] = 'shophelp.php?act=list_article&cat_id=' . $cat_id;
		sys_msg(sprintf($_LANG['articleedit_succeed'], $title), 0, $link);
		admin_log($_POST['title'], 'edit', 'shophelp');
	}
}
else if ($_REQUEST['act'] == 'edit_catname') {
	check_authz_json('shophelp_manage');
	$id = intval($_POST['id']);
	$cat_name = json_str_iconv(trim($_POST['val']));

	if ($exc_cat->num('cat_name', $cat_name, $id) != 0) {
		make_json_error(sprintf($_LANG['catname_exist'], $cat_name));
	}
	else if ($exc_cat->edit('cat_name = \'' . $cat_name . '\'', $id)) {
		clear_cache_files();
		admin_log($cat_name, 'edit', 'shophelpcat');
		make_json_result(stripslashes($cat_name));
	}
	else {
		make_json_error($db->error());
	}
}
else if ($_REQUEST['act'] == 'edit_cat_order') {
	check_authz_json('shophelp_manage');
	$id = intval($_POST['id']);
	$order = json_str_iconv(trim($_POST['val']));

	if (!preg_match('/^[0-9]+$/', $order)) {
		make_json_result('', sprintf($_LANG['enter_int'], $order));
	}
	else if ($exc_cat->edit('sort_order = \'' . $order . '\'', $id)) {
		clear_cache_files();
		make_json_result(stripslashes($order));
	}
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('shophelp_manage');
	$id = intval($_GET['id']);

	if ($exc_article->num('cat_id', $id) != 0) {
		make_json_error(sprintf($_LANG['not_emptycat']));
	}
	else {
		$exc_cat->drop($id);
		clear_cache_files();
		admin_log('', 'remove', 'shophelpcat');
	}

	$url = 'shophelp.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'remove_art') {
	check_authz_json('shophelp_manage');
	$id = intval($_GET['id']);
	$cat_id = $db->getOne('SELECT cat_id FROM ' . $ecs->table('article') . ' WHERE article_id=\'' . $id . '\'');

	if ($exc_article->drop($id)) {
		clear_cache_files();
		admin_log('', 'remove', 'shophelp');
	}
	else {
		make_json_error(sprintf($_LANG['remove_fail']));
	}

	$url = 'shophelp.php?act=query_art&cat=' . $cat_id . '&' . str_replace('act=remove_art', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'add_catname') {
	check_authz_json('shophelp_manage');
	$cat_name = trim($_POST['cat_name']);

	if (!empty($cat_name)) {
		if ($exc_cat->num('cat_name', $cat_name) != 0) {
			make_json_error($_LANG['catname_exist']);
		}
		else {
			$sql = 'INSERT INTO ' . $ecs->table('article_cat') . ' (cat_name, cat_type) VALUES (\'' . $cat_name . '\', 0)';
			$db->query($sql);
			admin_log($cat_name, 'add', 'shophelpcat');
			ecs_header("Location: shophelp.php?act=query\n");
			exit();
		}
	}
	else {
		make_json_error($_LANG['js_languages']['no_catname']);
	}

	ecs_header("Location: shophelp.php?act=list_cat\n");
	exit();
}
else if ($_REQUEST['act'] == 'edit_title') {
	check_authz_json('shophelp_manage');
	$id = intval($_POST['id']);
	$title = json_str_iconv(trim($_POST['val']));

	if ($exc_article->num('title', $title, $id) == 0) {
		if ($exc_article->edit('title = \'' . $title . '\'', $id)) {
			clear_cache_files();
			admin_log($title, 'edit', 'shophelp');
			make_json_result(stripslashes($title));
		}
	}
	else {
		make_json_error(sprintf($_LANG['articlename_exist'], $title));
	}
}

?>
