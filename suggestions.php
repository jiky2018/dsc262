<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function cut_str($string, $sublen, $start = 0, $code = 'gbk')
{
	if ($code == 'utf-8') {
		$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
		preg_match_all($pa, $string, $t_string);

		if ($sublen < count($t_string[0]) - $start) {
			return join('', array_slice($t_string[0], $start, $sublen)) . '...';
		}

		return join('', array_slice($t_string[0], $start, $sublen));
	}
	else {
		$start = $start * 2;
		$sublen = $sublen * 2;
		$strlen = strlen($string);
		$tmpstr = '';

		for ($i = 0; $i < $strlen; $i++) {
			if ($start <= $i && $i < $start + $sublen) {
				if (129 < ord(substr($string, $i, 1))) {
					$tmpstr .= substr($string, $i, 2);
				}
				else {
					$tmpstr .= substr($string, $i, 1);
				}
			}

			if (129 < ord(substr($string, $i, 1))) {
				$i++;
			}
		}

		if (strlen($tmpstr) < $strlen) {
			$tmpstr .= '';
		}

		return $tmpstr;
	}
}

define('IN_ECS', true);

if (!function_exists('htmlspecialchars_decode')) {
	function htmlspecialchars_decode($string, $quote_style = ENT_COMPAT)
	{
		return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
	}
}

require dirname(__FILE__) . '/includes/init.php';
require_once dirname(__FILE__) . '/includes/cls_json.php';
$json = new JSON();
$keyword = empty($_POST['keyword']) ? '' : trim($_POST['keyword']);
$keyword = !empty($keyword) ? dsc_addslashes($keyword) : '';
$category = empty($_POST['category']) ? 0 : trim($_POST['category']);

if ($category == $_LANG['all_attribute']) {
	$children = '';
	$parent = '';
}
else if ($category == $_LANG['Template']) {
	$children = get_children(9);
	$children = str_replace('g.', ' AND ', $children);
	$parent = ' AND parent_id = 9';
}
else if ($category == $_LANG['plugins']) {
	$children = get_children(23);
	$children = str_replace('g.', ' AND ', $children);
	$parent = ' AND parent_id = 23';
}
else {
	$children = '';
	$parent = '';
}

