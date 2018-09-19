<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Api\Foundation;

class Token
{
	/**
     * When checking nbf, iat or expiration times,
     * we want to provide some extra leeway time to
     * account for clock skew.
     */
	static public $leeway = 0;
	static public $supported_algs = array(
		'HS256' => array('hash_hmac', 'SHA256'),
		'HS512' => array('hash_hmac', 'SHA512'),
		'HS384' => array('hash_hmac', 'SHA384'),
		'RS256' => array('openssl', 'SHA256')
		);

	static public function decode($jwt)
	{
		$key = app('App\\Services\\WxappService')->getWxappConfigByCode('token_secret');
		$allowed_algs = array(app('config')->get('app.TOKEN_ALG'));

		if (empty($key)) {
			return false;
		}

		$tks = explode('.', $jwt);

		if (count($tks) != 3) {
			return false;
		}

		list($headb64, $bodyb64, $cryptob64) = $tks;

		if (null === ($header = self::jsonDecode(self::urlsafeB64Decode($headb64)))) {
			return false;
		}

		if (null === ($payload = self::jsonDecode(self::urlsafeB64Decode($bodyb64)))) {
			return false;
		}

		$sig = self::urlsafeB64Decode($cryptob64);

		if (empty($header->alg)) {
			return false;
		}

		if (empty(self::$supported_algs[$header->alg])) {
			return false;
		}

		if (!is_array($allowed_algs) || !in_array($header->alg, $allowed_algs)) {
			return false;
		}

		if (is_array($key) || $key instanceof \ArrayAccess) {
			if (isset($header->kid)) {
				$key = $key[$header->kid];
			}
			else {
				return false;
			}
		}

		if (!self::verify($headb64 . '.' . $bodyb64, $sig, $key, $header->alg)) {
			return false;
		}

		if (isset($payload->nbf) && ((time() + self::$leeway) < $payload->nbf)) {
			return false;
		}

		if (isset($payload->iat) && ((time() + self::$leeway) < $payload->iat)) {
			return false;
		}

		if (isset($payload->exp) && ($payload->exp <= time() - self::$leeway)) {
			return 10002;
		}

		if (isset($payload->uid)) {
			if (!self::verifyPlatform($payload->uid)) {
				return false;
			}
		}

		return $payload;
	}

	static public function authorization()
	{
		$token = $_SERVER[strtoupper('http_x_ectouch_authorization')];

		if ($payload = self::decode($token)) {
			if (is_object($payload) && property_exists($payload, 'uid')) {
				return $payload->uid;
			}
		}

		if ($payload == 10002) {
			return 'token-expired';
		}

		return false;
	}

	static public function refresh()
	{
		$headers = Yii::$app->request->headers;
		$token = $headers->get('X-' . Yii::$app->params['name'] . '-Authorization');

		if ($token) {
			if ($payload = self::decode($token)) {
				if (is_object($payload)) {
					if (property_exists($payload, 'exp')) {
						if ((Yii::$app->params['TOKEN_REFRESH_TTL'] * 60) < ((time() + (Yii::$app->params['TOKEN_TTL'] * 60)) - $payload->exp)) {
							return self::new_token($payload);
						}
					}

					if (property_exists($payload, 'ver')) {
						if (version_compare(Yii::$app->params['TOKEN_VER'], $payload->ver) != 0) {
							return self::new_token($payload);
						}
					}

					if (!property_exists($payload, 'ver')) {
						return self::new_token($payload);
					}
				}
			}
		}

		return false;
	}

	static private function new_token($payload)
	{
		return self::encode(array('uid' => $payload->uid, 'ver' => Yii::$app->params['TOKEN_VER']));
	}

	static private function str_mix($domain, $uuid)
	{
		$uuid = explode('-', $uuid);
		$domain = explode('.', $domain);
		$mixed = array_merge($uuid, $domain);
		arsort($mixed);
		return implode('-', $mixed);
	}

	static private function parse_domain($url)
	{
		$data = parse_url($url);
		$host = $data['host'];

		if (preg_match('/^www.*$/', $host)) {
			return str_replace('www.', '', $host);
		}

		return $host;
	}

