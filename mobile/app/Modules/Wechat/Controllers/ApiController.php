<?php

namespace App\Modules\Wechat\Controllers;

use App\Modules\Base\Controllers\Frontend;
use App\Extensions\Wechat;

class ApiController extends \App\Modules\Base\Controllers\FrontendController
{
    private $weObj = '';
    private $wechat_id = 0;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        // 获取公众号配置
        if (isset($_COOKIE['ectouch_ru_id'])) {
            $ru_id = $_COOKIE['ectouch_ru_id'];
        } else {
            $ru_id = 0;
        }
        $wxConf = $this->getConfig($ru_id);
        $this->weObj = new Wechat($wxConf);

        $this->wechat_id = $wxConf['id'];
    }

    /**
     * PC后台发送发货通知模板消息接口方法
     */
    public function actionIndex()
    {
        $user_id = I('get.user_id', 0, 'intval');
        $code = I('get.code', '', 'trim');
        $pushData = I('get.pushData', '', 'trim');
        $url = I('get.url', '');
        $url = $url ? base64_decode(urldecode($url)) : '';

        if ($user_id && $code) {
            $pushData = stripslashes(urldecode($pushData));
            //转换成数组
            $pushData = unserialize($pushData);
            // 发送微信通模板消息
            push_template($code, $pushData, $url, $user_id);
        }
    }

    /**
     * JSSDK 参数
     * @return
     */
    public function actionJssdk()
    {
        $url = input('url', '', 'addslashes');
        if (!empty($url)) {
            $sdk = $this->weObj->getJsSign($url);
            $data = array('status' => '200', 'data' => $sdk);
        } else {
            $data = array('status' => '100', 'message' => '缺少参数');
        }
        exit(json_encode($data));
    }

    /**
     * 分享统计
     * @param string $jsApiname 分享的Api接口名
     * @return
     */
    public function actionCount()
    {
        $jsApiname = input('jsApiname', '', 'trim');
        $link = input('link', '', 'addslashes');

        $share_type = 0;
        switch ($jsApiname) {
            case 'shareTimeline':
                $share_type = 1;//对应分享到朋友圈接口 onMenuShareTimeline
                break;
            case 'sendAppMessage':
                $share_type = 2;// 对应分享给朋友接口 onMenuShareAppMessage
                break;
            case 'shareQQ':
                $share_type = 3;// 对应分享到QQ接口 onMenuShareQQ
                break;
            case 'shareQZone':
                $share_type = 4;// 对应分享到QQ空间接口 onMenuShareQZone
                break;
            default:
                break;
        }
        $openid = $_SESSION['openid'];
        if (!empty($share_type) && !empty($openid)) {
            $data = array(
                'wechat_id' => $this->wechat_id,
                'openid' => $openid,
                'share_type' => $share_type,
                'link' => $link,
                'share_time' => gmtime(),
            );
            dao('wechat_share_count')->data($data)->add();
            $result = array('status' => '200', 'msg' => '统计成功');
        } else {
            $result = array('status' => 'fail', 'msg' => '统计失败');
        }
        exit(json($result));
    }


    /**
     * 获取公众号配置
     *
     * @return array
     */
    private function getConfig($ru_id = 0)
    {
        // 公众号信息
        if ($ru_id > 0) {
            $where = array('ru_id' => $ru_id, 'status' => 1);
        } else {
            $where = array('default_wx' => 1, 'status' => 1);
        }
        $wxinfo = dao('wechat')->field('id, token, appid, appsecret')->where($where)->find();
        return $wxinfo;
    }

}
