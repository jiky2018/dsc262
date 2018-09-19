<?php

/**
 * ECSHOP 用户中心语言项
 * ============================================================================
 * * 版权所有 2005-2017 上海商创网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecmoban.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: user.php 17217 2011-01-19 06:29:08Z liubo $
*/

$_LANG['self_motion_goods'] = "自动确认收货";
$_LANG['illegal_operate'] = "非法操作";

$_LANG['progress'] = "进度";
$_LANG['use_limit'] = "限 %s 可用";
$_LANG['user_surplus_pay'] = "会员中心订单余额支付";

$_LANG['bind_login'] = '账号绑定';
$_LANG['shop_reg_closed'] = '很抱歉，目前暂不开放注册模块.';

//@author guan start
$_LANG['to_paid'] = '充值';
$_LANG['label_single_sun'] = '评价/晒单';
$_LANG['single_success'] = '您的晒单已成功提交，小编审核通过后将显示在晒单页';
$_LANG['single_list_lnk'] = '返回晒单列表页';
$_LANG['is_single'] = '此订单已经晒单请重新选择';
$_LANG['order_sun_single'] = '晒单';
$_LANG['back_page_up'] = '返回上一页';
$_LANG['back_page_list'] = '去晒单列表页';
$_LANG['invalid_img_url'] = '商品相册中图片格式不正确!';
$_LANG['invalid_goods_img'] = '商品图片格式不正确！'; 
$_LANG['invalid_goods_thumb'] = '商品缩略图格式不正确！';
$_LANG['invalid_img_url'] = '商品相册中图片格式不正确!';
$_LANG['goods_img_too_big'] = '商品图片文件太大了，无法上传。';
$_LANG['goods_thumb_too_big'] = '商品缩略图文件太大了，无法上传。';
$_LANG['img_url_too_big'] = '商品相册中第%s个图片文件太大了，无法上传。';
$_LANG['order_single'] = '订单不存在';
$_LANG['single_error'] = '上传失败，请检查填写信息是否正确';
$_LANG['comm_success'] = '您已发表，感谢您对我们的支持';
$_LANG['comm_error'] = '评论失败';
$_LANG['comm_content'] = '评论内容不能为空';
$_LANG['comm_is_user'] = '不能对自己评论';
//@author guan end

$_LANG['invalid_apply']     = '您存在未处理申请，请处理后再来吧！';

//退换货 start
$_LANG['return'] = '申请返修/退换货';
$_LANG['return_apply'] = '申请售后';
$_LANG['user_return'] ='由用户寄回';
$_LANG['get_goods'] = '退换商品已收到';
$_LANG['send_alone'] = '换货商品寄出(分单)';
$_LANG['send'] = '换货商品寄出';
$_LANG['refound'] = '已退款';
$_LANG['no_refound'] = '未退款';
$_LANG['complete'] = '完成';
$_LANG['label_return'] = '退换货订单';

$_LANG['edit_shipping_success'] = '您的退换货快递信息已成功更新！';
$_LANG['return_info'] = '返回退换货信息';
$_LANG['return_list_lnk'] = '我的退换货单列表';
$_LANG['return_exist'] = '该订单不存在！';
$_LANG['return_not_unconfirmed'] = '当前订单状态不是“用户寄回”。';
$_LANG['current_os_already_receive'] ='商家已收到退换货商品';
$_LANG['already_out_goods'] ='商家已发送图换货商品';
$_LANG['have_refound'] = "商家已退款";

$_LANG['rf'][RF_APPLICATION] = '由买家寄回';
$_LANG['rf'][RF_RECEIVE] =  '收到退换货';
$_LANG['rf'][RF_SWAPPED_OUT_SINGLE] =  '换出商品寄出【分单】';
$_LANG['rf'][RF_SWAPPED_OUT] =  '换出商品寄出';
$_LANG['rf'][RF_COMPLETE] =  '完成';   
$_LANG['rf'][REFUSE_APPLY] =  '申请被拒';
$_LANG['ff'][FF_REFOUND] = '已退款';
$_LANG['ff'][FF_NOREFOUND] = '未退款';
$_LANG['ff'][FF_NOMAINTENANCE] =  '未维修';
$_LANG['ff'][FF_MAINTENANCE] =  '已维修';
$_LANG['ff'][FF_NOEXCHANGE] =  '未换货';
$_LANG['ff'][FF_EXCHANGE] =  '已换货';
$_LANG['return_info'] = "退换货申请已提交";
$_LANG['only_return_money'] = '仅退款';
//退换货 end

//ecmoban模板堂 --zhuo start
$_LANG['cs'][OS_UNCONFIRMED] = '待确认';
$_LANG['cs'][CS_AWAIT_PAY] = '待付款';
$_LANG['cs'][CS_AWAIT_SHIP] = '待发货';
$_LANG['cs'][CS_TO_CONFIRM] = '待确认收货';
$_LANG['cs'][CS_FINISHED] = '已完成';
$_LANG['cs'][PS_PAYING] = '付款中';
$_LANG['cs'][OS_CANCELED] = '取消';
$_LANG['cs'][OS_INVALID] = '无效';
$_LANG['cs'][OS_RETURNED] = '退货';
$_LANG['cs'][OS_SHIPPED_PART] = '部分发货';
//ecmoban模板堂 --zhuo end

$_LANG['require_login'] = '非法入口。<br />必须登录才能完成操作。';

$_LANG['no_records'] = '没有记录';
$_LANG['shot_message'] = "短消息";

/* 用户菜单 */
$_LANG['label_welcome'] = "我的个人主页";
$_LANG['label_profile'] = '用户信息';
$_LANG['label_order'] = '我的订单';
$_LANG['label_address'] = '收货地址';
$_LANG['label_message'] = '我的留言';
$_LANG['label_tag'] = '我的标签';
$_LANG['label_collection'] = '我的收藏';
$_LANG['label_bonus'] = '我的红包';
$_LANG['label_coupons'] = '我的优惠券';
$_LANG['label_value_card'] = '我的储值卡';
$_LANG['label_comment'] = '我的评论';
$_LANG['label_affiliate'] = '我的推荐';
$_LANG['label_group_buy'] = '我的团购';
$_LANG['label_booking'] = '缺货登记';
$_LANG['label_user_surplus'] = '资金管理';
$_LANG['label_track_packages'] = '跟踪包裹';
$_LANG['label_transform_points'] = '积分兑换';
$_LANG['label_auction'] = '拍卖活动';
$_LANG['label_snatch'] = '夺宝奇兵';
$_LANG['label_logout'] = '退出';

/* 会员余额(预付款) */
$_LANG['add_surplus_log'] = '查看帐户明细';
$_LANG['view_application'] = '查看申请记录';
$_LANG['surplus_pro_type'] = '类型';
$_LANG['repay_money'] = '提现金额';
$_LANG['money'] = '金额';
$_LANG['points'] = '积分';
$_LANG['surplus_type_0'] = '充值';
$_LANG['surplus_type_1'] = '提现';
$_LANG['deposit_money'] = '充值金额';
$_LANG['process_notic'] = '会员备注';
$_LANG['admin_notic'] = '管理员备注';
$_LANG['submit_request'] = '提交申请';
$_LANG['process_time'] = '操作时间';
$_LANG['use_time'] = '操作时间';
$_LANG['is_paid'] = '状态';
$_LANG['is_confirmed'] = '已完成';
$_LANG['un_confirmed'] = '未确认';
$_LANG['pay'] = '付款';
$_LANG['is_cancel'] = '取消';
$_LANG['account_inc'] = '增加';
$_LANG['account_dec'] = '减少';
$_LANG['change_desc'] = '备注';
$_LANG['surplus_amount'] = '您的充值金额为：';
$_LANG['payment_name'] = '您选择的支付方式为：';
$_LANG['payment_fee'] = '支付手续费用为：';
$_LANG['payment_desc'] = '支付方式描述：';
$_LANG['current_surplus'] = '您当前的可用资金为：';
$_LANG['unit_yuan'] = '元';
$_LANG['for_free'] = '赠品免费';
$_LANG['surplus_amount_error'] = '您要申请提现的金额超过了您现有的余额，此操作将不可进行！';
$_LANG['surplus_appl_submit'] = '您的提现申请已成功提交，请等待管理员的审核！';
$_LANG['process_false'] = '此次操作失败，请返回重试！';
$_LANG['confirm_remove_account'] = '您确定要删除此条记录吗？';
$_LANG['back_page_up'] = '返回上一页';
$_LANG['back_account_log'] = '返回帐户明细列表';
$_LANG['amount_gt_zero'] = '请在“金额”栏输入大于0的数字';
$_LANG['select_payment_pls'] = '请选择支付方式';

//JS语言项
$_LANG['account_js']['surplus_amount_empty'] = '请输入您要操作的金额数量！';
$_LANG['account_js']['surplus_amount_error'] = '您输入的金额数量格式不正确！';
$_LANG['account_js']['process_desc'] = '请输入您此次操作的备注信息！';
$_LANG['account_js']['payment_empty'] = '请选择支付方式！';

/* 缺货登记 */
$_LANG['oos_booking'] = '缺货登记';
$_LANG['booking_goods_name'] = '订购商品名';
$_LANG['booking_amount'] = '订购数量';
$_LANG['booking_time'] = '登记时间';
$_LANG['process_desc'] = '处理备注';
$_LANG['describe'] = '订购描述';
$_LANG['contact_username'] = '联系人';
$_LANG['contact_phone'] = '联系电话';
$_LANG['submit_booking_goods'] = '提交缺货登记';
$_LANG['booking_success'] = '您的商品订购已经成功提交！';
$_LANG['booking_rec_exist'] = '此商品您已经进行过缺货登记了！';
$_LANG['back_booking_list'] = '返回缺货登记列表';
$_LANG['not_dispose'] = '未处理';
$_LANG['no_goods_id'] = '请指定商品ID';

//JS语言项
$_LANG['booking_js']['booking_amount_empty'] = '请输入您要订购的商品数量！';
$_LANG['booking_js']['booking_amount_error'] = '您输入的订购数量格式不正确！';
$_LANG['booking_js']['describe_empty'] = '请输入您的订购描述信息！';
$_LANG['booking_js']['contact_username_empty'] = '请输入联系人姓名！';
$_LANG['booking_js']['email_empty'] = '请输入联系人的电子邮件地址！';
$_LANG['booking_js']['email_error'] = '您输入的电子邮件地址格式不正确！';
$_LANG['booking_js']['contact_phone_empty'] = '请输入联系人的电话！';

/* 个人资料 */
$_LANG['confirm_submit'] = '　确 定　';
$_LANG['member_rank'] = '会员等级';
$_LANG['member_discount'] = '会员折扣';
$_LANG['rank_integral'] = '等级积分';
$_LANG['consume_integral'] = '消费积分';
$_LANG['account_balance'] = '账户余额';
$_LANG['user_bonus'] = '用户红包';
$_LANG['user_bonus_info'] = '共计 %d 个,价值 %s';
$_LANG['not_bonus'] = '没有红包';
$_LANG['add_user_bonus'] = '添加一个红包';
$_LANG['bonus_number'] = '红包序列号';
$_LANG['old_password'] = '原密码';
$_LANG['new_password'] = '新密码';
$_LANG['confirm_password'] = '确认密码';
$_LANG['bonus_sn_exist'] = '此红包号码已经被占用了！';
$_LANG['bonus_sn_not_exist'] = '此红包号码不存在！';
$_LANG['add_bonus_sucess'] = '添加新的红包操作成功！';
$_LANG['add_bonus_false'] = '添加新的红包操作失败！';
$_LANG['not_login'] = '用户未登录。无法完成操作';
$_LANG['profile_lnk'] = '查看我的个人资料';
$_LANG['edit_email_failed'] = '编辑电子邮件地址失败！';
$_LANG['edit_profile_success'] = '您的个人资料已经成功修改！';
$_LANG['edit_profile_failed'] = '修改个人资料操作失败！';
$_LANG['oldpassword_error'] = '您输入的旧密码有误!请确认再后输入！';

//JS语言项
$_LANG['profile_js']['bonus_sn_empty'] = '请输入您要添加的红包号码！';
$_LANG['profile_js']['bonus_sn_error'] = '您输入的红包号码格式不正确！';

$_LANG['profile_js']['email_empty'] = '请输入您的电子邮件地址！';
$_LANG['profile_js']['email_error'] = '您输入的电子邮件地址格式不正确！';
$_LANG['profile_js']['old_password_empty'] = '请输入您的原密码！';
$_LANG['profile_js']['new_password_empty'] = '请输入您的新密码！';
$_LANG['profile_js']['confirm_password_empty'] = '请输入您的确认密码！';
$_LANG['profile_js']['both_password_error'] = '您现两次输入的密码不一致！';
$_LANG['profile_js']['msg_blank'] = '不能为空';
$_LANG['profile_js']['no_select_question'] = '- 您没有完成密码提示问题的操作';
$_LANG['profile_js']['phone_empty'] = '请输入您的手机号！';
$_LANG['profile_js']['phone_error'] = '您输入的手机号格式不正确！';

/* 支付方式 */
$_LANG['pay_name'] = '名称';
$_LANG['pay_desc'] = '描述';
$_LANG['pay_fee'] = '手续费';

