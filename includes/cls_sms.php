<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class sms
{
	public $sms_name;
	public $sms_password;

	public function __construct()
	{
		$this->sms();
	}

	public function sms()
	{
		$this->sms_name = $GLOBALS['_CFG']['sms_ecmoban_user'];
		$this->sms_password = $GLOBALS['_CFG']['sms_ecmoban_password'];
	}

	public function send($phones, $msg = '', $send_date = '', $send_num = 1, $sms_type = '', $version = '1.0', &$sms_error = '', $mobile_code = '')
	{
		if ($GLOBALS['_CFG']['sms_type'] == 0) {
			$contents = $this->get_contents($phones, $msg);

			if (!$contents) {
				return false;
			}

			$sms_url = 'http://106.ihuyi.com/webservice/sms.php?method=Submit&ismbt=1';

			if (1 < count($contents)) {
				foreach ($contents as $key => $val) {
					$post_data = 'account=' . $this->sms_name . '&password=' . $this->sms_password . '&mobile=' . $val['phones'] . '&content=' . rawurlencode($val['content']);
					$get = $this->Post($post_data, $sms_url);
					$gets = $this->xml_to_array($get);
					sleep(1);
				}
			}
			else {
				$post_data = 'account=' . $this->sms_name . '&password=' . $this->sms_password . '&mobile=' . $contents[0]['phones'] . '&content=' . rawurlencode($contents[0]['content']);
				$get = $this->Post($post_data, $sms_url);
				$gets = $this->xml_to_array($get);
			}

			if ($gets['SubmitResult']['code'] == 2) {
				return true;
			}
			else {
				$sms_error = $phones . $gets['SubmitResult']['msg'];
				$this->logResult($sms_error);
				return false;
			}
		}
		else if (1 <= $GLOBALS['_CFG']['sms_type']) {
			$msg = $this->get_usser_sms_msg($msg);

			if (!empty($msg['sms_value'])) {
				$smsParams = array('mobile_phone' => $phones, 'mobilephone' => $phones, 'code' => $msg['code']);
				$send_time = $msg['sms_value'];
			}
			else {
				$smsParams = array('mobile_phone' => $phones, 'mobilephone' => $phones, 'code' => $msg['code'], 'product' => $msg['product']);
				$send_time = 'sms_signin';
			}

			$result = sms_ali($smsParams, $send_time);
			$resp = $GLOBALS['ecs']->ali_yu($result);

			if ($resp->code == 0) {
				return true;
			}
			else {
				if ($resp->sub_msg) {
					$sms_error = $phones . $resp->sub_msg;
				}
				else {
					$sms_error = $phones . ':' . $resp->msg;
				}

				return false;
			}
		}
	}

	public function Post($curlPost, $url)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		$return_str = curl_exec($curl);
		curl_close($curl);
		return $return_str;
	}

	public function xml_to_array($xml)
	{
		$reg = '/<(\\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/';

		if (preg_match_all($reg, $xml, $matches)) {
			$count = count($matches[0]);

			for ($i = 0; $i < $count; $i++) {
				$subxml = $matches[2][$i];
				$key = $matches[1][$i];

				if (preg_match($reg, $subxml)) {
					$arr[$key] = $this->xml_to_array($subxml);
				}
				else {
					$arr[$key] = $subxml;
				}
			}
		}

		return $arr;
	}

	public function get_contents($phones, $msg)
	{
		if (empty($phones) || empty($msg)) {
			return false;
		}

		$phone_key = 0;
		$i = 0;
		$phones = explode(',', $phones);

		foreach ($phones as $key => $value) {
			if ($i < 200) {
				$i++;
			}
			else {
				$i = 0;
				$phone_key++;
			}

			if ($this->is_moblie($value)) {
				$phone[$phone_key][] = $value;
			}
			else {
				$i--;
			}
		}

		if (!empty($phone)) {
			foreach ($phone as $phone_key => $val) {
				if (EC_CHARSET != 'utf-8') {
					$phone_array[$phone_key]['phones'] = implode(',', $val);
					$phone_array[$phone_key]['content'] = $this->auto_charset($msg);
				}
				else {
					$phone_array[$phone_key]['phones'] = implode(',', $val);
					$phone_array[$phone_key]['content'] = $msg;
				}
			}

			return $phone_array;
		}
		else {
			return false;
		}
	}

	public function auto_charset($fContents, $from = 'gbk', $to = 'utf-8')
	{
		$from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
		$to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
		if (strtoupper($from) === strtoupper($to) || empty($fContents) || is_scalar($fContents) && !is_string($fContents)) {
			return $fContents;
		}

		if (is_string($fContents)) {
			if (function_exists('mb_convert_encoding')) {
				return mb_convert_encoding($fContents, $to, $from);
			}
			else if (function_exists('iconv')) {
				return iconv($from, $to, $fContents);
			}
			else {
				return $fContents;
			}
		}
		else if (is_array($fContents)) {
			foreach ($fContents as $key => $val) {
				$_key = auto_charset($key, $from, $to);
				$fContents[$_key] = auto_charset($val, $from, $to);

				if ($key != $_key) {
					unset($fContents[$key]);
				}
			}

			return $fContents;
		}
		else {
			return $fContents;
		}
	}

	public function is_moblie($moblie)
	{
		return preg_match('/^1[34578]\\d{9}$/', $moblie);
	}

	private function logResult($word = '')
	{
		$fp = fopen(ROOT_PATH . '/smserrlog.txt', 'a');
		flock($fp, LOCK_EX);
		fwrite($fp, '执行日期：' . strftime('%Y%m%d%H%M%S', time()) . "\n" . $word . "\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	public function get_usser_sms_msg($msg)
	{
		$arr['code'] = $msg['mobile_code'];
		$arr['product'] = $msg['user_name'];
		$arr['sms_value'] = $msg['sms_value'];
		return $arr;
	}
}

if (!defined('IN_ECS')) {
	exit('Hacking attempt');
}

?>
