<?php
function get_admin_ru_id_seller()
{
    $self = explode("/", substr(PHP_SELF, 1));
    $count = count($self);

    if ($count > 1) {
        $real_path = $self[$count - 2];
        if ($real_path == 'mobile') {
            $admin_id = $_SESSION['seller_id'];
        }

        if (isset($admin_id)) {
            $sql = "select ru_id from " . $GLOBALS['ecs']->table('admin_user') . " where user_id = '$admin_id'";
            return $GLOBALS['db']->getRow($sql);
        }
    }
}



function set_seller_menu()
{
    define('IN_ECS', true);
    define('MOBILE_WECHAT', APP_PATH . 'Wechat'); 
    include_once(dirname(ROOT_PATH) . '/' . SELLER_PATH . '/' . 'includes/inc_priv.php');
    include_once(dirname(ROOT_PATH) . '/' . SELLER_PATH . '/' . 'includes/inc_menu.php');
    $lang = str_replace('-', '_', C('shop.lang'));
    require(dirname(ROOT_PATH) . '/' . 'languages/' . $lang . '/' . ADMIN_PATH . '/common_merchants.php');

    
    foreach ($modules as $key => $value) {
        ksort($modules[$key]);
    }
    ksort($modules);

    
    $condition['user_id'] = isset($_SESSION['seller_id']) ? intval($_SESSION['seller_id']) : 0;
    $seller_action_list = dao('admin_user')->where($condition)->getField('action_list');
    $action_list = explode(',', $seller_action_list);

    
    $action_menu = [];
    foreach ($purview as $key => $val) {
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                if (in_array($v, $action_list)) {
                    $action_menu[$key] = $v;
                }
            }
        } else {
            if (in_array($val, $action_list)) {
                $action_menu[$key] = $val;
            }
        }
    }

    
    foreach ($modules as $key => $val) {
        foreach ($val as $k => $v) {
            if (!array_key_exists($k, $action_menu)) {
                unset($modules[$key][$k]);
            }
        }

        if (empty($modules[$key])) {
            unset($modules[$key]);
        }
    }

    
    $menu = [];
    $i = 0;
    foreach ($modules as $key => $val) {
        if ($key == '22_wechat') {
            $menu[$i] = [
                'action' => $key,
                'label' => get_menu_url(reset($val), $_LANG[$key]),
                'url' => get_wechat_menu_url(reset($val)),
                'children' => []
            ];

            foreach ($val as $k => $v) {
                $menu[$i]['children'][] = [
                    'action' => $k,
                    'label' => get_menu_url($v, $_LANG[$k]),
                    'url' => get_wechat_menu_url($v),
                    'status' => get_user_menu_status($k)
                ];
            }
        } else {
            $menu[$i] = [
                'action' => $key,
                'label' => get_menu_url(reset($val), $_LANG[$key]),
                'url' => get_menu_url(reset($val)),
                'children' => []
            ];

            foreach ($val as $k => $v) {
                $menu[$i]['children'][] = [
                    'action' => $k,
                    'label' => get_menu_url($v, $_LANG[$k]),
                    'url' => get_menu_url($v),
                    'status' => get_user_menu_status($k)
                ];
            }
        }

        $i++;
    }


    unset($modules, $purview); 
    return $menu;
}


function get_menu_url($url = '', $name = '')
{
    if ($url) {
        $url = '../seller/' . $url;
        $url_arr = explode('?', $url);
        if (!$url_arr[0] || !is_file($url_arr[0])) {
            $url = '#';
            if ($name) {
                $name = '<span style="text-decoration: line-through; color:#ccc; ">' . $name . '</span>';
            }
        }
    }

    if ($name) {
        return $name;
    } else {
        return $url;
    }
}


function get_wechat_menu_url($url = '', $name = '')
{
    if ($url) {
        $url_arr = explode('?', $url);
        if (!$url_arr[0] || !is_file($url_arr[0])) {
            $url = '#';
            if ($name) {
                $name = '<span style="text-decoration: line-through; color:#ccc; ">' . $name . '</span>';
            }
        }
    }

    if ($name) {
        return $name;
    } else {
        return $url;
    }
}


function get_user_menu_status($action = '')
{
    $user_menu_arr = get_user_menu_list();
    if ($user_menu_arr && in_array($action, $user_menu_arr)) {
        return 1;
    } else {
        return 0;
    }
}


function get_user_menu_list()
{
    $adminru = get_admin_ru_id_seller();
    if ($adminru['ru_id'] > 0) {
        $sql = " SELECT user_menu FROM " . $GLOBALS['ecs']->table('seller_shopinfo') . " WHERE ru_id = '" . $adminru['ru_id'] . "' ";
        $user_menu_str = $GLOBALS['db']->getOne($sql);
        if ($user_menu_str) {
            $user_menu_arr = explode(',', $user_menu_str);
            return $user_menu_arr;
        }
    }
    return false;
}


