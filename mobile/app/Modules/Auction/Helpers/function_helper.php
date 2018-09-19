<?php
//商创网络  禁止倒卖 一经发现停止任何服务 QQ:123456
function auction_count($keywords = '')
{
	$now = gmtime();
	$where = '';

	if ($keywords) {
		$where = 'AND (a.act_name LIKE \'%' . $keywords . '%\' OR g.goods_name LIKE \'%' . $keywords . '%\') ';
	}

	$sql = 'SELECT COUNT(*) ' . 'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS a ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON a.goods_id = g.goods_id ' . ' WHERE a.act_type = \'' . GAT_AUCTION . '\' ' . ('AND a.start_time <= \'' . $now . '\' AND a.end_time >= \'' . $now . '\' AND a.is_finished < 2 AND a.review_status = 3 ') . $where;
	return $GLOBALS['db']->getOne($sql);
}

function auction_list($keywords, $sort, $order, $size, $page)
{
	$auction_list = array();
	$auction_list['under_way'] = array();
	$auction_list['finished'] = array();
	$now = gmtime();
	$where = '';

	if ($keywords) {
		$where = 'AND (a.act_name LIKE \'%' . $keywords . '%\' OR g.goods_name LIKE \'%' . $keywords . '%\') ';
	}

	if ($sort) {
		$by_sort = ' a.' . $sort;
	}

	$sql = 'SELECT a.*, IFNULL(g.goods_thumb, \'\') AS goods_thumb ' . 'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS a ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON a.goods_id = g.goods_id ' . 'WHERE a.act_type = \'' . GAT_AUCTION . '\' ' . $where . ('AND a.start_time <= \'' . $now . '\' AND a.end_time >= \'' . $now . '\' AND a.is_finished < 2 AND a.review_status = 3 ORDER BY ' . $sort . ' ' . $order);
	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

	foreach ($res as $row) {
		$ext_info = unserialize($row['ext_info']);
		$auction = array_merge($row, $ext_info);
		$auction['status_no'] = auction_status($auction);
		$auction['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $auction['start_time']);
		$auction['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $auction['end_time']);
		$auction['formated_start_price'] = price_format($auction['start_price']);
		$auction['formated_end_price'] = price_format($auction['end_price']);
		$auction['formated_deposit'] = price_format($auction['deposit']);
		$auction['goods_thumb'] = get_image_path($row['goods_thumb']);
		$auction['url'] = build_uri('auction', array('auid' => $auction['act_id']));

		if (1 < $auction['status_no']) {
			$sql = 'SELECT COUNT(*)' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE extension_code = \'auction\'' . (' AND extension_id = \'' . $auction['act_id'] . '\'') . ' AND order_status ' . db_create_in(array(OS_CONFIRMED, OS_UNCONFIRMED));
			$auction['order_count'] = $GLOBALS['db']->getOne($sql);
		}
		else {
			$auction['order_count'] = 0;
		}

		$sql = 'SELECT COUNT(DISTINCT bid_user) FROM ' . $GLOBALS['ecs']->table('auction_log') . (' WHERE act_id = \'' . $auction['act_id'] . '\'');
		$auction['bid_user_count'] = $GLOBALS['db']->getOne($sql);

		if (0 < $auction['bid_user_count']) {
			$sql = 'SELECT a.*, u.user_name ' . 'FROM ' . $GLOBALS['ecs']->table('auction_log') . ' AS a, ' . $GLOBALS['ecs']->table('users') . ' AS u ' . 'WHERE a.bid_user = u.user_id ' . ('AND act_id = \'' . $auction['act_id'] . '\' ') . 'ORDER BY a.log_id DESC';
			$row = $GLOBALS['db']->getRow($sql);
			$row['formated_bid_price'] = price_format($row['bid_price'], false);
			$row['bid_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['bid_time']);
			$auction['last_bid'] = $row;
		}

		$auction['is_winner'] = 0;

		if ($auction['last_bid']['bid_user']) {
			if ($auction['status_no'] == FINISHED && $auction['last_bid']['bid_user'] == $_SESSION['user_id'] && $auction['order_count'] == 0) {
				$auction['is_winner'] = 1;
			}
		}

		if ($auction['status_no'] < 2) {
			$auction_list['under_way'][] = $auction;
		}
		else {
			$auction_list['finished'][] = $auction;
		}
	}

	if ($auction_list['under_way']) {
		$auction_list = @array_merge($auction_list['under_way'], $auction_list['finished']);
	}
	else {
		$auction_list = $auction_list['finished'];
	}

	return $auction_list;
}

function get_exchange_recommend_goods($type = '', $cats = '', $min = 0, $max = 0, $ext = '')
{
	$price_where = 0 < $min ? ' AND g.shop_price >= ' . $min . ' ' : '';
	$price_where .= 0 < $max ? ' AND g.shop_price <= ' . $max . ' ' : '';
	$now = gmtime();
	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, ' . 'g.goods_brief, g.goods_thumb, goods_img, b.brand_name, ' . 'ga.act_name, ga.act_id, ga.ext_info, ga.start_time, ga.start_time, ga.end_time ' . 'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS ga ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = ga.goods_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' . 'WHERE ga.act_type = \'' . GAT_AUCTION . '\' ' . ('AND ga.start_time <= \'' . $now . '\' AND ga.end_time >= \'' . $now . '\' AND ga.is_finished < 2 AND ga.review_status = 3') . $price_where . $ext;
	$num = 0;
	$type2lib = array('best' => 'auction_best', 'new' => 'auction_new', 'hot' => 'auction_hot');
	$num = 5;

	switch ($type) {
	case 'best':
		$sql .= ' AND ga.is_best = 1';
		break;

	case 'new':
		$sql .= ' AND ga.is_new = 1';
		break;

	case 'hot':
		$sql .= ' AND ga.is_hot = 1';
		break;
	}

	if (!empty($cats)) {
	}

	$order_type = $GLOBALS['_CFG']['recommend_order'];
	$sql .= $order_type == 0 ? ' ORDER BY g.sort_order, g.last_update DESC' : ' ORDER BY RAND()';
	$res = $GLOBALS['db']->selectLimit($sql, $num);
	$idx = 0;
	$auction = array();

	foreach ($res as $row) {
		$auction[$idx]['id'] = $row['goods_id'];
		$auction[$idx]['name'] = $row['goods_name'];
		$auction[$idx]['brief'] = $row['goods_brief'];
		$auction[$idx]['brand_name'] = $row['brand_name'];
		$auction[$idx]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$auction[$idx]['exchange_integral'] = $row['exchange_integral'];
		$auction[$idx]['thumb'] = get_image_path($row['goods_thumb']);
		$auction[$idx]['goods_img'] = get_image_path($row['goods_img']);
		$auction[$idx]['url'] = build_uri('auction', array('auid' => $row['act_id'], 0 => $row['act_name']));
		$auction[$idx]['format_start_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['start_time']);
		$auction[$idx]['format_end_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['end_time']);
		$ext_info = unserialize($row['ext_info']);
		$auction_info = array_merge($row, $ext_info);
		$auction[$idx]['auction'] = $auction_info;
		$auction[$idx]['status_no'] = auction_status($auction_info);
		$auction[$idx]['start_price'] = price_format($auction_info['start_price']);
		$auction[$idx]['count'] = auction_log($row['act_id'], 1);
		$auction[$idx]['short_style_name'] = add_style($auction[$idx]['short_name'], $row['goods_name_style']);
		$idx++;
	}

	return $auction;
}


?>
