<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function db_create_in($item_list, $field_name = '', $not = '')
{
	if (!empty($not)) {
		$not = ' ' . $not;
	}

	if (empty($item_list)) {
		return $field_name . $not . ' IN (\'\') ';
	}
	else {
		if (!is_array($item_list)) {
			$item_list = explode(',', $item_list);
		}

		$item_list = array_unique($item_list);
		$item_list_tmp = '';

		foreach ($item_list as $item) {
			if ($item !== '') {
				$item = addslashes($item);
				$item_list_tmp .= $item_list_tmp ? ',\'' . $item . '\'' : '\'' . $item . '\'';
			}
		}

		if (empty($item_list_tmp)) {
			return $field_name . $not . ' IN (\'\') ';
		}
		else {
			return $field_name . $not . ' IN (' . $item_list_tmp . ') ';
		}
	}
}

function is_email($user_email)
{
	$chars = '/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}$/i';
	if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false) {
		if (preg_match($chars, $user_email)) {
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}

function is_mobile($mobile)
{
	if (!is_numeric($mobile)) {
		return false;
	}

	return preg_match('/^1(3[0-9]|4[0-9]|5[0-35-9]|6[6]|7[01345678]|8[0-9]|9[89])\\d{8}$/', $mobile) ? true : false;
}

function is_time($time)
{
	$pattern = '/[\\d]{4}-[\\d]{1,2}-[\\d]{1,2}\\s[\\d]{1,2}:[\\d]{1,2}:[\\d]{1,2}/';
	return preg_match($pattern, $time);
}

function assign_query_info()
{
	if ($GLOBALS['db']->queryTime == '') {
		$query_time = 0;
	}
	else if ('5.0.0' <= PHP_VERSION) {
		$query_time = number_format(microtime(true) - $GLOBALS['db']->queryTime, 6);
	}
	else {
		list($now_usec, $now_sec) = explode(' ', microtime());
		list($start_usec, $start_sec) = explode(' ', $GLOBALS['db']->queryTime);
		$query_time = number_format($now_sec - $start_sec + ($now_usec - $start_usec), 6);
	}

	$GLOBALS['smarty']->assign('query_info', sprintf($GLOBALS['_LANG']['query_info'], $GLOBALS['db']->queryCount, $query_time));
	if ($GLOBALS['_LANG']['memory_info'] && function_exists('memory_get_usage')) {
		$GLOBALS['smarty']->assign('memory_info', sprintf($GLOBALS['_LANG']['memory_info'], memory_get_usage() / 1048576));
	}

	$gzip_enabled = gzip_enabled() ? $GLOBALS['_LANG']['gzip_enabled'] : $GLOBALS['_LANG']['gzip_disabled'];
	$GLOBALS['smarty']->assign('gzip_enabled', $gzip_enabled);
}

function region_result($parent, $sel_name, $type)
{
	global $cp;
	$arr = get_regions($type, $parent);

	foreach ($arr as $v) {
		$region = &$cp->add_node('region');
		$region_id = &$region->add_node('id');
		$region_name = &$region->add_node('name');
		$region_id->set_data($v['region_id']);
		$region_name->set_data($v['region_name']);
	}

	$select_obj = &$cp->add_node('select');
	$select_obj->set_data($sel_name);
}

function get_regions($type = 0, $parent = 0)
{
	$sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE region_type = \'' . $type . '\' AND parent_id = \'' . $parent . '\'');
	return $GLOBALS['db']->GetAll($sql);
}

function get_shipping_config($area_id)
{
	$sql = 'SELECT configure FROM ' . $GLOBALS['ecs']->table('shipping_area') . (' WHERE shipping_area_id = \'' . $area_id . '\'');
	$cfg = $GLOBALS['db']->GetOne($sql);

	if ($cfg) {
		$arr = unserialize($cfg);
	}
	else {
		$arr = array();
	}

	return $arr;
}

function& init_users()
{
	$set_modules = false;
	static $cls;

	if ($cls != NULL) {
		return $cls;
	}

	include_once ADDONS_PATH . 'integrates/' . $GLOBALS['_CFG']['integrate_code'] . '.php';
	$cfg = unserialize($GLOBALS['_CFG']['integrate_config']);
	$cls = new $GLOBALS['_CFG']['integrate_code']($cfg);
	return $cls;
}

function cat_list($cat_id = 0, $type = 0, $getrid = 0, $table = 'category', $seller_shop_cat = array(), $cat_level = 0, $user_id = 0)
{
	if ($getrid == 0) {
		$select = ', cat_name, cat_alias_name';

		if ($table == 'merchants_category') {
			$select .= ', user_id';
		}
	}
	else {
		$select = '';
	}

	$where = '';

	if ($seller_shop_cat) {
		if ($seller_shop_cat['parent'] && $seller_shop_cat['parent'] && $cat_level < 3) {
			$seller_shop_cat['parent'] = get_del_str_comma($seller_shop_cat['parent']);
			$where .= ' AND cat_id IN(' . $seller_shop_cat['parent'] . ')';
		}
	}

	if ($table == 'merchants_category' && $user_id) {
		$where .= ' AND user_id = \'' . $user_id . '\'';
	}

	$sql = 'SELECT cat_id ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE parent_id = \'' . $cat_id . '\' ' . $where . ' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	if ($res) {
		foreach ($res as $key => $row) {
			if ($getrid == 0) {
				$row['cat_name'] = htmlspecialchars(addslashes(str_replace("\r\n", '', $row['cat_name'])), ENT_QUOTES);
				$row['level'] = 0;
				$row['select'] = str_repeat('&nbsp;', $row['level'] * 4);
				$arr[$row['cat_id']] = $row;

				if ($table == 'merchants_category') {
					$build_uri = array('cid' => $row['cat_id'], 'urid' => $row['user_id'], 'append' => $row['cat_name']);
					$domain_url = get_seller_domain_url($row['user_id'], $build_uri);
					$arr[$row['cat_id']]['url'] = $domain_url['domain_name'];
				}
				else {
					$arr[$row['cat_id']]['url'] = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);
				}
			}
			else {
				$arr[$row['cat_id']]['cat_id'] = $row['cat_id'];
			}

			if ($type) {
				$arr[$row['cat_id']]['child_tree'] = get_child_tree_pro($row['cat_id'], 0, $table, $getrid, $user_id);
			}
		}
	}

	return $arr;
}

function flush_echo($data)
{
	ob_end_flush();
	ob_implicit_flush(true);
	echo $data;
}

function show_js_message($message, $ext = 0)
{
	flush_echo('<script type="text/javascript">showmessage(\'' . addslashes($message) . '\',' . $ext . ');</script>' . "\r\n");
}

function arr_stime()
{
	return time() + microtime();
}

function sc_timer($stime)
{
	$etime = time() + microtime();
	$pass_time = sprintf('%.2f', $etime - $stime);
	return $pass_time;
}

function load_ecsconfig()
{
	$arr = array();
	$data = read_static_cache('shop_config');

	if ($data === false) {
		$sql = 'SELECT code, value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE parent_id > 0';
		$res = $GLOBALS['db']->getAll($sql);

		foreach ($res as $row) {
			$arr[$row['code']] = $row['value'];
		}

		$arr['watermark_alpha'] = intval($arr['watermark_alpha']);
		$arr['market_price_rate'] = floatval($arr['market_price_rate']);
		$arr['integral_scale'] = floatval($arr['integral_scale']);
		$arr['cache_time'] = intval($arr['cache_time']);
		$arr['thumb_width'] = intval($arr['thumb_width']);
		$arr['thumb_height'] = intval($arr['thumb_height']);
		$arr['image_width'] = intval($arr['image_width']);
		$arr['image_height'] = intval($arr['image_height']);
		$arr['best_number'] = !empty($arr['best_number']) && 0 < intval($arr['best_number']) ? intval($arr['best_number']) : 3;
		$arr['new_number'] = !empty($arr['new_number']) && 0 < intval($arr['new_number']) ? intval($arr['new_number']) : 3;
		$arr['hot_number'] = !empty($arr['hot_number']) && 0 < intval($arr['hot_number']) ? intval($arr['hot_number']) : 3;
		$arr['promote_number'] = !empty($arr['promote_number']) && 0 < intval($arr['promote_number']) ? intval($arr['promote_number']) : 3;
		$arr['top_number'] = 0 < intval($arr['top_number']) ? intval($arr['top_number']) : 10;
		$arr['history_number'] = 0 < intval($arr['history_number']) ? intval($arr['history_number']) : 5;
		$arr['comments_number'] = 0 < intval($arr['comments_number']) ? intval($arr['comments_number']) : 5;
		$arr['article_number'] = 0 < intval($arr['article_number']) ? intval($arr['article_number']) : 5;
		$arr['page_size'] = 0 < intval($arr['page_size']) ? intval($arr['page_size']) : 10;
		$arr['bought_goods'] = intval($arr['bought_goods']);
		$arr['goods_name_length'] = intval($arr['goods_name_length']);
		$arr['top10_time'] = intval($arr['top10_time']);
		$arr['goods_gallery_number'] = intval($arr['goods_gallery_number']) ? intval($arr['goods_gallery_number']) : 5;
		$arr['no_picture'] = !empty($arr['no_picture']) ? str_replace('../', './', $arr['no_picture']) : 'images/no_picture.gif';
		$arr['qq'] = !empty($arr['qq']) ? $arr['qq'] : '';
		$arr['ww'] = !empty($arr['ww']) ? $arr['ww'] : '';
		$arr['default_storage'] = isset($arr['default_storage']) ? intval($arr['default_storage']) : 1;
		$arr['min_goods_amount'] = isset($arr['min_goods_amount']) ? floatval($arr['min_goods_amount']) : 0;
		$arr['one_step_buy'] = empty($arr['one_step_buy']) ? 0 : 1;
		$arr['invoice_type'] = empty($arr['invoice_type']) ? array(
	'type' => array(),
	'rate' => array()
	) : unserialize($arr['invoice_type']);
		$arr['show_order_type'] = isset($arr['show_order_type']) ? $arr['show_order_type'] : 0;
		$arr['help_open'] = isset($arr['help_open']) ? $arr['help_open'] : 1;
		$arr['currency_format'] = !empty($arr['currency_format']) ? strip_tags($arr['currency_format']) : '';
		$arr['tengxun_key'] = isset($arr['tengxun_key']) && !empty($arr['tengxun_key']) ? strip_tags($arr['tengxun_key']) : 'F75BZ-54UKV-6AGPT-UYF6Z-BLUBV-22BAE';
		$lang_array = array('zh_cn', 'zh_tw', 'en_us');
		if (empty($arr['lang']) || !in_array($arr['lang'], $lang_array)) {
			$arr['lang'] = 'zh_cn';
		}

		$arr['lang'] = str_replace('_', '-', $arr['lang']);

		if (empty($arr['integrate_code'])) {
			$arr['integrate_code'] = 'ecshop';
		}

		write_static_cache('shop_config', $arr);
	}
	else {
		$arr = $data;
	}

	return $arr;
}

function cat_options($spec_cat_id, $arr)
{
	static $cat_options = array();

	if (isset($cat_options[$spec_cat_id])) {
		return $cat_options[$spec_cat_id];
	}

	if (!isset($cat_options[0])) {
		$level = $last_cat_id = 0;
		$options = $cat_id_array = $level_array = array();
		$data = read_static_cache('cat_option_static');

		if ($data === false) {
			foreach ($arr as $key => $value) {
				$cat_id = $value['cat_id'];
				if ($level == 0 && $last_cat_id == 0) {
					if (0 < $value['parent_id']) {
						break;
					}

					$options[$cat_id] = $value;
					$options[$cat_id]['level'] = $level;
					$options[$cat_id]['id'] = $cat_id;
					$options[$cat_id]['name'] = $value['cat_name'];
					unset($arr[$key]);

					if ($value['has_children'] == 0) {
						continue;
					}

					$last_cat_id = $cat_id;
					$cat_id_array = array($cat_id);
					$level_array[$last_cat_id] = ++$level;
					continue;
				}

				if ($value['parent_id'] == $last_cat_id) {
					$options[$cat_id] = $value;
					$options[$cat_id]['level'] = $level;
					$options[$cat_id]['id'] = $cat_id;
					$options[$cat_id]['name'] = $value['cat_name'];
					unset($arr[$key]);

					if (0 < $value['has_children']) {
						if (end($cat_id_array) != $last_cat_id) {
							$cat_id_array[] = $last_cat_id;
						}

						$last_cat_id = $cat_id;
						$cat_id_array[] = $cat_id;
						$level_array[$last_cat_id] = ++$level;
					}
				}
				else if ($last_cat_id < $value['parent_id']) {
					break;
				}
			}

			$count = count($cat_id_array);

			if (1 < $count) {
				$last_cat_id = array_pop($cat_id_array);
			}
			else if ($count == 1) {
				if ($last_cat_id != end($cat_id_array)) {
					$last_cat_id = end($cat_id_array);
				}
				else {
					$level = 0;
					$last_cat_id = 0;
					$cat_id_array = array();
				}
			}

			if ($last_cat_id && isset($level_array[$last_cat_id])) {
				$level = $level_array[$last_cat_id];
			}
			else {
				$level = 0;
			}

			if (count($options) <= 2000) {
				write_static_cache('cat_option_static', $options);
			}
		}
		else {
			$options = $data;
		}

		$cat_options[0] = $options;
	}
	else {
		$options = $cat_options[0];
	}

	if (!$spec_cat_id) {
		return $options;
	}
	else {
		if (empty($options[$spec_cat_id])) {
			return array();
		}

		$spec_cat_id_level = $options[$spec_cat_id]['level'];

		foreach ($options as $key => $value) {
			if ($key != $spec_cat_id) {
				unset($options[$key]);
			}
			else {
				break;
			}
		}

		$spec_cat_id_array = array();

		foreach ($options as $key => $value) {
			if ($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id || $value['level'] < $spec_cat_id_level) {
				break;
			}
			else {
				$spec_cat_id_array[$key] = $value;
			}
		}

		$cat_options[$spec_cat_id] = $spec_cat_id_array;
		return $spec_cat_id_array;
	}
}

function get_store_brand_list()
{
	$sql = 'SELECT bid, brandName FROM ' . $GLOBALS['ecs']->table('merchants_shop_brand') . ' where user_id > 0 AND audit_status = 1 ORDER BY bid ASC';
	$res = $GLOBALS['db']->getAll($sql);
	$brand_list = array();

	foreach ($res as $row) {
		$brand_list[$row['bid']] = addslashes($row['brandName']);
	}

	return $brand_list;
}

function get_brands($cat = 0, $app = 'brand', $num = 0)
{
	global $page_libs;
	$template = basename(PHP_SELF);
	$template = substr($template, 0, strrpos($template, '.'));
	static $static_page_libs;

	if ($static_page_libs == NULL) {
		$static_page_libs = $page_libs;
	}

	$children = 0 < $cat ? '1 AND ' . get_children($cat) : 1;
	$sql = 'SELECT b.brand_id, b.brand_name, b.brand_logo, b.brand_desc, COUNT(*) AS goods_num, IF(b.brand_logo > \'\', \'1\', \'0\') AS tag ' . 'FROM ' . $GLOBALS['ecs']->table('brand') . 'AS b ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.brand_id = b.brand_id AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . ('WHERE ' . $children . ' AND b.is_show = 1 ') . 'GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY tag DESC, b.sort_order ASC';

	if (isset($static_page_libs[$template]['/library/brands.lbi'])) {
		$num = get_library_number('brands');
		$sql .= ' LIMIT ' . $num . ' ';
	}
	else if (0 < $num) {
		$sql .= ' LIMIT ' . $num . ' ';
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $val) {
		$row[$key]['url'] = build_uri($app, array('cid' => $cat, 'bid' => $val['brand_id']), $val['brand_name']);
		$row[$key]['brand_desc'] = htmlspecialchars($val['brand_desc'], ENT_QUOTES);
		$row[$key]['brand_logo'] = DATA_DIR . '/brandlogo/' . $val['brand_logo'];

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();
			$row[$key]['brand_logo'] = $bucket_info['endpoint'] . DATA_DIR . '/brandlogo/' . $val['brand_logo'];
		}
	}

	return $row;
}

function get_floor_brand($brand_ids)
{
	$row = array();

	if (is_array($brand_ids)) {
		$sql = 'SELECT brand_id, brand_name, brand_logo, brand_desc from ' . $GLOBALS['ecs']->table('brand') . ' where brand_id ' . db_create_in($brand_ids);
		$row = $GLOBALS['db']->getAll($sql);

		foreach ($row as $key => $val) {
			$row[$key]['url'] = build_uri('brand', array('bid' => $val['brand_id']), $val['brand_name']);
			$row[$key]['brand_desc'] = htmlspecialchars($val['brand_desc'], ENT_QUOTES);
			$row[$key]['brand_logo'] = DATA_DIR . '/brandlogo/' . $val['brand_logo'];
			if ($GLOBALS['_CFG']['open_oss'] == 1 && $val['brand_logo']) {
				$bucket_info = get_bucket_info();
				$row[$key]['brand_logo'] = $bucket_info['endpoint'] . DATA_DIR . '/brandlogo/' . $val['brand_logo'];
			}
		}
	}

	return $row;
}

