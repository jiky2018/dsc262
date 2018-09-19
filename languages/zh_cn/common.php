<?php

/**
 * DSC 前台语言文件
 * ============================================================================
 * * 版权所有 2005-2017 上海商创网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecmoban.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: Zhuo $
 * $Id: common.php 2016-01-04 Zhuo $
*/

$_LANG['merchants_article'] = "您查看的文章已不存在，请与网站联系";

/* 优惠券 start */
$_LANG['lang_goods_coupons']['all_pay'] = "全场券";
$_LANG['lang_goods_coupons']['user_pay'] = "会员券";
$_LANG['lang_goods_coupons']['goods_pay'] = "购物券";
$_LANG['lang_goods_coupons']['reg_pay'] = "注册券";
$_LANG['lang_goods_coupons']['free_pay'] = "免邮券";
$_LANG['lang_goods_coupons']['not_pay'] = "未知";
$_LANG['lang_goods_coupons']['is_goods'] = "限商品";
$_LANG['lang_goods_coupons']['is_cate'] = "限分类";
$_LANG['lang_goods_coupons']['is_all'] = "全品类通用";

$_LANG['lang_coupons_receive_failure'] = "领取失败,券已经被领完了";
$_LANG['lang_coupons_user_receive'] = "领取失败,您已经领取过该券了!每人限领取 %s 张";
$_LANG['lang_coupons_user_rank'] = "领取失败,仅限会员等级为: %s 领取";
$_LANG['lang_coupons_receive_succeed'] = "领取成功！感谢您的参与，祝您购物愉快~";
/* 优惠券 end */

/* 众筹 start */
$_LANG['lang_crowd_preheat'] = "预热中";
$_LANG['lang_crowd_of'] = "众筹中";
$_LANG['lang_crowd_succeed'] = "众筹成功";

$_LANG['lang_crowd_art_succeed'] = "发布成功";
$_LANG['lang_crowd_art_succeed_repeat'] = "已经发布过啦，请不要重复发布！";
$_LANG['lang_crowd_login'] = "登陆后发布话题";

$_LANG['lang_crowd_page_title'] = "众筹-收货地址";

$_LANG['lang_crowd_login_focus'] = "只有登陆用户才能关注";
$_LANG['lang_crowd_focus_succeed'] = "关注成功";
$_LANG['lang_crowd_focus_repeat'] = "已经关注过啦，请不要重复关注！";
$_LANG['lang_crowd_focus_cancel'] = "取消关注成功!";

$_LANG['lang_crowd_like'] = "点赞成功";
$_LANG['lang_crowd_like_repeat'] = "已经点过赞啦，请不要重复点赞！";

$_LANG['lang_crowd_next_step'] = "下一步";
$_LANG['lang_crowd_not_login'] = "您尚未登录，请登录您的账号！";

$_LANG['lang_crowd_not_pay'] = "您有未支付的众筹订单，请付款后再提交新订单";
$_LANG['lang_crowd_not_address'] = "您没有设置收货地址";
/* 众筹 end */

$_LANG['lang_product_sn'] = "货品货号";

$_LANG['dwt_shop_name'] = "";

$_LANG['order_payed_sms'] = '订单 %s 付款了，收货人：%s 电话：%s'; //wang

//ecmoban模板堂 --zhuo start 审核收货人地址
$_LANG['order_address_stay'] = "无法提交订单<br/>收货地址已被修改，待审核中...";
$_LANG['order_address_no'] = "无法提交订单<br/>收货地址已被修改，审核未通过...";
$_LANG['index_lnk'] = "返回首页，继续购物！";

$_LANG['edit_address_success'] = '您的收货地址信息已成功更新,待审核通过';
$_LANG['address_list_lnk'] = '返回地址列表';

$_LANG['signin_failed_user'] = '收货地址为空，请您添加收货地址';
$_LANG['lnk_user'] = "去添加收货地址";
//ecmoban模板堂 --zhuo end 审核收货人地址

$_LANG['single_user'] = '用户晒单'; //by zhang
$_LANG['discuss_user'] = '网友讨论圈'; //by zhang
$_LANG['allcount'] = '全部帖子'; //by zhang
$_LANG['s_count'] = '晒单帖';
$_LANG['t_count'] = '讨论帖';
$_LANG['w_count'] = '问答帖';
$_LANG['q_count'] = '圈子帖';
$_LANG['reply_number'] = '回复数';
$_LANG['click_count'] = '点击数';
$_LANG['sort']['single_sort'] = '排序';

$_LANG['set_gcolor'] = '设置商品颜色'; //by zhang

//ecmoban模板堂 --zhuo start
$_LANG['ff'][FF_NOMAINTENANCE] =  '未维修';
$_LANG['ff'][FF_MAINTENANCE] =  '已维修';
$_LANG['ff'][FF_NOEXCHANGE] =  '未换货';
$_LANG['ff'][FF_EXCHANGE] =  '已换货';

$_LANG['steps_UserLogin'] = '您尚未登录，无法申请入驻';
$_LANG['UserLogin'] = '去登陆';

$_LANG['please_select'] = '请选择';
$_LANG['country'] = '国家';
$_LANG['province'] = '省';
$_LANG['city'] = '市';
$_LANG['area'] = '区';

$_LANG['delivery_warehouse'] = '发货仓库';

$_LANG['group_shortage'] = "对不起，该套餐主件商品已经库存不足暂停销售。\n你现在要进行缺货登记来预订该商品吗？";
$_LANG['group_not_on_sale'] = '对不起，该套餐主件商品已经下架。';
$_LANG['group_goods_not_exists'] = "对不起，指定的套餐主件商品不存在";

$_LANG['not_start'] = '未开始';
$_LANG['overdue'] = '已过期';
$_LANG['has_ended'] = '已结束';
$_LANG['not_use'] = '未使用';
$_LANG['had_use'] = '已使用';
//ecmoban模板堂 --zhuo end

/* 用户登录语言项 */
$_LANG['empty_username_password'] = '对不起，您必须完整填写用户名和密码。';
$_LANG['shot_message'] = "短消息";

/* 公共语言项 */

$_LANG['largess'] = '赠品';
$_LANG['sys_msg'] = '系统提示';
$_LANG['catalog'] = '目录';
$_LANG['please_view_order_detail'] = '商品已发货，详情请到用户中心订单详情查看';
$_LANG['user_center'] = '用户中心';
$_LANG['shop_closed'] = "本店盘点中，请您稍后再来...";
$_LANG['shop_register_closed'] = '该网店暂停注册';
$_LANG['shop_upgrade'] = "本店升级中，管理员从 <a href=\\\"admin/\\\">管理中心</a> 登录后，系统会自动完成升级";
$_LANG['js_languages']['process_request'] = '正在处理您的请求...';
$_LANG['process_request'] = '正在处理您的请求...';
$_LANG['please_waiting'] = '请稍等, 正在载入中...';
$_LANG['icp_number'] = 'ICP备案证书号';
$_LANG['plugins_not_found'] = "插件 %s 无法定位";
$_LANG['home'] = '首页';
$_LANG['back_up_page'] = '返回上一页';
$_LANG['illegal_operate'] = '非法操作';
$_LANG['close_window'] = '关闭窗口';
$_LANG['back_home'] = '返回首页';
$_LANG['ur_here'] = '当前位置:';
$_LANG['all_goods'] = '全部商品';
$_LANG['all_recommend'] = "全部推荐";
$_LANG['all_attribute'] = "全部";
$_LANG['promotion_goods'] = '促销商品';
$_LANG['best_goods'] = '精品推荐';
$_LANG['new_goods'] = '新品上市';
$_LANG['hot_goods'] = '热销商品';
$_LANG['view_cart'] = "查看购物车";
$_LANG['catalog'] = '所有分类';
$_LANG['regist_login'] = '注册/登录';
$_LANG['profile'] = '个人资料';
$_LANG['query_info'] = "共执行 %d 个查询，用时 %f 秒，在线 %d 人";
$_LANG['gzip_enabled'] = '，Gzip 已启用';
$_LANG['gzip_disabled'] = '，Gzip 已禁用';
$_LANG['memory_info'] = '，占用内存 %0.3f MB';
$_LANG['cart_info'] = "%d";
$_LANG['shopping_and_other'] = '购买过此商品的人还购买过';
$_LANG['bought_notes'] = '购买记录';
$_LANG['later_bought_amounts'] = '近期成交数量';
$_LANG['bought_time'] = '购买时间';
$_LANG['turnover'] = '成交';
$_LANG['no_notes'] = '还没有人购买过此商品';
$_LANG['shop_price'] = "商 城 价";
$_LANG['market_price'] = "市场价";
$_LANG['goods_brief'] = '商品描述：';
$_LANG['goods_album'] = '商品相册';
$_LANG['promote_price'] = "促 销 价";
$_LANG['fittings_price'] = '配件价格：';
$_LANG['collect'] = '加入收藏夹';
$_LANG['add_to_cart'] = "加入购物车";
$_LANG['return_to_cart'] = "放回购物车";
$_LANG['search_goods'] = '商品搜索';
$_LANG['search'] = '搜索';
$_LANG['wholesale_search'] = '搜索批发商品';
$_LANG['article_title'] = '文章标题';
$_LANG['article_author'] = '作者';
$_LANG['article_add_time'] = '添加日期';
$_LANG['relative_file'] = '[ 相关下载 ]';
$_LANG['category'] = '分类';
$_LANG['brand'] = '品牌';
$_LANG['price_min'] = '最小价格';
$_LANG['price_max'] = '最大价格';
$_LANG['goods_name'] = '商品名称';
$_LANG['goods_attr'] = '商品属性';
$_LANG['goods_price_ladder'] = '价格阶梯';
$_LANG['ladder_price'] = '批发价格';
$_LANG['shop_prices'] = "本 店 价";
$_LANG['market_prices'] = "市 场 价";
$_LANG['group_buy_price'] = "团 购 价";
$_LANG['seckill_price'] = "秒 杀 价";
$_LANG['presale_price'] = "预 售 价";
$_LANG['deposit'] = '团购保证金';
$_LANG['amount'] = '商品总价';
$_LANG['number'] = '购买数量';
$_LANG['handle'] = '操作';
$_LANG['add'] = '添加';
$_LANG['edit'] = '编辑';
$_LANG['drop'] = '删除';
$_LANG['view'] = '查看';
$_LANG['modify'] = '修改';
$_LANG['is_cancel'] = '取消';
$_LANG['amend_amount'] = '修改数量';
$_LANG['end'] = '结束';
$_LANG['require_field'] = '(必填)';
$_LANG['search_result'] = '搜索结果';
$_LANG['order_number'] = '订单号';
$_LANG['consignment'] = '发货单';
$_LANG['activities'] = '商品正在进行的活动';
$_LANG['remark_package'] = '超值礼包';
$_LANG['old_price'] = '原  价：';
$_LANG['package_price'] = '礼包价：';
$_LANG['then_old_price'] = '节  省：';
$_LANG['free_goods'] = '免运费商品';
$_LANG['back_auction_home'] = '返回拍卖首页';

