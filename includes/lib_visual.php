<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function create_html($out = '', $cache_id = 0, $cachename = '', $suffix = '', $topic_type = 0)
{
	$smarty = new cls_template();
	$smarty->cache_lifetime = $_CFG['cache_time'];
	$seller_tem = '';

	if ($topic_type == 1) {
		$smarty->cache_dir = ROOT_PATH . 'data/topic';
		$seller_tem = 'topic_' . $cache_id;
	}
	else if ($topic_type == 2) {
		$smarty->cache_dir = ROOT_PATH . 'data';
	}
	else if ($topic_type == 3) {
		$smarty->cache_dir = ROOT_PATH . 'data/home_Templates';
		$seller_tem = $GLOBALS['_CFG']['template'];
	}
	else if ($topic_type == 5) {
		$smarty->cache_dir = ROOT_PATH . 'data/cms_Templates';
		$seller_tem = $GLOBALS['_CFG']['template'];
	}
	else {
		$smarty->cache_dir = ROOT_PATH . 'data/seller_templates';

		if (0 < $cache_id) {
			$seller_tem = 'seller_tem_' . $cache_id;
		}
		else {
			$seller_tem = 'seller_tem';
		}
	}

	if ($topic_type != 2) {
		$suffix = $suffix . '/temp';
	}

	$back = '';

	if ($out) {
		$out = str_replace("\r", '', $out);

		while (strpos($out, "\n\n") !== false) {
			$out = str_replace("\n\n", "\n", $out);
		}

		$hash_dir = $smarty->cache_dir . '/' . $seller_tem . '/' . $suffix;

		if (!is_dir($hash_dir)) {
			make_dir($hash_dir);
		}

		if ($cachename) {
			$files = explode('.', $cachename);
			$files_count = count($files) - (count($files) - 1);
			$suffix_name = $files[$files_count];

			if (2 < count($files)) {
				$path = count($files) - 1;
				$name = '';

				if ($files[$path]) {
					foreach ($files[$path] as $row) {
						$name .= $row . '.';
					}

					$name = substr($name, 0, -1);
				}

				$file_path = explode('/', $name);

				if (2 < $file_path) {
					$path = count($file_path) - 1;
					$cachename = $file_path[$path];
				}
				else {
					$cachename = $file_path[0];
				}
			}
			else {
				$cachename = $files[0];
			}

			$file_put = write_static_file_cache($cachename, $out, $suffix_name, $hash_dir . '/', 1);
		}
		else {
			$file_put = false;
		}

		if ($file_put === false) {
			trigger_error('can\'t write:' . $hash_dir . '/' . $cachename);
			$back = '';
		}
		else {
			$back = $cachename;
		}

		$smarty->template = array();
	}
	else {
		$back = '';
	}

	return $back;
}

function get_html_file($name)
{
	$smarty = new cls_template();

	if (file_exists($name)) {
		$smarty->_current_file = $name;
		$name = read_static_flie_cache($name, '', '', 1);
		$source = $smarty->fetch_str($name);
	}
	else {
		$source = '';
	}

	return $source;
}

function get_seller_templates($ru_id = 0, $type = 0, $tem = '', $pre_type = 0)
{
	if ($type == 0) {
		$seller_templates = 'pc_page';
	}
	else if ($type == 2) {
		$seller_templates = 'pc_head';
	}
	else {
		$seller_templates = 'pc_html';
	}

	$arr['tem'] = $tem;
	$arr['is_temp'] = 0;
	$seller_tem = 'seller_tem_' . $ru_id;

	if ($ru_id == 0) {
		$seller_tem = 'seller_tem';
	}

	$filename = ROOT_PATH . 'data/seller_templates' . '/' . $seller_tem . '/' . $arr['tem'] . '/' . $seller_templates . '.php';

	if ($pre_type == 1) {
		$pre_file = ROOT_PATH . 'data/seller_templates' . '/' . $seller_tem . '/' . $arr['tem'] . '/temp';

		if (is_dir($pre_file)) {
			$filename = $pre_file . '/' . $seller_templates . '.php';
			$arr['is_temp'] = 1;
		}
	}

	$arr['out'] = get_html_file($filename);
	return $arr;
}

