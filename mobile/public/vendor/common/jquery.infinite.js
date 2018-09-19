/** 
 @Name：ECTouch infinite js v0.1
 $Author：carson
 $Site：http://www.ectouch.cn
 @Date：2016-01-16
 @License：MIT
 $('.aa').infinite({url:'', params:'a=b&c=d'})
 */
;(function ($) {
    "use strict";
    var target = null;
    var locked = false;
    var opts = {
        "url": '',
        "pager": '1',
        "size": '10',
        "params": '',
        "template": '',
        "type": 'post',
        "format": 'json',
        "offset": '100',
        "cache": false,
        "cache_prefix": 'pre_'
    }
    var totalPage = 0;

    var methods = {
        // 初始化
        init: function (options) {
            target = $(this);
            if (options) {
                $.extend(opts, options);
            }
            methods.getData();
            $(window).scroll(methods.checkScroll);

            var method = {};
            //获取当前页码
            return method.getPager = function () {
                return opts.pager;
            },
            //刷新当前页
            method.reload = function () {
                methods.getData();
            },
            //重新加载
            method.onload = function (options) {
                if (options) {
                    opts.params = options;
                }
                opts.pager = 1;
                methods.getData();
            },
            //获取总页数
            method.getTotalPage = function () {
                return totalPage;
            },
            method
        },
        // 请求参数
        getParam: function () {
            var param = "page=" + opts.pager + "&size=" + opts.size;
            param = param + "&" + opts.params;
            return param;
        },
        // 请求数据
        getData: function () {
            // 读取缓存
            var maxPage = window.sessionStorage.getItem(opts.cache_prefix + 'max_page');
            if(opts.cache && typeof(maxPage) != 'object' && typeof(maxPage) != 'undefined' && opts.pager <= maxPage){
                for(var i = 1; i <= maxPage; i++){
                    var res = window.sessionStorage.getItem(opts.cache_prefix + i);
                    if(typeof(res) != 'object' && typeof(res) != 'undefined'){
                        var resData = JSON.parse(res);
                        template.config('openTag', '<%');
                        template.config('closeTag', '%>');
                        var html = template(opts.template, resData);
                        if (i > 1) {
                            target.append(html);
                        } else {
                            target.html(html);
                        }
                    }
                }
                opts.pager = maxPage;
                // 定位滚动条
                var scrollIndex = window.sessionStorage.getItem(opts.cache_prefix + 'scroll_to_index');
                // alert(scrollIndex);
                window.scrollTo(0, scrollIndex - 100);
                return false;
            }

            locked = true;
            var depr = (opts.url.indexOf('?') > 0) ? '&' : '?';
            var url = opts.url + depr + "ts=" + Math.random();
            $.ajax({
                url: url,
                type: opts.type,
                dataType: opts.format,
                data: methods.getParam(),
                async: false,
                success: function (data) {
                    totalPage = data.totalPage == 'undefind' ? 0 : data.totalPage;
                    // 存储缓存
                    var cache_id = opts.cache_prefix + opts.pager;
                    window.sessionStorage.setItem(cache_id, JSON.stringify(data));
                    window.sessionStorage.setItem(opts.cache_prefix + 'max_page', opts.pager);
                    // 视图渲染
                    template.config('openTag', '<%');
                    template.config('closeTag', '%>');
                    var html = template(opts.template, data);
                    if (opts.pager > 1) {
                        target.append(html);
                    } else {
                        target.html(html);
                    }
                    opts.pager++;
                    locked = false;
                }
            });
            if (opts.template == "j-product") {
                /*店铺信息商品滚动*/
                var swiper = new Swiper('.j-g-s-p-con', {
                    scrollbarHide: true,
                    slidesPerView: 'auto',
                    centeredSlides: false,
                    grabCursor: true
                });
            }
        },
        // 监听滚动
        checkScroll: function () {
            var scrollTop = $(window).scrollTop() + parseInt(opts.offset);
            var documentHeight = $(document).height() - $(window).height();
            window.sessionStorage.setItem(opts.cache_prefix + 'scroll_to_index', scrollTop);
            if (scrollTop >= documentHeight && opts.pager <= totalPage && locked == false) {
                methods.getData();
            }
        },
    }

    // $.fn.infinite = function(options) {
    // return init(options, $(this));
    // }

    $.fn.infinite = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method == 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist!');
        }
    }

})(jQuery)