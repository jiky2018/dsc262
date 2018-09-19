<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function get_brandlist($ru_id)
{
	$where = '';

	if (0 < $ru_id) {
		$where .= ' and user_id = \'' . $ru_id . '\'';
	}

	$result = get_filter();

	if ($result === false) {
		$filter = array();
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'msb.bid' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$brand_name = isset($_POST['brand_name']) ? trim($_POST['brand_name']) : '';
		$filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$filter['audit_status'] = isset($_REQUEST['audit_status']) ? trim($_REQUEST['audit_status']) : '';
		$store_where = '';
		$store_search_where = '';

		if ($filter['store_search'] != 0) {
			if ($ru_id == 0) {
				if ($_REQUEST['store_type']) {
					$store_search_where = 'AND msi.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
				}

				if ($filter['store_search'] == 1) {
					$where .= ' AND msb.user_id = \'' . $filter['merchant_id'] . '\' ';
				}
				else if ($filter['store_search'] == 2) {
					$store_where .= ' AND msi.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
				}
				else if ($filter['store_search'] == 3) {
					$store_where .= ' AND msi.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
				}

				if (1 < $filter['store_search']) {
					$where .= ' AND (SELECT msi.user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as msi ' . (' WHERE msi.user_id = msb.user_id ' . $store_where . ') > 0 ');
				}
			}
		}

		if ($filter['audit_status'] == 1 || $filter['audit_status'] == 2) {
			$where .= ' AND msb.audit_status = \'' . $filter['audit_status'] . '\'';
		}
		else if ($filter['audit_status'] == 3) {
			$where .= ' AND msb.audit_status = 0';
		}

		if (!empty($brand_name)) {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('merchants_shop_brand') . ' AS msb ' . ' WHERE msb.brandName LIKE \'%' . mysql_like_quote($brand_name) . '%\'' . $where;
		}
		else {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('merchants_shop_brand') . ' AS msb ' . ' where 1 ' . $where;
		}

		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);

		if (!empty($brand_name)) {
			if (strtoupper(EC_CHARSET) == 'GBK') {
				$keyword = iconv('UTF-8', 'gb2312', $brand_name);
			}
			else {
				$keyword = $brand_name;
			}

			$sql = 'SELECT msb.* FROM ' . $GLOBALS['ecs']->table('merchants_shop_brand') . ' AS msb ' . (' WHERE msb.brandName like \'%' . $keyword . '%\' ') . $where . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order']);
		}
		else {
			$sql = 'SELECT msb.* FROM ' . $GLOBALS['ecs']->table('merchants_shop_brand') . ' AS msb ' . ' where 1 ' . $where . (' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order']);
		}

		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
	$arr = array();

	while ($rows = $GLOBALS['db']->fetchRow($res)) {
		$site_url = empty($rows['site_url']) ? 'N/A' : '<a href="' . $rows['site_url'] . '" target="_brank">' . $rows['site_url'] . '</a>';
		$rows['site_url'] = $site_url;
		$brand_logo = empty($rows['brandLogo']) ? '' : '../' . $rows['brandLogo'];
		$site_url = empty($rows['site_url']) ? 'N/A' : '<a href="' . $rows['site_url'] . '" target="_brank">' . $rows['site_url'] . '</a>';
		$rows['brand_logo'] = $brand_logo;
		$rows['brand_id'] = $rows['bid'];
		$rows['brand_name'] = $rows['brandName'];
		$rows['brand_letter'] = $rows['bank_name_letter'];
		$rows['user_name'] = get_shop_name($rows['user_id'], 1);
		$rows['link_brand'] = get_link_brand_list($rows['bid'], 3);
		$arr[] = $rows;
	}

	return array('brand' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
include_once ROOT_PATH . 'includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table('merchants_shop_brand'), $db, 'bid', 'brandName');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

$smarty->assign('ru_id', $adminru['ru_id']);
$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => '07_merchants_brand'));

