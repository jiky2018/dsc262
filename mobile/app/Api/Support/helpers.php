<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function C($key)
{
	$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');

	if (strpos($key, '.')) {
		list($item, $key) = explode('.', $key, 2);
	}

	return $shopconfig->getShopConfigByCode($key);
}

function get_bucket_info()
{
	$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
	$res = $shopconfig->getOssConfig();

	if ($res) {
		$regional = substr($res['regional'], 0, 2);
		if ($regional == 'us' || $regional == 'ap') {
			$res['outside_site'] = 'https://' . $res['bucket'] . '.oss-' . $res['regional'] . '.aliyuncs.com';
			$res['inside_site'] = 'https://' . $res['bucket'] . '.oss-' . $res['regional'] . '-internal.aliyuncs.com';
		}
		else {
			$res['outside_site'] = 'https://' . $res['bucket'] . '.oss-cn-' . $res['regional'] . '.aliyuncs.com';
			$res['inside_site'] = 'https://' . $res['bucket'] . '.oss-cn-' . $res['regional'] . '-internal.aliyuncs.com';
		}

		$res['endpoint'] = str_replace('http://', 'https://', $res['endpoint']);
	}

	return $res;
}

function dump($var)
{
	echo '<pre>';
	var_dump($var);
	echo '</pre>';
}

function get_del_str_comma($str = '')
{
	if ($str && is_array($str)) {
		return $str;
	}
	else {
		if ($str) {
			$str = str_replace(',,', ',', $str);
			$str1 = substr($str, 0, 1);
			$str2 = substr($str, str_len($str) - 1);
			if ($str1 === ',' && $str2 !== ',') {
				$str = substr($str, 1);
			}
			else {
				if ($str1 !== ',' && $str2 === ',') {
					$str = substr($str, 0, -1);
				}
				else {
					if ($str1 === ',' && $str2 === ',') {
						$str = substr($str, 1);
						$str = substr($str, 0, -1);
					}
				}
			}
		}

		return $str;
	}
}

function str_len($str)
{
	$length = strlen(preg_replace('/[\\x00-\\x7F]/', '', $str));

	if ($length) {
		return strlen($str) - $length + intval($length / 3) * 2;
	}
	else {
		return strlen($str);
	}
}

function get_three_to_two_array($list = array())
{
	$new_list = array();

	if ($list) {
		foreach ($list as $lkey => $lrow) {
			foreach ($lrow as $ckey => $crow) {
				$new_list[] = $crow;
			}
		}
	}

	return $new_list;
}

function get_array_sort($arr, $keys, $type = 'asc')
{
	$new_array = array();
	if (is_array($arr) && !empty($arr)) {
		$keysvalue = $new_array = array();

		foreach ($arr as $k => $v) {
			$keysvalue[$k] = $v[$keys];
		}

		if ($type == 'asc') {
			asort($keysvalue);
		}
		else {
			arsort($keysvalue);
		}

		reset($keysvalue);

		foreach ($keysvalue as $k => $v) {
			$new_array[$k] = $arr[$k];
		}
	}

	return $new_array;
}

function get_order_transport($goods_list, $consignee = array(), $shipping_id = 0, $shipping_code = '')
{
	$sprice = 0;
	$type_left = array();
	$freight = 0;
	if ($goods_list && $shipping_code != 'cac') {
		$area_shipping = get_goods_area_shipping($goods_list, $shipping_id, $shipping_code, $consignee);

		foreach ($goods_list as $key => $row) {
			if ($row['freight'] && $row['is_shipping'] == 0) {
				if ($row['freight'] == 1) {
					$sprice += $row['shipping_fee'] * $row['goods_number'];
				}
				else {
					$trow = app('App\\Repositories\\Goods\\GoodsRepository')->getGoodsTransport($row['tid']);
					if (isset($trow['freight_type']) && $trow['freight_type'] == 0) {
						$transport = array('top_area_id', 'area_id', 'tid', 'ru_id', 'sprice');
						$transport_where = ' AND ru_id = \'' . $row['ru_id'] . '\' AND tid = \'' . $row['tid'] . '\'';
						$goods_transport = app('App\\Repositories\\Shop\\ShopRepository')->get_select_find_in_set(2, $consignee['city'], $transport, $transport_where, 'goods_transport_extend', 'area_id');

						if ($goods_transport) {
							$ship_transport = array('tid', 'ru_id', 'shipping_fee');
							$ship_transport_where = ' AND ru_id = \'' . $row['ru_id'] . '\' AND tid = \'' . $row['tid'] . '\'';
							$goods_ship_transport = app('App\\Repositories\\Shop\\ShopRepository')->get_select_find_in_set(2, $shipping_id, $ship_transport, $ship_transport_where, 'goods_transport_express', 'shipping_id');
						}

						$goods_transport['sprice'] = isset($goods_transport['sprice']) ? $goods_transport['sprice'] : 0;
						$goods_ship_transport['shipping_fee'] = isset($goods_ship_transport['shipping_fee']) ? $goods_ship_transport['shipping_fee'] : 0;

						if ($trow['type'] == 1) {
							$sprice += $goods_transport['sprice'] * $row['goods_number'] + $goods_ship_transport['shipping_fee'] * $row['goods_number'];
						}
						else {
							$type_left[$row['tid']] = $goods_transport['sprice'] + $goods_ship_transport['shipping_fee'];
						}
					}
				}
			}
			else {
				$freight += 1;
			}
		}

		$unified_total = get_cart_unified_freight_total($type_left);
		$arr = array('sprice' => $area_shipping['shipping_fee'] + $sprice + $unified_total, 'freight' => $freight);
	}
	else {
		$arr = array('sprice' => 0, 'freight' => $freight);
	}

	return $arr;
}

