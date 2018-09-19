// JavaScript Document
	$(document).ready(function(){
		
		//$('.zhuobox h1:first').addClass('active');	
		//$('.zhuobox .art_cont:not(:first)').hide();
		$('.zhuobox .art_cont').hide();  
		$('.zhuobox .art_cont .art_cont_h2').hide();
		$('.zhuobox .art_cont1 .art_cont_h2').hide();  
		$('.zhuobox h1').hover(function(){			
			//$(this).addClass('hover');
		},function(){
			$(this).removeClass('hover');	
			$(this).removeClass('active');	
		});
			
		$('.zhuobox h1').click(function(){		
			$(this).next('.art_cont').slideToggle().siblings('.art_cont').slideUp();	
			$(this).next('.art_cont1').slideToggle().siblings('.art_cont1').slideUp();	
			$(this).toggleClass('active').siblings('h1').removeClass('active');
			$(this).toggleClass('hover1 active').siblings('h1').removeClass('hover1 active');
		});
		
		$('.zhuobox h2').hover(function(){			
			//$(this).addClass('hover');
		},function(){
			$(this).removeClass('hover');	
		});
		
		$('.zhuobox h2').click(function(){		
			$(this).next('.art_cont_h2').slideToggle().siblings('.art_cont_h2').slideUp();	
			$(this).next('.art_cont_h3').slideToggle().siblings('.art_cont_h3').slideUp();	
			$(this).toggleClass('active').siblings('h2').removeClass('active');
		});
		
	});
		