##api/wx/user/address/add  添加收货地址

####链接
     http://domain/mobile/public/api/wx/user/address/add

####参数
1. consignee   名字
2. province   省
3. city    城市
4. district   区
5. address    详细地址
6. mobile    手机号码

####头部参数
1. x-ectouch-authorization     参数名
2.    参数值



####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  : 数据 （数组）
    > 1. 数字   收货地址ID
