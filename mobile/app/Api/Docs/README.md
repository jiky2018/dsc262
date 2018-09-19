大商创商城系统 API 文档
==============

### 概述

我们为商家提供的丰富API，涵盖大商创各个核心业务流程，基于这些内容可开发各类应用，解决店铺管理、营销推广、数据分析等方面的问题，以实现站点和客户端及单页应用等多种形式的应用接入。如果您是富有企业信息系统开发经验的传统软件厂商，您还可以基于大商创
API 为商家提供定制服务包括但不限于BI、ERP、DRP、CRM、SCM 等。

大商创的 API 是基于HTTP协议来调用的，开发者应用可以根据大商创的协议来封装 HTTP
请求进行调用，以下主要是针对自行封装 HTTP 请求进行 API 调用的原理进行详细解说。

### 接入流程

根据大商创的协议：填充参数 > 生成签名 > 拼装HTTP请求 > 发起HTTP请求>
得到HTTP响应 > 解释json/xml结果。

### 公共参数

| 参数名称     | 参数类型 | 是否必须 | 参数描述                                                                                                                |
|:------------|:--------|:--------|:-----------------------------------------------------------------------------------------------------------------------|
| method      | String  | 是      | API接口名称。                                                                                                           |
| app_key     | String  | 是      | 分配给应用的AppId。                                                                                                      |
| session     | String  | 否      | 用户登录成功后的授权信息。当此API的标签上注明："需要授权"，则此参数必传；"不需要授权"，则此参数不需要传；"可选授权"，则此参数为可选。  |
| timestamp   | String  | 是      | 时间戳，格式为yyyy-MM-dd HH:mm:ss，时区为GMT+8，例如：2016-01-01 12:00:00。服务端允许客户端请求最大时间误差为10分钟。           |
| format      | String  | 否      | 响应格式。默认为xml格式，可选值：xml，json。                                                                               |
| v           | String  | 是      | API协议版本，可选值：2.0。                                                                                                |
| sign        | String  | 是      | API输入参数签名结果，签名算法参照下面的介绍。                                                                               |
| sign_method | String  | 是      | 签名的摘要算法，可选值为：md5。                                                                                            |

### 业务参数

API调用除了必须包含公共参数外，如果API本身有业务级的参数也必须传入，每个API的业务级参数请参见各个
API 内的参数说明。

### 签名算法

为了防止API调用过程中被黑客恶意篡改，调用任何一个API都需要携带签名，服务端会根据请求参数，对签名进行验证，签名不合法的请求将会被拒绝。目前支持的签名算法有：MD5(sign_method=md5)，签名大体过程如下：

对所有API请求参数（包括公共参数和业务参数，但除去sign参数和byte[]类型的参数），根据参数名称的ASCII码表的顺序排序。如：foo=1,
bar=2, foo_bar=3, foobar=4排序后的顺序是bar=2, foo=1, foo_bar=3,
foobar=4。
将排序好的参数名和参数值拼装在一起，根据上面的示例得到的结果为：bar2foo1foo_bar3foobar4。
把拼装好的字符串采用utf-8编码，使用签名算法对编码后的字节流进行摘要。如果使用MD5算法，则需要在拼装的字符串前后加上app的secret后，再进行摘要，如：md5(secret+bar2foo1foo_bar3foobar4+secret)；
说明：MD5是128位长度的摘要算法，用16进制表示，一个十六进制的字符能表示4个位，所以签名后的字符串长度固定为32个十六进制字符。

### 调用示例

下面将以 ecapi.goods.get 接口调用为例，具体步骤如下：

#### Step 1: 设置参数值

公共参数：

```
method = "ecapi.goods.get"
app_key = "12345678"
session = "test"
timestamp = "2016-01-01 12:00:00"
format = "json"
v = "2.0"
sign_method = "md5"
```

业务参数：

```
goods_id = 11223344
```

#### Step 2: 按ASCII顺序排序

```
app_key = "12345678"
format = "json"
goods_id = 11223344
method = "ecapi.goods.get"
session = "test"
sign_method = "md5"
timestamp = "2016-01-01 12:00:00"
v = "2.0"
```

#### Step 3: 拼接参数名与参数值

```
app_key12345678formatjsongoods_id11223344methodecapi.goods.getsessiontestsign_methodmd5timestamp2016-01-01 12:00:00v2.0
```

#### Step 4: 生成签名

假设app的secret为helloworld，则签名结果为：md5(helloworld+按顺序拼接好的参数名与参数值+helloworld)
= "E9CB394C1AC658E727818B4C1500DFB6"

#### Step 5: 组装HTTP请求

