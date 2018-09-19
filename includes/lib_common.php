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
			$item_list_tmp = get_del_str_comma($item_list_tmp);
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

	include_once ROOT_PATH . 'includes/modules/integrates/' . $GLOBALS['_CFG']['integrate_code'] . '.php';
	$cfg = unserialize($GLOBALS['_CFG']['integrate_config']);
	$cls = new $GLOBALS['_CFG']['integrate_code']($cfg);
	return $cls;
}

function cat_level_html($cat_list, $ru_id, $type = 0, $table = 'category')
{
	$html = '';

	if ($cat_list) {
		foreach ($cat_list as $k => $row) {
			$sql = ' select cat_id from ' . $GLOBALS['ecs']->table($table) . ' where parent_id=\'' . $row['cat_id'] . '\'';
			$child_exist = $GLOBALS['db']->getOne($sql, true);

			if ($child_exist) {
				$show_status = 'up';
			}
			else {
				$show_status = 'down';
			}

			$html .= '<tr align="center" id="' . $row['level'] . '_' . $row['cat_id'] . '" class="' . $row['parent_id'] . '_' . $row['level'] . "\">\r\n                            <td align=\"left\" id=\"level_" . $row['level'] . '_' . $row['cat_id'] . '" class="first-cell"><div class="first_column">';

			if ($row['is_leaf'] != 1) {
				$html .= '<i data-level="' . $row['level'] . '" data-catid="' . $row['cat_id'] . '" data-isclick="0" style="margin-left:' . $row['level'] . 'em;" id="icon_' . $row['level'] . '_' . $row['cat_id'] . '" class="' . $show_status . '"></i>';
			}
			else {
				$html .= '<img width="9" height="9" border="0" style="margin-left:' . $row['level'] . 'em;vertical-align:middle; margin-top:-1px;" id="icon_' . $row['level'] . '_' . $row['cat_id'] . '" src="images/menu_arrow.gif">';
			}

			$html .= '<span><a href="goods.php?act=list&amp;cat_id=' . $row['cat_id'] . '&cat_type=seller">' . $row['cat_name'] . '</a></span>';

			if ($row['cat_image']) {
				$html .= '<img src="../' . $row['cat_image'] . '" border="0" style="vertical-align:middle;" width="60px" height="21px">';
			}

			$html .= '</div></td>';

			if ($type == 1) {
				if ($ru_id == 0) {
					$html .= '<td style="color:#F00;">' . $row['user_name'] . '</td>';
				}
			}

			$html .= '<td>' . $row['goods_num'] . '</td>';
			$html .= '<td><span onclick="listTable.edit(this, ' . '\'edit_measure_unit\'' . ', ' . $row['cat_id'] . ')">' . $row['measure_unit'] . '</span></td>';
			$html .= '</td>';

			if ($ru_id == 0) {
				if ($row['show_in_nav'] == 1) {
					$html .= '<td><img onclick="listTable.toggle(this, ' . '\'toggle_show_in_nav\'' . ', ' . $row['cat_id'] . ')" src="images/yes.gif"></td>';
				}
				else {
					$html .= '<td><img onclick="listTable.toggle(this, ' . '\'toggle_show_in_nav\'' . ', ' . $row['cat_id'] . ')" src="images/no.gif"></td>';
				}

				if ($row['is_show'] == 1) {
					$html .= '<td><img onclick="listTable.toggle(this, ' . '\'toggle_is_show\'' . ', ' . $row['cat_id'] . ')" src="images/yes.gif"></td>';
				}
				else {
					$html .= '<td><img onclick="listTable.toggle(this, ' . '\'toggle_is_show\'' . ', ' . $row['cat_id'] . ')" src="images/no.gif"></td>';
				}
			}

			if ($type == 0) {
				if ($ru_id == 0) {
					$html .= '<td><span onclick="listTable.edit(this, ' . '\'edit_grade\'' . ', ' . $row['cat_id'] . ')">' . $row['grade'] . '</span></td>';
				}
			}
			else {
				$html .= '<td><span onclick="listTable.edit(this, ' . '\'edit_grade\'' . ', ' . $row['cat_id'] . ')">' . $row['grade'] . '</span></td>';
				$html .= '<td align="center"><span onclick="listTable.edit(this, ' . '\'edit_sort_order\'' . ', ' . $row['cat_id'] . ')">' . $row['sort_order'] . '</span></td>';
			}

			if ($type == 1) {
				if ($row['is_show']) {
					$html .= '<td align="center"><img src="images/yes.gif" onclick="listTable.toggle(this, ' . '\'toggle_is_show\'' . ', ' . $row['cat_id'] . ')" title="点击" class="pointer" /></td>';
				}
				else {
					$html .= '<td align="center"><img src="images/no.gif" onclick="listTable.toggle(this, ' . '\'toggle_is_show\'' . ', ' . $row['cat_id'] . ')" title="点击" class="pointer" /></td>';
				}
			}

			$html .= '<td align="center">';

			if ($type == 1) {
				$html .= '<a href="category_store.php?act=move&amp;cat_id=' . $row['cat_id'] . '" class="blue">转移商品</a>';
			}
			else {
				$html .= '<a href="category.php?act=move&amp;cat_id=' . $row['cat_id'] . '" class="blue">转移商品</a>';
			}

			if ($ru_id == 0) {
				$html .= " |\r\n                                <a href=\"category.php?act=edit&amp;cat_id=" . $row['cat_id'] . "\" class=\"blue\">编辑</a> |\r\n                                <a title=\"移除\" href=\"javascript:;\" onclick=\"listTable.remove(" . $row['cat_id'] . ',' . '\'您确定要删除吗？\'' . ')" class="blue">移除</a>';
			}

			if ($ru_id && $row['ru_id']) {
				$html .= " |\r\n                                    <a href=\"category_store.php?act=edit&amp;cat_id=" . $row['cat_id'] . "\" class=\"blue\">编辑</a> |\r\n                                    <a title=\"移除\" onclick=\"listTable.remove(" . $row['cat_id'] . ',' . '\'您确定要删除吗？\'' . ')" href="javascript:;" class="blue">移除</a>';
			}

			$html .= "</td>\r\n                        </tr>";
		}
	}

	return $html;
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

function sc_stime()
{
	return gmtime() + microtime();
}

function sc_timer($stime)
{
	$etime = gmtime() + microtime();
	$pass_time = sprintf('%.2f', $etime - $stime);
	return $pass_time;
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

function load_config()
{
	$arr = array();
	$certi_url = '';
	$data = read_static_cache('shop_config');
	if ($data === false || empty($data)) {
		$sql = 'SELECT code, value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE parent_id > 0';
		$res = $GLOBALS['db']->getAll($sql);

		foreach ($res as $row) {
			$arr[$row['code']] = $row['value'];
		}

		if ($arr['qq']) {
			$kf_qq = array_filter(preg_split('/\\s+/', $arr['qq']));

			if (!empty($kf_qq[0])) {
				$kf_qq = explode('|', $kf_qq[0]);

				if ($kf_qq) {
					if (!empty($kf_qq[1])) {
						$kf_qq_one = $kf_qq[1];
					}
				}
			}
		}
		else {
			$kf_qq_one = '';
		}

		if ($arr['ww']) {
			$kf_ww = array_filter(preg_split('/\\s+/', $arr['ww']));

			if (!empty($kf_ww[0])) {
				$kf_ww = explode('|', $kf_ww[0]);

				if (!empty($kf_ww[1])) {
					$kf_ww_one = $kf_ww[1];
				}
				else {
					$kf_ww_one = '';
				}
			}
		}
		else {
			$kf_ww_one = '';
		}

		$certi_url = 'http://ecshop.ecmoban.com/dsc.php';
		if (empty($arr['certi']) || $arr['certi'] != $certi_url) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('shop_config') . (' SET value = \'' . $certi_url . '\' WHERE code = \'certi\'');
			$row = $GLOBALS['db']->query($sql);
		}

		$arr['certi'] = isset($arr['default_storage']) && !empty($arr['certi']) ? $arr['certi'] : $certi_url;
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
		$arr['no_picture'] = !empty($arr['no_picture']) ? $arr['no_picture'] : 'images/no_picture.gif';
		$arr['qq'] = !empty($kf_qq_one) ? $kf_qq_one : '';
		$arr['ww'] = !empty($kf_ww_one) ? $kf_ww_one : '';
		$arr['default_storage'] = isset($arr['default_storage']) ? intval($arr['default_storage']) : 1;
		$arr['min_goods_amount'] = isset($arr['min_goods_amount']) ? floatval($arr['min_goods_amount']) : 0;
		$arr['one_step_buy'] = empty($arr['one_step_buy']) ? 0 : 1;
		$arr['invoice_type'] = !isset($arr['invoice_type']) && empty($arr['invoice_type']) ? array(
	'type' => array(),
	'rate' => array()
	) : $arr['invoice_type'];
		$arr['show_order_type'] = isset($arr['show_order_type']) ? $arr['show_order_type'] : 0;
		$arr['help_open'] = isset($arr['help_open']) ? $arr['help_open'] : 1;
		$arr['cat_belongs'] = isset($arr['cat_belongs']) ? $arr['cat_belongs'] : 0;

		if (!is_array($arr['invoice_type'])) {
			$arr['invoice_type'] = dsc_unserialize($arr['invoice_type']);
		}

		if (!isset($GLOBALS['_CFG']['dsc_version'])) {
			$GLOBALS['_CFG']['dsc_version'] = 'v1.0';
		}

		$lang_array = array('zh_cn', 'zh_tw', 'en_us');
		if (empty($arr['lang']) || !in_array($arr['lang'], $lang_array)) {
			$arr['lang'] = 'zh_cn';
		}

		if (empty($arr['integrate_code'])) {
			$arr['integrate_code'] = 'ecshop';
		}

		$arr['site_domain'] = get_site_domain($arr['site_domain']);
		write_static_cache('shop_config', $arr);
	}
	else {
		$certi_url = $GLOBALS['db']->getOne('SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'certi\'');
		$certi_size = 'http://ecshop.ecmoban.com/dsc.php';
		if (empty($certi_url) || $certi_url != $certi_size) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('shop_config') . (' SET value = \'' . $certi_size . '\' WHERE code = \'certi\'');
			$row = $GLOBALS['db']->query($sql);
		}

		$data['site_domain'] = isset($data['site_domain']) && !empty($data['site_domain']) ? $data['site_domain'] : '';
		$data['site_domain'] = get_site_domain($data['site_domain']);
		$arr = $data;
	}

	return $arr;
}

function get_brand_list($goods_id = 0, $type = 0, $ru_id = 0)
{
	if (0 < $goods_id) {
		$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\'');
		$seller_id = $GLOBALS['db']->getOne($sql, true);
	}
	else if (0 < $ru_id) {
		$seller_id = $ru_id;
	}
	else {
		$adminru = get_admin_ru_id();
		$seller_id = $adminru['ru_id'];
	}

	if ($type == 2) {
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('brand') . ' WHERE 1';
		$brand_list = $GLOBALS['db']->getOne($sql, true);
	}
	else {
		$sql = 'SELECT brand_id, brand_name, brand_first_char FROM ' . $GLOBALS['ecs']->table('brand') . ' ORDER BY sort_order';
		$res = $GLOBALS['db']->getAll($sql);
		$brand_list = array();

		foreach ($res as $key => $row) {
			if ($seller_id) {
				$val['is_brand'] = get_seller_brand_count($row['brand_id'], $seller_id);
			}
			else {
				$val['is_brand'] = 1;
			}

			if (0 < $val['is_brand']) {
				if ($type == 1) {
					$brand_list[$key]['brand_id'] = $row['brand_id'];
					$brand_list[$key]['brand_name'] = addslashes($row['brand_name']);
					$brand_list[$key]['brand_first_char'] = $row['brand_first_char'];
				}
				else {
					$brand_list[$key]['brand_id'] = $row['brand_id'];
					$brand_list[$key]['brand_name'] = addslashes($row['brand_name']);
				}
			}
			else {
				unset($brand_list[$row['brand_id']]);
			}
		}
	}

	if ($brand_list && is_array($brand_list)) {
		$brand_list = array_values($brand_list);
	}

	return $brand_list;
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

