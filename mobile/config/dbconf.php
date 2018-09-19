<?php

$config = dirname(dirname(__DIR__)) . '/data/config.php';

try {
    if (file_exists($config)) {
        require($config);
    } else {
        throw new Exception('Database configuration file is not exists.');
    }

    $db_hosts = explode(':', $db_host);
    $db_host = isset($db_hosts[0]) ? $db_hosts[0] : 'localhost';
    $db_port = isset($db_hosts[1]) ? $db_hosts[1] : '3306';

    return [
        'db_type' => 'mysql',
        'db_host' => $db_host, // . ',' . '192.168.1.92',
        'db_user' => $db_user,
        'db_pwd' => $db_pass,
        'db_name' => $db_name,
        'db_prefix' => $prefix,
        'db_port' => $db_port,
        'db_charset' => 'utf8',
        // 分布式数据库配置项
        'DB_DEPLOY_TYPE' => 0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'DB_RW_SEPARATE' => true, // 数据库读写是否分离 主从式有效
        'DB_MASTER_NUM' => 1, // 读写分离后 主服务器数量
        'DEFAULT_TIMEZONE' => $timezone
    ];
} catch (Exception $e) {
    exit('Error Info: ' . $e->getMessage());
}
