<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n\n    <head>\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n        <title>报价单打印格式</title>\n    </head>\n\n    <body>\n\n        <table style=\"text-align: center; width: 100%\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n            <tr>\n                <td style=\"font-size: 26px; font-weight: bold; height: 40px;\">\n                    ";

if (!empty($shop_info['shop_logo'])) {
	echo '                        <img src="';
	echo str_replace(array('../'), '', $shop_info['shop_logo']);
	echo "\" alt=\"Logo\" width=\"134\" />\n                    ";
}

echo '                    ';
echo $shop_info['shop_name'];
echo "\n                </td>\n            </tr>\n            <tr>\n                <td style=\"font-size: 12px; height: 20px;\">地址：";
echo $shop_info['shop_address'];
echo "</td>\n            </tr>\n            <tr>\n                <td style=\"font-size: 12px; height: 20px;\">\n                    传真:";
echo $shop_info['kf_tel'] . '&nbsp;&nbsp;';
echo " \n                    ";

if (!empty($shop_info['kf_qq'])) {
	$kf_qq = array_filter(preg_split('/\\s+/', $shop_info['kf_qq']));
	$kf_qq = explode('|', $kf_qq[0]);

	if (!empty($kf_qq[1])) {
		$kf_qq_one = $kf_qq[1];
	}
	else {
		$kf_qq_one = '';
	}

	echo 'QQ客服：' . $kf_qq_one . '&nbsp;&nbsp;';
}

if (!empty($shop_info['kf_ww'])) {
	$kf_ww = array_filter(preg_split('/\\s+/', $shop_info['kf_ww']));
	$kf_ww = explode('|', $kf_ww[0]);

	if (!empty($kf_ww[1])) {
		$kf_ww_one = $kf_ww[1];
	}
	else {
		$kf_ww_one = '';
	}

	echo '淘宝客服：' . $kf_ww_one;
}