function get_brands($cat = 0, $app = 'brand', $num = 0, $page = 1, $page_size = 8)
{
	global $page_libs;
	$template = basename(PHP_SELF);
	$template = substr($template, 0, strrpos($template, '.'));
	static $static_page_libs;

	if ($static_page_libs == NULL) {
		$static_page_libs = $page_libs;
	}

	$row = read_static_cache('get_brands_list' . $cat, '/temp/static_caches/');

	if ($row === false) {
		$children = 0 < $cat ? '1 AND ' . get_children($cat) : 1;
		$sql = 'SELECT b.brand_id, b.brand_name, b.brand_logo, b.index_img, b.brand_desc, COUNT(*) AS goods_num, IF(b.brand_logo > \'\', \'1\', \'0\') AS tag, b.site_url ' . 'FROM ' . $GLOBALS['ecs']->table('brand') . 'AS b ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.brand_id = b.brand_id AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . ('WHERE ' . $children . ' AND b.is_show = 1 ') . 'GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY tag DESC, b.sort_order ASC';

		if (isset($static_page_libs[$template]['/library/brands.lbi'])) {
			$num = get_library_number('brands');
			$sql .= ' LIMIT ' . $num . ' ';
		}
		else if (0 < $num) {
			$sql .= ' LIMIT ' . $num . ' ';
		}

		$row = $GLOBALS['db']->getAll($sql);

		foreach ($row as $key => $val) {
			if ($val['site_url'] && 8 < strlen($val['site_url'])) {
				$row[$key]['url'] = $val['site_url'];
			}
			else {
				$row[$key]['url'] = build_uri($app, array('cid' => $cat, 'bid' => $val['brand_id']), $val['brand_name']);
			}

			$row[$key]['brand_desc'] = htmlspecialchars($val['brand_desc'], ENT_QUOTES);
			$row[$key]['brand_logo'] = DATA_DIR . '/brandlogo/' . $val['brand_logo'];
			$row[$key]['index_img'] = empty($val['index_img']) ? '' : DATA_DIR . '/indeximg/' . $val['index_img'];

			if ($GLOBALS['_CFG']['open_oss'] == 1) {
				$bucket_info = get_bucket_info();
				$row[$key]['brand_logo'] = $bucket_info['endpoint'] . DATA_DIR . '/brandlogo/' . $val['brand_logo'];
				$row[$key]['index_img'] = empty($val['index_img']) ? '' : $bucket_info['endpoint'] . DATA_DIR . '/indeximg/' . $val['index_img'];
			}

			if (defined('THEME_EXTENSION') && 0 < $_SESSION['user_id']) {
				$row[$key]['is_collect'] = get_collect_user_brand($val['brand_id']);
			}
		}

		write_static_cache('get_brands_list' . $cat, $row, '/temp/static_caches/');
	}

	if (defined('THEME_EXTENSION')) {
		$page_array = $GLOBALS['ecs']->page_array($page_size, $page, $row);
		$row = $page_array['list'];
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
			$row[$key]['url'] = build_uri('brandn', array('bid' => $val['brand_id']), $val['brand_name']);
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

function cat_brand_count($cat = 0)
{
	$children = 0 < $cat ? '1 AND ' . get_children($cat) : 1;
	$sql = 'SELECT b.brand_id, b.brand_name, b.brand_logo, b.brand_desc, COUNT(*) AS goods_num, IF(b.brand_logo > \'\', \'1\', \'0\') AS tag ' . 'FROM ' . $GLOBALS['ecs']->table('brand') . 'AS b ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.brand_id = b.brand_id AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . ('WHERE ' . $children . ' AND b.is_show = 1 ') . 'GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY tag DESC, b.sort_order ASC LIMIT 0,1';
	$row = $GLOBALS['db']->getAll($sql);
	return $row;
}

function is_promotion($goods_id)
{
	$arr = array();

	if (!empty($goods_id)) {
		$snatch = array();
		$group = array();
		$auction = array();
		$package = array();
		$sql = 'SELECT ga.act_id, ga.act_type, g.goods_sn FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga, ' . $GLOBALS['ecs']->table('goods') . ' g' . ' WHERE ga.goods_id = g.goods_id AND ga.goods_id ' . db_create_in($goods_id) . ' GROUP BY ga.goods_id';
		$res = $GLOBALS['db']->getAll($sql);

		if ($res) {
			foreach ($res as $key => $data) {
				switch ($data['act_type']) {
				case GAT_SNATCH:
					$snatch['snatch']['type'] = 'snatch';
					$snatch['snatch'][$snatch['snatch']['type']]['goods_sn'] .= $data['goods_sn'] . ',';
					break;

				case GAT_GROUP_BUY:
					$group['group_buy']['type'] = 'group_buy';
					$group['group_buy'][$group['group_buy']['type']]['goods_sn'] .= $data['goods_sn'] . ',';
					break;

				case GAT_AUCTION:
					$auction['auction']['type'] = 'auction';
					$auction['auction'][$auction['auction']['type']]['goods_sn'] .= $data['goods_sn'] . ',';
					break;
				}
			}
		}

		$sql = 'SELECT ga.act_id, ga.act_type, g.goods_sn FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga, ' . $GLOBALS['ecs']->table('package_goods') . ' pg,' . $GLOBALS['ecs']->table('goods') . ' g' . ' WHERE ga.act_id = pg.package_id AND pg.goods_id = g.goods_id AND pg.goods_id ' . db_create_in($goods_id) . ' GROUP BY pg.goods_id';
		$res = $GLOBALS['db']->getAll($sql);

		if ($res) {
			foreach ($res as $data) {
				switch ($data['act_type']) {
				case GAT_PACKAGE:
					$package['package']['type'] = 'package';
					$package['package'][$package['package']['type']]['goods_sn'] .= $data['goods_sn'] . ',';
					break;
				}
			}
		}

		$arr = array_merge($snatch, $group, $auction, $package);
	}

	return $arr;
}

function is_seckill($goods_id)
{
	$goods_sn = '';

	if (!empty($goods_id)) {
		$gmtime = gmtime();
		$sql = 'SELECT GROUP_CONCAT(g.goods_sn) AS goods_sn FROM ' . $GLOBALS['ecs']->table('seckill_goods') . ' AS sg, ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . ' WHERE sg.goods_id = g.goods_id AND sg.goods_id ' . db_create_in($goods_id);
		$goods_sn = $GLOBALS['db']->getOne($sql);
		$goods_sn = !empty($goods_sn) ? explode(',', $goods_sn) : '';
		$goods_sn = !empty($goods_sn) ? array_unique($goods_sn) : '';
		$goods_sn = !empty($goods_sn) ? implode(',', $goods_sn) : '';
	}

	return $goods_sn;
}

function get_promotion_info($goods_id = '', $ru_id = 0, $goods = array())
{
	$snatch = array();
	$group = array();
	$auction = array();
	$package = array();
	$favourable = array();
	$list_array = array();
	$gmtime = gmtime();
	$sql = 'SELECT act_id, act_name, act_type, start_time, end_time FROM ' . $GLOBALS['ecs']->table('goods_activity') . (' WHERE review_status = 3 AND is_finished=0 AND start_time <= \'' . $gmtime . '\' AND end_time >= \'' . $gmtime . '\' AND user_id = \'' . $ru_id . '\'');

	if (!empty($goods_id)) {
		$sql .= ' AND goods_id = \'' . $goods_id . '\'';
	}

	$sql .= ' LIMIT 15';
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $data) {
		switch ($data['act_type']) {
		case GAT_SNATCH:
			$snatch[$data['act_id']]['act_name'] = $data['act_name'];
			$snatch[$data['act_id']]['url'] = build_uri('snatch', array('sid' => $data['act_id']));
			$snatch[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
			$snatch[$data['act_id']]['sort'] = $data['start_time'];
			$snatch[$data['act_id']]['type'] = 'snatch';
			break;

		case GAT_GROUP_BUY:
			$group[$data['act_id']]['act_name'] = $data['act_name'];
			$group[$data['act_id']]['url'] = build_uri('group_buy', array('gbid' => $data['act_id']));
			$group[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
			$group[$data['act_id']]['sort'] = $data['start_time'];
			$group[$data['act_id']]['type'] = 'group_buy';
			break;

		case GAT_AUCTION:
			$auction[$data['act_id']]['act_name'] = $data['act_name'];
			$auction[$data['act_id']]['url'] = build_uri('auction', array('auid' => $data['act_id']));
			$auction[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
			$auction[$data['act_id']]['sort'] = $data['start_time'];
			$auction[$data['act_id']]['type'] = 'auction';
			break;

		case GAT_PACKAGE:
			$package[$data['act_id']]['act_name'] = $data['act_name'];
			$package[$data['act_id']]['url'] = 'package.php#' . $data['act_id'];
			$package[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
			$package[$data['act_id']]['sort'] = $data['start_time'];
			$package[$data['act_id']]['type'] = 'package';
			break;
		}
	}

	if (0 < $ru_id) {
		$ext_where = '';

		if ($GLOBALS['_CFG']['region_store_enabled']) {
			$ext_where = ' OR userFav_type_ext <> \'\' ';
		}

		$fav_where = '(user_id = \'' . $ru_id . '\' OR userFav_type = 1 ' . $ext_where . ' )';
	}
	else {
		$fav_where = 'user_id = \'' . $ru_id . '\'';
	}

	$user_rank = ',' . $_SESSION['user_rank'] . ',';
	$favourable = array();
	$ext_where = '';

	if ($GLOBALS['_CFG']['region_store_enabled']) {
		$ext_where = ', userFav_type_ext, rs_id ';
	}

	$sql = 'SELECT act_id, act_range, act_range_ext, act_name, start_time, end_time, act_type, userFav_type $ext_where FROM ' . $GLOBALS['ecs']->table('favourable_activity') . (' WHERE review_status = 3 AND start_time <= \'' . $gmtime . '\' AND end_time >= \'' . $gmtime . '\' AND ') . $fav_where;

	if (!empty($goods_id)) {
		$sql .= ' AND CONCAT(\',\', user_rank, \',\') LIKE \'%' . $user_rank . '%\'';
	}

	$sql .= ' LIMIT 15';
	$res = $GLOBALS['db']->getAll($sql);

	if (empty($goods_id)) {
		foreach ($res as $rows) {
			$favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
			$favourable[$rows['act_id']]['url'] = 'activity.php';
			$favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
			$favourable[$rows['act_id']]['sort'] = $rows['start_time'];
			$favourable[$rows['act_id']]['type'] = 'favourable';
			$favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
		}
	}
	else {
		if ($goods) {
			$category_id = isset($goods['cat_id']) && !empty($goods['cat_id']) ? $goods['cat_id'] : 0;
			$brand_id = isset($goods['brand_id']) && !empty($goods['brand_id']) ? $goods['brand_id'] : 0;
		}
		else {
			$sql = 'SELECT g.cat_id, g.brand_id FROM ' . $GLOBALS['ecs']->table('goods') . ' as g' . (' WHERE g.goods_id = \'' . $goods_id . '\' LIMIT 1');
			$row = $GLOBALS['db']->getRow($sql);
			$category_id = $row['cat_id'];
			$brand_id = $row['brand_id'];
		}

		foreach ($res as $rows) {
			if ($rows['act_range'] == FAR_ALL) {
				$mer_ids = true;

				if ($GLOBALS['_CFG']['region_store_enabled']) {
					$mer_ids = get_favourable_merchants($rows['userFav_type'], $rows['userFav_type_ext'], $rows['rs_id'], 1, $ru_id);
				}

				if ($mer_ids) {
					$favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
					$favourable[$rows['act_id']]['url'] = 'activity.php';
					$favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
					$favourable[$rows['act_id']]['sort'] = $rows['start_time'];
					$favourable[$rows['act_id']]['type'] = 'favourable';
					$favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
				}
			}
			else if ($rows['act_range'] == FAR_CATEGORY) {
				$id_list = array();
				$raw_id_list = explode(',', $rows['act_range_ext']);

				foreach ($raw_id_list as $id) {
					$cat_keys = get_array_keys_cat(intval($id));
					$list_array[$rows['act_id']][$id] = $cat_keys;
				}

				$list_array = !empty($list_array) ? array_merge($raw_id_list, $list_array[$rows['act_id']]) : $raw_id_list;
				$id_list = arr_foreach($list_array);
				$id_list = array_unique($id_list);
				$ids = join(',', array_unique($id_list));

				if (strpos(',' . $ids . ',', ',' . $category_id . ',') !== false) {
					$favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
					$favourable[$rows['act_id']]['url'] = 'activity.php';
					$favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
					$favourable[$rows['act_id']]['sort'] = $rows['start_time'];
					$favourable[$rows['act_id']]['type'] = 'favourable';
					$favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
				}
			}
			else if ($rows['act_range'] == FAR_BRAND) {
				$rows['act_range_ext'] = return_act_range_ext($rows['act_range_ext'], $rows['userFav_type'], $rows['act_range']);

				if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $brand_id . ',') !== false) {
					$favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
					$favourable[$rows['act_id']]['url'] = 'activity.php';
					$favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
					$favourable[$rows['act_id']]['sort'] = $rows['start_time'];
					$favourable[$rows['act_id']]['type'] = 'favourable';
					$favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
				}
			}
			else if ($rows['act_range'] == FAR_GOODS) {
				if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $goods_id . ',') !== false) {
					$mer_ids = true;

					if ($GLOBALS['_CFG']['region_store_enabled']) {
						$mer_ids = get_favourable_merchants($rows['userFav_type'], $rows['userFav_type_ext'], $rows['rs_id'], 1, $ru_id);
					}

					if ($mer_ids) {
						$favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
						$favourable[$rows['act_id']]['url'] = 'activity.php';
						$favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
						$favourable[$rows['act_id']]['sort'] = $rows['start_time'];
						$favourable[$rows['act_id']]['type'] = 'favourable';
						$favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
					}
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

function cat_list($cat_id = 0, $type = 0, $getrid = 0, $table = 'category', $seller_shop_cat = array(), $cat_level = 0, $user_id = 0)
{
	if ($getrid == 0) {
		$select = ', cat_name, cat_alias_name';

		if ($table == 'merchants_category') {
			$select .= ', user_id';
		}
		else if ($table == 'category') {
			$select .= ', cat_icon, style_icon';
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

	if ($table != 'presale_cat') {
		$where .= ' AND is_show = 1';
	}

	$sql = 'SELECT cat_id ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE parent_id = \'' . $cat_id . '\' ' . $where . '  ORDER BY sort_order ASC, cat_id ASC');
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

			if (defined('THEME_EXTENSION') && $getrid == 0 && $table == 'category') {
				$arr[$row['cat_id']]['cat_icon'] = $row['cat_icon'];
				$arr[$row['cat_id']]['style_icon'] = $row['style_icon'];
			}
		}
	}

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
				if (judge_supplier_enabled()) {
					$type_cat = 'w.cat_id ';
				}
				else {
					$type_cat = 'w.wholesale_cat_id ';
				}
			}
			else if ($type == 5) {
				$type_cat = 'a.cat_id ';
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
		$cat_keys = get_del_str_comma($cat_keys);
		return $cat_keys;
	}
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

	$admin_id = get_admin_id();

	if (is_null($username)) {
		$username = $GLOBALS['db']->getOne('SELECT user_name FROM ' . $GLOBALS['ecs']->table('admin_user') . (' WHERE user_id = \'' . $admin_id . '\''), true);
	}

	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('order_action') . ' (order_id, action_user, order_status, shipping_status, pay_status, action_place, action_note, log_time) ' . 'SELECT ' . ('order_id, \'' . $username . '\', \'' . $order_status . '\', \'' . $shipping_status . '\', \'' . $pay_status . '\', \'' . $place . '\', \'' . $note . '\', \'' . $log_time . '\' ') . 'FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_sn = \'' . $order_sn . '\'');
	$GLOBALS['db']->query($sql);
}

function price_format($price = 0, $change_price = true)
{
	if (empty($price)) {
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

function order_virtual_card_count($order_id = 0)
{
	$sql = 'SELECT goods_id  FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' AND is_real = 0 AND (goods_number - send_number) > 0 AND extension_code = \'virtual_card\' ');
	$goods_list = $GLOBALS['db']->getAll($sql);
	$is_number = 0;

	if ($goods_list) {
		foreach ($goods_list as $key => $row) {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('virtual_card') . ' WHERE goods_id = \'' . $row['goods_id'] . '\' AND is_saled = 0 AND order_sn = \'\'';

			if (!$GLOBALS['db']->getOne($sql)) {
				$is_number = 1;
				continue;
			}
		}
	}

	return $is_number;
}

function get_virtual_goods($order_id, $shipping = false)
{
	if ($shipping) {
		$sql = 'SELECT goods_id, goods_name, send_number AS num, extension_code FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' AND extension_code = \'virtual_card\'');
	}
	else {
		$sql = 'SELECT goods_id, goods_name, (goods_number - send_number) AS num, extension_code FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' AND is_real = 0 AND (goods_number - send_number) > 0 AND extension_code = \'virtual_card\' ');
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
	include_once ROOT_PATH . 'includes/lib_code.php';
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('virtual_card') . (' WHERE goods_id = \'' . $goods['goods_id'] . '\' AND is_saled = 0 ');
	$num = $GLOBALS['db']->GetOne($sql);

	if ($num < $goods['num']) {
		$msg .= sprintf($GLOBALS['_LANG']['virtual_card_oos'], $goods['goods_name']);
		return false;
	}

	$sql = 'SELECT card_id, card_sn, card_password, end_date, crc32 FROM ' . $GLOBALS['ecs']->table('virtual_card') . (' WHERE goods_id = \'' . $goods['goods_id'] . '\' AND is_saled = 0  LIMIT ') . $goods['num'];
	$arr = $GLOBALS['db']->getAll($sql);
	$card_ids = array();
	$cards = array();

	foreach ($arr as $virtual_card) {
		$card_info = array();
		if ($virtual_card['crc32'] == 0 || $virtual_card['crc32'] == crc32(AUTH_KEY)) {
			$card_info['card_sn'] = decrypt($virtual_card['card_sn']);
			$card_info['card_password'] = decrypt($virtual_card['card_password']);
		}
		else if ($virtual_card['crc32'] == crc32(OLD_AUTH_KEY)) {
			$card_info['card_sn'] = decrypt($virtual_card['card_sn'], OLD_AUTH_KEY);
			$card_info['card_password'] = decrypt($virtual_card['card_password'], OLD_AUTH_KEY);
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

	if (!$GLOBALS['db']->query($sql, 'SILENT')) {
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

		if (!$GLOBALS['db']->query($sql, 'SILENT')) {
			$msg .= $GLOBALS['db']->error();
			return false;
		}
	}

	$GLOBALS['smarty']->assign('virtual_card', $cards);
	$GLOBALS['smarty']->assign('order', $order);
	$GLOBALS['smarty']->assign('goods', $goods);
	$GLOBALS['smarty']->assign('send_time', date('Y-m-d H:i:s'));
	$GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
	$GLOBALS['smarty']->assign('send_date', date('Y-m-d'));
	$GLOBALS['smarty']->assign('sent_date', date('Y-m-d'));
	$tpl = get_mail_template('virtual_card');
	$content = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);
	send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']);
	return true;
}

function virtual_card_result($order_sn, $goods)
{
	include_once ROOT_PATH . 'includes/lib_code.php';
	$sql = 'SELECT card_sn, card_password, end_date, crc32 FROM ' . $GLOBALS['ecs']->table('virtual_card') . (' WHERE goods_id= \'' . $goods['goods_id'] . '\' AND order_sn = \'' . $order_sn . '\' ');
	$res = $GLOBALS['db']->query($sql);
	$cards = array();

	while ($row = $GLOBALS['db']->FetchRow($res)) {
		if ($row['crc32'] == 0 || $row['crc32'] == crc32(AUTH_KEY)) {
			$row['card_sn'] = decrypt($row['card_sn']);
			$row['card_password'] = decrypt($row['card_password']);
		}
		else if ($row['crc32'] == crc32(OLD_AUTH_KEY)) {
			$row['card_sn'] = decrypt($row['card_sn'], OLD_AUTH_KEY);
			$row['card_password'] = decrypt($row['card_password'], OLD_AUTH_KEY);
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
	$sql = 'SELECT (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('snatch_log') . (' AS lg2  WHERE lg2.snatch_id = \'' . $id . '\') as num, lg.user_id, lg.bid_price, lg.bid_time FROM ') . $GLOBALS['ecs']->table('snatch_log') . (' AS lg WHERE lg.snatch_id = \'' . $id . '\' ORDER BY num, lg.bid_price ASC, lg.bid_time ASC LIMIT 1');
	$rec = $GLOBALS['db']->GetRow($sql);

	if ($rec) {
		$sql = 'SELECT user_name, email FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $rec['user_id'] . '\' LIMIT 1';
		$user_info = $GLOBALS['db']->GetRow($sql);
		$rec['user_name'] = $user_info['user_name'];
		$rec['email'] = $user_info['email'];
		$rec['bid_time'] = local_date($GLOBALS['_CFG']['time_format'], $rec['bid_time']);
		$rec['formated_bid_price'] = price_format($rec['bid_price'], false);
		$sql = "SELECT ext_info \" .\r\n               \" FROM " . $GLOBALS['ecs']->table('goods_activity') . (' WHERE review_status = 3 AND act_id= \'' . $id . '\' AND act_type=') . GAT_SNATCH . ' LIMIT 1';
		$row = $GLOBALS['db']->getOne($sql);
		$info = unserialize($row);

		if (!empty($info['max_price'])) {
			$rec['buy_price'] = $info['max_price'] < $rec['bid_price'] ? $info['max_price'] : $rec['bid_price'];
		}
		else {
			$rec['buy_price'] = $rec['bid_price'];
		}

		$sql = 'SELECT COUNT(*)' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE extension_code = \'snatch\'' . (' AND extension_id = \'' . $id . '\'') . ' AND order_status ' . db_create_in(array(OS_CONFIRMED, OS_UNCONFIRMED, OS_SPLITED, OS_SPLITING_PART));
		$rec['order_count'] = $GLOBALS['db']->getOne($sql);
	}

	return $rec;
}

function clear_tpl_files($is_cache = true, $ext = '', $filename = '')
{
	if (empty($filename)) {
		$filename = 'admin';
	}

	if ($GLOBALS['_CFG']['open_memcached'] == 1) {
		return $GLOBALS['cache']->clear();
	}

	$dirs = array();
	if (isset($GLOBALS['shop_id']) && 0 < $GLOBALS['shop_id']) {
		$tmp_dir = DATA_DIR;
	}
	else {
		$tmp_dir = 'temp';
	}

	if ($is_cache) {
		$cache_dir = ROOT_PATH . $tmp_dir . '/caches/';
		$dirs[] = ROOT_PATH . $tmp_dir . '/query_caches/';
		$dirs[] = ROOT_PATH . $tmp_dir . '/static_caches/';

		for ($i = 0; $i < 16; $i++) {
			$hash_dir = $cache_dir . dechex($i);
			$dirs[] = $hash_dir . '/';
		}
	}
	else {
		$dirs[] = ROOT_PATH . $tmp_dir . '/compiled/';
		$dirs[] = ROOT_PATH . $tmp_dir . '/compiled/' . $filename . '/';
	}

	$str_len = strlen($ext);
	$count = 0;

	foreach ($dirs as $dir) {
		$folder = @opendir($dir);

		if ($folder === false) {
			continue;
		}

		while ($file = readdir($folder)) {
			if ($file == '.' || $file == '..' || $file == 'index.htm' || $file == 'index.html' || $file == '.gitignore') {
				continue;
			}

			if (is_file($dir . $file)) {
				$pos = $is_cache ? strrpos($file, '_') : strrpos($file, '.');
				if (0 < $str_len && $pos !== false) {
					$ext_str = substr($file, 0, $pos);

					if ($ext_str == $ext) {
						if (@unlink($dir . $file)) {
							$count++;
						}
					}
				}
				else if (@unlink($dir . $file)) {
					$count++;
				}
			}
		}

		closedir($folder);
	}

	return $count;
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

function setRewrite($initUrl = '', $params = '', $append = '', $page = 0, $keywords = '', $size = 0)
{
	$url = false;
	$rewrite = intval($GLOBALS['_CFG']['rewrite']);
	$baseUrl = basename($initUrl);
	$urlArr = explode('?', $baseUrl);
	if ($rewrite && !empty($urlArr[0]) && strpos($urlArr[0], '.php')) {
		$app = str_replace('.php', '', $urlArr[0]);
		@parse_str($urlArr[1], $queryArr);

		if (isset($queryArr['id'])) {
			$id = intval($queryArr['id']);
		}

		if (!empty($id)) {
			switch ($app) {
			case 'history_list':
				$idType = array('cid' => $id);
				break;

			case 'category':
				$idType = array('cid' => $id);
				break;

			case 'goods':
				$idType = array('gid' => $id);
				break;

			case 'presale':
				$idType = array('presaleid' => $id);
				break;

			case 'brand':
				$idType = array('bid' => $id);
				break;

			case 'brandn':
				$idType = array('bid' => $id);
				break;

			case 'article_cat':
				$idType = array('acid' => $id);
				break;

			case 'article':
				$idType = array('aid' => $id);
				break;

			case 'merchants':
				$idType = array('mid' => $id);
				break;

			case 'merchants_index':
				$idType = array('urid' => $id);
				break;

			case 'group_buy':
				$idType = array('gbid' => $id);
				break;

			case 'seckill':
				$idType = array('secid' => $id);
				break;

			case 'auction':
				$idType = array('gbid' => $id);
				break;

			case 'snatch':
				$idType = array('sid' => $id);
				break;

			case 'exchange':
				$idType = array('cid' => $id);
				break;

			case 'exchange_goods':
				$idType = array('gid' => $id);
				break;

			case 'gift_gard':
				$idType = array('cid' => $id);
				break;

			default:
				$idType = array('id' => '');
				break;
			}
		}
		else {
			switch ($app) {
			case 'index':
				$idType = NULL;
				break;

			case 'brand':
				$idType = NULL;
				break;

			case 'brandn':
				$idType = NULL;
				break;

			case 'group_buy':
				$idType = NULL;
				break;

			case 'seckill':
				$idType = NULL;
				break;

			case 'auction':
				$idType = NULL;
				break;

			case 'package':
				$idType = NULL;
				break;

			case 'activity':
				$idType = NULL;
				break;

			case 'snatch':
				$idType = NULL;
				break;

			case 'exchange':
				$idType = NULL;
				break;

			case 'store_street':
				$idType = NULL;
				break;

			case 'presale':
				$idType = NULL;
				break;

			case 'categoryall':
				$idType = NULL;
				break;

			case 'merchants':
				$idType = NULL;
				break;

			case 'merchants_index':
				$idType = NULL;
				break;

			case 'message':
				$idType = NULL;
				break;

			case 'wholesale':
				$idType = NULL;
				break;

			case 'gift_gard':
				$idType = NULL;
				break;

			case 'history_list':
				$idType = NULL;
				break;

			case 'merchants_steps':
				$idType = NULL;
				break;

			case 'merchants_steps_site':
				$idType = NULL;
				break;

			default:
				$idType = array('id' => '');
				break;
			}
		}

		if ($idType == NULL) {
			$url = $GLOBALS['_CFG']['site_domain'] . $app . '.html';
		}
		else {
			$params = empty($params) ? $idType : $params;
			$url = build_uri($app, $params, $append, $page, $keywords, $size);
		}
	}

	if ($url) {
		return $url;
	}
	else {
		if (strpos($initUrl, 'http://') === false && strpos($initUrl, 'https://') === false) {
			return $GLOBALS['_CFG']['site_domain'] . $initUrl;
		}
		else {
			return $initUrl;
		}
	}
}

function build_uri($app, $params, $append = '', $page = 0, $keywords = '', $size = 0)
{
	static $rewrite;

	if ($rewrite === NULL) {
		$rewrite = intval($GLOBALS['_CFG']['rewrite']);
	}

	$args = array('cid' => 0, 'gid' => 0, 'bid' => 0, 'acid' => 0, 'aid' => 0, 'mid' => 0, 'urid' => 0, 'ubrand' => 0, 'chkw' => '', 'is_ship' => '', 'hid' => 0, 'sid' => 0, 'gbid' => 0, 'auid' => 0, 'sort' => '', 'order' => '', 'status' => -1, 'secid' => 0, 'tmr' => 0);
	extract(array_merge($args, $params));
	$uri = '';

	switch ($app) {
	case 'history_list':
		if ($rewrite) {
			$uri = 'history_list-' . $cid;

			if (!empty($page)) {
				$uri .= '-' . $page;
			}
		}
		else {
			$uri = 'history_list.php?cat_id=' . $cid;

			if (!empty($page)) {
				$uri .= '&amp;page=' . $page;
			}
		}

		break;

	case 'category':
		if (empty($cid)) {
			return false;
		}
		else if ($rewrite) {
			$uri = 'category-' . $cid;
			if (isset($bid) && !empty($bid)) {
				$uri .= '-b' . $bid;
			}

			if (isset($ubrand) && !empty($ubrand)) {
				$uri .= '-ubrand' . $ubrand;
			}

			if (isset($price_min)) {
				$uri .= '-min' . $price_min;
			}

			if (isset($price_max)) {
				$uri .= '-max' . $price_max;
			}

			if (isset($filter_attr) && $filter_attr) {
				$uri .= '-attr' . $filter_attr;
			}

			if (isset($ship) && !empty($ship)) {
				$uri .= '-ship' . $ship;
			}

			if (isset($self) && !empty($self)) {
				$uri .= '-self' . $self;
			}

			if (isset($have) && !empty($have)) {
				$uri .= '-have' . $have;
			}

			if (!empty($page)) {
				$uri .= '-' . $page;
			}

			if (!empty($sort)) {
				$uri .= '-' . $sort;
			}

			if (!empty($order)) {
				$uri .= '-' . $order;
			}
		}
		else {
			$uri = 'category.php?id=' . $cid;

			if (!empty($bid)) {
				$uri .= '&amp;brand=' . $bid;
			}

			if (!empty($ubrand)) {
				$uri .= '&amp;ubrand=' . $ubrand;
			}

			if (isset($price_min) && !empty($price_min)) {
				$uri .= '&amp;price_min=' . $price_min;
			}

			if (isset($price_max) && !empty($price_max)) {
				$uri .= '&amp;price_max=' . $price_max;
			}

			if (isset($filter_attr) && !empty($filter_attr)) {
				$uri .= '&amp;filter_attr=' . $filter_attr;
			}

			if (isset($ship) && !empty($ship)) {
				$uri .= '&amp;ship=' . $ship;
			}

			if (isset($self) && !empty($self)) {
				$uri .= '&amp;self=' . $self;
			}

			if (isset($have) && !empty($have)) {
				$uri .= '&amp;have=' . $have;
			}

			if (!empty($page)) {
				$uri .= '&amp;page=' . $page;
			}

			if (!empty($sort)) {
				$uri .= '&amp;sort=' . $sort;
			}

			if (!empty($order)) {
				$uri .= '&amp;order=' . $order;
			}
		}

		break;

	case 'wholesale':
		if (empty($cid) && empty($act)) {
			return false;
		}
		else if ($rewrite) {
			$uri = 'wholesale';

			if (!empty($cid)) {
				$uri .= '-' . $cid;
			}

			if (!empty($cid)) {
				$uri .= '-c' . $cid;
			}

			if (isset($status) && $status != -1) {
				$uri .= '-status' . $status;
			}

			if (!empty($act)) {
				$uri .= '-' . $act;
			}
		}
		else {
			$uri = 'wholesale.php?';

			if (!empty($act)) {
				$uri .= 'act=' . $act;
			}

			if (!empty($cid)) {
				$uri .= '&amp;id=' . $cid;
			}

			if (isset($status) && $status != -1) {
				$uri .= '&amp;status=' . $status;
			}
		}

		break;

	case 'wholesale_cat':
		if (empty($cid) && empty($act)) {
			return false;
		}
		else if ($rewrite) {
			$uri = 'wholesale_cat';

			if (!empty($cid)) {
				$uri .= '-' . $cid;
			}

			if (isset($status) && $status != -1) {
				$uri .= '-status' . $status;
			}

			if (!empty($act)) {
				$uri .= '-' . $act;
			}
		}
		else {
			$uri = 'wholesale_cat.php?';

			if (!empty($cid)) {
				$uri .= 'id=' . $cid;
			}

			if (isset($status) && $status != -1) {
				$uri .= '&amp;status=' . $status;
			}

			if (!empty($act)) {
				$uri .= '&amp;act=' . $act;
			}

			if (!empty($page)) {
				$uri .= '&amp;page=' . $page;
			}
		}

		break;

	case 'wholesale_goods':
		if (empty($aid)) {
			return false;
		}
		else {
			$uri = $rewrite ? 'wholesale_goods-' . $aid : 'wholesale_goods.php?id=' . $aid;
		}

		break;

	case 'wholesale_purchase':
		if (empty($gid) && empty($act)) {
			return false;
		}
		else if ($rewrite) {
			$uri = 'wholesale_purchase';

			if (!empty($gid)) {
				$uri .= '-' . $gid;
			}

			if (!empty($act)) {
				$uri .= '-' . $act;
			}
		}
		else {
			$uri = 'wholesale_purchase.php?';

			if (!empty($gid)) {
				$uri .= 'id=' . $gid;
			}

			if (!empty($act)) {
				$uri .= '&amp;act=' . $act;
			}
		}

		break;

	case 'goods':
		if (empty($gid)) {
			return false;
		}
		else {
			$uri = $rewrite ? 'goods-' . $gid : 'goods.php?id=' . $gid;
		}

		break;

	case 'presale':
		if (empty($presaleid) && empty($act)) {
			return false;
		}
		else if ($rewrite) {
			$uri = 'presale';

			if (!empty($presaleid)) {
				$uri .= '-' . $presaleid;
			}

			if (!empty($cid)) {
				$uri .= '-c' . $cid;
			}

			if (isset($status) && $status != -1) {
				$uri .= '-status' . $status;
			}

			if (!empty($act)) {
				$uri .= '-' . $act;
			}
		}
		else {
			$uri = 'presale.php?';

			if (!empty($presaleid)) {
				$uri .= 'id=' . $presaleid;
			}

			if (!empty($cid)) {
				$uri .= 'cat_id=' . $cid;
			}

			if (isset($status) && $status != -1) {
				$uri .= '&amp;status=' . $status;
			}

			if (!empty($act)) {
				$uri .= '&amp;act=' . $act;
			}
		}

		break;

	case 'categoryall':
		if (empty($urid)) {
			return false;
		}
		else if ($rewrite) {
			$uri = 'categoryall';

			if (!empty($urid)) {
				$uri .= '-' . $urid;
			}
		}
		else {
			$uri = 'categoryall.php';

			if (!empty($urid)) {
				$uri .= '?id=' . $urid;
			}
		}

		break;

	case 'brand':
		if (empty($bid)) {
			return false;
		}
		else if ($rewrite) {
			$uri = 'brand-' . $bid;

			if (!empty($mbid)) {
				$uri .= '-mbid' . $mbid;
			}

			if (!empty($cid)) {
				$uri .= '-c' . $cid;
			}

			if (isset($price_min) && !empty($price_min)) {
				$uri .= '-min' . $price_min;
			}

			if (isset($price_max) && !empty($price_max)) {
				$uri .= '-max' . $price_max;
			}

			if (isset($ship) && !empty($ship)) {
				$uri .= '-ship' . $ship;
			}

			if (isset($self) && !empty($self)) {
				$uri .= '-self' . $self;
			}

			if (!empty($page)) {
				$uri .= '-' . $page;
			}

			if (!empty($sort)) {
				$uri .= '-' . $sort;
			}

			if (!empty($order)) {
				$uri .= '-' . $order;
			}
		}
		else {
			$uri = 'brand.php?id=' . $bid;

			if (!empty($mbid)) {
				$uri .= '&amp;mbid=' . $mbid;
			}

			if (!empty($cid)) {
				$uri .= '&amp;cat=' . $cid;
			}

			if (isset($price_min)) {
				$uri .= '&amp;price_min=' . $price_min;
			}

			if (isset($price_max)) {
				$uri .= '&amp;price_max=' . $price_max;
			}

			if (isset($ship) && !empty($ship)) {
				$uri .= '&amp;ship=' . $ship;
			}

			if (isset($self) && !empty($self)) {
				$uri .= '&amp;self=' . $self;
			}

			if (!empty($page)) {
				$uri .= '&amp;page=' . $page;
			}

			if (!empty($sort)) {
				$uri .= '&amp;sort=' . $sort;
			}

			if (!empty($order)) {
				$uri .= '&amp;order=' . $order;
			}
		}

		break;

	case 'brandn':
		if (empty($bid)) {
			return false;
		}
		else if ($rewrite) {
			$uri = 'brandn-' . $bid;
			if (isset($cid) && !empty($cid)) {
				$uri .= '-c' . $cid;
			}

			if (!empty($page)) {
				$uri .= '-' . $page;
			}

			if (!empty($sort)) {
				$uri .= '-' . $sort;
			}

			if (!empty($order)) {
				$uri .= '-' . $order;
			}

			if (!empty($act)) {
				$uri .= '-' . $act;
			}
		}
		else {
			$uri = 'brandn.php?id=' . $bid;

			if (!empty($cid)) {
				$uri .= '&amp;cat=' . $cid;
			}

			if (!empty($page)) {
				$uri .= '&amp;page=' . $page;
			}

			if (isset($price_min)) {
				$uri .= '&amp;price_min=' . $price_min;
			}

			if (isset($price_max)) {
				$uri .= '&amp;price_max=' . $price_max;
			}

			if (isset($is_ship) && !empty($is_ship)) {
				$uri .= '&amp;is_ship=' . $is_ship;
			}

			if (!empty($sort)) {
				$uri .= '&amp;sort=' . $sort;
			}

			if (!empty($order)) {
				$uri .= '&amp;order=' . $order;
			}

			if (!empty($act)) {
				$uri .= '&amp;act=' . $act;
			}
		}

		break;

	case 'article_cat':
		if (empty($acid)) {
			return false;
		}
		else if ($rewrite) {
			$uri = 'article_cat-' . $acid;

			if (!empty($page)) {
				$uri .= '-' . $page;
			}

			if (!empty($sort)) {
				$uri .= '-' . $sort;
			}

			if (!empty($order)) {
				$uri .= '-' . $order;
			}

			if (!empty($keywords)) {
				$uri .= '-' . $keywords;
			}
		}
		else {
			$uri = 'article_cat.php?id=' . $acid;

			if (!empty($page)) {
				$uri .= '&amp;page=' . $page;
			}

			if (!empty($sort)) {
				$uri .= '&amp;sort=' . $sort;
			}

			if (!empty($order)) {
				$uri .= '&amp;order=' . $order;
			}

			if (!empty($keywords)) {
				$uri .= '&amp;keywords=' . $keywords;
			}
		}

		break;

	case 'article':
		if (empty($aid)) {
			return false;
		}
		else {
			$uri = $rewrite ? 'article-' . $aid : 'article.php?id=' . $aid;
		}

		break;

	case 'merchants':
		if (empty($mid)) {
			return false;
		}
		else {
			$uri = $rewrite ? 'merchants-' . $mid : 'merchants.php?id=' . $mid;
		}

		break;

	case 'merchants_index':
		if (empty($urid) && empty($merchant_id)) {
			return false;
		}
		else {
			if ($urid) {
				if ($rewrite) {
					$uri = '';
					$uri .= 'merchants_index-' . $urid;
				}
				else {
					$uri = 'merchants_index.php?merchant_id=' . $urid;
				}
			}

			if ($merchant_id) {
				if ($rewrite) {
					$uri = '';
					$uri .= 'merchants_index-' . $merchant_id;
				}
				else {
					$uri = 'merchants_index.php?merchant_id=' . $merchant_id;
				}
			}
		}

		break;

	case 'merchants_store':
		if (empty($urid)) {
			return false;
		}
		else if ($rewrite) {
			$uri = '';
			if (isset($domain_name) && !empty($domain_name)) {
				$uri .= $domain_name . '/';
			}

			$uri .= 'merchants_store-' . $urid;

			if (!empty($cid)) {
				$uri .= '-c' . $cid;
			}

			if (!empty($bid)) {
				$uri .= '-b' . $bid;
			}

			if (!empty($keyword)) {
				$uri .= '-keyword' . $keyword;
			}

			if (isset($price_min)) {
				$uri .= '-min' . $price_min;
			}

			if (isset($price_max)) {
				$uri .= '-max' . $price_max;
			}

			if (isset($filter_attr)) {
				$uri .= '-attr' . $filter_attr;
			}

			if (!empty($page)) {
				$uri .= '-' . $page;
			}

			if (!empty($sort)) {
				$uri .= '-' . $sort;
			}

			if (!empty($order)) {
				$uri .= '-' . $order;
			}
		}
		else {
			$uri = 'merchants_store.php?merchant_id=' . $urid;

			if (!empty($cid)) {
				$uri .= '&amp;id=' . $cid;
			}

			if (!empty($bid)) {
				$uri .= '&amp;brand=' . $bid;
			}

			if (!empty($keyword)) {
				$uri .= '&amp;keyword=' . $keyword;
			}

			if (isset($price_min)) {
				$uri .= '&amp;price_min=' . $price_min;
			}

			if (isset($price_max)) {
				$uri .= '&amp;price_max=' . $price_max;
			}

			if (!empty($filter_attr)) {
				$uri .= '&amp;filter_attr=' . $filter_attr;
			}

			if (!empty($page)) {
				$uri .= '&amp;page=' . $page;
			}

			if (!empty($sort)) {
				$uri .= '&amp;sort=' . $sort;
			}

			if (!empty($order)) {
				$uri .= '&amp;order=' . $order;
			}
		}

		break;

	case 'merchants_store_shop':
		if (empty($urid)) {
			return false;
		}
		else if ($rewrite) {
			$uri .= 'merchants_store_shop-' . $urid;

			if (!empty($page)) {
				$uri .= '-' . $page;
			}

			if (!empty($sort)) {
				$uri .= '-' . $sort;
			}

			if (!empty($order)) {
				$uri .= '-' . $order;
			}
		}
		else {
			$uri = 'merchants_store_shop.php?id=' . $urid;

			if (!empty($page)) {
				$uri .= '&amp;page=' . $page;
			}

			if (!empty($sort)) {
				$uri .= '&amp;sort=' . $sort;
			}

			if (!empty($order)) {
				$uri .= '&amp;order=' . $order;
			}
		}

		break;

	case 'group_buy':
		if (empty($gbid)) {
			return false;
		}
		else {
			$uri = $rewrite ? 'group_buy-' . $gbid : 'group_buy.php?act=view&amp;id=' . $gbid;
		}

		break;

	case 'auction':
		if (empty($auid)) {
			return false;
		}
		else {
			$uri = $rewrite ? 'auction-' . $auid : 'auction.php?act=view&amp;id=' . $auid;
		}

		break;

	case 'snatch':
		if (empty($sid)) {
			return false;
		}
		else {
			$uri = $rewrite ? 'snatch-' . $sid : 'snatch.php?id=' . $sid;
		}

		break;

	case 'history_list':
		if (empty($hid)) {
			return false;
		}
		else {
			$uri = $rewrite ? 'history_list-' . $hid : 'history_list.php?act=user&amp;id=' . $hid;
		}

		break;

	case 'search':
		$uri = 'search.php?keywords=' . $chkw;

		if (!empty($bid)) {
			$uri .= '&amp;brand=' . $bid;
		}

		if (isset($price_min)) {
			$uri .= '&amp;price_min=' . $price_min;
		}

		if (isset($price_max)) {
			$uri .= '&amp;price_max=' . $price_max;
		}

		if (!empty($filter_attr)) {
			$uri .= '&amp;filter_attr=' . $filter_attr;
		}

		if (!empty($cou_id)) {
			$uri .= '&amp;cou_id=' . $cou_id;
		}

		break;

	case 'user':
		if (empty($act)) {
			return false;
		}
		else if ($rewrite) {
			$uri = 'user';

			if (!empty($act)) {
				$uri .= '-' . $act;
			}
		}
		else {
			$uri = 'user.php?';

			if (!empty($act)) {
				$uri .= 'act=' . $act;
			}
		}

		break;

	case 'exchange':
		if (empty($cid)) {
			if (!empty($page)) {
				$uri = 'exchange-' . $cid;

				if ($rewrite) {
					$uri .= '-' . $page;
				}
				else {
					$uri = 'exchange.php?';
					$uri .= 'page=' . $page;
				}
			}
			else {
				return false;
			}
		}
		else if ($rewrite) {
			$uri = 'exchange-' . $cid;

			if (isset($price_min)) {
				$uri .= '-min' . $price_min;
			}

			if (isset($price_max)) {
				$uri .= '-max' . $price_max;
			}

			if (!empty($page)) {
				$uri .= '-' . $page;
			}

			if (!empty($sort)) {
				$uri .= '-' . $sort;
			}

			if (!empty($order)) {
				$uri .= '-' . $order;
			}
		}
		else {
			$uri = 'exchange.php?cat_id=' . $cid;

			if (isset($price_min)) {
				$uri .= '&amp;integral_min=' . $price_min;
			}

			if (isset($price_max)) {
				$uri .= '&amp;integral_max=' . $price_max;
			}

			if (!empty($page)) {
				$uri .= '&amp;page=' . $page;
			}

			if (!empty($sort)) {
				$uri .= '&amp;sort=' . $sort;
			}

			if (!empty($order)) {
				$uri .= '&amp;order=' . $order;
			}
		}

		break;

	case 'exchange_goods':
		if (empty($gid)) {
			return false;
		}
		else {
			$uri = $rewrite ? 'exchange-id' . $gid : 'exchange.php?id=' . $gid . '&amp;act=view';
		}

		break;

	case 'gift_gard':
		if (empty($cid)) {
			return false;
		}
		else if ($rewrite) {
			$uri = 'gift_gard-' . $cid;

			if (!empty($page)) {
				$uri .= '-' . $page;
			}

			if (!empty($sort)) {
				$uri .= '-' . $sort;
			}

			if (!empty($order)) {
				$uri .= '-' . $order;
			}
		}
		else {
			$uri = 'gift_gard.php?cat_id=' . $cid;

			if (!empty($page)) {
				$uri .= '&amp;page=' . $page;
			}

			if (!empty($sort)) {
				$uri .= '&amp;sort=' . $sort;
			}

			if (!empty($order)) {
				$uri .= '&amp;order=' . $order;
			}
		}

		break;

	case 'seckill':
		if (empty($act)) {
			if (!empty($cid)) {
				$uri = $rewrite ? 'seckill-' . $cid : 'seckill.php?cat_id=' . $cid;
			}
			else {
				return false;
			}
		}
		else if ($rewrite) {
			$uri = 'seckill-' . $secid;

			if (!empty($act)) {
				$uri .= '-' . $act;
			}
		}
		else {
			$uri = 'seckill.php?id=' . $secid;

			if ($act == 'view') {
				$uri .= '&amp;act=view';
			}

			if ($tmr) {
				$uri .= '&tmr=1';
			}
		}

		break;

	default:
		return false;
		break;
	}

	if ($rewrite) {
		if ($rewrite == 2 && !empty($append)) {
			$uri .= '-' . urlencode(preg_replace('/[\\.|\\/|\\?|&|\\+|\\\\|\'|"|,]+/', '', $append));
		}

		if (!in_array($app, array('search'))) {
			$uri .= '.html';
		}
	}

	if ($rewrite == 2 && strpos(strtolower(EC_CHARSET), 'utf') !== 0) {
		$uri = urlencode($uri);
	}

	$site_domain = '';
	if (!isset($domain_name) && empty($domain_name)) {
		$site_domain = $GLOBALS['_CFG']['site_domain'];
	}

	return $site_domain . $uri;
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
	if ($change_desc && $order_type) {
		$change_desc_arr = explode(' ', $change_desc);

		if (2 <= count($change_desc_arr)) {
			$sql = 'SELECT order_id, main_order_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_sn = \'' . $change_desc_arr[1] . '\' LIMIT 1';
			$ordor_res = $GLOBALS['db']->getRow($sql);

			if ($ordor_res) {
				if (0 < $ordor_res['main_order_id']) {
					$sql = 'SELECT order_sn FROM ' . $GLOBALS['ecs']->table('order_info') . 'WHERE order_id = \'' . $ordor_res['main_order_id'] . '\' LIMIT 1';
					$ordor_main = $GLOBALS['db']->getRow($sql);
					$order_surplus_desc = sprintf($GLOBALS['_LANG']['return_order_surplus'], $ordor_main['order_sn']);
					$order_integral_desc = sprintf($GLOBALS['_LANG']['return_order_integral'], $ordor_main['order_sn']);
					$sql = 'SELECT log_id FROM ' . $GLOBALS['ecs']->table('account_log') . ' WHERE change_desc IN(' . '\'' . $order_surplus_desc . '\'' . ',\'' . $order_integral_desc . '\'' . ')';
					$log_res = $GLOBALS['db']->getAll($sql);

					if ($log_res) {
						$is_go = false;
					}
				}
				else {
					if ($ordor_res && 0 < $ordor_res['order_id']) {
						$sql = 'SELECT order_id, order_sn FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE main_order_id = \'' . $ordor_res['order_id'] . '\'';
						$main_ordor_res = $GLOBALS['db']->getAll($sql);

						if (0 < $main_ordor_res) {
							foreach ($main_ordor_res as $key => $row) {
								$order_surplus_desc = sprintf($GLOBALS['_LANG']['return_order_surplus'], $row['order_sn']);
								$order_integral_desc = sprintf($GLOBALS['_LANG']['return_order_integral'], $row['order_sn']);
								$main_change_desc = sprintf($GLOBALS['_LANG']['return_order_surplus'], $row['order_sn']);
								$sql = 'SELECT user_money, pay_points FROM ' . $GLOBALS['ecs']->table('account_log') . ' WHERE change_desc IN(' . '\'' . $order_surplus_desc . '\'' . ',\'' . $order_integral_desc . '\'' . ')';
								$parent_account_log = $GLOBALS['db']->getAll($sql);

								if ($user_money) {
									$is_user_money += $parent_account_log[0]['user_money'];
								}

								if ($pay_points) {
									$is_pay_points += $parent_account_log[1]['pay_points'];
								}
							}
						}
					}

					if ($user_money) {
						$user_money -= $is_user_money;
					}

					if ($pay_points) {
						$pay_points -= $is_pay_points;
					}
				}
			}
		}
	}

	if ($is_go && ($user_money || $frozen_money || $rank_points || $pay_points)) {
		$account_log = array('user_id' => $user_id, 'user_money' => $user_money, 'frozen_money' => $frozen_money, 'rank_points' => $rank_points, 'pay_points' => $pay_points, 'change_time' => gmtime(), 'change_desc' => $change_desc, 'change_type' => $change_type, 'deposit_fee' => $deposit_fee);
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('account_log'), $account_log, 'INSERT');
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . (' SET user_money = user_money + (\'' . $user_money . '\'+ \'' . $deposit_fee . '\'),') . (' frozen_money = frozen_money + (\'' . $frozen_money . '\'),') . (' rank_points = rank_points + (\'' . $rank_points . '\'),') . (' pay_points = pay_points + (\'' . $pay_points . '\')') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
		$GLOBALS['db']->query($sql);

		if (!judge_user_special_rank($user_id)) {
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
}

function log_seller_account_change($ru_id, $seller_money = 0, $frozen_money = 0)
{
	if ($seller_money || $frozen_money) {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' SET seller_money = seller_money + (\'' . $seller_money . '\'),') . (' frozen_money = frozen_money + (\'' . $frozen_money . '\')') . (' WHERE ru_id = \'' . $ru_id . '\' LIMIT 1');
		$GLOBALS['db']->query($sql);
	}
}

function merchants_account_log($ru_id, $user_money = 0, $frozen_money = 0, $change_desc, $change_type = 1)
{
	if ($user_money || $frozen_money) {
		$log = array('user_id' => $ru_id, 'user_money' => $user_money, 'frozen_money' => $frozen_money, 'change_time' => gmtime(), 'change_desc' => $change_desc, 'change_type' => $change_type);
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_account_log'), $log, 'INSERT');
	}
}

function article_cat_list_new($cat_id = 0, $selected = 0, $re_type = true, $level = 0)
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
			$select .= '<li><a href="javascript:;" cat_type="' . $var['cat_type'] . '" data-value="' . $var['cat_id'] . '" ';
			$select .= ' cat_type="' . $var['cat_type'] . '" class="ftx-01">';

			if (0 < $var['level']) {
				$select .= str_repeat('&nbsp;', $var['level'] * 4);
			}

			$select .= htmlspecialchars(addslashes(str_replace("\r\n", '', $var['cat_name']))) . '</a></li>';
		}

		return $select;
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
		include_once ROOT_PATH . 'uc_client/client.php';
	}

	$res = call_user_func_array($func, $params);
	set_error_handler('exception_handler');
	return $res;
}

function exception_handler($errno, $errstr, $errfile, $errline)
{
	return NULL;
}

function get_image_path($goods_id, $image = '', $thumb = false, $call = 'goods', $del = false, $retain = false)
{
	if (!empty($image) && (strpos($image, 'http://') === false && strpos($image, 'https://') === false && strpos($image, 'errorImg.png') === false)) {
		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();
			$image = $bucket_info['endpoint'] . $image;
		}
		else {
			$image = $GLOBALS['_CFG']['site_domain'] . $image;
		}
	}

	$return = is_admin_seller_path();

	if ($return < 4) {
		if (!empty($image) && (strpos($image, 'http://') === false && strpos($image, 'https://') === false && strpos($image, 'errorImg.png') === false)) {
			if ($return == 1) {
				$image = $GLOBALS['ecs']->url() . $image;
			}
			else if ($return == 2) {
				$image = $GLOBALS['ecs']->seller_url() . $image;
			}
			else if ($return == 3) {
				$image = $GLOBALS['ecs']->seller_url(SUPPLLY_PATH) . $image;
			}
			else {
				$image = $GLOBALS['ecs']->stores_url() . $image;
			}
		}
	}

	if ($retain) {
		$url = $image;
	}
	else {
		if ($return == 4) {
			$GLOBALS['_CFG']['no_picture'] = isset($GLOBALS['_CFG']['no_picture']) && !empty($GLOBALS['_CFG']['no_picture']) ? str_replace('../', '', $GLOBALS['_CFG']['no_picture']) : '';
		}

		$url = empty($image) ? $GLOBALS['_CFG']['no_picture'] : $image;
	}

	return $url;
}

function user_uc_call($func, $params = NULL)
{
	if (isset($GLOBALS['_CFG']['integrate_code']) && $GLOBALS['_CFG']['integrate_code'] == 'ucenter') {
		restore_error_handler();

		if (!function_exists($func)) {
			include_once ROOT_PATH . 'includes/lib_uc.php';
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
	$sql = 'SELECT `id` , `volume_number` , `volume_price`' . ' FROM ' . $GLOBALS['ecs']->table('volume_price') . '' . ' WHERE `goods_id` = \'' . $goods_id . '\' AND `price_type` = \'' . $price_type . '\'' . ' ORDER BY `volume_number`';
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $k => $v) {
		$volume_price[$temp_index]['id'] = $v['id'];
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

	$sql = "SELECT a.attr_type, v.attr_value, v.goods_attr_id, attr_checked\r\n            FROM " . $GLOBALS['ecs']->table('attribute') . " AS a\r\n            LEFT JOIN " . $GLOBALS['ecs']->table('goods_attr') . " AS v\r\n                ON v.attr_id = a.attr_id\r\n                AND a.attr_type = 1\r\n            WHERE v.goods_attr_id " . db_create_in($goods_attr_id_array) . ("\r\n            ORDER BY a.sort_order, a.attr_id, v.goods_attr_id " . $sort);
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

	$sql = "SELECT a.attr_type, v.attr_value, v.goods_attr_id\r\n            FROM " . $GLOBALS['ecs']->table('attribute') . " AS a\r\n            LEFT JOIN " . $GLOBALS['ecs']->table('goods_attr') . " AS v\r\n                ON v.attr_id = a.attr_id\r\n                AND a.attr_type = 1\r\n            WHERE v.goods_attr_id " . db_create_in($goods_attr_id_array) . ("\r\n            ORDER BY a.sort_order, a.attr_id, v.goods_attr_id " . $sort);
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

function get_package_info($id, $path = '')
{
	global $ecs;
	global $db;
	global $_CFG;
	$id = is_numeric($id) ? intval($id) : 0;
	$now = gmtime();
	$where = '';

	if (empty($path)) {
		$where = ' AND review_status = 3 ';
	}

	$sql = 'SELECT act_id AS id, user_id AS ru_id, act_name AS package_name, goods_id , goods_name, start_time, end_time, act_desc, ext_info, user_id, activity_thumb, review_status, review_content ' . ' FROM ' . $GLOBALS['ecs']->table('goods_activity') . (' WHERE act_id=\'' . $id . '\' AND act_type = ') . GAT_PACKAGE . $where;
	$package = $GLOBALS['db']->GetRow($sql);
	if ($package['start_time'] <= $now && $now <= $package['end_time']) {
		$package['is_on_sale'] = '1';
	}
	else {
		$package['is_on_sale'] = '0';
	}

	$package['start_time'] = local_date('Y-m-d H:i:s', $package['start_time']);
	$package['end_time'] = local_date('Y-m-d H:i:s', $package['end_time']);
	$row = unserialize($package['ext_info']);
	unset($package['ext_info']);

	if ($row) {
		foreach ($row as $key => $val) {
			$package[$key] = $val;
		}
	}

	$sql = 'SELECT pg.package_id, pg.goods_id, pg.goods_number, pg.admin_id, pg.product_id, ' . ' g.goods_sn, g.goods_name, g.market_price, g.goods_thumb, g.is_real, g.cloud_id, g.goods_number AS stock_number, ' . (' IFNULL(mp.user_price, g.shop_price * \'' . $_SESSION['discount'] . '\') AS rank_price ') . ' FROM ' . $GLOBALS['ecs']->table('package_goods') . ' AS pg ' . '   LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . '   ON g.goods_id = pg.goods_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ' WHERE pg.package_id = ' . $id . ' ' . ' ORDER BY pg.package_id, pg.goods_id';
	$goods_res = $GLOBALS['db']->getAll($sql);
	$market_price = 0;
	$real_goods_count = 0;
	$virtual_goods_count = 0;

	foreach ($goods_res as $key => $val) {
		if ($val['cloud_id']) {
			if ($val['product_id']) {
				$plugin_file = ROOT_PATH . '/plugins/cloudApi/cloudApi.php';
				$sql = 'SELECT cloud_product_id FROM' . $ecs->table('products') . 'WHERE product_id = \'' . $val['product_id'] . '\'';
				$productIds = $db->getCol($sql);

				if (file_exists($plugin_file)) {
					include_once $plugin_file;
					$cloud = new cloud();
					$cloud_prod = $cloud->queryInventoryNum($productIds);
					$cloud_prod = json_decode($cloud_prod, true);

					if ($cloud_prod['code'] == 10000) {
						$cloud_product = $cloud_prod['data'];

						if ($cloud_product) {
							foreach ($cloud_product as $k => $v) {
								if (in_array($v['productId'], $productIds)) {
									if ($v['hasTax'] == 1) {
										$goods_number = $v['taxNum'];
									}
									else {
										$goods_number = $v['noTaxNum'];
									}

									break;
								}
							}
						}
					}
				}
			}
			else {
				$goods_number = $val['stock_number'];
			}

			$goods_res[$key]['stock_number'] = $goods_number;
		}
		else {
			if ($val['product_id']) {
				$sql = 'SELECT product_id, product_number, product_sn FROM ' . $GLOBALS['ecs']->table('products') . ' WHERE product_id = \'' . $val['product_id'] . '\'';
				$product = $GLOBALS['db']->getRow($sql);
				$product_number = $product && !empty($product['product_number']) ? $product['product_number'] : 0;
			}
			else {
				$product_number = $val['stock_number'];
			}

			$goods_res[$key]['stock_number'] = $product_number;
		}

		$goods_res[$key]['goods_thumb'] = get_image_path($val['goods_id'], $val['goods_thumb'], true);
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
	$sql = "SELECT pg.goods_id, g.goods_name, pg.goods_number, p.goods_attr, p.product_number, p.product_id, g.goods_weight, g.goods_thumb ,g.shop_price\r\n            FROM " . $GLOBALS['ecs']->table('package_goods') . " AS pg\r\n                LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON pg.goods_id = g.goods_id\r\n                LEFT JOIN " . $GLOBALS['ecs']->table('products') . (" AS p ON pg.product_id = p.product_id\r\n            WHERE pg.package_id = '" . $package_id . '\'');
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

	while ($_row = $GLOBALS['db']->fetch_array($resource)) {
		$_row['goods_thumb'] = get_image_path($_row['goods_id'], $_row['goods_thumb'], true);
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

function get_class_nav($cat_id, $table = 'category')
{
	$sql = 'select cat_id,cat_name,parent_id from ' . $GLOBALS['ecs']->table($table) . (' where cat_id = \'' . $cat_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $key => $row) {
		$arr[$key]['cat_id'] = $row['cat_id'];
		$arr[$key]['cat_name'] = $row['cat_name'];
		$arr[$key]['parent_id'] = $row['parent_id'];
		$arr['catId'] .= $row['cat_id'] . ',';
		$arr[$key]['child'] = get_parent_child($row['cat_id'], $table);

		if (empty($arr[$key]['child']['catId'])) {
			$arr['catId'] = $arr['catId'];
		}
		else {
			$arr['catId'] .= $arr[$key]['child']['catId'];
		}
	}

	return $arr;
}

function get_parent_child($parent_id = 0, $table = 'category')
{
	$sql = 'select cat_id,cat_name,parent_id from ' . $GLOBALS['ecs']->table($table) . (' where parent_id = \'' . $parent_id . '\'');
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
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('goods_cat') . ' AS gc left join ' . $GLOBALS['ecs']->table('goods') . (' as g on gc.goods_id = g.goods_id WHERE g.is_delete = 0 AND g.is_show = 1 and gc.cat_id in(' . $cat_id . ')') . $ruCat;
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
	$sql = 'SELECT is_xiangou,xiangou_num, xiangou_start_date, xiangou_end_date, goods_name FROM ' . $GLOBALS['ecs']->table('goods') . ('WHERE goods_id = \'' . $goods_id . '\' LIMIT 1');
	return $GLOBALS['db']->getRow($sql);
}

function get_for_purchasing_goods($start_date = 0, $end_date = 0, $goods_id = 0, $user_id = 0, $extension_code = '', $attr_id = '')
{
	$where = '';

	if (!empty($extension_code)) {
		$where = ' AND oi.extension_code = \'' . $extension_code . '\'';
	}

	if ($attr_id) {
		$where .= ' AND og.goods_attr_id = \'' . $attr_id . '\'';
	}

	$where .= ' AND oi.order_status <> ' . OS_CANCELED;

	if ($extension_code != 'group_buy') {
		$where .= ' AND oi.user_id = ' . $user_id;
	}

	$sql = 'SELECT SUM(og.goods_number) AS goods_number FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og, ' . $GLOBALS['ecs']->table('order_info') . ' as oi ' . 'WHERE oi.order_id = og.order_id ' . ' AND og.goods_id = \'' . $goods_id . '\' AND oi.add_time > \'' . $start_date . '\' AND oi.add_time < \'' . $end_date . '\'' . $where;
	$goods_number = $GLOBALS['db']->getOne($sql);
	$goods_number = $goods_number ? $goods_number : 0;
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
	$arr = array();
	$sec_arr = array();

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

		$sql = 'SELECT b.brand_name,g.is_shipping, g.goods_sn, g.brand_id, g.goods_id, g.goods_name, g.user_id, g.goods_thumb,g.sales_volume, g.user_id, msi.self_run, g.model_attr, ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . 'g.product_price, g.product_promote_price, g.promote_start_date, g.promote_end_date ' . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' as g ' . ' left join ' . $GLOBALS['ecs']->table('brand') . ' as b' . ' on g.brand_id = b.brand_id ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . (' WHERE ' . $where . ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_show = 1 group by g.goods_id  ORDER BY ' . $sort . ' ' . $order);
		$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

		while ($row = $GLOBALS['db']->fetchRow($res)) {
			if (0 < $row['promote_price']) {
				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			}
			else {
				$promote_price = 0;
			}

			$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
			$arr[$row['goods_id']]['goods_sn'] = $row['goods_sn'];
			$arr[$row['goods_id']]['sales_volume'] = $row['sales_volume'];
			$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
			$arr[$row['goods_id']]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
			$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
			$arr[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
			$arr[$row['goods_id']]['brand_name'] = $row['brand_name'];
			$arr[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
			$arr[$row['goods_id']]['brand_url'] = build_uri('brand', array('bid' => $row['brand_id']), $row['brand_name']);
			$basic_info = get_shop_info_content($row['user_id']);
			$arr[$row['goods_id']]['kf_type'] = $basic_info['kf_type'];
			$arr[$row['goods_id']]['kf_qq'] = $basic_info['kf_qq'];
			$arr[$row['goods_id']]['kf_ww'] = $basic_info['kf_ww'];
			$goods_id = $row['goods_id'];
			$count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' where id_value =\'' . $goods_id . '\' AND status = 1 AND parent_id = 0'));
			$arr[$row['goods_id']]['review_count'] = $count;
			$arr[$row['goods_id']]['rz_shopName'] = get_shop_name($row['user_id'], 1);
			$arr[$row['goods_id']]['user_id'] = $row['user_id'];
			$arr[$row['goods_id']]['is_shipping'] = $row['is_shipping'];
			$arr[$row['goods_id']]['self_run'] = $row['self_run'];
			$build_uri = array('urid' => $row['user_id'], 'append' => $arr[$row['goods_id']]['rz_shopName']);
			$domain_url = get_seller_domain_url($row['user_id'], $build_uri);
			$arr[$row['goods_id']]['store_url'] = $domain_url['domain_name'];
			$mc_all = ments_count_all($row['goods_id']);
			$mc_one = ments_count_rank_num($row['goods_id'], 1);
			$mc_two = ments_count_rank_num($row['goods_id'], 2);
			$mc_three = ments_count_rank_num($row['goods_id'], 3);
			$mc_four = ments_count_rank_num($row['goods_id'], 4);
			$mc_five = ments_count_rank_num($row['goods_id'], 5);
			$arr[$row['goods_id']]['zconments'] = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);
			$arr[$row['goods_id']]['is_collect'] = get_collect_user_goods($row['goods_id']);
			$arr[$row['goods_id']]['pictures'] = get_goods_gallery($row['goods_id']);

			if ($GLOBALS['_CFG']['customer_service'] == 0) {
				$seller_id = 0;
			}
			else {
				$seller_id = $row['user_id'];
			}

			$shop_information = get_shop_name($seller_id);
			$arr[$row['goods_id']]['is_IM'] = $shop_information['is_IM'];

			if ($seller_id == 0) {
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

	if (!empty($_COOKIE['ECS']['sec_history'])) {
		$where = db_create_in($_COOKIE['ECS']['sec_history'], 'sg.id');
		$sql = 'SELECT b.brand_name,g.is_shipping, g.goods_sn, g.brand_id, g.goods_id, g.goods_name, g.user_id, g.goods_thumb,g.sales_volume, g.user_id, g.model_attr, sg.id, sg.sec_price ' . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' as g ' . ' left join ' . $GLOBALS['ecs']->table('brand') . ' as b' . ' on g.brand_id = b.brand_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('seckill_goods') . ' as sg on sg.goods_id = g.goods_id ' . (' WHERE ' . $where . ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_show = 1 AND g.is_delete = 0 group by g.goods_id ');
		$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

		while ($row = $GLOBALS['db']->fetchRow($res)) {
			$sec_arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
			$sec_arr[$row['goods_id']]['goods_sn'] = $row['goods_sn'];
			$sales_volume = sec_goods_stats($row['id']);
			$sec_arr[$row['goods_id']]['sales_volume'] = $sales_volume['valid_goods'];
			$sec_arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
			$sec_arr[$row['goods_id']]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
			$sec_arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$sec_arr[$row['goods_id']]['shop_price'] = price_format($row['sec_price']);
			$sec_arr[$row['goods_id']]['brand_name'] = $row['brand_name'];
			$sec_arr[$row['goods_id']]['url'] = build_uri('seckill', array('act' => 'view', 'secid' => $row['id']), $row['goods_name']);
			$sec_arr[$row['goods_id']]['brand_url'] = build_uri('brand', array('bid' => $row['brand_id']), $row['brand_name']);
			$sec_arr[$row['goods_id']]['seckill'] = true;
			$basic_info = get_shop_info_content($row['user_id']);
			$sec_arr[$row['goods_id']]['kf_type'] = $basic_info['kf_type'];
			$sec_arr[$row['goods_id']]['kf_qq'] = $basic_info['kf_qq'];
			$sec_arr[$row['goods_id']]['kf_ww'] = $basic_info['kf_ww'];
			$goods_id = $row['goods_id'];
			$count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' where id_value =\'' . $goods_id . '\' AND status = 1 AND parent_id = 0'));
			$sec_arr[$row['goods_id']]['review_count'] = $count;
			$sec_arr[$row['goods_id']]['rz_shopName'] = get_shop_name($row['user_id'], 1);
			$sec_arr[$row['goods_id']]['user_id'] = $row['user_id'];
			$build_uri = array('urid' => $row['user_id'], 'append' => $sec_arr[$row['goods_id']]['rz_shopName']);
			$domain_url = get_seller_domain_url($row['user_id'], $build_uri);
			$sec_arr[$row['goods_id']]['store_url'] = $domain_url['domain_name'];
			$sec_arr[$row['goods_id']]['is_collect'] = get_collect_user_goods($row['goods_id']);
			$sec_arr[$row['goods_id']]['pictures'] = get_goods_gallery($row['goods_id']);

			if ($GLOBALS['_CFG']['customer_service'] == 0) {
				$seller_id = 0;
			}
			else {
				$seller_id = $row['user_id'];
			}

			$shop_information = get_shop_name($seller_id);
			$sec_arr[$row['goods_id']]['is_IM'] = $shop_information['is_IM'];

			if ($seller_id == 0) {
				if ($GLOBALS['db']->getOne('SELECT kf_im_switch FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . 'WHERE ru_id = 0', true)) {
					$sec_arr[$row['goods_id']]['is_dsc'] = true;
				}
				else {
					$sec_arr[$row['goods_id']]['is_dsc'] = false;
				}
			}
			else {
				$sec_arr[$row['goods_id']]['is_dsc'] = false;
			}
		}
	}

	$arr = array_merge($arr, $sec_arr);
	return $arr;
}

function cate_history_count()
{
	$str = '';

	if (!empty($_COOKIE['ECS']['list_history'])) {
		$where = db_create_in($_COOKIE['ECS']['list_history'], 'g.goods_id');
		$sql = 'SELECT b.brand_name, g.brand_id, g.goods_id, g.goods_name, g.goods_thumb, g.shop_price, g.promote_price, g.is_promote FROM ' . $GLOBALS['ecs']->table('goods') . ' as g left join ' . $GLOBALS['ecs']->table('brand') . ' as b' . ' on g.brand_id = b.brand_id ' . (' WHERE ' . $where . ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_show = 1');
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
		$username = get_admin_name();
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

function get_store_shop_list($libType = 0, $keywords = '', $count = 0, $size = 16, $page = 1, $sort = 'shop_id', $order = 'DESC', $warehouse_id = 0, $area_id = 0, $store_province = 0, $store_city = 0, $store_district = 0, $store_user = '')
{
	require_once 'includes/cls_pager.php';
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

	if ($store_user) {
		$id .= 'store_user-' . $store_user . '|';
	}

	$substr = substr($id, -1);

	if ($substr == '|') {
		$id = substr($id, 0, -1);
	}

	$id .= '"';
	$store_shop = new Pager($count, $size, '', $id, 0, $page, 'store_shop_gotoPage', 1, $libType);
	$limit = $store_shop->limit;
	$pager = $store_shop->fpage(array(0, 4, 5, 6, 9));
	$whereShop = ' 1 ';
	$where = '1';
	$keywords = !empty($keywords) ? dsc_addslashes(trim($keywords)) : '';

	if (!empty($keywords)) {
		$keywords = mysql_like_quote($keywords);
		$where .= ' AND (shoprz_brandName LIKE \'%' . $keywords . '%\' OR shopNameSuffix LIKE \'%' . $keywords . '%\' OR rz_shopName LIKE \'%' . $keywords . '%\' OR CONCAT(shoprz_brandName, shopNameSuffix) LIKE \'%' . $keywords . '%\') ';
		$sql = 'SELECT GROUP_CONCAT(user_id) AS user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE ' . $where;
		$shop_list = $GLOBALS['db']->getOne($sql, true);

		if ($shop_list) {
			$shop_list = explode(',', $shop_list);
			$shop_list = array_unique($shop_list);
		}

		$scws_res = scws($keywords, 5);
		$arr = explode(',', $scws_res);
		$arr1[] = $keywords;
		if ($arr1 && is_array($arr)) {
			$arr = array_merge($arr1, $arr);
		}

		$operator = ' OR ';
		$goods_keywords = 'AND (';
		$goods_ids = array();

		foreach ($arr as $key => $val) {
			$val = !empty($val) ? dsc_addslashes($val) : '';

			if ($val) {
				if (0 < $key && $key < count($arr) && 1 < count($arr)) {
					$goods_keywords .= $operator;
				}

				$val = mysql_like_quote(trim($val));
				$goods_keywords .= '(goods_name LIKE \'%' . $val . '%\' OR goods_sn LIKE \'%' . $val . '%\' OR keywords LIKE \'%' . $val . '%\')';
			}
		}

		$goods_keywords .= ')';
		$reviewGodds = '';

		if ($GLOBALS['_CFG']['review_goods'] == 1) {
			$reviewGodds = ' AND review_status > 2 ';
		}

		$sql = 'SELECT GROUP_CONCAT(user_id) AS user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE 1 ' . $goods_keywords . ' ' . $reviewGodds . ' AND user_id > 0');
		$goods_user = $GLOBALS['db']->getOne($sql, true);

		if ($goods_user) {
			$goods_user = explode(',', $goods_user);
			$goods_user = array_unique($goods_user);
		}

		$user_list = array();
		if ($shop_list && $goods_user) {
			$user_list = array_merge($user_list, $shop_list, $goods_user);
		}
		else if ($shop_list) {
			$user_list = $shop_list;
		}
		else if ($goods_user) {
			$user_list = $goods_user;
		}

		$user_list = !empty($user_list) ? array_unique($user_list) : '';
		$user_list = !empty($user_list) ? implode(',', $user_list) : '';

		if (!empty($user_list)) {
			$user_list = get_del_str_comma($user_list);
			$whereShop .= ' AND msi.user_id IN(' . $user_list . ')';
		}
		else {
			$whereShop .= ' AND msi.user_id > 0';
		}
	}
	else if ($store_user) {
		$store_user = get_del_str_comma($store_user);
		$whereShop .= ' AND msi.user_id IN(' . $store_user . ')';
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

	$sql = 'SELECT msi.shop_id, msi.user_id, msi.shoprz_brandName, msi.shopNameSuffix, msi.self_run ' . $select . ' FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . $where_table . (' where ' . $whereShop) . (' AND msi.merchants_audit = 1 AND msi.shop_close = 1 ORDER BY ' . $sort . ' ' . $order . ' ') . $limit;
	$res = $GLOBALS['db']->query($sql);
	$arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$arr[$row['shop_id']]['shop_id'] = $row['shop_id'];
		$arr[$row['shop_id']]['shoprz_brandName'] = $row['shoprz_brandName'];
		$arr[$row['shop_id']]['shopNameSuffix'] = $row['shopNameSuffix'];
		$arr[$row['shop_id']]['self_run'] = $row['self_run'];
		$arr[$row['shop_id']]['shop_name'] = get_shop_name($row['user_id'], 3);
		$arr[$row['shop_id']]['shopName'] = get_shop_name($row['user_id'], 1);
		$arr[$row['shop_id']]['brand_list'] = get_shop_brand_list($row['user_id']);
		$arr[$row['shop_id']]['address'] = get_shop_address_info($row['user_id']);
		$arr[$row['shop_id']]['sales_volume'] = !empty($row['sales_volume']) ? $row['sales_volume'] : 0;
		$grade_info = get_seller_grade($row['user_id']);
		$arr[$row['shop_id']]['grade_img'] = $grade_info['grade_img'];
		$arr[$row['shop_id']]['grade_name'] = $grade_info['grade_name'];
		$shop_info = get_shop_info_content($row['user_id']);
		$arr[$row['shop_id']]['shop_logo'] = str_replace('../', '', $shop_info['shop_logo']);
		$arr[$row['shop_id']]['logo_thumb'] = str_replace('../', '', $shop_info['logo_thumb']);
		$arr[$row['shop_id']]['street_thumb'] = $shop_info['street_thumb'];
		$arr[$row['shop_id']]['brand_thumb'] = $shop_info['brand_thumb'];

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();
			$arr[$row['shop_id']]['shop_logo'] = $bucket_info['endpoint'] . $arr[$row['shop_id']]['shop_logo'];
			$arr[$row['shop_id']]['logo_thumb'] = $bucket_info['endpoint'] . $arr[$row['shop_id']]['logo_thumb'];
			$arr[$row['shop_id']]['street_thumb'] = $bucket_info['endpoint'] . $arr[$row['shop_id']]['street_thumb'];
			$arr[$row['shop_id']]['brand_thumb'] = $bucket_info['endpoint'] . $arr[$row['shop_id']]['brand_thumb'];
		}

		$arr[$row['shop_id']]['street_desc'] = $shop_info['street_desc'];
		$arr[$row['shop_id']]['merch_cmt'] = get_merchants_goods_comment($row['user_id']);
		$arr[$row['shop_id']]['shopNameSuffix'] = $row['shopNameSuffix'];
		$arr[$row['shop_id']]['ru_id'] = $row['user_id'];
		$build_uri = array('urid' => $row['user_id'], 'append' => $arr[$row['shop_id']]['shop_name']);
		$domain_url = get_seller_domain_url($row['user_id'], $build_uri);
		$arr[$row['shop_id']]['shop_url'] = $domain_url['domain_name'];
		$arr[$row['shop_id']]['store_shop_url'] = build_uri('merchants_store_shop', array('urid' => $row['user_id']), $arr[$row['shop_id']]['shop_name']);
		$arr[$row['shop_id']]['goods_count'] = get_shop_goods_count_list($row['user_id'], $warehouse_id, $area_id);
		$arr[$row['shop_id']]['goods_list'] = get_shop_goods_count_list($row['user_id'], $warehouse_id, $area_id, 1);
		$arr[$row['shop_id']]['collect_store'] = 0;

		if (0 < $_SESSION['user_id']) {
			$sql = 'SELECT rec_id FROM ' . $GLOBALS['ecs']->table('collect_store') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\' AND ru_id = \'' . $row['user_id'] . '\' ';
			$arr[$row['shop_id']]['collect_store'] = $GLOBALS['db']->getOne($sql);
		}

		$sql = 'select * from ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' where ru_id=\'' . $row['user_id'] . '\'';
		$basic_info = $GLOBALS['db']->getRow($sql);
		$arr[$row['shop_id']]['kf_type'] = $basic_info['kf_type'];

		if ($basic_info['kf_ww']) {
			$kf_ww = array_filter(preg_split('/\\s+/', $basic_info['kf_ww']));
			$kf_ww = explode('|', $kf_ww[0]);

			if (!empty($kf_ww[1])) {
				$arr[$row['shop_id']]['kf_ww'] = $kf_ww[1];
			}
			else {
				$arr[$row['shop_id']]['kf_ww'] = '';
			}
		}
		else {
			$arr[$row['shop_id']]['kf_ww'] = '';
		}

		if ($basic_info['kf_qq']) {
			$kf_qq = array_filter(preg_split('/\\s+/', $basic_info['kf_qq']));
			$kf_qq = explode('|', $kf_qq[0]);

			if (!empty($kf_qq[1])) {
				$arr[$row['shop_id']]['kf_qq'] = $kf_qq[1];
			}
			else {
				$arr[$row['shop_id']]['kf_qq'] = '';
			}
		}
		else {
			$arr[$row['shop_id']]['kf_qq'] = '';
		}

		$shop_information = get_shop_name($row['user_id']);
		$arr[$row['shop_id']]['is_IM'] = $shop_information['is_IM'];

		if ($row['user_id'] == 0) {
			if ($GLOBALS['db']->getOne('SELECT kf_im_switch FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . 'WHERE ru_id = 0', true)) {
				$arr[$row['shop_id']]['is_dsc'] = true;
			}
			else {
				$arr[$row['shop_id']]['is_dsc'] = false;
			}
		}
		else {
			$arr[$row['shop_id']]['is_dsc'] = false;
		}
	}

	$result = array('shop_list' => $arr, 'pager' => $pager);
	return $result;
}

function get_store_shop_count($keywords = '', $sort = 'shop_id', $store_province = 0, $store_city = 0, $store_district = 0, $store_user = '', $libType = 0)
{
	$whereShop = ' 1 ';
	$where = '1';
	$keywords = !empty($keywords) ? dsc_addslashes(trim($keywords)) : '';

	if (!empty($keywords)) {
		$keywords = mysql_like_quote($keywords);
		$where .= ' AND (shoprz_brandName LIKE \'%' . $keywords . '%\' OR shopNameSuffix LIKE \'%' . $keywords . '%\' OR rz_shopName LIKE \'%' . $keywords . '%\' OR CONCAT(shoprz_brandName, shopNameSuffix) LIKE \'%' . $keywords . '%\') ';
		$sql = 'SELECT GROUP_CONCAT(user_id) AS user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE ' . $where;
		$shop_list = $GLOBALS['db']->getOne($sql, true);

		if ($shop_list) {
			$shop_list = explode(',', $shop_list);
			$shop_list = array_unique($shop_list);
		}

		$scws_res = scws($keywords, 5);
		$arr = explode(',', $scws_res);
		$arr1[] = $keywords;
		if ($arr1 && is_array($arr)) {
			$arr = array_merge($arr1, $arr);
		}

		$operator = ' OR ';
		$goods_keywords = 'AND (';
		$goods_ids = array();

		foreach ($arr as $key => $val) {
			$val = !empty($val) ? dsc_addslashes($val) : '';

			if ($val) {
				if (0 < $key && $key < count($arr) && 1 < count($arr)) {
					$goods_keywords .= $operator;
				}

				$val = mysql_like_quote(trim($val));
				$goods_keywords .= '(goods_name LIKE \'%' . $val . '%\' OR goods_sn LIKE \'%' . $val . '%\' OR keywords LIKE \'%' . $val . '%\')';
			}
		}

		$goods_keywords .= ')';
		$reviewGodds = '';

		if ($GLOBALS['_CFG']['review_goods'] == 1) {
			$reviewGodds = ' AND review_status > 2 ';
		}

		$sql = 'SELECT GROUP_CONCAT(user_id) AS user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE 1 ' . $goods_keywords . ' ' . $reviewGodds . ' AND user_id > 0');
		$goods_user = $GLOBALS['db']->getOne($sql, true);

		if ($goods_user) {
			$goods_user = explode(',', $goods_user);
			$goods_user = array_unique($goods_user);
		}

		$user_list = array();
		if ($shop_list && $goods_user) {
			$user_list = array_merge($user_list, $shop_list, $goods_user);
		}
		else if ($shop_list) {
			$user_list = $shop_list;
		}
		else if ($goods_user) {
			$user_list = $goods_user;
		}

		$user_list = !empty($user_list) ? array_unique($user_list) : '';
		$user_list = !empty($user_list) ? implode(',', $user_list) : '';

		if (!empty($user_list)) {
			$user_list = get_del_str_comma($user_list);
			$whereShop .= ' AND msi.user_id IN(' . $user_list . ')';
		}
		else {
			$whereShop .= ' AND msi.user_id > 0';
		}
	}
	else if ($store_user) {
		$store_user = get_del_str_comma($store_user);
		$whereShop .= ' AND msi.user_id in(' . $store_user . ')';
	}

	$where_table = '';
	$select = '';

	if ($sort == 'sales_volume') {
		$no_main_order = ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = oi.order_id) = 0 ';
		$select .= ', (SELECT SUM(og.goods_number) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi, ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . ' WHERE oi.order_id = og.order_id AND og.ru_id = msi.user_id ' . ' AND (oi.order_status = \'' . OS_CONFIRMED . '\' OR  oi.order_status = \'' . OS_SPLITED . '\' OR oi.order_status = \'' . OS_SPLITING_PART . '\') ' . ' AND (oi.pay_status  = \'' . PS_PAYING . '\' OR  oi.pay_status  = \'' . PS_PAYED . '\') ' . $no_main_order . ') AS sales_volume ';
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
		$whereShop .= ' AND msi.is_street = 1 ';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS msi' . $where_table . (' where ' . $whereShop . ' ') . ' AND msi.merchants_audit = 1 AND msi.shop_close = 1 ';
	return $GLOBALS['db']->getOne($sql);
}

function get_store_shop_goods_list($keywords = '', $size, $page, $sort, $order, $warehouse_id, $area_id)
{
	$whereGodds = '1';
	$where = '1';
	$keywords = !empty($keywords) ? dsc_addslashes(trim($keywords)) : '';

	if (!empty($keywords)) {
		$keywords = mysql_like_quote($keywords);
		$where .= ' AND (shoprz_brandName LIKE \'%' . $keywords . '%\' OR shopNameSuffix LIKE \'%' . $keywords . '%\' OR rz_shopName LIKE \'%' . $keywords . '%\' OR CONCAT(shoprz_brandName, shopNameSuffix) LIKE \'%' . $keywords . '%\') ';
		$sql = 'SELECT GROUP_CONCAT(user_id) AS user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE ' . $where;
		$shop_list = $GLOBALS['db']->getOne($sql, true);

		if ($shop_list) {
			$shop_list = explode(',', $shop_list);
			$shop_list = array_unique($shop_list);
		}

		$scws_res = scws($keywords, 5);
		$arr = explode(',', $scws_res);
		$arr1[] = $keywords;
		if ($arr1 && is_array($arr)) {
			$arr = array_merge($arr1, $arr);
		}

		$operator = ' OR ';
		$goods_keywords = 'AND (';
		$goods_ids = array();

		foreach ($arr as $key => $val) {
			$val = !empty($val) ? dsc_addslashes($val) : '';

			if ($val) {
				if (0 < $key && $key < count($arr) && 1 < count($arr)) {
					$goods_keywords .= $operator;
				}

				$val = mysql_like_quote(trim($val));
				$goods_keywords .= '(goods_name LIKE \'%' . $val . '%\' OR goods_sn LIKE \'%' . $val . '%\' OR keywords LIKE \'%' . $val . '%\')';
			}
		}

		$goods_keywords .= ')';
		$reviewGodds = '';

		if ($GLOBALS['_CFG']['review_goods'] == 1) {
			$reviewGodds = ' AND review_status > 2 ';
		}

		$sql = 'SELECT GROUP_CONCAT(user_id) AS user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE 1 ' . $goods_keywords . ' ' . $reviewGodds . ' AND user_id > 0');
		$goods_user = $GLOBALS['db']->getOne($sql, true);

		if ($goods_user) {
			$goods_user = explode(',', $goods_user);
			$goods_user = array_unique($goods_user);
		}

		$user_list = array();
		if ($shop_list && $goods_user) {
			$user_list = array_merge($user_list, $shop_list, $goods_user);
		}
		else if ($shop_list) {
			$user_list = $shop_list;
		}
		else if ($goods_user) {
			$user_list = $goods_user;
		}

		$user_list = !empty($user_list) ? array_unique($user_list) : '';
		$user_list = !empty($user_list) ? implode(',', $user_list) : '';

		if (!empty($user_list)) {
			$user_list = get_del_str_comma($user_list);
			$whereGodds .= ' AND g.user_id IN(' . $user_list . ')';
		}
		else {
			$whereGodds .= ' AND g.user_id > 0 ';
		}
	}
	else {
		$whereGodds .= ' AND g.user_id > 0 ';
	}

	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$whereGodds .= ' AND lag.region_id = \'' . $area_id . '\' ';
	}

	$whereGodds .= ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_show=1 ';

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$whereGodds .= ' AND g.review_status > 2 ';
	}

	if ($sort == 'shop_price') {
		$sort = 'g.shop_price';
	}
	else if ($sort == 'last_update') {
		$sort = 'g.last_update';
	}

	$select = 'g.goods_id, g.sales_volume, g.goods_thumb,g.is_shipping, g.goods_name, g.user_id, g.promote_start_date, g.promote_end_date, g.market_price, ';
	$select .= 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ';
	$select .= 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price';
	$leftJoin .= 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ');
	$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' as g ' . $leftJoin . (' where ' . $whereGodds . ' ORDER BY ' . $sort . ' ' . $order);
	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
	$arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if ($row) {
			$sql = 'SELECT self_run FROM' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE user_id = \'' . $row['user_id'] . '\'';
			$arr[$row['goods_id']]['self_run'] = $GLOBALS['db']->getOne($sql, true);

			if (0 < $row['promote_price']) {
				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			}
			else {
				$promote_price = 0;
			}

			$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
			$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
			$arr[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
			$arr[$row['goods_id']]['sales_volume'] = $row['sales_volume'];
			$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
			$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
			$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$arr[$row['goods_id']]['goods_url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
			$arr[$row['goods_id']]['is_shipping'] = $row['is_shipping'];
			$sql = 'select * from ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' where ru_id=\'' . $row['user_id'] . '\'';
			$basic_info = $GLOBALS['db']->getRow($sql);
			$arr[$row['goods_id']]['kf_type'] = $basic_info['kf_type'];

			if ($basic_info['kf_qq']) {
				$kf_qq = array_filter(preg_split('/\\s+/', $basic_info['kf_qq']));
				$kf_qq = explode('|', $kf_qq[0]);

				if (!empty($kf_qq[1])) {
					$arr[$row['goods_id']]['kf_qq'] = $kf_qq[1];
				}
				else {
					$arr[$row['goods_id']]['kf_qq'] = '';
				}
			}
			else {
				$arr[$row['goods_id']]['kf_qq'] = '';
			}

			if ($basic_info['kf_ww']) {
				$kf_ww = array_filter(preg_split('/\\s+/', $basic_info['kf_ww']));
				$kf_ww = explode('|', $kf_ww[0]);

				if (!empty($kf_ww[1])) {
					$arr[$row['goods_id']]['kf_ww'] = $kf_ww[1];
				}
				else {
					$arr[$row['goods_id']]['kf_ww'] = '';
				}
			}
			else {
				$arr[$row['goods_id']]['kf_ww'] = '';
			}

			$arr[$row['goods_id']]['shop_name'] = get_shop_name($row['user_id'], 1);
			$build_uri = array('urid' => $row['user_id'], 'append' => $arr[$key]['shop_name']);
			$domain_url = get_seller_domain_url($row['user_id'], $build_uri);
			$arr[$row['goods_id']]['shop_url'] = $domain_url['domain_name'];
			$cmt_count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE id_value =\'' . $row['goods_id'] . '\' AND status = 1 AND parent_id = 0', true);
			$arr[$row['goods_id']]['cmt_count'] = $cmt_count;
			$arr[$row['goods_id']]['brand_list'] = get_shop_brand_list($row['user_id']);
			$arr[$row['goods_id']]['is_collect'] = get_collect_user_goods($row['goods_id']);
			$arr[$row['goods_id']]['pictures'] = get_goods_gallery($row['goods_id'], 6);
			$shop_information = get_shop_name($row['user_id']);
			$arr[$row['goods_id']]['is_IM'] = $shop_information['is_IM'];

			if ($row['user_id'] == 0) {
				if ($GLOBALS['db']->getOne('SELECT kf_im_switch FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . 'WHERE ru_id = 0')) {
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

function get_store_shop_goods_count($keywords, $sort)
{
	$whereGodds = '1';
	$where = '1';
	$keywords = !empty($keywords) ? dsc_addslashes(trim($keywords)) : '';

	if (!empty($keywords)) {
		$keywords = mysql_like_quote($keywords);
		$where .= ' AND (shoprz_brandName LIKE \'%' . $keywords . '%\' OR shopNameSuffix LIKE \'%' . $keywords . '%\' OR rz_shopName LIKE \'%' . $keywords . '%\' OR CONCAT(shoprz_brandName, shopNameSuffix) LIKE \'%' . $keywords . '%\') ';
		$sql = 'SELECT GROUP_CONCAT(user_id) AS user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE ' . $where;
		$shop_list = $GLOBALS['db']->getOne($sql, true);

		if ($shop_list) {
			$shop_list = explode(',', $shop_list);
			$shop_list = array_unique($shop_list);
		}

		$scws_res = scws($keywords, 5);
		$arr = explode(',', $scws_res);
		$arr1[] = $keywords;
		if ($arr1 && is_array($arr)) {
			$arr = array_merge($arr1, $arr);
		}

		$operator = ' OR ';
		$goods_keywords = 'AND (';
		$goods_ids = array();

		foreach ($arr as $key => $val) {
			$val = !empty($val) ? dsc_addslashes($val) : '';

			if ($val) {
				if (0 < $key && $key < count($arr) && 1 < count($arr)) {
					$goods_keywords .= $operator;
				}

				$val = mysql_like_quote(trim($val));
				$goods_keywords .= '(goods_name LIKE \'%' . $val . '%\' OR goods_sn LIKE \'%' . $val . '%\' OR keywords LIKE \'%' . $val . '%\')';
			}
		}

		$goods_keywords .= ')';
		$reviewGodds = '';

		if ($GLOBALS['_CFG']['review_goods'] == 1) {
			$reviewGodds = ' AND review_status > 2 ';
		}

		$sql = 'SELECT GROUP_CONCAT(user_id) AS user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE 1 ' . $goods_keywords . ' ' . $reviewGodds . ' AND user_id > 0');
		$goods_user = $GLOBALS['db']->getOne($sql, true);

		if ($goods_user) {
			$goods_user = explode(',', $goods_user);
			$goods_user = array_unique($goods_user);
		}

		$user_list = array();
		if ($shop_list && $goods_user) {
			$user_list = array_merge($user_list, $shop_list, $goods_user);
		}
		else if ($shop_list) {
			$user_list = $shop_list;
		}
		else if ($goods_user) {
			$user_list = $goods_user;
		}

		$user_list = !empty($user_list) ? array_unique($user_list) : '';
		$user_list = !empty($user_list) ? implode(',', $user_list) : '';

		if (!empty($user_list)) {
			$user_list = get_del_str_comma($user_list);
			$whereGodds .= ' AND g.user_id IN(' . $user_list . ')';
		}
		else {
			$whereGodds .= ' AND g.user_id > 0 ';
		}
	}
	else {
		$whereGodds .= ' AND g.user_id > 0 ';
	}

	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');

	if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
		$whereGodds .= ' AND lag.region_id = \'' . $area_id . '\' ';
	}

	$whereGodds .= ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_show = 1 ';

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$whereGodds .= ' AND g.review_status > 2 ';
	}

	if ($sort == 'shop_price') {
		$sort = 'g.shop_price';
	}
	else if ($sort == 'last_update') {
		$sort = 'g.last_update';
	}

	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('goods') . ' as g ' . (' WHERE ' . $whereGodds);
	$res = $GLOBALS['db']->getOne($sql);
	return $res;
}

function get_shop_brand_list($user_id = 0)
{
	$seller_brand = read_static_cache('seller_brand_' . $user_id, '/data/sc_file/seller_brand/');

	if (!$seller_brand) {
		$sql = 'SELECT msb.bid, b.brand_id, msb.brandName, b.brand_name FROM ' . $GLOBALS['ecs']->table('merchants_shop_brand') . ' AS msb, ' . $GLOBALS['ecs']->table('link_brand') . ' AS lb, ' . $GLOBALS['ecs']->table('brand') . ' AS b' . (' WHERE msb.user_id = \'' . $user_id . '\' AND msb.audit_status = 1 AND lb.bid = msb.bid AND b.brand_id = lb.brand_id ORDER BY bid ASC');
		$seller_brand = $GLOBALS['db']->getAll($sql);
		write_static_cache('seller_brand_' . $user_id, $seller_brand, '/data/sc_file/seller_brand/');
	}

	return $seller_brand;
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
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' where ru_id = \'' . $user_id . '\' LIMIT 1');
	$basic_info = $GLOBALS['db']->getRow($sql);

	if ($basic_info['kf_type']) {
		if ($basic_info['kf_ww']) {
			$kf_ww = array_filter(preg_split('/\\s+/', $basic_info['kf_ww']));

			foreach ($kf_ww as $k => $v) {
				$basic_info['kf_ww_all'][] = explode('|', $v);
			}

			$kf_ww = explode('|', $kf_ww[0]);

			if (!empty($kf_ww[1])) {
				$basic_info['kf_ww'] = $kf_ww[1];
			}
			else {
				$basic_info['kf_ww'] = '';
			}
		}
		else {
			$basic_info['kf_ww'] = '';
		}
	}
	else if ($basic_info['kf_qq']) {
		$kf_qq = array_filter(preg_split('/\\s+/', $basic_info['kf_qq']));

		foreach ($kf_qq as $k => $v) {
			$basic_info['kf_qq_all'][] = explode('|', $v);
		}

		$kf_qq = explode('|', $kf_qq[0]);

		if (!empty($kf_qq[1])) {
			$basic_info['kf_qq'] = $kf_qq[1];
		}
		else {
			$basic_info['kf_qq'] = '';
		}
	}
	else {
		$basic_info['kf_qq'] = '';
	}

	return $basic_info;
}

function get_shop_goods_count_list($user_id, $warehouse_id, $area_id, $type = 0, $isType = '', $show_type = 0, $limit = 0)
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
		$select = 'g.goods_id, g.goods_thumb, g.goods_name, g.user_id, g.promote_start_date, g.promote_end_date, g.market_price, g.sales_volume, g.model_attr, ';
		$select .= 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ';
		$select .= 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ';
		$select .= 'g.product_price, g.product_promote_price ';
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

	$where .= ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_show = 1 ';

	if ($type == 1) {
		if (!empty($limit)) {
			$limit = 'LIMIT ' . $limit;
		}
		else if ($show_type == 1) {
			$limit = 'LIMIT 6';
		}
		else {
			$limit = 'LIMIT 5';
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

			$arr[$key]['market_price'] = price_format($row['market_price']);
			$arr[$key]['shop_price'] = price_format($row['shop_price']);
			$arr[$key]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
			$arr[$key]['goods_id'] = $row['goods_id'];
			$arr[$key]['goods_name'] = $row['goods_name'];
			$arr[$key]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$arr[$key]['goods_url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
			$arr[$key]['sales_volume'] = $row['sales_volume'];
			$basic_info = get_shop_info_content($row['user_id']);
			$arr[$key]['kf_type'] = $basic_info['kf_type'];

			if ($basic_info['kf_qq']) {
				$kf_qq = array_filter(preg_split('/\\s+/', $basic_info['kf_qq']));
				$kf_qq = explode('|', $kf_qq[0]);

				if (!empty($kf_qq[1])) {
					$arr[$key]['kf_qq'] = $kf_qq[1];
				}
				else {
					$arr[$key]['kf_qq'] = '';
				}
			}
			else {
				$arr[$key]['kf_qq'] = '';
			}

			if ($basic_info['kf_ww']) {
				$kf_ww = array_filter(preg_split('/\\s+/', $basic_info['kf_ww']));
				$kf_ww = explode('|', $kf_ww[0]);

				if (!empty($kf_ww[1])) {
					$arr[$key]['kf_ww'] = $kf_ww[1];
				}
				else {
					$arr[$key]['kf_ww'] = '';
				}
			}
			else {
				$arr[$key]['kf_ww'] = '';
			}

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
	$where .= ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_show=1 ';

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
	$select = 'g.goods_id, g.goods_thumb, g.goods_name, g.user_id, g.promote_start_date, g.promote_end_date, g.market_price, g.sales_volume, g.model_attr, ';
	$select .= 'IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ';
	$select .= 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ';
	$select .= 'g.product_price, g.product_promote_price ';

	if ($sort == 'last_update') {
		$sort = 'g.last_update';
	}

	$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' as g ' . $leftJoin . (' WHERE ' . $where . ' AND g.user_id = \'' . $user_id . '\'  ORDER BY ' . $sort . ' ' . $order);
	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
		$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$arr[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
		$arr[$row['goods_id']]['sales_volume'] = $row['sales_volume'];
		$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
		$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$arr[$row['goods_id']]['goods_url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$arr[$row['goods_id']]['user_id'] = $row['user_id'];
		$basic_info = get_shop_info_content($row['user_id']);
		$arr[$row['goods_id']]['kf_type'] = $basic_info['kf_type'];
		$arr[$row['goods_id']]['kf_ww'] = $basic_info['kf_ww'];
		$arr[$row['goods_id']]['kf_qq'] = $basic_info['kf_qq'];
		$arr[$row['goods_id']]['shop_name'] = get_shop_name($row['user_id'], 1);
		$build_uri = array('urid' => $row['user_id'], 'append' => $arr[$key]['shop_name']);
		$domain_url = get_seller_domain_url($row['user_id'], $build_uri);
		$arr[$row['goods_id']]['shop_url'] = $domain_url['domain_name'];
		$cmt_count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' where id_value =\'' . $row['goods_id'] . '\' AND status = 1 AND parent_id = 0');
		$arr[$row['goods_id']]['cmt_count'] = $cmt_count;
		$arr[$row['goods_id']]['is_collect'] = get_collect_user_goods($row['goods_id']);
		$shop_information = get_shop_name($row['user_id']);
		$arr[$row['goods_id']]['is_IM'] = $shop_information['is_IM'];
		$arr[$row['goods_id']]['pictures'] = get_goods_gallery($row['goods_id'], 6);

		if ($row['user_id'] == 0) {
			if ($GLOBALS['db']->getOne('SELECT kf_im_switch FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . 'WHERE ru_id = 0')) {
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

function get_user_bouns_new_list($user_id = 0, $page = 1, $type = 0, $pageFunc = '', $amount = 0, $size = 10, $cart_ru_id = -1)
{
	require_once 'includes/cls_pager.php';
	$day = local_getdate();
	$cur_date = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
	$before_date = local_mktime(0, 0, 0, $day['mon'], $day['mday'], $day['year']) - 2 * 24 * 3600;
	$useDate = ' AND b.use_start_date < ' . $cur_date . ' AND b.use_end_date > ' . $cur_date;
	$where = '';

	if (-1 < $cart_ru_id) {
		$where .= ' AND IF(b.usebonus_type > 0, 1, b.user_id IN(' . $cart_ru_id . '))';
	}

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

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('user_bonus') . ' as u,' . $GLOBALS['ecs']->table('bonus_type') . ' AS b' . ' WHERE u.bonus_type_id = b.type_id AND b.review_status = 3 ' . $uOrder . (' AND u.user_id = \'' . $user_id . '\' ') . $useDate . $where;
	$record_count = $GLOBALS['db']->getOne($sql);
	$bouns_paper = '';
	$limit = '';

	if ($amount == 0) {
		$bouns = new Pager($record_count, $size, '', $user_id, 0, $page, $pageFunc, 1);
		$limit = $bouns->limit;
		$bouns_paper = $bouns->fpage(array(0, 4, 5, 6, 9));
	}

	$sql = 'SELECT  u.bonus_id, u.bonus_sn, u.order_id, u.bind_time, b.type_name, b.type_money,b.min_amount, b.min_goods_amount, b.use_start_date, b.use_end_date, ' . 'b.usebonus_type, b.user_id AS ru_id FROM ' . $GLOBALS['ecs']->table('user_bonus') . ' AS u ,' . $GLOBALS['ecs']->table('bonus_type') . ' AS b' . ' WHERE u.bonus_type_id = b.type_id AND b.review_status = 3 ' . $uOrder . ' AND u.user_id = \'' . $user_id . '\' ' . $useDate . $where . ' order by u.bonus_id DESC ' . $limit;
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
		$arr[$key]['type_name'] = $row['type_name'];
		$arr[$key]['min_goods_amount_old'] = $row['min_goods_amount'];
	}

	$bouns = array($arrName => $arr, 'record_count' => $record_count, 'paper' => $bouns_paper);
	return $bouns;
}

function get_user_bind_vc_list($user_id = 0, $page = 1, $type = 0, $pageFunc = '', $amount = 0, $size = 10)
{
	require_once 'includes/cls_pager.php';
	$sql = 'SELECT t.name, t.use_condition, v.vc_value, t.is_rec, v.vid, v.value_card_sn, v.card_money, v.bind_time,v.end_time FROM ' . $GLOBALS['ecs']->table('value_card') . ' AS v ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('value_card_type') . ' AS t ON v.tid = t.id ' . (' WHERE v.user_id = \'' . $user_id . '\' order by v.vid DESC ');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();
	$now = gmtime();

	foreach ($res as $key => $row) {
		if ($row['end_time'] < $now) {
			$arr[$key]['status'] = false;
		}
		else {
			$arr[$key]['status'] = true;
		}

		$arr[$key]['name'] = $row['name'];
		$arr[$key]['vid'] = $row['vid'];
		$arr[$key]['value_card_sn'] = $row['value_card_sn'];
		$arr[$key]['vc_value'] = price_format($row['vc_value']);
		$arr[$key]['use_condition'] = condition_format($row['use_condition']);
		$arr[$key]['is_rec'] = $row['is_rec'];
		$arr[$key]['card_money'] = price_format($row['card_money']);
		$arr[$key]['bind_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['bind_time']);
		$arr[$key]['end_time'] = local_date('Y-m-d H:i:s', $row['end_time']);
	}

	return $arr;
}

function value_card_use_info($vc_id = 0)
{
	require_once 'includes/cls_pager.php';
	$sql = 'SELECT o.order_sn, r.rid, r.use_val, r.add_val, r.record_time FROM ' . $GLOBALS['ecs']->table('value_card_record') . ' AS r ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS o ON r.order_id = o.order_id ' . (' WHERE r.vc_id = \'' . $vc_id . '\' order by r.rid DESC ');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['rid'] = $row['rid'];
		$arr[$key]['order_sn'] = $row['order_sn'];
		$arr[$key]['use_val'] = price_format($row['use_val']);
		$arr[$key]['add_val'] = price_format($row['add_val']);
		$arr[$key]['record_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['record_time']);
	}

	return $arr;
}

function get_bouns_amount_list($bouns_list)
{
	$bouns_amount = 0;

	foreach ($bouns_list['available_list'] as $key => $row) {
		$bouns_amount += $row['bouns_amount'];
	}

	return price_format($bouns_amount);
}

function get_merchants_search_brand($val = '', $type = 0, $brand_type = '', $brand_name = '', $brand_letter = '')
{
	$sqltype = '';
	$arr = array();
	$res = array();
	if (!empty($val) || $type == 2 && (!empty($brand_name) && !empty($brand_letter))) {
		if ($type == 2 || $type == 3) {
			if ($brand_type == 'm_bran') {
				$date = array('bid as brand_id', 'brandName as brand_name', 'bank_name_letter as brand_letter');
				$where = ' bid = \'' . $val . '\' AND audit_status = 1';
				$res = get_table_date('merchants_shop_brand', $where, $date);
			}
			else {
				$date = array('brand_id', 'brand_name', 'brand_letter');

				if ($type == 2) {
					if (empty($val)) {
						if (!empty($brand_name)) {
							$where = ' brand_name = \'' . $brand_name . '\'';
						}
						else {
							$where = ' brand_letter = \'' . $brand_letter . '\'';
						}
					}
					else {
						$where = ' brand_id = \'' . $val . '\'';
					}
				}
				else {
					$where = ' 1';
					$sqltype = 1;
				}

				$res = get_table_date('brand', $where, $date, $sqltype);
			}
		}
		else {
			if ($type == 1) {
				$sql = 'SELECT brand_id, brand_name, brand_letter FROM ' . $GLOBALS['ecs']->table('brand') . (' WHERE brand_letter REGEXP \'^' . $val . '\'');
				$res1 = $GLOBALS['db']->getAll($sql);
			}
			else {
				$sql = 'SELECT brand_id, brand_name, brand_letter FROM ' . $GLOBALS['ecs']->table('brand') . (' WHERE brand_name REGEXP \'^' . $val . '\'');
				$res1 = $GLOBALS['db']->getAll($sql);
			}

			$res = $res1;
		}
	}

	return $res;
}

function get_link_brand_list($brand_id, $type = 0, $sqlType = 0)
{
	if ($type == 1) {
		$select = 'b.bid as brand_id, b.brandName as brand_name';
		$table = 'merchants_shop_brand';
		$where = 'lb.bid = b.bid AND lb.bid = \'' . $brand_id . '\'';
	}
	else if ($type == 2) {
		$select = 'b.brand_id, b.brand_name';
		$table = 'brand';
		$where = 'lb.brand_id = b.brand_id AND lb.brand_id = \'' . $brand_id . '\'';
	}
	else if ($type == 3) {
		$select = 'b.brand_id, b.brand_name';
		$table = 'brand';
		$where = 'lb.brand_id = b.brand_id AND lb.bid = \'' . $brand_id . '\'';
	}
	else if ($type == 4) {
		$select = 'b.brand_id, b.brand_name';
		$table = 'brand';
		$where = 'lb.brand_id = b.brand_id AND lb.brand_id = \'' . $brand_id . '\'';
	}

	$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table('link_brand') . ' as lb, ' . $GLOBALS['ecs']->table($table) . (' as b WHERE ' . $where);

	if ($sqlType == 1) {
		return $GLOBALS['db']->getAll($sql);
	}
	else {
		return $GLOBALS['db']->getRow($sql);
	}
}

function get_update_flow_Consignee($address_id = 0)
{
	$consignee = array();

	if ($address_id) {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . (' SET address_id = \'' . $address_id . '\' WHERE user_id = \'') . $_SESSION['user_id'] . '\'';
		$GLOBALS['db']->query($sql);
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_address') . (' WHERE address_id = \'' . $address_id . '\'');
		$consignee = $GLOBALS['db']->getRow($sql);
	}

	return $consignee;
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

	$sql = 'SELECT c.*,g.goods_name,g.goods_thumb,g.goods_id,c.goods_number,c.goods_price' . ' FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id=c.goods_id ' . ' WHERE ' . $c_sess . ' AND rec_type = \'' . CART_GENERAL_GOODS . '\'' . $limit;
	$row = $GLOBALS['db']->GetAll($sql);
	$arr = array();
	$cart_value = '';

	foreach ($row as $k => $v) {
		$arr[$k]['goods_thumb'] = get_image_path($v['goods_id'], $v['goods_thumb'], true);
		$arr[$k]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($v['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $v['goods_name'];
		$arr[$k]['url'] = build_uri('goods', array('gid' => $v['goods_id']), $v['goods_name']);
		$arr[$k]['goods_number'] = $v['goods_number'];
		$arr[$k]['goods_name'] = $v['goods_name'];
		$arr[$k]['goods_price'] = price_format($v['goods_price']);
		$arr[$k]['rec_id'] = $v['rec_id'];
		$arr[$k]['warehouse_id'] = $v['warehouse_id'];
		$arr[$k]['area_id'] = $v['area_id'];
		$cart_value = !empty($cart_value) ? $cart_value . ',' . $v['rec_id'] : $v['rec_id'];
		$properties = get_goods_properties($v['goods_id'], $v['warehouse_id'], $v['area_id'], $v['area_city'], $v['goods_attr_id'], 1);

		if ($properties['spe']) {
			$arr[$k]['spe'] = array_values($properties['spe']);
		}
		else {
			$arr[$k]['spe'] = array();
		}
	}

	$sql = 'SELECT SUM(goods_number) AS number, SUM(goods_price * goods_number) AS amount' . ' FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . ' AND rec_type = \'' . CART_GENERAL_GOODS . '\'';
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

function get_return_store_shop_url($ru_id = 0, $shop_name = '')
{
	if (empty($shop_name)) {
		$shop_name = get_shop_name($ru_id, 1);
	}

	$url = build_uri('merchants_store_shop', array('urid' => $ru_id), $shop_name);
	return $url;
}

function get_return_store_url($params = '', $append = '')
{
	$url = build_uri('merchants_store', $params, $append);
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

function get_category_tree_leve_one($parent_id = 0, $type = 0)
{
	if (0 < $parent_id || 0 < $type) {
		$cat_list = get_category_leve_one($parent_id, $type);
	}
	else {
		$cat_list = read_static_cache('category_tree_leve_one0', '/data/sc_file/category/');

		if ($cat_list === false) {
			$cat_list = get_category_leve_one();
			write_static_cache('category_tree_leve_one0', $cat_list, '/data/sc_file/category/');
		}
	}

	return $cat_list;
}

function get_category_leve_one($parent_id = 0, $type = 0)
{
	$sql = 'SELECT cat_id, cat_name, style_icon, cat_icon, category_links, cat_alias_name FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE parent_id = 0 AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$row['cat_id']]['id'] = $row['cat_id'];
		$arr[$row['cat_id']]['cat_alias_name'] = $row['cat_alias_name'];
		$arr[$row['cat_id']]['url'] = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);
		$arr[$row['cat_id']]['style_icon'] = $row['style_icon'];
		$arr[$row['cat_id']]['cat_icon'] = $row['cat_icon'];

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

		$arr[$row['cat_id']]['nolinkname'] = $row['cat_name'];

		if ($type == 1) {
			$arr[$row['cat_id']]['child_tree'] = cat_list($row['cat_id'], 1);
		}

		$sql = 'SELECT * ' . ' FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE parent_id = \'' . $row['cat_id'] . '\' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC';
		$res = $GLOBALS['db']->getAll($sql);

		foreach ($res as $key2 => $val) {
			$arr[$row['cat_id']]['child_two'][$key2]['cat_name'] = $val['cat_name'];
			$arr[$row['cat_id']]['child_two'][$key2]['url'] = build_uri('category', array('cid' => $val['cat_id']), $val['cat_name']);
		}
	}

	return $arr;
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
	$g_children = get_children($cat_id);
	$gc_children = get_children($cat_id, 1);
	$sql = 'SELECT b.brand_id, b.brand_name,  b.brand_logo, COUNT(*) AS goods_num, IF(b.brand_logo > \'\', \'1\', \'0\') AS tag ' . 'FROM ' . $GLOBALS['ecs']->table('brand') . 'AS b ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.brand_id = b.brand_id AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_cat') . ' AS gc ON g.goods_id = gc.goods_id ' . ('WHERE (' . $g_children . ' OR ' . $gc_children . ') AND b.is_show = 1 ') . 'GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY b.sort_order, b.brand_id ASC LIMIT 0,12';
	$brands = $GLOBALS['db']->getAll($sql);
	$sql = 'SELECT cat_name FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE cat_id = \'' . $cat_id . '\' LIMIT 1');
	$name = $GLOBALS['db']->getOne($sql);

	foreach ($brands as $key => $val) {
		$temp_key = $key;
		$brands[$temp_key]['brand_name'] = $val['brand_name'];
		$brands[$temp_key]['url'] = build_uri('category', array('cid' => $cat_id, 'bid' => $val['brand_id']), $name);
		$brands[$temp_key]['brand_logo'] = $GLOBALS['_CFG']['site_domain'] . DATA_DIR . '/brandlogo/' . $val['brand_logo'];

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();
			$brands[$temp_key]['brand_logo'] = $bucket_info['endpoint'] . DATA_DIR . '/brandlogo/' . $val['brand_logo'];
		}

		$brands[$temp_key]['selected'] = 0;
	}

	$arr['brands'] = $brands;
	return $arr;
}

function get_category_topic($cat_id = 0)
{
	$arr = array();
	$sql = 'SELECT category_topic FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE cat_id = \'' . $cat_id . '\' LIMIT 1');

	if ($res = $GLOBALS['db']->getRow($sql)) {
		if ($res['category_topic']) {
			$category_topic_arr = explode("\r\n", $res['category_topic']);

			foreach ($category_topic_arr as $key => $row) {
				if ($row) {
					$row = explode('|', $row);
					$arr[$key]['topic_name'] = $row[0];
					$arr[$key]['topic_link'] = $row[1];
				}
			}
		}
	}

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

function get_template_js($arr = array())
{
	$str = '';

	if ($arr) {
		foreach ($arr as $row) {
			$str .= '<script type="text/javascript" src="' . $GLOBALS['_CFG']['site_domain'] . 'themes/' . $GLOBALS['_CFG']['template'] . '/js/' . $row . '.js"></script> ';
		}
	}

	return $str;
}

function get_select_category($cat_id = 0, $relation = 0, $self = true, $user_id = 0, $table = 'category')
{
	static $cat_list = array();
	$cat_list[] = intval($cat_id);

	if ($relation == 0) {
		return $cat_list;
	}
	else if ($relation == 1) {
		$sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE cat_id=\'' . $cat_id . '\' ';
		$parent_id = $GLOBALS['db']->getOne($sql);

		if (!empty($parent_id)) {
			get_select_category($parent_id, $relation, $self);
		}

		if ($self == false) {
			unset($cat_list[0]);
		}

		$cat_list[] = 0;
		return array_reverse(array_unique($cat_list));
	}
	else if ($relation == 2) {
		$sql = 'SELECT cat_id FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE parent_id=\'' . $cat_id . '\' ';
		$child_id = $GLOBALS['db']->getCol($sql);

		if (!empty($child_id)) {
			foreach ($child_id as $key => $val) {
				get_select_category($val, $relation, $self);
			}
		}

		if ($self == false) {
			unset($cat_list[0]);
		}

		return $cat_list;
	}
}

function get_merchant_category($cat_id = 0, $ru_id = 0)
{
	$sql = 'SELECT c.cat_id, c.cat_name FROM ' . $GLOBALS['ecs']->table('category') . ' AS c ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_category') . ' AS mc on mc.cat_id=c.cat_id ' . (' WHERE c.parent_id=\'' . $cat_id . '\' AND mc.user_id=\'' . $ru_id . '\' ORDER BY c.sort_order, c.cat_id DESC');
	$res = $GLOBALS['db']->getAll($sql);
	return $res;
}

function insert_select_category($cat_id = 0, $child_cat_id = 0, $cat_level = 0, $select_jsId = 'cat_parent_id', $type = 0, $table = 'category', $seller_shop_cat = array())
{
	$cat_level = $cat_level + 1;
	$child_category = cat_list($cat_id, 0, 0, $table, $seller_shop_cat, $cat_level);
	$GLOBALS['smarty']->assign('child_category', $child_category);
	$GLOBALS['smarty']->assign('child_cat_id', $child_cat_id);
	$GLOBALS['smarty']->assign('cat_level', $cat_level);
	$GLOBALS['smarty']->assign('select_jsId', $select_jsId);
	$GLOBALS['smarty']->assign('type', $type);
	$html = $GLOBALS['smarty']->fetch('templates/get_select_category.dwt');
	return $html;
}

function insert_seller_select_category($cat_id = 0, $child_cat_id = 0, $cat_level = 0, $select_jsId = 'cat_parent_id', $type = 0, $table = 'category', $seller_shop_cat = array(), $user_id = 0)
{
	$child_category = cat_list($cat_id, 0, 0, $table, $seller_shop_cat, 0, $user_id);
	$GLOBALS['smarty']->assign('child_category', $child_category);
	$GLOBALS['smarty']->assign('child_cat_id', $child_cat_id);
	$GLOBALS['smarty']->assign('cat_level', $cat_level + 1);
	$GLOBALS['smarty']->assign('select_jsId', $select_jsId);
	$GLOBALS['smarty']->assign('type', $type);
	$html = $GLOBALS['smarty']->fetch('templates/get_select_category_seller.dwt');
	return $html;
}

function get_seller_mainshop_cat($ru_id)
{
	$sql = 'select user_shopMain_category from ' . $GLOBALS['ecs']->table('merchants_shop_information') . (' where user_id = \'' . $ru_id . '\'');
	return $GLOBALS['db']->getOne($sql);
}

function get_seller_domain()
{
	$get_domain = $GLOBALS['ecs']->get_domain();
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('seller_domain') . (' WHERE domain_name = \'' . $get_domain . '\' AND is_enable = 1');
	return $GLOBALS['db']->getRow($sql);
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

function get_goods_flow_type($cart_value)
{
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

	if (exist_real_goods(0, $flow_type, $cart_value)) {
		$goods_flow_type = 101;
	}
	else {
		$goods_flow_type = 100;
	}

	$GLOBALS['smarty']->assign('goods_flow_type', $goods_flow_type);
}

function setAnonymous($user_name)
{
	if (129 < ord(substr($user_name, 0, 1))) {
		$str_1 = substr($user_name, 0, 3);
	}
	else {
		$str_1 = substr($user_name, 0, 1);
	}

	if (129 < ord(substr($user_name, -1))) {
		$str_2 = substr($user_name, -3);
	}
	else {
		$str_2 = substr($user_name, -1);
	}

	$user_name = $str_1 . '***' . $str_2;
	return $user_name;
}

function get_invalid_apply($type = 0)
{
	$grade_apply_time = 1;

	if (0 < $GLOBALS['_CFG']['grade_apply_time']) {
		$grade_apply_time = $GLOBALS['_CFG']['grade_apply_time'];
	}

	$time = gmtime() - 24 * 60 * 60 * $grade_apply_time;

	if ($type == 1) {
		$sql = 'DELETE FROM' . $GLOBALS['ecs']->table('seller_template_apply') . 'WHERE pay_status = 0 AND apply_status = 0 AND add_time < \'' . $time . '\'';
	}
	else {
		$sql = ' UPDATE' . $GLOBALS['ecs']->table('seller_apply_info') . ' SET apply_status = 3 WHERE is_paid = 0 AND add_time < \'' . $time . '\'';
	}

	return $GLOBALS['db']->query($sql);
}

function get_seller_grade($ru_id = 0, $type = 0)
{
	if ($type) {
		$ru_id = implode(',', $ru_id);
		$where = 'g.ru_id IN(' . $ru_id . ')';
	}
	else {
		$where = 'g.ru_id = \'' . $ru_id . '\' LIMIT 1';
	}

	$sql = 'SELECT s.grade_name, s.grade_img, s.grade_introduce, s.white_bar, g.grade_id, g.add_time, g.year_num, g.amount FROM' . $GLOBALS['ecs']->table('seller_grade') . ' AS s ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_grade') . (' AS g ON s.id = g.grade_id WHERE ' . $where);

	if ($type) {
		$str = 1;
		$res = $GLOBALS['db']->getAll($sql);

		foreach ($res as $k => $v) {
			$res[$k]['grade_img'] = get_image_path(0, $v['grade_img']);

			if ($v['white_bar'] == 0) {
				$str = 0;
				break;
			}
		}

		return $str;
	}
	else {
		return $GLOBALS['db']->getRow($sql);
	}
}

function grade_expire()
{
	$time = gmtime();
	$where = ' WHERE add_time+365*24*60*60*year_num < ' . $time;
	$sql = 'SELECT id FROM' . $GLOBALS['ecs']->table('seller_grade') . 'WHERE is_default = 1';
	$grade_id = $GLOBALS['db']->getOne($sql);

	if (0 < $grade_id) {
		$sql = 'UPDATE' . $GLOBALS['ecs']->table('merchants_grade') . 'SET grade_id = ' . $grade_id . ' , add_time = ' . $time . ' , year_num = 1' . $where;
	}
	else {
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('merchants_grade') . $where;
	}

	return $GLOBALS['db']->query($sql);
}

function update_zc_project($order_id = 0)
{
	$sql = ' SELECT user_id, is_zc_order, zc_goods_id FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\' ');
	$order_info = $GLOBALS['db']->getRow($sql);
	$user_id = $order_info['user_id'];
	$is_zc_order = $order_info['is_zc_order'];
	$zc_goods_id = $order_info['zc_goods_id'];
	if ($is_zc_order == 1 && 0 < $zc_goods_id) {
		$sql = ' select * from ' . $GLOBALS['ecs']->table('zc_goods') . (' where id = \'' . $zc_goods_id . '\' ');
		$zc_goods_info = $GLOBALS['db']->getRow($sql);
		$pid = $zc_goods_info['pid'];
		$goods_price = $zc_goods_info['price'];
		$sql = ' UPDATE ' . $GLOBALS['ecs']->table('zc_goods') . (' SET backer_num = backer_num+1 WHERE id = \'' . $zc_goods_id . '\' ');
		$GLOBALS['db']->query($sql);
		$sql = 'SELECT backer_list FROM ' . $GLOBALS['ecs']->table('zc_goods') . (' WHERE id = \'' . $zc_goods_id . '\'');
		$backer_list = $GLOBALS['db']->getOne($sql);

		if (empty($backer_list)) {
			$backer_list = $user_id;
		}
		else {
			$backer_list = $backer_list . ',' . $user_id;
		}

		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('zc_goods') . (' SET backer_list=\'' . $backer_list . '\' WHERE id = \'' . $zc_goods_id . '\'');
		$GLOBALS['db']->query($sql);
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('zc_project') . (' SET join_num=join_num+1, join_money=join_money+' . $goods_price . ' WHERE id = \'' . $pid . '\'');
		$GLOBALS['db']->query($sql);
	}
}

function have_file_upload()
{
	if (!empty($_FILES) && 0 < count($_FILES)) {
		foreach ($_FILES as $key => $val) {
			if (empty($val['name'])) {
				unset($_FILES[$key]);
			}
		}

		if (!empty($_FILES) && 0 < count($_FILES)) {
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

function get_zc_goods_info($order_id = 0)
{
	$sql = ' SELECT is_zc_order, zc_goods_id FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\' ');
	$order = $GLOBALS['db']->getRow($sql);

	if ($order['is_zc_order']) {
		$sql = ' SELECT zg.*, zg.id as gid, zp.* FROM ' . $GLOBALS['ecs']->table('zc_goods') . ' AS zg ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('zc_project') . ' AS zp on zp.id = zg.pid ' . (' WHERE zg.id = \'' . $order['zc_goods_id'] . '\' ');
		$zc_goods_info = $GLOBALS['db']->getRow($sql);
		$zc_goods_info['start_time'] = local_date('Y-m-d', $zc_goods_info['start_time']);
		$zc_goods_info['end_time'] = local_date('Y-m-d', $zc_goods_info['end_time']);
		$zc_goods_info['formated_amount'] = price_format($zc_goods_info['amount']);
		$zc_goods_info['formated_price'] = price_format($zc_goods_info['price']);
		$zc_goods_info['formated_shipping_fee'] = price_format($zc_goods_info['shipping_fee']);
		$zc_goods_info['return_time'] = sprintf($GLOBALS['_LANG']['zc_return_detail'], $zc_goods_info['return_time']);
		return $zc_goods_info;
	}

	return false;
}

function get_user_action_list($admin_id = 0, $string = '')
{
	$sql = 'SELECT action_list FROM ' . $GLOBALS['ecs']->table('admin_user') . (' WHERE user_id = \'' . $admin_id . '\'');
	$action_list = $GLOBALS['db']->getOne($sql);
	return $action_list;
}

function get_merchants_permissions($action_list, $string = '')
{
	if ($action_list == 'all') {
		return 1;
	}
	else {
		$action_list = explode(',', $action_list);

		if (in_array($string, $action_list)) {
			return 1;
		}
		else {
			return 0;
		}
	}
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

function get_category_list($cat_id = 0, $relation = 0, $seller_shop_cat = array(), $user_id = 0, $for_level = 0, $table = 'category')
{
	if ($relation == 0) {
		$parent_id = $GLOBALS['db']->getOne(' SELECT parent_id FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE cat_id = \'' . $cat_id . '\' '));
	}
	else if ($relation == 1) {
		$parent_id = $GLOBALS['db']->getOne(' SELECT parent_id FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE cat_id = \'' . $cat_id . '\' '));
	}
	else if ($relation == 2) {
		$parent_id = $cat_id;
	}

	$where = '';

	if ($user_id) {
		if (isset($seller_shop_cat['parent']) && $seller_shop_cat['parent'] && $for_level < 3) {
			$seller_shop_cat['parent'] = get_del_str_comma($seller_shop_cat['parent']);
			$where .= ' AND cat_id IN(' . $seller_shop_cat['parent'] . ')';
		}
	}

	$parent_id = empty($parent_id) ? 0 : $parent_id;
	$select = '';

	if ($table == 'category') {
		$select = ', cate_description, cate_title, cate_keywords ';
	}

	$sql = 'SELECT cat_id, cat_name ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE parent_id = \'' . $parent_id . '\' ' . $where);
	$category_list = $GLOBALS['db']->getAll($sql);

	foreach ($category_list as $key => $val) {
		if ($cat_id == $val['cat_id']) {
			$is_selected = 1;
		}
		else {
			$is_selected = 0;
		}

		$category_list[$key]['is_selected'] = $is_selected;
		$category_list[$key]['url'] = build_uri($table, array('cid' => $val['cat_id']), $val['cat_name']);
	}

	return $category_list;
}

function search_brand_list($goods_id = 0, $ru_id = NULL)
{
	$seller_id = 0;

	if (!is_null($ru_id)) {
		$seller_id = $ru_id;
	}
	else if (0 < $goods_id) {
		$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\'');
		$seller_id = $GLOBALS['db']->getOne($sql, true);
	}
	else {
		$adminru = get_admin_ru_id();
		$seller_id = $adminru['ru_id'];
	}

	$letter = !isset($_REQUEST['letter']) && empty($_REQUEST['letter']) ? '' : dsc_addslashes(trim($_REQUEST['letter']));
	$keyword = !isset($_REQUEST['keyword']) && empty($_REQUEST['keyword']) ? '' : dsc_addslashes(trim($_REQUEST['keyword']));
	$where = '';

	if (!empty($keyword)) {
		$where .= ' AND (brand_name LIKE \'%' . mysql_like_quote($keyword) . '%\' OR brand_letter LIKE \'%' . mysql_like_quote($keyword) . '%\') ';
	}

	$sql = 'SELECT brand_id, brand_name, brand_first_char FROM ' . $GLOBALS['ecs']->table('brand') . ' WHERE 1 ' . $where . ' ORDER BY sort_order';
	$res = $GLOBALS['db']->getAll($sql);
	$brand_list = read_static_cache('pin_brands', '/data/sc_file/');
	if ($brand_list === false && empty($keyword)) {
		$pin = new pin();
		$brand_list = array();

		foreach ($res as $key => $val) {
			if ($seller_id) {
				$val['is_brand'] = get_seller_brand_count($val['brand_id'], $seller_id);
			}
			else {
				$val['is_brand'] = 1;
			}

			if (0 < $val['is_brand']) {
				$brand_list[$key]['brand_id'] = $val['brand_id'];
				$brand_list[$key]['brand_name'] = $val['brand_name'];
				$brand_list[$key]['letter'] = !empty($val['brand_first_char']) ? $val['brand_first_char'] : strtoupper(substr($pin->Pinyin($val['brand_name'], EC_CHARSET), 0, 1));
			}
			else {
				unset($brand_list[$key]);
			}
		}

		!empty($brand_list) ? ksort($brand_list) : $brand_list;
		write_static_cache('pin_brands', $brand_list, '/data/sc_file/');
	}
	else {
		$brand_list = $res;

		if ($brand_list) {
			$pin = new pin();

			foreach ($brand_list as $key => $val) {
				if ($seller_id) {
					$val['is_brand'] = get_seller_brand_count($val['brand_id'], $seller_id);
				}
				else {
					$val['is_brand'] = 1;
				}

				if (0 < $val['is_brand']) {
					$brand_list[$key]['brand_id'] = $val['brand_id'];
					$brand_list[$key]['brand_name'] = $val['brand_name'];
					$brand_list[$key]['letter'] = !empty($val['brand_first_char']) ? $val['brand_first_char'] : strtoupper(substr($pin->Pinyin($val['brand_name'], EC_CHARSET), 0, 1));
				}
				else {
					unset($brand_list[$key]);
				}
			}
		}

		$arr = array();

		if ($brand_list) {
			foreach ($brand_list as $key => $val) {
				if (!empty($letter) && empty($keyword)) {
					if ($letter == 'QT' && !$brand_list[$key]['letter']) {
						$arr[$key] = $val;
					}
					else if ($letter == $brand_list[$key]['letter']) {
						$arr[$key] = $val;
					}
				}
				else {
					$arr = $brand_list;
				}
			}
		}

		$brand_list = $arr;
	}

	return $brand_list;
}

function get_seller_brand_count($brand_id = 0, $seller_id = 0)
{
	$where = '1';

	if ($brand_id) {
		$where .= ' AND lb.brand_id = \'' . $brand_id . '\'';
	}

	if ($seller_id) {
		$where .= ' AND msb.user_id = \'' . $seller_id . '\'';
	}

	$sql = 'SELECT lb.brand_id FROM ' . $GLOBALS['ecs']->table('link_brand') . ' AS lb,' . $GLOBALS['ecs']->table('brand') . ' AS b, ' . $GLOBALS['ecs']->table('merchants_shop_brand') . ' AS msb ' . (' WHERE ' . $where . ' AND lb.brand_id = b.brand_id AND lb.bid = msb.bid');
	$res = $GLOBALS['db']->getAll($sql, true);
	$count = count($res);
	return $count;
}

function available_shipping_list($region, $ru_id = 0, $is_limit = 0)
{
	$limit = '';

	if ($is_limit) {
		$limit = ' LIMIT 0, 1';
	}

	$shipping_list = array();
	$sql = 'SELECT s.* FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_transport_tpl') . ' AS gtt ON s.shipping_id = gtt.shipping_id' . (' WHERE gtt.user_id = \'' . $ru_id . '\' AND s.enabled = 1') . ' AND (FIND_IN_SET(\'' . $region[1] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[2] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[3] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[4] . '\', gtt.region_id))' . ' GROUP BY s.shipping_id' . $limit;
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

function get_complete_address($info = array())
{
	$complete_address = array();

	if ($info['country']) {
		$region_info = get_region_info($info['country']);
		$complete_address[] = $region_info['region_name'];
	}

	if ($info['province']) {
		$region_info = get_region_info($info['province']);
		$complete_address[] = $region_info['region_name'];
	}

	if ($info['city']) {
		$region_info = get_region_info($info['city']);
		$complete_address[] = $region_info['region_name'];
	}

	if ($info['district']) {
		$region_info = get_region_info($info['district']);
		$complete_address[] = $region_info['region_name'];
	}

	$complete_address = implode(' ', $complete_address);
	return $complete_address;
}

function get_store_order_info($id = 0, $type = 'id')
{
	if ($type == 'id') {
		$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('store_order') . (' WHERE id = \'' . $id . '\' ');
	}

	if ($type == 'order_id') {
		$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('store_order') . (' WHERE order_id = \'' . $id . '\' ');
	}

	$store_order_info = $GLOBALS['db']->getRow($sql);
	return $store_order_info;
}

function get_store_list($order_id = 0)
{
	$ru_id = get_ru_id($order_id);
	$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('offline_store') . (' WHERE ru_id = \'' . $ru_id . '\' ');
	$store_list = $GLOBALS['db']->getAll($sql);

	foreach ($store_list as $key => $val) {
		$info = array('country' => $val['country'], 'province' => $val['province'], 'city' => $val['city'], 'district' => $val['district']);
		$store_list[$key]['complete_store_address'] = get_complete_address($info) . ' ' . $val['stores_address'];
	}

	return $store_list;
}

function get_ru_id($order_id = 0)
{
	$sql = ' SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1 ');
	$ru_id = $GLOBALS['db']->getOne($sql);

	if (!$ru_id) {
		$adminru = get_admin_ru_id();
		$ru_id = $adminru['ru_id'];
	}

	return $ru_id;
}

function get_goods_checked_attr($values)
{
	foreach ($values as $key => $val) {
		if ($val['checked']) {
			return $val;
		}
	}
}

function get_cat_info($cat_id = 0, $select = array(), $table = 'category')
{
	if ($select) {
		$select = implode(',', $select);
	}
	else {
		$select = '*';
	}

	$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE cat_id = \'' . $cat_id . '\' LIMIT 1');
	$row = $GLOBALS['db']->getRow($sql);
	return $row;
}

function get_goods_attr_id($where_select = array(), $select = array(), $attr_type = 0, $retuen_db = 0)
{
	if ($select) {
		$select = implode(',', $select);
	}
	else {
		$select = 'ga.*, a.*';
	}

	$where = '';

	if (isset($where_select['goods_id'])) {
		$where .= ' AND ga.goods_id = \'' . $where_select['goods_id'] . '\'';
	}

	if (isset($where_select['attr_value']) && !empty($where_select['attr_value'])) {
		$where .= ' AND ga.attr_value = \'' . $where_select['attr_value'] . '\'';
	}

	if (isset($where_select['attr_id']) && !empty($where_select['attr_id'])) {
		$where .= ' AND ga.attr_id = \'' . $where_select['attr_id'] . '\'';
	}

	if (isset($where_select['goods_attr_id']) && !empty($where_select['goods_attr_id'])) {
		$where .= ' AND ga.goods_attr_id = \'' . $where_select['goods_attr_id'] . '\'';
	}

	if (isset($where_select['admin_id']) && !empty($where_select['admin_id'])) {
		$where .= ' AND ga.admin_id = \'' . $where_select['admin_id'] . '\'';
	}

	if ($attr_type && is_array($attr_type)) {
		$attr_type = implode(',', $attr_type);
		$where .= ' AND a.attr_type IN(' . $attr_type . ')';
	}
	else if ($attr_type) {
		$where .= ' AND a.attr_type = \'' . $attr_type . '\'';
	}

	$where .= ' ORDER BY a.sort_order, a.attr_id, ga.goods_attr_id';

	if ($retuen_db == 1) {
		$where .= ' LIMIT 1';
	}

	$sql = ' SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS ga, ' . $GLOBALS['ecs']->table('attribute') . ' AS a' . (' WHERE ga.attr_id = a.attr_id ' . $where);

	if ($retuen_db == 1) {
		return $GLOBALS['db']->getRow($sql);
	}
	else if ($retuen_db == 2) {
		return $GLOBALS['db']->getAll($sql);
	}
	else {
		return $GLOBALS['db']->getOne($sql, true);
	}
}

function get_goods_activity_info($act_id = 0, $select = array())
{
	if (!empty($select) && is_array($select)) {
		$select = implode(',', $select);
	}
	else if (empty($select)) {
		$select = '*';
	}

	$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table('goods_activity') . (' WHERE review_status = 3 AND act_id = \'' . $act_id . '\'');
	$activity = $GLOBALS['db']->getRow($sql);

	if ($activity) {
		$activity['goods_thumb'] = get_image_path($activity['act_id'], $activity['activity_thumb'], true);
	}

	return $activity;
}

function get_commission_rate($order_id = 0, $goods_id = 0, $type = 0)
{
	$sql = 'SELECT cat_id, proportion, commission_rate FROM ' . $GLOBALS['ecs']->table('seller_bill_goods') . (' WHERE order_id = \'' . $order_id . '\' AND goods_id = \'' . $goods_id . '\' LIMIT 1');
	$bill_goods = $GLOBALS['db']->getRow($sql);

	if ($bill_goods) {
		$cat_id = $bill_goods['cat_id'];
		$commission_rate = $bill_goods['proportion'];
	}
	else {
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
	}

	if ($type == 1) {
		$arr = array('commission_rate' => $commission_rate, 'cat_id' => $cat_id);
		return $arr;
	}
	else {
		return $commission_rate;
	}
}

function get_order_goods_commission($order_id = 0, $type = 0)
{
	$sql = ' SELECT goods_id, goods_price, goods_number, commission_rate, (goods_price * goods_number) AS goods_amount FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' ');
	$order_goods = $GLOBALS['db']->getAll($sql);
	$goods_amount = 0;
	$commission = 0;
	$cat = array();

	if ($order_goods) {
		foreach ($order_goods as $goods) {
			if ($type == 1) {
				$rate = get_commission_rate($order_id, $goods['goods_id'], $type);
				$cat[$goods['goods_id']]['commission_rate'] = $rate['commission_rate'];
				$cat[$goods['goods_id']]['cat_id'] = $rate['cat_id'];
				$commission_rate = $rate['commission_rate'];
			}
			else {
				$commission_rate = get_commission_rate($order_id, $goods['goods_id']);
			}

			if ($goods['commission_rate'] == 0) {
				$commission += $goods['goods_amount'] * $commission_rate;
				$goods_amount += $goods['goods_amount'];
			}
		}
	}

	if ($type == 1) {
		$arr = array('commission' => $commission, 'cat' => $cat, 'goods_amount' => $goods_amount);
		return $arr;
	}
	else {
		$arr = array('commission' => $commission, 'goods_amount' => $goods_amount);
		return $arr;
	}
}

function get_showapi()
{
	$paramArr = array('showapi_appid' => '29464', 'code' => '737110900011');
	$showapi_secret = 'ad31a785a8614098a4e16227c175145d';
	$paraStr = '';
	$signStr = '';
	ksort($paramArr);

	foreach ($paramArr as $key => $val) {
		if ($key != '' && $val != '') {
			$signStr .= $key . $val;
			$paraStr .= $key . '=' . urlencode($val) . '&';
		}
	}

	$signStr .= $showapi_secret;
	$sign = strtolower(md5($signStr));
	$paraStr .= 'showapi_sign=' . $sign;
	$http = new Http();
	$hres = $http->doPost('http://route.showapi.com/66-22', $paraStr);
	return json_decode($hres, true);
}

function get_jsapi($paramArr = array())
{
	$paraStr = '';

	foreach ($paramArr as $key => $val) {
		if ($key != '' && $val != '') {
			$signStr .= $key . $val;
			$paraStr .= $key . '=' . urlencode($val) . '&';
		}
	}

	$url = 'http://api.jisuapi.com/barcode2/query';
	$http = new Http();
	$hres = $http->doPost($url, $paraStr);
	return json_decode($hres, true);
}

function get_scan_code_config($ru_id = 0)
{
	$config = get_table_date('seller_shopinfo', 'ru_id = \'' . $ru_id . '\'', array('js_appkey', 'js_appsecret'));
	return $config;
}

function get_goods_extend_info($goods_id = 0)
{
	$arr = array();
	$select = 'width, height, depth, origincountry, originplace, assemblycountry, barcodetype, catena, isbasicunit, packagetype, grossweight, netweight, netcontent, licensenum, healthpermitnum';
	$sql = ' SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table('goods_extend') . (' WHERE goods_id = \'' . $goods_id . '\' LIMIT 1');
	$extend_info = $GLOBALS['db']->getRow($sql);

	foreach ($extend_info as $key => $val) {
		if (isset($GLOBALS['_LANG'][$key]) && !empty($val)) {
			$arr[$GLOBALS['_LANG'][$key]] = $val;
		}
	}

	return $arr;
}

function get_goods_user_area_position($ru_id = 0, $city_id = 0, $spec_arr = '', $goods_id = 0, $provinces_id = 0, $district_id = 0, $type = 0, $store_id = 0, $limit = 0)
{
	$where = '';

	if (0 < $goods_id) {
		$where .= 'AND s.goods_id =\'' . $goods_id . '\'';
	}

	if (0 < $provinces_id) {
		$where .= ' AND o.province = ' . $provinces_id;
	}

	if (0 < $city_id) {
		$where .= ' AND o.city = ' . $city_id;
	}

	if (0 < $district_id) {
		$where .= ' AND o.district = ' . $district_id;
	}

	if (0 < $store_id) {
		$where .= ' AND o.id = ' . $store_id;
	}
	else {
		$where .= ' AND o.ru_id = \'' . $ru_id . '\'';
	}

	if ($limit == 1) {
		$limit = ' LIMIT 1';
	}
	else {
		$limit = '';
	}

	$sql = 'SELECT o.id,s.goods_id,s.goods_number,o.ru_id,o.stores_name, o.province, o.city, o.district, o.stores_address, o.stores_tel, o.stores_opening_hours FROM ' . $GLOBALS['ecs']->table('offline_store') . ' AS o LEFT JOIN ' . $GLOBALS['ecs']->table('store_goods') . ' AS s ON o.id = s.store_id ' . 'WHERE  o.is_confirm=1 ' . $where . (' GROUP BY o.id ' . $limit);
	$store_list = $GLOBALS['db']->getAll($sql);

	if ($store_list) {
		if ($spec_arr) {
			$is_spec = explode(',', $spec_arr);
		}

		foreach ($store_list as $key => $row) {
			$unset_type = 0;

			if (is_spec($is_spec) == true) {
				$products = get_warehouse_id_attr_number($row['goods_id'], $spec_arr, $row['ru_id'], 0, 0, '', $row['id']);
				$store_list[$key]['goods_number'] = $products['product_number'];

				if ($products['product_number'] == 0) {
					unset($store_list[$key]);
					$unset_type = 1;
				}
			}

			if ($type == 0 && $unset_type == 0) {
				$region = array('province' => $row['province'], 'city' => $row['city'], 'district' => $row['district']);
				$store_list[$key]['area_info'] = get_area_region_info($region);
			}
		}
	}

	if (!empty($store_list)) {
		sort($store_list);
	}

	return $store_list;
}

function condition_format($conditon)
{
	switch ($conditon) {
	case 1:
		return $GLOBALS['_LANG']['spec_cat'];
		break;

	case 2:
		return $GLOBALS['_LANG']['spec_goods'];
		break;

	case 0:
		return $GLOBALS['_LANG']['all_goods'];
	default:
		return 'N/A';
		break;
	}
}

function get_parent_regions($region_id = 0)
{
	$sql = 'SELECT region_id,region_name FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE parent_id = (SELECT parent_id FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE region_id = \'' . $region_id . '\' )');
	return $GLOBALS['db']->GetAll($sql);
}

function set_clear_cache($dirName = '', $arr = array(), $type = 0)
{
	$j = 0;

	if (is_dir($dirName)) {
		if ($handle = opendir($dirName)) {
			while (false !== ($item = readdir($handle))) {
				if ($item != '.' && $item != '..' && $item != ADMIN_PATH && $item != SELLER_PATH && $item != STORES_PATH && $item != 'index.htm' && $item != 'index.html') {
					$aaa[] = $item;

					if (!is_dir($dirName . '/' . $item)) {
						if ($arr) {
							if (0 < $type) {
								$i = 0;

								foreach ($arr as $k => $v) {
									if ($v) {
										if (strstr($item, $v)) {
											$i++;
										}
									}
								}

								if ($i == 0) {
									$j++;
									@unlink($dirName . '/' . $item);
								}

								for ($i = 0; $i < 16; $i++) {
									$hash_dir = ROOT_PATH . 'temp/caches/' . dechex($i);
									$dirs = $hash_dir;
									set_clear_cache($dirs);
								}
							}
							else {
								foreach ($arr as $k => $v) {
									if ($v) {
										if (strstr($item, $v)) {
											$j++;
											@unlink($dirName . '/' . $item);
										}
									}
								}
							}
						}
						else {
							$j++;
							@unlink($dirName . '/' . $item);
						}
					}
				}
			}

			closedir($handle);
		}
	}

	return $j;
}

function get_cou_children($cat = '')
{
	$catlist = '';

	if ($cat) {
		$cat = explode(',', $cat);

		foreach ($cat as $key => $row) {
			$catlist .= get_children($row, 2) . ',';
		}

		$catlist = get_del_str_comma($catlist, 0, -1);
		$catlist = array_unique(explode(',', $catlist));
		$catlist = implode(',', $catlist);
		$cat = implode(',', $cat);
		$catlist = !empty($catlist) ? $catlist . ',' . $cat : $cat;
		$catlist = get_del_str_comma($catlist);
	}

	return $catlist;
}

function get_topparent_cat($cat_id = 0)
{
	static $cat_list = '';
	$sql = 'SELECT parent_id,cat_id,cat_name FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE cat_id=\'' . $cat_id . '\' LIMIT 1';
	$cat_info = $GLOBALS['db']->getRow($sql);

	if (!empty($cat_info['parent_id'])) {
		get_topparent_cat($cat_info['parent_id']);
	}
	else {
		$cat_list = $cat_info;
	}

	return $cat_list;
}

function get_category_store($cat_id = 0, $num = 6)
{
	$children = get_children($cat_id);
	$sql = ' SELECT g.user_id, COUNT(*) AS goods_num, ss.shop_name, ss.shop_title, ss.shop_logo, ss.logo_thumb, ss.street_thumb, ss.brand_thumb, ss.street_desc ' . ' FROM ' . $GLOBALS['ecs']->table('goods') . 'AS g ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS ss ON ss.ru_id = g.user_id ' . (' WHERE ' . $children . ' AND g.user_id > 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ') . (' GROUP BY g.user_id HAVING goods_num > 0 ORDER BY goods_num DESC LIMIT 0,' . $num . ' ');
	$store_list = $GLOBALS['db']->getAll($sql);

	foreach ($store_list as $key => $row) {
		$build_uri = array('urid' => $row['user_id'], 'append' => $row['shop_name']);
		$domain_url = get_seller_domain_url($row['user_id'], $build_uri);
		$store_list[$key]['shop_url'] = $domain_url['domain_name'];
	}

	return $store_list;
}

function get_floor_data($type = 'index', $id = 0)
{
	$data = array();

	if ($type == 'index') {
		$sql = 'SELECT c.cat_id, c.cat_name, c.cat_alias_name FROM ' . $GLOBALS['ecs']->table('template') . ' AS t ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('category') . ' AS c ON c.cat_id = t.id ' . ' WHERE t.filename = \'index\' AND t.type =1 AND t.theme=\'' . $GLOBALS['_CFG']['template'] . '\' AND t.remarks=\'\' order by t.sort_order asc';
		$template = $GLOBALS['db']->getAll($sql);

		foreach ($template as $key => $val) {
			$arr['id'] = $val['cat_id'];
			$arr['name'] = $val['cat_alias_name'];
			$data[] = $arr;
		}
	}

	return $data;
}

function upload_size_limit($type = 0)
{
	$upload_size_limit = $GLOBALS['_CFG']['upload_size_limit'] == '-1' ? ini_get('upload_max_filesize') . 'B' : $GLOBALS['_CFG']['upload_size_limit'] . 'KB';
	$upload_size_limit = strtoupper($upload_size_limit);

	if ($type == 0) {
		$size = $upload_size_limit[strlen($upload_size_limit) - 2];
		$upload_size_limit = intval(preg_replace('/(KB|MB)/i', '', $upload_size_limit));

		switch ($size) {
		case 'M':
			$upload_size_limit *= 1024 * 1024;
			break;

		case 'K':
			$upload_size_limit *= 1024;
			break;
		}
	}

	return $upload_size_limit;
}

function get_top_category_tree($parent_id = 0)
{
	$sql = 'SELECT cat_id, cat_name, style_icon, cat_icon, category_links, cat_alias_name FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE parent_id = 0 AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC ';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$row['cat_id']]['id'] = $row['cat_id'];
		$arr[$row['cat_id']]['cat_alias_name'] = $row['cat_alias_name'];
		$arr[$row['cat_id']]['url'] = build_uri('seckill', array('cid' => $row['cat_id']), $row['cat_name']);
		$arr[$row['cat_id']]['style_icon'] = $row['style_icon'];
		$arr[$row['cat_id']]['cat_icon'] = $row['cat_icon'];
		$arr[$row['cat_id']]['nolinkname'] = $row['cat_name'];
	}

	return $arr;
}

function get_filter_goods_list($filter = array('goods_ids' => '', 'cat_ids' => '', 'brand_ids' => '', 'user_id' => 0, 'mer_ids' => ''), $size = 10, $page = 1, $sort = 'sort_order', $order = 'ASC', $warehouse_id = 0, $area_id = 0, $type = '')
{
	$leftJoin = '';
	$where = ' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_show = 1 ';
	if (isset($filter['goods_ids']) && !empty($filter['goods_ids'])) {
		$where .= ' AND g.goods_id ' . db_create_in($filter['goods_ids']);
	}

	if (isset($filter['cat_ids']) && !empty($filter['cat_ids'])) {
		$cat_ids = array();

		foreach (explode(',', $filter['cat_ids']) as $key => $val) {
			$cat_ids[] = $val;
			$cat_keys = get_array_keys_cat($val);
			$cat_ids = array_merge($cat_ids, $cat_keys);
		}

		$cat_ids = array_unique($cat_ids);
		$where .= ' AND g.cat_id ' . db_create_in($cat_ids);
	}

	if (isset($filter['brand_ids']) && !empty($filter['brand_ids'])) {
		$where = ' AND g.brand_id ' . db_create_in($filter['brand_ids']);
	}

	if ($GLOBALS['_CFG']['region_store_enabled']) {
		if (isset($filter['mer_ids']) && !empty($filter['mer_ids'])) {
			$where .= ' AND g.user_id ' . db_create_in($filter['mer_ids']);
		}
	}
	else if (isset($filter['user_id'])) {
		$where .= ' AND g.user_id = \'' . $filter['user_id'] . '\' ';
	}

	$shop_price = ' IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price ';
	$promote_price = ' IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price ';
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_goods') . (' AS wg ON g.goods_id = wg.goods_id AND wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' AS wag ON g.goods_id = wag.goods_id AND wag.region_id = \'' . $area_id . '\' ');
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . (' AS mp ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ');
	$count_where = '';

	if ($type == 'goods') {
		$select = 'g.goods_id';
		$count_where .= $where . ' LIMIT ' . $size;
	}
	else {
		$select = 'COUNT(*)';
		$count_where = $where;
	}

	$sql = ' SELECT  ' . $select . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . (' WHERE 1 ' . $count_where . ' ');

	if ($type == 'goods') {
		$record_count = count($GLOBALS['db']->getAll($sql));
	}
	else {
		$record_count = $GLOBALS['db']->getOne($sql);
	}

	$page_count = 0 < $record_count ? ceil($record_count / $size) : 1;
	$sql = ' SELECT g.goods_id, g.goods_name, g.goods_thumb, g.promote_start_date, g.promote_end_date, g.product_price, g.product_promote_price, ' . $shop_price . ',' . $promote_price . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . (' WHERE 1 ' . $where . ' GROUP BY g.goods_id ORDER BY ' . $sort . ' ' . $order . ' ');
	$start = ($page - 1) * $size;
	$res = $GLOBALS['db']->selectLimit($sql, $size, $start);
	$arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if (0 < $row['promote_price']) {
			$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
		$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$arr[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$arr[$row['goods_id']]['promote_price'] = 0 < $promote_price ? price_format($promote_price) : '';
	}

	return array('goods_list' => $arr, 'page_count' => $page_count, 'record_count' => $record_count);
}

function get_top_presale_goods($goods_id, $cat_id)
{
	$now = gmtime();
	$sql = 'SELECT a.*, g.goods_thumb, g.goods_img, g.goods_name, g.shop_price, g.market_price, g.sales_volume, s.* FROM ' . $GLOBALS['ecs']->table('presale_activity') . ' AS a ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON a.goods_id = g.goods_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS s ON a.user_id = s.ru_id ' . ('WHERE a.cat_id = \'' . $cat_id . '\' AND g.is_on_sale = 0 AND a.review_status = 3 AND g.is_show = 1 AND a.start_time <= \'' . $now . '\' AND a.end_time >= \'' . $now . '\' AND g.goods_id <> \'' . $goods_id . '\' ORDER BY g.click_count DESC LIMIT 5 ');
	$res = $GLOBALS['db']->getAll($sql);

	if ($res) {
		foreach ($res as $key => $row) {
			$res[$key]['goods_name'] = $row['goods_name'];
			$res[$key]['shop_price'] = price_format($res[$key]['shop_price']);
			$res[$key]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$res[$key]['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$res[$key]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
			$res[$key]['url'] = build_uri('presale', array('act' => 'view', 'presaleid' => $row['act_id']), $row['goods_name']);
		}
	}

	return $res;
}

function get_brand_image_path($image = '')
{
	$url = empty($image) ? $GLOBALS['_CFG']['no_brand'] : $image;
	return $url;
}

function create_snapshot($order_id = 0)
{
	$sql = ' SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE main_order_id = \'' . $order_id . '\' ');
	if ($order_ids = $GLOBALS['db']->getAll($sql) && 0 < $order_id) {
		foreach ($order_ids as $val) {
			$sql = 'SELECT oi.order_sn, oi.user_id, og.ru_id, og.goods_id, og.goods_name, og.goods_sn, og.goods_attr, og.goods_attr_id, og.goods_price, og.goods_number,oi.shipping_fee, g.goods_weight, g.add_time, g.goods_desc, g.goods_img FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON oi.order_id = og.order_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = og.goods_id ' . (' WHERE oi.order_id = \'' . $val['order_id'] . '\' ');
			$result = $GLOBALS['db']->getAll($sql);

			foreach ($result as $v) {
				insert_snapshot($v);
			}
		}
	}
	else {
		$sql = ' SELECT oi.order_sn, oi.user_id, og.ru_id, og.goods_id, og.goods_name, og.goods_sn, og.goods_attr, og.goods_attr_id, og.goods_price, og.goods_number,oi.shipping_fee, g.goods_weight, g.add_time, g.goods_desc, g.goods_img FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON oi.order_id = og.order_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = og.goods_id ' . (' WHERE oi.order_id = \'' . $order_id . '\' ');
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
		$snapshot_info = array('order_sn' => $arr['order_sn'], 'user_id' => $arr['user_id'], 'goods_id' => $arr['goods_id'], 'goods_name' => addslashes($arr['goods_name']), 'goods_sn' => $arr['goods_sn'], 'shop_price' => $arr['goods_price'], 'goods_number' => $arr['goods_number'], 'shipping_fee' => $arr['shipping_fee'], 'rz_shopName' => get_shop_name($arr['ru_id'], 1), 'goods_weight' => $arr['goods_weight'], 'add_time' => $arr['add_time'], 'goods_attr' => $arr['goods_attr'], 'goods_attr_id' => $arr['goods_attr_id'], 'ru_id' => $arr['ru_id'], 'goods_desc' => $arr['goods_desc'], 'goods_img' => $arr['goods_img'], 'snapshot_time' => gmtime());
		return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('trade_snapshot'), $snapshot_info, 'INSERT');
	}
	else {
		return 0;
	}
}

function find_snapshot($order_sn = '', $goods_id = 0)
{
	$sql = ' SELECT trade_id FROM ' . $GLOBALS['ecs']->table('trade_snapshot') . (' WHERE order_sn = \'' . $order_sn . '\' AND goods_id = \'' . $goods_id . '\' ');
	return $GLOBALS['db']->getOne($sql);
}

function get_presale_num($order_id)
{
	$sql = 'SELECT pa.pre_num , og.goods_id FROM ' . $GLOBALS['ecs']->table('presale_activity') . ' AS pa' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON pa.goods_id = og.goods_id ' . (' WHERE og.order_id = \'' . $order_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $v) {
		$pre_num = $v['pre_num'];
		$pre_num += 1;
		$goods_id = $v['goods_id'];
		$sql = 'update ' . $GLOBALS['ecs']->table('presale_activity') . (' set pre_num=\'' . $pre_num . '\' WHERE goods_id = \'' . $goods_id . '\'');
		$GLOBALS['db']->query($sql);
	}
}

function is_update_sale($order_id)
{
	$sql = 'SELECT is_update_sale FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\'');
	return $GLOBALS['db']->getOne($sql, true);
}

