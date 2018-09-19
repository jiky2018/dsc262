ecapi.category.list 获取分类多维列表
=======================

获取分类多维列表。


Request Parameters
==================

| Parameter name | Type   | Required | Description |
|:---------------|:-------|:---------|:------------|


Response Elements
=================

| Parameter name | Type   | Required | Description |
|:---------------|:-------|:---------|:------------|
| id             | int    | **Yes**  | 分类ID       |
| name           | string | **Yes**  | 分类名       |
| cat_img        | string | **Yes**  | 分类图片      |
| haschild       | int    | **Yes**  | 是否有子分类   |
| cat_id         | array  | **Yes**  | 子分类        |



Request Example
===============

```
<?php
//请使用2017.02.22后更新的SDK 
require_once __DIR__ . '/lib/TokenClient.php';
$token = 'fill access_token';//请填入商家授权后获取的access_token 
$client = new YZTokenClient($token); 
$method = 'ecapi.shop.get';//要调用的api名称 
$methodVersion = '2.0';//要调用的api版本号 
$params = [ ]; 

echo '<pre>'; 
var_dump( $client->post($method, $methodVersion, $params) ); 
echo '</pre>';
```


Response Example
================

```
{
    "code": 0,
    "data": [
        {
            "id": 858,
            "name": "家用电器",
            "cat_img": "/dsc/mobile/public/img/no_image.jpg",
            "haschild": 1,
            "cat_id": [
                {
                    "id": 1105,
                    "name": "大家电",
                    "cat_img": "/dsc/mobile/public/img/no_image.jpg",
                    "haschild": 1,
                    "cat_id": {}
            ]
        }
    ]
}
```


Exception Example
=================

```
{
  "error_response": {
    "code": 50000,
    "msg": "此店铺不存在."
  }
}
```

