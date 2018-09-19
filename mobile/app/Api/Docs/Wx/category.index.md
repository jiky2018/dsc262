##api/wx/category  小程序分类列表

####链接
     http://domain/mobile/public/api/wx/category

####参数


####返回参数
1. code : 0 为正常   **1 为不正常**
2. data  : 数据 （数组）
3. data 下 数据 category(数组)   分类
    > 1. "id": 858,    //分类ID
    > 2. "name": "家用电器",    //分类名称
    > 3. "cat_img": "/dsc/mobile/public/img/no_image.jpg",   //分类图片
    > 4. "haschild": 1,   //分类是否有子分类
    > 5. "cat_id"  : []  子分类 （数组）

