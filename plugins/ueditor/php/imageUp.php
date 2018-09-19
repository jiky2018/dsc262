<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
require 'config.php';

if (!$enable) {
	exit('{\'url\':\'\',\'title\':\'\',\'original\':\'\',\'state\':\'没有上传权限\'}');
}

$config = array(
	'savePath'   => $root_path_relative . IMAGE_DIR . '/upload/',
	'maxSize'    => 3000,
	'allowFiles' => array('.gif', '.png', '.jpg', '.jpeg', '.bmp')
	);
$title = htmlspecialchars($_POST['pictitle'], ENT_QUOTES);

if (isset($_GET['fetch'])) {
	header('Content-Type: text/javascript');
	echo 'updateSavePath(["upload"]);';
	return NULL;
}

$up = new Uploader('upfile', $config);
$info = $up->getFileInfo();
$info['url'] = str_replace($root_path_relative, $root_path, $info['url']);
if ($info['url'] && (substr($info['url'], 0, 1) != '/')) {
	$info['url'] = '/' . $info['url'];
}

if ($GLOBALS['_CFG']['open_oss'] == 1) {
	if ($info['url']) {
		$dir_url = explode(IMAGE_DIR, $info['url']);

		if (count($dir_url) == 2) {
			$desc_image = IMAGE_DIR . $dir_url[1];
			$urlip = get_ip_url($GLOBALS['ecs']->get_domain(), 1);
			$url_site = $urlip . $dir_url[0];
		}
		else {
			$desc_image = IMAGE_DIR . $dir_url;
			$url_site = get_ip_url($GLOBALS['ecs']->get_domain());
		}

		$bucket_info = get_bucket_info();
		$url = $url_site . 'oss.php?act=upload';
		$Http = new Http();
		$post_data = array(
			'bucket'    => $bucket_info['bucket'],
			'keyid'     => $bucket_info['keyid'],
			'keysecret' => $bucket_info['keysecret'],
			'is_cname'  => $bucket_info['is_cname'],
			'endpoint'  => $bucket_info['outside_site'],
			'object'    => array($desc_image)
			);
		$Http->doPost($url, $post_data);
		if (!empty($info['url']) && (strpos($info['url'], 'http://') === false) && (strpos($info['url'], 'https://') === false)) {
			$info['url'] = $bucket_info['endpoint'] . $info['url'];
			$info['url'] = str_replace('//' . IMAGE_DIR, '/' . IMAGE_DIR, $info['url']);
			$dir = explode('/', ROOT_PATH);
			$web_dir = '/' . $dir[3] . '/';
			if (isset($dir[3]) && $dir[3]) {
				if ($web_dir && ($web_dir != '/')) {
					$info['url'] = str_replace($web_dir, '', $info['url']);
				}
			}
		}
	}
}

echo '{\'url\':\'' . $info['url'] . '\',\'title\':\'' . $title . '\',\'original\':\'' . $info['originalName'] . '\',\'state\':\'' . $info['state'] . '\'}';

?>
