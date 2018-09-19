<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
if (!defined('K_TCPDF_EXTERNAL_CONFIG')) {
	define('K_TCPDF_EXTERNAL_CONFIG', true);
	if (!isset($_SERVER['DOCUMENT_ROOT']) || empty($_SERVER['DOCUMENT_ROOT'])) {
		if (isset($_SERVER['SCRIPT_FILENAME'])) {
			$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
		}
		else if (isset($_SERVER['PATH_TRANSLATED'])) {
			$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
		}
		else {
			$_SERVER['DOCUMENT_ROOT'] = '/var/www';
		}
	}

	$kPathMain = str_replace('\\', '/', dirname(__FILE__));
	$kPathMain = dirname($kPathMain) . '/';
	$kPathMain .= '_tcpdf_' . HTML2PDF_USED_TCPDF_VERSION . '/';
	define('K_PATH_MAIN', $kPathMain);
	if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
		if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
			$kPathUrl = 'https://';
		}
		else {
			$kPathUrl = 'http://';
		}

		$kPathUrl .= $_SERVER['HTTP_HOST'];
		$kPathUrl .= str_replace('\\', '/', substr(K_PATH_MAIN, strlen($_SERVER['DOCUMENT_ROOT']) - 1));
	}

	define('K_PATH_URL', $kPathUrl);
	define('K_PATH_FONTS', K_PATH_MAIN . 'fonts/');
	define('K_PATH_CACHE', K_PATH_MAIN . 'cache/');
	define('K_PATH_URL_CACHE', K_PATH_URL . 'cache/');
	define('K_PATH_IMAGES', K_PATH_MAIN . 'images/');
	define('K_BLANK_IMAGE', K_PATH_IMAGES . '_blank.png');
	define('PDF_PAGE_FORMAT', 'A4');
	define('PDF_PAGE_ORIENTATION', 'P');
	define('PDF_CREATOR', 'HTML2PDF - TCPDF');
	define('PDF_AUTHOR', 'HTML2PDF - TCPDF');
	define('PDF_HEADER_TITLE', NULL);
	define('PDF_HEADER_STRING', NULL);
	define('PDF_HEADER_LOGO', NULL);
	define('PDF_HEADER_LOGO_WIDTH', NULL);
	define('PDF_UNIT', 'mm');
	define('PDF_MARGIN_HEADER', 0);
	define('PDF_MARGIN_FOOTER', 0);
	define('PDF_MARGIN_TOP', 0);
	define('PDF_MARGIN_BOTTOM', 0);
	define('PDF_MARGIN_LEFT', 0);
	define('PDF_MARGIN_RIGHT', 0);
	define('PDF_FONT_NAME_MAIN', 'helvetica');
	define('PDF_FONT_SIZE_MAIN', 10);
	define('PDF_FONT_NAME_DATA', 'helvetica');
	define('PDF_FONT_SIZE_DATA', 8);
	define('PDF_FONT_MONOSPACED', 'courier');
	define('PDF_IMAGE_SCALE_RATIO', 1);
	define('HEAD_MAGNIFICATION', 1);
	define('K_CELL_HEIGHT_RATIO', 1);
	define('K_TITLE_MAGNIFICATION', 1);
	define('K_SMALL_RATIO', 2 / 3);
	define('K_THAI_TOPCHARS', true);
	define('K_TCPDF_CALLS_IN_HTML', false);
}

?>
