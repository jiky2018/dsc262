<?php

namespace App\Modules\Team\Controllers;

use App\Modules\Base\Controllers\FrontendController;

class FlowController extends FrontendController
{
    private $sess_id = '';
    private $a_sess = '';
    private $b_sess = '';
    private $c_sess = '';
    private $sess_ip = '';
    private $region_id = 0;
    private $area_id = 0;

    /**
     * 构造，加载文件语言包和helper文件
     */
    public function __construct()
    {
        parent::__construct();
        L(require(LANG_PATH . C('shop.lang') . '/flow.php'));
        L(require(LANG_PATH . C('shop.lang') . '/user.php'));
        $files = array(
            'order',
            'clips',
            'transaction',
        );
        $this->load_helper($files);
        $this->check_login();
        //ecmoban模板堂 --zhuo start
        if (!empty($_SESSION['user_id'])) {
            $this->sess_id = " user_id = '" . $_SESSION['user_id'] . "' ";

            $this->a_sess = " a.user_id = '" . $_SESSION['user_id'] . "' ";
            $this->b_sess = " b.user_id = '" . $_SESSION['user_id'] . "' ";
            $this->c_sess = " c.user_id = '" . $_SESSION['user_id'] . "' ";

            $this->sess_ip = "";
        } else {
            $this->sess_id = " session_id = '" . real_cart_mac_ip() . "' ";

            $this->a_sess = " a.session_id = '" . real_cart_mac_ip() . "' ";
            $this->b_sess = " b.session_id = '" . real_cart_mac_ip() . "' ";
            $this->c_sess = " c.session_id = '" . real_cart_mac_ip() . "' ";

            $this->sess_ip = real_cart_mac_ip();
        }
        $area_info = get_area_info($this->province_id);
        $this->area_id = $area_info['region_id'];

        $where = "regionId = '$this->province_id'";
        $date = array('parent_id');
        $this->region_id = get_table_date('region_warehouse', $where, $date, 2);

        if (isset($_COOKIE['region_id']) && !empty($_COOKIE['region_id'])) {
            $this->region_id = $_COOKIE['region_id'];
        }
        //ecmoban模板堂 --zhuo end
    }

    /**
     * 购物车的商品是否符合优惠券优惠的价钱，显示可用优惠券
     * @param array $attr
     * @return array
     */
    public function showCoupons($attr)
    {
        $user_id = $_SESSION['user_id'];
        $arr = array();
        //优惠券
        $sql = " select * from  " . $GLOBALS['ecs']->table('coupons_user') . " cu  left join " . $GLOBALS['ecs']->table('coupons') . " cs  on cu.cou_id =  cs.cou_id   where cu.is_use = 0  and  cu.user_id='$user_id' ";
        $res = $GLOBALS['db']->getAll($sql);

        //$attr 商品id 数组 遍历
        foreach ($res as $i) {
            $goodsid = $i['cou_goods'];
            if (empty($goodsid)) {
                $arr[] = $i['cou_id'];
            } else {
                $gs = explode(",", $goodsid);
                foreach ($gs as $k) {           //优惠券商品ID
                    foreach ($attr as $j) {   //购物车的商品id
                        if ($j['goods_id'] == $k) {
                            $arr[] = $i['cou_id'];
                        }
                    }
                }
            }
        }
        return array_unique($arr);
    }

    /**
     * 订单确认
     */
    public function actionIndex()
    {
        $isStoreOrder = 1;  //判断是否为门店订单

        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
        //配送方式--自提点标识
        $_SESSION['shipping_type'] = 0;
        $_SESSION['shipping_type_ru_id'] = array();
        $_SESSION['flow_consignee']['point_id'] = array();
        //ecmoban模板堂 --zhuo
        $direct_shopping = isset($_REQUEST['direct_shopping']) ? intval($_REQUEST['direct_shopping']) : $_SESSION['direct_shopping'];
        $cart_value = isset($_REQUEST['cart_value']) ? htmlspecialchars($_REQUEST['cart_value']) : $_SESSION['cart_value'];
        $store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;  // 门店id

        //添加门店ID判断
        $store_id = !empty($_SESSION['store_id']) ? $_SESSION['store_id'] : $store_id;
        //添加门店ID判断

        if (empty($cart_value)) {
            $cart_value = get_cart_value($flow_type);
        }
        $_SESSION['cart_value'] = $cart_value;

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS) {
            $this->assign('is_group_buy', 1);
        } /* 积分兑换商品 */
        elseif ($flow_type == CART_EXCHANGE_GOODS) {
            $this->assign('is_exchange_goods', 1);
        } /* 预售商品 */
        elseif ($flow_type == CART_PRESALE_GOODS) {
            $this->assign('is_presale_goods', 1);
        } else {
            //正常购物流程  清空其他购物流程情况
            $_SESSION['flow_order']['extension_code'] = '';
        }

        /* 检查购物车中是否有商品 */
        $sql = "SELECT COUNT(*) FROM {pre}cart  WHERE " . $this->sess_id .
            "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type'";

        if ($this->db->getOne($sql) == 0) {
            show_message(L('no_goods_in_cart'), '', url('/'), 'warning');
        }

        /*
         * 检查用户是否已经登录
         * 如果用户已经登录了则检查是否有默认的收货地址
         * 如果没有登录则跳转到登录和注册页面
         */
        if (empty($direct_shopping) && $_SESSION['user_id'] == 0) {
            /* 用户没有登录且没有选定匿名购物，转向到登录页面 */
            ecs_header("Location: " . url('user/login/index'));
            exit;
        }
        $consignee = get_consignee($_SESSION['user_id']);
        /* 检查收货人信息是否完整 */
        if (!check_consignee_info($consignee, $flow_type) && $store_id <= 0) {
            ecs_header("Location: " . url('address_list'));
            exit;
        }
        //ecmoban模板堂 --zhuo start 审核收货人地址
        $user_address = get_order_user_address_list($_SESSION['user_id']);

        if ($direct_shopping != 1 && !empty($_SESSION['user_id'])) {
            $_SESSION['browse_trace'] = url('cart/index/index');
        } else {
            $_SESSION['browse_trace'] = url('flow/index/index');
        }

        if (count($user_address) <= 0 && $direct_shopping != 1 && $store_id <= 0) {
            ecs_header("Location: " . url('address_list'));
            exit;
        }
        if ($consignee) {
            $consignee['province_name'] = get_goods_region_name($consignee['province']);
            $consignee['city_name'] = get_goods_region_name($consignee['city']);
            $consignee['district_name'] = get_goods_region_name($consignee['district']);
            $street = get_region_name($consignee['street']);//街道
            $consignee['street_name'] = $street['region_name'];
            $consignee['region'] = $consignee['province_name'] . "&nbsp;" . $consignee['city_name'] . "&nbsp;" . $consignee['district_name'] . "&nbsp;" . $consignee['street_name'];
        }
        $default_id = $this->db->getOne("SELECT address_id FROM {pre}users WHERE user_id='$_SESSION[user_id]'");
        if ($consignee['address_id'] == $default_id) {
            $this->assign('is_default', '1');
        }

        $_SESSION['flow_consignee'] = $consignee;

        $this->assign('consignee', $consignee);

        /* 对商品信息赋值 */
        $cart_goods_list = cart_goods($flow_type, $cart_value, 1, $this->region_id, $this->area_id, '', $store_id); // 取得商品列表，计算合计
        if (empty($cart_goods_list)) {
            $this->redirect('/cart');
        }


        //商家商品总金额 by wanglu
        $store_goods_id = '';
        if ($cart_goods_list) {
            foreach ($cart_goods_list as $key => $val) {
                $amount = 0;
                $goods_price_amount = 0;
                $amount += $val['shipping']['shipping_fee'];
                foreach ($val['goods_list'] as $v) {
                    $amount += $v['subtotal'];
                    $goods_price_amount += $v['subtotal'];
                    if ($v['store_id'] == 0) {
                        $isStoreOrder = 0;
                    }//判断是否门店
                    $store_goods_id = $v['goods_id'];
                }
                $cart_goods_list[$key]['amount'] = $amount ? price_format($amount, false) : 0;
                $cart_goods_list[$key]['goods_price_amount'] = $goods_price_amount ? price_format($goods_price_amount, false) : 0;
            }
        }

        $this->assign('store_goods_id', $store_goods_id);
        $this->assign('isStoreOrder', $isStoreOrder);
        $cart_goods_list_new = cart_by_favourable($cart_goods_list);
        $this->assign('goods_list', $cart_goods_list_new);
        $this->assign('store', getStore($store_id));

        $cart_goods = cart_goods($flow_type, $cart_value); // 取得商品列表，计算合计

        /* 对是否允许修改购物车赋值 */
        if ($flow_type != CART_GENERAL_GOODS || C('shop.one_step_buy') == '1') {
            $this->assign('allow_edit_cart', 0);
        } else {
            $this->assign('allow_edit_cart', 1);
        }

        /*
         * 取得购物流程设置
         */
        $this->assign('config', C('shop'));
        /*
         * 取得订单信息
         */
        $order = flow_order_info();

        $this->assign('order', $order);
        /* 计算折扣 */
        if ($flow_type != CART_EXCHANGE_GOODS && $flow_type != CART_GROUP_BUY_GOODS) {
            $discount = compute_discount(3, $cart_value);
            $this->assign('discount', $discount['discount']);
            $favour_name = empty($discount['name']) ? '' : join(',', $discount['name']);
            $this->assign('your_discount', sprintf(L('your_discount'), $favour_name, price_format($discount['discount'])));
        }
        /*
         * 计算订单的费用
         */
        $total = order_fee($order, $cart_goods, $consignee, 0, $cart_value, 0, $cart_goods_list, 0, 0, $store_id, $store_seller);
        $this->assign('total', $total);
        $this->assign('shopping_money', sprintf(L('shopping_money'), $total['formated_goods_price']));
        $this->assign('market_price_desc', sprintf(L('than_market_price'), $total['formated_market_price'], $total['formated_saving'], $total['save_rate']));

        //配送时间
        $days = array();
        $shipping_date_list = $this->db->getAll("SELECT * FROM " . $this->ecs->table('shipping_date'));
        //组装配送时间
        $shipping_date = array();
        for ($i = 0; $i <= 6; $i++) {
            $year = date("Y-m-d", strtotime(' +' . $i . 'day'));
            $date = date("m月d日", strtotime(' +' . $i . 'day'));
            $shipping_date[$i]['id'] = $i;
            $shipping_date[$i]['name'] = $date . '【周' . transition_date($year) . '】';
            if ($shipping_date_list) {
                foreach ($shipping_date_list as $key => $val) {
                    $strtime = strtotime($year . " " . $val['end_date']);
                    if ($i >= $val['select_day'] && ($strtime >= gmtime() + 8 * 3600)) {
                        $shipping_date[$i]['child'][$key]['id'] = $val['shipping_date_id'];
                        $shipping_date[$i]['child'][$key]['name'] = $val['start_date'] . '-' . $val['end_date'];
                    }
                }
            }
        }
        $this->assign('shipping_date', json_encode($shipping_date));

        $district = $_SESSION['flow_consignee']['district'];
        $city = $_SESSION['flow_consignee']['city'];
        //全部区域
        $sql = "SELECT * FROM " . $this->ecs->table('region') . " WHERE parent_id = '$city'";
        $district_list = $this->db->getAll($sql);
        $picksite_list = get_self_point($district);

