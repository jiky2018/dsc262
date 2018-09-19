<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_shipping_area_list($shipping_id, $ru_id)
{
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('shipping_area') . ' where ru_id=\'' . $ru_id . '\' ';

	if (0 < $shipping_id) {
		$sql .= ' and shipping_id = \'' . $shipping_id . '\'';
	}

	$res = $GLOBALS['db']->query($sql);
	$list = array();

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$sql = 'SELECT r.region_name ' . 'FROM ' . $GLOBALS['ecs']->table('area_region') . ' AS a, ' . $GLOBALS['ecs']->table('region') . ' AS r ' . 'WHERE a.region_id = r.region_id ' . 'AND a.shipping_area_id = \'' . $row['shipping_area_id'] . '\'';
		$regions = join(', ', $GLOBALS['db']->getCol($sql));
		$row['shipping_area_regions'] = empty($regions) ? '<a href="shipping_area.php?act=edit&amp;id=' . $row['shipping_area_id'] . '" style="color:red">' . $GLOBALS['_LANG']['empty_regions'] . '</a>' : $regions;
		$list[] = $row;
	}

	return $list;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
include_once ROOT_PATH . '/includes/cls_image.php';
$exc = new exchange($ecs->table('shipping_area'), $db, 'shipping_area_id', 'shipping_area_name');
$image = new cls_image($_CFG['bgcolor']);
$smarty->assign('menus', $_SESSION['menus']);
$adminru = get_admin_ru_id();
$smarty->assign('menu_select', array('action' => '11_system', 'current' => '03_shipping_list'));

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('primary_cat', $_LANG['11_system']);
	$shipping_id = intval($_REQUEST['shipping']);
	$sql = 'SELECT shipping_code FROM ' . $ecs->table('shipping') . ' WHERE shipping_id=\'' . $shipping_id . '\'';
	$shipping_code = $db->getOne($sql);
	$list = get_shipping_area_list($shipping_id, $adminru['ru_id']);
	if (!empty($list) && ($shipping_code == 'cac')) {
		foreach ($list as $key => $val) {
			$sql = 'SELECT name FROM ' . $ecs->table('shipping_point') . ' WHERE shipping_area_id=\'' . $val['shipping_area_id'] . '\'';
			$list[$key]['name'] = $db->getAll($sql);
		}
	}

	$smarty->assign('areas', $list);
	$smarty->assign('ur_here', $_LANG['03_shipping_list'] . ' - ' . $_LANG['shipping_area_list']);
	$smarty->assign('action_link', array('href' => 'shipping_area.php?act=add&shipping=' . $shipping_id, 'text' => $_LANG['new_area'], 'class' => 'icon-plus'));
	$smarty->assign('action_link2', array('href' => 'shipping.php?act=list', 'text' => $_LANG['03_shipping_list'], 'class' => 'icon-reply'));
	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->assign('current', 'shipping');
	$smarty->assign('shipping_code', $shipping_code);
	$smarty->display('shipping_area_list.dwt');
}
else {
	if (($_REQUEST['act'] == 'add') && !empty($_REQUEST['shipping'])) {
		admin_priv('shiparea_manage');
		$smarty->assign('action_link', array('href' => 'shipping_area.php?act=list&shipping=' . $_REQUEST['shipping'], 'text' => $_LANG['09_region_area_management'], 'class' => 'icon-reply'));
		$shipping = $db->getRow('SELECT shipping_name, shipping_code FROM ' . $ecs->table('shipping') . ' WHERE shipping_id=\'' . $_REQUEST['shipping'] . '\'');
		$smarty->assign('primary_cat', $_LANG['11_system']);
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
		$smarty->assign('ur_here', $shipping['shipping_name'] . ' - ' . $_LANG['new_area']);
		$smarty->assign('shipping_area', array('shipping_id' => $_REQUEST['shipping'], 'shipping_code' => $shipping['shipping_code']));
		$smarty->assign('fields', $fields);
		$smarty->assign('form_action', 'insert');
		$smarty->assign('countries', get_regions());
		$smarty->assign('province_all', get_regions(1, 1));
		$smarty->assign('default_country', $_CFG['shop_country']);
		assign_query_info();
		$smarty->assign('current', 'shipping');
		$smarty->display('shipping_area_info.dwt');
	}
	else if ($_REQUEST['act'] == 'insert') {
		admin_priv('shiparea_manage');
		$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('shipping_area') . ' WHERE shipping_id=\'' . $_POST['shipping'] . '\' AND shipping_area_name=\'' . $_POST['shipping_area_name'] . '\' and ru_id=\'' . $adminru['ru_id'] . '\'';

		if (0 < $db->getOne($sql)) {
			sys_msg($_LANG['repeat_area_name'], 1);
		}
		else {
			$shipping_code = $db->getOne('SELECT shipping_code FROM ' . $ecs->table('shipping') . ' WHERE shipping_id=\'' . $_POST['shipping'] . '\'');
			$plugin = '../includes/modules/shipping/' . $shipping_code . '.php';

			if (!file_exists($plugin)) {
				sys_msg($_LANG['not_find_plugin'], 1);
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

			$sql = 'INSERT INTO ' . $ecs->table('shipping_area') . ' (shipping_area_name, shipping_id, configure,ru_id) ' . 'VALUES' . ' (\'' . $_POST['shipping_area_name'] . '\', \'' . $_POST['shipping'] . '\', \'' . serialize($config) . '\',\'' . $adminru['ru_id'] . '\')';
			$db->query($sql);
			$new_id = $db->insert_Id();

			if ($shipping_code == 'cac') {
				$district = (isset($_POST['district']) ? intval($_POST['district']) : 0);

				if ($district == 0) {
					sys_msg('请选择所辖区域！', 1);
				}

				$sql = 'INSERT INTO ' . $ecs->table('area_region') . ' (shipping_area_id, region_id) VALUES (\'' . $new_id . '\', \'' . $district . '\')';
				$db->query($sql);
			}
			else {
				if (isset($_POST['regions']) && is_array($_POST['regions'])) {
					foreach ($_POST['regions'] as $key => $val) {
						$sql = 'INSERT INTO ' . $ecs->table('area_region') . ' (shipping_area_id, region_id,ru_id) VALUES (\'' . $new_id . '\', \'' . $val . '\',\'' . $adminru['ru_id'] . '\')';
						$db->query($sql);
					}
				}
			}

			$point_name = (isset($_POST['point_name']) ? $_POST['point_name'] : array());
			$user_name = (isset($_POST['user_name']) ? $_POST['user_name'] : array());
			$mobile = (isset($_POST['mobile']) ? $_POST['mobile'] : array());
			$address = (isset($_POST['address']) ? $_POST['address'] : array());
			$anchor = (isset($_POST['anchor']) ? $_POST['anchor'] : array());
			$line = (isset($_POST['line']) ? $_POST['line'] : array());

			if ($point_name) {
				foreach ($point_name as $key => $val) {
					if (empty($val)) {
						continue;
					}

					$upload = array('name' => $_FILES['img_url']['name'][$key], 'type' => $_FILES['img_url']['type'][$key], 'tmp_name' => $_FILES['img_url']['tmp_name'][$key], 'size' => $_FILES['img_url']['size'][$key]);

					if (isset($_FILES['img_url']['error'])) {
						$upload['error'] = $_FILES['img_url']['error'][$key];
					}

					$map_img = $image->upload_image($upload, 'map_img');
					$sql = 'INSERT INTO ' . $ecs->table('shipping_point') . ' (shipping_area_id, name, user_name, mobile,address,img_url,anchor,line) ' . ' VALUES (\'' . $new_id . '\', \'' . $val . '\' , \'' . $user_name[$key] . '\' ,\'' . $mobile[$key] . '\' , \'' . $address[$key] . '\', \'' . $map_img . '\', \'' . $anchor[$key] . '\', \'' . $line[$key] . '\')';
					$db->query($sql);
				}

				admin_log($_POST['shipping_area_name'], 'add', 'shipping_area');
			}

			$lnk[] = array('text' => $_LANG['back_list'], 'href' => 'shipping_area.php?act=list&shipping=' . $_POST['shipping']);
			$lnk[] = array('text' => $_LANG['add_continue'], 'href' => 'shipping_area.php?act=add&shipping=' . $_POST['shipping']);
			sys_msg($_LANG['add_area_success'], 0, $lnk, true, true);
		}
	}
	else if ($_REQUEST['act'] == 'edit') {
		admin_priv('shiparea_manage');
		$smarty->assign('primary_cat', $_LANG['11_system']);
		$sql = 'SELECT a.shipping_name, a.shipping_code, a.support_cod, b.* ' . 'FROM ' . $ecs->table('shipping') . ' AS a, ' . $ecs->table('shipping_area') . ' AS b ' . 'WHERE b.shipping_id=a.shipping_id AND b.shipping_area_id=\'' . $_REQUEST['id'] . '\' and b.ru_id=\'' . $adminru['ru_id'] . '\'';
		$row = $db->getRow($sql);
		if (!empty($row) && ($row['shipping_code'] == 'cac')) {
			$sql = 'SELECT * FROM ' . $ecs->table('shipping_point') . ' WHERE shipping_area_id=\'' . $row['shipping_area_id'] . '\'';
			$row['point'] = $db->getAll($sql);
		}

		$set_modules = 1;
		include_once ROOT_PATH . 'includes/modules/shipping/' . $row['shipping_code'] . '.php';
		$fields = unserialize($row['configure']);
		if ($row['support_cod'] && ($fields[count($fields) - 1]['name'] != 'pay_fee')) {
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

		$regions = array();
		$sql = 'SELECT a.region_id, r.region_name ' . 'FROM ' . $ecs->table('area_region') . ' AS a, ' . $ecs->table('region') . ' AS r ' . 'WHERE r.region_id=a.region_id AND a.shipping_area_id=\'' . $_REQUEST['id'] . '\' and a.ru_id=\'' . $adminru['ru_id'] . '\'';
		$res = $db->query($sql);

		while ($arr = $db->fetchRow($res)) {
			$regions[$arr['region_id']] = $arr['region_name'];
		}

		$sql = 'SELECT region_id FROM ' . $ecs->table('area_region') . ' WHERE shipping_area_id = \'' . $_REQUEST['id'] . '\'';
		$region_id = $db->getOne($sql);
		if ($region_id && ($row['shipping_code'] == 'cac')) {
			$sql = 'SELECT * FROM ' . $ecs->table('region') . ' WHERE region_id = \'' . $region_id . '\'';
			$district = $db->getRow($sql);
			$sql = 'SELECT * FROM ' . $ecs->table('region') . ' WHERE parent_id = \'' . $district['parent_id'] . '\'';
			$district_all = $db->getAll($sql);
			$sql = 'SELECT * FROM ' . $ecs->table('region') . ' WHERE region_id = \'' . $district['parent_id'] . '\'';
			$city = $db->getRow($sql);
			$sql = 'SELECT * FROM ' . $ecs->table('region') . ' WHERE parent_id = \'' . $city['parent_id'] . '\'';
			$city_all = $db->getAll($sql);
			$sql = 'SELECT * FROM ' . $ecs->table('region') . ' WHERE region_id = \'' . $city['parent_id'] . '\'';
			$province = $db->getRow($sql);
			$sql = 'SELECT * FROM ' . $ecs->table('region') . ' WHERE parent_id = \'' . $province['parent_id'] . '\'';
			$province_all = $db->getAll($sql);
		}

		$smarty->assign('action_link', array('href' => 'shipping_area.php?act=list&shipping=' . $row['shipping_id'], 'text' => $_LANG['09_region_area_management'], 'class' => 'icon-reply'));
		assign_query_info();
		$smarty->assign('ur_here', $row['shipping_name'] . ' - ' . $_LANG['edit_area']);
		$smarty->assign('id', $_REQUEST['id']);
		$smarty->assign('fields', $fields);
		$smarty->assign('shipping_area', $row);
		$smarty->assign('regions', $regions);
		$smarty->assign('form_action', 'update');
		$smarty->assign('countries', get_regions());
		$smarty->assign('district', $district);
		$smarty->assign('district_all', $district_all);
		$smarty->assign('city', $city);
		$smarty->assign('city_all', $city_all);
		$smarty->assign('province', $province);
		$smarty->assign('province_all', $province_all);
		$smarty->assign('default_country', 1);
		$smarty->assign('current', 'shipping');
		$smarty->display('shipping_area_info.dwt');
	}
	else if ($_REQUEST['act'] == 'update') {
		admin_priv('shiparea_manage');
		$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('shipping_area') . ' WHERE shipping_id=\'' . $_POST['shipping'] . '\' AND ' . 'shipping_area_name=\'' . $_POST['shipping_area_name'] . '\' AND ' . 'shipping_area_id<>\'' . $_POST['id'] . '\' and ru_id=\'' . $adminru['ru_id'] . '\'';

		if (0 < $db->getOne($sql)) {
			sys_msg($_LANG['repeat_area_name'], 1);
		}
		else {
			$shipping_code = $db->getOne('SELECT shipping_code FROM ' . $ecs->table('shipping') . ' WHERE shipping_id=\'' . $_POST['shipping'] . '\'');
			$plugin = '../includes/modules/shipping/' . $shipping_code . '.php';

			if (!file_exists($plugin)) {
				sys_msg($_LANG['not_find_plugin'], 1);
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

			$sql = 'UPDATE ' . $ecs->table('shipping_area') . ' SET shipping_area_name=\'' . $_POST['shipping_area_name'] . '\', ' . 'configure=\'' . serialize($config) . '\' ' . 'WHERE shipping_area_id=\'' . $_POST['id'] . '\' and ru_id=\'' . $adminru['ru_id'] . '\'';
			$db->query($sql);
			$point_id = (isset($_POST['point_id']) ? $_POST['point_id'] : array());
			$point_name = (isset($_POST['point_name']) ? $_POST['point_name'] : array());
			$user_name = (isset($_POST['user_name']) ? $_POST['user_name'] : array());
			$mobile = (isset($_POST['mobile']) ? $_POST['mobile'] : array());
			$address = (isset($_POST['address']) ? $_POST['address'] : array());
			$map_img = (isset($_POST['map_img']) ? $_POST['map_img'] : array());
			$anchor = (isset($_POST['anchor']) ? $_POST['anchor'] : array());
			$line = (isset($_POST['line']) ? $_POST['line'] : array());
			if ($point_name && $mobile && $address) {
				foreach ($point_name as $key => $val) {
					if (empty($val)) {
						continue;
					}

					$upload = array('name' => $_FILES['img_url']['name'][$key], 'type' => $_FILES['img_url']['type'][$key], 'tmp_name' => $_FILES['img_url']['tmp_name'][$key], 'size' => $_FILES['img_url']['size'][$key]);

					if (isset($_FILES['img_url']['error'])) {
						$upload['error'] = $_FILES['img_url']['error'][$key];
					}

					$map_img = $image->upload_image($upload, 'map_img');
					if (!$map_img && $map_img[$key]) {
						$map_img = $map_img[$key];
					}

					if ($_POST['point_id'][$key]) {
						$sql = 'UPDATE ' . $ecs->table('shipping_point') . 'SET name=\'' . $point_name[$key] . '\' , user_name=\'' . $user_name[$key] . '\' ,' . ' mobile=\'' . $mobile[$key] . '\' , address=\'' . $address[$key] . '\', img_url=\'' . $map_img . '\', anchor=\'' . $anchor[$key] . '\', line=\'' . $line[$key] . '\'' . 'WHERE id=\'' . $point_id[$key] . '\'';
					}
					else {
						$sql = 'INSERT INTO ' . $ecs->table('shipping_point') . ' (shipping_area_id, name, user_name, mobile, address, img_url, anchor, line) ' . ' VALUES (\'' . $_POST['id'] . '\', \'' . $point_name[$key] . '\' , \'' . $user_name[$key] . '\' ,\'' . $mobile[$key] . '\' , \'' . $address[$key] . '\', \'' . $map_img . '\', \'' . $anchor[$key] . '\', \'' . $line[$key] . '\')';
					}

					$db->query($sql);
				}
			}

			admin_log($_POST['shipping_area_name'], 'edit', 'shipping_area');
			$selected_regions = array();

			if (isset($_POST['regions'])) {
				foreach ($_POST['regions'] as $region_id) {
					$selected_regions[$region_id] = $region_id;
				}
			}

			$sql = 'SELECT region_id, parent_id FROM ' . $ecs->table('region');
			$res = $db->query($sql);

			while ($row = $db->fetchRow($res)) {
				$region_list[$row['region_id']] = $row['parent_id'];
			}

			foreach ($selected_regions as $region_id) {
				$id = $region_id;

				while ($region_list[$id] != 0) {
					$id = $region_list[$id];

					if (isset($selected_regions[$id])) {
						unset($selected_regions[$region_id]);
						break;
					}
				}
			}

			$db->query('DELETE FROM ' . $ecs->table('area_region') . ' WHERE shipping_area_id=\'' . $_POST['id'] . '\' and ru_id=\'' . $adminru['ru_id'] . '\'');

			if ($shipping_code == 'cac') {
				$district = (isset($_POST['district']) ? intval($_POST['district']) : 0);

				if ($district == 0) {
					sys_msg('请选择所辖区域！', 1);
				}

				$sql = 'INSERT INTO ' . $ecs->table('area_region') . ' (shipping_area_id, region_id) VALUES (\'' . $_POST['id'] . '\', \'' . $district . '\')';
				$db->query($sql);
			}
			else {
				foreach ($selected_regions as $key => $val) {
					$sql = 'INSERT INTO ' . $ecs->table('area_region') . ' (shipping_area_id, region_id,ru_id) VALUES (\'' . $_POST['id'] . '\', \'' . $val . '\',\'' . $adminru['ru_id'] . '\')';
					$db->query($sql);
				}
			}

			$lnk[] = array('text' => $_LANG['back_list'], 'href' => 'shipping_area.php?act=list&shipping=' . $_POST['shipping']);
			sys_msg($_LANG['edit_area_success'], 0, $lnk, true, true);
		}
	}
	else if ($_REQUEST['act'] == 'multi_remove') {
		admin_priv('shiparea_manage');
		if (isset($_POST['checkboxes']) && (0 < count($_POST['checkboxes']))) {
			$i = 0;

			foreach ($_POST['checkboxes'] as $v) {
				$db->query('DELETE FROM ' . $ecs->table('shipping_area') . ' WHERE shipping_area_id=\'' . $v . '\' and ru_id=\'' . $adminru['ru_id'] . '\'');
				$i++;
			}

			admin_log('', 'batch_remove', 'shipping_area');
		}

		$links[0] = array('href' => 'shipping_area.php?act=list&shipping=' . intval($_REQUEST['shipping']), 'text' => $_LANG['go_back']);
		sys_msg($_LANG['remove_success'], 0, $links);
	}
	else if ($_REQUEST['act'] == 'edit_area') {
		check_authz_json('shiparea_manage');
		$id = intval($_POST['id']);
		$val = json_str_iconv(trim($_POST['val']));
		$shipping_id = $exc->get_name($id, 'shipping_id');

		if (!$exc->is_only('shipping_area_name', $val, $id, 'shipping_id = \'' . $shipping_id . '\' and ru_id=\'' . $adminru['ru_id'] . '\'')) {
			make_json_error($_LANG['repeat_area_name']);
		}

		$exc->edit('shipping_area_name = \'' . $val . '\'', $id);
		admin_log($val, 'edit', 'shipping_area');
		make_json_result(stripcslashes($val));
	}
	else if ($_REQUEST['act'] == 'remove_area') {
		check_authz_json('shiparea_manage');
		$id = intval($_GET['id']);
		$name = $exc->get_name($id);
		$shipping_id = $exc->get_name($id, 'shipping_id');
		$exc->drop($id);
		$db->query('DELETE FROM ' . $ecs->table('shipping_area') . ' WHERE shipping_area_id=' . $id . ' and ru_id=\'' . $adminru['ru_id'] . '\'');
		$db->query('DELETE FROM ' . $ecs->table('area_region') . ' WHERE shipping_area_id=' . $id . ' and ru_id=\'' . $adminru['ru_id'] . '\'');
		admin_log($name, 'remove', 'shipping_area');
		$sql = 'SELECT shipping_code FROM ' . $ecs->table('shipping') . ' WHERE shipping_id=' . $shipping_id;
		$shipping_code = $db->getOne($sql);
		$list = get_shipping_area_list($shipping_id, $adminru['ru_id']);
		if (!empty($list) && ($shipping_code == 'cac')) {
			foreach ($list as $key => $val) {
				$sql = 'SELECT name FROM ' . $ecs->table('shipping_point') . ' WHERE shipping_area_id=' . $val['shipping_area_id'];
				$list[$key]['name'] = $db->getAll($sql);
			}
		}

		$smarty->assign('areas', $list);
		$smarty->assign('shipping_code', $shipping_code);
		$smarty->assign('current', 'shipping');
		make_json_result($smarty->fetch('shipping_area_list.dwt'));
	}
	else if ($_REQUEST['act'] == 'remove_point') {
		check_authz_json('shiparea_manage');
		include_once ROOT_PATH . 'includes/cls_json.php';
		$id = intval($_GET['id']);
		$name = $exc->get_name($id);
		$sql = 'DELETE FROM ' . $ecs->table('shipping_point') . ' WHERE id=\'' . $id . '\'';

		if ($db->query($sql)) {
			$data = array('error' => 2, 'message' => '删除成功', 'content' => '');
			admin_log($name, 'remove', 'shipping_area');
		}
		else {
			$data = array('error' => 0, 'message' => '删除失败', 'content' => '');
		}

		$json = new JSON();
		exit($json->encode($data));
	}
}

?>
