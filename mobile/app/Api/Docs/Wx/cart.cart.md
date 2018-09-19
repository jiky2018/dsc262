##api/wx/cart   购物车列表

####链接
    http://domain/mobile/public/api/wx/cart

####参数

####头部参数
1. x-ectouch-authorization     参数名
2.    参数值  token

####返回参数
1. code : 0 为正常   **1 为不正常**
2. data 下 数据 cart_list(数组)   分类
    > 1. rec_id: 858,    //记录ID
    > 2. user_id: "0",    //用户ID
    > 3. goods_id     //商品ID
    > 4. goods_name   //商品名称
    > 5. market_price   //市场价格
    > 6. goods_price    //本店价格
    > 7. goods_number   //商品数量
    > 8. goods_attr  //商品属性
    > 9. goods_attr_id:"5,7"  //商品属性id
    > 10. goods_thumb: "mobile/public/img/no_image.jpg",    //商品图片
    > 11. goods_price_formated： "¥799.00"   //格式化商品价格
    > 12. market_price_formated： "¥958.80"   //格式化市场价格
    > 13. ru_id: 0 商家ID
    > 14. shop_name: '商创自营'  店铺名称

3. data 下 数据 best_goods(数组)   推荐商品
    > 1. "goods_id": 903,     //商品ID
    > 2. "goods_name": "正品直邮Hermes爱马仕2017新款男鞋 时尚真皮休闲鞋H171325ZH02   7495",   //商品名称
    > 3. "shop_price": "798.00",    //商店价格
    > 4. "goods_thumb": "mobile/public/img/no_image.jpg",    //商品图片
    > 5. "market_price": "957.59",    //   市场价格
    > 6. "market_price_formated": "¥958.80"   // 格式化市场价格
    > 7. "shop_price_formated": "¥958.80"   // 格式化商店价格

4. data 下 数据 total_price(字符串)   购物车总价
    > 1. goods_number: "3"      // 购物车商品总数
    > 2. goods_price:"5707"       //商品总计
    > 3. goods_price_formated:"¥5707.00"     //格式化商品总计
    > 4. market_price:"6836.4"          //  市场价
    > 5. market_price_formated:"¥6836.40"     // 格式化市场价格
    > 6. real_goods_count:"2"       // 实体商品 数量 
    > 7. save_rate:"17%"     //   比市场价节省比率
    > 8. saving:"1129.4"       // 比市场价节省价格
    > 9. saving_formated:"¥1129.40"   格式化节省价格
    > 10. virtual_goods_count:"0"    虚拟商品 数量