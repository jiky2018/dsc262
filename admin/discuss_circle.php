<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_discuss_list($ru_id)
{
	$filter['keywords'] = empty($_REQUEST['keywords']) ? 0 : trim($_REQUEST['keywords']);
	if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
		$filter['keywords'] = json_str_iconv($filter['keywords']);
	}

	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'dc.add_time' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$where = ' WHERE 1';
	$where .= !empty($filter['keywords']) ? ' AND (dc.dis_title LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\' OR g.goods_name LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\') ' : '';

	if (0 < $ru_id) {
		$where .= ' AND g.user_id = \'' . $ru_id . '\'';
	}

	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('discuss_circle') . ' as dc, ' . $GLOBALS['ecs']->table('goods') . ' g ' . (' ' . $where) . ' AND dc.goods_id = g.goods_id';
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$arr = array();
	$sql = 'SELECT dc.*, g.goods_name, g.user_id as ru_id FROM ' . $GLOBALS['ecs']->table('discuss_circle') . ' as dc, ' . $GLOBALS['ecs']->table('goods') . ' g ' . (' ' . $where . ' ') . ' AND dc.goods_id = g.goods_id' . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		switch ($row['review_status']) {
		case 1:
			$row['lang_review_status'] = $GLOBALS['_LANG']['not_audited'];
			break;

		case 2:
			$row['lang_review_status'] = $GLOBALS['_LANG']['audited_not_adopt'];
			break;

		case 3:
			$row['lang_review_status'] = $GLOBALS['_LANG']['audited_yes_adopt'];
			break;
		}

		$row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
		$row['shop_name'] = get_shop_name($row['ru_id'], 1);
		$row['user_name'] = $GLOBALS['db']->getOne('SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $row['user_id'] . '\'', true);
		$arr[] = $row;
	}

	$filter['keywords'] = stripslashes($filter['keywords']);
	$arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_discuss_user_reply_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? 0 : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$dis_id = empty($_REQUEST['dis_id']) ? 0 : trim($_REQUEST['dis_id']);
		$id = empty($_REQUEST['id']) ? 0 : trim($_REQUEST['id']);
		$filter['dis_id'] = 0 < $dis_id ? $dis_id : $id;
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'dc.add_time' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$where = ' WHERE 1';
		$where .= !empty($filter['keywords']) ? ' AND dc.dis_title LIKE \'%' . mysql_like_quote($filter['keywords']) . '%\' ' : '';
		$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('discuss_circle') . ' as dc ' . (' ' . $where) . ' AND dc.parent_id = \'' . $filter['dis_id'] . '\'';
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$arr = array();
		$sql = 'SELECT dc.* FROM ' . $GLOBALS['ecs']->table('discuss_circle') . ' as dc ' . (' ' . $where . ' ') . ' AND dc.parent_id = \'' . $filter['dis_id'] . '\'' . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . $filter['start'] . (', ' . $filter['page_size']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
		$sql = 'SELECT u.user_name FROM ' . $GLOBALS['ecs']->table('users') . ' AS u, ' . $GLOBALS['ecs']->table('discuss_circle') . ' AS dc ' . ' WHERE u.user_id = dc.user_id AND dc.dis_id = \'' . $row['quote_id'] . '\'';
		$users = $GLOBALS['db']->getRow($sql);
		$row['quote_name'] = $users['user_name'];
		$arr[] = $row;
	}

	$filter['keywords'] = stripslashes($filter['keywords']);
	$arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count'], 'dis_id' => $filter['dis_id']);
	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require dirname(__FILE__) . '/includes/lib_goods.php';
include_once ROOT_PATH . '/includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);

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

