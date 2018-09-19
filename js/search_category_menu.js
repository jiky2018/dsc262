/**
 * --------------------------------------------------------------------
 * jQuery tree plugin
 * Author: Scott Jehl, scott@filamentgroup.com
 * Copyright (c) 2009 Filament Group 
 * licensed under MIT (filamentgroup.com/examples/mit-license.txt)
 * --------------------------------------------------------------------
 */
$.fn.tree = function(settings){
	var o = $.extend({
		expanded: ''
	},settings);
	
	return $(this).each(function(){
		if( !$(this).parents('.tree').length ){
		//save reference to tree UL
		var tree = $(this);
		
		//add the role and default state attributes
		if( !$('body').is('[role]') ){ $('body').attr('role','application'); }
		//add role and class of tree
		tree.attr({'role': 'tree'}).addClass('tree');
		//set first node's tabindex to 0
		tree.find('i:eq(0)').attr('tabindex','0');
		//set all others to -1
		tree.find('i:gt(0)').attr('tabindex','-1');
		//add group role and tree-group-collapsed class to all ul children
		//tree.find('ul').attr('role','group').addClass('tree-group-collapsed'); //ecmoban模板堂 --zhuo 注释
		//add treeitem role to all li children
		tree.find('li').attr('role','treeitem');
		//find tree group parents
		tree.find('li:has(ul)')
				//ecmoban模板堂 --zhuo start 注释
				/*.attr('aria-expanded', 'false')
				.find('>i')
				.addClass('tree-parent tree-parent-collapsed');*/
				//ecmoban模板堂 --zhuo end 注释
	
		//expanded at load		
		tree
			.find(o.expanded)
			.attr('aria-expanded', 'true')
				.find('>i')
				.removeClass('tree-parent-collapsed')
				.nextAll('ul')
				.removeClass('tree-group-collapsed');
					
		
		//bind the custom events
		tree
			//expand a tree node
			.bind('expand',function(event){
				var target = $(event.target) || tree.find('i[tabindex=0]');
				target.removeClass('tree-parent-collapsed');
				target.nextAll('ul').hide().removeClass('tree-group-collapsed').slideDown(150, function(){
					$(this).removeAttr('style');
					target.parent().attr('aria-expanded', 'true');
				});
			})
			//collapse a tree node
			.bind('collapse',function(event){
				var target = $(event.target) || tree.find('i[tabindex=0]');
				target.addClass('tree-parent-collapsed');
				target.nextAll('ul').slideUp(150, function(){
					target.parent().attr('aria-expanded', 'false');
					$(this).addClass('tree-group-collapsed').removeAttr('style');
				});
			})
			.bind('toggle',function(event){
				var target = $(event.target) || tree.find('i[tabindex=0]');
				//check if target parent LI is collapsed
				if( target.parent().is('[aria-expanded=false]') ){ 
					//call expand function on the target
					target.trigger('expand');
				}
				//otherwise, parent must be expanded
				else{ 
					//collapse the target
					target.trigger('collapse');
				}
			})
			//shift focus down one item		
			.bind('traverseDown',function(event){
				var target = $(event.target) || tree.find('i[tabindex=0]');
				var targetLi = target.parent();
				if(targetLi.is('[aria-expanded=true]')){
					target.nextAll('ul').find('i').eq(0).focus();
				}
				else if(targetLi.nextAll('ul').length) {
					targetLi.nextAll('ul').find('i').eq(0).focus();
				}	
				else {				
					targetLi.parents('li').nextAll('ul').find('i').eq(0).focus();
				}
			})
			//shift focus up one item
			.bind('traverseUp',function(event){
				var target = $(event.target) || tree.find('i[tabindex=0]');
				var targetLi = target.parent();
				if(targetLi.prev().length){ 
					if( targetLi.prev().is('[aria-expanded=true]') ){
						targetLi.prev().find('li:visible:last i').eq(0).focus();
					}
					else{
						targetLi.prev().find('i').eq(0).focus();
					}
				}
				else { 				
					targetLi.parents('li:eq(0)').find('i').eq(0).focus();
				}
			});

		
		//and now for the native events
		tree	
			.focus(function(event){
				//deactivate previously active tree node, if one exists
				tree.find('[tabindex=0]').attr('tabindex','-1').removeClass('tree-item-active');
				//assign 0 tabindex to focused item
				$(event.target).attr('tabindex','0').addClass('tree-item-active');
			})
			.click(function(event){
				//save reference to event target
				var target = $(event.target);
				//check if target is a tree node
				if( target.is('i.tree-parent') ){
					target.trigger('toggle');
					target.eq(0).focus();
					//return click event false because it's a tree node (folder)
					return false;
				}
			})
			.keydown(function(event){	
					var target = tree.find('i[tabindex=0]');
					//check for arrow keys
					if(event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40){
						//if key is left arrow 
						if(event.keyCode == 37){ 
							//if list is expanded
							if(target.parent().is('[aria-expanded=true]')){
								target.trigger('collapse');
							}
							//try traversing to parent
							else {
								target.parents('li:eq(1)').find('i').eq(0).focus();
							}	
						}						
						//if key is right arrow
						if(event.keyCode == 39){ 
							//if list is collapsed
							if(target.parent().is('[aria-expanded=false]')){
								target.trigger('expand');
							}
							//try traversing to child
							else {
								target.parents('li:eq(0)').find('li i').eq(0).focus();
							}
						}
						//if key is up arrow
						if(event.keyCode == 38){ 
							target.trigger('traverseUp');
						}
						//if key is down arrow
						if(event.keyCode == 40){ 
							target.trigger('traverseDown');
						}
						//return any of these keycodes false
						return false;
					}	
					//check if enter or space was pressed on a tree node
					else if((event.keyCode == 13 || event.keyCode == 32) && target.is('i.tree-parent')){
							target.trigger('toggle');
							//return click event false because it's a tree node (folder)
							return false;
					}
			});
		}
	});
};	