function get_goods_area_shipping($goods_list, $shipping_id = 0, $shipping_code = '', $consignee)
{
	$tid_arr1 = array();
	$prefix = \Illuminate\Support\Facades\Config::get('database.connections.mysql.prefix');

	foreach ($goods_list as $key => $row) {
		$tid_arr1[$row['tid']][$key] = $row;
	}

	$tid_arr2 = array();

	foreach ($tid_arr1 as $key => $row) {
		$row = !empty($row) ? array_values($row) : $row;
		$tid_arr2[$key]['weight'] = 0;
		$tid_arr2[$key]['number'] = 0;
		$tid_arr2[$key]['amount'] = 0;

		foreach ($row as $gkey => $grow) {
			$tid_arr2[$key]['number'] += $grow['goods_number'];
			$tid_arr2[$key]['amount'] += $grow['goods_price'] * $grow['goods_number'];
		}
	}

	if (empty($shipping_id)) {
		$select = array('shipping_code' => $shipping_code);
		$shipping_info = shipping_info($select, array('shipping_id'));
		$shipping_id = $shipping_info['shipping_id'];
	}

	if (empty($shipping_code)) {
		$shipping_info = shipping_info($shipping_id, array('shipping_code'));
		$shipping_code = $shipping_info['shipping_code'];
	}

	$shipping_fee = 0;
	$region = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district'], $consignee['street']);

	foreach ($tid_arr2 as $key => $row) {
		$trow = get_goods_transport($key);
		if ($trow && $trow['freight_type'] == 1) {
			$sql = 'SELECT * FROM ' . $prefix . 'goods_transport_tpl WHERE tid = \'' . $key . '\' AND shipping_id = \'' . $shipping_id . '\'' . ' AND ((FIND_IN_SET(\'' . $region[1] . '\', region_id)) OR (FIND_IN_SET(\'' . $region[2] . '\', region_id) OR FIND_IN_SET(\'' . $region[3] . '\', region_id) OR FIND_IN_SET(\'' . $region[4] . '\', region_id)))' . ' LIMIT 1';
			$transport_tpl = \Illuminate\Support\Facades\DB::select($sql);
			$transport_tpl = isset($transport_tpl[0]) ? json_decode(json_encode($transport_tpl[0]), 1) : array();
			$configure = !empty($transport_tpl) && $transport_tpl['configure'] ? unserialize($transport_tpl['configure']) : '';

			if (!empty($configure)) {
				$tid_arr2[$key]['shipping_fee'] = shipping_fee($shipping_code, $configure, $row['weight'], $row['amount'], $row['number']);
			}
			else {
				$tid_arr2[$key]['shipping_fee'] = 0;
			}

			$shipping_fee += $tid_arr2[$key]['shipping_fee'];
		}
	}

	$arr = array('tid_list' => $tid_arr2, 'shipping_fee' => $shipping_fee);
	return $arr;
}

function get_goods_transport($tid = 0)
{
	$prefix = \Illuminate\Support\Facades\Config::get('database.connections.mysql.prefix');
	$sql = 'SELECT * FROM ' . $prefix . ('goods_transport WHERE tid = \'' . $tid . '\' LIMIT 1');
	$transport = \Illuminate\Support\Facades\DB::select($sql);
	$transport = !empty($transport) ? get_object_vars($transport[0]) : '';
	return $transport;
}

function get_cart_unified_freight_total($total)
{
	$sprice = 0;

	if ($total) {
		foreach ($total as $key => $row) {
			$sprice += $row;
		}
	}

	return $sprice;
}

function shipping_fee($shipping_code, $shipping_config, $goods_weight, $goods_amount, $goods_number = '')
{
	if (!is_array($shipping_config)) {
		$shipping_config = unserialize($shipping_config);
	}

	$filename = base_path() . '/app/Plugins/shipping/' . $shipping_code . '.php';

	if (file_exists($filename)) {
		include_once $filename;
		$obj = new $shipping_code($shipping_config);
		return $obj->calculate($goods_weight, $goods_amount, $goods_number);
	}
	else {
		return 0;
	}
}

function unserialize_config($cfg)
{
	if (is_string($cfg) && ($arr = unserialize($cfg)) !== false) {
		$config = array();

		foreach ($arr as $key => $val) {
			$config[$val['name']] = $val['value'];
		}

		return $config;
	}
	else {
		return false;
	}
}

