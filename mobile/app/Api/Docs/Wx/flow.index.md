##api/wx/flow  小程序订单确认页面

####链接
     http://domain/mobile/public/api/wx/flow

####参数
   1： can_invoice : 0 不能开发票
   2： invoice_content  明细
   3： vat_invoice ：空，当前用户没有增值发票  **数组

####头部参数
1. x-ectouch-authorization     参数名
2.    参数值  token


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  : 数据 （数组）
3. data 下 数据 cart_goods_list(数组)   商品图片
    > 1. list  商家 商品、配送方式列表   （数组）
    >   1. shop_info  商家支持配送方式列表
    >      1. ru_id   商家ID
    >      2. shipping_id   配送方式ID 
    >      3. shipping_name 配送方式名称 
    >      
    >   2. shop_list  商家商品列表
    >      1. goods_attr  商品属性
    >      2. goods_id 商品ID
    >      3. goods_name  商品名称
    >      4. goods_number   商品数量
    >      5. goods_price    商品价格
    >      6. goods_price_formated   格式化商品价格
    >      7. goods_thumb   商品图片
    >      8. market_price   商品市场价格
    >      9. market_price_formated   格式化商品市场价格
    >      10. rec_id   购物车记录ID
    >      11. ru_id   商家ID
    >      12. shop_name   店铺名称
    >      13. user_id     用户ID
    >      
    >   3. total  商家商品总计
    >      1. number   商品总数量
    >      2. price    商品总计
    >      3. price_formated  商品总计格式化
    >      
    > 2. order_total  10615  预订单总计
    > 3. order_total_formated   预订单总计格式化
4. default_address    默认收货地址
    > address: "驱蚊器"   详细地址
    > address_id:16  地址ID
    > city:"龙岩"    城市名
    > consignee:"名"     收货人姓名
    > country:""   国家
    > district:"长汀县"   县级
    > mobile:"13569874412"   手机号码
    > province:"福建"    省
    > user_id:68    用户ID
