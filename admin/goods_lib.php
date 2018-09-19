<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function list_link($is_add = true, $extension_code = '')
{
	$href = 'goods_lib.php?act=list';

	if (!empty($extension_code)) {
		$href .= '&extension_code=' . $extension_code;
	}

	if (!$is_add) {
		$href .= '&' . list_link_postfix();
	}

	if ($extension_code == 'virtual_card') {
		$text = $GLOBALS['_LANG']['50_virtual_card_list'];
	}
	else {
		$text = $GLOBALS['_LANG']['01_goods_list'];
	}

	return array('href' => $href, 'text' => $text);
}

function add_link($extension_code = '')
{
	$href = 'goods_lib.php?act=add';

	if (!empty($extension_code)) {
		$href .= '&extension_code=' . $extension_code;
	}

	if ($extension_code == 'virtual_card') {
		$text = $GLOBALS['_LANG']['51_virtual_card_add'];
	}
	else {
		$text = $GLOBALS['_LANG']['02_goods_add'];
	}

	return array('href' => $href, 'text' => $text);
}

function lib_is_mer($goods_id)
{
	$sql = ' SELECT user_id FROM ' . $GLOBALS['ecs']->table('goods_lib') . (' WHERE goods_id = \'' . $goods_id . '\' ');
	$one = $GLOBALS['db']->getOne($sql);

	if ($one == 0) {
		return false;
	}
	else {
		return $one;
	}
}

function copy_img($image = '')
{
	if (stripos($image, 'http://') !== false || stripos($image, 'https://') !== false) {
		return $image;
	}

	$newname = '';

	if ($image) {
		$img = ROOT_PATH . $image;
		$pos = strripos(basename($img), '.');
		$newname = dirname($img) . '/' . cls_image::random_filename() . substr(basename($img), $pos);

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();
			$url = $bucket_info['endpoint'] . $image;

			if (!file_exists(dirname($img))) {
				make_dir(dirname($img));
			}

			@get_http_basename($url, $newname, 1);
		}
		else if (!@copy($img, $newname)) {
			return NULL;
		}
	}

	$new_name = str_replace(ROOT_PATH, '', $newname);
	get_oss_add_file(array($new_name));
	return $new_name;
}

function lib_update_goods($goods_id, $field, $value, $content = '', $type = '')
{
	if ($goods_id) {
		clear_cache_files();
		$date = array('model_attr');
		$where = 'goods_id = \'' . $goods_id . '\'';
		$table = 'goods_lib';
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table($table) . (' SET ' . $field . ' = \'' . $value . '\' , ') . $content . ' last_update = \'' . gmtime() . '\' ' . 'WHERE goods_id ' . db_create_in($goods_id);
		return $GLOBALS['db']->query($sql);
	}
	else {
		return false;
	}
}

function lib_delete_goods($goods_id)
{
	if (empty($goods_id)) {
		return NULL;
	}

	$sql = 'SELECT DISTINCT goods_id FROM ' . $GLOBALS['ecs']->table('goods_lib') . ' WHERE goods_id ' . db_create_in($goods_id);
	$goods_id = $GLOBALS['db']->getCol($sql);

	if (empty($goods_id)) {
		return NULL;
	}

	if ($GLOBALS['_CFG']['open_oss'] == 1) {
		$bucket_info = get_bucket_info();
		$urlip = get_ip_url($GLOBALS['ecs']->url());
		$url = $urlip . 'oss.php?act=del_file';
		$Http = new Http();
	}

	$sql = 'SELECT goods_thumb, goods_img, original_img ' . 'FROM ' . $GLOBALS['ecs']->table('goods_lib') . ' WHERE goods_id ' . db_create_in($goods_id);
	$res = $GLOBALS['db']->query($sql);

	while ($goods = $GLOBALS['db']->fetchRow($res)) {
		if (!empty($goods['goods_thumb'])) {
			@unlink('../' . $goods['goods_thumb']);
		}

		if (!empty($goods['goods_img'])) {
			@unlink('../' . $goods['goods_img']);
		}

		if (!empty($goods['original_img'])) {
			@unlink('../' . $goods['original_img']);
		}

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$post_data = array(
				'bucket'    => $bucket_info['bucket'],
				'keyid'     => $bucket_info['keyid'],
				'keysecret' => $bucket_info['keysecret'],
				'is_cname'  => $bucket_info['is_cname'],
				'endpoint'  => $bucket_info['outside_site'],
				'object'    => array($goods['goods_thumb'], $goods['goods_img'], $goods['original_img'])
				);
			$Http->doPost($url, $post_data);
		}
	}

	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('goods_lib') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'SELECT img_url, thumb_url, img_original ' . 'FROM ' . $GLOBALS['ecs']->table('goods_lib_gallery') . ' WHERE goods_id ' . db_create_in($goods_id);
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if (!empty($row['img_url'])) {
			@unlink('../' . $row['img_url']);
		}

		if (!empty($row['thumb_url'])) {
			@unlink('../' . $row['thumb_url']);
		}

		if (!empty($row['img_original'])) {
			@unlink('../' . $row['img_original']);
		}

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$post_data = array(
				'bucket'    => $bucket_info['bucket'],
				'keyid'     => $bucket_info['keyid'],
				'keysecret' => $bucket_info['keysecret'],
				'is_cname'  => $bucket_info['is_cname'],
				'endpoint'  => $bucket_info['outside_site'],
				'object'    => array($row['img_url'], $row['thumb_url'], $row['img_original'])
				);
			$Http->doPost($url, $post_data);
		}
	}

	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('goods_lib_gallery') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	clear_cache_files();
}

function get_import_goods_list($ru_id = 0)
{
	$sql = ' SELECT goods_id, goods_name FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE user_id = \'' . $ru_id . '\' ORDER BY sort_order ');
	$res = $GLOBALS['db']->getAll($sql);
	$goods_list = array();

	foreach ($res as $key => $row) {
		$goods_list[$key]['goods_id'] = $row['goods_id'];
		$goods_list[$key]['goods_name'] = addslashes($row['goods_name']);
	}

	return $goods_list;
}

