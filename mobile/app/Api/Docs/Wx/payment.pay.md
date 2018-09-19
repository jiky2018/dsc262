##api/wx/payment/pay  支付接口

####链接
     http://domain/mobile/public/api/wx/payment/pay

####参数
    > 参数   ： id  订单ID
    > 参数   ： open_id  小程序openID
    > 参数   ： code  订单支付（order.pay）  或者  资金充值

####头部参数
1. x-ectouch-authorization     参数名
2.    参数值


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  : 数据 （数组）
  > wxpay   以下为微信小程序支付回调参数（ 使用以下参数调起支付 ）
    > 1. appid: "wx8d05aead679423f1"
    > 2. mch_id:"1409532202" 
    > 3. nonce_str:"ibuaiVcKdpRxkhJA"
    > 4. packages:"prepay_id=wx201708171625290e15536b910295356996"
    > 5. prepay_id:"wx201708171625290e15536b910295356996"
    > 6. sign:"749FE773B991954057DC811971805CD7"
    > 7. timestamp:"1502929446"
