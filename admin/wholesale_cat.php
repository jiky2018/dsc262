<?php



define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_wholesale.php');
$exc = new exchange($ecs->table("wholesale_cat"), $db, 'cat_id', 'cat_name');


if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}




if ($_REQUEST['act'] == 'list') {
    $parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
	$level = isset($_REQUEST['level']) ? $_REQUEST['level'] + 1 : 0;

    if ($parent_id) {
        $cat_list = wholesale_child_cat($parent_id);
		$smarty->assign('parent_id', $parent_id);
    } else {
        $cat_list = wholesale_cat_list(0, 0, false, 0, true, 'admin');
		
    }
    

    
    $adminru = get_admin_ru_id();
    $smarty->assign('ru_id', $adminru['ru_id']);

    if ($adminru['ru_id'] == 0) {
        $smarty->assign('action_link', array('href' => 'wholesale_cat.php?act=add', 'text' => $_LANG['add_wholesale_cat']));
    }
    

    
    $smarty->assign('ur_here', $_LANG['wholesale_cat']);
    $smarty->assign('full_page', 1);
	$smarty->assign('level', $level);
    $smarty->assign('cat_info', $cat_list);

    
    self_seller(BASENAME($_SERVER['PHP_SELF']));      
    
    
    assign_query_info();
    $smarty->display('wholesale_cat_list.dwt');
}




elseif ($_REQUEST['act'] == 'query')
{
    $cat_list = wholesale_cat_list(0, 0, false);
    $smarty->assign('cat_info',     $cat_list);

    
    $adminru = get_admin_ru_id();
    $smarty->assign('ru_id',     $adminru['ru_id']);
    

    make_json_result($smarty->fetch('wholesale_cat_list.dwt'));
}



if ($_REQUEST['act'] == 'add') {
    
    admin_priv('whole_sale');

    
    $smarty->assign('ur_here', $_LANG['add_wholesale_cat']);
    $smarty->assign('action_link', array('href' => 'wholesale_cat.php?act=list', 'text' => $_LANG['wholesale_cat_list']));

    $parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;

    $cat_select = wholesale_cat_list(0, 0, false, 0, true, '', 1);
	
    
    foreach ($cat_select as $k => $v) {
        if ($v['level']) {
            $level = str_repeat('&nbsp;', $v['level'] * 4);
            $cat_select[$k]['name'] = $level . $v['name'];
        }
    }

    $smarty->assign('cat_select', $cat_select);
    $smarty->assign('form_act', 'insert');
    $smarty->assign('cat_info', array('is_show' => 1, 'parent_id' => $parent_id));

    
    $adminru = get_admin_ru_id();
    $smarty->assign('ru_id', $adminru['ru_id']);
    

    
    assign_query_info();
    $smarty->display('wholesale_cat_info.dwt');
}




if ($_REQUEST['act'] == 'insert')
{
    
    admin_priv('whole_sale');

    
    $cat['parent_id']   	= !empty($_POST['parent_id'])    	? intval($_POST['parent_id'])  	: 0;
    $cat['sort_order']  	= !empty($_POST['sort_order'])   	? intval($_POST['sort_order']) 	: 0;
    $cat['cat_name']    	= !empty($_POST['cat_name'])     	? trim($_POST['cat_name'])     	: '';
    $cat['keywords']		= !empty($_POST['keywords']) 		? trim($_POST['keywords']) 		: '';
    $cat['cat_desc'] 		= !empty($_POST['cat_desc']) 		? $_POST['cat_desc'] 			: '';
    $cat['cat_alias_name'] 	= !empty($_POST['cat_alias_name']) 	? trim($_POST['cat_alias_name']): '';
    $cat['show_in_nav'] 	= !empty($_POST['show_in_nav']) 	? intval($_POST['show_in_nav']) : 0;
    $cat['is_show'] 		= !empty($_POST['is_show']) 		? intval($_POST['is_show']) 	: 0;
	$cat['style_icon'] 		= !empty($_POST['style_icon']) 		? trim($_POST['style_icon']) 	: 'other'; 
	
    $pin = new pin();
    $pinyin = $pin->Pinyin($cat['cat_name'], 'UTF8');
    $cat['pinyin_keyword'] = $pinyin;

    if (cname_exists($cat['cat_name'], $cat['parent_id']))
    {
        
       $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
       sys_msg($_LANG['catname_exist'], 0, $link);
    }
	
	
	if (!empty($_FILES['cat_icon']['name'])) {
		if ($_FILES["cat_icon"]["size"] > 200000) {
			sys_msg('上传图片不得大于200kb', 0, $link);
		}

		$icon_name = explode('.', $_FILES['cat_icon']['name']);
		$key = count($icon_name);
		$type = $icon_name[$key - 1];
		
		if ($type != 'jpg' && $type != 'png' && $type != 'gif') {
			sys_msg('请上传jpg,gif,png格式图片', 0, $link);
		}
		$imgNamePrefix = time() . mt_rand(1001, 9999);
		
		$imgDir = ROOT_PATH . "images/cat_icon";
		if (!file_exists($imgDir)) {
			mkdir($imgDir);
		}
		
		$imgName = $imgDir . "/" . $imgNamePrefix . '.' . $type;
		$saveDir = "images/cat_icon" . "/" . $imgNamePrefix . '.' . $type;
		move_uploaded_file($_FILES["cat_icon"]["tmp_name"], $imgName);
		$cat['cat_icon'] = $saveDir;
	}
	

    
    if ($db->autoExecute($ecs->table('wholesale_cat'), $cat) !== false)
    {
        $cat_id = $db->insert_id();

        admin_log($_POST['cat_name'], 'add', 'wholesale_cat');   
        clear_cache_files();    

        
        $link[0]['text'] = $_LANG['continue_add'];
        $link[0]['href'] = 'wholesale_cat.php?act=add';

        $link[1]['text'] = $_LANG['back_list'];
        $link[1]['href'] = 'wholesale_cat.php?act=list';

        sys_msg($_LANG['catadd_succed'], 0, $link);
    }
 }




