<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_transport_list($ru_id = 0)
{
	$where = ' WHERE ru_id = \'' . $ru_id . '\' ';
	$filter = array('ru_id' => $ru_id);
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods_transport') . $where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('goods_transport') . $where . ' ORDER BY tid DESC';
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$arr = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$row['update_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['update_time']);
		$arr[] = $row;
	}

	return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_transport_area($tid = 0)
{
	$where = '';

	if ($tid == 0) {
		global $admin_id;
		$where .= ' AND admin_id = \'' . $admin_id . '\' ';
	}

	$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('goods_transport_extend') . (' WHERE tid = \'' . $tid . '\' ' . $where . ' ORDER BY id DESC ');
	$transport_area = $GLOBALS['db']->getAll($sql);

	foreach ($transport_area as $key => $val) {
		if (!empty($val['top_area_id']) && !empty($val['area_id'])) {
			$area_map = array();
			$top_area_arr = explode(',', $val['top_area_id']);

			foreach ($top_area_arr as $k => $v) {
				$top_area = get_table_date('region', 'region_id=\'' . $v . '\'', array('region_name'), 2);
				$sql = ' SELECT region_name FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE parent_id = \'' . $v . '\' AND region_id IN (' . $val['area_id'] . ') ');
				$area_arr = $GLOBALS['db']->getCol($sql);
				$area_list = implode(',', $area_arr);
				$area_map[$k]['top_area'] = $top_area;
				$area_map[$k]['area_list'] = $area_list;
			}

			$transport_area[$key]['area_map'] = $area_map;
		}
	}

	return $transport_area;
}

function get_area_list($area_id = '')
{
	$area_list = '';

	if (!empty($area_id)) {
		$sql = ' SELECT region_name FROM ' . $GLOBALS['ecs']->table('region') . (' WHERE region_id IN (' . $area_id . ') ');
		$area_list = $GLOBALS['db']->getCol($sql);
		$area_list = implode(',', $area_list);
	}

	return $area_list;
}

function get_transport_express($tid = 0)
{
	$where = '';

	if ($tid == 0) {
		global $admin_id;
		$where .= ' AND admin_id = \'' . $admin_id . '\' ';
	}

	$sql = ' SELECT * FROM ' . $GLOBALS['ecs']->table('goods_transport_express') . (' WHERE tid = \'' . $tid . '\' ' . $where . ' ORDER BY id DESC ');
	$transport_express = $GLOBALS['db']->getAll($sql);

	foreach ($transport_express as $key => $val) {
		$transport_express[$key]['express_list'] = get_express_list($val['shipping_id']);
	}

	return $transport_express;
}

