<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace app\func;

class common
{
	static private $format = 'json';
	static private $page_size = 10;
	static private $page = 1;
	static private $charset = 'utf-8';
	static private $result;
	static private $msg;
	static private $error;
	static private $code;
	static private $message;
	static private $allowOutputType = array('xml' => 'application/xml', 'json' => 'application/json', 'html' => 'text/html');

	public function __construct($data = array())
	{
		self::common($data);
	}

	static public function common($data = array())
	{
		self::$format = isset($data['format']) ? $data['format'] : 'josn';
		self::$page_size = isset($data['page_size']) ? $data['page_size'] : 10;
		self::$page = isset($data['page']) ? $data['page'] : 1;
		self::$msg = isset($data['msg']) ? $data['msg'] : '';
		self::$result = isset($data['result']) ? $data['result'] : 'success';
		self::$error = isset($data['error']) ? $data['error'] : 0;
		self::$code = isset($data['code']) ? $data['code'] : 10000;
		self::$message = isset($data['message']) ? $data['message'] : 'success';
	}

	static public function data_back($info = array(), $arr_type = 0)
	{
		if ($arr_type == 1) {
			$list = self::page_array(self::$page_size, self::$page, $info);
			$info = $list;
		}

		if ($arr_type == 2) {
			$data_arr = array('code' => self::$code, 'message' => self::$message);
		}
		else {
			$data_arr = array('result' => self::$result, 'error' => self::$error, 'msg' => self::$msg);
		}

		if ($info) {
			$data_arr['info'] = $info;
		}

		$data_arr = self::to_utf8_iconv($data_arr);

		if (self::$format == 'xml') {
			if (isset(self::$allowOutputType[self::$format])) {
				header('Content-Type: ' . self::$allowOutputType[self::$format] . '; charset=' . self::$charset);
			}

			return self::xml_encode($data_arr);
		}
		else {
			if (isset(self::$allowOutputType[self::$format])) {
				header('Content-Type: ' . self::$allowOutputType[self::$format] . '; charset=' . self::$charset);
			}

			return json_encode($data_arr);
		}
	}

	static public function xml_encode($data, $root = 'dsc', $item = 'item', $attr = '', $id = 'id', $encoding = 'utf-8')
	{
		if (is_array($attr)) {
			$_attr = array();

			foreach ($attr as $key => $value) {
				$_attr[] = $key . '="' . $value . '"';
			}

			$attr = implode(' ', $_attr);
		}

		$attr = trim($attr);
		$attr = empty($attr) ? '' : ' ' . $attr;
		$xml = '<?xml version="1.0" encoding="' . $encoding . '"?>';
		$xml .= '<' . $root . $attr . '>';
		$xml .= self::data_to_xml($data, $item, $id);
		$xml .= '</' . $root . '>';
		return $xml;
	}

	static public function data_to_xml($data, $item = 'item', $id = 'id')
	{
		$xml = $attr = '';

		foreach ($data as $key => $val) {
			if (is_numeric($key)) {
				$id && ($attr = ' ' . $id . '="' . $key . '"');
				$key = $item;
			}

			$xml .= '<' . $key . $attr . '>';
			$xml .= is_array($val) || is_object($val) ? self::data_to_xml($val, $item, $id) : $val;
			$xml .= '</' . $key . '>';
		}

		return $xml;
	}

	static public function to_utf8_iconv($str)
	{
		if (EC_CHARSET != 'utf-8') {
			if (is_string($str)) {
				return ecs_iconv(EC_CHARSET, 'utf-8', $str);
			}
			else if (is_array($str)) {
				foreach ($str as $key => $value) {
					$str[$key] = to_utf8_iconv($value);
				}

				return $str;
			}
			else if (is_object($str)) {
				foreach ($str as $key => $value) {
					$str->$key = to_utf8_iconv($value);
				}

				return $str;
			}
			else {
				return $str;
			}
		}

		return $str;
	}

	static public function page_array($page_size = 1, $page = 1, $array = array(), $order = 0)
	{
		$arr = array();
		$pagedata = array();

		if ($array) {
			global $countpage;
			$start = ($page - 1) * $page_size;

			if ($order == 1) {
				$array = array_reverse($array);
			}

			if (isset($array['record_count'])) {
				$totals = $array['record_count'];
				$countpage = ceil($totals / $page_size);
				$pagedata = $array['list'];
			}
			else {
				$totals = count($array);
				$countpage = ceil($totals / $page_size);
				$pagedata = array_slice($array, $start, $page_size);
			}

			$filter = array('page' => $page, 'page_size' => $page_size, 'record_count' => $totals, 'page_count' => $countpage);
			$arr = array('list' => $pagedata, 'filter' => $filter, 'page_count' => $countpage, 'record_count' => $totals);
		}

		return $arr;
	}

