<?php

namespace App\Modules\Wechat\Plugins\Sign;

use App\Modules\Wechat\Controllers\PluginController;

/**
 * 签到送积分
 *
 * @author wanglu
 *
 */
class Sign extends PluginController
{
    // 插件名称
    protected $plugin_name = '';
    // 配置
    protected $cfg = array();

    /**
     * 构造方法
     *
     * @param unknown $cfg
     */
    public function __construct($cfg = array())
    {
        parent::__construct();
        $this->plugin_name = strtolower(basename(__FILE__, '.php'));
        $this->cfg = $cfg;
    }

    /**
     * 安装
     */
    public function install()
    {
        $this->plugin_display('install', $this->cfg);
    }

    /**
     * 获取数据
     */
    public function returnData($fromusername, $info)
    {
        $articles = array('type' => 'text', 'content' => '签到失败');
        // 配置信息
        $config = array();
        $config = unserialize($info['config']);
        if (isset($config['point_status']) && $config['point_status'] == 1) {
            $users = get_wechat_user_id($fromusername);
            if ($users) {
                // 签到判断 最后一次签到时间 如果有
                $condition['openid'] = $fromusername;
                $condition['keywords'] = $info['command'];
                $result = dao('wechat_point')->field('createtime')->where($condition)->order('log_id desc')->find();

                $nowtime_format = local_date('Y-m-d', gmtime());
                $createtime = local_date('Y-m-d', $result['createtime']);

                // $result 为空 说明今天未签到 || 若存在 并且格式化时间day != 当前时间day 也说明未签到
                if (empty($result) || $createtime != $nowtime_format) {
                    if (!empty($config['rank_point_value']) || !empty($config['pay_point_value'])) {
                        // 积分赠送
                        $rs = $this->updatePoint($fromusername, $info, $config['rank_point_value'], $config['pay_point_value']);
                        if ($rs == true) {
                            $tips = "系统赠送您 ";
                            $tips .= !empty($config['rank_point_value']) ? $config['rank_point_value'] . " 等级积分 " : '';
                            $tips .= !empty($config['pay_point_value']) ? $config['pay_point_value'] . " 消费积分 " : '';

                            $articles['content'] = '签到成功！' . $tips;
                        }
                    }
                } else {
                    $articles['content'] = '今日签到次数已用完，请明天再来';
                }
            } else {
                $articles['content'] = '尚未绑定商城会员,请先绑定';
            }
        } else {
            $articles['content'] = '未启用签到送积分';
        }
        return $articles;
    }

    /**
     * 积分赠送
     *
     * @param unknown $fromusername
     * @param unknown $info
     */
    public function updatePoint($fromusername, $info, $rank_point_value = 0, $pay_point_value = 0)
    {
        return $this->do_point($fromusername, $info, $rank_point_value, $pay_point_value);
    }

    /**
     * 行为操作
     */
    public function executeAction()
    {
    }
}