function get_express_list($shipping_id = '')
{
	$express_list = '';

	if (!empty($shipping_id)) {
		$sql = ' SELECT shipping_name FROM ' . $GLOBALS['ecs']->table('shipping') . (' WHERE shipping_id IN (' . $shipping_id . ') ');
		$express_list = $GLOBALS['db']->getCol($sql);
		$express_list = implode(',', $express_list);
	}

	return $express_list;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
include_once ROOT_PATH . 'includes/lib_order.php';
$exc = new exchange($ecs->table('goods_transport'), $db, 'tid', 'title');
$exc_extend = new exchange($ecs->table('goods_transport_extend'), $db, 'id', 'tid');
$exc_express = new exchange($ecs->table('goods_transport_express'), $db, 'id', 'tid');
$adminru = get_admin_ru_id();
$admin_id = get_admin_id();
$smarty->assign('menu_select', array('action' => '11_system', 'current' => '03_shipping_list'));
$smarty->assign('primary_cat', $_LANG['02_cat_and_goods']);

if ($_REQUEST['act'] == 'list') {
	$tab_menu = array();
	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['03_shipping_list'], 'href' => 'shipping.php?act=list');
	$tab_menu[] = array('curr' => 1, 'text' => $_LANG['shipping_transport'], 'href' => 'goods_transport.php?act=list');
	$smarty->assign('tab_menu', $tab_menu);
	$transport_list = get_transport_list($adminru['ru_id']);
	$smarty->assign('transport_list', $transport_list['list']);
	$smarty->assign('filter', $transport_list['filter']);
	$smarty->assign('record_count', $transport_list['record_count']);
	$smarty->assign('page_count', $transport_list['page_count']);
	$page_count_arr = seller_page($transport_list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	$smarty->assign('action_link', array('text' => $_LANG['add_transport'], 'href' => 'goods_transport.php?act=add', 'class' => 'icon-plus'));
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', $_LANG['goods_transport']);
	assign_query_info();
	$smarty->display('goods_transport_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$transport_list = get_transport_list($adminru['ru_id']);
	$smarty->assign('transport_list', $transport_list['list']);
	$smarty->assign('filter', $transport_list['filter']);
	$smarty->assign('record_count', $transport_list['record_count']);
	$smarty->assign('page_count', $transport_list['page_count']);
	$page_count_arr = seller_page($transport_list, $_REQUEST['page']);
	$smarty->assign('page_count_arr', $page_count_arr);
	make_json_result($smarty->fetch('goods_transport_list.dwt'), '', array('filter' => $transport_list['filter'], 'page_count' => $transport_list['page_count']));
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		$tid = empty($_REQUEST['tid']) ? 0 : intval($_REQUEST['tid']);
		$shipping_id = 0;

		if ($_REQUEST['act'] == 'add') {
			$form_action = 'insert';
			$sql = 'DELETE FROM' . $ecs->table('goods_transport_tpl') . ('WHERE tid = 0 AND admin_id = \'' . $admin_id . '\'');
			$db->query($sql);
		}
		else {
			$form_action = 'update';

			if (0 < $tid) {
				$shipping_tpl = get_transport_shipping_list($tid, $adminru['ru_id']);
			}
		}

		$smarty->assign('shipping_tpl', $shipping_tpl);
		$sql = ' SELECT * FROM ' . $ecs->table('goods_transport') . (' WHERE tid = \'' . $tid . '\' ');
		$transport_info = $db->getRow($sql);
		$smarty->assign('form_action', $form_action);
		$smarty->assign('tid', $tid);
		$smarty->assign('transport_info', $transport_info);
		$area = get_transport_area($tid);

		foreach ($area as $v) {
			if (empty($v['top_area_id']) || empty($v['area_id'])) {
				$exc_extend->drop($v['id']);
			}
		}

		$express = get_transport_express($tid);

		foreach ($express as $v) {
			if (empty($v['shipping_id'])) {
				$exc_express->drop($v['id']);
			}
		}

		$smarty->assign('transport_area', get_transport_area($tid));
		$smarty->assign('transport_express', get_transport_express($tid));
		$shipping_list = shipping_list();

		foreach ($shipping_list as $key => $val) {
			if (substr($row['shipping_code'], 0, 5) == 'ship_') {
				unset($arr[$key]);
				continue;
			}

			if ($val['shipping_code'] == 'cac') {
				unset($shipping_list[$key]);
			}
		}

		$smarty->assign('shipping_list', $shipping_list);
		$smarty->assign('ur_here', $_LANG['transport_info']);
		$smarty->assign('action_link', array('href' => 'goods_transport.php?act=list', 'text' => $_LANG['goods_transport'], 'class' => 'icon-reply'));
		assign_query_info();
		$smarty->display('goods_transport_info.dwt');
	}
	else {
		if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
			$data = array();
			$data['tid'] = empty($_REQUEST['tid']) ? 0 : intval($_REQUEST['tid']);
			$data['ru_id'] = $adminru['ru_id'];
			$data['type'] = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);
			$data['title'] = empty($_REQUEST['title']) ? '' : trim($_REQUEST['title']);
			$data['freight_type'] = empty($_REQUEST['freight_type']) ? 0 : intval($_REQUEST['freight_type']);
			$data['update_time'] = gmtime();
			$data['free_money'] = empty($_REQUEST['free_money']) ? 0 : floatval($_REQUEST['free_money']);
			$data['shipping_title'] = empty($_REQUEST['shipping_title']) ? 0 : trim($_REQUEST['shipping_title']);
			$s_tid = $data['tid'];

			if ($_REQUEST['act'] == 'update') {
				$msg = '编辑成功';
				$db->autoExecute($ecs->table('goods_transport'), $data, 'UPDATE', 'tid = \'' . $data['tid'] . '\'');
				$tid = $s_tid;
				$where = ' tid = \'' . $tid . '\'';
			}
			else {
				$msg = '添加成功';
				$db->autoExecute($ecs->table('goods_transport'), $data, 'INSERT');
				$tid = $db->insert_id();
				$db->autoExecute($ecs->table('goods_transport_extend'), array('tid' => $tid), 'UPDATE', 'tid = \'0\' AND admin_id = \'' . $admin_id . '\' ');
				$db->autoExecute($ecs->table('goods_transport_express'), array('tid' => $tid), 'UPDATE', 'tid = \'0\' AND admin_id = \'' . $admin_id . '\' ');
				$where = ' admin_id = \'' . $admin_id . '\' AND tid = 0';
			}

			if (0 < $data['freight_type']) {
				if (!isset($_SESSION[$s_tid]['tpl_id']) && empty($_SESSION[$s_tid]['tpl_id'])) {
					$sql = 'SELECT GROUP_CONCAT(id) AS id FROM ' . $GLOBALS['ecs']->table('goods_transport_tpl') . ' WHERE ' . $where;
					$tpl_id = $GLOBALS['db']->getOne($sql);
				}
				else {
					$tpl_id = $_SESSION[$s_tid]['tpl_id'];
				}

				if (!empty($tpl_id)) {
					$sql = 'UPDATE' . $ecs->table('goods_transport_tpl') . (' SET tid = \'' . $tid . '\' WHERE admin_id = \'' . $admin_id . '\' AND tid = 0 AND id ') . db_create_in($tpl_id);
					$db->query($sql);
					unset($_SESSION[$s_tid]['tpl_id']);
				}
			}

			if (0 < count($_REQUEST['sprice'])) {
				foreach ($_REQUEST['sprice'] as $key => $val) {
					$info = array();
					$info['sprice'] = $val;
					$db->autoExecute($ecs->table('goods_transport_extend'), $info, 'UPDATE', 'id = \'' . $key . '\'');
				}
			}

			if (0 < count($_REQUEST['shipping_fee'])) {
				foreach ($_REQUEST['shipping_fee'] as $key => $val) {
					$info = array();
					$info['shipping_fee'] = $val;
					$db->autoExecute($ecs->table('goods_transport_express'), $info, 'UPDATE', 'id = \'' . $key . '\'');
				}
			}

			$links = array(
				array('href' => 'goods_transport.php?act=list', 'text' => $_LANG['back_list'])
				);
			sys_msg($msg, 0, $links);
		}
		else if ($_REQUEST['act'] == 'remove') {
			$id = intval($_REQUEST['id']);
			$exc->drop($id);
			$sql = ' DELETE FROM ' . $ecs->table('goods_transport_extend') . (' WHERE tid = \'' . $id . '\' ');
			$db->query($sql);
			$sql = ' DELETE FROM ' . $ecs->table('goods_transport_express') . (' WHERE tid = \'' . $id . '\' ');
			$db->query($sql);
			$sql = ' DELETE FROM ' . $ecs->table('goods_transport_tpl') . (' WHERE tid = \'' . $id . '\' ');
			$db->query($sql);
			$sql = ' UPDATE ' . $ecs->table('goods') . (' SET tid = 0 WHERE tid = \'' . $id . '\' ');
			$db->query($sql);
			$url = 'goods_transport.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
			ecs_header('Location: ' . $url . "\n");
			exit();
		}
		else if ($_REQUEST['act'] == 'batch_drop') {
			if (isset($_POST['checkboxes'])) {
				$del_count = 0;

				foreach ($_POST['checkboxes'] as $key => $id) {
					$id = !empty($id) ? intval($id) : 0;
					$exc->drop($id);
					$sql = ' DELETE FROM ' . $ecs->table('goods_transport_extend') . (' WHERE tid = \'' . $id . '\' ');
					$db->query($sql);
					$sql = ' DELETE FROM ' . $ecs->table('goods_transport_express') . (' WHERE tid = \'' . $id . '\' ');
					$db->query($sql);
					$sql = ' DELETE FROM ' . $ecs->table('goods_transport_tpl') . (' WHERE tid = \'' . $id . '\' ');
					$db->query($sql);
					$sql = ' UPDATE ' . $ecs->table('goods') . (' SET tid = 0 WHERE tid = \'' . $id . '\' ');
					$db->query($sql);
					$del_count++;
				}

				$links[] = array('text' => $_LANG['back_list'], 'href' => 'goods_transport.php?act=list');
				sys_msg(sprintf($_LANG['batch_drop_success'], $del_count), 0, $links);
			}
			else {
				$links[] = array('text' => $_LANG['back_list'], 'href' => 'goods_transport.php?act=list');
				sys_msg($_LANG['no_select_group_buy'], 0, $links);
			}
		}
		else if ($_REQUEST['act'] == 'edit_title') {
			$id = intval($_POST['id']);
			$title = json_str_iconv(trim($_POST['val']));

			if ($exc->edit('title = \'' . $title . '\', update_time=' . gmtime(), $id)) {
				make_json_result(stripslashes($title));
			}
		}
		else if ($_REQUEST['act'] == 'add_area') {
			$data = array();
			$data['tid'] = empty($_REQUEST['tid']) ? 0 : intval($_REQUEST['tid']);
			$data['ru_id'] = $adminru['ru_id'];
			$data['admin_id'] = $admin_id;
			$db->autoExecute($ecs->table('goods_transport_extend'), $data, 'INSERT');
			$smarty->assign('transport_area', get_transport_area($data['tid']));
			$html = $smarty->fetch('library/goods_transport_area.lbi');
			make_json_result($html);
		}
		else if ($_REQUEST['act'] == 'drop_area') {
			$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
			$tid = get_table_date('goods_transport_extend', 'id=\'' . $id . '\'', array('tid'), 2);
			$exc_extend->drop($id);
			$smarty->assign('transport_area', get_transport_area($tid));
			$html = $smarty->fetch('library/goods_transport_area.lbi');
			make_json_result($html);
		}
		else if ($_REQUEST['act'] == 'edit_area_fee') {
			$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
			$sprice = empty($_REQUEST['fee']) ? 0 : (double) $_REQUEST['fee'];

			if ($exc_extend->edit('sprice = \'' . $sprice . '\'', $id)) {
				clear_cache_files();
				make_json_result($sprice);
			}
		}
		else if ($_REQUEST['act'] == 'edit_area') {
			$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
			$tid = get_table_date('goods_transport_extend', 'id=\'' . $id . '\'', array('tid'), 2);
			$province_selected = get_table_date('goods_transport_extend', 'id=\'' . $id . '\'', array('top_area_id'), 2);
			$province_selected = explode(',', $province_selected);
			$city_selected = get_table_date('goods_transport_extend', 'id=\'' . $id . '\'', array('area_id'), 2);
			$city_selected = explode(',', $city_selected);
			$where = '';

			if ($tid == 0) {
				$where .= ' AND admin_id = \'' . $admin_id . '\' ';
			}

			$sql = ' SELECT area_id FROM ' . $ecs->table('goods_transport_extend') . (' WHERE tid=\'' . $tid . '\' ' . $where . ' AND id!=\'' . $id . '\' ');
			$city_disabled = $db->getCol($sql);
			$city_disabled = implode(',', $city_disabled);
			$city_disabled = explode(',', $city_disabled);
			$province = get_regions(1, 1);

			foreach ($province as $key => $val) {
				$child_num = 0;
				$other_num = 0;
				$province[$key]['is_selected'] = in_array($val['region_id'], $province_selected) ? 1 : 0;
				$city = get_regions(2, $val['region_id']);

				foreach ($city as $k => $v) {
					$city[$k]['is_selected'] = in_array($v['region_id'], $city_selected) ? 1 : 0;
					$city[$k]['is_disabled'] = in_array($v['region_id'], $city_disabled) ? 1 : 0;
					$child_num += in_array($v['region_id'], $city_selected) ? 1 : 0;
					$other_num += in_array($v['region_id'], $city_disabled) ? 1 : 0;
				}

				$province[$key]['child'] = $city;
				$province[$key]['child_num'] = $child_num;
				$province[$key]['is_disabled'] = count($city) == $child_num + $other_num ? 1 : 0;
			}

			$smarty->assign('id', $id);
			$smarty->assign('area_map', $province);
			$html = $smarty->fetch('library/goods_transport_area_list.lbi');
			make_json_result($html);
		}
		else if ($_REQUEST['act'] == 'save_area') {
			$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
			$tid = get_table_date('goods_transport_extend', 'id=\'' . $id . '\'', array('tid'), 2);
			$data = array();
			$data['area_id'] = empty($_REQUEST['area_id']) ? '' : trim($_REQUEST['area_id']);
			$data['top_area_id'] = empty($_REQUEST['top_area_id']) ? '' : trim($_REQUEST['top_area_id']);
			$db->autoExecute($ecs->table('goods_transport_extend'), $data, 'UPDATE', 'id = \'' . $id . '\'');
			$smarty->assign('transport_area', get_transport_area($tid));
			$html = $smarty->fetch('library/goods_transport_area.lbi');
			make_json_result($html);
		}
		else if ($_REQUEST['act'] == 'add_express') {
			$data = array();
			$data['tid'] = empty($_REQUEST['tid']) ? 0 : intval($_REQUEST['tid']);
			$data['ru_id'] = $adminru['ru_id'];
			$data['admin_id'] = $admin_id;
			$db->autoExecute($ecs->table('goods_transport_express'), $data, 'INSERT');
			$smarty->assign('transport_express', get_transport_express($data['tid']));
			$html = $smarty->fetch('library/goods_transport_express.lbi');
			make_json_result($html);
		}
		else if ($_REQUEST['act'] == 'drop_express') {
			$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
			$tid = get_table_date('goods_transport_express', 'id=\'' . $id . '\'', array('tid'), 2);
			$exc_express->drop($id);
			$smarty->assign('transport_express', get_transport_express($tid));
			$html = $smarty->fetch('library/goods_transport_express.lbi');
			make_json_result($html);
		}
		else if ($_REQUEST['act'] == 'edit_express_fee') {
			$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
			$shipping_fee = empty($_REQUEST['fee']) ? 0 : (double) $_REQUEST['fee'];

			if ($exc_express->edit('shipping_fee = \'' . $shipping_fee . '\'', $id)) {
				clear_cache_files();
				make_json_result($shipping_fee);
			}
		}
		else if ($_REQUEST['act'] == 'edit_express') {
			$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
			$tid = get_table_date('goods_transport_express', 'id=\'' . $id . '\'', array('tid'), 2);
			$express_selected = get_table_date('goods_transport_express', 'id=\'' . $id . '\'', array('shipping_id'), 2);
			$express_selected = explode(',', $express_selected);
			$where = '';

			if ($tid == 0) {
				$where .= ' AND admin_id = \'' . $admin_id . '\' ';
			}

			$sql = ' SELECT shipping_id FROM ' . $ecs->table('goods_transport_express') . (' WHERE tid=\'' . $tid . '\' ' . $where . ' AND id!=\'' . $id . '\' ');
			$express_disabled = $db->getCol($sql);
			$express_disabled = implode(',', $express_disabled);
			$express_disabled = explode(',', $express_disabled);
			$shipping_list = shipping_list();

			foreach ($shipping_list as $k => $v) {
				if ($v['shipping_code'] == 'cac') {
					unset($shipping_list[$k]);
					continue;
				}

				$shipping_list[$k]['is_selected'] = in_array($v['shipping_id'], $express_selected) ? 1 : 0;
				$shipping_list[$k]['is_disabled'] = in_array($v['shipping_id'], $express_disabled) ? 1 : 0;
			}

			$smarty->assign('id', $id);
			$smarty->assign('shipping_list', $shipping_list);
			$html = $smarty->fetch('library/goods_transport_express_list.lbi');
			make_json_result($html);
		}
		else if ($_REQUEST['act'] == 'save_express') {
			$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
			$tid = get_table_date('goods_transport_express', 'id=\'' . $id . '\'', array('tid'), 2);
			$data = array();
			$data['shipping_id'] = empty($_REQUEST['shipping_id']) ? '' : trim($_REQUEST['shipping_id']);
			$db->autoExecute($ecs->table('goods_transport_express'), $data, 'UPDATE', 'id = \'' . $id . '\'');
			$smarty->assign('transport_express', get_transport_express($tid));
			$html = $smarty->fetch('library/goods_transport_express.lbi');
			make_json_result($html);
		}
		else if ($_REQUEST['act'] == 'get_shipping_tem') {
			$shipping_id = empty($_REQUEST['shipping_id']) ? 0 : intval($_REQUEST['shipping_id']);
			$tid = empty($_REQUEST['tid']) ? 0 : intval($_REQUEST['tid']);
			$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);

			if (!empty($id)) {
				$where = 'id = \'' . $id . '\'';
			}
			else {
				$where = 'b.tid = \'' . $tid . '\' AND b.shipping_id = \'' . $shipping_id . '\' AND b.user_id = \'' . $adminru['ru_id'] . '\' AND id = 0';
			}

			$sql = 'SELECT a.shipping_name, a.shipping_code, a.support_cod, b.* ' . ' FROM ' . $ecs->table('goods_transport_tpl') . ' AS b ' . ' left join ' . $ecs->table('shipping') . ' AS a on a.shipping_id=b.shipping_id ' . (' WHERE ' . $where . ' LIMIT 1');
			$row = $db->getRow($sql);

			if (!empty($row)) {
				$set_modules = 1;

				if ($row['shipping_code']) {
					include_once ROOT_PATH . 'includes/modules/shipping/' . $row['shipping_code'] . '.php';
				}

				$fields = unserialize($row['configure']);
				if ($row['support_cod'] && $fields[count($fields) - 1]['name'] != 'pay_fee') {
					$fields[] = array('name' => 'pay_fee', 'value' => 0);
				}

				foreach ($fields as $key => $val) {
					if ($val['name'] == 'basic_fee') {
						$val['name'] = 'base_fee';
					}

					if ($val['name'] == 'item_fee') {
						$item_fee = 1;
					}

					if ($val['name'] == 'fee_compute_mode') {
						$smarty->assign('fee_compute_mode', $val['value']);
						unset($fields[$key]);
					}
					else {
						$fields[$key]['name'] = $val['name'];
						$fields[$key]['label'] = $_LANG[$val['name']];
					}
				}

				if (empty($item_fee)) {
					$field = array('name' => 'item_fee', 'value' => '0', 'label' => empty($_LANG['item_fee']) ? '' : $_LANG['item_fee']);
					array_unshift($fields, $field);
				}

				$smarty->assign('shipping_area', $row);
			}
			else {
				$shipping = $db->getRow('SELECT shipping_name, shipping_code FROM ' . $ecs->table('shipping') . (' WHERE shipping_id=\'' . $shipping_id . '\''));
				$set_modules = 1;
				include_once ROOT_PATH . 'includes/modules/shipping/' . $shipping['shipping_code'] . '.php';
				$fields = array();

				foreach ($modules[0]['configure'] as $key => $val) {
					$fields[$key]['name'] = $val['name'];
					$fields[$key]['value'] = $val['value'];
					$fields[$key]['label'] = $_LANG[$val['name']];
				}

				$count = count($fields);
				$fields[$count]['name'] = 'free_money';
				$fields[$count]['value'] = '0';
				$fields[$count]['label'] = $_LANG['free_money'];

				if ($modules[0]['cod']) {
					$count++;
					$fields[$count]['name'] = 'pay_fee';
					$fields[$count]['value'] = '0';
					$fields[$count]['label'] = $_LANG['pay_fee'];
				}

				$shipping_area['shipping_id'] = 0;
				$shipping_area['free_money'] = 0;
				$smarty->assign('shipping_area', array('shipping_id' => $_REQUEST['shipping_id'], 'shipping_code' => $shipping['shipping_code']));
			}

			$smarty->assign('fields', $fields);
			$smarty->assign('return_data', $return_data);
			$regions = array();

			if (!empty($row['region_id'])) {
				$sql = ' SELECT region_id,region_name from ' . $ecs->table('region') . ' where region_id in (' . $row['region_id'] . ') ';
				$res = $db->query($sql);

				while ($arr = $db->fetchRow($res)) {
					$regions[$arr['region_id']] = $arr['region_name'];
				}
			}

			$smarty->assign('shipping_info', shipping_info($shipping_id, array('shipping_name')));
			$smarty->assign('countries', get_regions());
			$Province_list = get_regions(1, 1);
			$smarty->assign('province_all', $Province_list);
			$smarty->assign('regions', $regions);
			$smarty->assign('tpl_info', $row);
			$smarty->assign('tid', $tid);
			$smarty->assign('shipping_id', $shipping_id);
			$smarty->assign('id', $id);
			$html = $smarty->fetch('library/shipping_tab.lbi');
			make_json_result($html);
		}
		else if ($_REQUEST['act'] == 'the_national') {
			$tid = empty($_REQUEST['tid']) ? 0 : intval($_REQUEST['tid']);
			$shipping_id = empty($_REQUEST['shipping_id']) ? 0 : intval($_REQUEST['shipping_id']);
			$regions = get_the_national();
			$sql = 'SELECT GROUP_CONCAT(region_id) AS region_id FROM ' . $GLOBALS['ecs']->table('goods_transport_tpl') . ' WHERE user_id = \'' . $adminru['ru_id'] . '\'' . (' AND tid = \'' . $tid . '\' AND shipping_id = \'' . $shipping_id . '\'');
			$region_list = $GLOBALS['db']->getOne($sql);
			$region_list = !empty($region_list) ? explode(',', $region_list) : array();
			$sql = 'SELECT GROUP_CONCAT(region_id) AS region_id FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE 1';
			$region = $GLOBALS['db']->getOne($sql);
			$region = !empty($region) ? explode(',', $region) : array();
			$assoc = array();
			if ($region && $region_list) {
				$assoc = array_intersect($region, $region_list);
			}

			if ($assoc) {
				$regions = array();
			}

			$smarty->assign('regions', $regions);
			$html = $smarty->fetch('library/shipping_the_national.lbi');
			make_json_result($html);
		}
		else if ($_REQUEST['act'] == 'add_shipping_tpl') {
			require ROOT_PATH . '/includes/cls_json.php';
			$json = new JSON();
			$result = array('error' => 0, 'message' => '');
			$rId = empty($_REQUEST['regions']) ? '' : implode(',', $_REQUEST['regions']);
			$tid = empty($_REQUEST['tid']) ? 0 : intval($_REQUEST['tid']);
			$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
			$regionId = $rId;
			$shipping_id = empty($_REQUEST['shipping_id']) ? 0 : intval($_REQUEST['shipping_id']);
			if ($shipping_id == 0 || empty($regionId)) {
				$result['error'] = 1;
				$result['message'] = '请将信息填写完整';
				exit($json->encode($result));
			}
			else {
				$shipping_code = $db->getOne('SELECT shipping_code FROM ' . $ecs->table('shipping') . (' WHERE shipping_id=\'' . $shipping_id . '\''));
				$plugin = '../includes/modules/shipping/' . $shipping_code . '.php';

				if (!file_exists($plugin)) {
					$add_to_mess = $_LANG['not_find_plugin'];
					$result['error'] = 1;
					$result['message'] = $add_to_mess;
					exit($json->encode($result));
				}
				else {
					$set_modules = 1;
					include_once $plugin;
				}

				$config = array();

				foreach ($modules[0]['configure'] as $key => $val) {
					$config[$key]['name'] = $val['name'];
					$config[$key]['value'] = $_POST[$val['name']];
				}

				$count = count($config);
				$config[$count]['name'] = 'free_money';
				$config[$count]['value'] = empty($_POST['free_money']) ? '' : $_POST['free_money'];
				$count++;
				$config[$count]['name'] = 'fee_compute_mode';
				$config[$count]['value'] = empty($_POST['fee_compute_mode']) ? '' : $_POST['fee_compute_mode'];

				if ($modules[0]['cod']) {
					$count++;
					$config[$count]['name'] = 'pay_fee';
					$config[$count]['value'] = make_semiangle(empty($_POST['pay_fee']) ? '' : $_POST['pay_fee']);
				}

				$other['tid'] = $tid;
				$other['shipping_id'] = $shipping_id;
				$other['region_id'] = $regionId;
				$other['configure'] = serialize($config);
				$other['user_id'] = $adminru['ru_id'];
				$other['tpl_name'] = isset($_REQUEST['tpl_name']) && !empty($_REQUEST['tpl_name']) ? dsc_addslashes($_REQUEST['tpl_name']) : '';
				$sql = 'SELECT count(*) FROM ' . $ecs->table('goods_transport_tpl') . (' WHERE id = \'' . $id . '\'');
				$res = $db->getOne($sql);

				if (0 < $res) {
					$db->autoExecute($ecs->table('goods_transport_tpl'), $other, 'UPDATE', 'id = \'' . $id . '\'');
				}
				else {
					$other['admin_id'] = $admin_id;
					$db->autoExecute($ecs->table('goods_transport_tpl'), $other, 'INSERT');
					$tpl_id[] = $GLOBALS['db']->insert_id();
				}

				if ($regionId) {
					$result['region_list'] = get_area_list($regionId);
				}
			}

			if ($tpl_id && isset($_SESSION[$tid]['tpl_id']) && !empty($_SESSION[$tid]['tpl_id'])) {
				$_SESSION[$tid]['tpl_id'] = array_merge($tpl_id, $_SESSION[$tid]['tpl_id']);
			}
			else {
				$_SESSION[$tid]['tpl_id'] = $tpl_id;
			}

			$shipping_tpl = get_transport_shipping_list($tid, $adminru['ru_id']);
			$smarty->assign('shipping_tpl', $shipping_tpl);
			$html = $smarty->fetch('library/goods_transport_tpl.lbi');
			$result['content'] = $html;
			exit($json->encode($result));
		}
		else if ($_REQUEST['act'] == 'drop_shipping') {
			require ROOT_PATH . '/includes/cls_json.php';
			$json = new JSON();
			$result = array('error' => 0, 'message' => '');
			$tid = empty($_REQUEST['tid']) ? 0 : intval($_REQUEST['tid']);
			$id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
			$sql = 'DELETE FROM' . $ecs->table('goods_transport_tpl') . ('WHERE id = \'' . $id . '\'');
			$db->query($sql);
			$shipping_tpl = get_transport_shipping_list($tid, $adminru['ru_id']);
			$smarty->assign('shipping_tpl', $shipping_tpl);
			$html = $smarty->fetch('library/goods_transport_tpl.lbi');
			$result['content'] = $html;
			exit($json->encode($result));
		}
		else if ($_REQUEST['act'] == 'select_area') {
			require ROOT_PATH . '/includes/cls_json.php';
			$json = new JSON();
			$result = array('error' => 0, 'message' => '');
			$tid = empty($_REQUEST['tid']) ? 0 : intval($_REQUEST['tid']);
			$shipping_id = empty($_REQUEST['shipping_id']) ? 0 : intval($_REQUEST['shipping_id']);
			$region_id = empty($_REQUEST['region_id']) ? 0 : intval($_REQUEST['region_id']);
			$parent_id = region_parent($region_id);
			$region_children = region_children($region_id);
			$region = $region_id . ',' . $parent_id . ',' . $region_children;
			$region = get_del_str_comma($region);
			$sql = 'SELECT GROUP_CONCAT(region_id) AS region_id FROM ' . $GLOBALS['ecs']->table('goods_transport_tpl') . ' WHERE user_id = \'' . $adminru['ru_id'] . '\'' . (' AND tid = \'' . $tid . '\' AND shipping_id = \'' . $shipping_id . '\'');
			$region_list = $GLOBALS['db']->getOne($sql);
			$region = !empty($region) ? explode(',', $region) : array();
			$region_list = !empty($region_list) ? explode(',', $region_list) : array();
			$assoc = array();
			if ($region && $region_list) {
				$assoc = array_intersect($region, $region_list);
			}

			if ($assoc) {
				$result['error'] = 1;
			}

			exit($json->encode($result));
		}
	}
}

?>