将所有参数名和参数值采用utf-8进行URL编码（参数顺序可随意，但必须要包括签名参数），然后通过GET或POST（含byte[]类型参数）发起请求，如：

```
http://m.dscmall.cn/?method=ecapi.item.get&app_key=12345678&session=test&timestamp=2016-01-01+12%3A00%3A00&format=json&v=1.0&sign_method=md5&goods_id=11223344&sign=D1F578E6E6EE4E7B85D3B94970328EEC
```

版权所有

2017 (c) 上海商创网络科技有限公司

### 店铺

| API                                                         | 描述                 |
|:------------------------------------------------------------|:--------------------|
| [ecapi.shop.create](ecapi.shop.create.md)                   | 创建店铺             |
| [ecapi.shop.get](ecapi.shop.get.md)                         | 获取店铺基础信息      |
| [ecapi.shop.update](ecapi.shop.update.md)                   | 更新店铺信息          |
| [ecapi.shop.address.list](ecapi.shop.address.list.md)       | 店铺地址库获取所有地址 |
| [ecapi.shop.address.add](ecapi.shop.address.add.md)         | 店铺地址库新建一个地址 |
| [ecapi.shop.address.get](ecapi.shop.address.get.md)         | 店铺地址库获取一个地址 |
| [ecapi.shop.address.update](ecapi.shop.address.update.md)   | 店铺地址库更新一个地址 |
| [ecapi.shop.address.delete](ecapi.shop.address.delete.md)   | 店铺地址库删除一个地址 |
| [ecapi.shop.category.list](ecapi.shop.category.list.md)     | 获取店铺分类多维列表   |
| [ecapi.shop.category.add](ecapi.shop.category.add.md)       | 新增一个店铺商品分类   |
| [ecapi.shop.category.get](ecapi.shop.category.get.md)       | 获取一个店铺商品分类   |
| [ecapi.shop.category.update](ecapi.shop.category.update.md) | 更新一个店铺商品分类   |
| [ecapi.shop.category.delete](ecapi.shop.category.delete.md) | 删除一个店铺商品分类   |

### 类目

| API                                                     | 描述            |
|:--------------------------------------------------------|:---------------|
| [ecapi.category.list](ecapi.category.list.md)           | 获取分类多维列表 |
| [ecapi.category.add](ecapi.category.add.md)             | 新增一个商品分类 |
| [ecapi.category.get](ecapi.category.get.md)             | 获取一个商品分类 |
| [ecapi.category.update](ecapi.category.update.md)       | 更新一个商品分类 |
| [ecapi.category.delete](ecapi.category.delete.md)       | 删除一个商品分类 |
| [ecapi.brand.category.get](ecapi.brand.category.get.md) | 获取品牌分类     |
| [ecapi.brand.list](ecapi.brand.list.md)                 | 获取品牌列表     |
| [ecapi.brand.add](ecapi.brand.add.md)                   | 新增一个商品品牌 |
| [ecapi.brand.get](ecapi.brand.get.md)                   | 获取单个品牌信息 |
| [ecapi.brand.update](ecapi.brand.update.md)             | 更新单个品牌信息 |
| [ecapi.brand.delete](ecapi.brand.delete.md)             | 删除单个品牌信息 |

### 商品

| API                                                       | 描述                |
|:----------------------------------------------------------|:-------------------|
| [ecapi.goods.list](ecapi.goods.list.md)                   | 获取商品列表         |
| [ecapi.goods.add](ecapi.goods.add.md)                     | 新增一个商品         |
| [ecapi.goods.get](ecapi.goods.get.md)                     | 获取一个商品         |
| [ecapi.goods.update](ecapi.goods.update.md)               | 更新一个商品         |
| [ecapi.goods.delete](ecapi.goods.delete.md)               | 删除一个商品         |
| [ecapi.goods.sku.list](ecapi.goods.sku.list.md)           | 获取商品SKU列表      |
| [ecapi.goods.sku.add](ecapi.goods.sku.add.md)             | 新增一个商品SKU      |
| [ecapi.goods.sku.get](ecapi.goods.sku.get.md)             | 获取一个商品SKU      |
| [ecapi.goods.sku.update](ecapi.goods.sku.update.md)       | 更新一个商品SKU      |
| [ecapi.goods.sku.delete](ecapi.goods.sku.delete.md)       | 删除一个商品SKU      |
| [ecapi.goods.inventory.get](ecapi.goods.inventory.get.md) | 获取仓库中的商品列表  |
| [ecapi.goods.onsale.get](ecapi.goods.onsale.get.md)       | 获取出售中的商品列表  |

### 购物车

