<?php

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
 //print_r($_LANG);DIE(); 
if (empty($_REQUEST['act'])) {
	$_REQUEST['act'] = 'list';
}
else {
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

$smarty->assign('controller', basename(PHP_SELF, '.php'));
$smarty->assign('menus', $_SESSION['menus']);
$smarty->assign('action_type', 'bonus');
$exc = new exchange($ecs->table('bonus_type'), $db, 'type_id', 'type_name');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

if ($_REQUEST['act'] == 'list') {

	$smarty->assign('full_page', 1); 
	$tab_menu = array();

	$tab_menu[] = array('curr' => 1, 'text' => $_LANG['18_team'], 'href' => 'team.php?act=list');
	$tab_menu[] = array('curr' => 0, 'text' => '团队信息', 'href' => 'team.php?act=team_info');
	$smarty->assign('tab_menu', $tab_menu);

	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('ur_here', $_LANG['18_team']);
	$smarty->assign('action_link', array('text' => '添加拼团商品', 'href' => 'team.php?act=add', 'class' => 'icon-plus'));  
	
	assign_query_info();
    //拼团商品列表
    $where='user_id='.$adminru['ru_id'];
	$smarty->assign('team_goods_list', team_goods_list($where));  
	$smarty->display('team_goods_list.dwt');   
}

//搜索拼团商品  
elseif ($_REQUEST['act'] == 'query') {  
    $where='user_id='.$adminru['ru_id'];
	$smarty->assign('team_goods_list', team_goods_list($where));    
	make_json_result($smarty->fetch('team_goods_list.dwt'), '', array('filter' => '', 'page_count' => ''));
				    
}
//拼团活动列表
if ($_REQUEST['act'] == 'team_info') {

    $smarty->assign('menu_select', array('action' => '02_promotion','current' => '18_team')); 

	$smarty->assign('full_page', 1); 

	$tab_menu = array();

	$tab_menu[] = array('curr' => 0, 'text' => $_LANG['18_team'], 'href' => 'team.php?act=list');
	$tab_menu[] = array('curr' => 1, 'text' => '团队信息', 'href' => 'team.php?act=team_info');
	$smarty->assign('tab_menu', $tab_menu);

	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('ur_here', $_LANG['18_team']);
    //拼团活动列表
	$smarty->assign('team_info_list',sqlteam_info_list($adminru['ru_id']));

	assign_query_info();
	$smarty->display('team_info_list.dwt');   
} 
//搜索拼团活动列表
if ($_REQUEST['act'] == 'team_info_query') {
	$smarty->assign('team_info_list', sqlteam_info_list($adminru['ru_id']));   
	make_json_result($smarty->fetch('team_info_list.dwt'), '', array('filter' => '', 'page_count' => ''));  
}

elseif ($_REQUEST['act'] == 'edit') {
    $smarty->assign('menu_select', array('action' => '02_promotion','current' => '18_team')); 
	$smarty->assign('full_page', 1); 

	$tab_menu = array();
	$tab_menu[] = array('curr' => 1, 'text' => '添加拼团商品', 'href' => 'team.php?act=add');
	$smarty->assign('tab_menu', $tab_menu);

	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('ur_here', $_LANG['18_team']);


	$id = isset($_REQUEST['id'])?intval($_REQUEST['id']):'0';


	$info=$GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('team_goods')." WHERE id=$id");

	$goods = $GLOBALS['db']->getOne("SELECT goods_name FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id=".$info['goods_id']);

	$info['goods_name'] = $goods;
	$smarty->assign('goods', $info);
	set_default_filter();
	$smarty->assign('team_list', team_cat_list(0, $info['tc_id']));

	$smarty->display('team_goods_info.dwt');    
}

elseif ($_REQUEST['act'] == 'add') {
    $smarty->assign('menu_select', array('action' => '02_promotion','current' => '18_team')); 
	$smarty->assign('full_page', 1); 

	$tab_menu = array();
	$tab_menu[] = array('curr' => 1, 'text' => '添加拼团商品', 'href' => 'team.php?act=add');
	$smarty->assign('tab_menu', $tab_menu);

	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('ur_here', $_LANG['18_team']);

	//读取拼团频道下拉
	$smarty->assign('team_list', team_cat_list(0, $info['tc_id']));
	assign_query_info();
	$smarty->display('team_goods_info.dwt');    
}


