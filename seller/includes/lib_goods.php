<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_intro_list()
{
	return array('store_best' => $GLOBALS['_LANG']['store_best'], 'store_new' => $GLOBALS['_LANG']['store_new'], 'store_hot' => $GLOBALS['_LANG']['store_hot'], 'is_promote' => $GLOBALS['_LANG']['is_promote'], 'all_type' => $GLOBALS['_LANG']['all_type']);
}

function get_unit_list()
{
	return array(1 => $GLOBALS['_LANG']['unit_kg'], '0.001' => $GLOBALS['_LANG']['unit_g']);
}

function get_user_rank_list()
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_rank') . ' ORDER BY min_points';
	return $GLOBALS['db']->getAll($sql);
}

function get_member_price_list($goods_id)
{
	$price_list = array();
	$sql = 'SELECT user_rank, user_price FROM ' . $GLOBALS['ecs']->table('member_price') . (' WHERE goods_id = \'' . $goods_id . '\'');
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$price_list[$row['user_rank']] = $row['user_price'];
	}

	return $price_list;
}

function handle_goods_attr($goods_id, $id_list, $is_spec_list, $value_price_list)
{
	$goods_attr_id = array();

	foreach ($id_list as $key => $id) {
		$is_spec = $is_spec_list[$key];

		if ($is_spec == 'false') {
			$value = $value_price_list[$key];
			$price = '';
		}
		else {
			$value_list = array();
			$price_list = array();

			if ($value_price_list[$key]) {
				$vp_list = explode(chr(13), $value_price_list[$key]);

				foreach ($vp_list as $v_p) {
					$arr = explode(chr(9), $v_p);
					$value_list[] = $arr[0];
					$price_list[] = $arr[1];
				}
			}

			$value = join(chr(13), $value_list);
			$price = join(chr(13), $price_list);
		}

		$sql = 'SELECT goods_attr_id FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE goods_id = \'' . $goods_id . '\' AND attr_id = \'' . $id . '\' AND attr_value = \'' . $value . '\' LIMIT 0, 1');
		$result_id = $GLOBALS['db']->getOne($sql);

		if (!empty($result_id)) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods_attr') . ("\r\n                    SET attr_value = '" . $value . "'\r\n                    WHERE goods_id = '" . $goods_id . "'\r\n                    AND attr_id = '" . $id . "'\r\n                    AND goods_attr_id = '" . $result_id . '\'');
			$goods_attr_id[$id] = $result_id;
		}
		else {
			$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('goods_attr') . ' (goods_id, attr_id, attr_value, attr_price) ' . ('VALUES (\'' . $goods_id . '\', \'' . $id . '\', \'' . $value . '\', \'' . $price . '\')');
		}

		$GLOBALS['db']->query($sql);

		if ($goods_attr_id[$id] == '') {
			$goods_attr_id[$id] = $GLOBALS['db']->insert_id();
		}
	}

	return $goods_attr_id;
}

function handle_member_price($goods_id, $rank_list, $price_list)
{
	foreach ($rank_list as $key => $rank) {
		$price = $price_list[$key];
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('member_price') . (' WHERE goods_id = \'' . $goods_id . '\' AND user_rank = \'' . $rank . '\'');

		if (0 < $GLOBALS['db']->getOne($sql)) {
			if ($price < 0) {
				$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('member_price') . (' WHERE goods_id = \'' . $goods_id . '\' AND user_rank = \'' . $rank . '\' LIMIT 1');
			}
			else {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('member_price') . (' SET user_price = \'' . $price . '\' ') . ('WHERE goods_id = \'' . $goods_id . '\' ') . ('AND user_rank = \'' . $rank . '\' LIMIT 1');
			}
		}
		else if ($price == -1) {
			$sql = '';
		}
		else {
			$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('member_price') . ' (goods_id, user_rank, user_price) ' . ('VALUES (\'' . $goods_id . '\', \'' . $rank . '\', \'' . $price . '\')');
		}

		if ($sql) {
			$GLOBALS['db']->query($sql);
		}
	}
}

function handle_other_cat($goods_id, $cat_list)
{
	$sql = 'SELECT cat_id FROM ' . $GLOBALS['ecs']->table('goods_cat') . (' WHERE goods_id = \'' . $goods_id . '\'');
	$exist_list = $GLOBALS['db']->getCol($sql);
	$delete_list = array_diff($exist_list, $cat_list);

	if ($delete_list) {
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('goods_cat') . (' WHERE goods_id = \'' . $goods_id . '\' ') . 'AND cat_id ' . db_create_in($delete_list);
		$GLOBALS['db']->query($sql);
	}

	$add_list = array_diff($cat_list, $exist_list, array(0));

	foreach ($add_list as $cat_id) {
		$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('goods_cat') . ' (goods_id, cat_id) ' . ('VALUES (\'' . $goods_id . '\', \'' . $cat_id . '\')');
		$GLOBALS['db']->query($sql);
	}
}

function handle_link_goods($goods_id)
{
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('link_goods') . ' SET ' . (' goods_id = \'' . $goods_id . '\' ') . ' WHERE goods_id = \'0\'' . (' AND admin_id = \'' . $_SESSION['seller_id'] . '\'');
	$GLOBALS['db']->query($sql);
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('link_goods') . ' SET ' . (' link_goods_id = \'' . $goods_id . '\' ') . ' WHERE link_goods_id = \'0\'' . (' AND admin_id = \'' . $_SESSION['seller_id'] . '\'');
	$GLOBALS['db']->query($sql);
}

function handle_group_goods($goods_id)
{
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('group_goods') . ' SET ' . (' parent_id = \'' . $goods_id . '\' ') . ' WHERE parent_id = \'0\'' . (' AND admin_id = \'' . $_SESSION['seller_id'] . '\'');
	$GLOBALS['db']->query($sql);
}

function handle_goods_article($goods_id)
{
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods_article') . ' SET ' . (' goods_id = \'' . $goods_id . '\' ') . ' WHERE goods_id = \'0\'' . (' AND admin_id = \'' . $_SESSION['seller_id'] . '\'');
	$GLOBALS['db']->query($sql);
}

function handle_goods_area($goods_id)
{
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('link_area_goods') . ' SET ' . (' goods_id = \'' . $goods_id . '\' ') . ' WHERE goods_id = \'0\'' . ' AND ru_id = (SELECT ru_id FROM ' . $GLOBALS['ecs']->table('admin_user') . ('  WHERE user_id = \'' . $_SESSION['seller_id'] . '\') ');
	$GLOBALS['db']->query($sql);
}