| API                                                                   | 描述                       |
|:----------------------------------------------------------------------|:--------------------------|
| [ecapi.trade.cart.get](ecapi.trade.cart.get.md)                       | 获取购物车商品              |
| [ecapi.trade.cart.add](ecapi.trade.cart.add.md)                       | 添加一个商品到购物车         |
| [ecapi.trade.cart.update](ecapi.trade.cart.update.md)                 | 更新购物车商品数目          |
| [ecapi.trade.cart.delete](ecapi.trade.cart.delete.md)                 | 从购物车中删除一商品         |
| [ecapi.trade.cart.clear](ecapi.trade.cart.clear.md)                   | 清空购物车中的商品          |
| [ecapi.trade.cart.collect](ecapi.trade.cart.collect.md)               | 将商品移至收藏夹            |
| [ecapi.trade.cart.package.add](ecapi.trade.cart.package.add.md)       | 添加礼包到购物车            |
| [ecapi.trade.cart.favourable.add](ecapi.trade.cart.favourable.add.md) | 添加优惠活动到购物车         |
| [ecapi.trade.cart.count](ecapi.trade.cart.count.md)                   | 查询用户在购物车中的商品数量  |

### 交易

| API                                                           | 描述             |
|:--------------------------------------------------------------|:----------------|
| [ecapi.trade.shipping.update](ecapi.trade.shipping.update.md) | 改变配送方式      |
| [ecapi.trade.insure.update](ecapi.trade.insure.update.md)     | 选定/取消配送保价 |
| [ecapi.trade.payment.update](ecapi.trade.payment.update.md)   | 改变支付方式      |
| [ecapi.trade.pack.update](ecapi.trade.pack.update.md)         | 改变包装         |
| [ecapi.trade.card.update](ecapi.trade.card.update.md)         | 改变贺卡         |
| [ecapi.trade.surplus.update](ecapi.trade.surplus.update.md)   | 改变余额         |
| [ecapi.trade.integral.update](ecapi.trade.integral.update.md) | 改变积分         |
| [ecapi.trade.bonus.update](ecapi.trade.bonus.update.md)       | 改变红包优惠券    |
| [ecapi.trade.needinv.update](ecapi.trade.needinv.update.md)   | 改变发票设置      |
| [ecapi.trade.oos.update](ecapi.trade.oos.update.md)           | 改变缺货处理方式  |

### 订单

| API                                             | 描述                |
|:------------------------------------------------|:-------------------|
| [ecapi.order.create](ecapi.order.create.md)     | 提交订单数据         |
| [ecapi.order.get](ecapi.order.get.md)           | 获取单个订单         |
| [ecapi.order.update](ecapi.order.update.md)     | 更新单个订单         |
| [ecapi.order.cancel](ecapi.order.cancel.md)     | 取消单个订单         |
| [ecapi.order.merge](ecapi.order.merge.md)       | 合并两个订单         |
| [ecapi.order.again](ecapi.order.again.md)       | 订单商品添加到购物车  |
| [ecapi.order.pay](ecapi.order.pay.md)           | 获取订单支付信息     |
| [ecapi.order.reminder](ecapi.order.reminder.md) | 订单发货提醒         |
| [ecapi.order.express](ecapi.order.express.md)   | 订单快递追踪         |
| [ecapi.order.received](ecapi.order.received.md) | 订单确认收货         |
| [ecapi.order.list](ecapi.order.list.md)         | 获取订单列表         |

### 会员

| API                                                         | 描述            |
|:------------------------------------------------------------|:---------------|
| [ecapi.user.get](ecapi.user.get.md)                         | 获取用户信息     |
| [ecapi.user.signup](ecapi.user.signup.md)                   | 用户注册        |
| [ecapi.user.signin](ecapi.user.signin.md)                   | 用户登录        |
| [ecapi.user.update](ecapi.user.update.md)                   | 更新用户资料     |
| [ecapi.user.signup.fields](ecapi.user.signup.fields.md)     | 获取注册字段     |
| [ecapi.user.password.update](ecapi.user.password.update.md) | 修改会员密码     |
| [ecapi.user.forget](ecapi.user.forget.md)                   | 找回密码修改密码 |
| [ecapi.user.bind](ecapi.user.bind.md)                       | 绑定注册        |
| [ecapi.user.logout](ecapi.user.logout.md)                   | 用户注销        |

### 收货地址

| API                                                         | 描述            |
|:------------------------------------------------------------|:---------------|
| [ecapi.user.address.get](ecapi.user.address.get.md)         | 获取用户收货地址 |
| [ecapi.user.address.add](ecapi.user.address.add.md)         | 添加用户收货地址 |
| [ecapi.user.address.update](ecapi.user.address.update.md)   | 更新用户收货地址 |
| [ecapi.user.address.delete](ecapi.user.address.delete.md)   | 删除用户收货地址 |
| [ecapi.user.address.default](ecapi.user.address.default.md) | 设置默认收货地址 |