$_LANG['searchkeywords_notice'] = '匹配多个关键字全部，可用 "空格" 或 "AND" 连接。如 win32 AND unix<br />匹配多个关键字其中部分，可用"+"或 "OR" 连接。如 win32 OR unix';
$_LANG['hidden_outstock'] = '隐藏已脱销的商品';
$_LANG['keywords'] = '关键字';
$_LANG['sc_ds'] = '搜索简介';
$_LANG['button_search'] = '立即搜索';
$_LANG['no_search_result'] = '无法搜索到您要找的商品！';
$_LANG['all_category'] = '所有分类';
$_LANG['all_brand'] = '所有品牌';
$_LANG['all_option'] = '请选择';
$_LANG['extension'] = '扩展选项';
$_LANG['gram'] = '克';
$_LANG['kilogram'] = '千克';
$_LANG['goods_sn'] = '商品货号：';
$_LANG['bar_code'] = '条形条码';
$_LANG['goods_brand'] = '品　　牌：';
$_LANG['goods_weight'] = '商品重量：';
$_LANG['goods_number'] = '商品库存：';
$_LANG['goods_give_integral'] = '购买此商品赠送：';
$_LANG['goods_integral'] = '可　　用：';
$_LANG['goods_bonus'] = '购买此商品可获得红包：';
$_LANG['goods_free_shipping'] = '此商品为免运费商品，计算配送金额时将不计入配送费用';
$_LANG['goods_rank'] = '用户评价：';
$_LANG['goods_compare'] = '商品比较';
$_LANG['properties'] = '商品属性：';
$_LANG['brief'] = '简要介绍：';
$_LANG['add_time'] = '上架时间：';
$_LANG['residual_time'] = '剩余时间';
$_LANG['begin_time_soon'] = '距开始时间';
$_LANG['day'] = '天';
$_LANG['hour'] = '小时';
$_LANG['minute'] = '分钟';
$_LANG['compare'] = '比较';
$_LANG['volume_price'] = '购买商品达到以下数量区间时可享受的优惠价格';
$_LANG['number_to'] = '数量';
$_LANG['article_list'] = '文章列表';

$_LANG['not'] = "无";
$_LANG['open'] = "展开";
$_LANG['open_more'] = "展开更多";
$_LANG['stop'] = "收起";
$_LANG['stop_more'] = "收起更多";
$_LANG['time'] = "时间";
$_LANG['money_symbol'] = "￥";
$_LANG['current_price'] = '当前价格';
$_LANG['start_time'] = '开始时间';
$_LANG['end_time'] = '结束时间';
$_LANG['con_cus_service'] = '联系客服';
$_LANG['min_fare'] = '最低加价';
$_LANG['max_fare'] = '最高加价';
$_LANG['store_shop'] = '商家店铺';
$_LANG['see_more'] = '查看更多';
$_LANG['comprehensive'] = '综合好评';
$_LANG['branch'] = '分';
$_LANG['score_detail'] = '评分明细';
$_LANG['industry_compare'] = '行业相比';
$_LANG['goods'] = '商品';
$_LANG['service'] = '服务';
$_LANG['prescription'] = '时效';
$_LANG['store_total'] = '店铺总分';
$_LANG['company'] = '公&nbsp;&nbsp;&nbsp;&nbsp;司';
$_LANG['seat_of'] = '所在地';
$_LANG['online_service'] = '在线客服';
$_LANG['enter_the_shop'] = '进入店铺';
$_LANG['follow'] = '关注';
$_LANG['follow_yes'] = '已关注';
$_LANG['follow_store'] = '关注店铺';
$_LANG['assign'] = '确定';
$_LANG['au_number'] = '次出价';
$_LANG['brand_home'] = '品牌首页';
$_LANG['brand_category'] = '品牌分类';

$_LANG['look_new'] = '找新品';
$_LANG['look_hot'] = '找热卖';
$_LANG['all_category'] = '全部分类';
$_LANG['change_a_lot'] = '换一批';
$_LANG['best'] = '精品';
$_LANG['see_all'] = '查看全部';
$_LANG['ren'] = '人';
$_LANG['jian_goods'] = '件商品';
$_LANG['guess_love'] = '猜你喜欢';
$_LANG['sale_amount'] = '销售量';
$_LANG['screen_price'] = '请填写筛选价格';
$_LANG['screen_price_left'] = '请填写筛选左边价格';
$_LANG['screen_price_right'] = '请填写筛选右边价格';
$_LANG['screen_price_dy'] = '左边价格不能大于或等于右边价格';
$_LANG['publish_top'] = '发表新话题';
$_LANG['types'] = '类型';
$_LANG['publish'] = '发表';
$_LANG['statement'] = '声明';
$_LANG['statement_one'] = '1、欢迎您在此提出与商品有关的问题，为保证话题质量，铁牌及以上级别用户可以发表话题，注册及以上级别用户可以回复；';
$_LANG['statement_two'] = '2、网友讨论采用先发布后审核原则，如果我们认为您的发贴不能给其他用户带来帮助或违反国家有关规定，' . @$dwt_shop_name . '商城有权删除';
$_LANG['commentTitle_not'] = '主题不能为空';
$_LANG['commentTitle_xz'] = '标题长度只能在2-50个字符之间';
$_LANG['content_not'] = '内容不能为空';
$_LANG['captcha_not'] = '验证码不能为空';
$_LANG['captcha_xz'] = '请输入4位数的验证码';
$_LANG['message_see'] = '【新消息】请查收!';
$_LANG['message_not'] = '【　　　】请查收!';

/* 商品比较JS语言项 */
$_LANG['compare_js']['button_compare'] = '比较选定商品';
$_LANG['compare_js']['exist'] = '您已经选择了%s';
$_LANG['compare_js']['count_limit'] = '最多只能选择4个商品进行对比';
$_LANG['compare_js']['goods_type_different'] = '\"%s\"和已选择商品类型不同无法进行对比';