function get_promotion_info($goods_id = '', $ru_id = 0)
{
	$snatch = array();
	$group = array();
	$auction = array();
	$package = array();
	$favourable = array();
	$gmtime = gmtime();
	$sql = 'SELECT act_id, act_name, act_type, start_time, end_time FROM ' . $GLOBALS['ecs']->table('goods_activity') . (' WHERE is_finished=0 AND start_time <= \'' . $gmtime . '\' AND end_time >= \'' . $gmtime . '\' AND user_id = \'' . $ru_id . '\'');

	if (!empty($goods_id)) {
		$sql .= ' AND goods_id = \'' . $goods_id . '\'';
	}

	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $data) {
		switch ($data['act_type']) {
		case GAT_SNATCH:
			$snatch[$data['act_id']]['act_name'] = $data['act_name'];
			$snatch[$data['act_id']]['url'] = build_uri('snatch', array('sid' => $data['act_id']));
			$snatch[$data['act_id']]['time'] = sprintf(L('promotion_time'), local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
			$snatch[$data['act_id']]['sort'] = $data['start_time'];
			$snatch[$data['act_id']]['type'] = 'snatch';
			break;

		case GAT_GROUP_BUY:
			$group[$data['act_id']]['act_name'] = $data['act_name'];
			$group[$data['act_id']]['url'] = build_uri('group_buy', array('gbid' => $data['act_id']));
			$group[$data['act_id']]['time'] = sprintf(L('promotion_time'), local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
			$group[$data['act_id']]['sort'] = $data['start_time'];
			$group[$data['act_id']]['type'] = 'group_buy';
			break;

		case GAT_AUCTION:
			$auction[$data['act_id']]['act_name'] = $data['act_name'];
			$auction[$data['act_id']]['url'] = build_uri('auction', array('auid' => $data['act_id']));
			$auction[$data['act_id']]['time'] = sprintf(L('promotion_time'), local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
			$auction[$data['act_id']]['sort'] = $data['start_time'];
			$auction[$data['act_id']]['type'] = 'auction';
			break;

		case GAT_PACKAGE:
			$package[$data['act_id']]['act_name'] = $data['act_name'];
			$package[$data['act_id']]['url'] = 'package.php#' . $data['act_id'];
			$package[$data['act_id']]['time'] = sprintf(L('promotion_time'), local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
			$package[$data['act_id']]['sort'] = $data['start_time'];
			$package[$data['act_id']]['type'] = 'package';
			break;
		}
	}

	if (0 < $ru_id) {
		$fav_where = '(user_id = \'' . $ru_id . '\' OR userFav_type = 1)';
	}
	else {
		$fav_where = 'user_id = \'' . $ru_id . '\'';
	}

	$user_rank = ',' . $_SESSION['user_rank'] . ',';
	$leftJoin = '';
	$favourable = array();
	$sql = 'SELECT act_id, act_range, act_range_ext, act_name, start_time, end_time, act_type FROM ' . $GLOBALS['ecs']->table('favourable_activity') . (' WHERE start_time <= \'' . $gmtime . '\' AND end_time >= \'' . $gmtime . '\' AND ') . $fav_where;

	if (!empty($goods_id)) {
		$sql .= ' AND CONCAT(\',\', user_rank, \',\') LIKE \'%' . $user_rank . '%\'';
	}

	$res = $GLOBALS['db']->getAll($sql);

	if (empty($goods_id)) {
		foreach ($res as $rows) {
			$favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
			$favourable[$rows['act_id']]['url'] = 'activity.php';
			$favourable[$rows['act_id']]['time'] = sprintf(L('promotion_time'), local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
			$favourable[$rows['act_id']]['sort'] = $rows['start_time'];
			$favourable[$rows['act_id']]['type'] = 'favourable';
			$favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
		}
	}
	else {
		$sql = 'SELECT g.cat_id, g.brand_id FROM ' . $GLOBALS['ecs']->table('goods') . ' as g' . $leftJoin . (' WHERE g.goods_id = \'' . $goods_id . '\'');
		$row = $GLOBALS['db']->getRow($sql);
		$category_id = $row['cat_id'];
		$brand_id = $row['brand_id'];

		foreach ($res as $rows) {
			if ($rows['act_range'] == FAR_ALL) {
				$favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
				$favourable[$rows['act_id']]['url'] = 'activity.php';
				$favourable[$rows['act_id']]['time'] = sprintf(L('promotion_time'), local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
				$favourable[$rows['act_id']]['sort'] = $rows['start_time'];
				$favourable[$rows['act_id']]['type'] = 'favourable';
				$favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
			}
			else if ($rows['act_range'] == FAR_CATEGORY) {
				$id_list = array();
				$raw_id_list = explode(',', $rows['act_range_ext']);

				foreach ($raw_id_list as $id) {
					$id_list = array_merge($id_list, array_keys(cat_list($id, 0, 0)));
					array_unshift($id_list, $id);
				}

				$ids = join(',', array_unique($id_list));

				if (strpos(',' . $ids . ',', ',' . $category_id . ',') !== false) {
					$favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
					$favourable[$rows['act_id']]['url'] = 'activity.php';
					$favourable[$rows['act_id']]['time'] = sprintf(L('promotion_time'), local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
					$favourable[$rows['act_id']]['sort'] = $rows['start_time'];
					$favourable[$rows['act_id']]['type'] = 'favourable';
					$favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
				}
			}
			else if ($rows['act_range'] == FAR_BRAND) {
				if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $brand_id . ',') !== false) {
					$favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
					$favourable[$rows['act_id']]['url'] = 'activity.php';
					$favourable[$rows['act_id']]['time'] = sprintf(L('promotion_time'), local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
					$favourable[$rows['act_id']]['sort'] = $rows['start_time'];
					$favourable[$rows['act_id']]['type'] = 'favourable';
					$favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
				}
			}
			else if ($rows['act_range'] == FAR_GOODS) {
				if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $goods_id . ',') !== false) {
					$favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
					$favourable[$rows['act_id']]['url'] = 'activity.php';
					$favourable[$rows['act_id']]['time'] = sprintf(L('promotion_time'), local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
					$favourable[$rows['act_id']]['sort'] = $rows['start_time'];
					$favourable[$rows['act_id']]['type'] = 'favourable';
					$favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
				}
			}
		}
	}

	$sort_time = array();
	$arr = array_merge($snatch, $group, $auction, $package, $favourable);

	foreach ($arr as $key => $value) {
		$sort_time[] = $value['sort'];
	}

	array_multisort($sort_time, SORT_NUMERIC, SORT_DESC, $arr);
	return $arr;
}

function get_children($cat = 0, $type = 0, $child_three = 0, $table = 'category', $type_cat = '')
{
	$cat_keys = get_array_keys_cat($cat, 0, $table);

	if ($type != 2) {
		if (empty($type_cat)) {
			if ($type == 1) {
				$type_cat = 'gc.cat_id ';
			}
			else if ($type == 3) {
				$type_cat = 'wc.cat_id ';
			}
			else if ($type == 4) {
				$type_cat = 'w.wholesale_cat_id ';
			}
			else {
				$type_cat = 'g.cat_id ';
			}
		}

		if ($child_three == 1) {
			if ($cat) {
				return $type_cat . db_create_in($cat);
			}
			else {
				return $type_cat . db_create_in('');
			}
		}
		else {
			$cat = array_unique(array_merge(array($cat), $cat_keys));

			if ($cat) {
				$cat = db_create_in($cat);
			}
			else {
				$cat = db_create_in('');
			}

			return $type_cat . $cat;
		}
	}
	else {
		$cat_keys = !empty($cat_keys) ? implode(',', $cat_keys) : '';
		return $cat_keys;
	}
}

function get_array_keys_cat($cat_id, $type = 0, $table = 'category')
{
	$list = arr_foreach(cat_list($cat_id, 1, 1, $table));

	if ($type == 1) {
		if ($list) {
			$list = implode(',', $list);
			$list = get_del_str_comma($list);
		}
	}

	return $list;
}

function get_article_children($cat = 0)
{
	return db_create_in(array_unique(array_merge(array($cat), array_keys(article_cat_list($cat, 0, false)))), 'cat_id');
}

function get_mail_template($tpl_name)
{
	$sql = 'SELECT template_subject, is_html, template_content FROM ' . $GLOBALS['ecs']->table('mail_templates') . (' WHERE template_code = \'' . $tpl_name . '\'');
	return $GLOBALS['db']->GetRow($sql);
}

function order_action($order_sn, $order_status, $shipping_status, $pay_status, $note = '', $username = NULL, $place = 0, $confirm_take_time = 0)
{
	if (!empty($confirm_take_time)) {
		$log_time = $confirm_take_time;
	}
	else {
		$log_time = gmtime();
	}

	if (is_null($username)) {
		$username = $_SESSION['admin_name'];
	}

	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('order_action') . ' (order_id, action_user, order_status, shipping_status, pay_status, action_place, action_note, log_time) ' . 'SELECT ' . ('order_id, \'' . $username . '\', \'' . $order_status . '\', \'' . $shipping_status . '\', \'' . $pay_status . '\', \'' . $place . '\', \'' . $note . '\', \'' . $log_time . '\'') . ' FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_sn = \'' . $order_sn . '\'');
	$GLOBALS['db']->query($sql);
}

function price_format($price, $change_price = true)
{
	if ($price === '') {
		$price = 0;
	}

	if ($change_price && defined('ECS_ADMIN') === false) {
		switch ($GLOBALS['_CFG']['price_format']) {
		case 0:
			$price = number_format($price, 2, '.', '');
			break;

		case 1:
			$price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/', '\\1\\2\\3', number_format($price, 2, '.', ''));

			if (substr($price, -1) == '.') {
				$price = substr($price, 0, -1);
			}

			break;

		case 2:
			$price = substr(number_format($price, 2, '.', ''), 0, -1);
			break;

		case 3:
			$price = intval($price);
			break;

		case 4:
			$price = number_format($price, 1, '.', '');
			break;

		case 5:
			$price = round($price);
			break;
		}
	}
	else {
		@$price = number_format($price, 2, '.', '');
	}

	return sprintf($GLOBALS['_CFG']['currency_format'], $price);
}

function get_virtual_goods($order_id, $shipping = false)
{
	if ($shipping) {
		$sql = 'SELECT goods_id, goods_name, send_number AS num, extension_code FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' AND extension_code > \'\'');
	}
	else {
		$sql = 'SELECT goods_id, goods_name, (goods_number - send_number) AS num, extension_code FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' AND is_real = 0 AND (goods_number - send_number) > 0 AND extension_code > \'\' ');
	}

	$res = $GLOBALS['db']->getAll($sql);
	$virtual_goods = array();

	foreach ($res as $row) {
		$virtual_goods[$row['extension_code']][] = array('goods_id' => $row['goods_id'], 'goods_name' => $row['goods_name'], 'num' => $row['num']);
	}

	return $virtual_goods;
}

function virtual_goods_ship(&$virtual_goods, &$msg, $order_sn, $return_result = false, $process = 'other')
{
	$virtual_card = array();

	foreach ($virtual_goods as $code => $goods_list) {
		if ($code == 'virtual_card') {
			foreach ($goods_list as $goods) {
				if (virtual_card_shipping($goods, $order_sn, $msg, $process)) {
					if ($return_result) {
						$virtual_card[] = array('goods_id' => $goods['goods_id'], 'goods_name' => $goods['goods_name'], 'info' => virtual_card_result($order_sn, $goods));
					}
				}
				else {
					return false;
				}
			}

			$GLOBALS['smarty']->assign('virtual_card', $virtual_card);
		}
	}

	return true;
}

function virtual_card_shipping($goods, $order_sn, &$msg, $process = 'other')
{
	include_once BASE_PATH . 'Helpers/code_helper.php';
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('virtual_card') . (' WHERE goods_id = \'' . $goods['goods_id'] . '\' AND is_saled = 0 ');
	$num = $GLOBALS['db']->GetOne($sql);

	if ($num < $goods['num']) {
		$msg .= sprintf(L('virtual_card_oos'), $goods['goods_name']);
		return false;
	}

	$sql = 'SELECT card_id, card_sn, card_password, end_date, crc32 FROM ' . $GLOBALS['ecs']->table('virtual_card') . (' WHERE goods_id = \'' . $goods['goods_id'] . '\' AND is_saled = 0  LIMIT ') . $goods['num'];
	$arr = $GLOBALS['db']->getAll($sql);
	$card_ids = array();
	$cards = array();

	foreach ($arr as $virtual_card) {
		$card_info = array();
		if ($virtual_card['crc32'] == 0 || $virtual_card['crc32'] == crc32(AUTH_KEY)) {
			$card_info['card_sn'] = _decrypt($virtual_card['card_sn']);
			$card_info['card_password'] = _decrypt($virtual_card['card_password']);
		}
		else if ($virtual_card['crc32'] == crc32(OLD_AUTH_KEY)) {
			$card_info['card_sn'] = _decrypt($virtual_card['card_sn'], OLD_AUTH_KEY);
			$card_info['card_password'] = _decrypt($virtual_card['card_password'], OLD_AUTH_KEY);
		}
		else {
			$msg .= 'error key';
			return false;
		}

		$card_info['end_date'] = date($GLOBALS['_CFG']['date_format'], $virtual_card['end_date']);
		$card_ids[] = $virtual_card['card_id'];
		$cards[] = $card_info;
	}

	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('virtual_card') . ' SET ' . 'is_saled = 1 ,' . ('order_sn = \'' . $order_sn . '\' ') . 'WHERE ' . db_create_in($card_ids, 'card_id');

	if (!$GLOBALS['db']->query($sql)) {
		$msg .= $GLOBALS['db']->error();
		return false;
	}

	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . (' SET goods_number = goods_number - \'' . $goods['num'] . '\' WHERE goods_id = \'' . $goods['goods_id'] . '\'');
	$GLOBALS['db']->query($sql);

	if (true) {
		$sql = 'SELECT order_id, order_sn, consignee, email FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_sn = \'' . $order_sn . '\'');
		$order = $GLOBALS['db']->GetRow($sql);

		if ($process == 'split') {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . "\r\n                    SET send_number = send_number + '" . $goods['num'] . "'\r\n                    WHERE order_id = '" . $order['order_id'] . "'\r\n                    AND goods_id = '" . $goods['goods_id'] . '\' ';
		}
		else {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . "\r\n                    SET send_number = '" . $goods['num'] . "'\r\n                    WHERE order_id = '" . $order['order_id'] . "'\r\n                    AND goods_id = '" . $goods['goods_id'] . '\' ';
		}

		if (!$GLOBALS['db']->query($sql)) {
			$msg .= $GLOBALS['db']->error();
			return false;
		}
	}

	if (C('shop.send_ship_email') == 1) {
		$GLOBALS['smarty']->assign('virtual_card', $cards);
		$GLOBALS['smarty']->assign('order', $order);
		$GLOBALS['smarty']->assign('goods', $goods);
		$GLOBALS['smarty']->assign('send_time', date('Y-m-d H:i:s'));
		$GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
		$GLOBALS['smarty']->assign('send_date', date('Y-m-d'));
		$GLOBALS['smarty']->assign('sent_date', date('Y-m-d'));
		$tpl = get_mail_template('virtual_card');
		$content = $GLOBALS['smarty']->fetch('', $tpl['template_content']);
		send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']);
	}

	return true;
}

function virtual_card_result($order_sn, $goods)
{
	include_once BASE_PATH . 'Helpers/code_helper.php';
	$sql = 'SELECT card_sn, card_password, end_date, crc32 FROM ' . $GLOBALS['ecs']->table('virtual_card') . (' WHERE goods_id= \'' . $goods['goods_id'] . '\' AND order_sn = \'' . $order_sn . '\' ');
	$res = $GLOBALS['db']->query($sql);
	$cards = array();

	foreach ($res as $row) {
		if ($row['crc32'] == 0 || $row['crc32'] == crc32(AUTH_KEY)) {
			$row['card_sn'] = _decrypt($row['card_sn']);
			$row['card_password'] = _decrypt($row['card_password']);
		}
		else if ($row['crc32'] == crc32(OLD_AUTH_KEY)) {
			$row['card_sn'] = _decrypt($row['card_sn'], OLD_AUTH_KEY);
			$row['card_password'] = _decrypt($row['card_password'], OLD_AUTH_KEY);
		}
		else {
			$row['card_sn'] = '***';
			$row['card_password'] = '***';
		}

		$cards[] = array('card_sn' => $row['card_sn'], 'card_password' => $row['card_password'], 'end_date' => date($GLOBALS['_CFG']['date_format'], $row['end_date']));
	}

	return $cards;
}

function get_snatch_result($id)
{
	$sql = 'SELECT u.user_id, u.user_name, u.email, lg.bid_price, lg.bid_time, count(*) as num' . ' FROM ' . $GLOBALS['ecs']->table('snatch_log') . ' AS lg ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON lg.user_id = u.user_id' . (' WHERE lg.snatch_id = \'' . $id . '\'') . ' GROUP BY lg.bid_price' . ' ORDER BY num ASC, lg.bid_price ASC, lg.bid_time ASC LIMIT 1';
	$rec = $GLOBALS['db']->GetRow($sql);

	if ($rec) {
		$rec['bid_time'] = local_date($GLOBALS['_CFG']['time_format'], $rec['bid_time']);
		$rec['formated_bid_price'] = price_format($rec['bid_price'], false);
		$sql = "SELECT ext_info \" .\r\n               \" FROM " . $GLOBALS['ecs']->table('goods_activity') . (' WHERE act_id= \'' . $id . '\' AND act_type=') . GAT_SNATCH . ' LIMIT 1';
		$row = $GLOBALS['db']->getOne($sql);
		$info = unserialize($row);

		if (!empty($info['max_price'])) {
			$rec['buy_price'] = $info['max_price'] < $rec['bid_price'] ? $info['max_price'] : $rec['bid_price'];
		}
		else {
			$rec['buy_price'] = $rec['bid_price'];
		}

		$sql = 'SELECT COUNT(*)' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE extension_code = \'snatch\'' . (' AND extension_id = \'' . $id . '\'') . ' AND order_status ' . db_create_in(array(OS_CONFIRMED, OS_UNCONFIRMED));
		$rec['order_count'] = $GLOBALS['db']->getOne($sql);
	}

	return $rec;
}

function clear_tpl_files($is_cache = true, $ext = '')
{
	$cachePath = ROOT_PATH . 'storage/framework/';
	$dirs = array('cache', 'data', 'temp', 'views');

	foreach ($dirs as $dir) {
		\App\Extensions\Util::delDir($cachePath . $dir);
	}

	return true;
}

function clear_compiled_files($ext = '')
{
	return clear_tpl_files(false, $ext);
}

function clear_cache_files($ext = '')
{
	return clear_tpl_files(true, $ext);
}

function clear_all_files($ext = '', $filename = '')
{
	return clear_tpl_files(false, $ext, $filename) + clear_tpl_files(true, $ext, $filename);
}

function get_deldir($dir, $strpos = '', $is_rmdir = false)
{
	$dh = opendir($dir);

	while ($file = readdir($dh)) {
		if ($file != '.' && $file != '..') {
			$fullpath = $dir . '/' . $file;

			if ($strpos) {
				$spos = strpos($fullpath, $strpos);

				if ($spos !== false) {
					if (!is_dir($fullpath)) {
						unlink($fullpath);
					}
					else {
						deldir($fullpath);
					}
				}
			}
			else if (!is_dir($fullpath)) {
				unlink($fullpath);
			}
			else {
				deldir($fullpath);
			}
		}
	}

	closedir($dh);

	if ($is_rmdir == true) {
		if (rmdir($dir)) {
			return true;
		}
		else {
			return false;
		}
	}
}

