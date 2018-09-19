<?php

namespace App\Modules\Wechat\Plugins\Jfcx;

use App\Modules\Wechat\Controllers\PluginController;

/**
 * 积分查询
 *
 * @author wanglu
 *
 */
class Jfcx extends PluginController
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
        // $this->load_helper(array('common'));
        $articles = array('type' => 'text', 'content' => '暂无积分信息');
        $users = get_wechat_user_id($fromusername);
        if (!empty($users)) {
            $data = dao('users')->field('rank_points, pay_points, user_money')->where(array('user_id' => $users['user_id']))->find();
            if (!empty($data)) {
                $data['user_money'] = strip_tags(price_format($data['user_money'], false));
                $articles['content'] = '余额：' . $data['user_money'] . "\r\n" . '等级积分：' . $data['rank_points'] . "\r\n" . '消费积分：' . $data['pay_points'];
                // 积分赠送
                $this->updatePoint($fromusername, $info);
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
