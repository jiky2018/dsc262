<?php
/**
 * 微信支付异步响应操作
 */
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');

/* 支付方式代码 */
$pay_code = 'wxpay';
/* 支付信息 */
$payment  = get_payment($pay_code);

// 获取异步数据postData

$postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
if (empty($postStr)){
	$postStr = file_get_contents('php://input');
}
//logResult($postStr);

if(!empty($postStr)){
    $postdata = json_decode(json_encode(simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    $plugin_file = ROOT_PATH . 'includes/modules/payment/' . $pay_code . '.php';
    /* 检查插件文件是否存在，如果存在则验证支付是否成功，否则则返回失败信息 */
    if (file_exists($plugin_file)) {
        /* 根据支付方式代码创建支付类的对象并调用其响应操作方法 */
        include_once($plugin_file);

        //微信端签名
        $wxsign = $postdata['sign'];
        unset($postdata['sign']);

        foreach ($postdata as $k => $v)
        {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);

        $buff = "";
        foreach ($Parameters as $k => $v)
        {
            $buff .= $k . "=" . $v . "&";
        }
        $String;
        if (strlen($buff) > 0) 
        {
            $String = substr($buff, 0, strlen($buff)-1);
        }
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$payment['wxpay_key'];
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $sign = strtoupper($String);
        //验证成功
        if ($wxsign == $sign) {
            //交易成功
           if($postdata['result_code'] == 'SUCCESS'){
                //获取log_id
                $out_trade_no = explode('O', $postdata['out_trade_no']);
                $order_sn = $out_trade_no[1];//订单号log_id
                // 改变订单状态
                order_paid($order_sn, 2);

                //修改订单信息(openid，tranid)
                $sql = "update ".$GLOBALS['ecs']->table('pay_log')." set openid = '".$postdata['openid']."', transid = '".$postdata['transaction_id']."' where log_id = ".$order_sn;
                $GLOBALS['db']->query($sql);
           }
           $returndata['return_code'] = 'SUCCESS';
        }
        else{
            $returndata['return_code'] = 'FAIL';
            $returndata['return_msg'] = '签名失败';
        }
        
    } 
    else {
        $returndata['return_code'] = 'FAIL';
        $returndata['return_msg'] = '插件不存在';
    }
}else{
    $returndata['return_code'] = 'FAIL';
    $returndata['return_msg'] = '无数据返回';
}
//数组转化为xml
$xml = "<xml>";
foreach ($returndata as $key=>$val)
{
     if (is_numeric($val))
     {
        $xml.="<".$key.">".$val."</".$key.">"; 

     }
     else
        $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
}
$xml.="</xml>";

echo $xml;
exit;
/*
//打印日志
function logResult($word='') {
    $fp = fopen("log.txt","a");
    flock($fp, LOCK_EX) ;
    fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}
*/
?>