	static public function get_reference_only($table, $where = 1, $select = '', $type = 0)
	{
		if (!empty($select) && is_array($select)) {
			$select = implode(',', $select);
		}
		else {
			$select = '*';
		}

		$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . (' WHERE ' . $where);

		if ($type == 1) {
			return $GLOBALS['db']->getRow($sql);
		}
		else {
			return $GLOBALS['db']->getOne($sql);
		}
	}

	static public function reformat_image_name($type = 0, $goods_id = 0, $source_img = '', $position = '')
	{
		$rand_name = time() . sprintf('%03d', mt_rand(1, 999));
		$img_ext = substr($source_img, strrpos($source_img, '.'));
		$dir = 'images';

		if (defined('IMAGE_DIR')) {
			$dir = IMAGE_DIR;
		}

		$sub_dir = date('Ym', time());

		if (!self::make_dir(ROOT_PATH . $dir . '/' . $sub_dir)) {
			return false;
		}

		if (!self::make_dir(ROOT_PATH . $dir . '/' . $sub_dir . '/source_img')) {
			return false;
		}

		if (!self::make_dir(ROOT_PATH . $dir . '/' . $sub_dir . '/goods_img')) {
			return false;
		}

		if (!self::make_dir(ROOT_PATH . $dir . '/' . $sub_dir . '/thumb_img')) {
			return false;
		}

		switch ($type) {
		case 'goods':
			$img_name = $goods_id . '_G_' . $rand_name;
			break;

		case 'goods_thumb':
			$img_name = $goods_id . '_thumb_G_' . $rand_name;
			break;

		case 'gallery':
			$img_name = $goods_id . '_P_' . $rand_name;
			break;

		case 'gallery_thumb':
			$img_name = $goods_id . '_thumb_P_' . $rand_name;
			break;
		}

		if (strpos($source_img, 'temp') !== false) {
			$ex_img = explode('temp', $source_img);
			$source_img = 'temp' . $ex_img[1];
		}
		else if (strpos($source_img, ROOT_PATH) !== false) {
			$source_img = !empty($source_img) ? str_replace(ROOT_PATH, '', $source_img) : '';
		}

		if ($position == 'source') {
			if (self::move_image_file(ROOT_PATH . $source_img, ROOT_PATH . $dir . '/' . $sub_dir . '/source_img/' . $img_name . $img_ext)) {
				return $dir . '/' . $sub_dir . '/source_img/' . $img_name . $img_ext;
			}
		}
		else if ($position == 'thumb') {
			if (self::move_image_file(ROOT_PATH . $source_img, ROOT_PATH . $dir . '/' . $sub_dir . '/thumb_img/' . $img_name . $img_ext)) {
				return $dir . '/' . $sub_dir . '/thumb_img/' . $img_name . $img_ext;
			}
		}
		else if (self::move_image_file(ROOT_PATH . $source_img, ROOT_PATH . $dir . '/' . $sub_dir . '/goods_img/' . $img_name . $img_ext)) {
			return $dir . '/' . $sub_dir . '/goods_img/' . $img_name . $img_ext;
		}

		return false;
	}

	static public function move_image_file($source, $dest)
	{
		if (@copy($source, $dest)) {
			@unlink($source);
			return true;
		}

		return false;
	}

	static public function get_width_to_height($path, $image_width = 0, $image_height = 0)
	{
		$width = 0;
		$height = 0;
		$img = @getimagesize($path);

		if ($img) {
			$width = $img[0];
			$height = $img[1];

			if ($width < $image_width) {
				$image_width = $width;
			}

			if ($height < $image_height) {
				$image_height = $height;
			}

			$arr = array('width' => $width, 'height' => $height, 'image_width' => $image_width, 'image_height' => $image_height);
		}

		return $arr;
	}

	static public function img_resource($img_file, $mime_type)
	{
		switch ($mime_type) {
		case 1:
		case 'image/gif':
			$res = imagecreatefromgif($img_file);
			break;

		case 2:
		case 'image/pjpeg':
		case 'image/jpeg':
			$res = imagecreatefromjpeg($img_file);
			break;

		case 3:
		case 'image/x-png':
		case 'image/png':
			$res = imagecreatefrompng($img_file);
			break;

		default:
			return false;
		}

		return $res;
	}

	static public function make_dir($folder)
	{
		$reval = false;

		if (!file_exists($folder)) {
			@umask(0);
			preg_match_all('/([^\\/]*)\\/?/i', $folder, $atmp);
			$base = $atmp[0][0] == '/' ? '/' : '';

			foreach ($atmp[1] as $val) {
				if ('' != $val) {
					$base .= $val;
					if ('..' == $val || '.' == $val) {
						$base .= '/';
						continue;
					}
				}
				else {
					continue;
				}

				$base .= '/';

				if (!file_exists($base)) {
					if (@mkdir(rtrim($base, '/'), 511)) {
						@chmod($base, 511);
						$reval = true;
					}
				}
			}
		}
		else {
			$reval = is_dir($folder);
		}

		clearstatcache();
		return $reval;
	}

