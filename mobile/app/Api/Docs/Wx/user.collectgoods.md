##api/wx/user/collectgoods   我的收藏


####链接
    http://domain/mobile/public/api/wx/user/collectgoods

####参数
1. page  页数
2. size  每页条数


####头部参数
1. x-ectouch-authorization     参数名
2.    参数值


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  （数组）
    > 1. goods_name     // 操作号
    > 2. shop_price     // 金额
    > 3. goods_thumb    // 操作时间
    > 4. goods_stock    // 商品库存
    > 5. time           // 收藏时间
    > 6. goods_id       // 商品ID