function smarty_insert_scripts($args)
{
	static $scripts = array();
	$arr = explode(',', str_replace(' ', '', $args['files']));
	$str = '';

	foreach ($arr as $val) {
		if (in_array($val, $scripts) == false) {
			$scripts[] = $val;

			if ($val[0] == '.') {
				$str .= '<script type="text/javascript" src="' . $val . '"></script>';
			}
			else {
				$str .= '<script type="text/javascript" src="js/' . $val . '"></script>';
			}
		}
	}

	return $str;
}

function smarty_create_pages($params)
{
	extract($params);
	$str = '';
	$len = 10;

	if (empty($page)) {
		$page = 1;
	}

	if (!empty($count)) {
		$step = 1;
		$str .= '<option value=\'1\'>1</option>';

		for ($i = 2; $i < $count; $i += $step) {
			$step = $page + $len - 1 <= $i || $i <= $page - $len + 1 ? $len : 1;
			$str .= '<option value=\'' . $i . '\'';
			$str .= $page == $i ? ' selected=\'true\'' : '';
			$str .= '>' . $i . '</option>';
		}

		if (1 < $count) {
			$str .= '<option value=\'' . $count . '\'';
			$str .= $page == $count ? ' selected=\'true\'' : '';
			$str .= '>' . $count . '</option>';
		}
	}

	return $str;
}

function build_uri($app, $params, $append = '', $page = 0, $keywords = '', $size = 0)
{
	static $rewrite;

	if ($rewrite === NULL) {
		$rewrite = intval($GLOBALS['_CFG']['rewrite']);
	}

	$args = array('cid' => 0, 'gid' => 0, 'bid' => 0, 'acid' => 0, 'aid' => 0, 'mid' => 0, 'urid' => 0, 'ubrand' => 0, 'chkw' => '', 'is_ship' => '', 'hid' => 0, 'sid' => 0, 'gbid' => 0, 'auid' => 0, 'sort' => '', 'order' => '', 'actid' => 0, 'stid' => 0);
	extract(array_merge($args, $params));
	$uri = '';

	switch ($app) {
	case 'category':
		if (empty($cid)) {
			return false;
		}
		else {
			$data = array('id' => $cid);

			if (!empty($bid)) {
				$data['brand'] = $bid;
			}

			if (!empty($ubrand)) {
				$data['ubrand'] = $ubrand;
			}

			if (isset($price_min)) {
				$data['price_min'] = $price_min;
			}

			if (isset($price_max)) {
				$data['price_max'] = $price_max;
			}

			if (!empty($filter_attr)) {
				$data['filter_attr'] = $filter_attr;
			}

			if (!empty($page)) {
				$data['page'] = $page;
			}

			if (!empty($sort)) {
				$data['sort'] = $sort;
			}

			if (!empty($order)) {
				$data['order'] = $order;
			}

			$uri = url('category/index/products', $data);
		}

		break;

	case 'goods':
		if (empty($gid)) {
			return false;
		}
		else {
			$uri = url('goods/index/index', array('id' => $gid, 'u' => $_SESSION['user_id']));
		}

		break;

	case 'activity':
		if (empty($actid)) {
			return false;
		}
		else {
			$uri = url('activity/index/detail', array('id' => $actid));
		}

		break;

	case 'community':
		if (empty($params['coid'])) {
			return false;
		}
		else {
			$uri = url('community/index/detail', array('id' => $params['coid'], 'type' => $params['type']));
		}

		break;

	case 'topic':
		if (empty($params['topic_id'])) {
			return false;
		}
		else {
			$uri = url('topic/index/detail', array('topic_id' => $params['topic_id']));
		}

		break;

	case 'presale':
		if (empty($params['id'])) {
			return false;
		}
		else {
			$uri = url('presale/index/detail', array('id' => $params['id']));
		}

		break;

	case 'brand':
		if (empty($bid)) {
			return false;
		}
		else {
			$uri = url('brand/index/detail', array('id' => $bid));
		}

		break;

	case 'article_cat':
		if (empty($acid)) {
			return false;
		}
		else {
			$data = array('id' => $acid);

			if (!empty($page)) {
				$data['page'] = $page;
			}

			if (!empty($sort)) {
				$data['sort'] = $sort;
			}

			if (!empty($order)) {
				$data['order'] = $order;
			}

			if (!empty($keywords)) {
				$data['keywords'] = $keywords;
			}

			$uri = url('article/index/category', $data);
		}

		break;

	case 'article':
		if (empty($aid)) {
			return false;
		}
		else {
			$uri = url('article/index/detail', array('id' => $aid));
		}

		break;

	case 'groupbuy':
		if (empty($gbid)) {
			return false;
		}
		else {
			$uri = url('groupbuy/index/detail', array('id' => $gbid));
		}

		break;

	case 'store':
		if (empty($stid)) {
			return false;
		}
		else {
			$uri = url('store/index/shop_info', array('id' => $stid));
		}

		break;

	case 'auction':
		if (empty($auid)) {
			return false;
		}
		else {
			$uri = url('auction/index/detail', array('id' => $auid));
		}

		break;

	case 'snatch':
		if (empty($sid)) {
			return false;
		}
		else {
			$uri = url('snatch/index/index', array('id' => $sid));
		}

		break;

	case 'search':
		break;

	case 'exchange':
		$data = array('cat_id' => $cid);

		if (isset($price_min)) {
			$data['integral_min'] = $price_min;
		}

		if (isset($price_max)) {
			$data['integral_max'] = $price_max;
		}

		if (!empty($page)) {
			$data['page'] = $page;
		}

		if (!empty($sort)) {
			$data['sort'] = $sort;
		}

		if (!empty($order)) {
			$data['order'] = $order;
		}

		$uri = url('exchange/index/index', $data);
		break;

	case 'exchange_goods':
		if (empty($gid)) {
			return false;
		}
		else {
			$uri = url('exchange/index/detail', array('id' => $gid));
		}

		break;

	default:
		return false;
		break;
	}

	return $uri;
}

function formated_weight($weight)
{
	$weight = round(floatval($weight), 3);

	if (0 < $weight) {
		if ($weight < 1) {
			return intval($weight * 1000) . $GLOBALS['_LANG']['gram'];
		}
		else {
			return $weight . $GLOBALS['_LANG']['kilogram'];
		}
	}
	else {
		return 0;
	}
}

function log_account_change($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '', $change_type = ACT_OTHER, $order_type = 0, $deposit_fee = 0)
{
	$is_go = true;
	$is_user_money = 0;
	$is_pay_points = 0;
	if ($is_go && ($user_money || $frozen_money || $rank_points || $pay_points)) {
		$account_log = array('user_id' => $user_id, 'user_money' => $user_money, 'frozen_money' => $frozen_money, 'rank_points' => $rank_points, 'pay_points' => $pay_points, 'change_time' => gmtime(), 'change_desc' => $change_desc, 'change_type' => $change_type, 'deposit_fee' => $deposit_fee);
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('account_log'), $account_log, 'INSERT');
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . (' SET user_money = user_money + (\'' . $user_money . '\'+ \'' . $deposit_fee . '\'),') . (' frozen_money = frozen_money + (\'' . $frozen_money . '\'),') . (' rank_points = rank_points + (\'' . $rank_points . '\'),') . (' pay_points = pay_points + (\'' . $pay_points . '\')') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
		$GLOBALS['db']->query($sql);
		$sql = 'SELECT rank_points FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $user_id . '\'');
		$user_rank_points = $GLOBALS['db']->getOne($sql, true);
		$sql = 'SELECT rank_id, discount FROM ' . $GLOBALS['ecs']->table('user_rank') . ' WHERE special_rank = 0 AND min_points <= \'' . $user_rank_points . '\' AND max_points > \'' . $user_rank_points . '\' LIMIT 1';
		$rank_row = $GLOBALS['db']->getRow($sql);

		if ($rank_row) {
			$rank_row['discount'] = $rank_row['discount'] / 100;
		}
		else {
			$rank_row['discount'] = 1;
			$rank_row['rank_id'] = 0;
		}

		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . 'SET user_rank = \'' . $rank_row['rank_id'] . ('\' WHERE user_id = \'' . $user_id . '\'');
		$GLOBALS['db']->query($sql);
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('sessions') . 'SET user_rank = \'' . $rank_row['rank_id'] . '\', discount= \'' . $rank_row['discount'] . ('\' WHERE userid = \'' . $user_id . '\' AND adminid = 0');
		$GLOBALS['db']->query($sql);
	}
}

function article_cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0)
{
	static $res;

	if ($res === NULL) {
		$data = read_static_cache('art_cat_pid_releate');

		if ($data === false) {
			$sql = 'SELECT c.*, COUNT(s.cat_id) AS has_children, COUNT(a.article_id) AS aricle_num,a.description ' . ' FROM ' . $GLOBALS['ecs']->table('article_cat') . ' AS c' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('article_cat') . ' AS s ON s.parent_id=c.cat_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('article') . ' AS a ON a.cat_id=c.cat_id' . ' GROUP BY c.cat_id ' . ' ORDER BY parent_id, sort_order ASC';
			$res = $GLOBALS['db']->getAll($sql);
			write_static_cache('art_cat_pid_releate', $res);
		}
		else {
			$res = $data;
		}
	}

	if (empty($res) == true) {
		return $re_type ? '' : array();
	}

	$options = article_cat_options($cat_id, $res);

	if (0 < $level) {
		if ($cat_id == 0) {
			$end_level = $level;
		}
		else {
			$first_item = reset($options);
			$end_level = $first_item['level'] + $level;
		}

		foreach ($options as $key => $val) {
			if ($end_level <= $val['level']) {
				unset($options[$key]);
			}
		}
	}

	$pre_key = 0;

	foreach ($options as $key => $value) {
		$options[$key]['has_children'] = 1;

		if (0 < $pre_key) {
			if ($options[$pre_key]['cat_id'] == $options[$key]['parent_id']) {
				$options[$pre_key]['has_children'] = 1;
			}
		}

		$pre_key = $key;
	}

	if ($re_type == true) {
		$select = '';

		foreach ($options as $var) {
			$select .= '<option value="' . $var['cat_id'] . '" ';
			$select .= ' cat_type="' . $var['cat_type'] . '" ';
			$select .= $selected == $var['cat_id'] ? 'selected=\'ture\'' : '';
			$select .= '>';

			if (0 < $var['level']) {
				$select .= str_repeat('&nbsp;', $var['level'] * 4);
			}

			$select .= htmlspecialchars(addslashes($var['cat_name'])) . '</option>';
		}

		return $select;
	}
	else {
		foreach ($options as $key => $value) {
			$options[$key]['url'] = build_uri('article_cat', array('acid' => $value['cat_id']), $value['cat_name']);
		}

		return $options;
	}
}

function article_cat_options($spec_cat_id, $arr)
{
	static $cat_options = array();

	if (isset($cat_options[$spec_cat_id])) {
		return $cat_options[$spec_cat_id];
	}

	if (!isset($cat_options[0])) {
		$level = $last_cat_id = 0;
		$options = $cat_id_array = $level_array = array();

		while (!empty($arr)) {
			foreach ($arr as $key => $value) {
				$cat_id = $value['cat_id'];
				if ($level == 0 && $last_cat_id == 0) {
					if (0 < $value['parent_id']) {
						break;
					}

					$options[$cat_id] = $value;
					$options[$cat_id]['level'] = $level;
					$options[$cat_id]['id'] = $cat_id;
					$options[$cat_id]['name'] = $value['cat_name'];
					unset($arr[$key]);

					if ($value['has_children'] == 0) {
						continue;
					}

					$last_cat_id = $cat_id;
					$cat_id_array = array($cat_id);
					$level_array[$last_cat_id] = ++$level;
					continue;
				}

				if ($value['parent_id'] == $last_cat_id) {
					$options[$cat_id] = $value;
					$options[$cat_id]['level'] = $level;
					$options[$cat_id]['id'] = $cat_id;
					$options[$cat_id]['name'] = $value['cat_name'];
					unset($arr[$key]);

					if (0 < $value['has_children']) {
						if (end($cat_id_array) != $last_cat_id) {
							$cat_id_array[] = $last_cat_id;
						}

						$last_cat_id = $cat_id;
						$cat_id_array[] = $cat_id;
						$level_array[$last_cat_id] = ++$level;
					}
				}
				else if ($last_cat_id < $value['parent_id']) {
					break;
				}
			}

			$count = count($cat_id_array);

			if (1 < $count) {
				$last_cat_id = array_pop($cat_id_array);
			}
			else if ($count == 1) {
				if ($last_cat_id != end($cat_id_array)) {
					$last_cat_id = end($cat_id_array);
				}
				else {
					$level = 0;
					$last_cat_id = 0;
					$cat_id_array = array();
					continue;
				}
			}

			if ($last_cat_id && isset($level_array[$last_cat_id])) {
				$level = $level_array[$last_cat_id];
			}
			else {
				$level = 0;
			}
		}

		$cat_options[0] = $options;
	}
	else {
		$options = $cat_options[0];
	}

	if (!$spec_cat_id) {
		return $options;
	}
	else {
		if (empty($options[$spec_cat_id])) {
			return array();
		}

		$spec_cat_id_level = $options[$spec_cat_id]['level'];

		foreach ($options as $key => $value) {
			if ($key != $spec_cat_id) {
				unset($options[$key]);
			}
			else {
				break;
			}
		}

		$spec_cat_id_array = array();

		foreach ($options as $key => $value) {
			if ($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id || $value['level'] < $spec_cat_id_level) {
				break;
			}
			else {
				$spec_cat_id_array[$key] = $value;
			}
		}

		$cat_options[$spec_cat_id] = $spec_cat_id_array;
		return $spec_cat_id_array;
	}
}

function uc_call($func, $params = NULL)
{
	restore_error_handler();

	if (!function_exists($func)) {
		include_once dirname(ROOT_PATH) . '/uc_client/client.php';
	}

	$res = call_user_func_array($func, $params);
	set_error_handler('exception_handler');
	return $res;
}

function exception_handler($errno, $errstr, $errfile, $errline)
{
	return NULL;
}

function get_image_path($image = '', $path = '', $default = NULL)
{
	if (strtolower(substr($image, 0, 4)) == 'http') {
		$url = $image;
	}
	else {
		$no_picture = is_null($default) ? elixir('img/no_image.jpg') : $default;
		$path = empty($path) ? '' : rtrim($path, '/') . '/';

		if (C('shop.open_oss') == 1) {
			$bucket_info = get_bucket_info();
			$bucket_info['endpoint'] = empty($bucket_info['endpoint']) ? $bucket_info['outside_site'] : $bucket_info['endpoint'];
			$url = empty($image) ? $no_picture : rtrim($bucket_info['endpoint'], '/') . '/' . $path . $image;
		}
		else {
			$img_path = $path . $image;
			if (empty($image) || !file_exists(dirname(ROOT_PATH) . '/' . $img_path)) {
				$url = $no_picture;
			}
			else {
				$url = __STATIC__ . '/' . $img_path;
			}
		}
	}

	return $url;
}

function get_zc_image_path($image = '', $path = '')
{
	if (strtolower(substr($image, 0, 4)) == 'http') {
		$url = $image;
	}
	else {
		$no_picture = elixir('img/no_image.jpg');
		$path = empty($path) ? '' : rtrim($path, '/') . '/';
		$img_path = $path . $image;
		if (empty($image) || !file_exists(dirname(ROOT_PATH) . '/' . $img_path)) {
			$url = $no_picture;
		}
		else {
			$url = __STATIC__ . '/' . $img_path;
		}
	}

	return $url;
}

function get_data_path($url = '', $path = '', $default = NULL)
{
	return get_image_path($url, 'data/' . $path, $default);
}

function user_uc_call($func, $params = NULL)
{
	if (isset($GLOBALS['_CFG']['integrate_code']) && $GLOBALS['_CFG']['integrate_code'] == 'ucenter') {
		restore_error_handler();

		if (!function_exists($func)) {
			include_once BASE_PATH . 'Helpers/uc_helper.php';
		}

		$res = call_user_func_array($func, $params);
		set_error_handler('exception_handler');
		return $res;
	}
	else {
		return NULL;
	}
}

function get_volume_price_list($goods_id, $price_type = '1')
{
	$volume_price = array();
	$temp_index = '0';
	$sql = 'SELECT `volume_number` , `volume_price`' . ' FROM ' . $GLOBALS['ecs']->table('volume_price') . '' . ' WHERE `goods_id` = \'' . $goods_id . '\' AND `price_type` = \'' . $price_type . '\'' . ' ORDER BY `volume_number`';
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $k => $v) {
		$volume_price[$temp_index] = array();
		$volume_price[$temp_index]['number'] = $v['volume_number'];
		$volume_price[$temp_index]['price'] = $v['volume_price'];
		$volume_price[$temp_index]['format_price'] = price_format($v['volume_price']);
		$temp_index++;
	}

	return $volume_price;
}

