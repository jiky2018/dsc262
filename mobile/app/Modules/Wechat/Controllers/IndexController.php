<?php

namespace App\Modules\Wechat\Controllers;

use App\Modules\Base\Controllers\Frontend;
use App\Extensions\Wechat;

class IndexController extends \App\Modules\Base\Controllers\FrontendController 
{
    private $weObj = '';
    private $secret_key = '';
    private $wechat_id = 0;
    private $ru_id = 0;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        // 获取公众号配置
        $this->secret_key = I('get.key', '', 'trim');
        if ($this->secret_key) {
            $this->load_helper('passport');
            $wxinfo = $this->get_config($this->secret_key);
            $this->wechat_id = $wxinfo['id'];
            $this->ru_id = $wxinfo['ru_id'];
            if ($this->ru_id) {
                cookie('ectouch_ru_id', $this->ru_id, gmtime() + 3600 * 24);
            } else {
                cookie('ectouch_ru_id', null);
            }
            $config['token'] = $wxinfo['token'];
            $config['appid'] = $wxinfo['appid'];
            $config['appsecret'] = $wxinfo['appsecret'];
            $config['encodingaeskey'] = $wxinfo['encodingaeskey'];
            // $config['debug'] = APP_DEBUG;
            $this->weObj = new Wechat($config);
            $this->weObj->valid();
        }
    }

    /**
     * 执行方法
     */
    public function actionIndex()
    {
        // 事件类型
        $type = $this->weObj->getRev()->getRevType();
        $wedata = $this->weObj->getRev()->getRevData();
        $keywords = '';
        // 微信消息日志队列之存入数据库
        $event_data = array('subscribe', 'unsubscribe', 'LOCATION', 'VIEW', 'SCAN');
        if (!in_array($wedata['Event'], $event_data)) {
            if (!empty($wedata['EventKey']) || !empty($wedata['Content'])) {
                message_log_alignment_add($wedata, $this->wechat_id);
            }
        }
        // 兼容更新用户关注状态（未配置微信通之前关注的粉丝）
        update_wechatuser_subscribe($wedata['FromUserName'], $this->wechat_id, $this->weObj);

        // 接收消息
        switch ($type) {
            // 文本消息
            case Wechat::MSGTYPE_TEXT:
                $keywords = $wedata['Content'];
                break;
            // 事件推送
            case Wechat::MSGTYPE_EVENT:
                // 关注事件
                if ($wedata['Event'] == Wechat::EVENT_SUBSCRIBE) {
                    $scene_id = '';
                    $flag = false;
                    // 用户扫描带参数二维码(未关注)
                    if (isset($wedata['Ticket']) && !empty($wedata['Ticket'])) {
                        $scene_id = $this->weObj->getRevSceneId();
                        $flag = true;
                        // 关注
                        $this->subscribe($wedata['FromUserName'], $scene_id);
                    } else {
                        // 关注
                        $this->subscribe($wedata['FromUserName']);
                    }
                    // 关注自动回复信息
                    $this->msg_reply('subscribe');
                } elseif ($wedata['Event'] == Wechat::EVENT_UNSUBSCRIBE) {
                    // 取消关注事件
                    $this->unsubscribe($wedata['FromUserName']);
                    exit();
                } elseif ($wedata['Event'] == Wechat::EVENT_SCAN) {
                    // 扫描带参数二维码(用户已关注)
                    $scene_id = $this->weObj->getRevSceneId();
                } elseif ($wedata['Event'] == Wechat::EVENT_MENU_CLICK) {
                    // 自定义菜单事件 点击菜单拉取消息
                    $keywords = $wedata['EventKey'];
                } elseif ($wedata['Event'] == Wechat::EVENT_MENU_VIEW) {
                    // 自定义菜单事件 点击菜单跳转链接
                    redirect($wedata['EventKey']);
                } elseif ($wedata['Event'] == Wechat::EVENT_LOCATION) {
                    // 上报地理位置事件
                    exit();
                } elseif ($wedata['Event'] == 'kf_create_session') {
                    // 多客服接入
                } elseif ($wedata['Event'] == 'kf_close_session') {
                    // 多客服关闭
                } elseif ($wedata['Event'] == 'kf_switch_session') {
                    // 多客服转接
                } elseif ($wedata['Event'] == 'MASSSENDJOBFINISH') {
                    // 更新群发消息结果
                    $data['status'] = $wedata['Status'];
                    $data['totalcount'] = $wedata['TotalCount'];
                    $data['filtercount'] = $wedata['FilterCount'];
                    $data['sentcount'] = $wedata['SentCount'];
                    $data['errorcount'] = $wedata['ErrorCount'];
                    // 更新群发结果
                    dao('wechat_mass_history')->data($data)->where(array('msg_id' => $wedata['MsgID'], 'wechat_id' => $this->wechat_id))->save();
                    exit();
                } elseif ($wedata['Event'] == 'TEMPLATESENDJOBFINISH') {
                    // 模板消息发送结束事件
                    if ($wedata['Status'] == 'success') {
                        // 推送成功
                        $data = array('status' => 1);
                    } elseif ($wedata['Status'] == 'failed:user block') {
                        // 用户拒收
                        $data = array('status' => 2);
                    } else {
                        // 发送失败
                        $data = array('status' => 0); // status 0 发送失败，1 发送与接收成功，2 用户拒收
                    }
                    // 更新模板消息发送状态
                    dao('wechat_template_log')->data($data)->where(array('msgid' => $wedata['MsgID'], 'openid' => $wedata['FromUserName'], 'wechat_id' => $this->wechat_id))->save();
                    exit();
                }
                break;
            // 图片消息
            case Wechat::MSGTYPE_IMAGE:
                exit();
                break;
            // 语音消息
            case Wechat::MSGTYPE_VOICE:
                exit();
                break;
            // 视频消息
            case Wechat::MSGTYPE_VIDEO:
                exit();
                break;
            // 小视频消息
            case Wechat::MSGTYPE_SHORTVIDEO:
                exit();
                break;
            // 地理位置消息
            case Wechat::MSGTYPE_LOCATION:
                exit();
                break;
            // 链接消息
            case Wechat::MSGTYPE_LINK:
                exit();
                break;
            default:
                $this->msg_reply('msg'); // 消息自动回复
                exit();
        }

        // 扫描二维码
        if (!empty($scene_id)) {
            $keywords = $this->do_qrcode_subscribe($scene_id, $flag);
            if (!empty($keywords)) {
                // 功能插件
                $rs1 = $this->get_function($wedata['FromUserName'], $keywords);
                // 微信营销
                $rs3 = $this->get_marketing($wedata['FromUserName'], $keywords);
                if (empty($rs1) || empty($rs3)) {
                    // 关键词回复
                    $rs2 = $this->keywords_reply($keywords);
                    if (empty($rs2)) {
                        // 消息自动回复
                        $this->msg_reply('msg');
                    }
                }
            }
        }

        // 回复消息
        // 查询发送状态
        if ($wedata['MsgType'] == 'event') {
            $where = array(
                'wechat_id' => $this->wechat_id,
                'fromusername' => $wedata['FromUserName'],
                'createtime' => $wedata['CreateTime'],
                'keywords' => $keywords,
                'is_send' => 0
            );
        } else {
            $where = array(
                'wechat_id' => $this->wechat_id,
                'msgid' => $wedata['MsgId'],
                'keywords' => $keywords,
                'is_send' => 0
            );
        }
        $contents = dao('wechat_message_log')->field('fromusername, createtime, keywords, msgid, msgtype')->where($where)->find();
        if (!empty($contents) && !empty($contents['keywords'])) {
            $keyword = html_in($contents['keywords']);
            $fromusername = $contents['fromusername'];
            // 多客服
            $rs = $this->customer_service($fromusername, $keyword);
            if (empty($rs)) {
                // 功能插件
                $rs1 = $this->get_function($fromusername, $keyword);
                // 微信营销
                $rs3 = $this->get_marketing($fromusername, $keyword);
                if (empty($rs1) || empty($rs3)) {
                    // 关键词回复
                    $rs2 = $this->keywords_reply($keyword);
                    if (empty($rs2)) {
                        // 消息自动回复
                        $this->msg_reply('msg');
                    }
                }
            }
            // 记录用户操作信息
            $this->record_msg($fromusername, $keyword);
            // 微信消息日志队列之处理发送状态
            message_log_alignment_send($contents, $this->wechat_id);
        }
    }

    /**
     * 关注处理
     *
     * @param array $info
     */
    private function subscribe($openid = '', $scene_id = '')
    {
        if (!empty($openid)) {
            // 获取微信用户信息
            $info = $this->weObj->getUserInfo($openid);
            if (empty($info)) {
                $this->weObj->resetAuth();
                exit('null');
            }

            // 组合数据
            $data['wechat_id'] = $this->wechat_id;
            $data['subscribe'] = $info['subscribe'];
            $data['openid'] = $info['openid'];
            $data['nickname'] = $info['nickname'];
            $data['sex'] = $info['sex'];
            $data['language'] = $info['language'];
            $data['city'] = $info['city'];
            $data['province'] = $info['province'];
            $data['country'] = $info['country'];
            $data['headimgurl'] = $info['headimgurl'];
            $data['subscribe_time'] = $info['subscribe_time'];
            $data['remark'] = $info['remark'];
            $data['groupid'] = isset($info['groupid']) ? $info['groupid'] : $this->weObj->getUserGroup($openid);
            $data['unionid'] = isset($info['unionid']) ? $info['unionid'] : '';

            // 公众号启用微信开发者平台，平台检查unionid, 商家不检查unionid
            if ($this->ru_id == 0 && empty($data['unionid'])) {
                exit('null');
            }
            // 已关注用户基本信息
            if ($this->ru_id == 0) {
                update_wechat_unionid($info, $this->wechat_id); //兼容更新平台粉丝unionid
            }
            $condition = array('unionid' => $data['unionid'], 'wechat_id' => $this->wechat_id);
            $result = dao('wechat_user')->field('ect_uid, openid, unionid')->where($condition)->find();
            // 查找用户是否存在
            if (isset($result)) {
                $users = dao('users')->where(array('user_id' => $result['ect_uid']))->find();
                if (empty($users) || empty($result['ect_uid'])) {
                    dao('wechat_user')->where($condition)->delete();
                    $result = array();
                    unset($_SESSION['user_id']);
                }
            }

            // 未关注
            if (empty($result)) {
                // 兼容老用户
                $old_users = dao('users')->field('user_id, parent_id')->where(array('aite_id' => 'wechat_' . $data['unionid']))->find();
                if (!empty($old_users)) {
                    // 清空aite_id
                    dao('users')->data(array('aite_id' => ''))->where(array('user_id' => $older_user['user_id']))->save();
                    // 同步社会化登录用户信息表
                    $res = array(
                        'unionid' => $data['unionid'],
                        'user_id' => $old_users['user_id']
                    );
                    update_connnect_user($res, 'wechat');
                }
                // 其他平台(PC,APP)是否注册
                $userinfo = get_connect_user($data['unionid']);
                // 商家不走注册
                if (empty($userinfo) && $this->ru_id == 0) {
                    // 设置的用户注册信息
                    $username = get_wechat_username($data['unionid'], 'wechat');
                    $password = mt_rand(100000, 999999);
                    $email = $username . '@qq.com';
                    // 用户注册
                    $extend = array(
                        'nick_name' => $data['nickname'],
                        'sex' => $data['sex'],
                        'user_picture' => $data['headimgurl']
                    );
                    // 查询推荐人ID
                    $scenes = return_is_drp($scene_id);
                    if ($scenes['is_drp'] == true) {
                        $extend['drp_parent_id'] = $scenes['drp_parent_id'];
                    } else {
                        $extend['parent_id'] = $scenes['parent_id'];
                    }
                    if (register($username, $password, $email, $extend) !== false) {
                        // 更新社会化登录用户信息
                        $res = array(
                            'unionid' => $data['unionid'],
                            'user_id' => $_SESSION['user_id'],
                            'nickname' => $data['nickname'],
                            'sex' => $data['sex'],
                            'province' => $data['province'],
                            'city' => $data['city'],
                            'country' => $data['country'],
                            'headimgurl' => $data['headimgurl'],
                        );
                        update_connnect_user($res, 'wechat');
                        // 首次注册 更新推荐二维码扫描量
                        if ($is_drp == false && !empty($parent_id)) {
                            $qrcode = dao('wechat_qrcode')->field('username')->where(array('scene_id' => $parent_id, 'wechat_id' => $this->wechat_id))->find();
                            if (!empty($qrcode['username'])) {
                                dao('wechat_qrcode')->where(array('scene_id' => $parent_id, 'wechat_id' => $this->wechat_id))->setInc('scan_num', 1);
                            }
                        }
                        // 注册微信资料
                        $data['ect_uid'] = $_SESSION['user_id'];
                        $data['from'] = 0; // 微信粉丝来源 0 关注公众号
                    } else {
                        exit('null');
                    }
                }

                // 新增微信粉丝
                dao('wechat_user')->data($data)->add();
                if ($this->ru_id == 0) {
                    // 新用户送红包
                    $data1['user_id'] = $_SESSION['user_id'];
                    $bonus_num = dao('user_bonus')->where($data1)->count();
                    if ($bonus_num <= 0) {
                        $content = $this->send_message($openid, 'bonus', $this->weObj, 1);
                        $bonus_msg = empty($content) ? '' : $content['content'];
                        if (!empty($bonus_msg)) {
                            // 微信端发送消息
                            $msg = array(
                                'touser' => $openid,
                                'msgtype' => 'text',
                                'text' => array(
                                    'content' => $bonus_msg
                                )
                            );
                            $this->weObj->sendCustomMessage($msg);
                        }
                    }
                }
            } else {
                $template = $data['nickname'] . '，欢迎您再次回来';
                // 微信端发送消息
                $this->send_custom_message($openid, 'text', $template);
                // 更新微信用户资料
                dao('wechat_user')->data($data)->where($condition)->save();
            }

            // 检测是否有模板消息待发送
            check_template_log($data['openid'], $this->wechat_id, $this->weObj);
        }
    }

    /**
     * 取消关注
     *
     * @param string $openid
     */
    public function unsubscribe($openid = '')
    {
        // 未关注
        $where['openid'] = $openid;
        $where['wechat_id'] = $this->wechat_id;
        $rs = dao('wechat_user')->where($where)->count();
        // 修改关注状态
        if ($rs > 0) {
            $data['subscribe'] = 0;
            dao('wechat_user')->data($data)->where($where)->save();

            // 同步用户标签 (取消关注 微信端标签也删除了)
            dao('wechat_user_tag')->where($where)->delete();
        }
    }

    // 关注二维码处理
    private function do_qrcode_subscribe($scene_id)
    {
        // 推荐uid
        if (strpos($scene_id, 'u') == 0) {
            $scene_id = str_replace('u=', '', $scene_id);
        }
        $scene_id = intval($scene_id);
        $qrcode = dao('wechat_qrcode')->field('function, username')->where(array('scene_id' => $scene_id, 'wechat_id' => $this->wechat_id))->find();
        // 增加渠道二维码的扫描量
        if (empty($qrcode['username'])) {
            dao('wechat_qrcode')->where(array('scene_id' => $scene_id, 'wechat_id' => $this->wechat_id))->setInc('scan_num', 1);
        }
        return $qrcode['function'];
    }

    /**
     * 被动关注，消息回复
     *
     * @param string $type
     * @param string $return
     */
    private function msg_reply($type, $return = 0)
    {
        $replyInfo = $this->db->table('wechat_reply')
            ->field('content, media_id')
            ->where(array('type' => $type, 'wechat_id' => $this->wechat_id))
            ->find();
        if (!empty($replyInfo)) {
            if (!empty($replyInfo['media_id'])) {
                $replyInfo['media'] = $this->db->table('wechat_media')
                    ->field('title, command, content, file, type, file_name')
                    ->where(array('id' => $replyInfo['media_id']))
                    ->find();
                if ($replyInfo['media']['type'] == 'news') {
                    $replyInfo['media']['type'] = 'image';
                }
                // 上传多媒体文件
                $filename = !empty($replyInfo['media']['command']) ? dirname(ROOT_PATH) . '/mobile/' . $replyInfo['media']['file'] : dirname(ROOT_PATH) . '/' . $replyInfo['media']['file'];
                $rs = $this->weObj->uploadMedia(array('media' => realpath_wechat($filename)), $replyInfo['media']['type']);
                if (empty($rs)) {
                    logResult($this->weObj->errMsg);
                }
                // 回复数据重组
                if ($rs['type'] == 'image' || $rs['type'] == 'voice') {
                    $replyData = array(
                        'ToUserName' => $this->weObj->getRev()->getRevFrom(),
                        'FromUserName' => $this->weObj->getRev()->getRevTo(),
                        'CreateTime' => time(),
                        'MsgType' => $rs['type'],
                        ucfirst($rs['type']) => array('MediaId' => $rs['media_id'])
                    );
                } elseif ('video' == $rs['type']) {
                    $replyData = array(
                        'ToUserName' => $this->weObj->getRev()->getRevFrom(),
                        'FromUserName' => $this->weObj->getRev()->getRevTo(),
                        'CreateTime' => time(),
                        'MsgType' => $rs['type'],
                        ucfirst($rs['type']) => array(
                            'MediaId' => $rs['media_id'],
                            'Title' => $replyInfo['media']['title'],
                            'Description' => strip_tags($replyInfo['media']['content'])
                        )
                    );
                }
                if ($return) {
                    return array('type' => 'media', 'content' => $replyData);
                }
                $this->weObj->reply($replyData);
                //记录用户操作信息
                $this->record_msg($this->weObj->getRev()->getRevTo(), '图文信息', 1);
            } else {
                // 文本回复
                $replyInfo['content'] = html_out($replyInfo['content']);
                if ($return) {
                    return array('type' => 'text', 'content' => $replyInfo['content']);
                }
                $this->weObj->text($replyInfo['content'])->reply();
                //记录用户操作信息
                $this->record_msg($this->weObj->getRev()->getRevTo(), $replyInfo['content'], 1);
            }
        }
    }

    /**
     * 关键词回复
     *
     * @param string $keywords
     * @return boolean
     */
    private function keywords_reply($keywords)
    {
        $endrs = false;
        $sql = 'SELECT r.content, r.media_id, r.reply_type FROM {pre}wechat_reply r LEFT JOIN {pre}wechat_rule_keywords k ON r.id = k.rid WHERE k.rule_keywords = "' . $keywords . '" and r.wechat_id = ' . $this->wechat_id . ' order by r.add_time desc LIMIT 1';
        $result = $this->db->query($sql);
        if (!empty($result)) {
            // 素材回复
            if (!empty($result[0]['media_id'])) {
                $mediaInfo = $this->db->table('wechat_media')
                    ->field('id, title, command, digest, content, file, type, file_name, article_id, link')
                    ->where(array('id' => $result[0]['media_id']))
                    ->find();
                // 回复数据重组
                if ($result[0]['reply_type'] == 'image' || $result[0]['reply_type'] == 'voice') {
                    // 上传多媒体文件
                    $filename = !empty($mediaInfo['command']) ? dirname(ROOT_PATH) . '/mobile/' . $mediaInfo['file'] : dirname(ROOT_PATH) . '/' . $mediaInfo['file'];
                    $rs = $this->weObj->uploadMedia(array('media' => realpath_wechat($filename)), $result[0]['reply_type']);
                    if (empty($rs)) {
                        logResult($this->weObj->errMsg);
                    }
                    $replyData = array(
                        'ToUserName' => $this->weObj->getRev()->getRevFrom(),
                        'FromUserName' => $this->weObj->getRev()->getRevTo(),
                        'CreateTime' => time(),
                        'MsgType' => $rs['type'],
                        ucfirst($rs['type']) => array('MediaId' => $rs['media_id'])
                    );
                    // 回复
                    $this->weObj->reply($replyData);
                    //记录用户操作信息
                    $record_cotent = $result[0]['reply_type'] == 'voice' ? '语音' : '图片';
                    $this->record_msg($this->weObj->getRev()->getRevTo(), $record_cotent, 1);
                    $endrs = true;
                } elseif ('video' == $result[0]['reply_type']) {
                    // 上传多媒体文件
                    $filename = !empty($mediaInfo['command']) ? dirname(ROOT_PATH) . '/mobile/' . $mediaInfo['file'] : dirname(ROOT_PATH) . '/' . $mediaInfo['file'];
                    $rs = $this->weObj->uploadMedia(array('media' => realpath_wechat($filename)), $result[0]['reply_type']);
                    if (empty($rs)) {
                        logResult($this->weObj->errMsg);
                    }
                    $replyData = array(
                        'ToUserName' => $this->weObj->getRev()->getRevFrom(),
                        'FromUserName' => $this->weObj->getRev()->getRevTo(),
                        'CreateTime' => time(),
                        'MsgType' => $rs['type'],
                        ucfirst($rs['type']) => array(
                            'MediaId' => $rs['media_id'],
                            'Title' => $mediaInfo['title'],
                            'Description' => strip_tags($mediaInfo['content'])
                        )
                    );
                    // 回复
                    $this->weObj->reply($replyData);
                    //记录用户操作信息
                    $this->record_msg($this->weObj->getRev()->getRevTo(), '视频', 1);
                    $endrs = true;
                } elseif ('news' == $result[0]['reply_type']) {
                    // 图文素材
                    $articles = array();
                    if (!empty($mediaInfo['article_id'])) {
                        $artids = explode(',', $mediaInfo['article_id']);
                        foreach ($artids as $key => $val) {
                            $artinfo = $this->db->table('wechat_media')
                                ->field('id, title, file, digest, content, link')
                                ->where(array('id' => $val))
                                ->find();
                            $artinfo['content'] = sub_str(strip_tags(html_out($artinfo['content'])), 100);
                            $articles[$key]['Title'] = $artinfo['title'];
                            $articles[$key]['Description'] = empty($artinfo['digest']) ? $artinfo['content'] : $artinfo['digest'];
                            $articles[$key]['PicUrl'] = get_wechat_image_path($artinfo['file']);
                            $articles[$key]['Url'] = empty($artinfo['link']) ? __HOST__ . url('article/index/wechat', array('id' => $artinfo['id'])) : strip_tags(html_out($artinfo['link']));
                        }
                    } else {
                        $articles[0]['Title'] = $mediaInfo['title'];
                        $articles[0]['Description'] = empty($mediaInfo['digest']) ? sub_str(strip_tags(html_out($mediaInfo['content'])), 100) : $mediaInfo['digest'];
                        $articles[0]['PicUrl'] = get_wechat_image_path($mediaInfo['file']);
                        $articles[0]['Url'] = empty($mediaInfo['link']) ? __HOST__ . url('article/index/wechat', array('id' => $mediaInfo['id'])) : strip_tags(html_out($mediaInfo['link']));
                    }
                    // 回复
                    $this->weObj->news($articles)->reply();
                    //记录用户操作信息
                    $this->record_msg($this->weObj->getRev()->getRevTo(), '图文信息', 1);
                    $endrs = true;
                }
            } else {
                // 文本回复
                $result[0]['content'] = html_out($result[0]['content']);
                $this->weObj->text($result[0]['content'])->reply();
                //记录用户操作信息
                $this->record_msg($this->weObj->getRev()->getRevTo(), $result[0]['content'], 1);
                $endrs = true;
            }
        }
        return $endrs;
    }

    /**
     * 功能变量查询
     *
     * @param unknown $tousername
     * @param unknown $fromusername
     * @param unknown $keywords
     * @return boolean
     */
    public function get_function($fromusername, $keywords)
    {
        $return = false;
        $rs = dao('wechat_extend')
            ->field('name, keywords, command, config')
            ->where('(keywords like "%' . $keywords . '%" or command like "%' . $keywords . '%") and enable = 1 and wechat_id = ' . $this->wechat_id)
            ->order('id asc')
            ->select();
        if (empty($rs)) {
            $rs = $this->db->query("SELECT name, keywords, command, config FROM {pre}wechat_extend WHERE command = 'search' and enable = 1 and wechat_id = '" . $this->wechat_id . "' ");
        }
        $info = reset($rs);
        $info['user_keywords'] = $keywords;
        /*if($rs){
            $key = explode(',', $rs['keywords']);
            if(!in_array($keywords, $key)){
                return $return;
            }
        }*/
        $file = MODULE_PATH . 'Plugins/' . ucfirst($info['command']) . '/' . ucfirst($info['command']) . '.php';
        if (file_exists($file)) {
            require_once($file);
            $new_command = '\\App\\Modules\\Wechat\\Plugins\\' . ucfirst($info['command']) . '\\' . ucfirst($info['command']);
            $cfg = array('ru_id' => $this->ru_id);
            $wechat = new $new_command($cfg);
            $data = $wechat->returnData($fromusername, $info);
            if (!empty($data)) {
                // 数据回复类型
                if ($data['type'] == 'text') {
                    $this->weObj->text($data['content'])->reply();
                    //记录用户操作信息
                    $this->record_msg($fromusername, $data['content'], 1);
                } elseif ($data['type'] == 'news') {
                    $this->weObj->news($data['content'])->reply();
                    //记录用户操作信息
                    $this->record_msg($fromusername, '图文消息', 1);
                } elseif ($data['type'] == 'image') {
                    // 上传多媒体文件
                    $filename = dirname(ROOT_PATH) . '/' . $data['path'];
                    $rs = $this->weObj->uploadMedia(array('media' => realpath_wechat($filename)), 'image');
                    if (empty($rs)) {
                        logResult($this->weObj->errMsg);
                    }
                    $this->weObj->image($rs['media_id'])->reply();
                    //记录用户操作信息
                    $this->record_msg($fromusername, '图片', 1);
                }
                $return = true;
            }
        }
        return $return;
    }

    /**
     * 微信营销功能查询
     *
     * @param unknown $tousername
     * @param unknown $fromusername
     * @param unknown $keywords
     * @return boolean
     */
    public function get_marketing($fromusername, $keywords)
    {
        $return = false;
        $sql = "SELECT id, name, command, status FROM {pre}wechat_marketing WHERE (command = '" . $keywords . "') AND wechat_id = '" . $this->wechat_id . "' ORDER BY id DESC ";
        $rs = $this->db->query($sql);

        $rs = reset($rs);
        if ($rs) {
            // $match_kewords = explode(',', $rs['keywords']);
            // if(!in_array($keywords, $match_kewords) && $rs['command'] != $keywords){
            //     return $return;
            // }
            $where = array(
                'id' => $rs['id'],
                'command' => $rs['command'],
                'wechat_id' => $this->wechat_id,
            );
            $result = dao('wechat_marketing')->field('id, name, background, description, status, url')->where($where)->find();
        }
        if ($result) {
            $articles = array('type' => 'text', 'content' => '活动未启用');
            if ($result['status'] == 1) {
                $articles = array();
                // 数据
                $articles['type'] = 'news';
                $articles['content'][0]['Title'] = $result['name'];
                $articles['content'][0]['Description'] = $result['description'];
                $articles['content'][0]['PicUrl'] = get_wechat_image_path($result['background']);
                $articles['content'][0]['Url'] = strip_tags(html_out($result['url']));
            }

            // 数据回复类型
            if ($articles['type'] == 'text') {
                $this->weObj->text($articles['content'])->reply();
                //记录用户操作信息
                $this->record_msg($fromusername, $articles['content'], 1);
            } elseif ($articles['type'] == 'news') {
                $this->weObj->news($articles['content'])->reply();
                //记录用户操作信息
                $this->record_msg($fromusername, '图文消息', 1);
            }
            $return = true;
        }

        return $return;
    }

    /**
     * 主动发送信息
     *
     * @param unknown $tousername
     * @param unknown $fromusername
     * @param unknown $keywords
     * @param unknown $weObj
     * @param unknown $return
     * @return boolean
     */
    public function send_message($fromusername, $keywords, $weObj, $return = 0)
    {
        $result = false;
        $condition = array('command' => $keywords, 'enable' => 1, 'wechat_id' => $this->wechat_id);
        $rs = dao('wechat_extend')->field('name, command, config')->where($condition)->find();
        $file = MODULE_PATH . 'Plugins/' . ucfirst($rs['command']) . '/' . ucfirst($rs['command']) . '.php';
        if (file_exists($file)) {
            require_once($file);
            $new_command = '\\App\\Modules\\Wechat\\Plugins\\' . ucfirst($rs['command']) . '\\' . ucfirst($rs['command']);
            $cfg = array('ru_id' => $this->ru_id);
            $wechat = new $new_command($cfg);
            $data = $wechat->returnData($fromusername, $rs);
            if (!empty($data)) {
                if ($return) {
                    $result = $data;
                } else {
                    $weObj->sendCustomMessage($data['content']);
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * 多客服
     *
     * @param unknown $fromusername
     * @param unknown $keywords
     */
    public function customer_service($fromusername, $keywords)
    {
        $result = false;
        // 是否处在多客服流程
        $kfsession = $this->weObj->getKFSession($fromusername);
        if (empty($kfsession) || empty($kfsession['kf_account'])) {
            $kefu = dao('wechat_user')->where(array('openid' => $fromusername, 'wechat_id' => $this->wechat_id))->getField('openid');
            if ($kefu && $keywords == 'kefu') {
                $rs = $this->db->table('wechat_extend')->where(array('command' => 'kefu', 'enable' => 1, 'wechat_id' => $this->wechat_id))->getField('config');
                if (!empty($rs)) {
                    $config = unserialize($rs);
                    $msg = array(
                        'touser' => $fromusername,
                        'msgtype' => 'text',
                        'text' => array(
                            'content' => '欢迎进入多客服系统'
                        )
                    );
                    $this->weObj->sendCustomMessage($msg);
                    //记录用户操作信息
                    $this->record_msg($fromusername, $msg['text']['content'], 1);
                    // 在线客服列表
                    $online_list = $this->weObj->getCustomServiceOnlineKFlist();
                    if ($online_list['kf_online_list']) {
                        foreach ($online_list['kf_online_list'] as $key => $val) {
                            if ($config['customer'] == $val['kf_account'] && $val['status'] > 0 && $val['accepted_case'] < $val['auto_accept']) {
                                $customer = $config['customer'];
                            } else {
                                $customer = '';
                            }
                        }
                    }
                    // 转发客服消息
                    $this->weObj->transfer_customer_service($customer)->reply();
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * 关闭多客服菜单
     */
    public function close_kf($openid, $keywords)
    {
        $openid = $this->model->table('wechat_user')->where(array('openid' => $openid, 'wechat_id' => $this->wechat_id))->getField('openid');
        if ($openid) {
            $kfsession = $this->weObj->getKFSession($openid);
            if ($keywords == 'q' && isset($kfsession['kf_account']) && !empty($kfsession['kf_account'])) {
                $rs = $this->weObj->closeKFSession($openid, $kfsession['kf_account'], '客户已主动关闭多客服');
                if ($rs) {
                    $msg = array(
                        'touser' => $openid,
                        'msgtype' => 'text',
                        'text' => array(
                            'content' => '您已退出多客服系统'
                        )
                    );
                    $this->weObj->sendCustomMessage($msg);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 记录用户操作信息
     */
    public function record_msg($fromusername, $keywords, $is_wechat_admin = 0)
    {
        $uid = dao('wechat_user')->where(array('openid' => $fromusername, 'wechat_id' => $this->wechat_id))->getField('uid');
        if ($uid) {
            $data['uid'] = $uid;
            $data['msg'] = $keywords;
            $data['wechat_id'] = $this->wechat_id;
            $data['send_time'] = gmtime();
            // 微信公众号回复标识
            if ($is_wechat_admin) {
                $data['is_wechat_admin'] = $is_wechat_admin;
            }
            dao('wechat_custom_message')->data($data)->add();
        }
    }

    /**
     * 插件页面显示方法
     *
     * @param string $plugin
     */
    public function actionPluginShow()
    {
        if (is_wechat_browser() && (!isset($_SESSION['unionid']) || empty($_SESSION['unionid']) || empty($_SESSION['openid']))) {
            $back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];
            $this->redirect('oauth/index/index', array('type' => 'wechat', 'back_url' => urlencode($back_url)));
        }
        $plugin_name = I('get.name', '', 'trim');
        $ru_id = I('ru_id', 0, 'intval');
        $ru_id = !empty($ru_id) ? $ru_id : $_COOKIE['ectouch_ru_id'];
        $file = MODULE_PATH . 'Plugins/' . ucfirst($plugin_name) . '/' . ucfirst($plugin_name) . '.php';
        if (file_exists($file)) {
            include_once($file);
            $new_plugin = '\\App\\Modules\\Wechat\\Plugins\\' . ucfirst($plugin_name) . '\\' . ucfirst($plugin_name);
            $cfg = array('ru_id' => $ru_id);
            $wechat = new $new_plugin($cfg);
            $wechat->html_show();
        }
    }

    /**
     * 插件处理方法
     *
     * @param string $plugin
     */
    public function actionPluginAction()
    {
        $plugin_name = I('get.name', '', 'trim');
        $ru_id = I('ru_id', 0, 'intval');
        $ru_id = !empty($ru_id) ? $ru_id : $_COOKIE['ectouch_ru_id'];
        $file = MODULE_PATH . 'Plugins/' . ucfirst($plugin_name) . '/' . ucfirst($plugin_name) . '.php';
        if (file_exists($file)) {
            include_once($file);
            $new_plugin = '\\App\\Modules\\Wechat\\Plugins\\' . ucfirst($plugin_name) . '\\' . ucfirst($plugin_name);
            $cfg = array('ru_id' => $ru_id);
            $wechat = new $new_plugin($cfg);
            $wechat->executeAction();
        }
    }

    /**
     * 营销页面显示方法
     *
     * @param string $market
     */
    public function actionMarketShow()
    {
        if (is_wechat_browser() && (!isset($_SESSION['unionid']) || empty($_SESSION['unionid']) || empty($_SESSION['openid']))) {
            $back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];
            $this->redirect('oauth/index/index', array('type' => 'wechat', 'back_url' => urlencode($back_url)));
        }
        $market_type = I('get.type', '', 'trim');
        $function = I('get.function', '', 'trim');
        $ru_id = I('ru_id', 0, 'intval');
        $ru_id = !empty($ru_id) ? $ru_id : $_COOKIE['ectouch_ru_id'];

        $file = MODULE_PATH . 'Market/' . ucfirst($market_type) . '/' . ucfirst($market_type) . '.php';
        if (file_exists($file) && !empty($function)) {
            include_once($file);
            $market = '\\App\\Modules\\Wechat\\Market\\' . ucfirst($market_type) . '\\' . ucfirst($market_type);

            $cfg['ru_id'] = $ru_id;

            $wechat = new $market($cfg);

            $function_name = 'action' . camel_cases($function, 1);

            $wechat->$function_name();
        }
    }

    /**
     * 获取公众号配置
     *
     * @param string $secret_key
     * @return array
     */
    private function get_config($secret_key = '')
    {
        $config = dao('wechat')
            ->field('id, token, appid, appsecret, encodingaeskey, ru_id')
            ->where(array('secret_key' => $secret_key, 'status' => 1))
            ->find();
        if (empty($config)) {
            $config = array();
        }
        return $config;
    }

    /**
     * 获取access_token的接口
     * @return [type] [description]
     */
    public function check_auth()
    {
        $appid = I('get.appid');
        $appsecret = I('get.appsecret');
        if (empty($appid) || empty($appsecret)) {
            echo json_encode(array('errmsg' => '信息不完整，请提供完整信息', 'errcode' => 1));
            exit;
        }
        $config = dao('wechat')
            ->field('token, appid, appsecret')
            ->where(array('appid' => $appid, 'appsecret' => $appsecret, 'status' => 1))
            ->find();
        if (empty($config)) {
            echo json_encode(array('errmsg' => '信息错误，请检查提供的信息', 'errcode' => 1));
            exit;
        }
        $obj = new Wechat($config);
        $access_token = $obj->checkAuth();
        if ($access_token) {
            echo json_encode(array('access_token' => $access_token, 'errcode' => 0));
            exit;
        } else {
            echo json_encode(array('errmsg' => $obj->errmsg, 'errcode' => $obj->errcode));
            exit;
        }
    }

    /**
     * 微信静默授权方法
     * @param  integer $ru_id
     * @return
     */
    public static function snsapi_base($ru_id = 0)
    {
        $where = array('ru_id' => $ru_id, 'status' => 1);
        $wxinfo = dao('wechat')->field('token, appid, appsecret')->where($where)->find();
        if (!empty($wxinfo) && !empty($wxinfo['appid']) && is_wechat_browser() && (empty($_SESSION['openid']) || empty($_COOKIE['openid']))) {
            $config = array(
                'appid' => $wxinfo['appid'],
                'appsecret' => $wxinfo['appsecret'],
                'token' => $wxinfo['token'],
            );
            $obj = new Wechat($config);
            // 用code换token
            if (isset($_GET['code']) && $_GET['state'] == 'repeat') {
                $token = $obj->getOauthAccessToken();
                $_SESSION['openid'] = $token['openid'];
                cookie('openid', $token['openid'], gmtime() + 3600 * 24);

                // 更新商家微信粉丝
                $userinfo['openid'] = $token['openid'];
                logResult($userinfo);
                update_seller_wechat($userinfo, $ru_id);
            }
            // 生成请求链接
            $callback = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];
            $url = $obj->getOauthRedirect($callback, 'repeat', 'snsapi_base');
            // 授权开始
            redirect($url);
        }
    }

    /**
     * 主动发送客服消息 统一方法
     */
    public function send_custom_message($openid = 0, $msgtype = '', $data)
    {
        $msg = array();
        if ($msgtype == 'text') {
            $msg = array(
                'touser' => $openid,
                'msgtype' => 'text',
                'text' => array(
                    'content' => $data
                )
            );
        } elseif ($msgtype == 'image') {
            $msg = array(
                'touser' => $openid,
                'msgtype' => 'image',
                'image' => array(
                    'media_id' => $data
                )
            );
        } elseif ($msgtype == 'voice') {
            $msg = array(
                'touser' => $openid,
                'msgtype' => 'voice',
                'voice' => array(
                    'media_id' => $data
                )
            );
        } elseif ($msgtype == 'video') {
            $msg = array(
                'touser' => $openid,
                'msgtype' => 'video',
                'video' => array(
                    'media_id' => $data['media_id'],
                    'thumb_media_id' => $data['media_id'],
                    'title' => $data['title'],
                    'description' => $data['description']
                )
            );
        } elseif ($msgtype == 'music') {
            $msg = array(
                'touser' => $openid,
                'msgtype' => 'music',
                'music' => array(
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'musicurl' => $data['musicurl'],
                    'hqmusicurl' => $data['hqmusicurl'],
                    'thumb_media_id' => $data['thumb_media_id']
                )
            );
        } elseif ($msgtype == 'news') {
            /**
             * $newsData 数组结构:
             *  array(
             *      "0"=>array(
             *          'title'=>'msg title',
             *          'description'=>'summary text',
             *          'picurl'=>'http://www.domain.com/1.jpg',
             *          'url'=>'http://www.domain.com/1.html'
             *      ),
             *      "1"=>....
             *  )
             */
            $newsData = $data;
            $msg = array(
                'touser' => $openid,
                'msgtype' => 'news',
                'news' => array(
                    'articles' => $newsData,
                )
            );
        }

        $this->weObj->sendCustomMessage($msg);
    }
}