/* 收货地址 */
$_LANG['consignee_name'] = '收货人姓名';
$_LANG['country_province'] = '配送区域';
$_LANG['please_select'] = '请选择';
$_LANG['city_district'] = '城市/地区';
$_LANG['email_address'] = '电子邮件地址';
$_LANG['detailed_address'] = '详细地址';
$_LANG['postalcode'] = '邮政编码';
$_LANG['phone'] = '电话';
$_LANG['mobile'] = '手机';
$_LANG['backup_phone'] = '手机';
$_LANG['sign_building'] = '地址别名';
$_LANG['deliver_goods_time'] = '最佳送货时间';
$_LANG['default'] = '默认';
$_LANG['default_address'] = '默认地址';
$_LANG['yes'] = '是';
$_LANG['no'] = '否';
$_LANG['country'] = '国家';
$_LANG['province'] = '省份';
$_LANG['city'] = '城市';
$_LANG['area'] = '所在区域';

$_LANG['search_ship'] = '查看支持的配送方式';

$_LANG['del_address_false'] = '删除收货地址失败！';
$_LANG['add_address_success'] = '添加新地址成功！';
$_LANG['edit_address_success'] = '您的收货地址信息已成功更新！';
$_LANG['address_list_lnk'] = '返回地址列表';
$_LANG['add_address'] = '新增收货地址';
$_LANG['confirm_edit'] = '确认修改';

$_LANG['confirm_drop_address'] = '你确认要删除该收货地址吗？';

/* 会员密码找回 */
$_LANG['username_and_email'] = '请输入您注册的用户名和注册时填写的电子邮件地址。';
$_LANG['enter_new_password'] = '请输入您的新密码';
$_LANG['username_no_email'] = '您填写的用户名与电子邮件地址不匹配，请重新输入！';
$_LANG['fail_send_password'] = '发送邮件出错，请与管理员联系！';
$_LANG['send_success'] = '重置密码的邮件已经发到您的邮箱：';
$_LANG['parm_error'] = '参数错误，请返回！';
$_LANG['edit_password_failure'] = '您输入的原密码不正确！';
$_LANG['edit_password_success'] = '您的新密码已设置成功！';
$_LANG['username_not_match_email'] = '用户名与电子邮件地址不匹配，请重新输入！';
$_LANG['get_question_username'] = '请输入您注册的用户名以取得您的密码提示问题。';
$_LANG['no_passwd_question'] = '您没有设置密码提示问题，无法通过这种方式找回密码。';
$_LANG['input_answer'] = '请根据您注册时设置的密码问题输入设置的答案';
$_LANG['wrong_passwd_answer'] = '密码答案与用户名，提示问题不匹配，请重新输入！';

//JS语言项
$_LANG['password_js']['user_name_empty'] = '请输入您的用户名！';
$_LANG['password_js']['email_address_empty'] = '请输入您的电子邮件地址！';
$_LANG['password_js']['phone_address_empty'] = '请输入您的手机号！';
$_LANG['password_js']['phone_address_empty_11'] = '请输入正确11位手机号码！';
$_LANG['password_js']['phone_address_empty_bzq'] = '您输入的手机号码不正确！';
$_LANG['password_js']['wenti_address_empty'] = '请输入您的注册问题！';
$_LANG['password_js']['email_address_error'] = '您输入的电子邮件地址格式不正确！';
$_LANG['password_js']['new_password_empty'] = '请输入您的新密码！';
$_LANG['password_js']['confirm_password_empty'] = '请输入您的确认密码！';
$_LANG['password_js']['both_password_error'] = '您两次输入的密码不一致！';

/* 会员留言 */
$_LANG['message_title'] = '主题';
$_LANG['message_time'] = '留言时间';
$_LANG['reply_time'] = '回复时间';
$_LANG['shopman_reply'] = '店主回复';
$_LANG['send_message'] = '发表留言';
$_LANG['message_type'] = '留言类型';
$_LANG['message_content'] = '留言内容';
$_LANG['submit_message'] = '提交留言';
$_LANG['upload_img'] = '上传文件';
$_LANG['img_name'] = '图片名称';

/* 留言类型 */
$_LANG['type'][M_MESSAGE] = '留言';
$_LANG['type'][M_COMPLAINT] = '投诉';
$_LANG['type'][M_ENQUIRY] = '询问';
$_LANG['type'][M_CUSTOME] = '售后';
$_LANG['type'][M_BUY] = '求购';
$_LANG['type'][M_BUSINESS] = '商家留言';

$_LANG['add_message_success'] = '发表留言成功';
$_LANG['message_list_lnk'] = '返回留言列表';
$_LANG['msg_title_empty'] = '留言标题为空';
$_LANG['upload_file_limit'] = '文件大小超过了限制 %dKB';

$_LANG['img_type_tips'] = '<font color="red">小提示：</font>';
$_LANG['img_type_list'] = '您可以上传以下格式的文件：<br />gif、jpg、png、word、excel、txt、zip、ppt、pdf';
$_LANG['view_upload_file'] = '查看上传的文件';
$_LANG['upload_file_type'] = '您上传的文件类型不正确,请重新上传！';
$_LANG['upload_file_error'] = '文件上传出现错误,请重新上传！';
$_LANG['message_empty'] = '您现在还没有留言！';
$_LANG['msg_success'] = '您的留言已成功提交！';
$_LANG['confirm_remove_msg'] = '你确实要彻底删除这条留言吗？';

/* 会员红包 */
$_LANG['bonus_is_used'] = '你输入的红包你已经领取过了！';
$_LANG['bonus_is_used_by_other'] = '你输入的红包已经被其他人领取！';
$_LANG['bonus_add_success'] = '您已经成功的添加了一个新的红包！';
$_LANG['bonus_not_exist'] = '你输入的红包不存在';
$_LANG['user_bonus_empty'] = '您现在还没有红包';
$_LANG['add_bonus_sucess'] = '添加新的红包操作成功！';
$_LANG['add_bonus_false'] = '添加新的红包操作失败！';
$_LANG['bonus_add_expire'] = '该红包已经过期！';
$_LANG['bonus_use_expire'] = '该红包已经过了使用期！';

/* 储值卡 */
$_LANG['no_use_record'] = '储值卡暂时没有使用记录';
$_LANG['add_value_card_sucess'] = '绑定储值卡操作成功！';
$_LANG['add_value_card_false'] = '绑定储值卡操作失败！';
$_LANG['vc_is_used'] = '您已绑定此储值卡！';
$_LANG['vc_is_used_by_other'] = '您输入的储值卡已被其他用户绑定！';
$_LANG['vc_not_exist'] = '您输入的储值卡账号或密码错误！';
$_LANG['vc_use_expire'] = '该储值卡已经过使用期！';
$_LANG['vc_no_use_order'] = '该储值卡无法用于此订单！';
$_LANG['vc_use_inspire'] = '该储值卡还未开始使用！';
$_LANG['vc_limit_expire'] = '超出此类储值卡可绑定张数限制！';

/* 充值卡 */
$_LANG['pc_is_used'] = '此充值卡已被使用！';
$_LANG['pc_use_expire'] = '此充值卡已经过使用期！';
$_LANG['use_pay_card_sucess'] = '使用充值卡操作成功！';
$_LANG['pc_not_exist'] = '您输入的充值卡不存在！';

/* 会员订单 */
$_LANG['order_list_lnk'] = '我的订单列表';
$_LANG['order_number'] = '订单编号';
$_LANG['order_addtime'] = '下单时间';
$_LANG['order_money'] = '订单总金额';
$_LANG['order_status'] = '订单状态';
$_LANG['first_order'] = '主订单';
$_LANG['second_order'] = '从订单';
$_LANG['merge_order'] = '合并订单';
$_LANG['no_priv'] = '你没有权限操作他人订单';
$_LANG['buyer_cancel'] = '用户取消';


$_LANG['cancel'] = '取消订单';
$_LANG['pay_money'] = '付款';
$_LANG['view_order'] = '查看订单';
$_LANG['received'] = '确认收货';
$_LANG['ss_received'] = '已完成';

//ecmoban模板堂 --zhuo start
$_LANG['wait_pay'] = '等待付款';
$_LANG['shipping_single'] = '部分分单';
//ecmoban模板堂 --zhuo end

$_LANG['confirm_cancel'] = '您确认要取消该订单吗？取消后此订单将视为无效订单';
$_LANG['merge_ok'] = '订单合并成功！';
$_LANG['merge_invalid_order'] = '对不起，您选择合并的订单不允许进行合并的操作。';
$_LANG['select'] = '请选择...';
$_LANG['order_not_pay'] = "你的订单状态为 %s ,不需要付款";
$_LANG['order_sn_empty'] = '合并主订单号不能为空';
$_LANG['merge_order_notice'] = '订单合并是在发货前将相同状态的订单合并成一新的订单。<br />收货地址，送货方式等以主定单为准。';
$_LANG['order_exist'] = '该订单不存在！';
$_LANG['order_is_group_buy'] = '[团购]';
$_LANG['order_is_exchange'] = '[积分商城]';
$_LANG['order_is_presale'] = '[预售]';
$_LANG['order_is_auction'] = '[拍卖活动]';
$_LANG['order_is_seckill'] = '[秒杀活动]';
$_LANG['order_is_snatch'] = '[夺宝奇兵]';
$_LANG['gb_deposit'] = '（保证金）';
$_LANG['notice_gb_order_amount'] = '（备注：团购如果有保证金，第一次只需支付保证金和相应的支付费用）';
$_LANG['business_message'] = '发送/查看商家留言';
$_LANG['pay_order_by_surplus'] = '追加使用余额支付订单：%s';
$_LANG['return_surplus_on_cancel'] = '取消订单 %s，退回支付订单时使用的预付款';
$_LANG['return_integral_on_cancel'] = '取消订单 %s，退回支付订单时使用的积分';

/* 订单状态 */
$_LANG['os'][OS_UNCONFIRMED] = '未确认';
$_LANG['os'][OS_CONFIRMED] = '已确认';
$_LANG['os'][OS_SPLITED] = '已确认';
$_LANG['os'][OS_SPLITING_PART] = '已确认';
$_LANG['os'][OS_CANCELED] = '已取消';
$_LANG['os'][OS_INVALID] = '无效';
$_LANG['os'][OS_RETURNED] = '退货';
$_LANG['os'][OS_ONLY_REFOUND] = '仅退款';
$_LANG['os'][OS_RETURNED_PART] = '部分已退货';

$_LANG['ss'][SS_UNSHIPPED] = '未发货';
$_LANG['ss'][SS_PREPARING] = '配货中';
$_LANG['ss'][SS_SHIPPED] = '已发货';
$_LANG['ss'][SS_RECEIVED] = '收货确认';
$_LANG['ss'][SS_SHIPPED_PART] = '已发货(部分商品)';
$_LANG['ss'][SS_SHIPPED_ING] = '配货中'; // 已分单

$_LANG['ps'][PS_UNPAYED] = '未付款';
$_LANG['ps'][PS_PAYING] = '付款中';
$_LANG['ps'][PS_PAYED] = '已付款';
$_LANG['ps'][PS_PAYED_PART] = '部分付款(定金)';
$_LANG['ps'][PS_REFOUND] = '已退款';
//ecmoban模板堂 --zhuo start

//ecmoban模板堂 --zhuo end

$_LANG['shipping_not_need'] = '无需使用配送方式';
$_LANG['current_os_not_unconfirmed'] = '当前订单状态不是“未确认”。';
$_LANG['current_os_already_confirmed'] = '当前订单已经被确认，无法取消，请与店主联系。';
$_LANG['current_ss_not_cancel'] = '只有在未发货状态下才能取消，你可以与店主联系。';
$_LANG['current_ps_not_cancel'] = '只有未付款状态才能取消，要取消请联系店主。';
$_LANG['confirm_received'] = '你确认已经收到货物了吗？否则财物两空哦！';

/* 合并订单及订单详情 */
$_LANG['merge_order_success'] = '合并的订单的操作已成功！';
$_LANG['merge_order_failed']  = '合并的订单的操作失败！请返回重试！';
$_LANG['order_sn_not_null'] = '请填写要合并的订单号';
$_LANG['two_order_sn_same'] = '要合并的两个订单号不能相同';
$_LANG['order_not_exist'] = "订单 %s 不存在";
$_LANG['os_not_unconfirmed_or_confirmed'] = " %s 的订单状态不是“未确认”或“已确认”";
$_LANG['ps_not_unpayed'] = "订单 %s 的付款状态不是“未付款”";
$_LANG['ss_not_unshipped'] = "订单 %s 的发货状态不是“未发货”";
$_LANG['order_user_not_same'] = '要合并的两个订单不是同一个用户下的';
$_LANG['from_order_sn'] = '第一个订单号：';
$_LANG['to_order_sn'] = '第二个订单号：';
$_LANG['merge'] = '合并';
$_LANG['notice_order_sn'] = '当两个订单不一致时，合并后的订单信息（如：支付方式、配送方式、包装、贺卡、红包等）以第二个为准。';
$_LANG['subtotal'] = '小计';
$_LANG['goods_price'] = '商品价格';
$_LANG['goods_attr'] = '属性';
$_LANG['use_balance'] = '使用余额';
$_LANG['order_postscript'] = '订单附言';
$_LANG['order_number'] = '订单号';
$_LANG['consignment'] = '发货单';
$_LANG['shopping_money'] = '商品总价';
$_LANG['invalid_order_id'] = '订单号错误';
$_LANG['shipping'] = '配送方式';
$_LANG['payment'] = '支付方式';
$_LANG['use_pack'] = '使用包装';
$_LANG['use_card'] = '使用贺卡';
$_LANG['order_total_fee'] = '订单总金额';
$_LANG['order_money_paid'] = '已付款金额';
$_LANG['order_amount'] = '应付款金额';
$_LANG['accessories'] = '配件';
$_LANG['largess'] = '赠品';
$_LANG['use_more_surplus'] = '追加使用余额';
$_LANG['max_surplus'] = '（您的帐户余额：%s）';
$_LANG['button_submit'] = '确定';
$_LANG['order_detail'] = '订单详情';
$_LANG['error_surplus_invalid'] = '您输入的数字不正确！';
$_LANG['error_order_is_paid'] = '该订单不需要付款！';
$_LANG['error_surplus_not_enough'] = '您的帐户余额不足！';

