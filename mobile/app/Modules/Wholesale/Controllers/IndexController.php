<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Wholesale\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	public function __construct()
	{
		parent::__construct();
		L(require LANG_PATH . C('shop.lang') . '/wholesale.php');
	}

	public function actionIndex()
	{
		$category_list = cat_list(0, 1, 0, 'category', '', 2);

		foreach ($category_list as $key => $val) {
			if (0 < $val['parent_id'] && 0 < $val['level']) {
				$category_list[$val['parent_id']]['children'][$key] = $val;
				unset($category_list[$key]);
			}
		}

		$this->assign('category_list', $category_list);
		$this->assign('page_title', L('wholesale_list'));
		$this->display('wholesale_list');
	}

	public function actionWholeList()
	{
		$result = array('error' => 0, 'msg' => '');
		$search_category = I('search_category', 0, 'intval');
		$search_keywords = I('search_keywords', '', array('htmlspecialchars', 'trim'));
		$sort = I('sort', '', array('htmlspecialchars', 'trim'));
		$sort = $sort == 'ASC' ? 'ASC' : 'DESC';
		$order = I('order', '', array('htmlspecialchars', 'trim'));
		$param = array();
		$where = " WHERE g.goods_id = w.goods_id\r\n               AND w.enabled = 1\r\n               AND CONCAT(',', w.rank_ids, ',') LIKE '" . '%,' . session('user_rank') . ',%' . '\' ';

		if ($search_category) {
			$where .= ' AND g.cat_id = \'' . $search_category . '\' ';
			$param['search_category'] = $search_category;
			$result['search_category'] = $search_category;
		}

		if ($search_keywords) {
			$where .= ' AND (g.keywords LIKE \'%' . $search_keywords . '%\' OR g.goods_name LIKE \'%' . $search_keywords . '%\') ';
			$param['search_keywords'] = $search_keywords;
			$result['search_keywords'] = $search_keywords;
		}

		$where .= ' AND w.review_status = 3';
		$sql = 'SELECT COUNT(*) FROM ' . $this->ecs->table('wholesale') . ' AS w, ' . $this->ecs->table('goods') . ' AS g ' . $where;
		$count = $this->db->getOne($sql);
		$countSql = '';
		$where_sort = '';

		if ($order) {
			$where_sort .= ' ORDER BY ' . $order . ' ' . $sort;
			$countSql = ' ,(SELECT COUNT(*) FROM ' . $this->ecs->table('order_goods') . ' og WHERE g.goods_id = og.goods_id) AS sales_num';
			$param['sort'] = $search_keywords;
			$result['sort'] = $search_keywords;
		}

		if (0 < $count) {
			$default_display_type = C('show_order_type') == '0' ? 'list' : 'text';
			$display = isset($_REQUEST['display']) && in_array(trim(strtolower($_REQUEST['display'])), array('list', 'text')) ? trim($_REQUEST['display']) : (isset($_COOKIE['ECS']['display']) ? $_COOKIE['ECS']['display'] : $default_display_type);
			$display = in_array($display, array('list', 'text')) ? $display : 'text';
			setcookie('ECS[display]', $display, gmtime() + 86400 * 7, $GLOBALS['cookie_path'], $GLOBALS['cookie_domain']);
			$size = isset($GLOBALS['_CFG']['page_size']) && 0 < intval($GLOBALS['_CFG']['page_size']) ? intval($GLOBALS['_CFG']['page_size']) : 10;
			$page_count = ceil($count / $size);
			$page = isset($_REQUEST['page']) && 0 < intval($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
			$page = $page_count < $page ? $page_count : $page;
			$wholesale_list = wholesale_list($size, $page, $where, $where_sort, $countSql, $sort, $order);
			$result['wholesale_list'] = $wholesale_list;
			$param['act'] = 'list';
			$pager = get_pager('wholesale.php', array_reverse($param, true), $count, $page, $size);
			$pager['display'] = $display;
			$result['pager'] = $pager;
			$result['cart_goods'] = isset($_SESSION['wholesale_goods']) ? $_SESSION['wholesale_goods'] : array();
		}
		else if (empty($_SESSION['user_id'])) {
			$result['error'] = 1;
			$result['msg'] = L('need_to_login');
		}
		else {
			$result['error'] = 2;
			$result['msg'] = L('no_goods');
		}

		$this->ajaxReturn($result);
	}

	public function actionDetail()
	{
		$id = I('id', 0, 'intval');
		$sql = 'SELECT act_id, goods_id, user_id, goods_name, prices  FROM ' . $this->ecs->table('wholesale') . ' where act_id = ' . $id;
		$wholesale = $this->db->getRow($sql);

		if (empty($wholesale)) {
			show_message(L('no_wholesale_goods'), L('back_up_page'), url('index'), 'warning');
		}

		$goods = get_goods_info($wholesale['goods_id']);

		if (empty($goods)) {
			show_message(L('no_wholesale_goods'), L('back_up_page'), url('index'), 'warning');
		}

		$prices = unserialize($wholesale['prices']);
		$hasPrice = false;

		foreach ($prices as $k => $v) {
			foreach ($v['attr'] as $kitem => $vitem) {
				$kstr = $this->db->getOne('SELECT attr_name FROM ' . $this->ecs->table('attribute') . ' WHERE attr_id = ' . $kitem);
				$vstr = $this->db->getOne('SELECT attr_value FROM ' . $this->ecs->table('goods_attr') . ' WHERE goods_attr_id = ' . $vitem);
				$prices[$k]['attr'][$kstr] = $vstr;
				unset($prices[$k]['attr'][$kitem]);
				$hasPrice = true;
			}

			$minNum = 0;

			foreach ($v['qp_list'] as $kitem => $vitem) {
				$prices[$k]['qp_list'][$kitem]['quantity'] = $vitem['quantity'];
				$prices[$k]['qp_list'][$kitem]['price'] = price_format($vitem['price']);
				if ($minNum == 0 || $vitem['quantity'] < $minNum) {
					$minNum = $vitem['quantity'];
				}
			}

			$prices[$k]['minNum'] = $minNum;
		}

		$this->assign('id', $wholesale['act_id']);
		$this->assign('prices', $prices);
		$this->assign('hasPrice', $hasPrice);
		$this->assign('goods', $goods);
		$this->assign('minNum', $minNum);
		$this->assign('page_title', L('wholesal_detail'));
		$this->display('wholesale_details');
	}

	public function actionAddToCart()
	{
		$id = I('id', 0, 'intval');
		$number = I('number', '');

		if (empty($id)) {
			$this->ajaxReturn(array('msg' => L('no_wholesale_goods')));
		}

		if (empty($number)) {
			$this->ajaxReturn(array('msg' => L('no_wholesale_goods')));
		}

		$wholesale = wholesale_info($id);
		$wholeattr = array();

		foreach ($wholesale['price_list'] as $k => $v) {
			$wholeattr[$k] = $v['attr'];
		}

		if (isset($_SESSION['wholesale_goods'])) {
			foreach ($_SESSION['wholesale_goods'] as $goods) {
				if ($goods['goods_id'] == $wholesale['goods_id']) {
					if (empty($goods_attr)) {
						$this->ajaxReturn(array('msg' => L('goods_have_in_cart')));
					}
					else if (in_array($goods['goods_attr_id'], $goods_attr)) {
						$this->ajaxReturn(array('msg' => L('goods_have_in_cart')));
					}
				}
			}
		}

		$goods_list = array();

		foreach ($wholeattr as $klist => $list) {
			$goods_attr = array();

			foreach ($list as $k => $v) {
				$row['attr_id'] = $k;
				$row['attr_val_id'] = $v;
				$sql = 'select attr_name from ' . $this->ecs->table('attribute') . ' where attr_id = ' . $k;
				$row['attr_name'] = $this->db->getOne($sql);
				$sql = 'select attr_value from ' . $this->ecs->table('goods_attr') . ' where goods_attr_id = ' . $v;
				$row['attr_val'] = $this->db->getOne($sql);
				array_push($goods_attr, $row);
			}

			$goods_list[] = array('number' => $number[$klist], 'goods_attr' => $goods_attr);
		}

		$attr_matching = false;

		foreach ($wholesale['price_list'] as $attr_price) {
			if (empty($attr_price['attr'])) {
				$attr_matching = true;
				$goods_list[0]['qp_list'] = $attr_price['qp_list'];
				break;
			}
			else if (($key = is_attr_matching($goods_list, $attr_price['attr'])) !== false) {
				$attr_matching = true;
				$goods_list[$key]['qp_list'] = $attr_price['qp_list'];
			}
		}

		if (!$attr_matching) {
			$this->ajaxReturn(array('msg' => L('no_match_goods_attr')));
		}

		foreach ($goods_list as $goods_key => $goods) {
			if ($goods['number'] < $goods['qp_list'][0]['quantity']) {
				$this->ajaxReturn(array('msg' => L('dont_match_min_num')));
			}
			else if (6 < strlen(intval($goods['number']))) {
				$this->ajaxReturn(array('msg' => L('number_is_to_large')));
			}
			else {
				$goods_price = 0;

				foreach ($goods['qp_list'] as $qp) {
					if ($qp['quantity'] <= $goods['number']) {
						$goods_list[$goods_key]['goods_price'] = $qp['price'];
					}
					else {
						break;
					}
				}
			}
		}

		foreach ($goods_list as $goods_key => $goods) {
			$goods_attr_name = '';

			if (!empty($goods['goods_attr'])) {
				foreach ($goods['goods_attr'] as $key => $attr) {
					$attr['attr_name'] = htmlspecialchars($attr['attr_name']);
					$goods['goods_attr'][$key]['attr_name'] = $attr['attr_name'];
					$attr['attr_val'] = htmlspecialchars($attr['attr_val']);
					$goods['goods_attr'][$key]['attr_name'] = $attr['attr_name'];
					$goods_attr_name .= $attr['attr_name'] . '：' . $attr['attr_val'] . '&nbsp;';
				}
			}

			$total = $goods['number'] * $goods['goods_price'];
			$sql = 'select goods_thumb from ' . $this->ecs->table('goods') . ' where goods_id = ' . $wholesale['goods_id'];
			$goods['goods_thumb'] = $this->db->getOne($sql);
			$_SESSION['wholesale_goods'][] = array('goods_id' => $wholesale['goods_id'], 'goods_name' => $wholesale['goods_name'], 'goods_attr_id' => $goods['goods_attr'], 'goods_attr' => $goods_attr_name, 'goods_number' => $goods['number'], 'goods_price' => $goods['goods_price'], 'goods_thumb' => get_image_path($goods['goods_thumb']), 'subtotal' => $total, 'formated_goods_price' => price_format($goods['goods_price'], false), 'formated_subtotal' => price_format($total, false), 'goods_url' => url('goods/index/index', array('id' => $wholesale['goods_id'])));
		}

		$this->ajaxReturn(array('error' => '0', 'msg' => L('success_add_to_cart')));
	}

	public function actionCart()
	{
		$goods_list = isset($_SESSION['wholesale_goods']) ? $_SESSION['wholesale_goods'] : array();
		$total = 0;

		foreach ($goods_list as $key => $val) {
			$total += $val['subtotal'];
			$sql = 'SELECT shop_price FROM ' . $this->ecs->table('goods') . ' WHERE goods_id = ' . $val['goods_id'];
			$res = $this->db->getRow($sql);
			$goods_list[$key]['shop_price'] = $res['shop_price'];
			$goods_list[$key]['format_shop_price'] = price_format($res['shop_price']);
		}

		$this->assign('cart_goods', $goods_list);
		$this->assign('total', price_format($total));
		$this->assign('page_title', L('wholesaled_goods'));
		$this->display();
	}

	public function actionDropGoods()
	{
		$id = I('id');

		if (isset($_SESSION['wholesale_goods'][$id])) {
			unset($_SESSION['wholesale_goods'][$id]);
		}

		$this->ajaxReturn(array('error' => 0, 'msg' => '删除成功'));
	}

	public function actionSubmitOrder()
	{
		include_once ROOT_PATH . 'includes/lib_order.php';
		$files = array('order');
		$this->load_helper($files);

		if (count($_SESSION['wholesale_goods']) == 0) {
			$this->ajaxReturn(array('error' => 1, 'msg' => L('no_wholesale_goods_in_cart')));
		}

		if (empty($_POST['remark'])) {
			$this->ajaxReturn(array('error' => 1, 'msg' => L('please_mark_wholesale_info')));
		}

		$goods_amount = 0;

		foreach ($_SESSION['wholesale_goods'] as $goods) {
			$goods_amount += $goods['subtotal'];
		}

		$order = array('postscript' => htmlspecialchars($_POST['remark']), 'user_id' => $_SESSION['user_id'], 'add_time' => gmtime(), 'order_status' => OS_UNCONFIRMED, 'shipping_status' => SS_UNSHIPPED, 'pay_status' => PS_UNPAYED, 'goods_amount' => $goods_amount, 'order_amount' => $goods_amount, 'extension_code' => 'wholesale', 'referer' => 'touch');
		$error_no = 0;

		do {
			$order['order_sn'] = get_order_sn();
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $order, 'INSERT');
			$error_no = $GLOBALS['db']->errno();
			if (0 < $error_no && $error_no != 1062) {
				exit($GLOBALS['db']->errorMsg());
			}
		} while ($error_no == 1062);

		$new_order_id = $this->db->getLastInsID();
		$order['order_id'] = $new_order_id;

		foreach ($_SESSION['wholesale_goods'] as $goods) {
			$product_id = 0;

			if (!empty($goods['goods_attr_id'])) {
				$goods_attr_id = array();

				foreach ($goods['goods_attr_id'] as $value) {
					$goods_attr_id[$value['attr_id']] = $value['attr_val_id'];
				}

				ksort($goods_attr_id);
				$goods_attr = implode('|', $goods_attr_id);
				$sql = 'SELECT product_id FROM ' . $this->ecs->table('products') . (' WHERE goods_attr = \'' . $goods_attr . '\' AND goods_id = \'') . $goods['goods_id'] . '\'';
				$product_id = $this->db->getOne($sql);
			}

			$sql = 'INSERT INTO ' . $this->ecs->table('order_goods') . '( ' . 'order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ' . 'goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, ru_id) ' . (' SELECT \'' . $new_order_id . '\', goods_id, goods_name, goods_sn, \'' . $product_id . '\',\'' . $goods['goods_number'] . '\', market_price, ') . ('\'' . $goods['goods_price'] . '\', \'' . $goods['goods_attr'] . '\', is_real, extension_code, 0, 0 , user_id ') . ' FROM ' . $this->ecs->table('goods') . (' WHERE goods_id = \'' . $goods['goods_id'] . '\'');
			$this->db->query($sql);
		}

		unset($_SESSION['wholesale_goods']);
		$this->ajaxReturn(array('error' => 0, 'msg' => sprintf(L('ws_order_submitted'), $order['order_sn'])));
	}
}

?>
