<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Base\Controllers;

abstract class FoundationController extends \Think\Controller\RestController
{
	protected $model;
	protected $cache;
	protected $fs;
	protected $pager = '';

	public function __construct()
	{
		parent::__construct();
		define('__HOST__', (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
		define('__URL__', __HOST__ . __ROOT__);
		define('__PC__', C('TMPL_PARSE_STRING.__PC__'));
		define('__STATIC__', C('TMPL_PARSE_STRING.__STATIC__'));
		define('__PUBLIC__', C('TMPL_PARSE_STRING.__PUBLIC__'));
		define('__TPL__', C('TMPL_PARSE_STRING.__TPL__'));
		define('MODULE_BASE_PATH', APP_PATH . MODULE_NAME . '/');
		$this->fs = new \Illuminate\Filesystem\Filesystem();
		$this->model = new \App\Libraries\Mysql();
		$GLOBALS['cache'] = $this->cache = \Think\Cache::getInstance();
		$GLOBALS['smarty'] = \Think\Think::instance('Think\\View');
	}

	protected function getApiCityName($ip = '')
	{
		$ip = (empty($ip) ? get_client_ip() : $ip);
		$data = array('ip' => $ip);
		new \App\Services\IpBasedLocation($data);
		return $data['city'];
	}

	protected function load_helper($files = array(), $type = 'base')
	{
		if (!is_array($files)) {
			$files = array($files);
		}

		$base_path = ($type == 'app' ? MODULE_BASE_PATH : BASE_PATH);

		foreach ($files as $vo) {
			$helper = $base_path . 'Helpers/' . $vo . '_helper.php';

			if (file_exists($helper)) {
				require_once $helper;
			}
		}
	}

	protected function pageLimit($url, $num = 10)
	{
		$url = str_replace(urlencode('{page}'), '{page}', $url);
		$page = (isset($this->pager['obj']) && is_object($this->pager['obj']) ? $this->pager['obj'] : new \App\Extensions\Page());
		$cur_page = $page->getCurPage($url);
		$limit_start = ($cur_page - 1) * $num;
		$limit = $limit_start . ',' . $num;
		$this->pager = array('obj' => $page, 'url' => $url, 'num' => $num, 'cur_page' => $cur_page, 'limit' => $limit);
		return $limit;
	}

	protected function pageShow($count)
	{
		return $this->pager['obj']->show($this->pager['url'], $count, $this->pager['num']);
	}

	protected function upload($savePath = '', $hasOne = false, $size = 2, $thumb = false, $autoSub = false)
	{
		$config = array(
			'maxSize'  => $size * 1024 * 1024,
			'rootPath' => C('UPLOAD_PATH'),
			'savePath' => rtrim($savePath, '/') . '/',
			'exts'     => array('jpg', 'gif', 'png', 'jpeg', 'bmp', 'mp3', 'amr', 'mp4'),
			'autoSub'  => $autoSub,
			'thumb'    => $thumb,
			'subName'  => array('date', 'Ym')
			);
		$aliossConfig = $this->getBucketInfo();
		if ((C('shop.open_oss') == 1) && ($aliossConfig !== false)) {
			$up = new \Think\Upload($config, 'Alioss', $aliossConfig);
		}
		else {
			$up = new \Think\Upload($config);
		}

		$result = $up->upload();

		if (!$result) {
			return array('error' => 1, 'message' => $up->getError());
		}
		else {
			$res = array('error' => 0);

			if ($hasOne) {
				$info = reset($result);
				$res['url'] = $info['savepath'] . $info['savename'];
			}
			else {
				foreach ($result as $k => $v) {
					$result[$k]['url'] = $v['savepath'] . $v['savename'];
				}

				$res['url'] = $result;
			}

			return $res;
		}
	}

	protected function remove($file = '')
	{
		if (empty($file) || in_array($file, array('/', '\\'))) {
			return false;
		}

		$config = $this->getBucketInfo();
		if ((C('shop.open_oss') == 1) && ($config !== false)) {
			$client = new \Think\Upload\Driver\Alioss($config);

			if ($client->delete($file)) {
				return true;
			}
		}
		else {
			$file = (is_file(ROOT_PATH . $file) ? ROOT_PATH . $file : dirname(ROOT_PATH) . '/' . $file);

			if (is_file($file)) {
				$this->fs->delete($file);
				return true;
			}
		}

		return false;
	}

	protected function ossMirror($file = '', $savepath = '')
	{
		$data = array('savepath' => rtrim($savepath, '/') . '/', 'savename' => basename($file), 'tmp_name' => $file);
		$config = $this->getBucketInfo();

		if ($config !== false) {
			$client = new \Think\Upload\Driver\Alioss($config);
			$client->save($data);
			return $data['url'];
		}

		return false;
	}

	protected function downloadFiles($url, $path = '')
	{
		$path = (empty($path) ? '' : rtrim($path, '/') . '/');
		$dir = dirname(ROOT_PATH) . '/' . $path;
		if (!file_exists($dir) && !empty($path)) {
			make_dir($dir, 511);
		}

		if ((strtolower(substr($url, 0, 4)) == 'http') && !empty($path)) {
			$filepath = $dir . basename($url);

			if (!file_exists($filepath)) {
				\Org\Net\Http::curlDownload($url, $filepath);
			}
		}
	}

	protected function BatchUploadOss($imglist, $path = '', $is_delete = false)
	{
		if (C('shop.open_oss') == 1) {
			foreach ($imglist as $k => $filename) {
				$image_name = $this->ossMirror(dirname(ROOT_PATH) . '/' . $path . $filename, $path);

				if ($is_delete == true) {
					$this->remove($image_name);
				}
			}

			return isset($image_name) ? true : false;
		}
	}

	protected function BatchDownloadOss($imglist, $path)
	{
		if (C('shop.open_oss') == 1) {
			$bucket_info = get_bucket_info();
			$bucket_info['endpoint'] = empty($bucket_info['endpoint']) ? $bucket_info['outside_site'] : $bucket_info['endpoint'];
			$http = rtrim($bucket_info['endpoint'], '/') . '/';

			foreach ($imglist as $k => $filename) {
				$url = $http . $path . $filename;
				$this->downloadFiles($url, $path);
			}

			return true;
		}
	}

	private function getBucketInfo()
	{
		$condition = array('is_use' => 1);
		$res = $this->model->table('oss_configure')->cache(true)->where($condition)->find();

		if (empty($res)) {
			return false;
		}

		$regional = substr($res['regional'], 0, 2);
		$endpoint = rtrim(str_replace(array('http://', 'https://'), '', strtolower($res['endpoint'])), '/');
		if (($regional == 'us') || ($regional == 'ap')) {
			$res['endpoint'] = $res['is_cname'] == 1 ? $endpoint : 'oss-' . $res['regional'] . '.aliyuncs.com';
			$res['outside_site'] = 'http://' . $res['bucket'] . '.oss-' . $res['regional'] . '.aliyuncs.com';
			$res['inside_site'] = 'http://' . $res['bucket'] . '.oss-' . $res['regional'] . '-internal.aliyuncs.com';
		}
		else {
			$res['endpoint'] = $res['is_cname'] == 1 ? $endpoint : 'oss-cn-' . $res['regional'] . '.aliyuncs.com';
			$res['outside_site'] = 'http://' . $res['bucket'] . '.oss-cn-' . $res['regional'] . '.aliyuncs.com';
			$res['inside_site'] = 'http://' . $res['bucket'] . '.oss-cn-' . $res['regional'] . '-internal.aliyuncs.com';
		}

		return array('bucket' => $res['bucket'], 'accessKeyId' => $res['keyid'], 'accessKeySecret' => $res['keysecret'], 'endpoint' => $res['endpoint'], 'isCName' => (bool) $res['is_cname']);
	}

	protected function sentry($e, $type = 0)
	{
		$client = new \Raven_Client('https://ae2118aa1c3149c5bba492ed9abaf43f:2e4b9be6f4d9495eb3f0a44f28484893@sentry.io/106949');
		$error_handler = new \Raven_ErrorHandler($client);
		$error_handler->registerExceptionHandler();
		$error_handler->registerErrorHandler();
		$error_handler->registerShutdownFunction();

		if ($type) {
			$client->captureMessage($e);
		}
		else {
			$client->captureException($e);
		}
	}
}

?>
