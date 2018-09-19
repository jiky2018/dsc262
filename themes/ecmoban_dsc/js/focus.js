(function(){
	function Focus(){
		$(".text").focus(function(){
			$(this).parents(".item").addClass("item-focus");
		});
		$(".text").blur(function(){
			$(this).parents(".item").removeClass("item-focus");
		});
	}
	Focus();
})()