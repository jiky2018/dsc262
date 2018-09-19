<?php

namespace App\Modules\Wechat\Market\Redpack;

use App\Modules\Wechat\Controllers\PluginController;
use App\Extensions\QRcode;
use Think\Image;
use App\Extensions\Form;
use App\Extensions\WxHongbao;

/**
 * 现金红包后台模块
 * Class Admin
 * @package App\Modules\Wechat\Market\Redpack
 */
class Admin extends PluginController
{
    protected $marketing_type = ''; // 活动类型
    protected $wechat_id = 0; // 微信通ID
    protected $page_num = 10; // 分页数量

    // 配置
    protected $cfg = array();

    public function __construct($cfg = array())
    {
        parent::__construct();

        $this->cfg = $cfg;
        $this->cfg['plugin_path'] = 'Market';
        $this->plugin_name = $this->marketing_type = $cfg['keywords'];
        $this->wechat_id = $cfg['wechat_id'];
        $this->ru_id = isset($cfg['ru_id']) ? $cfg['ru_id'] : 0;
        $this->page_num = isset($cfg['page_num']) ? $cfg['page_num'] : 10;

        $this->assign('ru_id', $this->ru_id);
    }

    /**
     * 活动列表
     */
    public function marketList()
    {
        $filter['type'] = $this->marketing_type;
        $offset = $this->pageLimit(url('market_list', $filter), $this->page_num);

        $total = dao('wechat_marketing')->where(array('marketing_type' => $this->marketing_type, 'wechat_id' => $this->wechat_id))->count();

        $list = dao('wechat_marketing')->field('id, name, command, starttime, endtime, status')->where(array('marketing_type' => $this->marketing_type, 'wechat_id' => $this->wechat_id))->order('id DESC')->limit($offset)->select();
        if ($list[0]['id']) {
            foreach ($list as $k => $v) {
                $list[$k]['starttime'] = local_date('Y-m-d', $v['starttime']);
                $list[$k]['endtime'] = local_date('Y-m-d', $v['endtime']);
                $config = $this->get_market_config($v['id'], $v['marketing_type']);
                $list[$k]['hb_type'] = $config['hb_type'] == 1 ? L('group_redpack') : L('normal_redpack');
                $status = get_status($v['starttime'], $v['endtime']); // 活动状态 0未开始,1正在进行,2已结束
                if ($status == 0) {
                    $list[$k]['status'] = L('no_start');
                } elseif ($status == 1) {
                    $list[$k]['status'] = L('start');
                } elseif ($status == 2) {
                    $list[$k]['status'] = L('over');
                }
            }
        } else {
            $list = array();
        }

        $this->assign('page', $this->pageShow($total));
        $this->assign('list', $list);
        $this->plugin_display('market_list', $this->cfg);
    }

