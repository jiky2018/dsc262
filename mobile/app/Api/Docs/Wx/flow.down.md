##api/wx/flow/down  小程序订单确认页面

####链接
     http://domain/mobile/public/api/wx/flow/down

####参数
1. consignee    收货地址ID
2. shipping  配送方式 数组
    > ru_id  商家ID
    > shipping_id  配送方式ID
3. tax_id    纳税人识别码
4. inv_payee  个人还是公司名称 ，增值发票时此值为空
5. inv_content 发票明细
6. vat_id     增值发票对应的id
7. invoice_type 0普通发票，1增值发票

####头部参数
1. x-ectouch-authorization     参数名
2.    参数值


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  : 18   //订单ID




