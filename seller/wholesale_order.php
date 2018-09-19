<?php



define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'includes/lib_goods.php');
require_once(ROOT_PATH . 'includes/lib_wholesale.php');
require_once(ROOT_PATH .SELLER_PATH. '/includes/lib_comment.php');

$smarty->assign('menus',$_SESSION['menus']);
$smarty->assign('action_type',"wholesale_order");
$user_action_list = get_user_action_list($_SESSION['seller_id']);




if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}


$adminru = get_admin_ru_id();
$ruCat = '';
if($adminru['ru_id'] == 0){
        $smarty->assign('priv_ru',   1);
}else{
        $smarty->assign('priv_ru',   0);
}

$smarty->assign('primary_cat',     $_LANG['19_supply_and_demand']);
include_once(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/wholesale_order.php');
include_once(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/wholesale_purchase.php');
$smarty->assign('lang', $_LANG);






if ($_REQUEST['act'] == 'list')
{
    admin_priv('wholesale_order');
    $smarty->assign('ur_here',     $_LANG['02_wholesale_order']);
	$smarty->assign('primary_cat', $_LANG['supply_and_demand']);
	$smarty->assign('menu_select', array('action' => 'supply_and_demand', 'current' => '02_wholesale_order'));
    $smarty->assign('full_page',  1);
    $smarty->assign('status_list', $_LANG['qs']);   
	
    $list = wholesale_order_list();

    
	
    $page_count_arr=array();
    $page_count_arr=seller_page($list,$_REQUEST['page']);
	
	$smarty->assign('order_list',     $list['orders']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
	$smarty->assign('page_count_arr',   $page_count_arr);
	
    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('wholesale_store_order.dwt');
}


elseif($_REQUEST['act'] == 'order_export'){
    
    setlocale(LC_ALL, 'en_US.UTF-8');
    $filename = date('YmdHis') . ".csv";
    header("Content-type:text/csv");
    header("Content-Disposition:attachment;filename=" . $filename);
    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
    header('Expires:0');
    header('Pragma:public');
    
    $order_list = wholesale_order_list();

    echo download_orderlist($order_list['orders']);
    exit;
}








 elseif ($_REQUEST['act'] == 'query') 
{
	admin_priv('wholesale_order');
    $list = wholesale_order_list();

    
    
    $priv_str = $db->getOne("SELECT action_list FROM " . $ecs->table('admin_user') . " WHERE user_id = '" . $_SESSION['admin_id'] . "'");

    
    if ($priv_str == 'all') {
        $smarty->assign('priv_str', $priv_str);
    } else {
        $smarty->assign('priv_str', $priv_str);
    }

    
    $composite_status = isset($_REQUEST['composite_status']) ? trim($_REQUEST['composite_status']) : -1;
    $smarty->assign('status', $composite_status);
    
    

	$page_count_arr=array();
    $page_count_arr=seller_page($list,$_REQUEST['page']);
	
	$smarty->assign('order_list',     $list['orders']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
	$smarty->assign('page_count_arr',   $page_count_arr);
    $sort_flag = sort_flag($list['filter']);
    

    make_json_result($smarty->fetch('wholesale_store_order.dwt'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}





elseif ($_REQUEST['act'] == 'info')
{

    
    if (isset($_REQUEST['order_id']))
    {
        $order_id = intval($_REQUEST['order_id']);
        $order = wholesale_order_info($order_id);
    }
    elseif (isset($_REQUEST['order_sn']))
    {
        $order_sn = trim($_REQUEST['order_sn']);
        $order = wholesale_order_info(0, $order_sn);
    }
    else
    {
        
        die('invalid parameter');
    }

	

    
    if (empty($order))
    {
        die('order does not exist');
    }

    
    if ($order['user_id'] > 0)
    {
        $user = user_info($order['user_id']);
        if (!empty($user))
        {
            $order['user_name'] = $user['user_name'];
        }
    }
	

    
    if ($order['order_amount'] < 0)
    {
        $order['money_refund']          = abs($order['order_amount']);
        $order['formated_money_refund'] = price_format(abs($order['order_amount']));
    }

    
    $order['order_time']    = local_date($_CFG['time_format'], $order['add_time']);
    $order['status']        = $_LANG['os'][$order['order_status']];

    
    $weight_price = order_weight_price($order['order_id']);
    $order['total_weight'] = $weight_price['formated_weight'];
    
    $date = array('order_id');
        
	$order_child = count(get_table_date('wholesale_order_info', "main_order_id='".$order['order_id']."'", $date, 1));
	$order['order_child'] = $order_child;
	
    
    $smarty->assign('order', $order);
      
    if ($order['user_id'] > 0)
    {
        
        if ($user['user_rank'] > 0)
        {
            $where = " WHERE rank_id = '$user[user_rank]' ";
        }
        else
        {
            $where = " WHERE min_points <= " . intval($user['rank_points']) . " ORDER BY min_points DESC ";
        }
        $sql = "SELECT rank_name FROM " . $ecs->table('user_rank') . $where;
        $user['rank_name'] = $db->getOne($sql);

        
        $sql = "SELECT * FROM " . $ecs->table('user_address') . " WHERE user_id = '$order[user_id]'";
        $smarty->assign('address_list', $db->getAll($sql));
    }

    
    $goods_list = array();
    $goods_attr = array();
    $sql = " SELECT o.*, c.measure_unit, w.goods_number AS storage, g.model_inventory, g.model_attr as model_attr, o.goods_attr, g.suppliers_id, p.product_sn,
            g.user_id AS ru_id, g.brand_id, g.goods_thumb , g.bar_code 
            FROM " . $ecs->table('wholesale_order_goods') . " AS o
			LEFT JOIN " . $ecs->table('wholesale_products') . " AS p ON p.product_id = o.product_id
			LEFT JOIN " . $ecs->table('goods') . " AS g ON o.goods_id = g.goods_id
			LEFT JOIN " . $ecs->table('category') . " AS c ON g.cat_id = c.cat_id
			LEFT JOIN " . $ecs->table('wholesale') . " AS w ON w.goods_id = g.goods_id 
            WHERE o.order_id = '$order[order_id]' ";
    $res = $db->query($sql);

    while ($row = $db->fetchRow($res))
    {
      
        $_goods_thumb = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $row['goods_thumb'] = $_goods_thumb;		
        
        $row['formated_subtotal']       = price_format($row['goods_price'] * $row['goods_number']);
        $row['formated_goods_price']    = price_format($row['goods_price']);
		

        $goods_attr[] = explode(' ', trim($row['goods_attr'])); 

        $goods_list[] = $row;
    }
	
    $attr = array();
    $arr  = array();
    foreach ($goods_attr AS $index => $array_val)
    {
        foreach ($array_val AS $value)
        {
            $arr = explode(':', $value);
            $attr[$index][] =  @array('name' => $arr[0], 'value' => $arr[1]);
        }
    }
    $smarty->assign('goods_attr', $attr);
    $smarty->assign('goods_list', $goods_list);

	
    $sql = "SELECT log_time  FROM " . $ecs->table('order_action') . " WHERE order_id = '$order[order_id]' ";
    $res_time = local_date($_CFG['time_format'], $db->getOne($sql));
    $smarty->assign('res_time', $res_time);
    

    
    if (isset($_GET['print']))
    {   
        $smarty->assign('shop_name',    $store['shop_name']);
        $smarty->assign('shop_url',     $ecs->url());
        $smarty->assign('shop_address', $store['shop_address']);
        $smarty->assign('service_phone',$store['kf_tel']);
        $smarty->assign('print_time',   local_date($_CFG['time_format']));
        $smarty->assign('action_user',  $_SESSION['admin_name']);

        $smarty->template_dir = '../' . DATA_DIR;
        $smarty->display('order_print.html');
    }
    
    elseif (isset($_GET['shipping_print']))
    {
        
        $region_array = array();
		$region = $db->getAll("SELECT region_id, region_name FROM " . $ecs->table("region")); 
        if (!empty($region))
        {
            foreach($region as $region_data)
            {
                $region_array[$region_data['region_id']] = $region_data['region_name'];
            }
        }
        $smarty->assign('shop_name',    $store['shop_name']);
        $smarty->assign('order_id',    $order_id);
        $smarty->assign('province', $region_array[$store['province']]);
        $smarty->assign('city', $region_array[$store['city']]);
        $smarty->assign('district', $region_array[$store['district']]);
        $smarty->assign('shop_address', $store['shop_address']);
        $smarty->assign('service_phone',$store['kf_tel']);
        $shipping = $db->getRow("SELECT * FROM " . $ecs->table("shipping_tpl") . " WHERE shipping_id = '" . $order['shipping_id']."' and ru_id='".$order['ru_id']."'");
        
        if ($shipping['print_model'] == 2)
        {
            
            
            $shipping['print_bg'] = empty($shipping['print_bg']) ? '' : get_site_root_url() . $shipping['print_bg'];

            
            if (!empty($shipping['print_bg']))
            {
                $_size = @getimagesize($shipping['print_bg']);

                if ($_size != false)
                {
                    $shipping['print_bg_size'] = array('width' => $_size[0], 'height' => $_size[1]);
                }
            }

            if (empty($shipping['print_bg_size']))
            {
                $shipping['print_bg_size'] = array('width' => '1024', 'height' => '600');
            }

            
            $lable_box = array();
            $lable_box['t_shop_country'] = $region_array[$store['country']]; 
            $lable_box['t_shop_city'] = $region_array[$store['city']]; 
            $lable_box['t_shop_province'] = $region_array[$store['province']]; 
			$sql = "select og.ru_id from " .$GLOBALS['ecs']->table('order_info') ." as oi ". "," .$GLOBALS['ecs']->table('order_goods'). " as og " .
					" where oi.order_id = og.order_id and oi.order_id = '" .$order['order_id']. "' group by oi.order_id";
			$ru_id = $GLOBALS['db']->getOne($sql);
			
			if($ru_id > 0){
				
				$sql = "select shoprz_brandName, shopNameSuffix from " .$GLOBALS['ecs']->table('merchants_shop_information') . " where user_id = '$ru_id'";
				$shop_info = $GLOBALS['db']->getRow($sql);
				
				$lable_box['t_shop_name'] = $shop_info['shoprz_brandName'] . $shop_info['shopNameSuffix']; 
			}else{
				$lable_box['t_shop_name'] = $_CFG['shop_name']; 
			}
			
            $lable_box['t_shop_district'] = $region_array[$store['district']]; 
            $lable_box['t_shop_tel'] = $store['kf_tel']; 
            $lable_box['t_shop_address'] = $store['shop_address']; 
            $lable_box['t_customer_country'] = $region_array[$order['country']]; 
            $lable_box['t_customer_province'] = $region_array[$order['province']]; 
            $lable_box['t_customer_city'] = $region_array[$order['city']]; 
            $lable_box['t_customer_district'] = $region_array[$order['district']]; 
            $lable_box['t_customer_tel'] = $order['tel']; 
            $lable_box['t_customer_mobel'] = $order['mobile']; 
            $lable_box['t_customer_post'] = $order['zipcode']; 
            $lable_box['t_customer_address'] = $order['address']; 
            $lable_box['t_customer_name'] = $order['consignee']; 

            $gmtime_utc_temp = gmtime(); 
            $lable_box['t_year'] = date('Y', $gmtime_utc_temp); 
            $lable_box['t_months'] = date('m', $gmtime_utc_temp); 
            $lable_box['t_day'] = date('d', $gmtime_utc_temp); 

            $lable_box['t_order_no'] = $order['order_sn']; 
            $lable_box['t_order_postscript'] = $order['postscript']; 
            $lable_box['t_order_best_time'] = $order['best_time']; 
            $lable_box['t_pigeon'] = '√'; 
            $lable_box['t_custom_content'] = ''; 

            
            $temp_config_lable = explode('||,||', $shipping['config_lable']);
            if (!is_array($temp_config_lable))
            {
                $temp_config_lable[] = $shipping['config_lable'];
            }
            foreach ($temp_config_lable as $temp_key => $temp_lable)
            {
                $temp_info = explode(',', $temp_lable);
                if (is_array($temp_info))
                {
                    $temp_info[1] = $lable_box[$temp_info[0]];
                }
                $temp_config_lable[$temp_key] = implode(',', $temp_info);
            }
            $shipping['config_lable'] = implode('||,||',  $temp_config_lable);

            $smarty->assign('shipping', $shipping);

            $smarty->display('print.dwt');
        }
        elseif (!empty($shipping['shipping_print']))
        {
            
            echo $smarty->fetch("str:" . $shipping['shipping_print']);
        }
        else
        {
            $shipping_code = $db->getOne("SELECT shipping_code FROM " . $ecs->table('shipping') . " WHERE shipping_id='" . $order['shipping_id']."'");		
			
            if ($shipping_code)
            {
                include_once(ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php');
            }

            if (!empty($_LANG['shipping_print']))
            {
                echo $smarty->fetch("str:$_LANG[shipping_print]");
            }
            else
            {
                echo $_LANG['no_print_shipping'];
            }
        }
    }
    else
    {
        
		$smarty->assign('primary_cat', $_LANG['supply_and_demand']);
		$smarty->assign('menu_select', array('action' => 'supply_and_demand', 'current' => '02_wholesale_order'));
        $smarty->assign('ur_here', $_LANG['order_info']);
        $smarty->assign('action_link', array('href' => 'wholesale_order.php?act=list&' . list_link_postfix(), 'text' => $_LANG['02_order_list']));
        
        
        assign_query_info();
        $smarty->display('wholesale_store_order_info.dwt');
    }
}




elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('wholesale_purchase');

    $id = intval($_GET['id']);

    $exc->drop($id);
	
	
	$goods_list = get_table_date('wholesale_purchase_goods', "purchase_id='$id'", array('goods_id', 'goods_img'), 1);
	foreach($goods_list as $key=>$val){
		if(!empty($val['goods_img'])){
			$goods_img = unserialize($val['goods_img']);
			foreach($goods_img as $k=>$v){
				@unlink(ROOT_PATH . $v);
			}
		}
		$exc_goods->drop($val['goods_id']);
	}

    $url = 'wholesale_purchase.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}


elseif ($_REQUEST['act'] == 'operate')
{
    
    admin_priv('order_os_edit');
	
    $smarty->assign('menu_select',array('action' => 'supply_and_demand', 'current' => '02_wholesale_order'));
    
    $order_id = isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : 0;

	$order_id_list = explode(',', $order_id);
    
    $batch = isset($_REQUEST['batch']); 
    $action_note = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';
    
    if (isset($_POST['remove']))
    {
		
        $require_note = false;
        $operation = 'remove';

		if ($batch)
		{
			
			$order = wholesale_order_info($order_id);
			
			$type = 1;  
			$sql = "UPDATE " . $GLOBALS['ecs']->table('wholesale_order_info') . " SET is_delete = '$type'" . " WHERE order_sn " . db_create_in($order_id_list);
			$GLOBALS['db']->query($sql);
			
			admin_log($order['order_sn'], 'remove', 'wholesale_order');

			
			sys_msg($_LANG['order_removed'], 0, array(array('href'=>'wholesale_order.php?act=list&' . list_link_postfix(), 'text' => $_LANG['return_list'])));
		}

        
    }
  
    
    elseif (isset($_POST['print']))
    {
        if (empty($_POST['order_id']))
        {
            sys_msg($_LANG['pls_select_order']);
        }
		
		
        $url = 'tp_api.php?act=order_print&order_sn='.$_POST['order_id'].'&order_type=wholesale_order';
        ecs_header("Location: $url\n");
        exit;
		
        
        
        $smarty->assign('print_time',   local_date($_CFG['time_format']));
        $smarty->assign('action_user',  $_SESSION['seller_name']);
        
        $html = '';
        $order_sn_list = explode(',', $_POST['order_id']);
        foreach ($order_sn_list as $order_sn)
        {
            
            $order = wholesale_order_info(0, $order_sn);
			
            if (empty($order))
            {
                continue;
            }

            $user_id = !empty($order['user_id'])?$order['user_id']:0;

            
            if ($user_id > 0)
            {
                $user = user_info($order['user_id']);
                if (!empty($user))
                {
                    $order['user_name'] = $user['user_name'];
                }
            }
            
            
            
			$add_time = !empty($order['add_time']) ? $order['add_time'] : 0;
            $order['order_time']    = local_date($_CFG['time_format'], $add_time);
			$order_status = !empty($order['order_status']) ? $order['order_status'] : 0;
            $order['status']        = $_LANG['os'][$order_status];
            

           

            
            $smarty->assign('order', $order);

            
			$order_id = !empty($order['order_id']) ? $order['order_id'] : 0;
			
			$goods_list = array();
			$goods_attr = array();
			$sql = "SELECT o.*, g.goods_thumb, g.goods_sn, g.brand_id, g.user_id AS ru_id, w.goods_number AS storage, w.act_id, g.model_inventory, o.goods_attr, oi.order_sn " .
					"FROM " . $ecs->table('wholesale_order_goods') . " AS o ".
					"LEFT JOIN " . $ecs->table('goods') . " AS g ON o.goods_id = g.goods_id " .
					"LEFT JOIN " . $ecs->table('wholesale') . " AS w ON w.goods_id = g.goods_id " .
					"LEFT JOIN " . $ecs->table('wholesale_order_info') . " AS oi ON oi.order_id = o.order_id " .
					"WHERE o.order_id = '{$order_id}' ";
			$res = $GLOBALS['db']->query($sql);
			
			while ($row = $GLOBALS['db']->fetchRow($res))
			{
				
				
				if(empty($prod)){ 
					$row['goods_storage'] = $row['storage']; 
				}	
				$row['storage'] = !empty($row['goods_storage']) ? $row['goods_storage'] : 0;    	
				$row['formated_subtotal']       = price_format($row['goods_price'] * $row['goods_number']);
				$row['formated_goods_price']    = price_format($row['goods_price']);
				$row['goods_id'] = $row['act_id']; 
				
				$row['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
				
				$goods_attr[] = explode(' ', trim($row['goods_attr'])); 
				$goods_list[] = $row;
			}

			$attr = array();
			$arr  = array();
			foreach ($goods_attr AS $index => $array_val)
			{
				foreach ($array_val AS $value)
				{
					$arr = explode(':', $value);
					$attr[$index][] =  @array('name' => $arr[0], 'value' => $arr[1]);
				}
			}
            
            
            $sql="select shop_name,country,province,city,shop_address,kf_tel from ".$ecs->table('seller_shopinfo')." where ru_id='".$order['ru_id']."'";
            $store=$db->getRow($sql);

            $store['shop_name'] = get_shop_name($order['ru_id'], 1);
            
            $sql="SELECT domain_name FROM ".$ecs->table("seller_domain")." WHERE ru_id = '".$order['ru_id']."' AND  is_enable = 1";
            $domain_name = $db->getOne($sql);
            $smarty->assign('domain_name',    $domain_name);
                    
            $smarty->assign('shop_name',    $store['shop_name']);
            $smarty->assign('shop_url',     $ecs->seller_url());
            $smarty->assign('shop_address', $store['shop_address']);
            $smarty->assign('service_phone',$store['kf_tel']);
            
            $smarty->assign('goods_attr', $attr);
			
            $smarty->assign('goods_list', $goods_list);
            $smarty->template_dir = '../' . DATA_DIR;
            $html .= $smarty->fetch('wholesale_order_print.html') .
                '<div style="PAGE-BREAK-AFTER:always"></div>';
        }
        
        echo $html;
        exit;
    }
    


    

	
    	
    
    

        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
    
    
    
        
        
            
            
                
                        
                
            

                
                
                        
                
            
            
        
            
            
                    
            
        
    
}






elseif ($_REQUEST['act'] == 'get_goods_info')
{
    
    $order_id = isset($_REQUEST['order_id'])?intval($_REQUEST['order_id']):0;
    if (empty($order_id))
    {
        make_json_response('', 1, $_LANG['error_get_goods_info']);
    }
    $goods_list = array();
    $goods_attr = array();
    $sql = "SELECT o.*, g.goods_thumb, g.goods_sn, g.brand_id, g.user_id AS ru_id, w.goods_number AS storage, g.model_inventory, o.goods_attr, oi.order_sn " .
            "FROM " . $ecs->table('wholesale_order_goods') . " AS o ".
            "LEFT JOIN " . $ecs->table('goods') . " AS g ON o.goods_id = g.goods_id " .
            "LEFT JOIN " . $ecs->table('wholesale') . " AS w ON w.goods_id = g.goods_id " .
            "LEFT JOIN " . $ecs->table('wholesale_order_info') . " AS oi ON oi.order_id = o.order_id " .
            "WHERE o.order_id = '{$order_id}' ";
    $res = $db->query($sql);
    
    while ($row = $db->fetchRow($res))
    {     
        if(empty($prod)){ 
            $row['goods_storage'] = $row['storage']; 
        }	
        $row['storage'] = !empty($row['goods_storage']) ? $row['goods_storage'] : 0;    	
        $row['formated_subtotal']       = price_format($row['goods_price'] * $row['goods_number']);
        $row['formated_goods_price']    = price_format($row['goods_price']);
        
        
        $row['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        
        $goods_attr[] = explode(' ', trim($row['goods_attr'])); 
        $goods_list[] = $row;
    }
    $attr = array();
    $arr  = array();
    foreach ($goods_attr AS $index => $array_val)
    {
        foreach ($array_val AS $value)
        {
            $arr = explode(':', $value);
            $attr[$index][] =  @array('name' => $arr[0], 'value' => $arr[1]);
        }
    }
    $smarty->assign('goods_attr', $attr);
    $smarty->assign('goods_list', $goods_list);
    $str = $smarty->fetch('show_order_goods.dwt');
    $goods[] = array('order_id' => $order_id, 'str' => $str);
    make_json_result($goods);
}elseif($_REQUEST['act'] == 'pay_order'){
    require(ROOT_PATH . '/includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => 0,'msg'=>'');
    
    $order_id = !empty($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;

    if($order_id > 0){
        $sql = "SELECT pay_status FROM".$ecs->table('wholesale_order_info')."WHERE order_id = '$order_id'";
        $pay_status = $db->getOne($sql);
        if($pay_status == 2){
             
            $result['error'] = 1;
            $result['msg'] = '不能重复付款！';
        }else{
            require(ROOT_PATH . '/includes/lib_payment.php');
            $sql = "SELECT log_id FROM".$ecs->table('pay_log')."WHERE order_id = '$order_id' AND order_type = '". PAY_WHOLESALE ."'";
            $log_id = $db->getOne($sql);
            order_paid($log_id,2);
        }
    }else{
         
        $result['error'] = 1;
        $result['msg'] = 'invalid parameter';
    }
    
    die($json->encode($result));
}


function wholesale_order_list()
{
    
    $adminru = get_admin_ru_id();
    $ruCat = '';
    $no_main_order = '';
    $noTime = gmtime();
    

    $result = get_filter();
    if ($result === false)
    {
        
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        $filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);
        $filter['address'] = empty($_REQUEST['address']) ? '' : trim($_REQUEST['address']);
        $filter['shipped_deal'] = empty($_REQUEST['shipped_deal']) ? '' : trim($_REQUEST['shipped_deal']);

        if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1)
        {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
            $filter['order_sn'] = json_str_iconv($filter['order_sn']);
            $filter['consignee'] = json_str_iconv($filter['consignee']);
            $filter['address'] = json_str_iconv($filter['address']);
        }
        
        $filter['email'] = empty($_REQUEST['email']) ? '' : trim($_REQUEST['email']);
        $filter['zipcode'] = empty($_REQUEST['zipcode']) ? '' : trim($_REQUEST['zipcode']);
        $filter['tel'] = empty($_REQUEST['tel']) ? '' : trim($_REQUEST['tel']);
        $filter['mobile'] = empty($_REQUEST['mobile']) ? 0 : trim($_REQUEST['mobile']);
        $filter['country'] = empty($_REQUEST['order_country']) ? 0 : intval($_REQUEST['order_country']);
        $filter['province'] = empty($_REQUEST['order_province']) ? 0 : intval($_REQUEST['order_province']);
        $filter['city'] = empty($_REQUEST['order_city']) ? 0 : intval($_REQUEST['order_city']);
        $filter['district'] = empty($_REQUEST['order_district']) ? 0 : intval($_REQUEST['order_district']);
        $filter['street'] = empty($_REQUEST['order_street']) ? 0 : intval($_REQUEST['order_street']);
        $filter['shipping_id'] = empty($_REQUEST['shipping_id']) ? 0 : intval($_REQUEST['shipping_id']);
        $filter['pay_id'] = empty($_REQUEST['pay_id']) ? 0 : intval($_REQUEST['pay_id']);
        $filter['order_status'] = isset($_REQUEST['order_status']) ? intval($_REQUEST['order_status']) : -1;
        $filter['shipping_status'] = isset($_REQUEST['shipping_status']) ? intval($_REQUEST['shipping_status']) : -1;
        $filter['pay_status'] = isset($_REQUEST['pay_status']) ? intval($_REQUEST['pay_status']) : -1;
        $filter['user_id'] = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
        $filter['user_name'] = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);
        $filter['composite_status'] = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : -1;
        $filter['group_buy_id'] = isset($_REQUEST['group_buy_id']) ? intval($_REQUEST['group_buy_id']) : 0;
        $filter['presale_id'] = isset($_REQUEST['presale_id']) ? intval($_REQUEST['presale_id']) : 0; 
        $filter['store_id'] = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0; 
		$filter['order_cat'] = isset($_REQUEST['order_cat']) ? trim($_REQUEST['order_cat']) : '';
        
        $filter['source'] = empty($_REQUEST['source']) ? '' : trim($_REQUEST['source']); 

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);

        
        $filter['start_take_time'] = empty($_REQUEST['start_take_time']) ? '' : (strpos($_REQUEST['start_take_time'], '-') > 0 ?  local_strtotime($_REQUEST['start_take_time']) : $_REQUEST['start_take_time']);
        $filter['end_take_time'] = empty($_REQUEST['end_take_time']) ? '' : (strpos($_REQUEST['end_take_time'], '-') > 0 ?  local_strtotime($_REQUEST['end_take_time']) : $_REQUEST['end_take_time']);

        
        $filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
        
        $where = ' WHERE 1 ';
        
        if($filter['keywords']){
            $where  .= " AND (o.order_sn LIKE '%" .$filter['keywords']. "%'";
            $where  .= " OR (iog.goods_name LIKE '%" .$filter['keywords']. "%' OR iog.goods_sn LIKE '%" .$filter['keywords']. "%'))";
        }
        
        if($adminru['ru_id'] > 0){
            $where .= " AND (SELECT og.ru_id FROM " . $GLOBALS['ecs']->table('wholesale_order_goods') .' as og' . " WHERE og.order_id = o.order_id LIMIT 1) = '" .$adminru['ru_id']. "' ";
        }

        if($filter['source'] == 'start' || $adminru['ru_id'] > 0 || $filter['keywords'])
        {
            $no_main_order = " and (select count(*) from " .$GLOBALS['ecs']->table('wholesale_order_info'). " as oi2 where oi2.main_order_id = o.order_id) = 0 ";  
        }
        if($filter['shipped_deal']){
            $where .= " AND o.shipping_status<>" . SS_RECEIVED;
        }
        $leftJoin = '';
        $store_search = -1;
        $store_where = '';
        $store_search_where = '';
        if($filter['store_search'] > -1){
           if($adminru['ru_id'] == 0){ 
                if($filter['store_search'] > 0){
                    if($_REQUEST['store_type']){
                        $store_search_where = "AND msi.shopNameSuffix = '" .$_REQUEST['store_type']. "'";
                    }

                    $no_main_order = " and (SELECT count(*) FROM " .$GLOBALS['ecs']->table('order_info'). " AS oi2 where oi2.main_order_id = o.order_id) = 0 ";  
                    if($filter['store_search'] == 1){
                        $where .= " AND (SELECT og.ru_id FROM " . $GLOBALS['ecs']->table('order_goods') .' AS og' . " WHERE og.order_id = o.order_id LIMIT 1) = '" .$filter['merchant_id']. "' ";
                    }elseif($filter['store_search'] == 2){
                        $store_where .= " AND msi.rz_shopName LIKE '%" . mysql_like_quote($filter['store_keyword']) . "%'";
                    }elseif($filter['store_search'] == 3){
                        $store_where .= " AND msi.shoprz_brandName LIKE '%" . mysql_like_quote($filter['store_keyword']) . "%' " . $store_search_where;
                    }

                    if($filter['store_search'] > 1){
                        $where .= " AND (SELECT og.ru_id FROM " . $GLOBALS['ecs']->table('order_goods') .' AS og, ' . 
                                   $GLOBALS['ecs']->table('merchants_shop_information') .' AS msi ' . 
                                  " WHERE og.order_id = o.order_id AND msi.user_id = og.ru_id $store_where LIMIT 1) > 0 ";
                    }
                }else{
                    $store_search = 0;
                }    
           }
        }
        
        
        
        if($filter['store_id'] > 0){
            $leftJoin .= " LEFT JOIN".$GLOBALS['ecs']->table('store_order')." AS sto ON sto.order_id = o.order_id";
            $where .= " AND sto.store_id  = '".$filter['store_id']."'";
        }
        
        if ($filter['order_sn'])
        {
            $where .= " AND o.order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'";
        }
        if ($filter['consignee'])
        {
            $where .= " AND o.consignee LIKE '%" . mysql_like_quote($filter['consignee']) . "%'";
        }
        if ($filter['email'])
        {
            $where .= " AND o.email LIKE '%" . mysql_like_quote($filter['email']) . "%'";
        }
        if ($filter['address'])
        {
            $where .= " AND o.address LIKE '%" . mysql_like_quote($filter['address']) . "%'";
        }
        if ($filter['zipcode'])
        {
            $where .= " AND o.zipcode LIKE '%" . mysql_like_quote($filter['zipcode']) . "%'";
        }
        if ($filter['tel'])
        {
            $where .= " AND o.tel LIKE '%" . mysql_like_quote($filter['tel']) . "%'";
        }
        if ($filter['mobile'])
        {
            $where .= " AND o.mobile LIKE '%" .mysql_like_quote($filter['mobile']) . "%'";
        }
        if ($filter['country'])
        {
            $where .= " AND o.country = '$filter[country]'";
        }
        if ($filter['province'])
        {
            $where .= " AND o.province = '$filter[province]'";
        }
        if ($filter['city'])
        {
            $where .= " AND o.city = '$filter[city]'";
        }
        if ($filter['district'])
        {
            $where .= " AND o.district = '$filter[district]'";
        }
        if ($filter['street'])
        {
            $where .= " AND o.street = '$filter[street]'";
        }
        if ($filter['shipping_id'])
        {
            $where .= " AND o.shipping_id  = '$filter[shipping_id]'";
        }
        if ($filter['pay_id'])
        {
            $where .= " AND o.pay_id  = '$filter[pay_id]'";
        }
        if ($filter['order_status'] != -1)
        {
            $where .= " AND o.order_status  = '$filter[order_status]'";
        }
        if ($filter['shipping_status'] != -1)
        {
            $where .= " AND o.shipping_status = '$filter[shipping_status]'";
        }
        if ($filter['pay_status'] != -1)
        {
            $where .= " AND o.pay_status = '$filter[pay_status]'";
        }
        if ($filter['user_id'])
        {
            $where .= " AND o.user_id = '$filter[user_id]'";
        }
        if ($filter['user_name'])
        {
            $where .= " AND (SELECT u.user_id FROM " .$GLOBALS['ecs']->table('users'). " AS u WHERE u.user_name LIKE '%" . mysql_like_quote($filter['user_name']) . "%' LIMIT 1) = o.user_id";
        }
        if ($filter['start_time'])
        {
            $where .= " AND o.add_time >= '$filter[start_time]'";
        }
        if ($filter['end_time'])
        {
            $where .= " AND o.add_time <= '$filter[end_time]'";
        }
		
		if($filter['composite_status'] == 0){
			$where .= " AND order_status = 0 ";
		}elseif($filter['composite_status'] == 1){
			$where .= " AND order_status = 1 ";
		}
		$where.= " AND o.is_delete = 0 ";

        
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
        {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        }
        elseif (isset($_COOKIE['ECSCP']['page_size']) && intval($_COOKIE['ECSCP']['page_size']) > 0)
        {
            $filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
        }
        else
        {
            $filter['page_size'] = 15;
        }
		
        $where_store = '';
        if(empty($filter['start_take_time']) || empty($filter['end_take_time']))
        {
            if($store_search == 0 && $adminru['ru_id'] == 0){
                $where_store = " AND (SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('wholesale_order_goods') ." AS og ". " WHERE o.order_id = og.order_id AND og.ru_id = 0 LIMIT 1) > 0 ".
                               " AND (SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('wholesale_order_info'). " AS oi2 WHERE oi2.main_order_id = o.order_id) = 0";
            }
        }

            
        if(!empty($filter['start_take_time']) || !empty($filter['end_take_time']))
        {
            $sql = "SELECT o.order_id FROM " . $GLOBALS['ecs']->table('wholesale_order_info') . " AS o " .
                    $leftJoin .
                    "LEFT JOIN " . $GLOBALS['ecs']->table('wholesale_order_action') . " AS oa ON o.order_id = oa.order_id " .
                    $where . $where_store . $no_main_order . " GROUP BY o.order_id";

            $record_count = count($GLOBALS['db']->getAll($sql));
        }
        elseif(!empty($filter['keywords']))
        {
            $leftJoin .= " LEFT JOIN " . $GLOBALS['ecs']->table('wholesale_order_goods') . " AS iog ON iog.order_id = o.order_id ";

            $sql = "SELECT o.order_id FROM " . $GLOBALS['ecs']->table('wholesale_order_info') . " AS o " .
                    $leftJoin .
                    "LEFT JOIN " . $GLOBALS['ecs']->table('wholesale_order_action') . " AS oa ON o.order_id = oa.order_id " .
                    $where . $where_store . $no_main_order . " GROUP BY o.order_id";

            $record_count = count($GLOBALS['db']->getAll($sql));
        }
        else
        {
            $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('wholesale_order_info') . " AS o ".$leftJoin. $where .$where_store. $no_main_order;
            $record_count = $GLOBALS['db']->getOne($sql);
        }
		
        $filter['record_count']   = $record_count;
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;
        
        $where_store = '';
        if(empty($filter['start_take_time']) || empty($filter['end_take_time']))
        {
            if($store_search == 0 && $adminru['ru_id'] == 0){
                $where .=  " AND (select count(*) from " .$GLOBALS['ecs']->table('wholesale_order_info'). " as oi2 where oi2.main_order_id = o.order_id) = 0";
                $where_ru = " (SELECT ru_id FROM " .$GLOBALS['ecs']->table('wholesale_order_goods'). " AS og WHERE og.order_id = o.order_id LIMIT 1) = 0";        
            }
        }
        elseif(!empty($filter['keywords']))
        {
            $groupBy = " GROUP BY o.order_id ";
            $leftJoin = " LEFT JOIN " .$GLOBALS['ecs']->table('wholesale_order_goods'). " AS iog ON iog.order_id = o.order_id ";
        }
        else
        {
            $groupBy = " GROUP BY o.order_id ";
            $leftJoin = " LEFT JOIN " .$GLOBALS['ecs']->table('wholesale_order_action'). " AS oa ON o.order_id = oa.order_id ";
        }
 
 		
        $filter = page_and_size($filter);
 
        
        $sql = "SELECT o.order_id, o.main_order_id, o.order_sn, o.add_time, o.order_status,o.pay_id,o.pay_fee,o.pay_time,o.pay_status," .
                " o.consignee, o.address, o.email, o.mobile, o.order_amount, o.is_delete, " .
                " o.user_id " . 
                " FROM " . $GLOBALS['ecs']->table('wholesale_order_info') . " AS o " .
                $leftJoin .
                $where . $where_store . $no_main_order . $groupBy .
                " ORDER BY $filter[sort_by] $filter[sort_order] " .
                " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";
        
        foreach (array('order_sn', 'consignee', 'email', 'address', 'zipcode', 'tel', 'user_name') AS $val)
        {
            $filter[$val] = stripslashes($filter[$val]);
        }

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql);


    
    foreach ($row AS $key => $value)
    {
        $row[$key]['pay_name'] = $GLOBALS['db']->getOne("SELECT pay_name FROM".$GLOBALS['ecs']->table('payment')."WHERE pay_id = '" . $value['pay_id']. "'");
        $row[$key]['pay_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['pay_time']);
        
        $value['ru_id'] = $GLOBALS['db']->getOne(" SELECT ru_id FROM ".$GLOBALS['ecs']->table('order_goods')." WHERE order_id = '".$value['order_id']."'", true);
        
        
        $sql = " SELECT user_name FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id = '".$value['user_id']."'";
        $value['buyer'] = $GLOBALS['db']->getOne($sql, true);
        $row[$key]['buyer'] = !empty($value['buyer']) ? $value['buyer'] : $GLOBALS['_LANG']['anonymous'];
        
        $row[$key]['formated_order_amount'] = price_format($value['order_amount']);
        $row[$key]['formated_money_paid'] = price_format($value['money_paid']);
        $row[$key]['formated_total_fee'] = price_format($value['total_fee']);
        $row[$key]['short_order_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
        $row[$key]['formated_total_fee_order'] = price_format($value['total_fee_order']);
        
        $row[$key]['region'] = get_user_region_address($value['order_id']);
		
        
        $row[$key]['user_name'] = get_shop_name($value['ru_id'], 1);

        $order_id = $value['order_id'];
        $date = array('order_id');
        
        $order_child = count(get_table_date('order_info', "main_order_id='$order_id'", $date, 1));
        $row[$key]['order_child'] = $order_child;

        $date = array('order_sn');
        $child_list = get_table_date('order_info', "main_order_id='$order_id'", $date, 1);
        $row[$key]['child_list'] = $child_list;
        
        
        $order = array(
            'order_id' => $value['order_id'],
            'order_sn' => $value['order_sn']
        );
        
        $goods = get_wholesale_order_goods($order_id);
        $row[$key]['goods_list'] = $goods['goods_list'];
    }
    
    $arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
    
    return $arr;
}

function get_wholesale_order_goods($order_id)
{
    global $ecs;
    $goods_list = array();
    $goods_attr = array();
    $sql = "SELECT o.*, g.goods_thumb, g.goods_sn, g.brand_id, g.user_id AS ru_id, w.goods_number AS storage, w.act_id, g.model_inventory, o.goods_attr, oi.order_sn " .
            "FROM " . $ecs->table('wholesale_order_goods') . " AS o ".
            "LEFT JOIN " . $ecs->table('goods') . " AS g ON o.goods_id = g.goods_id " .
            "LEFT JOIN " . $ecs->table('wholesale') . " AS w ON w.goods_id = g.goods_id " .
            "LEFT JOIN " . $ecs->table('wholesale_order_info') . " AS oi ON oi.order_id = o.order_id " .
            "WHERE o.order_id = '{$order_id}' ";
    $res = $GLOBALS['db']->query($sql);
    
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        
        
        if(empty($prod)){ 
            $row['goods_storage'] = $row['storage']; 
        }	
        $row['storage'] = !empty($row['goods_storage']) ? $row['goods_storage'] : 0;    	
        $row['formated_subtotal']       = price_format($row['goods_price'] * $row['goods_number']);
        $row['formated_goods_price']    = price_format($row['goods_price']);
        $row['goods_id'] = $row['act_id']; 
        
        $row['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        
        $goods_attr[] = explode(' ', trim($row['goods_attr'])); 
        $goods_list[] = $row;
    }

    $attr = array();
    $arr  = array();
    foreach ($goods_attr AS $index => $array_val)
    {
        foreach ($array_val AS $value)
        {
            $arr = explode(':', $value);
            $attr[$index][] =  @array('name' => $arr[0], 'value' => $arr[1]);
        }
    }

    return array('goods_list' => $goods_list, 'attr' => $attr);
}

function download_orderlist($result) {
    if(empty($result)) {
        return i("没有符合您要求的数据！^_^");
    }
	
	$data_name = "";
	$data_cnt = "";
	$adminru = get_admin_ru_id();
	if($adminru['ru_id'] < 1){
		$data_name = "商家名称,";
		$data_cnt = "存在";
	}
	
    $data = i('订单号,' .$data_name. '下单会员,下单时间,收货人,联系电话,地址,总金额,确认状态'."\n");
    $count = count($result);
    for ($i = 0; $i < $count; $i++) {
        $order_sn = i('#'.$result[$i]['order_sn']); 
        $order_user = i($result[$i]['buyer']);
        $order_time = i($result[$i]['short_order_time']);
        $consignee = i($result[$i]['consignee']);
        $tel = !empty($result[$i]['mobile']) ? i($result[$i]['mobile']) : i($result[$i]['tel']);
        $address = i($result[$i]['address']);
        $order_amount = i($result[$i]['order_amount']);
        $order_status = i($GLOBALS['_LANG']['os'][$result[$i]['order_status']]);
        $ru_name = !empty($data_cnt) ? i($result[$i]['user_name']) . ',' : ''; 
        $pay_status = i($GLOBALS['_LANG']['ps'][$result[$i]['pay_status']]);
        $shipping_status = i($GLOBALS['_LANG']['ss'][$result[$i]['shipping_status']]);
        $data .= $order_sn . ',' . $ru_name . $order_user . ',' .
                $order_time . ',' . $consignee . ',' . $tel . ',' .
                $address . ',' .
                $order_amount . ',' . $order_status . ',' .
                $pay_status . ',' . $shipping_status . "\n";
    }
    return $data;
}

function i($strInput) {
    return iconv('utf-8','gb2312',$strInput);
}
?>