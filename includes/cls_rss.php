<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class RSSBase
{
	public function RSSBase()
	{
	}
}

class RSSBuilder extends RSSBase
{
	public $encoding;
	public $about;
	public $title;
	public $description;
	public $publisher;
	public $creator;
	public $date;
	public $language;
	public $rights;
	public $image_link;
	public $coverage;
	public $contributor;
	public $period;
	public $frequency;
	public $base;
	public $category;
	public $cache;
	public $items = array();
	public $output;
	public $use_dc_data = false;
	public $use_sy_data = false;

	public function RSSBuilder($encoding = '', $about = '', $title = '', $description = '', $image_link = '', $category = '', $cache = '')
	{
		$this->setEncoding($encoding);
		$this->setAbout($about);
		$this->setTitle($title);
		$this->setDescription($description);
		$this->setImageLink($image_link);
		$this->setCategory($category);
		$this->setCache($cache);
	}

	public function addDCdata($publisher = '', $creator = '', $date = '', $language = '', $rights = '', $coverage = '', $contributor = '')
	{
		$this->setPublisher($publisher);
		$this->setCreator($creator);
		$this->setDate($date);
		$this->setLanguage($language);
		$this->setRights($rights);
		$this->setCoverage($coverage);
		$this->setContributor($contributor);
		$this->use_dc_data = (bool) true;
	}

	public function addSYdata($period = '', $frequency = '', $base = '')
	{
		$this->setPeriod($period);
		$this->setFrequency($frequency);
		$this->setBase($base);
		$this->use_sy_data = (bool) true;
	}

	public function isValidLanguageCode($code = '')
	{
		return (bool) (0 < preg_match('(^([a-zA-Z]{2})$)', $code) ? true : false);
	}

	public function setEncoding($encoding = '')
	{
		if (!isset($this->encoding)) {
			$this->encoding = (string) (0 < strlen(trim($encoding)) ? trim($encoding) : 'UTF-8');
		}
	}

	public function setAbout($about = '')
	{
		if (!isset($this->about) && (0 < strlen(trim($about)))) {
			$this->about = (string) trim($about);
		}
	}

	public function setTitle($title = '')
	{
		if (!isset($this->title) && (0 < strlen(trim($title)))) {
			$this->title = (string) trim($title);
		}
	}

	public function setDescription($description = '')
	{
		if (!isset($this->description) && (0 < strlen(trim($description)))) {
			$this->description = (string) trim($description);
		}
	}

	public function setPublisher($publisher = '')
	{
		if (!isset($this->publisher) && (0 < strlen(trim($publisher)))) {
			$this->publisher = (string) trim($publisher);
		}
	}

	public function setCreator($creator = '')
	{
		if (!isset($this->creator) && (0 < strlen(trim($creator)))) {
			$this->creator = (string) trim($creator);
		}
	}

	public function setDate($date = '')
	{
		if (!isset($this->date) && (0 < strlen(trim($date)))) {
			$this->date = (string) trim($date);
		}
	}

	public function setLanguage($language = '')
	{
		if (!isset($this->language) && ($this->isValidLanguageCode($language) === true)) {
			$this->language = (string) trim($language);
		}
	}

	public function setRights($rights = '')
	{
		if (!isset($this->rights) && (0 < strlen(trim($rights)))) {
			$this->rights = (string) trim($rights);
		}
	}

	public function setCoverage($coverage = '')
	{
		if (!isset($this->coverage) && (0 < strlen(trim($coverage)))) {
			$this->coverage = (string) trim($coverage);
		}
	}

	public function setContributor($contributor = '')
	{
		if (!isset($this->contributor) && (0 < strlen(trim($contributor)))) {
			$this->contributor = (string) trim($contributor);
		}
	}

	public function setImageLink($image_link = '')
	{
		if (!isset($this->image_link) && (0 < strlen(trim($image_link)))) {
			$this->image_link = (string) trim($image_link);
		}
	}

	public function setPeriod($period = '')
	{
		if (!isset($this->period) && (0 < strlen(trim($period)))) {
			switch ($period) {
			case 'hourly':
			case 'daily':
			case 'weekly':
			case 'monthly':
			case 'yearly':
				$this->period = (string) trim($period);
				break;

			default:
				$this->period = (string) '';
				break;
			}
		}
	}

	public function setFrequency($frequency = '')
	{
		if (!isset($this->frequency) && (0 < strlen(trim($frequency)))) {
			$this->frequency = (int) $frequency;
		}
	}

