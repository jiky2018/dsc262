// var str = '<style type="text/css">.suspension { position: fixed; z-index: 155; right: 10px; bottom: 10px; width: 80px; height: 204px; }.suspension-box { position: relative; float: right; }.suspension-box a.a { display: block; width: 50px; height: 50px; background-color: #B5B5B5; margin-bottom: 1px; cursor: pointer; }.suspension-box a.back_top { background-color: #797979; }.suspension-box a.qq:hover,.suspension-box a.tel:hover,.suspension-box a.code:hover { opacity: 0.8; filter: alpha(opacity=80); }.suspension-box a.back_top:hover { background-color: #FC7878; }.suspension-box a i { display: block; float: left; width: 25px; margin-left: 15px; margin-top: 15px; background-image: url(http://www.ecmoban.com/content/themes/ecmoban2014/images/suspension-bg.png); background-repeat: no-repeat; text-indent: -9999px; }.suspension-box a.qq i { background-position: 0 0; }.suspension-box a.tel i { background-position: -25px 0; }.suspension-box a.code i { background-position: 0 -25px; }.suspension-box a.back_top i { background-position: -28px -25px; }.suspension-box div.code { position: absolute; left: -200px; *left: -207px; top: 0; display: none; width: 178px; height: 198px; padding-left: 10px; padding-top: 10px; background-color: #fff; border: 1px solid #D9D9D9; }.suspension-box div.code p { width: 155px; margin-top: 5px; text-align: center; }.suspension-box div.code i { width: 8px; height: 15px; position: absolute; top: 118px; right: -8px; background-image: url(http://www.ecmoban.com/content/themes/ecmoban2014/images/trangle-right-suspension.png); background-repeat: no-repeat; }.suspension-box div.tencent { position: absolute; left: -187px; top: 0px; width: 177px; height: 85px; display:none; background-color: #fff; border: 1px solid #D7D7D7; }.suspension-box div.tencent p.tencent-p { width: 75px; float: right; margin-right: 18px; margin-top: 18px; font-size: 16px; color: #333; }.suspension-box div.tencent i.tencent-i { width: 8px; height: 15px; position: absolute; top: 10px; right: -8px; text-indent: -9999px; background-image: url(http://www.ecmoban.com/content/themes/ecmoban2014/images/trangle-right-suspension.png); background-repeat: no-repeat; }.suspension-box div.tencent b.tencent-b { position: absolute; left: 10px; top: 10px; width: 62px; height: 64px; background-image: url(http://www.ecmoban.com/content/themes/ecmoban2014/images/circle-tencent.png); background-repeat: no-repeat; }.suspension-box div.tencent span.tencent-span { position: absolute; left: 8px; top: 7px; border-radius: 22px; width: 45px; height: 48px; text-align: center; z-index: 99; overflow: hidden; }.suspension-box div.tencent .tencent-qq {position: absolute; left: 3px;top:0px; height: 45px; width: 39px; }.suspension-box div.tel { position: absolute; left: -187px; top: 51px; display: none; width: 177px; height: 85px; background-color: #fff; border: 1px solid #D7D7D7; }.suspension-box div.tel i { width: 8px; height: 15px; position: absolute; top: 20px; right: -8px; background-image: url(http://www.ecmoban.com/content/themes/ecmoban2014/images/trangle-right-suspension.png); background-repeat: no-repeat; }.suspension-box div.tel strong { display: block; margin-left: 17px; margin-top: 16px; font-size: 16px; font-weight: normal; }.suspension-box div.tel p { margin-left: 17px; font-size: 23px; font-family: arial; color: #FD696D; }.icon { margin-top:-5px; }.nav a.cart-title-name { color: #000; text-decoration: none; line-height: normal; }.nav a.cart-title-name:hover { color: #ef353d; text-decoration: underline; line-height: normal; }.nav a font { padding: 8px 15px; background:#fd7878; border-radius: 5px; }.nav a font:hover { padding: 8px 15px; background:#ea5050; border-radius: 5px; }</style><div class="suspension"><div class="suspension-box"><a href="http://wpa.b.qq.com/cgi/wpa.php?ln=1&amp;key=XzgwMDAwNzE2N18zNzUzN184MDAwMDcxNjdfMl8;" target="_blank"  class="qq j-icon a"><i>qq</i></a><a class="a tel j-icon"><i>tel</i></a><a class="code j-icon a"><i>code</i></a><a class="back_top j-back-top a"><i>back_top</i></a><div class="tencent j-tencent j-box"><a href="http://wpa.b.qq.com/cgi/wpa.php?ln=1&amp;key=XzgwMDAwNzE2N18zNzUzN184MDAwMDcxNjdfMl8" target="_blank"><i class="tencent-i">tencent</i><b class="tencent-b"><span class="tencent-span"><img class="tencent-qq" src="http://www.ecmoban.com/content/themes/ecmoban2014/images/qq-tencent.png" alt=""></span></b><p class="tencent-p">在线客服<br>点击交谈</p></a></div><div class="tel j-box"><strong>服务热线：</strong><p>4000-021-758</p><i></i></div><div class="code j-box"><img src="http://www.ecmoban.com/content/themes/ecmoban2014/images/suspension-code.png" alt=""><p>扫一扫，微信关注模板堂</p><i></i></div></div></div>';