    /**
     * 活动添加与编辑
     * @return
     */
    public function marketEdit()
    {
        // 提交
        if (IS_POST) {
            $json_result = array('error' => 0, 'msg' => '', 'url' => ''); // 初始化通知信息

            $id = I('post.id', 0 , 'intval');
            $data = I('post.data', '' ,'trim');
            $config = I('post.config', '' ,'trim');

            // act_name 字段必填,并且少于32个字符
            if (empty($data['name']) || strlen($data['name']) >= 32) {
                $json_result = array('error' => 1, 'msg' => '活动名称必填，并且须少于32个字符');
                exit(json_encode($json_result));
            }
            // 红包金额必须在1元~200元之间
            if ($config['base_money'] < 1 || $config['base_money'] > 200) {
                $json_result = array('error' => 1, 'msg' => '红包金额必须在1元~200元之间，请重新填写');
                exit(json_encode($json_result));
            }
            // 红包发放总人数 普通红包固定为1，裂变红包至少为3
            if ($config['hb_type'] == 0 && $config['total_num'] != 1) {
                $json_result = array('error' => 1, 'msg' => '红包发放总人数 普通红包固定为1人, 请重新填写');
                exit(json_encode($json_result));
            }
            if ($config['hb_type'] == 1 && $config['total_num'] < 3) {
                $json_result = array('error' => 1, 'msg' => '红包发放总人数 裂变红包至少为3人, 请重新填写');
                exit(json_encode($json_result));
            }
            // nick_name 字段必填，并且少于16字符
            if (empty($config['nick_name']) || strlen($config['nick_name']) >= 16) {
                $json_result = array('error' => 1, 'msg' => '提供方名称必填，并且须少于16个字符');
                exit(json_encode($json_result));
            }
            // send_name 字段为必填，并且少于32字符
            if (empty($config['send_name']) || strlen($config['send_name']) >= 32) {
                $json_result = array('error' => 1, 'msg' => '红包发送方名称必填，并且须少于32个字符');
                exit(json_encode($json_result));
            }
            $data['wechat_id'] = $this->wechat_id;
            $data['marketing_type'] = I('post.marketing_type');
            $data['starttime'] = local_strtotime($data['starttime']);
            $data['endtime'] = local_strtotime($data['endtime']);

            $data['status'] = get_status($data['starttime'], $data['endtime']); // 活动状态 0未开始,1正在进行,2已结束

            $background_path = I('post.background_path', '' ,'trim');
            // 编辑图片处理
            $background_path = edit_upload_image($background_path);

            // 上传背景图片
            if ($_FILES['background']['name']) {
                // 判断类型
                $type = array('image/jpeg', 'image/png');
                if ($_FILES['background']['type'] && !in_array($_FILES['background']['type'], $type)) {
                    // $this->message(L('not_file_type'), NULL, 2);
                    $json_result = array('error' => 1, 'msg' => L('not_file_type'));
                    exit(json_encode($json_result));
                }
                $result = $this->upload('data/attached/redpack', true);
                if ($result['error'] > 0) {
                    // $this->message($result['message'], NULL, 2);
                    $json_result = array('error' => 1, 'msg' => $result['message']);
                    exit(json_encode($json_result));
                }
            }
            //处理背景图片
            if ($_FILES['background']['name'] && $result['url']) {
                $data['background'] = $result['url'];
            } else {
                $data['background'] = $background_path;
            }

            // 验证
            $form = new Form();
            if (!$form->isEmpty($data['background'], 1)) {
                // $this->message(L('please_upload'), NULL, 2);
                $json_result = array('error' => 1, 'msg' => L('please_upload'));
                exit(json_encode($json_result));
            }
            //配置
            if ($config) {
                // 生成密钥证书文件
                // $appid = $config['appid'];
                // $apiclient_cert = $config['apiclient_cert'];
                // $apiclient_key = $config['apiclient_key'];
                // $rootca = $config['rootca'];
                file_write("index.html", "");
                // file_write(md5($appid) . "_apiclient_cert.pem", $apiclient_cert);
                // file_write(md5($appid) . "_apiclient_key.pem", $apiclient_key);
                // file_write(md5($appid) . "_rootca.pem", $rootca);

                $data['config'] = serialize($config);
            }
            // 不保存默认空图片
            if (strpos($data['background'], 'no_image') !== false) {
                unset($data['background']);
            }
            //更新活动
            if ($id) {
                // 删除原背景图片
                if ($data['background'] && $background_path != $data['background']) {
                    $background_path = strpos($background_path, 'no_image') == false ? $background_path : '';  // 且不删除默认空图片
                    $this->remove($background_path);
                }
                $where = array(
                    'id' => $id,
                    'wechat_id' => $this->wechat_id,
                    'marketing_type' => $data['marketing_type']
                );
                dao('wechat_marketing')->data($data)->where($where)->save();
                // $this->message(L('market_edit') . L('success'), url('index'));
                $json_result = array('error' => 0, 'msg' => L('market_edit') . L('success'), 'url' => url('market_list', array('type' => $data['marketing_type'])));
                exit(json_encode($json_result));
            } else {
                //添加活动
                $data['addtime'] = gmtime();
                $id = dao('wechat_marketing')->data($data)->add();
                // $this->message(L('market_add') . L('success'), url('index'));
                $json_result = array('error' => 0, 'msg' => L('market_add') . L('success'), 'url' => url('market_list', array('type' => $data['marketing_type'])));
                exit(json_encode($json_result));
            }
        }

        // 显示
        $nowtime = gmtime();
        $info = array();
        $market_id = $this->cfg['market_id'];
        if (!empty($market_id)) {
            $info = dao('wechat_marketing')->field('id, name, command, logo, background, starttime, endtime, config, description, support')->where(array('id' => $market_id, 'marketing_type' => $this->marketing_type, 'wechat_id' => $this->wechat_id))->find();
            if ($info) {
                $info['starttime'] = isset($info['starttime']) ? local_date('Y-m-d H:i:s', $info['starttime']) : local_date('Y-m-d H:i:s', $nowtime);
                $info['endtime'] = isset($info['endtime']) ? local_date('Y-m-d H:i:s', $info['endtime']) : local_date('Y-m-d H:i:s', local_strtotime("+1 months", $nowtime));
                $info['config'] = unserialize($info['config']);
                $info['background'] = get_wechat_image_path($info['background']);
            } else {
                $this->message('数据不存在', url('market_list', array('type' => $this->marketing_type)), 2);
            }
        } else {
            // 默认开始与结束时间
            $info['starttime'] = local_date('Y-m-d H:i:s', $nowtime);
            $info['endtime'] = local_date('Y-m-d H:i:s', local_strtotime("+1 months", $nowtime));

            $info['config']['hb_type'] = 0;
            $info['config']['money_extra'] = 0;
            $info['config']['total_num'] = 1;

            // 取得最新ID
            $last_id = dao('wechat_marketing')->where(array('wechat_id' => $this->wechat_id))->order('id desc')->getField('id');
            $market_id = !empty($last_id) ? $last_id + 1 : 1;
        }

        // 微信素材所需活动链接
        $info['url'] = __HOST__ . url('wechat/index/market_show', array('type' => 'redpack', 'function' => 'activity', 'market_id' => $market_id, 'ru_id' => $this->ru_id));

        $this->assign('info', $info);
        $this->plugin_display('market_edit', $this->cfg);
    }

