<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function read_tpl_style($tpl_name, $flag = 1)
{
	if (empty($tpl_name) && ($flag == 1)) {
		return 0;
	}

	$temp = '';
	$start = 0;
	$available_templates = array();
	$dir = ROOT_PATH . 'seller_themes/' . $tpl_name . '/';
	$tpl_style_dir = @opendir($dir);

	while ($file = readdir($tpl_style_dir)) {
		if (($file != '.') && ($file != '..') && is_file($dir . $file) && ($file != '.svn') && ($file != 'index.dwt')) {
			if (preg_match('/^(style|style_)(.*)*/i', $file)) {
				$start = strpos($file, '.');
				$temp = substr($file, 0, $start);
				$temp = explode('_', $temp);

				if (count($temp) == 2) {
					$available_templates[] = $temp[1];
				}
			}
		}
	}

	@closedir($tpl_style_dir);

	if ($flag == 1) {
		$ec = '<table border="0" width="100%" cellpadding="0" cellspacing="0" class="colortable" onMouseOver="javascript:onSOver(0, this);" onMouseOut="onSOut(this);" onclick="javascript:setupTemplateFG(0);"  bgcolor="#FFFFFF"><tr><td>&nbsp;</td></tr></table>';

		if (0 < count($available_templates)) {
			foreach ($available_templates as $value) {
				$tpl_info = get_template_info($tpl_name, $value);
				$ec .= '<table border="0" width="100%" cellpadding="0" cellspacing="0" class="colortable" onMouseOver="javascript:onSOver(\'' . $value . '\', this);" onMouseOut="onSOut(this);" onclick="javascript:setupTemplateFG(\'' . $value . '\');"  bgcolor="' . $tpl_info['type'] . '"><tr><td>&nbsp;</td></tr></table>';
				unset($tpl_info);
			}
		}
		else {
			$ec = '0';
		}

		return $ec;
	}
	else if ($flag == 2) {
		$templates_temp = array('');

		if (0 < count($available_templates)) {
			foreach ($available_templates as $value) {
				$templates_temp[] = $value;
			}
		}

		return $templates_temp;
	}
}

function read_style_and_tpl($tpl_name, $tpl_style)
{
	$style_info = array();
	$style_info = get_seller_template_info($tpl_name, $tpl_style);
	$tpl_style_info = array();
	$tpl_style_info = read_tpl_style($tpl_name, 2);
	$tpl_style_list = '';

	if (1 < count($tpl_style_info)) {
		foreach ($tpl_style_info as $value) {
			$tpl_style_list .= '<span style="cursor:pointer;" onMouseOver="javascript:onSOver(\'screenshot\', \'' . $value . '\', this);" onMouseOut="onSOut(\'screenshot\', this, \'' . $style_info['screenshot'] . '\');" onclick="javascript:setupTemplateFG(\'' . $tpl_name . '\', \'' . $value . '\', \'\');" id="templateType_' . $value . '"><img src="../themes/' . $tpl_name . '/images/type' . $value . '_';

			if ($value == $tpl_style) {
				$tpl_style_list .= '1';
			}
			else {
				$tpl_style_list .= '0';
			}

			$tpl_style_list .= '.gif" border="0"></span>&nbsp;';
		}
	}

	$style_info['tpl_style'] = $tpl_style_list;
	return $style_info;
}

