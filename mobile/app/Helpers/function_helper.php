<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_ip_area_name($ip = '', $api = '新浪')
{
	$Http = new \App\Extensions\Http();

	if ($ip == '') {
		$ip = real_ip();
	}

	if ($api == '淘宝') {
		$url = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip;
		$data = $Http->doGet($url);
		$str = json_decode($data, true);

		if ($str['data']['county'] != '') {
			$region = $str['data']['county'];
			$arr['county_level'] = 2;
		}
		else if ($str['data']['city'] != '') {
			$region = $str['data']['city'];
			$arr['county_level'] = 2;
		}
		else {
			$region = $str['data']['region'];
			$arr['county_level'] = 1;
		}
	}
	else {
		$url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=' . $ip;
		$data = $Http->doGet($url);
		$str = json_decode($data, true);

		if ($str['city'] != '') {
			$region = $str['city'];
			$arr['county_level'] = 2;
		}
		else {
			$region = $str['province'];
			$arr['county_level'] = 1;
		}
	}

	$area_name = str_replace(array('省', '市'), '', $region);

	if (strstr($area_name, '香港')) {
		$area_name = '香港';
	}
	else if (strstr($area_name, '澳门')) {
		$area_name = '澳门';
	}
	else if (strstr($area_name, '内蒙古')) {
		$area_name = '内蒙古';
	}
	else if (strstr($area_name, '宁夏')) {
		$area_name = '宁夏';
	}
	else if (strstr($area_name, '新疆')) {
		$area_name = '新疆';
	}
	else if (strstr($area_name, '西藏')) {
		$area_name = '西藏';
	}
	else if (strstr($area_name, '广西')) {
		$area_name = '广西';
	}

	$arr['area_name'] = $area_name;
	return $arr;
}

function sc_unserialize_config($cfg)
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

function sc_available_shipping_list($region_id_list)
{
	$sql = 'SELECT s.shipping_id, s.shipping_code, s.shipping_name, ' . 's.shipping_desc, s.insure, s.support_cod, a.configure ' . 'FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s, ' . $GLOBALS['ecs']->table('shipping_area') . ' AS a, ' . $GLOBALS['ecs']->table('area_region') . ' AS r ' . 'WHERE r.region_id ' . db_create_in($region_id_list) . ' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1 ORDER BY s.shipping_order';
	return $GLOBALS['db']->getAll($sql);
}

function get_order_query_sql($type = 'finished', $alias = '')
{
	if ($type == 'finished') {
		return ' AND ' . $alias . 'order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) . (' AND ' . $alias . 'shipping_status ') . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . (' AND ' . $alias . 'pay_status ') . db_create_in(array(PS_PAYED, PS_PAYING)) . ' ';
	}
	else if ($type == 'await_ship') {
		return ' AND   ' . $alias . 'order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) . (' AND   ' . $alias . 'shipping_status ') . db_create_in(array(SS_UNSHIPPED, SS_PREPARING, SS_SHIPPED_ING)) . (' AND ( ' . $alias . 'pay_status ') . db_create_in(array(PS_PAYED, PS_PAYING)) . (' OR ' . $alias . 'pay_id ') . db_create_in(get_payment_id_list(true)) . ') ';
	}
	else if ($type == 'await_pay') {
		return ' AND   ' . $alias . 'order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) . (' AND   ' . $alias . 'pay_status = \'') . PS_UNPAYED . '\'' . (' AND ( ' . $alias . 'shipping_status ') . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . (' OR ' . $alias . 'pay_id ') . db_create_in(get_payment_id_list(false)) . ') ';
	}
	else if ($type == 'unconfirmed') {
		return ' AND ' . $alias . 'order_status = \'' . OS_UNCONFIRMED . '\' ';
	}
	else if ($type == 'unprocessed') {
		return ' AND ' . $alias . 'order_status ' . db_create_in(array(OS_UNCONFIRMED, OS_CONFIRMED)) . (' AND ' . $alias . 'shipping_status = \'') . SS_UNSHIPPED . '\'' . (' AND ' . $alias . 'pay_status = \'') . PS_UNPAYED . '\' ';
	}
	else if ($type == 'unpay_unship') {
		return ' AND ' . $alias . 'order_status ' . db_create_in(array(OS_UNCONFIRMED, OS_CONFIRMED)) . (' AND ' . $alias . 'shipping_status ') . db_create_in(array(SS_UNSHIPPED, SS_PREPARING)) . (' AND ' . $alias . 'pay_status = \'') . PS_UNPAYED . '\' ';
	}
	else if ($type == 'shipped') {
		return ' AND ' . $alias . 'order_status = \'' . OS_CONFIRMED . '\'' . (' AND ' . $alias . 'shipping_status ') . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . ' ';
	}
	else if ($type == 'to_confirm') {
		return ' AND ' . $alias . 'order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) . ' ' . (' AND ' . $alias . 'shipping_status = \'') . SS_SHIPPED . '\'' . (' AND ' . $alias . 'pay_status ') . db_create_in(array(PS_PAYED, PS_PAYING));
	}
	else {
		exit('函数 order_query_sql 参数错误');
	}
}

function get_del_in_val($strCnt, $re_str)
{
	$strCnt = explode(',', $strCnt);
	$re_str = explode(',', $re_str);

	for ($i = 0; $i < count($re_str); $i++) {
		for ($j = 0; $j < count($strCnt); $j++) {
			if ($re_str[$i] == $strCnt[$j]) {
				unset($strCnt[$j]);
			}
		}
	}

	$strCnt = implode(',', $strCnt);
	return $strCnt;
}

function get_payment_id_list($is_cod)
{
	$sql = 'SELECT pay_id FROM ' . $GLOBALS['ecs']->table('payment');

	if ($is_cod) {
		$sql .= ' WHERE is_cod = 1';
	}
	else {
		$sql .= ' WHERE is_cod = 0';
	}

	return $GLOBALS['db']->getCol($sql);
}

function get_order_amount_field($alias = '')
{
	return '   ' . $alias . 'goods_amount + ' . $alias . 'tax + ' . $alias . 'shipping_fee' . (' + ' . $alias . 'insure_fee + ' . $alias . 'pay_fee + ' . $alias . 'pack_fee') . (' + ' . $alias . 'card_fee ');
}

function get_table_date($table = '', $where = 1, $date = array(), $sqlType = 0)
{
	$date = implode(',', $date);

	if (!empty($date)) {
		if ($sqlType != 1) {
			$where .= ' LIMIT 1';
		}

		$sql = 'select ' . $date . ' from ' . $GLOBALS['ecs']->table($table) . ' where ' . $where;

		if ($sqlType == 1) {
			return $GLOBALS['db']->getAll($sql);
		}
		else if ($sqlType == 2) {
			return $GLOBALS['db']->getOne($sql);
		}
		else {
			return $GLOBALS['db']->getRow($sql);
		}
	}
}

function get_store_cat_info($cat_id)
{
	return $GLOBALS['db']->getRow('SELECT cat_name, keywords, cat_desc, style, grade, filter_attr, parent_id FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE cat_id = \'' . $cat_id . '\''));
}

function get_store_parent_grade($cat_id)
{
	static $res;

	if ($res === NULL) {
		$data = read_static_cache('cat_parent_grade');

		if ($data === false) {
			$sql = 'SELECT parent_id, cat_id, grade ' . ' FROM ' . $GLOBALS['ecs']->table('category');
			$res = $GLOBALS['db']->getAll($sql);
			write_static_cache('cat_parent_grade', $res);
		}
		else {
			$res = $data;
		}
	}

	if (!$res) {
		return 0;
	}

	$parent_arr = array();
	$grade_arr = array();

	foreach ($res as $val) {
		$parent_arr[$val['cat_id']] = $val['parent_id'];
		$grade_arr[$val['cat_id']] = $val['grade'];
	}

	while (0 < $parent_arr[$cat_id] && $grade_arr[$cat_id] == 0) {
		$cat_id = $parent_arr[$cat_id];
	}

	return $grade_arr[$cat_id];
}

function get_print_r($arr)
{
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}

function goods_shipping_fee($shipping_code, $shipping_config, $goods_weight, $goods_amount, $goods_number = '')
{
	if (!is_array($shipping_config)) {
		$shipping_config = unserialize($shipping_config);
	}

	if (empty($shipping_config)) {
		$shipping_config = array();
	}

	$filename = ADDONS_PATH . 'shipping/' . $shipping_code . '.php';

	if (file_exists($filename)) {
		include_once $filename;
		$obj = new $shipping_code($shipping_config);
		return $obj->calculate($goods_weight, $goods_amount, $goods_number);
	}
	else {
		return 0;
	}
}

function get_regions_steps($type = 0, $parent = 0)
{
	$sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE region_type = \'' . $type . '\' AND parent_id = \'' . $parent . '\'');
	return $GLOBALS['db']->GetAll($sql);
}

function get_array_sort($arr, $keys, $type = 'asc')
{
	$new_array = array();
	if (is_array($arr) && !empty($arr)) {
		$keysvalue = $new_array = array();

		foreach ($arr as $k => $v) {
			$keysvalue[$k] = $v[$keys];
		}

		if ($type == 'asc') {
			asort($keysvalue);
		}
		else {
			arsort($keysvalue);
		}

		reset($keysvalue);

		foreach ($keysvalue as $k => $v) {
			$new_array[$k] = $arr[$k];
		}
	}

	return $new_array;
}

function get_Add_Drop_fields($date, $newDate = '', $table = '', $type = 'insert', $dateType = 'VARCHAR', $length = '', $IntType = 'NOT NULL', $comment = '')
{
	$date = trim($date);
	$comment = trim($comment);

	if (empty($newDate)) {
		$newDate = $date;
	}

	if ($dateType == 'VARCHAR') {
		$length = empty($length) ? 255 : $length;
		$dateType = 'VARCHAR( ' . $length . ' )';
	}
	else if ($dateType == 'CHAR') {
		$length = empty($length) ? 60 : $length;
		$dateType = 'CHAR( ' . $length . ' )';
	}
	else if ($dateType == 'INT') {
		$length = empty($length) ? 11 : $length;
		$codingType = '';
		$coding = '';
		$dateType = 'INT( ' . $length . ' ) UNSIGNED';
	}
	else if ($dateType == 'MEDIUMINT') {
		$length = empty($length) ? 11 : $length;
		$codingType = '';
		$coding = '';
		$dateType = 'MEDIUMINT( ' . $length . ' ) UNSIGNED';
	}
	else if ($dateType == 'SMALLINT') {
		$length = empty($length) ? 11 : $length;
		$codingType = '';
		$coding = '';
		$dateType = 'SMALLINT( ' . $length . ' ) UNSIGNED';
	}
	else if ($dateType == 'TINYINT') {
		$length = empty($length) ? 1 : $length;
		$codingType = '';
		$coding = '';
		$dateType = 'TINYINT( ' . $length . ' ) UNSIGNED';
	}
	else if ($dateType == 'TEXT') {
		$length = '';
		$dateType = 'TEXT';
	}
	else if ($dateType == 'DECIMAL') {
		$length = empty($length) ? '10,2' : $length;
		$codingType = '';
		$coding = '';
		$dateType = 'DECIMAL( ' . $length . ' )';
	}

	if ($IntType != 'NOT NULL') {
		$IntType = 'NULL';
	}

	if (!empty($comment)) {
		$comment = ' COMMENT \'' . $comment . '\'';
	}

	if (!empty($table)) {
		if ($type == 'insert') {
			$sql = 'ALTER TABLE ' . $GLOBALS['ecs']->table($table) . ' ADD `' . $date . '` ' . $dateType . ' ' . $IntType . $comment;
		}
		else if ($type == 'update') {
			$sql = 'ALTER TABLE ' . $GLOBALS['ecs']->table($table) . ' CHANGE `' . $date . '` `' . $newDate . '` ' . $dateType . ' ' . $codingType . ' ' . $IntType . ' ' . $comment;
		}
		else if ($type == 'delete') {
			$sql = 'ALTER TABLE ' . $GLOBALS['ecs']->table($table) . ' DROP `' . $date . '`';
		}

		$res = $GLOBALS['db']->query($sql);

		if ($res == 1) {
			return 1;
		}
		else {
			return 3;
		}
	}
	else {
		return 2;
	}
}

function get_array_fields($date, $newDate, $table, $type, $dateType, $length)
{
	for ($i = 0; $i < count($date); $i++) {
		get_add_drop_fields($date[$i], $newDate[$i], $table, $type, $dateType[$i], $length[$i]);
	}
}

function get_merchants_article_menu($cat_id)
{
	$sql = 'select article_id, title, file_url, open_type, article_type from ' . $GLOBALS['ecs']->table('article') . (' where cat_id = \'' . $cat_id . '\' order by article_id desc');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['article_id'] = $row['article_id'];
		$arr[$key]['article_type'] = $row['article_type'];
		$arr[$key]['title'] = $row['title'];

		if ($row['open_type'] != 1) {
			$arr[$key]['url'] = build_uri('merchants', array('mid' => $row['article_id']), $row['title']);
		}
		else {
			$arr[$key]['url'] = $row['file_url'];
		}
	}

	return $arr;
}

function get_merchants_article_info($article_id)
{
	$sql = 'select content from ' . $GLOBALS['ecs']->table('article') . (' where article_id = \'' . $article_id . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function get_merchants_steps_fields_admin($table, $date, $dateType, $length, $notnull, $coding, $formName, $fields_sort, $tid)
{
	$arr = array();

	for ($i = 0; $i < count($date); $i++) {
		if (!empty($date[$i])) {
			$arr[$i]['date'] = $date[$i];
			$arr[$i]['dateType'] = $dateType[$i];
			$arr[$i]['length'] = $length[$i];
			$arr[$i]['notnull'] = $notnull[$i];
			$arr[$i]['formName'] = $formName[$i];
			$arr[$i]['coding'] = $coding[$i];
			$arr[$i]['fields_sort'] = $fields_sort[$i];
			$arr['textFields'] .= $date[$i] . ',';
			$arr['fieldsDateType'] .= $dateType[$i] . ',';
			$arr['fieldsLength'] .= $length[$i] . ',';
			$arr['fieldsNotnull'] .= $notnull[$i] . ',';
			$arr['fieldsFormName'] .= $formName[$i] . ',';
			$arr['fieldsCoding'] .= $coding[$i] . ',';
			$arr['fields_sort'] .= $fields_sort[$i] . ',';
			$arr['will_choose'] .= $_POST['will_choose_' . $i] . ',';
			if ($dateType[$i] == 'INT' || $dateType[$i] == 'TINYINT' || $dateType[$i] == 'DECIMAL' || $dateType[$i] == 'MEDIUMINT' || $dateType[$i] == 'SMALLINT') {
				$arr[$i]['coding'] = '';
			}

			$type = 'insert';
			$test = mysql_query('Describe ' . $GLOBALS['ecs']->table($table) . $date[$i]);
			$test = mysql_fetch_array($test);

			if (is_array($test)) {
				$type = 'update';
				$newDate = '';
			}
			else {
				$type = 'insert';
			}

			$failure = get_add_drop_fields($arr[$i]['date'], $newDate, $table, $type, $arr[$i]['dateType'], $arr[$i]['length'], $arr[$i]['notnull'], $arr[$i]['formName'], $arr[$i]['coding']);

			if ($failure == 2) {
				$sql = 'select fields_steps from ' . $GLOBALS['ecs']->table('merchants_steps_title') . (' where tid = \'' . $tid . '\'');
				$pid = $GLOBALS['db']->getOne($sql);
				$link[] = array('text' => '返回一页', 'href' => 'merchants_steps.php?act=title_list&id=' . $pid);
				sys_msg('表名称为空', 0, $link);
				break;
			}
		}
	}

	$arr['textFields'] = substr($arr['textFields'], 0, -1);
	$arr['fieldsDateType'] = substr($arr['fieldsDateType'], 0, -1);
	$arr['fieldsLength'] = substr($arr['fieldsLength'], 0, -1);
	$arr['fieldsNotnull'] = substr($arr['fieldsNotnull'], 0, -1);
	$arr['fieldsFormName'] = substr($arr['fieldsFormName'], 0, -1);
	$arr['fieldsCoding'] = substr($arr['fieldsCoding'], 0, -1);
	$arr['fields_sort'] = substr($arr['fields_sort'], 0, -1);
	$arr['will_choose'] = substr($arr['will_choose'], 0, -1);
	return $arr;
}

function get_steps_form_choose($form_array = array())
{
	$form = $form_array['form'];
	$arr = array();

	for ($i = 0; $i < count($form); $i++) {
		if (!empty($form_array['formName_special'][$i])) {
			$formName_special = '+' . $form_array['formName_special'][$i];
		}
		else {
			$formName_special = '+' . ' ';
		}

		if ($form[$i] == 'input') {
			$arr[$i]['form'] = $form[$i] . ':' . $form_array['formSize'][$i] . $formName_special;
		}
		else if ($form[$i] == 'textarea') {
			$arr[$i]['form'] = $form[$i] . ':' . $form_array['rows'][$i] . ',' . $form_array['cols'][$i] . $formName_special;
		}
		else if ($form[$i] == 'radio') {
			$arr[$i]['form'] = $form[$i] . ':' . implode(',', get_formType_arr($_POST['radio_checkbox_' . $i], $_POST['rc_sort_' . $i])) . $formName_special;
		}
		else if ($form[$i] == 'checkbox') {
			$arr[$i]['form'] = $form[$i] . ':' . implode(',', get_formType_arr($_POST['radio_checkbox_' . $i], $_POST['rc_sort_' . $i])) . $formName_special;
		}
		else if ($form[$i] == 'select') {
			$arr[$i]['form'] = $form[$i] . ':' . implode(',', get_formType_arr($_POST['select_' . $i], '', 1)) . $formName_special;
		}
		else if ($form[$i] == 'other') {
			if ($form_array['formOther'][$i] == 'dateTime') {
				$dateTimeText = ',' . $form_array['formOtherSize'][$i];
			}

			$arr[$i]['form'] = $form[$i] . ':' . $form_array['formOther'][$i] . $dateTimeText . $formName_special;
		}

		if (!empty($form_array['date'][$i])) {
			$arr['chooseForm'] .= $arr[$i]['form'] . '|';
		}
	}

	$arr['chooseForm'] = substr($arr['chooseForm'], 0, -1);
	return $arr;
}

function get_formType_arr($formType, $rc_sort, $type = 0)
{
	$arr = array();

	for ($i = 0; $i < count($formType); $i++) {
		if (!empty($formType[$i])) {
			if ($type == 0) {
				$arr[$i] = trim($formType[$i]) . '*' . trim($rc_sort[$i]);
			}
			else {
				$arr[$i] = trim($formType[$i]);
			}
		}
	}

	return $arr;
}

function get_merchants_steps_fields_centent_insert_update($textFields, $fieldsDateType, $fieldsLength, $fieldsNotnull, $fieldsFormName, $fieldsCoding, $fields_sort, $will_choose, $chooseForm, $tid)
{
	$parent = array('tid' => $tid, 'textFields' => $textFields, 'fieldsDateType' => $fieldsDateType, 'fieldsLength' => $fieldsLength, 'fieldsNotnull' => $fieldsNotnull, 'fieldsFormName' => $fieldsFormName, 'fieldsCoding' => $fieldsCoding, 'fields_sort' => $fields_sort, 'will_choose' => $will_choose, 'fieldsForm' => $chooseForm);
	$sql = 'select id from ' . $GLOBALS['ecs']->table('merchants_steps_fields_centent') . (' where tid = \'' . $tid . '\'');
	$res = $GLOBALS['db']->getOne($sql);

	if (0 < $res) {
		$handler_type = 'update';
	}
	else {
		$handler_type = 'insert';
	}

	if ($handler_type == 'update') {
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_steps_fields_centent'), $parent, 'UPDATE', 'tid = \'' . $tid . '\'');
	}
	else {
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_steps_fields_centent'), $parent, 'INSERT');
	}

	return true;
}

function get_merchants_steps_title_insert_update($fields_steps, $fields_titles, $titles_annotation, $steps_style, $fields_special, $special_type, $handler_type = 'insert', $tid = 0)
{
	if ($handler_type == 'update') {
		$typeTid = ' and tid <> ' . $tid;
	}

	$sql = 'select tid from ' . $GLOBALS['ecs']->table('merchants_steps_title') . (' where fields_titles = \'' . $fields_titles . '\'') . $typeTid;
	$res = $GLOBALS['db']->getOne($sql);

	if (0 < $res) {
		return false;
	}
	else {
		$parent = array('fields_steps' => $fields_steps, 'fields_titles' => $fields_titles, 'titles_annotation' => $titles_annotation, 'steps_style' => $steps_style, 'fields_special' => $fields_special, 'special_type' => $special_type);

		if ($handler_type == 'update') {
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_steps_title'), $parent, 'UPDATE', 'tid = \'' . $tid . '\'');
			return true;
		}
		else {
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_steps_title'), $parent, 'INSERT');
			$tid = $GLOBALS['db']->insert_id();
			$res['tid'] = $tid;
			$res['true'] = true;
			return $res;
		}
	}
}

