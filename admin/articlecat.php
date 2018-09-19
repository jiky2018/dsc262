<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function cat_update($cat_id, $args)
{
	if (empty($args) || empty($cat_id)) {
		return false;
	}

	return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('article_cat'), $args, 'update', 'cat_id=\'' . $cat_id . '\'');
}

function get_aryicle_cart_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['cat_id'] = !empty($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : 0;
		$where = ' WHERE 1';

		if (0 < $filter['cat_id']) {
			$where .= ' AND parent_id = \'' . $filter['cat_id'] . '\'';
			$cat_back = 1;
		}
		else {
			$where .= ' AND parent_id = 0';
			$cat_back = 0;
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('article_cat') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('article_cat') . ('  ' . $where . ' GROUP BY cat_id ') . ' ORDER BY parent_id, sort_order ASC' . '  LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . ', ' . $filter['page_size'] . ' ';
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $cat) {
		$row[$key]['type_name'] = $GLOBALS['_LANG']['type_name'][$cat['cat_type']];
		$row[$key]['url'] = build_uri('article', array('acid' => $cat['cat_id']), $cat['cat_name']);
		$row[$key]['add_child'] = 'articlecat.php?act=add&cat_id=' . $cat['cat_id'] . '';
		$row[$key]['child_url'] = 'articlecat.php?act=list&cat_id=' . $cat['cat_id'];
	}

	$arr = array('result' => $row, 'filter' => $filter, 'cat_back' => $cat_back, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('article_cat'), $db, 'cat_id', 'cat_name');
$_REQUEST['act'] = trim($_REQUEST['act']);

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}

