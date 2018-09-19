<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function edit_profile($profile)
{
	global $_CFG;

	if (empty($profile['user_id'])) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['not_login']);
		return false;
	}

	$cfg = array();
	$cfg['user_id'] = $profile['user_id'];
	$cfg['username'] = $GLOBALS['db']->getOne('SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id=\'' . $profile['user_id'] . '\'');

	if (isset($profile['sex'])) {
		$cfg['gender'] = intval($profile['sex']);
	}

	if (!empty($profile['email'])) {
		if (!is_email($profile['email'])) {
			$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_invalid'], $profile['email']));
			return false;
		}

		$cfg['email'] = $profile['email'];
	}

	if (!empty($profile['mobile_phone'])) {
		$mobile = $GLOBALS['db']->getOne('SELECT mobile_phone FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id=\'' . $profile['user_id'] . '\'');
		if ($mobile != $profile['mobile_phone'] && $_CFG['sms_signin'] == 1) {
			if (!empty($profile['mobile_code'])) {
				if ($profile['mobile_phone'] != $_SESSION['sms_mobile'] || $profile['mobile_code'] != $_SESSION['sms_mobile_code']) {
					$GLOBALS['err']->add($GLOBALS['_LANG']['phone_check_code']);
					return false;
				}
			}
			else {
				$profile['mobile_phone'] = $mobile;
			}
		}

		$cfg['mobile_phone'] = $profile['mobile_phone'];
	}

	if (!empty($profile['birthday'])) {
		$cfg['bday'] = $profile['birthday'];
	}

	if (!$GLOBALS['user']->edit_user($cfg)) {
		if ($GLOBALS['user']->error == ERR_EMAIL_EXISTS) {
			$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_exist'], $profile['email']));
		}
		else if ($GLOBALS['user']->error == ERR_PHONE_EXISTS) {
			$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['phone_exist'], $profile['mobile_phone']));
		}
		else {
			$GLOBALS['err']->add('DB ERROR!');
		}

		return false;
	}

	$other_key_array = array('msn', 'qq', 'office_phone', 'home_phone');

	foreach ($profile['other'] as $key => $val) {
		if (!in_array($key, $other_key_array)) {
			unset($profile['other'][$key]);
		}
		else {
			$profile['other'][$key] = htmlspecialchars(trim($val));
		}
	}

	if (!empty($profile['other'])) {
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('users'), $profile['other'], 'UPDATE', 'user_id = \'' . $profile['user_id'] . '\'');
	}

	return true;
}

function get_profile($user_id)
{
	global $user;
	$info = array();
	$infos = array();
	$sql = 'SELECT user_name, birthday, sex, question, answer, rank_points, pay_points,user_money, user_rank, user_picture,' . ' msn, qq, office_phone, home_phone, mobile_phone, passwd_question, passwd_answer,is_validated, nick_name ' . 'FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $user_id . '\'');
	$infos = $GLOBALS['db']->getRow($sql);
	$infos['user_name'] = addslashes($infos['user_name']);
	$row = $user->get_profile_by_name($infos['user_name']);
	$_SESSION['email'] = $row['email'];

	if (0 < $infos['user_rank']) {
		$sql = 'SELECT rank_id, rank_name, discount FROM ' . $GLOBALS['ecs']->table('user_rank') . (' WHERE rank_id = \'' . $infos['user_rank'] . '\'');
	}
	else {
		$sql = 'SELECT rank_id, rank_name, discount, min_points' . ' FROM ' . $GLOBALS['ecs']->table('user_rank') . ' WHERE min_points<= ' . intval($infos['rank_points']) . ' ORDER BY min_points DESC';
	}

	if ($row = $GLOBALS['db']->getRow($sql)) {
		$info['rank_name'] = $row['rank_name'];
	}
	else {
		$info['rank_name'] = $GLOBALS['_LANG']['undifine_rank'];
	}

	$cur_date = date('Y-m-d H:i:s');
	$bonus = array();
	$sql = 'SELECT type_name, type_money ' . 'FROM ' . $GLOBALS['ecs']->table('bonus_type') . ' AS t1, ' . $GLOBALS['ecs']->table('user_bonus') . ' AS t2 ' . ('WHERE t1.type_id = t2.bonus_type_id AND t2.user_id = \'' . $user_id . '\' AND t1.use_start_date <= \'' . $cur_date . '\' ') . ('AND t1.use_end_date > \'' . $cur_date . '\' AND t2.order_id = 0');
	$bonus = $GLOBALS['db']->getAll($sql);

	if ($bonus) {
		$i = 0;

		for ($count = count($bonus); $i < $count; $i++) {
			$bonus[$i]['type_money'] = price_format($bonus[$i]['type_money'], false);
		}
	}

	$info['discount'] = $_SESSION['discount'] * 100 . '%';
	$info['email'] = $_SESSION['email'];
	$info['user_name'] = $infos['user_name'];
	$info['rank_points'] = isset($infos['rank_points']) ? $infos['rank_points'] : '';
	$info['pay_points'] = isset($infos['pay_points']) ? $infos['pay_points'] : 0;
	$info['user_money'] = isset($infos['user_money']) ? $infos['user_money'] : 0;
	$info['sex'] = isset($infos['sex']) ? $infos['sex'] : 0;
	$info['birthday'] = isset($infos['birthday']) ? $infos['birthday'] : '';
	$info['question'] = isset($infos['question']) ? htmlspecialchars($infos['question']) : '';
	$info['user_money'] = price_format($info['user_money'], false);
	$info['pay_points'] = $info['pay_points'] . $GLOBALS['_CFG']['integral_name'];
	$info['bonus'] = $bonus;
	$info['qq'] = $infos['qq'];
	$info['msn'] = $infos['msn'];
	$info['office_phone'] = $infos['office_phone'];
	$info['home_phone'] = $infos['home_phone'];
	$info['mobile_phone'] = $infos['mobile_phone'];
	$info['passwd_question'] = $infos['passwd_question'];
	$info['passwd_answer'] = $infos['passwd_answer'];
	$info['is_validate'] = $infos['is_validated'];
	$info['nick_name'] = !empty($infos['nick_name']) ? $infos['nick_name'] : $infos['username'];
	if (strpos($infos['user_picture'], 'http://') === false && strpos($infos['user_picture'], 'https://') === false) {
		if ($GLOBALS['_CFG']['open_oss'] == 1 && $infos['user_picture']) {
			$bucket_info = get_bucket_info();
			$info['user_picture'] = $bucket_info['endpoint'] . $infos['user_picture'];
		}
		else {
			$info['user_picture'] = $infos['user_picture'];
		}
	}
	else {
		$info['user_picture'] = $infos['user_picture'];
	}

	return $info;
}

function get_consignee_list($user_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_address') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 5');
	return $GLOBALS['db']->getAll($sql);
}

function get_new_consignee_list($user_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_address') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 10');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['address_id'] = $row['address_id'];
		$arr[$key]['consignee'] = $row['consignee'];
		$arr[$key]['region'] = user_consignee_region($row['address_id']);
		$arr[$key]['address'] = $row['address'];
		$arr[$key]['email'] = $row['email'];
		$arr[$key]['mobile'] = $row['mobile'];
		$arr[$key]['tel'] = $row['tel'];
		$arr[$key]['zipcode'] = $row['zipcode'];
		$arr[$key]['sign_building'] = $row['sign_building'];
		$arr[$key]['best_time'] = $row['best_time'];
		$arr[$key]['province_id'] = $row['province'];
		$arr[$key]['city_id'] = $row['city'];
		$arr[$key]['district_id'] = $row['district'];
		$city = get_region_info($row['city']);
		$arr[$key]['city_name'] = $city['region_name'];
		$district = get_region_info($row['district']);
		$arr[$key]['district_name'] = $district['region_name'];
		$street = get_region_info($row['street']);
		$arr[$key]['street_name'] = $street['region_name'];
	}

	return $arr;
}

function get_vat_consignee_list($vat_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('users_vat_invoices_info') . (' WHERE id = \'' . $vat_id . '\'');
	$res = $GLOBALS['db']->getRow($sql);
	$arr = array();
	$arr['region'] = user_vat_consignee_region($res['id']);
	return $arr;
}

function user_vat_consignee_region($id)
{
	$sql = 'SELECT concat(IFNULL(p.region_name, \'\'), ' . 'IFNULL(t.region_name, \'\'), IFNULL(d.region_name, \'\') )' . 'FROM ' . $GLOBALS['ecs']->table('users_vat_invoices_info') . ' AS u ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS p ON u.province = p.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS t ON u.city = t.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS d ON u.district = d.region_id ' . ('WHERE u.id = \'' . $id . '\'');
	$address = $GLOBALS['db']->getOne($sql);
	return $address;
}

function user_consignee_region($address_id)
{
	$sql = 'SELECT concat(IFNULL(p.region_name, \'\'), ' . '\'  \', IFNULL(t.region_name, \'\'), \'  \', IFNULL(d.region_name, \'\'), \'  \', IFNULL(s.region_name, \'\')) AS region ' . 'FROM ' . $GLOBALS['ecs']->table('user_address') . ' AS u ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS p ON u.province = p.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS t ON u.city = t.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS d ON u.district = d.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS s ON u.street = s.region_id ' . ('WHERE u.address_id = \'' . $address_id . '\'');
	$address = $GLOBALS['db']->getOne($sql);
	return $address;
}

function get_user_address_info($address_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_address') . (' WHERE address_id = \'' . $address_id . '\'');
	$arr = $GLOBALS['db']->getRow($sql);
	return $arr;
}

function add_bonus($user_id, $bouns_sn, $password)
{
	$sql = 'SELECT bonus_id, bonus_sn, user_id, bonus_type_id FROM ' . $GLOBALS['ecs']->table('user_bonus') . (' WHERE bonus_sn = \'' . $bouns_sn . '\' AND bonus_password = \'' . $password . '\'');
	$row = $GLOBALS['db']->getRow($sql);

	if ($row) {
		if ($row['user_id'] == 0) {
			$sql = 'SELECT send_end_date, use_end_date ' . ' FROM ' . $GLOBALS['ecs']->table('bonus_type') . ' WHERE type_id = \'' . $row['bonus_type_id'] . '\' AND review_status = 3';
			$bonus_time = $GLOBALS['db']->getRow($sql);
			$now = gmtime();

			if ($bonus_time['use_end_date'] < $now) {
				$GLOBALS['err']->add($GLOBALS['_LANG']['bonus_use_expire']);
				return false;
			}

			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_bonus') . (' SET user_id = \'' . $user_id . '\', bind_time = \'') . gmtime() . '\' ' . ('WHERE bonus_id = \'' . $row['bonus_id'] . '\'');
			$result = $GLOBALS['db']->query($sql);

			if ($result) {
				return true;
			}
			else {
				return $GLOBALS['db']->errorMsg();
			}
		}
		else {
			if ($row['user_id'] == $user_id) {
				$GLOBALS['err']->add($GLOBALS['_LANG']['bonus_is_used']);
			}
			else {
				$GLOBALS['err']->add($GLOBALS['_LANG']['bonus_is_used_by_other']);
			}

			return false;
		}
	}
	else {
		$GLOBALS['err']->add($GLOBALS['_LANG']['bonus_not_exist']);
		return false;
	}
}

