<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_area.php';
require_once ROOT_PATH . ADMIN_PATH . '/includes/lib_goods.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

$act = (isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'repay');
assign_template();
$comment_id = (empty($_REQUEST['comment_id']) ? 0 : $_REQUEST['comment_id']);
$smarty->assign('helps', get_shop_help());
$smarty->assign('data_dir', DATA_DIR);
$smarty->assign('action', $action);
$smarty->assign('lang', $_LANG);
$area_info = get_area_info($province_id);
$area_id = $area_info['region_id'];
$where = 'regionId = \'' . $province_id . '\'';
$date = array('parent_id');
$region_id = get_table_date('region_warehouse', $where, $date, 2);
$sql = 'select comment_id, id_value, user_id, order_id, content, user_name, add_time from ' . $ecs->table('comment') . ' where comment_id = \'' . $comment_id . '\'';
$comment = $db->getRow($sql);
$goods_id = $comment['id_value'];
$goodsInfo = get_goods_info($goods_id, $region_id, $area_id);
$goodsInfo['goods_price'] = price_format($goodsInfo['goods_price']);
$smarty->assign('goodsInfo', $goodsInfo);
$mc_all = ments_count_all($goods_id);
$mc_one = ments_count_rank_num($goods_id, 1);
$mc_two = ments_count_rank_num($goods_id, 2);
$mc_three = ments_count_rank_num($goods_id, 3);
$mc_four = ments_count_rank_num($goods_id, 4);
$mc_five = ments_count_rank_num($goods_id, 5);
$comment_all = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);
$smarty->assign('comment_all', $comment_all);

if (defined('THEME_EXTENSION')) {
	$categories_pro = get_category_tree_leve_one();
	$smarty->assign('categories_pro', $categories_pro);
}

if ($_REQUEST['act'] == 'repay') {
	$cache_id = $comment_id . '-' . $_SESSION['user_rank'] . '-' . $_CFG['lang'];
	$cache_id = sprintf('%X', crc32($cache_id));

	if (!$smarty->is_cached('goods_discuss_show.dwt', $cache_id)) {
		if (empty($comment_id)) {
			ecs_header("Location: ./\n");
			exit();
		}

		if (empty($comment)) {
			ecs_header("location: ./\n");
			exit();
		}

		$sql = 'SELECT user_picture from ' . $ecs->table('users') . ' WHERE user_id = \'' . $comment['user_id'] . '\'';
		$user_picture = $db->getOne($sql);
		$smarty->assign('user_picture', $user_picture);
		$comment['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $comment['add_time']);
		$smarty->assign('comment', $comment);
		$buy_goods = get_user_buy_goods_order($comment['id_value'], $comment['user_id'], $comment['order_id']);
		$smarty->assign('buy_goods', $buy_goods);
		$img_list = get_img_list($comment['id_value'], $comment['comment_id']);
		$smarty->assign('img_list', $img_list);
		$position = assign_ur_here($goodsInfo['cat_id'], $goodsInfo['goods_name'], array($comment['content']), $goodsInfo['goods_url']);
		$smarty->assign('ip', real_ip());
		$smarty->assign('goods', $goods);
		$smarty->assign('page_title', $position['title']);
		$smarty->assign('ur_here', $position['ur_here']);
		$type = 0;
		$reply_page = 1;
		$libType = 1;
		$size = 10;
		$reply = get_reply_list($comment['id_value'], $comment['comment_id'], $type, $reply_page, $libType, $size);
		$smarty->assign('reply', $reply);
		$smarty->assign('now_time', gmtime());
	}

	$smarty->display('comment_repay.dwt');
}

?>
