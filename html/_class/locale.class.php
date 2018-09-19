<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class HTML2PDF_locale
{
	/**
     * code of the current used locale
     * @var string
     */
	static protected $_code;
	/**
     * texts of the current used locale
     * @var array
     */
	static protected $_list = array();
	/**
     * directory where locale files are
     * @var string
     */
	static protected $_directory;

	static public function load($code)
	{
		if (self::$_directory === NULL) {
			self::$_directory = dirname(dirname(__FILE__)) . '/locale/';
		}

		$code = strtolower($code);

		if (!preg_match('/^([a-z0-9]+)$/isU', $code)) {
			throw new HTML2PDF_exception(0, 'invalid language code [' . self::$_code . ']');
		}

		self::$_code = $code;
		$file = self::$_directory . self::$_code . '.csv';

		if (!is_file($file)) {
			throw new HTML2PDF_exception(0, 'language code [' . self::$_code . '] unknown. You can create the translation file [' . $file . '] and send it to the webmaster of html2pdf in order to integrate it into a future release');
		}

		self::$_list = array();
		$handle = fopen($file, 'r');

		while (!feof($handle)) {
			$line = fgetcsv($handle);

			if (count($line) != 2) {
				continue;
			}

			self::$_list[trim($line[0])] = trim($line[1]);
		}

		fclose($handle);
	}

	static public function clean()
	{
		self::$_code = NULL;
		self::$_list = array();
	}

	static public function get($key, $default = '######')
	{
		return isset(self::$_list[$key]) ? self::$_list[$key] : $default;
	}
}


?>