/* 订单状态 */
$_LANG['detail_order_sn'] = '订单号';
$_LANG['detail_order_status'] = '订单状态';
$_LANG['detail_pay_status'] = '付款状态';
$_LANG['detail_shipping_status'] = '配送状态';
$_LANG['detail_order_sn'] = '订单号';
$_LANG['detail_to_buyer'] = '卖家留言';

$_LANG['confirm_time'] = '确认于 %s';
$_LANG['pay_time'] = '付款于 %s';
$_LANG['shipping_time'] = '发货于 %s';

$_LANG['select_payment'] = '所选支付方式';
$_LANG['order_amount'] = '应付款金额';
$_LANG['update_address'] = '更新收货人信息';
$_LANG['virtual_card_info'] = '虚拟卡信息';
$_LANG['virtual_goods_info'] = '查看卡密';

/* 取回密码 */
$_LANG['back_home_lnk'] = '返回首页';
$_LANG['get_password_lnk'] = '返回获取密码页面';
$_LANG['get_password_by_question'] = '密码问题找回密码';
$_LANG['get_password_by_mail'] = '注册邮件找回密码';
$_LANG['back_retry_answer'] = '返回重试';

/* 登录 注册 */
$_LANG['label_username'] = '用户名/邮箱/手机';
$_LANG['label_email'] = 'email';
$_LANG['label_password'] = '密码';
$_LANG['label_confirm_password'] = '确认密码';
$_LANG['label_password_intensity'] = '密码强度';
$_LANG['want_login'] = '我已有账号，我要登录';
$_LANG['other_msn'] = 'MSN';
$_LANG['other_qq'] = 'QQ';
$_LANG['other_office_phone'] = '办公电话';
$_LANG['other_home_phone'] = '家庭电话';
$_LANG['other_mobile_phone'] = '手机';
$_LANG['remember'] = '请保存我这次的登录信息。';

$_LANG['msg_un_blank'] = '用户名不能为空';
$_LANG['msg_un_length'] = '用户名最长不得超过15个字符，一个汉字等于2个字符';
$_LANG['msg_un_format'] = '用户名含有非法字符';
$_LANG['msg_un_registered'] = '用户名已经存在,请重新输入';
$_LANG['msg_can_rg'] = '可以注册';
$_LANG['msg_email_blank'] = '邮件地址不能为空';
$_LANG['msg_email_registered'] = '邮箱已存在,请重新输入';
$_LANG['msg_email_format'] = '邮件地址不合法';

$_LANG['msg_phone_blank'] = '手机号码不能为空';
$_LANG['msg_phone_registered'] = '手机已存在,请重新输入';
$_LANG['msg_phone_invalid'] = '无效的手机号码';
$_LANG['msg_phone_not_correct'] = '手机号码不正确，请重新输入';
$_LANG['msg_mobile_code_blank'] = '手机证码不能为空';
$_LANG['msg_mobile_code_not_correct'] = '手机验证码不正确';
$_LANG['msg_mobile_mobile_code'] = '手机动态码不能为空或无效';
$_LANG['msg_mobile_invalid'] = '%s 手机号无效';

$_LANG['msg_confirm_pwd_blank'] = '确认密码不能为空';

$_LANG['msg_identifying_code'] = '验证码不能为空';
$_LANG['msg_identifying_not_correct'] = '验证码不正确';

$_LANG['login_success'] = '登录成功';
$_LANG['confirm_login'] = '确认登录';
$_LANG['profile_lnk'] = '查看我的个人信息';
$_LANG['login_failure'] = "<i class='iconfont icon-minus-sign'></i>用户名或密码错误";
$_LANG['relogin_lnk'] = '重新登录';

$_LANG['sex'] = '性　别';
$_LANG['male'] = '男';
$_LANG['female'] = '女';
$_LANG['secrecy'] = '保密';
$_LANG['birthday'] = '出生日期';

$_LANG['logout'] = '您已经成功的退出了。';
$_LANG['username_empty'] = '用户名为空';
$_LANG['username_invalid'] = '用户名 %s 含有敏感字符';
$_LANG['username_exist'] = '用户名 %s 已经存在';
$_LANG['phone_exist'] = '手机号 %s 已经存在';
$_LANG['username_not_allow'] = '用户名 %s 不允许注册';
$_LANG['confirm_register'] = '确认注册';

$_LANG['agreement'] = "阅读并同意《<a class=\"agreement\" href=\"article.php?cat_id=-1\" target=\"_blank\">服务协议</a>》";

$_LANG['email_empty'] = 'email为空';
$_LANG['email_invalid'] = '%s 不是合法的email地址';
$_LANG['email_exist'] = '%s 已经存在';
$_LANG['email_not_allow'] = 'Email %s 不允许注册';
$_LANG['register'] = '注册新用户名';
$_LANG['register_success'] = '用户名 %s 注册成功';

$_LANG['passwd_question'] = '密码提示问题';
$_LANG['sel_question'] = '请选择密码提示问题';
$_LANG['passwd_answer'] = '密码问题答案';
$_LANG['passwd_balnk'] = '密码中不能包含空格';

/* 用户中心默认页面 */
$_LANG['welcome_to'] = "欢迎您回到";
$_LANG['last_time'] = '您的上一次登录时间';
$_LANG['your_account'] = '您的账户';
$_LANG['your_notice'] = '用户提醒';
$_LANG['your_surplus'] = '余额';
$_LANG['credit_line'] = '信用额度';
$_LANG['your_bonus'] = '您的红包';
$_LANG['your_message'] = '留言';
$_LANG['your_order'] = '订单';
$_LANG['your_integral'] = '您的积分';
$_LANG['your_level'] = '您的等级是 %s ';
$_LANG['next_level'] = ',您还差 %s 积分达到 %s ';
$_LANG['attention'] = '关注';
$_LANG['no_attention'] = '取消关注';
$_LANG['del_attention'] = '确认取消此商品的关注么？';
$_LANG['add_to_attention'] = '确定将此商品加入关注列表么？';
$_LANG['label_need_image'] = '是否显示商品图片：';
$_LANG['need'] = '显示';
$_LANG['need_not'] = '不显示';
$_LANG['horizontal'] = '横排';
$_LANG['verticle'] = '竖排';
$_LANG['generate'] = '生成代码';
$_LANG['label_goods_num'] = '显示商品数量：';
$_LANG['label_rows_num'] = '排列显示条目数：';
$_LANG['label_arrange'] = '选择商品排列方式：';
$_LANG['label_charset'] = '选择编码：';
$_LANG['charset']['utf8'] = '国际化编码（utf8）';
$_LANG['charset']['zh_cn'] = '简体中文';
$_LANG['charset']['zh_tw'] = '繁体中文';
$_LANG['goods_num_must_be_int'] = '商品数量应该是整数';
$_LANG['goods_num_must_over_0'] = '商品数量应该大于0';
$_LANG['rows_num_must_be_int'] = '排列显示条目数应该是整数';
$_LANG['rows_num_must_over_0'] = '排列显示条目数应该大于0';

$_LANG['last_month_order'] = '您最近30天内提交了';
$_LANG['order_unit'] = '个订单';
$_LANG['please_received'] = '以下订单已发货，请注意查收';
$_LANG['your_auction'] = '您竟拍到了<strong>%s</strong> ，现在去 <a href="auction.php?act=view&amp;id=%s"><span style="color:#06c;text-decoration:underline;">购买</span></a>';
$_LANG['your_snatch'] = '您夺宝奇兵竟拍到了<strong>%s</strong> ，现在去 <a href="snatch.php?act=main&amp;id=%s"><span style="color:#06c;text-decoration:underline;">购买</span></a>';

/* 我的标签 */
$_LANG['no_tag'] = '暂时没有标签';
$_LANG['confirm_drop_tag'] = '您确认要删除此标签吗？';

/* user_passport.dwt js语言文件 */
$_LANG['passport_js']['username_empty'] = '<i></i>请输入用户名';
$_LANG['passport_js']['username_shorter'] = '<i></i>用户名长度不能少于 4 个字符。';
$_LANG['passport_js']['username_invalid'] = '<i></i>用户名只能是由字母数字以及下划线组成。';
$_LANG['passport_js']['password_empty'] = '<i></i>请输入密码';
$_LANG['passport_js']['password_shorter'] = '<i></i>登录密码不能少于 6 个字符。';
$_LANG['passport_js']['confirm_password_invalid'] = '<i></i>两次输入密码不一致';
$_LANG['passport_js']['captcha_empty'] = '<i></i>请输入验证码';
$_LANG['passport_js']['email_empty'] = '<i></i>Email 为空';
$_LANG['passport_js']['email_invalid'] = '<i></i>Email 不是合法的地址';
$_LANG['passport_js']['agreement'] = '<i></i>您没有接受协议';
$_LANG['passport_js']['msn_invalid'] = '<i></i>msn地址不是一个有效的邮件地址';
$_LANG['passport_js']['qq_invalid'] = '<i></i>QQ号码不是一个有效的号码';
$_LANG['passport_js']['home_phone_invalid'] = '<i></i>家庭电话不是一个有效号码';
$_LANG['passport_js']['office_phone_invalid'] = '<i></i>办公电话不是一个有效号码';
$_LANG['passport_js']['mobile_phone_invalid'] = '<i></i>手机号码不是一个有效号码';
$_LANG['passport_js']['msg_un_blank'] = '<i></i>用户名不能为空';
$_LANG['passport_js']['msg_un_length'] = '<i></i>用户名最长不得超过15个字符，一个汉字等于2个字符';
$_LANG['passport_js']['msg_un_format'] = '<i></i>用户名含有非法字符';
$_LANG['passport_js']['msg_un_registered'] = '<i></i>用户名已经存在,请重新输入';
$_LANG['passport_js']['msg_can_rg'] = '<i></i>可以注册';
$_LANG['passport_js']['msg_email_blank'] = '<i></i>邮件地址不能为空';
$_LANG['passport_js']['msg_email_registered'] = '<i></i>邮箱已存在,请重新输入';
$_LANG['passport_js']['msg_email_format'] = '<i></i>格式错误，请输入正确的邮箱地址';
$_LANG['passport_js']['msg_blank'] = '<i></i>不能为空';
$_LANG['passport_js']['no_select_question'] = '<i></i>您没有完成密码提示问题的操作';
$_LANG['passport_js']['passwd_balnk'] = '<i></i>密码中不能包含空格';

$_LANG['passport_js']['msg_phone_blank'] = '<i></i>手机号码不能为空';
$_LANG['passport_js']['msg_phone_registered'] = '<i></i>手机已存在,请重新输入';
$_LANG['passport_js']['msg_phone_invalid'] = '<i></i>无效的手机号码';
$_LANG['passport_js']['msg_phone_not_correct'] = '<i></i>手机号码不正确，请重新输入';
$_LANG['passport_js']['msg_mobile_code_blank'] = '<i></i>手机验证码不能为空';
$_LANG['passport_js']['msg_mobile_code_not_correct'] = '<i></i>手机验证码不正确';

$_LANG['passport_js']['msg_confirm_pwd_blank'] = '<i></i>确认密码不能为空';

$_LANG['passport_js']['msg_identifying_code'] = '<i></i>验证码不能为空';
$_LANG['passport_js']['msg_identifying_not_correct'] = '<i></i>验证码不正确';



/* user_clips.dwt js 语言文件 */
$_LANG['clips_js']['msg_title_empty'] = '留言标题为空';
$_LANG['clips_js']['msg_content_empty'] = '留言内容为空';
$_LANG['clips_js']['msg_title_limit'] = '留言标题不能超过200个字';

/* 合并订单js */
$_LANG['merge_order_js']['from_order_empty'] = '请选择要合并的从订单';
$_LANG['merge_order_js']['to_order_empty'] = '请选择要合并的主订单';
$_LANG['merge_order_js']['order_same'] = '主订单和从订单相同，请重新选择';
$_LANG['merge_order_js']['confirm_merge'] = '您确实要合并这两个订单吗？';

/* 将用户订单中商品加入购物车 */
$_LANG['order_id_empty'] = '未指定订单号';
$_LANG['return_to_cart_success'] = '订单中商品已经成功加入购物车中';

/* 保存用户订单收货地址 */
$_LANG['consigness_empty'] = '收货人姓名为空';
$_LANG['address_empty'] = '收货地址详情为空';
$_LANG['require_unconfirmed'] = '该订单状态下不能再修改地址';

/* 红包详情 */
$_LANG['bonus_sn'] = '红包序号';
$_LANG['bonus_name'] = '红包名称';
$_LANG['bonus_amount'] = '红包金额';
$_LANG['min_goods_amount'] = '最小订单金额';
$_LANG['bonus_end_date'] = '截至使用日期';
$_LANG['bonus_status'] = '红包状态';

$_LANG['not_start'] = '未开始';
$_LANG['overdue'] = '已过期';
$_LANG['not_use'] = '未使用';
$_LANG['had_use'] = '已使用';