	static public function make_thumb($img, $thumb_width = 0, $thumb_height = 0, $path = '', $bgcolor = '', $filename = '')
	{
		$upload_type = 0;
		if ($img && is_array($img)) {
			$upload_type = $img['type'];
			$img = isset($img['img']) ? $img['img'] : '';
		}

		$gd = self::gd_version();

		if ($gd == 0) {
			return false;
		}

		if ($thumb_width == 0 && $thumb_height == 0) {
			return str_replace(ROOT_PATH, '', str_replace('\\', '/', realpath($img)));
		}

		$org_info = @getimagesize($img);

		if (!$org_info) {
			return false;
		}

		if (!self::check_img_function($org_info[2])) {
			return false;
		}

		$img_org = self::img_resource($img, $org_info[2]);
		$scale_org = $org_info[0] / $org_info[1];

		if ($thumb_width == 0) {
			$thumb_width = $thumb_height * $scale_org;
		}

		if ($thumb_height == 0) {
			$thumb_height = $thumb_width / $scale_org;
		}

		if ($gd == 2) {
			$img_thumb = imagecreatetruecolor($thumb_width, $thumb_height);
		}
		else {
			$img_thumb = imagecreate($thumb_width, $thumb_height);
		}

		if (empty($bgcolor)) {
			$bgcolor = '#FFFFFF';
		}

		$bgcolor = trim($bgcolor, '#');
		sscanf($bgcolor, '%2x%2x%2x', $red, $green, $blue);
		$clr = imagecolorallocate($img_thumb, $red, $green, $blue);
		imagefilledrectangle($img_thumb, 0, 0, $thumb_width, $thumb_height, $clr);

		if ($org_info[1] / $thumb_height < $org_info[0] / $thumb_width) {
			$lessen_width = $thumb_width;
			$lessen_height = $thumb_width / $scale_org;
		}
		else {
			$lessen_width = $thumb_height * $scale_org;
			$lessen_height = $thumb_height;
		}

		$dst_x = ($thumb_width - $lessen_width) / 2;
		$dst_y = ($thumb_height - $lessen_height) / 2;

		if ($gd == 2) {
			imagecopyresampled($img_thumb, $img_org, $dst_x, $dst_y, 0, 0, $lessen_width, $lessen_height, $org_info[0], $org_info[1]);
		}
		else {
			imagecopyresized($img_thumb, $img_org, $dst_x, $dst_y, 0, 0, $lessen_width, $lessen_height, $org_info[0], $org_info[1]);
		}

		if (empty($path)) {
			$admin_dir = date('Ym');
			$admin_dir = ROOT_PATH . IMAGE_DIR . '/' . $admin_dir . '/' . 'admin_0';

			if (!file_exists($admin_dir)) {
				self::make_dir($admin_dir);
			}

			$dir = ROOT_PATH . IMAGE_DIR . '/' . date('Ym') . '/' . 'admin_0/';
		}
		else {
			$dir = $path;
		}

		if (!file_exists($dir)) {
			if (!self::make_dir($dir)) {
				return false;
			}
		}

		if ($filename == '') {
			$filename = self::unique_name($dir);

			if (function_exists('imagejpeg')) {
				$filename .= '.jpg';
				imagejpeg($img_thumb, $dir . $filename, 90);
			}
			else if (function_exists('imagegif')) {
				$filename .= '.gif';
				imagegif($img_thumb, $dir . $filename);
			}
			else if (function_exists('imagepng')) {
				$filename .= '.png';
				imagepng($img_thumb, $dir . $filename);
			}
			else {
				return false;
			}
		}
		else {
			imagepng($img_thumb, $dir . $filename);
		}

		imagedestroy($img_thumb);
		imagedestroy($img_org);

		if (file_exists($dir . $filename)) {
			if ($upload_type) {
				return $dir . $filename;
			}
			else {
				return str_replace(ROOT_PATH, '', $dir) . $filename;
			}
		}
		else {
			return false;
		}
	}

	static public function unique_name($dir)
	{
		$filename = '';

		while (empty($filename)) {
			$filename = self::random_filename();
			if (file_exists($dir . $filename . '.jpg') || file_exists($dir . $filename . '.gif') || file_exists($dir . $filename . '.png')) {
				$filename = '';
			}
		}

		return $filename;
	}

	static public function random_filename()
	{
		$str = '';

		for ($i = 0; $i < 9; $i++) {
			$str .= mt_rand(0, 9);
		}

		return time() . $str;
	}

