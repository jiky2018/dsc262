<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function mc_explode_str($str, $exp = ',', $chk = '')
{
	$str_arr = explode($exp, $str);

	if ($chk == 'user') {
		$user_arr = array();

		foreach ($str_arr as $key => $value) {
			if (3 <= strlen($value)) {
				$user_arr[] = $value;
			}
		}

		$str_arr = $user_arr;
	}
	else if ($chk == 'int') {
		$id_arr = array();

		foreach ($str_arr as $key => $value) {
			if (is_numeric($value)) {
				$id_arr[] = $value;
			}
		}

		$str_arr = $id_arr;
	}

	return $str_arr;
}

function mc_read_txt($file)
{
	$pathfile = $file;

	if (!file_exists($pathfile)) {
		return false;
	}

	$fs = fopen($pathfile, 'r+');
	$content = fread($fs, filesize($pathfile));
	fclose($fs);

	if (!$content) {
		return false;
	}

	return $content;
}

function uploadfile($upfile, $upload_path, $redirect, $f_size = '102400', $f_type = 'txt,jpg|jpeg|gif|png')
{
	if (!file_exists($upload_path)) {
		mkdir($upload_path, 511);
		chmod($upload_path, 511);
	}

	$file_name = $_FILES[$upfile]['name'];

	if (empty($file_name)) {
		return false;
	}

	$file_type = $_FILES[$upfile]['type'];
	$file_size = $_FILES[$upfile]['size'];
	$file_tmp = $_FILES[$upfile]['tmp_name'];
	$upload_dir = $upload_path;
	$ext = explode('.', $file_name);
	$sub = count($ext) - 1;
	$ext_type = strtolower($ext[$sub]);
	$up_type = explode('|', $f_type);

	if (!in_array($ext_type, $up_type)) {
		exit("\n\t\t<script language=javascript>\n\t\t\t alert('您上传的文件类型不符合要求！请重新上传！\\n\\n上传类型只能是" . $f_type . "。');\n\t\t\t location.href='" . $redirect . "';\n\t\t</script>");
	}

	$file_names = time() . rand(1, 9999) . '.' . $ext[$sub];
	$upload_file_name = $upload_dir . $file_names;
	$chk_file = move_uploaded_file($file_tmp, $upload_file_name);

	if ($chk_file) {
		chmod($upload_file_name, 511);
		unset($ext[$sub]);
		$file_name = implode('.', $ext);
		return array($file_names, $file_size, $ext_type, $file_name);
	}
	else {
		return false;
	}
}

function get_str_trim($str, $type = ',')
{
	$str = explode($type, $str);
	$str2 = '';

	for ($i = 0; $i < count($str); $i++) {
		$str2 .= trim($str[$i]) . $type;
	}

	return substr($str2, 0, -1);
}

function get_preg_replace($str, $type = '|')
{
	$str = preg_replace("/\r\n/", ',', $str);
	$str = get_str_trim($str);
	$str = get_str_trim($str, $type);
	return $str;
}

function str_iconv($str)
{
	return iconv('gb2312', 'UTF-8', $str);
}

function get_infoCnt($table = '', $slt = '', $where = '', $type = 1)
{
	$sql = 'SELECT ' . $slt . ' FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where;

	if ($type == 1) {
		return $GLOBALS['db']->getOne($sql);
	}
	else {
		return $GLOBALS['db']->getRow($sql);
	}
}

function get_array_rand_return($arr)
{
	if (count($arr) < 1) {
		$arrNum = 1;
	}
	else {
		$arrNum = count($arr);
	}

	$rand_num = rand(1, $arrNum);
	$rand_key = array_rand($arr, $rand_num);
	$key = count($rand_key);

	if ($key == 1) {
		$newArr[] = $arr[rand(0, count($arr) - 1)];
	}
	else {
		$newArr = array();

		for ($i = 0; $i < $key; $i++) {
			$newArr[$i] = $arr[$rand_key[$i]];
		}
	}

	return $newArr;
}


?>