$(function(){
	
	
 
    //鼠标经过弹出图片信息
    $(".item").hover(
        function() {
            $(this).find(".goods-info").animate({"top": "180px"}, 400, "swing");
        },function() {
            $(this).find(".goods-info").stop(true,false).animate({"top": "230px"}, 400, "swing");
        }
    );
   
    
   
});

(function($, wp, wps, window, undefined) {
	'$:nomunge';
	var $w = $(window),
	waypoints = [],
	oldScroll = -99999,
	didScroll = false,
	didResize = false,
	eventName = 'waypoint.reached',
	methods = {
		init: function(f, options) {
			this.each(function() {
				var $this = $(this),
				ndx = waypointIndex($this),
				base = ndx < 0 ? $.fn[wp].defaults: waypoints[ndx].options,
				opts = $.extend({},
				base, options);
				opts.offset = opts.offset === "bottom-in-view" ?
				function() {
					return $[wps]('viewportHeight') - $(this).outerHeight();
				}: opts.offset;
				if (ndx < 0) {
					waypoints.push({
						element: $this,
						offset: $this.offset().top,
						options: opts
					});
				}
				 else {
					waypoints[ndx].options = opts;
				}
				f && $this.bind(eventName, f);
			});
			$[wps]('refresh');
			return this;
		},
		remove: function() {
			return this.each(function() {
				var ndx = waypointIndex($(this));
				if (ndx >= 0) {
					waypoints.splice(ndx, 1);
				}
			});
		},
		destroy: function() {
			return this.unbind(eventName)[wp]('remove');
		}
	};
	function waypointIndex(el) {
		var i = waypoints.length - 1;
		while (i >= 0 && waypoints[i].element[0] !== el[0]) {
			i -= 1;
		}
		return i;
	}
	function triggerWaypoint(way, dir) {
		way.element.trigger(eventName, dir)
		 if (way.options.triggerOnce) {
			way.element[wp]('destroy');
		}
	}
	function doScroll() {
		var newScroll = $w.scrollTop(),
		isDown = newScroll > oldScroll,
		pointsHit = $.grep(waypoints,
		function(el, i) {
			return isDown ? (el.offset > oldScroll && el.offset <= newScroll) : (el.offset <= oldScroll && el.offset > newScroll);
		});
		if (!oldScroll || !newScroll) {
			$[wps]('refresh');
		}
		oldScroll = newScroll;
		if (!pointsHit.length) return;
		if ($[wps].settings.continuous) {
			$.each(isDown ? pointsHit: pointsHit.reverse(),
			function(i, point) {
				triggerWaypoint(point, [isDown ? 'down': 'up']);
			});
		}
		 else {
			triggerWaypoint(pointsHit[isDown ? pointsHit.length - 1: 0], [isDown ? 'down': 'up']);
		}
	}
	$.fn[wp] = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}
		 else if (typeof method === "function" || !method) {
			return methods.init.apply(this, arguments);
		}
		 else if (typeof method === "object") {
			return methods.init.apply(this, [null, method]);
		}
		 else {
			$.error('Method ' + method + ' does not exist on jQuery' + wp);
		}
	};
	$.fn[wp].defaults = {
		offset: 0,
		triggerOnce: false
	};
	var jQMethods = {
		refresh: function() {
			$.each(waypoints,
			function(i, o) {
				var adjustment = 0,
				oldOffset = o.offset;
				if (typeof o.options.offset === "function") {
					adjustment = o.options.offset.apply(o.element);
				}
				 else if (typeof o.options.offset === "string") {
					var amount = parseFloat(o.options.offset),
					adjustment = o.options.offset.indexOf("%") ? Math.ceil($[wps]('viewportHeight') * (amount / 100)) : amount;
				}
				 else {
					adjustment = o.options.offset;
				}
				o.offset = o.element.offset().top - adjustment;
				if (oldScroll > oldOffset && oldScroll <= o.offset) {
					triggerWaypoint(o, ['up']);
				}
				 else if (oldScroll < oldOffset && oldScroll >= o.offset) {
					triggerWaypoint(o, ['down']);
				}
			});
			waypoints.sort(function(a, b) {
				return a.offset - b.offset;
			});
		},
		viewportHeight: function() {
			return (window.innerHeight ? window.innerHeight: $w.height());
		},
		aggregate: function() {
			var points = $();
			$.each(waypoints,
			function(i, e) {
				points = points.add(e.element);
			});
			return points;
		}
	};
	$[wps] = function(method) {
		if (jQMethods[method]) {
			return jQMethods[method].apply(this);
		}
		 else {
			return jQMethods["aggregate"]();
		}
	};
	$[wps].settings = {
		continuous: true,
		resizeThrottle: 200,
		scrollThrottle: 100
	};
	$w.scroll(function() {
		if (!didScroll) {
			didScroll = true;
			window.setTimeout(function() {
				doScroll();
				didScroll = false;
			},
			$[wps].settings.scrollThrottle);
		}
	}).resize(function() {
		if (!didResize) {
			didResize = true;
			window.setTimeout(function() {
				$[wps]('refresh');
				didResize = false;
			},
			$[wps].settings.resizeThrottle);
		}
	}).load(function() {
		$[wps]('refresh');
		doScroll();
	});
})(jQuery, 'waypoint', 'waypoints', this);



(function($){
	$.fn.membershipCard = function(options){};
})(jQuery);
	
	