/* 用户推荐 */
$_LANG['affiliate_mode'] = '分成模式';
$_LANG['affiliate_detail'] = '分成明细';
$_LANG['affiliate_member'] = '我推荐的会员';
$_LANG['affiliate_code'] = '推荐代码';
$_LANG['firefox_copy_alert'] = "您的firefox安全限制限制您进行剪贴板操作，请打开’about:config’将signed.applets.codebase_principal_support’设置为true’之后重试";
$_LANG['affiliate_type'][0] = '推荐注册分成';
$_LANG['affiliate_type'][1] = '推荐订单分成';
$_LANG['affiliate_type'][-1] = '推荐注册分成';
$_LANG['affiliate_type'][-2] = '推荐订单分成';

$_LANG['affiliate_codetype'] = '格式';

$_LANG['affiliate_introduction'] = '分成模式介绍';
$_LANG['affiliate_intro'][0] = '　　本网店为鼓励推荐新用户注册，现开展<b>推荐注册分成</b>活动，活动流程如下：

　　１、将本站提供给您的推荐代码，发送到论坛、博客上。
　　２、访问者点击链接，访问网店。
　　３、在访问者点击链接的 <b>%d%s</b> 内，若该访问者在本站注册，即认定该用户是您推荐的，您将获得等级积分 <b>%d</b> 的奖励 (当您的等级积分超过 <b>%d</b> 时，不再获得奖励)。
　　４、该用户今后在本站的一切消费，您均能获得一定比例的提成。目前实行的提成总额为订单金额的 <b>%s</b> 、积分的 <b>%s</b> ，分配给您、推荐您的人等，具体分配规则请参阅 <b><a href="#myrecommend">我推荐的会员</a></b>。
　　５、提成由管理员人工审核发放，请您耐心等待。
　　６、您可以通过分成明细来查看您的介绍、分成情况。';
$_LANG['affiliate_intro'][1] = '　　本网店为鼓励推荐新用户注册，现开展<b>推荐订单分成</b>活动，活动流程如下：

　　１、在浏览商品时，点击推荐此商品，获得推荐代码，将其发送到论坛、博客上。
　　２、访问者点击链接，访问网店。
　　３、在访问者点击链接的 <b>%d%s</b> 内，若该访问者在本站有订单，即认定该订单是您推荐的。
　　４、您将获得该订单金额的 <b>%s</b> 、积分的 <b>%s</b>的奖励。
　　５、提成由管理员人工审核发放，请您耐心等待。
　　６、您可以通过分成明细来查看您的介绍、分成情况。';

$_LANG['level_point_all'] = '积分分成总额百分比';
$_LANG['level_money_all'] = '现金分成总额百分比';
$_LANG['level_register_all'] = '注册积分分成数';
$_LANG['level_register_up'] = '等级积分分成上限';

$_LANG['affiliate_stats'][0] = '等待处理';
$_LANG['affiliate_stats'][1] = '已分成';
$_LANG['affiliate_stats'][2] = '取消分成';
$_LANG['affiliate_stats'][3] = '已撤销';
$_LANG['affiliate_stats'][4] = '等待买家付款';

$_LANG['level_point'] = '积分分成百分比';
$_LANG['level_money'] = '现金分成百分比';

$_LANG['affiliate_status'] = '分成状态';

$_LANG['affiliate_point'] = '积分分成';
$_LANG['affiliate_money'] = '现金分成';

$_LANG['affiliate_expire'] = '有效时间';

$_LANG['affiliate_lever'] = '等级';
$_LANG['affiliate_num'] = '人数';

$_LANG['affiliate_view'] = '效果';
$_LANG['affiliate_code'] = '代码';

$_LANG['register_affiliate'] = '推荐会员ID %s ( %s ) 注册送积分';
$_LANG['register_points'] = '注册送积分';

$_LANG['validate_ok'] = '%s 您好，您email %s 已通过验证';
$_LANG['validate_fail'] = '验证失败，请确认你的验证链接是否正确';
$_LANG['validate_mail_ok'] = '验证邮件发送成功';

$_LANG['not_validated'] = '您还没有通过邮件认证';
$_LANG['resend_hash_mail'] = '点此发送认证邮件';

$_LANG['query_status'] = '查询状态';

$_LANG['change_payment'] = '改用其他在线支付方式';

$_LANG['copy_to_clipboard'] = '已拷贝至剪贴板。';


$_LANG['expire_unit']['hour'] = '小时';
$_LANG['expire_unit']['day'] = '天';
$_LANG['expire_unit']['week'] = '周';

$_LANG['recommend_webcode'] = '网页签名代码';
$_LANG['recommend_bbscode'] = '论坛签名代码';
$_LANG['im_code'] = '聊天分享';
$_LANG['code_copy'] = '复制代码';
$_LANG['show_good_to_you'] = '推荐给你一个好东西';


/* 积分兑换 */
$_LANG['transform_points'] = '积分兑换';
$_LANG['invalid_points'] = '你输入的积分是不一个合法值';
$_LANG['invalid_input'] = '无效';
$_LANG['overflow_points'] = '您输入的兑换积分超过您的实际积分';
$_LANG['to_pay_points'] = '恭喜您， 你%s论坛%s兑换了%s的商城消费积分';
$_LANG['to_rank_points'] = '恭喜您， 你%s论坛%s兑换了%s的商城等级积分';
$_LANG['from_pay_points'] = '恭喜您， 你%s的商城消费积分兑换%s论坛%s';
$_LANG['from_rank_points'] = '恭喜您， 你%s论坛%s兑换了%s的商城消费积分';
$_LANG['cur_points'] = '您的当前积分';
$_LANG['rule_list'] = '兑换规则';
$_LANG['transform'] = '兑换';
$_LANG['rate_is'] = '比例为';
$_LANG['rule'] = '兑换规则';
$_LANG['transform_num'] = '兑换数量';
$_LANG['transform_result'] = '兑换结果';
$_LANG['bbs'] = '论坛';
$_LANG['exchange_amount'] = '支出';
$_LANG['exchange_desamount'] = '收入';
$_LANG['exchange_ratio'] = '兑换比率';
$_LANG['exchange_points'][0] = '商城等级积分';
$_LANG['exchange_points'][1] = '商城消费积分';
$_LANG['exchange_action'] = '换';
$_LANG['exchange_js']['deny'] = '禁止兑换';
$_LANG['exchange_js']['balance'] = '您的{%s}余额不足，请重新输入';
$_LANG['exchange_deny'] = '该积分不允许兑换';
$_LANG['exchange_success'] = '恭喜您， 你用%s个%s兑换了%s个%s';
$_LANG['exchange_error_1'] = 'UCenter提交积分兑换时发生错误';
$_LANG['rank_points'] = '商城等级积分';
$_LANG['pay_points'] = '商城消费积分';

/* 密码强度 */
$_LANG['pwd_lower'] = '弱';
$_LANG['pwd_middle'] = '中';
$_LANG['pwd_high'] = '强';
$_LANG['user_reg_info'][0] = '如果您不是会员，请注册';
$_LANG['user_reg_info'][1] = '友情提示';
$_LANG['user_reg_info'][2] = '不注册为会员也可在本店购买商品';
$_LANG['user_reg_info'][8] = '不注册为会员不可以在本店购买商品';
$_LANG['user_reg_info'][3] = '但注册之后您可以';
$_LANG['user_reg_info'][4] = '保存您的个人资料';
$_LANG['user_reg_info'][5] = '收藏您关注的商品';
$_LANG['user_reg_info'][6] = '享受会员积分制度';
$_LANG['user_reg_info'][7] = '订阅本店商品信息';
$_LANG['add_bonus'] = '添加红包';

/* 密码找回问题 */
$_LANG['passwd_questions']['friend_birthday'] = '我最好朋友的生日？';
$_LANG['passwd_questions']['old_address']     = '我儿时居住地的地址？';
$_LANG['passwd_questions']['motto']           = '我的座右铭是？';
$_LANG['passwd_questions']['favorite_movie']  = '我最喜爱的电影？';
$_LANG['passwd_questions']['favorite_song']   = '我最喜爱的歌曲？';
$_LANG['passwd_questions']['favorite_food']   = '我最喜爱的食物？';
$_LANG['passwd_questions']['interest']        = '我最大的爱好？';
$_LANG['passwd_questions']['favorite_novel']  = '我最喜欢的小说？';
$_LANG['passwd_questions']['favorite_equipe'] = '我最喜欢的运动队？';

/* 用户菜单 */
$_LANG['order_list'] = '我的订单';
$_LANG['address_list'] = '收货地址';
$_LANG['booking_list'] = '缺货登记';
$_LANG['return_list'] = '退换货订单';
$_LANG['profile'] = '用户信息';
$_LANG['collection_list'] = '我的收藏';
$_LANG['message_list'] = '我的留言';
$_LANG['affiliate'] = '我的推荐';
$_LANG['comment_list'] = '评论/晒单';
$_LANG['bonus'] = '我的红包';
$_LANG['track_packages'] = '跟踪包裹';
$_LANG['account_log'] = '资金管理';
$_LANG['baitiao'] = '我的白条';//bylu
$_LANG['repay_bt'] = '白条还款';//bylu
$_LANG['account_safe'] = '账户安全';
$_LANG['account_bind'] = '账号绑定';
$_LANG['crowdfunding'] = '我的众筹';
$_LANG['focus_brand'] = '关注品牌';
$_LANG['wholesale_buy'] = '采购单';
$_LANG['wholesale_purchase'] = '求购单';
$_LANG['invoice'] = '我的发票';
$_LANG['vat_invoice_info'] = '增值发票信息';
$_LANG['users_log'] = '操作日志';
$_LANG['order_recycle'] = '订单回收站';

//批发管理
$_LANG['wholesale_centre'] = '批发中心';
$_LANG['my_purchase_order'] = '我的采购单';
$_LANG['want_buy_order'] = '我的求购单';
$_LANG['want_buy_order_desc'] = '求购单详情';
$_LANG['supplier_info'] = '供货商信息';
$_LANG['wholesale_return'] = '我的退货单';


$_LANG['address'] = '收货人地址';
$_LANG['goods_order'] = '退换货订单';
$_LANG['apply_return'] = '申请退换货';
$_LANG['return_detail'] = '退换货详情';
$_LANG['user_picture'] = '编辑头像';
$_LANG['store_list'] = '关注店铺';
$_LANG['account_detail'] = '账户明细';
$_LANG['account_raply'] = '提现';
$_LANG['account_deposit'] = '充值';

//我的发票
$_LANG['my_invoice'] = '我的发票';
$_LANG['order_invoice_state'] = '订单发票状态';
$_LANG['increment_invoice_info'] = '增值发票信息';
$_LANG['invoice_help'] = '发票帮助中心';

//交易纠纷
$_LANG['transaction_disputes'] = '交易纠纷';
$_LANG['may_apply_order'] = '可申请订单';
$_LANG['already_apply_order'] = '已申请订单';
$_LANG['apply_disputes_order'] = '申请纠纷单';
$_LANG['view_disputes_order'] = '查看纠纷单';
$_LANG['complaint_title'] = '纠纷类型';
$_LANG['evidence_upload'] = "证据上传";
$_LANG['complaint_title_null'] = '请选择纠纷类型！';
$_LANG['complaint_content_null'] = '问题描述不能为空！';
$_LANG['complaint_success']  = "投诉成功";
$_LANG['back_complaint_list']  = "返回投诉列表";
$_LANG['complaint_reprat']  = "该订单您已经投诉过了，不能重复提交！";
//违规举报
$_LANG['illegal_report'] = '违规举报';
$_LANG['report_goods'] = '举报商品';

/*入驻申请*/
$_LANG['invalid_img_val']   = '商品相册中第%s个图片格式不正确!';
$_LANG['img_url_too_big']   = '商品相册中第%s个图片文件太大了（最大值：%s），无法上传。';

/*by kong haojlj*/
$_LANG['phone_check_code'] = "手机校验码为空或过期，稍后修改";
$_LANG['single_comment'] = "晒单";
$_LANG['user_keyword'] = "商品名称、商品编号、订单编号";

