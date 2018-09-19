<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . '/includes/lib_area.php';
$_REQUEST['id'] = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$_REQUEST['img'] = isset($_REQUEST['img']) ? intval($_REQUEST['img']) : 0;
$sql = 'SELECT goods_name FROM ' . $ecs->table('goods') . 'WHERE goods_id = \'' . $_REQUEST['id'] . '\'';
$goods_name = $db->getOne($sql);

if ($goods_name === false) {
	ecs_header("Location: ./\n");
	exit();
}

$sql = 'SELECT img_id, img_desc, thumb_url, img_url' . ' FROM ' . $ecs->table('goods_gallery') . ' WHERE goods_id = \'' . $_REQUEST['id'] . '\' ORDER BY img_id';
$img_list = $db->getAll($sql);
$img_count = count($img_list);
$gallery = array(
	'goods_name' => htmlspecialchars($goods_name, ENT_QUOTES),
	'list'       => array()
	);

if ($img_count == 0) {
	ecs_header('Location: goods.php?id=' . $_REQUEST['id'] . "\n");
	exit();
}
else {
	foreach ($img_list as $key => $img) {
		$gallery['list'][] = array('gallery_thumb' => get_image_path($_REQUEST['id'], $img_list[$key]['thumb_url'], true, 'gallery'), 'gallery' => get_image_path($_REQUEST['id'], $img_list[$key]['img_url'], false, 'gallery'), 'img_desc' => $img_list[$key]['img_desc']);
	}
}

$smarty->assign('shop_name', $_CFG['shop_name']);
$smarty->assign('watermark', str_replace('../', './', $_CFG['watermark']));
$smarty->assign('gallery', $gallery);
$smarty->display('gallery.dwt');

?>
