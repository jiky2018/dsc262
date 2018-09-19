<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function checkfiles($currentdir, $ext = '', $sub = 1, $skip = '')
{
	global $md5data;
	$currentdir = ROOT_PATH . str_replace(ROOT_PATH, '', $currentdir);
	$dir = @opendir($currentdir);
	$exts = '/(' . $ext . ')$/i';
	$skips = explode(',', $skip);

	while ($entry = @readdir($dir)) {
		$file = $currentdir . $entry;
		if (($entry != '.') && ($entry != '..') && ($entry != '.svn') && (preg_match($exts, $entry) || ($sub && is_dir($file))) && !in_array($entry, $skips)) {
			if ($sub && is_dir($file)) {
				checkfiles($file . '/', $ext, $sub, $skip);
			}
			else if (str_replace(ROOT_PATH, '', $file) != './md5.php') {
				$md5data[str_replace(ROOT_PATH, '', $file)] = md5_file($file);
			}
		}
	}
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
admin_priv('file_check');

if (!($ecshopfiles = @file('./ecshopfiles.md5'))) {
	sys_msg($_LANG['filecheck_nofound_md5file'], 1);
}

$step = (empty($_REQUEST['step']) ? 1 : max(1, intval($_REQUEST['step'])));
if (($step == 1) || ($step == 2)) {
	$smarty->assign('step', $step);

	if ($step == 1) {
		$smarty->assign('ur_here', $_LANG['file_check']);
	}

	if ($step == 2) {
		$smarty->assign('ur_here', $_LANG['fileperms_verify']);
	}

	assign_query_info();
	$smarty->display('filecheck.htm');
}
else if ($step == 3) {
	@set_time_limit(0);
	$md5data = array();
	checkfiles('./', '\\.php', 0);
	checkfiles(ADMIN_PATH . '/', '\\.php|\\.htm|\\.js|\\.css|\\xml');
	checkfiles('api/', '\\.php');
	checkfiles('includes/', '\\.php|\\.html|\\.js', 1, 'fckeditor');
	checkfiles('js/', '\\.js|\\.css');
	checkfiles('languages/', '\\.php');
	checkfiles('plugins/', '\\.php');
	checkfiles('wap/', '\\.php|\\.wml');
	checkfiles('mobile/', '\\.php');

	foreach ($ecshopfiles as $line) {
		$file = trim(substr($line, 34));
		$md5datanew[$file] = substr($line, 0, 32);

		if ($md5datanew[$file] != $md5data[$file]) {
			$modifylist[$file] = $md5data[$file];
		}

		$md5datanew[$file] = $md5data[$file];
	}

	$weekbefore = time() - 604800;
	$addlist = @array_diff_assoc($md5data, $md5datanew);
	$dellist = @array_diff_assoc($md5datanew, $md5data);
	$modifylist = @array_diff_assoc($modifylist, $dellist);
	$showlist = @array_merge($md5data, $md5datanew);
	$result = $dirlog = array();

	foreach ($showlist as $file => $md5) {
		$dir = dirname($file);
		$statusf = $statust = 1;

		if (@array_key_exists($file, $modifylist)) {
			$status = '<em class="edited">' . $_LANG['filecheck_modify'] . '</em>';

			if (!isset($dirlog[$dir]['modify'])) {
				$dirlog[$dir]['modify'] = '';
			}

			$dirlog[$dir]['modify']++;
			$dirlog[$dir]['marker'] = substr(md5($dir), 0, 3);
		}
		else if (@array_key_exists($file, $dellist)) {
			$status = '<em class="del">' . $_LANG['filecheck_delete'] . '</em>';

			if (!isset($dirlog[$dir]['del'])) {
				$dirlog[$dir]['del'] = '';
			}

			$dirlog[$dir]['del']++;
			$dirlog[$dir]['marker'] = substr(md5($dir), 0, 3);
		}
		else if (@array_key_exists($file, $addlist)) {
			$status = '<em class="unknown">' . $_LANG['filecheck_unknown'] . '</em>';

			if (!isset($dirlog[$dir]['add'])) {
				$dirlog[$dir]['add'] = '';
			}

			$dirlog[$dir]['add']++;
			$dirlog[$dir]['marker'] = substr(md5($dir), 0, 3);
		}
		else {
			$status = '<em class="correct">' . $_LANG['filecheck_check_ok'] . '</em>';
			$statusf = 0;
		}

		$filemtime = @filemtime(ROOT_PATH . $file);

		if ($weekbefore < $filemtime) {
			$filemtime = '<b>' . date('Y-m-d H:i:s', $filemtime) . '</b>';
		}
		else {
			$filemtime = date('Y-m-d H:i:s', $filemtime);
			$statust = 0;
		}

		if ($statusf) {
			$filelist[$dir][] = array('file' => basename($file), 'size' => file_exists(ROOT_PATH . $file) ? number_format(filesize(ROOT_PATH . $file)) . ' Bytes' : '', 'filemtime' => $filemtime, 'status' => $status);
		}
	}

	$result[$_LANG['result_modify']] = count($modifylist);
	$result[$_LANG['result_delete']] = count($dellist);
	$result[$_LANG['result_unknown']] = count($addlist);
	$smarty->assign('result', $result);
	$smarty->assign('dirlog', $dirlog);
	$smarty->assign('filelist', $filelist);
	$smarty->assign('step', $step);
	$smarty->assign('ur_here', $_LANG['filecheck_completed']);
	$smarty->assign('action_link', array('text' => $_LANG['filecheck_return'], 'href' => 'filecheck.php?step=1'));
	assign_query_info();
	$smarty->display('filecheck.htm');
}

?>
