<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\User\Controllers;

class RefoundController extends \App\Modules\Base\Controllers\FrontendController
{
	private $user_id;

	public function __construct()
	{
		parent::__construct();
		$this->user_id = $_SESSION['user_id'];
		$this->actionchecklogin();
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		L(require LANG_PATH . C('shop.lang') . '/flow.php');
		$this->assign('lang', array_change_key_case(L()));
		$files = array('order', 'clips', 'payment', 'transaction');
		$this->load_helper($files);
	}

	public function actionIndex()
	{
		$order_id = I('order_id', 0, 'intval');
		$page = I('page', 1);
		$size = I('size', 1);
		$type = I('type', 0);

		if (IS_AJAX) {
			if ($type == 0) {
				$order_list = get_all_return_order($order_id);
				$order_count = count($order_list);
				exit(json_encode(array('order_list' => $order_list, 'totalPage' => ceil($order_count / $size))));
			}
			else if ($type == 1) {
				$return_count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_return') . ' WHERE user_id =' . $_SESSION['user_id']);
				$refound_list = return_order($order_id);
				exit(json_encode(array('refound_list' => $refound_list, 'totalPage' => ceil($return_count / $size))));
			}
		}

		$this->assign('page_title', L('return'));
		$this->assign('order_id', $order_id);
		$this->display();
	}

	public function actionApplyReturn()
	{
		$return_rec_id = I('order_goods_id');

		if (empty($return_rec_id)) {
			show_message(L('return_exist'), '', '', 'info', true);
		}

		$is_refound = get_is_refound($return_rec_id);

		if ($is_refound == 1) {
			show_message(L('return_is_apply'), '', '', 'info', true);
		}

		$order_id = I('order_id', 0, 'intval');
		$order = dao('order_info')->field('shipping_status, order_status, chargeoff_status, is_settlement')->where(array('order_id' => $order_id))->find();
		$return_allowable = SS_UNSHIPPED < $order['shipping_status'] && $order['order_status'] != OS_RETURNED ? true : false;
		$this->assign('return_allowable', $return_allowable);
		$parent_cause = get_parent_cause();
		$this->assign('cause_list', $parent_cause);
		$sql = 'SELECT g.goods_cause FROM ' . $GLOBALS['ecs']->table('order_goods') . ' as og ' . ' LEFT JOIN  ' . $GLOBALS['ecs']->table('goods') . ' as g ON og.goods_id = g.goods_id ' . ' WHERE og.rec_id = \'' . $return_rec_id . '\' ';
		$goods_cause = $GLOBALS['db']->getOne($sql);
		$goods_cause_select = get_goods_cause($goods_cause, $order['chargeoff_status'], $order['is_settlement']);
		$cause_name = '';
		$lang_ort = L('order_return_type');

		foreach ($goods_cause_select as $key => $value) {
			if ($order['shipping_status'] == 0) {
				if ($value['cause'] == 3) {
					$value['is_checked'] = 1;
					$goods_cause_select[$key] = $value;
				}
				else {
					$goods_cause_select = array();
				}
			}

			if ($goods_cause_select[$key]['is_checked'] == 1) {
				$cause_name = $lang_ort[$value['cause']];
			}
		}

		$this->assign('cause_name', $cause_name);
		$this->assign('goods_cause', $goods_cause_select);
		$this->assign('country_list', get_regions());
		$this->assign('shop_country', C('shop.shop_country'));
		$this->assign('shop_province_list', get_regions(1, C('shop.shop_country')));
		$province_list = get_regions(1, C('shop.shop_country'));
		$this->assign('province_list', $province_list);
		$city_list = get_region_city_county($this->province_id);

		if ($city_list) {
			foreach ($city_list as $k => $v) {
				$city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
			}
		}

		$this->assign('city_list', $city_list);
		$district_list = get_region_city_county($this->city_id);
		$this->assign('district_list', $district_list);
		$consignee = get_consignee($_SESSION['user_id']);
		$this->assign('consignee', $consignee);
		$user_address = get_goods_region_name($consignee['province']) . ' ' . get_goods_region_name($consignee['city']) . ' ' . get_goods_region_name($consignee['district']);
		$userinfo = array('consignee' => $consignee['consignee'], 'mobile' => $consignee['mobile'], 'email' => $consignee['email'], 'user_address' => $user_address);
		$goods = get_order_goods_info($return_rec_id);
		$this->assign('goods', $goods);
		$this->assign('userinfo', $userinfo);
		$this->assign('user_id', $_SESSION['user_id']);
		$this->assign('order', $order);
		$this->assign('return_rec_id', $return_rec_id);
		$this->assign('return_g_id', $goods['goods_id']);
		$this->assign('return_g_number', $goods['goods_number']);
		$sql = 'SELECT id, img_file FROM ' . $GLOBALS['ecs']->table('return_images') . ' WHERE user_id = ' . $_SESSION['user_id'] . ' and rec_id = ' . $return_rec_id . ' order by id desc';
		$res = $GLOBALS['db']->query($sql);
		$reutrnPicList = array();

		foreach ($res as $key => $val) {
			$reutrnPicList[$key]['id'] = $val['id'];
			$reutrnPicList[$key]['pic'] = get_image_path($val['img_file']);
		}

		$_SESSION['refound_token'] = md5(uniqid('', true) . $return_rec_id);
		$this->assign('refound_token', $_SESSION['refound_token']);
		$this->assign('return_pic_list', $reutrnPicList);
		$this->assign('page_title', L('apply_return'));
		$this->display();
	}