	static public function gd_version()
	{
		static $version = -1;

		if (0 <= $version) {
			return $version;
		}

		if (!extension_loaded('gd')) {
			$version = 0;
		}
		else if ('4.3' <= PHP_VERSION) {
			if (function_exists('gd_info')) {
				$ver_info = gd_info();
				preg_match('/\\d/', $ver_info['GD Version'], $match);
				$version = $match[0];
			}
			else if (function_exists('imagecreatetruecolor')) {
				$version = 2;
			}
			else if (function_exists('imagecreate')) {
				$version = 1;
			}
		}
		else if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
			$version = 1;
		}
		else {
			ob_start();
			phpinfo(8);
			$info = ob_get_contents();
			ob_end_clean();
			$info = stristr($info, 'gd version');
			preg_match('/\\d/', $info, $match);
			$version = $match[0];
		}

		return $version;
	}

	static public function check_img_function($img_type)
	{
		switch ($img_type) {
		case 'image/gif':
		case 1:
			if ('4.3' <= PHP_VERSION) {
				return function_exists('imagecreatefromgif');
			}
			else {
				return 0 < (imagetypes() & IMG_GIF);
			}

			break;

		case 'image/pjpeg':
		case 'image/jpeg':
		case 2:
			if ('4.3' <= PHP_VERSION) {
				return function_exists('imagecreatefromjpeg');
			}
			else {
				return 0 < (imagetypes() & IMG_JPG);
			}

			break;

		case 'image/x-png':
		case 'image/png':
		case 3:
			if ('4.3' <= PHP_VERSION) {
				return function_exists('imagecreatefrompng');
			}
			else {
				return 0 < (imagetypes() & IMG_PNG);
			}

			break;

		default:
			return false;
		}
	}

	static public function get_http_basename($url = '', $path = '', $goods_lib = '')
	{
		$return_content = self::doGet($url);
		$url = basename($url);

		if ($goods_lib) {
			$filename = $path;
		}
		else {
			$filename = $path . '/' . $url;
		}

		if (file_put_contents($filename, $return_content)) {
			return $filename;
		}
		else {
			return false;
		}
	}

	static public function doGet($url, $timeout = 5, $header = '')
	{
		if (empty($url) || empty($timeout)) {
			return false;
		}

		if (!preg_match('/^(http|https)/is', $url)) {
			$url = 'http://' . $url;
		}

		$code = self::getSupport();

		switch ($code) {
		case 1:
			return self::curlGet($url, $timeout, $header);
			break;

		case 2:
			return self::socketGet($url, $timeout, $header);
			break;

		case 3:
			return self::phpGet($url, $timeout, $header);
			break;

		default:
			return false;
		}
	}

	static public function phpGet($url, $timeout = 5, $header = '')
	{
		$header = empty($header) ? self::defaultHeader() : $header;
		$opts = array(
			'http' => array('protocol_version' => '1.0', 'method' => 'GET', 'timeout' => $timeout, 'header' => $header)
			);
		$context = stream_context_create($opts);
		return @file_get_contents($url, false, $context);
	}

	static public function defaultHeader()
	{
		$header = "User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12\r\n";
		$header .= "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
		$header .= "Accept-language: zh-cn,zh;q=0.5\r\n";
		$header .= "Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n";
		return $header;
	}

	static public function getSupport()
	{
		if (isset(self::$way) && in_array(self::$way, array(1, 2, 3))) {
			return self::$way;
		}

		if (function_exists('curl_init')) {
			return 1;
		}
		else if (function_exists('fsockopen')) {
			return 2;
		}
		else if (function_exists('file_get_contents')) {
			return 3;
		}
		else {
			return 0;
		}
	}

	static public function curlGet($url, $timeout = 5, $header = '')
	{
		$header = empty($header) ? self::defaultHeader() : $header;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	static public function socketGet($url, $timeout = 5, $header = '')
	{
		$header = empty($header) ? self::defaultHeader() : $header;
		$url2 = parse_url($url);
		$url2['path'] = isset($url2['path']) ? $url2['path'] : '/';
		$url2['port'] = isset($url2['port']) ? $url2['port'] : 80;
		$url2['query'] = isset($url2['query']) ? '?' . $url2['query'] : '';
		$host_ip = @gethostbyname($url2['host']);

		if (($fsock = fsockopen($host_ip, $url2['port'], $errno, $errstr, $timeout)) < 0) {
			return false;
		}

		$request = $url2['path'] . $url2['query'];
		$in = 'GET ' . $request . " HTTP/1.0\r\n";

		if (false === strpos($header, 'Host:')) {
			$in .= 'Host: ' . $url2['host'] . "\r\n";
		}

		$in .= $header;
		$in .= "Connection: Close\r\n\r\n";

		if (!@fwrite($fsock, $in, strlen($in))) {
			@fclose($fsock);
			return false;
		}

		return self::GetHttpContent($fsock);
	}

	static private function GetHttpContent($fsock = NULL)
	{
		$out = null;

		while ($buff = @fgets($fsock, 2048)) {
			$out .= $buff;
		}

		fclose($fsock);
		$pos = strpos($out, "\r\n\r\n");
		$head = substr($out, 0, $pos);
		$status = substr($head, 0, strpos($head, "\r\n"));
		$body = substr($out, $pos + 4, strlen($out) - ($pos + 4));

		if (preg_match('/^HTTP\\/\\d\\.\\d\\s([\\d]+)\\s.*$/', $status, $matches)) {
			if (intval($matches[1]) / 100 == 2) {
				return $body;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	static private function generate_goods_sn($goods_id)
	{
		$goods_sn = $GLOBALS['_CFG']['sn_prefix'] . str_repeat('0', 6 - strlen($goods_id)) . $goods_id;
		$sql = 'SELECT goods_sn FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE goods_sn LIKE \'' . mysql_like_quote($goods_sn) . ('%\' AND goods_id <> \'' . $goods_id . '\' ') . ' ORDER BY LENGTH(goods_sn) DESC';
		$sn_list = $GLOBALS['db']->getCol($sql);

		if (in_array($goods_sn, $sn_list)) {
			$max = pow(10, strlen($sn_list[0]) - strlen($goods_sn) + 1) - 1;
			$new_sn = $goods_sn . mt_rand(0, $max);

			while (in_array($new_sn, $sn_list)) {
				$new_sn = $goods_sn . mt_rand(0, $max);
			}

			$goods_sn = $new_sn;
		}

		return $goods_sn;
	}

	static private function integral_to_give($order)
	{
		$leftJoin = '';

		if ($order['extension_code'] == 'group_buy') {
			$group_buy = self::group_buy_info(intval($order['extension_id']));
			$sql = 'SELECT ext_info FROM' . $GLOBALS['ecs']->table('goods_activity') . 'WHERE act_id = \'' . $order['extension_id'] . '\'';
			$ext_info = $GLOBALS['dn']->getOne($sql);
			$ext_info = unserialize($ext_info);
			return array('custom_points' => $ext_info['gift_integral'], 'rank_points' => $order['goods_amount']);
		}
		else {
			$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_goods') . ' as wg on g.goods_id = wg.goods_id and wg.region_id = og.warehouse_id ';
			$leftJoin .= ' left join ' . $GLOBALS['ecs']->table('warehouse_area_goods') . ' as wag on g.goods_id = wag.goods_id and wag.region_id = og.area_id ';
			$give_integral = 'IF(og.ru_id > 0, (SELECT sg.give_integral / 100 FROM ' . $GLOBALS['ecs']->table('merchants_grade') . ' AS mg, ' . $GLOBALS['ecs']->table('seller_grade') . ' AS sg ' . ' WHERE mg.grade_id = sg.id AND mg.ru_id = og.ru_id LIMIT 1), 1)';
			$rank_integral = 'IF(og.ru_id > 0, (SELECT sg.rank_integral / 100 FROM ' . $GLOBALS['ecs']->table('merchants_grade') . ' AS mg, ' . $GLOBALS['ecs']->table('seller_grade') . ' AS sg ' . ' WHERE mg.grade_id = sg.id AND mg.ru_id = og.ru_id LIMIT 1), 1)';
			$sql = 'SELECT SUM(og.goods_number * IF(IF(g.model_price < 1, g.give_integral, IF(g.model_price < 2, wg.give_integral, wag.give_integral)) > -1, IF(g.model_price < 1, g.give_integral, IF(g.model_price < 2, wg.give_integral, wag.give_integral)), og.goods_price * ' . $give_integral . ')) AS custom_points,' . (' SUM(og.goods_number * IF(IF(g.model_price < 1, g.rank_integral, IF(g.model_price < 2, wg.rank_integral, wag.rank_integral)) > -1, IF(g.model_price < 1, g.rank_integral, IF(g.model_price < 2, wg.rank_integral, wag.rank_integral)), og.goods_price * ' . $rank_integral . ')) AS rank_points ') . ' FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON og.goods_id = g.goods_id ' . $leftJoin . 'WHERE og.order_id = \'' . $order['order_id'] . '\' ' . 'AND og.goods_id > 0 ' . 'AND og.parent_id = 0 ' . 'AND og.is_gift = 0 AND og.extension_code != \'package_buy\'';
			$row = $GLOBALS['db']->getRow($sql);

			if ($row) {
				$row['custom_points'] = intval($row['custom_points']);
				$row['rank_points'] = intval($row['rank_points']);
			}

			return $row;
		}
	}

	static private function log_account_change($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '', $change_type = ACT_OTHER)
	{
		$is_go = true;
		$is_user_money = 0;
		$is_pay_points = 0;
		$deposit_fee = 0;
		if ($is_go && ($user_money || $frozen_money || $rank_points || $pay_points)) {
			$account_log = array('user_id' => $user_id, 'user_money' => $user_money, 'frozen_money' => $frozen_money, 'rank_points' => $rank_points, 'pay_points' => $pay_points, 'change_time' => gmtime(), 'change_desc' => $change_desc, 'change_type' => $change_type, 'deposit_fee' => $deposit_fee);
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('account_log'), $account_log, 'INSERT');
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . (' SET user_money = user_money + (\'' . $user_money . '\'+ \'' . $deposit_fee . '\'),') . (' frozen_money = frozen_money + (\'' . $frozen_money . '\'),') . (' rank_points = rank_points + (\'' . $rank_points . '\'),') . (' pay_points = pay_points + (\'' . $pay_points . '\')') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
			$GLOBALS['db']->query($sql);
			$sql = 'SELECT rank_points FROM ' . $GLOBALS['ecs']->table('users') . (' WHERE user_id = \'' . $user_id . '\'');
			$user_rank_points = $GLOBALS['db']->getOne($sql, true);
			$sql = 'SELECT rank_id, discount FROM ' . $GLOBALS['ecs']->table('user_rank') . ' WHERE special_rank = 0 AND min_points <= \'' . $user_rank_points . '\' AND max_points > \'' . $user_rank_points . '\' LIMIT 1';
			$rank_row = $GLOBALS['db']->getRow($sql);

			if ($rank_row) {
				$rank_row['discount'] = $rank_row['discount'] / 100;
			}
			else {
				$rank_row['discount'] = 1;
				$rank_row['rank_id'] = 0;
			}

			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . 'SET user_rank = \'' . $rank_row['rank_id'] . ('\' WHERE user_id = \'' . $user_id . '\'');
			$GLOBALS['db']->query($sql);
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('sessions') . 'SET user_rank = \'' . $rank_row['rank_id'] . '\', discount= \'' . $rank_row['discount'] . ('\' WHERE userid = \'' . $user_id . '\' AND adminid = 0');
			$GLOBALS['db']->query($sql);
		}
	}

	static private function send_order_bonus($order_id)
	{
		$bonus_list = self::order_bonus($order_id);

		if ($bonus_list) {
			$sql = 'SELECT u.user_id, u.user_name, u.email ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o, ' . $GLOBALS['ecs']->table('users') . ' AS u ' . ('WHERE o.order_id = \'' . $order_id . '\' ') . 'AND o.user_id = u.user_id ';
			$user = $GLOBALS['db']->getRow($sql);
			$count = 0;
			$money = '';

			foreach ($bonus_list as $bonus) {
				if ($bonus['number']) {
					$count = 1;
					$bonus['number'] = 1;
				}

				$money .= self::price_format($bonus['type_money']) . ' [' . $bonus['number'] . '], ';
				$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('user_bonus') . ' (bonus_type_id, user_id) ' . ('VALUES(\'' . $bonus['type_id'] . '\', \'' . $user['user_id'] . '\')');
				$GLOBALS['db']->qiery($sql);
			}
		}

		return true;
	}

	static private function order_amount($order_id, $include_gift = true)
	{
		$sql = 'SELECT SUM(goods_price * goods_number) ' . 'FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE order_id = \'' . $order_id . '\'');

		if (!$include_gift) {
			$sql .= ' AND is_gift = 0';
		}

		return floatval($GLOBALS['db']->getOne($sql));
	}

	static private function order_bonus($order_id)
	{
		$day = getdate();
		$today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
		$sql = 'SELECT b.type_id, b.type_money, SUM(o.goods_number) AS number ' . 'FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS o, ' . $GLOBALS['ecs']->table('goods') . ' AS g, ' . $GLOBALS['ecs']->table('bonus_type') . ' AS b ' . (' WHERE o.order_id = \'' . $order_id . '\' ') . ' AND o.is_gift = 0 ' . ' AND o.goods_id = g.goods_id ' . ' AND g.bonus_type_id = b.type_id ' . ' AND b.send_type = \'' . SEND_BY_GOODS . '\' ' . (' AND b.send_start_date <= \'' . $today . '\' ') . (' AND b.send_end_date >= \'' . $today . '\' ') . ' GROUP BY b.type_id ';
		$list = $GLOBALS['db']->getAll($sql);
		$amount = self::order_amount($order_id, false);
		$sql = 'SELECT oi.add_time, og.ru_id ' . ' FROM ' . $GLOBALS['ecs']->table('order_info') . 'AS oi,' . $GLOBALS['ecs']->table('order_goods') . 'AS og' . (' WHERE oi.order_id = og.order_id AND oi.order_id = \'' . $order_id . '\' LIMIT 1');
		$order = $GLOBALS['db']->getRow($sql);
		$order_time = $order['add_time'];
		$ru_id = $order['ru_id'];
		$sql = 'SELECT type_id, type_name, type_money, IFNULL(FLOOR(\'' . $amount . '\' / min_amount), 1) AS number ' . 'FROM ' . $GLOBALS['ecs']->table('bonus_type') . 'WHERE send_type = \'' . SEND_BY_ORDER . '\' ' . ('AND send_start_date <= \'' . $order_time . '\' ') . ('AND send_end_date >= \'' . $order_time . '\' AND user_id = \'' . $ru_id . '\' ');
		$list = array_merge($list, $GLOBALS['db']->getAll($sql));
		return $list;
	}

	static private function price_format($price = 0, $change_price = true)
	{
		if (empty($price)) {
			$price = 0;
		}

		if ($change_price && defined('ECS_ADMIN') === false) {
			switch ($GLOBALS['_CFG']['price_format']) {
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

		return sprintf($GLOBALS['_CFG']['currency_format'], $price);
	}

	static private function send_order_coupons($order)
	{
		$time = gmtime();
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('coupons') . ('WHERE review_status = 3 AND cou_type = 2 AND ' . $time . '<cou_end_time ');
		$coupons_buy_info = $GLOBALS['db']->getAll($sql);

		foreach ($coupons_buy_info as $k => $v) {
			$coupons_buy_info[$k]['uc_sn'] = $time . rand(10, 99);
		}

		$user_rank = self::get_one_user_rank($order['user_id']);

		foreach ($coupons_buy_info as $k => $v) {
			$cou_ok_user = !empty($v['cou_ok_user']) ? explode(',', $v['cou_ok_user']) : '';

			if ($cou_ok_user) {
				if (!in_array($user_rank, $cou_ok_user)) {
					continue;
				}
			}
			else {
				continue;
			}

			$num = $GLOBALS['db']->getOne(' SELECT COUNT(uc_id) FROM ' . $GLOBALS['ecs']->table('coupons_user') . ' WHERE cou_id=\'' . $v['cou_id'] . '\'');

			if ($v['cou_total'] <= $num) {
				continue;
			}

			$cou_user_num = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('coupons_user') . ' WHERE user_id=\'' . $order['user_id'] . '\' AND cou_id =\'' . $v['cou_id'] . '\' AND is_use = 0');

			if ($cou_user_num < $v['cou_user_num']) {
				$sql = ' SELECT GROUP_CONCAT(og.goods_id) AS goods_id, GROUP_CONCAT(g.cat_id) AS cat_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og,' . $GLOBALS['ecs']->table('goods') . ' AS g' . ' WHERE og.goods_id = g.goods_id AND order_id=\'' . $order['order_id'] . '\'';
				$goods = $GLOBALS['db']->getRow($sql);
				$goods_ids = !empty($goods['goods_id']) ? array_unique(explode(',', $goods['goods_id'])) : array();
				$goods_cats = !empty($goods['cat_id']) ? array_unique(explode(',', $goods['cat_id'])) : array();
				$flag = false;

				if ($v['cou_get_man'] <= $order['goods_amount']) {
					if ($v['cou_ok_goods']) {
						$cou_ok_goods = explode(',', $v['cou_ok_goods']);

						if ($goods_ids) {
							foreach ($goods_ids as $m => $n) {
								if (in_array($n, $cou_ok_goods)) {
									$flag = true;
									break;
								}
							}
						}
					}
					else if ($v['cou_ok_cat']) {
						$cou_ok_cat = self::get_cou_children($v['cou_ok_cat']);
						$cou_ok_cat = explode(',', $cou_ok_cat);

						if ($goods_cats) {
							foreach ($goods_cats as $m => $n) {
								if (in_array($n, $cou_ok_cat)) {
									$flag = true;
									break;
								}
							}
						}
					}
					else {
						$flag = true;
					}

					if ($flag) {
						$GLOBALS['db']->query('INSERT INTO ' . $GLOBALS['ecs']->table('coupons_user') . ' (`user_id`,`cou_id`,`uc_sn`) VALUES (\'' . $order['user_id'] . '\',\'' . $v['cou_id'] . '\',\'' . $v['uc_sn'] . '\') ');
					}
				}
			}
		}
	}

	static private function get_cou_children($cat = '')
	{
		$catlist = '';

		if ($cat) {
			$cat = explode(',', $cat);

			foreach ($cat as $key => $row) {
				$catlist .= self::get_children($row, 2) . ',';
			}

			$catlist = self::get_del_str_comma($catlist, 0, -1);
			$catlist = array_unique(explode(',', $catlist));
			$catlist = implode(',', $catlist);
			$cat = implode(',', $cat);
			$catlist = !empty($catlist) ? $catlist . ',' . $cat : $cat;
			$catlist = self::get_del_str_comma($catlist);
		}

		return $catlist;
	}

	static private function get_del_str_comma($str = '', $delstr = ',')
	{
		if ($str && is_array($str)) {
			return $str;
		}
		else {
			if ($str) {
				$str = str_replace($delstr . $delstr, $delstr, $str);
				$str1 = substr($str, 0, 1);
				$str2 = substr($str, str_len($str) - 1);
				if ($str1 === $delstr && $str2 !== $delstr) {
					$str = substr($str, 1);
				}
				else {
					if ($str1 !== $delstr && $str2 === $delstr) {
						$str = substr($str, 0, -1);
					}
					else {
						if ($str1 === $delstr && $str2 === $delstr) {
							$str = substr($str, 1);
							$str = substr($str, 0, -1);
						}
					}
				}
			}

			return $str;
		}
	}

	static private function get_one_user_rank($user_id)
	{
		if (!$user_id) {
			return false;
		}

		$time = date('Y-m-d');
		$sql = 'SELECT u.user_money,u.email, u.pay_points, u.user_rank, u.rank_points, ' . ' IFNULL(b.type_money, 0) AS user_bonus, u.last_login, u.last_ip' . ' FROM ' . $GLOBALS['ecs']->table('users') . ' AS u ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('user_bonus') . ' AS ub' . ' ON ub.user_id = u.user_id AND ub.used_time = 0 ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('bonus_type') . ' AS b' . (' ON b.type_id = ub.bonus_type_id AND b.use_start_date <= \'' . $time . '\' AND b.use_end_date >= \'' . $time . '\' ') . (' WHERE u.user_id = \'' . $user_id . '\'');

		if ($row = $GLOBALS['db']->getRow($sql)) {
			if (0 < $row['user_rank']) {
				$sql = 'SELECT special_rank from ' . $GLOBALS['ecs']->table('user_rank') . ('where rank_id=\'' . $row['user_rank'] . '\'');
				if ($GLOBALS['db']->getOne($sql) === '0' || $GLOBALS['db']->getOne($sql) === null) {
					$sql = 'update ' . $GLOBALS['ecs']->table('users') . ('set user_rank=\'0\' where user_id=\'' . $user_id . '\'');
					$GLOBALS['db']->query($sql);
					$row['user_rank'] = 0;
				}
			}

			if ($row['user_rank'] == 0) {
				$sql = 'SELECT rank_id, discount FROM ' . $GLOBALS['ecs']->table('user_rank') . ' WHERE special_rank = \'0\' AND min_points <= \'' . intval($row['rank_points']) . '\' AND max_points > \'' . intval($row['rank_points']) . '\' LIMIT 1';

				if ($row = $GLOBALS['db']->getRow($sql)) {
					return $row['rank_id'];
				}
				else {
					return false;
				}
			}
			else {
				$sql = 'SELECT rank_id, discount FROM ' . $GLOBALS['ecs']->table('user_rank') . (' WHERE rank_id = \'' . $row['user_rank'] . '\' LIMIT 1');

				if ($row = $GLOBALS['db']->getRow($sql)) {
					return $row['rank_id'];
				}
				else {
					return false;
				}
			}
		}
	}

	static private function get_shipping_info($code)
	{
		$shipping_info = array();
		$control_table = array(
			array('cloud_code' => 'sf', 'name' => '顺风快递', 'code' => 'sf_express'),
			array('cloud_code' => 'sto', 'name' => '申通', 'code' => 'sto_express'),
			array('cloud_code' => 'yt', 'name' => '圆通', 'code' => 'yto'),
			array('cloud_code' => 'yd', 'name' => '韵达', 'code' => 'yunda'),
			array('cloud_code' => 'tt', 'name' => '天天', 'code' => 'tiantian'),
			array('cloud_code' => 'ems', 'name' => 'EMS', 'code' => 'ems'),
			array('cloud_code' => 'zto', 'name' => '中通', 'code' => 'zto'),
			array('cloud_code' => 'qf', 'name' => '全峰', 'code' => 'quanfeng'),
			array('cloud_code' => 'db', 'name' => '德邦', 'code' => ''),
			array('cloud_code' => 'yousu', 'name' => '优速', 'code' => ''),
			array('cloud_code' => 'ht', 'name' => '汇通', 'code' => 'huitong'),
			array('cloud_code' => 'gt', 'name' => '国通', 'code' => ''),
			array('cloud_code' => 'zjs', 'name' => '宅急送', 'code' => 'zjs'),
			array('cloud_code' => 'kuaijie', 'name' => '快捷', 'code' => ''),
			array('cloud_code' => 'yzgn', 'name' => '邮政国内', 'code' => 'ems'),
			array('cloud_code' => 'XloboEX', 'name' => '贝海快递', 'code' => ''),
			array('cloud_code' => '8dt', 'name' => '八达通', 'code' => ''),
			array('cloud_code' => 'ANE', 'name' => '安能', 'code' => ''),
			array('cloud_code' => 'jdwl', 'name' => '京东物流', 'code' => ''),
			array('cloud_code' => 'EWE', 'name' => 'EWE国际快递', 'code' => '')
			);

		if ($code) {
			foreach ($control_table as $k => $v) {
				if ($v['cloud_code'] == $code && $v['code']) {
					$sql = 'SELECT * FROM' . $GLOBALS['ecs']->table('shipping') . 'WHERE shipping_code = \'' . $v['code'] . '\' LIMIT 1';
					$shipping_info = $GLOBALS['db']->getRow($sql);
					break;
				}
			}
		}

		return $shipping_info;
	}

	public static function __callStatic($method, $arguments)
	{
		return call_user_func_array(array(self, $method), $arguments);
	}
}


?>
