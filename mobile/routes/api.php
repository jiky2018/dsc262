<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$api = app('Dingo\Api\Routing\Router');

/**
 * Add in header    Accept:application/vnd.lumen.v2+json
 */
$api->version('v2', ['namespace' => 'App\Api\Controllers'], function ($api) {

    /** 小程序接口 */
    $api->group(['prefix' => 'wx', 'namespace' => 'Wx'] , function ($api) {

        /** 首页 */
        $api->post('/', 'IndexController@index');
        $api->post('index', 'IndexController@index');

        /** 购物车 */
        $api->post('cart', 'CartController@cart');
        $api->post('cart/add', 'CartController@addGoodsToCart');
        $api->post('cart/delete', 'CartController@deleteCartGoods');
        $api->post('cart/update', 'CartController@updateCartGoods');

        /** 商品 */
        $api->post('goods/list', 'GoodsController@goodsList');
        $api->post('goods/detail', 'GoodsController@goodsDetail');
        $api->post('goods/property', 'GoodsController@property');
        $api->post('goods/share', 'GoodsController@share');
        $api->post('goods/coupons', 'GoodsController@coupons');
        $api->post('goods/filtercondition', 'GoodsController@goodsFilterCondition');
        $api->post('goods/history', 'GoodsController@history');
        $api->post('goods/save', 'GoodsController@goodsSave');

        /** 分类 */
        $api->post('category', 'CategoryController@index');

        /** 文章 */
        $api->post('article', 'ArticleController@index');
        $api->post('article/detail', 'ArticleController@detail');

        /** 分享 */
        $api->post('share', 'ShareController@index');

        /** 订单确认 */
        $api->post('flow', 'FlowController@index');
        /** 选择优惠券 */
        $api->post('flow/changecou', 'FlowController@changecou');
        /** 订单提交 */
        $api->post('flow/down', 'FlowController@down');
        /** 订单费用 */
        $api->post('flow/shipping', 'FlowController@shipping');
        /** 订单结账 */
        $api->post('flow/detail', 'FlowController@detail');

        /** 店铺详情 */
        $api->post('store', 'StoreController@index');
        $api->post('store/detail', 'StoreController@detail');
        $api->post('store/attention', 'StoreController@attention');

        /** 用户中心 */
        $api->post('user', 'UserController@index');

        /** 用户登录 */
        $api->post('user/login', 'UserController@login');
        $api->post('user/order/list', 'UserController@orderList');
        $api->post('user/order/detail', 'UserController@orderDetail');
        $api->post('user/order/logistics', 'UserController@orderLogistics');
        $api->post('user/order/appraise', 'UserController@orderAppraise');
        $api->post('user/order/appraise/add', 'UserController@orderAppraiseAdd');
        $api->post('user/order/appraise/detail', 'UserController@orderAppraiseDetail');
        $api->post('user/order/cancel', 'UserController@orderCancel');
        $api->post('user/order/confirm', 'UserController@orderConfirm');
        $api->post('user/address/choice', 'UserController@addressChoice');
        $api->post('user/address/list', 'UserController@addressList');
        $api->post('user/address/add', 'UserController@addressAdd');
        $api->post('user/address/detail', 'UserController@addressDetail');
        $api->post('user/address/update', 'UserController@addressUpdate');
        $api->post('user/address/delete', 'UserController@addressDelete');
        $api->post('user/invoice/add', 'UserController@invoiceAdd');
        $api->post('user/invoice/detail', 'UserController@invoiceDetail');
        $api->post('user/invoice/update', 'UserController@invoiceUpdate');
        $api->post('user/invoice/delete', 'UserController@invoiceDelete');
        $api->post('user/account', 'UserController@account');
        $api->post('user/account/detail', 'UserController@accountDetail');
        $api->post('user/account/log', 'UserController@accountLog');
        $api->post('user/account/deposit', 'UserController@deposit');
        $api->post('user/collectgoods', 'UserController@collectGoods');
        $api->post('user/collectstore', 'UserController@collectStore');
        $api->post('user/collect/add', 'UserController@collectAdd');
        $api->post('user/conpont', 'UserController@conpont');
        $api->post('user/funds', 'UserController@funds');
        $api->post('user/history', 'UserController@history');

        /** 地区选择 */
        $api->post('region/list', 'RegionController@regionList');
        $api->post('location', 'LocationController@index');
        $api->post('location/info', 'LocationController@info');
        $api->post('location/getcity', 'LocationController@getcity');
        $api->post('location/setcity', 'LocationController@setcity');
        $api->post('location/specific', 'LocationController@specific');

        /** 支付 */
        $api->post('payment/pay', 'PaymentController@pay');
        $api->post('payment/notify', 'PaymentController@notify');

        /** 上传图片 */
        $api->post('upload', 'UserController@uploadFile');
        /** 砍价 */
        $api->post('bargain', 'BargainController@index');
        $api->post('bargain/list', 'BargainController@bargainList');
        $api->post('bargain/goodsDetail', 'BargainController@goodsDetail');
        $api->post('bargain/property', 'BargainController@property');
        $api->post('bargain/addBargain', 'BargainController@addBargain');
        $api->post('bargain/goBargain', 'BargainController@goBargain');
        $api->post('bargain/Bargainbuy', 'BargainController@Bargainbuy');
        $api->post('bargain/myBargain', 'BargainController@myBargain');
        /** 拼团 */
        $api->post('team', 'TeamController@index');  //拼团首页
        $api->post('team/teamList', 'TeamController@teamList');  //拼团首页商品列表
        $api->post('team/categoriesIndex', 'TeamController@categoriesIndex');  //拼团频道teamRanking
        $api->post('team/categoryList', 'TeamController@categoryList');  //拼团子频道商品列表
        $api->post('team/teamRanking', 'TeamController@teamRanking');  //拼团排行
        $api->post('team/goodsDetail', 'TeamController@goodsDetail');  //拼团商品详情
        $api->post('team/property', 'TeamController@property');  //普通商品属性切换
        $api->post('team/teamProperty', 'TeamController@teamProperty');  //拼团商品属性切换
        $api->post('team/teamBuy', 'TeamController@teamBuy');  //购买
        $api->post('team/teamWait', 'TeamController@teamWait');  //等待成团
        $api->post('team/teamIsBest', 'TeamController@teamIsBest');  //拼团推荐商品
        $api->post('team/teamUser', 'TeamController@teamUser');  //拼团成员
        $api->post('team/teamUserOrder', 'TeamController@teamUserOrder');  //拼团订单
        $api->post('team/virtualOrder', 'TeamController@virtualOrder');  //拼团成员



    });

    /**
     * Brand
     */
    $api->group(['prefix' => 'brand', 'namespace' => 'Brand'], function ($api) {
        /**
         * 品牌列表
         */
        $api->get('brand', 'BrandController@index');
        /**
         * 单个品牌
         */
        $api->get('detail', 'BrandController@get');
    });

    /**
     * Shop
     */
    $api->group(['prefix' => 'shop', 'namespace' => 'Shop'], function ($api) {
        $api->get('shop', 'ShopController@index');
    });


    /**
     * 地区列表
     */
    $api->group(['prefix' => 'location', 'namespace' => 'Location'], function ($api) {
        //地区列表
        $api->get('index', 'LocationController@index');
        //仓库
        $api->get('info', 'LocationController@info');
        //获取最近访问城市
        $api->get('getcity', 'LocationController@getcity');
        //设置最近访问城市
        $api->get('setcity', 'LocationController@setcity');
    });

    /**
     * Need authentication
     */
    $api->group(['prefix' => 'user', 'middleware' => 'api.auth'], function ($api) {

        /**
         * User Profile
         */
        $api->resource('index', 'UserController');

        /**
         * User Address
         */
        $api->resource('address', 'AddressController');

    });

});

