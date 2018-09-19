jQuery.notLogin = function(actUrl,backUrl){
	if(backUrl != null){
		if(backUrl.indexOf('&amp;') > -1){
			var backUrl = backUrl.replace("&amp;","|");
		}else if(backUrl.indexOf('&') > -1){
			var backUrl = backUrl.replace("&","|");
		}
	}
	Ajax.call(actUrl,'back_act='+ backUrl, function(data){
		pb({
			id:"loginDialogBody",
			title:json_languages.not_login,
			width:380,
			height:430,
			content:data.content, 	//调取内容
			drag:false,
			foot:false
		});
	}, 'POST','JSON');
}