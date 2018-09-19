<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function read_modules($directory = '.')
{
	global $_LANG;
	$dir = @opendir($directory);
	$set_modules = true;
	$modules = array();

	while (false !== ($file = @readdir($dir))) {
		if (preg_match('/^.*?\\.php$/', $file)) {
			include_once $directory . '/' . $file;
		}
	}

	@closedir($dir);
	unset($set_modules);

	foreach ($modules as $key => $value) {
		ksort($modules[$key]);
	}

	ksort($modules);
	return $modules;
}

function sys_msg($msg_detail, $msg_type = 0, $links = array(), $auto_redirect = true)
{
	if (count($links) == 0) {
		$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
		$links[0]['href'] = 'javascript:history.go(-1)';
	}

	assign_query_info();
	$GLOBALS['smarty']->assign('ur_here', $GLOBALS['_LANG']['system_message']);
	$GLOBALS['smarty']->assign('msg_detail', $msg_detail);
	$GLOBALS['smarty']->assign('msg_type', $msg_type);
	$GLOBALS['smarty']->assign('links', $links);
	$GLOBALS['smarty']->assign('default_url', $links[0]['href']);
	$GLOBALS['smarty']->assign('auto_redirect', $auto_redirect);
	$GLOBALS['smarty']->display('message.dwt');
	exit();
}

function admin_log($sn = '', $action, $content)
{
	$log_info = $GLOBALS['_LANG']['log_action'][$action] . $GLOBALS['_LANG']['log_action'][$content] . ': ' . addslashes($sn);
	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('admin_log') . ' (log_time, user_id, log_info, ip_address) ' . ' VALUES (\'' . gmtime() . '\', ' . $_SESSION['seller_id'] . ', \'' . stripslashes($log_info) . '\', \'' . real_ip() . '\')';
	$GLOBALS['db']->query($sql);
}

function sys_joindate($prefix)
{
	$year = (empty($_POST[$prefix . 'Year']) ? '0' : $_POST[$prefix . 'Year']);
	$month = (empty($_POST[$prefix . 'Month']) ? '0' : $_POST[$prefix . 'Month']);
	$day = (empty($_POST[$prefix . 'Day']) ? '0' : $_POST[$prefix . 'Day']);
	return $year . '-' . $month . '-' . $day;
}

function set_admin_session($user_id, $username, $store_id)
{
	$_SESSION['store_user_id'] = $user_id;
	$_SESSION['stores_id'] = $store_id;
	$_SESSION['stores_name'] = $username;
}

function insert_config($parent, $code, $value)
{
	global $ecs;
	global $db;
	global $_LANG;
	$sql = 'SELECT id FROM ' . $ecs->table('shop_config') . ' WHERE code = \'' . $parent . '\' AND type = 1';
	$parent_id = $db->getOne($sql);
	$sql = 'INSERT INTO ' . $ecs->table('shop_config') . ' (parent_id, code, value) ' . 'VALUES(\'' . $parent_id . '\', \'' . $code . '\', \'' . $value . '\')';
	$db->query($sql);
}

function admin_priv($priv_str, $msg_type = '', $msg_output = true)
{
	global $_LANG;

	if (!isset($_SESSION['seller_action_list'])) {
		$admin_id = get_admin_id();
		$sql = 'SELECT action_list ' . ' FROM ' . $GLOBALS['ecs']->table('admin_user') . ' WHERE user_id = \'' . $admin_id . '\'';
		$action_list = $GLOBALS['db']->getOne($sql, true);
		$_SESSION['seller_action_list'] = $action_list;
	}
	else {
		$action_list = $_SESSION['seller_action_list'];
	}

	if ($action_list == 'all') {
		return true;
	}

	if (strpos(',' . $action_list . ',', ',' . $priv_str . ',') === false) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');

		if ($msg_output) {
			sys_msg($_LANG['priv_error'], 0, $link);
		}

		return false;
	}
	else {
		return true;
	}
}

function check_authz($authz)
{
	return preg_match('/,*' . $authz . ',*/', $_SESSION['seller_action_list']) || ($_SESSION['seller_action_list'] == 'all');
}

function check_authz_json($authz)
{
	if (!check_authz($authz)) {
		make_json_error($GLOBALS['_LANG']['priv_error']);
	}
}

function get_bonus_type()
{
	$bonus = array();
	$sql = 'SELECT type_id, type_name, type_money FROM ' . $GLOBALS['ecs']->table('bonus_type') . ' WHERE send_type = 3';
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$bonus[$row['type_id']] = $row['type_name'] . ' [' . sprintf($GLOBALS['_CFG']['currency_format'], $row['type_money']) . ']';
	}

	return $bonus;
}

function get_rank_list($is_special = false)
{
	$rank_list = array();
	$sql = 'SELECT rank_id, rank_name, min_points FROM ' . $GLOBALS['ecs']->table('user_rank');

	if ($is_special) {
		$sql .= ' WHERE special_rank = 1';
	}

	$sql .= ' ORDER BY min_points';
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$rank_list[$row['rank_id']] = $row['rank_name'];
	}

	return $rank_list;
}

function get_user_rank($rankid, $where)
{
	$user_list = array();
	$sql = 'SELECT user_id, user_name FROM ' . $GLOBALS['ecs']->table('users') . $where . ' ORDER BY user_id DESC';
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$user_list[$row['user_id']] = $row['user_name'];
	}

	return $user_list;
}

function get_cfg_val($arr = array())
{
	$new_arr = array();

	if ($arr) {
		foreach ($arr as $row) {
			array_push($new_arr, $row['code'] . '**' . $row['value']);
		}

		$new_arr2 = array();

		foreach ($new_arr as $key => $rows) {
			$rows = explode('**', $rows);
			$new_arr2[$rows[0]] = $rows[1];
		}

		$new_arr = $new_arr2;
	}

	return $new_arr;
}

function get_position_list()
{
	$adminru = get_admin_ru_id();
	$ruCat = '';

	if (0 < $adminru['ru_id']) {
		$ruCat = ' where (user_id = \'' . $adminru['ru_id'] . '\' or is_public = 1) ';
	}

	$position_list = array();
	$sql = 'SELECT position_id, position_name, ad_width, ad_height ' . 'FROM ' . $GLOBALS['ecs']->table('ad_position') . $ruCat;
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$position_list[$row['position_id']] = addslashes($row['position_name']) . ' [' . $row['ad_width'] . 'x' . $row['ad_height'] . ']';
	}

	return $position_list;
}

function create_html_editor($input_name, $input_value = '')
{
	global $_CFG;
	global $smarty;

	if ($_CFG['editing_tools'] == 'fckeditor') {
		$editor = new FCKeditor($input_name);
		$editor->BasePath = '../includes/fckeditor/';
		$editor->ToolbarSet = 'Normal';
		$editor->Width = '100%';
		$editor->Height = '320';
		$editor->Value = $input_value;
		$FCKeditor = $editor->CreateHtml();
	}
	else {
		$input_height = ($_CFG['editing_tools'] == 'ueditor' ? 586 : 500);
		$FCKeditor = '<input type="hidden" id="' . $input_name . '" name="' . $input_name . '" value="' . htmlspecialchars($input_value) . '" /><iframe id="' . $input_name . '_frame" src="../plugins/' . $_CFG['editing_tools'] . '/ecmobanEditor.php?item=' . $input_name . '" width="100%" height="' . $input_height . '" frameborder="0" scrolling="no"></iframe>';
	}

	$smarty->assign('FCKeditor', $FCKeditor);
}

function create_html_editor2($input_name, $output_name, $input_value = '')
{
	global $_CFG;
	global $smarty;

	if ($_CFG['editing_tools'] == 'fckeditor') {
		$editor = new FCKeditor($input_name);
		$editor->BasePath = '../includes/fckeditor/';
		$editor->ToolbarSet = 'Normal';
		$editor->Width = '100%';
		$editor->Height = '320';
		$editor->Value = $input_value;
		$FCKeditor = $editor->CreateHtml();
	}
	else {
		$input_height = ($_CFG['editing_tools'] == 'ueditor' ? 586 : 500);
		$FCKeditor = '<input type="hidden" id="' . $input_name . '" name="' . $input_name . '" value="' . htmlspecialchars($input_value) . '" /><iframe id="' . $input_name . '_frame" src="../plugins/' . $_CFG['editing_tools'] . '/ecmobanEditor.php?item=' . $input_name . '" width="100%" height="' . $input_height . '" frameborder="0" scrolling="no"></iframe>';
	}

	$smarty->assign($output_name, $FCKeditor);
}

