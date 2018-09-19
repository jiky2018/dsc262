<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Libraries;

class Image
{
	public $error_no = 0;
	public $error_msg = '';
	public $images_dir = 'images';
	public $data_dir = 'data';
	public $bgcolor = '';
	public $type_maping = array(1 => 'image/gif', 2 => 'image/jpeg', 3 => 'image/png');

	public function __construct($bgcolor = '')
	{
		if ($bgcolor) {
			$this->bgcolor = $bgcolor;
		}
		else {
			$this->bgcolor = '#FFFFFF';
		}
	}

	public function upload_image($upload, $dir = '', $img_name = '')
	{
		if (empty($dir)) {
			$dir = date('Ym');
			$dir = dirname(ROOT_PATH) . '/' . $this->images_dir . '/' . $dir . '/';
		}
		else {
			$dir = dirname(ROOT_PATH) . '/' . $this->data_dir . '/' . $dir . '/';

			if ($img_name) {
				$img_name = $dir . $img_name;
			}
		}

		if (!file_exists($dir)) {
			if (!make_dir($dir)) {
				$this->error_msg = sprintf(L('directory_readonly'), $dir);
				$this->error_no = ERR_DIRECTORY_READONLY;
				return false;
			}
		}

		if (empty($img_name)) {
			$img_name = $this->unique_name($dir);
			$img_name = $dir . $img_name . $this->get_filetype($upload['name']);
		}

		if (!$this->check_img_type($upload['type'])) {
			$this->error_msg = L('invalid_upload_image_type');
			$this->error_no = ERR_INVALID_IMAGE_TYPE;
			return false;
		}

		$allow_file_types = '|GIF|JPG|JEPG|PNG|BMP|SWF|';

		if (!check_file_type($upload['tmp_name'], $img_name, $allow_file_types)) {
			$this->error_msg = L('invalid_upload_image_type');
			$this->error_no = ERR_INVALID_IMAGE_TYPE;
			return false;
		}

		if ($this->move_file($upload, $img_name)) {
			return str_replace(ROOT_PATH, '', $img_name);
		}
		else {
			$this->error_msg = sprintf(L('upload_failure'), $upload['name']);
			$this->error_no = ERR_UPLOAD_FAILURE;
			return false;
		}
	}

	public function make_thumb($img, $thumb_width = 0, $thumb_height = 0, $path = '', $bgcolor = '')
	{
		$gd = self::gd_version();

		if ($gd == 0) {
			$this->error_msg = L('missing_gd');
			return false;
		}

		if (($thumb_width == 0) && ($thumb_height == 0)) {
			return str_replace(ROOT_PATH, '', str_replace('\\', '/', realpath($img)));
		}

		$org_info = @getimagesize($img);

		if (!$org_info) {
			$this->error_msg = sprintf(L('missing_orgin_image'), $img);
			$this->error_no = ERR_IMAGE_NOT_EXISTS;
			return false;
		}

		if (!$this->check_img_function($org_info[2])) {
			$this->error_msg = sprintf(L('nonsupport_type'), $this->type_maping[$org_info[2]]);
			$this->error_no = ERR_NO_GD;
			return false;
		}

		$img_org = $this->img_resource($img, $org_info[2]);
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
			$bgcolor = $this->bgcolor;
		}

		$bgcolor = trim($bgcolor, '#');
		sscanf($bgcolor, '%2x%2x%2x', $red, $green, $blue);
		$clr = imagecolorallocate($img_thumb, $red, $green, $blue);
		imagefilledrectangle($img_thumb, 0, 0, $thumb_width, $thumb_height, $clr);

		if (($org_info[1] / $thumb_height) < ($org_info[0] / $thumb_width)) {
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
			$dir = ROOT_PATH . $this->data_dir . '/attached/' . date('Ym') . '/';
		}
		else {
			$dir = $path;
		}

		if (!file_exists($dir)) {
			if (!make_dir($dir)) {
				$this->error_msg = sprintf(L('directory_readonly'), $dir);
				$this->error_no = ERR_DIRECTORY_READONLY;
				return false;
			}
		}

		$filename = $this->unique_name($dir);

		if (function_exists('imagejpeg')) {
			$filename .= '.jpg';
			imagejpeg($img_thumb, $dir . $filename);
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
			$this->error_msg = L('creating_failure');
			$this->error_no = ERR_NO_GD;
			return false;
		}

		imagedestroy($img_thumb);
		imagedestroy($img_org);

