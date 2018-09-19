<?php


define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

require(ROOT_PATH . '/includes/lib_area.php');  
require(ROOT_PATH . 'includes/lib_wholesale.php');

if($GLOBALS['_CFG']['wholesale_user_rank'] == 0){
    $is_seller = get_is_seller();
    if($is_seller == 0){
        ecs_header("Location: " .$ecs->url(). "\n");
    }
}

$page = !empty($_REQUEST['page']) && intval($_REQUEST['page']) > 0 ? intval($_REQUEST['page']) : 1;
$size = !empty($_CFG['page_size']) && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;
$sort = (isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array('sort_order'))) ? trim($_REQUEST['sort']) : $default_sort_order_type;
$order = (isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC'))) ? trim($_REQUEST['order']) : $default_sort_order_method;




if (empty($_REQUEST['act'])) {
    $_REQUEST['act'] = 'list';
}



if ($_REQUEST['act'] == 'list') { 
    $cat_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);

    if ($cat_id) {
        $sql = " SELECT cat_name FROM " . $ecs->table('wholesale_cat') . " WHERE cat_id = '$cat_id' ";
        $smarty->assign('cat_name', $db->getOne($sql));
    }

    if (defined('THEME_EXTENSION')) {
        $wholesale_cat = get_wholesale_child_cat();
        $smarty->assign('wholesale_cat', $wholesale_cat);
    }
    $position = assign_ur_here();
    $goods_list = get_wholesale_list($cat_id, $size, $page, $sort);
    $children = get_children($cat_id, 3, 0, 'wholesale_cat');
    $count = get_wholesale_cat_goodsCount($children, $cat_id);

    $get_wholsale_navigator = get_wholsale_navigator();
    $smarty->assign('get_wholsale_navigator', $get_wholsale_navigator);

    $smarty->assign('goods_list', $goods_list);
    $smarty->assign('page_title', $position['title']);    
    $smarty->assign('ur_here', $position['ur_here']);  
    $smarty->assign('helps', get_shop_help());       
    assign_cat_pager('wholesale_cat', $cat_id, $count, $size, $sort, $order, $page);
    assign_template('wholesale');
    
    $smarty->display('wholesale_cat.dwt');
}


function get_wholesale_list($cat_id, $size, $page, $sort, $order) {
    $list = array();
    $where = " WHERE 1 ";
    $table = 'wholesale_cat';
    $type = 4;
    $children = get_children($cat_id, $type, 0, $table);
    if ($cat_id) {
        $where .= " AND ($children OR " . get_wholesale_extension_goods($children) . ") ";
    }

    $sql = "SELECT w.*, g.goods_thumb, g.user_id,g.goods_name as goods_name, g.shop_price, market_price, MIN(wvp.volume_number) AS volume_number, MAX(wvp.volume_price) AS volume_price " .
            "FROM " . $GLOBALS['ecs']->table('wholesale') . " AS w, " .
            $GLOBALS['ecs']->table('goods') . " AS g "
            . " LEFT JOIN " . $GLOBALS['ecs']->table('wholesale_volume_price') . " AS wvp ON wvp.goods_id = g.goods_id "
            . $where
            . " AND w.goods_id = g.goods_id AND w.enabled = 1 AND w.review_status = 3 GROUP BY goods_id ";
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

    while ($row = $GLOBALS['db']->fetchRow($res)) {
        $row['goods_thumb'] = get_image_path(0, $row['goods_thumb']); 

        
        $shop_information = get_shop_name($row['user_id']); 
        $row['is_IM'] = $shop_information['is_IM']; 
        
        if ($row['user_id'] == 0) {
            
            if ($GLOBALS['db']->getOne("SELECT kf_im_switch FROM " . $GLOBALS['ecs']->table('seller_shopinfo') . "WHERE ru_id = 0", true)) {
                $row['is_dsc'] = true;
            } else {
                $row['is_dsc'] = false;
            }
        } else {
            $row['is_dsc'] = false;
        }
        

        $row['goods_url'] = build_uri('wholesale_goods', array('aid' => $row['act_id']), $row['goods_name']);
        $properties = get_goods_properties($row['goods_id']);
        $row['goods_attr'] = $properties['pro'];
        $row['goods_sale'] = get_sale($row['goods_id']);
        $row['goods_extend'] = get_wholesale_extend($row['goods_id']); 
        $row['goods_price'] = $row['goods_price'];
        $row['moq'] = $row['moq'];
        $row['volume_number'] = $row['volume_number'];
        $row['volume_price'] = $row['volume_price'];
        $row['rz_shopName'] = get_shop_name($row['user_id'], 1); 
        $build_uri = array(
            'urid' 		=> $row['user_id'],
            'append' 	=> $row['rz_shopName']
        );

        $domain_url = get_seller_domain_url($row['user_id'], $build_uri);
        $row['store_url'] = $domain_url['domain_name'];
        $row['shop_price'] = price_format($row['shop_price']);
        $row['market_price'] = price_format($row['market_price']);
        $list[] = $row;
    }
    return $list;
}
































































