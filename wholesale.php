<?php


define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . '/includes/lib_area.php');  
require(ROOT_PATH . 'includes/lib_publicfunc.php'); 
require(ROOT_PATH . 'includes/lib_wholesale.php'); 

if($GLOBALS['_CFG']['wholesale_user_rank'] == 0){
    $is_seller = get_is_seller();
    if($is_seller == 0){
        ecs_header("Location: " .$ecs->url(). "\n");
    }
}




if (empty($_REQUEST['act'])) {
    $_REQUEST['act'] = 'index';
}


$sql = "SELECT user_id FROM " . $ecs->table('admin_user') . " WHERE ru_id = '" . $_SESSION['user_id'] . "'";
$seller_id = $db->getOne($sql, true);
$smarty->assign('seller_id', $seller_id);
$smarty->assign('cfg', $_CFG);




if ($_REQUEST['act'] == 'index') {
    
    assign_template();
    $position = assign_ur_here();
    $smarty->assign('page_title', $position['title']);    
    $smarty->assign('ur_here', $position['ur_here']);  
    $smarty->assign('index', $_REQUEST['act']);
	
	
	
    if (defined('THEME_EXTENSION')) {
        $wholesale_cat = get_wholesale_child_cat();
        $smarty->assign('wholesale_cat', $wholesale_cat);
    }

    $wholesale_limit = get_wholesale_limit();
    $smarty->assign('wholesale_limit', $wholesale_limit);
    $smarty->assign('helps', get_shop_help());       

    $smarty->assign('get_wholesale_cat', get_wholesale_cat());
    $res = get_purchase_list();
    $purchase_list = $res['purchase_list'];
    $smarty->assign('purchase', $purchase_list);
	
	$get_wholsale_navigator = get_wholsale_navigator();
	$smarty->assign('get_wholsale_navigator', $get_wholsale_navigator);

    if (defined('THEME_EXTENSION')) {
        
        for ($i = 1; $i <= $_CFG['auction_ad']; $i++) {
            $wholesale_ad .= "'wholesale_ad" . $i . ","; 
            $wholesale_cat_ad .= "'wholesale_cat_ad" . $i . ","; 
        }
        $smarty->assign('wholesale_ad', $wholesale_ad);
        $smarty->assign('wholesale_cat_ad', $wholesale_cat_ad);
    }

    assign_dynamic('wholesale');

    
    $smarty->display('wholesale_list.dwt');
}



 

elseif ($_REQUEST['act'] == 'price_list') {
    $data = $_LANG['goods_name'] . "\t" . $_LANG['goods_attr'] . "\t" . $_LANG['number'] . "\t" . $_LANG['ws_price'] . "\t\n";
    $sql = "SELECT * FROM " . $ecs->table('wholesale') .
            "WHERE enabled = 1 AND review_status = 3 AND CONCAT(',', rank_ids, ',') LIKE '" . '%,' . $_SESSION['user_rank'] . ',%' . "'";
    $res = $db->query($sql);
    while ($row = $db->fetchRow($res)) {
        $price_list = unserialize($row['prices']);
        foreach ($price_list as $attr_price) {
            if ($attr_price['attr']) {
                $sql = "SELECT attr_value FROM " . $ecs->table('goods_attr') .
                        " WHERE goods_attr_id " . db_create_in($attr_price['attr']);
                $goods_attr = join(',', $db->getCol($sql));
            } else {
                $goods_attr = '';
            }
            foreach ($attr_price['qp_list'] as $qp) {
                $data .= $row['goods_name'] . "\t" . $goods_attr . "\t" . $qp['quantity'] . "\t" . $qp['price'] . "\t\n";
            }
        }
    }

    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=price_list.xls");
    if (EC_CHARSET == 'utf-8') {
        echo ecs_iconv('UTF8', 'GB2312', $data);
    } else {
        echo $data;
    }
}



 