//拼团列表
elseif ($_REQUEST['act'] == 'team_order') {
    $smarty->assign('menu_select', array('action' => '02_promotion','current' => '18_team')); 
	$smarty->assign('full_page', 1); 
	$tab_menu = array();
	$smarty->assign('primary_cat', $_LANG['02_promotion']);
	$smarty->assign('ur_here','团队订单');

	$tab_menu[] = array('curr' => 0, 'text' => '拼团商品列表', 'href' => 'team.php?act=list');
	$tab_menu[] = array('curr' => 0, 'text' => '团队信息', 'href' => 'team.php?act=team_info');
	$tab_menu[] = array('curr' => 1, 'text' => '团队订单', 'href' => 'team.php?act=team_order');
	$smarty->assign('tab_menu', $tab_menu);
    $team_id = empty($_REQUEST['team_id'])?0:intval($_REQUEST['team_id']);

    $sql = 'select o.*,og.goods_name,og.ru_id from '.$GLOBALS['ecs']->table('order_info').' as o 
    LEFT JOIN '.$GLOBALS['ecs']->table('order_goods').'  as og on o.order_id = og.order_id 
    where o.team_id = ' . $team_id . '  and o.extension_code =\'team_buy\' order by o.order_id desc  ';

	$list = $GLOBALS['db']->getAll($sql);
	foreach ($list as $key => $val) {
		$list[$key]['region'] = get_user_region_address($val['order_id']);
		$list[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['add_time']);
		$list[$key]['formated_order_amount'] = price_format($val['order_amount']);
		$list[$key]['formated_total_fee'] = price_format($val['goods_amount']);
		$list[$key]['user_name'] = get_shop_name($val['ru_id'], 1);
		$list[$key]['status'] = $os[$val[order_status]] . ',' . $ps[$val[pay_status]] . ',' . $ss[$val[shipping_status]];	
	}
	$smarty->assign('team_order_list', $list);
	assign_query_info();
	$smarty->display('team_order_list.dwt');       
}

//删除拼团商品
elseif ($_REQUEST['act'] == 'remove') {
    //判断商品归属
    $id = intval($_REQUEST['id']);
	$sql = 'SELECT g.user_id ' . 'FROM ' . $ecs->table('goods') . '
    g INNER JOIN ' . $ecs->table('team_goods') . 'tg  ON g.goods_id=tg.goods_id 
	 WHERE tg.id = \'' . $id . '\' ';
	$team_goods = $db->getRow($sql);

	if ($team_goods['user_id'] != $adminru['ru_id']) {
		$url = 'team.php?act=list&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
		exit();
	}
    //执行数据操作   
    $sql='DELETE FROM ' . $ecs->table('team_goods') . ' WHERE id = \'' . $id . '\' ';
    $db->query($sql);
   
    //返回json数据
    make_json_result('','',array('filter' => $goods_list['filter'], 'page_count' => $goods_list['page_count'],'Location'=>1,'url'=>'team.php?act=list'));
}
//删除拼团活动
elseif ($_REQUEST['act'] == 'remove_info') {
    $id =empty($_REQUEST['id'])?0: intval($_REQUEST['id']);
    //判断商品归属
    $sql="SELECT g.user_id FROM ".$ecs->table('goods')." g LEFT JOIN ".$ecs->table('team_log')." tl ON g.goods_id=tl.goods_id WHERE tl.team_id=".$id;
    $user_id= $GLOBALS['db']->getOne($sql);
    //不是跳转
    if ($user_id != $adminru['ru_id']) {
		$url = 'team.php?act=list&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
		ecs_header('Location: ' . $url . "\n");
		exit();
	}
	//执行删除
	$sql="DELETE FROM  ".$ecs->table('team_log')." WHERE team_id=".$id;
	$db->query($sql);
	//查询数据 json返回
    $smarty->assign('team_info_list', sqlteam_info_list($adminru['ru_id']));   
 
	make_json_result($smarty->fetch('team_info_list.dwt'), '', array('filter' => '', 'page_count' => ''));  
	
}
//批量拼团删除
elseif ($_REQUEST['act'] == 'batch_drop') {
    //判断商品归属提交商品id
    check_team_id($_POST['checkboxes'],$adminru['ru_id']);
    $links = array(array('href' => 'team.php?act=list', 'text' => '返回拼团列表页'));
    sys_msg('删除成功', 0, $links);
}