if ($_REQUEST['act'] == 'edit') {
    admin_priv('whole_sale');   
    
    $cat_id = intval($_REQUEST['cat_id']);
    
    $cat_info = get_cat_info($cat_id, array(), 'wholesale_cat');  

    $smarty->assign('ur_here', $_LANG['edit_wholesale_cat']);
    $smarty->assign('action_link', array('text' => $_LANG['wholesale_cat_list'], 'href' => 'wholesale_cat.php?act=list'));

    
    $smarty->assign('cat_id', $cat_id);

    $adminru = get_admin_ru_id();
    $smarty->assign('ru_id', $adminru['ru_id']);
    
    $smarty->assign('cat_info', $cat_info);
    $smarty->assign('form_act', 'update');

    $cat_select = wholesale_cat_list(0, $cat_info['parent_id'], false, 0, true, '', 1);
    
    foreach ($cat_select as $k => $v) {
        if ($v['level']) {
            $level = str_repeat('&nbsp;', $v['level'] * 4);
            $cat_select[$k]['name'] = $level . $v['name'];
        }
    }

    $smarty->assign('cat_select', $cat_select);

    
    assign_query_info();
    $smarty->display('wholesale_cat_info.dwt');
}




if ($_REQUEST['act'] == 'update')
{
    
    admin_priv('whole_sale');

    
    $old_cat_name        	= $_POST['old_cat_name'];
    $cat_id              	= !empty($_POST['cat_id'])       	? intval($_POST['cat_id'])     : 0;
	
    $cat['parent_id']   	= !empty($_POST['parent_id'])    	? intval($_POST['parent_id'])  	: 0;
    $cat['sort_order']  	= !empty($_POST['sort_order'])   	? intval($_POST['sort_order']) 	: 0;
    $cat['cat_name']    	= !empty($_POST['cat_name'])     	? trim($_POST['cat_name'])     	: '';
    $cat['keywords']		= !empty($_POST['keywords']) 		? trim($_POST['keywords']) 		: '';
    $cat['cat_desc'] 		= !empty($_POST['cat_desc']) 		? $_POST['cat_desc'] 			: '';
    $cat['cat_alias_name'] 	= !empty($_POST['cat_alias_name']) 	? trim($_POST['cat_alias_name']): '';
    $cat['show_in_nav'] 	= !empty($_POST['show_in_nav']) 	? intval($_POST['show_in_nav']) : 0;
    $cat['is_show'] 		= !empty($_POST['is_show']) 		? intval($_POST['is_show']) 	: 0;
	$cat['style_icon'] 		= !empty($_POST['style_icon']) 		? trim($_POST['style_icon']) 	: 'other'; 
    
    $adminru = get_admin_ru_id();
	
       
    if ($cat['cat_name'] != $old_cat_name)
    {
        if (wholesale_cat_exists($cat['cat_name'],$cat['parent_id'], $cat_id))
        {
           $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
           sys_msg($_LANG['catname_exist'], 0, $link);
        }
    }

    $pin = new pin();
    $pinyin = $pin->Pinyin($cat['cat_name'], 'UTF8');
    $cat['pinyin_keyword'] = $pinyin;	
	
	
	if (!empty($_FILES['cat_icon']['name'])) {
		if ($_FILES["cat_icon"]["size"] > 200000) {
			sys_msg('上传图片不得大于200kb', 0, $link);
		}

		$icon_name = explode('.', $_FILES['cat_icon']['name']);
		$key = count($icon_name);
		$type = $icon_name[$key - 1];
		
		if ($type != 'jpg' && $type != 'png' && $type != 'gif') {
			sys_msg('请上传jpg,gif,png格式图片', 0, $link);
		}
		$imgNamePrefix = time() . mt_rand(1001, 9999);
		
		$imgDir = ROOT_PATH . "images/cat_icon";
		if (!file_exists($imgDir)) {
			mkdir($imgDir);
		}
		
		$imgName = $imgDir . "/" . $imgNamePrefix . '.' . $type;
		$saveDir = "images/cat_icon" . "/" . $imgNamePrefix . '.' . $type;
		move_uploaded_file($_FILES["cat_icon"]["tmp_name"], $imgName);
		$cat['cat_icon'] = $saveDir;
	}
	
	
    $dat = $db->getRow("SELECT cat_name FROM ". $ecs->table('wholesale_cat') . " WHERE cat_id = '$cat_id'");

    if ($db->autoExecute($ecs->table('wholesale_cat'), $cat, 'UPDATE', "cat_id = '$cat_id'"))
    {	
        clear_cache_files(); 
        admin_log($_POST['cat_name'], 'edit', 'wholesale_cat'); 

        
        $link[] = array('text' => $_LANG['back_list'], 'href' => 'wholesale_cat.php?act=list');
        sys_msg($_LANG['catedit_succed'], 0, $link);
    }
}





