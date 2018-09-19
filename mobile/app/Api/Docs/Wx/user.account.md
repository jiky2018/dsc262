##api/wx/user/account   用户账户


####链接
    http://10.10.10.145/dsc/mobile/public/api/wx/user/account

####参数


####头部参数
1. x-ectouch-authorization     参数名
2.    参数值


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  （数组）
    > 1. user_money: "0.00"     //用户余额
    > 2. frozen_money: "0.00"   //冻结余额
    > 3. pay_points: 0     //积分
    > 4. bonus_num: 1    //红包数量