function get_seller_template_info($template_name = '', $ru_id = 0, $theme = '')
{
	if (empty($template_style) || $template_style == '') {
		$template_style = '';
	}

	if (0 < $ru_id) {
		$seller_tem = 'seller_tem_' . $ru_id;
	}
	else {
		$seller_tem = 'seller_tem';
	}

	$info = array();
	$ext = array('png', 'gif', 'jpg', 'jpeg');
	$info['code'] = $template_name;
	$info['screenshot'] = '';

	if ($theme == '') {
		foreach ($ext as $val) {
			if (file_exists('../data/seller_templates/' . $seller_tem . '/' . $template_name . '/screenshot.' . $val)) {
				$info['screenshot'] = '../data/seller_templates/' . $seller_tem . '/' . $template_name . '/screenshot.' . $val;
				break;
			}
		}

		foreach ($ext as $val) {
			if (file_exists('../data/seller_templates/' . $seller_tem . '/' . $template_name . '/template.' . $val)) {
				$info['template'] = '../data/seller_templates/' . $seller_tem . '/' . $template_name . '/template.' . $val;
				break;
			}
		}

		$info_path = '../data/seller_templates/' . $seller_tem . '/' . $template_name . '/tpl_info.txt';
	}
	else {
		foreach ($ext as $val) {
			if (file_exists('../data/home_Templates/' . $theme . '/' . $template_name . '/screenshot.' . $val)) {
				$info['screenshot'] = '../data/home_Templates/' . $theme . '/' . $template_name . '/screenshot.' . $val;
				break;
			}
		}

		foreach ($ext as $val) {
			if (file_exists('../data/home_Templates/' . $theme . '/' . $template_name . '/template.' . $val)) {
				$info['template'] = '../data/home_Templates/' . $theme . '/' . $template_name . '/template.' . $val;
				break;
			}
		}

		$info_path = '../data/home_Templates/' . $theme . '/' . $template_name . '/tpl_info.txt';
	}

	if (file_exists($info_path) && !empty($template_name)) {
		$custom_content = addslashes(iconv('GB2312', 'UTF-8', $info_path));
		$arr = @array_slice(file($info_path), 0, 9);
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

function object_to_array($obj)
{
	$_arr = is_object($obj) ? get_object_vars($obj) : $obj;

	if ($_arr) {
		foreach ($_arr as $key => $val) {
			$val = is_array($val) || is_object($val) ? object_to_array($val) : $val;
			$arr[$key] = $val;
		}
	}
	else {
		$arr = array();
	}

	return $arr;
}

function getleft_attr($type = 0, $ru_id = 0, $tem = '', $theme = '')
{
	$sql = 'SELECT bg_color ,img_file ,if_show,bgrepeat,align,fileurl FROM' . $GLOBALS['ecs']->table('templates_left') . (' WHERE ru_id = \'' . $ru_id . '\' AND type = \'' . $type . '\' AND seller_templates = \'' . $tem . '\' AND theme = \'' . $theme . '\'');
	$templates_left = $GLOBALS['db']->getRow($sql);

	if ($templates_left['img_file']) {
		$templates_left['img_file'] = str_replace('../', '', $templates_left['img_file']);
		$templates_left['img_file'] = get_image_path(0, $templates_left['img_file']);
	}

	return $templates_left;
}

function del_DirAndFile($dirName)
{
	if (is_dir($dirName)) {
		if ($handle = opendir($dirName)) {
			while (false !== ($item = readdir($handle))) {
				if ($item != '.' && $item != '..') {
					if (is_dir($dirName . '/' . $item)) {
						del_DirAndFile($dirName . '/' . $item);
					}
					else {
						unlink($dirName . '/' . $item);
					}
				}
			}

			closedir($handle);
			return rmdir($dirName);
		}
	}
	else {
		return true;
	}
}

function recurse_copy($src, $des, $type = 0)
{
	$dir = opendir($src);

	if (!is_dir($des)) {
		make_dir($des);
	}

	while (false !== ($file = readdir($dir))) {
		if ($file != '.' && $file != '..') {
			if (is_dir($src . '/' . $file)) {
				recurse_copy($src . '/' . $file, $des . '/' . $file);
			}
			else if ($type == 0) {
				copy($src . '/' . $file, $des . '/' . $file);
			}
			else {
				$comtent = read_static_flie_cache($src . '/' . $file, '', '', 1);
				$files = explode('.', $file);
				$files_count = count($files) - (count($files) - 1);
				$suffix_name = $files[$files_count];

				if (2 < count($files)) {
					$path = count($files) - 1;
					$name = '';

					if ($files[$path]) {
						foreach ($files[$path] as $row) {
							$name .= $row . '.';
						}

						$name = substr($name, 0, -1);
					}

					$file_path = explode('/', $name);

					if (2 < $file_path) {
						$path = count($file_path) - 1;
						$cachename = $file_path[$path];
					}
					else {
						$cachename = $file_path[0];
					}
				}
				else {
					$cachename = $files[0];
				}

				write_static_file_cache($cachename, $comtent, $suffix_name, $des . '/', 1);
			}
		}
	}

	closedir($dir);
}

function get_new_dirName($ru_id = 0, $des = '')
{
	if ($des == '') {
		$des = ROOT_PATH . 'data/seller_templates/seller_tem_' . $ru_id;
	}

	if (!is_dir($des)) {
		return 'backup_tpl_1';
	}
	else {
		$res = array();
		$dir = opendir($des);

		while (false !== ($file = readdir($dir))) {
			if ($file != '.' && $file != '..') {
				if (is_dir($des . '/' . $file)) {
					$arr = explode('_', $file);

					if ($arr[2]) {
						$res[] = $arr[2];
					}
				}
			}
		}

		closedir($dir);

		if ($res) {
			$suffix = MAX($res) + 1;
			return 'backup_tpl_' . $suffix;
		}
		else {
			return 'backup_tpl_1';
		}
	}
}

function getAlbumList($album_id = 0)
{
	$adminru = get_admin_ru_id();
	$filter['album_id'] = !empty($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : 0;
	$filter['sort_name'] = !empty($_REQUEST['sort_name']) && $_REQUEST['sort_name'] != 'undefined' ? intval($_REQUEST['sort_name']) : 2;
	$where = ' WHERE 1';

	if (0 < $album_id) {
		$filter['album_id'] = $album_id;
	}

	if (0 < $adminru['suppliers_id']) {
		$sql = 'SELECT suppliers_id FROM' . $GLOBALS['ecs']->table('gallery_album') . 'WHERE album_id = \'' . $filter['album_id'] . '\' LIMIT 1';

		if ($GLOBALS['db']->getOne($sql) != $adminru['suppliers_id']) {
			$sql = 'SELECT album_id FROM' . $GLOBALS['ecs']->table('gallery_album') . 'WHERE suppliers_id = \'' . $adminru['suppliers_id'] . '\' LIMIT 1';
			$filter['album_id'] = $GLOBALS['db']->getOne($sql);
		}
	}
	else {
		$sql = 'SELECT ru_id,suppliers_id FROM' . $GLOBALS['ecs']->table('gallery_album') . 'WHERE album_id = \'' . $filter['album_id'] . '\'  LIMIT 1';
		$album_info = $GLOBALS['db']->getRow($sql);
		if (0 < $adminru['ru_id'] || 0 < $album_info['suppliers_id']) {
			if ($album_info['ru_id'] != $adminru['ru_id'] || 0 < $album_info['suppliers_id']) {
				$sql = 'SELECT album_id FROM' . $GLOBALS['ecs']->table('gallery_album') . 'WHERE ru_id = \'' . $adminru['ru_id'] . '\' AND suppliers_id = 0  LIMIT 1';
				$filter['album_id'] = $GLOBALS['db']->getOne($sql);
			}

			if (0 < $adminru['ru_id']) {
				$where .= ' AND ru_id = \'' . $adminru['ru_id'] . '\' ';
			}
		}
	}

	if (0 < $filter['album_id']) {
		$where .= ' AND album_id = \'' . $filter['album_id'] . '\'';
	}

	if (0 < $filter['sort_name']) {
		switch ($filter['sort_name']) {
		case '1':
			$where .= ' ORDER BY add_time ASC';
			break;

		case '2':
			$where .= ' ORDER BY add_time DESC';
			break;

		case '3':
			$where .= ' ORDER BY pic_size ASC';
			break;

		case '4':
			$where .= ' ORDER BY pic_size DESC';
			break;

		case '5':
			$where .= ' ORDER BY pic_name ASC';
			break;

		case '6':
			$where .= ' ORDER BY pic_name DESC';
			break;
		}
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('pic_album') . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter, 3);
	$where .= ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('pic_album') . $where;
	$recommend_brands = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($recommend_brands as $key => $row) {
		$row['pic_file'] = get_image_path($row['pic_id'], $row['pic_file']);
		$row['pic_thumb'] = get_image_path($row['pic_id'], $row['pic_thumb']);
		$row['pic_image'] = get_image_path($row['pic_id'], $row['pic_image']);
		$arr[] = $row;
	}

	$filter['page_arr'] = seller_page($filter, $filter['page'], 14);
	return array('list' => $arr, 'filter' => $filter);
}

function getGoodslist($where = '', $sort = '', $search = '', $leftjoin = '')
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftjoin . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$where .= $sort . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
	$sql = 'SELECT g.promote_start_date, g.promote_end_date, g.promote_price, g.goods_name, g.goods_id, g.goods_thumb, g.shop_price, g.market_price, g.original_img ' . $search . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftjoin . $where;
	$goods_list = $GLOBALS['db']->getAll($sql);
	$filter['page_arr'] = seller_page($filter, $filter['page']);
	return array('list' => $goods_list, 'filter' => $filter);
}

function resetBarnd($brand_id = array(), $table = 'goods', $category = 'goods_id')
{
	if ($brand_id) {
		if ($table == 'goods') {
			$adminru = get_admin_ru_id();
			$where = ' AND is_on_sale=1 AND is_delete=0 ';

			if ($_CFG['region_store_enabled'] == 1) {
				if (0 < $adminru['rs_id']) {
					$where = get_rs_goods_where('user_id', $adminru['rs_id']);
				}
			}
			else if (0 < $adminru['ru_id']) {
				$where .= ' AND user_id = \'' . $adminru['ru_id'] . '\'';
			}

			if ($GLOBALS['_CFG']['review_goods'] == 1) {
				$where .= ' AND review_status > 2 ';
			}

			$sql = 'SELECT ' . $category . ' FROM' . $GLOBALS['ecs']->table('goods') . ('WHERE ' . $category . ' in (' . $brand_id . ')') . $where;
		}
		else if ($table == 'brand') {
			$where = ' WHERE b.brand_id in (' . $brand_id . ') AND b.is_show=1';
			$where .= ' AND be.is_recommend=1';
			$sql = 'SELECT b.brand_id FROM ' . $GLOBALS['ecs']->table('brand') . ' as b left join ' . $GLOBALS['ecs']->table('brand_extend') . ' AS be on b.brand_id=be.brand_id ' . $where;
		}
		else if ($table == 'seckill') {
			$time = gmtime();
			$where = ' WHERE 1 ';
			$where .= ' AND s.review_status = 3 AND s.is_putaway = 1 AND s.begin_time < \'' . $time . '\' AND  s.acti_time > \'' . $time . '\'';
			$where .= ' AND sg.id' . db_create_in($brand_id);
			$where .= ' AND g.is_on_sale=1 AND g.is_delete=0 ';

			if ($GLOBALS['_CFG']['review_goods'] == 1) {
				$where .= ' AND g.review_status > 2 ';
			}

			$sql = 'SELECT sg.id FROM' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('seckill_goods') . ' AS sg ON sg.goods_id=g.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('seckill') . 'AS s ON s.sec_id=sg.sec_id' . $where;
		}

		$ids = $GLOBALS['db']->getAll($sql);

		if (!empty($ids)) {
			return implode(',', arr_foreach($ids));
		}
		else {
			return '';
		}
	}
	else {
		return '';
	}
}

function strFilter($str)
{
	$str = str_replace('`', '', $str);
	$str = str_replace('·', '', $str);
	$str = str_replace('~', '', $str);
	$str = str_replace('!', '', $str);
	$str = str_replace('！', '', $str);
	$str = str_replace('@', '', $str);
	$str = str_replace('#', '', $str);
	$str = str_replace('$', '', $str);
	$str = str_replace('￥', '', $str);
	$str = str_replace('%', '', $str);
	$str = str_replace('^', '', $str);
	$str = str_replace('……', '', $str);
	$str = str_replace('&', '', $str);
	$str = str_replace('*', '', $str);
	$str = str_replace('(', '', $str);
	$str = str_replace(')', '', $str);
	$str = str_replace('（', '', $str);
	$str = str_replace('）', '', $str);
	$str = str_replace('-', '', $str);
	$str = str_replace('_', '', $str);
	$str = str_replace('——', '', $str);
	$str = str_replace('+', '', $str);
	$str = str_replace('=', '', $str);
	$str = str_replace('|', '', $str);
	$str = str_replace('\\', '', $str);
	$str = str_replace('[', '', $str);
	$str = str_replace(']', '', $str);
	$str = str_replace('【', '', $str);
	$str = str_replace('】', '', $str);
	$str = str_replace('{', '', $str);
	$str = str_replace('}', '', $str);
	$str = str_replace(';', '', $str);
	$str = str_replace('；', '', $str);
	$str = str_replace(':', '', $str);
	$str = str_replace('：', '', $str);
	$str = str_replace('\'', '', $str);
	$str = str_replace('"', '', $str);
	$str = str_replace('“', '', $str);
	$str = str_replace('”', '', $str);
	$str = str_replace(',', '', $str);
	$str = str_replace('，', '', $str);
	$str = str_replace('<', '', $str);
	$str = str_replace('>', '', $str);
	$str = str_replace('《', '', $str);
	$str = str_replace('》', '', $str);
	$str = str_replace('.', '', $str);
	$str = str_replace('。', '', $str);
	$str = str_replace('/', '', $str);
	$str = str_replace('、', '', $str);
	$str = str_replace('?', '', $str);
	$str = str_replace('？', '', $str);
	return trim($str);
}

function get_floor_style($mode = '')
{
	$arr = array();

	switch ($mode) {
	case 'homeFloor':
		$arr = array('1,2,3', '1,2,3', '2,3', '1,2,3');
		break;

	case 'homeFloorModule':
		$arr = array('1,3', '1,3', '1,3', '1,3');
		break;

	case 'homeFloorThree':
		$arr = array('2', '1,2,3', '1,3', '2,3');
		break;

	case 'homeFloorFour':
		$arr = array('2', '1', '2', '');
		break;

	case 'homeFloorFive':
		$arr = array('1,2', '1,2,3', '1,2,3', '1,2,3', '1,2,3');
		break;

	case 'homeFloorSix':
		$arr = array('1,2', '1,2', '1,2', '1');
		break;

	case 'homeFloorSeven':
		$arr = array('1,2', '1,2', '1,2', '1,2', '1,2');
		break;

	case 'homeFloorEight':
		$arr = array('1,2', '1,2', '1', '1,2', '1,2');
		break;

	case 'homeFloorNine':
		$arr = array('1,2,3', '1,2,3', '1,2,3', '1,3');
		break;

	case 'homeFloorTen':
		$arr = array('1,2', '1,2', '1,2', '1');
		break;

	case 'storeOneFloor1':
		$arr = array('1', '2,3', '2', '');
		break;

	case 'storeTwoFloor1':
		$arr = array('2', '1,2', '2');
		break;

	case 'storeThreeFloor1':
		$arr = array('2', '1,2', '2', '');
		break;

	case 'storeFourFloor1':
		$arr = array('2', '', '1,2', '');
		break;

	case 'storeFiveFloor1':
		$arr = array('', '', '2', '2');
		break;

	case 'topicOneFloor':
		$arr = array('2', '', '');
		break;

	case 'topicTwoFloor':
		$arr = array('2', '2', '', '');
		break;

	case 'topicThreeFloor':
		$arr = array('2', '2', '');
		break;

	case 'CMS_ADV':
		$arr = array('1', '1,2', '1,2');
		break;

	case 'FhomeFloorModule':
		$arr = array('1,3', '1,3');
		break;
	}

	return $arr;
}

function getAdvNum($mode = '', $floorMode = 0)
{
	$arr = array();

	switch ($mode) {
	case 'homeFloor':
		$arr1 = array('leftBanner' => '3', 'leftAdv' => '2', 'rightAdv' => '5');
		$arr2 = array('leftBanner' => '3', 'leftAdv' => '2', 'rightAdv' => '5');
		$arr3 = array('leftAdv' => '2', 'rightAdv' => '5');
		$arr4 = array('leftBanner' => '3', 'leftAdv' => '2', 'rightAdv' => '5');

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 4) {
			$arr = $arr4;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr4;
		}

		break;

	case 'homeFloorModule':
		$arr1 = array('leftBanner' => '3', 'rightAdv' => '4');
		$arr2 = array('leftBanner' => '3', 'rightAdv' => '3');
		$arr3 = array('leftBanner' => '3', 'rightAdv' => '3');
		$arr4 = array('leftBanner' => '3', 'rightAdv' => '2');

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 4) {
			$arr = $arr4;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr4;
		}

		break;

	case 'homeFloorThree':
		$arr1 = array('leftAdv' => '5');
		$arr2 = array('leftBanner' => '3', 'leftAdv' => '1', 'rightAdv' => '6');
		$arr3 = array('leftBanner' => '3', 'rightAdv' => '8');
		$arr4 = array('leftAdv' => '2', 'rightAdv' => '8');

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 4) {
			$arr = $arr4;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr4;
		}

		break;

	case 'homeFloorFour':
		$arr1 = array('leftAdv' => '2');
		$arr2 = array('leftBanner' => '3');
		$arr3 = array('leftAdv' => '2');
		$arr4 = array();

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 4) {
			$arr = $arr4;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr4;
		}

		break;

	case 'homeFloorFive':
		$arr1 = array('leftBanner' => '3', 'leftAdv' => '3');
		$arr2 = array('leftBanner' => '3', 'leftAdv' => '3', 'rightAdv' => '3');
		$arr3 = array('leftBanner' => '3', 'leftAdv' => '3', 'rightAdv' => '2');
		$arr4 = array('leftBanner' => '3', 'leftAdv' => '3', 'rightAdv' => '1');
		$arr5 = array('leftBanner' => '3', 'leftAdv' => '3', 'rightAdv' => '2');

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 4) {
			$arr = $arr4;
		}
		else if ($floorMode == 5) {
			$arr = $arr5;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr4;
			$arr[5] = $arr5;
		}

		break;

	case 'homeFloorSix':
		$arr1 = array('leftBanner' => '3', 'leftAdv' => '4');
		$arr2 = array('leftBanner' => '3', 'leftAdv' => '2');
		$arr3 = array('leftBanner' => '3', 'leftAdv' => '1');
		$arr4 = array('leftBanner' => '3');

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 4) {
			$arr = $arr4;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr4;
		}

		break;

	case 'homeFloorSeven':
		$arr1 = array('leftBanner' => '3', 'leftAdv' => '1');
		$arr2 = array('leftBanner' => '3', 'leftAdv' => '1');
		$arr3 = array('leftBanner' => '3', 'leftAdv' => '1');
		$arr4 = array('leftBanner' => '3', 'leftAdv' => '1');
		$arr5 = array('leftBanner' => '3', 'leftAdv' => '1');

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 4) {
			$arr = $arr4;
		}
		else if ($floorMode == 5) {
			$arr = $arr5;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr4;
			$arr[5] = $arr5;
		}

		break;

	case 'homeFloorEight':
		$arr1 = array('leftBanner' => '3', 'leftAdv' => '4');
		$arr2 = array('leftBanner' => '3', 'leftAdv' => '1');
		$arr3 = array('leftBanner' => '3', 'leftAdv' => '1');
		$arr4 = array('leftBanner' => '3', 'leftAdv' => '1');
		$arr5 = array('leftBanner' => '3', 'leftAdv' => '2');

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 4) {
			$arr = $arr4;
		}
		else if ($floorMode == 5) {
			$arr = $arr5;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr4;
			$arr[5] = $arr5;
		}

		break;

	case 'homeFloorNine':
		$arr1 = array('leftBanner' => '3', 'leftAdv' => '3', 'rightAdv' => '4');
		$arr2 = array('leftBanner' => '3', 'leftAdv' => '2', 'rightAdv' => '4');
		$arr3 = array('leftBanner' => '3', 'leftAdv' => '1', 'rightAdv' => '6');
		$arr4 = array('leftBanner' => '3', 'rightAdv' => '8');

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 4) {
			$arr = $arr4;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr4;
		}

		break;

	case 'homeFloorTen':
		$arr1 = array('leftBanner' => '3', 'leftAdv' => '1');
		$arr2 = array('leftBanner' => '3', 'leftAdv' => '1');
		$arr3 = array('leftBanner' => '3', 'leftAdv' => '2');
		$arr4 = array('leftBanner' => '3');

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 4) {
			$arr = $arr4;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr4;
		}

		break;

	case 'storeOneFloor1':
		$arr1 = array('leftBanner' => '3');
		$arr2 = array('leftAdv' => '3', 'rightAdv' => '4');
		$arr3 = array('leftAdv' => '1');
		$arr4 = array();

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 3) {
			$arr = $arr4;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr4;
		}

		break;

	case 'storeTwoFloor1':
		$arr1 = array('leftAdv' => '4');
		$arr2 = array('leftBanner' => '1', 'leftAdv' => '3');
		$arr3 = array('leftAdv' => '6');

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
		}

		break;

	case 'storeThreeFloor1':
		$arr1 = array('leftAdv' => '8');
		$arr2 = array('leftBanner' => '3', 'leftAdv' => '3');
		$arr3 = array('leftAdv' => '3');
		$arr4 = array();

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 4) {
			$arr = $arr4;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr4;
		}

		break;

	case 'storeFourFloor1':
		$arr1 = array('leftAdv' => '3');
		$arr2 = array();
		$arr3 = array('leftBanner' => '1', 'leftAdv' => '2');
		$arr4 = array();

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 4) {
			$arr = $arr4;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr4;
		}

		break;

	case 'storeFiveFloor1':
		$arr1 = array();
		$arr2 = array();
		$arr3 = array('leftAdv' => '6');
		$arr4 = array('leftAdv' => '9');

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 4) {
			$arr = $arr4;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr4;
		}

		break;

	case 'topicOneFloor':
		$arr1 = array('leftAdv' => '4');
		$arr2 = array();
		$arr3 = array();

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
		}

		break;

	case 'topicTwoFloor':
		$arr1 = array('leftAdv' => '5');
		$arr2 = array('leftAdv' => '1');
		$arr3 = array();
		$arr4 = array();

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else if ($floorMode == 4) {
			$arr = $arr4;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
			$arr[4] = $arr3;
		}

		break;

	case 'topicThreeFloor':
		$arr1 = array('leftAdv' => '1');
		$arr2 = array('leftAdv' => '10');
		$arr3 = array();

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else if ($floorMode == 3) {
			$arr = $arr3;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
			$arr[3] = $arr3;
		}

		break;

	case 'CMS_ADV':
		$arr1 = array('leftBanner' => '3');
		$arr2 = array('leftBanner' => '3', 'leftAdv' => '2');
		$arr3 = array('leftBanner' => '3', 'leftAdv' => '1');

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else {
			$arr = $arr3;
		}

		break;

	case 'FhomeFloorModule':
		$arr1 = array('leftBanner' => '1', 'rightAdv' => '7');
		$arr2 = array('leftBanner' => '2', 'rightAdv' => '8');

		if ($floorMode == 1) {
			$arr = $arr1;
		}
		else if ($floorMode == 2) {
			$arr = $arr2;
		}
		else {
			$arr[1] = $arr1;
			$arr[2] = $arr2;
		}

		break;
	}

	return $arr;
}