elseif ($_REQUEST['act'] == 'add_to_cart') {
    
    $act_id = intval($_POST['act_id']);
    $goods_number = $_POST['goods_number'][$act_id];
    $attr_id = isset($_POST['attr_id']) ? $_POST['attr_id'] : array();
    if (isset($attr_id[$act_id])) {
        $goods_attr = $attr_id[$act_id];
    }

    

    
    if (empty($goods_number) || (is_array($goods_number) && array_sum($goods_number) <= 0)) {
        show_message($_LANG['ws_invalid_goods_number']);
    }

    
    $goods_list = array();
    if (is_array($goods_number)) {
        foreach ($goods_number as $key => $value) {
            if (!$value) {
                unset($goods_number[$key], $goods_attr[$key]);
                continue;
            }

            $goods_list[] = array('number' => $goods_number[$key], 'goods_attr' => $goods_attr[$key]);
        }
    } else {
        $goods_list[0] = array('number' => $goods_number, 'goods_attr' => '');
    }

    
    $wholesale = wholesale_info($act_id);

    
    if (isset($_SESSION['wholesale_goods'])) {
        foreach ($_SESSION['wholesale_goods'] as $goods) {
            if ($goods['goods_id'] == $wholesale['goods_id']) {
                if (empty($goods_attr)) {
                    show_message($_LANG['ws_goods_attr_exists']);
                } elseif (in_array($goods['goods_attr_id'], $goods_attr)) {
                    show_message($_LANG['ws_goods_attr_exists']);
                }
            }
        }
    }

    
    $attr_matching = false;
    foreach ($wholesale['price_list'] as $attr_price) {
        
        if (empty($attr_price['attr'])) {
            $attr_matching = true;
            $goods_list[0]['qp_list'] = $attr_price['qp_list'];
            break;
        }
        
        elseif (($key = is_attr_matching($goods_list, $attr_price['attr'])) !== false) {
            $attr_matching = true;
            $goods_list[$key]['qp_list'] = $attr_price['qp_list'];
        }
    }
    if (!$attr_matching) {
        show_message($_LANG['ws_attr_not_matching']);
    }

    
    foreach ($goods_list as $goods_key => $goods) {
        if ($goods['number'] < $goods['qp_list'][0]['quantity']) {
            show_message($_LANG['ws_goods_number_not_enough']);
        } else {
            $goods_price = 0;
            foreach ($goods['qp_list'] as $qp) {
                if ($goods['number'] >= $qp['quantity']) {
                    $goods_list[$goods_key]['goods_price'] = $qp['price'];
                } else {
                    break;
                }
            }
        }
    }

    
    foreach ($goods_list as $goods_key => $goods) {
        
        $goods_attr_name = '';
        if (!empty($goods['goods_attr'])) {
            foreach ($goods['goods_attr'] as $key => $attr) {
                $attr['attr_name'] = htmlspecialchars($attr['attr_name']);
                $goods['goods_attr'][$key]['attr_name'] = $attr['attr_name'];
                $attr['attr_val'] = htmlspecialchars($attr['attr_val']);
                $goods_attr_name .= $attr['attr_name'] . '：' . $attr['attr_val'] . '&nbsp;';
            }
        }

        
        $total = $goods['number'] * $goods['goods_price'];
        $goods_img = $db->getOne("SELECT goods_thumb FROM " . $ecs->table("goods") . " WHERE goods_id = '" . $wholesale['goods_id'] . "'");
        $_SESSION['wholesale_goods'][] = array(
            'goods_id' => $wholesale['goods_id'],
            'goods_name' => $wholesale['goods_name'],
            'goods_attr_id' => $goods['goods_attr'],
            'goods_attr' => $goods_attr_name,
            'goods_number' => $goods['number'],
            'goods_price' => $goods['goods_price'],
            'subtotal' => $total,
            'formated_goods_price' => price_format($goods['goods_price'], false),
            'formated_subtotal' => price_format($total, false),
            'goods_url' => build_uri('goods', array('gid' => $wholesale['goods_id']), $wholesale['goods_name']),
            'goods_img' => get_image_path($wholesale['goods_id'], $goods_img, true)
        );
    }

    unset($goods_attr, $attr_id, $goods_list, $wholesale, $goods_attr_name);

    
    ecs_header("Location: ./wholesale.php\n");
    exit;
}



 

elseif ($_REQUEST['act'] == 'drop_goods') {
    $key = intval($_REQUEST['key']);
    if (isset($_SESSION['wholesale_goods'][$key])) {
        unset($_SESSION['wholesale_goods'][$key]);
    }

    
    ecs_header("Location: ./wholesale.php\n");
    exit;
}



 