function get_seller_template_info($template_name, $template_style = '')
{
	if (empty($template_style) || ($template_style == '')) {
		$template_style = '';
	}

	$info = array();
	$ext = array('png', 'gif', 'jpg', 'jpeg');
	$info['code'] = $template_name;
	$info['screenshot'] = '';
	$info['stylename'] = $template_style;

	if ($template_style == '') {
		foreach ($ext as $val) {
			if (file_exists('../seller_themes/' . $template_name . '/screenshot.' . $val)) {
				$info['screenshot'] = '../seller_themes/' . $template_name . '/screenshot.' . $val;
				break;
			}
		}
	}
	else {
		foreach ($ext as $val) {
			if (file_exists('../seller_themes/' . $template_name . '/screenshot_' . $template_style . '.' . $val)) {
				$info['screenshot'] = '../seller_themes/' . $template_name . '/screenshot_' . $template_style . '.' . $val;
				break;
			}
		}
	}

	$info_path = '../seller_themes/' . $template_name . '/tpl_info.txt';

	if ($template_style != '') {
		$info_path = '../seller_themes/' . $template_name . '/tpl_info_' . $template_style . '.txt';
	}

	if (file_exists($info_path) && !empty($template_name)) {
		$custom_content = addslashes(iconv('GB2312', 'UTF-8', $info_path));
		$arr = array_slice(file($info_path), 0, 9);
		$arr[1] = addslashes(iconv('GB2312', 'UTF-8', $arr[1]));
		$arr[2] = addslashes(iconv('GB2312', 'UTF-8', $arr[2]));
		$arr[3] = addslashes(iconv('GB2312', 'UTF-8', $arr[3]));
		$arr[4] = addslashes(iconv('GB2312', 'UTF-8', $arr[4]));
		$arr[5] = addslashes(iconv('GB2312', 'UTF-8', $arr[5]));
		$arr[6] = addslashes(iconv('GB2312', 'UTF-8', $arr[6]));
		$arr[7] = addslashes(iconv('GB2312', 'UTF-8', $arr[7]));
		$arr[8] = addslashes(iconv('GB2312', 'UTF-8', $arr[8]));
		$template_name = explode('：', $arr[1]);
		$template_uri = explode('：', $arr[2]);
		$template_desc = explode('：', $arr[3]);
		$template_version = explode('：', $arr[4]);
		$template_author = explode('：', $arr[5]);
		$author_uri = explode('：', $arr[6]);
		$tpl_dwt_code = explode('：', $arr[7]);
		$win_goods_type = explode('：', $arr[8]);
		$info['name'] = isset($template_name[1]) ? trim($template_name[1]) : '';
		$info['uri'] = isset($template_uri[1]) ? trim($template_uri[1]) : '';
		$info['desc'] = isset($template_desc[1]) ? trim($template_desc[1]) : '';
		$info['version'] = isset($template_version[1]) ? trim($template_version[1]) : '';
		$info['author'] = isset($template_author[1]) ? trim($template_author[1]) : '';
		$info['author_uri'] = isset($author_uri[1]) ? trim($author_uri[1]) : '';
		$info['dwt_code'] = isset($tpl_dwt_code[1]) ? trim($tpl_dwt_code[1]) : '';
		$info['win_goods_type'] = isset($win_goods_type[1]) ? trim($win_goods_type[1]) : '';
		$info['sort'] = substr($info['code'], -1, 1);
	}
	else {
		$info['name'] = '';
		$info['uri'] = '';
		$info['desc'] = '';
		$info['version'] = '';
		$info['author'] = '';
		$info['author_uri'] = '';
		$info['dwt_code'] = '';
		$info['sort'] = '';
	}

	return $info;
}

function get_preg_replace($str, $type = '|')
{
	$str = preg_replace("/\r\n/", ',', $str);
	$str = get_str_trim($str);
	$str = get_str_trim($str, $type);
	return $str;
}

function get_str_trim($str, $type = ',')
{
	$str = explode($type, $str);
	$str2 = '';

	for ($i = 0; $i < count($str); $i++) {
		$str2 .= trim($str[$i]) . $type;
	}

	return substr($str2, 0, -1);
}

