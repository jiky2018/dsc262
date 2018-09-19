## api/wx/index  小程序首页接口


####链接
     http://domain/dsc/mobile/?app=api

####参数

####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  : 数据 （数组）
3. data 下 数据 banner(数组)   banner
    > 1. pic : 'http://1.jpg'   //banner图片
    > 2. banner_id ： 1      //banner 的 ID

4. data 下 数据 adsense(数组)   广告位
    > 1. pic  : 'http://1.jpg'   // 广告位图片
    > 2. adsense_id : 1    // 广告位ID

5. data 下 数据 goods_list(数组)   推荐商品列表
    > 1. "goods_id": 903,     //商品ID
    > 2. "goods_name": "正品直邮Hermes爱马仕2017新款男鞋 时尚真皮休闲鞋H171325ZH02   7495",   //商品名称
    > 3. "shop_price": "798.00",    //商店价格
    > 4. "goods_thumb": "http://10.10.10.145/dsc/mobile/public/img/no_image.jpg",    //商品图片
    > 5. "goods_sales": 0,    //销售量
    > 6. "market_price": "957.59",    //   市场价格
    > 7. "goods_stock": 998   //  商品库存
    > 8. "market_price_formated": "¥574.80"   //  格式化市场价格
    > 9. "shop_price_formated": "¥479.00"   //  格式化商店价格