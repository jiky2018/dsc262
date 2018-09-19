<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

$purview['set_gcolor'] = 'set_gcolor';
$purview['user_keywords'] = 'user_keywords';
$purview['01_goods_list'] = array('goods_manage', 'remove_back');
$purview['02_goods_add'] = 'goods_manage';
$purview['03_category_list'] = $purview['user_keywords_list'] = array('cat_drop');
$purview['03_store_category_list'] = 'cat_manage';
$purview['05_comment_manage'] = 'comment_priv';
$purview['06_goods_brand_list'] = 'brand_manage';
$purview['08_goods_type'] = 'attr_manage';
$purview['11_goods_trash'] = array('goods_manage', 'remove_back');
$purview['12_batch_pic'] = 'picture_batch';
$purview['13_batch_add'] = 'goods_batch';
$purview['14_goods_export'] = 'goods_export';
$purview['15_batch_edit'] = 'goods_batch';
$purview['16_goods_script'] = 'gen_goods_script';
$purview['17_tag_manage'] = 'tag_manage';
$purview['50_virtual_card_list'] = 'virualcard';
$purview['51_virtual_card_add'] = 'virualcard';
$purview['52_virtual_card_change'] = 'merch_virualcard';
$purview['goods_auto'] = 'goods_auto';
$purview['seller_service_rank'] = 'seller_service';
$purview['discuss_circle'] = 'discuss_circle';
$purview['website'] = 'website';
$purview['18_comment_edit_delete'] = 'comment_edit_delete';
$purview['comment_seller_rank'] = 'comment_seller';
$purview['area_attr_batch'] = 'goods_manage';
$purview['area_batch'] = 'goods_manage';
$purview['warehouse_batch'] = 'goods_manage';
$purview['sale_notice'] = 'sale_notice';
$purview['notice_logs'] = 'notice_logs';
$purview['gallery_album'] = 'gallery_album';
$purview['02_snatch_list'] = 'snatch_manage';
$purview['03_seckill_list'] = 'seckill_manage';
$purview['04_bonustype_list'] = 'bonus_manage';
$purview['06_pack_list'] = 'goods_pack';
$purview['07_card_list'] = 'card_manage';
$purview['08_group_buy'] = 'group_by';
$purview['09_topic'] = 'topic_manage';
$purview['10_auction'] = 'auction';
$purview['12_favourable'] = 'favourable';
$purview['13_wholesale'] = 'whole_sale';
$purview['14_package_list'] = 'package_manage';
$purview['gift_gard_list'] = 'gift_gard_manage';
$purview['take_list'] = 'take_manage';
$purview['15_exchange_goods'] = 'exchange_goods';
$purview['16_presale'] = 'presale';
$purview['17_coupons'] = 'coupons_manage';

if (file_exists(MOBILE_TEAM)) {
	$purview['18_team'] = 'team_manage';
}

if (file_exists(MOBILE_BARGAIN)) {
	$purview['19_bargain'] = 'bargain_manage';
}

$purview['02_articlecat_list'] = 'article_cat';
$purview['03_article_list'] = 'article_manage';
$purview['article_auto'] = 'article_auto';
$purview['vote_list'] = 'vote_priv';
$purview['03_users_list'] = 'users_manage';
$purview['04_users_add'] = 'users_manage';
$purview['11_users_add'] = 'users_manage';
$purview['05_user_rank_list'] = 'user_rank';
$purview['09_user_account'] = 'surplus_manage';
$purview['06_list_integrate'] = 'integrate_users';
$purview['08_unreply_msg'] = 'feedback_priv';
$purview['10_user_account_manage'] = 'account_manage';
$purview['12_user_address_list'] = 'users_manage';
$purview['13_user_baitiao_info'] = 'baitiao_manage';
$purview['admin_logs'] = array('logs_manage', 'logs_drop');
$purview['01_admin_list'] = array('admin_manage', 'admin_drop', 'allot_priv');
$purview['02_admin_seller'] = array('seller_manage', 'seller_drop', 'seller_allot');

if (is_dir(MOBILE_KEFU)) {
	$purview['kefu_list'] = array('seller_manage', 'seller_drop', 'seller_allot');
}

$purview['agency_list'] = 'agency_manage';

if (judge_supplier_enabled()) {
	$purview['01_suppliers_list'] = 'suppliers_manage';
}
else {
	$purview['suppliers_list'] = 'suppliers_manage';
}

