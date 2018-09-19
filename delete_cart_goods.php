<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
include_once 'includes/cls_json.php';

if (!empty($_SESSION['user_id'])) {
	$sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
	$c_sess = ' c.user_id = \'' . $_SESSION['user_id'] . '\' ';
}
else {
	$sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
	$c_sess = ' c.session_id = \'' . real_cart_mac_ip() . '\' ';
}

$result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '', 'index' => -1);
$result['index'] = !empty($_POST['index']) ? intval($_POST['index']) : 0;
$rec_id = !empty($_POST['id']) ? intval($_POST['id']) : 0;
$json = new JSON();
$sql = 'DELETE FROM ' . $GLOBALS['ecs']->table('cart') . (' WHERE rec_id = \'' . $rec_id . '\'');
$GLOBALS['db']->query($sql);
$sql = 'SELECT c.*,g.goods_thumb,g.goods_id,c.goods_number,c.goods_price, c.extension_code ' . ' FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id=c.goods_id ' . ' WHERE ' . $c_sess . ' AND rec_type = \'' . CART_GENERAL_GOODS . '\'';
$row = $GLOBALS['db']->GetAll($sql);
$arr = array();

foreach ($row as $k => $v) {
	$arr[$k]['goods_thumb'] = get_image_path($v['goods_id'], $v['goods_thumb'], true);
	$arr[$k]['short_name'] = 0 < $GLOBALS['_CFG']['goods_name_length'] ? sub_str($v['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $v['goods_name'];
	$arr[$k]['url'] = build_uri('goods', array('gid' => $v['goods_id']), $v['goods_name']);
	$arr[$k]['goods_number'] = $v['goods_number'];
	$arr[$k]['goods_name'] = $v['goods_name'];
	$arr[$k]['goods_price'] = price_format($v['goods_price']);
	$arr[$k]['warehouse_id'] = $v['warehouse_id'];
	$arr[$k]['area_id'] = $v['area_id'];
	$arr[$k]['rec_id'] = $v['rec_id'];
	$arr[$k]['extension_code'] = $v['extension_code'];
	$properties = get_goods_properties($v['goods_id'], $v['warehouse_id'], $v['area_id'], $v['area_city'], $v['goods_attr_id'], 1);

	if ($properties['spe']) {
		$arr[$k]['spe'] = array_values($properties['spe']);
	}
	else {
		$arr[$k]['spe'] = array();
	}
}

$sql = 'SELECT SUM(goods_number) AS number, SUM(goods_price * goods_number) AS amount' . ' FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE ' . $sess_id . ' AND rec_type = \'' . CART_GENERAL_GOODS . '\'';
$row = $GLOBALS['db']->GetRow($sql);

if ($row) {
	$number = intval($row['number']);
	$amount = floatval($row['amount']);
}
else {
	$number = 0;
	$amount = 0;
}

$result['cart_num'] = $number;
$GLOBALS['smarty']->assign('str', sprintf($GLOBALS['_LANG']['cart_info'], $number, price_format($amount, false)));
$GLOBALS['smarty']->assign('goods', $arr);
$cart_info = array('goods_list' => $arr, 'number' => $number, 'amount' => price_format($amount, false));
$GLOBALS['smarty']->assign('cart_info', $cart_info);
$result['content'] = $GLOBALS['smarty']->fetch('library/cart_info.lbi');
$result['cart_content'] = $GLOBALS['smarty']->fetch('library/cart_menu_info.lbi');
exit($json->encode($result));

?>
