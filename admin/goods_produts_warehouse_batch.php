<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_list_download($goods_sn = '', $warehouse_info = array(), $attr_info, $attr_num, $model = 0)
{
	$goods_date = array('model_attr');
	$where = 'goods_sn = \'' . $goods_sn . '\' and is_delete = 0';
	$arr = array();
	$attr = array();
	if (0 < count($warehouse_info) && $model == 1) {
		if ($attr_info) {
			foreach ($attr_info as $k => $v) {
				if ($v) {
					foreach ($v as $k2 => $v2) {
						if ($k2 == 'attr_values') {
							foreach ($v2 as $kid => $rid) {
								$v2[$kid] = $rid . '-' . $v['attr_id'];
							}

							$attr[] = $v2;
						}
					}
				}
			}
		}

		if ($attr) {
			$comb = combination(array_keys($attr), $attr_num);
			$res = array();

			foreach ($comb as $r) {
				$t = array();

				foreach ($r as $k) {
					$t[] = $attr[$k];
				}

				$res = array_merge($res, attr_group($t));
			}

			foreach ($res as $k => $v) {
				$arr[$k]['goods_sn'] = $goods_sn;
				$arr[$k]['region_name'] = $warehouse_info[0]['region_name'];
				$arr[$k]['attr_value'] = $v;

				if ($GLOBALS['_CFG']['goods_attr_price'] == 1) {
					if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
						$arr[$k]['product_market_price'] = '';
					}

					$arr[$k]['product_price'] = '';

					if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
						$arr[$k]['product_promote_price'] = '';
					}
				}

				$arr[$k]['product_number'] = '';
				$arr[$k]['product_warn_number'] = '';
				$arr[$k]['product_sn'] = '';
				$arr[$k]['bar_code'] = '';
			}
		}
	}

	return $arr;
}

function get_attribute_list($goods_id = 0)
{
	$sql = 'SELECT a.attr_id, a.attr_name FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS ga ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' as a ON ga.attr_id = a.attr_id' . (' WHERE ga.goods_id = \'' . $goods_id . '\' group by ga.attr_id ORDER BY a.sort_order, ga.attr_id ASC');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['attr_name'] = $row['attr_name'];
		$arr[$key]['goods_attr'] = get_goods_attr_list($row['attr_id'], $goods_id);
	}

	return $arr;
}

function get_goods_attr_list($attr_id = 0, $goods_id = 0)
{
	$sql = 'select goods_attr_id, attr_value from ' . $GLOBALS['ecs']->table('goods_attr') . (' where goods_id = \'' . $goods_id . '\' and attr_id = \'' . $attr_id . '\' order by goods_attr_id asc');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['goods_attr_id'] = $row['goods_attr_id'];
		$arr[$key]['attr_value'] = $row['attr_value'];
	}

	return $arr;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require 'includes/lib_goods.php';