function get_goods_list($filter)
{
	$filter->keyword = json_str_iconv($filter->keyword);
	$where = get_where_sql($filter);
	$sql = 'SELECT goods_id, goods_name, shop_price ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $where;
	$row = $GLOBALS['db']->getAll($sql);
	return $row;
}

function get_article_list($filter)
{
	$ol = new OptionList();
	$where = ' WHERE a.cat_id = c.cat_id AND c.cat_type = 1 ';
	$where .= (isset($filter->title) ? ' AND a.title LIKE \'%' . mysql_like_quote($filter->title) . '%\'' : '');
	$sql = 'SELECT a.article_id, a.title ' . 'FROM ' . $GLOBALS['ecs']->table('article') . ' AS a, ' . $GLOBALS['ecs']->table('article_cat') . ' AS c ' . $where;
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$ol->add_option($row['article_id'], $row['title']);
	}

	$ol->build_select();
}

function get_yes_no($var)
{
	return empty($var) ? '<img src="images/no.gif" border="0" />' : '<img src="images/yes.gif" border="0" />';
}

function get_where_sql($filter)
{
	$adminru = get_admin_ru_id();
	$time = date('Y-m-d');
	$where = (isset($filter->is_delete) && ($filter->is_delete == '1') ? ' WHERE is_delete = 1 ' : ' WHERE is_delete = 0 ');
	$where .= (isset($filter->real_goods) && (-1 < $filter->real_goods) ? ' AND is_real = ' . intval($filter->real_goods) : '');
	$where .= (isset($filter->cat_id) && (0 < $filter->cat_id) ? ' AND ' . get_children($filter->cat_id) : '');
	$brand_keyword = $filter->brand_keyword;
	$sel_mode = $filter->sel_mode;

	if ($filter->brand_keyword) {
		if (($sel_mode == 1) && !empty($brand_keyword)) {
			$new_array = array();
			$sql = 'SELECT brand_id FROM ' . $GLOBALS['ecs']->table('brand') . ' WHERE brand_name LIKE \'%' . $brand_keyword . '%\' ';
			$brand_id = $GLOBALS['db']->getAll($sql);

			foreach ($brand_id as $key => $value) {
				$new_array[] = $value['brand_id'];
			}

			$where .= (isset($filter->brand_keyword) && (trim($filter->brand_keyword) != '') ? ' AND brand_id ' . db_create_in($new_array) . '' : '');
		}
		else {
			if (($sel_mode == 1) && !empty($brand_keyword)) {
				$filter->brand_id = 0;
			}
		}
	}
	else {
		$where .= (isset($filter->brand_id) && (0 < $filter->brand_id) ? ' AND brand_id = \'' . $filter->brand_id . '\'' : '');
	}

	$where .= (isset($filter->intro_type) && ($filter->intro_type != '0') ? ' AND ' . $filter->intro_type . ' = \'1\'' : '');
	$where .= (isset($filter->intro_type) && ($filter->intro_type == 'is_promote') ? ' AND promote_start_date <= \'' . $time . '\' AND promote_end_date >= \'' . $time . '\' ' : '');
	$where .= (isset($filter->keyword) && (trim($filter->keyword) != '') ? ' AND (goods_name LIKE \'%' . mysql_like_quote($filter->keyword) . '%\' OR goods_sn LIKE \'%' . mysql_like_quote($filter->keyword) . '%\' OR goods_id LIKE \'%' . mysql_like_quote($filter->keyword) . '%\') ' : '');
	$where .= (isset($filter->suppliers_id) && (trim($filter->suppliers_id) != '') ? ' AND (suppliers_id = \'' . $filter->suppliers_id . '\') ' : '');
	$where .= (isset($filter->in_ids) ? ' AND goods_id ' . db_create_in($filter->in_ids) : '');
	$where .= (isset($filter->exclude) ? ' AND goods_id NOT ' . db_create_in($filter->exclude) : '');
	$where .= (isset($filter->stock_warning) ? ' AND goods_number <= warn_number' : '');
	$where .= (isset($filter->presale) ? ' AND is_on_sale = 0 ' : '');

	if (isset($filter->ru_id)) {
		$where .= ' AND user_id = \'' . $filter->ru_id . '\'';
	}
	else if (0 < $adminru['ru_id']) {
		$where .= ' AND user_id = \'' . $adminru['ru_id'] . '\'';
	}

	return $where;
}

function get_where_sql_unpre($filter)
{
	$time = date('Y-m-d');
	$where = (isset($filter->is_delete) && ($filter->is_delete == '1') ? ' WHERE g.is_delete = 1 ' : ' WHERE g.is_delete = 0 ');
	$where .= (isset($filter->real_goods) && (-1 < $filter->real_goods) ? ' AND g.is_real = ' . intval($filter->real_goods) : '');
	$where .= (isset($filter->cat_id) && (0 < $filter->cat_id) ? ' AND ' . get_children($filter->cat_id) : '');
	$where .= (isset($filter->brand_id) && (0 < $filter->brand_id) ? ' AND b.brand_id = \'' . $filter->brand_id . '\'' : '');
	$where .= (isset($filter->intro_type) && ($filter->intro_type != '0') ? ' AND ' . $filter->intro_type . ' = \'1\'' : '');
	$where .= (isset($filter->intro_type) && ($filter->intro_type == 'g.is_promote') ? ' AND g.promote_start_date <= \'' . $time . '\' AND g.promote_end_date >= \'' . $time . '\' ' : '');
	$where .= (isset($filter->keyword) && (trim($filter->keyword) != '') ? ' AND (g.goods_name LIKE \'%' . mysql_like_quote($filter->keyword) . '%\' OR g.goods_sn LIKE \'%' . mysql_like_quote($filter->keyword) . '%\' OR g.goods_id LIKE \'%' . mysql_like_quote($filter->keyword) . '%\') ' : '');
	$where .= (isset($filter->suppliers_id) && (trim($filter->suppliers_id) != '') ? ' AND (g.suppliers_id = \'' . $filter->suppliers_id . '\') ' : '');
	$where .= (isset($filter->in_ids) ? ' AND g.goods_id ' . db_create_in($filter->in_ids) : '');
	$where .= (isset($filter->exclude) ? ' AND g.goods_id NOT ' . db_create_in($filter->exclude) : '');
	$where .= (isset($filter->stock_warning) ? ' AND g.goods_number <= warn_number' : '');
	return $where;
}

function area_list($region_id = 0)
{
	$area_arr = array();
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE parent_id = \'' . $region_id . '\' ORDER BY region_id';
	$res = $GLOBALS['db']->query($sql);
	$idx = 0;

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$row['type'] = $row['region_type'] == 0 ? $GLOBALS['_LANG']['country'] : '';
		$row['type'] .= ($row['region_type'] == 1 ? $GLOBALS['_LANG']['province'] : '');
		$row['type'] .= ($row['region_type'] == 2 ? $GLOBALS['_LANG']['city'] : '');
		$row['type'] .= ($row['region_type'] == 3 ? $GLOBALS['_LANG']['cantonal'] : '');
		$area_arr[$idx] = $row;
		$idx++;
	}

	return $area_arr;
}

function chart_color($n)
{
	$arr = array('33FF66', 'FF6600', '3399FF', '009966', 'CC3399', 'FFCC33', '6699CC', 'CC3366', '33FF66', 'FF6600', '3399FF');

	if (8 < $n) {
		$n = $n % 8;
	}

	return $arr[$n];
}

