<?php

namespace App\Modules\Purchase\Controllers;

use App\Modules\Base\Controllers\FrontendController;

class OrderController extends FrontendController
{
    public $user_id;

    

    
    public function __construct()
    {
        parent::__construct();
        $this->user_id = $_SESSION['user_id'];
        $this->actionchecklogin();
        L(require(LANG_PATH . C('shop.lang') . '/user.php'));
        L(require(LANG_PATH . C('shop.lang') . '/flow.php'));
    }

    
    public function actionIndex()
    {
        $size = 10;
        $page = I('page', 1, 'intval');
        if (IS_POST) {
            $order_list = get_purchase_orders($this->user_id, $size, $page);
            exit(json_encode(['order_list' => $order_list['list'], 'totalPage' => $order_list['totalpage']]));
        }
        $this->assign('page_title', '我的采购单');
        $this->display();
    }
     
    public function actionReceived()
    {
        $order_id = I('order_id');
        if (IS_AJAX) {
            $sql = "UPDATE {pre}wholesale_order_info SET `order_status`=1 where order_id=" . $order_id;
            $this->db->query($sql);
            die(json_encode(['y' => 1]));
        }
    }

    
    public function actionDelOrder()
    {
        $order_id = I('order_id');
        if (IS_AJAX) {
            $sql = "UPDATE {pre}wholesale_order_info SET `is_delete`=1 where order_id=" . $order_id;
            $this->db->query($sql);
            die(json_encode(['y' => 1]));
        }
    }


    
    public function actionchecklogin()
    {
        if (!$this->user_id) {
            $url = urlencode(__HOST__ . $_SERVER['REQUEST_URI']);
            if (IS_POST) {
                $url = urlencode($_SERVER['HTTP_REFERER']);
            }
            ecs_header("Location: " . url('user/login/index', ['back_act' => $url]));
            exit;
        }
    }



}
