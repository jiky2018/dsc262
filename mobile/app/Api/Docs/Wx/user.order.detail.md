##api/wx/user/order/detail  订单列表

####链接
     http://domain/mobile/public/api/wx/user/order/detail

####参数
1. id   订单ID

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
    > 12. address                     // 收货地址
    > 13. consignee                    // 收货人姓名
    > 14. goods_amount_formated        // 商品价格格式化
    > 15. mobile                       // 手机号码
    > 16. order_amount_formated        // 订单金额格式化
    > 17. pay_fee                      // 支付费用
    > 18. pay_fee_formated             // 支付费用格式化
    > 19. pay_name                     // 支付方式名称
    > 20. pay_time                     // 支付时间
    > 21. shipping_fee                 // 运费
    > 22. shipping_fee_formated        // 格式化运费
    > 23. shipping_id                  // 配送方式ID
    > 24. shipping_name                // 配送方式名称
    > 25. total_amount_formated        // 总金额格式化
    > 26. goods    订单商品列表
        > goods_id: 693   // 商品ID
        > goods_name:"55吋液晶电视机4k曲面平板电视智能网络"   // 商品名称
        > goods_number:2                         // 商品数量
        > goods_price:"4109.00"                  // 商品价格
        > goods_price_formated:"¥4109.00"        // 商品价格格式化
        > goods_sn:"ECS000693"                   // 商品货号
        > goods_thumb:"/thumb_img/0_thumb_G_1490147169173.jpg"   // 商品图片
        > shop_name:"万卓旗舰店"         // 店铺名称