function goods_type_list($selected, $goods_id = 0)
{
	$adminru = get_admin_ru_id();
	$ruCat = '';

	if (0 < $goods_id) {
		$sql = 'select user_id from ' . $GLOBALS['ecs']->table('goods') . ' where goods_id = \'' . $goods_id . '\'';
		$user_id = $GLOBALS['db']->getOne($sql);

		if ($GLOBALS['_CFG']['attr_set_up'] == 0) {
			if (0 < $adminru['ru_id']) {
				$ruCat = ' and user_id = 0';
			}
		}
		else if ($GLOBALS['_CFG']['attr_set_up'] == 1) {
			$ruCat = ' and user_id = \'' . $user_id . '\'';
		}
	}
	else if ($GLOBALS['_CFG']['attr_set_up'] == 0) {
		if (0 < $adminru['ru_id']) {
			$ruCat = ' and user_id = 0';
		}
	}
	else if ($GLOBALS['_CFG']['attr_set_up'] == 1) {
		if (0 < $adminru['ru_id']) {
			$ruCat = ' and user_id = \'' . $adminru['ru_id'] . '\'';
		}
	}

	$sql = 'SELECT cat_id, cat_name FROM ' . $GLOBALS['ecs']->table('goods_type') . ' WHERE enabled = 1' . $ruCat;
	$res = $GLOBALS['db']->query($sql);
	$lst = '';

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$lst .= '<option value=\'' . $row['cat_id'] . '\'';
		$lst .= ($selected == $row['cat_id'] ? ' selected="true"' : '');
		$lst .= '>' . htmlspecialchars($row['cat_name']) . '</option>';
	}

	return $lst;
}

function get_pay_ids()
{
	$ids = array('is_cod' => '0', 'is_not_cod' => '0');
	$sql = 'SELECT pay_id, is_cod FROM ' . $GLOBALS['ecs']->table('payment') . ' WHERE enabled = 1';
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if ($row['is_cod']) {
			$ids['is_cod'] .= ',' . $row['pay_id'];
		}
		else {
			$ids['is_not_cod'] .= ',' . $row['pay_id'];
		}
	}

	return $ids;
}

function truncate_table($table_name)
{
	$sql = 'TRUNCATE TABLE ' . $GLOBALS['ecs']->table($table_name);
	return $GLOBALS['db']->query($sql);
}

function get_charset_list()
{
	return array('UTF8' => 'UTF-8', 'GB2312' => 'GB2312/GBK', 'BIG5' => 'BIG5');
}

function make_json_response($content = '', $error = 0, $message = '', $append = array())
{
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$res = array('error' => $error, 'message' => $message, 'content' => $content);

	if (!empty($append)) {
		foreach ($append as $key => $val) {
			$res[$key] = $val;
		}
	}

	$val = $json->encode($res);
	exit($val);
}

function make_json_result($content, $message = '', $append = array())
{
	make_json_response($content, 0, $message, $append);
}

function make_json_result_too($content, $error = 0, $message = '', $append = array())
{
	make_json_response($content, $error, $message, $append);
}

function make_json_error($msg)
{
	make_json_response('', 1, $msg);
}

function sort_flag($filter)
{
	$flag['tag'] = 'sort_' . preg_replace('/^.*\\./', '', $filter['sort_by']);
	$flag['img'] = '<img src="images/' . ($filter['sort_order'] == 'DESC' ? 'sort_desc.gif' : 'sort_asc.gif') . '"/>';
	return $flag;
}

function page_and_size($filter)
{
	if (isset($_REQUEST['page_size']) && (0 < intval($_REQUEST['page_size']))) {
		$filter['page_size'] = intval($_REQUEST['page_size']);
	}
	else {
		if (isset($_COOKIE['ECSCP']['page_size']) && (0 < intval($_COOKIE['ECSCP']['page_size']))) {
			$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
		}
		else {
			$filter['page_size'] = 15;
		}
	}

	$filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);
	$filter['page_count'] = !empty($filter['record_count']) && (0 < $filter['record_count']) ? ceil($filter['record_count'] / $filter['page_size']) : 1;

	if ($filter['page_count'] < $filter['page']) {
		$filter['page'] = $filter['page_count'];
	}

	$filter['start'] = ($filter['page'] - 1) * $filter['page_size'];
	return $filter;
}

function return_bytes($val)
{
	$val = trim($val);
	$last = strtolower($val[strlen($val) - 1]);

	switch ($last) {
	case 'g':
		$val *= 1024;
	case 'm':
		$val *= 1024;
	case 'k':
		$val *= 1024;
	}

	return $val;
}

function get_attr_groups($cat_id)
{
	$sql = 'SELECT attr_group FROM ' . $GLOBALS['ecs']->table('goods_type') . ' WHERE cat_id=\'' . $cat_id . '\'';
	$grp = str_replace("\r", '', $GLOBALS['db']->getOne($sql));

	if ($grp) {
		return explode("\n", $grp);
	}
	else {
		return array();
	}
}

function list_link_postfix()
{
	return 'uselastfilter=1';
}

function set_filter($filter, $sql, $param_str = '')
{
	$filterfile = basename(PHP_SELF, '.php');

	if ($param_str) {
		$filterfile .= $param_str;
	}

	setcookie('ECSCP[lastfilterfile]', sprintf('%X', crc32($filterfile)), time() + 600);
	setcookie('ECSCP[lastfilter]', urlencode(serialize($filter)), time() + 600);
	setcookie('ECSCP[lastfiltersql]', base64_encode($sql), time() + 600);
}

function get_filter($param_str = '')
{
	$filterfile = basename(PHP_SELF, '.php');

	if ($param_str) {
		$filterfile .= $param_str;
	}

	if (isset($_GET['uselastfilter']) && isset($_COOKIE['ECSCP']['lastfilterfile']) && ($_COOKIE['ECSCP']['lastfilterfile'] == sprintf('%X', crc32($filterfile)))) {
		return array('filter' => unserialize(urldecode($_COOKIE['ECSCP']['lastfilter'])), 'sql' => base64_decode($_COOKIE['ECSCP']['lastfiltersql']));
	}
	else {
		return false;
	}
}

function sanitize_url($url)
{
	if ($url && (strpos($url, 'http://') === false) && (strpos($url, 'https://') === false)) {
		$url = $GLOBALS['ecs']->http() . $url;
	}

	return $url;
}

function cat_exists($cat_name, $parent_cat, $exclude = 0, $ru_id = 0)
{
	if (0 < $ru_id) {
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('merchants_category') . ' WHERE parent_id = \'' . $parent_cat . '\' AND cat_name = \'' . $cat_name . '\' AND  cat_id <> \'' . $exclude . '\' AND user_id = \'' . $ru_id . '\'';
		return 0 < $GLOBALS['db']->getOne($sql) ? true : false;
	}
	else {
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE parent_id = \'' . $parent_cat . '\' AND cat_name = \'' . $cat_name . '\' AND cat_id<>\'' . $exclude . '\'';
		return 0 < $GLOBALS['db']->getOne($sql) ? true : false;
	}
}

function brand_exists($brand_name)
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('brand') . ' WHERE brand_name = \'' . $brand_name . '\'';
	return 0 < $GLOBALS['db']->getOne($sql) ? true : false;
}

function admin_info()
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('admin_user') . "\r\n            WHERE user_id = '" . $_SESSION['seller_id'] . "'\r\n            LIMIT 0, 1";
	$admin_info = $GLOBALS['db']->getRow($sql);

	if (empty($admin_info)) {
		return $admin_info = array();
	}

	return $admin_info;
}

function suppliers_list_info($conditions = '')
{
	$where = '';

	if (!empty($conditions)) {
		$where .= 'WHERE ';
		$where .= $conditions;
	}

	$sql = "SELECT suppliers_id, suppliers_name, suppliers_desc\r\n            FROM " . $GLOBALS['ecs']->table('suppliers') . "\r\n            " . $where;
	return $GLOBALS['db']->getAll($sql);
}

function suppliers_list_name()
{
	$suppliers_list = suppliers_list_info(' is_check = 1 ');
	$suppliers_name = array();

	if (0 < count($suppliers_list)) {
		foreach ($suppliers_list as $suppliers) {
			$suppliers_name[$suppliers['suppliers_id']] = $suppliers['suppliers_name'];
		}
	}

	return $suppliers_name;
}

function get_upload_pic($fname)
{
	$ret = '';
	if (empty($_FILES[$fname]['error']) || (!isset($_FILES[$fname]['error']) && isset($_FILES[$fname]['tmp_name']) && ($_FILES[$fname]['tmp_name'] != 'none'))) {
		if (!check_file_type($_FILES[$fname]['tmp_name'], $_FILES[$fname]['name'], $GLOBALS['allow_file_types'])) {
			sys_msg('无效的文件类型');
		}

		$res = upload_teacher_img($_FILES[$fname]);

		if ($res != false) {
			$ret = $res;
		}
	}

	return $ret;
}

