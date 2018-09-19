<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_article_info($article_id)
{
	$sql = 'SELECT a.*, IFNULL(AVG(r.comment_rank), 0) AS comment_rank ' . 'FROM ' . $GLOBALS['ecs']->table('article') . ' AS a ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('comment') . ' AS r ON r.id_value = a.article_id AND comment_type = 1 ' . ('WHERE a.is_open = 1 AND a.article_id = \'' . $article_id . '\' GROUP BY a.article_id');
	$row = $GLOBALS['db']->getRow($sql);

	if ($row !== false) {
		$row['comment_rank'] = ceil($row['comment_rank']);
		$row['add_time'] = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);
		if (empty($row['author']) || $row['author'] == '_SHOPHELP') {
			$row['author'] = $GLOBALS['_CFG']['shop_name'];
		}

		$row['file_url'] = get_image_path(0, $row['file_url']);

		if ($GLOBALS['_CFG']['open_oss'] == 1) {
			$bucket_info = get_bucket_info();

			if ($row['content']) {
				$desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $row['content']);
				$row['content'] = $desc_preg['goods_desc'];
			}
		}
	}

	return $row;
}

function article_related_goods($id)
{
	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, g.shop_price AS org_price, ' . ('IFNULL(mp.user_price, g.shop_price * \'' . $_SESSION['discount'] . '\') AS shop_price, ') . 'g.market_price, g.promote_price, g.promote_start_date, g.promote_end_date ' . 'FROM ' . $GLOBALS['ecs']->table('goods_article') . ' ga ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = ga.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ('WHERE ga.article_id = \'' . $id . '\' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0');
	$res = $GLOBALS['db']->query($sql);
	$arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
		$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$arr[$row['goods_id']]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$arr[$row['goods_id']]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
		$arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
		$arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
		$arr[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);

		if (0 < $row['promote_price']) {
			$arr[$row['goods_id']]['promote_price'] = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			$arr[$row['goods_id']]['formated_promote_price'] = price_format($arr[$row['goods_id']]['promote_price']);
		}
		else {
			$arr[$row['goods_id']]['promote_price'] = 0;
		}
	}

	return $arr;
}

function get_cat_id_art($article_id)
{
	$sql = 'select ac.cat_id,ac.cat_type,ac.cat_name,ac.parent_id from ' . $GLOBALS['ecs']->table('article') . ' as a left join ' . $GLOBALS['ecs']->table('article_cat') . (' as ac on a.cat_id=ac.cat_id where a.article_id = \'' . $article_id . '\'');
	$cat_info = $GLOBALS['db']->getRow($sql);
	return $cat_info;
}