function get_goods_sale($order_id = 0, $order = array())
{
	if (empty($order)) {
		$sql = 'SELECT order_id, pay_status, shipping_status FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
		$order = $GLOBALS['db']->getRow($sql);
	}

	$is_volume = 0;
	if ($GLOBALS['_CFG']['sales_volume_time'] == SALES_PAY && $order['pay_status'] == PS_PAYED) {
		$is_volume = 1;
	}
	else {
		if ($GLOBALS['_CFG']['sales_volume_time'] == SALES_SHIP && $order['shipping_status'] == SS_SHIPPED) {
			$is_volume = 1;
		}
	}

	if ($is_volume == 1) {
		$is_update_sale = is_update_sale($order['order_id']);

		if ($is_update_sale < 1) {
			$sql = 'SELECT goods_id, goods_number, send_number FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE order_id = \'' . $order['order_id'] . '\'';
			$order_res = $GLOBALS['db']->getAll($sql);

			foreach ($order_res as $idx => $val) {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . ' SET sales_volume = sales_volume + \'' . $val['send_number'] . '\' WHERE goods_id = \'' . $val['goods_id'] . '\'';
				$GLOBALS['db']->query($sql);
			}
		}
	}
}

function users_log_change($user_id, $change_type = USER_LOGIN)
{
	$ipCity = get_ip_area_name();
	$change_city = $ipCity['area_name'];
	$admin_id = 0;

	if (0 < $_SESSION['admin_id']) {
		$admin_id = $_SESSION['admin_id'];
	}

	$users_log = array('user_id' => $user_id, 'change_time' => gmtime(), 'change_type' => $change_type, 'ip_address' => real_ip(), 'change_city' => $change_city, 'admin_id' => $admin_id, 'logon_service' => 'pc');
	$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('users_log'), $users_log, 'INSERT');
}

