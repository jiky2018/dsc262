/**
 * 大商创 平台后台、商家后台可视化js
 * ============================================================================
 * 版权所有 2005-2016 上海商创网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecmoban.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: sunle and kong $
 * $Id: activity.php 17217 2017-07-26 06:29:08Z sunle and kong $
 */

$(function(){
	var suffix_obj = $("input[name='suffix']"),
		suffix = suffix_obj.val(),
		section = suffix_obj.data("section"),
		//luo1
		templates = suffix_obj.data("templates"),
		topic_type = "",
        adminpath = suffix_obj.data("adminpath"),
		ru_id = 0;
	
	var doc = $(document),
		visualShell = $("*[ectype='visualShell']");
		
	if(section == "vis_seller_topic" || section == "vis_topic" ){
		topic_type = "topic_type";
	}	
        
	$(document).ready(function(e){
		var arrLunbotu = [];
		
		$(".demo, .demo .column").sortable({
			connectWith: ".column",
			opacity: .35,
			handle: ".drag"
		});

		//可视化页面默认加载
		function visual_load(){
			var height = $(window).height(),
				width = $(window).width();

			$(".page-head-bg-content-wrap").css({"height": height-30});
			$(".pc-page").css({"height": height-61});
			$(".pc-page .demo").css({"min-height":height});
			
			$(".main-wrapper").addClass("wpst-toolbar-show");
			$(".tab-bar").find("li").eq(0).addClass("current");
			$(".toolbar").find("li").eq(0).addClass("current");
			
			if($(".main-wrapper").hasClass("wpst-toolbar-show")){
				$(".pc-page").css({"width":width-310});
			}else{
				$(".pc-page").css({"width":width-80});
			}
			
			//浏览器大小改变
			$(window).resize(function(e){
				height = $(window).height();
				width = $(window).width();
				
				$(".page-head-bg-content-wrap").css({"height": height - 30});
				$(".demo").css("min-height", height);
				$(".pc-page").css("height", height - 61);
				
				if($(".main-wrapper").hasClass("wpst-toolbar-show")){
					$(".pc-page").css({"width":width-310});
				}else{
					$(".pc-page").css({"width":width-80});
				}
			});
		}
		visual_load();
		
		//可视化左侧侧边栏区域js
		function db_column(){
			var height = $(window).height(),
				width = $(window).width(),
				url = '';
				
			//左侧侧边栏 展开
			var li = $(".tab-bar").find("li"),
				ul = li.parent(".tab-bar"),
				wrapper = li.parents(".main-wrapper"),
				toolbar = ul.siblings(".toolbar");
	
			li.on("click",function(){
				var index = $(this).index();
				
				if($(this).hasClass("current")){
					$(this).removeClass("current");
					wrapper.removeClass("wpst-toolbar-show");
					$(".pc-page").css({"width":width-80});
				}else{
					$(this).addClass("current").siblings().removeClass("current");
					toolbar.find("*[ectype='toolbar_li']").eq(index).addClass("current").siblings().removeClass("current");
					wrapper.addClass("wpst-toolbar-show");
					$(".pc-page").css({"width":width-310});
				}
			});
			
			//左侧侧边栏 关闭
			$(document).on("click","*[ectype='close']",function(){
				$(this).parents(".li").removeClass("current");
				$(this).parents(".main-wrapper").removeClass("wpst-toolbar-show");
				$(this).parents(".main-wrapper").find(".tab-bar li").removeClass("current");
				$(".pc-page").css({"width":width-80});
			});
			
			//左侧侧边栏 头部添加背景颜色
			$("input[name='header_dis']").on("click",function(){
				var style = $(this).parents(".li").data("style"),
					bgDiv = $(this).parents(".page-head-bg"),
					bgColor = bgDiv.find(".tm-picker-trigger").val();
			
				if($(this).prop("checked") == true){
					$(".pc-page").find(".hd_bg").css({"background-color":bgColor});
				}else{
					$(".pc-page").find(".hd_bg").css({"background-color":"transparent"});
				}
				
				generate(style);
			});
			
			var hdfile_url = "visual_editing.php?act=header_bg&type=headerbg&name=hdfile&suffix="+suffix+"&topic_type="+topic_type;
			
			if(adminpath == 'admin'){
				hdfile_url = "topic.php?act=header_bg&type=headerbg&name=hdfile&suffix="+suffix+"&topic_type="+topic_type;
			}
			
			//左侧侧边栏 头部添加背景图
			$.upload_file("input[name='hdfile']",hdfile_url,"#showbgfile",function fn(obj,img){
				var parent = obj.parents(".page-head-bgimg");
				var repeat = parent.siblings(".bg-show").find(".current").data("bg-show");
				var position = parent.siblings(".bg-align").find(".current").data("bg-align");
			
				parent.siblings(".bg-show,.bg-align").show();
				$(".pc-page").find(".hd_bg").css({"background-image":"url("+img+")","background-repeat":repeat,"background-position":position});
				
				visual();
			});

			if(section == "vis_home"){
				url = "topic.php?act=header_bg&type=contentbg&name=confile&suffix="+suffix + "&hometype=1";
			}else if(section == "vis_topic" || adminpath == 'admin'){
				url = "topic.php?act=header_bg&type=contentbg&name=confile&suffix="+suffix+"&topic_type="+topic_type;
			}else if((section == "vis_seller_topic" || section == "vis_seller_store") && adminpath != 'admin'){
				url = "visual_editing.php?act=header_bg&type=contentbg&name=confile&suffix="+suffix+"&topic_type="+topic_type;
			}
			
			//左侧侧边栏 中间添加背景图
			$.upload_file("input[name='confile']",url,"#confilefile",function fn(obj,img){
				var parent = obj.parents(".page-head-bgimg");
				var repeat = parent.siblings(".bg-show").find(".current").data("bg-show");
				var position = parent.siblings(".bg-align").find(".current").data("bg-align");
				parent.siblings(".bg-show,.bg-align").show();
				
				$(".demo").css({"background-image":"url("+img+")","background-repeat":repeat,"background-position":position});
				visual();
			});
			
			//左侧侧边栏 中间选择背景颜色
			$("input[name='content_dis']").on("click",function(){
				var style = $(this).parents(".li").data("style");
				var bgDiv = $(this).parents(".page-head-bg");
				var bgColor = bgDiv.find(".tm-picker-trigger").val();
			
				if($(this).prop("checked") == true){
					$(".demo").css({"background-color":bgColor});
				}else{
					$(".demo").css({"background-color":"transparent"});
				}
				generate(style);
			});
			
			$(".sp-replacer").on("click",function(){
				$(this).parents("li.li").find("input[type='checkbox']").prop("checked",false);
				$(".demo").css({"background-color":"transparent"});
			});
			
			//左侧侧边栏 删除头部或者中间背景图
			$(document).on("click","*[ectype='delete_bg']",function(){
				var form =$(this).parents("form"),
					fileimg = form.find("input[name='fileimg']").val(),
					type = form.parents('li').data("style");

				form.find("input[name='fileimg']").val("");
				form.find(".bgimg img").attr("src","../data/gallery_album/visualDefault/bgimg.gif");
				form.parent(".page-head-bgimg").siblings(".bg-show,.bg-align").hide();
				
				$(".pc-page").find(".hd_bg").css({"background-image":"none"});
				$(".demo").css({"background-image":"none"});
				
				if(section == "vis_home"){
					Ajax.call('topic.php', "act=remove_img&fileimg=" + fileimg + "&suffix="+suffix + "&type=" + type + "&hometype=1", '', 'POST', 'JSON');
				}else if(section == "vis_topic"){
					Ajax.call('topic.php', "act=remove_img&fileimg=" + fileimg + "&suffix="+suffix + "&type=" + type, '', 'POST', 'JSON');
				}else{
					Ajax.call('visual_editing.php', "act=remove_img&fileimg=" + fileimg + "&suffix="+suffix + "&type=" + type, '', 'POST', 'JSON');
				}
				visual();
			});
		
			//左侧侧边栏 头部/中间 背景平铺
			$(document).on("click", ".bg-show-nr a",function(){
				var style = $(this).parents(".li").data("style"),
					repeat = $(this).data("bg-show");
				
				$(this).addClass("current").siblings().removeClass("current");
				
				if(style == "head"){
					$(".pc-page").find(".hd_bg").css({"background-repeat":repeat});
				}else{
					$(".demo").css({"background-repeat":repeat});
				}
				generate(style);
			});
			
			//左侧侧边栏 头部/中间 背景对齐
			$(document).on("click", ".bg-align-nr a",function(){
				var style = $(this).parents(".li").data("style"),
					position = $(this).data("bg-align");
				
				$(this).addClass("current").siblings().removeClass("current");
				
				if(style == "head"){
					$(".pc-page").find(".hd_bg").css({"background-position":position});
				}else{
					$(".demo").css({"background-position":position});
				}
				generate(style);
			});
			
			
			
			//左侧侧边栏 弹出广告保存
			$(document).on('click','*[ectype="adcSubmit"]',function(){
				submitForm(1);
			});
			
			//左侧侧边栏 弹出广告上传图片
			$("input[name='advfile']").change(function(){
				submitForm(2);
			});
			
			/* 左侧侧边栏 弹出广告方法 */
			function submitForm(i){
				$("#advfileForm").ajaxSubmit({
					type: "POST",
					dataType: "json",
					url: "visualcategory.php?act=bonusAdv&suffix="+suffix,
					data: { "action": "TemporaryImage" },
					success: function (data) {
						if (data.error == "1") {
						   alert(data.prompt);
						}else{
							if(data.file != ''){
								$("input[name='advfile']").parents(".page-head-bgimg").find('img').attr('src',data.file);
							}
							if(i == 1){
								layer.msg('保存成功',{time:800,offset:["50%","10%"]});
							}else{
								layer.msg('上传成功',{time:800,offset:["50%","10%"]});
							}
						}
					},
					async: true
				});
			}
			
			/* 删除弹出广告 */
			$(document).on('click','*[ectype="delete_adv"]',function(){
				var _this = $(this);
				
				Ajax.call('visualcategory.php', "act=delete_adv&suffix=" +  suffix, function(data){
					_this.parents('.page-head-bgimg').find('img').attr('src',"../data/gallery_album/visualDefault/bgimg.gif");
					$("input[name='adv_url']").val('');
				}, 'POST', 'JSON');
			});
			
			$(window).ready(function(e) {
            	if($(".demo").hasClass("store_default_temp1")){
					$("*[ectype='stores_modules']").find("*[data-mode='storeOneFloor1']").show().siblings().hide();
				}else if($(".demo").hasClass("store_default_temp2")){
					$("*[ectype='stores_modules']").find("*[data-mode='storeTwoFloor1']").show().siblings().hide();
				}else if($(".demo").hasClass("store_default_temp3")){
					$("*[ectype='stores_modules']").find("*[data-mode='storeThreeFloor1']").show().siblings().hide();
				}else if($(".demo").hasClass("store_default_temp4")){
					$("*[ectype='stores_modules']").find("*[data-mode='storeFourFloor1']").show().siblings().hide();
				}else if($(".demo").hasClass("store_default_temp5")){
					$("*[ectype='stores_modules']").find("*[data-mode='storeFiveFloor1']").show().siblings().hide();
				}else{
					$("*[ectype='stores_modules']").hide();
				}
            });
		}
		db_column();
		
		//左侧模块拖动到右侧
		$(".module-list .lyrow").draggable({ connectToSortable:".demo", helper:"clone", handle:".drag",
			drag:function(e,t){
				t.helper.width(63);
			},
			stop:function(e,t){
				var zhi = $(this).hasClass('lunbotu');
                var brandList = $(this).hasClass('brandList');
				var mode = $(this).data("mode");
				
				$(".demo *[data-mode=" + mode + "]").each(function(index, element) {
					$(this).attr("data-diff",index);
					if(!zhi){
						$(this).find("*[data-type='range']").attr("id",mode + "_" + index);
					}
				});
				if(zhi || brandList){
					if(zhi){
						var lunbotu = $(".demo").find('.lunbotu');
					}else if(brandList){
						var lunbotu = $(".demo").find('.brandList');
					}
					
					arrLunbotu.push(t.position.top);
					if(arrLunbotu.length == 1){
						if(arrLunbotu[0] > arrLunbotu[1]){
							lunbotu.eq(0).remove();
						}else{
							lunbotu.eq(1).remove();
						}
						arrLunbotu.pop();
					}
					if(lunbotu.length > 1){
						layer.msg('此模块只能添加一次',{time:500,offset:["50%","50%"]});
					}else{
						if(t.position.left>300){
							layer.msg('添加成功',{time:500,offset:["50%","50%"]});
							disabled();
						}
					}
				}else{
					if(t.position.left>300){
						layer.msg('添加成功',{time:500,offset:["50%","50%"]});
						disabled();
					}
				}
			}
		});
		
		//模块上移
		$(document).on("click",".move-up",function(){
			var _this = $(this);
			var _div = _this.parents(".visual-item");
			var prev_div = _div.prev();
			
			var clone = _div.clone();
			if(!_this.hasClass("disabled")){
				_div.remove();
				prev_div.before(clone);
				visual();
				disabled();
			}
		});
		
		//模块下移
		$(document).on("click",".move-down",function(){
			var _this = $(this);
			var _div = _this.parents(".visual-item");
			var next_div = _div.next();
			
			var clone = _div.clone();
			if(!_this.hasClass("disabled")){
				_div.remove();
				next_div.after(clone);
				visual();
				disabled();
			}
		});
		
		//判断模块是顶部模块或底部模块
		function disabled(){
			var demo = $(".demo");
			demo.find(".visual-item .move-up").removeClass("disabled");
			demo.find(".visual-item:first .move-up").addClass("disabled");
			
			demo.find(".visual-item .move-down").removeClass("disabled");
			demo.find(".visual-item:last .move-down").addClass("disabled");
		}
		disabled();
		//删除模块
		function removeElm() {
			$(".demo").delegate(".move-remove", "click", function(e) {
				that = $(this);
				layer.confirm('您确定要删除这个模块吗？', {
					btn: ['确定','取消'],
				}, function(){
					time: 500,
					layer.msg('删除成功',{time:500,offset:["50%","50%"]}),
					e.preventDefault();
					that.parents(".visual-item").remove();
					disabled();
					visual();
					if (!$(".demo .lyrow").length > 0) {
						clearDemo();
					}
				});
			})
		}
		removeElm();
		
		//头部广告删除
		$(document).on("click","*[ectype='model_delete']",function(){
			 var _this = $(this);
			 //var suffix = $("input[name='suffix']").val();
			 if(confirm("确定删除此广告么？删除后前台不显示只能后台编辑，且不可找回！")){
				 Ajax.call('visualcategory.php', "act=model_delete&suffix=" +  suffix, function(data){
					 if(data.error == 1){
						 alert(data.message);
					 }else{
						 var obj = _this.parents('*[data-mode="topBanner"]');
						 //初始化默认值
						 obj.find('[data-type="range"]').parent().css({"background":"#dbe0e4"});
						 obj.find('[data-type="range"] a').attr("href","#"); 
						 obj.find('[data-type="range"] img').attr("src","../data/gallery_album/visualDefault/homeIndex_011.jpg"); 
						 obj.find(".spec").remove();
						 visual();
					 }
				 }, 'POST', 'JSON');
			 }
		});
		
		//判断模块是否存在
		function clearDemo() {
			if( $(".demo").html() == "" ){
				layer.msg('当前没有任何模板哦',{time:500,offset:["50%","50%"]})
			}else{
				layer.confirm('您确定要清空所有模块吗？', {
					btn: ['确定','取消'],
				}, function(){
					time: 500,
					layer.msg('清空成功',{time:500,offset:["50%","50%"]}),
					$(".demo").empty();
				});
			}
		}
		
		//模块展开收起
		$("*[ectype='head']").on("click",function(){
			var modulesWrap = $(this).parent();
			
			if(modulesWrap.hasClass("modules-wrap-current")){
				modulesWrap.removeClass("modules-wrap-current");
			}else{
				modulesWrap.addClass("modules-wrap-current");
			}
		});
	});
	
	//可视化操作：确认发布、还原、预览、信息编辑、选择模板
	function visualOperation(){
		var btn = $(".design-nav-wrap .btns"),
			downloadModal = btn.find("*[ectype='downloadModal']"),
			back = btn.find("*[ectype='back']"),
			preview = btn.find("*[ectype='preview']"),
			information = btn.find("*[ectype='information']"),
			releaseTemp = btn.find("*[ectype='releaseTemp']"); //店铺设为默认
		
		/* 确认发布 */
		downloadModal.on("click",function(){
			var Release_url = '';
			var Release_where = '';
                        if (suffix) {
                            if (section == "vis_home") {
                                Release_url = "visualcategory.php";
                                Release_where = "act=downloadModal&suffix=" + suffix;
                            }
                        }
			if(confirm("确定发布？")){
				Ajax.call(Release_url, Release_where, function(data){
					alert("发布成功");
					$("[ectype='back']").hide();
				}, 'POST', 'JSON');
			}
		});
		
		/* 商家店铺可视化 设为默认 */
		releaseTemp.on("click",function(){
			Ajax.call('visual_editing.php', "act=release&suffix=" + suffix,function(data){
				if(data.error == 1){
					location.href = 'visual_editing.php?act=first';
				}else{
					alert(data.content);
				}
			} , 'POST', 'JSON');
		});
		
		/* 预览 */
		preview.on("click",function(){
			var code = suffix;
						if(code){
                            if(section == "vis_home"){
                                    //首页可视化
                                    window.open('../category.php?suffix=' + code  + '&templates='+templates);
                            }
                        }else{
                             window.open('../news.php?preview=1');
                        }
			
		});
		
		/* 还原编辑前的模板 */
		back.on("click",function(){
			var url = "",
				where = "";
                        
			if(section == "vis_home" || suffix == ''){
				url = 'visualcategory.php';
                                if(suffix == ''){
                                    where = "&new=1";
                                }
			}else{
				url = 'visual_editing.php';
				where = "&topic_type=" + topic_type;
			}
			
			if(confirm("还原只能还原到你最后一次确认发布后的版本，还原后当前未保存的数据将丢失，不可找回，确定还原吗？")){
				Ajax.call(url, "act=backmodal&suffix=" +  suffix + where , function(data){
					location=location 
				}, 'POST', 'JSON');
			}
		});
		
		/* 可视化模板信息编辑 */
		information.on("click",function(){
			var list_code = $(this).data("code");
			var code = suffix;
			code = (list_code)  ?  list_code : code;
			Ajax.call('dialog.php', 'act=template_information' + '&code=' + code, informationResponse, 'POST', 'JSON');
		});
		
		/* 可视化模板信息编辑 弹窗*/
		function informationResponse(result){
			var content = result.content;
			pb({
				id: "template_information",
				title: "模板信息",
				width: 945,
				content: content,
				ok_title: "确定",
				drag: true,
				foot: true,
				cl_cBtn: false,
				onOk: function(){
					var fald = true;
					var name = $("#information").find("input[name='name']");
					var ten_file = $("#information").find("input[name='ten_file_textfile']");
					var big_file = $("#information").find("input[name='big_file_textfile']");
					
					if(name.val() == ""){
						error_div("#information input[name='name']","模板名称不能为空");
						fald = false;
					}else if(ten_file.val() == ""){
						error_div("#information input[name='ten_file']","请上传模板封面");
						fald = false;
					}else if(big_file.val() == ""){
						error_div("#information input[name='big_file']","请上传模板大图");
						fald = false;
					}else{
						var actionUrl = "visualcategory.php?act=edit_information";  
						$("#information").ajaxSubmit({
							type: "POST",
							dataType: "JSON",
							url: actionUrl,
							data: { "action": "TemporaryImage" },
							success: function (data) {
								if(data.error == 1){
									alert(data.massege);
								}else{
									$("[ectype='templateList']").find("ul").html(data.content);
								}
								resetHref();
							},
							async: true  
						});
						
						fald = true;
					}
					return fald;
				}
			});
		}
		
		//默认模板颜色设置
		$("*[ectype='templateColorSet']").on("click",function(){
			var templateName = $(".demo").data("default");
			var typeColor = $(".demo").attr("data-color");
			if(typeof typeColor == 'undefined'){
				typeColor = '';
			}
			if(templateName){
				$.jqueryAjax('dialog.php', 'act=templateColorSet&temp=' + templateName + "&typeColor=" + typeColor, function(data){
					goods_visual_desc("模板色调选择",750,data.content,function(){
						var colorItem = $(".dtColorSet").find(".selected");
						var colorInput = colorItem.find("input[type='hidden']").val();
						
						$(".demo").attr("data-color",colorInput);
						
						visual();
					},"templateColor");
				});
			}
		});
	}
	visualOperation();
	
	/************************可视化主区域编辑 start**************************/
	//可视化区域编辑
	visualShell.on("click","*[ectype='model_edit']",function(){
		var $this = $(this),
			lyrow = $this.parents(".lyrow"),
			mode = lyrow.attr("data-mode"),
			purebox = lyrow.attr("data-purebox"),
			diff = lyrow.attr("data-diff"),
			range = lyrow.find("*[data-type='range']"), 
			lift = range.attr("data-lift");
		
		var hierarchy = '',
			masterTitle = '',
			spec_attr = '',
			pic_number = 0,
			where = '';
		
		if(!lift){
			lift = '';
		}
		
		spec_attr = lyrow.find('.spec').data('spec');
		
		if(purebox == "banner"){
			purebox = "adv";
		}
                
		switch(purebox){
			case "homeAdv":
				//广告模块编辑
				if(mode == 'h-sepmodule'){
                    hierarchy = $this.parents("*[ectype='module']").find(".sepmodule_warp").data('hierarchy');
                    spec_attr = $this.parents("*[ectype='module']").find(".spec").data('spec');
                }else if(mode == 'h-master' || mode == 'h-storeRec'){
					masterTitle = lyrow.find('.spec').data('title');
				}
			
				spec_attr = JSON.stringify(spec_attr);
				Ajax.call('dialog.php', "act=home_adv&mode=" + mode + '&spec_attr=' + encodeURIComponent(spec_attr) + "&masterTitle=" + escape(masterTitle) + "&lift=" + lift + "&diff=" + diff + "&hierarchy=" + hierarchy,dialogResponse, 'POST', 'JSON');	
			
			break; 
			
			case "homeFloor":
				//楼层模块编辑	
				if(mode == 'homeFloorModule'){
                    hierarchy = $this.parents("*[ectype='module']").find(".view").data('hierarchy');
                    spec_attr = $this.parents("*[ectype='module']").find(".spec").data('spec');
                }
				
				spec_attr = JSON.stringify(spec_attr);
				var reg=/&/g;
				spec_attr=spec_attr.replace(reg,'＆');
                Ajax.call('dialog.php', 'act=homeFloor' + "&mode=" + mode + '&spec_attr=' + spec_attr + "&diff=" + diff + "&lift=" + lift + "&hierarchy=" + hierarchy, dialogResponse, 'POST', 'JSON');
			break;
			
			case "cust":
				//自定义模块编辑
				var custom_content = encodeURIComponent(range.html());	
                Ajax.call('dialog.php', 'act=custom' + '&mode=' + mode + '&custom_content=' + custom_content + "&diff=" + diff  + "&lift=" + lift, customResponse, 'POST', 'JSON');
			break;
			
			case "adv":
				//广告模块编辑
				pic_number = lyrow.data("length");
					
                spec_attr = JSON.stringify(spec_attr);
				var reg=/&/g;
				spec_attr=spec_attr.replace(reg,'＆');
                Ajax.call('dialog.php', "act=shop_banner&spec_attr=" + spec_attr  + "&pic_number=" + pic_number + "&mode=" + mode + "&diff=" + diff, query_banner, 'POST', 'JSON');
			break;
			
			case "nav_mode":
				//导航模板编辑
                spec_attr = JSON.stringify(spec_attr);
				
				if(section == "vis_topic" || adminpath == 'admin'){
					where = '&topic=1';
				}
                                
				Ajax.call('dialog.php', 'act=nav_mode' + '&mode=' + mode + '&cat=cat' + '&spec_attr=' + encodeURIComponent(spec_attr) + where, navigatorResponse, 'POST', 'JSON');
			break;
			
			case "goods":
				//商品模块编辑
				spec_attr = JSON.stringify(spec_attr);
                Ajax.call('dialog.php', "act=goods_info" + "&mode=" + mode + "&diff=" + diff + "&spec_attr=" + encodeURIComponent(spec_attr) + "&lift=" + lift , query_goods, 'POST', 'JSON');
			break;
			
			case "header":
				//店铺可视化头部
                spec_attr = JSON.stringify(spec_attr);
                var custom_content = encodeURIComponent(lyrow.find('.spec').html());
                Ajax.call('dialog.php', 'act=header' + '&mode=' + mode + "&spec_attr=" + encodeURIComponent(spec_attr) + "&custom_content=" + custom_content + "&suffix="+suffix, headerResponse, 'POST', 'JSON');
			break;
			
			case "nav":
				//店铺可视化导航
                spec_attr = JSON.stringify(spec_attr);
                Ajax.call('dialog.php', 'act=navigator' + '&mode=' + mode + '&spec_attr=' + encodeURIComponent(spec_attr) + "&topic_type=" + topic_type, navigatorResponse, 'POST', 'JSON');
			break;
			
			case "CMS":
				spec_attr = JSON.stringify(spec_attr);
				Ajax.call('dialog.php', "act=edit_cmsAdv&mode=" + mode + '&spec_attr=' + encodeURIComponent(spec_attr) + "&diff=" + diff + "&hierarchy=" + hierarchy,dialogResponse, 'POST', 'JSON');	
			break
                        case "CMS_ARTI":
				spec_attr = JSON.stringify(spec_attr);
				Ajax.call('dialog.php', "act=edit_cmsarti&mode=" + mode + '&spec_attr=' + encodeURIComponent(spec_attr) + "&diff=" + diff,dialogResponse, 'POST', 'JSON');	
			break
                        case "CMS_THREE_LIE":
				spec_attr = JSON.stringify(spec_attr);
				Ajax.call('dialog.php', "act=edit_cmsarti&mode=" + mode + '&spec_attr=' + encodeURIComponent(spec_attr) + "&diff=" + diff,dialogResponse, 'POST', 'JSON');	
			break
                        case "CMS_FAST_LIE":
				spec_attr = JSON.stringify(spec_attr);
				Ajax.call('dialog.php', "act=edit_cmsarti&mode=" + mode + '&spec_attr=' + encodeURIComponent(spec_attr) + "&diff=" + diff,dialogResponse, 'POST', 'JSON');	
			break
                        case "CMS_HEAT_LIE":
				spec_attr = JSON.stringify(spec_attr);
				Ajax.call('dialog.php', "act=edit_cmsgoods&mode=" + mode + '&spec_attr=' + encodeURIComponent(spec_attr) + "&diff=" + diff,dialogResponse, 'POST', 'JSON');	
			break
		}
	});
	
	//首页可视化 区域编辑 banner悬浮用户信息内容编辑
	$(document).on("click","*[ectype='vipEdit']",function(){
		var obj = $(this).parents(".vip-con"),
			mode = obj.find(".insertVipEdit").data("mode"),
			spec_attr = obj.find(".spec").data("spec");
			
		spec_attr = JSON.stringify(spec_attr);
		
		Ajax.call('dialog.php', 'act=vipEdit&spec_attr=' + encodeURIComponent(spec_attr), function(result){
			visual_edit_dialog("vip_dialog","用户信息",950,result.content,function(){
				vipEditInsert(mode);
			});
		}, 'POST', 'JSON');
	});
	
	//可视化编辑弹窗
	headerResponse = function(result){
		//店铺可视化头部编辑弹窗
		visual_edit_dialog("header_dialog","头部编辑器",950,result.content,function(){
			 header_back(result.mode,$("#header_dialog"));
		});
		
		/* 店铺可视头部回调函数 */
		function header_back(mode,obj) {
            var header_type =  obj.find("input[name='header_type']:checked").val(),
            	headerbg_img = obj.find("input[name='fileimg']").val(),
				picHeight = obj.find("input[name='picHeight']").val(),
				custom_content = obj.find("input[name='custom_content']").val(),
				spec_attr = new Object(),
				range = $("*[data-mode=" + mode + "]").find('[data-type="range"]');
			
            spec_attr.header_type = header_type;
            spec_attr.headerbg_img = headerbg_img;
            spec_attr.picHeight = picHeight;
//            spec_attr.custom_content = custom_content;

            if(header_type == 'defalt_type'){
            	range.html("<div class='spec' data-spec='"+$.toJSON(spec_attr)+"' style='height:"+picHeight+"px;'><img src='"+headerbg_img+"' /></div>");
            }else{
            	range.html("<div class='spec' data-spec='"+$.toJSON(spec_attr)+"' style='height:"+picHeight+"px;'>"+custom_content+"</div>");
            }
			
            visual();
        }
	},
	navigatorResponse = function(result){
		//平台可视化 导航编辑弹出窗口
		visual_edit_dialog("navigator_dialog","导航编辑器",850,result.content,function(){
			if((section == "vis_home" || section == "vis_seller_store") && adminpath != 'admin'){
				navigator_back(result.mode)
			}
            
			if(section == "vis_topic" || section == "vis_seller_topic" || adminpath == 'admin'){
				navigator_back_topic(result.mode);
			}
		});
		
		/* 首页可视化 导航回调函数 */
		function navigator_back(mode) {
			if(section == "vis_seller_store"){
				var spec_attr = new Object(),
					obj = $("#navigator_dialog"),
					navColor = '',
					target = '',
					align = '',
					url = '';

				navColor = obj.find(".navColor").val();
				align = obj.find("select[name='align']").val();
				target = obj.find("input[name='target']:checked").val();
				
				spec_attr.navColor = navColor;
				spec_attr.align = align;
				spec_attr.target = target;
				
				Ajax.call('get_ajax_content.php', "act=navigator" + "&spec_attr=" + encodeURIComponent($.toJSON(spec_attr))  + "&mode=" + mode, addnavigatorResponse, 'POST', 'JSON');
			}else{
				var actionUrl = "get_ajax_content.php?act=nav_mode";  
				$("#navInsert").ajaxSubmit({
					type: "POST",
					dataType: "JSON",
					url: actionUrl,
					data: { "action": "TemporaryImage" },
					success: function (result){
						var obj = $("*[data-mode=" + mode + "]").find('[data-type="range"]');

						obj.html(result.content);
						obj.find(".spec").remove();
						obj.append("<div class='spec' data-spec='"+$.toJSON(result.spec_attr)+"'></div>");
						
						visual(1);
					},
					async: true  
				})
			}
        }
		
		/* 专题可视化 导航回调函数 */
		function navigator_back_topic(mode){
            var actionUrl = "get_ajax_content.php?act=navInsert&adminpath="+adminpath;  
            $("#navInsert").ajaxSubmit({
                type: "POST",
                dataType: "JSON",
                url: actionUrl,
                data: { "action": "TemporaryImage" },
                success: function (result){
					var obj = $("*[data-mode=" + mode + "]").find('[data-type="range"]');
					
                    obj.html(result.content);
                   	obj.find(".spec").remove();
                    obj.append("<div class='spec' data-spec='"+$.toJSON(result.spec_attr)+"'></div>");
					
                    visual();
                },
                async: true  
            })
        }
		
        function addnavigatorResponse(result){
			var obj = $("*[data-mode=" + result.mode + "]").find('[data-type="range"]');
			
			obj.html(result.content);
            obj.find(".spec").remove();
            obj.append("<div class='spec' data-spec='"+result.spec_attr+"'></div>");
			
			if(section == "vis_seller_store"){
            	obj.parents(".nav_bg").css({"background-color":result.navColor});
				visual();
			}else{
				visual(1);
			}
        }
	},
	dialogResponse = function(result){
		//楼层编辑弹出窗口
		var id = "dialog_" + result.mode;
		
		visual_edit_dialog(id,"内容编辑",950,result.content,function(){
			var obj = $("#dialog_"+ result.mode),
				required = obj.find("*[ectype='required']");

			//CMS可视化文章分类必选
			if(result.mode == 'CMS_TWO_LIE' || result.mode == 'CMS_THREE_LIE' || result.mode == 'CMS_FAST_LIE'){
				var idssign = $("input[name='cat_idsign[]']").length;
				if(!idssign > 0){
					alert("请选择分类");
					return false;
				}
            }
			
			if(validation(required) == true){
				responseInsert(result.mode,result.diff,result.hierarchy);
				return true;
			}else{
				return false;
			}
		});
		
		//回调函数
		function responseInsert(mode,diff,hierarchy){
            var actionUrl = '', act = '', obj = '', t = '';
			
            if( mode == 'homeFloor' || mode == 'homeFloorModule' || mode == 'homeFloorThree' || mode == 'homeFloorFour' || 
				mode == 'homeFloorFive' || mode == 'homeFloorSix' || mode == 'homeFloorSeven' || mode == 'homeFloorSeven' || 
				mode == 'homeFloorEight' || mode == 'homeFloorNine' || mode == 'homeFloorTen' || mode == 'storeOneFloor1' || 
				mode == 'storeTwoFloor1' || mode == 'storeTwoFloor1' || mode == 'storeThreeFloor1' || mode == 'storeFourFloor1' || 
				mode == 'storeFiveFloor1' || mode == 'topicOneFloor' || mode == 'topicTwoFloor' || mode == 'topicThreeFloor'){
				
				//删除楼层模板模式未选中的默认值
				$("[ectype='floormodeItem']").each(function(){
					if(!$(this).find("input[name='floorMode']").is(':checked')) {
						$(this).find("[ectype='floorModehide']").remove();
					}
				});
				
				act = 'homeFloor';
            }else if(mode == 'h-brand'){
                act = "homeBrand";
            }else if(mode == 'CMS_ADV'){
                act = "edit_cmsIn";
                //删除楼层模板模式未选中的默认值
                $("[ectype='floormodeItem']").each(function(){
                        if(!$(this).find("input[name='floorMode']").is(':checked')) {
                                $(this).find("[ectype='floorModehide']").remove();
                        }
                });
            }
            else if(mode == 'CMS_TWO_LIE' || mode == 'CMS_THREE_LIE' || mode == 'CMS_FAST_LIE'){
                act = "edit_cmsarti";
            }
            else if(mode == 'CMS_HEAT_LIE'){
                act = "edit_cmsgoods";
            }
            else if(mode == 'h-promo' || mode == 'h-sepmodule'){
                act = "honePromo";
            }else{
				act = "homeAdvInsert";
			}
			
            actionUrl = "get_ajax_content.php?act=" + act;
            $("#"+mode+"Insert").ajaxSubmit({
				type: "POST",
				dataType: "JSON",
				url: actionUrl,
				data: { "action": "TemporaryImage"},
				success: function (data) {
					if(data.error == 1){
						alert(data.massege);
					}else{
						if(mode == 'CMS_ADV' || mode == 'CMS_TWO_LIE' ||  mode == 'CMS_THREE_LIE' || mode == 'CMS_FAST_LIE' || mode == 'CMS_HEAT_LIE'){
							obj = $(".demo *[data-mode=" + mode + "]");
						}else{
							obj = $(".demo *[data-mode=" + mode + "][data-diff=" + diff + "]");
						}
						
						if(mode == 'h-sepmodule'){
							t = obj.find("*[data-hierarchy='" + hierarchy + "']");
						}else if(mode == 'homeFloorModule'){
							t = obj.find("*[data-hierarchy='" + hierarchy + "']").find("[data-type='range']");
						}else{
							t = obj.find('[data-type="range"]');
						}
						
						t.attr("id",mode + "_" + diff);
						
						t.html(data.content);
						t.find(".spec").remove();
						t.append("<div class='spec' data-spec='"+$.toJSON(data.spec_attr)+"' data-title='"+data.masterTitle+"'></div>");
						if(data.lift){
							obj.find('[data-type="range"]').attr("data-lift",data.lift);
						}
					}

					f_defaultBrand();
					visual();
				},
				async: true  
        	});
		}
	},
	query_banner = function(result){
		//广告编辑弹出窗口
		visual_edit_dialog("banner_dialog","图片编辑器",950,result.content,function(){
			var obj = $("#banner_dialog"),
				required = obj.find("*[ectype='required']");
			
			if(validation(required) == true){
				addshop_banner("#banner_dialog",result.mode,result.diff);
				return true;
			}else{
				return false;
			}
		});
		
		function addshop_banner(obj,mode,diff){
            var spec_attr = new Object(),
            	pic_src = [],
            	link = [],
            	sort = [],
            	bg_color = [];
			
			var obj = $(obj),
				picHeight_val = obj.find("input[name='picHeight']").val(),
				picWidth_val = obj.find("input[name='picWidth']:checked").val()
				slideType_length = obj.find("input[name='slide_type']").length,
				target_val = obj.find("input[name='target']:checked").val(),
				itemsLayout_val = obj.find("input[name='itemsLayout']").val(),
				navColor_val = obj.find("input[name='navColor']").val();
				
            if(picHeight_val){
            	spec_attr.picHeight = picHeight_val;
            }

			if(picWidth_val){
            	spec_attr.picWidth = picWidth_val;
            }
			
			if(slideType_length>0){
				spec_attr.slide_type = obj.find("input[name='slide_type']:checked").val();
			}
			
			if(target_val){
				spec_attr.target = target_val;
			}
			
			if(itemsLayout_val){
				spec_attr.itemsLayout = itemsLayout_val;
			}
			
			if(navColor_val){
				spec_attr.navColor = navColor_val;
			}
			
            //图片路径
            obj.find("input[name='pic_src[]']").each(function(){
                var psrc = $(this).val();
                pic_src.push(psrc);
            });
			
            //图片链接
            obj.find("input[name='link[]']").each(function(){
                var plink = $(this).val();
                link.push(plink);
            });
			
            //排序
            obj.find("input[name='sort[]']").each(function(){
                var psort = $(this).val();
                sort.push(psort);
            });
			
            //背景
            obj.find("input[name='bg_color[]']").each(function(){
                var pbg_color = $(this).val();
                bg_color.push(pbg_color);
            });
			
            if($("*[data-mode=" + mode + "]").data('li')){
                spec_attr.is_li = $("*[data-mode=" + mode + "]").data('li');
            }else{
                spec_attr.is_li = 0;
            }
			
			//店铺新模板广告新增标题
			/*if(mode == "storeAdv1"){
				spec_attr.is_title = obj.find("input[name='is_title']:checked").val();
				spec_attr.cat_name = obj.find("input[name='cat_name']").val();
				spec_attr.cat_desc = $('input[name="cat_desc"]').val();
			}*/
			
            spec_attr.bg_color = bg_color;
            spec_attr.pic_src = pic_src;
            spec_attr.link = encodeURIComponent(link);
            spec_attr.sort = sort;
            
            Ajax.call('get_ajax_content.php', "act=addmodule&diff=" + diff  + "&mode=" + mode + "&spec_attr=" +  $.toJSON(spec_attr), addmoduleResponse, 'POST', 'JSON');
        }
		
		function addmoduleResponse(data) {
            var type = '', obj = '', range = '';
			
			if(data.mode == "topBanner"){
				obj = $("*[data-mode='topBanner']");
				obj.find(".top-banner").css({"background":data.navColor});
				range = obj.find("*[data-type='range']");
				type = 2;
			}else{
				obj = $(".demo *[data-mode=" + data.mode + "][data-diff="+data.diff+"]");
				range = obj.find("*[data-type='range']");
			}

			if(data.mode == "lunbo"){
				range.attr("data-slide",data.slide_type);
			}else if(data.mode == "advImg1"){
				range.attr("data-slide",data.slide_type);
				if(data.picWidth != undefined){
					range.parents(".visual-item").removeClass("w1200").removeClass("w1900").addClass("w"+data.picWidth);
				}else{
					range.parents(".visual-item").addClass("w1200");
				}
			}else if(data.mode == "advImg2"){
				range.removeClass().addClass("advImgtwo");
			}else if(data.mode == "advImg3"){
				range.removeClass().addClass(data.itemsLayout);
			}else if(data.mode == "advImg4"){
				range.removeClass().addClass(data.itemsLayout);
			}

			range.html(data.content);
            range.siblings(".spec").remove();
            range.after("<div class='spec' data-spec='"+data.spec_attr+"'>");
			
            sider();
            visual(type);
        }
		
		//根据cookie默认选中图片库筛选方式
		album_select(0);
	},
	query_goods = function(result){
		//商品编辑弹出窗口
		visual_edit_dialog("goods_dialog","商品编辑器",950,result.content,function(){
			var obj = $("#goods_dialog"),
				required = obj.find("*[ectype='required']");
			
			if(validation(required) == true){
				replace_goods(result.mode,result.diff,obj)
				return true;
			}else{
				return false;
			}
		});
		
		function replace_goods(mode,diff,obj) {
            var spec_attr = new Object(),
				lift = "";
			
            spec_attr.goods_ids = obj.find("input[name='goods_ids']").val();
            spec_attr.cat_name = obj.find("input[name='cat_name']").val();
            spec_attr.is_title = obj.find("input[name='is_title']:checked").val();
			
			if(section != "vis_home"){
				spec_attr.cat_desc = $('input[name="cat_desc"]').val();
				spec_attr.align = $('select[name="align"]').val();
				spec_attr.itemsLayout = $("input[name='itemsLayout']").val();
			}
			
            lift = obj.find("input[name='lift']").val();
			
			if(section == "vis_home"){
            	Ajax.call('get_ajax_content.php?is_ajax=1&act=changedgoods', "temp="+mode+"&spec_attr=" + $.toJSON(spec_attr) + "&diff=" + diff + "&mode=" + mode + "&lift=" + lift, replaceResponse, 'POST', 'JSON');
			}else{
				Ajax.call('get_ajax_content.php?is_ajax=1&act=changedgoods', "temp=replace&spec_attr=" + $.toJSON(spec_attr) + "&diff=" + diff + "&mode=" + mode, replaceResponseOther, 'POST', 'JSON');
			}
        }
    	
		/* 首页可视化 商品回调 */
        function replaceResponse(data) {
			var obj = $(".demo *[data-mode=" + data.mode + "][data-diff="+data.diff+"]"),
				goodsTitle = obj.find("*[data-goodsTitle='title']"),
				range = obj.find("*[data-type='range']");
			
			//设置商品楼层是否显示标题
			if(data.is_title == 1){
				if(data.mode=='guessYouLikehot' || data.mode=='guessYouLikebest'){
					goodsTitle.html("<div class='ftitb'><h3>" + data.cat_name + "</h3></div>");
				}else{
					goodsTitle.html("<div class='ftit'><h3>" + data.cat_name + "</h3></div>");
				}
			}else{
				goodsTitle.html("");
			}
			
            //替换楼层内容
			range.find("ul").html(data.content);
            range.find(".spec").remove();
			range.append("<div class='spec' data-spec='"+data.spec_attr+"'></div>");
			
			if(data.lift){
				range.attr("data-lift",data.lift);
			}
			
			//页面储存商品id，前台异步用
			obj.attr("data-goodsid",data.goods_ids);
            visual();
        }
		
		/* 专题可视化 商品回调 */
		function replaceResponseOther(data) {
			var obj = $("*[data-mode=" + data.mode + "][data-diff="+data.diff+"]");
			
			//设置商品楼层是否显示标题
			if(data.is_title == 1){
				obj.find(".mt").remove();
				obj.find(".mc").before("<div class='mt'><div class='con'><h2>" + data.cat_name + "</h2><p>" + data.cat_desc + "</p></div></div>");
			}else{
				obj.find(".mt").remove();
			}
		
			//替换楼层内容
			obj.find('[data-type="range"] ul').removeClass().addClass(data.itemsLayout);	
			obj.find('[data-type="range"] ul').html(data.content);
			obj.find('input[name="goods_info"]').val(data.goods_ids);
			obj.find('.mc').find(".spec").remove();
			obj.find('.mc').append("<div class='spec' data-spec='"+data.spec_attr+"'></div>");
			obj.attr("data-goodsId",data.goods_ids);
			
			visual();
		}
	},
	customResponse = function(result){
		visual_edit_dialog("custom_dialog","自定义编辑器",1000,result.content,function(){
			var obj = $("#custom_dialog"),
				required = obj.find("*[ectype='required']");
			
			if(validation(required) == true){
				custom_back(result.mode,result.diff,obj)
				return true;
			}else{
				return false;
			}
		});
		
		function custom_back(mode,diff,obj) {
            var custom_content = obj.find("input[name='custom_content']").val(),
            	lift = obj.find("input[name='lift']").val(),
				range = $("*[data-mode=" + mode + "][data-diff="+diff+"]").find('[data-type="range"]');
				
			if(lift){
                range.attr("data-lift",lift);
            }	

            range.html(custom_content);
			
            visual();
        }
	},
	vipEditInsert = function(mode){
		var spec_attr = new Object(),
			obj = $("#vip_dialog"),
			quick_name = [],
			quick_url = [],
			style_icon = [],
			align = '',
			index_article_cat = '';
		
		//图片链接
		obj.find("input[name='quick_name[]']").each(function () {
			var name = $(this).val();
			quick_name.push(name);
		});
		
		obj.find("input[name='quick_url[]']").each(function () {
			var url = $(this).val();
			quick_url.push(url);
		});
		
		obj.find("input[name='style_icon[]']").each(function () {
			var icon = $(this).val();
			style_icon.push(icon);
		});
		
		index_article_cat = $("input[name='index_article_cat']").val();
		
		spec_attr.quick_name = quick_name;
		spec_attr.quick_url = encodeURIComponent(quick_url);
		spec_attr.index_article_cat = index_article_cat;
		spec_attr.style_icon = style_icon;
		
		Ajax.call('get_ajax_content.php', "act=insertVipEdit&spec_attr=" +  $.toJSON(spec_attr) + "&mode=" + mode, insertVipEditResponse, 'POST', 'JSON');
		
		function insertVipEditResponse(result){
			var obj = $(".demo *[data-mode=" + result.mode + "]");
			
			obj.siblings(".spec").remove();
			obj.after("<div class='spec' data-spec='"+result.spec_attr+"'>");
			obj.html(result.content);
			
			visual();
		}
	}
	
	//可视化编辑区域弹窗方法封装函数
	function visual_edit_dialog(id,title,width,content,onOk){
		pb({
			id:id,
			title:title,
			width:width,
			content:content,
			ok_title:"确定",
			cl_cBtn: false,
			onOk:onOk
		});
	}
	/************************可视化主区域编辑 end**************************/
	
	/************************可视化编辑区域弹窗内 触发js start********************/
	/* 弹窗内标签切换 */
	$(document).on("click",".tab li",function(){
		var index = $(this).index();
		$(this).addClass("current").siblings().removeClass("current");
		$(".modal-body").find(".body_info").eq(index).show().siblings().hide();
	});
	
	/* 弹窗广告模式切换 */
	$(document).on("click", ".itemsLayout",function(){
		$(this).find(".itemsLayoutShot").addClass("dtselected");
		$(this).siblings().find(".itemsLayoutShot").removeClass("dtselected");
		$("input[name='itemsLayout']").val($(this).data("line"));
	});
	
	/* 弹窗内已选择广告删除 */
	$(document).on("click",".pic_del",function(){
		var tbody = $(this).parents("tbody");
		var table = tbody.parent("table").data("table");
		
		$(this).parents("tr").remove();
		
		if(tbody.find("tr").length < 1){
			if(table == "navtable"){
				tbody.append("<tr class='notic'><td colspan='4'>当前没有自定义商品分类，点击下面添加新分类添加</td></tr>")
			}else{
				tbody.append("<tr class='notic'><td colspan='5'>点击下列图片空间图片可添加图片或点击上传图片按钮上传新图片</td></tr>")
			}
		}
	});
	
	/* 弹窗内 店铺可视化 导航分类选择 */
	$(document).on("change","#catagory_type",function(){
		var val = $(this).val();
		var fald = false;
		if(val == 1){
			$(this).siblings("#sys_catagory").hide();
			$(this).siblings("input[name='custom_catagory']").show();
			fald = true;
		}else if(val == 2){
			$(this).siblings("input[name='custom_catagory']").hide();
			$(this).siblings("#sys_catagory").show();
			fald = true;
		}else{
			$(this).siblings("input[name='custom_catagory']").hide();
			$(this).siblings("#sys_catagory").hide();
			fald = false;
		}
		
		if(fald == true){
			$(this).siblings(".btn").removeClass("btn_disabled");
		}else{
			$(this).siblings(".btn").addClass("btn_disabled");
		} 
	});
	
	/* 弹窗内 店铺可视化 导航分类添加 */
	$(document).on("click","*[ectype='addCatagory'] *[ectype='store_btn']",function(){
		var tbody = $(this).parents(".body_info").find("tbody");
		
		if(!$(this).hasClass("btn_disabled")){
			var catagory_type = $("#catagory_type").val();
			var link ='';
			var nav_name = '';
			
			if(tbody.find(".notic").length>0){
				tbody.find(".notic").remove();
			}
			if(catagory_type == '1'){
				nav_name = $("input[name='custom_catagory']").val();
			}else if(catagory_type == '2'){
				var cat = $("#sys_catagory").val();
				cat = cat.split("|");
				nav_name = cat[2];
				link = cat[3];
			}
			Ajax.call('get_ajax_content.php?is_ajax=1&act=add_nav', "link=" +encodeURIComponent(link) + "&nav_name=" + nav_name  , function(data){
				if(data.error == 0){
					alert(data.content);
				}else{
					tbody.append(data.content)
				}
			}, 'POST', 'JSON');
			
		}else{
			alert("请选择添加分类类型");
		}
	});
	
	/* 弹窗内 专题可视化 导航分类添加 */
	$(document).on("click","*[ectype='addCatagory'] *[ectype='topic_btn']",function(){
		var tbody = $(this).parents(".body_info").find("tbody");
		var nav_name = '';

		if(tbody.find(".notic").length>0){
			tbody.find(".notic").remove();
		}
		nav_name = $("input[name='custom_catagory']").val();
		var html = '';
		if(nav_name){
			html += '<tr><td><input type="text" value="' +nav_name+ '" name="navname[]"></td>';
			html += '<td><input type="text" value="" name="navurl[]"></td>';
			html += '<td class="center"><input type="text" class="small" value="" name="navvieworder[]"></td>';
			html += '<td class="center"><a href="javascript:void(0);" onclick="remove_topicnav(this)" class="pic_del del">删除</a></td></tr>';
			tbody.append(html)
		}else{
			alert("分类名称不能为空");
		}
	});
	
	/* 弹窗内 楼层广告设置 广告图片选择上传 */
	$(document).on("click","*[ectype='uploadImage']",function(){
		var spec_attr = new Object(),
			pic_src = [],
			link = [],
			sort = [],
			title = [],
			subtitle = [];
						
		var t = $(this),
			pb_title = t.data("title"),
			pic_number = t.data("number"),
			showlink = t.data("showlink"),
			titleup = t.data("titleup"),
			uploadImage_type = t.data("uploadimagetype");
			content = '',
			uploadImage = 2;
			
		var imgValue  = t.siblings("[ectype='imgValue']"),
			inputName = imgValue.data('name'),
			mode_this = "";
		
		if(uploadImage_type == "home"){
			mode_this = $("input[name='floorMode']:checked").parents("*[ectype='floormodeItem']").find("*[ectype='floorModehide']");
		}else{
			mode_this = imgValue;
		}
		
		if(pic_number == 1 && showlink != 1 && uploadImage_type != "home"){
			uploadImage = 1;
		}
			
		//图片路径
		mode_this.find("input[name='" + inputName + "[]']").each(function () {
			var psrc = $(this).val();
			pic_src.push(psrc);
		});
		
		//图片链接
		mode_this.find("input[name='" + inputName + "Link[]']").each(function () {
			var plink = $(this).val();
			link.push(plink);
		});
		
		//排序
		mode_this.find("input[name='" + inputName + "Sort[]']").each(function () {
			var psort = $(this).val();
			sort.push(psort);
		});
		
		//主标题
		mode_this.find("input[name='" + inputName + "Title[]']").each(function () {
			var ptitle = $(this).val();
			title.push(ptitle);
		});
		
		//副标题
		mode_this.find("input[name='" + inputName + "Subtitle[]']").each(function () {
			var pSubtitle = $(this).val();
			subtitle.push(pSubtitle);
		});
		
		spec_attr.sort = sort;
		spec_attr.pic_src = pic_src;
		spec_attr.title = title;
		spec_attr.subtitle = subtitle;
		spec_attr.link = encodeURIComponent(link);
		
		Ajax.call('dialog.php', "act=shop_banner" + "&pic_number=" + pic_number + "&uploadImage="+uploadImage + "&spec_attr="+$.toJSON(spec_attr) + "&titleup=" + titleup, function(result){
			visual_edit_dialog("uploadImage",pb_title,950,result.content,function(){
				var back = '',
					html = '',
					input = '',
					url = '',
					obj = $("#uploadImage"),
					hiddenDiv = '';
					
				obj.find("input[name='pic_src[]']").each(function(){
					var psrc = $(this).val();
					if(psrc){
						input += "<input name='" + inputName + "[]' type='hidden' value='" + psrc + "'>";
						url = '<img src='+ psrc +'>';
						html += '<a href="'+ psrc +'" class="nyroModal" target="_blank"><i class="iconfont icon-image" onmouseover="toolTip('+"'"+url+"'"+')" onmouseout="toolTip()"></i></a>';
					}
				});
				
				obj.find("input[name='link[]']").each(function(){
					var link = $(this).val();
					input += "<input name='" + inputName + "Link[]' type='hidden' value='" + link + "'>";
				});
				
				obj.find("input[name='sort[]']").each(function(){
					var sort = $(this).val();
					input += "<input name='" + inputName + "Sort[]' type='hidden' value='" + sort + "'>";
				});
				
				obj.find("input[name='title[]']").each(function(){
					var title = $(this).val();
					input += "<input name='" + inputName + "Title[]' type='hidden' value='" + title + "'>";
				});
				
				obj.find("input[name='subtitle[]']").each(function(){
					var subtitle = $(this).val();
					input += "<input name='" + inputName + "Subtitle[]' type='hidden' value='" + subtitle + "'>";
				});
				
				
				if(uploadImage_type == "home"){
					hiddenDiv = mode_this.find("[ectype='" + inputName + "']");
					hiddenDiv.find("input[type='hidden']").remove();
					hiddenDiv.find("*[ectype='advimg']").html('').append(html);
					hiddenDiv.append(input);
					
					imgValue.html('').append(html);
				}else{
					mode_this.find("input[type='hidden']").remove();
					mode_this.find(".nyroModal").remove();
					mode_this.append(html+input); 	
				}
			});
			
			//判断弹出框是否需要加滚动轴
			pbct("#uploadImage");
			
			//根据cookie默认选中图片库筛选方式
			album_select(0);
			
		}, 'POST', 'JSON');
	});
	
	/* 可视化楼层 设置分类及商品 */
	function dialogFloorCate(){
		var addCate = "*[ectype='addCate']",
			removeCate = "*[ectype='removeCate']",
			iselectErji = "*[ectype='iselectErji']",
			iselectErji_a = "*[ectype='iselectErji'] li a",
			tit = "*[ectype='iselectErji'] *[ectype='tit']",
			setupGoods = "*[ectype='setupGoods']";

		
		/* 弹窗内 楼层分类设置 新增分类 */
		$(document).on("click",addCate,function(){
			var t = $(this),
				i = 0,
				number = t.parents(".control_value").data("catnumber"),
				parent = t.parents("*[ectype='item']"),
				clone = parent.clone(),
				remove = "<a href='javascript:void(0);' class='hdle' ectype='removeCate'>删除分类</a>";
			
			t.parents(".control_value").find("[ectype='item']").each(function(){
				i++;
			});
			
			if(number <= i){
				alert("此模块二级分类只能添加" + number + "个");
			}else{
				//处理克隆过后的内容
				clone.find("[ectype='addCate']").remove();
				clone.find("[ectype='tit']").html("请选择");
				clone.find("[name='cateValue']").val("");
				clone.find("[ectype='setupGoods']").hide();
				clone.find("[ectype='setupGoods']").after(remove);
				parent.after(clone);
			}
		});
		
		/* 弹窗内 楼层分类设置 删除已新增的分类 */
		$(document).on("click",removeCate,function(){
			var t = $(this),
				parent = t.parents("[ectype='item']");
				
			parent.remove();
		});
		
		/* 弹窗内 楼层分类设置 楼层2级分类select选择处理 start */
		$(document).on("click",tit,function(){
			var _this = $(this);
			var parent = _this.parents("*[ectype='iselectErji']");
			
			$("*[ectype='iselectErji'] ul").hide();
			
			parent.find("ul").show();
			parent.find("ul").perfectScrollbar("destroy");
			parent.find("ul").perfectScrollbar();
		});
		
		/* 弹窗内 楼层分类设置 判读是否选择分类，显示设置分类 */
		$(document).on("click","*[ectype='iselectErji'] li a",function(){
			var _this = $(this),
				value = _this.data("value"),
				input = _this.find("*[name='cateValue']"),
				text  = _this.html(),
				parent = _this.parents("*[ectype='iselectErji']"),
				parent_item = _this.parents("[ectype='item']"),
				fale = true;
				
			if(input && value > 0){
				parent_item.find("*[ectype='setupGoods']").show();
			}else{
				parent_item.find("*[ectype='setupGoods']").hide();
			}
			
			if(!_this.parent("li").hasClass("current")){
				parent_item.siblings().find("*[ectype='iselectErji']").each(function(index, element) {
					var val = $(element).find("input[name='cateValue[]']").val();
					if(value == val){
						alert("分类已存在，请重新选择分类！");
						parent.find("*[ectype='tit']").html("请选择");
						parent.find("input[name='cateValue[]']").val("");
						
						_this.parent("li").removeClass("current");
						parent.find("li").eq(0).addClass("current");
						
						//清除此分类设置的商品
						parent.siblings("*[ectype='setupGoods']").find("input[type='hidden']").val("");
						
						fale = false;
					}
					return fale;
				});
			}
			
			if(fale == true){
				_this.parent("li").addClass("current").siblings().removeClass("current");
				parent.find("*[ectype='tit']").html(text);
				parent.find("input[type=hidden]").val(value);
				
				//清除此分类设置的商品
				parent.siblings("*[ectype='setupGoods']").find("input[type='hidden']").val("");
			}
			
			parent.find("ul").hide();
		});
		
		/* 弹窗内 楼层分类设置 设置商品 */
		$(document).on("click",setupGoods,function(){
			var _this = $(this),
				spec_attr = new Object(),
				top = _this.data('top');
				
			if(top == 1){
				var good_number = _this.data("goodsnumber"),
				cat_id = $("input[name='Floorcat_id']").val(),
				cat_goods = $("input[name='top_goods']").val();
			}else{
				var good_number = _this.parents(".control_value").data("goodsnumber"),
				cat_id = _this.parents("[ectype='item']").find("input[name='cateValue[]']").val(),
				cat_goods = _this.parents("[ectype='item']").find("input[name='cat_goods[]']").val();
			}
			
			spec_attr.goods_ids = cat_goods;
			
			Ajax.call('dialog.php', "act=goods_info" + "&goods_type=1&cat_id=" + cat_id + "&spec_attr="+$.toJSON(spec_attr) + "&good_number=" + good_number , function(result){
				visual_edit_dialog("set_up_goods","设置商品",950,result.content,function(){
					var goods_ids = $("#set_up_goods").find("input[name='goods_ids']").val();
						
					if(top == 1){
						$("input[name='top_goods']").val(goods_ids)
					}else{
						_this.find("input[name='cat_goods[]']").val(goods_ids);
					}
				});
			}, 'POST', 'JSON');
		});
	}
	dialogFloorCate();
	
	/* 弹窗内 楼层分类设置 颜色选择 */
	$(document).on("click","*[ectype='colorItem']",function(){
		var t = $(this),
		val = t.find("input[type='hidden']").val();
		
		t.siblings("input[type='hidden']").val(val);
		t.addClass("selected").siblings().removeClass("selected");
	});
	
	/* 弹窗内 活动模块 内容设置 活动类型选择*/
	$(document).on("change","*[ectype='PromotionType']",function(){
		$("input[name='goods_ids']").val('');
		$("input[name='recommend']:checked").prop("checked", false);;
		ajaxchangedgoods(1);
	});
	
	/* 弹窗内 楼层品牌设置 品牌选择 */
	$(document).on("click","*[ectype='cliclkBrand']",function(){
		var _this = $(this),
			brand_ids = $("input[name='brand_ids']").val(),
			arr = '',
			brandId = _this.data("brand"),
			type = _this.data("type"),
			num = 0;
			
		arr = brand_ids.split(',');
			
		if(_this.hasClass("selected")){
			_this.removeClass("selected");
			for(var i =0;i<arr.length;i++){
				if(arr[i] == brandId){
					arr.splice(i,1);
				}
			}
		}else{
			if(type == "homeBrand"){
				num = 17;
			}else{
				var number = _this.parents("[ectype='brand_list']").data("bandnumber");
				if(number){
					num = number;
				}else{
					num = 10;
				}
			}
			if(arr.length < num){
				if(brand_ids){
					arr = brand_ids + ','+brandId;
				}else{
					arr = brandId;
				}
				_this.addClass("selected");
			}else{
				alert("品牌选择不能超过"+num+"个");
			}
		}
		$("input[name='brand_ids']").val(arr);
	});
	
	/* 楼层品牌未选择 自动生成default图片 */
	function f_defaultBrand(){
		var _this = $("*[ectype='defaultBrand']");
		var j = 0;
		_this.find(".item").each(function(){
			j ++;
		})
		var html = '<div class="item"><a href="#" target="_blank"><div class="link-l"></div><div class="img"><img src="../data/gallery_album/visualDefault/homeIndex_010.jpg" title="esprit"></div><div class="link"></div></a></div>';
		var i = 0;
		for (i = 0; i < 9; i++) {
			_this.append(html);
		}
	}
	
	/* 首页可视化 用户信息栏 图标选择框*/
	var hoverTimer, outTimer,hoverTimer2;
	$(document).on('mouseenter',"*[ectype='quickIcon']",function(){
		clearTimeout(outTimer);
		var $this = $(this);
		hoverTimer = setTimeout(function(){
			$this.siblings("*[ectype='iconItems']").show();
		},50);
	});

	$(document).on('mouseleave',"*[ectype='quickIcon']",function(){
		clearTimeout(hoverTimer);
		var $this = $(this);
		outTimer = setTimeout(function(){
			$this.siblings("*[ectype='iconItems']").hide();
		},50); 
	});
	
	$(document).on('mouseenter',"*[ectype='iconItems']",function(){
		clearTimeout(outTimer);
		hoverTimer2 = setTimeout(function(){
			$(this).show();
		});
	});
	
	$(document).on('mouseleave',"*[ectype='iconItems']",function(){
		$(this).hide();
	});
	
	$(document).on('click',"*[ectype='iconItems'] input[type='radio']",function(){
		var val = $(this).val();
		$(this).parents("[ectype='iconItems']").find("input[name='style_icon[]']").val(val);
	});
	
	/************************可视化编辑区域弹窗内 触发js end**********************/
	
	/* 生成缓存文件 */
	function visual(temp) {
        if (suffix) {
            var content = $(".pc-page").html(),
                    content_html = '',
                    preview = '',
                    nav_content = $("*[ectype='nav']").html(),
                    topBanner_content = $("*[data-homehtml='topBanner']").html(),
                    topBanner = '',
                    navlayout = '',
                    where = '',
                    nav_html = '';

            if (section == "vis_home") {
                if (temp == 1) {
                    //导航栏html
                    navlayout = $("#head-layout");

                    navlayout.html("");
                    navlayout.append(nav_content);

                    navlayout.find(".categorys").remove();
                    navlayout.find(".setup_box").remove();
                    content_html = navlayout.html();
                } else if (temp == 2) {
                    //导航栏html
                    topBanner = $("#topBanner-layout");

                    topBanner.html("");
                    topBanner.append(topBanner_content);
                    topBanner.find(".categorys").remove();
                    topBanner.find(".setup_box").remove();
                    content_html = topBanner.html();
                } else {
                    //全部内容页html(不包括头部和导航)
                    preview = $("#preview-layout");

                    preview.html("");

                    preview.append(content);

                    preview.find("*[ectype='user_info']").html("<i class='icon-spin'><img src='../data/gallery_album/visualDefault/load_spin.gif' width='30' height='30'></i>");

                    preview.find("*[data-html='not']").remove();
                    preview.find(".lyrow").removeClass("lyrow");
                    preview.find(".ui-draggable").removeClass("ui-draggable");
                    preview.find(".ui-box-display").removeClass("ui-box-display");
                    preview.find(".lunbotu").removeClass("lunbotu");
                    preview.find(".demo").removeClass().addClass("content");
                    preview.find(".spec").attr("data-spec", '');

                    preview.find(".pageHome").remove();
                    preview.find(".nav").remove();
                    preview.find(".setup_box").remove();
                    content_html = preview.html();
                }

                Ajax.call('visualcategory.php', "act=file_put_visual&content=" + encodeURIComponent(content) + "&content_html=" + encodeURIComponent(content_html) + "&suffix=" + suffix + "&temp=" + temp, file_put_visualResponse, 'POST', 'JSON');
            } else if (section == "vis_topic") {

                preview = $("#preview-layout");

                preview.html("");

                preview.append(content);

                preview.find("*[data-html='not']").remove();
                preview.find(".lyrow").removeClass("lyrow");
                preview.find(".ui-draggable").removeClass("ui-draggable");
                preview.find(".ui-box-display").removeClass("ui-box-display");
                preview.find(".lunbotu").removeClass("lunbotu");
                preview.find(".demo").removeClass().addClass("content");
                preview.find(".spec").attr("data-spec", '');

                /* 导航 */
                preview.find(".categorys").remove();
                preview.find(".setup_box").remove();
                nav_html = preview.find('[ectype="nav"]').html();

                /* 内容 */
                preview.find(".pageHome,.nav").remove();
                content_html = preview.html();

                Ajax.call('topic.php', "act=file_put_visual&content=" + encodeURIComponent(content) + "&content_html=" + encodeURIComponent(content_html) + "&nav_html=" + encodeURIComponent(nav_html) + "&suffix=" + suffix + "&topic_type=" + topic_type, file_put_visualResponse, 'POST', 'JSON');

            } else {
                preview = $("#preview-layout");
                preview.html("");

                preview.append(content);

                preview.find("*[data-html='not']").remove();
                preview.find(".lyrow").removeClass("lyrow");
                preview.find(".ui-draggable").removeClass("ui-draggable");
                preview.find(".ui-box-display").removeClass("ui-box-display");
                preview.find(".lunbotu").removeClass("lunbotu");
                preview.find(".demo").removeClass(".ui-sortable").addClass("content");
                preview.find(".spec").attr("data-spec", '');

                if (section == 'vis_seller_topic') {
                    /* 导航 */
                    preview.find(".categorys").remove();
                    preview.find(".setup_box").remove();
                    nav_html = preview.find('[ectype="nav"]').html();

                    /* 内容 */
                    preview.find(".pageHome,.nav").remove();
                    content_html = preview.html();

                    where = "&nav_html=" + encodeURIComponent(nav_html);
                } else {
                    content_html = preview.html();
                }

                //头部html
                var head_content = $('.hd_main').html()
                var headlayout = $("#head-layout");
                var head_html = '';
                var ajax_url = '';
                headlayout.html("");

                headlayout.append(head_content);

                headlayout.find("*[data-html='not']").remove();
                headlayout.find(".lyrow").removeClass("lyrow");
                headlayout.find(".ui-draggable").removeClass("ui-draggable");
                headlayout.find(".ui-box-display").removeClass("ui-box-display");
                headlayout.find(".lunbotu").removeClass("lunbotu");
                headlayout.find(".demo").removeClass().addClass("content");

                head_html = headlayout.html();
                if (adminpath == 'admin') {
                    ajax_url = 'topic.php';
                } else {
                    ajax_url = 'visual_editing.php';
                }
                Ajax.call(ajax_url, "act=file_put_visual&content=" + encodeURIComponent(content) + "&content_html=" + encodeURIComponent(content_html) + "&head_html=" + encodeURIComponent(head_html) + "&suffix=" + suffix + "&topic_type=" + topic_type + where, file_put_visualResponse, 'POST', 'JSON');
            }
        }else{
        	var content = $("[ectype='visualShell']").html(),
				content_html = '',
				preview = '',
				where = '';
				
                preview = $("#preview-layout");
                preview.html("");

                preview.append(content);

                preview.find("*[data-html='not']").remove();
                preview.find(".lyrow").removeClass("lyrow");
                preview.find(".ui-draggable").removeClass("ui-draggable");
                preview.find(".demo").removeClass(".ui-sortable").addClass("content");
                preview.find(".spec").attr("data-spec", '');
                
                content_html = preview.html();
                Ajax.call('topic.php', "act=file_put_visual&content=" + encodeURIComponent(content) + "&content_html=" + encodeURIComponent(content_html) + "&new=1" , file_put_visualResponse, 'POST', 'JSON');
        }
        //回调函数
            function file_put_visualResponse(result) {
                if (result.error == 0) {
                    suffix_obj.val(result.suffix);
                    $("[ectype='back']").show();
                } else {
                    alert("该模板不存在，请重试");
                }
            }
        
    }
		
	/* 更新左侧缓存文件 */
	function generate(type){
		var bgDiv = $("[data-style="+type+"]"),
			checkbox = bgDiv.find(".ui-checkbox"),
			bgimg = bgDiv.find("input[name='fileimg']"),
			bgColor = "",
			bgshow = "",
			bgalign = "",
			is_show = 0;
		
		if(checkbox.prop("checked") == true){
			bgColor = bgDiv.find(".tm-picker-trigger").val();
			is_show = 1;
		}
		
		if(bgimg != ""){
			bgshow = bgDiv.find(".bg-show-nr a.current").data("bg-show");
			bgalign = bgDiv.find(".bg-align-nr a.current").data("bg-align");
		}
                
		if(section == "vis_home" || section == "vis_topic" || adminpath == 'admin'){
			var hometype = '';
			if(section == "vis_home"){
				hometype = "&hometype=1";
			}
			Ajax.call('topic.php', "act=generate&suffix=" + suffix + "&bg_color=" +bgColor + "&is_show=" +is_show + "&type=" + type + "&bgshow=" + bgshow + "&bgalign=" + bgalign + hometype, generateResponse, 'POST', 'JSON');
		}else{
			Ajax.call('visual_editing.php', "act=generate&suffix=" + suffix + "&bg_color=" +bgColor + "&is_show=" +is_show + "&type=" + type + "&bgshow=" + bgshow + "&bgalign=" + bgalign, generateResponse, 'POST', 'JSON');
		}
		
		//回调函数
		function generateResponse(data){
			if(data.error == 1){
				visual();
			}else{
				alert(data.content);
			}
		}
	}
});