function get_final_price($goods_id, $goods_num = '1', $is_spec_price = false, $spec = array(), $warehouse_id = 0, $area_id = 0, $type = 0, $presale = 0, $add_tocart = 1, $show_goods = 0, $product_promote_price = 0)
{
	$spec_price = 0;
	$warehouse_area['warehouse_id'] = $warehouse_id;
	$warehouse_area['area_id'] = $area_id;

	if ($is_spec_price) {
		if (!empty($spec)) {
			$spec_price = spec_price($spec, $goods_id, $warehouse_area);
		}
	}

	$final_price = '0';
	$volume_price = '0';
	$promote_price = '0';
	$user_price = '0';
	$user_rank = $_SESSION['user_rank'];
	$price_list = get_volume_price_list($goods_id, '1');

	if (!empty($price_list)) {
		foreach ($price_list as $value) {
			if ($value['number'] <= $goods_num) {
				$volume_price = $value['price'];
			}
		}
	}

	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('presale_activity') . ' AS pa, ' . $GLOBALS['ecs']->table('goods') . (' AS g WHERE pa.goods_id = \'' . $goods_id . '\' AND pa.review_status = 3 AND pa.goods_id = g.goods_id AND g.is_on_sale = 0');
	$is_presale = $GLOBALS['db']->getOne($sql);
	$where = '';
	if (0 < $is_presale || $presale == 1) {
		$user_rank = 1;
		$discount = 1;
	}
	else {
		$discount = $_SESSION['discount'];
	}

	$leftJoin = '';
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_goods') . (' AS wg ON g.goods_id = wg.goods_id AND wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' AS wag ON g.goods_id = wag.goods_id AND wag.region_id = \'' . $area_id . '\' ');
	$sql = 'SELECT ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $discount . '\'), g.shop_price * \'' . $discount . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . ' g.promote_start_date, g.promote_end_date, mp.user_price, g.user_id, g.model_price, g.model_attr ' . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . (' AS mp ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $user_rank . '\' ') . $leftJoin . ' WHERE g.goods_id = \'' . $goods_id . '\'' . ' AND g.is_delete = 0 LIMIT 1';
	$goods = $GLOBALS['db']->getRow($sql);
	if ($GLOBALS['_CFG']['add_shop_price'] == 0 && $product_promote_price <= 0) {
		$product_spec = !empty($spec) && is_array($spec) ? implode(',', $spec) : '';
		$products = get_warehouse_id_attr_number($goods_id, $product_spec, $goods['user_id'], $warehouse_id, $area_id);
		$product_promote_price = isset($products['product_promote_price']) ? $products['product_promote_price'] : 0;
	}

	if ($GLOBALS['_CFG']['add_shop_price'] == 0 && !empty($product_promote_price)) {
		$goods['promote_price'] = $product_promote_price;
	}

	if (0 < $goods['promote_price']) {
		$promote_price = bargain_price($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
	}
	else {
		$promote_price = 0;
	}

	if (0 < $spec_price && $GLOBALS['_CFG']['add_shop_price'] == 0) {
		if ($add_tocart == 1) {
			$user_price = $goods['shop_price'];
		}
		else {
			if (0 < $goods['user_price'] && $goods['user_price'] < $spec_price) {
				$user_price = $goods['user_price'];
			}
			else {
				$user_price = $spec_price * $discount;
			}
		}

		if ($show_goods == 1) {
			if (!empty($goods['user_price'])) {
				$spec_price = $goods['user_price'];
			}
			else {
				$spec_price = $spec_price * $discount;
			}
		}
	}
	else {
		$user_price = $goods['shop_price'];
	}

	if (empty($volume_price) && empty($promote_price)) {
		$final_price = $user_price;
	}
	else {
		if (!empty($volume_price) && empty($promote_price)) {
			$final_price = min($volume_price, $user_price);
		}
		else {
			if (empty($volume_price) && !empty($promote_price)) {
				$final_price = min($promote_price, $user_price);
			}
			else {
				if (!empty($volume_price) && !empty($promote_price)) {
					$final_price = min($volume_price, $promote_price, $user_price);
				}
				else {
					$final_price = $user_price;
				}
			}
		}
	}

	if ($is_spec_price) {
		if (!empty($spec)) {
			if ($type == 0) {
				if ($add_tocart == 1) {
					$final_price += $spec_price;
				}
			}
		}
	}

	if ($GLOBALS['_CFG']['add_shop_price'] == 1) {
		if ($type == 1 && $promote_price == 0) {
			$final_price = $spec_price;
		}
	}

	return $final_price;
}

function sort_goods_attr_id_array($goods_attr_id_array, $sort = 'asc')
{
	if (empty($goods_attr_id_array)) {
		return $goods_attr_id_array;
	}

	$sql = "SELECT a.attr_type, v.attr_value, v.goods_attr_id, v.attr_checked\r\n            FROM " . $GLOBALS['ecs']->table('attribute') . " AS a\r\n            LEFT JOIN " . $GLOBALS['ecs']->table('goods_attr') . " AS v\r\n                ON v.attr_id = a.attr_id\r\n                AND a.attr_type = 1\r\n            WHERE v.goods_attr_id " . db_create_in($goods_attr_id_array) . ("\r\n            ORDER BY a.sort_order, v.attr_id " . $sort);
	$row = $GLOBALS['db']->GetAll($sql);
	$return_arr = array();

	foreach ($row as $value) {
		$return_arr['sort'][] = $value['goods_attr_id'];
		$return_arr['row'][$value['goods_attr_id']] = $value;
	}

	return $return_arr;
}

function is_spec($goods_attr_id_array, $sort = 'asc')
{
	if (empty($goods_attr_id_array)) {
		return $goods_attr_id_array;
	}

	$sql = "SELECT a.attr_type, v.attr_value, v.goods_attr_id\r\n            FROM " . $GLOBALS['ecs']->table('attribute') . " AS a\r\n            LEFT JOIN " . $GLOBALS['ecs']->table('goods_attr') . " AS v\r\n                ON v.attr_id = a.attr_id\r\n                AND a.attr_type = 1\r\n            WHERE v.goods_attr_id " . db_create_in($goods_attr_id_array) . ("\r\n            ORDER BY a.attr_id " . $sort);
	$row = $GLOBALS['db']->GetAll($sql);
	$return_arr = array();

	foreach ($row as $value) {
		$return_arr['sort'][] = $value['goods_attr_id'];
		$return_arr['row'][$value['goods_attr_id']] = $value;
	}

	if (!empty($return_arr)) {
		return true;
	}
	else {
		return false;
	}
}

function get_package_info($id)
{
	global $ecs;
	global $db;
	global $_CFG;
	$id = is_numeric($id) ? intval($id) : 0;
	$now = gmtime();
	$sql = 'SELECT act_id AS id,  act_name AS package_name, goods_id , goods_name, start_time, end_time, act_desc, ext_info, user_id, activity_thumb ' . ' FROM ' . $GLOBALS['ecs']->table('goods_activity') . (' WHERE act_id=\'' . $id . '\' AND act_type = ') . GAT_PACKAGE;
	$package = $db->GetRow($sql);
	if ($package['start_time'] <= $now && $now <= $package['end_time']) {
		$package['is_on_sale'] = '1';
	}
	else {
		$package['is_on_sale'] = '0';
	}

	$package['start_time'] = local_date('Y-m-d H:i', $package['start_time']);
	$package['end_time'] = local_date('Y-m-d H:i', $package['end_time']);
	$row = unserialize($package['ext_info']);
	unset($package['ext_info']);

	if ($row) {
		foreach ($row as $key => $val) {
			$package[$key] = $val;
		}
	}

	$sql = 'SELECT pg.package_id, pg.goods_id, pg.goods_number, pg.admin_id, ' . ' g.goods_sn, g.goods_name, g.market_price, g.goods_thumb, g.is_real, ' . (' IFNULL(mp.user_price, g.shop_price * \'' . $_SESSION['discount'] . '\') AS rank_price ') . ' FROM ' . $GLOBALS['ecs']->table('package_goods') . ' AS pg ' . '   LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . '   ON g.goods_id = pg.goods_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ' WHERE pg.package_id = ' . $id . ' ' . ' ORDER BY pg.package_id, pg.goods_id';
	$goods_res = $GLOBALS['db']->getAll($sql);
	$market_price = 0;
	$real_goods_count = 0;
	$virtual_goods_count = 0;

	foreach ($goods_res as $key => $val) {
		$goods_res[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
		$goods_res[$key]['market_price_format'] = price_format($val['market_price']);
		$goods_res[$key]['rank_price_format'] = price_format($val['rank_price']);
		$market_price += $val['market_price'] * $val['goods_number'];

		if ($val['is_real']) {
			$real_goods_count++;
		}
		else {
			$virtual_goods_count++;
		}
	}

	if (0 < $real_goods_count) {
		$package['is_real'] = 1;
	}
	else {
		$package['is_real'] = 0;
	}

	$package['goods_list'] = $goods_res;
	$package['market_package'] = $market_price;
	$package['market_package_format'] = price_format($market_price);
	$package['package_price_format'] = price_format($package['package_price']);
	return $package;
}

function get_package_goods($package_id, $seller_id = 0, $type = 0)
{
	$sql = 'SELECT pg.goods_id, g.goods_name, pg.goods_number, p.goods_attr, p.product_number, p.product_id, g.goods_weight, g.goods_thumb, g.shop_price * \'' . $_SESSION['discount'] . "' AS rank_price\r\n            FROM " . $GLOBALS['ecs']->table('package_goods') . " AS pg\r\n                LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON pg.goods_id = g.goods_id\r\n                LEFT JOIN " . $GLOBALS['ecs']->table('products') . (" AS p ON pg.product_id = p.product_id\r\n            WHERE pg.package_id = '" . $package_id . '\'');
	if ($package_id == 0 && $seller_id == 0) {
		$sql .= ' AND pg.admin_id = \'' . $_SESSION['admin_id'] . '\'';
	}
	else {
		if ($package_id == 0 && 0 < $seller_id) {
			$sql .= ' AND pg.admin_id = \'' . $_SESSION['seller_id'] . '\'';
		}
	}

	$resource = $GLOBALS['db']->query($sql);

	if (!$resource) {
		return array();
	}

	$row = array();
	$good_product_str = '';

	foreach ($resource as $_row) {
		$_row['goods_thumb'] = get_image_path($_row['goods_thumb']);
		$_row['goodsweight'] = $_row['goods_weight'];

		if (0 < $_row['product_id']) {
			$good_product_str .= ',' . $_row['goods_id'];
			$_row['g_p'] = $_row['goods_id'] . '_' . $_row['product_id'];
		}
		else {
			$_row['g_p'] = $_row['goods_id'];
		}

		$_row['url'] = build_uri('goods', array('gid' => $_row['goods_id']), $_row['goods_name']);
		$_row['shop_price'] = price_format($_row['shop_price']);

		if ($type == 1) {
			$_row['products'] = get_good_products($_row['goods_id']);
		}

		$row[] = $_row;
	}

	$good_product_str = trim($good_product_str, ',');
	unset($resource);
	unset($_row);
	unset($sql);

	if ($good_product_str != '') {
		$sql = 'SELECT goods_attr_id, attr_value FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE goods_id IN (' . $good_product_str . ')');
		$result_goods_attr = $GLOBALS['db']->getAll($sql);
		$_goods_attr = array();

		foreach ($result_goods_attr as $value) {
			$_goods_attr[$value['goods_attr_id']] = $value['attr_value'];
		}
	}

	$format[0] = '%s[%s]--[%d]';
	$format[1] = '%s--[%d]';

	foreach ($row as $key => $value) {
		$row[$key]['goods_name_pack'] = $value['goods_name'];

		if ($value['goods_attr'] != '') {
			$goods_attr_array = explode('|', $value['goods_attr']);
			$goods_attr = array();

			foreach ($goods_attr_array as $_attr) {
				$goods_attr[] = $_goods_attr[$_attr];
			}

			$row[$key]['goods_name'] = sprintf($format[0], $value['goods_name'], implode('，', $goods_attr), $value['goods_number']);
		}
		else {
			$row[$key]['goods_name'] = sprintf($format[1], $value['goods_name'], $value['goods_number']);
		}
	}

	return $row;
}

function get_good_products($goods_id, $conditions = '')
{
	if (empty($goods_id)) {
		return array();
	}

	switch (gettype($goods_id)) {
	case 'integer':
		$_goods_id = 'goods_id = \'' . intval($goods_id) . '\'';
		break;

	case 'string':
	case 'array':
		$_goods_id = db_create_in($goods_id, 'goods_id');
		break;
	}

	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('products') . (' WHERE ' . $_goods_id . ' ' . $conditions);
	$result_products = $GLOBALS['db']->getAll($sql);
	$sql = 'SELECT goods_attr_id, attr_value FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE ' . $_goods_id);
	$result_goods_attr = $GLOBALS['db']->getAll($sql);
	$_goods_attr = array();

	foreach ($result_goods_attr as $value) {
		$_goods_attr[$value['goods_attr_id']] = $value['attr_value'];
	}

	foreach ($result_products as $key => $value) {
		$goods_attr_array = explode('|', $value['goods_attr']);

		if (is_array($goods_attr_array)) {
			$goods_attr = array();

			foreach ($goods_attr_array as $_attr) {
				$goods_attr[] = $_goods_attr[$_attr];
			}

			$goods_attr_str = implode('，', $goods_attr);
		}

		$result_products[$key]['goods_attr_str'] = $goods_attr_str;
	}

	return $result_products;
}

function get_good_products_select($goods_id)
{
	$return_array = array();
	$products = get_good_products($goods_id);

	if (empty($products)) {
		return $return_array;
	}

	foreach ($products as $value) {
		$return_array[$value['product_id']] = $value['goods_attr_str'];
	}

	return $return_array;
}

function get_specifications_list($goods_id, $conditions = '')
{
	$sql = "SELECT ga.goods_attr_id, ga.attr_id, ga.attr_value, a.attr_name\r\n            FROM " . $GLOBALS['ecs']->table('goods_attr') . ' AS ga, ' . $GLOBALS['ecs']->table('attribute') . (" AS a\r\n            WHERE ga.attr_id = a.attr_id\r\n            AND ga.goods_id = '" . $goods_id . "'\r\n            " . $conditions);
	$result = $GLOBALS['db']->getAll($sql);
	$return_array = array();

	foreach ($result as $value) {
		$return_array[$value['goods_attr_id']] = $value;
	}

	return $return_array;
}

function get_class_nav($cat_id)
{
	$sql = 'select cat_id,cat_name,parent_id from ' . $GLOBALS['ecs']->table('category') . (' where cat_id = \'' . $cat_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $key => $row) {
		$arr[$key]['cat_id'] = $row['cat_id'];
		$arr[$key]['cat_name'] = $row['cat_name'];
		$arr[$key]['parent_id'] = $row['parent_id'];
		$arr['catId'] .= $row['cat_id'] . ',';
		$arr[$key]['child'] = get_parent_child($row['cat_id']);

		if (empty($arr[$key]['child']['catId'])) {
			$arr['catId'] = $arr['catId'];
		}
		else {
			$arr['catId'] .= $arr[$key]['child']['catId'];
		}
	}

	return $arr;
}

function get_parent_child($parent_id = 0)
{
	$sql = 'select cat_id,cat_name,parent_id from ' . $GLOBALS['ecs']->table('category') . (' where parent_id = \'' . $parent_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $key => $row) {
		$arr[$key]['cat_id'] = $row['cat_id'];
		$arr[$key]['cat_name'] = $row['cat_name'];
		$arr[$key]['parent_id'] = $row['parent_id'];
		$arr['catId'] .= $row['cat_id'] . ',';
		$arr[$key]['child'] = get_parent_child($row['cat_id']);
		$arr['catId'] .= $arr[$key]['child']['catId'];
	}

	return $arr;
}

function get_goodsCat_num($cat_id, $goods_ids = array(), $ruCat = '')
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('goods_cat') . ' AS gc left join ' . $GLOBALS['ecs']->table('goods') . (' as g on gc.goods_id = g.goods_id WHERE g.is_delete = 0 and gc.cat_id in(' . $cat_id . ')') . $ruCat;
	$cat_goods = $GLOBALS['db']->getAll($sql);

	foreach ($cat_goods as $key => $val) {
		if (in_array($val['goods_id'], $goods_ids)) {
			unset($cat_goods[$key]);
		}
	}

	return count($cat_goods);
}

function get_purchasing_goods_info($goods_id = 0)
{
	$sql = 'SELECT is_xiangou,xiangou_num, xiangou_start_date, xiangou_end_date FROM ' . $GLOBALS['ecs']->table('goods') . ('WHERE goods_id = \'' . $goods_id . '\' LIMIT 1');
	return $GLOBALS['db']->getRow($sql);
}

function get_for_purchasing_goods($start_date = 0, $end_date = 0, $goods_id = 0, $user_id = 0, $extension_code = '')
{
	$where = '';

	if (!empty($extension_code)) {
		$where = ' AND oi.extension_code = \'' . $extension_code . '\'';
	}

	$where .= ' AND oi.order_status <> ' . OS_CANCELED;
	$sql = 'SELECT og.goods_number FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og, ' . $GLOBALS['ecs']->table('order_info') . ' as oi ' . 'WHERE oi.order_id = og.order_id AND oi.user_id = ' . $user_id . ' AND og.goods_id = \'' . $goods_id . '\' AND oi.add_time > \'' . $start_date . '\' AND oi.add_time < \'' . $end_date . '\'' . $where;
	$res = $GLOBALS['db']->getAll($sql);
	$goods_number = 0;

	foreach ($res as $row) {
		$goods_number += $row['goods_number'];
	}

	return array('goods_number' => $goods_number);
}

function get_fine_store_category($options, $web_type, $array_type = 0, $ru_id)
{
	$cat_array = array();
	if ($web_type == 'admin' || $web_type == 'goodsInfo') {
		$sql = 'select cat_id, user_id from ' . $GLOBALS['ecs']->table('merchants_category') . ' where 1';
		$store_cat = $GLOBALS['db']->getAll($sql);

		foreach ($store_cat as $row) {
			$cat_array[$row['cat_id']]['cat_id'] = $row['cat_id'];
			$cat_array[$row['cat_id']]['user_id'] = $row['user_id'];
		}
	}

	if ($web_type == 'admin') {
		if ($cat_array) {
			if ($array_type == 0) {
				$options = array_diff_key($options, $cat_array);
			}
			else {
				$options = array_intersect_key($options, $cat_array);
			}
		}

		return $options;
	}
	else {
		if ($web_type == 'goodsInfo' && $ru_id == 0) {
			$options = array_diff_key($options, $cat_array);
			return $options;
		}
		else {
			return $options;
		}
	}
}

