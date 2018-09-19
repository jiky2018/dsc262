ecapi.shop.get 获取店铺基础信息
=======================

获取店铺基础信息。


Request Parameters
==================

| Parameter name | Type   | Required | Description |
|:---------------|:-------|:---------|:------------|
| id             | int    | **Yes**  | 店铺ID      |


Response Elements
=================

| Parameter name | Type   | Required | Description |
|:---------------|:-------|:---------|:------------|
| ru_id          | int    | **Yes**  | 店铺ID       |
| shop_name      | string | **Yes**  | 店铺名称      |
| shop_logo      | string | **Yes**  | 店铺logo     |
| brandName      | string | **Yes**  | 品牌名称     |



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
            "ru_id": 1,
            "shop_name": "万卓有限公司",
            "shop_logo": "../seller_imgs/seller_logo/seller_logo1.jpg",
            "shoprz_brandName": "万卓",
            "rz_shopName": "万卓旗舰店"
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

