<?php
//zend by 商创网络 Q Q:123456    禁止倒卖 一经发现停止任何服务
function get_user_log()
{
	$result = get_filter();

	if ($result === false) {
		$where = ' WHERE 1 ';
		$filter = array();
		$filter['id'] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '0';
		$where .= ' AND user_id = \'' . $filter['id'] . '\'';
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('users_log') . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT log_id,user_id,change_time,change_type,ip_address,change_city,logon_service,admin_id FROM' . $GLOBALS['ecs']->table('users_log') . ($where . '  ORDER BY change_time DESC');
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$arr = array();

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		if (0 < $rows['change_time']) {
			$rows['change_time'] = local_date('Y-m-d H:i:s', $rows['change_time']);
		}

		if (0 < $rows['admin_id']) {
			$sql = 'SELECT user_name FROM' . $GLOBALS['ecs']->table('admin_user') . ' WHERE user_id = \'' . $rows['admin_id'] . '\'';
			$rows['admin_name'] = '管理员：' . $GLOBALS['db']->getOne($sql);
		}
		else {
			$rows['admin_name'] = '会员操作';
		}

		$arr[] = $rows;
	}

	return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function user_date($result)
{
	if (empty($result)) {
		return i('没有符合您要求的数据！^_^');
	}

	$data = i('编号,会员名称,商家名称,联系方式,邮件地址,是否已验证,可用资金,冻结资金,等级积分,消费积分,注册日期' . "\n");
	$count = count($result);

	for ($i = 0; $i < $count; $i++) {
		if (empty($result[$i]['ru_name'])) {
			$result[$i]['ru_name'] = '商城会员';
		}

		$data .= i($result[$i]['user_id']) . ',' . i($result[$i]['user_name']) . ',' . i($result[$i]['ru_name']) . ',' . i($result[$i]['mobile_phone']) . ',' . i($result[$i]['email']) . ',' . i($result[$i]['is_validated']) . ',' . i($result[$i]['user_money']) . ',' . i($result[$i]['frozen_money']) . ',' . i($result[$i]['rank_points']) . ',' . i($result[$i]['pay_points']) . ',' . i($result[$i]['reg_time']) . "\n";
	}

	return $data;
}

function i($strInput)
{
	return iconv('utf-8', 'gb2312', $strInput);
}

function user_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['rank'] = empty($_REQUEST['rank']) ? 0 : intval($_REQUEST['rank']);
		$filter['pay_points_gt'] = empty($_REQUEST['pay_points_gt']) ? 0 : intval($_REQUEST['pay_points_gt']);
		$filter['pay_points_lt'] = empty($_REQUEST['pay_points_lt']) ? 0 : intval($_REQUEST['pay_points_lt']);
		$filter['mobile_phone'] = empty($_REQUEST['mobile_phone']) ? 0 : addslashes($_REQUEST['mobile_phone']);
		$filter['email'] = empty($_REQUEST['email']) ? 0 : addslashes($_REQUEST['email']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'u.user_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$ex_where = ' WHERE 1 ';
		$filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_where = '';
		$store_search_where = '';

		if ($filter['store_search'] != 0) {
			if ($ru_id == 0) {
				if ($_REQUEST['store_type']) {
					$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
				}

				if ($filter['store_search'] == 1) {
					$ex_where .= ' AND u.user_id = \'' . $filter['merchant_id'] . '\' ';
				}
				else if ($filter['store_search'] == 2) {
					$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
				}
				else if ($filter['store_search'] == 3) {
					$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
				}

				if (1 < $filter['store_search']) {
					$ex_where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . (' WHERE msi.user_id = u.user_id ' . $store_where . ') > 0 ');
				}
			}
		}

		if ($filter['keywords']) {
			$ex_where .= ' AND (u.user_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\' OR u.nick_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\')';
		}

		if ($filter['mobile_phone']) {
			$ex_where .= ' AND u.mobile_phone = \'' . $filter['mobile_phone'] . '\'';
		}

		if ($filter['email']) {
			$ex_where .= ' AND u.email = \'' . $filter['email'] . '\'';
		}

		if ($filter['rank']) {
			$sql = 'SELECT min_points, max_points, special_rank FROM ' . $GLOBALS['ecs']->table('user_rank') . (' WHERE rank_id = \'' . $filter['rank'] . '\'');
			$row = $GLOBALS['db']->getRow($sql);

			if (0 < $row['special_rank']) {
				$ex_where .= ' AND u.user_rank = \'' . $filter['rank'] . '\' ';
			}
			else {
				$ex_where .= ' AND u.rank_points >= ' . intval($row['min_points']) . ' AND u.rank_points < ' . intval($row['max_points']);
			}
		}

		if ($filter['pay_points_gt']) {
			$ex_where .= ' AND u.pay_points < \'' . $filter['pay_points_gt'] . '\' ';
		}

		if ($filter['pay_points_lt']) {
			$ex_where .= ' AND u.pay_points >= \'' . $filter['pay_points_lt'] . '\' ';
		}

		$filter['record_count'] = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . $ex_where);
		$filter = page_and_size($filter);
		$sql = 'SELECT u.user_rank,u.user_id, u.user_name, u.nick_name, u.mobile_phone, u.email, u.is_validated, u.user_money, u.frozen_money, u.rank_points, u.pay_points, u.reg_time,rank_points ' . ' FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . $ex_where . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$user_list = $GLOBALS['db']->getAll($sql);
	$count = count($user_list);

	for ($i = 0; $i < $count; $i++) {
		$user_list[$i]['ru_name'] = get_shop_name($user_list[$i]['user_id'], 1);
		$user_list[$i]['reg_time'] = local_date($GLOBALS['_CFG']['date_format'], $user_list[$i]['reg_time']);

		if (0 < $user_list[$i]['user_rank']) {
			$rank_where = ' rank_id = \'' . $user_list[$i]['user_rank'] . '\'';
		}
		else {
			$rank_where = 'min_points <= ' . $user_list[$i]['rank_points'] . ' ORDER BY min_points DESC';
		}

		$user_list[$i]['rank_name'] = $GLOBALS['db']->getOne('SELECT rank_name FROM' . $GLOBALS['ecs']->table('user_rank') . ' WHERE ' . $rank_where);

		if ($user_list[$i]['rank_name'] == '') {
			$user_list[$i]['rank_name'] = '无等级';
		}
	}

	$arr = array('user_list' => $user_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function user_update($user_id, $args)
{
	if (empty($args) || empty($user_id)) {
		return false;
	}

	return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('users'), $args, 'update', 'user_id=\'' . $user_id . '\'');
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

if ($_REQUEST['act'] == 'list') {
	admin_priv('users_manage');
	$smarty->assign('menu_select', array('action' => '08_members', 'current' => '03_users_list'));
	$sql = 'SELECT rank_id, rank_name, min_points FROM ' . $ecs->table('user_rank') . ' ORDER BY min_points ASC ';
	$rs = $db->query($sql);
	$ranks = array();

	while ($row = $db->FetchRow($rs)) {
		$ranks[$row['rank_id']] = $row['rank_name'];
	}

	$smarty->assign('user_ranks', $ranks);
	$smarty->assign('ur_here', $_LANG['03_users_list']);
	$smarty->assign('action_link', array('text' => $_LANG['04_users_add'], 'href' => 'users.php?act=add'));
	$smarty->assign('action_link2', array('text' => $_LANG['12_users_export'], 'href' => 'javascript:download_userlist();'));
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$user_list = user_list();
	$smarty->assign('user_list', $user_list['user_list']);
	$smarty->assign('filter', $user_list['filter']);
	$smarty->assign('record_count', $user_list['record_count']);
	$smarty->assign('page_count', $user_list['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');
	assign_query_info();
	$smarty->display('users_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$user_list = user_list();
	$smarty->assign('user_list', $user_list['user_list']);
	$smarty->assign('filter', $user_list['filter']);
	$smarty->assign('record_count', $user_list['record_count']);
	$smarty->assign('page_count', $user_list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($user_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('users_list.dwt'), '', array('filter' => $user_list['filter'], 'page_count' => $user_list['page_count']));
}
else if ($_REQUEST['act'] == 'add') {
	admin_priv('users_manage');
	$user = array('rank_points' => $_CFG['register_points'], 'pay_points' => $_CFG['register_points'], 'sex' => 0, 'credit_line' => 0);
	$sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
	$extend_info_list = $db->getAll($sql);
	$smarty->assign('extend_info_list', $extend_info_list);
	$smarty->assign('passwd_questions', $_LANG['passwd_questions']);
	$smarty->assign('ur_here', $_LANG['04_users_add']);
	$smarty->assign('action_link', array('text' => $_LANG['11_users_add'], 'href' => 'mc_user.php'));
	$smarty->assign('action_link2', array('text' => $_LANG['03_users_list'], 'href' => 'users.php?act=list'));
	$smarty->assign('form_action', 'insert');
	$smarty->assign('user', $user);
	$smarty->assign('special_ranks', get_rank_list(true));
	$select_date = array();
	$select_date['year'] = range(1956, date(Y));
	$select_date['month'] = range(1, 12);
	$select_date['day'] = range(1, 31);
	$smarty->assign('select_date', $select_date);
	assign_query_info();
	$smarty->display('user_add.dwt');
}
else if ($_REQUEST['act'] == 'insert') {
	admin_priv('users_manage');
	$username = empty($_POST['username']) ? '' : trim($_POST['username']);
	$password = empty($_POST['password']) ? '' : trim($_POST['password']);
	$email = empty($_POST['email']) ? '' : trim($_POST['email']);
	$sex = empty($_POST['sex']) ? 0 : intval($_POST['sex']);
	$sex = in_array($sex, array(0, 1, 2)) ? $sex : 0;
	$birthday = $_POST['birthdayYear'] . '-' . $_POST['birthdayMonth'] . '-' . $_POST['birthdayDay'];
	$rank = empty($_POST['user_rank']) ? 0 : intval($_POST['user_rank']);
	$credit_line = empty($_POST['credit_line']) ? 0 : floatval($_POST['credit_line']);
	$user_registerMode = array('email' => $email, 'register_mode' => 0);
	$sel_question = empty($_POST['sel_question']) ? '' : compile_str($_POST['sel_question']);
	$passwd_answer = isset($_POST['passwd_answer']) ? compile_str(trim($_POST['passwd_answer'])) : '';
	$users = &init_users();

	if (!$users->add_user($username, $password, $user_registerMode)) {
		if ($users->error == ERR_INVALID_USERNAME) {
			$msg = $_LANG['username_invalid'];
		}
		else if ($users->error == ERR_USERNAME_NOT_ALLOW) {
			$msg = $_LANG['username_not_allow'];
		}
		else if ($users->error == ERR_USERNAME_EXISTS) {
			$msg = $_LANG['username_exists'];
		}
		else if ($users->error == ERR_INVALID_EMAIL) {
			$msg = $_LANG['email_invalid'];
		}
		else if ($users->error == ERR_EMAIL_NOT_ALLOW) {
			$msg = $_LANG['email_not_allow'];
		}
		else if ($users->error == ERR_EMAIL_EXISTS) {
			$msg = $_LANG['email_exists'];
		}

		sys_msg($msg, 1);
	}

	if (!empty($GLOBALS['_CFG']['register_points'])) {
		log_account_change($_SESSION['user_id'], 0, 0, $GLOBALS['_CFG']['register_points'], $GLOBALS['_CFG']['register_points'], $_LANG['register_points']);
	}

	$sql = 'SELECT id FROM ' . $ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';
	$fields_arr = $db->getAll($sql);
	$extend_field_str = '';
	$user_id_arr = $users->get_profile_by_name($username);

	foreach ($fields_arr as $val) {
		$extend_field_index = 'extend_field' . $val['id'];

		if (!empty($_POST[$extend_field_index])) {
			$temp_field_content = 100 < strlen($_POST[$extend_field_index]) ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];
			$extend_field_str .= ' (\'' . $user_id_arr['user_id'] . '\', \'' . $val['id'] . '\', \'' . $temp_field_content . '\'),';
		}
	}

	$extend_field_str = substr($extend_field_str, 0, -1);

	if ($extend_field_str) {
		$sql = 'INSERT INTO ' . $ecs->table('reg_extend_info') . ' (`user_id`, `reg_field_id`, `content`) VALUES' . $extend_field_str;
		$db->query($sql);
	}

	$other = array();
	$other['credit_line'] = $credit_line;
	$other['user_rank'] = $rank;
	$other['sex'] = $sex;
	$other['birthday'] = $birthday;
	$other['reg_time'] = local_strtotime(local_date('Y-m-d H:i:s'));
	$other['msn'] = isset($_POST['extend_field1']) ? htmlspecialchars(trim($_POST['extend_field1'])) : '';
	$other['qq'] = isset($_POST['extend_field2']) ? htmlspecialchars(trim($_POST['extend_field2'])) : '';
	$other['office_phone'] = isset($_POST['extend_field3']) ? htmlspecialchars(trim($_POST['extend_field3'])) : '';
	$other['home_phone'] = isset($_POST['extend_field4']) ? htmlspecialchars(trim($_POST['extend_field4'])) : '';
	$other['mobile_phone'] = isset($_POST['extend_field5']) ? htmlspecialchars(trim($_POST['extend_field5'])) : '';
	$other['passwd_question'] = $sel_question;
	$other['passwd_answer'] = $passwd_answer;

	if (!empty($other['mobile_phone'])) {
		$sql = 'SELECT user_id FROM ' . $ecs->table('users') . (' WHERE mobile_phone = \'' . $other['mobile_phone'] . '\'');

		if (0 < $db->getOne($sql)) {
			sys_msg('该手机号已存在！', 1);
		}
	}

	$db->autoExecute($ecs->table('users'), $other, 'UPDATE', 'user_name = \'' . $username . '\'');
	admin_log($_POST['username'], 'add', 'users');
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'users.php?act=list');
	sys_msg(sprintf($_LANG['add_success'], htmlspecialchars(stripslashes($_POST['username']))), 0, $link);
}
else if ($_REQUEST['act'] == 'edit') {
	admin_priv('users_manage');
	$user_id = isset($_GET['id']) && !empty($_GET['id']) ? intval($_GET['id']) : 0;
	$sql = 'SELECT u.user_name, u.sex, u.birthday, u.pay_points, u.rank_points, u.user_rank , ' . 'u.user_money, u.frozen_money, u.credit_line, u.parent_id, u2.user_name as parent_username, u.qq, u.msn, u.office_phone, u.home_phone, u.mobile_phone, ' . 'u.question, u.answer, r.front_of_id_card, r.reverse_of_id_card ' . ' FROM ' . $ecs->table('users') . ' u LEFT JOIN ' . $ecs->table('users') . ' u2 ON u.parent_id = u2.user_id ' . ' LEFT JOIN ' . $ecs->table('users_real') . ' r ON u.user_id = r.user_id ' . (' WHERE u.user_id = \'' . $user_id . '\'');
	$row = $db->GetRow($sql);
	$row['user_name'] = addslashes($row['user_name']);
	$users = &init_users();
	$user = $users->get_user_info($row['user_name']);
	$sql = "SELECT u.user_id, u.sex, u.birthday, u.pay_points, u.rank_points, u.user_rank , u.user_money, u.frozen_money, u.credit_line, u.parent_id, u2.user_name as parent_username, u.qq, u.msn,\r\n            u.office_phone, u.home_phone, u.mobile_phone," . 'u.passwd_question, u.passwd_answer, r.front_of_id_card, r.reverse_of_id_card' . ' FROM ' . $ecs->table('users') . ' u LEFT JOIN ' . $ecs->table('users') . ' u2 ON u.parent_id = u2.user_id ' . ' LEFT JOIN ' . $ecs->table('users_real') . ' r ON u.user_id = r.user_id ' . (' WHERE u.user_id = \'' . $user_id . '\'');
	$row = $db->GetRow($sql);

	if ($row) {
		$user['user_id'] = $row['user_id'];
		$user['sex'] = $row['sex'];
		$user['birthday'] = date($row['birthday']);

		if ($user['birthday']) {
			$birthday = explode('-', $user['birthday']);
			$user['year'] = intval($birthday[0]);
			$user['month'] = intval($birthday[1]);
			$user['day'] = intval($birthday[2]);
		}

		$user['pay_points'] = $row['pay_points'];
		$user['rank_points'] = $row['rank_points'];
		$user['user_rank'] = $row['user_rank'];
		$user['user_money'] = $row['user_money'];
		$user['frozen_money'] = $row['frozen_money'];
		$user['credit_line'] = $row['credit_line'];
		$user['formated_user_money'] = price_format($row['user_money']);
		$user['formated_frozen_money'] = price_format($row['frozen_money']);
		$user['parent_id'] = $row['parent_id'];
		$user['parent_username'] = $row['parent_username'];
		$user['qq'] = $row['qq'];
		$user['msn'] = $row['msn'];
		$user['office_phone'] = $row['office_phone'];
		$user['home_phone'] = $row['home_phone'];
		$user['mobile_phone'] = $row['mobile_phone'];
		$user['passwd_question'] = $row['passwd_question'];
		$user['passwd_answer'] = $row['passwd_answer'];
		$user['front_of_id_card'] = get_image_path(0, $row['front_of_id_card']);
		$user['reverse_of_id_card'] = get_image_path(0, $row['reverse_of_id_card']);
	}
	else {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'users.php?act=list');
		sys_msg($_LANG['username_invalid'], 0, $links);
	}

	$smarty->assign('passwd_questions', $_LANG['passwd_questions']);
	$sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
	$extend_info_list = $db->getAll($sql);
	$sql = 'SELECT reg_field_id, content ' . 'FROM ' . $ecs->table('reg_extend_info') . (' WHERE user_id = ' . $user['user_id']);
	$extend_info_arr = $db->getAll($sql);
	$temp_arr = array();

	foreach ($extend_info_arr as $val) {
		$temp_arr[$val['reg_field_id']] = $val['content'];
	}

	foreach ($extend_info_list as $key => $val) {
		switch ($val['id']) {
		case 1:
			$extend_info_list[$key]['content'] = $user['msn'];
			break;

		case 2:
			$extend_info_list[$key]['content'] = $user['qq'];
			break;

		case 3:
			$extend_info_list[$key]['content'] = $user['office_phone'];
			break;

		case 4:
			$extend_info_list[$key]['content'] = $user['home_phone'];
			break;

		case 5:
			$extend_info_list[$key]['content'] = $user['mobile_phone'];
			break;

		default:
			$extend_info_list[$key]['content'] = empty($temp_arr[$val['id']]) ? '' : $temp_arr[$val['id']];
		}
	}

	$smarty->assign('extend_info_list', $extend_info_list);
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$smarty->assign('affiliate', $affiliate);
	empty($affiliate) && ($affiliate = array());

	if (empty($affiliate['config']['separate_by'])) {
		$affdb = array();
		$num = count($affiliate['item']);
		$up_uid = '\'' . $_GET['id'] . '\'';

		for ($i = 1; $i <= $num; $i++) {
			$count = 0;

			if ($up_uid) {
				$sql = 'SELECT user_id FROM ' . $ecs->table('users') . (' WHERE parent_id IN(' . $up_uid . ')');
				$query = $db->query($sql);
				$up_uid = '';

				while ($rt = $db->fetch_array($query)) {
					$up_uid .= $up_uid ? ',\'' . $rt['user_id'] . '\'' : '\'' . $rt['user_id'] . '\'';
					$count++;
				}
			}

			$affdb[$i]['num'] = $count;
		}

		if (0 < $affdb[1]['num']) {
			$smarty->assign('affdb', $affdb);
		}
	}

	$smarty->assign('full_page', 1);
	$smarty->assign('action_link2', array('text' => $_LANG['03_users_list'], 'href' => 'users.php?act=list'));
	$select_date = array();
	$select_date['year'] = range(1956, date(Y));
	$select_date['month'] = range(1, 12);
	$select_date['day'] = range(1, 31);
	$smarty->assign('select_date', $select_date);
	$smarty->assign('user_id', $user['user_id']);
	assign_query_info();
	$smarty->assign('ur_here', $_LANG['users_edit']);
	$smarty->assign('user', $user);
	$smarty->assign('form_action', 'update');
	$smarty->assign('special_ranks', get_rank_list(true));
	$smarty->display('user_list_edit.dwt');
}
else if ($_REQUEST['act'] == 'update') {
	admin_priv('users_manage');
	$username = empty($_POST['username']) ? '' : trim($_POST['username']);
	$password = empty($_POST['password']) ? '' : trim($_POST['password']);
	$email = empty($_POST['email']) ? '' : trim($_POST['email']);
	$sex = empty($_POST['sex']) ? 0 : intval($_POST['sex']);
	$sex = in_array($sex, array(0, 1, 2)) ? $sex : 0;
	$birthdayDay = isset($_POST['birthdayDay']) ? intval($_POST['birthdayDay']) : 0;
	$birthdayDay = strlen($birthdayDay) == 1 ? '0' . $birthdayDay : $birthdayDay;
	$birthdayMonth = isset($_POST['birthdayMonth']) ? intval($_POST['birthdayMonth']) : 0;
	$birthdayMonth = strlen($birthdayMonth) == 1 ? '0' . $birthdayMonth : $birthdayMonth;
	$birthday = $_POST['birthdayYear'] . '-' . $birthdayMonth . '-' . $birthdayDay;
	$rank = empty($_POST['user_rank']) ? 0 : intval($_POST['user_rank']);
	$credit_line = empty($_POST['credit_line']) ? 0 : floatval($_POST['credit_line']);
	$id = empty($_POST['id']) ? 0 : intval($_POST['id']);
	$sel_question = empty($_POST['sel_question']) ? '' : compile_str($_POST['sel_question']);
	$passwd_answer = isset($_POST['passwd_answer']) ? compile_str(trim($_POST['passwd_answer'])) : '';
	$users = &init_users();

	if (!$users->edit_user(array('user_id' => $id, 'username' => $username, 'password' => $password, 'email' => $email, 'gender' => $sex, 'bday' => $birthday), 1)) {
		if ($users->error == ERR_EMAIL_EXISTS) {
			$msg = $_LANG['email_exists'];
		}
		else {
			$msg = $_LANG['edit_user_failed'];
		}

		sys_msg($msg, 1);
	}

	if (!empty($password)) {
		$sql = 'UPDATE ' . $ecs->table('users') . 'SET `ec_salt`=\'0\' WHERE user_name= \'' . $username . '\'';
		$db->query($sql);
	}

	$sql = 'SELECT id FROM ' . $ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';
	$fields_arr = $db->getAll($sql);
	$user_id_arr = $users->get_profile_by_name($username);
	$user_id = $user_id_arr['user_id'];

	foreach ($fields_arr as $val) {
		$extend_field_index = 'extend_field' . $val['id'];

		if (isset($_POST[$extend_field_index])) {
			$temp_field_content = 100 < strlen($_POST[$extend_field_index]) ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];
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

	$other = array();
	$other['credit_line'] = $credit_line;
	$other['user_rank'] = $rank;
	$other['msn'] = isset($_POST['extend_field1']) ? htmlspecialchars(trim($_POST['extend_field1'])) : '';
	$other['qq'] = isset($_POST['extend_field2']) ? htmlspecialchars(trim($_POST['extend_field2'])) : '';
	$other['office_phone'] = isset($_POST['extend_field3']) ? htmlspecialchars(trim($_POST['extend_field3'])) : '';
	$other['home_phone'] = isset($_POST['extend_field4']) ? htmlspecialchars(trim($_POST['extend_field4'])) : '';
	$other['mobile_phone'] = isset($_POST['extend_field5']) ? htmlspecialchars(trim($_POST['extend_field5'])) : '';
	$other['passwd_question'] = $sel_question;
	$other['passwd_answer'] = $passwd_answer;

	if (!empty($other['mobile_phone'])) {
		$sql = 'SELECT user_id FROM ' . $ecs->table('users') . (' WHERE mobile_phone = \'' . $other['mobile_phone'] . '\' AND user_id != \'' . $id . '\'');

		if (0 < $db->getOne($sql)) {
			sys_msg('该手机号已存在！', 1);
		}
	}

	$old_user['old_email'] = empty($_POST['old_email']) ? '' : trim($_POST['old_email']);
	$old_user['old_user_rank'] = empty($_POST['user_rank']) ? 0 : intval($_POST['user_rank']);
	$old_user['old_sex'] = empty($_POST['old_sex']) ? 0 : intval($_POST['old_sex']);
	$old_user['old_birthday'] = empty($_POST['old_birthday']) ? '' : trim($_POST['old_birthday']);
	$old_user['old_credit_line'] = empty($_POST['old_credit_line']) ? 0 : floatval($_POST['old_credit_line']);
	$old_user['old_msn'] = isset($_POST['old_extend_field1']) ? htmlspecialchars(trim($_POST['old_extend_field1'])) : '';
	$old_user['old_qq'] = isset($_POST['old_extend_field2']) ? htmlspecialchars(trim($_POST['old_extend_field2'])) : '';
	$old_user['old_office_phone'] = isset($_POST['old_extend_field3']) ? htmlspecialchars(trim($_POST['old_extend_field3'])) : '';
	$old_user['old_home_phone'] = isset($_POST['old_extend_field4']) ? htmlspecialchars(trim($_POST['old_extend_field4'])) : '';
	$old_user['old_mobile_phone'] = isset($_POST['old_extend_field5']) ? htmlspecialchars(trim($_POST['old_extend_field5'])) : '';
	$old_user['old_passwd_answer'] = isset($_POST['old_passwd_answer']) ? compile_str(trim($_POST['old_passwd_answer'])) : '';
	$old_user['old_sel_question'] = empty($_POST['old_sel_question']) ? '' : compile_str($_POST['old_sel_question']);
	$old_user['password'] = $password;
	require_once ROOT_PATH . 'includes/lib_ipCity.php';
	$new_user = $other;
	$new_user['email'] = $email;
	$new_user['sex'] = $sex;
	$new_user['birthday'] = $birthday;
	users_log_change_type($old_user, $new_user, $id);
	$db->autoExecute($ecs->table('users'), $other, 'UPDATE', 'user_name = \'' . $username . '\'');
	admin_log($username, 'edit', 'users');
	$links[0]['text'] = $_LANG['goto_list'];
	$links[0]['href'] = 'users.php?act=list&' . list_link_postfix();
	$links[1]['text'] = $_LANG['go_back'];
	$links[1]['href'] = 'javascript:history.back()';
	sys_msg($_LANG['update_success'], 0, $links);
}

if ($_REQUEST['act'] == 'toggle_is_validated') {
	check_authz_json('users_manage');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);

	if (user_update($id, array('is_validated' => $val)) != false) {
		clear_cache_files();
		make_json_result($val);
	}
	else {
		make_json_error($db->error());
	}
}
else if ($_REQUEST['act'] == 'batch_remove') {
	admin_priv('users_drop');

	if (isset($_POST['checkboxes'])) {
		$priv_str = $db->getOne('SELECT action_list FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['admin_id'] . '\''));

		if ($priv_str != 'all') {
			foreach ($_POST['checkboxes'] as $key => $val) {
				$sql = 'SELECT id FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' WHERE ru_id = \'' . $val . '\'');
				$shopinfo = $GLOBALS['db']->getOne($sql);

				if (!empty($shopinfo)) {
					unset($_POST['checkboxes'][$key]);
				}
			}
		}

		$sql = 'SELECT user_name FROM ' . $ecs->table('users') . ' WHERE user_id ' . db_create_in($_POST['checkboxes']);
		$col = $db->getCol($sql);
		$usernames = implode(',', addslashes_deep($col));
		$count = count($col);
		$users = &init_users();
		$users->remove_user($col);
		admin_log($usernames, 'batch_remove', 'users');
		$lnk[] = array('text' => $_LANG['go_back'], 'href' => 'users.php?act=list');
		sys_msg(sprintf($_LANG['batch_remove_success'], $count), 0, $lnk);
	}
	else {
		$lnk[] = array('text' => $_LANG['go_back'], 'href' => 'users.php?act=list');
		sys_msg($_LANG['no_select_user'], 0, $lnk);
	}
}
else if ($_REQUEST['act'] == 'main_user') {
	require_once ROOT_PATH . '/includes/lib_base.php';
	$data = read_static_cache('main_user_str');

	if ($data === false) {
		include_once ROOT_PATH . 'includes/cls_transport.php';
		$ecs_version = VERSION;
		$ecs_lang = $_CFG['lang'];
		$ecs_release = RELEASE;
		$php_ver = PHP_VERSION;
		$mysql_ver = $db->version();
		$ecs_charset = strtoupper(EC_CHARSET);
		$scount = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('seller_shopinfo'));
		$no_main_order = ' WHERE 1 AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' AS oi2 WHERE oi2.main_order_id = o.order_id) = 0 ';
		$sql = 'SELECT COUNT(*) AS oCount, IFNULL(SUM(order_amount), 0) AS oAmount FROM ' . $ecs->table('order_info') . ' AS o ' . $no_main_order;
		$order['stats'] = $db->getRow($sql);
		$ocount = $order['stats']['oCount'];
		$oamount = $order['stats']['oAmount'];
		$goods['total'] = $db->GetOne('SELECT COUNT(*) FROM ' . $ecs->table('goods') . ' WHERE is_delete = 0 AND is_alone_sale = 1 AND is_real = 1');
		$gcount = $goods['total'];
		$ecs_user = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('users'));
		$ecs_template = $db->getOne('SELECT value FROM ' . $ecs->table('shop_config') . ' WHERE code = \'template\'');
		$style = $db->getOne('SELECT value FROM ' . $ecs->table('shop_config') . ' WHERE code = \'stylename\'');

		if ($style == '') {
			$style = '0';
		}

		$ecs_style = $style;
		$shop_url = urlencode($ecs->url());
		$httpData = array('domain' => $ecs->get_domain(), 'url' => urldecode($shop_url), 'ver' => $ecs_version, 'lang' => $ecs_lang, 'release' => $ecs_release, 'php_ver' => $php_ver, 'mysql_ver' => $mysql_ver, 'ocount' => $ocount, 'oamount' => $oamount, 'gcount' => $gcount, 'scount' => $scount, 'charset' => $ecs_charset, 'usecount' => $ecs_user, 'template' => $ecs_template, 'style' => $ecs_style);
		$Http = new Http();
		$Http->doPost('http://ecshop.ecmoban.com/dsc_checkver.php', $httpData);
		write_static_cache('main_user_str', $httpData);
	}
}
else if ($_REQUEST['act'] == 'remove') {
	admin_priv('users_drop');
	$user_id = intval($_GET['id']);
	$sql = 'SELECT user_name FROM ' . $ecs->table('users') . (' WHERE user_id = \'' . $user_id . '\'');
	$username = $db->getOne($sql);
	$sql = 'SELECT shop_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . (' WHERE user_id = \'' . $user_id . '\'');

	if ($GLOBALS['db']->getOne($sql)) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'users.php?act=list');
		sys_msg(sprintf($_LANG['remove_seller_fail'], $username, $user_id), 0, $link);
	}

	$users = &init_users();
	$users->remove_user($username);
	admin_log(addslashes($username), 'remove', 'users');
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'users.php?act=list');
	sys_msg(sprintf($_LANG['remove_success'], $username), 0, $link);
}
else if ($_REQUEST['act'] == 'address_list') {
	$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
	$sql = 'SELECT a.*, c.region_name AS country_name, p.region_name AS province, ct.region_name AS city_name, d.region_name AS district_name ' . ' FROM ' . $ecs->table('user_address') . ' as a ' . ' LEFT JOIN ' . $ecs->table('region') . ' AS c ON c.region_id = a.country ' . ' LEFT JOIN ' . $ecs->table('region') . ' AS p ON p.region_id = a.province ' . ' LEFT JOIN ' . $ecs->table('region') . ' AS ct ON ct.region_id = a.city ' . ' LEFT JOIN ' . $ecs->table('region') . ' AS d ON d.region_id = a.district ' . (' WHERE user_id=\'' . $id . '\'');
	$address = $db->getAll($sql);
	$smarty->assign('address', $address);
	$smarty->assign('user_id', $id);
	$smarty->assign('form_action', 'address_list');
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', $_LANG['address_list']);

	if (0 < $id) {
		$smarty->assign('action_link2', array('text' => $_LANG['address_list'], 'href' => 'users.php?act=list'));
	}

	assign_query_info();
	$smarty->display('user_list_edit.dwt');
}
else if ($_REQUEST['act'] == 'remove_parent') {
	admin_priv('users_manage');
	$sql = 'UPDATE ' . $ecs->table('users') . ' SET parent_id = 0 WHERE user_id = \'' . $_GET['id'] . '\'';
	$db->query($sql);
	$sql = 'SELECT user_name FROM ' . $ecs->table('users') . ' WHERE user_id = \'' . $_GET['id'] . '\'';
	$username = $db->getOne($sql);
	admin_log(addslashes($username), 'edit', 'users');
	$link[] = array('text' => $_LANG['go_back'], 'href' => 'users.php?act=list');
	sys_msg(sprintf($_LANG['update_success'], $username), 0, $link);
}
else if ($_REQUEST['act'] == 'aff_list') {
	admin_priv('users_manage');
	$smarty->assign('ur_here', $_LANG['03_users_list']);
	$auid = isset($_GET['auid']) && !empty($_GET['auid']) ? intval($_GET['auid']) : 0;
	$user_list['user_list'] = array();
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$smarty->assign('affiliate', $affiliate);
	empty($affiliate) && ($affiliate = array());
	$num = count($affiliate['item']);
	$up_uid = '\'' . $auid . '\'';
	$all_count = 0;

	for ($i = 1; $i <= $num; $i++) {
		$count = 0;

		if ($up_uid) {
			$sql = 'SELECT user_id FROM ' . $ecs->table('users') . (' WHERE parent_id IN(' . $up_uid . ')');
			$query = $db->query($sql);
			$up_uid = '';

			while ($rt = $db->fetch_array($query)) {
				$up_uid .= $up_uid ? ',\'' . $rt['user_id'] . '\'' : '\'' . $rt['user_id'] . '\'';
				$count++;
			}
		}

		$all_count += $count;

		if ($count) {
			$sql = 'SELECT user_id, user_name, \'' . $i . '\' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time ' . ' FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id IN(' . $up_uid . ')') . ' ORDER by level, user_id';
			$user_list['user_list'] = array_merge($user_list['user_list'], $db->getAll($sql));
		}
	}

	$temp_count = count($user_list['user_list']);

	for ($i = 0; $i < $temp_count; $i++) {
		$user_list['user_list'][$i]['reg_time'] = local_date($_CFG['date_format'], $user_list['user_list'][$i]['reg_time']);
	}

	$user_list['record_count'] = $all_count;
	$smarty->assign('user_list', $user_list['user_list']);
	$smarty->assign('record_count', $user_list['record_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('action_link', array('text' => $_LANG['back_note'], 'href' => 'users.php?act=edit&id=' . $auid));
	assign_query_info();
	$smarty->display('affiliate_list.dwt');
}
else if ($_REQUEST['act'] == 'export') {
	$filename = date('YmdHis') . '.csv';
	header('Content-type:text/csv');
	header('Content-Disposition:attachment;filename=' . $filename);
	header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
	header('Expires:0');
	header('Pragma:public');
	$user_list = user_list();
	echo user_date($user_list['user_list']);
	exit();
}
else if ($_REQUEST['act'] == 'users_log') {
	$smarty->assign('ur_here', $_LANG['users_log']);
	$user_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$user_log = get_user_log();
	$smarty->assign('user_log', $user_log['list']);
	$smarty->assign('filter', $user_log['filter']);
	$smarty->assign('record_count', $user_log['record_count']);
	$smarty->assign('page_count', $user_log['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('user_id', $user_id);
	$smarty->display('users_log.dwt');
}
else if ($_REQUEST['act'] == 'users_log_query') {
	$user_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$user_log = get_user_log();
	$smarty->assign('user_log', $user_log['list']);
	$smarty->assign('filter', $user_log['filter']);
	$smarty->assign('record_count', $user_log['record_count']);
	$smarty->assign('page_count', $user_log['page_count']);
	$smarty->assign('user_id', $user_id);
	make_json_result($smarty->fetch('users_log.dwt'), '', array('filter' => $user_log['filter'], 'page_count' => $user_log['page_count']));
}
else if ($_REQUEST['act'] == 'batch_log') {
	$user_id = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

	if (empty($_POST['checkboxes'])) {
		sys_msg($_LANG['no_record_selected']);
	}
	else {
		$ids = $_POST['checkboxes'];
		$sql = 'DELETE FROM ' . $ecs->table('users_log') . ' WHERE log_id ' . db_create_in($ids) . (' AND user_id = \'' . $user_id . '\'');
		$db->query($sql);
		clear_cache_files();
		$link[] = array('text' => '返回', 'href' => 'users.php?act=users_log&id=' . $user_id);
		sys_msg($_LANG['batch_drop_ok'], '', $link);
	}
}

?>
