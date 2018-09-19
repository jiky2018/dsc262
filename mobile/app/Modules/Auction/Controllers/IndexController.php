<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Auction\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	public $area_id = 0;
	public $region_id = 0;

	public function __construct()
	{
		parent::__construct();
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		L(require LANG_PATH . C('shop.lang') . '/flow.php');
		$area_info = get_area_info($this->province_id);
		$this->area_id = $area_info['region_id'];
		$where = 'regionId = \'' . $province_id . '\'';
		$date = array('parent_id');
		$this->region_id = get_table_date('region_warehouse', $where, $date, 2);
		if (isset($_COOKIE['region_id']) && !empty($_COOKIE['region_id'])) {
			$this->region_id = $_COOKIE['region_id'];
		}
	}

	public function actionIndex()
	{
		$size = (0 < intval(C('page_size')) ? intval(C('page_size')) : 10);
		$page = (isset($_REQUEST['page']) && (0 < intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1);

		if (IS_AJAX) {
			$default_sort_order_method = (C('shop.sort_order_method') == 0 ? 'DESC' : 'ASC');
			$default_sort_order_type = (C('shop.sort_order_type') == 0 ? 'act_id' : (C('shop.sort_order_type') == 1 ? 'start_time' : 'end_time'));
			$sort = I('sort');
			$order = I('order');
			$sort = (in_array($sort, array('act_id', 'start_time', 'end_time')) ? $sort : $default_sort_order_type);
			$order = (in_array($order, array('ASC', 'DESC')) ? $order : $default_sort_order_method);
			$keyword = I('request.keyword');
			$count = auction_count($keyword);

			if (0 < $count) {
				$page_count = ceil($count / $size);
				$page = ($page_count < $page ? $page_count : $page);
			}

			if (0 < $count) {
				$auction_list = auction_list($keyword, $sort, $order, $size, $page);
			}

			exit(json_encode(array('list' => $auction_list, 'totalPage' => $page_count)));
		}

		$this->assign('page_title', L('auction_action'));
		$this->display();
	}

	public function actionDetail()
	{
		$id = (isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);

		if ($id <= 0) {
			ecs_header("Location: ./\n");
			exit();
		}

		$auction = auction_info($id);

		if (empty($auction)) {
			ecs_header("Location: ./\n");
			exit();
		}

		if ($_SESSION['user_id']) {
			$where['user_id'] = $_SESSION['user_id'];
			$where['goods_id'] = $auction['goods_id'];
			$rs = $this->db->table('collect_goods')->where($where)->count();

			if (0 < $rs) {
				$this->assign('goods_collect', 1);
			}
		}

		$auction['is_winner'] = 0;
		if ($auction['last_bid'] && ($auction['status_no'] == FINISHED) && ($auction['last_bid']['bid_user'] == $_SESSION['user_id']) && ($auction['order_count'] == 0)) {
			$auction['is_winner'] = 1;
		}

		if (0 < $auction['product_id']) {
			$goods_specifications = get_specifications_list($auction['goods_id']);
			$good_products = get_good_products($auction['goods_id'], 'AND product_id = ' . $auction['product_id']);
			$_good_products = explode('|', $good_products[0]['goods_attr']);
			$products_info = '';

			foreach ($_good_products as $value) {
				$products_info .= ' ' . $goods_specifications[$value]['attr_name'] . '：' . $goods_specifications[$value]['attr_value'];
			}

			$this->assign('products_info', $products_info);
			unset($goods_specifications);
			unset($good_products);
			unset($_good_products);
			unset($products_info);
		}

		$auction['gmt_end_time'] = local_strtotime($auction['end_time']);
		$auction['price_times'] = intval(($auction['current_price_int'] / $auction['amplitude']) + 1);
		$this->assign('auction', $auction);
		$goods_id = $auction['goods_id'];
		$goods = goods_info($goods_id, 0, 0);

		if (empty($goods)) {
			ecs_header("Location: ./\n");
			exit();
		}

		$goods['url'] = build_uri('goods', array('gid' => $goods_id), $goods['goods_name']);
		$this->assign('pictures', get_goods_gallery($goods_id));
		$this->assign('auction_goods', $goods);
		$auction_log = auction_log($id);
		$this->assign('auction_log', $auction_log);
		$this->assign('auction_count', auction_log($id, 1));
		$cat_id = I('cat_id', 0, 'intval');
		$integral_max = I('integral_max', 0);
		$integral_min = I('integral_min', 0);
		$children = get_children($cat_id);
		$hot_goods = get_exchange_recommend_goods('hot', $children, $integral_min, $integral_max);
		$this->assign('hot_goods', $hot_goods);
		$this->assign('cfg', C('shop'));
		assign_template();
		$position = assign_ur_here(0, $goods['goods_name']);
		$this->assign('page_title', $position['title']);
		assign_dynamic('auction');
		$sql = 'UPDATE ' . $this->ecs->table('goods') . ' SET click_count = click_count + 1 ' . 'WHERE goods_id = \'' . $auction['goods_id'] . '\'';
		$this->db->query($sql);
		$this->assign('now_time', gmtime());
		$share_data = array('title' => '拍卖商品_' . $goods['goods_name'], 'desc' => $goods['goods_brief'], 'link' => '', 'img' => $goods['goods_img']);
		$this->assign('share_data', $this->get_wechat_share_content($share_data));
		$this->assign('keywords', $goods['keywords']);
		$this->assign('description', $goods['goods_brief']);
		$this->display();
	}

	public function actionAuctionLog()
	{
		$id = I('id', 0, 'intval');

		if ($id <= 0) {
			ecs_header("Location: ./\n");
			exit();
		}

		$auction = auction_info($id);

		if (empty($auction)) {
			ecs_header("Location: ./\n");
			exit();
		}

		$auction_log = auction_log($id);
		$this->assign('auction', $auction);
		$this->assign('auction_log', $auction_log);
		$this->assign('auction_count', auction_log($id, 1));
		$this->display();
	}

	public function actionBid()
	{
		$this->load_helper('order');
		$id = (isset($_POST['id']) ? intval($_POST['id']) : 0);

		if ($id <= 0) {
			ecs_header("Location: ./\n");
			exit();
		}

		$auction = auction_info($id);

		if (empty($auction)) {
			ecs_header("Location: ./\n");
			exit();
		}

		if ($auction['status_no'] != UNDER_WAY) {
			show_message(L('au_not_under_way'), '', '', 'error');
		}

		$user_id = $_SESSION['user_id'];

		if ($user_id <= 0) {
			$url = url('auction/index/detail', array('id' => $id));
			show_message(L('au_bid_after_login'), '登录', url('user/login/index', array('back_act' => urlencode($url))));
		}

		$user = user_info($user_id);
		$price_times = I('price_times', 0, 'intval');
		$bid_price = ($price_times ? round(floatval($price_times * $auction['amplitude']), 2) : 0);

		if ($bid_price <= 0) {
			show_message(L('au_bid_price_error'), '', '', 'error');
		}

		$is_ok = false;

		if (0 < $auction['end_price']) {
			if ($auction['end_price'] <= $bid_price) {
				$bid_price = $auction['end_price'];
				$is_ok = true;
			}
		}

		if (!$is_ok) {
			if ($auction['bid_user_count'] == 0) {
				$min_price = $auction['start_price'];
			}
			else {
				$min_price = $auction['last_bid']['bid_price'] + $auction['amplitude'];

				if (0 < $auction['end_price']) {
					$min_price = min($min_price, $auction['end_price']);
				}
			}

			if ($bid_price < $min_price) {
				show_message(sprintf(L('au_your_lowest_price'), price_format($min_price, false)), '', '', 'error');
			}
		}

		if (($auction['last_bid']['bid_user'] == $user_id) && ($bid_price != $auction['end_price'])) {
			show_message(L('au_bid_repeat_user'), '', '', 'error');
		}

		if (0 < $auction['deposit']) {
			if ($user['user_money'] < $auction['deposit']) {
				show_message(L('au_user_money_short'), '', '', 'error');
			}

			if (0 < $auction['bid_user_count']) {
				log_account_change($auction['last_bid']['bid_user'], $auction['deposit'], -1 * $auction['deposit'], 0, 0, sprintf(L('au_unfreeze_deposit'), $auction['act_name']));
			}

			log_account_change($user_id, -1 * $auction['deposit'], $auction['deposit'], 0, 0, sprintf(L('au_freeze_deposit'), $auction['act_name']));
		}

		$auction_log = array('act_id' => $id, 'bid_user' => $user_id, 'bid_price' => $bid_price, 'bid_time' => gmtime());
		$this->db->autoExecute($this->ecs->table('auction_log'), $auction_log, 'INSERT');

		if ($bid_price == $auction['end_price']) {
			$sql = 'UPDATE ' . $this->ecs->table('goods_activity') . ' SET is_finished = 1 WHERE act_id = \'' . $id . '\' LIMIT 1';
			$this->db->query($sql);
		}

		$url = url('detail', array('id' => $id));
		ecs_header('Location: ' . $url);
		exit();
	}

	public function actionBuy()
	{
		$id = (isset($_POST['id']) ? intval($_POST['id']) : 0);

		if ($id <= 0) {
			ecs_header("Location: ./\n");
			exit();
		}

		$auction = auction_info($id);

		if (empty($auction)) {
			ecs_header("Location: ./\n");
			exit();
		}

		if ($auction['status_no'] != FINISHED) {
			show_message(L('au_not_finished'), '', '', 'error');
		}

		if ($auction['bid_user_count'] <= 0) {
			show_message(L('au_no_bid'), '', '', 'error');
		}

		if (0 < $auction['order_count']) {
			show_message(L('au_order_placed'));
		}

		$user_id = $_SESSION['user_id'];

		if ($user_id <= 0) {
			show_message(L('au_buy_after_login'));
		}

		if ($auction['last_bid']['bid_user'] != $user_id) {
			show_message(L('au_final_bid_not_you'), '', '', 'error');
		}

		$goods = goods_info($auction['goods_id']);
		$goods_attr = '';
		$goods_attr_id = '';

		if (0 < $auction['product_id']) {
			$product_info = get_good_products($auction['goods_id'], 'AND product_id = ' . $auction['product_id']);
			$goods_attr_id = str_replace('|', ',', $product_info[0]['goods_attr']);
			$attr_list = array();
			$sql = 'SELECT a.attr_name, g.attr_value ' . 'FROM ' . $this->ecs->table('goods_attr') . ' AS g, ' . $this->ecs->table('attribute') . ' AS a ' . 'WHERE g.attr_id = a.attr_id ' . 'AND g.goods_attr_id ' . db_create_in($goods_attr_id);
			$res = $this->db->query($sql);

			foreach ($res as $row) {
				$attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
			}

			$goods_attr = join(chr(13) . chr(10), $attr_list);
		}
		else {
			$auction['product_id'] = 0;
		}

		$this->load_helper('order');
		clear_cart(CART_AUCTION_GOODS);
		$cart = array('user_id' => $user_id, 'session_id' => SESS_ID, 'goods_id' => $auction['goods_id'], 'goods_sn' => addslashes($goods['goods_sn']), 'goods_name' => addslashes($goods['goods_name']), 'market_price' => $goods['market_price'], 'goods_price' => $auction['last_bid']['bid_price'], 'goods_number' => 1, 'goods_attr' => $goods_attr, 'goods_attr_id' => $goods_attr_id, 'warehouse_id' => $this->region_id, 'area_id' => $this->area_id, 'is_real' => $goods['is_real'], 'ru_id' => $goods['user_id'], 'extension_code' => addslashes($goods['extension_code']), 'parent_id' => 0, 'rec_type' => CART_AUCTION_GOODS, 'is_gift' => 0);
		$this->db->autoExecute($this->ecs->table('cart'), $cart, 'INSERT');
		$_SESSION['flow_type'] = CART_AUCTION_GOODS;
		$_SESSION['extension_code'] = 'auction';
		$_SESSION['extension_id'] = $id;
		$_SESSION['direct_shopping'] = 2;
		$this->redirect('flow/index/index');
		exit();
	}
}

?>