$_LANG['bonus'] = '优惠券：';
$_LANG['no_comments'] = '暂时还没有任何用户评论';
$_LANG['give_comments_rank'] = '给出';
$_LANG['comments_rank'] = '评价';
$_LANG['comment_num'] = "用户评论 %d 条记录";
$_LANG['login_please'] = '由于您还没有登录，因此您还不能使用该功能。';
$_LANG['collect_existed'] = '该商品已经存在于您的收藏夹中。';
$_LANG['collect_success'] = '该商品已经成功地加入收藏夹。';
$_LANG['collect_brand_existed'] = '您已经关注过该品牌';
$_LANG['collect_brand_success'] = '成功关注该品牌';
$_LANG['cancel_brand_success'] = '已取消关注该品牌';
$_LANG['send_authemail_success'] = '验证邮件发送成功';
$_LANG['copyright'] = "&copy; 2005-%s %s 版权所有，并保留所有权利。";
$_LANG['no_ads_id'] = '没有指定广告的ID以及跳转的URL地址!';
$_LANG['remove_collection_confirm'] = '您确定要从收藏夹中删除选定的商品吗？';
$_LANG['err_change_attr'] = '没有找到指定的商品或者没有找到指定的商品属性。';

$_LANG['collect_goods'] = '收藏商品';
$_LANG['plus'] = '加';
$_LANG['minus'] = '减';
$_LANG['yes'] = '是';
$_LANG['no'] = '否';

$_LANG['same_attrbiute_goods'] = '相同%s的商品';

/* TAG */
$_LANG['button_submit_tag'] = '添加我的标记';
$_LANG['tag_exists'] = '您已经为该商品添加过一个标记，请不要重复提交.';
$_LANG['tag_cloud'] = '标签云';
$_LANG['tag_anonymous'] = '对不起，只有注册会员并且正常登录以后才能提交标记。';
$_LANG['tag_cloud_desc'] = '标签云（Tag cloud）是用以表示一个网站中的内容标签。 标签（tag、关键词）是一种更为灵活、有趣的商品分类方式，您可以为每个商品添加一个或多个标签，那么可以通过点击这个标签查看商品其他会员提交的与您的标签一样的商品,能够让您使用最快的方式查找某一个标签的所有网店商品。比方说点击“红色”这个标签，就可以打开这样的一个页面，显示所有的以“红色” 为标签的网店商品';

/* AJAX 相关 */
$_LANG['invalid_captcha'] = '对不起，您输入的验证码不正确。';
$_LANG['goods_exists'] = "对不起，您的购物车中已经存在相同的商品。";
$_LANG['fitting_goods_exists'] = "对不起，您的购物车中已经添加了该配件。";
$_LANG['invalid_number'] = '对不起，您输入了一个非法的商品数量。';
$_LANG['not_on_sale'] = '对不起，该商品已经下架。';
$_LANG['no_basic_goods'] = "对不起，您希望将该商品做为配件购买，可是购物车中还没有该商品的基本件。";
$_LANG['cannt_alone_sale'] = '对不起，该商品不能单独销售。';
$_LANG['shortage'] = "对不起，该商品已经库存不足暂停销售。\n你现在要进行缺货登记来预订该商品吗？";
$_LANG['shortage_little'] = "该商品已经库存不足。已将您的购货数量修改为 %d。\n您现在要去购物车吗？";
$_LANG['oos_tips'] = '该商品已经库存不足。您现在要进行缺货登记吗？';

$_LANG['addto_cart_success_1'] = "该商品已添加到购物车，您现在还需要继续购物吗？\n如果您希望马上结算，请点击“确定”按钮。\n如果您希望继续购物，请点击“取消”按钮。";
$_LANG['addto_cart_success_2'] = "该商品已添加到购物车，您现在还需要继续购物吗？\n如果您希望继续购物，请点击“确定”按钮。\n如果您希望马上结算，请点击“取消”按钮。";
$_LANG['no_keywords'] = "请输入搜索关键词！";

/* 分页排序 */
$_LANG['exchange_sort']['goods_id'] = '按上架时间排序';
$_LANG['exchange_sort']['exchange_integral'] = '按积分排序';
$_LANG['exchange_sort']['last_update'] = '按更新时间排序';
$_LANG['order']['DESC'] = '倒序';
$_LANG['order']['ASC'] = '正序';
$_LANG['pager_1'] = '总计 ';
$_LANG['pager_2'] = ' 个记录';
$_LANG['pager_3'] = '，共 ';
$_LANG['pager_4'] = ' 页。';
$_LANG['page_first'] = '第一页';
$_LANG['page_prev'] = '上一页';
$_LANG['page_next'] = '下一页';
$_LANG['page_last'] = '最末页';
$_LANG['btn_display'] = '显示方式';
$_LANG['page_last_new'] = '末页';

/* 投票 */
$_LANG['vote_times'] = '参与人次';
$_LANG['vote_ip_same'] = '对不起，您已经投过票了!';
$_LANG['submit_vote'] = '投票';
$_LANG['submit_reset'] = '重选';
$_LANG['vote_success'] = '恭喜你，投票成功';

/* 评论 */
$_LANG['cmt_submit_done'] = '您的评论已成功发表, 感谢您的参与!';
$_LANG['cmt_submit_wait'] = "您的评论已成功发表, 请等待管理员的审核!";
$_LANG['cmt_lang']['cmt_empty_username'] = '请输入您的用户名称';
$_LANG['cmt_lang']['cmt_empty_email'] = '请输入您的电子邮件地址';
$_LANG['cmt_lang']['cmt_error_login'] = '请您登录后再发表评论';
$_LANG['cmt_lang']['cmt_error_email'] = '电子邮件地址格式不正确';
$_LANG['cmt_lang']['cmt_empty_content'] = '您没有输入评论的内容';
$_LANG['cmt_spam_warning'] = '您至少在30秒后才可以继续发表评论!';
$_LANG['cmt_lang']['captcha_not_null'] = '验证码不能为空!';
$_LANG['cmt_lang']['cmt_invalid_comments'] = '无效的评论内容!';
$_LANG['invalid_comments'] = '无效的评论内容!';
$_LANG['error_email'] = '电子邮件地址格式不正确!';
$_LANG['admin_username'] = "管理员：";
$_LANG['reply_comment'] = '回复';
$_LANG['comment_captcha'] = '验证码';
$_LANG['comment_login'] = '只有注册会员才能发表评论，请您登录后再发表评论';
$_LANG['comment_custom'] = "评论失败。只有在本店购买过商品的注册会员才能发表评论。";
$_LANG['comment_brought'] = '评论失败。只有购买过此商品的注册用户才能评论该商品。';
$_LANG['anonymous'] = '匿名用户';

/* 其他信息 */
$_LANG['js_languages']['goodsname_not_null'] = '商品名不能为空！';

/* 商品比较 */
$_LANG['compare_remove'] = '移除';
$_LANG['compare_no_goods'] = '您没有选定任何需要比较的商品或者比较的商品数少于 2 个。';

$_LANG['no_user_name'] = '该用户名不存在';
$_LANG['undifine_rank'] = '暂无';
$_LANG['not_login'] = '您还没有登陆';
$_LANG['half_info'] = '信息不全，请填写所有信息';
$_LANG['no_id'] = '没有商品ID';
$_LANG['save_success'] = '修改成功';
$_LANG['drop_consignee_confirm'] = '您确定要删除该收货人信息吗？';

/* 夺宝奇兵 */
$_LANG['snatch_js']['price_not_null'] = '价格不能为空';
$_LANG['snatch_js']['price_not_number'] = '价格只能是数字';
$_LANG['snatch_list'] = '夺宝奇兵列表';
$_LANG['not_in_range'] = '你只能在%d到%d之间出价';
$_LANG['also_bid'] = '你已经出过价格 %s 了';
$_LANG['lack_pay_points'] = '你积分不够，不能出价';
$_LANG['snatch'] = '夺宝奇兵';
$_LANG['snatch_is_end'] = '活动已经结束';
$_LANG['snatch_start_time'] = '本次活动从 %s 到 %s 截止';
$_LANG['price_extent'] = '出价范围为';
$_LANG['user_to_use_up'] = '用户可多次出价，每次消耗';
$_LANG['snatch_victory_desc'] = '当本期活动截止时，系统将从所有竞价奖品的用户中，选出在所有竞价中出价最低、且没有其他出价与该价格重复的用户（即最低且唯一竞价），成为该款奖品的获胜者.';
$_LANG['price_less_victory'] = '如果用户获胜的价格低于';
$_LANG['price_than_victory'] = '将能按当期竞拍价购得该款奖品；如果用户获胜的价格高于';
$_LANG['or_can'] = '则能以';
$_LANG['shopping_product'] = '购买该款奖品';
$_LANG['victory_price_product'] = '获胜用户将能按当期竞拍价购得该款奖品.';
$_LANG['now_not_snatch'] = '当前没有活动';
$_LANG['my_integral'] = '我的积分';
$_LANG['bid'] = '出价';
$_LANG['me_bid'] = '我要出价';
$_LANG['me_now_bid'] = '我的出价';
$_LANG['only_price'] = '唯一价格';
$_LANG['view_snatch_result'] = '活动结果';
$_LANG['victory_user'] = '获奖用户';
$_LANG['price_bid'] = '所出价格';
$_LANG['bid_time'] = '出价时间';
$_LANG['not_victory_user'] = '没有获奖用户';
$_LANG['snatch_log'] = '参加夺宝奇兵%s ';
$_LANG['not_for_you'] = '你不是获胜者，不能购买';
$_LANG['order_placed'] = '您已经下过订单了，如果您想重新购买，请先取消原来的订单';