	public function setBase($base = '')
	{
		if (!isset($this->base) && (0 < strlen(trim($base)))) {
			$this->base = (string) trim($base);
		}
	}

	public function setCategory($category = '')
	{
		if (0 < strlen(trim($category))) {
			$this->category = (string) trim($category);
		}
	}

	public function setCache($cache = '')
	{
		if (0 < strlen(trim($cache))) {
			$this->cache = (int) $cache;
		}
	}

	public function getEncoding()
	{
		return (string) $this->encoding;
	}

	public function getAbout()
	{
		return (string) $this->about;
	}

	public function getTitle()
	{
		return (string) $this->title;
	}

	public function getDescription()
	{
		return (string) $this->description;
	}

	public function getPublisher()
	{
		return (string) $this->publisher;
	}

	public function getCreator()
	{
		return (string) $this->creator;
	}

	public function getDate()
	{
		return (string) $this->date;
	}

	public function getLanguage()
	{
		return (string) $this->language;
	}

	public function getRights()
	{
		return (string) $this->rights;
	}

	public function getCoverage()
	{
		return (string) $this->coverage;
	}

	public function getContributor()
	{
		return (string) $this->contributor;
	}

	public function getImageLink()
	{
		return (string) $this->image_link;
	}

	public function getPeriod()
	{
		return (string) $this->period;
	}

	public function getFrequency()
	{
		return (int) $this->frequency;
	}

	public function getBase()
	{
		return (string) $this->base;
	}

	public function getCategory()
	{
		return (string) $this->category;
	}

	public function getCache()
	{
		return (int) $this->cache;
	}

	public function addItem($about = '', $title = '', $link = '', $description = '', $subject = '', $date = '', $author = '', $comments = '')
	{
		$item = new RSSItem($about, $title, $link, $description, $subject, $date, $author = '', $comments = '');
		$this->items[] = $item;
	}

	public function deleteItem($id = -1)
	{
		if (array_key_exists($id, $this->items)) {
			unset($this->items[$id]);
			return (bool) true;
		}
		else {
			return (bool) false;
		}
	}

	public function getItemList()
	{
		return (array) array_keys($this->items);
	}

	public function getItems()
	{
		return (array) $this->items;
	}

	public function getItem($id = -1)
	{
		if (array_key_exists($id, $this->items)) {
			return (object) $this->items[$id];
		}
		else {
			return (bool) false;
		}
	}

	public function createOutputV090()
	{
		$this->createOutputV100();
	}

	public function createOutputV091()
	{
		$this->output = (string) '<!DOCTYPE rss SYSTEM "http://my.netscape.com/publish/formats/rss-0.91.dtd">' . "\n";
		$this->output .= (string) '<rss version="0.91">' . "\n";
		$this->output .= (string) '<channel>' . "\n";

		if (0 < strlen($this->rights)) {
			$this->output .= (string) '<copyright>' . $this->rights . '</copyright>' . "\n";
		}

		if (0 < strlen($this->date)) {
			$this->output .= (string) '<pubDate>' . $this->date . '</pubDate>' . "\n";
			$this->output .= (string) '<lastBuildDate>' . $this->date . '</lastBuildDate>' . "\n";
		}

		if (0 < strlen($this->about)) {
			$this->output .= (string) '<docs>' . $this->about . '</docs>' . "\n";
		}

		if (0 < strlen($this->description)) {
			$this->output .= (string) '<description>' . $this->description . '</description>' . "\n";
		}

		if (0 < strlen($this->about)) {
			$this->output .= (string) '<link>' . $this->about . '</link>' . "\n";
		}

		if (0 < strlen($this->title)) {
			$this->output .= (string) '<title>' . $this->title . '</title>' . "\n";
		}

		if (0 < strlen($this->image_link)) {
			$this->output .= (string) '<image>' . "\n";
			$this->output .= (string) '<title>' . $this->title . '</title>' . "\n";
			$this->output .= (string) '<url>' . $this->image_link . '</url>' . "\n";
			$this->output .= (string) '<link>' . $this->about . '</link>' . "\n";

			if (0 < strlen($this->description)) {
				$this->output .= (string) '<description>' . $this->description . '</description>' . "\n";
			}

			$this->output .= (string) '</image>' . "\n";
		}

		if (0 < strlen($this->publisher)) {
			$this->output .= (string) '<managingEditor>' . $this->publisher . '</managingEditor>' . "\n";
		}

		if (0 < strlen($this->creator)) {
			$this->output .= (string) '<webMaster>' . $this->creator . '</webMaster>' . "\n";
		}

		if (0 < strlen($this->language)) {
			$this->output .= (string) '<language>' . $this->language . '</language>' . "\n";
		}

		if (0 < count($this->getItemList())) {
			foreach ($this->getItemList() as $id) {
				$item = &$this->items[$id];
				if ((0 < strlen($item->getTitle())) && (0 < strlen($item->getLink()))) {
					$this->output .= (string) '<item>' . "\n";
					$this->output .= (string) '<title>' . $item->getTitle() . '</title>' . "\n";
					$this->output .= (string) '<link>' . $item->getLink() . '</link>' . "\n";

					if (0 < strlen($item->getDescription())) {
						$this->output .= (string) '<description>' . $item->getDescription() . '</description>' . "\n";
					}

					$this->output .= (string) '</item>' . "\n";
				}
			}
		}

		$this->output .= (string) '</channel>' . "\n";
		$this->output .= (string) '</rss>' . "\n";
	}

