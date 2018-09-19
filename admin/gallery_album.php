<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function getgallery_child($album_id = 0, $type = 0)
{
	$child_arr = '';

	if (0 < $album_id) {
		if ($type == 1) {
			$child_arr = $album_id;
		}

		$sql = 'SELECT  album_id FROM ' . $GLOBALS['ecs']->table('gallery_album') . ('WHERE parent_album_id = \'' . $album_id . '\'');
		$child_list = $GLOBALS['db']->getAll($sql);

		if (!empty($child_list)) {
			foreach ($child_list as $k => $v) {
				$child_arr .= ',' . $v['album_id'];
				$child_tree = getgallery_child($v['album_id']);

				if ($child_tree) {
					$child_arr .= ',' . $child_tree;
				}
			}
		}
	}

	$child_arr = get_del_str_comma($child_arr);
	return $child_arr;
}

function get_pzd_list($ru_id)
{
	$result = get_filter();

	if ($result === false) {
		$filter['album_mame'] = empty($_REQUEST['album_mame']) ? '' : trim($_REQUEST['album_mame']);
		$filter['parent_id'] = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
		$filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? intval($_REQUEST['seller_list']) : 0;
		$where = ' WHERE 1 ';

		if ($filter['album_mame']) {
			$where .= ' AND ga.album_mame LIKE \'%' . mysql_like_quote($filter['album_mame']) . '%\'';
		}

		$where .= ' AND ga.parent_album_id = \'' . $filter['parent_id'] . '\' ';
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
						$where .= ' AND ga.ru_id = \'' . $filter['merchant_id'] . '\' ';
					}
					else if ($filter['store_search'] == 2) {
						$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
					}
					else if ($filter['store_search'] == 3) {
						$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
					}

					if (1 < $filter['store_search']) {
						$where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . (' WHERE msi.user_id = ga.ru_id ' . $store_where . ') > 0 ');
					}
				}
				else {
					$where .= ' AND ga.ru_id = 0';
				}
			}
		}

		if ($filter['seller_list'] == 2) {
			$where .= ' AND ga.ru_id = 0  AND suppliers_id > 0';
		}
		else {
			$where .= !empty($filter['seller_list']) ? ' AND ga.ru_id > 0 AND suppliers_id = 0' : ' AND ga.ru_id = 0 AND suppliers_id = 0';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('gallery_album') . ' AS ga' . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT album_id,ru_id,album_mame,album_cover,album_desc,sort_order,suppliers_id FROM' . $GLOBALS['ecs']->table('gallery_album') . ' AS ga' . ('  ' . $where) . ' ORDER BY ru_id ,sort_order ASC LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $k => $v) {
		if (0 < $v['ru_id']) {
			$row[$k]['shop_name'] = get_shop_name($v['ru_id'], 1);
		}
		else if (0 < $v['suppliers_id']) {
			$row[$k]['shop_name'] = get_table_date('suppliers', 'suppliers_id=\'' . $v['suppliers_id'] . '\'', array('suppliers_name'), 2);
		}
		else {
			$row[$k]['shop_name'] = '自营';
		}

		$row[$k]['gallery_count'] = $GLOBALS['db']->getOne('SELECT COUNT(\'pic_id\') FROM' . $GLOBALS['ecs']->table('pic_album') . ' WHERE album_id = \'' . $v['album_id'] . '\'');
	}

	$arr = array('pzd_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function upload_article_file($upload, $file = '')
{
	if (!make_dir('../' . DATA_DIR . '/gallery_album')) {
		return false;
	}

	$filename = cls_image::random_filename() . substr($upload['name'], strpos($upload['name'], '.'));
	$path = ROOT_PATH . DATA_DIR . '/gallery_album/' . $filename;

	if (move_upload_file($upload['tmp_name'], $path)) {
		return DATA_DIR . '/gallery_album/' . $filename;
	}
	else {
		return false;
	}
}

function get_pic_album($album_id = 0)
{
	$result = get_filter();

	if ($result === false) {
		$filter['album_id'] = $album_id;
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('pic_album') . ' WHERE album_id = \'' . $filter['album_id'] . '\'';
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT pic_id,ru_id,album_id,pic_name,pic_file,pic_size,pic_spec,pic_thumb,pic_image FROM ' . $GLOBALS['ecs']->table('pic_album') . ' WHERE album_id = \'' . $filter['album_id'] . '\' ORDER BY pic_id DESC LIMIT ' . $filter['start'] . ',' . $filter['page_size'] . '';
		$gsql = 'SELECT album_id, album_mame, parent_album_id, sort_order, album_cover, album_desc, add_time FROM' . $GLOBALS['ecs']->table('gallery_album') . ' WHERE parent_album_id = \'' . $filter['album_id'] . '\' ORDER BY sort_order ASC';
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);
	$res = $GLOBALS['db']->getAll($gsql);

	foreach ($res as $k => $v) {
		if (isset($v['album_cover']) && $v['album_cover']) {
			$res[$k]['album_cover'] = get_image_path($v['album_id'], $v['album_cover']);
		}

		if (!empty($res[$k]['album_cover']) && (strpos($res[$k]['album_cover'], 'http://') === false && strpos($res[$k]['album_cover'], 'https://') === false)) {
			$res[$k]['album_cover'] = $GLOBALS['ecs']->url() . $res[$k]['album_cover'];
		}
	}

	foreach ($row as $k => $v) {
		$row[$k]['verific_pic'] = 0;
		if (verific_pic($v['pic_file']) || verific_pic($v['pic_thumb']) || verific_pic($v['pic_image'])) {
			$row[$k]['verific_pic'] = 1;
		}

		if (isset($v['pic_file']) && $v['pic_file']) {
			$row[$k]['pic_file'] = get_image_path($v['pic_id'], $v['pic_file']);
		}

		if (!empty($row[$k]['pic_file']) && (strpos($row[$k]['pic_file'], 'http://') === false && strpos($row[$k]['pic_file'], 'https://') === false)) {
			$row[$k]['pic_file'] = $GLOBALS['ecs']->url() . $row[$k]['pic_file'];
		}

		if (0 < $v['pic_size']) {
			$row[$k]['pic_size'] = number_format($v['pic_size'] / 1024, 2) . 'k';
		}
	}

	$arr = array('pzd_list' => $row, 'gal_list' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require dirname(__FILE__) . '/includes/cls_FileUpload.php';
require_once ROOT_PATH . 'includes/cls_image.php';
$exc = new exchange($ecs->table('gallery_album'), $db, 'album_id', 'album_mame');
$adminru = get_admin_ru_id();
$smarty->assign('priv_ru', 1);
$allow_file_types = '|GIF|JPG|PNG|JPEG|';

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('ur_here', $_LANG['gallery_album']);
	$smarty->assign('action_link', array('text' => $_LANG['add_album'], 'href' => 'gallery_album.php?act=add'));
	$parent_id = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
	$offline_store = get_pzd_list($adminru['ru_id']);

	if (0 < $parent_id) {
		$sql = 'SELECT parent_album_id FROM' . $ecs->table('gallery_album') . ('WHERE album_id = \'' . $parent_id . '\'');
		$parent_album_id = $db->getOne($sql);
		$smarty->assign('action_link1', array('text' => $_LANG['return_to_superior'], 'href' => 'gallery_album.php?act=list&parent_id=' . $parent_album_id . '&seller_list=' . $offline_store['filter']['seller_list']));
	}

	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$smarty->assign('gallery_album', $offline_store['pzd_list']);
	$smarty->assign('filter', $offline_store['filter']);
	$smarty->assign('record_count', $offline_store['record_count']);
	$smarty->assign('page_count', $offline_store['page_count']);
	$smarty->assign('full_page', 1);
	self_seller(BASENAME($_SERVER['PHP_SELF']));
	$smarty->display('gallery_album.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$offline_store = get_pzd_list($adminru['ru_id']);
	$smarty->assign('gallery_album', $offline_store['pzd_list']);
	$smarty->assign('filter', $offline_store['filter']);
	$smarty->assign('record_count', $offline_store['record_count']);
	$smarty->assign('page_count', $offline_store['page_count']);
	make_json_result($smarty->fetch('gallery_album.dwt'), '', array('filter' => $offline_store['filter'], 'page_count' => $offline_store['page_count']));
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		if ($_REQUEST['act'] == 'add') {
			$smarty->assign('ur_here', $_LANG['add_album']);
		}
		else {
			$smarty->assign('ur_here', $_LANG['edit_album']);
		}

		$smarty->assign('action_link', array('text' => $_LANG['gallery_album'], 'href' => 'gallery_album.php?act=list'));
		$album_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
		$album_info = array('ru_id' => 0);

		if (0 < $album_id) {
			$album_info = get_goods_gallery_album(2, $album_id);
		}

		if ($_REQUEST['act'] == 'add') {
			$cat_select = gallery_cat_list(0, 0, false, 0, true);

			foreach ($cat_select as $k => $v) {
				if ($v['level']) {
					$level = str_repeat('&nbsp;', $v['level'] * 4);
					$cat_select[$k]['name'] = $level . $v['name'];
				}
			}

			$album_info['parent_album_id'] = $parent_id;
			$album_info['ru_id'] = $adminru['ru_id'];
			$smarty->assign('cat_select', $cat_select);
		}
		else {
			$cat_select = gallery_cat_list(0, $cat_info['parent_id'], false, 0, true, $album_info['ru_id'], $album_info['suppliers_id']);
			$cat_child = get_cat_child($album_id);

			foreach ($cat_select as $k => $v) {
				if ($v['level']) {
					$level = str_repeat('&nbsp;', $v['level'] * 4);
					$cat_select[$k]['name'] = $level . $v['name'];
				}

				if (!empty($cat_child) && in_array($v['album_id'], $cat_child)) {
					unset($cat_select[$k]);
				}
			}

			$smarty->assign('cat_select', $cat_select);
		}

		$smarty->assign('album_info', $album_info);
		$form_action = $_REQUEST['act'] == 'add' ? 'insert' : 'update';
		$smarty->assign('form_action', $form_action);
		$smarty->display('gallery_album_info.dwt');
	}
	else {
		if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
			$album_mame = isset($_REQUEST['album_mame']) ? addslashes($_REQUEST['album_mame']) : '';
			$album_desc = isset($_REQUEST['album_desc']) ? addslashes($_REQUEST['album_desc']) : '';
			$sort_order = isset($_REQUEST['sort_order']) ? intval($_REQUEST['sort_order']) : 50;
			$parent_id = isset($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : 0;
			$seller_id = isset($_REQUEST['seller_id']) ? intval($_REQUEST['seller_id']) : 0;

			if ($_REQUEST['act'] == 'insert') {
				$is_only = $exc->is_only('album_mame', $album_mame, 0, 'ru_id = ' . $adminru['ru_id']);

				if (!$is_only) {
					sys_msg(sprintf($_LANG['title_exist'], stripslashes($album_mame)), 1);
				}

				$file_url = '';
				if (isset($_FILES['album_cover']['error']) && $_FILES['album_cover']['error'] == 0 || !isset($_FILES['album_cover']['error']) && isset($_FILES['album_cover']['tmp_name']) && $_FILES['album_cover']['tmp_name'] != 'none') {
					if (!check_file_type($_FILES['album_cover']['tmp_name'], $_FILES['album_cover']['name'], $allow_file_types)) {
						sys_msg($_LANG['invalid_file']);
					}

					$res = upload_article_file($_FILES['album_cover']);

					if ($res != false) {
						$file_url = $res;
					}
				}

				if ($file_url == '') {
					$file_url = $_POST['file_url'];
				}

				$time = gmtime();
				$sql = 'INSERT INTO' . $ecs->table('gallery_album') . '(`parent_album_id`,`album_mame`,`album_cover`,`album_desc`,`sort_order`,`add_time`)' . (' VALUES (\'' . $parent_id . '\',\'' . $album_mame . '\',\'' . $file_url . '\',\'' . $album_desc . '\',\'' . $sort_order . '\',\'' . $time . '\')');

				if ($db->query($sql) == true) {
					$link[0]['text'] = $_LANG['continue_add_album'];
					$link[0]['href'] = 'gallery_album.php?act=add';
					$link[1]['text'] = $_LANG['bank_list'];
					$link[1]['href'] = 'gallery_album.php?act=list';
					sys_msg($_LANG['add_succeed'], 0, $link);
				}
			}
			else {
				$album_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
				$album_info = get_goods_gallery_album(2, $album_id, array('suppliers_id'));
				$is_only_where = '';

				if (0 < $album_info['suppliers_id']) {
					$is_only_where = 'suppliers_id = \'' . $album_info['suppliers_id'] . '\'';
				}
				else {
					$is_only_where = 'ru_id = \'' . $seller_id . '\'';
				}

				$is_only = $exc->is_only('album_mame', $album_mame, 0, 'ru_id = ' . $seller_id . (' AND album_id != \'' . $album_id . '\''));

				if (!$is_only) {
					sys_msg(sprintf($_LANG['title_exist'], stripslashes($album_mame)), 1);
				}

				$file_url = '';
				if (isset($_FILES['album_cover']['error']) && $_FILES['album_cover']['error'] == 0 || !isset($_FILES['album_cover']['error']) && isset($_FILES['album_cover']['tmp_name']) && $_FILES['album_cover']['tmp_name'] != 'none') {
					if (!check_file_type($_FILES['album_cover']['tmp_name'], $_FILES['album_cover']['name'], $allow_file_types)) {
						sys_msg($_LANG['invalid_file']);
					}

					$res = upload_article_file($_FILES['album_cover']);

					if ($res != false) {
						$file_url = $res;
					}
				}

				if ($file_url == '') {
					$file_url = $_POST['file_url'];
				}

				$old_url = get_goods_gallery_album(0, $album_id, array('album_cover'));
				if ($old_url != '' && $old_url != $file_url && strpos($old_url, 'http://') === false && strpos($old_url, 'https://') === false) {
					@unlink(ROOT_PATH . $old_url);
					$del_arr_img[] = $old_url;
					get_oss_del_file($del_arr_img);
				}

				$sql = 'UPDATE ' . $ecs->table('gallery_album') . (' SET album_mame=\'' . $album_mame . '\',album_cover=\'' . $file_url . '\'') . (',album_desc=\'' . $album_desc . '\',sort_order=\'' . $sort_order . '\',parent_album_id=\'' . $parent_id . '\' WHERE album_id = \'' . $album_id . '\'');

				if ($db->query($sql) == true) {
					$link[0]['text'] = $_LANG['bank_list'];
					$link[0]['href'] = 'gallery_album.php?act=list';
					sys_msg($_LANG['edit_succeed'], 0, $link);
				}
			}
		}
		else if ($_REQUEST['act'] == 'view') {
			$album_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
			$sql = 'SELECT album_mame FROM ' . $ecs->table('gallery_album') . (' WHERE album_id = \'' . $album_id . '\'');
			$album_mame = $db->getOne($sql);
			$smarty->assign('ur_here', sprintf($_LANG['view_pic'], stripslashes($album_mame)));
			$smarty->assign('action_link', array('text' => '上传图片', 'spec' => 'ectype=\'addpic_album\'', 'href' => 'gallery_album.php?act=view&id=' . $album_id));
			$smarty->assign('album_id', $album_id);
			$album_info = get_goods_gallery_album(2, $album_id, array('suppliers_id,ru_id'));
			$cat_select = gallery_cat_list(0, 0, false, 0, true, $album_info['ru_id'], $album_info['suppliers_id']);
			$cat_select = gallery_cat_list(0, 0, false, 0, true, '', 1);
			$sql = 'SELECT parent_album_id,ru_id FROM ' . $ecs->table('gallery_album') . (' WHERE album_id = \'' . $album_id . '\' LIMIT 1');
			$album_info = $db->getRow($sql);
			$parent_album_id = $album_info['parent_album_id'];
			$smarty->assign('album_ru', $album_info['ru_id']);
			$smarty->assign('parent_album_id', $parent_album_id);

			foreach ($cat_select as $k => $v) {
				if ($v['level']) {
					$level = str_repeat('&nbsp;', $v['level'] * 4);
					$cat_select[$k]['name'] = $level . $v['name'];
				}
			}

			$smarty->assign('cat_select', $cat_select);
			$offline_store = get_pic_album($album_id);
			$gallery_list = gallery_child_cat_list($album_id);

			if ($parent_album_id) {
				$smarty->assign('action_link', array('text' => '上传图片', 'spec' => 'ectype=\'addpic_album\'', 'href' => 'gallery_album.php?act=view&id=' . $parent_album_id));
			}
			else {
				$smarty->assign('action_link', array('text' => '上传图片', 'spec' => 'ectype=\'addpic_album\'', 'href' => 'gallery_album.php?act=list'));
			}

			$gallery_num = gallery_child_cat_num($album_id);
			$smarty->assign('gallery_list', $gallery_list);
			$smarty->assign('gallery_num', $gallery_num);
			$smarty->assign('pic_album', $offline_store['pzd_list']);
			$smarty->assign('filter', $offline_store['filter']);
			$smarty->assign('record_count', $offline_store['record_count']);
			$smarty->assign('page_count', $offline_store['page_count']);
			$smarty->assign('full_page', 1);
			$smarty->display('pic_album.dwt');
		}
		else if ($_REQUEST['act'] == 'pic_query') {
			$album_id = isset($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : 0;
			$offline_store = get_pic_album($album_id);
			$smarty->assign('pic_album', $offline_store['pzd_list']);
			$smarty->assign('gallery_list', $offline_store['gal_list']);
			$smarty->assign('filter', $offline_store['filter']);
			$smarty->assign('record_count', $offline_store['record_count']);
			$smarty->assign('page_count', $offline_store['page_count']);
			make_json_result($smarty->fetch('pic_album.dwt'), '', array('filter' => $offline_store['filter'], 'page_count' => $offline_store['page_count']));
		}
		else if ($_REQUEST['act'] == 'remove') {
			require ROOT_PATH . '/includes/lib_visual.php';
			$album_id = intval($_GET['id']);
			$sql = 'SELECT COUNT(*) FROM' . $ecs->table('gallery_album') . (' WHERE parent_album_id = \'' . $album_id . '\'');
			$album_count = $db->getOne($sql);

			if (0 < $album_count) {
				make_json_error('不是末级相册，不能删除');
			}
			else {
				$sql = 'SELECT parent_album_id FROM ' . $ecs->table('gallery_album') . (' WHERE album_id = \'' . $album_id . '\'');
				$res = $db->getOne($sql);
				$old_url = get_goods_gallery_album(0, $album_id, array('album_cover'));
				if ($old_url != '' && @strpos($old_url, 'http://') === false && @strpos($old_url, 'https://') === false) {
					@unlink(ROOT_PATH . $old_url);
					$del_arr_img[] = $old_url;
					get_oss_del_file($del_arr_img);
				}

				$dir = ROOT_PATH . 'data/gallery_album/' . $album_id;
				$rmdir = del_DirAndFile($dir);
				$sql = 'DELETE FROM' . $ecs->table('pic_album') . 'WHERE album_id = ' . $album_id;
				$db->query($sql);
				$exc->drop($album_id);

				if (0 < $res) {
					$url = 'gallery_album.php?act=gallery_query&id=' . $res;
				}
				else {
					$url = 'gallery_album.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
				}

				ecs_header('Location: ' . $url . "\n");
			}

			exit();
		}
		else if ($_REQUEST['act'] == 'gallery_query') {
			$album_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
			$sql = 'SELECT album_mame FROM ' . $ecs->table('gallery_album') . (' WHERE album_id = \'' . $album_id . '\'');
			$album_mame = $db->getOne($sql);
			$smarty->assign('album_id', $album_id);
			$album_list = get_goods_gallery_album(1, 0, array('album_id', 'album_mame'));
			$smarty->assign('album_list', $album_list);
			$offline_store = get_pic_album($album_id);
			$gallery_list = gallery_child_cat_list($album_id);
			$gallery_num = gallery_child_cat_num($album_id);
			$smarty->assign('gallery_list', $gallery_list);
			$smarty->assign('gallery_num', $gallery_num);
			$smarty->assign('pic_album', $offline_store['pzd_list']);
			$smarty->assign('filter', $offline_store['filter']);
			$smarty->assign('record_count', $offline_store['record_count']);
			$smarty->assign('page_count', $offline_store['page_count']);
			make_json_result($smarty->fetch('pic_album.dwt'), '', array('filter' => $offline_store['filter'], 'page_count' => $offline_store['page_count']));
		}
		else if ($_REQUEST['act'] == 'pic_remove') {
			require ROOT_PATH . '/includes/cls_json.php';
			$json = new JSON();
			$result = array('error' => '', 'content' => '', 'url' => '');
			$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
			$pic_info = gallery_pic_album(2, $id, array('pic_file', 'pic_thumb', 'pic_image', 'album_id'));
			if ($pic_info['pic_file'] != '' && @strpos($pic_info['pic_file'], 'http://') === false && @strpos($pic_info['pic_file'], 'https://') === false) {
				$arr_img[] = $pic_info['pic_file'];
			}

			if ($pic_info['pic_thumb'] != '' && @strpos($pic_info['pic_thumb'], 'http://') === false && @strpos($pic_info['pic_thumb'], 'https://') === false) {
				$arr_img[] = $pic_info['pic_thumb'];
			}

			if ($pic_info['pic_image'] != '' && @strpos($pic_info['pic_image'], 'http://') === false && @strpos($pic_info['pic_image'], 'https://') === false) {
				$arr_img[] = $pic_info['pic_image'];
			}

			dsc_unlink($arr_img);
			get_oss_del_file($arr_img);
			$sql = 'DELETE FROM' . $ecs->table('pic_album') . (' WHERE pic_id = \'' . $id . '\' ');

			if ($db->query($sql)) {
				$result['error'] = 0;
				$result['id'] = $id;
			}
			else {
				$result['error'] = 1;
				$result['content'] = '系统出错，请重试！';
			}

			exit(json_encode($result));
		}
		else if ($_REQUEST['act'] == 'remove_batch') {
			require ROOT_PATH . '/includes/lib_visual.php';
			$checkboxes = !empty($_REQUEST['checkboxes']) ? $_REQUEST['checkboxes'] : array();

			if (!empty($checkboxes)) {
				$unremove_arr = array();

				foreach ($checkboxes as $k => $v) {
					$sql = 'SELECT COUNT(*) FROM' . $ecs->table('gallery_album') . (' WHERE parent_album_id = \'' . $v . '\'');
					$album_count = $db->getOne($sql);

					if (0 < $album_count) {
						unset($checkboxes[$k]);
						$unremove_arr[] = $v;
					}
					else {
						$dir = ROOT_PATH . 'data/gallery_album/' . $v;
						$rmdir = del_DirAndFile($dir);
						$sql = 'DELETE FROM' . $ecs->table('pic_album') . 'WHERE album_id = ' . $v;
						$db->query($sql);
					}
				}

				$sql = 'SELECT album_cover FROM ' . $ecs->table('gallery_album') . ' WHERE album_id' . db_create_in($checkboxes);
				$album_cover = $db->getAll($sql);

				if (!empty($album_cover)) {
					foreach ($album_cover as $k => $v) {
						if ($v['album_cover'] != '' && @strpos($v['album_cover'], 'http://') === false && @strpos($v['album_cover'], 'https://') === false) {
							@unlink(ROOT_PATH . $v['album_cover']);
							$del_arr_img[] = $v['album_cover'];
							get_oss_del_file($del_arr_img);
						}
					}
				}

				$sql = 'DELETE FROM' . $ecs->table('gallery_album') . ' WHERE album_id' . db_create_in($checkboxes);

				if ($db->query($sql) == true) {
					$back_msg = $_LANG['delete_succeed'];
					$link[0] = array('text' => $_LANG['back_list'], 'href' => 'gallery_album.php?act=list&' . list_link_postfix());

					if (!empty($unremove_arr)) {
						$sql = 'SELECT album_mame FROM' . $ecs->table('gallery_album') . 'WHERE album_id' . db_create_in($unremove_arr);
						$album_mame_arr = $db->getAll($sql);

						if (!empty($album_mame_arr)) {
							$album_mame_arr = arr_foreach($album_mame_arr);
							$album_mame_arr = implode(',', $album_mame_arr);
							$back_msg = sprintf($_LANG['unremove_succeed'], stripslashes($album_mame_arr));
						}
					}

					sys_msg($back_msg, 0, $link);
				}
			}
			else {
				$link[] = array('text' => $_LANG['back_list'], 'href' => 'gallery_album.php?act=list&' . list_link_postfix());
				sys_msg($_LANG['delete_fail'], 0, $link);
			}
		}
		else if ($_REQUEST['act'] == 'edit_sort_order') {
			$id = intval($_POST['id']);
			$order = json_str_iconv(trim($_POST['val']));

			if ($exc->edit('sort_order = \'' . $order . '\'', $id)) {
				clear_cache_files();
				make_json_result(stripslashes($order));
			}
			else {
				make_json_error($db->error());
			}
		}
		else if ($_REQUEST['act'] == 'upload_pic') {
			include_once ROOT_PATH . '/includes/cls_image.php';
			$image = new cls_image($_CFG['bgcolor']);
			require_once ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php';
			$result = array('error' => 0, 'pic' => '', 'name' => '');
			$album_id = isset($_REQUEST['album_id']) && !empty($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : 0;
			$bucket_info = get_bucket_info();

			if ($_FILES['file']['name']) {
				if ($realname) {
					$extname = strtolower(substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.') + 1));
				}
				else {
					$extname = strtolower(substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.') + 1));
				}
			}

			if ($extname == 'mp4') {
				$video_path = ROOT_PATH . DATA_DIR . '/uploads/pic_album/' . $album_id . '/';

				if (!file_exists($video_path)) {
					make_dir($video_path);
				}

				$videoOther = array(
					'isRandName' => true,
					'allowType'  => array('mp4'),
					'FilePath'   => $video_path,
					'MAXSIZE'    => 20000000
					);
				$upload = new FileUpload($videoOther);
				$pic_id = 0;

				if ($upload->uploadFile('file')) {
					$video = DATA_DIR . '/uploads/pic_album/' . $album_id . '/' . $upload->getNewFileName();
					$ru_id = get_goods_gallery_album(0, $album_id, array('ru_id'));
					$image_name = explode('.', $_FILES['file']['name']);
					$pic_name = $image_name[0];
					$pic_size = intval($_FILES['file']['size']);
					$add_time = gmtime();
					$sql = 'INSERT INTO' . $ecs->table('pic_album') . ('(`ru_id`,`album_id`,`pic_name`,`pic_file`,`pic_size`,`pic_spec`,`add_time`) VALUES(\'' . $ru_id . '\',\'' . $album_id . '\',\'' . $pic_name . '\',\'' . $video . '\',\'' . $pic_size . '\',\'\',\'' . $add_time . '\')');
					$db->query($sql);
					$pic_id = $db->insert_id();
					$arr_img = array($video);
					get_oss_add_file($arr_img);
					$result['picid'] = $pic_id;
				}
				else {
					$result['error'] = 1;
					$result['massege'] = $upload->getErrorMsg();
				}

				if ($pic_id) {
					if ($GLOBALS['_CFG']['open_oss'] == 1 && $bucket_info['is_delimg'] == 1) {
						dsc_unlink($video);
					}
				}
			}
			else {
				$images = '';
				$goods_thumb = '';
				$original_img = '';
				$old_original_img = '';
				$file_url = '';
				$pic_name = '';
				$pic_size = 0;
				$proc_thumb = isset($GLOBALS['shop_id']) && 0 < $GLOBALS['shop_id'] ? false : true;
				if (isset($_FILES['file']['error']) && $_FILES['file']['error'] == 0 || !isset($_FILES['file']['error']) && isset($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'] != 'none') {
					if (!check_file_type($_FILES['file']['tmp_name'], $_FILES['file']['name'], $allow_file_types)) {
						sys_msg($_LANG['invalid_file']);
					}

					$image_name = explode('.', $_FILES['file']['name']);
					$pic_name = $image_name[0];
					$pic_size = intval($_FILES['file']['size']);
					$dir = 'gallery_album/' . $album_id . '/original_img';
					$original_img = $image->upload_image($_FILES['file'], $dir);
					$images = $original_img;
					getimagesize('../' . $original_img);
					if ($proc_thumb && 0 < $image->gd_version() && $image->check_img_function($_FILES['file']['type'])) {
						if ($_CFG['thumb_width'] != 0 || $_CFG['thumb_height'] != 0) {
							$goods_thumb = $image->make_thumb('../' . $original_img, $GLOBALS['_CFG']['thumb_width'], $GLOBALS['_CFG']['thumb_height'], '../data/gallery_album/' . $album_id . '/thumb_img/');
							$goods_thumb = str_replace('../', '', $goods_thumb);

							if ($goods_thumb === false) {
								sys_msg($image->error_msg(), 1, array(), false);
							}
						}
						else {
							$goods_thumb = $original_img;
						}

						if ($_CFG['image_width'] != 0 || $_CFG['image_height'] != 0) {
							$images = $image->make_thumb('../' . $original_img, $GLOBALS['_CFG']['image_width'], $GLOBALS['_CFG']['image_height'], '../data/gallery_album/' . $album_id . '/images/');
						}
						else {
							$images = $original_img;
						}

						if (0 < intval($_CFG['watermark_place']) && !empty($GLOBALS['_CFG']['watermark'])) {
							if ($image->add_watermark($images, '', $GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']) === false) {
								sys_msg($image->error_msg(), 1, array(), false);
							}
						}
					}

					if ($images) {
						$images = str_replace('../', '', $images);
					}

					$result['data'] = array('original_img' => $original_img, 'goods_thumb' => $goods_thumb);
					$result['pic'] = get_image_path(0, $original_img);
					list($width, $height, $type, $attr) = getimagesize('../' . $original_img);
					$pic_spec = $width . 'x' . $height;
					$add_time = gmtime();
					$ru_id = get_goods_gallery_album(0, $album_id, array('ru_id'));
					$arr_img = array($original_img, $goods_thumb, $images);
					get_oss_add_file($arr_img);
					$sql = 'INSERT INTO' . $ecs->table('pic_album') . ('(`ru_id`,`album_id`,`pic_name`,`pic_file`,`pic_size`,`pic_spec`,`add_time`,`pic_thumb`,`pic_image`) VALUES(\'' . $ru_id . '\',\'' . $album_id . '\',\'' . $pic_name . '\',\'' . $original_img . '\',\'' . $pic_size . '\',\'' . $pic_spec . '\',\'' . $add_time . '\',\'' . $goods_thumb . '\',\'' . $images . '\')');
					$db->query($sql);
					$pic_id = $db->insert_id();

					if ($pic_id) {
						if ($GLOBALS['_CFG']['open_oss'] == 1 && $bucket_info['is_delimg'] == 1) {
							$album_images = array($original_img, $goods_thumb, $images);
							dsc_unlink($album_images);
						}
					}

					$result['picid'] = $pic_id;
				}
				else {
					$result['error'] = 1;
					$result['massege'] = '上传有误，清检查服务器配置！';
				}
			}

			exit(json_encode($result));
		}
		else if ($_REQUEST['act'] == 'goods_video') {
			$result = array('error' => 0, 'goods_id' => 0, 'massege' => '', 'goods_video' => '', 'goods_video_path' => '');
			$goods_id = isset($_REQUEST['goods_id']) && !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
			$result['goods_id'] = $goods_id;
			$bucket_info = get_bucket_info();

			if ($_FILES['file']['name']) {
				if ($realname) {
					$extname = strtolower(substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.') + 1));
				}
				else {
					$extname = strtolower(substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.') + 1));
				}
			}

			$video_path = ROOT_PATH . DATA_DIR . '/uploads/goods/' . $goods_id . '/';

			if (!file_exists($video_path)) {
				make_dir($video_path);
			}

			if ($extname == 'mp4') {
				$videoOther = array(
					'isRandName' => true,
					'allowType'  => array('mp4'),
					'FilePath'   => $video_path,
					'MAXSIZE'    => 20000000
					);
				$upload = new FileUpload($videoOther);

				if ($upload->uploadFile('file')) {
					$goods_video = DATA_DIR . '/uploads/goods/' . $goods_id . '/' . $upload->getNewFileName();
					$goodsOther = array($goods_video);
					get_oss_add_file($goodsOther);

					if ($goods_id) {
						$sql = 'SELECT goods_video FROM' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\'');
						$video_old = $GLOBALS['db']->getOne($sql, true);
						dsc_unlink($video_old);
						$arr[] = $video_old;
						get_oss_del_file($arr);
						$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . (' SET goods_video = \'' . $goods_video . '\' WHERE goods_id = \'' . $goods_id . '\'');
						$GLOBALS['db']->query($sql);
					}
					else {
						$admin_id = get_admin_id();
						$_SESSION['goods_video'][$admin_id][] = $goods_video;
					}

					$result['goods_video'] = $goods_video;
					$result['goods_video_path'] = !empty($goods_video) ? get_image_path($goods_id, $goods_video) : '';
				}
				else {
					$result['error'] = 1;
					$result['massege'] = $upload->getErrorMsg();
				}

				if ($goods_id) {
					sleep(3);
					if ($GLOBALS['_CFG']['open_oss'] == 1 && $bucket_info['is_delimg'] == 1) {
						dsc_unlink($goods_video);
					}
				}
			}
			else {
				$result['error'] = 2;
				$result['massege'] = '上传格式有误！';
			}

			exit(json_encode($result));
		}
		else if ($_REQUEST['act'] == 'del_video') {
			$result = array('error' => 0, 'goods_id' => 0, 'massege' => '', 'goods_video' => '');
			$goods_id = isset($_REQUEST['goods_id']) && !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;

			if ($goods_id) {
				$sql = 'SELECT goods_video FROM' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\'');
				$video_old = $GLOBALS['db']->getOne($sql, true);
				dsc_unlink($video_old);
				$arr[] = $video_old;
				get_oss_del_file($arr);
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . (' SET goods_video = \'\' WHERE goods_id = \'' . $goods_id . '\'');
				$GLOBALS['db']->query($sql);
			}
			else {
				get_del_goods_video();
			}

			exit(json_encode($result));
		}
		else if ($_REQUEST['act'] == 'batch') {
			$checkboxes = !empty($_REQUEST['checkboxes']) ? $_REQUEST['checkboxes'] : array();
			$gallery_checkboxes = !empty($_REQUEST['gallery_checkboxes']) ? $_REQUEST['gallery_checkboxes'] : array();
			$pic_checkboxes = !empty($_REQUEST['pic_checkboxes']) ? $_REQUEST['pic_checkboxes'] : array();
			$old_album_id = isset($_REQUEST['old_album_id']) ? intval($_REQUEST['old_album_id']) : 0;
			$album_id = isset($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : 0;
			$type = isset($_REQUEST['type']) ? addslashes($_REQUEST['type']) : '';
			$is_gallery = isset($_REQUEST['is_gallery']) ? addslashes($_REQUEST['is_gallery']) : '';
			$is_pic = isset($_REQUEST['is_pic']) ? addslashes($_REQUEST['is_pic']) : '';

			if (!empty($checkboxes)) {
				if ($type == 'remove') {
					$sql = 'SELECT pic_file,pic_thumb, pic_image FROM' . $ecs->table('pic_album') . ' WHERE pic_id' . db_create_in($pic_checkboxes);
					$pic_info = $db->getAll($sql);

					if (!empty($pic_info)) {
						foreach ($pic_info as $v) {
							if ($v['pic_file'] != '' && @strpos($v['pic_file'], 'http://') === false && @strpos($v['pic_file'], 'https://') === false) {
								dsc_unlink(ROOT_PATH . $v['pic_file']);
								$arr_img[] = $v['pic_file'];
							}

							if ($v['pic_thumb'] != '' && @strpos($v['pic_thumb'], 'http://') === false && @strpos($v['pic_thumb'], 'https://') === false) {
								dsc_unlink(ROOT_PATH . $v['pic_thumb']);
								$arr_img[] = $v['pic_thumb'];
							}

							if ($v['pic_image'] != '' && @strpos($v['pic_image'], 'http://') === false && @strpos($v['pic_image'], 'https://') === false) {
								dsc_unlink(ROOT_PATH . $v['pic_image']);
								$arr_img[] = $v['pic_image'];
							}

							get_oss_del_file($arr_img);
						}
					}

					if ($gallery_checkboxes) {
						$sql = 'DELETE FROM' . $ecs->table('gallery_album') . ' WHERE album_id' . db_create_in($checkboxes);
						$res = $db->query($sql);
					}

					if ($pic_checkboxes) {
						$sql = 'DELETE FROM' . $ecs->table('pic_album') . ' WHERE  pic_id' . db_create_in($checkboxes);
						$res = $db->query($sql);
					}

					if ($res) {
						$link[] = array('text' => $_LANG['bank_list'], 'href' => 'gallery_album.php?act=view&id=' . $old_album_id);
						sys_msg($_LANG['delete_succeed'], 0, $link);
					}
				}
				else if (0 < $album_id) {
					if ($pic_checkboxes) {
						$sql = 'UPDATE' . $ecs->table('pic_album') . ' SET album_id = \'' . $album_id . '\' WHERE pic_id' . db_create_in($checkboxes);
						$res = $db->query($sql);
					}

					if ($gallery_checkboxes) {
						$sql = 'UPDATE' . $ecs->table('gallery_album') . ' SET parent_album_id = \'' . $album_id . '\' WHERE album_id' . db_create_in($checkboxes);
						$res = $db->query($sql);
					}

					if ($res) {
						$link[] = array('text' => $_LANG['bank_list'], 'href' => 'gallery_album.php?act=view&id=' . $old_album_id);
						sys_msg($_LANG['remove_succeed'], 0, $link);
					}
				}
				else {
					$link[] = array('text' => $_LANG['bank_list'], 'href' => 'gallery_album.php?act=view&id=' . $old_album_id);
					sys_msg($_LANG['album_fail'], 1, $link);
				}
			}
			else {
				$link[] = array('text' => $_LANG['bank_list'], 'href' => 'gallery_album.php?act=view&id=' . $old_album_id);
				sys_msg($_LANG['handle_fail'], 1, $link);
			}
		}
		else if ($_REQUEST['act'] == 'move_pic') {
			$album_id = !empty($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : 0;
			$inherit = !empty($_REQUEST['inherit']) ? intval($_REQUEST['inherit']) : 0;
			$sql = 'SELECT suppliers_id,ru_id FROM' . $ecs->table('gallery_album') . ('WHERE album_id = \'' . $album_id . '\' LIMIT 1');
			$album_info = $db->getRow($sql);
			$cat_select = gallery_cat_list(0, 0, false, 0, true, $album_info['ru_id'], $album_info['suppliers_id']);

			foreach ($cat_select as $k => $v) {
				if ($v['level']) {
					$level = str_repeat('&nbsp;', $v['level'] * 4);
					$cat_select[$k]['name'] = $level . $v['name'];
				}
			}

			$smarty->assign('cat_select', $cat_select);
			$smarty->assign('form_act', 'submit_pic');
			$smarty->assign('action_type', 'move_pic');
			$smarty->assign('album_id', $album_id);
			$smarty->assign('inherit', $inherit);
			$html = $smarty->fetch('library/move_category.lbi');
			clear_cache_files();
			make_json_result($html);
		}
		else if ($_REQUEST['act'] == 'submit_pic') {
			$album_id = !empty($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : 0;
			$inherit = !empty($_REQUEST['inherit']) ? intval($_REQUEST['inherit']) : 0;
			$target_album_id = !empty($_REQUEST['target_album_id']) ? intval($_REQUEST['target_album_id']) : 0;
			$cat_select = $album_id;

			if ($inherit == 1) {
				$cat_select = getgallery_child($album_id, 1);
			}

			$sql = 'UPDATE' . $ecs->table('pic_album') . ' SET album_id = \'' . $target_album_id . '\' WHERE album_id' . db_create_in($cat_select);
			$db->query($sql);
			$sql = 'SELECT  parent_album_id FROM ' . $GLOBALS['ecs']->table('gallery_album') . ('WHERE album_id = \'' . $album_id . '\'');
			$parent_album_id = $db->getOne($sql);
			$link[] = array('text' => $_LANG['bank_list'], 'href' => 'gallery_album.php?act=list&parent_id=' . $parent_album_id);
			sys_msg($_LANG['attradd_succed'], 0, $link);
		}
	}
}

?>
