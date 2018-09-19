<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function get_oss_add_file($file = array())
{
	if ($file) {
		if (!isset($GLOBALS['_CFG']['open_oss'])) {
			$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'open_oss\'';
			$is_oss = $GLOBALS['db']->getOne($sql, true);
		}
		else {
			$is_oss = $GLOBALS['_CFG']['open_oss'];
		}

		if ($is_oss == 1) {
			$bucket_info = get_bucket_info();
			$url = $GLOBALS['ecs']->url();
			$self = explode('/', substr(PHP_SELF, 1));
			$count = count($self);

			if (1 < $count) {
				$real_path = $self[$count - 2];

				if ($real_path == SELLER_PATH) {
					$str_len = 0 - (str_len(SELLER_PATH) + 1);
					$url = substr($GLOBALS['ecs']->url(), 0, $str_len);
				}
			}

			$urlip = get_ip_url($url);
			$url = $urlip . 'oss.php?act=upload';
			$Http = new Http();
			$post_data = array('bucket' => $bucket_info['bucket'], 'keyid' => $bucket_info['keyid'], 'keysecret' => $bucket_info['keysecret'], 'is_cname' => $bucket_info['is_cname'], 'endpoint' => $bucket_info['outside_site'], 'is_delimg' => $bucket_info['is_delimg'], 'object' => $file);
			$Http->doPost($url, $post_data);
		}
	}
}

function get_oss_del_file($file = array())
{
	if ($file) {
		if (!isset($GLOBALS['_CFG']['open_oss'])) {
			$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'open_oss\'';
			$is_oss = $GLOBALS['db']->getOne($sql, true);
		}
		else {
			$is_oss = $GLOBALS['_CFG']['open_oss'];
		}

		if ($is_oss == 1) {
			$bucket_info = get_bucket_info();
			$url = $GLOBALS['ecs']->url();
			$self = explode('/', substr(PHP_SELF, 1));
			$count = count($self);

			if (1 < $count) {
				$real_path = $self[$count - 2];

				if ($real_path == SELLER_PATH) {
					$str_len = 0 - (str_len(SELLER_PATH) + 1);
					$url = substr($GLOBALS['ecs']->url(), 0, $str_len);
				}
			}

			$urlip = get_ip_url($url);
			$url = $urlip . 'oss.php?act=del_file';
			$Http = new Http();
			$post_data = array('bucket' => $bucket_info['bucket'], 'keyid' => $bucket_info['keyid'], 'keysecret' => $bucket_info['keysecret'], 'is_cname' => $bucket_info['is_cname'], 'endpoint' => $bucket_info['outside_site'], 'object' => $file);
			$Http->doPost($url, $post_data);
		}
	}
}

