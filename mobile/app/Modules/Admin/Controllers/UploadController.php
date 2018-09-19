<?php
//zend by 商创网络 Q Q:123456    禁止倒卖 一经发现停止任何服务
namespace App\Modules\Admin\Controllers;

class UploadController extends \App\Modules\Base\Controllers\BackendController
{
	private $conf = array();

	public function __construct()
	{
		parent::__construct();
		C('SHOW_PAGE_TRACE', false);
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		$this->content = file_get_contents(ROOT_PATH . 'public/vendor/editor/config.json');
		$this->conf = json_decode(preg_replace('/\\/\\*[\\s\\S]+?\\*\\//', '', str_replace('__ROOT__', ltrim(dirname(__ROOT__), '/'), $this->content)), true);
	}

	public function actionIndex()
	{
		$action = I('get.action');

		switch ($action) {
		case 'config':
			$result = json_encode($this->conf);
			break;

		case 'uploadimage':
		case 'uploadscrawl':
		case 'uploadvideo':
		case 'uploadfile':
			$result = $this->uploads();
			break;

		case 'listimage':
			$result = $this->lists();
			break;

		case 'listfile':
			$result = $this->lists();
			break;

		case 'catchimage':
			$result = $this->crawler();
			break;

		default:
			$result = json_encode(array('state' => L('request_url_error')));
			break;
		}

		if (isset($_GET['callback'])) {
			if (preg_match('/^[\\w_]+$/', $_GET['callback'])) {
				echo htmlspecialchars($_GET['callback']) . '(' . $result . ')';
			}
			else {
				echo json_encode(array('state' => L('parameter_error')));
			}
		}
		else {
			echo $result;
		}
	}

	private function uploads()
	{
		$base64 = 'upload';

		switch (htmlspecialchars($_GET['action'])) {
		case 'uploadimage':
			$config = array('pathFormat' => $this->conf['imagePathFormat'], 'maxSize' => $this->conf['imageMaxSize'], 'allowFiles' => $this->conf['imageAllowFiles']);
			$fieldName = $this->conf['imageFieldName'];
			break;

		case 'uploadscrawl':
			$config = array('pathFormat' => $this->conf['scrawlPathFormat'], 'maxSize' => $this->conf['scrawlMaxSize'], 'allowFiles' => $this->conf['scrawlAllowFiles'], 'oriName' => 'scrawl.png');
			$fieldName = $this->conf['scrawlFieldName'];
			$base64 = 'base64';
			break;

		case 'uploadvideo':
			$config = array('pathFormat' => $this->conf['videoPathFormat'], 'maxSize' => $this->conf['videoMaxSize'], 'allowFiles' => $this->conf['videoAllowFiles']);
			$fieldName = $this->conf['videoFieldName'];
			break;

		case 'uploadfile':
		default:
			$config = array('pathFormat' => $this->conf['filePathFormat'], 'maxSize' => $this->conf['fileMaxSize'], 'allowFiles' => $this->conf['fileAllowFiles']);
			$fieldName = $this->conf['fileFieldName'];
			break;
		}

		$aliossConfig = get_bucket_info(true);
		if (C('shop.open_oss') == 1 && $aliossConfig !== false) {
			$res = $this->oss_upload('data/attached/image/', true, 2, false, true, $aliossConfig);
			return json_encode($res);
		}
		else {
			$up = new \App\Extensions\Uploader($fieldName, $config, $base64);
			return json_encode($up->getFileInfo());
		}
	}

