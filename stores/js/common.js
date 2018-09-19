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
  /*var lastCheckOrder = new Date(document.getCookie('ECS_LastCheckOrder'));
  var today = new Date();

  if (lastCheckOrder == null || today-lastCheckOrder >= NEW_ORDER_INTERVAL)
  {
    document.setCookie('ECS_LastCheckOrder', today.toGMTString());

    try
    {
      Ajax.call('index.php?is_ajax=1&act=check_order','', checkOrderResponse, 'GET', 'JSON');
    }
    catch (e) { }
  }*/
}

/* *
 * 处理检查订单的反馈信息
 */
function checkOrderResponse(result)
{
  //出错屏蔽
  if (result.error != 0 || (result.new_orders == 0 && result.new_paid == 0))
  {
    return;
  }
  try
  {
    document.getElementById('spanNewOrder').innerHTML = result.new_orders;
    document.getElementById('spanNewPaid').innerHTML = result.new_paid;
    Message.show();
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

//jqueryAjax异步加载
$.jqueryAjax = function(url, data, ajaxFunc, type, dataType, async)
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
	
	if(async !== false)
	{
		async = true;
	}
	
	data = baseData + data;
	
	$.ajax({
		async:async,
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

//弹窗 by wu
function dialog(dialog_type, obj)
{
	//obj是json数据，这里是拓展数据处理
	var ext_info = "";
	if(obj.message){
		ext_info += "&message=" + obj.message;
	}
	
	if(obj.page){
		ext_info += "&page=" + obj.page;
	}
	
	var content = "";
	$.jqueryAjax("dialog.php", 'act=operate&dialog_type='+dialog_type+ext_info, function(data){
		if(data.content){
			content = data.content;
		}
	}, 'get', 'json', false);
	
	if(dialog_type == 'delete'){
		pb({
			id:"dialogHandle",
			title:"",
			content:content,
			drag:false,
			foot:true,
			ok_title:"确定",
			cl_title:"取消",
			onOk:function(){
				listTable.remove(obj.id, obj.opt);
			}
		});
	}else if(dialog_type == 'success' || dialog_type == 'ajaxfail'){
		if(obj.url == "index.php"){
			window.location.href = "index.php";
		}else{
			pb({
				id:"dialogHandle",
				title:"",
				content:content,
				drag:false,
				foot:false,
				timeCozuo: true,
				time : 3
			});
		}
	}else if(dialog_type == 'failure'){
		pb({
			id:"dialogHandle",
			title:"",
			content:content,
			drag:false,
			foot:true,
			timeCozuo: true,
			cBtn:false,
			cl_title:"请重新登录",
			time : 3
		});
	}
	for(var i = 3;i>=0;i--){
		window.setTimeout("doUpdate(" + i + ", '" + obj.url + "')", (3-i) * 1000); 
	}
	$(".cboxContent .pb-x").addClass("fail_icon");
	$(".cboxContent .pb-ft").addClass("failure_ft");	
	
}

//弹窗跳转
function doUpdate(num, url){
	$("#time").html(num);
	if(num == 0 && (url != '' && url != 'undefined' && url != null)){
		window.location.href = url;
	}	
}

//表单传值 by wu
function send_form_data(obj)
{
	var obj = $(obj);
	var method = obj.attr('method'); //方法：get/post
	var action = obj.attr('action'); //程序
	var inputs = obj.find("input"); //表单集合
	var textareas = obj.find("textarea"); //表单集合
	var data = new Array(); //数据集合
	inputs.each(function(){
		var type = $(this).attr('type');
		var name = $(this).attr('name');
		var value = "";		
		if(type == 'text' || type == 'password' || type == 'hidden'){ //文本
			value = $(this).val();
		}else if(type == 'radio'){ //单选
			if($(this).prop("checked") == true){
				value = $(this).val();
			}
		}else if(type == 'checkbox'){ //复选
			if($(this).prop("checked") == true){
				value = $(this).val();
			}
		}
		
		data.push(name+'='+value);
	})
	
	textareas.each(function(){
		var name = $(this).attr('name');
		var value = $(this).val();	
		data.push(name+'='+value);		
	})		

	var queryString = ""; //查询字符串
	queryString = data.join('&');
	

	$.ajax({
		type:method,
		url:action,
		data:queryString,
		dataType:'json',
		success:function(data){
			
			if(!data.page){
				data.page = 0;
			}
			if(data.error == 1){
				dialog('success', {message:data.message, url:data.url, page:data.page});
			}
                        else if(data.error == 2){
				dialog('ajaxfail', {message:data.message, url:data.url, page:data.page});
			}
                        else{
				dialog('failure', {message:data.message, url:data.url, page:data.page});
			}
		}
	});
}
$(function () {
//全选切换效果
    $(document).on("click", "input[name='all_list']", function () {
        if ($(this).prop("checked") == true) {
            $(".list-div").find("input[type='checkbox']").prop("checked", true);
            $(".list-div").find("input[type='checkbox']").parents("tr").addClass("tr_bg_org");
        } else {
            $(".list-div").find("input[type='checkbox']").prop("checked", false);
            $(".list-div").find("input[type='checkbox']").parents("tr").removeClass("tr_bg_org");
        }
        btnSubmit();
    });

    //列表单选
    $(document).on("click", ".sign .checkbox", function () {
        if ($(this).is(":checked")) {
            $(this).parents("tr").addClass("tr_bg_org");
        } else {
            $(this).parents("tr").removeClass("tr_bg_org");
        }
        btnSubmit();
    });

    $(document).on('click',"*[id='all']",function(){
        var frm = $("form[name='listForm']");
        var checkboxes = [];
        frm.find("input[name='checkboxes[]']").each(function(){
            var val = $(this).val();
            if(val){
                checkboxes.push(val);
            }
        });
        if(checkboxes){
            $(":input[name='order_id']").val(checkboxes);
        }

        btnSubmit()
    });

    function btnSubmit() {
        var length = $(".list-div").find("input[name='checkboxes[]']:checked").length;
        if (length > 0) {
            if ($("*[ectype='btnSubmit']").length > 0) {
                $("*[ectype='btnSubmit']").removeClass("btn_disabled");
                $("*[ectype='btnSubmit']").attr("disabled", false);
            }
        } else {
            if ($("*[ectype='btnSubmit']").length > 0) {
                $("*[ectype='btnSubmit']").addClass("btn_disabled");
                $("*[ectype='btnSubmit']").attr("disabled", true);
            }
        }
    }
});