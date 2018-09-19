function sendSms(){
	
	var str = '';
	if(document.getElementById('sms_value')){
		var sms_value = document.getElementById('sms_value').value;
		str = "&sms_value=" + sms_value;
	}
	
	var frm = $("form[name='formUser']");
    var mobile = document.getElementById('mobile_phone').value;
    var seccode = document.getElementById('seccode').value;
    var flag = document.getElementById('flag').value;
	var username=frm.find("input[name='username']").val();
	
    Ajax.call('sms.php?act=send&flag='+flag, 'mobile=' + mobile + '&seccode=' + seccode + '&username=' + username + str, sendSmsResponse, 'POST', 'JSON');
}
function sendSmsResponse(result){
	
  if(result.code==2){
    RemainTime();
	
	// 安全中心 start qin
	$('#mobile_code').removeAttr("disabled");	
	// 安全中心 end
	
    $("#seccode").val(result.sms_security_code);
    if(result.flag == 'register')
    {
      $("#phone_notice").removeClass("error").addClass("succeed");
      $("#phone_notice").html("<i></i>");
    }
    
  }else{
    $("#phone_notice").show();
    if(result.msg){
      if(result.error == 1){
        $("#username_notice_1").html("<i></i>"+result.msg);
      }else{
        $("#phone_notice").removeClass().addClass("error");
        $("#phone_notice").html("<i></i>"+result.msg);
      }    
    }else{
      $("#phone_notice").removeClass().addClass("error");
      $("#phone_notice").html("<i></i>"+"手机验证码发送失败");
    }
  }
  // 安全中心 start qin
    
  // 安全中心 end
}
function register2(){
    var mobile = document.getElementById('mobile_phone').value;
    if (mobile_phone != ''){
        var mobile_code = document.getElementById("mobile_code").value;
		var seccode = document.getElementById('seccode').value;
        if(mobile_code.length == ''){
            alert('请填写手机验证码');
            return false;
        }
        var result = Ajax.call('sms/sms.php?act=check', 'mobile=' + mobile + '&mobile_code=' + mobile_code + '&seccode=' + seccode, null, 'POST', 'JSON', false);	
        if (result.code==2){
            return register();
        }else{
            alert(result.msg);
            return false;
        }
    }
    return register();			
}

var iTime = 59;
var Account;
function RemainTime(){
  document.getElementById('zphone').disabled = true;
  var iSecond,sSecond="",sTime="";
  if (iTime >= 0){
    iSecond = parseInt(iTime%60);
    if (iSecond >= 0){
      sSecond = iSecond + "秒";
    }
    sTime=sSecond;
    if(iTime==0){
      clearTimeout(Account);
      sTime='获取手机验证码';
      iTime = 59;
      document.getElementById('zphone').disabled = false;
    }else{
      Account = setTimeout("RemainTime()",1000);
      iTime=iTime-1;
    }
  }else{
    sTime='没有倒计时';
  }
  document.getElementById('zphone').innerHTML = sTime;
}