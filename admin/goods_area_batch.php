<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_list_download($goods_name = '', $area_info = array())
{
	if (0 < count($area_info)) {
		$arr = array();

		for ($i = 0; $i < count($area_info); $i++) {
			$arr[$i]['goods_name'] = $goods_name;
			$arr[$i]['area_name'] = $area_info[$i]['region_name'];
			$arr[$i]['number'] = '';
			$arr[$i]['price'] = '';
			$arr[$i]['promote_price'] = '';
			$arr[$i]['give_integral'] = '';
			$arr[$i]['rank_integral'] = '';
			$arr[$i]['pay_integral'] = '';
			$arr[$i]['region_sort'] = $i;
		}

		return $arr;
	}
	else {
		return array();
	}
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require 'includes/lib_goods.php';

if ($_REQUEST['act'] == 'add') {
	admin_priv('goods_manage');
	$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => 'back_area_batch_list'));
	$goods_id = isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;

	if (0 < $goods_id) {
		$smarty->assign('action_link', array('text' => $_LANG['goto_goods'], 'href' => 'goods.php?act=edit&goods_id=' . $goods_id . '&extension_code='));
	}

	$dir = opendir('../languages');
	$lang_list = array('UTF8' => $_LANG['charset']['utf8'], 'GB2312' => $_LANG['charset']['zh_cn'], 'BIG5' => $_LANG['charset']['zh_tw']);
	$download_list = array();

	while (@$file = readdir($dir)) {
		if ($file != '.' && $file != '..' && $file != '.svn' && $file != '_svn' && is_dir('../languages/' . $file) == true) {
			$download_list[$file] = sprintf($_LANG['download_file'], isset($_LANG['charset'][$file]) ? $_LANG['charset'][$file] : $file);
		}
	}

	@closedir($dir);
	$smarty->assign('lang_list', $lang_list);
	$smarty->assign('download_list', $download_list);
	$smarty->assign('goods_id', $goods_id);
	$goods_date = array('goods_name');
	$where = 'goods_id = \'' . $goods_id . '\'';
	$goods_name = get_table_date('goods', $where, $goods_date, 2);
	$smarty->assign('goods_name', $goods_name);
	$ur_here = $_LANG['13_batch_add'];
	$smarty->assign('ur_here', $ur_here);
	assign_query_info();
	$smarty->display('goods_area_batch_add.dwt');
}
else if ($_REQUEST['act'] == 'upload') {
	admin_priv('goods_manage');
	$goods_id = isset($_REQUEST['goods_id']) && !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;

	if ($_FILES['file']['name']) {
		$line_number = 0;
		$arr = array();
		$goods_list = array();
		$field_list = array_keys($_LANG['upload_area']);
		$_POST['charset'] = 'GB2312';
		$data = file($_FILES['file']['tmp_name']);

		if (0 < count($data)) {
			foreach ($data as $line) {
				if ($line_number == 0) {
					$line_number++;
					continue;
				}

				if ($_POST['charset'] != 'UTF8' && strpos(strtolower(EC_CHARSET), 'utf') === 0) {
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

					if ($i == $len - 1) {
						if (!isset($field_list[count($arr)])) {
							continue;
						}

						$field_name = $field_list[count($arr)];
						$arr[$field_name] = trim($buff);
					}
				}

				$goods_list[] = $arr;
			}

			$goods_list = get_goods_bacth_area_list($goods_list, $goods_id);
			get_insert_bacth_area($goods_list);
			$link[] = array('href' => 'goods.php?act=list', 'text' => $_LANG['01_goods_list']);

			if ($goods_id) {
				$link[] = array('href' => 'goods.php?act=edit&goods_id=' . $goods_id . '&extension_code=', 'text' => $_LANG['03_goods_edit']);
				$link[] = array('href' => 'goods_area_batch.php?act=add&goods_id=' . $goods_id, 'text' => $_LANG['add_area_batch']);
			}
			else {
				$link[] = array('href' => 'goods_area_batch.php?act=add', 'text' => $_LANG['add_area_batch']);
			}

			sys_msg($_LANG['save_products'], 0, $link);
			exit();
		}
	}
}
else if ($_REQUEST['act'] == 'download') {
	admin_priv('goods_manage');
	$goods_id = isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
	header('Content-type: application/vnd.ms-excel; charset=utf-8');
	Header('Content-Disposition: attachment; filename=area_info_list.csv');

	if ($_GET['charset'] != $_CFG['lang']) {
		$lang_file = '../languages/' . $_GET['charset'] . '/' . ADMIN_PATH . '/goods_area_batch.php';

		if (file_exists($lang_file)) {
			unset($_LANG['upload_area']);
			require $lang_file;
		}
	}

	if (isset($_LANG['upload_area'])) {
		if ($_GET['charset'] == 'zh_cn' || $_GET['charset'] == 'zh_tw') {
			$to_charset = $_GET['charset'] == 'zh_cn' ? 'GB2312' : 'BIG5';
			$data = join(',', $_LANG['upload_area']) . "\t\n";
			$goods_date = array('goods_name');
			$where = 'goods_id = \'' . $goods_id . '\'';
			$goods_name = get_table_date('goods', $where, $goods_date, 2);
			$area_date = array('region_name');
			$where = 'region_type = 2';
			$area_info = get_table_date('region_warehouse', $where, $area_date, 1);
			$area_info = get_list_download($goods_name, $area_info);

			if (0 < count($area_info)) {
				for ($i = 0; $i < count($area_info); $i++) {
					$data .= $area_info[$i]['goods_name'] . ',';
					$data .= $area_info[$i]['area_name'] . ',';
					$data .= $area_info[$i]['number'] . ',';
					$data .= $area_info[$i]['price'] . ',';
					$data .= $area_info[$i]['promote_price'] . ',';
					$data .= $area_info[$i]['give_integral'] . ',';
					$data .= $area_info[$i]['rank_integral'] . ',';
					$data .= $area_info[$i]['pay_integral'] . ',';
					$data .= $area_info[$i]['region_sort'] . "\t\n";
				}
			}

			echo ecs_iconv(EC_CHARSET, $to_charset, $data);
		}
		else {
			echo join(',', $_LANG['upload_area']);
		}
	}
	else {
		echo 'error: $_LANG[upload_area] not exists';
	}
}

?>