function get_down_oss_template($list, $down_path = '')
{
	$bucket_info = get_bucket_info();
	$endpoint = $bucket_info['endpoint'];
	$list = array_values($list);
	$list['object'] = isset($list[0]) ? $list[0] : array();

	if (1 < count($list)) {
		$list['prefix'] = isset($list[1]) ? $list[1] : array();
	}

	if ($list['object']) {
		foreach ($list['object'] as $key => $row) {
			$file_path = $row[0];
			$count = 1;

			if ($file_path) {
				$count = explode('.', $file_path);
				$count = count($count);
			}

			if (1 < $count) {
				$type = 'is_file';
				$is_file = 1;
			}
			else {
				$type = 'is_dir';
				$is_file = 0;
			}

			if ($is_file == 1) {
				$file = explode('/', $file_path);
				$count = count($file) - 1;
				$str = '';

				for ($i = 0; $i < $count; $i++) {
					$str .= $file[$i] . '/';
				}

				$path = ROOT_PATH . $down_path . $str;

				if (!file_exists($path)) {
					make_dir($path);
				}

				get_http_basename($endpoint . $file_path, $path);
			}

			$list[$key] = $endpoint . $row;
		}
	}

	return $list;
}

function template_mall_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter = array();
		$filter['temp_mode'] = empty($_REQUEST['temp_mode']) ? 0 : intval($_REQUEST['temp_mode']);
		$where = ' WHERE 1 ';

		if (0 < $filter['temp_mode']) {
			$temp_mode = $filter['temp_mode'];

			if ($filter['temp_mode'] == 2) {
				$temp_mode = 0;
			}

			$where .= ' AND temp_mode = \'' . $temp_mode . '\' ';
		}

		$sql = 'SELECT  COUNT(*) FROM' . $GLOBALS['ecs']->table('template_mall') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT temp_id,temp_file,temp_mode,temp_cost,temp_code,sales_volume FROM ' . $GLOBALS['ecs']->table('template_mall') . $where;
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$arr = array();

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$seller_template_info = array('screenshot' => '', 'template' => '', 'name' => '', 'uri' => '', 'desc' => '', 'version' => '', 'author' => '', 'author_uri' => '');

		if ($rows['temp_code']) {
			$seller_template_info = get_seller_template_info($rows['temp_code']);
		}

		$rows['screenshot'] = $seller_template_info['screenshot'];
		$rows['template'] = $seller_template_info['template'];
		$rows['name'] = $seller_template_info['name'];
		$rows['uri'] = $seller_template_info['uri'];
		$rows['desc'] = $seller_template_info['desc'];
		$rows['version'] = $seller_template_info['version'];
		$rows['author'] = $seller_template_info['author'];
		$rows['author_uri'] = $seller_template_info['author_uri'];
		$rows['code'] = $rows['temp_code'];
		$rows['temp_cost'] = price_format($rows['temp_cost']);

		if (0 < $rows['add_time']) {
			$rows['add_time'] = local_date('Y-m-d H:i:s', $rows['add_time']);
		}

		$arr[] = $rows;
	}

	return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function Import_temp($suffix = '', $new_suffix = '', $ru_id = 0)
{
	$dir = ROOT_PATH . 'data/seller_templates/seller_tem_' . $ru_id . '/' . $new_suffix;
	$file_html = ROOT_PATH . 'data/seller_templates/seller_tem/' . $suffix;

	if (!is_dir($dir)) {
		make_dir($dir);
	}

	return recurse_copy($file_html, $dir);
}

