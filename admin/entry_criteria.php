<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function entry_criteria_list($id = 0, $selected = 0, $re_type = true, $level = 0)
{
	static $res;

	if ($res === NULL) {
		$data = read_static_cache('entry_criteria_releate');

		if ($data === false) {
			$sql = 'SELECT c.*, COUNT(s.id) AS has_children ' . ' FROM ' . $GLOBALS['ecs']->table('entry_criteria') . ' AS c' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('entry_criteria') . ' AS s ON s.parent_id=c.id' . ' GROUP BY c.id ' . ' ORDER BY parent_id ASC';
			$res = $GLOBALS['db']->getAll($sql);
			write_static_cache('entry_criteria_releate', $res);
		}
		else {
			$res = $data;
		}
	}

	if (empty($res) == true) {
		return $re_type ? '' : array();
	}

	$options = entry_criteria_options($id, $res);

	if (0 < $level) {
		if ($id == 0) {
			$end_level = $level;
		}
		else {
			$first_item = reset($options);
			$end_level = $first_item['level'] + $level;
		}

		foreach ($options as $key => $val) {
			if ($end_level <= $val['level']) {
				unset($options[$key]);
			}
		}
	}

	return $options;
}

function entry_criteria_options($spec_cat_id, $arr)
{
	static $cat_options = array();

	if (isset($cat_options[$spec_cat_id])) {
		return $cat_options[$spec_cat_id];
	}

	if (!isset($cat_options[0])) {
		$level = $last_cat_id = 0;
		$options = $cat_id_array = $level_array = array();

		while (!empty($arr)) {
			foreach ($arr as $key => $value) {
				$cat_id = $value['id'];
				if ($level == 0 && $last_cat_id == 0) {
					if (0 < $value['parent_id']) {
						break;
					}

					$options[$cat_id] = $value;
					$options[$cat_id]['level'] = $level;
					$options[$cat_id]['id'] = $cat_id;
					$options[$cat_id]['name'] = $value['criteria_name'];
					unset($arr[$key]);

					if ($value['has_children'] == 0) {
						continue;
					}

					$last_cat_id = $cat_id;
					$cat_id_array = array($cat_id);
					$level_array[$last_cat_id] = ++$level;
					continue;
				}

				if ($value['parent_id'] == $last_cat_id) {
					$options[$cat_id] = $value;
					$options[$cat_id]['level'] = $level;
					$options[$cat_id]['id'] = $cat_id;
					$options[$cat_id]['name'] = $value['criteria_name'];
					unset($arr[$key]);

					if (0 < $value['has_children']) {
						if (end($cat_id_array) != $last_cat_id) {
							$cat_id_array[] = $last_cat_id;
						}

						$last_cat_id = $cat_id;
						$cat_id_array[] = $cat_id;
						$level_array[$last_cat_id] = ++$level;
					}
				}
				else if ($last_cat_id < $value['parent_id']) {
					break;
				}
			}

			$count = count($cat_id_array);

			if (1 < $count) {
				$last_cat_id = array_pop($cat_id_array);
			}
			else if ($count == 1) {
				if ($last_cat_id != end($cat_id_array)) {
					$last_cat_id = end($cat_id_array);
				}
				else {
					$level = 0;
					$last_cat_id = 0;
					$cat_id_array = array();
					continue;
				}
			}

			if ($last_cat_id && isset($level_array[$last_cat_id])) {
				$level = $level_array[$last_cat_id];
			}
			else {
				$level = 0;
			}
		}

		$cat_options[0] = $options;
	}
	else {
		$options = $cat_options[0];
	}

	if (!$spec_cat_id) {
		return $options;
	}
	else {
		if (empty($options[$spec_cat_id])) {
			return array();
		}

		$spec_cat_id_level = $options[$spec_cat_id]['level'];

		foreach ($options as $key => $value) {
			if ($key != $spec_cat_id) {
				unset($options[$key]);
			}
			else {
				break;
			}
		}

		$spec_cat_id_array = array();

		foreach ($options as $key => $value) {
			if ($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id || $value['level'] < $spec_cat_id_level) {
				break;
			}
			else {
				$spec_cat_id_array[$key] = $value;
			}
		}

		$cat_options[$spec_cat_id] = $spec_cat_id_array;
		return $spec_cat_id_array;
	}
}

