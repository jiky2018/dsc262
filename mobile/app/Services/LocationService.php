<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Services;

class LocationService
{
	private $locationRepository;
	private $goodsRepository;
	private $authService;
	private $goodsAttrRepository;

	public function __construct(\App\Repositories\Location\LocationRepository $locationRepository)
	{
		$this->locationRepository = $locationRepository;
	}

	public function index($region_id = 0)
	{
		if (0 < $region_id) {
			$city = $this->locationRepository->index($region_id);
			return $city;
		}

		$url = '../../data/sc_file/pin_regions.php';
		$list = file_get_contents($url);
		$arr = explode("\r\n", $list);
		$area = $arr[1];

		if (empty($area)) {
			$city = $this->locationRepository->index();

			foreach ($city as $key => $sett) {
				$sname = $sett['region_name'];
				$sett['region_name'] = $sname;
				$licity = $this->pinyin($sname);
				$area[$licity][$key] = $sett;
			}

			ksort($area);
		}

		return $area;
	}

	public function specific($name)
	{
		$name = mb_substr($name, 0, 2);
		$region_name = $this->locationRepository->contrast($name);
		return $region_name;
	}

	public function pinyin($city)
	{
		$fchar = ord($city[0]);
		if ((ord('A') <= $fchar) && ($fchar <= ord('Z'))) {
			return strtoupper($city[0]);
		}

		$s1 = iconv('UTF-8', 'gb2312', $city);
		$s2 = iconv('gb2312', 'UTF-8', $s1);
		$s = ($s2 == $city ? $s1 : $city);
		$asc = ((ord($s[0]) * 256) + ord($s[1])) - 65536;
		if ((-20319 <= $asc) && ($asc <= -20284)) {
			return 'A';
		}

		if (((-20283 <= $asc) && ($asc <= -19776)) || ((-9743 <= $asc) && ($asc <= -9743))) {
			return 'B';
		}

		if ((-19775 <= $asc) && ($asc <= -19219)) {
			return 'C';
		}

		if (((-19218 <= $asc) && ($asc <= -18711)) || ((-9767 <= $asc) && ($asc <= -9767))) {
			return 'D';
		}

		if ((-18710 <= $asc) && ($asc <= -18527)) {
			return 'E';
		}

		if ((-18526 <= $asc) && ($asc <= -18240)) {
			return 'F';
		}

		if ((-18239 <= $asc) && ($asc <= -17923)) {
			return 'G';
		}

		if ((-17922 <= $asc) && ($asc <= -17418)) {
			return 'H';
		}

		if ((-17417 <= $asc) && ($asc <= -16475)) {
			return 'J';
		}

		if ((-16474 <= $asc) && ($asc <= -16213)) {
			return 'K';
		}

		if (((-16212 <= $asc) && ($asc <= -15641)) || ((-7182 <= $asc) && ($asc <= -7182)) || ((-6928 <= $asc) && ($asc <= -6928))) {
			return 'L';
		}

		if ((-15640 <= $asc) && ($asc <= -15166)) {
			return 'M';
		}

		if ((-15165 <= $asc) && ($asc <= -14923)) {
			return 'N';
		}

		if ((-14922 <= $asc) && ($asc <= -14915)) {
			return 'O';
		}

		if (((-14914 <= $asc) && ($asc <= -14631)) || ((-6745 <= $asc) && ($asc <= -6745))) {
			return 'P';
		}

		if (((-14630 <= $asc) && ($asc <= -14150)) || ((-7703 <= $asc) && ($asc <= -7703))) {
			return 'Q';
		}

		if ((-14149 <= $asc) && ($asc <= -14091)) {
			return 'R';
		}

		if ((-14090 <= $asc) && ($asc <= -13319)) {
			return 'S';
		}

		if ((-13318 <= $asc) && ($asc <= -12839)) {
			return 'T';
		}

		if ((-12838 <= $asc) && ($asc <= -12557)) {
			return 'W';
		}

		if ((-12556 <= $asc) && ($asc <= -11848)) {
			return 'X';
		}

		if ((-11847 <= $asc) && ($asc <= -11056)) {
			return 'Y';
		}

		if ((-11055 <= $asc) && ($asc <= -10247)) {
			return 'Z';
		}

		return $asc;
	}
}


?>