function get_template_apply_list()
{
	$result = get_filter();

	if ($result === false) {
		$adminru = get_admin_ru_id();
		$filter['pay_starts'] = empty($_REQUEST['pay_starts']) ? '-1' : intval($_REQUEST['pay_starts']);
		$filter['apply_sn'] = !empty($_REQUEST['apply_sn']) ? trim($_REQUEST['apply_sn']) : '-1';
		$where = ' WHERE 1 ';

		if (0 < $adminru['ru_id']) {
			$where .= ' AND ru_id = \'' . $adminru['ru_id'] . '\'';
		}

		if ($filter['pay_starts'] != -1) {
			if ($filter['pay_starts'] == 2) {
				$filter['pay_starts'] = 0;
			}

			$where .= ' AND pay_status = \'' . $filter['pay_starts'] . '\'';
		}

		if ($filter['apply_sn'] != -1) {
			$where .= ' AND apply_sn = \'' . $filter['apply_sn'] . '\'';
		}

		$filter = array();
		$sql = 'SELECT  COUNT(*) FROM' . $GLOBALS['ecs']->table('seller_template_apply') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT apply_id,ru_id,temp_id,temp_code,pay_status,apply_status,total_amount,pay_fee,add_time,pay_time,pay_id,apply_sn FROM ' . $GLOBALS['ecs']->table('seller_template_apply') . $where . ' ORDER BY add_time DESC';
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$arr = array();

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$seller_template_info = array('name' => '');

		if ($rows['temp_code']) {
			$seller_template_info = get_seller_template_info($rows['temp_code']);
		}

		$rows['name'] = $seller_template_info['name'];
		$rows['total_amount'] = price_format($rows['total_amount']);
		$rows['pay_fee'] = price_format($rows['pay_fee']);

		if (0 < $rows['add_time']) {
			$rows['add_time'] = local_date('Y-m-d H:i:s', $rows['add_time']);
		}

		if (0 < $rows['pay_time']) {
			$rows['pay_time'] = local_date('Y-m-d H:i:s', $rows['pay_time']);
		}

		$sql = 'SELECT pay_name FROM' . $GLOBALS['ecs']->table('payment') . 'WHERE pay_id = \'' . $rows['pay_id'] . '\'';
		$rows['pay_name'] = $GLOBALS['db']->getOne($sql);
		$rows['shop_name'] = get_shop_name($rows['ru_id'], 1);
		$arr[] = $rows;
	}

	return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_down_hometemplates($suffix = '')
{
	if ($GLOBALS['_CFG']['open_oss'] && $GLOBALS['_CFG']['server_model'] && !empty($suffix)) {
		if (!file_exists(ROOT_PATH . 'data/sc_file/hometemplates/' . $suffix . '.php')) {
			$oss_list = get_oss_list_file(array('prefix' => 'data/home_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $suffix . '/'));
			$oss_list = json_decode($oss_list, true);
			get_down_oss_template($oss_list['list']);
			$oss_bonusadv_list = get_oss_list_file(array('prefix' => 'data/home_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $suffix . '/images/bonusadv/'));
			$oss_bonusadv_list = json_decode($oss_bonusadv_list, true);
			get_down_oss_template($oss_bonusadv_list['list']);
			$oss_content_list = get_oss_list_file(array('prefix' => 'data/home_Templates/' . $GLOBALS['_CFG']['template'] . '/' . $suffix . '/images/content/'));
			$oss_content_list = json_decode($oss_content_list, true);
			get_down_oss_template($oss_content_list['list']);
			write_static_cache($suffix, array(1), 'data/sc_file/hometemplates/');
		}
	}
}

function get_down_topictemplates($topic_id = 0, $seller_id = 0)
{
	if ($GLOBALS['_CFG']['open_oss'] && $GLOBALS['_CFG']['server_model']) {
		if (!file_exists(ROOT_PATH . 'data/sc_file/topic/topic_' . $seller_id . '/' . 'topic_' . $topic_id . '.php')) {
			$oss_list = get_oss_list_file(array('prefix' => 'data/topic/topic_' . $seller_id . '/' . 'topic_' . $topic_id . '/'));
			$oss_list = json_decode($oss_list, true);
			get_down_oss_template($oss_list['list']);
			$oss_images_list = get_oss_list_file(array('prefix' => 'data/topic/topic_' . $seller_id . '/' . 'topic_' . $topic_id . '/images/'));
			$oss_images_list = json_decode($oss_images_list, true);
			get_down_oss_template($oss_images_list['list']);
			$oss_content_list = get_oss_list_file(array('prefix' => 'data/topic/topic_' . $seller_id . '/' . 'topic_' . $topic_id . '/images/content/'));
			$oss_content_list = json_decode($oss_content_list, true);
			get_down_oss_template($oss_content_list['list']);
			write_static_cache('topic_' . $topic_id, array(1), 'data/sc_file/topic/topic_' . $seller_id . '/');
		}
	}
}

function get_down_sellertemplates($merchant_id = 0, $tem = '')
{
	if ($GLOBALS['_CFG']['open_oss'] && $GLOBALS['_CFG']['server_model'] && !empty($tem)) {
		if (!file_exists(ROOT_PATH . 'data/sc_file/sellertemplates/seller_tem_' . $merchant_id . '/' . $tem . '.php')) {
			$oss_list = get_oss_list_file(array('prefix' => 'data/seller_templates/seller_tem_' . $merchant_id . '/' . $tem . '/'));
			$oss_list = json_decode($oss_list, true);
			get_down_oss_template($oss_list['list']);
			$oss_css_list = get_oss_list_file(array('prefix' => 'data/seller_templates/seller_tem_' . $merchant_id . '/' . $tem . '/css/'));
			$oss_css_list = json_decode($oss_css_list, true);
			get_down_oss_template($oss_css_list['list']);
			$oss_images_list = get_oss_list_file(array('prefix' => 'data/seller_templates/seller_tem_' . $merchant_id . '/' . $tem . '/images/'));
			$oss_images_list = json_decode($oss_images_list, true);
			get_down_oss_template($oss_images_list['list']);
			$oss_head_list = get_oss_list_file(array('prefix' => 'data/seller_templates/seller_tem_' . $merchant_id . '/' . $tem . '/images/head/'));
			$oss_head_list = json_decode($oss_head_list, true);
			get_down_oss_template($oss_head_list['list']);
			$oss_content_list = get_oss_list_file(array('prefix' => 'data/seller_templates/seller_tem_' . $merchant_id . '/' . $tem . '/images/content/'));
			$oss_content_list = json_decode($oss_content_list, true);
			get_down_oss_template($oss_content_list['list']);
			write_static_cache($tem, array(1), 'data/sc_file/sellertemplates/seller_tem_' . $merchant_id . '/');
		}
	}
}

function getcat_atr($cat_id = 0, $where = '')
{
	$condition = get_article_children($cat_id);
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('article') . ' AS a WHERE a.is_open = 1 AND article_type=0  AND a.' . $condition . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$limit = ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
	$sql = 'SELECT a.article_id,a.title ' . ' FROM ' . $GLOBALS['ecs']->table('article') . ' AS a ' . ' WHERE a.is_open = 1 AND a.' . $condition . $where . ' ORDER BY a.article_type DESC, a.sort_order ASC ' . $limit;
	$article_list = $GLOBALS['db']->getAll($sql);
	$filter['page_arr'] = seller_page($filter, $filter['page']);
	return array('list' => $article_list, 'filter' => $filter);
}

function get_home_templates()
{
	$adminru = get_admin_ru_id();
	$result = get_filter();

	if ($result === false) {
		$filter = array();
		$where = ' WHERE 1 ';

		if (0 < $adminru['rs_id']) {
			$where .= ' AND rs_id = \'' . $adminru['rs_id'] . '\'';
		}

		$where .= ' AND theme = \'' . $GLOBALS['_CFG']['template'] . '\'';
		$sql = 'SELECT  COUNT(*) FROM' . $GLOBALS['ecs']->table('home_templates') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT temp_id,rs_id,code,is_enable FROM ' . $GLOBALS['ecs']->table('home_templates') . $where;
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$default_tem = '';
	$arr = array();

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$seller_template_info = array('screenshot' => '', 'template' => '', 'name' => '', 'uri' => '', 'desc' => '', 'version' => '', 'author' => '', 'author_uri' => '');

		if ($rows['code']) {
			$seller_template_info = get_seller_template_info($rows['code'], 0, $GLOBALS['_CFG']['template']);
		}

		$rows['screenshot'] = $seller_template_info['screenshot'];
		$rows['template'] = $seller_template_info['template'];
		$rows['name'] = $seller_template_info['name'];
		$rows['uri'] = $seller_template_info['uri'];
		$rows['desc'] = $seller_template_info['desc'];
		$rows['version'] = $seller_template_info['version'];
		$rows['author'] = $seller_template_info['author'];
		$rows['author_uri'] = $seller_template_info['author_uri'];
		$rows['code'] = $rows['code'];
		if ($rows['is_enable'] == 1 && $rows['rs_id'] == $adminru['rs_id']) {
			$default_tem = $rows['code'];
		}

		$rows['rs_name'] = $GLOBALS['db']->getOne('SELECT rs_name FROM ' . $GLOBALS['ecs']->table('region_store') . 'WHERE rs_id = \'' . $rows['rs_id'] . '\'');
		$arr[] = $rows;
	}

	return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count'], 'default_tem' => $default_tem);
}


?>
