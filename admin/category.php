<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function cat_update($cat_id, $args)
{
	if (empty($args) || empty($cat_id)) {
		return false;
	}

	return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('category'), $args, 'update', 'cat_id=\'' . $cat_id . '\'');
}

function get_attr_list()
{
	$sql = 'SELECT a.attr_id, a.cat_id, a.attr_name ' . ' FROM ' . $GLOBALS['ecs']->table('attribute') . ' AS a,  ' . $GLOBALS['ecs']->table('goods_type') . ' AS c ' . ' WHERE  a.cat_id = c.cat_id AND c.enabled = 1 ' . ' ORDER BY a.cat_id , a.sort_order';
	$arr = $GLOBALS['db']->getAll($sql);
	$list = array();

	foreach ($arr as $val) {
		$list[$val['cat_id']][] = array($val['attr_id'] => $val['attr_name']);
	}

	return $list;
}

function insert_cat_recommend($recommend_type, $cat_id)
{
	if (!empty($recommend_type)) {
		$recommend_res = $GLOBALS['db']->getAll('SELECT recommend_type FROM ' . $GLOBALS['ecs']->table('cat_recommend') . ' WHERE cat_id=' . $cat_id);

		if (empty($recommend_res)) {
			foreach ($recommend_type as $data) {
				$data = intval($data);
				$GLOBALS['db']->query('INSERT INTO ' . $GLOBALS['ecs']->table('cat_recommend') . ('(cat_id, recommend_type) VALUES (\'' . $cat_id . '\', \'' . $data . '\')'));
			}
		}
		else {
			$old_data = array();

			foreach ($recommend_res as $data) {
				$old_data[] = $data['recommend_type'];
			}

			$delete_array = array_diff($old_data, $recommend_type);

			if (!empty($delete_array)) {
				$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('cat_recommend') . (' WHERE cat_id=' . $cat_id . ' AND recommend_type ') . db_create_in($delete_array));
			}

			$insert_array = array_diff($recommend_type, $old_data);

			if (!empty($insert_array)) {
				foreach ($insert_array as $data) {
					$data = intval($data);
					$GLOBALS['db']->query('INSERT INTO ' . $GLOBALS['ecs']->table('cat_recommend') . ('(cat_id, recommend_type) VALUES (\'' . $cat_id . '\', \'' . $data . '\')'));
				}
			}
		}
	}
	else {
		$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('cat_recommend') . ' WHERE cat_id=' . $cat_id);
	}
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('category'), $db, 'cat_id', 'cat_name');

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$adminru = get_admin_ru_id();
$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => '03_category_list'));
$smarty->assign('commission_model', $_CFG['commission_model']);

