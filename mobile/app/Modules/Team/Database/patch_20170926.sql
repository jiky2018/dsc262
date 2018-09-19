--
--  增加 `dsc_team_log`  字段
--

ALTER TABLE  `dsc_team_log` ADD  `t_id` mediumint(8) NOT NULL  COMMENT  '拼团活动id' AFTER  `goods_id` ;