function get_criteria_cat_level($parent_id = 0)
{
	$res = array();
	$sql = 'SELECT c.*, COUNT(s.id) AS has_children ' . ' FROM ' . $GLOBALS['ecs']->table('entry_criteria') . ' AS c' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('entry_criteria') . ' AS s ON s.parent_id=c.id' . (' WHERE c.parent_id = \'' . $parent_id . '\' ') . ' GROUP BY c.id ' . ' ORDER BY c.id ASC ';
	$res = $GLOBALS['db']->getAll($sql);

	if ($res) {
		foreach ($res as $k => $row) {
			$res[$k]['level'] = $level;
		}
	}

	return $res;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'includes/cls_image.php';
$exc = new exchange($ecs->table('entry_criteria'), $db, 'id', 'criteria_name');

if ($_REQUEST['act'] == 'list') {
	admin_priv('seller_grade');
	$smarty->assign('ur_here', $_LANG['entry_criteria_list']);
	$smarty->assign('action_link', array('text' => $_LANG['seller_garde_list'], 'href' => 'seller_grade.php?act=list'));
	$smarty->assign('action_link2', array('text' => $_LANG['add_entry_criteria'], 'href' => 'entry_criteria.php?act=add'));
	$parent_id = isset($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : 0;
	$articlecat = get_criteria_cat_level($parent_id);

	foreach ($articlecat as $k => $v) {
		if (0 < $v['parent_id']) {
			$articlecat[$k]['parent_name'] = $db->getOne(' SELECT criteria_name FROM ' . $ecs->table('entry_criteria') . ' WHERE id = \'' . $v['parent_id'] . '\'');
		}

		switch ($v['type']) {
		case 'text':
			$articlecat[$k]['type'] = $_LANG['text'];
			break;

		case 'select':
			$articlecat[$k]['type'] = $_LANG['select'];
			break;

		case 'textarea':
			$articlecat[$k]['type'] = $_LANG['textarea'];
			break;

		case 'file':
			$articlecat[$k]['type'] = $_LANG['file'];
			break;

		case 'charge':
			$articlecat[$k]['type'] = $_LANG['charge'];
			break;
		}
	}

	$smarty->assign('entry_criteria', $articlecat);
	$smarty->assign('parent_id', $parent_id);
	$smarty->display('entry_criteria.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	admin_priv('seller_grade');
	$smarty->assign('ur_here', $_LANG['entry_criteria_list']);
	$smarty->assign('action_link', array('text' => $_LANG['seller_garde_list'], 'href' => 'seller_grade.php?act=list'));
	$smarty->assign('action_link2', array('text' => $_LANG['add_entry_criteria'], 'href' => 'entry_criteria.php?act=add'));
	$parent_id = isset($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : 0;
	$articlecat = get_criteria_cat_level($parent_id);

	foreach ($articlecat as $k => $v) {
		if (0 < $v['parent_id']) {
			$articlecat[$k]['parent_name'] = $db->getOne(' SELECT criteria_name FROM ' . $ecs->table('entry_criteria') . ' WHERE id = \'' . $v['parent_id'] . '\'');
		}

		switch ($v['type']) {
		case 'text':
			$articlecat[$k]['type'] = $_LANG['text'];
			break;

		case 'select':
			$articlecat[$k]['type'] = $_LANG['select'];
			break;

		case 'textarea':
			$articlecat[$k]['type'] = $_LANG['textarea'];
			break;

		case 'file':
			$articlecat[$k]['type'] = $_LANG['file'];
			break;

		case 'charge':
			$articlecat[$k]['type'] = $_LANG['charge'];
			break;
		}
	}

	$smarty->assign('entry_criteria', $articlecat);
	$smarty->assign('parent_id', $parent_id);
	make_json_result($smarty->fetch('entry_criteria.dwt'));
}
else {
	if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
		admin_priv('seller_grade');
		$id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$parent_id = isset($_REQUEST['parent_id']) && !empty($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : 0;
		$smarty->assign('ur_here', $_LANG['add_entry_criteria']);
		$smarty->assign('action_link', array('text' => $_LANG['entry_criteria_list'], 'href' => 'entry_criteria.php?act=list'));
		$act = $_REQUEST['act'] == 'add' ? 'insert' : 'update';
		$smarty->assign('act', $act);
		$entry_criteria = $db->getAll(' SELECT id,criteria_name FROM ' . $ecs->table('entry_criteria') . ' WHERE  parent_id = 0');
		$smarty->assign('criteria', $entry_criteria);
		if ($id || $parent_id) {
			$where = '';

			if ($parent_id) {
				$where = ' WHERE id = \'' . $parent_id . '\'';
			}
			else {
				$where = ' WHERE id = \'' . $id . '\'';
			}

			$entry_criteria = $db->getRow('SELECT * FROM ' . $ecs->table('entry_criteria') . $where);
			$entry_criteria['option_value'] = explode(',', $entry_criteria['option_value']);
			$smarty->assign('entry_criteria', $entry_criteria);
		}

		$smarty->display('entry_criteria_info.dwt');
	}
	else {
		if ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
			admin_priv('seller_grade');
			$smarty->assign('ur_here', $_LANG['edit_entry_criteria']);
			$smarty->assign('action_link', array('text' => $_LANG['entry_criteria_list'], 'href' => 'entry_criteria.php?act=list'));
			$entry_criteria = $db->getAll(' SELECT id,criteria_name FROM ' . $ecs->table('entry_criteria') . ' WHERE  parent_id = 0');
			$smarty->assign('criteria', $entry_criteria);
			$criteria_name = !empty($_REQUEST['criteria_name']) ? $_REQUEST['criteria_name'] : '';
			$parent_id = !empty($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : 0;
			$charge = !empty($_REQUEST['charge']) ? round($_REQUEST['charge'], 2) : 0;
			$type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '';
			$is_mandatory = !empty($_REQUEST['is_mandatory']) ? $_REQUEST['is_mandatory'] : 0;
			$is_cumulative = !empty($_REQUEST['is_cumulative']) ? $_REQUEST['is_cumulative'] : 0;
			$data_type = !empty($_REQUEST['data_type']) ? intval($_REQUEST['data_type']) : 0;
			$option_value = !empty($_REQUEST['option_value']) ? implode(',', array_unique($_REQUEST['option_value'])) : '';

			if ($_REQUEST['act'] == 'update') {
				$is_only = $exc->is_only('criteria_name', $criteria_name, 0, 'id != ' . $_POST['id']);

				if (!$is_only) {
					sys_msg(sprintf($_LANG['criteria_name_repeat'], stripslashes($criteria_name)), 1);
				}

				$sql = ' UPDATE ' . $ecs->table('entry_criteria') . (' SET criteria_name = \'' . $criteria_name . '\' , parent_id = \'' . $parent_id . '\' ,charge=\'' . $charge . '\',type=\'' . $type . '\',is_mandatory=\'' . $is_mandatory . '\' ,option_value=\'' . $option_value . '\',is_cumulative=\'' . $is_cumulative . '\' ,data_type = \'' . $data_type . '\' WHERE id = \'') . $_REQUEST['id'] . '\'';

				if ($db->query($sql) == true) {
					$link[0]['text'] = $_LANG['bank_list'];
					$link[0]['href'] = 'entry_criteria.php?act=list';
					$lang = $_LANG['edit_succeed'];
				}
			}
			else if ($_REQUEST['act'] == 'insert') {
				$is_only = $exc->is_only('criteria_name', $criteria_name, 0);

				if (!$is_only) {
					sys_msg(sprintf($_LANG['criteria_name_repeat'], stripslashes($criteria_name)), 1);
				}

				$sql = ' INSERT INTO ' . $ecs->table('entry_criteria') . (' (`criteria_name`,`parent_id`,`charge`,`type`,`is_mandatory`,`option_value`,`is_cumulative`,`data_type`) values  ( \'' . $criteria_name . '\',\'' . $parent_id . '\',\'' . $charge . '\',\'' . $type . '\',\'' . $is_mandatory . '\',\'' . $option_value . '\',\'' . $is_cumulative . '\',\'' . $data_type . '\')');

				if ($db->query($sql) == true) {
					$link[0]['text'] = $_LANG['GO_add'];
					$link[0]['href'] = 'entry_criteria.php?act=add';
					$link[1]['text'] = $_LANG['bank_list'];
					$link[1]['href'] = 'entry_criteria.php?act=list';
					$lang = $_LANG['add_succeed'];
				}
			}

			clear_cache_files();
			sys_msg($lang, 0, $link);
		}
		else if ($_REQUEST['act'] == 'remove') {
			$id = intval($_GET['id']);
			$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('entry_criteria') . (' WHERE parent_id = \'' . $id . '\'');

			if (0 < $db->getOne($sql)) {
				make_json_error($_LANG['is_fullentry']);
			}

			$exc->drop($id);
			clear_cache_files();
			$url = 'entry_criteria.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
			ecs_header('Location: ' . $url . "\n");
			exit();
		}
		else if ($_REQUEST['act'] == 'edit_charge') {
			check_authz_json('seller_grade');
			$id = intval($_POST['id']);
			$order = json_str_iconv(trim($_POST['val']));

			if ($exc->edit('charge = \'' . $order . '\'', $id)) {
				clear_cache_files();
				make_json_result(stripslashes($order));
			}
			else {
				make_json_error($db->error());
			}
		}
		else if ($_REQUEST['act'] == 'toggle_show') {
			check_authz_json('seller_grade');
			$id = intval($_POST['id']);
			$order = json_str_iconv(trim($_POST['val']));

			if ($exc->edit('is_mandatory = \'' . $order . '\'', $id)) {
				clear_cache_files();
				make_json_result(stripslashes($order));
			}
			else {
				make_json_error($db->error());
			}
		}
	}
}

?>
