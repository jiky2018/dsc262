<?php

namespace App\Modules\Bargain\Controllers;

use App\Modules\Base\Controllers\FrontendController;

class IndexController extends FrontendController
{
    private $sort = 'last_update';
    private $order = 'ASC';

    public function __construct()
    {
        parent::__construct();
        $files = [
            'order',
            'clips',
            'payment',
            'transaction'
        ];
        $this->load_helper($files);
        $this->page = 1;
        $this->size = 10;
    }

    public function actionIndex()
    {
        $this->page = I('page', 1, 'intval');
        if (IS_AJAX) {
            $time = gmtime();
            $where .= " g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.review_status>2 and bg.status != 1 and bg.is_delete !=1 and bg.is_audit = 2 and $time > bg.start_time and $time < bg.end_time ";

            $arr = [];
            $sql = 'SELECT bg.id,bg.bargain_name,bg.start_time,bg.end_time,bg.target_price,bg.total_num,g.goods_id, g.goods_name, g.shop_price, g.market_price, g.goods_thumb , g.goods_img FROM ' . $this->ecs->table('bargain_goods') . 'AS bg LEFT JOIN ' . $this->ecs->table('goods') . ' AS g ON bg.goods_id = g.goods_id ' .
                "WHERE $where ";
            $goods_list = $this->db->query($sql);
            $total = is_array($goods_list) ? count($goods_list) : 0;
            $res = $this->db->selectLimit($sql, $this->size, ($this->page - 1) * $this->size);
            foreach ($res as $key => $val) {
                $arr[$key]['goods_id'] = $val['goods_id'];
                $arr[$key]['bargain_id'] = $val['id'];
                $arr[$key]['goods_name'] = $val['goods_name'];
                $arr[$key]['bargain_name'] = $val['bargain_name'];
                $arr[$key]['shop_price'] = price_format($val['shop_price']);
                $target_price = get_bargain_target_price($val['id']);
                if($target_price){
                    $arr[$key]['target_price'] = price_format($target_price);
                }else{
                    $arr[$key]['target_price'] = price_format($val['target_price']);
                }
                $arr[$key]['goods_img'] = get_image_path($val['goods_img']);
                $arr[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
                $arr[$key]['total_num'] = $val['total_num'];
                $arr[$key]['url'] = url('bargain/goods/index', ['id' => $val['id']]);
            }

            exit(json_encode(['list' => array_values($arr), 'totalPage' => ceil($total / $this->size)]));
        }
        $this->assign('page_title', '砍价首页');
        $this->display();
    }


}