elseif ($_REQUEST['act'] == 'submit_order') {
    include_once(ROOT_PATH . 'includes/lib_order.php');

    
    if (count($_SESSION['wholesale_goods']) == 0) {
        show_message($_LANG['no_goods_in_cart']);
    }

    
    if (empty($_POST['remark'])) {
        show_message($_LANG['ws_remark']);
    }

    
    $goods_amount = 0;
    foreach ($_SESSION['wholesale_goods'] as $goods) {
        $goods_amount += $goods['subtotal'];
    }

    $order = array(
        'postscript' => htmlspecialchars($_POST['remark']),
        'user_id' => $_SESSION['user_id'],
        'add_time' => gmtime(),
        'order_status' => OS_UNCONFIRMED,
        'shipping_status' => SS_UNSHIPPED,
        'pay_status' => PS_UNPAYED,
        'goods_amount' => $goods_amount,
        'order_amount' => $goods_amount,
    );

    
    $error_no = 0;
    do {
        $order['order_sn'] = get_order_sn(); 
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $order, 'INSERT');

        $error_no = $GLOBALS['db']->errno();

        if ($error_no > 0 && $error_no != 1062) {
            die($GLOBALS['db']->errorMsg());
        }
    } while ($error_no == 1062); 

    $new_order_id = $db->insert_id();
    $order['order_id'] = $new_order_id;

    
    foreach ($_SESSION['wholesale_goods'] as $goods) {
        
        $product_id = 0;
        if (!empty($goods['goods_attr_id'])) {
            $goods_attr_id = array();
            foreach ($goods['goods_attr_id'] as $value) {
                $goods_attr_id[$value['attr_id']] = $value['attr_val_id'];
            }

            ksort($goods_attr_id);
            $goods_attr = implode('|', $goods_attr_id);

            $sql = "SELECT product_id FROM " . $ecs->table('products') . " WHERE goods_attr = '$goods_attr' AND goods_id = '" . $goods['goods_id'] . "'";
            $product_id = $db->getOne($sql);
        }

        $sql = "INSERT INTO " . $ecs->table('order_goods') . "( " .
                "order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, " .
                "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, ru_id) " .
                " SELECT '$new_order_id', goods_id, goods_name, goods_sn, '$product_id','$goods[goods_number]', market_price, " .
                "'$goods[goods_price]', '$goods[goods_attr]', is_real, extension_code, 0, 0 , user_id " .
                " FROM " . $ecs->table('goods') .
                " WHERE goods_id = '$goods[goods_id]'";
        $db->query($sql);
    }

    
    if ($_CFG['service_email'] != '') {
        $tpl = get_mail_template('remind_of_new_order');
        $smarty->assign('order', $order);
        $smarty->assign('shop_name', $_CFG['shop_name']);
        $smarty->assign('send_date', local_date($_CFG['time_format'], gmtime()));
        $content = $smarty->fetch('str:' . $tpl['template_content']);
        send_mail($_CFG['shop_name'], $_CFG['service_email'], $tpl['template_subject'], $content, $tpl['is_html']);
    }

    
    if ($_CFG['sms_order_placed'] == '1' && $_CFG['sms_shop_mobile'] != '') {

        $sql = "SELECT user_name, mobile_phone FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '" . $_SESSION['user_id'] . "' LIMIT 1";
        $info = $GLOBALS['db']->getRow($sql);

        
        $smsParams = array(
            'shop_name' => '',
            'user_name' => $_CFG['shop_name'],
            'username' => $_CFG['shop_name'],
            'order_msg' => $msg ? $msg : '',
            'ordermsg' => $msg ? $msg : '',
            'consignee' => $info['user_name'],
            'order_mobile' => $info['mobile_phone'],
            'ordermobile' => $info['mobile_phone'],
            'mobile_phone' => $_CFG['sms_shop_mobile'],
            'mobilephone' => $_CFG['sms_shop_mobile']
        );

        if ($GLOBALS['_CFG']['sms_type'] == 0) {

            huyi_sms($smsParams, 'sms_order_placed');
        } elseif ($GLOBALS['_CFG']['sms_type'] >=1) {
            $result = sms_ali($smsParams, 'sms_order_placed'); 
            $resp = $GLOBALS['ecs']->ali_yu($result);
        }
    }

    
    unset($_SESSION['wholesale_goods']);

    
    show_message(sprintf($_LANG['ws_order_submitted'], $order['order_sn']), $_LANG['ws_return_wholesale'], 'wholesale.php');
}