if ($_REQUEST['act'] == 'list') {
	admin_priv('discuss_circle');
	$smarty->assign('ur_here', $_LANG['discuss_circle']);
	$smarty->assign('full_page', 1);
	$list = get_discuss_list($adminru['ru_id']);
	$smarty->assign('discuss_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	$smarty->assign('action_link', array('text' => $_LANG['discuss_add'], 'href' => 'discuss_circle.php?act=add'));
	assign_query_info();
	$smarty->display('discuss_list.dwt');
}

if ($_REQUEST['act'] == 'add') {
	admin_priv('discuss_circle');
	$smarty->assign('lang', $_LANG);
	$smarty->assign('ur_here', $_LANG['discuss_add']);
	$smarty->assign('action_link', array('href' => 'discuss_circle.php?act=list', 'text' => $_LANG['discuss_circle']));
	$smarty->assign('action', 'add');
	$smarty->assign('act', 'insert');
	$smarty->assign('cfg_lang', $_CFG['lang']);
	assign_query_info();
	$smarty->display('discuss_info.dwt');
}

if ($_REQUEST['act'] == 'insert') {
	$goods_id = !empty($_POST['goods_id']) ? trim($_POST['goods_id']) : 0;
	$dis_title = !empty($_POST['dis_title']) ? trim($_POST['dis_title']) : '';
	$dis_text = !empty($_POST['content']) ? trim($_POST['content']) : '';
	$user_name = !empty($_POST['user_name']) ? trim($_POST['user_name']) : '';
	$discuss_type = !empty($_POST['discuss_type']) ? intval($_POST['discuss_type']) : 0;
	$sql = 'SELECT user_id, user_name FROM ' . $ecs->table('users') . (' WHERE user_name=\'' . $user_name . '\'');
	$user = $db->getRow($sql);

	if ($user['user_id'] <= 0) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['type_name_exist'], 0, $link);
	}

	$add_time = gmtime();

	if ($_FILES['img_url']) {
		foreach ($_FILES['img_url']['error'] as $key => $value) {
			if ($value == 0) {
				if (!$image->check_img_type($_FILES['img_url']['type'][$key])) {
					$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
					sys_msg($_LANG['invalid_img_url'], 0, $link);
				}
			}
			else if ($value == 1) {
				$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
				sys_msg($_LANG['img_url_too_big'], 0, $link);
			}
			else if ($_FILES['img_url']['error'] == 2) {
				$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
				sys_msg($_LANG['img_url_too_big'], 0, $link);
			}
		}

		foreach ($_FILES['img_url']['tmp_name'] as $key => $value) {
			if ($value != 'none') {
				if (!$image->check_img_type($_FILES['img_url']['type'][$key])) {
					$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
					sys_msg($_LANG['invalid_img_url'], 0, $link);
				}
			}
		}
	}

	$sql = 'INSERT INTO ' . $ecs->table('discuss_circle') . (" (goods_id, user_id, order_id, dis_type, dis_title, dis_text, add_time, user_name,review_status)\r\n\tVALUES ('" . $goods_id . "',\r\n\t'" . $user['user_id'] . "',\r\n\t'0',\r\n\t'" . $discuss_type . "',\r\n\t'" . $dis_title . "',\r\n\t'" . $dis_text . "',\r\n\t'" . $add_time . "',\r\n\t'" . $user['user_name'] . '\',1)');
	$db->query($sql);
	$dis_id = $db->insert_id();

	if ($_FILES['img_url']) {
		if (!empty($dis_id)) {
			handle_gallery_image(0, $_FILES['img_url'], $_POST['img_desc'], $_POST['img_file'], 0, $dis_id, 'true');
		}
		else {
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
			sys_msg($_LANG['dis_error'], 0, $link);
		}
	}

	admin_log($dis_title, 'add', 'discussinsert');
	clear_cache_files();
	$link[0]['text'] = $_LANG['discuss_add'];
	$link[0]['href'] = 'discuss_circle.php?act=add';
	$link[1]['text'] = $_LANG['back_list'];
	$link[1]['href'] = 'discuss_circle.php?act=list';
	sys_msg($_LANG['add'] . '&nbsp;' . $dis_title . '&nbsp;' . $_LANG['attradd_succed'], 0, $link);
}

if ($_REQUEST['act'] == 'update') {
	$dis_id = !empty($_POST['dis_id']) ? trim($_POST['dis_id']) : 0;

	if (empty($dis_id)) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg($_LANG['discuss_exits'], 0, $link);
	}

	$dis_title = !empty($_POST['dis_title']) ? trim($_POST['dis_title']) : '';
	$dis_text = !empty($_POST['content']) ? trim($_POST['content']) : '';
	$old_img_desc = !empty($_POST['old_img_desc']) ? $_POST['old_img_desc'] : '';
	$front_cover = !empty($_POST['front_cover']) ? $_POST['front_cover'] : 0;
	$discuss_type = !empty($_POST['discuss_type']) ? $_POST['discuss_type'] : 1;
	$review_status = !empty($_REQUEST['review_status']) ? intval($_REQUEST['review_status']) : 1;
	$review_content = !empty($_REQUEST['review_content']) ? trim($_REQUEST['review_content']) : '';
	$add_time = gmtime();
	$sql = 'UPDATE ' . $ecs->table('discuss_circle') . (" SET\r\n\t\t\tdis_title='" . $dis_title . "',\r\n\t\t\tdis_text='" . $dis_text . "',\r\n\t\t\tadd_time='" . $add_time . "',\r\n\t\t\tdis_type='" . $discuss_type . "',\r\n            review_status = '" . $review_status . "',\r\n            review_content = '" . $review_content . "'\r\n\t\t\t WHERE dis_id='" . $dis_id . '\'');
	$db->query($sql);
	admin_log($dis_title, 'add', 'discussinsert');
	clear_cache_files();
	$link[0]['text'] = $_LANG['discuss_edit'];
	$link[0]['href'] = 'discuss_circle.php?act=reply&id=' . $dis_id;
	$link[1]['text'] = $_LANG['back_list'];
	$link[1]['href'] = 'discuss_circle.php?act=list';
	sys_msg($_LANG['edit'] . '&nbsp;' . $dis_title . '&nbsp;' . $_LANG['attradd_succed'], 0, $link);
}