		if (file_exists($dir . $filename)) {
			return str_replace(ROOT_PATH, '', $dir) . $filename;
		}
		else {
			$this->error_msg = L('writting_failure');
			$this->error_no = ERR_DIRECTORY_READONLY;
			return false;
		}
	}

	public function add_watermark($filename, $target_file = '', $watermark = '', $watermark_place = '', $watermark_alpha = 0.65000000000000002)
	{
		$gd = self::gd_version();

		if ($gd == 0) {
			$this->error_msg = L('missing_gd');
			$this->error_no = ERR_NO_GD;
			return false;
		}

		if (!file_exists($filename) || !is_file($filename)) {
			$this->error_msg = sprintf(L('missing_orgin_image'), $filename);
			$this->error_no = ERR_IMAGE_NOT_EXISTS;
			return false;
		}

		if (($watermark_place == 0) || empty($watermark)) {
			return str_replace(ROOT_PATH, '', str_replace('\\', '/', realpath($filename)));
		}

		if (!$this->validate_image($watermark)) {
			return false;
		}

		$watermark_info = @getimagesize($watermark);
		$watermark_handle = $this->img_resource($watermark, $watermark_info[2]);

		if (!$watermark_handle) {
			$this->error_msg = sprintf(L('create_watermark_res'), $this->type_maping[$watermark_info[2]]);
			$this->error_no = ERR_INVALID_IMAGE;
			return false;
		}

		$source_info = @getimagesize($filename);
		$source_handle = $this->img_resource($filename, $source_info[2]);

		if (!$source_handle) {
			$this->error_msg = sprintf(L('create_origin_image_res'), $this->type_maping[$source_info[2]]);
			$this->error_no = ERR_INVALID_IMAGE;
			return false;
		}

		switch ($watermark_place) {
		case '1':
			$x = 0;
			$y = 0;
			break;

		case '2':
			$x = $source_info[0] - $watermark_info[0];
			$y = 0;
			break;

		case '4':
			$x = 0;
			$y = $source_info[1] - $watermark_info[1];
			break;

		case '5':
			$x = $source_info[0] - $watermark_info[0];
			$y = $source_info[1] - $watermark_info[1];
			break;

		default:
			$x = ($source_info[0] / 2) - ($watermark_info[0] / 2);
			$y = ($source_info[1] / 2) - ($watermark_info[1] / 2);
		}

		if (strpos(strtolower($watermark_info['mime']), 'png') !== false) {
			imageAlphaBlending($watermark_handle, true);
			imagecopy($source_handle, $watermark_handle, $x, $y, 0, 0, $watermark_info[0], $watermark_info[1]);
		}
		else {
			imagecopymerge($source_handle, $watermark_handle, $x, $y, 0, 0, $watermark_info[0], $watermark_info[1], $watermark_alpha);
		}

		$target = (empty($target_file) ? $filename : $target_file);

		switch ($source_info[2]) {
		case 'image/gif':
		case 1:
			imagegif($source_handle, $target);
			break;

		case 'image/pjpeg':
		case 'image/jpeg':
		case 2:
			imagejpeg($source_handle, $target);
			break;

		case 'image/x-png':
		case 'image/png':
		case 3:
			imagepng($source_handle, $target);
			break;

		default:
			$this->error_msg = L('creating_failure');
			$this->error_no = ERR_NO_GD;
			return false;
		}

		imagedestroy($source_handle);
		$path = realpath($target);

		if ($path) {
			return str_replace(ROOT_PATH, '', str_replace('\\', '/', $path));
		}
		else {
			$this->error_msg = L('writting_failure');
			$this->error_no = ERR_DIRECTORY_READONLY;
			return false;
		}
	}

	public function validate_image($path)
	{
		if (empty($path)) {
			$this->error_msg = L('empty_watermark');
			$this->error_no = ERR_INVALID_PARAM;
			return false;
		}

		if (!file_exists($path)) {
			$this->error_msg = sprintf(L('missing_watermark'), $path);
			$this->error_no = ERR_IMAGE_NOT_EXISTS;
			return false;
		}

		$image_info = @getimagesize($path);

		if (!$image_info) {
			$this->error_msg = sprintf(L('invalid_image_type'), $path);
			$this->error_no = ERR_INVALID_IMAGE;
			return false;
		}

		if (!$this->check_img_function($image_info[2])) {
			$this->error_msg = sprintf(L('nonsupport_type'), $this->type_maping[$image_info[2]]);
			$this->error_no = ERR_NO_GD;
			return false;
		}

		return true;
	}

	public function error_msg()
	{
		return $this->error_msg;
	}

	public function check_img_type($img_type)
	{
		return ($img_type == 'image/pjpeg') || ($img_type == 'image/x-png') || ($img_type == 'image/png') || ($img_type == 'image/gif') || ($img_type == 'image/jpeg');
	}

	public function check_img_function($img_type)
	{
		switch ($img_type) {
		case 'image/gif':
		case 1:
			return function_exists('imagecreatefromgif');
			break;

		case 'image/pjpeg':
		case 'image/jpeg':
		case 2:
			return function_exists('imagecreatefromjpeg');
			break;

		case 'image/x-png':
		case 'image/png':
		case 3:
			return function_exists('imagecreatefrompng');
			break;

		default:
			return false;
		}
	}

	public function random_filename()
	{
		$str = '';

		for ($i = 0; $i < 9; $i++) {
			$str .= mt_rand(0, 9);
		}

		return gmtime() . $str;
	}

	public function unique_name($dir)
	{
		$filename = '';

		while (empty($filename)) {
			$filename = image::random_filename();
			if (file_exists($dir . $filename . '.jpg') || file_exists($dir . $filename . '.gif') || file_exists($dir . $filename . '.png')) {
				$filename = '';
			}
		}

		return $filename;
	}

	public function get_filetype($path)
	{
		$pos = strrpos($path, '.');

		if ($pos !== false) {
			return substr($path, $pos);
		}
		else {
			return '';
		}
	}

	public function img_resource($img_file, $mime_type)
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

	static public function gd_version()
	{
		static $version = -1;

		if (0 <= $version) {
			return $version;
		}

		if (!extension_loaded('gd')) {
			$version = 0;
		}
		else if (function_exists('gd_info')) {
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

		return $version;
	}

	public function move_file($upload, $target)
	{
		if (isset($upload['error']) && (0 < $upload['error'])) {
			return false;
		}

		if (!move_upload_file($upload['tmp_name'], $target)) {
			return false;
		}

		return true;
	}
}


?>