/* 购物流程中的前台部分 */
$_LANG['select_spe'] = '请选择商品属性';

/* 购物流程中的订单部分 */
$_LANG['price'] = '价格';
$_LANG['name'] = '名称';
$_LANG['describe'] = '描述';
$_LANG['fee'] = '费用';
$_LANG['free_money'] = '免费额度';
$_LANG['img'] = '图片';
$_LANG['no_pack'] = '不要包装';
$_LANG['no_card'] = '不要贺卡';
$_LANG['bless_note'] = '祝福语';
$_LANG['use_integral'] = '使用积分';
$_LANG['can_use_integral'] = '您当前的可用积分为';
$_LANG['noworder_can_integral'] = '本订单最多可以使用';
$_LANG['use_surplus'] = '使用余额';
$_LANG['your_surplus'] = '您当前的可用余额为';
$_LANG['pay_fee'] = '支付手续费';
$_LANG['insure_fee'] = '保价费用';
$_LANG['need_insure'] = '配送是否需要保价';
$_LANG['cod'] = '配送决定';

$_LANG['curr_stauts'] = '当前状态';
$_LANG['use_bonus'] = '使用红包';
$_LANG['use_value_card'] = '使用储值卡';
$_LANG['value_card_dis'] = '储值卡折扣';
$_LANG['use_bonus_kill'] = '使用线下红包';
$_LANG['invoice'] = '开发票';
$_LANG['invoice_type'] = '发票类型';
$_LANG['invoice_title'] = '发票抬头';
$_LANG['invoice_content'] = '发票内容';
$_LANG['order_postscript'] = '订单附言';
$_LANG['booking_process'] = '缺货处理';
$_LANG['complete_acquisition'] = '该订单完成后，您将获得';
$_LANG['with_price'] = '以及价值';
$_LANG['de'] = '的';
$_LANG['bonus'] = '红包';
$_LANG['goods_all_price'] = '商品总价';
$_LANG['dis_amount'] = '商品优惠';
$_LANG['discount'] = '折扣';
$_LANG['tax'] = '发票税额';
$_LANG['shipping_fee'] = '配送费用';
$_LANG['pack_fee'] = '包装费用';
$_LANG['card_fee'] = '贺卡费用';
$_LANG['total_fee'] = '应付款金额';
$_LANG['self_site'] = '本站';
$_LANG['order_gift_integral'] = '订单 %s 赠送的积分';

/* 缺货处理 */
$_LANG['oos'][OOS_WAIT] = '等待所有商品备齐后再发';
$_LANG['oos'][OOS_CANCEL] = '取消订单';
$_LANG['oos'][OOS_CONSULT] = '与店主协商';

/* 评论部分 */
$_LANG['username'] = '用户名';
$_LANG['email'] = '电子邮件地址';
$_LANG['comment_rank'] = '评价等级';
$_LANG['comment_content'] = '评论内容';
$_LANG['submit_comment'] = '提交评论';
$_LANG['button_reset'] = '重置表单';
$_LANG['goods_comment'] = '商品评论';
$_LANG['article_comment'] = '文章评论';

/* 支付确认部分 */
$_LANG['pay_status'] = '支付状态';
$_LANG['pay_not_exist'] = '此支付方式不存在或者参数错误！';
$_LANG['pay_disabled'] = '此支付方式还没有被启用！';
$_LANG['pay_success'] = '您此次的支付操作已成功！';
$_LANG['pay_fail'] = '支付操作失败，请返回重试！';

/* 文章部分 */
$_LANG['new_article'] = '最新文章';
$_LANG['shop_notice'] = '商店公告';
$_LANG['order_already_received'] = "此订单已经确认过了，感谢您在本站购物，欢迎再次光临。";
$_LANG['order_invalid'] = '您提交的订单不正确。';
$_LANG['act_ok'] = "谢谢您通知我们您已收到货，感谢您在本站购物，欢迎再次光临。";
$_LANG['receive'] = '收货确认';
$_LANG['buyer'] = '买家';
$_LANG['next_article'] = '下一篇';
$_LANG['prev_article'] = '上一篇';

/* 虚拟商品 */
$_LANG['virtual_goods_ship_fail'] = '部分商品自动发货失败，请尽快联系商家重新发货';

/* 选购中心 */
$_LANG['pick_out'] = '选购中心';
$_LANG['fit_count'] = "共有 %s 件商品符合条件";
$_LANG['goods_type'] = "商品类型";
$_LANG['remove_all'] = '移除所有';
$_LANG['advanced_search'] = '高级搜索';
$_LANG['activity'] = '本商品正在进行';
$_LANG['order_not_exists'] = "非常抱歉，没有找到指定的订单。请和网站管理员联系。";

$_LANG['promotion_time'] = '的时间为%s到%s，赶快来抢吧！';

/* 倒计时 */
$_LANG['goods_js']['day'] = '天';
$_LANG['goods_js']['hour'] = '小时';
$_LANG['goods_js']['minute'] = '分钟';
$_LANG['goods_js']['second'] = '秒';
$_LANG['goods_js']['end'] = '结束';

/*商品语言JS start*/
$_LANG['goods_js']['goods_attr_js'] = '请选择商品规格类型';
/*商品语言JS end*/

$_LANG['favourable'] = '优惠活动';

/* 团购部分语言项 */
$_LANG['group_buy'] = '团购活动';
$_LANG['group_buy_goods'] = '团购商品';
$_LANG['gb_goods_name'] = '团购商品：';
$_LANG['gb_start_date'] = '开始时间：';
$_LANG['gb_end_date'] = '结束时间：';
$_LANG['gbs_pre_start'] = '该团购活动尚未开始，请继续关注。';
$_LANG['gbs_under_way'] = '该团购活动正在火热进行中，距离结束时间还有：';
$_LANG['gbs_finished'] = '该团购活动已结束，正在等待处理...';
$_LANG['gbs_succeed'] = '该团购活动已成功结束！';
$_LANG['gbs_fail'] = '该团购活动已结束，没有成功。';
$_LANG['gb_price_ladder'] = '价格阶梯：';
$_LANG['gb_ladder_amount'] = '数量';
$_LANG['gb_ladder_price'] = '价格';
$_LANG['gb_deposit'] = '保证金：';
$_LANG['gb_restrict_amount'] = '限购数量：';
$_LANG['gb_limited'] = '限购';
$_LANG['gb_gift_integral'] = '赠送积分：';
$_LANG['gb_cur_price'] = '当前价格：';
$_LANG['gb_valid_goods'] = '当前定购数量：';
$_LANG['gb_final_price'] = '成交价格：';
$_LANG['gb_final_amount'] = '成交数量：';
$_LANG['gb_notice_login'] = '提示：您需要先注册成为本站会员并且登录后，才能参加商品团购!';
$_LANG['gb_error_goods_lacking'] = '对不起，商品库存不足，请您修改数量！';
$_LANG['gb_error_restrict_amount'] = '对不起，您购买的商品数量已达到限购数量！';
$_LANG['gb_error_status'] = '对不起，该团购活动已经结束或尚未开始，现在不能参加！';
$_LANG['gb_error_login'] = '对不起，您没有登录，不能参加团购，请您先登录！';
$_LANG['group_goods_empty'] = '当前没有团购活动';

/* 预售部分语言项 */
$_LANG['presale_error_status'] = '对不起，该预售活动已经结束或尚未开始，现在不能参加！';
$_LANG['back_to_presale'] = '返回预售商品';

