<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
require 'include.php';
$mq = $c->notify();
$i = 0;

while (1) {
	$mq->pub('order.new', 'message hello world: ' . $i++);
	echo 'send ' . $i . " \n";
}

?>
