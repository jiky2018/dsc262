//购物车修改产品属性
$(".p-attr").hover(function(){
	$(this).addClass("item-props-can-hover");
},function(){
	$(this).removeClass("item-props-can-hover");
});
//修改
$(".btn-edit-sku").click(function(){
	$(this).parent().addClass("item-props-can-click");
	$(this).parents(".item-item").find(".sku-edit-popup").show();
});
//取消
$(".operate .cancel").click(function(){
	$(this).parents(".sku-edit-popup").hide();
	$(this).parents(".sku-edit-popup").parent().find(".item-props-can-click").removeClass("item-props-can-click");
});

//购物车单选全选
$(function(){
	function cartCheckbox(){
		var all_cart_value = ''; 	//商品购物ID字符串
		var cart_check_num = 0; 	//商品勾选数量
		var cartValue = $("input[name='cart_value']"); 
		var checkboxshop = $(".checkboxshopAll");
		var checkbox = $(".cart-checkbox input[type='checkbox']");
		var orderCheckbox = $("#cart-list .item-item input[type='checkbox']");
		var sellerCheck =$(".CheckBoxShop");
		var storeCheckbox = $(".stores-checkboxs-all");
		var stageCheckbox = $(".stages-checkboxs-all");
		var favourable_id = 0;		//活动ID
		var select_flag = "";		//活动商品选中id拼接字符串
		
		//初始化全选状态
		checkboxshop.prop("checked",true);
		checkbox.prop("checked",true);
		checkbox.parent().addClass("cart-checkbox-checked");
		$(".cart-tbody").addClass("all-select");
		$(".item-body").addClass("item-selected");
		
		//换购默认不选
		$(".item-gift").find("input[type='checkbox']").prop("checked",false);
		
		all_cart_value = get_cart_value();
		cartValue.val(all_cart_value);	
		
		//获取选择的购物车ID的商品信息
		change_cart_goods_number(all_cart_value);
		
		//默认勾选数量
		// var cart_check_num = $("input[name='checkItem']").size();
		var cart_check_num = get_checkItem_num();
		$('.cart_check_num').html(cart_check_num);
		
		//点击全选
		checkboxshop.click(function(){
			if($(this).prop("checked")== true){
				checkbox.prop("checked",true);
				storeCheckbox.prop("checked",false);
				stageCheckbox.prop("checked",false);
				checkbox.parent().addClass("cart-checkbox-checked");
				$("#cart-list").find(".cart-tbody").addClass("all-select");
				$("#cart-list").find(".item-body").addClass("item-selected");
			}else{
				checkbox.prop("checked",false);
				checkbox.parent().removeClass("cart-checkbox-checked");
				$("#cart-list").find(".cart-tbody").removeClass("all-select");
				$("#cart-list").find(".item-body").removeClass("item-selected");
			}
			
			//默认商品勾选数量
			cart_check_num = get_checkItem_num();
			$('.cart_check_num').html(cart_check_num);
			
			all_cart_value = get_cart_value();
			cartValue.val(all_cart_value);	

			//获取选择的购物车ID的商品信息
			change_cart_goods_number(all_cart_value);
		});
		
		//选择每个店铺全选
		sellerCheck.click(function(){
			var $this = $(this);
			var order_body = $this.parents(".cart-tbody");
			if($this.prop("checked") == true){
				order_body.find(".cart-checkbox").addClass("cart-checkbox-checked");
				order_body.find(".cart-checkbox input[type='checkbox']").prop("checked",true);
				order_body.find(".item-body").addClass("item-selected");
				order_body.addClass("all-select");				
			}else{
				order_body.find(".cart-checkbox").removeClass("cart-checkbox-checked");
				order_body.find(".cart-checkbox input[type='checkbox']").prop("checked",false);
				order_body.find(".item-body").removeClass("item-selected");
				order_body.removeClass("all-select");
			}
			
			cart_check_num = get_checkItem_num();
			all_cart_value = get_cart_value();
			
			sfAll(all_cart_value, cart_check_num);
			replace_cart_goods(all_cart_value,favourable_id,$this);
		});
		
		//店铺商品勾选
		$(document).on("click",".item-list .ui-checkbox",function(){
			var $this = $(this);
			var rec_id = $this.val();
			var order_body = $this.parents(".cart-tbody");
			var item_list = $this.parents(".item-list");
			var item_body = item_list.find(".item-body");
			var itemfull = item_body.find(".item-full");
			var itemfullLength = itemfull.find("input[name='checkItem']:checked").length;
			var itemItem = itemfull.find(".item-item").length;
			
			var goodsid = $this.parents(".item-body").data("goodsid");
			
			if(itemfullLength == itemItem){
				$(".item-full").addClass("minus-item");
				$this.parents(".item-body").addClass("item-selected");
			}else{
				$(".item-full").removeClass("minus-item");
				$this.parents(".item-body").removeClass("item-selected");
			}
			
			if($this.prop("checked") == true){
				$this.parent().addClass("cart-checkbox-checked");
				
				//组合购买配件商品
				$(".m_goods_1_"+goodsid).addClass("item-selected");
				$(".m_goods_1_"+goodsid).find(".cart-checkbox").addClass("cart-checkbox-checked");
				$(".m_goods_1_"+goodsid).find(".ui-checkbox").prop("checked",true);
			}else{
				$this.parent().removeClass("cart-checkbox-checked");
				
				//组合购买配件商品
				$(".m_goods_1_"+goodsid).removeClass("item-selected");
				$(".m_goods_1_"+goodsid).find(".cart-checkbox").removeClass("cart-checkbox-checked");
				$(".m_goods_1_"+goodsid).find(".ui-checkbox").prop("checked",false);
			}
			
			var minusItem = item_list.find(".minus-item").length;
			
			//单个店铺商品是否全选
			if(item_body.length == minusItem){
				order_body.find(".CheckBoxShop").prop("checked",true);
				order_body.find(".CheckBoxShop").parent().addClass("cart-checkbox-checked");
				order_body.addClass("all-select");
			}else{
				favourable_id = $this.parents(".item-body").data("actid");
				order_body.find(".CheckBoxShop").prop("checked",false);
				order_body.find(".CheckBoxShop").parent().removeClass("cart-checkbox-checked");
				order_body.removeClass("all-select");
			}
			
			cart_check_num = get_checkItem_num();
			all_cart_value = get_cart_value();

			sfAll(all_cart_value, cart_check_num);
			
			replace_cart_goods(all_cart_value,favourable_id,$this);
		});	
		
		//门店商品选择
		/*storeCheckbox.click(function(){
			var item_item = checkbox.parents(".item-item");
			var item_item_length = item_item.length;
			
			if($(this).prop("checked") == true){
				orderCheckbox.prop("checked",false);
				stageCheckbox.prop("checked",false);
				checkbox.parent().removeClass("cart-checkbox-checked");
				checkbox.parents(".item-body").removeClass("item-selected");
				checkbox.parents(".cart-tbody").removeClass("all-select");
				item_item.each(function(){
					var cCheckbox = $(this).find(".cart-checkbox");
					if(cCheckbox.hasClass("store_type")){
						cCheckbox.find(".ui-checkbox").prop("checked",true);
						cCheckbox.addClass("cart-checkbox-checked");
						cCheckbox.parents(".item-body").addClass("item-selected");
						
						if(item_item_length == $(".store_type").length){
							checkbox.parents(".item-body").addClass("item-selected");
							checkbox.parents(".cart-tbody").addClass("all-select");
						}
					}
				});
				$("input[name='store_seller']").val("store_seller");
			}else{
				orderCheckbox.prop("checked",true);
				checkbox.parent().addClass("cart-checkbox-checked");
				
				$("input[name='store_seller']").val("");
				checkbox.parents(".item-body").addClass("item-selected");
				checkbox.parents(".cart-tbody").addClass("all-select");
			}
			
			cart_check_num = get_checkItem_num();
			all_cart_value = get_cart_value();
			
			sfAll(all_cart_value, cart_check_num);
			dpAll();
		});*/
		
		//分期商品选择
		/*stageCheckbox.click(function(){
			var item_item = checkbox.parents(".item-item");
			var item_item_length = item_item.length;
			
			if($(this).prop("checked") == true){
				orderCheckbox.prop("checked",false);
				storeCheckbox.prop("checked",false);
				checkbox.parent().removeClass("cart-checkbox-checked");
				checkbox.parents(".item-body").removeClass("item-selected");
				checkbox.parents(".cart-tbody").removeClass("all-select");
				item_item.each(function(){
					var cCheckbox = $(this).find(".cart-checkbox");
					if(cCheckbox.hasClass("stages_type")){
						cCheckbox.find(".ui-checkbox").prop("checked",true);
						cCheckbox.addClass("cart-checkbox-checked");
						cCheckbox.parents(".item-body").addClass("item-selected");
						
						if(item_item_length == $(".stages_type").length){
							checkbox.parents(".item-body").addClass("item-selected");
							checkbox.parents(".cart-tbody").addClass("all-select");
						}
					}
				});
				$("input[name='store_seller']").val("stages_goods");
			}else{
				orderCheckbox.prop("checked",true);
				checkbox.parent().addClass("cart-checkbox-checked");
				
				$("input[name='store_seller']").val("");
				checkbox.parents(".item-body").addClass("item-selected");
				checkbox.parents(".cart-tbody").addClass("all-select");
			}
			
			cart_check_num = get_checkItem_num();
			all_cart_value = get_cart_value();
			
			sfAll(all_cart_value, cart_check_num);
			dpAll();
		});*/
		
		//判断是否全选了
		function sfAll(all_cart_value, cart_check_num){
			
			var orderLength = $("#cart-list .item-item input[type='checkbox']:checked").length;
			if( orderLength == orderCheckbox.length){
				checkboxshop.prop("checked",true);
				checkboxshop.parent().addClass("cart-checkbox-checked");
			}else{
				checkboxshop.prop("checked",false);
				checkboxshop.parent().removeClass("cart-checkbox-checked");
			}
			
			$('.cart_check_num').html(cart_check_num);
			cartValue.val(all_cart_value);	
			
			change_cart_goods_number(all_cart_value); //计算勾选的商品总价
			
			//获取选择的购物车ID的商品信息
			//change_cart_goods_number2(all_cart_value, favourable_id, rec_id);
		}
		
		//判断每个店铺是否全选了
		function dpAll(){
			var cart_item_list = $(".cart-item-list");
			cart_item_list.each(function(){
				var item_body = $(this).find(".item-body");
				var length = item_body.find(".cart-checkbox input[type='checkbox']:checked").length;
				if(item_body.length == length){
					$(this).find(".shop .cart-checkbox input[type='checkbox']").prop("checked",true);
				}else{
					$(this).find(".shop .cart-checkbox input[type='checkbox']").prop("checked",false);
				}
			});
		}
		
		//换购商品
		$(document).on('click',".item-gift input[type='checkbox']",function(){
			var length = $(this).parents(".item-gift").find("input[type='checkbox']:checked").length;
			var value = $(this).val();
			var ru_id = $(this).data("ruid");
			var act_id = $(this).data("actid");
			var num = $(this).parents(".gift-goods").data("num");
			
			if(length > num){
				$(this).prop("checked",false);
				$(this).parent().removeClass("cart-checkbox-checked");
				var msg = json_languages.buy_more + num + json_languages.a_goods;
				
				pbDialog(msg,"",0);
			}
			else
			{
				$(".gift-mt #giftNumber_" + act_id + "_" + ru_id).html(length);
			}
		});
		
		
		//领取赠品确定
		$(document).on('click','.select-gift',function(){
			var act_id = $(this).data('actid');
			var ru_id = $(this).data('ruid');
			add_gift_cart(act_id, ru_id);
		})
	}
	
	
	/**
	*	优惠活动——满赠活动start
	*/
	
	//添加赠品到购物车
	function add_gift_cart(act_id, ru_id)
	{
		var arr ="";
		var gift = $("#product_promo_" + ru_id + "_" + act_id);
		gift.find(".cart-gift-checkbox input").each(function(){
			if($(this).prop("checked")==true){
				var val = $(this).val();
				arr += val+',';
			}
		});
	
		select_gift = arr.substring(0,arr.length-1);
		
		//替换此店铺购物车里的商品
		var gift_input = gift.find(".cart-checkbox input[type='checkbox']");
		var str='';
		gift_input.each(function(){
			if($(this).prop('checked')== true){
				var val = $(this).val();
				str += val + ',';
			}
		});
		
		str = str.substring(str.length-1,0);
		
		if (str != '') {
			select_flag = '&sel_id=' + str + '&sel_flag=' + 'cart_sel_flag';
		}
		
		Ajax.call('flow.php?step=add_favourable', 'act_id=' + act_id + '&ru_id=' + ru_id + select_flag + '&select_gift=' + select_gift, add_gift_cart_response, 'POST', 'JSON');
	}
	
	//添加赠品到购物车回调函数
	function add_gift_cart_response(result)
	{
		if (result.error){
			pbDialog(result.message,"",0);
			return false;
		}else{
			var cart_favourable_box = document.getElementById('product_promo_' + result.ru_id + "_" + result.act_id);
			if(cart_favourable_box){
				cart_favourable_box.innerHTML = result.content;
			}
		}
		//赋值产品数量
		$('.cart_check_num').html(get_checkItem_num());
		$("input[name='cart_value']").val(get_cart_value());
		$('#cart_goods_amount').html(result.goods_amount); //商品总金额
	}
	
	//获取购物车已选商品数量
	function get_checkItem_num(){
		var num = 0;
		$("input[name='checkItem']").each(function(index, element) {
			if($(element).is(":checked")){
				var selectNum = Number($(this).parents('.item-form').find(".itxt").val());
				num += selectNum;
			}
		});
		
		return num;
	}
	//获取购物车已选ID
	function get_cart_value(){
		var cart_value = '';
		$("input[name='checkItem']").each(function(index, element) {
			if($(element).is(':checked')){
				cart_value += $(element).val() + ",";
			}
        });
		cart_value = cart_value.substring(0,cart_value.length-1)
		
		return cart_value;
	}
	
	//参加同一个优惠活动切换勾选时判断
	function replace_cart_goods(rec_id, favourable_id, $this)
	{
		var ajax_where = '';
		var select_flag = '';
		var str ='';
		var items = $this.parents('.cart-tbody').find('.item-item');
		var input = items.find("input[type='checkbox']");

		if(typeof(favourable_id) != 'undefined'){
			ajax_where = '&favourable_id=' + favourable_id;
		}
		
		input.each(function(){
			if($(this).prop('checked')== true){
				var val = $(this).val();
				str += val + ',';
			}
		});
		
		str = str.substring(str.length-1,0);
		console.log(str);
		if (str != '') {
			select_flag = '&sel_id=' + str + '&sel_flag=' + 'cart_sel_flag';
		}

		Ajax.call('flow.php?step=ajax_cart_goods_amount', 'rec_id=' + rec_id + select_flag + ajax_where, replace_cart_goods_response, 'POST','JSON');                
	}
	function replace_cart_goods_response(result)
	{
		$('#cart_goods_amount').html(result.goods_amount); //商品总金额
		$('#save_total_amount').html(result.save_total_amount); //优惠节省总金额
		$('.cart_check_num').html(result.subtotal_number); //商品总数
		
		if(result.act_id > 0){
			$("#product_promo_" + result.ru_id + "_" + result.act_id).html(result.favourable_box_content);
			$(".item-full").removeClass("minus-item");
		}
	}

	//购物车换购效果
    $(document).on('click','.trade-btn',function(){
        var itemFull = $(this).parents(".item-full");
		var left = $(this).position().left+10;
        var top = $(this).parents(".item-header").outerHeight();
        var favourable_id = $(this).data("actid");
		var ru_id = $(this).data("ruid");
        var gift = $("#product_promo_" + ru_id + "_" + favourable_id);

        itemFull.find(".gift-box").show().css({"left":left,"top":top,"z-index":"100"});
		
        //替换此店铺购物车里的商品
		var gift_input = gift.find(".cart-checkbox input[type='checkbox']");
		
		var str='';
		gift_input.each(function(){
			if($(this).prop('checked')== true){
				var val = $(this).val();
				str += val + ',';
			}
		});

		str = str.substring(str.length-1,0);
		
		if (str != '') {
			select_flag = '&sel_id=' + str + '&sel_flag=' + 'cart_sel_flag';
		}


        Ajax.call('flow.php?step=show_gift_div', 'favourable_id=' + favourable_id + '&ru_id=' + ru_id + select_flag, show_gift_div_response, 'POST', 'JSON');
    });

    $(document).on("click",".gift-box .close",function(){
		var itemFull = $(this).parents(".item-full");
        $(this).parents(".gift-box").hide();
		itemFull.css("z-index","initial");
    });

    function show_gift_div_response(result)
    {
        var giftInfo = document.getElementById('gift_box_list_' + result.act_id + "_" + result.ru_id);
        if (giftInfo)
        {
          giftInfo.innerHTML = result.content;
        }
    }

	/* 满赠活动end */
	
	cartCheckbox();
});

