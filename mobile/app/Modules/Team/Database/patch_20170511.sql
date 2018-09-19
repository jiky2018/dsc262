--
--  增加拼团模板消息 `dsc_wechat_template` 
--

INSERT INTO `dsc_wechat_template` VALUES ('', '', 'OPENTM407307456', null, '{{first.DATA}}\r\n商品名称：{{keyword1.DATA}}\r\n商品价格：{{keyword2.DATA}}\r\n组团人数：{{keyword3.DATA}}\r\n拼团类型：{{keyword4.DATA}}\r\n组团时间：{{keyword5.DATA}}\r\n{{remark.DATA}}', '开团成功通知', '1494467185', '0', '1');

INSERT INTO `dsc_wechat_template` VALUES ('', '', 'OPENTM400048581', null, '{{first.DATA}}\r\n拼团名：{{keyword1.DATA}}\r\n拼团价：{{keyword2.DATA}}\r\n有效期：{{keyword3.DATA}}\r\n{{remark.DATA}}', '参团成功通知', '1494467185', '0', '1');

INSERT INTO `dsc_wechat_template` VALUES ('', '', 'OPENTM407456411', null, '{{first.DATA}}\r\n订单编号：{{keyword1.DATA}}\r\n团购商品：{{keyword2.DATA}}\r\n{{remark.DATA}}', '拼团成功通知', '1494467185', '0', '1');

INSERT INTO `dsc_wechat_template` VALUES ('', '', 'OPENTM400940587', null, '{{first.DATA}}\r\n单号：{{keyword1.DATA}}\r\n商品：{{keyword2.DATA}}\r\n原因：{{keyword3.DATA}}\r\n退款：{{keyword4.DATA}}\r\n{{remark.DATA}}', '拼团退款通知', '1494467185', '0', '1');