function upload_teacher_img($upload)
{
	$img_dir = '/goods_attr_img';

	if (!make_dir(ROOT_PATH . DATA_DIR . $img_dir)) {
		return false;
	}

	$filename = $GLOBALS['image']->random_filename() . substr($upload['name'], strpos($upload['name'], '.'));
	$path = ROOT_PATH . DATA_DIR . $img_dir . '/' . $filename;

	if (move_upload_file($upload['tmp_name'], $path)) {
		return DATA_DIR . $img_dir . '/' . $filename;
	}
	else {
		return false;
	}
}

function get_add_attr_values($attr_id, $type = 0, $list = array())
{
	$sql = 'select attr_values from ' . $GLOBALS['ecs']->table('attribute') . ' where attr_id = \'' . $attr_id . '\'';
	$attr_values = $GLOBALS['db']->getOne($sql);

	if (!empty($attr_values)) {
		$attr_values = preg_replace("/\r\n/", ',', $attr_values);
		$attr_values = explode(',', $attr_values);
		$arr = array();

		for ($i = 0; $i < count($attr_values); $i++) {
			$sql = 'select attr_img, attr_site from ' . $GLOBALS['ecs']->table('attribute_img') . ' where attr_id = \'' . $attr_id . '\' and attr_values = \'' . $attr_values[$i] . '\'';
			$res = $GLOBALS['db']->getRow($sql);
			$arr[$i]['values'] = $attr_values[$i];
			$arr[$i]['attr_img'] = $res['attr_img'];
			$arr[$i]['attr_site'] = $res['attr_site'];

			if ($type == 1) {
				if ($list) {
					foreach ($list as $lk => $row) {
						if ($attr_values[$i] == $row[0]) {
							$arr[$i]['color'] = !empty($row[1]) ? $row[1] : '';
						}
					}
				}
			}
		}

		return $arr;
	}
	else {
		return array();
	}
}

function get_attrimg_insert_update($attr_id, $attr_values)
{
	include_once ROOT_PATH . '/includes/cls_image.php';
	$image = new cls_image($_CFG['bgcolor']);

	if (0 < count($attr_values)) {
		for ($i = 0; $i < count($attr_values); $i++) {
			$upload = $_FILES['attr_img_' . $i];
			$attr_site = trim($_POST['attr_site_' . $i]);
			$upFile = $image->upload_image($upload, 'septs_Image/attr_img_' . $attr_id);
			$upFile = (!empty($upFile) ? $upFile : '');
			$sql = 'select id, attr_img from ' . $GLOBALS['ecs']->table('attribute_img') . ' where attr_id = \'' . $attr_id . '\' and attr_values = \'' . $attr_values[$i]['values'] . '\'';
			$res = $GLOBALS['db']->getRow($sql);
			$drop_img = 0;

			if (empty($upFile)) {
				$upFile = $res['attr_img'];
			}

			$other = array('attr_id' => $attr_id, 'attr_values' => $attr_values[$i]['values'], 'attr_img' => $upFile, 'attr_site' => $attr_site);

			if (!empty($upFile)) {
				if (0 < $res['id']) {
					if ($upFile != $res['attr_img']) {
						@unlink(ROOT_PATH . $res['attr_img']);
					}

					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('attribute_img'), $other, 'UPDATE', 'attr_id = \'' . $attr_id . '\' and attr_values = \'' . $attr_values[$i]['values'] . '\'');
				}
				else {
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('attribute_img'), $other, 'INSERT');
				}
			}
		}
	}
}

function get_add_edit_link_desc($linked_array, $type = 0, $id = 0)
{
	if ($linked_array) {
		$arr['goods_id'] = '';

		for ($i = 0; $i < count($linked_array); $i++) {
			$arr['goods_id'] .= $linked_array[$i] . ',';
		}

		if (0 < $id) {
			$sql = 'select goods_id from ' . $GLOBALS['ecs']->table('link_goods_desc') . ' where id = \'' . $id . '\'';
			$desc_goods_id = $GLOBALS['db']->getOne($sql);
		}

		$arr['goods_id'] = substr($arr['goods_id'], 0, -1);
		$other['goods_id'] = $arr['goods_id'];
		if (!empty($desc_goods_id) && ($type != 1)) {
			$other['goods_id'] = $other['goods_id'] . ',' . $desc_goods_id;
			$other['goods_id'] = explode(',', $other['goods_id']);
			$other['goods_id'] = array_unique($other['goods_id']);
			$other['goods_id'] = implode(',', $other['goods_id']);
		}

		$sql = 'select goods_id from ' . $GLOBALS['ecs']->table('link_desc_temporary') . ' where 1 limit 0, 1';
		$tgoods = $GLOBALS['db']->getOne($sql);

		if ($type == 1) {
			if (!empty($tgoods)) {
				$other['goods_id'] = get_del_in_val($tgoods, $other['goods_id']);
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('link_desc_temporary'), $other, 'UPDATE', '1');
			}
			else {
				$other['goods_id'] = get_del_in_val($desc_goods_id, $other['goods_id']);
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('link_desc_temporary'), $other, 'INSERT');
			}
		}
		else if (!empty($tgoods)) {
			$other['goods_id'] .= ',' . $tgoods;
			$other['goods_id'] = get_other_goods_id($other['goods_id']);
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('link_desc_temporary'), $other, 'UPDATE', '1');
		}
		else {
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('link_desc_temporary'), $other, 'INSERT');
		}
	}
}

function get_other_goods_id($goods_id)
{
	$goods_id = explode(',', $goods_id);
	$goods_id = array_unique($goods_id);
	$goods_id = implode(',', $goods_id);
	return $goods_id;
}

function get_linked_goods_desc($id = 0)
{
	if (0 < $id) {
		$table = 'link_goods_desc';
		$where = ' where id = ' . $id;
	}
	else {
		$table = 'link_desc_temporary';
		$where = ' where 1';
	}

	$sql = 'select goods_id from ' . $GLOBALS['ecs']->table($table) . $where;
	$goods_id = $GLOBALS['db']->getOne($sql);
	$arr = array();

	if (!empty($goods_id)) {
		$goods_id = explode(',', $goods_id);

		for ($i = 0; $i < count($goods_id); $i++) {
			$sql = 'select goods_name from ' . $GLOBALS['ecs']->table('goods') . ' where goods_id = \'' . $goods_id[$i] . '\'';
			$goods_name = $GLOBALS['db']->getOne($sql);
			$arr[$i]['goods_id'] = $goods_id[$i];
			$arr[$i]['goods_name'] = $goods_name;
		}
	}

	return $arr;
}

function get_add_desc_goodsId($goods_id, $id)
{
	if (!empty($goods_id)) {
		$goods_id = explode(',', $goods_id);

		for ($i = 0; $i < count($goods_id); $i++) {
			$other = array('goods_id' => $goods_id[$i], 'd_id' => $id);
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('link_desc_goodsid'), $other, 'INSERT');
		}
	}
}

function get_main_order_nodisplay($order_list)
{
	if ($order_list['orders']) {
		$arr = array();

		foreach ($order_list['orders'] as $key => $row) {
			$arr[$key] = $row;

			if (0 < $arr[$key]['order_child']) {
				unset($arr[$key]);
			}
		}

		$order_list['orders'] = $arr;
	}

	return $order_list;
}

function get_bacth_category($cat_name, $cat, $ru_id)
{
	for ($i = 0; $i < count($cat_name); $i++) {
		if (!empty($cat_name)) {
			$cat['cat_name'] = $cat_name[$i];

			if ($GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('category'), $cat) !== false) {
				$cat_id = $GLOBALS['db']->insert_id();

				if ($cat['show_in_nav'] == 1) {
					$vieworder = $GLOBALS['db']->getOne('SELECT max(vieworder) FROM ' . $GLOBALS['ecs']->table('nav') . ' WHERE type = \'middle\'');
					$vieworder += 2;
					$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('nav') . ' (name,ctype,cid,ifshow,vieworder,opennew,url,type)' . ' VALUES(\'' . $cat['cat_name'] . '\', \'c\', \'' . $cat_id . '\',\'1\',\'' . $vieworder . '\',\'0\', \'' . build_uri('category', array('cid' => $cat_id), $cat['cat_name']) . '\',\'middle\')';
					$GLOBALS['db']->query($sql);
				}

				insert_cat_recommend($cat['cat_recommend'], $cat_id);

				if (0 < $ru_id) {
					$parent = array('cat_id' => $cat_id, 'user_id' => $ru_id, 'is_show' => $cat['is_show_merchants'], 'add_titme' => gmtime());
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_category'), $parent, 'INSERT');
				}

				admin_log($cat['cat_name'], 'add', 'category');
			}
		}
	}
}

