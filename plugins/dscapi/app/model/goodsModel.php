<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace app\model;

abstract class goodsModel extends \app\func\common
{
	private $alias_config;

	public function __construct()
	{
		$this->goodsModel();
	}

	public function goodsModel($table = '')
	{
		$this->alias_config = array('goods' => 'g', 'warehouse_goods' => 'wg', 'warehouse_area_goods' => 'wag', 'goods_gallery' => 'gll', 'goods_attr' => 'ga', 'goods_transport' => 'gtt', 'goods_transport_express' => 'gtes', 'goods_transport_extend' => 'gted');

		if ($table) {
			return $this->alias_config[$table];
		}
		else {
			return $this->alias_config;
		}
	}

	public function get_where($val = array(), $alias = '')
	{
		$where = 1;
		$where .= \app\func\base::get_where($val['goods_id'], $alias . 'goods_id');
		$where .= \app\func\base::get_where($val['goods_sn'], $alias . 'goods_sn');
		$where .= \app\func\base::get_where($val['bar_code'], $alias . 'bar_code');
		$where .= \app\func\base::get_where($val['cat_id'], $alias . 'cat_id');

		if (0 < $val['brand_id']) {
			$val['brand_id'] = \app\func\base::get_del_str_comma($val['brand_id']);
			$seller_brand = \app\func\base::get_link_seller_brand($val['brand_id']);

			if ($seller_brand) {
				$brand_id = $seller_brand['brand_id'] . ',' . $val['brand_id'];
				$brand_id = \app\func\base::get_del_str_comma($brand_id);
				$brand_id = explode(',', $brand_id);
				$val['brand_id'] = array_unique($brand_id);
			}
		}

		$where .= \app\func\base::get_where($val['brand_id'], $alias . 'brand_id');
		$where .= \app\func\base::get_where($val['user_cat'], $alias . 'user_cat');

		if (0 < $val['seller_type']) {
			$where .= \app\func\base::get_where($val['seller_id'], $alias . 'ru_id');
		}
		else {
			$where .= \app\func\base::get_where($val['seller_id'], $alias . 'user_id');
		}

		$where .= \app\func\base::get_where($val['w_id'], $alias . 'w_id');
		$where .= \app\func\base::get_where($val['a_id'], $alias . 'a_id');
		$where .= \app\func\base::get_where($val['region_id'], $alias . 'region_id');
		$where .= \app\func\base::get_where($val['region_sn'], $alias . 'region_sn');
		$where .= \app\func\base::get_where($val['img_id'], $alias . 'img_id');
		$where .= \app\func\base::get_where($val['attr_id'], $alias . 'attr_id');
		$where .= \app\func\base::get_where($val['goods_attr_id'], $alias . 'goods_attr_id');
		$where .= \app\func\base::get_where($val['tid'], $alias . 'tid');
		return $where;
	}

	public function get_select_list($table, $select, $where, $page_size, $page, $sort_by, $sort_order)
	{
		$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where;
		$result['record_count'] = $GLOBALS['db']->getOne($sql);

		if ($sort_by) {
			$where .= ' ORDER BY ' . $sort_by . ' ' . $sort_order . ' ';
		}

		$where .= ' LIMIT ' . ($page - 1) * $page_size . (',' . $page_size);
		$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where;
		$result['list'] = $GLOBALS['db']->getAll($sql);
		return $result;
	}

	public function get_join_select_list($table, $select, $where, $join_on = array())
	{
		$result = \app\func\base::get_join_table($table, $join_on, $select, $where, 1);
		return $result;
	}

	public function get_select_info($table, $select, $where)
	{
		$sql = 'SELECT ' . $select . ' FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where . ' LIMIT 1';
		$goods = $GLOBALS['db']->getRow($sql);
		return $goods;
	}

	public function get_join_select_info($table, $select, $where, $join_on)
	{
		$goods = \app\func\base::get_join_table($table, $join_on, $select, $where, 2);
		return $goods;
	}