/* 拍卖部分语言项 */
$_LANG['auction'] = '拍卖活动';
$_LANG['auction_list'] = '拍卖活动列表';
$_LANG['act_status'] = '活动状态';
$_LANG['au_start_price'] = '起拍价';
$_LANG['au_start_price_two'] = '起 拍 价';
$_LANG['au_end_price'] = '一口价';
$_LANG['au_end_price_two'] = '一 口 价';
$_LANG['au_amplitude'] = '加价幅度';
$_LANG['au_deposit'] = '保证金';
$_LANG['au_deposit_two'] = '保 证 金';
$_LANG['no_auction'] = '当前没有拍卖活动';
$_LANG['au_pre_start'] = '该拍卖活动尚未开始';
$_LANG['au_under_way'] = '该拍卖活动正在进行中，距离结束时间还有：';
$_LANG['au_under_way_1'] = '该拍卖活动正在进行中';
$_LANG['au_bid_user_count'] = '已出价人数';
$_LANG['au_last_bid_price'] = '最后出价';
$_LANG['au_last_bid_user'] = '最后出价的买家';
$_LANG['au_last_bid_time'] = '最后出价时间';
$_LANG['au_finished'] = '该拍卖活动已结束';
$_LANG['au_bid_user'] = '买家';
$_LANG['au_bid_price'] = '出价';
$_LANG['au_bid_time'] = '时间';
$_LANG['au_bid_status'] = '状态';
$_LANG['no_bid_log'] = '暂时没有买家出价';
$_LANG['au_bid_ok'] = '领先';
$_LANG['out'] = '出局';
$_LANG['au_i_want_bid'] = '我要出价';
$_LANG['nin_want_bid'] = '您的出价';
$_LANG['button_bid'] = '出价';
$_LANG['button_buy'] = '立即购买';
$_LANG['au_not_under_way'] = '拍卖活动已结束，不能再出价了';
$_LANG['au_bid_price_error'] = '请输入正确的价格';
$_LANG['au_bid_after_login'] = '您只有注册成为会员并且登录之后才能出价';
$_LANG['au_bid_repeat_user'] = '您已经是这个商品的最高出价人了!';
$_LANG['au_your_lowest_price'] = '您的出价不能低于 %s';
$_LANG['au_your_lowest_price_wen'] ='您的出价不能低于 ¥';
$_LANG['au_user_money_short'] = '您的可用资金不足，请先到用户中心充值!';
$_LANG['au_unfreeze_deposit'] = '解冻拍卖活动的保证金：%s';
$_LANG['au_freeze_deposit'] = '冻结拍卖活动的保证金：%s';
$_LANG['au_not_finished'] = '该拍卖活动尚未结束，不能购买';
$_LANG['au_order_placed'] = '您已经下过订单了，如果您想重新购买，请先取消原来的订单';
$_LANG['au_no_bid'] = '该拍卖活动没有人出价，不能购买';
$_LANG['au_final_bid_not_you'] = '您不是最高出价者，不能购买';
$_LANG['au_buy_after_login'] = '请您先登录';
$_LANG['au_is_winner'] = '恭喜您！您已经赢得了该商品的购买权.您可以点击购买按钮将宝贝买回家。';
$_LANG['au_mechanism'] = '拍卖机构';
$_LANG['bidding_process'] = '竞拍流程';
$_LANG['process_step_one'] = '用户账号当前余额不低于保证金金额';
$_LANG['process_step_two'] = '保证竞拍结束时出价最高，获得竞拍商品';
$_LANG['process_step_three'] = '填写订单信息，提交订单';
$_LANG['process_step_four'] = '支付已提交的订单';
$_LANG['process_step_five'] = '支付成功后等待收货，竞拍完成';
$_LANG['auction_desc'] = '竞拍详情';
$_LANG['au_introduce'] = '拍卖介绍';
$_LANG['au_raiders'] = '竞拍攻略';
$_LANG['au_ren'] = '竞拍人';
$_LANG['rec_au'] = '推荐拍品';
$_LANG['bid_number'] = '出价人次';
$_LANG['au_end'] = '竞拍结束';
$_LANG['more'] = '更多';

/* 批发部分语言项 */
$_LANG['ws_user_rank'] = '您的等级暂时无法查看批发方案';
$_LANG['ws_login_please'] = '请您先登录';
$_LANG['ws_return_home'] = '返回首页';
$_LANG['ws_return_wholesale'] = '返回批发市场';
$_LANG['wholesale'] = '批发';
$_LANG['no_wholesale'] = '没有批发商品';
$_LANG['ws_price'] = '批发价';
$_LANG['ws_subtotal'] = '小计';
$_LANG['ws_invalid_goods_number'] = '请输入正确的数量';
$_LANG['ws_attr_not_matching'] = '您选择的商品属性不存在，请参照批发价格单选择';
$_LANG['ws_goods_number_not_enough'] = '您购买的数量没有达到批发的最小数量，请参照批发价格单';
$_LANG['ws_goods_attr_exists'] = "该商品已经在购物车中，不能再次加入";
$_LANG['ws_remark'] = '请输入您的联系方式、付款方式和配送方式等信息';
$_LANG['ws_order_submitted'] = '您的订单已提交成功，请记住您的订单号: %s。';
$_LANG['ws_price_list'] = '价格单';
$_LANG['give_integral'] = '商品赠送积分';

/* 积分兑换部分语言项 */
$_LANG['exchange'] = '积分商城';
$_LANG['exchange_integral'] = '消耗积分：';
$_LANG['exchange_goods'] = '立刻兑换';
$_LANG['eg_error_login'] = '对不起，您没有登录，不能参加兑换，请您先登录！';
$_LANG['eg_error_status'] = '对不起，该商品已经取消，现在不能兑换！';
$_LANG['eg_error_integral'] = '对不起，您现有的积分值不够兑换本商品！';
$_LANG['notice_eg_integral'] = '积分商城商品需要消耗积分：';
$_LANG['eg_error_number'] = '对不起，该商品库存不足，现在不能兑换！';

/* 会员登录注册 */
$_LANG['member_name'] = '会员';
$_LANG['password'] = '密码';
$_LANG['confirm_password'] = '确认密码';
$_LANG['sign_up'] = '注册新会员';
$_LANG['forgot_password'] = '您忘记密码了吗？';
$_LANG['hello'] = '您好';
$_LANG['welcome_return'] = '欢迎您回来';
$_LANG['now_account'] = '您的账户中现在有';
$_LANG['balance'] = '余额';
$_LANG['along_with'] = '以及';
$_LANG['preferential'] = '优惠券';
$_LANG['edit_user_info'] = '进入用户中心';
$_LANG['logout'] = '退出';
$_LANG['user_logout'] = '退出';
$_LANG['welcome'] = "欢迎光临本店";
$_LANG['user_login'] = '会员登陆';
$_LANG['login_now'] = '立即登陆';
$_LANG['reg_now'] = '立即注册';

/* 商品品牌页 */
$_LANG['official_site'] = '官方网站：';
$_LANG['brand_category'] = '分类浏览：';
$_LANG['all_category'] = '所有分类';

/* 商品分类页 */
$_LANG['goods_filter'] = '商品筛选';

/* cls_image类的语言项 */
$_LANG['directory_readonly'] = '目录 % 不存在或不可写';
$_LANG['invalid_upload_image_type'] = '不是允许的图片格式';
$_LANG['upload_failure'] = '文件 %s 上传失败。';
$_LANG['missing_gd'] = '没有安装GD库';
$_LANG['missing_orgin_image'] = '找不到原始图片 %s ';
$_LANG['nonsupport_type'] = '不支持该图像格式 %s ';
$_LANG['creating_failure'] = '创建图片失败';
$_LANG['writting_failure'] = '图片写入失败';
$_LANG['empty_watermark'] = '水印文件参数不能为空';
$_LANG['missing_watermark'] = '找不到水印文件%s';
$_LANG['create_watermark_res'] = '创建水印图片资源失败。水印图片类型为%s';
$_LANG['create_origin_image_res'] = '创建原始图片资源失败，原始图片类型%s';
$_LANG['invalid_image_type'] = '无法识别水印图片 %s ';
$_LANG['file_unavailable'] = '文件 %s 不存在或不可读';

/* 邮件发送错误信息 */
$_LANG['smtp_setting_error'] = '邮件服务器设置信息不完整';
$_LANG['smtp_connect_failure'] = '无法连接到邮件服务器 %s';
$_LANG['smtp_login_failure'] = '邮件服务器验证帐号或密码不正确';
$_LANG['smtp_refuse'] = '服务器拒绝发送该邮件';
$_LANG['sendemail_false'] = "邮件发送失败，请与网站管理员联系！";
$_LANG['disabled_fsockopen'] = 'fsockopen函数被禁用';

