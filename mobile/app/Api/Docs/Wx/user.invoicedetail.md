##api/wx/user/invoice/detail  用户增值发票详情

####链接
     http://10.10.10.145/dsc/mobile/public/api/wx/user/invoice/detail


#### 参数
参数由  微信获取

####头部参数
1. x-ectouch-authorization     参数名



####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  : 数据 （数组）
    > 1. id: 1    //ID
    > 2. user_id: ""   //会员id
    > 3. company_name: "名"    // 单位名称
    > 4. tax_id: ""    // 纳税人识别码
    > 5. company_address: // 注册地址
    > 6. company_telephone:  // 注册电话
    > 7. bank_of_deposit: "中国",     //开会行
    > 8. bank_account: "北京"     //银行账户
    > 9. consignee_name: "北京"         //收票人名字
    > 10. consignee_mobile_phone: "东城区"  // 收票人电话
    > 11. consignee_provice: ""       //收票人省份
    > 12. consignee_address: ""       //收票人地址
    > 13. audit_status: "" 0未审核，1，已审核 2，审核未通过      //审核状态
    > 14. add_time: ""       //添加时间