function assign_article_comment($id, $type, $page = 1)
{
	require_once 'includes/cls_pager.php';
	$tag = array();
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') . (' WHERE id_value = \'' . $id . '\' AND comment_type = \'' . $type . '\' AND status = 1 AND parent_id = 0 ');
	$count = $GLOBALS['db']->getOne($sql);
	$size = !empty($GLOBALS['_CFG']['comments_number']) ? $GLOBALS['_CFG']['comments_number'] : 5;
	$comment = new Pager($count, $size, '', $id, 0, $page, 'gotoPage', 1);
	$limit = $comment->limit;
	$pager = $comment->fpage(array(0, 4, 5, 6, 9));
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') . (' WHERE id_value = \'' . $id . '\' AND comment_type = \'' . $type . '\' AND status = 1 AND parent_id = 0 ' . $where) . ' ORDER BY add_time DESC ' . $limit;
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $row) {
		$row['user_name'] = setAnonymous($row['user_name']);
		$ids .= $ids ? ',' . $row['comment_id'] : $row['comment_id'];
		$arr[$row['comment_id']]['id'] = $row['comment_id'];
		$arr[$row['comment_id']]['email'] = $row['email'];
		$arr[$row['comment_id']]['username'] = $row['user_name'];
		$arr[$row['comment_id']]['user_id'] = $row['user_id'];
		$arr[$row['comment_id']]['id_value'] = $row['id_value'];
		$arr[$row['comment_id']]['useful'] = $row['useful'];
		$arr[$row['comment_id']]['user_picture'] = $GLOBALS['db']->getOne('select user_picture from ' . $GLOBALS['ecs']->table('users') . ' where user_id = \'' . $row['user_id'] . '\'');
		$arr[$row['comment_id']]['content'] = nl2br(str_replace('\\n', '<br />', htmlspecialchars($row['content'])));
		$arr[$row['comment_id']]['rank'] = $row['comment_rank'];
		$arr[$row['comment_id']]['server'] = $row['comment_server'];
		$arr[$row['comment_id']]['delivery'] = $row['comment_delivery'];
		$arr[$row['comment_id']]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
		$arr[$row['comment_id']]['buy_goods'] = get_user_buy_goods_order($row['id_value'], $row['user_id'], $row['order_id']);

		if ($row['goods_tag']) {
			$row['goods_tag'] = explode(',', $row['goods_tag']);

			foreach ($row['goods_tag'] as $key => $val) {
				$tag[$key]['txt'] = $val;
				$tag[$key]['num'] = comment_goodstag_num($row['id_value'], $val);
			}

			$arr[$row['comment_id']]['goods_tag'] = $tag;
		}

		$reply = get_reply_list($row['id_value'], $row['comment_id']);
		$arr[$row['comment_id']]['reply_list'] = $reply['reply_list'];
		$arr[$row['comment_id']]['reply_count'] = $reply['reply_count'];
		$arr[$row['comment_id']]['reply_size'] = $reply['reply_size'];
		$arr[$row['comment_id']]['reply_pager'] = $reply['reply_pager'];
		$img_list = get_img_list($row['id_value'], $row['comment_id']);
		$arr[$row['comment_id']]['img_list'] = $img_list;
		$arr[$row['comment_id']]['img_cont'] = count($img_list);
		if (strpos($arr[$row['comment_id']]['user_picture'], 'http://') === false && strpos($arr[$row['comment_id']]['user_picture'], 'https://') === false) {
			if ($GLOBALS['_CFG']['open_oss'] == 1 && $arr[$row['comment_id']]['user_picture']) {
				$bucket_info = get_bucket_info();
				$arr[$row['comment_id']]['user_picture'] = $bucket_info['endpoint'] . $arr[$row['comment_id']]['user_picture'];
			}
		}
	}

	if ($ids) {
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') . (' WHERE parent_id IN( ' . $ids . ' )');
		$res = $GLOBALS['db']->query($sql);

		while ($row = $GLOBALS['db']->fetch_array($res)) {
			$arr[$row['parent_id']]['re_content'] = nl2br(str_replace('\\n', '<br />', htmlspecialchars($row['content'])));
			$arr[$row['parent_id']]['re_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
			$arr[$row['parent_id']]['re_email'] = $row['email'];
			$arr[$row['parent_id']]['re_username'] = $row['user_name'];
			$shop_info = get_shop_name($row['ru_id']);
			$arr[$row['parent_id']]['shop_name'] = $shop_info['shop_name'];
		}
	}

	$cmt = array('comments' => $arr, 'pager' => $pager, 'count' => $count, 'size' => $size);
	return $cmt;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

require ROOT_PATH . 'includes/lib_area.php';
$article_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
if (isset($_REQUEST['cat_id']) && $_REQUEST['cat_id'] < 0) {
	$article_id = $db->getOne('SELECT article_id FROM ' . $ecs->table('article') . ' WHERE cat_id = \'' . intval($_REQUEST['cat_id']) . '\' ');
}

if ($_REQUEST['act'] == 'get_ajax_content') {
	$article = get_article_info($article_id);
	$smarty->assign('article', $article);
	$html = $smarty->fetch('article.dwt');
	$result = array('error' => 0, 'message' => '', 'content' => $html);
	exit(json_encode($result));
}
else if ($_REQUEST['act'] == 'add_comment') {
	require_once ROOT_PATH . '/includes/cls_json.php';
	$json = new JSON();
	$article_id = isset($_REQUEST['article_id']) ? intval($_REQUEST['article_id']) : 0;
	$content = !empty($_REQUEST['content']) ? trim($_REQUEST['content']) : '';
	$ip = real_ip();
	$result = array('error' => 0, 'message' => '', 'content' => '');
	if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
		$sql = ' SELECT comment_id FROM ' . $ecs->table('comment') . (' WHERE comment_type = 1 AND id_value = \'' . $article_id . '\' AND user_id = \'') . $_SESSION['user_id'] . '\' ';
		$comment_id = $db->getOne($sql);

		if (!$comment_id) {
			$other = array('comment_type' => 1, 'id_value' => $article_id, 'email' => $_SESSION['email'], 'user_name' => $_SESSION['user_name'], 'content' => $content, 'add_time' => gmtime(), 'ip_address' => $ip, 'status' => 0, 'user_id' => $_SESSION['user_id']);

			if ($db->autoExecute($ecs->table('comment'), $other, 'INSERT')) {
				$result['message'] = '评论文章成功，等待平台审核通过显示！';
			}
		}
		else {
			$result['error'] = 1;
			$result['message'] = '您已对该文章做出过评论！';
		}
	}
	else {
		$result['error'] = 2;
		$result['message'] = '请登录会员';
	}

	exit($json->encode($result));
}

get_request_filter();
$cache = '';

if ($user_id) {
	$cache .= '-' . $user_id;
}

$cache_id = sprintf('%X', crc32($article_id . $cache . '-' . $_CFG['lang']));

