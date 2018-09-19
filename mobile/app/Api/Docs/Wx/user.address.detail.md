##api/wx/user/address/detail  收货地址详情

####链接
     http://domain/mobile/public/api/wx/user/address/detail

####参数
1. id  收货地址ID


####头部参数
1. x-ectouch-authorization     参数名
2.    参数值

####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  : 数据 （数组）
    > 1. address 收货地址
      > address: "是当前娃儿请问"   // 详细地址
      > city_id:37     // 城市ID
      > consignee:"请问"   // 收货人
      > district_id:410    // 区ID
      > id:31     // 收货地址ID
      > mobile:"13625631259"    // 手机号码
      > province_id:3     // 省ID
    > 2. city   城市列表
      > agency_id: 0
      > parent_id:3
      > region_id:37     // 地区ID
      > region_name:"蚌埠"   // 地区名称
      > region_type:2
    > 3. district  区列表   同上
    > 4. province  省列表   同上
