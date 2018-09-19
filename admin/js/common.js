/* $Id : common.js 4824 2007-01-31 08:23:56Z paulgao $ */

/* 检查新订单的时间间隔 */
var NEW_ORDER_INTERVAL = 180000;

/* *
 * 开始检查新订单；
 */
function startCheckOrder()
{
  checkOrder();
  window.setInterval("checkOrder()", NEW_ORDER_INTERVAL);
}

/*
 * 检查订单
 */
function checkOrder()
{
	Ajax.call('index.php?is_ajax=1&act=check_order','', checkOrderResponse, 'GET', 'JSON');
}

/* *
 * 处理检查订单的反馈信息
 */
function checkOrderResponse(data)
{
  //出错屏蔽
  if (data.error != 0)
  {
    return;
  }
  try
  {
	//订单提醒 start
		var new_orders = data.new_orders ? data.new_orders :0;//新订单 
		var await_ship = data.await_ship ? data.await_ship :0;//待发货订单
		var no_change = data.no_change ? data.no_change :0;//待处理退换货订单
		var complaint = data.complaint ? data.complaint :0;//待处理投诉订单
		var booking_goods = data.booking_goods ? data.booking_goods :0;//待处理缺货商品
		//订单提醒 end
		
		//商品提醒 start
		var goods_report = data.goods_report ? data.goods_report :0;//未处理商品投诉
		var sale_notice = data.sale_notice ? data.sale_notice :0;//未处理商品降价通知
		var no_check_goods = data.no_check_goods ? data.no_check_goods :0;//未审核商家商品
		var no_check_brand = data.no_check_brand ? data.no_check_brand :0;//未审核商家品牌
		var self_warn_number = data.self_warn_number ? data.self_warn_number :0;//自营商品库存预警值
		var merchants_warn_number = data.merchants_warn_number ? data.merchants_warn_number :0;//商家商品库存预警值
		//商品提醒 end
		
		//商家提醒 start
		var shop_account = data.shop_account ? data.shop_account :0;//未审核商家
		var shopinfo_account = data.shopinfo_account ? data.shopinfo_account :0;//未审核店铺信息
		var seller_account = data.seller_account ? data.seller_account :0;//未处理商家实名认证
		var wait_cash = data.wait_cash ? data.wait_cash :0;//待审核商家提现
		var wait_balance = data.wait_balance ? data.wait_balance :0;//待审核商家结算
		var wait_recharge = data.wait_recharge ? data.wait_recharge :0;//待审核商家充值
		var seller_apply = data.seller_apply ? data.seller_apply :0;//待审核店铺等级
		//商家提醒 end
		
		//广告位提醒 start
		var advance_date = data.advance_date ? data.advance_date :0;//即将过期广告
		//广告位提醒 end
		
		//会员提醒 start
		var user_account = data.user_account ? data.user_account :0;//未处理会员实名认证
		var user_recharge = data.user_recharge ? data.user_recharge :0;//未处理会员充值申请
		var user_withdraw = data.user_withdraw ? data.user_withdraw :0;//未处理会员提现申请
		var user_vat = data.user_vat ? data.user_vat :0;//未处理会员增票资质审核
		var user_discuss = data.user_discuss ? data.user_discuss :0;//网友讨论圈审核
		//会员提醒 end
		
		//促销活动提醒 start
		var snatch = data.snatch ? data.snatch :0;//未审核夺宝奇兵
		var bonus_type = data.bonus_type ? data.bonus_type :0;//未审核红包类型
		var group_by = data.group_by ? data.group_by :0;//未审核团购活动
		var topic = data.topic ? data.topic :0;//未审核专题
		var auction = data.auction ? data.auction :0;//未审拍卖活动
		var favourable = data.favourable ? data.favourable :0;//未审优惠活动
		var presale = data.presale ? data.presale :0;//未审预售活动
		var package_goods = data.package_goods ? data.package_goods :0;//未审超值礼包
		var exchange_goods = data.exchange_goods ? data.exchange_goods :0;//未审积分商城商品
		var coupons = data.coupons ? data.coupons :0;//未审优惠券
		var gift_gard = data.gift_gard ? data.gift_gard :0;//未审礼品卡
		var wholesale = data.wholesale ? data.wholesale :0;//未审批发
		//促销活动提醒 end
        
		var total = parseInt(new_orders)+parseInt(await_ship)+parseInt(no_change)+parseInt(complaint)+parseInt(booking_goods)+parseInt(goods_report)+parseInt(sale_notice)+parseInt(no_check_goods)+parseInt(no_check_brand)+parseInt(self_warn_number)+parseInt(merchants_warn_number)
+parseInt(shop_account)+parseInt(shopinfo_account)+parseInt(seller_account)+parseInt(wait_cash)+parseInt(wait_balance)+parseInt(wait_recharge)+parseInt(seller_apply)+parseInt(advance_date)+parseInt(user_account)+parseInt(user_recharge)+parseInt(user_withdraw)+parseInt(user_vat)+parseInt(user_discuss)+parseInt(snatch)+parseInt(bonus_type)+parseInt(group_by)+parseInt(topic)+parseInt(auction)+parseInt(favourable)+parseInt(presale)+parseInt(package_goods)+parseInt(exchange_goods)+parseInt(coupons)+parseInt(gift_gard)+parseInt(wholesale);
		//提示总数目格式
		if(total >0 && total<100){
			$('.msg #total').html(total);
		}else if(total>99){
			$('.msg').siblings("s").remove();
			$('.msg').after('<s><img src="images/gduo.png"></s>');
		} 
		
		//订单提示
		var order_total = parseInt(new_orders)+parseInt(await_ship)+parseInt(no_change)+parseInt(complaint)+parseInt(booking_goods);
		if(order_total >0){
			$('.order_msg #total').html(order_total);
		}else{
			$('.order_msg #total').html(0);
		}
		//商品提示
		var goods_total = parseInt(goods_report)+parseInt(sale_notice)+parseInt(no_check_goods)+parseInt(no_check_brand)+parseInt(self_warn_number)+parseInt(merchants_warn_number);
		if(goods_total >0){
			$('.goods_msg #total').html(goods_total);
		}else{
			$('.goods_msg #total').html(0);
		}
		//商家审核提示
		var shop_total = parseInt(shop_account)+parseInt(shopinfo_account)+parseInt(seller_account)+parseInt(wait_cash)+parseInt(wait_balance)+parseInt(wait_recharge)+parseInt(seller_apply);
		if(shop_total >0){
			$('.shop_msg #total').html(shop_total);
		}else{
			$('.shop_msg #total').html(0);
		}
		//广告位提示
		var ad_total = parseInt(advance_date);
		if(ad_total >0){
			$('.ad_msg #total').html(ad_total);
		}else{
			$('.ad_msg #total').html(0);
		}
		//会员提示
		var user_total = parseInt(user_account)+parseInt(user_recharge)+parseInt(user_withdraw)+parseInt(user_vat)+parseInt(user_discuss);
		if(user_total >0){
			$('.user_msg #total').html(user_total);
		}else{
			$('.user_msg #total').html(0);
		}
		//活动提示
		var campaign_total = parseInt(snatch)+parseInt(bonus_type)+parseInt(group_by)+parseInt(topic)+parseInt(auction)+parseInt(favourable)+parseInt(presale)+parseInt(package_goods)+parseInt(exchange_goods)+parseInt(coupons)+parseInt(gift_gard)+parseInt(wholesale);
		if(campaign_total >0){
			$('.campaign_msg #total').html(campaign_total);
		}else{
			$('.campaign_msg #total').html(0);
		}
		

        if(total != 0){
			//订单提醒 start
				//新订单 start
				if(new_orders>=0 && new_orders<100){
					$("*[ectype='orderMsg']").html("").append('<p><a href="javascript:void(0);" data-url="order.php?act=list&composite_status=106" data-param="menushopping|02_order_list" target="workspace" class="message" >您有新订单</a> <span class="tiptool">（<em id="new_orders">'+new_orders+'</em>）</span></p>')
				}else if(new_orders>99){
					$("*[ectype='orderMsg']").html("").append('<p><a href="javascript:void(0);" data-url="order.php?act=list&composite_status=106" data-param="menushopping|02_order_list" target="workspace" class="message" >您有新订单</a><span class="tiptool">（<em id="new_orders">99+</em>）</span></p>')
				}
				//新订单 end
				
				//待发货订单 start
				if(await_ship>=0 && await_ship<100){
					$("*[ectype='orderMsg']").append('<p><a href="javascript:void(0);" data-url="order.php?act=list&composite_status=101&source=start" data-param="menushopping|02_order_list" target="workspace" class="message" >待发货订单</a> <span class="tiptool">（<em id="no_paid">'+await_ship+'</em>）</span></p>')
				}else if(await_ship>99){
					$("*[ectype='orderMsg']").append('<p><a href="javascript:void(0);" data-url="order.php?act=list&composite_status=101&source=start" data-param="menushopping|02_order_list" target="workspace" class="message" >待发货订单</a><span class="tiptool">（<em id="no_paid">99+</em>）</span></p>')
				}
				//待发货订单 end
				
				//待处理退换货订单 start
				if(no_change>=0 && no_change<100){
					$("*[ectype='orderMsg']").append('<p><a href="javascript:void(0);" data-url="order.php?act=return_list&composite_status=105" data-param="menushopping|12_back_apply" target="workspace" class="message" >待处理退换货订单</a> <span class="tiptool">（<em id="no_change">'+no_change+'</em>）</span></p>')
				}else if(no_change>99){
					$("*[ectype='orderMsg']").append('<p><a href="javascript:void(0);" data-url="order.php?act=return_list&composite_status=105" data-param="menushopping|12_back_apply" class="message" >待处理退换货订单</a><span class="tiptool">（<em id="no_change">99+</em>）</span></p>')
				}
				//待处理退换货订单 end
				
				//待处理投诉订单 start
				if(complaint>=0 && complaint<100){
					$("*[ectype='orderMsg']").append('<p><a href="javascript:void(0);" data-url="complaint.php?act=list" data-param="menushopping|13_complaint" target="workspace" class="message" >交易纠纷</a> <span class="tiptool">（<em id="no_change">'+complaint+'</em>）</span></p>')
				}else if(complaint>99){
					$("*[ectype='orderMsg']").append('<p><a href="javascript:void(0);" data-url="complaint.php?act=list" data-param="menushopping|13_complaint" class="message" >交易纠纷</a><span class="tiptool">（<em id="no_change">99+</em>）</span></p>')
				}
				//待处理投诉订单 end
				
				//待处理缺货商品 start
				if(booking_goods>=0 && booking_goods<100){
					$("*[ectype='orderMsg']").append('<p><a href="javascript:void(0);" data-url="goods_booking.php?act=list_all" data-param="menushopping|06_undispose_booking" target="workspace" class="message" >缺货商品</a> <span class="tiptool">（<em id="no_change">'+booking_goods+'</em>）</span></p>')
				}else if(booking_goods>99){
					$("*[ectype='orderMsg']").append('<p><a href="javascript:void(0);" data-url="goods_booking.php?act=list_all" data-param="menushopping|06_undispose_booking" class="message" >缺货商品</a><span class="tiptool">（<em id="no_change">99+</em>）</span></p>')
				}
				//待处理缺货商品 end
			//订单提醒 end
			
			//商品提醒 start
				//未处理商品投诉 start
				if(goods_report>=0 && goods_report<100){
					$("*[ectype='goodMsg']").html("").append('<p><a href="javascript:void(0);" data-url="goods_report.php?act=list&handle_type=6" data-param="menushopping|goods_report" target="workspace" class="message">商品举报</a> <span class="tiptool">（<em id="goods_report">'+goods_report+'</em>）</span></p>')
				}else if(goods_report>99){
					$("*[ectype='goodMsg']").html("").append('<p><a href="javascript:void(0);" data-url="goods_report.php?act=list&handle_type=6" data-param="menushopping|goods_report" target="workspace" class="message">商品举报</a><span class="tiptool">（<em id="goods_report">99+</em>）</span></p>')
				}
				//未处理商品投诉 end
				
				//未处理商品降价通知 start
				if(sale_notice>=0 && sale_notice<100){
					$("*[ectype='goodMsg']").append('<p><a href="javascript:void(0);" data-url="sale_notice.php?act=list" data-param="menushopping|sale_notice" target="workspace" class="message">商品降价通知</a> <span class="tiptool">（<em id="goods_report">'+sale_notice+'</em>）</span></p>')
				}else if(sale_notice>99){
					$("*[ectype='goodMsg']").append('<p><a href="javascript:void(0);" data-url="sale_notice.php?act=list" data-param="menushopping|sale_notice" target="workspace" class="message">商品降价通知</a><span class="tiptool">（<em id="goods_report">99+</em>）</span></p>')
				}
				//未处理商品降价通知 end
				
				//未审核商家商品 start
				if(no_check_goods>=0 && no_check_goods<100){
					$("*[ectype='goodMsg']").append('<p><a href="javascript:void(0);" data-url="goods.php?act=review_status&seller_list=1" data-param="menushopping|01_goods_list" target="workspace" class="message">未审核商家商品</a> <span class="tiptool">（<em id="goods_report">'+no_check_goods+'</em>）</span></p>')
				}else if(no_check_goods>99){
					$("*[ectype='goodMsg']").append('<p><a href="javascript:void(0);" data-url="goods.php?act=review_status&seller_list=1" data-param="menushopping|01_goods_list" target="workspace" class="message">未审核商家商品</a><span class="tiptool">（<em id="goods_report">99+</em>）</span></p>')
				}
				//未审核商家商品 end
				
				//未审核商家品牌 start
				if(no_check_brand>=0 && no_check_brand<100){
					$("*[ectype='goodMsg']").append('<p><a href="javascript:void(0);" data-url="merchants_brand.php?act=list&audit_status=3" data-param="menushopping|06_goods_brand" target="workspace" class="message">未审核商家品牌</a> <span class="tiptool">（<em id="goods_report">'+no_check_brand+'</em>）</span></p>')
				}else if(no_check_brand>99){
					$("*[ectype='goodMsg']").append('<p><a href="javascript:void(0);" data-url="merchants_brand.php?act=list&audit_status=3" data-param="menushopping|06_goods_brand" target="workspace" class="message">未审核商家品牌</a><span class="tiptool">（<em id="goods_report">99+</em>）</span></p>')
				}
				//未审核商家品牌 end
				
				//自营商品库存预警值 start
				if(self_warn_number>=0 && self_warn_number<100){
					$("*[ectype='goodMsg']").append('<p><a href="javascript:void(0);" data-url="goods.php?act=list&warn_number=1&seller_list=0" data-param="menushopping|01_goods_list" target="workspace" class="message">自营普通商品库存预警</a> <span class="tiptool">（<em id="goods_report">'+self_warn_number+'</em>）</span></p>')
				}else if(self_warn_number>99){
					$("*[ectype='goodMsg']").append('<p><a href="javascript:void(0);" data-url="goods.php?act=list$warn_number=1&seller_list=0" data-param="menushopping|01_goods_list" target="workspace" class="message">自营普通商品库存预警</a><span class="tiptool">（<em id="goods_report">99+</em>）</span></p>')
				}
				//自营商品库存预警值 end
				
				//商家商品库存预警值 start
				if(merchants_warn_number>=0 && merchants_warn_number<100){
					$("*[ectype='goodMsg']").append('<p><a href="javascript:void(0);" data-url="goods.php?act=list&warn_number=1&seller_list=1" data-param="menushopping|01_goods_list" target="workspace" class="message">商家普通商品库存预警</a> <span class="tiptool">（<em id="goods_report">'+merchants_warn_number+'</em>）</span></p>')
				}else if(merchants_warn_number>99){
					$("*[ectype='goodMsg']").append('<p><a href="javascript:void(0);" data-url="goods.php?act=list$warn_number=1&seller_list=1" data-param="menushopping|01_goods_list" target="workspace" class="message">商家普通商品库存预警</a><span class="tiptool">（<em id="goods_report">99+</em>）</span></p>')
				}
				//商家商品库存预警值 end
			//商品提醒 end
			
			//商家提醒 start
				//待审核商家 start
				if(shop_account >= 0  && shop_account<100){
					$("*[ectype='sellerMsg']").html("").append('<p><a href="javascript:void(0);" data-url="merchants_users_list.php?act=list&check=1" data-param="menushopping|02_merchants_users_list" target="workspace" class="message">未审核商家</a> <span class="tiptool">（<em id="shop_account">'+shop_account+'</em>）</span></p>')
				}else if(shop_account > 99){
					$("*[ectype='sellerMsg']").html("").append('<p><a href="javascript:void(0);" data-url="merchants_users_list.php?act=list&check=1" data-param="menushopping|02_merchants_users_list" class="message">未审核商家</a><span class="tiptool">（<em id="shop_account">99+</em>）</span></p>')
				}
				//待审核商家 end
				
				//待审核店铺信息 start
				if(shopinfo_account >= 0  && shopinfo_account<100){
					$("*[ectype='sellerMsg']").append('<p><a href="javascript:void(0);" data-url="merchants_users_list.php?act=list&shopinfo_check=1" data-param="menushopping|02_merchants_users_list" target="workspace" class="message">未审核店铺信息</a> <span class="tiptool">（<em id="shopinfo_account">'+shopinfo_account+'</em>）</span></p>')
				}else if(shopinfo_account > 99){
					$("*[ectype='sellerMsg']").append('<p><a href="javascript:void(0);" data-url="merchants_users_list.php?act=list&shopinfo_check=1" data-param="menushopping|02_merchants_users_list" class="message">未审核店铺信息</a><span class="tiptool">（<em id="shopinfo_account">99+</em>）</span></p>')
				}
				//待审核店铺信息 start	
				
				//待审核商家提现 start
				if(wait_cash >= 0  && wait_cash<100){
					$("*[ectype='sellerMsg']").append('<p><a href="javascript:void(0);" data-url="merchants_account.php?act=list&act_type=account_log&handler=2&rawals=1" data-param="menushopping|12_seller_account" target="workspace" class="message">待审核商家提现</a> <span class="tiptool">（<em id="wait_cash">'+wait_cash+'</em>）</span></p>')
				}else if(wait_cash > 99){
					$("*[ectype='sellerMsg']").append('<p><a href="javascript:void(0);" data-url="merchants_account.php?act=list&act_type=account_log&handler=2&rawals=1" data-param="menushopping|12_seller_account" target="workspace" class="message">待审核商家提现</a><span class="tiptool">（<em id="wait_cash">99+</em>）</span></p>')
				}
				//待审核商家提现 end
				
				//待审核商家实名认证 start
				if(seller_account >= 0  && seller_account<100){
					$("*[ectype='sellerMsg']").append('<p><a href="javascript:void(0);" data-url="user_real.php?act=list&review_status=0&user_type=1" data-param="menushopping|16_users_real" target="workspace" class="message">待审核商家实名认证</a> <span class="tiptool">（<em id="seller_account">'+seller_account+'</em>）</span></p>')
				}else if(shop_account > 99){
					$("*[ectype='sellerMsg']").append('<p><a href="javascript:void(0);" data-url="user_real.php?act=list&review_status=0&user_type=1" data-param="menushopping|16_users_real" target="workspace" class="message">待审核商家实名认证</a><span class="tiptool">（<em id="seller_account">99+</em>）</span></p>')
				}
				//待审核商家实名认证 end
				
				//待审核商家结算 start
				if(wait_balance >= 0  && wait_balance<100){
					$("*[ectype='sellerMsg']").append('<p><a href="javascript:void(0);" data-url="merchants_account.php?act=list&act_type=detail&log_type=2" data-param="menushopping|12_seller_account" target="workspace" class="message">待审核商家结算</a> <span class="tiptool">（<em id="seller_account">'+wait_balance+'</em>）</span></p>')
				}else if(wait_balance > 99){
					$("*[ectype='sellerMsg']").append('<p><a href="javascript:void(0);" data-url="merchants_account.php?act=list&act_type=detail&log_type=2" data-param="menushopping|12_seller_account" target="workspace" class="message">待审核商家结算</a><span class="tiptool">（<em id="seller_account">99+</em>）</span></p>')
				}
				//待审核商家结算 end
				
				//待审核商家充值 start
				if(wait_recharge >= 0  && wait_recharge<100){
					$("*[ectype='sellerMsg']").append('<p><a href="javascript:void(0);" data-url="merchants_account.php?act=list&act_type=detail&log_type=3" data-param="menushopping|12_seller_account" target="workspace" class="message">待审核商家充值</a> <span class="tiptool">（<em id="seller_account">'+wait_recharge+'</em>）</span></p>')
				}else if(wait_recharge > 99){
					$("*[ectype='sellerMsg']").append('<p><a href="javascript:void(0);" data-url="merchants_account.php?act=list&act_type=detail&log_type=3" data-param="menushopping|12_seller_account" target="workspace" class="message">待审核商家充值</a><span class="tiptool">（<em id="seller_account">99+</em>）</span></p>')
				}
				//待审核商家充值 end
				
				//待审核店铺等级 start
				if(seller_apply >= 0  && seller_apply<100){
					$("*[ectype='sellerMsg']").append('<p><a href="javascript:void(0);" data-url="seller_apply.php?act=list" data-param="menushopping|02_merchants_users_list" target="workspace" class="message">待审核店铺等级</a> <span class="tiptool">（<em id="seller_account">'+seller_apply+'</em>）</span></p>')
				}else if(seller_apply > 99){
					$("*[ectype='sellerMsg']").append('<p><a href="javascript:void(0);" data-url="seller_apply.php?act=list" data-param="menushopping|02_merchants_users_list" target="workspace" class="message">待审核店铺等级</a><span class="tiptool">（<em id="seller_account">99+</em>）</span></p>')
				}
				//待审核店铺等级 end
			//商家提醒 end
			
			//广告位提醒 start
			if(advance_date >= 0  && advance_date<100){
                $("*[ectype='advMsg']").html("").append('<p><a href="javascript:void(0);" data-url="ads.php?act=list&advance_date=1" data-param="menuplatform|ad_list" target="workspace" class="message">广告位即将到期</a> <span class="tiptool">（<em id="advance_date">'+advance_date+'</em>）</span></p>')
            }else if(advance_date > 99){
                $("*[ectype='advMsg']").html("").append('<p><a href="javascript:void(0);" data-url="ads.php?act=list&advance_date=1" data-param="menushopping|ad_list" target="workspace" class="message">广告即将位到期</a><span class="tiptool">（<em id="advance_date">99+</em>）</span></p>')
            }
			//广告位提醒 end
			
			//会员提醒 start
				//未处理会员实名认证 start
				if(user_account >= 0  && user_account<100){
					$("*[ectype='userMsg']").html("").append('<p><a href="javascript:void(0);" data-url="user_real.php?act=list&review_status=0&user_type=0" data-param="menuplatform|03_users_list" target="workspace" class="message">会员实名认证</a> <span class="tiptool">（<em id="user_account">'+user_account+'</em>）</span></p>')
				}else if(user_account > 99){
					$("*[ectype='userMsg']").html("").append('<p><a href="javascript:void(0);" data-url="user_real.php?act=list&review_status=0&user_type=0" data-param="menushopping|03_users_list" target="workspace" class="message">会员实名认证</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未处理会员实名认证 end
				
				//未处理会员充值申请 start
				if(user_recharge >= 0  && user_recharge<100){
					$("*[ectype='userMsg']").append('<p><a href="javascript:void(0);" data-url="user_account.php?act=list&process_type=0&is_paid=0" data-param="menuplatform|09_user_account" target="workspace" class="message">会员充值申请</a> <span class="tiptool">（<em id="user_account">'+user_recharge+'</em>）</span></p>')
				}else if(user_recharge > 99){
					$("*[ectype='userMsg']").append('<p><a href="javascript:void(0);" data-url="user_account.php?act=list&process_type=0&is_paid=0" data-param="menushopping|09_user_account" target="workspace" class="message">会员充值申请</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未处理会员充值申请 end
				
				//未处理会员提现申请 start
				if(user_withdraw >= 0  && user_withdraw<100){
					$("*[ectype='userMsg']").append('<p><a href="javascript:void(0);" data-url="user_account.php?act=list&process_type=1&is_paid=0" data-param="menuplatform|09_user_account" target="workspace" class="message">会员提现申请</a> <span class="tiptool">（<em id="user_account">'+user_withdraw+'</em>）</span></p>')
				}else if(user_withdraw > 99){
					$("*[ectype='userMsg']").append('<p><a href="javascript:void(0);" data-url="user_account.php?act=list&process_type=1&is_paid=0" data-param="menushopping|09_user_account" target="workspace" class="message">会员提现申请</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未处理会员提现申请 end
				
				//未处理会员增票资质审核 start
				if(user_vat >= 0  && user_vat<100){
					$("*[ectype='userMsg']").append('<p><a href="javascript:void(0);" data-url="user_vat.php?act=list&audit_status=0" data-param="menuplatform|15_user_vat_info" target="workspace" class="message">会员增票资质审核</a> <span class="tiptool">（<em id="user_account">'+user_vat+'</em>）</span></p>')
				}else if(user_vat > 99){
					$("*[ectype='userMsg']").append('<p><a href="javascript:void(0);" data-url="user_vat.php?act=list&audit_status=0" data-param="menushopping|15_user_vat_info" target="workspace" class="message">会员增票资质审核</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未处理会员增票资质审核 end

				//网友讨论圈审核 start
				if(user_discuss >= 0  && user_discuss<100){
					$("*[ectype='userMsg']").append('<p><a href="javascript:void(0);" data-url="discuss_circle.php?act=list" target="workspace" class="message">网友讨论圈审核</a> <span class="tiptool">（<em id="user_discuss">'+user_discuss+'</em>）</span></p>')
				}else if(user_discuss > 99){
					$("*[ectype='userMsg']").append('<p><a href="javascript:void(0);" data-url="discuss_circle.php?act=list" target="workspace" class="message">网友讨论圈审核</a><span class="tiptool">（<em id="user_discuss">99+</em>）</span></p>')
				}
				//网友讨论圈审核 end
			//会员提醒 end
			
			//促销活动提醒 start
				//未审核夺宝奇兵 start
				if(snatch >= 0  && snatch<100){
					$("*[ectype='promotionMsg']").html("").append('<p><a href="javascript:void(0);" data-url="snatch.php?act=list&seller_list=1&review_status=1" data-param="menushopping|02_snatch_list" target="workspace" class="message">夺宝奇兵</a> <span class="tiptool">（<em id="user_account">'+snatch+'</em>）</span></p>')
				}else if(snatch > 99){
					$("*[ectype='promotionMsg']").html("").append('<p><a href="javascript:void(0);" data-url="snatch.php?act=list&seller_list=1&review_status=1" data-param="menushopping|02_snatch_list" target="workspace" class="message">夺宝奇兵</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未审核夺宝奇兵 end
				
				//未审核红包类型 start
				if(bonus_type >= 0  && bonus_type<100){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="bonus.php?act=list&seller_list=1&review_status=1" data-param="menushopping|04_bonustype_list" target="workspace" class="message">红包类型</a> <span class="tiptool">（<em id="user_account">'+bonus_type+'</em>）</span></p>')
				}else if(bonus_type > 99){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="bonus.php?act=list&seller_list=1&review_status=1" data-param="menushopping|04_bonustype_list" target="workspace" class="message">红包类型</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未审核红包类型 end
				
				//未审核团购活动 start
				if(group_by >= 0  && group_by<100){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="group_buy.php?act=list&seller_list=1&review_status=1" data-param="menushopping|08_group_buy" target="workspace" class="message">团购活动</a> <span class="tiptool">（<em id="user_account">'+group_by+'</em>）</span></p>')
				}else if(group_by > 99){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="group_buy.php?act=list&seller_list=1&review_status=1" data-param="menushopping|08_group_buy" target="workspace" class="message">团购活动</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未审核团购活动 end
				
				//未审核专题 start
				if(topic >= 0  && topic<100){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="topic.php?act=list&seller_list=1&review_status=1" data-param="menushopping|09_topic" target="workspace" class="message">专题</a> <span class="tiptool">（<em id="user_account">'+topic+'</em>）</span></p>')
				}else if(topic > 99){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="topic.php?act=list&seller_list=1&review_status=1" data-param="menushopping|09_topic" target="workspace" class="message">专题</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未审核专题 end
				
				//未审核拍卖活动 start
				if(auction >= 0  && auction<100){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="auction.php?act=list&seller_list=1&review_status=1" data-param="menushopping|10_auction" target="workspace" class="message">拍卖活动</a> <span class="tiptool">（<em id="user_account">'+auction+'</em>）</span></p>')
				}else if(auction > 99){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="auction.php?act=list&seller_list=1&review_status=1" data-param="menushopping|10_auction" target="workspace" class="message">拍卖活动</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未审核拍卖活动 end
				
				//未审核优惠活动 start
				if(favourable >= 0  && favourable<100){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="favourable.php?act=list&seller_list=1&review_status=1" data-param="menushopping|12_favourable" target="workspace" class="message">优惠活动</a> <span class="tiptool">（<em id="user_account">'+favourable+'</em>）</span></p>')
				}else if(favourable > 99){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="favourable.php?act=list&seller_list=1&review_status=1" data-param="menushopping|12_favourable" target="workspace" class="message">优惠活动</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未审核优惠活动 end
				
				//未审核预售活动 start
				if(presale >= 0  && presale<100){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="presale.php?act=list&seller_list=1&review_status=1" data-param="menushopping|16_presale" target="workspace" class="message">预售活动</a> <span class="tiptool">（<em id="user_account">'+presale+'</em>）</span></p>')
				}else if(presale > 99){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="presale.php?act=list&seller_list=1&review_status=1" data-param="menushopping|16_presale" target="workspace" class="message">预售活动</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未审核预售活动 end
				
				//未审核超值礼包 start
				if(package_goods >= 0  && package_goods<100){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="package.php?act=list&seller_list=1&review_status=1" data-param="menushopping|14_package_list" target="workspace" class="message">超值礼包</a> <span class="tiptool">（<em id="user_account">'+package_goods+'</em>）</span></p>')
				}else if(package_goods > 99){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="package.php?act=list&seller_list=1&review_status=1" data-param="menushopping|14_package_list" target="workspace" class="message">超值礼包</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未审核超值礼包 end
				
				//未审核积分商品 start
				if(exchange_goods >= 0  && exchange_goods<100){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="exchange_goods.php?act=list&seller_list=1&review_status=1" data-param="menushopping|15_exchange_goods" target="workspace" class="message">积分商品</a> <span class="tiptool">（<em id="user_account">'+exchange_goods+'</em>）</span></p>')
				}else if(exchange_goods > 99){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="exchange_goods.php?act=list&seller_list=1&review_status=1" data-param="menushopping|15_exchange_goods" target="workspace" class="message">积分商品</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未审核积分商品 end
				
				//未审核优惠券 start
				if(coupons >= 0  && coupons<100){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="coupons.php?act=list&seller_list=1&review_status=1" data-param="menushopping|17_coupons" target="workspace" class="message">优惠券</a> <span class="tiptool">（<em id="user_account">'+coupons+'</em>）</span></p>')
				}else if(coupons > 99){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="coupons.php?act=list&seller_list=1&review_status=1" data-param="menushopping|17_coupons" target="workspace" class="message">优惠券</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未审核优惠券 end
				
				//未审核礼品卡 start
				if(gift_gard >= 0  && gift_gard<100){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="gift_gard.php?act=list&seller_list=1&review_status=1" data-param="menushopping|gift_gard_list" target="workspace" class="message">礼品卡</a> <span class="tiptool">（<em id="user_account">'+gift_gard+'</em>）</span></p>')
				}else if(gift_gard > 99){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="gift_gard.php?act=list&seller_list=1&review_status=1" data-param="menushopping|gift_gard_list" target="workspace" class="message">礼品卡</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未审核礼品卡 end
				
				//未审核批发 start
				if(wholesale >= 0  && wholesale<100){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="wholesale.php?act=list&seller_list=1&review_status=1" data-param="menushopping|13_wholesale" target="workspace" class="message">批发</a> <span class="tiptool">（<em id="user_account">'+wholesale+'</em>）</span></p>')
				}else if(wholesale > 99){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="wholesale.php?act=list&seller_list=1&review_status=1" data-param="menushopping|13_wholesale" target="workspace" class="message">批发</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
				}
				//未审核批发 end
			//促销活动提醒 end
        }else{
			$('#msg_Container').append('<div class="no_msg">暂无消息！</div>')
        }
  }
  catch (e) { }
}

/* *
 * 开始检查账单；
 */
function startCheckBill()
{
  checkBill();
  window.setInterval("checkBill()", NEW_ORDER_INTERVAL);
}

/*
 * 检查订单
 */
function checkBill()
{
	
	var seller_id;
	
	if($(":input[name='bill_seller']").length > 0){
		seller_id = $(":input[name='bill_seller']").val();
	}else{
		seller_id = 0;
	}
	
	Ajax.call('index.php?is_ajax=1&act=check_bill','seller_id=' + seller_id, checkBillResponse, 'GET', 'JSON');
}

/* *
 * 处理检查账单的反馈信息
 */
function checkBillResponse(result)
{
  //出错屏蔽
  if (result.error != 0)
  {
    return;
  }
  try
  {
  }
  catch (e) { }
}

/**
 * 确认后跳转到指定的URL
 */
function confirm_redirect(msg, url)
{
  if (confirm(msg))
  {
    location.href=url;
  }
}

/* *
 * 设置页面宽度
 */
function set_size(w)
{
  var y_width = document.body.clientWidth
  var s_width = screen.width
  var agent   = navigator.userAgent.toLowerCase();

  if (y_width < w)
  {
    if (agent.indexOf("msie") != - 1)
    {
      document.body.style.width = w + "px";
    }
    else
    {
      document.getElementById("bd").style.width = (w - 10) + 'px';
    }
  }
}

/* *
 * 显示隐藏图片
 * @param   id  div的id
 * @param   show | hide
 */
function showImg(id, act)
{
  if (act == 'show')
  {
    document.getElementById(id).style.visibility = 'visible';
  }
  else
  {
    document.getElementById(id).style.visibility = 'hidden';
  }
}

/*
 * 气泡式提示信息
 */
var Message = Object();

Message.bottom  = 0;
Message.count   = 0;
Message.elem    = "popMsg";
Message.mvTimer = null;

Message.show = function()
{
  try
  {
    Message.controlSound('msgBeep');
    document.getElementById(Message.elem).style.visibility = "visible"
    document.getElementById(Message.elem).style.display = "block"

    Message.bottom  = 0 - parseInt(document.getElementById(Message.elem).offsetHeight);
    Message.mvTimer = window.setInterval("Message.move()", 10);

    document.getElementById(Message.elem).style.bottom = Message.bottom + "px";
  }
  catch (e)
  {
    alert(e);
  }
}

Message.move = function()
{
  try
  {
    if (Message.bottom == 0)
    {
      window.clearInterval(Message.mvTimer)
      Message.mvTimer = window.setInterval("Message.close()", 5000)
    }

    Message.bottom ++ ;
    document.getElementById(Message.elem).style.bottom = Message.bottom + "px";
  }
  catch (e)
  {
    alert(e);
  }
}

Message.close = function()
{
  document.getElementById(Message.elem).style.visibility = 'hidden';
  document.getElementById(Message.elem).style.display = 'none';
  if (Message.mvTimer) window.clearInterval(Message.mvTimer)
}

Message.controlSound = function(_sndObj)
{
  sndObj = document.getElementById(_sndObj);

  try
  {
    sndObj.Play();
  }
  catch (e) { }
}

var listZone = new Object();

/* *
 * 显示正在载入
 */
listZone.showLoader = function()
{
  listZone.toggleLoader(true);
}

listZone.hideLoader = function()
{
  listZone.toggleLoader(false);
}

listZone.toggleLoader = function(disp)
{
  document.getElementsByTagName('body').item(0).style.cursor = (disp) ? "wait" : 'auto';

  try
  {
    var doc = top.frames['header-frame'].document;
    var loader = doc.getElementById("load-div");

    if (typeof loader == 'object') loader.style.display = disp ? "block" : "none";
  }
  catch (ex) { }
}

function $import(path,type,title){
  var s,i;
  if(type == "js"){
    var ss = document.getElementsByTagName("script");
    for(i =0;i < ss.length; i++)
    {
      if(ss[i].src && ss[i].src.indexOf(path) != -1)return ss[i];
    }
    s      = document.createElement("script");
    s.type = "text/javascript";
    s.src  =path;
  }
  else if(type == "css")
  {
    var ls = document.getElementsByTagName("link");
    for(i = 0; i < ls.length; i++)
    {
      if(ls[i].href && ls[i].href.indexOf(path)!=-1)return ls[i];
    }
    s          = document.createElement("link");
    s.rel      = "alternate stylesheet";
    s.type     = "text/css";
    s.href     = path;
    s.title    = title;
    s.disabled = false;
  }
  else return;
  var head = document.getElementsByTagName("head")[0];
  head.appendChild(s);
  return s;
}

/**
 * 返回随机数字符串
 *
 * @param : prefix  前缀字符
 *
 * @return : string
 */
function rand_str(prefix)
{
  var dd = new Date();
  var tt = dd.getTime();
  tt = prefix + tt;

  var rand = Math.random();
  rand = Math.floor(rand * 100);

  return (tt + rand);
}

// 分类分级 by qin

function catList(val, level)
{
    var cat_id = val;
    document.getElementById('cat_id').value = cat_id;
    Ajax.call('goods.php?is_ajax=1&act=sel_cat', 'cat_id='+cat_id+'&cat_level='+level, catListResponse, 'GET', 'JSON');
}

function catListResponse(result)
{
    if (result.error == '1' && result.message != '')
    {
      alert(result.message);
      return;
    }
    var response = result.content;
    var cat_level = result.cat_level; // 分类级别， 1为顶级分类
    for(var i=cat_level;i<10;i++)
    {
      $("#cat_list"+Number(i+1)).remove();
    }
    if(response)
    {
        $("#cat_list"+cat_level).after(response);
    }
	
	if(document.getElementById('cat_level')){
		if(result.parent_id == 0){
			cat_level = 0;
		}
		document.getElementById('cat_level').value = cat_level;
	}
  return;
}

/*
 * 获取选择分类下拉列表by wu
 * cat_id:选择的分类id 
 * cat_level:选择的分类id等级
 * select_jsId:需要赋值的input id,为0,则将值赋给同级
 */
function get_select_category(cat_id,cat_level,select_jsId,type)
{
	//需要赋值的input
	var obj=$("#"+select_jsId);
	
	//当前页面url
	//var page_url=window.location.href.replace(/\?(.)+/g,'');
	var page_url='get_ajax_content.php';
	
	//给input赋值
	switch(type)
	{
		case 0: obj.val(cat_id+'_'+(cat_level-1)); break;
		case 1: obj.val(cat_id); break;
		case 2: obj.val(cat_level); break;
		default: obj.val(cat_id+'_'+(cat_level-1)); break;
	}	
	
	//移除该级的其他子分类列表
	obj.siblings("select[cat-type=select]").each(function(){
		if($(this).attr('cat-level')>cat_level)
		{
			$(this).remove();
		}
		else
		{
			if(cat_id==0 && cat_level==1)
			{
				switch(type)
				{
					case 0: obj.val('0_0'); break;
					case 1: obj.val('0'); break;
					case 2: obj.val('0'); break;
					default: obj.val('0_0'); break;
				}
			}
			if(cat_id==0 && cat_level>1)
			{
				switch(type)
				{
					case 0: obj.val($(this).prev().val()+'_'+($(this).prev().attr('cat-level')-1)); break;
					case 1: obj.val($(this).prev().val()); break;
					case 2: obj.val($(this).prev().attr('cat-level')-1); break;
					default: obj.val($(this).prev().val()+'_'+($(this).prev().attr('cat-level')-1)); break;
				}
			}
		}
	});

	if(cat_id>0)
	{	
		//加载选择分类的子分类列表
		$.ajax({
			type:'get',
			url:page_url,
			data:'act=get_select_category&cat_id='+cat_id+'&cat_level='+cat_level+'&select_jsId='+select_jsId+'&type='+type,
			dataType:'json',
			success:function(data){
				if(data.error==1)
				{			
					obj.siblings("select[cat-type=select]").last().after(data.content);
				}
			}		
		});
	}
}

//筛选显示分类 by wu
function filter_category(cat_id,cat_level,select_jsId)
{
	var obj=$("#"+select_jsId);
	obj.children('option').each(function(){
		var val=$(this).val();		
		var valArr=val.split('_');
		if(valArr[1]>cat_level)
		{
			$(this).hide();
		}
	});
}

//删除图片 type：图片类型；id：数据编号；key：图片序号
function deleteImage(obj, type, id, key)
{
	obj = $(obj);
	var t = confirm("您确定要删除这张图片么？");
	if(t)
	{
		$.ajax({
			type:'get',
			url:'zc_project.php',
			data:'act=delete_image&type='+type+'&id='+id+'&key='+key,
			dataType:'json',
			success:function(data)
			{
				window.location.reload();
			}
		});
	}
}

//jqueryAjax异步加载
$.jqueryAjax = function(url, data, ajaxFunc, type, dataType)
{
	var baseData = "is_ajax=1&";
	var baseFunc = function(){}
	
	if(!url)
	{
		url = "index.php";
	}
	
	if(!data)
	{
		data = "";
	}
	
	if(!type)
	{
		type = "get";
	}
	
	if(!dataType)
	{
		dataType = "json";
	}
	
	if(!ajaxFunc)
	{
		ajaxFunc = baseFunc;
	}
	
	data = baseData + data;
	
	$.ajax({
		type:type,
		url:url,
		data:data,
		dataType:dataType,
		success:ajaxFunc.success? ajaxFunc.success:ajaxFunc,
		error:ajaxFunc.error? ajaxFunc.error:baseFunc,
		beforeSend:ajaxFunc.beforeSend? ajaxFunc.beforeSend:baseFunc,
		complete:ajaxFunc.complete? ajaxFunc.complete:baseFunc,
		//dataFilter:ajaxFunc.dataFilter? ajaxFunc.dataFilter:baseFunc
	});	
}

//设置商品分类 by wu
function get_select_category_pro(obj, cat_id, cat_level, goods_id)
{
	var obj = $(obj);
	var thisSection = obj.parents(".sort_info");
	
	var ex_goods = '';
	if(goods_id){
		ex_goods = '&goods_id=' + goods_id;
	}
	
	$.jqueryAjax('goods.php', 'act=get_select_category_pro&cat_id=' + cat_id + '&cat_level=' + cat_level + ex_goods, function(data){
		if(cat_id == 0){
			var parent_id1 = thisSection.find("ul[data-cat_level="+(cat_level-1)+"] li.current").data("cat_id"); //上一级
			var parent_id2 = thisSection.find("ul[data-cat_level="+(cat_level-2)+"] li.current").data("cat_id"); //上上一级
			parent_id = parent_id1? parent_id1:parent_id2? parent_id2:0; //如果都没有，则cat_id=0
			thisSection.find("input[ectype=cat_id]").val(parent_id); //设置分类id
			thisSection.find("ul[data-cat_level="+(cat_level+1)+"] li:gt(0)").remove(); //除第一行，其他移除
			thisSection.find("ul[data-cat_level="+(cat_level+1)+"] li:first").removeClass("current"); //去除第一行的选中效果
		}else{
			thisSection.find("input[ectype=cat_id]").val(cat_id); //设置分类id
			thisSection.find("ul[data-cat_level="+(cat_level+1)+"]").html(data.content); //异步加载内容
			$(".category_list").perfectScrollbar('destroy');
			$(".category_list").perfectScrollbar();
		}
		thisSection.find("ul[data-cat_level="+(cat_level+2)+"] li:gt(0)").remove(); //除第一行，其他移除
		thisSection.find("ul[data-cat_level="+(cat_level+2)+"] li:first").removeClass("current"); //去除第一行的选中效果
		
		//拓展分类不能修改分类导航
		if(obj.parents("#extension_category").length == 0){
			set_cat_nav();
		}
	});
}

//设置分类导航 by wu
function set_cat_nav()
{
	var cat_nav = "";
	$("ul[ectype='category']").each(function(){
		var category = $(this).find("li.current a").text();
		if(category){
			if($(this).data("cat_level") == 1){
				cat_nav += category;
			}else{
				cat_nav += " > " + category;
			}		
		}		
	})
	$("#choiceClass strong").html(cat_nav);
	$(".edit_category").siblings("span").html(cat_nav);
}

//添加/删除扩展分类
function deal_extension_category(obj, goods_id, cat_id, type)
{
    var other_catids = $("#other_catids").val();
	var obj = $(obj);
	$.jqueryAjax("goods.php", "act=deal_extension_category&goods_id="+goods_id+"&cat_id="+cat_id+"&type="+type + "&other_catids="+other_catids, function(data){
		$("#other_catids").val(data.content);
	});
}

//设置属性表格
function set_attribute_table(goodsId , type, city_id)
{
	var attr_id_arr = [];
	var attr_value_arr = [];
	var attrId = $("#tbody-goodsAttr").find("input[type=checkbox][data-type=attr_id]:checked");
	//var attrValue = $("#tbody-goodsAttr").find("input[type=checkbox][data-type=attr_value]:checked");
	var attrValue = attrId.siblings("input[type=checkbox][data-type=attr_value]");
	attrId.each(function(){
		attr_id_arr.push($(this).val());
	});
	attrValue.each(function(){
		
		/**
		*过滤ajax传值加号问题
		*/

		var dataVal = $(this).val();
		dataVal = dataVal.replace(/\+/g, "%2B");
    	dataVal = dataVal.replace(/\&/g, "%26");
		dataVal = dataVal.replace(/\#/g, "%23");
		dataVal = dataVal.replace(/\//g, "%2F");
		dataVal = dataVal.replace(/\@/g, "%40");
		dataVal = dataVal.replace(/\$/g, "%24");
		dataVal = dataVal.replace(/\*/g, "%2A");
	
		attr_value_arr.push(dataVal);
	});

	//商品模式
	var extension = "";
	var goods_model = $("input[name=model_price]").val(); 
	var warehouse_id = $("#attribute_model").find("input[type=radio][data-type=warehouse_id]:checked").val();
	
	if($("#attribute_city_region").length > 0){
		if(type != 2){
			var region_id = $("#attribute_model").find("input[type=radio][data-type=region_id]:checked").val();
			var city_id = $("#attribute_city_region").find("input[type=radio][data-type=city_region_id]:checked").val();
		}else{
			var region_id = $("#attribute_region").find("input[type=radio][data-warehouse=" + warehouse_id + "]:first").val();
		}
	}else{
		var region_id = $("#attribute_model").find("input[type=radio][data-type=region_id]:checked").val();
	}
	
	if(type != 3){
		var warehouse_obj = $("#attribute_region .value[data-wareid="+warehouse_id+"]");
		warehouse_obj.find("input[type=radio]:first").prop("checked", true);
	}
	
	extension += "&goods_model="+goods_model;
	if(goods_model == 1){
		extension += "&region_id="+warehouse_id;
	}else if(goods_model == 2){
		if($("#attribute_city_region").length > 0){
			extension += "&region_id="+region_id + "&city_id="+city_id;
		}else{
			extension += "&region_id="+region_id;
		}
	}
	
	var goods_type = $("input[name='goods_type']").val();
	if(goods_type > 0){
		extension += "&goods_type="+goods_type;
	}
	
	//获取筛选项
	if(type == 1){
		var search_attr = '';
		$("*[ectype='attr_search_main']").find(".select").each(function(){
			var search_val = $(this).find('input[type="hidden"]').val();
			if(search_val){
				if(search_attr){
					search_attr = search_attr + "," + search_val
				}else{
					search_attr = search_val;
				}
			}
		});
		extension += "&search_attr=" + search_attr;
	}
	$.jqueryAjax('goods.php', 'act=set_attribute_table&goods_id='+goodsId+'&attr_id='+attr_id_arr+'&attr_value='+attr_value_arr+extension, function(data){
		$("#attribute_table").html(data.content);
		/*处理属性图片 start*/
		$("#goods_attr_gallery").html(data.goods_attr_gallery);
		/*处理属性图片 end*/
	})

//getAttrList(goodsId);
}
  
function ajax_title(){
	var content = "<div class='list-div'> " + 
					"<table cellpadding='0' cellspacing='0' border='0'>" +
						"<tbody>" +
							"<tr>" +
								"<td align='center'>&nbsp;</td>" +
							"</tr>" +
							"<tr>" +
								"<td align='center'><div class='ml10' id='title_name'></div></td>" +
							"</tr>" +
							"<tr>" +
								"<td align='center'>&nbsp;</td>" +
							"</tr>" +
						"</tbody>" +
					"</table>   " +     
				"</div>";
	pb({
		id:"categroy_dialog",
		title:"温馨提示",
		width:588,
		content:content,
		ok_title:"确定",
		drag:false,
		foot:false,
		cl_cBtn:false,
	});
}

/* 设置单选项默认第一个选中 */
function checked_prop(name){
	$(":input[name='" + name + "']").eq(0).prop("checked", true);
}

//设置属性表格
function wholesale_set_attribute_table(goodsId , type)
{
	var attr_id_arr = [];
	var attr_value_arr = [];
	var attrId = $("#tbody-wholesale-goodsAttr").find("input[type=checkbox][data-type=attr_id]:checked");
	//var attrValue = $("#tbody-wholesale-goodsAttr").find("input[type=checkbox][data-type=attr_value]:checked");
	var attrValue = attrId.siblings("input[type=checkbox][data-type=attr_value]");
	attrId.each(function(){
		attr_id_arr.push($(this).val());
	});
	
	attrValue.each(function(){
		
		/**
		*过滤ajax传值加号问题
		*/

		var dataVal = $(this).val();
		dataVal = dataVal.replace(/\+/g, "%2B");
    	dataVal = dataVal.replace(/\&/g, "%26");
		dataVal = dataVal.replace(/\#/g, "%23");
		dataVal = dataVal.replace(/\//g, "%2F");
		dataVal = dataVal.replace(/\@/g, "%40");
		dataVal = dataVal.replace(/\$/g, "%24");
		dataVal = dataVal.replace(/\*/g, "%2A");
	
		attr_value_arr.push(dataVal);
	});

	//商品模式
	var extension = "";
	var goods_model = $("input[name=model_price]").val(); 
	var warehouse_id = $("#attribute_model").find("input[type=radio][data-type=warehouse_id]:checked").val();
	var region_id = $("#attribute_model").find("input[type=radio][data-type=region_id]:checked").val();
	extension += "&goods_model="+goods_model;
	if(goods_model == 1){
		extension += "&region_id="+warehouse_id;
	}else if(goods_model == 2){
		extension += "&region_id="+region_id;
	}
	
	var goods_type = $("input[name='goods_type']").val();
	if(goods_type > 0){
		extension += "&goods_type="+goods_type;
	}
	//获取筛选项
	if(type == 1){
		var search_attr = '';
		$("*[ectype='attr_search_main']").find(".select").each(function(){
			var search_val = $(this).find('input[type="hidden"]').val();
			if(search_val){
				if(search_attr){
					search_attr = search_attr + "," + search_val
				}else{
					search_attr = search_val;
				}
			}
		});
		extension += "&search_attr=" + search_attr;
	}
	$.jqueryAjax('wholesale.php', 'act=set_attribute_table&goods_id='+goodsId+'&attr_id='+attr_id_arr+'&attr_value='+attr_value_arr+extension, function(data){
		$("#attribute_table").html(data.content);
		/*处理属性图片 start*/
		$("#goods_attr_gallery").html(data.goods_attr_gallery);
		/*处理属性图片 end*/		
	})

//getAttrList(goodsId);
}

/* 统计图表 */
function set_chart_view(chart_date, chart_id){
    var myChart = echarts.init(document.getElementById(chart_id));
    myChart.setOption(chart_date);

    //销售量
    if(chart_date.total_volume){
    	$("[ectype='total_volume']").text(chart_date.total_volume);
    }
    //销售额
    if(chart_date.total_money){
    	$("[ectype='total_money']").text(chart_date.total_money);
    }
}
function search_chart_view(url, formSelect, chart_id, ext_data){
    var search_data = $(formSelect).serialize();
    if(ext_data){
    	for(ext in ext_data){
    		search_data += '&'+ext+'='+ext_data[ext];
    	}
    }
    $.jqueryAjax(url, search_data, function(data){
        set_chart_view(data.content, chart_id);
    })
}