function users_log_change_type($old_user = array(), $other = array(), $user_id = 0)
{
	if ($old_user['old_email'] != $other['email']) {
		users_log_change($user_id, USER_EMAIL);
	}

	if ($old_user['old_credit_line'] != $other['credit_line']) {
		users_log_change($user_id, USER_LINE);
	}

	if ($old_user['password']) {
		users_log_change($user_id, USER_LPASS);
	}

	if ($old_user['old_mobile_phone'] != $other['mobile_phone']) {
		users_log_change($user_id, USER_PHONE);
	}

	if ($old_user['old_user_rank'] != $other['user_rank'] || $old_user['old_sex'] != $other['sex'] || $old_user['old_birthday'] != $other['birthday'] || $old_user['old_msn'] != $other['msn'] || $old_user['old_qq'] != $other['qq'] || $old_user['old_office_phone'] != $other['office_phone'] || $old_user['old_home_phone'] != $other['home_phone'] || $old_user['old_passwd_answer'] != $other['passwd_answer'] || $old_user['old_sel_question'] != $other['sel_question']) {
		users_log_change($user_id, USER_INFO);
	}
}

function set_prevent_token($cookie = '')
{
	if ($cookie) {
		unset($_COOKIE[$cookie]);
		$sc_rand = rand(1000, 9999);
		$sc_guid = sc_guid();
		$prevent_cookie = MD5($sc_guid . '-' . $sc_rand);
		setcookie($cookie, $prevent_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
		$GLOBALS['smarty']->assign('sc_guid', $sc_guid);
		$GLOBALS['smarty']->assign('sc_rand', $sc_rand);
	}
}

function get_prevent_token($cookie = '')
{
	$is_prevent = 0;

	if ($cookie) {
		$sc_rand = isset($_POST['sc_rand']) && !empty($_POST['sc_rand']) ? dsc_addslashes(trim($_POST['sc_rand'])) : '';
		$sc_guid = isset($_POST['sc_guid']) && !empty($_POST['sc_guid']) ? dsc_addslashes(trim($_POST['sc_guid'])) : '';
		$prevent_cookie = MD5($sc_guid . '-' . $sc_rand);
		if (!empty($sc_guid) && !empty($sc_rand) && isset($_COOKIE[$cookie])) {
			if (!empty($_COOKIE[$cookie])) {
				if (!($_COOKIE[$cookie] == $prevent_cookie)) {
					$is_prevent = 1;
				}
			}
			else {
				$is_prevent = 1;
			}
		}
	}

	return $is_prevent;
}

function check_main_order_status($order_id)
{
	$sql = ' SELECT main_order_id FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\' ');
	$main_order_id = $GLOBALS['db']->getOne($sql);

	if ($main_order_id) {
		$sql = ' SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE main_order_id = \'' . $main_order_id . '\' ');
		$order_ids = $GLOBALS['db']->getAll($sql);
		$order_status = OS_CONFIRMED;
		$pay_status = PS_PAYED;

		foreach ($order_ids as $v) {
			$sql = ' SELECT order_status, pay_status FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $v['order_id'] . '\' ';
			$order_info = $GLOBALS['db']->getRow($sql);

			if ($order_info['order_status'] != OS_CONFIRMED) {
				$order_status = OS_UNCONFIRMED;
			}

			if ($order_info['pay_status'] != PS_PAYED) {
				$pay_status = PS_UNPAYED;
			}
		}

		$sql = ' UPDATE ' . $GLOBALS['ecs']->table('order_info') . (' SET order_status = \'' . $order_status . '\', pay_status = \'' . $pay_status . '\' WHERE order_id = \'' . $main_order_id . '\' ');
		$GLOBALS['db']->query($sql);
	}
}

