$(function(){
	//logo点击跳转到首页 
	$(".admin-logo a").on("click",function(){
		//var url = $(this).data('url');
		var param = $(this).data('param');
		
		//$(".admin-main").addClass("start_home");
		$(".admincj_nav").find(".item").eq(0).show();
		$(".admincj_nav").find(".sub-menu").hide();
		$(".module-menu").find("li").removeClass("active");
		
		openItem(param);
	});
	
	//顶部管理员信息展开
	function adminSetup(){
		var hoverTimer, outTimer;
		$('#admin-manager-btn,.manager-menu,.admincp-map').mouseenter(function(){
			clearTimeout(outTimer);
			hoverTimer = setTimeout(function(){
				$('.manager-menu').show();
				$('#admin-manager-btn i').removeClass().addClass("arrow-close");
			},200);
		});
		
		$('#admin-manager-btn,.manager-menu,.admincp-map').mouseleave(function(){
			clearTimeout(hoverTimer);
			outTimer = setTimeout(function(){
				$('.manager-menu').hide();
				$('#admin-manager-btn i').removeClass().addClass("arrow");
			},100);	
		});
	}
	adminSetup();
	
	function loadEach(){
		$('.admincj_nav').find('div[id^="adminNavTabs_"]').each(function(){
			var $this = $(this);
			
			var name = $this.attr("id").replace("adminNavTabs_","");
			
			$this.find('.item > .tit > a').each(function(i){
				$(this).parent().next().css('top', (-68)*i + 'px');
				$(this).click(function(){
					var type = $(this).parents(".item").data("type");
					if(type == "home"){
						var url = $(this).data('url');
						var param = $(this).data('param');
						
						//$(".admin-main").addClass("start_home");
						$(".admincj_nav").find(".item").eq(0).addClass("current").siblings().removeClass("current");
						$(".admincj_nav").find(".item").eq(0).show();
						$(".module-menu").find("li").removeClass("active");
						$this.find('.sub-menu').hide();
						openItem(param,1);
					}else{
						var url = '';
						$this.find('.sub-menu').hide();
						$this.find('.item').removeClass('current');
						if(name == "menushopping"){
							//商品 默认三级分类链接到第二个 商品列表
							var param = $(this).parent().next().find('a:first').data('param');
							var data_str = param.split('|');
							if($(this).parents('.item').index() == 0 && data_str[1] == "001_goods_setting"){
								$(this).parents('.item').eq(1).addClass('current');
								$(this).parent().next().find('a').eq(1).click();
								url = $(this).parent().next().find('a').eq(1).data('url');
							}else{
								$(this).parents('.item:first').addClass('current');
								$(this).parent().next().find('a:first').click();
								url = $(this).parent().next().find('a:first').data('url');
							}
						}else{
							$(this).parents('.item:first').addClass('current');
							$(this).parent().next().find('a:first').click();
							url = $(this).parent().next().find('a:first').data('url');
						}
						//$(".admin-main").removeClass("start_home");
						//loadUrl(url);
					}
				});
			});
		});
	}
	loadEach();
	
	//右侧二级导航选择切换
	$(".sub-menu li a").on("click",function(){
		var param = $(this).data("param");
		var url = $(this).data("url");
		if(param != null){
			loadUrl(url);
			openItem(param);
		}
	});
	
	//顶部导航栏菜单切换
	$(".module-menu li").on("click",function(){
		var modules = $(this).data("param");
		var items = $("#adminNavTabs_"+ modules).find(".item");
		var first_item = items.first();
		var default_a = "";
		
		items.find('.sub-menu').hide();
		$(this).addClass("active").siblings().removeClass("active");
		//$(".admin-main").removeClass("start_home");
		$("#adminNavTabs_" + modules).show().siblings().hide();
		items.removeClass("current");
		first_item.addClass('current');
		
		if(modules == "menushopping"){
			var param = first_item.find('li').find("a").data("param");
			var data_str = param.split('|');
			
			if(data_str[1] == "001_goods_setting"){
				default_a = first_item.find('li').eq(1).find("a");
			}else{
				default_a = first_item.find('li').eq(0).find("a");
			}
		}else{
			default_a = first_item.find('li').eq(0).find('a:first');
		}

		default_a.click();
		
		//var url = default_a.data("url");
		//loadUrl(url);
	});
	
	//后台提示
	$(document).on("click","#msg_Container .msg_content a",function(){
		var param = $(this).data("param");
		var url = $(this).data("url");
		
		loadUrl(url);
		openItem(param);
	});
	
	$(".foldsider").click(function(){
		var leftdiv = $(".admin-main");
		if(leftdiv.hasClass("fold")){
			leftdiv.removeClass("fold");
			$(this).find("i.icon").removeClass("icon-indent-right").addClass("icon-indent-left");
			leftdiv.find(".current").children(".sub-menu").show();
			
			loadEach();
		}else{
			leftdiv.addClass("fold");
			$(this).find("i.icon").removeClass("icon-indent-left").addClass("icon-indent-right");
			leftdiv.find(".sub-menu").hide();
			leftdiv.find(".sub-menu").css("top","0px");
		}
	});
	
	function ready(){
		var bwidth = $(window).width();
		
		if(bwidth < 1380){
			$(".foldsider").click();
		}
		
		$(window).resize(function(){
			bwidth = $(window).width();

			if(bwidth < 1380 && !$(".admin-main").hasClass("fold")){
				$(".foldsider").click();
			}
		});
	}
	
	ready();
	
	var foldHoverTimer, foldOutTimer,foldHoverTimer2;
	$(document).on("mouseenter",".fold .tit",function(){
		var $this = $(this);
		var items = $this.parents(".item");
		
		var length = items.find(".sub-menu").find("li").length;
		items.parent().find(".item:gt(5)").find(".sub-menu").css("top",-((40*length)-68));
		$this.next().show();
		items.addClass("current");
		items.siblings(".item").removeClass("current");
	});
	
	$(document).on("mouseleave",".fold .tit",function(){
		var $this = $(this);
		clearTimeout(foldHoverTimer);
		foldOutTimer = setTimeout(function(){
			$this.next().hide();
		});
	});
	
	$(document).on("mouseenter",".fold .sub-menu",function(){
		clearTimeout(foldOutTimer);
		var $this = $(this);
		foldHoverTimer2 = setTimeout(function(){
			$this.show();
		});
	});
	
	$(document).on("mouseleave",".fold .sub-menu",function(){
		var $this = $(this);
		$this.hide();
	});
	
	//没有cookie默认选择起始页
	if ($.cookie('dscActionParam') == null) {
        $('.admin-logo').find('a').click();
    } else {
        openItem($.cookie('dscActionParam'));
    }

	//顶部布局换色设置
	var bgColorSelectorColors = [{ c: '#981767', cName: '' }, { c: '#AD116B', cName: '' }, { c: '#B61944', cName: '' }, { c: '#AA1815', cName: '' }, { c: '#C4182D', cName: '' }, { c: '#D74641', cName: '' }, { c: '#ED6E4D', cName: '' }, { c: '#D78A67', cName: '' }, { c: '#F5A675', cName: '' }, { c: '#F8C888', cName: '' }, { c: '#F9D39B', cName: '' }, { c: '#F8DB87', cName: '' }, { c: '#FFD839', cName: '' }, { c: '#F9D12C', cName: '' }, { c: '#FABB3D', cName: '' }, { c: '#F8CB3C', cName: '' }, { c: '#F4E47E', cName: '' }, { c: '#F4ED87', cName: '' }, { c: '#DFE05E', cName: '' }, { c: '#CDCA5B', cName: '' }, { c: '#A8C03D', cName: '' }, { c: '#73A833', cName: '' }, { c: '#468E33', cName: '' }, { c: '#5CB147', cName: '' }, { c: '#6BB979', cName: '' }, { c: '#8EC89C', cName: '' }, { c: '#9AD0B9', cName: '' }, { c: '#97D3E3', cName: '' }, { c: '#7CCCEE', cName: '' }, { c: '#5AC3EC', cName: '' }, { c: '#16B8D8', cName: '' }, { c: '#49B4D6', cName: '' }, { c: '#6DB4E4', cName: '' }, { c: '#8DC2EA', cName: '' }, { c: '#BDB8DC', cName: '' }, { c: '#8381BD', cName: '' }, { c: '#7B6FB0', cName: '' }, { c: '#AA86BC', cName: '' }, { c: '#AA7AB3', cName: '' }, { c: '#935EA2', cName: '' }, { c: '#9D559C', cName: '' }, { c: '#C95C9D', cName: '' }, { c: '#DC75AB', cName: '' }, { c: '#EE7DAE', cName: '' }, { c: '#E6A5CA', cName: '' }, { c: '#EA94BE', cName: '' }, { c: '#D63F7D', cName: '' }, { c: '#C1374A', cName: '' }, { c: '#AB3255', cName: '' }, { c: '#A51263', cName: '' }, { c: '#7F285D', cName: ''}];
	$("#trace_show").click(function(){
		$("div.bgSelector").toggle(300, function() {
			if ($(this).html() == '') {
				$(this).sColor({
					colors: bgColorSelectorColors,  // 必填，所有颜色 c:色号（必填） cName:颜色名称（可空）
					colorsWidth: '50px',  // 必填，颜色的高度
					colorsHeight: '31px',  // 必填，颜色的高度
					curTop: '0', // 可选，颜色选择对象高偏移，默认0
					curImg: 'images/cur.png',  //必填，颜色选择对象图片路径
					form: 'drag', // 可选，切换方式，drag或click，默认drag
					keyEvent: true,  // 可选，开启键盘控制，默认true
					prevColor: true, // 可选，开启切换页面后背景色是上一页面所选背景色，如不填则换页后背景色是defaultItem，默认false
					defaultItem: ($.cookie('bgColorSelectorPosition') != null) ? $.cookie('bgColorSelectorPosition') : 22  // 可选，第几个颜色的索引作为初始颜色，默认第1个颜色
				});
			}
		});//切换显示
	});
	if ($.cookie('bgColorSelectorPosition') != null) {
		$('body').css('background-color', bgColorSelectorColors[$.cookie('bgColorSelectorPosition')].c);
	} else {
		$('body').css('background-color', bgColorSelectorColors[30].c);
	}

	//上传管理员头像
	$("#_pic").change(function(){
		var actionUrl = "index.php?act=upload_store_img";
		$("#fileForm").ajaxSubmit({
			type: "POST",
			dataType: "json",
			url: actionUrl,
			data: { "action": "TemporaryImage" },
			success: function (data) {
				if (data.error == "0") {
					alert(data.massege);
				} else if (data.error == "1") {
					$(".avatar img").attr("src", data.content);
				}
			},
			async: true
		});
	});

	/*  @author-bylu 添加快捷菜单 start  */
	$('.admincp-map-nav li').click(function(){
		var i = $(this).index();
		$(this).addClass('selected');
		$(this).siblings().removeClass('selected');
		$('.admincp-map-div').eq(i).show();
		$('.admincp-map-div').eq(i).siblings('.admincp-map-div').hide();
	});

	$('.admincp-map-div dd i').click(function(){
		var auth_name = $(this).prev('a').text();
		var auth_href = $(this).prev('a').attr('href');
		if(!$(this).parent('dd').hasClass('selected')){

			if($('.admincp-map-div dd.selected').length >=10){
				alert('最多只允许添加10个快捷菜单!');return false;
			}

			$(this).parent('dd').addClass('selected');
			$('.quick_link ul').append('<li class="tl"><a href="'+auth_href+'" data-url="'+auth_href+'" data-param="" target="workspace">'+auth_name+'</a></li>')

			$.post('index.php?act=auth_menu',{'type':'add','auth_name':auth_name,'auth_href':auth_href});

		}else{
			$(this).parent('dd').removeClass('selected');
			$('.quick_link ul li').each(function(k,v){
				if(auth_name == $(v).text()){
					$(v).remove();
				}
			});
			$.post('index.php?act=auth_menu',{'type':'del','auth_name':auth_name,'auth_href':auth_href});
		}
	});

	$('.add_nav,.sitemap').click(function(){
		$('#allMenu').show();
	});
        
	//消息通知
	function message(){
		var hoverTimer, outTimer;
		$("*[ectype='oper_msg']").mouseenter(function(){
			clearTimeout(outTimer);
			hoverTimer = setTimeout(function(){
				$('#msg_Container').show();
			},200);
		});
		
		$("*[ectype='oper_msg']").mouseleave(function(){
			clearTimeout(hoverTimer);
			outTimer = setTimeout(function(){
				$('#msg_Container').hide();
			},100);	
		});
	}
	
	message();
	
    Ajax.call('index.php?is_ajax=1&act=check_order','', function(data){
		// var wait_orders = data.wait_orders ? data.wait_orders :0;
		// var new_paid = data.new_paid ? data.new_paid :0;
		
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
			$('.msg').after('<s id="total">'+total+'</s>');
		}else if(total>99){
			$('.msg').after('<s><img src="images/gduo.png"></s>');
		} 
		
		//订单提示
		var order_total = parseInt(new_orders)+parseInt(await_ship)+parseInt(no_change)+parseInt(complaint)+parseInt(booking_goods);
		if(order_total >0){
			$('.order_msg').after('<s id="total">'+order_total+'</s>');
		}else{
			$('.order_msg').after('<s id="total">0</s>');
		}
		//商品提示
		var goods_total = parseInt(goods_report)+parseInt(sale_notice)+parseInt(no_check_goods)+parseInt(no_check_brand)+parseInt(self_warn_number)+parseInt(merchants_warn_number);
		if(goods_total >0){
			$('.goods_msg').after('<s id="total">'+goods_total+'</s>');
		}else{
			$('.goods_msg').after('<s id="total">0</s>');
		}
		//商家审核提示
		var shop_total = parseInt(shop_account)+parseInt(shopinfo_account)+parseInt(seller_account)+parseInt(wait_cash)+parseInt(wait_balance)+parseInt(wait_recharge)+parseInt(seller_apply);
		if(shop_total >0){
			$('.shop_msg').after('<s id="total">'+shop_total+'</s>');
		}else{
			$('.shop_msg').after('<s id="total">0</s>');
		}
		//广告位提示
		var ad_total = parseInt(advance_date);
		if(ad_total >0){
			$('.ad_msg').after('<s id="total">'+ad_total+'</s>');
		}else{
			$('.ad_msg').after('<s id="total">0</s>');
		}
		//会员提示
		var user_total = parseInt(user_account)+parseInt(user_recharge)+parseInt(user_withdraw)+parseInt(user_vat)+parseInt(user_discuss);
		if(user_total >0){
			$('.user_msg').after('<s id="total">'+user_total+'</s>');
		}else{
			$('.user_msg').after('<s id="total">0</s>');
		}
		//活动提示
		var campaign_total = parseInt(snatch)+parseInt(bonus_type)+parseInt(group_by)+parseInt(topic)+parseInt(auction)+parseInt(favourable)+parseInt(presale)+parseInt(package_goods)+parseInt(exchange_goods)+parseInt(coupons)+parseInt(gift_gard)+parseInt(wholesale);
		if(campaign_total >0){
			$('.campaign_msg').after('<s id="total">'+campaign_total+'</s>');
		}else{
			$('.campaign_msg').after('<s id="total">0</s>');
		}
		

        if(total != 0){
			//订单提醒 start
				//新订单 start
				if(new_orders>=0 && new_orders<100){
					$("*[ectype='orderMsg']").append('<p><a href="javascript:void(0);" data-url="order.php?act=list&composite_status=106" data-param="menushopping|02_order_list" target="workspace" class="message" >您有新订单</a> <span class="tiptool">（<em id="new_orders">'+new_orders+'</em>）</span></p>')
				}else if(new_orders>99){
					$("*[ectype='orderMsg']").append('<p><a href="javascript:void(0);" data-url="order.php?act=list&composite_status=106" data-param="menushopping|02_order_list" target="workspace" class="message" >您有新订单</a><span class="tiptool">（<em id="new_orders">99+</em>）</span></p>')
				}
				//新订单 end
				
				//待发货订单 start
				if(await_ship>=0 && await_ship<100){
					$("*[ectype='orderMsg']").append('<p><a href="javascript:void(0);" data-url="order.php?act=list&serch_type=8&source=start" data-param="menushopping|02_order_list" target="workspace" class="message" >待发货订单</a> <span class="tiptool">（<em id="no_paid">'+await_ship+'</em>）</span></p>')
				}else if(await_ship>99){
					$("*[ectype='orderMsg']").append('<p><a href="javascript:void(0);" data-url="order.php?act=list&serch_type=8&source=start" data-param="menushopping|02_order_list" target="workspace" class="message" >待发货订单</a><span class="tiptool">（<em id="no_paid">99+</em>）</span></p>')
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
					$("*[ectype='goodMsg']").append('<p><a href="javascript:void(0);" data-url="goods_report.php?act=list&handle_type=6" data-param="menushopping|goods_report" target="workspace" class="message">商品举报</a> <span class="tiptool">（<em id="goods_report">'+goods_report+'</em>）</span></p>')
				}else if(goods_report>99){
					$("*[ectype='goodMsg']").append('<p><a href="javascript:void(0);" data-url="goods_report.php?act=list&handle_type=6" data-param="menushopping|goods_report" target="workspace" class="message">商品举报</a><span class="tiptool">（<em id="goods_report">99+</em>）</span></p>')
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
					$("*[ectype='sellerMsg']").append('<p><a href="javascript:void(0);" data-url="merchants_users_list.php?act=list&check=1" data-param="menushopping|02_merchants_users_list" target="workspace" class="message">未审核商家</a> <span class="tiptool">（<em id="shop_account">'+shop_account+'</em>）</span></p>')
				}else if(shop_account > 99){
					$("*[ectype='sellerMsg']").append('<p><a href="javascript:void(0);" data-url="merchants_users_list.php?act=list&check=1" data-param="menushopping|02_merchants_users_list" class="message">未审核商家</a><span class="tiptool">（<em id="shop_account">99+</em>）</span></p>')
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
                $("*[ectype='advMsg']").append('<p><a href="javascript:void(0);" data-url="ads.php?act=list&advance_date=1" data-param="menuplatform|ad_list" target="workspace" class="message">广告位即将到期</a> <span class="tiptool">（<em id="advance_date">'+advance_date+'</em>）</span></p>')
            }else if(advance_date > 99){
                $("*[ectype='advMsg']").append('<p><a href="javascript:void(0);" data-url="ads.php?act=list&advance_date=1" data-param="menushopping|ad_list" target="workspace" class="message">广告即将位到期</a><span class="tiptool">（<em id="advance_date">99+</em>）</span></p>')
            }
			//广告位提醒 end
			
			//会员提醒 start
				//未处理会员实名认证 start
				if(user_account >= 0  && user_account<100){
					$("*[ectype='userMsg']").append('<p><a href="javascript:void(0);" data-url="user_real.php?act=list&review_status=0&user_type=0" data-param="menuplatform|03_users_list" target="workspace" class="message">会员实名认证</a> <span class="tiptool">（<em id="user_account">'+user_account+'</em>）</span></p>')
				}else if(user_account > 99){
					$("*[ectype='userMsg']").append('<p><a href="javascript:void(0);" data-url="user_real.php?act=list&review_status=0&user_type=0" data-param="menushopping|03_users_list" target="workspace" class="message">会员实名认证</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
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
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="snatch.php?act=list&seller_list=1&review_status=1" data-param="menushopping|02_snatch_list" target="workspace" class="message">夺宝奇兵</a> <span class="tiptool">（<em id="user_account">'+snatch+'</em>）</span></p>')
				}else if(snatch > 99){
					$("*[ectype='promotionMsg']").append('<p><a href="javascript:void(0);" data-url="snatch.php?act=list&seller_list=1&review_status=1" data-param="menushopping|02_snatch_list" target="workspace" class="message">夺宝奇兵</a><span class="tiptool">（<em id="user_account">99+</em>）</span></p>')
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
    }, 'GET', 'JSON');
	
	/* 后台消息提示 展开伸缩*/
	$("[ectype='msg_tit']").on("click",function(){
		var t = $(this),
			con = t.siblings(".msg_content"),
			Item = t.parents(".item");
			
		if(con.is(":hidden")){
			con.slideDown();
			Item.siblings().find(".msg_content").slideUp();
			t.find(".iconfont").addClass("icon-up").removeClass("icon-down");
			Item.siblings().find(".iconfont").removeClass("icon-up").addClass("icon-down");
		}else{
			con.slideUp();
			t.find(".iconfont").removeClass("icon-up").addClass("icon-down");
		}
	});
	
	/* 判断浏览器是ie6 - ie8 后台不可以进入*/
	if(!$.support.leadingWhitespace){
		notIe();
	}
});

//iframe内页 a标签链接跳转方法
function intheHref(obj){
	var url = obj.data("url"),
		param = obj.data("param");
		
	openItem(param);
	loadUrl(url);
}

function openItem(param,home){
	//若cookie值不存在，则跳出iframe框架
	if(!$.cookie('dscActionParam')){
		top.location.href = location.href;
		//top.location = self.location
		//window.location.reload();
	}
	
	var $this = $('div[id^="adminNavTabs_"]').find('a[data-param="' + param + '"]');
	var url = $this.data('url');

	data_str = param.split('|');
	
	if(home == 0){
		//$this.parents('.admin-main').removeClass('start_home');
	}
	
	if($this.parents(".admin-main").hasClass("fold")){
		$this.parents('.sub-menu').hide();
	}else{
		$this.parents('.sub-menu').show();
	}

	$this.parents('.item').addClass('current').siblings().removeClass('current');
	$this.parents('.item').siblings().find(".sub-menu").hide();
	$this.parents('li').addClass('curr').siblings().removeClass('curr');
	$this.parents('div[id^="adminNavTabs_"]').show().siblings().hide();
	
	$('li[data-param="' + data_str[0] + '"]').addClass('active').siblings().removeClass("active");
	
	$.cookie('dscActionParam', data_str[0] + '|' + data_str[1] , { expires: 1 ,path:'/'});
	
	if(param == 'home')
	{
		$('#adminNavTabs_home').show().siblings().hide();
		$('#adminNavTabs_home').find(".sub-menu").show();
		$('#adminNavTabs_home .sub-menu').find("li a:first").click();
		url = 'index.php?act=main';
		loadUrl(url);
	}
	
	/*if(param == "index|main"){
		$(".admin-main").addClass("start_home");
	}else{
		$(".admin-main").removeClass("start_home");
	}*/
}

function loadUrl(url){
	$.cookie('dscUrl', url , { expires: 1 ,path:'/'});

	$('.admin-main-right iframe[name="workspace"]').attr('src','dialog.php?act=getload_url');
	setTimeout(function(){
		$('.admin-main-right iframe[name="workspace"]').attr('src', url);
		/* 检查订单 */
  		startCheckOrder();
		
		/* 检查账单 */
  		startCheckBill();
	},300);
}