### 收藏关注

| API                                                           | 描述            |
|:--------------------------------------------------------------|:---------------|
| [ecapi.user.collect.add](ecapi.user.collect.add.md)           | 用户收藏单个商品 |
| [ecapi.user.collect.delete](ecapi.user.collect.delete.md)     | 用户删除收藏商品 |
| [ecapi.user.collects.get](ecapi.user.collects.get.md)         | 用户收藏列表     |
| [ecapi.user.attention.add](ecapi.user.attention.add.md)       | 添加关注商品     |
| [ecapi.user.attention.delete](ecapi.user.attention.delete.md) | 取消关注商品     |

### 资金

| API                                                         | 描述                 |
|:------------------------------------------------------------|:--------------------|
| [ecapi.user.account.log](ecapi.user.account.log.md)         | 获取会员充值提现记录   |
| [ecapi.user.account.deposit](ecapi.user.account.deposit.md) | 创建会员充值申请      |
| [ecapi.user.account.raply](ecapi.user.account.raply.md)     | 创建会员提现申请      |
| [ecapi.user.account.detail](ecapi.user.account.detail.md)   | 获取帐户资金明细      |
| [ecapi.user.account.pay](ecapi.user.account.pay.md)         | 会员充值付款          |
| [ecapi.user.account.cancel](ecapi.user.account.cancel.md)   | 会员充值/提现申请取消  |

### 优惠券

| API                                                                                   | 描述                      |
|:--------------------------------------------------------------------------------------|:-------------------------|
| [ecapi.user.bonus.get](ecapi.user.bonus.get.md)                                       | 会员红包列表               |
| [ecapi.user.bonus.add](ecapi.user.bonus.add.md)                                       | 添加一个红包               |
| [ecapi.ump.promocode.add](ecapi.ump.promocode.add.md)                                 | 创建优惠码                |
| [ecapi.ump.coupon.consume.fetchlogs.get](ecapi.ump.coupon.consume.fetchlogs.get.md)   | 获取优惠券/优惠码领取记录   |
| [ecapi.ump.promocard.add](ecapi.ump.promocard.add.md)                                 | 创建优惠券                |
| [ecapi.ump.coupon.take](ecapi.ump.coupon.take.md)                                     | 微信粉丝领取优惠券优惠码    |
| [ecapi.ump.coupon.consume.verify](ecapi.ump.coupon.consume.verify.md)                 | 核销优惠券/优惠码          |
| [ecapi.ump.coupon.consume.verifylogs.get](ecapi.ump.coupon.consume.verifylogs.get.md) | 获取优惠券/优惠码核销记录   |
| [ecapi.ump.coupon.consume.get](ecapi.ump.coupon.consume.get.md)                       | 根据核销码获取优惠券/优惠码 |
| [ecapi.ump.coupons.unfinished.search](ecapi.ump.coupons.unfinished.search.md)         | 获取所有未结束的优惠列表    |
| [ecapi.user.booking.add](ecapi.user.booking.add.md)                                   | 添加缺货登记               |
| [ecapi.user.booking.get](ecapi.user.booking.get.md)                                   | 显示缺货登记列表           |
| [ecapi.user.booking.delete](ecapi.user.booking.delete.md)                             | 删除缺货登记               |
| [ecapi.user.tag.get](ecapi.user.tag.get.md)                                           | 标签云列表                |
| [ecapi.user.tag.add](ecapi.user.tag.add.md)                                           | 添加标签云                |
| [ecapi.user.tag.delete](ecapi.user.tag.delete.md)                                     | 删除标签                  |
| [ecapi.user.affiliate](ecapi.user.affiliate.md)                                       | 用户推荐分享               |
| [ecapi.user.validate.email](ecapi.user.validate.email.md)                             | 验证用户注册邮件           |
| [ecapi.user.history.clear](ecapi.user.history.clear.md)                               | 清除商品浏览历史           |

### 评价

| API                                             | 描述         |
|:------------------------------------------------|:------------|
| [ecapi.comment.get](ecapi.comment.get.md)       | 显示评论列表  |
| [ecapi.comment.add](ecapi.comment.add.md)       | 发表商品评论  |
| [ecapi.comment.delete](ecapi.comment.delete.md) | 删除评论     |
| [ecapi.message.get](ecapi.message.get.md)       | 显示留言列表  |
| [ecapi.message.add](ecapi.message.add.md)       | 提交留言反馈  |
| [ecapi.message.delete](ecapi.message.delete.md) | 删除留言     |

