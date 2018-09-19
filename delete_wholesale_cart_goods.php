<?php

/**
 * ECSHOP 购物流程
 * ============================================================================
 * 版权所有 2005-2016 上海商创网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecmoban.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: zblikai $
 * $Id: flow.php 15632 2009-02-20 03:58:31Z zblikai $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once('includes/cls_json.php');
require(dirname(__FILE__) . '/includes/lib_wholesale.php');
//ecmoban模板堂 --zhuo start
if(!empty($_SESSION['user_id'])){
    $sess_id = " user_id = '" . $_SESSION['user_id'] . "' ";
    $c_sess = " c.user_id = '" . $_SESSION['user_id'] . "' ";
}else{
    $sess_id = " session_id = '" . real_cart_mac_ip() . "' ";
    $c_sess = " c.session_id = '" . real_cart_mac_ip() . "' ";
}
//ecmoban模板堂 --zhuo end

$result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '','index'=>-1);
$result['index']=!empty($_POST['index'])?intval($_POST['index']):'0';
$json  = new JSON;
if($_POST['id'])
{
    $sql = 'DELETE FROM '.$GLOBALS['ecs']->table('wholesale_cart')." WHERE rec_id=".$_POST['id'];
    $GLOBALS['db']->query($sql);
}

$sql = 'SELECT c.*,g.goods_thumb,g.goods_id,c.goods_number,c.goods_price, c.extension_code ' .
        ' FROM ' . $GLOBALS['ecs']->table('wholesale_cart') ." AS c ".
        " LEFT JOIN ".$GLOBALS['ecs']->table('goods')." AS g ON g.goods_id=c.goods_id ".
        " WHERE " .$c_sess;
$row = $GLOBALS['db']->GetAll($sql);
$arr = array();
foreach($row AS $k=>$v)
{
    $arr[$k]['goods_thumb']  =get_image_path($v['goods_id'], $v['goods_thumb'], true);
    $arr[$k]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($v['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $v['goods_name'];
    $arr[$k]['url']          = build_uri('goods', array('gid' => $v['goods_id']), $v['goods_name']);
    $arr[$k]['goods_number'] = $v['goods_number'];
    $arr[$k]['goods_name']   = $v['goods_name'];
    $arr[$k]['goods_price']  = price_format($v['goods_price']);
    $arr[$k]['warehouse_id'] = $v['warehouse_id'];
    $arr[$k]['area_id'] = $v['area_id'];
    $arr[$k]['rec_id']       = $v['rec_id'];
    $arr[$k]['goods_attr'] = array_values(get_goods_attr_array($v['goods_attr_id']));
}		
$sql = 'SELECT COUNT(rec_id) AS cart_number, SUM(goods_number) AS number, SUM(goods_price * goods_number) AS amount' .
        ' FROM ' . $GLOBALS['ecs']->table('wholesale_cart') .
        " WHERE " . $sess_id;
$row = $GLOBALS['db']->GetRow($sql);

if ($row)
{
    $cart_number = intval($row['cart_number']);
    $number = intval($row['number']);
    $amount = price_format(floatval($row['amount']));
}
else
{
    $cart_number = 0;
    $number = 0;
    $amount = 0;
}

$result['cart_num'] = $cart_number;

$GLOBALS['smarty']->assign('str',$cart_number);
$GLOBALS['smarty']->assign('goods',$arr);

$GLOBALS['smarty']->assign('number', $number);
$GLOBALS['smarty']->assign('amount', $amount);
$result['content'] = $GLOBALS['smarty']->fetch('library/wholesale_cart_info.lbi');
$result['cart_content'] = $GLOBALS['smarty']->fetch('library/wholesale_cart_menu_info.lbi');
die($json->encode($result));


?>