$_LANG['greet'][0] = "早上好";
$_LANG['greet'][1] = "中午好";
$_LANG['greet'][2] = "下午好";
$_LANG['greet'][3] = "晚上好";
$_LANG['plugins'] = "服务器尚未注册该插件！";
$_LANG['Access_timeout'] = "非法访问或请求超时！";
$_LANG['Illegal_access'] = "非法访问或访问出错，请联系管理员！";
$_LANG['Mobile_code_error'] = "手机校验码为空或过期，稍后修改";
$_LANG['Mobile_code_null'] = "手机号或手机验证码不为空";
$_LANG['Mobile_code_msg'] = "手机号或手机验证码不正确";
$_LANG['Mobile_username'] = "用户名或手机号错误！";
$_LANG['password_difference'] = "两次密码不一致！";
$_LANG['delete_order'] = "删除订单";
$_LANG['update_address_error'] = "您的收货地址信息更新失败";
$_LANG['address_perfect_error'] = "收货人信息不完善";
$_LANG['receipt_fail'] = "收货失败-_-，请重新收货";
$_LANG['back_receipt'] = "返回重新收货";
$_LANG['vouchers_all'] = "全场券";
$_LANG['vouchers_user'] = "会员券";
$_LANG['vouchers_shoping'] = "购物券";
$_LANG['vouchers_login'] = "注册券";
$_LANG['vouchers_free'] = "免邮券";
$_LANG['unknown'] = "未知";
$_LANG['user_vouchers'] = "用户中心_优惠券列表";
$_LANG['Repeated_submission'] = "同一订单的同一商品不能重复提交";
$_LANG['Return_abnormal'] = "退换货提交出现异常，请稍后重试";
$_LANG['Apply_refund'] = "申请退款（由用户寄回）";
$_LANG['Apply_Success_Prompt'] = "申请提交成功，工作人员将尽快审核！";
$_LANG['See_Returnlist'] = "查看退换货订单";
$_LANG['Apply_abnormal'] = "申请提交出现了异常，请稍后重试";
$_LANG['payment_coupon'] = "还款提醒";
$_LANG['baitiao_is_pay'] = " 已还款";
$_LANG['balance_insufficient'] = "账户余额不足，请选择其他支付方式";
$_LANG['Ious_Prompt_one'] = "白条分期还款 第%u期 订单号：%s";
$_LANG['Ious_Prompt_two'] = "白条还款 订单号：%s";
$_LANG['Ious_Payment_success'] = "恭喜您,付款成功!";
$_LANG['my_Ious'] = "我的白条";
$_LANG['pay_fail'] = "支付失败";
$_LANG['ious_pay'] = "白条支付";
$_LANG['pay_noline'] = "在线支付";
$_LANG['seller_garde'] = "商家等级入驻";
$_LANG['seller_apply'] = "编号 %s 商家升级申请付款";
$_LANG['apply_success'] = "申请成功";
$_LANG['pay_password_fail'] = "支付密码验证不正确";
$_LANG['back_input'] = "返回重新输入";
$_LANG['back_input_Code'] = "返回重新验证"; 
$_LANG['back_choose'] = "返回重新选择";
$_LANG['back_Fill'] = "返回重新填写";
$_LANG['Mobile_code_null'] = "手机验证码不可为空";
$_LANG['Mobile_code_fail'] = "手机或手机验证码错误";
$_LANG['Real_name_authentication_Mobile_one'] = "手机号和验证手机不符";
$_LANG['Real_name_authentication_Mobile_two'] = "手机号为空，请先认证手机号";
$_LANG['Real_name_authentication_Mobile_three'] = "密码为空或密码不一致";
$_LANG['pay_password_packup_error'] = '您的支付密码有误！'; 
$_LANG['permissions_null'] = "勿闯禁地";
$_LANG['msg_email_format'] = '格式错误，请输入正确的邮箱地址';
$_LANG['msg_email_null'] = '验证邮箱不可为空，请输入正确的邮箱地址';
$_LANG['on_failure'] = "执行失败";
$_LANG['Real_name_password_null'] = "密码不能为空";
$_LANG['Real_name_null'] = "真是姓名不能为空";
$_LANG['self_num_null'] = '身份证号不可为空';
$_LANG['bank_name_null'] = "银行不可为空";
$_LANG['bank_card_null'] = '银行卡号不可为空';
$_LANG['bank_mobile_null'] = '手机号不可为空';
$_LANG['single_comment'] = "晒单评论";
$_LANG['single_comment_on'] = "追加评论";
$_LANG['Risk_rating'][0] = "超级危险";
$_LANG['Risk_rating'][1] = "危险";
$_LANG['Risk_rating'][2] = "低级";
$_LANG['Risk_rating'][3] = "中级";
$_LANG['Risk_rating'][4] = "中高级";
$_LANG['Risk_rating'][5] = "高级";

/*白条*/
$_LANG['operation'] = '操作';
$_LANG['record_id'] = "编号";
$_LANG['qi'] = "期";
$_LANG['dijiqi'] = "第几期";
$_LANG['also_amount'] = "还款金额";
$_LANG['also_state'] = '状态';
$_LANG['also_time'] = '支付时间';
$_LANG['also_pay']['not_pay'] = '未付款';
$_LANG['also_pay']['is_pay'] = '已付款';
$_LANG['bt_Total_amount'] = '白条总额度'; 
$_LANG['element'] = "元";
$_LANG['zhang'] = "张";
$_LANG['Surplus_baitiao'] = "剩余白条额度";
$_LANG['also_day'] = '间隔天数';
$_LANG['also_delay'] = '延期';
$_LANG['amount_paid'] = "待还付款总额";
$_LANG['Pending_payment'] = "待付款白条";
$_LANG['stay_pay'] = "待还款金额";
$_LANG['already_amount'] = "已还款金额";
$_LANG['Transaction_detail'] = "交易明细";
$_LANG['Consumer_account_day'] = "消费记账日";
$_LANG['label_bt_one'] = "到期还款日";
$_LANG['label_bt_two'] = "我的还款日";
$_LANG['order_fee'] = '订单金额';
$_LANG['stage'] = "期";
$_LANG['Has_paid_off'] = "已还清";
$_LANG['repayment'] = "还款";
$_LANG['formated_order_amount'] = "订单总价";
$_LANG['rate'] = "费率";
$_LANG['Number_periods'] = "已还期数";
$_LANG['Repayment_amount_now'] = "当前应还款金额";
$_LANG['Bind_Account'] = "账 号 绑 定";
$_LANG['existing'] = "已有";
$_LANG['No_existing'] = "没有";
$_LANG['Bind_one'] = "账号，请绑定";
$_LANG['user_number_bind'] = "用 户 账 号";
$_LANG['user_password_bind'] = "输 入 密 码";
$_LANG['Code_bind'] = '验　证　码';
$_LANG['captcha_empty'] = '请输入验证码';
$_LANG['forget_password'] = '忘记登录密码';
$_LANG['bind_now'] = "立即绑定";
$_LANG['bind_login_one'] = "您的账户名和登录名";
$_LANG['bind_login_two'] = "建议使用字母、数字和符号两种及以上的组合，6-20个字符";
$_LANG['bind_login_three'] = '建议至少使用两种字符组合';
$_LANG['bind_login_four'] = '支持中文、字母、数字、“-”“_”的组合，3-20个字符';
$_LANG['username_bind'] = "用　户　名";
$_LANG['bind_password'] = "设 置 密 码";
$_LANG['bind_password2'] = "确 认 密 码";
$_LANG['bind_password2_one'] = "请再次输入密码";
$_LANG['bind_phone'] = "手 机 号 码";
$_LANG['bind_phone_one'] = "完成验证后，可以使用该手机登录和找回密码";
$_LANG['bind_phone_two'] = "请正确填写手机号码";
$_LANG['Code_bind_one'] = "看不清？点击图片更换验证码";
$_LANG['bindMobile_code'] = "手机验证码";
$_LANG['bindMobile_code_null'] = "请输入手机验证码";
$_LANG['get_bindMobile_code'] = "获取手机验证码";
$_LANG['comment_captcha_code'] = "邮箱验证码";
$_LANG['agreed_bind'] = "我已阅读并同意";
$_LANG['protocol_bind'] = "用户注册协议";
$_LANG['bind_Mobile'] = "绑定手机";
$_LANG['binding'] = "绑定";
$_LANG['email_user'] = '邮箱';
$_LANG['bonus_user'] = "红包";
$_LANG['confirmation_not'] = "待确认";
$_LANG['piad_not'] = "待付款";
$_LANG['Receipt_receipt'] = "待确认收货";
$_LANG['receipt_not'] = "待收货";
$_LANG['comment_not'] = "待评价";
$_LANG['complete_user'] = "已确认";
$_LANG['see_all_order'] = "查看所有订单";
$_LANG['order_total'] = "总金额";
$_LANG['consignee'] = '收货人';
$_LANG['order_time'] = "下单日期";
$_LANG['see_all_Collection'] = '查看所有收藏';
$_LANG['unit_price_user'] = '单价'; 
$_LANG['Collection_time'] = '收藏时间';
$_LANG['Recent_collection'] = "近期收藏";
$_LANG['calendar_help'] = '帮助';
$_LANG['help_Prompt_one'] = "售后服务保证";
$_LANG['help_Prompt_two'] = "支付方式说明";
$_LANG['help_Prompt_three'] = "货到付款区域";
$_LANG['help_Prompt_four'] = "如何分辨水货手机";
$_LANG['help_Prompt_five'] = "订购方式";
$_LANG['help_Prompt_six'] = "退换货原则";
$_LANG['help_Prompt_Seven'] = "配送支付智能查询";
$_LANG['help_Prompt_Eight'] = "如何享受全国联保";
$_LANG['help_Prompt_Nine'] = "如何分辨原装电池";
$_LANG['you_have'] = '您有';
$_LANG['Out_stock_goods'] = "件缺货商品";
$_LANG['valid_goods'] = '订购商品';
$_LANG['novalid_goods'] = '您当前没有缺货商品';
$_LANG['Collection_goods'] = "收藏商品";
$_LANG['message_title'] = '留言主题';
$_LANG['message_content'] = '留言内容';
$_LANG['Select_file'] = '选择文件'; 
$_LANG['message_type_list'] = '仅支持gif、jpg、png、word、excel、txt、zip、ppt、pdf ';
$_LANG['Split_rule'] = "分成规则";
$_LANG['Web_signature'] = "网页签名";
$_LANG['stay_evaluate_goods'] = "待商品评价";
$_LANG['stay_add_file'] = "待追加图片";
$_LANG['Already_evaluated'] = "已评价";
$_LANG['goods_info'] = "商品信息";
$_LANG['message_state'] = "评价状态";
$_LANG['Click_review'] = "点击评论";
$_LANG['order_Prompt'] = "您还没有完成的订单-_-";
$_LANG['Continue_bask_single'] = "继续晒单";
$_LANG['comment_see'] = "查看评论";
$_LANG['comment_again'] = "继续评论";
$_LANG['comment_add'] = "添加评论";
$_LANG['Satisfaction_evaluation'] = "满意度评价";
$_LANG['No_comment'] = "暂无";
$_LANG['product_desc'] = '商品描述相符';
$_LANG['Like_ata'] = "赞一个";
$_LANG['Please_rate'] = '请打分';
$_LANG['seller_fwtd'] = '卖家服务态度';
$_LANG['logistics_speed'] = '物流发货速度';
$_LANG['logistics_senders'] = '配送人员态度';
$_LANG['comments_think'] = "感谢您的评价";
$_LANG['take_list'] = "提货列表";
$_LANG['complaint_list'] = "交易纠纷";
$_LANG['gift_gard_number'] = "礼品卡卡号";
$_LANG['gift_goods_name'] = '提货商品';
$_LANG['tpnd_time'] = '提货时间';
$_LANG['gift_address'] = "提货地址";
$_LANG['gift_status'] = '提货状态';
$_LANG['gift_Prompt'] = '提货成功，等待发货';
$_LANG['null_gift_Prompt'] = '您暂无提货信息';
$_LANG['null_handle'] = '暂无';
$_LANG['is_complaint'] = '已申诉';
$_LANG['complaint'] = '我要申诉';
$_LANG['complaint_success'] = '申诉成功，已提交系统审核！';
$_LANG['Transaction_record'] = "交易记录";
$_LANG['all_status'] = '全部状态';
$_LANG['Complaint_goods'] = "投诉商品";
$_LANG['Complaint_store'] = "被投诉店铺";
$_LANG['Complaint_title'] = "投诉主题";
$_LANG['Treatment_status'] = "处理状态";
$_LANG['Label_number_null'] = "至少选择一个标签";
$_LANG['Verify_email'] = "验 证 邮 箱";
$_LANG['Login_name'] = "登陆名";
$_LANG['Post'] = "邮";
$_LANG['box'] = "箱";
$_LANG['verification_code'] = "免费获取验证码";
$_LANG['code_number'] = "4位数字";
$_LANG['Free_registration'] = "免费注册";
$_LANG['Welcome_login'] = "欢迎登录";
$_LANG['passport_one'] = "账户名与密码不匹配，请重新输入";
$_LANG['passportforgot_password'] = '忘记密码？';
$_LANG['signin_now'] = '登&nbsp;&nbsp;录';
$_LANG['Third_party_Lgion'] = "用第三方账号直接登录";
$_LANG['jingdong'] = "用APP";
$_LANG['code_lgion'] = "扫码安全登录";
$_LANG['lgion_fail'] = "登陆失败";
$_LANG['code_lgion_again'] = "请刷新二维码后重新扫描";
$_LANG['Refresh_two_code'] = "刷新二维码";
$_LANG['Use_help'] = "使用帮助";
$_LANG['code_problem'] = "扫描不上，版本过低？";
$_LANG['code_lgion_now'] = '扫码登录'; 
$_LANG['Have_account'] = "有账号";
$_LANG['login_here'] = '在此登录';
$_LANG['or_login'] = '或';
$_LANG['email_yanzheng'] = "邮箱验证";
$_LANG['email_label'] = "邮 箱 验 证";
$_LANG['phone_yanzheng'] = "手机验证";
$_LANG['Prompt_problem'] = "提 示 问 题";
$_LANG['passwd_answer_useer'] = '问 题 答 案';
$_LANG['getMobile_code'] = "获取验证码";
$_LANG['service_agreement'] = "服务协议";
$_LANG['Agreement_register'] = "同意协议并注册";
$_LANG['register_now'] = "立即注册";
$_LANG['reset_email_password'] = "邮箱验证";
$_LANG['reset_phone_password'] = '手机验证';
$_LANG['Regist_problem'] = "注册问题";
$_LANG['email_reset'] = '电子邮箱';
$_LANG['reset_password'] = '重置密码';
$_LANG['bind_mobile_regist'] = '绑定手机';
$_LANG['msg_mobile_code'] = '手机动态码';
$_LANG['get_verification_code'] = "获取验证码";
$_LANG['Order_recycling_station'] = "订单回收站";
$_LANG['all_time'] = '全部时间';
$_LANG['Today'] = "今天";
$_LANG['three_today'] = "3天内";
$_LANG['aweek'] = "一周内";
$_LANG['thismonth'] = "一月内";
$_LANG['search_oreder_user'] = "输入商品名称或者订单号搜索";
$_LANG['query'] = '查询';
$_LANG['Ious_staging'] = "白条分期";
$_LANG['baitiao_order'] = "白条订单";
$_LANG['Waybill_number'] = "运单号";
$_LANG['pick_code'] = "提货码";
$_LANG['amount_each'] = "每期金额";
$_LANG['yuan_stage'] ='元/期';
$_LANG['bt_go_refund'] = "去还款";
$_LANG['logistics_tracking'] ='物流跟踪';
$_LANG['info'] = "信息";
$_LANG['zc_scheme_info'] = "众筹方案信息";
$_LANG['zc_project_name'] ='众筹项目名称';
$_LANG['zc_project_raise_money'] = '众筹金额';
$_LANG['zc_goods_price'] = '方案价格';
$_LANG['freight'] = "运费";
$_LANG['user_goods_sn'] = "货号";
$_LANG['Discount_user'] = "优惠";
$_LANG['check_all'] = '全选';
$_LANG['put_in_cat'] = '放入购物车';
$_LANG['Other'] = '其他';
$_LANG['Deposit_user'] = "应付款(定金)";
$_LANG['Deposit_user_one'] = "应付款";
$_LANG['end_pay_time'] = "非常抱歉，已超出最后支付尾款日期";
$_LANG['pay_end_one'] = "请留意支付尾款时间为";
$_LANG['zhi'] = "至";
$_LANG['pay_end_two'] = "止， 订金支付后无法退还！";
$_LANG['Fixed_telephone'] = "固定电话";
$_LANG['user_address'] = "地址";
$_LANG['email_address'] = "电子邮件";
$_LANG['offline_store_information'] = '门店信息';
$_LANG['stores_name']                 = '门店名称';
$_LANG['stores_opening_hours']        = '营业时间';
$_LANG['stores_traffic_line']         = '交通线路';
$_LANG['stores_img']                  = '实景图片';
$_LANG['since_some_info'] = '自提点信息';
$_LANG['Invoice_information'] = '发票信息';
$_LANG['Order_note_user'] = "订单备注";
$_LANG['store_grade_list'] = "商家等级入驻列表";
$_LANG['grade_name'] = "等级名称";
$_LANG['good_number'] = '商品数量';
$_LANG['temp_number'] = '模板数量';
$_LANG['grade_introduce']   = '等级介绍';
$_LANG['entry_criteria']    = '加入标准';
$_LANG['grade_img']         =  '等级标志';
$_LANG['Has_succeeded'] = "已成功";
$_LANG['once'] = '立即申请';
$_LANG['grade_info']        = '当前等级信息';
$_LANG['now_grade']         = '当前等级';
$_LANG['examine_info']         = '审核信息';
$_LANG['in_time']           = '入驻时间';
$_LANG['end_time']          = '到期时间';
$_LANG['refund_grade']      = '剩余金额';
$_LANG['year'] = "年";
$_LANG['information_count'] = '综合信息';
$_LANG['settled_down'] = "入驻年限";
$_LANG['label_total_user'] = '合计';
$_LANG['Settlement'] = "结算";
$_LANG['Select_payment'] = '您选定的支付方式为';
$_LANG['Fee_for_user'] = "手续费为";
$_LANG['payment_amount_user'] = "您的应付款金额为";
$_LANG['remark_package'] = '（礼包）';
$_LANG['nothing'] = "无";
$_LANG['Have_applied'] = "[已申请]";
$_LANG['close_applied'] = "[已关闭]";
$_LANG['applied'] = "申请";
$_LANG['batch_applied'] = "批量申请";
$_LANG['Return_repair'] = "返修退换货";
$_LANG['Return_type'] = "退换类别";
$_LANG['Return_reason'] = "退换原因";
$_LANG['Return_one'] = "7天内退换货";
$_LANG['Return_two'] = "8-15天换货";
$_LANG['Return_three'] = "15天以上在质保期内";
$_LANG['Performance_fault'] = "性能故障";
$_LANG['Performance_fault_one'] = "商品使用过程中，无法满足售前介绍的使用基本需求";
$_LANG['Missing_parts'] = "缺少配件";
$_LANG['Missing_parts_one'] = "实际收到商品附件与网页介绍包装清单中的内容不符";
$_LANG['Logistics_loss'] = "物流损";
$_LANG['Logistics_loss_one'] = "因物流运输导致商品损坏、残缺，无法正常使用";
$_LANG['Inconsistent_goods'] = "商品实物与网站不符";
$_LANG['Inconsistent_goods_one'] = "实际收到 商品实物与网页介绍规格参数中的内容不符";
$_LANG['Buy_wrong'] = "错买、多买";
$_LANG['Buy_wrong_one'] = "在商品（包装及附件）完好的前提下";
$_LANG['Buy_wrong_two'] = "可退";
$_LANG['Return_Explain'] = "<h3>服务说明</h3>
                <p><i>1、</i><span>附件赠品，退换货时请一并退回。</span></p>
                <p><i>2、</i><span>关于物流损：请您在收货时务必仔细验货，如发现商品外包装或商品本身外观存在异常，需当场向配送人员指出，并拒收整个包裹；如您在收货后发现外观异常，请在收货24小时内提交退换货申请。如超时未申请，将无法受理。</span></p>
                <p><i>3、</i><span>关于商品实物与网站描述不符：保证所出售的商品均为正品行货，并与时下市场上同样主流新品一致。但因厂家会在没有任何提前通知的情况下更改产品包装、产地或者一些附件，所以我们无法确保您收到的货物与商城图片、产地、附件说明完全一致。</span></p>
                <p><i>4、</i><span>如果您在使用时对商品质量表示置疑，您可出具相关书面鉴定，我们会按照国家法律规定予以处理。</span></p>";
