##api/wx/user  用户登录接口

####链接
     http://domain/mobile/public/api/wx/user

####参数

####头部参数
1. x-ectouch-authorization     参数名
2.    参数值  token


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data : 数据 （数组）
3. best_goods  推荐商品列表
    > goods_id:903   // 商品ID
    > goods_name:"正品直邮Hermes爱马仕2017新款男鞋 时尚真皮休闲鞋"    // 商品名称
    > goods_thumb:"images/201703/thumb_img/0_thumb_G_1490915806032.jpg"  // 商品图片
    > market_price:"958.80"   市场价格
    > shop_price:"799.00"   本店价格

4. order   订单数量统计
    > all_num   所有订单
    > no_evaluation_num  待评价
    > no_paid_num   代付款
    > no_received_num  待收货

5. userInfo  用户信息   （暂时没用）
    > address_id ： 16   默认收货地址ID
    > birthday:"1000-01-01"   生日
    > frozen_money:"0.00"   冻结资金
    > id:68   用户ID
    > mobile_phone:""   手机号码
    > nick_name:""   用户昵称
    > pay_points:0   使用积分
    > qq:""    
    > rank_points:0   等级积分
    > sex:0   性别
    > user_money:"20.00"   用户资金
    > user_name:"wxmpooftw0j_"    用户名
    > user_picture:"themes/ecmoban_dsc2017/images/avatar.png"    用户头像