function get_select_menu()
{
    $left_menu = [

        '22_wechat' =>
            [
                '01_wechat_admin' => 'm=wechat&c=seller&a=modify',
                '02_mass_message' => 'm=wechat&c=seller&a=mass_message',
                '02_mass_message_01' => 'm=wechat&c=seller&a=mass_list',
                '03_auto_reply' => 'm=wechat&c=seller&a=reply_subscribe',
                '03_auto_reply_01' => 'm=wechat&c=seller&a=reply_msg',
                '03_auto_reply_02' => 'm=wechat&c=seller&a=reply_keywords',
                '04_menu' => 'm=wechat&c=seller&a=menu_list',
                '04_menu_01' => 'm=wechat&c=seller&a=menu_edit',
                '05_fans' => 'm=wechat&c=seller&a=subscribe_list',
                '05_fans_01' => 'm=wechat&c=seller&a=custom_message_list',
                '05_fans_02' => 'm=wechat&c=seller&a=subscribe_search',
                '05_fans_03' => 'm=wechat&c=seller&a=sys_tags',
                '06_media' => 'm=wechat&c=seller&a=article',
                '06_media_01' => 'm=wechat&c=seller&a=article_edit',
                '06_media_02' => 'm=wechat&c=seller&a=article_edit_news',
                '06_media_03' => 'm=wechat&c=seller&a=picture',
                '06_media_04' => 'm=wechat&c=seller&a=voice',
                '06_media_05' => 'm=wechat&c=seller&a=video',
                '06_media_06' => 'm=wechat&c=seller&a=video_edit',
                '07_qrcode' => 'm=wechat&c=seller&a=qrcode_list',
                '07_qrcode_01' => 'm=wechat&c=seller&a=qrcode_edit',
                '09_extend' => 'm=wechat&c=seller&a=extend_index',
                '09_extend_01' => 'm=wechat&c=seller&a=extend_edit',
                '09_extend_02' => 'm=wechat&c=seller&a=winner_list',
                '10_market' => 'm=wechat&c=seller&a=market_index',
                '10_market_01' => 'm=wechat&c=seller&a=market_list',
                '10_market_02' => 'm=wechat&c=seller&a=market_edit',
                '10_market_03' => 'm=wechat&c=seller&a=data_list',
                '10_market_04' => 'm=wechat&c=seller&a=market_qrcode',
            ]

    ];

    $url = isset($_SERVER["QUERY_STRING"]) ? trim($_SERVER["QUERY_STRING"]) : '';

    
    $info = get_url_query($url);
    $url = match_url($url, $info['a']);

    $menu_arr = get_menu_arr($url, $left_menu);

    return $menu_arr;
}


function match_url($url = '', $fuction_a = '', $prefix = 'm=wechat&c=seller&a=')
{
    $is_match = strstr($url, $fuction_a);
    if ($is_match) {
        $url = $prefix . $fuction_a;
    }
    return $url;
}


function get_menu_arr($url = '', $list = [])
{
    static $menu_arr = [];
    static $menu_key = null;
    foreach ($list as $key => $val) {
        if (is_array($val)) {
            $menu_key = $key;
            get_menu_arr($url, $val);
        } else {
            if ($val == $url) {
                $menu_arr['action'] = $menu_key;
                $menu_arr['current'] = $key;
                
                $key_2 = substr($key, 0, -3);
                $menu_arr['current_2'] = $key_2;
            }
        }
    }
    return $menu_arr;
}


function edit_upload_image($url = '', $no_path = 'public/assets/wechat')
{
    if (strpos($url, $no_path)) {
        $prex_patch = __HOST__ . __ROOT__;
    } else {
        $prex_patch = __HOST__ ;
    }

    $prex_patch = rtrim($prex_patch, '/') . '/';
    $url = str_replace($prex_patch, '', $url);
    $url = ltrim($url, '/');
    return $url;
}


function add_url_suffix($url = '', $vars = '')
{
    
    $info = parse_url($url);
    $path = !empty($info['path']) ? $info['path'] : '';
    
    if (is_string($vars)) { 
        parse_str($vars, $vars);
    } elseif (!is_array($vars)) {
        $vars = [];
    }
    if (isset($info['query'])) { 
        $info['query'] = htmlspecialchars_decode($info['query']); 
        parse_str($info['query'], $params);
        $vars = array_merge($params, $vars);
    }
    $depr = '?';
    if (!empty($vars)) {
        $vars = http_build_query($vars);
        $path .= $depr . $vars;
    }
    $url = $info['host'] . $path;
    
    
    if (!preg_match("/^(http|https):/", $url)) {
        $url = (is_ssl() ? 'https://' : 'http://') . $url;
    }
    return strtolower($url);
}