if (!$smarty->is_cached('article.dwt', $cache_id)) {
	$article = get_article_info($article_id);

	if (empty($article)) {
		ecs_header("Location: ./\n");
		exit();
	}

	if (!empty($article['link']) && $article['link'] != 'http://' && $article['link'] != 'https://') {
		ecs_header('location:' . $article['link'] . "\n");
		exit();
	}

	$smarty->assign('helps', get_shop_help());
	$smarty->assign('sys_categories', article_categories_tree(0, 2));
	$smarty->assign('custom_categories', article_categories_tree(0, 1));

	if (!defined('THEME_EXTENSION')) {
		$categories_pro = get_category_tree_leve_one();
		$smarty->assign('categories_pro', $categories_pro);
	}

	$smarty->assign('new_article', get_new_article(5));
	$smarty->assign('best_goods', get_recommend_goods('best'));
	$smarty->assign('new_goods', get_recommend_goods('new'));
	$smarty->assign('hot_goods', get_recommend_goods('hot'));
	$smarty->assign('promotion_goods', get_promote_goods());
	$smarty->assign('related_goods', article_related_goods($article_id));
	$smarty->assign('id', $article_id);
	$smarty->assign('username', $_SESSION['user_name']);
	$smarty->assign('email', $_SESSION['email']);
	$smarty->assign('type', '1');
	$cat_info = get_cat_id_art($article_id);
	$smarty->assign('cat_info', $cat_info);
	if (intval($_CFG['captcha']) & CAPTCHA_COMMENT && 0 < gd_version()) {
		$smarty->assign('enabled_captcha', 1);
		$smarty->assign('rand', mt_rand());
	}

	$smarty->assign('article', $article);
	$smarty->assign('keywords', htmlspecialchars($article['keywords']));
	$smarty->assign('description', htmlspecialchars($article['description']));
	$catlist = array();

	foreach (get_article_parent_cats($article['cat_id']) as $k => $v) {
		$catlist[] = $v['cat_id'];
	}

	assign_template('a', $catlist);
	$position = assign_ur_here($article['cat_id'], $article['title']);
	$smarty->assign('ur_here', $position['ur_here']);
	$smarty->assign('comment_type', 1);
	$sql = 'SELECT a.goods_id, g.goods_name, g.goods_img, g.shop_price ' . 'FROM ' . $ecs->table('goods_article') . ' AS a, ' . $ecs->table('goods') . ' AS g ' . 'WHERE a.goods_id = g.goods_id ' . ('AND a.article_id = \'' . $article_id . '\' ');
	$smarty->assign('goods_list', $db->getAll($sql));
	$next_article = $db->getRow('SELECT article_id, title FROM ' . $ecs->table('article') . (' WHERE article_id > \'' . $article_id . '\' AND cat_id=\'') . $article['cat_id'] . '\' AND is_open=1 ORDER BY article_id ASC LIMIT 1');

	if (!empty($next_article)) {
		$next_article['url'] = build_uri('article', array('aid' => $next_article['article_id']), $next_article['title']);
		$smarty->assign('next_article', $next_article);
	}

	$prev_aid = $db->getOne('SELECT max(article_id) FROM ' . $ecs->table('article') . (' WHERE article_id < \'' . $article_id . '\' AND cat_id=\'') . $article['cat_id'] . '\' AND is_open=1 ORDER BY article_id ASC');

	if (!empty($prev_aid)) {
		$prev_article = $db->getRow('SELECT article_id, title FROM ' . $ecs->table('article') . (' WHERE article_id = \'' . $prev_aid . '\''));
		$prev_article['url'] = build_uri('article', array('aid' => $prev_article['article_id']), $prev_article['title']);
		$smarty->assign('prev_article', $prev_article);
	}

	$smarty->assign('full_page', 1);
	assign_dynamic('article');
}

$article_comment = assign_article_comment($article_id, 1);
$smarty->assign('article_comment', $article_comment['comments']);
$smarty->assign('pager', $article_comment['pager']);
$smarty->assign('count', $article_comment['count']);
$smarty->assign('size', $article_comment['size']);
$smarty->assign('article_id', $article_id);
$seo = get_seo_words('article_content');

foreach ($seo as $key => $value) {
	$seo[$key] = str_replace(array('{sitename}', '{key}', '{name}', '{description}', '{article_class}'), array($_CFG['shop_name'], $article['keywords'], $article['title'], $article['description'], $cat_info['cat_name']), $value);
}

if (!empty($seo['keywords'])) {
	$smarty->assign('keywords', htmlspecialchars($seo['keywords']));
}
else {
	$smarty->assign('keywords', htmlspecialchars($_CFG['shop_keywords']));
}

if (!empty($seo['description'])) {
	$smarty->assign('description', htmlspecialchars($seo['description']));
}
else {
	$smarty->assign('description', htmlspecialchars($_CFG['shop_desc']));
}

if (!empty($seo['title'])) {
	$smarty->assign('page_title', htmlspecialchars($seo['title']));
}
else {
	$smarty->assign('page_title', $position['title']);
}

if (isset($article) && 2 < $article['cat_id']) {
	$smarty->display('article.dwt', $cache_id);
}
else {
	$smarty->display('article_pro.dwt', $cache_id);
}

?>
