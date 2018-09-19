<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function wholesale_list($size, $page, $where, $where_sort, $countSql = '', $sort, $order)
{
	$list = array();
	$sql = 'SELECT w.*, g.goods_thumb, g.goods_name as goods_name ' . $countSql . ' FROM ' . $GLOBALS['ecs']->table('wholesale') . ' AS w, ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $where . ' AND w.goods_id = g.goods_id AND w.review_status = 3 ' . $where_sort;
	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

	foreach ($res as $row) {
		$sql = 'SELECT shop_price FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_id = ' . $row['goods_id'];
		$res = $GLOBALS['db']->getRow($sql);
		$row['format_shop_price'] = price_format($res['shop_price']);

		if (empty($row['goods_thumb'])) {
			$row['goods_thumb'] = C('no_picture');
		}
		else {
			$row['goods_thumb'] = get_image_path($row['goods_thumb']);
		}

		$row['goods_url'] = url('detail', array('id' => $row['act_id']));
		$properties = get_goods_properties($row['goods_id']);
		$row['goods_attr'] = $properties['pro'];
		$price_ladder = get_price_ladder($row['goods_id']);
		$temp = '';

		foreach ($price_ladder as $k => $v) {
			foreach ($v['qp_list'] as $qk => $qv) {
				if (($temp == '') || ((int) $qv < $temp)) {
					$temp = $qv;
				}
			}
		}

		$tap = array_values($price_ladder[0]['qp_list']);
		$row['price_ladder'] = $price_ladder;
		$row['qp_list_min'] = $tap[0];
		$list[] = $row;
	}

	if ($order == 'prices') {
		if ($sort == 'DESC') {
			array_multisort(array_column($list, 'qp_list_min'), SORT_DESC, $list);
		}
		else {
			array_multisort(array_column($list, 'qp_list_min'), SORT_ASC, $list);
		}
	}

	foreach ($list as $key => $val) {
		$list[$key]['qp_list_min'] = price_format($val['qp_list_min']);
	}

	return $list;
}

function get_price_ladder($goods_id)
{
	$goods_attr_list = array_values(get_goods_attr($goods_id));
	$sql = 'SELECT prices FROM ' . $GLOBALS['ecs']->table('wholesale') . 'WHERE review_status = 3 and goods_id = ' . $goods_id;
	$row = $GLOBALS['db']->getRow($sql);
	$arr = array();
	$_arr = unserialize($row['prices']);

	if (is_array($_arr)) {
		foreach (unserialize($row['prices']) as $key => $val) {
			if (!empty($val['attr'])) {
				foreach ($val['attr'] as $attr_key => $attr_val) {
					$goods_attr = array();

					foreach ($goods_attr_list as $goods_attr_val) {
						if ($goods_attr_val['attr_id'] == $attr_key) {
							$goods_attr = $goods_attr_val;
							break;
						}
					}

					if (!empty($goods_attr)) {
						$arr[$key]['attr'][] = array('attr_id' => $goods_attr['attr_id'], 'attr_name' => $goods_attr['attr_name'], 'attr_val' => isset($goods_attr['goods_attr_list'][$attr_val]) ? $goods_attr['goods_attr_list'][$attr_val] : '', 'attr_val_id' => $attr_val);
					}
				}
			}

			foreach ($val['qp_list'] as $index => $qp) {
				$arr[$key]['qp_list'][$qp['quantity']] = $qp['price'];
			}
		}
	}

	return $arr;
}

function is_attr_matching(&$goods_list, $reference)
{
	foreach ($goods_list as $key => $goods) {
		if (count($goods['goods_attr']) != count($reference)) {
			break;
		}

		$is_check = true;

		if (is_array($goods['goods_attr'])) {
			foreach ($goods['goods_attr'] as $attr) {
				if (!(array_key_exists($attr['attr_id'], $reference) && ($attr['attr_val_id'] == $reference[$attr['attr_id']]))) {
					$is_check = false;
					break;
				}
			}
		}

		if ($is_check) {
			return $key;
			break;
		}
	}

	return false;
}


?>
