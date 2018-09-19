<?php

namespace App\Modules\Team\Controllers;

use App\Modules\Base\Controllers\FrontendController;

class GoodsController extends FrontendController
{
    private $user_id = 0;
    private $goods_id = 0;
    private $region_id = 0;
    private $area_info = array();

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        L(require(LANG_PATH . C('shop.lang') . '/team.php'));
        $files = array(
            'order',
            'clips',
            'payment',
            'transaction'
        );
        $this->load_helper($files);

        $this->user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $this->keywords = I('request.keywords');
        $this->goods_id = I('id', 0, 'intval');
        $this->team_id = I('team_id', 0, 'intval');

        $this->page = 1;
        $this->size = 10;
    }


    /**
     *拼团商品详情
     */
    public function actionIndex()
    {
        if ($this->goods_id == 0) {
            /* 如果没有传入id则跳回到首页 */
            ecs_header("Location: ./\n");
            exit;
        }

        //验证参团活动是否结束
        if ($this->team_id) {
            $team_info = team_is_failure($this->team_id);
            if ($team_info['is_team'] != 1) {
                show_message('该拼团活动已结束，去查看新的活动吧', '查看新的活动', url('team/index/userranking'), 'success');
            }
        }

        //商品信息
        $goods = get_goods_info($this->goods_id, $this->region_id, $this->area_info['region_id']);
        //拼团商品信息
        $team = $this->db->table('team_goods')->field('id,team_price,team_num,limit_num,astrict_num,is_team,team_desc')->where(array('goods_id' => $this->goods_id,'is_team' => '1'))->find();
        $goods['t_id'] =$team['id'];
        $goods['team_price'] = price_format($team['team_price']);
        $goods['team_num'] = $team['team_num'];
        $goods['limit_num'] = $team['limit_num'];
        $goods['astruct_num'] = $team['astrict_num'];
        $goods['team_desc'] = $team['team_desc'];

        //验证拼团活动是否结束
        if ($team['is_team'] == 0) {
            show_message('该拼团活动已结束，去查看新的活动吧', '查看新的活动', url('team/index/userranking'), 'success');
        }
        $info = $this->db->table('goods')->field('goods_desc')->where(array('goods_id' => $this->goods_id))->find();

        //ecmoban模板堂 --zhuo start 限购
        $start_date = $goods['xiangou_start_date'];
        $end_date = $goods['xiangou_end_date'];

        $nowTime = gmtime();
        if ($nowTime > $start_date && $nowTime < $end_date) {
            $xiangou = 1;
        } else {
            $xiangou = 0;
        }
        $order_goods = get_for_purchasing_goods($start_date, $end_date, $this->goods_id, $this->user_id);
        $this->assign('xiangou', $xiangou);
        $this->assign('orderG_number', $order_goods['goods_number']);

        // 获得商品的规格和属性
        $properties = get_goods_properties($this->goods_id, $this->region_id, $this->area_info['region_id']);


		if (empty($info['goods_desc'])) {
			$info['goods_desc'] = $link_desc;
		}
		$info['goods_desc'] = str_replace('src="images/upload', 'src="' . __STATIC__ . '/images/upload', $info['goods_desc']);
		$this->assign('goods_desc', $info['goods_desc']);

        //默认选中的商品规格
        $default_spe = '';
        if ($properties['spe']) {
            foreach ($properties['spe'] as $k => $v) {
                if ($v['attr_type'] == 1) {
                    if ($v['is_checked'] > 0) {
                        foreach ($v['values'] as $key => $val) {
                            $default_spe .= $val['checked'] ? $val['label'] . '、' : '';
                        }
                    } else {
                        foreach ($v['values'] as $key => $val) {
                            if ($key == 0) {
                                $default_spe .= $val['label'] . '、';
                            }
                        }
                    }
                }
            }
        }
        $this->assign('default_spe', $default_spe);
        $this->assign('properties', $properties['pro']);                                 // 商品规格
        $this->assign('specification', $properties['spe']);                        // 商品规格

        $this->assign('pictures', get_goods_gallery($this->goods_id));                    // 商品相册

        //购物车数量
        $cart_num = cart_number();
        $this->assign('cart_num', $cart_num);

        //评分 start
        $mc_all = ments_count_all($this->goods_id);       //总条数
        $mc_one = ments_count_rank_num($this->goods_id, 1);     //一颗星
        $mc_two = ments_count_rank_num($this->goods_id, 2);     //两颗星
        $mc_three = ments_count_rank_num($this->goods_id, 3);       //三颗星
        $mc_four = ments_count_rank_num($this->goods_id, 4);        //四颗星
        $mc_five = ments_count_rank_num($this->goods_id, 5);        //五颗星
        $comment_all = get_conments_stars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);
        if ($goods['user_id'] > 0) {
            //商家所有商品评分类型汇总
            $merchants_goods_comment = get_merchants_goods_comment($goods['user_id']);
            $this->assign('merch_cmt', $merchants_goods_comment);
        }
        $this->assign('comment_all', $comment_all);
        //查询一条好评
        $good_comment = get_good_comment($this->goods_id, 4, 1, 0, 1);
        $this->assign('good_comment', $good_comment);

        // 检查是否已经存在于用户的收藏夹
        if ($_SESSION ['user_id']) {
            $where['user_id'] = $_SESSION ['user_id'];
            $where['goods_id'] = $this->goods_id;
            $rs = $this->db->table('collect_goods')->where($where)->count();
            if ($rs > 0) {
                $this->assign('goods_collect', 1);
            }
        }

        //店铺关注人数 by wanglu
        $sql = "SELECT count(*) FROM " . $this->ecs->table('collect_store') . " WHERE ru_id = " . $goods['user_id'];
        $collect_number = $this->db->getOne($sql);
        $this->assign('collect_number', $collect_number ? $collect_number : 0);
        //评分 end
        $sql = "select b.is_IM,a.ru_id,a.province, a.city, a.kf_type, a.kf_ww, a.kf_qq, a.meiqia, a.shop_name, a.kf_appkey,kf_secretkey from {pre}seller_shopinfo as a left join {pre}merchants_shop_information as b on a.ru_id=b.user_id where a.ru_id='" . $goods['user_id'] . "' ";
        $basic_info = $this->db->getRow($sql);

        $info_ww = $basic_info['kf_ww'] ? explode("\r\n", $basic_info['kf_ww']) : '';
        $info_qq = $basic_info['kf_qq'] ? explode("\r\n", $basic_info['kf_qq']) : '';
        $kf_ww = $info_ww ? $info_ww[0] : '';
        $kf_qq = $info_qq ? $info_qq[0] : '';
        $basic_ww = $kf_ww ? explode('|', $kf_ww) : '';
        $basic_qq = $kf_qq ? explode('|', $kf_qq) : '';
        $basic_info['kf_ww'] = $basic_ww ? $basic_ww[1] : '';
        $basic_info['kf_qq'] = $basic_qq ? $basic_qq[1] : '';

        if (($basic_info['is_im'] == 1 || $basic_info['ru_id'] == 0) && !empty($basic_info['kf_appkey'])) {
            $basic_info['kf_appkey'] = $basic_info['kf_appkey'];
        } else {
            $basic_info['kf_appkey'] = '';
        }

        $basic_date = array('region_name');
        $basic_info['province'] = get_table_date('region', "region_id = '" . $basic_info['province'] . "'", $basic_date, 2);
        $basic_info['city'] = get_table_date('region', "region_id= '" . $basic_info['city'] . "'", $basic_date, 2) . "市";

        $this->assign('basic_info', $basic_info);

        //获取拼团新品
        $new_goods = team_new_goods('is_new', $goods['user_id']);
        $this->assign('new_goods', $new_goods);


        //获取该商品已成功开团信息
        $team_log = team_goods_log($this->goods_id);
        $this->assign('team_log', $team_log);
        $this->assign('team_id', $this->team_id);
        $this->assign('goods', $goods);
        $this->assign('goods_id', $goods['goods_id']);
        $this->assign('keywords', $goods['keywords']);       // 商品关键词
        $this->assign('description', $goods['goods_brief']);    // 商品简单描述

        // 微信JSSDK分享
        $share_data = array(
            'title' => '拼团商品_' . $goods['goods_name'],
            'desc' => $goods['goods_brief'],
            'link' => '',
            'img' => $goods['goods_img'],
        );
        $this->assign('share_data', $this->get_wechat_share_content($share_data));
        /** 判断客服目录 */
        if (is_dir(dirname(ROOT_PATH) . '/kefu')) {
            $this->assign('kefu', 1);
        }
        $this->assign('page_title', $goods['goods_name']);
        $this->display();
    }


    /**
     * 商品详情
     */
    public function actionInfo()
    {
        $info = $this->db->table('goods')->field('goods_desc,desc_mobile')->where(array('goods_id' => $this->goods_id))->find();
        $properties = get_goods_properties($this->goods_id, $this->region_id, $this->area_info['region_id']);  // 获得商品的规格和属性
        // 查询关联商品描述
        $sql = "SELECT ld.goods_desc FROM {pre}link_desc_goodsid AS dg, {pre}link_goods_desc AS ld WHERE dg.goods_id = {$this->goods_id}  AND dg.d_id = ld.id";
        $link_desc = $this->db->getOne($sql);
        if (!empty($info['desc_mobile'])) {
            $goods_desc = preg_replace('/<div[^>]*(tools)[^>]*>(.*?)<\/div>(.*?)<\/div>/is', '', $info['desc_mobile']);
        }
        if (empty($info['desc_mobile']) && !empty($info['goods_desc'])) {
            if (C('shop.open_oss') == 1) {
                $bucket_info = get_bucket_info();
                $bucket_info['endpoint'] = empty($bucket_info['endpoint']) ? $bucket_info['outside_site'] : $bucket_info['endpoint'];
                $goods_desc = str_replace(array('src="/images/upload', 'src="images/upload'), 'src="' . $bucket_info['endpoint'] . 'images/upload', $info['goods_desc']);
            } else {
                $goods_desc = str_replace(array('src="/images/upload', 'src="images/upload'), 'src="' . __STATIC__ . '/images/upload', $info['goods_desc']);
            }
        }
        if (empty($info['desc_mobile']) && empty($info['goods_desc'])) {
            $goods_desc = $link_desc;
        }
        $goods_desc = preg_replace("/height\=\"[0-9]+?\"/", "", $goods_desc);
        $goods_desc = preg_replace("/width\=\"[0-9]+?\"/", "", $goods_desc);
        $goods_desc = preg_replace("/style=.+?[*|\"]/i", "", $goods_desc);
        $this->assign('goods_desc', $goods_desc);
        // 商品属性
        $this->assign('properties', $properties['pro']);
        $this->assign('page_title', L('team_goods_info'));
        $this->assign('goods_id', $this->goods_id);
        $this->display();
    }


    /**
     * 商品评论列表
     */
    public function actionComment($img = 0)
    {
        if (IS_AJAX) {
            $rank = I('rank', '');
            $page = I('page');
            $page = ($page - 1) * $this->size;
            if ($rank == 'img') {
                $rank = 5;
                $img = 1;
            }
            $arr = get_good_comment_as($this->goods_id, $rank, 1, $page, $this->size);
            $comments = $arr['arr'];
            if ($img) {
                foreach ($comments as $key => $val) {
                    if ($val['thumb'] == 0) {
                        unset($comments[$key]);
                    }
                }
                $rank = 'img';
            }
            $show = count($comments) > 0 ? 1 : 0;
            $max = $page > 0 ? 0 : 1;
            die(json_encode(array('comments' => $comments, 'rank' => $rank, 'show' => $show, 'reset' => $max, 'totalPage' => $arr['max'], 'top' => 1)));
        }
        $this->assign('img', $img);
        $this->assign('info', commentCol($this->goods_id));
        $this->assign('id', $this->goods_id);
        $this->assign('page_title', L('team_goods_comment'));
        $this->display();
    }


    /**
     * 改变属性、数量时重新计算商品价格
     */
    public function actionPrice()
    {
        $res = array('err_msg' => '', 'result' => '', 'qty' => 1);
        $attr = I('attr');
        $number = I('number', 1, 'intval');
        $attr_id = !empty($attr) ? explode(',', $attr) : array();
        $warehouse_id = I('request.warehouse_id', 0, 'intval');
        $area_id = I('request.area_id', 0, 'intval'); //仓库管理的地区ID
        $onload = I('request.onload', '', 'trim');
        ; //仓库管理的地区ID

        $goods_attr = isset($_REQUEST['goods_attr']) ? explode(',', $_REQUEST['goods_attr']) : array();
        $attr_ajax = get_goods_attr_ajax($this->goods_id, $goods_attr, $attr_id);

        $goods = get_goods_info($this->goods_id, $warehouse_id, $area_id);

        if ($this->goods_id == 0) {
            $res['err_msg'] = L('err_change_attr');
            $res['err_no'] = 1;
        } else {
            if ($number == 0) {
                $res['qty'] = $number = 1;
            } else {
                $res['qty'] = $number;
            }
            //ecmoban模板堂 --zhuo start
            $products = get_warehouse_id_attr_number($this->goods_id, $_REQUEST['attr'], $goods['user_id'], $warehouse_id, $area_id);
            $attr_number = $products['product_number'];

            if ($goods['model_attr'] == 1) {
                $table_products = "products_warehouse";
                $type_files = " and warehouse_id = '$warehouse_id'";
            } elseif ($goods['model_attr'] == 2) {
                $table_products = "products_area";
                $type_files = " and area_id = '$area_id'";
            } else {
                $table_products = "products";
                $type_files = "";
            }

            $sql = "SELECT * FROM " . $GLOBALS['ecs']->table($table_products) . " WHERE goods_id = '$this->goods_id'" . $type_files . " LIMIT 0, 1";
            $prod = $GLOBALS['db']->getRow($sql);

            if ($goods['goods_type'] == 0) {
                $attr_number = $goods['goods_number'];
            } else {
                if (empty($prod)) { //当商品没有属性库存时
                    $attr_number = $goods['goods_number'];
                }

                if (!empty($prod) && $GLOBALS['_CFG']['add_shop_price'] == 0 && $onload == 'onload') { //当商品没有属性库存时
                    if (empty($attr_number)) {
                        $attr_number = $goods['goods_number'];
                    }
                }
            }

            $attr_number = !empty($attr_number) ? $attr_number : 0;
            $res['attr_number'] = $attr_number;

            //限制用户购买的数量 bywanglu
            $res['limit_number'] = $attr_number < $number ? ($attr_number ? $attr_number : 1) : $number;
            $shop_price = get_final_price($this->goods_id, $number, true, $attr_id, $warehouse_id, $area_id);
            //ecmoban模板堂 --zhuo end

            $res['shop_price'] = price_format($shop_price);
            $res['market_price'] = $goods['market_price'];

            $res['show_goods'] = 0;

            if ($goods_attr && $GLOBALS['_CFG']['add_shop_price'] == 0) {
                if (count($goods_attr) == count($attr_ajax['attr_id'])) {
                    $res['show_goods'] = 1;
                }
            }

            //属性价格
            $spec_price = get_final_price($this->goods_id, $number, true, $attr_id, $warehouse_id, $area_id, 1, 0, 0, $res['show_goods']);
            if ($GLOBALS['_CFG']['add_shop_price'] == 0 && empty($spec_price)) {
                $spec_price = $shop_price;
            }

            $res['spec_price'] = price_format($spec_price);

            $martetprice_amount = $spec_price + $goods['marketPrice'];
            $res['marketPrice_amount'] = price_format($spec_price + $goods['marketPrice']);

            //切换属性后的价格折扣 by wanglu
            $res['discount'] = round($shop_price / $martetprice_amount, 2) * 10;

            $res['result'] = price_format($shop_price); //商品价格不跟随增加数量而增加，之前代码  $res['result'] = price_format($shop_price * $number);


            if ($GLOBALS['_CFG']['add_shop_price'] == 0) {
                $res['result_market'] = price_format($goods['marketPrice']);//商品价格不跟随增加数量而增加，之前代码    $res['result_market'] = price_format($goods['marketPrice'] * $number);
            } else {
                $res['result_market'] = price_format($martetprice_amount);//商品价格不跟随增加数量而增加，之前代码    $res['result_market'] = price_format($martetprice_amount * $number);
            }
        }
        $goods_fittings = get_goods_fittings_info($this->goods_id, $warehouse_id, $area_id, '', 1);
        $fittings_list = get_goods_fittings(array($this->goods_id), $warehouse_id, $area_id);

        if ($fittings_list) {
            if (is_array($fittings_list)) {
                foreach ($fittings_list as $vo) {
                    $fittings_index[$vo['group_id']] = $vo['group_id'];//关联数组
                }
            }
            ksort($fittings_index);//重新排序

            $merge_fittings = get_merge_fittings_array($fittings_index, $fittings_list); //配件商品重新分组
            $fitts = get_fittings_array_list($merge_fittings, $goods_fittings);

            for ($i = 0; $i < count($fitts); $i++) {
                $fittings_interval = $fitts[$i]['fittings_interval'];

                $res['fittings_interval'][$i]['fittings_minMax'] = price_format($fittings_interval['fittings_min']) . "-" . number_format($fittings_interval['fittings_max'], 2, '.', '');
                $res['fittings_interval'][$i]['market_minMax'] = price_format($fittings_interval['market_min']) . "-" . number_format($fittings_interval['market_max'], 2, '.', '');

                if ($fittings_interval['save_minPrice'] == $fittings_interval['save_maxPrice']) {
                    $res['fittings_interval'][$i]['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']);
                } else {
                    $res['fittings_interval'][$i]['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']) . "-" . number_format($fittings_interval['save_maxPrice'], 2, '.', '');
                }

                $res['fittings_interval'][$i]['groupId'] = $fittings_interval['groupId'];
            }
        }


        if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
            $area_list = get_goods_link_area_list($this->goods_id, $goods['user_id']);
            if ($area_list['goods_area']) {
                if (!in_array($area_id, $area_list['goods_area'])) {
                    $res['err_no'] = 2;
                }
            } else {
                $res['err_no'] = 2;
            }
        }
        $attr_info = get_attr_value($this->goods_id, $attr_id[0]);
        if (!empty($attr_info['attr_img_flie'])) {
            $res['attr_img'] = get_image_path($attr_info['attr_img_flie']);
        }

        $res['onload'] = $onload;

        die(json_encode($res));
    }

    /**
     * 拼团改变属性、数量时重新计算商品价格
     */
    public function actionTeamprice()
    {
        $res = array('err_msg' => '', 'result' => '', 'qty' => 1);
        $attr = I('attr');
        $number = I('number', 1, 'intval');
        $attr_id = !empty($attr) ? explode(',', $attr) : array();
        $warehouse_id = I('request.warehouse_id', 0, 'intval');
        $area_id = I('request.area_id', 0, 'intval'); //仓库管理的地区ID
        $onload = I('request.onload', '', 'trim');
        ; //仓库管理的地区ID

        $goods_attr = isset($_REQUEST['goods_attr']) ? explode(',', $_REQUEST['goods_attr']) : array();
        $attr_ajax = get_goods_attr_ajax($this->goods_id, $goods_attr, $attr_id);

        $goods = get_goods_info($this->goods_id, $warehouse_id, $area_id);
        //拼团商品信息
        $team = $this->db->table('team_goods')->field('team_price,team_num,astrict_num')->where(array('goods_id' => $this->goods_id,'is_team' => '1'))->find();
        $goods['team_price'] = price_format($team['team_price']);
        $goods['team_num'] = $team['team_num'];
        $goods['astruct_num'] = $team['astrict_num'];

        if ($this->goods_id == 0) {
            $res['err_msg'] = L('err_change_attr');
            $res['err_no'] = 1;
        } else {
            if ($number == 0) {
                $res['qty'] = $number = 1;
            } else {
                $res['qty'] = $number;
            }
            //ecmoban模板堂 --zhuo start
            $products = get_warehouse_id_attr_number($this->goods_id, $_REQUEST['attr'], $goods['user_id'], $warehouse_id, $area_id);
            $attr_number = $products['product_number'];

            if ($goods['model_attr'] == 1) {
                $table_products = "products_warehouse";
                $type_files = " and warehouse_id = '$warehouse_id'";
            } elseif ($goods['model_attr'] == 2) {
                $table_products = "products_area";
                $type_files = " and area_id = '$area_id'";
            } else {
                $table_products = "products";
                $type_files = "";
            }

            $sql = "SELECT * FROM " . $GLOBALS['ecs']->table($table_products) . " WHERE goods_id = '$this->goods_id'" . $type_files . " LIMIT 0, 1";
            $prod = $GLOBALS['db']->getRow($sql);

            if ($goods['goods_type'] == 0) {
                $attr_number = $goods['goods_number'];
            } else {
                if (empty($prod)) { //当商品没有属性库存时
                    $attr_number = $goods['goods_number'];
                }

                if (!empty($prod) && $GLOBALS['_CFG']['add_shop_price'] == 0) { //当商品没有属性库存时
                    if (empty($attr_number)) {
                        $attr_number = $goods['goods_number'];
                    }
                }
            }

            $attr_number = !empty($attr_number) ? $attr_number : 0;
            $res['attr_number'] = $attr_number;

            //限制用户购买的数量 bywanglu
            $res['limit_number'] = $attr_number < $number ? ($attr_number ? $attr_number : 1) : $number;
            $shop_price = tean_get_final_price($this->goods_id, $number, true, $attr_id, $warehouse_id, $area_id);
            //ecmoban模板堂 --zhuo end

            $res['shop_price'] = price_format($shop_price);
            $res['market_price'] = $goods['market_price'];

            $res['show_goods'] = 0;

            if ($goods_attr && $GLOBALS['_CFG']['add_shop_price'] == 0) {
                if (count($goods_attr) == count($attr_ajax['attr_id'])) {
                    $res['show_goods'] = 1;
                }
            }

            //属性价格
            $spec_price = tean_get_final_price($this->goods_id, $number, true, $attr_id, $warehouse_id, $area_id, 1, 0, 0, $res['show_goods']);
            if ($GLOBALS['_CFG']['add_shop_price'] == 0 && empty($spec_price)) {
                $spec_price = $shop_price;
            }

            $res['spec_price'] = price_format($spec_price);

            $martetprice_amount = $spec_price + $goods['marketPrice'];
            $res['marketPrice_amount'] = price_format($spec_price + $goods['marketPrice']);

            //切换属性后的价格折扣 by wanglu
            $res['discount'] = round($shop_price / $martetprice_amount, 2) * 10;
            $res['result'] = price_format($shop_price);//商品价格不跟随增加数量而增加，之前代码 $res['result'] = price_format($shop_price * $number);
        }
        $goods_fittings = get_goods_fittings_info($this->goods_id, $warehouse_id, $area_id, '', 1);
        $fittings_list = get_goods_fittings(array($this->goods_id), $warehouse_id, $area_id);

        if ($fittings_list) {
            if (is_array($fittings_list)) {
                foreach ($fittings_list as $vo) {
                    $fittings_index[$vo['group_id']] = $vo['group_id'];//关联数组
                }
            }
            ksort($fittings_index);//重新排序

            $merge_fittings = get_merge_fittings_array($fittings_index, $fittings_list); //配件商品重新分组
            $fitts = get_fittings_array_list($merge_fittings, $goods_fittings);

            for ($i = 0; $i < count($fitts); $i++) {
                $fittings_interval = $fitts[$i]['fittings_interval'];

                $res['fittings_interval'][$i]['fittings_minMax'] = price_format($fittings_interval['fittings_min']) . "-" . number_format($fittings_interval['fittings_max'], 2, '.', '');
                $res['fittings_interval'][$i]['market_minMax'] = price_format($fittings_interval['market_min']) . "-" . number_format($fittings_interval['market_max'], 2, '.', '');

                if ($fittings_interval['save_minPrice'] == $fittings_interval['save_maxPrice']) {
                    $res['fittings_interval'][$i]['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']);
                } else {
                    $res['fittings_interval'][$i]['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']) . "-" . number_format($fittings_interval['save_maxPrice'], 2, '.', '');
                }

                $res['fittings_interval'][$i]['groupId'] = $fittings_interval['groupId'];
            }
        }


        if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
            $area_list = get_goods_link_area_list($this->goods_id, $goods['user_id']);
            if ($area_list['goods_area']) {
                if (!in_array($area_id, $area_list['goods_area'])) {
                    $res['err_no'] = 2;
                }
            } else {
                $res['err_no'] = 2;
            }
        }
        $attr_info = get_attr_value($this->goods_id, $attr_id[0]);
        if (!empty($attr_info['attr_img_flie'])) {
            $res['attr_img'] = get_image_path($attr_info['attr_img_flie']);
        }

        $res['onload'] = $onload;
        die(json_encode($res));
    }

    /*
    *拼团商品 --> 购买
    */

    public function actionTeambuy()
    {
        $this->check_login();
        /* 查询：取得数量 */
        $number = I('number', 1, 'intval');
        $goods_id = I('goods_id', 0, 'intval'); //拼团商品id
        $t_id = I('t_id', 0, 'intval');         //拼团活动id
        $team_id = I('team_id', 0, 'intval');   //拼团开团id
        if ($team_id > 0) {
            $team = dao('order_info')->where(array("team_id" => $team_id,'user_id'=>$_SESSION['user_id']))->find();
            if (!empty($team)) {
                show_message('该团已经参加，请选择其他团', '', '', 'error');
            }
        }
        if (empty($goods_id)) {
            ecs_header("Location: ./\n");
            exit;
        }
        /* 查询：验证拼团限购数量 */
        if (!is_numeric($number) || $number <= 0) {
            show_message(L('invalid_number'), '', '', 'error');
        }

        //拼团商品信息
        $goods = team_goods_info($goods_id, $t_id);
        /* 查询：取得规格 */
        $specs = isset($_POST['goods_spec']) ? htmlspecialchars(trim($_POST['goods_spec'])) : '';
        /* 查询：取得规格 */
        $specs = '';
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'spec_') !== false) {
                $specs .= ',' . intval($value);
            }
        }
        $specs = trim($specs, ',');

        /* 查询：如果商品有规格则取规格商品信息 配件除外 */
        if ($specs) {
            $_specs = explode(',', $specs);
            $product_info = get_products_info($goods['goods_id'], $_specs, $warehouse_id, $this->area_id);
        }
        empty($product_info) ? $product_info = array('product_number' => 0, 'product_id' => 0) : '';

        if ($goods['model_attr'] == 1) {
            $table_products = "products_warehouse";
            $type_files = " and warehouse_id = '$warehouse_id'";
        } elseif ($goods['model_attr'] == 2) {
            $table_products = "products_area";
            $type_files = " and area_id = '$this->area_id'";
        } else {
            $table_products = "products";
            $type_files = "";
        }

        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table($table_products) . " WHERE goods_id = '" . $goods['goods_id'] . "'" . $type_files . " LIMIT 0, 1";
        $prod = $GLOBALS['db']->getRow($sql);

        /* 查询：判断数量是否足够 */
        if ($number > $goods['goods_number']) {
            show_message(L('gb_error_goods_lacking'), '', '', 'error');
        }
        /* 查询：验证拼团限购数量 */
        if ($number > $goods['astrict_num']) {
            show_message('已超过拼团限购数量', '', '', 'error');
        }
        /* 查询：查询规格名称和值，不考虑价格 */
        $attr_list = array();
        $sql = "SELECT a.attr_name, g.attr_value " .
            "FROM " . $GLOBALS['ecs']->table('goods_attr') . " AS g, " .
            $GLOBALS['ecs']->table('attribute') . " AS a " .
            "WHERE g.attr_id = a.attr_id " .
            "AND g.goods_attr_id " . db_create_in($specs);
        $res = $GLOBALS['db']->query($sql);
        foreach ($res as $row) {
            $attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
        }
        $goods_attr = join(chr(13) . chr(10), $attr_list);
        /* 更新：清空购物车中所有团购商品 */
        clear_cart(CART_TEAM_GOODS);

        //ecmoban模板堂 --zhuo start

        $area_info = get_area_info($this->province_id);
        $this->area_id = $area_info['region_id'];

        $where = "regionId = '$this->province_id'";
        $date = array('parent_id');
        $this->region_id = get_table_date('region_warehouse', $where, $date, 2);

        if (!empty($_SESSION['user_id'])) {
            $sess = "";
        } else {
            $sess = real_cart_mac_ip();
        }
        //ecmoban模板堂 --zhuo end
        $shop_price = tean_get_final_price($goods_id, $number, true, $_specs, $warehouse_id, $area_id);
        /* 更新：加入购物车 */
        //$goods_price = $goods['team_price'];
        $goods_price = $shop_price;
        $cart = array(
            'user_id' => $_SESSION['user_id'],
            'session_id' => $sess,
            'goods_id' => $goods['goods_id'],
            'product_id' => $product_info['product_id'],
            'goods_sn' => addslashes($goods['goods_sn']),
            'goods_name' => addslashes($goods['goods_name']),
            'market_price' => $goods['market_price'],
            'goods_price' => $goods_price,
            'goods_number' => $number,
            'goods_attr' => addslashes($goods_attr),
            'goods_attr_id' => $specs,
            //ecmoban模板堂 --zhuo start
            'ru_id' => $goods['user_id'],
            'warehouse_id' => $this->region_id,
            'area_id' => $this->area_id,
            'add_time' => gmtime(),
            //ecmoban模板堂 --zhuo end
            'is_real' => $goods['is_real'],
            'extension_code' => addslashes($goods['extension_code']),
            'parent_id' => 0,
            'rec_type' => CART_TEAM_GOODS,
            'is_gift' => 0,
            'is_shipping' => $goods['is_shipping'],
            'is_checked' => 1
        );
        $this->db->autoExecute($GLOBALS['ecs']->table('cart'), $cart, 'INSERT');
        /* 更新：记录购物流程类型：团购 */
        $_SESSION['flow_type'] = CART_TEAM_GOODS;
        $_SESSION['extension_code'] = 'team_buy';
        $_SESSION['extension_id'] = '';
        $_SESSION['cart_value'] = '';
        $_SESSION['t_id'] = $t_id;
        if ($team_id > 0) {
            $_SESSION['team_id'] = $team_id;
        }
        /* 进入收货人页面 */
        $_SESSION['browse_trace'] = "team_buy";
        $this->redirect('team/flow/index');
        exit;
    }


    /**
     * 等待成团
     */
    public function actionTeamwait()
    {
        $this->team_id = I('team_id', 0, 'intval');
        $user_id = I('user_id', 0, 'intval');
        if ($this->team_id <= 0 || $user_id <= 0) {
            /* 如果没有传入id则跳回到首页 */
            ecs_header("Location: ./\n");
            exit;
        }

        $sql = "select order_id,order_status, pay_status from " . $this->ecs->table('order_info') . " where team_id = $this->team_id and user_id =$user_id  ";
        $res = $this->db->getRow($sql);
        if ($res['order_status'] == 2) {
            show_message('亲，您的拼团订单已取消', '查看订单', url('user/order/detail', array('order_id' => $res['order_id'])), 'success');
            exit;
        }
        if ($res['pay_status'] != PS_PAYED && $res['order_status'] != 2 && $res['order_status'] != 4) {
            show_message('亲，您的拼团订单没有支付', '请前去支付', url('user/order/detail', array('order_id' => $res['order_id'])), 'success');
            exit;
        }
        $sql = "select tl.team_id, tl.start_time,o.team_parent_id,g.goods_id,g.goods_img,g.goods_name,g.goods_brief,tg.validity_time ,tg.team_num ,tg.team_price from " . $this->ecs->table('team_log') . " as tl LEFT JOIN " . $this->ecs->table('order_info') . " as o ON tl.team_id = o.team_id LEFT JOIN  " . $this->ecs->table('goods') . " as g ON tl.goods_id = g.goods_id LEFT JOIN " . $this->ecs->table('team_goods') . " AS tg ON tl.t_id = tg.id  " . " where tl.team_id = $this->team_id  and o.extension_code ='team_buy' and o.team_parent_id > 0 ";
        $result = $this->db->query($sql);
        foreach ($result as $vo) {
            $goods['goods_id'] = $vo['goods_id'];
            $goods['goods_name'] = $vo['goods_name'];
            $goods['goods_img'] = get_image_path($vo['goods_img']);
            $goods['goods_brief'] = $vo['goods_brief'];
            $goods['team_id'] = $vo['team_id'];//开团id
            $goods['team_num'] = $vo['team_num'];
            $goods['team_price'] = price_format($vo['team_price']);

            //用户名、头像
            $user_nick = get_user_default($vo['team_parent_id']);
            $goods['user_name'] = encrypt_username($user_nick['nick_name']);
            $goods['headerimg'] = $user_nick['user_picture'];
        }

        /* --获取拼团信息-- */
        $sql = "select tl.team_id, tl.start_time,tl.goods_id,tl.status,g.validity_time ,g.team_num,g.team_price,g.is_team from " . $this->ecs->table('team_log') . " as tl LEFT JOIN " . $this->ecs->table('team_goods') . " as g ON tl.t_id = g.id where tl.team_id =$this->team_id  ";
        $team = $this->db->getRow($sql);
        $team['team_price'] = price_format($team['team_price']);
        $team['end_time'] = $team['start_time'] + ($team['validity_time'] * 3600);//剩余时间
        $surplus = surplus_num($team['team_id']);//几人参团
        $team['surplus'] = $team['team_num'] - $surplus;//还差几人
        $team['bar'] = round($surplus * 100 / $team['team_num'], 0);//百分比
        if ($team['status'] != 1 && gmtime() < ($team['start_time'] + ($team['validity_time'] * 3600)) && $team['is_team'] == 1) {//进项中
            $team['status'] = 0;
            $this->assign('page_title', L('waiting_team'));
        } elseif (($team['status'] != 1 && gmtime() > ($team['start_time'] + ($team['validity_time'] * 3600))) || $team['is_team'] != 1) {//失败
            $team['status'] = 2;
            $this->assign('page_title', L('team_failure'));
        } elseif ($team['status'] = 1) {//成功
            $team['status'] = 1;
            $this->assign('page_title', L('team_succes'));
        }


        /* --获取拼团团员信息-- */
        $sql = "select o.team_id, o.user_id,o.team_parent_id,o.team_user_id from " . $this->ecs->table('order_info') . " as o LEFT JOIN " . $this->ecs->table('users') . " as u ON o.user_id = u.user_id where o.team_id =$this->team_id and o.extension_code ='team_buy' and (pay_status = '" . PS_PAYED . "' or order_status = 4)  order by o.add_time asc  limit 0,5";
        $team_user = $this->db->query($sql);

        foreach ($team_user as $key => $vo) {
            //用户名、头像
            $user_nick = get_user_default($vo['user_id']);
            $team_user[$key]['user_name'] = encrypt_username($user_nick['nick_name']);
            $team_user[$key]['headerimg'] = $user_nick['user_picture'];
        }

        /* --验证是否已经参团-- */
        $team_join = $this->db->table('order_info')->where(array('user_id' => $_SESSION['user_id'], 'team_id' => $this->team_id))->count();
        if ($team_join > 0) {
            $this->assign('team_join', 1);
        }
        $this->assign('team_user', $team_user);
        $this->assign('goods', $goods);
        $this->assign('team', $team);
        $this->assign('cfg', C(shop));

        $this->assign('description', $goods['goods_brief']);    // 商品简单描述

        // 微信JSSDK分享
        $share_data = array(
            'title' => '拼团商品_' . $goods['goods_name'],
            'desc' => $goods['goods_brief'],
            'link' => '',
            'img' => $goods['goods_img'],
        );
        $this->assign('share_data', $this->get_wechat_share_content($share_data));

        $this->display();
    }


    /**
     * 拼团成员
     */
    public function actionTeamuser()
    {
        $this->team_id = I('team_id', 0, 'intval');
        $sql = "select o.team_id, o.user_id,o.team_parent_id,o.team_user_id,o.add_time ,u.user_name from " . $this->ecs->table('order_info') . " as o LEFT JOIN " . $this->ecs->table('users') . " as u ON o.user_id = u.user_id where o.team_id =$this->team_id and o.extension_code ='team_buy' and (pay_status = '" . PS_PAYED . "' or order_status = 4) order by o.add_time asc ";
        $team_user = $this->db->query($sql);
        foreach ($team_user as $key => $vo) {
            $team_user[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $vo['add_time']);
            //用户名、头像
            $user_nick = get_user_default($vo['user_id']);
            $team_user[$key]['user_name'] = encrypt_username($user_nick['nick_name']);
            $team_user[$key]['headerimg'] = $user_nick['user_picture'];
        }
        $this->assign('team_user', $team_user);
        $this->assign('page_title', L('team_user_list'));
        $this->display();
    }

    /**
     * ajax拼推荐商品
     */
    public function actionTeamlist()
    {
        $this->page = I('page', 1, 'intval');
        $type = isset($_REQUEST ['type']) ? $_REQUEST ['type'] : 'is_best';

        if (IS_AJAX) {
            $goods_list = team_goods($this->size, $this->page, $type);
            exit(json_encode(array('list' => $goods_list['list'], 'totalPage' => $goods_list['totalpage'])));
        }
    }


    /**
     * 验证是否登录
     */
    private function check_login()
    {
        if (!($_SESSION['user_id'] > 0)) {
            $url = urlencode(__HOST__ . $_SERVER['REQUEST_URI']);
            if (IS_POST) {
                $url = urlencode($_SERVER['HTTP_REFERER']);
            }
            ecs_header("Location: " . url('user/login/index', array('back_act' => $url)));
            exit;
        }
    }
}
