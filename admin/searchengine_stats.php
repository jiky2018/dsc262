<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/statistic.php';

if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'view';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

if ($_REQUEST['act'] == 'view') {
	admin_priv('client_flow_stats');
	if (isset($_POST) && !empty($_POST)) {
		$start_date = $_POST['start_date'];
		$end_date = $_POST['end_date'];
	}
	else {
		$start_date = local_date('Y-m-d', strtotime('-1 week'));
		$end_date = local_date('Y-m-d');
	}

	$max = 0;
	$general_xml = '<chart caption=\'' . $_LANG['tab_keywords'] . '\' shownames=\'1\' showvalues=\'0\' decimals=\'0\' numberPrefix=\'\' outCnvBaseFontSize=\'12\' baseFontSize=\'12\'>';
	$sql = 'SELECT keyword, count, searchengine ' . ' FROM ' . $ecs->table('keywords') . ' WHERE date >= \'' . $start_date . '\' AND date <= \'' . $end_date . '\'';

	if (isset($_POST['filter'])) {
		$sql .= ' AND ' . db_create_in($_POST['filter'], 'searchengine');
	}

	$res = $db->query($sql);
	$search = array();
	$searchengine = array();
	$keyword = array();

	while ($val = $db->fetchRow($res)) {
		$keyword[$val['keyword']] = 1;
		$searchengine[$val['searchengine']][$val['keyword']] = $val['count'];
	}

	$general_xml .= '<categories>';

	foreach ($keyword as $key => $val) {
		$key = str_replace('&', '＆', $key);
		$key = str_replace('>', '＞', $key);
		$key = str_replace('<', '＜', $key);
		$key = htmlspecialchars($key);
		$general_xml .= '<category label=\'' . str_replace('\'', '', $key) . '\' />';
	}

	$general_xml .= "</categories>\n";
	$i = 0;

	foreach ($searchengine as $key => $val) {
		$general_xml .= '<dataset seriesName=\'' . $key . '\' color=\'' . chart_color($i) . '\' showValues=\'0\'>';

		foreach ($keyword as $k => $v) {
			$count = 0;

			if (!empty($searchengine[$key][$k])) {
				$count = $searchengine[$key][$k];
			}

			$general_xml .= '<set value=\'' . $count . '\' />';
		}

		$general_xml .= '</dataset>';
		$i++;
	}

	$general_xml .= '</chart>';
	$smarty->assign('ur_here', $_LANG['searchengine_stats']);
	$smarty->assign('general_data', $general_xml);
	$searchengines = array('ecshop' => false, 'MSLIVE' => false, 'BAIDU' => false, 'GOOGLE' => false, 'GOOGLE CHINA' => false, 'CT114' => false, 'SOSO' => false);

	if (isset($_POST['filter'])) {
		foreach ($_POST['filter'] as $v) {
			$searchengines[$v] = true;
		}
	}

	$smarty->assign('searchengines', $searchengines);
	$smarty->assign('start_date', $start_date);
	$smarty->assign('end_date', $end_date);
	$filename = local_date('Ymd', $start_date) . '_' . local_date('Ymd', $end_date);
	$smarty->assign('action_link', array('text' => $_LANG['down_search_stats'], 'href' => 'searchengine_stats.php?act=download&start_date=' . $start_date . '&end_date=' . $end_date . '&filename=' . $filename));
	$smarty->assign('lang', $_LANG);
	assign_query_info();
	$smarty->display('searchengine_stats.htm');
}
else if ($_REQUEST['act'] == 'download') {
	$start_date = (empty($_REQUEST['start_date']) ? strtotime('-20 day') : intval($_REQUEST['start_date']));
	$end_date = (empty($_REQUEST['end_date']) ? time() : intval($_REQUEST['end_date']));
	$filename = $start_date . '_' . $end_date;
	$sql = 'SELECT keyword, count,searchengine ' . ' FROM ' . $ecs->table('keywords') . ' WHERE date >= \'' . $start_date . '\' AND date <= \'' . $end_date . '\'';
	$res = $db->query($sql);
	$searchengine = array();
	$keyword = array();

	while ($val = $db->fetchRow($res)) {
		$keyword[$val['keyword']] = 1;
		$searchengine[$val['searchengine']][$val['keyword']] = $val['count'];
	}

	header('Content-type: application/vnd.ms-excel; charset=utf-8');
	header('Content-Disposition: attachment; filename=' . $filename . '.xls');
	$data = '	';

	foreach ($searchengine as $k => $v) {
		$data .= $k . '	';
	}

	foreach ($keyword as $kw => $val) {
		$data .= "\n" . $kw . '	';

		foreach ($searchengine as $k => $v) {
			if (isset($searchengine[$k][$kw])) {
				$data .= $searchengine[$k][$kw] . '	';
			}
			else {
				$data .= '0' . '	';
			}
		}
	}

	echo ecs_iconv(EC_CHARSET, 'GB2312', $data) . '	';
}

?>