	static public function encode($payload, $keyId = NULL, $head = NULL)
	{
		$key = app('App\\Services\\WxappService')->getWxappConfigByCode('token_secret');
		$alg = app('config')->get('app.TOKEN_ALG');

		if (!isset($payload['exp'])) {
			$payload['exp'] = time() + (app('config')->get('app.TOKEN_TTL') * 60);
		}

		if (isset($payload['uid'])) {
			$payload['platform'] = 'wx';
		}

		$header = array('typ' => 'JWT', 'alg' => $alg);

		if ($keyId !== null) {
			$header['kid'] = $keyId;
		}

		if (isset($head) && is_array($head)) {
			$header = array_merge($head, $header);
		}

		$segments = array();
		$segments[] = self::urlsafeB64Encode(self::jsonEncode($header));
		$segments[] = self::urlsafeB64Encode(self::jsonEncode($payload));
		$signing_input = implode('.', $segments);
		$signature = self::sign($signing_input, $key, $alg);
		$segments[] = self::urlsafeB64Encode($signature);
		return implode('.', $segments);
	}

	static public function sign($msg, $key, $alg = 'HS256')
	{
		if (empty(self::$supported_algs[$alg])) {
			return false;
		}

		list($function, $algorithm) = self::$supported_algs[$alg];

		switch ($function) {
		case 'hash_hmac':
			return hash_hmac($algorithm, $msg, $key, true);
		case 'openssl':
			$signature = '';
			$success = openssl_sign($msg, $signature, $key, $algorithm);

			if (!$success) {
				return false;
			}
			else {
				return $signature;
			}
		}
	}

	static private function verify($msg, $signature, $key, $alg)
	{
		if (empty(self::$supported_algs[$alg])) {
			return false;
		}

		list($function, $algorithm) = self::$supported_algs[$alg];

		switch ($function) {
		case 'openssl':
			$success = openssl_verify($msg, $signature, $key, $algorithm);

			if (!$success) {
				return false;
			}
			else {
				return $signature;
			}
		case 'hash_hmac':
		default:
			$hash = hash_hmac($algorithm, $msg, $key, true);

			if (function_exists('hash_equals')) {
				return hash_equals($signature, $hash);
			}

			$len = min(self::safeStrlen($signature), self::safeStrlen($hash));
			$status = 0;

			for ($i = 0; $i < $len; $i++) {
				$status |= ord($signature[$i]) ^ ord($hash[$i]);
			}

			$status |= self::safeStrlen($signature) ^ self::safeStrlen($hash);
			return $status === 0;
		}
	}

	static public function jsonDecode($input)
	{
		if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && (4 < PHP_INT_SIZE))) {
			$obj = json_decode($input, false, 512, JSON_BIGINT_AS_STRING);
		}
		else {
			$max_int_length = strlen((string) PHP_INT_MAX) - 1;
			$json_without_bigints = preg_replace('/:\\s*(-?\\d{' . $max_int_length . ',})/', ': "$1"', $input);
			$obj = json_decode($json_without_bigints);
		}

		if (function_exists('json_last_error') && ($errno = json_last_error())) {
			self::handleJsonError($errno);
		}
		else {
			if (($obj === null) && ($input !== 'null')) {
				return false;
			}
		}

		return $obj;
	}

	static public function jsonEncode($input)
	{
		$json = json_encode($input);
		if (function_exists('json_last_error') && ($errno = json_last_error())) {
			self::handleJsonError($errno);
		}
		else {
			if (($json === 'null') && ($input !== null)) {
				return false;
			}
		}

		return $json;
	}

	static public function urlsafeB64Decode($input)
	{
		$remainder = strlen($input) % 4;

		if ($remainder) {
			$padlen = 4 - $remainder;
			$input .= str_repeat('=', $padlen);
		}

		return base64_decode(strtr($input, '-_', '+/'));
	}

	static public function urlsafeB64Encode($input)
	{
		return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
	}

	static private function handleJsonError($errno)
	{
		$messages = array(JSON_ERROR_DEPTH => 'Maximum stack depth exceeded', JSON_ERROR_CTRL_CHAR => 'Unexpected control character found', JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON');
		return false;
	}

	static private function safeStrlen($str)
	{
		if (function_exists('mb_strlen')) {
			return mb_strlen($str, '8bit');
		}

		return strlen($str);
	}

	static private function setPlatform($uid)
	{
		$platform = Header::getUserAgent('Platform');
		$key = 'platform:' . $uid;
		Yii::$app->cache->set($key, $platform, 0);
		return $platform;
	}

	static private function verifyPlatform($uid)
	{
		return true;
		$platform = Header::getUserAgent('Platform');
		$key = 'platform:' . $uid;

		if ($platform == Yii::$app->cache->get($key)) {
			return true;
		}

		return false;
	}
}


?>