	public function createOutputV100()
	{
		$this->output = (string) '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" ';

		if ($this->use_dc_data === true) {
			$this->output .= (string) 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
		}

		if ($this->use_sy_data === true) {
			$this->output .= (string) 'xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" ';
		}

		$this->output .= (string) 'xmlns="http://purl.org/rss/1.0/">' . "\n";

		if (0 < strlen($this->about)) {
			$this->output .= (string) '<channel rdf:about="' . $this->about . '">' . "\n";
		}
		else {
			$this->output .= (string) '<channel>' . "\n";
		}

		if (0 < strlen($this->title)) {
			$this->output .= (string) '<title>' . $this->title . '</title>' . "\n";
		}

		if (0 < strlen($this->about)) {
			$this->output .= (string) '<link>' . $this->about . '</link>' . "\n";
		}

		if (0 < strlen($this->description)) {
			$this->output .= (string) '<description>' . $this->description . '</description>' . "\n";
		}

		if (0 < strlen($this->publisher)) {
			$this->output .= (string) '<dc:publisher>' . $this->publisher . '</dc:publisher>' . "\n";
		}

		if (0 < strlen($this->creator)) {
			$this->output .= (string) '<dc:creator>' . $this->creator . '</dc:creator>' . "\n";
		}

		if (0 < strlen($this->date)) {
			$this->output .= (string) '<dc:date>' . $this->date . '</dc:date>' . "\n";
		}

		if (0 < strlen($this->language)) {
			$this->output .= (string) '<dc:language>' . $this->language . '</dc:language>' . "\n";
		}

		if (0 < strlen($this->rights)) {
			$this->output .= (string) '<dc:rights>' . $this->rights . '</dc:rights>' . "\n";
		}

		if (0 < strlen($this->coverage)) {
			$this->output .= (string) '<dc:coverage>' . $this->coverage . '</dc:coverage>' . "\n";
		}

		if (0 < strlen($this->contributor)) {
			$this->output .= (string) '<dc:contributor>' . $this->contributor . '</dc:contributor>' . "\n";
		}

		if (0 < strlen($this->period)) {
			$this->output .= (string) '<sy:updatePeriod>' . $this->period . '</sy:updatePeriod>' . "\n";
		}

		if (0 < strlen($this->frequency)) {
			$this->output .= (string) '<sy:updateFrequency>' . $this->frequency . '</sy:updateFrequency>' . "\n";
		}

		if (0 < strlen($this->base)) {
			$this->output .= (string) '<sy:updateBase>' . $this->base . '</sy:updateBase>' . "\n";
		}

		if (0 < strlen($this->image_link)) {
			$this->output .= (string) '<image rdf:resource="' . $this->image_link . '" />' . "\n";
		}

		if (0 < strlen($this->image_link)) {
			$this->output .= (string) '<image rdf:about="' . $this->image_link . '">' . "\n";
			$this->output .= (string) '<title>' . $this->title . '</title>' . "\n";
			$this->output .= (string) '<url>' . $this->image_link . '</url>' . "\n";
			$this->output .= (string) '<link>' . $this->about . '</link>' . "\n";

			if (0 < strlen($this->description)) {
				$this->output .= (string) '<description>' . $this->description . '</description>' . "\n";
			}

			$this->output .= (string) '</image>' . "\n";
		}

		if (0 < count($this->getItemList())) {
			$this->output .= (string) '<items><rdf:Seq>' . "\n";

			foreach ($this->getItemList() as $id) {
				$item = &$this->items[$id];

				if (0 < strlen($item->getAbout())) {
					$this->output .= (string) ' <rdf:li resource="' . $item->getAbout() . '" />' . "\n";
				}
			}

			$this->output .= (string) '</rdf:Seq></items>' . "\n";
		}

		$this->output .= (string) '</channel>' . "\n";

		if (0 < count($this->getItemList())) {
			foreach ($this->getItemList() as $id) {
				$item = &$this->items[$id];
				if ((0 < strlen($item->getTitle())) && (0 < strlen($item->getLink()))) {
					if (0 < strlen($item->getAbout())) {
						$this->output .= (string) '<item rdf:about="' . $item->getAbout() . '">' . "\n";
					}
					else {
						$this->output .= (string) '<item>' . "\n";
					}

					$this->output .= (string) '<title>' . $item->getTitle() . '</title>' . "\n";
					$this->output .= (string) '<link>' . $item->getLink() . '</link>' . "\n";

					if (0 < strlen($item->getDescription())) {
						$this->output .= (string) '<description>' . $item->getDescription() . '</description>' . "\n";
					}

					if (($this->use_dc_data === true) && (0 < strlen($item->getSubject()))) {
						$this->output .= (string) '<dc:subject>' . $item->getSubject() . '</dc:subject>' . "\n";
					}

					if (($this->use_dc_data === true) && (0 < strlen($item->getDate()))) {
						$this->output .= (string) '<dc:date>' . $item->getDate() . '</dc:date>' . "\n";
					}

					$this->output .= (string) '</item>' . "\n";
				}
			}
		}

		$this->output .= (string) '</rdf:RDF>';
	}