/* 可视化模板信息编辑 弹窗内 必填验证 */
function error_div(obj,error, is_error){
	var error_div = $(obj).parents('div.value').find('div.form_prompt');
	$(obj).parents('div.value').find(".notic").hide();
	
	if(is_error != 1){
		$(obj).addClass("error");
	}
	
	$(obj).focus();
	error_div.find("label").remove();
	error_div.append("<label class='error'><i class='icon icon-exclamation-sign'></i>"+error+"</label>");
}

/* 可视化模板信息编辑 弹窗内 图片链接标识 */
function resetHref(){
	$("*[ectype='see']").each(function(){
		var href = $(this).attr("href");
		$(this).attr("href",href + "?&" + +Math.random());
	});
	$("*[ectype='pic']").each(function(){
		var src = $(this).attr("src");
		$(this).attr("src",src + "?&" + +Math.random());
	});
}
 function checked_hearder_body(){
    var html = $(".prompt").prevAll();
    if(html.length == 0){
         Ajax.call('topic.php', "act=get_hearder_body", function(data){
             $(".prompt").before(data.content);
         }, 'POST', 'JSON');
    }
}
//上传方法
jQuery.upload_file = function(file,url,showImg,fn){
	$(file).change(function(){
		var _this = $(this);
		var actionUrl = url;
		var form = _this.parents("form");
		var hiddenInput = form.find("input[name='fileimg']");
		form.ajaxSubmit({
			type: "POST",
			dataType: "json",
			url: actionUrl,
			data: { "action": "TemporaryImage" },
			success: function (data) {
				if (data.error == "1") {
				   alert(data.prompt);
				}else if(data.error == "2") {
					if(showImg != ""){		
						$(showImg).attr('src',data.content); 
					}
					hiddenInput.val(data.content);
					if(fn){
						fn(_this,data.content);
					} 
				}
			},
			async: true
		});
	});
};

