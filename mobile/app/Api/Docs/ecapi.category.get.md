ecapi.category.get 获取一个商品分类
=======================

获取一个商品分类。


Request Parameters
==================

| Parameter name | Type   | Required | Description |
|:---------------|:-------|:---------|:------------|
| id             | int    | **Yes**  | 分类ID      |


Response Elements
=================

| Parameter name | Type   | Required | Description |
|:---------------|:-------|:---------|:------------|
| goods_id       | int    | **Yes**  | 商品ID       |
| goods_sn       | string | **Yes**  | 商品号      |
| goods_name     | string | **Yes**  | 商品名称     |
| url            | string | **Yes**  | 链接     |
| goodsthumb     | string | **Yes**  | 商品图片     |



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
            "goods_id": 738,
            "goods_sn": "ECS000738",
            "goods_name": "香港特产珍妮聪明小熊曲奇饼干640g四味奶油4mix礼盒装进口零食品 香港人气美食 手工四味奶油曲奇",
            "url": "/dsc/mobile/index.php?m=goods&id=738",
            "goodsthumb": "/dsc/mobile/public/img/no_image.jpg"
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