$_LANG['Service_Mingxi'] = "服务单明细";
$_LANG['reminder'] = '温馨提示';
$_LANG['reminder_one'] = '本次售后服务将由卖家';
$_LANG['reminder_two'] = '为您提供';
$_LANG['Service_type'] = "服务类型";
$_LANG['order_return_type'][0] = "维修";
$_LANG['order_return_type'][1] = "退货";
$_LANG['order_return_type'][2] = "换货";
$_LANG['order_return_type'][3] = "仅退款";
$_LANG['Repair_number'] = "维修数量";
$_LANG['Repair_one'] = "可供维修";
$_LANG['Repair_two'] = "件，已维修";
$_LANG['jian'] = "件";
$_LANG['return_number'] = "退货数量";
$_LANG['return_one'] = "可供退换";
$_LANG['return_two'] = "件，已退";
$_LANG['return_one'] = "可供退换";
$_LANG['return_two'] = "件，已退";
$_LANG['change_two'] = "件，已换";
$_LANG['Application_credentials'] = "申请凭据";
$_LANG['has_Test_Report'] = "有检测报告";
$_LANG['return_reason']='退换货原因';
$_LANG['problem_desc'] = '问题描述';
$_LANG['pic_info'] = '图片信息';
$_LANG['pic_info_one'] = '<p>为了帮助我们更好的解决问题，请您上传图片</p>
                                    <p>按住Ctrl可以多个图片上传</p>
									<p>最多可上传10张图片，每张图片大小不超过5M，支持bmp,gif,jpg,png,jpeg格式文件</p>
									';
$_LANG['Empty_picture'] = '清空图片';
$_LANG['Contact_name'] = "联系人姓名";
$_LANG['label_mobile'] = '手机号码';
$_LANG['detailed_info'] = "详细信息";
$_LANG['return_sn'] = "退货编码";
$_LANG['apply_time'] = "申请时间";
$_LANG['return_type_user'] = "退款类型";
$_LANG['back_cause'] = '退货原因';
$_LANG['amount_return'] = '商品退款';
$_LANG['return_shipping'] = '运费退款';
$_LANG['return_total'] = '合计已退款';
$_LANG['Contact_address'] = "联系地址";
$_LANG['progress_return'] = "换货发货进度";
$_LANG['Waybill'] = "运货单";
$_LANG['Logistics_company'] = "物流公司";
$_LANG['Express_info'] = "快递信息";
$_LANG['Express_company'] = "快递公司";
$_LANG['select_Express_company'] = "请选择快递公司";
$_LANG['User_sent'] = "用户寄出";
$_LANG['courier_sz'] ='快递单号';
$_LANG['Express_info_two'] = "请填写您寄回商品的快递信息";
$_LANG['seller_shipping'] ='商家发货';
$_LANG['consignee_new'] = '新增收货人信息';
$_LANG['add_consignee_one'] = '您已创建';
$_LANG['add_consignee_two'] = '个收货地址，最多可创建';
$_LANG['default_consigneeing'] = "已设置为默认地址";
$_LANG['default_consignee_to'] = "设为默认";
$_LANG['modify'] = "修改";
$_LANG['Newly'] = "新增";
$_LANG['consignee_empty'] = "收货人信息不为空";
$_LANG['Local_area'] = '所在地区';
$_LANG['select_Local_area'] = '请选择所在地区';
$_LANG['detailed_address_null'] = '请填写详细地址';
$_LANG['label_tel'] = '电话号码';
$_LANG['label_tel_empty'] = "请填写联系方式";
$_LANG['default_consigneed'] = "设为默认收货地址";
$_LANG['submit_address'] = '保存收货地址';
$_LANG['youhave'] = '你有';
$_LANG['return_goods_user'] = '件退换货商品';
$_LANG['return_sn_user'] = '退换货流水号';
$_LANG['y_amount'] = '应退金额';
$_LANG['return_order_user'] = '您当前没有退换货订单';
$_LANG['return_order_user_desc'] = '<h3>提示</h3>
                    <p>1、请您尽快寄出退换货商品</p>
                    <p>2、请您寄出商品后填写快递信息，填写快递信息后，您的退换货业务将在我们收到退换货商品后第一时间为您受理</p>';
