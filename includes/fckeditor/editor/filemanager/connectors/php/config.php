<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
global $Config;
define('IN_ECS', true);
define('ROOT_PATH', preg_replace('/includes(.*)/i', '', str_replace('\\', '/', __FILE__)));

if (isset($_SERVER['PHP_SELF'])) {
	define('PHP_SELF', $_SERVER['PHP_SELF']);
}
else {
	define('PHP_SELF', $_SERVER['SCRIPT_NAME']);
}

$root_path = preg_replace('/includes(.*)/i', '', PHP_SELF);
require ROOT_PATH . 'data/config.php';
require ROOT_PATH . 'includes/lib_base.php';
require ROOT_PATH . 'includes/cls_mysql.php';
require ROOT_PATH . 'includes/cls_ecshop.php';
require ROOT_PATH . 'includes/cls_session.php';
require ROOT_PATH . 'includes/lib_common.php';
require ROOT_PATH . 'includes/lib_oss.php';
$ecs = new ECS($db_name, $prefix);
define('DATA_DIR', $ecs->data_dir());
define('IMAGE_DIR', $ecs->image_dir());
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$sess = new cls_session($db, $ecs->table('sessions'), $ecs->table('sessions_data'), 'ECSCP_ID');

if (!empty($_SESSION['admin_id'])) {
	if ($_SESSION['action_list'] == 'all') {
		$enable = true;
	}
	else {
		if ((strpos(',' . $_SESSION['action_list'] . ',', ',goods_manage,') === false) && (strpos(',' . $_SESSION['action_list'] . ',', ',virualcard,') === false) && (strpos(',' . $_SESSION['action_list'] . ',', ',article_manage,') === false)) {
			$enable = false;
		}
		else {
			$enable = true;
		}
	}
}
else {
	$enable = false;
}

$_CFG = load_config();
$Config['Enabled'] = $enable;
$Config['UserFilesPath'] = $root_path . IMAGE_DIR . '/upload/';
$Config['UserFilesAbsolutePath'] = ROOT_PATH . IMAGE_DIR . '/upload/';
$Config['ForceSingleExtension'] = true;
$Config['SecureImageUploads'] = true;
$Config['ConfigAllowedCommands'] = array('QuickUpload', 'FileUpload', 'GetFolders', 'GetFoldersAndFiles', 'CreateFolder');
$Config['ConfigAllowedTypes'] = array('File', 'Image', 'Flash', 'Media');
$Config['HtmlExtensions'] = array('html', 'htm', 'xml', 'xsd', 'txt', 'js');
$Config['ChmodOnUpload'] = 511;
$Config['ChmodOnFolderCreate'] = 511;
$Config['AllowedExtensions']['File'] = array('7z', 'aiff', 'asf', 'avi', 'bmp', 'csv', 'doc', 'fla', 'flv', 'gif', 'gz', 'gzip', 'jpeg', 'jpg', 'mid', 'mov', 'mp3', 'mp4', 'mpc', 'mpeg', 'mpg', 'ods', 'odt', 'pdf', 'png', 'ppt', 'pxd', 'qt', 'ram', 'rar', 'rm', 'rmi', 'rmvb', 'rtf', 'sdc', 'sitd', 'swf', 'sxc', 'sxw', 'tar', 'tgz', 'tif', 'tiff', 'txt', 'vsd', 'wav', 'wma', 'wmv', 'xls', 'xml', 'zip');
$Config['FileTypesPath']['File'] = $Config['UserFilesPath'] . 'File/';
$Config['FileTypesAbsolutePath']['File'] = $Config['UserFilesAbsolutePath'] == '' ? '' : $Config['UserFilesAbsolutePath'] . 'File/';
$Config['QuickUploadPath']['File'] = $Config['UserFilesPath'] . 'File/';
$Config['QuickUploadAbsolutePath']['File'] = $Config['UserFilesAbsolutePath'] . 'File/';
$Config['AllowedExtensions']['Image'] = array('jpg', 'gif', 'jpeg', 'png');
$Config['DeniedExtensions']['Image'] = array();
$Config['FileTypesPath']['Image'] = $Config['UserFilesPath'] . 'Image/';
$Config['FileTypesAbsolutePath']['Image'] = $Config['UserFilesAbsolutePath'] == '' ? '' : $Config['UserFilesAbsolutePath'] . 'Image/';
$Config['QuickUploadPath']['Image'] = $Config['UserFilesPath'] . 'Image/';
$Config['QuickUploadAbsolutePath']['Image'] = $Config['UserFilesAbsolutePath'] . 'Image/';
$Config['AllowedExtensions']['Flash'] = array('swf', 'fla');
$Config['DeniedExtensions']['Flash'] = array();
$Config['FileTypesPath']['Flash'] = $Config['UserFilesPath'] . 'Flash/';
$Config['FileTypesAbsolutePath']['Flash'] = $Config['UserFilesAbsolutePath'] == '' ? '' : $Config['UserFilesAbsolutePath'] . 'Flash/';
$Config['QuickUploadPath']['Flash'] = $Config['UserFilesPath'] . 'Flash/';
$Config['QuickUploadAbsolutePath']['Flash'] = $Config['UserFilesAbsolutePath'] . 'Flash/';
$Config['AllowedExtensions']['Media'] = array('7z', 'aiff', 'asf', 'avi', 'bmp', 'csv', 'doc', 'fla', 'flv', 'gif', 'gz', 'gzip', 'jpeg', 'jpg', 'mid', 'mov', 'mp3', 'mp4', 'mpc', 'mpeg', 'mpg', 'ods', 'odt', 'pdf', 'png', 'ppt', 'pxd', 'qt', 'ram', 'rar', 'rm', 'rmi', 'rmvb', 'rtf', 'sdc', 'sitd', 'swf', 'sxc', 'sxw', 'tar', 'tgz', 'tif', 'tiff', 'txt', 'vsd', 'wav', 'wma', 'wmv', 'xls', 'xml', 'zip');
$Config['DeniedExtensions']['Media'] = array();
$Config['FileTypesPath']['Media'] = $Config['UserFilesPath'] . 'Media/';
$Config['FileTypesAbsolutePath']['Media'] = $Config['UserFilesAbsolutePath'] == '' ? '' : $Config['UserFilesAbsolutePath'] . 'Media/';
$Config['QuickUploadPath']['Media'] = $Config['UserFilesPath'] . 'Media/';
$Config['QuickUploadAbsolutePath']['Media'] = $Config['UserFilesAbsolutePath'] . 'Media/';

?>