if ($_REQUEST['act'] == 'query') {
	$list = get_discuss_list($adminru['ru_id']);
	$smarty->assign('discuss_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('discuss_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

if ($_REQUEST['act'] == 'reply') {
	admin_priv('discuss_circle');
	$id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$discuss_info = array();
	$id_value = array();
	$sql = 'SELECT * FROM ' . $ecs->table('discuss_circle') . (' WHERE dis_id = \'' . $id . '\' LIMIT 1');
	$discuss_info = $db->getRow($sql);

	if (!empty($discuss_info)) {
		$discuss_info['dis_title'] = str_replace('\\r\\n', '<br />', htmlspecialchars($discuss_info['dis_title']));
		$discuss_info['dis_title'] = nl2br(str_replace('\\n', '<br />', $discuss_info['dis_title']));
		$discuss_info['dis_text'] = str_replace('\\r\\n', '<br />', htmlspecialchars($discuss_info['dis_text']));
		$discuss_info['dis_text'] = nl2br(str_replace('\\n', '<br />', $discuss_info['dis_text']));
		$discuss_info['add_time'] = local_date($_CFG['time_format'], $discuss_info['add_time']);
	}

	$sql = 'SELECT goods_name, original_img FROM ' . $ecs->table('goods') . ' WHERE goods_id = \'' . $discuss_info['goods_id'] . '\'';
	$goods = $db->getRow($sql);

	if (!empty($goods)) {
		$discuss_info['original_img'] = $goods['original_img'];
		$discuss_info['goods_name'] = $goods['goods_name'];
	}

	$sql = 'SELECT * FROM ' . $ecs->table('goods_gallery') . ' WHERE dis_id = \'' . $discuss_info['dis_id'] . '\'';
	$imgs = $db->getAll($sql);
	$sql = 'SELECT user_name, email FROM ' . $ecs->table('admin_user') . (' WHERE user_id = \'' . $_SESSION['admin_id'] . '\'');
	$admin_info = $db->getRow($sql);
	$sql = 'SELECT goods_name FROM ' . $ecs->table('goods') . (' WHERE goods_id = \'' . $discuss_info['goods_id'] . '\'');
	$id_value = $db->getOne($sql);
	$smarty->assign('imgs', $imgs);
	$smarty->assign('msg', $discuss_info);
	$smarty->assign('admin_info', $admin_info);
	$smarty->assign('act', 'update');
	$smarty->assign('action', 'relpy');
	$smarty->assign('ur_here', $_LANG['discuss_info']);
	$smarty->assign('action_link', array('text' => $_LANG['discuss_circle'], 'href' => 'discuss_circle.php?act=list'));
	assign_query_info();
	$smarty->display('discuss_info.dwt');
}

if ($_REQUEST['act'] == 'action') {
	admin_priv('discuss_circle');
	$ip = real_ip();
	$sql = 'SELECT comment_id, content, parent_id FROM ' . $ecs->table('comment') . (' WHERE parent_id = \'' . $_REQUEST['comment_id'] . '\'');
	$reply_info = $db->getRow($sql);

	if (!empty($reply_info['content'])) {
		$sql = 'UPDATE ' . $ecs->table('comment') . ' SET ' . ('email     = \'' . $_POST['email'] . '\', ') . ('user_name = \'' . $_POST['user_name'] . '\', ') . ('content   = \'' . $_POST['content'] . '\', ') . 'add_time  =  \'' . gmtime() . '\', ' . ('ip_address= \'' . $ip . '\', ') . 'status    = 0' . ' WHERE comment_id = \'' . $reply_info['comment_id'] . '\'';
	}
	else {
		$sql = 'INSERT INTO ' . $ecs->table('comment') . ' (comment_type, id_value, email, user_name , ' . 'content, add_time, ip_address, status, parent_id) ' . ('VALUES(\'' . $_POST['comment_type'] . '\', \'' . $_POST['id_value'] . '\',\'' . $_POST['email'] . '\', ') . ('\'' . $_SESSION['admin_name'] . '\',\'' . $_POST['content'] . '\',\'') . gmtime() . ('\', \'' . $ip . '\', \'0\', \'' . $_POST['comment_id'] . '\')');
	}

	$db->query($sql);
	$sql = 'UPDATE ' . $ecs->table('comment') . (' SET status = 1 WHERE comment_id = \'' . $_POST['comment_id'] . '\'');
	$db->query($sql);
	if (!empty($_POST['send_email_notice']) || isset($_POST['remail'])) {
		$sql = 'SELECT user_name, email, content ' . 'FROM ' . $ecs->table('comment') . (' WHERE comment_id =\'' . $_REQUEST['comment_id'] . '\'');
		$comment_info = $db->getRow($sql);
		$template = get_mail_template('recomment');
		$smarty->assign('user_name', $comment_info['user_name']);
		$smarty->assign('recomment', $_POST['content']);
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
	if ($_REQUEST['check'] == 'allow') {
		$sql = 'UPDATE ' . $ecs->table('comment') . (' SET status = 1 WHERE comment_id = \'' . $_REQUEST['id'] . '\'');
		$db->query($sql);
		clear_cache_files();
		ecs_header('Location: comment_manage.php?act=reply&id=' . $_REQUEST['id'] . "\n");
		exit();
	}
	else {
		$sql = 'UPDATE ' . $ecs->table('comment') . (' SET status = 0 WHERE comment_id = \'' . $_REQUEST['id'] . '\'');
		$db->query($sql);
		clear_cache_files();
		ecs_header('Location: comment_manage.php?act=reply&id=' . $_REQUEST['id'] . "\n");
		exit();
	}
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('discuss_circle');
	$id = intval($_GET['id']);
	$dis_id = intval($_GET['dis_id']);
	$sql = 'DELETE FROM ' . $ecs->table('discuss_circle') . (' WHERE dis_id = \'' . $id . '\'');
	$db->query($sql);
	admin_log('', 'remove', 'ads');

	if ($dis_id) {
		$query = 'discuss_reply_query';
	}
	else {
		$query = 'query';
	}

	$url = 'discuss_circle.php?act=' . $query . '&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}

if ($_REQUEST['act'] == 'batch') {
	admin_priv('discuss_circle');
	$dis_id = isset($_POST['dis_id']) ? trim($_POST['dis_id']) : 0;
	if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
		sys_msg('没有选择任何数据', 1);
	}

	$ids = !empty($_POST['checkboxes']) ? join(',', $_POST['checkboxes']) : 0;
	$del_count = count($_POST['checkboxes']);
	if (isset($_POST['type']) && !empty($_POST['type'])) {
		if ($_POST['type'] == 'batch_remove') {
			$db->query('DELETE FROM ' . $ecs->table('discuss_circle') . ' WHERE ' . db_create_in($ids, 'dis_id'));
			clear_cache_files();
			$action = $_POST['type'] == 'batch_remove' ? 'remove' : 'edit';
			admin_log('', $action, 'adminlog');

			if (0 < $dis_id) {
				$href = 'discuss_circle.php?act=user_reply&id=' . $dis_id;
				$back_list = $_LANG['discuss_user_reply'];
			}
			else {
				$href = 'discuss_circle.php?act=list';
				$back_list = $_LANG['back_list'];
			}

			$link[] = array('text' => $back_list, 'href' => $href);
			sys_msg(sprintf($_LANG['batch_drop_success'], count($_POST['checkboxes'])), 0, $link);
		}
		else if ($_POST['type'] == 'review_to') {
			$review_status = $_POST['review_status'];
			$sql = 'UPDATE ' . $ecs->table('discuss_circle') . (' SET review_status = \'' . $review_status . '\' ') . ' WHERE dis_id ' . db_create_in($ids);
			$res = $db->query($sql);

			if ($res) {
				if (0 < $dis_id) {
					$href = 'discuss_circle.php?act=user_reply&id=' . $dis_id;
					$back_list = $_LANG['discuss_user_reply'];
				}
				else {
					$href = 'discuss_circle.php?act=list';
					$back_list = $_LANG['back_list'];
				}

				$link[] = array('text' => $back_list, 'href' => $href);
				sys_msg('审核状态设置成功', 0, $link);
			}
		}
	}
}

if ($_REQUEST['act'] == 'user_reply') {
	admin_priv('discuss_circle');
	$smarty->assign('ur_here', $_LANG['discuss_user_reply']);
	$smarty->assign('full_page', 1);
	$list = get_discuss_user_reply_list();
	$smarty->assign('reply_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$smarty->assign('dis_id', $list['dis_id']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	assign_query_info();
	$smarty->display('discuss_user_reply.dwt');
}

if ($_REQUEST['act'] == 'discuss_reply_query') {
	$list = get_discuss_user_reply_list();
	$smarty->assign('reply_list', $list['item']);
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$smarty->assign('dis_id', $list['dis_id']);
	$sort_flag = sort_flag($list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('discuss_user_reply.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

?>
