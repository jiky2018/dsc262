##api/wx/goods/list 小程序商品列表接口

####链接
     http://domain/mobile/public/api/wx/goods/list

####参数
1. id  商品分类ID(参考, 必须)  以下参数非必须
2. page 页数
3. keyword    关键词
4. per_page  每页数量
5. sort_key   排序 值 （0 1 2）
6. sort_value   排序 方式  （升序 降序）


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  : 数据 （数组）
    > 1.   "goods_id": 738,     //商品ID
    > 2.   "goods_name": "香港特产珍妮聪明小熊曲奇饼干640g四味奶油4mix礼盒装进口零食品 香港人气美食 手工四味奶油曲奇",  //商品名称
    > 3.   "shop_price": "188.00",    //商品商店价格
    > 4.   "goods_thumb": "/dsc/mobile/public/img/no_image.jpg",   // 商品图片
    > 5.   "goods_number": 1000,    //  商品数量
    > 6.   "market_price": "225.60",     //市场价格
    > 7.   "sales_volume": 0,    //  销售量
    > 8.   "market_price_formated": "¥2640.00",  //  格式化市场价格
    > 9.   "shop_price_formated": "¥2200.00"   // 格式化商店价格