function cause_exists($cause_name, $c_id = 0)
{
	$where = (!empty($c_id) ? ' AND cause_id <> \'' . $c_id . '\'' : '');
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('return_cause') . ' WHERE cause_name = \'' . $cause_name . '\'' . $where;
	return 0 < $GLOBALS['db']->getOne($sql) ? true : false;
}

function return_order_list()
{
	$result = get_filter();
	$adminru = get_admin_ru_id();

	if ($result === false) {
		$filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
		if (!empty($_GET['is_ajax']) && ($_GET['is_ajax'] == 1)) {
			$_REQUEST['consignee'] = json_str_iconv($_REQUEST['consignee']);
		}

		$filter['return_sn'] = isset($_REQUEST['return_sn']) ? trim($_REQUEST['return_sn']) : '';
		$filter['order_id'] = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;
		$filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);
		$filter['email'] = empty($_REQUEST['email']) ? '' : trim($_REQUEST['email']);
		$filter['address'] = empty($_REQUEST['address']) ? '' : trim($_REQUEST['address']);
		$filter['zipcode'] = empty($_REQUEST['zipcode']) ? '' : trim($_REQUEST['zipcode']);
		$filter['tel'] = empty($_REQUEST['tel']) ? '' : trim($_REQUEST['tel']);
		$filter['mobile'] = empty($_REQUEST['mobile']) ? 0 : intval($_REQUEST['mobile']);
		$filter['country'] = empty($_REQUEST['country']) ? 0 : intval($_REQUEST['country']);
		$filter['province'] = empty($_REQUEST['province']) ? 0 : intval($_REQUEST['province']);
		$filter['city'] = empty($_REQUEST['city']) ? 0 : intval($_REQUEST['city']);
		$filter['district'] = empty($_REQUEST['district']) ? 0 : intval($_REQUEST['district']);
		$filter['shipping_id'] = empty($_REQUEST['shipping_id']) ? 0 : intval($_REQUEST['shipping_id']);
		$filter['pay_id'] = empty($_REQUEST['pay_id']) ? 0 : intval($_REQUEST['pay_id']);
		$filter['order_status'] = isset($_REQUEST['order_status']) ? intval($_REQUEST['order_status']) : -1;
		$filter['shipping_status'] = isset($_REQUEST['shipping_status']) ? intval($_REQUEST['shipping_status']) : -1;
		$filter['pay_status'] = isset($_REQUEST['pay_status']) ? intval($_REQUEST['pay_status']) : -1;
		$filter['user_id'] = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
		$filter['user_name'] = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);
		$filter['composite_status'] = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : -1;
		$filter['group_buy_id'] = isset($_REQUEST['group_buy_id']) ? intval($_REQUEST['group_buy_id']) : 0;
		$filter['return_type'] = isset($_REQUEST['return_type']) ? intval($_REQUEST['return_type']) : -1;
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'ret_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (0 < strpos($_REQUEST['start_time'], '-') ? local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
		$filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (0 < strpos($_REQUEST['end_time'], '-') ? local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);
		$where = 'WHERE 1 ';

		if (0 < $adminru['ru_id']) {
			$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og' . ' WHERE og.order_id = o.order_id limit 0, 1) = \'' . $adminru['ru_id'] . '\' ';
			$no_main_order = ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
		}

		if ($filter['order_id']) {
			$where .= ' AND o.order_id = \'' . $filter['order_id'] . '\'';
		}

		if ($filter['return_sn']) {
			$where .= ' AND r.return_sn LIKE \'%' . mysql_like_quote($filter['return_sn']) . '%\'';
		}

		if ($filter['order_sn']) {
			$where .= ' AND o.order_sn LIKE \'%' . mysql_like_quote($filter['order_sn']) . '%\'';
		}

		if ($filter['consignee']) {
			$where .= ' AND o.consignee LIKE \'%' . mysql_like_quote($filter['consignee']) . '%\'';
		}

		if ($filter['email']) {
			$where .= ' AND o.email LIKE \'%' . mysql_like_quote($filter['email']) . '%\'';
		}

		if ($filter['address']) {
			$where .= ' AND o.address LIKE \'%' . mysql_like_quote($filter['address']) . '%\'';
		}

		if ($filter['zipcode']) {
			$where .= ' AND o.zipcode LIKE \'%' . mysql_like_quote($filter['zipcode']) . '%\'';
		}

		if ($filter['tel']) {
			$where .= ' AND o.tel LIKE \'%' . mysql_like_quote($filter['tel']) . '%\'';
		}

		if ($filter['mobile']) {
			$where .= ' AND o.mobile LIKE \'%' . mysql_like_quote($filter['mobile']) . '%\'';
		}

		if ($filter['country']) {
			$where .= ' AND o.country = \'' . $filter['country'] . '\'';
		}

		if ($filter['province']) {
			$where .= ' AND o.province = \'' . $filter['province'] . '\'';
		}

		if ($filter['city']) {
			$where .= ' AND o.city = \'' . $filter['city'] . '\'';
		}

		if ($filter['district']) {
			$where .= ' AND o.district = \'' . $filter['district'] . '\'';
		}

		if ($filter['shipping_id']) {
			$where .= ' AND o.shipping_id  = \'' . $filter['shipping_id'] . '\'';
		}

		if ($filter['pay_id']) {
			$where .= ' AND o.pay_id  = \'' . $filter['pay_id'] . '\'';
		}

		if ($filter['order_status'] != -1) {
			$where .= ' AND o.order_status  = \'' . $filter['order_status'] . '\'';
		}

		if ($filter['shipping_status'] != -1) {
			$where .= ' AND o.shipping_status = \'' . $filter['shipping_status'] . '\'';
		}

		if ($filter['pay_status'] != -1) {
			$where .= ' AND o.pay_status = \'' . $filter['pay_status'] . '\'';
		}

		if ($filter['user_id']) {
			$where .= ' AND o.user_id = \'' . $filter['user_id'] . '\'';
		}

		if ($filter['user_name']) {
			$where .= ' AND u.user_name LIKE \'%' . mysql_like_quote($filter['user_name']) . '%\'';
		}

		if ($filter['start_time']) {
			$where .= ' AND o.add_time >= \'' . $filter['start_time'] . '\'';
		}

		if ($filter['end_time']) {
			$where .= ' AND o.add_time <= \'' . $filter['end_time'] . '\'';
		}

		if ($filter['return_type'] != -1) {
			$where .= ' AND r.return_type = \'' . $filter['return_type'] . '\'';
		}

		switch ($filter['composite_status']) {
		case CS_AWAIT_PAY:
			$where .= order_query_sql('await_pay');
			break;

		case CS_AWAIT_SHIP:
			$where .= order_query_sql('await_ship');
			break;

		case CS_FINISHED:
			$where .= order_query_sql('finished');
			break;

		case PS_PAYING:
			if ($filter['composite_status'] != -1) {
				$where .= ' AND o.pay_status = \'' . $filter['composite_status'] . '\' ';
			}

			break;

		case OS_SHIPPED_PART:
			if ($filter['composite_status'] != -1) {
				$where .= ' AND o.shipping_status  = \'' . $filter['composite_status'] . '\'-2 ';
			}

			break;

		default:
			if ($filter['composite_status'] != -1) {
				$where .= ' AND o.order_status = \'' . $filter['composite_status'] . '\' ';
			}
		}

		if ($filter['group_buy_id']) {
			$where .= ' AND o.extension_code = \'group_buy\' AND o.extension_id = \'' . $filter['group_buy_id'] . '\' ';
		}

		$sql = 'SELECT agency_id FROM ' . $GLOBALS['ecs']->table('admin_user') . ' WHERE user_id = \'' . $_SESSION['seller_id'] . '\'';
		$agency_id = $GLOBALS['db']->getOne($sql);

		if (0 < $agency_id) {
			$where .= ' AND o.agency_id = \'' . $agency_id . '\' ';
		}

		$filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);
		if (isset($_REQUEST['page_size']) && (0 < intval($_REQUEST['page_size']))) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && (0 < intval($_COOKIE['ECSCP']['page_size']))) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_search = -1;
		$store_where = '';
		$store_search_where = '';

		if (-1 < $filter['store_search']) {
			if ($adminru['ru_id'] == 0) {
				if (0 < $filter['store_search']) {
					if ($_REQUEST['store_type']) {
						$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
					}

					$no_main_order = ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';

					if ($filter['store_search'] == 1) {
						$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og' . ' WHERE og.order_id = o.order_id limit 0, 1) = \'' . $filter['merchant_id'] . '\' ';
					}
					else if ($filter['store_search'] == 2) {
						$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
					}
					else if ($filter['store_search'] == 3) {
						$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
					}

					if (1 < $filter['store_search']) {
						$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og, ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . ' WHERE og.order_id = o.order_id AND msi.user_id = og.ru_id ' . $store_where . ' limit 0, 1) > 0 ';
					}
				}
				else {
					$store_search = 0;
				}
			}
		}

		if (($store_search == 0) && ($adminru['ru_id'] == 0)) {
			$where_store = ' AND (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . ' WHERE o.order_id = og.order_id AND og.ru_id = 0 limit 0,1) > 0 ' . ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0';
		}
		else {
			$where_store = '';
		}

		if ($filter['user_name']) {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_return') . ' AS o ,' . $GLOBALS['ecs']->table('users') . ' AS u ' . $where . $where_store;
		}
		else {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_return') . ' AS r, ' . $GLOBALS['ecs']->table('order_info') . ' as o ' . $where . ' AND r.order_id = o.order_id';
		}

		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = 'SELECT o.order_id, o.order_sn, o.add_time, o.order_status, o.shipping_status, o.order_amount, o.money_paid, og.ru_id,' . 'o.pay_status, o.consignee, o.address, o.email, o.tel, o.extension_code, o.extension_id,' . 'r.ret_id ,r.rec_id , r.back , r.exchange ,r.attr_val , r.cause_id , r.apply_time , r.should_return , r.actual_return , r.remark , r.address , o.sign_time ,r.return_status , r.refound_status , ' . ' r.return_type, r.addressee, r.phone, r.return_sn, ' . '(' . order_amount_field('o.') . ') AS total_fee, ' . 'IFNULL(u.user_name, \'' . $GLOBALS['_LANG']['anonymous'] . '\') AS buyer ' . 'FROM ' . $GLOBALS['ecs']->table('order_return') . ' AS r ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS o ON r.order_id = o.order_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON og.order_id=o.order_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id=o.user_id  ' . $where . $where_store . ' GROUP BY r.ret_id ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ' . ' LIMIT ' . (($filter['page'] - 1) * $filter['page_size']) . ',' . $filter['page_size'];

		foreach (array('order_sn', 'consignee', 'email', 'address', 'zipcode', 'tel', 'user_name') as $val) {
			$filter[$val] = stripslashes($filter[$val]);
		}

		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $value) {
		$row[$key]['formated_order_amount'] = price_format($value['order_amount']);
		$row[$key]['formated_money_paid'] = price_format($value['money_paid']);
		$row[$key]['formated_total_fee'] = price_format($value['total_fee']);
		$row[$key]['short_order_time'] = local_date('m-d H:i', $value['add_time']);
		$row[$key]['apply_time'] = local_date('m-d H:i', $value['apply_time']);
		$row[$key]['sign_time'] = local_date('m-d H:i', $value['sign_time']);
		$row[$key]['user_name'] = get_shop_name($value['ru_id'], 1);
		$sql = 'select return_number from ' . $GLOBALS['ecs']->table('return_goods') . ' where rec_id = \'' . $value['rec_id'] . '\'';
		$return_number = $GLOBALS['db']->getOne($sql);
		$row[$key]['return_number'] = $return_number;
		$row[$key]['address_detail'] = get_consignee_info($value['order_id'], $value['address']);
		if (($value['order_status'] == OS_INVALID) || ($value['order_status'] == OS_CANCELED)) {
			$row[$key]['can_remove'] = 1;
		}
		else {
			$row[$key]['can_remove'] = 0;
		}

		if ($value['return_type'] == 0) {
			if ($value['return_status'] == 4) {
				$row[$key]['refound_status'] = FF_MAINTENANCE;
			}
			else {
				$row[$key]['refound_status'] = FF_NOMAINTENANCE;
			}
		}
		else if ($value['return_type'] == 1) {
			if ($value['refound_status'] == 1) {
				$row[$key]['refound_status'] = FF_REFOUND;
			}
			else {
				$row[$key]['refound_status'] = FF_NOREFOUND;
			}
		}
		else if ($value['return_type'] == 2) {
			if ($value['return_status'] == 4) {
				$row[$key]['refound_status'] = FF_EXCHANGE;
			}
			else {
				$row[$key]['refound_status'] = FF_NOEXCHANGE;
			}
		}
	}

	$arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function notice_log($goods_id, $email, $send_ok, $send_type)
{
	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('notice_log') . ' (goods_id, email, send_ok, send_time, send_type) ' . ' VALUES (\'' . $goods_id . '\', \'' . $email . '\', \'' . $send_ok . '\', \'' . gmtime() . '\', \'' . $send_type . '\')';
	$GLOBALS['db']->query($sql);
}

