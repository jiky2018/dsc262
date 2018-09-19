<?php



define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . '/includes/lib_wholesale.php');

include_once(ROOT_PATH . 'includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);
$exc   = new exchange($ecs->table("wholesale_purchase"), $db, 'purchase_id', 'subject');
$exc_goods   = new exchange($ecs->table("wholesale_purchase_goods"), $db, 'goods_id', 'goods_name');


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


include_once(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/wholesale_purchase.php');
$smarty->assign('lang', $_LANG);



if ($_REQUEST['act'] == 'list')
{
	admin_priv('wholesale_purchase');
	
    $smarty->assign('ur_here',     $_LANG['01_wholesale_purchase']);
    $smarty->assign('full_page',  1);
	
    $purchase_list = purchase_list();

    $smarty->assign('purchase_list',     $purchase_list['purchase_list']);
    $smarty->assign('filter',       $purchase_list['filter']);
    $smarty->assign('record_count', $purchase_list['record_count']);
    $smarty->assign('page_count',   $purchase_list['page_count']);
	
    $sort_flag  = sort_flag($purchase_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('purchase_list.dwt');
}




elseif ($_REQUEST['act'] == 'edit')
{
    admin_priv('wholesale_purchase');	

	$purchase_id = empty($_REQUEST['purchase_id'])? 0:intval($_REQUEST['purchase_id']);

    $smarty->assign('ur_here',       $_LANG['purchase_info']);
    $smarty->assign('action_link',   array('href' => 'wholesale_purchase.php?act=list', 'text' => $_LANG['01_wholesale_purchase']));
    $smarty->assign('form_act',      'update');
    $smarty->assign('action',        'edit');
    $smarty->assign('purchase_info', get_purchase_info($purchase_id));

    assign_query_info();
    $smarty->display('purchase_info.dwt');
}




elseif ($_REQUEST['act'] == 'update')
{
    admin_priv('ad_manage');

    
    $purchase_id = empty($_REQUEST['purchase_id'])? 0:intval($_REQUEST['purchase_id']);

	

    
    $href[] = array('text' => $_LANG['back_list'], 'href' => 'wholesale_purchase.php?act=list');
    sys_msg($_LANG['edit_success'], 0, $href);
}




elseif ($_REQUEST['act'] == 'query')
{
    $purchase_list = purchase_list();

    $smarty->assign('purchase_list',     $purchase_list['purchase_list']);
    $smarty->assign('filter',       $purchase_list['filter']);
    $smarty->assign('record_count', $purchase_list['record_count']);
    $smarty->assign('page_count',   $purchase_list['page_count']);

    $sort_flag  = sort_flag($purchase_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('purchase_list.dwt'), '',
        array('filter' => $purchase_list['filter'], 'page_count' => $purchase_list['page_count']));
}




elseif ($_REQUEST['act'] == 'batch')
{
    
    if (empty($_POST['checkboxes']))
    {
        sys_msg($_LANG['no_record_selected']);
    }
    else
    {
        
        admin_priv('whole_sale');

        $ids = $_POST['checkboxes'];

        if (isset($_POST['drop']))
        {
            
            $sql = "DELETE w FROM " . $ecs->table('wholesale_purchase') . " AS W " .
                    " WHERE purchase_id " . db_create_in($ids);
            $db->query($sql);

            
            admin_log('', 'batch_remove', 'wholesale_purchase');

            
            clear_cache_files();

            $links[] = array('text' => $_LANG['back_list'], 'href' => 'wholesale_purchase.php?act=list&' . list_link_postfix());
            sys_msg($_LANG['batch_drop_ok'], 0, $links);
        }
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




if ($_REQUEST['act'] == 'toggle_review_status')
{
    check_authz_json('wholesale_purchase');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

	$sql = " UPDATE ".$GLOBALS['ecs']->table('wholesale_purchase')." SET review_status = '$val' WHERE purchase_id = '$id' ";
    if ($GLOBALS['db']->query($sql))
    {
        
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}


function purchase_list()
{	
    
    $filter = array();
	
    
    $filter['keyword'] = !empty($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
    if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
    {
		$filter['keyword'] = json_str_iconv($filter['keyword']);
    }
    
    
    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'purchase_id' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

    $where = 'WHERE 1 ';
    
    
    if (!empty($filter['keyword']))
    {
        $where .= " AND subject LIKE '%" . mysql_like_quote($filter['keyword']) . "%'";  
    }

    
    $sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('wholesale_purchase') . $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    $filter = page_and_size($filter);

    
    $arr = array();
    $sql = 'SELECT * FROM ' .$GLOBALS['ecs']->table('wholesale_purchase').$where.
		'ORDER by '.$filter['sort_by'].' '.$filter['sort_order'];

    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $idx = 0;
    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        
        $rows['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $rows['add_time']);
        $rows['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $rows['end_time']);
        $rows['user_name'] = get_table_date('users', "user_id = '$rows[user_id]'", array('user_name'), 2);
        
		 
        $arr[$idx] = $rows;

        $idx++;
    }

    return array('purchase_list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}


?>