$_LANG['topic_goods_empty'] = '当前没有专题商品';
$_LANG['email_list_ok'] = '订阅';
$_LANG['email_list_cancel'] = '退订';
$_LANG['email_invalid'] = '邮件地址非法！';
$_LANG['email_alreadyin_list'] = '邮件地址已经存在于列表中！';
$_LANG['email_notin_list'] = '邮件地址不在列表中！';
$_LANG['email_re_check'] = '已经重新发送验证邮件，请查收并确认！';
$_LANG['email_check'] = '请查收邮件进行确认操作！';
$_LANG['email_not_alive'] = '此邮件地址是未验证状态，不需要退订！';
$_LANG['check_mail'] = '验证邮件';
$_LANG['check_mail_content'] = "%s 您好：<br><br>这是由%s发送的邮件订阅验证邮件,点击以下的链接地址,完成验证操作。<br><a href=\"%s\" target=\"_blank\">%s</a>\n<br><br>%s<br>%s";
$_LANG['email_checked'] = '邮件已经被确认！';
$_LANG['hash_wrong'] = '验证串错误！请核对验证串或输入email地址重新发送验证串！';
$_LANG['email_canceled'] = '邮件已经被退定！';
$_LANG['goods_click_count'] = '商品点击数';
$_LANG['p_y']['link_start'] = '<a href="http://www.ecmoban.com" target="_blank" style=" font-family:Verdana; font-size:11px;">';
$_LANG['p_y']['link_p'] = 'Powe';
$_LANG['p_y']['link_r'] = 'red&nbsp;';
$_LANG['p_y']['link_b'] = 'by&nbsp;';
$_LANG['p_y']['main_start'] = '<strong><span style="color: #3366FF">';
$_LANG['p_y']['main_e'] = 'E';
$_LANG['p_y']['main_c'] = 'CSho';
$_LANG['p_y']['main_p'] = 'p</span>&nbsp;';
$_LANG['p_y']['v_s'] = '<span style="color: #FF9966">';
$_LANG['p_y']['v'] = VERSION;
$_LANG['p_y']['link_end'] = '</span></strong></a>&nbsp;';

/* 虚拟卡 */
$_LANG['card_sn'] = '卡片序号';
$_LANG['card_password'] = '卡片密码';
$_LANG['end_date'] = '截至日期';
$_LANG['virtual_card_oos'] = '虚拟卡已缺货';

/* 订单状态查询 */
$_LANG['invalid_order_sn'] = '无效订单号';
$_LANG['order_status'] = '订单状态';
$_LANG['shipping_date'] = '发货时间';
$_LANG['query_order'] = '查询该订单号';
$_LANG['order_query_toofast'] = '您的提交频率太高，歇会儿再查吧。';

$_LANG['online_info'] = '当前共有 %s 人在线';

/* 按钮 */
$_LANG['btn_direct_buy'] = '直接购买';
$_LANG['btn_buy'] = '购买';
$_LANG['btn_collect'] = '收藏';
$_LANG['btn_store_pick'] = '到店取货';
$_LANG['btn_add_to_cart'] = "加入购物车";
$_LANG['btn_add_to_collect'] = '添加收藏';

$_LANG['stock_up'] = '缺货';


$_LANG['hot_search'] = '热门搜索';

$_LANG['please_select_attr'] = "你加入购物车的商品有不同型号可选，你是否要立即跳转到商品详情选择型号？";

/* 促销信息栏 */
$_LANG['snatch_promotion'] = '[夺宝]';
$_LANG['group_promotion'] = '[团购]';
$_LANG['auction_promotion'] = '[拍卖]';
$_LANG['favourable_promotion'] = '[优惠]';
$_LANG['wholesale_promotion'] = '[批发]';
$_LANG['package_promotion'] = '[礼包]';

/* feed推送 */
$_LANG['feed_user_buy'] = "购买了";
$_LANG['feed_user_comment'] = "评论了";
$_LANG['feed_goods_price'] = "商品价格";
$_LANG['feed_goods_desc'] = "商品描述";

/* 留言板 */
$_LANG['shopman_comment'] = '商品评论';
$_LANG['message_ping'] = '评';
$_LANG['message_board'] = "留言板";
$_LANG['post_message'] = "我要留言";
$_LANG['message_title'] = '主题';
$_LANG['message_time'] = '留言时间';
$_LANG['reply_time'] = '回复时间';
$_LANG['reply_browse'] = '回复/浏览';
$_LANG['shop_owner_reply'] = '店主回复';
$_LANG['message_board_type'] = '留言类型';
$_LANG['content'] = '内容';
$_LANG['message_content'] = '留言内容';
$_LANG['message_anonymous'] = '匿名留言';
$_LANG['message_type'][M_MESSAGE] = '留言';
$_LANG['message_type'][M_COMPLAINT] = '投诉';
$_LANG['message_type'][M_ENQUIRY] = '询问';
$_LANG['message_type'][M_CUSTOME] = '售后';
$_LANG['message_type'][M_BUY] = '求购';
$_LANG['message_type'][M_BUSINESS] = '商家留言';
$_LANG['message_type'][M_COMMENT] = '评论';
$_LANG['message_board_js']['msg_empty_email'] = '请输入您的电子邮件地址';
$_LANG['message_board_js']['msg_error_email'] = '电子邮件地址格式不正确';
$_LANG['message_board_js']['msg_title_empty'] = '留言标题为空';
$_LANG['message_board_js']['msg_content_empty'] = '留言内容为空';
$_LANG['message_board_js']['msg_captcha_empty'] = '验证码为空';
$_LANG['message_board_js']['msg_title_limit'] = '留言标题不能超过200个字';
$_LANG['message_submit_wait'] = '您的留言已成功发表, 请等待管理员的审核!';
$_LANG['message_submit_done'] = '发表留言成功';
$_LANG['message_board_close'] = "暂停留言板功能";
$_LANG['upload_file_limit'] = '文件大小超过了限制 %dKB';
$_LANG['message_list_lnk'] = '返回留言列表';

/* 报价单 */
$_LANG['quotation'] = "报价单";
$_LANG['print_quotation'] = "打印报价单";
$_LANG['goods_inventory'] = "库存";
$_LANG['goods_category'] = "商品分类";
$_LANG['shopman_reply'] = '管理员回复';
$_LANG['specifications'] = '规格';

/* 相册JS语言项 */
$_LANG['gallery_js']['close_window'] = '您是否关闭当前窗口';
$_LANG['submit'] = '提 交';
$_LANG['reset'] = '重 置';
$_LANG['order_query'] = '订单查询';
$_LANG['shipping_query'] = '发货查询';
$_LANG['view_history'] = '浏览历史';
$_LANG['clear_history'] = '[清空]';
$_LANG['no_history'] = '您已清空最近浏览过的商品';
$_LANG['goods_tag'] = '商品标签';
$_LANG['releate_goods'] = "用户还喜欢";
$_LANG['goods_list'] = '商品列表';
$_LANG['favourable_goods'] = '收藏该商品';
$_LANG['accessories_releate'] = '相关配件';
$_LANG['article_releate'] = '相关文章';
$_LANG['email_subscribe'] = '邮件订阅';
$_LANG['consignee_info'] = '收货人信息';
$_LANG['user_comment'] = '用户评论';
$_LANG['total'] = '共';
$_LANG['user_comment_num'] = '条评论';
$_LANG['auction_goods'] = '拍卖商品';
$_LANG['auction_goods_info'] = '拍卖商品详情';
$_LANG['article_cat'] = '文章分类';
$_LANG['online_vote'] = '在线调查';
$_LANG['new_price'] = '最新出价';
$_LANG['promotion_info'] = '促销信息';
$_LANG['price_grade'] = '价格范围';
$_LANG['your_choice'] = '您的选择';
$_LANG['system_info'] = '系统信息';
$_LANG['all_tags'] = '所有标签';
$_LANG['activity_list'] = '活动列表';
$_LANG['package_list'] = '礼包列表';
$_LANG['treasure_info'] = '宝贝详情';
$_LANG['activity_desc'] = '活动描述';
$_LANG['activity_intro'] = '活动介绍';
$_LANG['get_password'] = '找回密码';
$_LANG['fee_total'] = '费用总计';
$_LANG['other_info'] = '其它信息';
$_LANG['user_balance'] = '会员余额';
$_LANG['wholesale_goods_cart'] = "批发商品购物车";
$_LANG['wholesale_goods_list'] = '批发商品列表';
$_LANG['bid_record'] = '出价记录';
$_LANG['shipping_method'] = '配送方式';
$_LANG['payment_method'] = '支付方式';
$_LANG['goods_package'] = '商品包装';
$_LANG['goods_card'] = '祝福贺卡';
$_LANG['groupbuy_intro'] = '团购说明';
$_LANG['groupbuy_goods_info'] = '团购商品详情';
$_LANG['act_time'] = '起止时间';
$_LANG['top10'] = '销售排行';
$_LANG['service_guarantee'] = '服务保障';