echo "                </td>\n            </tr>\n            <tr>\n                <td style=\"font-size: 12px; height: 20px;\">网址：";
echo $ecs->url();
echo "</td>\n            </tr>\n            <tr>\n                <td style=\"font-size: 12px; height: 20px;\"></td>\n            </tr>\n            <tr>\n                <td style=\"font-size: 26px; font-weight: bold; height: 40px;\">订单</td>\n            </tr>\n        </table>\n\n        <!--ecmoban模板堂 --zhuo start-->\n        <table style=\"margin: auto; width: 980px; font-family: 宋体;\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n            <tr>\n                <td style=\"width:200px; font-size: 12px;\">\n                    <table style=\"width: 100%\" cellspacing=\"0\" cellpadding=\"0\">\n                        <tr>\n                            <td style=\"height:20px\">收货人姓名： ";
echo $consignee['consignee'];
echo "</td>\n                            <td style=\"height:20px\">&nbsp;</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">&nbsp;收货地址： ";
echo $consignee['address'];
echo "</td>\n                            <td style=\"height:20px\">&nbsp;</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">电&nbsp;&nbsp;话：";
echo $consignee['tel'];
echo "</td>\n                            <td style=\"height:20px\">&nbsp;</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">传&nbsp;&nbsp;真：";
echo $consignee['sign_building'];
echo "</td>\n                            <td style=\"height:20px\">&nbsp;</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">手&nbsp;&nbsp;机：";
echo $consignee['mobile'];
echo "</td>\n                            <td style=\"height:20px\">&nbsp;</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">电子邮件：";
echo $consignee['email'];
echo "</td>\n                            <td style=\"height:20px\">&nbsp;</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">期望交货期：";
echo $consignee['best_time'];
echo "</td>\n                            <td style=\"height:20px\">&nbsp;</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:10px\"></td>\n                            <td style=\"height:10px\"></td>\n                        </tr>\n                    </table>\n                </td>\n                <td style=\"width: 600px; \"></td>\n                <td style=\"width: 150px; font-size: 12px;\">\n                    <table style=\"width: 100%\" cellspacing=\"0\" cellpadding=\"0\">\n                        <tr>\n                            <td style=\"height:20px\">订单号: </td>\n                            <td style=\"height:20px\">";
echo $order_info['order_sn'];
echo "</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">下单时间: </td>\n                            <td style=\"height:20px\">";
echo $order_info['add_time'];
echo "</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">手&nbsp;&nbsp;机: </td>\n                            <td style=\"height:20px\">13811221526</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">币&nbsp;&nbsp;别:</td>\n                            <td style=\"height:20px\">RMB</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\"></td>\n                            <td style=\"height:20px\"></td>\n                        </tr>\n\n                    </table>\n                </td>\n            </tr>\n        </table>\n        <!--ecmoban模板堂 --zhuo end-->\n\n        <div style=\"height:10px; overflow:hidden;\"></div>\n        <div style=\"height:10px; overflow:hidden;\"></div>\n\n\n        <table style=\"border: 1px solid #000000; text-align: center; background: #FFFFFF; width: 980px; margin: auto\" cellspacing=\"0\" cellpadding=\"0\">\n            <tr>\n                <td style=\"height:30px; width: 39px; font-size: 12px; font-weight: bold; text-align: center; border-bottom: 1px solid #000000;border-right: 1px solid #000000;\">序号</td>\n                <td style=\"height:30px; width: 149px; font-size: 12px; font-weight: bold; text-align: center; border-bottom: 1px solid #000000;border-right: 1px solid #000000;\">订货号</td>\n                <td style=\"height:30px; width: 359px; font-size: 12px; font-weight: bold; text-align: center; border-bottom: 1px solid #000000;border-right: 1px solid #000000;\">产品名称</td>\n                <td style=\"height:30px; width: 169px; font-size: 12px; font-weight: bold; text-align: center; border-bottom: 1px solid #000000;border-right: 1px solid #000000;\">产品属性</td>\n                <td style=\"height:30px; width: 79px; font-size: 12px; font-weight: bold; text-align: center; border-bottom: 1px solid #000000;border-right: 1px solid #000000;\">数量</td>\n                <td style=\"height:30px; width: 79px; font-size: 12px; font-weight: bold; text-align: center; border-bottom: 1px solid #000000;border-right: 1px solid #000000;\">单价</td>\n                <td style=\"height:30px; width: 100px; font-size: 12px; font-weight: bold; text-align: center; border-bottom-style: solid; border-bottom-color: #000000; border-bottom-width: 1px;\">金额</td>\n            </tr>\n            ";

foreach ($order_goods as $k => $v) {
	echo "                <tr>\n                    <td style=\"height:30px; width: 39px; font-size: 12px; text-align: center; border-bottom: 1px solid #000000;border-right: 1px solid #000000;\">";
	echo $k + 1;
	echo "</td>\n                    <td style=\"height:30px; width: 149px; font-size: 12px; text-align: center; border-bottom: 1px solid #000000;border-right: 1px solid #000000;\">";
	echo $v['goods_sn'];
	echo "</td>\n                    <td style=\"height:30px; width: 359px; font-size: 12px; text-align: center; border-bottom: 1px solid #000000;border-right: 1px solid #000000;\">";
	echo $v['goods_name'];
	echo "</td>\n                    <td style=\"height:30px; width: 169px; font-size: 12px; text-align: center; border-bottom: 1px solid #000000;border-right: 1px solid #000000;\">";
	echo $v['goods_attr'];
	echo "</td>\n                    <td style=\"height:30px; width: 79px; font-size: 12px; text-align: center; border-bottom: 1px solid #000000;border-right: 1px solid #000000;\">";
	echo $v['goods_number'];
	echo "</td>\n                    <td style=\"height:30px; width: 79px; font-size: 12px; text-align: center; border-bottom: 1px solid #000000;border-right: 1px solid #000000;\">";
	echo $v['formated_goods_price'];
	echo "</td>\n                    <td style=\"height:30px; width: 100px; font-size: 12px; text-align: center; border-bottom-style: solid; border-bottom-color: #000000; border-bottom-width: 1px;\">\n                        ";
	echo $v['formated_subtotal'];
	echo "                        <br/>\n                        ";

	if (0 < $v['dis_amount']) {
		echo '<font style=\'color:#F00\'>（优惠：' . $v['discount_amount'] . '）</font>';
	}

	echo "                    </td>\n                </tr>\n            ";
}

