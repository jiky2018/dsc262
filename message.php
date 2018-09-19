<?php
//zend by 商创网络 Q Q:123456    禁止倒卖 一经发现停止任何服务
function get_msg_list($num, $start)
{
	$msg = array();
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('feedback') . ' WHERE parent_id = 0 AND msg_area = 1 AND msg_status = 1';
	$res = $GLOBALS['db']->SelectLimit($sql, $num, $start);

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		for ($i = 0; $i < count($rows); $i++) {
			$msg[$rows['msg_time']]['user_name'] = htmlspecialchars($rows['user_name']);
			$msg[$rows['msg_time']]['msg_content'] = str_replace('\\r\\n', '<br />', htmlspecialchars($rows['msg_content']));
			$msg[$rows['msg_time']]['msg_content'] = str_replace('\\n', '<br />', $msg[$rows['msg_time']]['msg_content']);
			$msg[$rows['msg_time']]['msg_time'] = local_date($GLOBALS['_CFG']['time_format'], $rows['msg_time']);
			$msg[$rows['msg_time']]['msg_type'] = $GLOBALS['_LANG']['message_type'][$rows['msg_type']];
			$msg[$rows['msg_time']]['msg_title'] = nl2br(htmlspecialchars($rows['msg_title']));
			$msg[$rows['msg_time']]['message_img'] = $rows['message_img'];
			$msg[$rows['msg_time']]['tablename'] = $rows['tablename'];

			if (isset($rows['order_id'])) {
				$msg[$rows['msg_time']]['order_id'] = $rows['order_id'];
			}

			$msg[$rows['msg_time']]['comment_rank'] = $rows['comment_rank'];
			$msg[$rows['msg_time']]['id_value'] = $rows['id_value'];

			if ($rows['id_value']) {
				$sql_goods = 'SELECT goods_name FROM ' . $GLOBALS['ecs']->table('goods');
				$sql_goods .= 'WHERE goods_id= ' . $rows['id_value'];
				$goods_res = $GLOBALS['db']->getRow($sql_goods);
				$msg[$rows['msg_time']]['goods_name'] = $goods_res['goods_name'];
				$msg[$rows['msg_time']]['goods_url'] = build_uri('goods', array('gid' => $rows['id_value']), $goods_res['goods_name']);
			}
		}

		$id = $rows['msg_id'];
		$reply = array();
		$sql = 'SELECT user_name AS re_name, user_email AS re_email, msg_time AS re_time, msg_content AS re_content ,parent_id' . ' FROM ' . $GLOBALS['ecs']->table('feedback') . ' WHERE parent_id = \'' . $id . '\'';
		$reply = $GLOBALS['db']->getRow($sql);

		if ($reply) {
			$msg[$rows['msg_time']]['re_name'] = $reply['re_name'];
			$msg[$rows['msg_time']]['re_email'] = $reply['re_email'];
			$msg[$rows['msg_time']]['re_time'] = local_date($GLOBALS['_CFG']['time_format'], $reply['re_time']);
			$msg[$rows['msg_time']]['re_content'] = nl2br(htmlspecialchars($reply['re_content']));
		}
	}

	return $msg;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_area.php';

if (empty($_CFG['message_board'])) {
	show_message($_LANG['message_board_close']);
}

$smarty->assign('category', 1.0E+19);
$user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
get_request_filter();
include 'includes/cls_json.php';
$json = new JSON();
$result = array('err_msg' => '', 'err_no' => 0, 'content' => '');
$action = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';