if ($_REQUEST['act'] == 'list') {
	$parent_id = !isset($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
	if (isset($_REQUEST['back_level']) && 0 < $_REQUEST['back_level']) {
		$level = $_REQUEST['back_level'] - 1;
		$parent_id = $db->getOne('SELECT parent_id FROM ' . $ecs->table('category') . (' WHERE cat_id = \'' . $parent_id . '\''), true);
	}
	else {
		$level = isset($_REQUEST['level']) ? $_REQUEST['level'] + 1 : 0;
	}

	$smarty->assign('level', $level);
	$smarty->assign('parent_id', $parent_id);
	$cat_list = get_cat_level($parent_id, $level);
	$smarty->assign('cat_info', $cat_list);
	$smarty->assign('ru_id', $adminru['ru_id']);

	if ($adminru['ru_id'] == 0) {
		$smarty->assign('action_link', array('href' => 'category.php?act=add', 'text' => $_LANG['04_category_add']));
	}

	$smarty->assign('ur_here', $_LANG['03_category_list']);
	$smarty->assign('full_page', 1);
	$cat_level = array('一', '二', '三', '四', '五', '六', '气', '八', '九', '十');
	$smarty->assign('cat_level', $cat_level[$level]);
	$smarty->assign('commission_model', $_CFG['commission_model']);
	assign_query_info();
	$smarty->display('category_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$cat_list = get_cat_level();
	$smarty->assign('cat_info', $cat_list);
	$smarty->assign('ru_id', $adminru['ru_id']);
	make_json_result($smarty->fetch('category_list.dwt'));
}

if ($_REQUEST['act'] == 'add') {
	admin_priv('cat_manage');
	$parent_id = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);

	if (!empty($parent_id)) {
		set_default_filter(0, $parent_id);
		$smarty->assign('parent_category', get_every_category($parent_id));
		$smarty->assign('parent_id', $parent_id);
	}
	else {
		set_default_filter();
	}

	$type_level = get_type_cat_arr(0, 0, 0, $adminru['ru_id']);
	$smarty->assign('type_level', $type_level);
	$smarty->assign('ur_here', $_LANG['04_category_add']);
	$smarty->assign('action_link', array('href' => 'category.php?act=list', 'text' => $_LANG['03_category_list']));
	$smarty->assign('goods_type_list', goods_type_list(0));
	$smarty->assign('attr_list', get_attr_list());
	$smarty->assign('form_act', 'insert');
	$smarty->assign('cat_info', array('is_show' => 1));
	$smarty->assign('ru_id', $adminru['ru_id']);
	assign_query_info();
	$smarty->display('category_info.dwt');
}

if ($_REQUEST['act'] == 'delete_icon') {
	admin_priv('cat_manage');
	$result = array('error' => 0, 'msg' => '');
	$cat_id = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$cat_info = get_cat_info($cat_id);

	if (!empty($cat_info)) {
		$sql = ' update ' . $GLOBALS['ecs']->table('category') . ' set cat_icon=\'\' where cat_id= ' . $cat_id;

		if ($GLOBALS['db']->query($sql)) {
			@unlink(ROOT_PATH . $cat_info['cat_icon']);
			$result = array('error' => 1, 'msg' => '成功删除');
		}
	}

	exit(json_encode($result));
}

if ($_REQUEST['act'] == 'insert') {
	admin_priv('cat_manage');
	$cat['cat_id'] = !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
	$cat['parent_id'] = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
	$cat['level'] = count(get_select_category($cat['parent_id'], 1, true)) - 2;
	if (1 < $cat['level'] && $adminru['ru_id'] == 0) {
		$link[0]['text'] = $_LANG['go_back'];

		if (0 < $cat['cat_id']) {
			$link[0]['href'] = 'category.php?act=edit&cat_id=' . $cat['cat_id'];
		}
		else {
			$link[0]['href'] = 'category.php?act=add&parent_id=' . $cat['parent_id'];
		}

		sys_msg('平台最多只能设置三级分类', 0, $link);
		exit();
	}

	if ($cat['level'] < 2 && 0 < $adminru['ru_id']) {
		$link[0]['text'] = $_LANG['go_back'];

		if (0 < $cat['cat_id']) {
			$link[0]['href'] = 'category.php?act=edit&cat_id=' . $cat['cat_id'];
		}
		else {
			$link[0]['href'] = 'category.php?act=add&parent_id=' . $cat['parent_id'];
		}

		sys_msg('您目前的权限只能添加四级分类', 0, $link);
		exit();
	}

	if (!empty($_FILES['cat_icon']['name'])) {
		if (200000 < $_FILES['cat_icon']['size']) {
			sys_msg('上传图片不得大于200kb', 0, $link);
		}

		$icon_name = explode('.', $_FILES['cat_icon']['name']);
		$key = count($icon_name);
		$type = $icon_name[$key - 1];
		if ($type != 'jpg' && $type != 'png' && $type != 'gif') {
			sys_msg('请上传jpg,gif,png格式图片', 0, $link);
		}

		$imgNamePrefix = time() . mt_rand(1001, 9999);
		$imgDir = ROOT_PATH . 'images/cat_icon';

		if (!file_exists($imgDir)) {
			mkdir($imgDir);
		}

		$imgName = $imgDir . '/' . $imgNamePrefix . '.' . $type;
		$saveDir = 'images/cat_icon' . '/' . $imgNamePrefix . '.' . $type;
		move_uploaded_file($_FILES['cat_icon']['tmp_name'], $imgName);
		$cat['cat_icon'] = $saveDir;
		get_oss_add_file(array($cat['cat_icon']));
	}

	if (!empty($_FILES['touch_icon']['name'])) {
		if (200000 < $_FILES['touch_icon']['size']) {
			sys_msg('上传图片不得大于200kb', 0, $link);
		}

		$icon_name = explode('.', $_FILES['touch_icon']['name']);
		$key = count($icon_name);
		$type = $icon_name[$key - 1];
		if ($type != 'jpg' && $type != 'png' && $type != 'gif') {
			sys_msg('请上传jpg,gif,png格式图片', 0, $link);
		}

		$touch_iconPrefix = time() . mt_rand(1001, 9999);
		$touch_iconDir = '../' . DATA_DIR . '/touch_icon';

		if (!file_exists($touch_iconDir)) {
			mkdir($touch_iconDir);
		}

		$touchimgName = $touch_iconDir . '/' . $touch_iconPrefix . '.' . $type;
		$touchsaveDir = DATA_DIR . '/touch_icon' . '/' . $touch_iconPrefix . '.' . $type;
		move_uploaded_file($_FILES['touch_icon']['tmp_name'], $touchimgName);
		$cat['touch_icon'] = $touchsaveDir;
		get_oss_add_file(array($cat['touch_icon']));
	}

	$cat['commission_rate'] = !empty($_POST['commission_rate']) ? intval($_POST['commission_rate']) : 0;
	if (100 < $cat['commission_rate'] || $cat['commission_rate'] < 0) {
		sys_msg('佣金比率为0-100以内，请重新设置', 0, $link);
	}

	$cat['style_icon'] = !empty($_POST['style_icon']) ? trim($_POST['style_icon']) : 'other';
	$cat['sort_order'] = !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
	$cat['keywords'] = !empty($_POST['keywords']) ? trim($_POST['keywords']) : '';
	$cat['cat_desc'] = !empty($_POST['cat_desc']) ? $_POST['cat_desc'] : '';
	$cat['measure_unit'] = !empty($_POST['measure_unit']) ? trim($_POST['measure_unit']) : '';
	$cat['cat_name'] = !empty($_POST['cat_name']) ? trim($_POST['cat_name']) : '';
	$cat['cat_alias_name'] = !empty($_POST['cat_alias_name']) ? trim($_POST['cat_alias_name']) : '';
	$pin = new pin();
	$pinyin = $pin->Pinyin($cat['cat_name'], 'UTF8');
	$cat['pinyin_keyword'] = $pinyin;
	$cat['show_in_nav'] = !empty($_POST['show_in_nav']) ? intval($_POST['show_in_nav']) : 0;
	$cat['style'] = !empty($_POST['style']) ? trim($_POST['style']) : '';
	$cat['is_show'] = !empty($_POST['is_show']) ? intval($_POST['is_show']) : 0;
	$cat['is_top_show'] = !empty($_POST['is_top_show']) ? intval($_POST['is_top_show']) : 0;
	$cat['is_top_style'] = !empty($_POST['is_top_style']) ? intval($_POST['is_top_style']) : 0;
	$cat['top_style_tpl'] = !empty($_POST['top_style_tpl']) ? $_POST['top_style_tpl'] : 0;
	$cat['grade'] = !empty($_POST['grade']) ? intval($_POST['grade']) : 0;
	$cat['filter_attr'] = !empty($_POST['filter_attr']) ? implode(',', array_unique(array_diff($_POST['filter_attr'], array(0)))) : 0;
	$cat['cat_recommend'] = !empty($_POST['cat_recommend']) ? $_POST['cat_recommend'] : array();

	if (cat_exists($cat['cat_name'], $cat['parent_id'])) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['catname_exist'], 0, $link);
	}

	if (10 < $cat['grade'] || $cat['grade'] < 0) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['grade_error'], 0, $link);
	}

	$cat_name = explode(',', $cat['cat_name']);

	if (1 < count($cat_name)) {
		$cat['is_show_merchants'] = !empty($_POST['is_show_merchants']) ? intval($_POST['is_show_merchants']) : 0;
		get_bacth_category($cat_name, $cat, $adminru['ru_id']);
		clear_cache_files();
		$link[0]['text'] = $_LANG['continue_add'];
		$link[0]['href'] = 'category.php?act=add&parent_id=' . $cat['parent_id'];
		$link[1]['text'] = $_LANG['back_list'];
		$link[1]['href'] = 'category.php?act=list&parent_id=' . $cat['parent_id'] . '&level=' . $cat['level'];
		sys_msg($_LANG['catadd_succed'], 0, $link);
	}
	else if ($db->autoExecute($ecs->table('category'), $cat) !== false) {
		$cat_id = $db->insert_id();
		$cache['category']['type'] = 'add_edit';
		$cache['category']['is_show'] = 1;
		$cache['category']['cache_path'] = 'data/sc_file/category/';
		get_admin_seller_static_cache($cache);

		if ($cat['show_in_nav'] == 1) {
			$vieworder = $db->getOne('SELECT max(vieworder) FROM ' . $ecs->table('nav') . ' WHERE type = \'middle\'');
			$vieworder += 2;
			$sql = 'INSERT INTO ' . $ecs->table('nav') . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type)' . ' VALUES(\'' . $cat['cat_name'] . '\', \'c\', \'' . $db->insert_id() . ('\',\'1\',\'' . $vieworder . '\',\'0\', \'') . build_uri('category', array('cid' => $cat_id), $cat['cat_name']) . '\',\'middle\')';
			$db->query($sql);
		}

		insert_cat_recommend($cat['cat_recommend'], $cat_id);
		admin_log($_POST['cat_name'], 'add', 'category');
		$dt_list = isset($_POST['document_title']) ? $_POST['document_title'] : array();
		$dt_id = isset($_POST['dt_id']) ? $_POST['dt_id'] : array();
		get_documentTitle_insert_update($dt_list, $cat_id, $dt_id);
		clear_cache_files();
		$link[0]['text'] = $_LANG['continue_add'];
		$link[0]['href'] = 'category.php?act=add&parent_id=' . $cat['parent_id'];
		$link[1]['text'] = $_LANG['back_list'];
		$link[1]['href'] = 'category.php?act=list&parent_id=' . $cat['parent_id'] . '&level=' . $cat['level'];
		sys_msg($_LANG['catadd_succed'], 0, $link);
	}
}