### 营销

| API                                                               | 描述       |
|:------------------------------------------------------------------|:----------|
| [ecapi.promotion.activity](ecapi.promotion.activity.md)           | 优惠活动   |
| [ecapi.promotion.auction](ecapi.promotion.auction.md)             | 拍卖活动   |
| [ecapi.promotion.group_buy](ecapi.promotion.group_buy.md)         | 团购活动   |
| [ecapi.promotion.exchange](ecapi.promotion.exchange.md)           | 积分兑换   |
| [ecapi.promotion.topic](ecapi.promotion.topic.md)                 | 专题汇     |
| [ecapi.promotion.bargain](ecapi.promotion.bargain.md)             | 砍价活动   |
| [ecapi.promotion.article](ecapi.promotion.article.md)             | 社区资讯   |
| [ecapi.promotion.distribution](ecapi.promotion.distribution.md)   | 分销活动   |
| [ecapi.promotion.crowd_funding](ecapi.promotion.crowd_funding.md) | 微众筹     |
| [ecapi.promotion.spell_group](ecapi.promotion.spell_group.md)     | 拼团       |
| [ecapi.promotion.package](ecapi.promotion.package.md)             | 超值礼包   |
| [ecapi.promotion.wholesale](ecapi.promotion.wholesale.md)         | 批发活动   |
| [ecapi.promotion.snatch](ecapi.promotion.snatch.md)               | 夺宝奇兵   |
| [ecapi.promotion.check_in](ecapi.promotion.check_in.md)           | 每日签到   |
| [ecapi.promotion.shark_it_off](ecapi.promotion.shark_it_off.md)   | 摇一摇     |
| [ecapi.promotion.paying_agent](ecapi.promotion.paying_agent.md)   | 订单代付   |
| [ecapi.promotion.egg_frenzy](ecapi.promotion.egg_frenzy.md)       | 砸金蛋     |
| [ecapi.promotion.scratch_card](ecapi.promotion.scratch_card.md)   | 刮刮卡     |
| [ecapi.promotion.big_wheel](ecapi.promotion.big_wheel.md)         | 大转盘     |
| [ecapi.promotion.coupon](ecapi.promotion.coupon.md)               | 领取优惠券 |

### 支付

| API                                     | 描述       |
|:----------------------------------------|:----------|
| [ecapi.pay.qrcode](ecapi.pay.qrcode.md) | 支付二维码 |

### 微信公众号

| API                                                       | 描述         |
|:----------------------------------------------------------|:------------|
| [ecapi.wechat.oauth](ecapi.wechat.oauth.md)               | 微信授权     |
| [ecapi.wechat.jssdk](ecapi.wechat.jssdk.md)               | 微信JSSDK    |
| [ecapi.wechat.userinfo.get](ecapi.wechat.userinfo.get.md) | 微信用户信息  |

### 商家

| API                                               | 描述         |
|:--------------------------------------------------|:------------|
| [ecapi.sellers.get](ecapi.sellers.get.md)         | 店铺街       |
| [ecapi.seller.get](ecapi.seller.get.md)           | 商家店铺详情  |
| [ecapi.seller.merchant](ecapi.seller.merchant.md) | 入驻商家信息  |

### 门店

| API                                     | 描述     |
|:----------------------------------------|:--------|
| [ecapi.store.list](ecapi.store.list.md) | 门店列表 |
| [ecapi.store.get](ecapi.store.get.md)   | 门店详情 |


### 系统

| API                                           | 描述     |
|:----------------------------------------------|:--------|
| [ecapi.shop.config](ecapi.shop.config.md)     | 系统配置 |
| [ecapi.shop.shipping](ecapi.shop.shipping.md) | 配送方式 |
| [ecapi.shop.payment](ecapi.shop.payment.md)   | 支付方式 |
| [ecapi.shop.ad](ecapi.shop.ad.md)             | 手机广告 |
| [ecapi.shop.help](ecapi.shop.help.md)         | 商店帮助 |

### 工具

| API                                               | 描述       |
|:--------------------------------------------------|:----------|
| [ecapi.tool.region](ecapi.tool.region.md)         | 地区       |
| [ecapi.tool.chat](ecapi.tool.chat.md)             | 在线客服   |
| [ecapi.tool.sms](ecapi.tool.sms.md)               | 短信发送   |
| [ecapi.search.keywords](ecapi.search.keywords.md) | 搜索关键词 |


### 商家订单