	public function createOutputV200()
	{
		$this->createOutputV100();
		$this->output = (string) '<rss version="2.0">' . "\n";
		$this->output .= (string) '<channel>' . "\n";

		if (0 < strlen($this->rights)) {
			$this->output .= (string) '<copyright>' . $this->rights . '</copyright>' . "\n";
		}

		if (0 < strlen($this->date)) {
			$this->output .= (string) '<pubDate>' . $this->date . '</pubDate>' . "\n";
		}

		if (0 < strlen($this->about)) {
			$this->output .= (string) '<docs>' . $this->about . '</docs>' . "\n";
		}

		if (0 < strlen($this->description)) {
			$this->output .= (string) '<description>' . $this->description . '</description>' . "\n";
		}

		if (0 < strlen($this->about)) {
			$this->output .= (string) '<link>' . $this->about . '</link>' . "\n";
		}

		if (0 < strlen($this->title)) {
			$this->output .= (string) '<title>' . $this->title . '</title>' . "\n";
		}

		if (0 < strlen($this->image_link)) {
			$this->output .= (string) '<image>' . "\n";
			$this->output .= (string) '<title>' . $this->title . '</title>' . "\n";
			$this->output .= (string) '<url>' . $this->image_link . '</url>' . "\n";
			$this->output .= (string) '<link>' . $this->about . '</link>' . "\n";

			if (0 < strlen($this->description)) {
				$this->output .= (string) '<description>' . $this->description . '</description>' . "\n";
			}

			$this->output .= (string) '</image>' . "\n";
		}

		if (0 < strlen($this->publisher)) {
			$this->output .= (string) '<managingEditor>' . $this->publisher . '</managingEditor>' . "\n";
		}

		if (0 < strlen($this->creator)) {
			$this->output .= (string) '<generator>' . $this->creator . '</generator>' . "\n";
		}

		if (0 < strlen($this->language)) {
			$this->output .= (string) '<language>' . $this->language . '</language>' . "\n";
		}

		if (0 < strlen($this->category)) {
			$this->output .= (string) '<category>' . $this->category . '</category>' . "\n";
		}

		if (0 < strlen($this->cache)) {
			$this->output .= (string) '<ttl>' . $this->cache . '</ttl>' . "\n";
		}

		if (0 < count($this->getItemList())) {
			foreach ($this->getItemList() as $id) {
				$item = &$this->items[$id];
				if ((0 < strlen($item->getTitle())) && (0 < strlen($item->getLink()))) {
					$this->output .= (string) '<item>' . "\n";
					$this->output .= (string) '<title>' . $item->getTitle() . '</title>' . "\n";
					$this->output .= (string) '<link>' . $item->getLink() . '</link>' . "\n";

					if (0 < strlen($item->getDescription())) {
						$this->output .= (string) '<description>' . $item->getDescription() . '</description>' . "\n";
					}

					if (($this->use_dc_data === true) && (0 < strlen($item->getSubject()))) {
						$this->output .= (string) '<category>' . $item->getSubject() . '</category>' . "\n";
					}

					if (($this->use_dc_data === true) && (0 < strlen($item->getDate()))) {
						$this->output .= (string) '<pubDate>' . $item->getDate() . '</pubDate>' . "\n";
					}

					if (0 < strlen($item->getAbout())) {
						$this->output .= (string) '<guid>' . $item->getAbout() . '</guid>' . "\n";
					}

					if (0 < strlen($item->getAuthor())) {
						$this->output .= (string) '<author>' . $item->getAuthor() . '</author>' . "\n";
					}

					if (0 < strlen($item->getComments())) {
						$this->output .= (string) '<comments>' . $item->getComments() . '</comments>' . "\n";
					}

					$this->output .= (string) '</item>' . "\n";
				}
			}
		}

		$this->output .= (string) '</channel>' . "\n";
		$this->output .= (string) '</rss>' . "\n";
	}

