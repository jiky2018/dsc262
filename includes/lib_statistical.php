<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function statistical_field_shop_num()
{
	return 'COUNT(DISTINCT spi.ru_id)';
}

function statistical_field_order_num()
{
	return 'COUNT(DISTINCT o.order_id)';
}

function statistical_field_user_num($alias = 'o.')
{
	return 'COUNT(DISTINCT ' . $alias . 'user_id)';
}

function statistical_field_return_num()
{
	return 'COUNT(DISTINCT re.order_id)';
}

function statistical_field_goods_num()
{
	return 'COUNT(DISTINCT g.goods_id)';
}

function statistical_field_order_goods_num()
{
	return 'COUNT(DISTINCT og.goods_id)';
}

function statistical_field_no_order_goods_num()
{
	return statistical_field_goods_num() . '-' . statistical_field_order_goods_num();
}

function statistical_field_valid_num()
{
	return 'COUNT(DISTINCT IF(o.order_status!=' . OS_INVALID . ', o.order_id, NULL))';
}

function statistical_field_total_fee()
{
	return 'SUM(' . order_amount_field('o.') . ')';
}

function statistical_field_valid_fee()
{
	return 'SUM(IF(o.order_status!=' . OS_INVALID . ', ' . order_amount_field('o.') . ', 0))';
}

function statistical_field_return_fee()
{
	return 'SUM(re.actual_return)';
}

function statistical_field_sale_money()
{
	return 'SUM(o.money_paid + o.surplus)';
}

function statistical_field_order_goods_number()
{
	return 'SUM(og.goods_number)';
}

function statistical_field_order_goods_amount()
{
	return 'SUM(og.goods_number * og.goods_price)';
}

function statistical_field_goods_amount()
{
	return 'SUM(o.goods_amount)';
}

function statistical_field_valid_goods_amount()
{
	return '(' . statistical_field_order_goods_amount() . '/' . statistical_field_goods_amount() . '*' . statistical_field_valid_fee() . ')';
}

function statistical_field_average_goods_price()
{
	return 'ROUND(' . statistical_field_order_goods_amount() . '/' . statistical_field_order_goods_number() . ', 2)';
}

function statistical_field_average_total_fee()
{
	return 'ROUND(' . statistical_field_total_fee() . '/' . statistical_field_order_num() . ', 2)';
}

function statistical_field_average_valid_fee()
{
	return 'ROUND(' . statistical_field_valid_fee() . '/' . statistical_field_valid_num() . ', 2)';
}

function statistical_field_user_recharge_money()
{
	return 'SUM(IF(al.change_type=0, al.user_money, 0))';
}

function statistical_field_user_consumption_money()
{
	return 'SUM(IF(al.change_type=99 AND al.user_money<0, al.user_money, 0))';
}

function statistical_field_user_cash_money()
{
	return 'SUM(IF(al.change_type=1, al.frozen_money, 0))';
}

function statistical_field_user_return_money()
{
	return 'SUM(IF(al.change_type=99 AND al.user_money>0, al.user_money, 0))';
}

function statistical_field_user_money()
{
	return 'SUM(u.user_money)';
}

function child_query_ru_id()
{
	return '(SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og' . ' WHERE og.order_id = o.order_id LIMIT 1)';
}

function no_main_order()
{
	return ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
}

function get_time_diff()
{
	$timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];
	$time_diff = $timezone * 3600;
	return $time_diff;
}

function get_statistical_new_shop($search_data = array())
{
	$data = array();
	$where_data = '';
	if (isset($search_data['shop_categoryMain']) && !empty($search_data['shop_categoryMain'])) {
		$where_data .= ' AND msi.shop_categoryMain = \'' . $search_data['shop_categoryMain'] . '\' ';
	}

	if (isset($search_data['shopNameSuffix']) && !empty($search_data['shopNameSuffix'])) {
		$where_data .= ' AND msi.shopNameSuffix = \'' . $search_data['shopNameSuffix'] . '\' ';
	}

	$date_start = $search_data['start_date'];
	$date_end = $search_data['end_date'];
	$time_diff = get_time_diff();
	$day_num = ceil($date_end - $date_start) / 86400;
	$sql = 'SELECT FROM_UNIXTIME(msi.add_time+' . $time_diff . ',\'%y-%m-%d\') AS day, COUNT(*) AS count FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS msi' . ' WHERE msi.add_time BETWEEN ' . $date_start . ' AND ' . $date_end . $where_data . ' GROUP BY day ORDER BY day ASC ';
	$result = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($result)) {
		$series_data[$row['day']] = intval($row['count']);
	}

	for ($i = 1; $i <= $day_num; $i++) {
		$day = local_date('y-m-d', local_strtotime(' - ' . ($day_num - $i) . ' days'));

		if (empty($series_data[$day])) {
			$series_data[$day] = 0;
		}

		$day = local_date('m-d', local_strtotime($day));
		$xAxis_data[] = $day;
	}

	$title = array('text' => '', 'subtext' => '');
	$toolbox = array(
		'show'    => true,
		'orient'  => 'vertical',
		'x'       => 'right',
		'y'       => '60',
		'feature' => array(
			'magicType'   => array(
				'show' => true,
				'type' => array('line', 'bar')
				),
			'saveAsImage' => array('show' => true)
			)
		);
	$tooltip = array(
		'trigger'     => 'axis',
		'axisPointer' => array(
			'lineStyle' => array('color' => '#6cbd40')
			)
		);
	$xAxis = array(
		'type'        => 'category',
		'boundaryGap' => false,
		'axisLine'    => array(
			'lineStyle' => array('color' => '#ccc', 'width' => 0)
			),
		'data'        => array()
		);
	$yAxis = array(
		'type'      => 'value',
		'axisLine'  => array(
			'lineStyle' => array('color' => '#ccc', 'width' => 0)
			),
		'axisLabel' => array('formatter' => '')
		);
	$series = array(
		array(
			'name'      => '',
			'type'      => 'line',
			'itemStyle' => array(
				'normal' => array(
					'color'     => '#6cbd40',
					'lineStyle' => array('color' => '#6cbd40')
					)
				),
			'data'      => array(),
			'markPoint' => array(
				'itemStyle' => array(
					'normal' => array('color' => '#6cbd40')
					),
				'data'      => array(
					array('type' => 'max', 'name' => '最大值'),
					array('type' => 'min', 'name' => '最小值')
					)
				)
			),
		array(
			'type'      => 'force',
			'name'      => '',
			'draggable' => false,
			'nodes'     => array('draggable' => false)
			)
		);
	$calculable = true;
	$legend = array(
		'data' => array()
		);
	$title['text'] = '';
	$xAxis['data'] = $xAxis_data;
	$yAxis['formatter'] = '{value}个';
	ksort($series_data);
	$series[0]['name'] = '新增店铺';
	$series[0]['data'] = array_values($series_data);
	$data['title'] = $title;
	$data['series'] = $series;
	$data['tooltip'] = $tooltip;
	$data['legend'] = $legend;
	$data['toolbox'] = $toolbox;
	$data['calculable'] = $calculable;
	$data['xAxis'] = $xAxis;
	$data['yAxis'] = $yAxis;
	$data['xy_file'] = get_dir_file_list();
	return $data;
}