if ($_REQUEST['act'] == 'edit') {
	admin_priv('cat_manage');
	$cat_id = intval($_REQUEST['cat_id']);
	$cat_info = get_cat_info($cat_id);
	$attr_list = get_attr_list();
	$filter_attr_list = array();
	$smarty->assign('parent_id', $cat_info['parent_id']);
	$smarty->assign('parent_category', get_every_category($cat_info['parent_id']));
	set_default_filter(0, $cat_info['parent_id']);

	if ($cat_info['filter_attr']) {
		$filter_attr = explode(',', $cat_info['filter_attr']);

		foreach ($filter_attr as $k => $v) {
			$attr_cat_id = $db->getOne('SELECT cat_id FROM ' . $ecs->table('attribute') . ' WHERE attr_id = \'' . intval($v) . '\'');
			$filter_attr_list[$k]['goods_type_list'] = goods_type_list($attr_cat_id);
			$filter_attr_list[$k]['goods_type'] = $attr_cat_id;
			$filter_attr_list[$k]['filter_attr'] = $v;
			$attr_option = array();
			if (isset($attr_list[$attr_cat_id]) && $attr_list[$attr_cat_id]) {
				foreach ($attr_list[$attr_cat_id] as $val) {
					$attr_option[key($val)] = current($val);
				}
			}

			$filter_attr_list[$k]['option'] = $attr_option;
		}

		$smarty->assign('filter_attr_list', $filter_attr_list);
	}
	else {
		$attr_cat_id = 0;
	}

	$type_level = get_type_cat_arr(0, 0, 0, $adminru['ru_id']);
	$smarty->assign('type_level', $type_level);

	if ($cat_info['parent_id'] == 0) {
		$cat_name_arr = explode('、', $cat_info['cat_name']);
		$smarty->assign('cat_name_arr', $cat_name_arr);
	}

	$smarty->assign('attr_list', $attr_list);
	$smarty->assign('attr_cat_id', $attr_cat_id);
	$smarty->assign('ur_here', $_LANG['category_edit']);
	$smarty->assign('action_link', array('text' => $_LANG['03_category_list'], 'href' => 'category.php?act=list'));
	$res = $db->getAll('SELECT recommend_type FROM ' . $ecs->table('cat_recommend') . ' WHERE cat_id=' . $cat_id);

	if (!empty($res)) {
		$cat_recommend = array();

		foreach ($res as $data) {
			$cat_recommend[$data['recommend_type']] = 1;
		}

		$smarty->assign('cat_recommend', $cat_recommend);
	}

	$sql = 'select dt_id, dt_title from ' . $ecs->table('merchants_documenttitle') . (' where cat_id = \'' . $cat_id . '\'');
	$title_list = $db->getAll($sql);
	$smarty->assign('title_list', $title_list);
	$smarty->assign('cat_id', $cat_id);
	$smarty->assign('ru_id', $adminru['ru_id']);
	$smarty->assign('cat_info', $cat_info);
	$smarty->assign('form_act', 'update');
	$smarty->assign('goods_type_list', goods_type_list(0));
	assign_query_info();
	$smarty->display('category_info.dwt');
}
else if ($_REQUEST['act'] == 'titleFileView') {
	$cat_id = intval($_REQUEST['cat_id']);
	$sql = 'select dt_id, dt_title from ' . $ecs->table('merchants_documenttitle') . (' where cat_id = \'' . $cat_id . '\'');
	$title_list = $db->getAll($sql);
	$smarty->assign('title_list', $title_list);
	$smarty->assign('cat_id', $cat_id);
	$smarty->assign('form_act', 'title_update');
	$sql = 'select cat_name from ' . $ecs->table('category') . (' where cat_id = \'' . $cat_id . '\'');
	$cat_name = $db->getOne($sql);
	$smarty->assign('cat_name', $cat_name);
	$smarty->assign('action_link', array('href' => 'category.php?act=edit&cat_id=' . $cat_id, 'text' => $_LANG['go_back']));
	assign_query_info();
	$smarty->display('category_titleFileView.dwt');
}
else if ($_REQUEST['act'] == 'title_update') {
	$cat_id = intval($_REQUEST['cat_id']);
	$dt_list = isset($_POST['document_title']) ? $_POST['document_title'] : array();
	$dt_id = isset($_POST['dt_id']) ? $_POST['dt_id'] : array();
	get_documentTitle_insert_update($dt_list, $cat_id, $dt_id);
	clear_cache_files();
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'category.php?act=titleFileView&cat_id=' . $cat_id);
	sys_msg($_LANG['title_catedit_succed'], 0, $link);
}
else if ($_REQUEST['act'] == 'add_category') {
	$parent_id = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
	$category = empty($_REQUEST['cat']) ? '' : json_str_iconv(trim($_REQUEST['cat']));

	if (cat_exists($category, $parent_id)) {
		make_json_error($_LANG['catname_exist']);
	}
	else {
		$sql = 'INSERT INTO ' . $ecs->table('category') . '(cat_name, parent_id, is_show)' . ('VALUES ( \'' . $category . '\', \'' . $parent_id . '\', 1)');
		$db->query($sql);
		$category_id = $db->insert_id();
		$arr = array('parent_id' => $parent_id, 'id' => $category_id, 'cat' => $category);
		clear_cache_files();
		exit(json_encode($arr));
	}
}