function add_value_card($user_id, $value_card, $password)
{
	$sql = 'SELECT vid, tid, value_card_sn, user_id, end_time FROM ' . $GLOBALS['ecs']->table('value_card') . (' WHERE value_card_sn = \'' . $value_card . '\' AND value_card_password = \'' . $password . '\'');
	$row = $GLOBALS['db']->getRow($sql);

	if ($row) {
		if ($row['user_id'] == 0) {
			$sql = 'SELECT vc_indate, vc_limit ' . ' FROM ' . $GLOBALS['ecs']->table('value_card_type') . ' WHERE id = \'' . $row['tid'] . '\'';
			$vc_type = $GLOBALS['db']->getRow($sql);

			if ($row['end_time']) {
				if ($row['end_time'] < gmtime()) {
					$GLOBALS['err']->add($GLOBALS['_LANG']['vc_use_expire']);
					return 1;
				}
			}
			else {
				$end_time = ' , end_time = \'' . local_strtotime('+' . $vc_type['vc_indate'] . ' months ') . '\' ';
			}

			$sql = ' SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('value_card') . (' WHERE user_id = \'' . $user_id . '\' AND tid = \'' . $row['tid'] . '\' ');
			$limit = $GLOBALS['db']->getOne($sql);

			if ($vc_type['vc_limit'] <= $limit) {
				$GLOBALS['err']->add($GLOBALS['_LANG']['vc_limit_expire']);
				return 5;
			}

			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('value_card') . (' SET user_id = \'' . $user_id . '\', bind_time = \'') . gmtime() . '\'' . $end_time . (' WHERE vid = \'' . $row['vid'] . '\' ');
			$result = $GLOBALS['db']->query($sql);

			if ($result) {
				return 0;
			}
			else {
				return $GLOBALS['db']->errorMsg();
			}
		}
		else if ($row['user_id'] == $user_id) {
			$GLOBALS['err']->add($GLOBALS['_LANG']['vc_is_used']);
			return 2;
		}
		else {
			$GLOBALS['err']->add($GLOBALS['_LANG']['vc_is_used_by_other']);
			return 3;
		}
	}
	else {
		return 4;
	}
}

function use_pay_card($user_id, $vid, $pay_card, $password)
{
	$sql = 'SELECT p.id, p.c_id, p.card_number, p.user_id, pt.type_money FROM ' . $GLOBALS['ecs']->table('pay_card') . ' AS p LEFT JOIN ' . $GLOBALS['ecs']->table('pay_card_type') . ' AS pt ON pt.type_id = p.c_id ' . (' WHERE p.card_number = \'' . $pay_card . '\' AND p.card_psd = \'' . $password . '\'');
	$row = $GLOBALS['db']->getRow($sql);
	$sql = ' SELECT t.is_rec FROM ' . $GLOBALS['ecs']->table('value_card_type') . ' AS t ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('value_card') . ' AS v ON v.tid = t.id ' . (' WHERE v.vid = \'' . $vid . '\'  ');
	$is_rec = $GLOBALS['db']->getOne($sql);

	if ($row) {
		if ($row['user_id'] == 0 && $is_rec) {
			$sql = 'SELECT use_end_date ' . ' FROM ' . $GLOBALS['ecs']->table('pay_card_type') . ' WHERE type_id = \'' . $row['c_id'] . '\'';
			$pc_type = $GLOBALS['db']->getRow($sql);
			$now = gmtime();

			if ($pc_type['use_end_date'] < $now) {
				$GLOBALS['err']->add($GLOBALS['_LANG']['vc_use_expire']);
				return false;
			}

			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('pay_card') . (' SET user_id = \'' . $user_id . '\', used_time = \'') . gmtime() . '\' ' . ('WHERE id = \'' . $row['id'] . '\'');
			$result = $GLOBALS['db']->query($sql);

			if ($result) {
				$sql = ' UPDATE ' . $GLOBALS['ecs']->table('value_card') . ' SET card_money = card_money + ' . $row['type_money'] . (' WHERE vid = \'' . $vid . '\' ');
				$res = $GLOBALS['db']->query($sql);

				if ($res) {
					$sql = ' INSERT INTO ' . $GLOBALS['ecs']->table('value_card_record') . (' (vc_id, add_val, record_time) VALUE (\'' . $vid . '\', \'' . $row['type_money'] . '\', \'') . gmtime() . '\' ) ';
					$GLOBALS['db']->query($sql);
					return true;
				}
				else {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('pay_card') . ' SET user_id = 0, used_time = \'\' ' . ('WHERE id = \'' . $row['id'] . '\'');
					$GLOBALS['db']->query($sql);
					return $GLOBALS['db']->errorMsg();
				}
			}
			else {
				return $GLOBALS['db']->errorMsg();
			}
		}
		else {
			$GLOBALS['err']->add($GLOBALS['_LANG']['pc_is_used']);
			return false;
		}
	}
	else {
		return false;
	}
}

function get_all_auction($user_id, $type = '')
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('auction_log') . ' AS al ON ga.act_id = al.act_id ' . (' WHERE al.bid_user=\'' . $user_id . '\'') . $type;
	$auction_count = $GLOBALS['db']->getOne($sql);
	return $auction_count;
}

function get_auction_list($user_id, $record_count, $page, $where = '', $list = '', $pagesize = 10)
{
	require_once 'includes/cls_pager.php';
	$time = gmtime();

	if ($list) {
		$idTxt = $list->idTxt;
		$keyword = $list->keyword;
		$action = $list->action;
		$type = $list->type;
		$status_keyword = $list->status_keyword;
		$date_keyword = $list->date_keyword;
		$id = '"';
		$id .= $user_id . '=';
		$id .= 'idTxt@' . $idTxt . '|';
		$id .= 'keyword@' . $keyword . '|';
		$id .= 'action@' . $action . '|';
		$id .= 'type@' . $type . '|';

		if ($status_keyword) {
			$id .= 'status_keyword@' . $status_keyword . '|';
		}

		if ($date_keyword) {
			$id .= 'date_keyword@' . $date_keyword;
		}

		$substr = substr($id, -1);

		if ($substr == '|') {
			$id = substr($id, 0, -1);
		}

		$id .= '"';
	}
	else {
		$id = $user_id;
	}

	$config = array('header' => $GLOBALS['_LANG']['pager_2'], 'prev' => '<i><<</i>' . $GLOBALS['_LANG']['page_prev'], 'next' => '' . $GLOBALS['_LANG']['page_next'] . '<i>>></i>', 'first' => $GLOBALS['_LANG']['page_first'], 'last' => $GLOBALS['_LANG']['page_last']);
	$user_auction = new Pager($record_count, $pagesize, '', $id, 0, $page, 'user_auction_gotoPage', 1, 0, 0, $config);
	$limit = $user_auction->limit;
	$pager = $user_auction->fpage(array(0, 4, 5, 6, 9));
	$order_by = ' ORDER BY bid_time desc ';
	$list = array();
	$sql = 'SELECT ga.act_id, ga.act_name,g.goods_thumb,ga.goods_name,ga.start_time,ga.end_time,ga.is_finished, al.bid_time, al.bid_price FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('auction_log') . ' AS al ON ga.act_id = al.act_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = ga.goods_id ' . (' WHERE al.bid_user=\'' . $user_id . '\'') . $where . $order_by . $limit;
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$arr['status_no'] = auction_status($row);
		$arr['act_id'] = $row['act_id'];
		$arr['act_name'] = $row['act_name'];
		$arr['goods_thumb'] = $row['goods_thumb'];
		$arr['goods_name'] = $row['goods_name'];
		$arr['start_time'] = $row['start_time'];
		$arr['end_time'] = $row['end_time'];
		$arr['bid_time'] = local_date('Y-m-d H:i:s', $row['bid_time']);
		$arr['bid_price'] = price_format($row['bid_price']);
		$arr['status'] = $GLOBALS['_LANG']['auction_staues'][$arr['status_no']];
		$list[] = $arr;
	}

	$auction_list = array('auction_list' => $list, 'pager' => $pager, 'record_count' => $record_count);
	return $auction_list;
}

function get_all_snatch($user_id, $type = '')
{
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('snatch_log') . ' AS sl ON ga.act_id = sl.snatch_id ' . (' WHERE sl.user_id=\'' . $user_id . '\'') . $type;
	$snatch_count = $GLOBALS['db']->getOne($sql);
	return $snatch_count;
}

function get_snatch_list($user_id, $record_count, $page, $where = '', $list = '', $pagesize = 10)
{
	require_once 'includes/cls_pager.php';
	$time = gmtime();
	if ($list && is_object($list)) {
		$idTxt = $list->idTxt;
		$keyword = $list->keyword;
		$action = $list->action;
		$type = $list->type;
		$status_keyword = $list->status_keyword;
		$date_keyword = $list->date_keyword;
		$id = '"';
		$id .= $user_id . '=';
		$id .= 'idTxt@' . $idTxt . '|';
		$id .= 'keyword@' . $keyword . '|';
		$id .= 'action@' . $action . '|';
		$id .= 'type@' . $type . '|';

		if ($status_keyword) {
			$id .= 'status_keyword@' . $status_keyword . '|';
		}

		if ($date_keyword) {
			$id .= 'date_keyword@' . $date_keyword;
		}

		$substr = substr($id, -1);

		if ($substr == '|') {
			$id = substr($id, 0, -1);
		}

		$id .= '"';
	}
	else {
		$id = $user_id;
	}

	$config = array('header' => $GLOBALS['_LANG']['pager_2'], 'prev' => '<i><<</i>' . $GLOBALS['_LANG']['page_prev'], 'next' => '' . $GLOBALS['_LANG']['page_next'] . '<i>>></i>', 'first' => $GLOBALS['_LANG']['page_first'], 'last' => $GLOBALS['_LANG']['page_last']);
	$user_snatch = new Pager($record_count, $pagesize, '', $id, 0, $page, 'user_snatch_gotoPage', 1, 0, 0, $config);
	$limit = $user_snatch->limit;
	$pager = $user_snatch->fpage(array(0, 4, 5, 6, 9));
	$order_by = ' ORDER BY bid_time desc ';
	$list = array();
	$sql = 'SELECT ga.act_id, ga.act_name,g.goods_thumb,ga.goods_name,ga.start_time,ga.end_time,ga.is_finished, sl.bid_time, sl.bid_price FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('snatch_log') . ' AS sl ON ga.act_id = sl.snatch_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = ga.goods_id ' . (' WHERE sl.user_id=\'' . $user_id . '\'') . $where . $order_by . $limit;
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$arr['status_no'] = auction_status($row);
		$arr['act_id'] = $row['act_id'];
		$arr['act_name'] = $row['act_name'];
		$arr['goods_thumb'] = $row['goods_thumb'];
		$arr['goods_name'] = $row['goods_name'];
		$arr['start_time'] = $row['start_time'];
		$arr['end_time'] = $row['end_time'];
		$arr['bid_time'] = local_date('Y-m-d H:i:s', $row['bid_time']);
		$arr['bid_price'] = price_format($row['bid_price']);
		$arr['status'] = $GLOBALS['_LANG']['auction_staues'][$arr['status_no']];
		$list[] = $arr;
	}

	$auction_list = array('snatch_list' => $list, 'pager' => $pager, 'record_count' => $record_count);
	return $auction_list;
}

