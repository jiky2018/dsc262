<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class sitemap
{
	public $head = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.google.com/schemas/sitemap/0.84\">\n";
	public $footer = "</urlset>\n";
	public $item;

	public function item($item)
	{
		$this->item .= "<url>\n";

		foreach ($item as $key => $val) {
			$this->item .= ' <' . $key . '>' . htmlentities($val, ENT_QUOTES) . '</' . $key . ">\n";
		}

		$this->item .= "</url>\n";
	}

	public function generate()
	{
		$all = $this->head;
		$all .= $this->item;
		$all .= $this->footer;
		return $all;
	}
}

define('IN_ECS', true);
define('INIT_NO_USERS', true);
define('INIT_NO_SMARTY', true);
require dirname(__FILE__) . '/includes/init.php';
if (file_exists(ROOT_PATH . DATA_DIR . '/sitemap.dat') && ((time() - filemtime(ROOT_PATH . DATA_DIR . '/sitemap.dat')) < 86400)) {
	$out = file_get_contents(ROOT_PATH . DATA_DIR . '/sitemap.dat');
}
else {
	$site_url = rtrim($ecs->url(), '/');
	$sitemap = new sitemap();
	$config = unserialize($_CFG['sitemap']);
	$item = array('loc' => $site_url . '/', 'lastmod' => local_date('Y-m-d'), 'changefreq' => $config['homepage_changefreq'], 'priority' => $config['homepage_priority']);
	$sitemap->item($item);
	$sql = 'SELECT cat_id,cat_name FROM ' . $ecs->table('category') . ' ORDER BY parent_id';
	$res = $db->query($sql);

	while ($row = $db->fetchRow($res)) {
		if (strpos(build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']), $site_url) === false) {
			$cat_loc = $site_url . '/' . build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);
		}
		else {
			$cat_loc = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);
		}

		$item = array('loc' => $cat_loc, 'lastmod' => local_date('Y-m-d'), 'changefreq' => $config['category_changefreq'], 'priority' => $config['category_priority']);
		$sitemap->item($item);
	}

	$sql = 'SELECT cat_id,cat_name FROM ' . $ecs->table('article_cat') . ' WHERE cat_type=1';
	$res = $db->query($sql);

	while ($row = $db->fetchRow($res)) {
		if (strpos(build_uri('article_cat', array('acid' => $row['cat_id']), $row['cat_name']), $site_url) === false) {
			$art_cat_loc = $site_url . '/' . build_uri('article_cat', array('acid' => $row['cat_id']), $row['cat_name']);
		}
		else {
			$art_cat_loc = build_uri('article_cat', array('acid' => $row['cat_id']), $row['cat_name']);
		}

		$item = array('loc' => $art_cat_loc, 'lastmod' => local_date('Y-m-d'), 'changefreq' => $config['category_changefreq'], 'priority' => $config['category_priority']);
		$sitemap->item($item);
	}

	$sql = 'SELECT goods_id, goods_name, last_update FROM ' . $ecs->table('goods') . ' WHERE is_delete = 0 LIMIT 300';
	$res = $db->query($sql);

	while ($row = $db->fetchRow($res)) {
		if (strpos(build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']), $site_url) === false) {
			$goods_loc = $site_url . '/' . build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		}
		else {
			$goods_loc = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
		}

		$item = array('loc' => $goods_loc, 'lastmod' => local_date('Y-m-d', $row['last_update']), 'changefreq' => $config['content_changefreq'], 'priority' => $config['content_priority']);
		$sitemap->item($item);
	}

	$sql = 'SELECT article_id,title,file_url,open_type, add_time FROM ' . $ecs->table('article') . ' WHERE is_open=1';
	$res = $db->query($sql);

	while ($row = $db->fetchRow($res)) {
		$article_url = ($row['open_type'] != 1 ? build_uri('article', array('aid' => $row['article_id']), $row['title']) : trim($row['file_url']));

		if (strpos($article_url, $site_url) === false) {
			$art_loc = $site_url . '/' . $article_url;
		}
		else {
			$art_loc = $article_url;
		}

		$item = array('loc' => $art_loc, 'lastmod' => local_date('Y-m-d', $row['add_time']), 'changefreq' => $config['content_changefreq'], 'priority' => $config['content_priority']);
		$sitemap->item($item);
	}

	$out = $sitemap->generate();
	file_put_contents(ROOT_PATH . DATA_DIR . '/sitemap.dat', $out);
}

if (function_exists('gzencode')) {
	header('Content-type: application/x-gzip');
	$out = gzencode($out, 9);
}
else {
	header('Content-type: application/xml; charset=utf-8');
}

exit($out);

?>
