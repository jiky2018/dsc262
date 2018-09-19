//导航
$(function(){
	function Slide(id,li,tips){
		this.id=$("#"+id);
		this.li=this.id.find(li);
		this.tips=this.id.find("."+tips);
		this.index=0;
		this.init.apply(this,arguments);
		
	}
	Slide.prototype={
		init:function(){
			this.addEvent();
			this.run();
		},
		run:function(){
			this.tips.css({width:this.li.eq(0).outerWidth(),left:this.li.eq(0).position().left});
		},
		addEvent:function(){
			var _this=this;
			this.li.hover(function(){
				_this.index=_this.li.index(this);
				var left=$(this).position().left,w=$(this).outerWidth();
				_this.tips.stop().animate({left:left,width:w});
			},function(){
				_this.tips.stop().animate({left:_this.li.eq(0).position().left,width:_this.li.eq(0).outerWidth()});
			});
		}
	}
	new Slide("nav","li","wrap-line");
});