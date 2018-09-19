<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function cat_update($cat_id, $args)
{
	if (empty($args) || empty($cat_id)) {
		return false;
	}

	return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_category'), $args, 'update', 'cat_id=\'' . $cat_id . '\'');
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
$exc = new exchange($ecs->table('merchants_category'), $db, 'cat_id', 'cat_name');

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$adminru = get_admin_ru_id();
$smarty->assign('ru_id', $adminru['ru_id']);
$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => '03_store_category_list'));

if ($_REQUEST['act'] == 'list') {
	$_REQUEST['parent_id'] = !isset($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
	if (isset($_REQUEST['back_level']) && 0 < $_REQUEST['back_level']) {
		$_REQUEST['level'] = intval($_REQUEST['back_level']) - 1;
		$_REQUEST['parent_id'] = $db->getOne('SELECT parent_id FROM ' . $ecs->table('merchants_category') . ' WHERE cat_id = \'' . $_REQUEST['parent_id'] . '\'', true);
	}
	else {
		$_REQUEST['level'] = isset($_REQUEST['level']) ? $_REQUEST['level'] + 1 : 0;
	}

	$smarty->assign('level', $_REQUEST['level']);
	$smarty->assign('parent_id', $_REQUEST['parent_id']);

	if (0 < $_REQUEST['parent_id']) {
		$cat_info = get_cat_info($_REQUEST['parent_id'], array('user_id'), 'merchants_category');
		$user_id = $cat_info['user_id'];
	}
	else {
		$user_id = $adminru['ru_id'];
	}

	$cat_list = get_category_store_list($user_id);
	$smarty->assign('ur_here', $_LANG['03_store_category_list']);
	$smarty->assign('full_page', 1);
	$smarty->assign('cat_info', $cat_list['cate']);
	$smarty->assign('filter', $cat_list['filter']);
	$smarty->assign('record_count', $cat_list['record_count']);
	$smarty->assign('page_count', $cat_list['page_count']);
	$cat_level = array('一', '二', '三', '四', '五', '六', '七', '八', '九', '十');
	$smarty->assign('cat_level', $cat_level[$_REQUEST['level']]);
	assign_query_info();
	$smarty->display('category_store_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$cat_list = get_category_store_list();
	$smarty->assign('cat_info', $cat_list['cate']);
	$smarty->assign('filter', $cat_list['filter']);
	$smarty->assign('record_count', $cat_list['record_count']);
	$smarty->assign('page_count', $cat_list['page_count']);
	$smarty->assign('level', $cat_list['filter']['level']);
	$smarty->assign('parent_id', $cat_list['filter']['parent_id']);
	$cat_level = array('一', '二', '三', '四', '五', '六', '气', '八', '九', '十');
	$smarty->assign('cat_level', $cat_level[$cat_list['filter']['level']]);
	make_json_result($smarty->fetch('category_store_list.dwt'), '', array('filter' => $cat_list['filter'], 'page_count' => $cat_list['page_count']));
}

if ($_REQUEST['act'] == 'edit') {
	admin_priv('cat_manage');
	$cat_id = !isset($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
	$cat_info = get_seller_cat_info($cat_id);
	$attr_list = get_attr_list();
	$filter_attr_list = array();
	$smarty->assign('parent_id', $cat_info['parent_id']);
	$smarty->assign('parent_category', get_seller_every_category($cat_info['parent_id']));
	set_seller_default_filter(0, $cat_info['parent_id'], $cat_info['user_id']);
	$type_level = get_type_cat_arr(0, 0, 0, $cat_info['user_id']);
	$smarty->assign('type_level', $type_level);
	$smarty->assign('user_id', $cat_info['user_id']);

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

	if ($cat_info['parent_id'] == 0) {
		$cat_name_arr = explode('、', $cat_info['cat_name']);
		$smarty->assign('cat_name_arr', $cat_name_arr);
	}

	$smarty->assign('attr_list', $attr_list);
	$smarty->assign('attr_cat_id', $attr_cat_id);
	$smarty->assign('ur_here', $_LANG['category_edit']);
	$smarty->assign('action_link', array('text' => $_LANG['03_category_list'], 'href' => 'category_store.php?act=list'));
	$res = $db->getAll('SELECT recommend_type FROM ' . $ecs->table('cat_recommend') . ' WHERE cat_id=' . $cat_id);

	if (!empty($res)) {
		$cat_recommend = array();

		foreach ($res as $data) {
			$cat_recommend[$data['recommend_type']] = 1;
		}

		$smarty->assign('cat_recommend', $cat_recommend);
	}

	$smarty->assign('cat_info', $cat_info);
	$smarty->assign('form_act', 'update');
	$smarty->assign('goods_type_list', goods_type_list(0));
	assign_query_info();
	$smarty->display('category_store_info.dwt');
}
else if ($_REQUEST['act'] == 'category_separate') {
	admin_priv('brand_manage');
	$smarty->assign('ur_here', $_LANG['category_separate']);
	$cat_list = get_seller_category();
	$smarty->assign('record_count', count($cat_list));
	$smarty->assign('page', 1);
	write_static_cache('seller_cat_list', array(), '/data/sc_file/');
	assign_query_info();
	$smarty->display('category_separate.dwt');
}
else if ($_REQUEST['act'] == 'category_separate_initial') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$page_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 1;
	$cat_list = get_seller_category();

	if ($cat_list) {
		$cat_list = get_array_sort($cat_list, 'level');
	}

	$cat_list = $ecs->page_array($page_size, $page, $cat_list);
	$result['list'] = $cat_list['list'][0];

	if ($result['list']) {
		if ($result['list']['level'] == 0) {
			$parent_id = 0;
		}
		else {
			$parent_id = $result['list']['parent_id'];
		}

		$other = array('cat_name' => $result['list']['cat_name'], 'parent_id' => $parent_id, 'keywords' => $result['list']['keywords'], 'cat_desc' => $result['list']['cat_desc'], 'sort_order' => $result['list']['sort_order'], 'measure_unit' => $result['list']['measure_unit'], 'show_in_nav' => $result['list']['show_in_nav'], 'style' => $result['list']['style'], 'grade' => $result['list']['grade'], 'filter_attr' => $result['list']['filter_attr'], 'is_top_style' => $result['list']['is_top_style'], 'top_style_tpl' => $result['list']['top_style_tpl'], 'cat_icon' => $result['list']['cat_icon'], 'is_top_show' => $result['list']['is_top_show'], 'category_links' => $result['list']['category_links'], 'category_topic' => $result['list']['category_topic'], 'pinyin_keyword' => $result['list']['pinyin_keyword'], 'cat_alias_name' => $result['list']['cat_alias_name']);
		$db->autoExecute($ecs->table('merchants_category'), $other, 'UPDATE', 'cat_id = \'' . $result['list']['cat_id'] . '\'');

		if ($result['list']['cat_id']) {
			$new_arr = read_static_cache('seller_cat_list', '/data/sc_file/');

			if ($new_arr === false) {
				$new_arr = array($result['list']['cat_id']);
			}
			else {
				array_unshift($new_arr, $result['list']['cat_id']);
			}

			write_static_cache('seller_cat_list', $new_arr, '/data/sc_file/');
		}
	}

	$result['page'] = $cat_list['filter']['page'] + 1;
	$result['page_size'] = $cat_list['filter']['page_size'];
	$result['record_count'] = $cat_list['filter']['record_count'];
	$result['page_count'] = $cat_list['filter']['page_count'];
	$result['is_stop'] = 1;

	if ($cat_list['filter']['page_count'] < $page) {
		$result['is_stop'] = 0;
		$sql = 'UPDATE ' . $ecs->table('shop_config') . ' SET value = 1 WHERE code = \'cat_belongs\'';
		$db->query($sql);
		$cat = read_static_cache('seller_cat_list', '/data/sc_file/');

		if ($cat !== false) {
			if ($cat) {
				$cat = implode(',', $cat);
				$sql = 'UPDATE ' . $ecs->table('goods') . ' SET user_cat = cat_id' . (' WHERE cat_id IN(' . $cat . ')');
				$db->query($sql);
				$sql = 'DELETE FROM ' . $ecs->table('category') . (' WHERE cat_id IN(' . $cat . ')');
				$db->query($sql);
			}
		}

		clear_all_files();
		load_config();
	}
	else {
		$result['filter_page'] = $cat_list['filter']['page'];
	}

	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'add_category') {
	$parent_id = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
	$category = empty($_REQUEST['cat']) ? '' : json_str_iconv(trim($_REQUEST['cat']));

	if (cat_exists($category, $parent_id)) {
		make_json_error($_LANG['catname_exist']);
	}
	else {
		$sql = 'INSERT INTO ' . $ecs->table('merchants_category') . '(cat_name, parent_id, is_show)' . ('VALUES ( \'' . $category . '\', \'' . $parent_id . '\', 1)');
		$db->query($sql);
		$category_id = $db->insert_id();
		$arr = array('parent_id' => $parent_id, 'id' => $category_id, 'cat' => $category);
		clear_cache_files();
		make_json_result($arr);
	}
}

if ($_REQUEST['act'] == 'update') {
	admin_priv('cat_manage');
	$cat_id = !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
	$user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : 0;
	$cat['category_links'] = !empty($_POST['category_links']) ? $_POST['category_links'] : '';
	$old_cat_name = $_POST['old_cat_name'];
	$cat['parent_id'] = isset($_POST['parent_id']) ? trim($_POST['parent_id']) : '0_-1';
	$parent_id = explode('_', $cat['parent_id']);
	$cat['parent_id'] = intval($parent_id[0]);
	$cat['level'] = intval($parent_id[1]);
	$link[0]['text'] = $_LANG['go_back'];

	if (0 < $cat_id) {
		$link[0]['href'] = 'category_store.php?act=edit&cat_id=' . $cat_id;
	}
	else {
		$link[0]['href'] = 'category_store.php?act=add';
	}

	$reject_cat = arr_foreach(cat_list($cat_id, 1, 1, 'merchants_category'));
	if ($cat['parent_id'] == $cat_id || in_array($cat['parent_id'], $reject_cat)) {
		sys_msg('分类本身或自身下级不能作为父级成员！', 1, $link);
		exit();
	}

	if ($cat['level'] < 2 && 0 < $adminru['ru_id']) {
		sys_msg('您目前的权限只能添加四级分类', 1, $link);
		exit();
	}

	if (!empty($_FILES['touch_icon']['name'])) {
		if (200000 < $_FILES['touch_icon']['size']) {
			sys_msg('上传图片不得大于200kb', 1, $link);
		}

		$type = end(explode('.', $_FILES['touch_icon']['name']));
		if ($type != 'jpg' && $type != 'png' && $type != 'gif') {
			sys_msg('请上传jpg,gif,png格式图片', 1, $link);
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

		if (!empty($cat_id)) {
			$cat_info = get_cat_info($cat_id);
			@unlink(ROOT_PATH . $cat_info['touch_icon']);
		}
	}

	$cat['sort_order'] = !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
	$cat['keywords'] = !empty($_POST['keywords']) ? trim($_POST['keywords']) : '';
	$cat['cat_desc'] = !empty($_POST['cat_desc']) ? $_POST['cat_desc'] : '';
	$cat['measure_unit'] = !empty($_POST['measure_unit']) ? trim($_POST['measure_unit']) : '';
	$cat['cat_name'] = !empty($_POST['cat_name']) ? trim($_POST['cat_name']) : '';
	$cat['is_show'] = !empty($_POST['is_show']) ? intval($_POST['is_show']) : 0;
	$cat['is_top_show'] = !empty($_POST['is_top_show']) ? intval($_POST['is_top_show']) : 0;
	$cat['is_top_style'] = !empty($_POST['is_top_style']) ? intval($_POST['is_top_style']) : 0;
	$cat['show_in_nav'] = !empty($_POST['show_in_nav']) ? intval($_POST['show_in_nav']) : 0;
	$cat['style'] = !empty($_POST['style']) ? trim($_POST['style']) : '';
	$cat['grade'] = !empty($_POST['grade']) ? intval($_POST['grade']) : 0;
	$cat['filter_attr'] = !empty($_POST['filter_attr']) ? implode(',', array_unique(array_diff($_POST['filter_attr'], array(0)))) : 0;
	$cat['cat_recommend'] = !empty($_POST['cat_recommend']) ? $_POST['cat_recommend'] : array();

	if ($cat['cat_name'] != $old_cat_name) {
		if (cat_exists($cat['cat_name'], $cat['parent_id'], $cat_id, $user_id)) {
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

	$dat = $db->getRow('SELECT cat_name, show_in_nav FROM ' . $ecs->table('merchants_category') . (' WHERE cat_id = \'' . $cat_id . '\' LIMIT 1'));

	if ($db->autoExecute($ecs->table('merchants_category'), $cat, 'UPDATE', 'cat_id=\'' . $cat_id . '\'')) {
		if ($cat['cat_name'] != $dat['cat_name']) {
			$sql = 'UPDATE ' . $ecs->table('merchants_nav') . ' SET name = \'' . $cat['cat_name'] . '\' WHERE ctype = \'c\' AND cid = \'' . $cat_id . '\' AND type = \'middle\'';
			$db->query($sql);
		}

		if ($cat['show_in_nav'] != $dat['show_in_nav']) {
			if ($cat['show_in_nav'] == 1) {
				$nid = $db->getOne('SELECT id FROM ' . $ecs->table('merchants_nav') . ' WHERE ctype = \'c\' AND cid = \'' . $cat_id . '\' AND type = \'middle\'');

				if (empty($nid)) {
					$vieworder = $db->getOne('SELECT max(vieworder) FROM ' . $ecs->table('merchants_nav') . ' WHERE type = \'middle\'');
					$vieworder += 2;
					$uri = build_uri('merchants_store', array('urid' => $user_id, 'cid' => $cat_id), $cat['cat_name']);
					$sql = 'INSERT INTO ' . $ecs->table('merchants_nav') . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type) VALUES(\'' . $cat['cat_name'] . ('\', \'c\', \'' . $cat_id . '\',\'1\',\'' . $vieworder . '\',\'0\', \'') . $uri . '\',\'middle\')';
				}
				else {
					$sql = 'UPDATE ' . $ecs->table('merchants_nav') . ' SET ifshow = 1 WHERE ctype = \'c\' AND cid = \'' . $cat_id . '\' AND type = \'middle\'';
				}

				$db->query($sql);
			}
			else {
				$db->query('UPDATE ' . $ecs->table('merchants_nav') . ' SET ifshow = 0 WHERE ctype = \'c\' AND cid = \'' . $cat_id . '\' AND type = \'middle\'');
			}
		}

		clear_cache_files();
		admin_log($_POST['cat_name'], 'edit', 'merchants_category');
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'category_store.php?act=list');
		sys_msg($_LANG['catedit_succed'], 0, $link);
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

if ($_REQUEST['act'] == 'toggle_show_in_nav') {
	check_authz_json('cat_manage');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);

	if (cat_update($id, array('show_in_nav' => $val)) != false) {
		if ($val == 1) {
			$vieworder = $db->getOne('SELECT max(vieworder) FROM ' . $ecs->table('merchants_nav') . ' WHERE type = \'middle\'');
			$vieworder += 2;
			$catname = $db->getOne('SELECT cat_name FROM ' . $ecs->table('merchants_category') . (' WHERE cat_id = \'' . $id . '\''));
			$_CFG['rewrite'] = 0;
			$uri = build_uri('category', array('cid' => $id), $catname);
			$nid = $db->getOne('SELECT id FROM ' . $ecs->table('merchants_nav') . ' WHERE ctype = \'c\' AND cid = \'' . $id . '\' AND type = \'middle\'');

			if (empty($nid)) {
				$sql = 'INSERT INTO ' . $ecs->table('merchants_nav') . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type) VALUES(\'' . $catname . ('\', \'c\', \'' . $id . '\',\'1\',\'' . $vieworder . '\',\'0\', \'') . $uri . '\',\'middle\')';
			}
			else {
				$sql = 'UPDATE ' . $ecs->table('merchants_nav') . ' SET ifshow = 1 WHERE ctype = \'c\' AND cid = \'' . $id . '\' AND type = \'middle\'';
			}

			$db->query($sql);
		}
		else {
			$db->query('UPDATE ' . $ecs->table('merchants_nav') . 'SET ifshow = 0 WHERE ctype = \'c\' AND cid = \'' . $id . '\' AND type = \'middle\'');
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
	$children = get_children($id, 0, 0, 'merchants_category', 'g.user_cat');
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
	check_authz_json('cat_manage');
	require ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => 0, 'massege' => '', 'level' => '');
	$result['level'] = $_REQUEST['level'];
	$cat_id = intval($_GET['cat_id']);
	$result['cat_id'] = $cat_id;
	$cat_name = $db->getOne('SELECT cat_name FROM ' . $ecs->table('merchants_category') . (' WHERE cat_id=\'' . $cat_id . '\''));
	$cat_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('merchants_category') . (' WHERE parent_id=\'' . $cat_id . '\''));
	$goods_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('goods') . (' WHERE user_cat = \'' . $cat_id . '\''));
	if ($cat_count == 0 && $goods_count == 0) {
		$sql = 'DELETE FROM ' . $ecs->table('merchants_category') . (' WHERE cat_id = \'' . $cat_id . '\'');

		if ($db->query($sql)) {
			$db->query('DELETE FROM ' . $ecs->table('merchants_nav') . 'WHERE ctype = \'c\' AND cid = \'' . $cat_id . '\' AND type = \'middle\'');
			clear_cache_files();
			admin_log($cat_name, 'remove', 'category');
			$result['error'] = 1;
		}
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
	$url = 'category_store.php?act=titleFileView&cat_id=' . $cat_id;
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'remove_cat') {
	check_authz_json('cat_manage');
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
			$result['error'] = 1;
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

?>