if ($_REQUEST['act'] == 'list') {
	$cat_id = isset($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$articlecat = get_aryicle_cart_list();
	$smarty->assign('articlecat', $articlecat['result']);
	$smarty->assign('filter', $articlecat['filter']);
	$smarty->assign('record_count', $articlecat['record_count']);
	$smarty->assign('page_count', $articlecat['page_count']);
	$smarty->assign('cat_back', $articlecat['cat_back']);
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', $_LANG['02_articlecat_list']);
	$smarty->assign('action_link', array('text' => $_LANG['articlecat_add'], 'href' => 'articlecat.php?act=add'));

	if (0 < $cat_id) {
		$sql = 'SELECT parent_id FROM' . $ecs->table('article_cat') . (' WHERE cat_id = \'' . $cat_id . '\'');
		$parent_id = $db->getOne($sql);
		$smarty->assign('action_link1', array('text' => $_LANG['return_to_superior'], 'href' => 'articlecat.php?act=list&cat_id=' . $parent_id));
	}

	assign_query_info();
	$smarty->display('articlecat_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$articlecat = get_aryicle_cart_list();
	$smarty->assign('articlecat', $articlecat['result']);
	$smarty->assign('filter', $articlecat['filter']);
	$smarty->assign('record_count', $articlecat['record_count']);
	$smarty->assign('page_count', $articlecat['page_count']);
	$smarty->assign('cat_back', $articlecat['cat_back']);
	make_json_result($smarty->fetch('articlecat_list.dwt'), '', array('filter' => $articlecat['filter'], 'page_count' => $articlecat['page_count']));
}
else if ($_REQUEST['act'] == 'add') {
	admin_priv('article_cat');
	$cat_id = !empty($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : 0;

	if (0 < $cat_id) {
		$cat_name = $db->getOne('SELECT cat_name FROM' . $ecs->table('article_cat') . ' WHERE cat_id = \'' . $cat_id . '\'');
		$smarty->assign('cat_name', $cat_name);
		$smarty->assign('cat_id', $cat_id);
	}

	$smarty->assign('cat_select', article_cat_list_new(0));
	$smarty->assign('ur_here', $_LANG['articlecat_add']);
	$smarty->assign('action_link', array('text' => $_LANG['02_articlecat_list'], 'href' => 'articlecat.php?act=list'));
	$smarty->assign('form_action', 'insert');
	assign_query_info();
	$smarty->display('articlecat_info.dwt');
}
else if ($_REQUEST['act'] == 'insert') {
	admin_priv('article_cat');
	$is_only = $exc->is_only('cat_name', $_POST['cat_name']);

	if (!$is_only) {
		sys_msg(sprintf($_LANG['catname_exist'], stripslashes($_POST['cat_name'])), 1);
	}

	$cat_type = 1;

	if (0 < $_POST['parent_id']) {
		$sql = 'SELECT cat_type FROM ' . $ecs->table('article_cat') . (' WHERE cat_id = \'' . $_POST['parent_id'] . '\'');
		$p_cat_type = $db->getOne($sql);
		if ($p_cat_type == 2 || $p_cat_type == 3 || $p_cat_type == 5) {
			sys_msg($_LANG['not_allow_add'], 0);
		}
		else if ($p_cat_type == 4) {
			$cat_type = 5;
		}
	}

	$sql = 'INSERT INTO ' . $ecs->table('article_cat') . ("(cat_name, cat_type, cat_desc,keywords, parent_id, sort_order, show_in_nav)\r\n           VALUES ('" . $_POST['cat_name'] . '\', \'' . $cat_type . '\',  \'' . $_POST['cat_desc'] . '\',\'' . $_POST['keywords'] . '\', \'' . $_POST['parent_id'] . '\', \'' . $_POST['sort_order'] . '\', \'' . $_POST['show_in_nav'] . '\')');
	$db->query($sql);

	if ($_POST['show_in_nav'] == 1) {
		$vieworder = $db->getOne('SELECT max(vieworder) FROM ' . $ecs->table('nav') . ' WHERE type = \'middle\'');
		$vieworder += 2;
		$sql = 'INSERT INTO ' . $ecs->table('nav') . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type) VALUES(\'' . $_POST['cat_name'] . '\', \'a\', \'' . $db->insert_id() . ('\',\'1\',\'' . $vieworder . '\',\'0\', \'') . build_uri('article_cat', array('acid' => $db->insert_id()), $_POST['cat_name']) . '\',\'middle\')';
		$db->query($sql);
	}

	admin_log($_POST['cat_name'], 'add', 'articlecat');
	$link[0]['text'] = $_LANG['continue_add'];
	$link[0]['href'] = 'articlecat.php?act=add';
	$link[1]['text'] = $_LANG['back_list'];
	$link[1]['href'] = 'articlecat.php?act=list&cat_id=' . $_POST['parent_id'];
	clear_cache_files();
	sys_msg($_POST['cat_name'] . $_LANG['catadd_succed'], 0, $link);
}
else if ($_REQUEST['act'] == 'edit') {
	admin_priv('article_cat');
	$sql = 'SELECT cat_id, cat_name, cat_type, cat_desc, show_in_nav, keywords, parent_id,sort_order FROM ' . $ecs->table('article_cat') . (' WHERE cat_id=\'' . $_REQUEST['id'] . '\'');
	$cat = $db->GetRow($sql);
	if ($cat['cat_type'] == 2 || $cat['cat_type'] == 3 || $cat['cat_type'] == 4) {
		$smarty->assign('disabled', 1);
	}

	$options = article_cat_list_new(0);

	if (0 < $cat['parent_id']) {
		$cat_name = $db->getOne('SELECT cat_name FROM' . $ecs->table('article_cat') . ' WHERE cat_id = \'' . $cat['parent_id'] . '\'');
		$smarty->assign('cat_name', $cat_name);
	}

	$smarty->assign('cat', $cat);
	$smarty->assign('cat_select', $options);
	$smarty->assign('ur_here', $_LANG['articlecat_edit']);
	$smarty->assign('action_link', array('text' => $_LANG['02_articlecat_list'], 'href' => 'articlecat.php?act=list'));
	$smarty->assign('form_action', 'update');
	assign_query_info();
	$smarty->display('articlecat_info.dwt');
}
else if ($_REQUEST['act'] == 'update') {
	admin_priv('article_cat');

	if ($_POST['cat_name'] != $_POST['old_catname']) {
		$is_only = $exc->is_only('cat_name', $_POST['cat_name'], $_POST['id']);

		if (!$is_only) {
			sys_msg(sprintf($_LANG['catname_exist'], stripslashes($_POST['cat_name'])), 1);
		}
	}

	if (!isset($_POST['parent_id'])) {
		$_POST['parent_id'] = 0;
	}

	$row = $db->getRow('SELECT cat_type, parent_id FROM ' . $ecs->table('article_cat') . (' WHERE cat_id=\'' . $_POST['id'] . '\''));
	$cat_type = $row['cat_type'];
	if ($cat_type == 3 || $cat_type == 4) {
		$_POST['parent_id'] = $row['parent_id'];
	}

	$child_cat = article_cat_list($_POST['id'], 0, false);

	if (!empty($child_cat)) {
		foreach ($child_cat as $child_data) {
			$catid_array[] = $child_data['cat_id'];
		}
	}

	if (in_array($_POST['parent_id'], $catid_array)) {
		sys_msg(sprintf($_LANG['parent_id_err'], stripslashes($_POST['cat_name'])), 1);
	}

	if ($cat_type == 1 || $cat_type == 5) {
		if (0 < $_POST['parent_id']) {
			$sql = 'SELECT cat_type FROM ' . $ecs->table('article_cat') . (' WHERE cat_id = \'' . $_POST['parent_id'] . '\'');
			$p_cat_type = $db->getOne($sql);

			if ($p_cat_type == 4) {
				$cat_type = 5;
			}
			else {
				$cat_type = 1;
			}
		}
		else {
			$cat_type = 1;
		}
	}

	$dat = $db->getOne('SELECT cat_name, show_in_nav FROM ' . $ecs->table('article_cat') . ' WHERE cat_id = \'' . $_POST['id'] . '\'');

	if ($exc->edit('cat_name = \'' . $_POST['cat_name'] . '\', cat_desc =\'' . $_POST['cat_desc'] . '\', keywords=\'' . $_POST['keywords'] . '\',parent_id = \'' . $_POST['parent_id'] . '\', cat_type=\'' . $cat_type . '\', sort_order=\'' . $_POST['sort_order'] . '\', show_in_nav = \'' . $_POST['show_in_nav'] . '\'', $_POST['id'])) {
		if (!empty($dat['cat_name'])) {
			if ($_POST['cat_name'] != $dat['cat_name']) {
				$sql = 'UPDATE ' . $ecs->table('nav') . ' SET name = \'' . $_POST['cat_name'] . '\' WHERE ctype = \'a\' AND cid = \'' . $_POST['id'] . '\' AND type = \'middle\'';
				$db->query($sql);
			}
		}

		if (!empty($dat['show_in_nav'])) {
			if ($_POST['show_in_nav'] != $dat['show_in_nav']) {
				if ($_POST['show_in_nav'] == 1) {
					$nid = $db->getOne('SELECT id FROM ' . $ecs->table('nav') . ' WHERE ctype = \'a\' AND cid = \'' . $_POST['id'] . '\' AND type = \'middle\'');

					if (empty($nid)) {
						$vieworder = $db->getOne('SELECT max(vieworder) FROM ' . $ecs->table('nav') . ' WHERE type = \'middle\'');
						$vieworder += 2;
						$uri = build_uri('article_cat', array('acid' => $_POST['id']), $_POST['cat_name']);
						$sql = 'INSERT INTO ' . $ecs->table('nav') . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type) ' . 'VALUES(\'' . $_POST['cat_name'] . '\', \'a\', \'' . $_POST['id'] . ('\',\'1\',\'' . $vieworder . '\',\'0\', \'') . $uri . '\',\'middle\')';
					}
					else {
						$sql = 'UPDATE ' . $ecs->table('nav') . ' SET ifshow = 1 WHERE ctype = \'a\' AND cid = \'' . $_POST['id'] . '\' AND type = \'middle\'';
					}

					$db->query($sql);
				}
				else {
					$db->query('UPDATE ' . $ecs->table('nav') . ' SET ifshow = 0 WHERE ctype = \'a\' AND cid = \'' . $_POST['id'] . '\' AND type = \'middle\'');
				}
			}
		}

		if (0 < $_POST['parent_id']) {
			$link[0]['href'] = 'articlecat.php?act=list&cat_id=' . $_POST['parent_id'];
		}
		else {
			$link[0]['href'] = 'articlecat.php?act=list&uselastfilter=1';
		}

		$note = sprintf($_LANG['catedit_succed'], $_POST['cat_name']);
		admin_log($_POST['cat_name'], 'edit', 'articlecat');
		clear_cache_files();
		sys_msg($note, 0, $link);
	}
	else {
		exit($db->error());
	}
}
else if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('article_cat');
	$id = intval($_POST['id']);
	$order = json_str_iconv(trim($_POST['val']));

	if (!preg_match('/^[0-9]+$/', $order)) {
		make_json_error(sprintf($_LANG['enter_int'], $order));
	}
	else if ($exc->edit('sort_order = \'' . $order . '\'', $id)) {
		clear_cache_files();
		make_json_result(stripslashes($order));
	}
	else {
		make_json_error($db->error());
	}
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('article_cat');
	$id = intval($_GET['id']);
	$sql = 'SELECT cat_type FROM ' . $ecs->table('article_cat') . (' WHERE cat_id = \'' . $id . '\'');
	$cat_type = $db->getOne($sql);
	if ($cat_type == 2 || $cat_type == 3 || $cat_type == 4) {
		make_json_error($_LANG['not_allow_remove']);
	}

	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('article_cat') . (' WHERE parent_id = \'' . $id . '\'');

	if (0 < $db->getOne($sql)) {
		make_json_error($_LANG['is_fullcat']);
	}

	$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('article') . (' WHERE cat_id = \'' . $id . '\'');

	if (0 < $db->getOne($sql)) {
		make_json_error(sprintf($_LANG['not_emptycat']));
	}
	else {
		$exc->drop($id);
		$db->query('DELETE FROM ' . $ecs->table('nav') . ('WHERE  ctype = \'a\' AND cid = \'' . $id . '\' AND type = \'middle\''));
		clear_cache_files();
		admin_log($cat_name, 'remove', 'category');
	}

	$url = 'articlecat.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

if ($_REQUEST['act'] == 'toggle_show_in_nav') {
	check_authz_json('cat_manage');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);

	if (cat_update($id, array('show_in_nav' => $val)) != false) {
		if ($val == 1) {
			$nid = $db->getOne('SELECT id FROM ' . $ecs->table('nav') . (' WHERE ctype=\'a\' AND cid=\'' . $id . '\' AND type = \'middle\''));

			if (empty($nid)) {
				$vieworder = $db->getOne('SELECT max(vieworder) FROM ' . $ecs->table('nav') . ' WHERE type = \'middle\'');
				$vieworder += 2;
				$catname = $db->getOne('SELECT cat_name FROM ' . $ecs->table('article_cat') . (' WHERE cat_id = \'' . $id . '\''));
				$uri = build_uri('article_cat', array('acid' => $id), $_POST['cat_name']);
				$sql = 'INSERT INTO ' . $ecs->table('nav') . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type) ' . 'VALUES(\'' . $catname . ('\', \'a\', \'' . $id . '\',\'1\',\'' . $vieworder . '\',\'0\', \'') . $uri . '\',\'middle\')';
			}
			else {
				$sql = 'UPDATE ' . $ecs->table('nav') . (' SET ifshow = 1 WHERE ctype=\'a\' AND cid=\'' . $id . '\' AND type = \'middle\'');
			}

			$db->query($sql);
		}
		else {
			$db->query('UPDATE ' . $ecs->table('nav') . (' SET ifshow = 0 WHERE ctype=\'a\' AND cid=\'' . $id . '\' AND type = \'middle\''));
		}

		clear_cache_files();
		make_json_result($val);
	}
	else {
		make_json_error($db->error());
	}
}

?>