    /**
     * 摇一摇广告记录列表
     * @param market_id 活动ID
     * @param function 访问类型 如 shake
     * @param handler 操作类型 如 编辑
     * @return
     */
    public function marketShake()
    {
        $market_id = $this->cfg['market_id'];

        $function = I('get.function', '', 'trim');
        $handler = I('get.handler', '', 'trim');

        // 添加与编辑广告
        if ($handler && $handler == 'edit') {
            // 提交
            if (IS_POST) {
                $json_result = array('error' => 0, 'msg' => '', 'url' => ''); // 初始化通知信息

                $id = I('post.advertice_id', 0, 'intval');
                $data = I('post.advertice', '' ,'trim');
                $icon_path = I('post.icon_path', '' ,'trim');
                // 验证数据
                $form = new Form();
                if (!$form->isEmpty($data['content'], 1)) {
                    // $this->message(L('advertice_content') . L('empty'), NULL, 2);
                    $json_result = array('error' => 1, 'msg' => L('advertice_content'));
                    exit(json_encode($json_result));
                }
                // 验证url格式
                if (substr($data['url'], 0, 4) !== 'http') {
                    // $this->message(L('link_err'), NULL, 2);
                    $json_result = array('error' => 1, 'msg' => L('link_err'));
                    exit(json_encode($json_result));
                }

                $icon_path = edit_upload_image($icon_path);
                // 上传图片处理
                $file = $_FILES['icon'];
                if ($file['name']) {
                    $type = array('image/jpeg', 'image/png');
                    if (!in_array($file['type'], $type)) {
                        // $this->message(L('not_file_type'), NULL, 2);
                        $json_result = array('error' => 1, 'msg' => L('not_file_type'));
                        exit(json_encode($json_result));
                    }
                    $result = $this->upload('data/attached/redpack', true);
                    if ($result['error'] > 0) {
                        // $this->message($result['message'], NULL, 2);
                        $json_result = array('error' => 1, 'msg' => $result['message']);
                        exit(json_encode($json_result));
                    }
                    $data['icon'] = $result['url'];
                    $data['file_name'] = $file['name'];
                    $data['size'] = $file['size'];
                } else {
                    $data['icon'] = $icon_path;
                }

                if (!$form->isEmpty($data['icon'], 1)) {
                    // $this->message(L('please_upload'), NULL, 2);
                    $json_result = array('error' => 1, 'msg' => L('please_upload'));
                    exit(json_encode($json_result));
                }
                // 不保存默认空图片
                if (strpos($data['icon'], 'no_image') !== false) {
                    unset($data['icon']);
                }
                // 更新
                if ($id) {
                    // 删除原图片
                    if ($data['icon'] && $icon_path != $data['icon']) {
                        $icon_path = strpos($icon_path, 'no_image') == false ? $icon_path : '';  // 不删除默认空图片
                        $this->remove($icon_path);
                    }
                    $where = array('id' => $id, 'wechat_id' => $this->wechat_id);
                    dao('wechat_redpack_advertice')->data($data)->where($where)->save();
                    // $this->message(L('wechat_editor') . L('success'), url('shake', array('market_id' => $data['market_id'])));
                    $json_result = array('error' => 0, 'msg' => L('wechat_editor') . L('success'));
                    exit(json_encode($json_result));
                } else {
                    $data['wechat_id'] = $this->wechat_id;
                    dao('wechat_redpack_advertice')->data($data)->add();
                    // $this->message(L('add') . L('success'), url('shake', array('market_id' => $data['market_id'])));
                    $json_result = array('error' => 0, 'msg' => L('add') . L('success'));
                    exit(json_encode($json_result));
                }
            }
            // 显示单个广告信息
            $advertices_id = I('get.advertice_id', 0, 'intval');
            if ($advertices_id) {
                $condition = array(
                    'id' => $advertices_id,
                    'wechat_id' => $this->wechat_id
                );
                $info = dao('wechat_redpack_advertice')->where($condition)->find();
                if (empty($info)) {
                    $this->message('数据不存在', url('data_list', array('type' => $this->marketing_type, 'function' => $function, 'id' => $market_id)), 2);
                }
                $info['icon'] = get_wechat_image_path($info['icon']);
            }
            $where = array(
                'id' => $market_id,
                'wechat_id' => $this->wechat_id,
                'marketing_type' => $this->marketing_type,
            );
            $info['act_name'] = dao('wechat_marketing')->where($where)->getField('name');
            $this->assign('act_name', $info['act_name']);

            $this->assign('info', $info);
            $this->plugin_display('market_shake_edit', $this->cfg);
        } else {
            // 广告列表显示
            // 分页
            $filter['type'] = $this->marketing_type;
            $filter['function'] = $function;
            $filter['id'] = $market_id;
            $offset = $this->pageLimit(url('data_list', $filter), $this->page_num);

            $condition = array(
                'market_id' => $market_id,
                'wechat_id' => $this->wechat_id
            );
            $total = dao('wechat_redpack_advertice')->where($condition)->count();
            // $page = $this->pageShow($total);
            $this->assign('page', $this->pageShow($total));

            $list = dao('wechat_redpack_advertice')->where($condition)->order('id desc')->limit($offset)->select();
            if ($list) {
                foreach ($list as $key => $value) {
                    $list[$key]['icon'] = get_wechat_image_path($value['icon']);
                }
            }

            // 当前活动名称
            $where = array(
                'id' => $market_id,
                'wechat_id' => $this->wechat_id,
                'marketing_type' => $this->marketing_type
            );
            $act_name = dao('wechat_marketing')->where($where)->getField('name');
            $this->assign('act_name', $act_name);

            $this->assign('list', $list);
            $this->plugin_display('market_shake', $this->cfg);
        }
    }