$purview['admin_role'] = 'role_manage';
$purview['privilege_seller'] = 'privilege_seller';
$purview['admin_message'] = 'admin_message';
$purview['01_shop_config'] = $purview['user_keywords_list'] = 'shop_config';
$purview['shop_authorized'] = 'shop_authorized';
$purview['shp_webcollect'] = 'webcollect_manage';
$purview['02_payment_list'] = 'payment';
$purview['03_shipping_list'] = array('ship_manage', 'shiparea_manage');
$purview['05_area_list'] = 'area_list';
$purview['07_cron_schcron'] = 'cron';
$purview['08_friendlink_list'] = 'friendlink';
$purview['sitemap'] = 'sitemap';
$purview['check_file_priv'] = 'file_priv';
$purview['captcha_manage'] = 'shop_config';
$purview['file_check'] = 'file_check';
$purview['navigator'] = 'navigator';
$purview['flashplay'] = 'flash_manage';
$purview['ucenter_setup'] = 'integrate_users';
$purview['16_reg_fields'] = 'reg_fields';
$purview['oss_configure'] = 'oss_configure';
$purview['09_warehouse_management'] = 'warehouse_manage';
$purview['09_region_area_management'] = 'region_area';
$purview['shipping_date_list'] = 'shipping_date_list';
$purview['z_clicks_stats'] = 'ad_manage';
$purview['ad_position'] = 'ad_manage';
$purview['ad_list'] = 'ad_manage';
$purview['13_goods_inventory_logs'] = 'order_view';
$purview['02_order_list'] = 'order_view';
$purview['03_order_query'] = 'order_view';
$purview['04_merge_order'] = 'order_os_edit';
$purview['06_undispose_booking'] = 'booking';
$purview['08_add_order'] = 'order_edit';
$purview['09_delivery_order'] = 'delivery_view';
$purview['10_back_order'] = 'back_view';
$purview['11_complaint'] = 'complaint';
$purview['11_add_order'] = 'batch_add_order';
$purview['11_back_cause'] = 'order_back_cause';
$purview['12_back_apply'] = 'order_back_apply';
$purview['05_edit_order_print'] = 'order_print';
$purview['11_order_detection'] = 'order_detection';
$purview['11_order_delayed'] = 'order_delayed';
$purview['flow_stats'] = 'users_flow_stats';
$purview['report_guest'] = 'client_report_guest';
$purview['report_users'] = 'client_flow_stats';
$purview['visit_buy_per'] = 'client_flow_stats';
$purview['searchengine_stats'] = 'client_searchengine';
$purview['report_order'] = 'sale_order_stats';
$purview['report_sell'] = 'sale_order_stats';
$purview['sale_list'] = 'sale_order_stats';
$purview['sell_stats'] = 'sale_order_stats';
$purview['02_template_select'] = 'template_select';
$purview['03_template_setup'] = 'template_setup';
$purview['04_template_library'] = 'library_manage';
$purview['05_edit_languages'] = 'lang_edit';
$purview['06_template_backup'] = 'backup_setting';
$purview['mail_template_manage'] = 'mail_template';
$purview['02_db_manage'] = array('db_backup', 'db_renew');
$purview['03_db_optimize'] = 'db_optimize';
$purview['04_sql_query'] = 'sql_query';
$purview['convert'] = 'convert';
$purview['02_sms_my_info'] = 'my_info';
$purview['03_sms_send'] = 'sms_send';
$purview['04_sms_charge'] = 'sms_charge';
$purview['05_sms_send_history'] = 'send_history';
$purview['06_sms_charge_history'] = 'charge_history';
$purview['affiliate'] = 'affiliate';
$purview['affiliate_ck'] = 'affiliate_ck';
$purview['07_merchants_brand'] = 'merchants_brand';
$purview['01_merchants_steps_list'] = 'merchants_setps';
$purview['02_merchants_users_list'] = 'users_merchants';
$purview['03_merchants_commission'] = 'merchants_commission';
$purview['03_merchants_percent'] = 'merchants_percent';
$purview['03_users_merchants_priv'] = 'users_merchants_priv';
$purview['09_seller_domain'] = 'seller_dimain';
$purview['10_account_manage'] = 'seller_account';
$purview['04_create_seller_grade'] = 'create_seller_grade';
$purview['01_oauth_admin'] = 'oauth_admin';
$purview['02_touch_nav_admin'] = 'touch_nav_admin';
$purview['03_touch_ads'] = 'touch_ad';
$purview['04_touch_ad_position'] = 'touch_ad_position';
$purview['01_cloud_services'] = 'cloud_services';
$purview['01_merchants_basic_info'] = 'seller_store_informa';
$purview['08_merchants_template'] = 'seller_store_other';
$purview['07_merchants_window'] = 'seller_store_other';
$purview['06_merchants_custom'] = 'seller_store_other';
$purview['05_merchants_shop_bg'] = 'seller_store_other';
$purview['04_merchants_basic_nav'] = 'seller_store_other';
$purview['03_merchants_shop_top'] = 'seller_store_other';
$purview['02_merchants_ad'] = 'seller_store_other';
$purview['09_merchants_upgrade'] = 'seller_store_other';
$purview['12_apply_suppliers'] = 'supplier_apply';
$purview['09_merchants_upgrade'] = 'seller_store_other';
$purview['10_visual_editing'] = '10_visual_editing';
$purview['11_touch_dashboard'] = 'touch_dashboard';
$purview['12_offline_store'] = 'offline_store';
$purview['2_order_stats'] = 'offline_store';

if (file_exists(MOBILE_WECHAT)) {
	$purview['01_wechat_admin'] = 'wechat_admin';
	$purview['02_mass_message'] = 'mass_message';
	$purview['03_auto_reply'] = 'auto_reply';
	$purview['04_menu'] = 'menu';
	$purview['05_fans'] = 'fans';
	$purview['06_media'] = 'media';
	$purview['07_qrcode'] = 'qrcode';
	$purview['09_extend'] = 'extend';
	$purview['10_market'] = 'market';
}

if (!judge_supplier_enabled()) {
	$purview['supply_and_demand'] = 'supply_and_demand';
	$purview['02_wholesale_order'] = 'wholesale_order';
	$purview['01_wholesale'] = 'whole_sale';
}

$purview['04_goods_lib_list'] = 'goods_lib_list';
$purview['order_print_setting'] = 'order_print_setting';

?>