/*分类搜索的下拉列表*/
jQuery.category = function(){
	$(document).on("click",'.selection input[name="category_name"]',function(){
		$(this).parents(".selection").next('.select-container').show();
	});
	
	$(document).on('click', '.select-list li', function(){
		var obj = $(this);
		var cat_id = obj.data('cid');
		var cat_name = obj.data('cname');
		var cat_type_show = obj.data('show');
		var user_id = obj.data('seller');
		var table = obj.data('table');
		var url = obj.data('url');
		
		/* 自定义导航 start */
		if(document.getElementById('item_name')){
			$("#item_name").val(cat_name);
		}
		
		if(document.getElementById('item_url')){
			$("#item_url").val(url);
		}
		
		if(document.getElementById('item_catId')){
			$("#item_catId").val(cat_id);
		}
		/* 自定义导航 end */
		
		$.jqueryAjax('get_ajax_content.php', 'act=filter_category&cat_id='+cat_id+"&cat_type_show=" + cat_type_show + "&user_id=" + user_id + "&table=" + table, function(data){
			if(data.content){
				obj.parents(".categorySelect").find("input[data-filter=cat_name]").val(data.cat_nav); //修改cat_name
				obj.parents(".select-container").html(data.content);
				$(".select-list").perfectScrollbar("destroy");
				$(".select-list").perfectScrollbar();
			}
		});
		obj.parents(".categorySelect").find("input[data-filter=cat_id]").val(cat_id); //修改cat_id
		
		var cat_level = obj.parents(".categorySelect").find(".select-top a").length; //获取分类级别
		if(cat_level >= 3){
			$('.categorySelect .select-container').hide();		
		}
	});
	
	//点击a标签返回所选分类 by wu
	$(document).on('click', '.select-top a', function(){
		var obj = $(this);
		var cat_id = obj.data('cid');
		var cat_name = obj.data('cname');
		var cat_type_show = obj.data('show');
		var user_id = obj.data('seller');
		var table = obj.data('table');
		var url = obj.data('url');
		
		/* 自定义导航 start */
		if(document.getElementById('item_name')){
			$("#item_name").val(cat_name);
		}
		
		if(document.getElementById('item_url')){
			$("#item_url").val(url);
		}
		
		if(document.getElementById('item_catId')){
			$("#item_catId").val(cat_id);
		}
		/* 自定义导航 end */

		$.jqueryAjax('get_ajax_content.php', 'act=filter_category&cat_id='+cat_id+"&cat_type_show=" + cat_type_show + "&user_id=" + user_id + "&table=" + table, function(data){
			if(data.content){
				obj.parents(".categorySelect").find("input[data-filter=cat_name]").val(data.cat_nav); //修改cat_name
				obj.parents(".select-container").html(data.content);
				$(".select-list").perfectScrollbar("destroy");
				$(".select-list").perfectScrollbar();
			}
		});
		obj.parents(".categorySelect").find("input[data-filter=cat_id]").val(cat_id); //修改cat_id
	});	
	/*分类搜索的下拉列表end*/
}