    /**
     * 活动记录
     * @return
     */
    public function marketLogList()
    {
        $market_id = $this->cfg['market_id'];

        $function = I('get.function', '', 'trim');
        $handler = I('get.handler', '', 'trim');

        if ($handler && $handler == 'info') {
            // 显示单条记录
            $log_id = I('get.log_id', 0, 'intval');
            if ($log_id) {
                $condition = array(
                    'id' => $log_id,
                    'wechat_id' => $this->wechat_id
                );
                $info = dao('wechat_redpack_log')->where($condition)->find();

                $info['nickname'] = dao('wechat_user')->where(array('wechat_id' => $this->wechat_id, 'openid' => $info['openid']))->getField('nickname');
                $info['hb_type'] = $info['hb_type'] == 1 ? '裂变红包' : '普通红包';
                $info['time'] = !empty($info['time']) ? local_date('Y-m-d H:i:s', $info['time']) : '';
                $info['hassub'] = $info['hassub'] == 1 ? '已领取' : '未领取';
                // 接口查询更多详情
                if ($info['hassub'] == 1) {
                    $condition = array(
                        'id' => $market_id,
                        'wechat_id' => $this->wechat_id,
                    );
                    $data = dao('wechat_marketing')->field('config')->where($condition)->find();
                    $config = unserialize($data['config']);
                    // dd($config);
                    $configure = array(
                        'appid' => $info['wxappid'],
                        'partnerkey' => $config['partnerkey'],
                    );
                    $WxHongbao = new WxHongbao($configure);
                    // 请求参数
                    $WxHongbao->setParameter("nonce_str", $WxHongbao->create_noncestr()); // 随机字符串，不长于32位
                    $WxHongbao->setParameter("mch_billno", $info['mch_billno']); // 商户发放红包的商户订单号
                    $WxHongbao->setParameter("mch_id", $info['mch_id']); // 微信支付分配的商户号
                    $WxHongbao->setParameter("appid", $info['wxappid']); // 公众账号appid
                    $WxHongbao->setParameter("bill_type", "MCHT"); //订单类型 MCHT:通过商户订单号获取红包信息。

                    $responseObj = $WxHongbao->query_redpack();
                    // logResult($responseObj);
                    $return_code = $responseObj->return_code;
                    $result_code = $responseObj->result_code;

                    if ($return_code == 'SUCCESS') {
                        if ($result_code == 'SUCCESS') {
                            // 显示返回的信息
                            $info['status'] = $responseObj->status; // 红包状态
                            $info['total_num'] = $responseObj->total_num; // 红包个数
                            $info['hb_type'] = $responseObj->hb_type; // 红包类型
                            $info['openid'] = $responseObj->openid; // 领取红包的Openid
                            $info['send_time'] = $responseObj->send_time; // 发送时间
                            $info['rcv_time'] = $responseObj->rcv_time;// 接收时间
                        } else {
                            // return $responseObj->return_msg;
                            // exit(json_encode(array('status' => 0, 'msg' => $responseObj->return_msg)));
                        }
                    } else {
                        // return $responseObj->return_msg;
                        // exit(json_encode(array('status' => 0, 'msg' => $responseObj->return_msg)));
                    }
                }
            }
            // dd($info);
            $this->assign('info', $info);
            $this->plugin_display('market_log_info', $this->cfg);
        } else {
            // 记录列表
            // 分页
            $filter['type'] = $this->marketing_type;
            $filter['function'] = $function;
            $filter['id'] = $market_id;
            $offset = $this->pageLimit(url('data_list', $filter), $this->page_num);
            $where = array(
                'wechat_id' => $this->wechat_id,
                'market_id' => $market_id
            );
            $total = dao('wechat_redpack_log')->where($where)->count();
            $list = dao('wechat_redpack_log')->where($where)->order('id desc')->limit($offset)->select();

            foreach ($list as $key => $value) {
                $list[$key]['nickname'] = dao('wechat_user')->where(array('wechat_id' => $this->wechat_id, 'openid' => $value['openid']))->getField('nickname');
                $list[$key]['time'] = !empty($value['time']) ? local_date('Y-m-d H:i:s', $value['time']) : '';
            }
            $this->assign('page', $this->pageShow($total));
            $this->assign('market_id', $market_id);
            $this->assign('redpacks', $list);

            $this->plugin_display('market_log_list', $this->cfg);
        }
    }

