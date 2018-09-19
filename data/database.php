<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$db_hosts = explode(':', $db_host);
$db_host_conf = isset($db_hosts[0]) ? $db_hosts[0] : 'localhost';
$db_port_conf = isset($db_hosts[1]) ? $db_hosts[1] : '3306';

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $db_host_conf,
    'port'      => $db_port_conf,
    'database'  => $db_name,
    'username'  => $db_user,
    'password'  => $db_pass,
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => $prefix,
    'strict'    => false,
]);

try {
    // Make this Capsule instance available globally via static methods... (optional)
    $capsule->setAsGlobal();
    // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
    $capsule->bootEloquent();
} catch (Exception $e) {
    exit($e->getMessage());
}