function mc_read_txt($file)
{
	$pathfile = $file;

	if (!file_exists($pathfile)) {
		return false;
	}

	$fs = fopen($pathfile, 'r+');
	$content = fread($fs, filesize($pathfile));
	fclose($fs);

	if (!$content) {
		return false;
	}

	return $content;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once 'includes/lib_template.php';
$adminru = get_admin_ru_id();
$adminru['ru_id'] = 0;
$sql = 'select id,seller_theme,store_style from ' . $ecs->table('seller_shopinfo') . ' where ru_id = \'' . $adminru['ru_id'] . '\'';
$shop_info = $db->getRow($sql);
$sql = 'select count(*) from ' . $ecs->table('seller_shopinfo') . ' where ru_id = \'' . $adminru['ru_id'] . '\'';
$shop_id = $db->getOne($sql);

if ($shop_id < 1) {
	$lnk[] = array('text' => '设置店铺信息', 'href' => 'index.php?act=merchants_first');
	sys_msg('请先设置店铺基本信息', 0, $lnk);
	exit();
}

if ($_REQUEST['act'] == 'list') {
	admin_priv('seller_store_other');
	$curr_template = $shop_info['seller_theme'];
	$curr_style = $shop_info['store_style'];

	if (0 < $adminru['ru_id']) {
		$sql = 'SELECT sg.seller_temp FROM' . $ecs->table('seller_grade') . ' AS sg LEFT JOIN ' . $ecs->table('merchants_grade') . ' AS mg ON sg.id = mg.grade_id WHERE mg.ru_id = \'' . $adminru['ru_id'] . '\'';
		$seller_temp = $db->getOne($sql);
		$smarty->assign('seller_temp', $seller_temp);
	}

	$available_templates = array();
	$template_dir = @opendir(ROOT_PATH . 'seller_themes/');

	while ($file = readdir($template_dir)) {
		if (($file != '.') && ($file != '..') && is_dir(ROOT_PATH . 'seller_themes/' . $file) && ($file != '.svn') && ($file != 'index.dwt')) {
			$available_templates[] = get_seller_template_info($file);
		}
	}

	$available_templates = get_array_sort($available_templates, 'sort');
	@closedir($template_dir);
	$templates_style = array();

	if (0 < count($available_templates)) {
		foreach ($available_templates as $value) {
			$templates_style[$value['code']] = read_tpl_style($value['code'], 2);
		}
	}

	$db->query($sql);
	assign_query_info();
	$smarty->assign('ur_here', $_LANG['template_manage']);
	$smarty->assign('curr_tpl_style', $curr_style);
	$smarty->assign('template_style', $templates_style);
	$smarty->assign('curr_template', get_seller_template_info($curr_template, $curr_style));
	$smarty->assign('available_templates', $available_templates);
	$smarty->display('merchants_template_list.dwt');
}

if ($_REQUEST['act'] == 'install') {
	$tpl_name = trim($_GET['tpl_name']);
	$tpl_fg = 0;
	$tpl_fg = trim($_GET['tpl_fg']);
	$custom_dirname = $ecs->url();
	$preg = '/<script[\\s\\S]*?<\\/script>/i';
	$template_info = get_seller_template_info($tpl_name);
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' SET seller_theme = \'' . $tpl_name . '\', store_style = \'' . $tpl_fg . '\', win_goods_type = \'' . $template_info['win_goods_type'] . '\'' . ' WHERE ru_id = \'' . $adminru['ru_id'] . '\'';
	$step_install = $db->query($sql, 'SILENT');
	$sql = ' delete from ' . $GLOBALS['ecs']->table('seller_shopheader') . ' where seller_theme=\'' . $tpl_name . '\' and ru_id = \'' . $adminru['ru_id'] . '\'';
	$GLOBALS['db']->query($sql);
	$sql = 'select count(*) as count, content from ' . $GLOBALS['ecs']->table('seller_shopheader') . ' where seller_theme=\'' . $tpl_name . '\' and ru_id = \'' . $adminru['ru_id'] . '\'';
	$header_info = $GLOBALS['db']->getRow($sql);

	if ($header_info['count'] == 0) {
		$header_path = ROOT_PATH . 'seller_themes/' . $tpl_name . '/header.txt';

		if (file_exists($header_path)) {
			$content = file_get_contents($header_path);
			$header_content = (!empty($content) ? preg_replace($preg, '', stripslashes($content)) : '');
			$header_content = addslashes(iconv('GB2312', 'UTF-8', $header_content));

			if (3 <= strlen($header_content)) {
				$patterns = array();
				$patterns[0] = '/themes/';
				$replacements = array();
				$replacements[0] = $custom_dirname . 'themes';
				$header_content = preg_replace($patterns, $replacements, $header_content);
				$sql = 'insert into' . $GLOBALS['ecs']->table('seller_shopheader') . '(content,seller_theme,ru_id) values (\'' . $header_content . '\',\'' . $tpl_name . '\',' . $adminru['ru_id'] . ')';
				$GLOBALS['db']->query($sql);
			}
		}
	}
	else if ($header_info['content'] == '') {
		$header_path = ROOT_PATH . 'seller_themes/' . $tpl_name . '/header.txt';

		if (file_exists($header_path)) {
			$content = file_get_contents($header_path);
			$header_content = (!empty($content) ? preg_replace($preg, '', stripslashes($content)) : '');
			$header_content = addslashes(iconv('GB2312', 'UTF-8', $header_content));

			if (3 <= strlen($header_content)) {
				$patterns = array();
				$patterns[0] = '/themes/';
				$replacements = array();
				$replacements[0] = $custom_dirname . 'themes';
				$header_content = preg_replace($patterns, $replacements, $header_content);
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('seller_shopheader') . ' SET content = \'' . $header_content . '\' WHERE seller_theme = \'' . $tpl_name . '\' AND ru_id = \'' . $adminru['ru_id'] . '\'';
				$GLOBALS['db']->query($sql);
			}
		}
	}

	$sql = 'select count(id) from ' . $GLOBALS['ecs']->table('seller_shopslide') . ' where seller_theme=\'' . $tpl_name . '\' and ru_id = \'' . $adminru['ru_id'] . '\'';
	$count = $GLOBALS['db']->getOne($sql);

	if ($count == 0) {
		$silde_path = ROOT_PATH . 'seller_themes/' . $tpl_name . '/slides.txt';

		if (file_exists($silde_path)) {
			$str = mc_read_txt($silde_path);
			$str = get_preg_replace($str);
			$slide_arr = explode(',', $str);

			if ($slide_arr) {
				$sql = 'insert into ' . $GLOBALS['ecs']->table('seller_shopslide') . ' (ru_id,img_url,img_link,img_desc,is_show,seller_theme,install_img) values ';

				foreach ($slide_arr as $key => $val) {
					$val = addslashes($val);

					if (($key + 1) < count($slide_arr)) {
						$sql .= '(' . $adminru['ru_id'] . ',\'' . $val . '\',\'\',\'\',1,\'' . $tpl_name . '\', 1),';
					}
					else {
						$sql .= '(' . $adminru['ru_id'] . ',\'' . $val . '\',\'\',\'\',1,\'' . $tpl_name . '\', 1)';
					}
				}

				$GLOBALS['db']->query($sql);
			}
		}
	}

	$sql = 'select count(*) from ' . $GLOBALS['ecs']->table('seller_shopwindow') . ' where seller_theme=\'' . $tpl_name . '\' and win_type=0 and ru_id = \'' . $adminru['ru_id'] . '\'';
	$count = $GLOBALS['db']->getOne($sql);

	if ($count == 0) {
		$custom_path = ROOT_PATH . 'seller_themes/' . $tpl_name . '/custom/';
		$dir = @opendir($custom_path);

		while ($file = @readdir($dir)) {
			$file = iconv('GB2312', 'UTF-8', $file);
			if (($file != '.') && ($file != '..') && !is_dir(ROOT_PATH . 'seller_themes/' . $file)) {
				$content_path = ROOT_PATH . 'seller_themes/' . $tpl_name . '/custom/' . $file;
				$ext = pathinfo($content_path);
				$cus_name = substr($file, 0, strrpos($file, '.'));
				$win_order = str_replace('custom', '', $cus_name);

				if ($ext['extension'] == 'txt') {
					$content_path = iconv('UTF-8', 'GB2312', $content_path);
					$content = file_get_contents($content_path, true);
					$custom_content = (!empty($content) ? preg_replace($preg, '', stripslashes($content)) : '');
					$custom_content = addslashes(iconv('GB2312', 'UTF-8', $custom_content));

					if (3 <= strlen($custom_content)) {
						$patterns = array();
						$patterns[0] = '/themes/';
						$replacements = array();
						$replacements[0] = $custom_dirname . 'themes';
						$custom_content = preg_replace($patterns, $replacements, $custom_content);
						$sql = 'insert into' . $GLOBALS['ecs']->table('seller_shopwindow') . '(win_type,win_name,win_order,ru_id,is_show,win_custom,seller_theme) values (\'0\',\'' . $cus_name . '\',\'' . $win_order . '\',' . $adminru['ru_id'] . ',1,\'' . $custom_content . '\',\'' . $tpl_name . '\')';
						$GLOBALS['db']->query($sql);
					}
				}
			}
		}

		@closedir($custom_path);
	}

	if ($step_install) {
		clear_all_files();
		$error_msg = '';
		make_json_result(read_style_and_tpl($tpl_name, $tpl_fg), ' 模板安装成功');
	}
	else {
		make_json_error($db->error());
	}
}
else if ($_REQUEST['act'] == 'user_default') {
	$adminru = get_admin_ru_id();
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' SET seller_theme = \'\' WHERE ru_id = \'' . $adminru['ru_id'] . '\'';
	$GLOBALS['db']->query($sql);
	make_json_result('', ' 默认模板设置成功');
}

?>