	public function actionSubmitReturn()
	{
		$rec_id = empty($_REQUEST['return_rec_id']) ? 0 : intval($_REQUEST['return_rec_id']);
		$last_option = !isset($_REQUEST['last_option']) ? $_REQUEST['parent_id'] : $_REQUEST['last_option'];
		$return_remark = !isset($_REQUEST['return_remark']) ? '' : htmlspecialchars(trim($_REQUEST['return_remark']));
		$return_brief = !isset($_REQUEST['return_brief']) ? '' : htmlspecialchars(trim($_REQUEST['return_brief']));
		$chargeoff_status = input('chargeoff_status', 0, 'intval');
		$refound_token = input('refound_token');

		if ($_SESSION['refound_token'] !== $refound_token) {
			return false;
		}

		if (empty($rec_id)) {
			show_message(L('Apply_Abnormal'), '', '', 'info', true);
		}

		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_return') . ' WHERE rec_id = ' . $rec_id;
		$num = $GLOBALS['db']->getOne($sql);

		if (0 < $num) {
			show_message(L('Repeated_Submission'), '', '', 'info', true);
		}

		if (empty($last_option)) {
			show_message(L('cause_id_empty'), '', '', 'info', true);
		}

		$sql = 'select g.goods_name, g.goods_sn,g.brand_id, og.order_id, og.goods_id, og.product_id, og.goods_attr, og.warehouse_id, og.area_id, o.order_sn, ' . ' og.is_real, og.goods_attr_id, og.goods_price, og.goods_price, og.goods_number, o.user_id ' . ' , o.consignee, o.mobile, o.country, o.province, o.city , o.district ' . 'from ' . $GLOBALS['ecs']->table('order_goods') . ' as og ' . ' left join ' . $GLOBALS['ecs']->table('goods') . ' as g on og.goods_id = g.goods_id ' . ' left join ' . $GLOBALS['ecs']->table('order_info') . ' as o on o.order_id = og.order_id ' . (' where og.rec_id = \'' . $rec_id . '\'');
		$order_goods = $GLOBALS['db']->getRow($sql);

		if ($order_goods['user_id'] != $_SESSION['user_id']) {
			show_message(L('Apply_Abnormal'), '', '', 'info', true);
		}

		$return_number = empty($_REQUEST['goods_number']) ? 1 : intval($_REQUEST['goods_number']);
		$return_type = intval($_REQUEST['return_type']);
		$maintain = 0;
		$return_status = 0;

		if ($return_type == 1) {
			$back = 1;
			$exchange = 0;
		}
		else if ($return_type == 2) {
			$back = 0;
			$exchange = 2;
		}
		else if ($return_type == 3) {
			$back = 0;
			$exchange = 0;
			$return_status = -1;
		}
		else {
			$back = 0;
			$exchange = 0;
		}

		$aftersn = 0;
		$sql = 'SELECT cloud_orderid,cloud_detailed_id FROM' . $GLOBALS['ecs']->table('order_cloud') . ('WHERE rec_id = \'' . $rec_id . '\' LIMIT 1');
		$order_cloud = $GLOBALS['db']->getRow($sql);

		if (!empty($order_cloud)) {
			if ($return_type == 0 || $return_type == 2) {
				show_message(L('return_error'), '', '', 'info', true);
			}

			$isRefund = 1;

			if ($return_type == 3) {
				$isRefund = 2;
			}

			$order_return_request = array('isRefund' => intval($isRefund), 'orderDetailId' => intval($order_cloud['cloud_detailed_id']), 'orderInfoId' => intval($order_cloud['cloud_orderid']), 'refundNum' => intval($return_number), 'userReason' => trim($return_brief), 'imgProof1' => '', 'imgProof2' => '', 'imgProof3' => '');
			$sql = 'select img_file from' . $GLOBALS['ecs']->table('return_images') . (' where rec_id = \'' . $rec_id . '\' and user_id = \'') . $_SESSION['user_id'] . '\' LIMIT 0,3';
			$images_list = $GLOBALS['db']->getAll($sql);

			if (!empty($images_list)) {
				foreach ($images_list as $k => $v) {
					if ($v) {
						$img = get_image_path($v['img_file']);
						if (!empty($img) && (strpos($img, 'http://') === false && strpos($img, 'https://') === false && strpos($img, 'errorImg.png') === false)) {
							$img = $GLOBALS['ecs']->url() . $img;
						}

						$i = $k + 1;
						$order_return_request['imgProof' . $i] = $img;
					}
				}
			}

			$cloud = new \App\Services\Erp\JigonService();
			$requ = $cloud->saveAfterSales($order_return_request);

			if ($requ) {
				$requ = json_decode($requ, true);

				if ($requ['code'] != '10000') {
					show_message($requ['message'], '', '', 'info', true);
				}
				else {
					$aftersn = $requ['data']['afterSn'];
				}
			}
			else {
				show_message(L('process_false'), '', '', 'info', true);
			}
		}

		$attr_val = isset($_REQUEST['attr_val']) ? $_REQUEST['attr_val'] : array();
		$return_attr_id = !empty($attr_val) ? implode(',', $attr_val) : '';
		$attr_val = get_goods_attr_info_new($attr_val, 'pice', $order_goods['warehouse_id'], $order_goods['area_id']);
		$order_return = array('rec_id' => $rec_id, 'goods_id' => $order_goods['goods_id'], 'order_id' => $order_goods['order_id'], 'order_sn' => $order_goods['order_sn'], 'chargeoff_status' => $chargeoff_status, 'return_type' => $return_type, 'maintain' => $maintain, 'back' => $back, 'exchange' => $exchange, 'user_id' => $_SESSION['user_id'], 'goods_attr' => $order_goods['goods_attr'], 'attr_val' => $attr_val, 'return_brief' => $return_brief, 'remark' => $return_remark, 'credentials' => isset($_REQUEST['credentials']) ? intval($_REQUEST['credentials']) : 0, 'country' => empty($_REQUEST['country']) ? 0 : intval($_REQUEST['country']), 'province' => empty($_REQUEST['province_region_id']) ? 0 : intval($_REQUEST['province_region_id']), 'city' => empty($_REQUEST['city_region_id']) ? 0 : intval($_REQUEST['city_region_id']), 'district' => empty($_REQUEST['district_region_id']) ? 0 : intval($_REQUEST['district_region_id']), 'street' => empty($_REQUEST['street']) ? 0 : intval($_REQUEST['street']), 'cause_id' => $last_option, 'apply_time' => gmtime(), 'actual_return' => '', 'address' => empty($_REQUEST['return_address']) ? '' : htmlspecialchars(trim($_REQUEST['return_address'])), 'zipcode' => empty($_REQUEST['code']) ? '' : trim($_REQUEST['code']), 'addressee' => empty($_REQUEST['addressee']) ? $order_goods['consignee'] : htmlspecialchars(trim($_REQUEST['addressee'])), 'phone' => empty($_REQUEST['mobile']) ? $order_goods['mobile'] : htmlspecialchars(trim($_REQUEST['mobile'])), 'return_status' => $return_status);

		if (in_array($return_type, array(1, 3))) {
			$return_info = get_return_refound($order_return['order_id'], $order_return['rec_id'], $return_number);
			$order_return['should_return'] = $return_info['return_price'];
			$order_return['return_shipping_fee'] = $return_info['return_shipping_fee'];
		}
		else {
			$order_return['should_return'] = 0;
			$order_return['return_shipping_fee'] = 0;
		}

		$error_no = 0;

		do {
			$order_return['return_sn'] = get_order_sn();
			$query = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_return'), $order_return, 'INSERT', '', 'SILENT');
			$error_no = $GLOBALS['db']->errno();
			if (0 < $error_no && $error_no != 1062) {
				exit($GLOBALS['db']->errorMsg());
			}
		} while ($error_no == 1062);

		if ($query) {
			$return = dao('order_return')->field('ret_id, refound_status')->where(array('return_sn' => $order_return['return_sn']))->find();
			return_action($return['ret_id'], '申请退款（由用户寄回）', '', $order_return['remark'], L('buyer'));
			unset($_SESSION['refound_token']);
			$return_goods['rec_id'] = $order_return['rec_id'];
			$return_goods['ret_id'] = $return['ret_id'];
			$return_goods['goods_id'] = $order_goods['goods_id'];
			$return_goods['goods_name'] = $order_goods['goods_name'];
			$return_goods['brand_name'] = $order_goods['brand_name'];
			$return_goods['product_id'] = $order_goods['product_id'];
			$return_goods['goods_sn'] = $order_goods['goods_sn'];
			$return_goods['is_real'] = $order_goods['is_real'];
			$return_goods['goods_attr'] = $order_goods['goods_attr'];
			$return_goods['attr_id'] = $order_goods['goods_attr_id'];
			$return_goods['refound'] = $order_goods['goods_price'];
			$return_goods['return_type'] = $return_type;
			$return_goods['return_number'] = $return_number;

			if ($return_type == 1) {
				$return_goods['out_attr'] = '';
			}
			else if ($return_type == 2) {
				$return_goods['out_attr'] = $order_return['attr_val'];
				$return_goods['return_attr_id'] = $return_attr_id;
			}
			else {
				$return_goods['out_attr'] = '';
			}

			$query = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('return_goods'), $return_goods, 'INSERT', '', 'SILENT');
			$sql = 'select count(*) from' . $GLOBALS['ecs']->table('return_images') . (' where rec_id = \'' . $rec_id . '\' and user_id = \'') . $_SESSION['user_id'] . '\'';
			$images_count = $GLOBALS['db']->getOne($sql);

			if (0 < $images_count) {
				$images['rg_id'] = $order_goods['goods_id'];
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('return_images'), $images, 'UPDATE', 'rec_id = \'' . $rec_id . '\' and user_id = \'' . $_SESSION['user_id'] . '\'');
			}