function get_cou_region_list($cou_id = 0)
{
	$arr = array('free_value_name' => '');
	$sql = 'SELECT region_list FROM' . $GLOBALS['ecs']->table('coupons_region') . ('WHERE cou_id = \'' . $cou_id . '\' LIMIT 1');
	$arr['free_value'] = $GLOBALS['db']->getOne($sql);
	$sql = 'SELECT region_name FROM' . $GLOBALS['ecs']->table('region') . 'WHERE region_id' . db_create_in($arr['free_value']);
	$region_list = $GLOBALS['db']->getCol($sql);

	if ($region_list) {
		$arr['free_value_name'] = implode(',', $region_list);
	}

	return $arr;
}

function get_region_level($region_id = 0)
{
	$array = array();

	while (0 < $region_id) {
		$array[] = intval($region_id);
		$sql = ' SELECT parent_id FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE region_id = \'' . $region_id . '\' ');
		$region_id = $GLOBALS['db']->getOne($sql);
	}

	$array = array_reverse($array);
	return $array;
}

function get_region_store_list()
{
	$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('region_store') . ' ORDER BY rs_name ';
	$data = $GLOBALS['db']->getAll($sql);
	return $data;
}

function get_rs_where($region_id = 0, $field = 'g.user_id')
{
	$where = '';

	if ($GLOBALS['_CFG']['region_store_enabled']) {
		if (!empty($region_id)) {
			$sql = ' SELECT user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . (' WHERE region_id = \'' . $region_id . '\' ');
			$user_ids = $GLOBALS['db']->getCol($sql);

			if (!empty($user_ids)) {
				$where .= ' AND (' . $field . ' ' . db_create_in($user_ids) . (' OR ' . $field . ' = 0 )');
			}
			else {
				$where .= ' AND ' . $field . ' = 0 ';
			}
		}
		else {
			$where .= ' AND ' . $field . ' = 0 ';
		}
	}

	return $where;
}