function get_fields_centent_info($id, $textFields, $fieldsDateType, $fieldsLength, $fieldsNotnull, $fieldsFormName, $fieldsCoding, $fieldsForm, $fields_sort, $will_choose, $webType = 'admin', $user_id = 0)
{
	if (!empty($textFields)) {
		$textFields = explode(',', $textFields);
		$fieldsDateType = explode(',', $fieldsDateType);
		$fieldsLength = explode(',', $fieldsLength);
		$fieldsNotnull = explode(',', $fieldsNotnull);
		$fieldsFormName = explode(',', $fieldsFormName);
		$fieldsCoding = explode(',', $fieldsCoding);
		$choose = explode('|', $fieldsForm);
		$fields_sort = explode(',', $fields_sort);
		$will_choose = explode(',', $will_choose);
		$arr = array();

		for ($i = 0; $i < count($textFields); $i++) {
			$arr[$i + 1]['id'] = $id;
			$arr[$i + 1]['textFields'] = $textFields[$i];
			$arr[$i + 1]['fieldsDateType'] = $fieldsDateType[$i];
			$arr[$i + 1]['fieldsLength'] = $fieldsLength[$i];
			$arr[$i + 1]['fieldsNotnull'] = $fieldsNotnull[$i];
			$arr[$i + 1]['fieldsFormName'] = $fieldsFormName[$i];
			$arr[$i + 1]['fieldsCoding'] = $fieldsCoding[$i];
			$arr[$i + 1]['fields_sort'] = $fields_sort[$i];
			$arr[$i + 1]['will_choose'] = $will_choose[$i];

			if (0 < $user_id) {
				$sql = 'select ' . $textFields[$i] . ' from ' . $GLOBALS['ecs']->table('merchants_steps_fields') . (' where user_id = \'' . $user_id . '\'');
				$arr[$i + 1]['titles_centents'] = $GLOBALS['db']->getOne($sql);
			}

			$chooseForm = explode(':', $choose[$i]);
			$arr[$i + 1]['chooseForm'] = $chooseForm[0];
			$form_special = explode('+', $chooseForm[1]);
			$arr[$i + 1]['formSpecial'] = $form_special[1];

			if ($chooseForm[0] == 'input') {
				$arr[$i + 1]['inputForm'] = $form_special[0];
			}
			else if ($chooseForm[0] == 'textarea') {
				$textareaForm = explode(',', $form_special[0]);
				$arr[$i + 1]['rows'] = $textareaForm[0];
				$arr[$i + 1]['cols'] = $textareaForm[1];
			}
			else {
				if ($chooseForm[0] == 'radio' || $chooseForm[0] == 'checkbox') {
					if (!empty($form_special[0])) {
						$radioCheckbox_sort = get_radioCheckbox_sort(explode(',', $form_special[0]));

						if ($webType == 'root') {
							$radioCheckbox_sort = get_array_sort($radioCheckbox_sort, 'rc_sort');
						}

						$arr[$i + 1]['radioCheckboxForm'] = $radioCheckbox_sort;
					}
					else {
						$arr[$i + 1]['radioCheckboxForm'] = array();
					}
				}
				else if ($chooseForm[0] == 'select') {
					if (!empty($form_special[0])) {
						$arr[$i + 1]['selectList'] = explode(',', $form_special[0]);
					}
					else {
						$arr[$i + 1]['selectList'] = array();
					}
				}
				else if ($chooseForm[0] == 'other') {
					$otherForm = explode(',', $form_special[0]);
					$arr[$i + 1]['otherForm'] = $otherForm[0];

					if ($otherForm[0] == 'dateTime') {
						if ($webType == 'root') {
							$arr[$i + 1]['dateTimeForm'] = get_dateTimeForm_arr(explode('--', $otherForm[1]), explode(',', $arr[$i + 1]['titles_centents']));
						}
						else {
							$arr[$i + 1]['dateTimeForm'] = $otherForm[1];
						}
					}
					else if ($otherForm[0] == 'textArea') {
						if ($webType == 'root') {
							$arr[$i + 1]['textAreaForm'] = get_textAreaForm_arr(explode(',', $arr[$i + 1]['titles_centents']));
							$arr[$i + 1]['province_list'] = get_regions_steps(1, $arr[$i + 1]['textAreaForm']['country']);
							$arr[$i + 1]['city_list'] = get_regions_steps(2, $arr[$i + 1]['textAreaForm']['province']);
							$arr[$i + 1]['district_list'] = get_regions_steps(3, $arr[$i + 1]['textAreaForm']['city']);
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

function get_radioCheckbox_sort($radioCheckbox_sort)
{
	$arr = array();

	for ($i = 0; $i < count($radioCheckbox_sort); $i++) {
		$rc_sort = explode('*', $radioCheckbox_sort[$i]);
		$arr[$i]['radioCheckbox'] = $rc_sort[0];
		$arr[$i]['rc_sort'] = $rc_sort[1];
	}

	return $arr;
}

function get_dateTimeForm_arr($dateTime, $date_centent)
{
	$arr = array();

	for ($i = 0; $i < $dateTime[0]; $i++) {
		$arr[$i]['dateSize'] = $dateTime[1];
		$arr[$i]['dateCentent'] = $date_centent[$i];
	}

	return $arr;
}

function get_textAreaForm_arr($textArea)
{
	$arr['country'] = $textArea[0];
	$arr['province'] = $textArea[1];
	$arr['city'] = $textArea[2];
	$arr['district'] = $textArea[3];
	return $arr;
}

function get_fields_date_title_remove($tid, $objName, $type = 0)
{
	$sql = 'select * from ' . $GLOBALS['ecs']->table('merchants_steps_fields_centent') . (' where tid = \'' . $tid . '\'');
	$row = $GLOBALS['db']->getRow($sql);
	$textFields = explode(',', $row['textFields']);
	$fieldsDateType = explode(',', $row['fieldsDateType']);
	$fieldsLength = explode(',', $row['fieldsLength']);
	$fieldsNotnull = explode(',', $row['fieldsNotnull']);
	$fieldsFormName = explode(',', $row['fieldsFormName']);
	$fieldsCoding = explode(',', $row['fieldsCoding']);
	$fieldsForm = explode('|', $row['fieldsForm']);
	$arr = array();

	for ($i = 0; $i < count($textFields); $i++) {
		if ($type == 1) {
			if ($textFields[$i] != $objName) {
				$arr[$i]['textFields'] = $textFields[$i];
				$arr[$i]['fieldsDateType'] = $fieldsDateType[$i];
				$arr[$i]['fieldsLength'] = $fieldsLength[$i];
				$arr[$i]['fieldsNotnull'] = $fieldsNotnull[$i];
				$arr[$i]['fieldsFormName'] = $fieldsFormName[$i];
				$arr[$i]['fieldsCoding'] = $fieldsCoding[$i];
				$arr[$i]['fieldsForm'] = $fieldsForm[$i];
			}
		}
		else {
			$arr[$i]['textFields'] = $textFields[$i];
		}
	}

	return $arr;
}

function get_title_remove($tid, $fields, $objName)
{
	$fields = array_values($fields);

	for ($i = 0; $i < count($fields); $i++) {
		$arr[$i] = $fields[$i];
		$arr['textFields'] .= $fields[$i]['textFields'] . ',';
		$arr['fieldsDateType'] .= $fields[$i]['fieldsDateType'] . ',';
		$arr['fieldsLength'] .= $fields[$i]['fieldsLength'] . ',';
		$arr['fieldsNotnull'] .= $fields[$i]['fieldsNotnull'] . ',';
		$arr['fieldsFormName'] .= $fields[$i]['fieldsFormName'] . ',';
		$arr['fieldsCoding'] .= $fields[$i]['fieldsCoding'] . ',';
		$arr['fieldsForm'] .= $fields[$i]['fieldsForm'] . '|';
	}

	$arr['textFields'] = substr($arr['textFields'], 0, -1);
	$arr['fieldsDateType'] = substr($arr['fieldsDateType'], 0, -1);
	$arr['fieldsLength'] = substr($arr['fieldsLength'], 0, -1);
	$arr['fieldsNotnull'] = substr($arr['fieldsNotnull'], 0, -1);
	$arr['fieldsFormName'] = substr($arr['fieldsFormName'], 0, -1);
	$arr['fieldsCoding'] = substr($arr['fieldsCoding'], 0, -1);
	$arr['fieldsForm'] = substr($arr['fieldsForm'], 0, -1);
	$parent = array('textFields' => $arr['textFields'], 'fieldsDateType' => $arr['fieldsDateType'], 'fieldsLength' => $arr['fieldsLength'], 'fieldsNotnull' => $arr['fieldsNotnull'], 'fieldsFormName' => $arr['fieldsFormName'], 'fieldsCoding' => $arr['fieldsCoding'], 'fieldsForm' => $arr['fieldsForm']);
	$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_steps_fields_centent'), $parent, 'UPDATE', 'tid = \'' . $tid . '\'');
	get_add_drop_fields($objName, '', 'merchants_steps_fields', 'delete');
	return $arr;
}

function get_documentTitle_insert_update($dt_list, $cat_id, $dt_id = array())
{
	for ($i = 0; $i < count($dt_list); $i++) {
		$dt_list[$i] = trim($dt_list[$i]);
		$sql = 'select cat_id from ' . $GLOBALS['ecs']->table('merchants_documenttitle') . ' where dt_id = \'' . $dt_id[$i] . '\'';
		$catId = $GLOBALS['db']->getOne($sql);

		if (!empty($dt_list[$i])) {
			$parent = array('cat_id' => $cat_id, 'dt_title' => $dt_list[$i]);

			if (0 < $catId) {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_documenttitle'), $parent, 'UPDATE', 'dt_id = \'' . $dt_id[$i] . '\'');
			}
			else {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_documenttitle'), $parent, 'INSERT');
			}
		}
		else if (0 < $catId) {
			$sql = 'delete from ' . $GLOBALS['ecs']->table('merchants_documenttitle') . ' where dt_id = \'' . $dt_id[$i] . '\' and user_id = \'' . $_SESSION['user_id'] . '\'';
			$GLOBALS['db']->query($sql);
		}
	}
}

function get_admin_ru_id()
{
	$sql = 'select ru_id from ' . $GLOBALS['ecs']->table('admin_user') . ' where user_id = \'' . $_SESSION['admin_id'] . '\'';
	return $GLOBALS['db']->getRow($sql);
}

function get_user_category($options, $shopMain_category, $ru_id = 0, $admin_type = 0)
{
	if (0 < $ru_id) {
		$shopMain_category = get_category_child_tree($shopMain_category);
		$arr = array();

		if (!empty($shopMain_category)) {
			$category = explode(',', $shopMain_category);

			foreach ($options as $key => $row) {
				if ($row['level'] < 3) {
					for ($i = 0; $i < count($category); $i++) {
						if ($key == $category[$i]) {
							$arr[$key] = $row;
						}
					}
				}
				else {
					$sql = 'select cat_id from ' . $GLOBALS['ecs']->table('merchants_category') . ' where cat_id = \'' . $row['cat_id'] . ('\' and user_id = \'' . $ru_id . '\'');
					$uc_id = $GLOBALS['db']->getOne($sql);

					if ($admin_type == 0) {
						if (0 < $uc_id) {
							$arr[$key] = $row;
						}
					}
				}
			}
		}

		return $arr;
	}
	else {
		return $options;
	}
}

function get_category_child_tree($shopMain_category)
{
	$category = explode('-', $shopMain_category);

	for ($i = 0; $i < count($category); $i++) {
		$category[$i] = explode(':', $category[$i]);
		$twoChild = explode(',', $category[$i][1]);

		for ($j = 0; $j < count($twoChild); $j++) {
			$sql = ' select cat_id, cat_name from ' . $GLOBALS['ecs']->table('category') . ' where parent_id = \'' . $twoChild[$j] . '\'';
			$threeChild = $GLOBALS['db']->getAll($sql);
			$category[$i]['three_' . $twoChild[$j]] = get_category_three_child($threeChild);
			$category[$i]['three'] .= $category[$i][0] . ',' . $category[$i][1] . ',' . $category[$i]['three_' . $twoChild[$j]]['threeChild'] . ',';
		}

		$category[$i]['three'] = substr($category[$i]['three'], 0, -1);
	}

	$category = get_link_cat_id($category);
	$category = $category['all_cat'];
	return $category;
}

function get_category_three_child($threeChild)
{
	for ($i = 0; $i < count($threeChild); $i++) {
		if (!empty($threeChild[$i]['cat_id'])) {
			$threeChild['threeChild'] .= $threeChild[$i]['cat_id'] . ',';
		}
	}

	$threeChild['threeChild'] = substr($threeChild['threeChild'], 0, -1);
	return $threeChild;
}

function get_link_cat_id($category)
{
	for ($i = 0; $i < count($category); $i++) {
		if (!empty($category[$i]['three'])) {
			$category['all_cat'] .= $category[$i]['three'] . ',';
		}
	}

	$category['all_cat'] = substr($category['all_cat'], 0, -1);
	return $category;
}

function get_root_directory_steps($sid)
{
	$sql = 'select process_title, process_article from ' . $GLOBALS['ecs']->table('merchants_steps_process') . (' where process_steps = \'' . $sid . '\'');
	$row = $GLOBALS['db']->getRow($sql);

	if (0 < $row['process_article']) {
		$row['article_centent'] = $GLOBALS['db']->getOne('select content from ' . $GLOBALS['ecs']->table('article') . ' where article_id = \'' . $row['process_article'] . '\'');
	}

	return $row;
}

function get_root_steps_process_list($sid)
{
	$sql = 'select id, process_title, fields_next from ' . $GLOBALS['ecs']->table('merchants_steps_process') . (' where process_steps = \'' . $sid . '\' order by steps_sort ASC');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['id'] = $row['id'];
		$arr[$key]['process_title'] = $row['process_title'];
		$arr[$key]['fields_next'] = $row['fields_next'];
	}

	return $arr;
}

function get_merchants_septs_custom_info($table = '', $type = '', $id = '')
{
	if ($type == 'pingpai') {
		$id = ' and bid = \'' . $id . '\'';
	}

	$sql = 'select * from ' . $GLOBALS['ecs']->table($table) . ' where user_id = \'' . $_SESSION['user_id'] . '\'' . $id;
	return $GLOBALS['db']->getRow($sql);
}

function get_root_merchants_steps_title($pid, $user_id)
{
	$image = new cls_image(C('shop.bgcolor'));
	$brandId = isset($_REQUEST['brandId']) ? intval($_REQUEST['brandId']) : 0;
	$search_brandType = isset($_REQUEST['search_brandType']) ? htmlspecialchars($_REQUEST['search_brandType']) : '';
	$searchBrandZhInput = isset($_REQUEST['searchBrandZhInput']) ? htmlspecialchars(trim($_REQUEST['searchBrandZhInput'])) : '';
	$searchBrandEnInput = isset($_REQUEST['searchBrandEnInput']) ? htmlspecialchars(trim($_REQUEST['searchBrandEnInput'])) : '';
	$ec_shop_bid = isset($_REQUEST['ec_shop_bid']) ? intval($_REQUEST['ec_shop_bid']) : 0;
	$ec_shoprz_type = isset($_POST['ec_shoprz_type']) ? intval($_POST['ec_shoprz_type']) : 0;
	$ec_subShoprz_type = isset($_POST['ec_subShoprz_type']) ? intval($_POST['ec_subShoprz_type']) : 0;
	$ec_shop_expireDateStart = isset($_POST['ec_shop_expireDateStart']) ? htmlspecialchars(trim($_POST['ec_shop_expireDateStart'])) : '';
	$ec_shop_expireDateEnd = isset($_POST['ec_shop_expireDateEnd']) ? htmlspecialchars(trim($_POST['ec_shop_expireDateEnd'])) : '';
	$ec_shop_permanent = isset($_POST['ec_shop_permanent']) ? intval($_POST['ec_shop_permanent']) : 0;
	$ec_shop_categoryMain = isset($_POST['ec_shop_categoryMain']) ? intval($_POST['ec_shop_categoryMain']) : 0;
	$bank_name_letter = isset($_POST['ec_bank_name_letter']) ? htmlspecialchars(trim($_POST['ec_bank_name_letter'])) : $searchBrandEnInput;
	$brandName = isset($_POST['ec_brandName']) ? htmlspecialchars(trim($_POST['ec_brandName'])) : $searchBrandZhInput;
	$brandFirstChar = isset($_POST['ec_brandFirstChar']) ? htmlspecialchars(trim($_POST['ec_brandFirstChar'])) : substr($searchBrandEnInput, 0, 1);
	$brandLogo = isset($_FILES['ec_brandLogo']) ? $_FILES['ec_brandLogo'] : '';
	$brandLogo = $image->upload_image($brandLogo, 'septs_Image');
	$brandType = isset($_POST['ec_brandType']) ? intval($_POST['ec_brandType']) : 0;
	$brand_operateType = isset($_POST['ec_brand_operateType']) ? intval($_POST['ec_brand_operateType']) : 0;
	$brandEndTime = isset($_POST['ec_brandEndTime']) ? intval($_POST['ec_brandEndTime']) : '';
	$brandEndTime_permanent = isset($_POST['ec_brandEndTime_permanent']) ? intval($_POST['ec_brandEndTime_permanent']) : 0;
	$qualificationNameInput = isset($_POST['ec_qualificationNameInput']) ? $_POST['ec_qualificationNameInput'] : array();
	$qualificationImg = isset($_FILES['ec_qualificationImg']) ? $_FILES['ec_qualificationImg'] : array();
	$expiredDateInput = isset($_POST['ec_expiredDateInput']) ? $_POST['ec_expiredDateInput'] : array();
	$b_fid = isset($_POST['b_fid']) ? $_POST['b_fid'] : array();
	$ec_shoprz_brandName = isset($_POST['ec_shoprz_brandName']) ? htmlspecialchars(trim($_POST['ec_shoprz_brandName'])) : '';
	$ec_shop_class_keyWords = isset($_POST['ec_shop_class_keyWords']) ? htmlspecialchars(trim($_POST['ec_shop_class_keyWords'])) : '';
	$ec_shopNameSuffix = isset($_POST['ec_shopNameSuffix']) ? htmlspecialchars(trim($_POST['ec_shopNameSuffix'])) : '';
	$ec_rz_shopName = isset($_POST['ec_rz_shopName']) ? htmlspecialchars(trim($_POST['ec_rz_shopName'])) : '';
	$ec_hopeLoginName = isset($_POST['ec_hopeLoginName']) ? htmlspecialchars(trim($_POST['ec_hopeLoginName'])) : '';
	$shop_info = get_merchants_septs_custom_info('merchants_shop_information');

	if (0 < $ec_shop_bid) {
		$brand_info = get_merchants_septs_custom_info('merchants_shop_brand', 'pingpai', $ec_shop_bid);
	}
	else if (0 < $brandId) {
		if ($search_brandType == 'm_bran') {
			$search_brandType = 'merchants_brands';
		}
		else {
			$search_brandType = '';
		}

		$brand_info = get_brand_info($brandId, $search_brandType);
		$bank_name_letter = $brand_info['brand_letter'];
		$brandName = $brand_info['brand_name'];
		$brandFirstChar = substr($brand_info['brand_letter'], 0, 1);

		if ($search_brandType != 'merchants_brands') {
			$brandLogo = DATA_DIR . '/brandlogo/' . $brand_info['brand_logo'];
		}
		else {
			$brandLogo = $brand_info['brand_logo'];
			$brand_m = get_brand_info($brand_info['brand_name'], $search_brandType, 1);
		}
	}

	$sql = 'select tid, fields_titles, titles_annotation, steps_style, fields_special, special_type from ' . $GLOBALS['ecs']->table('merchants_steps_title') . (' where fields_steps=\'' . $pid . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$sql = 'select shop_id from ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' where user_id = \'' . $_SESSION['user_id'] . '\'';
		$shop_id = $GLOBALS['db']->getOne($sql);
		$arr[$key]['tid'] = $row['tid'];
		$arr[$key]['fields_titles'] = $row['fields_titles'];
		$arr[$key]['titles_annotation'] = $row['titles_annotation'];
		$arr[$key]['steps_style'] = $row['steps_style'];
		$arr[$key]['fields_special'] = $row['fields_special'];
		$arr[$key]['special_type'] = $row['special_type'];
		$sql = 'select * from ' . $GLOBALS['ecs']->table('merchants_steps_fields_centent') . ' where tid = \'' . $row['tid'] . '\'';
		$centent = $GLOBALS['db']->getRow($sql);
		$cententFields = get_fields_centent_info($centent['id'], $centent['textFields'], $centent['fieldsDateType'], $centent['fieldsLength'], $centent['fieldsNotnull'], $centent['fieldsFormName'], $centent['fieldsCoding'], $centent['fieldsForm'], $centent['fields_sort'], $centent['will_choose'], 'root', $user_id);
		$arr[$key]['cententFields'] = get_array_sort($cententFields, 'fields_sort');

		if ($row['steps_style'] == 1) {
			$ec_authorizeFile = $image->upload_image($_FILES['ec_authorizeFile'], 'septs_Image');
			$ec_authorizeFile = empty($ec_authorizeFile) ? $shop_info['authorizeFile'] : $ec_authorizeFile;
			$ec_shop_hypermarketFile = $image->upload_image($_FILES['ec_shop_hypermarketFile'], 'septs_Image');
			$ec_shop_hypermarketFile = empty($ec_shop_hypermarketFile) ? $shop_info['shop_hypermarketFile'] : $ec_shop_hypermarketFile;

			if ($ec_shop_permanent != 1) {
				$ec_shop_expireDateStart = empty($ec_shop_expireDateStart) ? local_date('Y-m-d H:i', $shop_info['shop_expireDateStart']) : $ec_shop_expireDateStart;
				$ec_shop_expireDateEnd = empty($ec_shop_expireDateEnd) ? local_date('Y-m-d H:i', $shop_info['shop_expireDateEnd']) : $ec_shop_expireDateEnd;
				if (!empty($ec_shop_expireDateStart) || !empty($ec_shop_expireDateEnd)) {
					$ec_shop_expireDateStart = local_strtotime($ec_shop_expireDateStart);
					$ec_shop_expireDateEnd = local_strtotime($ec_shop_expireDateEnd);
				}
			}
			else {
				$ec_shop_expireDateStart = '';
				$ec_shop_expireDateEnd = '';
			}

			if ($ec_shoprz_type == 0) {
				$ec_shoprz_type = $shop_info['shoprz_type'];
			}

			if ($ec_subShoprz_type == 0) {
				$ec_subShoprz_type = $shop_info['subShoprz_type'];
			}

			if ($ec_shop_categoryMain == 0) {
				$ec_shop_categoryMain = $shop_info['shop_categoryMain'];
			}

			$parent = array('user_id' => $_SESSION['user_id'], 'shoprz_type' => $ec_shoprz_type, 'subShoprz_type' => $ec_subShoprz_type, 'shop_expireDateStart' => $ec_shop_expireDateStart, 'shop_expireDateEnd' => $ec_shop_expireDateEnd, 'shop_permanent' => $ec_shop_permanent, 'authorizeFile' => $ec_authorizeFile, 'shop_hypermarketFile' => $ec_shop_hypermarketFile, 'shop_categoryMain' => $ec_shop_categoryMain);

			if (0 < $_SESSION['user_id']) {
				if (0 < $shop_id) {
					if ($parent['shop_expireDateStart'] == '' || $parent['shop_expireDateEnd'] == '') {
						if ($ec_shop_permanent != 1) {
							if ($shop_info['shop_permanent'] == 1) {
								$parent['shop_permanent'] = $shop_info['shop_permanent'];
							}
						}
					}

					if (empty($parent['authorizeFile'])) {
						$parent['shop_permanent'] = 0;
					}
					else {
						if ($parent['shop_expireDateStart'] == '' || $parent['shop_expireDateEnd'] == '') {
							$parent['shop_permanent'] = 1;
							$parent['shop_expireDateStart'] = '';
							$parent['shop_expireDateEnd'] = '';
						}
					}

					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_information'), $parent, 'UPDATE', 'user_id = \'' . $_SESSION['user_id'] . '\'');
				}
				else {
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_information'), $parent, 'INSERT');
				}
			}

			if ($ec_shop_permanent == 0) {
				if ($parent['shop_expireDateStart'] != '') {
					$parent['shop_expireDateStart'] = local_date('Y-m-d H:i', $shop_info['shop_expireDateStart']);
				}

				if ($parent['shop_expireDateEnd'] != '') {
					$parent['shop_expireDateEnd'] = local_date('Y-m-d H:i', $shop_info['shop_expireDateEnd']);
				}
			}
		}
		else if ($row['steps_style'] == 2) {
			if (0 < $_SESSION['user_id']) {
				if ($shop_id < 1) {
					$parent['user_id'] = $_SESSION['user_id'];
					$parent['shop_categoryMain'] = $ec_shop_categoryMain;
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_information'), $parent, 'INSERT');
				}
			}

			$arr[$key]['first_cate'] = get_first_cate_list(0, 0, array(), $_SESSION['user_id']);
			$catId_array = get_catId_array();
			$parent['user_shopMain_category'] = implode('-', $catId_array);

			if ($ec_shop_categoryMain == 0) {
				$ec_shop_categoryMain = $shop_info['shop_categoryMain'];
				$parent['shop_categoryMain'] = $ec_shop_categoryMain;
			}

			$parent['shop_categoryMain'] = $ec_shop_categoryMain;
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_information'), $parent, 'UPDATE', 'user_id = \'' . $_SESSION['user_id'] . '\'');

			if (!empty($parent['user_shopMain_category'])) {
				get_update_temporarydate_isAdd($catId_array);
			}

			get_update_temporarydate_isAdd($catId_array, 1);
		}
		else if ($row['steps_style'] == 3) {
			$arr[$key]['brand_list'] = get_septs_shop_brand_list($_SESSION['user_id']);
			if (0 < $ec_shop_bid || 0 < $brand_m['brand_id']) {
				$bank_name_letter = empty($bank_name_letter) ? $brand_info['bank_name_letter'] : $bank_name_letter;
				$brandName = empty($brandName) ? $brand_info['brandName'] : $brandName;
				$brandFirstChar = empty($brandFirstChar) ? $brand_info['brandFirstChar'] : $brandFirstChar;
				$brandLogo = empty($brandLogo) ? $brand_info['brandLogo'] : $brandLogo;
				$brandType = empty($brandType) ? $brand_info['brandType'] : $brandType;
				$brand_operateType = empty($brand_operateType) ? $brand_info['brand_operateType'] : $brand_operateType;
				$brandEndTime = empty($brandEndTime) ? $brand_info['brandEndTime'] : local_strtotime($brandEndTime);
				$brandEndTime_permanent = empty($brandEndTime_permanent) ? $brand_info['brandEndTime_permanent'] : $brandEndTime_permanent;
				$brandfile_list = get_shop_brandfile_list($ec_shop_bid);
				$arr[$key]['brandfile_list'] = $brandfile_list;
				$parent = array('user_id' => $_SESSION['user_id'], 'bank_name_letter' => $bank_name_letter, 'brandName' => $brandName, 'brandFirstChar' => $brandFirstChar, 'brandLogo' => $brandLogo, 'brandType' => $brandType, 'brand_operateType' => $brand_operateType, 'brandEndTime' => $brandEndTime, 'brandEndTime_permanent' => $brandEndTime_permanent);

				if (!empty($parent['brandEndTime'])) {
					$arr[$key]['parentType']['brandEndTime'] = local_date('Y-m-d H:i', $parent['brandEndTime']);
				}

				if (0 < $_SESSION['user_id']) {
					if ($parent['brandEndTime_permanent'] == 1) {
						$parent['brandEndTime'] = '';
					}

					if ($_SESSION['user_id'] == $brand_info['user_id']) {
						$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_brand'), $parent, 'UPDATE', 'user_id = \'' . $_SESSION['user_id'] . ('\' and bid = \'' . $ec_shop_bid . '\''));
						get_shop_brand_file($qualificationNameInput, $qualificationImg, $expiredDateInput, $b_fid, $ec_shop_bid);
					}
				}
			}
			else if (0 < $_SESSION['user_id']) {
				$parent = array('user_id' => $_SESSION['user_id'], 'bank_name_letter' => $bank_name_letter, 'brandName' => $brandName, 'brandFirstChar' => $brandFirstChar, 'brandLogo' => $brandLogo, 'brandType' => $brandType, 'brand_operateType' => $brand_operateType, 'brandEndTime' => $brandEndTime, 'brandEndTime_permanent' => $brandEndTime_permanent, 'add_time' => gmtime());

				if (!empty($bank_name_letter)) {
					$sql = 'select bid from ' . $GLOBALS['ecs']->table('merchants_shop_brand') . (' where brandName = \'' . $brandName . '\' and user_id = \'') . $_SESSION['user_id'] . '\'';
					$bRes = $GLOBALS['db']->getOne($sql);

					if (0 < $bRes) {
						$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_brand'), $parent, 'UPDATE', 'user_id = \'' . $_SESSION['user_id'] . ('\' and bid = \'' . $bRes . '\''));
						get_shop_brand_file($qualificationNameInput, $qualificationImg, $expiredDateInput, $b_fid, $bRes);
						$back_pid_key = $row['steps_style'] - 1;
						$back_url = 'merchants_steps.php?step=stepThree&pid_key=' . $back_pid_key;
						ecs_header('Location: ' . $back_url . "\n");
						exit();
					}
					else {
						$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_brand'), $parent, 'INSERT');
						$bid = $GLOBALS['db']->insert_id();
						get_shop_brand_file($qualificationNameInput, $qualificationImg, $expiredDateInput, $b_fid, $bid);
					}
				}
			}
		}
		else if ($row['steps_style'] == 4) {
			$sql = 'select bid, brandName from ' . $GLOBALS['ecs']->table('merchants_shop_brand') . ' where user_id = \'' . $_SESSION['user_id'] . '\'';
			$brand_list = $GLOBALS['db']->getAll($sql);
			$arr[$key]['brand_list'] = $brand_list;
			$ec_shoprz_brandName = empty($ec_shoprz_brandName) ? $shop_info['shoprz_brandName'] : $ec_shoprz_brandName;
			$ec_shop_class_keyWords = empty($ec_shop_class_keyWords) ? $shop_info['shop_class_keyWords'] : $ec_shop_class_keyWords;
			$ec_shopNameSuffix = empty($ec_shopNameSuffix) ? $shop_info['shopNameSuffix'] : $ec_shopNameSuffix;
			$ec_rz_shopName = empty($ec_rz_shopName) ? $shop_info['rz_shopName'] : $ec_rz_shopName;
			$ec_hopeLoginName = empty($ec_hopeLoginName) ? $shop_info['hopeLoginName'] : $ec_hopeLoginName;

			if (!empty($ec_rz_shopName)) {
				$parent = array('shoprz_brandName' => $ec_shoprz_brandName, 'shop_class_keyWords' => $ec_shop_class_keyWords, 'shopNameSuffix' => $ec_shopNameSuffix, 'rz_shopName' => $ec_rz_shopName, 'hopeLoginName' => $ec_hopeLoginName);

				if (0 < $_SESSION['user_id']) {
					if (0 < $shop_id) {
						$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_information'), $parent, 'UPDATE', 'user_id = \'' . $_SESSION['user_id'] . '\'');
					}
					else {
						$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_information'), $parent, 'INSERT');
					}
				}
			}

			$parent['shoprz_type'] = $shop_info['shoprz_type'];
		}

		$parent['brandEndTime'] = $arr[$key]['parentType']['brandEndTime'];
		$arr[$key]['parentType'] = $parent;
	}

	return $arr;
}

