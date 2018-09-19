<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
include_once 'BCGDraw.php';

if (!function_exists('file_put_contents')) {
	function file_put_contents($filename, $data)
	{
		$f = @fopen($filename, 'w');

		if (!$f) {
			return false;
		}
		else {
			$bytes = fwrite($f, $data);
			fclose($f);
			return $bytes;
		}
	}
}

class BCGDrawPNG extends BCGDraw
{
	private $dpi;
	static private $crc_table = array();
	static private $crc_table_computed = false;

	public function __construct($im)
	{
		parent::__construct($im);
	}

	public function setDPI($dpi)
	{
		if (is_numeric($dpi)) {
			$this->dpi = max(1, $dpi);
		}
		else {
			$this->dpi = NULL;
		}
	}

	public function draw()
	{
		ob_start();
		imagepng($this->im);
		$bin = ob_get_contents();
		ob_end_clean();
		$this->setInternalProperties($bin);

		if (empty($this->filename)) {
			echo $bin;
		}
		else {
			file_put_contents($this->filename, $bin);
		}
	}

	private function setInternalProperties(&$bin)
	{
		if (strcmp(substr($bin, 0, 8), pack('H*', '89504E470D0A1A0A')) === 0) {
			$chunks = $this->detectChunks($bin);
			$this->internalSetDPI($bin, $chunks);
			$this->internalSetC($bin, $chunks);
		}
	}

	private function detectChunks($bin)
	{
		$data = substr($bin, 8);
		$chunks = array();
		$c = strlen($data);
		$offset = 0;

		while ($offset < $c) {
			$packed = unpack('Nsize/a4chunk', $data);
			$size = $packed['size'];
			$chunk = $packed['chunk'];
			$chunks[] = array('offset' => $offset + 8, 'size' => $size, 'chunk' => $chunk);
			$jump = $size + 12;
			$offset += $jump;
			$data = substr($data, $jump);
		}

		return $chunks;
	}

	private function internalSetDPI(&$bin, &$chunks)
	{
		if ($this->dpi !== NULL) {
			$meters = (int) ($this->dpi * 39.370078739999997);
			$found = -1;
			$c = count($chunks);

			for ($i = 0; $i < $c; $i++) {
				if ($chunks[$i]['chunk'] === 'pHYs') {
					$found = $i;
					break;
				}
			}

			$data = 'pHYs' . pack('NNC', $meters, $meters, 1);
			$crc = self::crc($data, 13);
			$cr = pack('Na13N', 9, $data, $crc);

			if ($found == -1) {
				if ((2 <= $c) && ($chunks[0]['chunk'] === 'IHDR')) {
					array_splice($chunks, 1, 0, array(
	array('offset' => 33, 'size' => 9, 'chunk' => 'pHYs')
	));

					for ($i = 2; $i < $c; $i++) {
						$chunks[$i]['offset'] += 21;
					}

					$firstPart = substr($bin, 0, 33);
					$secondPart = substr($bin, 33);
					$bin = $firstPart;
					$bin .= $cr;
					$bin .= $secondPart;
				}
			}
			else {
				$bin = substr_replace($bin, $cr, $chunks[$i]['offset'], 21);
			}
		}
	}

	private function internalSetC(&$bin, &$chunks)
	{
		if ((2 <= count($chunks)) && ($chunks[0]['chunk'] === 'IHDR')) {
			$firstPart = substr($bin, 0, 33);
			$secondPart = substr($bin, 33);
			$cr = pack('H*', '0000004C74455874436F707972696768740047656E657261746564207769746820426172636F64652047656E657261746F7220666F722050485020687474703A2F2F7777772E626172636F64657068702E636F6D597F70B8');
			$bin = $firstPart;
			$bin .= $cr;
			$bin .= $secondPart;
		}
	}

	static private function make_crc_table()
	{
		for ($n = 0; $n < 256; $n++) {
			$c = $n;

			for ($k = 0; $k < 8; $k++) {
				if (($c & 1) == 1) {
					$c = 3988292384 ^ self::SHR($c, 1);
				}
				else {
					$c = self::SHR($c, 1);
				}
			}

			self::$crc_table[$n] = $c;
		}

		self::$crc_table_computed = true;
	}

	static private function SHR($x, $n)
	{
		$mask = 1073741824;

		if ($x < 0) {
			$x &= 2147483647;
			$mask = $mask >> ($n - 1);
			return ($x >> $n) | $mask;
		}

		return (int) $x >> (int) $n;
	}

	static private function update_crc($crc, $buf, $len)
	{
		$c = $crc;

		if (!self::$crc_table_computed) {
			self::make_crc_table();
		}

		for ($n = 0; $n < $len; $n++) {
			$c = self::$crc_table[($c ^ ord($buf[$n])) & 255] ^ self::SHR($c, 8);
		}

		return $c;
	}

	static private function crc($data, $len)
	{
		return self::update_crc(-1, $data, $len) ^ -1;
	}
}

?>
