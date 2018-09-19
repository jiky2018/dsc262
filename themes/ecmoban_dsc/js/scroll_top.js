// JavaScript Document
jQuery.scrollLeft = function(b){
	var top = $(b).offset().top;
	$(window).scroll(function(){
		var scrollTop = $(document).scrollTop();
		if(scrollTop>top){
			$(b).css({"position":"fixed","top":0});
		}else{
			$(b).css({"position":"absolute","top":0});
		}
	});
}