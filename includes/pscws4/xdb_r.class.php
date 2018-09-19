<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class XDB_R
{
	public $fd = false;
	public $hash_base = 0;
	public $hash_prime = 0;

	public function __construct()
	{
		$this->XDB_R();
	}

	public function XDB_R()
	{
	}

	public function __destruct()
	{
		$this->Close();
	}

	public function Open($fpath)
	{
		$this->Close();

		if (!($fd = @fopen($fpath, 'rb'))) {
			trigger_error('XDB::Open(' . basename($fpath) . ') failed.', 512);
			return false;
		}

		if (!$this->_check_header($fd)) {
			trigger_error('XDB::Open(' . basename($fpath) . '), invalid xdb format.', 512);
			fclose($fd);
			return false;
		}

		$this->fd = $fd;
		return true;
	}

	public function Get($key)
	{
		if (!$this->fd) {
			trigger_error('XDB:Get(), null db handler.', 512);
			return false;
		}

		$klen = strlen($key);
		if (($klen == 0) || (XDB_MAXKLEN < $klen)) {
			return false;
		}

		$rec = $this->_get_record($key);
		if (!isset($rec['vlen']) || ($rec['vlen'] == 0)) {
			return false;
		}

		return $rec['value'];
	}

	public function Close()
	{
		if (!$this->fd) {
			return NULL;
		}

		fclose($this->fd);
		$this->fd = false;
	}

	public function _get_index($key)
	{
		$l = strlen($key);
		$h = $this->hash_base;

		while ($l--) {
			$h += $h << 5;
			$h ^= ord($key[$l]);
			$h &= 2147483647;
		}

		return $h % $this->hash_prime;
	}

	public function _check_header($fd)
	{
		fseek($fd, 0, SEEK_SET);
		$buf = fread($fd, 32);

		if (strlen($buf) !== 32) {
			return false;
		}

		$hdr = unpack('a3tag/Cver/Ibase/Iprime/Ifsize/fcheck/a12reversed', $buf);

		if ($hdr['tag'] != XDB_TAGNAME) {
			return false;
		}

		$fstat = fstat($fd);

		if ($fstat['size'] != $hdr['fsize']) {
			return false;
		}

		$this->hash_base = $hdr['base'];
		$this->hash_prime = $hdr['prime'];
		$this->version = $hdr['ver'];
		$this->fsize = $hdr['fsize'];
		return true;
	}

	public function _get_record($key)
	{
		$this->_io_times = 1;
		$index = (1 < $this->hash_prime ? $this->_get_index($key) : 0);
		$poff = ($index * 8) + 32;
		fseek($this->fd, $poff, SEEK_SET);
		$buf = fread($this->fd, 8);

		if (strlen($buf) == 8) {
			$tmp = unpack('Ioff/Ilen', $buf);
		}
		else {
			$tmp = array('off' => 0, 'len' => 0);
		}

		return $this->_tree_get_record($tmp['off'], $tmp['len'], $poff, $key);
	}

	public function _tree_get_record($off, $len, $poff = 0, $key = '')
	{
		if ($len == 0) {
			return array('poff' => $poff);
		}

		$this->_io_times++;
		fseek($this->fd, $off, SEEK_SET);
		$rlen = XDB_MAXKLEN + 17;

		if ($len < $rlen) {
			$rlen = $len;
		}

		$buf = fread($this->fd, $rlen);
		$rec = unpack('Iloff/Illen/Iroff/Irlen/Cklen', substr($buf, 0, 17));
		$fkey = substr($buf, 17, $rec['klen']);
		$cmp = ($key ? strcmp($key, $fkey) : 0);

		if (0 < $cmp) {
			unset($buf);
			return $this->_tree_get_record($rec['roff'], $rec['rlen'], $off + 8, $key);
		}
		else if ($cmp < 0) {
			unset($buf);
			return $this->_tree_get_record($rec['loff'], $rec['llen'], $off, $key);
		}
		else {
			$rec['poff'] = $poff;
			$rec['off'] = $off;
			$rec['len'] = $len;
			$rec['voff'] = $off + 17 + $rec['klen'];
			$rec['vlen'] = $len - 17 - $rec['klen'];
			$rec['key'] = $fkey;
			fseek($this->fd, $rec['voff'], SEEK_SET);
			$rec['value'] = fread($this->fd, $rec['vlen']);
			return $rec;
		}
	}
}

define('XDB_VERSION', 34);
define('XDB_TAGNAME', 'XDB');
define('XDB_MAXKLEN', 240);

?>