	public function oss_upload($savePath = '', $hasOne = false, $size = 2, $thumb = false, $autoSub = false, $aliossConfig = array())
	{
		$oss_config = array(
			'maxSize'  => $size * 1024 * 1024,
			'rootPath' => C('UPLOAD_PATH'),
			'savePath' => rtrim($savePath, '/') . '/',
			'exts'     => array('jpg', 'gif', 'png', 'jpeg', 'bmp', 'mp3', 'amr', 'mp4'),
			'autoSub'  => $autoSub,
			'thumb'    => $thumb,
			'subName'  => array('date', 'Ymd')
			);
		$oss_up = new \Think\Upload($oss_config, 'Alioss', $aliossConfig);
		$result = $oss_up->upload();

		if (!$result) {
			$res = array('state' => $oss_up->getError());
		}
		else {
			$res = array('state' => 'SUCCESS');

			if ($hasOne) {
				$info = reset($result);
				$res['url'] = $info['savepath'] . $info['savename'];
				$res['title'] = $info['savename'];
				$res['original'] = $info['name'];
				$res['type'] = $info['type'];
				$res['size'] = $info['size'];
				$res['url'] = get_image_path($res['url']);
			}
			else {
				foreach ($result as $k => $v) {
					$result[$k]['url'] = $v['savepath'] . $v['savename'];
					$result[$k]['url'] = get_image_path($result[$k]['url']);
				}

				$res['url'] = $result;
			}
		}

		return $res;
	}

	private function lists()
	{
		switch ($_GET['action']) {
		case 'listfile':
			$allowFiles = $this->conf['fileManagerAllowFiles'];
			$listSize = $this->conf['fileManagerListSize'];
			$path = $this->conf['fileManagerListPath'];
			break;

		case 'listimage':
		default:
			$allowFiles = $this->conf['imageManagerAllowFiles'];
			$listSize = $this->conf['imageManagerListSize'];
			$path = $this->conf['imageManagerListPath'];
		}

		$allowFiles = substr(str_replace('.', '|', join('', $allowFiles)), 1);
		$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
		$start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
		$end = $start + $size;
		$path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == '/' ? '' : '/') . $path;
		$files = $this->getfiles($path, $allowFiles);

		if (!count($files)) {
			return json_encode(array(
	'state' => 'no match file',
	'list'  => array(),
	'start' => $start,
	'total' => count($files)
	));
		}

		$len = count($files);
		$i = min($end, $len) - 1;

		for ($list = array(); $i < $len && 0 <= $i && $start <= $i; $i--) {
			$list[] = $files[$i];
		}

		$result = json_encode(array('state' => 'SUCCESS', 'list' => $list, 'start' => $start, 'total' => count($files)));
		return $result;
	}

	private function crawler()
	{
		set_time_limit(0);
		$config = array('pathFormat' => $this->conf['catcherPathFormat'], 'maxSize' => $this->conf['catcherMaxSize'], 'allowFiles' => $this->conf['catcherAllowFiles'], 'oriName' => 'remote.png');
		$fieldName = $this->conf['catcherFieldName'];
		$list = array();

		if (isset($_POST[$fieldName])) {
			$source = $_POST[$fieldName];
		}
		else {
			$source = $_GET[$fieldName];
		}

		foreach ($source as $imgUrl) {
			$item = new \App\Extensions\Uploader($imgUrl, $config, 'remote');
			$info = $item->getFileInfo();
			array_push($list, array('state' => $info['state'], 'url' => $info['url'], 'size' => $info['size'], 'title' => htmlspecialchars($info['title']), 'original' => htmlspecialchars($info['original']), 'source' => htmlspecialchars($imgUrl)));
		}

		return json_encode(array('state' => count($list) ? 'SUCCESS' : 'ERROR', 'list' => $list));
	}

	private function getfiles($path, $allowFiles, &$files = array())
	{
		if (!is_dir($path)) {
			return null;
		}

		if (substr($path, strlen($path) - 1) != '/') {
			$path .= '/';
		}

		$handle = opendir($path);

		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..') {
				$path2 = $path . $file;

				if (is_dir($path2)) {
					$this->getfiles($path2, $allowFiles, $files);
				}
				else if (preg_match('/\\.(' . $allowFiles . ')$/i', $file)) {
					$files[] = array('url' => substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])), 'mtime' => filemtime($path2));
				}
			}
		}

		return $files;
	}
}

?>
