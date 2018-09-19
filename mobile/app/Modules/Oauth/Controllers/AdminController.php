<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Oauth\Controllers;

class AdminController extends \App\Modules\Base\Controllers\BackendController
{
	public function __construct()
	{
		parent::__construct();
		L(require LANG_PATH . C('shop.lang') . '/other.php');
		$this->assign('lang', array_change_key_case(L()));
		$this->admin_priv('oauth_admin');
	}

	public function actionIndex()
	{
		$modules = $this->read_modules(ADDONS_PATH . 'connect');

		foreach ($modules as $key => $value) {
			$modules[$key]['install'] = dao('touch_auth')->where(array('type' => $value['type']))->count();
		}

		$this->assign('modules', $modules);
		$this->display();
	}

	public function actionInstall()
	{
		if (IS_POST) {
			$data['type'] = I('type');
			$data['status'] = I('status', 0, 'intval');
			$data['sort'] = I('sort', 0, 'intval');
			$cfg_value = I('cfg_value');
			$cfg_name = I('cfg_name');
			$cfg_type = I('cfg_type');
			$cfg_label = I('cfg_label');
			$auth_config = array();
			if (isset($cfg_value) && is_array($cfg_value)) {
				for ($i = 0; $i < count($cfg_value); $i++) {
					$auth_config[] = array('name' => trim($cfg_name[$i]), 'type' => trim($cfg_type[$i]), 'value' => trim($cfg_value[$i]));
				}
			}

			$data['auth_config'] = serialize($auth_config);
			$this->model->table('touch_auth')->data($data)->add();
			$this->message(L('msg_ins_success'), url('index'));
			return NULL;
		}

		$type = I('type');
		$oauth_config = $this->getOauthConfig($type);

		if ($oauth_config !== false) {
			$this->redirect('index');
		}

		$filepath = ADDONS_PATH . 'connect/' . $type . '.php';

		if (file_exists($filepath)) {
			$set_modules = true;
			include_once $filepath;
			$info = $modules[$i];

			foreach ($info['config'] as $key => $value) {
				$info['config'][$key] = $value + array('label' => L($value['name']));
			}
		}

		$this->assign('info', $info);
		$this->assign('ur_here', L('plug_install'));
		$this->display();
	}

	public function actionEdit()
	{
		if (IS_POST) {
			$data['type'] = I('type');
			$data['status'] = I('status', 0, 'intval');
			$data['sort'] = I('sort', 0, 'intval');
			$cfg_value = I('cfg_value', '', array('htmlspecialchars', 'trim'));
			$cfg_name = I('cfg_name', '', array('htmlspecialchars', 'trim'));
			$cfg_type = I('cfg_type', '', array('htmlspecialchars', 'trim'));
			$cfg_label = I('cfg_label', '', array('htmlspecialchars', 'trim'));
			$auth_config = array();
			if (isset($cfg_value) && is_array($cfg_value)) {
				for ($i = 0; $i < count($cfg_value); $i++) {
					if (strpos($cfg_value[$i], '*') == true) {
						$old_oauth_config = $this->getOauthInfo($data['type']);
						$cfg_value[$i] = $old_oauth_config[$i];
					}

					$auth_config[] = array('name' => $cfg_name[$i], 'type' => $cfg_type[$i], 'value' => $cfg_value[$i]);
				}
			}

			$data['auth_config'] = serialize($auth_config);
			dao('touch_auth')->data($data)->where(array('type' => $data['type']))->save();
			$this->message(L('edit_success'), url('index'));
			return NULL;
		}

		$type = I('type');
		$oauth_config = $this->getOauthConfig($type);

		if ($oauth_config === false) {
			$this->redirect('index');
		}

		$filepath = ADDONS_PATH . 'connect/' . $type . '.php';

		if (file_exists($filepath)) {
			$set_modules = true;
			include_once $filepath;
			$info = $modules[$i];

			foreach ($info['config'] as $key => $value) {
				$info['config'][$key] = $value + array('label' => L($value['name']));
			}
		}

		foreach ($info['config'] as $key => $value) {
			if (isset($oauth_config[$value['name']])) {
				if ($key == 1) {
					$info['config'][$key]['value'] = string_to_star($oauth_config[$value['name']]);
				}
				else {
					$info['config'][$key]['value'] = $oauth_config[$value['name']];
				}
			}
			else {
				$info['config'][$key]['value'] = $value['value'];
			}
		}

		$info['status'] = $oauth_config['status'];
		$info['sort'] = $oauth_config['sort'];
		$this->assign('info', $info);
		$this->assign('ur_here', L('edit_plug'));
		$this->display();
	}

	public function actionUninstall()
	{
		$condition['type'] = I('type');
		dao('touch_auth')->where($condition)->delete();
		$this->message(L('upload_success'), url('index'));
	}

	private function getOauthConfig($type)
	{
		$condition['type'] = $type;
		$info = dao('touch_auth')->field('auth_config, status, sort')->where($condition)->find();

		if ($info) {
			$user = unserialize($info['auth_config']);
			$config = array('status' => $info['status'], 'sort' => $info['sort']);

			foreach ($user as $key => $value) {
				$config[$value['name']] = $value['value'];
			}

			return $config;
		}

		return false;
	}

	private function getOauthInfo($type)
	{
		$info = dao('touch_auth')->field('auth_config')->where(array('type' => $type))->find();

		if ($info) {
			$auth = unserialize($info['auth_config']);

			foreach ($auth as $key => $value) {
				$config[$key] = $value['value'];
			}

			return $config;
		}

		return false;
	}

	private function read_modules($directory = '.')
	{
		$dir = @opendir($directory);
		$set_modules = true;
		$modules = array();

		while (false !== ($file = @readdir($dir))) {
			if (preg_match('/^.*?\\.php$/', $file)) {
				include_once $directory . '/' . $file;
			}
		}

		@closedir($dir);
		unset($set_modules);

		foreach ($modules as $key => $value) {
			ksort($modules[$key]);
		}

		ksort($modules);
		return $modules;
	}
}

?>
