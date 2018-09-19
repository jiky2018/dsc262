
--
-- 转存表中的数据 `dsc_admin_action` 权限
--
INSERT INTO `dsc_admin_action` VALUES ('', '7', 'team_manage', '', '1');

--
-- 转存表中的数据 `dsc_shop_config`  是否显示首页订单提示轮播
--
INSERT INTO `dsc_shop_config` (`parent_id`, `code`, `type`, `store_range`, `store_dir`, `value`, `sort_order` ) VALUES  ('2', 'virtual_order', 'select', '0,1', '', '0', '1');
INSERT INTO `dsc_shop_config` (`parent_id`, `code`, `type`, `store_range`, `store_dir`, `value`, `sort_order` ) VALUES  ('2', 'virtual_limit_nim', 'select', '0,1', '', '0', '1');

--
-- 拼团导航
--
INSERT INTO  `dsc_touch_nav` (`id` ,`ctype` ,`cid` ,`name` ,`ifshow` ,`vieworder` ,`opennew` ,`url` ,`pic` ,`type`)VALUES (
NULL , NULL , '0' ,  '拼团',  '1',  '11',  '',  'index.php?r=team',  'pintuan.png',  'top');