	public function createOutput($version = '')
	{
		if (strlen(trim($version)) === 0) {
			$version = (string) '1.0';
		}

		switch ($version) {
		case '0.9':
			$this->createOutputV090();
			break;

		case '0.91':
			$this->createOutputV091();
			break;

		case '2.00':
			$this->createOutputV200();
			break;

		case '1.0':
		default:
			$this->createOutputV100();
			break;
		}
	}

	public function outputRSS($version = '')
	{
		if (!isset($this->output)) {
			$this->createOutput($version);
		}

		$this->output = '<' . '?xml version="1.0" encoding="' . $this->encoding . '"?' . '>' . "\n" . '<!--  RSS generated by ECSHOP (http://www.ecmoban.com) [' . date('Y-m-d H:i:s') . ']  -->' . "\n" . $this->output;
		echo $this->output;
	}

	public function getRSSOutput($version = '')
	{
		if (!isset($this->output)) {
			$this->createOutput($version);
		}

		return (string) '<' . '?xml version="1.0" encoding="' . $this->encoding . '"?' . '>' . "\n" . '<!--  RSS generated by ' . APP_NAME . ' ' . APP_VERSION . ' [' . date('Y-m-d H:i:s') . ']  --> ' . "\n" . $this->output;
	}

	public function RSSBase()
	{
	}
}

class RSSItem extends RSSBase
{
	public $about;
	public $title;
	public $link;
	public $description;
	public $subject;
	public $date;
	public $author;
	public $comments;

	public function RSSItem($about = '', $title = '', $link = '', $description = '', $subject = '', $date = '', $author = '', $comments = '')
	{
		$this->setAbout($about);
		$this->setTitle($title);
		$this->setLink($link);
		$this->setDescription($description);
		$this->setSubject($subject);
		$this->setDate($date);
		$this->setAuthor($author);
		$this->setComments($comments);
	}

	public function setAbout($about = '')
	{
		if (!isset($this->about) && (0 < strlen(trim($about)))) {
			$this->about = (string) trim($about);
		}
	}

	public function setTitle($title = '')
	{
		if (!isset($this->title) && (0 < strlen(trim($title)))) {
			$this->title = (string) trim($title);
		}
	}

	public function setLink($link = '')
	{
		if (!isset($this->link) && (0 < strlen(trim($link)))) {
			$this->link = (string) trim($link);
		}
	}

	public function setDescription($description = '')
	{
		if (!isset($this->description) && (0 < strlen(trim($description)))) {
			$this->description = (string) trim($description);
		}
	}

	public function setSubject($subject = '')
	{
		if (!isset($this->subject) && (0 < strlen(trim($subject)))) {
			$this->subject = (string) trim($subject);
		}
	}

	public function setDate($date = '')
	{
		if (!isset($this->date) && (0 < strlen(trim($date)))) {
			$this->date = (string) trim($date);
		}
	}

	public function setAuthor($author = '')
	{
		if (!isset($this->author) && (0 < strlen(trim($author)))) {
			$this->author = (string) trim($author);
		}
	}

	public function setComments($comments = '')
	{
		if (!isset($this->comments) && (0 < strlen(trim($comments)))) {
			$this->comments = (string) trim($comments);
		}
	}

	public function getAbout()
	{
		return (string) $this->about;
	}

	public function getTitle()
	{
		return (string) $this->title;
	}

	public function getLink()
	{
		return (string) $this->link;
	}

	public function getDescription()
	{
		return (string) $this->description;
	}

	public function getSubject()
	{
		return (string) $this->subject;
	}

	public function getDate()
	{
		return (string) $this->date;
	}

	public function getAuthor()
	{
		return (string) $this->author;
	}

	public function getComments()
	{
		return (string) $this->comments;
	}

	public function RSSBase()
	{
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