function get_user_orders($user_id, $record_count, $page, $is_delete = 0, $where = '', $order = '', $handle_type = 0, $pagesize = 10)
{
	require_once 'includes/cls_pager.php';
	$og_where = ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og WHERE og.order_id = oi.order_id) > 0 ';
	$action = '';
	if ($order && is_object($order)) {
		$idTxt = $order->idTxt;
		$keyword = $order->keyword;
		$action = $order->action;
		$type = $order->type;
		$status_keyword = $order->status_keyword;
		$date_keyword = $order->date_keyword;
		$id = '"';
		$id .= $user_id . '=';
		$id .= 'idTxt@' . $idTxt . '|';
		$id .= 'keyword@' . $keyword . '|';
		$id .= 'action@' . $action . '|';
		$id .= 'type@' . $type . '|';

		if ($status_keyword) {
			$id .= 'status_keyword@' . $status_keyword . '|';
		}

		if ($date_keyword) {
			$id .= 'date_keyword@' . $date_keyword;
		}

		$substr = substr($id, -1);

		if ($substr == '|') {
			$id = substr($id, 0, -1);
		}

		$id .= '"';

		if ($type == 'text') {
			if ($keyword == $GLOBALS['_LANG']['user_keyword']) {
				$keyword = '';
			}

			if (empty($keyword)) {
				$where .= $og_where;
			}
		}
	}
	else {
		$id = $user_id;
		$where .= $og_where;
	}

	$config = array('header' => $GLOBALS['_LANG']['pager_2'], 'prev' => '<i><<</i>' . $GLOBALS['_LANG']['page_prev'], 'next' => '' . $GLOBALS['_LANG']['page_next'] . '<i>>></i>', 'first' => $GLOBALS['_LANG']['page_first'], 'last' => $GLOBALS['_LANG']['page_last']);
	$act = '';
	if ($order == 'auction' || $action == 'auction' || $order == 'auction_order_recycle') {
		$user_order = new Pager($record_count, $pagesize, '', $id, 0, $page, 'user_auction_order_gotoPage', 1, 0, 0, $config);

		if ($order == 'auction') {
			$act = ' AND oi.extension_code = \'' . $order . '\'';
		}
		else {
			$ext = $order->action;
			$act = ' AND oi.extension_code = \'' . $ext . '\'';
		}
	}
	else {
		$user_order = new Pager($record_count, $pagesize, '', $id, 0, $page, 'user_order_gotoPage', 1, 0, 0, $config);
	}

	$limit = $user_order->limit;
	$pager = $user_order->fpage(array(0, 4, 5, 6, 9));
	$arr = array();
	$sql = 'SELECT IFNULL(bai.is_stages,0) is_stages, oi.main_order_id, oi.consignee,oi.pay_name, oi.order_id, oi.order_sn, oi.pay_id, ' . ' oi.order_status, oi.shipping_status, oi.pay_status, oi.add_time, oi.shipping_time, oi.auto_delivery_time, oi.sign_time, ' . '(oi.goods_amount + oi.shipping_fee + oi.insure_fee + oi.pay_fee + oi.pack_fee + oi.card_fee + oi.tax - oi.discount - oi.coupons) AS total_fee, ' . 'oi.invoice_no, oi.shipping_name, oi.tel, oi.email, oi.address, oi.province, oi.city, oi.district, oi.user_id, oi.surplus, oi.money_paid, ' . 'oi.bonus_id, oi.integral_money, oi.bonus ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' as oi' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('baitiao_log') . ' as bai on oi.order_id = bai.order_id' . (' WHERE oi.user_id = \'' . $user_id . '\' and oi.is_delete = \'' . $is_delete . '\'') . $where . ' AND (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi2 WHERE oi2.main_order_id = oi.order_id) = 0 ' . ' AND oi.is_zc_order = 0' . $act . ' ORDER BY oi.add_time DESC ' . $limit;
	$res = $GLOBALS['db']->query($sql);

	if (isset($GLOBALS['_CFG']['sign'])) {
		$sign_time = $GLOBALS['_CFG']['sign'];
	}
	else {
		$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code ="sign"';
		$sign_time = $GLOBALS['db']->getOne($sql);
	}

	$time = gmtime();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$select = ' (SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' AS c WHERE c.comment_type = 0 AND c.id_value = og.goods_id AND c.rec_id = og.rec_id AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\') AS sign1, ') . '(SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment_img') . ' AS ci, ' . $GLOBALS['ecs']->table('comment') . ' AS c' . (' WHERE c.comment_type = 0 AND c.id_value = og.goods_id AND ci.rec_id = og.rec_id AND c.parent_id = 0 AND c.user_id = \'' . $user_id . '\' AND ci.comment_id = c.comment_id )  AS sign2, ');
		$sql = 'SELECT ' . $select . 'og.ru_id, og.goods_id, og.extension_code FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og WHERE og.order_id = \'' . $row['order_id'] . '\' LIMIT 1';
		$order_goods = $GLOBALS['db']->getRow($sql);
		$row['ru_id'] = $order_goods['ru_id'];
		$row['goods_id'] = $order_goods['goods_id'];
		$row['extension_code'] = $order_goods['extension_code'];
		$row['sign1'] = $order_goods['sign1'];
		$row['sign2'] = $order_goods['sign2'];
		$pay_effective_time = isset($GLOBALS['_CFG']['pay_effective_time']) && 0 < $GLOBALS['_CFG']['pay_effective_time'] ? intval($GLOBALS['_CFG']['pay_effective_time']) : 0;
		$pay_code = get_table_date('payment', 'pay_id=\'' . $row['pay_id'] . '\'', array('pay_code'), 2);
		if (0 < $pay_effective_time && $row['pay_status'] == PS_UNPAYED && in_array($row['order_status'], array(OS_UNCONFIRMED, OS_CONFIRMED)) && in_array($row['shipping_status'], array(SS_UNSHIPPED, SS_PREPARING)) && !in_array($pay_code, array('bank', 'cod', 'post'))) {
			$pay_effective_time = $pay_effective_time * 60;

			if ($pay_effective_time < gmtime() - $row['add_time']) {
				$sql = 'SELECT store_id FROM' . $GLOBALS['ecs']->table('store_order') . ' WHERE order_id = \'' . $row['order_id'] . '\'';
				$store_order_id = $GLOBALS['db']->getOne($sql);
				$store_id = 0 < $store_order_id ? $store_order_id : 0;
				update_order($row['order_id'], array('order_status' => OS_INVALID));
				order_action($row['order_sn'], OS_INVALID, SS_UNSHIPPED, PS_UNPAYED, $GLOBALS['_LANG']['pay_effective_Invalid']);
				if ($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == SDT_PLACE) {
					change_order_goods_storage($row['order_id'], false, SDT_PLACE, 2, 0, $store_id);
				}

				return_user_surplus_integral_bonus($row);
			}
		}

		if ($row['shipping_status'] == SS_SHIPPED) {
			$auto_delivery_time = $row['shipping_time'] + $row['auto_delivery_time'] * 86400;
			$order_delay_day = isset($GLOBALS['_CFG']['order_delay_day']) && 0 < $GLOBALS['_CFG']['order_delay_day'] ? intval($GLOBALS['_CFG']['order_delay_day']) : 3;
			$row['auto_delivery_data'] = local_date('Y-m-d H:i:s', $auto_delivery_time);

			if (($auto_delivery_time - gmtime()) / 86400 < $order_delay_day) {
				$row['allow_order_delay'] = 1;
			}

			$row['auto_delivery_time'] = local_date($GLOBALS['_CFG']['time_format'], $auto_delivery_time);
		}

		if (($row['order_status'] == OS_UNCONFIRMED || $row['order_status'] == OS_CONFIRMED || $row['order_status'] == OS_SPLITED) && $row['pay_status'] == PS_UNPAYED) {
			$pay_log = get_pay_log($row['order_id'], 1);
			if ($pay_log && $pay_log['is_paid'] == 0) {
				$payment = payment_info($row['pay_id']);
				$file_pay = ROOT_PATH . 'includes/modules/payment/' . $payment['pay_code'] . '.php';
				if ($payment && file_exists($file_pay)) {
					include_once $file_pay;

					if (class_exists($payment['pay_code'])) {
						$pay_obj = new $payment['pay_code']();
						$is_callable = array($pay_obj, 'query');

						if (is_callable($is_callable)) {
							$order_other = array('order_sn' => $row['order_sn'], 'log_id' => $pay_log['log_id']);
							$pay_obj->query($order_other);
							$sql = 'SELECT order_status, shipping_status, pay_status, pay_time FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $row['order_id'] . '\' LIMIT 1';
							$order_info = $GLOBALS['db']->getRow($sql);

							if ($order_info) {
								$row['order_status'] = $order_info['order_status'];
								$row['shipping_status'] = $order_info['shipping_status'];
								$row['pay_status'] = $order_info['pay_status'];
								$row['pay_time'] = $order_info['pay_time'];
							}
						}
					}
				}
			}
		}

		$row['handler_return'] = '';
		$row['order_over'] = 0;
		$row['handler_order_status'] = false;

		if ($GLOBALS['_CFG']['open_delivery_time'] == 1) {
			if ($row['order_status'] == OS_SPLITED && $row['shipping_status'] == SS_SHIPPED && $row['pay_status'] == PS_PAYED) {
				$delivery_time = $row['shipping_time'] + 24 * 3600 * $row['auto_delivery_time'];

				if ($delivery_time <= $time) {
					$row['order_over'] = 1;
				}
			}
		}

		if ($row['order_status'] == OS_UNCONFIRMED) {
			$row['remind'] = $GLOBALS['_LANG']['confirm_cancel'];
			$row['original_handler'] = $GLOBALS['_LANG']['cancel'];
			$row['handler_act'] = 'cancel_order';
			$row['handler'] = '<a href="user.php?act=cancel_order&order_id=' . $row['order_id'] . '" onclick="if (!confirm(\'' . $GLOBALS['_LANG']['confirm_cancel'] . '\')) return false;">' . $GLOBALS['_LANG']['cancel'] . '</a>';
		}
		else {
			if ($row['order_status'] == OS_SPLITED || $row['order_status'] == OS_CONFIRMED || $row['order_status'] == OS_RETURNED_PART) {
				if ($row['shipping_status'] == SS_SHIPPED) {
					@$row['allow_order_delay_handler'] = $GLOBALS['_LANG']['allow_order_delay'];
					@$row['remind'] = $GLOBALS['_LANG']['confirm_received'];
					@$row['original_handler'] = $GLOBALS['_LANG']['received'];
					@$row['handler_act'] = 'affirm_received';
					@$row['handler'] = '<a href="user.php?act=affirm_received&order_id=' . $row['order_id'] . '" onclick="if (!confirm(\'' . $GLOBALS['_LANG']['confirm_received'] . '\')) return false;">' . $GLOBALS['_LANG']['received'] . '</a>';
				}
				else if ($row['shipping_status'] == SS_RECEIVED) {
					@$row['original_handler'] = $GLOBALS['_LANG']['ss_received'];
					@$row['handler'] = '<span style="color:red">' . $GLOBALS['_LANG']['ss_received'] . '</span>';
				}
				else {
					if ($row['pay_status'] == PS_UNPAYED || $row['pay_status'] == PS_PAYED_PART) {
						if ($order == 'auction') {
							@$row['handler_act'] = 'auction_order_detail';
						}
						else {
							@$row['handler_act'] = 'order_detail';
						}

						@$row['handler'] = '<a href="user.php?act=order_detail&order_id=' . $row['order_id'] . '">' . $GLOBALS['_LANG']['pay_money'] . '</a>';
					}
					else {
						@$row['original_handler'] = $GLOBALS['_LANG']['view_order'];

						if ($order == 'auction') {
							@$row['handler_act'] = 'auction_order_detail';
						}
						else {
							@$row['handler_act'] = 'order_detail';
						}

						@$row['handler'] = '<a href="user.php?act=order_detail&order_id=' . $row['order_id'] . '">' . $GLOBALS['_LANG']['view_order'] . '</a>';
					}
				}
			}
			else {
				$row['handler_order_status'] = true;
				$row['original_handler'] = $GLOBALS['_LANG']['os'][$row['order_status']];
				$row['handler'] = '<span style="color:red">' . $GLOBALS['_LANG']['os'][$row['order_status']] . '</span>';
				if ($row['pay_status'] == PS_UNPAYED && $row['shipping_status'] == SS_UNSHIPPED && $row['order_status'] != OS_CANCELED) {
					$row['remind'] = $GLOBALS['_LANG']['confirm_cancel'];
					$row['original_handler'] = $GLOBALS['_LANG']['cancel'];
					$row['handler_act'] = 'cancel_order';
					$row['handler'] = '<a href="user.php?act=cancel_order&order_id=' . $row['order_id'] . '" onclick="if (!confirm(\'' . $GLOBALS['_LANG']['confirm_cancel'] . '\')) return false;">' . $GLOBALS['_LANG']['cancel'] . '</a>';
				}
			}
		}

		$row['user_order'] = $row['order_status'];
		$row['user_shipping'] = $row['shipping_status'];
		$row['user_pay'] = $row['pay_status'];
		if (($row['user_order'] == OS_UNCONFIRMED || $row['user_order'] == OS_CANCELED) && $row['user_shipping'] == SS_UNSHIPPED && $row['user_pay'] == PS_UNPAYED) {
			$row['delete_yes'] = 0;
		}
		else {
			if ($row['user_order'] == OS_INVALID && $row['user_pay'] == PS_PAYED_PART && $row['user_shipping'] == SS_UNSHIPPED) {
				$row['delete_yes'] = 1;
			}
			else {
				if (($row['user_order'] == OS_CONFIRMED || $row['user_order'] == OS_SPLITED || $row['user_order'] == OS_SPLITING_PART) && $row['user_shipping'] != SS_RECEIVED && ($row['user_pay'] == PS_PAYED || $row['user_pay'] == PS_PAYED_PART) || ($row['user_shipping'] == SS_SHIPPED || $row['user_shipping'] == SS_RECEIVED) && $row['user_pay'] == PS_UNPAYED) {
					$row['delete_yes'] = 1;
				}
				else {
					$row['delete_yes'] = 0;
				}
			}
		}

		if ($row['sign1'] == 0) {
			$row['sign'] = 0;
		}
		else {
			if (0 < $row['sign1'] && $row['sign2'] == 0) {
				$row['sign'] = 1;
			}
			else {
				if (0 < $row['sign1'] && 0 < $row['sign2']) {
					$row['sign'] = 2;
				}
			}
		}

		$row['shipping_status'] = $row['shipping_status'] == SS_SHIPPED_ING ? SS_PREPARING : $row['shipping_status'];

		if ($row['order_status'] == OS_RETURNED) {
			$row['order_status'] = $GLOBALS['_LANG']['os'][$row['order_status']] . '<br />' . $GLOBALS['_LANG']['ps'][$row['pay_status']];
		}
		else if ($row['order_status'] == OS_ONLY_REFOUND) {
			$row['order_status'] = $GLOBALS['_LANG']['os'][$row['order_status']] . '<br />' . $GLOBALS['_LANG']['ps'][$row['pay_status']];
		}
		else {
			$row['order_status'] = $GLOBALS['_LANG']['os'][$row['order_status']] . '<br />' . $GLOBALS['_LANG']['ps'][$row['pay_status']] . '<br />' . $GLOBALS['_LANG']['ss'][$row['shipping_status']];
		}

		$br = '';
		$order_over = 0;
		if ($row['user_order'] == OS_SPLITED && $row['user_shipping'] == SS_RECEIVED && $row['user_pay'] == PS_PAYED) {
			$order_over = 1;
			$row['order_status'] = $GLOBALS['_LANG']['ss_received'];
			$row['original_handler'] = $GLOBALS['_LANG']['single_comment'];

			if (0 < $row['sign']) {
				$sign = '&sign=' . $row['sign'];

				if (0 < $row['ru_id']) {
					$row['original_handler'] = $GLOBALS['_LANG']['single_comment_on'];
				}
				else {
					$row['is_my_shop'] = 'my_shop';
				}
			}
			else {
				$sign = '';
			}

			$row['handler_order_status'] = false;

			if ($row['extension_code'] != 'package_buy') {
				$row['handler_act'] = 'commented_view';
			}

			$row['handler'] = '<a href="user.php?act=commented_view&order_id=' . $row['order_id'] . $sign . '">' . $row['original_handler'] . '</a><br/>';
			@$row['original_handler_return'] = $GLOBALS['_LANG']['return'];
			@$row['handler_return_act'] = 'goods_order';
			@$row['handler_return'] = '<a href="user.php?act=goods_order&order_id=' . $row['order_id'] . '" style="margin-left:5px;" >' . $GLOBALS['_LANG']['return'] . '</a><br/>';
		}
		else {
			if ($row['user_order'] == OS_CANCELED && $row['user_shipping'] == SS_UNSHIPPED && $row['user_pay'] == PS_UNPAYED) {
				$order_over = 1;
				$row['order_status'] = $GLOBALS['_LANG']['os'][OS_CANCELED];
				$row['handler_order_status'] = false;
				$row['handler'] = '';
			}
			else {
				if ($row['user_order'] == OS_SPLITED && $row['user_shipping'] == SS_SHIPPED && $row['user_pay'] == PS_PAYED) {
					$row['handler'] = $row['handler'];
					$br = '<br/>';
				}
				else {
					if ($row['user_order'] == OS_CONFIRMED && $row['user_shipping'] == SS_RECEIVED && $row['user_pay'] == PS_PAYED) {
						$order_over = 1;
						$row['order_status'] = $GLOBALS['_LANG']['ss_received'];
						$row['original_handler'] = $GLOBALS['_LANG']['single_comment'];

						if (0 < $row['sign']) {
							$sign = '&sign=' . $row['sign'];
							$row['original_handler'] = $GLOBALS['_LANG']['single_comment_on'];
						}
						else {
							$sign = '';
						}

						$row['handler_order_status'] = false;

						if ($row['extension_code'] != 'package_buy') {
							$row['handler_act'] = 'commented_view';
						}

						$row['handler'] = '<a href="user.php?act=commented_view&order_id=' . $row['order_id'] . $sign . '">' . $row['original_handler'] . '</a><br/>';
						@$row['original_handler_return'] = $GLOBALS['_LANG']['return'];
						@$row['handler_return_act'] = 'goods_order';
						@$row['handler_return'] = '<a href="user.php?act=goods_order&order_id=' . $row['order_id'] . '" style="margin-left:5px;" >' . $GLOBALS['_LANG']['return'] . '</a><br/>';
					}
					else {
						if (!($row['user_order'] == OS_UNCONFIRMED && $row['user_shipping'] == SS_UNSHIPPED && $row['user_pay'] == PS_UNPAYED) && !($row['user_order'] == OS_CONFIRMED && $row['user_shipping'] == SS_SHIPPED && $row['user_pay'] == PS_PAYED)) {
							$row['handler_order_status'] = false;
							$row['handler'] = '';
						}
						else {
							$br = '<br/>';
						}
					}
				}
			}
		}

		$sql = 'SELECT sid FROM ' . $GLOBALS['ecs']->table('comment_seller') . ' WHERE order_id = \'' . $row['order_id'] . '\'';
		$is_comment_again = $GLOBALS['db']->getOne($sql, true);
		$row['is_comment_again'] = $is_comment_again;
		$row['sign_url'] = $sign;

		if (0 < $sign_time) {
			if ($row['user_pay'] == PS_UNPAYED) {
				$where_log = ' AND ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART), 'order_status') . ' AND ' . db_create_in(array(SS_RECEIVED), 'shipping_status') . ' AND ' . db_create_in(array(PS_UNPAYED), 'pay_status');
			}
			else if ($row['user_pay'] == PS_PAYED) {
				$where_log = ' AND ' . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART), 'order_status') . ' AND ' . db_create_in(array(SS_UNSHIPPED, SS_SHIPPED, SS_RECEIVED, SS_SHIPPED_PART, OS_SHIPPED_PART), 'shipping_status') . ' AND ' . db_create_in(array(PS_PAYED), 'pay_status');
			}

			$sql = 'SELECT log_time FROM ' . $GLOBALS['ecs']->table('order_action') . ' WHERE order_id =' . $row['order_id'] . ' ' . $where_log . ' order by action_id DESC';
			$log_time = $GLOBALS['db']->getOne($sql);
			$order_status = array(OS_CANCELED, OS_INVALID, OS_RETURNED);

			if (!in_array($row['user_order'], $order_status)) {
				if (!$log_time) {
					$log_time = !empty($row['pay_time']) ? $row['pay_time'] : $row['add_time'];
				}

				$signtime = $log_time + $sign_time * 3600 * 24;
				if ($time < $signtime && $row['user_pay'] == PS_PAYED) {
					@$row['original_handler_return'] = $GLOBALS['_LANG']['return'];
					@$row['handler_return_act'] = 'goods_order';
					@$row['handler_return'] = $br . '<a href="user.php?act=goods_order&order_id=' . $row['order_id'] . '" style="margin-left:5px;" >' . $GLOBALS['_LANG']['return'] . '</a>';
				}
				else {
					@$row['original_handler_return'] = '';
					@$row['handler_return'] = '';
				}
			}

			if ($row['handler_return']) {
				$row['return_url'] = 'user.php?act=goods_order&order_id=' . $row['order_id'];
			}
		}

		$ru_id = $row['ru_id'];
		$row['order_goods'] = get_order_goods_toInfo($row['order_id']);
		$row['order_goods_count'] = count($row['order_goods']);
		$order_id = $row['order_id'];
		$date = array('order_id');
		$order_child = count(get_table_date('order_info', 'main_order_id=\'' . $order_id . '\'', $date, 1));
		$row[$key]['order_child'] = $order_child;
		$sql = 'select order_id from ' . $GLOBALS['ecs']->table('order_info') . ' where main_order_id = \'' . $row['main_order_id'] . '\' and main_order_id > 0';
		$order_count = count($GLOBALS['db']->getAll($sql));
		$sql = 'select kf_type, kf_ww, kf_qq  from ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' where ru_id=\'' . $ru_id . '\'');
		$basic_info = $GLOBALS['db']->getRow($sql);
		$sql = 'select invoice_no, shipping_name, update_time from ' . $GLOBALS['ecs']->table('delivery_order') . ' where order_id = \'' . $row['order_id'] . '\'';
		$delivery = $GLOBALS['db']->getRow($sql);
		$sql = 'select invoice_no, shipping_name, update_time from ' . $GLOBALS['ecs']->table('delivery_order') . ' where order_id = \'' . $row['order_id'] . '\'';
		$delivery = $GLOBALS['db']->getRow($sql);
		$province = get_order_region_name($row['province']);
		$city = get_order_region_name($row['city']);
		$district = get_order_region_name($row['district']);

		if ($district['region_name']) {
			$district_name = $district['region_name'];
		}

		$address_detail = $province['region_name'] . '&nbsp;' . $city['region_name'] . '市' . '&nbsp;' . $district_name;
		$delivery['delivery_time'] = local_date($GLOBALS['_CFG']['time_format'], $delivery['update_time']);

		if ($handle_type == 1) {
			$row['order_status'] = str_replace(array('<br />'), '', $row['order_status']);
		}

		if (defined('THEME_EXTENSION')) {
			$row['order_status'] = str_replace(array('<br>', '<br />'), array('', '，'), $row['order_status']);
		}

		$row['shop_name'] = get_shop_name($ru_id, 1);
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

		if (!empty($row['invoice_no'])) {
			$invoice_no_arr = explode(',', $row['invoice_no']);
			$row['invoice_no'] = reset($invoice_no_arr);
		}

		if ($row['extension_code'] == 'package_buy') {
			$activity = get_goods_activity_info($row['goods_id'], array('act_id'));

			if ($activity) {
				$row['is_package'] = $activity['act_id'];
			}
		}

		$arr[] = array('order_id' => $row['order_id'], 'order_sn' => $row['order_sn'], 'order_time' => local_date($GLOBALS['_CFG']['time_format'], $row['add_time']), 'sign' => $row['sign'], 'is_IM' => $shop_information['is_IM'], 'is_dsc' => $row['is_dsc'], 'order_status' => $row['order_status'], 'ru_id' => $row['ru_id'], 'status_number' => $status_number, 'consignee' => $row['consignee'], 'main_order_id' => $row['main_order_id'], 'shop_name' => $row['shop_name'], 'shop_url' => $row['shop_url'], 'order_goods' => $row['order_goods'], 'order_goods_count' => $row['order_goods_count'], 'order_child' => $order_child, 'no_picture' => $GLOBALS['_CFG']['no_picture'], 'order_child' => $order_child, 'delete_yes' => $row['delete_yes'], 'invoice_no' => $row['invoice_no'], 'shipping_name' => $row['shipping_name'], 'pay_name' => $row['pay_name'], 'email' => $row['email'], 'address_detail' => $row['address_detail'], 'address' => $row['address'], 'address_detail' => $address_detail, 'tel' => $row['tel'], 'delivery_time' => $delivery['delivery_time'], 'order_count' => $order_count, 'kf_type' => $basic_info['kf_type'], 'kf_ww' => $kf_ww_one, 'kf_qq' => $kf_qq_one, 'total_fee' => price_format($row['total_fee'], false), 'handler_return' => $row['handler_return'], 'handler' => $row['handler'], 'original_handler' => $row['original_handler'], 'is_my_shop' => $row['is_my_shop'], 'original_handler_return' => $row['original_handler_return'], 'handler_act' => $row['handler_act'], 'handler_return_act' => $row['handler_return_act'], 'return_url' => $row['return_url'], 'remind' => $row['remind'] ? $row['remind'] : '', 'handler_order_status' => $row['handler_order_status'] ? true : false, 'is_stages' => $row['is_stages'], 'order_over' => $row['order_over'], 'sign' => $row['sign'], 'sign_url' => $row['sign_url'], 'auto_delivery_data' => $row['auto_delivery_data'], 'allow_order_delay' => $row['allow_order_delay'], 'auto_delivery_time' => $row['auto_delivery_time'], 'allow_order_delay_handler' => $row['allow_order_delay_handler'], 'is_comment_again' => $row['is_comment_again'], 'is_package' => $row['is_package']);
	}

	$order_list = array('order_list' => $arr, 'pager' => $pager, 'record_count' => $record_count);
	return $order_list;
}

