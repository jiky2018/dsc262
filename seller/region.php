<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
define('INIT_NO_USERS', true);
define('INIT_NO_SMARTY', true);
require dirname(__FILE__) . '/includes/init.php';
require ROOT_PATH . 'includes/cls_json.php';
header('Content-type: text/html; charset=' . EC_CHARSET);
$data = array('content' => '', 'region_name' => '');
$type = (!empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 0);
$parent = (!empty($_REQUEST['parent']) ? intval($_REQUEST['parent']) : 0);
$shipping = (!empty($_REQUEST['shipping']) ? intval($_REQUEST['shipping']) : 0);
$region = get_regions($type, $parent);
$value = '';
$type = $type + 1;

foreach ($region as $k => $v) {
	if (0 < $v['region_id']) {
		if ($shipping == 1) {
			$value .= '<div class="region_item"><input type="checkbox" name="region_name" data-region="' . $v['region_name'] . '" value="' . $v['region_id'] . '" class="ui-checkbox" id="region_' . $v['region_id'] . '" /><label for="region_' . $v['region_id'] . '" class="ui-label">' . $v['region_name'] . '</label></div>';
		}
		else {
			$value .= '<span class="liv" data-text="' . $v['region_name'] . '" data-type="' . $type . '"  data-value="' . $v['region_id'] . '">' . $v['region_name'] . '</span>';
		}
	}
}

if (0 < $parent) {
	$sql = 'SELECT region_name FROM' . $ecs->table('region') . ' WHERE region_id = \'' . $parent . '\'';
	$data['region_name'] = $db->getOne($sql);
}

$json = new JSON();
$data['content'] = $value;
echo $json->encode($data);

?>
