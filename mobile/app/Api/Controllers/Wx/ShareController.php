<?php
//zend by 商创网络 Q Q:123456    禁止倒卖 一经发现停止任何服务
namespace App\Api\Controllers\Wx;

class ShareController extends \App\Api\Controllers\Controller
{
	private $shareService;
	private $authService;

	public function __construct(\App\Services\ShareService $shareService, \App\Services\AuthService $authService)
	{
		$this->shareService = $shareService;
		$this->authService = $authService;
	}

	public function index(\Illuminate\Http\Request $request)
	{
		$this->validate($request, array('id' => 'required|integer', 'path' => 'required|string'));
		$uid = $this->authService->authorization();
		if (isset($uid['error']) && 0 < $uid['error']) {
			return $this->apiReturn($uid, 1);
		}

		$share = $this->shareService->Share($uid, $request->get('path'));
		return $this->apiReturn($share);
	}
}

?>
