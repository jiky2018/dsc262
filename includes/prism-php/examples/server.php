<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class api_handler
{
	public function get_id_by_domain($params)
	{
		return $_SERVER;
	}
}

require_once '../lib/provider.php';
$provider = new prism_provider();
$provider->add('get_id_by_domain', prism_api('id', prism_params('arg1', 'useinput'), prism_params('arg2', 'useinput'), prism_params('arg3', 'useinput'), prism_params('arg4', 'useinput')));
$provider->set_url($_SERVER['DOCUMENT_URI']);
$provider->handler(new api_handler());

if (array_key_exists('show_api_json', $_GET)) {
	$provider->output_json();
}
else if (array_key_exists('method', $_REQUEST)) {
	$provider->dispatch($_REQUEST['method']);
}
else {
	echo '        <a href="?show_api_json">json</a>';
}

?>
