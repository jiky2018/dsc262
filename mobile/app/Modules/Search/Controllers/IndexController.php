<?php
//zend by QQ:123456  商创-网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Search\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	public function actionIndex()
	{
		$this->assign('page_title', L('search'));
		$this->display();
	}

	public function actionHotkey()
	{
		$search_keywords = C('shop.search_keywords');
		$hot_keywords = array();

		if ($search_keywords) {
			$hot_keywords = explode(',', $search_keywords);
		}

		$history = '';

		if (!empty($_COOKIE['ECS']['keywords'])) {
			$history = explode(',', $_COOKIE['ECS']['keywords']);
			$history = array_unique($history);
		}

		$hotkey = array();
		$hotkey['hot_keywords'] = $hot_keywords;
		$hotkey['history'] = $history;
		$this->response(array('error' => 0, 'hotkey' => $hotkey));
	}

	public function actionSegoods()
	{
		if (IS_POST) {
			$kwords = input('kwords', '', array('htmlspecialchars', 'addslashes'));
			$pageSize = input('pageSize', 10, 'intval');
			$currentPage = input('currentPage', 1, 'intval');

			if ($currentPage == 1) {
				$current = 0;
			}
			else {
				$current = ($currentPage - 1) * $pageSize;
			}

			$keywords .= ' AND ';
			$val = mysql_like_quote(trim($kwords));
			$keywords .= '(goods_name LIKE \'%' . $val . '%\' OR goods_sn LIKE \'%' . $val . '%\' OR keywords LIKE \'%' . $val . '%\')';
			$where = 'g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ';

			if ($keywords) {
				$where .= ' AND (( 1 ' . $keywords . ' ) ) ';
			}

			$wherenum = '  LIMIT ' . $current . ' , ' . $pageSize . ' ';
			$sql = 'SELECT g.goods_id, g.user_id, g.goods_name, g.shop_price, g.promote_price, g.goods_name_style, g.comments_number,g.sales_volume,g.market_price, g.is_new, g.is_best, g.is_hot, g.promote_start_date, g.promote_end_date, g.is_promote, g.goods_brief, g.goods_thumb , g.goods_img ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . ('ON mp.goods_id = g.goods_id AND mp.user_rank = \'' . $_SESSION['user_rank'] . '\' ') . ('WHERE ' . $where . ' ' . $wherenum . ' ');
			$total_query = $GLOBALS['db']->query($sql);
			$sql = 'SELECT g.goods_id ' . 'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $leftJoin . 'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' . 'ON mp.goods_id = g.goods_id  ' . ('WHERE ' . $where . ' ');
			$number = $GLOBALS['db']->query($sql);

			foreach ($total_query as $key => $val) {
				$total_query[$key]['shop_price'] = price_format($val['shop_price']);
				$total_query[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
				$total_query[$key]['goods_img'] = get_image_path($val['goods_img']);
				$properties = get_goods_properties($val['goods_id'], $this->region_id, $this->area_info['region_id']);
				$total_query[$key]['specification'] = $properties['spe'];
			}

			$this->response(array('goods_list' => $total_query, 'total' => count($number)));
		}
	}

	public function actionSearchKeyword()
	{
		if (IS_AJAX) {
			$kwords = input('kwords', '', array('htmlspecialchars', 'addslashes'));
			$pageSize = input('pageSize', 10, 'intval');
			$currentPage = input('currentPage', 1, 'intval');

			if (!empty($kwords)) {
				$current = $currentPage == 1 ? 0 : ($currentPage - 1) * $pageSize;
				$map['keyword'] = array('like', $kwords . '%');
				$keywords_list = dao('keywords')->field('keyword')->cache('select_keyword', 60)->where($map)->group('keyword')->order('count DESC, date DESC')->limit($current, $pageSize)->select();
				$this->response(array('keywords_list' => $keywords_list, 'total' => count($keywords_list)));
			}
		}
	}
}

?>