if ($action == 'act_add_message') {
	$_POST = get_request_filter($_POST, 1);
	include_once ROOT_PATH . 'includes/lib_clips.php';
	if (intval($_CFG['captcha']) & CAPTCHA_MESSAGE && 0 < gd_version()) {
		$captcha_str = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';
		$verify = new Verify();
		$captcha_code = $verify->check($captcha_str, 'captcha_common');

		if (!$captcha_code) {
			show_message($_LANG['invalid_captcha']);
		}
	}
	else {
		if (!isset($_SESSION['send_time'])) {
			$_SESSION['send_time'] = 0;
		}

		$cur_time = gmtime();

		if ($cur_time - $_SESSION['send_time'] < 30) {
			show_message($_LANG['cmt_spam_warning']);
		}
	}

	$user_name = '';
	if (empty($_POST['anonymous']) && !empty($_SESSION['user_name'])) {
		$user_name = $_SESSION['user_name'];
	}
	else {
		if (!empty($_POST['anonymous']) && !isset($_POST['user_name'])) {
			$user_name = $_LANG['anonymous'];
		}
		else if (empty($_POST['user_name'])) {
			$user_name = $_LANG['anonymous'];
		}
		else {
			$user_name = htmlspecialchars(trim($_POST['user_name']));
		}
	}

	if (empty($user_id)) {
		show_message($_LANG['login_please']);
		exit();
	}

	$_POST['user_email'] = !empty($_POST['user_email']) ? addslashes_deep($_POST['user_email']) : '';
	$_POST['user_email'] = !empty($_POST['user_email']) ? compile_str($_POST['user_email']) : '';
	$_POST['msg_title'] = !empty($_POST['msg_title']) ? addslashes_deep($_POST['msg_title']) : '';
	$_POST['msg_title'] = !empty($_POST['msg_title']) ? compile_str($_POST['msg_title']) : '';
	$_POST['msg_content'] = !empty($_POST['msg_content']) ? addslashes_deep($_POST['msg_content']) : '';
	$_POST['msg_content'] = !empty($_POST['msg_content']) ? compile_str($_POST['msg_content']) : '';
	$message = array(
		'user_id'     => $user_id,
		'user_name'   => $user_name,
		'user_email'  => isset($_POST['user_email']) ? htmlspecialchars(trim($_POST['user_email'])) : '',
		'msg_type'    => isset($_POST['msg_type']) ? intval($_POST['msg_type']) : 0,
		'msg_title'   => isset($_POST['msg_title']) ? trim($_POST['msg_title']) : '',
		'msg_content' => isset($_POST['msg_content']) ? trim($_POST['msg_content']) : '',
		'order_id'    => 0,
		'msg_area'    => 1,
		'upload'      => array()
		);

	if (add_message($message)) {
		if (intval($_CFG['captcha']) & CAPTCHA_MESSAGE) {
			unset($_SESSION[$validator->session_word]);
		}
		else {
			$_SESSION['send_time'] = $cur_time;
		}

		$msg_info = $_CFG['message_check'] ? $_LANG['message_submit_wait'] : $_LANG['message_submit_done'];
		show_message($msg_info, $_LANG['message_list_lnk'], 'message.php');
	}
	else {
		$err->show($_LANG['message_list_lnk'], 'message.php');
	}
}

if ($action == 'default') {
	assign_template();
	$position = assign_ur_here(0, $_LANG['message_board']);
	$smarty->assign('page_title', $position['title']);
	$smarty->assign('ur_here', $position['ur_here']);
	$smarty->assign('helps', get_shop_help());

	if (defined('THEME_EXTENSION')) {
		$categories_pro = get_category_tree_leve_one();
		$smarty->assign('categories_pro', $categories_pro);
	}

	$smarty->assign('brand_list', get_brand_list());
	$smarty->assign('enabled_mes_captcha', intval($_CFG['captcha']) & CAPTCHA_MESSAGE);
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . ' WHERE STATUS =1 AND comment_type =0 AND id_value = 0 ';
	$record_count = $db->getOne($sql);
	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$pagesize = get_library_number('message_list', 'message_board');
	$pager = get_pager('message.php', array(), $record_count, $page, $pagesize);
	$msg_lists = get_msg_list($pagesize, $pager['start']);
	assign_dynamic('message_board');
	$smarty->assign('rand', mt_rand());
	$smarty->assign('msg_lists', $msg_lists);
	$smarty->assign('pager', $pager);
	$smarty->assign('user_id', $user_id);
	if (defined('THEME_EXTENSION') && 0 < $_SESSION['user_id']) {
		$smarty->assign('user_info', get_user_default($_SESSION['user_id']));
	}

	if (intval($_CFG['captcha']) & CAPTCHA_MESSAGE && 0 < gd_version()) {
		$smarty->assign('enabled_captcha', 1);
		$smarty->assign('rand', mt_rand());
	}

	$smarty->display('message_board.dwt');
}
else if ($action == 'cat_tree_two') {
	$cat_id = intval($_REQUEST['id']);
	$sql = 'select parent_id from ' . $ecs->table('category') . (' where cat_id = \'' . $cat_id . '\'');
	$parent_id = $db->getOne($sql);
	$sql = 'select parent_id from ' . $ecs->table('category') . (' where cat_id = \'' . $parent_id . '\'');
	$parentCat = $db->getOne($sql);
	$smarty->assign('category', $cat_id);
	$smarty->assign('parent_id', $parent_id);
	$smarty->assign('parentCat', $parentCat);
	$tree_arr = array(0, 'goodsList');
	$categories_pro2 = get_cache_site_file('category_tree2', $tree_arr);
	$smarty->assign('categories_pro2', $categories_pro2);
	$result['content'] = $smarty->fetch('library/secondlevel_cat_tree2.lbi');
	exit($json->encode($result));
}

?>
