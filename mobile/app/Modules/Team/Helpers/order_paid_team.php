<?php

/**
 * 修改拼团订单的支付状态
 *
 * @access  public
 * @param   string $log_id 支付编号
 * @param   integer $pay_status 状态
 * @param   string $note 备注
 * @return  void
 */
function order_paid_team($log_id, $pay_status = PS_PAYED, $note = '')
{
    /* 取得支付编号 */
    $log_id = intval($log_id);
    if ($log_id > 0) {
        /* 取得要修改的支付记录信息 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('pay_log') .
            " WHERE log_id = '$log_id'";
        $pay_log = $GLOBALS['db']->getRow($sql);
        if ($pay_log && $pay_log['is_paid'] == 0) {
            /* 修改此次支付操作的状态为已付款 */
            $sql = 'UPDATE ' . $GLOBALS['ecs']->table('pay_log') .
                " SET is_paid = '1' WHERE log_id = '$log_id'";
            $GLOBALS['db']->query($sql);

            /* 根据记录类型做相应处理 */
            if ($pay_log['order_type'] == PAY_ORDER) {
                /* 取得订单信息 */
                $sql = 'SELECT main_order_id, order_id, user_id, order_sn, consignee, address, tel, mobile, shipping_id, pay_status, extension_code, extension_id, goods_amount,team_id, ' .
                    'shipping_fee, insure_fee, pay_fee, tax, pack_fee, card_fee, surplus, money_paid, integral_money, bonus, order_amount, discount ' .
                    'FROM ' . $GLOBALS['ecs']->table('order_info') .
                    " WHERE order_id = '$pay_log[order_id]'";
                $order = $GLOBALS['db']->getRow($sql);
                $main_order_id = $order['main_order_id'];
                $order_id = $order['order_id'];
                $order_sn = $order['order_sn'];

                /* 修改订单状态为已付款 */
                $sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') .
                    " SET order_status = '" . OS_CONFIRMED . "', " .
                    " confirm_time = '" . gmtime() . "', " .
                    " pay_status = '$pay_status', " .
                    " pay_time = '" . gmtime() . "', " .
                    " money_paid = order_amount," .
                    " order_amount = 0 " .
                    "WHERE order_id = '$order_id'";
                $GLOBALS['db']->query($sql);

                /* 记录订单操作记录 */
                order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, $pay_status, $note, $GLOBALS['_LANG']['buyer']);

                /* 修改子订单状态为已付款 by wanganlin */
                $sql = "SELECT order_id FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE main_order_id = '$order_id'";
                $child_order_id_arr = $GLOBALS['db']->getAll($sql);
                if ($main_order_id == 0 && count($child_order_id_arr) > 0) {
                    $sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') .
                        " SET order_status = '" . OS_CONFIRMED . "', " .
                        " confirm_time = '" . gmtime() . "', " .
                        " pay_status = '$pay_status', " .
                        " pay_time = '" . gmtime() . "', " .
                        " money_paid = order_amount," .
                        " order_amount = 0 " .
                        "WHERE main_order_id = '$order_id'";
                    $GLOBALS['db']->query($sql);

                    $sql = 'SELECT order_sn ' . 'FROM ' . $GLOBALS['ecs']->table('order_info') .
                        " WHERE main_order_id = '$order_id'";
                    $order_res = $GLOBALS['db']->getAll($sql);
                    foreach ($order_res as $row) {
                        /* 记录订单操作记录 */
                        order_action($row['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, $pay_status, $note, L('buyer'));
                    }
                }

                /* 拼团状态的更改 sty */
                $team_id = $order['team_id'];
                if ($team_id > 0) {
                    $sql = "select g.goods_id,g.limit_num, g.team_num from " . $GLOBALS['ecs']->table('team_log') . " as tl LEFT JOIN " . $GLOBALS['ecs']->table('team_goods') . " as g ON tl.goods_id = g.goods_id where tl.team_id =$team_id ";
                    $res = $GLOBALS['db']->getRow($sql);
                    //验证拼团是否成功
                    $sql = "SELECT count(order_id) as num  FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE team_id = '" . $team_id . "' AND extension_code = 'team_buy'  and pay_status = '" . PS_PAYED . "' ";
                    $team_count = $GLOBALS['db']->getRow($sql);
                    if ($team_count['num'] >= $res['team_num']) {
                        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('team_log') .
                            " SET status = '1' " .
                            " WHERE team_id = '" . $team_id . "' ";
                        $GLOBALS['db']->query($sql);
                    }

                    //统计拼团人数
                    $limit_num = $res['limit_num'] + 1;
                    $sql = 'UPDATE ' . $GLOBALS['ecs']->table('team_goods') .
                        " SET limit_num = '$limit_num' " .
                        " WHERE goods_id = '" . $res['goods_id'] . "' ";
                    $GLOBALS['db']->query($sql);
                }

                /* 拼团状态的更改 end */

                /* 如果需要，发短信 */
                $sql = "SELECT ru_id FROM " . $GLOBALS['ecs']->table('order_goods') . " WHERE order_id = '$order_id' LIMIT 1";
                $ru_id = $GLOBALS['db']->getOne($sql);
                if ($ru_id == 0) {
                    $sms_shop_mobile = $GLOBALS['_CFG']['sms_shop_mobile'];
                } else {
                    $sql = "SELECT mobile FROM " . $GLOBALS['ecs']->table('seller_shopinfo') . " WHERE ru_id = '$ru_id'";
                    $sms_shop_mobile = $GLOBALS['db']->getOne($sql);
                }

                if ($GLOBALS['_CFG']['sms_order_payed'] == '1' && $sms_shop_mobile != '') {
                    // 组装数据
                    $message = array(
                        'consignee' => $order['consignee'],
                        'order_mobile' => $order['mobile']
                    );
                    send_sms($sms_shop_mobile, 'sms_order_payed', $message);
                }

                /* 如果安装微信通,订单支付成功消息提醒 */

                if (is_dir(APP_WECHAT_PATH) && !empty($pay_log['openid'])) {
                    $pushData = array(
                        'keyword1' => array('value' => $order_sn, 'color' => '#173177'),
                        'keyword2' => array('value' => '已付款', 'color' => '#173177'),
                        'keyword3' => array('value' => date('Y-m-d', gmtime()), 'color' => '#173177'),
                        'keyword4' => array('value' => $GLOBALS['_CFG']['shop_name'], 'color' => '#173177'),
                        'keyword5' => array('value' => number_format($pay_log['order_amount'], 2, '.', ''), 'color' => '#173177')
                    );
                    $order_url = __HOST__ . url('user/order/detail', array('order_id' => $order_id));
                    $url = str_replace('public/notify/wxpay.php', 'index.php', $order_url);
                    push_template('OPENTM204987032', $pushData, $url, $order['user_id']);
                }

                /* 对虚拟商品的支持 */
                $virtual_goods = get_virtual_goods($order_id);
                if (!empty($virtual_goods)) {
                    $msg = '';
                    if (!virtual_goods_ship($virtual_goods, $msg, $order_sn, true)) {
                        $GLOBALS['_LANG']['pay_success'] .= '<div style="color:red;">' . $msg . '</div>' . $GLOBALS['_LANG']['virtual_goods_ship_fail'];
                    }

                    /* 如果订单没有配送方式，自动完成发货操作 */
                    if ($order['shipping_id'] == -1) {
                        /* 将订单标识为已发货状态，并记录发货记录 */
                        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') .
                            " SET shipping_status = '" . SS_SHIPPED . "', shipping_time = '" . gmtime() . "'" .
                            " WHERE order_id = '$order_id'";
                        $GLOBALS['db']->query($sql);

                        /* 记录订单操作记录 */
                        order_action($order_sn, OS_CONFIRMED, SS_SHIPPED, $pay_status, $note, $GLOBALS['_LANG']['buyer']);
                        $integral = integral_to_give($order);
                        log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($GLOBALS['_LANG']['order_gift_integral'], $order['order_sn']));
                    }
                }
            }
        }
    }
}