function cate_history($size, $page, $sort, $order, $warehouse_id = 0, $area_id = 0, $ship = 0, $self = 0)
{
	$str = '';

	if (!empty($_COOKIE['ECS']['list_history'])) {
		$where = db_create_in($_COOKIE['ECS']['list_history'], 'g.goods_id');

		if ($self == 1) {
			$where .= ' AND (g.user_id = 0 or msi.self_run = 1) ';
		}

		if ($ship == 1) {
			$where .= ' AND g.is_shipping = 1 ';
		}

		$leftJoin = '';
		$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi on msi.user_id = g.user_id ';

		if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
			$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
			$where .= ' and lag.region_id = \'' . $area_id . '\' ';
		}

		if ($sort == 'last_update') {
			$sort = 'g.last_update';
		}

		$sql = 'SELECT b.brand_name,g.is_shipping, g.goods_sn, g.brand_id, g.goods_id, g.goods_name, g.user_id, g.goods_thumb,g.sales_volume, g.user_id, msi.self_run, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price ' . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' as g ' . ' left join ' . $GLOBALS['ecs']->table('brand') . ' as b' . ' on g.brand_id = b.brand_id ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . (' WHERE ' . $where . ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 group by g.goods_id  ORDER BY ' . $sort . ' ' . $order);
		$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
		$arr = array();

		foreach ($res as $row) {
			if (0 < $row['promote_price']) {
				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			}
			else {
				$promote_price = 0;
			}

			$price_other = array('market_price' => $row['market_price'], 'org_price' => $row['org_price'], 'shop_price' => $row['shop_price'], 'promote_price' => $promote_price);
			$price_info = get_goods_one_attr_price($row['goods_id'], $warehouse_id, $area_id, $price_other);
			$row = !empty($row) ? array_merge($row, $price_info) : $row;
			$promote_price = $row['promote_price'];
			$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
			$arr[$row['goods_id']]['goods_sn'] = $row['goods_sn'];
			$arr[$row['goods_id']]['sales_volume'] = $row['sales_volume'];
			$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
			$arr[$row['goods_id']]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
			$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_thumb']);
			$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
			$arr[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
			$arr[$row['goods_id']]['brand_name'] = $row['brand_name'];
			$arr[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
			$arr[$row['goods_id']]['brand_url'] = build_uri('brand', array('bid' => $row['brand_id']), $row['brand_name']);
			$sql = 'select * from ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' where ru_id=\'' . $row['user_id'] . '\'';
			$basic_info = $GLOBALS['db']->getRow($sql);
			$arr[$row['goods_id']]['kf_type'] = $basic_info['kf_type'];
			$arr[$row['goods_id']]['kf_ww'] = $basic_info['kf_ww'];
			$arr[$row['goods_id']]['kf_qq'] = $basic_info['kf_qq'];
			$goods_id = $row['goods_id'];
			$count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' where id_value =\'' . $goods_id . '\' AND status = 1 AND parent_id = 0'));
			$arr[$row['goods_id']]['review_count'] = $count;

			if (0 < $row['user_id']) {
				$sql = 'select shoprz_brandName, shopNameSuffix from ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' where user_id=\'' . $row['user_id'] . '\'';
				$shop_info = $GLOBALS['db']->getRow($sql);
				$arr[$row['goods_id']]['rz_shopName'] = $shop_info['shoprz_brandName'] . $shop_info['shopNameSuffix'];
			}
			else {
				$sql = 'select shop_name from ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' where ru_id=\'' . $row['user_id'] . '\'';
				$shop_name = $GLOBALS['db']->getOne($sql);
				$arr[$row['goods_id']]['rz_shopName'] = $shop_name;
			}

			$arr[$row['goods_id']]['user_id'] = $row['user_id'];
			$arr[$row['goods_id']]['is_shipping'] = $row['is_shipping'];
			$arr[$row['goods_id']]['self_run'] = $row['self_run'];
			$arr[$row['goods_id']]['store_url'] = build_uri('merchants_store', array('urid' => $row['user_id']), $arr[$row['goods_id']]['rz_shopName']);
			$arr[$row['goods_id']]['count'] = selled_count($row['goods_id']);
			$mc_all = ments_count_all($row['goods_id']);
			$mc_one = ments_count_rank_num($row['goods_id'], 1);
			$mc_two = ments_count_rank_num($row['goods_id'], 2);
			$mc_three = ments_count_rank_num($row['goods_id'], 3);
			$mc_four = ments_count_rank_num($row['goods_id'], 4);
			$mc_five = ments_count_rank_num($row['goods_id'], 5);
			$arr[$row['goods_id']]['zconments'] = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);
			$arr[$row['goods_id']]['is_collect'] = get_collect_user_goods($row['goods_id']);
			$arr[$row['goods_id']]['pictures'] = get_goods_gallery($row['goods_id']);
			$shop_information = get_shop_name($row['user_id']);
			$arr[$row['goods_id']]['is_IM'] = $shop_information['is_IM'];

			if ($row['user_id'] == 0) {
				if ($GLOBALS['db']->getOne('SELECT kf_im_switch FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . 'WHERE ru_id = 0', true)) {
					$arr[$row['goods_id']]['is_dsc'] = true;
				}
				else {
					$arr[$row['goods_id']]['is_dsc'] = false;
				}
			}
			else {
				$arr[$row['goods_id']]['is_dsc'] = false;
			}
		}
	}

	return $arr;
}

function cate_history_count()
{
	$str = '';

	if (!empty($_COOKIE['ECS']['list_history'])) {
		$where = db_create_in($_COOKIE['ECS']['list_history'], 'g.goods_id');
		$sql = 'SELECT b.brand_name, g.brand_id, g.goods_id, g.goods_name, g.goods_thumb, g.shop_price, g.promote_price, g.is_promote FROM ' . $GLOBALS['ecs']->table('goods') . ' as g left join ' . $GLOBALS['ecs']->table('brand') . ' as b' . ' on g.brand_id = b.brand_id ' . (' WHERE ' . $where . ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0');
		$res = count($GLOBALS['db']->getAll($sql));
	}

	return $res;
}

function cause_list($cause_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true)
{
	static $res;

	if ($res === NULL) {
		$sql = 'SELECT c.cause_id, c.cause_name, c.sort_order, c.is_show ,c.parent_id , COUNT(s.cause_id) AS has_children ' . 'FROM ' . $GLOBALS['ecs']->table('return_cause') . ' AS c ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('return_cause') . ' AS s ON s.parent_id=c.cause_id ' . 'GROUP BY c.cause_id ' . 'ORDER BY c.parent_id, c.sort_order ASC';
		$res = $GLOBALS['db']->getAll($sql);

		if (count($res) <= 1000) {
			write_static_cache('cause_pid_releate', $res);
		}
	}

	if (empty($res) == true) {
		return $re_type ? '' : array();
	}

	$options = cause_options($cause_id, $res);
	$children_level = 99999;

	if ($is_show_all == false) {
		foreach ($options as $key => $val) {
			if ($children_level < $val['level']) {
				unset($options[$key]);
			}
			else if ($val['is_show'] == 0) {
				unset($options[$key]);

				if ($val['level'] < $children_level) {
					$children_level = $val['level'];
				}
			}
			else {
				$children_level = 99999;
			}
		}
	}

	if (0 < $level) {
		if ($cause_id == 0) {
			$end_level = $level;
		}
		else {
			$first_item = reset($options);
			$end_level = $first_item['level'] + $level;
		}

		foreach ($options as $key => $val) {
			if ($end_level <= $val['level']) {
				unset($options[$key]);
			}
		}
	}

	if ($re_type == true) {
		$select = '';

		foreach ($options as $var) {
			$select .= '<option value="' . $var['cause_id'] . '" ';
			$select .= $selected == $var['cause_id'] ? 'selected=\'ture\'' : '';
			$select .= '>';

			if (0 < $var['level']) {
				$select .= str_repeat('&nbsp;', $var['level'] * 4);
			}

			$select .= htmlspecialchars(addslashes($var['cause_name']), ENT_QUOTES) . '</option>';
		}

		return $select;
	}
	else {
		foreach ($options as $key => $value) {
			$options[$key]['url'] = build_uri('reutrn_cause', array('cid' => $value['cause_id']), $value['cause_name']);
		}

		return $options;
	}
}

function get_parent_cause()
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('return_cause') . ' WHERE parent_id = 0  AND is_show = 1  ORDER BY sort_order';
	$result = $GLOBALS['db']->getAll($sql);

	if (is_array($result)) {
		$select = '';

		foreach ($result as $var) {
			$select .= '<option value="' . $var['cause_id'] . '" ';
			$select .= $selected == $var['cause_id'] ? 'selected=\'ture\'' : '';
			$select .= '>';

			if (0 < $var['level']) {
				$select .= str_repeat('&nbsp;', $var['level'] * 4);
			}

			$select .= htmlspecialchars(addslashes($var['cause_name']), ENT_QUOTES) . '</option>';
		}

		return $select;
	}
	else {
		return array();
	}
}

function cause_options($spec_cat_id, $arr)
{
	static $cat_options = array();

	if (isset($cat_options[$spec_cat_id])) {
		return $cat_options[$spec_cat_id];
	}

	if (!isset($cat_options[0])) {
		$level = $last_cat_id = 0;
		$options = $cat_id_array = $level_array = array();

		while (!empty($arr)) {
			foreach ($arr as $key => $value) {
				$cat_id = $value['cause_id'];
				if ($level == 0 && $last_cat_id == 0) {
					if (0 < $value['parent_id']) {
						break;
					}

					$options[$cat_id] = $value;
					$options[$cat_id]['level'] = $level;
					$options[$cat_id]['id'] = $cat_id;
					$options[$cat_id]['name'] = $value['cause_name'];
					unset($arr[$key]);

					if ($value['has_children'] == 0) {
						continue;
					}

					$last_cat_id = $cat_id;
					$cat_id_array = array($cat_id);
					$level_array[$last_cat_id] = ++$level;
					continue;
				}

				if ($value['parent_id'] == $last_cat_id) {
					$options[$cat_id] = $value;
					$options[$cat_id]['level'] = $level;
					$options[$cat_id]['id'] = $cat_id;
					$options[$cat_id]['name'] = $value['cause_name'];
					unset($arr[$key]);

					if (0 < $value['has_children']) {
						if (end($cat_id_array) != $last_cat_id) {
							$cat_id_array[] = $last_cat_id;
						}

						$last_cat_id = $cat_id;
						$cat_id_array[] = $cat_id;
						$level_array[$last_cat_id] = ++$level;
					}
				}
				else if ($last_cat_id < $value['parent_id']) {
					break;
				}
			}

			$count = count($cat_id_array);

			if (1 < $count) {
				$last_cat_id = array_pop($cat_id_array);
			}
			else if ($count == 1) {
				if ($last_cat_id != end($cat_id_array)) {
					$last_cat_id = end($cat_id_array);
				}
				else {
					$level = 0;
					$last_cat_id = 0;
					$cat_id_array = array();
					continue;
				}
			}

			if ($last_cat_id && isset($level_array[$last_cat_id])) {
				$level = $level_array[$last_cat_id];
			}
			else {
				$level = 0;
			}
		}

		if (count($options) <= 2000) {
		}

		$cat_options[0] = $options;
	}
	else {
		$options = $cat_options[0];
	}

	if (!$spec_cat_id) {
		return $options;
	}
	else {
		if (empty($options[$spec_cat_id])) {
			return array();
		}

		$spec_cat_id_level = $options[$spec_cat_id]['level'];

		foreach ($options as $key => $value) {
			if ($key != $spec_cat_id) {
				unset($options[$key]);
			}
			else {
				break;
			}
		}

		$spec_cat_id_array = array();

		foreach ($options as $key => $value) {
			if ($spec_cat_id_level == $value['level'] && $value['cause_id'] != $spec_cat_id || $value['level'] < $spec_cat_id_level) {
				break;
			}
			else {
				$spec_cat_id_array[$key] = $value;
			}
		}

		$cat_options[$spec_cat_id] = $spec_cat_id_array;
		return $spec_cat_id_array;
	}
}

function return_action($ret_id, $return_status, $refound_status, $note = '', $username = NULL, $place = 0)
{
	if (is_null($username)) {
		$username = $_SESSION['admin_name'];
	}

	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('return_action') . ' (ret_id, action_user, return_status, refound_status, action_place, action_note, log_time) ' . 'SELECT ' . ('ret_id, \'' . $username . '\', \'' . $return_status . '\', \'' . $refound_status . '\', \'' . $place . '\', \'' . $note . '\', \'') . gmtime() . '\' ' . 'FROM ' . $GLOBALS['ecs']->table('order_return') . (' WHERE ret_id = \'' . $ret_id . '\'');
	$GLOBALS['db']->query($sql);
}

function get_single($goods_id, $order_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('single') . ('WHERE goods_id=\'' . $goods_id . '\' AND order_id=\'' . $order_id . '\' AND is_audit=1');
	$singles = $GLOBALS['db']->getRow($sql);
	$imaegs = array();

	foreach ($singles as $k => $v) {
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('goods_gallery') . (' WHERE single_id=\'' . $singles['single_id'] . '\'');
		$images = $GLOBALS['db']->getAll($sql);
	}

	return $images;
}

function get_single_detaile($goods_id, $order_id = 0)
{
	if (empty($order_id)) {
		$order_where = '';
	}
	else {
		$order_where = ' AND order_id=\'' . $order_id . '\' ';
	}

	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('single') . ('WHERE goods_id=\'' . $goods_id . '\'' . $order_where . ' AND is_audit=1 ORDER BY addtime');
	$singles = $GLOBALS['db']->getRow($sql);
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' WHERE single_id=\'' . $singles['single_id'] . '\' ORDER BY add_time');
	$singles['comment_nums'] = $GLOBALS['db']->getOne($sql);
	$singles['addtime'] = local_date('Y-m-d H:i:s', $singles['addtime']);
	return $singles;
}

function dimensional_array_sort($arr, $keys, $type = 'DESC')
{
	$keysvalue = $new_array = array();

	foreach ($arr as $k => $v) {
		$keysvalue[$k] = $v[$keys];
	}

	if ($type == 'ASC') {
		asort($keysvalue);
	}
	else {
		arsort($keysvalue);
	}

	reset($keysvalue);

	foreach ($keysvalue as $k => $v) {
		$new_array[$k] = $arr[$k];
	}

	return $new_array;
}

function get_store_shop_list($libType = 0, $keywords = '', $count = 0, $size = 15, $page = 1, $sort = 'shop_id', $order = 'DESC', $warehouse_id = 0, $area_id = 0, $store_province = 0, $store_city = 0, $store_district = 0, $store_user = '')
{
	$id = '"';

	if ($keywords) {
		$id .= 'keywords-' . $keywords . '|';
	}

	if ($warehouse_id) {
		$id .= 'warehouse_id-' . $warehouse_id . '|';
	}

	if ($area_id) {
		$id .= 'area_id-' . $area_id . '|';
	}

	if ($store_province) {
		$id .= 'store_province-' . $store_province . '|';
	}

	if ($store_city) {
		$id .= 'store_city-' . $store_city . '|';
	}

	if ($store_district) {
		$id .= 'store_district-' . $store_district . '|';
	}

	if ($sort) {
		$id .= 'sort-' . $sort . '|';
	}

	if ($order) {
		$id .= 'order-' . $order . '|';
	}

	$substr = substr($id, -1);

	if ($substr == '|') {
		$id = substr($id, 0, -1);
	}

	$id .= '"';
	$whereShop = ' 1 ';
	$where = '1';
	$keywords = mysql_like_quote(trim($keywords));

	if (!empty($keywords)) {
		$where .= ' AND (shoprz_brandName LIKE \'%' . $keywords . '%\' OR shopNameSuffix LIKE \'%' . $keywords . '%\' OR rz_shopName LIKE \'%' . $keywords . '%\') ';
		$user_list = '';
		$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' where ' . $where;
		$shop_list = $GLOBALS['db']->getAll($sql);

		if ($shop_list) {
			foreach ($shop_list as $key => $row) {
				$user_list .= $row['user_id'] . ',';
			}

			$user_list = substr($user_list, 0, -1);
		}
		else {
			$reviewGodds = '';

			if ($GLOBALS['_CFG']['review_goods'] == 1) {
				$reviewGodds = ' AND review_status > 2 ';
			}

			$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' where goods_name LIKE \'%' . $keywords . '%\' ' . $reviewGodds . ' group by goods_id');
			$goodsUlist = $GLOBALS['db']->getAll($sql);

			if ($goodsUlist) {
				foreach ($goodsUlist as $key => $row) {
					$user_list .= $row['user_id'] . ',';
				}

				$user_list = substr($user_list, 0, -1);
			}
		}

		if (!empty($user_list)) {
			$whereShop .= ' AND msi.user_id in(' . $user_list . ')';
		}
		else {
			$whereShop .= ' AND msi.user_id in(0)';
		}
	}
	else if ($store_user) {
		$whereShop .= ' AND msi.user_id in(' . $store_user . ')';
	}

	$where_table = '';
	$select = '';

	if ($sort == 'sales_volume') {
		$select .= ', (SELECT SUM(og.goods_number) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi, ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . ' WHERE oi.order_id = og.order_id AND og.ru_id = msi.user_id ' . ' AND (oi.order_status = \'' . OS_CONFIRMED . '\' OR  oi.order_status = \'' . OS_SPLITED . '\' OR oi.order_status = \'' . OS_SPLITING_PART . '\') ' . ' AND (oi.pay_status  = \'' . PS_PAYING . '\' OR  oi.pay_status  = \'' . PS_PAYED . '\')) AS sales_volume ';
	}
	else if ($sort == 'goods_number') {
		$select .= ', ((SELECT SUM(g.goods_number) FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ' WHERE g.user_id = msi.user_id AND g.review_status > 2)) AS goods_number ';
	}

	if (0 < $store_province || 0 < $store_city || 0 < $store_district) {
		$where_table .= ', ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS ssfo ';
		$whereShop .= 'AND msi.user_id = ssfo.ru_id ';
	}

	if (0 < $store_province) {
		$whereShop .= 'AND ssfo.province = \'' . $store_province . '\' ';
	}

	if (0 < $store_city) {
		$whereShop .= 'AND ssfo.city = \'' . $store_city . '\' ';
	}

	if (0 < $store_district) {
		$whereShop .= 'AND ssfo.district = \'' . $store_district . '\' ';
	}

	if ($libType == 0) {
		$whereShop .= 'AND msi.is_street = 1 ';
	}

	$sql = 'SELECT msi.shop_id, msi.user_id, msi.shoprz_brandName, msi.shopNameSuffix ' . $select . ' FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_grade') . ' AS mg ON mg.ru_id = msi.user_id ' . $where_table . (' where ' . $whereShop) . (' AND msi.merchants_audit = 1 ORDER BY ' . $sort . ' ' . $order . ' ');
	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
	$arr = array();

	foreach ($res as $row) {
		$arr[$row['shop_id']]['shop_id'] = $row['shop_id'];
		$arr[$row['shop_id']]['shoprz_brandName'] = $row['shoprz_brandName'];
		$arr[$row['shop_id']]['shopNameSuffix'] = $row['shopNameSuffix'];
		$arr[$row['shop_id']]['shop_name'] = get_shop_name($row['user_id'], 3);
		$arr[$row['shop_id']]['shopName'] = get_shop_name($row['user_id'], 1);
		$arr[$row['shop_id']]['brand_list'] = get_shop_brand_list($row['user_id']);
		$arr[$row['shop_id']]['address'] = get_shop_address_info($row['user_id']);
		$arr[$row['shop_id']]['sales_volume'] = !empty($row['sales_volume']) ? $row['sales_volume'] : 0;
		$shop_info = get_shop_info_content($row['user_id']);
		$arr[$row['shop_id']]['shop_logo'] = str_replace('../', '', $shop_info['shop_logo']);
		$arr[$row['shop_id']]['logo_thumb'] = str_replace('../', '', $shop_info['logo_thumb']);
		$arr[$row['shop_id']]['street_thumb'] = $shop_info['street_thumb'];
		$arr[$row['shop_id']]['brand_thumb'] = $shop_info['brand_thumb'];
		$arr[$row['shop_id']]['street_desc'] = $shop_info['street_desc'];
		$arr[$row['shop_id']]['merch_cmt'] = get_merchants_goods_comment($row['user_id']);
		$arr[$row['shop_id']]['shopNameSuffix'] = $row['shopNameSuffix'];
		$arr[$row['shop_id']]['ru_id'] = $row['user_id'];
		$arr[$row['shop_id']]['shop_url'] = build_uri('merchants_store', array('cid' => 0, 'urid' => $row['user_id']), $arr[$row['shop_id']]['shop_name']);
		$arr[$row['shop_id']]['goods_count'] = get_shop_goods_count_list($row['user_id'], $warehouse_id, $area_id);
		$arr[$row['shop_id']]['goods_list'] = get_shop_goods_count_list($row['user_id'], $warehouse_id, $area_id, 1);
	}

	$result = array('shop_list' => $arr, 'pager' => $pager);
	return $result;
}

