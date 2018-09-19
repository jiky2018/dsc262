<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function assign_sql($sql)
{
	global $db;
	global $smarty;
	global $_LANG;
	$sql = stripslashes($sql);
	$smarty->assign('sql', $sql);
	$sql = str_replace("\r", '', $sql);
	$query_items = explode(";\n", $sql);

	foreach ($query_items as $key => $value) {
		if (empty($value)) {
			unset($query_items[$key]);
		}
	}

	if (1 < count($query_items)) {
		foreach ($query_items as $key => $value) {
			if ($db->query($value, 'SILENT')) {
				$smarty->assign('type', 1);
			}
			else {
				$smarty->assign('type', 0);
				$smarty->assign('error', $db->error());
				return NULL;
			}
		}

		return NULL;
	}

	if (preg_match('/^(?:UPDATE|DELETE|TRUNCATE|ALTER|DROP|FLUSH|INSERT|REPLACE|SET|CREATE)\\s+/i', $sql)) {
		if ($db->query($sql, 'SILENT')) {
			$smarty->assign('type', 1);
		}
		else {
			$smarty->assign('type', 0);
			$smarty->assign('error', $db->error());
		}
	}
	else {
		$data = $db->GetAll($sql);

		if ($data === false) {
			$smarty->assign('type', 0);
			$smarty->assign('error', $db->error());
		}
		else {
			$result = '';
			if (is_array($data) && isset($data[0]) === true) {
				$result = "<table cellpadding='1' cellspacing='1'> \n <tr>";
				$keys = array_keys($data[0]);
				$i = 0;

				for ($num = count($keys); $i < $num; $i++) {
					$result .= '<th>' . $keys[$i] . "</th>\n";
				}

				$result .= "</tr> \n";

				foreach ($data as $data1) {
					$result .= "<tr>\n";

					foreach ($data1 as $value) {
						$result .= '<td>' . $value . '</td>';
					}

					$result .= "</tr>\n";
				}

				$result .= "</table>\n";
			}
			else {
				$result = '<center><h3>' . $_LANG['no_data'] . '</h3></center>';
			}

			$smarty->assign('type', 2);
			$smarty->assign('result', $result);
		}
	}
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$_POST['sql'] = !empty($_POST['sql']) ? trim($_POST['sql']) : '';

if (!$_POST['sql']) {
	$_REQUEST['act'] = 'main';
}

if ($_REQUEST['act'] == 'main') {
	admin_priv('sql_query');
	assign_query_info();
	$smarty->assign('type', -1);
	$smarty->assign('ur_here', $_LANG['04_sql_query']);
	$smarty->display('sql.dwt');
}

if ($_REQUEST['act'] == 'query') {
	admin_priv('sql_query');
	assign_sql($_POST['sql']);
	assign_query_info();
	$smarty->assign('ur_here', $_LANG['04_sql_query']);
	$smarty->display('sql.dwt');
}

?>