function file_write($filename, $content = '')
{
    $fp = fopen(ROOT_PATH . 'storage/app/certs/' . $filename, "w+"); 
    flock($fp, LOCK_EX);
    fwrite($fp, $content);
    flock($fp, LOCK_UN);
    fclose($fp);
}


function new_html_in($str)
{
    $str = htmlspecialchars($str);
    
    if (get_magic_quotes_gpc()) {
        $str = stripslashes($str);
    }
    return $str;
}


function get_share_type($val = '')
{
    $share_type = '';
    switch ($val) {
        case '1':
            $share_type = '分享到朋友圈';
            break;
        case '2':
            $share_type = '分享给朋友';
            break;
        case '3':
            $share_type = '分享到QQ';
            break;
        case '4':
            $share_type = '分享到QQ空间';
            break;
        default:
            break;
    }
    return $share_type;
}


function get_wechat_user_from($from = 0)
{
    $from_type = '';
    switch ($from) {
        case 0:
            $from_type = '微信公众号关注';
            break;
        case 1:
            $from_type = '微信授权注册';
            break;
        case 2:
            $from_type = '微信扫码注册';
            break;
        case 3:
            $from_type = '微信小程序注册';
            break;
        default:
            break;
    }
    return $from_type;
}


function realpath_wechat($file)
{
    if (class_exists('\CURLFile')) {
        return new \CURLFile(realpath($file));
    } else {
        return '@' . realpath($file);
    }
}


function check_template_log($openid = '', $wechat_id = 0, $weObj)
{
    $logs = dao('wechat_template_log')->field('wechat_id, code, openid, data, url')->where(['openid' => $openid, 'wechat_id' => $wechat_id, 'status' => 0])->order('id desc')->find();
    if (!empty($logs)) {
        
        $message_data['touser'] = $logs['openid'];
        $message_data['template_id'] = dao('wechat_template')->where(['code' => $logs['code']])->getField('template_id');
        $message_data['url'] = $logs['url'];
        $message_data['topcolor'] = '#FF0000';
        $message_data['data'] = unserialize($logs['data']);
        $rs = $weObj->sendTemplateMessage($message_data);
        if (empty($rs)) {
            
            
            exit('null');
        }
        
        dao('wechat_template_log')->data(['msgid' => $rs['msgid']])->where(['code' => $logs['code'], 'openid' => $logs['openid'], 'wechat_id' => $wechat_id])->save();
    }
}


function message_log_alignment_add($wedata = [], $wechat_id = 0)
{
    
    if ($wedata['MsgType'] == 'event') {
        $data = [
            'wechat_id' => $wechat_id,
            'fromusername' => $wedata['FromUserName'],
            'createtime' => $wedata['CreateTime'],
            'msgtype' => $wedata['MsgType'],
            'keywords' => $wedata['EventKey'],
        ];
        
        $where = [
            'wechat_id' => $wechat_id,
            'fromusername' => $wedata['FromUserName'],
            'createtime' => $wedata['CreateTime'],
            'keywords' => $data['keywords'],
        ];
    } else {
        $data = [
            'wechat_id' => $wechat_id,
            'fromusername' => $wedata['FromUserName'],
            'createtime' => $wedata['CreateTime'],
            'msgtype' => $wedata['MsgType'],
            'keywords' => $wedata['Content'],
            'msgid' => $wedata['MsgId'],
        ];
        
        $where = [
            'wechat_id' => $wechat_id,
            'msgid' => $data['msgid'],
            'keywords' => $data['keywords'],
        ];
    }
    
    $rs = dao('wechat_message_log')->where($where)->find();
    if (empty($rs)) {
        dao('wechat_message_log')->data($data)->add();
    }
}


function message_log_alignment_send($contents, $wechat_id = 0)
{
    
    if ($contents['msgtype'] == 'event') {
        
        $where = [
            'wechat_id' => $wechat_id,
            'fromusername' => $contents['fromusername'],
            'createtime' => $contents['createtime'],
            'keywords' => $contents['keywords'],
            'is_send' => 0
        ];
    } else {
        
        $where = [
            'wechat_id' => $wechat_id,
            'msgid' => $contents['msgid'],
            'keywords' => $contents['keywords'],
            'is_send' => 0
        ];
    }
    dao('wechat_message_log')->data(['is_send' => 1])->where($where)->save();
    
    $map['fromusername'] = $contents['fromusername'];
    $map['keywords'] = $contents['keywords'];
    $lastId = dao('wechat_message_log')->where($map)->order('id desc')->getField('id');
    if (!empty($lastId)) {
        $map['is_send'] = 1;
        $map['_string'] = 'id < ' . $lastId;
        dao('wechat_message_log')->where($map)->delete();
    }
}