function get_order_search_keyword($order = array())
{
	$where = '';
	$condition = $order->idTxt == 'signNum' ? true : false;

	if (isset($order->keyword)) {
		if ($order->type == 'dateTime' || $order->type == 'order_status' || $order->type == 'toBe_confirmed' || $order->type == 'toBe_finished' || $order->type == 'toBe_pay' || $order->type == 'toBe_unconfirmed' || $condition) {
			if ($order->idTxt == 'submitDate') {
				$date_keyword = $order->keyword;
				$status_keyword = $order->status_keyword;
			}
			else if ($order->idTxt == 'status_list') {
				$date_keyword = $order->date_keyword;
				$status_keyword = $order->keyword;
			}
			else {
				if ($order->idTxt == 'payId' || $order->idTxt == 'to_finished' || $order->idTxt == 'to_confirm_order' || $order->idTxt == 'to_unconfirmed' || $condition) {
					$status_keyword = $order->keyword;
				}
			}

			$firstSecToday = local_mktime(0, 0, 0, date('m'), date('d'), date('Y'));
			$lastSecToday = local_mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;

			if ($date_keyword == 'today') {
				$where .= ' AND oi.add_time >= \'' . $firstSecToday . '\' and oi.add_time <= \'' . $lastSecToday . '\'';
			}
			else if ($date_keyword == 'three_today') {
				$firstSecToday = $firstSecToday - 24 * 3600 * 2;
				$where .= ' AND oi.add_time >= \'' . $firstSecToday . '\' and oi.add_time <= \'' . $lastSecToday . '\'';
			}
			else if ($date_keyword == 'aweek') {
				$firstSecToday = $firstSecToday - 24 * 3600 * 6;
				$where .= ' AND oi.add_time >= \'' . $firstSecToday . '\' and oi.add_time <= \'' . $lastSecToday . '\'';
			}
			else if ($date_keyword == 'thismonth') {
				$first_month_day = strtotime('-1 month');
				$last_month_day = gmtime();
				$where .= ' AND oi.add_time >= \'' . $first_month_day . '\' and oi.add_time <= \'' . $last_month_day . '\'';
			}

			switch ($status_keyword) {
			case CS_AWAIT_PAY:
				$where .= get_order_query_sql('await_pay', 'oi.');
				break;

			case CS_AWAIT_SHIP:
				$where .= get_order_query_sql('await_ship', 'oi.');
				break;

			case CS_FINISHED:
				$where .= get_order_query_sql('finished', 'oi.');
				break;

			case CS_TO_CONFIRM:
				$where .= get_order_query_sql('to_confirm', 'oi.');
				break;

			case OS_UNCONFIRMED:
				$where .= get_order_query_sql('unconfirmed', 'oi.');
				break;

			case PS_PAYING:
				if ($status_keyword != -1) {
					$where .= ' AND oi.pay_status = \'' . $status_keyword . '\' ';
				}

				break;

			case OS_SHIPPED_PART:
				if ($status_keyword != -1) {
					$where .= ' AND oi.shipping_status  = \'' . $status_keyword . '\'-2 ';
				}

				break;

			default:
				if ($status_keyword != -1) {
					$where .= ' AND oi.order_status = \'' . $status_keyword . '\' ';
				}
			}
		}
	}

	return $where;
}