function handle_gallery_image($goods_id, $image_files, $image_descs, $image_urls, $single_id = 0, $files_type = 0)
{
	if ($files_type == 0) {
		$files_type = 'single_id';
	}
	else if ($files_type = 1) {
		$files_type = 'dis_id';
	}

	$admin_id = get_admin_id();
	$admin_temp_dir = 'seller';
	$admin_temp_dir = ROOT_PATH . 'temp' . '/' . $admin_temp_dir . '/' . 'admin_' . $admin_id;

	if (!file_exists($admin_temp_dir)) {
		make_dir($admin_temp_dir);
	}

	$proc_thumb = isset($GLOBALS['shop_id']) && 0 < $GLOBALS['shop_id'] ? false : true;

	foreach ($image_descs as $key => $img_desc) {
		$flag = false;

		if (isset($image_files['error'])) {
			if ($image_files['error'][$key] == 0) {
				$flag = true;
			}
		}
		else {
			if ($image_files['tmp_name'][$key] != 'none' && $image_files['tmp_name'][$key]) {
				$flag = true;
			}
		}

		if ($flag) {
			$upload = array('name' => $image_files['name'][$key], 'type' => $image_files['type'][$key], 'tmp_name' => $image_files['tmp_name'][$key], 'size' => $image_files['size'][$key]);

			if (isset($image_files['error'])) {
				$upload['error'] = $image_files['error'][$key];
			}

			$img_original = $GLOBALS['image']->upload_image($upload, array('type' => 1));

			if ($img_original === false) {
				if ($is_ajax == 'ajax') {
					$result['error'] = '1';
					$result['massege'] = sprintf($_LANG['img_url_too_big'], $key + 1, $htm_maxsize);
					return NULL;
				}
				else {
					sys_msg($GLOBALS['image']->error_msg(), 1, array(), false);
				}
			}

			$img_url = $img_original;

			if ($proc_thumb) {
				$thumb_url = $GLOBALS['image']->make_thumb(array('img' => $img_original, 'type' => 1), $GLOBALS['_CFG']['thumb_width'], $GLOBALS['_CFG']['thumb_height']);
				$thumb_url = is_string($thumb_url) ? $thumb_url : '';
			}
			else {
				$thumb_url = $img_original;
			}

			if ($proc_thumb && 0 < gd_version()) {
				$pos = strpos(basename($img_original), '.');
				$newname = dirname($img_original) . '/' . $GLOBALS['image']->random_filename() . substr(basename($img_original), $pos);
				copy($img_original, $newname);
				$img_url = $newname;
				$GLOBALS['image']->add_watermark($img_url, '', $GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']);
			}

			if ($goods_id == 0) {
				$img_original = reformat_image_name('gallery', $single_id, $img_original, 'source');
				$img_url = reformat_image_name('gallery', $single_id, $img_url, 'goods');
				$thumb_url = reformat_image_name('gallery_thumb', $single_id, $thumb_url, 'thumb');
			}
			else {
				$img_original = reformat_image_name('gallery', $goods_id, $img_original, 'source');
				$img_url = reformat_image_name('gallery', $goods_id, $img_url, 'goods');
				$thumb_url = reformat_image_name('gallery_thumb', $goods_id, $thumb_url, 'thumb');
			}

			$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('goods_gallery') . ' (goods_id, img_url, img_desc, thumb_url, img_original, ' . $files_type . ') ' . ('VALUES (\'' . $goods_id . '\', \'' . $img_url . '\', \'' . $img_desc . '\', \'' . $thumb_url . '\', \'' . $img_original . '\', \'' . $single_id . '\')');
			$GLOBALS['db']->query($sql);
			$thumb_img_id[] = $GLOBALS['db']->insert_id();
			if ($proc_thumb && !$GLOBALS['_CFG']['retain_original_img'] && !empty($img_original)) {
				$GLOBALS['db']->query('UPDATE ' . $GLOBALS['ecs']->table('goods_gallery') . (' SET img_original=\'\' WHERE `goods_id`=\'' . $goods_id . '\''));
				@unlink('../' . $img_original);
			}
		}
		else {
			if (!empty($image_urls[$key]) && $image_urls[$key] != $GLOBALS['_LANG']['img_file'] && $image_urls[$key] != 'http://' && (strpos($image_urls[$key], 'http://') !== false || strpos($image_urls[$key], 'https://') !== false)) {
				if (get_http_basename($image_urls[$key], $admin_temp_dir)) {
					$image_url = trim($image_urls[$key]);
					$down_img = $admin_temp_dir . '/' . basename($image_url);
					$img_wh = $GLOBALS['image']->get_width_to_height($down_img, $GLOBALS['_CFG']['image_width'], $GLOBALS['_CFG']['image_height']);
					$GLOBALS['_CFG']['image_width'] = isset($img_wh['image_width']) ? $img_wh['image_width'] : $GLOBALS['_CFG']['image_width'];
					$GLOBALS['_CFG']['image_height'] = isset($img_wh['image_height']) ? $img_wh['image_height'] : $GLOBALS['_CFG']['image_height'];
					$goods_img = $GLOBALS['image']->make_thumb(array('img' => $down_img, 'type' => 1), $GLOBALS['_CFG']['image_width'], $GLOBALS['_CFG']['image_height']);

					if ($proc_thumb) {
						$thumb_url = $GLOBALS['image']->make_thumb(array('img' => $down_img, 'type' => 1), $GLOBALS['_CFG']['thumb_width'], $GLOBALS['_CFG']['thumb_height']);
						$thumb_url = reformat_image_name('gallery_thumb', $goods_id, $thumb_url, 'thumb');
					}
					else {
						$thumb_url = $GLOBALS['image']->make_thumb(array('img' => $down_img, 'type' => 1));
						$thumb_url = reformat_image_name('gallery_thumb', $goods_id, $thumb_url, 'thumb');
					}

					$img_original = reformat_image_name('gallery', $goods_id, $down_img, 'source');
					$img_url = reformat_image_name('gallery', $goods_id, $goods_img, 'goods');
					$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('goods_gallery') . ' (goods_id, img_url, img_desc, thumb_url, img_original, ' . $files_type . ') ' . ('VALUES (\'' . $goods_id . '\', \'' . $img_url . '\', \'' . $img_desc . '\', \'' . $thumb_url . '\', \'' . $img_original . '\', \'' . $single_id . '\')');
					$GLOBALS['db']->query($sql);
					$thumb_img_id[] = $GLOBALS['db']->insert_id();
					@unlink($down_img);
				}
			}
		}

		get_oss_add_file(array($img_url, $thumb_url, $img_original));
	}
}

function handle_gallery_image_add($goods_id, $image_files, $image_descs, $image_urls, $single_id = 0, $files_type = 0, $is_ajax, $gallery_count = 0)
{
	if ($files_type == 0) {
		$files_type = 'single_id';
	}
	else if ($files_type = 1) {
		$files_type = 'dis_id';
	}

	$max_image_descs = $GLOBALS['db']->getOne(' SELECT MAX(img_desc) FROM' . $GLOBALS['ecs']->table('goods_gallery') . (' WHERE goods_id = \'' . $goods_id . '\''));
	$admin_id = get_admin_id();
	$admin_temp_dir = 'seller';
	$admin_temp_dir = ROOT_PATH . 'temp' . '/' . $admin_temp_dir . '/' . 'admin_' . $admin_id;

	if (!file_exists($admin_temp_dir)) {
		make_dir($admin_temp_dir);
	}

	$proc_thumb = isset($GLOBALS['shop_id']) && 0 < $GLOBALS['shop_id'] ? false : true;

	foreach ($image_descs as $key => $img_desc) {
		$flag = false;

		if (isset($image_files['error'])) {
			if ($image_files['error'][$key] == 0) {
				$flag = true;
			}
		}
		else {
			if ($image_files['tmp_name'][$key] != 'none' && $image_files['tmp_name'][$key]) {
				$flag = true;
			}
		}

		if ($flag) {
			$upload = array('name' => $image_files['name'][$key], 'type' => $image_files['type'][$key], 'tmp_name' => $image_files['tmp_name'][$key], 'size' => $image_files['size'][$key]);

			if (isset($image_files['error'])) {
				$upload['error'] = $image_files['error'][$key];
			}

			$img_original = $GLOBALS['image']->upload_image($upload, array('type' => 1));

			if ($img_original === false) {
				if ($is_ajax == 'ajax') {
					$result['error'] = '1';
					$result['massege'] = sprintf($_LANG['img_url_too_big'], $key + 1, $htm_maxsize);
					return NULL;
				}
				else {
					sys_msg($GLOBALS['image']->error_msg(), 1, array(), false);
				}
			}

			$img_url = $img_original;

			if ($proc_thumb) {
				$thumb_url = $GLOBALS['image']->make_thumb(array('img' => $img_original, 'type' => 1), $GLOBALS['_CFG']['thumb_width'], $GLOBALS['_CFG']['thumb_height']);
				$thumb_url = is_string($thumb_url) ? $thumb_url : '';
			}
			else {
				$thumb_url = $img_original;
			}

			if ($proc_thumb && 0 < gd_version()) {
				$pos = strpos(basename($img_original), '.');
				$newname = dirname($img_original) . '/' . $GLOBALS['image']->random_filename() . substr(basename($img_original), $pos);
				copy($img_original, $newname);
				$img_url = $newname;
				$GLOBALS['image']->add_watermark($img_url, '', $GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']);
			}

			if ($goods_id == 0) {
				$img_original = reformat_image_name('gallery', $single_id, $img_original, 'source');
				$img_url = reformat_image_name('gallery', $single_id, $img_url, 'goods');
				$thumb_url = reformat_image_name('gallery_thumb', $single_id, $thumb_url, 'thumb');
			}
			else {
				$img_original = reformat_image_name('gallery', $goods_id, $img_original, 'source');
				$img_url = reformat_image_name('gallery', $goods_id, $img_url, 'goods');
				$thumb_url = reformat_image_name('gallery_thumb', $goods_id, $thumb_url, 'thumb');
			}

			$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('goods_gallery') . ' (goods_id, img_url, img_desc, thumb_url, img_original, ' . $files_type . ') ' . ('VALUES (\'' . $goods_id . '\', \'' . $img_url . '\', \'' . $gallery_count . '\', \'' . $thumb_url . '\', \'' . $img_original . '\', \'' . $single_id . '\')');
			$GLOBALS['db']->query($sql);
			$thumb_img_id[] = $GLOBALS['db']->insert_id();
			if ($proc_thumb && !$GLOBALS['_CFG']['retain_original_img'] && !empty($img_original)) {
				$GLOBALS['db']->query('UPDATE ' . $GLOBALS['ecs']->table('goods_gallery') . (' SET img_original=\'\' WHERE `goods_id`=\'' . $goods_id . '\''));
				@unlink('../' . $img_original);
			}
		}
		else {
			if (!empty($image_urls[$key]) && $image_urls[$key] != $GLOBALS['_LANG']['img_file'] && $image_urls[$key] != 'http://' && (strpos($image_urls[$key], 'http://') !== false || strpos($image_urls[$key], 'https://') !== false)) {
				if (get_http_basename($image_urls[$key], $admin_temp_dir)) {
					$image_url = trim($image_urls[$key]);
					$down_img = $admin_temp_dir . '/' . basename($image_url);
					$img_wh = $GLOBALS['image']->get_width_to_height($down_img, $GLOBALS['_CFG']['image_width'], $GLOBALS['_CFG']['image_height']);
					$GLOBALS['_CFG']['image_width'] = isset($img_wh['image_width']) ? $img_wh['image_width'] : $GLOBALS['_CFG']['image_width'];
					$GLOBALS['_CFG']['image_height'] = isset($img_wh['image_height']) ? $img_wh['image_height'] : $GLOBALS['_CFG']['image_height'];
					$goods_img = $GLOBALS['image']->make_thumb(array('img' => $down_img, 'type' => 1), $GLOBALS['_CFG']['image_width'], $GLOBALS['_CFG']['image_height']);

					if ($proc_thumb) {
						$thumb_url = $GLOBALS['image']->make_thumb(array('img' => $down_img, 'type' => 1), $GLOBALS['_CFG']['thumb_width'], $GLOBALS['_CFG']['thumb_height']);
						$thumb_url = reformat_image_name('gallery_thumb', $goods_id, $thumb_url, 'thumb');
					}
					else {
						$thumb_url = $GLOBALS['image']->make_thumb(array('img' => $down_img, 'type' => 1));
						$thumb_url = reformat_image_name('gallery_thumb', $goods_id, $thumb_url, 'thumb');
					}

					$img_original = reformat_image_name('gallery', $goods_id, $down_img, 'source');
					$img_url = reformat_image_name('gallery', $goods_id, $goods_img, 'goods');
					$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('goods_gallery') . ' (goods_id, img_url, img_desc, thumb_url, img_original, ' . $files_type . ') ' . ('VALUES (\'' . $goods_id . '\', \'' . $img_url . '\', \'' . $gallery_count . '\', \'' . $thumb_url . '\', \'' . $img_original . '\', \'' . $single_id . '\')');
					$GLOBALS['db']->query($sql);
					$thumb_img_id[] = $GLOBALS['db']->insert_id();
					@unlink($down_img);
				}
			}
		}

		get_oss_add_file(array($img_url, $thumb_url, $img_original));
	}

	if (!empty($_SESSION['thumb_img_id' . $_SESSION['seller_id']])) {
		$_SESSION['thumb_img_id' . $_SESSION['seller_id']] = array_merge($thumb_img_id, $_SESSION['thumb_img_id' . $_SESSION['seller_id']]);
	}
	else {
		$_SESSION['thumb_img_id' . $_SESSION['seller_id']] = $thumb_img_id;
	}
}

function update_goods($goods_id, $field, $value, $content = '', $type = '')
{
	if ($goods_id) {
		clear_cache_files();
		$date = array('model_attr');
		$where = 'goods_id = \'' . $goods_id . '\'';
		$model_attr = get_table_date('goods', $where, $date, 2);
		$table = 'goods';

		if ($type == 'updateNum') {
			if ($model_attr == 1) {
				$table = 'warehouse_goods';
				$field = 'region_number';
			}
			else if ($model_attr == 2) {
				$table = 'warehouse_area_goods';
				$field = 'region_number';
			}
		}

		if ($value == 2 && !empty($content)) {
			$content = 'review_content = \'' . $content . '\', ';
		}

		if ($field == 'is_on_sale') {
			if ($value == 1) {
				$sql = 'SELECT act_id FROM ' . $GLOBALS['ecs']->table('presale_activity') . (' WHERE goods_id = \'' . $goods_id . '\'');

				if ($GLOBALS['db']->getOne($sql, true)) {
					$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('presale_activity') . (' WHERE goods_id = \'' . $goods_id . '\' '));
					$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('cart') . (' WHERE goods_id = \'' . $goods_id . '\' '));
				}
			}
			else {
				$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE goods_id ' . db_create_in($goods_id);
				$GLOBALS['db']->query($sql);
			}
		}

		$sql = 'UPDATE ' . $GLOBALS['ecs']->table($table) . (' SET ' . $field . ' = \'' . $value . '\' , ') . $content . ' last_update = \'' . gmtime() . '\' ' . 'WHERE goods_id ' . db_create_in($goods_id);
		return $GLOBALS['db']->query($sql);
	}
	else {
		return false;
	}
}