function get_del_batch($checkboxs = '', $val_id = '', $select = '', $id = '', $table = '', $del = 0, $fileDir = '')
{
	$select = implode(',', $select);
	$is = true;

	if (!empty($checkboxs)) {
		$where = db_create_in($checkboxs, $id);
	}
	else if (!empty($val_id)) {
		$where = ' ' . $id . ' = \'' . $val_id . '\'';
	}
	else {
		$is = false;
	}

	if ($is) {
		$sql = 'SELECT ' . $select . '  FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where;
		$list = $GLOBALS['db']->getAll($sql);
		$arr = array();
		$select = explode(',', $select);
		$val = '';

		foreach ($list as $key => $row) {
			$arr[] = $row;

			foreach ($select as $ks => $rows) {
				if ($del == 1) {
					$val .= $row[$rows] . ',';
					@unlink(ROOT_PATH . $row[$rows]);
				}
				else {
					$val .= $fileDir . $row[$rows] . ',';
					@unlink(ROOT_PATH . $fileDir . $row[$rows]);
				}
			}

			$arr['list'] .= $val;
		}

		if ($arr) {
			$str_list = substr($arr['list'], 0, -1);
			$str_list = explode(',', $str_list);
		}
		else {
			$str_list = array();
		}

		if (!isset($GLOBALS['_CFG']['open_oss'])) {
			$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'open_oss\'';
			$is_oss = $GLOBALS['db']->getOne($sql, true);
		}
		else {
			$is_oss = $GLOBALS['_CFG']['open_oss'];
		}

		if ($is_oss == 1) {
			$bucket_info = get_bucket_info();
			$url = $GLOBALS['ecs']->url();
			$self = explode('/', substr(PHP_SELF, 1));
			$count = count($self);

			if (1 < $count) {
				$real_path = $self[$count - 2];

				if ($real_path == SELLER_PATH) {
					$str_len = 0 - (str_len(SELLER_PATH) + 1);
					$url = substr($GLOBALS['ecs']->url(), 0, $str_len);
				}
			}

			$urlip = get_ip_url($url);
			$url = $urlip . 'oss.php?act=del_file';
			$Http = new Http();
			$post_data = array('bucket' => $bucket_info['bucket'], 'keyid' => $bucket_info['keyid'], 'keysecret' => $bucket_info['keysecret'], 'is_cname' => $bucket_info['is_cname'], 'endpoint' => $bucket_info['outside_site'], 'object' => $str_list);
			$Http->doPost($url, $post_data);
		}
	}
}

function del_visual_templates($ip = array(), $suffix = '', $act = 'del_hometemplates', $seller_id = 0)
{
	if ($ip) {
		$where = '';

		if ($seller_id) {
			$where .= '&seller_id=' . $seller_id;
		}

		$Http = new Http();

		if (1 < count($ip)) {
			foreach ($ip as $key => $row) {
				$url = $GLOBALS['ecs']->http() . $row . '/' . 'ajax_dialog.php?act=' . $act . '&suffix=' . $suffix . $where;
				$Http->doGet($url);
			}
		}
		else {
			$url = $GLOBALS['ecs']->http() . $ip . '/' . 'ajax_dialog.php?act=' . $act . '&suffix=' . $suffix . $where;
			$Http->doGet($url);
		}
	}
}

function get_ip_url($url, $type = 0)
{
	$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'server_model\'';
	$server_model = $GLOBALS['db']->getOne($sql, true);

	if ($server_model) {
		$http = $GLOBALS['ecs']->http();
		$file = ROOT_PATH . DATA_DIR . '/urlip.txt';
		$file = file_get_contents($file);
		$file = trim($file);

		if ($type == 1) {
			return $http . $file;
		}
		else {
			return $http . $file . '/';
		}
	}
	else {
		return $url;
	}
}

function get_oss_list_file($file = array())
{
	if ($file) {
		if (!isset($GLOBALS['_CFG']['open_oss'])) {
			$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE code = \'open_oss\'';
			$is_oss = $GLOBALS['db']->getOne($sql, true);
		}
		else {
			$is_oss = $GLOBALS['_CFG']['open_oss'];
		}

		if ($is_oss == 1) {
			$bucket_info = get_bucket_info();
			$url = $GLOBALS['ecs']->url();
			$self = explode('/', substr(PHP_SELF, 1));
			$count = count($self);

			if (1 < $count) {
				$real_path = $self[$count - 2];

				if ($real_path == SELLER_PATH) {
					$str_len = 0 - (str_len(SELLER_PATH) + 1);
					$url = substr($GLOBALS['ecs']->url(), 0, $str_len);
				}
			}

			$urlip = get_ip_url($url);
			$url = $urlip . 'oss.php?act=list_file';
			$Http = new Http();
			$post_data = array('bucket' => $bucket_info['bucket'], 'keyid' => $bucket_info['keyid'], 'keysecret' => $bucket_info['keysecret'], 'is_cname' => $bucket_info['is_cname'], 'endpoint' => $bucket_info['outside_site'], 'object' => $file);
			return $Http->doPost($url, $post_data);
		}
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
