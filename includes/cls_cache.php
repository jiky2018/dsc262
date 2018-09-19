<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class cls_cache
{
	protected $cache;

	public function __construct($config = array())
	{
		$cacheDriver = ucfirst($config['type']);
		require_once dirname(__FILE__) . '/caches/' . $cacheDriver . '.class.php';
		$hander = 'Cache\\' . $cacheDriver;
		$this->cache = new $hander($config[strtolower($cacheDriver)]);
	}

	public function get($key)
	{
		return $this->cache->get($key);
	}

	public function set($key, $value, $expire = 1800)
	{
		return $this->cache->set($key, $value, $expire);
	}

	public function replace($key, $value, $expire = 1800)
	{
		return $this->cache->replace($key, $value, $expire);
	}

	public function rm($key)
	{
		return $this->cache->rm($key);
	}

	public function clear()
	{
		return $this->cache->clear();
	}
}


?>