        $this->assign('picksite_list', $picksite_list);
        $this->assign('district_list', $district_list);
        $this->assign('district', $district);
        $this->assign('city', $city);
        /* 取得支付列表 */
        if ($order['shipping_id'] == 0) {
            $cod = true;
            $cod_fee = 0;
        } else {
            $shipping = shipping_info($order['shipping_id']);
            $cod = $shipping['support_cod'];
            if ($cod) {
                /* 如果是团购，且保证金大于0，不能使用货到付款 */
                if ($flow_type == CART_GROUP_BUY_GOODS) {
                    $group_buy_id = $_SESSION['extension_id'];
                    if ($group_buy_id <= 0) {
                        show_message('error group_buy_id');
                    }
                    $group_buy = group_buy_info($group_buy_id);
                    if (empty($group_buy)) {
                        show_message('group buy not exists: ' . $group_buy_id);
                    }

                    if ($group_buy['deposit'] > 0) {
                        $cod = false;
                        $cod_fee = 0;

                        /* 赋值保证金 */
                        $this->assign('gb_deposit', $group_buy['deposit']);
                    }
                }

                if ($cod) {
                    $shipping_area_info = shipping_area_info($order['shipping_id'], $region);
                    $cod_fee = $shipping_area_info['pay_fee'];
                }
            } else {
                $cod_fee = 0;
            }
        }

        // 给货到付款的手续费加<span id>，以便改变配送的时候动态显示
        $payment_list = available_payment_list(1, $cod_fee);
        if (isset($payment_list)) {
            foreach ($payment_list as $key => $payment) {
                //pc端去除ecjia的支付方式
                if (substr($payment['pay_code'], 0, 4) == 'pay_') {
                    unset($payment_list[$key]);
                    continue;
                }
                if ($payment['is_cod'] == '1') {
                    $payment_list[$key]['format_pay_fee'] = '<span id="ECS_CODFEE">' . $payment['format_pay_fee'] . '</span>';
                }
                /* 如果有易宝神州行支付 如果订单金额大于300 则不显示 */
                if ($payment['pay_code'] == 'yeepayszx' && $total['amount'] > 300) {
                    unset($payment_list[$key]);
                }
                /* 如果有余额支付 */
                if ($payment['pay_code'] == 'balance') {
                    /* 如果未登录，不显示 */
                    if ($_SESSION['user_id'] == 0) {
                        unset($payment_list[$key]);
                    } else {
                        if ($_SESSION['flow_order']['pay_id'] == $payment['pay_id']) {
                            $this->assign('disable_surplus', 1);
                        }
                    }
                }
                if (!file_exists(ADDONS_PATH . 'payment/' . $payment['pay_code'] . '.php')) {
                    unset($payment_list[$key]);
                }
                if ($payment['pay_code'] == 'cod') {
                    unset($payment_list[$key]);
                }
                if ($payment['pay_code'] == 'wxpay') {
                    if (!is_dir(APP_WECHAT_PATH)) {
                        unset($payment_list[$key]);
                    }
                    // 非微信浏览控制显示h5
                    if (is_wechat_browser() == false && is_wxh5() == 0) {
                        unset($payment_list[$key]);
                    }
                }
            }
        }
        $this->assign('payment_list', $payment_list);
        //当前选中的支付方式
        if ($order['pay_id']) {
            $payment_selected = payment_info($order['pay_id']);
            if (file_exists(ADDONS_PATH . 'payment/' . $payment_selected['pay_code'] . '.php') && $payment_selected['pay_code'] != 'cod') {
                $payment_selected['format_pay_fee'] = strpos($payment_selected['pay_fee'], '%') !== false ? $payment_selected['pay_fee'] : price_format($payment_selected['pay_fee'], false);
                $this->assign('payment_selected', $payment_selected);
            }
        }

        /* 取得包装与贺卡 */
        if ($total['real_goods_count'] > 0) {
            /* 只有有实体商品,才要判断包装和贺卡 */
            $use_package = C('shop.use_package');
            if (!isset($use_package) || $use_package == '1') {
                $pack_list = pack_list();
                /* 如果使用包装，取得包装列表及用户选择的包装 */
                $this->assign('pack_list', $pack_list);
            }
            //当前选中包装信息 by wanglu
            $pack_info = $order['pack_id'] ? pack_info($order['pack_id']) : array();
            $pack_info['format_pack_fee'] = price_format($pack_info['pack_fee'], false);
            $pack_info['format_free_money'] = price_format($pack_info['free_money'], false);
            $this->assign('pack_info', $pack_info);

            /* 如果使用贺卡，取得贺卡列表及用户选择的贺卡 */
            $use_card = C('shop.use_card');
            if (!isset($use_card) || $use_card == '1') {
                $this->assign('card_list', card_list());
            }
        }

        $user_info = user_info($_SESSION['user_id']);

        /* 如果使用余额，取得用户余额 */
        $use_surplus = C('shop.use_surplus');
        if ((!isset($use_surplus) || $use_surplus == '1') && $_SESSION['user_id'] > 0 && $user_info['user_money'] > 0) {
            // 能使用余额
            $this->assign('allow_use_surplus', 1);
            $this->assign('your_surplus', $user_info['user_money']);
        }

