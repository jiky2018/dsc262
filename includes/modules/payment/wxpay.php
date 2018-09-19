<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：wxpay.php
 * ----------------------------------------------------------------------------
 * 功能描述：微信支付插件
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/wxpay.php';

if (file_exists($payment_lang))
{
    global $_LANG;

    include_once($payment_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE) {
    $i = isset($modules) ? count($modules) : 0;
    /* 代码 */
    $modules[$i]['code'] = basename(__FILE__, '.php');
    /* 描述对应的语言项 */
    $modules[$i]['desc'] = 'wxpay_desc';
    /* 是否支持货到付款 */
    $modules[$i]['is_cod'] = '0';
    /* 是否支持在线支付 */
    $modules[$i]['is_online'] = '1';
    /* 作者 */
    $modules[$i]['author'] = 'ECTOUCH TEAM';
    /* 网址 */
    $modules[$i]['website'] = 'http://mp.weixin.qq.com/';
    /* 版本号 */
    $modules[$i]['version'] = '2.5';
    /* 配置信息 */
    $modules[$i]['config'] = array(
        //微信公众号身份的唯一标识
        array(
            'name' => 'wxpay_appid',
            'type' => 'text',
            'value' => ''
        ),
        //JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
        array(
            'name' => 'wxpay_appsecret',
            'type' => 'text',
            'value' => ''
        ),
        //商户支付密钥Key
        array(
            'name' => 'wxpay_key',
            'type' => 'text',
            'value' => ''
        ),
        //受理商ID
        array(
            'name' => 'wxpay_mchid',
            'type' => 'text',
            'value' => ''
        )
    );

    return;
}

/**
 * 微信支付类
 */
class wxpay
{

    var $parameters; // cft 参数
    var $payment; // 配置信息
    /**
     * 生成支付代码
     *
     * @param array $order
     * 订单信息
     * @param array $payment
     * 支付方式信息
     */
    function get_code($order, $payment)
    {
        // 配置参数
        $this->payment = $payment;
        //设置必填参数
        $this->setParameter("body", $order['order_sn']);//商品描述
        $this->setParameter("out_trade_no", $order['order_sn'] .'O'. $order['log_id'].'O'.( $order['order_amount'] * 100));//商户订单号
        $this->setParameter("total_fee", $order['order_amount'] * 100);//总金额
        $this->setParameter("notify_url", $GLOBALS['ecs']->url().'wxpay_native_callback.php');//通知地址
        $this->setParameter("trade_type", "NATIVE");//交易类型

        $result = $this->getResult();
        //商户根据实际情况设置相应的处理流程
        if ($result["return_code"] == "FAIL")
        {
            show_message("支付失败：".$result['return_msg'], '我的订单', 'user.php?act=order_list');
        }
        elseif($result["result_code"] == "FAIL")
        {
			show_message("支付失败：".$result['err_code'] . $result['err_code_des'], '我的订单', 'user.php?act=order_list');
        }
        elseif($result["code_url"] != NULL)
        {
            //从统一支付接口获取到code_url
            $code_url = $result["code_url"];
			if(file_exists(ROOT_PATH . 'includes/phpqrcode/phpqrcode.php')){
				include(ROOT_PATH . 'includes/phpqrcode/phpqrcode.php');
			}
			// 纠错级别：L、M、Q、H
			$errorCorrectionLevel = 'L';
			// 点的大小：1到10
			$matrixPointSize = 3;
			// 生成的文件名
			$tmp = ROOT_PATH .'images/qrcode/';
			if(!is_dir($tmp)){
				@mkdir($tmp);
			}
			$filename = $tmp . $errorCorrectionLevel . $matrixPointSize . '.png';
			QRcode::png($code_url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);

			$script = '<script type="text/javascript">
				$(function(){
					$("[data-type=\'wxpay\']").on("click",function(){
						var content = $("#wxpay_dialog").html();
						pb({
							id: "scanCode",
							title: "",
							width: 260,
							content: content,
							drag: true,
							foot: false,
							cl_cBtn: false,
							cBtn: false
						});
					});
					function get_wxpay_native_status( id ){
						$.get("wxpay_native_query.php", "id="+id,function( result ){
							if ( result.error == 0 && result.is_paid == 1 ){
								window.location.href = result.url;
							}
						}, "json");
					}
	
				window.setInterval(function(){ get_wxpay_native_status("'. $order['log_id'] .'"); }, 2000); 

				});</script>';
			
			
			return '<a href="javascript:;" class="weizf" data-type="wxpay" style="display: block;"><img src="./images/wxpay-icon.png" alt="微信支付"></a><div style="display: none;" id="wxpay_dialog"><img src="'.$GLOBALS['ecs']->url(). 'images/qrcode/'.basename($filename).'?t='.time().'" style="height: 260px;width: 260px;"/></div>'.$script;
        }
    }

    /**
     * 响应操作
     */
    function respond()
    {
        if($_GET['status'] == 1){
            return true;
        }
        else{
            return false;
        }
    }

    function trimString($value)
    {
        $ret = null;
        if (null != $value)
        {
            $ret = $value;
            if (strlen($ret) == 0)
            {
                $ret = null;
            }
        }
        return $ret;
    }

    /**
     *  作用：产生随机字符串，不长于32位
     */
    public function createNoncestr( $length = 32 )
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     *  作用：设置请求参数
     */
    function setParameter($parameter, $parameterValue)
    {
        $this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    /**
     *  作用：生成签名
     */
    public function getSign($Obj)
    {
        foreach ($Obj as $k => $v)
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
        $String = $String."&key=".$this->payment['wxpay_key'];
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

    /**
     *  作用：以post方式提交xml到对应的接口url
     */
    public function postXmlCurl($xml,$url,$second=30)
    {
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data)
        {
            curl_close($ch);
            return $data;
        }
        else
        {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error"."<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }

    /**
     * 获取结果
     */
    function getResult()
    {
        //设置接口链接
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        try
        {
            //检测必填参数
            if($this->parameters["out_trade_no"] == null){
                throw new Exception("缺少统一支付接口必填参数out_trade_no！"."<br>");
            }elseif($this->parameters["body"] == null){
                throw new Exception("缺少统一支付接口必填参数body！"."<br>");
            }elseif ($this->parameters["total_fee"] == null ) {
                throw new Exception("缺少统一支付接口必填参数total_fee！"."<br>");
            }elseif ($this->parameters["notify_url"] == null) {
                throw new Exception("缺少统一支付接口必填参数notify_url！"."<br>");
            }elseif ($this->parameters["trade_type"] == null) {
                throw new Exception("缺少统一支付接口必填参数trade_type！"."<br>");
            }elseif ($this->parameters["trade_type"] == "JSAPI" && $this->parameters["openid"] == NULL){
                throw new Exception("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！"."<br>");
            }
            $this->parameters["appid"] = $this->payment['wxpay_appid'];//公众账号ID
            $this->parameters["mch_id"] = $this->payment['wxpay_mchid'];//商户号
            $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'];//终端ip
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            $xml = "<xml>";
            foreach ($this->parameters as $key=>$val)
            {
                 if (is_numeric($val))
                 {
                    $xml.="<".$key.">".$val."</".$key.">";

                 }
                 else
                 {
                    $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
                 }
            }
            $xml.="</xml>";
        }catch (Exception $e)
        {
            die($e->getMessage());
        }

        $response = $this->postXmlCurl($xml, $url, 30);
        $result = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }
}