function get_update_temporarydate_isAdd($catId_array, $type = 0)
{
	$arr = array();

	if ($type == 0) {
		for ($i = 0; $i < count($catId_array); $i++) {
			$parentChild = explode(':', $catId_array[$i]);
			$arr[$i] = explode(',', $parentChild[1]);

			for ($j = 0; $j < count($arr[$i]); $j++) {
				$sql = 'update ' . $GLOBALS['ecs']->table('merchants_category_temporarydate') . ' set is_add = 1 ' . ' where cat_id = \'' . $arr[$i][$j] . '\'';
				$GLOBALS['db']->query($sql);
			}
		}
	}
	else {
		for ($i = 0; $i < count($catId_array); $i++) {
			$parentChild = explode(':', $catId_array[$i]);
			$arr[$i] = explode(',', $parentChild[1]);
			$cat_id = isset($_POST['permanentCat_id_' . $parentChild[0]]) ? $_POST['permanentCat_id_' . $parentChild[0]] : array();
			$dt_id = isset($_POST['permanent_title_' . $parentChild[0]]) ? $_POST['permanent_title_' . $parentChild[0]] : array();
			$permanentFile['name'] = $_FILES['permanentFile_' . $parentChild[0]]['name'];
			$permanentFile['type'] = $_FILES['permanentFile_' . $parentChild[0]]['type'];
			$permanentFile['tmp_name'] = $_FILES['permanentFile_' . $parentChild[0]]['tmp_name'];
			$permanentFile['tmp_name'] = $_FILES['permanentFile_' . $parentChild[0]]['tmp_name'];
			$permanentFile['error'] = $_FILES['permanentFile_' . $parentChild[0]]['error'];
			$permanentFile['size'] = $_FILES['permanentFile_' . $parentChild[0]]['size'];
			$permanent_date = isset($_POST['categoryId_date_' . $parentChild[0]]) ? $_POST['categoryId_date_' . $parentChild[0]] : array();

			if (0 < count($cat_id)) {
				get_merchants_dt_file_insert_update($cat_id, $dt_id, $permanentFile, $permanent_date);
			}
		}
	}

	return $arr;
}

function get_merchants_dt_file_insert_update($cat_id, $dt_id, $permanentFile, $permanent_date)
{
	$image = new cls_image(C('shop.bgcolor'));

	for ($i = 0; $i < count($cat_id); $i++) {
		$sql = 'select * from ' . $GLOBALS['ecs']->table('merchants_dt_file') . ' where cat_id = \'' . $cat_id[$i] . '\' and dt_id = \'' . $dt_id[$i] . '\' and user_id = \'' . $_SESSION['user_id'] . '\'';
		$row = $GLOBALS['db']->getRow($sql);
		$pFile = $image->upload_image('', 'septs_Image', '', 1, $permanentFile['name'][$i], $permanentFile['type'][$i], $permanentFile['tmp_name'][$i], $permanentFile['error'][$i], $permanentFile['size'][$i]);
		$pFile = empty($pFile) ? $row['permanent_file'] : $pFile;

		if (!empty($permanent_date[$i])) {
			$permanent_date[$i] = local_strtotime(trim($permanent_date[$i]));
		}
		else {
			$permanent_date[$i] = '';
		}

		if (!empty($pFile)) {
			if (!empty($permanent_date[$i])) {
				$catPermanent = 0;
			}
			else {
				$catPermanent = 1;
			}
		}
		else {
			$catPermanent = 0;
		}

		$parent = array('cat_id' => intval($cat_id[$i]), 'dt_id' => intval($dt_id[$i]), 'user_id' => $_SESSION['user_id'], 'permanent_file' => $pFile, 'permanent_date' => $permanent_date[$i], 'cate_title_permanent' => $catPermanent);

		if (0 < $row['dtf_id']) {
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_dt_file'), $parent, 'UPDATE', 'cat_id = \'' . $cat_id[$i] . '\' and dt_id = \'' . $dt_id[$i] . '\' and user_id = \'' . $_SESSION['user_id'] . '\'');
		}
		else {
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_dt_file'), $parent, 'INSERT');
		}
	}
}

function get_septs_shop_brand_list($user_id = 0)
{
	$sql = 'select bid, bank_name_letter, brandName, brandFirstChar, brandLogo, brandType, brand_operateType, brandEndTime from ' . $GLOBALS['ecs']->table('merchants_shop_brand') . ' where user_id = \'' . $user_id . '\' order by bid asc';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$key = $key + 1;
		$arr[$key]['bid'] = $row['bid'];
		$arr[$key]['bank_name_letter'] = $row['bank_name_letter'];
		$arr[$key]['brandName'] = $row['brandName'];
		$arr[$key]['brandFirstChar'] = $row['brandFirstChar'];
		$arr[$key]['brandLogo'] = $row['brandLogo'];
		$arr[$key]['brandType'] = $row['brandType'];
		$arr[$key]['brand_operateType'] = $row['brand_operateType'];
		$arr[$key]['brandEndTime'] = local_date('Y-m-d H:i', $row['brandEndTime']);
	}

	return $arr;
}

function get_shop_brand_file($qInput, $qImg, $eDinput, $b_fid, $ec_shop_bid)
{
	$image = new cls_image(C('shop.bgcolor'));

	for ($i = 0; $i < count($qInput); $i++) {
		$qInput[$i] = trim($qInput[$i]);
		$qImg[$i] = $image->upload_image('', 'septs_Image', '', 1, $qImg['name'][$i], $qImg['type'][$i], $qImg['tmp_name'][$i], $qImg['error'][$i], $qImg['size'][$i]);
		$eDinput[$i] = trim($eDinput[$i]);

		if (empty($qImg[$i])) {
			$qPermanent = 0;
		}
		else if (!empty($eDinput[$i])) {
			$qPermanent = 0;
		}
		else {
			$qPermanent = 1;
		}

		if (!empty($eDinput[$i])) {
			$eDinput[$i] = local_strtotime($eDinput[$i]);
		}
		else {
			$eDinput[$i] = '';
		}

		if (!empty($qInput[$i])) {
			$parent = array('bid' => $ec_shop_bid, 'qualificationNameInput' => $qInput[$i], 'qualificationImg' => $qImg[$i], 'expiredDateInput' => $eDinput[$i], 'expiredDate_permanent' => $qPermanent);

			if (!empty($b_fid[$i])) {
				$sql = 'select qualificationImg from ' . $GLOBALS['ecs']->table('merchants_shop_brandfile') . (' where bid = \'' . $ec_shop_bid . '\' and b_fid = \'') . $b_fid[$i] . '\'';
				$qualificationImg = $GLOBALS['db']->getOne($sql);

				if (empty($parent['qualificationImg'])) {
					$parent['qualificationImg'] = $qualificationImg;
				}

				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_brandfile'), $parent, 'UPDATE', 'bid = \'' . $ec_shop_bid . '\' and b_fid = \'' . $b_fid[$i] . '\'');
			}
			else {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_brandfile'), $parent, 'INSERT');
			}
		}
	}
}

function get_shop_brandfile_list($ec_shop_bid)
{
	$sql = 'select b_fid, bid, qualificationNameInput, qualificationImg, expiredDateInput, expiredDate_permanent from ' . $GLOBALS['ecs']->table('merchants_shop_brandfile') . (' where bid = \'' . $ec_shop_bid . '\' order by b_fid asc');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[] = $row;
		$arr[$key]['expiredDateInput'] = local_date('Y-m-d H:i', $row['expiredDateInput']);
	}

	return $arr;
}

function get_steps_title_insert_form($pid = 0)
{
	$steps_title = get_root_merchants_steps_title($pid);

	for ($i = 0; $i < count($steps_title); $i++) {
		if (is_array($steps_title[$i]['cententFields'])) {
			$cententFields = $steps_title[$i]['cententFields'];

			for ($j = 1; $j <= count($cententFields); $j++) {
				$arr['formName'] .= $cententFields[$j]['textFields'] . ',';
			}
		}
	}

	$arr['formName'] = substr($arr['formName'], 0, -1);
	return $arr;
}

function get_setps_form_insert_date($formName)
{
	$image = new cls_image(C('shop.bgcolor'));
	$formName = explode(',', $formName);
	$arr = array();

	for ($i = 0; $i < count($formName); $i++) {
		if (substr($formName[$i], -3) == 'Img') {
			$setps_thumb = $image->upload_image($_FILES[$formName[$i]], 'septs_Image');
			$textImg = $_POST['text_' . $formName[$i]];

			if (empty($setps_thumb)) {
				if (!empty($textImg)) {
					$setps_thumb = $textImg;
				}
			}

			$arr[$formName[$i]] = $setps_thumb;
		}
		else {
			$arr[$formName[$i]] = $_POST[$formName[$i]];
		}

		if (is_array($arr[$formName[$i]])) {
			$arr[$formName[$i]] = implode(',', $arr[$formName[$i]]);
		}
	}

	return $arr;
}

function get_first_cate_list($parent_id = 0, $type = 0, $catarr = array(), $user_id = 0)
{
	if ($type == 1) {
		for ($i = 0; $i < count($catarr); $i++) {
			if (!empty($catarr[$i])) {
				$sql = 'delete from' . $GLOBALS['ecs']->table('merchants_category_temporarydate') . ' where cat_id = \'' . $catarr[$i] . '\' and user_id = \'' . $user_id . '\'';
				$GLOBALS['db']->query($sql);
			}
		}

		return array();
	}
	else {
		$sql = 'select cat_id, cat_name from ' . $GLOBALS['ecs']->table('category') . (' where parent_id = \'' . $parent_id . '\'');
		return $GLOBALS['db']->getAll($sql);
	}
}

function get_child_category($cat)
{
	$arr = array();

	for ($i = 0; $i < count($cat); $i++) {
		if (!empty($cat[$i])) {
			$arr[$i] = $cat[$i];
			$arr['cat_id'] .= $cat[$i] . ',';
		}
	}

	$arr['cat_id'] = substr($arr['cat_id'], 0, -1);
	return $arr;
}

function get_add_childCategory_info($cat_id, $user_id)
{
	if (empty($cat_id)) {
		$cat_id = 0;
	}

	$sql = 'select cat_id, cat_name, parent_id from ' . $GLOBALS['ecs']->table('category') . (' where cat_id in(' . $cat_id . ') order by cat_id');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$key = $key + 1;
		$arr[$key]['cat_id'] = $row['cat_id'];
		$arr[$key]['cat_name'] = $row['cat_name'];
		$arr[$key]['parent_name'] = $GLOBALS['db']->getOne('select cat_name from ' . $GLOBALS['ecs']->table('category') . ' where cat_id = \'' . $row['parent_id'] . '\'');
		$parent = array('user_id' => $user_id, 'cat_id' => $row['cat_id'], 'parent_id' => $row['parent_id'], 'cat_name' => $row['cat_name'], 'parent_name' => $arr[$key]['parent_name']);

		if ($cat_id != 0) {
			$sql = 'select ct_id from ' . $GLOBALS['ecs']->table('merchants_category_temporarydate') . ' where cat_id = \'' . $row['cat_id'] . ('\' and user_id = \'' . $user_id . '\'');
			$ct_id = $GLOBALS['db']->getOne($sql);

			if ($ct_id <= 0) {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_category_temporarydate'), $parent, 'INSERT');
			}
		}
	}

	return $arr;
}

function get_fine_category_info($cat_id, $user_id)
{
	if ($cat_id != 0) {
		get_add_childcategory_info($cat_id, $user_id);
	}

	$sql = 'select ct_id, cat_id, cat_name, parent_name from ' . $GLOBALS['ecs']->table('merchants_category_temporarydate') . (' where user_id = \'' . $user_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$key = $key + 1;
		$arr[$key]['ct_id'] = $row['ct_id'];
		$arr[$key]['cat_id'] = $row['cat_id'];
		$arr[$key]['cat_name'] = $row['cat_name'];
		$arr[$key]['parent_name'] = $row['parent_name'];
	}

	return $arr;
}

function get_permanent_parent_cat_id($user_id = 0, $type = 0)
{
	if ($type == 1) {
		$group_by = 'group by c.parent_id';
	}
	else {
		$group_by = '';
	}

	$sql = 'select c.parent_id, mct.cat_id from ' . $GLOBALS['ecs']->table('merchants_category_temporarydate') . ' as mct ' . 'left join ' . $GLOBALS['ecs']->table('category') . ' as c on mct.cat_id = c.cat_id ' . 'where user_id = \'' . $user_id . '\' ' . $group_by;
	return $GLOBALS['db']->getAll($sql);
}

function get_catId_array($user_id = 0)
{
	if ($user_id <= 0) {
		$user_id = $_SESSION['user_id'];
	}

	$res = get_permanent_parent_cat_id($user_id);

	foreach ($res as $key => $row) {
		@$arr[$row['parent_id']] .= $row['cat_id'] . ',';
	}

	@$arr = get_explode_array($arr);
	return $arr;
}

function get_explode_array($arr)
{
	$newArr = array();
	$i = 0;

	foreach ($arr as $key => $row) {
		$newArr[$i] = substr($key . ':' . $row, 0, -1);
		$i++;
	}

	return $newArr;
}

function get_category_permanent_list($user_id)
{
	$res = get_permanent_parent_cat_id($user_id, 1);
	$arr = array();
	$arr['parentId'] = '';

	foreach ($res as $key => $row) {
		$arr[$key]['parent_id'] = $row['parent_id'];
		$arr['parentId'] .= $row['parent_id'] . ',';
	}

	$arr['parentId'] = substr($arr['parentId'], 0, -1);

	if (empty($arr['parentId'])) {
		$arr['parentId'] = 0;
	}

	$new_parentId = substr($arr['parentId'], 0, 1);

	if ($new_parentId == ',') {
		$arr['parentId'] = substr($arr['parentId'], 1);
	}

	$sql = 'select dt_id, dt_title, cat_id from ' . $GLOBALS['ecs']->table('merchants_documenttitle') . ' where cat_id in(' . $arr['parentId'] . ') order by dt_id asc';
	$res = $GLOBALS['db']->getAll($sql);
	$parentId = $arr['parentId'];
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['dt_id'] = $row['dt_id'];
		$arr[$key]['dt_title'] = $row['dt_title'];
		$arr[$key]['cat_id'] = $row['cat_id'];
		$arr[$key]['cat_name'] = $GLOBALS['db']->getOne('select cat_name from ' . $GLOBALS['ecs']->table('category') . ' where cat_id = \'' . $row['cat_id'] . '\'');
		$sql = 'select permanent_file, permanent_date, cate_title_permanent from ' . $GLOBALS['ecs']->table('merchants_dt_file') . ' where cat_id = \'' . $row['cat_id'] . '\' and dt_id = \'' . $row['dt_id'] . '\' and user_id = \'' . $user_id . '\'';
		$row = $GLOBALS['db']->getRow($sql);
		$arr[$key]['permanent_file'] = $row['permanent_file'];
		$arr[$key]['cate_title_permanent'] = $row['cate_title_permanent'];

		if (!empty($row['permanent_date'])) {
			$arr[$key]['permanent_date'] = local_date('Y-m-d H:i', $row['permanent_date']);
		}
	}

	return $arr;
}