var qq = $("input[name='seller_kf_qq']").val();
var tel = $("input[name='seller_kf_tel']").val();
var IM = $("input[name='seller_kf_IM']").val();// bylu
var site_domain = $("input[name='seller_kf_IM']").attr('rev');// bylu

var str = '<style type="text/css">';
    str += '.suspension { position: fixed; z-index: 99999; right: 10px; bottom: 10px; width: 80px; height: 204px; }';
    str += '.suspension-box { position: relative; float: right; }';
    str += '.suspension-box a.a { display: block; width: 50px; height: 50px; background-color: #000; border-bottom: 1px #686767 solid; cursor: pointer; }';
    str += '.suspension-box a.back_top { background-color: #000; border-bottom:0;}';
    str += '.suspension-box a.qq:hover,.suspension-box a.tel:hover,.suspension-box a.code:hover { opacity: 0.8; filter: alpha(opacity=80); }';
    str += '.suspension-box a.back_top:hover { background-color: #ec5051; }';
    str += '.suspension-box a i { display: block; float: left; width: 25px; margin-left: 14px; margin-top: 15px; background-image: url(http://www.ecmoban.com/content/themes/ecmoban2014/images/suspension-bg.png); background-repeat: no-repeat; text-indent: -9999px; }';
    str += '.suspension-box a.qq i { background-position: 0 0; }';
    str += '.suspension-box a.tel i { background-position: -25px 0; }';
    str += '.suspension-box a.code i { background-position: 0 -25px; }';
    str += '.suspension-box a.back_top i { background-position: -28px -25px; }';
    str += '.suspension-box div.code { position: absolute; left: -200px; *left: -207px; top: 0; display: none; width: 178px; height: 198px; padding-left: 10px; padding-top: 10px; background-color: #fff; border: 1px solid #D9D9D9; }';
    str += '.suspension-box div.code p { width: 155px; margin-top: 5px; text-align: center; }';
    str += '.suspension-box div.code i { width: 8px; height: 15px; position: absolute; top: 118px; right: -8px; background-image: url(http://www.ecmoban.com/content/themes/ecmoban2014/images/trangle-right-suspension.png); background-repeat: no-repeat; }';
    str += '.suspension-box div.tencent { position: absolute; left: -187px; top: 0px; width: 177px; height: 85px; display:none; background-color: #fff; border: 1px solid #D7D7D7; }';
    str += '.suspension-box div.tencent p.tencent-p { width: 75px; float: right; margin-right: 18px; margin-top: 24px; font-size: 14px; color: #333;font-family: microsoft yahei;}';
    str += '.suspension-box div.tencent i.tencent-i { width: 8px; height: 15px; position: absolute; top: 10px; right: -8px; text-indent: -9999px; background-image: url(http://www.ecmoban.com/content/themes/ecmoban2014/images/trangle-right-suspension.png); background-repeat: no-repeat; }';
    str += '.suspension-box div.tencent b.tencent-b { position: absolute; left: 10px; top: 10px; width: 62px; height: 64px; background-image: url(http://www.ecmoban.com/content/themes/ecmoban2014/images/circle-tencent.png); background-repeat: no-repeat; }';
    str += '.suspension-box div.tencent span.tencent-span { position: absolute; left: 8px; top: 7px; border-radius: 22px; width: 45px; height: 48px; text-align: center; z-index: 99; overflow: hidden; }';
    str += '.suspension-box div.tencent .tencent-qq {position: absolute; left: 3px;top:0px; height: 45px; width: 39px; }';
    str += '.suspension-box div.tel { position: absolute; left: -187px; top: 51px; display: none; width: 177px; height: 85px; background-color: #fff; border: 1px solid #D7D7D7; }';
    str += '.suspension-box div.tel i { width: 8px; height: 15px; position: absolute; top: 20px; right: -8px; background-image: url(http://www.ecmoban.com/content/themes/ecmoban2014/images/trangle-right-suspension.png); background-repeat: no-repeat; }';
    str += '.suspension-box div.tel strong { display: block; margin-left: 17px; margin-top: 16px; font-size: 16px; font-weight: normal; }';
    str += '.suspension-box div.tel p { margin-left: 17px; font-size: 23px; font-family: arial; color: #FD696D; }';
    str += '.icon {}.nav a.cart-title-name { color: #000; text-decoration: none; line-height: normal; }';
    str += '.nav a.cart-title-name:hover { color: #ef353d; text-decoration: underline; line-height: normal; }';
    str += '.nav a font { padding: 8px 15px; background:#fd7878; border-radius: 5px; }';
    str += '.nav a font:hover { padding: 8px 15px; background:#ea5050; border-radius: 5px; }';
    str += '.suspension-mobile { position: fixed; z-index: 55; right: 10px; bottom: 30px; width: 50px; height: 50px;}';
    str += '.suspension-mobile .suspension-box { display: none; }';
    str += '.suspension-mobile .suspension-box a.a { width: 70px; height: 70px; }';
    str += '.suspension-mobile .suspension-box a i { margin-left: 24px; margin-top: 25px; }';
    str += '.suspension-mobile .suspension-box a.back_top { border-radius: 35px;  background: #000; opacity: 0.8}';
    str += '</style>';

    str += '<div class="suspension">';
    str += '<div class="suspension-box">';

    // bylu
    if(IM == 1){
        str += '<a id="IM" class="qq j-icon a" style="color: #fff;" onclick="openWin(this)" href="javascript:;"  class="seller-btn"><i class="icon">'+Online_customer_service+'</i></a>';

    }else{
        str += '<a href="http://wpa.qq.com/msgrd?v=3&uin=' + qq + '&site=qq&menu=yes" class="qq j-icon a"><i>qq</i></a>';

    }
    str += '<a class="a tel j-icon"><i>tel</i></a>';
    str += '<a class="code j-icon a"><i>code</i></a>';
    str += '<a class="back_top j-back-top a"><i>back_top</i></a>';

    if(IM == 1) {
        // bylu
        str += '<div class="tencent j-tencent j-box">';
        str += '<a id="IM" onclick="openWin(this)" href="javascript:;">';
        str += '<i class="tencent-i">tencent</i>';
        str += '<b class="tencent-b"><span class="tencent-span"><img class="tencent-qq" src="http://dsc-kf.oss-cn-shanghai.aliyuncs.com/dsc_kf/p16812444.jpg" alt=""></span></b>';
        str += '<p class="tencent-p">'+Online_customer_service+'<br>'+Click_on_the_chat+'</p>';
        str += '</a>';
        str += '</div>';
    }else{
        str += '<div class="tencent j-tencent j-box">';
        str += '<a href="http://wpa.qq.com/msgrd?v=3&uin=' + qq + '&site=qq&menu=yes" target="_blank">';
        str += '<i class="tencent-i">tencent</i>';
        str += '<b class="tencent-b"><span class="tencent-span"><img class="tencent-qq" src="http://www.ecmoban.com/content/themes/ecmoban2014/images/qq-tencent.png" alt=""></span></b>';
        str += '<p class="tencent-p">'+Online_customer_service+'<br>'+Click_on_the_chat+'</p>';
        str += '</a>';
        str += '</div>';
    }
    str += '<div class="tel j-box">';
    str += '<strong>'+Service_hotline+'</strong>';
    str += '<p>' + tel + '</p><i></i>';
    str += '</div>';

    str += '<div class="code j-box">';
    str += seller_qrcode; //by wu
    str += scan_to_mobile;
    str += '</div>';

    str += '</div>';
    str += '</div>';


    str += '<div class="suspension-mobile">';
    str += '<div class="suspension-box">';

    str += '<a class="back_top j-back-top a"><i>back_top</i></a>';

    str += '</div>';
    str += '</div>';



