<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class google_sitemap
{
	public $header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n\t<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
	public $charset = 'UTF-8';
	public $footer = "\t</urlset>\n";
	public $items = array();

	public function add_item($new_item)
	{
		$this->items[] = $new_item;
	}

	public function build($file_name = NULL)
	{
		$map = $this->header . "\n";

		foreach ($this->items as $item) {
			$item->loc = htmlentities($item->loc, ENT_QUOTES);
			$map .= "\t\t<url>\n\t\t\t<loc>" . $item->loc . "</loc>\n";

			if (!empty($item->lastmod)) {
				$map .= '			<lastmod>' . $item->lastmod . "</lastmod>\n";
			}

			if (!empty($item->changefreq)) {
				$map .= '			<changefreq>' . $item->changefreq . "</changefreq>\n";
			}

			if (!empty($item->priority)) {
				$map .= '			<priority>' . $item->priority . "</priority>\n";
			}

			$map .= "\t\t</url>\n\n";
		}

		$map .= $this->footer . "\n";

		if (!is_null($file_name)) {
			return file_put_contents($file_name, $map);
		}
		else {
			return $map;
		}
	}
}

class google_sitemap_item
{
	public function google_sitemap_item($loc, $lastmod = '', $changefreq = '', $priority = '')
	{
		$this->loc = $loc;
		$this->lastmod = $lastmod;
		$this->changefreq = $changefreq;
		$this->priority = $priority;
	}
}
//ci cheng xu ban quan shu yu shang chuang ，po jie cheng xu chu zi yu jin meng wang luo ！
if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