function assign_cat_pager($app, $cat, $record_count, $size, $sort, $order, $page = 1) {
    $sch = array('sort' => $sort,
        'order' => $order,
        'cat' => $cat,
    );

    $page = intval($page);
    if ($page < 1) {
        $page = 1;
    }

    $page_count = $record_count > 0 ? intval(ceil($record_count / $size)) : 1;

    $pager['page'] = $page;
    $pager['size'] = $size;
    $pager['sort'] = $sort;
    $pager['order'] = $order;
    $pager['record_count'] = $record_count;
    $pager['page_count'] = $page_count;

    switch ($app) {
        case 'wholesale_cat':
            $uri_args = array('act' => 'list', 'cid' => $cat, 'sort' => $sort, 'order' => $order);
            break;
    }

    $page_prev = ($page > 1) ? $page - 1 : 1;
    $page_next = ($page < $page_count) ? $page + 1 : $page_count;

    $_pagenum = 10;     
    $_offset = 2;       
    $_from = $_to = 0;  
    if ($_pagenum > $page_count) {
        $_from = 1;
        $_to = $page_count;
    } else {
        $_from = $page - $_offset;
        $_to = $_from + $_pagenum - 1;
        if ($_from < 1) {
            $_to = $page + 1 - $_from;
            $_from = 1;
            if ($_to - $_from < $_pagenum) {
                $_to = $_pagenum;
            }
        } elseif ($_to > $page_count) {
            $_from = $page_count - $_pagenum + 1;
            $_to = $page_count;
        }
    }
    if (!empty($url_format)) {
        $pager['page_first'] = ($page - $_offset > 1 && $_pagenum < $page_count) ? $url_format . 1 : '';
        $pager['page_prev'] = ($page > 1) ? $url_format . $page_prev : '';
        $pager['page_next'] = ($page < $page_count) ? $url_format . $page_next : '';
        $pager['page_last'] = ($_to < $page_count) ? $url_format . $page_count : '';
        $pager['page_kbd'] = ($_pagenum < $page_count) ? true : false;
        $pager['page_number'] = array();
        for ($i = $_from; $i <= $_to;  ++$i) {
            $pager['page_number'][$i] = $url_format . $i;
        }
    } else {
        $pager['page_first'] = ($page - $_offset > 1 && $_pagenum < $page_count) ? build_uri($app, $uri_args, '', 1, $keywords) : '';
        $pager['page_prev'] = ($page > 1) ? build_uri($app, $uri_args, '', $page_prev, $keywords) : '';
        $pager['page_next'] = ($page < $page_count) ? build_uri($app, $uri_args, '', $page_next, $keywords) : '';
        $pager['page_last'] = ($_to < $page_count) ? build_uri($app, $uri_args, '', $page_count, $keywords) : '';
        $pager['page_kbd'] = ($_pagenum < $page_count) ? true : false;
        $pager['page_number'] = array();
        for ($i = $_from; $i <= $_to;  ++$i) {
            $pager['page_number'][$i] = build_uri($app, $uri_args, '', $i, $keywords);
        }
    }
    $GLOBALS['smarty']->assign('pager', $pager);
}

function get_wholesale_cat_goodsCount($children, $cat_id, $ext = '') {
	
    $where = " wc.is_show = 1 AND $children AND w.review_status = 3 ";
    
        
    
    $leftJoin = '';
    $leftJoin .= " LEFT JOIN " . $GLOBALS['ecs']->table('wholesale_cat') . " as wc on w.wholesale_cat_id = wc.cat_id ";
    $leftJoin .= " LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " as g on g.goods_id = w.goods_id ";
    return $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('wholesale') . " AS w " . $leftJoin . " WHERE $where $ext");
}

?>