function delete_goods($goods_id)
{
	if (empty($goods_id)) {
		return NULL;
	}

	$sql = 'SELECT DISTINCT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id ' . db_create_in($goods_id) . ' AND is_delete = 1';
	$goods_id = $GLOBALS['db']->getCol($sql);

	if (empty($goods_id)) {
		return NULL;
	}

	if ($GLOBALS['_CFG']['open_oss'] == 1) {
		$bucket_info = get_bucket_info();
		$url = $GLOBALS['ecs']->seller_url();
		$self = explode('/', substr(PHP_SELF, 1));
		$count = count($self);

		if (1 < $count) {
			$real_path = $self[$count - 2];

			if ($real_path == SELLER_PATH) {
				$str_len = 0 - (str_len(SELLER_PATH) + 1);
				$url = substr($GLOBALS['ecs']->seller_url(), 0, $str_len);
			}
		}

		$urlip = get_ip_url($url);
		$url = $urlip . 'oss.php?act=del_file';
		$Http = new Http();
	}

	$sql = 'SELECT goods_id, goods_thumb, goods_img, original_img, goods_video ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id ' . db_create_in($goods_id);
	$res = $GLOBALS['db']->query($sql);

	while ($goods = $GLOBALS['db']->fetchRow($res)) {
		if (!empty($goods['goods_thumb'])) {
			dsc_unlink(ROOT_PATH . $goods['goods_thumb']);
		}

		if (!empty($goods['goods_img'])) {
			dsc_unlink(ROOT_PATH . $goods['goods_img']);
		}

		if (!empty($goods['original_img'])) {
			dsc_unlink(ROOT_PATH . $goods['original_img']);
		}

		if (!empty($goods['goods_video'])) {
			dsc_unlink(ROOT_PATH . $goods['goods_video']);
			$video_path = ROOT_PATH . DATA_DIR . '/uploads/goods/' . $goods['goods_id'];

			if (file_exists($video_path)) {
				rmdir($video_path);
			}
		}

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$post_data = array(
				'bucket'    => $bucket_info['bucket'],
				'keyid'     => $bucket_info['keyid'],
				'keysecret' => $bucket_info['keysecret'],
				'is_cname'  => $bucket_info['is_cname'],
				'endpoint'  => $bucket_info['outside_site'],
				'object'    => array($goods['goods_thumb'], $goods['goods_img'], $goods['original_img'], $goods['goods_video'])
				);
			$Http->doPost($url, $post_data);
		}
	}

	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('products') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'SELECT img_url, thumb_url, img_original ' . 'FROM ' . $GLOBALS['ecs']->table('goods_gallery') . ' WHERE goods_id ' . db_create_in($goods_id);
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if (!empty($row['img_url'])) {
			dsc_unlink(ROOT_PATH . $row['img_url']);
		}

		if (!empty($row['thumb_url'])) {
			dsc_unlink(ROOT_PATH . $row['thumb_url']);
		}

		if (!empty($row['img_original'])) {
			dsc_unlink(ROOT_PATH . $row['img_original']);
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

	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('goods_gallery') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('collect_goods') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('goods_article') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('goods_cat') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('member_price') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('group_goods') . ' WHERE parent_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('group_goods') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('link_goods') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('link_goods') . ' WHERE link_goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('tag') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE comment_type = 0 AND id_value ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('presale_activity') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('warehouse_goods') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('warehouse_attr') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('warehouse_area_attr') . ' WHERE goods_id ' . db_create_in($goods_id);
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('virtual_card') . ' WHERE goods_id ' . db_create_in($goods_id);
	if (!$GLOBALS['db']->query($sql, 'SILENT') && $GLOBALS['db']->errno() != 1146) {
		exit($GLOBALS['db']->error());
	}

	clear_cache_files();
}