function shop_sale_stats($page = 0)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['start_date'] = empty($_REQUEST['start_date']) ? '' : (0 < strpos($_REQUEST['start_date'], '-') ? local_strtotime($_REQUEST['start_date']) : $_REQUEST['start_date']);
		$filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (0 < strpos($_REQUEST['end_date'], '-') ? local_strtotime($_REQUEST['end_date']) : $_REQUEST['end_date']);
		$filter['shop_categoryMain'] = empty($_REQUEST['shop_categoryMain']) ? 0 : intval($_REQUEST['shop_categoryMain']);
		$filter['shopNameSuffix'] = empty($_REQUEST['shopNameSuffix']) ? '' : trim($_REQUEST['shopNameSuffix']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'spi.ru_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);
		if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$where_msi = ' WHERE 1 ';
		$where_o = '';

		if ($filter['start_date']) {
			$where_o .= ' AND o.add_time >= \'' . $filter['start_date'] . '\'';
		}

		if ($filter['end_date']) {
			$where_o .= ' AND o.add_time <= \'' . $filter['end_date'] . '\'';
		}

		if ($filter['keywords']) {
			$where_msi .= ' AND (msi.rz_shopName LIKE \'%' . $filter['keywords'] . '%\')';
		}

		if ($filter['shop_categoryMain']) {
			$where_msi .= ' AND msi.shop_categoryMain = \'' . $filter['shop_categoryMain'] . '\'';
		}

		if ($filter['shopNameSuffix']) {
			$where_msi .= ' AND msi.shopNameSuffix = \'' . $filter['shopNameSuffix'] . '\'';
		}

		$no_main_order = no_main_order();
		$where_o .= $no_main_order;
		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);

		if (0 < $page) {
			$filter['page'] = $page;
		}

		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$groupBy = ' GROUP BY spi.ru_id ';
		$leftJoin = '';
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS o ON ' . child_query_ru_id() . ' = spi.ru_id ';
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_return') . ' AS re ON re.order_id = o.order_id ';
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS msi ON msi.user_id = spi.ru_id ';
		$sql = 'SELECT spi.ru_id FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS spi ' . $leftJoin . $where_msi . $where_o . $groupBy;
		$record_count = count($GLOBALS['db']->getAll($sql));
		$filter['record_count'] = $record_count;
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = 'SELECT spi.ru_id, ' . statistical_field_order_num() . ' AS total_order_num, ' . statistical_field_user_num() . ' AS total_user_num, ' . statistical_field_return_num() . ' AS total_return_num, ' . statistical_field_valid_num() . ' AS total_valid_num, ' . statistical_field_total_fee() . ' AS total_fee, ' . statistical_field_valid_fee() . ' AS valid_fee, ' . statistical_field_return_fee() . ' AS return_amount ' . ' FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS spi ' . $leftJoin . $where_msi . $where_o . $groupBy . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $value) {
		$row[$key]['user_name'] = get_shop_name($value['ru_id'], 1);
		$row[$key]['formated_total_fee'] = price_format($value['total_fee']);
		$row[$key]['formated_valid_fee'] = price_format($value['valid_fee']);
		$row[$key]['formated_return_amount'] = price_format($value['return_amount']);
	}

	$arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function shop_area_stats($page = 0)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['start_date'] = empty($_REQUEST['start_date']) ? '' : (0 < strpos($_REQUEST['start_date'], '-') ? local_strtotime($_REQUEST['start_date']) : $_REQUEST['start_date']);
		$filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (0 < strpos($_REQUEST['end_date'], '-') ? local_strtotime($_REQUEST['end_date']) : $_REQUEST['end_date']);
		$filter['shop_categoryMain'] = empty($_REQUEST['shop_categoryMain']) ? 0 : intval($_REQUEST['shop_categoryMain']);
		$filter['shopNameSuffix'] = empty($_REQUEST['shopNameSuffix']) ? '' : trim($_REQUEST['shopNameSuffix']);
		$filter['area'] = empty($_REQUEST['area']) ? '' : trim($_REQUEST['area']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'store_num' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$where_spi = ' WHERE 1 ';
		$where_msi = '';

		if ($filter['start_date']) {
			$where_msi .= ' AND msi.add_time >= \'' . $filter['start_date'] . '\'';
		}

		if ($filter['end_date']) {
			$where_msi .= ' AND msi.add_time <= \'' . $filter['end_date'] . '\'';
		}

		if ($filter['keywords']) {
			$where_msi .= ' AND (msi.rz_shopName LIKE \'%' . $filter['keywords'] . '%\')';
		}

		if ($filter['shop_categoryMain']) {
			$where_msi .= ' AND msi.shop_categoryMain = \'' . $filter['shop_categoryMain'] . '\'';
		}

		if ($filter['shopNameSuffix']) {
			$where_msi .= ' AND msi.shopNameSuffix = \'' . $filter['shopNameSuffix'] . '\'';
		}

		if ($filter['area']) {
			$sql = ' SELECT region_id FROM ' . $GLOBALS['ecs']->table('merchants_region_info') . (' WHERE ra_id = \'' . $filter['area'] . '\' ');
			$region_ids = $GLOBALS['db']->getCol($sql);
			$where_spi .= ' AND spi.province ' . db_create_in($region_ids);
		}

		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);

		if (0 < $page) {
			$filter['page'] = $page;
		}

		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$groupBy = ' GROUP BY spi.district ';
		$leftJoin = '';
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS msi ON msi.user_id = spi.ru_id ';
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS r ON r.region_id = spi.district ';
		$sql = 'SELECT spi.district FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS spi ' . $leftJoin . $where_spi . $where_msi . $groupBy;
		$record_count = count($GLOBALS['db']->getAll($sql));
		$filter['record_count'] = $record_count;
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = 'SELECT spi.district, r.region_name as district_name, ' . statistical_field_shop_num() . ' AS store_num ' . ' FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS spi ' . $leftJoin . $where_spi . $where_msi . $groupBy . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $value) {
		$city_id = get_table_date('region', 'region_id=\'' . $value['district'] . '\'', array('parent_id'), 2);
		$row[$key]['city_name'] = get_table_date('region', 'region_id=\'' . $city_id . '\'', array('region_name'), 2);
		$province_id = get_table_date('region', 'region_id=\'' . $city_id . '\'', array('parent_id'), 2);
		$row[$key]['province_name'] = get_table_date('region', 'region_id=\'' . $province_id . '\'', array('region_name'), 2);
	}

	$arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function shop_total_stats()
{
	$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
	$filter['start_date'] = empty($_REQUEST['start_date']) ? '' : (0 < strpos($_REQUEST['start_date'], '-') ? local_strtotime($_REQUEST['start_date']) : $_REQUEST['start_date']);
	$filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (0 < strpos($_REQUEST['end_date'], '-') ? local_strtotime($_REQUEST['end_date']) : $_REQUEST['end_date']);
	$filter['shop_categoryMain'] = empty($_REQUEST['shop_categoryMain']) ? 0 : intval($_REQUEST['shop_categoryMain']);
	$filter['shopNameSuffix'] = empty($_REQUEST['shopNameSuffix']) ? '' : trim($_REQUEST['shopNameSuffix']);
	if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
		$filter['keywords'] = json_str_iconv($filter['keywords']);
	}

	$where_msi = ' WHERE 1 ';
	$where_o = '';

	if ($filter['start_date']) {
		$where_o .= ' AND o.add_time >= \'' . $filter['start_date'] . '\'';
	}

	if ($filter['end_date']) {
		$where_o .= ' AND o.add_time <= \'' . $filter['end_date'] . '\'';
	}

	if ($filter['keywords']) {
		$where_msi .= ' AND (msi.rz_shopName LIKE \'%' . $filter['keywords'] . '%\')';
	}

	if ($filter['shop_categoryMain']) {
		$where_msi .= ' AND msi.shop_categoryMain = \'' . $filter['shop_categoryMain'] . '\'';
	}

	if ($filter['shopNameSuffix']) {
		$where_msi .= ' AND msi.shopNameSuffix = \'' . $filter['shopNameSuffix'] . '\'';
	}

	$no_main_order = no_main_order();
	$where_o .= $no_main_order;
	$leftJoin = '';
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_return') . ' AS re ON re.order_id = o.order_id ';
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS msi ON msi.user_id = ' . child_query_ru_id();
	$sql = 'SELECT ' . statistical_field_order_num() . ' AS total_order_num, ' . statistical_field_user_num() . ' AS total_user_num, ' . statistical_field_return_num() . ' AS total_return_num, ' . statistical_field_valid_num() . ' AS total_valid_num, ' . statistical_field_total_fee() . ' AS total_fee, ' . statistical_field_valid_fee() . ' AS valid_fee, ' . statistical_field_return_fee() . ' AS return_amount ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $leftJoin . $where_msi . $where_o;
	$row = $GLOBALS['db']->getRow($sql);

	foreach ($row as $key => $val) {
		$row[$key] = !isset($val) || empty($val) ? '0' : $val;
	}

	return $row;
}

