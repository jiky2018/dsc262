<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
define('INIT_NO_USERS', true);
require dirname(__FILE__) . '/includes/init.php';

if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}

require ROOT_PATH . '/includes/lib_area.php';
$charset = (empty($_GET['charset']) ? EC_CHARSET : $_GET['charset']);
$type = (empty($_GET['type']) ? '' : 'collection');

if (strtolower($charset) == 'gb2312') {
	$charset = 'gbk';
}

header('content-type: application/x-javascript; charset=' . ($charset == 'UTF8' ? 'utf-8' : $charset));
$cache_id = sprintf('%X', crc32($_SERVER['QUERY_STRING']));
$tpl = ROOT_PATH . DATA_DIR . '/goods_script.html';

if (!$smarty->is_cached($tpl, $cache_id)) {
	$time = gmtime();
	$sql = '';

	if ($type == '') {
		$sitename = (!empty($_GET['sitename']) ? $_GET['sitename'] : '');
		$_from = (!empty($_GET['charset']) && ($_GET['charset'] != 'UTF8') ? urlencode(ecs_iconv('UTF-8', 'GBK', $sitename)) : urlencode(@$sitename));
		$goods_url = $ecs->url() . 'affiche.php?ad_id=-1&amp;from=' . $_from . '&amp;goods_id=';
		$sql = 'SELECT goods_id, goods_name, market_price, goods_thumb, RAND() AS rnd, ' . 'IF(is_promote = 1 AND \'' . $time . '\' >= promote_start_date AND ' . '\'' . $time . '\' <= promote_end_date, promote_price, shop_price) AS goods_price ' . 'FROM ' . $ecs->table('goods') . ' AS g ' . 'WHERE is_delete = \'0\' AND is_on_sale = \'1\' AND is_alone_sale = \'1\' ';

		if (!empty($_GET['cat_id'])) {
			$sql .= ' AND ' . get_children(intval($_GET['cat_id']));
		}

		if (!empty($_GET['brand_id'])) {
			$sql .= ' AND brand_id = \'' . intval($_GET['brand_id']) . '\'';
		}

		if (!empty($_GET['intro_type'])) {
			$_GET['intro_type'] = trim($_GET['intro_type']);
			if (($_GET['intro_type'] == 'is_best') || ($_GET['intro_type'] == 'is_new') || ($_GET['intro_type'] == 'is_hot') || ($_GET['intro_type'] == 'is_promote') || ($_GET['intro_type'] == 'is_random')) {
				if ($_GET['intro_type'] == 'is_random') {
					$sql .= ' ORDER BY rnd';
				}
				else {
					if ($_GET['intro_type'] == 'is_promote') {
						$sql .= ' AND promote_start_date <= \'' . $time . '\' AND promote_end_date >= \'' . $time . '\'';
					}

					$sql .= ' AND ' . $_GET['intro_type'] . ' = 1 ORDER BY add_time DESC';
				}
			}
		}
	}
	else if ($type == 'collection') {
		$uid = (int) $_GET['u'];
		$goods_url = $ecs->url() . 'goods.php?u=' . $uid . '&id=';
		$sql = 'SELECT g.goods_id, g.goods_name, g.market_price, g.goods_thumb, IF(g.is_promote = 1 AND \'' . $time . '\' >= g.promote_start_date AND ' . '\'' . $time . '\' <= g.promote_end_date, g.promote_price, g.shop_price) AS goods_price FROM ' . $ecs->table('goods') . ' g LEFT JOIN ' . $ecs->table('collect_goods') . ' c ON g.goods_id = c.goods_id ' . ' WHERE c.user_id = \'' . $uid . '\'';
	}

	$sql .= ' LIMIT ' . (!empty($_GET['goods_num']) ? intval($_GET['goods_num']) : 10);
	$res = $db->query($sql);
	$goods_list = array();

	while ($goods = $db->fetchRow($res)) {
		$goods['goods_price'] = price_format($goods['goods_price']);

		if ($charset != EC_CHARSET) {
			if (EC_CHARSET == 'gbk') {
				$tmp_goods_name = htmlentities($goods['goods_name'], ENT_QUOTES, 'gb2312');
			}
			else {
				$tmp_goods_name = htmlentities($goods['goods_name'], ENT_QUOTES, EC_CHARSET);
			}

			$goods['goods_name'] = ecs_iconv(EC_CHARSET, $charset, $tmp_goods_name);
			$goods['goods_price'] = ecs_iconv(EC_CHARSET, $charset, $goods['goods_price']);
		}

		$goods['goods_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($goods['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $goods['goods_name'];
		$goods['goods_thumb'] = get_image_path($goods['goods_id'], $goods['goods_thumb'], true);
		$goods_list[] = $goods;
	}

	$arrange = (empty($_GET['arrange']) || !in_array($_GET['arrange'], array('h', 'v')) ? 'h' : $_GET['arrange']);
	$goods_num = (!empty($_GET['goods_num']) ? intval($_GET['goods_num']) : 10);
	$rows_num = (!empty($_GET['rows_num']) ? intval($_GET['rows_num']) : '1');

	if ($arrange == 'h') {
		$goods_items = array_chunk($goods_list, $rows_num);
	}
	else {
		$columns_num = ceil($goods_num / $rows_num);
		$goods_items = array_chunk($goods_list, $columns_num);
	}

	$smarty->assign('goods_list', $goods_items);
	$need_image = (empty($_GET['need_image']) || ($_GET['need_image'] == 'true') ? 1 : 0);
	$smarty->assign('need_image', $need_image);
	$smarty->assign('thumb_width', intval($_CFG['thumb_width']));
	$smarty->assign('thumb_height', intval($_CFG['thumb_height']));
	$smarty->assign('url', $ecs->url());
	$smarty->assign('goods_url', $goods_url);
}

$output = $smarty->fetch($tpl, $cache_id);
$output = str_replace("\r", '', $output);
$output = str_replace("\n", '', $output);
echo 'document.write(\'' . $output . '\');';

?>
