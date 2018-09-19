/***
 * 涛涛Jquery弹出层插件
 * 编写时间：2013年7月25号
***/

;(function(window,undefined){
		var TTPopups=function(options)
			{
				this.opt=this.setOpt(options);
				this.container=this.$(this.opt.content);
				this.setup();
				this.closePupus();
			}
			TTPopups.prototype={
				setup:function(){
					if(!this.opt.isAutoShow)return;
					var oWrap=document.createElement('div'),
						oTitle=document.createElement('div'),
						oClose=document.createElement('div'),
						oContent=document.createElement('div'),
						oMark=document.createElement('div'),
						w=this.opt.width-46,h=this.opt.height-4,
						vieH=Math.max((document.documentElement.clientHeight||document.body.clientHeight),h),
						vieW=Math.max((document.documentElement.clientWidth||document.body.clientWidth),w);
						oTitle.style.cssText='height:42px;text-indent:10px;line-height:42px;color:#fff;background:#ec5151;overflow:hidden;';
						oClose.style.cssText='margin:12px;*margin-top:-30px;width:17px;height:15px;float:right;background:url(themes/ecmoban_sc/images/close.png) no-repeat center;cursor:pointer';
						var h1 = h+4;
						var w1 = w+64
						oWrap.style.cssText='opacity:0;filter:alpha(opacity:0);border:2px solid #333;position:absolute;z-index:99999;height:'+h+'px;width:'+w+'px;left:50%;top:22%;margin:-'+h1/2+'px 0 0 -'+w1/2+'px;background:#fff';
						oMark.style.cssText='opacity:0;filter:alpha(opacity:0);height:'+vieH+'px;width:'+vieW+'px;position:absolute;z-index:9999;left:0;top:0;';
						oContent.innerHTML=this.container.innerHTML;
						oTitle.innerHTML=this.opt.title;
						oTitle.appendChild(oClose);
						oWrap.appendChild(oTitle);
						oWrap.appendChild(oContent);
						document.body.appendChild(oWrap);
						document.body.appendChild(oMark);

						this.oClose=oClose;
						this.oWrap=oWrap;
						this.oMark=oMark;
						this.startMove(oWrap,100);
						this.startMove(oMark,100);
				},
				closePupus:function()
				{
					   if(!this.oClose)return;
					   this.addEvent(this.oClose,'click',this.bind(this,closefn));
					   function closefn()
					   {	
					   		var This=this;
					   	 	this.startMove(this.oMark,0,function(){
					   	 		 document.body.removeChild(This.oMark);
					   	 		 if(typeof This.opt.closeCallBack==='function')
					   	 		 {
					   	 		 	This.opt.closeCallBack();
					   	 		 }
					   	 	})
					   	 	this.startMove(this.oWrap,0,function(){
					   	 		document.body.removeChild(This.oWrap);
					   	 	})
					   }
				},
				bind:function(o,fn)
				{
					return function(){
						return fn.apply(o,arguments)
					}
				},
				setOpt:function(o)
				{   
					var defaultOptions={
						title:'物流查询',//title
						content:'TTPopups',	//请传入ID，或DOM对象
						width:600,			
						height:300,			//弹出层大小
						closeCallBack:null,	//关闭执行的回调函数
						isAutoShow:false	//自动显示
					};					
					if(o && Object.prototype.toString.call(o)=='[object Object]')
					{
						for(var k in o)
						{
							defaultOptions[k]= typeof o[k]==='undefined' ? defaultOptions[k] : o[k];
						}
					}
					return defaultOptions;
				},
				$:function(s)
				{
					return typeof s==='string' ? document.getElementById(s) : s;
				},
				addEvent: function(e, n, o){
					if(e.addEventListener){
							e.addEventListener(n, o,false);
					} else if(e.attachEvent){
							e.attachEvent('on' + n, o);
					}
				},
				startMove:function(obj,t,callBack)
				{
					clearInterval(obj.t);
					var This=this;
					obj.t=setInterval(function(){
				        var iCur=This.getStyle(obj,'opacity').toFixed(2)*100,iSpeed=(t-iCur)/10;
			        	iSpeed=iSpeed>0 ? Math.ceil(iSpeed): Math.floor(iSpeed);
		            	obj.style.filter='alpha(opacity:'+(iSpeed+iCur)+')';
		            	obj.style.opacity=(iCur+iSpeed)/100;
				        if(iCur==t)
				        {
			        	 	clearInterval(obj.t);
			        	 	if(typeof callBack==='function')callBack();
				        }
					},50)
				},
				getStyle:function(o,a)
				{
					return o.currentStyle ? parseFloat(o.currentStyle[a]) : parseFloat(getComputedStyle(o,false)[a]);
				}
			}

			window.TTPopups=TTPopups;

	})(window,undefined);