function get_invite_Instantiation($sc_contents = '')
{
	$row = explode('-', $sc_contents);
	$arr['invite_code'] = $row[0];
	$arr['active_time'] = $row[1];
	$arr['end_time'] = $row[2];
	return $arr;
}

function get_delete_seller_info($table = '', $where = '')
{
	if (!empty($table) && !empty($where)) {
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where;
		$GLOBALS['db']->query($sql);
	}
}

function get_seller_delete_order_list($ru_id)
{
	$sql = 'SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE ru_id = \'' . $ru_id . '\'';
	$order_id = $GLOBALS['db']->getOne($sql);
	$sql = 'SELECT ret_id FROM ' . $GLOBALS['ecs']->table('order_return') . ' WHERE order_id = \'' . $order_id . '\'';
	$ret_list = $GLOBALS['db']->getAll($sql);

	foreach ($ret_list as $key => $row) {
		$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('return_goods') . ' WHERE rec_id = \'' . $row['ret_id'] . '\'');
		$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('return_action') . ' WHERE rec_id = \'' . $row['ret_id'] . '\'');
	}

	$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('order_return') . ' WHERE order_id = \'' . $order_id . '\'');
	$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $order_id . '\'');
	$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE ru_id = \'' . $ru_id . '\'');
}

function get_seller_delete_goods_list($ru_id)
{
	get_delete_seller_info('goods', 'user_id = \'' . $ru_id . '\'');
	$sql = 'SELECT cat_id FROM ' . $GLOBALS['ecs']->table('goods_type') . ' WHERE user_id = \'' . $ru_id . '\'';
	$goods_type = $GLOBALS['db']->getAll($sql);

	foreach ($goods_type as $key => $row) {
		$sql = 'SELECT attr_id FROM ' . $GLOBALS['ecs']->table('attribute') . ' WHERE cat_id = \'' . $row['cat_id'] . '\'';
		$attribute_list = $GLOBALS['db']->getAll($sql);

		foreach ($attribute_list as $arow) {
			$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE attr_id = \'' . $row['attr_id'] . '\'');
		}
	}
}

function get_php_self($type = 0)
{
	$php_self = substr(PHP_SELF, strrpos(PHP_SELF, '/') + 1);

	if ($type == 1) {
		$self = explode('.', $php_self);
		$php_self = $self[0];
	}

	return $php_self;
}

