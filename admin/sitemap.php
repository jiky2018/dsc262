<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
admin_priv('sitemap');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	assign_query_info();
	$config = unserialize($_CFG['sitemap']);
	$smarty->assign('config', $config);
	$smarty->assign('ur_here', $_LANG['sitemap']);
	$smarty->assign('arr_changefreq', array(1, 0.90000000000000002, 0.80000000000000004, 0.69999999999999996, 0.59999999999999998, 0.5, 0.40000000000000002, 0.29999999999999999, 0.20000000000000001, 0.10000000000000001));
	$smarty->display('sitemap.dwt');
}
else {
	include_once 'includes/cls_phpzip.php';
	include_once 'includes/cls_google_sitemap.php';
	$domain = $ecs->url();
	$today = local_date('Y-m-d');
	$sm = new google_sitemap();
	$smi = new google_sitemap_item($domain, $today, $_POST['homepage_changefreq'], $_POST['homepage_priority']);
	$sm->add_item($smi);
	$config = array('homepage_changefreq' => $_POST['homepage_changefreq'], 'homepage_priority' => $_POST['homepage_priority'], 'category_changefreq' => $_POST['category_changefreq'], 'category_priority' => $_POST['category_priority'], 'content_changefreq' => $_POST['content_changefreq'], 'content_priority' => $_POST['content_priority']);
	$config = serialize($config);
	$db->query('UPDATE ' . $ecs->table('shop_config') . ' SET VALUE=\'' . $config . '\' WHERE code=\'sitemap\'');
	$sql = 'SELECT cat_id,cat_name FROM ' . $ecs->table('category') . ' ORDER BY parent_id';
	$res = $db->query($sql);

	while ($row = $db->fetchRow($res)) {
		if (strpos(build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']), $domain) === false) {
			$build_uri = $domain . build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);
		}
		else {
			$build_uri = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);
		}

		$smi = new google_sitemap_item($build_uri, $today, $_POST['category_changefreq'], $_POST['category_priority']);
		$sm->add_item($smi);
	}

	$sql = 'SELECT cat_id,cat_name FROM ' . $ecs->table('article_cat') . ' WHERE cat_type=1';
	$res = $db->query($sql);

	while ($row = $db->fetchRow($res)) {
		if (strpos(build_uri('article_cat', array('acid' => $row['cat_id']), $row['cat_name']), $domain) === false) {
			$build_uri = $domain . build_uri('article_cat', array('acid' => $row['cat_id']), $row['cat_name']);
		}
		else {
			$build_uri = build_uri('article_cat', array('acid' => $row['cat_id']), $row['cat_name']);
		}

		$smi = new google_sitemap_item($build_uri, $today, $_POST['category_changefreq'], $_POST['category_priority']);
		$sm->add_item($smi);
	}

	$sql = 'SELECT goods_id, goods_name FROM ' . $ecs->table('goods') . ' WHERE is_delete = 0';
	$res = $db->query($sql);

	while ($row = $db->fetchRow($res)) {
		if (strpos(build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']), $domain) === false) {
			$build_uri = $domain . build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		}
		else {
			$build_uri = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		}

		$smi = new google_sitemap_item($build_uri, $today, $_POST['content_changefreq'], $_POST['content_priority']);
		$sm->add_item($smi);
	}

	$sql = 'SELECT article_id,title,file_url,open_type FROM ' . $ecs->table('article') . ' WHERE is_open=1';
	$res = $db->query($sql);

	while ($row = $db->fetchRow($res)) {
		$article_url = ($row['open_type'] != 1 ? build_uri('article', array('aid' => $row['article_id']), $row['title']) : trim($row['file_url']));

		if (strpos($article_url, $domain) === false) {
			$build_uri = $domain . $article_url;
		}
		else {
			$build_uri = $article_url;
		}

		$smi = new google_sitemap_item($build_uri, $today, $_POST['content_changefreq'], $_POST['content_priority']);
		$sm->add_item($smi);
	}

	clear_cache_files();
	$sm_file = '../sitemaps.xml';

	if ($sm->build($sm_file)) {
		sys_msg(sprintf($_LANG['generate_success'], $ecs->url() . 'sitemaps.xml'));
	}
	else {
		$sm_file = '../' . DATA_DIR . '/sitemaps.xml';

		if ($sm->build($sm_file)) {
			sys_msg(sprintf($_LANG['generate_success'], $ecs->url() . DATA_DIR . '/sitemaps.xml'));
		}
		else {
			sys_msg(sprintf($_LANG['generate_failed']));
		}
	}
}

?>