function get_temporarydate_ctId_catParent($ct_id)
{
	$sql = 'select parent_id from ' . $GLOBALS['ecs']->table('merchants_category_temporarydate') . (' where ct_id = \'' . $ct_id . '\'');
	$parent_id = $GLOBALS['db']->getOne($sql);
	$sql = 'select ct_id from ' . $GLOBALS['ecs']->table('merchants_category_temporarydate') . (' where parent_id = \'' . $parent_id . '\'');
	$num = count($GLOBALS['db']->getAll($sql));
	$arr['parent_id'] = $parent_id;
	$arr['num'] = $num;
	return $arr;
}

function get_goods_region_name($region_id)
{
	$sql = 'select region_name from ' . $GLOBALS['ecs']->table('region') . (' where region_id = \'' . $region_id . '\'');
	return $GLOBALS['db']->getOne($sql);
}

function get_merchants_shop_info($table = '', $user_id = 0)
{
	$sql = 'select * from ' . $GLOBALS['ecs']->table($table) . (' where user_id = \'' . $user_id . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function get_license_comp_adress($steps_adress)
{
	$adress = explode(',', $steps_adress);
	$arr['province'] = '';
	$arr['city'] = '';
	$arr['province'] = get_goods_region_name($adress[1]);
	$arr['city'] = get_goods_region_name($adress[2]);

	if (!empty($arr['city'])) {
		$arr['city'] = $arr['city'] . '市';
	}

	return $arr;
}

function area_warehouse_list($region_id)
{
	$area_arr = array();
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('region_warehouse') . (' WHERE parent_id = \'' . $region_id . '\' ORDER BY region_id');
	$res = $GLOBALS['db']->query($sql);
	$i = 0;

	foreach ($res as $row) {
		$row['type'] = $row['region_type'] == 0 ? $GLOBALS['_LANG']['country'] : '';
		$row['type'] .= $row['region_type'] == 1 ? $GLOBALS['_LANG']['province'] : '';
		$row['type'] .= $row['region_type'] == 2 ? $GLOBALS['_LANG']['city'] : '';
		$row['type'] .= $row['region_type'] == 3 ? $GLOBALS['_LANG']['cantonal'] : '';
		$area_arr[$i]['region_id'] = $row['region_id'];
		$area_arr[$i]['regionid'] = $row['regionid'];
		$area_arr[$i]['parent_id'] = $row['parent_id'];
		$area_arr[$i]['region_name'] = $row['region_name'];
		$area_arr[$i]['region_type'] = $row['region_type'];
		$area_arr[$i]['agency_id'] = $row['agency_id'];
		$area_arr[$i]['type'] = $row['type'];
		$area_arr[$i]['child'] = get_child_region($row['regionid']);
		$area_arr[$i]['region_child'] = area_warehouse_list($row['region_id']);
		$i++;
	}

	return $area_arr;
}

function get_child_region($region_id = 0)
{
	$sql = 'select * from ' . $GLOBALS['ecs']->table('region') . (' where parent_id = \'' . $region_id . '\'');
	return $GLOBALS['db']->getAll($sql);
}

function warehouse_shipping_list($goods = array(), $region_id = 0, $number = 1, $goods_region = array())
{
	$sql = 'select s.shipping_id, s.shipping_name, s.shipping_code from ' . $GLOBALS['ecs']->table('shipping') . ' as s, ' . $GLOBALS['ecs']->table('shipping_area') . ' as sa ' . ' where 1 and s.shipping_id = sa.shipping_id group by s.shipping_id';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		if (substr($row['shipping_code'], 0, 5) == 'ship_') {
			unset($arr[$key]);
			continue;
		}
		else {
			$arr[$key]['shipping_id'] = $row['shipping_id'];
			$arr[$key]['shipping_name'] = $row['shipping_name'];
			$arr[$key]['shipping_code'] = $row['shipping_code'];

			if (0 < $region_id) {
				$goods['ru_id'] = $goods['user_id'];
				$shipping = get_goods_freight($goods, $region_id, $goods_region, $number, $row['shipping_code']);
				$arr[$key]['shipping_fee'] = price_format($shipping['shipping_fee'], false);
			}
		}
	}

	return $arr;
}

function get_warehouse_freight_type($region_id)
{
	$adminru = get_admin_ru_id();

	if (0 < $adminru['ru_id']) {
		$ru_id = $adminru['ru_id'];
	}
	else {
		$ru_id = 0;
	}

	$ruCat = ' and wf.user_id = \'' . $ru_id . '\' ';
	$sql = 'select wf.id, wf.configure, wf.shipping_id, wf.region_id, s.shipping_name, rw1.region_name as region_name1, rw2.region_name as region_name2, s.support_cod, s.shipping_code from ' . $GLOBALS['ecs']->table('warehouse_freight') . ' as wf ' . ' left join ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw1 on wf.warehouse_id = rw1.region_id' . ' left join ' . $GLOBALS['ecs']->table('shipping') . ' as s on wf.shipping_id = s.shipping_id' . ' left join ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw2 on wf.region_id = rw2.regionId' . (' where wf.region_id = \'' . $region_id . '\' ') . $ruCat . ' group by wf.shipping_id order by id asc';
	return $GLOBALS['db']->getAll($sql);
}

function get_warehouse_province($type = 'root', $ra_id = 0)
{
	$sql = 'SELECT region_id AS regionId, region_name FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE region_type = 1 ORDER BY region_id ASC';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['region_id'] = $row['regionId'];
		$arr[$key]['region_name'] = $row['region_name'];
		$where = '';

		if ($type == 'admin') {
			if (0 < $ra_id) {
				$where = 'ra_id <> \'' . $ra_id . '\' and ';
			}

			$where .= 'region_id = \'' . $row['regionId'] . '\'';
			$date = array('region_id');
			$region_id = get_table_date('merchants_region_info', $where, $date);

			if (0 < $region_id) {
				$arr[$key]['disabled'] = 1;
			}
			else {
				$arr[$key]['disabled'] = 0;
			}

			if (0 < $ra_id) {
				$where = 'ra_id = \'' . $ra_id . '\' and ' . 'region_id = \'' . $row['regionId'] . '\'';
				$date = array('region_id');
				$region_id = get_table_date('merchants_region_info', $where, $date);

				if (0 < $region_id) {
					$arr[$key]['checked'] = 1;
				}
				else {
					$arr[$key]['checked'] = 0;
				}
			}
		}
	}

	return $arr;
}

function get_region_city_county($city_district)
{
	$sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE parent_id = \'' . $city_district . '\' ORDER BY region_id ASC');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['region_id'] = $row['region_id'];
		$arr[$key]['region_name'] = $row['region_name'];
	}

	return $arr;
}

function get_warehouse_list_goods($region_type = 0)
{
	$sql = 'select region_id, region_name from ' . $GLOBALS['ecs']->table('region_warehouse') . (' where region_type = \'' . $region_type . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['region_id'] = $row['region_id'];
		$arr[$key]['region_name'] = $row['region_name'];
	}

	return $arr;
}

function get_warehouse_name_id($region_id = 0, $region_name = '')
{
	if (!empty($region_name)) {
		$name_type = 'region_name = \'' . $region_name . '\' and region_type = \'' . $region_id . '\'';
		$region_id = '';
		$region = 'region_id';
	}
	else {
		$name_type = '';
		$region_type = '';
		$region_id = 'region_id = \'' . $region_id . '\'';
		$region = 'region_name';
	}

	$sql = 'select ' . $region . ' from ' . $GLOBALS['ecs']->table('region_warehouse') . ' where ' . $region_id . $name_type;
	return $GLOBALS['db']->getOne($sql);
}

function get_region_name($region_id)
{
	$sql = 'SELECT region_id, region_name, parent_id FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE region_id = \'' . $region_id . '\' LIMIT 1');
	return $GLOBALS['db']->getRow($sql);
}

function get_user_address_region($user_id)
{
	$sql = 'select address_id, province, city, district from ' . $GLOBALS['ecs']->table('user_address') . (' where user_id = \'' . $user_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['address_id'] = $row['address_id'];
		$arr[$key]['province'] = $row['province'];
		$arr[$key]['city'] = $row['city'];
		$arr[$key]['district'] = $row['district'];
		$arr['region_address'] .= $row['province'] . ',' . $row['city'] . ',' . $row['district'] . ',';
	}

	$arr['region_address'] = substr($arr['region_address'], 0, -1);
	return $arr;
}

function get_user_order_area($user_id = 0)
{
	$sql = 'select country, province, city, district from ' . $GLOBALS['ecs']->table('order_info') . (' where user_id = \'' . $user_id . '\' order by order_id DESC');
	return $GLOBALS['db']->getRow($sql);
}

function get_user_area_reg($user_id)
{
	$sql = 'select ut.province, ut.city, ut.district from ' . $GLOBALS['ecs']->table('users') . ' as u ' . ' left join ' . $GLOBALS['ecs']->table('users_type') . ' as ut on u.user_id = ut.user_id' . (' where u.user_id = \'' . $user_id . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function get_province_id_warehouse($province_id)
{
	$sql = 'select parent_id from ' . $GLOBALS['ecs']->table('region_warehouse') . (' where regionId = \'' . $province_id . '\'');
	return $GLOBALS['db']->getOne($sql);
}

function get_region_name_goods($region_type = 1, $region_name = '')
{
	$sql = 'select region_id from ' . $GLOBALS['ecs']->table('region') . (' where region_name = \'' . $region_name . '\' and region_type = \'' . $region_type . '\'');
	return $GLOBALS['db']->getOne($sql);
}

function get_region_child_num($id = 0)
{
	$sql = 'select region_id from ' . $GLOBALS['ecs']->table('region') . (' where parent_id = \'' . $id . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	return count($res);
}

function get_warehouse_goods_region($province_id)
{
	$sql = 'select rw2.region_id, rw2.region_name from' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw1 left join ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw2 on rw1.parent_id = rw2.region_id' . (' where rw1.regionId = \'' . $province_id . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function get_goods_freight($goods, $warehouse_id = 0, $goods_region = array(), $buy_number = 1, $shipping_code)
{
	$sql = 'SELECT shipping_id, shipping_code, shipping_name FROM ' . $GLOBALS['ecs']->table('shipping') . (' WHERE shipping_code = \'' . $shipping_code . '\' LIMIT 1');
	$shipping = $GLOBALS['db']->getRow($sql);
	$goods_transport = array();

	if ($goods['goods_transport']) {
		$goods['goods_transport'] = substr($goods['goods_transport'], 0, -1);
		$goods['goods_transport'] = explode('-', $goods['goods_transport']);

		if ($goods['goods_transport']) {
			foreach ($goods['goods_transport'] as $key => $row) {
				$transport = explode('|', $row);
				$goods_transport[$key]['goods_id'] = $transport[0];
				$goods_transport[$key]['ru_id'] = $transport[1];
				$goods_transport[$key]['tid'] = $transport[2];
				$goods_transport[$key]['freight'] = $transport[3];
				$goods_transport[$key]['shipping_fee'] = $transport[4];
				$goods_transport[$key]['goods_number'] = $transport[5];
				$goods_transport[$key]['goods_weight'] = $transport[6];
				$goods_transport[$key]['shop_price'] = $transport[7];
			}
		}

		$order_transport = get_order_transport($goods_transport, $goods_region, $shipping['shipping_id'], $shipping_code);
	}

	if ($goods['goods_price']) {
		$street_configure = get_goods_freight_configure($goods, $warehouse_id, $goods_region['street'], $shipping_code);
		$district_configure = get_goods_freight_configure($goods, $warehouse_id, $goods_region['district'], $shipping_code);
		$city_configure = get_goods_freight_configure($goods, $warehouse_id, $goods_region['city'], $shipping_code);
		$province_configure = get_goods_freight_configure($goods, $warehouse_id, $goods_region['province'], $shipping_code);
		$default_configure = get_goods_default_configure($goods, $warehouse_id, $goods_region, $shipping_code);

		if ($street_configure) {
			$configure = $street_configure;
		}
		else if (!empty($district_configure)) {
			$configure = $district_configure;
		}
		else if (!empty($city_configure)) {
			$configure = $city_configure;
		}
		else if (!empty($province_configure)) {
			$configure = $province_configure;
		}
		else {
			$configure = $default_configure;
		}

		$goods['number'] = empty($goods['number']) ? $buy_number : $goods['number'];
		$shipping_cfg = sc_unserialize_config($configure);
		$configure_price = goods_shipping_fee($shipping_code, unserialize($configure), $goods['weight'], $goods['goods_price'], $goods['number']);
		$arr['shipping_fee'] = $configure_price;
		$arr['configure_price'] = price_format($configure_price, false);
		$arr['shipping_name'] = $shipping['shipping_name'];
		$arr['shipping_code'] = $shipping['shipping_code'];
		$arr['item_fee'] = price_format($shipping_cfg['item_fee'], false);
		$arr['base_fee'] = price_format($shipping_cfg['base_fee'], false);
		$arr['step_fee'] = price_format($shipping_cfg['step_fee'], false);
		$arr['free_money'] = price_format($shipping_cfg['free_money'], false);
		$arr['fee_compute_mode'] = $shipping_cfg['fee_compute_mode'];
		@$arr['pay_fee'] = price_format($shipping_cfg['pay_fee'], false);
	}
	else {
		$arr['shipping_fee'] = 0;
	}

	if ($order_transport['freight']) {
		$arr['shipping_fee'] += $order_transport['sprice'];
	}
	else {
		$arr['shipping_fee'] = $order_transport['sprice'];
	}

	$arr['configure_price'] = price_format($configure_price, false);
	$arr['shipping_name'] = empty($shipping['shipping_name']) ? '' : $shipping['shipping_name'];
	$arr['shipping_code'] = empty($shipping['shipping_code']) ? '' : $shipping['shipping_code'];
	$arr['warehouse_id'] = $warehouse_id;
	return $arr;
}

function get_goods_freight_configure($goods, $warehouse_id, $region_id, $shipping_code)
{
	$user_id = $goods['ru_id'];
	$date = array('shipping_id');
	$where = 'shipping_code = \'' . $shipping_code . '\'';
	$shipping_id = get_table_date('shipping', $where, $date, 2);
	$sql = 'SELECT configure FROM ' . $GLOBALS['ecs']->table('warehouse_freight') . (' where user_id = \'' . $user_id . '\' and warehouse_id = \'' . $warehouse_id . '\' and shipping_id = \'' . $shipping_id . '\' and region_id = \'' . $region_id . '\'');
	return $GLOBALS['db']->getOne($sql, true);
}

function get_goods_default_configure($goods, $warehouse_id, $region_id, $shipping_code)
{
	$user_id = $goods['ru_id'];
	$date = array('shipping_id');
	$where = 'shipping_code = \'' . $shipping_code . '\'';
	$shipping_id = get_table_date('shipping', $where, $date, 2);
	$sql = 'select * from ' . $GLOBALS['ecs']->table('warehouse_freight_tpl') . (' where user_id = \'' . $user_id . '\' and shipping_id = \'' . $shipping_id . '\' ');
	$tpl_info = $GLOBALS['db']->getAll($sql);

	foreach ($tpl_info as $tpl) {
		$tpl_status_1 = array_intersect($region_id, explode(',', $tpl['region_id']));
		$tpl_status_2 = in_array($warehouse_id, explode(',', $tpl['warehouse_id']));
		if ($tpl_status_1 && $tpl_status_2) {
			return $tpl['configure'];
		}
	}

	return false;
}

function get_ship_tpl_list($shipping_id = 0, $ru_id = 0)
{
	if (empty($ru_id)) {
		$ru_id = $_SESSION['ru_id'];
	}

	$sql = ' select * from ' . $GLOBALS['ecs']->table('warehouse_freight_tpl') . (' where shipping_id=\'' . $shipping_id . '\' and user_id=\'' . $ru_id . '\'');
	$tpl_list = $GLOBALS['db']->getAll($sql);

	foreach ($tpl_list as $key => $value) {
		if (!empty($value['region_id'])) {
			$sql = ' SELECT region_name from ' . $GLOBALS['ecs']->table('region') . ' where region_id in (' . $value['region_id'] . ') ';
			$regions = $GLOBALS['db']->getCol($sql);
			$tpl_list[$key]['regions'] = implode(',', $regions);
		}

		if (!empty($value['warehouse_id'])) {
			$sql = ' SELECT region_name from ' . $GLOBALS['ecs']->table('region_warehouse') . ' where region_id in (' . $value['warehouse_id'] . ') ';
			$warehouses = $GLOBALS['db']->getCol($sql);
			$tpl_list[$key]['warehouses'] = implode(' | ', $warehouses);
		}
	}

	return $tpl_list;
}

function get_warehouse_list($type = 0, $goods_id = 0)
{
	$sql = 'select region_id, region_name from ' . $GLOBALS['ecs']->table('region_warehouse') . (' where 1 and region_type = \'' . $type . '\'');
	return $GLOBALS['db']->getAll($sql);
}

function get_insert_warehouse_goods($goods_id = 0, $warehouse_name = array(), $warehouse_number = array(), $warehouse_price = array(), $warehouse_promote_price = array(), $user_id = 0)
{
	$add_time = gmtime();

	for ($i = 0; $i < count($warehouse_name); $i++) {
		if (!empty($warehouse_name[$i])) {
			if ($warehouse_number[$i] == 0) {
				$warehouse_number[$i] = 1;
			}

			$sql = 'select w_id from ' . $GLOBALS['ecs']->table('warehouse_goods') . (' where goods_id = \'' . $goods_id . '\' and region_id = \'') . $warehouse_name[$i] . '\'';
			$w_id = $GLOBALS['db']->getOne($sql);
			$parent = array('goods_id' => $goods_id, 'region_id' => $warehouse_name[$i], 'region_number' => intval($warehouse_number[$i]), 'warehouse_price' => floatval($warehouse_price[$i]), 'warehouse_promote_price' => floatval($warehouse_promote_price[$i]), 'user_id' => $user_id, 'add_time' => $add_time);

			if (0 < $w_id) {
				$link[] = array('text' => '返回一页', 'href' => 'goods.php?act=edit&goods_id=' . $goods_id . '&extension_code=');
				sys_msg('该商品的仓库库存已存在', 0, $link);
				break;
			}
			else {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('warehouse_goods'), $parent, 'INSERT');
			}
		}
	}
}

function get_insert_warehouse_area_goods($goods_id = 0, $area_name = array(), $region_number = array(), $region_price = array(), $region_promote_price = array(), $user_id = 0)
{
	$add_time = gmtime();

	for ($i = 0; $i < count($area_name); $i++) {
		if (!empty($area_name[$i])) {
			$sql = 'select a_id from ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' where goods_id = \'' . $goods_id . '\' and region_id = \'') . $area_name[$i] . '\'';
			$a_id = $GLOBALS['db']->getOne($sql);

			if (0 < $a_id) {
				$link[] = array('text' => '返回一页', 'href' => 'goods.php?act=edit&goods_id=' . $goods_id . '&extension_code=');
				sys_msg('该商品的地区价格已存在', 0, $link);
				break;
			}
			else {
				$sql = 'insert into ' . $GLOBALS['ecs']->table('warehouse_area_goods') . '(goods_id, region_id, region_number, region_price, region_promote_price, user_id, add_time)VALUES(\'' . $goods_id . '\',\'' . $area_name[$i] . '\',\'' . $region_number[$i] . '\',\'' . floatval($region_price[$i]) . '\',\'' . floatval($region_promote_price[$i]) . ('\',\'' . $user_id . '\',\'' . $add_time . '\')');
				$GLOBALS['db']->query($sql);
			}
		}
	}
}

function get_warehouse_goods_list($goods_id = 0)
{
	$sql = 'SELECT wg.w_id, wg.region_id, wg.region_sn, wg.region_number, wg.warehouse_price, wg.warehouse_promote_price, rw.region_name, rw.region_name, wg.give_integral, wg.rank_integral, wg.pay_integral FROM ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg, ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw ' . (' WHERE wg.region_id = rw.region_id AND wg.goods_id = \'' . $goods_id . '\' ORDER BY rw.region_id asc');
	return $GLOBALS['db']->getAll($sql);
}

function get_warehouse_area_goods_list($goods_id = 0)
{
	$sql = 'SELECT wag.a_id, wag.region_id, wag.region_sn, wag.region_number, wag.region_price, wag.region_promote_price, wag.region_sort, rw.region_name, rw.parent_id, wag.give_integral, wag.rank_integral, wag.pay_integral FROM ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag, ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw ' . (' WHERE wag.region_id = rw.region_id AND wag.goods_id = \'' . $goods_id . '\' ORDER BY rw.region_id, wag.region_sort asc');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key] = $row;
		$arr[$key]['warehouse_name'] = $GLOBALS['db']->getOne('select region_name from ' . $GLOBALS['ecs']->table('region_warehouse') . ' where region_id = \'' . $row['parent_id'] . '\'');
	}

	return $arr;
}

function get_produts_warehouse_list($goods_list)
{
	$arr = array();

	for ($i = 0; $i < count($goods_list); $i++) {
		$arr[$i]['goods_id'] = get_products_name($goods_list[$i]['goods_name'], 'goods');
		$arr[$i]['warehouse_id'] = get_products_name($goods_list[$i]['warehouse_id'], 'region_warehouse');
		$arr[$i]['goods_attr'] = $goods_list[$i]['goods_attr'];
		$arr[$i]['product_sn'] = $goods_list[$i]['product_sn'];
		$arr[$i]['product_number'] = $goods_list[$i]['product_number'];
	}

	return $arr;
}

function get_produts_warehouse_list2($goods_list, $attr_num = 0)
{
	$arr = array();

	for ($i = 0; $i < count($goods_list); $i++) {
		$arr[$i]['goods_id'] = get_products_name2($goods_list[$i]['goods_sn'], 'goods');
		$arr[$i]['warehouse_id'] = get_products_name2($goods_list[$i]['warehouse_id'], 'region_warehouse');

		for ($j = 0; $j < $attr_num; $j++) {
			if ($j == $attr_num - 1) {
				$attr_name[$j] = $goods_list[$i]['goods_attr' . $j];
				$goods_id = $GLOBALS['db']->getOne('SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_sn=\'' . $goods_list[$i]['goods_sn'] . '\'');
				$sql = 'SELECT goods_attr_id FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE attr_value=\'' . $goods_list[$i]['goods_attr' . $j] . '\' AND goods_id=\'' . $goods_id . '\'';
				$attr[$j] = $GLOBALS['db']->getOne($sql);
			}
			else {
				$attr_name[$j] = $goods_list[$i]['goods_attr' . $j] . '|';
				$goods_id = $GLOBALS['db']->getOne('SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_sn=\'' . $goods_list[$i]['goods_sn'] . '\'');
				$sql = 'SELECT goods_attr_id FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE attr_value=\'' . $goods_list[$i]['goods_attr' . $j] . '\' AND goods_id=\'' . $goods_id . '\'';
				$attr[$j] = $GLOBALS['db']->getOne($sql) . '|';
			}
		}

		$arr[$i]['goods_attr'] = implode('', $attr);
		$arr[$i]['goods_attr_name'] = implode('', $attr_name);

		if (empty($goods_list[$i]['product_sn'])) {
			$arr[$i]['product_sn'] = $goods_list[$i]['goods_sn'] . 'g_p' . $i;
		}
		else {
			$arr[$i]['product_sn'] = $goods_list[$i]['product_sn'];
		}

		$arr[$i]['product_number'] = $goods_list[$i]['product_number'];
	}

	return $arr;
}

