##api/wx/user/address/update  编辑收货地址

####链接
     http://domain/mobile/public/api/wx/user/address/update

####参数
1. consignee   名字
2. country    国家
3. province   省
4. city    城市
5. district   区
6. address    地址
7. mobile    手机号码
8. id  收货地址ID


####头部参数
1. x-ectouch-authorization     参数名
2.    参数值


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  : 数据 （数组）
    > 1. 数字   删除成功
