<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_validate_info($user_id)
{
	$sql = 'SELECT u.mobile_phone, u.is_validated, u.email, up.pay_password, ur.bank_mobile, ur.real_name, ur.bank_card, ur.bank_name, ur.review_status FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users_paypwd') . ' AS up ON u.user_id = up.user_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users_real') . ' AS ur ON u.user_id = ur.user_id ' . (' WHERE u.user_id=\'' . $user_id . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function security_rating()
{
	global $db;
	global $ecs;
	$count = 2;
	$count_info = '';
	$Percentage = 0;
	$result = array();
	$sql = 'SELECT u.is_validated as email_validate, u.email, u.mobile_phone, up.paypwd_id, up.pay_password, ur.real_id, ur.real_name, ur.bank_card ' . ' FROM ' . $ecs->table('users') . ' AS u ' . ' LEFT JOIN ' . $ecs->table('users_paypwd') . ' AS up ON u.user_id = up.user_id ' . ' LEFT JOIN ' . $ecs->table('users_real') . ' AS ur ON u.user_id = ur.user_id AND user_type = 0 ' . (' WHERE u.user_id = \'' . $_SESSION['user_id'] . '\' ');
	$res = $db->getRow($sql);

	if ($res['email_validate']) {
		$count++;
	}

	if ($res['mobile_phone']) {
		$count++;
	}

	if ($res['pay_password']) {
		$count++;
	}

	if ($res['real_id']) {
		$count++;
	}

	switch ($count) {
	case 1:
		$count_info = $GLOBALS['_LANG']['Risk_rating'][0];
		$Percentage = 15;
		break;

	case 2:
		$count_info = $GLOBALS['_LANG']['Risk_rating'][1];
		$Percentage = 30;
		break;

	case 3:
		$count_info = $GLOBALS['_LANG']['Risk_rating'][2];
		$Percentage = 45;
		break;

	case 4:
		$count_info = $GLOBALS['_LANG']['Risk_rating'][3];
		$Percentage = 60;
		break;

	case 5:
		$count_info = $GLOBALS['_LANG']['Risk_rating'][4];
		$Percentage = 80;
		break;

	case 6:
		$count_info = $GLOBALS['_LANG']['Risk_rating'][5];
		$Percentage = 100;
		break;

	default:
		break;
	}

	$result = array('count' => $count, 'count_info' => $count_info, 'Percentage' => $Percentage);
	return $result;
}

function get_entry_criteria($entry_criteria = '')
{
	$entry_criteria = unserialize($entry_criteria);
	$rel = '';

	if (!empty($entry_criteria)) {
		$sql = ' SELECT id,criteria_name FROM' . $GLOBALS['ecs']->table('entry_criteria') . ' WHERE id ' . db_create_in($entry_criteria);
		$rel = $GLOBALS['db']->getAll($sql);

		foreach ($rel as $k => $v) {
			$child = $GLOBALS['db']->getAll(' SELECT * FROM' . $GLOBALS['ecs']->table('entry_criteria') . ' WHERE parent_id = \'' . $v['id'] . '\'');

			foreach ($child as $key => $val) {
				if ($val['type'] == 'select' && $val['option_value'] != '') {
					$child[$key]['option_value'] = explode(',', $val['option_value']);
				}

				$rel['count_charge'] += $val['charge'];

				if ($val['is_cumulative'] == 0) {
					$rel['no_cumulative_price'] += $val['charge'];
				}
			}

			$rel[$k]['child'] = $child;
		}
	}

	return $rel;
}

function upload_apply_file($image_files = array(), $file_id = array(), $url = array())
{
	foreach ($file_id as $v) {
		$flag = false;

		if (isset($image_files['error'])) {
			if ($image_files['error'][$v] == 0) {
				$flag = true;
			}
		}
		else {
			if ($image_files['tmp_name'][$v] != 'none' && $image_files['tmp_name'][$v]) {
				$flag = true;
			}
		}

		if ($flag) {
			$upload = array('name' => $image_files['name'][$v], 'type' => $image_files['type'][$v], 'tmp_name' => $image_files['tmp_name'][$v], 'size' => $image_files['size'][$v]);

			if (isset($image_files['error'])) {
				$upload['error'] = $image_files['error'][$v];
			}

			$img_original = $GLOBALS['image']->upload_image($upload);

			if ($img_original === false) {
				show_message($GLOBALS['image']->error_msg());
			}

			$img_url[$v] = $img_original;

			if (!empty($url[$v])) {
				@unlink(ROOT_PATH . $url[$v]);
				unset($url[$v]);
			}
		}
	}

	$return_file = array();

	if ($url) {
		foreach ($url as $k => $v) {
			if ($v == '') {
				unset($url[$k]);
			}
		}
	}

	if (!empty($url) && !empty($img_url)) {
		$return_file = $url + $img_url;
	}
	else if (!empty($url)) {
		$return_file = $url;
	}
	else if (!empty($img_url)) {
		$return_file = $img_url;
	}

	if (!empty($return_file)) {
		return $return_file;
	}
	else {
		return false;
	}
}

function get_seller_grade_info($num = 10, $start = 0)
{
	$sql = 'SELECT * FROM' . $GLOBALS['ecs']->table('seller_grade') . (' WHERE is_open = 1  ORDER BY id ASC LIMIT  ' . $start . ',' . $num);
	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $k => $v) {
		if ($v['entry_criteria']) {
			$entry_criteria = unserialize($v['entry_criteria']);
			$criteria = '';

			foreach ($entry_criteria as $key => $val) {
				$sql = 'SELECT criteria_name FROM' . $GLOBALS['ecs']->table('entry_criteria') . ' WHERE id = \'' . $val . '\'';
				$criteria_name = $GLOBALS['db']->getOne($sql);

				if ($criteria_name) {
					$entry_criteria[$key] = $criteria_name;
				}
			}

			$row[$k]['entry_criteria'] = implode(' , ', $entry_criteria);
		}
	}

	return $row;
}

function get_merchants_upgrade_log($num = 10, $start = 0)
{
	$sql = 'SELECT * FROM' . $GLOBALS['ecs']->table('seller_apply_info') . ' WHERE ru_id = \'' . $_SESSION['user_id'] . ('\'  ORDER BY add_time ASC LIMIT ' . $start . ',' . $num);
	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $k => $v) {
		$row[$k]['shop_name'] = get_shop_name($v['ru_id'], 1);
		$row[$k]['grade_name'] = $GLOBALS['db']->getOne('SELECT grade_name FROM ' . $GLOBALS['ecs']->table('seller_grade') . ' WHERE id = \'' . $v['grade_id'] . '\'');
		$row[$k]['add_time'] = local_date('Y-m-d H:i:s', $v['add_time']);

		if (0 < $v['pay_id']) {
			$row[$k]['pay_name'] = $GLOBALS['db']->getOne('SELECT pay_name FROM ' . $GLOBALS['ecs']->table('payment') . ' WHERE pay_id = \'' . $v['pay_id'] . '\'');
		}

		switch ($v['pay_status']) {
		case '0':
			$row[$k]['status_paid'] = '未付款';
			break;

		case '1':
			$row[$k]['status_paid'] = '已付款';
			break;
		}

		switch ($v['apply_status']) {
		case '0':
			$row[$k]['status_apply'] = '未审核';
			break;

		case '1':
			$row[$k]['status_apply'] = '审核通过';
			break;

		case '2':
			$row[$k]['status_apply'] = '审核未通过';
			break;

		case '3':
			$row[$k]['status_apply'] = '<span style=\'color:red\'>无效</span>';
			break;
		}
	}

	return $row;
}

function get_order_where_count($user_id = 0, $show_type = 0, $where = '', $action = '')
{
	$act = '';

	if ($action) {
		$act = ' AND oi.extension_code = \'' . $action . '\'';
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi' . (' WHERE oi.user_id = \'' . $user_id . '\' and oi.is_delete = \'' . $show_type . '\'') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi_2 where oi_2.main_order_id = oi.order_id) = 0 ' . ' AND oi.is_zc_order = 0' . $act . $where;
	return $GLOBALS['db']->getOne($sql);
}

function get_regions_log($type = 0, $parent = 0)
{
	$sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE region_type = \'' . $type . '\' AND parent_id = \'' . $parent . '\'');
	return $GLOBALS['db']->GetAll($sql);
}

function move_image_file_single($source, $dest)
{
	if (@copy($source, $dest)) {
		@unlink($source);
		return true;
	}

	return false;
}

function createFolder($path)
{
	if (!file_exists($path)) {
		createFolder(dirname($path));
		mkdir($path, 511);
	}
}

function create_password($pw_length = 8)
{
	$randpwd = '';

	for ($i = 0; $i < $pw_length; $i++) {
		$randpwd .= chr(mt_rand(33, 126));
	}

	return $randpwd;
}

function presale_settle_status($extension_id)
{
	$now = gmtime();
	$sql = ' SELECT pay_start_time, pay_end_time FROM ' . $GLOBALS['ecs']->table('presale_activity') . (' WHERE act_id = \'' . $extension_id . '\' AND review_status = 3 ');
	$row = $GLOBALS['db']->getRow($sql);
	$result = array();
	if ($row['pay_start_time'] <= $now && $now <= $row['pay_end_time']) {
		$result['start_time'] = local_date('Y-m-d H:i:s', $row['pay_start_time']);
		$result['end_time'] = local_date('Y-m-d H:i:s', $row['pay_end_time']);
		$result['settle_status'] = 1;
		return $result;
	}
	else if ($row['pay_end_time'] < $now) {
		$result['start_time'] = local_date('Y-m-d H:i:s', $row['pay_start_time']);
		$result['end_time'] = local_date('Y-m-d H:i:s', $row['pay_end_time']);
		$result['settle_status'] = -1;
		return $result;
	}
	else {
		$result['start_time'] = local_date('Y-m-d H:i:s', $row['pay_start_time']);
		$result['end_time'] = local_date('Y-m-d H:i:s', $row['pay_end_time']);
		$result['settle_status'] = 0;
		return $result;
	}
}

function get_explain($vid)
{
	$rz_shopName = array();
	$arr = array();
	$sql = ' SELECT use_condition, use_merchants, spec_goods, spec_cat FROM ' . $GLOBALS['ecs']->table('value_card_type') . ' AS t LEFT JOIN ' . $GLOBALS['ecs']->table('value_card') . (' AS v ON v.tid = t.id WHERE vid = \'' . $vid . '\' ');
	$row = $GLOBALS['db']->getRow($sql);

	if ($row['use_condition'] == 0) {
		$explain = $GLOBALS['_LANG']['all_goods_explain'];
	}
	else if ($row['use_condition'] == 1) {
		$sql = ' SELECT cat_name,cat_id FROM ' . $GLOBALS['ecs']->table('category') . (' WHERE cat_id IN(' . $row['spec_cat'] . ') ');
		$res = $GLOBALS['db']->getAll($sql);
		$explain = str_replace('%', cat_format($res), $GLOBALS['_LANG']['spec_cat_explain']);
	}
	else if ($row['use_condition'] == 2) {
		$explain['explain'] = str_replace('%', $row['spec_goods'], $GLOBALS['_LANG']['spec_goods_explain']);
		$explain['goods_ids'] = $row['spec_goods'];
	}
	else {
		$explain = '';
	}

	$other_explain = '';

	if ($row['use_merchants'] == 'all') {
		$other_explain = ' | ' . $GLOBALS['_LANG']['all_merchants'];
	}
	else if ($row['use_merchants'] == 'self') {
		$other_explain = ' | ' . $GLOBALS['_LANG']['self_merchants'];
	}
	else if (!empty($row['use_merchants'])) {
		$ru_ids = explode(',', $row['use_merchants']);

		if (!empty($ru_ids)) {
			foreach ($ru_ids as $k => $v) {
				$shop_name = array();
				$shop_name['shop_name'] = get_shop_name($v, 1);
				$build_uri = array('urid' => $v, 'append' => $shop_name['shop_name']);
				$domain_url = get_seller_domain_url($v, $build_uri);
				$shop_name['shop_url'] = $domain_url['domain_name'];
				$rz_shopName[] = $shop_name;
			}
		}

		$other_explain = ' | ' . $GLOBALS['_LANG']['assign_merchants'];
	}

	$arr['rz_shopNames'] = $rz_shopName;

	if ($other_explain) {
		$arr['explain'] = $explain . $other_explain;
	}
	else {
		$arr['explain'] = $explain;
	}

	return $arr;
}

function cat_format($res)
{
	if ($res) {
		$result = '';

		foreach ($res as $v) {
			$result .= '<a href="category.php?id=' . $v['cat_id'] . '" style="color:red;">' . $v['cat_name'] . '</a>' . '，';
		}

		$result = rtrim($result, '，');
		return $result;
	}
	else {
		return false;
	}
}

function get_users_auth($unionid)
{
	$sql = 'SELECT identifier AS aite_id, user_id, user_name FROM ' . $GLOBALS['ecs']->table('users_auth') . ' WHERE identifier = \'' . $unionid . '\' LIMIT 1';
	return $GLOBALS['db']->getRow($sql);
}

function get_connect_user($unionid)
{
	$sql = 'SELECT connect_code, user_id, open_id, profile FROM ' . $GLOBALS['ecs']->table('connect_user') . ' WHERE open_id = \'' . $unionid . '\' LIMIT 1';
	return $GLOBALS['db']->getRow($sql);
}

function getWebsiteList()
{
	define('WEBSITE', true);
	$filepath = ROOT_PATH . 'includes/website/';
	$openfn = opendir($filepath);
	$name = '';
	$web = array();

	while ($file = readdir($openfn)) {
		if ($file != '.' && $file != '..' && $file != 'jntoo.php' && $file != 'config' && $file != 'tb_callback.php' && $file != 'tb_index.php' && substr($file, strlen($file) - 4) == '.php' && substr($file, 0, 3) != 'cls') {
			include_once $filepath . $file;

			if (file_exists($filepath . 'config/' . $web[$i]['type'] . '_config.php')) {
				$web[$i]['install'] = 1;
			}
			else {
				$web[$i]['install'] = 0;
			}

			$web[$i]['path'] = $filepath . $file;
			$web[$i]['file'] = $file;
		}
	}

	closedir($openfn);
	return $web;
}

function get_goods_report_list($num = 10, $start = 0)
{
	$sql = 'SELECT report_id,goods_image,goods_name,goods_id,title_id,type_id,add_time,report_state,handle_type FROM' . $GLOBALS['ecs']->table('goods_report') . 'WHERE user_id = \'' . $_SESSION['user_id'] . ('\' AND report_state < 3  ORDER BY add_time DESC LIMIT  ' . $start . ',' . $num);
	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $k => $v) {
		if (0 < $v['title_id']) {
			$sql_title = 'SELECT title_name FROM ' . $GLOBALS['ecs']->table('goods_report_title') . 'WHERE title_id = \'' . $v['title_id'] . '\'';
			$row[$k]['title_name'] = $GLOBALS['db']->getOne($sql_title);
		}

		if (0 < $v['type_id']) {
			$sql_type = 'SELECT type_name FROM ' . $GLOBALS['ecs']->table('goods_report_type') . 'WHERE type_id = \'' . $v['type_id'] . '\'';
			$row[$k]['type_name'] = $GLOBALS['db']->getOne($sql_type);
		}

		if (0 < $v['add_time']) {
			$row[$k]['add_time'] = local_date('Y-m-d H:i:s', $v['add_time']);
		}

		$row[$k]['url'] = build_uri('goods', array('gid' => $v['goods_id']), $v['goods_name']);
		$sql = 'SELECT user_id FROM' . $GLOBALS['ecs']->table('goods') . 'WHERE goods_id = \'' . $v['goods_id'] . '\' LIMIT 1';
		$basic_info = get_seller_shopinfo($GLOBALS['db']->getOne($sql));
		$row[$k]['shop_name'] = $basic_info['shop_name'];
		$row[$k]['goods_thumb'] = get_image_path($v['goods_id'], $v['goods_image']);
	}

	return $row;
}

function get_complaint_list($num = 10, $start = 0, $where = '', $is_complaint = 0)
{
	$sql = 'SELECT IFNULL(bai.complaint_id,0) AS is_complaint,bai.complaint_state,bai.complaint_active,og.ru_id, oi.order_id, oi.order_sn, oi.add_time, oi.shipping_time, ' . '(oi.goods_amount + oi.shipping_fee + oi.insure_fee + oi.pay_fee + oi.pack_fee + oi.card_fee + oi.tax - oi.discount) AS total_fee, og.goods_id, ' . ' oi.shipping_name, oi.tel ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi' . ' left join ' . $GLOBALS['ecs']->table('order_goods') . ' as og on oi.order_id = og.order_id' . ' left join ' . $GLOBALS['ecs']->table('complaint') . ' as bai on oi.order_id = bai.order_id' . $left_join . ' WHERE oi.user_id = \'' . $_SESSION['user_id'] . '\' and oi.is_delete =0 ' . $where . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = oi.order_id) = 0 ' . (' group by oi.order_id ORDER BY oi.add_time DESC LIMIT  ' . $start . ',' . $num);
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$noTime = gmtime();
		$ru_id = $row['ru_id'];
		$row['order_goods'] = get_order_goods_toInfo($row['order_id']);
		$order_id = $row['order_id'];
		$sql = 'select kf_type, kf_ww, kf_qq  from ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' where ru_id=\'' . $ru_id . '\'');
		$basic_info = $GLOBALS['db']->getRow($sql);
		$row['shop_name'] = get_shop_name($ru_id, 1);
		$row['shop_ru_id'] = $ru_id;
		$build_uri = array('urid' => $ru_id, 'append' => $row['shop_name']);
		$domain_url = get_seller_domain_url($ru_id, $build_uri);
		$row['shop_url'] = $domain_url['domain_name'];

		if ($basic_info['kf_qq']) {
			$kf_qq = array_filter(preg_split('/\\s+/', $basic_info['kf_qq']));
			$kf_qq = explode('|', $kf_qq[0]);

			if (!empty($kf_qq[1])) {
				$kf_qq_one = $kf_qq[1];
			}
			else {
				$kf_qq_one = '';
			}
		}
		else {
			$kf_qq_one = '';
		}

		if ($basic_info['kf_ww']) {
			$kf_ww = array_filter(preg_split('/\\s+/', $basic_info['kf_ww']));
			$kf_ww = explode('|', $kf_ww[0]);

			if (!empty($kf_ww[1])) {
				$kf_ww_one = $kf_ww[1];
			}
			else {
				$kf_ww_one = '';
			}
		}
		else {
			$kf_ww_one = '';
		}

		if ($GLOBALS['_CFG']['customer_service'] == 0) {
			$ru_id = 0;
		}
		else {
			$ru_id = $row['ru_id'];
		}

		$shop_information = get_shop_name($ru_id);

		if ($ru_id == 0) {
			if ($GLOBALS['db']->getOne('SELECT kf_im_switch FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = 0', true)) {
				$row['is_dsc'] = true;
			}
			else {
				$row['is_dsc'] = false;
			}
		}
		else {
			$row['is_dsc'] = false;
		}

		$row['has_talk'] = 0;

		if (1 < $row['complaint_state']) {
			$sql = 'SELECT view_state FROM' . $GLOBALS['ecs']->table('complaint_talk') . 'WHERE complaint_id=\'' . $row['is_complaint'] . '\' ORDER BY talk_time DESC';
			$talk_list = $GLOBALS['db']->getAll($sql);

			if ($talk_list) {
				foreach ($talk_list as $k => $v) {
					if ($v['view_state']) {
						$view_state = explode(',', $v['view_state']);

						if (!in_array('user', $view_state)) {
							$row['has_talk'] = 1;
							break;
						}
					}
				}
			}
		}

		$arr[] = array('order_id' => $row['order_id'], 'order_sn' => $row['order_sn'], 'order_time' => local_date($GLOBALS['_CFG']['time_format'], $row['add_time']), 'is_IM' => $shop_information['is_IM'], 'is_dsc' => $row['is_dsc'], 'ru_id' => $row['ru_id'], 'shop_name' => $row['shop_name'], 'shop_url' => $row['shop_url'], 'order_goods' => $row['order_goods'], 'no_picture' => $GLOBALS['_CFG']['no_picture'], 'kf_type' => $basic_info['kf_type'], 'kf_ww' => $kf_ww_one, 'kf_qq' => $kf_qq_one, 'total_fee' => price_format($row['total_fee'], false), 'is_complaint' => $row['is_complaint'], 'complaint_state' => $row['complaint_state'], 'complaint_active' => $row['complaint_active'], 'has_talk' => $row['has_talk']);
	}

	return $arr;
}

function complaint_images_list($user_id = 0, $order_id = 0, $where = '')
{
	$sql = 'SELECT img_id as id , order_id, complaint_id, user_id, img_file as comment_img FROM ' . $GLOBALS['ecs']->table('complaint_img') . (' WHERE user_id = \'' . $user_id . '\' AND order_id = \'' . $order_id . '\' ' . $where . ' ORDER BY  id DESC');
	$img_list = $GLOBALS['db']->getAll($sql);

	if ($img_list) {
		foreach ($img_list as $key => $row) {
			$img_list[$key]['comment_img'] = get_image_path($row['id'], $row['comment_img']);
		}
	}

	return $img_list;
}

function report_images_list($user_id = 0, $where = '')
{
	$sql = 'SELECT img_id as id , goods_id, report_id, user_id, img_file as comment_img FROM ' . $GLOBALS['ecs']->table('goods_report_img') . (' WHERE user_id = \'' . $user_id . '\' ' . $where . ' ORDER BY  id DESC');
	$img_list = $GLOBALS['db']->getAll($sql);

	if ($img_list) {
		foreach ($img_list as $key => $row) {
			$img_list[$key]['comment_img'] = get_image_path($row['id'], $row['comment_img']);
		}
	}

	return $img_list;
}

function get_vat_consignee($id = 0)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('users_vat_invoices_info') . (' WHERE id = \'' . $id . '\'');
	return $GLOBALS['db']->getRow($sql);
}

function get_users_log_list($num = 10, $start = 0, $user_id = 0)
{
	$where = '';

	if (0 < $user_id) {
		$where .= ' AND user_id = \'' . $user_id . '\' ';
	}

	$where .= ' AND change_type != 9 ';
	$sql = 'SELECT log_id, user_id, change_time, change_type, ip_address, change_city, logon_service, admin_id FROM' . $GLOBALS['ecs']->table('users_log') . ('WHERE 1 ' . $where . '  ORDER BY change_time DESC LIMIT  ' . $start . ',' . $num);
	$row = $GLOBALS['db']->getAll($sql);

	if ($row) {
		foreach ($row as $k => $v) {
			if (0 < $v['change_time']) {
				$row[$k]['change_time'] = local_date('Y-m-d H:i:s', $v['change_time']);
			}

			if (0 < $v['admin_id']) {
				$sql = 'SELECT user_name FROM' . $GLOBALS['ecs']->table('admin_user') . ' WHERE user_id = \'' . $v['admin_id'] . '\'';
				$row[$k]['admin_name'] = '管理员：' . $GLOBALS['db']->getOne($sql);
			}
		}
	}

	return $row;
}

function is_invalid($goods_sid = 0)
{
	$sql = ' SELECT s.is_putaway, acti_time FROM ' . $GLOBALS['ecs']->table('seckill_goods') . ' AS sg LEFT JOIN ' . $GLOBALS['ecs']->table('seckill') . ' AS s ON sg.sec_id = s.sec_id ' . (' WHERE sg.id = \'' . $goods_sid . '\' ');
	$row = $GLOBALS['db']->getRow($sql);
	if ($row['is_putaway'] == 0 || $row['acti_time'] < gmtime()) {
		return true;
	}
	else {
		return false;
	}
}

function deal_js_strlen($str)
{
	$strlen = strlen($str);
	$zhcn_len = 0;
	$pattern = '/[^\\x00-\\x80]+/';

	if (preg_match_all($pattern, $str, $matches)) {
		$words = $matches[0];

		foreach ($words as $word) {
			$zhcn_len += strlen($word);
		}
	}

	$left_len = $strlen - $zhcn_len;
	$deal_len = $left_len + $zhcn_len / 3 * 2;
	return $deal_len;
}

function get_user_accountlog_count($user_id = 0, $account_type = '')
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('account_log') . (' WHERE user_id = \'' . $user_id . '\'') . (' AND ' . $account_type . ' <> 0 ');
	$record_count = $GLOBALS['db']->getOne($sql);
	return $record_count;
}

function get_user_accountlog_list($user_id = 0, $account_type = '', $pager = array())
{
	$account_log = array();
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('account_log') . (' WHERE user_id = \'' . $user_id . '\'') . (' AND ' . $account_type . ' <> 0 ') . ' ORDER BY log_id DESC';

	if ($pager) {
		$res = $GLOBALS['db']->selectLimit($sql, $pager['size'], $pager['start']);
	}
	else {
		$res = $GLOBALS['db']->query($sql);
	}

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$row['change_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['change_time']);
		$row['type'] = 0 < $row[$account_type] ? $GLOBALS['_LANG']['account_inc'] : $GLOBALS['_LANG']['account_dec'];
		$row['user_money'] = price_format(abs($row['user_money']), false);
		$row['frozen_money'] = price_format(abs($row['frozen_money']), false);
		$row['rank_points'] = abs($row['rank_points']);
		$row['pay_points'] = abs($row['pay_points']);
		$row['short_change_desc'] = sub_str($row['change_desc'], 60);
		$row['amount'] = $row[$account_type];
		$account_log[] = $row;
	}

	return $account_log;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require dirname(__FILE__) . '/includes/lib_code.php';
require ROOT_PATH . '/includes/lib_area.php';
require(ROOT_PATH . '/includes/lib_wholesale.php');/*jin*meng*wang*luo*/
$warehouse_other = array('province_id' => $province_id, 'city_id' => $city_id);
$warehouse_area_info = get_warehouse_area_info($warehouse_other);
$region_id = $warehouse_area_info['region_id'];
$area_id = $warehouse_area_info['area_id'];
$area_city = $warehouse_area_info['city_id'];
require_once ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
include_once ROOT_PATH . '/includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);
get_request_filter();
$user_id = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$action = isset($_REQUEST['act']) && !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';
$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
$smarty->assign('affiliate', $affiliate);
$back_act = '';
$smarty->assign('use_value_card', $GLOBALS['_CFG']['use_value_card']);

if (defined('THEME_EXTENSION')) {
	$smarty->assign('user_passport', array('login', 'register', 'get_password', 'reset_password'));
}

$not_login_arr = array('login', 'act_login', 'register', 'act_register', 'act_edit_password', 'get_password', 'send_pwd_email', 'get_pwd_mobile', 'password', 'signin', 'add_tag', 'collect', 'return_to_cart', 'logout', 'email_list', 'validate_email', 'send_hash_mail', 'order_query', 'is_registered', 'check_email', 'clear_history', 'qpassword_name', 'get_passwd_question', 'check_answer', 'oath', 'oath_login', 'other_login', 'is_mobile_phone', 'check_phone', 'captchas', 'phone_captcha', 'code_notice', 'captchas_pass', 'oath_register', 'is_user', 'is_login_captcha', 'is_register_captcha', 'is_mobile_code', 'oath_remove', 'oath_weixin_login', 'user_email_verify', 'user_email_send', 'add_value_card', 'email_send_succeed', 'pay_pwd', 'checkd_email_send_code', 'checkorder');
$ui_arr = array('register', 'act_register', 'login', 'profile', 'order_list', 'order_detail', 'auction_order_detail', 'order_delete_restore', 'order_to_query', 'order_recycle', 'auction_order_recycle', 'address_list', 'address', 'collection_list', 'store_list', 'account_safe', 'account_bind', 'focus_brand', 'message_list', 'tag_list', 'get_password', 'get_pwd_mobile', 'reset_password', 'booking_list', 'add_booking', 'account_raply', 'commented_view', 'crowdfunding', 'to_paid', 'wholesale_buy', 'wholesale_purchase', 'purchase_info', 'purchase_edit', 'purchase_delete', 'wholesale_batch_applied', 'wholesale_return_list', 'wholesale_return_detail', 'wholesale_goods_order', 'wholesale_apply_return', 'wholesale_affirm_received', 'apply_suppliers', 'wholesale_return', 'account_deposit', 'account_log', 'account_detail', 'act_account', 'pay', 'default', 'bonus', 'value_card', 'value_card_info', 'group_buy', 'group_buy_detail', 'affiliate', 'comment_list', 'validate_email', 'track_packages', 'transform_points', 'qpassword_name', 'get_passwd_question', 'check_answer', 'service_detail', 'account_paypoints', 'account_rankpoints', 'return_list', 'apply_return', 'apply_info', 'batch_applied', 'submit_return', 'goods_order', 'return_detail', 'edit_express', 'return_shipping', 'face', 'check_comm', 'single_sun', 'single_sun_insert', 'single_list', 'user_picture', 'ajax_del_address', 'ajax_add_address', 'vat_insert', 'vat_update', 'vat_remove', 'account_complaint', 'account_complaint_insert', 'ajax_make_address', 'ajax_update_address', 'ajax_BatchCancelFollow', 'baitiao', 'repay_bt', 'take_list', 'merchants_upgrade', 'grade_load', 'application_grade', 'application_grade_edit', 'merchants_upgrade_log', 'confirm_inventory', 'update_submit', 'coupons', 'complaint_list', 'complaint_info', 'complaint_apply', 'return_order_status', 'purchase', 'want_buy', 'invoice', 'illegal_report', 'arbitration', 'vat_invoice_info', 'vat_consignee', 'auction', 'auction_list', 'snatch_list', 'users_log', 'baitiao_pay_log', 'apply_delivery');

if (empty($_SESSION['user_id'])) {
	if (!in_array($action, $not_login_arr)) {
		if (in_array($action, $ui_arr)) {
			if (!empty($_SERVER['QUERY_STRING'])) {
				$back_act = 'user.php?' . strip_tags($_SERVER['QUERY_STRING']);
			}

			$action = 'login';
		}
		else if ($action != 'act_add_bonus') {
			exit($_LANG['require_login']);
		}
	}
}

if (defined('THEME_EXTENSION')) {
	$footer = array('login', 'act_login', 'register', 'act_register', 'act_edit_password', 'get_password', 'send_pwd_email', 'get_pwd_mobile', 'password');

	if (in_array($action, $footer)) {
		$smarty->assign('footer', 1);
	}
}

$sql = 'SELECT user_id FROM ' . $ecs->table('merchants_shop_information') . (' WHERE user_id = \'' . $user_id . '\' AND merchants_audit != 2');
$is_apply = $db->getOne($sql, true);
$smarty->assign('is_apply', $is_apply);
$user_default_info = get_user_default($user_id);
$smarty->assign('user_default_info', $user_default_info);

if (in_array($action, $ui_arr)) {
	assign_template();
	$position = assign_ur_here(0, $_LANG['user_center']);
	$smarty->assign('page_title', $position['title']);
	$categories_pro = get_category_tree_leve_one();
	$smarty->assign('categories_pro', $categories_pro);
	$smarty->assign('ur_here', $position['ur_here']);
	$sql = 'SELECT value FROM ' . $ecs->table('shop_config') . ' WHERE id = 419';
	$row = $db->getRow($sql);
	$car_off = $row['value'];
	$smarty->assign('car_off', $car_off);
	if (!empty($_CFG['points_rule']) && unserialize($_CFG['points_rule'])) {
		$smarty->assign('show_transform_points', 1);
	}

	$smarty->assign('helps', get_shop_help());
	$smarty->assign('data_dir', DATA_DIR);
	$smarty->assign('action', $action);
	$smarty->assign('lang', $_LANG);
	$info = $user_default_info;

	if ($user_id) {
		if (!$info['is_validated'] && $_CFG['user_login_register'] == 1) {
			$Location = $ecs->url() . 'user.php?act=user_email_verify';
			header('location:' . $Location);
			exit();
		}
	}

	$sql = 'SELECT user_id FROM ' . $ecs->table('admin_user') . ' WHERE ru_id = \'' . $_SESSION['user_id'] . '\'';
	$is_merchants = 0;

	if ($db->getOne($sql, true)) {
		$is_merchants = 1;
	}

	$smarty->assign('is_merchants', $is_merchants);
	$smarty->assign('shop_reg_closed', $GLOBALS['_CFG']['shop_reg_closed']);
	$smarty->assign('filename', 'user');
}

$smarty->assign('wholesale_use', judge_supplier_enabled() && judge_wholesale_use(1));

if ($action == 'default') {
	include_once ROOT_PATH . 'includes/lib_clips.php';

	if ($rank = get_rank_info()) {
		$h = date('G');

		if ($h < 11) {
			$rank['time_reminder'] = $_LANG['greet'][0];
		}
		else if ($h < 13) {
			$rank['time_reminder'] = $_LANG['greet'][1];
		}
		else if ($h < 17) {
			$rank['time_reminder'] = $_LANG['greet'][2];
		}
		else {
			$rank['time_reminder'] = $_LANG['greet'][3];
		}

		$smarty->assign('rank', $rank);

		if (!empty($rank['next_rank_name'])) {
			$smarty->assign('next_rank_name', sprintf($_LANG['next_level'], $rank['next_rank'], $rank['next_rank_name']));
		}
	}

	$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('order_info') . ' as oi_1' . (' WHERE oi_1.user_id = \'' . $user_id . '\' and oi_1.is_delete = 0 ') . ' AND oi_1.is_zc_order = 0 ' . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi_2 where oi_2.main_order_id = oi_1.order_id) = 0 ');
	$order_list = get_default_user_orders($user_id, $record_count, ' AND oi.is_zc_order = 0 ');

	if (defined('THEME_EXTENSION')) {
		foreach ($order_list as $k => $v) {
			$order_list[$k]['goods'] = get_order_goods_toInfo($v['order_id']);
		}

		$signNum = get_user_order_comment_list($user_id, 1, 0);
		$smarty->assign('signNum', $signNum);
		$sql = 'SELECT u.is_validated as email_validate, u.email, u.mobile_phone, up.paypwd_id, ur.real_id, ur.real_name, ur.bank_card ' . ' FROM ' . $ecs->table('users') . ' AS u ' . ' LEFT JOIN ' . $ecs->table('users_paypwd') . ' AS up ON u.user_id = up.user_id ' . ' LEFT JOIN ' . $ecs->table('users_real') . ' AS ur ON u.user_id = ur.user_id AND user_type = 0 ' . (' WHERE u.user_id = \'' . $user_id . '\' ');
		$res = $db->getRow($sql);
		$smarty->assign('validate', $res);
		$sql = ' SELECT COUNT(*) AS num, SUM(c.cou_money) AS money FROM ' . $ecs->table('coupons_user') . ' cu LEFT JOIN ' . $ecs->table('coupons') . ' c ON c.cou_id=cu.cou_id LEFT JOIN ' . $ecs->table('order_info') . (' o ON cu.order_id=o.order_id WHERE cu.user_id = \'' . $user_id . '\' AND cu.is_use = 0 ');
		$cou = $db->getRow($sql);
		$cou['money'] = price_format($cou['money']);
		$smarty->assign('coupons', $cou);
		$sql = ' SELECT COUNT(*) AS num, SUM(card_money) AS money FROM ' . $ecs->table('value_card') . (' WHERE user_id = \'' . $user_id . '\' ');
		$vc = $db->getRow($sql);
		$vc['money'] = price_format($vc['money']);
		$smarty->assign('value_card', $vc);
	}

	$smarty->assign('order_list', $order_list);
	$collection_goods = get_default_collection_goods($user_id);
	$smarty->assign('collection_goods', $collection_goods);
	$smarty->assign('guess_goods', get_guess_goods($user_id));
	$helpart_list = get_user_helpart();
	$smarty->assign('helpart_list', $helpart_list);
	$info = get_user_default($user_id);
	if (!$info['is_validated'] && $_CFG['user_login_register'] == 1) {
		$Location = $ecs->url() . 'user.php?act=user_email_verify';
		header('location:' . $Location);
		exit();
	}

	$where_stay = ' AND   oi.order_status = \'' . OS_UNCONFIRMED . '\'';
	$unconfirmed = get_order_where_count($user_id, 0, $where_stay);
	$smarty->assign('unconfirmed', $unconfirmed);
	$where_pay = ' AND   oi.order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) . ' AND oi.pay_status ' . db_create_in(array(PS_UNPAYED, PS_PAYED_PART)) . ' AND ( oi.shipping_status ' . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . ' OR oi.pay_id ' . db_create_in(get_payment_id_list(false)) . ') ';
	$pay_count = get_order_where_count($user_id, 0, $where_pay);
	$smarty->assign('pay_count', $pay_count);
	$where_confirmed = ' AND oi.order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) . '  AND oi.shipping_status = \'' . SS_SHIPPED . '\' AND oi.pay_status ' . db_create_in(array(PS_PAYED, PS_PAYING));
	$to_confirm_order = get_order_where_count($user_id, 0, $where_confirmed);
	$smarty->assign('to_confirm_order', $to_confirm_order);
	$where_complete = ' AND oi.order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) . '  AND oi.shipping_status = \'' . SS_RECEIVED . '\' AND oi.pay_status ' . db_create_in(array(PS_PAYED, PS_PAYING));
	$to_finished = get_order_where_count($user_id, 0, $where_complete);
	$smarty->assign('to_finished', $to_finished);

	if (defined('THEME_EXTENSION')) {
		$smarty->assign('security_rating', security_rating());
	}

	unset($_SESSION['qqoath']);
	unset($_SESSION['weibooath']);
	unset($_SESSION['wechatoath']);
	$smarty->assign('info', $info);
	$smarty->assign('user_notice', $_CFG['user_notice']);
	$smarty->assign('prompt', get_user_prompt($user_id));
	$smarty->display('user_clips.dwt');
}

if ($action == 'register') {
	if (0 < intval($_CFG['sms_signin'])) {
		$sms_security_code = $_SESSION['sms_security_code'] = rand(1000, 9999);
		$smarty->assign('sms_security_code', $sms_security_code);
		$smarty->assign('enabled_sms_signin', 1);
	}

	if ((!isset($back_act) || empty($back_act)) && isset($GLOBALS['_SERVER']['HTTP_REFERER'])) {
		$back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
	}

	$sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
	$extend_info_list = $db->getAll($sql);
	$smarty->assign('extend_info_list', $extend_info_list);
	if (intval($_CFG['captcha']) & CAPTCHA_REGISTER && 0 < gd_version()) {
		$smarty->assign('enabled_captcha', 1);
		$smarty->assign('rand', mt_rand());
	}

	$smarty->assign('passwd_questions', $_LANG['passwd_questions']);
	$smarty->assign('shop_reg_closed', $_CFG['shop_reg_closed']);
	$smarty->assign('sms_register', $_CFG['sms_signin']);
	$smarty->assign('regist_banner', 'regist_banner');
	$register_article_id = build_uri('article', array('aid' => $_CFG['register_article_id']), $_LANG['protocol_bind']);
	$smarty->assign('register_article_id', $register_article_id);
	$smarty->display('user_passport.dwt');
}
else if ($action == 'act_register') {
	if ($_CFG['shop_reg_closed']) {
		$smarty->assign('action', 'register');
		$smarty->display('user_passport.dwt');
	}
	else {
		include_once ROOT_PATH . 'includes/lib_passport.php';
		$_POST = get_request_filter($_POST, 1);
		$username = isset($_POST['username']) ? compile_str(trim($_POST['username'])) : '';
		$password = isset($_POST['password']) ? compile_str(trim($_POST['password'])) : '';
		$email = isset($_POST['email']) ? compile_str(trim($_POST['email'])) : '';
		$other['msn'] = isset($_POST['extend_field1']) ? compile_str(trim($_POST['extend_field1'])) : '';
		$other['qq'] = isset($_POST['extend_field2']) ? compile_str(trim($_POST['extend_field2'])) : '';
		$other['office_phone'] = isset($_POST['extend_field3']) ? compile_str(trim($_POST['extend_field3'])) : '';
		$other['home_phone'] = isset($_POST['extend_field4']) ? compile_str(trim($_POST['extend_field4'])) : '';
		$sel_question = empty($_POST['sel_question']) ? '' : compile_str($_POST['sel_question']);
		$passwd_answer = isset($_POST['passwd_answer']) ? compile_str(trim($_POST['passwd_answer'])) : '';
		$other['mobile_phone'] = isset($_POST['mobile_phone']) ? compile_str(trim($_POST['mobile_phone'])) : '';
		$other['mobile_code'] = isset($_POST['mobile_code']) ? compile_str(trim($_POST['mobile_code'])) : '';
		$back_act = isset($_POST['back_act']) ? compile_str(trim($_POST['back_act'])) : '';
		$register_mode = isset($_POST['register_type']) ? intval($_POST['register_type']) : 0;
		$js_strlen = deal_js_strlen($username);

		if ($js_strlen < 4) {
			show_message($_LANG['passport_js']['username_shorter']);
		}

		if (15 < $js_strlen) {
			show_message($_LANG['passport_js']['msg_un_length']);
		}

		if (strlen($password) < 6) {
			show_message($_LANG['passport_js']['password_shorter']);
		}

		if (0 < strpos($password, ' ')) {
			show_message($_LANG['passwd_balnk']);
		}

		if (intval($_CFG['captcha']) & CAPTCHA_REGISTER && 0 < gd_version()) {
			if (empty($_POST['captcha'])) {
				show_message($_LANG['invalid_captcha'], $_LANG['sign_up'], 'user.php?act=register', 'error');
			}

			$seKey = 'mobile_phone';
			$captcha = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';
			$verify = new Verify();
			$captcha_code = $verify->check($captcha, $seKey);

			if (!$captcha_code) {
				show_message($_LANG['invalid_captcha'], $_LANG['sign_up'], 'user.php?act=register', 'error');
			}
		}

		if (isset($_POST['mobile_code']) && !empty($other['mobile_code']) && $other['mobile_code'] != $_SESSION['sms_mobile_code']) {
			show_message($_LANG['msg_mobile_code_not_correct'], $_LANG['sign_up'], 'user.php?act=register', 'error');
		}

		if (register($username, $password, $email, $other, $register_mode) !== false) {
			$sql = 'SELECT id FROM ' . $ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';
			$fields_arr = $db->getAll($sql);
			$extend_field_str = '';

			foreach ($fields_arr as $val) {
				$extend_field_index = 'extend_field' . $val['id'];

				if (!empty($_POST[$extend_field_index])) {
					$temp_field_content = 100 < strlen($_POST[$extend_field_index]) ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];
					$extend_field_str .= ' (\'' . $_SESSION['user_id'] . '\', \'' . $val['id'] . '\', \'' . compile_str($temp_field_content) . '\'),';
				}
			}

			$extend_field_str = substr($extend_field_str, 0, -1);

			if ($extend_field_str) {
				$sql = 'INSERT INTO ' . $ecs->table('reg_extend_info') . ' (`user_id`, `reg_field_id`, `content`) VALUES' . $extend_field_str;
				$db->query($sql);
			}

			if (!empty($passwd_answer) && !empty($sel_question)) {
				$sql = 'UPDATE ' . $ecs->table('users') . (' SET `passwd_question`=\'' . $sel_question . '\', `passwd_answer`=\'' . $passwd_answer . '\'  WHERE `user_id`=\'') . $_SESSION['user_id'] . '\'';
				$db->query($sql);
			}

			if ($GLOBALS['_CFG']['member_email_validate'] && $GLOBALS['_CFG']['send_verify_email']) {
				send_regiter_hash($_SESSION['user_id']);
			}

			$ucdata = empty($user->ucdata) ? '' : $user->ucdata;
			if (!$register_mode && $_CFG['user_login_register'] == 1) {
				header('Location:user.php?act=user_email_verify');
			}
			else {
				header('Location:user.php');
			}
		}
		else {
			$err->show($_LANG['sign_up'], 'user.php?act=register');
		}
	}
}
else if ($action == 'user_email_verify') {
	assign_template();

	if (!$user_id) {
		header('Location: ' . $ecs->url());
	}

	$position = assign_ur_here(0, $_LANG['bind_login']);
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('helps', get_shop_help());
	$smarty->assign('data_dir', DATA_DIR);
	$info = get_user_default($user_id);
	$smarty->assign('info', $info);
	$smarty->display('user_email_verify.dwt');
}
else if ($action == 'user_email_send') {
	include ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$res = array('result' => '', 'error' => 0);
	$info = get_user_default($user_id);
	$type = isset($_REQUEST['type']) && !empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
	$info['email'] = isset($_REQUEST['email']) ? addslashes(trim($_REQUEST['email'])) : $info['email'];
	$is_error = 0;
	if (isset($_REQUEST['email']) && $type == 1) {
		$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE email = \'' . addslashes(trim($_REQUEST['email'])) . '\'';

		if ($GLOBALS['db']->getOne($sql)) {
			$is_error = 1;
		}
	}

	$result = false;
	if ($info['email'] && $is_error == 0) {
		$user_email_verify = rand(1000, 9999);
		$_SESSION['user_email_verify'] = $user_email_verify;
		$template = get_mail_template('user_register');
		$smarty->assign('user_name', $info['username']);
		$smarty->assign('register_code', $user_email_verify);
		$content = $smarty->fetch('str:' . $template['template_content']);
		$result = send_mail($_CFG['shop_name'], $info['email'], $template['template_subject'], $content, $template['is_html']);
	}

	if (!$result) {
		echo 'false';
	}
	else {
		echo 'ok';
	}
}
else if ($action == 'email_send_succeed') {
	$email = isset($_REQUEST['email']) ? addslashes(trim($_REQUEST['email'])) : '';
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET is_validated = 1, email = \'' . $email . ('\' WHERE user_id = \'' . $user_id . '\'');
	$GLOBALS['db']->query($sql);
	ecs_header('Location: ' . $ecs->url() . 'user.php');
}
else if ($action == 'checkd_email_send_code') {
	$code = isset($_REQUEST['send_code']) ? intval($_REQUEST['send_code']) : '';
	$error = true;

	if ($_SESSION['user_email_verify'] == $code) {
		$error = true;
	}
	else {
		$error = false;
	}

	exit(json_encode($error));
}
else if ($action == 'oath') {
	$type = empty($_REQUEST['type']) ? '' : $_REQUEST['type'];

	if ($type == 'taobao') {
		header('location:includes/website/tb_index.php');
		exit();
	}

	$user_callblock = isset($_GET['user_callblock']) && !empty($_GET['user_callblock']) ? urldecode(trim($_GET['user_callblock'])) : '';
	$_SESSION['user_callblock'] = $user_callblock;
	include_once ROOT_PATH . 'includes/website/jntoo.php';
	$c = &website($type);

	if ($c) {
		if (empty($_REQUEST['callblock'])) {
			if (empty($_REQUEST['callblock']) && isset($GLOBALS['_SERVER']['HTTP_REFERER'])) {
				$back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? 'index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
			}
			else {
				$back_act = 'index.php';
			}
		}
		else {
			$back_act = trim($_REQUEST['callblock']);
		}

		$open = empty($_REQUEST['open']) ? 0 : intval($_REQUEST['open']);

		if ($type == 'qq') {
			$url = $c->login(substr($ecs->url(), 0, -1) . '/index.php');
		}
		else {
			$oath_where = '';
			if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
				$oath_where .= '&user_id=' . $_SESSION['user_id'];
				$oath_where .= '&jump=account_bind';
			}

			$url = $c->login($ecs->url() . 'user.php?act=oath_login&type=' . $type . '&callblock=' . urlencode($back_act) . '&open=' . $open . $oath_where);
		}

		if (!$url) {
			show_message($c->get_error(), '首页', $ecs->url(), 'error');
		}

		header('Location: ' . $url);
	}
	else {
		show_message('服务器尚未注册该插件！', '首页', $ecs->url(), 'error');
	}
}
else if ($action == 'oath_login') {
	assign_template();
	$position = assign_ur_here(0, $lANG['bind_login']);
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('helps', get_shop_help());
	$smarty->assign('data_dir', DATA_DIR);
	$access = array();
	$info = array();
	if (!empty($_GET['callblock']) && 0 < intval($_GET['error_code'])) {
		$return_url = urldecode(trim($_GET['callblock']));
		header('Location:' . $return_url);
		exit();
	}

	$type = empty($_REQUEST['type']) ? '' : $_REQUEST['type'];
	$user_id = !isset($_REQUEST['user_id']) && empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
	$jump = !isset($_REQUEST['jump']) ? '' : compile_str($_REQUEST['jump']);
	include_once ROOT_PATH . 'includes/cls_login.php';
	include_once ROOT_PATH . 'includes/website/jntoo.php';
	$c = &website($type);
	$Loaction = 'user.php?act=oath&type=' . $type;

	if ($c) {
		$access = $c->getAccessToken();

		if (!$access) {
			ecs_header('Location: ' . $Loaction . "\n");
		}

		$c->setAccessToken($access);
		$info = $c->getMessage();
		$ecs_login = new ecs_login($access['access_token']);
		$unionid = $ecs_login->get_unionid();
		$info['info_user_id'] = $type . '_' . $info['user_id'];
		$info['name'] = str_replace('\'', '', $info['name']);
		$info['unionid'] = isset($unionid) && !empty($unionid) ? $unionid : '';

		if (!$info) {
			ecs_header('Location: ' . $Loaction . "\n");
		}

		if (!$info['user_id']) {
			ecs_header('Location: ' . $Loaction . "\n");
		}

		$count = get_users_auth($info['info_user_id']);
		$connect_user = get_connect_user($info['user_id']);
		$is_pc = 0;

		if (!$count) {
			if ($connect_user) {
				if ($unionid && $type == 'qq' && $unionid == $connect_user['open_id']) {
					$count['aite_id'] = $unionid;
				}
				else {
					$count['aite_id'] = $type . '_' . $connect_user['open_id'];
				}

				$profile = unserialize($connect_user['profile']);
				$count['user_name'] = $profile['nickname'];
				$count['user_id'] = $connect_user['user_id'];
				$is_pc = 1;
			}
		}

		if ($unionid && $type == 'qq' && $count['identifier'] != $unionid) {
			$info['user_id'] = $unionid;
			$info['info_user_id'] = $unionid;
		}

		if ($count) {
			if ($is_pc == 1) {
				$other['user_id'] = $connect_user['user_id'];
				$other['user_name'] = $count['user_name'];
				$other['identity_type'] = $type;
				$other['identifier'] = $count['aite_id'];
				$other['add_time'] = gmtime();
				$db->autoExecute($ecs->table('users_auth'), $other, 'INSERT');
			}

			$profile = array('openid' => $info['user_id'], 'nickname' => $count['user_name'], 'user_id' => $count['user_id']);

			if ($type == 'qq') {
				$profile['unionid'] = $unionid;
			}

			$profile = serialize($profile);

			if (!$connect_user) {
				if ($type == 'qq' && $count['aite_id'] == $unionid) {
					$info['user_id'] = $unionid;
				}

				$other['connect_code'] = 'sns_' . $type;
				$other['user_id'] = $count['user_id'];
				$other['open_id'] = $info['user_id'];
				$other['profile'] = $profile;
				$other['create_at'] = gmtime();
				$db->autoExecute($ecs->table('connect_user'), $other, 'INSERT');
			}

			if (str_replace($type . '_', '', $count['aite_id']) != $info['user_id']) {
				if ($type == 'qq' && $unionid) {
					$info['info_user_id'] = $unionid;
					$info['user_id'] = $unionid;
				}

				$sql = 'UPDATE ' . $ecs->table('users_auth') . ' SET identifier = \'' . $info['info_user_id'] . ('\' WHERE identifier = \'' . $count['aite_id'] . '\'');
				$db->query($sql);
				$open_id = !empty($count['aite_id']) ? str_replace($type . '_', '', $count['aite_id']) : '';
				$sql = 'UPDATE ' . $ecs->table('connect_user') . ' SET open_id = \'' . $info['user_id'] . ('\', profile=\'' . $profile . '\' WHERE open_id = \'' . $open_id . '\'');
				$db->query($sql);
			}

			if ($info['name'] != $count['user_name']) {
				if ($user->check_user($info['name'])) {
					$info['name'] = $info['name'] . '_' . $type . rand() * 1000;
				}

				$sql = 'UPDATE ' . $ecs->table('users_auth') . (' SET user_name = \'' . $info['name'] . '\' WHERE identifier = \'') . $info['info_user_id'] . '\'';
				$db->query($sql);
			}

			$user->set_session($info['name'], 0, $info['user_id']);
			$user->set_cookie($info['name'], 0, 1, $info['user_id']);
			update_user_info();
			recalculate_price();
			$user_callblock = isset($_SESSION['user_callblock']) && !empty($_SESSION['user_callblock']) ? $_SESSION['user_callblock'] : '';
			unset($_SESSION['user_callblock']);
			$user_callblock = !empty($user_callblock) ? str_replace('|', '&', $user_callblock) : '';
			if (strpos($user_callblock, 'http://') === false && strpos($user_callblock, 'https://') === false) {
				ecs_header('Location: ' . $ecs->url() . $user_callblock);
			}
			else {
				ecs_header('Location: ' . $user_callblock);
			}
		}
	}

	$smarty->assign('login_ret', $login_ret);
	$smarty->assign('type', $type);
	$smarty->assign('info', $info);
	$smarty->assign('access', $access);
	$info['type'] = $type;
	$info['access_token'] = $access['access_token'];
	$oath_info = serialize($info);
	$_SESSION[$type . 'oath'] = $oath_info;
	$users_auth = get_users_auth($info['info_user_id']);

	if (!$users_auth) {
		$users_auth = get_connect_user($info['user_id']);
	}

	if ($users_auth) {
		$user_id = $users_auth['user_id'];
		$jump = 'old_user';
	}

	if ($_CFG['sms_signin']) {
		$sms_security_code = $_SESSION['sms_security_code'] = rand(1000, 9999);
		$smarty->assign('sms_security_code', $sms_security_code);
		$smarty->assign('enabled_sms_signin', 1);
	}

	$smarty->assign('sms_register', $_CFG['sms_signin']);
	$captcha = intval($_CFG['captcha']);
	if ($captcha & CAPTCHA_LOGIN && (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha & CAPTCHA_LOGIN_FAIL && 2 < $_SESSION['login_fail']) && 0 < gd_version()) {
		$smarty->assign('login_captcha', 1);
		$smarty->assign('rand', mt_rand());
	}

	if (intval($_CFG['captcha']) & CAPTCHA_REGISTER && 0 < gd_version()) {
		$smarty->assign('register_captcha', 1);
		$smarty->assign('rand', mt_rand());
	}

	if (!empty($user_id)) {
		$Loaction = 'user.php?act=oath_register&bind_type=1' . '&info_user_id=' . $info['info_user_id'] . '&user_id=' . $info['user_id'] . '&unionid=' . $unionid . '&name=' . $info['name'] . '&sex=' . $info['sex'] . '&rank_id=' . $info['rank_id'] . '&img=' . $info['img'] . '&token=' . $access['access_token'] . '&type=' . $type . '&sess_user=' . $user_id . '&jump=' . $jump;
		ecs_header('Location: ' . $Loaction . "\n");
	}

	$smarty->display('user_bind.dwt');
}
else if ($action == 'oath_register') {
	$bind_type = isset($_REQUEST['bind_type']) ? intval($_REQUEST['bind_type']) : 1;
	$username = isset($_REQUEST['username']) ? compile_str($_REQUEST['username']) : '';
	$password = isset($_REQUEST['password']) ? compile_str($_REQUEST['password']) : '';
	$mobile_phone = isset($_REQUEST['mobile_phone']) ? trim($_REQUEST['mobile_phone']) : '';
	$captcha_value = isset($_REQUEST['captcha']) ? trim($_REQUEST['captcha']) : '';
	$type = !isset($_REQUEST['type']) ? $oath_info['type'] : $_REQUEST['type'];
	$_SESSION[$type . 'oath'] = isset($_SESSION[$type . 'oath']) && !empty($_SESSION[$type . 'oath']) ? $_SESSION[$type . 'oath'] : $oath_info;
	$oath_info = unserialize($_SESSION[$type . 'oath']);
	$unionid = !empty($oath_info['unionid']) ? $oath_info['unionid'] : '';

	if ($type != 'wechat') {
		$info_user_id = !isset($_REQUEST['info_user_id']) ? $oath_info['info_user_id'] : $_REQUEST['info_user_id'];
		$user_id = !isset($_REQUEST['user_id']) ? $oath_info['user_id'] : $_REQUEST['user_id'];
		$name = !isset($_REQUEST['name']) ? $oath_info['name'] : $_REQUEST['name'];
		$sex = !isset($_REQUEST['sex']) ? $oath_info['sex'] : $_REQUEST['sex'];
		$rank_id = !isset($_REQUEST['rank_id']) ? $oath_info['rank_id'] : $_REQUEST['rank_id'];
		$img = isset($oath_info['figureurl_qq_2']) && !empty($oath_info['figureurl_qq_2']) ? $oath_info['figureurl_qq_2'] : $oath_info['img'];
		$token = !isset($_REQUEST['token']) ? $oath_info['access_token'] : $_REQUEST['token'];
	}
	else {
		$token = $info['openid'];
		$name = isset($oath_info['nickname']) ? $oath_info['nickname'] : '';

		if (!empty($name)) {
			$nickname = explode('@', $name);

			if (1 < count($nickname)) {
				$name = str_replace('@', '#', $name);
			}
		}

		$info_user_id = $type . '_' . $oath_info['unionid'];
		$img = $info['headimgurl'];
	}

	$sess_user = isset($_REQUEST['sess_user']) ? trim($_REQUEST['sess_user']) : 0;

	if (empty($sess_user)) {
		$type_captcha = 0;

		if ($bind_type == 1) {
			$seKey = 'captcha_login';
			$captcha = intval($_CFG['captcha']);
			if ($captcha & CAPTCHA_LOGIN && (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha & CAPTCHA_LOGIN_FAIL && 2 < $_SESSION['login_fail']) && 0 < gd_version()) {
				$type_captcha = 1;
			}
		}
		else if ($bind_type == 2) {
			$seKey = 'mobile_phone';
			if (intval($_CFG['captcha']) & CAPTCHA_REGISTER && 0 < gd_version()) {
				$type_captcha = 1;
			}
		}

		if ($type_captcha) {
			$verify = new Verify();
			$captcha_code = $verify->check($captcha_value, $seKey);

			if (!$captcha_code) {
				if ($type == 'wechat') {
					$Loaction = 'wechat_oauth.php?act=login';
				}
				else {
					$Loaction = 'user.php?act=oath&type=' . $type;
				}

				$result['message'] = $_LANG['invalid_captcha'];
				show_message($_LANG['invalid_captcha'], $_LANG['relogin_lnk'], $Loaction, 'error');
				exit();
			}
		}
	}

	$other = array('identity_type' => $type, 'credential' => $token, 'verified' => 1);
	$openid = str_replace($type . '_', '', $info_user_id);

	if ($bind_type == 1) {
		if (!empty($sess_user)) {
			$other['user_id'] = $sess_user;
			$other['user_name'] = $name;
			$other['identifier'] = $info_user_id;
			$other['add_time'] = gmtime();
			$users_auth_count = get_users_auth($info_user_id);

			if (!$users_auth_count) {
				$db->autoExecute($ecs->table('users_auth'), $other, 'INSERT');
			}

			$connect_user = get_connect_user($openid);

			if (!$connect_user) {
				$connect_serialize = array('openid' => $openid, 'nickname' => $name, 'user_id' => $sess_user, 'unionid' => $unionid);
				$connect_other = array('connect_code' => 'sns_' . $type, 'user_id' => $sess_user, 'open_id' => $openid, 'profile' => serialize($connect_serialize), 'create_at' => gmtime());
				$connect_user_count = get_connect_user($openid);

				if (!$connect_user_count) {
					$db->autoExecute($ecs->table('connect_user'), $connect_other, 'INSERT');
				}
			}

			$sql = 'UPDATE ' . $ecs->table('users') . (' SET nick_name = \'' . $name . '\', old_user_picture = user_picture, aite_id = \'' . $info_user_id . '\', user_picture = \'' . $img . '\' WHERE user_id = \'' . $sess_user . '\'');
			$db->query($sql);
		}
		else if ($user->login($username, $password, NULL, 0)) {
			$is_email = get_is_email($username);
			$is_phone = get_is_phone($username);

			if ($is_email) {
				$field_name = 'email = \'' . $username . '\'';
			}
			else if ($is_phone) {
				$field_name = 'mobile_phone = \'' . $username . '\'';
			}
			else {
				$field_name = 'user_name = \'' . $username . '\'';
			}

			$sql = 'SELECT user_id, user_name FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE ' . $field_name . ' LIMIT 1');
			$user_info = $db->getRow($sql);
			$other['user_id'] = $user_info['user_id'];
			$other['user_name'] = $name;
			$other['identifier'] = $info_user_id;
			$other['add_time'] = gmtime();
			$users_auth_count = get_users_auth($info_user_id);

			if (!$users_auth_count) {
				$db->autoExecute($ecs->table('users_auth'), $other, 'INSERT');
			}
			else {
				$db->autoExecute($ecs->table('users_auth'), $other, 'UPDATE', 'identifier = \'' . $info_user_id . '\'');
			}

			$sql = 'UPDATE ' . $ecs->table('users') . (' SET nick_name = \'' . $name . '\', old_user_picture = user_picture, aite_id = \'' . $info_user_id . '\', user_picture = \'' . $img . '\' WHERE ' . $field_name);
			$db->query($sql);
			$connect_serialize = array('openid' => $openid, 'nickname' => $name, 'user_id' => $user_info['user_id'], 'unionid' => $unionid);
			$connect_other = array('connect_code' => 'sns_' . $type, 'user_id' => $user_info['user_id'], 'open_id' => $openid, 'profile' => serialize($connect_serialize), 'create_at' => gmtime());
			$connect_user_count = get_connect_user($openid);

			if (!$connect_user_count) {
				$db->autoExecute($ecs->table('connect_user'), $connect_other, 'INSERT');
			}
			else {
				$db->autoExecute($ecs->table('connect_user'), $connect_other, 'UPDATE', 'open_id = \'' . $openid . '\'');
			}
		}
	}
	else if ($bind_type == 2) {
		if ($user->check_user($username)) {
			$username = $username . '_' . $type . rand(10000, 99999);
		}

		$user_pass = $user->compile_password(array('password' => $password));
		$user_other = array('user_name' => $username, 'password' => $user_pass, 'aite_id' => $info_user_id, 'nick_name' => $name, 'sex' => $sex, 'mobile_phone' => $mobile_phone, 'reg_time' => gmtime(), 'user_rank' => $rank_id, 'user_picture' => $img, 'is_validated' => 1);
		$res = $db->autoExecute($ecs->table('users'), $user_other, 'INSERT');
		$other['user_id'] = $db->insert_id();
		$other['user_name'] = $name;
		$other['identifier'] = $info_user_id;
		$other['add_time'] = gmtime();

		if ($res) {
			$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
			if (isset($affiliate['on']) && $affiliate['on'] == 1) {
				$up_uid = get_affiliate();
				empty($affiliate) && ($affiliate = array());
				$affiliate['config']['level_register_all'] = intval($affiliate['config']['level_register_all']);
				$affiliate['config']['level_register_up'] = intval($affiliate['config']['level_register_up']);

				if ($up_uid) {
					if (!empty($affiliate['config']['level_register_all'])) {
						if (!empty($affiliate['config']['level_register_up'])) {
							$rank_points = $GLOBALS['db']->getOne('SELECT rank_points FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $up_uid . '\''));

							if ($rank_points + $affiliate['config']['level_register_all'] <= $affiliate['config']['level_register_up']) {
								log_account_change($up_uid, 0, 0, $affiliate['config']['level_register_all'], 0, sprintf($GLOBALS['_LANG']['register_affiliate'], $_SESSION['user_id'], $username));
							}
						}
						else {
							log_account_change($up_uid, 0, 0, $affiliate['config']['level_register_all'], 0, $GLOBALS['_LANG']['register_affiliate']);
						}
					}

					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET parent_id = ' . $up_uid . ' WHERE user_id = ' . $other['user_id'];
					$GLOBALS['db']->query($sql);
				}
			}
		}

		uc_call('uc_user_register', array($username, $user_pass));
		$users_auth_count = get_users_auth($info_user_id);

		if (!$users_auth_count) {
			$db->autoExecute($ecs->table('users_auth'), $other, 'INSERT');
		}

		$connect_serialize = array('openid' => $openid, 'nickname' => $name, 'user_id' => $other['user_id'], 'unionid' => $unionid);
		$connect_other = array('connect_code' => 'sns_' . $type, 'user_id' => $other['user_id'], 'open_id' => $openid, 'profile' => serialize($connect_serialize), 'create_at' => gmtime());
		$connect_user_count = get_connect_user($openid);

		if (!$connect_user_count) {
			$db->autoExecute($ecs->table('connect_user'), $connect_other, 'INSERT');
		}
	}

	if (empty($sess_user) || $_REQUEST['jump'] == 'old_user') {
		$user->set_session($name, 0, $openid);
		$user->set_cookie($name, 0, 1, $openid);
		update_user_info();
		recalculate_price();

		if (file_exists(MOBILE_WECHAT)) {
			$sql = 'UPDATE ' . $ecs->table('wechat_user') . ' SET ect_uid = \'' . $other['user_id'] . '\' WHERE unionid = \'' . $oath_info['unionid'] . '\' AND ect_uid = 0';
			$db->query($sql);
		}

		$user_callblock = isset($_SESSION['user_callblock']) && !empty($_SESSION['user_callblock']) ? $_SESSION['user_callblock'] : '';
		unset($_SESSION['user_callblock']);
		$user_callblock = !empty($user_callblock) ? str_replace('|', '&', $user_callblock) : '';
		if (strpos($user_callblock, 'http://') === false && strpos($user_callblock, 'https://') === false) {
			ecs_header('Location: ' . $ecs->url() . $user_callblock);
		}
		else {
			ecs_header('Location: ' . $user_callblock);
		}
	}
	else {
		ecs_header('Location: user.php?act=' . $_REQUEST['jump']);
	}
}
else if ($action == 'oath_weixin_login') {
	assign_template();
	$position = assign_ur_here(0, $lANG['bind_login']);
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('helps', get_shop_help());
	$smarty->assign('data_dir', DATA_DIR);
	$user_callblock = isset($_GET['user_callblock']) && !empty($_GET['user_callblock']) ? urldecode(trim($_GET['user_callblock'])) : '';
	$user_callblock = !empty($user_callblock) ? dsc_addslashes($user_callblock) : '';
	$_SESSION['wechatoath'] = isset($_SESSION['wechatoath']) && !empty($_SESSION['wechatoath']) ? $_SESSION['wechatoath'] : '';
	$info = unserialize($_SESSION['wechatoath']);
	$info['nickname'] = addslashes($info['nickname']);
	$info['img'] = $info['headimgurl'];
	$unionid = $info['type'] . '_' . $info['unionid'];
	$users_auth = get_users_auth($unionid);
	$connect_user = get_connect_user($info['unionid']);

	if (!$users_auth) {
		$app_type = 'sns_' . $info['type'];

		if ($connect_user) {
			$users_auth['aite_id'] = $info['type'] . $connect_user['open_id'];
			$users_auth['user_id'] = $connect_user['user_id'];
			$profile = unserialize($connect_user['profile']);
			$users_auth['user_name'] = $profile['nickname'];
		}
	}

	$user_id = !isset($_GET['user_id']) ? $info['login_user'] : intval($_GET['user_id']);
	$nickname = explode('@', $info['nickname']);

	if (1 < count($nickname)) {
		$info['nickname'] = str_replace('@', '#', $info['nickname']);
	}

	if ($users_auth) {
		$user_id = $users_auth['user_id'];
	}

	if ($user_id) {
		$pc_users_auth = get_users_auth($unionid);

		if (!$pc_users_auth) {
			$other = array('identity_type' => $info['type'], 'credential' => $info['openid'], 'verified' => 1);
			$other['user_id'] = $user_id;
			$other['user_name'] = $info['nickname'];
			$other['identifier'] = $unionid;
			$other['add_time'] = gmtime();
			$db->autoExecute($ecs->table('users_auth'), $other, 'INSERT');
			$users_auth = array('aite_id' => $other['identifier'], 'user_name' => $user_id);
		}

		if (!$connect_user) {
			$connect_serialize = array('openid' => $info['unionid'], 'nickname' => $info['nickname'], 'user_id' => $user_id);
			$connect_other = array('connect_code' => 'sns_' . $info['type'], 'user_id' => $user_id, 'open_id' => $info['unionid'], 'profile' => serialize($connect_serialize), 'create_at' => gmtime());
			$db->autoExecute($ecs->table('connect_user'), $connect_other, 'INSERT');
		}

		if ($users_auth['aite_id'] == $unionid) {
			$sql = 'UPDATE ' . $ecs->table('users_auth') . (' SET identifier = \'' . $unionid . '\' WHERE identifier = \'' . $users_auth['aite_id'] . '\'');
			$db->query($sql);
			$sql = 'UPDATE ' . $ecs->table('users') . ' SET old_user_picture = user_picture, user_picture = \'' . $info['img'] . ('\' WHERE aite_id = \'' . $users_auth['aite_id'] . '\'');
			$db->query($sql);
		}

		if ($info['nickname'] != $users_auth['user_name']) {
			if ($user->check_user($info['nickname'])) {
				$info['nickname'] = $info['nickname'] . '_' . $type . rand() * 1000;
			}

			$sql = 'UPDATE ' . $ecs->table('users_auth') . (' SET user_name = \'' . $info['nickname'] . '\' WHERE identifier = \'' . $unionid . '\'');
			$db->query($sql);
		}

		if (file_exists(MOBILE_WECHAT)) {
			$sql = 'UPDATE ' . $ecs->table('wechat_user') . (' SET ect_uid = \'' . $user_id . '\' WHERE unionid = \'') . $info['unionid'] . '\' AND ect_uid = 0';
			$db->query($sql);
		}

		$user->set_session($info['nickname'], 0, $info['unionid']);
		$user->set_cookie($info['nickname'], 0, 1, $info['unionid']);
		update_user_info();
		recalculate_price();
		$user_callblock = isset($_SESSION['user_callblock']) && !empty($_SESSION['user_callblock']) ? trim($_SESSION['user_callblock']) : $user_callblock;
		unset($_SESSION['user_callblock']);
		$user_callblock = !empty($user_callblock) ? str_replace('|', '&', $user_callblock) : '';
		if (strpos($user_callblock, 'http://') === false && strpos($user_callblock, 'https://') === false) {
			ecs_header('Location: ' . $ecs->url() . $user_callblock);
		}
		else {
			ecs_header('Location: ' . $user_callblock);
		}
	}

	$info['name'] = $info['nickname'];
	$smarty->assign('info', $info);
	$smarty->assign('type', $info['type']);

	if ($_CFG['sms_signin']) {
		$sms_security_code = $_SESSION['sms_security_code'] = rand(1000, 9999);
		$smarty->assign('sms_security_code', $sms_security_code);
		$smarty->assign('enabled_sms_signin', 1);
	}

	$smarty->assign('sms_register', $_CFG['sms_signin']);
	$captcha = intval($_CFG['captcha']);
	if ($captcha & CAPTCHA_LOGIN && (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha & CAPTCHA_LOGIN_FAIL && 2 < $_SESSION['login_fail']) && 0 < gd_version()) {
		$smarty->assign('login_captcha', 1);
		$smarty->assign('rand', mt_rand());
	}

	if (intval($_CFG['captcha']) & CAPTCHA_REGISTER && 0 < gd_version()) {
		$smarty->assign('register_captcha', 1);
		$smarty->assign('rand', mt_rand());
	}

	$smarty->display('user_bind.dwt');
}
else if ($action == 'other_login') {
	$type = empty($_REQUEST['type']) ? '' : $_REQUEST['type'];
	session_start();
	$info = $_SESSION['user_info'];

	if (empty($info)) {
		show_message($_LANG['Access_timeout'], $_LANG['home'], $ecs->url(), 'error', false);
	}

	if (!$info['user_id']) {
		show_message($_LANG['Illegal_access'], $_LANG['home'], $ecs->url(), 'error', false);
	}

	$info_user_id = $type . '_' . $info['user_id'];
	$info['name'] = str_replace('\'', '', $info['name']);
	$sql = 'SELECT user_name,password,aite_id FROM ' . $ecs->table('users') . ' WHERE aite_id = \'' . $info_user_id . '\' OR aite_id=\'' . $info['user_id'] . '\'';
	$count = $db->getRow($sql);
	$login_name = $info['name'];

	if (!$count) {
		if ($user->check_user($info['name'])) {
			$info['name'] = $info['name'] . '_' . $type . rand() * 1000;
		}

		$login_name = $info['name'];
		$user_pass = $user->compile_password(array('password' => $info['user_id']));
		$sql = 'INSERT INTO ' . $ecs->table('users') . '(user_name , password, aite_id , sex , reg_time , user_rank , is_validated) VALUES ' . ('(\'' . $info['name'] . '\' , \'' . $user_pass . '\' , \'' . $info_user_id . '\' , \'' . $info['sex'] . '\' , \'') . gmtime() . ('\' , \'' . $info['rank_id'] . '\' , \'0\')');
		$db->query($sql);
		$ucdata = empty($user->ucdata) ? '' : $user->ucdata;
	}
	else {
		$login_name = $count['user_name'];
		$sql = '';

		if ($count['aite_id'] == $info['user_id']) {
			$sql = 'UPDATE ' . $ecs->table('users') . (' SET aite_id = \'' . $info_user_id . '\' WHERE aite_id = \'' . $count['aite_id'] . '\'');
			$db->query($sql);
		}
	}

	$user->set_session($login_name);
	$user->set_cookie($login_name);
	update_user_info();
	recalculate_price();
	$redirect_url = 'http://' . $_SERVER['HTTP_HOST'] . str_replace('user.php', 'index.php', $_SERVER['REQUEST_URI']);
	header('Location: ' . $redirect_url);
}
else if ($action == 'validate_email') {
	$hash = empty($_GET['hash']) ? '' : trim($_GET['hash']);

	if ($hash) {
		include_once ROOT_PATH . 'includes/lib_passport.php';
		$id = register_hash('decode', $hash);

		if (0 < $id) {
			$sql = 'UPDATE ' . $ecs->table('users') . (' SET is_validated = 1 WHERE user_id=\'' . $id . '\'');
			$db->query($sql);
			$sql = 'SELECT user_name, email FROM ' . $ecs->table('users') . (' WHERE user_id = \'' . $id . '\'');
			$row = $db->getRow($sql);
			show_message(sprintf($_LANG['validate_ok'], $row['user_name'], $row['email']), $_LANG['profile_lnk'], 'user.php?act=account_safe&type=change_email&step=last');
		}
	}

	show_message($_LANG['validate_fail']);
}
else if ($action == 'is_registered') {
	include_once ROOT_PATH . 'includes/lib_passport.php';
	include ROOT_PATH . 'includes/cls_json.php';
	$error = true;
	$username = trim($_REQUEST['username']);
	$username = json_str_iconv($username);
	$password = isset($_REQUEST['password']) ? json_str_iconv($_REQUEST['password']) : '';
	if ($user->check_user($username, $password) || admin_registered($username)) {
		$error = false;
	}
	else {
		$error = true;
	}

	exit(json_encode($error));
}
else if ($action == 'is_user') {
	include_once ROOT_PATH . 'includes/lib_passport.php';
	include ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$res = array('result' => '', 'error' => 0, 'mode' => 0);
	$username = trim($_GET['username']);
	$username = json_str_iconv($username);
	$password = isset($_GET['password']) ? json_str_iconv($_GET['password']) : '';

	if ($user->check_user($username, $password)) {
		$res['result'] = 'ok';
	}
	else {
		$res['result'] = 'false';
	}

	exit($json->encode($res));
}
else if ($action == 'is_login_captcha') {
	include_once ROOT_PATH . 'includes/lib_passport.php';
	include ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$res = array('result' => 'ok', 'error' => 0, 'mode' => 0);
	$captcha_str = addslashes(trim($_GET['captcha']));
	$captcha = intval($_CFG['captcha']);
	if ($captcha & CAPTCHA_LOGIN && (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha & CAPTCHA_LOGIN_FAIL && 2 < $_SESSION['login_fail']) && 0 < gd_version()) {
		$verify = new Verify();
		$captcha_code = $verify->check($captcha_str, 'captcha_login');

		if (!$captcha_code) {
			$res['result'] = 'false';
			$res['message'] = $_LANG['invalid_captcha'];
		}
	}

	exit($json->encode($res));
}
else if ($action == 'is_register_captcha') {
	include_once ROOT_PATH . 'includes/lib_passport.php';
	include ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$res = array('result' => '', 'error' => 0, 'mode' => 0);
	$captcha = trim($_GET['captcha']);
	if (intval($_CFG['captcha']) & CAPTCHA_REGISTER && 0 < gd_version()) {
		$seKey = 'mobile_phone';
		$verify = new Verify();
		$captcha_code = $verify->check($captcha, $seKey, '', 'ajax');

		if (!$captcha_code) {
			$res['result'] = 'false';
			$res['message'] = $_LANG['invalid_captcha'];
		}
		else {
			$res['result'] = 'ok';
		}
	}

	exit($json->encode($res));
}
else if ($action == 'is_mobile_code') {
	include_once ROOT_PATH . 'includes/lib_passport.php';
	include ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$res = array('result' => '', 'error' => 0, 'mode' => 0);
	$mobile_code = trim($_GET['mobile_code']);

	if ($mobile_code != $_SESSION['sms_mobile_code']) {
		$res['result'] = 'false';
	}
	else {
		$res['result'] = 'ok';
	}

	exit($json->encode($res));
}
else if ($action == 'is_mobile_phone') {
	include_once ROOT_PATH . 'includes/lib_passport.php';
	$phone = trim($_GET['phone']);
	$phone = json_str_iconv($phone);

	if ($user->check_mobile_phone($phone)) {
		echo 'false';
	}
	else {
		echo 'true';
	}
}
else if ($action == 'check_email') {
	include_once ROOT_PATH . 'includes/lib_passport.php';
	$email = trim($_POST['email']);
	$error = true;

	if ($user->check_email($email)) {
		$error = false;
	}
	else {
		$error = true;
	}

	exit(json_encode($error));
}
else if ($action == 'check_phone') {
	include_once ROOT_PATH . 'includes/lib_passport.php';
	$mobile_phone = trim($_REQUEST['mobile_phone']);

	if ($user->check_mobile_phone($mobile_phone)) {
		$error = false;
	}
	else {
		$error = true;
	}

	exit(json_encode($error));
}
else if ($action == 'code_notice') {
	include_once ROOT_PATH . 'includes/lib_passport.php';
	$error = true;
	$mobile_code = isset($_REQUEST['mobile_code']) ? trim($_REQUEST['mobile_code']) : '';
	$sms_security_code = isset($_SESSION['sms_mobile_code']) ? trim($_SESSION['sms_mobile_code']) : '';

	if (!empty($_REQUEST['mobile_code'])) {
		if ($_REQUEST['mobile_code'] != $_SESSION['sms_mobile_code']) {
			$error = false;
		}
	}

	exit(json_encode($error));
}
else if ($action == 'captchas') {
	$captcha = isset($_REQUEST['captcha']) ? trim($_REQUEST['captcha']) : '';
	if (intval($_CFG['captcha']) && 0 < gd_version()) {
		if (empty($captcha)) {
			echo 1;
		}
		else {
			$seKey = 'register_email';
			$verify = new Verify();
			$captcha_code = $verify->check($captcha, $seKey, '', 'ajax');

			if (!$captcha_code) {
				echo 2;
			}
			else {
				echo 3;
			}
		}
	}
}
else if ($action == 'phone_captcha') {
	$captcha = isset($_REQUEST['captcha']) ? trim($_REQUEST['captcha']) : '';
	include_once ROOT_PATH . 'includes/lib_passport.php';
	$error = true;
	if (intval($_CFG['captcha']) && 0 < gd_version()) {
		$seKey = 'mobile_phone';
		$verify = new Verify();
		$captcha_code = $verify->check($captcha, $seKey, '', 'ajax');

		if (!$captcha_code) {
			$error = false;
		}
		else {
			$error = true;
		}
	}

	exit(json_encode($error));
}
else if ($action == 'pay_pwd') {
	include 'includes/cls_json.php';
	$json = new JSON();
	$res = array('error' => 0, 'err_msg' => '', 'content' => '');
	$_POST = get_request_filter($_POST, 1);
	$pay_pwd = isset($_POST['pay_pwd']) && !empty($_POST['pay_pwd']) ? addslashes(trim($_POST['pay_pwd'])) : '';
	$sql = 'SELECT pay_online, ec_salt, pay_password FROM ' . $ecs->table('users_paypwd') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\' LIMIT 1';
	$pay = $db->getRow($sql);
	$ec_salt = $pay['ec_salt'];
	$new_password = md5(md5($pay_pwd) . $ec_salt);

	if (empty($pay_pwd)) {
		$res['error'] = 1;
	}
	else if ($new_password != $pay['pay_password']) {
		$res['error'] = 2;
	}

	exit($json->encode($res));
}
else if ($action == 'login') {
	$dsc_token = get_dsc_token();
	$smarty->assign('dsc_token', $dsc_token);

	if (empty($back_act)) {
		if (empty($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER'])) {
			$back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
		}
		else {
			$back_act = 'user.php';
		}
	}

	$captcha = intval($_CFG['captcha']);
	if ($captcha & CAPTCHA_LOGIN && (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha & CAPTCHA_LOGIN_FAIL && 2 < $_SESSION['login_fail']) && 0 < gd_version()) {
		$GLOBALS['smarty']->assign('enabled_captcha', 1);
		$GLOBALS['smarty']->assign('rand', mt_rand());
	}

	$login_banner = '';

	for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
		$login_banner .= '\'login_banner' . $i . ',';
	}

	$smarty->assign('login_banner', $login_banner);
	$website_dir = ROOT_PATH . 'includes/website/config/';
	$website_list = get_dir_file_list($website_dir, 1, '_');

	for ($i = 0; $i < count($website_list); $i++) {
		if ($website_list[$i]['file'] == 'index.htm' || $website_list[$i]['file'] == 'index.html') {
			unset($website_list[$i]);
		}
	}

	$count = !empty($website_list) ? count($website_list) : 0;

	if (file_exists(ROOT_PATH . 'wechat_oauth.php')) {
		$website_list[$count]['web_type'] = 'weixin';
	}

	$smarty->assign('website_list', $website_list);
	$smarty->assign('back_act', urlencode($back_act));
	$smarty->display('user_passport.dwt');
}
else if ($action == 'captchas_pass') {
	$captcha = isset($_REQUEST['captcha']) ? trim($_REQUEST['captcha']) : '';
	$error = true;
	if (intval($_CFG['captcha']) && 0 < gd_version()) {
		if (!empty($captcha)) {
			$seKey = !empty($_GET['seKey']) ? $_GET['seKey'] : '';
			$verify = new Verify();
			$captcha_code = $verify->check($captcha, $seKey, '', 'ajax');

			if (!$captcha_code) {
				$error = false;
			}
			else {
				$error = true;
			}
		}
	}

	exit(json_encode($error));
}
else if ($action == 'act_login') {
	include_once 'includes/cls_json.php';
	$_POST = get_request_filter($_POST, 1);
	$is_jsonp = INPUT_I('post.is_jsonp', 0, 'intval');
	$username = INPUT_I('post.username', '');
	$password = INPUT_I('post.password', '');
	$back_act = INPUT_I('post.back_act', '');
	$back_act = str_replace(array('|', '&amp;'), '&', $back_act);
	$username = !empty($username) ? $username : dsc_addslashes($username);
	$result = array('error' => 0, 'message' => '', 'url' => '');
	$json = new JSON();
	$captcha = intval($_CFG['captcha']);
	if ($captcha & CAPTCHA_LOGIN && 0 < gd_version()) {
		if (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha & CAPTCHA_LOGIN_FAIL && 2 < $_SESSION['login_fail']) {
			if (empty($_POST['captcha'])) {
				$result['error'] = 1;
				$result['message'] = $_LANG['invalid_captcha'];
			}

			$captcha_str = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';
			$verify = new Verify();
			$captcha_code = $verify->check($captcha_str, 'captcha_login');

			if (!$captcha_code) {
				$result['error'] = 1;
				$result['message'] = $_LANG['invalid_captcha'];
			}
		}

		if (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha & CAPTCHA_LOGIN_FAIL && 2 <= $_SESSION['login_fail']) {
			$GLOBALS['smarty']->assign('enabled_captcha', 1);
			$GLOBALS['smarty']->assign('rand', mt_rand());
			$result['captcha'] = $smarty->fetch('library/captcha.lbi');
		}
	}

	if ($result['error'] == 0) {
		if ($user->login($username, $password, isset($_POST['remember']))) {
			update_user_info();
			recalculate_price();
			$sql = 'SELECT nick_name, is_validated FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $_SESSION['user_id'] . '\' LIMIT 1';
			$info = $db->getRow($sql);

			if (empty($info['nick_name'])) {
				$nick_name = rand(1, 99999999) . '-' . rand(1, 999999);
				$update_data['nick_name'] = $nick_name;
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('users'), $update_data, 'UPDATE', 'user_id = ' . $_SESSION['user_id']);
			}

			$ucdata = isset($user->ucdata) ? $user->ucdata : '';
			$back_act = !empty($back_act) ? $back_act : 'index.php';
			$result['url'] = $back_act;
			$result['ucdata'] = $ucdata;

			if ($_CFG['user_login_register'] == 1) {
				$result['is_validated'] = $info['is_validated'];
			}
			else {
				$result['is_validated'] = 1;
			}

			users_log_change($_SESSION['user_id'], USER_LOGIN);
		}
		else {
			$_SESSION['login_fail']++;
			$result['error'] = 1;
			$result['message'] = $_LANG['login_failure'];
		}
	}

	if ($is_jsonp) {
		echo $_REQUEST['jsoncallback'] . '(' . $json->encode($result) . ')';
	}
	else {
		exit($json->encode($result));
	}
}
else if ($action == 'signin') {
	include_once 'includes/cls_json.php';
	$json = new JSON();
	$_POST = get_request_filter($_POST, 1);
	$username = !empty($_POST['username']) ? json_str_iconv(trim($_POST['username'])) : '';
	$password = !empty($_POST['password']) ? trim($_POST['password']) : '';
	$captcha = !empty($_POST['captcha']) ? json_str_iconv(trim($_POST['captcha'])) : '';
	$result = array('error' => 0, 'content' => '');
	$captcha = intval($_CFG['captcha']);
	if ($captcha & CAPTCHA_LOGIN && (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha & CAPTCHA_LOGIN_FAIL && 2 < $_SESSION['login_fail']) && 0 < gd_version()) {
		if (empty($captcha)) {
			$result['error'] = 1;
			$result['content'] = $_LANG['invalid_captcha'];
			exit($json->encode($result));
		}

		include_once 'includes/cls_captcha.php';
		$validator = new captcha();
		$validator->session_word = 'captcha_login';

		if (!$validator->check_word($_POST['captcha'])) {
			$result['error'] = 1;
			$result['content'] = $_LANG['invalid_captcha'];
			exit($json->encode($result));
		}
	}

	if ($user->login($username, $password)) {
		update_user_info();
		recalculate_price();
		$smarty->assign('user_info', get_user_info());
		$ucdata = empty($user->ucdata) ? '' : $user->ucdata;
		$result['ucdata'] = $ucdata;
		$result['content'] = $smarty->fetch('library/member_info.lbi');
	}
	else {
		$_SESSION['login_fail']++;

		if (2 < $_SESSION['login_fail']) {
			$smarty->assign('enabled_captcha', 1);
			$result['html'] = $smarty->fetch('library/member_info.lbi');
		}

		$result['error'] = 1;
		$result['content'] = $_LANG['login_failure'];
	}

	exit($json->encode($result));
}
else if ($action == 'logout') {
	if ((!isset($back_act) || empty($back_act)) && isset($GLOBALS['_SERVER']['HTTP_REFERER'])) {
		$back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
	}

	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('sessions') . ' WHERE userid = \'' . $_SESSION['user_id'] . '\' AND adminid = 0';
	$GLOBALS['db']->query($sql);
	$user->logout();
	$ucdata = empty($user->ucdata) ? '' : $user->ucdata;
	header('Location:user.php?act=login');
}
else if ($action == 'profile') {
	include_once ROOT_PATH . 'includes/lib_transaction.php';

	if (0 < intval($_CFG['sms_signin'])) {
		$sms_security_code = $_SESSION['sms_security_code'] = rand(1000, 9999);
		$smarty->assign('sms_security_code', $sms_security_code);
		$smarty->assign('enabled_sms_signin', 1);
	}

	$user_info = get_profile($user_id);
	$sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
	$extend_info_list = $db->getAll($sql);
	$sql = 'SELECT reg_field_id, content ' . 'FROM ' . $ecs->table('reg_extend_info') . (' WHERE user_id = ' . $user_id);
	$extend_info_arr = $db->getAll($sql);
	$temp_arr = array();

	foreach ($extend_info_arr as $val) {
		$temp_arr[$val['reg_field_id']] = $val['content'];
	}

	foreach ($extend_info_list as $key => $val) {
		switch ($val['id']) {
		case 1:
			$extend_info_list[$key]['content'] = $user_info['msn'];
			break;

		case 2:
			$extend_info_list[$key]['content'] = $user_info['qq'];
			break;

		case 3:
			$extend_info_list[$key]['content'] = $user_info['office_phone'];
			break;

		case 4:
			$extend_info_list[$key]['content'] = $user_info['home_phone'];
			break;

		case 5:
			$extend_info_list[$key]['content'] = $user_info['mobile_phone'];
			break;

		default:
			$extend_info_list[$key]['content'] = empty($temp_arr[$val['id']]) ? '' : $temp_arr[$val['id']];
		}
	}

	if (defined('THEME_EXTENSION')) {
		$select_date = array();
		$select_date['year'] = range(date('Y', strtotime('-60 year')), date('Y', strtotime('+1 year')));
		$select_date['month'] = range(1, 12);
		$select_date['day'] = range(1, 31);
		$smarty->assign('select_date', $select_date);

		if ($user_info['birthday']) {
			$birthday = explode('-', $user_info['birthday']);
			$user_info['year'] = intval($birthday[0]);
			$user_info['month'] = intval($birthday[1]);
			$user_info['day'] = intval($birthday[2]);
		}
	}

	$user_info['passwd_question'] = $_LANG['passwd_questions'][$user_info['passwd_question']];
	$smarty->assign('extend_info_list', $extend_info_list);
	$smarty->assign('passwd_questions', $_LANG['passwd_questions']);
	$smarty->assign('sms_register', $_CFG['sms_signin']);
	$smarty->assign('profile', $user_info);
	$smarty->display('user_transaction.dwt');
}
else if ($action == 'user_picture') {
	$create = create_password();
	$img_sir = 'data/images_user/' . $_SESSION['user_id'];

	if (file_exists($img_sir . '_120.jpg')) {
		$img_sir = $img_sir . '_120.jpg';
	}
	else {
		$img_sir = 'data/images_user/0_120.jpg';
	}

	$smarty->assign('create', $create);
	$smarty->assign('img_sir', $img_sir);
	$smarty->assign('user_id', $_SESSION['user_id']);
	$smarty->display('user_transaction.dwt');
}
else if ($action == 'act_edit_profile') {
	include_once ROOT_PATH . 'includes/lib_transaction.php';
	$_POST = get_request_filter($_POST, 1);
	$birthday = trim($_POST['birthdayYear']) . '-' . trim($_POST['birthdayMonth']) . '-' . trim($_POST['birthdayDay']);
	$email = isset($_POST['email']) && !empty($_POST['email']) ? trim($_POST['email']) : '';
	$other['msn'] = $msn = isset($_POST['extend_field1']) ? trim($_POST['extend_field1']) : '';
	$other['qq'] = $qq = isset($_POST['extend_field2']) ? trim($_POST['extend_field2']) : '';
	$other['office_phone'] = $office_phone = isset($_POST['extend_field3']) ? trim($_POST['extend_field3']) : '';
	$other['home_phone'] = $home_phone = isset($_POST['extend_field4']) ? trim($_POST['extend_field4']) : '';
	$mobile_phone = isset($_POST['mobile_phone']) ? trim($_POST['mobile_phone']) : '';
	$sel_question = empty($_POST['sel_question']) ? '' : compile_str($_POST['sel_question']);
	$passwd_answer = isset($_POST['passwd_answer']) ? compile_str(trim($_POST['passwd_answer'])) : '';
	$mobile_code = isset($_POST['mobile_code']) ? trim($_POST['mobile_code']) : '';
	$nick_name = empty($_POST['nick_name']) ? '' : compile_str($_POST['nick_name']);
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . (' SET nick_name = \'' . $nick_name . '\' WHERE user_id = \'' . $user_id . '\'');
	$GLOBALS['db']->query($sql);
	$sql = 'SELECT id FROM ' . $ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';
	$fields_arr = $db->getAll($sql);

	foreach ($fields_arr as $val) {
		$extend_field_index = 'extend_field' . $val['id'];

		if (isset($_POST[$extend_field_index])) {
			$temp_field_content = 100 < strlen($_POST[$extend_field_index]) ? mb_substr(htmlspecialchars($_POST[$extend_field_index]), 0, 99) : htmlspecialchars($_POST[$extend_field_index]);
			$sql = 'SELECT * FROM ' . $ecs->table('reg_extend_info') . ('  WHERE reg_field_id = \'' . $val['id'] . '\' AND user_id = \'' . $user_id . '\'');

			if ($db->getOne($sql)) {
				$sql = 'UPDATE ' . $ecs->table('reg_extend_info') . (' SET content = \'' . $temp_field_content . '\' WHERE reg_field_id = \'' . $val['id'] . '\' AND user_id = \'' . $user_id . '\'');
			}
			else {
				$sql = 'INSERT INTO ' . $ecs->table('reg_extend_info') . (' (`user_id`, `reg_field_id`, `content`) VALUES (\'' . $user_id . '\', \'' . $val['id'] . '\', \'' . $temp_field_content . '\')');
			}

			$db->query($sql);
		}
	}

	if (!empty($passwd_answer) && !empty($sel_question)) {
		$sql = 'UPDATE ' . $ecs->table('users') . (' SET `passwd_question`=\'' . $sel_question . '\', `passwd_answer`=\'' . $passwd_answer . '\'  WHERE `user_id`=\'') . $_SESSION['user_id'] . '\'';
		$db->query($sql);
	}

	if (!empty($msn) && !is_email($msn)) {
		show_message($_LANG['passport_js']['msn_invalid']);
	}

	if (!empty($qq) && !preg_match('/^\\d+$/', $qq)) {
		show_message($_LANG['passport_js']['qq_invalid']);
	}

	if (!empty($mobile_phone) && !preg_match('/^13[\\d]{9}$|^14[5,7]{1}\\d{8}$|^15[^4]{1}\\d{8}$|^17[0,6,7,8]{1}\\d{8}$|^18[\\d]{9}$/', $mobile_phone)) {
		show_message($_LANG['passport_js']['mobile_phone_invalid']);
	}

	$profile = array('user_id' => $user_id, 'email' => isset($_POST['email']) ? trim($_POST['email']) : '', 'mobile_phone' => $mobile_phone, 'mobile_code' => $mobile_code, 'sex' => isset($_POST['sex']) ? intval($_POST['sex']) : 0, 'birthday' => $birthday, 'other' => isset($other) ? $other : array());

	if (edit_profile($profile)) {
		users_log_change($_SESSION['user_id'], USER_INFO);
		show_message($_LANG['edit_profile_success'], $_LANG['profile_lnk'], 'user.php?act=profile', 'info');
	}
	else {
		if ($user->error == ERR_EMAIL_EXISTS) {
			$msg = sprintf($_LANG['email_exist'], $profile['email']);
		}
		else if ($user->error == ERR_PHONE_EXISTS) {
			$msg = sprintf($_LANG['phone_exist'], $profile['mobile_phone']);
		}
		else if ($err->error_no) {
			$msg = $_LANG['Mobile_code_error'];
		}
		else {
			$msg = $_LANG['edit_profile_failed'];
		}

		show_message($msg, '', '', 'info');
	}
}
else if ($action == 'get_password') {
	$smarty->assign('cfg', $_CFG);
	include_once ROOT_PATH . 'includes/lib_passport.php';
	if (intval($_CFG['captcha']) & CAPTCHA_REGISTER && 0 < gd_version()) {
		$smarty->assign('enabled_captcha', 1);
		$smarty->assign('rand', mt_rand());
	}

	if (isset($_GET['code']) && isset($_GET['uid'])) {
		$code = trim($_GET['code']);
		$uid = intval($_GET['uid']);
		$user_info = $user->get_profile_by_id($uid);
		if (empty($user_info) || $user_info && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) != $code) {
			show_message($_LANG['parm_error'], $_LANG['back_home_lnk'], './', 'info');
		}

		$smarty->assign('uid', $uid);
		$smarty->assign('code', $code);
		$smarty->assign('action', 'reset_password');
		$smarty->display('user_passport.dwt');
	}
	else {
		$sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
		$extend_info_list = $db->getAll($sql);
		$smarty->assign('extend_info_list', $extend_info_list);
		$smarty->assign('passwd_questions', $_LANG['passwd_questions']);
		$smarty->display('user_passport.dwt');
	}
}
else if ($action == 'qpassword_name') {
	$smarty->display('user_passport.dwt');
}
else if ($action == 'get_passwd_question') {
	$_POST = get_request_filter($_POST, 1);

	if (empty($_POST['user_name'])) {
		show_message($_LANG['no_passwd_question'], $_LANG['back_home_lnk'], './', 'info');
	}
	else {
		$user_name = trim($_POST['user_name']);
	}

	$sql = 'SELECT user_id, user_name, passwd_question, passwd_answer FROM ' . $ecs->table('users') . ' WHERE user_name = \'' . $user_name . '\'';
	$user_question_arr = $db->getRow($sql);

	if (empty($user_question_arr['passwd_answer'])) {
		show_message($_LANG['no_passwd_question'], $_LANG['back_home_lnk'], './', 'info');
	}

	$_SESSION['temp_user'] = $user_question_arr['user_id'];
	$_SESSION['temp_user_name'] = $user_question_arr['user_name'];
	$_SESSION['passwd_answer'] = $user_question_arr['passwd_answer'];
	$captcha = intval($_CFG['captcha']);
	if ($captcha & CAPTCHA_LOGIN && (!($captcha & CAPTCHA_LOGIN_FAIL) || $captcha & CAPTCHA_LOGIN_FAIL && 2 < $_SESSION['login_fail']) && 0 < gd_version()) {
		$GLOBALS['smarty']->assign('enabled_captcha', 1);
		$GLOBALS['smarty']->assign('rand', mt_rand());
	}

	$smarty->assign('passwd_question', $_LANG['passwd_questions'][$user_question_arr['passwd_question']]);
	$smarty->display('user_passport.dwt');
}
else if ($action == 'check_answer') {
	$_POST = get_request_filter($_POST, 1);
	$user_name = empty($_POST['user_name']) ? '' : compile_str(trim($_POST['user_name']));
	$sel_question = empty($_POST['sel_question']) ? '' : compile_str($_POST['sel_question']);
	$passwd_answer = isset($_POST['passwd_answer']) ? compile_str(trim($_POST['passwd_answer'])) : '';
	$captcha_str = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';
	$verify = new Verify();
	$captcha_code = $verify->check($captcha_str, 'psw_question');

	if (!$captcha_code) {
		show_message($_LANG['invalid_captcha'], $_LANG['back_up_page'], 'user.php?act=get_password', 'error');
		exit();
	}

	$sql = 'SELECT user_id, user_name, passwd_question, passwd_answer FROM ' . $ecs->table('users') . ' WHERE user_name = \'' . $user_name . '\' ' . ('AND passwd_question = \'' . $sel_question . '\' AND passwd_answer = \'' . $passwd_answer . '\'');
	$user_question_arr = $db->getRow($sql);

	if (empty($user_question_arr)) {
		show_message($_LANG['wrong_passwd_answer'], '', 'user.php?act=get_password', 'info');
	}
	else {
		$_SESSION['user_id'] = $user_question_arr['user_id'];
		$_SESSION['user_name'] = $user_question_arr['user_name'];
		$smarty->assign('uid', $_SESSION['user_id']);
		$smarty->assign('action', 'reset_password');
		$smarty->display('user_passport.dwt');
	}
}
else if ($action == 'send_pwd_email') {
	include_once ROOT_PATH . 'includes/lib_passport.php';
	$_POST = get_request_filter($_POST, 1);
	$user_name = !empty($_POST['user_name']) ? trim($_POST['user_name']) : '';
	$email = !empty($_POST['email']) ? trim($_POST['email']) : '';
	$captcha_str = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';
	$verify = new Verify();
	$captcha_code = $verify->check($captcha_str, 'get_password');
	if (intval($_CFG['captcha']) & CAPTCHA_REGISTER && 0 < gd_version()) {
		if (!$captcha_code) {
			show_message($_LANG['invalid_captcha'], $_LANG['back_up_page'], 'user.php?act=get_password', 'error');
			exit();
		}
	}

	$user_info = $user->get_user_info($user_name);
	if ($user_info && $user_info['email'] == $email) {
		$code = md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']);

		if (send_pwd_email($user_info['user_id'], $user_name, $email, $code)) {
			show_message($_LANG['send_success'] . $email, $_LANG['back_home_lnk'], './', 'info');
		}
		else {
			show_message($_LANG['fail_send_password'], $_LANG['back_page_up'], './', 'info');
		}
	}
	else {
		show_message($_LANG['username_no_email'], $_LANG['back_page_up'], '', 'info');
	}
}
else if ($action == 'get_pwd_mobile') {
	include_once ROOT_PATH . 'includes/lib_passport.php';
	if (intval($_CFG['captcha']) & CAPTCHA_REGISTER && 0 < gd_version()) {
		$smarty->assign('enabled_captcha', 1);
		$smarty->assign('rand', mt_rand());
	}

	$verify = new Verify();
	$captcha_code = $verify->check($captcha_str, 'get_phone_password');
	$_POST = get_request_filter($_POST, 1);
	$user_name = !empty($_POST['user_name']) ? trim($_POST['user_name']) : '';
	$mobile_phone = !empty($_POST['mobile_phone']) ? trim($_POST['mobile_phone']) : '';
	$mobile_code = !empty($_POST['mobile_code']) ? trim($_POST['mobile_code']) : '';
	$user_name = INPUT_I('post.user_name', '');
	$mobile_phone = INPUT_I('post.mobile_phone', '');
	$mobile_code = INPUT_I('post.mobile_code', '');
	$user_name = !empty($user_name) ? $user_name : dsc_addslashes($user_name);
	$mobile_phone = !empty($username) ? $mobile_phone : dsc_addslashes($mobile_phone);
	$mobile_code = !empty($username) ? $mobile_code : dsc_addslashes($mobile_code);
	$is_phone = get_is_phone($mobile_phone);

	if (!$is_phone) {
		show_message($_LANG['Mobile_username'], $_LANG['back_retry_answer'], 'user.php?act=get_password', 'info');
	}

	if (empty($mobile_phone) || empty($mobile_code)) {
		show_message($_LANG['Mobile_code_null'], $_LANG['back_retry_answer'], 'user.php?act=get_password', 'info');
		exit();
	}

	if ($mobile_code != $_SESSION['sms_mobile_code']) {
		show_message($_LANG['Mobile_code_msg'], $_LANG['back_retry_answer'], 'user.php?act=get_password', 'info');
		exit();
	}

	$where = '';

	if (!empty($user_name)) {
		$where = ' AND user_name = \'' . $user_name . '\'';
	}

	$sql = 'SELECT user_id, user_name FROM ' . $ecs->table('users') . (' WHERE mobile_phone = \'' . $mobile_phone . '\' ' . $where . ' LIMIT 1');
	$user_arr = $db->getRow($sql);
	$smarty->assign('uid', $user_arr['user_id']);
	$smarty->assign('action', 'reset_password');
	$smarty->display('user_passport.dwt');
}
else if ($action == 'reset_password') {
	$smarty->display('user_passport.dwt');
}
else if ($action == 'act_edit_password') {
	include_once ROOT_PATH . 'includes/lib_passport.php';
	$_POST = get_request_filter($_POST, 1);
	$old_password = isset($_POST['old_password']) ? trim($_POST['old_password']) : '';
	$new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
	$user_id = isset($_POST['uid']) ? intval($_POST['uid']) : $user_id;
	$code = isset($_POST['code']) ? trim($_POST['code']) : '';
	$captcha_str = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';
	$verify = new Verify();
	$captcha_code = $verify->check($captcha_str, 'get_password');
	$comfirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

	if (strlen($new_password) < 6) {
		show_message($_LANG['passport_js']['password_shorter']);
	}

	if (strlen($new_password) !== strlen($comfirm_password)) {
		show_message($_LANG['password_difference']);
	}

	$user_info = $user->get_profile_by_id($user_id);
	if (isset($_SESSION['user_id']) && empty($_SESSION['user_id']) && empty($old_password)) {
		$is_true = $user_id;
	}
	else {
		$is_true = 0 < $_SESSION['user_id'] && $_SESSION['user_id'] == $user_id;
	}

	if (isset($_SESSION['user_name']) && empty($_SESSION['user_name']) && empty($old_password)) {
		$user_name = $user_info['user_name'];
	}
	else {
		$user_name = $_SESSION['user_name'];
	}

	if (!empty($old_password)) {
		$is_oldpwd = $user->check_user($user_name, $old_password);
	}
	else {
		$is_oldpwd = true;
	}

	if ($user_info && (!empty($code) && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) == $code) || $is_true && $is_oldpwd) {
		if (!empty($old_password)) {
			$user_name = empty($code) ? $_SESSION['user_name'] : $user_info['user_name'];
		}
		else {
			$user_name = $user_info['user_name'];
		}

		if ($user->edit_user(array('user_id' => $user_id, 'username' => $user_name, 'old_password' => $old_password, 'password' => $new_password), 1)) {
			$sql = 'UPDATE ' . $ecs->table('users') . 'SET `ec_salt`=\'0\' WHERE user_id= \'' . $user_id . '\'';
			$db->query($sql);
			$user->logout();
			show_message($_LANG['edit_password_success'], $_LANG['relogin_lnk'], 'user.php?act=login', 'info');
		}
		else {
			show_message($_LANG['edit_password_failure'], $_LANG['back_page_up'], '', 'info');
		}
	}
	else {
		show_message($_LANG['edit_password_failure'], $_LANG['back_page_up'], '', 'info');
	}
}
else if ($action == 'act_add_bonus') {
	include_once ROOT_PATH . 'includes/lib_transaction.php';
	include 'includes/cls_json.php';
	$json = new JSON();
	$result = array('message' => '', 'result' => '', 'error' => 0);
	$_REQUEST['bns'] = isset($_REQUEST['bns']) ? json_str_iconv($_REQUEST['bns']) : '';
	$bns = $json->decode($_REQUEST['bns']);
	$bouns_sn = intval($bns->bonus_sn);
	$password = compile_str($bns->password);
	$captcha_str = isset($bns->captcha) ? trim($bns->captcha) : '';

	if (0 < gd_version()) {
		$verify = new Verify();
		$captcha_code = $verify->check($captcha_str, 'bonus');

		if (!$captcha_code) {
			$result['error'] = 3;
			$result['message'] = $_LANG['invalid_captcha'];
		}
	}

	if ($result['error'] != 3) {
		if (empty($user_id)) {
			$result['error'] = 2;
			$result['message'] = $GLOBALS['_LANG']['not_login'];
		}
		else if (add_bonus($user_id, $bouns_sn, $password)) {
			$result['message'] = $_LANG['add_bonus_sucess'];
		}
		else {
			$result['error'] = 1;
			$result['message'] = $_LANG['add_bonus_false'];
		}
	}

	exit($json->encode($result));
}
else if ($action == 'add_value_card') {
	include_once ROOT_PATH . 'includes/lib_transaction.php';
	include 'includes/cls_json.php';
	$json = new JSON();
	$result = array('message' => '', 'result' => '', 'error' => 0);
	$_REQUEST['vc'] = isset($_REQUEST['vc']) ? json_str_iconv($_REQUEST['vc']) : '';
	$vc = $json->decode($_REQUEST['vc']);
	$value_card_sn = trim($vc->value_card_sn);
	$password = compile_str($vc->password);
	$captcha_str = isset($vc->captcha) ? trim($vc->captcha) : '';

	if (0 < gd_version()) {
		$verify = new Verify();
		$captcha_code = $verify->check($captcha_str, 'value_card');

		if (!$captcha_code) {
			$result['error'] = 3;
			$result['message'] = $_LANG['invalid_captcha'];
		}
	}

	if ($result['error'] != 3) {
		if (empty($user_id)) {
			$result['error'] = 2;
		}
		else {
			$result['error'] = 1;

			switch (add_value_card($user_id, $value_card_sn, $password)) {
			case 1:
				$result['message'] = $_LANG['vc_use_expire'];
				break;

			case 2:
				$result['message'] = $_LANG['vc_is_used'];
				break;

			case 3:
				$result['message'] = $_LANG['vc_is_used_by_other'];
				break;

			case 4:
				$result['message'] = $_LANG['vc_not_exist'];
				break;

			case 5:
				$result['message'] = $_LANG['vc_limit_expire'];
				break;

			default:
				$result['error'] = 0;
				$result['message'] = $_LANG['add_value_card_sucess'];
				break;
			}
		}
	}

	exit($json->encode($result));
}
else if ($action == 'use_pay_card') {
	include_once ROOT_PATH . 'includes/lib_transaction.php';
	include 'includes/cls_json.php';
	$json = new JSON();
	$result = array('message' => '', 'result' => '', 'error' => 0);
	$_REQUEST['pc'] = isset($_REQUEST['pc']) ? json_str_iconv($_REQUEST['pc']) : '';
	$pc = $json->decode($_REQUEST['pc']);
	$pay_card_sn = trim($pc->pay_card_sn);
	$password = compile_str($pc->password);
	$vid = trim($pc->vid);
	$captcha_str = isset($pc->captcha) ? trim($pc->captcha) : '';

	if (0 < gd_version()) {
		$verify = new Verify();
		$captcha_code = $verify->check($captcha_str, 'pay_card');

		if (!$captcha_code) {
			$result['error'] = 3;
			$result['message'] = $_LANG['invalid_captcha'];
		}
	}

	if ($result['error'] != 3) {
		if (empty($user_id)) {
			$result['error'] = 2;
			$result['message'] = $GLOBALS['_LANG']['not_login'];
		}
		else if (use_pay_card($user_id, $vid, $pay_card_sn, $password)) {
			$result['message'] = $_LANG['use_pay_card_sucess'];
		}
		else {
			$result['error'] = 1;
			$result['message'] = $_LANG['pc_not_exist'];
		}
	}

	exit($json->encode($result));
}
else if ($action == 'to_paid') {
	$value_card_id = empty($_GET['vid']) ? 0 : intval($_GET['vid']);

	if (0 < $value_card_id) {
		$smarty->assign('action', $action);
		$smarty->assign('vid', $value_card_id);
		$smarty->display('user_transaction.dwt');
	}
	else {
		header('location:user.php?act=value_card');
		exit();
	}
}
else {
	if ($action == 'order_list' || $action == 'order_recycle') {
		include_once ROOT_PATH . 'includes/lib_payment.php';
		include_once ROOT_PATH . 'includes/lib_order.php';

		if ($action == 'order_list') {
			$show_type = 0;
		}
		else if ($action == 'order_recycle') {
			$show_type = 1;
		}

		if (defined('THEME_EXTENSION')) {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$allorders = get_order_where_count($user_id, $show_type);
			$smarty->assign('allorders', $allorders);
			$signNum = get_user_order_comment_list($user_id, 1, 0);
			$smarty->assign('signNum', $signNum);
		}

		include_once ROOT_PATH . 'includes/lib_transaction.php';
		$order_type = isset($_REQUEST['order_type']) ? addslashes(trim($_REQUEST['order_type'])) : '';
		$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
		$basic_info = get_seller_info();

		if ($basic_info['kf_ww']) {
			$kf_ww = array_filter(preg_split('/\\s+/', $basic_info['kf_ww']));
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

		if ($basic_info['kf_qq']) {
			$kf_qq = array_filter(preg_split('/\\s+/', $basic_info['kf_qq']));
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

		$smarty->assign('basic_info', $basic_info);
		$smarty->assign('status_list', $_LANG['cs']);

		if ($action == 'order_list') {
			$type = 0;
			$smarty->assign('action', $action);
		}
		else if ($action == 'order_recycle') {
			$type = 1;
			$smarty->assign('action', $action);
		}

		$category = get_oneTwo_category();
		$smarty->assign('category', $category);
		$where_zc_order = ' AND oi.is_zc_order = 0 ';
		$where_stay = ' AND   oi.order_status = \'' . OS_UNCONFIRMED . '\'' . $where_zc_order;
		$unconfirmed = get_order_where_count($user_id, $type, $where_stay);
		$smarty->assign('unconfirmed', $unconfirmed);
		$where_pay = ' AND   oi.order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) . ' AND oi.pay_status ' . db_create_in(array(PS_UNPAYED, PS_PAYED_PART)) . ' AND ( oi.shipping_status ' . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . ' OR oi.pay_id ' . db_create_in(get_payment_id_list(false)) . ') ' . $where_zc_order;
		$pay_count = get_order_where_count($user_id, $type, $where_pay);
		$smarty->assign('pay_count', $pay_count);
		$where_confirmed = ' AND oi.order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) . '  AND oi.shipping_status = \'' . SS_SHIPPED . '\' AND oi.pay_status ' . db_create_in(array(PS_PAYED, PS_PAYING)) . $where_zc_order;
		$to_confirm_order = get_order_where_count($user_id, $type, $where_confirmed);
		$smarty->assign('to_confirm_order', $to_confirm_order);
		$where_complete = ' AND oi.order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) . '  AND oi.shipping_status = \'' . SS_RECEIVED . '\' AND oi.pay_status ' . db_create_in(array(PS_PAYED, PS_PAYING)) . $where_zc_order;
		$to_finished = get_order_where_count($user_id, $type, $where_complete);
		$smarty->assign('to_finished', $to_finished);
		$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('order_info') . ' as oi_1' . (' WHERE oi_1.user_id = \'' . $user_id . '\' and oi_1.is_delete = \'' . $type . '\' ') . ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi_2 WHERE oi_2.main_order_id = oi_1.order_id) = 0 ' . ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og WHERE og.order_id = oi_1.order_id) > 0 ' . ' AND is_zc_order = 0 ';
		$record_count = $db->getOne($sql);
		$order_where = '';

		if (str_len($order_type) == str_len('toBe_unconfirmed')) {
			$order_where = 1;
		}
		else if (str_len($order_type) == str_len('toBe_pay')) {
			$order_where = 2;
		}
		else if (str_len($order_type) == str_len('toBe_confirmed')) {
			$order_where = 3;
		}
		else if (str_len($order_type) == str_len('toBe_finished')) {
			$order_where = 4;
		}
		else {
			$order_where = 0;
		}

		$orders = get_user_orders($user_id, $record_count, $page, $type, '', $action);
		$merge = get_user_merge($user_id);
		$smarty->assign('order_type', $order_type);
		$smarty->assign('order_where', $order_where);
		$smarty->assign('merge', $merge);
		$smarty->assign('orders', $orders);
		$smarty->assign('open_delivery_time', $GLOBALS['_CFG']['open_delivery_time']);
		$smarty->display('user_transaction.dwt');
	}
	else if ($action == 'apply_delivery') {
		include_once 'includes/cls_json.php';
		$json = new JSON();
		$res = array('err_msg' => '', 'result' => '', 'error' => 0);
		$order_id = !empty($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;

		if ($order_id) {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_delayed') . (' WHERE order_id = \'' . $order_id . '\'');
			$apply_count = $GLOBALS['db']->getOne($sql);
			$order_delay_num = isset($GLOBALS['_CFG']['order_delay_num']) && 0 < $GLOBALS['_CFG']['order_delay_num'] ? intval($GLOBALS['_CFG']['order_delay_num']) : 3;

			if ($apply_count <= $order_delay_num) {
				$apply_data = array('order_id' => $order_id, 'apply_time' => gmtime());
				$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_delayed') . (' WHERE order_id = \'' . $order_id . '\' AND review_status = 0');
				$no_review = $GLOBALS['db']->getOne($sql);

				if (0 < $no_review) {
					$res = array('err_msg' => $_LANG['order_delayed_repeat'], 'result' => '', 'error' => 3);
					exit($json->encode($res));
				}

				if ($GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_delayed'), $apply_data, 'INSERT')) {
					$res = array('err_msg' => $_LANG['order_delayed_success'], 'result' => '', 'error' => 0);
				}
			}
			else {
				$res = array('err_msg' => sprintf($_LANG['order_delayed_beyond'], $order_delay_num), 'result' => '', 'error' => 2);
			}
		}
		else {
			$res = array('err_msg' => $_LANG['order_delayed_wrong'], 'result' => '', 'error' => 1);
		}

		exit($json->encode($res));
	}
	else {
		if ($action == 'order_detail' || $action == 'auction_order_detail') {
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			include_once ROOT_PATH . 'includes/lib_payment.php';
			include_once ROOT_PATH . 'includes/lib_order.php';
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$order_id = isset($_GET['order_id']) && !empty($_GET['order_id']) ? intval($_GET['order_id']) : 0;
			$noTime = gmtime();
			$sql = 'SELECT order_id, user_id, order_sn , order_status, shipping_status, pay_status, auto_delivery_time, add_time, pay_time, ' . 'order_amount, goods_amount, tax, invoice_type, shipping_fee, insure_fee, pay_fee, pack_fee, card_fee, shipping_time, ' . 'bonus, integral_money, coupons, discount, money_paid, surplus, confirm_take_time, tax_id, pay_id ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
			$orderInfo = $GLOBALS['db']->GetRow($sql);
			if (empty($orderInfo) || $user_id != $orderInfo['user_id']) {
				$Loaction = 'user.php';
				ecs_header('Location: ' . $Loaction . "\n");
			}

			if ($_CFG['open_delivery_time'] == 1) {
				if (($orderInfo['order_status'] == OS_CONFIRMED || $orderInfo['order_status'] == OS_SPLITED) && $orderInfo['shipping_status'] == SS_SHIPPED && $orderInfo['pay_status'] == PS_PAYED) {
					$delivery_time = $orderInfo['shipping_time'] + 24 * 3600 * $orderInfo['auto_delivery_time'];

					if ($delivery_time < $noTime) {
						$confirm_take_time = gmtime();
						$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET order_status = \'' . OS_SPLITED . '\', ' . 'shipping_status = \'' . SS_RECEIVED . '\', pay_status = \'' . PS_PAYED . ('\', confirm_take_time = \'' . $confirm_take_time . '\' WHERE order_id = \'' . $order_id . '\'');

						if ($GLOBALS['db']->query($sql)) {
							$note = $GLOBALS['_LANG']['self_motion_goods'];
							order_action($orderInfo['order_sn'], OS_SPLITED, SS_RECEIVED, PS_PAYED, $note, $GLOBALS['_LANG']['buyer'], 0, $confirm_take_time);
							$seller_id = $GLOBALS['db']->getOne('SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\''), true);
							$value_card = $GLOBALS['db']->getOne('SELECT use_val FROM ' . $GLOBALS['ecs']->table('value_card_record') . (' WHERE order_id = \'' . $order_id . '\''), true);
							$return_amount = get_order_return_amount($order_id);
							$other = array('user_id' => $orderInfo['user_id'], 'seller_id' => $seller_id, 'order_id' => $orderInfo['order_id'], 'order_sn' => $orderInfo['order_sn'], 'order_status' => $orderInfo['order_status'], 'shipping_status' => SS_RECEIVED, 'pay_status' => $orderInfo['pay_status'], 'order_amount' => $orderInfo['order_amount'], 'return_amount' => $return_amount, 'goods_amount' => $orderInfo['goods_amount'], 'tax' => $orderInfo['tax'], 'tax_id' => $orderInfo['tax_id'], 'invoice_type' => $orderInfo['invoice_type'], 'shipping_fee' => $orderInfo['shipping_fee'], 'insure_fee' => $orderInfo['insure_fee'], 'pay_fee' => $orderInfo['pay_fee'], 'pack_fee' => $orderInfo['pack_fee'], 'card_fee' => $orderInfo['card_fee'], 'bonus' => $orderInfo['bonus'], 'integral_money' => $orderInfo['integral_money'], 'coupons' => $orderInfo['coupons'], 'discount' => $orderInfo['discount'], 'value_card' => $value_card, 'money_paid' => $orderInfo['money_paid'], 'surplus' => $orderInfo['surplus'], 'confirm_take_time' => $confirm_take_time);

							if ($seller_id) {
								get_order_bill_log($other);
							}
						}
					}
				}
			}

			if ($db->getOne('SELECT user_surplus FROM ' . $ecs->table('users_paypwd') . (' WHERE user_id = \'' . $_SESSION['user_id'] . '\''))) {
				$smarty->assign('open_pay_password', 1);
			}

			$order = get_order_detail($order_id, $user_id);
			$payment = payment_info($orderInfo['pay_id']);
			$smarty->assign('pay_desc', $payment['pay_desc']);
			if (($orderInfo['order_status'] == OS_UNCONFIRMED || $orderInfo['order_status'] == OS_CONFIRMED || $orderInfo['order_status'] == OS_SPLITED) && $orderInfo['pay_status'] == PS_UNPAYED) {
				$pay_log = get_pay_log($orderInfo['order_id'], 1);
				if ($pay_log && $pay_log['is_paid'] == 0) {
					$file_pay = ROOT_PATH . 'includes/modules/payment/' . $payment['pay_code'] . '.php';
					if ($payment && file_exists($file_pay)) {
						include_once $file_pay;

						if (class_exists($payment['pay_code'])) {
							$pay_obj = new $payment['pay_code']();
							$is_callable = array($pay_obj, 'query');

							if (is_callable($is_callable)) {
								$order_other = array('order_sn' => $row['order_sn'], 'log_id' => $pay_log['log_id']);
								$pay_obj->query($order_other);
								$sql = 'SELECT order_status, shipping_status, pay_status, pay_time FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $orderInfo['order_id'] . '\' LIMIT 1';
								$order_info = $GLOBALS['db']->getRow($sql);

								if ($order_info) {
									$orderInfo['order_status'] = $order['order_status'] = $order_info['order_status'];
									$orderInfo['shipping_status'] = $order['shipping_status'] = $order_info['shipping_status'];
									$orderInfo['pay_status'] = $order['pay_status'] = $order_info['pay_status'];
									$orderInfo['pay_time'] = $order['pay_time'] = $order_info['pay_time'];
								}
							}
						}
					}
				}
			}

			if ($order['extension_code'] == 'seckill') {
				$seckill_status = is_invalid($order['extension_id']);
				$smarty->assign('seckill_status', $seckill_status);
			}

			if ($order['invoice_type'] == 1) {
				$sql = ' SELECT * FROM ' . $ecs->table('users_vat_invoices_info') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
				$res = $db->getRow($sql);
				$smarty->assign('vat_info', $res);
			}

			$sql = 'SELECT store_id,pick_code,take_time  FROM' . $ecs->table('store_order') . (' WHERE order_id = \'' . $order_id . '\'');
			$stores = $db->getRow($sql);
			$order['store_id'] = $stores['store_id'];
			$order['pick_code'] = $stores['pick_code'];
			$order['take_time'] = $stores['take_time'];

			if (0 < $order['store_id']) {
				$sql = 'SELECT o.*,p.region_name as province,c.region_name as city,d.region_name as district FROM' . $ecs->table('offline_store') . ' AS o ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS p ON p.region_id = o.province ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS c ON c.region_id = o.city ' . 'LEFT JOIN ' . $ecs->table('region') . ' AS d ON d.region_id = o.district WHERE o.id = \'' . $order['store_id'] . '\'';
				$offline_store = $db->getRow($sql);
				$smarty->assign('offline_store', $offline_store);
			}

			if ($order === false) {
				$err->show($_LANG['back_home_lnk'], './');
				exit();
			}

			if ($orderInfo['order_status'] == OS_SPLITED && $orderInfo['shipping_status'] == SS_RECEIVED && $orderInfo['pay_status'] == PS_PAYED) {
				$order['return_url'] = 'user.php?act=goods_order&order_id=' . $order_id;
			}
			else {
				if ($orderInfo['order_status'] == OS_CONFIRMED && $orderInfo['shipping_status'] == SS_RECEIVED && $orderInfo['pay_status'] == PS_PAYED) {
					$order['return_url'] = 'user.php?act=goods_order&order_id=' . $order_id;
				}
			}

			$order['affirm_received'] = '';
			if (($orderInfo['order_status'] == OS_CONFIRMED || $orderInfo['order_status'] == OS_SPLITED) && $orderInfo['shipping_status'] == SS_SHIPPED && $orderInfo['pay_status'] == PS_PAYED) {
				$order['affirm_received'] = 'user.php?act=affirm_received&order_id=' . $order_id . '&action=info';
			}

			$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code ="sign"';
			$sign_time = $GLOBALS['db']->getOne($sql);

			if (0 < $sign_time) {
				if ($orderInfo['pay_status'] == PS_UNPAYED) {
					$where_log = ' AND ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART), 'order_status') . ' AND ' . db_create_in(array(SS_RECEIVED), 'shipping_status') . ' AND ' . db_create_in(array(PS_UNPAYED), 'pay_status');
				}
				else if ($orderInfo['pay_status'] == PS_PAYED) {
					$where_log = ' AND ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART), 'order_status') . ' AND ' . db_create_in(array(SS_UNSHIPPED, SS_SHIPPED, SS_RECEIVED, SS_SHIPPED_PART, OS_SHIPPED_PART), 'shipping_status') . ' AND ' . db_create_in(array(PS_PAYED), 'pay_status');
				}

				$sql = 'SELECT log_time FROM ' . $GLOBALS['ecs']->table('order_action') . ' WHERE order_id = \'' . $orderInfo['order_id'] . '\' ' . $where_log . ' order by action_id DESC';
				$log_time = $GLOBALS['db']->getOne($sql);
				$order['is_return'] = 0;
				$order_status = array(OS_CANCELED, OS_INVALID, OS_RETURNED);

				if (!in_array($orderInfo['order_status'], $order_status)) {
					if (!$log_time) {
						$log_time = !empty($orderInfo['pay_time']) ? $orderInfo['pay_time'] : $orderInfo['add_time'];
					}

					$signtime = $log_time + $sign_time * 3600 * 24;

					if ($noTime < $signtime) {
						$order['is_return'] = 1;
					}
				}

				if ($order['is_return'] != 1) {
					$order['return_url'] = '';
				}
			}

			if ($order['extension_code'] != 'group_buy' && $order['extension_code'] != 'exchange_goods' && $order['extension_code'] != 'presale') {
				$smarty->assign('allow_to_cart', 1);
			}

			$goods_list = order_goods($order_id);
			$order['goods_list_count'] = 0;

			foreach ($goods_list as $key => $value) {
				$goods_list[$key]['market_price'] = price_format($value['market_price'], false);
				$goods_list[$key]['goods_price'] = price_format($value['goods_price'], false);
				$goods_list[$key]['subtotal'] = price_format($value['subtotal'], false);

				if ($value['is_real'] == 0) {
					$goods_list[$key]['virtual_info'] = get_virtual_goods_info($value['rec_id']);
				}

				$order['goods_list_count'] += $value['goods_number'];
			}

			$shop_info = order_shop_info($order_id);
			$order['shop_name'] = $shop_info['shop_name'];
			$order['shop_url'] = $shop_info['shop_url'] ? $shop_info['shop_url'] : 'index.php';
			$zc_goods_info = get_zc_goods_info($order_id);
			$smarty->assign('zc_goods_info', $zc_goods_info);

			if (0 < $order['order_amount']) {
				if ($order['order_status'] == OS_UNCONFIRMED || $order['order_status'] == OS_CONFIRMED) {
					$user = user_info($order['user_id']);
					$is_balance = $db->getOne('SELECT enabled FROM ' . $ecs->table('payment') . ' WHERE pay_code = \'balance\'');
					if (0 < $user['user_money'] + $user['credit_line'] && $is_balance) {
						$smarty->assign('allow_edit_surplus', 1);
						$smarty->assign('max_surplus', sprintf($_LANG['max_surplus'], '<em id=\'max_surplus\'>' . $user['user_money'] . '</em>'));
					}
				}
			}

			if (0 < $order['order_amount'] && ($order['pay_status'] == PS_UNPAYED || $order['pay_status'] == PS_PAYED_PART) && $order['shipping_status'] == SS_UNSHIPPED) {
				$payment_list = available_payment_list(false, 0, true);
				$is_onlinepay = $GLOBALS['db']->getOne('SELECT enabled FROM ' . $ecs->table('payment') . ' WHERE pay_code=\'onlinepay\'');

				if ($is_onlinepay != 0) {
					$smarty->assign('is_onlinepay', $is_onlinepay);
				}

				$seller_grade = 1;

				if ($order['ru_id']) {
					$sg_ru_id = array($order['ru_id']);
					$seller_grade = get_seller_grade($sg_ru_id, 1);
				}

				if (is_array($payment_list)) {
					$bt_sql = 'SELECT amount FROM ' . $ecs->table('baitiao') . ' WHERE user_id=\'' . $_SESSION['user_id'] . '\'';
					$user_baitao_amount = $GLOBALS['db']->getOne($bt_sql);

					if ($payment_list) {
						foreach ($payment_list as $key => $payment) {
							if (substr($payment['pay_code'], 0, 4) == 'pay_') {
								unset($payment_list[$key]);
								continue;
							}

							if ($payment['pay_id'] == $order['pay_id'] || $payment['pay_code'] == 'balance') {
								unset($payment_list[$key]);
							}

							if ($payment['pay_code'] == 'chunsejinrong') {
								if (1 < count($goods_list) || count($goods_list) == 1 && $goods_list[0]['stages_qishu'] < 0) {
									unset($payment_list[$key]);
								}

								if ($seller_grade == 0) {
									unset($payment_list[$key]);
								}
							}
						}
					}
				}

				$smarty->assign('payment_list', $payment_list);
			}

			$order['order_tracking'] = 1;
			if ($order['order_status'] == OS_CONFIRMED || $order['order_status'] == OS_SPLITED || $order['order_status'] == OS_SPLITING_PART || $order['order_status'] == OS_RETURNED_PART) {
				$order['order_tracking'] = 2;

				if ($order['pay_status'] == PS_PAYED) {
					$order['order_tracking'] = 3;

					if ($order['shipping_status'] == SS_SHIPPED) {
						$order['order_tracking'] = 4;
					}
					else if ($order['shipping_status'] == SS_RECEIVED) {
						$order['order_tracking'] = 5;
					}
				}
			}

			$order['dsc_shipping_status'] = $order['shipping_status'];
			$order['order_status'] = $_LANG['os'][$order['order_status']];
			$order['pay_status_desc'] = $_LANG['ps'][$order['pay_status']];
			$order['shipping_status'] = $_LANG['ss'][$order['shipping_status']];
			$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('feedback') . (' WHERE parent_id = 0 AND order_id = \'' . $order_id . '\' AND user_id = \'' . $user_id . '\'');
			$feedback_num = $db->getOne($sql);
			$sql = 'SELECT address_id FROM ' . $ecs->table('users') . (' WHERE user_id = \'' . $user_id . '\'');
			$address_id = $db->getOne($sql);
			$sql = 'SELECT shipping_code FROM ' . $ecs->table('shipping') . (' WHERE shipping_id = \'' . $order['shipping_id'] . '\'');

			if ($db->getOne($sql) == 'cac') {
				$sql = 'SELECT * FROM ' . $ecs->table('shipping_point') . ' WHERE id IN (SELECT point_id FROM ' . $ecs->table('order_info') . (' WHERE order_id=\'' . $order_id . '\')');
				$order['point'] = $db->getRow($sql);
				$order['point']['pickDate'] = $order['shipping_dateStr'];
			}

			if ($main_order_id = $db->getOne('SELECT main_order_id FROM ' . $ecs->table('order_info') . (' WHERE order_id = \'' . $order_id . '\' AND main_order_id <> 0 '))) {
				$sql = 'SELECT is_repay,is_stages,stages_total,stages_one_price,yes_num,order_id FROM ' . $ecs->table('baitiao_log') . (' WHERE order_id= \'' . $main_order_id . '\' AND user_id = \'' . $user_id . '\'');
			}
			else {
				$sql = 'SELECT is_repay,is_stages,stages_total,stages_one_price,yes_num,order_id,repay_date FROM ' . $ecs->table('baitiao_log') . (' WHERE order_id= \'' . $order_id . '\' AND user_id = \'' . $user_id . '\'');
			}

			$stages_info = $db->getRow($sql);

			if ($stages_info) {
				$repay_dates = unserialize($stages_info['repay_date']);
				$stages_info['repay_date'] = $repay_dates[$stages_info['yes_num'] + 1];
				$smarty->assign('is_baitiao', true);
				$smarty->assign('stages_info', $stages_info);
			}

			if ($order['extension_code'] == 'presale' && $order['pay_status'] == PS_PAYED_PART) {
				$smarty->assign('is_presale', true);
				$result = presale_settle_status($order['extension_id']);
				$smarty->assign('settle_status', $result['settle_status']);
				$smarty->assign('pay_start_time', $result['start_time']);
				$smarty->assign('pay_end_time', $result['end_time']);
			}

			if (!empty($order['invoice_no'])) {
				$invoice_no_list = explode(',', $order['invoice_no']);
				$order['invoice_no_list'] = $invoice_no_list;
				$order['invoice_no_count'] = count($invoice_no_list);
			}

			$smarty->assign('order', $order);
			$smarty->assign('address_id', $address_id);
			$smarty->assign('goods_list', $goods_list);
			$smarty->assign('goods_list_count', $order['goods_list_count']);
			$smarty->assign('feedback_num', $feedback_num);
			$smarty->assign('open_order_delay', $_CFG['open_order_delay']);
			$smarty->assign('can_invoice', $_CFG['can_invoice']);
			$smarty->display('user_transaction.dwt');
		}
		else if ($action == 'order_delete_restore') {
			include_once 'includes/cls_json.php';
			$_POST['order'] = strip_tags(urldecode($_POST['order']));
			$_POST['order'] = json_str_iconv($_POST['order']);
			$result = array('error' => 0, 'message' => '', 'content' => '', 'order_id' => '');
			$json = new JSON();
			$order = $json->decode($_POST['order']);
			$act = $order->act;
			$order_id = $order->order_id;
			$result['order_id'] = $order_id;

			if ($order->order_id <= 0) {
				$result['error'] = 1;
				exit($json->encode($result));
			}

			if ($order->action == 'delete') {
				$type = 1;
				$show_type = 0;
				$smarty->assign('action', 'order_list');
			}
			else if ($order->action == 'restore') {
				$type = 0;
				$show_type = 1;
				$smarty->assign('action', 'order_recycle');
			}
			else if ($order->action == 'thorough') {
				$show_type = 1;
				$smarty->assign('action', 'order_recycle');
			}

			if ($order->action != 'thorough') {
				$parent = array('is_delete' => $type);
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $parent, 'UPDATE', 'order_id = \'' . $order_id . '\'');
			}
			else {
				$main_order_id = $db->getOne('select main_order_id from ' . $ecs->table('order_info') . (' where order_id = \'' . $order_id . '\''));
				$db->query('UPDATE ' . $ecs->table('order_info') . ' SET is_delete = 2' . (' where order_id = \'' . $order_id . '\''));
				$sql = 'SELECT order_status, shipping_status, pay_status FROM ' . $ecs->table('order_info') . (' WHERE order_id = \'' . $order_id . '\'');
				$order_info = $db->getRow($sql);
				$parent = array('order_id' => $order_id, 'action_user' => $_LANG['buyer'], 'order_status' => $order_info['order_status'], 'shipping_status' => $order_info['shipping_status'], 'pay_status' => $order_info['pay_status'], 'action_note' => $_LANG['delete_order'], 'log_time' => gmtime());
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_action'), $parent, 'INSERT');
			}

			include_once ROOT_PATH . 'includes/lib_transaction.php';
			$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
			$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('order_info') . (' WHERE user_id = \'' . $user_id . '\' and is_delete = \'' . $show_type . '\''));
			$action = 'order_list';
			$pager = get_pager('user.php', array('act' => $action), $record_count, $page);
			$orders = get_user_orders($user_id, $pager['size'], $pager['start'], $show_type, '', $act);
			$smarty->assign('pager', $pager);
			$smarty->assign('orders', $orders);
			$insert_arr = array('act' => $order->action, 'filename' => 'user');
			$smarty->assign('no_records', insert_get_page_no_records($insert_arr));
			$result['content'] = $smarty->fetch('library/user_order_list.lbi');
			$result['page_content'] = $smarty->fetch('library/pages.lbi');
			exit($json->encode($result));
		}
		else if ($action == 'order_to_query') {
			include_once 'includes/cls_json.php';
			$_POST['order'] = strip_tags(urldecode($_POST['order']));
			$_POST['order'] = json_str_iconv($_POST['order']);
			$result = array('error' => 0, 'message' => '', 'content' => '', 'order_id' => '');
			$json = new JSON();
			$order = $json->decode($_POST['order']);
			$order->keyword = addslashes(trim($order->keyword));

			if (0 < $order->order_id) {
				$result['error'] = 1;
				exit($json->encode($result));
			}

			if ($order->action == 'order_list' || $order->action == 'auction') {
				$show_type = 0;
			}
			else {
				$show_type = 1;
			}

			if ($order->action == 'auction') {
				$act = ' AND oi.extension_code = \'auction\'';
			}

			include_once ROOT_PATH . 'includes/lib_transaction.php';
			$where = get_order_search_keyword($order);
			$left_join = '';

			if (defined('THEME_EXTENSION')) {
				if ($order->idTxt == 'signNum') {
					$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' AS c WHERE c.comment_type = 0 AND c.id_value = g.goods_id AND c.rec_id = og.rec_id AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\') = 0 AND og.order_id = oi.order_id ');
				}

				$left_join = ' LEFT JOIN ' . $ecs->table('goods') . ' AS g ON g.goods_id = og.goods_id ';
			}

			$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
			$og_where = '';

			if ($order->type == 'text') {
				if ($order->keyword == $GLOBALS['_LANG']['user_keyword']) {
					$order->keyword = '';
				}

				if (!empty($order->keyword)) {
					$og_where = ' AND (og.goods_name LIKE \'%' . mysql_like_quote($order->keyword) . '%\' OR og.goods_sn LIKE \'%' . mysql_like_quote($order->keyword) . '%\')';
					$where .= ' AND (oi.order_sn LIKE \'%' . mysql_like_quote($order->keyword) . '%\' OR (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_goods') . (' AS og WHERE og.order_id = oi.order_id ' . $og_where . ') > 0)');
				}
			}
			else {
				$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og WHERE og.order_id = oi.order_id) > 0 ';
			}

			$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('order_info') . ' as oi' . (' WHERE oi.user_id = \'' . $user_id . '\' AND oi.is_delete = \'' . $show_type . '\' ') . ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi1 WHERE oi1.main_order_id = oi.order_id) = 0 ' . ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_goods') . (' AS og WHERE og.order_id = oi.order_id ' . $og_where . ') > 0 ') . ' AND oi.is_zc_order = 0 ' . $where;
			$record_count = $db->getOne($sql);
			$orders = get_user_orders($user_id, $record_count, $page, $show_type, $where, $order, $show_type);

			if ($order->idTxt == 'submitDate') {
				$date_keyword = $order->keyword;
				$status_keyword = $order->status_keyword;
			}
			else if ($order->idTxt == 'status_list') {
				$date_keyword = $order->date_keyword;
				$status_keyword = $order->keyword;
			}
			else {
				if ($order->idTxt == 'payId' || $order->idTxt == 'to_finished' || $order->idTxt == 'to_confirm_order' || $order->idTxt == 'to_unconfirmed' || $order->idTxt == 'signNum') {
					$status_keyword = $order->keyword;
				}
			}

			$result['date_keyword'] = $date_keyword;
			$result['status_keyword'] = $status_keyword;
			$smarty->assign('orders', $orders);
			$smarty->assign('status_list', $_LANG['cs']);
			$smarty->assign('date_keyword', $date_keyword);
			$smarty->assign('status_keyword', $status_keyword);
			$insert_arr = array('act' => $order->action, 'filename' => 'user');
			$smarty->assign('no_records', insert_get_page_no_records($insert_arr));
			$smarty->assign('open_delivery_time', $GLOBALS['_CFG']['open_delivery_time']);
			$smarty->assign('action', $order->action);
			$smarty->assign('order_type', $order->idTxt);
			$result['content'] = $smarty->fetch('library/user_order_list.lbi');
			exit($json->encode($result));
		}
		else if ($action == 'cancel_order') {
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			include_once ROOT_PATH . 'includes/lib_order.php';
			$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

			if (cancel_order($order_id, $user_id)) {
				ecs_header("Location: user.php?act=order_list\n");
				exit();
			}
			else {
				$err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
			}
		}
		else if ($action == 'address_list') {
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/shopping_flow.php';
			$smarty->assign('lang', $_LANG);
			$from_flow = isset($_REQUEST['from_flow']) && !empty($_REQUEST['from_flow']) ? intval($_REQUEST['from_flow']) : 0;
			$smarty->assign('country_list', get_regions());
			$smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));
			$address_id = $db->getOne('SELECT address_id FROM ' . $ecs->table('users') . (' WHERE user_id=\'' . $user_id . '\''));
			$new_consignee_list = get_new_consignee_list($_SESSION['user_id']);
			$smarty->assign('new_consignee_list', $new_consignee_list);
			$smarty->assign('count_consignee', count($new_consignee_list));
			$smarty->assign('real_goods_count', 1);
			$smarty->assign('shop_country', $_CFG['shop_country']);
			$smarty->assign('address', $address_id);
			$smarty->assign('currency_format', $_CFG['currency_format']);
			$smarty->assign('integral_scale', $_CFG['integral_scale']);
			$smarty->assign('name_of_region', array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));

			if ($from_flow != 1) {
				$_SESSION['browse_trace'] = 'user.php?act=address_list';
			}

			$smarty->display('user_transaction.dwt');
		}
		else if ($action == 'address') {
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			$address_id = isset($_GET['aid']) ? intval($_GET['aid']) : 0;
			$smarty->assign('country_list', get_regions());
			$smarty->assign('province_list', get_regions(1, 1));
			$consignee = get_user_address_info($address_id);
			$sql = 'SELECT address_id FROM ' . $ecs->table('users') . (' WHERE user_id = \'' . $user_id . '\'');
			$default_address = $db->getOne($sql);
			$new_consignee_list = get_new_consignee_list($_SESSION['user_id']);
			$smarty->assign('new_consignee_list', $new_consignee_list);
			$smarty->assign('count_consignee', count($new_consignee_list));

			if ($address_id) {
				$province_list = get_regions(1, 1);
				$city_list = get_regions(2, $consignee['province']);
				$district_list = get_regions(3, $consignee['city']);
				$street_list = get_regions(4, $consignee['district']);
				$smarty->assign('province_list', $province_list);
				$smarty->assign('city_list', $city_list);
				$smarty->assign('district_list', $district_list);
				$smarty->assign('street_list', $street_list);
			}

			$smarty->assign('consignee', $consignee);
			$smarty->assign('address_id', $address_id);
			$smarty->assign('default_address', $default_address);
			$smarty->display('user_transaction.dwt');
		}
		else if ($action == 'ajax_BatchCancelFollow') {
			include_once 'includes/cls_json.php';
			$json = new JSON();
			$res = array('err_msg' => '', 'result' => '', 'error' => 0);
			$type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
			$goods_id = isset($_REQUEST['goods_id']) ? trim($_REQUEST['goods_id']) : '';

			if ($type == 0) {
				$is_attention = 1;
			}
			else if ($type == 1) {
				$is_attention = 0;
			}

			if (!empty($goods_id)) {
				if ($type == 0 || $type == 1) {
					$sql = 'UPDATE ' . $ecs->table('collect_goods') . (' SET is_attention = ' . $is_attention . ' WHERE goods_id in(' . $goods_id . ')');
				}
				else if ($type == 2) {
					$sql = 'DELETE FROM' . $ecs->table('collect_goods') . (' WHERE goods_id in(' . $goods_id . ')');
				}

				$db->query($sql);
			}

			$res['goods_id'] = $goods_id;
			exit($json->encode($res));
		}
		else if ($action == 'ajax_BrandBatchCancel') {
			include_once 'includes/cls_json.php';
			$json = new JSON();
			$res = array('err_msg' => '', 'result' => '', 'error' => 0);
			$type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
			$brands_rec_id = isset($_REQUEST['brands_rec_id']) ? trim($_REQUEST['brands_rec_id']) : '';

			if (!empty($brands_rec_id)) {
				$sql = 'DELETE FROM' . $ecs->table('collect_brand') . (' WHERE rec_id in(' . $brands_rec_id . ')');
				$db->query($sql);
			}

			$res['brands_rec_id'] = $brands_rec_id;
			exit($json->encode($res));
		}
		else if ($action == 'ajax_del_address') {
			include_once 'includes/cls_json.php';
			$json = new JSON();
			$res = array('err_msg' => '', 'result' => '', 'error' => 0);
			$address_id = isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0;
			$sql = 'DELETE FROM ' . $ecs->table('user_address') . (' WHERE address_id = \'' . $address_id . '\'');
			$db->query($sql);
			$res['address_id'] = $address_id;
			exit($json->encode($res));
		}
		else if ($action == 'ajax_update_address') {
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			include_once 'includes/cls_json.php';
			$json = new JSON();
			$res = array('err_msg' => '', 'result' => '', 'error' => 0);
			$address_id = isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0;
			$address = get_user_address_info($address_id);
			$smarty->assign('address', $address);
			$new_province_list = get_regions(1, $address['country']);
			$new_city_list = get_regions(2, $address['province']);
			$new_district_list = get_regions(3, $address['city']);
			$smarty->assign('country_list', get_regions());
			$smarty->assign('new_province_list', $new_province_list);
			$smarty->assign('new_city_list', $new_city_list);
			$smarty->assign('new_district_list', $new_district_list);
			$res['content'] = $smarty->fetch('library/user_editaddress.lbi');
			exit($json->encode($res));
		}
		else if ($action == 'ajax_add_address') {
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/shopping_flow.php';
			include_once 'includes/cls_json.php';
			$_POST['user_address'] = strip_tags(urldecode($_POST['user_address']));
			$_POST['user_address'] = json_str_iconv($_POST['user_address']);
			$result = array('error' => 0, 'message' => '', 'content' => '');
			$json = new JSON();
			$user_address = $json->decode($_POST['user_address']);
			$address = array('user_id' => $user_id, 'address_id' => intval($user_address->address_id), 'country' => isset($user_address->country) ? intval($user_address->country) : 0, 'province' => isset($user_address->province) ? intval($user_address->province) : 0, 'city' => isset($user_address->city) ? intval($user_address->city) : 0, 'district' => isset($user_address->district) ? intval($user_address->district) : 0, 'address' => isset($user_address->address) ? compile_str(trim($user_address->address)) : '', 'consignee' => isset($user_address->consignee) ? compile_str(trim($user_address->consignee)) : '', 'email' => isset($user_address->email) ? compile_str(trim($user_address->email)) : '', 'tel' => isset($user_address->tel) ? compile_str(make_semiangle(trim($user_address->tel))) : '', 'mobile' => isset($user_address->mobile) ? compile_str(make_semiangle(trim($user_address->mobile))) : '', 'best_time' => isset($user_address->best_time) ? compile_str(trim($user_address->best_time)) : '', 'sign_building' => isset($user_address->sign_building) ? compile_str(trim($user_address->sign_building)) : '', 'zipcode' => isset($user_address->zipcode) ? compile_str(make_semiangle(trim($user_address->zipcode))) : '');

			if (!update_address($address)) {
				$result['error'] = 1;
				$result['edit_address_failure'] = $_LANG['update_address_error'];
			}
			else {
				$result['browse_trace'] = $_SESSION['browse_trace'];
			}

			exit($json->encode($result));
		}
		else if ($action == 'ajax_make_address') {
			include_once 'includes/cls_json.php';
			$json = new JSON();
			$res = array('err_msg' => '', 'result' => '', 'error' => 0);
			$address_id = isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0;
			$sql = 'UPDATE ' . $ecs->table('users') . (' SET address_id = \'' . $address_id . '\' WHERE user_id = \'' . $user_id . '\'');
			$db->query($sql);
			$res['address_id'] = $address_id;
			exit($json->encode($res));
		}
		else if ($action == 'act_edit_address') {
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/shopping_flow.php';
			$smarty->assign('lang', $_LANG);
			$_POST = get_request_filter($_POST, 1);
			$default = isset($_POST['default']) ? intval($_POST['default']) : 0;
			$time = gmtime();
			$address = array('user_id' => $user_id, 'address_id' => intval($_POST['address_id']), 'country' => isset($_POST['country']) ? intval($_POST['country']) : 1, 'province' => isset($_POST['province']) ? intval($_POST['province']) : 0, 'city' => isset($_POST['city']) ? intval($_POST['city']) : 0, 'district' => isset($_POST['district']) ? intval($_POST['district']) : 0, 'street' => isset($_POST['street']) ? intval($_POST['street']) : 0, 'address' => isset($_POST['address']) ? compile_str(trim($_POST['address'])) : '', 'consignee' => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee'])) : '', 'email' => isset($_POST['email']) ? compile_str(trim($_POST['email'])) : '', 'tel' => isset($_POST['tel']) ? compile_str(make_semiangle(trim($_POST['tel']))) : '', 'mobile' => isset($_POST['mobile']) ? compile_str(make_semiangle(trim($_POST['mobile']))) : '', 'best_time' => isset($_POST['best_time']) ? trim($_POST['best_time']) : '', 'userUp_time' => $time, 'sign_building' => isset($_POST['sign_building']) ? compile_str(trim($_POST['sign_building'])) : '', 'zipcode' => isset($_POST['zipcode']) ? compile_str(make_semiangle(trim($_POST['zipcode']))) : '');
			if (!$address['user_id'] || !$address['province'] || !$address['mobile'] || !$address['address'] || !$address['consignee']) {
				show_message($_LANG['address_perfect_error'], $_LANG['back_up_page'], '', 'error');
			}

			if (update_address($address, $default)) {
				ecs_header("Location: user.php?act=address_list\n");
			}
		}
		else if ($action == 'drop_consignee') {
			include_once 'includes/lib_transaction.php';
			$consignee_id = intval($_GET['id']);

			if (drop_consignee($consignee_id)) {
				ecs_header("Location: user.php?act=address_list\n");
				exit();
			}
			else {
				show_message($_LANG['del_address_false']);
			}
		}
		else if ($action == 'collection_list') {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
			$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('collect_goods') . ' AS cg, ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . (' WHERE cg.goods_id = g.goods_id AND cg.user_id=\'' . $user_id . '\''));

			if (defined('THEME_EXTENSION')) {
				$size = 12;
			}
			else {
				$size = 10;
			}

			$collection_goods = get_collection_goods($user_id, $record_count, $page, 'collection_goods_gotoPage', $size);
			$smarty->assign('goods_list', $collection_goods['goods_list']);
			$smarty->assign('pager', $collection_goods['pager']);
			$smarty->assign('count', $collection_goods['record_count']);
			$smarty->assign('size', $collection_goods['size']);
			$smarty->assign('url', $ecs->url());
			$lang_list = array('UTF8' => $_LANG['charset']['utf8'], 'GB2312' => $_LANG['charset']['zh_cn'], 'BIG5' => $_LANG['charset']['zh_tw']);
			$smarty->assign('lang_list', $lang_list);
			$smarty->assign('user_id', $user_id);
			$smarty->display('user_clips.dwt');
		}
		else if ($action == 'store_list') {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
			$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('collect_store') . (' WHERE user_id=\'' . $user_id . '\''));

			if (defined('THEME_EXTENSION')) {
				$size = 5;
			}
			else {
				$size = 3;
			}

			$collection_store = get_collection_store($user_id, $record_count, $page, 'collection_store_gotoPage', $size);
			$smarty->assign('store_list', $collection_store['store_list']);
			$smarty->assign('pager', $collection_store['pager']);
			$smarty->assign('count', $collection_store['record_count']);
			$smarty->assign('size', $collection_store['size']);
			$smarty->assign('url', $ecs->url());
			$lang_list = array('UTF8' => $_LANG['charset']['utf8'], 'GB2312' => $_LANG['charset']['zh_cn'], 'BIG5' => $_LANG['charset']['zh_tw']);
			$smarty->assign('lang_list', $lang_list);
			$smarty->assign('user_id', $user_id);
			$smarty->display('user_clips.dwt');
		}
		else if ($action == 'focus_brand') {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
			$size = 5;
			$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('collect_brand') . (' WHERE user_id=\'' . $user_id . '\''));
			$collection_brands = get_collection_brands($user_id, $record_count, $page, 'collection_brands_gotoPage', $size);
			$smarty->assign('collection_brands', $collection_brands['brand_list']);
			$smarty->assign('pager', $collection_brands['pager']);
			$smarty->assign('count', $collection_brands['record_count']);
			$smarty->assign('size', $collection_brands['size']);
			$smarty->assign('url', $ecs->url());
			$lang_list = array('UTF8' => $_LANG['charset']['utf8'], 'GB2312' => $_LANG['charset']['zh_cn'], 'BIG5' => $_LANG['charset']['zh_tw']);
			$smarty->assign('lang_list', $lang_list);
			$smarty->assign('user_id', $user_id);
			$smarty->display('user_clips.dwt');
		}
		else if ($action == 'delete_collection') {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$type = isset($_GET['type']) ? intval($_GET['type']) : 0;
			$collection_id = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : 0;
			$sql = 'SELECT count(*) FROM ' . $ecs->table('collect_goods') . (' WHERE rec_id=\'' . $collection_id . '\' AND user_id =\'' . $user_id . '\'');

			if (0 < $db->getOne($sql)) {
				$db->query('DELETE FROM ' . $ecs->table('collect_goods') . (' WHERE rec_id=\'' . $collection_id . '\' AND user_id =\'' . $user_id . '\''));
			}

			if ($type == 1) {
				ecs_header("Location: user.php?act=collection_list\n");
			}
			else {
				ecs_header("Location: user.php\n");
			}
		}
		else if ($action == 'add_to_attention') {
			$rec_id = (int) $_GET['rec_id'];
			$goods_id = (int) $_GET['goods_id'];

			if ($rec_id) {
				$db->query('UPDATE ' . $ecs->table('collect_goods') . ('SET is_attention = 1 WHERE rec_id=\'' . $rec_id . '\' AND user_id =\'' . $user_id . '\''));
				$sql = 'SELECT COUNT(user_id) AS user_num FROM ' . $ecs->table('collect_goods') . ' WHERE goods_id=' . $goods_id . ' AND is_attention=1';
				$attention_num = $db->getOne($sql);
				$num = array('goods_id' => $goods_id, 'user_attention_number' => $attention_num);
				update_attention_num($goods_id, $num);
			}

			ecs_header("Location: user.php?act=collection_list\n");
			exit();
		}
		else if ($action == 'del_attention') {
			$rec_id = (int) $_GET['rec_id'];
			$goods_id = (int) $_GET['goods_id'];

			if ($rec_id) {
				$db->query('UPDATE ' . $ecs->table('collect_goods') . ('SET is_attention = 0 WHERE rec_id=\'' . $rec_id . '\' AND user_id =\'' . $user_id . '\''));
				$sql = 'SELECT COUNT(user_id) AS user_num FROM ' . $ecs->table('collect_goods') . ' WHERE goods_id=' . $goods_id . ' AND is_attention=1';
				$attention_num = $db->getOne($sql);
				$num = array('goods_id' => $goods_id, 'user_attention_number' => $attention_num);
				update_attention_num($goods_id, $num);
			}

			ecs_header("Location: user.php?act=collection_list\n");
			exit();
		}
		else if ($action == 'message_list') {
			include_once ROOT_PATH . 'includes/lib_clips.php';

			if (defined('THEME_EXTENSION')) {
				$smarty->assign('user_info', get_user_default($_SESSION['user_id']));
				$smarty->assign('upload_size_limit', upload_size_limit(1));
			}

			$is_order = isset($_REQUEST['is_order']) ? $_REQUEST['is_order'] : 0;
			$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
			$order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
			$order_info = array();
			$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('feedback') . (' WHERE parent_id = \'0\' AND  user_id = \'' . $user_id . '\' AND msg_type = \'5\' ');
			$is_have_order = $db->getOne($sql);
			$smarty->assign('is_have_order', $is_have_order);

			if ($is_order) {
				$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('feedback') . (' WHERE parent_id = \'0\' AND  user_id = \'' . $user_id . '\' AND msg_type = \'5\' ');
				$order_info = $db->getRow('SELECT * FROM ' . $ecs->table('order_info') . (' WHERE order_id = \'' . $order_id . '\' AND user_id = \'' . $user_id . '\' LIMIT 1'));
				$order_info['url'] = 'user.php?act=order_detail&order_id=' . $order_id;
				$record_count = $db->getOne($sql);
			}
			else {
				$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('feedback') . (' WHERE parent_id = 0 AND msg_status = 0  AND user_id = \'' . $user_id . '\' AND user_name = \'') . $_SESSION['user_name'] . '\' AND order_id=0';
				$record_count = $db->getOne($sql);
			}

			if (intval($_CFG['captcha']) & CAPTCHA_MESSAGE && 0 < gd_version()) {
				$smarty->assign('enabled_captcha', 1);
				$smarty->assign('rand', mt_rand());
			}

			if ($is_order) {
				$act = array('act' => $action . '&is_order=1');
			}
			else {
				$act = array('act' => $action);
			}

			if ($order_id != '') {
				$act['order_id'] = $order_id;
			}

			$pager = get_pager('user.php', $act, $record_count, $page, 5);
			$smarty->assign('is_order', $is_order);
			$message_list = get_message_list($user_id, $_SESSION['user_name'], $pager['size'], $pager['start'], $order_id, $is_order);
			$smarty->assign('message_list', $message_list);
			$smarty->assign('pager', $pager);
			$smarty->assign('order_info', $order_info);
			$smarty->display('user_clips.dwt');
		}
		else if ($action == 'comment_list') {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			if (intval($_CFG['captcha']) & CAPTCHA_COMMENT && 0 < gd_version()) {
				$smarty->assign('enabled_captcha', 1);
				$smarty->assign('rand', mt_rand());
			}

			$sign = isset($_REQUEST['sign']) ? intval($_REQUEST['sign']) : 0;
			$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
			$size = 10;
			$sql = 'select id, comment_img, img_thumb from ' . $ecs->table('comment_img') . (' where user_id = \'' . $user_id . '\' AND comment_id = 0');
			$img_list = $db->getAll($sql);

			foreach ($img_list as $key => $val) {
				get_oss_del_file(array($val['comment_img'], $val['img_thumb']));
				@unlink(ROOT_PATH . $val['comment_img']);
				@unlink(ROOT_PATH . $val['img_thumb']);
			}

			$sql = 'DELETE FROM ' . $ecs->table('comment_img') . (' WHERE user_id=\'' . $user_id . '\' AND comment_id = 0');
			$db->query($sql);
			$record_count = get_user_order_comment_list($user_id, 1, $sign);

			if (isset($_REQUEST['sign'])) {
				$action = $action . '&sign=' . $sign;
			}

			$pager = get_pager('user.php', array('act' => $action), $record_count, $page, $size);
			$comment_list = get_user_order_comment_list($user_id, 0, $sign, 0, $size, $pager['start']);
			$signNum0 = get_user_order_comment_list($user_id, 1, 0);
			$signNum1 = get_user_order_comment_list($user_id, 1, 1);
			$signNum2 = get_user_order_comment_list($user_id, 1, 2);
			$smarty->assign('comment_list', $comment_list);
			$smarty->assign('pager', $pager);
			$smarty->assign('sign', $sign);
			$smarty->assign('signNum0', $signNum0);
			$smarty->assign('signNum1', $signNum1);
			$smarty->assign('signNum2', $signNum2);
			$smarty->assign('sessid', SESS_ID);
			$smarty->display('user_clips.dwt');
		}
		else if ($action == 'commented_view') {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;
			$sign = isset($_REQUEST['sign']) ? intval($_REQUEST['sign']) : 0;
			if (intval($_CFG['captcha']) & CAPTCHA_COMMENT && 0 < gd_version()) {
				$smarty->assign('enabled_captcha', 1);
				$smarty->assign('rand', mt_rand());
			}

			$sql = 'DELETE FROM ' . $ecs->table('comment_img') . (' WHERE user_id=\'' . $_SESSION['user_id'] . '\' AND comment_id = 0');
			$db->query($sql);
			$order_goods = get_user_order_comment_list($_SESSION['user_id'], 0, $sign, $order_id);
			$ru_id = empty($order_goods[0]['ru_id']) ? 0 : $order_goods[0]['ru_id'];

			if ($ru_id) {
				$sql = 'SELECT ru_id,logo_thumb,shop_name,kf_tel FROM ' . $ecs->table('seller_shopinfo') . (' WHERE ru_id = \'' . $ru_id . '\'');
				$shop_info = $db->getRow($sql);

				if ($shop_info['logo_thumb']) {
					$shop_info['logo_thumb'] = substr($shop_info['logo_thumb'], 3);
				}

				$shop_info['logo_thumb'] = get_image_path($ru_id, $shop_info['logo_thumb']);
				$shop_info['seller_score'] = 5;
				$sql = 'SELECT SUM(service_rank) + SUM(desc_rank) + SUM(delivery_rank) + SUM(sender_rank) AS sum_rank, count(*) as num FROM ' . $ecs->table('comment_seller') . (' WHERE ru_id = \'' . $ru_id . '\'');
				$seller_row = $db->getRow($sql);

				if ($seller_row['num']) {
					$shop_info['seller_score'] = $shop_info['sum_rank'] / $shop_info['num'] / 4;
				}

				$shop_info['shop_name'] = get_shop_name($shop_info['ru_id'], 1);

				if (defined('THEME_EXTENSION')) {
					$merchants_goods_comment = get_merchants_goods_comment($ru_id);
					$build_uri = array('urid' => $row['user_id'], 'append' => $row['rz_shopName']);
					$domain_url = get_seller_domain_url($ru_id, $build_uri);
					$merchants_goods_comment['store_url'] = $domain_url['domain_name'];
					$shop_information = get_shop_name($ru_id);

					if ($goods['user_id'] == 0) {
						if ($db->getOne('SELECT kf_im_switch FROM ' . $ecs->table('seller_shopinfo') . 'WHERE ru_id = 0')) {
							$shop_information['is_dsc'] = true;
						}
						else {
							$shop_information['is_dsc'] = false;
						}
					}
					else {
						$shop_information['is_dsc'] = false;
					}

					$smarty->assign('shop_information', $shop_information);
					$smarty->assign('merch_cmt', $merchants_goods_comment);
				}
			}

			$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('comment_seller') . (' WHERE order_id = \'' . $order_id . '\' AND user_id = \'' . $_SESSION['user_id'] . '\'');
			$degree_count = $db->getOne($sql);
			$smarty->assign('order_goods', $order_goods);
			$smarty->assign('order_id', $order_id);
			$smarty->assign('degree_count', $degree_count);
			$smarty->assign('shop_info', $shop_info);
			$smarty->assign('sessid', SESS_ID);
			$smarty->assign('sign', $sign);
			$smarty->display('user_clips.dwt');
		}
		else if ($action == 'take_list') {
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
			$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('user_gift_gard') . (' WHERE user_id = \'' . $user_id . '\''));
			$pager = get_pager('user.php', array('act' => $action), $record_count, $page);
			$sql = 'SELECT ub.*, u.user_name, u.email, o.goods_name, bt.gift_name ,o.goods_thumb ' . ' FROM ' . $ecs->table('user_gift_gard') . ' AS ub ' . ' LEFT JOIN ' . $ecs->table('gift_gard_type') . ' AS bt ON bt.gift_id=ub.gift_id ' . ' LEFT JOIN ' . $ecs->table('users') . ' AS u ON u.user_id=ub.user_id ' . ' LEFT JOIN ' . $ecs->table('goods') . (' AS o ON o.goods_id=ub.goods_id WHERE ub.user_id=\'' . $user_id . '\'') . ' ORDER BY ub.user_time DESC ' . ' LIMIT ' . $pager['start'] . (', ' . $pager['size']);
			$row = $db->getAll($sql);

			foreach ($row as $key => $val) {
				$row[$key]['user_time'] = local_date('Y-m-d H:i:s', empty($val['user_time']) ? '' : $val['user_time']);

				if (empty($val['goods_thumb'])) {
					$row[$key]['goods_thumb'] = $GLOBALS['_CFG']['no_picture'];
				}
			}

			$smarty->assign('pager', $pager);
			$smarty->assign('take_list', $row);
			$smarty->display('user_clips.dwt');
		}
		else if ($action == 'confim_goods') {
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			$take_id = isset($_REQUEST['take_id']) ? intval($_REQUEST['take_id']) : 1;
			$up_id = $db->getOne('UPDATE ' . $ecs->table('user_gift_gard') . (' SET status=\'3\' WHERE gift_gard_id = \'' . $take_id . '\''));

			if ($db->affected_rows()) {
				ecs_header("Location: user.php?act=take_list\n");
				exit();
			}
			else {
				show_message($_LANG['receipt_fail'], $_LANG['back_receipt'], 'user.php?act=take_list');
			}
		}
		else if ($action == 'complaint_list') {
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
			$is_complaint = isset($_REQUEST['is_complaint']) ? intval($_REQUEST['is_complaint']) : 0;
			$time = gmtime();
			$complain_time = 15;

			if (0 < $_CFG['receipt_time']) {
				$complain_time = $_CFG['receipt_time'];
			}

			$dealy_time = $complain_time * 86400;
			$where_zc_order = ' AND oi.is_zc_order = 0 ';
			$where_confirmed = '';

			if ($is_complaint == 0) {
				$where_confirmed = ' AND oi.order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) . '  ' . 'AND IF(oi.pay_status = ' . PS_PAYED . ', IF(oi.shipping_status = ' . SS_RECEIVED . ', oi.shipping_status = \'' . SS_RECEIVED . ('\' AND (\'' . $time . '\'- oi.confirm_take_time) < \'' . $dealy_time . '\', ') . db_create_in(array(SS_RECEIVED), 'oi.shipping_status', 'NOT') . ') ';
				$where_confirmed .= 'AND oi.pay_status ' . db_create_in(array(PS_PAYED)) . ', IF(oi.shipping_status = ' . SS_RECEIVED . ', ' . db_create_in(array(SS_RECEIVED), 'oi.shipping_status') . (' AND (\'' . $time . '\'- oi.confirm_take_time) < \'' . $dealy_time . '\', ') . db_create_in(array(SS_UNSHIPPED), 'oi.shipping_status', 'NOT') . '))' . $where_zc_order;
			}

			if ($is_complaint == 0) {
				$where_confirmed .= ' AND (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('complaint') . ' AS com WHERE com.order_id = oi.order_id) = 0 ';
			}
			else {
				$where_confirmed .= ' AND (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('complaint') . ' AS com WHERE com.order_id = oi.order_id) > 0  ';
			}

			$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('order_info') . ' as oi' . (' WHERE oi.user_id = \'' . $user_id . '\' and oi.is_delete = 0 ') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi_2 where oi_2.main_order_id = oi.order_id) = 0 ' . (' AND is_zc_order = 0  ' . $where_confirmed));
			$pager = get_pager('user.php', array('act' => $action), $record_count, $page);
			$complaint_list = get_complaint_list($pager['size'], $pager['start'], $where_confirmed);
			$smarty->assign('no_records', $_LANG['no_records']);
			$smarty->assign('page', $page);
			$smarty->assign('pager', $pager);
			$smarty->assign('is_complaint', $is_complaint);
			$smarty->assign('orders', $complaint_list);
			$smarty->assign('action', $action);
			$smarty->display('user_clips.dwt');
		}
		else if ($action == 'complaint_apply') {
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			$complaint_id = !empty($_REQUEST['complaint_id']) ? intval($_REQUEST['complaint_id']) : 0;
			$order_id = !empty($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;
			$where = '';

			if (0 < $complaint_id) {
				$complaint_info = get_complaint_info($complaint_id);
				$order_id = $complaint_info['order_id'];

				if (1 < $complaint_info['complaint_state']) {
					$talk_list = checkTalkView($complaint_id, 'user');
					$smarty->assign('talk_list', $talk_list);
				}

				$where = ' AND complaint_id = \'' . $complaint_id . '\'';
				$smarty->assign('complaint_info', $complaint_info);
			}
			else {
				$where = ' AND complaint_id = 0';
				$complaint_title = get_complaint_title();
				$smarty->assign('complaint_title', $complaint_title);
			}

			$orders = order_info($order_id);
			$sql = 'SELECT kf_type, kf_ww, kf_qq  FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id=\'' . $orders['ru_id'] . '\'';
			$basic_info = $GLOBALS['db']->getRow($sql);
			$orders['shop_name'] = get_shop_name($orders['ru_id'], 1);
			$build_uri = array('urid' => $orders['ru_id'], 'append' => $orders['shop_name']);
			$domain_url = get_seller_domain_url($orders['ru_id'], $build_uri);
			$orders['shop_url'] = $domain_url['domain_name'];

			if ($basic_info['kf_qq']) {
				$kf_qq = array_filter(preg_split('/\\s+/', $basic_info['kf_qq']));
				$kf_qq = explode('|', $kf_qq[0]);

				if (!empty($kf_qq[1])) {
					$kf_qq_one = $kf_qq[1];
				}
				else {
					$kf_qq_one = '';
				}
			}
			else {
				$kf_qq_one = '';
			}

			$orders['kf_qq'] = $kf_qq_one;

			if ($basic_info['kf_ww']) {
				$kf_ww = array_filter(preg_split('/\\s+/', $basic_info['kf_ww']));
				$kf_ww = explode('|', $kf_ww[0]);

				if (!empty($kf_ww[1])) {
					$kf_ww_one = $kf_ww[1];
				}
				else {
					$kf_ww_one = '';
				}
			}
			else {
				$kf_ww_one = '';
			}

			$orders['kf_ww'] = $kf_ww_one;

			if ($GLOBALS['_CFG']['customer_service'] == 0) {
				$ru_id = 0;
			}
			else {
				$ru_id = $orders['ru_id'];
			}

			$shop_information = get_shop_name($ru_id);
			$orders['is_IM'] = $shop_information['is_IM'];

			if ($ru_id == 0) {
				if ($GLOBALS['db']->getOne('SELECT kf_im_switch FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = 0', true)) {
					$orders['is_dsc'] = true;
				}
				else {
					$orders['is_dsc'] = false;
				}
			}
			else {
				$orders['is_dsc'] = false;
			}

			$orders['order_goods'] = get_order_goods_toInfo($order_id);
			$img_list = complaint_images_list($user_id, $order_id, $where);
			$smarty->assign('img_list', $img_list);
			$smarty->assign('sessid', SESS_ID);
			$smarty->assign('complaint_id', $complaint_id);
			$smarty->assign('order', $orders);
			$smarty->assign('order_id', $order_id);
			$smarty->display('user_clips.dwt');
		}
		else if ($action == 'complaint_submit') {
			$order_id = !empty($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;
			$title_id = !empty($_REQUEST['title_id']) ? intval($_REQUEST['title_id']) : 0;
			$complaint_content = !empty($_REQUEST['complaint_content']) ? trim($_REQUEST['complaint_content']) : '';
			$sql = 'SELECT COUNT(*) FROM' . $ecs->table('complaint') . ('WHERE order_id = \'' . $order_id . '\'');
			$complaint_count = $db->getOne($sql);

			if (0 < $complaint_count) {
				show_message($_LANG['complaint_reprat']);
			}

			if ($title_id == 0) {
				show_message($_LANG['complaint_title_null']);
			}
			else if ($complaint_content == '') {
				show_message($_LANG['complaint_content_null']);
			}
			else {
				$sql = 'SELECT og.ru_id,oi.order_sn FROM' . $ecs->table('order_info') . ' AS oi LEFT JOIN ' . $ecs->table('order_goods') . (' AS og ON og.order_id = oi.order_id  WHERE oi.order_id = \'' . $order_id . '\' LIMIT 1');
				$order_info = $db->getRow($sql);
				$shop_name = get_shop_name($order_info['ru_id'], 1);
				$time = gmtime();
				$other = array('user_id' => $user_id, 'user_name' => $_SESSION['user_name'], 'order_id' => $order_id, 'shop_name' => $shop_name, 'order_sn' => $order_info['order_sn'], 'ru_id' => $order_info['ru_id'], 'title_id' => $title_id, 'add_time' => $time, 'complaint_content' => $complaint_content);
				$db->autoExecute($ecs->table('complaint'), $other, 'INSERT');
				$complaint_id = $db->insert_id();

				if (0 < $complaint_id) {
					$sql = 'UPDATE' . $ecs->table('complaint_img') . (' SET complaint_id = \'' . $complaint_id . '\' WHERE user_id = \'' . $user_id . '\' AND order_id = \'' . $order_id . '\' AND complaint_id = 0');
					$db->query($sql);
				}

				show_message($_LANG['complaint_success'], $_LANG['back_complaint_list'], 'user.php?act=complaint_list');
			}
		}
		else if ($action == 'arbitration') {
			$complaint_id = !empty($_REQUEST['complaint_id']) ? intval($_REQUEST['complaint_id']) : 0;
			$complaint_state = !empty($_REQUEST['complaint_state']) ? intval($_REQUEST['complaint_state']) : 3;
			$set = '';

			if ($complaint_state == 4) {
				$set = ',end_handle_messg=\'买家自行关闭\'';
			}

			$sql = 'UPDATE' . $ecs->table('complaint') . ('SET complaint_state = \'' . $complaint_state . '\' ' . $set . ' WHERE complaint_id = \'' . $complaint_id . '\'');
			$db->query($sql);
			show_message($_LANG['apply_success'], $_LANG['back_page_up'], 'user.php?act=complaint_apply&complaint_id=' . $complaint_id);
		}
		else if ($action == 'act_add_message') {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$_POST = get_request_filter($_POST, 1);
			$is_order = isset($_POST['is_order']) ? intval($_POST['is_order']) : 0;
			$message = array('user_id' => $user_id, 'user_name' => $_SESSION['user_name'], 'user_email' => $_SESSION['email'], 'msg_type' => isset($_POST['msg_type']) ? intval($_POST['msg_type']) : 0, 'msg_title' => isset($_POST['msg_title']) ? trim($_POST['msg_title']) : '', 'msg_content' => isset($_POST['msg_content']) ? trim($_POST['msg_content']) : '', 'order_id' => empty($_POST['order_id']) ? 0 : intval($_POST['order_id']), 'upload' => isset($_FILES['message_img']['error']) && $_FILES['message_img']['error'] == 0 || !isset($_FILES['message_img']['error']) && isset($_FILES['message_img']['tmp_name']) && $_FILES['message_img']['tmp_name'] != 'none' ? $_FILES['message_img'] : array());

			if (add_message($message)) {
				if ($is_order) {
					show_message($_LANG['add_message_success'], $_LANG['message_list_lnk'], 'user.php?act=message_list&is_order=1&order_id=' . $message['order_id'], 'info');
				}
				else {
					show_message($_LANG['add_message_success'], $_LANG['message_list_lnk'], 'user.php?act=message_list&order_id=' . $message['order_id'], 'info');
				}
			}
			else {
				$err->show($_LANG['message_list_lnk'], 'javascript:history.go(-1);');
			}
		}
		else if ($action == 'tag_list') {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$good_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$smarty->assign('tags', get_user_tags($user_id));
			$smarty->assign('tags_from', 'user');
			$smarty->display('user_clips.dwt');
		}
		else if ($action == 'act_del_tag') {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$tag_words = isset($_GET['tag_words']) ? trim($_GET['tag_words']) : '';
			delete_tag($tag_words, $user_id);
			ecs_header("Location: user.php?act=tag_list\n");
			exit();
		}
		else if ($action == 'booking_list') {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
			$sql = 'SELECT COUNT(*) ' . 'FROM ' . $ecs->table('booking_goods') . ' AS bg, ' . $ecs->table('goods') . ' AS g ' . ('WHERE bg.goods_id = g.goods_id AND bg.user_id = \'' . $user_id . '\'');
			$record_count = $db->getOne($sql);
			$pager = get_pager('user.php', array('act' => $action), $record_count, $page);
			$smarty->assign('booking_list', get_booking_list($user_id, $pager['size'], $pager['start']));
			$smarty->assign('pager', $pager);
			$smarty->display('user_clips.dwt');
		}
		else if ($action == 'add_booking') {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$goods_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

			if ($goods_id == 0) {
				show_message($_LANG['no_goods_id'], $_LANG['back_page_up'], '', 'error');
			}

			$goods_attr = '';

			if ($_GET['spec'] != '') {
				$goods_attr_id = $_GET['spec'];
				$attr_list = array();
				$sql = 'SELECT a.attr_name, g.attr_value ' . 'FROM ' . $ecs->table('goods_attr') . ' AS g, ' . $ecs->table('attribute') . ' AS a ' . 'WHERE g.attr_id = a.attr_id ' . 'AND g.goods_attr_id ' . db_create_in($goods_attr_id) . ' ORDER BY a.sort_order, a.attr_id, g.goods_attr_id';
				$res = $db->query($sql);

				while ($row = $db->fetchRow($res)) {
					$attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
				}

				$goods_attr = join(chr(13) . chr(10), $attr_list);
			}

			$smarty->assign('goods_attr', $goods_attr);
			$smarty->assign('info', get_goodsinfo($goods_id));
			$smarty->display('user_clips.dwt');
		}
		else if ($action == 'act_add_booking') {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$_POST = get_request_filter($_POST, 1);
			$booking = array('goods_id' => isset($_POST['id']) ? intval($_POST['id']) : 0, 'goods_amount' => isset($_POST['number']) ? intval($_POST['number']) : 0, 'desc' => isset($_POST['desc']) ? trim($_POST['desc']) : '', 'linkman' => isset($_POST['linkman']) ? trim($_POST['linkman']) : '', 'email' => isset($_POST['email']) ? trim($_POST['email']) : '', 'tel' => isset($_POST['tel']) ? trim($_POST['tel']) : '', 'booking_id' => isset($_POST['rec_id']) ? intval($_POST['rec_id']) : 0);
			$rec_id = get_booking_rec($user_id, $booking['goods_id']);

			if (0 < $rec_id) {
				show_message($_LANG['booking_rec_exist'], $_LANG['back_page_up'], '', 'error');
			}

			if (add_booking($booking)) {
				show_message($_LANG['booking_success'], $_LANG['back_booking_list'], 'user.php?act=booking_list', 'info');
			}
			else {
				$err->show($_LANG['booking_list_lnk'], 'user.php?act=booking_list');
			}
		}
		else if ($action == 'act_del_booking') {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			if ($id == 0 || $user_id == 0) {
				ecs_header("Location: user.php?act=booking_list\n");
				exit();
			}

			$result = delete_booking($id, $user_id);

			if ($result) {
				ecs_header("Location: user.php?act=booking_list\n");
				exit();
			}
		}
		else if ($action == 'affirm_received') {
			include_once ROOT_PATH . 'includes/lib_transaction.php';
			$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
			$act = isset($_GET['action']) ? $_GET['action'] : '';

			if (affirm_received($order_id, $user_id)) {
				$sql = 'SELECT goods_id, goods_number FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE order_id =' . $order_id;
				$res = $GLOBALS['db']->getAll($sql);

				foreach ($res as $val) {
					$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' WHERE goods_id=' . $val['goods_id'] . ' GROUP BY user_id';
					$user_number = COUNT($GLOBALS['db']->getAll($sql));
					$num = array('goods_number' => $val['goods_number'], 'goods_id' => $val['goods_id'], 'user_number' => $user_number);
					update_manual($val['goods_id'], $num);
				}

				if ($act == 'auction') {
					ecs_header("Location: user.php?act=auction\n");
				}
				else if ($act == 'crowdfunding') {
					ecs_header("Location: user.php?act=crowdfunding\n");
				}
				else if ($act == 'info') {
					ecs_header('Location: user.php?act=order_detail&order_id=' . $order_id . "\n");
				}
				else {
					ecs_header("Location: user.php?act=order_list\n");
				}

				exit();
			}
			else if ($act == 'auction') {
				$err->show($_LANG['order_list_lnk'], 'user.php?act=auction');
			}
			else if ($act == 'crowdfunding') {
				$err->show($_LANG['order_list_lnk'], 'user.php?act=crowdfunding');
			}
			else {
				$err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
			}
		}
		else if ($action == 'order_delete_return') {
			include_once 'includes/cls_json.php';
			$_POST['order'] = strip_tags(urldecode($_POST['order']));
			$_POST['order'] = json_str_iconv($_POST['order']);
			$result = array('error' => 0, 'content' => '', 'order_id' => '', 'pager' => '');
			$json = new JSON();
			$order = $json->decode($_POST['order']);
			$order_id = $order->order_id;
			$result['order_id'] = $order_id;
			$return_list = return_order();
			$sql = 'DELETE FROM' . $ecs->table('order_return') . (' WHERE user_id = \'' . $user_id . '\' AND ret_id = ') . $result['order_id'];
			$db->query($sql);

			if ($db->query($sql)) {
				$return_list = return_order();
				$smarty->assign('orders', $return_list);
				$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
				$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('order_return') . (' WHERE user_id = \'' . $user_id . '\''));
				$action = 'order_return';
				$result['pager'] = get_pager('user.php', array('act' => $action), $record_count, $page);
				$result['content'] = $smarty->fetch('library/user_return_order_list.lbi');
				exit($json->encode($result));
			}
		}
		else if ($action == 'account_raply') {
			$validate_info = get_validate_info($_SESSION['user_id']);

			if ($validate_info['review_status'] != 1) {
				$Loaction = 'user.php?act=account_safe&type=real_name&step=realname_ok';
				ecs_header('Location: ' . $Loaction . "\n");
				exit();
			}

			if (0 < intval($_CFG['sms_code'])) {
				$sms_security_code = $_SESSION['sms_security_code'] = rand(1000, 9999);
				$smarty->assign('sms_security_code', $sms_security_code);
				$smarty->assign('enabled_sms_signin', 1);
			}

			$sc_rand = rand(1000, 9999);
			$sc_guid = sc_guid();
			$account_cookie = MD5($sc_guid . '-' . $sc_rand);
			setcookie('user_account_cookie', $account_cookie, gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
			$smarty->assign('sc_guid', $sc_guid);
			$smarty->assign('sc_rand', $sc_rand);
			$user_info = get_user_default($_SESSION['user_id']);
			$validate_info = get_validate_info($_SESSION['user_id']);
			$smarty->assign('validate_info', $validate_info);
			$smarty->assign('deposit_fee', $_CFG['deposit_fee']);
			$smarty->assign('buyer_cash', $_CFG['buyer_cash']);
			$smarty->assign('user_info', $user_info);
			$smarty->display('user_transaction.dwt');
		}
		else if ($action == 'account_deposit') {
			include_once ROOT_PATH . 'includes/lib_clips.php';
			$surplus_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$account = get_surplus_info($surplus_id);
			$user_info = get_user_default($_SESSION['user_id']);
			$validate_info = get_validate_info($_SESSION['user_id']);
			$smarty->assign('validate_info', $validate_info);
			$smarty->assign('payment', get_online_payment_list(false));
			$smarty->assign('order', $account);
			$smarty->assign('user_info', $user_info);
			$smarty->assign('buyer_recharge', $_CFG['buyer_recharge']);
			$smarty->display('user_transaction.dwt');
		}
		else {
			if ($action == 'account_detail' || $action == 'account_paypoints' || $action == 'account_rankpoints') {
				include_once ROOT_PATH . 'includes/lib_clips.php';
				$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

				if ($action == 'account_detail') {
					$account_type = 'user_money';
					$size = 15;
				}
				else if ($action == 'account_paypoints') {
					$account_type = 'pay_points';
					$size = 10;
				}
				else if ($action == 'account_rankpoints') {
					$account_type = 'rank_points';
					$size = 10;
				}

				$record_count = get_user_accountlog_count($user_id, $account_type);
				$pager = get_pager('user.php', array('act' => $action), $record_count, $page, $size);
				$sql = 'SELECT user_money FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $user_id . '\'');
				$surplus_amount = $GLOBALS['db']->getOne($sql);

				if (empty($surplus_amount)) {
					$surplus_amount = 0;
				}

				$validate_info = get_validate_info($_SESSION['user_id']);
				$smarty->assign('validate_info', $validate_info);

				if (defined('THEME_EXTENSION')) {
					$info = get_user_default($user_id);
					$smarty->assign('info', $info);
					$sql = ' SELECT COUNT(*) AS num, SUM(c.cou_money) AS money FROM ' . $ecs->table('coupons_user') . ' cu LEFT JOIN ' . $ecs->table('coupons') . ' c ON c.cou_id=cu.cou_id LEFT JOIN ' . $ecs->table('order_info') . (' o ON cu.order_id=o.order_id WHERE cu.user_id = \'' . $user_id . '\' AND cu.is_use = 0 ');
					$cou = $db->getRow($sql);
					$cou['money'] = price_format($cou['money']);
					$smarty->assign('coupons', $cou);
					$sql = ' SELECT COUNT(*) AS num, SUM(card_money) AS money FROM ' . $ecs->table('value_card') . (' WHERE user_id = \'' . $user_id . '\' ');
					$vc = $db->getRow($sql);
					$vc['money'] = price_format($vc['money']);
					$smarty->assign('value_card', $vc);
				}

				$account_log = get_user_accountlog_list($user_id, $account_type, $pager);
				$smarty->assign('surplus_amount', price_format($surplus_amount, false));
				$smarty->assign('account_log', $account_log);
				$smarty->assign('pager', $pager);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'account_log') {
				include_once ROOT_PATH . 'includes/lib_clips.php';
				$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
				$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('user_account') . (' WHERE user_id = \'' . $user_id . '\'') . ' AND process_type ' . db_create_in(array(SURPLUS_SAVE, SURPLUS_RETURN));
				$record_count = $db->getOne($sql);
				$pager = get_pager('user.php', array('act' => $action), $record_count, $page);
				$sql = 'SELECT user_money FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $user_id . '\'');
				$surplus_amount = $GLOBALS['db']->getOne($sql);

				if (empty($surplus_amount)) {
					$surplus_amount = 0;
				}

				$account_log = get_account_log($user_id, $pager['size'], $pager['start']);

				if (defined('THEME_EXTENSION')) {
					$info = get_user_default($user_id);
					$smarty->assign('info', $info);
					$sql = ' SELECT COUNT(*) AS num, SUM(c.cou_money) AS money FROM ' . $ecs->table('coupons_user') . ' cu LEFT JOIN ' . $ecs->table('coupons') . ' c ON c.cou_id=cu.cou_id LEFT JOIN ' . $ecs->table('order_info') . (' o ON cu.order_id=o.order_id WHERE cu.user_id = \'' . $user_id . '\' AND cu.is_use = 0 ');
					$cou = $db->getRow($sql);
					$cou['money'] = price_format($cou['money']);
					$smarty->assign('coupons', $cou);
					$sql = ' SELECT COUNT(*) AS num, SUM(card_money) AS money FROM ' . $ecs->table('value_card') . (' WHERE user_id = \'' . $user_id . '\' ');
					$vc = $db->getRow($sql);
					$vc['money'] = price_format($vc['money']);
					$smarty->assign('value_card', $vc);
				}

				$validate_info = get_validate_info($_SESSION['user_id']);

				if ($validate_info['review_status'] != 1) {
					$validate_info['real_name'] = '';
				}

				$smarty->assign('validate_info', $validate_info);
				$smarty->assign('surplus_amount', price_format($surplus_amount, false));
				$smarty->assign('account_log', $account_log);
				$smarty->assign('pager', $pager);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'account_complaint') {
				$id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
				include_once ROOT_PATH . 'includes/lib_clips.php';
				$user_account = get_user_account_log($id);
				if (empty($id) || empty($user_account) || $user_account['is_paid'] == 1) {
					$Loaction = 'user.php?act=account_log';
					ecs_header('Location: ' . $Loaction . "\n");
					exit();
				}

				$smarty->assign('user_account', $user_account);
				$smarty->assign('operate', 'account_complaint_insert');
				$smarty->assign('id', $id);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'account_complaint_insert') {
				$id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
				$complaint_imges = $image->upload_image($_FILES['complaint_imges'], 'complaint_imges');
				$complaint_details = isset($_REQUEST['complaint_details']) && !empty($_REQUEST['complaint_details']) ? dsc_addslashes(trim($_REQUEST['complaint_details'])) : '';
				$other = array('complaint_imges' => $complaint_imges, 'complaint_details' => $complaint_details, 'complaint_time' => gmtime());
				$db->autoExecute($ecs->table('user_account'), $other, 'UPDATE', 'id = \'' . $id . '\' AND user_id = \'' . $user_id . '\'');
				show_message($_LANG['complaint_success'], $_LANG['back_up_page'], 'user.php?act=account_log', 'info');
			}
			else if ($action == 'act_account') {
				include_once ROOT_PATH . 'includes/lib_clips.php';
				include_once ROOT_PATH . 'includes/lib_order.php';
				$_POST = get_request_filter($_POST, 1);
				$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

				if ($amount <= 0) {
					show_message($_LANG['amount_gt_zero']);
				}

				$surplus = array('user_id' => $user_id, 'rec_id' => !empty($_POST['rec_id']) ? intval($_POST['rec_id']) : 0, 'process_type' => isset($_POST['surplus_type']) ? intval($_POST['surplus_type']) : 0, 'payment_id' => isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0, 'user_note' => isset($_POST['user_note']) ? trim($_POST['user_note']) : '', 'amount' => $amount);

				if ($surplus['process_type'] == 1) {
					$buyer_cash = !empty($_CFG['buyer_cash']) ? intval($_CFG['buyer_cash']) : 0;

					if ($amount < $buyer_cash) {
						show_message('会员提现金额不得少于' . $buyer_cash . '元');
					}

					$deposit_fee = !empty($_CFG['deposit_fee']) ? intval($_CFG['deposit_fee']) : 0;
					$deposit_money = 0;

					if (0 < $deposit_fee) {
						$deposit_money = $amount * $deposit_fee / 100;
					}

					if (isset($_POST['mobile_code']) && !empty($_POST['mobile_code'])) {
						if ($_POST['mobile_phone'] != $_SESSION['sms_mobile'] || $_POST['mobile_code'] != $_SESSION['sms_mobile_code']) {
							show_message($_LANG['Mobile_code_fail'], $_LANG['back_input_Code'], '', 'error');
						}
					}

					$sur_amount = get_user_surplus($user_id);

					if ($sur_amount < $amount - $deposit_money) {
						$content = $_LANG['surplus_amount_error'];
						show_message($content, $_LANG['back_page_up'], '', 'info');
					}

					if ($sur_amount < $amount + $deposit_money) {
						$amount = $amount - $deposit_money;
					}

					$surplus['deposit_fee'] = '-' . $deposit_money;
					$frozen_money = $amount + $deposit_money;
					$amount = '-' . $amount;
					$surplus['payment'] = '';
					$surplus['rec_id'] = insert_user_account($surplus, $amount);

					if (0 < $surplus['rec_id']) {
						$sc_rand = isset($_POST['sc_rand']) && !empty($_POST['sc_rand']) ? addslashes(trim($_POST['sc_rand'])) : '';
						$sc_guid = isset($_POST['sc_guid']) && !empty($_POST['sc_guid']) ? addslashes(trim($_POST['sc_guid'])) : '';
						$user_account_cookie = MD5($sc_guid . '-' . $sc_rand);
						if (!empty($sc_guid) && !empty($sc_rand) && isset($_COOKIE['user_account_cookie'])) {
							$is_ok = 1;

							if (!empty($_COOKIE['user_account_cookie'])) {
								if (!($_COOKIE['user_account_cookie'] == $user_account_cookie)) {
									$is_ok = 0;
								}
							}
							else {
								$is_ok = 0;
							}

							if ($is_ok == 1) {
								$user_account_fields = array('user_id' => $surplus['user_id'], 'account_id' => $surplus['rec_id'], 'bank_number' => !empty($_POST['bank_number']) ? trim($_POST['bank_number']) : '', 'real_name' => !empty($_POST['real_name']) ? trim($_POST['real_name']) : '');
								insert_user_account_fields($user_account_fields);
								log_account_change($user_id, $amount, $frozen_money, 0, 0, '【' . $_LANG['Application_withdrawal'] . '】' . $surplus['user_note'], ACT_ADJUSTING, 0, $surplus['deposit_fee']);
								setcookie('user_account_cookie', '', gmtime() + 3600 * 24 * 30, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
								$content = $_LANG['surplus_appl_submit'];
								show_message($content, $_LANG['back_account_log'], 'user.php?act=account_log', 'info');
							}
						}
					}
					else {
						$content = $_LANG['process_false'];
						show_message($content, $_LANG['back_page_up'], '', 'info');
					}
				}
				else {
					$buyer_recharge = !empty($_CFG['buyer_recharge']) ? intval($_CFG['buyer_recharge']) : 0;

					if ($amount < $buyer_recharge) {
						show_message('会员充值金额不得少于' . $buyer_cash . '元');
					}

					if ($surplus['payment_id'] <= 0) {
						show_message($_LANG['select_payment_pls']);
					}

					include_once ROOT_PATH . 'includes/lib_payment.php';
					$payment_info = array();
					$payment_info = payment_info($surplus['payment_id']);
					$surplus['payment'] = $payment_info['pay_name'];

					if (0 < $surplus['rec_id']) {
						$surplus['rec_id'] = update_user_account($surplus);
					}
					else {
						$surplus['rec_id'] = insert_user_account($surplus, $amount);
					}

					$payment = unserialize_config($payment_info['pay_config']);
					$order = array();
					$order['order_sn'] = $surplus['rec_id'];
					$order['user_name'] = $_SESSION['user_name'];
					$order['surplus_amount'] = $amount;
					$payment_info['pay_fee'] = pay_fee($surplus['payment_id'], $order['surplus_amount'], 0);
					$order['order_amount'] = $amount + $payment_info['pay_fee'];
					$order['log_id'] = insert_pay_log($surplus['rec_id'], $order['order_amount'], PAY_SURPLUS, 0);
					include_once ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php';
					$pay_obj = new $payment_info['pay_code']();
					$payment_info['pay_button'] = $pay_obj->get_code($order, $payment);
					$smarty->assign('payment', $payment_info);
					$smarty->assign('pay_fee', price_format($payment_info['pay_fee'], false));
					$smarty->assign('amount', price_format($amount, false));
					$smarty->assign('order', $order);
					$smarty->display('user_transaction.dwt');
				}
			}
			else if ($action == 'cancel') {
				include_once ROOT_PATH . 'includes/lib_clips.php';
				$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
				if ($id == 0 || $user_id == 0) {
					ecs_header("Location: user.php?act=account_log\n");
					exit();
				}

				$result = del_user_account($id, $user_id);

				if ($result) {
					del_user_account_fields($id, $user_id);
					ecs_header("Location: user.php?act=account_log\n");
					exit();
				}
			}
			else if ($action == 'pay') {
				include_once ROOT_PATH . 'includes/lib_clips.php';
				include_once ROOT_PATH . 'includes/lib_payment.php';
				include_once ROOT_PATH . 'includes/lib_order.php';
				$surplus_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
				$payment_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

				if ($surplus_id == 0) {
					ecs_header("Location: user.php?act=account_log\n");
					exit();
				}

				if ($payment_id == 0) {
					ecs_header('Location: user.php?act=account_deposit&id=' . $surplus_id . "\n");
					exit();
				}

				$order = array();
				$order = get_surplus_info($surplus_id);
				$payment_info = array();
				$payment_info = payment_info($payment_id);

				if (!empty($payment_info)) {
					$payment = unserialize_config($payment_info['pay_config']);
					$order['order_sn'] = $surplus_id;
					$order['log_id'] = get_paylog_id($surplus_id, $pay_type = PAY_SURPLUS);
					$order['user_name'] = $_SESSION['user_name'];
					$order['surplus_amount'] = $order['amount'];
					$payment_info['pay_fee'] = pay_fee($payment_id, $order['surplus_amount'], 0);
					$order['order_amount'] = $order['surplus_amount'] + $payment_info['pay_fee'];
					$order_amount = $db->getOne('SELECT order_amount FROM ' . $ecs->table('pay_log') . (' WHERE log_id = \'' . $order['log_id'] . '\''));

					if ($order_amount != $order['order_amount']) {
						$db->query('UPDATE ' . $ecs->table('pay_log') . (' SET order_amount = \'' . $order['order_amount'] . '\' WHERE log_id = \'' . $order['log_id'] . '\''));
					}

					include_once ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php';
					$pay_obj = new $payment_info['pay_code']();
					$payment_info['pay_button'] = $pay_obj->get_code($order, $payment);
					$smarty->assign('payment', $payment_info);
					$smarty->assign('order', $order);
					$smarty->assign('pay_fee', price_format($payment_info['pay_fee'], false));
					$smarty->assign('amount', price_format($order['surplus_amount'], false));
					$smarty->assign('action', 'act_account');
					$smarty->display('user_transaction.dwt');
				}
				else {
					include_once ROOT_PATH . 'includes/lib_clips.php';
					$smarty->assign('payment', get_online_payment_list());
					$smarty->assign('order', $order);
					$smarty->assign('action', 'account_deposit');
					$smarty->display('user_transaction.dwt');
				}
			}
			else if ($action == 'add_tag') {
				include_once 'includes/cls_json.php';
				include_once 'includes/lib_clips.php';
				$_POST = get_request_filter($_POST, 1);
				$result = array('error' => 0, 'message' => '', 'content' => '');
				$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
				$tag = isset($_POST['tag']) ? json_str_iconv(trim($_POST['tag'])) : '';

				if ($user_id == 0) {
					$result['error'] = 1;
					$result['message'] = $_LANG['tag_anonymous'];
				}
				else {
					add_tag($id, $tag);
					clear_cache_files('goods');
					$arr = get_tags($id);

					foreach ($arr as $row) {
						$result['content'][] = array('word' => htmlspecialchars($row['tag_words']), 'count' => $row['tag_count']);
					}
				}

				$json = new JSON();
				echo $json->encode($result);
				exit();
			}
			else if ($action == 'collect') {
				include_once ROOT_PATH . 'includes/cls_json.php';
				$json = new JSON();
				$result = array('error' => 0, 'message' => '', 'url' => '');
				$_GET = get_request_filter($_GET, 2);
				$goods_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
				$cat_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
				$merchant_id = isset($_GET['merchant_id']) ? intval($_GET['merchant_id']) : 0;
				$script_name = isset($_GET['script_name']) ? htmlspecialchars(trim($_GET['script_name'])) : '';
				$keywords = isset($_GET['keywords']) ? htmlspecialchars(trim($_GET['keywords'])) : '';
				$cur_url = isset($_GET['cur_url']) ? htmlspecialchars(trim($_GET['cur_url'])) : '';
				if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
					if ($script_name != '') {
						if ($script_name == 'category') {
							$result['url'] = get_return_category_url($cat_id);
						}
						else {
							if ($script_name == 'search' || $script_name == 'merchants_shop') {
								$result['url'] = $cur_url;
							}
							else if ($script_name == 'merchants_store_shop') {
								$result['url'] = get_return_store_shop_url($merchant_id);
							}
						}
					}

					$result['goods_url'] = build_uri('goods', array('gid' => $goods_id), $script_name);
					$result['error'] = 2;
					$result['message'] = $_LANG['login_please'];
					exit($json->encode($result));
				}
				else {
					$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('collect_goods') . (' WHERE user_id=\'' . $_SESSION['user_id'] . '\' AND goods_id = \'' . $goods_id . '\'');

					if (0 < $GLOBALS['db']->GetOne($sql)) {
						$result['error'] = 1;
						$result['message'] = $GLOBALS['_LANG']['collect_existed'];
						exit($json->encode($result));
					}
					else {
						$time = gmtime();
						$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('collect_goods') . ' (user_id, goods_id, add_time)' . ('VALUES (\'' . $_SESSION['user_id'] . '\', \'' . $goods_id . '\', \'' . $time . '\')');

						if ($GLOBALS['db']->query($sql) === false) {
							$result['error'] = 1;
							$result['message'] = $GLOBALS['db']->errorMsg();
							exit($json->encode($result));
						}
						else {
							$collect_count = get_collect_goods_user_count($goods_id);
							$result['collect_count'] = $collect_count;
							clear_all_files();
							$result['error'] = 0;
							$result['message'] = $GLOBALS['_LANG']['collect_success'];
							exit($json->encode($result));
						}
					}
				}
			}
			else if ($action == 'del_msg') {
				$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
				$is_order = isset($_GET['is_order']) ? intval($_GET['is_order']) : 0;
				$order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);

				if (0 < $id) {
					$sql = 'SELECT user_id, message_img FROM ' . $ecs->table('feedback') . (' WHERE msg_id = \'' . $id . '\' LIMIT 1');
					$row = $db->getRow($sql);
					if ($row && $row['user_id'] == $user_id) {
						if ($row['message_img']) {
							@unlink(ROOT_PATH . DATA_DIR . '/feedbackimg/' . $row['message_img']);
						}

						$sql = 'DELETE FROM ' . $ecs->table('feedback') . (' WHERE msg_id = \'' . $id . '\' OR parent_id = \'' . $id . '\'');
						$db->query($sql);
					}
				}

				if ($is_order) {
					ecs_header('Location: user.php?act=message_list&is_order=1&order_id=' . $order_id . "\n");
				}
				else {
					ecs_header('Location: user.php?act=message_list&order_id=' . $order_id . "\n");
				}

				exit();
			}
			else if ($action == 'del_cmt') {
				$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

				if (0 < $id) {
					$sql = 'DELETE FROM ' . $ecs->table('comment') . (' WHERE comment_id = \'' . $id . '\' AND user_id = \'' . $user_id . '\'');
					$db->query($sql);
				}

				ecs_header("Location: user.php?act=comment_list\n");
				exit();
			}
			else if ($action == 'merge_order') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				include_once ROOT_PATH . 'includes/lib_order.php';
				$_POST = get_request_filter($_POST, 1);
				$from_order = isset($_POST['from_order']) ? trim($_POST['from_order']) : '';
				$to_order = isset($_POST['to_order']) ? trim($_POST['to_order']) : '';

				if (merge_user_order($from_order, $to_order, $user_id)) {
					show_message($_LANG['merge_order_success'], $_LANG['order_list_lnk'], 'user.php?act=order_list', 'info');
				}
				else {
					$err->show($_LANG['order_list_lnk']);
				}
			}
			else if ($action == 'return_to_cart') {
				include_once ROOT_PATH . 'includes/cls_json.php';
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				$json = new JSON();
				$_POST = get_request_filter($_POST, 1);
				$result = array('error' => 0, 'message' => '', 'cart_info' => '');
				$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
				$recStr = isset($_POST['rec_id']) ? trim($_POST['rec_id']) : '';
				$rec_id = array();

				if ($order_id == 0) {
					$result['error'] = 1;
					$result['message'] = $_LANG['order_id_empty'];
					exit($json->encode($result));
				}

				if ($user_id == 0) {
					$result['error'] = 1;
					$result['message'] = $_LANG['login_please'];
					exit($json->encode($result));
				}

				if (!empty($recStr)) {
					$rec_id = explode(',', $recStr);
				}

				$order_user = $db->getOne('SELECT user_id FROM ' . $ecs->table('order_info') . (' WHERE order_id = \'' . $order_id . '\''));

				if (empty($order_user)) {
					$result['error'] = 1;
					$result['message'] = $_LANG['order_exist'];
					exit($json->encode($result));
				}
				else if ($order_user != $user_id) {
					$result['error'] = 1;
					$result['message'] = $_LANG['no_priv'];
					exit($json->encode($result));
				}

				$message = return_to_cart($order_id, $rec_id);
				$cart_info = get_cart_info();
				$result['cart_info'] = $cart_info;

				if ($message === true) {
					$result['error'] = 0;
					$result['message'] = $_LANG['return_to_cart_success'];
					exit($json->encode($result));
				}
				else {
					$result['error'] = 1;
					$result['message'] = $_LANG['order_exist'];
					exit($json->encode($result));
				}
			}
			else if ($action == 'act_edit_surplus') {
				$_POST = get_request_filter($_POST, 1);

				if ($_SESSION['user_id'] <= 0) {
					ecs_header("Location: ./\n");
					exit();
				}

				$order_id = intval($_POST['order_id']);

				if ($order_id <= 0) {
					ecs_header("Location: ./\n");
					exit();
				}

				$surplus = floatval($_POST['surplus']);

				if ($surplus <= 0) {
					$err->add($_LANG['error_surplus_invalid']);
					$err->show($_LANG['order_detail'], 'user.php?act=order_detail&order_id=' . $order_id);
				}

				include_once ROOT_PATH . 'includes/lib_order.php';
				$order = order_info($order_id);

				if (empty($order)) {
					ecs_header("Location: ./\n");
					exit();
				}

				if ($_POST['pay_status'] == 'presale' && $order['pay_status'] == PS_PAYED_PART) {
					$result = presale_settle_status($order['extendsion_id']);

					if ($result['settle_status'] == false) {
						ecs_header("Location: ./\n");
						exit();
					}
				}

				if ($_SESSION['user_id'] != $order['user_id']) {
					ecs_header("Location: ./\n");
					exit();
				}

				if ($order['pay_status'] != PS_UNPAYED || $order['order_amount'] <= 0) {
					if ($order['pay_status'] != PS_PAYED_PART) {
						$err->add($_LANG['error_order_is_paid']);
						$err->show($_LANG['order_detail'], 'user.php?act=order_detail&order_id=' . $order_id);
					}
				}

				$order['order_amount'] -= $order['pay_fee'];

				if ($order['order_amount'] < $surplus) {
					$surplus = $order['order_amount'];
				}

				$user = user_info($_SESSION['user_id']);

				if ($user['user_money'] + $user['credit_line'] < $surplus) {
					$err->add($_LANG['error_surplus_not_enough']);
					$err->show($_LANG['order_detail'], 'user.php?act=order_detail&order_id=' . $order_id);
				}

				$order['surplus'] += $surplus;
				$order['order_amount'] -= $surplus;

				if (0 < $order['order_amount']) {
					$cod_fee = 0;

					if (0 < $order['shipping_id']) {
						$regions = array($order['country'], $order['province'], $order['city'], $order['district']);
						$shipping = shipping_info($order['shipping_id']);
						if (isset($shipping['support_cod']) && $shipping['support_cod'] == '1') {
							$cod_fee = isset($shipping['pay_fee']) ? $shipping['pay_fee'] : 0;
						}
					}

					$pay_fee = 0;

					if (0 < $order['pay_id']) {
						$pay_fee = pay_fee($order['pay_id'], $order['order_amount'], $cod_fee);
					}

					$order['pay_fee'] = $pay_fee;
					$order['order_amount'] += $pay_fee;
					$new_order_sn = correct_order_sn($order['order_sn']);
					$order['order_sn'] = $new_order_sn;
				}

				$stores_sms = 0;

				if ($order['order_amount'] == 0) {
					$amount = $order['goods_amount'] + $order['shipping_fee'];
					$paid = $order['money_paid'] + $order['surplus'];
					if ($_POST['pay_status'] == 'presale' && $paid < $amount) {
						$order['pay_status'] = PS_PAYED_PART;
						$order['order_amount'] = $amount - $paid;
					}
					else {
						$order['pay_status'] = PS_PAYED;
						$stores_sms = 1;
					}

					if ($order['order_status'] == OS_UNCONFIRMED) {
						$order['order_status'] = OS_CONFIRMED;
						$order['confirm_time'] = gmtime();
					}

					$order['pay_time'] = gmtime();

					if ($_CFG['sales_volume_time'] == SALES_PAY) {
						$order['is_update_sale'] = 1;
					}

					$is_update_sale = is_update_sale($order_id);
					if ($_CFG['sales_volume_time'] == SALES_PAY && $is_update_sale == 0) {
						get_goods_sale($order_id);
					}

					update_zc_project($order_id);
					create_snapshot($order_id);
					$sql = 'SELECT store_id FROM ' . $GLOBALS['ecs']->table('store_order') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
					$store_id = $GLOBALS['db']->getOne($sql);
					if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PAID && $order['order_amount'] <= 0) {
						change_order_goods_storage($order_id, true, SDT_PAID, $_CFG['stock_dec_time'], 0, $store_id);
					}

					get_presale_num($order_id);
				}

				$payment = payment_info('balance', 1);
				$order['pay_id'] = $payment['pay_id'];
				$order['pay_name'] = $payment['pay_name'];
				$order = addslashes_deep($order);
				update_order($order_id, $order);
				check_main_order_status($order_id);
				$store_result = array();

				if ($stores_sms == 1) {
					$sql = 'SELECT id, store_id, order_id FROM ' . $GLOBALS['ecs']->table('store_order') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
					$stores_order = $GLOBALS['db']->getRow($sql);
					if ($stores_order && 0 < $stores_order['store_id']) {
						$sql = 'SELECT mobile_phone, user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $order['user_id'] . '\' LIMIT 1';
						$orderUsers = $GLOBALS['db']->getRow($sql);

						if ($order['mobile']) {
							$user_mobile_phone = $order['mobile'];
						}
						else {
							$user_mobile_phone = $orderUsers['mobile_phone'];
						}

						if (!empty($user_mobile_phone)) {
							$pick_code = substr($order['order_sn'], -3) . rand(0, 9) . rand(0, 9) . rand(0, 9);
							$sql = 'UPDATE ' . $GLOBALS['ecs']->table('store_order') . (' SET pick_code = \'' . $pick_code . '\' WHERE id = \'') . $stores_order['id'] . '\'';
							$db->query($sql);
							$sql = 'SELECT id, country, province, city, district, stores_address, stores_name, stores_tel FROM ' . $GLOBALS['ecs']->table('offline_store') . ' WHERE id = \'' . $stores_order['store_id'] . '\' LIMIT 1';
							$stores_info = $GLOBALS['db']->getRow($sql);
							$store_address = get_area_region_info($stores_info) . $stores_info['stores_address'];
							$user_name = !empty($orderUsers['user_name']) ? $orderUsers['user_name'] : '';
							$store_smsParams = array('user_name' => $user_name, 'order_sn' => $order['order_sn'], 'code' => $pick_code, 'store_address' => $store_address, 'mobile_phone' => $user_mobile_phone);

							if ($GLOBALS['_CFG']['sms_type'] == 0) {
								if (0 < $stores_order['store_id'] && !empty($store_smsParams)) {
									$resp = huyi_sms($store_smsParams, 'store_order_code');
								}
							}
							else if (1 <= $GLOBALS['_CFG']['sms_type']) {
								if (0 < $stores_order['store_id'] && !empty($store_smsParams)) {
									$store_result = sms_ali($store_smsParams, 'store_order_code');
								}
							}
						}
					}
				}

				$sql = 'SELECT ru_id, stages_qishu FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
				$order_goods = $GLOBALS['db']->getRow($sql);
				$ru_id = $order_goods['ru_id'];
				$stages_qishu = $order_goods['stages_qishu'];
				$shop_name = get_shop_name($ru_id, 1);

				if ($ru_id == 0) {
					$sms_shop_mobile = $GLOBALS['_CFG']['sms_shop_mobile'];
				}
				else {
					$sql = 'SELECT mobile FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' WHERE ru_id = \'' . $ru_id . '\'');
					$sms_shop_mobile = $GLOBALS['db']->getOne($sql, true);
				}

				$order_result = array();
				if ($GLOBALS['_CFG']['sms_order_payed'] == '1' && $sms_shop_mobile != '') {
					$order_region = get_flow_user_region($order_id);
					$smsParams = array('shop_name' => $shop_name, 'shopname' => $shop_name, 'order_sn' => $order['order_sn'], 'ordersn' => $order['order_sn'], 'consignee' => $order['consignee'], 'order_region' => $order_region, 'orderregion' => $order_region, 'address' => $order['address'], 'order_mobile' => $order['mobile'], 'ordermobile' => $order['mobile'], 'mobile_phone' => $sms_shop_mobile, 'mobilephone' => $sms_shop_mobile);

					if ($GLOBALS['_CFG']['sms_type'] == 0) {
						huyi_sms($smsParams, 'sms_order_payed');
					}
					else if (1 <= $GLOBALS['_CFG']['sms_type']) {
						$order_result = sms_ali($smsParams, 'sms_order_payed');
					}
				}

				if (1 <= $GLOBALS['_CFG']['sms_type']) {
					$sms_send = array($store_result, $order_result);
					$resp = $GLOBALS['ecs']->ali_yu($sms_send, 1);
				}

				if (0 < $stages_qishu) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_goods') . (' SET stages_qishu = \'-1\' WHERE order_id = \'' . $order_id . '\'');
					$GLOBALS['db']->query($sql);
				}

				$change_desc = sprintf($_LANG['pay_order_by_surplus'], $order['order_sn']);
				log_account_change($user['user_id'], -1 * $surplus, 0, 0, 0, $change_desc);
				order_action($order['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $GLOBALS['_LANG']['user_surplus_pay'], $GLOBALS['_LANG']['buyer']);

				if ($_POST['pay_status'] == 'auction') {
					ecs_header('Location: user.php?act=auction_order_detail&order_id=' . $order_id . "\n");
				}
				else {
					ecs_header('Location: user.php?act=order_detail&order_id=' . $order_id . "\n");
				}

				exit();
			}
			else if ($action == 'act_edit_payment') {
				$_POST = get_request_filter($_POST, 1);

				if ($_SESSION['user_id'] <= 0) {
					ecs_header("Location: ./\n");
					exit();
				}

				$pay_id = intval($_POST['pay_id']);

				if ($pay_id <= 0) {
					ecs_header("Location: ./\n");
					exit();
				}

				include_once ROOT_PATH . 'includes/lib_order.php';
				$payment_info = payment_info($pay_id);

				if (empty($payment_info)) {
					ecs_header("Location: ./\n");
					exit();
				}

				$order_id = intval($_POST['order_id']);

				if ($order_id <= 0) {
					ecs_header("Location: ./\n");
					exit();
				}

				$order = order_info($order_id);

				if (empty($order)) {
					ecs_header("Location: ./\n");
					exit();
				}

				if ($_SESSION['user_id'] != $order['user_id']) {
					ecs_header("Location: ./\n");
					exit();
				}

				if ($order['pay_status'] != PS_UNPAYED && $order['pay_status'] != PS_PAYED_PART || $order['shipping_status'] != SS_UNSHIPPED || $order['order_amount'] <= 0 || $order['pay_id'] == $pay_id) {
					ecs_header('Location: user.php?act=order_detail&order_id=' . $order_id . "\n");
					exit();
				}

				$order_amount = $order['order_amount'] - $order['pay_fee'];
				$pay_fee = pay_fee($pay_id, $order_amount);
				$order_amount += $pay_fee;
				$sql = 'UPDATE ' . $ecs->table('order_info') . (' SET pay_id=\'' . $pay_id . '\', pay_name=\'' . $payment_info['pay_name'] . '\', pay_fee=\'' . $pay_fee . '\', order_amount=\'' . $order_amount . '\'') . (' WHERE order_id = \'' . $order_id . '\'');
				$db->query($sql);
				ecs_header('Location: user.php?act=order_detail&order_id=' . $order_id . "\n");
				exit();
			}
			else if ($action == 'save_order_address') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				$_POST = get_request_filter($_POST, 1);
				$time = gmtime();
				$address = array('consignee' => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee'])) : '', 'email' => isset($_POST['email']) ? compile_str(trim($_POST['email'])) : '', 'address' => isset($_POST['address']) ? compile_str(trim($_POST['address'])) : '', 'zipcode' => isset($_POST['zipcode']) ? compile_str(make_semiangle(trim($_POST['zipcode']))) : '', 'tel' => isset($_POST['tel']) ? compile_str(trim($_POST['tel'])) : '', 'mobile' => isset($_POST['mobile']) ? compile_str(trim($_POST['mobile'])) : '', 'sign_building' => isset($_POST['sign_building']) ? compile_str(trim($_POST['sign_building'])) : '', 'best_time' => isset($_POST['best_time']) ? compile_str(trim($_POST['best_time'])) : '', 'userUp_time' => $time, 'order_id' => isset($_POST['order_id']) ? intval($_POST['order_id']) : 0);

				if (save_order_address($address, $user_id)) {
					ecs_header('Location: user.php?act=order_detail&order_id=' . $address['order_id'] . "\n");
					exit();
				}
				else {
					$err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
				}
			}
			else if ($action == 'bonus') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
				$size = 10;
				$bonus = get_user_bouns_new_list($user_id, $page, 0, 'bouns_available_gotoPage', 0, $size);
				$smarty->assign('bonus', $bonus);
				$bonus1 = get_user_bouns_new_list($user_id, $page, 1, 'bouns_expire_gotoPage', 0, $size);
				$smarty->assign('bonus1', $bonus1);
				$bonus2 = get_user_bouns_new_list($user_id, $page, 2, 'bouns_useup_gotoPage', 0, $size);
				$smarty->assign('bonus2', $bonus2);
				$smarty->assign('size', $size);
				$bonus3 = get_user_bouns_new_list($user_id, $page, 0, '', 1);
				$bouns_amount = get_bouns_amount_list($bonus3);
				$smarty->assign('bouns_amount', $bouns_amount);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'coupons') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				$coupons_list = get_user_coupons_list($user_id, true);

				foreach ($coupons_list as $k => $v) {
					$v['cou_start_time_date'] = local_date('Y-m-d', $v['cou_start_time']);
					$v['cou_end_time_date'] = local_date('Y-m-d', $v['cou_end_time']);
					$v['add_time'] = local_date('Y-m-d', $v['add_time']);

					if (!empty($v['cou_goods'])) {
						$v['goods_list'] = $db->getAll('SELECT goods_name FROM' . $ecs->table('goods') . ' WHERE goods_id IN (' . $v['cou_goods'] . ')');
					}

					if ($v['ru_id']) {
						$v['store_name'] = sprintf($GLOBALS['_LANG']['use_limit'], get_shop_name($v['ru_id'], 1));
					}

					$v['cou_type_name'] = $v['cou_type'] == 1 ? $_LANG['vouchers_login'] : ($v['cou_type'] == 2 ? $_LANG['vouchers_shoping'] : ($v['cou_type'] == 3 ? $_LANG['vouchers_all'] : ($v['cou_type'] == 4 ? $_LANG['vouchers_user'] : ($v['cou_type'] == 5 ? $_LANG['vouchers_free'] : $_LANG['unknown']))));

					if ($v['spec_cat']) {
						$v['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_cate'];
					}
					else if ($v['cou_goods']) {
						$v['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_goods'];
					}
					else {
						$v['cou_goods_name'] = $_LANG['lang_goods_coupons']['is_all'];
					}

					if ($v['is_use'] == 0 && gmtime() < $v['cou_end_time']) {
						$no_use[] = $v;
					}

					if ($v['is_use'] == 1) {
						$yes_use[] = $v;
					}

					if ($v['cou_end_time'] < gmtime() && $v['is_use'] == 0) {
						$yes_time[] = $v;
					}

					$three_date = gmtime() + 3600 * 24 * 3;
					if ($v['cou_end_time'] < $three_date && gmtime() < $v['cou_end_time'] && $v['is_use'] == 0) {
						$no_time[] = $v;
					}
				}

				$smarty->assign('no_use', $no_use);
				$smarty->assign('yes_use', $yes_use);
				$smarty->assign('yes_time', $yes_time);
				$smarty->assign('no_time', $no_time);
				$smarty->assign('no_use_count', count($no_use));
				$smarty->assign('yes_use_count', count($yes_use));
				$smarty->assign('yes_time_count', count($yes_time));
				$smarty->assign('no_time_count', count($no_time));
				$smarty->assign('action', $action);
				$smarty->assign('page_title', '用户中心_优惠券列表');
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'value_card') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
				$bind_vc = get_user_bind_vc_list($user_id, $page, 0, '', 1);
				$sql = ' SELECT COUNT(*) FROM ' . $ecs->table('value_card') . (' WHERE user_id = \'' . $_SESSION['user_id'] . '\' ');
				$amount = $db->getOne($sql);
				$smarty->assign('amount', $amount);
				$smarty->assign('no_use', 1);
				$smarty->assign('bind_vc', $bind_vc);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'verify_mobilecode') {
				if (!empty($_REQUEST['mobile_code'])) {
					include_once ROOT_PATH . 'includes/cls_json.php';
					include_once ROOT_PATH . 'includes/lib_passport.php';
					$json = new JSON();
					$result = array('error' => 0, 'message' => '');

					if ($_REQUEST['mobile_code'] != $_SESSION['sms_mobile_code']) {
						$result['error'] = 1;
						$result['message'] = $_LANG['user_one_code'];
					}

					if (empty($result['message']) && empty($_REQUEST['error'])) {
						$sql = ' UPDATE ' . $ecs->table('value_card') . (' SET user_id = 0, bind_time = 0 WHERE vid = \'' . $_REQUEST['vid'] . '\' ');
						$db->query($sql);
					}

					exit(json_encode($result));
				}
			}
			else if ($action == 'value_card_info') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				$vid = $_REQUEST['vid'] ? intval($_REQUEST['vid']) : 0;
				$explain_info = get_explain($vid);
				$explain = $explain_info['explain'];

				if (is_array($explain)) {
					$smarty->assign('explain', $explain['explain']);
					$smarty->assign('goods_ids', $explain['goods_ids']);
				}
				else {
					$smarty->assign('explain', $explain);
				}

				$value_card_info = value_card_use_info($vid);
				$smarty->assign('value_card_info', $value_card_info);
				$smarty->assign('rz_shopNames', $explain_info['rz_shopNames']);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'group_buy') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'group_buy_detail') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'affiliate') {
				$goodsid = isset($_REQUEST['goodsid']) && !empty($_REQUEST['goodsid']) ? intval($_REQUEST['goodsid']) : 0;

				if (empty($goodsid)) {
					$page = !empty($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
					$size = !empty($_CFG['page_size']) && 0 < intval($_CFG['page_size']) ? intval($_CFG['page_size']) : 10;
					empty($affiliate) && ($affiliate = array());
					$where = ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
					$where .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og WHERE og.order_id = o.order_id LIMIT 1) = 0';

					if (empty($affiliate['config']['separate_by'])) {
						$affdb = array();
						$num = count($affiliate['item']);
						$up_uid = '\'' . $user_id . '\'';
						$all_uid = '\'' . $user_id . '\'';

						for ($i = 1; $i <= $num; $i++) {
							$count = 0;

							if ($up_uid) {
								$sql = 'SELECT user_id FROM ' . $ecs->table('users') . (' WHERE parent_id IN(' . $up_uid . ')');
								$query = $db->query($sql);
								$up_uid = '';

								while ($rt = $db->fetch_array($query)) {
									$up_uid .= $up_uid ? ',\'' . $rt['user_id'] . '\'' : '\'' . $rt['user_id'] . '\'';

									if ($i < $num) {
										$all_uid .= ', \'' . $rt['user_id'] . '\'';
									}

									$count++;
								}
							}

							$affdb[$i]['num'] = $count;
							$affdb[$i]['point'] = $affiliate['item'][$i - 1]['level_point'];
							$affdb[$i]['money'] = $affiliate['item'][$i - 1]['level_money'];
						}

						$smarty->assign('affdb', $affdb);
						$sqlcount = 'SELECT count(*) FROM ' . $ecs->table('order_info') . ' o' . ' LEFT JOIN' . $ecs->table('users') . ' u ON o.user_id = u.user_id' . ' LEFT JOIN ' . $ecs->table('affiliate_log') . ' a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND (u.parent_id IN (' . $all_uid . ') AND o.is_separate = 0 OR a.user_id = \'' . $user_id . '\' AND o.is_separate > 0) ' . $where);
						$sql = 'SELECT o.*, a.log_id, a.user_id as suid,  a.user_name as auser, a.money, a.point, a.separate_type FROM ' . $ecs->table('order_info') . ' o' . ' LEFT JOIN' . $ecs->table('users') . ' u ON o.user_id = u.user_id' . ' LEFT JOIN ' . $ecs->table('affiliate_log') . ' a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND (u.parent_id IN (' . $all_uid . ') AND o.is_separate = 0 OR a.user_id = \'' . $user_id . '\' AND o.is_separate > 0) ' . $where) . ' ORDER BY order_id DESC';
						$affiliate_intro = nl2br(sprintf($_LANG['affiliate_intro'][$affiliate['config']['separate_by']], $affiliate['config']['expire'], $_LANG['expire_unit'][$affiliate['config']['expire_unit']], $affiliate['config']['level_register_all'], $affiliate['config']['level_register_up'], $affiliate['config']['level_money_all'], $affiliate['config']['level_point_all']));
					}
					else {
						$sqlcount = 'SELECT count(*) FROM ' . $ecs->table('order_info') . ' o' . ' LEFT JOIN' . $ecs->table('users') . ' u ON o.user_id = u.user_id' . ' LEFT JOIN ' . $ecs->table('affiliate_log') . ' a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND (o.parent_id = \'' . $user_id . '\' AND o.is_separate = 0 OR a.user_id = \'' . $user_id . '\' AND o.is_separate > 0) ' . $where);
						$sql = 'SELECT o.*, a.log_id,a.user_id as suid, a.user_name as auser, a.money, a.point, a.separate_type,u.parent_id as up FROM ' . $ecs->table('order_info') . ' o' . ' LEFT JOIN' . $ecs->table('users') . ' u ON o.user_id = u.user_id' . ' LEFT JOIN ' . $ecs->table('affiliate_log') . ' a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND (o.parent_id = \'' . $user_id . '\' AND o.is_separate = 0 OR a.user_id = \'' . $user_id . '\' AND o.is_separate > 0) ' . $where) . ' ORDER BY order_id DESC';
						$affiliate_intro = nl2br(sprintf($_LANG['affiliate_intro'][$affiliate['config']['separate_by']], $affiliate['config']['expire'], $_LANG['expire_unit'][$affiliate['config']['expire_unit']], $affiliate['config']['level_money_all'], $affiliate['config']['level_point_all']));
					}

					$count = $db->getOne($sqlcount);
					$max_page = 0 < $count ? ceil($count / $size) : 1;

					if ($max_page < $page) {
						$page = $max_page;
					}

					$res = $db->SelectLimit($sql, $size, ($page - 1) * $size);
					$logdb = array();

					while ($rt = $GLOBALS['db']->fetchRow($res)) {
						if (!empty($rt['suid'])) {
							if ($rt['separate_type'] == -1 || $rt['separate_type'] == -2) {
								$rt['is_separate'] = 3;
							}
						}

						$rt['order_sn'] = substr($rt['order_sn'], 0, strlen($rt['order_sn']) - 5) . '***' . substr($rt['order_sn'], -2, 2);
						$logdb[] = $rt;
					}

					$url_format = 'user.php?act=affiliate&page=';
					$pager = array(
						'page'         => $page,
						'size'         => $size,
						'sort'         => '',
						'order'        => '',
						'record_count' => $count,
						'page_count'   => $max_page,
						'page_first'   => $url_format . '1',
						'page_prev'    => 1 < $page ? $url_format . ($page - 1) : 'javascript:;',
						'page_next'    => $page < $max_page ? $url_format . ($page + 1) : 'javascript:;',
						'page_last'    => $url_format . $max_page,
						'array'        => array()
						);

					for ($i = 1; $i <= $max_page; $i++) {
						$pager['array'][$i] = $i;
					}

					$smarty->assign('url_format', $url_format);
					$smarty->assign('pager', $pager);
					$smarty->assign('affiliate_intro', $affiliate_intro);
					$smarty->assign('affiliate_type', $affiliate['config']['separate_by']);
					$smarty->assign('logdb', $logdb);
				}
				else {
					$smarty->assign('userid', $user_id);
					$smarty->assign('goodsid', $goodsid);
					$types = array(1, 2, 3, 4, 5);
					$smarty->assign('types', $types);
					$goods = get_goods_info($goodsid);
					$shopurl = $ecs->url();
					$goods['goods_img'] = strpos($goods['goods_img'], 'http://') === false && strpos($goods['goods_img'], 'https://') === false ? $shopurl . $goods['goods_img'] : $goods['goods_img'];
					$goods['goods_thumb'] = strpos($goods['goods_thumb'], 'http://') === false && strpos($goods['goods_thumb'], 'https://') === false ? $shopurl . $goods['goods_thumb'] : $goods['goods_thumb'];
					$goods['shop_price'] = price_format($goods['shop_price']);
					$smarty->assign('goods', $goods);
				}

				$smarty->assign('shopname', $_CFG['shop_name']);
				$smarty->assign('userid', $user_id);
				$smarty->assign('shopurl', $ecs->url());
				$smarty->assign('logosrc', 'themes/' . $_CFG['template'] . '/images/logo.gif');
				$smarty->display('user_clips.dwt');
			}
			else if ($action == 'email_list') {
				$job = $_GET['job'];
				if ($job == 'add' || $job == 'del') {
					if (isset($_SESSION['last_email_query'])) {
						if (time() - $_SESSION['last_email_query'] <= 30) {
							exit($_LANG['order_query_toofast']);
						}
					}

					$_SESSION['last_email_query'] = time();
				}

				$email = trim($_GET['email']);
				$email = htmlspecialchars($email);

				if (!is_email($email)) {
					$info = sprintf($_LANG['email_invalid'], $email);
					exit($info);
				}

				$ck = $db->getRow('SELECT * FROM ' . $ecs->table('email_list') . (' WHERE email = \'' . $email . '\''));

				if ($job == 'add') {
					if (empty($ck)) {
						$hash = substr(md5(time()), 1, 10);
						$sql = 'INSERT INTO ' . $ecs->table('email_list') . (' (email, stat, hash) VALUES (\'' . $email . '\', 0, \'' . $hash . '\')');
						$db->query($sql);
						$info = $_LANG['email_check'];
						$url = $ecs->url() . ('user.php?act=email_list&job=add_check&hash=' . $hash . '&email=' . $email);
						send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email, $_CFG['shop_name'], $url, $url, $_CFG['shop_name'], local_date('Y-m-d')), 1);
					}
					else if ($ck['stat'] == 1) {
						$info = sprintf($_LANG['email_alreadyin_list'], $email);
					}
					else {
						$hash = substr(md5(time()), 1, 10);
						$sql = 'UPDATE ' . $ecs->table('email_list') . ('SET hash = \'' . $hash . '\' WHERE email = \'' . $email . '\'');
						$db->query($sql);
						$info = $_LANG['email_re_check'];
						$url = $ecs->url() . ('user.php?act=email_list&job=add_check&hash=' . $hash . '&email=' . $email);
						send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email, $_CFG['shop_name'], $url, $url, $_CFG['shop_name'], local_date('Y-m-d')), 1);
					}

					exit($info);
				}
				else if ($job == 'del') {
					if (empty($ck)) {
						$info = sprintf($_LANG['email_notin_list'], $email);
					}
					else if ($ck['stat'] == 1) {
						$hash = substr(md5(time()), 1, 10);
						$sql = 'UPDATE ' . $ecs->table('email_list') . ('SET hash = \'' . $hash . '\' WHERE email = \'' . $email . '\'');
						$db->query($sql);
						$info = $_LANG['email_check'];
						$url = $ecs->url() . ('user.php?act=email_list&job=del_check&hash=' . $hash . '&email=' . $email);
						send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email, $_CFG['shop_name'], $url, $url, $_CFG['shop_name'], local_date('Y-m-d')), 1);
					}
					else {
						$info = $_LANG['email_not_alive'];
					}

					exit($info);
				}
				else if ($job == 'add_check') {
					if (empty($ck)) {
						$info = sprintf($_LANG['email_notin_list'], $email);
					}
					else if ($ck['stat'] == 1) {
						$info = $_LANG['email_checked'];
					}
					else if ($_GET['hash'] == $ck['hash']) {
						$sql = 'UPDATE ' . $ecs->table('email_list') . ('SET stat = 1 WHERE email = \'' . $email . '\'');
						$db->query($sql);
						$info = $_LANG['email_checked'];
					}
					else {
						$info = $_LANG['hash_wrong'];
					}

					show_message($info, $_LANG['back_home_lnk'], 'index.php');
				}
				else if ($job == 'del_check') {
					if (empty($ck)) {
						$info = sprintf($_LANG['email_invalid'], $email);
					}
					else if ($ck['stat'] == 1) {
						if ($_GET['hash'] == $ck['hash']) {
							$sql = 'DELETE FROM ' . $ecs->table('email_list') . ('WHERE email = \'' . $email . '\'');
							$db->query($sql);
							$info = $_LANG['email_canceled'];
						}
						else {
							$info = $_LANG['hash_wrong'];
						}
					}
					else {
						$info = $_LANG['email_not_alive'];
					}

					show_message($info, $_LANG['back_home_lnk'], 'index.php');
				}
			}
			else if ($action == 'send_hash_mail') {
				include_once ROOT_PATH . 'includes/cls_json.php';
				include_once ROOT_PATH . 'includes/lib_passport.php';
				$json = new JSON();
				$result = array('error' => 0, 'message' => '', 'content' => '');

				if ($user_id == 0) {
					$result['error'] = 1;
					$result['message'] = $_LANG['login_please'];
					exit($json->encode($result));
				}

				if (send_regiter_hash($user_id)) {
					$result['message'] = $_LANG['validate_mail_ok'];
					exit($json->encode($result));
				}
				else {
					$result['error'] = 1;
					$result['message'] = $GLOBALS['err']->last_message();
				}

				exit($json->encode($result));
			}
			else if ($action == 'track_packages') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				include_once ROOT_PATH . 'includes/lib_order.php';
				$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
				$size = 20;
				$orders = array();
				$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('order_info') . ' as oi' . (' WHERE oi.user_id = \'' . $user_id . '\' AND oi.shipping_status IN (\'') . SS_SHIPPED . '\', \'' . SS_RECEIVED . '\')' . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi_2 where oi_2.main_order_id = oi.order_id) = 0';
				$record_count = $GLOBALS['db']->getOne($sql);
				$sql = 'SELECT oi.order_id,oi.order_sn,oi.user_id,oi.invoice_no,oi.shipping_id,oi.shipping_name,oi.shipping_time,oi.shipping_status,oi.mobile,oi.address,oi.consignee FROM ' . $ecs->table('order_info') . ' as oi' . (' WHERE oi.user_id = \'' . $user_id . '\' AND oi.shipping_status IN (\'') . SS_SHIPPED . '\', \'' . SS_RECEIVED . '\')' . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi_2 where oi_2.main_order_id = oi.order_id) = 0 ORDER BY order_id DESC';
				$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

				while ($item = $GLOBALS['db']->fetchRow($res)) {
					$shipping = get_shipping_object($item['shipping_id']);
					$item['formated_shipping_time'] = local_date($GLOBALS['_CFG']['time_format'], $item['shipping_time']);
					$item['shipping_status'] = $GLOBALS['_LANG']['ss'][$item['shipping_status']];

					if (method_exists($shipping, 'query')) {
						$query_link = $shipping->query($item['invoice_no']);
					}
					else {
						$query_link = $item['invoice_no'];
					}

					if ($query_link != $item['invoice_no']) {
						$item['query_link'] = $query_link;

						if (defined('THEME_EXTENSION')) {
							$item['goods'] = get_order_goods_toInfo($item['order_id']);
						}

						$orders[] = $item;
					}
				}

				$pager = get_pager('user.php', array('act' => $action), $record_count, $page, $size);
				$smarty->assign('pager', $pager);
				$smarty->assign('orders', $orders);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'order_query') {
				$_GET['order_sn'] = trim(substr($_GET['order_sn'], 1));
				$order_sn = empty($_GET['order_sn']) ? '' : addslashes($_GET['order_sn']);
				include_once ROOT_PATH . 'includes/cls_json.php';
				$json = new JSON();
				$result = array('error' => 0, 'message' => '', 'content' => '');

				if (isset($_SESSION['last_order_query'])) {
					if (gmtime() - $_SESSION['last_order_query'] <= 10) {
						$result['error'] = 1;
						$result['message'] = $_LANG['order_query_toofast'];
						exit($json->encode($result));
					}
				}

				$_SESSION['last_order_query'] = gmtime();

				if (empty($order_sn)) {
					$result['error'] = 1;
					$result['message'] = $_LANG['invalid_order_sn'];
					exit($json->encode($result));
				}

				$sql = 'SELECT order_id, order_status, shipping_status, pay_status, ' . ' shipping_time, shipping_id, invoice_no, user_id ' . ' FROM ' . $ecs->table('order_info') . (' WHERE order_sn = \'' . $order_sn . '\' LIMIT 1');
				$row = $db->getRow($sql);

				if (empty($row)) {
					$result['error'] = 1;
					$result['message'] = $_LANG['invalid_order_sn'];
					exit($json->encode($result));
				}

				$order_query = array();
				$order_query['order_sn'] = $order_sn;
				$order_query['order_id'] = $row['order_id'];
				$order_query['order_status'] = $_LANG['os'][$row['order_status']] . ',' . $_LANG['ps'][$row['pay_status']] . ',' . $_LANG['ss'][$row['shipping_status']];
				if ($row['invoice_no'] && 0 < $row['shipping_id']) {
					$sql = 'SELECT shipping_code FROM ' . $ecs->table('shipping') . (' WHERE shipping_id = \'' . $row['shipping_id'] . '\'');
					$shipping_code = $db->getOne($sql);
					$plugin = ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php';

					if (file_exists($plugin)) {
						include_once $plugin;
						$shipping = new $shipping_code();
						$order_query['invoice_no'] = $shipping->query((string) $row['invoice_no']);
					}
					else {
						$order_query['invoice_no'] = (string) $row['invoice_no'];
					}
				}

				$order_query['user_id'] = $row['user_id'];
				if ($row['user_id'] == 0 && 0 < $row['shipping_time']) {
					$order_query['shipping_date'] = local_date($GLOBALS['_CFG']['date_format'], $row['shipping_time']);
				}

				$smarty->assign('order_query', $order_query);
				$result['content'] = $smarty->fetch('library/order_query.lbi');
				exit($json->encode($result));
			}
			else if ($action == 'transform_points') {
				$rule = array();

				if (!empty($_CFG['points_rule'])) {
					$rule = unserialize($_CFG['points_rule']);
				}

				$cfg = array();

				if (!empty($_CFG['integrate_config'])) {
					$cfg = unserialize($_CFG['integrate_config']);
					$_LANG['exchange_points'][0] = empty($cfg['uc_lang']['credits'][0][0]) ? $_LANG['exchange_points'][0] : $cfg['uc_lang']['credits'][0][0];
					$_LANG['exchange_points'][1] = empty($cfg['uc_lang']['credits'][1][0]) ? $_LANG['exchange_points'][1] : $cfg['uc_lang']['credits'][1][0];
				}

				$sql = 'SELECT user_id, user_name, pay_points, rank_points FROM ' . $ecs->table('users') . (' WHERE user_id=\'' . $user_id . '\'');
				$row = $db->getRow($sql);

				if ($_CFG['integrate_code'] == 'ucenter') {
					$exchange_type = 'ucenter';
					$to_credits_options = array();
					$out_exchange_allow = array();

					foreach ($rule as $credit) {
						$out_exchange_allow[$credit['appiddesc'] . '|' . $credit['creditdesc'] . '|' . $credit['creditsrc']] = $credit['ratio'];

						if (!array_key_exists($credit['appiddesc'] . '|' . $credit['creditdesc'], $to_credits_options)) {
							$to_credits_options[$credit['appiddesc'] . '|' . $credit['creditdesc']] = $credit['title'];
						}
					}

					$smarty->assign('selected_org', $rule[0]['creditsrc']);
					$smarty->assign('selected_dst', $rule[0]['appiddesc'] . '|' . $rule[0]['creditdesc']);
					$smarty->assign('descreditunit', $rule[0]['unit']);
					$smarty->assign('orgcredittitle', $_LANG['exchange_points'][$rule[0]['creditsrc']]);
					$smarty->assign('descredittitle', $rule[0]['title']);
					$smarty->assign('descreditamount', round(1 / $rule[0]['ratio'], 2));
					$smarty->assign('to_credits_options', $to_credits_options);
					$smarty->assign('out_exchange_allow', $out_exchange_allow);
				}
				else {
					$exchange_type = 'other';
					$bbs_points_name = $user->get_points_name();
					$total_bbs_points = $user->get_points($row['user_name']);
					$bbs_points = array();

					foreach ($bbs_points_name as $key => $val) {
						$bbs_points[$key] = array('title' => $_LANG['bbs'] . $val['title'], 'value' => $total_bbs_points[$key]);
					}

					$rule_list = array();

					foreach ($rule as $key => $val) {
						$rule_key = substr($key, 0, 1);
						$bbs_key = substr($key, 1);
						$rule_list[$key]['rate'] = $val;

						switch ($rule_key) {
						case TO_P:
							$rule_list[$key]['from'] = $_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
							$rule_list[$key]['to'] = $_LANG['pay_points'];
							break;

						case TO_R:
							$rule_list[$key]['from'] = $_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
							$rule_list[$key]['to'] = $_LANG['rank_points'];
							break;

						case FROM_P:
							$rule_list[$key]['from'] = $_LANG['pay_points'];
							$_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
							$rule_list[$key]['to'] = $_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
							break;

						case FROM_R:
							$rule_list[$key]['from'] = $_LANG['rank_points'];
							$rule_list[$key]['to'] = $_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
							break;
						}
					}

					$smarty->assign('bbs_points', $bbs_points);
					$smarty->assign('rule_list', $rule_list);
				}

				$smarty->assign('shop_points', $row);
				$smarty->assign('exchange_type', $exchange_type);
				$smarty->assign('action', $action);
				$smarty->assign('lang', $_LANG);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'act_transform_points') {
				$_POST = get_request_filter($_POST, 1);
				$rule_index = empty($_POST['rule_index']) ? '' : trim($_POST['rule_index']);
				$num = empty($_POST['num']) ? 0 : intval($_POST['num']);
				if ($num <= 0 || $num != floor($num)) {
					show_message($_LANG['invalid_points'], $_LANG['transform_points'], 'user.php?act=transform_points');
				}

				$num = floor($num);
				$bbs_key = substr($rule_index, 1);
				$rule_key = substr($rule_index, 0, 1);
				$max_num = 0;
				$sql = 'SELECT user_name, user_id, pay_points, rank_points FROM ' . $ecs->table('users') . (' WHERE user_id=\'' . $user_id . '\'');
				$row = $db->getRow($sql);
				$bbs_points = $user->get_points($row['user_name']);
				$points_name = $user->get_points_name();
				$rule = array();

				if ($_CFG['points_rule']) {
					$rule = unserialize($_CFG['points_rule']);
				}

				list($from, $to) = explode(':', $rule[$rule_index]);
				$max_points = 0;

				switch ($rule_key) {
				case TO_P:
					$max_points = $bbs_points[$bbs_key];
					break;

				case TO_R:
					$max_points = $bbs_points[$bbs_key];
					break;

				case FROM_P:
					$max_points = $row['pay_points'];
					break;

				case FROM_R:
					$max_points = $row['rank_points'];
				}

				if ($max_points <= 0 || $max_points < $num) {
					show_message($_LANG['overflow_points'], $_LANG['transform_points'], 'user.php?act=transform_points');
				}

				switch ($rule_key) {
				case TO_P:
					$result_points = floor($num * $to / $from);
					$user->set_points($row['user_name'], array($bbs_key => 0 - $num));
					log_account_change($row['user_id'], 0, 0, 0, $result_points, $_LANG['transform_points'], ACT_OTHER);
					show_message(sprintf($_LANG['to_pay_points'], $num, $points_name[$bbs_key]['title'], $result_points), $_LANG['transform_points'], 'user.php?act=transform_points');
				case TO_R:
					$result_points = floor($num * $to / $from);
					$user->set_points($row['user_name'], array($bbs_key => 0 - $num));
					log_account_change($row['user_id'], 0, 0, $result_points, 0, $_LANG['transform_points'], ACT_OTHER);
					show_message(sprintf($_LANG['to_rank_points'], $num, $points_name[$bbs_key]['title'], $result_points), $_LANG['transform_points'], 'user.php?act=transform_points');
				case FROM_P:
					$result_points = floor($num * $to / $from);
					log_account_change($row['user_id'], 0, 0, 0, 0 - $num, $_LANG['transform_points'], ACT_OTHER);
					$user->set_points($row['user_name'], array($bbs_key => $result_points));
					show_message(sprintf($_LANG['from_pay_points'], $num, $result_points, $points_name[$bbs_key]['title']), $_LANG['transform_points'], 'user.php?act=transform_points');
				case FROM_R:
					$result_points = floor($num * $to / $from);
					log_account_change($row['user_id'], 0, 0, 0 - $num, 0, $_LANG['transform_points'], ACT_OTHER);
					$user->set_points($row['user_name'], array($bbs_key => $result_points));
					show_message(sprintf($_LANG['from_rank_points'], $num, $result_points, $points_name[$bbs_key]['title']), $_LANG['transform_points'], 'user.php?act=transform_points');
				}
			}
			else if ($action == 'act_transform_ucenter_points') {
				$rule = array();

				if ($_CFG['points_rule']) {
					$rule = unserialize($_CFG['points_rule']);
				}

				$shop_points = array('rank_points', 'pay_points');
				$sql = 'SELECT user_id, user_name, pay_points, rank_points FROM ' . $ecs->table('users') . (' WHERE user_id=\'' . $user_id . '\'');
				$row = $db->getRow($sql);
				$_POST = get_request_filter($_POST, 1);
				$exchange_amount = intval($_POST['amount']);
				$fromcredits = intval($_POST['fromcredits']);
				$tocredits = trim($_POST['tocredits']);
				$cfg = unserialize($_CFG['integrate_config']);

				if (!empty($cfg)) {
					$_LANG['exchange_points'][0] = empty($cfg['uc_lang']['credits'][0][0]) ? $_LANG['exchange_points'][0] : $cfg['uc_lang']['credits'][0][0];
					$_LANG['exchange_points'][1] = empty($cfg['uc_lang']['credits'][1][0]) ? $_LANG['exchange_points'][1] : $cfg['uc_lang']['credits'][1][0];
				}

				list($appiddesc, $creditdesc) = explode('|', $tocredits);
				$ratio = 0;

				if ($exchange_amount <= 0) {
					show_message($_LANG['invalid_points'], $_LANG['transform_points'], 'user.php?act=transform_points');
				}

				if ($row[$shop_points[$fromcredits]] < $exchange_amount) {
					show_message($_LANG['overflow_points'], $_LANG['transform_points'], 'user.php?act=transform_points');
				}

				foreach ($rule as $credit) {
					if ($credit['appiddesc'] == $appiddesc && $credit['creditdesc'] == $creditdesc && $credit['creditsrc'] == $fromcredits) {
						$ratio = $credit['ratio'];
						break;
					}
				}

				if ($ratio == 0) {
					show_message($_LANG['exchange_deny'], $_LANG['transform_points'], 'user.php?act=transform_points');
				}

				$netamount = floor($exchange_amount / $ratio);
				include_once ROOT_PATH . './includes/lib_uc.php';
				$result = exchange_points($row['user_id'], $fromcredits, $creditdesc, $appiddesc, $netamount);

				if ($result === true) {
					$sql = 'UPDATE ' . $ecs->table('users') . (' SET ' . $shop_points[$fromcredits] . '=' . $shop_points[$fromcredits] . '-\'' . $exchange_amount . '\' WHERE user_id=\'' . $row['user_id'] . '\'');
					$db->query($sql);
					$sql = 'INSERT INTO ' . $ecs->table('account_log') . ('(user_id, ' . $shop_points[$fromcredits] . ', change_time, change_desc, change_type)') . (' VALUES (\'' . $row['user_id'] . '\', \'-' . $exchange_amount . '\', \'') . gmtime() . '\', \'' . $cfg['uc_lang']['exchange'] . '\', \'98\')';
					$db->query($sql);
					show_message(sprintf($_LANG['exchange_success'], $exchange_amount, $_LANG['exchange_points'][$fromcredits], $netamount, $credit['title']), $_LANG['transform_points'], 'user.php?act=transform_points');
				}
				else {
					show_message($_LANG['exchange_error_1'], $_LANG['transform_points'], 'user.php?act=transform_points');
				}
			}
			else if ($action == 'clear_history') {
				setcookie('ECS[history]', '', 1, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
				setcookie('ECS[list_history]', '', 1, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
			}
			else if ($action == 'goods_order') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				include_once ROOT_PATH . 'includes/lib_order.php';

				if (isset($_REQUEST['order_id'])) {
					$order_id = intval($_REQUEST['order_id']);
				}
				else {
					exit('invalid parameter');
				}

				$order = order_info($order_id);
				$goods_list = order_goods($order_id);

				foreach ($goods_list as $key => $value) {
					if ($value['extension_code'] != 'package_buy') {
						$price[] = $value['subtotal'];
						$goods_list[$key]['market_price'] = price_format($value['market_price'], false);
						$goods_list[$key]['goods_price'] = price_format($value['goods_price'], false);
						$goods_list[$key]['subtotal'] = price_format($value['subtotal'], false);
						$goods_list[$key]['is_refound'] = get_is_refound($value['rec_id']);
						$goods_list[$key]['goods_attr'] = str_replace(' ', '&nbsp;&nbsp;&nbsp;&nbsp;', $value['goods_attr']);
						$goods_info = get_goods_info($value['goods_id'], 0, 0, array('goods_cause'));
						$goods_list[$key]['goods_cause'] = get_goods_cause($goods_info['goods_cause'], $order['chargeoff_status'], $order['is_settlement']);
					}
					else {
						unset($goods_list[$key]);
						$smarty->assign('package_buy', true);
					}
				}

				$formated_goods_amount = price_format(array_sum($price), false);
				$smarty->assign('formated_goods_amount', $formated_goods_amount);
				$smarty->assign('order_id', $order_id);
				$smarty->assign('goods_list', $goods_list);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'service_detail') {
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'apply_return') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				include_once ROOT_PATH . 'includes/lib_order.php';

				if (isset($_REQUEST['rec_id'])) {
					$recr_id = intval($_REQUEST['rec_id']);
				}
				else {
					exit('invalid parameter');
				}

				$order_id = intval($_REQUEST['order_id']);
				$order = order_info($order_id);
				$sql = ' SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\' AND shipping_status > 0 ');
				$return_allowable = $GLOBALS['db']->getOne($sql, true);
				$smarty->assign('return_allowable', $return_allowable);
				$goods_info = rec_goods($recr_id);
				$parent_cause = get_parent_cause();
				$consignee = array('country' => $order['country'], 'province' => $order['province'], 'city' => $order['city'], 'district' => $order['district'], 'address' => $order['address'], 'mobile' => $order['mobile'], 'consignee' => $order['consignee'], 'user_id' => $order['user_id'], 'region' => get_user_region_address($order['order_id']));
				$smarty->assign('consignee', $consignee);
				$smarty->assign('show_goods_thumb', $GLOBALS['_CFG']['show_goods_in_cart']);
				$smarty->assign('show_goods_attribute', $GLOBALS['_CFG']['show_attr_in_cart']);
				$smarty->assign('goods', $goods_info);
				$smarty->assign('goods_return', $goods_info);
				$smarty->assign('order_id', $order_id);
				$smarty->assign('cause_list', $parent_cause);
				$smarty->assign('order_sn', $order['order_sn']);
				$smarty->assign('order', $order);
				$country_list = get_regions_log(0, 0);
				$province_list = get_regions_log(1, $consignee['country']);
				$city_list = get_regions_log(2, $consignee['province']);
				$district_list = get_regions_log(3, $consignee['city']);
				$street_list = get_regions_log(4, $consignee['district']);
				$cause_list = array('0', '1', '2', '3');
				$goods_info = get_goods_info($goods_info['goods_id'], 0, 0, array('goods_cause'));
				$goods_cause = get_goods_cause($goods_info['goods_cause'], $order['chargeoff_status'], $order['is_settlement']);
				$sql = 'SELECT COUNT(*) FROM' . $GLOBALS['ecs']->table('order_cloud') . ('WHERE rec_id = \'' . $rec_id . '\' LIMIT 1');
				$cloud_count = $db->getRow($sql);
				if (0 < $cloud_count && !empty($goods_cause)) {
					foreach ($goods_cause as $k => $v) {
						if ($v['cause'] == 0 || $v['cause'] == 2) {
							unset($goods_cause[$k]);
						}
					}
				}

				if ($order['shipping_status'] == 0) {
					$goods_info['goods_cause'] = '3';
				}

				$goods_cause = get_goods_cause($goods_info['goods_cause'], $order['chargeoff_status'], $order['is_settlement']);
				$smarty->assign('goods_cause', $goods_cause);
				$sql = 'select img_file from ' . $ecs->table('return_images') . (' where user_id = \'' . $user_id . '\' and rec_id = \'' . $recr_id . '\' order by id desc');
				$img_list = $db->getAll($sql);
				$smarty->assign('img_list', $img_list);
				$sn = 0;
				$smarty->assign('country_list', $country_list);
				$smarty->assign('province_list', $province_list);
				$smarty->assign('city_list', $city_list);
				$smarty->assign('district_list', $district_list);
				$smarty->assign('street_list', $street_list);
				$smarty->assign('sn', $sn);
				$smarty->assign('sessid', SESS_ID);
				$smarty->assign('return_pictures', $GLOBALS['_CFG']['return_pictures']);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'apply_info') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				include_once ROOT_PATH . 'includes/lib_order.php';

				if (isset($_REQUEST['goods_id'])) {
					$goods_id = intval($_REQUEST['goods_id']);
					$order_id = intval($_REQUEST['order_id']);
				}
				else {
					exit('invalid parameter');
				}

				$goods_info = array();
				$sql = "SELECT o.*, IF(o.product_id > 0, p.product_number, g.goods_number) AS storage, o.goods_attr, g.suppliers_id, IFNULL(b.brand_name, '') AS brand_name, p.product_sn\r\n            FROM " . $ecs->table('order_goods') . " AS o\r\n                LEFT JOIN " . $ecs->table('products') . " AS p\r\n                    ON p.product_id = o.product_id\r\n                LEFT JOIN " . $ecs->table('goods') . " AS g\r\n                    ON o.goods_id = g.goods_id\r\n                LEFT JOIN " . $ecs->table('brand') . (" AS b\r\n                    ON g.brand_id = b.brand_id\r\n            WHERE g.goods_id = '" . $goods_id . '\'');
				$goods_info = $db->getRow($sql);
				$sql = 'SELECT consignee , tel , country , province , city , district , address   FROM ' . $ecs->table('order_info') . 'WHERE order_id = ' . $order_id;
				$user_info = $db->getRow($sql);
				$smarty->assign('lang', $_LANG);
				$smarty->assign('country_list', get_regions());
				$consignee_list = get_consignee_list($_SESSION['user_id']);
				$smarty->assign('consignee_list', $consignee_list);

				foreach ($consignee_list as $region_id => $consignee) {
					$consignee['country'] = isset($consignee['country']) ? intval($consignee['country']) : 0;
					$consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
					$consignee['city'] = isset($consignee['city']) ? intval($consignee['city']) : 0;
					$province_list[$region_id] = get_regions(1, $consignee['country']);
					$city_list[$region_id] = get_regions(2, $consignee['province']);
					$district_list[$region_id] = get_regions(3, $consignee['city']);
				}

				$address_id = $db->getOne('SELECT address_id FROM ' . $ecs->table('users') . (' WHERE user_id=\'' . $user_id . '\''));
				$smarty->assign('province_list', $province_list);
				$smarty->assign('address', $address_id);
				$smarty->assign('city_list', $city_list);
				$smarty->assign('district_list', $district_list);
				$smarty->assign('currency_format', $_CFG['currency_format']);
				$smarty->assign('sn', 0);
				$smarty->assign('user_info', $user_info);
				$smarty->assign('goods_info', $goods_info);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'submit_return') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				include_once ROOT_PATH . 'includes/lib_order.php';
				$rec_id = empty($_REQUEST['rec_id']) ? 0 : intval($_REQUEST['rec_id']);
				$last_option = !isset($_REQUEST['last_option']) ? $_REQUEST['parent_id'] : $_REQUEST['last_option'];
				$return_remark = !isset($_REQUEST['return_remark']) ? '' : addslashes(trim($_REQUEST['return_remark']));
				$return_brief = !isset($_REQUEST['return_brief']) ? '' : addslashes(trim($_REQUEST['return_brief']));
				$chargeoff_status = !isset($_REQUEST['chargeoff_status']) && empty($_REQUEST['chargeoff_status']) ? 0 : intval($_REQUEST['chargeoff_status']);

				if (0 < $rec_id) {
					$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('order_return') . ' WHERE rec_id = ' . $rec_id;
					$num = $db->getOne($sql);

					if (0 < $num) {
						show_message($_LANG['Repeated_submission'], '', '', 'info', true);
					}
				}
				else {
					show_message($_LANG['Return_abnormal'], '', '', 'info', true);
				}

				$sql = 'select g.goods_name, g.goods_sn,g.brand_id, og.order_id, og.goods_id, og.product_id, og.goods_attr, og.warehouse_id, og.area_id, ' . ' og.is_real, og.goods_attr_id, og.goods_price, og.goods_price, og.goods_number ' . 'from ' . $ecs->table('order_goods') . ' as og ' . ' left join ' . $ecs->table('goods') . ' as g on og.goods_id = g.goods_id ' . (' where og.rec_id = \'' . $rec_id . '\'');
				$order_goods = $db->getRow($sql);
				$sql = ' SELECT order_sn, country,province,city ,district FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id =' . $order_goods['order_id'];
				$res = $GLOBALS['db']->getRow($sql);
				$maintain_number = empty($_REQUEST['maintain_number']) ? 0 : intval($_REQUEST['maintain_number']);
				$return_num = empty($_REQUEST['return_num']) ? 0 : intval($_REQUEST['return_num']);
				$back_number = empty($_REQUEST['attr_num']) ? 0 : intval($_REQUEST['attr_num']);
				$goods_number = empty($_REQUEST['return_g_number']) ? 0 : intval($_REQUEST['return_g_number']);
				$return_type = intval($_REQUEST['return_type']);
				$maintain = 0;
				$return_status = 0;

				if ($return_type == 1) {
					$back = 1;
					$exchange = 0;
					$return_number = $return_num;
				}
				else if ($return_type == 2) {
					$back = 0;
					$exchange = 2;
					$return_number = $back_number;
				}
				else if ($return_type == 3) {
					$back = 0;
					$exchange = 0;
					$return_number = $goods_number;
					$return_status = -1;
				}
				else {
					$back = 0;
					$exchange = 0;
					$return_number = $maintain_number;
				}

				$aftersn = 0;
				$sql = 'SELECT cloud_orderid,cloud_detailed_id FROM' . $GLOBALS['ecs']->table('order_cloud') . ('WHERE rec_id = \'' . $rec_id . '\' LIMIT 1');
				$order_cloud = $db->getRow($sql);

				if (!empty($order_cloud)) {
					if ($return_type == 0 || $return_type == 2) {
						show_message($_LANG['return_error'], '', '', 'info', true);
					}

					$isRefund = 1;

					if ($return_type == 3) {
						$isRefund = 2;
					}

					$order_return_request = array('isRefund' => intval($isRefund), 'orderDetailId' => intval($order_cloud['cloud_detailed_id']), 'orderInfoId' => intval($order_cloud['cloud_orderid']), 'refundNum' => intval($return_number), 'userReason' => trim($return_brief), 'imgProof1' => '', 'imgProof2' => '', 'imgProof3' => '');
					$sql = 'select img_file from' . $ecs->table('return_images') . (' where rec_id = \'' . $rec_id . '\' and user_id = \'') . $_SESSION['user_id'] . '\' LIMIT 0,3';
					$images_list = $db->getCol($sql);

					if (!empty($images_list)) {
						foreach ($images_list as $k => $v) {
							if ($v) {
								$img = get_image_path(0, $v);
								if (!empty($img) && (strpos($img, 'http://') === false && strpos($img, 'https://') === false && strpos($img, 'errorImg.png') === false)) {
									$img = $ecs->url() . $img;
								}

								$i = $k + 1;
								$order_return_request['imgProof' . $i] = $img;
							}
						}
					}

					$plugin_file = ROOT_PATH . 'plugins/cloudApi/cloudApi.php';

					if (file_exists($plugin_file)) {
						include_once $plugin_file;
						$cloud = new cloud();
						$requ = $cloud->apiAfterSales($order_return_request);

						if ($requ) {
							$requ = json_decode($requ, true);

							if ($requ['code'] != '10000') {
								show_message($requ['message'], '', '', 'info', true);
							}
							else {
								$aftersn = $requ['data']['afterSn'];
							}
						}
						else {
							show_message($_LANG['process_false'], '', '', 'info', true);
						}
					}
				}

				$attr_val = isset($_REQUEST['attr_val']) ? $_REQUEST['attr_val'] : array();
				$return_attr_id = !empty($attr_val) ? implode(',', $attr_val) : '';
				$attr_val = get_goods_attr_info_new($attr_val, 'pice', $order_goods['warehouse_id'], $order_goods['area_id']);
				$order_return = array('rec_id' => $rec_id, 'goods_id' => $order_goods['goods_id'], 'order_id' => $order_goods['order_id'], 'order_sn' => $order_goods['goods_sn'], 'chargeoff_status' => $chargeoff_status, 'return_type' => $return_type, 'maintain' => $maintain, 'back' => $back, 'exchange' => $exchange, 'user_id' => $_SESSION['user_id'], 'goods_attr' => $order_goods['goods_attr'], 'attr_val' => $attr_val, 'return_brief' => $return_brief, 'remark' => $return_remark, 'credentials' => !isset($_REQUEST['credentials']) ? 0 : intval($_REQUEST['credentials']), 'country' => empty($_REQUEST['country']) ? 0 : intval($_REQUEST['country']), 'province' => empty($_REQUEST['province']) ? 0 : intval($_REQUEST['province']), 'city' => empty($_REQUEST['city']) ? 0 : intval($_REQUEST['city']), 'district' => empty($_REQUEST['district']) ? 0 : intval($_REQUEST['district']), 'street' => empty($_REQUEST['street']) ? 0 : intval($_REQUEST['street']), 'cause_id' => $last_option, 'apply_time' => gmtime(), 'actual_return' => '', 'address' => empty($_REQUEST['return_address']) ? '' : addslashes(trim($_REQUEST['return_address'])), 'zipcode' => empty($_REQUEST['code']) ? '' : intval($_REQUEST['code']), 'addressee' => empty($_REQUEST['addressee']) ? '' : addslashes(trim($_REQUEST['addressee'])), 'phone' => empty($_REQUEST['mobile']) ? '' : addslashes(trim($_REQUEST['mobile'])), 'return_status' => $return_status);

				if (in_array($return_type, array(1, 3))) {
					$return_info = get_return_refound($order_return['order_id'], $order_return['rec_id'], $return_number);
					$order_return['should_return'] = $return_info['return_price'];
					$order_return['return_shipping_fee'] = $return_info['return_shipping_fee'];
				}
				else {
					$order_return['should_return'] = 0;
					$order_return['return_shipping_fee'] = 0;
				}

				$error_no = 0;

				do {
					$order_return['return_sn'] = get_order_sn();
					$query = $db->autoExecute($ecs->table('order_return'), $order_return, 'INSERT', '', 'SILENT');
					$error_no = $GLOBALS['db']->errno();
					if (0 < $error_no && $error_no != 1062) {
						exit($GLOBALS['db']->errorMsg());
					}
				} while ($error_no == 1062);

				if ($query) {
					$ret_id = $db->insert_id();
					return_action($ret_id, $_LANG['Apply_refund'], '', $order_return['remark'], $_LANG['buyer']);
					$return_goods['rec_id'] = $order_return['rec_id'];
					$return_goods['ret_id'] = $ret_id;
					$return_goods['goods_id'] = $order_goods['goods_id'];
					$return_goods['goods_name'] = $order_goods['goods_name'];
					$return_goods['brand_name'] = $order_goods['brand_name'];
					$return_goods['product_id'] = $order_goods['product_id'];
					$return_goods['goods_sn'] = $order_goods['goods_sn'];
					$return_goods['is_real'] = $order_goods['is_real'];
					$return_goods['goods_attr'] = $attr_val;
					$return_goods['attr_id'] = $return_attr_id;
					$return_goods['refound'] = $order_goods['goods_price'];
					$return_goods['return_type'] = $return_type;
					$return_goods['return_number'] = $return_number;

					if ($return_type == 1) {
						$return_goods['out_attr'] = '';
					}
					else if ($return_type == 2) {
						$return_goods['out_attr'] = $attr_val;
						$return_goods['return_attr_id'] = $return_attr_id;
					}
					else {
						$return_goods['out_attr'] = '';
					}

					$query = $db->autoExecute($ecs->table('return_goods'), $return_goods, 'INSERT', '', 'SILENT');
					$sql = 'select count(*) from' . $ecs->table('return_images') . (' where rec_id = \'' . $rec_id . '\' and user_id = \'') . $_SESSION['user_id'] . '\'';
					$images_count = $db->getOne($sql);

					if (0 < $images_count) {
						$images['rg_id'] = $order_goods['goods_id'];
						$db->autoExecute($ecs->table('return_images'), $images, 'UPDATE', 'rec_id = \'' . $rec_id . '\' and user_id = \'' . $_SESSION['user_id'] . '\'');
					}

					$order_return_extend = array('ret_id' => $ret_id, 'return_number' => $return_number, 'aftersn' => $aftersn);
					$db->autoExecute($ecs->table('order_return_extend'), $order_return_extend, 'INSERT', '', 'SILENT');
					$address_detail = get_user_region_address($order_goods['order_id'], $order_return['address']);
					$order_return['address_detail'] = $address_detail;
					$order_return['apply_time'] = local_date('Y-m-d H:i:s', $order_return['apply_time']);
					show_message($_LANG['Apply_Success_Prompt'], $_LANG['See_Returnlist'], 'user.php?act=return_list', 'info', true, $order_return);
				}
				else {
					show_message($_LANG['Apply_abnormal'], '', '', 'info', true);
				}
			}
			else if ($action == 'batch_applied') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				include_once ROOT_PATH . 'includes/lib_order.php';

				if (isset($_REQUEST['checkboxes'])) {
					$order_id = intval($_REQUEST['order_id']);
					$order = order_info($order_id);
					$error = 0;
					$cause_arr = '';

					foreach ($_REQUEST['checkboxes'] as $key => $val) {
						$goods = rec_goods($val);
						$goods_info = get_goods_info($goods['goods_id'], 0, 0, array('goods_cause', 'extension_code'));

						if (empty($cause_arr)) {
							$cause_arr = explode(',', $goods_info['goods_cause']);
						}

						$cause_arr_next = explode(',', $goods_info['goods_cause']);
						if (!$goods_info['goods_cause'] || $goods_info['extension_code'] == 'virtual_card') {
							$error += 1;
						}
						else {
							if ($cause_arr) {
								$cause_arr = array_intersect($cause_arr, $cause_arr_next);
							}

							$goods_info_arr[$key] = $goods;
							$shop_name = $goods['user_name'];
							$rec_ids[] = $goods['rec_id'];
						}
					}

					if ($error) {
						show_message($_LANG['nonsupport_return_goods'], '', '', 'info', true);
					}
					else {
						$cause_str = implode(',', $cause_arr);
						$goods_cause = get_goods_cause($cause_str, $order['chargeoff_status']);
					}
				}
				else {
					show_message($_LANG['please_select_goods'], '', '', 'info', true);
				}

				$parent_cause = get_parent_cause();
				$consignee = get_consignee($_SESSION['user_id']);
				$smarty->assign('consignee', $consignee);
				$smarty->assign('show_goods_thumb', $GLOBALS['_CFG']['show_goods_in_cart']);
				$smarty->assign('show_goods_attribute', $GLOBALS['_CFG']['show_attr_in_cart']);
				$smarty->assign('goods', $goods_info_arr);
				$smarty->assign('goods_return', $goods_info_arr);
				$smarty->assign('shop_name', $shop_name);
				$smarty->assign('rec_ids', implode('-', $rec_ids));
				$smarty->assign('order_id', $order_id);
				$smarty->assign('cause_list', $parent_cause);
				$smarty->assign('order_sn', $order['order_sn']);
				$smarty->assign('order', $order);
				$country_list = get_regions_log(0, 0);
				$province_list = get_regions_log(1, $consignee['country']);
				$city_list = get_regions_log(2, $consignee['province']);
				$district_list = get_regions_log(3, $consignee['city']);
				$street_list = get_regions_log(4, $consignee['district']);
				$smarty->assign('goods_cause', $goods_cause);
				$sn = 0;
				$smarty->assign('country_list', $country_list);
				$smarty->assign('province_list', $province_list);
				$smarty->assign('city_list', $city_list);
				$smarty->assign('district_list', $district_list);
				$smarty->assign('street_list', $street_list);
				$smarty->assign('sn', $sn);
				$smarty->assign('sessid', SESS_ID);
				$smarty->assign('return_pictures', $GLOBALS['_CFG']['return_pictures']);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'submit_batch_return') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				include_once ROOT_PATH . 'includes/lib_order.php';

				if ($_REQUEST['return_rec_id']) {
					$return_remark = !isset($_REQUEST['return_remark']) ? '' : addslashes(trim($_REQUEST['return_remark']));
					$return_brief = !isset($_REQUEST['return_brief']) ? '' : addslashes(trim($_REQUEST['return_brief']));
					$chargeoff_status = !isset($_REQUEST['chargeoff_status']) && empty($_REQUEST['chargeoff_status']) ? 0 : intval($_REQUEST['chargeoff_status']);

					foreach ($_REQUEST['return_rec_id'] as $rec_id) {
						if (0 < $rec_id) {
							$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('order_return') . ' WHERE rec_id = ' . $rec_id;
							$num = $db->getOne($sql);

							if (0 < $num) {
								show_message($_LANG['Repeated_submission'], '', '', 'info', true);
							}
						}
						else {
							show_message($_LANG['Return_abnormal'], '', '', 'info', true);
						}

						$sql = 'select g.goods_name, g.goods_sn,g.brand_id, og.order_id, og.goods_id, og.product_id, og.goods_attr, og.warehouse_id, og.area_id, ' . ' og.is_real, og.goods_attr_id, og.goods_price, og.goods_price, og.goods_number ' . 'from ' . $ecs->table('order_goods') . ' as og ' . ' left join ' . $ecs->table('goods') . ' as g on og.goods_id = g.goods_id ' . (' where og.rec_id = \'' . $rec_id . '\'');
						$order_goods = $db->getRow($sql);
						$sql = ' SELECT order_sn, country,province,city ,district FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id =' . $order_goods['order_id'];
						$res = $GLOBALS['db']->getRow($sql);
						$return_number = $goods_number = $order_goods['goods_number'];
						$return_type = intval($_REQUEST['return_type']);
						$maintain = 0;
						$return_status = 0;

						if ($return_type == 1) {
							$back = 1;
							$exchange = 0;
						}
						else if ($return_type == 2) {
							$back = 0;
							$exchange = 2;
						}
						else if ($return_type == 3) {
							$back = 0;
							$exchange = 0;
							$return_status = -1;
						}
						else {
							$back = 0;
							$exchange = 0;
						}

						$aftersn = 0;
						$sql = 'SELECT cloud_orderid,cloud_detailed_id FROM' . $GLOBALS['ecs']->table('order_cloud') . ('WHERE rec_id = \'' . $rec_id . '\' LIMIT 1');
						$order_cloud = $db->getRow($sql);

						if (!empty($order_cloud)) {
							if ($return_type == 0 || $return_type == 2) {
								show_message($_LANG['return_error'], '', '', 'info', true);
							}

							$isRefund = 1;

							if ($return_type == 3) {
								$isRefund = 2;
							}

							$order_return_request = array('isRefund' => intval($isRefund), 'orderDetailId' => intval($order_cloud['cloud_detailed_id']), 'orderInfoId' => intval($order_cloud['cloud_orderid']), 'refundNum' => intval($return_number), 'userReason' => trim($return_brief), 'imgProof1' => '', 'imgProof2' => '', 'imgProof3' => '');
							$sql = 'select img_file from' . $ecs->table('return_images') . (' where rec_id = \'' . $rec_id . '\' and user_id = \'') . $_SESSION['user_id'] . '\' LIMIT 0,3';
							$images_list = $db->getCol($sql);

							if (!empty($images_list)) {
								foreach ($images_list as $k => $v) {
									if ($v) {
										$img = get_image_path(0, $v);
										if (!empty($img) && (strpos($img, 'http://') === false && strpos($img, 'https://') === false && strpos($img, 'errorImg.png') === false)) {
											$img = $ecs->url() . $img;
										}

										$i = $k + 1;
										$order_return_request['imgProof' . $i] = $img;
									}
								}
							}

							$plugin_file = ROOT_PATH . 'plugins/cloudApi/cloudApi.php';

							if (file_exists($plugin_file)) {
								include_once $plugin_file;
								$cloud = new cloud();
								$requ = $cloud->apiAfterSales($order_return_request);

								if ($requ) {
									$requ = json_decode($requ, true);

									if ($requ['code'] != '10000') {
										show_message($requ['message'], '', '', 'info', true);
									}
									else {
										$aftersn = $requ['data']['afterSn'];
									}
								}
								else {
									show_message($_LANG['process_false'], '', '', 'info', true);
								}
							}
						}

						$order_return = array('rec_id' => $rec_id, 'goods_id' => $order_goods['goods_id'], 'order_id' => $order_goods['order_id'], 'order_sn' => $order_goods['goods_sn'], 'chargeoff_status' => $chargeoff_status, 'return_type' => $return_type, 'maintain' => $maintain, 'back' => $back, 'exchange' => $exchange, 'user_id' => $_SESSION['user_id'], 'return_brief' => $return_brief, 'remark' => $return_remark, 'credentials' => !isset($_REQUEST['credentials']) ? 0 : intval($_REQUEST['credentials']), 'country' => empty($_REQUEST['country']) ? 0 : intval($_REQUEST['country']), 'province' => empty($_REQUEST['province']) ? 0 : intval($_REQUEST['province']), 'city' => empty($_REQUEST['city']) ? 0 : intval($_REQUEST['city']), 'district' => empty($_REQUEST['district']) ? 0 : intval($_REQUEST['district']), 'street' => empty($_REQUEST['street']) ? 0 : intval($_REQUEST['street']), 'cause_id' => $last_option, 'apply_time' => gmtime(), 'actual_return' => '', 'address' => empty($_REQUEST['return_address']) ? '' : addslashes(trim($_REQUEST['return_address'])), 'zipcode' => empty($_REQUEST['code']) ? '' : intval($_REQUEST['code']), 'addressee' => empty($_REQUEST['addressee']) ? '' : addslashes(trim($_REQUEST['addressee'])), 'phone' => empty($_REQUEST['mobile']) ? '' : addslashes(trim($_REQUEST['mobile'])), 'return_status' => $return_status);

						if (in_array($return_type, array(1, 3))) {
							$return_info = get_return_refound($order_return['order_id'], $order_return['rec_id'], $return_number);
							$order_return['should_return'] = $return_info['return_price'];
							$order_return['return_shipping_fee'] = $return_info['return_shipping_fee'];
						}
						else {
							$order_return['should_return'] = 0;
							$order_return['return_shipping_fee'] = 0;
						}

						$error_no = 0;

						do {
							$order_return['return_sn'] = get_order_sn();
							$query = $db->autoExecute($ecs->table('order_return'), $order_return, 'INSERT', '', 'SILENT');
							$error_no = $GLOBALS['db']->errno();
							if (0 < $error_no && $error_no != 1062) {
								exit($GLOBALS['db']->errorMsg());
							}
						} while ($error_no == 1062);

						if ($query) {
							$ret_id = $db->insert_id();
							return_action($ret_id, $_LANG['Apply_refund'], '', $order_return['remark'], $_LANG['buyer']);
							$return_goods['rec_id'] = $order_return['rec_id'];
							$return_goods['ret_id'] = $ret_id;
							$return_goods['goods_id'] = $order_goods['goods_id'];
							$return_goods['goods_name'] = $order_goods['goods_name'];
							$return_goods['brand_name'] = $order_goods['brand_name'];
							$return_goods['product_id'] = $order_goods['product_id'];
							$return_goods['goods_sn'] = $order_goods['goods_sn'];
							$return_goods['is_real'] = $order_goods['is_real'];
							$return_goods['goods_attr'] = $attr_val;
							$return_goods['attr_id'] = $return_attr_id;
							$return_goods['refound'] = $order_goods['goods_price'];
							$return_goods['return_type'] = $return_type;
							$return_goods['return_number'] = $return_number;

							if ($return_type == 1) {
								$return_goods['out_attr'] = '';
							}
							else if ($return_type == 2) {
								$return_goods['out_attr'] = $attr_val;
								$return_goods['return_attr_id'] = $return_attr_id;
							}
							else {
								$return_goods['out_attr'] = '';
							}

							$query = $db->autoExecute($ecs->table('return_goods'), $return_goods, 'INSERT', '', 'SILENT');
							$sql = 'select count(*) from' . $ecs->table('return_images') . (' where rec_id = \'' . $rec_id . '\' and user_id = \'') . $_SESSION['user_id'] . '\'';
							$images_count = $db->getOne($sql);

							if (0 < $images_count) {
								$images['rg_id'] = $order_goods['goods_id'];
								$db->autoExecute($ecs->table('return_images'), $images, 'UPDATE', 'rec_id = \'' . $rec_id . '\' and user_id = \'' . $_SESSION['user_id'] . '\'');
							}

							$order_return_extend = array('ret_id' => $ret_id, 'return_number' => $return_number, 'aftersn' => $aftersn);
							$db->autoExecute($ecs->table('order_return_extend'), $order_return_extend, 'INSERT', '', 'SILENT');
							$address_detail = get_user_region_address($order_goods['order_id'], $order_return['address']);
							$order_return['address_detail'] = $address_detail;
							$order_return['apply_time'] = local_date('Y-m-d H:i:s', $order_return['apply_time']);
						}
						else {
							show_message($_LANG['Apply_abnormal'], '', '', 'info', true);
						}
					}

					show_message($_LANG['Apply_Success_Prompt'], $_LANG['See_Returnlist'], 'user.php?act=return_list', 'info', true, $order_return);
				}
			}
			else if ($action == 'return_list') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				include_once ROOT_PATH . 'includes/lib_order.php';
				$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
				$size = 10;
				$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('order_return') . ' WHERE user_id =' . $_SESSION['user_id']);
				$pager = get_pager('user.php', array('act' => $action), $record_count, $page, $size);
				$return_list = return_order($size, $pager['start']);
				$smarty->assign('orders', $return_list);
				$smarty->assign('pager', $pager);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'cancel_return') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				include_once ROOT_PATH . 'includes/lib_order.php';
				$ret_id = isset($_GET['ret_id']) ? intval($_GET['ret_id']) : 0;

				if (cancel_return($ret_id, $user_id)) {
					ecs_header("Location: user.php?act=return_list\n");
					exit();
				}
				else {
					$err->show($_LANG['return_list_lnk'], 'user.php?act=return_list');
				}
			}
			else if ($action == 'return_delivery') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				include_once ROOT_PATH . 'includes/lib_order.php';
				$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

				if (affirm_return_received($order_id, $user_id)) {
					ecs_header("Location: user.php?act=return_list\n");
					exit();
				}
				else {
					$err->show($_LANG['return_list_lnk'], 'user.php?act=return_list');
				}
			}
			else if ($action == 'activation_return_order') {
				include_once 'includes/cls_json.php';
				$json = new JSON();
				$res = array('err_msg' => '', 'result' => '', 'error' => 0);
				$ret_id = isset($_REQUEST['ret_id']) ? intval($_REQUEST['ret_id']) : 0;
				$activation_number_type = 0 < intval($_CFG['activation_number_type']) ? intval($_CFG['activation_number_type']) : 2;
				$sql = 'SELECT activation_number FROM' . $ecs->table('order_return') . (' WHERE ret_id = \'' . $ret_id . '\' LIMIT 1');
				$activation_number = $db->getOne($sql);

				if ($activation_number < $activation_number_type) {
					$sql = 'UPDATE' . $ecs->table('order_return') . (' SET activation_number = activation_number+1,return_status=0 WHERE ret_id = \'' . $ret_id . '\'');
					$db->query($sql);
				}
				else {
					$res['error'] = 1;
					$res['err_msg'] = sprintf($_LANG['activation_number_msg'], $activation_number_type);
				}

				exit($json->encode($res));
			}
			else if ($action == 'return_detail') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				include_once ROOT_PATH . 'includes/lib_order.php';
				$ret_id = isset($_GET['ret_id']) ? intval($_GET['ret_id']) : 0;
				$order = get_return_detail($ret_id);

				if ($order === false) {
					$err->show($_LANG['back_home_lnk'], './');
					exit();
				}

				$region = array($order['country'], $order['province'], $order['city'], $order['district']);
				$shipping_list = available_shipping_list($region, $order['ru_id']);

				foreach ($shipping_list as $key => $val) {
					$shipping_cfg = unserialize_config($val['configure']);
					$shipping_fee = $shipping_count == 0 && $cart_weight_price['free_shipping'] == 1 ? 0 : shipping_fee($val['shipping_code'], unserialize($val['configure']), $cart_weight_price['weight'], $cart_weight_price['amount'], $cart_weight_price['number']);
					$shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);
					$shipping_list[$key]['shipping_fee'] = $shipping_fee;
					$shipping_list[$key]['free_money'] = price_format($shipping_cfg['free_money'], false);
					$shipping_list[$key]['insure_formated'] = strpos($val['insure'], '%') === false ? price_format($val['insure'], false) : $val['insure'];

					if ($val['shipping_id'] == $order['shipping_id']) {
						$insure_disabled = $val['insure'] == 0;
						$cod_disabled = $val['support_cod'] == 0;
					}
				}

				if ($order['return_type'] == 0) {
					if ($order['return_status1'] == 4) {
						$order['refound_status1'] = FF_MAINTENANCE;
					}
					else {
						$order['refound_status1'] = FF_NOMAINTENANCE;
					}
				}
				else if ($order['return_type'] == 1) {
					if ($order['refound_status1'] == 1) {
						$order['refound_status1'] = FF_REFOUND;
					}
					else {
						$order['refound_status1'] = FF_NOREFOUND;
					}
				}
				else if ($order['return_type'] == 2) {
					if ($order['return_status1'] == 4) {
						$order['refound_status1'] = FF_EXCHANGE;
					}
					else {
						$order['refound_status1'] = FF_NOEXCHANGE;
					}
				}

				$getImage = array();
				$smarty->assign('shipping_list', $shipping_list);
				$sql = 'SELECT aftersn FROM' . $GLOBALS['ecs']->table('order_return_extend') . (' WHERE ret_id = \'' . $ret_id . '\' LIMIT 1');
				$aftersn = $db->getOne($sql);
				$return_info = array();

				if ($aftersn) {
					$plugin_file = ROOT_PATH . 'plugins/cloudApi/cloudApi.php';

					if (file_exists($plugin_file)) {
						$store_addres = array('afterSn' => $aftersn);
						include_once $plugin_file;
						$cloud = new cloud();
						$requ = $cloud->getStoreRefundAddress($store_addres);
						$requ = json_decode($requ, true);

						if ($requ['code'] = 10000) {
							$return_info = $requ['data'];
						}
					}
				}

				$smarty->assign('return_info', $return_info);
				$smarty->assign('goods', $order);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'edit_express') {
				$ret_id = empty($_REQUEST['ret_id']) ? '' : intval($_REQUEST['ret_id']);
				$order_id = empty($_REQUEST['order_id']) ? '' : intval($_REQUEST['order_id']);
				$back_shipping_name = empty($_REQUEST['express_name']) ? '' : intval($_REQUEST['express_name']);
				$back_other_shipping = empty($_REQUEST['other_express']) ? '' : $_REQUEST['other_express'];
				$back_invoice_no = empty($_REQUEST['express_sn']) ? '' : $_REQUEST['express_sn'];

				if ($ret_id) {
					$db->query('UPDATE ' . $ecs->table('order_return') . ('SET back_shipping_name = \'' . $back_shipping_name . '\' , back_other_shipping= \'' . $back_other_shipping . '\' , back_invoice_no=\'' . $back_invoice_no . '\' WHERE ret_id = \'' . $ret_id . '\''));
				}

				show_message($_LANG['edit_shipping_success'], $_LANG['return_info'], 'user.php?act=return_detail&order_id=' . $order_id . '&ret_id=' . $ret_id);
			}
			else if ($action == 'ajax_select_cause') {
				require_once ROOT_PATH . 'includes/cls_json.php';
				$json = new JSON();
				$res = array('error' => 0, 'message' => '', 'option' => '', 'rec_id' => 0);
				$c_id = intval($_REQUEST['c_id']);
				$rec_id = intval($_REQUEST['rec_id']);
				if (isset($c_id) && isset($rec_id)) {
					$sql = 'SELECT * FROM ' . $ecs->table('return_cause') . ' WHERE parent_id = ' . $c_id . ' AND is_show = 1 order by sort_order ';
					$result = $db->getAll($sql);

					if ($result) {
						$select = '<select name="last_option" id="last_option_' . $rec_id . '">';

						foreach ($result as $var) {
							$select .= '<option value="' . $var['cause_id'] . '" ';
							$select .= $selected == $var['cause_id'] ? 'selected=\'ture\'' : '';
							$select .= '>';

							if (0 < $var['level']) {
								$select .= str_repeat('&nbsp;', $var['level'] * 4);
							}

							$select .= htmlspecialchars(addslashes($var['cause_name']), ENT_QUOTES) . '</option>';
						}

						$select .= '</select>';
						$res['option'] = $select;
						$res['rec_id'] = $rec_id;
					}

					exit($json->encode($res));
				}
				else {
					$res['error'] = 100;
					$res['message'] = '';
					exit($json->encode($res));
				}
			}
			else if ($action == 'baitiao') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				assign_template();
				$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
				$baitiao_balance = get_baitiao_balance($user_id);
				$bt_info = $baitiao_balance['bt_info'];
				$remain_amount = $baitiao_balance['balance'];
				$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('baitiao_log') . (' WHERE user_id = \'' . $user_id . '\'');
				$record_count = $GLOBALS['db']->getOne($sql);
				$pager = get_pager('user.php', array('act' => $action), $record_count, $page);
				$bt_log = get_baitiao_log_list($user_id, $pager['size'], $pager['start']);
				$remain_amount = floatval($bt_info['amount']) - floatval($repay_bt['total_amount']);
				$smarty->assign('action', 'baitiao');
				$smarty->assign('remain_amount', $remain_amount);
				$smarty->assign('bt_info', $bt_info);
				$smarty->assign('repay_sun_amount', $repay_sun_amount);
				$smarty->assign('repay_bt', $baitiao_balance);
				$smarty->assign('bt_amount', $bt_amount);
				$smarty->assign('bt_logs', $bt_log);
				$smarty->assign('page', $page);
				$smarty->assign('pager', $pager);

				if (defined('THEME_EXTENSION')) {
					$smarty->display('user_transaction.dwt');
				}
				else {
					$smarty->display('user_baitiao.dwt');
				}
			}
			else if ($action == 'baitiao_pay_log') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				assign_template();
				$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
				$log_id = isset($_REQUEST['log_id']) && !empty($_REQUEST['log_id']) ? intval($_REQUEST['log_id']) : 0;
				$where = '1';

				if ($log_id) {
					$where .= ' AND log_id = \'' . $log_id . '\'';
				}
				else {
					$sql = ' SELECT log_id FROM ' . $GLOBALS['ecs']->table('baitiao_log') . (' WHERE user_id = \'' . $user_id . '\' ');
					$log_id = $GLOBALS['db']->getCol($sql);
					$where .= ' AND log_id ' . db_create_in($log_id);
				}

				$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('baitiao_pay_log') . ' WHERE ' . $where;
				$record_count = $GLOBALS['db']->getOne($sql);
				$pager = get_pager('user.php', array('act' => $action), $record_count, $page);
				$pay_list = get_baitiao_pay_log_list($log_id, $pager['size'], $pager['start']);
				$smarty->assign('pay_list', $pay_list);
				$smarty->assign('page', $page);
				$smarty->assign('pager', $pager);
				$where_other = array('user_id' => $user_id);
				$bt_info = get_baitiao_info($where_other);
				$baitiao_balance = get_baitiao_balance($user_id);
				$remain_amount = $baitiao_balance['balance'];
				$smarty->assign('action', 'baitiao_pay_log');
				$smarty->assign('remain_amount', $remain_amount);
				$smarty->assign('bt_info', $bt_info);
				$smarty->assign('repay_sun_amount', $repay_sun_amount);
				$smarty->assign('repay_bt', $baitiao_balance);
				$smarty->assign('bt_amount', $bt_amount);

				if (defined('THEME_EXTENSION')) {
					$smarty->display('user_transaction.dwt');
				}
				else {
					$smarty->display('user_baitiao.dwt');
				}
			}
			else if ($action == 'repay_bt') {
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				include_once ROOT_PATH . 'includes/lib_payment.php';
				include_once ROOT_PATH . 'includes/lib_order.php';
				include_once ROOT_PATH . 'includes/lib_clips.php';
				require ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';
				assign_template();
				get_request_filter();
				$order_id = isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;
				$pay_id = isset($_REQUEST['pay_id']) && !empty($_REQUEST['pay_id']) ? intval($_REQUEST['pay_id']) : 0;
				$stages_num = isset($_REQUEST['stages_num']) && !empty($_REQUEST['stages_num']) ? intval($_REQUEST['stages_num']) : 0;
				if (empty($user_id) || empty($order_id) || empty($pay_id) || empty($stages_num)) {
					show_message($_LANG['payment_coupon'], $_LANG['baitiao'], 'user.php?act=baitiao');
					exit();
				}

				$payment_info = payment_info($pay_id);

				if (empty($payment_info)) {
					ecs_header("Location: ./\n");
					exit();
				}

				$order = order_info($order_id);

				if (empty($order)) {
					ecs_header("Location: ./\n");
					exit();
				}

				if ($user_id != $order['user_id']) {
					show_message($_LANG['payment_coupon'], $_LANG['baitiao'], 'user.php?act=baitiao');
					exit();
				}

				$where_other = array('order_id' => $order['order_id']);
				$stages_info = get_baitiao_log_info($where_other);
				$where_pay_other = array('baitiao_id' => $stages_info['baitiao_id'], 'log_id' => $stages_info['log_id'], 'stages_num' => $stages_num, 'is_pay' => 1);
				$bt_pay_log_info = get_baitiao_pay_log_info($where_pay_other);

				if ($bt_pay_log_info) {
					show_message($_LANG['payment_coupon'] . ',' . $_LANG['baitiao_is_pay'], $_LANG['baitiao'], 'user.php?act=baitiao');
					exit();
				}

				if ($stages_info['stages_total'] == $stages_info['yes_num'] && $stages_info['is_repay'] == 1) {
					show_message($_LANG['payment_coupon'], $_LANG['label_order'], 'user.php');
					exit();
				}

				if ($stages_info['is_stages'] == 1) {
					$order_amount = $stages_info['stages_one_price'];
					$stages_one_price = $order_amount;
					$stages_rate = $db->getOne('SELECT stages_rate FROM ' . $ecs->table('goods') . ' WHERE goods_id=(SELECT goods_id FROM ' . $ecs->table('order_goods') . ' WHERE order_id=\'' . $order['order_id'] . '\')');
				}
				else {
					$order_amount = $order['order_amount'] - $order['pay_fee'];
					$pay_fee = pay_fee($pay_id, $order_amount);
					$order_amount += $pay_fee;
				}

				if ($payment_info['pay_code'] == 'balance' && 0 < $order_amount) {
					$user_info = user_info($_SESSION['user_id']);

					if (0 < $order['surplus']) {
						$order_amount = $order['order_amount'] + $order['surplus'];
						$order['surplus'] = 0;

						if ($order['pay_code'] == 'chunsejinrong') {
							$order_amount = $order['money_paid'];
						}
					}

					$bt_time = gmtime();

					if ($user_info['user_money'] + $user_info['credit_line'] < $order_amount) {
						show_message($_LANG['balance_insufficient']);
					}
					else {
						$user = array();

						if ($stages_info['is_stages'] == 1) {
							log_account_change($_SESSION['user_id'], $order_amount * -1, 0, 0, 0, sprintf($_LANG['Ious_Prompt_one'], $stages_info['yes_num'] + 1, $order['order_sn']));
						}
						else {
							log_account_change($_SESSION['user_id'], $order_amount * -1, 0, 0, 0, sprintf($_LANG['Ious_Prompt_two'], $order['order_sn']));
						}

						if ($stages_info['is_stages'] == 1) {
							$bt_log_sql = 'UPDATE ' . $ecs->table('baitiao_log') . (' SET yes_num = yes_num + 1,repayed_date = \'' . $bt_time . '\' WHERE order_id = \'') . $order['order_id'] . '\'';
							$is_pay_bt = $db->query($bt_log_sql);
							$sql = 'UPDATE ' . $GLOBALS['ecs']->table('stages') . (' SET yes_num = yes_num + 1, repay_date = \'' . $bt_time . '\' WHERE order_sn = \'') . $order['order_sn'] . '\'';
							$GLOBALS['db']->query($sql);
							$sql = 'SELECT stages_total,yes_num,is_repay,order_id FROM ' . $ecs->table('baitiao_log') . ' WHERE order_id=\'' . $order['order_id'] . '\'';
							$stages_info_2 = $db->getRow($sql);
							if ($stages_info_2['stages_total'] == $stages_info_2['yes_num'] && $stages_info_2['is_repay'] == 0) {
								$db->query('UPDATE ' . $ecs->table('baitiao_log') . ' SET is_repay = 1 WHERE order_id = \'' . $stages_info_2['order_id'] . '\'');
							}
						}
						else {
							$bt_log_sql = 'UPDATE ' . $ecs->table('baitiao_log') . (' SET is_repay=1,repayed_date = \'' . $bt_time . '\' WHERE order_id = \'') . $order['order_id'] . '\'';
							$is_pay_bt = $db->query($bt_log_sql);
						}

						if ($is_pay_bt) {
							$sql = 'UPDATE ' . $ecs->table('baitiao_pay_log') . (' SET is_pay = 1, pay_time = \'' . $bt_time . '\' ') . ' WHERE baitiao_id = \'' . $stages_info['baitiao_id'] . '\' ' . ' AND log_id = \'' . $stages_info['log_id'] . ('\' AND stages_num = \'' . $stages_num . '\'');
							$db->query($sql);
							show_message($_LANG['Ious_Payment_success'], $_LANG['my_Ious'], 'user.php?act=baitiao');
						}
						else {
							show_message($_LANG['pay_fail']);
						}

						exit();
					}
				}

				if ($stages_info['is_stages'] != 1) {
					$sql = 'UPDATE ' . $ecs->table('order_info') . (' SET order_amount=\'' . $order_amount . '\'') . (' WHERE order_id = \'' . $order_id . '\'');
				}

				$db->query($sql);
				$order = get_order_detail($order_id, $user_id, true);
				$payment_list = available_payment_list(false, 0, 2);

				foreach ($payment_list as $k => $v) {
					if ($v['pay_name'] == $_LANG['ious_pay']) {
						unset($payment_list[$k]);
					}
				}

				$where_pay_other = array('baitiao_id' => $stages_info['baitiao_id'], 'log_id' => $stages_info['log_id'], 'stages_num' => $stages_num, 'is_pay' => 0);
				$bt_pay_log_info = get_baitiao_pay_log_info($where_pay_other);
				if ($stages_info && $stages_info['is_repay'] == 0) {
					if ($bt_pay_log_info && $bt_pay_log_info['pay_id']) {
						$payment = array('pay_id' => $bt_pay_log_info['pay_id'], 'pay_code' => $bt_pay_log_info['pay_code']);
						$file_pay = ROOT_PATH . 'includes/modules/payment/' . $payment['pay_code'] . '.php';
						if ($payment && file_exists($file_pay)) {
							include_once $file_pay;

							if (class_exists($payment['pay_code'])) {
								$pay_obj = new $payment['pay_code']();
								$is_callable = array($pay_obj, 'query');

								if (is_callable($is_callable)) {
									$order_other = array('order_sn' => $row['order_sn'], 'log_id' => $bt_pay_log_info['id']);
									$pay_obj->query($order_other);
									$sql = 'SELECT is_pay FROM ' . $GLOBALS['ecs']->table('baitiao_pay_log') . ' WHERE id = \'' . $bt_pay_log_info['id'] . '\'';
									$is_pay = $GLOBALS['db']->getOne($sql);

									if ($is_pay == 1) {
										show_message($_LANG['payment_coupon'] . ',' . $_LANG['baitiao_is_pay'], $_LANG['baitiao'], 'user.php?act=baitiao');
										exit();
									}
								}
							}
						}
					}
				}

				$payment_info = payment_info($pay_id);

				if ($payment_info === false) {
					$order['pay_online'] = '';
				}
				else {
					$order['order_amount'] = $order_amount;
					$payment = unserialize_config($payment_info['pay_config']);
					$order['log_id'] = $bt_pay_log_info['id'];

					if ($order['pay_name'] != $_LANG['pay_noline']) {
						include_once ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php';
						$pay_obj = new $payment_info['pay_code']();
						$order['pay_online'] = $pay_obj->get_code($order, $payment);
					}

					$order['pay_desc'] = $payment_info['pay_desc'];
					$order['user_name'] = isset($_SESSION['user_name']) && !empty($_SESSION['user_name']) ? dsc_addslashes($_SESSION['user_name']) : '';
					$sql = 'UPDATE ' . $ecs->table('baitiao_pay_log') . ' SET pay_id = \'' . $payment_info['pay_id'] . '\', pay_code = \'' . $payment_info['pay_code'] . '\' ' . ' WHERE baitiao_id = \'' . $stages_info['baitiao_id'] . '\' AND log_id = \'' . $stages_info['log_id'] . ('\' AND stages_num = \'' . $stages_num . '\'');
					$db->query($sql);
				}

				$smarty->assign('payment_info', $payment_info);
				$smarty->assign('action', 'repay_bt');
				$smarty->assign('order', $order);
				$smarty->assign('stages_info', $stages_info);
				$smarty->assign('stages_rate', $stages_rate);
				$smarty->assign('stages_one_price', $stages_one_price);
				$smarty->assign('payment_list', $payment_list);
				$smarty->assign('stages_num', $stages_num);

				if (defined('THEME_EXTENSION')) {
					$smarty->display('user_transaction.dwt');
				}
				else {
					$smarty->display('user_baitiao.dwt');
				}
			}
			else if ($action == 'checkorder') {
				include_once 'includes/cls_json.php';
				$json = new JSON();
				$pay_code = isset($_GET['pay_code']) ? dsc_addslashes($_GET['pay_code']) : '';
				$baitiao_id = isset($_GET['baitiao_id']) ? intval($_GET['baitiao_id']) : 0;
				$log_id = isset($_GET['log_id']) ? intval($_GET['log_id']) : 0;
				$stages_num = isset($_GET['stages_num']) ? intval($_GET['stages_num']) : 0;

				if ($baitiao_id) {
					$where_pay_other = array('baitiao_id' => $baitiao_id, 'log_id' => $log_id, 'stages_num' => $stages_num, 'is_pay' => 1);
					$bt_pay_log_info = get_baitiao_pay_log_info($where_pay_other);

					if ($bt_pay_log_info) {
						$result = array('code' => 1, 'pay_code' => $pay_code);
					}
					else {
						$result = array('code' => 0, 'pay_code' => $pay_code);
					}
				}
				else {
					$sql = ' SELECT log_id FROM ' . $ecs->table('pay_log') . (' WHERE log_id = \'' . $log_id . '\' AND is_pay = 1 ');

					if ($db->getOne($sql)) {
						$result = array('code' => 1, 'pay_code' => $pay_code);
					}
					else {
						$result = array('code' => 0, 'pay_code' => $pay_code);
					}
				}

				echo $json->encode($result);
				exit();
			}
			else if ($action == 'merchants_upgrade') {
				$smarty->assign('action', $action);
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				$position = assign_ur_here(0, $_LANG['seller_garde']);
				get_invalid_apply();
				$seller_grader = get_seller_grade($user_id);
				$smarty->assign('grade_id', $seller_grader['grade_id']);
				$smarty->assign('is_expiry', judge_seller_grade_expiry($user_id));
				$smarty->assign('page_title', $position['title']);
				$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
				$smarty->assign('page', $page);
				$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('seller_grade') . ' WHERE is_open = 1';
				$record_count = $GLOBALS['db']->getOne($sql);
				$pager = get_pager('user.php', array('act' => $action), $record_count, $page);
				$seller_grade = get_seller_grade_info($pager['size'], $pager['start']);
				$smarty->assign('seller_grade', $seller_grade);
				$smarty->assign('pager', $pager);
				$smarty->display('user_transaction.dwt');
			}
			else if ($action == 'merchants_upgrade_log') {
				$smarty->assign('action', $action);
				include_once ROOT_PATH . 'includes/lib_transaction.php';
				$position = assign_ur_here(0, $_LANG['merchants_upgrade_log']);
				get_invalid_apply();
				$seller_grader = get_seller_grade($user_id);
				$smarty->assign('grade_id', $seller_grader['grade_id']);
				$smarty->assign('page_title', $position['title']);
				$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
				$smarty->assign('page', $page);
				$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('seller_apply_info') . ('WHERE ru_id = \'' . $user_id . '\'');
				$record_count = $GLOBALS['db']->getOne($sql);
				$pager = get_pager('user.php', array('act' => $action), $record_count, $page);
				$merchants_upgrade_log = get_merchants_upgrade_log($pager['size'], $pager['start']);
				$smarty->assign('merchants_upgrade_log', $merchants_upgrade_log);
				$smarty->assign('pager', $pager);
				$smarty->display('user_transaction.dwt');
			}
			else {
				if ($action == 'application_grade' || $action == 'application_grade_edit') {
					$smarty->assign('action', $action);
					include_once ROOT_PATH . 'includes/lib_transaction.php';
					include_once ROOT_PATH . 'includes/lib_payment.php';
					include_once ROOT_PATH . 'includes/lib_order.php';
					$position = assign_ur_here(0, $_LANG['seller_garde']);
					$smarty->assign('page_title', $position['title']);
					$grade_id = !empty($_REQUEST['grade_id']) ? intval($_REQUEST['grade_id']) : 0;
					$smarty->assign('grade_id', $grade_id);
					$seller_grade = get_seller_grade($_SESSION['user_id']);

					if ($seller_grade) {
						$seller_grade['end_time'] = date('Y', $seller_grade['add_time']) + $seller_grade['year_num'] . '-' . date('m-d H:i:s', $seller_grade['add_time']);
						$seller_grade['addtime'] = date('Y-m-d H:i:s', $seller_grade['add_time']);

						if (0 < $seller_grade['amount']) {
							$rest = (gmtime() - $seller_grade['add_time']) / (strtotime($seller_grade['end_time']) - $seller_grade['add_time']);
							$seller_grade['refund_price'] = round($seller_grade['amount'] - $seller_grade['amount'] * $rest, 2);
						}

						$smarty->assign('seller_grade', $seller_grade);
					}

					if ($action == 'application_grade_edit') {
						$apply_id = !empty($_REQUEST['apply_id']) ? intval($_REQUEST['apply_id']) : 0;
						$seller_apply_info = $db->getRow('SELECT * FROM' . $ecs->table('seller_apply_info') . (' WHERE apply_id = \'' . $apply_id . '\' LIMIT 1'));
						$apply_criteria = unserialize($seller_apply_info['entry_criteria']);
						if (0 < $seller_apply_info['pay_id'] && $seller_apply_info['is_paid'] == 0 && $seller_apply_info['pay_status'] == 0) {
							include_once ROOT_PATH . 'includes/lib_clips.php';
							$payment_info = array();
							$payment_info = payment_info($seller_apply_info['pay_id']);

							if ($payment_info === false) {
								$seller_apply_info['pay_online'] = '';
							}
							else if (substr($payment_info['pay_code'], 0, 4) == 'pay_') {
								$seller_apply_info['pay_online'] = '';
							}
							else {
								$payment = unserialize_config($payment_info['pay_config']);
								$apply['log_id'] = get_paylog_id($seller_apply_info['allpy_id'], $pay_type = PAY_APPLYGRADE);
								$amount = $seller_apply_info['total_amount'];
								$apply['order_sn'] = $seller_apply_info['apply_sn'];
								$apply['user_id'] = $seller_apply_info['ru_id'];
								$apply['surplus_amount'] = $amount;
								$payment_info['pay_fee'] = pay_fee($pay_id, $apply['surplus_amount'], 0);
								$apply['order_amount'] = $amount + $payment_info['pay_fee'];
								include_once ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php';
								$pay_obj = new $payment_info['pay_code']();
								$seller_apply_info['pay_online'] = $pay_obj->get_code($apply, $payment);
							}
						}

						$act = 'update_submit';
						$smarty->assign('apply_criteria', $apply_criteria);
						$smarty->assign('seller_apply_info', $seller_apply_info);
					}
					else {
						$act = 'confirm_inventory';
						$sql = 'SELECT apply_id FROM ' . $ecs->table('seller_apply_info') . ' WHERE ru_id = \'' . $user_id . '\' AND apply_status = 0 AND is_paid = 0  LIMIT 1';

						if ($db->getRow($sql)) {
							show_message($_LANG['invalid_apply']);
						}
					}

					$grade_info = $db->getRow('SELECT entry_criteria,grade_name FROM ' . $ecs->table('seller_grade') . (' WHERE id = \'' . $grade_id . '\''));
					$entry_criteriat_info = get_entry_criteria($grade_info['entry_criteria']);
					$smarty->assign('entry_criteriat_info', $entry_criteriat_info);
					$pay = available_payment_list(0);
					$smarty->assign('pay', $pay);
					$smarty->assign('act', $act);
					unset($_SESSION['grade_reload'][$_SESSION['user_id']]);
					set_prevent_token('grade_cookie');
					$smarty->assign('grade_name', $grade_info['grade_name']);
					$smarty->display('user_transaction.dwt');
				}
				else {
					if ($action == 'confirm_inventory' || $action == 'update_submit') {
						$smarty->assign('action', $action);

						if (get_prevent_token('grade_cookie') == 1) {
							header("Location:user.php?act=grade_load\n");
							exit();
						}

						include_once ROOT_PATH . 'includes/lib_transaction.php';
						include_once ROOT_PATH . 'includes/lib_payment.php';
						include_once ROOT_PATH . 'includes/lib_order.php';
						include_once ROOT_PATH . 'includes/lib_clips.php';
						$grade_id = !empty($_REQUEST['grade_id']) ? intval($_REQUEST['grade_id']) : 0;
						$pay_id = !empty($_REQUEST['pay_id']) ? intval($_REQUEST['pay_id']) : 0;
						$entry_criteria = !empty($_REQUEST['value']) ? $_REQUEST['value'] : array();
						$file_id = !empty($_REQUEST['file_id']) ? $_REQUEST['file_id'] : array();
						$fee_num = !empty($_REQUEST['fee_num']) ? intval($_REQUEST['fee_num']) : 1;
						$all_count_charge = !empty($_REQUEST['all_count_charge']) ? round($_REQUEST['all_count_charge'], 2) : 0;
						$refund_price = !empty($_REQUEST['refund_price']) ? $_REQUEST['refund_price'] : 0;
						$file_url = !empty($_REQUEST['file_url']) ? $_REQUEST['file_url'] : array();
						$apply_info = array();
						$back_price = 0;
						$payable_amount = 0;

						if (0 < $refund_price) {
							if ($_CFG['apply_options'] == 1) {
								if ($all_count_charge < $refund_price) {
									$payable_amount = 0;
									$back_price = $refund_price - $all_count_charge;
								}
								else {
									$payable_amount = $all_count_charge - $refund_price;
								}
							}
							else if ($_CFG['apply_options'] == 2) {
								if ($all_count_charge < $refund_price) {
									$payable_amount = 0;
									$back_price = 0;
								}
								else {
									$payable_amount = $all_count_charge - $refund_price;
								}
							}
						}
						else {
							$payable_amount = $all_count_charge;
						}

						$payment_info = array();
						$payment_info = payment_info($pay_id);
						$payment_info['pay_fee'] = pay_fee($pay_id, $payable_amount, 0);
						$apply_info['order_amount'] = $payable_amount + $payment_info['pay_fee'];
						$php_maxsize = ini_get('upload_max_filesize');
						$htm_maxsize = '2M';
						$img_url = array();

						if ($_FILES['value']) {
							foreach ($_FILES['value']['error'] as $key => $value) {
								if ($value == 0) {
									if (!$image->check_img_type($_FILES['value']['type'][$key])) {
										$massege = sprintf($_LANG['invalid_img_val'], $key + 1);
									}
									else {
										$goods_pre = 1;
									}
								}
								else if ($value == 1) {
									$massege = sprintf($_LANG['img_url_too_big'], $key + 1, $php_maxsize);
								}
								else if ($_FILES['img_url']['error'] == 2) {
									$massege = sprintf($_LANG['img_url_too_big'], $key + 1, $htm_maxsize);
								}

								if ($massege) {
									show_message($massege);
								}
							}

							if ($goods_pre == 1) {
								$res = upload_apply_file($_FILES['value'], $file_id, $file_url);

								if ($res != false) {
									$img_url = $res;
								}
								else {
									$img_url = $file_url;
								}
							}
							else {
								$img_url = $file_url;
							}
						}
						else {
							$img_url = $file_url;
						}

						if ($img_url) {
							$valus = serialize($entry_criteria + $img_url);
						}
						else {
							$valus = serialize($entry_criteria);
						}

						if ($action == 'confirm_inventory') {
							$apply_sn = get_order_sn();
							$time = gmtime();
							$key = '(`ru_id`,`grade_id`,`apply_sn`,`total_amount`,`pay_fee`,`fee_num`,`entry_criteria`,`add_time`,`pay_id`,`refund_price`,`back_price`,`payable_amount`)';
							$value = '(\'' . $user_id . '\',\'' . $grade_id . '\',\'' . $apply_sn . '\',\'' . $all_count_charge . '\',\'' . $payment_info['pay_fee'] . '\',\'' . $fee_num . '\',\'' . $valus . '\',\'' . $time . '\',\'' . $pay_id . '\',\'' . $refund_price . ('\',\'' . $back_price . '\',\'' . $payable_amount . '\')');
							$sql = 'INSERT INTO' . $ecs->table('seller_apply_info') . $key . ' VALUES' . $value;
							$db->query($sql);
							$apply_id = $db->insert_id();
							$apply_info['log_id'] = insert_pay_log($apply_id, $apply_info['order_amount'], $type = PAY_APPLYGRADE, 0);
						}
						else {
							$apply_sn = !empty($_REQUEST['apply_sn']) ? $_REQUEST['apply_sn'] : 0;
							$apply_id = !empty($_REQUEST['apply_id']) ? intval($_REQUEST['apply_id']) : 0;

							if ($action == 'update_submit') {
								$sql = 'SELECT pay_status FROM' . $ecs->table('seller_apply_info') . ('WHERE apply_id = \'' . $apply_id . '\' limit 1');

								if ($db->getOne($sql) == 1) {
									show_message('该申请已完成支付，不能进行此操作！');
								}
							}

							$sql = 'UPDATE' . $ecs->table('seller_apply_info') . (' SET payable_amount = \'' . $payable_amount . '\', back_price = \'' . $back_price . '\', total_amount = \'' . $all_count_charge . '\',pay_fee=\'' . $payment_info['pay_fee'] . '\',fee_num = \'' . $fee_num . '\',entry_criteria=\'' . $valus . '\',pay_id=\'' . $pay_id . '\' WHERE apply_id = \'' . $apply_id . '\' AND apply_sn = \'' . $apply_sn . '\'');
							$db->query($sql);
							$apply_info['log_id'] = get_paylog_id($apply_id, $pay_type = PAY_APPLYGRADE);
						}

						if (0 < $pay_id && 0 < $payable_amount) {
							$payment = unserialize_config($payment_info['pay_config']);
							$apply_info['order_sn'] = $apply_sn;
							$apply_info['user_id'] = $user_id;
							$apply_info['surplus_amount'] = $payable_amount;

							if ($payment_info['pay_code'] == 'balance') {
								$user_money = $db->getOne('SELECT user_money FROM ' . $ecs->table('users') . ' WHERE user_id=\'' . $user_id . '\'');

								if ($payable_amount < $user_money) {
									$sql = ' UPDATE ' . $ecs->table('seller_apply_info') . ' SET is_paid = 1 ,pay_time = \'' . gmtime() . '\' ,pay_status = 1 WHERE apply_id= \'' . $apply_id . '\'';
									$db->query($sql);
									$sql = 'UPDATE ' . $ecs->table('pay_log') . 'SET is_paid = 1 WHERE order_id = \'' . $apply_id . '\' AND order_type = \'' . PAY_APPLYGRADE . '\'';
									$db->query($sql);
									log_account_change($user_id, $payable_amount * -1, 0, 0, 0, sprintf($_LANG['seller_apply'], $apply_sn));
								}
								else {
									show_message($_LANG['balance_insufficient']);
								}
							}
							else {
								include_once ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php';
								$pay_obj = new $payment_info['pay_code']();
								$payment_info['pay_button'] = $pay_obj->get_code($apply_info, $payment);
							}

							$smarty->assign('payment', $payment_info);
							$smarty->assign('pay_fee', price_format($payment_info['pay_fee'], false));
							$smarty->assign('amount', price_format($payable_amount, false));
							$smarty->assign('order', $apply_info);
							$grade_reload['apply_id'] = $apply_id;
							$_SESSION['grade_reload'][$_SESSION['user_id']] = $grade_reload;
							set_prevent_token('grade_cookie');
							$smarty->display('user_transaction.dwt');
						}
						else {
							show_message($_LANG['apply_success']);
						}
					}
					else if ($action == 'grade_load') {
						$smarty->assign('action', $action);
						include_once ROOT_PATH . 'includes/lib_transaction.php';
						include_once ROOT_PATH . 'includes/lib_payment.php';
						include_once ROOT_PATH . 'includes/lib_clips.php';
						include_once ROOT_PATH . 'includes/lib_order.php';
						$position = assign_ur_here(0, $_LANG['seller_garde']);
						$smarty->assign('page_title', $position['title']);
						$apply_id = $_SESSION['grade_reload'][$_SESSION['user_id']]['apply_id'];

						if (0 < $apply_id) {
							$sql = 'SELECT apply_sn,pay_fee,pay_id,payable_amount FROM ' . $ecs->table('seller_apply_info') . (' WHERE ru_id = \'' . $user_id . '\' AND apply_id = \'' . $apply_id . '\'');
							$seller_apply_info = $db->getRow($sql);

							if (!empty($seller_apply_info)) {
								if (0 < $seller_apply_info['pay_id'] && 0 < $seller_apply_info['payable_amount']) {
									$payment_info = array();
									$payment_info = payment_info($seller_apply_info['pay_id']);
									$payment_info['pay_fee'] = $seller_apply_info['pay_fee'];
									$apply_info['order_amount'] = $seller_apply_info['payable_amount'] + $payment_info['pay_fee'];
									$apply_info['log_id'] = get_paylog_id($apply_id, $pay_type = PAY_APPLYGRADE);
									$payment = unserialize_config($payment_info['pay_config']);
									$apply_info['order_sn'] = $seller_apply_info['apply_sn'];
									$apply_info['user_id'] = $user_id;
									$apply_info['surplus_amount'] = $seller_apply_info['payable_amount'];

									if ($payment_info['pay_code'] == 'balance') {
										$user_money = $db->getOne('SELECT user_money FROM ' . $ecs->table('users') . ' WHERE user_id=\'' . $user_id . '\'');

										if ($seller_apply_info['payable_amount'] < $user_money) {
											$sql = ' UPDATE ' . $ecs->table('seller_apply_info') . ' SET is_paid = 1 ,pay_time = \'' . gmtime() . '\' ,pay_status = 1 WHERE apply_id= \'' . $apply_id . '\'';
											$db->query($sql);
											$sql = 'UPDATE ' . $ecs->table('pay_log') . 'SET is_paid = 1 WHERE order_id = \'' . $apply_id . '\' AND order_type = \'' . PAY_APPLYGRADE . '\'';
											$db->query($sql);
											log_account_change($user_id, $seller_apply_info['payable_amount'] * -1, 0, 0, 0, sprintf($_LANG['seller_apply'], $seller_apply_info['apply_sn']));
										}
										else {
											show_message($_LANG['balance_insufficient']);
										}
									}
									else {
										include_once ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php';
										$pay_obj = new $payment_info['pay_code']();
										$payment_info['pay_button'] = $pay_obj->get_code($apply_info, $payment);
									}

									$smarty->assign('payment', $payment_info);
									$smarty->assign('pay_fee', price_format($payment_info['pay_fee'], false));
									$smarty->assign('amount', price_format($seller_apply_info['payable_amount'], false));
									$smarty->assign('order', $apply_info);
									$smarty->display('user_transaction.dwt');
								}
								else {
									show_message($_LANG['apply_success']);
								}
							}
							else {
								show_message('系统错误，请重试');
							}
						}
						else {
							show_message('系统错误，请重试');
						}
					}
					else if ($action == 'remove_apply_info') {
						$id = intval($_GET['id']);
						$entry_criteria = unserialize($db->getRow('SELECT entry_criteria FROM ' . $ecs->table('seller_apply_info') . (' WHERE apply_id = \'' . $id . '\' LIMIT 1')));

						foreach ($entry_criteria as $k => $v) {
							$type = $db->getOne(' SELECT type FROM' . $ecs->table('entry_criteria') . (' WHERE id = \'' . $k . '\''));
							if ($type == 'file' && $v != '') {
								@unlink(ROOT_PATH . $v);
							}
						}

						$sql = 'DELETE FROM' . $ecs->table('seller_apply_info') . ('WHERE apply_id = \'' . $id . '\'');
						$db->query($sql);
						show_message('删除成功', $_LANG['back'], 'user.php?act=merchants_upgrade_log');
					}
					else if ($action == 'account_safe') {
						$_REQUEST['type'] = isset($_REQUEST['type']) && !empty($_REQUEST['type']) ? dsc_addslashes(trim($_REQUEST['type'])) : '';
						$_GET['type'] = isset($_GET['type']) && !empty($_GET['type']) ? dsc_addslashes(trim($_GET['type'])) : '';
						$_REQUEST['verify'] = isset($_REQUEST['verify']) && !empty($_REQUEST['verify']) ? dsc_addslashes($_REQUEST['verify']) : '';
						$_REQUEST['type'] = $_REQUEST['type'] == strtolower($_GET['type']) ? $_GET['type'] : $_REQUEST['type'];
						$_POST = get_request_filter($_POST, 1);
						$type = empty($_REQUEST['type']) ? 'default' : trim($_REQUEST['type']);
						$step = empty($_REQUEST['step']) ? 'first' : trim($_REQUEST['step']);
						$vali_info = $db->getRow('SELECT is_validated, mobile_phone, email FROM ' . $ecs->table('users') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1'));
						$validate_info = get_validate_info($user_id);
						if (empty($validate_info['is_validated']) && empty($validate_info['mobile_phone']) && empty($validate_info['pay_password'])) {
							if ($_CFG['user_phone'] == 1 || $_CFG['user_login_register'] == 0) {
								$sign = 'mobile';
							}
							else {
								$sign = 'email';
							}
						}
						else {
							$sign = '';
							if ($validate_info['mobile_phone'] && $_REQUEST['type'] == 'change_phone') {
								$sign = 'mobile';
							}
							else {
								if ($validate_info['is_validated'] && $_REQUEST['type'] == 'change_email') {
									$sign = 'email';
								}
								else {
									if ($_CFG['user_phone'] == 1 || $_CFG['user_login_register'] == 0) {
										$sign = 'mobile';
									}
									else {
										$sign = 'email';
									}
								}
							}
						}

						$sign = isset($_REQUEST['sign']) && !empty($_REQUEST['sign']) ? trim($_REQUEST['sign']) : $sign;
						include_once ROOT_PATH . 'includes/lib_transaction.php';
						$user_info = get_profile($user_id);
						$smarty->assign('user_info', $user_info);
						$smarty->assign('validate_info', $validate_info);

						if ($type == 'validated_email') {
							$hash = empty($_GET['hash']) ? '' : trim($_GET['hash']);
							$mail_type = empty($_GET['mail_type']) ? '' : trim($_GET['mail_type']);

							if ($hash) {
								include_once ROOT_PATH . 'includes/lib_passport.php';
								$id = register_hash('decode', $hash);

								if (0 < $id) {
									switch ($mail_type) {
									case 'change_pwd':
										$Loaction = 'user.php?act=account_safe&type=change_password&step=second&sign=email&hash=' . $hash;
										break;

									case 'change_mail':
										$Loaction = 'user.php?act=account_safe&type=change_email&step=second&sign=email&hash=' . $hash;
										break;

									case 'change_mobile':
										$Loaction = 'user.php?act=account_safe&type=change_phone&step=second&sign=email&hash=' . $hash;
										break;

									case 'change_paypwd':
										$Loaction = 'user.php?act=account_safe&type=payment_password&step=second&sign=email&hash=' . $hash;
										break;

									case 'editmail':
										$new_mail = $_SESSION['new_email' . $user_id];

										if (empty($new_mail)) {
											$Loaction = 'user.php?act=account_safe&type=change_email';
											ecs_header('Location: ' . $Loaction . "\n");
										}

										$validated = isset($_REQUEST['validated']) ? $_REQUEST['validated'] : 0;

										if (!empty($validated)) {
											$validated = '&validated=1';
										}
										else {
											$validated = '';
										}

										$sql = 'UPDATE ' . $ecs->table('users') . (' SET email = \'' . $new_mail . '\', is_validated = 1 WHERE user_id=\'' . $id . '\'');
										$db->query($sql);
										users_log_change($_SESSION['user_id'], USER_EMAIL);
										$Loaction = 'user.php?act=account_safe&type=change_email&step=last&sign=editmail_ok' . $validated . '&hash=' . $hash;
										break;

									default:
										break;
									}

									ecs_header('Location: ' . $Loaction . "\n");
								}
							}

							show_message($_LANG['validate_fail']);
						}

						if ($type == 'change_password') {
							if ($_REQUEST['verify'] == 'authcode') {
								$authcode = $_REQUEST['authCode'];
								$seKey = 'change_password_f';
								$verify = new Verify();
								$captcha_code = $verify->check($authcode, $seKey, '', 'ajax');
								include_once ROOT_PATH . 'includes/cls_json.php';
								include_once ROOT_PATH . 'includes/lib_passport.php';
								$json = new JSON();
								$error = true;

								if (!$captcha_code) {
									$error = false;
								}

								exit($json->encode($error));
							}
							else if ($_REQUEST['verify'] == 'mobilecode') {
								if (!empty($_REQUEST['mobile_code'])) {
									include_once ROOT_PATH . 'includes/cls_json.php';
									include_once ROOT_PATH . 'includes/lib_passport.php';
									$json = new JSON();
									$error = true;

									if ($_REQUEST['mobile_code'] != $_SESSION['sms_mobile_code']) {
										$error = false;
									}

									exit(json_encode($error));
								}
							}
							else if ($_REQUEST['verify'] == 'pay_pwd') {
								if (!empty($_REQUEST['payPwd'])) {
									include_once ROOT_PATH . 'includes/cls_json.php';
									include_once ROOT_PATH . 'includes/lib_passport.php';
									$json = new JSON();
									$error = true;
									$pay_password = $_REQUEST['payPwd'];
									$row = $db->getRow('SELECT ec_salt, pay_password FROM ' . $ecs->table('users_paypwd') . (' WHERE user_id = \'' . $user_id . '\' '));
									$new_password = md5(md5($pay_password) . $row['ec_salt']);

									if ($new_password != $row['pay_password']) {
										$error = false;
									}

									exit(json_encode($error));
								}
							}

							if ($step == 'first') {
								if ($sign == 'mobile') {
									if (empty($user_info['mobile_phone'])) {
										$Loaction = 'user.php?act=account_safe&type=change_phone';
										ecs_header('Location: ' . $Loaction . "\n");
									}

									if (0 < intval($_CFG['sms_signin'])) {
										$sms_security_code = $_SESSION['sms_security_code'] = rand(1000, 9999);
										$smarty->assign('sms_security_code', $sms_security_code);
										$smarty->assign('enabled_sms_signin', 1);
									}
								}
								else if ($sign == 'email') {
									$is_validated = $db->getOne('SELECT is_validated FROM ' . $ecs->table('users') . (' WHERE user_id = \'' . $user_id . '\' '));

									if (empty($is_validated)) {
										$Loaction = 'user.php?act=account_safe&type=change_email';
										ecs_header('Location: ' . $Loaction . "\n");
									}
								}
								else if ($sign == 'paypwd') {
									$pay_password = $db->getOne('SELECT pay_password FROM ' . $ecs->table('users_paypwd') . (' WHERE user_id = \'' . $user_id . '\' '));

									if (empty($pay_password)) {
										$Loaction = 'user.php?act=account_safe&type=payment_password';
										ecs_header('Location: ' . $Loaction . "\n");
									}
								}
								else if ($sign == 'validate_mail_ok') {
								}
							}
							else if ($step == 'second') {
								if ($sign == 'mobile') {
									if (intval($_CFG['captcha']) && 0 < gd_version()) {
										$captcha = isset($_POST['authCode']) ? trim($_POST['authCode']) : '';

										if (empty($captcha)) {
											show_message($_LANG['msg_identifying_code'], $_LANG['back_input'], '', 'error');
										}

										$seKey = 'change_password_f';
										$verify = new Verify();
										$captcha_code = $verify->check($captcha, $seKey);

										if (!$captcha_code) {
											show_message($_LANG['invalid_captcha'], $_LANG['back_input_Code'], '', 'error');
										}
									}

									if (!empty($_POST['mobile_phone'])) {
										if (empty($_POST['mobile_code'])) {
											show_message($_LANG['Mobile_code_null'], $_LANG['back_input'], '', 'error');
										}

										$mobile = $GLOBALS['db']->getOne('SELECT mobile_phone FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id=\'' . $user_id . '\''));
										if ($mobile == $_POST['mobile_phone'] && $_CFG['sms_signin'] == 1) {
											if (!empty($_POST['mobile_code'])) {
												if ($_POST['mobile_phone'] != $_SESSION['sms_mobile'] || $_POST['mobile_code'] != $_SESSION['sms_mobile_code']) {
													show_message($_LANG['Mobile_code_fail'], $_LANG['back_input_Code'], '', 'error');
												}
											}
										}
										else {
											if ($mobile != $_POST['mobile_phone'] && $_CFG['sms_signin'] == 1) {
												show_message($_LANG['Real_name_authentication_Mobile_one'], $_LANG['back_input_Code'], '', 'error');
											}
										}
									}
									else {
										show_message($_LANG['Real_name_authentication_Mobile_two'], $_LANG['back_input_Code'], '', 'error');
									}
								}
								else if ($sign == 'email') {
									$hash = empty($_GET['hash']) ? '' : trim($_GET['hash']);

									if ($hash) {
										include_once ROOT_PATH . 'includes/lib_passport.php';
										$id = register_hash('decode', $hash);

										if ($id <= 0) {
											show_message($_LANG['validate_fail'], $_LANG['back'], 'index.php');
										}
									}
									else {
										show_message($_LANG['validate_fail'], $_LANG['back'], 'index.php');
									}
								}
								else if ($sign == 'paypwd') {
									if (intval($_CFG['captcha']) && 0 < gd_version()) {
										$captcha = isset($_POST['authCode']) ? trim($_POST['authCode']) : '';

										if (empty($captcha)) {
											show_message($_LANG['msg_identifying_code'], $_LANG['back_input'], '', 'error');
										}

										$seKey = 'change_password_f';
										$verify = new Verify();
										$captcha_code = $verify->check($captcha, $seKey);

										if (!$captcha_code) {
											show_message($_LANG['invalid_captcha'], $_LANG['back_input_Code'], '', 'error');
										}
									}

									$pay_password = $_REQUEST['pay_password'];
									$row = $db->getRow('SELECT ec_salt, pay_password FROM ' . $ecs->table('users_paypwd') . (' WHERE user_id = \'' . $user_id . '\' '));
									$new_password = md5(md5($pay_password) . $row['ec_salt']);

									if ($new_password != $row['pay_password']) {
										show_message($_LANG['pay_password_packup_error'], $_LANG['back_input_Code'], '', 'error');
									}
								}
								else {
									show_message($_LANG['permissions_null'], 'index.php', '', 'error');
								}
							}
							else if ($step == 'last') {
								if (intval($_CFG['captcha']) && 0 < gd_version()) {
									if (empty($_POST['authCode'])) {
										show_message($_LANG['msg_identifying_code'], $_LANG['back_input'], '', 'error');
									}

									$captcha = isset($_POST['authCode']) ? trim($_POST['authCode']) : '';
									$seKey = 'change_password_f';
									$verify = new Verify();
									$captcha_code = $verify->check($captcha, $seKey);

									if (!$captcha_code) {
										show_message($_LANG['invalid_captcha'], $_LANG['back_input'], '', 'error');
									}
								}

								if (!empty($_POST['new_password']) && trim($_POST['new_password']) != trim($_POST['re_new_password'])) {
									show_message($_LANG['Real_name_authentication_Mobile_three'], $_LANG['back_input'], '', 'error');
								}

								$cfg = array('user_id' => $user_id, 'username' => $GLOBALS['db']->getOne('SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id=\'' . $user_id . '\'')), 'password' => trim($_POST['new_password']));

								if (!$GLOBALS['user']->edit_user($cfg, 1)) {
									show_message('DB ERROR', $_LANG['back'], '');
								}

								if (!empty($_POST['new_password'])) {
									$sql = 'UPDATE ' . $ecs->table('users') . ('SET `ec_salt`=\'0\' WHERE user_id= \'' . $user_id . '\'');
									$db->query($sql);
									$user->logout();
									$ucdata = empty($user->ucdata) ? '' : $user->ucdata;
									users_log_change($user_id, USER_LPASS);
									show_message('修改密码成功，重新登录' . $ucdata, '去登陆', 'user.php?act=login');
									$smarty->assign('security_rating', security_rating());
								}
							}
						}
						else if ($type == 'change_email') {
							if ($step == 'first') {
								if ($sign == 'mobile') {
									if (empty($user_info['mobile_phone'])) {
										$Loaction = 'user.php?act=account_safe&type=change_phone';
										ecs_header('Location: ' . $Loaction . "\n");
									}

									if (0 < intval($_CFG['sms_signin'])) {
										$sms_security_code = $_SESSION['sms_security_code'] = rand(1000, 9999);
										$smarty->assign('sms_security_code', $sms_security_code);
										$smarty->assign('enabled_sms_signin', 1);
									}
								}
								else if ($sign == 'email') {
									if ($_CFG['user_phone'] == 1) {
										$is_validated = $db->getOne('SELECT is_validated FROM ' . $ecs->table('users') . (' WHERE user_id = \'' . $user_id . '\' '));

										if (empty($is_validated)) {
											$Loaction = 'user.php?act=account_safe&type=change_email';
											ecs_header('Location: ' . $Loaction . "\n");
										}
									}
									else {
										$is_validated = $db->getOne('SELECT is_validated FROM ' . $ecs->table('users') . (' WHERE user_id = \'' . $user_id . '\' '));
										$smarty->assign('is_validated', $is_validated);
									}
								}
								else if ($sign == 'paypwd') {
									$pay_password = $db->getOne('SELECT pay_password FROM ' . $ecs->table('users_paypwd') . (' WHERE user_id = \'' . $user_id . '\' '));

									if (empty($pay_password)) {
										$Loaction = 'user.php?act=account_safe&type=payment_password';
										ecs_header('Location: ' . $Loaction . "\n");
									}
								}
								else if ($sign == 'validate_mail_ok') {
								}
							}
							else if ($step == 'second') {
								if ($sign == 'mobile') {
									$user_email = $GLOBALS['db']->getOne('SELECT email FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $user_id . '\''));
									$smarty->assign('user_email', $user_email);
									if (intval($_CFG['captcha']) && 0 < gd_version()) {
										$captcha = isset($_POST['authCode']) ? trim($_POST['authCode']) : '';

										if (empty($captcha)) {
											show_message($_LANG['msg_identifying_code'], $_LANG['back_input'], '', 'error');
										}

										$seKey = 'change_password_f';
										$verify = new Verify();
										$captcha_code = $verify->check($captcha, $seKey);

										if (!$captcha_code) {
											show_message($_LANG['invalid_captcha'], $_LANG['back_input_Code'], '', 'error');
										}
									}

									if (!empty($_POST['mobile_phone'])) {
										if (empty($_POST['mobile_code'])) {
											show_message('手机验证码不可为空', $_LANG['back_input'], '', 'error');
										}

										$mobile = $GLOBALS['db']->getOne('SELECT mobile_phone FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id=\'' . $user_id . '\''));
										if ($mobile == $_POST['mobile_phone'] && $_CFG['sms_signin'] == 1) {
											if (!empty($_POST['mobile_code'])) {
												if ($_POST['mobile_phone'] != $_SESSION['sms_mobile'] || $_POST['mobile_code'] != $_SESSION['sms_mobile_code']) {
													show_message($_LANG['Mobile_code_fail'], $_LANG['back_input_Code'], '', 'error');
												}
											}
										}
										else {
											if ($mobile != $_POST['mobile_phone'] && $_CFG['sms_signin'] == 1) {
												show_message($_LANG['Real_name_authentication_Mobile_one'], $_LANG['back_input_Code'], '', 'error');
											}
										}
									}
									else {
										show_message($_LANG['Real_name_authentication_Mobile_two'], $_LANG['back_input_Code'], '', 'error');
									}
								}
								else if ($sign == 'email') {
									$hash = empty($_GET['hash']) ? '' : trim($_GET['hash']);

									if ($hash) {
										include_once ROOT_PATH . 'includes/lib_passport.php';
										$id = register_hash('decode', $hash);

										if ($id <= 0) {
											show_message($_LANG['validate_fail'], $_LANG['back'], 'index.php');
										}
									}
									else {
										show_message($_LANG['validate_fail'], $_LANG['back'], 'index.php');
									}
								}
								else if ($sign == 'paypwd') {
									if (intval($_CFG['captcha']) && 0 < gd_version()) {
										$captcha = isset($_POST['authCode']) ? trim($_POST['authCode']) : '';

										if (empty($captcha)) {
											show_message($_LANG['msg_identifying_code'], $_LANG['back_input'], '', 'error');
										}

										$seKey = 'change_password_f';
										$verify = new Verify();
										$captcha_code = $verify->check($captcha, $seKey);

										if (!$captcha_code) {
											show_message($_LANG['invalid_captcha'], $_LANG['back_input_Code'], '', 'error');
										}
									}

									$pay_password = $_REQUEST['pay_password'];
									$row = $db->getRow('SELECT ec_salt, pay_password FROM ' . $ecs->table('users_paypwd') . (' WHERE user_id = \'' . $user_id . '\' '));
									$new_password = md5(md5($pay_password) . $row['ec_salt']);

									if ($new_password != $row['pay_password']) {
										show_message($_LANG['pay_password_packup_error'], $_LANG['back_input_Code'], '', 'error');
									}
								}
								else if ($sign == 'edit_email_ok') {
									if (empty($_SESSION['new_email' . $user_id])) {
										show_message($_LANG['msg_identifying_code'], $_LANG['back_input'], '', 'error');
									}
									else {
										$smarty->assign('validate_new_mail', $_SESSION['new_email' . $user_id]);
									}
								}
								else {
									show_message($_LANG['permissions_null'], 'index.php', '', 'error');
								}
							}
							else if ($step == 'last') {
								if ($sign == 'editmail_ok') {
									$validated = isset($_REQUEST['validated']) ? $_REQUEST['validated'] : 0;
									$smarty->assign('validated', $validated);
									$smarty->assign('security_rating', security_rating());
								}
							}
							else if ($step == 'second_email_verify') {
								$mail_type = !empty($_REQUEST['mail_type']) ? trim($_REQUEST['mail_type']) : 'validate_mail';
								$validated = isset($_REQUEST['validated']) ? $_REQUEST['validated'] : 0;
								include_once ROOT_PATH . 'includes/cls_json.php';
								include_once ROOT_PATH . 'includes/lib_passport.php';
								$json = new JSON();
								$result = array('error' => 0, 'message' => '', 'content' => '');

								if ($user_id == 0) {
									$result['error'] = 1;
									$result['message'] = $_LANG['login_please'];
									exit($json->encode($result));
								}

								$email = !empty($_POST['mail_address_data']) ? trim($_POST['mail_address_data']) : '';

								if (!empty($email)) {
									$a = '/([a-z0-9]*[-_\\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)(\\.[a-z]*)/i';

									if (!preg_match($a, $email)) {
										$result['error'] = 1;
										$result['message'] = $_LANG['msg_email_format'];
										exit($json->encode($result));
									}
								}
								else {
									$result['error'] = 1;
									$result['message'] = $_LANG['msg_email_null'];
									exit($json->encode($result));
								}

								switch ($mail_type) {
								case 'change_pwd':
									if (send_account_safe_hash($user_id, 'change_pwd')) {
										$result['message'] = $_LANG['validate_mail_ok'];
										exit($json->encode($result));
									}
									else {
										$result['error'] = 1;
										$result['message'] = $GLOBALS['err']->last_message();
									}

									break;

								case 'change_mail':
									if (send_account_safe_hash($user_id, 'change_mail')) {
										$result['message'] = $_LANG['validate_mail_ok'];
										exit($json->encode($result));
									}
									else {
										$result['error'] = 1;
										$result['message'] = $GLOBALS['err']->last_message();
									}

									break;

								case 'change_mobile':
									if (send_account_safe_hash($user_id, 'change_mobile')) {
										$result['message'] = $_LANG['validate_mail_ok'];
										exit($json->encode($result));
									}
									else {
										$result['error'] = 1;
										$result['message'] = $GLOBALS['err']->last_message();
									}

									break;

								case 'change_paypwd':
									if (send_account_safe_hash($user_id, 'change_paypwd')) {
										$result['message'] = $_LANG['validate_mail_ok'];
										exit($json->encode($result));
									}
									else {
										$result['error'] = 1;
										$result['message'] = $GLOBALS['err']->last_message();
									}

									break;

								case 'validate_mail':
									if (send_regiter_hash($user_id)) {
										$result['message'] = $_LANG['validate_mail_ok'];
										exit($json->encode($result));
									}
									else {
										$result['error'] = 1;
										$result['message'] = $GLOBALS['err']->last_message();
									}

									break;

								case 'edit_mail':
									$new_email = !empty($_POST['mail_address_data']) ? trim($_POST['mail_address_data']) : '';

									if (!empty($new_email)) {
										$a = '/([a-z0-9]*[-_\\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)(\\.[a-z]*)/i';

										if (!preg_match($a, $email)) {
											$result['error'] = 1;
											$result['message'] = $_LANG['msg_email_format'];
											exit($json->encode($result));
										}

										$_SESSION['new_email' . $user_id] = $new_email;

										if (send_account_safe_hash($user_id, 'editmail', $validated)) {
											$result['message'] = $_LANG['validate_mail_ok'];
											exit($json->encode($result));
										}
										else {
											$result['error'] = 1;
											$result['message'] = $GLOBALS['err']->last_message();
										}
									}
									else {
										$result['error'] = 1;
										$result['message'] = $_LANG['msg_email_null'];
									}

									break;

								default:
									break;
								}

								exit($json->encode($result));
							}
						}
						else if ($type == 'change_phone') {
							if ($step == 'first') {
								if ($sign == 'mobile') {
									if (0 < intval($_CFG['sms_signin'])) {
										$sms_security_code = $_SESSION['sms_security_code'] = rand(1000, 9999);
										$smarty->assign('sms_security_code', $sms_security_code);
										$smarty->assign('enabled_sms_signin', 1);
									}
								}
								else if ($sign == 'email') {
									$is_validated = $db->getOne('SELECT is_validated FROM ' . $ecs->table('users') . (' WHERE user_id = \'' . $user_id . '\' '));

									if (empty($is_validated)) {
										$Loaction = 'user.php?act=account_safe&type=change_email';
										ecs_header('Location: ' . $Loaction . "\n");
									}
								}
								else if ($sign == 'paypwd') {
									$pay_password = $db->getOne('SELECT pay_password FROM ' . $ecs->table('users_paypwd') . (' WHERE user_id = \'' . $user_id . '\' '));

									if (empty($pay_password)) {
										$Loaction = 'user.php?act=account_safe&type=payment_password';
										ecs_header('Location: ' . $Loaction . "\n");
									}
								}
								else if ($sign == 'validate_mail_ok') {
								}
							}
							else if ($step == 'second') {
								if (0 < intval($_CFG['sms_signin'])) {
									$sms_security_code = $_SESSION['sms_security_code'] = rand(1000, 9999);
									$smarty->assign('sms_security_code', $sms_security_code);
									$smarty->assign('enabled_sms_signin', 1);
								}

								if ($sign == 'mobile') {
									if (!empty($_POST['bind'])) {
										$smarty->assign('mobile_phone', $_POST['mobile_phone']);
									}

									if (intval($_CFG['captcha']) && 0 < gd_version()) {
										$captcha = isset($_POST['authCode']) ? trim($_POST['authCode']) : '';

										if (empty($captcha)) {
											show_message($_LANG['msg_identifying_code'], $_LANG['back_input'], '', 'error');
										}

										$seKey = 'change_password_f';
										$verify = new Verify();
										$captcha_code = $verify->check($captcha, $seKey);

										if (!$captcha_code) {
											show_message($_LANG['invalid_captcha'], $_LANG['back_input_Code'], '', 'error');
										}
									}

									if (!empty($_POST['mobile_phone'])) {
										if (empty($_POST['mobile_code'])) {
											show_message($_LANG['Mobile_code_null'], $_LANG['back_input'], '', 'error');
										}

										$mobile = $GLOBALS['db']->getOne('SELECT mobile_phone FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id=\'' . $user_id . '\''));

										if (empty($mobile)) {
											if (!empty($_POST['mobile_code'])) {
												if ($_POST['mobile_phone'] != $_SESSION['sms_mobile'] || $_POST['mobile_code'] != $_SESSION['sms_mobile_code']) {
													show_message($_LANG['Mobile_code_fail'], $_LANG['back_input_Code'], '', 'error');
												}
												else {
													$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET mobile_phone = \'' . $_POST['mobile_phone'] . ('\' WHERE user_id = \'' . $user_id . '\'');
													$GLOBALS['db']->query($sql);
													$Loaction = 'user.php?act=account_safe&type=change_phone&step=last&step=last&phone_first=1';
													ecs_header('Location: ' . $Loaction . "\n");
												}
											}
										}
										else {
											if ($mobile == $_POST['mobile_phone'] && $_CFG['sms_signin'] == 1) {
												if (!empty($_POST['mobile_code'])) {
													if ($_POST['mobile_phone'] != $_SESSION['sms_mobile'] || $_POST['mobile_code'] != $_SESSION['sms_mobile_code']) {
														show_message($_LANG['Mobile_code_fail'], $_LANG['back_input_Code'], '', 'error');
													}
												}
											}
											else {
												if ($mobile != $_POST['mobile_phone'] && $_CFG['sms_signin'] == 1) {
													show_message($_LANG['Real_name_authentication_Mobile_one'], $_LANG['back_input_Code'], '', 'error');
												}
											}
										}
									}
									else {
										show_message($_LANG['Real_name_authentication_Mobile_two'], $_LANG['back_input_Code'], '', 'error');
									}
								}
								else if ($sign == 'email') {
									$hash = empty($_GET['hash']) ? '' : trim($_GET['hash']);

									if ($hash) {
										include_once ROOT_PATH . 'includes/lib_passport.php';
										$id = register_hash('decode', $hash);

										if ($id <= 0) {
											show_message($_LANG['validate_fail'], $_LANG['back'], 'index.php');
										}
									}
									else {
										show_message($_LANG['validate_fail'], $_LANG['back'], 'index.php');
									}
								}
								else if ($sign == 'paypwd') {
									if (intval($_CFG['captcha']) && 0 < gd_version()) {
										$captcha = isset($_POST['authCode']) ? trim($_POST['authCode']) : '';

										if (empty($captcha)) {
											show_message($_LANG['msg_identifying_code'], $_LANG['back_input'], '', 'error');
										}

										$seKey = 'change_password_f';
										$verify = new Verify();
										$captcha_code = $verify->check($captcha, $seKey);

										if (!$captcha_code) {
											show_message($_LANG['invalid_captcha'], $_LANG['back_input_Code'], '', 'error');
										}
									}

									$pay_password = $_REQUEST['pay_password'];
									$row = $db->getRow('SELECT ec_salt, pay_password FROM ' . $ecs->table('users_paypwd') . (' WHERE user_id = \'' . $user_id . '\' '));
									$new_password = md5(md5($pay_password) . $row['ec_salt']);

									if ($new_password != $row['pay_password']) {
										show_message($_LANG['pay_password_packup_error'], $_LANG['back_input_Code'], '', 'error');
									}
								}
								else {
									show_message($_LANG['permissions_null'], 'index.php', '', 'error');
								}
							}
							else if ($step == 'last') {
								$phone_first = isset($_REQUEST['phone_first']) && !empty($_REQUEST['phone_first']) ? intval($_REQUEST['phone_first']) : 0;
								if (intval($_CFG['captcha']) && 0 < gd_version() && $phone_first == 0) {
									$captcha = isset($_POST['authCode']) ? trim($_POST['authCode']) : '';

									if (empty($captcha)) {
										show_message($_LANG['msg_identifying_code'], $_LANG['back_input'], '', 'error');
									}

									$seKey = 'change_password_f';
									$verify = new Verify();
									$captcha_code = $verify->check($captcha, $seKey);

									if (!$captcha_code) {
										show_message($_LANG['invalid_captcha'], $_LANG['back_input_Code'], '', 'error');
									}
								}

								if (!empty($_POST['mobile_phone']) && $_CFG['sms_signin'] == 1 || $phone_first == 1) {
									if (isset($_POST['mobile_code'])) {
										if (!empty($_POST['mobile_code'])) {
											if ($_POST['mobile_phone'] != $_SESSION['sms_mobile'] || $_POST['mobile_code'] != $_SESSION['sms_mobile_code']) {
												show_message($_LANG['Mobile_code_fail'], $_LANG['back_input_Code'], '', 'error');
											}
										}
										else {
											show_message($_LANG['Mobile_code_null'], $_LANG['back_input_Code'], '', 'error');
										}
									}

									if ($phone_first == 0) {
										$user_inf = array('mobile_phone' => $_POST['mobile_phone']);
										$db->autoExecute($ecs->table('users'), $user_inf, 'UPDATE', 'user_id = \'' . $user_id . '\'');
										users_log_change($_SESSION['user_id'], USER_PHONE);
									}

									$smarty->assign('security_rating', security_rating());
								}
								else {
									show_message($_LANG['Real_name_authentication_Mobile_two'], $_LANG['back_input_Code'], '', 'error');
								}
							}
						}
						else if ($type == 'payment_password') {
							if ($step == 'first') {
								if ($sign == 'mobile') {
									$password_type = !empty($_REQUEST['password_type']) ? intval($_REQUEST['password_type']) : 0;
									$smarty->assign('password_type', $password_type);

									if (empty($user_info['mobile_phone'])) {
										$Loaction = 'user.php?act=account_safe&type=change_phone';
										ecs_header('Location: ' . $Loaction . "\n");
									}

									if (0 < intval($_CFG['sms_signin'])) {
										$sms_security_code = $_SESSION['sms_security_code'] = rand(1000, 9999);
										$smarty->assign('sms_security_code', $sms_security_code);
										$smarty->assign('enabled_sms_signin', 1);
									}
								}
								else if ($sign == 'email') {
									$is_validated = $db->getOne('SELECT is_validated FROM ' . $ecs->table('users') . (' WHERE user_id = \'' . $user_id . '\' '));

									if (empty($is_validated)) {
										$Loaction = 'user.php?act=account_safe&type=change_email';
										ecs_header('Location: ' . $Loaction . "\n");
									}
								}
								else if ($sign == 'paypwd') {
									$pay_password = $db->getOne('SELECT pay_password FROM ' . $ecs->table('users_paypwd') . (' WHERE user_id = \'' . $user_id . '\' '));

									if (empty($pay_password)) {
										$Loaction = 'user.php?act=account_safe&type=payment_password';
										ecs_header('Location: ' . $Loaction . "\n");
									}
								}
								else if ($sign == 'validate_mail_ok') {
								}
							}
							else if ($step == 'second') {
								$user_paypwd = $GLOBALS['db']->getRow('SELECT * FROM ' . $GLOBALS['ecs']->table('users_paypwd') . (' WHERE user_id = \'' . $user_id . '\' '));
								$smarty->assign('user_paypwd', $user_paypwd);

								if ($sign == 'mobile') {
									$password_type = !empty($_REQUEST['password_type']) ? intval($_REQUEST['password_type']) : 0;
									$smarty->assign('password_type', $password_type);

									if (!empty($_POST['bind'])) {
										$smarty->assign('mobile_phone', $_POST['mobile_phone']);
									}

									if (intval($_CFG['captcha']) && 0 < gd_version()) {
										$captcha = isset($_POST['authCode']) ? trim($_POST['authCode']) : '';

										if (empty($captcha)) {
											show_message($_LANG['msg_identifying_code'], $_LANG['back_input'], '', 'error');
										}

										$seKey = 'change_password_f';
										$verify = new Verify();
										$captcha_code = $verify->check($captcha, $seKey);

										if (!$captcha_code) {
											show_message($_LANG['invalid_captcha'], $_LANG['back_input_Code'], '', 'error');
										}
									}

									if (!empty($_POST['mobile_phone'])) {
										if (empty($_POST['mobile_code'])) {
											show_message($_LANG['Mobile_code_null'], $_LANG['back_input'], '', 'error');
										}

										$mobile = $GLOBALS['db']->getOne('SELECT mobile_phone FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id=\'' . $user_id . '\''));
										if ($mobile == $_POST['mobile_phone'] && $_CFG['sms_signin'] == 1) {
											if (!empty($_POST['mobile_code'])) {
												if ($_POST['mobile_phone'] != $_SESSION['sms_mobile'] || $_POST['mobile_code'] != $_SESSION['sms_mobile_code']) {
													show_message($_LANG['Mobile_code_fail'], $_LANG['back_input_Code'], '', 'error');
												}
											}
										}
										else {
											if ($mobile != $_POST['mobile_phone'] && $_CFG['sms_signin'] == 1) {
												show_message($_LANG['Real_name_authentication_Mobile_one'], $_LANG['back_input_Code'], '', 'error');
											}
										}
									}
									else {
										show_message($_LANG['Real_name_authentication_Mobile_two'], $_LANG['back_input_Code'], '', 'error');
									}
								}
								else if ($sign == 'email') {
									$hash = empty($_GET['hash']) ? '' : trim($_GET['hash']);

									if ($hash) {
										include_once ROOT_PATH . 'includes/lib_passport.php';
										$id = register_hash('decode', $hash);

										if ($id <= 0) {
											show_message($_LANG['validate_fail'], $_LANG['back'], 'index.php');
										}
									}
									else {
										show_message($_LANG['validate_fail'], $_LANG['back'], 'index.php');
									}
								}
								else if ($sign == 'paypwd') {
									if (intval($_CFG['captcha']) && 0 < gd_version()) {
										$captcha = isset($_POST['authCode']) ? trim($_POST['authCode']) : '';

										if (empty($captcha)) {
											show_message($_LANG['msg_identifying_code'], $_LANG['back_input'], '', 'error');
										}

										$seKey = 'change_password_f';
										$verify = new Verify();
										$captcha_code = $verify->check($captcha, $seKey);

										if (!$captcha_code) {
											show_message($_LANG['invalid_captcha'], $_LANG['back_input_Code'], '', 'error');
										}
									}

									$pay_password = $_REQUEST['pay_password'];
									$row = $db->getRow('SELECT ec_salt, pay_password FROM ' . $ecs->table('users_paypwd') . (' WHERE user_id = \'' . $user_id . '\' '));
									$new_password = md5(md5($pay_password) . $row['ec_salt']);

									if ($new_password != $row['pay_password']) {
										show_message($_LANG['pay_password_packup_error'], $_LANG['back_input_Code'], '', 'error');
									}
								}
								else {
									show_message($_LANG['permissions_null'], 'index.php', '', 'error');
								}
							}
							else if ($step == 'last') {
								$password_type = !empty($_REQUEST['password_type']) ? intval($_REQUEST['password_type']) : 0;
								$smarty->assign('password_type', $password_type);
								$count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('users_paypwd') . (' WHERE user_id = \'' . $user_id . '\' '));
								$pay_online = !empty($_POST['pay_online']) ? intval($_POST['pay_online']) : 0;
								$user_surplus = !empty($_POST['user_surplus']) ? intval($_POST['user_surplus']) : 0;
								$user_point = !empty($_POST['user_point']) ? intval($_POST['user_point']) : 0;
								$baitiao = !empty($_POST['baitiao']) ? intval($_POST['baitiao']) : 0;
								$gift_card = !empty($_POST['gift_card']) ? intval($_POST['gift_card']) : 0;
								$pay_password = !empty($_POST['new_password']) ? trim($_POST['new_password']) : 0;
								$re_pay_password = !empty($_POST['re_new_password']) ? trim($_POST['re_new_password']) : 0;
								$real_user = array('user_id' => $user_id, 'pay_online' => $pay_online, 'user_surplus' => $user_surplus, 'user_point' => $user_point, 'baitiao' => $baitiao, 'gift_card' => $gift_card);
								$smarty->assign('security_rating', security_rating());
								if (!empty($pay_password) && !empty($re_pay_password)) {
									if ($re_pay_password != $pay_password) {
										show_message($_LANG['password_difference'], $_LANG['back_input'], '', 'error');
									}
								}
								else {
									show_message($_LANG['Real_name_password_null'], $_LANG['back_input'], '', 'error');
								}

								$ec_salt = rand(1, 9999);
								$new_password = md5(md5($pay_password) . $ec_salt);
								$real_user['pay_password'] = $new_password;
								$real_user['ec_salt'] = $ec_salt;
								users_log_change($user_id, USER_PPASS);

								if ($count == 1) {
									if (!$db->autoExecute($ecs->table('users_paypwd'), $real_user, 'UPDATE', 'user_id = \'' . $user_id . '\'')) {
										show_message($_LANG['on_failure'], $_LANG['back_choose'], '', 'error');
									}
								}
								else if (!$db->autoExecute($ecs->table('users_paypwd'), $real_user, 'INSERT')) {
									show_message($_LANG['on_failure'], $_LANG['back_choose'], '', 'error');
								}
							}
						}
						else if ($type == 'real_name') {
							$real_user = get_users_real($user_id);

							if ($step == 'first') {
								$operate = isset($_REQUEST['operate']) && !(empty($_REQUEST['operate']) && trim($_REQUEST['operate']) == 'edit') ? trim($_REQUEST['operate']) : '';
								if ($real_user && empty($operate)) {
									$Loaction = 'user.php?act=account_safe&type=real_name&step=realname_ok';
									ecs_header('Location: ' . $Loaction . "\n");
								}

								if ($operate) {
									$smarty->assign('real_user', $real_user);
									$smarty->assign('operate', 'edit');
								}

								if (0 < intval($_CFG['sms_signin'])) {
									$sms_security_code = $_SESSION['sms_security_code'] = rand(1000, 9999);
									$smarty->assign('sms_security_code', $sms_security_code);
									$smarty->assign('enabled_sms_signin', 1);
								}
							}
							else if ($step == 'second') {
								if (!empty($_POST['mobile_phone'])) {
									if (0 < intval($_CFG['sms_signin'])) {
										if (empty($_POST['mobile_code'])) {
											show_message($_LANG['Mobile_code_null'], $_LANG['back_input'], '', 'error');
										}

										if (!empty($_POST['mobile_code'])) {
											if ($_POST['mobile_phone'] != $_SESSION['sms_mobile'] || $_POST['mobile_code'] != $_SESSION['sms_mobile_code']) {
												show_message($_LANG['Mobile_code_fail'], $_LANG['back_input_Code'], '', 'error');
											}
										}
									}
								}
								else {
									show_message($_LANG['Real_name_authentication_Mobile_two'], $_LANG['back_input_Code'], '', 'error');
								}

								$real_user['user_id'] = $user_id;
								$real_user['real_name'] = dsc_addslashes(trim($_POST['real_name']));
								$real_user['self_num'] = dsc_addslashes(trim($_POST['self_num']));
								$real_user['bank_mobile'] = dsc_addslashes(trim($_POST['mobile_phone']));
								$real_user['bank_name'] = dsc_addslashes(trim($_POST['bank_name']));
								$real_user['bank_card'] = dsc_addslashes(trim($_POST['bank_card']));
								$textfile_zheng = dsc_addslashes(trim($_POST['textfile_zheng']));
								$textfile_fan = dsc_addslashes(trim($_POST['textfile_fan']));
								$real_user['add_time'] = gmtime();
								$real_user['review_status'] = 0;

								if (empty($_FILES['front_of_id_card']['size'])) {
									$front_name = $textfile_zheng;
								}
								else {
									$front_name = $image->upload_image($_FILES['front_of_id_card'], 'idcard');
									get_oss_add_file(array($front_name));
								}

								if (empty($_FILES['reverse_of_id_card']['size'])) {
									$reverse_name = $textfile_fan;
								}
								else {
									$reverse_name = $image->upload_image($_FILES['reverse_of_id_card'], 'idcard');
									get_oss_add_file(array($reverse_name));
								}

								$real_user['front_of_id_card'] = $front_name;
								$real_user['reverse_of_id_card'] = $reverse_name;

								if (empty($real_user['real_name'])) {
									show_message($_LANG['Real_name_null'], $_LANG['back_Fill'], '', 'error');
								}

								if (empty($real_user['self_num'])) {
									show_message($_LANG['self_num_null'], $_LANG['back_Fill'], '', 'error');
								}

								if (empty($real_user['bank_name'])) {
									show_message($_LANG['bank_name_null'], $_LANG['back_Fill'], '', 'error');
								}

								if (empty($real_user['bank_card'])) {
									show_message($_LANG['bank_card_null'], $_LANG['back_Fill'], '', 'error');
								}

								if (empty($real_user['bank_mobile'])) {
									show_message($_LANG['bank_mobile_null'], $_LANG['back_Fill'], '', 'error');
								}

								$count_user = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('users_real') . (' WHERE user_id = \'' . $user_id . '\' AND user_type = 0'));

								if ($count_user) {
									if ($db->autoExecute($ecs->table('users_real'), $real_user, 'UPDATE', 'user_id=\'' . $user_id . '\' AND user_type = 0')) {
										$Loaction = 'user.php?act=account_safe&type=real_name&step=realname_ok';
										ecs_header('Location: ' . $Loaction . "\n");
									}
								}
								else if ($db->autoExecute($ecs->table('users_real'), $real_user, 'INSERT')) {
									$Loaction = 'user.php?act=account_safe&type=real_name&step=realname_ok';
									ecs_header('Location: ' . $Loaction . "\n");
								}
							}
							else if ($step == 'realname_ok') {
								if (!$real_user) {
									users_log_change($user_id, USER_REAL);
									$Loaction = 'user.php?act=account_safe&type=real_name&step=first';
									ecs_header('Location: ' . $Loaction . "\n");
								}

								$real_user['validate_time'] = local_date('Y-m-d H:i:s', empty($real_user['add_time']) ? '' : $real_user['add_time']);
								$real_user['reverse_of_id_card'] = get_image_path(0, $real_user['reverse_of_id_card']);
								$real_user['front_of_id_card'] = get_image_path(0, $real_user['front_of_id_card']);
								$smarty->assign('real_user', $real_user);
								$smarty->assign('edit_user', 'user.php?act=account_safe&type=real_name&step=first&operate=edit');
								$mobile = $db->getOne('SELECT  mobile_phone FROM ' . $ecs->table('users') . (' WHERE user_id = \'' . $user_id . '\' '));
								$smarty->assign('mobile_phone', $mobile);
							}
						}
						else if ($type == 'default') {
							$sql = 'SELECT u.is_validated as email_validate, u.email, u.mobile_phone, up.paypwd_id, ur.real_id, ur.real_name, ur.bank_card ' . ' FROM ' . $ecs->table('users') . ' AS u ' . ' LEFT JOIN ' . $ecs->table('users_paypwd') . ' AS up ON u.user_id = up.user_id ' . ' LEFT JOIN ' . $ecs->table('users_real') . ' AS ur ON u.user_id = ur.user_id AND user_type = 0 ' . (' WHERE u.user_id = \'' . $user_id . '\' ');
							$res = $db->getRow($sql);
							$smarty->assign('validate', $res);
							$smarty->assign('security_rating', security_rating());
						}

						$smarty->assign('type', $type);
						$smarty->assign('step', $step);
						$smarty->assign('sign', $sign);
						$smarty->display('user_transaction.dwt');
					}
					else if ($action == 'account_bind') {
						include_once ROOT_PATH . 'includes/lib_clips.php';
						$qq_info = get_bind_oath_info($user_id, 'qq');
						$weibo_info = get_bind_oath_info($user_id, 'weibo');
						$weixin_info = get_bind_oath_info($user_id, 'wechat');
						$smarty->assign('qq_info', $qq_info);
						$smarty->assign('weibo_info', $weibo_info);
						$smarty->assign('weixin_info', $weixin_info);
						$WebsiteList = getWebsiteList();
						$count = !empty($WebsiteList) ? count($WebsiteList) : 0;
						$WebsiteList[$count]['name'] = '微信';
						$WebsiteList[$count]['type'] = 'wechat';

						if (file_exists(ROOT_PATH . 'wechat_oauth.php')) {
							$WebsiteList[$count]['install'] = 1;
						}
						else {
							$WebsiteList[$count]['install'] = 0;
						}

						$insert = array();

						foreach ($WebsiteList as $k => $v) {
							if ($v['type'] == 'qq') {
								$insert['qq_install'] = $v['install'];
							}

							if ($v['type'] == 'weibo') {
								$insert['weibo_install'] = $v['install'];
							}

							if ($v['type'] == 'wechat') {
								$insert['wechat_install'] = $v['install'];
							}
						}

						$info = get_user_default($user_id);
						$smarty->assign('info', $info);
						$smarty->assign('user_id', $user_id);
						$smarty->assign('insert', $insert);
						$smarty->display('user_transaction.dwt');
					}
					else if ($action == 'oath_remove') {
						require_once ROOT_PATH . 'includes/cls_json.php';
						$json = new JSON();
						$result = array('error' => 0, 'message' => '');
						$_POST = get_request_filter($_POST, 1);
						$id = !empty($_POST['id']) ? intval($_POST['id']) : 0;
						$sql = 'SELECT user_id, identity_type, identifier FROM ' . $ecs->table('users_auth') . (' WHERE id = \'' . $id . '\' LIMIT 1');
						$users_auth = $db->getRow($sql);
						$open_id = !empty($users_auth['identifier']) ? str_replace($users_auth['identity_type'] . '_', '', $users_auth['identifier']) : '';
						$sql = 'DELETE FROM ' . $ecs->table('users_auth') . (' WHERE id = \'' . $id . '\'');
						$db->query($sql);
						$sql = 'DELETE FROM ' . $ecs->table('connect_user') . ' WHERE user_id = \'' . $users_auth['user_id'] . ('\' AND open_id = \'' . $open_id . '\'');
						$db->query($sql);
						$result['id'] = $id;
						$result['identity'] = $_POST['identity'];
						exit($json->encode($result));
					}
					else if ($action == 'crowdfunding') {
						include_once ROOT_PATH . 'includes/lib_transaction.php';
						$user_id = $_SESSION['user_id'];
						$sql = 'select zp.*,sum(zg.backer_num) as zhichi_num from ' . $ecs->table('zc_focus') . " zf\r\n\t\t\tleft join " . $ecs->table('zc_project') . " zp on zf.pid=zp.id\r\n\t\t\tleft join " . $ecs->table('zc_goods') . (" zg on zp.id=zg.pid\r\n\t\t\twhere zf.user_id = '" . $user_id . '\' group by zp.id');
						$zc_focus_list = $db->getAll($sql);

						foreach ($zc_focus_list as $k => &$v) {
							$v['surplus_time'] = ceil(($v['end_time'] - time()) / 86400);
							$v['complete'] = round($v['join_money'] / $v['amount'] * 100);
						}

						$sql = 'select zp.*,oi.order_id,oi.order_sn, oi.pay_status,oi.shipping_status,oi.goods_amount,join_num as zhichi_num from ' . $ecs->table('zc_goods') . ' as zg left join ' . $ecs->table('zc_project') . " as zp on zg.pid=zp.id\r\n            left join " . $ecs->table('order_info') . (' as oi on zg.id=oi.zc_goods_id where oi.user_id = \'' . $user_id . '\' and oi.is_zc_order=1 order by oi.order_id desc');
						$zc_support_list = $db->getAll($sql);

						foreach ($zc_support_list as $k => &$v) {
							$v['surplus_time'] = ceil(($v['end_time'] - time()) / 86400);
							$v['surplus_time'] = 0 < $v['surplus_time'] ? $v['surplus_time'] : 0;
							$v['complete'] = round($v['join_money'] / $v['amount'] * 100);

							if ($v['pay_status'] == 2) {
								$zc_support_list_yes_pay[] = $v;
							}
							else {
								$zc_support_list_no_pay[] = $v;
							}
						}

						$smarty->assign('zc_focus_list', $zc_focus_list);
						$smarty->assign('zc_support_list', $zc_support_list);
						$smarty->assign('zc_support_list_yes_pay', $zc_support_list_yes_pay);
						$smarty->assign('zc_support_list_no_pay', $zc_support_list_no_pay);
						$smarty->display('user_transaction.dwt');
					}
					else if ($action == 'delete_zc_focus') {
						$pid = intval($_GET['rec_id']);
						$res = $db->query('DELETE FROM ' . $ecs->table('zc_focus') . ' WHERE pid=\'' . $pid . '\'');
						$res = $db->query('UPDATE' . $ecs->table('zc_project') . ('SET focus_num=focus_num-1 WHERE id=\'' . $pid . '\''));

						if ($res) {
							header('location:user.php?act=crowdfunding');
						}
						else {
							show_message($_LANG['process_false'], $_LANG['back_page_up'], 'user.php?act=crowdfunding');
						}
					}
					else if ($action == 'return_order_status') {
						include ROOT_PATH . 'includes/cls_json.php';
						$json = new JSON();
						$res = array('result' => '', 'error' => 0, 'msg' => '');
						$order_id = !empty($_GET['order_id']) ? intval($_GET['order_id']) : 0;
						if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
							$noTime = gmtime();
							$sql = 'SELECT order_id, user_id, order_sn , order_status, shipping_status, pay_status, auto_delivery_time, add_time, ' . 'order_amount, goods_amount, tax, shipping_fee, insure_fee, pay_fee, pack_fee, card_fee, ' . 'bonus, integral_money, coupons, discount, money_paid, surplus, confirm_take_time ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\'');
							$order = $GLOBALS['db']->GetRow($sql);

							if ($GLOBALS['_CFG']['open_delivery_time'] == 1) {
								if (($order['order_status'] == OS_CONFIRMED || $order['order_status'] == OS_SPLITED) && $order['shipping_status'] == SS_SHIPPED && $order['pay_status'] == PS_PAYED) {
									$delivery_time = $order['shipping_time'] + 24 * 3600 * $order['auto_delivery_time'];

									if ($delivery_time <= $noTime) {
										$confirm_take_time = gmtime();
										$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET order_status = \'' . OS_SPLITED . '\', ' . 'shipping_status = \'' . SS_RECEIVED . '\', pay_status = \'' . PS_PAYED . ('\', confirm_take_time = \'' . $confirm_take_time . '\' WHERE order_id = \'' . $order_id . '\'');

										if ($GLOBALS['db']->query($sql)) {
											$note = $GLOBALS['_LANG']['self_motion_goods'];
											order_action($order['order_sn'], OS_SPLITED, SS_RECEIVED, PS_PAYED, $note, $GLOBALS['_LANG']['buyer'], 0, $confirm_take_time);
											$seller_id = $GLOBALS['db']->getOne('SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\''), true);
											$value_card = $GLOBALS['db']->getOne('SELECT use_val FROM ' . $GLOBALS['ecs']->table('value_card_record') . (' WHERE order_id = \'' . $order_id . '\''), true);
											$return_amount = get_order_return_amount($order_id);
											$other = array('user_id' => $order['user_id'], 'seller_id' => $seller_id, 'order_id' => $order['order_id'], 'order_sn' => $order['order_sn'], 'order_status' => $order['order_status'], 'shipping_status' => SS_RECEIVED, 'pay_status' => $order['pay_status'], 'order_amount' => $order['order_amount'], 'return_amount' => $return_amount, 'goods_amount' => $order['goods_amount'], 'tax' => $order['tax'], 'shipping_fee' => $order['shipping_fee'], 'insure_fee' => $order['insure_fee'], 'pay_fee' => $order['pay_fee'], 'pack_fee' => $order['pack_fee'], 'card_fee' => $order['card_fee'], 'bonus' => $order['bonus'], 'integral_money' => $order['integral_money'], 'coupons' => $order['coupons'], 'discount' => $order['discount'], 'value_card' => $value_card, 'money_paid' => $order['money_paid'], 'surplus' => $order['surplus'], 'confirm_take_time' => $confirm_take_time);

											if ($seller_id) {
												get_order_bill_log($other);
											}
										}

										$res['ss_received'] = $GLOBALS['_LANG']['ss_received'];
										$res['error'] = 1;

										if (defined('THEME_EXTENSION')) {
											$res['msg'] = '<a href=\'user.php?act=order_detail&amp;order_id=' . $order_id . '\' class=\'sc-btn\'>' . $GLOBALS['_LANG']['order_detail'] . '</a>' . '<a href=\'javascript:get_order_delete_restore(\'delete\', ' . $order_id . ');\' class=\'sc-btn\'>' . $GLOBALS['_LANG']['delete_order'] . '</a>' . '<a href=\'user.php?act=commented_view&amp;order_id=' . $order_id . '\' class=\'sc-btn\'>' . $GLOBALS['_LANG']['single_comment'] . '</a>';
										}
										else {
											$res['msg'] = '<div class=\'item\'>' . '<a href=\'user.php?act=order_detail&amp;order_id=' . $order_id . '\'>' . $_LANG['view'] . '</a>' . '<br><span class=\'pop-recycle-a\'><a href=\'user.php?act=commented_view&amp;order_id=' . $order_id . '\'>' . $_LANG['single_comment'] . '</a><br>' . '<a style=\'margin-left:5px;\' href=\'user.php?act=goods_order&amp;order_id=' . $order_id . '\'>' . $_LANG['return'] . '</a></span>' . '</div>';
										}
									}
								}
							}
						}

						exit($json->encode($res));
					}
					else if ($action == 'trade') {
						assign_template();
						$tradeId = isset($_REQUEST['tradeId']) ? intval($_REQUEST['tradeId']) : 0;
						$snapshot = isset($_REQUEST['snapshot']) ? true : false;
						$sql = ' SELECT * FROM ' . $ecs->table('trade_snapshot') . (' WHERE trade_id = \'' . $tradeId . '\' ');
						$row = $db->getRow($sql);
						if ($row && $row['goods_desc'] && $GLOBALS['_CFG']['open_oss'] == 1) {
							$bucket_info = get_bucket_info();

							if ($row['goods_desc']) {
								$desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $row['goods_desc']);
								$row['goods_desc'] = $desc_preg['goods_desc'];
							}
						}

						$row['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
						$smarty->assign('pictures', get_goods_gallery($row['goods_id']));
						$row['snapshot_time'] = local_date('Y-m-d H:i:s', $row['snapshot_time']);
						$row['shop_url'] = '';

						if (0 < $row['ru_id']) {
							$merchants_goods_comment = get_merchants_goods_comment($row['ru_id']);
							$smarty->assign('merch_cmt', $merchants_goods_comment);
							$build_uri = array('urid' => $row['ru_id'], 'append' => get_shop_name($row['ru_id'], 3));
							$domain_url = get_seller_domain_url($row['ru_id'], $build_uri);
							$row['shop_url'] = $domain_url['domain_name'];
						}

						$shop_information = get_shop_name($row['ru_id']);
						$shop_information['kf_tel'] = $db->getOne('SELECT kf_tel FROM ' . $ecs->table('seller_shopinfo') . 'WHERE ru_id = \'' . $row['ru_id'] . '\'');

						if ($row['ru_id'] == 0) {
							if ($db->getOne('SELECT kf_im_switch FROM ' . $ecs->table('seller_shopinfo') . 'WHERE ru_id = 0')) {
								$shop_information['is_dsc'] = true;
							}
							else {
								$shop_information['is_dsc'] = false;
							}
						}
						else {
							$shop_information['is_dsc'] = false;
						}

						$properties = get_goods_properties($row['goods_id'], $region_id, $area_id, $area_city);
						$smarty->assign('properties', $properties['pro']);
						$smarty->assign('specification', $properties['spe']);
						$smarty->assign('shop_information', $shop_information);
						$smarty->assign('page_title', $row['goods_name']);
						$smarty->assign('helps', get_shop_help());
						$smarty->assign('snapshot', $snapshot);
						$smarty->assign('goods', $row);
						$smarty->display('trade_snapshot.dwt');
					}
					else if ($action == 'purchase') {
						include_once ROOT_PATH . 'includes/lib_transaction.php';
						$user_id = $_SESSION['user_id'];
						$smarty->assign('action', $action);
						$smarty->display('user_transaction.dwt');
					}
					else if ($action == 'want_buy') {
						include_once ROOT_PATH . 'includes/lib_transaction.php';
						$user_id = $_SESSION['user_id'];
						$smarty->assign('action', $action);
						$smarty->display('user_transaction.dwt');
					}
					else if ($action == 'invoice') {
						include_once ROOT_PATH . 'includes/lib_transaction.php';
						$user_id = $_SESSION['user_id'];
						$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
						$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('order_info') . ' as oi_1' . (' WHERE oi_1.user_id = \'' . $user_id . '\' ') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi_2 where oi_2.main_order_id = oi_1.order_id) = 0 ');
						$invoice_list = invoice_list($user_id, $record_count, $page);
						$smarty->assign('invoice_list', $invoice_list);
						$smarty->assign('action', $action);
						$smarty->display('user_clips.dwt');
					}
					else if ($action == 'vat_invoice_info') {
						include_once ROOT_PATH . 'includes/lib_transaction.php';
						$sql = ' SELECT * FROM ' . $ecs->table('users_vat_invoices_info') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
						$res = $db->getRow($sql);
						$new_vat_consignee_list = get_vat_consignee_list($res['id']);
						$smarty->assign('new_vat_consignee_list', $new_vat_consignee_list);

						if ($res) {
							$smarty->assign('submitted', true);
							$smarty->assign('vat_id', $res['id']);
							$audit_status = $res['audit_status'];
							$smarty->assign('audit_status', $audit_status);
							$smarty->assign('vat_info', $res);
						}

						$smarty->assign('action', $action);
						$smarty->display('user_clips.dwt');
					}
					else {
						if ($action == 'vat_insert' || $action == 'vat_update') {
							include_once ROOT_PATH . 'includes/lib_transaction.php';
							$vat_id = isset($_REQUEST['vat_id']) ? trim($_REQUEST['vat_id']) : '';
							$user_id = $_SESSION['user_id'];
							$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : 'insert';
							$company_name = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
							$tax_id = isset($_POST['tax_id']) ? trim($_POST['tax_id']) : '';
							$company_address = isset($_POST['company_address']) ? trim($_POST['company_address']) : '';
							$company_telephone = isset($_POST['company_telephone']) ? trim($_POST['company_telephone']) : '';
							$bank_of_deposit = isset($_POST['bank_of_deposit']) ? trim($_POST['bank_of_deposit']) : '';
							$bank_account = isset($_POST['bank_account']) ? trim($_POST['bank_account']) : '';
							$audit_status = 0;
							$content = array('company_name' => $company_name, 'user_id' => $user_id, 'tax_id' => $tax_id, 'company_address' => $company_address, 'company_telephone' => $company_telephone, 'bank_of_deposit' => $bank_of_deposit, 'bank_account' => $bank_account, 'audit_status' => $audit_status);

							if ($action = 'vat_insert') {
								$content['add_time'] = gmtime();
							}

							if ($vat_id) {
								if ($status == 'update') {
									if ($db->autoExecute($ecs->table('users_vat_invoices_info'), $content, 'UPDATE', 'id = \'' . $vat_id . '\'')) {
										$smarty->assign('submitted', true);
									}
								}

								$smarty->assign('edit', 'vat_update');
								$smarty->assign('vat_id', $vat_id);
								$smarty->assign('status', 'update');
							}
							else {
								$sql = ' SELECT user_id FROM ' . $ecs->table('users_vat_invoices_info') . (' WHERE user_id = \'' . $user_id . '\' ');

								if (!$db->getOne($sql)) {
									$vat_info = $db->autoExecute($ecs->table('users_vat_invoices_info'), $content, 'INSERT');
									$id = $db->insert_id();
									$smarty->assign('submitted', true);
									$smarty->assign('vat_id', $id);
								}
							}

							$sql = ' SELECT * FROM ' . $ecs->table('users_vat_invoices_info') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
							$res = $db->getRow($sql);
							$new_vat_consignee_list = get_vat_consignee_list($res['id']);
							$smarty->assign('new_vat_consignee_list', $new_vat_consignee_list);

							if ($res) {
								$audit_status = $res['audit_status'];
								$smarty->assign('audit_status', $audit_status);
							}

							$smarty->assign('vat_info', $res);
							$smarty->assign('action', 'vat_invoice_info');
							$smarty->display('user_clips.dwt');
						}
						else if ($action == 'vat_remove') {
							include_once ROOT_PATH . 'includes/lib_transaction.php';
							$vat_id = isset($_REQUEST['vat_id']) ? intval($_REQUEST['vat_id']) : 0;
							$sql = ' DELETE FROM ' . $ecs->table('users_vat_invoices_info') . (' WHERE id = \'' . $vat_id . '\' ');
							$db->query($sql);
							$smarty->assign('action', 'vat_invoice_info');
							$smarty->display('user_clips.dwt');
						}
						else if ($action == 'vat_consignee') {
							include_once ROOT_PATH . 'includes/lib_transaction.php';
							$user_id = $_SESSION['user_id'];
							$status = isset($_POST['status']) ? trim($_POST['status']) : '';
							$vat_id = isset($_REQUEST['vat_id']) ? trim($_REQUEST['vat_id']) : '';

							if ($status == 'update') {
								$consignee_name = isset($_POST['consignee_name']) ? trim($_POST['consignee_name']) : '';
								$consignee_mobile_phone = isset($_POST['consignee_mobile_phone']) ? trim($_POST['consignee_mobile_phone']) : '';
								$country = isset($_POST['country']) ? trim($_POST['country']) : '';
								$province = isset($_POST['province']) ? trim($_POST['province']) : '';
								$city = isset($_POST['city']) ? trim($_POST['city']) : '';
								$district = isset($_POST['district']) ? trim($_POST['district']) : '';
								$consignee_address = isset($_POST['consignee_address']) ? trim($_POST['consignee_address']) : '';
								$content = array('consignee_name' => $consignee_name, 'consignee_mobile_phone' => $consignee_mobile_phone, 'country' => $country, 'province' => $province, 'city' => $city, 'district' => $district, 'consignee_address' => $consignee_address, 'audit_status' => 0);
								$vat_id = $db->getOne(' SELECT id FROM ' . $ecs->table('users_vat_invoices_info') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1 '));
								$db->autoExecute($ecs->table('users_vat_invoices_info'), $content, 'UPDATE', ' id = \'' . $vat_id . '\' AND user_id = \'' . $user_id . '\' ');
							}

							$sql = ' SELECT * FROM ' . $ecs->table('users_vat_invoices_info') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1 ');
							$res = $db->getRow($sql);
							$new_vat_consignee_list = get_vat_consignee_list($res['id']);
							$smarty->assign('new_vat_consignee_list', $new_vat_consignee_list);

							if (empty($status)) {
								$smarty->assign('action', $action);
							}
							else {
								$smarty->assign('status', $status);
								$smarty->assign('submitted', true);
								$smarty->assign('action', 'vat_invoice_info');
							}

							$smarty->assign('country_list', get_regions());
							$consignee = get_vat_consignee($_REQUEST['vat_id']);
							$country_list = get_regions_log(0, 0);
							$province_list = get_regions_log(1, $consignee['country']);
							$city_list = get_regions_log(2, $consignee['province']);
							$district_list = get_regions_log(3, $consignee['city']);
							$smarty->assign('country_list', $country_list);
							$smarty->assign('province_list', $province_list);
							$smarty->assign('city_list', $city_list);
							$smarty->assign('district_list', $district_list);
							$smarty->assign('consignee', $consignee);
							$audit_status = $res['audit_status'];
							$smarty->assign('audit_status', $audit_status);
							$smarty->assign('vat_id', $vat_id);
							$smarty->assign('vat_info', $res);
							$smarty->display('user_clips.dwt');
						}
						else if ($action == 'flow_inv_form') {
							include_once ROOT_PATH . 'includes/cls_json.php';
							include_once ROOT_PATH . 'includes/lib_visual.php';
							include_once ROOT_PATH . 'includes/lib_transaction.php';
							$result = array('error' => 0, 'content' => '');
							$json = new JSON();
							$obj = $json->decode($_REQUEST['msg']);
							$arr = object_to_array($obj);
							$user_id = $_SESSION['user_id'];
							$status = isset($_POST['status']) ? trim($_POST['status']) : '';
							$content = array('company_name' => $arr['company_name'], 'user_id' => $user_id, 'tax_id' => $arr['tax_id'], 'company_address' => $arr['company_address'], 'company_telephone' => $arr['company_telephone'], 'bank_of_deposit' => $arr['bank_of_deposit'], 'bank_account' => $arr['bank_account'], 'consignee_name' => $arr['consignee_name'], 'consignee_mobile_phone' => $arr['consignee_mobile_phone'], 'country' => $arr['country'], 'province' => $arr['province'], 'city' => $arr['city'], 'district' => $arr['district'], 'consignee_address' => $arr['consignee_address'], 'add_time' => gmtime(), 'audit_status' => 0);
							$vat_id = $db->getOne(' SELECT id FROM ' . $ecs->table('users_vat_invoices_info') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1 '));

							if ($vat_id) {
								$result['error'] = 1;
								$result['content'] = '您已提交过增票资质申请，请勿重复提交！';
							}
							else if ($db->autoExecute($ecs->table('users_vat_invoices_info'), $content, 'INSERT')) {
								$result['content'] = '您的增票资质已提交，等待审核。';
							}

							exit($json->encode($result));
						}
						else if ($action == 'users_log') {
							$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
							$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('users_log') . (' WHERE user_id = \'' . $user_id . '\' AND change_type != 9 ');
							$record_count = $GLOBALS['db']->getOne($sql);
							$pager = get_pager('user.php', array('act' => $action), $record_count, $page);
							$user_log = get_users_log_list($pager['size'], $pager['start'], $user_id);
							$smarty->assign('user_log', $user_log);
							$smarty->assign('page', $page);
							$smarty->assign('pager', $pager);
							$smarty->assign('action', $action);
							$smarty->display('user_transaction.dwt');
						}
						else if ($action == 'illegal_report') {
							include_once ROOT_PATH . 'includes/lib_transaction.php';
							$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
							$sql = 'SELECT report_time FROM ' . $ecs->table('users') . ('WHERE user_id = \'' . $user_id . '\'');
							$report_time = $db->getOne($sql);
							$report_time = local_date('Y-m-d H:i:s', $report_time);
							$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods_report') . (' WHERE user_id = \'' . $user_id . '\'');
							$record_count = $GLOBALS['db']->getOne($sql);
							$pager = get_pager('user.php', array('act' => $action), $record_count, $page);
							$goods_report = get_goods_report_list($pager['size'], $pager['start']);
							$smarty->assign('goods_report', $goods_report);
							$smarty->assign('page', $page);
							$smarty->assign('pager', $pager);
							$smarty->assign('report_time', $report_time);
							$smarty->assign('action', $action);
							$smarty->display('user_clips.dwt');
						}
						else if ($action == 'goods_report') {
							assign_template();
							$report_id = !empty($_REQUEST['report_id']) ? intval($_REQUEST['report_id']) : 0;
							$goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
							$where = '';

							if (0 < $goods_id) {
								$new_time = gmtime();
								$sql = 'SELECT report_time FROM ' . $ecs->table('users') . ('WHERE user_id = \'' . $user_id . '\'');
								$report_time = $db->getOne($sql);

								if ($new_time < $report_time) {
									show_message($_LANG['malice_report'], $_LANG['back_report_list'], 'user.php?act=illegal_report');
								}

								$sql = 'SELECT count(*) FROM' . $ecs->table('goods_report') . ('WHERE goods_id=\'' . $goods_id . '\' AND user_id = \'' . $user_id . '\' AND report_state = 0');
								$goods_report_count = $db->getOne($sql);

								if (0 < $goods_report_count) {
									show_message($_LANG['repeat_report']);
								}

								$goods_info = get_goods_info($goods_id);
								$goods_info['shop_name'] = get_shop_name($goods_info['user_id'], 1);

								if ($goods_info['is_on_sale'] == 0) {
									show_message($_LANG['offgoods_report']);
								}

								$report_type = get_goods_report_type();
								$report_title = array();

								if ($report_type) {
									$report_title = get_goods_report_title($report_type[0]['type_id']);
								}

								$smarty->assign('sessid', SESS_ID);
								$smarty->assign('report_type', $report_type);
								$smarty->assign('report_title', $report_title);
								$where .= ' AND goods_id = \'' . $goods_id . '\' AND report_id = 0';
							}
							else if (0 < $report_id) {
								$goods_report_info = array('goods_id' => 0, 'goods_name' => '', 'goods_thumb' => '');
								$sql = 'SELECT g.report_id , g.user_id , g.user_name , g.goods_id , g.goods_name , g.goods_image , g.title_id , g.type_id , ' . 'g.inform_content , g.add_time , g.report_state , g.handle_type , g.handle_message , g.handle_time , g.admin_id , ' . 'gt.type_name , gt.type_desc , ge.title_name FROM' . $ecs->table('goods_report') . ' AS g ' . 'LEFT JOIN ' . $ecs->table('goods_report_type') . ' AS gt ON gt.type_id = g.type_id ' . 'LEFT JOIN ' . $ecs->table('goods_report_title') . ' AS ge ON ge.title_id=g.title_id ' . ('WHERE g.report_id = \'' . $report_id . '\' AND g.user_id = \'' . $user_id . '\' LIMIT 1');
								$goods_report_info = $db->getRow($sql);
								$goods_info['goods_id'] = $goods_report_info['goods_id'];
								$goods_info['goods_name'] = $goods_report_info['goods_name'];
								$goods_info['goods_thumb'] = get_image_path($goods_report_info['goods_id'], $goods_report_info['goods_image']);
								$sql = 'SELECT user_id FROM' . $GLOBALS['ecs']->table('goods') . 'WHERE goods_id = \'' . $goods_report_info['goods_id'] . '\' LIMIT 1';
								$basic_info = get_seller_shopinfo($GLOBALS['db']->getOne($sql));
								$goods_info['shop_name'] = $basic_info['shop_name'];
								$smarty->assign('goods_report_info', $goods_report_info);
								$where .= 'AND report_id = \'' . $report_id . '\' AND goods_id = \'' . $goods_report_info['goods_id'] . '\'';
							}

							$goods_info['url'] = build_uri('goods', array('gid' => $goods_info['goods_id']), $goods_info['goods_name']);
							$img_list = report_images_list($user_id, $where);
							$smarty->assign('report_id', $report_id);
							$smarty->assign('img_list', $img_list);
							$smarty->assign('goods_info', $goods_info);
							$smarty->assign('action', $action);
							$smarty->assign('page_title', $_LANG['report_goods']);
							$smarty->assign('helps', get_shop_help());
							$smarty->display('user_clips.dwt');
						}
						else if ($action == 'goods_report_submit') {
							$goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
							$goods_name = !empty($_REQUEST['goods_name']) ? trim($_REQUEST['goods_name']) : '';
							$goods_image = !empty($_REQUEST['goods_image']) ? trim($_REQUEST['goods_image']) : '';
							$title_id = !empty($_REQUEST['title_id']) ? intval($_REQUEST['title_id']) : 0;
							$type_id = !empty($_REQUEST['type_id']) ? intval($_REQUEST['type_id']) : 0;
							$inform_content = !empty($_REQUEST['inform_content']) ? trim($_REQUEST['inform_content']) : '';

							if ($title_id == 0) {
								show_message($_LANG['title_null']);
							}
							else if ($type_id == 0) {
								show_message($_LANG['type_null']);
							}
							else if ($inform_content == '') {
								show_message($_LANG['inform_content_null']);
							}
							else {
								$time = gmtime();
								$other = array('user_id' => $user_id, 'user_name' => $_SESSION['user_name'], 'goods_id' => $goods_id, 'goods_name' => $goods_name, 'goods_image' => $goods_image, 'title_id' => $title_id, 'type_id' => $type_id, 'inform_content' => $inform_content, 'add_time' => $time);
								$db->autoExecute($ecs->table('goods_report'), $other, 'INSERT');
								$report_id = $db->insert_id();

								if (0 < $report_id) {
									$sql = 'UPDATE' . $ecs->table('goods_report_img') . (' SET report_id = \'' . $report_id . '\' WHERE user_id = \'' . $user_id . '\' AND goods_id = \'' . $goods_id . '\' AND report_id = 0');
									$db->query($sql);
								}

								show_message($_LANG['report_success'], $_LANG['back_report_list'], 'user.php?act=illegal_report');
							}
						}
						else {
							if ($action == 'auction' || $action == 'auction_order_recycle') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';

								if ($action == 'auction') {
									$show_type = 0;
								}
								else if ($action == 'auction_order_recycle') {
									$show_type = 1;
								}

								$order_type = isset($_REQUEST['order_type']) ? addslashes(trim($_REQUEST['order_type'])) : '';
								$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
								$smarty->assign('status_list', $_LANG['cs']);

								if ($action == 'auction') {
									$type = 0;
									$smarty->assign('action', $action);
								}
								else if ($action == 'auction_order_recycle') {
									$type = 1;
									$smarty->assign('action', $action);
								}

								if (defined('THEME_EXTENSION')) {
									$allorders = get_order_where_count($user_id, $show_type = 0, $where = '', $action);
									$smarty->assign('allorders', $allorders);
								}

								$where_zc_order = ' AND oi.is_zc_order = 0 ';
								$where_stay = ' AND   oi.order_status = \'' . OS_UNCONFIRMED . '\'' . $where_zc_order;
								$unconfirmed = get_order_where_count($user_id, $type, $where_stay, $action);
								$smarty->assign('unconfirmed', $unconfirmed);
								$where_pay = ' AND   oi.order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) . ' AND oi.pay_status ' . db_create_in(array(PS_UNPAYED, PS_PAYED_PART)) . ' AND ( oi.shipping_status ' . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . ' OR oi.pay_id ' . db_create_in(get_payment_id_list(false)) . ') ' . $where_zc_order;
								$pay_count = get_order_where_count($user_id, $type, $where_pay, $action);
								$smarty->assign('pay_count', $pay_count);
								$where_confirmed = ' AND oi.order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) . '  AND oi.shipping_status = \'' . SS_SHIPPED . '\' AND oi.pay_status ' . db_create_in(array(PS_PAYED, PS_PAYING)) . $where_zc_order;
								$to_confirm_order = get_order_where_count($user_id, $type, $where_confirmed, $action);
								$smarty->assign('to_confirm_order', $to_confirm_order);
								$where_complete = ' AND oi.order_status ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) . '  AND oi.shipping_status = \'' . SS_RECEIVED . '\' AND oi.pay_status ' . db_create_in(array(PS_PAYED, PS_PAYING)) . $where_zc_order;
								$to_finished = get_order_where_count($user_id, $type, $where_complete, $action);
								$smarty->assign('to_finished', $to_finished);
								$order_where = '';

								if (str_len($order_type) == str_len('toBe_unconfirmed')) {
									$order_where = 1;
								}
								else if (str_len($order_type) == str_len('toBe_pay')) {
									$order_where = 2;
								}
								else if (str_len($order_type) == str_len('toBe_confirmed')) {
									$order_where = 3;
								}
								else if (str_len($order_type) == str_len('toBe_finished')) {
									$order_where = 4;
								}
								else {
									$order_where = 0;
								}

								$auction = get_user_orders($user_id, $allorders, $page, $type, '', $action);
								$smarty->assign('orders', $auction);
								$smarty->display('user_transaction.dwt');
							}
							else if ($action == 'auction_list') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								$time = gmtime();
								$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
								$all_auction = get_all_auction($user_id, $type = '');
								$smarty->assign('all_auction', $all_auction);
								$on_type = ' AND ga.is_finished= 0 AND ga.end_time > ' . $time . ' AND  ga.start_time < ' . $time . ' ';
								$is_going = get_all_auction($user_id, $on_type);
								$smarty->assign('is_going', $is_going);
								$finished_type = ' AND (ga.is_finished > 0 OR ga.is_finished = 0 AND ga.end_time < ' . $time . ')';
								$is_finished = get_all_auction($user_id, $finished_type);
								$smarty->assign('is_finished', $is_finished);
								$order_where = '';

								if (str_len($order_type) == str_len('toBe_unconfirmed')) {
									$order_where = 1;
								}
								else if (str_len($order_type) == str_len('toBe_pay')) {
									$order_where = 2;
								}
								else if (str_len($order_type) == str_len('toBe_confirmed')) {
									$order_where = 3;
								}
								else if (str_len($order_type) == str_len('toBe_finished')) {
									$order_where = 4;
								}
								else {
									$order_where = 0;
								}

								$auction_list = get_auction_list($user_id, $all_auction, $page);
								$smarty->assign('auction_list', $auction_list);
								$smarty->display('user_transaction.dwt');
							}
							else if ($action == 'auction_to_query') {
								include_once 'includes/cls_json.php';
								$_POST['auction'] = strip_tags(urldecode($_POST['auction']));
								$_POST['auction'] = json_str_iconv($_POST['auction']);
								$result = array('error' => 0, 'message' => '', 'content' => '', 'order_id' => '');
								$json = new JSON();
								$auction = $json->decode($_POST['auction']);
								$auction->keyword = addslashes(trim($auction->keyword));
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								$where = get_auction_search_keyword($auction);
								$left_join = '';
								$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
								$all_auction = get_all_auction($user_id, $where);
								$auction_list = get_auction_list($user_id, $all_auction, $page, $where, $auction);

								if ($order->idTxt == 'submitDate') {
									$date_keyword = $order->keyword;
									$status_keyword = $order->status_keyword;
								}
								else if ($order->idTxt == 'status_list') {
									$date_keyword = $order->date_keyword;
									$status_keyword = $order->keyword;
								}
								else {
									if ($order->idTxt == 'payId' || $order->idTxt == 'to_finished' || $order->idTxt == 'to_confirm_order' || $order->idTxt == 'to_unconfirmed' || $order->idTxt == 'signNum') {
										$status_keyword = $order->keyword;
									}
								}

								$result['date_keyword'] = $date_keyword;
								$result['status_keyword'] = $status_keyword;
								$smarty->assign('auction_list', $auction_list);
								$smarty->assign('date_keyword', $date_keyword);
								$smarty->assign('status_keyword', $status_keyword);
								$insert_arr = array('act' => $order->action, 'filename' => 'user');
								$smarty->assign('no_records', insert_get_page_no_records($insert_arr));
								$smarty->assign('open_delivery_time', $GLOBALS['_CFG']['open_delivery_time']);
								$smarty->assign('action', $order->action);
								$result['content'] = $smarty->fetch('library/user_auction_list.lbi');
								exit($json->encode($result));
							}
							else if ($action == 'snatch_to_query') {
								include_once 'includes/cls_json.php';
								$_POST['snatch'] = strip_tags(urldecode($_POST['snatch']));
								$_POST['snatch'] = json_str_iconv($_POST['snatch']);
								$result = array('error' => 0, 'message' => '', 'content' => '', 'order_id' => '');
								$json = new JSON();
								$snatch = $json->decode($_POST['snatch']);
								$snatch->keyword = addslashes(trim($snatch->keyword));
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								$where = get_snatch_search_keyword($snatch);
								$left_join = '';
								$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
								$all_snatch = get_all_snatch($user_id, $where);
								$snatch_list = get_snatch_list($user_id, $all_auction, $page, $where, $snatch);

								if ($order->idTxt == 'submitDate') {
									$date_keyword = $order->keyword;
									$status_keyword = $order->status_keyword;
								}
								else if ($order->idTxt == 'status_list') {
									$date_keyword = $order->date_keyword;
									$status_keyword = $order->keyword;
								}
								else {
									if ($order->idTxt == 'payId' || $order->idTxt == 'to_finished' || $order->idTxt == 'to_confirm_order' || $order->idTxt == 'to_unconfirmed' || $order->idTxt == 'signNum') {
										$status_keyword = $order->keyword;
									}
								}

								$result['date_keyword'] = $date_keyword;
								$result['status_keyword'] = $status_keyword;
								$smarty->assign('snatch_list', $snatch_list);
								$smarty->assign('date_keyword', $date_keyword);
								$smarty->assign('status_keyword', $status_keyword);
								$insert_arr = array('act' => $order->action, 'filename' => 'user');
								$smarty->assign('no_records', insert_get_page_no_records($insert_arr));
								$smarty->assign('open_delivery_time', $GLOBALS['_CFG']['open_delivery_time']);
								$smarty->assign('action', $order->action);
								$result['content'] = $smarty->fetch('library/user_snatch_list.lbi');
								exit($json->encode($result));
							}
							else if ($action == 'snatch_list') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								$time = gmtime();
								$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
								$all_snatch = get_all_snatch($user_id, $type = '');
								$smarty->assign('all_snatch', $all_snatch);
								$on_type = ' AND ga.is_finished= 0 AND ga.end_time > ' . $time . ' AND  ga.start_time < ' . $time . ' ';
								$is_going = get_all_snatch($user_id, $on_type);
								$smarty->assign('is_going', $is_going);
								$finished_type = ' AND (ga.is_finished > 0 OR ga.is_finished = 0 AND ga.end_time < ' . $time . ')';
								$is_finished = get_all_snatch($user_id, $finished_type);
								$smarty->assign('is_finished', $is_finished);
								$order_where = '';

								if (str_len($order_type) == str_len('toBe_unconfirmed')) {
									$order_where = 1;
								}
								else if (str_len($order_type) == str_len('toBe_pay')) {
									$order_where = 2;
								}
								else if (str_len($order_type) == str_len('toBe_confirmed')) {
									$order_where = 3;
								}
								else if (str_len($order_type) == str_len('toBe_finished')) {
									$order_where = 4;
								}
								else {
									$order_where = 0;
								}

								$snatch_list = get_snatch_list($user_id, $all_snatch, $page);
								$smarty->assign('snatch_list', $snatch_list);
								$smarty->display('user_transaction.dwt');
							}
							else if ($action == 'wholesale_buy') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/wholesale.php';
								$smarty->assign('lang', $_LANG);
								$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
								$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('wholesale_order_info') . ' as oi_1' . (' WHERE oi_1.user_id = \'' . $user_id . '\' and oi_1.is_delete = 0 ') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('wholesale_order_info') . ' as oi_2 where oi_2.main_order_id = oi_1.order_id) = 0 ');

								if (judge_supplier_enabled()) {
									$wholesale_orders = get_wholesale_orders($user_id, $record_count, $page, '', $action);
								}
								else {
									$wholesale_orders = get_wholesale_orders($user_id, $record_count, $page);
								}

								$user_id = $_SESSION['user_id'];
								$smarty->assign('orders', $wholesale_orders);
								$smarty->assign('action', $action);
								$smarty->display('user_transaction.dwt');
							}
							else if ($action == 'wholesale_affirm_received') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

								if (wholesale_affirm_received($order_id, $user_id)) {
									ecs_header("Location: user.php?act=wholesale_buy\n");
									exit();
								}
								else {
									$err->show($_LANG['order_list_lnk'], 'user.php?act=wholesale_buy');
								}
							}
							else if ($action == 'delete_wholesale_order') {
								include_once 'includes/cls_json.php';
								$_POST['order'] = strip_tags(urldecode($_POST['order']));
								$_POST['order'] = json_str_iconv($_POST['order']);
								$result = array('error' => 0, 'message' => '', 'content' => '', 'order_id' => '');
								$json = new JSON();

								if (0 < $order->order_id) {
									$result['error'] = 1;
									exit($json->encode($result));
								}

								$order = $json->decode($_POST['order']);
								$order_id = $order->order_id;
								$result['order_id'] = $order_id;
								$type = 1;
								$parent = array('is_delete' => $type);
								$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('wholesale_order_info'), $parent, 'UPDATE', 'order_id = \'' . $order_id . '\'');
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
								$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('wholesale_order_info') . (' WHERE user_id = \'' . $user_id . '\' and is_delete = 0'));
								$action = 'wholesale_buy';
								$pager = get_pager('user.php', array('act' => $action), $record_count, $page);

								if (judge_supplier_enabled()) {
									$orders = get_wholesale_orders($user_id, $pager['size'], $pager['start'], '', $action);
								}
								else {
									$orders = get_wholesale_orders($user_id, $pager['size'], $pager['start']);
								}

								$smarty->assign('pager', $pager);
								$smarty->assign('orders', $orders);
								$insert_arr = array('act' => $order->action, 'filename' => 'user');
								$smarty->assign('no_records', insert_get_page_no_records($insert_arr));
								$result['content'] = $smarty->fetch('library/user_wholesale_order_list.lbi');
								$result['page_content'] = $smarty->fetch('library/pages.lbi');
								exit($json->encode($result));
							}
							else if ($action == 'wholesale_order_to_query') {
								include_once 'includes/cls_json.php';
								include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/wholesale.php';
								$smarty->assign('lang', $_LANG);
								$_POST['order'] = strip_tags(urldecode($_POST['order']));
								$_POST['order'] = json_str_iconv($_POST['order']);
								$result = array('error' => 0, 'message' => '', 'content' => '', 'order_id' => '');
								$json = new JSON();
								$order = $json->decode($_POST['order']);
								$order->keyword = addslashes(trim($order->keyword));

								if (0 < $order->order_id) {
									$result['error'] = 1;
									exit($json->encode($result));
								}

								$show_type = 0;
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								$where = get_wholesale_order_search_keyword($order);
								$left_join = '';

								if (defined('THEME_EXTENSION')) {
									if ($order->idTxt == 'signNum') {
										$where .= ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' AS c WHERE c.comment_type = 0 AND c.id_value = g.goods_id AND c.rec_id = og.rec_id AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\') = 0 AND og.order_id = oi.order_id ');
									}

									$left_join = ' LEFT JOIN ' . $ecs->table('goods') . ' AS g ON g.goods_id = og.goods_id ';
								}

								$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
								$record_count = $db->getAll('SELECT oi.order_id FROM ' . $ecs->table('wholesale_order_info') . ' as oi' . ' left join ' . $ecs->table('wholesale_order_goods') . ' as og on oi.order_id = og.order_id' . $left_join . (' WHERE oi.user_id = \'' . $user_id . '\' and oi.is_delete = \'' . $show_type . '\' ') . ' and (select count(*) from ' . $GLOBALS['ecs']->table('wholesale_order_info') . ' as oi_2 where oi_2.main_order_id = oi.order_id) = 0 ' . $where . ' group by oi.order_id');
								$record_count = count($record_count);
								$orders = get_wholesale_orders($user_id, $record_count, $page, $where, $order);

								if ($order->idTxt == 'submitDate') {
									$date_keyword = $order->keyword;
									$status_keyword = $order->status_keyword;
								}
								else if ($order->idTxt == 'wholesale_status_list') {
									$date_keyword = $order->date_keyword;
									$status_keyword = $order->keyword;
								}

								$result['date_keyword'] = $date_keyword;
								$result['status_keyword'] = $status_keyword;
								$smarty->assign('orders', $orders);
								$smarty->assign('wholesale_status_list', $_LANG['cs']);
								$smarty->assign('date_keyword', $date_keyword);
								$smarty->assign('status_keyword', $status_keyword);
								$insert_arr = array('act' => $order->action, 'filename' => 'user');
								$smarty->assign('no_records', insert_get_page_no_records($insert_arr));
								$smarty->assign('open_delivery_time', $GLOBALS['_CFG']['open_delivery_time']);
								$smarty->assign('action', $order->action);
								$result['content'] = $smarty->fetch('library/user_wholesale_order_list.lbi');
								exit($json->encode($result));
							}
							else if ($action == 'wholesale_purchase') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/wholesale_purchase.php';
								$smarty->assign('lang', $_LANG);
								$user_id = $_SESSION['user_id'];
								$smarty->assign('action', $action);
								$keyword = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
								$start_date = isset($_REQUEST['start_date']) ? trim($_REQUEST['start_date']) : '';
								$end_date = isset($_REQUEST['end_date']) ? trim($_REQUEST['end_date']) : '';
								$review_status = isset($_REQUEST['review_status']) ? intval($_REQUEST['review_status']) : -1;
								$filter_array = array();
								$query_array = array();
								$query_array['act'] = 'wholesale_purchase';
								$filter_array['user_id'] = $user_id;

								if ($review_status != -1) {
									$query_array['review_status'] = $review_status;
									$filter_array['review_status'] = $review_status;
								}

								if (!empty($keyword)) {
									$query_array['keyword'] = $keyword;
									$filter_array['keyword'] = $keyword;
								}

								if (!empty($start_date)) {
									$query_array['start_date'] = $start_date;
									$filter_array['start_date'] = local_strtotime($start_date);
								}

								if (!empty($end_date)) {
									$query_array['end_date'] = $end_date;
									$filter_array['end_date'] = local_strtotime($end_date);
								}

								$size = 10;
								$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
								$purchase_list = get_purchase_list($filter_array, $size, $page);
								$pager = get_pager('user.php', $query_array, $purchase_list['record_count'], $page, $size);
								$smarty->assign('pager', $pager);
								$smarty->assign('purchase_list', $purchase_list['purchase_list']);
								$review_status_array = array();
								$review_status_array[-1] = get_table_date('wholesale_purchase', 'user_id=\'' . $user_id . '\'', array('COUNT(*)'), 2);
								$review_status_array[0] = get_table_date('wholesale_purchase', 'user_id=\'' . $user_id . '\' AND review_status=0', array('COUNT(*)'), 2);
								$review_status_array[1] = get_table_date('wholesale_purchase', 'user_id=\'' . $user_id . '\' AND review_status=1', array('COUNT(*)'), 2);
								$review_status_array[2] = get_table_date('wholesale_purchase', 'user_id=\'' . $user_id . '\' AND review_status=2', array('COUNT(*)'), 2);
								$smarty->assign('review_status_array', $review_status_array);
								$smarty->display('user_transaction.dwt');
							}
							else if ($action == 'purchase_info') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/wholesale_purchase.php';
								$smarty->assign('lang', $_LANG);
								$user_id = $_SESSION['user_id'];
								$smarty->assign('action', $action);
								$purchase_id = empty($_REQUEST['purchase_id']) ? 0 : intval($_REQUEST['purchase_id']);
								$purchase_info = get_purchase_info($purchase_id);
								$smarty->assign('purchase_info', $purchase_info);
								$smarty->display('user_transaction.dwt');
							}
							else if ($action == 'purchase_edit') {
								$data = array();
								$data['purchase_id'] = empty($_REQUEST['purchase_id']) ? 0 : intval($_REQUEST['purchase_id']);
								$data['supplier_company_name'] = empty($_REQUEST['supplier_company_name']) ? '' : trim($_REQUEST['supplier_company_name']);
								$data['supplier_contact_phone'] = empty($_REQUEST['supplier_contact_phone']) ? '' : trim($_REQUEST['supplier_contact_phone']);
								$data['status'] = 1;
								$db->autoExecute($ecs->table('wholesale_purchase'), $data, 'UPDATE', 'purchase_id=\'' . $data['purchase_id'] . '\'');
								show_message('编辑求购单信息成功', '返回列表', 'user.php?act=wholesale_purchase', 'info');
							}
							else if ($action == 'purchase_delete') {
								$purchase_id = empty($_REQUEST['purchase_id']) ? 0 : intval($_REQUEST['purchase_id']);
								$sql = ' DELETE FROM ' . $GLOBALS['ecs']->table('wholesale_purchase') . (' WHERE purchase_id = \'' . $purchase_id . '\' ');
								$GLOBALS['db']->query($sql);
								$goods_list = get_table_date('wholesale_purchase_goods', 'purchase_id=\'' . $purchase_id . '\'', array('goods_id', 'goods_img'), 1);

								foreach ($goods_list as $key => $val) {
									if (!empty($val['goods_img'])) {
										$goods_img = unserialize($val['goods_img']);

										foreach ($goods_img as $k => $v) {
											@unlink(ROOT_PATH . $v);
										}
									}
								}

								$sql = ' DELETE FROM ' . $GLOBALS['ecs']->table('wholesale_purchase_goods') . (' WHERE purchase_id = \'' . $purchase_id . '\' ');
								$GLOBALS['db']->query($sql);
								show_message('删除求购单信息成功', '返回列表', 'user.php?act=wholesale_purchase', 'info');
							}
							else if ($action == 'change_order_status') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
								$is_refund = isset($_GET['is_refund']) ? intval($_GET['is_refund']) : 0;
								$set_where = '';

								if ($is_refund) {
									$set_where = ', order_status = \'' . OS_RETURNED . '\'';
								}

								$sql = 'UPDATE ' . $GLOBALS['ecs']->table('wholesale_order_info') . (' SET is_refund = \'' . $is_refund . '\' ' . $set_where . ' WHERE order_id = \'' . $order_id . '\' AND user_id = \'' . $user_id . '\' ');

								if ($db->query($sql)) {
									ecs_header("Location: user.php?act=wholesale_buy\n");
									exit();
								}
							}
							else if ($action == 'apply_suppliers') {
								$user_id = $_SESSION['user_id'];
								$is_edit = $_REQUEST['is_edit'];
								$sql = ' SELECT suppliers_id FROM ' . $ecs->table('suppliers') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1 ');
								$row = $db->getRow($sql);
								$smarty->assign('country_list', get_regions());
								$smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));
								$first_cate = get_first_cate_list();
								$smarty->assign('first_cate', $first_cate);
								if ($row && !$is_edit) {
									$smarty->assign('is_applied', 1);
								}
								else {
									$smarty->assign('suppliers_info', $row);
								}

								$smarty->display('user_transaction.dwt');
							}
							else if ($action == 'supplier_info') {
								$supplier_info['user_id'] = $_SESSION['user_id'];
								$supplier_info['real_name'] = dsc_addslashes(trim($_POST['real_name']));
								$supplier_info['self_num'] = dsc_addslashes(trim($_POST['self_num']));
								$supplier_info['company_name'] = dsc_addslashes(trim($_POST['company_address']));
								$supplier_info['company_address'] = dsc_addslashes(trim($_POST['company_address']));
								$textfile_zheng = dsc_addslashes(trim($_POST['textfile_zheng']));
								$textfile_fan = dsc_addslashes(trim($_POST['textfile_fan']));
								$supplier_info['mobile_phone'] = dsc_addslashes(trim($_POST['mobile_phone']));
								$supplier_info['add_time'] = gmtime();
								$supplier_info['review_status'] = 0;

								if (empty($_FILES['front_of_id_card']['size'])) {
									$front_name = $textfile_zheng;
								}
								else {
									$front_name = $image->upload_image($_FILES['front_of_id_card'], 'idcard');
									get_oss_add_file(array($front_name));
								}

								if (empty($_FILES['reverse_of_id_card']['size'])) {
									$reverse_name = $textfile_fan;
								}
								else {
									$reverse_name = $image->upload_image($_FILES['reverse_of_id_card'], 'idcard');
									get_oss_add_file(array($reverse_name));
								}

								$supplier_info['front_of_id_card'] = $front_name;
								$supplier_info['reverse_of_id_card'] = $reverse_name;
								$count_suppliers = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('suppliers') . (' WHERE user_id = \'' . $user_id . '\' '));

								if ($count_suppliers) {
									if ($db->autoExecute($ecs->table('suppliers'), $supplier_info, 'UPDATE', 'user_id=\'' . $user_id . '\' AND user_type = 0')) {
										$Loaction = 'user.php?act=apply_suppliers';
										ecs_header('Location: ' . $Loaction . "\n");
									}
								}
								else if ($db->autoExecute($ecs->table('suppliers'), $supplier_info, 'INSERT')) {
									$Loaction = 'user.php?act=apply_suppliers';
									ecs_header('Location: ' . $Loaction . "\n");
								}

								$smarty->display('user_transaction.dwt');
							}
							else if ($action == 'addChildCate') {
								include_once ROOT_PATH . 'includes/cls_json.php';
								$cat_id = isset($_REQUEST['cat_id']) ? trim($_REQUEST['cat_id']) : 0;
								$type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
								$user_id = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
								$json = new JSON();
								$result = array('error' => 0, 'content' => '');
								$filter = $json->decode($_GET['JSON']);

								if (0 < $user_id) {
									if ($type == 1) {
										$_POST['cateArr'] = strip_tags(urldecode($_POST['cateArr']));
										$_POST['cateArr'] = json_str_iconv($_POST['cateArr']);
										$cat = $json->decode($_POST['cateArr']);
										$catarr = $cat->cat_id;
									}

									$cate_list = get_first_cate_list($filter->cat_id, $filter->type, $catarr, $filter->cat_id);

									if (!$filter->cat_id) {
										$cate_list = array();
									}

									$smarty->assign('user_center', 1);
									$smarty->assign('cate_list', $cate_list);
									$smarty->assign('cat_id', $cat_id);
									$result['content'] = $smarty->fetch('library/merchants_cate_list.lbi');
								}
								else {
									$result['error'] = 1;
									$result['message'] = $_LANG['login_again'];
								}

								exit($json->encode($result));
							}
							else if ($action == 'wholesale_batch_applied') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								include_once ROOT_PATH . 'includes/lib_order.php';
								include_once ROOT_PATH . 'includes/lib_suppliers.php';

								if (isset($_REQUEST['checkboxes'])) {
									$order_id = intval($_REQUEST['order_id']);
									$order = wholesale_order_info($order_id);
									$error = 0;
									$cause_arr = '';

									foreach ($_REQUEST['checkboxes'] as $key => $val) {
										$goods = wholesale_rec_goods($val);
										$goods_info = get_table_date('wholesale', 'goods_id=\'' . $goods['goods_id'] . '\'', array('goods_cause'));

										if (empty($cause_arr)) {
											$cause_arr = explode(',', $goods_info['goods_cause']);
										}

										$cause_arr_next = explode(',', $goods_info['goods_cause']);

										if (!$goods_info['goods_cause']) {
											$error += 1;
										}
										else {
											if ($cause_arr) {
												$cause_arr = array_intersect($cause_arr, $cause_arr_next);
											}

											$goods_info_arr[$key] = $goods;
											$suppliers_name = $goods['suppliers_name'];
											$rec_ids[] = $goods['rec_id'];
										}
									}

									if ($error) {
										show_message($_LANG['nonsupport_return_goods'], '', '', 'info', true);
									}
									else {
										$cause_str = implode(',', $cause_arr);
										$goods_cause = get_goods_cause($cause_str, $order['chargeoff_status']);
									}
								}
								else {
									show_message($_LANG['please_select_goods'], '', '', 'info', true);
								}

								$parent_cause = get_parent_cause();
								$consignee = get_consignee($_SESSION['user_id']);
								$smarty->assign('consignee', $consignee);
								$smarty->assign('show_goods_thumb', $GLOBALS['_CFG']['show_goods_in_cart']);
								$smarty->assign('show_goods_attribute', $GLOBALS['_CFG']['show_attr_in_cart']);
								$smarty->assign('goods', $goods_info_arr);
								$smarty->assign('goods_return', $goods_info_arr);
								$smarty->assign('suppliers_name', $suppliers_name);
								$smarty->assign('rec_ids', implode('-', $rec_ids));
								$smarty->assign('order_id', $order_id);
								$smarty->assign('cause_list', $parent_cause);
								$smarty->assign('order_sn', $order['order_sn']);
								$smarty->assign('order', $order);
								$country_list = get_regions_log(0, 0);
								$province_list = get_regions_log(1, $consignee['country']);
								$city_list = get_regions_log(2, $consignee['province']);
								$district_list = get_regions_log(3, $consignee['city']);
								$street_list = get_regions_log(4, $consignee['district']);
								$smarty->assign('goods_cause', $goods_cause);
								$sn = 0;
								$smarty->assign('country_list', $country_list);
								$smarty->assign('province_list', $province_list);
								$smarty->assign('city_list', $city_list);
								$smarty->assign('district_list', $district_list);
								$smarty->assign('street_list', $street_list);
								$smarty->assign('sn', $sn);
								$smarty->assign('sessid', SESS_ID);
								$smarty->assign('return_pictures', $GLOBALS['_CFG']['return_pictures']);
								$smarty->display('user_transaction.dwt');
							}
							else if ($action == 'wholesale_submit_batch_return') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								include_once ROOT_PATH . 'includes/lib_order.php';

								if ($_REQUEST['return_rec_id']) {
									$return_remark = !isset($_REQUEST['return_remark']) ? '' : addslashes(trim($_REQUEST['return_remark']));
									$return_brief = !isset($_REQUEST['return_brief']) ? '' : addslashes(trim($_REQUEST['return_brief']));
									$chargeoff_status = !isset($_REQUEST['chargeoff_status']) && empty($_REQUEST['chargeoff_status']) ? 0 : intval($_REQUEST['chargeoff_status']);

									foreach ($_REQUEST['return_rec_id'] as $rec_id) {
										if (0 < $rec_id) {
											$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('wholesale_order_return') . ' WHERE rec_id = ' . $rec_id;
											$num = $db->getOne($sql);

											if (0 < $num) {
												show_message($_LANG['Repeated_submission'], '', '', 'info', true);
											}
										}
										else {
											show_message($_LANG['Return_abnormal'], '', '', 'info', true);
										}

										$sql = 'select g.goods_name, g.goods_sn,g.brand_id, og.order_id, og.goods_id, og.product_id, og.goods_attr, ' . ' og.goods_attr_id, og.goods_price, og.goods_price, og.goods_number ' . 'from ' . $ecs->table('wholesale_order_goods') . ' as og ' . ' left join ' . $ecs->table('wholesale') . ' as g on og.goods_id = g.goods_id ' . (' where og.rec_id = \'' . $rec_id . '\'');
										$order_goods = $db->getRow($sql);
										$sql = ' SELECT order_sn, country,province,city ,district FROM ' . $GLOBALS['ecs']->table('wholesale_order_info') . ' WHERE order_id =' . $order_goods['order_id'];
										$res = $GLOBALS['db']->getRow($sql);
										$return_number = $goods_number = $order_goods['goods_number'];
										$return_type = intval($_REQUEST['return_type']);
										$maintain = 0;
										$return_status = 0;

										if ($return_type == 1) {
											$back = 1;
											$exchange = 0;
										}
										else if ($return_type == 2) {
											$back = 0;
											$exchange = 2;
										}
										else if ($return_type == 3) {
											$back = 0;
											$exchange = 0;
											$return_status = -1;
										}
										else {
											$back = 0;
											$exchange = 0;
										}

										$order_return = array('rec_id' => $rec_id, 'goods_id' => $order_goods['goods_id'], 'order_id' => $order_goods['order_id'], 'order_sn' => $order_goods['goods_sn'], 'chargeoff_status' => $chargeoff_status, 'return_type' => $return_type, 'maintain' => $maintain, 'back' => $back, 'exchange' => $exchange, 'user_id' => $_SESSION['user_id'], 'return_brief' => $return_brief, 'remark' => $return_remark, 'credentials' => !isset($_REQUEST['credentials']) ? 0 : intval($_REQUEST['credentials']), 'country' => empty($_REQUEST['country']) ? 0 : intval($_REQUEST['country']), 'province' => empty($_REQUEST['province']) ? 0 : intval($_REQUEST['province']), 'city' => empty($_REQUEST['city']) ? 0 : intval($_REQUEST['city']), 'district' => empty($_REQUEST['district']) ? 0 : intval($_REQUEST['district']), 'street' => empty($_REQUEST['street']) ? 0 : intval($_REQUEST['street']), 'cause_id' => $last_option, 'apply_time' => gmtime(), 'actual_return' => '', 'address' => empty($_REQUEST['return_address']) ? '' : addslashes(trim($_REQUEST['return_address'])), 'zipcode' => empty($_REQUEST['code']) ? '' : intval($_REQUEST['code']), 'addressee' => empty($_REQUEST['addressee']) ? '' : addslashes(trim($_REQUEST['addressee'])), 'phone' => empty($_REQUEST['mobile']) ? '' : addslashes(trim($_REQUEST['mobile'])), 'return_status' => $return_status);

										if (in_array($return_type, array(1, 3))) {
											$return_info = get_wholesale_return_refound($order_return['order_id'], $order_return['rec_id'], $return_number);
											$order_return['should_return'] = $return_info['return_price'];
											$order_return['return_shipping_fee'] = $return_info['return_shipping_fee'];
										}
										else {
											$order_return['should_return'] = 0;
											$order_return['return_shipping_fee'] = 0;
										}

										$error_no = 0;

										do {
											$order_return['return_sn'] = get_order_sn();
											$query = $db->autoExecute($ecs->table('wholesale_order_return'), $order_return, 'INSERT', '', 'SILENT');
											$error_no = $GLOBALS['db']->errno();
											if (0 < $error_no && $error_no != 1062) {
												exit($GLOBALS['db']->errorMsg());
											}
										} while ($error_no == 1062);

										if ($query) {
											$ret_id = $db->insert_id();
											wholesale_return_action($ret_id, $_LANG['Apply_refund'], '', $order_return['remark'], $_LANG['buyer']);
											$return_goods['rec_id'] = $order_return['rec_id'];
											$return_goods['ret_id'] = $ret_id;
											$return_goods['goods_id'] = $order_goods['goods_id'];
											$return_goods['goods_name'] = $order_goods['goods_name'];
											$return_goods['brand_name'] = $order_goods['brand_name'];
											$return_goods['product_id'] = $order_goods['product_id'];
											$return_goods['goods_sn'] = $order_goods['goods_sn'];
											$return_goods['goods_attr'] = $attr_val;
											$return_goods['attr_id'] = $return_attr_id;
											$return_goods['refound'] = $order_goods['goods_price'];
											$return_goods['return_type'] = $return_type;
											$return_goods['return_number'] = $return_number;

											if ($return_type == 1) {
												$return_goods['out_attr'] = '';
											}
											else if ($return_type == 2) {
												$return_goods['out_attr'] = $attr_val;
												$return_goods['return_attr_id'] = $return_attr_id;
											}
											else {
												$return_goods['out_attr'] = '';
											}

											$query = $db->autoExecute($ecs->table('wholesale_return_goods'), $return_goods, 'INSERT', '', 'SILENT');
											$sql = 'select count(*) from' . $ecs->table('wholesale_return_images') . (' where rec_id = \'' . $rec_id . '\' and user_id = \'') . $_SESSION['user_id'] . '\'';
											$images_count = $db->getOne($sql);

											if (0 < $images_count) {
												$images['rg_id'] = $order_goods['goods_id'];
												$db->autoExecute($ecs->table('wholesale_return_images'), $images, 'UPDATE', 'rec_id = \'' . $rec_id . '\' and user_id = \'' . $_SESSION['user_id'] . '\'');
											}

											$order_return_extend = array('ret_id' => $ret_id, 'return_number' => $return_number);
											$db->autoExecute($ecs->table('wholesale_order_return_extend'), $order_return_extend, 'INSERT', '', 'SILENT');
											$address_detail = get_user_region_address($order_goods['order_id'], $order_return['address']);
											$order_return['address_detail'] = $address_detail;
											$order_return['apply_time'] = local_date('Y-m-d H:i:s', $order_return['apply_time']);
										}
										else {
											show_message($_LANG['Apply_abnormal'], '', '', 'info', true);
										}
									}

									show_message($_LANG['Apply_Success_Prompt'], $_LANG['See_Returnlist'], 'user.php?act=wholesale_return_list', 'info', true, $order_return);
								}
							}
							else if ($action == 'wholesale_return_list') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								include_once ROOT_PATH . 'includes/lib_order.php';
								$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
								$size = 10;
								$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('wholesale_order_return') . ' WHERE user_id =' . $_SESSION['user_id']);
								$pager = get_pager('user.php', array('act' => $action), $record_count, $page, $size);
								$return_list = wholesale_return_order($size, $pager['start']);
								$smarty->assign('orders', $return_list);
								$smarty->assign('pager', $pager);
								$smarty->display('user_transaction.dwt');
							}
							else if ($action == 'wholesale_cancel_return') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								include_once ROOT_PATH . 'includes/lib_order.php';
								$ret_id = isset($_GET['ret_id']) ? intval($_GET['ret_id']) : 0;

								if (wholesale_cancel_return($ret_id, $user_id)) {
									ecs_header("Location: user.php?act=wholesale_return_list\n");
									exit();
								}
								else {
									$err->show($_LANG['return_list_lnk'], 'user.php?act=wholesale_return_list');
								}
							}
							else if ($action == 'wholesale_return_delivery') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								include_once ROOT_PATH . 'includes/lib_order.php';
								$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

								if (wholesale_affirm_return_received($order_id, $user_id)) {
									ecs_header("Location: user.php?act=wholesale_return_list\n");
									exit();
								}
								else {
									$err->show($_LANG['return_list_lnk'], 'user.php?act=wholesale_return_list');
								}
							}
							else if ($action == 'wholesale_activation_return_order') {
								include_once 'includes/cls_json.php';
								$json = new JSON();
								$res = array('err_msg' => '', 'result' => '', 'error' => 0);
								$ret_id = isset($_REQUEST['ret_id']) ? intval($_REQUEST['ret_id']) : 0;
								$activation_number_type = 0 < intval($_CFG['activation_number_type']) ? intval($_CFG['activation_number_type']) : 2;
								$sql = 'SELECT activation_number FROM' . $ecs->table('wholesale_order_return') . (' WHERE ret_id = \'' . $ret_id . '\' LIMIT 1');
								$activation_number = $db->getOne($sql);

								if ($activation_number < $activation_number_type) {
									$sql = 'UPDATE' . $ecs->table('wholesale_order_return') . (' SET activation_number = activation_number+1,return_status=0 WHERE ret_id = \'' . $ret_id . '\'');
									$db->query($sql);
								}
								else {
									$res['error'] = 1;
									$res['err_msg'] = sprintf($_LANG['activation_number_msg'], $activation_number_type);
								}

								exit($json->encode($res));
							}
							else if ($action == 'wholesale_return_detail') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								include_once ROOT_PATH . 'includes/lib_order.php';
								include_once ROOT_PATH . 'includes/lib_suppliers.php';
								$ret_id = isset($_GET['ret_id']) ? intval($_GET['ret_id']) : 0;
								$order = get_wholesale_return_detail($ret_id);

								if ($order === false) {
									$err->show($_LANG['back_home_lnk'], './');
									exit();
								}

								$region = array($order['country'], $order['province'], $order['city'], $order['district']);
								$shipping_list = available_shipping_list($region, $order['ru_id']);

								foreach ($shipping_list as $key => $val) {
									$shipping_cfg = unserialize_config($val['configure']);
									$shipping_fee = $shipping_count == 0 && $cart_weight_price['free_shipping'] == 1 ? 0 : shipping_fee($val['shipping_code'], unserialize($val['configure']), $cart_weight_price['weight'], $cart_weight_price['amount'], $cart_weight_price['number']);
									$shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);
									$shipping_list[$key]['shipping_fee'] = $shipping_fee;
									$shipping_list[$key]['free_money'] = price_format($shipping_cfg['free_money'], false);
									$shipping_list[$key]['insure_formated'] = strpos($val['insure'], '%') === false ? price_format($val['insure'], false) : $val['insure'];

									if ($val['shipping_id'] == $order['shipping_id']) {
										$insure_disabled = $val['insure'] == 0;
										$cod_disabled = $val['support_cod'] == 0;
									}
								}

								if ($order['return_type'] == 0) {
									if ($order['return_status1'] == 4) {
										$order['refound_status1'] = FF_MAINTENANCE;
									}
									else {
										$order['refound_status1'] = FF_NOMAINTENANCE;
									}
								}
								else if ($order['return_type'] == 1) {
									if ($order['refound_status1'] == 1) {
										$order['refound_status1'] = FF_REFOUND;
									}
									else {
										$order['refound_status1'] = FF_NOREFOUND;
									}
								}
								else if ($order['return_type'] == 2) {
									if ($order['return_status1'] == 4) {
										$order['refound_status1'] = FF_EXCHANGE;
									}
									else {
										$order['refound_status1'] = FF_NOEXCHANGE;
									}
								}

								$getImage = array();
								$smarty->assign('shipping_list', $shipping_list);
								$smarty->assign('goods', $order);
								$smarty->display('user_transaction.dwt');
							}
							else if ($action == 'wholesale_edit_express') {
								$ret_id = empty($_REQUEST['ret_id']) ? '' : intval($_REQUEST['ret_id']);
								$order_id = empty($_REQUEST['order_id']) ? '' : intval($_REQUEST['order_id']);
								$back_shipping_name = empty($_REQUEST['express_name']) ? '' : intval($_REQUEST['express_name']);
								$back_other_shipping = empty($_REQUEST['other_express']) ? '' : $_REQUEST['other_express'];
								$back_invoice_no = empty($_REQUEST['express_sn']) ? '' : $_REQUEST['express_sn'];

								if ($ret_id) {
									$db->query('UPDATE ' . $ecs->table('wholesale_order_return') . ('SET back_shipping_name = \'' . $back_shipping_name . '\' , back_other_shipping= \'' . $back_other_shipping . '\' , back_invoice_no=\'' . $back_invoice_no . '\' WHERE ret_id = \'' . $ret_id . '\''));
								}

								show_message($_LANG['edit_shipping_success'], $_LANG['return_info'], 'user.php?act=wholesale_return_detail&order_id=' . $order_id . '&ret_id=' . $ret_id);
							}
							else if ($action == 'wholesale_goods_order') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								include_once ROOT_PATH . 'includes/lib_order.php';
								include_once ROOT_PATH . 'includes/lib_suppliers.php';

								if (isset($_REQUEST['order_id'])) {
									$order_id = intval($_REQUEST['order_id']);
								}
								else {
									exit('invalid parameter');
								}

								$order = wholesale_order_info($order_id);
								$goods_list = wholesale_order_goods($order_id);

								foreach ($goods_list as $key => $value) {
									if ($value['extension_code'] != 'package_buy') {
										$price[] = $value['subtotal'];
										$goods_list[$key]['market_price'] = price_format($value['market_price'], false);
										$goods_list[$key]['goods_price'] = price_format($value['goods_price'], false);
										$goods_list[$key]['subtotal'] = price_format($value['subtotal'], false);
										$goods_list[$key]['is_refound'] = get_is_refound($value['rec_id'], 'wholesale_order_return');
										$goods_list[$key]['goods_attr'] = str_replace(' ', '&nbsp;&nbsp;&nbsp;&nbsp;', $value['goods_attr']);
										$goods_info = get_wholesale_goods_info($value['goods_id'], 0, 0, array('goods_cause'));
										$goods_list[$key]['goods_cause'] = get_goods_cause($goods_info['goods_cause'], $order['chargeoff_status'], $order['is_settlement']);
									}
									else {
										unset($goods_list[$key]);
										$smarty->assign('package_buy', true);
									}
								}

								$formated_goods_amount = price_format(array_sum($price), false);
								$smarty->assign('formated_goods_amount', $formated_goods_amount);
								$smarty->assign('order_id', $order_id);
								$smarty->assign('goods_list', $goods_list);
								$smarty->display('user_transaction.dwt');
							}
							else if ($action == 'wholesale_apply_return') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								include_once ROOT_PATH . 'includes/lib_order.php';
								include_once ROOT_PATH . 'includes/lib_suppliers.php';

								if (isset($_REQUEST['rec_id'])) {
									$recr_id = intval($_REQUEST['rec_id']);
								}
								else {
									exit('invalid parameter');
								}

								$order_id = intval($_REQUEST['order_id']);
								$order = wholesale_order_info($order_id);
								$sql = ' SELECT order_id FROM ' . $GLOBALS['ecs']->table('wholesale_order_info') . (' WHERE order_id = \'' . $order_id . '\' AND shipping_status > 0 ');
								$return_allowable = $GLOBALS['db']->getOne($sql, true);
								$smarty->assign('return_allowable', $return_allowable);
								$goods_info = wholesale_rec_goods($recr_id);
								$parent_cause = get_parent_cause();
								$consignee = get_consignee($_SESSION['user_id']);
								$smarty->assign('consignee', $consignee);
								$smarty->assign('show_goods_thumb', $GLOBALS['_CFG']['show_goods_in_cart']);
								$smarty->assign('show_goods_attribute', $GLOBALS['_CFG']['show_attr_in_cart']);
								$smarty->assign('goods', $goods_info);
								$smarty->assign('goods_return', $goods_info);
								$smarty->assign('order_id', $order_id);
								$smarty->assign('cause_list', $parent_cause);
								$smarty->assign('order_sn', $order['order_sn']);
								$smarty->assign('order', $order);
								$country_list = get_regions_log(0, 0);
								$province_list = get_regions_log(1, $consignee['country']);
								$city_list = get_regions_log(2, $consignee['province']);
								$district_list = get_regions_log(3, $consignee['city']);
								$street_list = get_regions_log(4, $consignee['district']);
								$cause_list = array('0', '1', '2', '3');
								$goods_info = get_wholesale_goods_info($goods_info['goods_id'], 0, 0, array('goods_cause'));
								$goods_cause = get_goods_cause($goods_info['goods_cause'], $order['chargeoff_status'], $order['is_settlement']);
								$smarty->assign('goods_cause', $goods_cause);
								$sql = 'select img_file from ' . $ecs->table('wholesale_return_images') . (' where user_id = \'' . $user_id . '\' and rec_id = \'' . $recr_id . '\' order by id desc');
								$img_list = $db->getAll($sql);
								$smarty->assign('img_list', $img_list);
								$sn = 0;
								$smarty->assign('country_list', $country_list);
								$smarty->assign('province_list', $province_list);
								$smarty->assign('city_list', $city_list);
								$smarty->assign('district_list', $district_list);
								$smarty->assign('street_list', $street_list);
								$smarty->assign('sn', $sn);
								$smarty->assign('sessid', SESS_ID);
								$smarty->assign('return_pictures', $GLOBALS['_CFG']['return_pictures']);
								$smarty->display('user_transaction.dwt');
							}
							else if ($action == 'wholesale_submit_return') {
								include_once ROOT_PATH . 'includes/lib_transaction.php';
								include_once ROOT_PATH . 'includes/lib_order.php';
								$rec_id = empty($_REQUEST['rec_id']) ? 0 : intval($_REQUEST['rec_id']);
								$last_option = !isset($_REQUEST['last_option']) ? $_REQUEST['parent_id'] : $_REQUEST['last_option'];
								$return_remark = !isset($_REQUEST['return_remark']) ? '' : addslashes(trim($_REQUEST['return_remark']));
								$return_brief = !isset($_REQUEST['return_brief']) ? '' : addslashes(trim($_REQUEST['return_brief']));
								$chargeoff_status = !isset($_REQUEST['chargeoff_status']) && empty($_REQUEST['chargeoff_status']) ? 0 : intval($_REQUEST['chargeoff_status']);

								if (0 < $rec_id) {
									$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('wholesale_order_return') . ' WHERE rec_id = ' . $rec_id;
									$num = $db->getOne($sql);

									if (0 < $num) {
										show_message($_LANG['Repeated_submission'], '', '', 'info', true);
									}
								}
								else {
									show_message($_LANG['Return_abnormal'], '', '', 'info', true);
								}

								$sql = 'select g.goods_name, g.goods_sn,g.brand_id, og.order_id, og.goods_id, og.product_id, og.goods_attr, ' . ' og.is_real, og.goods_attr_id, og.goods_price, og.goods_price, og.goods_number ' . 'from ' . $ecs->table('wholesale_order_goods') . ' as og ' . ' left join ' . $ecs->table('wholesale') . ' as g on og.goods_id = g.goods_id ' . (' where og.rec_id = \'' . $rec_id . '\'');
								$order_goods = $db->getRow($sql);
								$sql = ' SELECT order_sn, country,province,city ,district FROM ' . $GLOBALS['ecs']->table('wholesale_order_info') . ' WHERE order_id =' . $order_goods['order_id'];
								$res = $GLOBALS['db']->getRow($sql);
								$maintain_number = empty($_REQUEST['maintain_number']) ? 0 : intval($_REQUEST['maintain_number']);
								$return_num = empty($_REQUEST['return_num']) ? 0 : intval($_REQUEST['return_num']);
								$back_number = empty($_REQUEST['attr_num']) ? 0 : intval($_REQUEST['attr_num']);
								$goods_number = empty($_REQUEST['return_g_number']) ? 0 : intval($_REQUEST['return_g_number']);
								$return_type = intval($_REQUEST['return_type']);
								$maintain = 0;
								$return_status = 0;

								if ($return_type == 1) {
									$back = 1;
									$exchange = 0;
									$return_number = $return_num;
								}
								else if ($return_type == 2) {
									$back = 0;
									$exchange = 2;
									$return_number = $back_number;
								}
								else if ($return_type == 3) {
									$back = 0;
									$exchange = 0;
									$return_number = $goods_number;
									$return_status = -1;
								}
								else {
									$back = 0;
									$exchange = 0;
									$return_number = $maintain_number;
								}

								$attr_val = isset($_REQUEST['attr_val']) ? $_REQUEST['attr_val'] : array();
								$return_attr_id = !empty($attr_val) ? implode(',', $attr_val) : '';
								$attr_val = get_wholesale_goods_attr_info_new($attr_val, 'pice', $order_goods['warehouse_id'], $order_goods['area_id']);
								$order_return = array('rec_id' => $rec_id, 'goods_id' => $order_goods['goods_id'], 'order_id' => $order_goods['order_id'], 'order_sn' => $order_goods['goods_sn'], 'chargeoff_status' => $chargeoff_status, 'return_type' => $return_type, 'maintain' => $maintain, 'back' => $back, 'exchange' => $exchange, 'user_id' => $_SESSION['user_id'], 'goods_attr' => $order_goods['goods_attr'], 'attr_val' => $attr_val, 'return_brief' => $return_brief, 'remark' => $return_remark, 'credentials' => !isset($_REQUEST['credentials']) ? 0 : intval($_REQUEST['credentials']), 'country' => empty($_REQUEST['country']) ? 0 : intval($_REQUEST['country']), 'province' => empty($_REQUEST['province']) ? 0 : intval($_REQUEST['province']), 'city' => empty($_REQUEST['city']) ? 0 : intval($_REQUEST['city']), 'district' => empty($_REQUEST['district']) ? 0 : intval($_REQUEST['district']), 'street' => empty($_REQUEST['street']) ? 0 : intval($_REQUEST['street']), 'cause_id' => $last_option, 'apply_time' => gmtime(), 'actual_return' => '', 'address' => empty($_REQUEST['return_address']) ? '' : addslashes(trim($_REQUEST['return_address'])), 'zipcode' => empty($_REQUEST['code']) ? '' : intval($_REQUEST['code']), 'addressee' => empty($_REQUEST['addressee']) ? '' : addslashes(trim($_REQUEST['addressee'])), 'phone' => empty($_REQUEST['mobile']) ? '' : addslashes(trim($_REQUEST['mobile'])), 'return_status' => $return_status);

								if (in_array($return_type, array(1, 3))) {
									$return_info = get_wholesale_return_refound($order_return['order_id'], $order_return['rec_id'], $return_number);
									$order_return['should_return'] = $return_info['return_price'];
									$order_return['return_shipping_fee'] = $return_info['return_shipping_fee'];
								}
								else {
									$order_return['should_return'] = 0;
									$order_return['return_shipping_fee'] = 0;
								}

								$error_no = 0;

								do {
									$order_return['return_sn'] = get_order_sn();
									$query = $db->autoExecute($ecs->table('wholesale_order_return'), $order_return, 'INSERT', '', 'SILENT');
									$error_no = $GLOBALS['db']->errno();
									if (0 < $error_no && $error_no != 1062) {
										exit($GLOBALS['db']->errorMsg());
									}
								} while ($error_no == 1062);

								if ($query) {
									$ret_id = $db->insert_id();
									return_action($ret_id, $_LANG['Apply_refund'], '', $order_return['remark'], $_LANG['buyer']);
									$return_goods['rec_id'] = $order_return['rec_id'];
									$return_goods['ret_id'] = $ret_id;
									$return_goods['goods_id'] = $order_goods['goods_id'];
									$return_goods['goods_name'] = $order_goods['goods_name'];
									$return_goods['brand_name'] = $order_goods['brand_name'];
									$return_goods['product_id'] = $order_goods['product_id'];
									$return_goods['goods_sn'] = $order_goods['goods_sn'];
									$return_goods['is_real'] = $order_goods['is_real'];
									$return_goods['goods_attr'] = $attr_val;
									$return_goods['attr_id'] = $return_attr_id;
									$return_goods['refound'] = $order_goods['goods_price'];
									$return_goods['return_type'] = $return_type;
									$return_goods['return_number'] = $return_number;

									if ($return_type == 1) {
										$return_goods['out_attr'] = '';
									}
									else if ($return_type == 2) {
										$return_goods['out_attr'] = $attr_val;
										$return_goods['return_attr_id'] = $return_attr_id;
									}
									else {
										$return_goods['out_attr'] = '';
									}

									$query = $db->autoExecute($ecs->table('wholesale_return_goods'), $return_goods, 'INSERT', '', 'SILENT');
									$sql = 'select count(*) from' . $ecs->table('wholesale_return_images') . (' where rec_id = \'' . $rec_id . '\' and user_id = \'') . $_SESSION['user_id'] . '\'';
									$images_count = $db->getOne($sql);

									if (0 < $images_count) {
										$images['rg_id'] = $order_goods['goods_id'];
										$db->autoExecute($ecs->table('wholesale_return_images'), $images, 'UPDATE', 'rec_id = \'' . $rec_id . '\' and user_id = \'' . $_SESSION['user_id'] . '\'');
									}

									$order_return_extend = array('ret_id' => $ret_id, 'return_number' => $return_number);
									$db->autoExecute($ecs->table('wholesale_order_return_extend'), $order_return_extend, 'INSERT', '', 'SILENT');
									$address_detail = get_user_region_address($order_goods['order_id'], $order_return['address']);
									$order_return['address_detail'] = $address_detail;
									$order_return['apply_time'] = local_date('Y-m-d H:i:s', $order_return['apply_time']);
									show_message($_LANG['Apply_Success_Prompt'], $_LANG['See_Returnlist'], 'user.php?act=wholesale_return_list', 'info', true, $order_return);
								}
								else {
									show_message($_LANG['Apply_abnormal'], '', '', 'info', true);
								}
							}
							else if ($action == 'wholesale_order_delete_return') {
								include_once 'includes/cls_json.php';
								$_POST['order'] = strip_tags(urldecode($_POST['order']));
								$_POST['order'] = json_str_iconv($_POST['order']);
								$result = array('error' => 0, 'content' => '', 'order_id' => '', 'pager' => '');
								$json = new JSON();
								$order = $json->decode($_POST['order']);
								$order_id = $order->order_id;
								$result['order_id'] = $order_id;
								$return_list = wholesale_return_order();
								$sql = 'DELETE FROM' . $ecs->table('wholesale_order_return') . (' WHERE user_id = \'' . $user_id . '\' AND ret_id = ') . $result['order_id'];
								$db->query($sql);

								if ($db->query($sql)) {
									$return_list = wholesale_return_order();
									$smarty->assign('orders', $return_list);
									$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
									$record_count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('wholesale_order_return') . (' WHERE user_id = \'' . $user_id . '\''));
									$action = 'wholesale_return_list';
									$result['pager'] = get_pager('user.php', array('act' => $action), $record_count, $page);
									$result['content'] = $smarty->fetch('library/user_wholesale_return_order_list.lbi');
									exit($json->encode($result));
								}
							}
						}
					}
				}
			}
		}
	}
}

?>