function get_produts_list2($goods_list, $attr_num = 0)
{
	$arr = array();

	for ($i = 0; $i < count($goods_list); $i++) {
		$arr[$i]['goods_id'] = get_products_name2($goods_list[$i]['goods_sn'], 'goods');
		$arr[$i]['warehouse_id'] = 0;

		for ($j = 0; $j < $attr_num; $j++) {
			if ($j == $attr_num - 1) {
				$attr_name[$j] = $goods_list[$i]['goods_attr' . $j];
				$goods_id = $GLOBALS['db']->getOne('SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_sn=\'' . $goods_list[$i]['goods_sn'] . '\'');
				$sql = 'SELECT goods_attr_id FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE attr_value=\'' . $goods_list[$i]['goods_attr' . $j] . '\' AND goods_id=\'' . $goods_id . '\'';
				$attr[$j] = $GLOBALS['db']->getOne($sql);
			}
			else {
				$attr_name[$j] = $goods_list[$i]['goods_attr' . $j] . '|';
				$goods_id = $GLOBALS['db']->getOne('SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_sn=\'' . $goods_list[$i]['goods_sn'] . '\'');
				$sql = 'SELECT goods_attr_id FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE attr_value=\'' . $goods_list[$i]['goods_attr' . $j] . '\' AND goods_id=\'' . $goods_id . '\'';
				$attr[$j] = $GLOBALS['db']->getOne($sql) . '|';
			}
		}

		$arr[$i]['goods_attr'] = implode('', $attr);
		$arr[$i]['goods_attr_name'] = implode('', $attr_name);

		if (empty($goods_list[$i]['product_sn'])) {
			$arr[$i]['product_sn'] = $goods_list[$i]['goods_sn'] . 'g_p' . $i;
		}
		else {
			$arr[$i]['product_sn'] = $goods_list[$i]['product_sn'];
		}

		$arr[$i]['product_number'] = $goods_list[$i]['product_number'];
	}

	return $arr;
}

function get_insert_produts_warehouse($goods_list)
{
	$arr = array();

	for ($i = 0; $i < count($goods_list); $i++) {
		if (0 < $goods_list[$i]['goods_id']) {
			$other['goods_id'] = $goods_list[$i]['goods_id'];
			$goods_attr = get_produts_warehouse_attr_list($goods_list[$i]['goods_attr'], $goods_list[$i]['goods_id']);
			$other['goods_attr'] = $goods_attr['goods_attr'];
			$other['warehouse_id'] = $goods_list[$i]['warehouse_id'];
			$other['product_sn'] = $goods_list[$i]['product_sn'];
			$other['product_number'] = $goods_list[$i]['product_number'];
			$sql = 'select product_id from ' . $GLOBALS['ecs']->table('products_warehouse') . ' where goods_id = \'' . $other['goods_id'] . '\'' . ' and goods_attr = \'' . $other['goods_attr'] . '\'' . ' and warehouse_id = \'' . $other['warehouse_id'] . '\'';
			$res = $GLOBALS['db']->getOne($sql);

			if (0 < $res) {
				$return = 1;
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('products_warehouse'), $other, 'UPDATE', 'goods_id = \'' . $other['goods_id'] . '\'' . ' and goods_attr = \'' . $other['goods_attr'] . '\'' . ' and warehouse_id = \'' . $other['warehouse_id'] . '\'');
			}
			else {
				$return = 0;
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('products_warehouse'), $other, 'INSERT');
			}
		}
	}

	return $return;
}

function get_produts_area_list2($goods_list, $attr_num = 0)
{
	$arr = array();

	for ($i = 0; $i < count($goods_list); $i++) {
		$arr[$i]['goods_id'] = get_products_name2($goods_list[$i]['goods_sn'], 'goods');
		$arr[$i]['area_id'] = get_products_name2($goods_list[$i]['area_id'], 'region_warehouse');

		for ($j = 0; $j < $attr_num; $j++) {
			if ($j == $attr_num - 1) {
				$attr_name[$j] = $goods_list[$i]['goods_attr' . $j];
				$goods_id = $GLOBALS['db']->getOne('SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_sn=\'' . $goods_list[$i]['goods_sn'] . '\'');
				$sql = 'SELECT goods_attr_id FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE attr_value=\'' . $goods_list[$i]['goods_attr' . $j] . '\' AND goods_id=\'' . $goods_id . '\'';
				$attr[$j] = $GLOBALS['db']->getOne($sql);
			}
			else {
				$attr_name[$j] = $goods_list[$i]['goods_attr' . $j] . '|';
				$goods_id = $GLOBALS['db']->getOne('SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_sn=\'' . $goods_list[$i]['goods_sn'] . '\'');
				$sql = 'SELECT goods_attr_id FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE attr_value=\'' . $goods_list[$i]['goods_attr' . $j] . '\' AND goods_id=\'' . $goods_id . '\'';
				$attr[$j] = $GLOBALS['db']->getOne($sql) . '|';
			}
		}

		$arr[$i]['goods_attr'] = implode('', $attr);
		$arr[$i]['goods_attr_name'] = implode('', $attr_name);

		if (empty($goods_list[$i]['product_sn'])) {
			$arr[$i]['product_sn'] = $goods_list[$i]['goods_sn'] . 'g_p' . $i;
		}
		else {
			$arr[$i]['product_sn'] = $goods_list[$i]['product_sn'];
		}

		$arr[$i]['product_number'] = $goods_list[$i]['product_number'];
	}

	return $arr;
}

function get_produts_area_list($goods_list)
{
	$arr = array();

	for ($i = 0; $i < count($goods_list); $i++) {
		$arr[$i]['goods_id'] = get_products_name($goods_list[$i]['goods_name'], 'goods');
		$arr[$i]['area_id'] = get_products_name($goods_list[$i]['area_id'], 'region_warehouse');
		$arr[$i]['goods_attr'] = $goods_list[$i]['goods_attr'];
		$arr[$i]['product_sn'] = $goods_list[$i]['product_sn'];
		$arr[$i]['product_number'] = $goods_list[$i]['product_number'];
	}

	return $arr;
}

function get_insert_produts_area($goods_list)
{
	$arr = array();

	for ($i = 0; $i < count($goods_list); $i++) {
		if (0 < $goods_list[$i]['goods_id']) {
			$other['goods_id'] = $goods_list[$i]['goods_id'];
			$goods_attr = get_produts_warehouse_attr_list($goods_list[$i]['goods_attr'], $goods_list[$i]['goods_id']);
			$other['goods_attr'] = $goods_attr['goods_attr'];
			$other['area_id'] = $goods_list[$i]['area_id'];
			$other['product_sn'] = $goods_list[$i]['product_sn'];
			$other['product_number'] = $goods_list[$i]['product_number'];
			$sql = 'select product_id from ' . $GLOBALS['ecs']->table('products_area') . ' where goods_id = \'' . $other['goods_id'] . '\'' . ' and goods_attr = \'' . $other['goods_attr'] . '\'' . ' and area_id = \'' . $other['area_id'] . '\'';
			$res = $GLOBALS['db']->getOne($sql);

			if (0 < $res) {
				$return = 1;
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('products_area'), $other, 'UPDATE', 'goods_id = \'' . $other['goods_id'] . '\'' . ' and goods_attr = \'' . $other['goods_attr'] . '\'' . ' and area_id = \'' . $other['area_id'] . '\'');
			}
			else {
				$return = 0;
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('products_area'), $other, 'INSERT');
			}
		}
	}

	return $return;
}

function get_produts_warehouse_attr_list($goods_attr = '', $goods_id = 0)
{
	$goods_attr = explode(',', $goods_attr);
	$arr = array();

	for ($i = 0; $i < count($goods_attr); $i++) {
		$sql = 'select goods_attr_id, attr_value from ' . $GLOBALS['ecs']->table('goods_attr') . (' where goods_id = \'' . $goods_id . '\' and attr_value = \'') . $goods_attr[$i] . '\'';
		$row = $GLOBALS['db']->getRow($sql);
		$arr[$i]['goods_attr_id'] = $row['goods_attr_id'];
		$arr[$i]['attr_value'] = $row['attr_value'];
		$arr['goods_attr'] .= $row['goods_attr_id'] . '|';
	}

	$arr['goods_attr'] = substr($arr['goods_attr'], 0, -1);
	return $arr;
}

function get_products_name($name, $table)
{
	$as = '';

	if ($table === 'goods') {
		$select = 'goods_id';
		$whereName = 'goods_name = \'' . $name . '\' and is_delete = 0';
	}
	else if ($table === 'region_warehouse') {
		$select = 'region_id';
		$whereName = 'region_name = \'' . $name . '\'';
	}

	$sql = 'select ' . $select . ' from ' . $GLOBALS['ecs']->table($table) . ' where ' . $whereName;
	return $GLOBALS['db']->getOne($sql);
}

function get_goods_bacth_warehouse_list($goods_list)
{
	$arr = array();

	for ($i = 0; $i < count($goods_list); $i++) {
		$where_goods = 'goods_name = \'' . $goods_list[$i]['goods_name'] . '\'';
		$where_region = 'region_name = \'' . $goods_list[$i]['warehouse_name'] . '\'';
		$arr[$i]['user_id'] = get_table_date('goods', $where_goods, array('user_id'), 2);
		$arr[$i]['goods_id'] = get_table_date('goods', $where_goods, array('goods_id'), 2);
		$arr[$i]['region_id'] = get_table_date('region_warehouse', $where_region, array('region_id'), 2);
		$arr[$i]['region_number'] = $goods_list[$i]['warehouse_number'];
		$arr[$i]['warehouse_price'] = $goods_list[$i]['warehouse_price'];
		$arr[$i]['warehouse_promote_price'] = $goods_list[$i]['warehouse_promote_price'];
		$arr[$i]['add_time'] = gmtime();
	}

	return $arr;
}

function get_insert_bacth_warehouse($goods_list)
{
	$arr = array();

	for ($i = 0; $i < count($goods_list); $i++) {
		if (0 < $goods_list[$i]['goods_id']) {
			if (empty($goods_list[$i]['warehouse_price'])) {
				$goods_list[$i]['warehouse_price'] = 0;
			}

			if (empty($goods_list[$i]['warehouse_promote_price'])) {
				$goods_list[$i]['warehouse_promote_price'] = 0;
			}

			$other['user_id'] = $goods_list[$i]['user_id'];
			$other['goods_id'] = $goods_list[$i]['goods_id'];
			$other['region_id'] = $goods_list[$i]['region_id'];
			$other['region_number'] = $goods_list[$i]['region_number'];
			$other['warehouse_price'] = $goods_list[$i]['warehouse_price'];
			$other['warehouse_promote_price'] = $goods_list[$i]['warehouse_promote_price'];
			$other['add_time'] = $goods_list[$i]['add_time'];
			$sql = 'select w_id from ' . $GLOBALS['ecs']->table('warehouse_goods') . ' where user_id = \'' . $other['user_id'] . '\' and goods_id = \'' . $other['goods_id'] . '\'' . ' and region_id = \'' . $other['region_id'] . '\'';
			$res = $GLOBALS['db']->getOne($sql);

			if (0 < $res) {
				$return = 1;
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('warehouse_goods'), $other, 'UPDATE', ' user_id = \'' . $other['user_id'] . '\' and goods_id = \'' . $other['goods_id'] . '\'' . ' and region_id = \'' . $other['region_id'] . '\'');
			}
			else {
				$return = 0;
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('warehouse_goods'), $other, 'INSERT');
			}
		}
	}

	return $return;
}

function get_goods_bacth_area_list($goods_list)
{
	$arr = array();

	for ($i = 0; $i < count($goods_list); $i++) {
		$where_goods = 'goods_name = \'' . $goods_list[$i]['goods_name'] . '\'';
		$where_region = 'region_name = \'' . $goods_list[$i]['area_name'] . '\'';
		$arr[$i]['user_id'] = get_table_date('goods', $where_goods, array('user_id'), 2);
		$arr[$i]['goods_id'] = get_table_date('goods', $where_goods, array('goods_id'), 2);
		$arr[$i]['region_id'] = get_table_date('region_warehouse', $where_region, array('region_id'), 2);
		$arr[$i]['region_number'] = $goods_list[$i]['region_number'];
		$arr[$i]['region_price'] = $goods_list[$i]['region_price'];
		$arr[$i]['region_promote_price'] = $goods_list[$i]['region_promote_price'];
		$arr[$i]['add_time'] = gmtime();
		$arr[$i]['region_sort'] = $goods_list[$i]['region_sort'];
	}

	return $arr;
}

function get_insert_bacth_area($goods_list)
{
	$arr = array();

	for ($i = 0; $i < count($goods_list); $i++) {
		if (0 < $goods_list[$i]['goods_id']) {
			if (empty($goods_list[$i]['region_price'])) {
				$goods_list[$i]['region_price'] = 0;
			}

			if (empty($goods_list[$i]['region_promote_price'])) {
				$goods_list[$i]['region_promote_price'] = 0;
			}

			$other['user_id'] = $goods_list[$i]['user_id'];
			$other['goods_id'] = $goods_list[$i]['goods_id'];
			$other['region_id'] = $goods_list[$i]['region_id'];
			$other['region_number'] = $goods_list[$i]['region_number'];
			$other['region_price'] = $goods_list[$i]['region_price'];
			$other['region_promote_price'] = $goods_list[$i]['region_promote_price'];
			$other['add_time'] = $goods_list[$i]['add_time'];
			$other['region_sort'] = $goods_list[$i]['region_sort'];
			$sql = 'select a_id from ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' where user_id = \'' . $other['user_id'] . '\' and goods_id = \'' . $other['goods_id'] . '\'' . ' and region_id = \'' . $other['region_id'] . '\'';
			$res = $GLOBALS['db']->getOne($sql);
			$arr['goods_id'] = $other['goods_id'];

			if (0 < $res) {
				$arr['return'] = 1;
				$return = $arr;
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('warehouse_area_goods'), $other, 'UPDATE', ' user_id = \'' . $other['user_id'] . '\' and goods_id = \'' . $other['goods_id'] . '\'' . ' and region_id = \'' . $other['region_id'] . '\'');
			}
			else {
				$arr['return'] = 0;
				$return = $arr;
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('warehouse_area_goods'), $other, 'INSERT');
			}
		}
	}

	return $return;
}

function get_goods_bacth_area_attr_list($goods_list)
{
	$arr = array();

	for ($i = 0; $i < count($goods_list); $i++) {
		$where_goods = 'goods_name = \'' . $goods_list[$i]['goods_name'] . '\'';
		$where_region = 'region_name = \'' . $goods_list[$i]['area_name'] . '\'';
		$where_attr = 'attr_value = \'' . $goods_list[$i]['attr_name'] . '\'';
		$arr[$i]['goods_id'] = get_table_date('goods', $where_goods, array('goods_id'), 2);
		$arr[$i]['area_id'] = get_table_date('region_warehouse', $where_region, array('region_id'), 2);
		$arr[$i]['goods_attr_id'] = get_table_date('goods_attr', $where_attr, array('goods_attr_id'), 2);
		$arr[$i]['attr_price'] = $goods_list[$i]['attr_price'];
		$arr[$i]['attr_number'] = $goods_list[$i]['attr_number'];
	}

	return $arr;
}

function get_insert_bacth_area_attr($goods_list)
{
	$arr = array();

	for ($i = 0; $i < count($goods_list); $i++) {
		if (0 < $goods_list[$i]['goods_id']) {
			if (empty($goods_list[$i]['attr_price'])) {
				$goods_list[$i]['attr_price'] = 0;
			}

			$other['goods_id'] = $goods_list[$i]['goods_id'];
			$other['area_id'] = $goods_list[$i]['area_id'];
			$other['goods_attr_id'] = $goods_list[$i]['goods_attr_id'];
			$other['attr_price'] = $goods_list[$i]['attr_price'];
			$other['attrNumber'] = $goods_list[$i]['attr_number'];
			$sql = 'select id from ' . $GLOBALS['ecs']->table('warehouse_area_attr') . ' where goods_id = \'' . $other['goods_id'] . '\' and area_id = \'' . $other['area_id'] . '\'' . ' and goods_attr_id = \'' . $other['goods_attr_id'] . '\'';
			$res = $GLOBALS['db']->getOne($sql);
			$arr['goods_id'] = $other['goods_id'];

			if (0 < $res) {
				$arr['return'] = 1;
				$return = $arr;
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('warehouse_area_attr'), $other, 'UPDATE', ' goods_id = \'' . $other['goods_id'] . '\' and area_id = \'' . $other['area_id'] . '\'' . ' and goods_attr_id = \'' . $other['goods_attr_id'] . '\'');
			}
			else {
				$arr['return'] = 0;
				$return = $arr;
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('warehouse_area_attr'), $other, 'INSERT');
			}
		}
	}

	return $return;
}

function get_warehouse_id_attr_number($goods_id = 0, $attr_id = '', $seller_id = 0, $warehouse_id = 0, $area_id = 0, $model_attr = '', $store_id = 0)
{
	if (empty($model_attr)) {
		$model_attr = get_table_date('goods', 'goods_id = \'' . $goods_id . '\'', array('model_attr'), 2);
	}

	$where = '';

	if (empty($attr_id)) {
		$attr_id = 0;
	}
	else {
		if (strpos($attr_id, '|') !== false) {
			$attr_arr = explode('|', $attr_id);
		}
		else {
			$attr_arr = explode(',', $attr_id);
		}

		foreach ($attr_arr as $key => $val) {
			$attr_type = get_goods_attr_id(array('goods_id' => $goods_id, 'goods_attr_id' => $val), array('a.attr_type'));
			if (($attr_type == 0 || $attr_type == 2) && $attr_arr[$key]) {
				unset($attr_arr[$key]);
			}
		}

		foreach ($attr_arr as $key => $val) {
			$where .= ' AND FIND_IN_SET(\'' . $val . '\', REPLACE(goods_attr, \'|\', \',\')) ';
		}
	}

	$select = '';

	if (0 < $store_id) {
		$table = 'store_products';
		$where .= ' AND store_id = \'' . $store_id . '\'';
	}
	else {
		$select .= ', product_price, product_promote_price, product_market_price, bar_code';

		if ($model_attr == 1) {
			$table = 'products_warehouse';
			$where .= ' AND warehouse_id = \'' . $warehouse_id . '\'';
		}
		else if ($model_attr == 2) {
			$table = 'products_area';
			$where .= ' AND area_id = \'' . $area_id . '\'';
		}
		else {
			$table = 'products';
		}
	}

	$sql = 'SELECT product_id, product_number, product_sn ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE goods_id = \'' . $goods_id . '\'') . $where . ' ORDER BY product_id DESC LIMIT 1';
	$row = $GLOBALS['db']->getRow($sql);

	if ($row) {
		$row['product_table'] = $table;
	}

	return $row;
}

function get_goods_order_shipping_fee($goods = array(), $region = '', $shipping_id = 0)
{
	$arr = array();
	$arr['shipping_fee'] = 0;
	$cart_goods = get_warehouse_cart_goods_info($goods, 1, $region, $shipping_id);
	$arr['shipping_fee'] = $cart_goods['shipping']['shipping_fee'];
	$arr['ru_list'] = $cart_goods['ru_list'];
	return $arr;
}

function get_all_warehouse_area_count()
{
	$sql = 'select region_id, region_name from ' . $GLOBALS['ecs']->table('region_warehouse') . ' where parent_id = 0';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $row) {
		$arr[$row['region_id']]['region_id'] = $row['region_id'];
		$arr['region_id'] .= $row['region_id'] . ',';
	}

	$arr['region_id'] = substr($arr['region_id'], 0, -1);

	if (!empty($arr['region_id'])) {
		$sql = 'select count(*) from ' . $GLOBALS['ecs']->table('region_warehouse') . ' where parent_id in(' . $arr['region_id'] . ')';
		$count = $GLOBALS['db']->getOne($sql);
	}
	else {
		$count = 0;
	}

	return $count;
}

function get_warehouse_area_list($warehouse_id = 0)
{
	$sql = 'select region_id, region_name from ' . $GLOBALS['ecs']->table('region_warehouse') . (' where parent_id = \'' . $warehouse_id . '\'');
	return $GLOBALS['db']->getAll($sql);
}

function get_area_info($province_id = 0)
{
	$sql = 'select region_id, region_name from ' . $GLOBALS['ecs']->table('region_warehouse') . (' where regionId = \'' . $province_id . '\' LIMIT 1');
	return $GLOBALS['db']->getRow($sql);
}

function get_new_goods_attribute($goods_id, $_attribute = array())
{
	$arr = array();

	foreach ($_attribute as $key => $row) {
		$arr[$key] = $row;
		$arr[$key]['attr_valuesId'] = get_goods_attr_values_id($row['attr_values'], $row['goods_attr_id']);
		$arr[$key]['goods_attr'] = get_attribute_goods_attr($row['attr_id']);
		$arr[$key]['goods_attr'] = product_list($goods_id, '', $arr[$key]['goods_attr']['goods_attr_id']);
	}

	return $arr;
}

function get_attribute_goods_attr($attr_id = 0)
{
	$sql = 'select goods_attr_id from ' . $GLOBALS['ecs']->table('goods_attr') . (' where attr_id = \'' . $attr_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key] = $row;
		$arr['goods_attr_id'] .= $row['goods_attr_id'] . ',';
	}

	if (!empty($arr['goods_attr_id'])) {
		$arr['goods_attr_id'] = substr($arr['goods_attr_id'], 0, -1);
	}

	return $arr;
}

function get_goods_attr_values_id($attr_values = array(), $goods_attr_id = array())
{
	$arr = array();

	for ($i = 0; $i < count($attr_values); $i++) {
		$arr[$i]['attr_value'] = $attr_values[$i];
		$arr[$i]['goods_attr_id'] = $goods_attr_id[$i];
	}

	return $arr;
}

function get_goods_attr_nameId($goods_id = 0, $attr_id = 0, $attr_value = '')
{
	$sql = 'select goods_attr_id from ' . $GLOBALS['ecs']->table('goods_attr') . (' where goods_id = \'' . $goods_id . '\' and attr_id = \'' . $attr_id . '\' and attr_value = \'' . $attr_value . '\'');
	return $GLOBALS['db']->getOne($sql);
}