| API                                                                     | 描述                          |
|:------------------------------------------------------------------------|:-----------------------------|
| [ecapi.trade.star.update](ecapi.trade.star.update.md)                   | 订单标星接口                   |
| [ecapi.trade.memo.update](ecapi.trade.memo.update.md)                   | 增加修改订单备注               |
| [ecapi.trades.sold.get](ecapi.trades.sold.get.md)                       | 查询卖家已卖出的交易列表        |
| [ecapi.trade.refund.intervene](ecapi.trade.refund.intervene.md)         | 买家申请客服介入               |
| [ecapi.trade.close](ecapi.trade.close.md)                               | 卖家关闭未付款订单             |
| [ecapi.trade.virtualticket.get](ecapi.trade.virtualticket.get.md)       | 获取电子卡券信息               |
| [ecapi.trade.later.receive.update](ecapi.trade.later.receive.update.md) | 订单延长收货接口               |
| [ecapi.trades.sold.outer.get](ecapi.trades.sold.outer.get.md)           | 根据第三方用户id获取交易订单列表 |
| [ecapi.trade.selffetchcode.get](ecapi.trade.selffetchcode.get.md)       | 获取到店自提订单信息            |
| [ecapi.trade.price.update](ecapi.trade.price.update.md)                 | 订单改价                      |
| [ecapi.trade.get](ecapi.trade.get.md)                                   | 获取单笔交易的信息             |
| [ecapi.trade.sign.item.close](ecapi.trade.sign.item.close.md)           | 微信支付-自有订单标记退款       |

### 商品退款

| API                                                                   | 描述                |
|:----------------------------------------------------------------------|:-------------------|
| [ecapi.trade.refund.messages.get](ecapi.trade.refund.messages.get.md) | 查看退款凭证列表接口  |
| [ecapi.trade.returngoods.refuse](ecapi.trade.returngoods.refuse.md)   | 商家拒绝退货         |
| [ecapi.trade.refund.agree](ecapi.trade.refund.agree.md)               | 商家同意退款         |
| [ecapi.trade.refund.refuse](ecapi.trade.refund.refuse.md)             | 商家拒绝退款         |
| [ecapi.trade.returngoods.agree](ecapi.trade.returngoods.agree.md)     | 商家同意退货         |
| [ecapi.trade.refund.get](ecapi.trade.refund.get.md)                   | 查看退款详情         |

### 物流

| API                                                                     | 描述                                                          |
|:------------------------------------------------------------------------|:-------------------------------------------------------------|
| [ecapi.regions.get](ecapi.regions.get.md)                               | 获取区域地名列表信息                                            |
| [ecapi.logistics.local.get](ecapi.logistics.local.get.md)               | 读取商家同城配置的信息                                          |
| [ecapi.logistics.template.update](ecapi.logistics.template.update.md)   | 修改模板信息                                                   |
| [ecapi.logistics.template.get](ecapi.logistics.template.get.md)         | 获取指定模板信息                                               |
| [ecapi.logistics.template.search](ecapi.logistics.template.search.md)   | 获取店铺物流模板列表                                            |
| [ecapi.logistics.online.confirm](ecapi.logistics.online.confirm.md)     | 卖家确认发货                                                   |
| [ecapi.logistics.express.get](ecapi.logistics.express.get.md)           | 获取快递公司的列表                                             |
| [ecapi.logistics.fee.get](ecapi.logistics.fee.get.md)                   | 运费计算                                                      |
| [ecapi.logistics.template.create](ecapi.logistics.template.create.md)   | 创建物流模板                                                   |
| [ecapi.logistics.local.set](ecapi.logistics.local.set.md)               | 设置商家同城配置的信息                                          |
| [ecapi.logistics.setting.get](ecapi.logistics.setting.get.md)           | 获取同成配送的所有开关                                          |
| [ecapi.logistics.template.delete](ecapi.logistics.template.delete.md)   | 删除模板                                                      |
| [ecapi.logistics.setting.update](ecapi.logistics.setting.update.md)     | 设置开关配置 包括:(是否支持自提,是否支持同城,是否支持快递,计费类型)  |
| [ecapi.logistics.goodsexpress.get](ecapi.logistics.goodsexpress.get.md) | 获取物流快递信息                                               |

### 收银台

| API                                                                   | 描述                                      |
|:----------------------------------------------------------------------|:-----------------------------------------|
| [ecapi.pay.qrcodes.get](ecapi.pay.qrcodes.get.md)                     | 获取收款二维码生成记录列表                  |
| [ecapi.trade.qrcode.hasoutid.get](ecapi.trade.qrcode.hasoutid.get.md) | 线下收银台外部订单号查看使用详情             |
| [ecapi.trade.qrcode.ext.create](ecapi.trade.qrcode.ext.create.md)     | 线下收银台创建二维码                        |
| [ecapi.trade.qrlabel.search](ecapi.trade.qrlabel.search.md)           | 获取二维码标签列表                         |
| [ecapi.pay.qrcode.get](ecapi.pay.qrcode.get.md)                       | 二维码详情查询接口                         |
| [ecapi.pay.qrcode.create](ecapi.pay.qrcode.create.md)                 | 创建收款二维码                             |
| [ecapi.trades.qr.get](ecapi.trades.qr.get.md)                         | 查询二维码支付的交易列表，按创建时间的倒序排序 |