echo "            <tr>\n                <td style=\"border-bottom: 1px solid #000000; height:30px; font-size: 12px; text-align: left; \" colspan=\"7\">&nbsp;留言：</td>\n            </tr>\n        </table>\n\n\n\n        <table style=\"border: 1px solid #000000; text-align: left; background: #FFFFFF; width: 1014px; margin: auto; font-size: 12px;\" cellspacing=\"0\" cellpadding=\"0\">\n            <tr>\n                <td style=\"width:70%\">\n\n                    <table style=\"width: 100%\" cellspacing=\"0\" cellpadding=\"0\">\n                        <tr>\n                            <td align=\"center\" style=\"height:25px; font-size: 12px; font-weight: bold;\" colspan=\"3\">条款说明</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px; width: 100px; text-align:right\">付款方式：</td>\n                            <td style=\"height:20px\" colspan=\"2\">";
echo $order_info['pay_name'];
echo "</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px; width: 100px; text-align:right\">配送方式：</td>\n                            <td style=\"height:20px\" colspan=\"2\">";
echo $order_info['shipping_name'];
echo "</td>\n                        </tr>\n                        <tr>\n                            <td colspan=\"3\">&nbsp;</td>\n                        </tr>\n                    </table>\n\n                </td>\n                <td style=\"width:30%; border-left: 1px solid #000000\">\n                    <table style=\"width: 100%\" cellspacing=\"0\" cellpadding=\"0\">\n                        <tr>\n                            <td style=\"height:20px\">&nbsp;</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">&nbsp;</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">&nbsp;总金额: ";
echo $order_info['order_amount'];
echo "（RMB）</td>\n                        </tr>\n                        <tr>\n                            <td style=\"color:#F00\">&nbsp;包含：</td>\n                        </tr>\n                        <tr>\n                            <td style=\"color:#F00\">&nbsp;运费: ";
echo $order_info['formated_shipping_fee'];
echo "（RMB）</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">&nbsp;</td>\n                        </tr>\n                    </table>\n\n\n                </td>\n            </tr>\n        </table>\n\n\n        <table style=\"border: 1px solid #000000; text-align: left; background: #FFFFFF; ; margin: auto; font-size: 12px; width:1014px;\" cellspacing=\"0\" cellpadding=\"0\">\n            <tr>\n                <td style=\"width: 30px\">\t\t\t\n                </td>\n\n                <td style=\"border-right: 1px solid #000000; width: 470px\">\n                    <table style=\"width: 470px\" cellspacing=\"0\" cellpadding=\"0\">\n                        <tr>\n                            <td style=\"height:20px\">&nbsp;</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">&nbsp;客户确认:</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">&nbsp;";
echo $consignee['consignee'];
echo "</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">&nbsp;</td>\n                        </tr>\n                    </table>\n                </td>\n\n                <td style=\"width: 500px\">\n                    <table style=\"width: 500px\" cellspacing=\"0\" cellpadding=\"0\">\n                        <tr>\n                            <td style=\"height:20px\">&nbsp;</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">&nbsp;公司确认:</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px; font-size: 26px;\">&nbsp;";
echo $shop_info['shop_name'];
echo "</td>\n                        </tr>\n                        <tr>\n                            <td style=\"height:20px\">&nbsp;</td>\n                        </tr>\n                    </table>\n                </td>\n            </tr>\n        </table>\n    </body>\n\n</html>\n";

?>