//搜索   search_goods
elseif ($_REQUEST['act'] == 'search_goods') {
    $smarty->assign('menu_select', array('action' => '02_promotion','current' => '18_team')); 
    include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$filter = $json->decode($_GET['JSON']);
	$arr = get_goods_list($filter);
	make_json_result($arr);
}
else if ($_REQUEST['act'] == 'group_goods') {	
	include_once ROOT_PATH . 'includes/cls_json.php';
	$json = new JSON();
	$filter = $json->decode($_GET['JSON']);
	$arr = get_admin_goods_info($filter->goods_id);
	make_json_result($arr);
}
//编辑添加
elseif ($_REQUEST['act'] == 'insert_update') {
	$smarty->assign('menu_select', array('action' => '02_promotion','current' => '18_team')); 
    if (IS_POST) {
		$data =$_POST['data'];
		if ($data['tc_id'] <= 0) {
			$links = array(array('href' => 'team.php?act=add', 'text' => $_LANG['continue_add']));
			sys_msg('请选择频道', 0, $links);
		}
        
		$data['goods_id'] =empty($_POST['goods_id']) ? '':intval($_POST['goods_id']);
		$_POST['id']=empty($_POST['id']) ? 0:intval($_POST['id']);
        //添加商品
		if (empty($_POST['id'])) {
			$sql="SELECT id  FROM ".$GLOBALS['ecs']->table('team_goods')." WHERE goods_id=".$data['goods_id'];

			if (!empty($GLOBALS['db']->getAll($sql))) {
				$links = array(array('href' => 'team.php?act=add', 'text' => $_LANG['continue_add']));
			    sys_msg('此商品已存在拼团商品列表中', 0, $links);			
			}
 
			if($GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('team_goods'),$data,'INSERT')){
                $links = array(array('href' => 'team.php?act=add', 'text' => $_LANG['continue_add']),
                	array('href' => 'team.php?act=list', 'text' => '返回拼团列表页'));
			    sys_msg('添加成功', 0, $links);
			}
			else{
				$links = array(
					array('href' => 'team.php?act=add', 'text' => $_LANG['continue_add']),
					array('href' => 'team.php?act=list', 'text' => '返回拼团列表页')
					);
			    sys_msg('添加失败', 0, $links);
			}

		}
		else if ($GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('team_goods'),$data,'','id='.$_POST['id'])) {//更新
			$links = array(array('href' => 'team.php?act=add', 'text' => $_LANG['continue_add']),
				array('href' => 'team.php?act=list', 'text' => '返回拼团列表页'));
			sys_msg('修改成功', 0, $links);
		}
		else {
			$links = array(array('href' => 'team.php?act=add', 'text' => $_LANG['continue_add']));
			sys_msg('修改失败', 0, $links);
		}
	}
}

//拼团商品列表
function team_goods_list($where){

	$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
	if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
		$filter['keyword'] = json_str_iconv($filter['keyword']);
	}
	if (!empty($filter['keyword'])) {
		$where .= ' AND (g.goods_sn LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\' OR g.goods_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\'' . ')';
	}
	$sql = 'SELECT tg.*,g.user_id,g.goods_sn,g.goods_name,g.shop_price,g.market_price,g.goods_number,g.goods_img,g.goods_thumb,g.is_best,g.is_new,g.is_hot FROM 
	'.$GLOBALS['ecs']->table('team_goods').' as tg 
	LEFT JOIN '.$GLOBALS['ecs']->table('goods').'as g 
	ON tg.goods_id = g.goods_id WHERE ' . $where . ' ORDER BY tg.id DESC ' ;
    
	$list = $GLOBALS['db']->getAll($sql);
	foreach ($list as $key => $val) {
		$list[$key]['goods_name'] = $val['goods_name'];
		$list[$key]['user_name'] = get_shop_name($val['user_id'], 1);
		$list[$key]['shop_price'] = price_format($val['shop_price']);
		$list[$key]['market_price'] = price_format($val['market_price']);
		$list[$key]['goods_number'] = $val['goods_number'];
		$list[$key]['sales_volume'] = $val['sales_volume'];
		$list[$key]['goods_img'] = get_image_path($val['goods_img']);
		$list[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
		$list[$key]['team_price'] = price_format($val['team_price']);
		$list[$key]['team_num'] = $val['team_num'];
		if ($val['is_audit'] == 1) {
			$is_audit = '审核未通过';
		}
		else if ($val['is_audit'] == 2) {
			$is_audit = '审核已通过';
		}
		else {
			$is_audit = '未审核';
		}
		$list[$key]['is_audit'] = $is_audit;
		$sql="SELECT SUM( og.goods_number )  FROM  ".$GLOBALS['ecs']->table('order_goods')." og 
		INNER JOIN  ".$GLOBALS['ecs']->table('order_info')." oi ON og.order_id=oi.order_id
		INNER JOIN ".$GLOBALS['ecs']->table('team_log')." tl ON tl.team_id=oi.team_id
		WHERE tl.goods_id= ".$val['goods_id'] ." AND oi.pay_status =2" ;
		$list[$key]['limit_num'] = $GLOBALS['db']->getOne($sql);
	}
	return $list;
}