function generate_goods_sn($goods_id)
{
	$goods_sn = $GLOBALS['_CFG']['sn_prefix'] . str_repeat('0', 6 - strlen($goods_id)) . $goods_id;
	$sql = 'SELECT goods_sn FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_sn LIKE \'' . mysql_like_quote($goods_sn) . ('%\' AND goods_id <> \'' . $goods_id . '\' ') . ' ORDER BY LENGTH(goods_sn) DESC';
	$sn_list = $GLOBALS['db']->getCol($sql);

	if (in_array($goods_sn, $sn_list)) {
		$max = pow(10, strlen($sn_list[0]) - strlen($goods_sn) + 1) - 1;
		$new_sn = $goods_sn . mt_rand(0, $max);

		while (in_array($new_sn, $sn_list)) {
			$new_sn = $goods_sn . mt_rand(0, $max);
		}

		$goods_sn = $new_sn;
	}

	return $goods_sn;
}

function check_goods_sn_exist($goods_sn, $goods_id = 0)
{
	$goods_sn = trim($goods_sn);
	$goods_id = intval($goods_id);

	if (strlen($goods_sn) == 0) {
		return true;
	}

	if (empty($goods_id)) {
		$sql = 'SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . ("\r\n                WHERE goods_sn = '" . $goods_sn . '\'');
	}
	else {
		$sql = 'SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . ("\r\n                WHERE goods_sn = '" . $goods_sn . "'\r\n                AND goods_id <> '" . $goods_id . '\'');
	}

	$res = $GLOBALS['db']->getOne($sql);

	if (empty($res)) {
		return false;
	}
	else {
		return true;
	}
}

function get_attr_list($cat_id, $goods_id = 0)
{
	if (empty($cat_id)) {
		return array();
	}

	$sql = 'SELECT a.attr_id, a.attr_name, a.attr_input_type, a.attr_type, a.attr_values, v.attr_value, v.attr_price, v.attr_sort, v.attr_checked ' . 'FROM ' . $GLOBALS['ecs']->table('attribute') . ' AS a ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods_attr') . ' AS v ' . ('ON v.attr_id = a.attr_id AND v.goods_id = \'' . $goods_id . '\' ') . 'WHERE a.cat_id = ' . intval($cat_id) . ' OR a.cat_id = 0 ' . 'ORDER BY a.sort_order, a.attr_id, v.goods_attr_id';
	$row = $GLOBALS['db']->GetAll($sql);
	return $row;
}

function get_goods_type_specifications()
{
	$sql = "SELECT DISTINCT cat_id\r\n            FROM " . $GLOBALS['ecs']->table('attribute') . "\r\n            WHERE attr_type = 1";
	$row = $GLOBALS['db']->GetAll($sql);
	$return_arr = array();

	if (!empty($row)) {
		foreach ($row as $value) {
			$return_arr[$value['cat_id']] = $value['cat_id'];
		}
	}

	return $return_arr;
}

function build_attr_html($cat_id, $goods_id = 0, $model_attr = 0)
{
	$attr = get_attr_list($cat_id, $goods_id);
	$html = '<table width="100%" id="attrTable">';
	$spec = 0;

	foreach ($attr as $key => $val) {
		$html .= '<tr class=\'item\'><td class=\'label\'>';
		if ($val['attr_type'] == 1 || $val['attr_type'] == 2) {
			$html .= $spec != $val['attr_id'] ? '<a href=\'javascript:;\' onclick=\'addSpec(this)\' class=\'up\'></a>' : '<a href=\'javascript:;\' onclick=\'removeSpec(this)\' class=\'down\'></a>';
			$spec = $val['attr_id'];
		}

		$html .= $val['attr_name'] . '：</td><td><input type=\'hidden\' name=\'attr_id_list[]\' value=\'' . $val['attr_id'] . '\' />';

		if ($val['attr_input_type'] == 0) {
			$html .= '<input name="attr_value_list[]" type="text" class="text_2" value="' . htmlspecialchars($val['attr_value']) . '" size="40" /> ';

			if ($val['attr_value']) {
				$attr_name = ',\'' . $val['attr_value'] . '\'';
			}
			else {
				$attr_name = NULL;
			}
		}
		else if ($val['attr_input_type'] == 2) {
			$html .= '<textarea name="attr_value_list[]" rows="3" cols="40">' . htmlspecialchars($val['attr_value']) . '</textarea>';
		}
		else {
			$html .= '<select name="attr_value_list[]" class="select">';
			$html .= '<option value="">' . $GLOBALS['_LANG']['select_please'] . '</option>';
			$attr_values = explode("\n", $val['attr_values']);

			foreach ($attr_values as $opt) {
				$opt = trim(htmlspecialchars($opt));
				$opt2 = $opt;

				if (strpos($opt2, '_#')) {
					$opt2 = explode('_', $opt2);
					$opt2 = trim(htmlspecialchars($opt2[0]));
				}

				$opt2 = isset($opt2) ? $opt2 : $opt;
				$html .= $val['attr_value'] != $opt ? '<option value="' . $opt . '">' . $opt . '</option>' : '<option value="' . $opt . '" selected="selected">' . $opt2 . '</option>';

				if ($val['attr_value'] == $opt) {
					$attr_name = ',\'' . $opt . '\'';
				}
			}

			$html .= '</select> ';
		}

		$html .= '排序 <input style="text-align:center" type="text" class="text_3 mr10" maxlength="10" size="5" value="' . $val['attr_sort'] . '" name="attr_sort_list[]">';
		$new_model_attr = empty($model_attr) ? $val['model_attr'] : $model_attr;

		if ($new_model_attr == 0) {
			$html .= $val['attr_type'] == 1 && $GLOBALS['_CFG']['goods_attr_price'] == 0 || $val['attr_type'] == 2 ? '<span name="span_attrPrice[]">' . $GLOBALS['_LANG']['spec_price'] . ' <input type="text" class="text_3 mr10" name="attr_price_list[]" value="' . $val['attr_price'] . '" size="5" maxlength="10" />' . '</span>' : ' <input type="hidden" name="attr_price_list[]" value="0" />';

			if (!empty($attr_name)) {
				$html .= $val['attr_type'] == 1 && $GLOBALS['_CFG']['goods_attr_price'] == 0 || $val['attr_type'] == 2 ? ' <a name="a_warehouseAttr[]" href="javascript:add_warehouse_price(' . $goods_id . ',' . $val['attr_id'] . $attr_name . ');" style="display:none;" id="a_warehouseAttr" class="mr10 blue">' . $GLOBALS['_LANG']['warehouse_spec_price'] . '</a>' : '';
				$html .= $val['attr_type'] == 1 && $GLOBALS['_CFG']['goods_attr_price'] == 0 || $val['attr_type'] == 2 ? ' <a name="a_areaAttr[]" href="javascript:add_area_price(' . $goods_id . ',' . $val['attr_id'] . $attr_name . ');" style="display:none;" id="a_areaAttr" class="mr10 blue">' . $GLOBALS['_LANG']['area_spec_price'] . '</a>' : '';
			}
		}
		else if ($new_model_attr == 1) {
			$html .= $val['attr_type'] == 1 && $GLOBALS['_CFG']['goods_attr_price'] == 0 || $val['attr_type'] == 2 ? '<span name="span_attrPrice[]" style="display:none;">' . $GLOBALS['_LANG']['spec_price'] . ' <input type="text" class="text_3 mr10" name="attr_price_list[]" value="' . $val['attr_price'] . '" size="5" maxlength="10" />' . '</span>' : ' <input type="hidden" name="attr_price_list[]" value="0" />';

			if (!empty($attr_name)) {
				$html .= $val['attr_type'] == 1 && $GLOBALS['_CFG']['goods_attr_price'] == 0 || $val['attr_type'] == 2 ? ' <a name="a_warehouseAttr[]" href="javascript:add_warehouse_price(' . $goods_id . ',' . $val['attr_id'] . $attr_name . ');" id="a_warehouseAttr" class="mr10 blue">' . $GLOBALS['_LANG']['warehouse_spec_price'] . '</a>' : '';
				$html .= $val['attr_type'] == 1 && $GLOBALS['_CFG']['goods_attr_price'] == 0 || $val['attr_type'] == 2 ? ' <a name="a_areaAttr[]" href="javascript:add_area_price(' . $goods_id . ',' . $val['attr_id'] . $attr_name . ');" style="display:none;"  id="a_areaAttr" class="mr10 blue">' . $GLOBALS['_LANG']['area_spec_price'] . '</a>' : '';
			}
		}
		else if ($new_model_attr == 2) {
			$html .= $val['attr_type'] == 1 && $GLOBALS['_CFG']['goods_attr_price'] == 0 || $val['attr_type'] == 2 ? '<span name="span_attrPrice[]" style="display:none;">' . $GLOBALS['_LANG']['spec_price'] . ' <input type="text" class="text_3 mr10" name="attr_price_list[]" value="' . $val['attr_price'] . '" size="5" maxlength="10" />' . '</span>' : ' <input type="hidden" name="attr_price_list[]" value="0" />';

			if (!empty($attr_name)) {
				$html .= $val['attr_type'] == 1 && $GLOBALS['_CFG']['goods_attr_price'] == 0 || $val['attr_type'] == 2 ? ' <a name="a_warehouseAttr[]" href="javascript:add_warehouse_price(' . $goods_id . ',' . $val['attr_id'] . $attr_name . ');" style="display:none;" id="a_warehouseAttr" class="mr10 blue">' . $GLOBALS['_LANG']['warehouse_spec_price'] . '</a>' : '';
				$html .= $val['attr_type'] == 1 && $GLOBALS['_CFG']['goods_attr_price'] == 0 || $val['attr_type'] == 2 ? ' <a name="a_areaAttr[]" href="javascript:add_area_price(' . $goods_id . ',' . $val['attr_id'] . $attr_name . ');"  id="a_areaAttr" class="mr10 blue">' . $GLOBALS['_LANG']['area_spec_price'] . '</a>' : '';
			}
		}

		if (!empty($val['attr_value']) && 0 < $val['attr_type'] && $val['attr_type'] == 1) {
			if (!empty($attr_name)) {
				$html .= ' <a href="javascript:add_attr_img(' . $goods_id . ',' . $val['attr_id'] . $attr_name . ');" id="a_add_attr_img" class="blue">' . $GLOBALS['_LANG']['add_attr_img'] . '</a>';
			}
		}

		if ($val['attr_checked'] == 1) {
			$html .= '<font style=\'color:#F00\'>（已选择默认）</font>';
		}

		$html .= '</td></tr>';
	}

	$html .= '</table>';
	return $html;
}

