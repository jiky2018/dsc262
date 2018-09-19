<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
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

function get_win_goods($id)
{
	$adminru = get_admin_ru_id();
	$sql = 'select id,win_goods from ' . $GLOBALS['ecs']->table('seller_shopwindow') . (' where id=\'' . $id . '\' and ru_id=\'') . $adminru['ru_id'] . '\'';
	$win_info = $GLOBALS['db']->getRow($sql);

	if (0 < $win_info['id']) {
		$goods_ids = $win_info['win_goods'];
		$goods = array();

		if ($goods_ids) {
			$sql = 'select goods_id,goods_name from ' . $GLOBALS['ecs']->table('goods') . ' where user_id=\'' . $adminru['ru_id'] . ('\' and goods_id in (' . $goods_ids . ')');
			$goods = $GLOBALS['db']->getAll($sql);
		}

		$opt = array();

		foreach ($goods as $val) {
			$opt[] = array('value' => $val['goods_id'], 'text' => $val['goods_name'], 'data' => '');
		}

		return $opt;
	}
	else {
		return 'no_cc';
	}
}

function get_attributes($cat_id = 0)
{
	$sql = 'SELECT `attr_id`, `cat_id`, `attr_name` FROM ' . $GLOBALS['ecs']->table('attribute') . ' ';

	if (!empty($cat_id)) {
		$cat_id = intval($cat_id);
		$sql .= ' WHERE `cat_id` = \'' . $cat_id . '\' ';
	}

	$sql .= ' ORDER BY `cat_id` ASC, `attr_id` ASC ';
	$attributes = array();
	$query = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($query)) {
		$attributes[$row['attr_id']] = $row['attr_name'];
	}

	return $attributes;
}

function set_show_in_nav($type, $id, $val)
{
	if ($type == 'c') {
		$tablename = $GLOBALS['ecs']->table('category');
	}
	else {
		$tablename = $GLOBALS['ecs']->table('article_cat');
	}

	$GLOBALS['db']->query('UPDATE ' . $tablename . ' SET show_in_nav = \'' . $val . '\' WHERE cat_id = \'' . $id . '\'');
	clear_cache_files();
}

function get_brandlist($filters)
{
	$adminru = get_admin_ru_id();
	$cat_id = !empty($filters->cat_id) ? intval($filters->cat_id) : 0;
	$keyword = !empty($filters->keyword) ? trim($filters->keyword) : '';
	$brand_id = !empty($filters->brand_id) ? intval($filters->brand_id) : 0;
	$children = cat_list($cat_id, 1);
	$children = arr_foreach($children);

	if ($children) {
		$children = implode(',', $children) . ',' . $cat_id;
		$children = get_children($children, 0, 1);
	}
	else {
		$children = 'g.cat_id IN (' . $cat_id . ')';
	}

	$where = '1';
	$where .= ' AND g.user_id = \'' . $adminru['ru_id'] . '\' ';

	if (!empty($keyword)) {
		if (strtoupper(EC_CHARSET) == 'GBK') {
			$keyword = iconv('UTF-8', 'gb2312', $keyword);
		}

		$where .= ' AND brand_name like \'%' . $keyword . '%\'';
	}

	if (!empty($brand_id)) {
		$where .= ' AND b.brand_id = \'' . $brand_id . '\' ';
	}
	else {
		$cat_keys = get_array_keys_cat($cat_id);
		$where .= ' AND ' . $children . ' OR ' . 'gc.cat_id ' . db_create_in(array_unique(array_merge(array($cat_id), $cat_keys)));
	}

	$sql = 'SELECT b.brand_id, b.brand_name,b.brand_logo, COUNT(*) AS goods_num ' . 'FROM ' . $GLOBALS['ecs']->table('brand') . 'AS b ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.brand_id = b.brand_id AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_cat') . ' AS gc ON g.goods_id = gc.goods_id ' . (' WHERE ' . $where . ' AND b.is_show = 1 ') . 'GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY b.sort_order, b.brand_id ASC';
	$brands_list = $GLOBALS['db']->getAll($sql);
	$brands = array();

	foreach ($brands_list as $key => $val) {
		$brands[$key]['brand_id'] = $val['brand_id'];
		$brands[$key]['brand_name'] = $val['brand_name'];
		$brands[$key]['brand_logo'] = $val['brand_logo'];
	}

	return $brands;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . '/' . SELLER_PATH . '/includes/lib_goods.php';
require ROOT_PATH . '/includes/lib_visual.php';
require ROOT_PATH . '/includes/cls_json.php';
$_REQUEST['act'] = trim($_REQUEST['act']);
$data = array('error' => 0, 'message' => '', 'content' => '');
$smarty->assign('menus', $_SESSION['menus']);
$admin_id = get_admin_id();
$adminru = get_admin_ru_id();