function get_auction_search_keyword($auction = array())
{
	$where = '';

	if (isset($auction->keyword)) {
		if ($auction->type == 'text') {
			if ($auction->keyword == $GLOBALS['_LANG']['user_keyword']) {
				$auction->keyword = '';
			}

			$where .= ' AND (ga.goods_name LIKE \'%' . mysql_like_quote($auction->keyword) . '%\')';
		}
		else {
			if ($auction->type == 'dateTime' || $auction->type == 'order_status' || $auction->type == 'is_going' || $auction->type == 'is_finished') {
				if ($auction->idTxt == 'submitDate') {
					$date_keyword = $auction->keyword;
					$status_keyword = $auction->status_keyword;
				}
				else if ($auction->idTxt == 'status_list') {
					$date_keyword = $auction->date_keyword;
					$status_keyword = $auction->keyword;
				}
				else {
					if ($auction->idTxt == 'is_going' || $auction->idTxt == 'is_finished') {
						$status_keyword = $auction->keyword;
					}
				}

				$firstSecToday = local_mktime(0, 0, 0, date('m'), date('d'), date('Y'));
				$lastSecToday = local_mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;

				if ($date_keyword == 'today') {
					$where .= ' AND al.bid_time >= \'' . $firstSecToday . '\' and al.bid_time <= \'' . $lastSecToday . '\'';
				}
				else if ($date_keyword == 'three_today') {
					$firstSecToday = $firstSecToday - 24 * 3600 * 2;
					$where .= ' AND al.bid_time >= \'' . $firstSecToday . '\' and al.bid_time <= \'' . $lastSecToday . '\'';
				}
				else if ($date_keyword == 'aweek') {
					$firstSecToday = $firstSecToday - 24 * 3600 * 6;
					$where .= ' AND al.bid_time >= \'' . $firstSecToday . '\' and al.bid_time <= \'' . $lastSecToday . '\'';
				}
				else if ($date_keyword == 'thismonth') {
					$first_month_day = local_mktime(0, 0, 0, date('m'), 1, date('Y'));
					$last_month_day = local_mktime(0, 0, 0, date('m'), date('t'), date('Y')) - 1;
					$where .= ' AND al.bid_time >= \'' . $first_month_day . '\' and al.bid_time <= \'' . $last_month_day . '\'';
				}

				$time = gmtime();

				switch ($status_keyword) {
				case 1:
					$where .= ' AND ga.is_finished= 0 AND ga.end_time > ' . $time . ' AND  ga.start_time < ' . $time . ' ';
					break;

				case 3:
					$where .= ' AND (ga.is_finished > 0 OR ga.is_finished = 0 AND ga.end_time < ' . $time . ')';
					break;
				}
			}
		}
	}

	return $where;
}

function get_snatch_search_keyword($snatch = array())
{
	$where = '';

	if (isset($snatch->keyword)) {
		if ($snatch->type == 'text') {
			if ($snatch->keyword == $GLOBALS['_LANG']['user_keyword']) {
				$snatch->keyword = '';
			}

			$where .= ' AND (ga.goods_name LIKE \'%' . mysql_like_quote($snatch->keyword) . '%\')';
		}
		else {
			if ($snatch->type == 'dateTime' || $snatch->type == 'order_status' || $snatch->type == 'is_going' || $snatch->type == 'is_finished') {
				if ($snatch->idTxt == 'submitDate') {
					$date_keyword = $snatch->keyword;
					$status_keyword = $snatch->status_keyword;
				}
				else if ($snatch->idTxt == 'status_list') {
					$date_keyword = $snatch->date_keyword;
					$status_keyword = $snatch->keyword;
				}
				else {
					if ($snatch->idTxt == 'is_going' || $snatch->idTxt == 'is_finished') {
						$status_keyword = $snatch->keyword;
					}
				}

				$firstSecToday = local_mktime(0, 0, 0, date('m'), date('d'), date('Y'));
				$lastSecToday = local_mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;

				if ($date_keyword == 'today') {
					$where .= ' AND al.bid_time >= \'' . $firstSecToday . '\' and al.bid_time <= \'' . $lastSecToday . '\'';
				}
				else if ($date_keyword == 'three_today') {
					$firstSecToday = $firstSecToday - 24 * 3600 * 2;
					$where .= ' AND al.bid_time >= \'' . $firstSecToday . '\' and al.bid_time <= \'' . $lastSecToday . '\'';
				}
				else if ($date_keyword == 'aweek') {
					$firstSecToday = $firstSecToday - 24 * 3600 * 6;
					$where .= ' AND al.bid_time >= \'' . $firstSecToday . '\' and al.bid_time <= \'' . $lastSecToday . '\'';
				}
				else if ($date_keyword == 'thismonth') {
					$first_month_day = local_mktime(0, 0, 0, date('m'), 1, date('Y'));
					$last_month_day = local_mktime(0, 0, 0, date('m'), date('t'), date('Y')) - 1;
					$where .= ' AND al.bid_time >= \'' . $first_month_day . '\' and al.bid_time <= \'' . $last_month_day . '\'';
				}

				$time = gmtime();

				switch ($status_keyword) {
				case 1:
					$where .= ' AND ga.is_finished= 0 AND ga.end_time > ' . $time . ' AND  ga.start_time < ' . $time . ' ';
					break;

				case 3:
					$where .= ' AND (ga.is_finished > 0 OR ga.is_finished = 0 AND ga.end_time < ' . $time . ')';
					break;
				}
			}
		}
	}

	return $where;
}