function apiLog($word = '', $type = 'api')
{
	$word = is_array($word) ? var_export($word, true) : $word;
	$suffix = '_' . substr(md5(__DIR__), 0, 6);
	$fp = fopen(base_path('storage/logs/' . $type . $suffix . '.log'), 'a');
	flock($fp, LOCK_EX);
	fwrite($fp, '执行日期：' . date('Y-m-d H:i:s', time()) . "\n" . $word . "\n");
	flock($fp, LOCK_UN);
	fclose($fp);
}

if (!function_exists('get_image_path')) {
	function get_image_path($image = '', $path = '')
	{
		$rootPath = app('request')->root();
		$rootPath = dirname(dirname($rootPath)) . '/';
		$no_picture = $rootPath . 'mobile/public/img/no_image.jpg';

		if (strtolower(substr($image, 0, 4)) == 'http') {
			$url = $image;
		}
		else {
			$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
			$open_oss = $shopconfig->getShopConfigByCode('open_oss');

			if ($open_oss == 1) {
				$bucket_info = get_bucket_info();
				$bucket_info['endpoint'] = empty($bucket_info['endpoint']) ? $bucket_info['outside_site'] : $bucket_info['endpoint'];
				$url = empty($image) ? $no_picture : rtrim($bucket_info['endpoint'], '/') . '/' . $path . $image;
			}
			else {
				$path = empty($path) ? '' : rtrim($path, '/') . '/';
				$img_path = $path . $image;

				if (empty($image)) {
					$url = $no_picture;
				}
				else {
					$url = $rootPath . $img_path;
				}
			}
		}

		return $url;
	}
}

if (!function_exists('price_format')) {
	function price_format($price, $change_price = true)
	{
		$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
		$priceFormat = $shopconfig->getShopConfigByCode('price_format');
		$currencyFormat = strip_tags($shopconfig->getShopConfigByCode('currency_format'));

		if ($price === '') {
			$price = 0;
		}

		if ($change_price && defined('ECS_ADMIN') === false) {
			switch ($priceFormat) {
			case 0:
				$price = number_format($price, 2, '.', '');
				break;

			case 1:
				$price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/', '\\1\\2\\3', number_format($price, 2, '.', ''));

				if (substr($price, -1) == '.') {
					$price = substr($price, 0, -1);
				}

				break;

			case 2:
				$price = substr(number_format($price, 2, '.', ''), 0, -1);
				break;

			case 3:
				$price = intval($price);
				break;

			case 4:
				$price = number_format($price, 1, '.', '');
				break;

			case 5:
				$price = round($price);
				break;
			}
		}
		else {
			@$price = number_format($price, 2, '.', '');
		}

		return sprintf($currencyFormat, $price);
	}
}

if (!function_exists('make_semiangle')) {
	function make_semiangle($str)
	{
		$arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4', '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9', 'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E', 'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J', 'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O', 'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T', 'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y', 'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd', 'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i', 'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n', 'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's', 'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x', 'ｙ' => 'y', 'ｚ' => 'z', '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[', '】' => ']', '〖' => '[', '〗' => ']', '“' => '[', '”' => ']', '‘' => '[', '’' => ']', '｛' => '{', '｝' => '}', '《' => '<', '》' => '>', '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-', '：' => ':', '。' => '.', '、' => ',', '，' => '.', '、' => '.', '；' => ',', '？' => '?', '！' => '!', '…' => '-', '‖' => '|', '”' => '"', '’' => '`', '‘' => '`', '｜' => '|', '〃' => '"', '　' => ' ');
		return strtr($str, $arr);
	}
}

if (!function_exists('local_mktime()')) {
	function local_mktime($hour = NULL, $minute = NULL, $second = NULL, $month = NULL, $day = NULL, $year = NULL)
	{
		$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
		$timezone = $shopconfig->getShopConfigByCode('timezone');
		$time = mktime($hour, $minute, $second, $month, $day, $year) - $timezone * 3600;
		return $time;
	}
}

if (!function_exists('local_getdate()')) {
	function local_getdate($timestamp = NULL)
	{
		$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
		$timezone = $shopconfig->getShopConfigByCode('timezone');

		if ($timestamp === NULL) {
			$timestamp = time();
		}

		$gmt = $timestamp - date('Z');
		$local_time = $gmt + $timezone * 3600;
		return getdate($local_time);
	}
}

if (!function_exists('local_gettime()')) {
	function local_gettime($timestamp = NULL)
	{
		$tmp = local_getdate($timestamp);
		return $tmp[0];
	}
}

if (!function_exists('gmtime()')) {
	function gmtime()
	{
		return time() - date('Z');
	}
}

if (!function_exists('local_date()')) {
	function local_date($format, $time = NULL)
	{
		$shopconfig = app('App\\Repositories\\ShopConfig\\ShopConfigRepository');
		$timezone = $shopconfig->getShopConfigByCode('timezone');

		if ($time === NULL) {
			$time = gmtime();
		}
		else if ($time <= 0) {
			return '';
		}

		$time += $timezone * 3600;
		return date($format, $time);
	}
}

?>
