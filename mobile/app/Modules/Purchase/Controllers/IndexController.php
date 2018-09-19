<?php

namespace App\Modules\Purchase\Controllers;

use App\Modules\Purchase\Models\Purchase;
use App\Modules\Base\Controllers\FrontendController;

class IndexController extends FrontendController
{

    public function __construct()
    {
        parent::__construct();

        
        if ($GLOBALS['_CFG']['wholesale_user_rank'] == 0 && !$this->isSeller()) {
            $this->redirect(U('/'));
        }
    }

    
    public function actionIndex()
    {
        $this->assign('page_title', '批发首页');
        $this->assign('action', 'index');

        
        $banners = Purchase::get_banner(1022, 2048);
        $this->assign('banners', $banners);

        
        $wholesale_cat = Purchase::get_wholesale_child_cat();
        $this->assign('wholesale_cat', $wholesale_cat);

        
        $wholesale_limit = Purchase::get_wholesale_limit();
        $this->assign('wholesale_limit', $wholesale_limit);

        
        $goodsList = Purchase::get_wholesale_cat();
        $this->assign('get_wholesale_cat', $goodsList);

        $this->display();
    }

    
    public function actionList()
    {
        $page = I('page', 1, 'intval');
        $page = ($page > 0) ? $page : 1;

        $size = !empty($_CFG['page_size']) && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;

        $this->assign('page_title', '批发列表');
        $this->assign('action', 'list');

        
        $cat_id = I('id', 0, 'intval');
        if ($cat_id) {
            $this->assign('cat_name', Purchase::getCatName($cat_id));
        }

        
        $wholesale_cat = Purchase::get_wholesale_child_cat();
        $this->assign('wholesale_cat', $wholesale_cat);
        $this->assign('cat_id', $cat_id);

        $this->display();
    }

    
    public function actionGoodsList()
    {
        
        $act_id = I('id', 0, 'intval');
        $page = I('page', 1, 'intval');
        $size = I('size', 10, 'intval');

        $result = Purchase::get_wholesale_list($act_id, $size, $page);

        $this->ajaxReturn($result);
    }

    
    public function actionSearch()
    {
        $this->assign('page_title', '搜索页面');

        
        $_REQUEST['keywords'] = !empty($_REQUEST['keywords']) ? strip_tags(htmlspecialchars(trim($_REQUEST['keywords']))) : '';
        $_REQUEST['keywords'] = !empty($_REQUEST['keywords']) ? addslashes_deep(trim($_REQUEST['keywords'])) : '';

        
        $this->assign('keyword', $_REQUEST['keywords']);
        $this->display();
    }

    
    public function actionAsyncSearchList()
    {
        $page = !empty($_REQUEST['page']) && intval($_REQUEST['page']) > 0 ? intval($_REQUEST['page']) : 1;
        $size = !empty($_CFG['page_size']) && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;

        $list = Purchase::get_search_goods_list($_REQUEST['keywords'], $page, $size);

        $this->ajaxReturn($list);
    }

    
    public function actionGoods()
    {
        $this->assign('page_title', '批发详情');
        $this->assign('action', 'goods');

        
        $act_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $goods = Purchase::get_wholesale_goods_info($act_id);

        
        $area_info = get_area_info($this->province_id);
        $area_id = $area_info['region_id'];

        $where = "regionId = '$this->province_id'";
        $date = ['parent_id'];
        $region_id = get_table_date('region_warehouse', $where, $date, 2);
        

        
        $pictures = get_goods_gallery($goods['goods_id']);
        $this->assign('pictures', $pictures);                    

        
        $goods['goods_desc'] = preg_replace('/src=\"/', 'src="', $goods['goods_desc']);
        $this->assign('goods', $goods);

        
        $min = 0;
        foreach ($goods['volume_price'] as $list) {

            if ($min == 0 || $min > $list['volume_number']) {
                $min = $list['volume_number'];
            }
        }
        $this->assign('min', $min);

        
        $properties = Purchase::get_wholesale_goods_properties($goods['goods_id'], $region_id, $area_id);  
        $this->assign('specification', $properties['spe']);      

        $main_attr_list = Purchase::get_wholesale_main_attr_list($goods['goods_id']);
        $this->assign('main_attr_list', $main_attr_list);

        $this->assign('properties', $properties['pro']);      

        
        $is_jurisdiction = Purchase::isJurisdiction($goods);
        $this->assign('is_jurisdiction', $is_jurisdiction);


        
        $cartInfo = Purchase::get_wholesale_cart_info();
        $this->assign('cart_number', $cartInfo['cart_number']);

        

        $back_url = url('user/login/index', ['back_act' => urlencode(__SELF__)]);

        $this->assign('is_login', empty($_SESSION['user_id']) ? 0 : 1);
        $this->assign('back_url', $back_url);

        $this->display();
    }

    
    public function actionAddToCart()
    {
        $result = ['error' => 0, 'message' => '', 'content' => ''];

        
        $goods_id = I('goods_id', 0, 'intval');
        
        $goods_type = get_table_date('wholesale', "goods_id='$goods_id'", ['goods_type'], 2);

        if ($goods_type > 0) {
            $attr_array = empty($_REQUEST['attr_array']) ? [] : $_REQUEST['attr_array'];
            $num_array = empty($_REQUEST['num_array']) ? [] : $_REQUEST['num_array'];
            $total_number = array_sum($num_array);
        } else {
            $goods_number = empty($_REQUEST['num_array']) ? 0 : $_REQUEST['num_array'];
            $goods_number = $goods_number[0];
            $total_number = $goods_number;
        }

        $rank_ids = get_table_date('wholesale', "goods_id='$goods_id'", ['rank_ids'], 2);
        $is_jurisdiction = 0;
        if ($_SESSION['user_id'] > 0) {
            
            $sql = "SELECT user_id FROM " . $GLOBALS['ecs']->table('admin_user') . " WHERE ru_id = '" . $_SESSION['user_id'] . "'";
            $seller_id = $GLOBALS['db']->getOne($sql, true);
            if ($seller_id > 0) {
                $is_jurisdiction = 1;
            } else {
                
                if ($rank_ids) {
                    $rank_arr = explode(',', $rank_ids);
                    if (in_array($_SESSION['user_rank'], $rank_arr)) {
                        $is_jurisdiction = 1;
                    }
                }
            }
        } else {
            
            $back_act = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];

            $result['error'] = 2;
            $result['content'] = url('user/login/index', ['back_act' => urlencode($back_act)]);
            $result['message'] = '登陆过期，请重新登陆！';
            $this->ajaxReturn($result);
        }
        if ($is_jurisdiction == 0) {
            
            $back_act = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];

