##api/wx/user/account/detail   用户账户


####链接
    http://10.10.10.145/dsc/mobile/public/api/wx/user/account/detail

####参数
1. page  页数
2. size  每页条数


####头部参数
1. x-ectouch-authorization     参数名
2.    参数值


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  （数组）
    > 1. log_sn    // 操作号
    > 2. money     // 金额
    > 3. time      // 操作时间
