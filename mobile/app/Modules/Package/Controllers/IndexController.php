<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Package\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	public $area_id = 0;
	public $region_id = 0;

	public function __construct()
	{
		parent::__construct();
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		$area_info = get_area_info($this->province_id);
		$this->area_id = $area_info['region_id'];
		$where = 'regionId = \'' . $province_id . '\'';
		$date = array('parent_id');
		$this->region_id = get_table_date('region_warehouse', $where, $date, 2);
		if (isset($_COOKIE['region_id']) && !empty($_COOKIE['region_id'])) {
			$this->region_id = $_COOKIE['region_id'];
		}

		$this->region_id = $this->region_id ? $this->region_id : 0;
	}

	public function actionIndex()
	{
		$now = gmtime();
		$sql = 'SELECT * FROM ' . $this->ecs->table('goods_activity') . (' WHERE `start_time` <= \'' . $now . '\' AND `end_time` >= \'' . $now . '\' AND `act_type` = \'4\' AND `review_status` = \'3\' ORDER BY `end_time`');
		$res = $this->db->query($sql);
		$list = array();

		foreach ($res as $row) {
			$row['start_time'] = local_date('Y-m-d H:i', $row['start_time']);
			$row['end_time'] = local_date('Y-m-d H:i', $row['end_time']);
			$ext_arr = unserialize($row['ext_info']);
			unset($row['ext_info']);

			if ($ext_arr) {
				foreach ($ext_arr as $key => $val) {
					$row[$key] = $val;
				}
			}

			$sql = 'SELECT pg.package_id, pg.goods_id, pg.goods_number, pg.admin_id, ' . ' g.goods_sn, g.goods_name, g.market_price, g.goods_thumb, ' . (' IFNULL(mp.user_price, g.shop_price * \'' . $_SESSION['discount'] . '\') AS rank_price ') . ' FROM ' . $GLOBALS['ecs']->table('package_goods') . ' AS pg ' . '   LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . '   ON g.goods_id = pg.goods_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ' WHERE pg.package_id = ' . $row['act_id'] . ' ' . ' ORDER BY pg.goods_id';
			$goods_res = $GLOBALS['db']->getAll($sql);
			$subtotal = 0;
			$goods_number = 0;

			foreach ($goods_res as $key => $val) {
				$goods_res[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
				$goods_res[$key]['market_price'] = price_format($val['market_price']);
				$goods_res[$key]['rank_price'] = price_format($val['rank_price']);
				$goods_res[$key]['url'] = build_uri('goods', array('gid' => $val['goods_id']), $val['goods_name']);
				$subtotal += $val['rank_price'] * $val['goods_number'];
				$goods_number += $val['goods_number'];
			}

			$row['goods_list'] = $goods_res;
			$row['subtotal'] = price_format($subtotal);
			$row['saving'] = price_format(abs($subtotal - $row['package_price']));
			$row['package_price'] = price_format($row['package_price']);
			$row['package_number'] = $goods_number;
			$list[] = $row;
		}

		$this->assign('list', $list);
		$this->assign('area_id', $this->area_id);
		$this->assign('region_id', $this->region_id);
		$this->assign('page_title', L('package'));
		$this->display();
	}
}

?>
