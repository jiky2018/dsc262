<?php

namespace App\Modules\Wechat\Market\Redpack;

// use App\Http\Base\Controllers\Frontend;
use App\Modules\Wechat\Controllers\PluginController;
use App\Extensions\Wechat;
use App\Extensions\WxHongbao;

/**
 * 微信现金红包前台模块
 * Class Redpack
 * @package App\Modules\Wechat\Market\Redpack
 */
class Redpack extends PluginController
{
    private $weObj = '';
    private $wechat_id = 0;
    private $market_id = 0;
    private $marketing_type = 'redpack';

    protected $config = array();

    /**
     * 构造函数
     */
    public function __construct($config = array())
    {
        parent::__construct();

        $this->plugin_name = $this->marketing_type = strtolower(basename(__FILE__, '.php'));
        $this->config = $config;
        $this->ru_id = isset($this->config['ru_id']) ? $this->config['ru_id'] : 0;
        $this->config['plugin_path'] = 'Market';

        // 微信公众号ID
        $this->wechat_id = dao('wechat')->where(array('status' => 1, 'ru_id' => $this->ru_id))->getField('id');

        $this->market_id = I('market_id', 0, 'intval');
        if (empty($this->market_id)) {
            $this->redirect(url('index/index/index'));
        }
        // 资源路径
        $this->plugin_themes = __ROOT__ . '/public/assets/wechat/' . $this->marketing_type;
        $this->assign('plugin_themes', $this->plugin_themes);

        //活动配置
        $data = dao('wechat_marketing')->field('name, starttime, endtime, config')->where(array('id' => $this->market_id, 'marketing_type' => 'redpack', 'wechat_id' => $this->wechat_id))->find();
        $this->config['config'] = unserialize($data['config']);
        $this->config['config']['act_name'] = $data['name'];
        $wechat_arr = $this->get_wechat_config($this->wechat_id);
        $this->config['config']['appid'] = $wechat_arr['appid'];
        $this->config['starttime'] = $data['starttime'];
        $this->config['endtime'] = $data['endtime'];
    }

    /**
     * 网页摇一摇入口活动页面
     */
    public function actionActivity()
    {
        // 页面显示
        $info = dao('wechat_marketing')->field('id, name, logo, background, description, support')->where(array('id' => $this->market_id, 'marketing_type' => 'redpack', 'wechat_id' => $this->wechat_id))->find();

        if (!empty($info)) {
            $info['background'] = get_wechat_image_path($info['background']); // 背景图片
            if (strpos($info['background'], 'no_image') !== false) {
                unset($info['background']);
            }

            $status = get_status($this->config['starttime'], $this->config['endtime']); // 活动状态 0未开始,1正在进行,2已结束

            if ($status == 0) {
                $flag = "活动未开始"; // 未开始
            }
            if ($status == 2) {
                $flag = "活动已结束"; // 已结束
            }

            $is_subscribe = dao('wechat_user')->where(array('openid' => $_SESSION['openid'], 'wechat_id' => $this->wechat_id))->getField('subscribe');
            if ($is_subscribe == 0) {
                $flag = "请先关注微信公众号！";
            }
            $this->assign('flag', $flag);

            $shake_url = __HOST__ . url('wechat/index/market_show', array('type' => 'redpack', 'function' => 'shake', 'market_id' => $this->market_id)); // 摇一摇页面地址
            $this->assign('shake_url', $shake_url);

            // 分享
            $page_title = $info['name'];
            $description = $info['description'];
            // $link = $share['link'];
            $page_img = get_wechat_image_path($info['background']);
        }

        $this->assign('info', $info);
        $this->assign('page_title', $page_title);
        $this->assign('description', $description);
        $this->assign('link', $link);
        $this->assign('page_img', $page_img);
        $this->show_display('activity', $this->config);
    }