$_LANG['Modify_Avatar'] = '修改头像';
$_LANG['nick_name'] = '昵称';
$_LANG['Birthday_user'] = "生日";
$_LANG['sex_user'] = "性别";
$_LANG['Immediately_verify'] = "立即验证";
$_LANG['Security_Center'] = "安全中心";
$_LANG['Security_level'] = "安全级别";
$_LANG['Security_level_desc'] = "建议您启动全部安全设置，以保障账户及资金安全。";
$_LANG['password_user'] = '登录密码';
$_LANG['password_user_desc'] = '互联网账号存在被盗风险，建议您定期更改密码以保护账户安全。';
$_LANG['Email_authent'] = "邮箱认证";
$_LANG['email_yanzheng_you'] = "您验证的邮箱";
$_LANG['Email_authent_desc'] = "验证后，可用于快速找回登录密码，接收账户余额变动提醒等。";
$_LANG['is_validated'] = '验证';
$_LANG['phone_authent'] = "手机认证";
$_LANG['phone_authent_desc'] = "您的手机已验证，若已丢失或停用，请立即更换，避免账户被盗";
$_LANG['phone_authent_desc_one'] = "验证后，可用于快速找回登录密码及支付密码，接收账户余额变动提醒。";
$_LANG['pay_password'] = '支付密码';
$_LANG['confirm_pay_password'] = '确认支付密码';
$_LANG['Safety_certification'] = "安全验证";
$_LANG['Degree_of_safety'] = "安全程度";
$_LANG['Degree_of_safety_desc'] = "已启用支付密码，建议您定期更换更高强度的密码。";
$_LANG['pay_password_manage'] = '支付密码';
$_LANG['Safety_renzheng'] = "安全认证";
$_LANG['Safety_renzheng_desc'] = "安全认证";
$_LANG['Enable_now'] = "立即启用";
$_LANG['16_users_real'] = '实名认证';
$_LANG['16_users_real_info'] = '您认证的实名信息';
$_LANG['16_users_real_desc'] = '您还未实名认证该账户，立即实名认证可加快提现速度。';
$_LANG['Safety_now'] = "立即认证";
$_LANG['edit_password_login'] = "修改登录密码";
$_LANG['Verify_identity'] = "验证身份";
$_LANG['Verify_phone_in'] = "已验证手机";
$_LANG['adopt_phone'] = "通过手机验证";
$_LANG['Verify_email_in'] = "通过已验证邮箱验证";
$_LANG['Verify_password_in'] = "通过支付密码验证";
$_LANG['Verify_phone_user'] = "通过已验证手机验证";
$_LANG['Verify_password_user'] = "通过支付密码验证";
$_LANG['Verify_phone_codeempty'] = "请填写手机校验码";
$_LANG['get_verification_code_user'] = "获取短信校验码";
$_LANG['Verified_mailbox'] = "已验证邮箱";
$_LANG['send_verify_email'] = '发送验证邮件';
$_LANG['input_pay_password'] = '请输入支付密码';
$_LANG['send_email_in'] = '已发送验证邮件至';
$_LANG['send_email_desc_one'] = '请尽快登录您的邮箱在有效期内点击验证链接完成验证。';
$_LANG['send_email_desc_two'] = '未收到邮件返回验证';
$_LANG['send_email_desc_three'] = '（请立即完成验证，邮箱验证不通过则修改邮箱失败）';
$_LANG['new_login_password'] = "新的登录密码";
$_LANG['password_Prompt'] = "由字母加数字或符号至少两种以上字符组成的6-20位半角字符，区分大小写";
$_LANG['input_password_again'] = "请再输入一次密码";
$_LANG['security_rating'] = "恭喜您，修改密码成功！";
$_LANG['security_rating_one'] = "最新安全评级";
$_LANG['security_rating_two'] = "您的账户安全级还能提升哦，快去";
$_LANG['security_rating_three'] = "完善其它安全设置提高评级吧！";
$_LANG['Mailbox_Management'] = "管理邮箱";
$_LANG['edit_email'] = "修改邮箱";
$_LANG['Verify_mailbox'] = "验证邮箱";
$_LANG['website_email'] = '邮箱地址';
$_LANG['edit_email_desc_one'] = "恭喜您，邮箱修改验证成功！";
$_LANG['edit_email_desc_two'] = "恭喜您，邮箱验证成功！";
$_LANG['edit_email_desc_three'] = "恭喜您，邮箱修改验证成功！";
$_LANG['edit_email_desc_four'] = "恭喜您，邮箱修改验证成功！";
$_LANG['phone_Management'] = "手机管理";
$_LANG['phone_edit'] = "修改手机";
$_LANG['Verify_phone'] = "验证手机";
$_LANG['bind_phone_user'] = "恭喜您，手机绑定成功！";
$_LANG['pay_password_Management'] = "支付密码管理";
$_LANG['edit_pay_password'] = "修改支付密码";
$_LANG['Enable_pay_password'] = "启用支付密码";
$_LANG['Enable_pay_password_desc'] = "启用支付密码后，将在如下环节通过支付密码对您进行身份认证，确保您的资金安全，请您确认！";
$_LANG['pay_password_Prompt'] = "您的支付密码已开启！";
$_LANG['forgot_paypassword'] = '忘记支付密码？';
$_LANG['pay_online'] = '线上支付';
$_LANG['balance_pay'] = "余额支付";
$_LANG['Enable_pay_password_notice'] = "恭喜您，启用支付密码成功！";
$_LANG['Real_name'] = "真实姓名";
$_LANG['Real_name_notice'] = "为确保您的账户安全，请填写您本人的实名认证信息";
$_LANG['Real_name_input'] = "请输入姓名";
$_LANG['number_ID'] = "身份证号";
$_LANG['bank'] = '银行名称';
$_LANG['bank_card'] = '银行卡号';
$_LANG['Support_bank'] = '支持银行';
$_LANG['Savings_deposit_card'] = "储蓄卡";
$_LANG['Credit'] = "信用卡";
$_LANG['bank_name']['ICBC'] = '工商银行';
$_LANG['bank_name']['CCB'] = '建设银行';
$_LANG['bank_name']['CMB'] = '招商银行';
$_LANG['bank_name']['ABC'] = '农业银行';
$_LANG['bank_name']['BCOM'] = '中国交通银行';
$_LANG['bank_name']['GDB'] = '广发银行';
$_LANG['bank_name']['BOC'] = '中国银行';
$_LANG['bank_name']['CMBC'] = '中国民生银行';
$_LANG['label_mobile_notice'] = '请填写该卡在银行预留的手机号码，验证该银行卡是否真实属于您本人';
$_LANG['label_mobile_input'] = '请输入手机号';
$_LANG['label_mobile_code'] = '短信验证码';
$_LANG['Short_message_null'] = '没有收到短信？';
$_LANG['label_mobile_error'] = '手机号码有误？';
$_LANG['mobile_step_notice'] = "可按以下步骤依次确认解决问题：";
$_LANG['mobile_step_notice_one'] = "1. 请确认该预留手机号是当前使用手机号<br>
                                2. 若银行预留手机号已停用，请联系银行修改<br>
                                3. 银行预留手机号码修改完毕后请重新绑定<br>
                                4. 获取更多帮助，可以联系";
$_LANG['mobile_Agree'] = "同意协议并确定";
$_LANG['edit_info'] = "修改信息";
$_LANG['Verify_time'] = "认证时间";
$_LANG['adopt_status'] = '审核状态';
$_LANG['is_confirm'][0]   = '未审核';
$_LANG['is_confirm'][1]   = '审核通过';
$_LANG['is_confirm'][2]   = '审核未通过';
$_LANG['Authentication_channel'] = "认证渠道：实名认证";
$_LANG['bind_qq'] = "绑定QQ账号";
$_LANG['Bound'] = "已绑定";
$_LANG['not_Bound'] = "未绑定";
$_LANG['Unbundling'] = "解绑";
$_LANG['Un_bind'] = "解除绑定";
$_LANG['Shopping_user'] = "购物";
$_LANG['account_bind_one'] = "您正在使用的";
$_LANG['account_bind_two'] = "账号关联1个QQ账号";
$_LANG['account_bind_three'] = "请您牢记该";
$_LANG['account_bind_fuor'] = "账号";
$_LANG['account_bind_five'] = "<li>1、解绑后需要使用此账号进行登录，解绑后的账号仍可正常使用，订单等信息不会丢失，忘记密码请在登录页找回密码；</li>
                                <li>2、该账号下关联QQ账号将全部解绑；</li>";
$_LANG['account_bind_six'] = "绑定后，可以使用QQ帐号登录";
$_LANG['weibo_one'] = "绑定微博账号";
$_LANG['weibo_two'] = "账号关联1个微博账号";
$_LANG['weibo_three'] = "<li>1、解绑后需要使用此账号进行登录，解绑后的账号仍可正常使用，订单等信息不会丢失，忘记密码请在登录页找回密码；</li>
                                <li>2、该账号下关联微博账号将全部解绑；</li>";
$_LANG['weibo_four'] = "绑定后，可以使用微博帐号登录";
$_LANG['weixin_one'] = "绑定微信账号";
$_LANG['weixin_two'] = "账号关联1个微信账号";
$_LANG['weixin_three'] = "<li>1、解绑后需要使用此账号进行登录，解绑后的账号仍可正常使用，订单等信息不会丢失，忘记密码请在登录页找回密码；</li>
                                <li>2、该账号下关联微信账号将全部解绑；</li>";
$_LANG['weixin_four'] = "绑定后，可以使用微信帐号登录";
$_LANG['bonus_balance'] = "可用红包余额";
$_LANG['available_bonus'] = '可用红包';
$_LANG['About_expire'] = '即将到期';
$_LANG['bonus_info'] = '<h3>红包绑定与说明</h3>
                <p>1、在左侧输入红包卡号密码进行绑定或查询</p>
                <p>2、如果您持有旧版红包（卡号为19位），激活后请在"<span class="ftx-05"> 我的红包 </span>"中查询可用红包余额。</p>
                ';
$_LANG['card_number'] = '卡&nbsp;&nbsp;号';
$_LANG['card_password'] = '密&nbsp;&nbsp;码';
$_LANG['Bind_current_account'] = "绑定到当前账户";
$_LANG['Bound_bonus'] = "已绑定的红包";
$_LANG['keyong'] = "可用";
$_LANG['have_been_exhausted'] = "已用完";
$_LANG['card_number_label'] = "卡号";
$_LANG['face_value_label'] = "面值";
$_LANG['Min_order_amount'] = "最小订单金额";
$_LANG['bind_time'] = "绑定时间";
$_LANG['general_audience'] = '全场通用';
$_LANG['bonus_help_one'] = "红包使用帮助";
$_LANG['bonus_help_two'] = "<p>1.红包绑定账户后，只能供当前绑定账户使用；</p>
                                <p>2.使用红包支付的部分，将不予开发票；</p>
                                <p>3.请关注红包的有效期，我们会在即将过期时提醒您；</p>";
$_LANG['bonus_help_three'] = "<p>4.退货时，红包支付部分退回原红包内，不予兑换；</p>
                                <p>5.如有其他疑问，可点击以下帮助页面进行查询。</p>";
$_LANG['Use_rule'] = "使用规则";
$_LANG['Invoice_distribution'] = "发票及配送";
$_LANG['Red_envelope'] = "红包章程";
$_LANG['Coupon_list'] = "优惠券列表";
$_LANG['Consumption_full'] = "消费满";
$_LANG['no_use_sn'] = "券&nbsp;&nbsp;编&nbsp;&nbsp;号";
$_LANG['Category_restrictions'] = "品类限制";
$_LANG['Platform_limit'] = "平台限制";
$_LANG['xian'] = "限";
$_LANG['employ'] = "使用";
$_LANG['whole_platform'] = "自营";
$_LANG['Immediate_use'] = "立即使用";
$_lang['data_empty'] = "暂无数据";
$_LANG['Expiration_time'] = "过期时间";
$_LANG['latest_state'] ="最新状态";
$_LANG['null_invoice'] = "没有包裹";
$_LANG['label_user_balance'] = '会员余额';
$_LANG['05_seller_account_log'] = '申请记录';
$_LANG['account_log_empty'] = "您当前没有申请记录";
$_LANG['current_funding'] = "您当前的资金为";
$_LANG['operation_log_null'] = "您当前没有操作记录";
$_LANG['Current_balance'] = "当前可提现余额";
$_LANG['Current_balance'] = "当前可提现余额";
$_LANG['Current_balance_label'] = "可提现金额";
$_LANG['renmingni'] = "（人民币）";
$_LANG['Application_withdrawal'] = "申请提现";
$_LANG['Reset_Form'] = "重置表单";
$_LANG['yueangqian'] = "您当前账户余额为";
$_LANG['Determine_Recharge'] = "确定充值";
$_LANG['Recharge_info'] = "充值信息";
$_LANG['I_support'] = "我支持的";
$_LANG['I_concerned'] = "我关注的";
$_LANG['Already_paid'] = "已支付";
$_LANG['not_paid'] = "未支付";
$_LANG['Project_info'] = "项目信息";
$_LANG['zc_number'] = "支持人数(人)";
$_LANG['gz_number'] = "关注人数(人)";
$_LANG['zc_in'] = "众筹中";
$_LANG['zc_raise'] = "已筹集";
$_LANG['go_pay'] = "去付款";
$_LANG['zc_ss'] = '发货中';
$_LANG['Received_goods'] = "已收货";
$_LANG['Received_notice_one'] = "很抱歉，这儿是个荒地。<br/>您还没有支持任何项目，去";
$_LANG['Received_notice_two'] = "看看有什么感兴趣的吧！";
$_LANG['zc_home'] = "众筹首页";
$_LANG['To_support'] = "去支持";
$_LANG['report_img_number'] = "最多只能上传5张图片！";
$_LANG['inform_content_null']           = '举报内容不能为空！';
$_LANG['type_null']        = '请选择举报类型！';
$_LANG['title_null']        = '请选择举报主题！';
$_LANG['report_success']  = "举报成功";
$_LANG['back_report_list']  = "返回举报列表";
$_LANG['repeat_report']  = "该商品你已经举报，请等待处理结果！";
$_LANG['offgoods_report']  = "该商品下架整顿中，暂时不能举报！";


