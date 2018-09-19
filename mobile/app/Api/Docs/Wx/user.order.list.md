##api/wx/user/order/list  订单列表

####链接
     http://domain/mobile/public/api/wx/user/order/list

####参数
1. page   1 页数
2. size   10  每页记录数
3. status   2  订单类型   （0 待付款 1 已付款   3 已收货 待评价）

####头部参数
1. x-ectouch-authorization     参数名
2.    参数值


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  : 数据 （数组） 
    > 1. order_id: 10    // 订单ID
    > 2. order_sn: "2017070430366"     //订单号
    > 3. order_status: "已确认"         //订单状态
    > 4. shipping_status: "未发货"      // 发货状态
    > 5. pay_status: "已付款"           // 支付状态
    > 6. goods_amount: "0.00"          // 商品价格
    > 7. order_amount: "0.00"          // 订单价格
    > 8. add_time: "2017-07-04 16:08"  //添加时间
    > 9. money_paid: "0.00"            // 支付金额
    > 10. total_number: 0             //总数量
    > 11. total_amount: "¥0.00"       // 总金额
    > 12. goods_amount_formated       // 商品价格格式化
    > 13. money_paid_formated         // 支付金额格式化
    > 14. shipping_fee                // 运费
    > 15. shipping_fee_formated       // 运费格式化
    > 16. shop_name                   // 店铺名称
    > 17. total_amount                // 总价
    > 18. total_amount_formated       // 总价格式化
    > 19. total_number                // 商品总数量
    > 20. goods    商品属性
        > goods_attr   商品属性名称
        > goods_id   商品ID
        > goods_name  商品名称
        > goods_number   商品数量
        > goods_price     商品价格
        > goods_price_formated   商品价格格式化
        > goods_thumb    商品图片
        > order_id     订单ID

