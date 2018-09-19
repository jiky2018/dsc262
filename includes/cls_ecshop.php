<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class ECS
{
	public $db_name = '';
	public $prefix = 'ecs_';

	public function __construct($db_name, $prefix)
	{
		$this->db_name = $db_name;
		$this->prefix = $prefix;
	}

	public function table($str)
	{
		return '`' . $this->db_name . '`.`' . $this->prefix . $str . '`';
	}

	public function compile_password($pass)
	{
		return md5($pass);
	}

	public function get_domain()
	{
		$protocol = $this->http();

		if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
			$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
		}
		else if (isset($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
		}
		else {
			if (isset($_SERVER['SERVER_PORT'])) {
				$port = ':' . $_SERVER['SERVER_PORT'];
				if (':80' == $port && 'http://' == $protocol || ':443' == $port && 'https://' == $protocol) {
					$port = '';
				}
			}
			else {
				$port = '';
			}

			if (isset($_SERVER['SERVER_NAME'])) {
				$host = $_SERVER['SERVER_NAME'] . $port;
			}
			else if (isset($_SERVER['SERVER_ADDR'])) {
				$host = $_SERVER['SERVER_ADDR'] . $port;
			}
		}

		return $protocol . $host;
	}

	public function url()
	{
		$curr = strpos(PHP_SELF, ADMIN_PATH . '/') !== false ? preg_replace('/(.*)(' . ADMIN_PATH . ')(\\/?)(.)*/i', '\\1', dirname(PHP_SELF)) : dirname(PHP_SELF);
		$root = str_replace('\\', '/', $curr);

		if (substr($root, -1) != '/') {
			$root .= '/';
		}

		return $this->get_domain() . $root;
	}

	public function seller_url($path = '')
	{
		if ($path == '') {
			$path = SELLER_PATH;
		}

		$curr = strpos(PHP_SELF, $path . '/') !== false ? preg_replace('/(.*)(' . $path . ')(\\/?)(.)*/i', '\\1', dirname(PHP_SELF)) : dirname(PHP_SELF);
		$root = str_replace('\\', '/', $curr);

		if (substr($root, -1) != '/') {
			$root .= '/';
		}

		return $this->get_domain() . $root;
	}

	public function stores_url()
	{
		$curr = strpos(PHP_SELF, STORES_PATH . '/') !== false ? preg_replace('/(.*)(' . STORES_PATH . ')(\\/?)(.)*/i', '\\1', dirname(PHP_SELF)) : dirname(PHP_SELF);
		$root = str_replace('\\', '/', $curr);

		if (substr($root, -1) != '/') {
			$root .= '/';
		}

		return $this->get_domain() . $root;
	}

	public function http()
	{
		if (isset($_SERVER['HTTPS'])) {
			return isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off' ? 'https://' : 'http://';
		}
		else {
			if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && !empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
				$proto_http = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);

				if (strpos($proto_http, 'https') !== false) {
					$proto_http = 'https://';
				}
				else {
					$proto_http = 'http://';
				}

				return $proto_http;
			}
			else {
				if (isset($GLOBALS['_CFG']['site_domain'])) {
					$site_domain = $GLOBALS['_CFG']['site_domain'];
				}
				else {
					$site_domain = $this->get_sms_type('site_domain');
				}

				if ($site_domain && strpos($site_domain, 'http') !== false) {
					$site = explode(':', $site_domain);

					if ($site[0] == 'https') {
						$domain = 'https://';
					}
					else {
						$domain = 'http://';
					}

					return $domain;
				}
				else {
					return 'http://';
				}
			}
		}
	}

	public function data_dir($sid = 0)
	{
		if (empty($sid)) {
			$s = 'data';
		}
		else {
			$s = 'user_files/';
			$s .= ceil($sid / 3000) . '/';
			$s .= $sid % 3000;
		}

		return $s;
	}

	public function image_dir($sid = 0)
	{
		if (empty($sid)) {
			$s = 'images';
		}
		else {
			$s = 'user_files/';
			$s .= ceil($sid / 3000) . '/';
			$s .= $sid % 3000 . '/';
			$s .= 'images';
		}

		return $s;
	}

	public function ali_yu($msg, $is_array = 0)
	{
		if (isset($GLOBALS['_CFG']['sms_type'])) {
			$sms_type = $GLOBALS['_CFG']['sms_type'];
		}
		else {
			$sms_type = $this->get_sms_type();
		}

		if ($sms_type == 2) {
			return $this->ali_yuntongxin($msg, $is_array);
		}
		else if ($sms_type == 1) {
			return $this->ali_dayu($msg, $is_array);
		}
		else if ($sms_type == 3) {
			return $this->dscsms($msg, $is_array);
		}
	}

	public function dscsms($msg, $is_array)
	{
		include ROOT_PATH . 'plugins/ecmoban/dscsms.php';
		$dsc_appkey = $GLOBALS['_CFG']['dsc_appkey'];
		$dsc_appsecret = $GLOBALS['_CFG']['dsc_appsecret'];
		$sms = new dscsms($dsc_appkey, $dsc_appsecret);

		if ($is_array == 1) {
			$arr = array();

			foreach ($msg as $key => $row) {
				if ($row) {
					$url = $sms->getUrl();
					$data = $sms->composeData($row);
					$arr[$key]['resp'] = $sms->send($url, $data);
				}
			}

			return $arr;
		}
		else {
			$url = $sms->getUrl();
			$data = $sms->composeData($msg);
			$resp = $sms->send($url, $data);
			return $resp;
		}
	}

	public function ali_dayu($msg, $is_array)
	{
		include ROOT_PATH . 'plugins/aliyunyu/TopSdk.php';
		$c = new TopClient();
		$c->appkey = $GLOBALS['_CFG']['ali_appkey'];
		$c->secretKey = $GLOBALS['_CFG']['ali_secretkey'];
		$c->format = 'json';
		$req = new AlibabaAliqinFcSmsNumSendRequest();

		if ($is_array == 1) {
			$arr = array();

			foreach ($msg as $key => $row) {
				if ($row) {
					$phones = $row['mobile_phone'];
					$req->setSmsType($row['SmsType']);
					$req->setSmsFreeSignName($row['SignName']);
					$req->setSmsParam($row['smsParams']);
					$req->setRecNum(''.$phones);
					$req->setSmsTemplateCode($row['SmsCdoe']);
					$arr[$key]['resp'] = $c->execute($req);
				}
			}

			return $arr;
		}
		else {
			$phones = $msg['mobile_phone'];
			$req->setSmsType($msg['SmsType']);
			$req->setSmsFreeSignName($msg['SignName']);
			$req->setSmsParam($msg['smsParams']);
			$req->setRecNum(''.$phones);
			$req->setSmsTemplateCode($msg['SmsCdoe']);
			$resp = $c->execute($req);
			return $resp;
		}
	}

	public function ali_yuntongxin($msg, $is_array)
	{
		include ROOT_PATH . 'plugins/aliyunxin/aliyunxin.php';
		$accessKeyId = $GLOBALS['_CFG']['access_key_id'];
		$accessKeySecret = $GLOBALS['_CFG']['access_key_secret'];
		$ali = new aliyunxin($accessKeyId, $accessKeySecret);

		if ($is_array == 1) {
			$arr = array();

			foreach ($msg as $key => $row) {
				if ($row) {
					$url = $ali->composeUrl($row);
					$arr[$key]['resp'] = $ali->send($url);
				}
			}

			return $arr;
		}
		else {
			$url = $ali->composeUrl($msg);
			$resp = $ali->send($url);
			return $resp;
		}
	}

	public function page_array($page_size = 1, $page = 1, $array = array(), $order = 0, $filter_arr = array())
	{
		$arr = array();
		$pagedata = array();

		if ($array) {
			global $countpage;
			$start = ($page - 1) * $page_size;

			if ($order == 1) {
				$array = array_reverse($array);
			}

			$totals = count($array);
			$countpage = ceil($totals / $page_size);
			$pagedata = array_slice($array, $start, $page_size);
			$filter = array('page' => $page, 'page_size' => $page_size, 'record_count' => $totals, 'page_count' => $countpage);

			if ($filter_arr) {
				$filter = array_merge($filter, $filter_arr);
			}

			$arr = array('list' => $pagedata, 'filter' => $filter, 'page_count' => $countpage, 'record_count' => $totals);
		}

		return $arr;
	}

	public function get_explode_filter($str_arr, $type = 0)
	{
		switch ($type) {
		case 1:
			$str = 1;
			break;

		default:
			$str = $this->return_intval($str_arr);
			break;
		}

		return $str;
	}

	public function return_intval($str)
	{
		$new_str = '';

		if ($str) {
			$str = explode(',', $str);

			foreach ($str as $key => $row) {
				$row = intval($row);

				if ($row) {
					$new_str .= $row . ',';
				}
			}
		}

		$new_str = substr($new_str, 0, -1);
		return $new_str;
	}

	public function preg_is_letter($str)
	{
		$preg = '[^A-Za-z]+';

		if (preg_match('/' . $preg . '/', $str)) {
			return false;
		}
		else {
			return true;
		}
	}

	public function get_select_find_in_set($is_db = 0, $select_id, $select_array = array(), $where = '', $table = '', $id = '', $replace = '')
	{
		if ($replace) {
			$replace = 'REPLACE (' . $id . ',\'' . $replace . '\',\',\')';
		}
		else {
			$replace = $id;
		}

		if ($select_array && is_array($select_array)) {
			$select = implode(',', $select_array);
		}
		else {
			$select = '*';
		}

		$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE find_in_set(\'' . $select_id . '\', ' . $replace . ') ' . $where);

		if ($is_db == 1) {
			return $GLOBALS['db']->getAll($sql);
		}
		else if ($is_db == 2) {
			return $GLOBALS['db']->getRow($sql);
		}
		else {
			return $GLOBALS['db']->getOne($sql, true);
		}
	}

	public function get_del_find_in_set($select_id, $where = '', $table = '', $id = '', $replace = '')
	{
		if ($replace) {
			$replace = 'REPLACE (' . $id . ',\'' . $replace . '\',\',\')';
		}
		else {
			$replace = $id;
		}

		$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE find_in_set(\'' . $select_id . '\', ' . $replace . ') ' . $where);
		$GLOBALS['db']->query($sql);
	}

	public function get_dir_file_count($dir)
	{
		$count = sizeof(scandir($dir)) - 2;
		return $count;
	}

	public function byte_format($size, $dec = 2)
	{
		$a = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
		$pos = 0;

		while (1024 <= $size) {
			$size /= 1024;
			$pos++;
		}

		return round($size, $dec) . ' ' . $a[$pos];
	}

	public function get_file_list($dir)
	{
		$arr['all_size'] = 0;
		$arr['all_size_name'] = '';

		if (file_exists($dir)) {
			foreach (scandir($dir) as $v) {
				if (!is_dir($v)) {
					$size = filesize($dir . '/' . $v);
					$size = $this->byte_format($size);
					$arr['all_size'] += $size;
				}
			}
		}

		if (1024 * 1024 < $arr['all_size']) {
			$arr['all_size'] = round($arr['all_size'] / 1024 / 1024, 2);
			$arr['all_size_name'] = $arr['all_size'] . ' ' . 'G';
		}
		else {
			$arr['all_size'] = round($arr['all_size'] / 1024, 2);
			$arr['all_size_name'] = $arr['all_size'] . ' ' . 'MB';
		}

		return $arr;
	}

	public function get_sms_type($code = 'sms_type')
	{
		$sql = ' SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . (' WHERE code = \'' . $code . '\' ');
		return $GLOBALS['db']->getOne($sql, true);
	}

	public function get_filter_str_array($str, $type = 0)
	{
		$str_arr = array('order_id');

		if (!empty($str)) {
			$ex_rec = !is_array($str) ? explode(',', $str) : $str;
			$ex_rec = array_values($ex_rec);
			$preg = '/<script[\\s\\S]*?<\\/script>/i';

			foreach ($ex_rec as $key => $row) {
				if ($type == 1) {
					$row = addslashes($row);
					$lower_row = strtolower($row);
					$lower_row = !empty($lower_row) ? preg_replace($preg, '', stripslashes($lower_row)) : '';

					if (strpos($lower_row, '</script>') !== false) {
						$row = compile_str($row);
					}
					else {
						if (strpos($lower_row, 'updatexml') !== false || strpos($lower_row, 'extractvalue') !== false || strpos($lower_row, 'floor') !== false) {
							$row = '';
						}
						else {
							if (strpos($lower_row, ' or ') !== false && !in_array($lower_row, $str_arr)) {
								$row = '';
							}
							else {
								if (strpos($lower_row, ' hex ') !== false && !in_array($lower_row, $str_arr)) {
									$row = '';
								}
								else {
									if (strpos($lower_row, ' unhex ') !== false && !in_array($lower_row, $str_arr)) {
										$row = '';
									}
									else {
										if (strpos($lower_row, ' chr ') !== false && !in_array($lower_row, $str_arr)) {
											$row = '';
										}
									}
								}
							}
						}
					}

					$ex_rec[$key] = $row;
				}
				else {
					$ex_rec[$key] = intval($row);
				}
			}

			if (!is_array($str)) {
				$str = implode(',', $ex_rec);
			}
			else {
				$str = $ex_rec;
			}
		}

		return $str;
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

define('APPNAME', 'ECMOBAN_DSC');
define('VERSION', 'v2.6.1');
define('RELEASE', '20180517');

?>
