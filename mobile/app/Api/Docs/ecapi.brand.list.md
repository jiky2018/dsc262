ecapi.brand.list 获取品牌列表
=======================

获取品牌列表。


Request Parameters
==================

| Parameter name | Type   | Required | Description |
|:---------------|:-------|:---------|:------------|


Response Elements
=================

| Parameter name | Type   | Required | Description |
|:---------------|:-------|:---------|:------------|
| top             | int    | **Yes**  | 头部        |
| center          | int    | **Yes**  | 中间        |
| list2           | int    | **Yes**  | 中间        |
| brand_id        | int    | **Yes**  | 品牌ID      |
| brand_name      | string | **Yes**  | 品牌名称     |
| brand_logo      | string | **Yes**  | 品牌logo    |
| goods_num       | int    | **Yes**  | 品牌下商品数量 |
| brand_desc      | string | **Yes**  | 品牌描述     |



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
    "data": {
        "top": {
            "204": {
                "brand_id": 204,
                "brand_name": "金士顿",
                "brand_logo": "/dsc/mobile/public/img/no_image.jpg",
                "goods_num": 1,
                "brand_desc": ""
            }
        },
        "center": {
            "93": {
                "brand_id": 93,
                "brand_name": "同庆和堂",
                "brand_logo": "/dsc/mobile/public/img/no_image.jpg",
                "goods_num": 5,
                "brand_desc": ""
            }
        },
        "list2": {
            "71": {
                "brand_id": 71,
                "brand_name": "esprit",
                "brand_logo": "/dsc/mobile/public/img/no_image.jpg",
                "goods_num": 1,
                "brand_desc": ""
            }
        }
    }
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