function get_linked_goods($goods_id)
{
	$sql = 'SELECT lg.link_goods_id AS goods_id, g.goods_name, lg.is_double ' . 'FROM ' . $GLOBALS['ecs']->table('link_goods') . ' AS lg, ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ('WHERE lg.goods_id = \'' . $goods_id . '\' ') . 'AND lg.link_goods_id = g.goods_id ';

	if ($goods_id == 0) {
		$sql .= ' AND lg.admin_id = \'' . $_SESSION['seller_id'] . '\'';
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		$linked_type = $val['is_double'] == 0 ? $GLOBALS['_LANG']['single'] : $GLOBALS['_LANG']['double'];
		$row[$key]['goods_name'] = $val['goods_name'] . (' -- [' . $linked_type . ']');
		unset($row[$key]['is_double']);
	}

	return $row;
}

function get_group_goods($goods_id)
{
	$sql = 'SELECT gg.id, gg.goods_id, gg.group_id,g.goods_name ,gg.goods_price,g.shop_price ' . 'FROM ' . $GLOBALS['ecs']->table('group_goods') . ' AS gg, ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ('WHERE gg.parent_id = \'' . $goods_id . '\' ') . 'AND gg.goods_id = g.goods_id ';

	if ($goods_id == 0) {
		$sql .= ' AND gg.admin_id = \'' . $_SESSION['seller_id'] . '\'';
	}

	$sql .= ' order by gg.group_id asc, g.goods_id asc';
	$res = $GLOBALS['db']->getAll($sql);
	$group_goods = get_cfg_group_goods();
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key] = $row;

		if ($group_goods) {
			foreach ($group_goods as $gkey => $group) {
				if ($row['group_id'] == $gkey) {
					$arr[$key]['group_name'] = $group;
				}
			}
		}
	}

	return $arr;
}

function get_goods_articles($goods_id)
{
	$sql = 'SELECT g.article_id, a.title ' . 'FROM ' . $GLOBALS['ecs']->table('goods_article') . ' AS g, ' . $GLOBALS['ecs']->table('article') . ' AS a ' . ('WHERE g.goods_id = \'' . $goods_id . '\' ') . 'AND g.article_id = a.article_id ';

	if ($goods_id == 0) {
		$sql .= ' AND g.admin_id = \'' . $_SESSION['seller_id'] . '\'';
	}

	$row = $GLOBALS['db']->getAll($sql);
	return $row;
}

