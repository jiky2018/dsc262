<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function load_template($temp_id)
{
	$sql = 'SELECT template_subject, template_content, is_html ' . 'FROM ' . $GLOBALS['ecs']->table('mail_templates') . ' WHERE template_id=\'' . $temp_id . '\'';
	$row = $GLOBALS['db']->GetRow($sql);
	return $row;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
admin_priv('mail_template');

if ($_REQUEST['act'] == 'list') {
	include_once ROOT_PATH . 'includes/fckeditor/fckeditor.php';
	$sql = 'SELECT code FROM ' . $ecs->table('plugins');
	$rs = $db->query($sql);

	while ($row = $db->FetchRow($rs)) {
		if (file_exists('../plugins/' . $row['code'] . '/languages/common_' . $_CFG['lang'] . '.php')) {
			include_once ROOT_PATH . 'plugins/' . $row['code'] . '/languages/common_' . $_CFG['lang'] . '.php';
		}
	}

	$sql = 'SELECT template_id, template_code FROM ' . $ecs->table('mail_templates') . ' WHERE  type = \'template\'';
	$res = $db->query($sql);
	$cur = NULL;

	while ($row = $db->FetchRow($res)) {
		if ($cur == NULL) {
			$cur = $row['template_id'];
		}

		$len = strlen($_LANG[$row['template_code']]);
		$templates[$row['template_id']] = $len < 18 ? $_LANG[$row['template_code']] . str_repeat('&nbsp;', (18 - $len) / 2) . ' [' . $row['template_code'] . ']' : $_LANG[$row['template_code']] . ' [' . $row['template_code'] . ']';
	}

	assign_query_info();
	$content = load_template($cur);
	create_html_editor2('content', 'FCKeditor', $content['template_content']);
	$smarty->assign('tpl', $cur);
	$smarty->assign('cur', $cur);
	$smarty->assign('ur_here', $_LANG['06_mail_template_manage']);
	$smarty->assign('templates', $templates);
	$smarty->assign('template', $content);
	$smarty->assign('full_page', 1);
	$smarty->display('mail_template.dwt');
}
else if ($_REQUEST['act'] == 'loat_template') {
	include_once ROOT_PATH . 'includes/fckeditor/fckeditor.php';
	$tpl = intval($_GET['tpl']);
	$mail_type = (isset($_GET['mail_type']) ? $_GET['mail_type'] : -1);
	$sql = 'SELECT code FROM ' . $ecs->table('plugins');
	$rs = $db->query($sql);

	while ($row = $db->FetchRow($rs)) {
		if (file_exists('../plugins/' . $row['code'] . '/languages/common_' . $_CFG['lang'] . '.php')) {
			include_once ROOT_PATH . 'plugins/' . $row['code'] . '/languages/common_' . $_CFG['lang'] . '.php';
		}
	}

	$sql = 'SELECT template_id, template_code FROM ' . $ecs->table('mail_templates') . ' WHERE  type = \'template\'';
	$res = $db->query($sql);

	while ($row = $db->FetchRow($res)) {
		$len = strlen($_LANG[$row['template_code']]);
		$templates[$row['template_id']] = $len < 18 ? $_LANG[$row['template_code']] . str_repeat('&nbsp;', (18 - $len) / 2) . ' [' . $row['template_code'] . ']' : $_LANG[$row['template_code']] . ' [' . $row['template_code'] . ']';
	}

	$content = load_template($tpl);
	if ((($mail_type == -1) && ($content['is_html'] == 1)) || ($mail_type == 1)) {
		create_html_editor2('content', 'FCKeditor', $content['template_content']);
		$content['is_html'] = 1;
	}
	else if ($mail_type == 0) {
		$content['is_html'] = 0;
	}

	$smarty->assign('tpl', $tpl);
	$smarty->assign('cur', $tpl);
	$smarty->assign('templates', $templates);
	$smarty->assign('template', $content);
	make_json_result($smarty->fetch('mail_template.dwt'));
}
else if ($_REQUEST['act'] == 'save_template') {
	if (empty($_POST['subject'])) {
		sys_msg($_LANG['subject_empty'], 1, array(), false);
	}
	else {
		$subject = trim($_POST['subject']);
	}

	if (empty($_POST['content'])) {
		sys_msg($_LANG['content_empty'], 1, array(), false);
	}
	else {
		$content = trim($_POST['content']);
	}

	$type = intval($_POST['mail_type']);
	$tpl_id = intval($_POST['tpl']);
	$sql = 'UPDATE ' . $ecs->table('mail_templates') . ' SET ' . 'template_subject = \'' . str_replace('\\\'\\\'', '\\\'', $subject) . '\', ' . 'template_content = \'' . str_replace('\\\'\\\'', '\\\'', $content) . '\', ' . 'is_html = \'' . $type . '\', ' . 'last_modify = \'' . gmtime() . '\' ' . 'WHERE template_id=\'' . $tpl_id . '\'';

	if ($db->query($sql, 'SILENT')) {
		$link[0] = array('href' => 'mail_template.php?act=list', 'text' => $_LANG['update_success']);
		sys_msg($_LANG['update_success'], 0, $link);
	}
	else {
		sys_msg($_LANG['update_failed'], 1, array(), false);
	}
}

?>