//判断批量删除拼团商品id
function check_team_id($arr,$user_id){

	$id=0;
   //遍历判断
   foreach ($arr as $key => $value) {
   	   $sql="SELECT g.user_id FROM  ".$GLOBALS['ecs']->table("goods")."  g LEFT JOIN ".$GLOBALS['ecs']->table('team_goods')." tg ON tg.goods_id=g.goods_id WHERE g.user_id=".$user_id." AND id=".$value;
   	   
   	   //判断销毁
   	   if(!empty($GLOBALS['db']->getOne($sql))){
           $id.=','.$value;          
   	   }
   }
  
   //执行删除
   if($id!==0){
       $sql="DELETE FROM ".$GLOBALS['ecs']->table("team_goods")." WHERE id IN (".$id.");";

       $GLOBALS['db']->query($sql);
   }

}
//拼团列表
function sqlteam_info_list($ru_id){
    $where='AND g.user_id='.$ru_id;
  
	$filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
	if (isset($_REQUEST['is_ajax']) && ($_REQUEST['is_ajax'] == 1)) {
		$filter['keyword'] = json_str_iconv($filter['keyword']);
	}
	if (!empty($filter['keyword'])) {
		$where .= ' AND (g.goods_sn LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\' OR g.goods_name LIKE \'%' . mysql_like_quote($filter['keyword']) . '%\'' . ')';
	}
    
	$sql = 'select tl.team_id, tl.start_time,tl.goods_id,tl.status,tg.team_num,tg.validity_time,g.user_id,g.goods_name,g.goods_thumb,g.shop_price 
	from '.$GLOBALS['ecs']->table('team_log').' as tl 
	LEFT JOIN '.$GLOBALS['ecs']->table('team_goods').' as tg ON tl.goods_id = tg.goods_id 
	LEFT JOIN '.$GLOBALS['ecs']->table('goods').' as g ON tl.goods_id = g.goods_id where tl.is_show = 1 '.$where.'  order by tl.start_time desc ';
	
	$list = $GLOBALS['db']->getAll($sql);
	$time = gmtime();

	foreach ($list as $key => $val) {
		$list[$key]['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['start_time']);
		$list[$key]['shop_price'] = price_format($val['shop_price']);
		$list[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
		$list[$key]['user_name'] = get_shop_name($val['user_id'], 1);
		$list[$key]['surplus'] = $val['team_num'] - surplus_num($val['team_id']);
		if (($val['status'] != 1) && ($time < ($val['start_time'] + ($val['validity_time'] * 3600)))) {
			$list[$key]['status'] = '进行中';
		}
		else {
			if (($val['status'] != 1) && (($val['start_time'] + ($val['validity_time'] * 3600)) < $time)) {
				$list[$key]['status'] = '失败团';
			}
			else if ($val['status'] == 1) {
				$list[$key]['status'] = '成功团';
			}
		}

		$endtime = $val['start_time'] + ($val['validity_time'] * 3600);
		$cle = $endtime - $time;
		$d = floor($cle / 3600 / 24);
		$h = floor(($cle % (3600 * 24)) / 3600);
		$m = floor(($cle % (3600 * 24)) / 600 / 60);
		$list[$key]['time'] = $d . '天' . $h . '小时' . $m . '分钟';
		$list[$key]['cle'] = $cle;
	}

	return $list;
}
//统计拼团人数
function surplus_num($team_id = 0)
{
	$sql = 'SELECT count(order_id) as num  FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE team_id = \'' . $team_id . '\' AND extension_code = \'team_buy\'  and pay_status = \'' . PS_PAYED . '\' ';
	$res = $GLOBALS['db']->getRow($sql);
	return $res['num'];
}

//拼团分类函数
function team_cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0)
{
	static $res;

	$sql = 'SELECT c.*, COUNT(s.id) AS has_children' . ' FROM  ' . $GLOBALS['ecs']->table('team_category') . '  AS c ' . ' LEFT JOIN   ' . $GLOBALS['ecs']->table('team_category') . '  AS s ON s.parent_id=c.id' . ' where c.status = 1' . ' GROUP BY c.id ' . ' ORDER BY parent_id, sort_order DESC';
	$res = $GLOBALS['db']->getAll($sql);
	


	$options = team_cat_options($cat_id, $res);

	if (0 < $level) {
		if ($cat_id == 0) {
			$end_level = $level;
		}
		else {
			$first_item = reset($options);
			$end_level = $first_item['level'] + $level;
		}

		foreach ($options as $key => $val) {
			if ($end_level <= $val['level']) {
				unset($options[$key]);
			}
		}
	}

	$pre_key = 0;

	foreach ($options as $key => $value) {
		$options[$key]['has_children'] = 1;

		if (0 < $pre_key) {
			if ($options[$pre_key]['id'] == $options[$key]['parent_id']) {
				$options[$pre_key]['has_children'] = 1;
			}
		}

		$pre_key = $key;
	}

	if ($re_type == true) {
		$select = '';

		foreach ($options as $var) {
			$select .= '<option value="' . $var['id'] . '" ';
			$select .= ($selected == $var['id'] ? 'selected=\'ture\'' : '');
			$select .= '>';

			if (0 < $var['level']) {
				$select .= str_repeat('&nbsp;', $var['level'] * 4);
			}

			$select .= htmlspecialchars(addslashes($var['name'])) . '</option>';
		}

		return $select;
	}
	else {
		foreach ($options as $key => $value) {
			$options[$key]['url'] = build_uri('article_cat', array('acid' => $value['cat_id']), $value['cat_name']);
		}

		return $options;
	}
}

//生成拼团下拉
function team_cat_options($spec_cat_id, $arr)
{

	static $cat_options = array();

	if (isset($cat_options[$spec_cat_id])) {
		return $cat_options[$spec_cat_id];
	}

	if (!isset($cat_options[0])) {
		$level = $last_cat_id = 0;
		$options = $cat_id_array = $level_array = array();

		while (!empty($arr)) {
			foreach ($arr as $key => $value) {
				$cat_id = $value['id'];
				if (($level == 0) && ($last_cat_id == 0)) {
					if (0 < $value['parent_id']) {
						break;
					}

					$options[$cat_id] = $value;
					$options[$cat_id]['level'] = $level;
					$options[$cat_id]['id'] = $cat_id;
					$options[$cat_id]['name'] = $value['name'];
					unset($arr[$key]);

					if ($value['has_children'] == 0) {
						continue;
					}

					$last_cat_id = $cat_id;
					$cat_id_array = array($cat_id);
					$level_array[$last_cat_id] = ++$level;
					continue;
				}

				if ($value['parent_id'] == $last_cat_id) {
					$options[$cat_id] = $value;
					$options[$cat_id]['level'] = $level;
					$options[$cat_id]['id'] = $cat_id;
					$options[$cat_id]['name'] = $value['name'];
					unset($arr[$key]);

					if (0 < $value['has_children']) {
						if (end($cat_id_array) != $last_cat_id) {
							$cat_id_array[] = $last_cat_id;
						}

						$last_cat_id = $cat_id;
						$cat_id_array[] = $cat_id;
						$level_array[$last_cat_id] = ++$level;
					}
				}
				else if ($last_cat_id < $value['parent_id']) {
					break;
				}
			}

			$count = count($cat_id_array);

			if (1 < $count) {
				$last_cat_id = array_pop($cat_id_array);
			}
			else if ($count == 1) {
				if ($last_cat_id != end($cat_id_array)) {
					$last_cat_id = end($cat_id_array);
				}
				else {
					$level = 0;
					$last_cat_id = 0;
					$cat_id_array = array();
					continue;
				}
			}

			if ($last_cat_id && isset($level_array[$last_cat_id])) {
				$level = $level_array[$last_cat_id];
			}
			else {
				$level = 0;
			}
		}

		$cat_options[0] = $options;
	}
	else {
		$options = $cat_options[0];
	}

	if (!$spec_cat_id) {
		return $options;
	}
	else {
		if (empty($options[$spec_cat_id])) {
			return array();
		}

		$spec_cat_id_level = $options[$spec_cat_id]['level'];

		foreach ($options as $key => $value) {
			if ($key != $spec_cat_id) {
				unset($options[$key]);
			}
			else {
				break;
			}
		}

		$spec_cat_id_array = array();

		foreach ($options as $key => $value) {
			if ((($spec_cat_id_level == $value['level']) && ($value['id'] != $spec_cat_id)) || ($value['level'] < $spec_cat_id_level)) {
				break;
			}
			else {
				$spec_cat_id_array[$key] = $value;
			}
		}

		$cat_options[$spec_cat_id] = $spec_cat_id_array;
		return $spec_cat_id_array;
	}
}

?>