function update_wechatuser_subscribe($openid, $wechat_id = 0, $weObj)
{
    $userinfo = $weObj->getUserInfo($openid);
    if (!empty($userinfo) && !empty($userinfo['unionid'])) {
        $user_data = [
            'subscribe' => $userinfo['subscribe'],
            'subscribe_time' => $userinfo['subscribe_time'],
        ];
        $res = dao('wechat_user')->field('openid, unionid')->where(['unionid' => $userinfo['unionid'], 'wechat_id' => $wechat_id])->find();
        if (!empty($res)) {
            dao('wechat_user')->data($user_data)->where(['unionid' => $userinfo['unionid'], 'wechat_id' => $wechat_id])->save();
        }
    }
}


function delete_local_oss_image($filename)
{
    if (C('shop.open_oss') == 1) {
        if (file_exists($filename) && is_file($filename)) {
            unlink($filename);
        }
    }
}


function get_keywords_list($wechat_id)
{
    $sys_keywords = dao('wechat_extend')->field('command')->where(['wechat_id' => $wechat_id, 'enable' => 1])->select();
    $rule_keywords = dao('wechat_rule_keywords')->field('rule_keywords')->where(['wechat_id' => $wechat_id])->select();

    foreach ($sys_keywords as $key => $value) {
        if ($value['command'] == 'bonus' || $value['command'] == 'sign') {
            unset($sys_keywords[$key]);
        }
    }
    $new_sys_keywords = array_column($sys_keywords, 'command');
    $new_rule_keywords = array_column($rule_keywords, 'rule_keywords');

    $key_name = md5('wechat_keywords' . count($sys_keywords) + count($rule_keywords));
    $keywords_list = S($key_name);
    if ($keywords_list === false) {
        $keywords_list = array_merge($new_sys_keywords, $new_rule_keywords);
        S($key_name, $keywords_list);
    }

    return $keywords_list;
}


function return_is_drp($scene_id)
{
    $scenes = [
        'is_drp' => false,
        'parent_id' => 0,
        'drp_parent_id' => 0,
    ];

    if (strpos($scene_id, 'u') === 0) {
        
        $scene_uid = str_replace('u=', '', $scene_id);
        $parent_id = intval($scene_uid);

        $users = dao('users')->field('user_id')->where(['user_id' => $parent_id])->find();

        $parent_id = !empty($users) ? $parent_id : 0;

        $scenes['parent_id'] = $parent_id;
        $scenes['is_drp'] = false;

    } elseif (strpos($scene_id, 'd') === 0 && is_dir(APP_DRP_PATH)) {
        
        $scene_did = str_replace('d=', '', $scene_id);
        $drp_parent_id = intval($scene_did);

        $drp = dao('drp_shop')->field('user_id')->where(['user_id' => $drp_parent_id, 'audit' => 1])->find();

        $drp_parent_id = !empty($drp) ? $drp_parent_id : 0;

        $scenes['drp_parent_id'] = $drp_parent_id;
        $scenes['is_drp'] = true;
    }

    return $scenes;
}


function get_gallery_album_tree($tree_id = 0, $ru_id = 0)
{
    $three_arr = [];
    $count = dao('gallery_album')->where(['parent_album_id' => $tree_id, 'ru_id' => $ru_id])->count();
    if ($count > 0 || $tree_id == 0) {
        $res =  dao('gallery_album')->where(['parent_album_id' => $tree_id, 'ru_id' => $ru_id])->order('album_id DESC')->select();
        foreach ($res as $k => $row) {
            $three_arr[$k]['id']   = $row['album_id'];
            $three_arr[$k]['name'] = $row['album_mame'];
            $three_arr[$k]['haschild'] = 0;
            if (isset($row['album_id'])) {
                $child_tree = get_gallery_album_tree($row['album_id'], $ru_id);
                if ($child_tree) {
                    $three_arr[$k]['album_id'] = $child_tree;
                    $three_arr[$k]['haschild'] = 1;
                }
            }
        }
    }

    return $three_arr;
}


function return_qrcode_status($id, $user_id = 0)
{
    $status = 1;
    $users = dao('users')->field('user_id, user_name')->where(['user_id' => $user_id])->find();
    if (empty($users)) {
        $rs = dao('wechat_qrcode')->data(['status' => 0])->where(['id' => $id])->save();
        $status = 0;
    }
    return $status;
}