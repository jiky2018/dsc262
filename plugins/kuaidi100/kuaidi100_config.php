<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once '../../data/kuaidi_key.php';

switch ($getcom) {
case 'EMS':
	$postcom = 'ems';
	break;

case '中国邮政':
	$postcom = 'ems';
	break;

case '申通快递':
	$postcom = 'shentong';
	break;

case '圆通速递':
	$postcom = 'yuantong';
	break;

case '顺丰速运':
	$postcom = 'shunfeng';
	break;

case '天天快递':
	$postcom = 'tiantian';
	break;

case '韵达快递':
	$postcom = 'yunda';
	break;

case '中通速递':
	$postcom = 'zhongtong';
	break;

case '龙邦物流':
	$postcom = 'longbanwuliu';
	break;

case '宅急送':
	$postcom = 'zhaijisong';
	break;

case '全一快递':
	$postcom = 'quanyikuaidi';
	break;

case '汇通速递':
	$postcom = 'huitongkuaidi';
	break;

case '民航快递':
	$postcom = 'minghangkuaidi';
	break;

case '亚风速递':
	$postcom = 'yafengsudi';
	break;

case '快捷速递':
	$postcom = 'kuaijiesudi';
	break;

case '华宇物流':
	$postcom = 'tiandihuayu';
	break;

case '中铁快运':
	$postcom = 'zhongtiewuliu';
	break;

case '全峰快递':
	$postcom = 'quanfeng';
	break;

case 'FedEx':
	$postcom = 'fedex';
	break;

case 'UPS':
	$postcom = 'ups';
	break;

case 'DHL':
	$postcom = 'dhl';
	break;

default:
	$postcom = '';
}

?>