if ($_REQUEST['act'] == 'edit_sort_order')
{
    check_authz_json('whole_sale');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('sort_order' => $val)))
    {
        clear_cache_files(); 
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}




elseif ($_REQUEST['act'] == 'toggle_show')
{
    check_authz_json('auction');

    $cat_id     = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("is_show = '$val'", $cat_id);
    clear_cache_files();

    make_json_result($val);
}




if ($_REQUEST['act'] == 'remove')
{
    check_authz_json('whole_sale');

    
    $cat_id   = intval($_GET['id']);
    $cat_name = $db->getOne('SELECT cat_name FROM ' .$ecs->table('wholesale_cat'). " WHERE cat_id = '$cat_id'");

    
    $cat_count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('wholesale_cat'). " WHERE parent_id = '$cat_id'");

    
    $goods_count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('wholesale'). " WHERE wholesale_cat_id = '$cat_id'");

    
    if ($cat_count == 0 && $goods_count == 0)
    {
        
        $sql = 'DELETE FROM ' .$ecs->table('wholesale_cat'). " WHERE cat_id = '$cat_id'";
        if ($db->query($sql))
        {
            clear_cache_files();
            admin_log($cat_name, 'remove', 'wholesale_cat');
        }
    }
    else
    {
        make_json_error($cat_name .' '. $_LANG['cat_isleaf']);
    }

    $url = 'wholesale_cat.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}






function wholesale_cat_exists($cat_name, $parent_cat, $exclude = 0)
{
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('wholesale_cat').
           " WHERE parent_id = '$parent_cat' AND cat_name = '$cat_name' AND cat_id <> '$exclude'";
    return ($GLOBALS['db']->getOne($sql) > 0) ? true : false;
}


function cat_update($cat_id, $args)
{
    if (empty($args) || empty($cat_id))
    {
        return false;
    }

    return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('presale_cat'), $args, 'update', "cat_id='$cat_id'");
}


function cname_exists($cat_name, $parent_cat, $exclude = 0)
{
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('wholesale_cat').
    " WHERE parent_id = '$parent_cat' AND cat_name = '$cat_name' AND cat_id <> '$exclude'";
    return ($GLOBALS['db']->getOne($sql) > 0) ? true : false;
}


function wholesale_child_cat($pid){
	$sql = " SELECT cat_id, cat_name, parent_id, sort_order FROM ".$GLOBALS['ecs']->table('wholesale_cat')." WHERE parent_id = '$pid' ";
	return $GLOBALS['db']->getAll($sql);
}

?>