if ($_REQUEST['act'] == 'add') {
	admin_priv('goods_manage');
	$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => 'produts_warehouse_batch'));
	$goods_id = isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
	$model = isset($_REQUEST['model']) ? intval($_REQUEST['model']) : 0;
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;

	if (0 < $goods_id) {
		$smarty->assign('action_link', array('text' => '返回商品货品详细页', 'href' => 'goods.php?act=product_list&goods_id=' . $goods_id));
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
	$smarty->assign('model', $model);
	$smarty->assign('warehouse_id', $warehouse_id);
	$attribute_list = get_attribute_list($goods_id);
	$smarty->assign('attribute_list', $attribute_list);
	$goods_date = array('goods_name');
	$where = 'goods_id = \'' . $goods_id . '\'';
	$goods_name = get_table_date('goods', $where, $goods_date, 2);
	$smarty->assign('goods_name', $goods_name);
	$ur_here = $_LANG['13_batch_add'];
	$smarty->assign('ur_here', $ur_here);
	assign_query_info();
	$smarty->display('goods_produts_warehouse_batch.dwt');
}
else if ($_REQUEST['act'] == 'upload') {
	admin_priv('goods_manage');
	$smarty->assign('menu_select', array('action' => '02_cat_and_goods', 'current' => 'produts_area_batch'));

	if ($_FILES['file']['name']) {
		$attr_names = file($_FILES['file']['tmp_name']);
		$attr_names = explode(',', $attr_names[0]);

		if ($GLOBALS['_CFG']['goods_attr_price'] == 1) {
			if ($GLOBALS['_CFG']['add_shop_price'] == 1) {
				$end = -7 + 2;
			}
			else {
				$end = -7;
			}
		}
		else {
			$end = -4;
		}

		$attr_names = array_slice($attr_names, 6, $end);

		foreach ($attr_names as $k => $v) {
			$attr_names[$k] = ecs_iconv('GBK', 'UTF8', $v);
		}

		$attr_num = count($attr_names);
		$line_number = 0;
		$arr = array();
		$goods_list = array();
		$field_list = array_keys($_LANG['upload_product']);

		for ($i = 0; $i < $attr_num; $i++) {
			$field_list[] = 'goods_attr' . $i;
		}

		if ($GLOBALS['_CFG']['goods_attr_price'] == 1) {
			if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
				$field_list[] = 'product_market_price';
			}

			$field_list[] = 'product_price';

			if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
				$field_list[] = 'product_promote_price';
			}
		}

		$field_list[] = 'product_number';
		$field_list[] = 'product_warn_number';
		$field_list[] = 'product_sn';
		$field_list[] = 'bar_code';
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

			$goods_list = get_produts_warehouse_list2($goods_list, $attr_num);
		}
	}

	$_SESSION['goods_list'] = $goods_list;
	$smarty->assign('full_page', 2);
	$smarty->assign('page', 1);
	$smarty->assign('attr_names', $attr_names);
	$smarty->assign('cfg', $GLOBALS['_CFG']);
	assign_query_info();
	$smarty->assign('ur_here', $_LANG['13_batch_add']);
	$smarty->display('goods_produts_warehouse_batch_add.dwt');
}
else if ($_REQUEST['act'] == 'ajax_insert') {
	admin_priv('goods_manage');
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$result = array(
		'list'    => array(),
		'is_stop' => 0
		);
	$page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$page_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 1;
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
		$other['goods_attr'] = $result['list']['goods_attr'];

		if ($GLOBALS['_CFG']['goods_attr_price'] == 1) {
			if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
				$other['product_market_price'] = $result['list']['product_market_price'];
			}

			$other['product_price'] = $result['list']['product_price'];

			if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
				$other['product_promote_price'] = $result['list']['product_promote_price'];
			}
		}

		$other['product_number'] = $result['list']['product_number'];
		$other['product_warn_number'] = $result['list']['product_warn_number'];

		if ($result['list']['product_sn']) {
			$other['product_sn'] = $result['list']['product_sn'];
		}

		if ($result['list']['bar_code']) {
			$other['bar_code'] = $result['list']['bar_code'];
		}

		$other['warehouse_id'] = $result['list']['warehouse_id'];
		$where = '';

		if (!empty($result['list']['goods_attr'])) {
			$goods_attr = explode('|', $result['list']['goods_attr']);

			foreach ($goods_attr as $key => $val) {
				$where .= ' AND FIND_IN_SET(\'' . $val . '\', REPLACE(goods_attr, \'|\', \',\')) ';
			}

			$sql = 'SELECT product_id FROM ' . $GLOBALS['ecs']->table('products_warehouse') . ' WHERE goods_id = \'' . $result['list']['goods_id'] . '\'' . ' AND warehouse_id = \'' . $result['list']['warehouse_id'] . '\'' . $where;
			$product_id = $GLOBALS['db']->getOne($sql);

			if ($product_id) {
				$db->autoExecute($ecs->table('products_warehouse'), $other, 'UPDATE', 'product_id = \'' . $product_id . '\'');

				if ($GLOBALS['_CFG']['goods_attr_price'] == 1) {
					$goods_other = array('product_price' => $result['list']['product_price'], 'product_promote_price' => $result['list']['product_promote_price']);
					$db->autoExecute($ecs->table('goods'), $goods_other, 'UPDATE', 'goods_id = \'' . $result['list']['goods_id'] . ('\' AND product_id = \'' . $product_id . '\' AND product_table = \'products_warehouse\''));
				}

				$result['status_lang'] = '<span style="color: red;">已更新数据成功</span>';
			}
			else {
				$admin_id = get_admin_id();
				$other['admin_id'] = $admin_id;
				$db->autoExecute($ecs->table('products_warehouse'), $other, 'INSERT');
				$product_id = $db->insert_id();

				if ($product_id) {
					$result['status_lang'] = '<span style="color: red;">已添加数据成功</span>';
				}
				else {
					$result['status_lang'] = '<span style="color: red;">已添加数据失败</span>';
				}
			}
		}
	}

	exit($json->encode($result));
}
else if ($_REQUEST['act'] == 'download') {
	admin_priv('goods_manage');
	$goods_id = isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
	$model = isset($_REQUEST['model']) ? intval($_REQUEST['model']) : 0;
	$warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
	$goods_attr = isset($_REQUEST['goods_attr']) ? explode(',', $_REQUEST['goods_attr']) : array();
	header('Content-type: application/vnd.ms-excel; charset=utf-8');
	Header('Content-Disposition: attachment; filename=goods_produts_warehouse_list' . $goods_id . '_' . $warehouse_id . '.csv');

	if ($_GET['charset'] != $_CFG['lang']) {
		$lang_file = '../languages/' . $_GET['charset'] . '/' . ADMIN_PATH . '/goods_produts_warehouse_batch.php';

		if (file_exists($lang_file)) {
			unset($_LANG['upload_product']);
			require $lang_file;
		}
	}

	if (isset($_LANG['upload_product'])) {
		if ($_GET['charset'] == 'zh_cn' || $_GET['charset'] == 'zh_tw') {
			$to_charset = $_GET['charset'] == 'zh_cn' ? 'GB2312' : 'BIG5';
			$data = join(',', $_LANG['upload_product']);
			$attribute = get_goods_specifications_list($goods_id);

			if (empty($attribute)) {
				$link[] = array('href' => 'goods.php?act=edit&goods_id=' . $goods_id, 'text' => $_LANG['edit_goods']);
				sys_msg($_LANG['not_exist_goods_attr'], 1, $link);
			}

			foreach ($attribute as $attribute_value) {
				$_attribute[$attribute_value['attr_id']]['attr_values'][] = $attribute_value['attr_value'];
				$_attribute[$attribute_value['attr_id']]['attr_id'] = $attribute_value['attr_id'];
				$_attribute[$attribute_value['attr_id']]['attr_name'] = $attribute_value['attr_name'];
			}

			$attribute_count = count($_attribute);

			foreach ($_attribute as $k => $v) {
				$data .= ',' . $v['attr_name'];
			}

			if ($GLOBALS['_CFG']['goods_attr_price'] == 1) {
				if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
					$data .= ',' . $_LANG['market_price'];
				}

				$data .= ',' . $_LANG['product_price'];

				if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
					$data .= ',' . $_LANG['promote_price'];
				}
			}

			$data .= ',' . $_LANG['product_number'];
			$data .= ',' . $_LANG['product_warn_number'];
			$data .= ',' . $_LANG['product_sn'];
			$data .= ',' . $_LANG['bar_code'] . "\t\n";

			if ($goods_id) {
				$goods_info = get_admin_goods_info($goods_id, array('goods_name', 'goods_sn', 'user_id'));
				$goods_info['shop_name'] = get_shop_name($goods_info['user_id'], 1);
			}
			else {
				$adminru = get_admin_ru_id();
				$goods_info['user_id'] = $adminru['ru_id'];
				$goods_info['shop_name'] = get_shop_name($adminru['ru_id'], 1);
			}

			$area_date = array('region_name');
			$warehouse_where = '';

			if (0 < $warehouse_id) {
				$warehouse_where = ' and region_id = ' . $warehouse_id;
			}

			$where = 'region_type = 0' . $warehouse_where;
			$warehouse_info = get_table_date('region_warehouse', $where, $area_date, 1);
			$attr_info = get_list_download($goods_info['goods_sn'], $warehouse_info, $_attribute, count($_attribute), $model);

			if ($attr_info) {
				if (1 < count($attr_info)) {
					foreach ($attr_info as $k => $v) {
						$data .= $goods_id . ',';
						$data .= $goods_info['goods_name'] . ',';
						$data .= $goods_info['goods_sn'] . ',';
						$data .= $goods_info['shop_name'] . ',';
						$data .= $goods_info['user_id'] . ',';
						$data .= $attr_info[$k]['region_name'] . ',';
						$data .= implode(',', $v['attr_value']) . ',';

						if ($GLOBALS['_CFG']['goods_attr_price'] == 1) {
							if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
								$data .= $attr_info[$k]['product_market_price'] . ',';
							}

							$data .= $attr_info[$k]['product_price'] . ',';

							if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
								$data .= $attr_info[$k]['product_promote_price'] . ',';
							}
						}

						$data .= $attr_info[$k]['product_number'] . ',';
						$data .= $attr_info[$k]['product_warn_number'] . ',';
						$data .= $attr_info[$k]['product_sn'] . ',';
						$data .= $attr_info[$k]['bar_code'] . "\t\n";
					}
				}
				else {
					$attr_value = $attr_info[0]['attr_value'];
					$data .= $goods_id . ',';
					$data .= $goods_info['goods_name'] . ',';
					$data .= $goods_info['goods_sn'] . ',';
					$data .= $goods_info['shop_name'] . ',';
					$data .= $goods_info['user_id'] . ',';
					$data .= $attr_info[0]['region_name'] . ',';

					foreach ($attr_value as $key => $value) {
						$data .= $value . ',';
					}

					if ($GLOBALS['_CFG']['goods_attr_price'] == 1) {
						if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
							$data .= $attr_info[0]['product_market_price'] . ',';
						}

						$data .= $attr_info[0]['product_price'] . ',';

						if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
							$data .= $attr_info[0]['product_promote_price'] . ',';
						}
					}

					$data .= $attr_info[0]['product_number'] . ',';
					$data .= $attr_info[0]['product_warn_number'] . ',';
					$data .= $attr_info[0]['product_sn'] . ',';
					$data .= $attr_info[0]['bar_code'] . "\t\n";
				}
			}

			echo ecs_iconv(EC_CHARSET, $to_charset, $data);
		}
		else {
			echo join(',', $_LANG['upload_product']);
		}
	}
	else {
		echo 'error: $_LANG[upload_product] not exists';
	}
}

?>
