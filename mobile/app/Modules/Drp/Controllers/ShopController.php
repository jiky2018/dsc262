<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Drp\Controllers;

class ShopController extends \App\Modules\Base\Controllers\FrontendController
{
	private $region_id;
	private $area_id;
	private $isself = 0;
	private $promotion = 0;

	public function __construct()
	{
		parent::__construct();
		$this->assign('custom', C(custom));
		$this->assign('customs', C(customs));
	}

	public function actionIndex()
	{
		$province_id = (isset($_COOKIE['province']) ? $_COOKIE['province'] : 0);
		$area_info = get_area_info($province_id);
		$this->area_id = $area_info['region_id'];
		$where = 'regionId = \'' . $province_id . '\'';
		$date = array('parent_id');
		$this->region_id = get_table_date('region_warehouse', $where, $date, 2);
		if (isset($_COOKIE['region_id']) && !empty($_COOKIE['region_id'])) {
			$this->region_id = $_COOKIE['region_id'];
		}

		$shop_id = intval(I('id'));
		$shop_info = $this->getShop($shop_id);
		$size = 10;
		$page = I('page', 1, 'intval');
		$status = I('status', 1, 'intval');
		$this->cat_id = I('cat_id', 0, 'intval');

		if (0 < $this->cat_id) {
			$status = 4;
		}

		$type = drp_type($shop_info['user_id']);

		if ($type == 2) {
			$goodsid = drp_type_goods($shop_info['user_id'], $type);

			foreach ($goodsid as $key) {
				$goods_id .= $key['goods_id'] . ',';
			}

			$goods_id = substr($goods_id, 0, -1);

			if (0 < $this->cat_id) {
				$where = ' AND g.goods_id ' . db_create_in($goods_id) . ' and ' . get_children($this->cat_id);
			}
			else {
				$where = ' AND g.goods_id ' . db_create_in($goods_id);
			}
		}
		else if ($type == 1) {
			$catid = drp_type_cat($shop_info['user_id'], $type);

			foreach ($catid as $key) {
				$cat_id .= $key['cat_id'] . ',';
			}

			$cat_id = substr($cat_id, 0, -1);

			if (0 < $this->cat_id) {
				$where = ' AND g.cat_id ' . db_create_in($cat_id) . ' and ' . get_children($this->cat_id);
			}
			else {
				$where = ' AND g.cat_id ' . db_create_in($cat_id);
			}
		}
		else {
			$where = '';
		}

		if (IS_AJAX) {
			$this->order = C('shop.sort_order_method') == '0' ? 'desc' : 'asc';
			$this->sort = C('shop.sort_order_type') == '0' ? 'goods_id' : (C('shop.sort_order_type') == '1' ? 'shop_price' : 'last_update');
			$goodslist = get_goods($where, $this->region_id, $this->area_id, $size, $page, $status, $type, $this->sort, $this->order, $this->cat_id);
			exit(json_encode(array('list' => $goodslist['list'], 'totalPage' => $goodslist['totalpage'])));
		}

		$category = drp_get_child_tree(0, $shop_id);
		$this->assign('cat_id', $this->cat_id);
		$this->assign('category', $category);
		$this->assign('shop_info', $shop_info);
		$res = $this->checkShop($shop_id);
		$this->assign('status', $status);
		$this->assign('shop_id', $shop_id);
		$description = '快来参观我的店铺吧，惊喜多多优惠多多';
		$share_data = array('title' => $shop_info['shop_name'], 'desc' => $description, 'link' => '', 'img' => $shop_info['headimgurl']);
		$this->assign('share_data', $this->get_wechat_share_content($share_data));
		$this->assign('page_title', $shop_info['shop_name']);
		$this->assign('description', $description);
		$this->display();
	}

	public function actionDrpchildcategory()
	{
		$this->cat_id = I('request.id', 0, 'intval');
		$this->shop_id = I('request.shop_id', 0, 'intval');

		if (IS_AJAX) {
			if (empty($this->cat_id)) {
				exit(json_encode(array('code' => 1, 'message' => '请选择分类')));
			}

			if (APP_DEBUG) {
				$category = drp_get_child_tree($this->cat_id, $this->shop_id);
			}
			else {
				$category = cache('categorys' . $this->cat_id);

				if ($category === false) {
					$category = drp_get_child_tree($this->cat_id, $this->shop_id);
					cache('category' . $this->cat_id, $category);
				}
			}

			exit(json_encode(array('category' => $category)));
		}
	}

