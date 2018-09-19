<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function shopinfo_article_list()
{
	$list = array();
	$sql = 'SELECT article_id, title ,add_time' . ' FROM ' . $GLOBALS['ecs']->table('article') . ' WHERE cat_id = 0 ORDER BY article_id';
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
$exc = new exchange($ecs->table('article'), $db, 'article_id', 'title');

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('ur_here', $_LANG['shop_info']);
	$smarty->assign('action_link', array('text' => $_LANG['shopinfo_add'], 'href' => 'shopinfo.php?act=add'));
	$smarty->assign('full_page', 1);
	$smarty->assign('list', shopinfo_article_list());
	assign_query_info();
	$smarty->display('shopinfo_list.htm');
}
else if ($_REQUEST['act'] == 'query') {
	$smarty->assign('list', shopinfo_article_list());
	make_json_result($smarty->fetch('shopinfo_list.htm'));
}

if ($_REQUEST['act'] == 'add') {
	admin_priv('shopinfo_manage');
	create_html_editor('FCKeditor1');
	$article['article_type'] = 0;
	$smarty->assign('ur_here', $_LANG['shopinfo_add']);
	$smarty->assign('action_link', array('text' => $_LANG['shopinfo_list'], 'href' => 'shopinfo.php?act=list'));
	$smarty->assign('form_action', 'insert');
	assign_query_info();
	$smarty->display('shopinfo_info.htm');
}

if ($_REQUEST['act'] == 'insert') {
	admin_priv('shopinfo_manage');
	$title = (isset($_POST['title']) && !empty($_POST['title']) ? addslashes($_POST['title']) : '');
	$editor = (isset($_POST['FCKeditor1']) && !empty($_POST['FCKeditor1']) ? addslashes($_POST['FCKeditor1']) : '');
	$is_only = $exc->is_only('title', $title);

	if (!$is_only) {
		sys_msg(sprintf($_LANG['title_exist'], stripslashes($title)), 1);
	}

	$add_time = gmtime();
	$sql = 'INSERT INTO ' . $ecs->table('article') . '(title, cat_id, content, add_time) VALUES(\'' . $title . '\', \'0\', \'' . $editor . '\',\'' . $add_time . '\' )';
	$db->query($sql);
	$link[0]['text'] = $_LANG['continue_add'];
	$link[0]['href'] = 'shopinfo.php?act=add';
	$link[1]['text'] = $_LANG['back_list'];
	$link[1]['href'] = 'shopinfo.php?act=list';
	clear_cache_files();
	admin_log($_POST['title'], 'add', 'shopinfo');
	sys_msg($_LANG['articleadd_succeed'], 0, $link);
}

if ($_REQUEST['act'] == 'edit') {
	admin_priv('shopinfo_manage');
	$article_id = (isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$sql = 'SELECT article_id, title, content FROM ' . $ecs->table('article') . 'WHERE article_id = \'' . $article_id . '\'';
	$article = $db->GetRow($sql);
	create_html_editor('FCKeditor1', $article['content']);
	$smarty->assign('ur_here', $_LANG['article_add']);
	$smarty->assign('action_link', array('text' => $_LANG['shopinfo_list'], 'href' => 'shopinfo.php?act=list'));
	$smarty->assign('article', $article);
	$smarty->assign('form_action', 'update');
	$smarty->display('shopinfo_info.htm');
}

if ($_REQUEST['act'] == 'update') {
	admin_priv('shopinfo_manage');
	$article_id = (isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$title = (isset($_POST['title']) && !empty($_POST['title']) ? addslashes($_POST['title']) : '');
	$old_title = (isset($_POST['old_title']) && !empty($_POST['old_title']) ? addslashes($_POST['old_title']) : '');
	$editor = (isset($_POST['FCKeditor1']) && !empty($_POST['FCKeditor1']) ? addslashes($_POST['FCKeditor1']) : '');

	if ($title != $old_title) {
		$is_only = $exc->is_only('title', $title, $article_id);

		if (!$is_only) {
			sys_msg(sprintf($_LANG['title_exist'], stripslashes($title)), 1);
		}
	}

	$cur_time = gmtime();

	if ($exc->edit('title=\'' . $title . '\', content=\'' . $editor . '\',add_time =\'' . $cur_time . '\'', $article_id)) {
		clear_cache_files();
		$link[0]['text'] = $_LANG['back_list'];
		$link[0]['href'] = 'shopinfo.php?act=list';
		sys_msg(sprintf($_LANG['articleedit_succeed'], $title), 0, $link);
		admin_log($_POST['title'], 'edit', 'shopinfo');
	}
}
else if ($_REQUEST['act'] == 'edit_title') {
	check_authz_json('shopinfo_manage');
	$id = intval($_POST['id']);
	$title = json_str_iconv(trim($_POST['val']));

	if ($exc->num('title', $title, $id) == 0) {
		if ($exc->edit('title = \'' . $title . '\'', $id)) {
			clear_cache_files();
			admin_log($title, 'edit', 'shopinfo');
			make_json_result(stripslashes($title));
		}
	}
	else {
		make_json_error(sprintf($_LANG['title_exist'], $title));
	}
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('shopinfo_manage');
	$id = intval($_GET['id']);
	$title = $exc->get_name($id);

	if ($exc->drop($id)) {
		clear_cache_files();
		admin_log(addslashes($title), 'remove', 'shopinfo');
	}

	$url = 'shopinfo.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

?>