function get_statistical_new_user($search_data = array())
{
	$data = array();
	$where_data = '';
	$date_start = $search_data['start_date'];
	$date_end = $search_data['end_date'];
	$time_diff = get_time_diff();
	$day_num = ceil($date_end - $date_start) / 86400;
	$sql = 'SELECT FROM_UNIXTIME(u.reg_time+' . $time_diff . ',\'%y-%m-%d\') AS day, COUNT(*) AS count FROM ' . $GLOBALS['ecs']->table('users') . ' AS u' . ' WHERE u.reg_time BETWEEN ' . $date_start . ' AND ' . $date_end . $where_data . ' GROUP BY day ORDER BY day ASC ';
	$result = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($result)) {
		$series_data[$row['day']] = intval($row['count']);
	}

	for ($i = 1; $i <= $day_num; $i++) {
		$day = local_date('y-m-d', local_strtotime(' - ' . ($day_num - $i) . ' days'));

		if (empty($series_data[$day])) {
			$series_data[$day] = 0;
		}

		$day = local_date('m-d', local_strtotime($day));
		$xAxis_data[] = $day;
	}

	$title = array('text' => '', 'subtext' => '');
	$toolbox = array(
		'show'    => true,
		'orient'  => 'vertical',
		'x'       => 'right',
		'y'       => '60',
		'feature' => array(
			'magicType'   => array(
				'show' => true,
				'type' => array('line', 'bar')
				),
			'saveAsImage' => array('show' => true)
			)
		);
	$tooltip = array(
		'trigger'     => 'axis',
		'axisPointer' => array(
			'lineStyle' => array('color' => '#6cbd40')
			)
		);
	$xAxis = array(
		'type'        => 'category',
		'boundaryGap' => false,
		'axisLine'    => array(
			'lineStyle' => array('color' => '#ccc', 'width' => 0)
			),
		'data'        => array()
		);
	$yAxis = array(
		'type'      => 'value',
		'axisLine'  => array(
			'lineStyle' => array('color' => '#ccc', 'width' => 0)
			),
		'axisLabel' => array('formatter' => '')
		);
	$series = array(
		array(
			'name'      => '',
			'type'      => 'line',
			'itemStyle' => array(
				'normal' => array(
					'color'     => '#6cbd40',
					'lineStyle' => array('color' => '#6cbd40')
					)
				),
			'data'      => array(),
			'markPoint' => array(
				'itemStyle' => array(
					'normal' => array('color' => '#6cbd40')
					),
				'data'      => array(
					array('type' => 'max', 'name' => '最大值'),
					array('type' => 'min', 'name' => '最小值')
					)
				)
			),
		array(
			'type'      => 'force',
			'name'      => '',
			'draggable' => false,
			'nodes'     => array('draggable' => false)
			)
		);
	$calculable = true;
	$legend = array(
		'data' => array()
		);
	$title['text'] = '';
	$xAxis['data'] = $xAxis_data;
	$yAxis['formatter'] = '{value}个';
	ksort($series_data);
	$series[0]['name'] = '新增会员';
	$series[0]['data'] = array_values($series_data);
	$data['title'] = $title;
	$data['series'] = $series;
	$data['tooltip'] = $tooltip;
	$data['legend'] = $legend;
	$data['toolbox'] = $toolbox;
	$data['calculable'] = $calculable;
	$data['xAxis'] = $xAxis;
	$data['yAxis'] = $yAxis;
	$data['xy_file'] = get_dir_file_list();
	return $data;
}

function get_statistical_sale($search_data = array())
{
	$data = array();
	$data['total_volume'] = 0;
	$data['total_money'] = 0;
	$where_data = '';
	$date_start = $search_data['start_date'];
	$date_end = $search_data['end_date'];
	$time_diff = get_time_diff();
	$day_num = ceil($date_end - $date_start) / 86400;
	$no_main_order = no_main_order();
	$sql = ' SELECT FROM_UNIXTIME(o.add_time+' . $time_diff . ',\'%y-%m-%d\') AS day, ' . statistical_field_order_num() . ' AS volume, ' . statistical_field_sale_money() . ' AS money ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o' . ' WHERE o.add_time BETWEEN ' . $date_start . ' AND ' . $date_end . $no_main_order . $where_data . ' GROUP BY day ORDER BY day ASC ';
	$result = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($result)) {
		if ($search_data['type'] == 'money') {
			$series_data[$row['day']] = floatval($row['money']);
			$data['total_money'] += floatval($row['money']);
		}
		else {
			$series_data[$row['day']] = intval($row['volume']);
			$data['total_volume'] += floatval($row['volume']);
		}
	}

	for ($i = 1; $i <= $day_num; $i++) {
		$day = local_date('y-m-d', local_strtotime(' - ' . ($day_num - $i) . ' days'));

		if (empty($series_data[$day])) {
			$series_data[$day] = 0;
		}

		$day = local_date('m-d', local_strtotime($day));
		$xAxis_data[] = $day;
	}

	$title = array('text' => '', 'subtext' => '');
	$toolbox = array(
		'show'    => true,
		'orient'  => 'vertical',
		'x'       => 'right',
		'y'       => '60',
		'feature' => array(
			'magicType'   => array(
				'show' => true,
				'type' => array('line', 'bar')
				),
			'saveAsImage' => array('show' => true)
			)
		);
	$tooltip = array(
		'trigger'     => 'axis',
		'axisPointer' => array(
			'lineStyle' => array('color' => '#6cbd40')
			)
		);
	$xAxis = array(
		'type'        => 'category',
		'boundaryGap' => false,
		'axisLine'    => array(
			'lineStyle' => array('color' => '#ccc', 'width' => 0)
			),
		'data'        => array()
		);
	$yAxis = array(
		'type'      => 'value',
		'axisLine'  => array(
			'lineStyle' => array('color' => '#ccc', 'width' => 0)
			),
		'axisLabel' => array('formatter' => '')
		);
	$series = array(
		array(
			'name'      => '',
			'type'      => 'line',
			'itemStyle' => array(
				'normal' => array(
					'color'     => '#6cbd40',
					'lineStyle' => array('color' => '#6cbd40')
					)
				),
			'data'      => array(),
			'markPoint' => array(
				'itemStyle' => array(
					'normal' => array('color' => '#6cbd40')
					),
				'data'      => array(
					array('type' => 'max', 'name' => '最大值'),
					array('type' => 'min', 'name' => '最小值')
					)
				)
			),
		array(
			'type'      => 'force',
			'name'      => '',
			'draggable' => false,
			'nodes'     => array('draggable' => false)
			)
		);
	$calculable = true;
	$legend = array(
		'data' => array()
		);
	$title['text'] = '';
	$xAxis['data'] = $xAxis_data;
	$yAxis['formatter'] = '{value}个';
	ksort($series_data);
	$series[0]['name'] = $search_data['type'] == 'money' ? '销售额' : '销量';
	$series[0]['data'] = array_values($series_data);
	$data['title'] = $title;
	$data['series'] = $series;
	$data['tooltip'] = $tooltip;
	$data['legend'] = $legend;
	$data['toolbox'] = $toolbox;
	$data['calculable'] = $calculable;
	$data['xAxis'] = $xAxis;
	$data['yAxis'] = $yAxis;
	$data['xy_file'] = get_dir_file_list();
	return $data;
}