    /**
     * 微信摇一摇领取红包页面
     */
    public function actionShake()
    {
        if (IS_POST) {
            $time = I('time');
            $last = I('last');
            // $market_id = I('market_id');

            $openid = $_SESSION['openid'];
            if (empty($openid) || $openid == '' || $openid == null) {
                $result = array(
                    'icon' => $this->plugin_themes . "/images/error.png",
                    'content' => '请关注微信公众号或在微信客户端中打开！！',
                    'url' => ''
                );
                // return json_encode($result);
                exit(json_encode($result));
            }

            $cha = $time - $last; // 计算与上一次摇一摇的时间差(单位ms 毫秒),需要间隔4000ms以上
            if ($cha < 4000) {
                $result = array(
                    'status' => 0,
                    'icon' => $this->plugin_themes . "/images/icon.jpg",
                    'content' => "歇一会，您摇得过于频繁了！请隔4秒以上再试 ~~",
                    'url' => ''
                );
                exit(json_encode($result));
                // return json_encode($result);
            }
            // 计算随机数最小值和最大值之间的值，当产生的随机数与此处填的一个值相符即发放红包
            $min = $this->config['config']['randmin'];
            $max = $this->config['config']['randmax'];
            $sendNum = $this->config['config']['sendnum'];
            $sendArr = explode(',', $sendNum);
            $rand = rand($min, $max);
            $isInclude = in_array($rand, $sendArr);

            $hb_type = $this->config['config']['hb_type'];

            if ($isInclude) {
                $status = get_status($this->config['starttime'], $this->config['endtime']); // 活动状态 0未开始,1正在进行,2已结束

                if ($status == 0) {
                    $result = array(
                        'status' => 0,
                        'icon' => $this->plugin_themes . "/images/icon.jpg",
                        'content' => '您来早了，活动还没开始！！！',
                        'url' => ''
                    );
                    exit(json_encode($result));
                    // return json_encode($result);
                } elseif ($status == 2) {
                    $result = array(
                        'status' => 0,
                        'icon' => $this->plugin_themes . "/images/icon.jpg",
                        'content' => '您来迟了，活动已结束！！！',
                        'url' => ''
                    );
                    exit(json_encode($result));
                    // return json_encode($result);
                } else {
                    $log = dao('wechat_redpack_log')->field('hassub')->where(array('wechat_id' => $this->wechat_id, 'market_id' => $this->market_id, 'openid' => $openid))->find();
                    if (count($log) == 1) {
                        // 已领取过红包
                        if ($log['hassub'] == 1) {
                            $temp = "您已参与过本活动，请不要重复操作！";
                            $result = array(
                                'status' => 0,
                                'icon' => $this->plugin_themes . "/images/icon.jpg",
                                'content' => $temp,
                                'url' => ''
                            );
                        } else {
                            // 未领取过红包
                            $temp = $this->sendRedpack($openid, $hb_type);
                            $result = array(
                                'status' => 1,
                                'icon' => $this->plugin_themes . "/images/icon.jpg",
                                'content' => $temp,
                                'url' => ''
                            );
                        }
                    } elseif (count($log) == 0) {
                        // 未参与活动
                        $data = array(
                            'wechat_id' => $this->wechat_id,
                            'market_id' => $this->market_id,
                            'hb_type' => $hb_type,
                            'openid' => $openid,
                            'hassub' => 0,
                        );
                        dao('wechat_redpack_log')->data($data)->add();

                        $temp = $this->sendRedpack($openid, $hb_type);
                        $result = array(
                            'status' => 1,
                            'icon' => $this->plugin_themes . "/images/icon.jpg",
                            'content' => $temp,
                            'url' => ''
                        );
                    }
                    exit(json_encode($result));
                    // return json_encode($result);
                }
            } else {
                // 当用户没有摇到红包时展示广告内容
                $total = dao('wechat_redpack_advertice')->where(array('wechat_id' => $this->wechat_id, 'market_id' => $this->market_id))->count();
                if ($total == 0) {
                    $result = array(
                        'icon' => $this->plugin_themes . "/images/icon.jpg",
                        'content' => '什么都没摇到~~~',
                        'url' => ''
                    );
                    // return json_encode($result);
                    exit(json_encode($result));
                }
                // 随机一张广告
                $pageindex = rand(0, $total - 1);
                $temp = dao('wechat_redpack_advertice')->field('icon, content, url')->where(array('wechat_id' => $this->wechat_id, 'market_id' => $this->market_id))->limit($pageindex, 1)->select();
                $temp = reset($temp);
                $temp['icon'] = get_wechat_image_path($temp['icon']);

                $result = array(
                    'icon' => $temp['icon'],
                    'content' => $temp['content'],
                    'url' => $temp['url']
                );
                exit(json_encode($result));
                // return json_encode($result);
            }
        }

        $this->assign('back_url', __HOST__ . url('wechat/index/market_show', array('type' => 'redpack', 'function' => 'activity', 'market_id' => $this->market_id)));
        $this->assign('market_id', $this->market_id);
        $this->assign('page_title', "微信摇一摇活动页面");
        $this->show_display('shake', $this->config);
    }