function get_search_shopname_list($user_list)
{
	$html = '';

	if ($user_list) {
		$html .= '<ul>';

		foreach ($user_list as $key => $user) {
			$html .= '<li data-name=\'' . $user['shop_name'] . '\' data-id=\'' . $user['user_id'] . '\'>' . $user['shop_name'] . '</li>';
		}

		$html .= '</ul>';
	}
	else {
		$html = '<span class="red">查无该会员</span><input name="user_id" value="0" type="hidden" />';
	}

	return $html;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php';
include_once ROOT_PATH . '/includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table('goods_lib'), $db, 'goods_id', 'goods_name');
$exc_gallery = new exchange($ecs->table('goods_lib_gallery'), $db, 'img_id', 'goods_id');
$admin_id = get_admin_id();
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$smarty->assign('review_goods', $GLOBALS['_CFG']['review_goods']);

if ($_REQUEST['act'] == 'list') {
	admin_priv('goods_lib_list');
	lib_get_del_goodsimg_null();
	lib_get_del_goods_gallery();
	$cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
	$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => '01_goods_list'));
	$smarty->assign('ur_here', $_LANG['20_goods_lib']);
	$smarty->assign('lang', $_LANG);
	$smarty->assign('list_type', $_REQUEST['act'] == 'list' ? 'goods' : 'trash');
	$goods_list = lib_goods_list();
	$smarty->assign('goods_list', $goods_list['goods']);
	$smarty->assign('filter', $goods_list['filter']);
	$smarty->assign('record_count', $goods_list['record_count']);
	$smarty->assign('page_count', $goods_list['page_count']);
	$smarty->assign('full_page', 1);
	$sort_flag = sort_flag($goods_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$smarty->assign('nowTime', gmtime());
	set_default_filter();
	$smarty->assign('cfg', $_CFG);
	$smarty->display('goods_lib_list.dwt');
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		admin_priv('goods_lib_list');
		lib_get_del_goodsimg_null();
		lib_get_del_goods_gallery();
		$is_add = $_REQUEST['act'] == 'add';
		include_once ROOT_PATH . 'includes/fckeditor/fckeditor.php';
		$properties = empty($_REQUEST['properties']) ? 0 : intval($_REQUEST['properties']);
		$smarty->assign('properties', $properties);
		if (ini_get('safe_mode') == 1 && (!file_exists('../' . IMAGE_DIR . '/' . date('Ym')) || !is_dir('../' . IMAGE_DIR . '/' . date('Ym')))) {
			if (@!mkdir('../' . IMAGE_DIR . '/' . date('Ym'), 511)) {
				$warning = sprintf($_LANG['safe_mode_warning'], '../' . IMAGE_DIR . '/' . date('Ym'));
				$smarty->assign('warning', $warning);
			}
		}
		else {
			if (file_exists('../' . IMAGE_DIR . '/' . date('Ym')) && file_mode_info('../' . IMAGE_DIR . '/' . date('Ym')) < 2) {
				$warning = sprintf($_LANG['not_writable_warning'], '../' . IMAGE_DIR . '/' . date('Ym'));
				$smarty->assign('warning', $warning);
			}
		}

		$adminru = get_admin_ru_id();
		$goods_id = isset($_REQUEST['goods_id']) && !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;

		if ($is_add) {
			$goods = array(
				'goods_id'      => 0,
				'user_id'       => 0,
				'goods_desc'    => '',
				'goods_shipai'  => '',
				'cat_id'        => '0',
				'brand_id'      => 0,
				'is_on_sale'    => '1',
				'is_alone_sale' => '1',
				'is_shipping'   => '0',
				'other_cat'     => array(),
				'goods_type'    => 0,
				'shop_price'    => 0,
				'market_price'  => 0,
				'goods_weight'  => 0,
				'goods_extend'  => array('is_reality' => 0, 'is_return' => 0, 'is_fast' => 0)
				);
			$img_list = array();
		}
		else {
			$goods = $db->getRow(' SELECT * FROM ' . $ecs->table('goods_lib') . (' WHERE goods_id = \'' . $goods_id . '\' LIMIT 1 '));

			if (empty($goods)) {
				$link[] = array('href' => 'goods_lib.php?act=list', 'text' => $_LANG['back_goods_list']);
				sys_msg($_LANG['lab_not_goods'], 0, $link);
			}

			$http = $GLOBALS['ecs']->http();
			$goods['goods_thumb'] = get_image_path($goods['goods_id'], $goods['goods_thumb'], true);

			if (strpos($goods['goods_thumb'], $http) === false) {
				$goods['goods_thumb'] = $GLOBALS['ecs']->url() . $goods['goods_thumb'];
			}

			if (empty($goods) === true) {
				$goods = array(
					'goods_id'     => 0,
					'user_id'      => 0,
					'goods_desc'   => '',
					'cat_id'       => 0,
					'other_cat'    => array(),
					'goods_type'   => 0,
					'shop_price'   => 0,
					'market_price' => 0,
					'goods_weight' => 0,
					'goods_extend' => array('is_reality' => 0, 'is_return' => 0, 'is_fast' => 0)
					);
			}

			$goods['goods_extend'] = get_goods_extend($goods['goods_id']);
			$specifications = get_goods_type_specifications();
			$goods['specifications_id'] = $specifications[$goods['goods_type']];
			$_attribute = get_goods_specifications_list($goods['goods_id']);
			$goods['_attribute'] = empty($_attribute) ? '' : 1;

			if (0 < $goods['goods_weight']) {
				$goods['goods_weight_by_unit'] = 1 <= $goods['goods_weight'] ? $goods['goods_weight'] : $goods['goods_weight'] / 0.001;
			}

			if (!empty($goods['goods_brief'])) {
				$goods['goods_brief'] = $goods['goods_brief'];
			}

			if (!empty($goods['keywords'])) {
				$goods['keywords'] = $goods['keywords'];
			}

			if (isset($GLOBALS['shop_id']) && 10 < $GLOBALS['shop_id'] && !empty($goods['original_img'])) {
				$goods['goods_img'] = get_image_path($goods_id, $goods['goods_img']);
				$goods['goods_thumb'] = get_image_path($goods_id, $goods['goods_thumb'], true);
			}

			$sql = 'SELECT * FROM ' . $ecs->table('goods_lib_gallery') . (' WHERE goods_id = \'' . $goods_id . '\' ORDER BY img_desc');
			$img_list = $db->getAll($sql);
			$http = $GLOBALS['ecs']->http();
			if (isset($GLOBALS['shop_id']) && 0 < $GLOBALS['shop_id']) {
				foreach ($img_list as $key => $gallery_img) {
					$img_list[$key] = $gallery_img;

					if (!empty($gallery_img['external_url'])) {
						$img_list[$key]['img_url'] = $gallery_img['external_url'];
						$img_list[$key]['thumb_url'] = $gallery_img['external_url'];
					}
					else {
						$gallery_img['img_original'] = get_image_path($gallery_img['goods_id'], $gallery_img['img_original'], true);

						if (strpos($gallery_img['img_original'], $http) === false) {
							$gallery_img['img_original'] = $GLOBALS['ecs']->url() . $gallery_img['img_original'];
						}

						$img_list[$key]['img_url'] = $gallery_img['img_original'];
						$gallery_img['thumb_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['thumb_url'], true);

						if (strpos($gallery_img['thumb_url'], $http) === false) {
							$gallery_img['thumb_url'] = $GLOBALS['ecs']->url() . $gallery_img['thumb_url'];
						}

						$img_list[$key]['thumb_url'] = $gallery_img['thumb_url'];
					}
				}
			}
			else {
				foreach ($img_list as $key => $gallery_img) {
					$img_list[$key] = $gallery_img;

					if (!empty($gallery_img['external_url'])) {
						$img_list[$key]['img_url'] = $gallery_img['external_url'];
						$img_list[$key]['thumb_url'] = $gallery_img['external_url'];
					}
					else {
						$gallery_img['thumb_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['thumb_url'], true);

						if (strpos($gallery_img['thumb_url'], $http) === false) {
							$gallery_img['thumb_url'] = $GLOBALS['ecs']->url() . $gallery_img['thumb_url'];
						}

						$img_list[$key]['thumb_url'] = $gallery_img['thumb_url'];
					}
				}
			}

			$img_desc = array();

			foreach ($img_list as $k => $v) {
				$img_desc[] = $v['img_desc'];
			}

			@$img_default = min($img_desc);
			$min_img_id = $db->getOne(' SELECT img_id   FROM ' . $ecs->table('goods_lib_gallery') . ' WHERE goods_id = \'' . $goods_id . ('\' AND img_desc = \'' . $img_default . '\' ORDER BY img_desc   LIMIT 1'));
			$smarty->assign('min_img_id', $min_img_id);
		}

		$smarty->assign('ru_id', $adminru['ru_id']);
		$goods_name_style = explode('+', empty($goods['goods_name_style']) ? '+' : $goods['goods_name_style']);

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();

			if ($goods['goods_desc']) {
				$desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $goods['goods_desc']);
				$goods['goods_desc'] = $desc_preg['goods_desc'];
			}
		}

		create_html_editor('goods_desc', $goods['goods_desc']);
		create_html_editor2('goods_shipai', 'goods_shipai', $goods['goods_shipai']);
		$smarty->assign('integral_scale', $_CFG['integral_scale']);
		$sql = 'SELECT brand_name FROM ' . $GLOBALS['ecs']->table('brand') . ' WHERE brand_id = \'' . $goods['brand_id'] . '\' ORDER BY sort_order ';
		$brand_name = addslashes($GLOBALS['db']->getOne($sql));
		$smarty->assign('code', $code);
		$smarty->assign('ur_here', $is_add ? (empty($code) ? $_LANG['02_goods_add'] : $_LANG['51_virtual_card_add']) : ($_REQUEST['act'] == 'edit' ? $_LANG['edit_goods'] : $_LANG['copy_goods']));
		$smarty->assign('action_link', list_link($is_add, $code));
		$smarty->assign('goods', $goods);
		$smarty->assign('goods_name_color', $goods_name_style[0]);
		$smarty->assign('goods_name_style', $goods_name_style[1]);
		$smarty->assign('brand_list', search_brand_list($goods_id));
		$smarty->assign('brand_name', $brand_name);
		$smarty->assign('unit_list', get_unit_list());
		$smarty->assign('weight_unit', $is_add ? '1' : (1 <= $goods['goods_weight'] ? '1' : '0.001'));
		$smarty->assign('cfg', $_CFG);
		$smarty->assign('form_act', $is_add ? 'insert' : ($_REQUEST['act'] == 'edit' ? 'update' : 'insert'));
		$smarty->assign('is_add', true);
		$smarty->assign('img_list', $img_list);
		$smarty->assign('goods_type_list', goods_type_list($goods['goods_type'], $goods['goods_id'], 'array'));
		$smarty->assign('goods_type_name', $GLOBALS['db']->getOne(' SELECT cat_name FROM ' . $GLOBALS['ecs']->table('goods_type') . (' WHERE cat_id = \'' . $goods['goods_type'] . '\' ')));
		$smarty->assign('gd', gd_version());
		$smarty->assign('thumb_width', $_CFG['thumb_width']);
		$smarty->assign('thumb_height', $_CFG['thumb_height']);
		$level_limit = 3;
		$category_level = array();

		if ($is_add) {
			for ($i = 1; $i <= $level_limit; $i++) {
				$category_list = array();

				if ($i == 1) {
					$category_list = get_category_list();
				}

				$smarty->assign('cat_level', $i);
				$smarty->assign('category_list', $category_list);
				$category_level[$i] = $smarty->fetch('templates/library/get_select_category.lbi');
			}
		}
		else {
			$parent_cat_list = get_select_category($goods['cat_id'], 1, true);

			for ($i = 1; $i <= $level_limit; $i++) {
				$category_list = array();

				if (isset($parent_cat_list[$i])) {
					$category_list = get_category_list($parent_cat_list[$i], 0, '', 0, $i);
				}
				else if ($i == 1) {
					if ($goods['user_id']) {
						$category_list = get_category_list(0, 0, '', 0, $i);
					}
					else {
						$category_list = get_category_list();
					}
				}

				$smarty->assign('cat_level', $i);
				$smarty->assign('category_list', $category_list);
				$category_level[$i] = $smarty->fetch('templates/library/get_select_category.lbi');
			}
		}

		$cat_list = get_goods_lib_cat(0, $goods['cat_id'], false);
		$smarty->assign('goods_lib_cat', $cat_list);
		$smarty->assign('category_level', $category_level);
		set_default_filter($goods_id, 0, 0);
		assign_query_info();
		$smarty->display('goods_lib_info.dwt');
	}
	else if ($_REQUEST['act'] == 'get_select_category_pro') {
		$goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
		$cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
		$cat_level = empty($_REQUEST['cat_level']) ? 0 : intval($_REQUEST['cat_level']);
		$result = array('error' => 0, 'message' => '', 'content' => '');
		$goods = get_admin_goods_info($goods_id, array('user_id'));
		$seller_shop_cat = seller_shop_cat($goods['user_id']);
		$smarty->assign('cat_id', $cat_id);
		$smarty->assign('cat_level', $cat_level + 1);
		$smarty->assign('category_list', get_category_list($cat_id, 2, $seller_shop_cat, $goods['user_id'], $cat_level + 1));
		$result['content'] = $smarty->fetch('templates/library/get_select_category.lbi');
		exit(json_encode($result));
	}
	else if ($_REQUEST['act'] == 'set_common_category_pro') {
		$cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
		$result = array('error' => 0, 'message' => '', 'content' => '');
		$level_limit = 3;
		$category_level = array();
		$parent_cat_list = get_select_category($cat_id, 1, true);

		for ($i = 1; $i <= $level_limit; $i++) {
			$category_list = array();

			if (isset($parent_cat_list[$i])) {
				$category_list = get_category_list($parent_cat_list[$i]);
			}
			else if ($i == 1) {
				$category_list = get_category_list();
			}

			$smarty->assign('cat_level', $i);
			$smarty->assign('category_list', $category_list);
			$category_level[$i] = $smarty->fetch('templates/library/get_select_category.lbi');
		}

		$smarty->assign('cat_id', $cat_id);
		$result['content'] = $category_level;
		exit(json_encode($result));
	}
	else {
		if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
			$code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
			$proc_thumb = isset($GLOBALS['shop_id']) && 0 < $GLOBALS['shop_id'] ? false : true;
			admin_priv('goods_lib_list');
			$is_insert = $_REQUEST['act'] == 'insert';
			$original_img = empty($_REQUEST['original_img']) ? '' : trim($_REQUEST['original_img']);
			$goods_img = empty($_REQUEST['goods_img']) ? '' : trim($_REQUEST['goods_img']);
			$goods_thumb = empty($_REQUEST['goods_thumb']) ? '' : trim($_REQUEST['goods_thumb']);
			$is_img_url = empty($_REQUEST['is_img_url']) ? 0 : intval($_REQUEST['is_img_url']);
			$_POST['goods_img_url'] = isset($_POST['goods_img_url']) && !empty($_POST['goods_img_url']) ? trim($_POST['goods_img_url']) : '';
			if (!empty($_POST['goods_img_url']) && $_POST['goods_img_url'] != 'http://' && (strpos($_POST['goods_img_url'], 'http://') !== false || strpos($_POST['goods_img_url'], 'https://') !== false) && $is_img_url == 1) {
				$admin_temp_dir = 'seller';
				$admin_temp_dir = ROOT_PATH . 'temp' . '/' . $admin_temp_dir . '/' . 'admin_' . $admin_id;

				if (!file_exists($admin_temp_dir)) {
					make_dir($admin_temp_dir);
				}

				if (get_http_basename($_POST['goods_img_url'], $admin_temp_dir)) {
					$original_img = $admin_temp_dir . '/' . basename($_POST['goods_img_url']);
				}

				if ($original_img === false) {
					sys_msg($image->error_msg(), 1, array(), false);
				}

				$goods_img = $original_img;

				if ($_CFG['auto_generate_gallery']) {
					$img = $original_img;
					$pos = strpos(basename($img), '.');
					$newname = dirname($img) . '/' . $image->random_filename() . substr(basename($img), $pos);

					if (!copy($img, $newname)) {
						sys_msg('fail to copy file: ' . realpath('../' . $img), 1, array(), false);
					}

					$img = $newname;
					$gallery_img = $img;
					$gallery_thumb = $img;
				}

				if ($proc_thumb && 0 < $image->gd_version() || $is_url_goods_img) {
					if (empty($is_url_goods_img)) {
						$img_wh = $image->get_width_to_height($goods_img, $GLOBALS['_CFG']['image_width'], $GLOBALS['_CFG']['image_height']);
						$GLOBALS['_CFG']['image_width'] = isset($img_wh['image_width']) ? $img_wh['image_width'] : $GLOBALS['_CFG']['image_width'];
						$GLOBALS['_CFG']['image_height'] = isset($img_wh['image_height']) ? $img_wh['image_height'] : $GLOBALS['_CFG']['image_height'];
						$goods_img = $image->make_thumb(array('img' => $goods_img, 'type' => 1), $GLOBALS['_CFG']['image_width'], $GLOBALS['_CFG']['image_height']);

						if ($goods_img === false) {
							sys_msg($image->error_msg(), 1, array(), false);
						}

						$gallery_img = $image->make_thumb(array('img' => $gallery_img, 'type' => 1), $GLOBALS['_CFG']['image_width'], $GLOBALS['_CFG']['image_height']);

						if ($gallery_img === false) {
							sys_msg($image->error_msg(), 1, array(), false);
						}

						if (0 < intval($_CFG['watermark_place']) && !empty($GLOBALS['_CFG']['watermark'])) {
							if ($image->add_watermark($goods_img, '', $GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']) === false) {
								sys_msg($image->error_msg(), 1, array(), false);
							}

							if ($_CFG['auto_generate_gallery']) {
								if ($image->add_watermark($img, '', $GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']) === false) {
									sys_msg($image->error_msg(), 1, array(), false);
								}
							}
						}
					}

					if ($_CFG['auto_generate_gallery']) {
						if ($_CFG['thumb_width'] != 0 || $_CFG['thumb_height'] != 0) {
							$gallery_thumb = $image->make_thumb(array('img' => $img, 'type' => 1), $GLOBALS['_CFG']['thumb_width'], $GLOBALS['_CFG']['thumb_height']);

							if ($gallery_thumb === false) {
								sys_msg($image->error_msg(), 1, array(), false);
							}
						}
					}
				}

				if ($proc_thumb && !empty($original_img)) {
					if ($_CFG['thumb_width'] != 0 || $_CFG['thumb_height'] != 0) {
						$goods_thumb = $image->make_thumb(array('img' => $original_img, 'type' => 1), $GLOBALS['_CFG']['thumb_width'], $GLOBALS['_CFG']['thumb_height']);

						if ($goods_thumb === false) {
							sys_msg($image->error_msg(), 1, array(), false);
						}
					}
					else {
						$goods_thumb = $original_img;
					}
				}
			}

			$goods_img_id = !empty($_REQUEST['img_id']) ? $_REQUEST['img_id'] : '';
			$shop_price = !empty($_POST['shop_price']) ? trim($_POST['shop_price']) : 0;
			$shop_price = floatval($shop_price);
			$market_price = !empty($_POST['market_price']) ? trim($_POST['market_price']) : 0;
			$market_price = floatval($market_price);
			$cost_price = !empty($_POST['cost_price']) ? trim($_POST['cost_price']) : 0;
			$cost_price = floatval($cost_price);
			$review_status = isset($_POST['review_status']) ? intval($_POST['review_status']) : 5;
			$review_content = isset($_POST['review_content']) && !empty($_POST['review_content']) ? addslashes(trim($_POST['review_content'])) : '';
			$goods_weight = !empty($_POST['goods_weight']) ? $_POST['goods_weight'] * $_POST['weight_unit'] : 0;
			$bar_code = isset($_POST['bar_code']) && !empty($_POST['bar_code']) ? trim($_POST['bar_code']) : '';
			$goods_name_style = $_POST['goods_name_color'] . '+' . $_POST['goods_name_style'];
			$other_catids = isset($_POST['other_catids']) ? trim($_POST['other_catids']) : '';
			$lib_cat_id = isset($_POST['lib_cat_id']) ? intval($_POST['lib_cat_id']) : 0;
			$is_on_sale = isset($_POST['is_on_sale']) ? intval($_POST['is_on_sale']) : 0;
			$catgory_id = empty($_POST['cat_id']) ? '' : intval($_POST['cat_id']);
			if (empty($catgory_id) && !empty($_POST['common_category'])) {
				$catgory_id = intval($_POST['common_category']);
			}

			$brand_id = empty($_POST['brand_id']) ? '' : intval($_POST['brand_id']);
			$adminru = get_admin_ru_id();
			$model_price = isset($_POST['model_price']) ? intval($_POST['model_price']) : 0;
			$model_inventory = isset($_POST['model_inventory']) ? intval($_POST['model_inventory']) : 0;
			$model_attr = isset($_POST['model_attr']) ? intval($_POST['model_attr']) : 0;
			$goods_name = trim($_POST['goods_name']);
			$pin = new pin();
			$pinyin = $pin->Pinyin($goods_name, 'UTF8');

			if ($is_insert) {
				$sql = 'INSERT INTO ' . $ecs->table('goods_lib') . ' (goods_name, goods_name_style, bar_code, ' . ' cat_id, lib_cat_id, brand_id, shop_price, market_price, cost_price, goods_img, goods_thumb, original_img, keywords, goods_brief, ' . ' goods_weight, goods_desc, desc_mobile, add_time, last_update, goods_type, pinyin_keyword, is_on_sale ' . ')' . ('VALUES (\'' . $goods_name . '\', \'' . $goods_name_style . '\', \'' . $bar_code . '\', \'' . $catgory_id . '\', \'' . $lib_cat_id . '\', ') . (' \'' . $brand_id . '\', \'' . $shop_price . '\', \'' . $market_price . '\', \'' . $cost_price . '\', \'' . $goods_img . '\', \'' . $goods_thumb . '\', \'' . $original_img . '\', \'' . $_POST['keywords'] . '\', \'' . $_POST['goods_brief'] . '\', ') . (' \'' . $goods_weight . '\', \'' . $_POST['goods_desc'] . '\', \'' . $_POST['desc_mobile'] . '\', \'') . gmtime() . '\', \'' . gmtime() . ('\', \'' . $goods_type . '\', \'' . $pinyin . '\', \'' . $is_on_sale . '\' ') . ')';
				$not_number = !empty($goods_number) ? 1 : 0;
				$number = '+ ' . $goods_number;
				$use_storage = 7;
			}
			else {
				$_REQUEST['goods_id'] = isset($_REQUEST['goods_id']) && !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
				get_goods_file_content($_REQUEST['goods_id'], $GLOBALS['_CFG']['goods_file'], $adminru['ru_id'], $review_goods, $model_attr);
				$sql = 'UPDATE ' . $ecs->table('goods_lib') . ' SET ' . ('goods_name = \'' . $goods_name . '\', ') . ('goods_name_style = \'' . $goods_name_style . '\', ') . ('bar_code = \'' . $bar_code . '\', ') . ('cat_id = \'' . $catgory_id . '\', ') . ('lib_cat_id = \'' . $lib_cat_id . '\', ') . ('brand_id = \'' . $brand_id . '\', ') . ('shop_price = \'' . $shop_price . '\', ') . ('market_price = \'' . $market_price . '\', ') . ('cost_price = \'' . $cost_price . '\', ') . ('pinyin_keyword = \'' . $pinyin . '\', ') . ('is_on_sale = \'' . $is_on_sale . '\', ');

				if ($goods_img) {
					$sql .= 'goods_img = \'' . $goods_img . '\', original_img = \'' . $original_img . '\', ';
				}

				if ($goods_thumb) {
					$sql .= 'goods_thumb = \'' . $goods_thumb . '\', ';
				}

				if ($code != '') {
					$sql .= 'is_real=0, extension_code=\'' . $code . '\', ';
				}

				$sql .= 'keywords = \'' . $_POST['keywords'] . '\', ' . ('goods_brief = \'' . $_POST['goods_brief'] . '\', ') . ('goods_weight = \'' . $goods_weight . '\',') . ('goods_desc = \'' . $_POST['goods_desc'] . '\', ') . ('desc_mobile = \'' . $_POST['desc_mobile'] . '\', ') . 'last_update = \'' . gmtime() . '\' ' . 'WHERE goods_id = \'' . $_REQUEST['goods_id'] . '\' LIMIT 1 ';
			}

			$res = $db->query($sql);
			$goods_id = $is_insert ? $db->insert_id() : $_REQUEST['goods_id'];
			$extend_arr = array();
			$extend_arr['width'] = isset($_POST['width']) ? trim($_POST['width']) : '';
			$extend_arr['height'] = isset($_POST['height']) ? trim($_POST['height']) : '';
			$extend_arr['depth'] = isset($_POST['depth']) ? trim($_POST['depth']) : '';
			$extend_arr['origincountry'] = isset($_POST['origincountry']) ? trim($_POST['origincountry']) : '';
			$extend_arr['originplace'] = isset($_POST['originplace']) ? trim($_POST['originplace']) : '';
			$extend_arr['assemblycountry'] = isset($_POST['assemblycountry']) ? trim($_POST['assemblycountry']) : '';
			$extend_arr['barcodetype'] = isset($_POST['barcodetype']) ? trim($_POST['barcodetype']) : '';
			$extend_arr['catena'] = isset($_POST['catena']) ? trim($_POST['catena']) : '';
			$extend_arr['isbasicunit'] = isset($_POST['isbasicunit']) ? intval($_POST['isbasicunit']) : 0;
			$extend_arr['packagetype'] = isset($_POST['packagetype']) ? trim($_POST['packagetype']) : '';
			$extend_arr['grossweight'] = isset($_POST['grossweight']) ? trim($_POST['grossweight']) : '';
			$extend_arr['netweight'] = isset($_POST['netweight']) ? trim($_POST['netweight']) : '';
			$extend_arr['netcontent'] = isset($_POST['netcontent']) ? trim($_POST['netcontent']) : '';
			$extend_arr['licensenum'] = isset($_POST['licensenum']) ? trim($_POST['licensenum']) : '';
			$extend_arr['healthpermitnum'] = isset($_POST['healthpermitnum']) ? trim($_POST['healthpermitnum']) : '';
			$db->autoExecute($ecs->table('goods_extend'), $extend_arr, 'UPDATE', 'goods_id = \'' . $goods_id . '\'');

			if ($is_insert) {
				admin_log($_POST['goods_name'], 'add', 'goods_lib');
			}
			else {
				admin_log($_POST['goods_name'], 'edit', 'goods_lib');
			}

			if ($is_insert) {
				$thumb_img_id = $_SESSION['thumb_img_id' . $_SESSION['admin_id']];

				if ($thumb_img_id) {
					$sql = ' UPDATE ' . $ecs->table('goods_lib_gallery') . ' SET goods_id = \'' . $goods_id . '\' WHERE goods_id = 0 AND img_id ' . db_create_in($thumb_img_id);
					$db->query($sql);
				}

				unset($_SESSION['thumb_img_id' . $_SESSION['admin_id']]);
			}

			if (!empty($_POST['goods_img_url']) && $is_img_url == 1) {
				$original_img = reformat_image_name('goods', $goods_id, $original_img, 'source');
				$goods_img = reformat_image_name('goods', $goods_id, $goods_img, 'goods');
				$goods_thumb = reformat_image_name('goods_thumb', $goods_id, $goods_thumb, 'thumb');
				$sql = ' UPDATE ' . $ecs->table('goods_lib') . (' SET goods_thumb = \'' . $goods_thumb . '\', goods_img = \'' . $goods_img . '\', original_img = \'' . $original_img . '\' WHERE goods_id = \'' . $goods_id . '\' ');
				$db->query($sql);

				if (isset($img)) {
					if (empty($is_url_goods_img)) {
						$img = reformat_image_name('gallery', $goods_id, $img, 'source');
						$gallery_img = reformat_image_name('gallery', $goods_id, $gallery_img, 'goods');
					}
					else {
						$img = $original_img;
						$gallery_img = $goods_img;
					}

					$gallery_thumb = reformat_image_name('gallery_thumb', $goods_id, $gallery_thumb, 'thumb');
					$sql = 'INSERT INTO ' . $ecs->table('goods_lib_gallery') . ' (goods_id, img_url, thumb_url, img_original) ' . ('VALUES (\'' . $goods_id . '\', \'' . $gallery_img . '\', \'' . $gallery_thumb . '\', \'' . $img . '\')');
					$db->query($sql);
				}

				get_oss_add_file(array($goods_img, $goods_thumb, $original_img, $gallery_img, $gallery_thumb, $img));
			}
			else {
				get_oss_add_file(array($goods_img, $goods_thumb, $original_img));
			}

			clear_cache_files();
			$link = array();

			if ($is_insert) {
				$link[2] = add_link($code);
			}

			$link[3] = list_link($is_insert, $code);

			for ($i = 0; $i < count($link); $i++) {
				$key_array[] = $i;
			}

			krsort($link);
			$link = array_combine($key_array, $link);
			sys_msg($is_insert ? $_LANG['add_goods_ok'] : $_LANG['edit_goods_ok'], 0, $link);
		}
		else if ($_REQUEST['act'] == 'batch') {
			$code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
			$goods_id = !empty($_POST['checkboxes']) ? join(',', $_POST['checkboxes']) : 0;

			if (isset($_POST['type'])) {
				if ($_POST['type'] == 'on_sale') {
					admin_priv('goods_lib_list');
					lib_update_goods($goods_id, 'is_on_sale', '1');
				}
				else if ($_POST['type'] == 'not_on_sale') {
					admin_priv('goods_lib_list');
					lib_update_goods($goods_id, 'is_on_sale', '0');
				}
				else if ($_POST['type'] == 'drop') {
					admin_priv('goods_lib_list');
					lib_delete_goods($goods_id);
					admin_log('', 'batch_remove', 'goods_lib');
				}
			}

			clear_cache_files();

			if ($_POST['type'] == 'drop') {
				$link[] = array('href' => 'goods_lib.php?act=list', 'text' => $_LANG['20_goods_lib']);
			}
			else {
				$link[] = list_link(true, $code);
			}

			sys_msg($_LANG['batch_handle_ok'], 0, $link);
		}
		else if ($_REQUEST['act'] == 'edit_goods_name') {
			check_authz_json('goods_lib');
			$goods_id = intval($_POST['id']);
			$goods_name = json_str_iconv(trim($_POST['val']));

			if ($exc->edit('goods_name = \'' . $goods_name . '\', last_update=' . gmtime(), $goods_id)) {
				clear_cache_files();
				make_json_result(stripslashes($goods_name));
			}
		}
		else if ($_REQUEST['act'] == 'check_goods_sn') {
			check_authz_json('goods_lib');
			$goods_id = intval($_REQUEST['goods_id']);
			$goods_sn = htmlspecialchars(json_str_iconv(trim($_REQUEST['goods_sn'])));

			if (!$exc->is_only('goods_sn', $goods_sn, $goods_id)) {
				make_json_error($_LANG['goods_sn_exists']);
			}

			if (!empty($goods_sn)) {
				$sql = 'SELECT goods_id FROM ' . $ecs->table('products') . ('WHERE product_sn=\'' . $goods_sn . '\'');

				if ($db->getOne($sql)) {
					make_json_error($_LANG['goods_sn_exists']);
				}
			}

			make_json_result('');
		}
		else if ($_REQUEST['act'] == 'edit_goods_price') {
			check_authz_json('goods_lib');
			$goods_id = intval($_POST['id']);
			$goods_price = floatval($_POST['val']);
			$price_rate = floatval($_CFG['market_price_rate'] * $goods_price);
			if ($goods_price < 0 || $goods_price == 0 && $_POST['val'] != $goods_price) {
				make_json_error($_LANG['shop_price_invalid']);
			}
			else if ($exc->edit('shop_price = \'' . $goods_price . '\', market_price = \'' . $price_rate . '\', last_update=' . gmtime(), $goods_id)) {
				clear_cache_files();
				make_json_result(number_format($goods_price, 2, '.', ''));
			}
		}
		else if ($_REQUEST['act'] == 'toggle_on_sale') {
			check_authz_json('goods_lib');
			$goods_id = intval($_POST['id']);
			$on_sale = intval($_POST['val']);

			if ($exc->edit('is_on_sale = \'' . $on_sale . '\', last_update=' . gmtime(), $goods_id)) {
				clear_cache_files();
				make_json_result($on_sale);
			}
		}
		else if ($_REQUEST['act'] == 'edit_img_desc') {
			check_authz_json('goods_lib');
			$img_id = intval($_POST['id']);
			$img_desc = intval($_POST['val']);

			if ($exc_gallery->edit('img_desc = \'' . $img_desc . '\'', $img_id)) {
				clear_cache_files();
				make_json_result($img_desc);
			}
		}
		else if ($_REQUEST['act'] == 'main_dsc') {
			$data = read_static_cache('seller_goods_str');

			if ($data === false) {
				$shop_url = urlencode($ecs->url());
				$shop_info = get_shop_info_content(0);

				if ($shop_info) {
					$shop_country = $shop_info['country'];
					$shop_province = $shop_info['province'];
					$shop_city = $shop_info['city'];
					$shop_address = $shop_info['shop_address'];
				}
				else {
					$shop_country = $_CFG['shop_country'];
					$shop_province = $_CFG['shop_province'];
					$shop_city = $_CFG['shop_city'];
					$shop_address = $_CFG['shop_address'];
				}

				$qq = !empty($_CFG['qq']) ? $_CFG['qq'] : $shop_info['kf_qq'];
				$ww = !empty($_CFG['ww']) ? $_CFG['ww'] : $shop_info['kf_ww'];
				$service_email = !empty($_CFG['service_email']) ? $_CFG['service_email'] : $shop_info['seller_email'];
				$service_phone = !empty($_CFG['service_phone']) ? $_CFG['service_phone'] : $shop_info['kf_tel'];
				$shop_country = $db->getOne('SELECT region_name FROM ' . $ecs->table('region') . (' WHERE region_id=\'' . $shop_country . '\''));
				$shop_province = $db->getOne('SELECT region_name FROM ' . $ecs->table('region') . (' WHERE region_id=\'' . $shop_province . '\''));
				$shop_city = $db->getOne('SELECT region_name FROM ' . $ecs->table('region') . (' WHERE region_id=\'' . $shop_city . '\''));
				$httpData = array('domain' => $ecs->get_domain(), 'url' => urldecode($shop_url), 'shop_name' => $_CFG['shop_name'], 'shop_title' => $_CFG['shop_title'], 'shop_desc' => $_CFG['shop_desc'], 'shop_keywords' => $_CFG['shop_keywords'], 'country' => $shop_country, 'province' => $shop_province, 'city' => $shop_city, 'address' => $shop_address, 'qq' => $qq, 'ww' => $ww, 'ym' => $service_phone, 'msn' => $_CFG['msn'], 'email' => $service_email, 'phone' => $_CFG['sms_shop_mobile'], 'icp' => $_CFG['icp_number'], 'version' => VERSION, 'release' => RELEASE, 'language' => $_CFG['lang'], 'php_ver' => PHP_VERSION, 'mysql_ver' => $db->version(), 'charset' => EC_CHARSET);
				$Http = new Http();
				$Http->doPost($_CFG['certi'], $httpData);
				write_static_cache('seller_goods_str', $httpData);
			}
		}
		else if ($_REQUEST['act'] == 'edit_sort_order') {
			check_authz_json('goods_lib');
			$goods_id = intval($_POST['id']);
			$sort_order = intval($_POST['val']);

			if ($exc->edit('sort_order = \'' . $sort_order . '\', last_update=' . gmtime(), $goods_id)) {
				clear_cache_files();
				make_json_result($sort_order);
			}
		}
		else if ($_REQUEST['act'] == 'query') {
			$code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
			$goods_list = lib_goods_list();
			$smarty->assign('code', $code);
			$smarty->assign('goods_list', $goods_list['goods']);
			$smarty->assign('filter', $goods_list['filter']);
			$smarty->assign('record_count', $goods_list['record_count']);
			$smarty->assign('page_count', $goods_list['page_count']);
			$smarty->assign('use_storage', empty($_CFG['use_storage']) ? 0 : 1);
			$sort_flag = sort_flag($goods_list['filter']);
			$smarty->assign($sort_flag['tag'], $sort_flag['img']);
			$specifications = get_goods_type_specifications();
			$smarty->assign('specifications', $specifications);
			$store_list = get_common_store_list();
			$smarty->assign('store_list', $store_list);
			$smarty->assign('nowTime', gmtime());
			set_default_filter();
			make_json_result($smarty->fetch('goods_lib_list.dwt'), '', array('filter' => $goods_list['filter'], 'page_count' => $goods_list['page_count']));
		}
		else if ($_REQUEST['act'] == 'remove') {
			$goods_id = intval($_REQUEST['id']);
			$sql = 'SELECT goods_id, goods_name, goods_thumb, goods_img, original_img ' . 'FROM ' . $ecs->table('goods_lib') . (' WHERE goods_id = \'' . $goods_id . '\'');
			$goods = $db->getRow($sql);

			if (empty($goods)) {
				make_json_error($_LANG['goods_not_exist']);
			}

			$arr = array();
			if (!empty($goods['goods_thumb']) && strpos($goods['goods_thumb'], 'data/gallery_album') === false) {
				$arr[] = $goods['goods_thumb'];
				@unlink('../' . $goods['goods_thumb']);
			}

			if (!empty($goods['goods_img']) && strpos($goods['goods_img'], 'data/gallery_album') === false) {
				$arr[] = $goods['goods_img'];
				@unlink('../' . $goods['goods_img']);
			}

			if (!empty($goods['original_img']) && strpos($goods['original_img'], 'data/gallery_album') === false) {
				$arr[] = $goods['original_img'];
				@unlink('../' . $goods['original_img']);
			}

			if (!empty($arr)) {
				get_oss_del_file($arr);
			}

			check_authz_json('goods_lib');

			if ($exc->drop($goods_id)) {
				$sql = 'DELETE FROM ' . $ecs->table('goods_extend') . (' where goods_id=\'' . $goods_id . '\'');
				$db->query($sql);
				$sql = 'SELECT img_url, thumb_url, img_original ' . 'FROM ' . $ecs->table('goods_lib_gallery') . (' WHERE goods_id = \'' . $goods_id . '\'');
				$res = $db->query($sql);

				while ($row = $db->fetchRow($res)) {
					$arr = array();
					if (!empty($row['img_url']) && strpos($row['img_url'], 'data/gallery_album') === false) {
						$arr[] = $row['img_url'];
						@unlink('../' . $row['img_url']);
					}

					if (!empty($row['thumb_url']) && strpos($row['thumb_url'], 'data/gallery_album') === false) {
						$arr[] = $row['thumb_url'];
						@unlink('../' . $row['thumb_url']);
					}

					if (!empty($row['img_original']) && strpos($row['img_original'], 'data/gallery_album') === false) {
						$arr[] = $row['img_original'];
						@unlink('../' . $row['img_original']);
					}

					if (!empty($arr)) {
						get_oss_del_file($arr);
					}
				}

				clear_cache_files();
				$url = 'goods_lib.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
				ecs_header('Location: ' . $url . "\n");
				exit();
			}
		}
		else if ($_REQUEST['act'] == 'get_goods_list') {
			include_once ROOT_PATH . 'includes/cls_json.php';
			$json = new JSON();
			$filters = $json->decode($_GET['JSON']);
			$arr = get_goods_list($filters);
			$opt = array();

			foreach ($arr as $key => $val) {
				$opt[] = array('value' => $val['goods_id'], 'text' => $val['goods_name'], 'data' => $val['shop_price']);
			}

			make_json_result($opt);
		}
		else if ($_REQUEST['act'] == 'addImg') {
			require ROOT_PATH . '/includes/cls_json.php';
			$json = new JSON();
			$result = array('content' => '', 'error' => 0, 'massege' => '');
			$goods_id = !empty($_REQUEST['goods_id_img']) ? $_REQUEST['goods_id_img'] : '';
			$img_desc = !empty($_REQUEST['img_desc']) ? $_REQUEST['img_desc'] : '';
			$img_file = !empty($_REQUEST['img_file']) ? $_REQUEST['img_file'] : '';
			$php_maxsize = ini_get('upload_max_filesize');
			$htm_maxsize = '2M';

			if ($_FILES['img_url']) {
				foreach ($_FILES['img_url']['error'] as $key => $value) {
					if ($value == 0) {
						if (!$image->check_img_type($_FILES['img_url']['type'][$key])) {
							$result['error'] = '1';
							$result['massege'] = sprintf($_LANG['invalid_img_url'], $key + 1);
						}
						else {
							$goods_pre = 1;
						}
					}
					else if ($value == 1) {
						$result['error'] = '1';
						$result['massege'] = sprintf($_LANG['img_url_too_big'], $key + 1, $php_maxsize);
					}
					else if ($_FILES['img_url']['error'] == 2) {
						$result['error'] = '1';
						$result['massege'] = sprintf($_LANG['img_url_too_big'], $key + 1, $htm_maxsize);
					}
				}
			}

			handle_gallery_image_add($goods_id, $_FILES['img_url'], $img_desc, $img_file, '', '', 'ajax');
			clear_cache_files();

			if (0 < $goods_id) {
				$sql = 'SELECT * FROM ' . $ecs->table('goods_lib_gallery') . (' WHERE goods_id = \'' . $goods_id . '\' ORDER BY img_desc ASC');
			}
			else {
				$img_id = $_SESSION['thumb_img_id' . $_SESSION['admin_id']];
				$where = '';

				if ($img_id) {
					$where = 'AND img_id ' . db_create_in($img_id) . '';
				}

				$sql = 'SELECT * FROM ' . $ecs->table('goods_lib_gallery') . (' WHERE goods_id=\'\' ' . $where . ' ORDER BY img_desc ASC');
			}

			$img_list = $db->getAll($sql);
			if (isset($GLOBALS['shop_id']) && 0 < $GLOBALS['shop_id']) {
				foreach ($img_list as $key => $gallery_img) {
					$gallery_img[$key]['img_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['img_original'], false, 'gallery');
					$gallery_img[$key]['thumb_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['img_original'], true, 'gallery');
				}
			}
			else {
				foreach ($img_list as $key => $gallery_img) {
					$gallery_img[$key]['thumb_url'] = '../' . (empty($gallery_img['thumb_url']) ? $gallery_img['img_url'] : $gallery_img['thumb_url']);
				}
			}

			$goods['goods_id'] = $goods_id;
			$smarty->assign('img_list', $img_list);
			$img_desc = array();

			foreach ($img_list as $k => $v) {
				$img_desc[] = $v['img_desc'];
			}

			$img_default = min($img_desc);
			$min_img_id = $db->getOne(' SELECT img_id   FROM ' . $ecs->table('goods_gallery') . (' WHERE goods_id = \'' . $goods_id . '\' AND img_desc = \'' . $img_default . '\' ORDER BY img_desc   LIMIT 1'));
			$smarty->assign('min_img_id', $min_img_id);
			$smarty->assign('goods', $goods);
			$result['error'] = '2';
			$result['content'] = $GLOBALS['smarty']->fetch('gallery_img.lbi');
			exit($json->encode($result));
		}
		else if ($_REQUEST['act'] == 'img_default') {
			require ROOT_PATH . '/includes/cls_json.php';
			$json = new JSON();
			$result = array('content' => '', 'error' => 0, 'massege' => '', 'img_id' => '');
			$img_id = !empty($_REQUEST['img_id']) ? intval($_REQUEST['img_id']) : '0';
			$proc_thumb = isset($GLOBALS['shop_id']) && 0 < $GLOBALS['shop_id'] ? false : true;

			if (0 < $img_id) {
				$goods_gallery = $db->getRow(' SELECT goods_id,img_desc FROM' . $ecs->table('goods_lib_gallery') . (' WHERE img_id= \'' . $img_id . '\''));
				$goods_id = $goods_gallery['goods_id'];
				$sql = 'SELECT MIN(img_desc) FROM' . $ecs->table('goods_lib_gallery') . (' WHERE  goods_id = \'' . $goods_id . '\'');
				$least_img_desc = $db->getOne($sql);
				$db->query('UPDATE' . $ecs->table('goods_lib_gallery') . ' SET img_desc = \'' . $goods_gallery['img_desc'] . ('\' WHERE img_desc = \'' . $least_img_desc . '\' AND goods_id = \'' . $goods_id . '\' '));
				$sql = $db->query('UPDATE' . $ecs->table('goods_lib_gallery') . (' SET img_desc = \'' . $least_img_desc . '\' WHERE img_id = \'' . $img_id . '\''));

				if ($sql = true) {
					if (0 < $goods_id) {
						$where = ' goods_id = \'' . $goods_id . '\' ';
					}
					else {
						$where = ' img_id ' . db_create_in($_SESSION['thumb_img_id' . $_SESSION['admin_id']]) . ' and goods_id = 0 ';
					}

					$sql = 'SELECT * FROM ' . $ecs->table('goods_lib_gallery') . (' WHERE ' . $where . '  ORDER BY img_desc ASC ');
					$img_list = $db->getAll($sql);
					if (isset($GLOBALS['shop_id']) && 0 < $GLOBALS['shop_id']) {
						foreach ($img_list as $key => $gallery_img) {
							$img_list[$key] = $gallery_img;

							if (!empty($gallery_img['external_url'])) {
								$img_list[$key]['img_url'] = $gallery_img['external_url'];
								$img_list[$key]['thumb_url'] = $gallery_img['external_url'];
							}
							else {
								$img_list[$key]['img_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['img_original'], false, 'gallery');
								$img_list[$key]['thumb_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['img_original'], true, 'gallery');
							}
						}
					}
					else {
						foreach ($img_list as $key => $gallery_img) {
							$img_list[$key] = $gallery_img;

							if (!empty($gallery_img['external_url'])) {
								$img_list[$key]['img_url'] = $gallery_img['external_url'];
								$img_list[$key]['thumb_url'] = $gallery_img['external_url'];
							}
							else if ($proc_thumb) {
								$img_list[$key]['thumb_url'] = '../' . (empty($gallery_img['thumb_url']) ? $gallery_img['img_url'] : $gallery_img['thumb_url']);
							}
							else {
								$img_list[$key]['thumb_url'] = empty($gallery_img['thumb_url']) ? $gallery_img['img_url'] : $gallery_img['thumb_url'];
							}
						}
					}

					$img_desc = array();

					if (!empty($img_list)) {
						foreach ($img_list as $k => $v) {
							$img_desc[] = $v['img_desc'];
						}
					}

					if (!empty($img_desc)) {
						$img_default = min($img_desc);
					}

					$min_img_id = $db->getOne(' SELECT img_id   FROM ' . $ecs->table('goods_lib_gallery') . (' WHERE goods_id = \'' . $goods_id . '\' AND img_desc = \'') . $img_default . '\' ORDER BY img_desc LIMIT 1');
					$smarty->assign('min_img_id', $min_img_id);
					$smarty->assign('img_list', $img_list);
					$result['error'] = 1;
					$result['content'] = $GLOBALS['smarty']->fetch('gallery_img.lbi');
				}
				else {
					$result['error'] = 2;
					$result['massege'] = '修改失败';
				}
			}

			exit($json->encode($result));
		}
		else if ($_REQUEST['act'] == 'remove_consumption') {
			require ROOT_PATH . '/includes/cls_json.php';
			$json = new JSON();
			$result = array('error' => 0, 'massege' => '', 'con_id' => '');
			$con_id = !empty($_REQUEST['con_id']) ? intval($_REQUEST['con_id']) : '0';
			$goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : '0';

			if (0 < $con_id) {
				$sql = 'DELETE FROM' . $ecs->table('goods_consumption') . (' WHERE id = \'' . $con_id . '\' AND goods_id = \'' . $goods_id . '\'');

				if ($db->query($sql)) {
					$result['error'] = 2;
					$result['con_id'] = $con_id;
				}
			}
			else {
				$result['error'] = 1;
				$result['massege'] = '请选择删除目标';
			}

			exit($json->encode($result));
		}
		else if ($_REQUEST['act'] == 'gallery_album_dialog') {
			require ROOT_PATH . '/includes/cls_json.php';
			$json = new JSON();
			$result = array('error' => 0, 'message' => '', 'log_type' => '', 'content' => '');
			$sql = 'SELECT album_id,ru_id,album_mame,album_cover,album_desc,sort_order FROM ' . $ecs->table('gallery_album') . ' ' . ' WHERE ru_id = 0 ORDER BY sort_order';
			$gallery_album_list = $db->getAll($sql);
			$smarty->assign('gallery_album_list', $gallery_album_list);
			$log_type = !empty($_GET['log_type']) ? trim($_GET['log_type']) : 'image';
			$result['log_type'] = $log_type;
			$smarty->assign('log_type', $log_type);
			$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('pic_album') . ' WHERE ru_id = 0';
			$res = $GLOBALS['db']->getAll($sql);
			$smarty->assign('pic_album', $res);
			$result['content'] = $smarty->fetch('templates/library/album_dialog.lbi');
			exit($json->encode($result));
		}
		else if ($_REQUEST['act'] == 'gallery_album_pic') {
			require ROOT_PATH . '/includes/cls_json.php';
			$json = new JSON();
			$result = array('error' => 0, 'message' => '', 'content' => '');
			$album_id = !empty($_GET['album_id']) ? intval($_GET['album_id']) : 0;

			if (empty($album_id)) {
				$result['error'] = 1;
				exit($json->encode($result));
			}

			$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('pic_album') . (' WHERE album_id = \'' . $album_id . '\' ');
			$res = $GLOBALS['db']->getAll($sql);
			$smarty->assign('pic_album', $res);
			$result['content'] = $smarty->fetch('templates/library/album_pic.lbi');
			exit($json->encode($result));
		}
		else if ($_REQUEST['act'] == 'scan_code') {
			check_authz_json('goods_lib');
			require ROOT_PATH . '/includes/cls_json.php';
			$json = new JSON();
			$result = array('error' => 0, 'massege' => '', 'content' => '');
			$bar_code = empty($_REQUEST['bar_code']) ? '' : trim($_REQUEST['bar_code']);
			$config = get_scan_code_config($adminru['ru_id']);
			$data = get_jsapi(array('appkey' => $config['js_appkey'], 'barcode' => $bar_code));

			if ($data['status'] != 0) {
				$result['error'] = 1;
				$result['message'] = $data['msg'];
			}
			else {
				$goods_weight = 0;

				if (strpos($data['result']['grossweight'], '千克') !== false) {
					$goods_weight = floatval(str_replace('千克', '', $data['result']['grossweight']));
				}
				else if (strpos($data['result']['grossweight'], '克') !== false) {
					$goods_weight = floatval(str_replace('千克', '', $data['result']['grossweight'])) / 1000;
				}

				$goods_desc = '';

				if (!empty($data['result']['description'])) {
					create_html_editor('goods_desc', trim($data['result']['description']));
					$goods_desc = $smarty->get_template_vars('FCKeditor');
				}

				$goods_info = array();
				$goods_info['goods_name'] = isset($data['result']['name']) ? trim($data['result']['name']) : '';
				$goods_info['goods_name'] .= isset($data['result']['type']) ? trim($data['result']['type']) : '';
				$goods_info['shop_price'] = isset($data['result']['price']) ? floatval($data['result']['price']) : '0.00';
				$goods_info['goods_img_url'] = isset($data['result']['pic']) ? trim($data['result']['pic']) : '';
				$goods_info['goods_desc'] = $goods_desc;
				$goods_info['goods_weight'] = $goods_weight;
				$goods_info['keywords'] = isset($data['result']['keyword']) ? trim($data['result']['keyword']) : '';
				$goods_info['width'] = isset($data['result']['width']) ? trim($data['result']['width']) : '';
				$goods_info['height'] = isset($data['result']['height']) ? trim($data['result']['height']) : '';
				$goods_info['depth'] = isset($data['result']['depth']) ? trim($data['result']['depth']) : '';
				$goods_info['origincountry'] = isset($data['result']['origincountry']) ? trim($data['result']['origincountry']) : '';
				$goods_info['originplace'] = isset($data['result']['originplace']) ? trim($data['result']['originplace']) : '';
				$goods_info['assemblycountry'] = isset($data['result']['assemblycountry']) ? trim($data['result']['assemblycountry']) : '';
				$goods_info['barcodetype'] = isset($data['result']['barcodetype']) ? trim($data['result']['barcodetype']) : '';
				$goods_info['catena'] = isset($data['result']['catena']) ? trim($data['result']['catena']) : '';
				$goods_info['isbasicunit'] = isset($data['result']['isbasicunit']) ? intval($data['result']['isbasicunit']) : 0;
				$goods_info['packagetype'] = isset($data['result']['packagetype']) ? trim($data['result']['packagetype']) : '';
				$goods_info['grossweight'] = isset($data['result']['grossweight']) ? trim($data['result']['grossweight']) : '';
				$goods_info['netweight'] = isset($data['result']['netweight']) ? trim($data['result']['netweight']) : '';
				$goods_info['netcontent'] = isset($data['result']['netcontent']) ? trim($data['result']['netcontent']) : '';
				$goods_info['licensenum'] = isset($data['result']['licensenum']) ? trim($data['result']['licensenum']) : '';
				$goods_info['healthpermitnum'] = isset($data['result']['healthpermitnum']) ? trim($data['result']['healthpermitnum']) : '';
				$result['goods_info'] = $goods_info;
			}

			exit($json->encode($result));
		}
		else if ($_REQUEST['act'] == 'drop_image') {
			check_authz_json('goods_lib');
			$img_id = empty($_REQUEST['img_id']) ? 0 : intval($_REQUEST['img_id']);
			$sql = 'SELECT img_url, thumb_url, img_original ' . ' FROM ' . $GLOBALS['ecs']->table('goods_lib_gallery') . (' WHERE img_id = \'' . $img_id . '\'');
			$row = $GLOBALS['db']->getRow($sql);
			$img_url = ROOT_PATH . $row['img_url'];
			$thumb_url = ROOT_PATH . $row['thumb_url'];
			$img_original = ROOT_PATH . $row['img_original'];
			$arr = array();
			if ($row['img_url'] != '' && is_file($img_url) && strpos($row['img_url'], 'data/gallery_album') === false) {
				$arr[] = $row['img_url'];
				@unlink($img_url);
			}

			if ($row['thumb_url'] != '' && is_file($thumb_url) && strpos($row['img_url'], 'data/gallery_album') === false) {
				$arr[] = $row['thumb_url'];
				@unlink($thumb_url);
			}

			if ($row['img_original'] != '' && is_file($img_original) && strpos($row['img_url'], 'data/gallery_album') === false) {
				$arr[] = $row['img_original'];
				@unlink($img_original);
			}

			if (!empty($arr)) {
				get_oss_del_file($arr);
			}

			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('goods_lib_gallery') . (' WHERE img_id = \'' . $img_id . '\' LIMIT 1');
			$GLOBALS['db']->query($sql);
			clear_cache_files();
			make_json_result($img_id);
		}
		else if ($_REQUEST['act'] == 'import_seller_goods') {
			admin_priv('goods_lib_list');
			$action_link = array('href' => 'goods_lib.php?act=list', 'text' => '商品库列表');
			$smarty->assign('action_link', $action_link);
			$smarty->assign('ur_here', $_LANG['import_seller_goods']);
			$sql = ' SELECT user_id FROM ' . $ecs->table('merchants_shop_information');
			$seller_ids = $db->getCol($sql);

			foreach ($seller_ids as $k => $v) {
				$seller_list[$k]['shop_name'] = get_shop_name($v, 1);
				$seller_list[$k]['user_id'] = $v;
			}

			$smarty->assign('seller_list', $seller_list);
			$smarty->display('goods_lib_import.dwt');
		}
		else if ($_REQUEST['act'] == 'import_action') {
			admin_priv('goods_lib_list');
			$user_id = $_REQUEST['user_id'] ? intval($_REQUEST['user_id']) : 0;
			$record_count = $db->getOne(' SELECT COUNT(*) FROM ' . $ecs->table('goods') . (' WHERE user_id = \'' . $user_id . '\' '));
			$smarty->assign('ur_here', $_LANG['import_seller_goods']);
			$smarty->assign('record_count', $record_count);
			$smarty->assign('user_id', $user_id);
			$smarty->assign('page', 1);
			assign_query_info();
			$smarty->display('import_action_list.dwt');
		}
		else if ($_REQUEST['act'] == 'import_action_list') {
			admin_priv('goods_lib_list');
			$user_id = $_REQUEST['user_id'] ? intval($_REQUEST['user_id']) : 0;
			include_once ROOT_PATH . 'includes/cls_json.php';
			$json = new JSON();
			$page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
			$page_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 1;
			$goods_list = get_import_goods_list($user_id);
			$goods_list = $ecs->page_array($page_size, $page, $goods_list);
			$result['list'] = $goods_list['list'][0];

			if ($result['list']) {
				$sql = ' SELECT goods_id, cat_id, bar_code, goods_name, goods_name_style, brand_id, goods_weight, market_price,' . ' cost_price, shop_price, keywords, goods_brief, goods_desc, desc_mobile, goods_thumb, goods_img, original_img, ' . ' is_real, extension_code, sort_order, goods_type, is_check, largest_amount, pinyin_keyword FROM ' . $ecs->table('goods') . (' WHERE user_id = \'' . $user_id . '\' AND goods_id = \'') . $result['list']['goods_id'] . '\' ';
				$goods_info = $db->getRow($sql);
				$sql = ' SELECT goods_id FROM ' . $ecs->table('goods_lib') . (' WHERE lib_goods_id = \'' . $goods_info['goods_id'] . '\' ');

				if (!$GLOBALS['db']->getOne($sql)) {
					$goods_thumb = copy_img($goods_info['goods_thumb']);
					$goods_img = copy_img($goods_info['goods_img']);
					$original_img = copy_img($goods_info['original_img']);
					$sql = 'INSERT INTO ' . $ecs->table('goods_lib') . '(cat_id, bar_code, goods_name, goods_name_style, brand_id, goods_weight, market_price,' . ' cost_price, shop_price, keywords, goods_brief, goods_desc, desc_mobile, goods_thumb, goods_img, original_img, ' . ' is_real, extension_code, sort_order, goods_type, is_check, largest_amount, pinyin_keyword, lib_goods_id, from_seller ) ' . ' VALUES ' . ('(\'' . $goods_info['cat_id'] . '\', \'' . $goods_info['bar_code'] . '\', \'' . $goods_info['goods_name'] . '\', \'' . $goods_info['goods_name_style'] . '\', \'' . $goods_info['brand_id'] . '\', \'' . $goods_info['goods_weight'] . '\', \'' . $goods_info['market_price'] . '\', ') . (' \'' . $goods_info['cost_price'] . '\', \'' . $goods_info['shop_price'] . '\', \'' . $goods_info['keywords'] . '\', \'' . $goods_info['goods_brief'] . '\', \'') . addslashes($goods_info['goods_desc']) . ('\', \'' . $goods_info['desc_mobile'] . '\', \'' . $goods_thumb . '\', \'' . $goods_img . '\', \'' . $original_img . '\', ') . (' \'' . $goods_info['is_real'] . '\', \'' . $goods_info['extension_code'] . '\', \'' . $goods_info['sort_order'] . '\', \'' . $goods_info['goods_type'] . '\', \'' . $goods_info['is_check'] . '\', \'' . $goods_info['largest_amount'] . '\', \'' . $goods_info['pinyin_keyword'] . '\', \'' . $goods_info['goods_id'] . '\', \'' . $user_id . '\' )');

					try {
						$db->query($sql);
						$new_goods_id = $db->insert_id();
						$res = $db->getAll(' SELECT img_desc, img_url, thumb_url, img_original FROM ' . $ecs->table('goods_gallery') . (' WHERE goods_id = \'' . $goods_info['goods_id'] . '\' '));

						if ($res) {
							foreach ($res as $k => $v) {
								$img_url = copy_img($v['img_url']);
								$thumb_url = copy_img($v['thumb_url']);
								$img_original = copy_img($v['img_original']);
								$sql = ' INSERT INTO ' . $ecs->table('goods_lib_gallery') . ' ( goods_id, img_desc, img_url, thumb_url, img_original ) ' . ' VALUES ' . (' ( \'' . $new_goods_id . '\', \'' . $v['img_desc'] . '\', \'' . $img_url . '\', \'' . $thumb_url . '\', \'' . $img_original . '\' ) ');

								if (!$db->query($sql)) {
									$result['list']['status'] = '图片导入失败';
								}
							}
						}

						$result['list']['status'] = '导入成功';
					}
					catch (Exception $e) {
						$result['list']['status'] = '导入失败';
						continue;
					}
				}
				else {
					$result['list']['status'] = '重复导入';
				}
			}

			$result['page'] = $goods_list['filter']['page'] + 1;
			$result['page_size'] = $goods_list['filter']['page_size'];
			$result['record_count'] = $goods_list['filter']['record_count'];
			$result['page_count'] = $goods_list['filter']['page_count'];
			$result['is_stop'] = 1;

			if ($goods_list['filter']['page_count'] < $page) {
				$result['is_stop'] = 0;
			}
			else {
				$result['filter_page'] = $goods_list['filter']['page'];
			}

			exit($json->encode($result));
		}
		else if ($_REQUEST['act'] == 'get_shopname') {
			check_authz_json('goods_lib');
			$shop_name = empty($_REQUEST['shop_name']) ? '' : trim($_REQUEST['shop_name']);
			$sql = ' SELECT user_id FROM ' . $ecs->table('merchants_shop_information');
			$seller_ids = $db->getCol($sql);

			foreach ($seller_ids as $k => $v) {
				if (is_numeric(stripos(get_shop_name($v, 1), $shop_name)) || empty($shop_name)) {
					$seller_list[$k]['shop_name'] = get_shop_name($v, 1);
					$seller_list[$k]['user_id'] = $v;
				}
			}

			$res = get_search_shopname_list($seller_list);
			clear_cache_files();
			make_json_result($res);
		}
	}
}

?>
