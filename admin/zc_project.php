<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_item_list($cat_id = '')
{
	$result = get_filter();

	if ($result === false) {
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('zc_project');
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);

		if ($cat_id) {
			$where = ' WHERE cat_id = \'' . $cat_id . '\' ';
		}
		else {
			$where = ' ';
		}

		$filter = page_and_size($filter);
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('zc_project') . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$arr = array();
	$res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

	while ($row = $GLOBALS['db']->fetchRow($res)) {
		$arr[] = $row;
	}

	$arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function zc_project_list($conditions = '')
{
	$result = get_filter();

	if ($result === false) {
		$filter['cat_id'] = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
		$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$where = ' WHERE 1 ';
		$where .= (0 < $filter['cat_id'] ? ' AND ' . zc_get_children($filter['cat_id']) : '');

		if (!empty($filter['keyword'])) {
			$where .= ' AND title LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\' ';
		}

		$where .= $conditions;
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('zc_project') . ' AS g  ' . $where;
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = 'SELECT `id`, `cat_id`, `title`, `start_time`, `end_time`, `amount`, `is_best`, `join_money`, `join_num`, `title_img`, `describe` ' . ' FROM ' . $GLOBALS['ecs']->table('zc_project') . ' AS g  ' . $where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' ' . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keyword'] = stripslashes($filter['keyword']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$row = $GLOBALS['db']->getAll($sql);

	foreach ($row as $k => $val) {
		$row[$k]['start_time'] = date('Y-m-d', $val['start_time']);
		$row[$k]['end_time'] = date('Y-m-d', $val['end_time']);

		if ($val['end_time'] < time()) {
			$status = $GLOBALS['_LANG']['zc_out'];

			if ($val['amount'] < $val['join_money']) {
				$row[$k]['result'] = 1;
			}
			else {
				$row[$k]['result'] = 2;
			}
		}
		else if (time() < $val['start_time']) {
			$status = $GLOBALS['_LANG']['zc_soon'];
		}
		else {
			$status = $GLOBALS['_LANG']['zc_in'];
		}

		$row[$k]['zc_status'] = $status;
	}

	return array('zc_projects' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function zc_get_children($cat = 0)
{
	return 'g.cat_id ' . db_create_in(array_unique(array_merge(array($cat), get_array_keys_cat($cat, 0, 'zc_category'))));
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require dirname(__FILE__) . '/includes/lib_goods.php';
include_once ROOT_PATH . '/includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table('zc_project'), $db, 'id', 'title');

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$smarty->assign('act', $_REQUEST['act']);

if ($_REQUEST['act'] == 'list') {
	admin_priv('zc_project_manage');
	$smarty->assign('ur_here', $_LANG['01_crowdfunding_list']);
	$action_link = array('href' => 'zc_project.php?act=add', 'text' => $_LANG['add_zc_project']);
	$smarty->assign('action_link', $action_link);
	$list = zc_project_list();
	set_default_filter(0, 0, 0, 0, 'zc_category');
	$smarty->assign('table', 'zc_category');
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('arr_zc', $list['zc_projects']);
	$smarty->display('zc_project_list.dwt');
}

if ($_REQUEST['act'] == 'query') {
	$list = zc_project_list();
	$smarty->assign('filter', $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count', $list['page_count']);
	$smarty->assign('arr_zc', $list['zc_projects']);
	make_json_result($smarty->fetch('zc_project_list.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
else {
	if (($_REQUEST['act'] == 'add') || ($_REQUEST['act'] == 'edit')) {
		admin_priv('zc_project_manage');

		if ($_REQUEST['act'] == 'add') {
			$smarty->assign('ur_here', $_LANG['add_zc_project']);
		}

		if ($_REQUEST['act'] == 'edit') {
			$smarty->assign('ur_here', $_LANG['edit_zc_project']);
		}

		$action_link = array('href' => 'zc_project.php?act=list', 'text' => $_LANG['01_crowdfunding_list']);
		$smarty->assign('action_link', $action_link);
		$id = (isset($_GET['id']) && !empty($_GET['id']) ? intval($_GET['id']) : 0);

		if ($id) {
			$sql = ' SELECT * FROM ' . $ecs->table('zc_project') . ' WHERE id = \'' . $id . '\' ';
			$result = $db->getRow($sql);
			create_html_editor2('details', 'details', $result['details']);
			create_html_editor2('describe', 'describe', $result['describe']);
			create_html_editor2('risk_instruction', 'risk_instruction', $result['risk_instruction']);
			$result['start_time'] = date('Y-m-d', $result['start_time']);
			$result['end_time'] = date('Y-m-d', $result['end_time']);
			$cat_id = $result['cat_id'];
			$sql1 = ' SELECT cat_name FROM ' . $ecs->table('zc_category') . ' WHERE cat_id = \'' . $cat_id . '\' ';
			$result['cat_name'] = $db->getOne($sql1);
			$sql = ' SELECT id, name FROM ' . $ecs->table('zc_initiator');
			$initiator = $db->getAll($sql);
			$smarty->assign('initiator', $initiator);
			$smarty->assign('item_id', $id);
			$smarty->assign('parent_category', get_every_category($result['cat_id'], 'zc_category'));
			set_default_filter(0, $result['cat_id'], 0, 0, 'zc_category');
			$smarty->assign('table', 'zc_category');
			$smarty->assign('info', $result);
			$smarty->assign('state', 'update');
			$smarty->display('zc_project_info.dwt');
		}
		else {
			create_html_editor2('details', 'details');
			create_html_editor2('describe', 'describe');
			create_html_editor2('risk_instruction', 'risk_instruction');
			$sql = ' SELECT id, name FROM ' . $ecs->table('zc_initiator');
			$initiator = $db->getAll($sql);
			$smarty->assign('initiator', $initiator);
			$start_date = date('Y-m-d');
			$end_date = date('Y-m-d', strtotime('+1 month'));
			$smarty->assign('state', 'insert');
			$smarty->assign('start_date', $start_date);
			$smarty->assign('end_date', $end_date);
			set_default_filter(0, 0, 0, 0, 'zc_category');
			$smarty->assign('table', 'zc_category');
			$smarty->display('zc_project_info.dwt');
		}
	}
	else if ($_REQUEST['act'] == 'insert') {
		admin_priv('zc_project_manage');
		$title = (!empty($_POST['title']) ? trim($_POST['title']) : '');
		$cat_id = (!empty($_POST['cat_id']) ? trim($_POST['cat_id']) : 0);
		$amount = (!empty($_POST['money']) ? trim($_POST['money']) : 0);
		$start_time = (!empty($_POST['promote_start_date']) ? strtotime(trim($_POST['promote_start_date'])) : date('Y-m-d'));
		$end_time = (!empty($_POST['promote_end_date']) ? strtotime(trim($_POST['promote_end_date'])) : date('Y-m-d', strtotime('+1 month')));
		$details = (!empty($_POST['details']) ? trim($_POST['details']) : '无描述');
		$describe = (!empty($_POST['describe']) ? trim($_POST['describe']) : '无描述');
		$risk_instruction = (!empty($_POST['risk_instruction']) ? trim($_POST['risk_instruction']) : '无描述');
		$initiator = (!empty($_POST['initiator']) ? trim($_POST['initiator']) : '');
		$is_best = (!empty($_POST['is_best']) ? trim($_POST['is_best']) : 0);
		$title_img = '';
		$dir_title = 'zc_title_images';
		$title_img = $image->upload_image($_FILES['tit_img'], $dir_title);
		$sql = 'INSERT INTO' . $ecs->table('zc_project') . '(`id`,`cat_id`,`title`,`init_id`,`is_best`,`start_time`,`end_time`,`amount`,`title_img`,`details`,`describe`,`risk_instruction`) ' . ' VALUES (NULL,\'' . $cat_id . '\',\'' . $title . '\',\'' . $initiator . '\',\'' . $is_best . '\',\'' . $start_time . '\',\'' . $end_time . '\',\'' . $amount . '\',\'' . $title_img . '\',`details`,\'' . $describe . '\',\'' . $risk_instruction . '\')';
		$insert = $db->query($sql);

		if ($insert) {
			$links[0]['text'] = $_LANG['go_list'];
			$links[0]['href'] = 'zc_project.php?act=list';
			sys_msg($_LANG['add_succeed'], 0, $links);
		}
		else {
			$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
			$links[0]['href'] = 'javascript:history.go(-1)';
			sys_msg($_LANG['add_failure'], 1, $links);
		}
	}
	else if ($_REQUEST['act'] == 'update') {
		admin_priv('zc_project_manage');
		$id = $_POST['item_id'];
		$title = (!empty($_POST['title']) ? trim($_POST['title']) : '');
		$cat_id = (!empty($_POST['cat_id']) ? trim($_POST['cat_id']) : 0);
		$amount = (!empty($_POST['money']) ? trim($_POST['money']) : 0);
		$start_time = (!empty($_POST['promote_start_date']) ? strtotime(trim($_POST['promote_start_date'])) : date('Y-m-d'));
		$end_time = (!empty($_POST['promote_end_date']) ? strtotime(trim($_POST['promote_end_date'])) : date('Y-m-d', strtotime('+1 month')));
		$details = (!empty($_POST['details']) ? trim($_POST['details']) : '无描述');
		$describe = (!empty($_POST['describe']) ? trim($_POST['describe']) : '无描述');
		$risk_instruction = (!empty($_POST['risk_instruction']) ? trim($_POST['risk_instruction']) : '无描述');
		$initiator = (!empty($_POST['initiator']) ? trim($_POST['initiator']) : '');
		$is_best = (!empty($_POST['is_best']) ? trim($_POST['is_best']) : 0);
		$title_img = '';
		$img = '';
		$dir_title = 'zc_title_images';

		if (!empty($_FILES['tit_img']['name'])) {
			$title_img = $image->upload_image($_FILES['tit_img'], $dir_title);
		}

		$sql = ' UPDATE ' . $ecs->table('zc_project') . ' SET ' . ' `cat_id`=\'' . $cat_id . '\', ' . ' `title`=\'' . $title . '\', ' . ' `init_id`=\'' . $initiator . '\', ' . ' `is_best`=\'' . $is_best . '\', ' . ' `start_time`=\'' . $start_time . '\', ' . ' `end_time`=\'' . $end_time . '\', ' . ' `amount`=\'' . $amount . '\', ';

		if ($title_img) {
			$sql .= ' `title_img`=\'' . $title_img . '\', ';
		}

		$sql .= ' `details`=\'' . $details . '\', ';
		$sql .= ' `risk_instruction`=\'' . $risk_instruction . '\', ';
		$sql .= ' `describe`=\'' . $describe . '\' WHERE id=\'' . $id . '\' ';
		$update = $db->query($sql);

		if ($update) {
			$links[0]['text'] = $_LANG['go_list'];
			$links[0]['href'] = 'zc_project.php?act=list';
			sys_msg($_LANG['edit_success'], 0, $links);
		}
		else {
			$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
			$links[0]['href'] = 'javascript:history.go(-1)';
			sys_msg($_LANG['edit_fail'], 1, $links);
		}
	}
	else if ($_REQUEST['act'] == 'del') {
		admin_priv('zc_project_manage');
		$id = $_GET['id'];
		$sql = ' SELECT count(*) FROM ' . $ecs->table('zc_goods') . ' WHERE pid = \'' . $id . '\' ';
		$res = $db->getOne($sql);

		if ($res) {
			$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
			$links[0]['href'] = 'javascript:history.go(-1)';
			sys_msg($_LANG['zc_project_del_fail'], 1, $links);
		}
		else {
			$sql = 'SELECT img, title_img ' . ' FROM ' . $ecs->table('zc_project') . ' WHERE id = \'' . $id . '\'';
			$row = $db->getRow($sql);
			@unlink(ROOT_PATH . $row['title_img']);
			$row['img'] = unserialize($row['img']);

			foreach ($row['img'] as $v) {
				@unlink(ROOT_PATH . $v);
			}

			$sql = ' DELETE FROM ' . $ecs->table('zc_project') . ' WHERE id = \'' . $id . '\' ';
			$db->query($sql);
			Header('Location:zc_project.php?act=list');
		}
	}
	else if ($_REQUEST['act'] == 'toggle_best') {
		$id = intval($_POST['id']);
		$is_best = intval($_POST['val']);

		if ($exc->edit('is_best = \'' . $is_best . '\'', $id)) {
			clear_cache_files();
			make_json_result($is_best);
		}
	}
	else if ($_REQUEST['act'] == 'product_list') {
		admin_priv('zc_project_manage');
		$id = $_GET['id'];
		$smarty->assign('ur_here', $_LANG['zc_goods_manage']);
		$action_link = array('href' => 'zc_project.php?act=add_product&id=' . $id, 'text' => $_LANG['add_zc_goods']);
		$sql = ' SELECT * FROM ' . $ecs->table('zc_goods') . ' WHERE pid = \'' . $id . '\' ';
		$result = $db->getAll($sql);
		$smarty->assign('id', $id);
		$smarty->assign('product_list', $result);
		$smarty->assign('action_link', $action_link);
		$smarty->assign('full_page', 1);
		$smarty->display('zc_goods_list.dwt');
	}
	else {
		if (($_REQUEST['act'] == 'add_product') || ($_REQUEST['act'] == 'edit_product')) {
			admin_priv('zc_project_manage');

			if ($_REQUEST['act'] == 'add_product') {
				$smarty->assign('ur_here', $_LANG['add_zc_goods']);
			}

			if ($_REQUEST['act'] == 'edit_product') {
				$smarty->assign('ur_here', $_LANG['edit_zc_goods']);
			}

			$action_link = array('href' => 'zc_project.php?act=product_list&id=' . intval($_GET['id']), 'text' => $_LANG['zc_goods_manage']);
			$smarty->assign('action_link', $action_link);

			if ($_GET['product_id']) {
				$id = $_GET['id'];
				$product_id = $_GET['product_id'];
				$sql = ' SELECT * FROM ' . $ecs->table('zc_goods') . ' WHERE id = \'' . $product_id . '\' ';
				$row = $db->getRow($sql);
				$smarty->assign('id', $id);
				$smarty->assign('item_id', $product_id);
				$smarty->assign('product', $row);
				$smarty->assign('state', 'update_product');
				$smarty->display('zc_goods_info.dwt');
			}
			else {
				$id = $_GET['id'];
				$smarty->assign('item_id', $id);
				$smarty->assign('state', 'insert_product');
				$smarty->display('zc_goods_info.dwt');
			}
		}
		else if ($_REQUEST['act'] == 'insert_product') {
			admin_priv('zc_project_manage');
			$id = $_POST['item_id'];
			$limit = (!empty($_POST['limit']) ? trim(intval($_POST['limit'])) : 0);
			$price = (!empty($_POST['price']) ? trim($_POST['price']) : 0);
			$carriage = (!empty($_POST['yunfei']) ? trim($_POST['yunfei']) : 0);
			$return_time = (!empty($_POST['return_time']) ? trim($_POST['return_time']) : '');
			$return_cont = (!empty($_POST['content']) ? trim($_POST['content']) : '');
			$product_img = '';
			$dir_product = 'zc_product_images';
			$product_img = $image->upload_image($_FILES['product_img'], $dir_product);
			$sql = 'INSERT INTO' . $ecs->table('zc_goods') . '(`id`,`pid`,`limit`,`price`,`shipping_fee`,`content`,`img`,`return_time`) VALUES (\'\',\'' . $id . '\',\'' . $limit . '\',\'' . $price . '\',\'' . $carriage . '\',\'' . $return_cont . '\',\'' . $product_img . '\',\'' . $return_time . '\')';
			$insert = $db->query($sql);

			if ($insert) {
				$links[0]['text'] = $_LANG['go_list'];
				$links[0]['href'] = 'zc_project.php?act=product_list&id=' . $id;
				sys_msg($_LANG['add_succeed'], 0, $links);
			}
			else {
				$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
				$links[0]['href'] = 'javascript:history.go(-1)';
				sys_msg($_LANG['add_failure'], 1, $links);
			}
		}
		else if ($_REQUEST['act'] == 'update_product') {
			admin_priv('zc_project_manage');
			$id = $_POST['id'];
			$product_id = $_POST['item_id'];
			$limit = (!empty($_POST['limit']) ? trim($_POST['limit']) : 0);
			$price = (!empty($_POST['price']) ? trim($_POST['price']) : 0);
			$carriage = (!empty($_POST['shipping_fee']) ? trim($_POST['shipping_fee']) : 0);
			$return_time = (!empty($_POST['return_time']) ? trim($_POST['return_time']) : '');
			$return_cont = (!empty($_POST['content']) ? trim($_POST['content']) : '');

			if (!empty($_POST['infinite'])) {
				$limit = -1;
			}

			$product_img = '';
			$dir_product = 'zc_product_images';

			if (!empty($_FILES['product_img']['name'])) {
				$product_img = $image->upload_image($_FILES['product_img'], $dir_product);
			}

			$sql = 'SELECT img FROM ' . $ecs->table('zc_goods') . ' WHERE id = \'' . $product_id . '\'';
			$row = $db->getRow($sql);
			if (($product_img != '') && $row['img']) {
				@unlink(ROOT_PATH . $row['product_img']);
			}

			$sql = ' UPDATE ' . $ecs->table('zc_goods') . ' SET ' . ' `limit`=\'' . $limit . '\', ' . ' `price`=\'' . $price . '\', ' . ' `shipping_fee`=\'' . $carriage . '\', ' . ' `return_time`=\'' . $return_time . '\', ';

			if ($product_img) {
				$sql .= ' `img`=\'' . $product_img . '\', ';
			}

			$sql .= ' `content`=\'' . $return_cont . '\' WHERE id=\'' . $product_id . '\' ';
			$update = $db->query($sql);

			if ($update) {
				$links[0]['text'] = $_LANG['go_list'];
				$links[0]['href'] = 'zc_project.php?act=product_list&id=' . $id;
				sys_msg($_LANG['edit_success'], 0, $links);
			}
			else {
				$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
				$links[0]['href'] = 'javascript:history.go(-1)';
				sys_msg($_LANG['edit_fail'], 1, $links);
			}
		}
		else if ($_REQUEST['act'] == 'del_product') {
			admin_priv('zc_project_manage');
			$id = $_GET['id'];
			$product_id = $_GET['product_id'];
			$sql = ' SELECT count(*) FROM ' . $ecs->table('order_info') . ' WHERE zc_goods_id = \'' . $product_id . '\' ';
			$res = $db->getOne($sql);

			if ($res) {
				$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
				$links[0]['href'] = 'javascript:history.go(-1)';
				sys_msg($_LANG['zc_goods_del_fail'], 1, $links);
			}
			else {
				$sql = 'SELECT img FROM ' . $ecs->table('zc_goods') . ' WHERE id = \'' . $product_id . '\'';
				$row = $db->getRow($sql);
				@unlink(ROOT_PATH . $row['img']);
				$sql = 'DELETE FROM ' . $ecs->table('zc_goods') . ' WHERE id = \'' . $product_id . '\' ';
				$db->query($sql);
				Header('Location:zc_project.php?act=product_list&id=' . $id);
			}
		}
		else if ($_REQUEST['act'] == 'progress') {
			admin_priv('zc_project_manage');
			$item_id = $_GET['id'];
			$smarty->assign('ur_here', $_LANG['zc_progress_manage']);
			$action_link = array('href' => 'zc_project.php?act=add_evolve&id=' . $item_id, 'text' => $_LANG['add_zc_progress']);
			$sql = ' SELECT * FROM ' . $ecs->table('zc_progress') . ' WHERE pid = \'' . $item_id . '\' ';
			$result = $db->getAll($sql);

			foreach ($result as $k => $v) {
				$result[$k]['add_time'] = date('Y-m-d', $v['add_time']);
				$result[$k]['img'] = unserialize($v['img']);
			}

			$smarty->assign('item_id', $item_id);
			$smarty->assign('evolve_list', $result);
			$smarty->assign('action_link', $action_link);
			$smarty->assign('full_page', 1);
			$smarty->display('zc_progress_list.dwt');
		}
		else {
			if (($_REQUEST['act'] == 'add_evolve') || ($_REQUEST['act'] == 'edit_evolve')) {
				admin_priv('zc_project_manage');

				if ($_REQUEST['act'] == 'add_evolve') {
					$smarty->assign('ur_here', $_LANG['add_zc_progress']);
				}

				if ($_REQUEST['act'] == 'edit_evolve') {
					$smarty->assign('ur_here', $_LANG['edit_zc_progress']);
				}

				$action_link = array('href' => 'zc_project.php?act=progress&id=' . intval($_GET['id']), 'text' => $_LANG['zc_progress_manage']);
				$smarty->assign('action_link', $action_link);

				if ($_GET['evolve_id']) {
					$id = $_GET['id'];
					$evolve_id = $_GET['evolve_id'];
					$sql = ' SELECT * FROM ' . $ecs->table('zc_progress') . ' WHERE id = \'' . $evolve_id . '\' ';
					$row = $db->getRow($sql);
					$row['img'] = unserialize($row['img']);
					$smarty->assign('id', $evolve_id);
					$smarty->assign('item_id', $id);
					$smarty->assign('evolve', $row);
					$smarty->assign('state', 'update_evolve');
					$smarty->display('zc_progress_info.dwt');
				}
				else {
					$id = $_GET['id'];
					$smarty->assign('item_id', $id);
					$smarty->assign('state', 'insert_evolve');
					$smarty->display('zc_progress_info.dwt');
				}
			}
			else if ($_REQUEST['act'] == 'insert_evolve') {
				admin_priv('zc_project_manage');
				$item_id = $_POST['item_id'];
				$progress = (!empty($_POST['progress']) ? trim($_POST['progress']) : '');
				$add_time = gmtime();
				$evolve_img = '';
				$dir_evolve = 'funding_evolve_images';

				if (have_file_upload()) {
					for ($i = 0; $i < count($_FILES); $i++) {
						if ($_FILES['img_' . $i]) {
							$evolve_img[] = $image->upload_image($_FILES['img_' . $i], $dir_evolve);
						}
					}
				}

				$evolve_img = serialize($evolve_img);
				$sql = 'INSERT INTO' . $ecs->table('zc_progress') . '(`id`,`pid`,`progress`,`add_time`,`img`) ' . ' VALUES (\'\',\'' . $item_id . '\',\'' . $progress . '\',\'' . $add_time . '\',\'' . $evolve_img . '\') ';
				$insert = $db->query($sql);

				if ($insert) {
					$links[0]['text'] = $_LANG['go_list'];
					$links[0]['href'] = 'zc_project.php?act=progress&id=' . $item_id;
					sys_msg($_LANG['add_succeed'], 0, $links);
				}
				else {
					$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
					$links[0]['href'] = 'javascript:history.go(-1)';
					sys_msg($_LANG['add_failure'], 1, $links);
				}
			}
			else if ($_REQUEST['act'] == 'update_evolve') {
				admin_priv('zc_project_manage');
				$id = $_POST['id'];
				$item_id = $_POST['item_id'];
				$progress = (!empty($_POST['progress']) ? trim($_POST['progress']) : '');
				$evolve_img = '';
				$dir_evolve = 'funding_evolve_images';

				for ($i = 0; $i < count($_FILES); $i++) {
					if (!empty($_FILES['img_' . $i]['name'])) {
						$evolve_img[] = $image->upload_image($_FILES['img_' . $i], $dir_evolve);
					}
				}

				if ($evolve_img) {
					$sql = 'SELECT img FROM ' . $ecs->table('zc_progress') . ' WHERE id = \'' . $id . '\'';
					$row = $db->getRow($sql);

					if ($row['img']) {
						$row['img'] = unserialize($row['img']);
						$evolve_img = array_merge($evolve_img, $row['img']);
					}

					$evolve_img = serialize($evolve_img);
				}

				$sql = ' UPDATE ' . $ecs->table('zc_progress') . ' SET ' . ' `progress`=\'' . $progress . '\' ';

				if ($evolve_img) {
					$sql .= ' , `img`=\'' . $evolve_img . '\' ';
				}

				$sql .= ' WHERE id=\'' . $id . '\' ';
				$update = $db->query($sql);

				if ($update) {
					$links[0]['text'] = $_LANG['go_list'];
					$links[0]['href'] = 'zc_project.php?act=progress&id=' . $item_id;
					sys_msg($_LANG['edit_success'], 0, $links);
				}
				else {
					$links[0]['text'] = $GLOBALS['_LANG']['go_back'];
					$links[0]['href'] = 'javascript:history.go(-1)';
					sys_msg($_LANG['edit_fail'], 1, $links);
				}
			}
			else if ($_REQUEST['act'] == 'del_evolve') {
				admin_priv('zc_project_manage');
				$id = $_GET['id'];
				$evolve_id = $_GET['evolve_id'];
				$sql = 'SELECT img FROM ' . $ecs->table('zc_progress') . ' WHERE id = \'' . $evolve_id . '\'';
				$row = $db->getRow($sql);
				$row['img'] = unserialize($row['img']);

				foreach ($row['img'] as $v) {
					@unlink(ROOT_PATH . $v);
				}

				$sql = ' DELETE FROM ' . $ecs->table('zc_progress') . ' WHERE id = \'' . $evolve_id . '\' ';
				$db->query($sql);
				Header('Location:zc_project.php?act=progress&id=' . $id);
			}
			else if ($_REQUEST['act'] == 'delete_image') {
				admin_priv('zc_project_manage');
				$result = array('error' => 0, 'message' => '', 'content' => '');
				$type = (empty($_REQUEST['type']) ? '' : trim($_REQUEST['type']));
				$id = (empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']));
				$key = (empty($_REQUEST['key']) ? 0 : intval($_REQUEST['key']));

				if ($type == 'project') {
					$sql = ' SELECT img FROM ' . $GLOBALS['ecs']->table('zc_project') . ' WHERE id = \'' . $id . '\' ';
					$img = $GLOBALS['db']->getOne($sql);

					if ($img) {
						$img_arr = unserialize($img);
						@unlink(ROOT_PATH . $img_arr[$key]);
						unset($img_arr[$key]);
						$img = serialize($img_arr);
						$sql = ' UPDATE ' . $GLOBALS['ecs']->table('zc_project') . ' SET img = \'' . $img . '\' WHERE id = \'' . $id . '\' ';
						$GLOBALS['db']->query($sql);
					}
				}

				if ($type == 'progress') {
					$sql = ' SELECT img FROM ' . $GLOBALS['ecs']->table('zc_progress') . ' WHERE id = \'' . $id . '\' ';
					$img = $GLOBALS['db']->getOne($sql);

					if ($img) {
						$img_arr = unserialize($img);
						@unlink(ROOT_PATH . $img_arr[$key]);
						unset($img_arr[$key]);
						$img = serialize($img_arr);
						$sql = ' UPDATE ' . $GLOBALS['ecs']->table('zc_progress') . ' SET img = \'' . $img . '\' WHERE id = \'' . $id . '\' ';
						$GLOBALS['db']->query($sql);
					}
				}

				exit(json_encode($result));
			}
		}
	}
}

?>
