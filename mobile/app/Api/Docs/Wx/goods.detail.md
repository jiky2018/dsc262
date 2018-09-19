##api/wx/goods/detail  小程序商品详情接口

####链接
     http://domain/mobile/public/api/wx/goods/detail

####参数
1. id  商品ID


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  : 数据 （数组）
3. data 下 数据 goods_img(数组)   商品图片组
    > 1. thumb_url  图片路径
4. data 下 数据  goods_info(数组)      商品基本信息
    > 1. "goods_id": 858,    //商品ID
    > 2. "goods_name": "家用电器",    //商品名称
    > 3. "goods_price": "18.00",   //
    > 4. "market_price": 1,   //市场价格
    > 5. "stock"  :  233 库存
    > 6. "goods_desc" : '223'   商品描述
    > 7. "url"    : '/dsc/mobile/index.php?m=goods&id=700'   商品地址
    > 8.  goodsthumb"    商品图片
    > 9. sales    //商品销量
5. data 下 数据  goods_comment(数组)      商品评论
    > 1. "id": 3    评论ID
    > 2. "user_name": "df"  用户名
    > 3. "content": "sssss"    评论内容
    > 4. "add_time": 0   添加时间
6. data 下 数据  goods_properties(数组)      商品属性规格
    > 1. pro   []  （值为数组）  键为属性分类ID  值为属性
    >   2. attr_type   属性类型  [唯一属性， 单选属性]
    >   3. name   属性分类名称
    >   4. values  具体属性值  [数组]    **`以下为具体属性值`**
    >     5. label  属性名称
    >     6. attr_sort  属性排序
    >     7. price   属性的价格
    >     8. format_price   格式化属性价格
    >     9. id  商品属性ID
7. data 下 数据   root_path 项目跟目录
8. data 下 数据   shop_name  店铺名称
9. data 下 数据   total_comment_number  评论总数


