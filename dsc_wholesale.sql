/*
MySQL Data Transfer
Source Host: localhost
Source Database: 11
Target Host: localhost
Target Database: 11
Date: 2017/10/26 16:39:55
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for dsc_wholesale
-- ----------------------------
DROP TABLE IF EXISTS `dsc_wholesale`;
CREATE TABLE `dsc_wholesale` (
  `act_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `goods_id` mediumint(8) unsigned NOT NULL,
  `goods_name` varchar(255) NOT NULL,
  `rank_ids` varchar(255) NOT NULL,
  `prices` text NOT NULL,
  `enabled` tinyint(3) unsigned NOT NULL,
  `review_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `review_content` varchar(1000) NOT NULL,
  PRIMARY KEY (`act_id`),
  KEY `goods_id` (`goods_id`),
  KEY `review_status` (`review_status`)
) ENGINE=MyISAM AUTO_INCREMENT=116 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `dsc_wholesale` VALUES ('113', '0', '785', '森马夹克 2016冬装新款男士飞行夹克贴布绣立领休闲外套韩版潮流', '6,7,8,3', 'a:1:{i:0;a:2:{s:4:\"attr\";a:0:{}s:7:\"qp_list\";a:2:{i:0;a:2:{s:8:\"quantity\";i:30;s:5:\"price\";d:200;}i:1;a:2:{s:8:\"quantity\";i:50;s:5:\"price\";d:180;}}}}', '1', '3', '');
INSERT INTO `dsc_wholesale` VALUES ('114', '0', '781', '宝石蝶真丝围巾女士春秋季高档丝巾重磅桑蚕丝大方巾披肩丝绸礼品 16姆米重磅真丝丝滑缎面90方巾礼盒包装', '6,7,8,3', 'a:1:{i:0;a:2:{s:4:\"attr\";a:0:{}s:7:\"qp_list\";a:1:{i:0;a:2:{s:8:\"quantity\";i:50;s:5:\"price\";d:100;}}}}', '1', '3', '');
INSERT INTO `dsc_wholesale` VALUES ('115', '0', '786', '裤子男士哈伦裤春季新款2017束脚裤修身韩版潮流小脚裤男裤休闲裤 弹力', '6,7,8,3', 'a:1:{i:0;a:2:{s:4:\"attr\";a:0:{}s:7:\"qp_list\";a:2:{i:0;a:2:{s:8:\"quantity\";i:10;s:5:\"price\";d:150;}i:1;a:2:{s:8:\"quantity\";i:20;s:5:\"price\";d:140;}}}}', '1', '3', '');