function get_favourable_merchants($userFav_type = 0, $userFav_type_ext = '', $rs_id = 0, $type = 0, $ru_id = 0)
{
	if ($userFav_type != GENERAL_AUDIENCE && !empty($userFav_type_ext)) {
		if (0 < $rs_id) {
			if ($type == 1) {
				if ($ru_id) {
					if (in_array($ru_id, explode(',', $userFav_type_ext))) {
						return true;
					}
					else {
						return false;
					}
				}
				else {
					return explode(',', $userFav_type_ext);
				}
			}
			else {
				return $userFav_type_ext;
			}
		}
		else {
			$sql = ' SELECT m.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS m LEFT JOIN ' . $GLOBALS['ecs']->table('rs_region') . ' AS r ON r.region_id = m.region_id ' . ' WHERE r.rs_id ' . db_create_in($userFav_type_ext);
			$res = $GLOBALS['db']->getCol($sql);

			if ($res) {
				if ($type == 1) {
					if ($ru_id) {
						if (in_array($ru_id, $res)) {
							return true;
						}
						else {
							return false;
						}
					}
					else {
						return $res;
					}
				}
				else {
					return implode(',', $res);
				}
			}
		}
	}
	else {
		if ($userFav_type != GENERAL_AUDIENCE && empty($userFav_type_ext)) {
			return NULL;
		}
	}
}

