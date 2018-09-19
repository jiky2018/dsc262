<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function get_topic_list()
{
	$adminru = get_admin_ru_id();
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 't.topic_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);
		$filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;
		$filter['rs_id'] = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);

		if (0 < $adminru['rs_id']) {
			$filter['rs_id'] = $adminru['rs_id'];
		}

		$where = '1';
		$where .= !empty($filter['keywords']) ? ' AND t.title like \'%' . mysql_like_quote($filter['keywords']) . '%\'' : '';

		if (0 < $adminru['ru_id']) {
			$where .= ' AND t.user_id = \'' . $adminru['ru_id'] . '\' ';
		}

		if ($filter['review_status']) {
			$where .= ' AND t.review_status = \'' . $filter['review_status'] . '\' ';
		}

		$where .= get_rs_null_where('t.user_id', $filter['rs_id']);
		$filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_where = '';
		$store_search_where = '';

		if (-1 < $filter['store_search']) {
			if ($ru_id == 0) {
				if (0 < $filter['store_search']) {
					if ($_REQUEST['store_type']) {
						$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
					}

					if ($filter['store_search'] == 1) {
						$where .= ' AND t.user_id = \'' . $filter['merchant_id'] . '\' ';
					}
					else if ($filter['store_search'] == 2) {
						$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
					}
					else if ($filter['store_search'] == 3) {
						$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
					}

					if (1 < $filter['store_search']) {
						$where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . (' WHERE msi.user_id = t.user_id ' . $store_where . ') > 0 ');
					}
				}
				else {
					$where .= ' AND t.user_id = 0';
				}
			}
		}

		$where .= !empty($filter['seller_list']) ? ' AND t.user_id > 0 ' : ' AND t.user_id = 0 ';
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('topic') . ' AS t ' . (' WHERE ' . $where);
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT t.* FROM ' . $GLOBALS['ecs']->table('topic') . ' AS t ' . (' WHERE ' . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order']);
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
		$topic['url'] = $GLOBALS['ecs']->url() . 'topic.php?topic_id=' . $topic['topic_id'];
		$topic['ru_name'] = get_shop_name($topic['user_id'], 1);
		$res[] = $topic;
	}

	$arr = array('item' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function list_link($is_add = true, $text = '')
{
	$href = 'topic.php?act=list';

	if (!$is_add) {
		$href .= '&' . list_link_postfix();
	}

	if ($text == '') {
		$text = $GLOBALS['_LANG']['topic_list'];
	}

	return array('href' => $href, 'text' => $text);
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

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_visual.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$topic_style_color = array('008080', '008000', 'ffa500', 'ff0000', 'ffff00', '9acd32', 'ffd700');
$allow_suffix = array('gif', 'jpg', 'png', 'jpeg', 'bmp', 'swf');

if ($_REQUEST['act'] == 'list') {
	admin_priv('topic_manage');
	$smarty->assign('ur_here', $_LANG['09_topic']);
	$smarty->assign('full_page', 1);
	$list = get_topic_list();
	$smarty->assign('topic_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	self_seller(BASENAME($_SERVER['PHP_SELF']));
	assign_query_info();
	$smarty->assign('action_link', array('text' => $_LANG['topic_add'], 'href' => 'topic.php?act=add'));
	$smarty->display('topic_list.dwt');
}

if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
	admin_priv('topic_manage');
	$isadd = $_REQUEST['act'] == 'add';
	$smarty->assign('isadd', $isadd);
	$topic_id = empty($_REQUEST['topic_id']) ? 0 : intval($_REQUEST['topic_id']);
	include_once ROOT_PATH . 'includes/fckeditor/fckeditor.php';
	$smarty->assign('ur_here', $_LANG['09_topic']);
	$smarty->assign('action_link', list_link($isadd));
	set_default_filter();
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
		$sql = 'SELECT * FROM ' . $ecs->table('topic') . (' WHERE topic_id = \'' . $topic_id . '\'');
		$topic = $db->getRow($sql);
		$topic['start_time'] = local_date('Y-m-d H:i:s', $topic['start_time']);
		$topic['end_time'] = local_date('Y-m-d H:i:s', $topic['end_time']);
		$smarty->assign('topic', $topic);
		$smarty->assign('act', 'update');
	}
	else {
		$topic = array('title' => '', 'topic_type' => 0, 'url' => 'http://');
		$topic['start_time'] = date('Y-m-d H:i:s', time());
		$topic['end_time'] = date('Y-m-d H:i:s', time() + 4 * 86400);
		$smarty->assign('topic', $topic);
		create_html_editor('topic_intro');
		$smarty->assign('act', 'insert');
	}

	$smarty->display('topic_edit.dwt');
}
else if ($_REQUEST['act'] == 'visual') {
	$topic_id = !isset($_REQUEST['topic_id']) && empty($_REQUEST['topic_id']) ? 0 : intval($_REQUEST['topic_id']);
	get_down_topictemplates($topic_id, $adminru['ru_id']);
	$temp_type = empty($_REQUEST['temp_type']) ? '' : trim($_REQUEST['temp_type']);

	if ($temp_type == 'seller') {
		$arr['tem'] = !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
		$des = ROOT_PATH . 'data/seller_templates/seller_tem' . '/' . $arr['tem'];
	}
	else {
		$arr['tem'] = 'topic_' . $topic_id;
		$des = ROOT_PATH . 'data/topic' . '/topic_' . $adminru['ru_id'] . '/' . $arr['tem'];
	}

	if (file_exists($des . '/temp/pc_page.php')) {
		$filename = $des . '/temp/pc_page.php';
		$is_temp = 1;
	}
	else {
		$filename = $des . '/pc_page.php';
	}

	$arr['out'] = get_html_file($filename);

	if ($GLOBALS['_CFG']['open_oss'] == 1) {
		$bucket_info = get_bucket_info();

		if ($arr['out']) {
			$desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $arr['out']);
			$arr['out'] = $desc_preg['goods_desc'];
		}
	}

	if (defined('THEME_EXTENSION')) {
		$theme_extension = 1;
	}
	else {
		$theme_extension = 0;
	}

	$smarty->assign('theme_extension', $theme_extension);
	$domain = $GLOBALS['ecs']->url();
	$head = getleft_attr('head', $adminru['ru_id'], $arr['tem']);
	$content = getleft_attr('content', $adminru['ru_id'], $arr['tem']);
	$smarty->assign('head', $head);
	$smarty->assign('content', $content);
	$smarty->assign('pc_page', $arr);
	$smarty->assign('domain', $domain);
	$smarty->assign('is_temp', $is_temp);

	if ($temp_type == 'seller') {
		$smarty->assign('vis_section', 'vis_seller_store');
		$smarty->assign('admin_path', 'admin');
	}
	else {
		$smarty->assign('topic_id', $topic_id);
		$smarty->assign('vis_section', 'vis_topic');
	}

	$smarty->display('visual_topic.dwt');
}
else if ($_REQUEST['act'] == 'file_put_visual') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('suffix' => '', 'error' => '');
	$topic_type = isset($_REQUEST['topic_type']) ? addslashes($_REQUEST['topic_type']) : '';
	$content = isset($_REQUEST['content']) ? unescape($_REQUEST['content']) : '';
	$content = !empty($content) ? stripslashes($content) : '';
	$content_html = isset($_REQUEST['content_html']) ? unescape($_REQUEST['content_html']) : '';
	$content_html = !empty($content_html) ? stripslashes($content_html) : '';
	$suffix = isset($_REQUEST['suffix']) ? addslashes($_REQUEST['suffix']) : '';
	$new = isset($_REQUEST['new']) ? intval($_REQUEST['new']) : 0;
	$pc_page_name = 'pc_page.php';
	$pc_html_name = 'pc_html.php';
	$pc_nav_html = 'nav_html.php';
	$pc_head_name = 'pc_head.php';
	$type = 0;
	$ru_id = 0;

	if ($new == 1) {
		$type = 5;
	}
	else if ($topic_type == 'topic_type') {
		$nav_html = isset($_REQUEST['nav_html']) ? unescape($_REQUEST['nav_html']) : '';
		$nav_html = !empty($nav_html) ? stripslashes($nav_html) : '';
		$type = 1;
		create_html($nav_html, $adminru['ru_id'], $pc_nav_html, $suffix, 1);
		$ru_id = $adminru['ru_id'];
	}
	else {
		$head_html = isset($_REQUEST['head_html']) ? unescape($_REQUEST['head_html']) : '';
		$head_html = !empty($head_html) ? stripslashes($head_html) : '';
		create_html($head_html, 0, $pc_head_name, $suffix);
	}

	create_html($content_html, $ru_id, $pc_html_name, $suffix, $type);
	create_html($content, $ru_id, $pc_page_name, $suffix, $type);
	$result['error'] = 0;
	$result['suffix'] = $suffix;
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'header_bg') {
	include_once ROOT_PATH . '/includes/cls_image.php';
	$image = new cls_image($_CFG['bgcolor']);
	require_once ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php';
	$result = array('error' => 0, 'prompt' => '', 'content' => '');
	$hometype = isset($_REQUEST['hometype']) ? intval($_REQUEST['hometype']) : '';
	$type = isset($_REQUEST['type']) ? addslashes($_REQUEST['type']) : '';
	$name = isset($_REQUEST['name']) ? addslashes($_REQUEST['name']) : '';
	$suffix = isset($_REQUEST['suffix']) ? addslashes($_REQUEST['suffix']) : '';
	$topic_type = isset($_REQUEST['topic_type']) ? addslashes($_REQUEST['topic_type']) : '';
	$allow_file_types = '|GIF|JPG|PNG|';

	if ($_FILES[$name]) {
		$file = $_FILES[$name];
		if (isset($file['error']) && $file['error'] == 0 || !isset($file['error']) && $file['tmp_name'] != 'none') {
			if (!check_file_type($file['tmp_name'], $file['name'], $allow_file_types)) {
				$result['error'] = 1;
				$result['prompt'] = '请上传正确格式图片（' . $allow_file_types）;
			}
			else {
				$file_ext = explode('.', $file['name']);
				$ext = array_pop($file_ext);
				$tem = '';

				if ($type == 'headerbg') {
					$tem = '/head';
				}
				else if ($type == 'contentbg') {
					$tem = '/content';
				}

				if ($hometype == 1) {
					$file_dir = '../data/home_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $suffix . '/images' . $tem;
				}
				else if ($topic_type == 'topic_type') {
					$file_dir = '../data/topic/topic_' . $adminru['ru_id'] . '/' . $suffix . '/images' . $tem;
				}
				else {
					$file_dir = '../data/seller_templates/seller_tem' . '/' . $suffix . '/images' . $tem;
				}

				if (!is_dir($file_dir)) {
					make_dir($file_dir);
				}

				$bgtype = '';

				if ($type == 'headerbg') {
					$bgtype = 'head';
					$file_name = $file_dir . '/hdfile_' . gmtime() . '.' . $ext;
					$back_name = '/hdfile_' . gmtime() . '.' . $ext;
				}
				else if ($type == 'contentbg') {
					$bgtype = 'content';
					$file_name = $file_dir . '/confile_' . gmtime() . '.' . $ext;
					$back_name = '/confile_' . gmtime() . '.' . $ext;
				}
				else {
					$file_name = $file_dir . '/slide_' . gmtime() . '.' . $ext;
					$back_name = '/slide_' . gmtime() . '.' . $ext;
				}

				if (move_upload_file($file['tmp_name'], $file_name)) {
					$url = $GLOBALS['ecs']->url();
					$content_file = $file_name;
					$oss_img_url = str_replace('../', '', $content_file);
					get_oss_add_file(array($oss_img_url));

					if ($bgtype) {
						$theme = '';
						$tem_RuId = $adminru['ru_id'];

						if ($hometype == 1) {
							$theme = $GLOBALS['_CFG']['template'];
							$tem_RuId = 0;
						}

						$sql = 'SELECT id ,img_file FROM' . $ecs->table('templates_left') . ' WHERE ru_id = \'' . $tem_RuId . ('\' AND seller_templates = \'' . $suffix . '\' AND type = \'' . $bgtype . '\' AND theme = \'' . $theme . '\'');
						$templates_left = $db->getRow($sql);

						if (0 < $templates_left['id']) {
							if ($templates_left['img_file'] != '') {
								$old_oss_img_url = str_replace('../', '', $templates_left['img_file']);
								get_oss_del_file(array($old_oss_img_url));
								@unlink($templates_left['img_file']);
							}

							$sql = 'UPDATE' . $ecs->table('templates_left') . (' SET img_file = \'' . $oss_img_url . '\' WHERE ru_id = \'') . $tem_RuId . ('\' AND seller_templates = \'' . $suffix . '\' AND id=\'') . $templates_left['id'] . ('\' AND type = \'' . $bgtype . '\' AND theme = \'' . $theme . '\'');
							$db->query($sql);
						}
						else {
							$sql = 'INSERT INTO' . $ecs->table('templates_left') . ' (`ru_id`,`seller_templates`,`img_file`,`type`,`theme`) VALUES (\'' . $tem_RuId . ('\',\'' . $suffix . '\',\'' . $oss_img_url . '\',\'' . $bgtype . '\',\'' . $theme . '\')');
							$db->query($sql);
						}
					}

					if ($content_file) {
						$content_file = str_replace('../', '', $content_file);
						$content_file = get_image_path(0, $content_file);
					}

					$result['error'] = 2;
					$result['content'] = $content_file;
				}
				else {
					$result['error'] = 1;
					$result['prompt'] = '系统错误，请重新上传';
				}
			}
		}
	}
	else {
		$result['error'] = 1;
		$result['prompt'] = '请选择上传的图片';
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'generate') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'content' => '');
	$hometype = !empty($_REQUEST['hometype']) ? intval($_REQUEST['hometype']) : 0;
	$suffix = isset($_REQUEST['suffix']) ? addslashes($_REQUEST['suffix']) : 'store_tpl_1';
	$bg_color = isset($_REQUEST['bg_color']) ? stripslashes($_REQUEST['bg_color']) : '';
	$is_show = isset($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0;
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'hrad';
	$bgshow = isset($_REQUEST['bgshow']) ? addslashes($_REQUEST['bgshow']) : '';
	$bgalign = isset($_REQUEST['bgalign']) ? addslashes($_REQUEST['bgalign']) : '';
	$theme = '';
	$tem_RuId = $adminru['ru_id'];

	if ($hometype == 1) {
		$theme = $GLOBALS['_CFG']['template'];
		$tem_RuId = 0;
	}

	$sql = 'SELECT id  FROM' . $ecs->table('templates_left') . ' WHERE ru_id = \'' . $tem_RuId . ('\' AND seller_templates = \'' . $suffix . '\' AND type=\'' . $type . '\' AND theme = \'' . $theme . '\'');
	$id = $db->getOne($sql);

	if (0 < $id) {
		$sql = 'UPDATE ' . $ecs->table('templates_left') . (' SET seller_templates = \'' . $suffix . '\',bg_color = \'' . $bg_color . '\' ,if_show = \'' . $is_show . '\',bgrepeat=\'' . $bgshow . '\',align= \'' . $bgalign . '\',type=\'' . $type . '\' WHERE ru_id = \'') . $tem_RuId . ('\' AND seller_templates = \'' . $suffix . '\' AND id=\'' . $id . '\' AND type=\'' . $type . '\' AND theme = \'' . $theme . '\'');
	}
	else {
		$sql = 'INSERT INTO ' . $ecs->table('templates_left') . ' (`ru_id`,`seller_templates`,`bg_color`,`if_show`,`bgrepeat`,`align`,`type`) VALUES (\'' . $tem_RuId . ('\',\'' . $suffix . '\',\'' . $bg_color . '\',\'' . $is_show . '\',\'' . $bgshow . '\',\'' . $bgalign . '\',\'' . $type . '\')');
	}

	if ($db->query($sql) == true) {
		$result['error'] = 1;
	}
	else {
		$result['error'] = 2;
		$result['content'] = '系统出错。请重试！！！';
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'remove_img') {
	$hometype = !empty($_REQUEST['hometype']) ? intval($_REQUEST['hometype']) : 0;
	$fileimg = isset($_REQUEST['fileimg']) ? addslashes($_REQUEST['fileimg']) : '';
	$suffix = isset($_REQUEST['suffix']) ? addslashes($_REQUEST['suffix']) : '';
	$type = isset($_REQUEST['type']) ? addslashes($_REQUEST['type']) : '';
	$theme = '';
	$tem_RuId = $adminru['ru_id'];

	if ($hometype == 1) {
		$theme = $GLOBALS['_CFG']['template'];
		$tem_RuId = 0;
	}

	if ($fileimg != '') {
		@unlink($fileimg);
	}

	$sql = 'UPDATE ' . $ecs->table('templates_left') . ' SET img_file = \'\' WHERE ru_id = \'' . $tem_RuId . ('\' AND type = \'' . $type . '\' AND seller_templates = \'' . $suffix . '\' AND theme = \'' . $theme . '\'');
	$db->query($sql);
}
else if ($_REQUEST['act'] == 'downloadModal') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'message' => '');
	$code = isset($_REQUEST['suffix']) ? trim($_REQUEST['suffix']) : '';
	$dir = ROOT_PATH . 'data/topic/topic_' . $adminru['ru_id'] . '/' . $code . '/temp';
	$file = ROOT_PATH . 'data/topic/topic_' . $adminru['ru_id'] . '/' . $code;

	if (!empty($code)) {
		if (!is_dir($dir)) {
			make_dir($dir);
		}

		recurse_copy($dir, $file, 1);
		del_DirAndFile($dir);
		$result['error'] = 0;
	}

	if (!isset($GLOBALS['_CFG']['open_oss'])) {
		$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'open_oss\'';
		$is_oss = $GLOBALS['db']->getOne($sql, true);
	}
	else {
		$is_oss = $GLOBALS['_CFG']['open_oss'];
	}

	if (!isset($GLOBALS['_CFG']['server_model'])) {
		$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'server_model\'';
		$server_model = $GLOBALS['db']->getOne($sql, true);
	}
	else {
		$server_model = $GLOBALS['_CFG']['server_model'];
	}

	if ($is_oss && $server_model) {
		$dir = ROOT_PATH . 'data/topic/topic_' . $adminru['ru_id'] . '/' . $code . '/';
		$path = 'data/topic/topic_' . $adminru['ru_id'] . '/' . $code . '/';
		$file_list = get_recursive_file_oss($dir, $path, true);
		get_oss_add_file($file_list);
		dsc_unlink(ROOT_PATH . 'data/sc_file/topic/topic_' . $adminru['ru_id'] . '/' . $code . '.php');
		$id_data = read_static_cache('urlip_list', '/data/sc_file/');

		if ($pin_region_list !== false) {
			del_visual_templates($id_data, $code, 'del_topictemplates', $adminru['ru_id']);
		}
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'backmodal') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'message' => '');
	$code = isset($_REQUEST['suffix']) ? trim($_REQUEST['suffix']) : '';
	$section = isset($_REQUEST['section']) ? trim($_REQUEST['section']) : '';

	if ($section == 'vis_seller_store') {
		$dir = ROOT_PATH . 'data/seller_templates/seller_tem/' . $code . '/temp';
	}
	else {
		$dir = ROOT_PATH . 'data/topic/topic_' . $adminru['ru_id'] . '/' . $code . '/temp';
	}

	if (!empty($code)) {
		del_DirAndFile($dir);
		$result['error'] = 0;
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'get_hearder_body') {
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'message' => '');
	$smarty->assign('hearder_body', 1);
	$result['content'] = $GLOBALS['smarty']->fetch('library/pc_page.lbi');
	exit(json_encode($result));
}
else {
	if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
		admin_priv('topic_manage');
		$is_insert = $_REQUEST['act'] == 'insert';
		$topic_id = empty($_POST['topic_id']) ? 0 : intval($_POST['topic_id']);
		require ROOT_PATH . 'includes/cls_json.php';
		$start_time = local_strtotime($_POST['start_time']);
		$end_time = local_strtotime($_POST['end_time']);
		$keywords = $_POST['keywords'];
		$description = $_POST['description'];
		$record = array('title' => $_POST[topic_name], 'start_time' => $start_time, 'end_time' => $end_time, 'keywords' => $keywords, 'description' => $description, 'review_status' => 3);

		if ($is_insert) {
			$record['user_id'] = $adminru['ru_id'];
			$db->AutoExecute($ecs->table('topic'), $record, 'INSERT');
		}
		else {
			if (isset($_POST['review_status'])) {
				$review_status = !empty($_POST['review_status']) ? intval($_POST['review_status']) : 1;
				$review_content = !empty($_POST['review_content']) ? addslashes(trim($_POST['review_content'])) : '';
				$record['review_status'] = $review_status;
				$record['review_content'] = $review_content;
			}

			$db->AutoExecute($ecs->table('topic'), $record, 'UPDATE', 'topic_id=\'' . $topic_id . '\'');
		}

		clear_cache_files();
		$links[] = array('href' => 'topic.php', 'text' => $_LANG['back_list']);
		sys_msg($_LANG['succed'], 0, $links);
	}
	else if ($_REQUEST['act'] == 'batch') {
		admin_priv('topic_manage');

		if (isset($_POST['type'])) {
			if ($_POST['type'] == 'batch_remove') {
				get_del_batch($_POST['checkboxes'], intval($_GET['id']), array('topic_img', 'title_pic'), 'topic_id', 'topic', 1);
				$sql = 'DELETE FROM ' . $ecs->table('topic') . ' WHERE ';

				if (!empty($_POST['checkboxes'])) {
					$sql .= db_create_in($_POST['checkboxes'], 'topic_id');

					foreach ($_POST['checkboxes'] as $v) {
						if (0 < $v) {
							$suffix = 'topic_' . $v;
							$dir = ROOT_PATH . 'data/topic/topic_' . $adminru['ru_id'] . '/' . $suffix;
							$rmdir = del_DirAndFile($dir);
						}
					}
				}
				else if (!empty($_GET['id'])) {
					$_GET['id'] = intval($_GET['id']);
					$sql .= 'topic_id = \'' . $_GET['id'] . '\'';
					$suffix = 'topic_' . $_GET['id'];
					$dir = ROOT_PATH . 'data/topic/topic_' . $adminru['ru_id'] . '/' . $suffix;
					$rmdir = del_DirAndFile($dir);
				}
				else {
					exit();
				}

				$db->query($sql);
				clear_cache_files();

				if (!empty($_REQUEST['is_ajax'])) {
					$url = 'topic.php?act=query&' . str_replace('act=delete', '', $_SERVER['QUERY_STRING']);
					ecs_header('Location: ' . $url . "\n");
					exit();
				}

				$links[] = array('href' => 'topic.php', 'text' => $_LANG['back_list']);
				sys_msg($_LANG['succed'], 0, $links);
			}
			else if ($_POST['type'] == 'review_to') {
				$ids = !empty($_POST['checkboxes']) ? join(',', $_POST['checkboxes']) : 0;
				$review_status = $_POST['review_status'];
				$sql = 'UPDATE ' . $ecs->table('topic') . (' SET review_status = \'' . $review_status . '\' ') . ' WHERE topic_id ' . db_create_in($ids);

				if ($db->query($sql)) {
					$lnk[] = array('text' => $_LANG['back_list'], 'href' => 'topic.php?act=list&seller_list=1&' . list_link_postfix());
					sys_msg('专题管理审核状态设置成功', 0, $lnk);
				}
			}
		}
	}
	else if ($_REQUEST['act'] == 'delete') {
		admin_priv('topic_manage');
		get_del_batch($_POST['checkboxes'], intval($_GET['id']), array('topic_img', 'title_pic'), 'topic_id', 'topic', 1);
		$sql = 'DELETE FROM ' . $ecs->table('topic') . ' WHERE ';

		if (!empty($_POST['checkboxes'])) {
			$sql .= db_create_in($_POST['checkboxes'], 'topic_id');

			foreach ($_POST['checkboxes'] as $v) {
				if (0 < $v) {
					$suffix = 'topic_' . $v;
					$dir = ROOT_PATH . 'data/topic/topic_' . $adminru['ru_id'] . '/' . $suffix;
					$rmdir = del_DirAndFile($dir);
				}
			}
		}
		else if (!empty($_GET['id'])) {
			$_GET['id'] = intval($_GET['id']);
			$sql .= 'topic_id = \'' . $_GET['id'] . '\'';
			$suffix = 'topic_' . $_GET['id'];
			$dir = ROOT_PATH . 'data/topic/topic_' . $adminru['ru_id'] . '/' . $suffix;
			$rmdir = del_DirAndFile($dir);
		}
		else {
			exit();
		}

		$db->query($sql);
		clear_cache_files();

		if (!empty($_REQUEST['is_ajax'])) {
			$url = 'topic.php?act=query&' . str_replace('act=delete', '', $_SERVER['QUERY_STRING']);
			ecs_header('Location: ' . $url . "\n");
			exit();
		}

		$links[] = array('href' => 'topic.php', 'text' => $_LANG['back_list']);
		sys_msg($_LANG['succed'], 0, $links);
	}
	else if ($_REQUEST['act'] == 'query') {
		$topic_list = get_topic_list();
		$smarty->assign('topic_list', $topic_list['item']);
		$smarty->assign('filter', $topic_list['filter']);
		$smarty->assign('record_count', $topic_list['record_count']);
		$smarty->assign('page_count', $topic_list['page_count']);
		$smarty->assign('use_storage', empty($_CFG['use_storage']) ? 0 : 1);
		$sort_flag = sort_flag($topic_list['filter']);
		$smarty->assign($sort_flag['tag'], $sort_flag['img']);
		$tpl = 'topic_list.dwt';
		make_json_result($smarty->fetch($tpl), '', array('filter' => $topic_list['filter'], 'page_count' => $topic_list['page_count']));
	}
}

?>