			$order_return_extend = array('ret_id' => $return['ret_id'], 'return_number' => $return_number, 'aftersn' => $aftersn);
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_return_extend'), $order_return_extend, 'INSERT', '', 'SILENT');
			$address_detail = get_consignee_info($order_goods['order_id'], $order_return['address']);
			$order_return['address_detail'] = $address_detail;
			$order_return['apply_time'] = local_date('Y-m-d H:i:s', $order_return['apply_time']);
			$new_order = dao('order_info')->field('pay_id, pay_status, money_paid')->where(array('order_id' => $order_goods['order_id']))->find();
			$new_order['ret_id'] = $return_goods['ret_id'];
			$new_order['rec_id'] = $return_goods['rec_id'];
			$new_order['order_id'] = $order_goods['order_id'];
			$new_order['order_sn'] = $order_goods['order_sn'];
			$new_order['user_id'] = $order_goods['user_id'];
			$new_order['should_return'] = $order_return['should_return'];
			if (!empty($new_order) && $new_order['pay_status'] == 2 && $return['refound_status'] == 0) {
				$payment_info = array();
				$payment_info = payment_info($new_order['pay_id']);
				if ($payment_info && $payment_info['pay_code'] == 'wxpay') {
					$payment = unserialize_config($payment_info['pay_config']);

					if (file_exists(ADDONS_PATH . 'payment/' . $payment_info['pay_code'] . '.php')) {
						include_once ADDONS_PATH . 'payment/' . $payment_info['pay_code'] . '.php';
						$pay_obj = new $payment_info['pay_code']();
						$res = $pay_obj->payRefund($new_order, $payment);
						if ($res && $res == $order['return_sn']) {
							order_refund_online($new_order, 1, '生成退款申请', $order_return['should_return']);
						}
					}
				}
			}

