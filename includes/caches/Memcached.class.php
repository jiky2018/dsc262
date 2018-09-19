<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace Cache;

class Memcached
{
	public function __construct($options = array())
	{
		if (!extension_loaded('memcached')) {
			exit('Not Support Memcached.');
		}

		$this->options = array(
	'servers'  => array('127.0.0.1', 11211),
	'options'  => null,
	'username' => '',
	'password' => '',
	'prefix'   => '',
	'expire'   => 0
	);
		$this->options = array_merge($this->options, (array) $options);
		$this->handler = new \Memcached();
		$this->handler->addServers($this->options['servers']);
		$this->options['options'] && $this->handler->setOptions($this->options['options']);
		$this->options['username'] && $this->handler->setSaslAuthData($this->options['username'], $this->options['password']);
	}

	public function get($name)
	{
		return $this->handler->get($this->options['prefix'] . $name);
	}

	public function set($name, $value, $expire = NULL)
	{
		if (is_null($expire)) {
			$expire = $this->options['expire'];
		}

		$name = $this->options['prefix'] . $name;

		if ($this->handler->set($name, $value, time() + $expire)) {
			return true;
		}

		return false;
	}

	public function replace($name, $value, $expire = NULL)
	{
		if (is_null($expire)) {
			$expire = $this->options['expire'];
		}

		$name = $this->options['prefix'] . $name;

		if ($this->handler->replace($name, $value, time() + $expire)) {
			return true;
		}

		return false;
	}

	public function rm($name, $ttl = false)
	{
		$name = $this->options['prefix'] . $name;
		return false === $ttl ? $this->handler->delete($name) : $this->handler->delete($name, $ttl);
	}

	public function clear()
	{
		return $this->handler->flush();
	}
}


?>
