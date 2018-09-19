--
--  增加 `dsc_team_goods`  字段
--

ALTER TABLE  `dsc_team_goods` ADD  `team_desc` varchar(255) NOT NULL  COMMENT  '拼团介绍' AFTER  `sort_order` ;

ALTER TABLE  `dsc_team_goods` ADD  `isnot_aduit_reason` varchar(255) NOT NULL  COMMENT  '审核未通过说明' AFTER  `team_desc` ;