### 门店

| API                                                                                 | 描述                              |
|:------------------------------------------------------------------------------------|:---------------------------------|
| [ecapi.multistore.goods.sku.update](ecapi.multistore.goods.sku.update.md)           | 更新网点商品sku                    |
| [ecapi.multistore.goods.delivery.update](ecapi.multistore.goods.delivery.update.md) | 更新网点商品配送方式                |
| [ecapi.multistore.goods.sku.get](ecapi.multistore.goods.sku.get.md)                 | 获取网点商品sku                    |
| [ecapi.multistore.offline.delete](ecapi.multistore.offline.delete.md)               | 删除网点                          |
| [ecapi.multistore.offline.create](ecapi.multistore.offline.create.md)               | 创建网点                          |
| [ecapi.multistore.setting.get](ecapi.multistore.setting.get.md)                     | 获取多门店设置                     |
| [ecapi.multistore.offline.get](ecapi.multistore.offline.get.md)                     | 获取网点详情                       |
| [ecapi.multistore.offline.search](ecapi.multistore.offline.search.md)               | 获取网点列表                       |
| [ecapi.multistore.offline.update](ecapi.multistore.offline.update.md)               | 更新网点                          |
| [ecapi.multistore.goods.delivery.get](ecapi.multistore.goods.delivery.get.md)       | 获取某个网点单个商品的配送方式       |
| [ecapi.multistore.goods.delivery.list](ecapi.multistore.goods.delivery.list.md)     | 获取某个网点所有可配送商品的配送方式  |

### 销售员

| API列表                                                            | 描述                   |
|:------------------------------------------------------------------|:----------------------|
| [ecapi.salesman.item.share.get](ecapi.salesman.item.share.get.md) | 获取商品推广链接        |
| [ecapi.salesman.accounts.get](ecapi.salesman.accounts.get.md)     | 获取店铺销售员列表      |
| [ecapi.salesman.account.add](ecapi.salesman.account.add.md)       | 设置用户为销售员        |
| [ecapi.salesman.customers.get](ecapi.salesman.customers.get.md)   | 获取销售员客户列表      |
| [ecapi.salesman.account.get](ecapi.salesman.account.get.md)       | 获取销售员账户信息      |
| [ecapi.salesman.trades.get](ecapi.salesman.trades.get.md)         | 获取推广订单列表        |
| [ecapi.salesman.items.get](ecapi.salesman.items.get.md)           | 批量获取商品提成比例信息 |

### 核销

| API列表                                                                              | 描述                |
|:------------------------------------------------------------------------------------|:-------------------|
| [ecapi.virtualcode.get](ecapi.virtualcode.get.md)                                   | 核销订单信息         |
| [ecapi.virtualcode.apply](ecapi.virtualcode.apply.md)                               | 使用核销验证码       |
| [ecapi.trade.virtualticket.verifycode](ecapi.trade.virtualticket.verifycode.md)     | 电子卡券整单核销     |
| [ecapi.trade.selffetchcode.apply](ecapi.trade.selffetchcode.apply.md)               | 核销到店自提订单     |
| [ecapi.trade.virtualticket.verifyticket](ecapi.trade.virtualticket.verifyticket.md) | 电子卡券单个码券核销  |

### 买家退款

| API                                                                     | 描述                |
|:------------------------------------------------------------------------|:-------------------|
| [ecapi.trade.refund.modify](ecapi.trade.refund.modify.md)               | 买家修改申请         |
| [ecapi.trade.refund.apply](ecapi.trade.refund.apply.md)                 | 买家申请退款         |
| [ecapi.trade.refund.close](ecapi.trade.refund.close.md)                 | 买家撤销退款申请     |
| [ecapi.trade.returngoods.fill](ecapi.trade.returngoods.fill.md)         | 上传退货物流信息     |
| [ecapi.trade.refund.condition.get](ecapi.trade.refund.condition.get.md) | 退款申请条件信息获取  |

### 会员卡

