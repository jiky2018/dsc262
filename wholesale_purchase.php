<?php



define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

require(ROOT_PATH . '/includes/lib_area.php');  
require(ROOT_PATH . '/includes/lib_wholesale.php');

if($GLOBALS['_CFG']['wholesale_user_rank'] == 0){
    $is_seller = get_is_seller();
    if($is_seller == 0){
        ecs_header("Location: " .$ecs->url(). "\n");
    }
}

$action = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'list';
$smarty->assign('action', $action);


if ($action == 'list') {
    $page_title = '求购单';
    
    $is_finished = isset($_REQUEST['is_finished']) ? intval($_REQUEST['is_finished']) : -1;
    $keyword = isset($_REQUEST['keyword']) ? htmlspecialchars(stripcslashes($_REQUEST['keyword'])) : '';
    $filter_array = array();
    $filter_array['review_status'] = 1;
    $query_array = array();
    $query_array['act'] = 'list';
    if ($is_finished != -1) {
        $query_array['is_finished'] = $is_finished;
        $filter_array['is_finished'] = $is_finished;
    }
    if($keyword){
        $filter_array['keyword'] = $keyword;
        $query_array['keyword'] = $keyword;
    }
    
    $size = 6;
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $purchase_list = get_purchase_list($filter_array, $size, $page);
    $pager = get_pager('wholesale_purchase.php', $query_array, $purchase_list['record_count'], $page, $size);
    $smarty->assign('pager', $pager);
    $smarty->assign('purchase_list', $purchase_list['purchase_list']);
    $smarty->assign('is_finished', $is_finished);
	
	$get_wholsale_navigator = get_wholsale_navigator();
	$smarty->assign('get_wholsale_navigator', $get_wholsale_navigator);
	
    
    $today_start = local_strtotime(local_date('Y-m-d'), gmtime());
    $today_end = $today_start + 86400;
    $sql = " SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('wholesale_purchase') . " WHERE add_time BETWEEN $today_start AND $today_end ";
    $today_count = $GLOBALS['db']->getOne($sql);
    $smarty->assign('today_count', $today_count);
    
    $sql = " SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('wholesale_purchase') . " WHERE 1 AND status = 1 ";
    $deal_count = $GLOBALS['db']->getOne($sql);
    $smarty->assign('deal_count', $deal_count);
    $smarty->assign('buy', $action);
}


elseif ($action == 'info') {
    $page_title = '求购单详情';
    $purchase_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    if (empty($purchase_id)) {
        ecs_header("Location: ./\n");
        exit;
    }
    $purchase_info = get_purchase_info($purchase_id);
    $smarty->assign('purchase_info', $purchase_info);
    
    $smarty->assign('is_merchant', check_user_is_merchant($_SESSION['user_id']));
}


elseif ($action == 'release') {
    $page_title = '发布求购单';	
    if (empty($_SESSION['user_id']) || !check_user_is_merchant($_SESSION['user_id'])) {
        show_message('您不是商家无法发布求购单', '前去入驻', 'merchants.php', 'info');
    }
	
	$get_wholsale_navigator = get_wholsale_navigator();
	$smarty->assign('get_wholsale_navigator', $get_wholsale_navigator);
	
    $smarty->assign('country_list', get_regions());
    $smarty->assign('province_list', get_regions(1, 1));
}