function is_attr_matching(&$goods_list, $reference) {
    foreach ($goods_list as $key => $goods) {
        
        if (count($goods['goods_attr']) != count($reference)) {
            break;
        }

        
        $is_check = true;
        if (is_array($goods['goods_attr'])) {
            foreach ($goods['goods_attr'] as $attr) {
                if (!(array_key_exists($attr['attr_id'], $reference) && $attr['attr_val_id'] == $reference[$attr['attr_id']])) {
                    $is_check = false;
                    break;
                }
            }
        }
        if ($is_check) {
            return $key;
            break;
        }
    }

    return false;
}


function get_wholesale_cat() {
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('wholesale_cat') . "WHERE parent_id = 0 ORDER BY sort_order ASC ";
    $cat_res = $GLOBALS['db']->getAll($sql);

    foreach ($cat_res as $key => $row) {
        $cat_res[$key]['goods'] = get_business_goods($row['cat_id']);
        $cat_res[$key]['count_goods'] = count(get_business_goods($row['cat_id']));
        $cat_res[$key]['cat_url'] = build_uri('wholesale_cat', array('act' => 'list', 'cid' => $row['cat_id']), $row['cat_name']);
    }
    return $cat_res;
}


function get_business_goods($cat_id) {
    $table = 'wholesale_cat';
    $type = 4;
    $children = get_children($cat_id, $type, 0, $table);
    $sql = "SELECT w.*, g.goods_thumb, g.goods_img, MIN(wvp.volume_number) AS volume_number, MAX(wvp.volume_price) AS volume_price, g.goods_unit FROM " . $GLOBALS['ecs']->table('wholesale') . " AS w "
            . " LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON w.goods_id = g.goods_id "
            . " LEFT JOIN " . $GLOBALS['ecs']->table('wholesale_volume_price') . " AS wvp ON wvp.goods_id = g.goods_id "
            . " WHERE ($children OR " . get_wholesale_extension_goods($children, 'w.') . ") AND w.enabled = 1 AND w.review_status = 3 GROUP BY goods_id";
    $res = $GLOBALS['db']->getAll($sql);
    foreach ($res as $key => $row) {
        $res[$key]['goods_extend'] = get_wholesale_extend($row['goods_id']);
        $res[$key]['goods_sale'] = get_sale($row['goods_id']);
        $res[$key]['goods_price'] = $row['goods_price'];
        $res[$key]['moq'] = $row['moq'];
        $res[$key]['volume_number'] = $row['volume_number'];
        $res[$key]['volume_price'] = $row['volume_price'];
		$res[$key]['goods_unit'] = $row['goods_unit'];
        $res[$key]['goods_name'] = $row['goods_name'];
        $res[$key]['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $res[$key]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
        $res[$key]['url'] = build_uri('wholesale_goods', array('aid' => $row['act_id']), $row['goods_name']);
    }

    return $res;
}


function get_wholesale_limit() {
    $now = gmtime();
    $sql = "SELECT w.*, g.goods_name, g.goods_thumb, g.goods_img, MIN(wvp.volume_number) AS volume_number, MAX(wvp.volume_price) AS volume_price, g.goods_unit FROM " . $GLOBALS['ecs']->table('wholesale') . " AS w"
            . " LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON w.goods_id = g.goods_id "
            . " LEFT JOIN " . $GLOBALS['ecs']->table('wholesale_volume_price') . " AS wvp ON wvp.goods_id = g.goods_id "
            . " WHERE w.enabled = 1 AND w.review_status = 3 AND w.is_promote = 1 AND '$now' BETWEEN w.start_time AND w.end_time GROUP BY goods_id";
    $res = $GLOBALS['db']->getAll($sql);
    foreach ($res as $key => $row) {
        $res[$key]['formated_end_date'] = local_date($GLOBALS['_CFG']['date_format'], $row['end_time']);
        $res[$key]['small_time'] = $row['end_time'] - $now;
        $res[$key]['goods_name'] = $row['goods_name'];
        $res[$key]['goods_price'] = $row['goods_price'];
        $res[$key]['moq'] = $row['moq'];
        $res[$key]['volume_number'] = $row['volume_number'];
        $res[$key]['volume_price'] = $row['volume_price'];
		$res[$key]['goods_unit'] = $row['goods_unit'];
        $res[$key]['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $res[$key]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
        $res[$key]['url'] = build_uri('wholesale_goods', array('aid' => $row['act_id']), $row['goods_name']);
    }

    return $res;
}

?>
