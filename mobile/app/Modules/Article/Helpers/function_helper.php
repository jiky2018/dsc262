<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_article_info($article_id)
{
	$sql = 'SELECT a.*, IFNULL(AVG(r.comment_rank), 0) AS comment_rank ' . 'FROM ' . $GLOBALS['ecs']->table('article') . ' AS a ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('comment') . ' AS r ON r.id_value = a.article_id AND comment_type = 1 ' . 'WHERE a.is_open = 1 AND a.article_id = \'' . $article_id . '\' GROUP BY a.article_id';
	$row = $GLOBALS['db']->getRow($sql);

	if (!empty($row)) {
		$row['comment_rank'] = ceil($row['comment_rank']);
		$row['add_time'] = local_date('Y-m-d H:i:s', $row['add_time']);
		if (empty($row['author']) || ($row['author'] == '_SHOPHELP')) {
			$row['author'] = $GLOBALS['_CFG']['shop_name'];
		}
	}

	return $row;
}

function article_related_goods($id)
{
	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, g.shop_price AS org_price, ' . 'IFNULL(mp.user_price, g.shop_price * \'' . $_SESSION['discount'] . '\') AS shop_price, ' . 'g.market_price, g.promote_price, g.promote_start_date, g.promote_end_date ' . 'FROM ' . $GLOBALS['ecs']->table('goods_article') . ' ga ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = ga.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ' . 'WHERE ga.article_id = \'' . $id . '\' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0';
	$res = $GLOBALS['db']->query($sql);
	$arr = array();

	foreach ($res as $row) {
		$arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
		$arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$arr[$row['goods_id']]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_thumb']);
		$arr[$row['goods_id']]['goods_img'] = get_image_path($row['goods_img']);
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

function get_cat_articles($cat_id, $page = 1, $size = 20, $requirement = '')
{
	if ($cat_id == '-1') {
		$cat_str = 'cat_id > 0';
	}
	else {
		$cat_str = get_article_children($cat_id);
	}

	if ($requirement != '') {
		$sql = 'SELECT article_id, title, author, add_time, file_url, open_type' . ' FROM ' . $GLOBALS['ecs']->table('article') . ' WHERE is_open = 1 AND title like \'%' . $requirement . '%\' ' . ' ORDER BY article_type DESC, article_id DESC';
	}
	else {
		$sql = 'SELECT article_id, title, author, add_time, file_url, open_type' . ' FROM ' . $GLOBALS['ecs']->table('article') . ' WHERE is_open = 1 AND ' . $cat_str . ' ORDER BY article_type DESC, article_id DESC';
	}

	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
	$arr = array();

	foreach ($res as $row) {
		$article_id = $row['article_id'];
		$arr[$article_id]['id'] = $article_id;
		$arr[$article_id]['title'] = $row['title'];
		$arr[$article_id]['short_title'] = 0 < $GLOBALS['_CFG']['article_title_length'] ? sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
		$arr[$article_id]['author'] = empty($row['author']) || ($row['author'] == '_SHOPHELP') ? $GLOBALS['_CFG']['shop_name'] : $row['author'];
		$arr[$article_id]['url'] = $row['open_type'] != 1 ? build_uri('article', array('aid' => $article_id), $row['title']) : trim($row['file_url']);
		$arr[$article_id]['add_time'] = date($GLOBALS['_CFG']['date_format'], $row['add_time']);
	}

	return $arr;
}

function article_content_html_out($str)
{
	if (function_exists('htmlspecialchars_decode')) {
		$str = htmlspecialchars_decode($str);
	}
	else {
		$str = html_entity_decode($str);
	}

	$str = stripslashes($str);
	return $str;
}


?>