	private function getShop($shop_id = 0)
	{
		$time = gmtime();
		$sql = 'SELECT * FROM {pre}drp_shop WHERE id=' . $shop_id;
		$res = $this->db->getRow($sql);
		$sql = 'SELECT headimgurl FROM {pre}wechat_user WHERE ect_uid=\'' . $res['user_id'] . '\'';
		$headimgurl = $this->db->getOne($sql);
		$shop_info = '';

		if ($headimgurl) {
			$shop_info['headimgurl'] = $headimgurl;
		}
		else {
			$sql = 'SELECT user_picture FROM {pre}users WHERE user_id=\'' . $res['user_id'] . '\'';
			$user_picture = $this->db->getOne($sql);
			$shop_info['headimgurl'] = get_image_path($user_picture);
		}

		$shop_info['id'] = $res['id'];
		$shop_info['shop_name'] = C('shop_name') . $res['shop_name'];
		$shop_info['real_name'] = $res['real_name'];
		$shop_info['audit'] = $res['audit'];
		$shop_info['status'] = $res['status'];

		if (empty($res['shop_img'])) {
			$shop_info['shop_img'] = elixir('img/user-shop.png');
		}
		else {
			$shop_info['shop_img'] = get_image_path($res['shop_img']);
		}

		$shop_info['user_id'] = $res['user_id'];
		$shop_info['create_time'] = date('Y-m-d', $res['create_time']);

		if ($res['user_id'] = $_SESSION['user_id']) {
			$shop_info['url'] = url('drp/user/index', array('id' => $res['user_id']));
		}

		$cat = substr($res['goods_id'], 0, -1);
		$shop_info['goods_id'] = $cat;
		$type = drp_type($_SESSION['user_id']);

		if ($type == 2) {
			$goodsid = drp_type_goods($_SESSION['user_id'], $type);

			foreach ($goodsid as $key) {
				$goods_id .= $key['goods_id'] . ',';
			}

			$goods_id = substr($goods_id, 0, -1);
			$where = ' AND goods_id ' . db_create_in($goods_id);
		}
		else if ($type == 1) {
			$catid = drp_type_cat($_SESSION['user_id'], $type);

			foreach ($catid as $key) {
				$cat_id .= $key['cat_id'] . ',';
			}

			$cat_id = substr($cat_id, 0, -1);
			$where = ' AND cat_id ' . db_create_in($cat_id);
		}
		else {
			$where = '';
		}

		$sql = 'SELECT count(goods_id) as sum from {pre}goods WHERE is_on_sale = 1 AND is_distribution = 1 AND dis_commission >0 AND is_alone_sale = 1 AND is_delete = 0 ' . $where;
		$sum['all'] = $this->db->getOne($sql);
		$shop_info['sum'] = $sum['all'];
		$sql = 'SELECT count(goods_id) as sum FROM {pre}goods WHERE  is_new = 1 AND is_distribution = 1 AND is_on_sale = 1 AND dis_commission >0 AND is_alone_sale = 1 AND is_delete = 0 ' . $where;
		$sum['new'] = $this->db->getOne($sql);
		$shop_info['new'] = $sum['new'];
		$sql = 'SELECT count(goods_id) as sum FROM {pre}goods WHERE is_promote = 1 AND is_distribution = 1 AND dis_commission >0 AND promote_start_date <= \'' . $time . '\' AND promote_end_date >= \'' . $time . '\' AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 ' . $where;
		$sum['promote'] = $this->db->getOne($sql);
		$shop_info['promote'] = $sum['promote'];
		return $shop_info;
	}

	private function checkShop($shop_id = 0)
	{
		$sql = 'SELECT * FROM {pre}drp_shop WHERE id=\'' . $shop_id . '\'';
		$res = $this->db->getRow($sql);

		if ($res['audit'] != 1) {
			show_message(L('admin_check'), L('in_shop'), url('/'), 'fail');
		}

		if ($res['status'] != 1) {
			show_message(L('shop_close'), L('in_shop'), url('/'), 'fail');
		}

		return ture;
	}
}

?>
