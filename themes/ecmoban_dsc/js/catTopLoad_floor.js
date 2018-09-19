jQuery.catTopLoad = function(tpl){
	//by wang start异步加载信息
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
			adpos();
			tabs();
			execute=true;
		}
	}
	
	//异步加载每个楼层的分类切换
	function tabs(){
		var li = $(".tab").find("li");
		var index = 0;
		var floors ='';
		li.hover(function(){
			$(this).addClass("on");
			$(this).siblings().removeClass("on");
			index = $(this).index();
			floors = $(this).parents(".floor-container");
			
			floors.find(".ecsc-cp-tabs").hide();
			floors.find(".ecsc-cp-tabs").eq(index).show();
		});
	}
	
	//异步加载楼层出现广告位提示
	function adpos(){
		$("*[ecdscType='adPos']").each(function(i,e){			
			var _this = $(this);
			var div = _this.find('img');
			var text = _this.data("adposname");

			if(!div.length>0){
				_this.addClass('adPos_hint');
				_this.html('<section>请去后台广告位置" '+text+'" 里面设置广告！</section>');
			}
		});
	}
}