function goods_list($is_delete, $real_goods = 1, $conditions = '')
{
	$adminru = get_admin_ru_id();
	$ruCat = '';

	if (0 < $adminru['ru_id']) {
		$ruCat = ' and g.user_id = \'' . $adminru['ru_id'] . '\' ';
	}

	$param_str = '-' . $is_delete . '-' . $real_goods;
	$result = get_filter($param_str);

	if ($result === false) {
		$day = getdate();
		$today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
		$filter['cat_id'] = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
		$filter['intro_type'] = empty($_REQUEST['intro_type']) ? '' : trim($_REQUEST['intro_type']);
		$filter['is_promote'] = empty($_REQUEST['is_promote']) ? 0 : intval($_REQUEST['is_promote']);
		$filter['stock_warning'] = empty($_REQUEST['stock_warning']) ? 0 : intval($_REQUEST['stock_warning']);
		$filter['cat_type'] = !isset($_REQUEST['cat_type']) ? '' : addslashes($_REQUEST['cat_type']);
		$filter['brand_id'] = empty($_REQUEST['brand_id']) ? 0 : intval($_REQUEST['brand_id']);
		$filter['brand_keyword'] = empty($_REQUEST['brand_keyword']) ? '' : trim($_REQUEST['brand_keyword']);
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		$filter['suppliers_id'] = isset($_REQUEST['suppliers_id']) ? (empty($_REQUEST['suppliers_id']) ? '' : trim($_REQUEST['suppliers_id'])) : '';
		$filter['is_on_sale'] = isset($_REQUEST['is_on_sale']) ? (empty($_REQUEST['is_on_sale']) && $_REQUEST['is_on_sale'] === 0 ? '' : trim($_REQUEST['is_on_sale'])) : '';
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'g.goods_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['extension_code'] = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
		$filter['is_delete'] = $is_delete;
		$filter['real_goods'] = $real_goods;
		$where = 1;

		if ($filter['cat_type'] == 'seller') {
			$where .= 0 < $filter['cat_id'] ? ' AND (' . get_children($filter['cat_id'], 0, 0, 'merchants_category', 'g.user_cat') . ')' : '';
		}
		else {
			$where .= 0 < $filter['cat_id'] ? ' AND (' . get_children($filter['cat_id']) . ' OR ' . get_children($filter['cat_id'], 1) . ')' : '';
		}

		if ($filter['brand_keyword']) {
			$filter['brand_id'] = $GLOBALS['db']->getAll('SELECT GROUP_CONCAT(brand_id) AS brand_id FROM ' . $GLOBALS['ecs']->table('brand') . (' WHERE brand_name LIKE \'%' . $brand_keyword . '%\' '));
			$where .= ' AND (g.brand_id = \'' . db_create_in($filter['brand_id']) . '\')';
		}

		if ($filter['brand_id']) {
			$where .= ' AND (g.brand_id = \'' . $filter['brand_id'] . '\')';
		}

		$filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_where = '';
		$store_search_where = '';

		if ($filter['store_search'] != 0) {
			if ($adminru['ru_id'] == 0) {
				if ($_REQUEST['store_type']) {
					$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
				}

				if ($filter['store_search'] == 1) {
					$where .= ' AND g.user_id = \'' . $filter['merchant_id'] . '\' ';
				}
				else if ($filter['store_search'] == 2) {
					$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
				}
				else if ($filter['store_search'] == 3) {
					$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
				}

				if (1 < $filter['store_search']) {
					$where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . (' WHERE msi.user_id = g.user_id ' . $store_where . ') > 0 ');
				}
			}
		}

		switch ($filter['intro_type']) {
		case 'is_best':
			$where .= ' AND g.is_best=1';
			break;

		case 'is_hot':
			$where .= ' AND g.is_hot=1';
			break;

		case 'is_new':
			$where .= ' AND g.is_new=1';
			break;

		case 'store_hot':
			$where .= ' AND g.store_hot = 1';
			break;

		case 'store_new':
			$where .= ' AND g.store_new = 1';
			break;

		case 'store_best':
			$where .= ' AND g.store_best = 1';
			break;

		case 'is_promote':
			$where .= ' AND g.is_promote = 1 AND g.promote_price > 0 AND g.promote_start_date <= \'' . $today . '\' AND g.promote_end_date >= \'' . $today . '\'';
			break;

		case 'all_type':
			$where .= ' AND (g.is_best=1 OR g.is_hot=1 OR g.is_new=1 OR (g.is_promote = 1 AND g.promote_price > 0 AND g.promote_start_date <= \'' . $today . '\' AND g.promote_end_date >= \'' . $today . '\'))';
		}

		if ($filter['stock_warning'] || $_REQUEST['stock_warning']) {
			$where .= ' AND g.goods_number <= g.warn_number ';
		}

		if ($filter['extension_code']) {
			$where .= ' AND g.extension_code=\'' . $filter['extension_code'] . '\'';
		}

		if (!empty($filter['keyword'])) {
			$where .= ' AND (g.goods_sn LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\' OR g.goods_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\'' . ')';
		}

		if (-1 < $real_goods) {
			$where .= ' AND g.is_real=\'' . $real_goods . '\'';
		}

		if ($filter['is_on_sale'] !== '') {
			$where .= ' AND (g.is_on_sale = \'' . $filter['is_on_sale'] . '\')';
		}

		if (!empty($filter['suppliers_id'])) {
			$where .= ' AND (g.suppliers_id = \'' . $filter['suppliers_id'] . '\')';
		}

		if (0 < $filter['review_status']) {
			if ($filter['review_status'] == 3) {
				$where .= ' AND (g.review_status >= \'' . $filter['review_status'] . '\')';
			}
			else {
				$where .= ' AND (g.review_status = \'' . $filter['review_status'] . '\')';
			}
		}
		else {
			$where .= ' AND (g.review_status > 0)';
		}

		$where .= $ruCat;
		$where .= $conditions;
		$where .= ' AND g.is_delete = \'' . $is_delete . '\'';
		$sql = 'SELECT g.goods_id, g.goods_name, g.model_price, g.model_inventory, g.user_id, g.goods_type, g.goods_sn, g.shop_price, g.is_on_sale, g.is_best, g.is_new, g.is_hot, g.sort_order, g.goods_number, g.integral, ' . ' g.is_promote ' . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_cat') . ' as gc on g.goods_id = gc.goods_id' . (' WHERE ' . $where . ' GROUP BY g.goods_id');
		$filter['record_count'] = count($GLOBALS['db']->getAll($sql));
		$select = '';

		if (file_exists(MOBILE_DRP)) {
			$select .= ', g.is_distribution';
		}

		$filter = page_and_size($filter);
		$sql = 'SELECT g.goods_id, g.goods_name, g.user_id, g.brand_id, g.goods_type, g.goods_sn, g.shop_price, g.is_on_sale, ' . 'g.is_best, g.is_new, g.is_hot, g.sort_order, g.goods_number, g.integral, g.commission_rate, ' . 'g.is_promote, g.model_price, g.model_inventory, g.model_attr, g.review_status, g.review_content, g.store_best, ' . 'g.store_new , g.store_hot , g.is_real, g.is_shipping, g.stages,g.goods_thumb, add_time, freight, tid, ' . 'g.is_alone_sale, g.is_xiangou, g.promote_end_date, g.xiangou_end_date, g.bar_code ' . $select . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_cat') . ' as gc on g.goods_id = gc.goods_id' . (' WHERE ' . $where) . (' GROUP BY g.goods_id ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . $filter['start'] . (',' . $filter['page_size']);
		$filter['keyword'] = stripslashes($filter['keyword']);
		set_filter($filter, $sql, $param_str);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);
	$count = count($row);

	for ($i = 0; $i < $count; $i++) {
		$row[$i]['user_name'] = get_shop_name($row[$i]['user_id'], 1);
		$brand = get_goods_brand_info($row[$i]['brand_id']);
		$row[$i]['brand_name'] = $brand['brand_name'];
		$row[$i]['is_attr'] = 0;

		if ($row[$i]['goods_type'] == 0) {
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('products') . ' WHERE goods_id = \'' . $row[$i]['goods_id'] . '\'';
			$GLOBALS['db']->query($sql);
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('products_area') . ' WHERE goods_id = \'' . $row[$i]['goods_id'] . '\'';
			$GLOBALS['db']->query($sql);
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('products_warehouse') . ' WHERE goods_id = \'' . $row[$i]['goods_id'] . '\'';
			$GLOBALS['db']->query($sql);
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE goods_id = \'' . $row[$i]['goods_id'] . '\'';
			$GLOBALS['db']->query($sql);
		}
		else {
			$sql = 'SELECT ga.goods_attr_id FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS ga,' . $GLOBALS['ecs']->table('attribute') . ' AS a' . ' WHERE ga.goods_id = \'' . $row[$i]['goods_id'] . '\' AND ga.attr_id = a.attr_id AND a.attr_type <> 0';

			if ($GLOBALS['db']->getOne($sql, true)) {
				$row[$i]['is_attr'] = 1;
			}
		}

		$row[$i]['goods_extend'] = get_goods_extend($row[$i]['goods_id']);
		$row[$i]['url'] = build_uri('goods', array('gid' => $row[$i]['goods_id']), $row[$i]['goods_name']);
		$row[$i]['formated_shop_price'] = price_format($row[$i]['shop_price']);
		$row[$i]['formated_add_tim'] = local_date($GLOBALS['_CFG']['time_format'], $row[$i]['add_time']);

		if ($row[$i]['freight'] == 2) {
			$row[$i]['transport'] = get_goods_transport_info($row[$i]['tid']);
		}

		$row[$i]['goods_thumb'] = get_image_path($row[$i]['goods_id'], $row[$i]['goods_thumb'], true);
	}

	return array('goods' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function check_goods_product_exist($goods_id, $conditions = '')
{
	if (empty($goods_id)) {
		return -1;
	}

	$sql = "SELECT goods_id\r\n            FROM " . $GLOBALS['ecs']->table('products') . ("\r\n            WHERE goods_id = '" . $goods_id . "'\r\n            ") . $conditions . "\r\n            LIMIT 0, 1";
	$result = $GLOBALS['db']->getRow($sql);

	if (empty($result)) {
		return 0;
	}

	return 1;
}

function product_number_count($goods_id, $conditions = '', $warehouse_id = 0)
{
	if (empty($goods_id)) {
		return -1;
	}

	$sql = "SELECT product_number\r\n            FROM " . $GLOBALS['ecs']->table('products') . ("\r\n            WHERE goods_id = '" . $goods_id . "' \r\n            ") . $conditions;
	$nums = $GLOBALS['db']->getOne($sql);
	$nums = empty($nums) ? 0 : $nums;
	return $nums;
}

function product_goods_attr_list($goods_id)
{
	if (empty($goods_id)) {
		return array();
	}

	$sql = 'SELECT goods_attr_id, attr_value FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE goods_id = \'' . $goods_id . '\'');
	$results = $GLOBALS['db']->getAll($sql);
	$return_arr = array();

	foreach ($results as $value) {
		$return_arr[$value['goods_attr_id']] = $value['attr_value'];
	}

	return $return_arr;
}

function get_goods_specifications_list($goods_id)
{
	$where = '';
	$admin_id = get_admin_id();

	if (empty($goods_id)) {
		if ($admin_id) {
			$where .= ' AND admin_id = \'' . $admin_id . '\'';
		}
		else {
			return array();
		}
	}

	$sql = "SELECT g.goods_attr_id, g.attr_value, g.attr_id, a.attr_name\r\n            FROM " . $GLOBALS['ecs']->table('goods_attr') . " AS g\r\n                LEFT JOIN " . $GLOBALS['ecs']->table('attribute') . (" AS a\r\n                    ON a.attr_id = g.attr_id\r\n            WHERE goods_id = '" . $goods_id . "'\r\n            AND a.attr_type = 1") . $where . ' ORDER BY a.sort_order, a.attr_id, g.goods_attr_id';
	$results = $GLOBALS['db']->getAll($sql);
	return $results;
}

function product_list($goods_id, $conditions = '')
{
	$param_str = '-' . $goods_id;
	$result = get_filter($param_str);

	if ($result === false) {
		$day = getdate();
		$today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
		$filter['goods_id'] = $goods_id;
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		$filter['stock_warning'] = empty($_REQUEST['stock_warning']) ? 0 : intval($_REQUEST['stock_warning']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'product_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);
		$filter['extension_code'] = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
		$filter['page_count'] = isset($filter['page_count']) ? $filter['page_count'] : 1;
		$where = '';

		if ($filter['stock_warning']) {
			$where .= ' AND goods_number <= warn_number ';
		}

		if (!empty($filter['keyword'])) {
			$where .= ' AND (product_sn LIKE \'%' . $filter['keyword'] . '%\')';
		}

		$where .= $conditions;
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('products') . (' AS p WHERE goods_id = ' . $goods_id . ' ' . $where);
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$sql = "SELECT product_id, goods_id, goods_attr, product_sn, bar_code, product_price, product_number\r\n                FROM " . $GLOBALS['ecs']->table('products') . (" AS g\r\n                WHERE goods_id = " . $goods_id . ' ' . $where . "\r\n                ORDER BY " . $filter['sort_by'] . ' ' . $filter['sort_order']);
		$filter['keyword'] = stripslashes($filter['keyword']);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);
	$goods_attr = product_goods_attr_list($goods_id);

	foreach ($row as $key => $value) {
		$_goods_attr_array = explode('|', $value['goods_attr']);

		if (is_array($_goods_attr_array)) {
			$_temp = '';

			foreach ($_goods_attr_array as $_goods_attr_value) {
				$_temp[] = $goods_attr[$_goods_attr_value];
			}

			$row[$key]['goods_attr'] = $_temp;
		}
	}

	return array('product' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_product_info($product_id, $filed = '')
{
	$return_array = array();

	if (empty($product_id)) {
		return $return_array;
	}

	$filed = trim($filed);

	if (empty($filed)) {
		$filed = '*';
	}

	$sql = 'SELECT ' . $filed . ' FROM  ' . $GLOBALS['ecs']->table('products') . (' WHERE product_id = \'' . $product_id . '\'');
	$return_array = $GLOBALS['db']->getRow($sql);
	return $return_array;
}

function check_goods_specifications_exist($goods_id)
{
	$goods_id = intval($goods_id);
	$sql = "SELECT COUNT(a.attr_id)\r\n            FROM " . $GLOBALS['ecs']->table('attribute') . ' AS a, ' . $GLOBALS['ecs']->table('goods') . (" AS g\r\n            WHERE a.cat_id = g.goods_type\r\n            AND g.goods_id = '" . $goods_id . '\'');
	$count = $GLOBALS['db']->getOne($sql);

	if (0 < $count) {
		return true;
	}
	else {
		return false;
	}
}

function check_goods_attr_exist($goods_attr, $goods_id, $product_id = 0, $region_id = 0)
{
	$where_products = '';
	$goods_model = $GLOBALS['db']->getOne(' SELECT model_price FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\' '));

	if ($goods_model == 1) {
		$table = 'products_warehouse';
		$where_products .= ' AND warehouse_id = \'' . $region_id . '\' ';
	}
	else if ($goods_model == 2) {
		$table = 'products_area';
		$where_products .= ' AND area_id = \'' . $region_id . '\' ';
	}
	else {
		$table = 'products';
	}

	$goods_id = intval($goods_id);
	if (strlen($goods_attr) == 0 || empty($goods_id)) {
		return true;
	}

	if (empty($product_id)) {
		$sql = 'SELECT product_id FROM ' . $GLOBALS['ecs']->table($table) . ("\r\n                WHERE goods_attr = '" . $goods_attr . "'\r\n                AND goods_id = '" . $goods_id . '\'') . $where_products;
	}
	else {
		$sql = 'SELECT product_id FROM ' . $GLOBALS['ecs']->table($table) . ("\r\n                WHERE goods_attr = '" . $goods_attr . "'\r\n                AND goods_id = '" . $goods_id . "'\r\n                AND product_id <> '" . $product_id . '\'') . $where_products;
	}

	$res = $GLOBALS['db']->getOne($sql);

	if (empty($res)) {
		return false;
	}
	else {
		return true;
	}
}

function check_product_sn_exist($product_sn, $product_id = 0, $ru_id = 0, $goods_model = 0, $warehouse_id = 0, $area_id = 0)
{
	$where = '';

	if ($goods_model == 1) {
		$table = 'products_warehouse';
		$where .= ' AND warehouse_id = \'' . $warehouse_id . '\'';
	}
	else if ($goods_model == 2) {
		$table = 'products_area';
		$where .= ' AND area_id = \'' . $area_id . '\'';
	}
	else {
		$table = 'products';
	}

	$product_sn = trim($product_sn);
	$product_id = intval($product_id);

	if (strlen($product_sn) == 0) {
		return true;
	}

	$sql = 'SELECT g.goods_id FROM ' . $GLOBALS['ecs']->table('goods') . (' AS g WHERE g.goods_sn=\'' . $product_sn . '\' AND g.user_id = \'' . $ru_id . '\'');

	if ($GLOBALS['db']->getOne($sql)) {
		return true;
	}

	$where .= ' AND (SELECT g.user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' AS g WHERE g.goods_id = p.goods_id LIMIT 1) = \'' . $ru_id . '\'');

	if (empty($product_id)) {
		$sql = 'SELECT p.product_id FROM ' . $GLOBALS['ecs']->table($table) . ' AS p ' . ("\r\n                WHERE p.product_sn = '" . $product_sn . '\'') . $where;
	}
	else {
		$sql = 'SELECT p.product_id FROM ' . $GLOBALS['ecs']->table($table) . ' AS p ' . ("\r\n                WHERE p.product_sn = '" . $product_sn . "'\r\n                AND p.product_id <> '" . $product_id . '\'') . $where;
	}

	$res = $GLOBALS['db']->getOne($sql);

	if (empty($res)) {
		return false;
	}
	else {
		return true;
	}
}

function check_product_bar_code_exist($product_bar_code, $product_id = 0, $ru_id = 0, $goods_model = 0, $warehouse_id = 0, $area_id = 0)
{
	$where = '';

	if ($goods_model == 1) {
		$table = 'products_warehouse';
		$where .= ' AND warehouse_id = \'' . $warehouse_id . '\'';
	}
	else if ($goods_model == 2) {
		$table = 'products_area';
		$where .= ' AND area_id = \'' . $area_id . '\'';
	}
	else {
		$table = 'products';
	}

	$product_bar_code = trim($product_bar_code);
	$product_id = intval($product_id);

	if (strlen($product_bar_code) == 0) {
		return true;
	}

	if (!empty($product_id)) {
		$sql = 'SELECT g.user_id FROM ' . $GLOBALS['ecs']->table($table) . ' AS p, ' . $GLOBALS['ecs']->table('goods') . ' AS g' . (' WHERE p.goods_id = g.goods_id AND p.product_id = \'' . $product_id . '\'');
		$ru_id = $GLOBALS['db']->getOne($sql, true);
	}
	else {
		$ru_id = 0;
	}

	$sql = 'SELECT g.goods_id FROM ' . $GLOBALS['ecs']->table('goods') . (' AS g WHERE g.bar_code=\'' . $product_bar_code . '\' AND g.user_id = \'' . $ru_id . '\'');

	if ($GLOBALS['db']->getOne($sql)) {
		return true;
	}

	$where .= ' AND (SELECT g.user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' AS g WHERE g.goods_id = p.goods_id LIMIT 1) = \'' . $ru_id . '\'');

	if (empty($product_id)) {
		$sql = 'SELECT p.product_id FROM ' . $GLOBALS['ecs']->table($table) . ' AS p ' . ("\r\n                WHERE p.bar_code = '" . $product_bar_code . '\'') . $where;
	}
	else {
		$sql = 'SELECT p.product_id FROM ' . $GLOBALS['ecs']->table($table) . ' AS p ' . ("\r\n                WHERE p.bar_code = '" . $product_bar_code . "'\r\n                AND product_id <> '" . $product_id . '\'') . $where;
	}

	$res = $GLOBALS['db']->getOne($sql);

	if (empty($res)) {
		return false;
	}
	else {
		return true;
	}
}