/* 优惠活动 */
$_LANG['label_act_name'] = '优惠活动名称：';
$_LANG['label_start_time'] = '优惠开始时间：';
$_LANG['label_end_time'] = '优惠结束时间：';
$_LANG['label_user_rank'] = '享受优惠的会员等级：';
$_LANG['not_user'] = '非会员';
$_LANG['label_act_range'] = '优惠范围：';
$_LANG['far_all'] = '全部商品';
$_LANG['far_category'] = '以下分类';
$_LANG['far_brand'] = '以下品牌';
$_LANG['far_goods'] = '以下商品';
$_LANG['label_min_amount'] = '金额下限：';
$_LANG['label_max_amount'] = '金额上限：';
$_LANG['notice_max_amount'] = '0表示没有上限';
$_LANG['label_act_type'] = '优惠方式：';
$_LANG['fat_goods'] = '享受赠品';
$_LANG['fat_price'] = '现金减免';
$_LANG['fat_discount'] = '享受折扣';
$_LANG['orgtotal'] = '原始价格';
$_LANG['heart_buy'] = '心动不如行动';
$_LANG['activity_txt'] = '活动';
$_LANG['activity_time'] = '活动时间';

/* 其他模板涉及常用语言项 */
$_LANG['label_regist'] = '用户注册';
$_LANG['label_login'] = '用户登录';
$_LANG['label_profile'] = '用户信息';
$_LANG['label_collection'] = '我的收藏';
$_LANG['article_list'] = '文章列表';
$_LANG['preferences_price'] = '优惠价格';
$_LANG['divided_into'] = '分成规则';

//店铺街by wang
$_LANG['store_street'] = '店铺街';

$_LANG['no_goods_in_cart'] = "您的购物车中没有商品！";
$_LANG['no_consignee'] = "请填写您的收货地址";
$_LANG['over_bind_limit'] = "您即将绑定的储值卡已超出该类卡可绑定上限！";

//瀑布流加载分类商品 by wu
$_LANG['add_to_cart'] = '加入购物车';
$_LANG['sales_volume'] = '销量';
$_LANG['compare'] = '对比';
$_LANG['collect'] = '收藏';
$_LANG['have_no_goods'] = '暂时缺货';
$_LANG['select_attr'] = '请选择属性';
$_LANG['order_wholesale_sms'] = '订单批发信息';

/* 众筹title */
$_LANG['crowdfunding'] = '众筹项目';
$_LANG['zc_search'] = '搜索众筹项目';
$_LANG['zc_order_info'] = '订单信息';
$_LANG['zc_order_submit'] = '订单提交';

/*门店 by kong 20160722*/
$_LANG['store_shortage'] = "对不起，该商品门店库存不足，请选择其他门店";


/*页面底部  by kong haojlj*/
$_LANG['Authentic_guarantee'] = "正品保障";
$_LANG['Rave_reviews'] = "好评如潮";
$_LANG['7_days_return'] = '七天包退';
$_LANG['Lightning_delivery'] = '闪电发货';
$_LANG['Authority_of_honor'] = '权威荣誉';
$_LANG['Coupon_redemption_succeed'] = "领取成功！感谢您的参与，祝您购物愉快~";
$_LANG['coupons_prompt'] = "本活动为概率性事件，不能保证所有客户成功领取优惠券";
$_LANG['Site_navigation'] = "网站导航";
$_LANG['seller_store']  = '店铺';
$_LANG['all_goods_cat'] = "全部商品分类";
$_LANG['Good_coupon_market'] = "好券集市";
$_LANG['task_mart'] = "任务集市";
$_LANG['presell_cat'] = "全部预售分类";
$_LANG['New_product_release'] = "新品发布";
$_LANG['First_order'] = "抢先定";
$_LANG['piece_total'] = "件共计";
$_LANG['go_to_cart'] = "去购物车";
$_LANG['Welcome_to'] = "欢迎来到";
$_LANG['please_login'] = "请登录";
$_LANG['registered'] = "登录";
$_LANG['download_prompt'] = "下载文件不存在！";
$_LANG['flagship_store'] = '旗舰店';
$_LANG['exclusive_shop'] = '专卖店';
$_LANG['franchised_store'] = '专营店';
$_LANG['week'][1] = '星期一';
$_LANG['week'][2] = '星期二';
$_LANG['week'][3] = '星期三';
$_LANG['week'][4] = '星期四';
$_LANG['week'][5] = '星期五';
$_LANG['week'][6] = '星期六';
$_LANG['week'][7] = '星期日';
$_LANG['table_prompt'] = "表名称或表字段名称不能为空";
$_LANG['presell'] = "预售";
$_LANG['gift_card_exchange'] = "礼品卡兑换";
$_LANG['project'] = "专题";
$_LANG['rec_txt'][1] = '普通';
$_LANG['rec_txt'][2] = '团购';
$_LANG['rec_txt'][3] = '拍卖';
$_LANG['rec_txt'][4] = '夺宝奇兵';
$_LANG['rec_txt'][5] = '积分商城';
$_LANG['rec_txt'][6] = '预售';
$_LANG['cat_list'] = '购物车';
$_LANG['my_cart'] = '我的购物车';
$_LANG['back'] = '返回';
$_LANG['Since_some'] = "市自提点";
$_LANG['implement_time'] = "执行时间：";
$_LANG['information_null'] = "抱歉，没有找到符合条件的数据";
$_LANG['Shopping_together_single'] = "购物凑单";
$_LANG['Contact_us'] = "联系我们";
$_LANG['submit_goods'] = '提交';
$_LANG['Mall_announcement'] = "商城公告";
$_LANG['System_cat'] = "系统分类";
$_LANG['Other_information'] = "其他信息";
$_LANG['Recent_popular'] = "近期热门";
$_LANG['View_larger'] = "查看大图";
$_LANG['general_audience'] = '全场通用';
$_LANG['Send_light'] = '送照明灯';
$_LANG['Jingdong_peisong'] = "京东配送";
$_LANG['Please_select'] = "请选择..";

