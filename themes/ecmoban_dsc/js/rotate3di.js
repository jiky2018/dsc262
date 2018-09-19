(function ($) {
    var calcRotate3Di = {
        direction: function (now) {return (now < 0 ? -1 : 1);},
        degrees: function (now) {return (Math.floor(Math.abs(now))) % 360;},
        scale: function (degrees) {return (1 - (degrees % 180) / 90)
                                            * (degrees >= 180 ? -1 : 1);}
    }
    $.fx.step.rotate3Di = function (fx) {
        direction = calcRotate3Di.direction(fx.now);
        degrees = calcRotate3Di.degrees(fx.now);
        scale = calcRotate3Di.scale(degrees);

        if (fx.options && typeof fx.options['sideChange'] != 'undefined') {
            if (fx.options['sideChange']) {
                var prevScale = $(fx.elem).data('rotate3Di.prevScale');
                if (scale * prevScale <= 0) {
                    fx.options['sideChange'].call(
                        fx.elem,
                        (scale > 0 || prevScale < 0)
                    );
                    $(fx.elem).data(
                        'rotate3Di.prevScale',
                        $(fx.elem).data('rotate3Di.prevScale') * -1
                    );
                }
            }
            scale = Math.abs(scale);
        }
        $(fx.elem).data('rotate3Di.degrees', direction * degrees);
        $(fx.elem).css(
            'transform',
            'skew(0deg, ' + direction * degrees + 'deg)'
                + ' scale(' + scale + ', 1)'
        );
    }
    
    var proxied = $.fx.prototype.cur;
    $.fx.prototype.cur = function () {
        if (this.prop == 'rotate3Di') {
            var style = $(this.elem).css('transform');
            if (style) {
                var m = style.match(/, (-?[0-9]+)deg\)/);
                if (m && m[1]) {
                    return parseInt(m[1]);
                } else {
                    return 0;
                }
            }
        }
        
        return proxied.apply(this, arguments);
    }
	
    $.fn.rotate3Di = function (degrees, duration, options) {
        if (typeof duration == 'undefined') {
            duration = 0;
        }
        
        if (typeof options == 'object') {
            $.extend(options, {duration: duration});
        } else {
            options = {duration: duration};
        }
        
        if (degrees == 'toggle') {
            if ($(this).data('rotate3Di.flipped')) {
                degrees = 'unflip';
                
            } else {
                degrees = 'flip';
            }
        }
        
        if (degrees == 'flip') {
            $(this).data('rotate3Di.flipped', true);

            var direction = -1;
            if (
                typeof options == 'object'
                && options['direction']
                && options['direction'] == 'clockwise'
            ) {
                direction = 1;
            }
            
            degrees = direction * 180;
            
        } else if (degrees == 'unflip') {
            $(this).data('rotate3Di.flipped', false);
            
            degrees = 0;
        }
        
        var d = $(this).data('rotate3Di.degrees') || 0;
        $(this).data(
            'rotate3Di.prevScale',
            calcRotate3Di.scale(calcRotate3Di.degrees(d))
        );
        $(this).animate({rotate3Di: degrees}, options);
    }
})(jQuery);