if (empty($keyword)) {
	echo '';
	exit();
}
else {
	$goods_where = ' AND g.is_show = 1 ';

	if ($GLOBALS['_CFG']['review_goods'] == 1) {
		$goods_where .= ' AND g.review_status > 2 ';
	}

	$sql = 'SELECT distinct keyword FROM ' . $ecs->table('search_keyword') . 'WHERE keyword LIKE \'%' . mysql_like_quote($keyword) . '%\' OR pinyin_keyword LIKE \'%' . mysql_like_quote($keyword) . '%\' ORDER BY count DESC';
	$result = $db->selectLimit($sql, 10);
	$sql = 'SELECT cat_id, cat_name, parent_id FROM ' . $ecs->table('category') . ' WHERE cat_name LIKE \'%' . mysql_like_quote($keyword) . '%\' OR pinyin_keyword LIKE \'%' . mysql_like_quote($keyword) . ('%\' ' . $children . ' limit 0,4');
	$cate_res = $db->getAll($sql);
	$cat_html = '';

	foreach ($cate_res as $key => $row) {
		if (0 < $row['parent_id']) {
			$sql_1 = 'SELECT cat_name FROM ' . $ecs->table('category') . 'WHERE cat_id=' . $row['parent_id'];
			$parent_res = $db->getRow($sql_1);
			$url = build_uri('category', array('cid' => $row['cat_id']));

			if ($url == '') {
				$url = '#';
			}

			$cat_html .= '<li onmouseover="_over(this);" onmouseout="_out(this);">' . '&nbsp;&nbsp;&nbsp;在<a class=\'cate_user\' href=' . $url . ' style=\'color:#ec5151;\'>' . $parent_res['cat_name'] . '>' . $row['cat_name'] . '</a>' . $_LANG['cat_search'] . '</li>';
		}
	}

	$html = '<ul id="suggestions_list_id"><input type="hidden" value="1" name="selectKeyOne" id="keyOne" />';
	$res_num = 0;
	$exist_keyword = array();

	while ($row = $db->FetchRow($result)) {
		$scws_res = scws($row['keyword']);
		$arr = explode(',', $scws_res);
		$operator = ' AND ';
		$insert_keyword = trim($row['keyword']);

		if (empty($arr[0])) {
			$arr[0] = $insert_keyword;
		}

		$keywords = 'AND (';
		$goods_ids = array();

		foreach ($arr as $key => $val) {
			$val = !empty($val) ? dsc_addslashes($val) : '';
			if (0 < $key && $key < count($arr) && 1 < count($arr)) {
				$keywords .= $operator;
			}

			$val = mysql_like_quote(trim($val));
			$keywords .= '(goods_name LIKE \'%' . $val . '%\' OR goods_sn LIKE \'%' . $val . '%\' OR keywords LIKE \'%' . $val . '%\' ' . $sc_dsad . ')';
			$sql = 'SELECT DISTINCT goods_id FROM ' . $ecs->table('tag') . (' WHERE tag_words LIKE \'%' . $val . '%\' ');
			$res = $db->query($sql);

			while ($rows = $db->FetchRow($res)) {
				$goods_ids[] = $rows['goods_id'];
			}
		}

		$keywords .= ')';
		require ROOT_PATH . '/includes/lib_area.php';
		$area_info = get_area_info($province_id);
		$area_id = $area_info['region_id'];
		$where = 'regionId = \'' . $province_id . '\'';
		$date = array('parent_id');
		$region_id = get_table_date('region_warehouse', $where, $date, 2);
		$categories = '';
		$brand = '';
		$price_min = '';
		$price_max = '';
		$intro = '';
		$outstock = '';
		$tag_where = '';
		$leftJoin = '';
		$shop_price = 'wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr, ';
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . (' as wg on g.goods_id = wg.goods_id and wg.region_id = \'' . $region_id . '\' ');
		$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . (' as wag on g.goods_id = wag.goods_id and wag.region_id = \'' . $area_id . '\' ');
		$area_where = '';

		if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
			$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('link_area_goods') . ' as lag on g.goods_id = lag.goods_id ';
			$area_where = ' and lag.region_id = \'' . $area_id . '\' ';
		}

		if ($GLOBALS['_CFG']['review_goods'] == 1) {
			$tag_where .= ' AND g.review_status > 2 ';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('goods') . ' AS g ' . $leftJoin . 'WHERE g.is_delete = 0 AND g.is_on_sale = 1 ' . $area_where . (' AND g.is_alone_sale = 1 ' . $attr_in . ' ') . $goods_where . 'AND (( 1 ' . $categories . $keywords . $brand . $price_min . $price_max . $intro . $outstock . ' ) ' . $tag_where . ' )';
		$count = $db->getOne($sql);

		if ($count <= 0) {
			continue;
		}

		$keyword = preg_quote($keyword);
		$keyword_style = preg_replace('/(' . $keyword . ')/i', '<font style=\'font-weight:normal;color:#ec5151;\'>$1</font>', $row['keyword']);
		$keyword_string = '<font style=\'font-weight:;\'>' . $keyword . '</font>';
		$keyword_name = str_replace($keyword, $keyword_string, $weight_keyword);
		$html .= '<li onmouseover="_over(this);" title="' . $row['keyword'] . '" onmouseout="_out(this);" onClick="javascript:fill(\'' . $row['keyword'] . '\');"><div class="left-span">&nbsp;' . $keyword_style . '</div><div class="suggest_span">约' . $count . '个商品</div></li>';
		$res_num++;
		$exist_keyword[] = $row['keyword'];
	}

	if (isset($cat_html) && $cat_html != '') {
		$html .= $cat_html;
		$html .= '<li style="height:1px; overflow:hidden; border-bottom:1px #eee solid; margin-top:-1px;"></li>';
		unset($cat_html);
	}

	if ($res_num < 10) {
		$sql = 'SELECT distinct g.goods_name FROM ' . $ecs->table('goods') . ' AS g ' . (' WHERE g.goods_name like \'%' . $keyword . '%\' OR g.pinyin_keyword LIKE \'%' . $keyword . '%\' AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1') . $goods_where;
		$keyword_res = $db->getAll($sql);
		$res_count = count($keyword_res);

		if ($res_count <= 0) {
			$html .= '</ul>';

			if ($html == '<ul id="suggestions_list_id"><input type="hidden" value="1" name="selectKeyOne" id="keyOne" /></ul>') {
				$html = '';
			}

			echo $html;
			exit();
		}

		$len = 10 - $res_num;

		for ($i = 0; $i < $len; $i++) {
			if ($res_count == $i) {
				break;
			}

			$scws_res = scws($keyword_res[$i]['goods_name']);
			$arr = explode(',', $scws_res);
			$operator = ' AND ';
			$keywords = 'AND (';
			$goods_ids = array();

			foreach ($arr as $key => $val) {
				$val = !empty($val) ? dsc_addslashes($val) : '';
				if (0 < $key && $key < count($arr) && 1 < count($arr)) {
					$keywords .= $operator;
				}

				$val = mysql_like_quote(trim($val));
				$keywords .= '(g.goods_name LIKE \'%' . $val . '%\' OR g.goods_sn LIKE \'%' . $val . '%\' OR g.keywords LIKE \'%' . $val . '%\' ' . $sc_dsad . ')';
				$sql = 'SELECT DISTINCT goods_id FROM ' . $ecs->table('tag') . (' WHERE tag_words LIKE \'%' . $val . '%\' ');
				$res = $db->query($sql);

				while ($rows = $db->FetchRow($res)) {
					$goods_ids[] = $rows['goods_id'];
				}
			}

			$keywords .= ')';
			$count = $db->getOne('SELECT count(*) FROM ' . $ecs->table('goods') . ' AS g ' . (' WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 ' . $keywords . ' ' . $goods_where));

			if ($count <= 0) {
				continue;
			}

			if (in_array($keyword_res[$i]['goods_name'], $exist_keyword)) {
				continue;
			}

			$keyword_new_name = $keyword_res[$i]['goods_name'];
			cut_str($keyword_new_name, 25);
			$keyword_style = preg_replace('/(' . $keyword . ')/i', '<font style=\'font-weight:normal;color:#ec5151;\'>$1</font>', $keyword_new_name);
			$html .= '<li onmouseover="_over(this);" onmouseout="_out(this);" title="' . $keyword_new_name . '" onClick="javascript:fill(\'' . $keyword_new_name . '\');"><div class="left-span">&nbsp;' . $keyword_style . '</div>&nbsp;<b>' . '</b>' . '<div class="suggest_span">约' . $count . '个商品</div></li>';
		}
	}

	$html .= '</ul>';

	if ($html == '<ul id="suggestions_list_id"><input type="hidden" value="1" name="selectKeyOne" id="keyOne" /></ul>') {
		$html = '';
	}

	echo $html;
	exit();
}

?>
