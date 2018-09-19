##api/wx/flow/shipping  小程序订单确认页面  运费计算

####链接
     http://domain/mobile/public/api/wx/flow/shipping

####参数
1. address  收货地址
2. id  配送方式ID
3. ru_id  商家ID

####头部参数
1. x-ectouch-authorization     参数名
2.    参数值  token


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  : 数据 （数组） cart_goods_list(数组)   商品图片
    > 1. fee  运费价格
    > 2. fee_formated  格式化运费价格