	public function get_insert($table, $select, $format)
	{
		$goodsLang = \languages\goodsLang::lang_goods_insert();
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $select, 'INSERT');
		$id = $GLOBALS['db']->insert_id();
		$common_data = array('result' => empty($id) ? 'failure' : 'success', 'msg' => empty($id) ? $goodsLang['msg_failure']['failure'] : $goodsLang['msg_success']['success'], 'error' => empty($id) ? $goodsLang['msg_failure']['error'] : $goodsLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_more_insert($table, $select, $format)
	{
		$goodsLang = \languages\goodsLang::lang_goods_insert();
		$first_table = $table[0];
		$first_select = $select[0];
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($first_table), $first_select, 'INSERT');
		$tid = $GLOBALS['db']->insert_id();

		for ($i = 0; $i < count($table); $i++) {
			if (0 < $i && $table[$i]) {
				if ($select[$i]) {
					$select[$i]['tid'] = $tid;
				}

				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table[$i]), $select[$i], 'INSERT');
			}
		}

		$common_data = array('result' => empty($tid) ? 'failure' : 'success', 'msg' => empty($tid) ? $goodsLang['msg_failure']['failure'] : $goodsLang['msg_success']['success'], 'error' => empty($tid) ? $goodsLang['msg_failure']['error'] : $goodsLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_update($table, $select, $where, $format)
	{
		$goodsLang = \languages\goodsLang::lang_goods_update();

		if (strlen($where) != 1) {
			$info = $this->get_select_info($table, '*', $where);

			if (!$info) {
				$common_data = array('result' => 'failure', 'msg' => $goodsLang['null_failure']['failure'], 'error' => $goodsLang['null_failure']['error'], 'format' => $format);
			}
			else {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $select, 'UPDATE', $where);
				$common_data = array('result' => empty($select) ? 'failure' : 'success', 'msg' => empty($select) ? $goodsLang['msg_failure']['failure'] : $goodsLang['msg_success']['success'], 'error' => empty($select) ? $goodsLang['msg_failure']['error'] : $goodsLang['msg_success']['error'], 'format' => $format);
			}
		}
		else {
			$common_data = array('result' => 'failure', 'msg' => $goodsLang['where_failure']['failure'], 'error' => $goodsLang['where_failure']['error'], 'format' => $format);
		}

		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_more_update($table, $select, $where, $format)
	{
		$goodsLang = \languages\goodsLang::lang_goods_update();

		if (strlen($where) != 1) {
			$first_table = $table[0];
			$first_select = $select[0];
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($first_table), $first_select, 'UPDATE', $where);

			for ($i = 0; $i < count($table); $i++) {
				if (0 < $i && $table[$i]) {
					if ($select[$i]) {
						$select[$i]['tid'] = $this->tid;
					}

					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table[$i]), $select[$i], 'UPDATE', $where);
				}
			}

			$common_data = array('result' => empty($select) ? 'failure' : 'success', 'msg' => empty($select) ? $goodsLang['msg_failure']['failure'] : $goodsLang['msg_success']['success'], 'error' => empty($select) ? $goodsLang['msg_failure']['error'] : $goodsLang['msg_success']['error'], 'format' => $format);
		}
		else {
			$common_data = array('result' => 'failure', 'msg' => $goodsLang['where_failure']['failure'], 'error' => $goodsLang['where_failure']['error'], 'format' => $format);
		}

		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_delete($table, $where, $format)
	{
		$goodsLang = \languages\goodsLang::lang_goods_delete();

		if (strlen($where) != 1) {
			$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table($table) . ' WHERE ' . $where;
			$GLOBALS['db']->query($sql);
			$common_data = array('result' => 'success', 'msg' => $goodsLang['msg_success']['success'], 'error' => $goodsLang['msg_success']['error'], 'format' => $format);
		}
		else {
			$common_data = array('result' => 'failure', 'msg' => $goodsLang['where_failure']['failure'], 'error' => $goodsLang['where_failure']['error'], 'format' => $format);
		}

		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_more_delete($table, $where, $format)
	{
		$goodsLang = \languages\goodsLang::lang_goods_delete();

		if (strlen($where) != 1) {
			for ($i = 0; $i < count($table); $i++) {
				$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table($table[$i]) . ' WHERE ' . $where;
				$GLOBALS['db']->query($sql);
			}

			$common_data = array('result' => 'success', 'msg' => $goodsLang['msg_success']['success'], 'error' => $goodsLang['msg_success']['error'], 'format' => $format);
		}
		else {
			$common_data = array('result' => 'failure', 'msg' => $goodsLang['where_failure']['failure'], 'error' => $goodsLang['where_failure']['error'], 'format' => $format);
		}

		\app\func\common::common($common_data);
		return \app\func\common::data_back();
	}

	public function get_list_common_data($result, $page_size, $page, $goodsLang, $format)
	{
		$common_data = array('page_size' => $page_size, 'page' => $page, 'result' => empty($result['record_count']) ? 'failure' : 'success', 'msg' => empty($result['record_count']) ? $goodsLang['msg_failure']['failure'] : $goodsLang['msg_success']['success'], 'error' => empty($result['record_count']) ? $goodsLang['msg_failure']['error'] : $goodsLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$result = \app\func\common::data_back($result, 1);
		return $result;
	}

	public function get_info_common_data_fs($goods, $goodsLang, $format)
	{
		$common_data = array('result' => empty($goods) ? 'failure' : 'success', 'msg' => empty($goods) ? $goodsLang['msg_failure']['failure'] : $goodsLang['msg_success']['success'], 'error' => empty($goods) ? $goodsLang['msg_failure']['error'] : $goodsLang['msg_success']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$goods = \app\func\common::data_back($goods);
		return $goods;
	}

	public function get_info_common_data_f($goodsLang, $format)
	{
		$goods = array();
		$common_data = array('result' => 'failure', 'msg' => $goodsLang['where_failure']['failure'], 'error' => $goodsLang['where_failure']['error'], 'format' => $format);
		\app\func\common::common($common_data);
		$goods = \app\func\common::data_back($goods);
		return $goods;
	}

	public function get_goods_batch_insert($table, $select, $format)
	{
		$goodsLang = \languages\goodsLang::lang_goods_batch_insert();

		if (!empty($select)) {
			$proc_thumb = isset($GLOBALS['shop_id']) && 0 < $GLOBALS['shop_id'] ? false : true;
			$admin_temp_dir = ROOT_PATH . '/temp' . '/seller/' . 'admin_0';

			if (!file_exists($admin_temp_dir)) {
				\app\func\common::make_dir($admin_temp_dir);
			}

			$sql = 'SELECT cat_id FROM' . $GLOBALS['ecs']->table('goods_type') . 'WHERE cat_name = \'cloud\' LIMIT 1';
			$cat_id = $GLOBALS['db']->getOne($sql);
			if ($cat_id == 0 || $cat_id == '') {
				$goods_type = array('cat_name' => 'cloud', 'enabled' => '1');
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('goods_type'), $goods_type, 'INSERT');
				$cat_id = $GLOBALS['db']->insert_id();
			}

			foreach ($select as $k => $v) {
				$goods = array();
				$shop_price = !empty($v['suggestedPrice']) ? trim($v['suggestedPrice']) : 0;

				if (0 < $shop_price) {
					$shop_price = $shop_price / 100;
				}

				$shop_price = floatval($shop_price);
				$goods['goods_name'] = !empty($v['name']) ? trim(addslashes($v['name'])) : '';
				$goods['bar_code'] = !empty($v['goodsCode']) ? trim($v['goodsCode']) : '';
				$goods['shop_price'] = $shop_price;
				$goods['goods_unit'] = !empty($v['unit']) ? trim($v['unit']) : '个';
				$goods['cloud_id'] = intval($v['id']);
				$goods['review_status'] = 3;
				$goods['goods_type'] = $cat_id;
				$goods['freight'] = 0;
				$goods['cloud_goodsname'] = !empty($v['name']) ? trim(addslashes($v['name'])) : '';
				$goods['goods_cause'] = '1,3';
				$id = 0;
				$sql = 'SELECT goods_id FROM' . $GLOBALS['ecs']->table($table) . 'WHERE cloud_id = \'' . $v['id'] . '\' LIMIT 1';
				$goods_id = $GLOBALS['db']->getOne($sql);

				if (0 < $goods_id) {
					$id = $goods_id;
					$sql_where = ' goods_id = \'' . $id . '\'';
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $goods, 'UPDATE', $sql_where);
					$sql = 'SELECT img_original , img_url , thumb_url FROM' . $GLOBALS['ecs']->table('goods_gallery') . ('WHERE goods_id = \'' . $id . '\'');
					$goods_gallery_list = $GLOBALS['db']->getAll($sql);

					if (!empty($goods_gallery_list)) {
						foreach ($goods_gallery_list as $gallery_list_key => $gallery_list_val) {
							if ($gallery_list_val['img_original'] != '' && strpos($gallery_list_val['img_original'], 'http://') === false && strpos($gallery_list_val['img_original'], 'https://') === false) {
								@unlink(ROOT_PATH . $gallery_list_val['img_original']);
							}

							if ($gallery_list_val['img_url'] != '' && strpos($gallery_list_val['img_url'], 'http://') === false && strpos($gallery_list_val['img_url'], 'https://') === false) {
								@unlink(ROOT_PATH . $gallery_list_val['img_url']);
							}

							if ($gallery_list_val['thumb_url'] != '' && strpos($gallery_list_val['thumb_url'], 'http://') === false && strpos($gallery_list_val['thumb_url'], 'https://') === false) {
								@unlink(ROOT_PATH . $gallery_list_val['thumb_url']);
							}
						}
					}

					$sql = 'DELETE FROM' . $GLOBALS['ecs']->table('goods_gallery') . ('WHERE goods_id = \'' . $id . '\'');
					$GLOBALS['db']->query($sql);
				}
				else {
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $goods, 'INSERT');
					$id = $GLOBALS['db']->insert_id();
				}

				if ($id == 0) {
					continue;
				}

				if ($v['goodsDetailList']) {
					$j = 1;
					$goods_desc = '';

					foreach ($v['goodsDetailList'] as $detail_key => $detail_val) {
						if (!empty($detail_val['imagePath']) && $detail_val['imagePath'] != 'http://' && (strpos($detail_val['imagePath'], 'http://') !== false || strpos($detail_val['imagePath'], 'https://') !== false)) {
							if ($j == $detail_val['orderNo']) {
								$goods_desc .= '<p><img src="' . $detail_val['imagePath'] . '" title="' . basename($detail_val['imagePath']) . '"/></p>';
							}

							$j++;
						}
					}

					$arr['goods_desc'] = $goods_desc;
					$sql_where = ' goods_id = \'' . $id . '\'';
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $arr, 'UPDATE', $sql_where);
				}

				if ($v['goodsNavigateList']) {
					$i = 0;

					foreach ($v['goodsNavigateList'] as $gallery_key => $gallery_val) {
						$i++;
						if (!empty($gallery_val['navigateImage']) && $gallery_val['navigateImage'] != 'http://' && (strpos($gallery_val['navigateImage'], 'http://') !== false || strpos($gallery_val['navigateImage'], 'https://') !== false)) {
							if (\app\func\common::get_http_basename($gallery_val['navigateImage'], $admin_temp_dir)) {
								$image_url = trim($gallery_val['navigateImage']);
								$down_img = $admin_temp_dir . '/' . basename($image_url);
								$img_wh = \app\func\common::get_width_to_height($down_img, $GLOBALS['_CFG']['image_width'], $GLOBALS['_CFG']['image_height']);
								$GLOBALS['_CFG']['image_width'] = isset($img_wh['image_width']) ? $img_wh['image_width'] : $GLOBALS['_CFG']['image_width'];
								$GLOBALS['_CFG']['image_height'] = isset($img_wh['image_height']) ? $img_wh['image_height'] : $GLOBALS['_CFG']['image_height'];
								$goods_img = \app\func\common::make_thumb(array('img' => $down_img, 'type' => 1), $GLOBALS['_CFG']['image_width'], $GLOBALS['_CFG']['image_height']);

								if ($proc_thumb) {
									$thumb_url = \app\func\common::make_thumb(array('img' => $down_img, 'type' => 1), $GLOBALS['_CFG']['thumb_width'], $GLOBALS['_CFG']['thumb_height']);
									$thumb_url = \app\func\common::reformat_image_name('gallery_thumb', $id, $thumb_url, 'thumb');
								}
								else {
									$thumb_url = \app\func\common::make_thumb(array('img' => $down_img, 'type' => 1));
									$thumb_url = \app\func\common::reformat_image_name('gallery_thumb', $id, $thumb_url, 'thumb');
								}

								$img_original = \app\func\common::reformat_image_name('gallery', $id, $down_img, 'source');
								$img_url = \app\func\common::reformat_image_name('gallery', $id, $goods_img, 'goods');
								$goods_gallery = array('goods_id' => $id, 'img_original' => $img_original, 'img_desc' => intval($gallery_val['orderNo']), 'img_url' => $img_url, 'thumb_url' => $thumb_url);
								$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('goods_gallery'), $goods_gallery, 'INSERT');

								if ($i == 1) {
									$goods_sn = \app\func\common::generate_goods_sn($id);
									$goods_arr = array('goods_thumb' => $img_original, 'goods_img' => $img_url, 'goods_thumb' => $thumb_url, 'goods_sn' => $goods_sn);
									$sql_where = ' goods_id = \'' . $id . '\'';
									$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $goods_arr, 'UPDATE', $sql_where);
								}

								@unlink($down_img);
							}
						}
					}
				}

				if ($v['specDesc']) {
					$specDesc = json_decode($v['specDesc'], true);

					if (!empty($specDesc)) {
						foreach ($specDesc as $specDesc_key => $specDesc_val) {
							$specDesc_arr = array('cloud_attr_id' => intval($specDesc_val['specificationId']), 'attr_name' => addslashes($specDesc_val['name']), 'cat_id' => $cat_id, 'attr_type' => 1);
							$sql = 'SELECT attr_id FROM' . $GLOBALS['ecs']->table('attribute') . 'WHERE (cloud_attr_id = \'' . $specDesc_val['specificationId'] . '\' OR attr_name = \'' . addslashes($specDesc_val['name']) . ('\') AND cat_id = \'' . $cat_id . '\' LIMIT 1');
							$attr_id = $GLOBALS['db']->getOne($sql);
							if ($attr_id == 0 || $attr_id == '') {
								$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('attribute'), $specDesc_arr, 'INSERT');
								$attr_id = $GLOBALS['db']->insert_id();
							}
							else {
								$sql_where = ' attr_id = \'' . $attr_id . '\'';
								$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('attribute'), $specDesc_arr, 'UPDATE', $sql_where);
							}

							if (!empty($specDesc_val['value'])) {
								foreach ($specDesc_val['value'] as $value_key => $value_val) {
									$value_arr = array('cloud_id' => intval($value_val['specificationDetailId']), 'attr_value' => addslashes($value_val['detailName']), 'attr_id' => $attr_id, 'goods_id' => $id, 'attr_gallery_flie' => $value_val['specificationDetailImage']);
									$sql = 'SELECT goods_attr_id FROM' . $GLOBALS['ecs']->table('goods_attr') . 'WHERE cloud_id = \'' . $value_val['specificationDetailId'] . ('\' AND goods_id = \'' . $id . '\' LIMIT 1');
									$goods_attr_id = $GLOBALS['db']->getOne($sql);
									if ($goods_attr_id == 0 || $goods_attr_id == '') {
										$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('goods_attr'), $value_arr, 'INSERT');
										$goods_attr_id = $GLOBALS['db']->insert_id();
									}
									else {
										$sql_where = ' goods_attr_id = \'' . $goods_attr_id . '\'';
										$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('goods_attr'), $value_arr, 'UPDATE', $sql_where);
									}
								}
							}
						}
					}
				}

				$goods_number = 0;
				$shop_pirce = 0;

				if ($v['products']) {
					$products = $v['products'];

					foreach ($products as $products_key => $products_val) {
						$insert_attr = array();
						$insert_attr['goods_id'] = $id;

						if ($products_val['specInfo']) {
							$sql = 'SELECT goods_attr_id FROM' . $GLOBALS['ecs']->table('goods_attr') . 'WHERE cloud_id in(' . $products_val['specInfo'] . (') AND goods_id = \'' . $id . '\' ');
							$goods_attr_arr = $GLOBALS['db']->getCol($sql);

							if ($goods_attr_arr) {
								$sql = 'DELETE FROM' . $GLOBALS['ecs']->table('products') . (' WHERE goods_id = \'' . $id . '\' AND cloud_product_id = \'') . intval($products_val['id']) . '\'';
								$GLOBALS['db']->query($sql);
								$product_price = !empty($products_val['inventory']['salePrice']) ? trim($products_val['inventory']['salePrice']) : 0;

								if (0 < $product_price) {
									$product_price = $product_price / 100;
								}

								$product_price = floatval($product_price);
								$insert_attr['product_price'] = $product_price;
								$insert_attr['goods_attr'] = implode('|', $goods_attr_arr);
								$insert_attr['product_sn'] = addslashes($products_val['productBn']);
								$insert_attr['product_number'] = intval($products_val['inventory']['inventoryNum']);
								$insert_attr['cloud_product_id'] = intval($products_val['id']);
								$insert_attr['inventoryid'] = intval($products_val['inventory']['id']);
								$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('products'), $insert_attr, 'INSERT');
								$goods_number += $insert_attr['product_number'];
								if ($shop_pirce == 0 || $product_price < $shop_price) {
									$shop_price = $product_price;
								}
							}
						}
					}
				}

				if ($goods_number || $shop_price) {
					$arr['goods_number'] = empty($goods_number) ? 0 : $goods_number;
					$arr['shop_price'] = empty($shop_price) ? 0 : $shop_price;
					$sql_where = ' goods_id = \'' . $id . '\'';
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table($table), $arr, 'UPDATE', $sql_where);
				}
			}

			$common_data = array('code' => $goodsLang['msg_success']['code'], 'message' => $goodsLang['msg_success']['message'], 'format' => $format);
		}
		else {
			$common_data = array('code' => $goodsLang['msg_failure']['code'], 'message' => $goodsLang['msg_failure']['message'], 'format' => $format);
		}

		\app\func\common::common($common_data);
		return \app\func\common::data_back('', 2);
	}

	public function get_goodsnotification_update($table, $select, $format)
	{
		$goodsLang = \languages\goodsLang::lang_goods_batch_insert();
		$common_data = array();

		if (!empty($select)) {
			$goods_id = 0;

			if (0 < $select['goodsId']) {
				$sql = 'SELECT goods_id FROM' . $GLOBALS['ecs']->table('goods') . 'WHERE cloud_id = \'' . $select['goodsId'] . '\' LIMIT 1';
				$goods_id = $GLOBALS['db']->getOne($sql);
			}

			$order = array();

			if ($select['orderSn']) {
				$sql = 'SELECT oi.order_id , oi.order_sn , oi.user_id,oi.surplus,oi.money_paid,oi.bonus_id,oi.integral_money,oi.bonus,oi.coupons FROM' . $GLOBALS['ecs']->table('order_goods') . 'AS og LEFT JOIN ' . $GLOBALS['ecs']->table('order_cloud') . 'AS oc ON oc.rec_id = og.rec_id LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' AS oi ON oi.order_id = og.order_id WHERE oc.parentordersn = \'' . $select['orderSn'] . '\'';
				$order = $GLOBALS['db']->getAll($sql);
			}

			switch ($select['mainType']) {
			case 101:
				if (0 < $goods_id) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . ' SET is_on_sale = \'1\' ,  last_update = \'' . gmtime() . '\' ' . ('WHERE goods_id = \'' . $goods_id . '\'');
					$GLOBALS['db']->query($sql);
					$sql = 'SELECT act_id FROM ' . $GLOBALS['ecs']->table('presale_activity') . (' WHERE goods_id =\'' . $goods_id . '\'');

					if ($GLOBALS['db']->getOne($sql, true)) {
						$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('presale_activity') . (' WHERE goods_id = \'' . $goods_id . '\''));
						$GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('cart') . (' WHERE goods_id = \'' . $goods_id . '\''));
					}
				}
				else {
					$common_data = array('code' => $goodsLang['msg_failure']['code'], 'message' => $goodsLang['msg_failure']['message'], 'format' => $format);
				}

				break;

			case 102:
				if (0 < $goods_id) {
					$sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . ' SET is_on_sale = \'0\' ,  last_update = \'' . gmtime() . '\' ' . ('WHERE goods_id = \'' . $goods_id . '\'');
					$GLOBALS['db']->query($sql);
					$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . (' WHERE goods_id = \'' . $goods_id . '\'');
					$GLOBALS['db']->query($sql);
				}
				else {
					$common_data = array('code' => $goodsLang['msg_failure']['code'], 'message' => $goodsLang['msg_failure']['message'], 'format' => $format);
				}

				break;

			case 201:
				if (!empty($order)) {
					foreach ($order as $k => $v) {
						$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), array('order_status' => OS_INVALID), 'UPDATE', 'order_id = \'' . $v['order_id'] . '\'');
						$log_time = gmtime();
						$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('order_action') . ' (order_id, action_user, order_status, shipping_status, pay_status, action_place, action_note, log_time) ' . 'SELECT ' . 'order_id, \'支付超时\', \'' . OS_INVALID . '\', \'' . SS_UNSHIPPED . '\', \'' . PS_UNPAYED . ('\', 0, \'支付超时\', \'' . $log_time . '\' ') . 'FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_sn = \'' . $v['order_sn'] . '\'';
						$GLOBALS['db']->query($sql);
						$this->return_user_surplus_integral_bonus($v);
					}
				}
				else {
					$common_data = array('code' => $goodsLang['msg_failure']['code'], 'message' => $goodsLang['msg_failure']['message'], 'format' => $format);
				}

				break;
			}
		}
		else {
			$common_data = array('code' => $goodsLang['msg_failure']['code'], 'message' => $goodsLang['msg_failure']['message'], 'format' => $format);
		}

		if (!empty($common_data)) {
			$common_data = array('code' => $goodsLang['msg_success']['code'], 'message' => $goodsLang['msg_success']['message'], 'format' => $format);
		}

		\app\func\common::common($common_data);
		return \app\func\common::data_back('', 2);
	}

	public function return_user_surplus_integral_bonus($order)
	{
		if (0 < $order['user_id'] && 0 < $order['surplus']) {
			$surplus = $order['money_paid'] < 0 ? $order['surplus'] + $order['money_paid'] : $order['surplus'];
			\app\func\common::log_account_change($order['user_id'], $surplus, 0, 0, 0, '由于取消、无效或退货操作，退回支付订单 ' . $order['order_sn'] . ' 时使用的预付款', ACT_OTHER);
			$GLOBALS['db']->query('UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET `order_amount` = \'0\' WHERE `order_id` =' . $order['order_id']);
		}

		if (0 < $order['user_id'] && 0 < $order['integral']) {
			\app\func\common::log_account_change($order['user_id'], 0, 0, 0, $order['integral'], '由于取消、无效或退货操作，退回支付订单' . $order['order_sn'] . ' 时使用的积分', ACT_OTHER);
		}

		if (0 < $order['bonus_id']) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_bonus') . ' SET order_id = 0, used_time = 0 ' . 'WHERE bonus_id = \'' . $order['bonus_id'] . '\' LIMIT 1';
			$GLOBALS['db']->query($sql);
		}

		if (0 < $order['coupons']) {
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('coupons_user') . ' SET order_id = 0, is_use_time = 0, is_use=0 ' . ('WHERE order_id = \'' . $order_id . '\' LIMIT 1');
			$GLOBALS['db']->query($sql);
		}

		$arr = array('bonus_id' => 0, 'bonus' => 0, 'integral' => 0, 'integral_money' => 0, 'surplus' => 0);
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $arr, 'UPDATE', 'order_id = \'' . $order['order_id'] . '\'');
	}
}

?>