if ($_REQUEST['act'] == 'get_select_category') {
	$cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
	$child_cat_id = empty($_REQUEST['child_cat_id']) ? 0 : intval($_REQUEST['child_cat_id']);
	$cat_level = empty($_REQUEST['cat_level']) ? 0 : intval($_REQUEST['cat_level']);
	$select_jsId = empty($_REQUEST['select_jsId']) ? 'cat_parent_id' : trim($_REQUEST['select_jsId']);
	$type = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);
	$table = empty($_REQUEST['table']) ? 0 : intval($_REQUEST['table']);

	if ($table == 1) {
		$content = insert_seller_select_category($cat_id, $child_cat_id, $cat_level, $select_jsId, $type, 'merchants_category', array(), $adminru['ru_id']);
	}
	else {
		$content = insert_select_category($cat_id, $child_cat_id, $cat_level, $select_jsId, $type);
	}

	if (!empty($content)) {
		$data['error'] = 1;
		$data['content'] = $content;
	}

	exit(json_encode($data));
}
else if ($_REQUEST['act'] == 'filter_category') {
	$cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
	$cat_type_show = empty($_REQUEST['cat_type_show']) ? 0 : intval($_REQUEST['cat_type_show']);
	$user_id = $adminru['ru_id'];
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$table = isset($_REQUEST['table']) && $_REQUEST['table'] != 'undefined' ? trim($_REQUEST['table']) : 'category';

	if ($table == 'wholesale_cat') {
		$user_id = 0;
	}

	if ($cat_type_show == 1) {
		$parent_cat_list = get_seller_select_category($cat_id, 1, true, $user_id, $table);
		$filter_category_navigation = get_seller_array_category_info($parent_cat_list, $table);
	}
	else {
		$parent_cat_list = get_select_category($cat_id, 1, true, 0, $table);
		$filter_category_navigation = get_array_category_info($parent_cat_list, $table);
	}

	$cat_nav = '';

	if ($filter_category_navigation) {
		foreach ($filter_category_navigation as $key => $val) {
			if ($key == 0) {
				$cat_nav .= $val['cat_name'];
			}
			else if (0 < $key) {
				$cat_nav .= ' > ' . $val['cat_name'];
			}
		}
	}
	else {
		$cat_nav = '请选择分类';
	}

	$result['cat_nav'] = $cat_nav;
	$cat_level = count($parent_cat_list);

	if ($cat_type_show == 1) {
		if ($cat_level <= 3) {
			$filter_category_list = get_seller_category_list($cat_id, 2, $user_id);
		}
		else {
			$filter_category_list = get_seller_category_list($cat_id, 0, $user_id);
			$cat_level -= 1;
		}
	}
	else {
		$seller_shop_cat = seller_shop_cat($user_id);

		if ($cat_level <= 3) {
			$filter_category_list = get_category_list($cat_id, 2, $seller_shop_cat, $user_id, $cat_level, $table);
		}
		else {
			$filter_category_list = get_category_list($cat_id, 0, array(), $user_id, 0, $table);
			$cat_level -= 1;
		}
	}

	$smarty->assign('user_id', $user_id);

	if ($user_id) {
		$smarty->assign('seller_cat_type_show', $cat_type_show);
		$smarty->assign('seller_filter_category_navigation', $filter_category_navigation);
		$smarty->assign('seller_filter_category_list', $filter_category_list);
	}
	else {
		$smarty->assign('cat_type_show', $cat_type_show);
	}

	$smarty->assign('filter_category_level', $cat_level);
	$smarty->assign('table', $table);
	$smarty->assign('filter_category_navigation', $filter_category_navigation);
	$smarty->assign('filter_category_list', $filter_category_list);

	if ($cat_type_show) {
		if (empty($filter_category_list)) {
			$result['type'] = 1;
		}

		$result['content'] = $smarty->fetch('library/filter_category_seller.lbi');
	}
	else {
		$result['content'] = $smarty->fetch('library/filter_category.lbi');
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'get_albun_pic') {
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$is_vis = !empty($_REQUEST['is_vis']) ? intval($_REQUEST['is_vis']) : 0;
	$inid = !empty($_REQUEST['inid']) ? trim($_REQUEST['inid']) : 0;
	$pic_list = getAlbumList();
	$smarty->assign('pic_list', $pic_list['list']);
	$smarty->assign('filter', $pic_list['filter']);
	$smarty->assign('temp', 'ajaxPiclist');
	$smarty->assign('is_vis', $is_vis);
	$smarty->assign('inid', $inid);
	$result['content'] = $GLOBALS['smarty']->fetch('library/dialog.lbi');
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'addmodule') {
	$json = new JSON();
	$result = array('error' => 0, 'message' => '', 'content' => '', 'mode' => '');
	$result['spec_attr'] = !empty($_REQUEST['spec_attr']) ? stripslashes($_REQUEST['spec_attr']) : '';

	if ($_REQUEST['spec_attr']) {
		$_REQUEST['spec_attr'] = strip_tags(urldecode($_REQUEST['spec_attr']));
		$_REQUEST['spec_attr'] = json_str_iconv($_REQUEST['spec_attr']);

		if (!empty($_REQUEST['spec_attr'])) {
			$spec_attr = $json->decode($_REQUEST['spec_attr']);
			$spec_attr = object_to_array($spec_attr);
		}
	}

	$pic_src = isset($spec_attr['pic_src']) ? $spec_attr['pic_src'] : array();
	$bg_color = isset($spec_attr['bg_color']) ? $spec_attr['bg_color'] : array();
	$link = isset($spec_attr['link']) && $spec_attr['link'] != ',' ? explode(',', $spec_attr['link']) : array();
	$sort = isset($spec_attr['sort']) ? $spec_attr['sort'] : array();
	$result['mode'] = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
	$is_li = isset($spec_attr['is_li']) ? intval($spec_attr['is_li']) : 0;
	$result['slide_type'] = isset($spec_attr['slide_type']) ? addslashes($spec_attr['slide_type']) : '';
	$result['itemsLayout'] = isset($spec_attr['itemsLayout']) ? addslashes($spec_attr['itemsLayout']) : '';
	$result['diff'] = isset($_REQUEST['diff']) ? intval($_REQUEST['diff']) : 0;
	$count = COUNT($pic_src);
	$arr = array();
	$sort_vals = array();

	for ($i = 0; $i < $count; $i++) {
		$arr[$i]['pic_src'] = $pic_src[$i];

		if ($link[$i]) {
			$arr[$i]['link'] = setRewrite($link[$i]);
		}
		else {
			$arr[$i]['link'] = $link[$i];
		}

		$arr[$i]['bg_color'] = $bg_color[$i];
		$arr[$i]['sort'] = isset($sort[$i]) ? $sort[$i] : 0;
		$sort_vals[$i] = isset($sort[$i]) ? $sort[$i] : 0;
	}

	if (!empty($arr)) {
		array_multisort($sort_vals, SORT_ASC, $arr);
		$smarty->assign('img_list', $arr);
	}

	$smarty->assign('is_li', $is_li);
	$smarty->assign('temp', 'img_list');
	$smarty->assign('attr', $spec_attr);
	$smarty->assign('mode', $result['mode']);
	$result['content'] = $GLOBALS['smarty']->fetch('library/dialog.lbi');
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'changedgoods') {
	require ROOT_PATH . '/includes/lib_goods.php';
	$json = new JSON();
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$spec_attr = array();
	$search_type = isset($_REQUEST['search_type']) ? trim($_REQUEST['search_type']) : '';
	$result['spec_attr'] = !empty($_REQUEST['spec_attr']) ? stripslashes($_REQUEST['spec_attr']) : '';

	if ($_REQUEST['spec_attr']) {
		$_REQUEST['spec_attr'] = strip_tags(urldecode($_REQUEST['spec_attr']));
		$_REQUEST['spec_attr'] = json_str_iconv($_REQUEST['spec_attr']);

		if (!empty($_REQUEST['spec_attr'])) {
			$spec_attr = $json->decode($_REQUEST['spec_attr']);
			$spec_attr = object_to_array($spec_attr);
		}
	}

	$sort_order = isset($_REQUEST['sort_order']) ? $_REQUEST['sort_order'] : 0;
	$cat_id = isset($_REQUEST['cat_id']) ? explode('_', $_REQUEST['cat_id']) : array();
	$brand_id = isset($_REQUEST['brand_id']) ? intval($_REQUEST['brand_id']) : 0;
	$keyword = isset($_REQUEST['keyword']) ? addslashes($_REQUEST['keyword']) : '';
	$goodsAttr = isset($spec_attr['goods_ids']) ? explode(',', $spec_attr['goods_ids']) : '';
	$goods_ids = isset($_REQUEST['goods_ids']) ? explode(',', $_REQUEST['goods_ids']) : '';
	$result['goods_ids'] = !empty($goodsAttr) ? $goodsAttr : $goods_ids;
	$spec_attr['goods_ids'] = resetBarnd($spec_attr['goods_ids']);
	$result['cat_desc'] = isset($spec_attr['cat_desc']) ? addslashes($spec_attr['cat_desc']) : '';
	$result['cat_name'] = isset($spec_attr['cat_name']) ? addslashes($spec_attr['cat_name']) : '';
	$result['align'] = isset($spec_attr['align']) ? addslashes($spec_attr['align']) : '';
	$result['is_title'] = isset($spec_attr['is_title']) ? intval($spec_attr['is_title']) : 0;
	$result['itemsLayout'] = isset($spec_attr['itemsLayout']) ? addslashes($spec_attr['itemsLayout']) : '';
	$result['diff'] = isset($_REQUEST['diff']) ? intval($_REQUEST['diff']) : 0;
	$type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
	$temp = isset($_REQUEST['temp']) ? $_REQUEST['temp'] : 'goods_list';
	$result['mode'] = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
	$resetRrl = isset($_REQUEST['resetRrl']) ? intval($_REQUEST['resetRrl']) : 0;
	$smarty->assign('temp', $temp);
	$where = 'WHERE g.is_delete=0 AND g.user_id = \'' . $adminru['ru_id'] . '\'';

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$where .= ' AND g.review_status > 2 ';
	}

	if ($search_type != 'goods') {
		$where .= ' AND g.is_on_sale = 1 ';
	}

	if ($cat_id) {
		$where .= ' AND ' . get_children($cat_id[0]);
	}

	if (0 < $brand_id) {
		$where .= ' AND g.brand_id = \'' . $brand_id . '\'';
	}

	if ($keyword) {
		$where .= ' AND g.goods_name  LIKE \'%' . $keyword . '%\'';
	}

	if ($result['goods_ids'] && $type == '0') {
		$where .= ' AND g.goods_id' . db_create_in($result['goods_ids']);
	}

	$sort = ' ORDER BY g.sort_order, g.goods_id DESC';

	switch ($sort_order) {
	case '1':
		$sort = ' ORDER BY g.add_time ASC';
		break;

	case '2':
		$sort = ' ORDER BY g.add_time DESC';
		break;

	case '3':
		$sort = ' ORDER BY g.sort_order ASC';
		break;

	case '4':
		$sort = ' ORDER BY g.sort_order DESC';
		break;

	case '5':
		$sort = ' ORDER BY g.goods_name ASC';
		break;

	case '6':
		$sort = ' ORDER BY g.goods_name DESC';
		break;
	}

	if ($type == 1) {
		$list = getGoodslist($where, $sort);
		$goods_list = $list['list'];
		$filter = $list['filter'];
		$filter['cat_id'] = $cat_id[0];
		$filter['sort_order'] = $sort_order;
		$filter['keyword'] = $keyword;
		$filter['search_type'] = $search_type;
		$smarty->assign('filter', $filter);
	}
	else {
		$sql = 'SELECT g.promote_start_date, g.promote_end_date, g.promote_price, g.goods_name, g.goods_id, g.goods_thumb, g.shop_price, g.market_price, g.original_img FROM ' . $ecs->table('goods') . ' AS g ' . $where . $sort;
		$goods_list = $db->getAll($sql);
	}

	if (!empty($goods_list)) {
		foreach ($goods_list as $k => $v) {
			$goods_list[$k]['goods_thumb'] = get_image_path($v['goods_id'], $v['goods_thumb']);
			$goods_list[$k]['original_img'] = get_image_path($v['goods_id'], $v['original_img']);
			$goods_list[$k]['url'] = build_uri('goods', array('gid' => $v['goods_id']), $v['goods_name']);
			$goods_list[$k]['shop_price'] = price_format($v['shop_price']);

			if (0 < $v['promote_price']) {
				$goods_list[$k]['promote_price'] = bargain_price($v['promote_price'], $v['promote_start_date'], $v['promote_end_date']);
			}
			else {
				$goods_list[$k]['promote_price'] = 0;
			}

			if (0 < $v['goods_id'] && in_array($v['goods_id'], $result['goods_ids']) && !empty($result['goods_ids'])) {
				$goods_list[$k]['is_selected'] = 1;
			}
		}
	}

	$smarty->assign('is_title', $result['is_title']);
	$smarty->assign('goods_count', count($goods_list));
	$smarty->assign('goods_list', $goods_list);
	$smarty->assign('attr', $spec_attr);
	$result['goods_ids'] = implode(',', $result['goods_ids']);
	$result['content'] = $GLOBALS['smarty']->fetch('library/dialog.lbi');
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'navigator') {
	$json = new JSON();
	$attr = array();
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$result['mode'] = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
	$result['diff'] = isset($_REQUEST['diff']) ? intval($_REQUEST['diff']) : 0;
	$result['spec_attr'] = !empty($_REQUEST['spec_attr']) ? stripslashes($_REQUEST['spec_attr']) : '';
	$_REQUEST['spec_attr'] = strip_tags(urldecode($_REQUEST['spec_attr']));
	$_REQUEST['spec_attr'] = json_str_iconv($_REQUEST['spec_attr']);

	if (!empty($_REQUEST['spec_attr'])) {
		$spec_attr = $json->decode($_REQUEST['spec_attr']);
		$spec_attr = object_to_array($spec_attr);
	}

	$result['navColor'] = $spec_attr['navColor'];
	$where = ' where ru_id = ' . $adminru['ru_id'] . ' AND ifshow = 1 ';
	$sql = 'SELECT name, url ' . ' FROM ' . $GLOBALS['ecs']->table('merchants_nav') . $where . ' ORDER by vieworder';
	$navigator = $db->getAll($sql);

	foreach ($navigator as $k => $v) {
		if ($v['url']) {
			$navigator[$k]['url'] = setRewrite($v['url']);
		}
	}

	$smarty->assign('navigator', $navigator);
	$index_url = '#';
	$seller_domain = get_seller_domain_info($adminru['ru_id']);
	if ($seller_domain && $seller_domain['is_enable'] == 1) {
		$index_url = $seller_domain['domain_name'];
	}
	else {
		$index_url = 'merchants_store.php?merchant_id=' + $adminru['ru_id'];
	}

	$smarty->assign('index_url', $index_url);
	$smarty->assign('temp', 'navigator');
	$smarty->assign('attr', $spec_attr);
	$result['content'] = $GLOBALS['smarty']->fetch('library/dialog.lbi');
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'album_move_back') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('content' => '', 'pic_id' => '');
	$pic_id = isset($_REQUEST['pic_id']) ? intval($_REQUEST['pic_id']) : 0;
	$album_id = isset($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : 0;
	$sql = 'UPDATE' . $ecs->table('pic_album') . ' SET album_id = \'' . $album_id . '\' WHERE pic_id = \'' . $pic_id . '\' AND ru_id = \'' . $adminru['ru_id'] . '\'';
	$db->query($sql);
	$result['pic_id'] = $pic_id;
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'add_albun_pic') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'pic_id' => '', 'content' => '');
	require_once ROOT_PATH . 'includes/cls_image.php';
	$exc = new exchange($ecs->table('gallery_album'), $db, 'album_id', 'album_mame');
	$allow_file_types = '|GIF|JPG|PNG|';
	$album_mame = isset($_REQUEST['album_mame']) ? addslashes($_REQUEST['album_mame']) : '';
	$album_desc = isset($_REQUEST['album_desc']) ? addslashes($_REQUEST['album_desc']) : '';
	$sort_order = isset($_REQUEST['sort_order']) ? intval($_REQUEST['sort_order']) : 50;
	$is_only = $exc->is_only('album_mame', $album_mame, 0, 'ru_id = ' . $adminru['ru_id']);

	if (!$is_only) {
		$result['error'] = 0;
		$result['content'] = '相册’' . $album_mame . '‘存在';
		exit(json_encode($result));
	}

	$file_url = '';
	if (isset($_FILES['album_cover']['error']) && $_FILES['album_cover']['error'] == 0 || !isset($_FILES['album_cover']['error']) && isset($_FILES['album_cover']['tmp_name']) && $_FILES['album_cover']['tmp_name'] != 'none') {
		if (!check_file_type($_FILES['album_cover']['tmp_name'], $_FILES['album_cover']['name'], $allow_file_types)) {
			$result['error'] = 0;
			$result['content'] = '相册封面格式必须为|GIF|JPG|PNG|格式。请重新上传';
			exit(json_encode($result));
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
	$sql = 'INSERT INTO' . $ecs->table('gallery_album') . '(`album_mame`,`album_cover`,`album_desc`,`sort_order`,`add_time`,`ru_id`)' . (' VALUES (\'' . $album_mame . '\',\'' . $file_url . '\',\'' . $album_desc . '\',\'' . $sort_order . '\',\'' . $time . '\',\'') . $adminru['ru_id'] . '\')';
	$db->query($sql);
	$result['error'] = 1;
	$result['pic_id'] = $db->insert_id();
	$album_list = get_goods_gallery_album(1, $adminru['ru_id'], array('album_id', 'album_mame'), 'ru_id');
	$html = '<li><a href="javascript:;" data-value="0" class="ftx-01">请选择...</a></li>';

	if (!empty($album_list)) {
		foreach ($album_list as $v) {
			$html .= '<li><a href="javascript:;" data-value="' . $v['album_id'] . '" class="ftx-01">' . $v['album_mame'] . '</a></li>';
		}
	}

	$result['content'] = $html;
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'search_brand_list') {
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
	$result = array('error' => 0, 'message' => '', 'content' => '');
	if (isset($_REQUEST['ru_id']) && trim($_REQUEST['ru_id']) != 'undefined') {
		$smarty->assign('filter_brand_list', search_brand_list(0, intval($_REQUEST['ru_id'])));
	}
	else {
		$smarty->assign('filter_brand_list', search_brand_list($goods_id));
	}

	$result['content'] = $smarty->fetch('library/search_brand_list.lbi');
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'filter_list') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$search_type = empty($_REQUEST['search_type']) ? '' : trim($_REQUEST['search_type']);
	$result = array('error' => 0, 'message' => '', 'content' => '');

	if ($search_type == 'goods') {
		$cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
		$brand_id = empty($_REQUEST['brand_id']) ? 0 : intval($_REQUEST['brand_id']);
		$keyword = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		$limit['start'] = empty($_REQUEST['limit_start']) ? 0 : trim($_REQUEST['limit_start']);
		$limit['number'] = empty($_REQUEST['limit_number']) ? 50 : trim($_REQUEST['limit_number']);
		$filters['cat_id'] = $cat_id;
		$filters['brand_id'] = $brand_id;
		$filters['keyword'] = urlencode($keyword);
		$filters['sel_mode'] = 0;
		$filters['brand_keyword'] = '';
		$filters['exclude'] = '';
		$filters = $json->decode(urldecode(json_encode($filters)));
		$arr = get_goods_list($filters, $limit);
		$opt = array();

		foreach ($arr as $key => $val) {
			$opt[] = array('value' => $val['goods_id'], 'text' => $val['goods_name'], 'data' => $val['shop_price']);
		}

		$filter_list = $opt;
	}
	else if ($search_type == 'article') {
		$title = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		$where = ' WHERE cat_id > 0 ';

		if (!empty($title)) {
			$keyword = trim($filters['title']);
			$where .= ' AND title LIKE \'%' . mysql_like_quote($title) . '%\' ';
		}

		$sql = 'SELECT article_id, title FROM ' . $ecs->table('article') . $where . 'ORDER BY article_id DESC LIMIT 50';
		$res = $db->query($sql);
		$arr = array();

		while ($row = $db->fetchRow($res)) {
			$arr[] = array('value' => $row['article_id'], 'text' => $row['title'], 'data' => '');
		}

		$filter_list = $arr;
	}
	else if ($search_type == 'area') {
		$ra_id = empty($_REQUEST['keyword']) ? 0 : intval($_REQUEST['keyword']);
		$arr = get_areaRegion_info_list($ra_id);
		$opt = array();

		foreach ($arr as $key => $val) {
			$opt[] = array('value' => $val['region_id'], 'text' => $val['region_name'], 'data' => 0);
		}

		$filter_list = $opt;
	}
	else if ($search_type == 'goods_type') {
		$cat_id = empty($_REQUEST['keyword']) ? 0 : intval($_REQUEST['keyword']);
		$goods_fields = my_array_merge($_LANG['custom'], get_attributes($cat_id));
		$opt = array();

		foreach ($goods_fields as $key => $val) {
			$opt[] = array('value' => $key, 'text' => $val, 'data' => 0);
		}

		$filter_list = $opt;
	}
	else if ($search_type == 'get_content') {
		$cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
		$brand_id = empty($_REQUEST['brand_id']) ? 0 : intval($_REQUEST['brand_id']);
		$keyword = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		$FloorBrand = empty($_REQUEST['FloorBrand']) ? 0 : intval($_REQUEST['FloorBrand']);
		$brand_ids = empty($_REQUEST['brand_ids']) ? '' : trim($_REQUEST['brand_ids']);
		$is_selected = empty($_REQUEST['is_selected']) ? 0 : intval($_REQUEST['is_selected']);
		$filters['cat_id'] = $cat_id;
		$filters['brand_id'] = $brand_id;
		$filters['keyword'] = $keyword;
		$filters = $json->decode(json_encode($filters));
		$arr = get_brandlist($filters);
		$opt = array();

		if ($FloorBrand == 1) {
			if (!empty($arr)) {
				$brand_ids = explode(',', $brand_ids);

				foreach ($arr as $key => $val) {
					$val['brand_logo'] = DATA_DIR . '/brandlogo/' . $val['brand_logo'];
					$arr[$key]['brand_logo'] = get_image_path($val['brand_id'], $val['brand_logo']);
					if (!empty($brand_ids) && in_array($val['brand_id'], $brand_ids)) {
						$arr[$key]['selected'] = 1;
					}
					else if ($is_selected == 1) {
						unset($arr[$key]);
					}
				}
			}

			$smarty->assign('recommend_brands', $arr);
			$smarty->assign('temp', 'brand_query');
			$result['FloorBrand'] = $smarty->fetch('templates/library/dialog.lbi');
		}
		else {
			foreach ($arr as $key => $val) {
				$opt[] = array('value' => $val['brand_id'], 'text' => $val['brand_name'], 'data' => $val['brand_id']);
			}
		}

		$filter_list = $opt;
	}

	$smarty->assign('filter_list', $filter_list);
	$result['content'] = $smarty->fetch('library/move_left.lbi');
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'add_win_goods') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$linked_array = empty($_REQUEST['value']) ? array() : explode(',', $_REQUEST['value']);
	$id = empty($_REQUEST['win_id']) ? 0 : intval($_REQUEST['win_id']);
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$sql = 'select win_goods from ' . $GLOBALS['ecs']->table('seller_shopwindow') . (' where id=\'' . $id . '\'');
	$win_goods = $GLOBALS['db']->getOne($sql);

	foreach ($linked_array as $val) {
		if (!strstr($win_goods, $val) && !empty($val)) {
			$win_goods .= !empty($win_goods) ? ',' . $val : $val;
		}
	}

	$sql = 'update ' . $GLOBALS['ecs']->table('seller_shopwindow') . (' set win_goods=\'' . $win_goods . '\' where id=\'' . $id . '\'');
	$GLOBALS['db']->query($sql);
	$win_goods = get_win_goods($id);
	$smarty->assign('filter_result', $win_goods);
	$result['content'] = $smarty->fetch('library/move_right.lbi');
	clear_cache_files();
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'drop_win_goods') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$drop_goods = empty($_REQUEST['value']) ? array() : explode(',', $_REQUEST['value']);
	$id = empty($_REQUEST['win_id']) ? 0 : intval($_REQUEST['win_id']);
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$win_goods = $GLOBALS['db']->getOne('select win_goods from ' . $GLOBALS['ecs']->table('seller_shopwindow') . (' where id=\'' . $id . '\''));
	$win_goods_arr = explode(',', $win_goods);

	foreach ($drop_goods as $val) {
		if (strstr($win_goods, $val) && !empty($val)) {
			$key = array_search($val, $win_goods_arr);

			if ($key !== false) {
				array_splice($win_goods_arr, $key, 1);
			}
		}
	}

	$new_win_goods = '';

	foreach ($win_goods_arr as $val) {
		if (!strstr($new_win_goods, $val) && !empty($val)) {
			$new_win_goods .= !empty($new_win_goods) ? ',' . $val : $val;
		}
	}

	$sql = 'update ' . $GLOBALS['ecs']->table('seller_shopwindow') . (' set win_goods=\'' . $new_win_goods . '\' where id=\'' . $id . '\'');
	$GLOBALS['db']->query($sql);
	$win_goods = get_win_goods($id);
	$smarty->assign('filter_result', $win_goods);
	$result['content'] = $smarty->fetch('library/move_right.lbi');
	clear_cache_files();
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'upload_img') {
	include_once ROOT_PATH . '/includes/cls_image.php';
	$image = new cls_image($_CFG['bgcolor']);
	$act_type = empty($_REQUEST['type']) ? '' : trim($_REQUEST['type']);
	$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
	$result = array('error' => 0, 'pic' => '', 'name' => '');
	$typeArr = array('jpg', 'png', 'gif', 'jepg');
	$bucket_info = get_bucket_info();

	if (isset($_POST)) {
		$name = $_FILES['file']['name'];
		$size = $_FILES['file']['size'];
		$name_tmp = $_FILES['file']['tmp_name'];

		if (empty($name)) {
			$result['error'] = '您还未选择图片！';
		}

		$type = strtolower(substr(strrchr($name, '.'), 1));

		if (!in_array($type, $typeArr)) {
			$result['error'] = '清上传jpg,jepg,png或gif类型的图片！';
		}
	}

	if ($act_type == 'goods_img') {
		$_FILES['goods_img'] = $_FILES['file'];
		$proc_thumb = isset($GLOBALS['shop_id']) && 0 < $GLOBALS['shop_id'] ? false : true;
		$_POST['auto_thumb'] = 1;
		$_REQUEST['goods_id'] = $id;
		$goods_id = $id;
		$goods_img = '';
		$goods_thumb = '';
		$original_img = '';
		$old_original_img = '';
		if ($_FILES['goods_img']['tmp_name'] != '' && $_FILES['goods_img']['tmp_name'] != 'none') {
			if (empty($is_url_goods_img)) {
				$original_img = $image->upload_image($_FILES['goods_img'], array('type' => 1));
			}

			$goods_img = $original_img;

			if ($_CFG['auto_generate_gallery']) {
				$img = $original_img;
				$pos = strpos(basename($img), '.');
				$newname = dirname($img) . '/' . $image->random_filename() . substr(basename($img), $pos);
				copy($img, $newname);
				$img = $newname;
				$gallery_img = $img;
				$gallery_thumb = $img;
			}

			if ($proc_thumb && 0 < $image->gd_version() && $image->check_img_function($_FILES['goods_img']['type']) || $is_url_goods_img) {
				if (empty($is_url_goods_img)) {
					if ($_CFG['image_width'] != 0 || $_CFG['image_height'] != 0) {
						$goods_img = $image->make_thumb(array('img' => $goods_img, 'type' => 1), $GLOBALS['_CFG']['image_width'], $GLOBALS['_CFG']['image_height']);
					}

					if ($_CFG['auto_generate_gallery']) {
						$newname = dirname($img) . '/' . $image->random_filename() . substr(basename($img), $pos);
						copy($img, $newname);
						$gallery_img = $newname;
					}

					if (0 < intval($_CFG['watermark_place']) && !empty($GLOBALS['_CFG']['watermark'])) {
						if ($image->add_watermark($goods_img, '', $GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']) === false) {
							sys_msg($image->error_msg(), 1, array(), false);
						}

						if ($_CFG['auto_generate_gallery']) {
							if ($image->add_watermark($gallery_img, '', $GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']) === false) {
								sys_msg($image->error_msg(), 1, array(), false);
							}
						}
					}
				}

				if ($_CFG['auto_generate_gallery']) {
					if ($_CFG['thumb_width'] != 0 || $_CFG['thumb_height'] != 0) {
						$gallery_thumb = $image->make_thumb(array('img' => $img, 'type' => 1), $GLOBALS['_CFG']['thumb_width'], $GLOBALS['_CFG']['thumb_height']);
					}
				}
			}
		}

		if (isset($_FILES['goods_thumb']) && $_FILES['goods_thumb']['tmp_name'] != '' && isset($_FILES['goods_thumb']['tmp_name']) && $_FILES['goods_thumb']['tmp_name'] != 'none') {
			$goods_thumb = $image->upload_image($_FILES['goods_thumb'], array('type' => 1));
		}
		else {
			if ($proc_thumb && isset($_POST['auto_thumb']) && !empty($original_img)) {
				if ($_CFG['thumb_width'] != 0 || $_CFG['thumb_height'] != 0) {
					$goods_thumb = $image->make_thumb(array('img' => $original_img, 'type' => 1), $GLOBALS['_CFG']['thumb_width'], $GLOBALS['_CFG']['thumb_height']);
				}
				else {
					$goods_thumb = $original_img;
				}
			}
		}

		$original_img = reformat_image_name('goods', $goods_id, $original_img, 'source');
		$goods_img = reformat_image_name('goods', $goods_id, $goods_img, 'goods');
		$goods_thumb = reformat_image_name('goods_thumb', $goods_id, $goods_thumb, 'thumb');
		$result['data'] = array('original_img' => $original_img, 'goods_img' => $goods_img, 'goods_thumb' => $goods_thumb);

		if (empty($goods_id)) {
			$_SESSION['goods'][$admin_id][$goods_id] = $result['data'];
		}
		else {
			get_del_edit_goods_img($goods_id);
			$db->autoExecute($ecs->table('goods'), $result['data'], 'UPDATE', 'goods_id = \'' . $goods_id . '\'');
		}

		get_oss_add_file($result['data']);
		$gallery_images = array();

		if ($img) {
			if (empty($is_url_goods_img)) {
				$img = reformat_image_name('gallery', $goods_id, $img, 'source');
				$gallery_img = reformat_image_name('gallery', $goods_id, $gallery_img, 'goods');
			}
			else {
				$img = $url_goods_img;
				$gallery_img = $url_goods_img;
			}

			$gallery_thumb = reformat_image_name('gallery_thumb', $goods_id, $gallery_thumb, 'thumb');
			$gallery_count = $GLOBALS['db']->getOne('SELECT MAX(img_desc) FROM ' . $GLOBALS['ecs']->table('goods_gallery') . (' WHERE goods_id = \'' . $goods_id . '\''));
			$img_desc = $gallery_count + 1;
			$gallery_images = array($gallery_img, $gallery_thumb, $img);
			$sql = 'INSERT INTO ' . $ecs->table('goods_gallery') . ' (goods_id, img_url, img_desc, thumb_url, img_original) ' . ('VALUES (\'' . $goods_id . '\', \'' . $gallery_img . '\', ' . $img_desc . ', \'' . $gallery_thumb . '\', \'' . $img . '\')');
			$db->query($sql);
			$thumb_img_id[] = $GLOBALS['db']->insert_id();
			get_oss_add_file(array($gallery_img, $gallery_thumb, $img));

			if (!empty($_SESSION['thumb_img_id' . $_SESSION['seller_id']])) {
				$_SESSION['thumb_img_id' . $_SESSION['seller_id']] = array_merge($thumb_img_id, $_SESSION['thumb_img_id' . $_SESSION['seller_id']]);
			}
			else {
				$_SESSION['thumb_img_id' . $_SESSION['seller_id']] = $thumb_img_id;
			}

			$result['img_desc'] = $img_desc;
		}

		if ($GLOBALS['_CFG']['open_oss'] == 1 && $bucket_info['is_delimg'] == 1) {
			if ($result['data']) {
				$goods_images = array($result['data']['original_img'], $result['data']['goods_img'], $result['data']['goods_thumb']);
				dsc_unlink($goods_images);
			}

			if ($gallery_images) {
				dsc_unlink($gallery_images);
			}
		}

		$pic_name = '';
		$goods_img = get_image_path($goods_id, $goods_img, true);
		$pic_url = $goods_img;
		$upload_status = 1;
	}
	else if ($act_type == 'gallery_img') {
		$_FILES['img_url'] = array(
	'name'     => array($_FILES['file']['name']),
	'type'     => array($_FILES['file']['type']),
	'tmp_name' => array($_FILES['file']['tmp_name']),
	'error'    => array($_FILES['file']['error']),
	'size'     => array($_FILES['file']['size'])
	);
		$_REQUEST['goods_id_img'] = $id;
		$_REQUEST['img_desc'] = array(
	array('')
	);
		$_REQUEST['img_file'] = array(
	array('')
	);
		$goods_id = !empty($_REQUEST['goods_id_img']) ? intval($_REQUEST['goods_id_img']) : 0;
		$img_desc = !empty($_REQUEST['img_desc']) ? $_REQUEST['img_desc'] : array();
		$img_file = !empty($_REQUEST['img_file']) ? $_REQUEST['img_file'] : array();
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

		$gallery_count = get_goods_gallery_count($goods_id);
		$result['img_desc'] = $gallery_count + 1;
		handle_gallery_image_add($goods_id, $_FILES['img_url'], $img_desc, $img_file, '', '', 'ajax', $result['img_desc']);
		clear_cache_files();

		if (0 < $goods_id) {
			$sql = 'SELECT * FROM ' . $ecs->table('goods_gallery') . (' WHERE goods_id = \'' . $goods_id . '\' ORDER BY img_desc ASC');
		}
		else {
			$img_id = $_SESSION['thumb_img_id' . $_SESSION['seller_id']];
			$where = '';

			if ($img_id) {
				$where = 'AND img_id ' . db_create_in($img_id) . '';
			}

			$sql = 'SELECT * FROM ' . $ecs->table('goods_gallery') . (' WHERE goods_id=\'\' ' . $where . ' ORDER BY img_desc ASC');
		}

		$img_list = $db->getAll($sql);
		if (isset($GLOBALS['shop_id']) && 0 < $GLOBALS['shop_id']) {
			foreach ($img_list as $key => $gallery_img) {
				if ($GLOBALS['_CFG']['open_oss'] == 1 && $bucket_info['is_delimg'] == 1) {
					$gallery_images = array($gallery_img['img_url'], $gallery_img['thumb_url'], $gallery_img['img_original']);
					dsc_unlink($gallery_images);
				}

				$gallery_img['img_original'] = get_image_path($gallery_img['goods_id'], $gallery_img['img_original'], true);
				$img_list[$key]['img_url'] = $gallery_img['img_original'];
				$gallery_img['thumb_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['thumb_url'], true);
				$img_list[$key]['thumb_url'] = $gallery_img['thumb_url'];
			}
		}
		else {
			foreach ($img_list as $key => $gallery_img) {
				if ($GLOBALS['_CFG']['open_oss'] == 1 && $bucket_info['is_delimg'] == 1) {
					$gallery_images = array($gallery_img['img_url'], $gallery_img['thumb_url'], $gallery_img['img_original']);
					dsc_unlink($gallery_images);
				}

				$gallery_img['thumb_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['thumb_url'], true);
				$img_list[$key]['thumb_url'] = $gallery_img['thumb_url'];
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
		$smarty->assign('goods', $goods);
		$this_img_info = $GLOBALS['db']->getRow(' SELECT * FROM ' . $GLOBALS['ecs']->table('goods_gallery') . (' WHERE goods_id = \'' . $goods_id . '\' ORDER BY img_id DESC LIMIT 1 '));
		$result['img_id'] = $this_img_info['img_id'];
		$result['min_img_id'] = $min_img_id;
		$pic_name = '';
		$this_img_info['thumb_url'] = get_image_path($goods_id, $this_img_info['thumb_url'], true);
		$pic_url = $this_img_info['thumb_url'];
		$upload_status = 1;
		$result['external_url'] = '';
	}

	if ($upload_status) {
		$result['error'] = 0;
		$result['pic'] = $pic_url;
		$result['name'] = $pic_name;
	}
	else {
		$result['error'] = '上传有误，清检查服务器配置！';
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'add_link_goods') {
	$linked_array = empty($_REQUEST['value']) ? array() : explode(',', $_REQUEST['value']);
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
	$is_double = empty($_REQUEST['is_single']) ? 1 : 0;
	$result = array('error' => 0, 'message' => '', 'content' => '');

	foreach ($linked_array as $val) {
		if ($is_double) {
			$sql = 'INSERT INTO ' . $ecs->table('link_goods') . ' (goods_id, link_goods_id, is_double, admin_id) ' . ('VALUES (\'' . $val . '\', \'' . $goods_id . '\', \'' . $is_double . '\', \'' . $_SESSION['seller_id'] . '\')');
			$db->query($sql, 'SILENT');
		}

		$sql = 'INSERT INTO ' . $ecs->table('link_goods') . ' (goods_id, link_goods_id, is_double, admin_id) ' . ('VALUES (\'' . $goods_id . '\', \'' . $val . '\', \'' . $is_double . '\', \'' . $_SESSION['seller_id'] . '\')');
		$db->query($sql, 'SILENT');
	}

	$linked_goods = get_linked_goods($goods_id);
	$options = array();

	foreach ($linked_goods as $val) {
		$options[] = array('value' => $val['goods_id'], 'text' => $val['goods_name'], 'data' => '');
	}

	$smarty->assign('filter_result', $options);
	$result['content'] = $smarty->fetch('library/move_right.lbi');
	clear_cache_files();
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'drop_link_goods') {
	$drop_goods = empty($_REQUEST['value']) ? array() : explode(',', $_REQUEST['value']);
	$drop_goods_ids = db_create_in($drop_goods);
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
	$is_signle = empty($_REQUEST['is_single']) ? 0 : 1;
	$result = array('error' => 0, 'message' => '', 'content' => '');

	if (!$is_signle) {
		$sql = 'DELETE FROM ' . $ecs->table('link_goods') . (' WHERE link_goods_id = \'' . $goods_id . '\' AND goods_id ') . $drop_goods_ids;
	}
	else {
		$sql = 'UPDATE ' . $ecs->table('link_goods') . ' SET is_double = 0 ' . (' WHERE link_goods_id = \'' . $goods_id . '\' AND goods_id ') . $drop_goods_ids;
	}

	if ($goods_id == 0) {
		$sql .= ' AND admin_id = \'' . $_SESSION['seller_id'] . '\'';
	}

	$db->query($sql);
	$sql = 'DELETE FROM ' . $ecs->table('link_goods') . (' WHERE goods_id = \'' . $goods_id . '\' AND link_goods_id ') . $drop_goods_ids;

	if ($goods_id == 0) {
		$sql .= ' AND admin_id = \'' . $_SESSION['seller_id'] . '\'';
	}

	$db->query($sql);
	$linked_goods = get_linked_goods($goods_id);
	$options = array();

	foreach ($linked_goods as $val) {
		$options[] = array('value' => $val['goods_id'], 'text' => $val['goods_name'], 'data' => '');
	}

	$smarty->assign('filter_result', $options);
	$result['content'] = $smarty->fetch('library/move_right.lbi');
	clear_cache_files();
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'add_group_goods') {
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
	$goods_ids = empty($_REQUEST['goods_ids']) ? 0 : trim($_REQUEST['goods_ids']);
	$price = empty($_REQUEST['price2']) ? 0 : floatval($_REQUEST['price2']);
	$group_id = empty($_REQUEST['group2']) ? 1 : intval($_REQUEST['group2']);
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$sql = 'select count(*) from ' . $ecs->table('group_goods') . (' where parent_id = \'' . $goods_id . '\' and group_id = \'' . $group_id . '\' and admin_id = \'') . $_SESSION['seller_id'] . '\'';
	$groupCount = $db->getOne($sql);
	$message = '';

	if ($groupCount < 1000) {
		if ($goods_ids) {
			$goods_ids = explode(',', $goods_ids);
		}

		if (!empty($goods_ids)) {
			foreach ($goods_ids as $key => $val) {
				$sql = 'SELECT id FROM ' . $ecs->table('group_goods') . (' WHERE parent_id = \'' . $goods_id . '\' AND goods_id = \'' . $val . '\'  LIMIT 1');

				if (!$db->getOne($sql)) {
					$price_goods = 0;

					if ($price == 0) {
						$price_goods = $db->getOne('SELECT shop_price FROM' . $ecs->table('goods') . (' WHERE goods_id = \'' . $val . '\''));
					}
					else {
						$price_goods = $price;
					}

					$sql = 'INSERT INTO ' . $ecs->table('group_goods') . ' (parent_id, goods_id, goods_price, admin_id, group_id) ' . ('VALUES (\'' . $goods_id . '\', \'' . $val . '\', \'' . $price_goods . '\', \'' . $_SESSION['seller_id'] . '\', \'' . $group_id . '\')');
					$db->query($sql, 'SILENT');
				}
			}
		}

		$error = 0;
	}
	else {
		$error = 1;
		$message = '一组配件只能添加五个商品，如需添加则删除该组其它配件商品';
	}

	$arr = get_group_goods($goods_id);
	$smarty->assign('group_goods_list', $arr);

	if ($_CFG['group_goods']) {
		$group_goods_arr = explode(',', $_CFG['group_goods']);
		$arr = array();

		foreach ($group_goods_arr as $k => $v) {
			$arr[$k + 1] = $v;
		}

		$smarty->assign('group_goods_arr', $arr);
	}

	$result['content'] = $smarty->fetch('library/group_goods_list.lbi');
	$result['error'] = $error;
	$result['message'] = $message;
	clear_cache_files();
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'drop_group_goods') {
	$fittings = empty($_REQUEST['value']) ? array() : explode(',', $_REQUEST['value']);
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
	$price = empty($_REQUEST['price2']) ? 0 : floatval($_REQUEST['price2']);
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$sql = 'DELETE FROM ' . $ecs->table('group_goods') . (' WHERE parent_id=\'' . $goods_id . '\' AND ') . db_create_in($fittings, 'id');

	if ($goods_id == 0) {
		$sql .= ' AND admin_id = \'' . $_SESSION['seller_id'] . '\'';
	}

	$db->query($sql);
	$arr = get_group_goods($goods_id);
	$opt = array();

	foreach ($arr as $val) {
		$opt[] = array('value' => $val['id'], 'text' => '[' . $val['group_name'] . ']' . $val['goods_name'], 'data' => '');
	}

	$smarty->assign('filter_result', $opt);
	$result['content'] = $smarty->fetch('library/move_right.lbi');
	clear_cache_files();
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'add_goods_article') {
	$articles = empty($_REQUEST['value']) ? array() : explode(',', $_REQUEST['value']);
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
	$result = array('error' => 0, 'message' => '', 'content' => '');

	foreach ($articles as $val) {
		$sql = 'INSERT INTO ' . $ecs->table('goods_article') . ' (goods_id, article_id, admin_id) ' . ('VALUES (\'' . $goods_id . '\', \'' . $val . '\', \'' . $_SESSION['seller_id'] . '\')');
		$db->query($sql);
	}

	$arr = get_goods_articles($goods_id);
	$opt = array();

	foreach ($arr as $val) {
		$opt[] = array('value' => $val['article_id'], 'text' => $val['title'], 'data' => '');
	}

	$smarty->assign('filter_result', $opt);
	$result['content'] = $smarty->fetch('library/move_right.lbi');
	clear_cache_files();
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'drop_goods_article') {
	$articles = empty($_REQUEST['value']) ? array() : explode(',', $_REQUEST['value']);
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$sql = 'DELETE FROM ' . $ecs->table('goods_article') . ' WHERE ' . db_create_in($articles, 'article_id') . (' AND goods_id = \'' . $goods_id . '\'');
	$db->query($sql);
	$arr = get_goods_articles($goods_id);
	$opt = array();

	foreach ($arr as $val) {
		$opt[] = array('value' => $val['article_id'], 'text' => $val['title'], 'data' => '');
	}

	$smarty->assign('filter_result', $opt);
	$result['content'] = $smarty->fetch('library/move_right.lbi');
	clear_cache_files();
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'add_area_goods') {
	$fittings = empty($_REQUEST['value']) ? array() : explode(',', $_REQUEST['value']);
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
	$result = array('error' => 0, 'message' => '', 'content' => '');

	if ($goods_id) {
		$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $goods_id . '\'');
		$ru_id = $GLOBALS['db']->getOne($sql);
	}
	else {
		$ru_id = $adminru['ru_id'];
	}

	foreach ($fittings as $val) {
		$sql = 'INSERT INTO ' . $ecs->table('link_area_goods') . ' (goods_id, region_id, ru_id) ' . ('VALUES (\'' . $goods_id . '\', \'' . $val . '\', \'' . $ru_id . '\')');
		$db->query($sql, 'SILENT');
	}

	$arr = get_area_goods($goods_id);
	$opt = array();

	foreach ($arr as $val) {
		$opt[] = array('value' => $val['region_id'], 'text' => $val['region_name'], 'data' => 0);
	}

	$smarty->assign('filter_result', $opt);
	$result['content'] = $smarty->fetch('library/move_right.lbi');
	clear_cache_files();
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'drop_area_goods') {
	$drop_goods = empty($_REQUEST['value']) ? array() : explode(',', $_REQUEST['value']);
	$drop_goods_ids = db_create_in($drop_goods);
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$sql = 'DELETE FROM ' . $ecs->table('link_area_goods') . ' WHERE region_id' . $drop_goods_ids . (' and goods_id = \'' . $goods_id . '\'');

	if ($goods_id == 0) {
		$ru_id = $adminru['ru_id'];
		$sql .= ' AND ru_id = \'' . $ru_id . '\'';
	}

	$db->query($sql);
	$arr = get_area_goods($goods_id);
	$opt = array();

	foreach ($arr as $val) {
		$opt[] = array('value' => $val['region_id'], 'text' => $val['region_name'], 'data' => 0);
	}

	$smarty->assign('filter_result', $opt);
	$result['content'] = $smarty->fetch('library/move_right.lbi');
	clear_cache_files();
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'add_link_desc') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	check_authz_json('goods_manage');
	$linked_array = empty($_REQUEST['value']) ? array() : explode(',', $_REQUEST['value']);
	$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
	get_add_edit_link_desc($linked_array, 0, $id);
	$linked_goods = get_linked_goods_desc();
	$options = array();

	foreach ($linked_goods as $val) {
		$options[] = array('value' => $val['goods_id'], 'text' => $val['goods_name'], 'data' => '');
	}

	$smarty->assign('filter_result', $options);
	$content = $smarty->fetch('library/move_right.lbi');
	clear_cache_files();
	make_json_result($content);
}
else if ($_REQUEST['act'] == 'drop_link_desc') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	check_authz_json('goods_manage');
	$drop_goods = empty($_REQUEST['value']) ? array() : explode(',', $_REQUEST['value']);
	$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
	get_add_edit_link_desc($drop_goods, 1, $id);
	$linked_goods = get_linked_goods_desc();
	$options = array();

	foreach ($linked_goods as $val) {
		$options[] = array('value' => $val['goods_id'], 'text' => $val['goods_name'], 'data' => '');
	}

	if (empty($linked_goods)) {
		$sql = 'delete from ' . $ecs->table('link_desc_temporary') . ' where 1';
		$db->query($sql);
	}

	$smarty->assign('filter_result', $options);
	$content = $smarty->fetch('library/move_right.lbi');
	clear_cache_files();
	make_json_result($content);
}
else if ($_REQUEST['act'] == 'edit_navname') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'pic_id' => '', 'content' => '');
	$exc = new exchange($ecs->table('merchants_nav'), $db, 'id', 'name');
	$nav_name = isset($_REQUEST['val']) ? addslashes($_REQUEST['val']) : '';
	$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	if (0 < $id && !empty($nav_name)) {
		$is_only = $exc->is_only('name', $nav_name, 0, ' ru_id = ' . $adminru['ru_id']);

		if (!$is_only) {
			$result['error'] = 0;
			$result['content'] = '导航’' . $nav_name . '‘已存在';
		}
		else {
			$sql = 'UPDATE' . $ecs->table('merchants_nav') . (' SET name = \'' . $nav_name . '\' WHERE id = \'' . $id . '\' AND ru_id = ') . $adminru['ru_id'];
			$db->query($sql);
			$result['error'] = 1;
			$result['content'] = '编辑成功';
		}
	}
	else {
		$result['error'] = 0;
		$result['content'] = '导航不存在或者导航名称不能为空';
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'edit_navurl') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'pic_id' => '', 'content' => '');
	$url = isset($_REQUEST['val']) ? addslashes($_REQUEST['val']) : '';
	$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if (0 < $id) {
		$sql = 'UPDATE' . $ecs->table('merchants_nav') . (' SET url = \'' . $url . '\' WHERE id = \'' . $id . '\' AND ru_id = ') . $adminru['ru_id'];
		$db->query($sql);
		$result['error'] = 1;
		$result['content'] = '编辑成功';
	}
	else {
		$result['error'] = 0;
		$result['content'] = '导航不存在';
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'edit_navvieworder') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'pic_id' => '', 'content' => '');
	$order = isset($_REQUEST['val']) ? intval($_REQUEST['val']) : '';
	$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if (0 < $id) {
		if (preg_match('/^\\d+$/i', $order)) {
			$sql = 'UPDATE' . $ecs->table('merchants_nav') . (' SET vieworder = \'' . $order . '\' WHERE id = \'' . $id . '\' AND ru_id = ') . $adminru['ru_id'];
			$db->query($sql);
			$result['error'] = 1;
			$result['content'] = '编辑成功';
		}
		else {
			$result['error'] = 0;
			$result['content'] = '排序必须为数字';
		}
	}
	else {
		$result['error'] = 0;
		$result['content'] = '导航不存在';
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'remove_nav') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'pic_id' => '', 'content' => '');
	$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if (0 < $id) {
		$row = $db->getRow('SELECT ctype,cid,type FROM ' . $GLOBALS['ecs']->table('merchants_nav') . (' WHERE id = \'' . $id . '\' LIMIT 1'));
		if ($row['type'] == 'middle' && $row['ctype'] && $row['cid']) {
			set_show_in_nav($row['ctype'], $row['cid'], 0);
		}

		$sql = ' DELETE FROM ' . $GLOBALS['ecs']->table('merchants_nav') . (' WHERE id=\'' . $id . '\' LIMIT 1');
		$db->query($sql);
		$result['error'] = 1;
		$result['content'] = '删除成功';
	}
	else {
		$result['error'] = 0;
		$result['content'] = '导航不存在';
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'add_nav') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'pic_id' => '', 'content' => '');
	$exc = new exchange($ecs->table('merchants_nav'), $db, 'id', 'name');
	$link = isset($_REQUEST['link']) ? addslashes($_REQUEST['link']) : '';
	$name = isset($_REQUEST['nav_name']) ? addslashes($_REQUEST['nav_name']) : '';

	if (!empty($name)) {
		$is_only = $exc->is_only('name', $name, 0, ' ru_id = ' . $adminru['ru_id']);

		if (!$is_only) {
			$result['error'] = 0;
			$result['content'] = '导航’' . $name . '‘已存在';
		}
		else {
			$sql = 'INSERT INTO' . $ecs->table('merchants_nav') . ('(`name`,`url`,`ifshow`,`type`,`ru_id`,`vieworder`) VALUES(\'' . $name . '\',\'' . $link . '\',1,\'middle\',\'') . $adminru['ru_id'] . '\',50)';
			$db->query($sql);
			$id = $db->insert_id();
			$result['error'] = 1;
			$html_id = '\'' . $id . '\'';
			$html_act_name = '\'edit_navname\'';
			$html_act_url = '\'edit_navurl\'';
			$html_act_order = '\'edit_navvieworder\'';
			$html_act_if_show = '\'edit_ifshow\'';
			$html_act_type = '\'1\'';
			$html = '<tr><td><input type="text" onchange = "edit_nav(this.value ,' . $html_id . ',' . $html_act_name . ')" value="' . $name . '"></td>';
			$html .= '<td><input type="text" onchange = "edit_nav(this.value ,' . $html_id . ',' . $html_act_url . ')" value="' . $link . '"></td>';
			$html .= '<td class="center"><input type="text" onchange = "edit_nav(this.value ,' . $html_id . ',' . $html_act_order . ')" class="small" value="50"></td>';
			$html .= '<td class="center" id="nav_' . $id . '"><img onclick = "edit_nav(' . $html_act_type . ' ,' . $html_id . ',' . $html_act_if_show . ',' . $html_act_type . ')" src="images/yes.gif"/></td>';
			$html .= '<td class="center"><a href="javascript:void(0);" onclick="remove_nav(' . $html_id . ')" class="pic_del del">删除</a></td></tr>';
			$result['content'] = $html;
		}
	}
	else {
		$result['error'] = 0;
		$result['content'] = '导航名称不能为空';
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'edit_ifshow') {
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array('error' => '', 'pic_id' => '', 'content' => '');
	$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$ifshow = isset($_REQUEST['val']) ? intval($_REQUEST['val']) : 0;

	if (0 < $id) {
		if ($ifshow == 0) {
			$val = 1;
		}
		else {
			$val = 0;
		}

		$sql = 'UPDATE' . $ecs->table('merchants_nav') . (' SET ifshow = \'' . $val . '\' WHERE id = \'' . $id . '\' AND ru_id = ') . $adminru['ru_id'];
		$db->query($sql);
		$result['error'] = 1;
		$result['id'] = $id;
		$html_ifshow = '\'' . $val . '\'';
		$html_id = '\'' . $id . '\'';
		$html_act_if_show = '\'edit_ifshow\'';
		$html_act_type = '\'1\'';

		if ($val == 1) {
			$src = 'images/yes.gif';
		}
		else {
			$src = 'images/no.gif';
		}

		$html = '<img onclick = "edit_nav(' . $html_ifshow . ' ,' . $html_id . ',' . $html_act_if_show . ',' . $html_act_type . ')" src="' . $src . '"/>';
		$result['content'] = $html;
	}
	else {
		$result['error'] = 0;
		$result['content'] = '导航不存在';
	}

	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'gallery_album_list') {
	$json = new JSON();
	$result = array('error' => 0, 'message' => '', 'log_type' => '', 'content' => '');
	$album_id = !empty($_REQUEST['album_id']) ? intval($_REQUEST['album_id']) : 0;
	$inid = !empty($_REQUEST['inid']) ? addslashes($_REQUEST['inid']) : '';
	$sql = 'SELECT album_id,ru_id,album_mame,album_cover,album_desc,sort_order FROM ' . $ecs->table('gallery_album') . ' ' . ' WHERE ru_id = \'' . $adminru['ru_id'] . '\' ORDER BY sort_order';
	$gallery_album_list = $db->getAll($sql);
	$smarty->assign('gallery_album_list', $gallery_album_list);
	$res = array();
	if ($gallery_album_list || 0 < $album_id) {
		$album_id = 0 < $album_id ? $album_id : $gallery_album_list[0]['album_id'];
		$sql = 'SELECT album_mame FROM ' . $ecs->table('gallery_album') . ' ' . ' WHERE ru_id = \'' . $adminru['ru_id'] . '\' AND  album_id = \'' . $album_id . '\'';
		$album_mame = $db->getOne($sql);
		$where = '';
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('pic_album') . ' WHERE ru_id = \'' . $adminru['ru_id'] . '\' AND album_id = \'' . $album_id . '\'';
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter, 2);
		$where = 'LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('pic_album') . ' WHERE ru_id = \'' . $adminru['ru_id'] . '\' AND album_id =\'' . $album_id . ('\' ORDER BY pic_id ASC ' . $where);
		$res = $GLOBALS['db']->getAll($sql);

		if (!empty($res)) {
			foreach ($res as $k => $v) {
				if (0 < $v['pic_size']) {
					$res[$k]['pic_size'] = number_format($v['pic_size'] / 1024, 2) . 'k';
				}
			}
		}
	}

	$smarty->assign('pic_album_list', $res);
	$filter['page_arr'] = seller_page($filter, $filter['page'], 14);
	$smarty->assign('filter', $filter);
	$smarty->assign('album_id', $album_id);
	$smarty->assign('inid', $inid);
	$smarty->assign('album_mame', $album_mame);
	$smarty->assign('image_type', 1);
	$result['content'] = $smarty->fetch('templates/library/album_dialog.lbi');
	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'insert_goodsImg') {
	$json = new JSON();
	$result = array('error' => 0, 'message' => '');
	$pic_id = !empty($_REQUEST['pic_id']) ? trim($_REQUEST['pic_id']) : '';
	$goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
	$inid = !empty($_REQUEST['inid']) ? trim($_REQUEST['inid']) : '';
	$thumb_img_id = array();
	$img_default = 1;
	$sql = 'SELECT pic_file,pic_thumb,pic_image FROM' . $ecs->table('pic_album') . 'WHERE pic_id in(' . $pic_id . ')';
	$img_list = $db->getAll($sql);

	if ($img_list) {
		$j = 0;

		foreach ($img_list as $key => $val) {
			$j++;
			$img_id = 0;

			if ($inid == 'gallery_album') {
				$img_desc_new = 1;

				if (0 < $goods_id) {
					$sql = 'UPDATE' . $ecs->table('goods_gallery') . (' SET img_desc = img_desc+1 WHERE goods_id = \'' . $goods_id . '\'');
					$db->query($sql);
				}
			}
			else {
				$gallery_count = get_goods_gallery_count($goods_id);
				$img_desc_new = $gallery_count + 1;
			}

			$val['pic_file'] = str_replace(' ', '', $val['pic_file'], $i);
			$val['pic_image'] = str_replace(' ', '', $val['pic_image'], $i);
			$val['pic_thumb'] = str_replace(' ', '', $val['pic_thumb'], $i);

			if ($j == 1) {
				$result['data'] = array('original_img' => $val['pic_file'], 'goods_img' => $val['pic_image'], 'goods_thumb' => $val['pic_thumb']);
			}
			else {
				$result['data'] = array();
			}

			$sql = 'INSERT INTO ' . $ecs->table('goods_gallery') . ' (goods_id, img_url, img_desc, thumb_url, img_original) ' . ('VALUES (\'' . $goods_id . '\', \'') . $val['pic_image'] . ('\', ' . $img_desc_new . ', \'') . $val['pic_thumb'] . '\', \'' . $val['pic_file'] . '\')';
			$db->query($sql);
			$thumb_img_id[] = $img_id = $GLOBALS['db']->insert_id();
			get_oss_add_file($result['data']);
		}
	}

	if (!empty($_SESSION['thumb_img_id' . $_SESSION['seller_id']]) && is_array($thumb_img_id) && is_array($_SESSION['thumb_img_id' . $_SESSION['seller_id']])) {
		$_SESSION['thumb_img_id' . $_SESSION['seller_id']] = array_merge($thumb_img_id, $_SESSION['thumb_img_id' . $_SESSION['seller_id']]);
	}
	else {
		$_SESSION['thumb_img_id' . $_SESSION['seller_id']] = $thumb_img_id;
	}

	if (0 < $goods_id) {
		$sql = 'SELECT img_desc,thumb_url,goods_id,external_url,img_id FROM ' . $ecs->table('goods_gallery') . (' WHERE goods_id = \'' . $goods_id . '\' ORDER BY img_desc ASC');
	}
	else {
		$where = '';

		if (!empty($_SESSION['thumb_img_id' . $_SESSION['seller_id']])) {
			$where = 'AND img_id ' . db_create_in($_SESSION['thumb_img_id' . $_SESSION['seller_id']]) . '';
		}

		$sql = 'SELECT img_desc,thumb_url,goods_id,external_url,img_id FROM ' . $ecs->table('goods_gallery') . (' WHERE goods_id=\'\' ' . $where . ' ORDER BY img_desc ASC');
	}

	$goods_gallery_list = $db->getAll($sql);

	if (!empty($goods_gallery_list)) {
		foreach ($goods_gallery_list as $key => $val) {
			if ($val['thumb_url']) {
				$goods_gallery_list[$key]['thumb_url'] = get_image_path($goods_id, $val['thumb_url']);
			}
		}
	}

	$smarty->assign('img_list', $goods_gallery_list);
	$result['content'] = $smarty->fetch('library/gallery_img.lbi');
	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'getFCKeditor') {
	$json = new JSON();
	$result = array('goods_desc' => 0);
	include_once ROOT_PATH . 'includes/fckeditor/fckeditor.php';
	$content = isset($_REQUEST['content']) ? stripslashes($_REQUEST['content']) : '';
	$img_src = isset($_REQUEST['img_src']) ? trim($_REQUEST['img_src']) : '';

	if ($img_src) {
		$img_src = explode(',', $img_src);

		if (!empty($img_src)) {
			foreach ($img_src as $v) {
				$content .= '<p><img src=\'' . $v . '\' /></p>';
			}
		}
	}

	if (!empty($content)) {
		create_html_editor('goods_desc', trim($content));
		$result['goods_desc'] = $smarty->get_template_vars('FCKeditor');
	}

	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'navInsert') {
	require ROOT_PATH . '/includes/lib_goods.php';
	$json = new JSON();
	$result = array();
	$result['mode'] = !empty($_REQUEST['mode']) ? trim($_REQUEST['mode']) : '';
	$navname = !empty($_REQUEST['navname']) ? $_REQUEST['navname'] : '';
	$navurl = !empty($_REQUEST['navurl']) ? $_REQUEST['navurl'] : '';
	$navvieworder = !empty($_REQUEST['navvieworder']) ? $_REQUEST['navvieworder'] : '';
	$count = COUNT($navname);
	$arr = array();
	$sort_vals = array();

	for ($i = 0; $i < $count; $i++) {
		$arr[$i]['name'] = trim($navname[$i]);
		$arr[$i]['url'] = $navurl[$i];
		$arr[$i]['opennew'] = 1;
		$arr[$i]['navvieworder'] = isset($navvieworder[$i]) ? intval($navvieworder[$i]) : 0;
		$sort_vals[$i] = isset($navvieworder[$i]) ? intval($navvieworder[$i]) : 0;
	}

	if (!empty($arr)) {
		array_multisort($sort_vals, SORT_ASC, $arr);
	}

	$smarty->assign('navigator', $arr);
	$smarty->assign('temp', 'navigator_home');
	$result['spec_attr'] = $arr;
	$result['content'] = $GLOBALS['smarty']->fetch('library/dialog.lbi');
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'getChildCat') {
	$json = new JSON();
	$result = array('content' => '');
	$cat_id = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$cat_list = cat_list($cat_id);
	$html = '';
	$html_child = '';

	if ($cat_list) {
		$html = '<div class="imitate_select select_w220" id="cat_id1"><div class="cite">' . $_LANG['select_please'] . '</div><ul>';

		foreach ($cat_list as $k => $v) {
			$html_child .= '<li><a href="javascript:void(0);" data-value="' . $v[cat_id] . '">' . $v[cat_name] . '</a></li>';
		}

		$html .= $html_child . '</ul><input type="hidden" value="" id="cat_id_val1"></div> ';
	}

	$result['content'] = $html;
	$result['contentChild'] = $html_child;
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'homeFloor') {
	$json = new JSON();
	$result = array('content' => '');
	$spec_attr = '';
	$result['floor_title'] = !empty($_REQUEST['floor_title']) ? trim($_REQUEST['floor_title']) : '';
	$spec_attr['hierarchy'] = !empty($_REQUEST['hierarchy']) ? intval($_REQUEST['hierarchy']) : '';
	$result['cat_goods'] = !empty($_REQUEST['cat_goods']) ? $_REQUEST['cat_goods'] : '';
	$result['moded'] = !empty($_REQUEST['mode']) ? trim($_REQUEST['mode']) : '';
	$result['cat_id'] = !empty($_REQUEST['Floorcat_id']) ? intval($_REQUEST['Floorcat_id']) : 0;
	$result['cateValue'] = !empty($_REQUEST['cateValue']) ? $_REQUEST['cateValue'] : '';
	$result['typeColor'] = !empty($_REQUEST['typeColor']) ? trim($_REQUEST['typeColor']) : '';
	$result['fontColor'] = !empty($_REQUEST['fontColor']) ? trim($_REQUEST['fontColor']) : '';
	$result['floorMode'] = !empty($_REQUEST['floorMode']) ? intval($_REQUEST['floorMode']) : '';
	$result['brand_ids'] = !empty($_REQUEST['brand_ids']) ? trim($_REQUEST['brand_ids']) : '';
	$result['leftBanner'] = !empty($_REQUEST['leftBanner']) ? $_REQUEST['leftBanner'] : '';
	$result['leftBannerLink'] = !empty($_REQUEST['leftBannerLink']) ? $_REQUEST['leftBannerLink'] : '';
	$result['leftBannerSort'] = !empty($_REQUEST['leftBannerSort']) ? $_REQUEST['leftBannerSort'] : '';
	$result['leftAdv'] = !empty($_REQUEST['leftAdv']) ? $_REQUEST['leftAdv'] : '';
	$result['leftAdvLink'] = !empty($_REQUEST['leftAdvLink']) ? $_REQUEST['leftAdvLink'] : '';
	$result['leftAdvSort'] = !empty($_REQUEST['leftAdvSort']) ? $_REQUEST['leftAdvSort'] : '';
	$result['rightAdv'] = !empty($_REQUEST['rightAdv']) ? $_REQUEST['rightAdv'] : '';
	$result['rightAdvLink'] = !empty($_REQUEST['rightAdvLink']) ? $_REQUEST['rightAdvLink'] : '';
	$result['rightAdvSort'] = !empty($_REQUEST['rightAdvSort']) ? $_REQUEST['rightAdvSort'] : '';
	$result['rightAdvTitle'] = !empty($_REQUEST['rightAdvTitle']) ? $_REQUEST['rightAdvTitle'] : '';
	$result['rightAdvSubtitle'] = !empty($_REQUEST['rightAdvSubtitle']) ? $_REQUEST['rightAdvSubtitle'] : '';
	$result['top_goods'] = !empty($_REQUEST['top_goods']) ? trim($_REQUEST['top_goods']) : '';
	$spec_attr = $result;
	$result['lift'] = !empty($_REQUEST['lift']) ? trim($_REQUEST['lift']) : '';
	$AdvNum = getAdvNum($result['moded'], $result['floorMode']);
	$AdvBanner = '';

	if (0 < $AdvNum['leftBanner']) {
		if (!empty($result['leftBanner'])) {
			foreach ($result['leftBanner'] as $k => $v) {
				$AdvBanner[$k]['leftBanner'] = $result['leftBanner'][$k];
				$AdvBanner[$k]['leftBannerSort'] = $result['leftBannerSort'][$k];

				if ($result['leftBannerLink'][$k]) {
					$AdvBanner[$k]['leftBannerLink'] = setRewrite($result['leftBannerLink'][$k]);
				}
				else {
					$AdvBanner[$k]['leftBannerLink'] = $result['leftBannerLink'][$k];
				}

				$sort_vals[$k] = isset($result['leftBannerSort'][$k]) ? $result['leftBannerSort'][$k] : 0;
			}
		}
		else {
			$AdvNum['leftBanner'] = 1;

			for ($k = 0; $k < $AdvNum['leftBanner']; $k++) {
				$AdvBanner[$k]['leftBanner'] = $result['leftBanner'][$k];
				$AdvBanner[$k]['leftBannerSort'] = $result['leftBannerSort'][$k];

				if ($result['leftBannerLink'][$k]) {
					$AdvBanner[$k]['leftBannerLink'] = setRewrite($result['leftBannerLink'][$k]);
				}
				else {
					$AdvBanner[$k]['leftBannerLink'] = $result['leftBannerLink'][$k];
				}

				$sort_vals[$k] = isset($result['leftBannerSort'][$k]) ? $result['leftBannerSort'][$k] : 0;
			}
		}
	}

	if ($AdvBanner) {
		array_multisort($sort_vals, SORT_ASC, $AdvBanner);
	}

	$spec_attr['leftBanner'] = $AdvBanner;
	$AdvLeft = '';
	$sort_vals = '';

	if (0 < $AdvNum['leftAdv']) {
		for ($k = 0; $k < $AdvNum['leftAdv']; $k++) {
			$AdvLeft[$k]['leftAdv'] = $result['leftAdv'][$k];
			$AdvLeft[$k]['leftAdvSort'] = $result['leftAdvSort'][$k];

			if ($result['leftAdvLink'][$k]) {
				$AdvLeft[$k]['leftAdvLink'] = setRewrite($result['leftAdvLink'][$k]);
			}
			else {
				$AdvLeft[$k]['leftAdvLink'] = $result['leftAdvLink'][$k];
			}

			$sort_vals[$k] = isset($result['leftAdvSort'][$k]) ? $result['leftAdvSort'][$k] : 0;
		}
	}

	if ($AdvLeft) {
		array_multisort($sort_vals, SORT_ASC, $AdvLeft);
	}

	$spec_attr['leftAdv'] = $AdvLeft;
	$AdvRight = '';
	$sort_vals = '';

	if (0 < $AdvNum['rightAdv']) {
		for ($k = 0; $k < $AdvNum['rightAdv']; $k++) {
			$AdvRight[$k]['rightAdv'] = $result['rightAdv'][$k];
			$AdvRight[$k]['rightAdvSort'] = $result['rightAdvSort'][$k];

			if ($result['leftBannerLink'][$k]) {
				$AdvRight[$k]['rightAdvLink'] = setRewrite($result['rightAdvLink'][$k]);
			}
			else {
				$AdvRight[$k]['rightAdvLink'] = $result['rightAdvLink'][$k];
			}

			$AdvRight[$k]['rightAdvTitle'] = $result['rightAdvTitle'][$k];
			$AdvRight[$k]['rightAdvSubtitle'] = $result['rightAdvSubtitle'][$k];
			$sort_vals[$k] = isset($result['rightAdvSort'][$k]) ? $result['rightAdvSort'][$k] : 0;
		}
	}

	if ($AdvRight) {
		array_multisort($sort_vals, SORT_ASC, $AdvRight);
	}

	$spec_attr['rightAdv'] = $AdvRight;

	if (0 < $result['cat_id']) {
		$cat_info = get_cat_info($result['cat_id'], array('cat_id', 'cat_name', 'cat_alias_name', 'style_icon'));

		if ($cat_info['cat_alias_name']) {
			$spec_attr['cat_alias_name'] = $cat_info['cat_alias_name'];
		}
		else {
			$spec_attr['cat_alias_name'] = $cat_info['cat_name'];
		}

		$spec_attr['cat_name'] = $cat_info['cat_name'];
		$spec_attr['style_icon'] = $cat_info['style_icon'];
	}

	$storeThree = 0;

	if (!empty($result['cateValue'])) {
		$cat_tow = '';
		$i = 0;

		foreach ($result['cateValue'] as $k => $v) {
			$i++;
			if ($result['moded'] == 'storeThreeFloor1' && $result['floorMode'] == 4 && $i == 1) {
				$storeThree = $v;
			}

			$arr = array();

			if (0 < $v) {
				$sql = 'SELECT cat_name,cat_id FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE cat_id = \'' . $v . '\'');
				$arr = $db->getRow($sql);
				$arr['goods_id'] = $result['cat_goods'][$k];
				$cat_tow[] = $arr;
			}
		}

		$spec_attr['cateValue'] = $cat_tow;
	}

	$brand_list = '';

	if ($result['brand_ids']) {
		$where = ' WHERE 1';
		$brandId = $result['brand_ids'];
		$where .= ' AND b.brand_id in (' . $brandId . ')';
		$sql = 'SELECT b.brand_id,b.brand_name,b.brand_logo,b.site_url FROM ' . $GLOBALS['ecs']->table('brand') . ' as b left join ' . $GLOBALS['ecs']->table('brand_extend') . ' AS be on b.brand_id=be.brand_id ' . $where;
		$brand_list = $db->getAll($sql);

		if (!empty($brand_list)) {
			foreach ($brand_list as $key => $val) {
				if ($val['site_url'] && 8 < strlen($val['site_url'])) {
					$brand_list[$key]['url'] = $val['site_url'];
				}
				else {
					$brand_list[$key]['url'] = build_uri('brandn', array('bid' => $val['brand_id']), $val['brand_name']);
				}

				$val['brand_logo'] = DATA_DIR . '/brandlogo/' . $val['brand_logo'];
				$brand_list[$key]['brand_logo'] = get_image_path($val['brand_id'], $val['brand_logo']);
			}
		}

		$smarty->assign('brand_list', $brand_list);
	}

	$advNumber = 6;

	if ($spec_attr['floorMode'] == '5') {
		$advNumber = 5;
	}
	else {
		if ($spec_attr['floorMode'] == '6' || $spec_attr['floorMode'] == '7') {
			$advNumber = 4;
		}
		else if ($spec_attr['floorMode'] == '8') {
			$advNumber = 3;
		}
	}

	if ($result['rightAdvTitle']) {
		foreach ($result['rightAdvTitle'] as $k => $v) {
			if ($v) {
				$result['rightAdvTitle'][$k] = strFilter($v);
			}
		}
	}

	if ($result['rightAdvSubtitle']) {
		foreach ($result['rightAdvSubtitle'] as $k => $v) {
			if ($v) {
				$result['rightAdvSubtitle'][$k] = strFilter($v);
			}
		}
	}

	if ($result['moded'] == 'homeFloorFour' || $result['moded'] == 'homeFloorFive' || $result['moded'] == 'homeFloorSeven' || $result['moded'] == 'homeFloorEight' || $result['moded'] == 'homeFloorTen' || $result['moded'] == 'storeOneFloor1' || $result['moded'] == 'storeTwoFloor1' || $result['moded'] == 'storeThreeFloor1' || $result['moded'] == 'storeFourFloor1' || $result['moded'] == 'storeFiveFloor1' || $result['moded'] == 'topicOneFloor' || $result['moded'] == 'topicTwoFloor' || $result['moded'] == 'topicThreeFloor' || $result['moded'] == 'homeFloorSix' && $result['floorMode'] != 1) {
		$adminru = get_admin_ru_id();
		$where_goods = 'WHERE 1 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.review_status > 2  AND g.user_id = \'' . $adminru['ru_id'] . '\'';

		if ($result['top_goods']) {
			$where_goods .= ' AND g.goods_id in (' . $result['top_goods'] . ')';
		}

		if (0 < $result['cat_id']) {
			include_once ROOT_PATH . '/includes/lib_goods.php';
			$search_cat = $result['cat_id'];
			if ($result['moded'] == 'storeThreeFloor1' && $result['floorMode'] == 4) {
				$search_cat = $storeThree;
			}

			$children = get_children($search_cat);
			$where_goods .= ' AND (' . $children . ' OR ' . get_extension_goods($children) . ')';
		}

		$limit = ' LIMIT 8';
		$goods_num = -1;

		if ($result['moded'] == 'homeFloorFour') {
			if ($result['floorMode'] == 1) {
				$goods_num = 3;
			}
			else if ($result['floorMode'] == 2) {
				$limit = ' LIMIT 10';
			}
			else if ($result['floorMode'] == 3) {
				$limit = ' LIMIT 10';
			}
			else if ($result['floorMode'] == 4) {
				$limit = ' LIMIT 12';
			}
		}
		else if ($result['moded'] == 'homeFloorSix') {
			if ($result['floorMode'] == 2) {
				$limit = ' LIMIT 4';
			}
			else if ($result['floorMode'] == 3) {
				$limit = ' LIMIT 6';
			}
		}
		else if ($result['moded'] == 'homeFloorSeven') {
			$limit = ' LIMIT 6';
		}
		else if ($result['moded'] == 'storeOneFloor1') {
			$limit = 'LIMIT 6';

			if ($result['floorMode'] == 3) {
				$limit = ' LIMIT 6';

				if ($result['top_goods'] != '') {
					$limit = '';
				}
			}
			else if ($result['floorMode'] == 4) {
				$limit = ' LIMIT 8';

				if ($result['top_goods'] != '') {
					$limit = '';
				}
			}
		}
		else if ($result['moded'] == 'storeTwoFloor1') {
			if ($result['floorMode'] == 2) {
				$limit = ' LIMIT 6';
			}
			else if ($result['floorMode'] == 3) {
				$limit = ' LIMIT 8';
			}

			if ($result['top_goods'] != '') {
				$limit = '';
			}
		}
		else if ($result['moded'] == 'storeThreeFloor1') {
			if ($result['floorMode'] == 2) {
				$limit = ' LIMIT 6';
			}
			else if ($result['floorMode'] == 3) {
				$limit = ' LIMIT 10';
			}
			else if ($result['floorMode'] == 4) {
				$limit = ' LIMIT 8';
			}
		}
		else if ($result['moded'] == 'storeFourFloor1') {
			if ($result['floorMode'] == 3) {
				$limit = ' LIMIT 6';
			}
			else if ($result['floorMode'] == 4) {
				$limit = ' LIMIT 12';
			}
		}
		else if ($result['moded'] == 'storeFiveFloor1') {
			if ($result['floorMode'] == 1) {
				$limit = ' LIMIT 6';
			}
			else {
				$limit = ' LIMIT 8';

				if ($result['top_goods'] != '') {
					$limit = '';
				}
			}
		}
		else if ($result['moded'] == 'topicOneFloor') {
			if ($result['floorMode'] == 2) {
				$limit = ' LIMIT 6';
			}
			else if ($result['floorMode'] == 3) {
				$limit = ' LIMIT 8';
			}

			if ($result['top_goods'] != '') {
				$limit = '';
			}
		}
		else if ($result['moded'] == 'topicTwoFloor') {
			$limit = ' LIMIT 10';
		}
		else if ($result['moded'] == 'topicThreeFloor') {
			if ($result['floorMode'] == 1) {
				$limit = ' LIMIT 8';
			}
			else if ($result['floorMode'] == 3) {
				$limit = ' LIMIT 10';
			}
		}

		$sql = 'SELECT g.promote_start_date, g.promote_end_date, g.promote_price, g.goods_name, g.goods_id, g.goods_thumb, g.shop_price, g.market_price, g.original_img  FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $where_goods . ' ORDER BY g.sort_order, g.goods_id DESC ' . $limit;
		$goods_list = $db->getAll($sql);

		if (!empty($goods_list)) {
			foreach ($goods_list as $key => $val) {
				if (0 < $val['promote_price'] && $val['promote_start_date'] <= $time && $time <= $val['promote_end_date']) {
					$goods_list[$key]['promote_price'] = price_format(bargain_price($val['promote_price'], $val['promote_start_date'], $val['promote_end_date']));
				}
				else {
					$goods_list[$key]['promote_price'] = '';
				}

				$goods_list[$key]['shop_price'] = price_format($val['shop_price']);
				$goods_list[$key]['goods_thumb'] = get_image_path($val['goods_id'], $val['goods_thumb']);
				$goods_list[$key]['original_img'] = get_image_path($val['goods_id'], $val['original_img']);
				if (strpos($goods_list[$key]['goods_thumb'], 'http://') === false && strpos($goods_list[$key]['goods_thumb'], 'https://') === false) {
					$goods_list[$key]['goods_thumb'] = $ecs->url() . $goods_list[$key]['goods_thumb'];
				}

				if (strpos($goods_list[$key]['original_img'], 'http://') === false && strpos($goods_list[$key]['original_img'], 'https://') === false) {
					$goods_list[$key]['original_img'] = $ecs->url() . $goods_list[$key]['original_img'];
				}

				$goods_list[$key]['url'] = build_uri('goods', array('gid' => $val['goods_id']), $val['goods_name']);
			}
		}

		$smarty->assign('goods_list', $goods_list);
		$smarty->assign('goods_num', $goods_num);
	}

	$smarty->assign('advNumber', $advNumber);
	$smarty->assign('spec_attr', $spec_attr);
	$smarty->assign('temp', $result['moded']);
	$result['spec_attr'] = $result;
	$result['content'] = $GLOBALS['smarty']->fetch('library/dialog.lbi');
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'getsearchgoodsDiv') {
	$json = new JSON();
	$result = array();
	$goods_ids = !empty($_REQUEST['goods_ids']) ? trim($_REQUEST['goods_ids']) : '';
	$pbtype = !empty($_REQUEST['pbtype']) ? trim($_REQUEST['pbtype']) : '';
	$goods_list = array();
	$back_goods = '';

	if ($goods_ids) {
		$where = 'WHERE g.is_delete=0 ';
		$where .= ' AND g.goods_id in (' . $goods_ids . ')';
		$where .= ' AND g.user_id = \'' . $adminru['ru_id'] . '\' ';

		if ($GLOBALS['_CFG']['review_goods'] == 1) {
			$where .= ' AND g.review_status > 2 ';
		}

		$sql = 'SELECT  g.goods_name, g.goods_id  FROM ' . $ecs->table('goods') . ' AS g ' . $where;
		$goods_list = $db->getAll($sql);

		if (!empty($goods_list)) {
			foreach ($goods_list as $k) {
				if ($back_goods) {
					$back_goods .= ',' . $k['goods_id'];
				}
				else {
					$back_goods .= $k['goods_id'];
				}
			}
		}
	}

	$smarty->assign('goods_list', $goods_list);
	$smarty->assign('pbtype', $pbtype);
	$result['back_goods'] = $back_goods;
	$result['content'] = $GLOBALS['smarty']->fetch('library/getsearchgoodsDiv.lbi');
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'add_seckill_goods') {
	$goods_ids = empty($_REQUEST['goods_ids']) ? '' : trim($_REQUEST['goods_ids']);
	$sec_id = empty($_REQUEST['sec_id']) ? 0 : intval($_REQUEST['sec_id']);
	$tb_id = empty($_REQUEST['tb_id']) ? 0 : intval($_REQUEST['tb_id']);
	$result = array('error' => 0, 'message' => '', 'content' => '', 'goods_ids' => '');

	if ($goods_ids) {
		$goods_ids_arr = explode(',', $goods_ids);

		foreach ($goods_ids_arr as $val) {
			$sql = 'SELECT id FROM ' . $ecs->table('seckill_goods') . (' WHERE goods_id = \'' . $val . '\' AND sec_id = \'' . $sec_id . '\' AND tb_id = \'' . $tb_id . '\'');

			if (!$db->getOne($sql)) {
				$sql = 'INSERT INTO ' . $ecs->table('seckill_goods') . ' (sec_id, tb_id, goods_id) ' . ('VALUES (\'' . $sec_id . '\',\'' . $tb_id . '\', \'' . $val . '\')');
				$db->query($sql);
			}
		}

		$list = get_add_seckill_goods($sec_id, $tb_id);
		$sql = ' SELECT GROUP_CONCAT(goods_id) FROM ' . $ecs->table('seckill_goods') . (' WHERE sec_id = \'' . $sec_id . '\' AND tb_id = \'' . $tb_id . '\' ');
		$result['goods_ids'] = $db->getOne($sql);
		$smarty->assign('seckill_goods', $list['seckill_goods']);
		$smarty->assign('filter', $list['filter']);
		$smarty->assign('record_count', $list['record_count']);
		$smarty->assign('page_count', $list['page_count']);
		$result['content'] = $smarty->fetch('templates/seckill_set_goods_info.dwt');
		exit(json_encode($result));
	}
}
else if ($_REQUEST['act'] == 'add_goods_type_cat') {
	$exc_cat = new exchange($ecs->table('goods_type_cat'), $db, 'cat_id', 'cat_name');
	$json = new JSON();
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$cat_name = !empty($_REQUEST['cat_name']) ? trim($_REQUEST['cat_name']) : '';
	$parent_id = !empty($_REQUEST['attr_parent_id']) ? intval($_REQUEST['attr_parent_id']) : 0;
	$sort_order = !empty($_REQUEST['sort_order']) ? intval($_REQUEST['sort_order']) : 50;
	$user_id = $adminru['ru_id'];

	if (0 < $parent_id) {
		$sql = 'SELECT level FROM' . $ecs->table('goods_type_cat') . (' WHERE cat_id = \'' . $parent_id . '\' LIMIT 1');
		$level = $db->getOne($sql) + 1;
	}
	else {
		$level = 1;
	}

	$cat_info = array('cat_name' => $cat_name, 'parent_id' => $parent_id, 'level' => $level, 'user_id' => $user_id, 'sort_order' => $sort_order);
	$where = ' user_id = \'' . $user_id . '\'';
	$is_only = $exc_cat->is_only('cat_name', $cat_name, 0, $where);

	if (!$is_only) {
		$result['error'] = 1;
		$result['message'] = sprintf($_LANG['exist_cat'], stripslashes($cat_name));
		exit(json_encode($result));
	}

	$db->autoExecute($ecs->table('goods_type_cat'), $cat_info, 'INSERT');
	$cat_id = $db->insert_id();
	$type_level = get_type_cat_arr(0, 0, 0, $user_id);
	$smarty->assign('type_level', $type_level);
	$cat_tree = get_type_cat_arr($cat_id, 2, 0, $user_id);
	$cat_tree1 = array('checked_id' => $cat_tree['checked_id']);

	if (0 < $cat_tree['checked_id']) {
		$cat_tree1 = get_type_cat_arr($cat_tree['checked_id'], 2, 0, $user_id);
	}

	$smarty->assign('type_c_id', $cat_id);
	$smarty->assign('cat_tree', $cat_tree);
	$smarty->assign('cat_tree1', $cat_tree1);
	$result['cat_id'] = $cat_id;
	$result['content'] = $GLOBALS['smarty']->fetch('library/type_cat_list.lbi');
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'add_goods_type') {
	$json = new JSON();
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
	$parent_id = !empty($_REQUEST['attr_parent_id']) ? intval($_REQUEST['attr_parent_id']) : 0;
	$goods_type['cat_name'] = sub_str($_POST['cat_name'], 60);
	$goods_type['attr_group'] = sub_str($_POST['attr_group'], 255);
	$goods_type['user_id'] = $adminru['ru_id'];
	$goods_type['c_id'] = $parent_id;
	$db->autoExecute($ecs->table('goods_type'), $goods_type);
	$result['type_id'] = $db->insert_id();
	$result['cat_id'] = $parent_id;
	$type_level = get_type_cat_arr(0, 0, 0, $goods_type['user_id']);
	$smarty->assign('type_level', $type_level);
	$cat_tree = get_type_cat_arr($parent_id, 2, 0, $goods_type['user_id']);
	$cat_tree1 = array('checked_id' => $cat_tree['checked_id']);

	if (0 < $cat_tree['checked_id']) {
		$cat_tree1 = get_type_cat_arr($cat_tree['checked_id'], 2, 0, $goods_type['user_id']);
	}

	$smarty->assign('type_c_id', $parent_id);
	$smarty->assign('cat_tree', $cat_tree);
	$smarty->assign('cat_tree1', $cat_tree1);
	$result['content'] = $GLOBALS['smarty']->fetch('library/type_cat_list.lbi');
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'attribute_add') {
	$json = new JSON();
	$result = array('error' => 0, 'message' => '', 'content' => '');
	$cat_id = isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
	$sort_order = isset($_POST['sort_order']) && !empty($_POST['sort_order']) ? $_POST['sort_order'] : 0;
	$exc = new exchange($ecs->table('attribute'), $db, 'attr_id', 'attr_name');
	$exclude = empty($_POST['attr_id']) ? 0 : intval($_POST['attr_id']);

	if (!$exc->is_only('attr_name', $_POST['attr_name'], $exclude, ' cat_id = \'' . $cat_id . '\'')) {
		$result['error'] = 1;
		$result['message'] = '属性名称重复';
		exit(json_encode($result));
	}

	$attr = array('cat_id' => $cat_id, 'attr_name' => $_POST['attr_name'], 'attr_cat_type' => $_POST['attr_cat_type'], 'attr_index' => $_POST['attr_index'], 'sort_order' => $sort_order, 'attr_input_type' => $_POST['attr_input_type'], 'is_linked' => $_POST['is_linked'], 'attr_values' => isset($_POST['attr_values']) ? $_POST['attr_values'] : '', 'attr_type' => empty($_POST['attr_type']) ? '0' : intval($_POST['attr_type']), 'attr_group' => isset($_POST['attr_group']) ? intval($_POST['attr_group']) : 0);
	$db->autoExecute($ecs->table('attribute'), $attr, 'INSERT');
	$attr_id = $db->insert_id();
	$sql = 'SELECT MAX(sort_order) AS sort FROM ' . $ecs->table('attribute') . (' WHERE cat_id = \'' . $cat_id . '\'');
	$sort = $db->getOne($sql);
	if (empty($attr['sort_order']) && !empty($sort)) {
		$attr = array('sort_order' => $attr_id);
		$db->autoExecute($ecs->table('attribute'), $attr, 'UPDATE', 'attr_id = \'' . $attr_id . '\'');
	}

	$result['type_id'] = $cat_id;
	admin_log($_POST['attr_name'], 'add', 'attribute');
	exit(json_encode($result));
}

?>
