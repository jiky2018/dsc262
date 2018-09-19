##api/wx/goods/property  小程序商品详情属性接口

####链接
     http://domain/mobile/public/api/wx/goods/property

####参数
1. id  2    //商品ID
2. attr_id   //属性ID  为属性数组
3. num    //商品数量
4. warehouse_id    仓库id
5. area_id    地区id


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data 下 数据 
    > 1. attr_img ： "/img/no_image.jpg"   //属性图片
    > 2. goods_price ： 4109   //商品价格
    > 3. goods_price_formated:"¥4109.00"   //商品价格格式化
    > 4. market_price:"4918.80"   //市场价格
    > 5. market_price_formated:"¥4918.80"   //市场价格格式化
    > 6. qty:1   //商品数量
    > 7. spec_price:"10.00"   //属性价格
    > 8. spec_price_formated:"¥10.00"   //属性价格格式化
    > 9. stock:14   //商品库存