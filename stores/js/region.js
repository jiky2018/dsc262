/**
 * 地区联动js
 * @return
 */
jQuery.levelLink = function (){
	var opt = '.options',
	liv = '.options > .liv',
	txt = '.txt',
	input = 'input[type="hidden"]',
	dropdown = $('.smartdropdown');
	
        //select下拉默认值赋值
	$('.ui-dropdown').each(function()
	{
		var sel_this = $(this)
		var val = sel_this.children('input[type=hidden]').val();
		sel_this.find('.liv').each(function(){
			if($(this).attr('data-value') == val){
				sel_this.children('.txt').html($(this).html());
			}
		})
	});
        
	$(document).find(txt).on('click',dropdown,function(){
		var t = $(this);
		if(t.parent(dropdown).hasClass("visible")){
			t.parents(dropdown).removeClass("visible");
			t.nextAll(opt).hide();
		}else{
			t.parents(dropdown).addClass("visible");
			t.nextAll(opt).show();
			t.parents(dropdown).siblings().removeClass("visible");
			t.parents(dropdown).siblings().find(opt).hide();
		}
	});
	
	$(document).on('click',liv,function(){
		var t = $(this);
		var text = t.data("text");
		var value = t.data("value");
		var type = t.data("type");
		var old_val = t.parents(opt).prevAll(input).val()
		if(old_val != value){
                        if(type == 1){
                                $('#dlProvinces').children('.txt').html("省/直辖市");
				$('#dlProvinces').find('.options').html('<span class="liv hide" data-text="市" data-value="0">市</span>');
				$('#selProvinces').val(0);
                                $('#dlCity').children('div.txt').html("市");
				$('#dlCity').find('.options').html('<span class="liv hide" data-text="市" data-value="0">市</span>');
				$('#selCities').val(0);
				$('#dlRegion').find('.txt').html("区/县");
				$('#dlRegion').find('.options').html('<span class="liv hide" data-text="区/县" data-value="0">区/县</span>');
				$('#selDistricts').val(0);
                        }else if(type == 2){
				$('#dlCity').children('div.txt').html("市");
				$('#dlCity').find('.options').html('<span class="liv hide" data-text="市" data-value="0">市</span>');
				$('#selCities').val(0);
				$('#dlRegion').find('.txt').html("区/县");
				$('#dlRegion').find('.options').html('<span class="liv hide" data-text="区/县" data-value="0">区/县</span>');
				$('#selDistricts').val(0);
			}else if(type == 3){
				$('#dlRegion').find('.txt').html("区/县");
				$('#dlRegion').find('.options').html('<span class="liv hide" data-text="区/县" data-value="0">区/县</span>');
				$('#selDistricts').val(0);
			}
		}
		if(value != t.parents(opt).prevAll(input).val()){
			$.jqueryAjax('region.php', 'type='+type+'&parent='+value, function(data){
				t.parents(dropdown).next().find(opt).html(data);
				t.parents(dropdown).next().addClass("visible");
				t.parents(dropdown).next().find(opt).show();
				t.parents(dropdown).next().find(dropdown).addClass("visible");
			});
		}

		t.parents(opt).prevAll(input).val(value);
		t.parents(opt).prevAll(txt).html(text);
		t.parents(opt).hide();
		t.parents(dropdown).removeClass("visible");	
	});

	$(document).click(function(e){
		if(e.target.className !='txt' && !$(e.target).parents("div").is(opt)){
			$(opt).hide();
			$(dropdown).removeClass("visible");
		}
	});
	var i = 100;
	$(".smartdropdown").each(function(index, element) {
        $(this).css({"z-index":i--});
    });
}