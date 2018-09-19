<?php

namespace App\Modules\Wechat\Controllers;

use App\Modules\Base\Controllers\Foundation;

abstract class PluginController extends \App\Modules\Base\Controllers\FrontendController 
{
    protected $_data = array();

    /**
     * 数据显示返回
     */
    abstract protected function returnData($fromusername, $info);

    /**
     * 积分加减
     */
    abstract protected function updatePoint($fromusername, $info);

    /**
     * 行为处理
     */
    abstract protected function executeAction();

    /**
     * 积分赠送处理
     */
    public function do_point($fromusername, $info, $rank_points = 0, $pay_points = 0)
    {
        $time = gmtime();
        $users = get_wechat_user_id($fromusername);
        if ($users) {
            // 积分记录
            $data['user_id'] = $users['user_id'];
            $data['user_money'] = 0;
            $data['frozen_money'] = 0;
            $data['rank_points'] = intval($rank_points);
            $data['pay_points'] = intval($pay_points);
            $data['change_time'] = $time;
            $data['change_desc'] = $info['name'] . '积分赠送';
            $data['change_type'] = ACT_OTHER;

            // 同一时间 同一用户不能重复插入
            $where = array(
                'user_id' => $data['user_id'],
                'change_time' => $data['change_time'],
                'change_type' => ACT_OTHER,
            );
            $account_log_num = dao('account_log')->where($where)->count();
            if ($account_log_num == 0) {
                $ac_log_id = dao('account_log')->data($data)->add();

                // 从表记录
                $data1['log_id'] = $ac_log_id;
                $data1['openid'] = $fromusername;
                $data1['keywords'] = $info['command'];
                $data1['createtime'] = $time;

                $where1 = array(
                    'openid' => $data1['openid'],
                    'keywords' => $data1['keywords'],
                    'createtime' => $data1['createtime'],
                );
                $wechat_point_num = dao('wechat_point')->where($where1)->count();
                if ($wechat_point_num == 0) {
                    $we_log_id = dao('wechat_point')->data($data1)->add();

                    // 增加等级积分
                    $sql = "UPDATE {pre}users SET rank_points = rank_points + " . intval($rank_points) . " WHERE user_id = '" . $users['user_id'] . "' ";
                    $GLOBALS['db']->query($sql);
                    // 增加消费积分
                    $sql = "UPDATE {pre}users SET pay_points = pay_points + " . intval($pay_points) . " WHERE user_id = '" . $users['user_id'] . "' ";
                    $GLOBALS['db']->query($sql);
                }

                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 积分扣除处理
     */
    public function do_takeout_point($fromusername, $info, $point_value)
    {
        $time = gmtime();
        $users = get_wechat_user_id($fromusername);
        if ($users) {
            // 扣除处理
            $usable_points = dao('users')->where(array('user_id' => $users['user_id']))->getField('pay_points');
            // 判断用户消费积分 大于扣除消费积分
            if (intval($usable_points) >= intval($point_value)) {

                // 积分记录
                $data['user_id'] = $users['user_id'];
                $data['user_money'] = 0;
                $data['frozen_money'] = 0;
                $data['rank_points'] = 0;
                $data['pay_points'] = $point_value;
                $data['change_time'] = $time;
                $data['change_desc'] = $info['name'] . '积分扣除';
                $data['change_type'] = ACT_OTHER;

                // 同一时间 同一用户不能重复插入
                $where = array(
                    'user_id' => $data['user_id'],
                    'change_time' => $data['change_time'],
                    'change_type' => ACT_OTHER,
                );

                $account_log_num = dao('account_log')->where($where)->count();
                if ($account_log_num == 0) {

                    $ac_log_id = dao('account_log')->data($data)->add();

                    // 从表记录
                    $data1['log_id'] = $ac_log_id;
                    $data1['openid'] = $fromusername;
                    $data1['keywords'] = $info['command'];
                    $data1['createtime'] = $time;

                    $where1 = array(
                        'openid' => $data1['openid'],
                        'keywords' => $data1['keywords'],
                        'createtime' => $data1['createtime'],
                    );
                    $wechat_point_num = dao('wechat_point')->where($where1)->count();
                    if ($wechat_point_num == 0) {
                        $we_log_id = dao('wechat_point')->data($data1)->add();

                        $sql = "UPDATE {pre}users SET pay_points = pay_points - " . intval($point_value) . " WHERE user_id = '" . $users['user_id'] . "' ";
                        $GLOBALS['db']->query($sql);
                    }

                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * 显示插件模板（后端）
     * @param  string $tpl
     * @param  array $config
     * @return
     */
    public function plugin_display($tpl = '', $config = array())
    {
        L(require(MODULE_PATH . 'Language/' . C('shop.lang') . '/wechat.php'));
        $this->_data['lang'] = array_change_key_case(L());
        $this->_data['config'] = $config;
        $this->assign($this->_data);

        $plugin_path = !empty($config['plugin_path']) ? $config['plugin_path'] : 'Plugins';

        if ($_SESSION['seller_id'] > 0) {
            $tpl = 'app/Modules/' . MODULE_NAME . '/' . $plugin_path . '/' . ucfirst($this->plugin_name) . '/Views_seller/' . $tpl . C('TMPL_TEMPLATE_SUFFIX');
        } else {
            $tpl = 'app/Modules/' . MODULE_NAME . '/' . $plugin_path . '/' . ucfirst($this->plugin_name) . '/Views/' . $tpl . C('TMPL_TEMPLATE_SUFFIX');
        }

        $this->template_content = $this->fetch(ROOT_PATH . $tpl);
        $this->assign('template_content', $this->template_content);
        if ($_SESSION['seller_id'] > 0) {
            $this->display('wechat@seller.layout');
        } else {
            $this->display('wechat@admin.layout');
        }
    }

    /**
     * 显示插件模板（前端）
     * @param  string $tpl
     * @param  array $config
     * @return
     */
    public function show_display($tpl = '', $config = array())
    {
        L(require(MODULE_PATH . 'Language/' . C('shop.lang') . '/wechat.php'));
        $this->_data['lang'] = array_change_key_case(L());
        $this->_data['config'] = $config;
        $this->assign($this->_data);

        $plugin_path = !empty($config['plugin_path']) ? $config['plugin_path'] : 'Plugins';

        if ($config['ru_id'] > 0) {
            $tpl = 'app/Modules/' . MODULE_NAME . '/' . $plugin_path . '/' . ucfirst($this->plugin_name) . '/Views_seller/' . $tpl . C('TMPL_TEMPLATE_SUFFIX');
        } else {
            $tpl = 'app/Modules/' . MODULE_NAME . '/' . $plugin_path . '/' . ucfirst($this->plugin_name) . '/Views/' . $tpl . C('TMPL_TEMPLATE_SUFFIX');
        }
        $this->template_content = $this->fetch(ROOT_PATH . $tpl);
        $this->assign('template_content', $this->template_content);
        $this->display('wechat@show.layout');
    }


    /**
     * 操作成功之后跳转,默认三秒钟跳转
     *
     * @param unknown $msg
     * @param unknown $url
     * @param string $type
     * @param number $waitSecond
     */
    protected function message($msg, $url = null, $type = '1', $ru_id = 0, $waitSecond = 3)
    {
        if ($url == null) {
            $url = 'javascript:history.back();';
        }
        if ($type == '2') {
            $title = L('error_information');
        } else {
            $title = L('prompt_information');
        }
        $data['title'] = $title;
        $data['message'] = $msg;
        $data['type'] = $type;
        $data['url'] = $url;
        $data['second'] = $waitSecond;
        $this->assign('data', $data);
        $tpl = ($ru_id > 0) ? 'admin/seller_message' : 'admin/message';
        $this->display($tpl);
        exit();
    }

    /**
     * 中奖概率计算
     *
     * @param unknown $proArr
     * @return Ambigous <string, unknown>
     */
    public function get_rand($proArr)
    {
        $result = '';
        // 概率数组的总概率精度
        $proSum = array_sum($proArr);
        // 概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset($proArr);
        return $result;
    }

    public function __get($name)
    {
        return isset($this->_data [$name]) ? $this->_data [$name] : null;
    }

    public function __set($name, $value)
    {
        $this->_data [$name] = $value;
    }
}
