##api/wx/user/order/appraise/detail  待评价详情

####链接
     http://domain/mobile/public/api/wx/user/order/appraise/detail

####参数
1. oid   1 订单ID
2. gid   10  商品ID

####头部参数
1. x-ectouch-authorization     参数名
2.    参数值


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  : 数据 （数组）
    > order_id":  订单ID,
    > goods_id":  商品ID,
    > goods_name": "商品名称",
    > goods_attr": "商品属性",
    > goods_price": "商品价格",
    > goods_thumb": "商品图片",