function get_store_shop_count($keywords = '', $sort = 'shop_id', $store_province = 0, $store_city = 0, $store_district = 0, $store_user = '')
{
	$whereShop = ' 1 ';
	$where = '1';
	$keywords = mysql_like_quote(trim($keywords));

	if (!empty($keywords)) {
		$where .= ' AND (shoprz_brandName LIKE \'%' . $keywords . '%\' OR shopNameSuffix LIKE \'%' . $keywords . '%\') ';
		$user_list = '';
		$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' where ' . $where;
		$shop_list = $GLOBALS['db']->getAll($sql);

		if ($shop_list) {
			foreach ($shop_list as $key => $row) {
				$user_list .= $row['user_id'] . ',';
			}

			$user_list = substr($user_list, 0, -1);
		}
		else {
			$reviewGodds = '';

			if ($GLOBALS['_CFG']['review_goods'] == 1) {
				$reviewGodds = ' AND review_status > 2 ';
			}

			$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' where goods_name LIKE \'%' . $keywords . '%\' ' . $reviewGodds . ' group by goods_id');
			$goodsUlist = $GLOBALS['db']->getAll($sql);

			if ($goodsUlist) {
				foreach ($goodsUlist as $key => $row) {
					$user_list .= $row['user_id'] . ',';
				}

				$user_list = substr($user_list, 0, -1);
			}
		}

		if (!empty($user_list)) {
			$whereShop .= ' AND msi.user_id in(' . $user_list . ')';
		}
		else {
			$whereShop .= ' AND msi.user_id in(0)';
		}
	}
	else if ($store_user) {
		$whereShop .= ' AND msi.user_id in(' . $store_user . ')';
	}

	$where_table = '';
	$select = '';

	if ($sort == 'sales_volume') {
		$select .= ', (SELECT SUM(og.goods_number) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi, ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . ' WHERE oi.order_id = og.order_id AND og.ru_id = msi.user_id ' . ' AND (oi.order_status = \'' . OS_CONFIRMED . '\' OR  oi.order_status = \'' . OS_SPLITED . '\' OR oi.order_status = \'' . OS_SPLITING_PART . '\') ' . ' AND (oi.pay_status  = \'' . PS_PAYING . '\' OR  oi.pay_status  = \'' . PS_PAYED . '\')) AS sales_volume ';
	}
	else if ($sort == 'goods_number') {
		$select .= ', ((SELECT SUM(g.goods_number) FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ' WHERE g.user_id = msi.user_id AND g.review_status > 2)) AS goods_number ';
	}

	if (0 < $store_province || 0 < $store_city || 0 < $store_district) {
		$where_table .= ', ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS ssfo ';
		$whereShop .= 'AND msi.user_id = ssfo.ru_id ';
	}

	if (0 < $store_province) {
		$whereShop .= 'AND ssfo.province = \'' . $store_province . '\' ';
	}

	if (0 < $store_city) {
		$whereShop .= 'AND ssfo.city = \'' . $store_city . '\' ';
	}

	if (0 < $store_district) {
		$whereShop .= 'AND ssfo.district = \'' . $store_district . '\' ';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS msi' . $where_table . (' where ' . $whereShop) . ' AND msi.is_street = 1 AND msi.merchants_audit = 1';
	return $GLOBALS['db']->getOne($sql);
}

function get_store_shop_goods_list($keywords = '', $size, $page, $sort, $order, $warehouse_id, $area_id)
{
	$whereGodds = '1';
	$where = '1';
	$keywords = mysql_like_quote(trim($keywords));

	if (!empty($keywords)) {
		$where .= ' AND (shoprz_brandName LIKE \'%' . $keywords . '%\' OR shopNameSuffix LIKE \'%' . $keywords . '%\') ';
		$user_list = '';
		$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' where ' . $where;
		$shop_list = $GLOBALS['db']->getAll($sql);

		if ($shop_list) {
			foreach ($shop_list as $key => $row) {
				$user_list .= $row['user_id'] . ',';
			}

			$user_list = substr($user_list, 0, -1);
		}
		else {
			$reviewGodds = '';

			if ($GLOBALS['_CFG']['review_goods'] == 1) {
				$reviewGodds = ' AND review_status > 2 ';
			}

			$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' where goods_name LIKE \'%' . $keywords . '%\' ' . $reviewGodds . ' group by goods_id');
			$goodsUlist = $GLOBALS['db']->getAll($sql);

			if ($goodsUlist) {
				foreach ($goodsUlist as $key => $row) {
					if (0 < $row['user_id']) {
						$user_list .= $row['user_id'] . ',';
					}
				}
			}
		}

		if (!empty($user_list)) {
			if (strpos($user_list, ',') !== false) {
				$user_list = substr($user_list, 0, -1);
			}

			$whereGodds .= ' AND g.user_id in(' . $user_list . ')';
		}
	}
	else {
		$whereGodds .= ' AND g.user_id > 0 ';
	}

	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi on msi.user_id = g.user_id ';

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$whereGodds .= ' AND lag.region_id = \'' . $area_id . '\' ';
	}

	$whereGodds .= ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ';

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$whereGodds .= ' AND g.review_status > 2 ';
	}

	$select = 'g.goods_id, g.goods_thumb,g.is_shipping, g.goods_name, g.user_id, g.promote_start_date, g.promote_end_date, g.market_price, msi.self_run, ';
	$select .= 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ';
	$select .= 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price';
	$leftJoin .= 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ');
	$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' as g ' . $leftJoin . (' where ' . $whereGodds . ' ORDER BY ' . $sort . ' ' . $order);
	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

	foreach ($res as $row) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
		$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$arr[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
		$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_thumb']);
		$arr[$row['goods_id']]['goods_url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$arr[$row['goods_id']]['order_count'] = selled_count($row['goods_id']);
		$arr[$row['goods_id']]['is_shipping'] = $row['is_shipping'];
		$arr[$row['goods_id']]['self_run'] = $row['self_run'];
		$basic_info = get_shop_info_content($row['user_id']);
		$arr[$row['goods_id']]['kf_type'] = $basic_info['kf_type'];
		$arr[$row['goods_id']]['kf_ww'] = $basic_info['kf_ww'];
		$arr[$row['goods_id']]['kf_qq'] = $basic_info['kf_qq'];
		$arr[$row['goods_id']]['shop_name'] = get_shop_name($row['user_id'], 1);
		$arr[$row['goods_id']]['shop_url'] = build_uri('merchants_store', array('cid' => 0, 'urid' => $row['user_id']), $arr[$key]['shop_name']);
		$cmt_count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' where id_value =\'' . $row['goods_id'] . '\' AND status = 1 AND parent_id = 0');
		$arr[$row['goods_id']]['cmt_count'] = $cmt_count;
		$arr[$row['goods_id']]['brand_list'] = get_shop_brand_list($row['user_id']);
		$arr[$row['goods_id']]['is_collect'] = get_collect_user_goods($row['goods_id']);
	}

	return $arr;
}

function get_store_shop_goods_count($keywords, $sort)
{
	$whereGodds = '1';
	$where = '1';
	$keywords = mysql_like_quote(trim($keywords));

	if (!empty($keywords)) {
		$where .= ' AND (shoprz_brandName LIKE \'%' . $keywords . '%\' OR shopNameSuffix LIKE \'%' . $keywords . '%\') ';
		$user_list = '';
		$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' where ' . $where;
		$shop_list = $GLOBALS['db']->getAll($sql);

		if ($shop_list) {
			foreach ($shop_list as $key => $row) {
				$user_list .= $row['user_id'] . ',';
			}

			$user_list = substr($user_list, 0, -1);
		}
		else {
			$reviewGodds = '';

			if ($GLOBALS['_CFG']['review_goods'] == 1) {
				$reviewGodds = ' AND review_status > 2 ';
			}

			$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' where goods_name LIKE \'%' . $keywords . '%\' ' . $reviewGodds . ' group by goods_id');
			$goodsUlist = $GLOBALS['db']->getAll($sql);

			if ($goodsUlist) {
				foreach ($goodsUlist as $key => $row) {
					if (0 < $row['user_id']) {
						$user_list .= $row['user_id'] . ',';
					}
				}
			}
		}

		if (!empty($user_list)) {
			if (strpos($user_list, ',') !== false) {
				$user_list = substr($user_list, 0, -1);
			}

			$whereGodds .= ' AND g.user_id in(' . $user_list . ')';
		}
	}
	else {
		$whereGodds .= ' AND g.user_id > 0 ';
	}

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$whereGodds .= ' AND g.review_status > 2 ';
	}

	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('goods') . ' as g ' . (' where ' . $whereGodds . ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0');
	$res = $GLOBALS['db']->getOne($sql);
	return $res;
}

function get_shop_brand_list($user_id = 0)
{
	$sql = 'SELECT bid as brand_id, brandName as brand_name FROM ' . $GLOBALS['ecs']->table('merchants_shop_brand') . (' where user_id = \'' . $user_id . '\' AND audit_status = 1 order by bid ASC');
	return $res = $GLOBALS['db']->getAll($sql);
}

function get_shop_address_info($user_id = 0)
{
	$res = get_shop_info_content($user_id);
	$province = get_shop_address($res['province']);
	$city = get_shop_address($res['city']);
	$region = $province . str_repeat('&nbsp;', 2) . $city;
	return $region;
}

function get_shop_address($region, $type = 0)
{
	if ($type == 1) {
		$region = str_replace(array('省', '市'), '', $region);
		$select = 'region_id';
		$where = 'region_name = \'' . $region . '\'';
	}
	else {
		$select = 'region_name';
		$where = 'region_id = \'' . $region . '\'';
	}

	$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table('region') . ' where ' . $where;
	return $GLOBALS['db']->getOne($sql);
}

function get_shop_info_content($user_id = 0)
{
	$sql = 'SELECT country, province, city, shop_logo, logo_thumb, street_thumb, brand_thumb, street_desc, kf_type, kf_ww, kf_qq FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' where ru_id = \'' . $user_id . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function get_shop_goods_count_list($user_id, $warehouse_id, $area_id, $type = 0, $isType = '', $show_type = 0)
{
	$leftJoin = '';
	$where = '1';

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$where .= ' AND g.review_status > 2 ';
	}

	if ($type == 1) {
		$arr = array();
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

		if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
			$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
			$where .= ' AND lag.region_id = \'' . $area_id . '\' ';
		}

		$leftJoin .= 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ');
		$select = 'g.goods_id, g.goods_thumb, g.goods_name, g.user_id, g.promote_start_date, g.promote_end_date, g.market_price, ';
		$select .= 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ';
		$select .= 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price';
	}
	else {
		$select = 'count(*)';
	}

	if ($isType == 'store_best') {
		$where .= ' AND g.store_best = 1';
		$where .= ' and g.user_id > ' . $user_id . ' ';
	}
	else {
		$where .= ' and g.user_id = \'' . $user_id . '\' ';
	}

	$where .= ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ';

	if ($type == 1) {
		if ($show_type == 1) {
			$limit = 'limit 0,6';
		}
		else {
			$limit = 'limit 0,5';
		}

		$where .= ' order by g.sort_order ASC ' . $limit;
	}

	$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' as g ' . $leftJoin . (' WHERE ' . $where . ' ');

	if ($type == 1) {
		$res = $GLOBALS['db']->getAll($sql);

		foreach ($res as $key => $row) {
			if (0 < $row['promote_price']) {
				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			}
			else {
				$promote_price = 0;
			}

			$price_other = array('market_price' => $row['market_price'], 'org_price' => $row['org_price'], 'shop_price' => $row['shop_price'], 'promote_price' => $promote_price);
			$price_info = get_goods_one_attr_price($row['goods_id'], $warehouse_id, $area_id, $price_other);
			$row = !empty($row) ? array_merge($row, $price_info) : $row;
			$promote_price = $row['promote_price'];
			$arr[$key]['market_price'] = price_format($row['market_price']);
			$arr[$key]['shop_price'] = price_format($row['shop_price']);
			$arr[$key]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
			$arr[$key]['goods_id'] = $row['goods_id'];
			$arr[$key]['goods_name'] = $row['goods_name'];
			$arr[$key]['goods_thumb'] = get_image_path($row['goods_thumb']);
			$arr[$key]['goods_url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
			$arr[$key]['order_count'] = selled_count($row['goods_id']);
			$basic_info = get_shop_info_content($row['user_id']);
			$arr[$key]['kf_type'] = $basic_info['kf_type'];
			$arr[$key]['kf_ww'] = $basic_info['kf_ww'];
			$arr[$key]['kf_qq'] = $basic_info['kf_qq'];
			$arr[$key]['shop_name'] = get_shop_name($row['user_id'], 1);
			$arr[$key]['shop_url'] = build_uri('merchants_store', array('cid' => 0, 'urid' => $row['user_id']), $arr[$key]['shop_name']);
			$cmt_count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' where id_value =\'' . $row['goods_id'] . '\' AND status = 1 AND parent_id = 0');
			$arr[$key]['cmt_count'] = $cmt_count;
		}

		return $arr;
	}
	else {
		return $GLOBALS['db']->getOne($sql);
	}
}

function get_shop_goods_cmt_list($user_id, $warehouse_id, $area_id, $price_min, $price_max, $page, $size, $sort, $order)
{
	$leftJoin = '';
	$where = '1';
	$where .= ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ';

	if (0 < $min) {
		$where .= ' AND IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) >= ' . $min . ' ';
	}

	if (0 < $max) {
		$where .= ' AND IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) <= ' . $max . ' ';
	}

	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$where .= ' AND lag.region_id = \'' . $area_id . '\' ';
	}

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$where .= ' AND g.review_status > 2 ';
	}

	$leftJoin .= 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ');
	$select = 'g.goods_id, g.goods_thumb, g.goods_name, g.user_id, g.promote_start_date, g.promote_end_date, g.market_price, ';
	$select .= 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ';
	$select .= 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price';

	if ($sort == 'last_update') {
		$sort = 'g.last_update';
	}

	$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' as g ' . $leftJoin . (' WHERE ' . $where . ' AND g.user_id = \'' . $user_id . '\'  ORDER BY ' . $sort . ' ' . $order);
	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

	foreach ($res as $row) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$price_other = array('market_price' => $row['market_price'], 'org_price' => $row['org_price'], 'shop_price' => $row['shop_price'], 'promote_price' => $promote_price);
		$price_info = get_goods_one_attr_price($row['goods_id'], $warehouse_id, $area_id, $price_other);
		$row = !empty($row) ? array_merge($row, $price_info) : $row;
		$promote_price = $row['promote_price'];
		$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
		$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$arr[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
		$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_thumb']);
		$arr[$row['goods_id']]['goods_url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$arr[$row['goods_id']]['order_count'] = selled_count($row['goods_id']);
		$basic_info = get_shop_info_content($row['user_id']);
		$arr[$row['goods_id']]['kf_type'] = $basic_info['kf_type'];
		$arr[$row['goods_id']]['kf_ww'] = $basic_info['kf_ww'];
		$arr[$row['goods_id']]['kf_qq'] = $basic_info['kf_qq'];
		$arr[$row['goods_id']]['shop_name'] = get_shop_name($row['user_id'], 1);
		$arr[$row['goods_id']]['shop_url'] = build_uri('merchants_store', array('cid' => 0, 'urid' => $row['user_id']), $arr[$key]['shop_name']);
		$cmt_count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' where id_value =\'' . $row['goods_id'] . '\' AND status = 1 AND parent_id = 0');
		$arr[$row['goods_id']]['cmt_count'] = $cmt_count;
		$arr[$row['goods_id']]['is_collect'] = get_collect_user_goods($row['goods_id']);
	}

	return $arr;
}

function get_shop_goods_cmt_count($user_id, $price_min, $price_max)
{
	$where = '';

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$where .= ' AND review_status > 2 ';
	}

	if (0 < $min) {
		$where .= ' AND IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) >= ' . $min . ' ';
	}

	if (0 < $max) {
		$where .= ' AND IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) <= ' . $max . ' ';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE user_id = \'' . $user_id . '\' AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 ') . $where;
	$res = $GLOBALS['db']->getOne($sql);
	return $res;
}

function get_seller_info_area()
{
	$sql = 'select province, city, district from ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' where ru_id = 0';
	return $GLOBALS['db']->getRow($sql);
}

