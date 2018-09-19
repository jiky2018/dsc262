<?php
//zend by QQ:123456  商创-网络  禁止倒卖 一经发现停止任何服务
function get_bonus_list($num = 10, $page = 1, $status = 4)
{
	$time = gmtime();
	$sql = 'SELECT count(*) as num FROM ' . $GLOBALS['ecs']->table('bonus_type') . (' WHERE  send_type =  \'' . $status . '\' and  ' . $time . ' < send_end_date and ' . $time . ' > send_start_date and review_status = 3');
	$total = $GLOBALS['db']->getOne($sql);
	$total = !empty($total) ? $total : 0;
	$start = ($page - 1) * $num;
	$sql = 'SELECT bt.* ,s.shop_name FROM ' . $GLOBALS['ecs']->table('bonus_type') . ' as bt left join ' . $GLOBALS['ecs']->table('seller_shopinfo') . (' as s on bt.user_id =s.ru_id WHERE  bt.send_type = \'' . $status . '\'  and  ' . $time . ' < bt.send_end_date and bt.review_status = 3 and  ' . $time . ' > bt.send_start_date ') . (' order by bt.type_id DESC limit ' . $start . ',' . $num);
	$tab = $GLOBALS['db']->getAll($sql);

	foreach ($tab as $k => $v) {
		$tab[$k]['begintime'] = local_date('Y-m-d ', $v['send_start_date']);
		$tab[$k]['endtime'] = local_date('Y-m-d ', $v['send_end_date']);
		$tab[$k]['min_goods_amount'] = $v['min_goods_amount'];
		$tab[$k]['type_money'] = price_format($v['type_money'], true);

		if ($v['usebonus_type'] == 0) {
			$tab[$k]['shop_name'] = sprintf(L('use_limit'), get_shop_name($v['user_id'], 1));
		}
		else {
			$tab[$k]['shop_name'] = '全场通用';
		}

		if ($_SESSION['user_id']) {
			$sql = 'SELECT bonus_id FROM ' . $GLOBALS['ecs']->table('user_bonus') . ' WHERE bonus_type_id = \'' . $v['type_id'] . ('\' AND user_id = \'' . $_SESSION['user_id'] . '\' LIMIT 1 ');
			$exist = $GLOBALS['db']->getOne($sql);
			$tab[$k]['is_receive'] = !empty($exist) && 0 < $exist ? 1 : 0;
		}

		$sql = 'SELECT COUNT(bonus_id) FROM {pre}user_bonus WHERE bonus_type_id = \'' . $v['type_id'] . '\' AND user_id = 0 LIMIT 1 ';
		$left_num = $GLOBALS['db']->getOne($sql);
		$tab[$k]['is_left'] = !empty($left_num) && 0 < $left_num ? 1 : 0;
	}

	return array('tab' => $tab, 'totalpage' => ceil($total / $num));
}


?>