if ($_REQUEST['act'] == 'list') {
	$smarty->assign('ur_here', $_LANG['07_merchants_brand']);
	$smarty->assign('full_page', 1);

	if (0 < $adminru['ru_id']) {
		$smarty->assign('action_link', array('text' => $_LANG['07_brand_add'], 'href' => 'merchants_brand.php?act=add'));
	}

	$brand_list = get_brandlist($adminru['ru_id']);
	$smarty->assign('brand_list', $brand_list['brand']);
	$smarty->assign('filter', $brand_list['filter']);
	$smarty->assign('record_count', $brand_list['record_count']);
	$smarty->assign('page_count', $brand_list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	assign_query_info();
	$smarty->display('merchants_brand_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$brand_list = get_brandlist($adminru['ru_id']);
	$smarty->assign('brand_list', $brand_list['brand']);
	$smarty->assign('filter', $brand_list['filter']);
	$smarty->assign('record_count', $brand_list['record_count']);
	$smarty->assign('page_count', $brand_list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	make_json_result($smarty->fetch('merchants_brand_list.dwt'), '', array('filter' => $brand_list['filter'], 'page_count' => $brand_list['page_count']));
}
else if ($_REQUEST['act'] == 'edit') {
	admin_priv('merchants_brand');
	$brand_id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$sql = 'SELECT bid as brand_id, brandName as brand_name, bank_name_letter, site_url, brandLogo as brand_logo, ' . 'user_id, brand_desc, is_show, sort_order, audit_status, major_business ' . 'FROM ' . $ecs->table('merchants_shop_brand') . (' WHERE bid = \'' . $brand_id . '\'');
	$brand = $db->GetRow($sql);
	$platform_brand_list = get_merchants_search_brand(1, 3);
	$smarty->assign('brand_list', $platform_brand_list);
	$link_brand = get_link_brand_list($brand['brand_id'], 3);
	$smarty->assign('link_brand', $link_brand);
	$smarty->assign('ubrand', $_REQUEST['ubrand']);
	$smarty->assign('ur_here', $_LANG['brand_edit']);
	$smarty->assign('action_link', array('text' => $_LANG['07_merchants_brand'], 'href' => 'merchants_brand.php?act=list&' . list_link_postfix()));
	$smarty->assign('brand', $brand);
	$smarty->assign('form_action', 'updata');
	$date = array('major_brand');
	$where = ' ru_id = \'' . $adminru['ru_id'] . '\'';
	$major_brand = get_table_date('admin_user', $where, $date, 2);
	$smarty->assign('major_brand', $major_brand);
	set_default_filter();
	assign_query_info();
	$smarty->display('merchants_brand_info.dwt');
}
else if ($_REQUEST['act'] == 'updata') {
	admin_priv('merchants_brand');
	$major_business = isset($_POST['major_business']) ? intval($_POST['major_business']) : '';
	$brand_name = !empty($_POST['mer_brand_name']) ? trim($_POST['mer_brand_name']) : '';
	$brand_letter = !empty($_POST['brank_letter']) ? trim($_POST['brank_letter']) : '';
	$bid = isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : 0;
	$linkBrand = isset($_POST['link_brand']) && !empty($_POST['id']) ? intval($_POST['link_brand']) : 0;
	$ru_id = isset($_POST['ru_id']) && !empty($_POST['ru_id']) ? intval($_POST['ru_id']) : 0;
	$audit_status = isset($_POST['audit_status']) && !empty($_POST['audit_status']) ? intval($_POST['audit_status']) : 0;

	if ($brand_name != $_POST['old_brandname']) {
		$is_only = $exc->is_only('brandName', $brand_name, $bid, '', 'merchants_shop_brand', 'bid');

		if (!$is_only) {
			sys_msg(sprintf($_LANG['brandname_exist'], stripslashes($brand_name)), 1);
		}
	}

	if (!empty($_POST['brand_desc'])) {
		$_POST['brand_desc'] = $_POST['brand_desc'];
	}

	$is_show = isset($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0;
	$site_url = sanitize_url($_POST['site_url']);

	if (!empty($_FILES['brand_logo']['name'])) {
		$img_name = 'data/septs_Image/' . basename($image->upload_image($_FILES['brand_logo'], 'septs_Image'));
	}
	else {
		$img_name = '';
	}

	get_oss_add_file(array($img_name));
	if (isset($_POST['link_brand']) && 0 < $adminru['ru_id']) {
		$parent['major_brand'] = $bid;
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('admin_user'), $parent, 'UPDTAE', 'ru_id =' . $adminru['ru_id']);
	}

	if (isset($_POST['major_business'])) {
		$major_business = ',  major_business=\'' . $major_business . '\'';
	}

	if (isset($_POST['audit_status']) && $adminru['ru_id'] == 0) {
		$audit_status = ', audit_status= \'' . $audit_status . '\'';

		if ($_POST['audit_status'] == 1) {
			if (empty($linkBrand)) {
				$sql = 'SELECT brand_id FROM ' . $GLOBALS['ecs']->table('brand') . (' WHERE brand_name = \'' . $brand_name . '\'');
			}
			else {
				$sql = 'SELECT brand_id FROM ' . $GLOBALS['ecs']->table('brand') . (' WHERE brand_id = \'' . $linkBrand . '\'');
			}

			$brand_id = $GLOBALS['db']->getOne($sql);

			if (!$brand_id) {
				$sql = 'INSERT INTO ' . $ecs->table('brand') . (' (`brand_name`, `brand_letter`) VALUES (\'' . $brand_name . '\', \'' . $brand_letter . '\')');
				$GLOBALS['db']->query($sql);
				$linkBrand = $GLOBALS['db']->insert_id();
			}

			if (0 < $linkBrand) {
				$sql = 'SELECT id FROM ' . $GLOBALS['ecs']->table('link_brand') . (' WHERE bid= \'' . $bid . '\'');
				$lid = $GLOBALS['db']->getOne($sql);
				$link_brand = array('bid' => $bid, 'brand_id' => $linkBrand);

				if ($lid) {
					if ($audit_status == 1) {
						$goods_brand_id = $GLOBALS['db']->getOne('SELECT brand_id FROM ' . $GLOBALS['ecs']->table('link_brand') . (' WHERE bid =\'' . $id . '\''), true);
						$sql = 'UPDATE ' . $ecs->table('goods') . (' SET brand_id = \'' . $linkBrand . '\' WHERE brand_id = \'' . $goods_brand_id . '\' AND user_id = \'' . $ru_id . '\'');
						$db->query($sql);
						dsc_unlink(ROOT_PATH . DATA_DIR . '/sc_file/seller_brand/seller_brand_' . $ru_id);
					}

					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('link_brand'), $link_brand, 'UPDTAE', 'bid = \'' . $bid . '\'');
				}
				else {
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('link_brand'), $link_brand, 'INSERT');
				}
			}
		}
	}

	$param = 'brandName = \'' . $brand_name . '\', bank_name_letter = \'' . $brand_letter . '\',  site_url=\'' . $site_url . '\', brand_desc=\'' . $_POST['brand_desc'] . '\' ' . $audit_status . ', is_show=\'' . $is_show . '\', sort_order=\'' . $_POST['sort_order'] . '\' ' . $major_business;

	if (!empty($img_name)) {
		$param .= ' ,brandLogo = \'' . $img_name . '\' ';
	}

	if ($exc->edit($param, $bid, 'merchants_shop_brand', 'bid')) {
		clear_cache_files();
		admin_log($brand_name, 'edit', 'merchants_shop_brand');
		$link[0]['text'] = $_LANG['back_list'];
		$link[0]['href'] = 'merchants_brand.php?act=list&' . list_link_postfix();
		$note = vsprintf($_LANG['brandedit_succed'], $brand_name);
		sys_msg($note, 0, $link);
	}
	else {
		exit($db->error());
	}
}
else if ($_REQUEST['act'] == 'edit_brand_name') {
	check_authz_json('merchants_brand');
	$id = intval($_POST['id']);
	$name = json_str_iconv(trim($_POST['val']));

	if ($exc->num('brandName', $name, $id, 'user_id = \'' . $adminru['ru_id'] . '\'') != 0) {
		make_json_error(sprintf($_LANG['brandname_exist'], $name));
	}
	else if ($exc->edit('brandName = \'' . $name . '\'', $id)) {
		admin_log($name, 'edit', 'merchants_shop_brand');
		make_json_result(stripslashes($name));
	}
	else {
		make_json_result(sprintf($_LANG['brandedit_fail'], $name));
	}
}
else if ($_REQUEST['act'] == 'edit_brand_letter') {
	check_authz_json('brand_manage');
	$id = intval($_POST['id']);
	$name = json_str_iconv(trim($_POST['val']));

	if ($exc->num('bank_name_letter', $name, $id) != 0) {
		make_json_error(sprintf($_LANG['brandname_exist'], $name));
	}
	else if ($exc->edit('bank_name_letter = \'' . $name . '\'', $id)) {
		admin_log($name, 'edit', 'merchants_shop_brand');
		make_json_result(stripslashes($name));
	}
	else {
		make_json_result(sprintf($_LANG['brandedit_fail'], $name));
	}
}
else if ($_REQUEST['act'] == 'edit_sort_order') {
	check_authz_json('merchants_brand');
	$id = intval($_POST['id']);
	$order = intval($_POST['val']);
	$name = $exc->get_name($id);

	if ($exc->edit('sort_order = \'' . $order . '\'', $id)) {
		admin_log(addslashes($name), 'edit', 'merchants_shop_brand');
		make_json_result($order);
	}
	else {
		make_json_error(sprintf($_LANG['brandedit_fail'], $name));
	}
}
else if ($_REQUEST['act'] == 'toggle_show') {
	check_authz_json('merchants_brand');
	$id = intval($_POST['id']);
	$val = intval($_POST['val']);
	$exc->edit('is_show=\'' . $val . '\'', $id);
	make_json_result($val);
}
else if ($_REQUEST['act'] == 'remove') {
	check_authz_json('merchants_brand');
	$id = intval($_GET['id']);
	$ru_id = $GLOBALS['db']->getOne('SELECT user_id FROM ' . $GLOBALS['ecs']->table('merchants_shop_brand') . (' WHERE bid =\'' . $id . '\''), true);
	$brand_id = $GLOBALS['db']->getOne('SELECT brand_id FROM ' . $GLOBALS['ecs']->table('link_brand') . (' WHERE bid =\'' . $id . '\''), true);
	get_del_batch('', $id, array('brandLogo'), 'bid', 'merchants_shop_brand', 1);
	$exc->drop($id, 'merchants_shop_brand', 'bid');
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('merchants_shop_brandfile') . (' WHERE bid = \'' . $id . '\'');
	$GLOBALS['db']->query($sql);
	$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('link_brand') . (' WHERE bid = \'' . $id . '\'');
	$GLOBALS['db']->query($sql);
	$sql = 'UPDATE ' . $ecs->table('goods') . (' SET brand_id = 0 WHERE brand_id = \'' . $brand_id . '\' AND user_id = \'' . $ru_id . '\'');
	$db->query($sql);
	dsc_unlink(ROOT_PATH . DATA_DIR . '/sc_file/seller_brand/seller_brand_' . $ru_id);
	$url = 'merchants_brand.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
	ecs_header('Location: ' . $url . "\n");
	exit();
}
else if ($_REQUEST['act'] == 'drop_logo') {
	admin_priv('merchants_brand');
	$brand_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
	get_del_batch('', $brand_id, array('brandLogo'), 'bid', 'merchants_shop_brand', 1);
	$sql = 'UPDATE ' . $ecs->table('merchants_shop_brand') . (' SET brandLogo = \'\' WHERE bid = \'' . $brand_id . '\'');
	$db->query($sql);
	$link = array(
		array('text' => $_LANG['brand_edit_lnk'], 'href' => 'merchants_brand.php?act=edit&id=' . $brand_id),
		array('text' => $_LANG['brand_list_lnk'], 'href' => 'merchants_brand.php?act=list')
		);
	sys_msg($_LANG['drop_brand_logo_success'], 0, $link);
}

?>