function get_order_user_address_list($user_id)
{
	$sql = 'SELECT ua.*, ' . 'concat(IFNULL(p.region_name, \'\'), ' . '\'  \', IFNULL(t.region_name, \'\'), ' . '\'  \', IFNULL(d.region_name, \'\'), ' . ' \'  \', IFNULL(s.region_name, \'\')) AS region ' . 'FROM ' . $GLOBALS['ecs']->table('user_address') . ' AS ua ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS p ON ua.province = p.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS t ON ua.city = t.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS d ON ua.district = d.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS s ON ua.street = s.region_id ' . (' WHERE user_id = \'' . $user_id . '\' GROUP BY ua.address_id');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $row) {
		$arr[] = $row;
	}

	return $arr;
}

function get_user_bouns_new_list($user_id = 0, $page = 1, $type = 0, $pageFunc = '', $amount = 0)
{
	$day = local_getdate();
	$cur_date = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
	$before_date = local_mktime(0, 0, 0, $day['mon'], $day['mday'], $day['year']) - 2 * 24 * 3600;
	$useDate = ' AND b.use_start_date < ' . $cur_date . ' AND b.use_end_date > ' . $cur_date;

	if ($type == 0) {
		$uOrder = ' AND u.order_id = 0';
		$arrName = 'available_list';
	}
	else if ($type == 1) {
		$uOrder = ' AND u.order_id = 0';
		$useDate = ' AND b.use_start_date >= ' . $before_date . ' AND b.use_end_date > ' . $cur_date;
		$arrName = 'expire_list';
	}
	else if ($type == 2) {
		$uOrder = ' AND u.order_id > 0';
		$arrName = 'useup_list';
	}

	$record_count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('user_bonus') . ' as u,' . $GLOBALS['ecs']->table('bonus_type') . ' AS b' . ' WHERE u.bonus_type_id = b.type_id ' . $uOrder . (' AND u.user_id = \'' . $user_id . '\' ') . $useDate);
	$bouns_paper = '';
	$limit = '';

	if ($amount == 0) {
		$bouns = new Pager($record_count, $size, '', $user_id, 0, $page, $pageFunc, 1);
		$limit = $bouns->limit;
		$bouns_paper = $bouns->fpage(array(0, 4, 5, 6, 9));
	}

	$sql = 'SELECT  u.bonus_id, u.bonus_sn, u.order_id, u.bind_time, b.type_name, b.type_money,b.min_amount, b.min_goods_amount, b.use_start_date, b.use_end_date, ' . 'b.usebonus_type, b.user_id AS ru_id FROM ' . $GLOBALS['ecs']->table('user_bonus') . ' AS u ,' . $GLOBALS['ecs']->table('bonus_type') . ' AS b' . ' WHERE u.bonus_type_id = b.type_id ' . $uOrder . ' AND u.user_id = \'' . $user_id . '\' ' . $useDate . ' order by u.bonus_id DESC ' . $limit;
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['bonus_id'] = $row['bonus_id'];

		if ($type < 2) {
			$arr[$key]['status'] = $GLOBALS['_LANG']['not_use'];
		}
		else if ($type == 2) {
			$arr[$key]['status'] = '<a href="user.php?act=order_detail&order_id=' . $row['order_id'] . '" >' . $GLOBALS['_LANG']['had_use'] . '</a>';
		}

		$arr[$key]['shop_name'] = get_shop_name($row['ru_id'], 1);
		$arr[$key]['usebonus_type'] = $row['usebonus_type'];
		$arr[$key]['bonus_sn'] = $row['bonus_sn'];
		$arr[$key]['bouns_amount'] = $row['type_money'];
		$arr[$key]['type_money'] = price_format($row['type_money']);
		$arr[$key]['min_goods_amount'] = price_format($row['min_goods_amount']);
		$arr[$key]['use_startdate'] = local_date($GLOBALS['_CFG']['time_format'], $row['use_start_date']);
		$arr[$key]['use_enddate'] = local_date($GLOBALS['_CFG']['time_format'], $row['use_end_date']);
		$arr[$key]['bind_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['bind_time']);
	}

	$bouns = array($arrName => $arr, 'record_count' => $record_count, 'paper' => $bouns_paper);
	return $bouns;
}

function get_bouns_amount_list($bouns_list)
{
	$bouns_amount = 0;

	foreach ($bouns_list['available_list'] as $key => $row) {
		$bouns_amount += $row['bouns_amount'];
	}

	return price_format($bouns_amount);
}

function get_update_flow_Consignee($address_id)
{
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . (' SET address_id = \'' . $address_id . '\' WHERE user_id = \'') . $_SESSION['user_id'] . '\'';
	$GLOBALS['db']->query($sql);
	$sql = 'select * from ' . $GLOBALS['ecs']->table('user_address') . (' where address_id = \'' . $address_id . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function get_cart_info($type = 0)
{
	if (!empty($_SESSION['user_id'])) {
		$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
		$c_sess = ' c.user_id = \'' . $_SESSION['user_id'] . '\' ';
	}
	else {
		$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
		$c_sess = ' c.session_id = \'' . real_cart_mac_ip() . '\' ';
	}

	$limit = '';

	if ($type == 1) {
		$limit = ' LIMIT 0,4';
	}

	$sql = 'SELECT c.*,g.goods_name,g.goods_thumb,g.goods_id,c.goods_number,c.goods_price' . ' FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id=c.goods_id ' . ' WHERE ' . $c_sess . ' AND rec_type = \'' . CART_GENERAL_GOODS . '\' AND stages_qishu=\'-1\' AND store_id = 0 ' . $limit;
	$row = $GLOBALS['db']->GetAll($sql);
	$arr = array();
	$cart_value = '';

	foreach ($row as $k => $v) {
		$arr[$k]['goods_thumb'] = get_image_path($v['goods_thumb']);
		$arr[$k]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($v['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $v['goods_name'];
		$arr[$k]['url'] = build_uri('goods', array('gid' => $v['goods_id']), $v['goods_name']);
		$arr[$k]['goods_number'] = $v['goods_number'];
		$arr[$k]['goods_name'] = $v['goods_name'];
		$arr[$k]['goods_price'] = price_format($v['goods_price']);
		$arr[$k]['rec_id'] = $v['rec_id'];
		$arr[$k]['warehouse_id'] = $v['warehouse_id'];
		$arr[$k]['area_id'] = $v['area_id'];
		$cart_value = !empty($cart_value) ? $cart_value . ',' . $v['rec_id'] : $v['rec_id'];
		$properties = get_goods_properties($v['goods_id'], $v['warehouse_id'], $v['area_id'], $v['goods_attr_id'], 1);

		if ($properties['spe']) {
			$arr[$k]['spe'] = array_values($properties['spe']);
		}
		else {
			$arr[$k]['spe'] = array();
		}
	}

	$sql = 'SELECT SUM(goods_number) AS number, SUM(goods_price * goods_number) AS amount' . ' FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . ' AND rec_type = \'' . CART_GENERAL_GOODS . '\' AND stages_qishu=\'-1\' AND store_id = 0 ';
	$row = $GLOBALS['db']->GetRow($sql);

	if ($row) {
		$number = intval($row['number']);
		$amount = floatval($row['amount']);
	}
	else {
		$number = 0;
		$amount = 0;
	}

	if ($type == 1) {
		$cart = array('goods_list' => $arr, 'number' => $number, 'amount' => price_format($amount, false));
		return $cart;
	}
	else if ($type == 2) {
		$cart = array('goods_list' => $arr, 'number' => $number, 'amount' => price_format($amount, false));
		return $cart;
	}
	else {
		$GLOBALS['smarty']->assign('number', $number);
		$GLOBALS['smarty']->assign('amount', $amount);
		$GLOBALS['smarty']->assign('cart_info', $row);
		$GLOBALS['smarty']->assign('cart_value', $cart_value);
		$GLOBALS['smarty']->assign('str', sprintf($GLOBALS['_LANG']['cart_info'], $number, price_format($amount, false)));
		$GLOBALS['smarty']->assign('goods', $arr);
		$output = $GLOBALS['smarty']->fetch('library/cart_info.lbi');
		return $output;
	}
}

function get_return_goods_url($goods_id = 0, $goods_name = '')
{
	if (empty($goods_name)) {
		$goods_name = $GLOBALS['db']->getOne('SELECT goods_name FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\''));
	}

	$url = build_uri('goods', array('gid' => $goods_id), $goods_name);
	return $url;
}

function get_return_category_url($cat_id = 0)
{
	$cat_name = $GLOBALS['db']->getOne('SELECT cat_name FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE cat_id = \'' . $cat_id . '\''));
	$url = build_uri('category', array('cid' => $cat_id), $cat_name);
	return $url;
}

function get_return_store_shop_url($ru_id = 0)
{
	$shop_name = get_shop_name($ru_id, 1);
	$url = build_uri('merchants_store_shop', array('urid' => $ru_id), $shop_name);
	return $url;
}

function get_return_search_url($keywords = '')
{
	$url = build_uri('search', array('chkw' => $keywords), $keywords);
	return $url;
}

function get_return_self_url()
{
	$cur_url = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
	$cur_url = explode('/', $cur_url);
	$cur_url = $cur_url[count($cur_url) - 1];
	return $cur_url;
}

function get_category_tree_leve_one($parent_id = 0)
{
	$sql = 'SELECT cat_id, cat_name, category_links FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE parent_id = 0 AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$row['cat_id']]['id'] = $row['cat_id'];
		$arr[$row['cat_id']]['url'] = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);

		if (!empty($row['category_links'])) {
			if (empty($type)) {
				$cat_name_arr = explode('、', $row['cat_name']);

				if (!empty($cat_name_arr)) {
					$category_links_arr = explode("\r\n", $row['category_links']);
				}

				$cat_name_str = '';

				foreach ($cat_name_arr as $cat_name_key => $cat_name_val) {
					$link_str = $category_links_arr[$cat_name_key];
					$cat_name_str .= '<a href="' . $link_str . '" target="_blank" class="division_cat">' . $cat_name_val;

					if (count($cat_name_arr) == $cat_name_key + 1) {
						$cat_name_str .= '</a>';
					}
					else {
						$cat_name_str .= '</a>、';
					}
				}

				$arr[$row['cat_id']]['name'] = $cat_name_str;
				$arr[$row['cat_id']]['category_link'] = 1;
				$arr[$row['cat_id']]['oldname'] = $row['cat_name'];
			}
			else {
				$arr[$row['cat_id']]['name'] = $row['cat_name'];
				$arr[$row['cat_id']]['oldname'] = $row['cat_name'];
			}
		}
		else {
			$arr[$row['cat_id']]['name'] = $row['cat_name'];
		}
	}

	return $arr;
}

function get_category_parentChild_tree1($parent_id = 0, $cat_type = 0, $cat_list = 0)
{
	$sql = 'SELECT cat_id,cat_alias_name, cat_name FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE parent_id = \'' . $parent_id . '\' ORDER BY sort_order ASC, cat_id ASC');
	$res = $GLOBALS['db']->getAll($sql);
	$new_arr = array();
	$arr = array();

	if ($res) {
		foreach ($res as $key => $row) {
			$arr[$row['cat_id']]['id'] = $row['cat_id'];

			if ($cat_type == 0) {
				$arr[$row['cat_id']]['cat_name'] = $row['cat_name'];
				$arr[$row['cat_id']]['level'] = 1;
				$arr[$row['cat_id']]['cat_alias_name'] = $row['cat_alias_name'];
				$arr[$row['cat_id']]['name'] = $row['cat_name'];
				$arr[$row['cat_id']]['url'] = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);
			}

			$arr[$row['cat_id']]['cat_id'] = get_category_parentChild_tree2($row['cat_id'], $cat_type);

			if ($cat_list == 1) {
				$r = array(
					$row['cat_id'] => array('cat_id' => $arr[$row['cat_id']]['id'], 'cat_name' => $arr[$row['cat_id']]['cat_name'], 'level' => $arr[$row['cat_id']]['level'], 'id' => $arr[$row['cat_id']]['id'], 'name' => $row['cat_name'], 'cat_alias_name' => $row['cat_alias_name'], 'url' => build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']))
					);
				array_push($new_arr, $r, $arr[$row['cat_id']]['cat_id']);
			}
		}

		if ($cat_list == 1) {
			$new_arr = get_new_arr_one_tree($new_arr);
		}
	}

	return $arr;
}

function get_category_parentChild_tree2($parent_id = 0, $cat_type = 0)
{
	$sql = 'SELECT cat_id, cat_name FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE parent_id = \'' . $parent_id . '\' ORDER BY sort_order ASC, cat_id ASC');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$row['cat_id']]['id'] = $row['cat_id'];

		if ($cat_type == 0) {
			$arr[$row['cat_id']]['cat_name'] = $row['cat_name'];
			$arr[$row['cat_id']]['level'] = 2;
			$arr[$row['cat_id']]['cat_id'] = $row['cat_id'];
			$arr[$row['cat_id']]['name'] = $row['cat_name'];
			$arr[$row['cat_id']]['url'] = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);
		}
	}

	if ($cat_list == 1) {
		return $new_arr;
	}
	else {
		return $arr;
	}
}

function get_new_arr_one_tree($arr)
{
	$new_arr = array();
	$r = array();

	if ($arr) {
		foreach ($arr as $rows) {
			foreach ($rows as $row) {
				array_push($new_arr, $row);
			}
		}
	}

	return $new_arr;
}

function get_category_brands_ad($cat_id)
{
	$arr['ad_position'] = '';
	$arr['brands'] = '';
	$cat_name = '';

	for ($i = 1; $i <= $GLOBALS['_CFG']['auction_ad']; $i++) {
		$cat_name .= '\'cat_tree_' . $cat_id . '_' . $i . '\',';
	}

	$cat_name = substr($cat_name, 0, -1);
	$arr['ad_position'] = get_ad_posti_child($cat_name);
	$children = get_category_parentchild_tree1($cat_id, 1);
	$children = arr_foreach($children);

	if ($children) {
		$children = implode(',', $children) . ',' . $cat_id;
		$children = get_children($children, 0, 1);
	}
	else {
		$children = 'g.cat_id IN (' . $cat_id . ')';
	}

	$sql = 'SELECT b.brand_id, b.brand_name,  b.brand_logo, COUNT(*) AS goods_num, IF(b.brand_logo > \'\', \'1\', \'0\') AS tag ' . 'FROM ' . $GLOBALS['ecs']->table('brand') . 'AS b ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.brand_id = b.brand_id AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_cat') . ' AS gc ON g.goods_id = gc.goods_id ' . ('WHERE (' . $children . ' OR ') . 'gc.cat_id ' . db_create_in(array_unique(array_merge(array($cat_id), array_keys(cat_list($cat_id, 0, 0))))) . ') AND b.is_show = 1 ' . 'GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY b.sort_order, b.brand_id ASC LIMIT 0,12';
	$brands = $GLOBALS['db']->getAll($sql);
	$sql = 'SELECT cat_name FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE cat_id = \'' . $cat_id . '\' LIMIT 1');
	$name = $GLOBALS['db']->getOne($sql);

	foreach ($brands as $key => $val) {
		$temp_key = $key;
		$brands[$temp_key]['brand_name'] = $val['brand_name'];
		$brands[$temp_key]['url'] = build_uri('category', array('cid' => $cat_id, 'bid' => $val['brand_id']), $name);

		if ($brand == $brands[$key]['brand_id']) {
			$brands[$temp_key]['selected'] = 1;
		}
		else {
			$brands[$temp_key]['selected'] = 0;
		}
	}

	$arr['brands'] = $brands;
	return $arr;
}

function print_arr($arr)
{
	echo '<pre>';
	print_r($arr);
	exit();
}

function get_parent_cat_tree($cat_id)
{
	$categories_child = read_static_cache('cat_top_cache' . $cat_id);

	if (!$categories_child) {
		$categories_child = get_parent_cat_child($cat_id);
		write_static_cache('cat_top_cache' . $cat_id, $categories_child);
	}

	return $categories_child;
}

function friendlyDate($sTime, $type = 'normal', $alt = 'false')
{
	if (!$sTime) {
		return '';
	}

	$cTime = time();
	$dTime = $cTime - $sTime;
	$dDay = intval(date('z', $cTime)) - intval(date('z', $sTime));
	$dYear = intval(date('Y', $cTime)) - intval(date('Y', $sTime));

	if ($type == 'normal') {
		if ($dTime < 60) {
			if ($dTime < 10) {
				return '刚刚';
			}
			else {
				return intval(floor($dTime / 10) * 10) . '秒前';
			}
		}
		else if ($dTime < 3600) {
			return intval($dTime / 60) . '分钟前';
		}
		else {
			if ($dYear == 0 && $dDay == 0) {
				return '今天' . date('H:i', $sTime);
			}
			else if ($dYear == 0) {
				return date('m月d日 H:i', $sTime);
			}
			else {
				return date('Y-m-d H:i', $sTime);
			}
		}
	}
	else if ($type == 'mohu') {
		if ($dTime < 60) {
			return $dTime . '秒前';
		}
		else if ($dTime < 3600) {
			return intval($dTime / 60) . '分钟前';
		}
		else {
			if (3600 <= $dTime && $dDay == 0) {
				return intval($dTime / 3600) . '小时前';
			}
			else {
				if (0 < $dDay && $dDay <= 7) {
					return intval($dDay) . '天前';
				}
				else {
					if (7 < $dDay && $dDay <= 30) {
						return intval($dDay / 7) . '周前';
					}
					else if (30 < $dDay) {
						return intval($dDay / 30) . '个月前';
					}
				}
			}
		}
	}
	else if ($type == 'full') {
		return date('Y-m-d , H:i:s', $sTime);
	}
	else if ($type == 'ymd') {
		return date('Y-m-d', $sTime);
	}
	else if ($type == 'moremohu') {
		if ($dTime < 60) {
			return $dTime . '秒前';
		}
		else if ($dTime < 3600) {
			return intval($dTime / 60) . '分钟前';
		}
		else {
			if (3600 <= $dTime && $dDay == 0) {
				return intval($dTime / 3600) . '小时前';
			}
			else {
				if (0 < $dDay && $dDay <= 7) {
					return intval($dDay) . '天前';
				}
				else {
					if (7 < $dDay && $dDay <= 30) {
						return intval($dDay / 7) . '周前';
					}
					else {
						return '一个月前';
					}
				}
			}
		}
	}
	else if ($dTime < 60) {
		return $dTime . '秒前';
	}
	else if ($dTime < 3600) {
		return intval($dTime / 60) . '分钟前';
	}
	else {
		if (3600 <= $dTime && $dDay == 0) {
			return intval($dTime / 3600) . '小时前';
		}
		else if ($dYear == 0) {
			return date('Y-m-d H:i:s', $sTime);
		}
		else {
			return date('Y-m-d H:i:s', $sTime);
		}
	}
}