function get_fine_warehouse_area_all($parent_id = 0, $goods_id = 0, $goods_attr_id = 0)
{
	$sql = 'select region_id, region_name, parent_id from ' . $GLOBALS['ecs']->table('region_warehouse') . (' where parent_id = \'' . $parent_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['region_id'] = $row['region_id'];
		$arr[$key]['region_name'] = $row['region_name'];

		if ($row['parent_id'] == 0) {
			$arr[$key]['child'] = get_fine_warehouse_area_all($row['region_id'], $goods_id, $goods_attr_id);
		}

		$sql = 'select * from ' . $GLOBALS['ecs']->table('warehouse_area_attr') . (' where goods_id = \'' . $goods_id . '\' and goods_attr_id = \'' . $goods_attr_id . '\' and area_id = \'') . $row['region_id'] . '\'';
		$area_attr = $GLOBALS['db']->getRow($sql);
		$arr[$key]['area_attr'] = $area_attr;
	}

	return $arr;
}

function get_area_attr_price_insert($area_name, $goods_id, $goods_attr_id)
{
	$arr = array();

	for ($i = 0; $i < count($area_name); $i++) {
		if (!empty($area_name[$i])) {
			$parent = array('goods_id' => $goods_id, 'goods_attr_id' => $goods_attr_id, 'area_id' => $area_name[$i], 'attr_price' => $_POST['attrPrice_' . $area_name[$i]]);
			$sql = 'select id from ' . $GLOBALS['ecs']->table('warehouse_area_attr') . (' where goods_id = \'' . $goods_id . '\' and goods_attr_id = \'' . $goods_attr_id . '\' and area_id = \'') . $area_name[$i] . '\'';
			$id = $GLOBALS['db']->getOne($sql);

			if (0 < $id) {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('warehouse_area_attr'), $parent, 'UPDATE', 'goods_id = \'' . $goods_id . '\' and goods_attr_id = \'' . $goods_attr_id . '\' and area_id = \'' . $area_name[$i] . '\'');
			}
			else {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('warehouse_area_attr'), $parent, 'INSERT');
			}
		}
	}
}

function get_fine_warehouse_all($parent_id = 0, $goods_id = 0, $goods_attr_id = 0)
{
	$sql = 'select rw.region_id, rw.region_name, wa.attr_price from ' . $GLOBALS['ecs']->table('region_warehouse') . ' as rw' . ' left join ' . $GLOBALS['ecs']->table('warehouse_attr') . (' as wa on rw.region_id = wa.warehouse_id and wa.goods_id = \'' . $goods_id . '\' and wa.goods_attr_id = \'' . $goods_attr_id . '\'') . (' where rw.parent_id = \'' . $parent_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['region_id'] = $row['region_id'];
		$arr[$key]['region_name'] = $row['region_name'];
		$arr[$key]['attr_price'] = $row['attr_price'];
	}

	return $arr;
}

function get_warehouse_attr_price_insert($warehouse_name, $goods_id, $goods_attr_id)
{
	$arr = array();

	for ($i = 0; $i < count($warehouse_name); $i++) {
		if (!empty($warehouse_name[$i])) {
			$parent = array('goods_id' => $goods_id, 'goods_attr_id' => $goods_attr_id, 'warehouse_id' => $warehouse_name[$i], 'attr_price' => $_POST['attr_price_' . $warehouse_name[$i]]);
			$sql = 'select id from ' . $GLOBALS['ecs']->table('warehouse_attr') . (' where goods_id = \'' . $goods_id . '\' and goods_attr_id = \'' . $goods_attr_id . '\' and warehouse_id = \'') . $warehouse_name[$i] . '\'';
			$id = $GLOBALS['db']->getOne($sql);

			if (0 < $id) {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('warehouse_attr'), $parent, 'UPDATE', 'goods_id = \'' . $goods_id . '\' and goods_attr_id = \'' . $goods_attr_id . '\' and warehouse_id = \'' . $warehouse_name[$i] . '\'');
			}
			else {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('warehouse_attr'), $parent, 'INSERT');
			}
		}
	}
}

function get_order_child_sn($order_id = 0, $ru_id = 0)
{
	$time = explode(' ', microtime());
	$time = $time[1] . $time[0] * 1000;
	$time = explode('.', $time);
	$time = isset($time[1]) ? $time[1] : 0;
	$time = date('YmdHis') + $time;
	mt_srand((double) microtime() * 1000000);
	return $time . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function get_main_order_info($order_id = 0, $type = 0)
{
	$sql = 'select * from ' . $GLOBALS['ecs']->table('order_info') . (' where order_id = \'' . $order_id . '\'');
	$row = $GLOBALS['db']->getRow($sql);

	if ($type == 1) {
		$row['all_ruId'] = get_main_order_goods_info($order_id, 1);
		$ru_id = explode(',', $row['all_ruId']['ru_id']);

		if (1 < count($ru_id)) {
			$row['order_goods'] = get_main_order_goods_info($order_id);
			$row['newInfo'] = get_new_ru_goods_info($row['all_ruId'], $row['order_goods']);
			$row['newOrder'] = get_new_order_info($row['newInfo']);
			$row['orderBonus'] = get_new_order_info($row['newInfo'], 1, $row['bonus_id']);
			$row['orderFavourable'] = get_new_order_info($row['newInfo'], 2);
			$row['shipping_code'] = !empty($row['shipping_code']) ? $row['shipping_code'] : 0;
		}
	}

	return $row;
}

function get_main_order_goods_info($order_id = 0, $type = 0)
{
	$sql = 'SELECT og.*, g.goods_weight as goodsweight, g.is_shipping FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og, ' . $GLOBALS['ecs']->table('goods') . ' as g' . (' WHERE og.goods_id = g.goods_id AND og.order_id = \'' . $order_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	if ($type == 1) {
		$arr['ru_id'] = '';
	}

	foreach ($res as $key => $row) {
		$sql = 'SELECT shipping_type FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $row['order_id'] . '\'';
		$row['shipping_type'] = $GLOBALS['db']->getOne($sql, true);

		if ($type == 0) {
			$arr[] = $row;
		}
		else {
			$arr['ru_id'] .= $row['ru_id'] . ',';
		}
	}

	if ($type == 1) {
		$arr['ru_id'] = explode(',', substr($arr['ru_id'], 0, -1));
		$arr['ru_id'] = array_unique($arr['ru_id']);
		$arr['ru_id'] = implode(',', $arr['ru_id']);
	}

	return $arr;
}

function get_new_ru_goods_info($all_ruId = '', $order_goods = array())
{
	$all_ruId = $all_ruId['ru_id'];
	$arr = array();

	if (!empty($all_ruId)) {
		$all_ruId = explode(',', $all_ruId);
		$all_ruId = array_values($all_ruId);
	}

	if ($all_ruId) {
		for ($i = 0; $i < count($order_goods); $i++) {
			for ($j = 0; $j < count($all_ruId); $j++) {
				if ($order_goods[$i]['ru_id'] == $all_ruId[$j]) {
					$arr[$all_ruId[$j]][$i] = $order_goods[$i];
				}
			}
		}
	}

	return $arr;
}

function get_new_order_info($newInfo, $type = 0, $bonus_id = 0)
{
	$arr = array();

	if ($type == 0) {
		foreach ($newInfo as $key => $row) {
			$arr[$key]['goods_amount'] = 0;
			$arr[$key]['shopping_fee'] = 0;
			$arr[$key]['goods_id'] = 0;
			$arr[$key]['ru_list'] = get_cart_goods_combined_freight($row, 2, '', $key);
			$row = array_values($row);

			for ($j = 0; $j < count($row); $j++) {
				$arr[$key]['goods_id'] = $row[$j]['goods_id'];
				$goods_amount = $row[$j]['goods_price'] * $row[$j]['goods_number'];

				if (0 < $goods_amount) {
					$goods_con = get_con_goods_amount($goods_amount, $row[$j]['goods_id'], 0, 0, $row[$j]['parent_id']);
					$goods_con['amount'] = explode(',', $goods_con['amount']);
					$amount = min($goods_con['amount']);
					$arr[$key]['goods_amount'] += $amount;
				}
				else {
					$arr[$key]['goods_amount'] += $row[$j]['goods_price'] * $row[$j]['goods_number'];
				}

				$arr[$key]['shopping_fee'] = $arr[$key]['ru_list']['shipping_fee'];
			}
		}
	}
	else if ($type == 1) {
		foreach ($newInfo as $key => $row) {
			$arr[$key]['user_id'] = $key;
			$bonus = get_bonus_merchants($bonus_id, $key);
			$arr[$key]['bonus'] = $bonus;
		}
	}
	else if ($type == 2) {
		foreach ($newInfo as $key => $row) {
			$arr[$key]['user_id'] = $key;

			if (0 < $key) {
				$arr[$key]['compute_discount'] = compute_discount($type, $row, 1);
			}
			else {
				$arr[$key]['compute_discount'] = array(
	'discount' => 0,
	'name'     => array()
	);
			}
		}
	}

	return $arr;
}

function get_insert_order_goods_single($orderInfo, $row, $order_id, $ru_number)
{
	$newOrder = $orderInfo['newOrder'];
	$orderBonus = $orderInfo['orderBonus'];
	$newInfo = $orderInfo['newInfo'];
	$orderFavourable = $orderInfo['orderFavourable'];
	$surplus = $row['surplus'];
	$integral_money = $row['integral_money'];
	$shipping_fee = $row['shipping_fee'];
	$use_bonus = 0;
	$discount = !empty($row['discount']) ? $row['discount'] : 0;
	$commonuse_discount = get_single_order_fav($discount, $orderFavourable, 1);
	$discount_child = 0;
	$residue_integral = 0;
	$bonus_id = $row['bonus_id'];
	$bonus = $row['bonus'];
	$coupons = $row['coupons'];
	$usebonus_type = get_bonus_all_goods($bonus_id);
	$shipping_id = $row['shipping_id'];
	$shipping_name = empty($row['shipping_name']) ? '' : $row['shipping_name'];
	$shipping_code = empty($row['shipping_code']) ? '' : $row['shipping_code'];
	$shipping_type = $row['shipping_type'];
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$arr = array();
	$sms_send = array();
	$i = 0;

	foreach ($newInfo as $key => $info) {
		$i += 1;
		$arr[$key] = $info;
		$shipping = get_seller_shipping_order($key, $shipping_id, $shipping_name, $shipping_code, $shipping_type);
		$row['shipping_id'] = $shipping['shipping_id'];
		$shipping_name = empty($row['shipping_name']) ? '' : $row['shipping_name'];
		$shipping_code = empty($row['shipping_code']) ? '' : $row['shipping_code'];
		$row['shipping_type'] = $shipping['shipping_type'];
		$error_no = 0;

		do {
			$row['order_sn'] = get_order_child_sn($order_id, $key);
			$_SESSION['order_done_sn'] = $row['order_sn'];
			$row['main_order_id'] = $order_id;
			$row['goods_amount'] = $newOrder[$key]['goods_amount'];

			if ($commonuse_discount['has_terrace'] == 1) {
				if ($key == 0) {
					$row['discount'] = $commonuse_discount['discount'];
				}
				else {
					$row['discount'] = $orderFavourable[$key]['compute_discount']['discount'];
				}
			}
			else {
				$row['discount'] = $orderFavourable[$key]['compute_discount']['discount'] + $commonuse_discount['discount'];
				$commonuse_discount['discount'] = 0;
			}

			$row['discount'] = !empty($row['discount']) ? $row['discount'] : 0;
			$cou_type = 0;
			$order_coupons = get_user_order_coupons($order_id, $key, 1);

			if ($order_coupons) {
				$cou_type = 1;
				$row['coupons'] = $coupons;
				$coupons = 0;
			}
			else {
				$row['coupons'] = 0;
			}

			if ($GLOBALS['_CFG']['freight_model'] == 1) {
				$row['shipping_fee'] = $newOrder[$key]['shopping_fee'];
				$row['order_amount'] = $newOrder[$key]['goods_amount'] + $row['shipping_fee'];
			}
			else {
				$row['shipping_fee'] = 0;
				$sellerOrderInfo = array();
				$sellerOrderInfo['ru_id'] = $key;
				$sellerOrderInfo['weight'] = 0;
				$sellerOrderInfo['goods_price'] = 0;
				$sellerOrderInfo['number'] = 0;
				$sellerOrderInfo['region'] = array($row['country'], $row['province'], $row['city'], $row['district'], $row['street']);
				$sellerOrderInfo['shipping_id'] = $row['shipping_id'];

				if (!empty($newOrder[$key]['ru_list'])) {
					foreach ($newOrder[$key]['ru_list'] as $k => $v) {
						if (isset($v['order_id'])) {
							$sellerOrderInfo['weight'] += floatval($v['weight']);
							$sellerOrderInfo['goods_price'] += floatval($v['goods_price']);
							$sellerOrderInfo['number'] += intval($v['number']);
						}
					}

					$row['shipping_fee'] = getSellerShippingFee($sellerOrderInfo, $arr[$key]);
				}
			}

			$couponsInfo = array();
			if (isset($row['uc_id']) && !empty($row['uc_id'])) {
				$couponsInfo = get_coupons($row['uc_id'], array('c.cou_id', 'c.cou_man', 'c.cou_type', 'c.ru_id', 'c.cou_money', 'cu.uc_id'), $row['user_id'], $key);
			}

			if (!empty($couponsInfo) && $key == $couponsInfo['ru_id']) {
				if ($couponsInfo['cou_type'] == 5) {
					if ($couponsInfo['cou_man'] <= $newOrder[$key]['goods_amount'] || $couponsInfo['cou_man'] == 0) {
						$cou_region = get_coupons_region($couponsInfo['cou_id']);
						$cou_region = !empty($cou_region) ? explode(',', $cou_region) : array();

						if ($cou_region) {
							if (!in_array($row['province'], $cou_region)) {
								$row['shipping_fee'] = 0;
							}
						}
						else {
							$row['shipping_fee'] = 0;
						}
					}
				}
			}

			$row['order_amount'] = $newOrder[$key]['goods_amount'] + $row['shipping_fee'];
			$row['tax'] = get_order_invoice_total($row['goods_amount'], $row['inv_content']);
			$row['order_amount'] = $row['order_amount'] + $row['tax'];

			if (0 < $row['coupons']) {
				if ($row['coupons'] <= $row['order_amount']) {
					$row['order_amount'] -= $row['coupons'];
				}
				else {
					$row['coupons'] = $row['order_amount'];
					$row['order_amount'] = 0;
				}
			}

			if ($commonuse_discount['has_terrace'] == 0) {
				if (0 < $discount_child) {
					$row['discount'] += $discount_child;
				}

				if (0 < $row['discount']) {
					if ($row['discount'] < $row['order_amount']) {
						$row['order_amount'] -= $row['discount'];
					}
					else {
						$discount_child = $row['discount'] - $row['order_amount'];
						$row['discount'] = $row['order_amount'];
						$row['order_amount'] = 0;
					}
				}
			}
			else {
				$row['order_amount'] -= $row['discount'];
			}

			if ($usebonus_type == 1) {
				if (0 < $bonus) {
					if ($bonus <= $row['order_amount']) {
						$row['order_amount'] = $row['order_amount'] - $bonus;
						$row['bonus'] = $bonus;
						$bonus = 0;
					}
					else {
						$bonus = $bonus - $row['order_amount'];
						$row['bonus'] = $row['order_amount'];
						$row['order_amount'] = 0;
					}

					$row['bonus_id'] = $bonus_id;
				}
				else {
					$row['bonus'] = 0;
					$row['bonus_id'] = 0;
				}
			}
			else if (isset($orderBonus[$key]['bonus']['type_money'])) {
				$use_bonus = min($orderBonus[$key]['bonus']['type_money'], $row['order_amount']);
				$row['order_amount'] -= $use_bonus;
				$row['bonus'] = $orderBonus[$key]['bonus']['type_money'];
				$row['bonus_id'] = $row['bonus_id'];
			}
			else {
				$row['bonus'] = 0;
				$row['bonus_id'] = 0;
			}

			if (0 < $surplus) {
				if ($row['order_amount'] <= $surplus) {
					$surplus = $surplus - $row['order_amount'];
					$row['surplus'] = $row['order_amount'];
					$row['order_amount'] = 0;
				}
				else {
					$row['order_amount'] = $row['order_amount'] - $surplus;
					$row['surplus'] = $surplus;
					$surplus = 0;
				}
			}
			else {
				$row['surplus'] = 0;
			}

			if (0 < $integral_money) {
				if ($i < $ru_number) {
					$integral_ratio = get_integral_ratio($order_id, $info);
					$row['integral_money'] = round($integral_money * $integral_ratio, 2);
					$row['integral'] = $integral_money * $integral_ratio;
					$row['order_amount'] = $row['order_amount'] - round($integral_money * $integral_ratio, 2);
					$residue_integral += $integral_money * $integral_ratio;
				}
				else {
					$row['integral'] = $integral_money - $residue_integral;
					$row['integral_money'] = round($row['integral'], 2);
					$row['order_amount'] = $row['order_amount'] - round($row['integral'], 2);
				}
			}
			else {
				$row['integral_money'] = 0;
				$row['integral'] = 0;
			}

			$row['integral'] = intval(integral_of_value($row['integral']));
			$row['order_amount'] = number_format($row['order_amount'], 2, '.', '');

			if ($row['order_amount'] <= 0) {
				$row['order_status'] = OS_CONFIRMED;
				$row['confirm_time'] = gmtime();
				$row['pay_status'] = PS_PAYED;
				$row['pay_time'] = gmtime();
			}
			else {
				$row['order_status'] = 0;
				$row['confirm_time'] = 0;
				$row['pay_status'] = 0;
				$row['pay_time'] = 0;
			}

			unset($row['order_id']);

			if ($row['shipping_code'] != 'cac') {
				$row['point_id'] = 0;
				$row['shipping_dateStr'] = '';
			}

			$new_row = $GLOBALS['db']->filter_field('order_info', $row);
			$new_orderId = $GLOBALS['db']->table('order_info')->data($new_row)->add();
			$error_no = $GLOBALS['db']->errno();
			if (0 < $error_no && $error_no != 1062) {
				exit($error_no);
			}

			$sql = 'SELECT seller_email FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' WHERE ru_id = \'' . $key . '\'');
			$seller_email = $GLOBALS['db']->getOne($sql);
			if ($GLOBALS['_CFG']['send_service_email'] && $seller_email != '' && $GLOBALS['_CFG']['seller_email'] == 1) {
				$cart_goods = $arr[$key];
				$order['order_sn'] = $row['order_sn'];
				$order['order_amount'] = $row['order_amount'];
				$order['consignee'] = $row['consignee'];
				$order['address'] = $row['address'];
				$order['tel'] = $row['tel'];
				$order['mobile'] = $row['mobile'];
				$order['shipping_name'] = $row['shipping_name'];
				$order['shipping_fee'] = $row['shipping_fee'];
				$order['pay_id'] = $row['pay_id'];
				$order['pay_name'] = $row['pay_name'];
				$order['pay_fee'] = $row['pay_fee'];
				$order['surplus'] = $row['surplus'];
				$order['integral_money'] = $row['integral_money'];
				$order['bonus'] = $row['bonus'];
				$tpl = get_mail_template('remind_of_new_order');
				$GLOBALS['smarty']->assign('order', $order);
				$GLOBALS['smarty']->assign('goods_list', $cart_goods);
				$GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
				$GLOBALS['smarty']->assign('send_date', date($GLOBALS['_CFG']['time_format']));
				$content = $GLOBALS['smarty']->fetch('', $tpl['template_content']);
				send_mail($GLOBALS['_CFG']['shop_name'], $seller_email, $tpl['template_subject'], $content, $tpl['is_html']);
			}

			if ($key == 0) {
				$sms_shop_mobile = $GLOBALS['_CFG']['sms_shop_mobile'];
			}
			else {
				$sql = 'SELECT mobile FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' WHERE ru_id = \'' . $key . '\'');
				$sms_shop_mobile = $GLOBALS['db']->getOne($sql);
			}

			if ($GLOBALS['_CFG']['sms_order_placed'] == '1' && $sms_shop_mobile != '') {
				$msg = array('consignee' => $order['consignee'], 'order_mobile' => $order['mobile'], 'ordermobile' => $order['mobile']);
				send_sms($sms_shop_mobile, 'sms_order_placed', $msg);
			}
		} while ($error_no == 1062);

		$arr[$key] = array_values($arr[$key]);

		for ($j = 0; $j < count($arr[$key]); $j++) {
			$arr[$key][$j]['order_id'] = $new_orderId;
			unset($arr[$key][$j]['rec_id']);
			$arr[$key][$j]['goods_name'] = addslashes($arr[$key][$j]['goods_name']);
			$arr[$key][$j]['goods_attr'] = addslashes($arr[$key][$j]['goods_attr']);
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_goods'), $arr[$key][$j], 'INSERT');
			$virtual_goods = get_virtual_goods($arr[$key][$j]['order_id']);
			$order_sn = $GLOBALS['db']->getOne(' SELECT order_sn FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $arr[$key][$j]['order_id'] . '\' ');
			$msg = '';
			if ($virtual_goods && $flow_type != CART_GROUP_BUY_GOODS) {
				if (virtual_goods_ship($virtual_goods, $msg, $order_sn, true)) {
					$sql = 'SELECT COUNT(*)' . ' FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE order_id = \'' . $arr[$key][$j]['order_id'] . '\' ' . ' AND is_real = 1';

					if ($GLOBALS['db']->getOne($sql) <= 0) {
						update_order($arr[$key][$j]['order_id'], array('shipping_status' => SS_SHIPPED, 'shipping_time' => gmtime()));
					}
				}
			}
		}

		$row['log_id'] = insert_pay_log($new_orderId, $row['order_amount'], PAY_ORDER);
	}
}

function get_integral_ratio($order_id = 0, $info = array())
{
	$count_goods_integral = get_integral($order_id);
	$goods_id = array();

	if (!empty($info)) {
		foreach ($info as $v) {
			$goods_id[] = $v['goods_id'];
		}
	}

	$chlid_goods_integral = get_integral($order_id, $goods_id);
	$integral_ratio = $chlid_goods_integral / $count_goods_integral;
	return $integral_ratio;
}

function get_integral($order_id = 0, $goods_id = array())
{
	$where = '';

	if (!empty($goods_id)) {
		$where = 'AND og.goods_id ' . db_create_in($goods_id);
	}

	$sql = 'SELECT g.integral*og.goods_number as integral FROM' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . (' AS og ON g.goods_id = og.goods_id WHERE og.order_id=\'' . $order_id . '\'') . $where;
	$rel = $GLOBALS['db']->getAll($sql);
	$count = 0;

	foreach ($rel as $v) {
		$count += $v['integral'];
	}

	return $count;
}

