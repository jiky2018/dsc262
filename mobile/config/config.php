<?php

$dbconf = require __DIR__ . '/dbconf.php';
$deploy = require __DIR__ . '/deploy.php';
$envfile = __DIR__ . '/config-local.php';
$application = require __DIR__ . '/app.php';
$env = is_file($envfile) ? require $envfile : array();
$protocol = (is_ssl() ? 'https' : 'http') . '://';
$port = '';
if (isset($_SERVER['SERVER_PORT'])) {
    $port = ':' . $_SERVER['SERVER_PORT'];
    if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol)) {
        $port = '';
    }
}
$mobile_url = $protocol . $_SERVER['HTTP_HOST'] . $port . dirname($_SERVER["SCRIPT_NAME"]);
$pc_url = empty($deploy['pc_url']) ? dirname($mobile_url) : $deploy['pc_url'];
$static = empty($deploy['static_url']) ? $pc_url : $deploy['static_url'];
$upload_path = empty($deploy['upload_path']) ? dirname(dirname(__DIR__)) . '/' : $deploy['upload_path'];

$config = [
    'url_model' => 0,
    'url_pathinfo_depr' => '/',

    'url_router_on' => true,
    'url_route_rules' => require dirname(__DIR__) . '/routes/web.php',

    'curl_http_version' => CURL_HTTP_VERSION_1_1, // 设置curl的HTTP版本

    'session_auto_start' => false,
    'session_options' => [
        'path' => dirname(__DIR__) . '/storage/sessions'
    ],

    'default_module' => 'index',
    'action_prefix' => 'action',
    'var_pathinfo' => 'r',

    'taglib_begin' => '{',
    'taglib_end' => '}',

    'tmpl_file_depr' => '.',
    'tmpl_parse_string' => [
        '__PC__' => rtrim(str_replace('\\', '/', $pc_url), '/'),
        '__STATIC__' => rtrim(str_replace('\\', '/', $static), '/'),
        '__PUBLIC__' => rtrim(__ROOT__, '/public/notify') . '/public',
        '__TPL__' => rtrim(__ROOT__, '/public/notify') . '/public'
    ],

    'upload_path' => $upload_path,

    'assets' => require __DIR__ . '/assets.php',

    'tmpl_action_error' => dirname(__DIR__) . '/resources/views/vendor/message.html', // 默认错误跳转对应的模板文件
    'tmpl_action_success' => dirname(__DIR__) . '/resources/views/vendor/message.html', // 默认成功跳转对应的模板文件
    'tmpl_exception_file' => dirname(__DIR__) . '/resources/views/errors/exception.html',// 异常页面的模板文件

    'check_app_dir' => false,

//    'DATA_CACHE_TYPE' => 'Memcached',
//    'MEMCACHED_SERVER' => [['xxx.memcache.rds.aliyuncs.com', 11211]],
//    'MEMCACHED_LIB' => [\Memcached::OPT_COMPRESSION => false, \Memcached::OPT_BINARY_PROTOCOL => true],

];

return array_merge($application, $config, $dbconf, $deploy, $env);
