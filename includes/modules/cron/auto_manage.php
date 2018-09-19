<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

$cron_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/cron/auto_manage.php';

if (file_exists($cron_lang)) {
	global $_LANG;
	include_once $cron_lang;
}

if (isset($set_modules) && ($set_modules == true)) {
	$i = (isset($modules) ? count($modules) : 0);
	$modules[$i]['code'] = basename(__FILE__, '.php');
	$modules[$i]['desc'] = 'auto_manage_desc';
	$modules[$i]['author'] = 'ECSHOP TEAM';
	$modules[$i]['website'] = 'http://www.ecmoban.com';
	$modules[$i]['version'] = '1.0.0';
	$modules[$i]['config'] = array(
	array('name' => 'auto_manage_count', 'type' => 'select', 'value' => '5')
	);
	return NULL;
}

$time = gmtime();
$limit = (!empty($cron['auto_manage_count']) ? $cron['auto_manage_count'] : 5);
$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('auto_manage') . ' WHERE starttime > \'0\' AND starttime <= \'' . $time . '\' OR endtime > \'0\' AND endtime <= \'' . $time . '\' LIMIT ' . $limit;
$autodb = $db->getAll($sql);

foreach ($autodb as $key => $val) {
	$del = $up = false;

	if ($val['type'] == 'goods') {
		$goods = true;
		$where = ' WHERE goods_id = \'' . $val['item_id'] . '\'';
	}
	else {
		$goods = false;
		$where = ' WHERE article_id = \'' . $val['item_id'] . '\'';
	}

	if (!empty($val['starttime']) && !empty($val['endtime'])) {
		if (($val['starttime'] <= $time) && ($time < $val['endtime'])) {
			$up = true;
			$del = false;
		}
		else {
			if (($time <= $val['starttime']) && ($val['endtime'] < $time)) {
				$up = false;
				$del = false;
			}
			else {
				if (($val['starttime'] == $time) && ($time == $val['endtime'])) {
					$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('auto_manage') . 'WHERE item_id = \'' . $val['item_id'] . '\' AND type = \'' . $val['type'] . '\'';
					$db->query($sql);
					continue;
				}
				else if ($val['endtime'] < $val['starttime']) {
					$up = true;
					$del = true;
				}
				else if ($val['starttime'] < $val['endtime']) {
					$up = false;
					$del = true;
				}
				else {
					$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('auto_manage') . 'WHERE item_id = \'' . $val['item_id'] . '\' AND type = \'' . $val['type'] . '\'';
					$db->query($sql);
					continue;
				}
			}
		}
	}
	else if (!empty($val['starttime'])) {
		$up = true;
		$del = true;
	}
	else {
		$up = false;
		$del = true;
	}

	if ($goods) {
		if ($up) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . ' SET is_on_sale = 1 ' . $where;
		}
		else {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . ' SET is_on_sale = 0 ' . $where;
		}
	}
	else if ($up) {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('article') . ' SET is_open = 1 ' . $where;
	}
	else {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('article') . ' SET is_open = 0 ' . $where;
	}

	$db->query($sql);

	if ($del) {
		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('auto_manage') . 'WHERE item_id = \'' . $val['item_id'] . '\' AND type = \'' . $val['type'] . '\'';
		$db->query($sql);
	}
	else {
		if ($up) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('auto_manage') . ' SET starttime = 0 WHERE item_id = \'' . $val['item_id'] . '\' AND type = \'' . $val['type'] . '\'';
		}
		else {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('auto_manage') . ' SET endtime = 0 WHERE item_id = \'' . $val['item_id'] . '\' AND type = \'' . $val['type'] . '\'';
		}

		$db->query($sql);
	}
}

?>