function get_order_detection_list($is_ajax = 0)
{
	$adminru = get_admin_ru_id();
	$ruCat = '';
	$no_main_order = '';
	$where = ' WHERE 1 ';

	if (0 < $adminru['ru_id']) {
		$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og' . ' WHERE og.order_id = o.order_id limit 0, 1) = \'' . $adminru['ru_id'] . '\' ';
	}

	$no_main_order = ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
	$noTime = gmtime();
	$result = get_filter();

	if ($result === false) {
		$filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
		if (!empty($_GET['is_ajax']) && ($_GET['is_ajax'] == 1)) {
			$_REQUEST['consignee'] = json_str_iconv($_REQUEST['consignee']);
		}

		$filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_search = -1;
		$store_where = '';
		$store_search_where = '';

		if (-1 < $filter['store_search']) {
			if ($adminru['ru_id'] == 0) {
				if (0 < $filter['store_search']) {
					if ($_REQUEST['store_type']) {
						$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
					}

					$no_main_order = ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';

					if ($filter['store_search'] == 1) {
						$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og' . ' WHERE og.order_id = o.order_id limit 0, 1) = \'' . $filter['merchant_id'] . '\' ';
					}
					else if ($filter['store_search'] == 2) {
						$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
					}
					else if ($filter['store_search'] == 3) {
						$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
					}

					if (1 < $filter['store_search']) {
						$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og, ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . ' WHERE og.order_id = o.order_id AND msi.user_id = og.ru_id ' . $store_where . ' limit 0, 1) > 0 ';
					}
				}
				else {
					$store_search = 0;
				}
			}
		}

		if ($filter['order_sn']) {
			$where .= ' AND o.order_sn LIKE \'%' . mysql_like_quote($filter['order_sn']) . '%\'';
		}

		if ($filter['consignee']) {
			$where .= ' AND o.consignee LIKE \'%' . mysql_like_quote($filter['consignee']) . '%\'';
		}

		$filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);
		if (isset($_REQUEST['page_size']) && (0 < intval($_REQUEST['page_size']))) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && (0 < intval($_COOKIE['ECSCP']['page_size']))) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		if (($store_search == 0) && ($adminru['ru_id'] == 0)) {
			$where_store = ' AND (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . ' WHERE o.order_id = og.order_id AND og.ru_id = 0 limit 0,1) > 0 ' . ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0';
		}
		else {
			$where_store = '';
		}

		if ($is_ajax == 1) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET `order_status` = \'' . OS_SPLITED . '\', `shipping_status` = \'' . SS_RECEIVED . '\',`pay_status` = \'' . PS_PAYED . '\' ' . ' WHERE shipping_status = 1 AND (add_time + auto_delivery_time * 24 * 3600) <= \'' . $noTime . '\'';
			$GLOBALS['db']->query($sql);
			return true;
		}
		else if ($is_ajax == 2) {
			$where .= ' AND o.shipping_status = 2 ';
			$where .= ' AND (o.add_time + o.auto_delivery_time * 24 * 3600) <= \'' . $noTime . '\'';
			$filter['page_size'] = 1;
		}
		else if ($is_ajax == 3) {
			$where .= ' AND o.shipping_status = 1 ';
			$where .= ' AND (o.add_time + o.auto_delivery_time * 24 * 3600) <= \'' . $noTime . '\'';
		}
		else {
			$where .= ' AND o.shipping_status = 1 ';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $where . $where_store . $no_main_order;
		$record_count = $GLOBALS['db']->getOne($sql);
		$filter['record_count'] = $record_count;
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$groupBy = ' group by o.order_id ';
		$sql = 'SELECT ifnull(bai.is_stages,0) is_stages,og.ru_id, o.order_id, o.main_order_id, o.order_sn, o.add_time, o.order_status, o.shipping_status, o.order_amount, o.money_paid, o.is_delete,' . 'o.shipping_time, o.auto_delivery_time, o.pay_status, o.consignee, o.address, o.email, o.tel, o.mobile, o.extension_code, o.extension_id, ' . '(' . order_amount_field('o.') . ') AS total_fee, ' . 'IFNULL(u.user_name, \'' . $GLOBALS['_LANG']['anonymous'] . '\') AS buyer, o.auto_delivery_time ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id=o.user_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON o.order_id=og.order_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('baitiao_log') . ' AS bai ON o.order_id=bai.order_id ' . $where . $where_store . $no_main_order . $groupBy . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ' . ' LIMIT ' . (($filter['page'] - 1) * $filter['page_size']) . ',' . $filter['page_size'];

		foreach (array('order_sn', 'consignee', 'email', 'address', 'zipcode', 'tel', 'user_name') as $val) {
			$filter[$val] = stripslashes($filter[$val]);
		}

		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $value) {
		$row[$key]['formated_order_amount'] = price_format($value['order_amount']);
		$row[$key]['formated_money_paid'] = price_format($value['money_paid']);
		$row[$key]['formated_total_fee'] = price_format($value['total_fee']);
		$row[$key]['short_order_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
		$auto_confirm_time = $value['add_time'] + ($value['auto_delivery_time'] * 3600 * 24);
		$row[$key]['auto_confirm_time'] = local_date($GLOBALS['_CFG']['time_format'], $auto_confirm_time);
		$sql = 'SELECT concat(IFNULL(c.region_name, \'\'), \'  \', IFNULL(p.region_name, \'\'), ' . '\'  \', IFNULL(t.region_name, \'\'), \'  \', IFNULL(d.region_name, \'\')) AS region ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS c ON o.country = c.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS p ON o.province = p.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS t ON o.city = t.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS d ON o.district = d.region_id ' . 'WHERE o.order_id = \'' . $value['order_id'] . '\'';
		$row[$key]['region'] = $GLOBALS['db']->getOne($sql);
		$row[$key]['user_name'] = get_shop_name($value['ru_id'], 1);
		$order_id = $value['order_id'];
		$date = array('order_id');
		$order_child = count(get_table_date('order_info', 'main_order_id=\'' . $order_id . '\'', $date, 1));
		$row[$key]['order_child'] = $order_child;
		$date = array('order_sn');
		$child_list = get_table_date('order_info', 'main_order_id=\'' . $order_id . '\'', $date, 1);
		$row[$key]['child_list'] = $child_list;
		if (($value['order_status'] == OS_INVALID) || ($value['order_status'] == OS_CANCELED)) {
			$row[$key]['can_remove'] = 1;
		}
		else {
			$row[$key]['can_remove'] = 0;
		}

		if ($auto_confirm_time <= $noTime) {
			$row[$key]['is_auto_confirm'] = 1;
		}
		else {
			$row[$key]['is_auto_confirm'] = 0;
		}

		$row[$key]['new_shipping_status'] = $GLOBALS['_LANG']['ss'][$value['shipping_status']];
	}

	$arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_goods_brand_info($brand_id = 0)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('brand') . ' WHERE brand_id = \'' . $brand_id . '\' LIMIT 1';
	return $GLOBALS['db']->getRow($sql);
}

function seller_page($list, $nowpage, $show = '10')
{
	$arr = array();

	if ($list['page_count'] < $show) {
		$show = $list['page_count'];
	}

	if (($show % 2) == 0) {
		$begin = $nowpage - ceil($show / 2);
		$end = $nowpage + floor($show / 2);
	}
	else {
		$begin = $nowpage - floor($show / 2);
		$end = $nowpage + ceil($show / 2);
	}

	if (1 < $show) {
		if (((ceil($show / 2) + 1) < $nowpage) && ($nowpage <= $list['page_count'] - ceil($show / 2))) {
			for ($i = $begin; $i < $end; $i++) {
				$arr[$i] = $i;
			}
		}
		else {
			if (((ceil($show / 2) + 1) < $nowpage) && (($list['page_count'] - $show - 1) < $nowpage)) {
				for ($i = $list['page_count'] - $show - 1; $i <= $list['page_count']; $i++) {
					$arr[$i] = $i;
				}
			}
			else {
				for ($i = 1; $i <= $show; $i++) {
					$arr[$i] = $i;
				}
			}
		}
	}
	else {
		$arr[1] = 1;
	}

	return $arr;
}

function get_seller_action_list()
{
	include_once 'includes/inc_menu.php';
	include_once 'includes/inc_priv.php';

	foreach ($modules as $key => $value) {
		ksort($modules[$key]);
	}

	ksort($modules);

	foreach ($modules as $key => $val) {
		$menus[$key]['label'] = $GLOBALS['_LANG'][$key];

		if (is_array($val)) {
			foreach ($val as $k => $v) {
				if (isset($purview[$k])) {
					if (is_array($purview[$k])) {
						$boole = false;

						foreach ($purview[$k] as $action) {
							$boole = $boole || admin_priv($action, '', false);
						}

						if (!$boole) {
							continue;
						}
					}
					else if (!admin_priv($purview[$k], '', false)) {
						continue;
					}
				}

				if (($k == 'ucenter_setup') && ($GLOBALS['_CFG']['integrate_code'] != 'ucenter')) {
					continue;
				}

				$menus[$key]['children'][$k]['label'] = $GLOBALS['_LANG'][$k];
				$menus[$key]['children'][$k]['action'] = $v;
			}
		}
		else {
			$menus[$key]['action'] = $val;
		}

		if (empty($menus[$key]['children'])) {
			unset($menus[$key]);
		}
	}

	$menus = get_menu_list($menus);
	return $menus;
}