function get_fine_all_category($where = array())
{
	$sql_where = ' WHERE 1';

	if ($where) {
		if (isset($where['is_show'])) {
			$sql_where .= ' AND is_show = \'' . $where['is_show'] . '\'';
		}
	}

	$sql = 'SELECT cat_id, cat_name, parent_id FROM ' . $GLOBALS['ecs']->table('category') . $sql_where;
	return $GLOBALS['db']->GetAll($sql);
}

function update_comment_seller($goods_id, $num)
{
	$sql = 'SELECT merchants_comment_number,goods_id FROM' . $GLOBALS['ecs']->table('intelligent_weight') . (' WHERE goods_id = \'' . $goods_id . '\' LIMIT 1');
	$res = $GLOBALS['db']->getRow($sql);
	$num['merchants_comment_number'] += $res['merchants_comment_number'];

	if ($res['goods_id']) {
		return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('intelligent_weight'), $num, 'UPDATE', 'goods_id = \'' . $goods_id . '\'');
	}
	else {
		return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('intelligent_weight'), $num, 'INSERT', 'goods_id = \'' . $goods_id . '\'');
	}
}

function update_attention_num($goods_id, $num)
{
	$sql = 'SELECT user_attention_number,goods_id FROM' . $GLOBALS['ecs']->table('intelligent_weight') . (' WHERE goods_id = \'' . $goods_id . '\' LIMIT 1');
	$res = $GLOBALS['db']->getRow($sql);
	$num['user_attention_number'] += $res['user_attention_number'];

	if ($res['goods_id']) {
		return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('intelligent_weight'), $num, 'UPDATE', 'goods_id = \'' . $goods_id . '\'');
	}
	else {
		return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('intelligent_weight'), $num, 'INSERT', 'goods_id = \'' . $goods_id . '\'');
	}
}