function user_sale_stats($page = 0)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['start_date'] = empty($_REQUEST['start_date']) ? '' : (0 < strpos($_REQUEST['start_date'], '-') ? local_strtotime($_REQUEST['start_date']) : $_REQUEST['start_date']);
		$filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (0 < strpos($_REQUEST['end_date'], '-') ? local_strtotime($_REQUEST['end_date']) : $_REQUEST['end_date']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'total_fee' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$where_o = ' WHERE 1 ';
		$where_u = '';

		if ($filter['start_date']) {
			$where_o .= ' AND o.add_time >= \'' . $filter['start_date'] . '\'';
		}

		if ($filter['end_date']) {
			$where_o .= ' AND o.add_time <= \'' . $filter['end_date'] . '\'';
		}

		if ($filter['keywords']) {
			$where_u .= ' AND (u.user_name LIKE \'%' . $filter['keywords'] . '%\')';
		}

		$no_main_order = no_main_order();
		$where_o .= $no_main_order;
		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);

		if (0 < $page) {
			$filter['page'] = $page;
		}

		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$groupBy = ' GROUP BY o.user_id ';
		$leftJoin = '';
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON u.user_id = o.user_id ';
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_return') . ' AS re ON re.order_id = o.order_id ';
		$sql = 'SELECT o.user_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $leftJoin . $where_o . $where_u . $groupBy;
		$record_count = count($GLOBALS['db']->getAll($sql));
		$filter['record_count'] = $record_count;
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = 'SELECT u.user_id, u.user_name, u.nick_name, u.mobile_phone, ' . statistical_field_order_num() . ' AS total_num, ' . statistical_field_return_num() . ' AS return_num, ' . statistical_field_total_fee() . ' AS total_fee, ' . statistical_field_valid_num() . ' AS valid_num, ' . statistical_field_valid_fee() . ' AS valid_fee, ' . statistical_field_return_fee() . ' AS return_fee ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $leftJoin . $where_o . $where_u . $groupBy . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $value) {
		$row[$key]['formated_return_fee'] = price_format($value['return_fee']);
		$row[$key]['formated_total_fee'] = price_format($value['total_fee']);
	}

	$arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function user_area_stats($page = 0)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['start_date'] = empty($_REQUEST['start_date']) ? '' : (0 < strpos($_REQUEST['start_date'], '-') ? local_strtotime($_REQUEST['start_date']) : $_REQUEST['start_date']);
		$filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (0 < strpos($_REQUEST['end_date'], '-') ? local_strtotime($_REQUEST['end_date']) : $_REQUEST['end_date']);
		$filter['shop_categoryMain'] = empty($_REQUEST['shop_categoryMain']) ? 0 : intval($_REQUEST['shop_categoryMain']);
		$filter['shopNameSuffix'] = empty($_REQUEST['shopNameSuffix']) ? '' : trim($_REQUEST['shopNameSuffix']);
		$filter['area'] = empty($_REQUEST['area']) ? '' : trim($_REQUEST['area']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'user_num' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$where_o = ' WHERE 1 ';

		if ($filter['start_date']) {
			$where_o .= ' AND o.add_time >= \'' . $filter['start_date'] . '\'';
		}

		if ($filter['end_date']) {
			$where_o .= ' AND o.add_time <= \'' . $filter['end_date'] . '\'';
		}

		if ($filter['keywords']) {
			$where_o .= ' AND (o.consignee LIKE \'%' . $filter['keywords'] . '%\')';
		}

		if ($filter['area']) {
			$sql = ' SELECT region_id FROM ' . $GLOBALS['ecs']->table('merchants_region_info') . (' WHERE ra_id = \'' . $filter['area'] . '\' ');
			$region_ids = $GLOBALS['db']->getCol($sql);
			$where_o .= ' AND o.province ' . db_create_in($region_ids);
		}

		$no_main_order = no_main_order();
		$where_o .= $no_main_order;
		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);

		if (0 < $page) {
			$filter['page'] = $page;
		}

		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$groupBy = ' GROUP BY o.district ';
		$leftJoin = '';
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS r ON r.region_id = o.district ';
		$sql = 'SELECT o.district FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $leftJoin . $where_o . $groupBy;
		$record_count = count($GLOBALS['db']->getAll($sql));
		$filter['record_count'] = $record_count;
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = 'SELECT o.district, r.region_name as district_name, ' . statistical_field_user_num() . ' AS user_num, ' . statistical_field_order_num() . ' AS total_num, ' . statistical_field_total_fee() . ' AS total_fee ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o ' . $leftJoin . $where_o . $groupBy . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $value) {
		$row[$key]['formated_total_fee'] = price_format($value['total_fee']);
		$city_id = get_table_date('region', 'region_id=\'' . $value['district'] . '\'', array('parent_id'), 2);
		$row[$key]['city_name'] = get_table_date('region', 'region_id=\'' . $city_id . '\'', array('region_name'), 2);
		$province_id = get_table_date('region', 'region_id=\'' . $city_id . '\'', array('parent_id'), 2);
		$row[$key]['province_name'] = get_table_date('region', 'region_id=\'' . $province_id . '\'', array('region_name'), 2);
	}

	$arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_statistical_user_rank()
{
	$data = array();
	$sql = ' SELECT rank_id, rank_name, special_rank FROM ' . $GLOBALS['ecs']->table('user_rank');
	$arr = $GLOBALS['db']->getAll($sql);
	$rank_list = array_column($arr, 'rank_id');

	foreach ($arr as $key => $val) {
		$arr[$key]['user_num'] = get_table_date('users', 'user_rank=\'' . $val['rank_id'] . '\'', array('COUNT(*)'), 2);
	}

	$no_rank_user_num = get_table_date('users', 'user_rank' . db_create_in($rank_list, '', 'NOT'), array('COUNT(*)'), 2);
	$no_rank = array('rank_id' => 0, 'rank_name' => '无等级', 'user_num' => $no_rank_user_num);
	$arr[] = $no_rank;
	$user_count = get_table_date('users', '1', array('COUNT(*)'), 2);
	$data['text'] = array();
	$data['list'] = array();

	foreach ($arr as $key => $val) {
		$data['list'][] = array('name' => $val['rank_name'], 'value' => $val['user_num']);
		$data['text'][] = $val['rank_name'];
		$arr[$key]['percent'] = round($val['user_num'] / $user_count, 4) * 100;
	}

	$data['source'] = $arr;
	return $data;
}