function cancel_order($order_id, $user_id = 0)
{
	$sql = 'SELECT user_id, order_id, order_sn , surplus , integral , bonus_id, order_status, shipping_status, pay_status FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\'');
	$order = $GLOBALS['db']->GetRow($sql);

	if (empty($order)) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['order_exist']);
		return false;
	}

	if (0 < $user_id && $order['user_id'] != $user_id) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['no_priv']);
		return false;
	}

	if ($order['order_status'] != OS_UNCONFIRMED && $order['order_status'] != OS_CONFIRMED) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['current_os_not_unconfirmed']);
		return false;
	}

	if ($order['order_status'] == OS_CONFIRMED) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['current_os_already_confirmed']);
		return false;
	}

	if ($order['shipping_status'] != SS_UNSHIPPED) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['current_ss_not_cancel']);
		return false;
	}

	if ($order['pay_status'] != PS_UNPAYED) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['current_ps_not_cancel']);
		return false;
	}

	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET order_status = \'' . OS_CANCELED . ('\' WHERE order_id = \'' . $order_id . '\'');

	if ($GLOBALS['db']->query($sql)) {
		order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED, $GLOBALS['_LANG']['buyer_cancel'], 'buyer');
		return_card_money($order_id);
		if (0 < $order['user_id'] && 0 < $order['surplus']) {
			$change_desc = sprintf($GLOBALS['_LANG']['return_surplus_on_cancel'], $order['order_sn']);
			log_account_change($order['user_id'], $order['surplus'], 0, 0, 0, $change_desc);
		}

		if (0 < $order['user_id'] && 0 < $order['integral']) {
			$change_desc = sprintf($GLOBALS['_LANG']['return_integral_on_cancel'], $order['order_sn']);
			log_account_change($order['user_id'], 0, 0, 0, $order['integral'], $change_desc);
		}

		if (0 < $order['user_id'] && 0 < $order['bonus_id']) {
			change_user_bonus($order['bonus_id'], $order['order_id'], false);
		}

		if ($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == SDT_PLACE) {
			change_order_goods_storage($order['order_id'], false, 1, 3);
		}

		$arr = array('bonus_id' => 0, 'bonus' => 0, 'integral' => 0, 'integral_money' => 0, 'surplus' => 0);
		update_order($order['order_id'], $arr);
		return true;
	}
	else {
		exit($GLOBALS['db']->errorMsg());
	}
}

function cancel_return($ret_id, $user_id = 0)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('order_return') . (' WHERE ret_id = \'' . $ret_id . '\'');
	$order = $GLOBALS['db']->GetRow($sql);

	if (empty($order)) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['return_exist']);
		return false;
	}

	if (0 < $user_id && $order['user_id'] != $user_id) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['no_priv']);
		return false;
	}

	if ($order['return_status'] != RF_APPLICATION && $order['refound_status'] != FF_NOREFOUND) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['return_not_unconfirmed']);
		return false;
	}

	if ($order['return_status'] == RF_RECEIVE) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['current_os_already_receive']);
		return false;
	}

	if ($order['return_status'] == RF_SWAPPED_OUT_SINGLE || $order['return_status'] == RF_SWAPPED_OUT) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['already_out_goods']);
		return false;
	}

	if ($order['refound_status'] == FF_REFOUND) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['have_refound']);
		return false;
	}

	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('order_return') . ' WHERE ret_id =' . $ret_id;

	if ($GLOBALS['db']->query($sql)) {
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('return_goods') . ' WHERE rec_id =' . $order['rec_id'];
		$GLOBALS['db']->query($sql);
		$sql = 'select img_file from ' . $GLOBALS['ecs']->table('return_images') . ' where user_id = \'' . $_SESSION['user_id'] . '\' and rec_id = \'' . $order['rec_id'] . '\'';
		$img_list = $GLOBALS['db']->getAll($sql);

		if ($img_list) {
			foreach ($img_list as $key => $row) {
				@unlink(ROOT_PATH . $row['img_file']);
			}

			$sql = 'delete from ' . $GLOBALS['ecs']->table('return_images') . ' where user_id = \'' . $_SESSION['user_id'] . '\' and rec_id = \'' . $order['rec_id'] . '\'';
			$GLOBALS['db']->query($sql);
		}

		$sql = 'delete from ' . $GLOBALS['ecs']->table('order_return_extend') . ' where ret_id = \'' . $ret_id . '\' ';
		$GLOBALS['db']->query($sql);
		return_action($ret_id, '取消', '', '', '买家', '');
		return true;
	}
	else {
		exit($GLOBALS['db']->errorMsg());
	}
}

function affirm_received($order_id, $user_id = 0)
{
	$sql = 'SELECT order_id, user_id, order_sn , order_status, shipping_status, pay_status, ' . 'order_amount, goods_amount, tax, shipping_fee, insure_fee, pay_fee, pack_fee, card_fee, ' . 'bonus, integral_money, coupons, discount, money_paid, surplus, confirm_take_time ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $order_id . '\'');
	$order = $GLOBALS['db']->GetRow($sql);
	if (0 < $user_id && $order['user_id'] != $user_id) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['no_priv']);
		return false;
	}
	else if ($order['shipping_status'] == SS_RECEIVED) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['order_already_received']);
		return false;
	}
	else if ($order['shipping_status'] != SS_SHIPPED) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['order_invalid']);
		return false;
	}
	else {
		if ($order['order_status'] == OS_RETURNED_PART) {
			$order['order_status'] = OS_SPLITED;
			$change_order_status = ', order_status = \'' . $order['order_status'] . '\'';
		}
		else {
			$change_order_status = '';
		}

		$confirm_take_time = gmtime();
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET shipping_status = \'' . SS_RECEIVED . ('\', confirm_take_time = \'' . $confirm_take_time . '\' ' . $change_order_status . ' WHERE order_id = \'' . $order_id . '\'');

		if ($GLOBALS['db']->query($sql)) {
			order_action($order['order_sn'], $order['order_status'], SS_RECEIVED, $order['pay_status'], '', $GLOBALS['_LANG']['buyer'], 0, $confirm_take_time);
			$seller_id = $GLOBALS['db']->getOne('SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\''), true);
			$value_card = $GLOBALS['db']->getOne('SELECT use_val FROM ' . $GLOBALS['ecs']->table('value_card_record') . (' WHERE order_id = \'' . $order_id . '\''), true);
			$return_amount = get_order_return_amount($order_id);
			$other = array('user_id' => $order['user_id'], 'seller_id' => $seller_id, 'order_id' => $order['order_id'], 'order_sn' => $order['order_sn'], 'order_status' => $order['order_status'], 'shipping_status' => SS_RECEIVED, 'pay_status' => $order['pay_status'], 'order_amount' => $order['order_amount'], 'return_amount' => $return_amount, 'goods_amount' => $order['goods_amount'], 'tax' => $order['tax'], 'shipping_fee' => $order['shipping_fee'], 'insure_fee' => $order['insure_fee'], 'pay_fee' => $order['pay_fee'], 'pack_fee' => $order['pack_fee'], 'card_fee' => $order['card_fee'], 'bonus' => $order['bonus'], 'integral_money' => $order['integral_money'], 'coupons' => $order['coupons'], 'discount' => $order['discount'], 'value_card' => $value_card, 'money_paid' => $order['money_paid'], 'surplus' => $order['surplus'], 'confirm_take_time' => $confirm_take_time);

			if ($seller_id) {
				get_order_bill_log($other);
			}

			return true;
		}
		else {
			exit($GLOBALS['db']->errorMsg());
		}
	}
}

function affirm_return_received($order_id, $user_id = 0)
{
	$sql = 'SELECT user_id, return_sn , return_status FROM ' . $GLOBALS['ecs']->table('order_return') . (' WHERE order_id = \'' . $order_id . '\'');
	$return_order = $GLOBALS['db']->GetRow($sql);
	if (0 < $user_id && $return_order['user_id'] != $user_id) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['no_priv']);
		return false;
	}
	else if ($return_order['return_status'] == 4) {
		$GLOBALS['err']->add('此订单已经确认收货');
		return false;
	}
	else {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_return') . ' SET return_status = \'' . 4 . ('\' WHERE order_id = \'' . $order_id . '\'');

		if ($GLOBALS['db']->query($sql)) {
			order_action($order['return_sn'], $order['return_status'], SS_RECEIVED, '', '', $GLOBALS['_LANG']['buyer']);
			return true;
		}
		else {
			exit($GLOBALS['db']->errorMsg());
		}
	}
}

function save_consignee($consignee, $default = false)
{
	if (0 < $consignee['address_id']) {
		$res = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('user_address'), $consignee, 'UPDATE', 'address_id = ' . $consignee['address_id'] . ' AND `user_id`= \'' . $_SESSION['user_id'] . '\'');
	}
	else {
		$res = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('user_address'), $consignee, 'INSERT');
		$consignee['address_id'] = $GLOBALS['db']->insert_id();
	}

	if ($default) {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . (' SET address_id = \'' . $consignee['address_id'] . '\' WHERE user_id = \'' . $_SESSION['user_id'] . '\'');
		$res = $GLOBALS['db']->query($sql);
	}

	return $res !== false;
}

function drop_consignee($id)
{
	$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('user_address') . (' WHERE address_id = \'' . $id . '\'');
	$uid = $GLOBALS['db']->getOne($sql);

	if ($uid != $_SESSION['user_id']) {
		return false;
	}
	else {
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('user_address') . (' WHERE address_id = \'' . $id . '\'');
		$res = $GLOBALS['db']->query($sql);
		return $res;
	}
}

function update_address($address, $default = 0)
{
	$address_id = intval($address['address_id']);
	unset($address['address_id']);

	if (0 < $address_id) {
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('user_address'), $address, 'UPDATE', 'address_id = ' . $address_id . ' AND user_id = ' . $address['user_id']);
	}
	else {
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('user_address'), $address, 'INSERT');
		$address_id = $GLOBALS['db']->insert_id();
	}

	if (0 < $address_id) {
		$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('user_address') . ' WHERE user_id = \'' . $address['user_id'] . '\'';
		$res_count = $GLOBALS['db']->getOne($sql);

		if ($res_count == 1) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET address_id = \'' . $address_id . '\' ' . ' WHERE user_id = \'' . $address['user_id'] . '\'';
			$GLOBALS['db']->query($sql);
			$_SESSION['flow_consignee'] = $address;
		}
	}

	if (0 < $default && isset($address['user_id'])) {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . ' SET address_id = \'' . $address_id . '\' ' . ' WHERE user_id = \'' . $address['user_id'] . '\'';
		$GLOBALS['db']->query($sql);
	}

	return true;
}

