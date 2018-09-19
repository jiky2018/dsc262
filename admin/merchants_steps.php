<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function steps_process_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'process_steps, steps_sort' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);
		$ex_where = ' WHERE 1 ';

		if ($filter['keywords']) {
			$ex_where .= ' AND process_title LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\'';
		}

		$filter['record_count'] = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('merchants_steps_process') . $ex_where);
		$filter = page_and_size($filter);
		$sql = 'SELECT id, process_steps, process_title, steps_sort, is_show ' . ' FROM ' . $GLOBALS['ecs']->table('merchants_steps_process') . $ex_where . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$process_list = $GLOBALS['db']->getAll($sql);
	$count = count($process_list);

	for ($i = 0; $i < $count; $i++) {
		$process_list[$i]['process_steps'] = $process_list[$i]['process_steps'];
		$process_list[$i]['process_left'] = $process_list[$i]['process_left'];
		$process_list[$i]['process_right'] = $process_list[$i]['process_right'];
	}

	$arr = array('process_list' => $process_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function steps_process_title_list($id)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'tid' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$ex_where = ' WHERE fields_steps = \'' . $id . '\' ';

		if ($filter['keywords']) {
			$ex_where .= ' AND fields_titles LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\'';
		}

		$filter['record_count'] = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('merchants_steps_title') . $ex_where);
		$filter = page_and_size($filter);
		$sql = 'SELECT tid, fields_steps, steps_style, fields_titles, titles_annotation, fields_special, special_type ' . ' FROM ' . $GLOBALS['ecs']->table('merchants_steps_title') . $ex_where . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$title_list = $GLOBALS['db']->getAll($sql);
	$count = count($title_list);

	for ($i = 0; $i < $count; $i++) {
		$title_list[$i]['fields_steps'] = $GLOBALS['db']->getOne('select process_title from ' . $GLOBALS['ecs']->table('merchants_steps_process') . ' where id = \'' . $title_list[$i]['fields_steps'] . '\'');
	}

	$arr = array('title_list' => $title_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc_process = new exchange($ecs->table('merchants_steps_process'), $db, 'id', 'process_title');

if ($_REQUEST['act'] == 'list') {
	admin_priv('merchants_setps');
	$smarty->assign('menu_select', array('action' => '17_merchants', 'current' => '01_merchants_steps_list'));
	$smarty->assign('ur_here', $_LANG['01_merchants_list']);
	$smarty->assign('action_link', array('text' => $_LANG['02_merchants_add'], 'href' => 'merchants_steps.php?act=add'));
	$process_list = steps_process_list();
	$smarty->assign('process_list', $process_list['process_list']);
	$smarty->assign('filter', $process_list['filter']);
	$smarty->assign('record_count', $process_list['record_count']);
	$smarty->assign('page_count', $process_list['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');
	assign_query_info();
	$smarty->display('merchants_steps_list.dwt');
}
else if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('merchants_setps');
	$id = intval($_POST['id']);
	$order = intval($_POST['val']);
	$name = $exc_process->get_name($id);

	if ($exc_process->edit('steps_sort = \'' . $order . '\'', $id)) {
		make_json_result($order);
	}
	else {
		make_json_error(sprintf($_LANG['brandedit_fail'], $name));
	}
}
else if ($_REQUEST['act'] == 'query') {
	$process_list = steps_process_list();
	$smarty->assign('process_list', $process_list['process_list']);
	$smarty->assign('filter', $process_list['filter']);
	$smarty->assign('record_count', $process_list['record_count']);
	$smarty->assign('page_count', $process_list['page_count']);
	$sort_flag = sort_flag($process_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('merchants_steps_list.dwt'), '', array('filter' => $process_list['filter'], 'page_count' => $process_list['page_count']));
}

if ($_REQUEST['act'] == 'title_list') {
	admin_priv('merchants_setps');
	$id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$smarty->assign('ur_here', $_LANG['05_merchants_title_list']);
	$smarty->assign('action_link', array('text' => $_LANG['04_merchants_add_info'], 'href' => 'merchants_steps.php?act=title_add&id=' . $id));
	$smarty->assign('action_link2', array('text' => $_LANG['01_merchants_list'], 'href' => 'merchants_steps.php?act=list'));
	$_SESSION['title_id'] = $id;
	$title_list = steps_process_title_list($id);
	$smarty->assign('title_list', $title_list['title_list']);
	$smarty->assign('filter', $title_list['filter']);
	$smarty->assign('record_count', $title_list['record_count']);
	$smarty->assign('page_count', $title_list['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');
	$smarty->assign('tid', $id);
	assign_query_info();
	$smarty->display('merchants_steps_title_list.dwt');
}
else if ($_REQUEST['act'] == 'query_title') {
	$title_list = steps_process_title_list($_SESSION['title_id']);
	$smarty->assign('title_list', $title_list['title_list']);
	$smarty->assign('filter', $title_list['filter']);
	$smarty->assign('record_count', $title_list['record_count']);
	$smarty->assign('page_count', $title_list['page_count']);
	$sort_flag = sort_flag($title_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('merchants_steps_title_list.dwt'), '', array('filter' => $title_list['filter'], 'page_count' => $title_list['page_count']));
}
else if ($_REQUEST['act'] == 'toggle_steps_show') {
	check_authz_json('merchants_setps');
	$id = intval($_POST['id']);
	$is_show = intval($_POST['val']);

	if ($exc_process->edit('is_show = \'' . $is_show . '\'', $id)) {
		clear_cache_files();
		make_json_result($is_show);
	}
}
else if ($_REQUEST['act'] == 'add') {
	admin_priv('merchants_setps');
	$smarty->assign('ur_here', $_LANG['02_merchants_add']);
	$smarty->assign('action_link', array('text' => $_LANG['01_merchants_list'], 'href' => 'merchants_steps.php?act=list'));
	$smarty->assign('form_action', 'insert');
	assign_query_info();
	$smarty->display('merchants_steps_process.dwt');
}
else if ($_REQUEST['act'] == 'insert') {
	admin_priv('merchants_setps');
	$process_steps = (isset($_POST['process_steps']) ? trim($_POST['process_steps']) : 1);
	$process_title = (isset($_POST['process_title']) ? trim($_POST['process_title']) : '');
	$process_article = (isset($_POST['process_article']) ? trim($_POST['process_article']) : 0);
	$steps_sort = (isset($_POST['steps_sort']) ? trim($_POST['steps_sort']) + 0 : 0);
	$fields_next = (isset($_POST['fields_next']) ? trim($_POST['fields_next']) : '');
	$sql = 'select id from ' . $ecs->table('merchants_steps_process') . ' where process_title = \'' . $process_title . '\' or fields_next = \'' . $fields_next . '\'';
	$res = $db->getOne($sql);

	if (0 < $res) {
		$add = $_LANG['add_failure'];
	}
	else {
		$parent = array('process_steps' => $process_steps, 'process_title' => $process_title, 'process_article' => $process_article, 'steps_sort' => $steps_sort, 'fields_next' => $fields_next);
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_steps_process'), $parent, 'INSERT');
		$add = $_LANG['add_success_process'];
	}

	admin_log($process_title, 'add', 'merchants_steps_process');
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'merchants_steps.php?act=list');
	sys_msg($add, 0, $link);
}
else if ($_REQUEST['act'] == 'title_add') {
	admin_priv('merchants_setps');
	$id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$smarty->assign('ur_here', $_LANG['02_merchants_add']);
	$smarty->assign('action_link', array('text' => $_LANG['01_merchants_list'], 'href' => 'merchants_steps.php?act=title_list&id=' . $id));
	$smarty->assign('form_action', 'title_insert');
	$sql = 'select id, process_title from ' . $ecs->table('merchants_steps_process') . ' where 1';
	$process_list = $db->getAll($sql);
	$smarty->assign('process_list', $process_list);
	$smarty->assign('fields_steps', $id);
	assign_query_info();
	$smarty->display('merchants_steps_info.dwt');
}
else if ($_REQUEST['act'] == 'title_insert') {
	admin_priv('merchants_setps');
	$tid = 0;
	$fields_steps = (isset($_POST['fields_steps']) ? intval($_POST['fields_steps']) : 1);
	$fields_titles = (isset($_POST['fields_titles']) ? trim($_POST['fields_titles']) : '');
	$titles_annotation = (isset($_POST['titles_annotation']) ? trim($_POST['titles_annotation']) : '');
	$steps_style = (isset($_POST['steps_style']) ? trim($_POST['steps_style']) : '');
	$fields_special = (isset($_POST['fields_special']) ? trim($_POST['fields_special']) : '');
	$special_type = (isset($_POST['special_type']) ? intval($_POST['special_type']) : 1);
	$date = (isset($_POST['merchants_date']) ? $_POST['merchants_date'] : array());
	$dateType = (isset($_POST['merchants_dateType']) ? $_POST['merchants_dateType'] : array());
	$length = (isset($_POST['merchants_length']) ? $_POST['merchants_length'] : array());
	$notnull = (isset($_POST['merchants_notnull']) ? $_POST['merchants_notnull'] : array());
	$coding = (isset($_POST['merchants_coding']) ? $_POST['merchants_coding'] : array());
	$formName = (isset($_POST['merchants_formName']) ? $_POST['merchants_formName'] : array());
	$form = (isset($_POST['merchants_form']) ? $_POST['merchants_form'] : array());
	$formOther = (isset($_POST['merchants_formOther']) ? $_POST['merchants_formOther'] : array());
	$formSize = (isset($_POST['merchants_formSize']) ? $_POST['merchants_formSize'] : array());
	$rows = (isset($_POST['merchants_rows']) ? $_POST['merchants_rows'] : array());
	$cols = (isset($_POST['merchants_cols']) ? $_POST['merchants_cols'] : array());
	$formOtherSize = (isset($_POST['merchants_formOtherSize']) ? $_POST['merchants_formOtherSize'] : array());
	$formName_special = (isset($_POST['formName_special']) ? $_POST['formName_special'] : array());
	$fields_sort = (isset($_POST['fields_sort']) ? $_POST['fields_sort'] : array());
	$form_array = array('form' => $form, 'formOther' => $formOther, 'formSize' => $formSize, 'rows' => $rows, 'cols' => $cols, 'formOtherSize' => $formOtherSize, 'formName_special' => $formName_special, 'date' => $date);
	$form_choose = get_steps_form_choose($form_array);
	$res = get_merchants_steps_title_insert_update($fields_steps, $fields_titles, $titles_annotation, $steps_style, $fields_special, $special_type, 'insert', $tid);
	$sql = 'select fields_steps from ' . $ecs->table('merchants_steps_title') . ' where tid = \'' . $res['tid'] . '\'';
	$fields_steps = $db->getOne($sql);

	if ($res['true']) {
		$steps = get_merchants_steps_fields_admin('merchants_steps_fields', $date, $dateType, $length, $notnull, $coding, $formName, $fields_sort, $res['tid']);
		get_merchants_steps_fields_centent_insert_update($steps['textFields'], $steps['fieldsDateType'], $steps['fieldsLength'], $steps['fieldsNotnull'], $steps['fieldsFormName'], $steps['fieldsCoding'], $steps['fields_sort'], $steps['will_choose'], $form_choose['chooseForm'], $res['tid']);
		$update = $_LANG['insert_success'];
	}
	else {
		$update = $_LANG['insert_failure'];
	}

	admin_log($_LANG['merchants_fields_add'], 'add', 'merchants_steps');
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'merchants_steps.php?act=title_list&id=' . $fields_steps);
	sys_msg($_LANG['add_success'], 0, $link);
}
else if ($_REQUEST['act'] == 'edit') {
	admin_priv('merchants_setps');
	$id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$sql = 'select * from ' . $ecs->table('merchants_steps_process') . ' where id = \'' . $id . '\'';
	$process_info = $db->getRow($sql);
	$smarty->assign('ur_here', $_LANG['02_merchants_add']);
	$smarty->assign('action_link', array('text' => $_LANG['01_merchants_list'], 'href' => 'merchants_steps.php?act=list'));
	assign_query_info();
	$smarty->assign('process_info', $process_info);
	$smarty->assign('form_action', 'update');
	$smarty->display('merchants_steps_process.dwt');
}
else if ($_REQUEST['act'] == 'title_edit') {
	admin_priv('merchants_setps');
	$id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$sql = 'SELECT t.*, p.process_title FROM ' . $ecs->table('merchants_steps_title') . ' AS t ' . ' LEFT JOIN ' . $ecs->table('merchants_steps_process') . ' AS p ON t.fields_steps = p.id ' . ' WHERE t.tid = \'' . $id . '\' ';
	$title_info = $db->getRow($sql);
	$smarty->assign('title_info', $title_info);
	$smarty->assign('ur_here', $_LANG['02_merchants_add']);
	$smarty->assign('action_link', array('text' => $_LANG['01_merchants_list'], 'href' => 'merchants_steps.php?act=title_list&id=' . $title_info['fields_steps']));
	$sql = 'select id, process_title from ' . $ecs->table('merchants_steps_process') . ' where 1';
	$process_list = $db->getAll($sql);
	$smarty->assign('process_list', $process_list);
	$sql = 'select * from ' . $ecs->table('merchants_steps_fields_centent') . ' where tid = \'' . $id . '\'';
	$centent = $db->getRow($sql);
	$cententFields = get_fields_centent_info($centent['id'], $centent['textFields'], $centent['fieldsDateType'], $centent['fieldsLength'], $centent['fieldsNotnull'], $centent['fieldsFormName'], $centent['fieldsCoding'], $centent['fieldsForm'], $centent['fields_sort'], $centent['will_choose']);
	$smarty->assign('cententFields', $cententFields);
	$smarty->assign('fieldsCount', count($cententFields) + 1);
	$smarty->assign('fields_steps', $title_info['fields_steps']);
	assign_query_info();
	$smarty->assign('form_action', 'title_update');
	$smarty->assign('tid', $id);
	$smarty->display('merchants_steps_info.dwt');
}
else if ($_REQUEST['act'] == 'update') {
	admin_priv('merchants_setps');
	$id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$process_steps = (isset($_POST['process_steps']) ? trim($_POST['process_steps']) : 1);
	$process_title = (isset($_POST['process_title']) ? trim($_POST['process_title']) : '');
	$process_article = (isset($_POST['process_article']) ? trim($_POST['process_article']) : 0);
	$steps_sort = (isset($_POST['steps_sort']) ? trim($_POST['steps_sort']) + 0 : 0);
	$fields_next = (isset($_POST['fields_next']) ? trim($_POST['fields_next']) : '');
	$sql = 'select id from ' . $ecs->table('merchants_steps_process') . ' where (process_title = \'' . $process_title . '\' or fields_next = \'' . $fields_next . '\') and id <> \'' . $id . '\'';
	$res = $db->getOne($sql);

	if (0 < $res) {
		$update = $_LANG['update_failure'];
	}
	else {
		$parent = array('process_steps' => $process_steps, 'process_title' => $process_title, 'process_article' => $process_article, 'steps_sort' => $steps_sort, 'fields_next' => $fields_next);
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_steps_process'), $parent, 'UPDATE', 'id = \'' . $id . '\'');
		$update = $_LANG['update_success'];
	}

	$links[0]['text'] = $_LANG['goto_list'];
	$links[0]['href'] = 'merchants_steps.php?act=list';
	$links[1]['text'] = $_LANG['go_back'];
	$links[1]['href'] = 'merchants_steps.php?act=edit&id=' . $id;
	sys_msg($update, 0, $links);
}
else if ($_REQUEST['act'] == 'title_update') {
	admin_priv('merchants_setps');
	$tid = (isset($_REQUEST['tid']) ? intval($_REQUEST['tid']) : 0);
	$fields_steps = (isset($_POST['fields_steps']) ? intval($_POST['fields_steps']) : 1);
	$fields_titles = (isset($_POST['fields_titles']) ? trim($_POST['fields_titles']) : '');
	$titles_annotation = (isset($_POST['titles_annotation']) ? trim($_POST['titles_annotation']) : '');
	$steps_style = (isset($_POST['steps_style']) ? trim($_POST['steps_style']) : '');
	$fields_special = (isset($_POST['fields_special']) ? trim($_POST['fields_special']) : '');
	$special_type = (isset($_POST['special_type']) ? intval($_POST['special_type']) : 1);
	$date = (isset($_POST['merchants_date']) ? $_POST['merchants_date'] : array());
	$dateType = (isset($_POST['merchants_dateType']) ? $_POST['merchants_dateType'] : array());
	$length = (isset($_POST['merchants_length']) ? $_POST['merchants_length'] : array());
	$notnull = (isset($_POST['merchants_notnull']) ? $_POST['merchants_notnull'] : array());
	$coding = (isset($_POST['merchants_coding']) ? $_POST['merchants_coding'] : array());
	$formName = (isset($_POST['merchants_formName']) ? $_POST['merchants_formName'] : array());
	$form = (isset($_POST['merchants_form']) ? $_POST['merchants_form'] : array());
	$formOther = (isset($_POST['merchants_formOther']) ? $_POST['merchants_formOther'] : array());
	$formSize = (isset($_POST['merchants_formSize']) ? $_POST['merchants_formSize'] : array());
	$rows = (isset($_POST['merchants_rows']) ? $_POST['merchants_rows'] : array());
	$cols = (isset($_POST['merchants_cols']) ? $_POST['merchants_cols'] : array());
	$formOtherSize = (isset($_POST['merchants_formOtherSize']) ? $_POST['merchants_formOtherSize'] : array());
	$formName_special = (isset($_POST['formName_special']) ? $_POST['formName_special'] : array());
	$fields_sort = (isset($_POST['fields_sort']) ? $_POST['fields_sort'] : array());
	$form_array = array('form' => $form, 'formOther' => $formOther, 'formSize' => $formSize, 'rows' => $rows, 'cols' => $cols, 'formOtherSize' => $formOtherSize, 'formName_special' => $formName_special, 'date' => $date);
	$form_choose = get_steps_form_choose($form_array);
	$steps = get_merchants_steps_fields_admin('merchants_steps_fields', $date, $dateType, $length, $notnull, $coding, $formName, $fields_sort, $tid);
	get_merchants_steps_fields_centent_insert_update($steps['textFields'], $steps['fieldsDateType'], $steps['fieldsLength'], $steps['fieldsNotnull'], $steps['fieldsFormName'], $steps['fieldsCoding'], $steps['fields_sort'], $steps['will_choose'], $form_choose['chooseForm'], $tid);
	$res = get_merchants_steps_title_insert_update($fields_steps, $fields_titles, $titles_annotation, $steps_style, $fields_special, $special_type, 'update', $tid);

	if ($res) {
		$update = $_LANG['update_success'];
	}
	else {
		$update = $_LANG['update_failure'];
	}

	$sql = 'select fields_steps from ' . $GLOBALS['ecs']->table('merchants_steps_title') . ' where tid = \'' . $tid . '\'';
	$pid = $GLOBALS['db']->getOne($sql);
	$links[0]['text'] = $_LANG['goto_list'];
	$links[0]['href'] = 'merchants_steps.php?act=title_list&id=' . $pid;
	$links[1]['text'] = $_LANG['go_back'];
	$links[1]['href'] = 'merchants_steps.php?act=title_edit&id=' . $tid;
	sys_msg($update, 0, $links);
}
else if ($_REQUEST['act'] == 'batch_remove') {
	admin_priv('merchants_setps_drop');

	if (isset($_POST['checkboxes'])) {
		$sql = 'SELECT process_title FROM ' . $ecs->table('merchants_steps_process') . ' WHERE id ' . db_create_in($_POST['checkboxes']);
		$col = $db->getCol($sql);
		$usernames = implode(',', addslashes_deep($col));
		$count = count($col);
		$sql = 'DELETE FROM ' . $ecs->table('merchants_steps_process') . "\r\n           WHERE id " . db_create_in($_POST['checkboxes']);

		if ($db->query($sql) == true) {
			admin_log($usernames, 'batch_remove', 'merchants_steps');
			$lnk[] = array('text' => $_LANG['go_back'], 'href' => 'merchants_steps.php?act=list');
			sys_msg(sprintf($_LANG['batch_remove_success'], $count), 0, $lnk);
		}
	}
	else {
		$lnk[] = array('text' => $_LANG['go_back'], 'href' => 'merchants_steps.php?act=list');
		sys_msg($_LANG['no_select_user'], 0, $lnk);
	}
}
else if ($_REQUEST['act'] == 'remove') {
	admin_priv('merchants_setps_drop');
	$id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$sql = 'delete from ' . $ecs->table('merchants_steps_process') . ' where id = \'' . $id . '\'';
	$db->query($sql);
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'merchants_steps.php?act=list');
	sys_msg($_LANG['remove_success'], 0, $link);
}
else if ($_REQUEST['act'] == 'titleList_remove') {
	admin_priv('merchants_setps_drop');
	$tid = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$sql = 'select fields_steps from ' . $ecs->table('merchants_steps_title') . ' where tid = \'' . $tid . '\'';
	$fields_steps = $db->getOne($sql);
	$sql = 'delete from ' . $ecs->table('merchants_steps_title') . ' where tid = \'' . $tid . '\'';
	$db->query($sql);
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'merchants_steps.php?act=title_list&id=' . $fields_steps);
	sys_msg($_LANG['remove_success'], 0, $link);
}
else if ($_REQUEST['act'] == 'title_remove') {
	admin_priv('merchants_setps_drop');
	$tid = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
	$objName = (isset($_REQUEST['objName']) ? $_REQUEST['objName'] : '');
	$fields_date_all = get_fields_date_title_remove($tid, $objName);

	if (count($fields_date_all) == 1) {
		$sql = 'delete from ' . $ecs->table('merchants_steps_fields_centent') . ' where tid = \'' . $tid . '\'';
		$db->query($sql);
		get_Add_Drop_fields($objName, '', 'merchants_steps_fields', 'delete');
	}
	else {
		$fields_date = get_fields_date_title_remove($tid, $objName, 1);
		get_title_remove($tid, $fields_date, $objName);
	}

	$link[] = array('text' => $_LANG['go_back'], 'href' => 'merchants_steps.php?act=title_edit&id=' . $tid);
	sys_msg($_LANG['remove_success'], 0, $link);
}
else if ($_REQUEST['act'] == 'step_up') {
	admin_priv('merchants_setps');
	require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/shop_config.php';
	$smarty->assign('ur_here', $_LANG['01_seller_stepup']);
	$smarty->assign('menu_select', array('action' => '17_merchants', 'current' => '01_seller_stepup'));
	$group_list = get_up_settings('seller');
	$smarty->assign('group_list', $group_list);
	assign_query_info();
	$smarty->display('merchants_step_up.dwt');
}

?>