function correct_order_sn($order_sn = '')
{
	$new_order_sn = get_order_sn();
	$sql = 'UPDATE' . $GLOBALS['ecs']->table('order_info') . ('SET order_sn = \'' . $new_order_sn . '\' WHERE order_sn = \'' . $order_sn . '\'');
	$GLOBALS['db']->query($sql);
	$sql = 'UPDATE' . $GLOBALS['ecs']->table('stages') . ('SET order_sn = \'' . $new_order_sn . '\' WHERE order_sn = \'' . $order_sn . '\'');
	$GLOBALS['db']->query($sql);
	$sql = 'UPDATE' . $GLOBALS['ecs']->table('trade_snapshot') . ('SET order_sn = \'' . $new_order_sn . '\' WHERE order_sn = \'' . $order_sn . '\'');
	$GLOBALS['db']->query($sql);
	$sql = 'UPDATE' . $GLOBALS['ecs']->table('virtual_card') . ('SET order_sn = \'' . $new_order_sn . '\' WHERE order_sn = \'' . $order_sn . '\'');
	$GLOBALS['db']->query($sql);
	$sql = 'UPDATE' . $GLOBALS['ecs']->table('complaint') . ('SET order_sn = \'' . $new_order_sn . '\' WHERE order_sn = \'' . $order_sn . '\'');
	$GLOBALS['db']->query($sql);
	$new_order_sn_msg = '，订单号已修改为：' . $new_order_sn;
	$sql = 'UPDATE' . $GLOBALS['ecs']->table('account_log') . ('SET change_desc = concat(change_desc,\'' . $new_order_sn_msg . '\') WHERE change_desc LIKE \'%') . $order_sn . '%\'';
	$GLOBALS['db']->query($sql);
	$new_order_sn_msg = '，订单号已修改为：' . $new_order_sn;
	$sql = 'UPDATE' . $GLOBALS['ecs']->table('admin_log') . ('SET log_info = concat(log_info,\'' . $new_order_sn_msg . '\') WHERE log_info LIKE \'%') . $order_sn . '%\'';
	$GLOBALS['db']->query($sql);
	return $new_order_sn;
}

function get_seo_words($type)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('seo') . (' WHERE type=\'' . $type . '\' LIMIT 1');
	$res = $GLOBALS['db']->getRow($sql);
	return $res;
}

function get_category_seo_words($cat_id)
{
	$sql = 'SELECT cate_title, cate_keywords, cate_description FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE cat_id=\'' . $cat_id . '\' LIMIT 1');
	$res = $GLOBALS['db']->getRow($sql);
	return $res;
}

function judge_store_goods($goods_id)
{
	$store_ids = array();
	$sql = 'SELECT DISTINCT store_id FROM' . $GLOBALS['ecs']->table('offline_store') . ' AS o LEFT JOIN' . $GLOBALS['ecs']->table('store_goods') . (' AS s ON o.id = s.store_id WHERE s.goods_id = \'' . $goods_id . '\' AND o.is_confirm = 1');
	$store_goods = $GLOBALS['db']->getCol($sql);
	$sql = 'SELECT DISTINCT store_id FROM' . $GLOBALS['ecs']->table('offline_store') . ' AS o LEFT JOIN' . $GLOBALS['ecs']->table('store_products') . (' AS s ON o.id = s.store_id WHERE s.goods_id = \'' . $goods_id . '\' AND o.is_confirm = 1');
	$store_products = $GLOBALS['db']->getCol($sql);
	$store_ids = array_merge($store_ids, $store_goods, $store_products);
	$store_ids = array_unique($store_ids);
	return $store_ids;
}

function get_goods_isshow($cat_id)
{
	$is_show = 1;

	if (0 < $cat_id) {
		$sql = "SELECT IF(a.is_show=0,0,IF(a.parent_id=0,1,IF(b.is_show=1,IF(b.parent_id=0,1,IF(c.cat_id>0,c.is_show,0)),0))) as is_show\r\nFROM" . $GLOBALS['ecs']->table('category') . ' AS a LEFT JOIN ' . $GLOBALS['ecs']->table('category') . ' AS b  ON b.cat_id = a.parent_id LEFT JOIN' . $GLOBALS['ecs']->table('category') . (' AS c ON c.cat_id = b.parent_id where a.cat_id = \'' . $cat_id . '\'');
		$is_show = $GLOBALS['db']->getOne($sql);
	}

	return $is_show;
}

function judge_supplier_enabled()
{
	if (file_exists(ROOT_PATH . 'suppliers')) {
		return true;
	}
	else {
		return false;
	}
}

function judge_wholesale_use($return_type = 0)
{
	if ($_SESSION['user_id']) {
		if ($GLOBALS['_CFG']['wholesale_user_rank'] == 0) {
			$is_seller = get_is_seller();

			if ($is_seller == 0) {
				if ($return_type == 1) {
					return false;
				}
				else {
					show_message($GLOBALS['_LANG']['not_seller_user']);
				}
			}
		}
	}
	else if ($return_type == 1) {
		return false;
	}
	else {
		show_message($GLOBALS['_LANG']['not_login_user']);
	}

	return true;
}

function wxapp_enabled()
{
	if (file_exists(ROOT_PATH . 'mobile/config/app.php')) {
		$data = include ROOT_PATH . 'mobile/config/app.php';
		return isset($data['wxapp_on']) ? $data['wxapp_on'] : false;
	}
	else {
		return false;
	}
}

function judge_seller_grade_expiry($ru_id = 0)
{
	if (0 < $ru_id) {
		$seller_grade = get_seller_grade($ru_id);
		$end_time = local_date('Y', $seller_grade['add_time']) + $seller_grade['year_num'] . '-' . local_date('m-d H:i:s', $seller_grade['add_time']);
		$end_stamp = local_strtotime($end_time);
		$is_expiry = $end_stamp < gmtime() ? true : false;
		return $is_expiry;
	}

	return false;
}

function push_template_curl($code = '', $pushData = array(), $order_url = '', $user_id = 0, $shop_url = '')
{
	if (!empty($pushData)) {
		$order_url = urlencode(base64_encode($order_url));
		$data = urlencode(serialize($pushData));
		$api_url = $shop_url . 'mobile/?m=wechat&c=api&user_id=' . $user_id . '&code=' . urlencode($code) . '&pushData=' . $data . '&url=' . $order_url;
		curlGet_weixin($api_url);
	}
}

function curlGet_weixin($url, $timeout = 5, $header = '')
{
	$defaultHeader = "\$header = \"User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.%d Safari/537.%d\\r\\n\";\r\n        \$header .= \"Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\\r\\n\";\r\n        \$header .= \"Accept-language: zh-cn,zh;q=0.5\\r\\n\";\r\n        \$header .= \"Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\\r\\n\";\r\n        \$header = sprintf(\$header, time(), time() + rand(1000, 9999));";
	$header = empty($header) ? $defaultHeader : $header;
	$ch = curl_init();

	if (stripos($url, 'https://') !== false) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSLVERSION, 1);
	}

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function judge_user_special_rank($user_id = 0)
{
	$user_rank = get_table_date('users', 'user_id = \'' . $user_id . '\'', array('user_rank'), 2);
	$special_rank = get_table_date('user_rank', 'rank_id = \'' . $user_rank . '\'', array('special_rank'), 2);
	return $special_rank;
}

function get_warehouse_area_info($other = array())
{
	$area_info = get_area_info($other['province_id']);
	$area_id = $area_info['region_id'];
	$where = 'regionId = \'' . $other['province_id'] . '\'';
	$date = array('parent_id');
	$region_id = get_table_date('region_warehouse', $where, $date, 2);
	$where = 'regionId = \'' . $other['city_id'] . '\'';
	$date = array('region_id');
	$city_id = get_table_date('region_warehouse', $where, $date, 2);
	$area = array('region_id' => $region_id, 'area_id' => $area_id, 'city_id' => $city_id);
	return $area;
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
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