$_LANG['js_languages']['user_name_bind'] = "请输入账户名和密码";
$_LANG['js_languages']['user_namepassword_bind'] = "请输入正确的账户名和密码";
$_LANG['js_languages']['is_user_follow'] = "您确实要关注所选商品吗？";
$_LANG['js_languages']['cancel_user_follow'] = "您确实要取消关注所选商品吗？";
$_LANG['js_languages']['delete_user_follow'] = "您确实要删除关注所选商品吗？";
$_LANG['js_languages']['delete_brand_follow'] = "您确实要删除所关注品牌吗？";
$_LANG['js_languages']['select_follow_goods'] = "请选择关注商品";
$_LANG['js_languages']['select_follow_brand'] = "请选择关注品牌";
$_LANG['js_languages']['follow_Prompt'] = "提示";
$_LANG['js_languages']['comments_think'] = "感谢您的评价";
$_LANG['js_languages']['comment_img_number'] = "最多只能上传10张图片！";
$_LANG['js_languages']['content_not'] = '内容不能为空';
$_LANG['js_languages']['word_number'] = '麻烦填写0-500个字哦';
$_LANG['js_languages']['button_unremove'] = '确认';
$_LANG['js_languages']['comments_Other'] = '您可以继续评论其它订单商品。';
$_LANG['js_languages']['parameter_error'] = "提交参数有误！";
$_LANG['js_languages']['fuzhizgantie'] = "该地址已经复制，你可以使用Ctrl+V 粘贴";
$_LANG['js_languages']['verify_email_null'] = "邮件地址不能为空";
$_LANG['js_languages']['verify_email_Wrongful'] = "邮件地址不合法";
$_LANG['js_languages']['verify_email_code_number'] = "请填写4位数验证码";
$_LANG['js_languages']['Mailbox_sent'] = "邮箱已发送";
$_LANG['js_languages']['operation_order_one'] = "您确实要删除该订单？";
$_LANG['js_languages']['operation_order_two'] = "您确实要还原该订单？";
$_LANG['js_languages']['operation_order_three'] = "您确实要彻底删除该订单？";
$_LANG['js_languages']['logistics_tracking_in'] ='正在查询物流信息，请稍后...';
$_LANG['js_languages']['surplus_null'] ='使用余额不能为空';
$_LANG['js_languages']['surplus_isnumber'] ='使用余额必须是数字';
$_LANG['js_languages']['cannot_null'] = '不能为空';
$_LANG['js_languages']['select_payment_pls'] = '请选择支付方式';
$_LANG['js_languages']['settled_down_lt1'] = "缴纳年限不能小于1";
$_LANG['js_languages']['Wrongful_input'] = "输入内容不合法";
$_LANG['js_languages']['return_one'] = "请选择退换货原因！";
$_LANG['js_languages']['return_two'] = "请选择退换货原因！";
$_LANG['js_languages']['return_three'] = "问题描述不能为空！";
$_LANG['js_languages']['return_four'] = "请选择国家";
$_LANG['js_languages']['address_empty'] = '收货地址不能为空';
$_LANG['js_languages']['Contact_name_empty'] = '联系人姓名不能为空';
$_LANG['js_languages']['phone_format_error'] = '手机号码格式不对。';
$_LANG['js_languages']['msg_phone_blank'] = '手机号码不能为空';
$_LANG['js_languages']['change_two'] = "最多可换数量：";
$_LANG['js_languages']['jian'] = "件";
$_LANG['js_languages']['select_Express_company'] = "请选择快递公司";
$_LANG['js_languages']['Express_companyname_null'] = "请填写快递公司名称";
$_LANG['js_languages']['courier_sz_nul'] ='请填写快递单号';
$_LANG['js_languages']['delete_consignee'] = "您确实要删除该收货地址吗？";
$_LANG['js_languages']['default_consignee'] = "您确实要设置为默认收货地址吗？";
$_LANG['js_languages']['sign_building_desc'] = "设置一个易记的名称，如：'送到家里'、'送到公司'";
$_LANG['js_languages']['Immediately_verify'] = "立即验证";
$_LANG['js_languages']['null_email_user'] = '邮箱不能为空'; 
$_LANG['js_languages']['SMS_code_empty'] = '短信验证码不能为空'; 
$_LANG['js_languages']['Real_name_password_null'] = "密码不能为空";
$_LANG['js_languages']['Verify_password_deff'] = "密码不一样";
$_LANG['js_languages']['Real_name_null'] = "真实姓名不能为空";
$_LANG['js_languages']['number_ID_null'] = "身份证号不能为空";
$_LANG['js_languages']['bank_name_null'] = "银行不能为空";
$_LANG['js_languages']['bank_number_null'] = "银行卡号不能为空";
$_LANG['js_languages']['Un_bind'] = "解除绑定";
$_LANG['js_languages']['bind_user_one'] = "您确定要";
$_LANG['js_languages']['account_bind_fuor'] = "账号";
$_LANG['js_languages']['account_bind_five'] = "解绑后请用";
$_LANG['js_languages']['registered'] = "登录";
$_LANG['js_languages']['card_number_null'] = "卡号不能为空";
$_LANG['js_languages']['go_login'] = "去登录";
$_LANG['js_languages']['cancel_zc'] = "是否确定取消关注该众筹项目";
$_LANG['js_languages']['no_attention'] = '取消关注';
$_LANG['js_languages']['Unbundling'] = "解绑";
$_LANG['js_languages']['binding'] = "绑定";
$_LANG['js_languages']['stop'] = "收起";
$_LANG['js_languages']['number_ID_error'] = "身份证号格式错误，请输入正确的身份证号";
$_LANG['js_languages']['bank_number_error'] = "银行卡号格式不正确";

//20161214 start
$_LANG['follow_batch'] = '批量关注';
$_LANG['drop_batch'] = '批量删除';
$_LANG['Collection_goods_null'] = "抱歉，您尚未收藏商品！";
$_LANG['Popularity_follow'] = "关注人气";
$_LANG['Service_evaluation'] = "服务评价";
$_LANG['follow_time'] = "关注时间";
$_LANG['shop_sells'] = "本店热卖";
$_LANG['shop_new'] = "本店新品";
$_LANG['Collection_store_null'] = "抱歉，您尚未收藏店铺！";
$_LANG['score'] = "评分";
$_LANG['Pleas_mark'] = "请打分数";
$_LANG['Experience'] = "心得";
$_LANG['Experience_one'] = "屏幕大小合适么？系统用的习惯么？配件质量如何？快写下你的评价，分享给大家吧！";
$_LANG['Experience_two'] = "最多输入500字";
$_LANG['drop_pic'] = '删除图片';
$_LANG['Personal_homepage'] = "个人主页";
$_LANG['oreder_core'] = "订单中心";
$_LANG['user_core'] = "会员中心";
$_LANG['Trade_complaint'] = "交易纠纷";
$_LANG['Account_center'] = "账户中心";
$_LANG['Shop_management'] = "店铺管理";
$_LANG['Store_backstage'] = "店铺后台";
$_LANG['business'] = "商家";
$_LANG['people_time'] = "收货人/下单日期";
$_LANG['Sub_order'] = "子订单";
$_LANG['reduction'] = "还原";
$_LANG['order_user_desc'] = "尊敬的客户，由于您的商品有可能在不同商家，所以您的订单被拆分为多个子订单分开配送，给您带来的不便还请谅解。";
//20161214 end

$_LANG['seller_Grade'] = '商家等级';

//储值卡
$_LANG['user_one_code'] = "短信验证码不正确";
$_LANG['value_card_list'] = '储值卡列表';
$_LANG['overdue_time'] = "有效期至";
$_LANG['face_value_card'] = "面&nbsp;&nbsp;&nbsp;&nbsp;值";
$_LANG['card_type'] = '卡类别';
$_LANG['menuplatform_card'] = "平台";
$_LANG['value_card_info'] = "储值卡使用详情";
$_LANG['lab_card_id'] = '编号';
$_LANG['Use_the_amount_of'] = "使用金额";
$_LANG['use_value_card'] = '使用储值卡';
$_LANG['value_card_bind'] = '储值卡绑定';
$_LANG['card_bind_desc'] = '储值卡绑定与说明';
$_LANG['card_desc']['one'] = '在右侧输入储值卡卡号密码进行绑定';
$_LANG['card_desc']['two'] = '请仔细查阅储值卡使用范围';
$_LANG['value_card_filling'] = '储值卡充值';
$_LANG['value_card_unwrap'] = '储值卡解绑';
$_LANG['Buyer_impression'] = '买家印象';

/*2017模板 新增*/
//内容为空时
$_LANG['no_records'] = '对不起，没有数据';
$_LANG['no_brand_goods'] = '该品牌暂时没有商品哟~';
$_LANG['no_store_goods'] = '该商家暂时没有商品哟~';
$_LANG['no_bonus_keyong'] = '主人，您还没有可用红包~';
$_LANG['no_bonus_daoqi'] = '主人，您还没有即将到期红包~';
$_LANG['no_bonus_end'] = '主人，您还没有使用过红包~';

$_LANG['no_coupons_keyong'] = '主人，您还没有可用优惠券~';
$_LANG['no_coupons_use'] = '主人，您还没有已使用优惠券~';
$_LANG['no_coupons_over'] = '主人，您还没有过期优惠券~';
$_LANG['no_coupons_soon_over'] = '主人，您还没有即将过期优惠券~';
$_LANG['after_service_desc'] = '被拒原因';

$_LANG['user']['default']['no_records'] = '主人，您近期还没有购买任何商品哟~';
$_LANG['user']['order_list']['no_records'] = '主人，您还没有购买任何商品哟~';
$_LANG['user']['booking_list']['no_records'] = '主人，您现在还没有缺货登记商品哟~';
$_LANG['user']['return_list']['no_records'] = '主人，您现在还没有退换货订单哟~';
$_LANG['user']['crowdfunding']['no_records'] = '主人，您还没有支持的任何项目哟~';
$_LANG['user']['collection_list']['no_records'] = '主人，您还没有收藏任何商品哟~';
$_LANG['user']['store_list']['no_records'] = '主人，您还没有关注任何店铺哟~';
$_LANG['user']['focus_brand']['no_records'] = '主人，您还没有关注任何品牌哟~';
$_LANG['user']['comment_list_0']['no_records'] = '没有可评论的订单哦~';
$_LANG['user']['comment_list_1']['no_records'] = '没有待追加的晒单哦~';
$_LANG['user']['comment_list_2']['no_records'] = '没有已评论的订单哦~';
$_LANG['user']['take_list']['no_records'] = '主人，您还没有提货信息哦~';
$_LANG['user']['value_card']['no_records'] = '主人，您还没有储值卡哦~';
$_LANG['user']['track_packages']['no_records'] = '主人，您还没有跟踪包裹哦~';
$_LANG['user']['restore']['no_records'] = '主人，您现在没有已删除订单哦~';
$_LANG['user']['order_recycle']['no_records'] = '主人，您现在没有已删除订单哦~';
/*2017模板 新增end*/

$_LANG['trade_snapshot'] = '交易快照';
$_LANG['malice_report']  = "您存在恶意举报，您的举报权限已被冻结！";
$_LANG['malice_report_end']  = "冻结到期时间：";
$_LANG['handle_message']        = "回复";
$_LANG['handle_type']        = "处理结果";
$_LANG['handle_type_desc'][1]   = "无效举报--商品会正常销售";
$_LANG['handle_type_desc'][2]   = "恶意举报--您所有未处理举报将被无效处理，用户将被禁止举报";
$_LANG['handle_type_desc'][3]   = "有效举报--商品将被违规下架";
$_LANG['complaint_state'][0]       = "未处理";
$_LANG['complaint_state'][1]       = "待申诉";
$_LANG['complaint_state'][2]       = "对话中";
$_LANG['complaint_state'][3]       = "待仲裁";
$_LANG['complaint_state'][4]       = "完成";
$_LANG['complaint_info']           = "投诉信息";
$_LANG['appeal_info']              = "申诉详情";
$_LANG['appeal_content']        = "申诉内容";
$_LANG['appeal_img']            = "申诉图片";
$_LANG['complaint_time'] = "投诉时间";
$_LANG['talk_record'] = "对话记录";
$_LANG['talk_release'] = "发布对话";
$_LANG['talk_refresh'] = "刷新对话";
$_LANG['talk_info'] = "对话详情";
$_LANG['talk_member_type'][1]            = "投诉方";
$_LANG['talk_member_type'][2]            = "被投诉方";
$_LANG['talk_member_type'][3]            = "管理员";
$_LANG['end_handle_time'] = "处理时间";
$_LANG['complaint_handle_time'] = "初步审核时间";
$_LANG['handle_user'] = "操作人";
$_LANG['end_handle_messg'] = "处理意见";


$_LANG['need_invoice'][0] = "普通发票";
$_LANG['need_invoice'][1] = "增值税发票";


$_LANG['auction_staues'][0] = '未开始';
$_LANG['auction_staues'][1] = '进行中';
$_LANG['auction_staues'][2] = '已结束';
$_LANG['auction_staues'][3] = '已结束';

//退换货激活
$_LANG['activation_return'] = '激活';
$_LANG['activation_number_msg'] = '只能激活 %s 次，您不能激活了哦。。。';

$_LANG['change_type'] = '操作类型';
$_LANG['ip_address'] = "IP地址";
$_LANG['change_city'] = "参考地点";
$_LANG['logon_service'] = "登录业务";

$_LANG['change_type_user'][1] = '会员登录';
$_LANG['change_type_user'][2] = '修改会员头像';
$_LANG['change_type_user'][3] = '修改会员信息';
$_LANG['change_type_user'][4] = '会员实名认证';
$_LANG['change_type_user'][5] = '会员支付密码';
$_LANG['change_type_user'][6] = '修改会员手机';
$_LANG['change_type_user'][7] = '修改会员邮箱';
$_LANG['change_type_user'][8] = '修改会员登录密码';
$_LANG['change_type_user'][9] = '修改会员信用额度';

$_LANG['merchants_upgrade_log'] = "商家等级申请记录";
$_LANG['refund_price'] = "等级差额";
$_LANG['payable_amount'] = "应付金额";
$_LANG['back_price'] = "应退金额";
$_LANG['apply_status'] = "申请状态";
$_LANG['drop_confirm'] = "申请状态";
$_LANG['please_select_goods'] = "请选择需要退货的商品！";
$_LANG['nonsupport_return_goods'] = "包含不支持退货的商品，请重新选择";

$_LANG['cope_order_amount'] = '应付款';
$_LANG['should_back_amount'] = '应退款';
$_LANG['realpay_amount'] = '实付款';

//延迟收货
$_LANG['order_delayed'] = '延迟收货';
$_LANG['auto_delivery_data']  = '自动确认收货时间';
$_LANG['order_delay_day_desc'] = '距离结束超过 %s 天不可延长收货';
$_LANG['order_delay_apply_status'][0] = "&nbsp;&nbsp; 【%s】 &nbsp;天延期收货申请（审核中）";
$_LANG['order_delay_apply_status'][1] = "&nbsp;&nbsp;  【%s】 &nbsp;天延期收货申请（已通过）";
$_LANG['order_delay_apply_status'][2] = "&nbsp;&nbsp; 【%s】 &nbsp;天延期收货申请（未通过审核）";
$_LANG['order_delayed_repeat'] = '该订单已提交过申请，正在审核中';
$_LANG['order_delayed_success'] = '已成功提交申请，请等待审核';
$_LANG['order_delayed_beyond'] = '已申请 %s 次不能在提交延期收货';
$_LANG['order_delayed_wrong'] = '未知错误，请重试！';
$_LANG['pay_effective_Invalid'] = '支付超时';
$_LANG['return_order_surplus'] = '由于取消、无效或退货操作，退回支付订单 %s 时使用的预付款';
$_LANG['return_order_integral'] = '由于取消、无效或退货操作，退回支付订单 %s 时使用的积分';
$_LANG['order_vcard_return'] = '【订单退款】储值卡退款金额：%s';

$_LANG['allow_order_delay'] = '延迟收货';

//批发
$_LANG['wholesale_reminder_one'] = '本次售后服务将由供应商';
$_LANG['wholesale_return_list'] = '采购退货单';
?>