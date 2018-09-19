<?php

/**
 * ECMOBAN 公用函数库
 * ============================================================================
 * * 版权所有 2005-2016 上海商创网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecmoban.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: lib_common.php 17217 2011-01-19 06:29:08Z liubo $
*/

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}


/*
 * 【阿里大鱼】
 * ---后台管理员修改商家账号密码发送短信功能.
 * 
 * 【变量】seller_name 店铺登录账号
 * 【变量】seller_password 店铺登录密码
 * 【变量】current_admin_name 操作员
 * 【变量】edit_time 修改时间
 * 【变量】shop_name 商店名称
 * 【变量】seller_name 店铺名称
 * 【变量】content 发送短信内容
 * 【变量】mobile_phone 店铺联系电话
 */
function get_seller_edit_info_lang($str_centent = array())
{
    //可根据需要使用格式修改 --start
    $content = sprintf($GLOBALS['_LANG']['edit_seller_info'], $str_centent['seller_name'], $str_centent['seller_password'], $str_centent['current_admin_name'], $str_centent['edit_time']);

    //短信内容参数（注意短信模板参数要和此参数名称一致）
    $smsParams = array(
        'shop_name' => '',
        'user_name' => $str_centent['shop_name'],
        'content' => $content
    );

    $result = array(
        'SmsType' => 'normal', //短信类型，一般默认
        
        //接口调用对应修改部分 start
        'SignName' => '注册验证',       //短信签名
        'SmsCdoe' => 'SMS_12811399',     //短信模板ID
        //接口调用对应修改部分 end
        
        'smsParams' => json_encode($smsParams),
        'mobile_phone' => $str_centent['mobile_phone'],
    );
    
    return $result;
}

/*
 * 【阿里大鱼】
 * ---会员注册发送短信功能.
 * 
 * 【变量】mobile_code 验证码
 * 【变量】user_name 会员名称
 * 【变量】mobile_phone 注册会员手机号
 */
function get_register_lang($str_centent = array())
{
    //短信内容参数（注意短信模板参数要和此参数名称一致）
    $smsParams = array(
        'code'    => $str_centent['mobile_code']
    );
    
    if($str_centent['user_name']){
        $smsParams['product'] = $str_centent['user_name'];
    }

    $result = array(
        'SmsType' => 'normal', //短信类型，一般默认
        
        //接口调用对应修改部分 start
        'SignName' => '注册验证',       //短信签名
        'SmsCdoe' => 'SMS_12465179',     //短信模板ID
        //接口调用对应修改部分 end
        
        'smsParams' => json_encode($smsParams),
        'mobile_phone' => $str_centent['mobile_phone']
    );
    
    return $result;
}

/*
 * 【阿里大鱼】
 * ---会员下单发送短信通知功能.
 * 
 * 【变量】order_msg 订单信息
 * 【变量】shop_name 商店名称/店铺名称
 */
function get_order_info_lang($str_centent = array())
{
    if ($str_centent['shop_name'])
    {
        $str_centent['shop_name'] = "【" .$str_centent['shop_name'] ."】";
    }
    //短信内容参数（注意短信模板参数要和此参数名称一致）
    $smsParams = array(
        'shop_name' => $str_centent['shop_name'],
        'user_name' => $str_centent['user_name'],
        'content'   => $str_centent['order_msg']
    );
    
    $result = array(
        'SmsType' => 'normal', //短信类型，一般默认
        
        //接口调用对应修改部分 start
        'SignName' => '变更验证',       //短信签名
        'SmsCdoe' => 'SMS_12826146',     //短信模板ID
        //接口调用对应修改部分 end
        
        'smsParams' => json_encode($smsParams),
        'mobile_phone' => $str_centent['mobile_phone'],
    );
    
    return $result;
}
?>