function get_order_detail($order_id, $user_id = 0)
{
	include_once ROOT_PATH . 'includes/lib_order.php';
	$order_id = intval($order_id);

	if ($order_id <= 0) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['invalid_order_id']);
		return false;
	}

	$order = order_info($order_id);
	$order['allow_order_delay'] = 0;

	if ($order['shipping_status'] == SS_SHIPPED) {
		$auto_delivery_time = $order['shipping_time'] + $order['auto_delivery_time'] * 86400;
		$order_delay_day = isset($GLOBALS['_CFG']['order_delay_day']) && 0 < $GLOBALS['_CFG']['order_delay_day'] ? intval($GLOBALS['_CFG']['order_delay_day']) : 3;
		$order['auto_delivery_data'] = local_date('Y-m-d H:i:s', $auto_delivery_time);

		if (($auto_delivery_time - gmtime()) / 86400 < $order_delay_day) {
			$order['allow_order_delay'] = 1;
		}

		$order['auto_delivery_time'] = local_date($GLOBALS['_CFG']['time_format'], $auto_delivery_time);
	}

	if (0 < $user_id && $user_id != $order['user_id']) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['no_priv']);
		return false;
	}

	if (!empty($order['invoice_no'])) {
		$shipping_code = $GLOBALS['db']->GetOne('SELECT shipping_code FROM ' . $GLOBALS['ecs']->table('shipping') . (' WHERE shipping_id = \'' . $order['shipping_id'] . '\''));
		$plugin = ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php';

		if (file_exists($plugin)) {
			include_once $plugin;
			$shipping = new $shipping_code();
		}
	}

	if ($order['order_status'] == OS_UNCONFIRMED) {
		$order['allow_update_address'] = 1;
	}
	else {
		$order['allow_update_address'] = 0;
	}

	$order['exist_real_goods'] = exist_real_goods($order_id);
	if ($order['pay_status'] == PS_PAYED_PART || $order['pay_status'] == PS_UNPAYED && ($order['order_status'] == OS_UNCONFIRMED || $order['order_status'] == OS_CONFIRMED)) {
		$payment_info = array();
		$payment_info = payment_info($order['pay_id']);

		if ($payment_info === false) {
			$order['pay_online'] = '';
		}
		else if (substr($payment_info['pay_code'], 0, 4) == 'pay_') {
			$order['pay_online'] = '';
		}
		else {
			$payment = unserialize_config($payment_info['pay_config']);
			$order['log_id'] = get_paylog_id($order['order_id'], $pay_type = PAY_ORDER);
			$order['user_name'] = $_SESSION['user_name'];
			$order['pay_desc'] = $payment_info['pay_desc'];
			include_once ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php';
			$pay_obj = new $payment_info['pay_code']();
			$order['pay_online'] = $pay_obj->get_code($order, $payment);
		}
	}
	else {
		$order['pay_online'] = '';
	}

	$order['shipping_id'] == -1 && $order['shipping_name'] = $GLOBALS['_LANG']['shipping_not_need'];
	$order['how_oos_name'] = $order['how_oos'];
	$order['how_surplus_name'] = $order['how_surplus'];

	if ($order['pay_status'] != PS_UNPAYED) {
		$virtual_goods = get_virtual_goods($order_id, true);
		$virtual_card = array();

		foreach ($virtual_goods as $code => $goods_list) {
			if ($code == 'virtual_card') {
				foreach ($goods_list as $goods) {
					if ($info = virtual_card_result($order['order_sn'], $goods)) {
						$virtual_card[] = array('goods_id' => $goods['goods_id'], 'goods_name' => $goods['goods_name'], 'info' => $info);
					}
				}
			}

			if ($code == 'package_buy') {
				foreach ($goods_list as $goods) {
					$sql = 'SELECT g.goods_id FROM ' . $GLOBALS['ecs']->table('package_goods') . ' AS pg, ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'WHERE pg.goods_id = g.goods_id AND pg.package_id = \'' . $goods['goods_id'] . '\' AND extension_code = \'virtual_card\'';
					$vcard_arr = $GLOBALS['db']->getAll($sql);

					foreach ($vcard_arr as $val) {
						if ($info = virtual_card_result($order['order_sn'], $val)) {
							$virtual_card[] = array('goods_id' => $goods['goods_id'], 'goods_name' => $goods['goods_name'], 'info' => $info);
						}
					}
				}
			}
		}

		$var_card = deleteRepeat($virtual_card);
		$GLOBALS['smarty']->assign('virtual_card', $var_card);
	}

	if (0 < $order['confirm_time'] && ($order['order_status'] == OS_CONFIRMED || $order['order_status'] == OS_SPLITED || $order['order_status'] == OS_SPLITING_PART)) {
		$order['confirm_time'] = sprintf($GLOBALS['_LANG']['confirm_time'], local_date($GLOBALS['_CFG']['time_format'], $order['confirm_time']));
	}
	else {
		$order['confirm_time'] = '';
	}

	if (0 < $order['pay_time'] && $order['pay_status'] != PS_UNPAYED) {
		$order['pay_time'] = sprintf($GLOBALS['_LANG']['pay_time'], local_date($GLOBALS['_CFG']['time_format'], $order['pay_time']));
	}
	else {
		$order['pay_time'] = '';
	}

	if (0 < $order['shipping_time'] && in_array($order['shipping_status'], array(SS_SHIPPED, SS_RECEIVED))) {
		$order['shipping_time'] = local_date($GLOBALS['_CFG']['time_format'], $order['shipping_time']);
	}
	else {
		$order['shipping_time'] = '';
	}

	if (!empty($order['confirm_take_time'])) {
		$order['confirm_take_time'] = sprintf($GLOBALS['_LANG']['confirm_time'], local_date($GLOBALS['_CFG']['time_format'], $order['confirm_take_time']));
	}
	else {
		$order['confirm_take_time'] = '';
	}

	if ($GLOBALS['_CFG']['customer_service'] == 0) {
		$ru_id = 0;
	}
	else {
		$ru_id = $order['ru_id'];
	}

	$shop_information = get_shop_name($ru_id);
	$order['is_IM'] = $shop_information['is_IM'];

	if ($ru_id == 0) {
		if ($GLOBALS['db']->getOne('SELECT kf_im_switch FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = 0', true)) {
			$order['is_dsc'] = true;
		}
		else {
			$order['is_dsc'] = false;
		}
	}
	else {
		$order['is_dsc'] = false;
	}

	$sql = 'select kf_type, kf_ww, kf_qq  from ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' where ru_id = \'' . $ru_id . '\' LIMIT 1');
	$basic_info = $GLOBALS['db']->getRow($sql);

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

	$order['kf_type'] = $basic_info['kf_type'];
	$order['kf_ww'] = $kf_ww_one;
	$order['kf_qq'] = $kf_qq_one;
	$order['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $order['add_time']);
	$sql = 'SELECT concat(IFNULL(p.region_name, \'\'), ' . '\'  \', IFNULL(t.region_name, \'\'), \'  \', IFNULL(d.region_name, \'\')) AS region ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS p ON o.province = p.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS t ON o.city = t.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS d ON o.district = d.region_id ' . 'WHERE o.order_id = \'' . $order['order_id'] . '\'';
	$order['region'] = $GLOBALS['db']->getOne($sql);
	$sql = 'SELECT ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' LIMIT 1');
	$order_goods = $GLOBALS['db']->getRow($sql);
	$order['ru_id'] = $order_goods['ru_id'];
	$sql = 'SELECT store_id FROM ' . $GLOBALS['ecs']->table('store_order') . (' WHERE order_id = \'' . $order_id . '\'');
	$order['store_id'] = $GLOBALS['db']->getOne($sql, true);
	return $order;
}

function get_return_detail($ret_id, $user_id = 0)
{
	include_once ROOT_PATH . 'includes/lib_order.php';
	$ret_id = intval($ret_id);

	if ($ret_id <= 0) {
		$GLOBALS['err']->add($GLOBALS['_LANG']['invalid_order_id']);
		return false;
	}

	$order = return_order_info($ret_id);
	return $order;
}

function get_user_merge($user_id)
{
	include_once ROOT_PATH . 'includes/lib_order.php';
	$sql = 'SELECT order_sn FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE user_id  = \'' . $user_id . '\' ') . order_query_sql('unprocessed') . 'AND extension_code = \'\' ' . ' ORDER BY add_time DESC';
	$list = $GLOBALS['db']->GetCol($sql);
	$merge = array();

	foreach ($list as $val) {
		$merge[$val] = $val;
	}

	return $merge;
}

function merge_user_order($from_order, $to_order, $user_id = 0)
{
	if (0 < $user_id) {
		if (0 < strlen($to_order)) {
			$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_sn = \'' . $to_order . '\'');
			$order_user = $GLOBALS['db']->getOne($sql);

			if ($order_user != $user_id) {
				$GLOBALS['err']->add($GLOBALS['_LANG']['no_priv']);
			}
		}
		else {
			$GLOBALS['err']->add($GLOBALS['_LANG']['order_sn_empty']);
			return false;
		}
	}

	$result = merge_order($from_order, $to_order);

	if ($result === true) {
		return true;
	}
	else {
		$GLOBALS['err']->add($result);
		return false;
	}
}

function return_to_cart($order_id, $rec_id = array())
{
	if (!empty($_SESSION['user_id'])) {
		$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
		$sess = '';
	}
	else {
		$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
		$sess = real_cart_mac_ip();
	}

	$basic_number = array();
	$sql = 'SELECT rec_id,goods_id, product_id,goods_number, goods_attr, parent_id, model_attr, goods_attr_id, ' . ' goods_price, ru_id, warehouse_id, area_id, model_attr, shopping_fee ' . ' FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\' AND is_gift = 0 AND extension_code <> \'package_buy\'') . ' ORDER BY parent_id ASC';
	$res = $GLOBALS['db']->query($sql);
	$time = gmtime();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$sql = 'SELECT g.goods_sn, g.goods_name, ' . 'IF(g.model_inventory < 1, g.goods_number, IF(g.model_inventory < 2, wg.region_number, wag.region_number)) AS goods_number, ' . ' g.market_price, ' . ('IF(g.is_promote = 1 AND \'' . $time . '\' BETWEEN g.promote_start_date AND g.promote_end_date, g.promote_price, g.shop_price) AS goods_price,') . 'g.is_real, g.extension_code, g.is_alone_sale, g.goods_type ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' as g ' . ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg on g.goods_id = wg.goods_id' . ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag on g.goods_id = wag.goods_id' . (' WHERE g.goods_id = \'' . $row['goods_id'] . '\' ') . ' AND is_delete = 0 LIMIT 1';
		$goods = $GLOBALS['db']->getRow($sql);
		if (empty($goods) || !empty($rec_id) && !in_array($row['rec_id'], $rec_id)) {
			continue;
		}

		if ($row['product_id']) {
			$order_goods_product_id = $row['product_id'];

			if ($row['model_attr'] == 1) {
				$products_table = 'products_warehouse';
			}
			else if ($row['model_attr'] == 2) {
				$products_table = 'products_area';
			}
			else {
				$products_table = 'products';
			}

			$sql = 'SELECT product_number from ' . $GLOBALS['ecs']->table($products_table) . ('where product_id=\'' . $order_goods_product_id . '\'');
			$product_number = $GLOBALS['db']->getOne($sql);
		}

		if ($GLOBALS['_CFG']['use_storage'] == 1 && ($row['product_id'] ? $product_number < $row['goods_number'] : $goods['goods_number'] < $row['goods_number'])) {
			if ($goods['goods_number'] == 0 || $product_number === 0) {
				continue;
			}
			else if ($row['product_id']) {
				$row['goods_number'] = $product_number;
			}
			else {
				$row['goods_number'] = $goods['goods_number'];
			}
		}

		$sql = 'SELECT goods_number FROM' . $GLOBALS['ecs']->table('cart') . ' ' . 'WHERE ' . $sess_id . 'AND goods_id = \'' . $row['goods_id'] . '\' ' . 'AND rec_type = \'' . CART_GENERAL_GOODS . '\' LIMIT 1';
		$temp_number = $GLOBALS['db']->getOne($sql);
		$row['goods_number'] += $temp_number;
		$attr_array = empty($row['goods_attr_id']) ? array() : explode(',', $row['goods_attr_id']);
		$goods['goods_price'] = get_final_price($row['goods_id'], $row['goods_number'], true, $attr_array);
		$return_goods = array('goods_id' => $row['goods_id'], 'goods_sn' => addslashes($goods['goods_sn']), 'goods_name' => addslashes($goods['goods_name']), 'market_price' => $goods['market_price'], 'product_id' => $row['product_id'], 'goods_price' => $goods['goods_price'], 'warehouse_id' => $row['warehouse_id'], 'area_id' => $row['area_id'], 'ru_id' => $row['ru_id'], 'model_attr' => $row['model_attr'], 'shopping_fee' => $row['shopping_fee'], 'goods_number' => $row['goods_number'], 'goods_attr' => empty($row['goods_attr']) ? '' : addslashes($row['goods_attr']), 'goods_attr_id' => empty($row['goods_attr_id']) ? '' : $row['goods_attr_id'], 'is_real' => $goods['is_real'], 'extension_code' => addslashes($goods['extension_code']), 'parent_id' => '0', 'is_gift' => '0', 'rec_type' => CART_GENERAL_GOODS);

		if (0 < $row['parent_id']) {
			$sql = 'SELECT goods_id ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id = \'' . $row['parent_id'] . '\' ') . ' AND is_delete = 0 AND is_on_sale = 1 AND is_alone_sale = 1 LIMIT 1';
			$parent = $GLOBALS['db']->getRow($sql);

			if ($parent) {
				$sql = 'SELECT goods_price ' . 'FROM ' . $GLOBALS['ecs']->table('group_goods') . (' WHERE parent_id = \'' . $row['parent_id'] . '\' ') . (' AND goods_id = \'' . $row['goods_id'] . '\' LIMIT 1');
				$fitting_price = $GLOBALS['db']->getOne($sql);

				if ($fitting_price) {
					$return_goods['parent_id'] = $row['parent_id'];
					$return_goods['goods_price'] = $fitting_price;
					$return_goods['goods_number'] = $basic_number[$row['parent_id']];
				}
			}
		}
		else {
			$basic_number[$row['goods_id']] = $row['goods_number'];
		}

		$sql = 'SELECT goods_id ' . 'FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . (' AND goods_id = \'' . $return_goods['goods_id'] . '\' ') . (' AND goods_attr = \'' . $return_goods['goods_attr'] . '\' ') . (' AND parent_id = \'' . $return_goods['parent_id'] . '\' ') . ' AND is_gift = 0 ' . ' AND rec_type = \'' . CART_GENERAL_GOODS . '\'';
		$cart_goods = $GLOBALS['db']->getOne($sql);

		if (empty($cart_goods)) {
			$return_goods['session_id'] = $sess;
			$return_goods['user_id'] = $_SESSION['user_id'];
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $return_goods, 'INSERT');
		}
		else {
			$set = '';

			if (0 < $return_goods['goods_price']) {
				$set .= ', goods_price = \'' . $return_goods['goods_price'] . '\' ';
			}

			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('cart') . ' SET ' . 'goods_number = \'' . $return_goods['goods_number'] . '\' ' . $set . 'WHERE ' . $sess_id . 'AND goods_id = \'' . $return_goods['goods_id'] . '\' ' . 'AND rec_type = \'' . CART_GENERAL_GOODS . '\' LIMIT 1';
			$GLOBALS['db']->query($sql);
		}
	}

	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . ' AND is_gift = 1';
	$GLOBALS['db']->query($sql);
	return true;
}