function reformat_image_name($type, $goods_id, $source_img, $position = '')
{
	$rand_name = gmtime() . sprintf('%03d', mt_rand(1, 999));
	$img_ext = substr($source_img, strrpos($source_img, '.'));
	$dir = 'images';

	if (defined('IMAGE_DIR')) {
		$dir = IMAGE_DIR;
	}

	$sub_dir = date('Ym', gmtime());

	if (!make_dir(ROOT_PATH . $dir . '/' . $sub_dir)) {
		return false;
	}

	if (!make_dir(ROOT_PATH . $dir . '/' . $sub_dir . '/source_img')) {
		return false;
	}

	if (!make_dir(ROOT_PATH . $dir . '/' . $sub_dir . '/goods_img')) {
		return false;
	}

	if (!make_dir(ROOT_PATH . $dir . '/' . $sub_dir . '/thumb_img')) {
		return false;
	}

	switch ($type) {
	case 'goods':
		$img_name = $goods_id . '_G_' . $rand_name;
		break;

	case 'goods_thumb':
		$img_name = $goods_id . '_thumb_G_' . $rand_name;
		break;

	case 'gallery':
		$img_name = $goods_id . '_P_' . $rand_name;
		break;

	case 'gallery_thumb':
		$img_name = $goods_id . '_thumb_P_' . $rand_name;
		break;
	}

	if (strpos($source_img, 'temp') !== false) {
		$ex_img = explode('temp', $source_img);
		$source_img = 'temp' . $ex_img[1];
	}
	else if (strpos($source_img, ROOT_PATH) !== false) {
		$source_img = !empty($source_img) ? str_replace(ROOT_PATH, '', $source_img) : '';
	}

	if ($position == 'source') {
		if (move_image_file(ROOT_PATH . $source_img, ROOT_PATH . $dir . '/' . $sub_dir . '/source_img/' . $img_name . $img_ext)) {
			return $dir . '/' . $sub_dir . '/source_img/' . $img_name . $img_ext;
		}
	}
	else if ($position == 'thumb') {
		if (move_image_file(ROOT_PATH . $source_img, ROOT_PATH . $dir . '/' . $sub_dir . '/thumb_img/' . $img_name . $img_ext)) {
			return $dir . '/' . $sub_dir . '/thumb_img/' . $img_name . $img_ext;
		}
	}
	else if (move_image_file(ROOT_PATH . $source_img, ROOT_PATH . $dir . '/' . $sub_dir . '/goods_img/' . $img_name . $img_ext)) {
		return $dir . '/' . $sub_dir . '/goods_img/' . $img_name . $img_ext;
	}

	return false;
}