if ($_REQUEST['act'] == 'update') {
	admin_priv('cat_manage');
	$cat_id = $cat['cat_id'] = !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
	$cat['parent_id'] = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
	$cat['level'] = count(get_select_category($cat['parent_id'], 1, true)) - 2;
	$old_cat_name = isset($_REQUEST['old_cat_name']) ? $_REQUEST['old_cat_name'] : '';
	$link[0]['text'] = $_LANG['go_back'];

	if (0 < $cat['cat_id']) {
		$link[0]['href'] = 'category.php?act=edit&cat_id=' . $cat['cat_id'];
	}
	else {
		$link[0]['href'] = 'category.php?act=add&parent_id=' . $cat['parent_id'];
	}

	$reject_cat = arr_foreach(cat_list($cat_id, 1, 1));
	if ($cat['parent_id'] == $cat_id || in_array($cat['parent_id'], $reject_cat)) {
		sys_msg('分类本身或自身下级不能作为父级成员！', 1, $link);
		exit();
	}

	if (1 < $cat['level'] && $adminru['ru_id'] == 0) {
		sys_msg('平台最多只能设置三级分类', 1, $link);
		exit();
	}

	if ($cat['level'] < 2 && 0 < $adminru['ru_id']) {
		sys_msg('您目前的权限只能添加四级分类', 1, $link);
		exit();
	}

	if (!empty($_FILES['cat_icon']['name'])) {
		if (200000 < $_FILES['cat_icon']['size']) {
			sys_msg('上传图片不得大于200kb', 1, $link);
		}

		$icon_name = explode('.', $_FILES['cat_icon']['name']);
		$key = count($icon_name);
		$type = $icon_name[$key - 1];
		if ($type != 'jpg' && $type != 'png' && $type != 'gif' && $type != 'jpeg') {
			sys_msg('请上传jpg,gif,png,jpeg格式图片', 1, $link);
		}

		$imgNamePrefix = time() . mt_rand(1001, 9999);
		$imgDir = ROOT_PATH . 'images/cat_icon';

		if (!file_exists($imgDir)) {
			mkdir($imgDir);
		}

		$imgName = $imgDir . '/' . $imgNamePrefix . '.' . $type;
		$saveDir = 'images/cat_icon' . '/' . $imgNamePrefix . '.' . $type;
		move_uploaded_file($_FILES['cat_icon']['tmp_name'], $imgName);
		$cat['cat_icon'] = $saveDir;
		get_oss_add_file(array($cat['cat_icon']));

		if (!empty($cat_id)) {
			$cat_info = get_cat_info($cat_id);
			@unlink(ROOT_PATH . $cat_info['cat_icon']);
			get_oss_del_file(array($cat_info['cat_icon']));
		}
	}

	if (!empty($_FILES['touch_icon']['name'])) {
		if (200000 < $_FILES['touch_icon']['size']) {
			sys_msg('上传图片不得大于200kb', 0, $link);
		}

		$icon_name = explode('.', $_FILES['touch_icon']['name']);
		$key = count($icon_name);
		$type = $icon_name[$key - 1];
		if ($type != 'jpg' && $type != 'png' && $type != 'gif' && $type != 'jpeg') {
			sys_msg('请上传jpg,gif,png,jpeg格式图片', 0, $link);
		}

		$touch_iconPrefix = time() . mt_rand(1001, 9999);
		$touch_iconDir = '../' . DATA_DIR . '/touch_icon';

		if (!file_exists($touch_iconDir)) {
			mkdir($touch_iconDir);
		}

		$touchimgName = $touch_iconDir . '/' . $touch_iconPrefix . '.' . $type;
		$touchsaveDir = DATA_DIR . '/touch_icon' . '/' . $touch_iconPrefix . '.' . $type;
		move_uploaded_file($_FILES['touch_icon']['tmp_name'], $touchimgName);
		$cat['touch_icon'] = $touchsaveDir;
		get_oss_add_file(array($cat['touch_icon']));

		if (!empty($cat_id)) {
			$cat_info = get_cat_info($cat_id);
			@unlink(ROOT_PATH . $cat_info['touch_icon']);
			get_oss_del_file(array($cat_info['touch_icon']));
		}
	}

	$cat['commission_rate'] = !empty($_POST['commission_rate']) ? intval($_POST['commission_rate']) : 0;
	if (100 < $cat['commission_rate'] || $cat['commission_rate'] < 0) {
		sys_msg('佣金比率为0-100以内，请重新设置', 0, $link);
	}

	$cat['style_icon'] = !empty($_POST['style_icon']) ? trim($_POST['style_icon']) : 'other';
	$cat['sort_order'] = !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
	$cat['keywords'] = !empty($_POST['keywords']) ? trim($_POST['keywords']) : '';
	$cat['cat_desc'] = !empty($_POST['cat_desc']) ? $_POST['cat_desc'] : '';
	$cat['measure_unit'] = !empty($_POST['measure_unit']) ? trim($_POST['measure_unit']) : '';
	$cat['cat_name'] = !empty($_POST['cat_name']) ? trim($_POST['cat_name']) : '';
	$cat['cat_alias_name'] = !empty($_POST['cat_alias_name']) ? trim($_POST['cat_alias_name']) : '';
	$cat['category_links'] = !empty($_POST['category_links']) ? $_POST['category_links'] : '';
	$cat['category_topic'] = !empty($_POST['category_topic']) ? $_POST['category_topic'] : '';
	$pin = new pin();
	$pinyin = $pin->Pinyin($cat['cat_name'], 'UTF8');
	$cat['pinyin_keyword'] = $pinyin;
	$cat['is_show'] = !empty($_POST['is_show']) ? intval($_POST['is_show']) : 0;
	$cat['is_top_show'] = !empty($_POST['is_top_show']) ? intval($_POST['is_top_show']) : 0;
	$cat['is_top_style'] = !empty($_POST['is_top_style']) ? intval($_POST['is_top_style']) : 0;
	$cat['top_style_tpl'] = !empty($_POST['top_style_tpl']) ? $_POST['top_style_tpl'] : 0;
	$cat['floor_style_tpl'] = !empty($_POST['floor_style_tpl']) ? $_POST['floor_style_tpl'] : 0;
	$cat['show_in_nav'] = !empty($_POST['show_in_nav']) ? intval($_POST['show_in_nav']) : 0;
	$cat['style'] = !empty($_POST['style']) ? trim($_POST['style']) : '';
	$cat['grade'] = !empty($_POST['grade']) ? intval($_POST['grade']) : 0;
	$cat['filter_attr'] = !empty($_POST['filter_attr']) ? implode(',', array_unique(array_diff($_POST['filter_attr'], array(0)))) : 0;
	$cat['cat_recommend'] = !empty($_POST['cat_recommend']) ? $_POST['cat_recommend'] : array();

	if ($cat['cat_name'] != $old_cat_name) {
		if (cat_exists($cat['cat_name'], $cat['parent_id'], $cat_id)) {
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
			sys_msg($_LANG['catname_exist'], 0, $link);
		}
	}

	$children = get_array_keys_cat($cat_id);

	if (in_array($cat['parent_id'], $children)) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['is_leaf_error'], 0, $link);
	}

	if (10 < $cat['grade'] || $cat['grade'] < 0) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['grade_error'], 0, $link);
	}

	$dat = $db->getRow('SELECT cat_name, show_in_nav FROM ' . $ecs->table('category') . (' WHERE cat_id = \'' . $cat_id . '\''));

	if ($db->autoExecute($ecs->table('category'), $cat, 'UPDATE', 'cat_id=\'' . $cat_id . '\'')) {
		$cache['category']['type'] = 'add_edit';
		$cache['category']['is_show'] = 1;
		$cache['category']['cache_path'] = 'data/sc_file/category/';
		get_admin_seller_static_cache($cache);

		if ($cat['cat_name'] != $dat['cat_name']) {
			$sql = 'UPDATE ' . $ecs->table('nav') . ' SET name = \'' . $cat['cat_name'] . '\' WHERE ctype = \'c\' AND cid = \'' . $cat_id . '\' AND type = \'middle\'';
			$db->query($sql);
		}

		if ($cat['show_in_nav'] != $dat['show_in_nav']) {
			if ($cat['show_in_nav'] == 1) {
				$nid = $db->getOne('SELECT id FROM ' . $ecs->table('nav') . ' WHERE ctype = \'c\' AND cid = \'' . $cat_id . '\' AND type = \'middle\'');

				if (empty($nid)) {
					$vieworder = $db->getOne('SELECT max(vieworder) FROM ' . $ecs->table('nav') . ' WHERE type = \'middle\'');
					$vieworder += 2;
					$uri = build_uri('category', array('cid' => $cat_id), $cat['cat_name']);
					$sql = 'INSERT INTO ' . $ecs->table('nav') . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type) VALUES(\'' . $cat['cat_name'] . ('\', \'c\', \'' . $cat_id . '\',\'1\',\'' . $vieworder . '\',\'0\', \'') . $uri . '\',\'middle\')';
				}
				else {
					$sql = 'UPDATE ' . $ecs->table('nav') . ' SET ifshow = 1 WHERE ctype = \'c\' AND cid = \'' . $cat_id . '\' AND type = \'middle\'';
				}

				$db->query($sql);
			}
			else {
				$db->query('UPDATE ' . $ecs->table('nav') . ' SET ifshow = 0 WHERE ctype = \'c\' AND cid = \'' . $cat_id . '\' AND type = \'middle\'');
			}
		}

		insert_cat_recommend($cat['cat_recommend'], $cat_id);
		$dt_list = isset($_POST['document_title']) ? $_POST['document_title'] : array();
		$dt_id = isset($_POST['dt_id']) ? $_POST['dt_id'] : array();
		get_documentTitle_insert_update($dt_list, $cat_id, $dt_id);
		clear_cache_files();
		admin_log($_POST['cat_name'], 'edit', 'category');
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'category.php?act=list&parent_id=' . $cat['parent_id'] . '&level=' . $cat['level']);
		sys_msg($_LANG['catedit_succed'], 0, $link);
	}
}

