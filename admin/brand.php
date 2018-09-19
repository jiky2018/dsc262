<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_brandlist()
{
	$result = get_filter();

	if ($result === false) {
		$filter = array();
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'brand_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$keyword = '';

		if (isset($_POST['brand_name'])) {
			if (strtoupper(EC_CHARSET) == 'GBK') {
				$keyword = iconv('UTF-8', 'gb2312', $_POST['brand_name']);
			}
			else {
				$keyword = $_POST['brand_name'];
			}
		}

		if (isset($_POST['brand_name'])) {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('brand') . (' WHERE brand_name like \'%' . $keyword . '%\'');
		}
		else {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('brand');
		}

		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$leftjoin = ' left join ' . $GLOBALS['ecs']->table('brand_extend') . ' as be on b.brand_id=be.brand_id ';

		if (isset($_POST['brand_name'])) {
			$sql = 'SELECT b.*,be.is_recommend FROM ' . $GLOBALS['ecs']->table('brand') . ' as b ' . $leftjoin . (' WHERE brand_name like \'%' . $keyword . '%\' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order']);
		}
		else {
			$sql = 'SELECT b.*,be.is_recommend FROM ' . $GLOBALS['ecs']->table('brand') . ' as b ' . $leftjoin . (' where 1 ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order']);
		}

		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$arr = array();

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$brand_logo = empty($rows['brand_logo']) ? '' : '../' . DATA_DIR . '/brandlogo/' . $rows['brand_logo'];
		$site_url = empty($rows['site_url']) ? 'N/A' : '<a href="' . $rows['site_url'] . '" target="_brank">' . $rows['site_url'] . '</a>';
		$rows['brand_logo'] = $brand_logo;
		$rows['site_url'] = $site_url;
		$arr[] = $rows;
	}

	return array('brand' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
include_once ROOT_PATH . 'includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table('brand'), $db, 'brand_id', 'brand_name');
$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => '06_goods_brand_list'));

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('ur_here', $_LANG['06_goods_brand_list']);
	$smarty->assign('action_link', array('text' => $_LANG['07_brand_add'], 'href' => 'brand.php?act=add'));
	$smarty->assign('full_page', 1);
	$brand_list = get_brandlist();
	$smarty->assign('brand_list', $brand_list['brand']);
	$smarty->assign('filter', $brand_list['filter']);
	$smarty->assign('record_count', $brand_list['record_count']);
	$smarty->assign('page_count', $brand_list['page_count']);
	assign_query_info();
	$smarty->display('brand_list.dwt');
}
else if ($_REQUEST['act'] == 'add') {
	admin_priv('brand_manage');
	$smarty->assign('ur_here', $_LANG['07_brand_add']);
	$smarty->assign('action_link', array('text' => $_LANG['06_goods_brand_list'], 'href' => 'brand.php?act=list'));
	$smarty->assign('form_action', 'insert');
	$smarty->assign('is_need', $_CFG['template'] == 'ecmoban_dsc2017' ? 1 : 0);
	assign_query_info();
	$smarty->assign('brand', array('sort_order' => 50, 'is_show' => 1));
	$smarty->display('brand_info.dwt');
}
else if ($_REQUEST['act'] == 'insert') {
	admin_priv('brand_manage');
	$is_show = isset($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0;
	$_POST['brand_name'] = isset($_POST['brand_name']) && !empty($_POST['brand_name']) ? dsc_addslashes($_POST['brand_name']) : '';
	$is_only = $exc->is_only('brand_name', $_POST['brand_name']);

	if (!$is_only) {
		sys_msg(sprintf($_LANG['brandname_exist'], stripslashes($_POST['brand_name'])), 1);
	}

	if (!empty($_POST['brand_desc'])) {
		$_POST['brand_desc'] = $_POST['brand_desc'];
	}

	$img_name = basename($image->upload_image($_FILES['brand_logo'], 'brandlogo'));
	get_oss_add_file(array(DATA_DIR . '/brandlogo/' . $img_name));
	$index_img = basename($image->upload_image($_FILES['index_img'], 'indeximg'));
	get_oss_add_file(array(DATA_DIR . '/indeximg/' . $index_img));
	$brand_bg = basename($image->upload_image($_FILES['brand_bg'], 'brandbg'));
	get_oss_add_file(array(DATA_DIR . '/brandbg/' . $brand_bg));
	$site_url = sanitize_url($_POST['site_url']);
	$sql = 'INSERT INTO ' . $ecs->table('brand') . '(brand_name, brand_letter, brand_first_char, site_url, brand_desc, brand_logo, index_img, brand_bg, is_show, sort_order) ' . ('VALUES (\'' . $_POST['brand_name'] . '\', \'' . $_POST['brand_letter'] . '\', \'') . strtoupper($_POST['brand_first_char']) . ('\', \'' . $site_url . '\', \'' . $_POST['brand_desc'] . '\', \'' . $img_name . '\', \'' . $index_img . '\', \'' . $brand_bg . '\', \'' . $is_show . '\', \'' . $_POST['sort_order'] . '\')');
	$db->query($sql);

	if ($brand_id = $db->insert_id()) {
		$is_recommend = !empty($_POST['is_recommend']) ? intval($_POST['is_recommend']) : 0;
		$extend_sql = 'INSERT INTO ' . $ecs->table('brand_extend') . (' (brand_id,is_recommend) values (\'' . $brand_id . '\',\'' . $is_recommend . '\')');
		$db->query($extend_sql);
	}

	admin_log($_POST['brand_name'], 'add', 'brand');
	clear_cache_files();
	$link[0]['text'] = $_LANG['continue_add'];
	$link[0]['href'] = 'brand.php?act=add';
	$link[1]['text'] = $_LANG['back_list'];
	$link[1]['href'] = 'brand.php?act=list';
	sys_msg($_LANG['brandadd_succed'], 0, $link);
}
else if ($_REQUEST['act'] == 'edit') {
	admin_priv('brand_manage');
	$sql = 'SELECT b.brand_id, b.brand_name, b.brand_letter, b.brand_first_char, b.site_url, b.brand_logo, b.index_img, b.brand_bg, b.brand_desc, b.brand_logo, b.is_show, b.sort_order,be.is_recommend ' . 'FROM ' . $ecs->table('brand') . ' as b left join ' . $ecs->table('brand_extend') . (' as be on b.brand_id=be.brand_id WHERE b.brand_id=\'' . $_REQUEST['id'] . '\'');
	$brand = $db->GetRow($sql);
	$brand['brand_logo'] = !empty($brand['brand_logo']) ? get_image_path($brand['brand_id'], DATA_DIR . '/brandlogo/' . $brand['brand_logo']) : '';
	$brand['index_img'] = !empty($brand['index_img']) ? get_image_path($brand['brand_id'], DATA_DIR . '/indeximg/' . $brand['index_img']) : '';
	$brand['brand_bg'] = !empty($brand['brand_bg']) ? get_image_path($brand['brand_id'], DATA_DIR . '/brandbg/' . $brand['brand_bg']) : '';
	$smarty->assign('ur_here', $_LANG['brand_edit']);
	$smarty->assign('action_link', array('text' => $_LANG['06_goods_brand_list'], 'href' => 'brand.php?act=list&' . list_link_postfix()));
	$smarty->assign('brand', $brand);
	$smarty->assign('form_action', 'updata');
	$smarty->assign('is_need', $_CFG['template'] == 'ecmoban_dsc2017' ? 1 : 0);
	assign_query_info();
	$smarty->display('brand_info.dwt');
}
else if ($_REQUEST['act'] == 'updata') {
	admin_priv('brand_manage');

	if ($_POST['brand_name'] != $_POST['old_brandname']) {
		$is_only = $exc->is_only('brand_name', $_POST['brand_name'], $_POST['id']);

		if (!$is_only) {
			sys_msg(sprintf($_LANG['brandname_exist'], stripslashes($_POST['brand_name'])), 1);
		}
	}

	if (!empty($_POST['brand_desc'])) {
		$_POST['brand_desc'] = $_POST['brand_desc'];
	}

	$is_show = isset($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0;
	$site_url = sanitize_url($_POST['site_url']);
	$add_oss_file = array();
	$img_name = basename($image->upload_image($_FILES['brand_logo'], 'brandlogo'));
	$param = 'brand_name = \'' . $_POST['brand_name'] . '\', brand_letter = \'' . $_POST['brand_letter'] . '\', brand_first_char = \'' . strtoupper($_POST['brand_first_char']) . ('\', site_url=\'' . $site_url . '\', brand_desc=\'' . $_POST['brand_desc'] . '\', is_show=\'' . $is_show . '\', sort_order=\'' . $_POST['sort_order'] . '\' ');

	if (!empty($img_name)) {
		$param .= ' ,brand_logo = \'' . $img_name . '\' ';
		$add_oss_file[] = DATA_DIR . '/brandlogo/' . $img_name;
	}

	$index_img = basename($image->upload_image($_FILES['index_img'], 'indeximg'));

	if (!empty($index_img)) {
		$param .= ' ,index_img = \'' . $index_img . '\' ';
		$add_oss_file[] = DATA_DIR . '/indeximg/' . $index_img;
	}

	$brand_bg = basename($image->upload_image($_FILES['brand_bg'], 'brandbg'));

	if (!empty($brand_bg)) {
		$param .= ' ,brand_bg = \'' . $brand_bg . '\' ';
		$add_oss_file[] = DATA_DIR . '/brandbg/' . $brand_bg;
	}

	get_oss_add_file($add_oss_file);

	if ($exc->edit($param, $_POST['id'])) {
		$brand_id = !empty($_POST['id']) ? intval($_POST['id']) : 0;

		if (0 < $brand_id) {
			$is_recommend = !empty($_POST['is_recommend']) ? intval($_POST['is_recommend']) : 0;

			if ($db->query('select count(id) from ' . $ecs->table('brand_extend') . (' where brand_id=\'' . $brand_id . '\''))) {
				$extend_sql = 'update ' . $ecs->table('brand_extend') . (' set is_recommend=\'' . $is_recommend . '\' where brand_id=\'' . $brand_id . '\'');
			}
			else {
				$extend_sql = 'INSERT INTO ' . $ecs->table('brand_extend') . (' (brand_id,is_recommend) values (\'' . $brand_id . '\',\'' . $is_recommend . '\')');
			}

			$db->query($extend_sql);
		}

		clear_cache_files();
		admin_log($_POST['brand_name'], 'edit', 'brand');
		$link[0]['text'] = $_LANG['back_list'];
		$link[0]['href'] = 'brand.php?act=list&' . list_link_postfix();
		$note = vsprintf($_LANG['brandedit_succed'], $_POST['brand_name']);
		sys_msg($note, 0, $link);
	}
	else {
		exit($db->error());
	}
}
else if ($_REQUEST['act'] == 'brand_separate') {
	admin_priv('brand_manage');
	$smarty->assign('ur_here', $_LANG['brand_separate']);
	$smarty->assign('page', 1);
	assign_query_info();
	$smarty->display('brand_separate.dwt');
}
else if ($_REQUEST['act'] == 'brand_separate_initial') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$page_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 1;
	$brand_list = get_seller_brand();
	$brand_list = $ecs->page_array($page_size, $page, $brand_list);
	$result['list'] = $brand_list['list'][0];

	if ($result['list']) {
		$other = array('brand_id' => $result['list']['brand_id'], 'user_brand' => $result['list']['bid']);

		if ($result['list']['user_id']) {
			$db->autoExecute($ecs->table('goods'), $other, 'UPDATE', 'user_id = \'' . $result['list']['user_id'] . '\' AND brand_id = \'' . $result['list']['bid'] . '\' AND user_brand = 0');
			$db->autoExecute($ecs->table('collect_brand'), $other, 'UPDATE', 'ru_id = \'' . $result['list']['user_id'] . '\' AND brand_id = \'' . $result['list']['bid'] . '\' AND user_brand = 0');
			$sql = 'SELECT GROUP_CONCAT(act_id) AS act_id FROM ' . $GLOBALS['ecs']->table('favourable_activity') . ' WHERE act_range = 2 AND user_id = \'' . $result['list']['user_id'] . '\' ' . 'AND FIND_IN_SET(\'' . $result['list']['bid'] . '\', act_range_ext) AND ' . db_create_in($result['list']['bid'], 'user_range_ext', 'NOT') . ' LIMIT 1';
			$favourable = $GLOBALS['db']->getRow($sql);
			if ($favourable && $favourable['act_id']) {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('favourable_activity') . ' SET user_range_ext = act_range_ext ' . ' WHERE act_id ' . db_create_in($favourable['act_id']) . ' AND is_user_brand = 0';
				$GLOBALS['db']->query($sql);
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('favourable_activity') . ' SET act_range_ext =  REPLACE ( act_range_ext, \'' . $result['list']['bid'] . '\', \'' . $result['list']['brand_id'] . '\' ), is_user_brand = 1 ' . ' WHERE act_id ' . db_create_in($favourable['act_id']);
				$GLOBALS['db']->query($sql);
			}

			$result['status_lang'] = '<span style="color: red;">已更新数据成功</span>';
		}
		else {
			$result['status_lang'] = '<span style="color: red;">已更新数据失败</span>';
		}
	}

	$result['page'] = $brand_list['filter']['page'] + 1;
	$result['page_size'] = $brand_list['filter']['page_size'];
	$result['record_count'] = $brand_list['filter']['record_count'];
	$result['page_count'] = $brand_list['filter']['page_count'];
	$result['is_stop'] = 1;

	if ($brand_list['filter']['page_count'] < $page) {
		$result['is_stop'] = 0;
		$sql = 'UPDATE ' . $ecs->table('shop_config') . ' SET value = 1 WHERE code = \'brand_belongs\'';
		$db->query($sql);
		clear_all_files();
		load_config();
		$result['status_lang'] = '<span style="color: red;">已更新数据成功</span>';
	}
	else {
		$result['filter_page'] = $brand_list['filter']['page'];
	}

	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'create_brand_letter') {
	admin_priv('brand_manage');
	$smarty->assign('ur_here', $_LANG['06_goods_brand_list']);
	$record_count = get_brand_list(0, 2);
	$smarty->assign('record_count', $record_count);
	$smarty->assign('page', 1);
	assign_query_info();
	$smarty->display('brand_first_letter.dwt');
}
else if ($_REQUEST['act'] == 'create_brand_initial') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$page_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 1;

	if ($page == 1) {
		@unlink(ROOT_PATH . DATA_DIR . '/sc_file/pin_brands.php');
	}

	$brand_list = get_brand_list(0, 1);
	$brand_list = $ecs->page_array($page_size, $page, $brand_list);
	$result['list'] = $brand_list['list'][0];

	if ($result['list']) {
		$arr = array();
		if (!empty($result['list']['brand_first_char']) && in_array(strtoupper($result['list']['brand_first_char']), range('A', 'Z'))) {
			$arr['brand_id'] = $result['list']['brand_id'];
			$arr['brand_name'] = $result['list']['brand_name'];
			$arr['letter'] = strtoupper($result['list']['brand_first_char']);
		}
		else {
			$str_first = strtolower(substr($result['list']['brand_name'], 0, 1));

			if ($ecs->preg_is_letter($str_first)) {
				$arr['brand_id'] = $result['list']['brand_id'];
				$arr['brand_name'] = $result['list']['brand_name'];
				$arr['letter'] = strtoupper($str_first);
			}
			else {
				$pin = new pin();
				$letters = range('A', 'Z');

				foreach ($letters as $key => $val) {
					$str = strtoupper($result['list']['brand_name']);
					$str = substr($str, 0, 1);

					if (in_array($str, range('A', 'Z'))) {
						$arr['brand_id'] = $result['list']['brand_id'];
						$arr['brand_name'] = $result['list']['brand_name'];
						$arr['letter'] = $str;
					}
					else if (strtolower($val) == substr($pin->Pinyin($result['list']['brand_name'], EC_CHARSET), 0, 1)) {
						$arr['brand_id'] = $result['list']['brand_id'];
						$arr['brand_name'] = $result['list']['brand_name'];
						$arr['letter'] = $val;
					}
				}
			}

			$sql = 'UPDATE ' . $ecs->table('brand') . ' SET brand_first_char = \'' . $arr['letter'] . '\' WHERE brand_id = \'' . $result['list']['brand_id'] . '\'';
			$db->query($sql);
		}

		$result['list'] = $arr;
		$pin_brands = read_static_cache('pin_brands', '/data/sc_file/');

		if ($pin_brands === false) {
			write_static_cache('pin_brands', array($result['list']), '/data/sc_file/');
		}
		else {
			array_push($pin_brands, $result['list']);
			write_static_cache('pin_brands', $pin_brands, '/data/sc_file/');
		}
	}

	$result['page'] = $brand_list['filter']['page'] + 1;
	$result['page_size'] = $brand_list['filter']['page_size'];
	$result['record_count'] = $brand_list['filter']['record_count'];
	$result['page_count'] = $brand_list['filter']['page_count'];
	$result['is_stop'] = 1;

	if ($brand_list['filter']['page_count'] < $page) {
		$result['is_stop'] = 0;
	}
	else {
		$result['filter_page'] = $brand_list['filter']['page'];
	}

	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'edit_brand_name') {
	check_authz_json('brand_manage');
	$id = intval($_POST['id']);
	$name = json_str_iconv(trim($_POST['val']));

	if ($exc->num('brand_name', $name, $id) != 0) {
		make_json_error(sprintf($_LANG['brandname_exist'], $name));
	}
	else if ($exc->edit('brand_name = \'' . $name . '\'', $id)) {
		admin_log($name, 'edit', 'brand');
		make_json_result(stripslashes($name));
	}
	else {
		make_json_result(sprintf($_LANG['brandedit_fail'], $name));
	}
}
else if ($_REQUEST['act'] == 'edit_brand_letter') {
	check_authz_json('brand_manage');
	$id = intval($_POST['id']);
	$name = json_str_iconv(trim($_POST['val']));

	if ($exc->num('brand_letter', $name, $id) != 0) {
		make_json_error(sprintf($_LANG['brandname_exist'], $name));
	}
	else if ($exc->edit('brand_letter = \'' . $name . '\'', $id)) {
		admin_log($name, 'edit', 'brand');
		make_json_result(stripslashes($name));
	}
	else {
		make_json_result(sprintf($_LANG['brandedit_fail'], $name));
	}
}
else if ($_REQUEST['act'] == 'add_brand') {
	$brand = empty($_REQUEST['brand']) ? '' : json_str_iconv(trim($_REQUEST['brand']));

	if (brand_exists($brand)) {
		make_json_error($_LANG['brand_name_exist']);
	}
	else {
		$sql = 'INSERT INTO ' . $ecs->table('brand') . '(brand_name)' . ('VALUES ( \'' . $brand . '\')');
		$db->query($sql);
		$brand_id = $db->insert_id();
		$arr = array('id' => $brand_id, 'brand' => $brand);
		make_json_result($arr);
	}
}
else if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('brand_manage');
	$id = intval($_POST['id']);
	$order = intval($_POST['val']);
	$name = $exc->get_name($id);

	if ($exc->edit('sort_order = \'' . $order . '\'', $id)) {
		admin_log(addslashes($name), 'edit', 'brand');
		make_json_result($order);
	}
	else {
		make_json_error(sprintf($_LANG['brandedit_fail'], $name));
	}
}
else if ($_REQUEST['act'] == 'toggle_show') {
	check_authz_json('brand_manage');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	$exc->edit('is_show=\'' . $val . '\'', $id);
	make_json_result($val);
}
else if ($_REQUEST['act'] == 'toggle_recommend') {
	check_authz_json('brand_manage');
	$brand_id = intval($_POST['id']);
	$is_recommend = intval($_POST['val']);

	if ($db->getOne('select count(id) from ' . $ecs->table('brand_extend') . (' where brand_id=\'' . $brand_id . '\''))) {
		$extend_sql = 'update ' . $ecs->table('brand_extend') . (' set is_recommend=\'' . $is_recommend . '\' where brand_id=\'' . $brand_id . '\'');
	}
	else {
		$extend_sql = 'INSERT INTO ' . $ecs->table('brand_extend') . (' (brand_id,is_recommend) values (\'' . $brand_id . '\',\'' . $is_recommend . '\')');
	}

	$db->query($extend_sql);
	make_json_result($is_recommend);
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('brand_manage');
	$id = intval($_GET['id']);
	get_del_batch('', $id, array('brand_logo'), 'brand_id', 'brand', 0, DATA_DIR . '/brandlogo/');
	$exc->drop($id);
	$sql = 'UPDATE ' . $ecs->table('goods') . (' SET brand_id=0 WHERE brand_id=\'' . $id . '\'');
	$db->query($sql);
	$url = 'brand.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'drop_logo') {
	admin_priv('brand_manage');
	$brand_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
	get_del_batch('', $brand_id, array('brand_logo'), 'brand_id', 'brand', 0, DATA_DIR . '/brandlogo/');
	$sql = 'UPDATE ' . $ecs->table('brand') . (' SET brand_logo = \'\' WHERE brand_id = \'' . $brand_id . '\'');
	$db->query($sql);
	$link = array(
		array('text' => $_LANG['brand_edit_lnk'], 'href' => 'brand.php?act=edit&id=' . $brand_id),
		array('text' => $_LANG['brand_list_lnk'], 'href' => 'brand.php?act=list')
		);
	sys_msg($_LANG['drop_brand_logo_success'], 0, $link);
}
else if ($_REQUEST['act'] == 'query') {
	$brand_list = get_brandlist();
	$smarty->assign('brand_list', $brand_list['brand']);
	$smarty->assign('filter', $brand_list['filter']);
	$smarty->assign('record_count', $brand_list['record_count']);
	$smarty->assign('page_count', $brand_list['page_count']);
	make_json_result($smarty->fetch('brand_list.dwt'), '', array('filter' => $brand_list['filter'], 'page_count' => $brand_list['page_count']));
}

?>