			show_message(L('Apply_Success_Prompt'), L('See_returnlist'), url('detail', array('ret_id' => $return['ret_id'])), 'info', true, $order_return);
		}
		else {
			show_message(L('Apply_Abnormal'), '', '', 'info', true);
		}
	}

	public function actionImgReturn()
	{
		$img = $_FILES['myfile']['tmp_name'];
		list($width, $height, $type) = getimagesize($img);

		if (empty($img)) {
			return NULL;
		}

		$user_id = $_SESSION['user_id'];
		$rec_id = I('rec_id');
		$sql = 'SELECT count(*) FROM' . $GLOBALS['ecs']->table('return_images') . 'WHERE user_id = ' . $user_id . ' and rec_id = ' . $rec_id;
		$res = $GLOBALS['db']->getOne($sql);

		if (5 <= $res) {
			echo json_encode(array('error' => 1, 'content' => '图片不能超过5张'));
			return NULL;
		}

		if (empty($type)) {
			echo json_encode(array('error' => 1, 'content' => '图片类型不正确'));
			return NULL;
		}

		$result = $this->upload('data/return_images', false, 20, array(C('shop.thumb_width'), C('shop.thumb_height')));
		$path = $result['url']['myfile']['url'];
		$add_time = gmtime();
		$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('return_images') . ' (rec_id,user_id,img_file,add_time)values(' . $rec_id . ',' . $user_id . ',\'' . $path . '\',' . $add_time . ')';
		$GLOBALS['db']->query($sql);
		$sql = 'SELECT id, img_file FROM' . $GLOBALS['ecs']->table('return_images') . 'WHERE user_id = ' . $user_id . ' and rec_id = ' . $rec_id;
		$res = $GLOBALS['db']->query($sql);
		$img = array();

		foreach ($res as $key => $val) {
			$img[$key]['id'] = $val['id'];
			$img[$key]['pic'] = get_image_path($val['img_file']);
		}

		echo json_encode($img);
	}

	public function actionClearPictures()
	{
		$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$rec_id = isset($_REQUEST['rec_id']) ? intval($_REQUEST['rec_id']) : 0;
		$result = array('error' => 0, 'content' => '');
		$sql = 'select img_file from ' . $GLOBALS['ecs']->table('return_images') . ' where user_id = \'' . $_SESSION['user_id'] . ('\' and rec_id = \'' . $rec_id . '\'') . ' and id=' . $id;
		$img_list = $GLOBALS['db']->getAll($sql);

		foreach ($img_list as $key => $row) {
			get_oss_del_file(array($row['img_file']));
			@unlink(get_image_path($row['img_file']));
		}

		$sql = 'delete from ' . $GLOBALS['ecs']->table('return_images') . ' where user_id = \'' . $_SESSION['user_id'] . ('\' and rec_id = \'' . $rec_id . '\'') . ' and id=' . $id;
		$GLOBALS['db']->query($sql);
		echo json_encode($result);
	}

	public function actionDetail()
	{
		$ret_id = input('ret_id', 0, 'intval');
		$order = get_return_detail($ret_id);

		if ($order === false) {
			$this->err->show('退换货列表', url('index'));
			exit();
		}

		if (!empty($order['out_invoice_no'])) {
			$shipping_code = dao('shipping')->where(array('shipping_id' => $order['out_shipping_name']))->getField('shipping_code');
			$plugin = ADDONS_PATH . 'shipping/' . $shipping_code . '.php';

			if (file_exists($plugin)) {
				include_once $plugin;
				$shipping = new $shipping_code();
				$order['out_invoice_no_btn'] = $shipping->query($order['out_invoice_no']);
			}
		}

		if (!empty($order['back_invoice_no'])) {
			$shipping_code = dao('shipping')->where(array('shipping_id' => $order['back_shipping_name']))->getField('shipping_code');
			$plugin = ADDONS_PATH . 'shipping/' . $shipping_code . '.php';

			if (file_exists($plugin)) {
				include_once $plugin;
				$shipping = new $shipping_code();
				$order['back_invoice_no_btn'] = $shipping->query($order['back_invoice_no']);
			}
		}

		$region = array($order['country'], $order['province'], $order['city'], $order['district']);
		$shipping_list = available_shipping_list($region, $order['ru_id']);

		foreach ($shipping_list as $key => $val) {
			$shipping_cfg = unserialize_config($val['configure']);
			$shipping_fee = $shipping_count == 0 && $cart_weight_price['free_shipping'] == 1 ? 0 : shipping_fee($val['shipping_code'], unserialize($val['configure']), $cart_weight_price['weight'], $cart_weight_price['amount'], $cart_weight_price['number']);
			$shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);
			$shipping_list[$key]['shipping_fee'] = $shipping_fee;
			$shipping_list[$key]['free_money'] = price_format($shipping_cfg['free_money'], false);
			$shipping_list[$key]['insure_formated'] = strpos($val['insure'], '%') === false ? price_format($val['insure'], false) : $val['insure'];

			if ($val['shipping_id'] == $order['shipping_id']) {
				$insure_disabled = $val['insure'] == 0;
				$cod_disabled = $val['support_cod'] == 0;
			}
		}

		$this->assign('shipping_list', $shipping_list);
		$sql = 'select return_status, refound_status, agree_apply  from ' . $GLOBALS['ecs']->table('order_return') . (' where ret_id = \'' . $ret_id . '\'');
		$status = $GLOBALS['db']->getRow($sql);
		$order['status'] = $status['return_status'];
		$order['refound'] = $status['refound_status'];
		$order['agree_apply'] = $status['agree_apply'];
		$sql = 'SELECT aftersn FROM' . $GLOBALS['ecs']->table('order_return_extend') . (' WHERE ret_id = \'' . $ret_id . '\' LIMIT 1');
		$aftersn = $GLOBALS['db']->getOne($sql);
		$new_order = dao('order_info')->field('pay_id, pay_status, money_paid')->where(array('order_id' => $order['order_id']))->find();
		$new_order['ret_id'] = $order['ret_id'];
		$new_order['rec_id'] = $order['rec_id'];
		$new_order['order_id'] = $order['order_id'];
		$new_order['order_sn'] = $order['order_sn'];
		$new_order['user_id'] = $order['user_id'];
		if ($new_order['pay_status'] == 2 && $order['refound'] == 0) {
			$payment_info = array();
			$payment_info = payment_info($new_order['pay_id']);
			if ($payment_info && $payment_info['pay_code'] == 'wxpay') {
				$payment = unserialize_config($payment_info['pay_config']);

				if (file_exists(ADDONS_PATH . 'payment/' . $payment_info['pay_code'] . '.php')) {
					include_once ADDONS_PATH . 'payment/' . $payment_info['pay_code'] . '.php';
					$pay_obj = new $payment_info['pay_code']();
					$res = $pay_obj->payRefundQuery($new_order, $payment);
					if ($res && $res == $order['return_sn']) {
						order_refund_online($new_order, 2, '在线自动退款', $order['should_return1']);
					}
					else {
						$order['return_status'] = '等待退款到账';
					}
				}
			}
		}

		$this->assign('page_title', L('return_detail'));
		$this->assign('return_detail', $order);
		$this->display();
	}

	public function actionCancel()
	{
		if (IS_AJAX) {
			$json_res = array('error' => 0, 'msg' => '', 'url' => '');
			$ret_id = input('ret_id', 0, 'intval');
			$user_id = $_SESSION['user_id'];

			if (cancel_return($ret_id, $user_id)) {
				$json_res['error'] = 0;
				$json_res['msg'] = L('取消申请成功');
				$json_res['url'] = url('index');
				exit(json_encode($json_res));
			}

			$json_res['error'] = 1;
			$json_res['msg'] = L('取消申请失败');
			exit(json_encode($json_res));
		}
	}

	public function actionOrderTracking()
	{
		$order_id = I('order_id', 0, 'intval');
		$order = get_order_detail($order_id, $this->user_id);

		if ($order === false) {
			$this->err->show(L('back_home_lnk'), './');
			exit();
		}

		if ($order['invoice_no']) {
			preg_match('/^<a.*href="(.*?)">/is', $order['invoice_no'], $url);

			if ($url[1]) {
				redirect($url[1]);
			}
		}

		show_message(L('msg_unfilled_or_receive'), L('user_center'), url('user/index/index'));
	}

	public function actionchecklogin()
	{
		if (!$this->user_id) {
			$back_act = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];
			$this->redirect('user/login/index', array('back_act' => urlencode($back_act)));
		}
	}

	public function actionGetSpec()
	{
		if (IS_AJAX) {
			$result = array('error' => 0, 'message' => '', 'attr_val' => '');
			$rec_id = I('id', 0, 'intval');
			$sql = 'SELECT warehouse_id, area_id, goods_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE rec_id = \'' . $rec_id . '\'');
			$order_goods = $GLOBALS['db']->getRow($sql);
			$g_id = $order_goods['goods_id'];
			if ($rec_id == 0 || $g_id == 0 || empty($order_goods)) {
				$result['message'] = '获取不到属性值';
				$result['error'] = 1;
			}
			else {
				$sql = 'SELECT goods_attr_id FROM ' . $GLOBALS['ecs']->table('order_goods') . (' WHERE rec_id = \'' . $rec_id . '\'');
				$goods_attr_id = $GLOBALS['db']->getOne($sql);
				$goods_attr = array();

				if (!empty($goods_attr_id)) {
					$goods_attr = explode(',', $goods_attr_id);
				}

				$properties = get_goods_properties($g_id, $order_goods['warehouse_id'], $order_goods['area_id']);
				$spec = $properties['spe'];

				if (!empty($spec)) {
					foreach ($spec as $key => $value) {
						if ($value['values']) {
							foreach ($value['values'] as $k => $v) {
								$arr_class = get_user_attr_checked($goods_attr, $v['id']);

								if ($arr_class['class'] == 'cattsel') {
									$v['checked'] = 1;
									$spec[$key]['attr_val'] = $arr_class['attr_val'];
								}

								$spec[$key]['values'][$k] = $v;
							}
						}
					}

					$result['error'] = 0;
					$result['spec'] = $spec;
				}
				else {
					$result['error'] = 1;
				}
			}

			exit(json_encode($result));
		}
	}

	public function actionAffirmReceived()
	{
		$user_id = $_SESSION['user_id'];
		$ret_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_return') . (' SET return_status = 4 where user_id = \'' . $user_id . '\' AND ret_id = \'' . $ret_id . '\'');
		$update = $GLOBALS['db']->query($sql);

		if ($update) {
			return_action($ret_id, L('return_received'), '', L('return_received'), L('buyer'));
			exit(json_encode(array('y' => 1)));
		}
		else {
			show_message(L('msg_unfilled_or_receive'));
		}
	}

	public function actionSelectCause()
	{
		if (IS_POST) {
			$res = array('error' => 0, 'message' => '', 'option' => '', 'rec_id' => 0);
			$c_id = input('c_id', 0, 'intval');
			$rec_id = input('rec_id', 0, 'intval');
			if (isset($c_id) && isset($rec_id)) {
				$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('return_cause') . ' WHERE parent_id = ' . $c_id . ' AND is_show = 1 order by sort_order ';
				$result = $GLOBALS['db']->getAll($sql);

				if ($result) {
					$select = '<select class="select form-control " name="last_option" id="last_option_' . $rec_id . '">';

					foreach ($result as $var) {
						$select .= '<option value="' . $var['cause_id'] . '" ';
						$select .= $selected == $var['cause_id'] ? 'selected=\'ture\'' : '';
						$select .= '>';

						if (0 < $var['level']) {
							$select .= str_repeat('&nbsp;', $var['level'] * 4);
						}

						$select .= htmlspecialchars(addslashes($var['cause_name']), ENT_QUOTES) . '</option>';
					}

					$select .= '</select>';
					$res['option'] = $select;
					$res['rec_id'] = $rec_id;
				}
				else {
					$res['error'] = 1;
					$res['message'] = '';
					$res['rec_id'] = $rec_id;
				}

				exit(json_encode($res));
			}
			else {
				$res['error'] = 100;
				$res['message'] = '';
				$res['rec_id'] = $rec_id;
				exit(json_encode($res));
			}
		}
	}

	public function actionEditExpress()
	{
		if (IS_POST) {
			$express = input('express', '', 'trim');
			$ret_id = $express['ret_id'];
			$back_shipping_name = $express['express_name'];
			$back_other_shipping = $express['other_express'];
			$back_invoice_no = $express['express_sn'];

			if (empty($ret_id)) {
				$json_res = array('error' => 1, 'msg' => L('return_exist'));
				exit(json_encode($json_res));
			}

			if (empty($back_shipping_name)) {
				$json_res = array('error' => 1, 'msg' => L('shipping_name_empty'));
				exit(json_encode($json_res));
			}

			if (empty($back_invoice_no)) {
				$json_res = array('error' => 1, 'msg' => L('invoice_no_empty'));
				exit(json_encode($json_res));
			}

			if ($ret_id) {
				$data = array('back_shipping_name' => $back_shipping_name, 'back_other_shipping' => $back_other_shipping, 'back_invoice_no' => $back_invoice_no);
				dao('order_return')->data($data)->where(array('ret_id' => $ret_id))->save();
				$json_res = array('error' => 0, 'msg' => L('edit_shipping_success'));
				exit(json_encode($json_res));
			}
		}
	}

	public function actionActivationReturnOrder()
	{
		if (IS_POST) {
			$json_res = array('error' => 0, 'msg' => '', 'url' => '');
			$ret_id = input('ret_id', 0, 'intval');
			$activation_number_type = 0 < C('shop.activation_number_type') ? C('shop.activation_number_type') : 2;
			$activation_number = dao('order_return')->where(array('ret_id' => $ret_id))->getField('activation_number');

			if ($activation_number < $activation_number_type) {
				$Order = dao('order_return');
				$Order->return_status = 0;
				$Order->activation_number = array('exp', 'activation_number+1');
				$Order->where(array('ret_id' => $ret_id))->save();
				$json_res['error'] = 0;
				$json_res['msg'] = L('activation_success');
				$json_res['url'] = url('user/refound/index');
			}
			else {
				$json_res['error'] = 1;
				$json_res['msg'] = sprintf(L('activation_number_msg'), $activation_number_type);
			}

			exit(json_encode($json_res));
		}
	}

	public function actionDeleteReturnOrder()
	{
		if (IS_POST) {
			$json_res = array('error' => 0, 'msg' => '', 'url' => '');
			$ret_id = input('ret_id', 0, 'intval');

			if (0 < $ret_id) {
				$user_id = $this->user_id;
				dao('order_return')->where(array('ret_id' => $ret_id, 'user_id' => $user_id))->delete();
				$json_res['error'] = 0;
				$json_res['msg'] = '已删除';
				$json_res['url'] = url('user/refound/index');
			}
			else {
				$json_res['error'] = 1;
				$json_res['msg'] = '删除失败';
			}

			exit(json_encode($json_res));
		}
	}
}

?>
