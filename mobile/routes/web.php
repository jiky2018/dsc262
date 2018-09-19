<?php

return [
    // activity
    'activity/:id\d' => 'activity/index/detail',
    '/^activity$/' => 'activity/index/index',

    // article
    'article/:id\d' => 'article/index/detail',
    '/^article$/' => 'article/index/index',

    // auction
    'auction/:id\d' => 'auction/index/detail',
    '/^auction$/' => 'auction/index/index',

    // brand
    'brand/all' => 'brand/index/nav',
    'brand/:id\d' => 'brand/index/detail',
    '/^brand$/' => 'brand/index/index',

    // cart
    '/^cart$/' => 'cart/index/index',

    // category
    'category/:id\d' => 'category/index/products',
    '/^search$/' => 'category/index/search',
    '/^category$/' => 'category/index/index',

    // community
    'circle/me' => 'community/index/my',
    'circle/publish' => 'community/post/index',
    'circle/:type\d' => 'community/index/list',
    '/^circle$/' => 'community/index/index',

    // coupont

    // crowd funding
    '/^crowd_funding$/' => 'crowd_funding/index/index',

    // drp

    // exchange
    'exchange/:id\d' => 'exchange/index/detail',
    '/^exchange$/' => 'exchange/index/index',

    // flow
    '/^checkout$/' => 'flow/index/index',
    '/^done$/' => 'flow/index/done',

    // goods
    'goods/:id\d' => 'goods/index/index',
    'goods/info/:id\d' => 'goods/index/info',
    'goods/comment/:id\d' => 'goods/index/comment',

    // groupbuy
    'groupbuy/:id\d' => 'groupbuy/index/detail',
    '/^groupbuy$/' => 'groupbuy/index/index',

    // location
    '/^location$/' => 'location/index/index',

    // oauth
    'oauth/bindregister' => 'oauth/index/bindregister',
    '/^oauth$/' => 'oauth/index/index',

    // offline store

    // package
    '/^package$/' => 'package/index/index',

    // presale

    // respond

    // store
    'store/:id\d' => 'store/index/shop_info',
    'store/detail/:ru_id\d' => 'store/index/shop_about',
    'store/goods/:ru_id\d' => 'store/index/pro_list',
    '/^store$/' => 'store/index/index',

    // topic
    'topic/:topic_id\d' => 'topic/index/detail',
    '/^topic$/' => 'topic/index/index',

    // user
    '/^user\/login$/' => 'user/login/index',
    '/^user\/logout$/' => 'user/login/logout',
    '/^user\/register$/' => 'user/login/register',
    '/^user\/forget$/' => 'user/login/get_password',

    'user/order/:order_id\d' => 'user/order/detail',

    // wholesale

    // more
    'more' => 'site/index/more',

    // respond
    'respond/:code' => 'respond/index/index',

    // notify
    'notify/:code' => 'respond/index/notify',
];