function move_image_file($source, $dest)
{
	if (@copy($source, $dest)) {
		@unlink($source);
		return true;
	}

	return false;
}

function cat_list_one($cat_id = 0, $cat_level = 0, $seller_shop_cat = array())
{
	if ($cat_id == 0) {
		$arr = cat_list($cat_id, 0, 0, 'category', $seller_shop_cat);
		return $arr;
	}
	else {
		$arr = cat_list($cat_id);

		foreach ($arr as $key => $value) {
			if ($key == $cat_id) {
				unset($arr[$cat_id]);
			}
		}

		$str = '';

		if ($arr) {
			$cat_level++;
			$str .= '<select name=\'catList' . $cat_level . '\' id=\'cat_list' . $cat_level . '\' onchange=\'catList(this.value, ' . $cat_level . ')\' class=\'select\'>';
			$str .= '<option value=\'0\'>全部分类</option>';

			foreach ($arr as $key1 => $value1) {
				$str .= '<option value=\'' . $value1['cat_id'] . '\'>' . $value1['cat_name'] . '</option>';
			}

			$str .= '</select>';
		}

		return $str;
	}
}

function cat_list_one1($cat_id = 0, $cat_level = 0)
{
	if ($cat_id == 0) {
		$arr = cat_list($cat_id);
		return $arr;
	}
	else {
		$arr = cat_list($cat_id);

		foreach ($arr as $key => $value) {
			if ($key == $cat_id) {
				unset($arr[$cat_id]);
			}
		}

		$str = '';

		if ($arr) {
			$cat_level++;
			$str .= '<select name=\'newcatList' . $cat_level . '\' id=\'newcat_list' . $cat_level . '\' onchange=\'newcatList(this.value, ' . $cat_level . ')\' class=\'select\'>';
			$str .= '<option value=\'0\'>全部分类</option>';

			foreach ($arr as $key1 => $value1) {
				$str .= '<option value=\'' . $value1['cat_id'] . '\'>' . $value1['cat_name'] . '</option>';
			}

			$str .= '</select>';
		}

		return $str;
	}
}

function cat_list_one2($cat_id = 0, $cat_level = 0)
{
	if ($cat_id == 0) {
		$arr = cat_list($cat_id);
		return $arr;
	}
	else {
		$arr = cat_list($cat_id);

		foreach ($arr as $key => $value) {
			if ($key == $cat_id) {
				unset($arr[$cat_id]);
			}
		}

		$str = '';

		if ($arr) {
			$cat_level++;
			$str .= '<select name=\'new2catList' . $cat_level . '\' id=\'new2cat_list' . $cat_level . '\' onchange=\'new2catList(this.value, ' . $cat_level . ')\' class=\'select\'>';
			$str .= '<option value=\'0\'>全部分类</option>';

			foreach ($arr as $key1 => $value1) {
				$str .= '<option value=\'' . $value1['cat_id'] . '\'>' . $value1['cat_name'] . '</option>';
			}

			$str .= '</select>';
		}

		return $str;
	}
}

function get_goods_gallery_count($goods_id = 0)
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods_gallery') . (' WHERE goods_id = \'' . $goods_id . '\'');
	return $GLOBALS['db']->getOne($sql);
}

function get_add_seckill_goods($sec_id, $tb_id)
{
	$filter['sec_id'] = $sec_id = empty($sec_id) ? $filter['sec_id'] : $sec_id;
	$filter['tb_id'] = $tb_id = empty($tb_id) ? $filter['tb_id'] : $tb_id;
	$result = get_filter();

	if ($result === false) {
		$where = ' where 1 ';
		$where .= ' AND sg.sec_id = \'' . $sec_id . '\' AND sg.tb_id = \'' . $tb_id . '\' ';
		$sql = ' SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('seckill_goods') . ' AS sg LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON sg.goods_id = g.goods_id  ' . $where . ' ORDER BY sg.tb_id ASC, sg.goods_id ASC ';
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = ' SELECT sg.id, sg.sec_id, sg.tb_id, sg.sec_num, sg.sec_limit, sg.sec_price, g.goods_name, g.shop_price FROM ' . $GLOBALS['ecs']->table('seckill_goods') . ' AS sg LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON sg.goods_id = g.goods_id  ' . $where . ' ORDER BY sg.tb_id ASC, sg.goods_id ASC LIMIT ' . $filter['start'] . ', ' . $filter['page_size'];
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		$row[$key]['shop_price'] = price_format($val['shop_price']);
	}

	$sql = ' SELECT GROUP_CONCAT(sg.goods_id) FROM ' . $GLOBALS['ecs']->table('seckill_goods') . ' AS sg ' . $where;
	$cat_goods = $GLOBALS['db']->getOne($sql);
	$arr = array('seckill_goods' => $row, 'filter' => $filter, 'cat_goods' => $cat_goods, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
