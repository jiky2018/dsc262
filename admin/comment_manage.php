<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_comment_list($ru_id)
{
	$filter['keywords'] = empty($_REQUEST['keywords']) ? 0 : addslashes(trim($_REQUEST['keywords']));
	if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
		$filter['keywords'] = json_str_iconv($filter['keywords']);
	}

	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : addslashes(trim($_REQUEST['sort_by']));
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : addslashes(trim($_REQUEST['sort_order']));
	$filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;
	$sql = 'select user_id from ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' where shoprz_brandName LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\' OR shopNameSuffix LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\'';
	$user_id = $GLOBALS['db']->getOne($sql);

	if (empty($user_id)) {
		$user_id = 0;
	}

	$where_user = '';

	if (0 < $user_id) {
		$where_user = ' OR ru_id in(' . $user_id . ')';
	}

	$where = '1';
	$where .= !empty($filter['keywords']) ? ' AND (content LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\' ' . $where_user . ') ' : '';
	$filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
	$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
	$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
	$store_where = '';
	$store_search_where = '';

	if (-1 < $filter['store_search']) {
		if ($ru_id == 0) {
			if (0 < $filter['store_search']) {
				if ($_REQUEST['store_type']) {
					$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
				}

				if ($filter['store_search'] == 1) {
					$where .= ' AND c.ru_id = \'' . $filter['merchant_id'] . '\' ';
				}
				else if ($filter['store_search'] == 2) {
					$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
				}
				else if ($filter['store_search'] == 3) {
					$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
				}

				if (1 < $filter['store_search']) {
					$where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . (' WHERE msi.user_id = c.ru_id ' . $store_where . ') > 0 ');
				}
			}
			else {
				$where .= ' AND c.ru_id = 0';
			}
		}
	}

	if (0 < $ru_id) {
		$where .= ' and ru_id = \'' . $ru_id . '\' ';
	}

	$where .= ' AND (parent_id = 0 OR (parent_id > 0 AND user_id > 0))';
	$where .= !empty($filter['seller_list']) ? ' AND ru_id > 0 ' : ' AND ru_id = 0 ';
	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' AS c ' . (' WHERE ' . $where);
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$arr = array();
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') . ' AS c ' . (' WHERE ' . $where . ' ') . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		if ($row['comment_type'] == 2) {
			$sql = 'SELECT goods_name FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id=\'' . $row['id_value'] . '\'');
			$goods_name = $GLOBALS['db']->getOne($sql);
			$row['title'] = $goods_name . '<br/><font style=\'color:#1b9ad5;\'>(' . $GLOBALS['_LANG']['goods_user_reply'] . ')</font>';
		}
		else if ($row['comment_type'] == 3) {
			$sql = 'SELECT goods_name FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id=\'' . $row['id_value'] . '\'');
			$row['title'] = $GLOBALS['db']->getOne($sql);
		}
		else {
			$sql = $row['comment_type'] == 0 ? 'SELECT goods_name FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id=\'' . $row['id_value'] . '\'') : 'SELECT title FROM ' . $GLOBALS['ecs']->table('article') . (' WHERE article_id=\'' . $row['id_value'] . '\'');
			$row['title'] = $GLOBALS['db']->getOne($sql);
		}

		$row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
		$row['ru_name'] = get_shop_name($row['ru_id'], 1);
		$arr[] = $row;
	}

	$filter['keywords'] = stripslashes($filter['keywords']);
	$arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_single_list($ru_id)
{
	$filter['keywords'] = empty($_REQUEST['keywords']) ? 0 : addslashes(trim($_REQUEST['keywords']));
	if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
		$filter['keywords'] = json_str_iconv($filter['keywords']);
	}

	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 's.addtime' : addslashes(trim($_REQUEST['sort_by']));
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : addslashes(trim($_REQUEST['sort_order']));
	$where = !empty($filter['keywords']) ? ' AND s.order_sn LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\' ' : '';

	if (0 < $ru_id) {
		$where .= ' AND g.user_id = \'' . $ru_id . '\'';
	}

	$sql = 'SELECT s.* FROM ' . $GLOBALS['ecs']->table('single') . ' as s, ' . $GLOBALS['ecs']->table('goods') . ' as g ' . (' WHERE s.goods_id = g.goods_id AND 1=1 ' . $where . ' ');
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$arr = array();
	$sql = 'SELECT s.*, g.user_id as ru_id FROM ' . $GLOBALS['ecs']->table('single') . ' as s, ' . $GLOBALS['ecs']->table('goods') . ' as g ' . (' WHERE s.goods_id = g.goods_id AND 1=1 ' . $where . ' ') . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$sql = 'SELECT goods_name FROM ' . $GLOBALS['ecs']->table('goods') . (' WHERE goods_id=\'' . $row['goods_id'] . '\'');
		$row['goods_name'] = $GLOBALS['db']->getOne($sql);
		$row['addtime'] = local_date($GLOBALS['_CFG']['time_format'], $row['addtime']);
		$row['order_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['order_time']);
		$row['shop_name'] = get_shop_name($row['ru_id'], 1);
		$arr[] = $row;
	}

	$filter['keywords'] = stripslashes($filter['keywords']);
	$arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('comment'), $db, 'comment_id', 'user_name');

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$user_action_list = get_user_action_list($_SESSION['admin_id']);

