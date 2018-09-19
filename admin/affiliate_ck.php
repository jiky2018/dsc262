<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_affiliate_ck()
{
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	empty($affiliate) && ($affiliate = array());
	$separate_by = $affiliate['config']['separate_by'];
	$status = isset($_REQUEST['status']) && !empty($_REQUEST['status']) ? intval($_REQUEST['status']) : 0;
	$order_sn = isset($_REQUEST['order_sn']) && !empty($_REQUEST['order_sn']) ? dsc_addslashes($_REQUEST['order_sn']) : '';
	$auid = isset($_REQUEST['auid']) && !empty($_REQUEST['auid']) ? intval($_REQUEST['auid']) : 0;
	$order_status = array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART);
	$sqladd = ' AND o.order_status ' . db_create_in($order_status);

	if ($status) {
		$sqladd = ' AND o.is_separate = ' . $status;
		$filter['status'] = $status;
	}

	if ($order_sn) {
		$sqladd = ' AND (o.order_sn LIKE \'%' . $order_sn . '%\' OR u.user_name LIKE \'%' . $order_sn . '%\'  OR  u.nick_name LIKE\'%' . $order_sn . '%\') ';
		$filter['order_sn'] = $order_sn;
	}

	if ($auid) {
		$sqladd = ' AND a.user_id = \'' . $auid . '\'';
	}

	$sqladd .= ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
	$sqladd .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og WHERE og.order_id = o.order_id AND og.ru_id = 0 LIMIT 1) = 0';

	if (!empty($affiliate['on'])) {
		if (empty($separate_by)) {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' o' . ' LEFT JOIN' . $GLOBALS['ecs']->table('users') . ' u ON o.user_id = u.user_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('affiliate_log') . ' a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND (u.parent_id > 0 AND o.is_separate = 0 OR o.is_separate > 0) ' . $sqladd);
		}
		else {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' o' . ' LEFT JOIN' . $GLOBALS['ecs']->table('users') . ' u ON o.user_id = u.user_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('affiliate_log') . ' a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND (o.parent_id > 0 AND o.is_separate = 0 OR o.is_separate > 0) ' . $sqladd);
		}
	}
	else {
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' o' . ' LEFT JOIN' . $GLOBALS['ecs']->table('users') . ' u ON o.user_id = u.user_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('affiliate_log') . ' a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND o.is_separate > 0 ' . $sqladd);
	}

	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$logdb = array();
	$filter = page_and_size($filter);

	if (!empty($affiliate['on'])) {
		if (empty($separate_by)) {
			$sql = 'SELECT o.*, a.log_id, a.user_id as suid,  a.user_name as auser, a.money, a.point, a.separate_type,u.parent_id as up FROM ' . $GLOBALS['ecs']->table('order_info') . ' o' . ' LEFT JOIN' . $GLOBALS['ecs']->table('users') . ' u ON o.user_id = u.user_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('affiliate_log') . ' a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND (u.parent_id > 0 AND o.is_separate = 0 OR o.is_separate > 0) ' . $sqladd) . ' ORDER BY order_id DESC' . ' LIMIT ' . $filter['start'] . (',' . $filter['page_size']);
		}
		else {
			$sql = 'SELECT o.*, a.log_id,a.user_id as suid, a.user_name as auser, a.money, a.point, a.separate_type,u.parent_id as up FROM ' . $GLOBALS['ecs']->table('order_info') . ' o' . ' LEFT JOIN' . $GLOBALS['ecs']->table('users') . ' u ON o.user_id = u.user_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('affiliate_log') . ' a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND (o.parent_id > 0 AND o.is_separate = 0 OR o.is_separate > 0) ' . $sqladd) . ' ORDER BY order_id DESC' . ' LIMIT ' . $filter['start'] . (',' . $filter['page_size']);
		}
	}
	else {
		$sql = 'SELECT o.*, a.log_id,a.user_id as suid, a.user_name as auser, a.money, a.point, a.separate_type,u.parent_id as up FROM ' . $GLOBALS['ecs']->table('order_info') . ' o' . ' LEFT JOIN' . $GLOBALS['ecs']->table('users') . ' u ON o.user_id = u.user_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('affiliate_log') . ' a ON o.order_id = a.order_id' . (' WHERE o.user_id > 0 AND o.is_separate > 0 ' . $sqladd) . ' ORDER BY order_id DESC' . ' LIMIT ' . $filter['start'] . (',' . $filter['page_size']);
	}

	$query = $GLOBALS['db']->query($sql);

	while ($rt = $GLOBALS['db']->fetch_array($query)) {
		if (empty($separate_by) && 0 < $rt['up']) {
			$rt['separate_able'] = 1;
		}
		else {
			if (!empty($separate_by) && 0 < $rt['parent_id']) {
				$rt['separate_able'] = 1;
			}
		}

		if (!empty($rt['suid'])) {
			$rt['info'] = sprintf($GLOBALS['_LANG']['separate_info2'], $rt['suid'], $rt['auser'], $rt['money'], $rt['point']);
			if ($rt['separate_type'] == -1 || $rt['separate_type'] == -2) {
				$rt['is_separate'] = 3;
				$rt['info'] = '<s>' . $rt['info'] . '</s>';
			}
		}

		$logdb[] = $rt;
	}

	$arr = array('logdb' => $logdb, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function write_affiliate_log($oid, $uid, $username, $money, $point, $separate_by)
{
	$time = gmtime();
	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('affiliate_log') . '( order_id, user_id, user_name, time, money, point, separate_type)' . (' VALUES ( \'' . $oid . '\', \'' . $uid . '\', \'' . $username . '\', \'' . $time . '\', \'' . $money . '\', \'' . $point . '\', ' . $separate_by . ')');

	if ($oid) {
		$GLOBALS['db']->query($sql);
	}
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
admin_priv('affiliate_ck');
$timestamp = gmtime();
$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
empty($affiliate) && ($affiliate = array());
$separate_on = $affiliate['on'];

if ($_REQUEST['act'] == 'list') {
	$auid = isset($_GET['auid']) && !empty($_GET['auid']) ? intval($_GET['auid']) : 0;
	$logdb = get_affiliate_ck();
	$smarty->assign('full_page', 1);
	$smarty->assign('ur_here', $_LANG['affiliate_ck']);
	$smarty->assign('on', $separate_on);
	$smarty->assign('logdb', $logdb['logdb']);
	$smarty->assign('filter', $logdb['filter']);
	$smarty->assign('record_count', $logdb['record_count']);
	$smarty->assign('page_count', $logdb['page_count']);

	if (!empty($auid)) {
		$smarty->assign('action_link', array('text' => $_LANG['back_note'], 'href' => 'users.php?act=edit&id=' . $auid));
	}

	assign_query_info();
	$smarty->display('affiliate_ck_list.dwt');
}
else if ($_REQUEST['act'] == 'query') {
	$logdb = get_affiliate_ck();
	$smarty->assign('logdb', $logdb['logdb']);
	$smarty->assign('on', $separate_on);
	$smarty->assign('filter', $logdb['filter']);
	$smarty->assign('record_count', $logdb['record_count']);
	$smarty->assign('page_count', $logdb['page_count']);
	$sort_flag = sort_flag($logdb['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('affiliate_ck_list.dwt'), '', array('filter' => $logdb['filter'], 'page_count' => $logdb['page_count']));
}
else if ($_REQUEST['act'] == 'del') {
	$oid = isset($_REQUEST['oid']) && !empty($_REQUEST['oid']) ? intval($_REQUEST['oid']) : 0;
	$stat = $db->getOne('SELECT is_separate FROM ' . $GLOBALS['ecs']->table('order_info') . (' WHERE order_id = \'' . $oid . '\''));

	if (empty($stat)) {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET is_separate = 2' . (' WHERE order_id = \'' . $oid . '\'');
		$db->query($sql);
	}

	$links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
	sys_msg($_LANG['edit_ok'], 0, $links);
}
else if ($_REQUEST['act'] == 'rollback') {
	$logid = isset($_REQUEST['logid']) && !empty($_REQUEST['logid']) ? intval($_REQUEST['logid']) : 0;
	$stat = $db->getRow('SELECT * FROM ' . $GLOBALS['ecs']->table('affiliate_log') . (' WHERE log_id = \'' . $logid . '\''));

	if (!empty($stat)) {
		if ($stat['separate_type'] == 1) {
			$flag = -2;
		}
		else {
			$flag = -1;
		}

		log_account_change($stat['user_id'], 0 - $stat['money'], 0, 0 - $stat['point'], 0, $_LANG['loginfo']['cancel']);
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('affiliate_log') . (' SET separate_type = \'' . $flag . '\'') . (' WHERE log_id = \'' . $logid . '\'');
		$db->query($sql);
	}

	$links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
	sys_msg($_LANG['edit_ok'], 0, $links);
}
else if ($_REQUEST['act'] == 'separate') {
	include_once ROOT_PATH . 'includes/lib_order.php';
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	empty($affiliate) && ($affiliate = array());
	$separate_by = $affiliate['config']['separate_by'];
	$oid = isset($_REQUEST['oid']) && !empty($_REQUEST['oid']) ? intval($_REQUEST['oid']) : 0;
	$row = $db->getRow('SELECT o.order_sn, o.is_separate, (o.goods_amount - o.discount) AS goods_amount, o.user_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' o' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' u ON o.user_id = u.user_id' . (' WHERE order_id = \'' . $oid . '\''));
	$order_sn = $row['order_sn'];

	if (empty($row['is_separate'])) {
		$affiliate['config']['level_point_all'] = (double) $affiliate['config']['level_point_all'];
		$affiliate['config']['level_money_all'] = (double) $affiliate['config']['level_money_all'];

		if ($affiliate['config']['level_point_all']) {
			$affiliate['config']['level_point_all'] /= 100;
		}

		if ($affiliate['config']['level_money_all']) {
			$affiliate['config']['level_money_all'] /= 100;
		}

		$money = round($affiliate['config']['level_money_all'] * $row['goods_amount'], 2);
		$money = floatval($money);
		$integral = integral_to_give(array('order_id' => $oid, 'extension_code' => ''));
		$point = round($affiliate['config']['level_point_all'] * intval($integral['rank_points']), 0);
		$point = floatval($point);

		if (empty($separate_by)) {
			$num = count($affiliate['item']);

			for ($i = 0; $i < $num; $i++) {
				$affiliate['item'][$i]['level_point'] = (double) $affiliate['item'][$i]['level_point'];
				$affiliate['item'][$i]['level_money'] = (double) $affiliate['item'][$i]['level_money'];

				if ($affiliate['item'][$i]['level_point']) {
					$affiliate['item'][$i]['level_point'] /= 100;
				}

				if ($affiliate['item'][$i]['level_money']) {
					$affiliate['item'][$i]['level_money'] /= 100;
				}

				$setmoney = round($money * $affiliate['item'][$i]['level_money'], 2);
				$setpoint = round($point * $affiliate['item'][$i]['level_point'], 0);
				$row = $db->getRow('SELECT o.parent_id as user_id,u.user_name FROM ' . $GLOBALS['ecs']->table('users') . ' o' . ' LEFT JOIN' . $GLOBALS['ecs']->table('users') . ' u ON o.parent_id = u.user_id' . (' WHERE o.user_id = \'' . $row['user_id'] . '\''));
				$up_uid = $row['user_id'];
				if (empty($up_uid) || empty($row['user_name'])) {
					break;
				}
				else {
					$info = sprintf($_LANG['separate_info'], $order_sn, $setmoney, $setpoint);
					log_account_change($up_uid, $setmoney, 0, $setpoint, 0, $info);
					write_affiliate_log($oid, $up_uid, $row['user_name'], $setmoney, $setpoint, $separate_by);
				}
			}
		}
		else {
			$row = $db->getRow('SELECT o.parent_id, u.user_name FROM ' . $GLOBALS['ecs']->table('order_info') . ' o' . ' LEFT JOIN' . $GLOBALS['ecs']->table('users') . ' u ON o.parent_id = u.user_id' . (' WHERE o.order_id = \'' . $oid . '\''));
			$up_uid = $row['parent_id'];
			if (!empty($up_uid) && 0 < $up_uid) {
				$info = sprintf($_LANG['separate_info'], $order_sn, $money, $point);
				log_account_change($up_uid, $money, 0, $point, 0, $info);
				write_affiliate_log($oid, $up_uid, $row['user_name'], $money, $point, $separate_by);
			}
			else {
				$links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
				sys_msg($_LANG['edit_fail'], 1, $links);
			}
		}

		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET is_separate = 1' . (' WHERE order_id = \'' . $oid . '\'');
		$db->query($sql);
	}

	$links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
	sys_msg($_LANG['edit_ok'], 0, $links);
}

?>