    /**
     * 发送红包
     * @param $param_openid  用户openid
     * @param $hb_type  发送红包类型 0 普通、1 裂变
     * @return
     */
    public function sendRedpack($param_openid, $hb_type = 0)
    {
        // 随机计算发放红包
        $randmin = $this->config['config']['randmin'];
        $randmax = $this->config['config']['randmax'];
        $sendnum = $this->config['config']['sendnum'];

        $sendArr = explode(',', $sendnum);
        $rand = rand($randmin, $randmax);
        $isInclude = in_array($rand, $sendArr);

        if ($isInclude) {
            // 设置参数
            $mch_billno = $mchid . date('YmdHis') . rand(1000, 9999);
            // 红包金额
            $money = $this->config['config']['base_money'] + rand(0, $this->config['config']['money_extra']);
            $money = $money * 100; // 转换为分
            if ($hb_type == 0) {
                $total_num = 1;
            } else {
                $total_num = $total_num > 3 ? $total_num : 3; // 裂变红包发放总人数，最小3人
            }

            $appid = $this->config['config']['appid'];
            $mchid = $this->config['config']['mchid'];
            $partnerkey = $this->config['config']['partner'];
            $nick_name = $this->config['config']['nick_name'];
            $send_name = $this->config['config']['send_name'];
            $wishing = $this->config['config']['wishing'];

            $act_name = $this->config['config']['act_name'];  //活动名称

            $remark = $this->config['config']['remark'];
            // 场景ID
            $scene_id = strtoupper($this->config['config']['scene_id']);

            $configure = array(
                'appid' => $appid,
                'partnerkey' => $partnerkey,
            );
            $WxHongbao = new WxHongbao($configure);

            if ($hb_type == 0) {
                // 普通红包参数
                $WxHongbao->setParameter("nonce_str", $WxHongbao->create_noncestr()); // 随机字符串，不长于32位
                $WxHongbao->setParameter("mch_billno", $mch_billno); // 商户订单号（每个订单号必须唯一）组成：mch_id+yyyymmdd+10位一天内不能重复的数字。
                $WxHongbao->setParameter("mch_id", $mchid); // 商户号
                $WxHongbao->setParameter("wxappid", $appid); // 公众账号appid
                $WxHongbao->setParameter("nick_name", $nick_name); //提供方名称
                $WxHongbao->setParameter("send_name", $send_name); //红包发送者名称,商户名称
                $WxHongbao->setParameter("re_openid", $param_openid); // 接受红包的用户,用户在wxappid下的openid
                $WxHongbao->setParameter("total_amount", $money); // 付款金额，单位分
                $WxHongbao->setParameter("min_value", $money); // 最小红包金额，单位分
                $WxHongbao->setParameter("max_value", $money); // 最大红包金额，单位分  发放金额、最小金额、最大金额必须相等
                $WxHongbao->setParameter("total_num", $total_num); // 红包发放总人数 1
                $WxHongbao->setParameter("wishing", $wishing); // 红包祝福语
                $WxHongbao->setParameter("client_ip", $_SERVER['REMOTE_ADDR']); // 终端ip
                $WxHongbao->setParameter("act_name", $act_name); // 活动名称
                $WxHongbao->setParameter("remark", $remark); // 备注信息
            } elseif ($hb_type == 1) {
                // 裂变红包参数
                $WxHongbao->setParameter("nonce_str", $WxHongbao->create_noncestr()); // 随机字符串，不长于32位
                $WxHongbao->setParameter("mch_billno", $mch_billno); // 商户订单号（每个订单号必须唯一）组成：mch_id+yyyymmdd+10位一天内不能重复的数字。
                $WxHongbao->setParameter("mch_id", $mchid); // 商户号
                $WxHongbao->setParameter("wxappid", $appid); // 公众账号appid
                $WxHongbao->setParameter("nick_name", $nick_name); //提供方名称
                $WxHongbao->setParameter("send_name", $send_name); //红包发送者名称,商户名称
                $WxHongbao->setParameter("re_openid", $param_openid); // 接受红包的用户,用户在wxappid下的openid
                $WxHongbao->setParameter("total_amount", $money); // 付款金额，单位分 最少300
                // $WxHongbao->setParameter("min_value", $money); // 最小红包金额，单位分
                // $WxHongbao->setParameter("max_value", $money); // 最大红包金额，单位分  发放金额、最小金额、最大金额必须相等
                $WxHongbao->setParameter("total_num", $total_num); // 红包发放总人数，最小3人
                $WxHongbao->setParameter("amt_type", 'ALL_RAND'); // 红包金额设置方式
                $WxHongbao->setParameter("wishing", $wishing); // 红包祝福语
                $WxHongbao->setParameter("act_name", $act_name); // 活动名称
                $WxHongbao->setParameter("remark", $remark); // 备注信息
            }
            // 发放红包使用场景，红包金额大于200时必传
            if ($scene_id && $scene_id > 0) {
                $WxHongbao->setParameter("scene_id", $scene_id);
            }
            $hb_type = $hb_type == 1 ? 'GROUP' : 'NORMAL';
            $responseObj = $WxHongbao->creat_sendredpack($hb_type);
            // logResult($responseObj);
            $return_code = $responseObj->return_code;
            $result_code = $responseObj->result_code;

            if ($return_code == 'SUCCESS') {
                if ($result_code == 'SUCCESS') {
                    $total_amount = $responseObj->total_amount * 1.0 / 100;
                    $re_openid = $responseObj->re_openid;
                    $mch_billno = $responseObj->mch_billno;
                    $mch_id = $responseObj->mch_id;
                    $wxappid = $responseObj->wxappid;

                    // 返回成功更新
                    $where = array(
                        'wechat_id' => $this->wechat_id,
                        'market_id' => $this->market_id,
                        'openid' => !empty($re_openid) ? $re_openid : $param_openid,
                    );
                    $data = array(
                        'hassub' => 1,
                        'money' => $total_amount,
                        'time' => gmtime(),
                        'mch_billno' => $mch_billno,
                        'mch_id' => $mch_id,
                        'wxappid' => $wxappid,
                        'bill_type' => 'MCHT',
                    );
                    $result = dao('wechat_redpack_log')->data($data)->where($where)->save();

                    return "红包发放成功！金额为：" . $total_amount . "元！拆开发放的红包即可领取红包！";
                } else {
                    if ($responseObj->err_code == 'NOTENOUGH') {
                        return "您来迟了，红包已经发完！！！";
                    } elseif ($responseObj->err_code == 'TIME_LIMITED') {
                        return "现在非红包发放时间，请在北京时间0:00-8:00之外的时间前来领取";
                    } elseif ($responseObj->err_code == 'SYSTEMERROR') {
                        return "系统繁忙，请稍后再试！";
                    } elseif ($responseObj->err_code == 'DAY_OVER_LIMITED') {
                        return "今日红包已达上限，请明日再试！";
                    } elseif ($responseObj->err_code == 'SECOND_OVER_LIMITED') {
                        return "每分钟红包已达上限，请稍后再试！";
                    }
                    return "红包发放失败！" . $responseObj->return_msg . "！请稍后再试！";
                }
            } else {
                if ($responseObj->err_code == 'NOTENOUGH') {
                    return "您来迟了，红包已经发放完！！!";
                } elseif ($responseObj->err_code == 'TIME_LIMITED') {
                    return "现在非红包发放时间，请在北京时间0:00-8:00之外的时间前来领取";
                } elseif ($responseObj->err_code == 'SYSTEMERROR') {
                    return "系统繁忙，请稍后再试！";
                } elseif ($responseObj->err_code == 'DAY_OVER_LIMITED') {
                    return "今日红包已达上限，请明日再试！";
                } elseif ($responseObj->err_code == 'SECOND_OVER_LIMITED') {
                    return "每分钟红包已达上限，请稍后再试！";
                }
                return "红包发放失败！" . $responseObj->return_msg . "！请稍后再试！";
            }
        } else {
            $where = array(
                'wechat_id' => $this->wechat_id,
                'market_id' => $this->market_id,
                'openid' => $param_openid,
            );
            $data = array(
                'hassub' => 1,
                'money' => 0,
                'time' => gmtime(),
            );
            $result = dao('wechat_redpack_log')->data($data)->where($where)->save();
            return "很遗憾，您没有抢到红包！感谢您的参与！";
        }
    }

    /**
     * 获取公众号配置
     *
     * @param string $secret_key
     * @return array
     */
    private function get_wechat_config($wechat_id = 0)
    {
        $config = dao('wechat')->field('appid, appsecret')->where(array('id' => $wechat_id, 'status' => 1))->find();
        if (empty($config)) {
            $config = array();
        }
        return $config;
    }

    /**
     * 获取数据
     */
    public function returnData($fromusername, $info)
    {
    }

    /**
     * 积分赠送
     *
     * @param unknown $fromusername
     * @param unknown $info
     */
    public function updatePoint($fromusername, $info)
    {
    }

    /**
     * 页面显示
     */
    public function html_show()
    {
    }

    /**
     * 执行方法
     */
    public function executeAction()
    {
    }

}