if ($_REQUEST['act'] == 'move') {
	check_authz_json('cat_drop');
	$cat_id = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$smarty->assign('parent_id', $cat_id);
	$smarty->assign('parent_category', get_every_category($cat_id));
	set_default_filter(0, $cat_id);
	$smarty->assign('ur_here', $_LANG['move_goods']);
	$smarty->assign('action_link', array('href' => 'category.php?act=list', 'text' => $_LANG['03_category_list']));
	$smarty->assign('form_act', 'move_cat');
	$html = $smarty->fetch('library/move_category.lbi');
	clear_cache_files();
	make_json_result($html);
}

if ($_REQUEST['act'] == 'move_cat') {
	admin_priv('cat_drop');
	$cat_id = !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
	$target_cat_id = !empty($_POST['target_cat_id']) ? intval($_POST['target_cat_id']) : 0;
	if ($cat_id == 0 || $target_cat_id == 0) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'category.php?act=move');
		sys_msg($_LANG['cat_move_empty'], 0, $link);
	}

	$children = get_children($cat_id, 0, 0, 'category', 'cat_id');
	$sql = 'UPDATE ' . $ecs->table('goods') . (' SET cat_id = \'' . $target_cat_id . '\' ') . ('WHERE ' . $children . '  AND user_id = \'') . $adminru['ru_id'] . '\'';

	if ($db->query($sql)) {
		clear_cache_files();
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'category.php?act=list');
		sys_msg($_LANG['move_cat_success'], 0, $link);
	}
}