function save_order_address($address, $user_id)
{
	$GLOBALS['err']->clean();
	empty($address['consignee']) && $GLOBALS['err']->add($GLOBALS['_LANG']['consigness_empty']);
	empty($address['address']) && $GLOBALS['err']->add($GLOBALS['_LANG']['address_empty']);
	$address['order_id'] == 0 && $GLOBALS['err']->add($GLOBALS['_LANG']['order_id_empty']);

	if (empty($address['email'])) {
		$GLOBALS['err']->add($GLOBALS['email_empty']);
	}
	else if (!is_email($address['email'])) {
		$GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_invalid'], $address['email']));
	}

	if (0 < $GLOBALS['err']->error_no) {
		return false;
	}

	$sql = 'SELECT user_id, order_status FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $address['order_id'] . '\'';
	$row = $GLOBALS['db']->getRow($sql);

	if ($row) {
		if (0 < $user_id && $user_id != $row['user_id']) {
			$GLOBALS['err']->add($GLOBALS['_LANG']['no_priv']);
			return false;
		}

		if ($row['order_status'] != OS_UNCONFIRMED) {
			$GLOBALS['err']->add($GLOBALS['_LANG']['require_unconfirmed']);
			return false;
		}

		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $address, 'UPDATE', 'order_id = \'' . $address['order_id'] . '\'');
		return true;
	}
	else {
		$GLOBALS['err']->add($GLOBALS['_LANG']['order_exist']);
		return false;
	}
}

function get_user_bouns_list($user_id, $num = 10, $start = 0)
{
	$sql = 'SELECT u.bonus_sn, u.order_id, b.type_name, b.type_money, b.min_goods_amount, b.use_start_date, b.use_end_date ' . ' FROM ' . $GLOBALS['ecs']->table('user_bonus') . ' AS u ,' . $GLOBALS['ecs']->table('bonus_type') . ' AS b' . ' WHERE u.bonus_type_id = b.type_id AND u.user_id = \'' . $user_id . '\'';
	$res = $GLOBALS['db']->selectLimit($sql, $num, $start);
	$arr = array();
	$day = getdate();
	$cur_date = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if (empty($row['order_id'])) {
			if ($cur_date < $row['use_start_date']) {
				$row['status'] = $GLOBALS['_LANG']['not_start'];
			}
			else if ($row['use_end_date'] < $cur_date) {
				$row['status'] = $GLOBALS['_LANG']['overdue'];
			}
			else {
				$row['status'] = $GLOBALS['_LANG']['not_use'];
			}
		}
		else {
			$row['status'] = '<a href="user.php?act=order_detail&order_id=' . $row['order_id'] . '" >' . $GLOBALS['_LANG']['had_use'] . '</a>';
		}

		$row['use_startdate'] = local_date($GLOBALS['_CFG']['date_format'], $row['use_start_date']);
		$row['use_enddate'] = local_date($GLOBALS['_CFG']['date_format'], $row['use_end_date']);
		$arr[] = $row;
	}

	return $arr;
}

function get_user_group_buy($user_id, $num = 10, $start = 0)
{
	return true;
}

function get_group_buy_detail($user_id, $group_buy_id)
{
	return true;
}

function deleteRepeat($array)
{
	$_card_sn_record = array();

	foreach ($array as $_k => $_v) {
		foreach ($_v['info'] as $__k => $__v) {
			if (in_array($__v['card_sn'], $_card_sn_record)) {
				unset($array[$_k]['info'][$__k]);
			}
			else {
				array_push($_card_sn_record, $__v['card_sn']);
			}
		}
	}

	return $array;
}

function order_shop_info($order_id)
{
	$goods_id = $GLOBALS['db']->getOne(' SELECT og.goods_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . (' AS og ON oi.order_id = og.order_id WHERE oi.order_id = \'' . $order_id . '\' '));
	$sql = ' SELECT g.user_id FROM ' . $GLOBALS['ecs']->table('goods') . (' AS g WHERE g.goods_id = \'' . $goods_id . '\' ');
	$ru_id = $GLOBALS['db']->getOne($sql);
	$row['shop_name'] = get_shop_name($ru_id, 1);
	$row['ru_id'] = $ru_id;
	$build_uri = array('urid' => $ru_id, 'append' => $row['shop_name']);
	$domain_url = get_seller_domain_url($ru_id, $build_uri);
	$row['shop_url'] = $domain_url['domain_name'];
	return $row;
}

function get_goods_cause($goods_cause, $chargeoff_status = -1, $is_settlement = 0)
{
	$arr = array();

	if ($goods_cause) {
		$goods_cause = explode(',', $goods_cause);

		foreach ($goods_cause as $key => $row) {
			if (0 < $chargeoff_status || $is_settlement == 1) {
				if (!in_array($row, array(1, 3))) {
					$arr[$key]['cause'] = $row;
					$arr[$key]['lang'] = $GLOBALS['_LANG']['order_return_type'][$row];

					if ($key == 0) {
						$arr[$key]['is_checked'] = 1;
					}
					else {
						$arr[$key]['is_checked'] = 0;
					}
				}
			}
			else {
				$arr[$key]['cause'] = $row;
				$arr[$key]['lang'] = $GLOBALS['_LANG']['order_return_type'][$row];

				if ($key == 0) {
					$arr[$key]['is_checked'] = 1;
				}
				else {
					$arr[$key]['is_checked'] = 0;
				}
			}
		}
	}

	return $arr;
}

function invoice_list($user_id = 0, $record_count, $page, $pagesize = 10)
{
	require_once 'includes/cls_pager.php';
	$config = array('header' => $GLOBALS['_LANG']['pager_2'], 'prev' => '<i><<</i>' . $GLOBALS['_LANG']['page_prev'], 'next' => '' . $GLOBALS['_LANG']['page_next'] . '<i>>></i>', 'first' => $GLOBALS['_LANG']['page_first'], 'last' => $GLOBALS['_LANG']['page_last']);
	$user_order = new Pager($record_count, $pagesize, '', $user_id, 0, $page, 'user_inv_gotoPage', 1, 0, 0, $config);
	$limit = $user_order->limit;
	$pager = $user_order->fpage(array(0, 4, 5, 6, 9));
	$sql = ' SELECT o.order_id, o.mobile, o.order_sn, o.add_time, o.inv_payee, o.inv_content, o.invoice_type, g.user_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON o.order_id = og.order_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = og.goods_id ' . (' WHERE o.user_id = \'' . $user_id . '\' GROUP BY o.order_id ORDER BY o.add_time DESC ') . $limit;
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	if ($res) {
		foreach ($res as $k => $val) {
			$arr[$k] = $val;
			$ru_id = $val['user_id'];
			$arr[$k]['ru_id'] = $ru_id;
			$arr[$k]['add_time'] = local_date('Y-m-d H:i:s', $val['add_time']);

			if ($val['invoice_type'] == 1) {
				$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('users_vat_invoices_info') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
				$res = $GLOBALS['db']->getRow($sql);
				$arr[$k]['vat_info'] = $res;
			}

			$arr[$k]['invoice_type'] = $val['invoice_type'] ? '增值税发票' : '普通发票';
			if ($val['inv_content'] == '' && $val['invoice_type'] == 0) {
				$arr[$k]['inv_status'] = '无法开票';
				$arr[$k]['invoice_type'] = '';
			}
			else {
				$arr[$k]['inv_status'] = $arr[$k]['invoice_type'];
			}

			$arr[$k]['mobile'] = $val['mobile'];
			$arr[$k]['shop_name'] = get_shop_name($ru_id, 1);
			$sql = 'SELECT kf_type, kf_ww, kf_qq  from ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' WHERE ru_id=\'' . $ru_id . '\'');
			$basic_info = $GLOBALS['db']->getRow($sql);
			$kf_ww_one = '';
			$kf_qq_one = '';

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
				$ru_id = $orders['ru_id'];
			}

			$shop_information = get_shop_name($ru_id);
			$arr[$k]['is_IM'] = $shop_information['is_IM'];

			if ($ru_id == 0) {
				if ($GLOBALS['db']->getOne('SELECT kf_im_switch FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = 0', true)) {
					$arr[$k]['is_dsc'] = true;
				}
				else {
					$arr[$k]['is_dsc'] = false;
				}
			}
			else {
				$arr[$k]['is_dsc'] = false;
			}

			$arr[$k]['kf_type'] = $basic_info['kf_type'];
			$arr[$k]['kf_ww'] = $kf_ww_one;
			$arr[$k]['kf_qq'] = $kf_qq_one;
			$arr[$k]['order_goods'] = get_order_goods_toInfo($val['order_id']);
			$arr[$k]['order_goods_count'] = count($row['order_goods']);
		}
	}

	$order_list = array('order_list' => $arr, 'pager' => $pager, 'record_count' => $record_count);
	return $order_list;
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
