<?php

return [

    'name' => 'ECTouch',
    'wxapp_on' => true, // 是否支持微信小程序 true 支持， false 不支持

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => md5(__DIR__),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'zh-cn',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en-us',

    // 商城URL
    'SHOP_URL' => 'http://shop.ectouch.cn/b2c/',

    // 微信小程序
    'WX_MINI_APPID' => '',
    'WX_MINI_SECRET' => '',
    // 微信小程序支付 配置
    'WX_MCH_ID' => '',
    'WX_MCH_KEY' => '',

    // 注册协议地址
    'TERMS_URL' => 'http://localhost/article.php?cat_id=-1',
    'ABOUT_URL' => 'http://localhost/article.php?cat_id=-2',

    // Token授权加密key
    'TOKEN_SECRET' => '', // 重要！！！
    'TOKEN_ALG' => 'HS256',
    'TOKEN_TTL' => '43200',
    'TOKEN_REFRESH' => false,
    'TOKEN_REFRESH_TTL' => '1440',
    'TOKEN_VER' => '1.0.0',

    // 短信验证信息模版
    'SMS_TEMPLATE' => '#CODE#，短信验证码有效期30分钟，请尽快进行验证。'
];
