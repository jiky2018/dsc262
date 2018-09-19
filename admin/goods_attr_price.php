<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_list_download($goods_name = '', $attr_name = '', $area_info = array())
{
	if (0 < count($area_info)) {
		$arr = array();

		for ($i = 0; $i < count($area_info); $i++) {
			$arr[$i]['goods_name'] = $goods_name;
			$arr[$i]['area_name'] = $area_info[$i]['region_name'];
			$arr[$i]['attr_name'] = $attr_name;
			$arr[$i]['area_price'] = '';
		}

		return $arr;
	}
	else {
		return array();
	}
}

function get_goodsLattr_list($goods_id, $goods_type)
{
	$sql = 'SELECT ga.*, g.goods_sn FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS ga, ' . $GLOBALS['ecs']->table('attribute') . ' AS a, ' . $GLOBALS['ecs']->table('goods') . ' AS g' . ' WHERE ga.attr_id = a.attr_id AND attr_type <> 0 AND g.goods_id = ga.goods_id AND g.goods_id = \'' . $goods_id . '\' ORDER BY a.sort_order';
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res as $key => $value) {
		$goods_type_name = $GLOBALS['db']->getOne('SELECT cat_name FROM ' . $GLOBALS['ecs']->table('goods_type') . ' WHERE cat_id = \'' . $goods_type . '\' ');
		$attr_name = $GLOBALS['db']->getOne('SELECT attr_name FROM ' . $GLOBALS['ecs']->table('attribute') . ' WHERE attr_id = \'' . $value['attr_id'] . '\' ');
		$res[$key]['attr_all_value'] = $goods_type_name . '-' . $attr_name . '-' . $value['attr_value'];
	}

	return $res;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require 'includes/lib_goods.php';

if ($_REQUEST['act'] == 'add') {
	admin_priv('goods_manage');
	$goods_id = (isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0);
	$goods_type = (isset($_REQUEST['goods_type']) ? $_REQUEST['goods_type'] : '');

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
	$smarty->assign('goods_type', $goods_type);
	$goods_date = array('goods_name');
	$where = 'goods_id = \'' . $goods_id . '\'';
	$goods_name = get_table_date('goods', $where, $goods_date, 2);
	$smarty->assign('goods_name', $goods_name);
	$ur_here = $_LANG['13_batch_add'];
	$smarty->assign('ur_here', $ur_here);
	assign_query_info();
	$smarty->display('goods_attr_price_batch.dwt');
}
else if ($_REQUEST['act'] == 'upload') {
	admin_priv('goods_manage');

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

			for ($i = 0; $i < count($goods_list); $i++) {
				$goods_id = $GLOBALS['db']->getOne('SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_sn = \'' . $goods_list[$i]['goods_sn'] . '\' ');
				$goods_attr = explode('-', $goods_list[$i]['goods_attr']);
				$attr_value = $goods_attr[2];
				$attr_id = $GLOBALS['db']->getOne('SELECT a.attr_id FROM ' . $GLOBALS['ecs']->table('attribute') . ' AS a, ' . $GLOBALS['ecs']->table('goods_type') . ' AS gt ' . ' WHERE a.cat_id = gt.cat_id AND gt.cat_name = \'' . $goods_attr[0] . '\' AND a.attr_name = \'' . $goods_attr[1] . '\'');
				$goods_attr_id = $GLOBALS['db']->getOne('SELECT goods_attr_id FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE goods_id = \'' . $goods_id . '\' AND attr_id = \'' . $attr_id . '\' AND attr_value = \'' . $attr_value . '\' ');

				if (0 < $goods_attr_id) {
					$attr_res['attr_sort'] = $goods_list[$i]['attr_sort'];
					$attr_res['attr_price'] = $goods_list[$i]['attr_price'];
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('goods_attr'), $attr_res, 'UPDATE', ' goods_attr_id = \'' . $goods_attr_id . '\' ');
				}
				else {
					$goods_type_id = $GLOBALS['db']->getONe('SELECT cat_id FROM ' . $GLOBALS['ecs']->table('goods_type') . ' WHERE cat_name = \'' . $goods_attr[0] . '\' ');
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('goods'), array('goods_type' => $goods_type_id), 'UPDATE', ' goods_id = \'' . $goods_id . '\' ');
					$attr_res['goods_id'] = $goods_id;
					$attr_res['attr_id'] = $attr_id;
					$attr_res['attr_value'] = $attr_value;
					$attr_res['attr_sort'] = $goods_list[$i]['attr_sort'];
					$attr_res['attr_price'] = $goods_list[$i]['attr_price'];
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('goods_attr'), $attr_res, 'INSERT');
				}
			}

			$link[] = array('href' => 'goods.php?act=edit&goods_id=' . $goods_id . '&extension_code=', 'text' => $_LANG['03_goods_edit']);
			$link[] = array('href' => 'goods.php?act=list', 'text' => $_LANG['01_goods_list']);
			sys_msg($_LANG['save_products'], 0, $link);
			exit();
		}
	}
}
else if ($_REQUEST['act'] == 'download') {
	admin_priv('goods_manage');
	$goods_id = (isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0);
	$goods_type = (isset($_REQUEST['goods_type']) ? intval($_REQUEST['goods_type']) : '');
	header('Content-type: application/vnd.ms-excel; charset=utf-8');
	Header('Content-Disposition: attachment; filename=goods_attr_batch.csv');

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

			if ($_CFG['goods_attr_price'] == 1) {
				$data = join(',', $_LANG['upload_goods_attr']) . "\t\n";
				$res = get_goodsLattr_list($goods_id, $goods_type);

				if (0 < count($res)) {
					for ($i = 0; $i < count($res); $i++) {
						$data .= join(',', array($res[$i]['goods_sn'], $res[$i]['attr_all_value'], $res[$i]['attr_price'], $res[$i]['attr_price'])) . "\t\n";
					}
				}
			}
			else {
				$data = join(',', $_LANG['upload_area_attr']) . "\t\n";
				$res = get_goodsLattr_list($goods_id, $goods_type);

				if (0 < count($res)) {
					for ($i = 0; $i < count($res); $i++) {
						$data .= join(',', array($res[$i]['goods_sn'], $res[$i]['attr_all_value'], $res[$i]['attr_sort'], $res[$i]['attr_price'])) . "\t\n";
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
