<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_language_item_list($file_path, $keyword)
{
	if (empty($keyword)) {
		return array();
	}

	$line_array = file($file_path);

	if (!$line_array) {
		return false;
	}
	else {
		$keyword = preg_quote($keyword, '/');
		$matches = array();
		$pattern = '/\\[[\'|"](.*?)' . $keyword . '(.*?)[\'|"]\\]\\s|=\\s?[\'|"](.*?)' . $keyword . '(.*?)[\'|"];/';
		$regx = '/(?P<item>(?P<item_id>\\$_LANG\\[[\'|"].*[\'|"]\\])\\s*?=\\s*?[\'|"](?P<item_content>.*)[\'|"];)/';

		foreach ($line_array as $lang) {
			if (preg_match($pattern, $lang)) {
				$out = array();

				if (preg_match($regx, $lang, $out)) {
					$matches[] = $out;
				}
			}
		}

		return $matches;
	}
}

function set_language_items($file_path, $src_items, $dst_items)
{
	if (file_mode_info($file_path) < 2) {
		return false;
	}

	$line_array = file($file_path);

	if (!$line_array) {
		return false;
	}
	else {
		$file_content = implode('', $line_array);
	}

	$snum = count($src_items);
	$dnum = count($dst_items);

	if ($snum != $dnum) {
		return false;
	}

	ksort($src_items);
	ksort($dst_items);

	for ($i = 0; $i < $snum; $i++) {
		$file_content = str_replace($src_items[$i], $dst_items[$i], $file_content);
	}

	$f = fopen($file_path, 'wb');

	if (!$f) {
		return false;
	}

	if (!fwrite($f, $file_content)) {
		return false;
	}
	else {
		return true;
	}
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

admin_priv('lang_edit');

if ($_REQUEST['act'] == 'list') {
	$lang_arr = array();
	$lang_path = '../languages/' . $_CFG['lang'];
	$lang_dir = @opendir($lang_path);

	while ($file = @readdir($lang_dir)) {
		if (substr($file, -3) == 'php') {
			$filename = substr($file, 0, -4);
			$lang_arr[$filename] = $file . ' - ' . @$_LANG['language_files'][$filename];
		}
	}

	ksort($lang_arr);
	@closedir($lang_dir);
	$lang_file = (isset($_POST['lang_file']) ? trim($_POST['lang_file']) : '');

	if ($lang_file == 'common') {
		$file_path = '../languages/' . $_CFG['lang'] . '/common.php';
	}
	else if ($lang_file == 'shopping_flow') {
		$file_path = '../languages/' . $_CFG['lang'] . '/shopping_flow.php';
	}
	else {
		$file_path = '../languages/' . $_CFG['lang'] . '/user.php';
	}

	$file_attr = '';

	if (file_mode_info($file_path) < 7) {
		$file_attr = $lang_file . '.php：' . $_LANG['file_attribute'];
	}

	$keyword = (!empty($_POST['keyword']) ? trim(stripslashes($_POST['keyword'])) : '');
	$language_arr = get_language_item_list($file_path, $keyword);
	$smarty->assign('ur_here', $_LANG['edit_languages']);
	$smarty->assign('keyword', $keyword);
	$smarty->assign('action_link', array());
	$smarty->assign('file_attr', $file_attr);
	$smarty->assign('lang_arr', $lang_arr);
	$smarty->assign('file_path', $file_path);
	$smarty->assign('lang_file', $lang_file);
	$smarty->assign('language_arr', $language_arr);
	assign_query_info();
	$smarty->display('language_list.dwt');
}
else if ($_REQUEST['act'] == 'edit') {
	$lang_file = (isset($_POST['file_path']) ? trim($_POST['file_path']) : '');
	$src_items = (!empty($_POST['item']) ? stripslashes_deep($_POST['item']) : '');
	$dst_items = array();
	$_POST['item_id'] = stripslashes_deep($_POST['item_id']);

	for ($i = 0; $i < count($_POST['item_id']); $i++) {
		if (trim($_POST['item_content'][$i]) == '') {
			unset($src_items[$i]);
		}
		else {
			$_POST['item_content'][$i] = str_replace('\\\\n', '\\n', $_POST['item_content'][$i]);
			$dst_items[$i] = $_POST['item_id'][$i] . ' = ' . '\'' . $_POST['item_content'][$i] . '\';';
		}
	}

	$result = set_language_items($lang_file, $src_items, $dst_items);

	if ($result === false) {
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['edit_languages_false'], 0, $link);
	}
	else {
		admin_log('', 'edit', 'languages');
		clear_cache_files();
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'edit_languages.php?act=list');
		sys_msg($_LANG['edit_languages_success'], 0, $link);
	}
}

?>