if ($_REQUEST['act'] == 'list') {
	admin_priv('comment_priv');
	$comment_edit_delete = get_merchants_permissions($user_action_list, 'comment_edit_delete');
	$smarty->assign('comment_edit_delete', $comment_edit_delete);
	$smarty->assign('ur_here', $_LANG['05_comment_manage']);
	$smarty->assign('full_page', 1);
	$list = get_comment_list($adminru['ru_id']);
	$smarty->assign('comment_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	self_seller(BASENAME($_SERVER['PHP_SELF']));
	assign_query_info();
	$smarty->display('comment_list.dwt');
}

if ($_REQUEST['act'] == 'single_list') {
	admin_priv('single_manage');
	require_once ROOT_PATH . 'includes/lib_order.php';
	$smarty->assign('ur_here', $_LANG['single_manage']);
	$smarty->assign('full_page', 1);
	$single_edit_delete = get_merchants_permissions($user_action_list, 'single_edit_delete');
	$smarty->assign('single_edit_delete', $single_edit_delete);
	$list = get_single_list($adminru['ru_id']);
	$smarty->assign('single_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('single_list.htm');
}

if ($_REQUEST['act'] == 'query') {
	admin_priv('comment_priv');
	$list = get_comment_list($adminru['ru_id']);
	$smarty->assign('comment_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$comment_edit_delete = get_merchants_permissions($user_action_list, 'comment_edit_delete');
	$smarty->assign('comment_edit_delete', $comment_edit_delete);
	make_json_result($smarty->fetch('comment_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else if ($_REQUEST['act'] == 'toggle_status') {
	check_authz_json('comment_priv');
	$comment_id = intval($_POST['id']);
	$status = intval($_POST['val']);

	if ($exc->edit('status = \'' . $status . '\'', $comment_id)) {
		$sql = 'SELECT id_value FROM ' . $ecs->table('comment') . (' WHERE comment_id = \'' . $comment_id . '\'');
		$goods_id = $db->getOne($sql, true);

		if ($status == 1) {
			$set = 'goods_comment_number = goods_comment_number + 1';
		}
		else {
			$set = 'goods_comment_number = goods_comment_number - 1';
		}

		$sql = 'UPDATE ' . $ecs->table('intelligent_weight') . (' SET ' . $set . ' WHERE goods_id = \'' . $goods_id . '\'');
		$db->query($sql);
		clear_cache_files();
		make_json_result($status);
	}
}

if ($_REQUEST['act'] == 'single_query') {
	admin_priv('single_manage');
	$list = get_single_list($adminru['ru_id']);
	$smarty->assign('single_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$single_edit_delete = get_merchants_permissions($user_action_list, 'single_edit_delete');
	$smarty->assign('single_edit_delete', $single_edit_delete);
	make_json_result($smarty->fetch('single_list.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

if ($_REQUEST['act'] == 'single_reply') {
	admin_priv('single_manage');
	$single_info = array();
	$reply_info = array();
	$id_value = array();
	$sql = $sql = 'SELECT * FROM ' . $ecs->table('single') . (' WHERE single_id = \'' . $_REQUEST['id'] . '\'');
	$single_info = $db->getRow($sql);
	$single_info['addtime'] = local_date($_CFG['time_format'], $single_info['addtime']);
	$id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$send_ok = isset($_REQUEST['send_ok']) && !empty($_REQUEST['send_ok']) ? addslashes($_REQUEST['send_ok']) : '';
	$sql = $sql = 'SELECT id, img_file, cont_desc FROM ' . $ecs->table('single_sun_images') . (' WHERE single_id = \'' . $id . '\' order by id DESC');
	$single_img = $db->getAll($sql);
	$img_list = array();

	foreach ($single_img as $key => $gallery_img) {
		$img_list[$key]['id'] = $gallery_img['id'];
		$img_list[$key]['img_file'] = $gallery_img['img_file'];
		$img_list[$key]['cont_desc'] = $gallery_img['cont_desc'];
	}

	$sql = 'SELECT user_name, email FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['admin_id'] . '\'');
	$admin_info = $db->getRow($sql);
	$smarty->assign('msg', $single_info);
	$smarty->assign('single_img', $img_list);
	$smarty->assign('admin_info', $admin_info);
	$smarty->assign('send_fail', !empty($send_ok));
	$smarty->assign('ur_here', $_LANG['single_info']);
	$smarty->assign('action_link', array('text' => $_LANG['single_manage'], 'href' => 'comment_manage.php?act=single_list'));
	$single_edit_delete = get_merchants_permissions($user_action_list, 'single_edit_delete');
	$smarty->assign('single_edit_delete', $single_edit_delete);
	assign_query_info();
	$smarty->display('single_info.htm');
}
else if ($_REQUEST['act'] == 'drop_single_image') {
	check_authz_json('single_manage');
	$img_id = empty($_REQUEST['img_id']) ? 0 : intval($_REQUEST['img_id']);
	$sql = 'SELECT img_file  ' . ' FROM ' . $GLOBALS['ecs']->table('single_sun_images') . (' WHERE id = \'' . $img_id . '\'');
	$row = $GLOBALS['db']->getRow($sql);
	if ($row['img_file'] != '' && is_file('../' . $row['img_file'])) {
		@unlink('../' . $row['img_file']);
	}

	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('single_sun_images') . (' WHERE id = \'' . $img_id . '\'');
	$GLOBALS['db']->query($sql);
	clear_cache_files();
	make_json_result($img_id);
}

if ($_REQUEST['act'] == 'single_check') {
	admin_priv('single_manage');
	$order_id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$goods_id = isset($_REQUEST['goods_id']) && !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
	$integ = isset($_REQUEST['integ']) && !empty($_REQUEST['integ']) ? floatval($_REQUEST['integ']) : 0;
	$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

	if ($_REQUEST['check'] == 'allow') {
		$sql = 'UPDATE ' . $ecs->table('order_goods') . (' SET is_single = 2 WHERE order_id = \'' . $order_id . '\' AND goods_id = \'' . $goods_id . '\'');
		$db->query($sql);
		$sql = 'UPDATE ' . $ecs->table('single') . (' SET is_audit = 1, integ=\'' . $integ . '\' WHERE order_id = \'' . $order_id . '\'');
		$db->query($sql);

		if ($integ) {
			log_account_change($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = $integ, $change_desc = '晒单奖励');
		}

		clear_cache_files();
		ecs_header("Location: comment_manage.php?act=single_list\n");
		exit();
	}
	else {
		$sql = 'UPDATE ' . $ecs->table('order_goods') . (' SET is_single = 3 WHERE order_id = \'' . $order_id . '\' AND goods_id = \'' . $goods_id . '\'');
		$db->query($sql);
		$sql = 'UPDATE ' . $ecs->table('single') . (' SET is_audit = 0, integ=\'-' . $integ . '\' WHERE order_id = \'' . $order_id . '\'');
		$db->query($sql);

		if (!empty($_REQUEST['integ'])) {
			log_account_change($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0 - $integ, $change_desc = '晒单禁止扣除积分');
		}

		clear_cache_files();
		ecs_header("Location: comment_manage.php?act=single_list\n");
		exit();
	}
}

if ($_REQUEST['act'] == 'reply') {
	admin_priv('comment_priv');
	$comment_id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$send_ok = isset($_REQUEST['send_ok']) && !empty($_REQUEST['send_ok']) ? addslashes($_REQUEST['send_ok']) : '';
	$comment_info = array();
	$reply_info = array();
	$id_value = array();
	$sql = 'SELECT * FROM ' . $ecs->table('comment') . (' WHERE comment_id = \'' . $comment_id . '\'');
	$comment_info = $db->getRow($sql);
	$comment_info['content'] = str_replace('\\r\\n', '<br />', htmlspecialchars($comment_info['content']));
	$comment_info['content'] = nl2br(str_replace('\\n', '<br />', $comment_info['content']));
	$comment_info['add_time'] = local_date($_CFG['time_format'], $comment_info['add_time']);
	$sql = 'SELECT img_thumb FROM ' . $ecs->table('comment_img') . (' WHERE comment_id = \'' . $comment_id . '\'');
	$img_list = $db->getAll($sql);
	$comment_info['img_list'] = $img_list;
	$sql = 'SELECT user_name, email FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['admin_id'] . '\'');
	$admin_info = $db->getRow($sql);
	$sql = 'SELECT * FROM ' . $ecs->table('comment') . (' WHERE parent_id = \'' . $comment_id . '\'') . ' AND single_id = 0 AND dis_id = 0 AND user_id = \'' . $_SESSION['admin_id'] . '\' AND user_name = \'' . $admin_info['user_name'] . '\' AND ru_id = \'' . $adminru['ru_id'] . '\' ';
	$reply_info = $db->getRow($sql);

	if (empty($reply_info)) {
		$reply_info['content'] = '';
		$reply_info['add_time'] = '';
	}
	else {
		$reply_info['content'] = nl2br(htmlspecialchars($reply_info['content']));
		$reply_info['add_time'] = local_date($_CFG['time_format'], $reply_info['add_time']);
	}

	if ($comment_info['comment_type'] == 0) {
		$sql = 'SELECT goods_name FROM ' . $ecs->table('goods') . (' WHERE goods_id = \'' . $comment_info['id_value'] . '\'');
		$id_value = $db->getOne($sql);
	}
	else {
		$sql = 'SELECT title FROM ' . $ecs->table('article') . (' WHERE article_id=\'' . $comment_info['id_value'] . '\'');
		$id_value = $db->getOne($sql);
	}

	$smarty->assign('msg', $comment_info);
	$smarty->assign('admin_info', $admin_info);
	$smarty->assign('reply_info', $reply_info);
	$smarty->assign('id_value', $id_value);
	$smarty->assign('send_fail', !empty($send_ok));
	$smarty->assign('ur_here', $_LANG['comment_info']);
	$smarty->assign('action_link', array('text' => $_LANG['05_comment_manage'], 'href' => 'comment_manage.php?act=list'));
	assign_query_info();
	$smarty->display('comment_info.dwt');
}

if ($_REQUEST['act'] == 'action') {
	admin_priv('comment_priv');
	$email = isset($_REQUEST['email']) && !empty($_REQUEST['email']) ? addslashes($_REQUEST['email']) : '';
	$remail = isset($_REQUEST['remail']) && !empty($_REQUEST['remail']) ? addslashes($_REQUEST['remail']) : '';
	$user_name = isset($_REQUEST['user_name']) && !empty($_REQUEST['user_name']) ? addslashes($_REQUEST['user_name']) : '';
	$content = isset($_REQUEST['content']) && !empty($_REQUEST['content']) ? addslashes($_REQUEST['content']) : '';
	$send_email_notice = isset($_REQUEST['send_email_notice']) && !empty($_REQUEST['send_email_notice']) ? addslashes($_REQUEST['send_email_notice']) : '';
	$comment_type = isset($_REQUEST['comment_type']) && !empty($_REQUEST['comment_type']) ? intval($_REQUEST['comment_type']) : 3;
	$id_value = isset($_REQUEST['id_value']) && !empty($_REQUEST['id_value']) ? intval($_REQUEST['id_value']) : 0;
	$sql = 'SELECT user_id, ru_id FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['admin_id'] . '\'');
	$admin_info = $db->getRow($sql);
	$ip = real_ip();
	$comment_id = isset($_REQUEST['comment_id']) && !empty($_REQUEST['comment_id']) ? intval($_REQUEST['comment_id']) : 0;
	$comment_info = $db->getRow('SELECT comment_id,ru_id FROM ' . $ecs->table('comment') . (' WHERE comment_id = \'' . $comment_id . '\' AND ru_id=\'') . $adminru['ru_id'] . '\'');
	$sql = 'SELECT comment_id,content,parent_id,ru_id FROM ' . $ecs->table('comment') . (' WHERE parent_id = \'' . $comment_info['comment_id'] . '\' AND single_id = 0 AND dis_id = 0 AND user_id = \'') . $admin_info['user_id'] . '\' AND ru_id =\'' . $comment_info['ru_id'] . '\'';
	$reply_info = $db->getRow($sql);
	if (!empty($reply_info['content']) && $adminru['ru_id'] == $comment_info['ru_id']) {
		$sql = 'UPDATE ' . $ecs->table('comment') . ' SET ' . ('email     = \'' . $email . '\', ') . ('user_name = \'' . $user_name . '\', ') . ('content   = \'' . $content . '\', ') . 'add_time  =  \'' . gmtime() . '\', ' . ('ip_address= \'' . $ip . '\', ') . 'status    = 0' . ' WHERE comment_id = \'' . $reply_info['comment_id'] . '\'';
	}
	else if ($adminru['ru_id'] == $comment_info['ru_id']) {
		$sql = 'INSERT INTO ' . $ecs->table('comment') . ' (comment_type, id_value, email, user_name , ' . 'content, add_time, ip_address, status, parent_id, user_id, ru_id) ' . ('VALUES(\'' . $comment_type . '\', \'' . $id_value . '\',\'' . $email . '\', ') . ('\'' . $_SESSION['admin_name'] . '\',\'' . $content . '\',\'') . gmtime() . '\', ' . ('\'' . $ip . '\', \'0\', \'' . $comment_id . '\', \'' . $admin_info['user_id'] . '\', \'' . $adminru['ru_id'] . '\')');
	}
	else {
		sys_msg($_LANG['priv_error']);
	}

	$db->query($sql);
	$sql = 'UPDATE ' . $ecs->table('comment') . (' SET status = 1 WHERE comment_id = \'' . $comment_id . '\'');
	$db->query($sql);
	if (!empty($send_email_notice) || isset($remail) && !empty($remail)) {
		$sql = 'SELECT user_name, email, content ' . 'FROM ' . $ecs->table('comment') . (' WHERE comment_id =\'' . $comment_id . '\' LIMIT 1');
		$comment_info = $db->getRow($sql);
		$template = get_mail_template('recomment');
		$smarty->assign('user_name', $comment_info['user_name']);
		$smarty->assign('recomment', $content);
		$smarty->assign('comment', $comment_info['content']);
		$smarty->assign('shop_name', '<a href=\'' . $ecs->url() . '\'>' . $_CFG['shop_name'] . '</a>');
		$smarty->assign('send_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
		$content = $smarty->fetch('str:' . $template['template_content']);

		if (send_mail($comment_info['user_name'], $comment_info['email'], $template['template_subject'], $content, $template['is_html'])) {
			$send_ok = 0;
		}
		else {
			$send_ok = 1;
		}
	}

	clear_cache_files();
	admin_log(addslashes($_LANG['reply']), 'edit', 'users_comment');
	ecs_header('Location: comment_manage.php?act=reply&id=' . $_REQUEST['comment_id'] . '&send_ok=' . $send_ok . "\n");
	exit();
}

if ($_REQUEST['act'] == 'check') {
	admin_priv('comment_priv');
	$comment_id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if ($_REQUEST['check'] == 'allow') {
		$sql = 'UPDATE ' . $ecs->table('comment') . (' SET status = 1 WHERE comment_id = \'' . $comment_id . '\'');
		$db->query($sql);
		$sql = 'SELECT id_value FROM ' . $ecs->table('comment') . (' WHERE comment_id = \'' . $comment_id . '\'');
		$goods_id = $db->getOne($sql);
		$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('comment') . (' WHERE id_value = \'' . $goods_id . '\' AND comment_type = 0 AND status = 1 AND parent_id = 0 ');
		$count = $db->getOne($sql);
		$sql = 'UPDATE ' . $ecs->table('goods') . (' SET comments_number = \'' . $count . '\' WHERE goods_id = \'' . $goods_id . '\'');
		$db->query($sql);
		clear_cache_files();
		ecs_header('Location: comment_manage.php?act=reply&id=' . $comment_id . "\n");
		exit();
	}
	else {
		$sql = 'UPDATE ' . $ecs->table('comment') . (' SET status = 0 WHERE comment_id = \'' . $comment_id . '\'');
		$db->query($sql);
		$sql = 'SELECT id_value FROM ' . $ecs->table('comment') . (' WHERE comment_id = \'' . $comment_id . '\'');
		$goods_id = $db->getOne($sql);
		$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('comment') . (' WHERE id_value = \'' . $goods_id . '\' AND comment_type = 0 AND status = 1 AND parent_id = 0 ');
		$count = $db->getOne($sql);
		$sql = 'UPDATE ' . $ecs->table('goods') . (' SET comments_number = \'' . $count . '\' WHERE goods_id = \'' . $goods_id . '\'');
		$db->query($sql);
		clear_cache_files();
		ecs_header('Location: comment_manage.php?act=reply&id=' . $comment_id . "\n");
		exit();
	}
}
else if ($_REQUEST['act'] == 'single_remove') {
	check_authz_json('single_manage');
	$single_id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$sql = 'SELECT order_id FROM ' . $ecs->table('single') . (' WHERE single_id = \'' . $single_id . '\'');
	$res = $db->getRow($sql);
	$order_id = $res['order_id'];
	$db->query('UPDATE ' . $ecs->table('order_info') . ' SET is_single=\'4\'' . (' WHERE order_id = \'' . $order_id . '\''));
	$sql = 'DELETE FROM ' . $ecs->table('single') . (' WHERE single_id = \'' . $single_id . '\'');
	$res = $db->query($sql);

	if ($res) {
		$db->query('DELETE FROM ' . $ecs->table('goods_gallery') . (' WHERE single_id = \'' . $single_id . '\''));
	}

	admin_log('', 'single_remove', 'ads');
	$url = 'comment_manage.php?act=single_query&' . str_replace('act=single_remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('comment_priv');
	$comment_id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$sql = 'SELECT comment_img, img_thumb FROM ' . $ecs->table('comment_img') . (' WHERE comment_id = \'' . $comment_id . '\'');
	$img = $db->getAll($sql);

	if ($img) {
		for ($i = 0; $i < count($img); $i++) {
			@unlink(ROOT_PATH . $img[$i]['comment_img']);
			@unlink(ROOT_PATH . $img[$i]['img_thumb']);
			get_oss_del_file(array($img[$i]['comment_img'], $img[$i]['img_thumb']));
		}
	}

	$sql = 'DELETE FROM ' . $ecs->table('comment_img') . (' WHERE comment_id = \'' . $comment_id . '\'');
	$res = $db->query($sql);
	$sql = 'SELECT id_value FROM ' . $ecs->table('comment') . (' WHERE comment_id = \'' . $comment_id . '\'');
	$goods_id = $db->getOne($sql);
	$sql = 'DELETE FROM ' . $ecs->table('comment') . (' WHERE comment_id = \'' . $comment_id . '\'');
	$res = $db->query($sql);

	if ($res) {
		if ($goods_id) {
			$sql = 'UPDATE ' . $ecs->table('goods') . (' SET comments_number = comments_number - 1 WHERE goods_id = \'' . $goods_id . '\'');
			$res = $db->query($sql);
			$sql = 'UPDATE ' . $ecs->table('intelligent_weight') . (' SET goods_comment_number = goods_comment_number - 1 WHERE goods_id = \'' . $goods_id . '\'');
			$db->query($sql);
		}

		$db->query('DELETE FROM ' . $ecs->table('comment') . (' WHERE parent_id = \'' . $comment_id . '\''));
	}

	admin_log('', 'remove', 'ads');
	$url = 'comment_manage.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

if ($_REQUEST['act'] == 'batch') {
	admin_priv('comment_priv');
	$action = isset($_POST['sel_action']) ? trim($_POST['sel_action']) : 'deny';

	if (isset($_POST['checkboxes'])) {
		switch ($action) {
		case 'remove':
			$sql = 'SELECT comment_img, img_thumb FROM ' . $ecs->table('comment_img') . ' WHERE ' . db_create_in($_POST['checkboxes'], 'comment_id');
			$img = $db->getAll($sql);

			if ($img) {
				for ($i = 0; $i < count($img); $i++) {
					@unlink(ROOT_PATH . $img[$i]['comment_img']);
					@unlink(ROOT_PATH . $img[$i]['img_thumb']);
					get_oss_del_file(array($img[$i]['comment_img'], $img[$i]['img_thumb']));
				}
			}

			$db->query('DELETE FROM ' . $ecs->table('comment_img') . ' WHERE ' . db_create_in($_POST['checkboxes'], 'comment_id'));
			$db->query('DELETE FROM ' . $ecs->table('comment') . ' WHERE ' . db_create_in($_POST['checkboxes'], 'comment_id'));
			$db->query('DELETE FROM ' . $ecs->table('comment') . ' WHERE ' . db_create_in($_POST['checkboxes'], 'parent_id'));
			break;

		case 'allow':
			$db->query('UPDATE ' . $ecs->table('comment') . ' SET status = 1  WHERE ' . db_create_in($_POST['checkboxes'], 'comment_id'));
			break;

		case 'deny':
			$db->query('UPDATE ' . $ecs->table('comment') . ' SET status = 0  WHERE ' . db_create_in($_POST['checkboxes'], 'comment_id'));
			break;

		default:
			break;
		}

		clear_cache_files();
		$action = $action == 'remove' ? 'remove' : 'edit';
		admin_log('', $action, 'adminlog');
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'comment_manage.php?act=list');
		sys_msg(sprintf($_LANG['batch_drop_success'], count($_POST['checkboxes'])), 0, $link);
	}
	else {
		$link[] = array('text' => $_LANG['back_list'], 'href' => 'comment_manage.php?act=list');
		sys_msg($_LANG['no_select_comment'], 0, $link);
	}
}

?>
