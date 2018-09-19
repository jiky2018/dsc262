<?php

namespace App\Modules\Wechat\Plugins\Bonus;

use App\Modules\Wechat\Controllers\PluginController;

/**
 * 关注送红包
 *
 * @author wanglu
 *
 */
class Bonus extends PluginController
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
        //红包数据，线下发放类型
        $time = gmtime();
        $sql = "SELECT type_id, type_name, type_money FROM {pre}bonus_type WHERE send_type = 3 AND send_end_date > $time";
        $bonus = $GLOBALS['db']->query($sql);
        $this->cfg['bonus'] = $bonus;
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
        $articles = array('type' => 'text', 'content' => '');
        if (!empty($info)) {
            // 配置信息
            $config = array();
            $config = unserialize($info['config']);
            //开启红包赠送
            if (isset($config['bonus_status']) && $config['bonus_status'] == 1 && !empty($this->cfg['bonus'])) {
                //用户第一次关注赠送红包并且设置了赠送的红包
                $uid = dao('wechat_user')->where(array('openid' => $fromusername))->getField('ect_uid');
                if (!empty($uid) && !empty($config['bonus'])) {
                    $time = gmtime();
                    $sql = "SELECT count(*) as num FROM {pre}user_bonus u LEFT JOIN {pre}bonus_type b ON u.bonus_type_id = b.type_id WHERE u.user_id = $uid AND b.send_type = 3 AND b.type_id = " . $config['bonus'] . " AND b.send_end_date > " . $time;
                    $bonus_num = $GLOBALS['db']->query($sql);
                    if ($bonus_num[0]['num'] > 0) {
                        $articles['content'] = '红包已经赠送过了，不要重复领取哦！';
                    } else {
                        $data['bonus_type_id'] = $config['bonus'];
                        $data['bonus_sn'] = 0;
                        $data['user_id'] = $uid;
                        $data['used_time'] = 0;
                        $data['order_id'] = 0;
                        $data['emailed'] = 0;
                        dao('user_bonus')->data($data)->add();

                        $where = array(
                            'send_type' => 3,
                            'type_id' => $config['bonus']
                        );
                        $type_money = dao('bonus_type')->where($where)->getField('type_money');
                        $articles['content'] = '感谢您的关注，赠送您一个 ' . $type_money . '元红包';
                        // 积分赠送
                        $this->updatePoint($fromusername, $info);
                    }
                }
            }
        }
        return $articles;
    }

    /**
     * 积分赠送
     *
     * @param unknown $fromusername
     * @param unknown $info
     */
    public function updatePoint($fromusername, $info)
    {
        if (!empty($info)) {
            // 配置信息
            $config = array();
            $config = unserialize($info['config']);
            // 开启积分赠送
            if (isset($config['point_status']) && $config['point_status'] == 1) {
                $where = 'openid = "' . $fromusername . '" and keywords = "' . $info['command'] . '" and createtime > (UNIX_TIMESTAMP(NOW())- ' . $config['point_interval'] . ')';
                $sql = 'SELECT count(*) as num FROM {pre}wechat_point WHERE ' . $where . 'ORDER BY createtime DESC';
                $num = $GLOBALS['db']->query($sql);
                // 当前时间减去时间间隔得到的历史时间之后赠送的次数
                if ($num[0]['num'] < $config['point_num']) {
                    $this->do_point($fromusername, $info, $config['point_value']);
                }
            }
        }
    }

    /**
     * 行为操作
     */
    public function executeAction()
    {
    }
}