| API                                                                   | 描述                          |
|:----------------------------------------------------------------------|:-----------------------------|
| [ecapi.scrm.card.create](ecapi.scrm.card.create.md)                   | 商家创建会员卡                 |
| [ecapi.scrm.card.disable](ecapi.scrm.card.disable.md)                 | 商家禁用会员卡                 |
| [ecapi.scrm.card.list](ecapi.scrm.card.list.md)                       | 获取商家会员卡列表             |
| [ecapi.scrm.card.url.get](ecapi.scrm.card.url.get.md)                 | 获取会员卡链接                 |
| [ecapi.scrm.customer.card.list](ecapi.scrm.customer.card.list.md)     | 获取用户的会员卡列表            |
| [ecapi.scrm.customer.card.delete](ecapi.scrm.customer.card.delete.md) | 删除用户的会员卡               |
| [ecapi.scrm.customer.card.grant](ecapi.scrm.customer.card.grant.md)   | 给用户发放会员卡               |
| [ecapi.scrm.customer.search](ecapi.scrm.customer.search.md)           | 会员卡对应的会员列表            |
| [ecapi.scrm.card.get](ecapi.scrm.card.get.md)                         | 通过卡id获取卡详情             |
| [ecapi.scrm.card.update](ecapi.scrm.card.update.md)                   | 更新会员卡                    |
| [ecapi.scrm.customer.get](ecapi.scrm.customer.get.md)                 | 获取会员详情                   |
| [ecapi.scrm.customer.info.get](ecapi.scrm.customer.info.get.md)       | 通过用户会员卡号获取会员概要信息 |
| [ecapi.scrm.card.enable](ecapi.scrm.card.enable.md)                   | 商家启用会员卡                 |


## 错误码

目前开发者调用API可能出现的错误有两类：全局错误、业务错误。

1、全局错误

错误码小于100(不包含15,40,41错误码)的调用错误，这种错误一般是由于用户的请求不符合各种基本校验而引起的。用户遇到这些错误的返回首先检查应用的权限、频率等情况，然后参照文档检验一下传入的参数是否完整且合法。

2、业务错误

业务级错误是传入的参数缺失，有误或格式错误等原因造成的错误。因此开发者应该根据错误信息检验是否传入了相应的信息，对于这一类错误建议改正后再重试。错误响应是用户和服务器交互失败的最直接展示，在调用API服务时，如果调用失败，请尽量保留下错误日志以便进行后面的错误追查。

3、全局错误返回码

| 错误码 | 错误描述               | 解决方案                                                              |
|:------|:----------------------|:---------------------------------------------------------------------|
| -1    | 系统错误               | 系统内部错误，请直接联系技术支持，或邮件给网站管理员                        |
| 40001 | 未指定AppId            | 请求时传入AppId                                                        |
| 40002 | 无效的App              | 填写有效的AppId                                                        |
| 40003 | 无效的时间参数          | 以当前时间重新发起请求；如果系统时间和服务器时间误差超过10分钟，请调整系统时间 |
| 40004 | 请求没有签名           | 请使用协议规范对请求中的参数进行签名                                      |
| 40005 | 签名校验失败           | 检查 AppId 和 AppSecret 是否正确；如果是自行开发的协议封装，请检查代码      |
| 40006 | 未指定请求的API接口名称 | 指定API接口名称                                                        |
| 40007 | 请求非法的API接口名称   | 检查请求的API接口名称的值                                               |

4、错误返回结果

| 名称    | 类型    | 是否必须 | 描述         |
|:-------|:-------|:--------|:------------|
| code   | Number | 是      | 错误编号     |
| msg    | String | 是      | 错误信息     |
| params | List   | 是      | 请求参数列表  |

5、返回结果示例

```
{
    "error_response": {
        "code": 40002,
        "msg": "invalid app",
        "params": {
            "app_id": "6000800060008000",
            "method": "ecapi.goods.get",
            "timestamp": "2016-01-20 20:38:42",
            "format": "json",
            "sign_method": "md5",
            "v": "2.0",
            "sign": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
        }
    }
}
```

<a name="notice"></a>

## 注意事项

所有的请求和响应数据编码皆为utf-8格式，URL里的所有参数名和参数值请做URL编码。如果请求的Content-Type是application/x-www-form-urlencoded，则HTTP
Body体里的所有参数值也做URL编码；如果是multipart/form-data格式，每个表单字段的参数值无需编码，但每个表单字段的charset部分需要指定为utf-8。

参数名与参数值拼装起来的URL长度小于1024个字符时，可以用GET发起请求；参数类型含byte[]类型或拼装好的请求URL过长时，必须用POST发起请求。所有API都可以用POST发起请求。

订单号生成规则：
> php: time() . str_pad($user_id, 4, '0', STR_PAD_LEFT) . rand(0000, 9999);

