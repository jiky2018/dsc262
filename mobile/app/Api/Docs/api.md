##6-15
api文档的全局文档
http://git.oschina.net/dscmall/mobile/tree/develop/app/api/docs
示例文档
https://git.oschina.net/dscmall/mobile/blob/develop/app/api/docs/ecapi.shop.get.md\
http://dscmall.cn/api/   api接口文档


##api开发
ecapi.shop.get （商家店铺详情）
ecapi.category.list （商家商品分类）
ecapi.category.get （商家商品分类详情）
ecapi.brand.list （品牌列表）
ecapi.brand.get （品牌详情）


##文件
1. app\api\foundation\Validation.php    接口数据验证类
2. app\api\foundation\TestAllApis.php    接口测试接口类
3. app\api\v2\index\controllers\Guide.php   测试接口控制器  （待定）
4. app\http\test\controllers\Index.php    测试显示控制器
5. resources\views\test\index.html   测试显示页面
    > http://10.10.10.145/dsc/mobile/index.php?m=test  手机端访问测试页面

##日志
1. $logger = ApiLogger::init(arg1, arg2);   参数1 日志名（日志文件中显示）  参数2  日志等级
    > 等级说明   debug < info < notice < warning < error   由低到高  共5种
    > 声明日志对象为低等级时   可以使用 高等级方法记录   如  声明为debug  以上4种都可记录
    > 声明日志对象为高等级时   不可使用 低等级方法记录   如  声明为error  下面4种都不会被写入日志
    > $logger->debug($arg)  记录 调试信息
    > $logger->info($arg)  记录 信息
    > $logger->notice($arg)  记录 提醒
    > $logger->warning($arg)  记录 警告
    > $logger->error($arg)  记录 错误
    > $arg  为字符串

2. ApiLogger::setLogFile(arg1);   设置日志文件  参数1  日志文件路径
3. ApiLogger::getLogFile();  获取日志文件路径  用于查看日志路径


##数据验证
1. 验证规则格式 
    > $pattern=[
    >    "shop" => "integer|min:1",          // 店铺ID
    >    "consignee" => "required|integer|min:1", // 收货人ID
    >    "score" => "integer",                 // 积分
    >    "property" => "required|string",         // 用户选择的属性ID
    > ] 
    > 格式为数组
    > 键名 为 需要验证的参数名
    > 键值 为 需要验证的具体格式  以 | 间隔 最多三种验证
    > required  为必须给出 此参数
    > integer   为参数类型  为 整形
    > string    为参数类型  为 字符串

2. 使用  $this->check($args, $pattern);
    > $args  为请求参数
    > $pattern  为验证规则
    > 验证通过  返回true
    > 验证不通过   返回错误信息