    /**
     * 设置分享 功能待定
     * @return
     */
    public function marketShare_setting()
    {
        $this->plugin_display('market_share_setting', $this->cfg);
    }


    /**
     * 活动二维码
     * @return
     */
    public function marketQrcode()
    {
        $market_id = I('get.id', 0, 'intval');

        if (!empty($market_id)) {
            $url = __HOST__ . url('wechat/index/market_show', array('type' => 'redpack', 'function' => 'activity', 'market_id' => $market_id, 'ru_id' => $this->ru_id));

            $info = dao('wechat_marketing')->field('qrcode')->where(array('id' => $market_id, 'marketing_type' => $this->marketing_type, 'wechat_id' => $this->wechat_id))->find();

            // 生成二维码
            // 纠错级别：L、M、Q、H
            $errorCorrectionLevel = 'M';
            // 点的大小：1到10
            $matrixPointSize = 7;
            // 生成的文件位置
            $path = dirname(ROOT_PATH) . '/data/attached/redpack/';
            // 水印logo
            $water_logo = ROOT_PATH . 'public/img/shop_app_icon.png';
            $water_logo_out = $path . 'water_logo' . $market_id . '.png';

            // 输出二维码路径
            $filename = $path . $errorCorrectionLevel . $matrixPointSize . $market_id . '.png';

            if (!is_dir($path)) {
                @mkdir($path);
            }
            QRcode::png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);

            // 添加水印
            $img = new Image();
            // 生成水印缩略图
            $img->open($water_logo)->thumb(80, 80)->save($water_logo_out);
            // 生成原图+水印
            $img->open($filename)->water($water_logo_out, 5, 100)->save($filename);

            $qrcode_url = __HOST__ . __STATIC__ . '/data/attached/redpack/' . basename($filename) . '?t=' . time();
            $this->cfg['qrcode_url'] = $qrcode_url;
        }

