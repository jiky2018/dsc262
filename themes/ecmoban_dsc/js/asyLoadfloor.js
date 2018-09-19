//顶级分类页楼层异步加载信息start
jQuery.catTopLoad = function(tpl){
	var load_num=0;
	var execute=true;
	var $minUl = $(".lazy-ecsc-warp");
	var wrapHeight = $minUl.height();
	var wrapTop = $minUl.offset().top;
	var items = $("#parent-cata-nav").find(".item");
	function load_cat_top(key)
	{
		var region_id = $("input[name='region_id']").val();
		var area_id = $("input[name='area_id']").val();
		var cat_id = items.eq(key).data('catid');
		var prent_id = $("input[name='cat_id']").val();
		if(items.length>key){
			execute = true;
		}else{
			execute = false;
		}
			
		if(execute&&key<items.length)
		{
			$.ajax({
			   type: "POST",
			   url: "get_ajax_content.php",
			   data: "act=get_cat_top_list&tpl="+ tpl +"&cat_id=" + cat_id + "&rome_key=" + load_num + "&prent_id=" + prent_id + "&region_id=" + region_id + "&area_id=" + area_id,
			   dataType:'json',
			   success: function(data){
				   $("#floor_loading").hide();
				   load_brand_response(data);
			   },
			   beforeSend : function(){
				   $("#floor_loading").show();
			   }
			});
			execute=false;
		}
	}
	
	load_cat_top(load_num); //默认显示品牌

	$(window).on("scroll",function(){
		var scrollTop = $(window).scrollTop();			
		if(scrollTop > wrapHeight-200){
			if(execute)
			{
				load_cat_top(load_num);
				execute=false;
			}
		}
	});
	
	function load_brand_response(result)
	{
		if(!result.error)
		{
			$("#cat_top_lit").append(result.content);
			
			load_num+=1;
			loadCategoryTop(load_num);
			$.adpos(); //异步加载楼层出现广告位提示调用
			$.tabs(); //异步加载每个楼层的分类切换
			execute=true;
		}
	}
}
//顶级分类页楼层异步加载信息start

//首页楼层异步加载信息start
jQuery.homefloorLoad =function(){
	var load_num=0;
	var execute=true;
	var $minUl = $(".lazy-ecsc-warp");
	var wrapHeight = $minUl.height();
	var wrapTop = $minUl.offset().top;
	function load_goods_cat(key)
	{				
		if(execute)
		{
			$.ajax({
			   type: "POST",
			   url: "get_ajax_content.php",
			   data: "act=get_index_goods_cat&rome_key=" + load_num,
			   dataType:'json',
			   success: function(data){
				   $("#floor_loading").hide();
				   load_goods_cat_response(data);
			   },
			   beforeSend : function(){
				   $("#floor_loading").show();
			   }
			});
			execute=false;
		}
	}		
	
	load_goods_cat(load_num);
	
	$(window).on("scroll",function(){
		var scrollTop = $(window).scrollTop();	
		if(scrollTop > wrapHeight-400){
			if(execute)
			{	
				load_goods_cat(load_num);
				execute=false;
			}
		}
		
	});
	
	function load_goods_cat_response(result)
	{
		if(!result.error)
		{
			$("#goods_cat_level").append(result.content);
			
			load_num+=1;
			load_js_content(load_num);
			if(result.maxindex)
			{
				indexfloor();
				execute=false;
			}
			else
			{
				execute=true;	
			}
		}
		else
		{
			load_js_content();
			execute=false;	
		}
	}
}
//首页楼层异步加载信息end

//商品列表异步加载瀑布流
jQuery.goodsLoad =function(obj,it,best,query_string,model){
	var execute=true,obj = $(obj),goods_num = 0,best_num = 0,wrapHeight,scrollTop,loading = $(".floor_loading");
	
	//判断it值是否存在
	if(!it)it = "li";
	
	$(window).on("scroll",function(){
		//判断best是否存在
		if(best){
			best_num  = $(best).find("ul li").length; 
		}else{
			best_num = 0;
		}
		goods_num = obj.find(it).length;
		wrapHeight = obj.height();
		scrollTop = $(window).scrollTop();
		
		if(scrollTop > wrapHeight-1000){
			if(execute)
			{
				load_more_goods(goods_num, best_num);
				execute=false;
			}
		}
	});
	
	function load_more_goods(goods_num, best_num)
	{
		$.ajax({
			type:'get',
			url:window.location.href.replace(/\?.+/g,''),
			data:query_string+'&act=load_more_goods&goods_num='+goods_num+'&best_num='+best_num +'&model=' + model,
			dataType:'json',
			success:function(data)			
			{
				loading.hide();
				if(data.cat_goods)
				{	
					obj.find("[ectype='items']").append(data.cat_goods);
					if(data.best_goods)
					{
						$(best).find("ul").append(data.best_goods);
					}
					sildeImg(goods_num);
					execute=true;
				}
			},
			beforeSend : function(){
			   if(goods_num>20){	
			   		loading.show();
			   }
		   }
		});
	}
}
//商品列表异步加载瀑布流end

