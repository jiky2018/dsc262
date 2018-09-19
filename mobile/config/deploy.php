<?php

/**
 * 独立二级域名部署配置
 * 如启用类似 m.dscmall.cn 的域名访问，启用静态资源URL地址配置。
 * 注意：由于需上传静态资源文件，所以 mobile 目录需要与PC端文件部署同一服务器
 */

return [

    // 设置URL链接地址
    'pc_url' => '', // 如；http://www.dscmall.cn（PC站点的URL地址）

    // 设置资源URL链接地址
    'static_url' => '', // 如；http://static.dscmall.cn（PC站点的资源URL地址）

    // 设置PC目录绝对路径
    'upload_path' => dirname(dirname(__DIR__)) . '/', // 如：/mnt/www（PC站点所在文件目录）

];