function get_oss_add_file($file = array())
{
	if ($file) {
		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$urlip = get_ip_url(dirname($GLOBALS['ecs']->url()));
			$bucket_info = get_bucket_info();
			$url = $urlip . 'oss.php?act=upload';
			$Http = new \App\Extensions\Http();
			$post_data = array('bucket' => $bucket_info['bucket'], 'keyid' => $bucket_info['keyid'], 'keysecret' => $bucket_info['keysecret'], 'is_cname' => $bucket_info['is_cname'], 'endpoint' => $bucket_info['outside_site'], 'object' => $file);
			$Http->doPost($url, $post_data);
		}
	}
}

function get_oss_del_file($file = array())
{
	if ($file) {
		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();
			$url = $GLOBALS['ecs']->url();
			$self = explode('/', substr(PHP_SELF, 1));
			$count = count($self);

			if (1 < $count) {
				$real_path = $self[$count - 2];

				if ($real_path == 'seller') {
					$str_len = 0 - (str_len('seller') + 1);
					$url = substr($GLOBALS['ecs']->url(), 0, $str_len);
				}
			}

			$urlip = get_ip_url($url);
			$url = $urlip . 'oss.php?act=del_file';
			$Http = new \App\Extensions\Http();
			$post_data = array('bucket' => $bucket_info['bucket'], 'keyid' => $bucket_info['keyid'], 'keysecret' => $bucket_info['keysecret'], 'is_cname' => $bucket_info['is_cname'], 'endpoint' => $bucket_info['outside_site'], 'object' => $file);
			$Http->doPost($url, $post_data);
		}
	}
}

function get_ip_url($url, $type = 0)
{
	if (strpos($url, 'http://') !== false) {
		$http = 'http://';
	}
	else if (strpos($url, 'https://') !== false) {
		$http = 'https://';
	}

	$file = dirname(ROOT_PATH) . '/data/urlip.txt';
	$file = file_get_contents($file);

	if ($type == 1) {
		return $http . $file;
	}
	else {
		return $http . $file . '/';
	}
}

function get_bucket_info($config = false)
{
	$res = dao('oss_configure')->cache(true)->where(array('is_use' => 1))->find();

	if (empty($res)) {
		return false;
	}

	$regional = substr($res['regional'], 0, 2);

	if ($config == true) {
		$endpoint = rtrim(str_replace(array('http://', 'https://'), '', strtolower($res['endpoint'])), '/');
	}

	if ($regional == 'us' || $regional == 'ap') {
		if ($config == true) {
			$res['endpoint'] = $res['is_cname'] == 1 ? $endpoint : 'oss-' . $res['regional'] . '.aliyuncs.com';
		}

		$res['outside_site'] = 'http://' . $res['bucket'] . '.oss-' . $res['regional'] . '.aliyuncs.com';
		$res['inside_site'] = 'http://' . $res['bucket'] . '.oss-' . $res['regional'] . '-internal.aliyuncs.com';
	}
	else {
		if ($config == true) {
			$res['endpoint'] = $res['is_cname'] == 1 ? $endpoint : 'oss-cn-' . $res['regional'] . '.aliyuncs.com';
		}

		$res['outside_site'] = 'http://' . $res['bucket'] . '.oss-cn-' . $res['regional'] . '.aliyuncs.com';
		$res['inside_site'] = 'http://' . $res['bucket'] . '.oss-cn-' . $res['regional'] . '-internal.aliyuncs.com';
	}

	if ($config == true) {
		return array('bucket' => $res['bucket'], 'accessKeyId' => $res['keyid'], 'accessKeySecret' => $res['keysecret'], 'endpoint' => $res['endpoint'], 'isCName' => (bool) $res['is_cname']);
	}
	else {
		return $res;
	}
}

function get_goods_desc_images_preg($endpoint = '', $text_desc = '', $str_file = 'goods_desc')
{
	if ($text_desc) {
		$preg = '/<img.*?src=[\\"|\']?(.*?)[\\"|\'].*?>/i';
		preg_match_all($preg, $text_desc, $desc_img);
	}
	else {
		$desc_img = '';
	}

	$arr = array();
	if ($desc_img && $endpoint) {
		foreach ($desc_img[1] as $key => $row) {
			$row = explode('images', $row);
			$arr[] = $endpoint . 'images' . $row[1];
		}

		if ($desc_img[1]) {
			if (1 < count($desc_img[1])) {
				$desc_img[1] = array_unique($desc_img[1]);

				foreach ($desc_img[1] as $key => $row) {
					if (strpos($row, 'http://') === false && strpos($row, 'https://') === false) {
						$row_str = substr($row, 0, 1);
						$str = substr($endpoint, str_len($endpoint) - 1);
						if ($str == '/' && $row_str == '/') {
							$endpoint = substr($endpoint, 0, -1);
						}

						if ($str_file == 'desc_mobile') {
							$text_desc = str_replace($row, $endpoint . ltrim($row, '../'), $text_desc);
						}
						else {
							$text_desc = str_replace($row, $endpoint . $row, $text_desc);
						}
					}
				}
			}
			else if (strpos($text_desc, $endpoint) === false) {
				$text_desc = str_replace('/' . 'images', $endpoint . 'images', $text_desc);
			}
		}
	}

	$res = array('images_list' => $arr, $str_file => $text_desc);
	return $res;
}

function get_invoice_list($invoice, $order_type = 0, $inv_content = '')
{
	$arr = array();

	if ($invoice['type']) {
		$type = array_values($invoice['type']);
		$rate = array_values($invoice['rate']);

		for ($i = 0; $i < count($type); $i++) {
			if ($order_type == 1) {
				if ($type[$i] == $inv_content) {
					$arr['type'] = $type[$i];
					$arr['rate'] = $rate[$i];
				}
			}
			else {
				$arr[$i]['type'] = $type[$i];
				$arr[$i]['rate'] = $rate[$i];
			}
		}
	}

	return $arr;
}

function available_shipping_list($region, $ru_id = 0, $is_limit = 0)
{
	$order_by = '';
	$limit = '';

	if ($is_limit) {
		$limit = ' LIMIT 0, 1';
	}
	else {
		$order_by = ' ORDER BY s.shipping_order';
	}

	$shipping_list = array();
	$sql = 'SELECT s.* FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_transport_tpl') . ' AS gtt ON s.shipping_id = gtt.shipping_id' . (' WHERE gtt.user_id = \'' . $ru_id . '\' AND s.enabled = 1') . ' AND (FIND_IN_SET(\'' . $region[1] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[2] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[3] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[4] . '\', gtt.region_id))' . ' GROUP BY s.shipping_id' . $order_by . $limit;
	$shipping_list1 = $GLOBALS['db']->getAll($sql);
	$sql = 'SELECT s.* FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_transport_extend') . (' AS gted ON gted.ru_id = \'' . $ru_id . '\'') . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_transport_express') . (' AS gte ON gted.tid = gte.tid AND gte.ru_id = \'' . $ru_id . '\'') . ' WHERE FIND_IN_SET(s.shipping_id, gte.shipping_id) ' . ' AND ((FIND_IN_SET(\'' . $region[1] . '\', gted.top_area_id)) OR (FIND_IN_SET(\'' . $region[2] . '\', gted.area_id) OR FIND_IN_SET(\'' . $region[3] . '\', gted.area_id) OR FIND_IN_SET(\'' . $region[4] . '\', gted.area_id)))' . ' GROUP BY s.shipping_id';
	$shipping_list2 = $GLOBALS['db']->getAll($sql);
	if ($shipping_list1 && $shipping_list2) {
		$shipping_list = array_merge($shipping_list1, $shipping_list2);
	}
	else if ($shipping_list1) {
		$shipping_list = $shipping_list1;
	}
	else if ($shipping_list2) {
		$shipping_list = $shipping_list2;
	}

	if ($shipping_list) {
		$new_shipping = array();

		foreach ($shipping_list as $key => $val) {
			@$new_shipping[$val['shipping_code']][] = $key;
		}

		foreach ($new_shipping as $key => $val) {
			if (1 < count($val)) {
				for ($i = 1; $i < count($val); $i++) {
					unset($shipping_list[$val[$i]]);
				}
			}
		}

		$shipping_list = get_array_sort($shipping_list, 'shipping_order');
	}

	$cfg = array(
		array('name' => 'item_fee', 'value' => 0),
		array('name' => 'base_fee', 'value' => 0),
		array('name' => 'step_fee', 'value' => 0),
		array('name' => 'free_money', 'value' => 100000)
		);

	if ($shipping_list) {
		foreach ($shipping_list as $key => $row) {
			if (!isset($row['configure']) && empty($row['configure'])) {
				$shipping_list[$key]['configure'] = serialize($cfg);
			}
		}
	}

	return $shipping_list;
}

function unserialize_config($cfg)
{
	if (is_string($cfg) && ($arr = unserialize($cfg)) !== false) {
		$config = array();

		foreach ($arr as $key => $val) {
			$config[$val['name']] = $val['value'];
		}

		return $config;
	}
	else {
		return false;
	}
}

function get_goods_one_attr_price($goods, $warehouse_id = 0, $area_id = 0, $promote_price = 0, $goods_product = array())
{
	$goods_product = array('product_price' => $goods['product_price'], 'product_promote_price' => $goods['product_promote_price']);
	$products = array();
	$market_price = $goods['market_price'];
	$org_price = $goods['org_price'];
	$shop_price = $goods['shop_price'];
	$time = gmtime();
	if ($goods['promote_start_date'] <= $time && $time <= $goods['promote_end_date']) {
		$is_promote = 1;
	}
	else {
		$is_promote = 0;
	}

	$price = array('goods_id' => !empty($goods['goods_id']) ? $goods['goods_id'] : 0, 'market_price' => $market_price, 'org_price' => $org_price, 'shop_price' => $shop_price, 'promote_price' => $promote_price);
	return $price;
}

function get_goods_checked_attr($values)
{
	foreach ($values as $key => $val) {
		if ($val['checked']) {
			return $val;
		}
	}
}

function get_del_str_comma($str = '')
{
	if ($str && is_array($str)) {
		return $str;
	}
	else {
		if ($str) {
			$str = str_replace(',,', ',', $str);
			$str1 = substr($str, 0, 1);
			$str2 = substr($str, str_len($str) - 1);
			if ($str1 === ',' && $str2 !== ',') {
				$str = substr($str, 1);
			}
			else {
				if ($str1 !== ',' && $str2 === ',') {
					$str = substr($str, 0, -1);
				}
				else {
					if ($str1 === ',' && $str2 === ',') {
						$str = substr($str, 1);
						$str = substr($str, 0, -1);
					}
				}
			}
		}

		return $str;
	}
}

function encrypt_username($username)
{
	$username_start = mb_substr($username, 0, 1, 'utf-8');
	$username_end = mb_substr($username, -1, 1, 'utf-8');
	$username_new = $username_start . '****' . $username_end;
	return $username_new;
}

function get_package_goods_info($package_list = array())
{
	if ($package_list) {
		$arr = array();
		$arr['goods_weight'] = 0;

		foreach ($package_list as $key => $row) {
			$arr[$key]['goods_weight'] = $row['goods_number'] * $row['goods_weight'];
			$arr['goods_weight'] += $arr[$key]['goods_weight'];
		}

		return $arr;
	}
}

function get_order_goods_commission($order_id = 0, $type = 0)
{
	$sql = ' SELECT goods_id, goods_price, goods_number FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' ');
	$order_goods = $GLOBALS['db']->getAll($sql);
	$commission = 0;
	$cat = array();

	if ($order_goods) {
		foreach ($order_goods as $goods) {
			if ($type == 1) {
				$rate = get_commission_rate($goods['goods_id'], $type);
				$cat[$goods['goods_id']]['commission_rate'] = $rate['commission_rate'];
				$cat[$goods['goods_id']]['cat_id'] = $rate['cat_id'];
				$commission_rate = $rate['commission_rate'];
			}
			else {
				$commission_rate = get_commission_rate($goods['goods_id']);
			}

			$commission += $goods['goods_price'] * $goods['goods_number'] * $commission_rate;
		}
	}

	if ($type == 1) {
		$arr = array('commission' => $commission, 'cat' => $cat);
		return $arr;
	}
	else {
		return $commission;
	}
}

function get_commission_rate($goods_id = 0, $type = 0)
{
	$sql = ' SELECT cat_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\' ');
	$cat_id = $GLOBALS['db']->getOne($sql);
	$commission_rate = 0;

	while (0 < $cat_id) {
		$sql = ' SELECT commission_rate FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE cat_id = \'' . $cat_id . '\' ');
		$commission_rate = $GLOBALS['db']->getOne($sql);

		if (0 < $commission_rate) {
			break;
		}
		else {
			$sql = ' SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE cat_id = \'' . $cat_id . '\' ');
			$cat_id = $GLOBALS['db']->getOne($sql);
		}
	}

	if (0 < $commission_rate) {
		$commission_rate /= 100;
	}

	if ($type == 1) {
		$arr = array('commission_rate' => $commission_rate, 'cat_id' => $cat_id);
		return $arr;
	}
	else {
		return $commission_rate;
	}
}

function create_snapshot($order_id = 0)
{
	$sql = ' SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE main_order_id = \'' . $order_id . '\' ');
	if ($order_ids = $GLOBALS['db']->getAll($sql) && 0 < $order_id) {
		foreach ($order_ids as $val) {
			$sql = 'SELECT oi.order_sn, oi.user_id, og.ru_id, og.goods_id, og.goods_name, og.goods_sn, og.goods_attr, og.goods_attr_id, og.goods_price, og.goods_number,og.shipping_fee, g.goods_weight, g.add_time, g.goods_desc, g.goods_img FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON oi.order_id = og.order_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = og.goods_id ' . (' WHERE oi.order_id = \'' . $val['order_id'] . '\' ');
			$result = $GLOBALS['db']->getAll($sql);

			foreach ($result as $v) {
				insert_snapshot($v);
			}
		}
	}
	else {
		$sql = ' SELECT oi.order_sn, oi.user_id, og.ru_id, og.goods_id, og.goods_name, og.goods_sn, og.goods_attr, og.goods_attr_id, og.goods_price, og.goods_number,og.shipping_fee, g.goods_weight, g.add_time, g.goods_desc, g.goods_img FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON oi.order_id = og.order_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = og.goods_id ' . (' WHERE oi.order_id = \'' . $order_id . '\' ');
		$result = $GLOBALS['db']->getAll($sql);

		foreach ($result as $v) {
			insert_snapshot($v);
		}
	}
}

function insert_snapshot($arr = array())
{
	$arr = is_array($arr) ? $arr : array();

	if ($arr) {
		$snapshot_info = array('order_sn' => $arr['order_sn'], 'user_id' => $arr['user_id'], 'goods_id' => $arr['goods_id'], 'goods_name' => addslashes($arr['goods_name']), 'goods_sn' => $arr['goods_sn'], 'shop_price' => $arr['goods_price'], 'goods_number' => $arr['goods_number'], 'shipping_fee' => $arr['shipping_fee'], 'rz_shopName' => get_shop_name($arr['ru_id'], 1), 'goods_weight' => $arr['goods_weight'], 'add_time' => $arr['add_time'], 'goods_attr' => $arr['goods_attr'], 'goods_attr_id' => $arr['goods_attr_id'], 'ru_id' => $arr['ru_id'], 'goods_desc' => addslashes($arr['goods_desc']), 'goods_img' => $arr['goods_img'], 'snapshot_time' => gmtime());
		return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('trade_snapshot'), $snapshot_info, 'INSERT');
	}
	else {
		return 0;
	}
}

function get_goods_sale($order_id)
{
	$sql = 'SELECT goods_id,goods_number FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE order_id =' . $order_id;
	$order_res = $GLOBALS['db']->getAll($sql);

	foreach ($order_res as $idx => $val) {
		$sql = 'SELECT SUM(og.goods_number) as goods_number ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g, ' . $GLOBALS['ecs']->table('order_info') . ' AS o, ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . ' WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND og.order_id = o.order_id AND og.goods_id = g.goods_id ' . ' AND g.goods_id=' . $val['goods_id'];
		$sales_volume = $GLOBALS['db']->getOne($sql);
		$sql = 'update ' . $GLOBALS['ecs']->table('goods') . (' set sales_volume=\'' . $sales_volume . '\' WHERE goods_id =') . $val['goods_id'];
		$GLOBALS['db']->query($sql);
	}
}

function is_update_sale($order_id)
{
	$sql = 'SELECT is_update_sale FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\'');
	return $GLOBALS['db']->getOne($sql, true);
}

function get_seo_words($type = '', $cat_id = 0)
{
	$res = array();

	if ($type) {
		if ($type == 'category' && 0 < $cat_id) {
			$res = dao('category')->field('cate_title as title, cate_keywords as keywords, cate_description as description')->where(array('cat_id' => $cat_id))->cache(true, 12 * 3600)->find();
		}
		else {
			$res = dao('seo')->field('title, keywords, description')->where(array('type' => $type))->cache(true, 12 * 3600)->find();
		}
	}

	return $res;
}

if (!function_exists('array_combine')) {
	function array_combine($keys, $values)
	{
		if (!is_array($keys)) {
			user_error('array_combine() expects parameter 1 to be array, ' . gettype($keys) . ' given', 512);
			return NULL;
		}

		if (!is_array($values)) {
			user_error('array_combine() expects parameter 2 to be array, ' . gettype($values) . ' given', 512);
			return NULL;
		}

		$key_count = count($keys);
		$value_count = count($values);

		if ($key_count !== $value_count) {
			user_error('array_combine() Both parameters should have equal number of elements', 512);
			return false;
		}

		if ($key_count === 0 || $value_count === 0) {
			user_error('array_combine() Both parameters should have number of elements at least 0', 512);
			return false;
		}

		$keys = array_values($keys);
		$values = array_values($values);
		$combined = array();

		for ($i = 0; $i < $key_count; $i++) {
			$combined[$keys[$i]] = $values[$i];
		}

		return $combined;
	}
}

?>