            $result['error'] = 1;
            $result['content'] = url('user/login/index', ['back_act' => urlencode($back_act)]);
            $result['message'] = '此商品您暂无购买权限！';
            $this->ajaxReturn($result);

        }
        
        $price_info = calculate_goods_price($goods_id, $total_number);
        
        $goods_info = get_table_date('goods', "goods_id='$goods_id'", ['goods_name, goods_sn, user_id']);
        
        $common_data = [];
        $common_data['user_id'] = $_SESSION['user_id'];
        $common_data['session_id'] = SESS_ID;
        $common_data['goods_id'] = $goods_id;
        $common_data['goods_sn'] = $goods_info['goods_sn'];
        $common_data['goods_name'] = $goods_info['goods_name'];
        $common_data['market_price'] = $price_info['market_price'];
        $common_data['goods_price'] = $price_info['unit_price'];
        $common_data['goods_number'] = 0;
        $common_data['goods_attr_id'] = '';
        $common_data['ru_id'] = $goods_info['user_id'];
        $common_data['add_time'] = gmtime();

        
        if ($_SESSION['user_id']) {
            $sess_id = " user_id = '" . $_SESSION['user_id'] . "' ";
        } else {
            $sess_id = " session_id = '" . real_cart_mac_ip() . "' ";
        }
        if ($goods_type > 0) {
            foreach ($attr_array as $key => $val) {
                
                $attr = explode(',', $val);
                
                $data = $common_data;
                $gooda_attr = get_goods_attr_array($val);
                foreach ($gooda_attr as $v) {
                    $data['goods_attr'] .= $v['attr_name'] . ":" . $v['attr_value'] . "\n";
                }
                $data['goods_attr_id'] = $val;
                $data['goods_number'] = $num_array[$key];
                
                $set = get_find_in_set($attr, 'goods_attr', ',');
                $sql = " SELECT * FROM " . $GLOBALS['ecs']->table('wholesale_products') . " WHERE goods_id = '$goods_id' $set ";
                $product_info = $GLOBALS['db']->getRow($sql);
                $data['goods_sn'] = $product_info['product_sn'];
                
                $set = get_find_in_set($attr, 'goods_attr_id', ',');

                $sql = " SELECT rec_id FROM " . $GLOBALS['ecs']->table('wholesale_cart') . " WHERE {$sess_id} AND goods_id = '$goods_id' $set ";

                $rec_id = $GLOBALS['db']->getOne($sql);

                if (!empty($rec_id)) {
                    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('wholesale_cart'), $data, 'UPDATE', "rec_id='$rec_id'");
                } else {
                    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('wholesale_cart'), $data, 'INSERT');
                }
            }
        } else {
            $data = $common_data;
            $data['goods_number'] = $goods_number;
            
            $sql = " SELECT rec_id FROM " . $GLOBALS['ecs']->table('wholesale_cart') . " WHERE {$sess_id} AND goods_id = '$goods_id' ";
            $rec_id = $GLOBALS['db']->getOne($sql);
            if (!empty($rec_id)) {
                $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('wholesale_cart'), $data, 'UPDATE', "rec_id='$rec_id'");
            } else {
                $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('wholesale_cart'), $data, 'INSERT');
            }
        }

        
        calculate_cart_goods_price($goods_id);
        $goods_data = Purchase::get_count_cart();


        
        $result['message'] = '商品已添加';
        $result['content'] = $goods_data;

        $this->ajaxReturn($result);

    }

    
    public function actionDown()
    {
        
        $common_data['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);
        $common_data['mobile'] = empty($_REQUEST['mobile']) ? '' : trim($_REQUEST['mobile']);
        $common_data['address'] = empty($_REQUEST['address']) ? '' : trim($_REQUEST['address']);
        $common_data['inv_type'] = empty($_REQUEST['inv_type']) ? 0 : intval($_REQUEST['inv_type']);
        $common_data['pay_id'] = empty($_REQUEST['pay_id']) ? 0 : intval($_REQUEST['pay_id']);
        $common_data['postscript'] = empty($_REQUEST['postscript']) ? '' : trim($_REQUEST['postscript']);
        $common_data['inv_payee'] = empty($_REQUEST['inv_payee']) ? '' : trim($_REQUEST['inv_payee']);
        $common_data['tax_id'] = empty($_REQUEST['tax_id']) ? '' : trim($_REQUEST['tax_id']);
        
        $main_order = $common_data;
        $main_order['order_sn'] = get_order_sn(); 
        $main_order['main_order_id'] = 0; 
        $main_order['user_id'] = $_SESSION['user_id'];
        $main_order['add_time'] = gmtime();
        $main_order['order_amount'] = 0;
        
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('wholesale_order_info'), $main_order, 'INSERT');
        $main_order_id = $GLOBALS['db']->getLastInsID(); 
        
        $rec_ids = empty($_REQUEST['rec_ids']) ? '' : implode(',', $_REQUEST['rec_ids']);
        $where = " WHERE user_id = '$_SESSION[user_id]' AND rec_id IN ($rec_ids) ";
        if (empty($rec_ids)) {
            
        }
        $sql = " SELECT DISTINCT ru_id FROM " . $GLOBALS['ecs']->table('wholesale_cart') . $where;
        $ru_ids = $GLOBALS['db']->getCol($sql);
        foreach ($ru_ids as $key => $val) {
            
            $child_order = $common_data;
            $child_order['order_sn'] = get_order_sn(); 
            $child_order['main_order_id'] = $main_order_id; 
            $child_order['user_id'] = $_SESSION['user_id'];
            $child_order['add_time'] = gmtime();
            $child_order['order_amount'] = 0;
            
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('wholesale_order_info'), $child_order, 'INSERT');
            $child_order_id = $GLOBALS['db']->getLastInsID(); 
            
            $sql = " SELECT goods_id, goods_name, goods_sn, goods_number, goods_price, goods_attr, goods_attr_id, ru_id FROM " .
                $GLOBALS['ecs']->table('wholesale_cart') . $where . " AND ru_id = '$val' ";
            $cart_goods = $GLOBALS['db']->getAll($sql);
            foreach ($cart_goods as $k => $v) {
                
                $v['order_id'] = $child_order_id;
                $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('wholesale_order_goods'), $v, 'INSERT');
                
                $child_order['order_amount'] += $v['goods_price'] * $v['goods_number'];
            }
            
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('wholesale_order_info'), $child_order, 'update', "order_id ='$child_order_id'");
            
            $main_order['order_amount'] += $child_order['order_amount'];
        }
        
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('wholesale_order_info'), $main_order, 'update', "order_id ='$main_order_id'");
        

        
        $sql = " DELETE FROM " . $GLOBALS['ecs']->table('wholesale_cart') . $where;
        $GLOBALS['db']->query($sql);

        $result = [
            'code' => 0,
            'message' => '提交成功'
        ];
        $this->ajaxReturn($result);
    }

    
    public function actionCart()
    {
        $this->assign('page_title', '进货单');
        $this->assign('action', 'cart');

        $goods_id = empty($_REQUEST['goods_id']) ? 0 : trim($_REQUEST['goods_id']);
        $rec_ids = empty($_REQUEST['rec_ids']) ? '' : trim($_REQUEST['rec_ids']);


        $goods_data = Purchase::wholesale_cart_goods($goods_id, $rec_ids);
        $this->assign('goods_data', $goods_data);

        $this->display();
    }

    
    public function actionUpdateCartGoods()
    {
        $result = ['error' => 0, 'message' => '', 'content' => ''];

        $rec_id = empty($_REQUEST['rec_id']) ? 0 : intval($_REQUEST['rec_id']);
        $rec_num = empty($_REQUEST['rec_num']) ? 0 : intval($_REQUEST['rec_num']);
        $rec_ids = I('rec_ids','');
        $rec_ids = implode(',', $rec_ids);

        
        $cart_info = get_table_date('wholesale_cart', "rec_id='$rec_id'", ['goods_id', 'goods_attr_id']);
        if (empty($cart_info['goods_attr_id'])) {
            $goods_number = get_table_date('wholesale', "goods_id='$cart_info[goods_id]'", ['goods_number'], 2);
        } else {
            $set = get_find_in_set(explode(',', $cart_info['goods_attr_id']));
            $goods_number = get_table_date('wholesale_products', "goods_id='$cart_info[goods_id]' $set", ['product_number'], 2);
        }
        $result['goods_number'] = $goods_number;

        if ($goods_number < $rec_num) {
            $result['error'] = 1;
            $result['message'] = "该商品库存只有{$goods_number}个";
            $rec_num = $goods_number;
        }
        $sql = " UPDATE " . $GLOBALS['ecs']->table('wholesale_cart') . " SET goods_number = '$rec_num' WHERE rec_id = '$rec_id' ";
        $GLOBALS['db']->query($sql);

        
        $cart_goods = Purchase:: wholesale_cart_goods(0, $rec_ids);
        $goods_list = array();
        foreach($cart_goods as $key=>$val){
            foreach($val['goods_list'] as $k=>$g){
                
                
                $goods_list[$g['goods_id']] = $g;
            }
        }
        $result['goods_list'] = $goods_list;
        

        $cart_info = Purchase::wholesale_cart_info(0, $rec_ids);

        $result['cart_info'] = $cart_info;

        $result['goods'] = Purchase::cartInfo($rec_id);
        $this->ajaxReturn($result);
    }

    
    public function actionRemove()
    {
        $result = ['error' => 0, 'message' => '', 'content' => ''];

        if ($_SESSION['user_id']) {
            $sess_id = " user_id = '" . $_SESSION['user_id'] . "' ";
        } else {
            $sess_id = " session_id = '" . real_cart_mac_ip() . "' ";
        }

        $goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
        if (!empty($goods_id)) {
            $sess_id .= " AND goods_id = '$goods_id' ";
            $sql = " DELETE FROM " . $GLOBALS['ecs']->table('wholesale_cart') . " WHERE $sess_id ";
            $GLOBALS['db']->query($sql);
        }
        $this->ajaxReturn($result);
    }

    
    public function actionInfo()
    {
        $this->assign('title', '批发首页');
        $this->assign('action', 'info');
        $result = [];

        $this->ajaxReturn($result);
    }

    
    public function actionShow()
    {
        $this->assign('page_title', '求购信息');
        $this->assign('action', 'show');
        $is_finished = isset($_REQUEST['is_finished']) ? intval($_REQUEST['is_finished']) : -1;
        $keyword = isset($_REQUEST['keyword']) ? htmlspecialchars(stripcslashes($_REQUEST['keyword'])) : '';

        $filter_array = [];
        $filter_array['review_status'] = 1;
        $query_array = [];
        $query_array['act'] = 'list';
        if ($is_finished != -1) {
            $query_array['is_finished'] = $is_finished;
            $filter_array['is_finished'] = $is_finished;
        }
        if ($keyword) {
            $filter_array['keyword'] = $keyword;
            $query_array['keyword'] = $keyword;
        }

        $page = I('page', 1, 'intval');
        $size = 10;
        if (IS_AJAX) {
            $purchase_list = Purchase::get_purchase_list($filter_array, $size, $page);
            exit(json_encode(['list' => array_values($purchase_list['purchase_list']), 'totalPage' => $purchase_list['page_count']]));
        }

        $this->assign('is_finished', $is_finished);
        $this->display();
    }

    
    public function actionShowDetail()
    {
        $this->assign('page_title', '求购详情');

        $purchase_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
        $purchase_info = Purchase::get_purchase_info($purchase_id);
        $this->assign('purchase_info', $purchase_info);

        $this->display();
    }

    
    private function isSeller()
    {
        $user_id = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

        $is_jurisdiction = 0;
        if ($user_id > 0) {
            
            $sql = "SELECT id FROM " . $GLOBALS['ecs']->table('seller_shopinfo') . " WHERE ru_id = '$user_id'";
            if ($GLOBALS['db']->getOne($sql, true)) {
                $is_jurisdiction = 1;
            }

            
            $sql = "SELECT fid FROM " . $GLOBALS['ecs']->table('merchants_steps_fields') . " WHERE user_id = '$user_id' AND company_type = '厂商'";
            $is_chang = $GLOBALS['db']->getOne($sql, true);

            if ($is_chang) {
                $is_jurisdiction = 0;
            }
        }

        return $is_jurisdiction;
    }
}