function industry_analysis($page = 0)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['start_date'] = empty($_REQUEST['start_date']) ? '' : (0 < strpos($_REQUEST['start_date'], '-') ? local_strtotime($_REQUEST['start_date']) : $_REQUEST['start_date']);
		$filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (0 < strpos($_REQUEST['end_date'], '-') ? local_strtotime($_REQUEST['end_date']) : $_REQUEST['end_date']);
		$filter['cat_id'] = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'goods_amount' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$where_c = ' WHERE 1 ';
		$where_o = '';

		if ($filter['start_date']) {
			$where_o .= ' AND o.add_time >= \'' . $filter['start_date'] . '\'';
		}

		if ($filter['end_date']) {
			$where_o .= ' AND o.add_time <= \'' . $filter['end_date'] . '\'';
		}

		if ($filter['keywords']) {
			$where_c .= ' AND (c.cat_name LIKE \'%' . $filter['keywords'] . '%\')';
		}

		if ($filter['cat_id']) {
			$where_c .= ' AND ' . get_children($filter['cat_id'], 0, 0, 'category', 'c.cat_id');
		}

		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);

		if (0 < $page) {
			$filter['page'] = $page;
		}

		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$groupBy = ' GROUP BY c.cat_id ';
		$leftJoin = '';
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.cat_id = c.cat_id ';
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON og.goods_id = g.goods_id ';
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS o ON o.order_id = og.order_id ';
		$sql = 'SELECT c.cat_id FROM ' . $GLOBALS['ecs']->table('category') . ' AS c ' . $leftJoin . $where_c . $where_o . $groupBy;
		$record_count = count($GLOBALS['db']->getAll($sql));
		$filter['record_count'] = $record_count;
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = 'SELECT c.cat_id, c.cat_name, ' . statistical_field_order_goods_amount() . ' AS goods_amount, ' . statistical_field_valid_goods_amount() . ' AS valid_goods_amount, ' . statistical_field_goods_num() . ' AS goods_num, ' . statistical_field_no_order_goods_num() . ' AS no_order_goods_num, ' . statistical_field_order_goods_num() . ' AS order_goods_num, ' . statistical_field_user_num() . ' AS user_num, ' . statistical_field_order_num() . ' as order_num, ' . statistical_field_valid_num() . ' as valid_num ' . ' FROM ' . $GLOBALS['ecs']->table('category') . ' AS c ' . $leftJoin . $where_c . $where_o . $groupBy . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $value) {
		$row[$key]['formated_goods_amount'] = price_format($value['goods_amount']);
		$row[$key]['formated_valid_goods_amount'] = price_format($value['valid_goods_amount']);
	}

	$arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_statistical_industry_analysis($search_data = array())
{
	$data = array();
	$cat_list = cat_list();

	if ($cat_list) {
		$xAxis_data = array();
		$series_data = array();

		foreach ($cat_list as $key => $val) {
			$where_cat = get_children($key, 0, 0, 'category', 'c.cat_id');
			$xAxis_data[] = $val['cat_alias_name'];
			$sql = ' SELECT ' . statistical_field_order_goods_amount() . ' AS order_fee, ' . statistical_field_order_num() . ' AS order_num, ' . statistical_field_order_goods_number() . ' AS order_goods_num ' . ' FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS o ON o.order_id = og.order_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = og.goods_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('category') . ' AS c ON c.cat_id = g.cat_id ' . ' WHERE 1 AND ' . $where_cat;
			$data = $GLOBALS['db']->getRow($sql);

			if ($search_data['type'] == 'order_fee') {
				$series_data[] = floatval($data['order_fee']);
			}
			else if ($search_data['type'] == 'order_num') {
				$series_data[] = intval($data['order_num']);
			}
			else if ($search_data['type'] == 'order_goods_num') {
				$series_data[] = intval($data['order_goods_num']);
			}
		}

		if ($search_data['type'] == 'order_fee') {
			$series_name = '订单金额';
		}
		else if ($search_data['type'] == 'order_num') {
			$series_name = '订单数量';
		}
		else if ($search_data['type'] == 'order_goods_num') {
			$series_name = '下单商品数';
		}

		$title = array('text' => '', 'subtext' => '');
		$toolbox = array(
			'show'    => true,
			'feature' => array(
				'magicType'   => array(
					'show' => true,
					'type' => array('line', 'bar')
					),
				'restore'     => array('show' => true),
				'saveAsImage' => array('show' => true)
				)
			);
		$tooltip = array('trigger' => 'axis');
		$xAxis = array('type' => 'category');
		$yAxis = array('type' => 'value');
		$series = array(
			array(
				'name'      => '',
				'type'      => 'bar',
				'data'      => array(),
				'markPoint' => array(
					'data' => array(
						array('type' => 'max', 'name' => '最大值'),
						array('type' => 'min', 'name' => '最小值')
						)
					),
				'markLine'  => array(
					'data' => array(
						array('type' => 'average', 'name' => '平均值')
						)
					)
				),
			array(
				'type'      => 'force',
				'name'      => '',
				'draggable' => false,
				'nodes'     => array('draggable' => false)
				)
			);
		$calculable = true;
		$legend = array(
			'data' => array()
			);
		$title['text'] = '';
		$xAxis['data'] = $xAxis_data;
		$yAxis['formatter'] = '{value}个';
		ksort($series_data);
		$series[0]['name'] = $series_name;
		$series[0]['data'] = array_values($series_data);
		$data['title'] = $title;
		$data['series'] = $series;
		$data['tooltip'] = $tooltip;
		$data['legend'] = $legend;
		$data['toolbox'] = $toolbox;
		$data['calculable'] = $calculable;
		$data['xAxis'] = $xAxis;
		$data['yAxis'] = $yAxis;
		$data['xy_file'] = get_dir_file_list();
	}

	return $data;
}

function get_statistical_today_trend($search_data = array())
{
	$data = array();
	$where_data = '';
	$date_start = $search_data['start_date'];
	$date_end = $search_data['end_date'];
	$time_diff = get_time_diff();
	$hour_num = ceil($date_end - $date_start) / 3600;
	$no_main_order = no_main_order();
	$sql = ' SELECT FROM_UNIXTIME(o.add_time,\'%y-%m-%d-%H\') AS hour, ' . statistical_field_order_num() . ' AS volume, ' . statistical_field_sale_money() . ' AS money ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o' . ' WHERE o.add_time BETWEEN ' . $date_start . ' AND ' . $date_end . $no_main_order . $where_data . ' GROUP BY hour ORDER BY hour ASC ';
	$result = $GLOBALS['db']->query($sql);

	while ($row = $GLOBALS['db']->fetchRow($result)) {
		if ($search_data['type'] == 'money') {
			$series_data[$row['hour']] = floatval($row['money']);
		}
		else {
			$series_data[$row['hour']] = intval($row['volume']);
		}
	}

	for ($i = 0; $i < $hour_num; $i++) {
		$this_time = local_strtotime(local_date('Y-m-d')) + 3600 * $i - $time_diff;
		$hour = local_date('y-m-d-H', $this_time);

		if (empty($series_data[$hour])) {
			$series_data[$hour] = 0;
		}

		$hour = local_date('H:i', local_strtotime($hour));
		$xAxis_data[] = $hour;
	}

	$title = array('text' => '', 'subtext' => '');
	$toolbox = array(
		'show'    => true,
		'orient'  => 'vertical',
		'x'       => 'right',
		'y'       => '60',
		'feature' => array(
			'magicType'   => array(
				'show' => true,
				'type' => array('line', 'bar')
				),
			'saveAsImage' => array('show' => true)
			)
		);
	$tooltip = array(
		'trigger'     => 'axis',
		'axisPointer' => array(
			'lineStyle' => array('color' => '#6cbd40')
			)
		);
	$xAxis = array(
		'type'        => 'category',
		'boundaryGap' => false,
		'axisLine'    => array(
			'lineStyle' => array('color' => '#ccc', 'width' => 0)
			),
		'data'        => array()
		);
	$yAxis = array(
		'type'      => 'value',
		'axisLine'  => array(
			'lineStyle' => array('color' => '#ccc', 'width' => 0)
			),
		'axisLabel' => array('formatter' => '')
		);
	$series = array(
		array(
			'name'      => '',
			'type'      => 'line',
			'itemStyle' => array(
				'normal' => array(
					'color'     => '#6cbd40',
					'lineStyle' => array('color' => '#6cbd40')
					)
				),
			'data'      => array(),
			'markPoint' => array(
				'itemStyle' => array(
					'normal' => array('color' => '#6cbd40')
					),
				'data'      => array(
					array('type' => 'max', 'name' => '最大值'),
					array('type' => 'min', 'name' => '最小值')
					)
				)
			),
		array(
			'type'      => 'force',
			'name'      => '',
			'draggable' => false,
			'nodes'     => array('draggable' => false)
			)
		);
	$calculable = true;
	$legend = array(
		'data' => array()
		);
	$title['text'] = '';
	$xAxis['data'] = $xAxis_data;
	$yAxis['formatter'] = '{value}个';
	ksort($series_data);
	$series[0]['name'] = $search_data['type'] == 'money' ? '销售额' : '销量';
	$series[0]['data'] = array_values($series_data);
	$data['title'] = $title;
	$data['series'] = $series;
	$data['tooltip'] = $tooltip;
	$data['legend'] = $legend;
	$data['toolbox'] = $toolbox;
	$data['calculable'] = $calculable;
	$data['xAxis'] = $xAxis;
	$data['yAxis'] = $yAxis;
	$data['xy_file'] = get_dir_file_list();
	return $data;
}

