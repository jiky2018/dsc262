<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Admin\Controllers;

class EditorSellerController extends \App\Modules\Base\Controllers\BackendSellerController
{
	protected $ru_id = 0;

	public function __construct()
	{
		parent::__construct();
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
		header('Access-Control-Allow-Headers: X-HTTP-Method-Override, Content-Type, x-requested-with, Authorization');
		$this->load_helper(array('function', 'ecmoban'));
		$this->seller_admin_priv('touch_dashboard');
		$seller_id = dao('admin_user')->where(array('user_id' => $_SESSION['seller_id']))->getField('ru_id');
		$get_ru_id = input('ru_id', 0, 'intval');
		$this->ru_id = !empty($get_ru_id) && ($get_ru_id == $seller_id) ? $get_ru_id : $seller_id;
	}

	public function actionIndex()
	{
		$shopInfo = json_encode(array('ruid' => $this->ru_id));
		$this->assign('shopInfo', $shopInfo);
		$this->display();
	}

	public function actionPreview()
	{
		$data = input('post.data');

		if (!empty($data)) {
			$data = $this->transform($data);
			\App\Libraries\Compile::setModule('preview', $data);
			$this->response(array('error' => 0, 'data' => $data));
		}

		$this->response(array('error' => 1, 'msg' => 'fail'));
	}

	public function actionSave()
	{
		$data = input('post.data');

		if (!empty($data)) {
			$data = $this->transform($data);
			\App\Libraries\Compile::setModule('index', $data);
			$this->response(array('error' => 0, 'data' => $data));
		}

		$this->response(array('error' => 1, 'msg' => 'fail'));
	}

	public function actionClean()
	{
		if (\App\Libraries\Compile::cleanModule()) {
			$this->response(array('error' => 0, 'msg' => 'success'));
		}

		$this->response(array('error' => 1, 'msg' => 'fail'));
	}

	public function actionPicture()
	{
		$thumb = input('post.thumb');
		$page = input('post.page', 1);
		$condition = array('ru_id' => 0, 'album_id' => 99);
		$list = $this->db->table('pic_album')->where($condition)->order('pic_id desc')->limit(15)->page($page)->select();
		$res = array();

		foreach ($list as $key => $vo) {
			$res[$key]['id'] = $vo['pic_id'];
			$res[$key]['desc'] = $vo['pic_name'];
			$res[$key]['img'] = get_image_path($vo['pic_file']);
			$res[$key]['isSelect'] = false;
		}

		if (empty($res)) {
			$this->response(array('error' => 1, 'msg' => 'fail'));
		}
		else {
			$total = $this->db->table('pic_album')->where($condition)->count();
			$this->response(array('error' => 0, 'total' => $total, 'data' => $res));
		}
	}

	public function actionRemovePicture()
	{
		$condition = array('ru_id' => 0, 'pic_id' => input('pic_id'));
		$picture = $this->db->table('pic_album')->where($condition)->find();

		if (empty($picture)) {
			$this->response(array('error' => 1, 'msg' => 'fail'));
		}

		$picturePath = dirname(ROOT_PATH) . '/' . $picture['pic_file'];

		if (is_file($picturePath)) {
			$this->fs->remove($picturePath);
			$this->db->table('pic_album')->where($condition)->delete();
			$this->response(array('error' => 0, 'msg' => 'success'));
		}

		$this->response(array('error' => 1, 'msg' => 'not found'));
	}

	public function actionUpload()
	{
		$res = $this->upload('data/gallery_album/original_img/');

		if ($res['error'] === 0) {
			$condition = array('album_id' => 99);
			$album = $this->db->table('gallery_album')->where($condition)->find();

			if (empty($album)) {
				$data = array('album_id' => 99, 'ru_id' => 0, 'album_mame' => '手机端可视化相册', 'album_cover' => '', 'album_desc' => '', 'sort_order' => 50, 'add_time' => gmtime());
				$this->db->table('gallery_album')->add($data);
			}

			$upinfo = $res['url']['file'];
			$data = array('pic_name' => $upinfo['name'], 'album_id' => 99, 'pic_file' => $upinfo['url'], 'pic_thumb' => '', 'pic_image' => '', 'pic_size' => $upinfo['size'], 'pic_spec' => $upinfo['name'], 'ru_id' => 0, 'add_time' => gmtime());
			$this->db->table('pic_album')->add($data);
		}
	}

	private function transform($data = array())
	{
		if (!empty($data)) {
			foreach ($data as $key => $vo) {
				if (is_array($vo)) {
					$data[$key] = $this->transform($vo);
				}
				else {
					if ($vo === 'true') {
						$data[$key] = true;
					}

					if (($vo === 'false') || ($key === 'setting')) {
						$data[$key] = false;
					}
				}
			}

			return $data;
		}
	}
}

?>
