<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_goods_attr_list($goods_id)
{
	$where = '';

	if (empty($goods_id)) {
		$where = ' AND ga.admin_id = \'' . $_SESSION['seller_id'] . '\'';
	}

	$sql = 'SELECT ga.goods_attr_id, ga.goods_id, ga.attr_id, ga.attr_value, ga.attr_sort, ga.admin_id, war.attr_price AS attr_price FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS ga ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('warehouse_area_attr') . ' AS war ON ga.goods_attr_id = war.goods_attr_id AND ga.goods_id = war.goods_id' . ' WHERE ga.goods_id = \'' . $goods_id . '\'' . $where . ' ORDER BY ga.attr_sort, ga.goods_attr_id';
	$goods_attr_list = $GLOBALS['db']->getAll($sql);
	return $goods_attr_list;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require 'includes/lib_goods.php';
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('action_type', 'goods_warehouse_batch');
$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => 'area_attr_batch'));

if ($_REQUEST['act'] == 'add') {
	$smarty->assign('primary_cat', $_LANG['18_batch_manage']);
	admin_priv('goods_manage');
	$goods_id = (isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0);
	$attr_name = (isset($_REQUEST['attr_name']) ? $_REQUEST['attr_name'] : '');

	if (0 < $goods_id) {
		$smarty->assign('action_link', array('text' => $_LANG['goto_goods'], 'href' => 'goods.php?act=edit&goods_id=' . $goods_id . '&extension_code='));
	}

	$dir = opendir('../languages');
	$lang_list = array('UTF8' => $_LANG['charset']['utf8'], 'GB2312' => $_LANG['charset']['zh_cn'], 'BIG5' => $_LANG['charset']['zh_tw']);
	$download_list = array();

	while (@$file = readdir($dir)) {
		if (($file != '.') && ($file != '..') && ($file != '.svn') && ($file != '_svn') && (is_dir('../languages/' . $file) == true)) {
			$download_list[$file] = sprintf($_LANG['download_file'], isset($_LANG['charset'][$file]) ? $_LANG['charset'][$file] : $file);
		}
	}

	@closedir($dir);
	$smarty->assign('lang_list', $lang_list);
	$smarty->assign('download_list', $download_list);
	$smarty->assign('goods_id', $goods_id);
	$smarty->assign('attr_name', $attr_name);
	$goods_date = array('goods_name');
	$where = 'goods_id = \'' . $goods_id . '\'';
	$goods_name = get_table_date('goods', $where, $goods_date, 2);
	$smarty->assign('goods_name', $goods_name);
	$ur_here = $_LANG['13_batch_add'];
	$smarty->assign('ur_here', $ur_here);
	assign_query_info();
	$smarty->assign('current', 'attr_batch');
	$smarty->display('goods_area_attr_batch.dwt');
}
else if ($_REQUEST['act'] == 'upload') {
	admin_priv('goods_manage');
	$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => 'area_attr_batch'));

	if ($_FILES['file']['name']) {
		$line_number = 0;
		$arr = array();
		$goods_list = array();
		$field_list = array_keys($_LANG['upload_area_attr']);
		$_POST['charset'] = 'GB2312';
		$data = file($_FILES['file']['tmp_name']);

		if (0 < count($data)) {
			foreach ($data as $line) {
				if ($line_number == 0) {
					$line_number++;
					continue;
				}

				if (($_POST['charset'] != 'UTF8') && (strpos(strtolower(EC_CHARSET), 'utf') === 0)) {
					$line = ecs_iconv($_POST['charset'], 'UTF8', $line);
				}

				$arr = array();
				$buff = '';
				$quote = 0;
				$len = strlen($line);

				for ($i = 0; $i < $len; $i++) {
					$char = $line[$i];

					if ('\\' == $char) {
						$i++;
						$char = $line[$i];

						switch ($char) {
						case '"':
							$buff .= '"';
							break;

						case '\'':
							$buff .= '\'';
							break;

						case ',':
							$buff .= ',';
							break;

						default:
							$buff .= '\\' . $char;
							break;
						}
					}
					else if ('"' == $char) {
						if (0 == $quote) {
							$quote++;
						}
						else {
							$quote = 0;
						}
					}
					else if (',' == $char) {
						if (0 == $quote) {
							if (!isset($field_list[count($arr)])) {
								continue;
							}

							$field_name = $field_list[count($arr)];
							$arr[$field_name] = trim($buff);
							$buff = '';
							$quote = 0;
						}
						else {
							$buff .= $char;
						}
					}
					else {
						$buff .= $char;
					}

					if ($i == ($len - 1)) {
						if (!isset($field_list[count($arr)])) {
							continue;
						}

						$field_name = $field_list[count($arr)];
						$arr[$field_name] = trim($buff);
					}
				}

				$goods_list[] = $arr;
			}

			$goods_list = get_goods_bacth_area_attr_list($goods_list);
		}
	}

	$_SESSION['goods_list'] = $goods_list;
	$smarty->assign('full_page', 2);
	$smarty->assign('page', 1);
	$smarty->assign('attr_names', $attr_names);
	assign_query_info();
	$smarty->assign('ur_here', $_LANG['13_batch_add']);
	$smarty->display('goods_area_attr_batch_add.dwt');
}
else if ($_REQUEST['act'] == 'ajax_insert') {
	admin_priv('goods_manage');
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array(
		'list'    => array(),
		'is_stop' => 0
		);
	$page = (!empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1);
	$page_size = (isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 1);
	@set_time_limit(300);
	if (isset($_SESSION['goods_list']) && $_SESSION['goods_list']) {
		$goods_list = $_SESSION['goods_list'];
		$goods_list = $ecs->page_array($page_size, $page, $goods_list);
		$result['list'] = $goods_list['list'][0];
		$result['page'] = $goods_list['filter']['page'] + 1;
		$result['page_size'] = $goods_list['filter']['page_size'];
		$result['record_count'] = $goods_list['filter']['record_count'];
		$result['page_count'] = $goods_list['filter']['page_count'];
		$result['is_stop'] = 1;

		if ($goods_list['filter']['page_count'] < $page) {
			$result['is_stop'] = 0;
		}

		$other['goods_id'] = $result['list']['goods_id'];
		$other['area_id'] = $result['list']['area_id'];
		$other['goods_attr_id'] = $result['list']['goods_attr_id'];
		$other['attr_price'] = $result['list']['attr_price'];
		$sql = 'SELECT id FROM ' . $GLOBALS['ecs']->table('warehouse_area_attr') . ' WHERE goods_id = \'' . $result['list']['goods_id'] . '\' AND area_id = \'' . $result['list']['area_id'] . '\'' . ' AND goods_attr_id = \'' . $result['list']['goods_attr_id'] . '\'';

		if ($GLOBALS['db']->getOne($sql, true)) {
			$where = '';

			if (empty($result['list']['goods_id'])) {
				$where = ' AND admin_id = \'' . $_SESSION['seller_id'] . '\'';
			}

			$db->autoExecute($ecs->table('warehouse_area_attr'), $other, 'UPDATE', 'goods_id = \'' . $result['list']['goods_id'] . '\' AND area_id = \'' . $result['list']['area_id'] . '\' AND goods_attr_id = \'' . $result['list']['goods_attr_id'] . '\' ' . $where);
			$result['status_lang'] = '<span style="color: red;">已更新数据成功</span>';
		}
		else {
			$other['admin_id'] = $_SESSION['seller_id'];
			$db->autoExecute($ecs->table('warehouse_area_attr'), $other, 'INSERT');
			$result['status_lang'] = '<span style="color: red;">已添加数据成功</span>';
		}
	}

	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'download') {
	admin_priv('goods_manage');
	$goods_id = (isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0);
	$attr_name = (isset($_REQUEST['attr_name']) ? $_REQUEST['attr_name'] : '');
	$goods_attr_list = get_goods_attr_list($goods_id);
	header('Content-type: application/vnd.ms-excel; charset=utf-8');
	Header('Content-Disposition: attachment; filename=area_attr_info_list' . $goods_id . '.csv');

	if ($_GET['charset'] != $_CFG['lang']) {
		$lang_file = '../languages/' . $_GET['charset'] . '/' . ADMIN_PATH . '/goods_area_attr_batch.php';

		if (file_exists($lang_file)) {
			unset($_LANG['upload_area_attr']);
			require $lang_file;
		}
	}

	if (isset($_LANG['upload_area_attr'])) {
		if (($_GET['charset'] == 'zh_cn') || ($_GET['charset'] == 'zh_tw')) {
			$to_charset = ($_GET['charset'] == 'zh_cn' ? 'GB2312' : 'BIG5');
			$data = join(',', $_LANG['upload_area_attr']) . "\t\n";
			$area_date = array('region_name');
			$where = 'region_type = 1';
			$area_info = get_table_date('region_warehouse', $where, $area_date, 1);

			if ($goods_id) {
				$goods_info = get_admin_goods_info($goods_id, array('goods_name', 'goods_sn', 'user_id'));
			}
			else {
				$adminru = get_admin_ru_id();
				$goods_info['user_id'] = $adminru['ru_id'];
				$goods_info['shop_name'] = get_shop_name($adminru['ru_id'], 1);
			}

			if (0 < count($area_info)) {
				for ($i = 0; $i < count($area_info); $i++) {
					$data .= '' . ',';
					$data .= '' . ',';
					$data .= '' . ',';
					$data .= '' . ',';
					$data .= '' . ',';
					$data .= '' . ',';
					$data .= '' . "\t\n";

					if ($goods_attr_list) {
						for ($j = 0; $j < count($goods_attr_list); $j++) {
							$data .= $goods_id . ',';
							$data .= $goods_info['goods_name'] . ',';
							$data .= $goods_info['shop_name'] . ',';
							$data .= $goods_info['user_id'] . ',';
							$data .= $area_info[$i]['region_name'] . ',';
							$attr_price = (!empty($goods_attr_list[$j]['attr_price']) ? $goods_attr_list[$j]['attr_price'] : 0);
							$data .= $goods_attr_list[$j]['attr_value'] . ',';
							$data .= $attr_price . "\t\n";
						}
					}
				}
			}

			echo ecs_iconv(EC_CHARSET, $to_charset, $data);
		}
		else {
			echo join(',', $_LANG['upload_area_attr']);
		}
	}
	else {
		echo 'error: $_LANG[upload_area_attr] not exists';
	}
}

?>