elseif ($action == 'do_release') {
    
    $data = array();
    $data['user_id'] = $_SESSION['user_id'];
    $data['subject'] = empty($_REQUEST['subject']) ? '' : trim($_REQUEST['subject']);
    $data['type'] = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);
    $data['contact_name'] = empty($_REQUEST['contact_name']) ? '' : trim($_REQUEST['contact_name']);
    $data['contact_gender'] = empty($_REQUEST['contact_gender']) ? '' : trim($_REQUEST['contact_gender']);
    $data['contact_phone'] = empty($_REQUEST['contact_phone']) ? '' : trim($_REQUEST['contact_phone']);
    $data['contact_email'] = empty($_REQUEST['contact_email']) ? '' : trim($_REQUEST['contact_email']);
    $data['add_time'] = gmtime();
    $data['end_time'] = empty($_REQUEST['end_time']) ? gmtime() : strtotime($_REQUEST['end_time']);
    $data['need_invoice'] = empty($_REQUEST['need_invoice']) ? 0 : intval($_REQUEST['need_invoice']);
    $data['invoice_tax_rate'] = empty($_REQUEST['invoice_tax_rate']) ? '' : trim($_REQUEST['invoice_tax_rate']);
    $data['consignee_address'] = empty($_REQUEST['consignee_address']) ? '' : trim($_REQUEST['consignee_address']);
    $data['description'] = empty($_REQUEST['description']) ? '' : trim($_REQUEST['description']);
    
    $consignee_region = 0;
    if (!empty($_REQUEST['district'])) {
        $consignee_region = intval($_REQUEST['district']);
    } elseif (!empty($_REQUEST['city'])) {
        $consignee_region = intval($_REQUEST['city']);
    } elseif (!empty($_REQUEST['province'])) {
        $consignee_region = intval($_REQUEST['province']);
    } elseif (!empty($_REQUEST['country'])) {
        $consignee_region = intval($_REQUEST['country']);
    }

    $data['consignee_region'] = $consignee_region;

    
    if ($db->autoExecute($ecs->table('wholesale_purchase'), $data, 'INSERT')) {
        $purchase_id = $db->insert_id();
        
        for ($i = 0; $i < count($_REQUEST['goods_name']); $i++) {
            $row = array();
            $row['purchase_id'] = $purchase_id;
            $row['goods_name'] = empty($_REQUEST['goods_name'][$i]) ? '' : trim($_REQUEST['goods_name'][$i]);
            $row['cat_id'] = empty($_REQUEST['cat_id'][$i]) ? 0 : intval($_REQUEST['cat_id'][$i]);
            $row['goods_number'] = empty($_REQUEST['goods_number'][$i]) ? 0 : intval($_REQUEST['goods_number'][$i]);
            $row['goods_price'] = empty($_REQUEST['goods_price'][$i]) ? 0 : floatval($_REQUEST['goods_price'][$i]);
            $row['remarks'] = empty($_REQUEST['remarks'][$i]) ? '' : trim($_REQUEST['remarks'][$i]);
            
            if (!empty($_REQUEST['pictures'][$i])) {
                $files = trim($_REQUEST['pictures'][$i]);
                $goods_img = move_temporary_files($files, 'data/purchase');
                $row['goods_img'] = serialize($goods_img);
            }
            $db->autoExecute($ecs->table('wholesale_purchase_goods'), $row, 'INSERT');
        }
        show_message('求购单发布成功', '返回首页', 'wholesale_purchase.php', 'info');
    } else {
        show_message('求购单发布失败', '返回上页', 'javascript:history.go(-1);', 'info');
    }
}


elseif ($action == 'upload_pic') {
    include_once(ROOT_PATH . '/includes/cls_image.php');
    $image = new cls_image($_CFG['bgcolor']);
    $result = array('error' => 0, 'message' => '', 'id' => '', 'path' => '');
    $type = "purchase"; 
    if ($_FILES['file']['tmp_name'] != '' && $_FILES['file']['tmp_name'] != 'none') {
        $dir = "temporary_files/$type";
        $path = $image->upload_image($_FILES['file'], $dir);
        
        $data = array();
        $data['type'] = $type;
        $data['path'] = $path;
        $data['add_time'] = gmtime();
        $data['identity'] = 0; 
        $data['user_id'] = $_SESSION['user_id']; 
        $db->autoExecute($ecs->table('temporary_files'), $data, 'INSERT');
        
        $result['id'] = $db->insert_id();
        $result['path'] = $path;
    } else {
        $result['error'] = '1';
        $result['message'] = "上传失败，请检查服务器配置";
    }
    die(json_encode($result));
}

if(defined('THEME_EXTENSION')){
	$wholesale_cat = get_wholesale_child_cat();
	$smarty->assign('wholesale_cat', $wholesale_cat);
}


assign_template();
$position = assign_ur_here(0, $page_title);
$smarty->assign('page_title', $position['title']);    
$smarty->assign('ur_here',    $position['ur_here']);  

$smarty->assign('categories', get_categories_tree()); 
$smarty->assign('helps',      get_shop_help());       

$smarty->display('wholesale_purchase.dwt');

?>