        $this->plugin_display('market_qrcode', $this->cfg);
    }

    /**
     * 将反序列化后的配置信息转换成数组格式
     * @param  [int] $id
     * @param  [string] $marketing_type
     * @return [array] array
     */
    public function get_market_config($id, $marketing_type)
    {
        $info = dao('wechat_marketing')->field('config')->where(array('id' => $id, 'marketing_type' => $this->marketing_type, 'wechat_id' => $this->wechat_id))->find();
        $result = unserialize($info['config']);
        return $result;
    }

    /**
     * 行为操作
     * @param handler 例如 删除
     */
    public function executeAction()
    {
        if (IS_AJAX) {
            $json_result = array('error' => 0, 'msg' => '', 'url' => '');

            $handler = I('get.handler', '', 'trim');
            $market_id = I('get.market_id', 0, 'intval');

            // 删除日志记录
            if ($handler && $handler == 'log_delete') {
                $log_id = I('get.log_id', 0, 'intval');
                if (!empty($log_id)) {
                    dao('wechat_redpack_log')->where(array('id' => $log_id, 'wechat_id' => $this->wechat_id, 'market_id' => $market_id))->delete();
                    $json_result['msg'] = '删除成功！';
                    exit(json_encode($json_result));
                } else {
                    $json_result['msg'] = '删除失败！';
                    exit(json_encode($json_result));
                }
            }
        }
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

}