        /* 如果使用积分，取得用户可用积分及本订单最多可以使用的积分 */
        $use_integral = C('shop.use_integral');
        if ((!isset($use_integral) || $use_integral == '1') && $_SESSION['user_id'] > 0 && $user_info['pay_points'] > 0 && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS)) {
            // 能使用积分
            $order_max_integral = flow_available_points($cart_value);
            $this->assign('allow_use_integral', 1);
            $this->assign('order_max_integral', $order_max_integral);  // 可用积分
            $this->assign('your_integral', $user_info['pay_points']); // 用户积分
            //积分比例（1元对积分的比例）
            $integral_scale = C('shop.integral_scale');
            $integral_scale = $integral_scale ? $integral_scale / 100 : 0;
            $integral_money = $order_max_integral * $integral_scale;

            $this->assign('integral_money', $integral_money);
            $this->assign('integral_money_format', price_format($integral_money, false));
        }

        /* 如果使用红包，取得用户可以使用的红包及用户选择的红包 */
        $use_bonus = C('shop.use_bonus');
        $this->assign('total_goods_price', $total['goods_price']);
        if ((!isset($use_bonus) || $use_bonus == '1') && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS)) {
            $user_bonus_count = user_bonus($_SESSION['user_id'], $total['goods_price'], $cart_value, 0);

            $this->assign('cart_value', $cart_value);

            if ($order['bonus_id']) {
                $order_bonus = bonus_info($order['bonus_id']);

                $order_bonus['type_money_format'] = price_format($order_bonus['type_money'], false);
                $this->assign('order_bonus', $order_bonus);
            } else {
                $order['bonus_id'] = 0;
            }

            // 能使用红包
            $this->assign('allow_use_bonus', 1);
            $this->assign('user_bonus_count', $user_bonus_count);
        }
        /*  @author-bylu 优惠券 start */
        // 取得用户可用优惠券
        if ((!isset($_CFG['use_coupons']) || $_CFG['use_coupons'] == '1') && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS) && $flow_type != CART_PRESALE_GOODS && $flow_type != CART_EXCHANGE_GOODS && $flow_type != CART_AUCTION_GOODS) {
            $user_coupons_count = get_user_coupons_list($_SESSION['user_id'], true, $total['goods_price'], $cart_goods, true, 0);
            $this->assign('user_coupons', $user_coupons_count);

            if ($order['cou_id']) {
                $order_coupont = getCouponsByUcId($order['cou_id']);
                $order_coupont['type_cou_money'] = price_format($order_coupont['cou_money'], false);
                $this->assign('order_coupont', $order_coupont);
            }
        }
        /* 如果能开发票，取得发票内容列表 */
        $can_invoice = C('shop.can_invoice');
        if ((!isset($can_invoice) || $can_invoice == '1') && isset($GLOBALS['_CFG']['invoice_content']) && trim($GLOBALS['_CFG']['invoice_content']) != '' && $flow_type != CART_EXCHANGE_GOODS) {
            $inv_content_list = explode("\n", str_replace("\r", '', $GLOBALS['_CFG']['invoice_content']));
            $this->assign('inv_content_list', $inv_content_list);

            $inv_type_list = array();
            $invoice_type = C('shop.invoice_type');
            if (is_array($invoice_type)) {
                foreach ($invoice_type['type'] as $key => $type) {
                    if (!empty($type)) {
                        $inv_type_list[$type] = $type . ' [' . floatval($GLOBALS['_CFG']['invoice_type']['rate'][$key]) . '%]';
                    }
                }
            }
            $this->assign('inv_type_list', $inv_type_list);
            //默认发票计算
            $invoice_type = C('shop.invoice_type');
            $order['need_inv'] = 1;
            $order['inv_type'] = $invoice_type['type'][0];
            $order['inv_payee'] = '个人';
            $order['inv_content'] = $inv_content_list[0];
            //公司发票抬头
            $sql = "select * from {pre}order_invoice where user_id='$_SESSION[user_id]'and tax_id >0";
            $invoice_list_company = $this->db->getAll($sql);
            $this->assign('invoice_list_company', $invoice_list_company);
        }
        //能否使用增值发票
        $invoice_list = dao('users_vat_invoices_info')->where(array('user_id' => $_SESSION[user_id], 'audit_status' => 1))->find();
        $invoice_list = !empty($invoice_list) ? $invoice_list['id'] : 0;
        $this->assign('users_vat_invoices_id', $invoice_list);
        //dump($order);
        /* 保存 session */
        $_SESSION['flow_order'] = $order;
        $this->assign('order', $order);
        //没有商店ID则从购物车中获取
        if (!empty($cart_goods) && empty($store_id)) {
            $store_id = '';
            foreach ($cart_goods as $val) {
                $store_id .= $val['store_id'] . ',';
            }
            $store_id = substr($store_id, 0, -1);
        }
        //
        $this->assign('store_id', $store_id);
        $this->assign('page_title', '订单确认');
        $this->display();
    }

    /**
     * 确定确认页面---异步请求用户红包
     */
    public function actionGetUserBonus()
    {
        $result = array('error' => 0);

        $total_goods_price = I('get.total_goods_price');
        $cart_value = I('get.cart_value');
        $page = 1;
        $size = 100;

        // 取得用户可用红包
        $user_bonus = user_bonus($_SESSION['user_id'], $total_goods_price, $cart_value, $size, ($page - 1) * $size);
        $count = $user_bonus['conut'];
        $user_bonus = $user_bonus['list'];
        if (!empty($user_bonus)) {
            foreach ($user_bonus as $key => $val) {
                $user_bonus[$key]['type_money'] = round($val['type_money']);
                $user_bonus[$key]['bonus_money_formated'] = price_format($val['type_money'], false);
                $user_bonus[$key]['use_start_date'] = local_date('Y-m-d', $val['use_start_date']);
                $user_bonus[$key]['use_end_date'] = local_date('Y-m-d', $val['use_end_date']);
                //全场通用优惠券
                if ($val['usebonus_type'] == 1) {
                    $user_bonus[$key]['shop_name'] = '全场通用';
                } elseif ($val['user_id'] == 0) {
                    //自营
                    $user_bonus[$key]['shop_name'] = '';
                } else {
                    $user_bonus[$key]['shop_name'] = get_shop_name($val['user_id'], 1);
                }
            }
            $this->assign('bonus_num', count($user_bonus));

            $result['bonus_list'] = $user_bonus;
            $result['totalPage'] = 1;
        }

        echo json_encode($result);
    }

    /**
     * 确定确认页面---异步请求用户优惠券
     */
    public function actionGetUserCouon()
    {
        $result = array('error' => 0);

        $total_goods_price = I('get.total_goods_price');
        $cart_value = I('get.cart_value');
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
        $cart_goods = cart_goods($flow_type, $cart_value); // 取得商品列表，计算合计

        // 取得用户可用优惠券
        $user_coupons = get_user_coupons_list($_SESSION['user_id'], true, $total_goods_price, $cart_goods, true);
        if (!empty($user_coupons)) {
            foreach ($user_coupons as $k => $v) {
                $user_coupons[$k]['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
                $user_coupons[$k]['cou_type'] = $v['cou_type'] == 3 ? '全场券' : ($v['cou_type'] == 4 ? '会员券' : ($v['cou_type'] == 2 ? '购物券' : ($v['cou_type'] == 1 ? '注册券' : '未知')));
                $user_coupons[$k]['cou_goods_name'] = $v['cou_goods'] ? '限商品' : '全品类通用';
            }
            //优惠券列表
            $result['user_coupons'] = $user_coupons;
            $result['totalPage'] = 1;
        }

        echo json_encode($result);
    }

    /**
     * 订单提交
     */
    public function actionDone()
    {

        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;//门店id

        /* 检查购物车中是否有商品 */
        $sql = "SELECT COUNT(*) FROM {pre}cart WHERE " . $this->sess_id . "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type' AND rec_id " . db_create_in($_SESSION['cart_value']) . " ";
        if ($this->db->getOne($sql) == 0) {
            show_message(L('no_goods_in_cart'), '', url('cart/index/index'), 'warning');
        }

        /* 检查商品库存 */
        /* 如果使用库存，且下订单时减库存，则减少库存 */
        if (C('shop.use_storage') == '1' && C('shop.stock_dec_time') == SDT_PLACE) {
            $cart_goods_stock = get_cart_goods($_SESSION['cart_value']);
            $_cart_goods_stock = array();
            if (!empty($cart_goods_stock['goods_list'])) {
                foreach ($cart_goods_stock['goods_list'] as $value) {
                    foreach ($value['goods_list'] as $value2) {
                        $_cart_goods_stock[$value2['rec_id']] = $value2['goods_number'];
                    }
                }
                flow_cart_stock($_cart_goods_stock, $store_id);
                unset($cart_goods_stock, $_cart_goods_stock);
            }
        }
        /*
         * 检查用户是否已经登录
         * 如果用户已经登录了则检查是否有默认的收货地址
         * 如果没有登录则跳转到登录和注册页面
         */
        if (empty($_SESSION['direct_shopping']) && $_SESSION['user_id'] == 0) {
            /* 用户没有登录且没有选定匿名购物，转向到登录页面 */
            ecs_header("Location: " . url('user/login/index'));
            exit;
        }

        $consignee = get_consignee($_SESSION['user_id']);

        /* 检查收货人信息是否完整 */
        if (!check_consignee_info($consignee, $flow_type) && $store_id <= 0) {
            /* 如果不完整则转向到收货人信息填写界面 */
            ecs_header("Location: " . url('address_list'));
            exit;
        }

        $where_flow = '';
        $_POST['how_oos'] = isset($_POST['how_oos']) ? intval($_POST['how_oos']) : 0;
        $_POST['card_message'] = isset($_POST['card_message']) ? compile_str($_POST['card_message']) : '';
        $_POST['inv_type'] = !empty($_POST['inv_type']) ? compile_str($_POST['inv_type']) : '';
        $_POST['inv_payee'] = isset($_POST['inv_payee']) ? compile_str($_POST['inv_payee']) : '';
        $_POST['inv_content'] = isset($_POST['inv_content']) ? compile_str($_POST['inv_content']) : '';
        $msg = I('post.postscript', '', 'trim');
        $ru_id_arr = I('post.ru_id');
        $shipping_arr = I('post.shipping');

        $postscript = '';
        if (count($msg) > 1) {
            $postscript = array();
            foreach ($msg as $k => $v) {
                $postscript[$ru_id_arr[$k]] = $v;
            }
        } else {
            $postscript = isset($msg[0]) ? $msg[0] : '';
        }

        $shipping_type = I('post.shipping_type');
        $shipping = get_order_post_shipping($shipping_arr, $ru_id_arr);
        //cac
        $point = I('post.point_id', '');
        if (is_array($point)) {
            $point = array_filter($point);
        }
        $point_id = '';
        $shipping_dateStr = '';
        if (is_array($point) && !empty($point)) {
            foreach (I('post.point_id') as $key => $val) {
                if ($shipping_type[$key] == 1) {
                    $point_id .= $key . "|" . $val . ",";  // key=商家ID val=自提点
                }
            }
            if (is_array(I('post.shipping_dateStr'))) {
                $shipping_dateStr = '';
                foreach (I('post.shipping_dateStr') as $key => $val) {
                    if ($shipping_type[$key] == 1) {
                        $shipping_dateStr .= $key . "|" . $val . ","; // key=商家ID val=自提时间
                    }
                }
                if ($point_id && $shipping_dateStr) {
                    $point_id = substr($point_id, 0, -1);
                    $shipping_dateStr = substr($shipping_dateStr, 0, -1);
                }
            }
        }
        if (count($_POST['shipping']) == 1) {
            $shipping['shipping_id'] = $shipping_arr[0];
            if (is_array($point)) {
                foreach (I('post.point_id') as $key => $val) {
                    if ($shipping_type[$key] == 1) {
                        $point_id = $val;
                    }
                }
            } else {
                $point_id = $point;
            }
            if (is_array(I('post.shipping_dateStr'))) {
                foreach (I('post.shipping_dateStr') as $key => $val) {
                    if ($shipping_type[$key] == 1) {
                        $shipping_dateStr = $val;
                    }
                }
            } else {
                $shipping_dateStr = I('post.shipping_dateStr');
            }
        }
        //cac
        //快递配送方式
        $order = array(
            'shipping_id' => empty($shipping['shipping_id']) ? 0 : $shipping['shipping_id'],
            'pay_id' => intval($_POST['payment']),
            'pack_id' => isset($_POST['pack']) ? intval($_POST['pack']) : 0,
            'card_id' => isset($_POST['card']) ? intval($_POST['card']) : 0,
            'card_message' => trim($_POST['card_message']),
            'surplus' => isset($_POST['surplus']) ? floatval($_POST['surplus']) : 0.00,
            'integral' => isset($_POST['integral']) ? intval($_POST['integral']) : 0,
            'bonus_id' => isset($_POST['bonus']) ? intval($_POST['bonus']) : 0,
            'need_inv' => empty($_POST['need_inv']) ? 0 : 1,
            'tax_id' => I('tax_id', 0, 'intval'),
            'invoice_id' => I('invoice_id', 0, 'intval'),
            'invoice' => I('invoice', 1, 'intval'),
            'inv_type' => I('inv_type'),
            'invoice_type' => I('inv_type', 1, 'intval'),
            'inv_payee' => trim($_POST['inv_payee']),
            'inv_content' => trim($_POST['inv_content']),
            'vat_id' => I('vat_id', 1, 'intval'),
            'postscript' => is_array($postscript) ? '' : $postscript,
            'how_oos' => isset($GLOBALS['LANG']['oos'][$_POST['how_oos']]) ? addslashes($GLOBALS['LANG']['oos'][$_POST['how_oos']]) : '',
            'need_insure' => isset($_POST['need_insure']) ? intval($_POST['need_insure']) : 0,
            'user_id' => $_SESSION['user_id'],
            'add_time' => gmtime(),
            'order_status' => OS_UNCONFIRMED,
            'shipping_status' => SS_UNSHIPPED,
            'pay_status' => PS_UNPAYED,
            'agency_id' => get_agency_by_regions(array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district'])),
            'point_id' => $point_id,
            'shipping_dateStr' => $shipping_dateStr,
            'uc_id' => I('uc_id', 0, 'intval'),
        );

        /* 扩展信息 */
        if (isset($_SESSION['flow_type']) && intval($_SESSION['flow_type']) != CART_GENERAL_GOODS) {
            $order['extension_code'] = $_SESSION['extension_code'];
            $order['extension_id'] = $_SESSION['extension_id'];
        } else {
            $order['extension_code'] = '';
            $order['extension_id'] = 0;
        }

        /* 检查积分余额是否合法 */
        $user_id = $_SESSION['user_id'];
        if ($user_id > 0) {
            $user_info = user_info($user_id);

            $order['surplus'] = min($order['surplus'], $user_info['user_money'] + $user_info['credit_line']);
            if ($order['surplus'] < 0) {
                $order['surplus'] = 0;
            }

            // 查询用户有多少积分
            $flow_points = flow_available_points($_SESSION['cart_value']);  // 该订单允许使用的积分
            $user_points = $user_info['pay_points']; // 用户的积分总数

            $order['integral'] = min($order['integral'], $user_points, $flow_points);
            if ($order['integral'] < 0) {
                $order['integral'] = 0;
            }
        } else {
            $order['surplus'] = 0;
            $order['integral'] = 0;
        }

        /* 检查红包是否存在 */
        if ($order['bonus_id'] > 0) {
            $bonus = bonus_info($order['bonus_id']);

            if (empty($bonus) || $bonus['user_id'] != $user_id || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount(true, $flow_type)) {
                $order['bonus_id'] = 0;
            }
        } elseif (isset($_POST['bonus_sn'])) {
            $bonus_sn = trim($_POST['bonus_sn']);
            $bonus = bonus_info(0, $bonus_sn);
            $now = gmtime();
            if (empty($bonus) || $bonus['user_id'] > 0 || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount(true, $flow_type) || $now > $bonus['use_end_date']) {
            } else {
                if ($user_id > 0) {
                    $sql = "UPDATE {pre}user_bonus  SET user_id = '$user_id' WHERE bonus_id = '$bonus[bonus_id]' LIMIT 1";
                    $this->db->query($sql);
                }
                $order['bonus_id'] = $bonus['bonus_id'];
                $order['bonus_sn'] = $bonus_sn;
            }
        }

        $cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $this->region_id, $this->area_id); // 取得商品列表，计算合计
        /* 订单中的商品 */
        $cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
        if (empty($cart_goods)) {
            show_message(L('no_goods_in_cart'), L('back_home'), './', 'warning');
        }

        /* 检查商品总额是否达到最低限购金额 */
        if ($flow_type == CART_GENERAL_GOODS && cart_amount(true, CART_GENERAL_GOODS) < C('shop.min_goods_amount')) {
            show_message(sprintf(L('goods_amount_not_enough'), price_format(C('shop.min_goods_amount'), false)));
        }
        /* 收货人信息 */
        foreach ($consignee as $key => $value) {
            if (!is_array($value)) {
                if ($key != 'shipping_dateStr') {
                    $order[$key] = addslashes($value);
                } else {
                    $order[$key] = addslashes($order['shipping_dateStr']);
                }
            }
        }
        /* 判断是不是实体商品 */
        foreach ($cart_goods as $val) {
            /* 统计实体商品的个数 */
            if ($val['is_real']) {
                $is_real_good = 1;
            }
        }
        // 虚拟商品不用选择配送方式
        if (isset($is_real_good)) {
            if ((empty($order['shipping_id']) && empty($point_id) && empty($store_id)) || empty($order['pay_id'])) {
                show_message("请选择配送方式或者支付方式");
            }
        }
        /* if(isset($is_real_good))
          {
          $sql="SELECT shipping_id FROM {pre}shipping WHERE shipping_id=".$order['shipping_id'] ." AND enabled =1";
          if(!$this->db->getOne($sql))
          {
          show_message(L('flow_no_shipping'));
          }
          } */
        //切换配送方式
        $post_ru_id = (empty($_POST['ru_id'])) ? array() : $_POST['ru_id'];
        foreach ($cart_goods_list as $key => $val) {
            foreach ($post_ru_id as $kk => $vv) {
                if ($val['ru_id'] == $vv) {
                    $cart_goods_list[$key]['tmp_shipping_id'] = $_POST['shipping'][$kk];
                    continue;
                }
            }
        }
        $pay_type = 0;
        /* 订单中的总额 */


        $total = order_fee($order, $cart_goods, $consignee, 1, $_SESSION['cart_value'], $pay_type, $cart_goods_list);
        $order['bonus'] = $total['bonus'];
        $order['goods_amount'] = $total['goods_price'];
        $order['discount'] = $total['discount'] ? $total['discount'] : 0;
        $order['surplus'] = $total['surplus'];
        $order['tax'] = $total['tax'];


        // 购物车中的商品能享受红包支付的总额
        $discount_amout = compute_discount_amount($_SESSION['cart_value']);
        // 红包和积分最多能支付的金额为商品总额
        $temp_amout = $order['goods_amount'] - $discount_amout;
        if ($temp_amout <= 0) {
            $order['bonus_id'] = 0;
        }

        /* 配送方式 ecmoban模板堂 --zhuo */
        if (!empty($order['shipping_id'])) {
            if (count($_POST['shipping']) == 1) {
                $shipping = shipping_info($order['shipping_id']);
            }
            $order['shipping_name'] = addslashes($shipping['shipping_name']);
        }
        $order['shipping_fee'] = $total['shipping_fee'];
        $order['insure_fee'] = $total['shipping_insure'];

        /* 支付方式 */
        if ($order['pay_id'] > 0) {
            $payment = payment_info($order['pay_id']);
            $order['pay_name'] = addslashes($payment['pay_name']);
        }
        $order['pay_fee'] = $total['pay_fee'];
        $order['cod_fee'] = $total['cod_fee'];

        /* 商品包装 */
        if ($order['pack_id'] > 0) {
            $pack = pack_info($order['pack_id']);
            $order['pack_name'] = addslashes($pack['pack_name']);
        }
        $order['pack_fee'] = $total['pack_fee'];

        /* 祝福贺卡 */
        if ($order['card_id'] > 0) {
            $card = card_info($order['card_id']);
            $order['card_name'] = addslashes($card['card_name']);
        }
        $order['card_fee'] = $total['card_fee'];

        $order['order_amount'] = number_format($total['amount'], 2, '.', '');

        //ecmoban模板堂 --zhuo
        if (isset($_SESSION['direct_shopping']) && !empty($_SESSION['direct_shopping'])) {
            $where_flow = "&direct_shopping=" . $_SESSION['direct_shopping'];
        }

        /* 如果全部使用余额支付，检查余额是否足够 */
        if ($payment['pay_code'] == 'balance' && $order['order_amount'] > 0) {
            if ($order['surplus'] > 0) { //余额支付里如果输入了一个金额
                $order['order_amount'] = $order['order_amount'] + $order['surplus'];
                $order['surplus'] = 0;
            }
            if ($order['order_amount'] > ($user_info['user_money'] + $user_info['credit_line'])) {
                //ecmoban模板堂 --zhuo
                show_message(L('balance_not_enough'), L('back_up_page'), url('team/flow/index') . $where_flow);
            } else {
                if ($_SESSION['flow_type'] == CART_PRESALE_GOODS) {
                    //预售--首次付定金
                    $order['surplus'] = $order['order_amount'];
                    $order['pay_status'] = PS_PAYED_PART; //部分付款
                    $order['order_status'] = OS_CONFIRMED; //已确认
                    $order['order_amount'] = $order['goods_amount'] + $order['shipping_fee'] + $order['insure_fee'] + $order['tax'] - $order['discount'] - $order['surplus'];
                } else {
                    $order['surplus'] = $order['order_amount'];
                    $order['order_amount'] = 0;
                }
            }
        }
        /* 如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款 */
        if ($order['order_amount'] <= 0) {
            $order['order_status'] = OS_CONFIRMED;
            $order['confirm_time'] = gmtime();
            $order['pay_status'] = PS_PAYED;
            $order['pay_time'] = gmtime();
            $order['order_amount'] = 0;
        }

        $order['integral_money'] = $total['integral_money'];
        $order['integral'] = $total['integral'];

        if ($order['extension_code'] == 'exchange_goods') {
            $order['integral_money'] = 0;
            $order['integral'] = $total['exchange_integral'];
        }

        $order['from_ad'] = !empty($_SESSION['from_ad']) ? $_SESSION['from_ad'] : '0';
        $order['referer'] = !empty($_SESSION['referer']) ? addslashes($_SESSION['referer']) : addslashes(L('self_site'));

        /* 记录扩展信息 */
        if ($flow_type != CART_GENERAL_GOODS) {
            $order['extension_code'] = $_SESSION['extension_code'];
            $order['extension_id'] = $_SESSION['extension_id'];
        }

        $affiliate = unserialize(C('shop.affiliate'));
        if (isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 1) {
            //推荐订单分成
            $parent_id = get_affiliate();
            if ($user_id == $parent_id) {
                $parent_id = 0;
            }
        } elseif (isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 0) {
            //推荐注册分成
            $parent_id = 0;
        } else {
            //分成功能关闭
            $parent_id = 0;
        }
        $order['parent_id'] = $parent_id;

        /* 插入拼团信息记录 sty */
        if ($flow_type == CART_TEAM_GOODS) {
            if ($_SESSION['team_id'] > 0) {
                $team_info = $this->db->table('team_log')->where(array('team_id' => $_SESSION['team_id']))->find();
                if ($team_info['status'] > 0) {//参与拼团人数溢出时，开启新的团
                    $sql = "SELECT * FROM {pre}cart WHERE " . $this->sess_id . "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type' AND rec_id " . db_create_in($_SESSION['cart_value']) . " ";
                    $team_doods = $this->db->getRow($sql);
                    $team['t_id'] = $_SESSION['t_id'];//拼团活动id
                    $team['goods_id'] = $team_doods['goods_id'];//拼团商品id
                    $team['start_time'] = gmtime();
                    $team['status'] = 0;
                    $new_team = $this->db->filter_field('team_log', $team);
                    $team_log_id = $this->db->table('team_log')->data($new_team)->add();
                    $order['team_id'] = $team_log_id;
                    $order['team_parent_id'] = $_SESSION['user_id'];
                } else {
                    $order ['team_id'] = $_SESSION['team_id'];
                    $order ['team_user_id'] = $_SESSION['user_id'];
                }
            } else {
                $sql = "SELECT * FROM {pre}cart WHERE " . $this->sess_id . "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type' AND rec_id " . db_create_in($_SESSION['cart_value']) . " ";
                $team_doods = $this->db->getRow($sql);
                $team['t_id'] = $_SESSION['t_id'];//拼团活动id
                $team['goods_id'] = $team_doods['goods_id'];//拼团商品id
                $team['start_time'] = gmtime();
                $team['status'] = 0;
                $new_team = $this->db->filter_field('team_log', $team);
                $team_log_id = $this->db->table('team_log')->data($new_team)->add();
                $order['team_id'] = $team_log_id;
                $order['team_parent_id'] = $_SESSION['user_id'];
            }
        }
        /* 插入拼团信息记录 end */

        /* 插入订单表 */
        $error_no = 0;
        do {
            $order['order_sn'] = get_order_sn(); //获取新订单号
            $new_order = $this->db->filter_field('order_info', $order);
            $new_order_id = $this->db->table('order_info')->data($new_order)->add();
            //$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $order, 'INSERT');

            $error_no = $GLOBALS['db']->errno();

            if ($error_no > 0 && $error_no != 1062) {
                die($GLOBALS['db']->errno());
            }
        } while ($error_no == 1062); //如果是订单号重复则重新提交数据
        $order['order_id'] = $new_order_id;


        $goodsIn = '';
        $cartValue = isset($_SESSION['cart_value']) ? $_SESSION['cart_value'] : '';
        if (!empty($cartValue)) {
            $goodsIn = " and rec_id in($cartValue)";
        }
        /* 插入订单商品 */
        $sql = "INSERT INTO " . $this->ecs->table('order_goods') . "( " .
            "order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, " .
            "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, model_attr, goods_attr_id, ru_id, shopping_fee, warehouse_id, area_id) " .
            " SELECT '$new_order_id', goods_id, goods_name, goods_sn, product_id, goods_number, market_price, " .
            "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, model_attr, goods_attr_id, ru_id, shopping_fee, warehouse_id, area_id" .
            " FROM " . $this->ecs->table('cart') .
            " WHERE " . $this->sess_id . " AND rec_type = '$flow_type'" . $goodsIn;
        $this->db->query($sql);


        /*插入门店订单表*/
        $good_ru_id = !empty($_REQUEST['ru_id']) ? $_REQUEST['ru_id'] : array();

        if ($store_id > 0) {
            foreach ($good_ru_id as $v) {
                $pick_code = substr($order['order_sn'], -3) . rand(0, 9) . rand(0, 9) . rand(0, 9);
                $sql = "INSERT INTO" . $this->ecs->table("store_order") . " (`order_id`,`store_id`,`ru_id`,`pick_code`) VALUES ('$new_order_id','$store_id','$v','$pick_code')";
                $this->db->query($sql);
            }
            $this->assign('store', getStore($store_id));
            $this->assign('pick_code', $pick_code);
            //门店发送信息通知
        }

        //插入门店订单结束
        //使用优惠券
        /* 记录优惠券使用 bylu */
        if ($order['uc_id'] > 0) {
            $this->use_coupons($order['uc_id'], $order['order_id']);
        }
        /* 修改拍卖活动状态 */
        if ($order['extension_code'] == 'auction') {
            $sql = "UPDATE {pre}goods_activity SET is_finished='2' WHERE review_status = 3 AND act_id=" . $order['extension_id'];
            $this->db->query($sql);
        }
        /* 处理余额、积分、红包、优惠券 */
        if ($order['user_id'] > 0 && $order['surplus'] > 0) {
            //log_account_change($order['user_id'], $order['surplus'] * (-1), 0, 0, 0, sprintf($GLOBALS['LANG']['pay_order'], $order['order_sn']));
            log_account_change($order['user_id'], $order['surplus'] * (-1), 0, 0, 0, '订单:' . $order['order_sn'], $order['order_sn']);

            //余额付款更新拼团信息记录
            if (!empty($order['team_id']) && $order['team_id'] > 0) {
                update_team($order['team_id'], $order['team_parent_id']);
            }
        }
        if ($order['user_id'] > 0 && $order['integral'] > 0) {
            log_account_change($order['user_id'], 0, 0, 0, $order['integral'] * (-1), sprintf(L('pay_order'), $order['order_sn']));
        }


        if ($order['bonus_id'] > 0 && $temp_amout > 0) {
            use_bonus($order['bonus_id'], $new_order_id);
        }

        /* 如果使用库存，且下订单时减库存，则减少库存 */
        if (C('shop.use_storage') == '1' && C('shop.stock_dec_time') == SDT_PLACE) {
            change_order_goods_storage($order['order_id'], true, SDT_PLACE);
        }

        if (count($cart_goods) <= 1) {
            if ($cart_goods[0]['ru_id'] >= 1) {
                $sql = "SELECT seller_email FROM " . $GLOBALS['ecs']->table('seller_shopinfo') . " WHERE ru_id = '" . $cart_goods[0]['ru_id'] . "'";
                $service_email = $GLOBALS['db']->getOne($sql);
            } else {
                $service_email = C('shop.service_email');
            }
        } else {
            $service_email = C('shop.service_email');
        }
        $msg = $order['pay_status'] == PS_UNPAYED ? L('order_placed_sms') : L('order_placed_sms') . '[' . L('sms_paid') . ']';

        /* 如果订单金额为0 处理虚拟卡 */
        if ($order['order_amount'] <= 0) {
            $sql = "SELECT goods_id, goods_name, goods_number AS num FROM " .
                $GLOBALS['ecs']->table('cart') .
                " WHERE is_real = 0 AND extension_code = 'virtual_card'" .
                " AND " . $this->sess_id . " AND rec_type = '$flow_type'";

            $res = $GLOBALS['db']->getAll($sql);

            $virtual_goods = array();
            foreach ($res as $row) {
                $virtual_goods['virtual_card'][] = array('goods_id' => $row['goods_id'], 'goods_name' => $row['goods_name'], 'num' => $row['num']);
            }

            if ($virtual_goods and $flow_type != CART_GROUP_BUY_GOODS) {
                /* 虚拟卡发货 */
                if (virtual_goods_ship($virtual_goods, $msg, $order['order_sn'], true)) {
                    /* 如果没有实体商品，修改发货状态，送积分和红包 */
                    $sql = "SELECT COUNT(*)" .
                        " FROM " . $this->ecs->table('order_goods') .
                        " WHERE order_id = '$order[order_id]' " .
                        " AND is_real = 1";
                    if ($this->db->getOne($sql) <= 0) {
                        /* 修改订单状态 */
                        update_order($order['order_id'], array('shipping_status' => SS_SHIPPED, 'shipping_time' => gmtime()));

                        /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
                        if ($order['user_id'] > 0) {
                            /* 取得用户信息 */
                            $user = user_info($order['user_id']);

                            /* 计算并发放积分 */
                            $integral = integral_to_give($order);
                            log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($GLOBALS['LANG']['order_gift_integral'], $order['order_sn']));

                            /* 发放红包 */
                            send_order_bonus($order['order_id']);
                        }
                    }
                }
            }
        }

        /* 清空购物车 */
        clear_cart($flow_type, $_SESSION['cart_value']);
        /* 清除缓存，否则买了商品，但是前台页面读取缓存，商品数量不减少 */
        clear_all_files();

        /* 插入支付日志 */
        $order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);

        /* 取得支付信息，生成支付代码 */
        $payment = payment_info($order['pay_id']);
        $order['pay_code'] = $payment['pay_code'];
        if ($order['order_amount'] > 0) {
            include_once(ADDONS_PATH . 'payment/' . $payment['pay_code'] . '.php');

            $pay_obj = new $payment['pay_code'];

            $pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));

            $order['pay_desc'] = $payment['pay_desc'];

            $this->assign('pay_online', $pay_online);
        }
        if (!empty($order['shipping_name'])) {
            $order['shipping_name'] = trim(stripcslashes($order['shipping_name']));
        }

        /* 订单信息 */
        $this->assign('order', $order);
        $this->assign('total', $total);
        $this->assign('goods_list', $cart_goods);
        $this->assign('order_submit_back', sprintf($GLOBALS['LANG']['order_submit_back'], $GLOBALS['LANG']['back_home'], $GLOBALS['LANG']['goto_user_center'])); // 返回提示

        user_uc_call('add_feed', array($order['order_id'], BUY_GOODS)); //推送feed到uc
        unset($_SESSION['flow_consignee']); // 清除session中保存的收货人信息
        unset($_SESSION['cart_value']);
        unset($_SESSION['flow_order']);
        unset($_SESSION['direct_shopping']);
        unset($_SESSION['store_id']);
        unset($_SESSION['team_id']);
        unset($_SESSION['t_id']);

        //订单分子订单 start
        $order_id = $order['order_id'];
        $row = get_main_order_info($order_id);
        $order_info = get_main_order_info($order_id, 1);

        $ru_id = explode(",", $order_info['all_ruId']['ru_id']);
        $ru_number = count($ru_id);
        if ($ru_number > 1) {
            //订单留言对应到商家 by wanglu
            get_insert_order_goods_single($order_info, $row, $order_id, $postscript, $ru_number);
        }

        $sql = "select count(order_id) from " . $this->ecs->table('order_info') . " where main_order_id = " . $order['order_id'];
        $child_order = $this->db->getOne($sql);
        if ($child_order > 1) {
            $child_order_info = get_child_order_info($order['order_id']);
            $this->assign('child_order_info', $child_order_info);
        }

        $this->assign('pay_type', $pay_type);
        $this->assign('child_order', $child_order);

        /* $goods_buy_list = get_order_goods_buy_list($this->region_id, $this->area_id);
          $this->assign('goods_buy_list', $goods_buy_list); */

        //对单商家下单
        if (count($ru_id) == 1) {
            /* 如果需要，发短信 */
            $sellerId = $ru_id[0];
            if ($sellerId == 0) {
                $sms_shop_mobile = C('shop.sms_shop_mobile');
            } else {
                $sql = "SELECT mobile FROM " . $this->ecs->table('seller_shopinfo') . " WHERE ru_id = '$sellerId'";
                $sms_shop_mobile = $this->db->getOne($sql);
            }
            if (C('shop.sms_order_placed') == '1' && $sms_shop_mobile != '') {
                $msg = array(
                    'consignee' => $order['consignee'],
                    'order_mobile' => $order['mobile']
                );
                send_sms($sms_shop_mobile, 'sms_order_placed', $msg);
            }
            /* 给商家发邮件 */
            /* 增加是否给客服发送邮件选项 */
            if (C('shop.send_service_email') && $service_email != '') {
                $tpl = get_mail_template('remind_of_new_order');
                $this->assign('order', $order);
                $this->assign('goods_list', $cart_goods);
                $this->assign('shop_name', C('shop.shop_name'));
                $send_date = local_date(C('shop.time_format'), gmtime());
                $this->assign('send_date', $send_date);
                $content = $this->fetch('', $tpl['template_content']);
                send_mail(C('shop.shop_name'), $service_email, $tpl['template_subject'], $content, $tpl['is_html']);
            }
        }
        if (is_dir(APP_WECHAT_PATH)) {
            $pushData = array(
                'orderID' => array('value' => $order['order_sn']), //订单号
                'orderMoneySum' => array('value' => $order['order_amount']), //订单金额
                'backupFieldName' => array('value' => ''),
                'remark' => array('value' => '感谢您的光临')
            );
            $url = __HOST__ . url('user/order/detail', array('order_id' => $order_id));
            push_template('TM00016', $pushData, $url);
        }
        $this->assign('page_title', L('order_success'));
        $this->display();
    }

    /**
     * 计算运费后订单总价
     */
    public function actionShippingfee()
    {
        if (IS_AJAX) {
            $result = array('error' => 0, 'massage' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);
            /* 取得购物类型 */
            $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
            /* 配送方式 */
            $shipping_type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
            $tmp_shipping_id = isset($_POST['shipping_id']) ? intval($_POST['shipping_id']) : 0;
            $ru_id = isset($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : 0;
            /* 获得收货人信息 */
            $consignee = get_consignee($_SESSION['user_id']);

            /* 对商品信息赋值 */
            $cart_goods = cart_goods($flow_type, $_SESSION['cart_value']); // 取得商品列表，计算合计
            if (empty($cart_goods) || (!check_consignee_info($consignee, $flow_type) && $_SESSION['store_id'] <= 0)) {
                //ecmoban模板堂 --zhuo start
                if (empty($cart_goods)) {
                    $result['error'] = 1;
                } elseif (!check_consignee_info($consignee, $flow_type)) {
                    $result['error'] = 2;
                }
                //ecmoban模板堂 --zhuo end
            } else {
                /* 取得购物流程设置 */
                $this->assign('config', C('shop'));

                /* 取得订单信息 */
                $order = flow_order_info();

                /* 保存 session */

                $_SESSION['flow_order'] = $order;
                //                $_SESSION['shipping_type'] = $shipping_type;
                if ($shipping_type == 1) {
                    if (is_array($_SESSION['shipping_type_ru_id'])) {
                        $_SESSION['shipping_type_ru_id'][$ru_id] = $ru_id;
                    }
                } else {
                    if (isset($_SESSION['shipping_type_ru_id'][$ru_id])) {
                        unset($_SESSION['shipping_type_ru_id'][$ru_id]);
                    }
                }

                //ecmoban模板堂 --zhuo start
                $cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
                $this->assign('cart_goods_number', $cart_goods_number);

                $consignee['province_name'] = get_goods_region_name($consignee['province']);
                $consignee['city_name'] = get_goods_region_name($consignee['city']);
                $consignee['district_name'] = get_goods_region_name($consignee['district']);
                $consignee['street'] = get_goods_region_name($consignee['street']);//街道
                $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];

                $this->assign('consignee', $consignee);
                $cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1); // 取得商品列表，计算合计
                $this->assign('goods_list', cart_by_favourable($cart_goods_list));
                $_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
                $tmp_shipping_id_arr = json_decode($_POST['shipping_id']);
                //切换配送方式 by kong
                foreach ($cart_goods_list as $key => $val) {
                    foreach ($tmp_shipping_id_arr as $k => $v) {
                        if ($v[1] > 0 && $val['ru_id'] == $v[0]) {
                            $cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
                        }
                    }
                }
                /* 计算订单的费用 */
                $total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
                $this->assign('order', $order);
                $this->assign('total', $total);
                //ecmoban模板堂 --zhuo end

                /* 团购标志 */
                if ($flow_type == CART_GROUP_BUY_GOODS) {
                    $this->assign('is_group_buy', 1);
                }
                $result['amount'] = $total['amount_formated'];

                $result['content'] = $this->fetch('order_total');
            }

            exit(json_encode($result));
        }
    }

    /**
     * 更改支付方式
     */
    public function actionSelectPayment()
    {
        if (IS_AJAX) {
            $result = array('error' => 0, 'massage' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);
            /* 取得购物类型 */
            $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
            $tmp_shipping_id_arr = I('shipping_id');
            /* 获得收货人信息 */
            $consignee = get_consignee($_SESSION['user_id']);

            /* 对商品信息赋值 */
            $cart_goods = cart_goods($flow_type, $_SESSION['cart_value']); // 取得商品列表，计算合计

            if (empty($cart_goods) || (!check_consignee_info($consignee, $flow_type) && $_SESSION['store_id'] <= 0)) {
                //ecmoban模板堂 --zhuo start
                if (empty($cart_goods)) {
                    $result['error'] = 1;
                } elseif (!check_consignee_info($consignee, $flow_type)) {
                    $result['error'] = 2;
                }
                //ecmoban模板堂 --zhuo end
            } else {
                /* 取得购物流程设置 */
                $this->assign('config', C('shop'));

                /* 取得订单信息 */
                $order = flow_order_info();

                $order['pay_id'] = intval($_REQUEST['payment']);
                $payment_info = payment_info($order['pay_id']);
                $result['pay_code'] = $payment_info['pay_code'];
                $result['pay_name'] = $payment_info['pay_name'];
                $result['pay_fee'] = $payment_info['pay_fee'];
                $result['format_pay_fee'] = strpos($payment_info['pay_fee'], '%') !== false ? $payment_info['pay_fee'] : price_format($payment_info['pay_fee'], false);
                $result['pay_id'] = $payment_info['pay_id'];

                /* 保存 session */
                $_SESSION['flow_order'] = $order;

                //ecmoban模板堂 --zhuo start
                $cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
                $this->assign('cart_goods_number', $cart_goods_number);

                $consignee['province_name'] = get_goods_region_name($consignee['province']);
                $consignee['city_name'] = get_goods_region_name($consignee['city']);
                $consignee['district_name'] = get_goods_region_name($consignee['district']);
                $consignee['street'] = get_goods_region_name($consignee['street']);//街道
                $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];
                $this->assign('consignee', $consignee);

                $cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1); // 取得商品列表，计算合计
                /* $this->assign('goods_list', $cart_goods_list); */
                //切换配送方式 by kong
                foreach ($cart_goods_list as $key => $val) {
                    foreach ($tmp_shipping_id_arr as $k => $v) {
                        if ($v[1] > 0 && $val['ru_id'] == $v[0]) {
                            $cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
                        }
                    }
                }
                /* 计算订单的费用 */
                $total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
                $this->assign('total', $total);
                //ecmoban模板堂 --zhuo end

                /* 取得可以得到的积分和红包 */
                /* $this->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
                  $this->assign('total_bonus',    price_format(get_total_bonus(), false)); */

                /* 团购标志 */
                if ($flow_type == CART_GROUP_BUY_GOODS) {
                    $this->assign('is_group_buy', 1);
                }

                $result['amount'] = $total['amount_formated'];
                $result['content'] = $this->fetch('order_total');
            }

            exit(json_encode($result));
        }
    }

    /**
     * 更改包装
     */
    public function actionSelectPack()
    {
        if (IS_AJAX) {
            $result = array('error' => '', 'content' => '', 'need_insure' => 0);

            /* 取得购物类型 */
            $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

            /* 获得收货人信息 */
            $consignee = get_consignee($_SESSION['user_id']);

            /* 对商品信息赋值 */
            $cart_goods = cart_goods($flow_type, $_SESSION['cart_value']); // 取得商品列表，计算合计

            if (empty($cart_goods) || (!check_consignee_info($consignee, $flow_type) && $_SESSION['store_id'] <= 0)) {
                $result['error'] = L('no_goods_in_cart');
            } else {
                /* 取得订单信息 */
                $order = flow_order_info();

                $order['pack_id'] = intval($_REQUEST['pack']);

                /* 保存 session */
                $_SESSION['flow_order'] = $order;

                //ecmoban模板堂 --zhuo start
                $cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
                $this->assign('cart_goods_number', $cart_goods_number);

                $consignee['province_name'] = get_goods_region_name($consignee['province']);
                $consignee['city_name'] = get_goods_region_name($consignee['city']);
                $consignee['district_name'] = get_goods_region_name($consignee['district']);
                $consignee['street'] = get_goods_region_name($consignee['street']);//街道
                $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];
                $this->assign('consignee', $consignee);

                $cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1); // 取得商品列表，计算合计
                $this->assign('goods_list', $cart_goods_list);

                /* 计算订单的费用 */
                $total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
                $this->assign('total', $total);
                //ecmoban模板堂 --zhuo end

                /* 取得可以得到的积分和红包 */
                $this->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
                $this->assign('total_bonus', price_format(get_total_bonus(), false));

                /* 团购标志 */
                if ($flow_type == CART_GROUP_BUY_GOODS) {
                    $this->assign('is_group_buy', 1);
                }
                $result['pack_id'] = $order['pack_id'];
                $result['amount'] = $total['amount_formated'];
                $result['content'] = $this->fetch('order_total');
            }
            exit(json_encode($result));
        }
    }

    /**
     * 选择红包
     */
    public function actionChangeBonus()
    {
        $result = array('error' => '', 'content' => '');
        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        /* 获得收货人信息 */
        $consignee = get_consignee($_SESSION['user_id']);

        /* 对商品信息赋值 */
        $cart_goods = cart_goods($flow_type, $_SESSION['cart_value']); // 取得商品列表，计算合计

        if (empty($cart_goods) || (!check_consignee_info($consignee, $flow_type) && $_SESSION['store_id'] <= 0)) {
            $result['error'] = $GLOBALS['_LANG']['no_goods_in_cart'];
        } else {
            /* 取得购物流程设置 */
            $this->assign('config', C('shop'));

            /* 取得订单信息 */
            $order = flow_order_info();
            $bonus = bonus_info(intval($_GET['bonus']));

            if ((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || $_GET['bonus'] == 0) {
                $order['bonus_id'] = intval($_GET['bonus']);
            } else {
                $order['bonus_id'] = 0;
                $result['error'] = $GLOBALS['_LANG']['invalid_bonus'];
            }
            $consignee['province_name'] = get_goods_region_name($consignee['province']);
            $consignee['city_name'] = get_goods_region_name($consignee['city']);
            $consignee['district_name'] = get_goods_region_name($consignee['district']);
            $consignee['street'] = get_goods_region_name($consignee['street']);//街道
            $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];
            $this->assign('consignee', $consignee);
            $cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1); // 取得商品列表，计算合计
            //$this->assign('goods_list', $cart_goods_list);
            /* 计算订单的费用 */
            $total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
            $this->assign('total', $total);
            $this->assign('order', $order);
            //ecmoban模板堂 --zhuo end
            /* 团购标志 */
            if ($flow_type == CART_GROUP_BUY_GOODS) {
                $this->assign('is_group_buy', 1);
            }
            $result['bonus_id'] = $order['bonus_id'];
            $result['amount'] = $total['amount_formated'];
            $result['content'] = $this->fetch('order_total');
        }
        exit(json_encode($result));
    }

    /**
     * 使用优惠卷
     */
    public function actionChangeCoupont()
    {
        /* 取得订单信息 */
        $order = flow_order_info();
        $result = array('error' => '', 'content' => '');
        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
        /* 获得收货人信息 */
        $consignee = get_consignee($_SESSION['user_id']);
        /* 对商品信息赋值 */
        $cart_goods = cart_goods($flow_type, $_SESSION['cart_value']); // 取得商品列表，计算合计
        if (empty($cart_goods) || (!check_consignee_info($consignee, $flow_type) && $_SESSION['store_id'] <= 0)) {
            $result['error'] = L('no_goods_in_cart');
        } else {
            /* 取得购物流程设置 */
            $this->assign('config', C());

            /* 取得订单信息 */
            $order = flow_order_info();
            $_SESSION['flow_order'] = null;
            $cou_id = I('cou_id');
            /* 获取优惠券信息 */
            $coupons_info = $this->get_coupons($cou_id);   //$_GET['uc_id']
            if (!empty($coupons_info) && $coupons_info['user_id'] == $_SESSION['user_id']) {
                $order['cou_id'] = $order['uc_id'] = $cou_id;
            } else {
                $order['cou_id'] = $order['uc_id'] = 0;
            }
            //ecmoban模板堂 --zhuo start
            $cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
            $this->assign('cart_goods_number', $cart_goods_number);
            $consignee['province_name'] = get_goods_region_name($consignee['province']);
            $consignee['city_name'] = get_goods_region_name($consignee['city']);
            $consignee['district_name'] = get_goods_region_name($consignee['district']);
            $consignee['street'] = get_goods_region_name($consignee['street']);//街道
            $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];
            $this->assign('consignee', $consignee);

            $cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1); // 取得商品列表，计算合计
            $this->assign('goods_list', $cart_goods_list);

            /* 计算订单的费用 */
            $total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
            $this->assign('order', $order);
            $this->assign('total', $total);
            $_SESSION['flow_order']['cou_id'] = 0;
            $_SESSION['flow_order']['uc_id'] = 0;
            //ecmoban模板堂 --zhuo end

            /* 团购标志 */
            if ($flow_type == CART_GROUP_BUY_GOODS) {
                $this->assign('is_group_buy', 1);
            } elseif ($flow_type == CART_EXCHANGE_GOODS) {
                // 积分兑换 qin
                $this->assign('is_exchange_goods', 1);
            }
            $result['cou_id'] = $order['cou_id'];
            $result['amount'] = $total['amount_formated'];
            $result['content'] = $this->fetch('order_total');
        }
        exit(json_encode($result));
    }

    /**
     * 订单使用积分计算
     */
    public function actionChangeIntegral()
    {
        $points = floatval($_GET['points']);
        $user_info = user_info($_SESSION['user_id']);

        /* 取得订单信息 */
        $order = flow_order_info();

        $flow_points = flow_available_points($_SESSION['cart_value']);  // 该订单允许使用的积分
        $user_points = $user_info['pay_points']; // 用户的积分总数
        $tmp_shipping_id_arr = I('shipping_id');
        if ($points > $user_points) {
            $result['error'] = L('integral_not_enough');
        } elseif ($points > $flow_points) {
            $result['error'] = sprintf(L('integral_too_much'), $flow_points);
        } else {
            /* 取得购物类型 */
            $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

            $order['integral'] = $points;

            /* 获得收货人信息 */
            $consignee = get_consignee($_SESSION['user_id']);

            /* 对商品信息赋值 */
            $cart_goods = cart_goods($flow_type, $_SESSION['cart_value']); // 取得商品列表，计算合计

            if (empty($cart_goods) || (!check_consignee_info($consignee, $flow_type) && $_SESSION['store_id'] <= 0)) {
                $result['error'] = L('no_goods_in_cart');
            } else {
                //ecmoban模板堂 --zhuo start
                $cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
                $this->assign('cart_goods_number', $cart_goods_number);

                $consignee['province_name'] = get_goods_region_name($consignee['province']);
                $consignee['city_name'] = get_goods_region_name($consignee['city']);
                $consignee['district_name'] = get_goods_region_name($consignee['district']);
                $consignee['street'] = get_goods_region_name($consignee['street']);//街道
                $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];
                //$this->assign('consignee', $consignee);

                $cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1); // 取得商品列表，计算合计
                //$this->assign('goods_list', $cart_goods_list);
                //切换配送方式 by kong
                if ($tmp_shipping_id_arr) {
                    foreach ($cart_goods_list as $key => $val) {
                        foreach ($tmp_shipping_id_arr as $k => $v) {
                            if ($v[1] > 0 && $val['ru_id'] == $v[0]) {
                                $cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
                            }
                        }
                    }
                }

                /* 计算订单的费用 */
                $total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
                $this->assign('total', $total);
                //ecmoban模板堂 --zhuo end
                $this->assign('config', C('shop'));

                /* 团购标志 */
                if ($flow_type == CART_GROUP_BUY_GOODS) {
                    $this->assign('is_group_buy', 1);
                }

                $result['integral'] = $order['integral'];
                $result['amount'] = $total['amount_formated'];
                $result['content'] = $this->fetch('order_total');
                $result['error'] = '';
            }
        }

        exit(json_encode($result));
    }

    /**
     * 验证积分
     */
    public function actionCheckIntegral()
    {
        if (IS_AJAX) {
            $points = floatval($_GET['integral']);
            $user_info = user_info($_SESSION['user_id']);
            $flow_points = flow_available_points($_SESSION['cart_value']);  // 该订单允许使用的积分
            $user_points = $user_info['pay_points']; // 用户的积分总数

            if ($points > $user_points) {
                die($GLOBALS['_LANG']['integral_not_enough']);
            }

            if ($points > $flow_points) {
                die(sprintf($GLOBALS['_LANG']['integral_too_much'], $flow_points));
            }
            exit;
        }
    }

    /**
     * 配送时间，自提点选择
     */
    public function actionSelectPicksite()
    {
        $result = array('error' => 0, 'err_msg' => '', 'content' => '');
        $ru_id = I('request.ru_id', 0, 'intval');

        if (isset($_REQUEST['picksite_id'])) {
            $picksite_id = I('request.picksite_id', 0, 'intval');
            if (is_array($_SESSION['flow_consignee']['point_id'])) {
                $_SESSION['flow_consignee']['point_id'][$ru_id] = $picksite_id;
            }
        } elseif (isset($_REQUEST['shipping_date']) && isset($_REQUEST['time_range'])) {
            $shipping_date = I('request.shipping_date');
            $time_range = I('request.time_range');
            $_SESSION['flow_consignee']['shipping_dateStr'] = $shipping_date . $time_range;
        }
        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        /* 获得收货人信息 */
        $consignee = get_consignee($_SESSION['user_id']);

        /* 对商品信息赋值 */
        $cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1); // 取得商品列表，计算合计

        if (empty($cart_goods_list) || !check_consignee_info($consignee, $flow_type)) {
            if (empty($cart_goods)) {
                $result['error'] = 1;
                $result['err_msg'] = L('no_goods_in_cart');
            } elseif (!check_consignee_info($consignee, $flow_type)) {
                $result['error'] = 2;
                $result['err_msg'] = L('au_buy_after_login');
            }
        }
        exit(json_encode($result));
    }

    /**
     * 改变发票的设置
     */
    public function actionChangeNeedinv()
    {
        $result = array('error' => '', 'content' => '', 'amount' => '');
        $_GET['inv_type'] = !empty($_GET['inv_type']) ? json_str_iconv(urldecode($_GET['inv_type'])) : 0;//增值发票还是纸质发票
        $_GET['inv_payee'] = !empty($_GET['inv_payee']) ? json_str_iconv(urldecode($_GET['inv_payee'])) : 0;//发票抬头名称
        $_GET['inv_content'] = !empty($_GET['inv_content']) ? json_str_iconv(urldecode($_GET['inv_content'])) : '';//发票内容-明细
        $_GET['tax_id'] = !empty($_GET['tax_id']) ? json_str_iconv(urldecode($_GET['tax_id'])) : '';//纳税人识别码
        $_GET['invoice_id'] = !empty($_GET['invoice_id']) ? json_str_iconv(urldecode($_GET['invoice_id'])) : 0;//个人还是单位
        $_GET['invoice'] = !empty($_GET['invoice']) ? json_str_iconv(urldecode($_GET['invoice'])) : '';//
        $_GET['vat_id'] = !empty($_GET['vat_id']) ? json_str_iconv(urldecode($_GET['vat_id'])) : 0;//个人还是单位
        $_POST['shipping_id'] = strip_tags(urldecode($_REQUEST['shipping_id']));
        $tmp_shipping_id_arr = json_decode($_POST['shipping_id']);
        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        //添加门店ID判断
        $store_id = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;  // 门店id
        $store_id = !empty($_SESSION['store_id']) ? $_SESSION['store_id'] : $store_id;
        //添加门店ID判断

        /* 获得收货人信息 */
        $consignee = get_consignee($_SESSION['user_id']);

        /* 对商品信息赋值 */
        $cart_goods = cart_goods($flow_type, $_SESSION['cart_value']); // 取得商品列表，计算合计

        if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
            $result['error'] = L('no_goods_in_cart');
            die(json_encode($result));
        } else {
            /* 取得购物流程设置 */
            $this->assign('config', C('shop'));

            /* 取得订单信息 */
            $order = flow_order_info();
            /* 保存 session */
            $_SESSION['flow_order'] = $order;
            if (isset($_GET['need_inv']) && intval($_GET['need_inv']) == 1) {
                $order['need_inv'] = 1;
                $order['inv_type'] = trim(stripslashes($_GET['inv_type']));
                $order['inv_payee'] = trim(stripslashes($_GET['inv_payee']));
                $order['inv_content'] = trim(stripslashes($_GET['inv_content']));
                $order['tax_id'] = trim(stripslashes($_GET['tax_id']));
                $order['invoice_id'] = trim(stripslashes($_GET['invoice_id']));
                $order['invoice'] = trim(stripslashes($_GET['invoice']));
                $order['vat_id'] = trim(stripslashes($_GET['vat_id']));
                $order['invoice_type'] = trim(stripslashes($_GET['inv_type']));
            } else {
                $order['need_inv'] = 0;
                $order['inv_type'] = '';
                $order['inv_payee'] = '';   //个人名称-单位名称     1
                $order['inv_content'] = '';//明细                  1
                $order['tax_id'] = '';      // 识别码              1
                $order['invoice_id'] = 0;//个人还是单位
                $order['invoice'] = '';
                $order['vat_id'] = 0;//关联增值id                  1
                $order['invoice_type'] = 0;//普通还是增值 1
            }
            $invoice_id = trim(stripslashes($_GET['invoice']));
            /* 保存发票纳税人识别码 */
            $sql = "SELECT invoice_id FROM {pre}order_invoice WHERE inv_payee = '$order[inv_payee]' AND user_id = '$_SESSION[user_id]'";
            if (empty($invoice_id) && empty($order['tax_id'])) {
                if (!$GLOBALS['db']->getOne($sql)) {
                    $sql = "INSERT INTO {pre}order_invoice (inv_payee,user_id) VALUES ('$order[inv_payee]',$_SESSION[user_id])";
                    $this->db->query($sql);
                }
            } elseif (empty($invoice_id) && !empty($order['tax_id'])) {
                if (!$GLOBALS['db']->getOne($sql)) {
                    $sql = "INSERT INTO {pre}order_invoice (inv_payee,tax_id,user_id) VALUES ('$order[inv_payee]',$order[tax_id],$_SESSION[user_id])";
                    $this->db->query($sql);
                }
            } else {
                $sql = "UPDATE {pre}order_invoice SET tax_id='$order[tax_id]' WHERE invoice_id='$invoice_id'and user_id=$_SESSION[user_id]";
                $this->db->query($sql);
            }
            //ecmoban模板堂 --zhuo start
            //$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
            //$this->assign('cart_goods_number', $cart_goods_number);

            $consignee['province_name'] = get_goods_region_name($consignee['province']);
            $consignee['city_name'] = get_goods_region_name($consignee['city']);
            $consignee['district_name'] = get_goods_region_name($consignee['district']);
            $consignee['street'] = get_goods_region_name($consignee['street']);//街道
            $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];
            $this->assign('consignee', $consignee);
            $cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1); // 取得商品列表，计算合计
            //切换配送方式 by kong
            foreach ($cart_goods_list as $key => $val) {
                foreach ($tmp_shipping_id_arr as $k => $v) {
                    if ($v[1] > 0 && $val['ru_id'] == $v[0]) {
                        $cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
                    }
                }
            }

            /* 计算订单的费用 */
            $total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list, 0, 0, $store_id);
            $this->assign('total', $total);

            //ecmoban模板堂 --zhuo end

            /* 团购标志 */
            if ($flow_type == CART_GROUP_BUY_GOODS) {
                $this->assign('is_group_buy', 1);
            }
            $result['amount'] = $total['amount_formated'];
            $result['content'] = $this->fetch('order_total');
            die(json_encode($result));
        }
    }

    //管理收货地址
    public function actionAddressList()
    {
        if (IS_AJAX) {
            $id = I('address_id');
            drop_consignee($id);
            unset($_SESSION['flow_consignee']);
            exit;
        }
        $user_id = $_SESSION['user_id'];
        if ($_SESSION['user_id'] > 0) {
            $consignee_list = get_consignee_list($_SESSION['user_id']);
        } else {
            if (isset($_SESSION['flow_consignee'])) {
                $consignee_list = array($_SESSION['flow_consignee']);
            } else {
                $consignee_list[] = array('country' => C('shop.shop_country'));
            }
        }
        $this->assign('name_of_region', array(C('shop.name_of_region_1'), C('shop.name_of_region_2'), C('shop.name_of_region_3'), C('shop.name_of_region_4')));
        if ($consignee_list) {
            foreach ($consignee_list as $k => $v) {
                $address = '';
                if ($v['province']) {
                    $res = get_region_name($v['province']);
                    $address .= $res['region_name'];
                }
                if ($v['city']) {
                    $ress = get_region_name($v['city']);
                    $address .= $ress['region_name'];
                }
                if ($v['district']) {
                    $resss = get_region_name($v['district']);
                    $address .= $resss['region_name'];
                }
                if ($v['street']) {
                    $resss = get_region_name($v['street']);
                    $address .= $resss['region_name'];
                }
                $consignee_list[$k]['address'] = $address . ' ' . $v['address'];
                $consignee_list[$k]['url'] = url('user/edit_address', array('id' => $v['address_id']));
            }
        } else {
            $this->redirect('add_address');
        }
        $default_id = $this->db->getOne("SELECT address_id FROM {pre}users WHERE user_id='$user_id'");
        $address_id = $_SESSION['flow_consignee']['address_id'];
        /* 取得每个收货地址的省市区列表 */
        $this->assign('defulte_id', $default_id);
        $this->assign('address_id', $address_id);
        $this->assign('consignee_list', $consignee_list);
        $this->assign('page_title', L('receiving_address'));
        $this->display();
    }

    /**
     * 添加收货地址
     */
    public function actionAddAddress()
    {
        if (IS_POST) {
            $consignee = array(
                'address_id' => I('address_id'),
                'consignee' => I('consignee'),
                'country' => 1,
                'province' => I('province_region_id'),
                'city' => I('city_region_id'),
                'district' => I('district_region_id'),
                'street' => I('town_region_id'),
                'email' => I('email'),
                'address' => I('address'),
                'zipcode' => I('zipcode'),
                'tel' => I('tel'),
                'mobile' => I('mobile'),
                'sign_building' => I('sign_building'),
                'best_time' => I('best_time'),
                'user_id' => $_SESSION['user_id']
            );

            if (preg_match('/^1[3|5|8|7|4]\d{9}$/', $consignee['mobile']) == false) {
                exit(json_encode(array('status' => 'n', 'info' => L('msg_mobile_format_error'))));
            }
            $limit_address = $this->db->getOne("select count(address_id) from {pre}user_address where user_id = '" . $consignee['user_id'] . "'");
            if (!empty($consignee['address_id'])) {
                if ($limit_address > 5) {
                    exit(json_encode(array('status' => 'n', 'info' => L('msg_save_address'))));
                }
            }
            if ($_SESSION['user_id'] > 0) {
                /* 如果用户已经登录，则保存收货人信息 */
                save_consignee($consignee, false);
            }
            /* 保存到session */
            $_SESSION['flow_consignee'] = stripslashes_deep($consignee);
            $back_act = url('address_list');
            if (isset($_SESSION['flow_consignee']) && empty($consignee['address_id'])) {
                exit(json_encode(array('status' => 'y', 'info' => L('success_address'), 'url' => $back_act)));
            } elseif (isset($_SESSION['flow_consignee']) && !empty($consignee['address_id'])) {
                exit(json_encode(array('status' => 'y', 'info' => L('edit_address'), 'url' => $back_act)));
            } else {
                exit(json_encode(array('status' => 'n', 'info' => L('error_address'))));
            }
        }
        if (is_wechat_browser() && is_dir(APP_WECHAT_PATH)) {
            $is_wechat = 1;
        }
        $this->assign('is_wechat', $is_wechat);
        $this->assign('user_id', $_SESSION['user_id']);
        $this->assign('country_list', get_regions());
        $this->assign('shop_country', C('shop.shop_country'));

        $this->assign('shop_province_list', get_regions(1, C('shop.shop_country')));

        $this->assign('address_id', I('address_id'));
        $province_list = get_regions(1, C('shop.shop_country'));
        $this->assign('province_list', $province_list); //省、直辖市
        $city_list = get_region_city_county($this->province_id);
        if ($city_list) {
            foreach ($city_list as $k => $v) {
                $city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
            }
        }
        $this->assign('city_list', $city_list); //省下级市
        $district_list = get_region_city_county($this->city_id);
        $this->assign('district_list', $district_list); //市下级县
        if (I('address_id')) {
            $address_id = intval($_GET['address_id']);
            $consignee_list = $this->db->getRow("SELECT * FROM {pre}user_address WHERE user_id='$_SESSION[user_id]]' AND address_id='$address_id'");
            if (empty($consignee_list)) {
                exit(json_encode(array('status' => 'n', 'info' => L('no_address'))));
            }
            $province = get_region_name($consignee_list['province']);
            $city = get_region_name($consignee_list['city']);
            $district = get_region_name($consignee_list['district']);
            $town = get_region_name($consignee_list['street']);

            $consignee_list['province'] = $province['region_name'];
            $consignee_list['city'] = $city['region_name'];
            $consignee_list['district'] = $district['region_name'];
            $consignee_list['town'] = $town['region_name'];

            $consignee_list['province_id'] = $province['region_id'];
            $consignee_list['city_id'] = $city['region_id'];
            $consignee_list['district_id'] = $district['region_id'];
            $consignee_list['town_region_id'] = $town['region_id'];

            $city_list = get_region_city_county($province['region_id']);

            if ($city_list) {
                foreach ($city_list as $k => $v) {
                    $city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
                }
            }
            $this->assign('city_list', $city_list); //省下级市
            $this->assign('consignee_list', $consignee_list);
            $this->assign('page_title', '修改收货地址');
            $this->display();
        } else {
            $this->assign('page_title', '添加收货地址');
            $this->display();
        }
    }

    /**
     * 添加收货地址
     */
    public function actionEditAddress()
    {
        if (IS_POST) {
            $consignee = array(
                'address_id' => I('address_id'),
                'consignee' => I('consignee'),
                'country' => 1,
                'province' => I('province_region_id'),
                'city' => I('city_region_id'),
                'district' => I('district_region_id'),
                'email' => I('email'),
                'address' => I('address'),
                'zipcode' => I('zipcode'),
                'tel' => I('tel'),
                'mobile' => I('mobile'),
                'sign_building' => I('sign_building'),
                'best_time' => I('best_time'),
                'user_id' => $_SESSION['user_id']
            );

            //验证收货人
            if (empty($consignee['consignee'])) {
                show_message(L('msg_receiving_notnull'));
            }
            //验证手机号码
            if (empty($consignee['mobile'])) {
                show_message(L('msg_contact_way_notnull'));
            }
            // 验证手机号格式
            if (is_mobile($consignee['mobile']) == false) {
                show_message(L('msg_mobile_format_error'));
            }
            if (empty($consignee['address'])) {
                show_message(L('msg_address_notnull'));
            }
            $limit_address = $this->db->getOne("select count(address_id) from {pre}user_address where user_id = '" . $consignee['user_id'] . "'");
            if ($limit_address > 5) {
                show_message(L('msg_save_address'));
            }
            if ($_SESSION['user_id'] > 0) {
                /* 如果用户已经登录，则保存收货人信息 */
                save_consignee($consignee, true);
            }
            /* 保存到session */
            $_SESSION['flow_consignee'] = stripslashes_deep($consignee);
            ecs_header("Location: " . url('team/flow/index') . "\n");
            exit;
        }
        $this->assign('user_id', $_SESSION['user_id']);
        $this->assign('country_list', get_regions());
        $this->assign('shop_country', C('shop.shop_country'));
        $this->assign('shop_province_list', get_regions(1, C('shop.shop_country')));
        $this->assign('address_id', I('address_id'));
        $province_list = get_regions(1, C('shop.shop_country'));
        $this->assign('province_list', $province_list); //省、直辖市
        $city_list = get_region_city_county($this->province_id);
        if ($city_list) {
            foreach ($city_list as $k => $v) {
                $city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
            }
        }
        if (I('address_id')) {
            $address_id = $_GET['address_id'];
            $consignee_list = $this->db->getRow("SELECT * FROM {pre}user_address WHERE user_id='$_SESSION[user_id]]' AND address_id='$address_id'");
            if (empty($consignee_list)) {
                show_message(L('not_exist_address'));
            }
            $c = get_region_name($consignee_list['province']);
            $cc = get_region_name($consignee_list['city']);
            $ccc = get_region_name($consignee_list['district']);
            $consignee_list['province'] = $c['region_name'];
            $consignee_list['city'] = $cc['region_name'];
            $consignee_list['district'] = $ccc['region_name'];
            $consignee_list['province_id'] = $c['region_id'];
            $consignee_list['city_id'] = $cc['region_id'];
            $consignee_list['district_id'] = $ccc['region_id'];
            $city_list = get_region_city_county($c['region_id']);
            if ($city_list) {
                foreach ($city_list as $k => $v) {
                    $city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
                }
            }
            $this->assign('consignee_list', $consignee_list);
        }
        $this->assign('city_list', $city_list); //省下级市
        $district_list = get_region_city_county($this->city_id);
        $this->assign('district_list', $district_list); //市下级县
        $this->assign('page_title', L('edit_address'));
        $this->display();
    }

    /**
     * AJAX显示地区名称
     */
    public function actionShowRegionName()
    {
        if (IS_AJAX) {
            $data['province'] = get_region_name(I('province'));
            $data['city'] = get_region_name(I('city'));
            $data['district'] = get_region_name(I('district'));
            die(json_encode($data));
        }
    }

    /**
     * 异步设置默认地址
     */
    public function actionSetAddress()
    {
        if (IS_AJAX) {
            $user_id = session('user_id');
            $address_id = isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0;
            $sql = "SELECT * FROM {pre}user_address WHERE address_id = '$address_id' AND user_id = '$user_id'";
            $address = $this->db->getRow($sql);
            if (!empty($address)) {
                $_SESSION['flow_consignee'] = $address;
                echo json_encode(array('url' => url('team/flow/index'), 'status' => 1));
            } else {
                echo json_encode(array('status' => 0));
            }
        }
    }

    /**
     * 添加礼包到购物车
     */
    public function actionAddPackageToCart()
    {
        if (IS_AJAX) {
            $_POST['package_info'] = stripslashes($_POST['package_info']);
            $result = array('error' => 0, 'message' => '', 'content' => '', 'package_id' => '');

            if (empty($_POST['package_info'])) {
                $result['error'] = 1;
                die(json_encode($result));
            }

            $package = json_decode($_POST['package_info']);

            /* 如果是一步购物，先清空购物车 */
            if (C('shop.one_step_buy') == '1') {
                clear_cart();
            }
            /* 商品数量是否合法 */
            if (!is_numeric($package->number) || intval($package->number) <= 0) {
                $result['error'] = 1;
                $result['message'] = L('invalid_number');
            } else {
                /* 添加到购物车 */
                if (add_package_to_cart($package->package_id, $package->number, $package->warehouse_id, $package->area_id)) {
                    if (C('shop.cart_confirm') > 2) {
                        $result['message'] = '';
                    } else {
                        $result['message'] = C('shop.cart_confirm') == 1 ? L('addto_cart_success_1') : L('addto_cart_success_2');
                    }

                    $result['content'] = insert_cart_info();
                    $result['one_step_buy'] = C('shop.one_step_buy');
                } else {
                    $result['message'] = $GLOBALS['err']->last_message();
                    $result['error'] = $GLOBALS['err']->error_no;
                    $result['package_id'] = stripslashes($package->package_id);
                }
            }

            $confirm_type = isset($package->confirm_type) ? $package->confirm_type : 0;

            if ($confirm_type > 0) {
                $result['confirm_type'] = $confirm_type;
            } else {
                $cart_confirm = C('shop.cart_confirm');
                $result['confirm_type'] = !empty($cart_confirm) ? $cart_confirm : 2;
            }
            die(json_encode($result));
        }
    }

    /**
     * 验证是否登录
     */
    public function check_login()
    {
        $without = array('AddPackageToCart');
        if (!$_SESSION['user_id'] && !in_array(ACTION_NAME, $without)) {
            if (IS_AJAX) {
                $this->ajaxReturn(array('error' => 1, 'message' => L('yet_login')));
            }
            ecs_header("Location: " . url('user/login/index'));
        }
    }

    /**
     * 通过 用户优惠券ID 获取该条优惠券详情 bylu
     * @param $uc_id 用户优惠券ID
     * @return mixed
     */
    public function get_coupons($uc_id)
    {
        $time = gmtime();
        return $GLOBALS['db']->getRow(" SELECT c.*,cu.* FROM " . $GLOBALS['ecs']->table('coupons_user') . " cu LEFT JOIN " . $GLOBALS['ecs']->table('coupons') . " c ON c.cou_id=cu.cou_id WHERE cu.uc_id='$uc_id' AND cu.user_id='" . $_SESSION['user_id'] . "' AND c.cou_end_time>$time ORDER BY  cu.uc_id DESC limit 1 ");
    }

    /**
     * 设置优惠券为已使用
     * @param   int $bonus_id 优惠券id
     * @param   int $order_id 订单id
     * @return  bool
     */
    public function use_coupons($cou_id, $order_id)
    {
        $sql = "UPDATE " . $GLOBALS['ecs']->table('coupons_user') .
            " SET order_id = '$order_id', is_use_time = '" . gmtime() . "', is_use =1 " .
            "WHERE uc_id = '$cou_id'";

        return $GLOBALS['db']->query($sql);
    }
}