function get_seller_shipping_order($ru_id = array(), $shipping_id = array(), $shipping_name = array(), $shipping_code = array(), $shipping_type = array())
{
	$shipping_id = explode(',', $shipping_id);
	$shipping_name = explode(',', $shipping_name);
	$shipping_code = explode(',', $shipping_code);
	$shipping_type = explode(',', $shipping_type);
	$shippingId = '';
	$shippingName = '';
	$shippingCode = '';
	$shippingType = '';

	foreach ($shipping_id as $key => $row) {
		$row = explode('|', $row);

		if ($row[0] == $ru_id) {
			$shippingId = $row[1];
		}
	}

	foreach ($shipping_name as $key => $row) {
		$row = explode('|', $row);

		if ($row[0] == $ru_id) {
			$shippingName = $row[1];
		}
	}

	if ($shipping_code) {
		foreach ($shipping_code as $key => $row) {
			$row = explode('|', $row);

			if ($row[0] == $ru_id) {
				$shippingCode = $row[1];
			}
		}
	}

	if ($shipping_type) {
		foreach ($shipping_type as $key => $row) {
			$row = explode('|', $row);

			if ($row[0] == $ru_id) {
				$shippingType = $row[1];
			}
		}
	}

	$shipping = array('shipping_id' => $shippingId, 'shipping_name' => $shippingName, 'shipping_code' => $shippingCode, 'shipping_type' => $shippingType);
	return $shipping;
}

function get_seller_cac_order($ru_id, $point_id, $shipping_dateStr)
{
	$cac = array('point_id' => '', 'shipping_dateStr' => '');

	if ($point_id) {
		$point_id = array_filter(explode(',', $point_id));
		$shipping_dateStr = array_filter(explode(',', $shipping_dateStr));
		$pointId = '';

		foreach ($point_id as $key => $row) {
			$row = explode('|', $row);

			if ($row[0] == $ru_id) {
				$pointId = $row[1];
			}
		}

		foreach ($shipping_dateStr as $key => $row) {
			$row = explode('|', $row);

			if ($row[0] == $ru_id) {
				$dateStr = $row[1];
			}
		}

		$cac = array('point_id' => $pointId, 'shipping_dateStr' => $dateStr);
	}

	return $cac;
}

function get_bonus_merchants($bonus_id = 0, $user_id = 0)
{
	$sql = 'select bt.user_id, bt.type_money from ' . $GLOBALS['ecs']->table('user_bonus') . ' as ub' . ' left join ' . $GLOBALS['ecs']->table('bonus_type') . ' as bt on ub.bonus_type_id = bt.type_id' . (' where ub.bonus_id = \'' . $bonus_id . '\' and bt.user_id = \'' . $user_id . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function get_order_goods_toInfo($order_id = 0)
{
	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, og.goods_number, og.goods_price, og.goods_price, og.extension_code, og.goods_name AS extension_name, oi.order_sn, ga.act_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ON og.order_id = oi.order_id ' . 'left join ' . $GLOBALS['ecs']->table('goods') . ' as g on og.goods_id = g.goods_id ' . 'left join ' . $GLOBALS['ecs']->table('goods_activity') . ' as ga on og.goods_id = ga.act_id AND ga.review_status = 3 ' . ('WHERE og.order_id = \'' . $order_id . '\' group by g.goods_id order by g.goods_id');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['goods_id'] = $row['goods_id'];
		$arr[$key]['goods_name'] = $row['goods_name'];
		$arr[$key]['goods_number'] = $row['goods_number'];
		$arr[$key]['extension_code'] = $row['extension_code'];
		$arr[$key]['goods_price'] = price_format($row['goods_price'], false);
		$arr[$key]['goods_thumb'] = get_image_path($row['goods_thumb']);
		$arr[$key]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);

		if ($row['extension_code'] == 'package_buy') {
			$arr[$key]['goods_name'] = $row['extension_name'];
			$activity = get_goods_activity_info($row['act_id'], array('act_id', 'activity_thumb'));
			$arr[$key]['goods_thumb'] = $activity['goods_thumb'];
		}
	}

	return $arr;
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
		$activity['goods_thumb'] = get_image_path($activity['activity_thumb']);
	}

	return $activity;
}

function get_child_order_info($order_id)
{
	$sql = 'select order_sn, order_amount, shipping_fee, order_id, shipping_name from ' . $GLOBALS['ecs']->table('order_info') . (' where main_order_id = \'' . $order_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['order_sn'] = $row['order_sn'];
		$arr[$key]['order_id'] = $row['order_id'];
		$arr[$key]['shipping_name'] = $row['shipping_name'];
		$arr[$key]['amount_formated'] = price_format($row['order_amount'], false);
		$arr[$key]['shipping_fee_formated'] = price_format($row['shipping_fee'], false);
	}

	return $arr;
}

function get_merchants_user_list()
{
	$sql = 'select msi.* from ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi' . ' where 1';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key] = $row;
		$date = array('user_name');
		$user_name = get_table_date('users', 'user_id = \'' . $row['user_id'] . '\'', $date, 2);
		$arr[$key]['user_name'] = $user_name;
	}

	return $arr;
}

function get_region_area_divide()
{
	$sql = 'select ra_id, ra_name from ' . $GLOBALS['ecs']->table('merchants_region_area') . ' where 1 order by ra_sort asc';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key] = $row;
		$arr[$key]['area_list'] = get_to_area_list($row['ra_id']);
	}

	return $arr;
}

function get_to_area_list($ra_id = 0)
{
	$sql = 'select ra_id, region_id from ' . $GLOBALS['ecs']->table('merchants_region_info') . (' where ra_id = \'' . $ra_id . '\' order by region_id asc');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key] = $row;
		$date = array('region_name');
		$arr[$key]['region_name'] = get_table_date('region', 'region_id = \'' . $row['region_id'] . '\'', $date, 2);
	}

	return $arr;
}

function get_user_store_category($ru_id)
{
	$sql = 'select cat_id, cat_name from ' . $GLOBALS['ecs']->table('merchants_category') . ('  where user_id = \'' . $ru_id . '\' and is_show = 1 and parent_id=0');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key] = $row;
		$arr[$key]['url'] = build_uri('merchants_store', array('cid' => $row['cat_id'], 'urid' => $ru_id), $row['cat_name']);
		$arr[$key]['opennew'] = 0;
		$arr[$key]['child'] = get_store_category_child($row['cat_id'], $ru_id);
	}

	$arr = array_merge($arr);
	return $arr;
}

function get_store_category_child($parent_id, $ru_id)
{
	$sql = 'select cat_id, cat_name from ' . $GLOBALS['ecs']->table('merchants_category') . ('  where parent_id = \'' . $parent_id . '\' and user_id = \'' . $ru_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['cat_id'] = $row['cat_id'];
		$arr[$key]['cat_name'] = $row['cat_name'];
		$arr[$key]['url'] = build_uri('merchants_store', array('cid' => $row['cat_id'], 'urid' => $ru_id), $row['cat_name']);
		$arr[$key]['child'] = get_store_category_child($row['cat_id'], $row['cat_id']);
	}

	return $arr;
}

function selled_count($goods_id, $type = '')
{
	if (!empty($type)) {
		$where = ' AND og.order_id = oi.order_id and oi.extension_code = \'' . $type . '\'';
	}
	else {
		$where = ' AND og.order_id = oi.order_id ';
	}

	$where .= 'AND (oi.order_status = \'' . OS_CONFIRMED . '\' OR oi.order_status = \'' . OS_SPLITED . '\') ' . 'AND (oi.pay_status = \'' . PS_PAYED . '\' OR oi.pay_status = \'' . PS_PAYING . '\') ' . 'AND (oi.shipping_status = \'' . SS_SHIPPED . '\' OR oi.shipping_status = \'' . SS_RECEIVED . '\')';
	$where .= ' group by g.goods_id';
	$sql = 'select count(og.goods_number) as count from ' . $GLOBALS['ecs']->table('order_goods') . ' as og , ' . $GLOBALS['ecs']->table('goods') . ' as g , ' . $GLOBALS['ecs']->table('order_info') . ' as oi ' . ' where og.goods_id = g.goods_id and og.goods_id =\'' . $goods_id . '\'' . $where;
	$res = $GLOBALS['db']->getOne($sql);

	if (0 < $res) {
		return $res;
	}
	else {
		return 0;
	}
}

function get_oneTwo_category($parent_id = 0)
{
	$sql = 'select cat_id, cat_name from ' . $GLOBALS['ecs']->table('category') . (' where parent_id = \'' . $parent_id . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key] = $row;
		$arr[$key]['child'] = get_oneTwo_category($row['cat_id']);

		if (empty($arr[$key]['child'])) {
			unset($arr[$key]['child']);
		}
	}

	return $arr;
}

function get_order_region_name($region_id = 0)
{
	$where = 'region_id = \'' . $region_id . '\'';
	$date = array('region_name');
	$region = get_table_date('region', $where, $date);
	return $region;
}

function get_cart_check_goods($cart_goods, $rec_id = '')
{
	$arr['subtotal_amount'] = 0;
	$arr['subtotal_number'] = 0;

	if (!empty($rec_id)) {
		if ($cart_goods) {
			foreach ($cart_goods as $row) {
				$arr['subtotal_amount'] += $row['subtotal'];
				$arr['subtotal_number'] += $row['goods_number'];
			}
		}
	}

	$arr['subtotal_amount'] = price_format($arr['subtotal_amount'], false);
	return $arr;
}

function get_goods_minMax_price($goods_id = 0, $warehouse_id = 0, $area_id = 0, $goods_price, $market_price, $type = 1)
{
	$model_attr = get_table_date('goods', 'goods_id = \'' . $goods_id . '\'', array('model_attr'), 2);

	if ($model_attr == 1) {
		$where .= ' AND wa.warehouse_id = \'' . $warehouse_id . '\'';
		$slelect = ', wa.attr_price as attr_price';
		$leftJoin = ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_attr') . ' AS wa on ga.goods_attr_id = wa.goods_attr_id ';
	}
	else if ($model_attr == 2) {
		$where .= ' AND waa.area_id = \'' . $area_id . '\'';
		$slelect = ', waa.attr_price as attr_price';
		$leftJoin = ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_attr') . ' AS waa on ga.goods_attr_id = waa.goods_attr_id ';
	}
	else if ($model_attr == 0) {
		$slelect = ', ga.attr_price as attr_price';
		$where = '';
		$leftJoin = '';
	}

	$sql = 'SELECT ga.attr_id ' . $slelect . ' FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' as ga ' . $leftJoin . (' WHERE ga.goods_id = \'' . $goods_id . '\' ') . $where;
	$arr_res = $GLOBALS['db']->getAll($sql);
	$arr_k = array();

	if ($arr_res) {
		foreach ($arr_res as $val) {
			$arr_k .= $val['attr_id'] . '@';
		}

		$arr_k = rtrim($arr_k, '@');
		$k_res = explode('@', $arr_k);
		$k_res = array_flip(array_flip($k_res));
	}

	$new_arr = array();

	if ($k_res) {
		foreach ($k_res as $val) {
			foreach ($arr_res as $v) {
				if ($v['attr_id'] == $val) {
					$new_arr[$val][] = $v['attr_price'];
				}
			}
		}
	}

	if ($type == 1) {
		$new_arr = get_unset_null_array($new_arr, 2);
	}

	$new_arr_res = array();

	if ($new_arr) {
		foreach ($new_arr as $k => $val) {
			$new_arr_res[$k]['max'] = $val[array_search(max($val), $val)];
			$new_arr_res[$k]['min'] = $val[array_search(min($val), $val)];
		}

		$num_res_max = 0;
		$num_res_min = 0;

		foreach ($new_arr_res as $val) {
			$num_res_max += $val['max'];
			$num_res_min += $val['min'];
		}
	}

	if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
		$num_res_min = 0;
	}
	else {
		$num_res_max = 0;
	}

	if ($type == 1) {
		$arr['goods_min'] = $goods_price + $num_res_min;
		$arr['goods_max'] = $goods_price + $num_res_max;
		$arr['market_min'] = $market_price + $num_res_min;
		$arr['market_max'] = $market_price + $num_res_max;
	}
	else if ($type == 2) {
		$goodsLeftJoin = '';
		$goodsLeftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $warehouse_id . '\' ');
		$goodsLeftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
		$sql = 'SELECT ' . ('IFNULL(IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\'), g.shop_price * \'' . $_SESSION['discount'] . '\')  AS shop_price, ') . 'IFNULL(IF(g.model_price < 1, g.promote_price, IF(g.model_price < 2, wg.warehouse_promote_price, wag.region_promote_price)), g.promote_price) AS promote_price, ' . 'g.promote_start_date, g.promote_end_date, g.is_promote ' . $goodsLeftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' as g ' . ('WHERE goods_id = \'' . $goods_id . '\'');
		$goods = $GLOBALS['db']->getRow($sql);

		if (0 < $goods['promote_price']) {
			$promote_price = bargain_price($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
		}
		else {
			$promote_price = 0;
		}

		$promote_price = 0 < $promote_price ? $promote_price : '';

		if (!empty($promote_price)) {
			$arr['promote_minPrice'] = price_format($promote_price + $num_res_min);
			$arr['promote_maxPrice'] = price_format($promote_price + $num_res_max);
		}
		else {
			$arr['promote_minPrice'] = $promote_price;
			$arr['promote_maxPrice'] = $promote_price;
		}

		$arr['shop_minPrice'] = price_format($goods['shop_price'] + $num_res_min);
		$arr['shop_maxPrice'] = price_format($goods['shop_price'] + $num_res_max);
	}

	return $arr;
}

function get_unset_null_array($arr = array(), $type = 0)
{
	$arr = array_values($arr);
	$new_arr = array();
	if ($arr && $type == 2) {
		for ($i = 0; $i < count($arr); $i++) {
			for ($j = 0; $j < count($arr[$i]); $j++) {
				if (0 < $arr[$i][$j]) {
					$new_arr[$i][$j] = $arr[$i][$j];
				}
			}
		}
	}
	else {
		if ($arr && $type == 1) {
			for ($i = 0; $i < count($arr); $i++) {
				if (0 < $arr[$i]) {
					$new_arr[$i] = $arr[$i];
				}
			}
		}
	}

	return $new_arr;
}

function get_choose_goods_combo_cart($fittings, $number = 1)
{
	$arr = array();
	$arr['fittings_min'] = 0;
	$arr['fittings_max'] = 0;
	$arr['market_min'] = 0;
	$arr['market_max'] = 0;
	$arr['save_price'] = '';
	$arr['collocation_number'] = 0;
	$arr['save_minPrice'] = 0;
	$arr['save_maxPrice'] = 0;
	$arr['fittings_price'] = 0;
	$arr['fittings_market_price'] = 0;
	$arr['save_price_amount'] = 0;
	$arr['groupId'] = 0;
	$arr['all_price_ori'] = 0;
	$arr['return_attr'] = 0;

	if ($fittings) {
		foreach ($fittings as $key => $row) {
			$arr[$key]['goods_id'] = $row['goods_id'];
			$arr[$key]['market_price'] = $row['market_price'] + $row['attr_price'];
			$arr[$key]['fittings_minPrice'] = $row['fittings_minPrice'];
			$arr[$key]['fittings_maxPrice'] = $row['fittings_maxPrice'];
			$arr[$key]['market_minPrice'] = $row['market_minPrice'];
			$arr[$key]['market_maxPrice'] = $row['market_maxPrice'];
			$arr[$key]['shop_price_ori'] = $row['shop_price_ori'];
			$arr[$key]['fittings_price_ori'] = $row['fittings_price_ori'];
			$arr[$key]['attr_price'] = $row['attr_price'];
			$arr[$key]['spare_price_ori'] = $row['spare_price_ori'];
			$arr[$key]['group_id'] = !empty($row['group_id']) ? $row['group_id'] : 0;
			$arr[$key]['is_attr'] = get_cart_combo_goods_product_list($row['goods_id']);

			if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
				$row['attr_price'] = 0;
			}

			if ($arr[$key]['group_id'] == 0) {
				$arr[$key]['price_ori'] = $row['shop_price_ori'] + $row['attr_price'];
			}
			else {
				$arr[$key]['price_ori'] = $row['fittings_price_ori'] + $row['attr_price'];
			}

			$arr['save_price_amount'] += $row['spare_price_ori'];
			$arr['fittings_price'] += $arr[$key]['price_ori'];
			$arr['fittings_market_price'] += $row['market_price'];
			$arr['save_price'] .= $row['spare_price_ori'] . ',';

			if (!empty($row['group_id'])) {
				$arr['groupId'] .= $row['group_id'] . ',';
			}
		}

		$arr['collocation_number'] = count($fittings) - 1;
		$arr['save_price'] = substr($arr['save_price'], 0, -1);
		$arr['save_price'] = explode(',', $arr['save_price']);
		$arr['save_price'] = get_unset_null_array($arr['save_price'], 1);
		$arr['save_minPrice'] = min($arr['save_price']);
		$arr['save_maxPrice'] = get_save_maxPrice($arr['save_price']);
		$arr['groupId'] = substr($arr['groupId'], 1, -1);
		$arr['groupId'] = explode(',', $arr['groupId']);
		$arr['groupId'] = array_unique($arr['groupId']);
		$arr['groupId'] = implode(',', $arr['groupId']);
		$minmax_values = get_min_or_max_values($arr);
		$arr['fittings_min'] = $minmax_values['fittings_minPrice'];
		$arr['fittings_max'] = $minmax_values['fittings_maxPrice'];
		$arr['market_min'] = $minmax_values['market_minPrice'];
		$arr['market_max'] = $minmax_values['market_maxPrice'];
		$arr['return_attr'] = $minmax_values['return_attr'];
		$arr['all_price_ori'] = $minmax_values['all_price_ori'];
		$arr['all_market_price'] = $minmax_values['all_market_price'];
	}

	return $arr;
}

function get_min_or_max_values($arr)
{
	$unsetStr = 'fittings_min,fittings_max,market_min,market_max,save_price,collocation_number,save_minPrice,save_maxPrice,fittings_price,save_price_amount,groupId,all_price_ori,return_attr,fittings_market_price';
	$unsetStr = explode(',', $unsetStr);

	foreach ($unsetStr as $str) {
		unset($arr[$str]);
	}

	$newArr = array();
	$newArr['fittings_minPrice'] = '';
	$newArr['fittings_maxPrice'] = '';
	$newArr['market_minPrice'] = '';
	$newArr['market_maxPrice'] = '';
	$newArr['is_attr'] = '';
	$shop_price = 0;
	$market_price = 0;
	$newArr['all_price_ori'] = 0;
	$newArr['return_attr'] = 0;
	$newArr['all_market_price'] = 0;

	foreach ($arr as $key => $row) {
		if (0 < $key) {
			$newArr['all_price_ori'] += $row['price_ori'] . ',';
			$newArr['all_market_price'] += $row['market_minPrice'] . ',';
			$newArr['fittings_minPrice'] .= $row['fittings_minPrice'] . ',';
			$newArr['fittings_maxPrice'] .= $row['fittings_maxPrice'] . ',';
			$newArr['market_minPrice'] .= $row['market_minPrice'] . ',';
			$newArr['market_maxPrice'] .= $row['market_maxPrice'] . ',';
			$newArr['is_attr'] .= $row['is_attr'] . ',';
		}
	}

	$is_attr = explode(',', substr($newArr['is_attr'], 0, -1));

	foreach ($is_attr as $key => $row) {
		$newArr['return_attr'] += $row;
	}

	$fittings_maxPrice = explode(',', substr($newArr['fittings_maxPrice'], 0, -1));
	$market_maxPrice = explode(',', substr($newArr['market_maxPrice'], 0, -1));

	foreach ($fittings_maxPrice as $key => $shop) {
		$shop_price += $shop;
	}

	$newArr['fittings_maxPrice'] = $shop_price;

	foreach ($market_maxPrice as $key => $market) {
		$market_price += $market;
	}

	$newArr['market_maxPrice'] = $market_price;
	$newArr['fittings_minPrice'] = $arr[0]['fittings_minPrice'] + min(explode(',', substr($newArr['fittings_minPrice'], 0, -1)));
	$newArr['fittings_maxPrice'] = $arr[0]['fittings_maxPrice'] + $newArr['fittings_maxPrice'];
	$newArr['market_minPrice'] = $arr[0]['market_minPrice'] + min(explode(',', substr($newArr['market_minPrice'], 0, -1)));
	$newArr['market_maxPrice'] = $arr[0]['market_maxPrice'] + $newArr['market_maxPrice'];
	$newArr['all_price_ori'] = $arr[0]['price_ori'] + $newArr['all_price_ori'];
	$newArr['all_market_price'] = $arr[0]['market_price'] + $arr[0]['attr_price'] + $newArr['all_market_price'];
	return $newArr;
}

function get_cart_combo_goods_product_list($goods_id)
{
	$sql = 'SELECT goods_attr_id, goods_id, attr_id FROM ' . $GLOBALS['ecs']->table('goods_attr') . (' WHERE goods_id = \'' . $goods_id . '\'');
	$attr_list = $GLOBALS['db']->getAll($sql);

	if ($attr_list) {
		return 1;
	}
	else {
		return 0;
	}
}

function get_save_maxPrice($save_price)
{
	$save_maxPrice = 0;

	if ($save_price) {
		foreach ($save_price as $key => $row) {
			$save_maxPrice += $row;
		}
	}

	return $save_maxPrice;
}

function get_goods_attr_type_list($goods_id = 0, $type = 0)
{
	$sql = 'select a.attr_id, a.attr_name from ' . $GLOBALS['ecs']->table('goods_attr') . ' as ga ' . ' left join ' . $GLOBALS['ecs']->table('attribute') . ' as a on ga.attr_id = a.attr_id ' . (' where goods_id = \'' . $goods_id . '\' group by a.attr_id ORDER BY a.sort_order, a.attr_id, ga.goods_attr_id');
	$attr_list = $GLOBALS['db']->getAll($sql);

	if ($type == 1) {
		$attr_list = count($attr_list);
	}

	return $attr_list;
}

function get_bonus_all_goods($bonus_id)
{
	$sql = 'SELECT t.usebonus_type FROM ' . $GLOBALS['ecs']->table('bonus_type') . ' as t, ' . $GLOBALS['ecs']->table('user_bonus') . ' as ub' . (' WHERE t.type_id = ub.bonus_type_id AND ub.bonus_id = \'' . $bonus_id . '\'');
	return $GLOBALS['db']->getOne($sql);
}

