--
-- 表的结构 `拼团信息表 {pre}team_goods`
--
CREATE TABLE IF NOT EXISTS `{pre}team_goods` (
	`id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '拼团商品列表id',
	`goods_id` mediumint(8) NOT NULL  COMMENT '拼团商品id',
	`team_price` DECIMAL( 10, 2 ) NOT NULL DEFAULT  '0.00' COMMENT '拼团商品价格',
	`team_num` int(10) COMMENT '几人团',
	`validity_time` int(10) COMMENT '开团有效期(小时)',
	`limit_num` int(10) DEFAULT '0' COMMENT '已参团人数(添加虚拟数量)',
	`astrict_num` int(10) COMMENT '限购数量',
	`tc_id` mediumint(8) NOT NULL  COMMENT '频道id',
	`is_audit` tinyint(10) NOT NULL DEFAULT '0' COMMENT '0未审核，1未通过，2通过',
	`is_team` tinyint(10) NOT NULL DEFAULT '1' COMMENT '显示0否 1显示',	
	`sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
	
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- 表的结构 `开团记录信息表{pre}team_log`
--

CREATE TABLE IF NOT EXISTS `{pre}team_log` (
	`team_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '开团记录id',
	`goods_id` mediumint(8) NOT NULL  COMMENT '拼团商品id',
	`start_time` int(10) COMMENT '开团时间',
	`status` tinyint(10) NOT NULL DEFAULT '0' COMMENT '拼团状态（1成功，2失败）',
	`is_show` int(10) NOT NULL DEFAULT '1' COMMENT '是否显示',
	PRIMARY KEY (`team_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- 表的结构 `拼团信息表 {pre}order_info 增加以下字段`
--

ALTER TABLE  `{pre}order_info` ADD  `team_id` INT( 10 ) NOT NULL  COMMENT  '开团记录id' AFTER  `drp_is_separate` ,
ADD  `team_parent_id` mediumint(8) NOT NULL COMMENT  '团长id' AFTER  `team_id` ,
ADD  `team_user_id` mediumint(8) NOT NULL COMMENT  '团员id' AFTER  `team_parent_id` ,
ADD  `team_price` DECIMAL( 10, 2 ) NOT NULL DEFAULT  '0.00' COMMENT  '拼团商品价格' AFTER  `team_user_id`;

--
-- 表的结构 `频道表{pre}team_category`
--

CREATE TABLE IF NOT EXISTS `{pre}team_category` (
	`id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '频道id',
	`name` varchar(255) DEFAULT NULL COMMENT '频道名称',
	`parent_id` int(10) NOT NULL DEFAULT '0' COMMENT '父级id',
	`content` varchar(120) DEFAULT NULL COMMENT '频道描述',
	`tc_img` varchar(255) DEFAULT NULL COMMENT '频道图标',
	`sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
	`status` tinyint(10) NOT NULL DEFAULT '1' COMMENT '显示0否 1显示',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- 转存表中的数据 `{pre}team_category`
--

INSERT INTO `{pre}team_category` (`id`, `name`, `parent_id`, `content`, `tc_img`, `sort_order`, `status`) VALUES
(1, '生鲜', 0, '', '', 0, 1),
(2, '服装', 0, '', '', 0, 1),
(3, '美妆', 0, NULL, '', 0, 1),
(4, '母婴', 0, NULL, '', 0, 1),
(5, '数码', 0, NULL, '', 0, 1),
(6, '电器', 0, NULL, '', 0, 1),
(7, '水果', 1, '', 'data/team_img/1477003670262146374.jpg', 0, 1),
(8, '海鲜', 1, '', 'data/team_img/1477003723558440986.jpg', 0, 1),
(9, '蔬菜', 1, '', 'data/team_img/1477003737543093730.jpg', 0, 1),
(10, '肉类', 1, '', 'data/team_img/1477003765554186648.jpg', 0, 1),
(11, '半身裙', 2, '', 'data/team_img/1476238976644478183.jpg', 0, 1),
(12, '小衫', 2, '', 'data/team_img/1476238818094280453.jpg', 0, 1),
(13, '裤子', 2, '', 'data/team_img/1476238847669867996.jpg', 0, 1),
(14, '套装', 2, '', 'data/team_img/1476238872975750020.jpg', 0, 1),
(15, '天然面膜', 3, '', 'images/201610/1477003847639238159.jpg', 0, 1),
(16, '唇彩口红', 3, '', 'images/201610/1477003868289504448.jpg', 0, 1),
(17, '保湿面乳', 3, '', 'images/201610/1477003883140316778.jpg', 0, 1),
(18, '时尚香水', 3, '', 'images/201610/1477003989942279800.jpg', 0, 1),
(19, '婴儿起居', 4, '', 'images/201610/1475967205858355624.jpg', 1, 1),
(20, '妈咪护理', 4, '', 'images/201610/1475967168002105903.jpg', 2, 1),
(21, '婴儿洗护', 4, '', 'images/201610/1475967135212140877.jpg', 3, 1),
(22, '智力开发', 4, '', 'images/201610/1475967092323697897.jpg', 4, 1),
(23, '数码相机', 5, '', 'images/201610/1475967219850127489.jpg', 1, 1),
(24, '电脑配件', 5, '', 'images/201610/1475967180221500573.jpg', 2, 1),
(25, '智能设备', 5, '', 'images/201610/1475967146915398463.jpg', 3, 1),
(26, '智能配件', 5, '', 'images/201610/1475967109605446878.jpg', 4, 1),
(27, '厨房电器', 6, '', 'images/201610/1475967232110271481.jpg', 1, 1),
(28, '生活电器', 6, '', 'images/201610/1475967192886890302.jpg', 2, 1),
(29, '个人护理', 6, '', 'images/201610/1475967156765457477.jpg', 3, 1),
(30, '影音电器', 6, '', 'images/201610/1475967122572998827.jpg', 4, 1);

--
-- 表的结构 `广告位置表 {pre}touch_ad_position 增加以下字段`
--

ALTER TABLE  `{pre}touch_ad_position` ADD  `tc_id` INT( 10 ) NOT NULL  COMMENT  '频道id' AFTER  `theme` ,
ADD  `tc_type` varchar(120) NOT NULL COMMENT  '广告类型' AFTER  `tc_id`;

--
-- 转存表中的数据 `dsc_touch_ad_position`
--

INSERT INTO `{pre}touch_ad_position` (`position_id`, `user_id`, `position_name`, `ad_width`, `ad_height`, `position_desc`, `position_style`, `is_public`, `theme`, `tc_id`, `tc_type`) VALUES 
(259, 0, '生鲜-banner', 360, 168, '', '{foreach $ads as $ad}<div class="swiper-slide">{$ad}</div>{/foreach}\r\n', 0, 'ecmoban_dsc', 1, 'banner'),
(260, 0, '生鲜-left', 360, 168, '', '{foreach $ads as $ad}\r\n{$ad}\r\n{/foreach}', 0, 'ecmoban_dsc', 1, 'left'),
(261, 0, '生鲜-right', 360, 168, '', '{foreach $ads as $ad}\r\n{$ad}\r\n{/foreach}', 0, 'ecmoban_dsc', 1, 'right'),
(264, 0, '服装-left', 360, 168, '', '{foreach $ads as $ad}\r\n{$ad}\r\n{/foreach}', 0, 'ecmoban_dsc', 2, 'left'),
(262, 0, '生鲜-bottom', 360, 168, '', '{foreach $ads as $ad}\r\n{$ad}\r\n{/foreach}', 0, 'ecmoban_dsc', 1, 'bottom'),
(263, 0, '服装-banner', 360, 168, '', '{foreach $ads as $ad}<div class="swiper-slide">{$ad}</div>{/foreach}\n', 0, 'ecmoban_dsc', 2, 'banner'),
(265, 0, '服装-right', 360, 168, '', '{foreach $ads as $ad}\r\n{$ad}\r\n{/foreach}', 0, 'ecmoban_dsc', 2, 'right'),
(266, 0, '服装-bottom', 360, 168, '', '{foreach $ads as $ad}\r\n{$ad}\r\n{/foreach}', 0, 'ecmoban_dsc', 2, 'bottom');


--
-- 转存表中的数据 `dsc_touch_ad`
--

INSERT INTO `{pre}touch_ad` (`ad_id`, `position_id`, `media_type`, `ad_name`, `ad_link`, `link_color`, `ad_code`, `start_time`, `end_time`, `link_man`, `link_email`, `link_phone`, `click_count`, `enabled`, `is_new`, `is_hot`, `is_best`, `public_ruid`, `ad_type`, `goods_name`) VALUES
(4, 259, 0, '生鲜-banner001', '', '', '1481672349255154283.jpg', 1481585927, 1490916876, '', '', '', 0, 1, 0, 0, 0, 0, 0, '0'),
(5, 259, 0, '生鲜-banner001', '', '', '1481672451859296675.jpg', 1480981215, 1489534815, '', '', '', 1, 1, 0, 0, 0, 0, 0, '0'),
(6, 260, 0, '生鲜-left', '', '', '1481672545602467804.jpg', 1480376515, 1493336515, '', '', '', 2, 1, 0, 0, 0, 0, 0, '0'),
(7, 261, 0, '生鲜-right001', '', '', '1481672619284617438.jpg', 1481586212, 1490830985, '', '', '', 1, 1, 0, 0, 0, 0, 0, '0'),
(8, 261, 0, '生鲜-right002', '', '', '1481672758685877435.jpg', 1481499940, 1495669540, '', '', '', 0, 1, 0, 0, 0, 0, 0, '0'),
(9, 262, 0, '生鲜-bottom', '', '', '1481679017761272361.jpg', 1480987778, 1496798978, '', '', '', 0, 1, 0, 0, 0, 0, 0, '0'),
(10, 266, 0, '服装-bottom', '', '', '1481844007547575973.jpg', 1481671178, 1513811964, '', '', '', 0, 1, 0, 0, 0, 0, 0, '0'),
(11, 265, 0, '服装-right', '', '', '1481844097616183068.jpg', 1480375228, 1548285628, '', '', '', 0, 1, 0, 0, 0, 0, 0, '0'),
(12, 265, 0, '服装-right02', '', '', '1481844124397414040.jpg', 1480980100, 1548285700, '', '', '', 0, 1, 0, 0, 0, 0, 0, '0'),
(13, 264, 0, '服装-left', '', '', '1481844211194785256.jpg', 1480893791, 1548112991, '', '', '', 1, 1, 0, 0, 0, 0, 0, '0'),
(14, 263, 0, '服装-banner', '', '', '1481844261695460726.jpg', 1480893834, 1577229853, '', '', '', 0, 1, 0, 0, 0, 0, 0, '0');