function get_menu_list($menus)
{
	$menus = array_values($menus);
	$arr = array();

	foreach ($menus as $key => $row) {
		$arr[$key] = $row;

		if ($row['label'] == '商品管理') {
			$arr[$key]['href'] = 'goods.php?act=list';
			$arr[$key]['action_type'] = 'goods';
		}
		else if ($row['label'] == '广告管理') {
			$arr[$key]['href'] = 'ads.php?act=list';
			$arr[$key]['action_type'] = 'ads';
		}
		else if ($row['label'] == '订单管理') {
			$arr[$key]['href'] = 'order.php?act=list';
			$arr[$key]['action_type'] = 'order';
		}
		else if ($row['label'] == '促销管理') {
			$arr[$key]['href'] = 'bonus.php?act=list';
			$arr[$key]['action_type'] = 'bonus';
		}
		else if ($row['label'] == '报表统计') {
			$arr[$key]['href'] = 'order_stats.php?act=list';
			$arr[$key]['action_type'] = 'order_stats';
		}
		else if ($row['label'] == '权限管理') {
			$arr[$key]['href'] = 'privilege.php?act=list';
			$arr[$key]['action_type'] = 'privilege';
		}
		else if ($row['label'] == '会员管理') {
			$arr[$key]['href'] = 'user_msg.php?act=list_all';
			$arr[$key]['action_type'] = 'users';
		}
		else if ($row['label'] == '系统设置') {
			$arr[$key]['href'] = 'warehouse.php?act=list';
			$arr[$key]['action_type'] = 'warehouse';
		}
		else if ($row['label'] == '商家入驻管理') {
			$arr[$key]['href'] = 'merchants_commission.php?act=list';
			$arr[$key]['action_type'] = 'merchants_commission';
		}
		else if ($row['label'] == '商品批量管理') {
			$arr[$key]['href'] = 'goods_warehouse_batch.php?act=add';
			$arr[$key]['action_type'] = 'goods_warehouse_batch';
		}
		else if ($row['label'] == '店铺设置管理') {
			$arr[$key]['href'] = 'index.php?act=merchants_first';
			$arr[$key]['action_type'] = 'index';
		}
		else if ($row['label'] == '文章管理') {
			$arr[$key]['href'] = 'articlecat.php?act=list';
			$arr[$key]['action_type'] = 'articlecat';
		}
		else if ($row['label'] == '模板管理') {
			$arr[$key]['href'] = 'template.php?act=list';
			$arr[$key]['action_type'] = 'template';
		}
		else if ($row['label'] == '推荐管理') {
			$arr[$key]['href'] = 'affiliate.php?act=list';
			$arr[$key]['action_type'] = 'affiliate';
		}
		else if ($row['label'] == '邮件群发管理') {
			$arr[$key]['href'] = 'view_sendlist.php?act=list';
			$arr[$key]['action_type'] = 'view_sendlist';
		}
		else if ($row['label'] == '数据库管理') {
			$arr[$key]['href'] = 'sql.php?act=main';
			$arr[$key]['action_type'] = 'sql';
		}
	}

	return $arr;
}

function get_favourable_count($ru_id)
{
	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('favourable_activity') . ' WHERE user_id = \'' . $ru_id . '\'';
	return $GLOBALS['db']->getOne($sql);
}

function get_favourable_dateout_count($ru_id)
{
	$firstSecToday = local_mktime(date('h'), date('i'), date('s'), date('m'), date('d'), date('Y')) - (24 * 60 * 60 * 2);
	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('favourable_activity') . ' WHERE user_id = \'' . $ru_id . '\' AND (end_time - 24 * 60 * 60 * 3) < \'' . $firstSecToday . '\'';
	return $GLOBALS['db']->getOne($sql);
}

function get_comment_reply_count($ru_id)
{
	$where = '(SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' AS c2 WHERE c2.parent_id = c1.comment_id LIMIT 1) < 1';
	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' AS c1 WHERE c1.comment_type = 0 AND c1.parent_id = 0 AND c1.ru_id = \'' . $ru_id . '\' AND c1.order_id > 0 AND ' . $where;
	return $GLOBALS['db']->getOne($sql);
}

function get_goods_special_count($ru_id, $type = '')
{
	$time = gmtime();

	switch ($type) {
	case 'is_hot':
		$where = 'AND is_hot = 1';
		break;

	case 'is_new':
		$where = 'AND is_new = 1';
		break;

	case 'is_best':
		$where = 'AND is_best = 1';
		break;

	case 'promotion':
		$where = 'AND is_promote = 1 AND promote_start_date < \'' . $time . '\' AND promote_end_date > ' . $time;
		break;

	default:
		$where = '';
		break;
	}

	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE user_id = \'' . $ru_id . '\' ' . $where . ' AND is_delete = 0';
	return $GLOBALS['db']->getOne($sql);
}

function store_priv($priv_str, $msg_type = '', $msg_output = true)
{
	$store_user_id = 0;

	if ($GLOBALS['store_user_id']) {
		$store_user_id = $GLOBALS['store_user_id'];
	}
	else if ($_SESSION['store_user_id']) {
		$store_user_id = $_SESSION['store_user_id'];
	}

	$sql = ' SELECT store_action FROM ' . $GLOBALS['ecs']->table('store_user') . ' WHERE id = \'' . $store_user_id . '\' ';
	$store_action = $GLOBALS['db']->getOne($sql);

	if ($store_action == 'all') {
		return true;
	}
	else {
		if (!empty($store_action) && in_array($priv_str, explode(',', $store_action))) {
			return true;
		}
		else {
			if ($msg_output) {
				sys_msg('您没有使用该功能的权限', 0, $link);
			}

			return false;
		}
	}
}

function get_store_ru_id()
{
	$admin_id = $_SESSION['store_user_id'];
	$sql = 'SELECT ru_id FROM' . $GLOBALS['ecs']->table('store_user') . 'WHERE id = \'' . $admin_id . '\'';
	return $GLOBALS['db']->getOne($sql);
}

function set_default_filter($goods_id = 0, $cat_id = 0, $user_id = 0, $cat_type_show = 0, $table = 'category')
{
	if ($cat_id) {
		$parent_cat_list = get_select_category($cat_id, 1, true, $user_id, $table);
		$filter_category_navigation = get_array_category_info($parent_cat_list, $table);
		$GLOBALS['smarty']->assign('filter_category_navigation', $filter_category_navigation);
	}

	if ($user_id) {
		$seller_shop_cat = seller_shop_cat($user_id);
	}
	else {
		$seller_shop_cat = array();
	}

	$GLOBALS['smarty']->assign('filter_category_list', get_category_list($cat_id, 0, $seller_shop_cat, $user_id, 2, $table));
	$GLOBALS['smarty']->assign('filter_brand_list', search_brand_list($goods_id));
	$GLOBALS['smarty']->assign('cat_type_show', $cat_type_show);
	return true;
}

function seller_shop_cat($user_id = 0)
{
	$seller_shop_cat = '';

	if ($user_id) {
		$sql = 'SELECT user_shopMain_category FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE user_id = \'' . $user_id . '\'';
		$seller_shop_cat = $GLOBALS['db']->getOne($sql, true);
	}

	$arr = array();
	$arr['parent'] = '';

	if ($seller_shop_cat) {
		$seller_shop_cat = explode('-', $seller_shop_cat);

		foreach ($seller_shop_cat as $key => $row) {
			if ($row) {
				$cat = explode(':', $row);
				$arr[$key]['cat_id'] = $cat[0];
				$arr[$key]['cat_tree'] = $cat[1];
				$arr['parent'] .= $cat[0] . ',';

				if ($cat[1]) {
					$arr['parent'] .= $cat[1] . ',';
				}
			}
		}
	}

	$arr['parent'] = substr($arr['parent'], 0, -1);
	return $arr;
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
