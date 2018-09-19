<?php

define('IN_ECS', true);

/* 取得当前ecshop所在的根目录 */
define('ROOT_PATH', str_replace('data/kuaidi_key.php', '', str_replace('\\', '/', __FILE__)));

$shop_config = ROOT_PATH . "temp/static_caches/shop_config.php";

$key = 'a630e858517211a5';
if(file_exists($shop_config)){
    include_once($shop_config);
    if ($data !== false){
        $key = $data['kuaidi100_key'];
    }
}

$kuaidi100key = $key;
?>