function get_arr_str_key($arr = array())
{
	$str = '';

	if ($arr) {
		$new_arr = array();

		foreach ($arr as $key => $row) {
			$strlen = mb_strlen($row, 'utf8');
			$new_arr[$key]['len'] = $strlen;
			$new_arr[$key]['val'] = $row;
		}

		$new_arr = get_array_sort($new_arr, 'len', 'desc');
	}

	return $str;
}

function get_seller_shipping_type($ru_id)
{
	$sql = 'SELECT s.shipping_id, s.shipping_name, s.shipping_code FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS ss, ' . $GLOBALS['ecs']->table('shipping') . ' AS s' . (' WHERE ss.shipping_id = s.shipping_id AND ru_id = \'' . $ru_id . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function get_city_region()
{
	$sql = 'select `region_id`, `parent_id`, `region_name` from ' . $GLOBALS['ecs']->table('region') . ' where region_type = 2 and parent_id > 0';
	return $GLOBALS['db']->getAll($sql);
}

function get_recommend_brands($num = 0)
{
	$where = ' where be.is_recommend=1 order by b.sort_order asc ';

	if (0 < $num) {
		$where .= ' limit 0,' . $num;
	}

	$sql = 'select b.* from ' . $GLOBALS['ecs']->table('brand') . ' as b left join ' . $GLOBALS['ecs']->table('brand_extend') . ' as be on b.brand_id=be.brand_id ' . $where;
	return $GLOBALS['db']->getAll($sql);
}

function get_freight_batch_remove($id)
{
	for ($i = 0; $i < count($id); $i++) {
		$sql = 'delete from ' . $GLOBALS['ecs']->table('warehouse_freight') . ' where id = ' . $id[$i];
		$GLOBALS['db']->query($sql);
	}
}

function goodsShippingFee($goods_id = 0, $warehouse_id = 0, $area_id = 0, $region = array(), $seckill_price = '')
{
	$transport_info = array();
	$shippingInfo = array('shipping_id' => 0, 'shipping_code' => '', 'shipping_name' => '', 'shipping_type' => $GLOBALS['_CFG']['freight_model'], 'shipping_fee' => '', 'shipping_fee_formated' => '', 'free_money' => '', 'is_shipping' => 0);
	$shippingFee = 0;
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_goods') . (' AS wg ON g.goods_id = wg.goods_id AND wg.region_id = \'' . $warehouse_id . '\' ');
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' AS wag ON g.goods_id = wag.goods_id AND wag.region_id = \'' . $area_id . '\' ');
	$sql = ' SELECT g.goods_id, g.freight, g.user_id AS ru_id, g.user_id, g.tid, g.is_shipping, g.shipping_fee, g.goods_weight, g.shop_price,  ' . ('IFNULL(mp.user_price, IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) * \'' . $_SESSION['discount'] . '\') AS goods_price ') . ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . (' WHERE g.goods_id = \'' . $goods_id . '\' LIMIT 1');
	$goodsInfo = $GLOBALS['db']->getRow($sql);

	if ($goodsInfo['freight'] == 2) {
		if (is_numeric($seckill_price)) {
			$goodsInfo['shop_price'] = $seckill_price;
		}

		$sellerShippingInfo = get_seller_shipping_type($goodsInfo['user_id']);
		$where = 1;

		if ($sellerShippingInfo) {
			$shippingInfo['shipping_id'] = $sellerShippingInfo['shipping_id'];
			$shippingInfo['shipping_code'] = $sellerShippingInfo['shipping_code'];
			$shippingInfo['shipping_name'] = $sellerShippingInfo['shipping_name'];
			$where .= ' AND s.shipping_id = \'' . $sellerShippingInfo['shipping_id'] . '\'';
		}

		$transport_info = get_goods_transport($goodsInfo['tid']);
		$val = array();

		if ($transport_info) {
			if ($transport_info['freight_type'] == 1) {
				$sql = 'SELECT gtt.*, s.shipping_id, s.shipping_code, s.shipping_name, ' . 's.shipping_desc, s.insure, s.support_cod, gtt.configure FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s, ' . $GLOBALS['ecs']->table('goods_transport_tpl') . ' AS gtt ' . ' WHERE ' . $where . ' AND gtt.shipping_id = s.shipping_id ' . ' AND s.enabled = 1 AND gtt.user_id = \'' . $goodsInfo['user_id'] . '\' AND gtt.tid = \'' . $goodsInfo['tid'] . '\'' . ' AND (FIND_IN_SET(\'' . $region[1] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[2] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[3] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[4] . '\', gtt.region_id))' . ' LIMIT 1';
				$val = $GLOBALS['db']->getRow($sql);
			}
			else {
				$sql = 'SELECT s.shipping_id, s.shipping_code, s.shipping_name, shipping_order FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_transport_extend') . ' AS gted ON gted.tid = \'' . $goodsInfo['tid'] . '\' AND gted.ru_id = \'' . $goodsInfo['user_id'] . '\'' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods_transport_express') . ' AS gte ON gted.tid = gte.tid AND gte.ru_id = \'' . $goodsInfo['user_id'] . '\'' . ' WHERE ' . $where . ' AND FIND_IN_SET(s.shipping_id, gte.shipping_id) ' . ' AND ((FIND_IN_SET(\'' . $region[1] . '\', gted.top_area_id)) OR (FIND_IN_SET(\'' . $region[2] . '\', gted.area_id) OR FIND_IN_SET(\'' . $region[3] . '\', gted.area_id) OR FIND_IN_SET(\'' . $region[4] . '\', gted.area_id)))' . ' LIMIT 1';
				$val = $GLOBALS['db']->getRow($sql);
			}
		}

		$is_shipping = 0;

		if (!empty($val)) {
			$is_shipping = 1;
		}
		else {
			$shipping_list = available_shipping_list($region, $goodsInfo['user_id'], 1);

			if ($shipping_list) {
				$val = $shipping_list[0];
				$is_shipping = 1;
				if ($sellerShippingInfo && $sellerShippingInfo['shipping_id']) {
					$cfg = array(
						array('name' => 'item_fee', 'value' => 0),
						array('name' => 'base_fee', 'value' => 0),
						array('name' => 'step_fee', 'value' => 0),
						array('name' => 'free_money', 'value' => 100000)
						);
					if (!isset($sellerShippingInfo['configure']) && empty($sellerShippingInfo['configure'])) {
						$sellerShippingInfo['configure'] = serialize($cfg);
					}

					$val = $sellerShippingInfo;
				}
			}
		}

		if ($goodsInfo['is_shipping']) {
			$shippingFee = 0;
		}
		else if (!empty($goodsInfo['freight'])) {
			$transportInfo = get_goods_transport($goodsInfo['tid']);

			if ($transportInfo) {
				if ($transportInfo['freight_type']) {
					$transport_tpl = get_goods_transport_tpl($goodsInfo, $region);
					$shippingFee = $transport_tpl['shippingFee'];
					$is_shipping = $transport_tpl['is_shipping'];
				}
				else {
					$transport = array('top_area_id', 'area_id', 'tid', 'ru_id', 'sprice');
					$transport_where = ' AND ru_id = \'' . $goodsInfo['user_id'] . '\' AND tid = \'' . $goodsInfo['tid'] . '\'';
					$goods_transport = $GLOBALS['ecs']->get_select_find_in_set(2, $region[2], $transport, $transport_where, 'goods_transport_extend', 'area_id');
					$shippingFee = $goods_transport['sprice'];
				}
			}
			else {
				$is_shipping = 0;
			}
		}
	}
	else if ($goodsInfo['freight'] == 1) {
		if ($goodsInfo['is_shipping']) {
			$shippingFee = 0;
		}
		else {
			$shippingFee = $goodsInfo['shipping_fee'];
		}

		$is_shipping = 1;
	}

	$sql = 'SELECT gs.shipping_id,s.shipping_code,s.shipping_name,gs.shipping_fee FROM ' . $GLOBALS['ecs']->table('goods_transport_express') . ' AS gs LEFT JOIN ' . $GLOBALS['ecs']->table('shipping') . ' AS s ON gs.shipping_id=s.shipping_id WHERE gs.tid = ' . $goodsInfo['tid'] . ' AND s.shipping_code = \'fpd\'';
	$fpd_shipping = $GLOBALS['db']->getRow($sql);

	if (!empty($fpd_shipping)) {
		$shippingInfo['shipping_id'] = $fpd_shipping['shipping_id'];
		$shippingInfo['shipping_code'] = $fpd_shipping['shipping_code'];
		$shippingInfo['shipping_name'] = $fpd_shipping['shipping_name'];

		if ($transport_info['freight_type'] == 0) {
			$shippingFee = $fpd_shipping['shipping_fee'];
		}
	}

	$shippingInfo['shipping_title'] = isset($transport_info['shipping_title']) ? $transport_info['shipping_title'] : '';
	$shippingInfo['shipping_fee'] = $shippingFee;
	$shippingInfo['shipping_fee_formated'] = price_format($shippingFee, false);
	$shippingInfo['is_shipping'] = $is_shipping;
	return $shippingInfo;
}

function get_goods_transport_tpl($goodsInfo = array(), $region = array(), $shippingInfo = array(), $goods_number = 1)
{
	$goodsInfo['goods_weight'] = isset($goodsInfo['goods_weight']) ? $goodsInfo['goods_weight'] : $goodsInfo['goodsweight'];
	$goodsInfo['shop_price'] = isset($goodsInfo['shop_price']) ? $goodsInfo['shop_price'] : $goodsInfo['goods_price'];

	if (empty($shippingInfo)) {
		$is_goods = 1;
		$shippingInfo = get_seller_shipping_type($goodsInfo['user_id']);

		if (!$shippingInfo) {
			$tpl_shipping = get_goods_transport_tpl_shipping($goodsInfo['tid'], 0, $region);

			if ($tpl_shipping) {
				$shippingInfo = $tpl_shipping[0];
			}
		}
		else {
			$shippingInfo = get_goods_transport_tpl_shipping($goodsInfo['tid'], $shippingInfo['shipping_id'], $region);
		}
	}
	else {
		$is_goods = 0;
		$shippingInfo = get_goods_transport_tpl_shipping($goodsInfo['tid'], $shippingInfo['shipping_id'], $region);
	}

	$where = '';
	if ($shippingInfo && $shippingInfo['shipping_id']) {
		$where .= ' AND s.shipping_id = \'' . $shippingInfo['shipping_id'] . '\'';
	}
	else {
		$shippingInfo = get_goods_transport_tpl_shipping($goodsInfo['tid'], 0, $region, $is_goods);

		if ($shippingInfo) {
			$shippingInfo = isset($shippingInfo[0]) ? $shippingInfo[0] : array();
		}
	}

	$sql = 'SELECT gtt.*, s.shipping_id, s.shipping_code, s.shipping_name, ' . 's.shipping_desc, s.insure, s.support_cod, gtt.configure FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s, ' . $GLOBALS['ecs']->table('goods_transport_tpl') . ' AS gtt ' . ' WHERE gtt.shipping_id = s.shipping_id ' . $where . ' AND s.enabled = 1 AND gtt.user_id = \'' . $goodsInfo['user_id'] . '\' AND gtt.tid = \'' . $goodsInfo['tid'] . '\'' . ' AND (FIND_IN_SET(\'' . $region[1] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[2] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[3] . '\', gtt.region_id) OR FIND_IN_SET(\'' . $region[4] . '\', gtt.region_id))' . ' LIMIT 1';
	$val = $GLOBALS['db']->getRow($sql);
	$is_shipping = 0;

	if ($val) {
		$is_shipping = 1;
	}

	if (!$shippingInfo) {
		$shippingInfo = array('shipping_id' => 0, 'shipping_code' => '', 'configure' => '');
	}

	$shippingFee = 0;

	if ($is_shipping) {
		if (empty($shippingInfo) || $shippingInfo && empty($shippingInfo['shipping_id'])) {
			$shippingInfo = $val;
		}

		$goods_weight = $goodsInfo['goods_weight'] * $goods_number;
		$shop_price = $goodsInfo['shop_price'] * $goods_number;
		$shippingFee = shipping_fee($shippingInfo['shipping_code'], $shippingInfo['configure'], $goods_weight, $shop_price, $goods_number);
		$shippingCfg = unserialize_config($shippingInfo['configure']);
		$free_money = price_format($shippingCfg['free_money'], false);
	}

	$arr = array('shippingFee' => $shippingFee, 'shipping_fee_formated' => price_format($shippingFee, false), 'is_shipping' => $is_shipping, 'shipping_id' => $shippingInfo['shipping_id']);
	return $arr;
}

function get_goods_transport_tpl_shipping($tid = 0, $shipping_id = 0, $region = array(), $type = 0, $limit = 0)
{
	$where = '';

	if ($shipping_id) {
		$where .= ' AND gtt.shipping_id = \'' . $shipping_id . '\'';
	}

	if ($limit) {
		$where .= ' LIMIT ' . $limit;
	}

	$sql = 'SELECT gtt.*, s.shipping_name, s.shipping_code FROM ' . $GLOBALS['ecs']->table('goods_transport_tpl') . ' AS gtt' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('shipping') . ' AS s ON gtt.shipping_id = s.shipping_id' . (' WHERE gtt.tid = \'' . $tid . '\' ' . $where);
	$arr = array();

	if ($type == 1) {
		$res = $GLOBALS['db']->getAll($sql);

		foreach ($res as $key => $row) {
			$region_id = !empty($row['region_id']) ? explode(',', $row['region_id']) : array();

			if ($region) {
				foreach ($region as $rk => $rrow) {
					if ($region_id && in_array($rrow, $region_id)) {
						$arr[] = $row;
					}
					else {
						continue;
					}
				}
			}
		}
	}
	else {
		$res = $GLOBALS['db']->getAll($sql);

		foreach ($res as $key => $row) {
			$region_id = !empty($row['region_id']) ? explode(',', $row['region_id']) : array();

			if ($region) {
				foreach ($region as $rk => $rrow) {
					if ($region_id && in_array($rrow, $region_id)) {
						return $row;
					}
				}
			}
		}
	}

	return $arr;
}

function get_order_transport($goods_list, $consignee = array(), $shipping_id = 0, $shipping_code = '')
{
	$sprice = 0;
	$type_left = array();
	$freight = 0;
	if ($goods_list && $shipping_code != 'cac') {
		$custom_shipping = get_goods_custom_shipping($goods_list);
		$area_shipping = get_goods_area_shipping($goods_list, $shipping_id, $shipping_code, $consignee);

		foreach ($goods_list as $key => $row) {
			if ($row['freight'] && $row['is_shipping'] == 0) {
				if ($row['freight'] == 1) {
					$sprice += $row['shipping_fee'] * $row['goods_number'];
				}
				else {
					$trow = get_goods_transport($row['tid']);

					if ($trow['freight_type'] == 0) {
						$transport = array('top_area_id', 'area_id', 'tid', 'ru_id', 'sprice');
						$transport_where = ' AND ru_id = \'' . $row['ru_id'] . '\' AND tid = \'' . $row['tid'] . '\'';
						$goods_transport = $GLOBALS['ecs']->get_select_find_in_set(2, $consignee['city'], $transport, $transport_where, 'goods_transport_extend', 'area_id');

						if ($goods_transport) {
							$ship_transport = array('tid', 'ru_id', 'shipping_fee');
							$ship_transport_where = ' AND ru_id = \'' . $row['ru_id'] . '\' AND tid = \'' . $row['tid'] . '\'';
							$goods_ship_transport = $GLOBALS['ecs']->get_select_find_in_set(2, $shipping_id, $ship_transport, $ship_transport_where, 'goods_transport_express', 'shipping_id');
						}

						$goods_transport['sprice'] = isset($goods_transport['sprice']) ? $goods_transport['sprice'] : 0;
						$goods_ship_transport['shipping_fee'] = isset($goods_ship_transport['shipping_fee']) ? $goods_ship_transport['shipping_fee'] : 0;
						if ($custom_shipping && $trow['free_money'] <= $custom_shipping[$row['tid']]['amount'] && 0 < $trow['free_money']) {
							$is_shipping = 1;
						}
						else {
							$is_shipping = 0;
						}

						if ($is_shipping == 0) {
							if ($trow['type'] == 1) {
								$sprice += $goods_transport['sprice'] * $row['goods_number'] + $goods_ship_transport['shipping_fee'] * $row['goods_number'];
							}
							else {
								$type_left[$row['tid']] = $goods_transport['sprice'] + $goods_ship_transport['shipping_fee'];
							}
						}
					}
				}
			}
			else {
				$freight += 1;
			}
		}

		$unified_total = get_cart_unified_freight_total($type_left);
		$arr = array('sprice' => $area_shipping['shipping_fee'] + $sprice + $unified_total, 'freight' => $freight);
	}
	else {
		$arr = array('sprice' => 0, 'freight' => $freight);
	}

	return $arr;
}

function get_goods_area_shipping($goods_list, $shipping_id = 0, $shipping_code = '', $consignee = array())
{
	$tid_arr1 = array();

	foreach ($goods_list as $key => $row) {
		$tid_arr1[$row['tid']][$key] = $row;
	}

	$tid_arr2 = array();

	foreach ($tid_arr1 as $key => $row) {
		$row = !empty($row) ? array_values($row) : $row;
		$tid_arr2[$key] = array('weight' => 0, 'number' => 0, 'amount' => 0);

		foreach ($row as $gkey => $grow) {
			$tid_arr2[$key]['weight'] += $grow['goodsweight'] * $grow['goods_number'];
			$tid_arr2[$key]['number'] += $grow['goods_number'];
			$tid_arr2[$key]['amount'] += $grow['goods_price'] * $grow['goods_number'];
		}
	}

	if (empty($shipping_id)) {
		$select = array('shipping_code' => $shipping_code);
		$shipping_info = shipping_info($select, array('shipping_id'));
		$shipping_id = $shipping_info['shipping_id'];
	}

	if (empty($shipping_code)) {
		$shipping_info = shipping_info($shipping_id, array('shipping_code'));
		$shipping_code = $shipping_info['shipping_code'];
	}

	$region = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district'], $consignee['street']);
	$shipping_fee = 0;

	foreach ($tid_arr2 as $key => $row) {
		$trow = get_goods_transport($key);
		if ($trow && $trow['freight_type'] == 1) {
			$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('goods_transport_tpl') . (' WHERE tid = \'' . $key . '\' AND shipping_id = \'' . $shipping_id . '\'') . ' AND ((FIND_IN_SET(\'' . $region[1] . '\', region_id)) OR (FIND_IN_SET(\'' . $region[2] . '\', region_id) OR FIND_IN_SET(\'' . $region[3] . '\', region_id) OR FIND_IN_SET(\'' . $region[4] . '\', region_id)))' . ' LIMIT 1';
			$transport_tpl = $GLOBALS['db']->getRow($sql);
			$configure = !empty($transport_tpl) && $transport_tpl['configure'] ? unserialize($transport_tpl['configure']) : '';

			if (!empty($configure)) {
				$tid_arr2[$key]['shipping_fee'] = shipping_fee($shipping_code, $configure, $row['weight'], $row['amount'], $row['number']);
			}
			else {
				$tid_arr2[$key]['shipping_fee'] = 0;
			}

			$shipping_fee += $tid_arr2[$key]['shipping_fee'];
		}
	}

	$arr = array('tid_list' => $tid_arr2, 'shipping_fee' => $shipping_fee);
	return $arr;
}

function get_cart_unified_freight_total($total)
{
	$sprice = 0;

	if ($total) {
		foreach ($total as $key => $row) {
			$sprice += $row;
		}
	}

	return $sprice;
}

function get_goods_transport($tid = 0)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('goods_transport') . (' WHERE tid = \'' . $tid . '\' LIMIT 1');
	return $GLOBALS['db']->getRow($sql);
}

function get_goods_shipping_fee($goodsInfo, $val, $sellerShippingInfo, $warehouse_id)
{
	if ($GLOBALS['_CFG']['freight_model'] == 0) {
		$shippingFee = shipping_fee($val['shipping_code'], $val['configure'], $goodsInfo['goods_weight'], $goodsInfo['shop_price'], 1);
		$shippingCfg = unserialize_config($val['configure']);
		$free_money = price_format($shippingCfg['free_money'], false);
	}

	$arr = array('shippingFee' => $shippingFee, 'free_money' => $free_money);
	return $arr;
}

function getSellerShippingFee($sellerOrderInfo = array(), $cart_goods)
{
	$sql = 'SELECT s.shipping_id, s.shipping_code ' . 'FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s ' . ' WHERE s.shipping_id = \'' . $sellerOrderInfo['shipping_id'] . '\' LIMIT 1';
	$val = $GLOBALS['db']->getRow($sql);
	$consignee['country'] = $sellerOrderInfo['region'][0];
	$consignee['province'] = $sellerOrderInfo['region'][1];
	$consignee['city'] = $sellerOrderInfo['region'][2];
	$consignee['district'] = $sellerOrderInfo['region'][3];
	$consignee['street'] = $sellerOrderInfo['region'][4];
	$order_transpor = get_order_transport($cart_goods, $consignee, $val['shipping_id'], $val['shipping_code']);
	$shippingFee = 0;

	if ($order_transpor['freight']) {
		$shippingFee += $order_transpor['sprice'];
	}
	else {
		$shippingFee = $order_transpor['sprice'];
	}

	return $shippingFee;
}

function getStore($id = 0)
{
	$sql = 'SELECT id, ru_id, stores_name, province, city, district, stores_address, stores_tel, stores_opening_hours, stores_traffic_line, stores_img, stores_opening_hours, stores_opening_hours, stores_opening_hours FROM ' . $GLOBALS['ecs']->table('offline_store') . ' WHERE id = ' . $id;
	$res = $GLOBALS['db']->getRow($sql);

	if (!empty($res)) {
		$res['province_name'] = get_goods_region_name($res['province']);
		$res['city_name'] = get_goods_region_name($res['city']);
		$res['district_name'] = get_goods_region_name($res['district']);
	}

	return $res;
}

function shipping_fee($shipping_code, $shipping_config, $goods_weight, $goods_amount, $goods_number = '')
{
	if (!is_array($shipping_config)) {
		$shipping_config = unserialize($shipping_config);
	}

	$filename = ADDONS_PATH . 'shipping/' . $shipping_code . '.php';

	if (file_exists($filename)) {
		include_once $filename;
		$obj = new $shipping_code($shipping_config);
		return $obj->calculate($goods_weight, $goods_amount, $goods_number);
	}
	else {
		return 0;
	}
}

function get_goods_attr_id($where_select = array(), $select = array(), $attr_type = 0, $retuen_db = 0)
{
	if ($where_select) {
	}

	if ($select) {
		$select = implode(',', $select);
	}
	else {
		$select = 'ga.*, a.*';
	}

	$where = '';
	if (isset($where_select['goods_id']) && !empty($where_select['goods_id'])) {
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


?>