function get_statistical_today_sale($search_data = array())
{
	$data = array();
	$where_data = '';
	$date_start = $search_data['start_date'];
	$date_end = $search_data['end_date'];
	$no_main_order = no_main_order();
	$sql = ' SELECT ' . statistical_field_average_total_fee() . ' AS average_total_fee, ' . statistical_field_average_goods_price() . ' AS average_goods_price, ' . statistical_field_order_goods_number() . ' AS goods_number, ' . statistical_field_user_num() . ' AS user_num, ' . statistical_field_order_num() . ' AS order_num, ' . statistical_field_total_fee() . ' AS total_fee ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ON og.order_id = o.order_id ' . ' WHERE o.add_time BETWEEN ' . $date_start . ' AND ' . $date_end . $no_main_order . $where_data;
	$result = $GLOBALS['db']->getRow($sql);

	foreach ($result as $key => $val) {
		$result[$key] = empty($val) ? 0 : $val;
	}

	return $result;
}

function member_account_stats($page = 0)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['start_date'] = empty($_REQUEST['start_date']) ? '' : (0 < strpos($_REQUEST['start_date'], '-') ? local_strtotime($_REQUEST['start_date']) : $_REQUEST['start_date']);
		$filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (0 < strpos($_REQUEST['end_date'], '-') ? local_strtotime($_REQUEST['end_date']) : $_REQUEST['end_date']);
		$filter['source_start_date'] = trim($_REQUEST['start_date']);
		$filter['source_end_date'] = trim($_REQUEST['end_date']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'u.user_money' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$where_u = ' WHERE 1 ';
		$where_al = '';

		if ($filter['start_date']) {
			$where_al .= ' AND al.change_time >= \'' . $filter['start_date'] . '\'';
		}

		if ($filter['end_date']) {
			$where_al .= ' AND al.change_time <= \'' . $filter['end_date'] . '\'';
		}

		if ($filter['keywords']) {
			$where_u .= ' AND ((u.user_name LIKE \'%' . $filter['keywords'] . '%\')' . ' OR (u.email LIKE \'%' . $filter['keywords'] . '%\') ' . ' OR (u.mobile_phone LIKE \'%' . $filter['keywords'] . '%\') ' . ' OR (u.nick_name LIKE \'%' . $filter['keywords'] . '%\')) ';
		}

		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);

		if (0 < $page) {
			$filter['page'] = $page;
		}

		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$groupBy = ' GROUP BY u.user_id ';
		$leftJoin = '';
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('user_rank') . ' AS ur ON ur.rank_id = u.user_rank ';
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('account_log') . ' AS al ON al.user_id = u.user_id ';
		$sql = 'SELECT u.user_id FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . $leftJoin . $where_u . $where_al . $groupBy;
		$record_count = count($GLOBALS['db']->getAll($sql));
		$filter['record_count'] = $record_count;
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = 'SELECT u.user_id, u.user_name, u.nick_name, u.user_money, u.frozen_money, ur.rank_name ' . ' FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . $leftJoin . $where_u . $where_al . $groupBy . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $value) {
	}

	$arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function balance_stats($page = 0)
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['start_date'] = empty($_REQUEST['start_date']) ? '' : (0 < strpos($_REQUEST['start_date'], '-') ? local_strtotime($_REQUEST['start_date']) : $_REQUEST['start_date']);
		$filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (0 < strpos($_REQUEST['end_date'], '-') ? local_strtotime($_REQUEST['end_date']) : $_REQUEST['end_date']);
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'u.user_money' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$where_u = ' WHERE 1 ';
		$where_al = '';

		if ($filter['start_date']) {
			$where_al .= ' AND al.change_time >= \'' . $filter['start_date'] . '\'';
		}

		if ($filter['end_date']) {
			$where_al .= ' AND al.change_time <= \'' . $filter['end_date'] . '\'';
		}

		if ($filter['keywords']) {
			$where_u .= ' AND (u.user_name LIKE \'%' . $filter['keywords'] . '%\')';
		}

		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);

		if (0 < $page) {
			$filter['page'] = $page;
		}

		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$groupBy = ' GROUP BY u.user_id ';
		$leftJoin = '';
		$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('account_log') . ' AS al ON al.user_id = u.user_id ';
		$sql = 'SELECT u.user_id FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . $leftJoin . $where_u . $where_al . $groupBy;
		$record_count = count($GLOBALS['db']->getAll($sql));
		$filter['record_count'] = $record_count;
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = 'SELECT u.user_id, u.user_name, u.user_money, ' . statistical_field_user_recharge_money() . ' AS recharge_money, ' . statistical_field_user_consumption_money() . ' AS consumption_money, ' . statistical_field_user_cash_money() . ' AS cash_money, ' . statistical_field_user_return_money() . ' AS return_money ' . ' FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . $leftJoin . $where_u . $where_al . $groupBy . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ') . ' LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . (',' . $filter['page_size']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $key => $value) {
		$row[$key]['formated_user_money'] = price_format($value['user_money']);
		$row[$key]['formated_recharge_money'] = price_format($value['recharge_money']);
		$row[$key]['formated_consumption_money'] = price_format(0 - $value['consumption_money']);
		$row[$key]['formated_cash_money'] = price_format(0 - $value['cash_money']);
		$row[$key]['formated_return_money'] = price_format($value['return_money']);
	}

	$arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function balance_total_stats()
{
	$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
	$filter['start_date'] = empty($_REQUEST['start_date']) ? '' : (0 < strpos($_REQUEST['start_date'], '-') ? local_strtotime($_REQUEST['start_date']) : $_REQUEST['start_date']);
	$filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (0 < strpos($_REQUEST['end_date'], '-') ? local_strtotime($_REQUEST['end_date']) : $_REQUEST['end_date']);
	if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
		$filter['keywords'] = json_str_iconv($filter['keywords']);
	}

	$where_u = ' WHERE 1 ';
	$where_al = '';

	if ($filter['start_date']) {
		$where_al .= ' AND al.change_time >= \'' . $filter['start_date'] . '\'';
	}

	if ($filter['end_date']) {
		$where_al .= ' AND al.change_time <= \'' . $filter['end_date'] . '\'';
	}

	if ($filter['keywords']) {
		$where_u .= ' AND (u.user_name LIKE \'%' . $filter['keywords'] . '%\')';
	}

	$leftJoin = '';
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('account_log') . ' AS al ON al.user_id = u.user_id ';
	$sql = 'SELECT ' . statistical_field_user_num('al.') . ' AS user_num, ' . statistical_field_user_recharge_money() . ' AS recharge_money, ' . statistical_field_user_consumption_money() . ' AS consumption_money, ' . statistical_field_user_cash_money() . ' AS cash_money, ' . statistical_field_user_return_money() . ' AS return_money ' . ' FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . $leftJoin . $where_u . $where_al;
	$row = $GLOBALS['db']->getRow($sql);
	$row['user_money'] = get_table_date('users', '1', array('SUM(user_money)'), 2);

	foreach ($row as $key => $val) {
		if ($val < 0) {
			$val = 0 - $val;
		}

		$row[$key] = !isset($val) || empty($val) ? '0' : $val;
	}

	return $row;
}