/*
// ADDRESS
address/add（添加地址）
address/delete（删除地址）
address/info（单条地址信息）
address/list（所有地址列表）
address/setDefault（设置默认地址）
address/update（更新单条地址信息）


BONUS
bonus/validate（红包获取验证）
bonus/bind（红包兑换）
bonus/coupon（获取优惠红包列表信息o2o）
receive/coupon（领取商品或店铺优惠券）
send/coupon（获取优惠券）

TOPIC
topic/info（专题详情）

COMMENTS
comment/create（发表评论）
order/comment（获取订单评论）

FEEDBACK
feedback/list（留言反馈列表）
feedback/create（提交留言反馈）
admin/feedback/list（咨询列表）
admin/feedback/messages（咨询详细信息）
admin/feedback/reply（掌柜咨询回复（包含：订单,商品及用户））

CART
cart/create（添加到购物车）
cart/gift/create（添加赠品到购物车）
cart/delete（从购物车中删除一商品）
cart/list（购物车列表）
cart/update（购物车更新商品数目）
flow/checkOrder（购物流检查订单）
flow/done（购物流完成）

GOODS
category（所有分类）
goods/category（所有分类）
comments（某商品的所有评论）
goods/comments（某商品的所有评论）
goods（单个商品的信息）
goods/list（商品列表）
goods/suggestlist（商品推荐列表）
goods/groupbuygoods（团购商品列表）
goods/mobilebuygoods（手机专享商品列表）
goods/detail（单个商品的信息）
goods/stock（商品属性库存）
goods/desc（单个商品的详情）
goods/brand（某一分类的品牌列表）
goods/filter（某一分类的属性列表）
goods/price_range（价格范围）
search（搜索）
searchKeywords（搜索关键词）

SHOP
shop/config（商店配置）
shop/token（获取token信息）
shop/payment（支付方式）
shop/region（地区）
shop/help（商店帮助分类列表）
shop/help/detail（商店帮助内容）
shop/info（网店信息）
shop/info/detail（网店信息内容）
shop/server（服务器环境信息）

HOME
home/category（HOME分类）
home/data（HOME数据）
tv/home/data（tv首页数据）
home/adsense（HOME广告）
home/discover（discover数据）
home/news（今日热点数据）

ORDER
order/affirmReceived（订单确认收货）
order/cancel（订单取消）
order/return/list（退换货列表）
order/return/detail（退换货详情）
order/return/apply（申请退换货）
order/return/cancel（取消退换货申请）
order/return/reason（退换货理由）
order/list（订单列表）
order/pay（订单支付）
order/detail（订单详情）
order/reminder（提醒卖家发货）
order/update（订单更新）
order/express（订单快递）

USER
user/collect/create（用户收藏商品）
user/collect/delete（用户删除收藏商品）
user/collect/list（用户收藏列表）
user/info（用户信息）
user/signin（用户登录）
user/signout（用户退出）
validate/signin（用户手机验证码登录）
user/signup（用户注册）
user/forget_password（用户找回密码）
validate/forget_password（用户找回密码验证）
user/reset_password（用户找回密码重置密码）
user/password（修改登录密码）
user/update（用户图片上传或修改）
user/snsbind（第三方登录）
user/send_pwdmail（邮箱找回密码/测试）
user/signupFields（用户注册字段）
user/account/record（用户充值提现记录）
user/account/log（用户账户资金变更日志/测试）
user/account/deposit（用户充值申请）
user/account/pay（用户充值付款）
user/account/raply（用户提现申请）
user/account/cancel（用户申请取消）
user/userbind（手机快速注册）
validate/bind（验证用户绑定注册）
validate/bonus（验证红包）
validate/integral（验证积分）
validate/account（验证用户账户信息）
user/account/update（修改会员账户信息）

CONNECT
connect/signin（第三方关联登录）
connect/signup（第三方关联注册）
connect/bind（第三方关联绑定）

SELLER
goods/search（商店搜索）

DEVICE
device/setDeviceToken（设备号）

MOBILE
mobile/qrcode/validate（扫码登录验证二维码有效性）
mobile/checkin/record（签到记录）
mobile/checkin（签到）
mobile/shake（摇一摇）
mobile/toutiao（获取店铺头条热门信息）

INVITE
invite/user（推荐用户信息）
invite/record（推荐用户奖励记录）

SELLER
seller/category（店铺分类）
seller/list（店铺列表）
seller/search（店铺搜索）
seller/collect/list（收藏店铺列表）
seller/collect/create（收藏店铺）
seller/collect/delete（删除收藏店铺）

merchant/home/data（商店基本信息）
merchant/goods/category（商店分类）
merchant/goods/list（商店商品）
merchant/goods/suggestlist（商店推荐商品）

ADMIN/MERCHANT
admin/merchant/info（店铺信息）
admin/merchant/update（店铺信息修改）
*/