if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('cat_manage');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);

	if (cat_update($id, array('sort_order' => $val))) {
		clear_cache_files();
		make_json_result($val);
	}
	else {
		make_json_error($db->error());
	}
}

if ($_REQUEST['act'] == 'edit_measure_unit') {
	check_authz_json('cat_manage');
	$id = intval($_POST['id']);
	$val = json_str_iconv($_POST['val']);

	if (cat_update($id, array('measure_unit' => $val))) {
		clear_cache_files();
		make_json_result($val);
	}
	else {
		make_json_error($db->error());
	}
}

if ($_REQUEST['act'] == 'edit_grade') {
	check_authz_json('cat_manage');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	if (10 < $val || $val < 0) {
		make_json_error($_LANG['grade_error']);
	}

	if (cat_update($id, array('grade' => $val))) {
		clear_cache_files();
		make_json_result($val);
	}
	else {
		make_json_error($db->error());
	}
}

if ($_REQUEST['act'] == 'edit_commission_rate') {
	check_authz_json('cat_manage');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	if (100 < $val || $val < 0) {
		make_json_error('佣金比率为0-100以内，请重新设置');
	}

	if (cat_update($id, array('commission_rate' => $val))) {
		clear_cache_files();
		make_json_result($val);
	}
	else {
		make_json_error($db->error());
	}
}

