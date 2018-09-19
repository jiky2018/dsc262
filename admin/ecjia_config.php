<?php
//商创网络  QQ:123456  禁止倒卖 一经发现停止任何服务
function ecjia_config($code)
{
	$sql = 'SELECT value FROM ' . $GLOBALS['ecs']->table('shop_config') . (' WHERE code = \'' . $code . '\' ');
	return $GLOBALS['db']->getOne($sql);
}

function update_config($code, $value)
{
	$sql = 'UPDATE ' . $GLOBALS['ecs']->table('shop_config') . (' SET value = \'' . $value . '\' WHERE code = \'' . $code . '\' ');
	$GLOBALS['db']->query($sql);
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
include_once ROOT_PATH . 'includes/cls_image.php';
$image = new cls_image($_CFG['bgcolor']);

if ($_REQUEST['act'] == 'list') {
	$filter = array();
	$smarty->assign('ur_here', '应用配置');
	$shop_app_icon = ecjia_config('shop_app_icon') ? ROOT_PATH . ecjia_config('shop_app_icon') : '';
	$smarty->assign('shop_app_icon', ecjia_config('shop_app_icon'));
	$smarty->assign('shop_app_description', ecjia_config('shop_app_description'));
	$smarty->assign('bonus_readme_url', ecjia_config('bonus_readme_url'));
	$smarty->assign('mobile_feedback_autoreply', ecjia_config('mobile_feedback_autoreply'));
	$smarty->assign('mobile_shopkeeper_urlscheme', ecjia_config('mobile_shopkeeper_urlscheme'));
	$smarty->assign('shop_pc_url', ecjia_config('shop_pc_url'));
	$smarty->assign('mobile_share_link', ecjia_config('mobile_share_link'));
	$time = gmtime();
	$sql = 'SELECT type_id, type_name FROM ' . $GLOBALS['ecs']->table('bonus_type') . (' WHERE use_start_date < \'' . $time . '\' AND \'' . $time . '\' < use_end_date ');
	$bonus_list = $db->getAll($sql);
	$bonus_select = '';

	foreach ($bonus_list as $key => $value) {
		$bonus_select .= '<li><a href="javascript:;" data-value="' . $value['type_id'] . '" class="ftx-01">' . $value['type_name'] . '</a></li>';
	}

	$smarty->assign('bonus_select', $bonus_select);
	$bonus_id = ecjia_config('mobile_signup_reward');
	$bonus_name = $db->getOne('SELECT type_name FROM ' . $ecs->table('bonus_type') . (' WHERE type_id = \'' . $bonus_id . '\' '));
	$smarty->assign('mobile_signup_reward', $bonus_id);
	$smarty->assign('mobile_signup_reward_notice', ecjia_config('mobile_signup_reward_notice'));
	$smarty->assign('mobile_iphone_qr_code', ecjia_config('mobile_iphone_qr_code'));
	$smarty->assign('shop_iphone_download', ecjia_config('shop_iphone_download'));
	$smarty->assign('mobile_android_qr_code', ecjia_config('mobile_android_qr_code'));
	$smarty->assign('shop_android_download', ecjia_config('shop_android_download'));
	$smarty->assign('mobile_ipad_qr_code', ecjia_config('mobile_ipad_qr_code'));
	$smarty->assign('shop_ipad_download', ecjia_config('shop_ipad_download'));
	$sql = 'SELECT ad_id, ad_name FROM ' . $ecs->table('ad') . (' WHERE start_time < \'' . $time . '\' AND \'' . $time . '\' < end_time');
	$ad_list = $db->getAll($sql);
	$smarty->assign('ad_list', $ad_list);
	$mobile_launch_select = '';

	foreach ($ad_list as $key => $value) {
		$mobile_launch_select .= '<li><a href="javascript:;" data-value="' . $value['ad_id'] . '" class="ftx-01">' . $value['ad_name'] . '</a></li>';
	}

	$smarty->assign('mobile_launch_select', $mobile_launch_select);
	$launch_ad_id = ecjia_config('mobile_launch_adsense');
	$launch_ad_name = $db->getOne('SELECT ad_name FROM ' . $ecs->table('ad') . (' WHERE ad_id = \'' . $launch_ad_id . '\' '));
	$smarty->assign('launch_ad_name', $launch_ad_name);
	$smarty->assign('launch_ad_id', $launch_ad_id);
	$ads_id = ecjia_config('mobile_home_adsense_group');
	$where = '';

	if ($ads_id) {
		$where .= ' AND ad_id IN (' . $ads_id . ')';
	}
	else {
		$where .= ' AND ad_id = 0 ';
	}

	$sql = 'SELECT ad_id, ad_name FROM ' . $ecs->table('ad') . (' WHERE 1 ' . $where . ' ');
	$mobile_home_adsense_group = $db->getAll($sql);
	$smarty->assign('mobile_home_adsense_group', $mobile_home_adsense_group);
	$regions_id = ecjia_config('mobile_recommend_city');
	$where1 = '';

	if ($regions_id) {
		$where1 .= ' AND region_id IN (' . $regions_id . ')';
	}
	else {
		$where1 .= ' AND region_id = 0 ';
	}

	$sql = 'SELECT region_id, region_name FROM ' . $ecs->table('region') . (' WHERE 1 ' . $where1 . ' ');
	$regions = $db->getAll($sql);
	$smarty->assign('regions', $regions);
	$mobile_topic_select = '';

	foreach ($ad_list as $key => $value) {
		$mobile_topic_select .= '<li><a href="javascript:;" data-value="' . $value['ad_id'] . '" class="ftx-01">' . $value['ad_name'] . '</a></li>';
	}

	$smarty->assign('mobile_topic_select', $mobile_topic_select);
	$topic_ad_id = ecjia_config('mobile_topic_adsense');
	$topic_ad_name = $db->getOne('SELECT ad_name FROM ' . $ecs->table('ad') . (' WHERE ad_id = \'' . $topic_ad_id . '\' '));
	$smarty->assign('topic_ad_name', $topic_ad_name);
	$smarty->assign('topic_ad_id', $topic_ad_id);
	$smarty->assign('mobile_phone_login_fgcolor', ecjia_config('mobile_phone_login_fgcolor'));
	$smarty->assign('mobile_phone_login_bgcolor', ecjia_config('mobile_phone_login_bgcolor'));
	$smarty->assign('mobile_phone_login_bgimage', ecjia_config('mobile_phone_login_bgimage'));
	$smarty->assign('mobile_pad_login_fgcolor', ecjia_config('mobile_pad_login_fgcolor'));
	$smarty->assign('mobile_pad_login_bgcolor', ecjia_config('mobile_pad_login_bgcolor'));
	$smarty->assign('mobile_pad_login_bgimage', ecjia_config('mobile_pad_login_bgimage'));
	$smarty->assign('mobile_recommend_city', ecjia_config('mobile_recommend_city'));
	$smarty->assign('form_action', 'update');
	assign_query_info();
	$smarty->display('ecjia_config.dwt');
}

if ($_REQUEST['act'] == 'update') {
	admin_priv('article_manage');
	if (isset($_FILES['shop_app_icon']['error']) && $_FILES['shop_app_icon']['error'] == 0 || !isset($_FILES['shop_app_icon']['error']) && isset($_FILES['shop_app_icon']['tmp_name']) && $_FILES['shop_app_icon']['tmp_name'] != 'none') {
		$img_up_info = basename($image->upload_image($_FILES['shop_app_icon'], 'assets/ecmoban_sc'));
		$code = ecjia_config('shop_app_icon');
		if ($code && $code != DATA_DIR . '/assets/ecmoban_sc/' . $img_up_info) {
			@unlink('../' . $code);
		}

		get_oss_add_file(array(DATA_DIR . '/assets/ecmoban_sc/' . $img_up_info));
		$shop_app_icon_img_src = DATA_DIR . '/assets/ecmoban_sc/' . $img_up_info;
	}

	if (isset($_FILES['mobile_iphone_qr_code']['error']) && $_FILES['mobile_iphone_qr_code']['error'] == 0 || !isset($_FILES['mobile_iphone_qr_code']['error']) && isset($_FILES['mobile_iphone_qr_code']['tmp_name']) && $_FILES['mobile_iphone_qr_code']['tmp_name'] != 'none') {
		$img_up_info = basename($image->upload_image($_FILES['mobile_iphone_qr_code'], 'assets'));
		$code = ecjia_config('mobile_iphone_qr_code');
		if ($code && $code != DATA_DIR . '/assets/' . $img_up_info) {
			@unlink('../' . $code);
		}

		get_oss_add_file(array(DATA_DIR . '/assets/' . $img_up_info));
		$mobile_iphone_qr_code_img_src = DATA_DIR . '/assets/' . $img_up_info;
	}

	if (isset($_FILES['mobile_android_qr_code']['error']) && $_FILES['mobile_android_qr_code']['error'] == 0 || !isset($_FILES['mobile_android_qr_code']['error']) && isset($_FILES['mobile_android_qr_code']['tmp_name']) && $_FILES['mobile_android_qr_code']['tmp_name'] != 'none') {
		$img_up_info = basename($image->upload_image($_FILES['mobile_android_qr_code'], 'assets'));
		$code = ecjia_config('mobile_android_qr_code');
		if ($code && $code != DATA_DIR . '/assets/' . $img_up_info) {
			@unlink('../' . $code);
		}

		get_oss_add_file(array(DATA_DIR . '/assets/' . $img_up_info));
		$mobile_android_qr_code_img_src = DATA_DIR . '/assets/' . $img_up_info;
	}

	if (isset($_FILES['mobile_ipad_qr_code']['error']) && $_FILES['mobile_ipad_qr_code']['error'] == 0 || !isset($_FILES['mobile_ipad_qr_code']['error']) && isset($_FILES['mobile_ipad_qr_code']['tmp_name']) && $_FILES['mobile_ipad_qr_code']['tmp_name'] != 'none') {
		$img_up_info = basename($image->upload_image($_FILES['mobile_ipad_qr_code'], 'assets'));
		$code = ecjia_config('mobile_ipad_qr_code');
		if ($code && $code != DATA_DIR . '/assets/' . $img_up_info) {
			@unlink('../' . $code);
		}

		get_oss_add_file(array(DATA_DIR . '/assets/' . $img_up_info));
		$mobile_ipad_qr_code_img_src = DATA_DIR . '/assets/' . $img_up_info;
	}

	if (isset($_FILES['mobile_phone_login_bgimage']['error']) && $_FILES['mobile_phone_login_bgimage']['error'] == 0 || !isset($_FILES['mobile_phone_login_bgimage']['error']) && isset($_FILES['mobile_phone_login_bgimage']['tmp_name']) && $_FILES['mobile_phone_login_bgimage']['tmp_name'] != 'none') {
		$img_up_info = basename($image->upload_image($_FILES['mobile_phone_login_bgimage'], 'assets'));
		$code = ecjia_config('mobile_phone_login_bgimage');
		if ($code && $code != DATA_DIR . '/assets/' . $img_up_info) {
			@unlink('../' . $code);
		}

		get_oss_add_file(array(DATA_DIR . '/assets/' . $img_up_info));
		$mobile_phone_login_bgimage_img_src = DATA_DIR . '/assets/' . $img_up_info;
	}

	if (isset($_FILES['mobile_pad_login_bgimage']['error']) && $_FILES['mobile_pad_login_bgimage']['error'] == 0 || !isset($_FILES['mobile_pad_login_bgimage']['error']) && isset($_FILES['mobile_pad_login_bgimage']['tmp_name']) && $_FILES['mobile_pad_login_bgimage']['tmp_name'] != 'none') {
		$img_up_info = basename($image->upload_image($_FILES['mobile_pad_login_bgimage'], 'assets'));
		$code = ecjia_config('mobile_pad_login_bgimage');
		if ($code && $code != DATA_DIR . '/assets/' . $img_up_info) {
			@unlink('../' . $code);
		}

		get_oss_add_file(array(DATA_DIR . '/assets/' . $img_up_info));
		$mobile_pad_login_bgimage_img_src = DATA_DIR . '/assets/' . $img_up_info;
	}

	$shop_app_icon = !empty($shop_app_icon_img_src) ? $shop_app_icon_img_src : trim($_POST['shop_app_icon_textfile']);
	$mobile_iphone_qr_code = !empty($mobile_iphone_qr_code_img_src) ? $mobile_iphone_qr_code_img_src : trim($_POST['mobile_iphone_qr_code_textfile']);
	$mobile_android_qr_code = !empty($mobile_android_qr_code_img_src) ? $mobile_android_qr_code_img_src : trim($_POST['mobile_android_qr_code_textfile']);
	$mobile_ipad_qr_code = !empty($mobile_ipad_qr_code_img_src) ? $mobile_ipad_qr_code_img_src : trim($_POST['mobile_ipad_qr_code_textfile']);
	$mobile_phone_login_bgimage = !empty($mobile_phone_login_bgimage_img_src) ? $mobile_phone_login_bgimage_img_src : trim($_POST['mobile_phone_login_bgimage_textfile']);
	$mobile_pad_login_bgimage = !empty($mobile_pad_login_bgimage_img_src) ? $mobile_pad_login_bgimage_img_src : trim($_POST['mobile_pad_login_bgimage_textfile']);
	$shop_app_description = !empty($_POST['shop_app_description']) ? trim($_POST['shop_app_description']) : '';
	$bonus_readme_url = !empty($_POST['bonus_readme_url']) ? trim($_POST['bonus_readme_url']) : '';
	$mobile_feedback_autoreply = !empty($_POST['mobile_feedback_autoreply']) ? trim($_POST['mobile_feedback_autoreply']) : '';
	$mobile_shopkeeper_urlscheme = !empty($_POST['mobile_shopkeeper_urlscheme']) ? trim($_POST['mobile_shopkeeper_urlscheme']) : '';
	$shop_pc_url = !empty($_POST['shop_pc_url']) ? trim($_POST['shop_pc_url']) : '';
	$mobile_share_link = !empty($_POST['mobile_share_link']) ? trim($_POST['mobile_share_link']) : '';
	$mobile_signup_reward = !empty($_POST['mobile_signup_reward']) ? trim($_POST['mobile_signup_reward']) : '';
	$mobile_signup_reward_notice = !empty($_POST['mobile_signup_reward_notice']) ? trim($_POST['mobile_signup_reward_notice']) : '';
	$shop_iphone_download = !empty($_POST['shop_iphone_download']) ? trim($_POST['shop_iphone_download']) : '';
	$shop_android_download = !empty($_POST['shop_android_download']) ? trim($_POST['shop_android_download']) : '';
	$shop_ipad_download = !empty($_POST['shop_ipad_download']) ? trim($_POST['shop_ipad_download']) : '';
	$mobile_launch_adsense = !empty($_POST['mobile_launch_adsense']) ? trim($_POST['mobile_launch_adsense']) : '';
	$mobile_home_adsense_group = !empty($_POST['mobile_home_adsense_group']) ? trim(implode(',', $_POST['mobile_home_adsense_group'])) : '';
	$mobile_topic_adsense = !empty($_POST['mobile_topic_adsense']) ? trim($_POST['mobile_topic_adsense']) : '';
	$mobile_phone_login_fgcolor = !empty($_POST['mobile_phone_login_fgcolor']) ? trim($_POST['mobile_phone_login_fgcolor']) : '';
	$mobile_phone_login_bgcolor = !empty($_POST['mobile_phone_login_bgcolor']) ? trim($_POST['mobile_phone_login_bgcolor']) : '';
	$mobile_pad_login_fgcolor = !empty($_POST['mobile_pad_login_fgcolor']) ? trim($_POST['mobile_pad_login_fgcolor']) : '';
	$mobile_pad_login_bgcolor = !empty($_POST['mobile_pad_login_bgcolor']) ? trim($_POST['mobile_pad_login_bgcolor']) : '';
	$mobile_recommend_city = !empty($_POST['regions']) ? trim(implode(',', $_POST['regions'])) : '';
	update_config('shop_app_icon', $shop_app_icon);
	update_config('shop_app_description', $shop_app_description);
	update_config('bonus_readme_url', '/index.php?m=article&c=mobile&a=info&id=' . $bonus_readme_url);
	update_config('mobile_feedback_autoreply', $mobile_feedback_autoreply);
	update_config('mobile_shopkeeper_urlscheme', $mobile_shopkeeper_urlscheme);
	update_config('shop_pc_url', $shop_pc_url);
	update_config('mobile_share_link', $mobile_share_link);
	update_config('mobile_signup_reward', $mobile_signup_reward);
	update_config('mobile_signup_reward_notice', $mobile_signup_reward_notice);
	update_config('mobile_iphone_qr_code', $mobile_iphone_qr_code);
	update_config('shop_iphone_download', $shop_iphone_download);
	update_config('mobile_android_qr_code', $mobile_android_qr_code);
	update_config('shop_android_download', $shop_android_download);
	update_config('mobile_ipad_qr_code', $mobile_ipad_qr_code);
	update_config('shop_ipad_download', $shop_ipad_download);
	update_config('mobile_launch_adsense', $mobile_launch_adsense);
	update_config('mobile_home_adsense_group', $mobile_home_adsense_group);
	update_config('mobile_topic_adsense', $mobile_topic_adsense);
	update_config('mobile_phone_login_fgcolor', $mobile_phone_login_fgcolor);
	update_config('mobile_phone_login_bgcolor', $mobile_phone_login_bgcolor);
	update_config('mobile_phone_login_bgimage', $mobile_phone_login_bgimage);
	update_config('mobile_pad_login_fgcolor', $mobile_pad_login_fgcolor);
	update_config('mobile_pad_login_bgcolor', $mobile_pad_login_bgcolor);
	update_config('mobile_pad_login_bgimage', $mobile_pad_login_bgimage);
	update_config('mobile_recommend_city', $mobile_recommend_city);
	clear_cache_files();
	$link[0]['text'] = '返回';
	$link[0]['href'] = 'ecjia_config.php?act=list';
	sys_msg($_LANG['attradd_succed'], 0, $link);
}
else if ($_REQUEST['act'] == 'search_article') {
	$result = array('error' => 0, 'msg' => '', 'content' => '');
	$title = !empty($_REQUEST['article_keywords']) ? trim($_REQUEST['article_keywords']) : '';
	$where = '';

	if ($title) {
		$where .= ' AND title LIKE \'%' . $title . '%\' ';
	}

	$sql = 'SELECT article_id, title FROM ' . $GLOBALS['ecs']->table('article') . (' WHERE 1 ' . $where);
	$res = $GLOBALS['db']->getAll($sql);
	$article_str = '<div class="cite">' . $_LANG['please_select'] . "</div>\r\n            <ul class=\"ps-container\" style=\"display: none;\">";

	foreach ($res as $key => $value) {
		$article_str .= '<li><a href="javascript:;" data-value="' . $value['article_id'] . '" class="ftx-01">' . $value['title'] . '</a></li>';
	}

	$article_str .= "</ul>\r\n            <input name=\"bonus_readme_url\" type=\"hidden\" value=\"" . ecjia_config('$bonus_readme_url') . '" id="bonus_readme_url">';
	$result['content'] = $article_str;
	exit(json_encode($result));
}

?>
