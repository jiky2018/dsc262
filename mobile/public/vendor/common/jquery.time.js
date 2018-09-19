         /*倒计时*/
        $.extend($.fn,{
                fnTimeCountDown:function(d){
                    this.each(function(){
                        var $this = $(this);
                        var o = {
                            sec: $this.find(".sec"),
                            mini: $this.find(".mini"),
                            hour: $this.find(".hour"),
                            day: $this.find(".day")
                        };
                        var block=0;
                        ($(this).attr('block') == 1) ? block = 1 : block = 0;
                        var f = {
                            haomiao: function(n){
                                if(n < 10)return "00" + n.toString();
                                if(n < 100)return "0" + n.toString();
                                return n.toString();
                            },
                            zero: function(n){
                                var _n = parseInt(n, 10);//解析字符串,返回整数
                                if(_n > 0){
                                    if(_n <= 9){
                                        _n = "0" + _n
                                    }
                                    return String(_n);
                                }else{
                                    return "00";
                                }
                            },
                            dv: function(){
                                d = d || Date.UTC(2050, 0, 1); //如果未定义时间，则我们设定倒计时日期是2050年1月1日
                                var _d = $this.data("end") || d;
                                var now = new Date(),
                                        endDate = new Date(_d.replace(/-/g, "/"));
                                //现在将来秒差值
                                //alert(future.getTimezoneOffset());
                                var dur = (endDate - now.getTime()) / 1000 , mss = endDate - now.getTime() ,pms = {
                                    sec: "00",
                                    mini: "00",
                                    hour: "00",
                                    day: "00",
                                };
                                if(mss > 0){
                                    pms.sec = f.zero(dur % 60);
                                    pms.mini = Math.floor((dur / 60)) > 0? f.zero(Math.floor((dur / 60)) % 60) : "00";
                                    pms.hour = Math.floor((dur / 3600)) > 0? f.zero(Math.floor((dur / 3600)) % 24) : "00";
                                    pms.day = Math.floor((dur / 86400)) > 0? f.zero(Math.floor((dur / 86400))) : "00";

                                }else{
                                    pms.day=pms.hour=pms.mini=pms.sec="00";
                                    if(block != 1){
                                        $(".btn-submit").remove();
                                        $(".btn-disab").css('display','block');
                                    }
                                }
                                return pms;
                            },
                            ui: function(){
                                if(o.sec){
                                    o.sec.html(f.dv().sec);
                                }
                                if(o.mini){
                                    o.mini.html(f.dv().mini);
                                }
                                if(o.hour){
                                    o.hour.html(f.dv().hour);
                                }
                                if(o.day){
                                    o.day.html(f.dv().day);
                                }
                                setTimeout(f.ui, 1);
                            }
                        };
                        f.ui();
                    });
                }
            });