<?php

return [
    'secret' => '123456aA', // (import) token key !
    'alg' => 'HS256', // 加密类型
    'iss' => '', // 签发者
    'aud' => '', // jwt所面向的用户
    'exp' => 3600 * 24 * 30, // 有效期一个月
];
