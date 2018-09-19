##api/wx/cart/add   添加购物车商品


####链接
    http://domain/mobile/public/api/wx/cart/add

####头部参数
1. x-ectouch-authorization     参数名
2.    参数值

####参数
1. id:700    // 商品ID
2. num:3     // 商品数量
3. attr_id :  "[721,732]" // 属性值  （可选）


####返回参数
1. code : 0 为正常   **1 为不正常**
2. goods_number  : 3  购物车此商品数量
3. total_number  : 4  购物车商品总数
