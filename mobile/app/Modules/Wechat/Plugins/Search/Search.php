<?php

namespace App\Modules\Wechat\Plugins\Search;

use App\Modules\Wechat\Controllers\PluginController;

/**
 * 推荐商品
 *
 * @author wanglu
 *
 */
class Search extends PluginController
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
        $articles = array();
        $sql = "SELECT goods_id, goods_name, goods_img FROM {pre}goods WHERE is_on_sale = 1 AND is_delete = 0 AND goods_name like '%" . $info['user_keywords'] . "%' ORDER BY last_update DESC LIMIT 4";
        // logResult($sql);
        // logResult($info['user_keywords']);
        $data = $GLOBALS['db']->query($sql);

        if (!empty($data)) {
            $articles['type'] = 'news';
            foreach ($data as $key => $val) {
                $articles['content'][$key]['PicUrl'] = get_wechat_image_path($val['goods_img']);
                $articles['content'][$key]['Title'] = $val['goods_name'];
                $articles['content'][$key]['Url'] = __HOST__ . url('goods/index/index', array(
                        'id' => $val['goods_id']
                    ));
            }
        } else {
            $goods = dao('goods')
                ->field('goods_id, goods_name')
                ->where(array('is_best' => 1, 'is_on_sale' => 1, 'is_delete' => 0))
                ->order('RAND()')->find();
            $goods_url = __HOST__ . url('goods/index/index', array('id' => $goods['goods_id']));

            $articles['type'] = 'text';
            $articles['content'] = '没有搜索到相关商品，我们为您推荐：<a href="' . $goods_url . '" >' . $goods['goods_name'] . '</a>';
        }
        // 积分赠送
        $this->updatePoint($fromusername, $info);

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
