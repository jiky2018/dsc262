<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include 'TopSdk.php';
date_default_timezone_set('Asia/Shanghai');
$c = new TopClient();
$c->appkey = '23371193';
$c->secretKey = 'b651d80053d94a1c44110f9ea99c49ff';
$req = new OpenimUsersAddRequest();
$userinfos = new Userinfos();
$userinfos->nick = '简简单单';
$userinfos->icon_url = 'http://xxx.com/xxx';
$userinfos->email = 'uid@taobao.com';
$userinfos->mobile = '18600000000';
$userinfos->taobaoid = 'test12';
$userinfos->userid = 'bbb';
$userinfos->password = '简简单单';
$userinfos->remark = 'demo';
$userinfos->extra = '{}';
$userinfos->career = 'demo';
$userinfos->vip = '{}';
$userinfos->address = 'demo';
$userinfos->name = 'demo';
$userinfos->age = '123';
$userinfos->gender = 'M';
$userinfos->wechat = 'demo';
$userinfos->qq = 'demo';
$userinfos->weibo = 'demo';
$req->setUserinfos(json_encode($userinfos));
$resp = $c->execute($req);
echo '<pre>';
echo '<pre>';
print_r($resp);

?>