function merchants_commission_list()
{
	$adminru = get_admin_ru_id();
	$result = get_filter();

	if ($result === false) {
		$aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;
		$filter['user_name'] = !isset($_REQUEST['user_name']) && empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);
		if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
			$filter['user_name'] = json_str_iconv($filter['user_name']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'mis.user_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);
		$filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (0 < strpos($_REQUEST['start_time'], '-') ? local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
		$filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (0 < strpos($_REQUEST['end_time'], '-') ? local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);
		$filter['cycle'] = !isset($_REQUEST['cycle']) ? '-1' : intval($_REQUEST['cycle']);
		$where = 'WHERE 1 ';
		$left_join = '';

		if ($filter['user_name']) {
			$sql = 'SELECT GROUP_CONCAT(user_id) AS user_id FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_name LIKE \'%' . mysql_like_quote($filter['user_name']) . '%\'';
			$user_list = $GLOBALS['db']->getOne($sql);
			$where .= ' AND mis.user_id ' . db_create_in($user_list);
		}

		if ($filter['cycle'] != -1) {
			$where .= ' AND ms.cycle = \'' . $filter['cycle'] . '\' ';
			$left_join .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_server') . ' AS ms ON ms.user_id = mis.user_id ';
		}

		$filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_search_where = '';

		if ($filter['store_search'] != 0) {
			if ($adminru['ru_id'] == 0) {
				if ($_REQUEST['store_type']) {
					$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
				}

				if ($filter['store_search'] == 1) {
					$where .= ' AND mis.user_id = \'' . $filter['merchant_id'] . '\' ';
				}
				else if ($filter['store_search'] == 2) {
					$where .= ' AND mis.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
				}
				else if ($filter['store_search'] == 3) {
					$where .= ' AND mis.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
				}
			}
		}

		$where .= ' AND mis.merchants_audit = 1 ';
		$filter['page'] = empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);
		if (isset($_REQUEST['page_size']) && 0 < intval($_REQUEST['page_size'])) {
			$filter['page_size'] = intval($_REQUEST['page_size']);
		}
		else {
			if (isset($_COOKIE['ECSCP']['page_size']) && 0 < intval($_COOKIE['ECSCP']['page_size'])) {
				$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
			}
			else {
				$filter['page_size'] = 15;
			}
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as mis ' . $left_join . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter['page_count'] = 0 < $filter['record_count'] ? ceil($filter['record_count'] / $filter['page_size']) : 1;
		$sql = 'SELECT mis.*, msf.companyName, msf.company_adress, msf.company_contactTel FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as mis ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_steps_fields') . ' as msf on mis.user_id = msf.user_id ' . $left_join . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
		$sql .= ' LIMIT ' . ($filter['page'] - 1) * $filter['page_size'] . ', ' . $filter['page_size'] . ' ';
		set_filter($filter, $sql);
		$this_sql = $sql;
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);
	$admin_commission = array();

	for ($i = 0; $i < count($row); $i++) {
		$sql = 'SELECT server_id, suppliers_desc, suppliers_percent FROM ' . $GLOBALS['ecs']->table('merchants_server') . ' WHERE user_id = \'' . $row[$i]['user_id'] . '\' LIMIT 1';
		$server_info = $GLOBALS['db']->getRow($sql);

		if ($server_info) {
			$row[$i]['server_id'] = $server_info['server_id'];
			$row[$i]['suppliers_desc'] = $server_info['suppliers_desc'];
			$row[$i]['suppliers_percent'] = $server_info['suppliers_percent'];
		}
		else {
			$row[$i]['server_id'] = 0;
			$row[$i]['suppliers_desc'] = '';
			$row[$i]['suppliers_percent'] = '';
		}

		$row[$i]['user_name'] = $GLOBALS['db']->getOne('SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $row[$i]['user_id'] . '\'');
		$row[$i]['server_id'] = $row[$i]['server_id'];
		$valid = get_merchants_order_valid_refund($row[$i]['user_id']);
		$row[$i]['valid_total'] = $valid['total_fee'];
		$row[$i]['order_valid_total'] = price_format($valid['total_fee']);
		$row[$i]['is_goods_rate'] = $valid['is_goods_rate'];
		$row[$i]['order_total_fee'] = $valid['order_total_fee'];
		$row[$i]['goods_total_fee'] = $valid['goods_total_fee'];

		if (file_exists(MOBILE_DRP)) {
			$row[$i]['order_drp_commission'] = price_format($valid['drp_money']);
		}

		$refund = get_merchants_order_valid_refund($row[$i]['user_id'], 1);
		$row[$i]['refund_total'] = $refund['total_fee'];
		$row[$i]['order_refund_total'] = price_format($refund['total_fee']);
		$row[$i]['store_name'] = get_shop_name($row[$i]['user_id'], 1);
		$is_settlement = merchants_is_settlement($row[$i]['user_id'], 1);
		$no_settlement = merchants_is_settlement($row[$i]['user_id'], 0);

		if (file_exists(MOBILE_DRP)) {
			$is_settlement = $is_settlement['all_price'];
			$no_settlement = $no_settlement['all_price'];
		}

		$row[$i]['platform_commission'] = $valid['total_fee'] - $is_settlement;
		$row[$i]['is_settlement'] = $is_settlement;
		$row[$i]['no_settlement'] = $no_settlement;
		$row[$i]['formated_platform_commission'] = price_format($row[$i]['platform_commission']);
		$row[$i]['formated_is_settlement'] = price_format($is_settlement);
		$row[$i]['formated_no_settlement'] = price_format($no_settlement);
		$admin_commission['is_settlement'] += $is_settlement;
		$admin_commission['no_settlement'] += $no_settlement;
		$row[$i]['total_fee_price'] = number_format($valid['total_fee'], 2, '.', '');
		$row[$i]['total_fee_refund'] = number_format($refund['total_fee'], 2, '.', '');
		$row[$i]['is_settlement_price'] = $is_settlement;
		$row[$i]['no_settlement_price'] = $no_settlement;
		$sql = 'SELECT ss.shop_name, ss.shop_address, ss.mobile, ' . 'concat(IFNULL(p.region_name, \'\'), ' . '\'  \', IFNULL(t.region_name, \'\'), \'  \', IFNULL(d.region_name, \'\')) AS region ' . ' FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS ss ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS p ON ss.province = p.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS t ON ss.city = t.region_id ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS d ON ss.district = d.region_id ' . ' WHERE ss.ru_id = \'' . $row[$i]['user_id'] . '\' LIMIT 1';
		$seller_shopinfo = $GLOBALS['db']->getRow($sql);

		if ($seller_shopinfo['shop_name']) {
			$row[$i]['companyName'] = $seller_shopinfo['shop_name'];
			$row[$i]['company_adress'] = '[' . $seller_shopinfo['region'] . '] ' . $seller_shopinfo['shop_address'];
		}

		if ($seller_shopinfo['mobile']) {
			$row[$i]['company_contactTel'] = $seller_shopinfo['mobile'];
		}
		else {
			$row[$i]['company_contactTel'] = $row[$i]['contactPhone'];
		}
	}

	$admin_commission['is_settlement'] = price_format($admin_commission['is_settlement']);
	$admin_commission['no_settlement'] = price_format($admin_commission['no_settlement']);
	set_filter($filter, $this_sql);
	$arr = array('result' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count'], 'admin_commission' => $admin_commission);
	return $arr;
}

function settlement_total_stats()
{
	$where = ' WHERE 1 AND mis.merchants_audit = 1 ';
	$left_join = '';
	$sql = ' SELECT mis.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS mis ' . $left_join . $where;
	$seller = $GLOBALS['db']->getCol($sql);
	$store_num = count($seller);

	if (0 < $store_num) {
		$child_query_ru_id = '(SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og' . ' WHERE og.order_id = o.order_id LIMIT 1)';
		$sql = ' SELECT SUM(' . order_commission_field('o.') . ') AS total_amount ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . (' AS o WHERE ' . $child_query_ru_id . ' ') . db_create_in($seller);
		$total_amount = $GLOBALS['db']->getOne($sql);
	}

	$total_amount = !isset($total_amount) || empty($total_amount) ? '0.00' : $total_amount;
	$admin_commission = array();
	$admin_commission['is_settlement'] = 0;
	$admin_commission['no_settlement'] = 0;
	$admin_commission['valid_fee'] = 0;
	$admin_commission['refund_fee'] = 0;

	if ($seller) {
		for ($i = 0; $i < count($seller); $i++) {
			if (file_exists(MOBILE_DRP)) {
				$is_drp_settlement = merchants_is_settlement($seller[$i], 1);
				$no_drp_settlement = merchants_is_settlement($seller[$i], 0);
				$is_settlement = $is_drp_settlement['all_price'];
				$no_settlement = $no_drp_settlement['all_price'];
			}
			else {
				$is_settlement = merchants_is_settlement($seller[$i], 1);
				$no_settlement = merchants_is_settlement($seller[$i], 0);
			}

			$admin_commission['is_settlement'] += $is_settlement;
			$admin_commission['no_settlement'] += $no_settlement;
			$valid = get_merchants_order_valid_refund($seller[$i]);
			$refund = get_merchants_order_valid_refund($seller[$i], 1);
			$admin_commission['valid_fee'] += $valid['total_fee'];
			$admin_commission['refund_fee'] += $refund['total_fee'];
		}
	}

	$admin_all = array();
	$admin_all['store_num'] = $store_num;
	$admin_all['total_amount'] = $total_amount;
	$admin_all['is_settlement'] = empty($admin_commission['is_settlement']) ? '0.00' : sprintf('%.2f', $admin_commission['is_settlement']);
	$admin_all['no_settlement'] = empty($admin_commission['no_settlement']) ? '0.00' : sprintf('%.2f', $admin_commission['is_settlement']);
	$admin_all['valid_fee'] = empty($admin_commission['valid_fee']) ? '0.00' : sprintf('%.2f', $admin_commission['valid_fee']);
	$admin_all['refund_fee'] = empty($admin_commission['refund_fee']) ? '0.00' : sprintf('%.2f', $admin_commission['refund_fee']);
	$admin_all['actual_fee'] = sprintf('%.2f', $admin_all['valid_fee'] - $admin_all['refund_fee']);
	$admin_all['platform_commission'] = sprintf('%.2f', $admin_all['valid_fee'] - $admin_all['is_settlement']);
	return $admin_all;
}

function get_statistical_shop_area($search_data = array())
{
	$data = array();
	$where_data = '';
	if (isset($search_data['shop_categoryMain']) && !empty($search_data['shop_categoryMain'])) {
		$where_data .= ' AND msi.shop_categoryMain = \'' . $search_data['shop_categoryMain'] . '\' ';
	}

	if (isset($search_data['shopNameSuffix']) && !empty($search_data['shopNameSuffix'])) {
		$where_data .= ' AND msi.shopNameSuffix = \'' . $search_data['shopNameSuffix'] . '\' ';
	}

	if (isset($search_data['start_date']) && !empty($search_data['start_date'])) {
		$where_data .= ' AND msi.add_time >= \'' . $filter['start_date'] . '\'';
	}

	if (isset($search_data['end_date']) && !empty($search_data['end_date'])) {
		$where_data .= ' AND msi.add_time <= \'' . $filter['end_date'] . '\'';
	}

	if (isset($search_data['area']) && !empty($search_data['area'])) {
		$sql = ' SELECT region_id FROM ' . $GLOBALS['ecs']->table('merchants_region_info') . (' WHERE ra_id = \'' . $search_data['area'] . '\' ');
		$region_ids = $GLOBALS['db']->getCol($sql);
		$where_data .= ' AND spi.province ' . db_create_in($region_ids);
	}

	$groupBy = ' GROUP BY spi.province ';
	$leftJoin = '';
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS msi ON msi.user_id = spi.ru_id ';
	$leftJoin .= ' LEFT JOIN ' . $GLOBALS['ecs']->table('region') . ' AS r ON r.region_id = spi.province ';
	$sql = 'SELECT spi.province, r.region_name as province_name, ' . statistical_field_shop_num() . ' AS store_num ' . ' FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' AS spi ' . $leftJoin . $where_data . $groupBy;
	$result = $GLOBALS['db']->getAll($sql);
	$series_data = array();
	$value_arr = array();

	foreach ($result as $key => $val) {
		$series_data[] = array('name' => $val['province_name'], 'value' => $val['store_num']);
		$value_arr[] = $val['store_num'];
	}

	$max = max($value_arr);
	$title = array('text' => '', 'subtext' => '');
	$toolbox = array(
		'show'    => true,
		'orient'  => 'vertical',
		'x'       => 'right',
		'y'       => 'center',
		'feature' => array(
			'mark'     => array('show' => true),
			'dataView' => array('show' => true, 'readOnly' => false)
			)
		);
	$tooltip = array('trigger' => 'item');
	$dataRange = array(
		'orient'      => 'horizontal',
		'min'         => 0,
		'max'         => $max,
		'text'        => array('高', '低'),
		'splitNumber' => 0
		);
	$series = array(
		array(
			'name'         => '',
			'type'         => 'map',
			'mapType'      => 'china',
			'mapLocation'  => array('x' => 'center'),
			'selectedMode' => 'multiple',
			'itemStyle'    => array(
				'normal'   => array(
					'label' => array('show' => true)
					),
				'emphasis' => array(
					'label' => array('show' => true)
					)
				),
			'data'         => array()
			)
		);
	$animation = false;
	$title['text'] = '店铺地区分布';
	$xAxis['data'] = $xAxis_data;
	$yAxis['formatter'] = '{value}个';
	ksort($series_data);
	$series[0]['name'] = '店铺地区分布';
	$series[0]['data'] = array_values($series_data);
	$data['title'] = $title;
	$data['series'] = $series;
	$data['tooltip'] = $tooltip;
	$data['toolbox'] = $toolbox;
	$data['animation'] = $animation;
	$data['dataRange'] = $dataRange;
	$data['xy_file'] = get_dir_file_list();
	return $data;
}

function list_download($config = array())
{
	if ($config['filename']) {
		$filename = $config['filename'];
	}
	else {
		$filename = time();
	}

	header('Content-type: application/vnd.ms-excel; charset=GB2312');
	header('Content-Disposition: attachment; filename=' . $filename . '.csv');
	$csv_data = '';
	$csv_data = implode(',', $config['thead']) . "\r\n";

	foreach ($config['tdata'] as $data) {
		$row = array();

		foreach ($config['tbody'] as $body) {
			$row[] = $data[$body];
		}

		$csv_data .= implode(',', $row) . "\r\n";
	}

	echo ecs_iconv(EC_CHARSET, 'GB2312', $csv_data);
	exit();
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