/*20161214  start*/
$_LANG['View_details'] = "查看详情";
$_LANG['Browsing_record'] = "浏览记录";
$_LANG['default'] = "默认";
$_LANG['is_new'] = '新品';
$_LANG['is_hot'] = '热卖';
$_LANG['is_best'] = '精品';
$_LANG['Comment_number'] = "评论数";
$_LANG['clear'] = "清空";
$_LANG['Receiving_land'] = "收货地";
$_LANG['Free_shipping'] = "包邮";
$_LANG['Self_goods'] = "自营商品";
$_LANG['Grid_model'] = "网格模式";
$_LANG['List_model'] = "列表模式";
$_LANG['Recent_browse'] = "最近浏览";
$_LANG['tuiguang_goods'] = "推广商品";
$_LANG['Popular_recommendation'] = "热门推荐";
$_LANG['torob_buy'] = "去抢购";
$_LANG['brand_goods'] = "品牌商品";
$_LANG['Enter_brand_page'] = "进入品牌页";
$_LANG['Prompt'] = "提示";
$_LANG['Cancel_attention'] = "取消关注";
$_LANG['check_all'] = '全选';
$_LANG['Share'] = "共有";
$_LANG['pic_count'] = "张图片";
$_LANG['cilck_view'] = "点击查看";
$_LANG['cilck_retract'] = "点击收起";
$_LANG['View_all_replies'] = "查看全部回复";
$_LANG['Collection_success'] = "您已成功收藏该商品！";
$_LANG['Collection_see'] = "您已成功收藏该商品！";
$_LANG['remove_goods_Collection'] = "您确定要将该商品从收藏夹中删除吗？";
$_LANG['cancel_grand'] = "您确定要取消关注该品牌吗？";
$_LANG['add_address_zc'] = "添加收货地址";
$_LANG['confirm_address_zc'] = "确定收货地址";
$_LANG['Basic_info_comp'] = "基本信息对比";
$_LANG['Model'] = "型号";
$_LANG['contrast_item'] = "暂无对比项";
$_LANG['compare_click_see'] = "点击这里折叠/展开";
$_LANG['compare_one'] = "点击取消固定";
$_LANG['compare_two'] = "取消高亮显示不同项";
$_LANG['compare_three'] = "高亮显示不同项";
$_LANG['compare_four'] = "显示相同项";
$_LANG['compare_five'] = "隐藏相同项";
$_LANG['add_consignee_address'] = '新增收货人地址';
$_LANG['edit_consignee_info'] = '编辑收货人信息';
$_LANG['remove_consignee_info'] = '删除收货人信息';
$_LANG['Consignee'] = "收货人";
$_LANG['input_Consignee_name'] = "请您填写收货人姓名";
$_LANG['Local_area'] = '所在地区';
$_LANG['address_info'] = "详细信息";
$_LANG['input_address_info'] = "请您填写收货详细地址";
$_LANG['phone_con'] = "手机号码";
$_LANG['Fixed_telephone'] = "固定电话";
$_LANG['input_contact'] = "请您填写收货人联系方式";
$_LANG['con_email'] = "邮箱";
$_LANG['con_email_input'] = "请您填写邮箱";
$_LANG['con_sign_building'] = '地址别名';
$_LANG['inputcon_sign_building'] = '请您填写地址别名';
$_LANG['sign_building_desc'] = "设置一个易记的名称，如：'送到家里'、'送到公司'";
$_LANG['Zip_code'] = "邮编";
$_LANG['deliver_goods_time'] = '最佳送货时间';
$_LANG['con_Preservation'] = '保存收货人信息';
$_LANG['goods_info'] = "商品信息";
$_LANG['shop_Price_dis'] = "商城价";
$_LANG['Evaluation_points'] = "评价分";
$_LANG['tiao_dis'] = "条";
$_LANG['Hot_topic'] = "热门话题";
$_LANG['hidden'] = '隐藏';
$_LANG['Contrast_bar'] = "对比栏";
$_LANG['Continue_add_dui'] = "您还可以继续添加";
$_LANG['empty_contrast'] = '清空对比栏';
$_LANG['flow_info_lbi'] = "总价(不含运费)";
$_LANG['Selling_price'] = "售　　价";
$_LANG['selling_price_alt'] = "售价";
$_LANG['gs_number'] = '数　　量';
$_LANG['Rate'] = '好评';
$_LANG['zhong_p'] = '中评';
$_LANG['Bad'] = '差评'; 
$_LANG['share_flow'] = "分享";
$_LANG['existing'] = "已有";
$_LANG['No_goods'] = "无货";
$_LANG['Customer_service_p'] = "客服";
$_LANG['freight_p'] = '运费';
$_LANG['jian'] = "件";
$_LANG['seller_Grade']='商家等级';
$_LANG['brand_gm'] = '品&nbsp;&nbsp;&nbsp;&nbsp;牌';
$_LANG['Go_to_store'] = "进店逛逛";
$_LANG['go_see'] = "去看看";
$_LANG['Deliver_to'] = "送货至";
$_LANG['Sold'] = "售出";
$_LANG['his_bi'] = "件";
$_LANG['go_shoping'] = "去购物";
$_LANG['Flash_sale'] = "限时抢购";
$_LANG['hour_two'] = '时';
$_LANG['minutes'] = '分';
$_LANG['seconds'] = '秒';
$_LANG['Rush_once'] = "立即抢";
$_LANG['rush_renshu'] = "人已抢购";
$_LANG['Brand_Street'] = "品牌街";
$_LANG['Mall_info'] = "商城资讯";
$_LANG['people_participate'] = "人已参加";
$_LANG['zhe'] = "折";
$_LANG['Group_purchase_end'] = "团购结束";
$_LANG['Group_purchase_now'] = "立即团";
$_LANG['End_pitch'] = "距结束";
$_LANG['Wait_shooting'] = "等待开拍";
$_LANG['Exchange_price'] = "换购价";
$_LANG['combo_markPrice'] = "参考价";
$_LANG['integral'] = '积分';
$_LANG['integral_coune'] = '人兑换';
$_LANG['exchange_now'] = '立即兑换';
$_LANG['Detailed_map'] = "详细地图";
$_LANG['Track'] = "跟踪";
$_LANG['View_all_orders'] = "查看全部订单";
$_LANG['Distribution_limit'] = "商品暂时只支持配送至中国大陆地区";
$_LANG['payment_is_cod'] = '货到付款';
$_LANG['Classification_selection'] = "分类筛选";
$_LANG['successfully_added_shoping'] = "宝贝已成功添加到购物车！";
$_LANG['cart_count'] = "购物车共有";
$_LANG['Baby_total_amount'] = "宝贝总金额为";
$_LANG['cart_baby_success'] = "已成功添加到购物袋！";
$_LANG['zhong_boby'] = "种宝贝";
$_LANG['total_cart'] = '合计';
$_LANG['pay_to_cart'] = "去购物袋结算";
$_LANG['Detailed_score'] = '评分详细';
$_LANG['my_take_delivery'] = "我的提货";
$_LANG['level_pos'] = "级&nbsp;别";
$_LANG['temporary_no'] = "暂无";
$_LANG['order_list'] = '我的订单';
$_LANG['My_assets'] = "我的资产";
$_LANG['My_footprint'] = '我的足迹'; 
$_LANG['Email_subscription'] = "邮箱订阅";
$_LANG['Customer_service_center'] = "客服中心";
$_LANG['email_posi'] = "请输入您的邮箱帐号";
$_LANG['Support_project'] = "支持项目";
$_LANG['Launch'] = "发起";
$_LANG['Support'] = '支持';
$_LANG['zc_see_content'] = "点击显示更多回复内容";
$_LANG['input_number_desc'] = "还可以输入";
$_LANG['zi_zc'] = "字";
$_LANG['zhuce'] = "免费注册";
/*20161214  end*/

$_LANG['switch'] = "切换";
$_LANG['select_country'] = "选择国别";
$_LANG['different_countries'] = "配&nbsp;&nbsp;送";
$_LANG['click_countries'] = "点击切换国别";

$_LANG['by_stages'] = "分期";
$_LANG['Sun_to_single'] = "去晒单";
$_LANG['goods_null_cart'] = "购物车中还没有商品，赶紧选购吧！";
$_LANG['sort'] = '排序';
$_LANG['value_card'] = "储值卡";
$_LANG['value_card_bind'] = "储值卡绑定";
$_LANG['in_value_card_bind'] = "已绑定储值卡";
$_LANG['value_card_instructions'] = "<h3>储值卡绑定与说明</h3>
                <p>1、在右侧输入储值卡卡号密码进行绑定</p>
                <p>2、请仔细查阅储值卡使用范围</p>";
$_LANG['wu'] = "无";	
$_LANG['checked_city'] = "切换城市";
$_LANG['hot_city'] = "热门城市";
$_LANG['sufficient'] = "充足";
$_LANG['only_leave'] = "仅剩";
$_LANG['time_shop'] = "到店时间";
$_LANG['take_time_desc'] = "默认为一天后的当前时间哦";
$_LANG['store_take_mobile'] = "请输入手机号码";
$_LANG['store_take_mobile_one'] = "手机号码将获取取货码订单哦";
$_LANG['address'] = '地址';
$_LANG['sales_hotline'] = "销售热线";
$_LANG['working_time'] = "工作时间";
$_LANG['change_choice'] = "更改选择";
$_LANG['change_choice_desc'] = "该地区暂无门店或属性没有库存";
$_LANG['select_store_info'] = "选择门店";
$_LANG['wu'] = "无";	
$_LANG['all_goods'] = '所有自营商品';
$_LANG['spec_cat'] = '指定分类';
$_LANG['spec_goods'] = '指定商品';		
$_LANG['all_goods_explain'] = '限平台自营商品使用';
$_LANG['spec_cat_explain'] = '限平台指定分类使用：% ';
$_LANG['spec_goods_explain'] = '限平台指定商品使用：<a href="javascript:;" onclick="specGoods()" style="color:red;" >点此查看</a>';	

$_LANG['label_mobile'] = '手机号码';
$_LANG['get_verification_code_user'] = "获取短信校验码";
$_LANG['Un_bind'] = "解除绑定";
$_LANG['Immediately_verify'] = "立即验证";
$_LANG['package_nonumer'] = "对不起，该超值礼包已经库存不足。请选择其他礼包或者联系管理员！";
$_LANG['big_pic'] = "大图";
$_LANG['Small_pic'] = "小图";
$_LANG['pattern'] = "模式";
$_LANG['peer_comparison'] = "同行相比";
$_LANG['recommended_store'] = "推荐店铺";

$_LANG['update_Success'] = "更新成功！";
$_LANG['Submit_Success'] = "提交成功！";
$_LANG['Submit_fail'] = "提交失败，稍后在提交一次";

$_LANG['indate'] = '有效期';
$_LANG['bonus_card_number'] = "卡&nbsp;&nbsp;&nbsp;号";
$_LANG['keyong'] = "可用";
$_LANG['range_bonus'] = "范   围";
$_LANG['settled_down'] = "我要入驻";
$_LANG['settled_down_schedule_step'] = "进度查询";
$_LANG['Only_have_inventory'] = "仅显示有货";
/* 秒杀活动 */
$_LANG['merchant_rec'] = "店铺推荐";

$_LANG['self_run'] = "自营";
$_LANG['platform_self'] = '平台自营';

$_LANG['all_merchants'] = '所有店铺';
$_LANG['self_merchants'] = '自营店铺';
$_LANG['assign_merchants'] = '指定店铺';

$_LANG['overdue_login'] = '登陆过期，请重新登陆！';

$_LANG['no_address'] = '收货地址不能为空!';

//b2b
$_LANG['not_seller_user'] = '您不是商家用户，无权查看此页面!';
$_LANG['not_login_user'] = '您还没有登录，无权查看此页面!';

?>