//加载字符串
$("body").append(str);
// 效果
$(".suspension").find(".j-icon").mouseover(
    function(){
        $(".suspension").find(".j-box").eq($(this).index()).show().siblings(".j-box").hide();
    }
);
//判断ie浏览器
    
    if(navigator.appVersion.match(/11./i) == '11.' || navigator.appVersion.match(/10./i) == '10.') {
        $(".suspension").css("right","30px");
    }

$(".suspension .j-back-top").hide();

$(window).scroll(function(){
    if($(window).scrollTop() > 100){
        $(".suspension .j-back-top").stop().show()
        $(".suspension-mobile .suspension-box").stop().show()
    }else {
        $(".suspension .j-back-top").stop().hide()
        $(".suspension-mobile .suspension-box").stop().hide()
    }
});
$(".j-tencent").hover(function(){
            $(".tencent-qq").stop(true,false).animate({width:"51",height:"59",left:"-3"});
        },function(){
            $(".tencent-qq").stop(true,false).animate({width:"39",height:"45",left:"3"});
        });
// 效果
$(".suspension").mouseleave(function(event){
    event.preventDefault();
    $(".suspension").find(".j-box").hide();
});

$(".j-back-top").click(function(){
    $("html,body").animate({scrollTop:0},500);
});


<!--@author-bylu start-->
    function openWin(obj) {
        var ru_id = $('input[name=seller_kf_IM]').attr('ru_id');
        var url= site_domain + 'online.php?act=service&ru_id='+ru_id                   //转向网页的地址;
        var name='webcall';                         //网页名称，可为空;
        var iWidth=700;                          //弹出窗口的宽度;
        var iHeight=500;                         //弹出窗口的高度;
        //获得窗口的垂直位置
        var iTop = (window.screen.availHeight - 30 - iHeight) / 2;
        //获得窗口的水平位置
        var iLeft = (window.screen.availWidth - 10 - iWidth) / 2;
        window.open(url, name, 'height=' + iHeight + ',,innerHeight=' + iHeight + ',width=' + iWidth + ',innerWidth=' + iWidth + ',top=' + iTop + ',left=' + iLeft + ',status=no,toolbar=no,menubar=no,location=no,resizable=no,scrollbars=0,titlebar=no');
    }
<!--@author-bylu end-->