if ($_REQUEST['act'] == 'toggle_show_in_nav') {
	check_authz_json('cat_manage');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);

	if (cat_update($id, array('show_in_nav' => $val)) != false) {
		if ($val == 1) {
			$vieworder = $db->getOne('SELECT max(vieworder) FROM ' . $ecs->table('nav') . ' WHERE type = \'middle\'');
			$vieworder += 2;
			$catname = $db->getOne('SELECT cat_name FROM ' . $ecs->table('category') . (' WHERE cat_id = \'' . $id . '\''));
			$_CFG['rewrite'] = 0;
			$uri = build_uri('category', array('cid' => $id), $catname);
			$nid = $db->getOne('SELECT id FROM ' . $ecs->table('nav') . ' WHERE ctype = \'c\' AND cid = \'' . $id . '\' AND type = \'middle\'');

			if (empty($nid)) {
				$sql = 'INSERT INTO ' . $ecs->table('nav') . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type) VALUES(\'' . $catname . ('\', \'c\', \'' . $id . '\',\'1\',\'' . $vieworder . '\',\'0\', \'') . $uri . '\',\'middle\')';
			}
			else {
				$sql = 'UPDATE ' . $ecs->table('nav') . ' SET ifshow = 1 WHERE ctype = \'c\' AND cid = \'' . $id . '\' AND type = \'middle\'';
			}

			$db->query($sql);
		}
		else {
			$db->query('UPDATE ' . $ecs->table('nav') . 'SET ifshow = 0 WHERE ctype = \'c\' AND cid = \'' . $id . '\' AND type = \'middle\'');
		}

		clear_cache_files();
		make_json_result($val);
	}
	else {
		make_json_error($db->error());
	}
}

