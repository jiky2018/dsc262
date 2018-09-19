<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class TCPDF2DBarcode
{
	/**
	 * @var array representation of barcode.
	 * @access protected
	 */
	protected $barcode_array = false;

	public function __construct($code, $type)
	{
		$this->setBarcode($code, $type);
	}

	public function getBarcodeArray()
	{
		return $this->barcode_array;
	}

	public function setBarcode($code, $type)
	{
		$mode = explode(',', $type);
		$qrtype = strtoupper($mode[0]);

		switch ($qrtype) {
		case 'QRCODE':
			require_once dirname(__FILE__) . '/qrcode.php';
			if (!isset($mode[1]) || !in_array($mode[1], array('L', 'M', 'Q', 'H'))) {
				$mode[1] = 'L';
			}

			$qrcode = new QRcode($code, strtoupper($mode[1]));
			$this->barcode_array = $qrcode->getBarcodeArray();
			break;

		case 'RAW':
		case 'RAW2':
			$code = preg_replace('/[\\s]*/si', '', $code);

			if (strlen($code) < 3) {
				break;
			}

			if ($qrtype == 'RAW') {
				$rows = explode(',', $code);
			}
			else {
				$code = substr($code, 1, -1);
				$rows = explode('][', $code);
			}

			$this->barcode_array['num_rows'] = count($rows);
			$this->barcode_array['num_cols'] = strlen($rows[0]);
			$this->barcode_array['bcode'] = array();

			foreach ($rows as $r) {
				$this->barcode_array['bcode'][] = str_split($r, 1);
			}

			break;

		case 'TEST':
			$this->barcode_array['num_rows'] = 5;
			$this->barcode_array['num_cols'] = 15;
			$this->barcode_array['bcode'] = array(
	array(1, 1, 1, 0, 1, 1, 1, 0, 1, 1, 1, 0, 1, 1, 1),
	array(0, 1, 0, 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0),
	array(0, 1, 0, 0, 1, 1, 0, 0, 1, 1, 1, 0, 0, 1, 0),
	array(0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0),
	array(0, 1, 0, 0, 1, 1, 1, 0, 1, 1, 1, 0, 0, 1, 0)
	);
			break;

		default:
			$this->barcode_array = false;
		}
	}
}


?>