//订单中心全选反选
$(function(){
	function orderCheckbox(){
		var orderCheckAll = $(".orderCheckAll");
		var orderCheck = $(".checkbox input[type='checkbox']");
		var checkAll = $(".item_checkbox input[type='checkbox']");
		//全选
		$(document).on('click',".orderCheckAll",function(){
			if($(this).prop("checked")== true){
				orderCheck.prop("checked",true);
				orderCheck.parent().addClass("order-checkbox-checked");
				$(this).parent().addClass("order-checkbox-checked");
			}else{
				orderCheck.prop("checked",false);
				orderCheck.parent().removeClass("order-checkbox-checked");
				$(this).parent().removeClass("order-checkbox-checked")
			}
		});
		//单选
		$(document).on('click',".checkbox input[type='checkbox']",function(){
			if($(this).prop("checked")==true){
				$(this).parent().addClass("order-checkbox-checked");
			}else{
				$(this).parent().removeClass("order-checkbox-checked");
			}
			sfAll();
		});
		//判断是否全选了
		function sfAll(){
			var length = $(".item_checkbox input[type='checkbox']:checked").length;
			if(length == checkAll.length){
				orderCheckAll.prop("checked",true);
				orderCheckAll.parent().addClass("order-checkbox-checked")
			}else{
				orderCheckAll.prop("checked",false);
				orderCheckAll.parent().removeClass("order-checkbox-checked")
			}
		}
	}
	orderCheckbox();
});
function change_cart_goods_number(rec_id)
{   
	Ajax.call('flow.php?step=ajax_cart_goods_amount', 'rec_id=' + rec_id, change_cart_goods_response, 'POST','JSON');                
}
function change_cart_goods_response(result)
{                
	$('#cart_goods_amount').html(result.goods_amount); //商品总金额
	$('#save_total_amount').html(result.save_total_amount); //优惠节省总金额
    $('.cart_check_num').html(result.subtotal_number); //商品总数
}