if ($_REQUEST['act'] == 'toggle_is_show') {
	check_authz_json('cat_manage');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	$children = get_children($id);
	$sql = 'UPDATE' . $ecs->table('goods') . ' AS g SET g.is_show = \'' . $val . '\' WHERE ' . $children;
	$db->query($sql);

	if (cat_update($id, array('is_show' => $val)) != false) {
		clear_cache_files();
		make_json_result($val);
	}
	else {
		make_json_error($db->error());
	}
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('cat_drop');
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => 0, 'massege' => '', 'level' => '');
	$result['level'] = $_REQUEST['level'];
	$cat_id = intval($_GET['cat_id']);
	$result['cat_id'] = $cat_id;
	$cat_name = $db->getOne('SELECT cat_name FROM ' . $ecs->table('category') . (' WHERE cat_id=\'' . $cat_id . '\''));
	$cat_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('category') . (' WHERE parent_id=\'' . $cat_id . '\''));
	$goods_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('goods') . (' WHERE cat_id=\'' . $cat_id . '\''));
	if ($cat_count == 0 && $goods_count == 0) {
		$sql = 'DELETE FROM ' . $ecs->table('category') . (' WHERE cat_id = \'' . $cat_id . '\'');

		if ($db->query($sql)) {
			$db->query('DELETE FROM ' . $ecs->table('nav') . 'WHERE ctype = \'c\' AND cid = \'' . $cat_id . '\' AND type = \'middle\'');
			clear_cache_files();
			admin_log($cat_name, 'remove', 'category');
			$cache['category']['type'] = 'add_edit';
			$cache['category']['is_show'] = 1;
			$cache['category']['cache_path'] = 'data/sc_file/category/';
			get_admin_seller_static_cache($cache);
		}
		else {
			$result['error'] = 1;
			$result['message'] = 'delete failed!';
		}

		$sql = 'delete from ' . $ecs->table('merchants_documenttitle') . (' where cat_id = \'' . $cat_id . '\'');
		$db->query($sql);
	}
	else {
		$result['error'] = 2;
		$result['massege'] = $cat_name . ' ' . $_LANG['cat_isleaf'];
	}

	exit($json->encode($result));
}

if ($_REQUEST['act'] == 'title_remove') {
	check_authz_json('cat_manage');
	$dt_id = intval($_GET['dt_id']);
	$cat_id = intval($_GET['cat_id']);
	$sql = 'delete from ' . $ecs->table('merchants_documenttitle') . (' where dt_id = \'' . $dt_id . '\'');
	$db->query($sql);
	$url = 'category.php?act=titleFileView&cat_id=' . $cat_id;
	ecs_header('Location: ' . $url . "\n");
	exit();
}

?>
