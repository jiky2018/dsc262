<?php
$site = '';
$cache_config = array(
    'type' => 'memcached',
    'memcached' => array(
        'servers' => array(array($site, 11211)),
        'options' => array(Memcached::OPT_COMPRESSION => false, Memcached::OPT_BINARY_PROTOCOL => true)
    ),
    'file' => array()
);
?>