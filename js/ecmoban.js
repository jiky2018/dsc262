var ecmoban = {
    error : "",
    uploads : function(opts){
        var o = $.extend({
            buttonId: "",
            fileName: "",
            fileShow: "",
            fileShowBox: "",
            fileWidth: 0, //小图宽度
            fileHeight: 0, //小图高度，0为自动拉伸
            fileSize: "1MB", //上传文件的限制大小
            skinPath: '', //路径
            imageUrl: '', //图片地址的路径
            userId: '', //用户id
            fileObjName: 'Filedata',
            exts: "*.gif;*.jpg;*.png", //定义允许上传的文件后缀。
            multi: false, //设置值为false时，一次只能选中一个文件。
            type: 1, //1 标示图片上传 2 其他的文档上传
            timeout: 10, //表示uploadify的成功等待时间
            btnText: uploads_file, //定义显示在默认按钮上的文本。
            btnImage: "", //按钮图片
            btnWidth: 88, //按钮宽度
            btnHeight: 30 //按钮高度
        }, opts || {});
        
        $("#" + o.buttonId).uploadify({
            uploader : o.uploader,
            swf: o.skinPath + './js/uploadify.swf',
            width: o.btnWidth,
            height: o.btnHeight,
            queueID: true,
            buttonImage: o.btnImage,
            buttonText: o.btnText,
            fileObjName : o.fileObjName,
            fileTypeExts: o.exts,
            fileSizeLimit: o.fileSize,
            multi: o.multi,
            successTimeout: o.timeout,
            onUploadSuccess: function(file, data, response) {
                var upimgbox = $("#" + o.buttonId).parents(".upload-btn").prev();
                var imgbigbox = $("#" + o.buttonId).parents(".upload-img-box").find(".img-bigbox");
                var img_num = $("#" + o.buttonId).parents(".upload-img-box").find("em");
        
                if(typeof(data) === 'string'){
                    try{
                        data = JSON.parse(data);//ie 89 ff ch
                    }catch(e){
                        data = eval('('+data+')'); //ie7
                    }
                }
                if(data.error == 1){
                        alert(data.msg);
                }else if(data.error == 2){
                        alert(data.msg);
                        location.href = "user.php";
                }else{
                        upimgbox.html(data.content); //小图
                        imgbigbox.find("img").attr("src", data.currentImg_path);
                        imgbigbox.find("img").attr("data-imgId", data.currentImg_id);
                        imgbigbox.show(); //大图
                        img_num.html(data.imglist_count);
                }
            }
        });
    },
    uploads_back : function(opts){
        var o = $.extend({
            buttonId: "",
            fileName: "",
            fileShow: "",
            fileShowBox: "",
            fileWidth: 0, //小图宽度
            fileHeight: 0, //小图高度，0为自动拉伸
            fileSize: "1MB", //上传文件的限制大小
            skinPath: '', //路径
            imageUrl: '', //图片地址的路径
            userId: '', //用户id
            fileObjName: 'Filedata',
            exts: "*.gif;*.jpg;*.png", //定义允许上传的文件后缀。
            multi: false, //设置值为false时，一次只能选中一个文件。
            type: 1, //1 标示图片上传 2 其他的文档上传
            timeout: 10, //表示uploadify的成功等待时间
            btnText: uploads_file, //定义显示在默认按钮上的文本。
            btnImage: "", //按钮图片
            btnWidth: 88, //按钮宽度
            btnHeight: 30 //按钮高度
        }, opts || {});
        
        $("#" + o.buttonId).uploadify({
            uploader : o.uploader,
            swf: o.skinPath + './js/uploadify.swf',
            width: o.btnWidth,
            height: o.btnHeight,
            queueID: true,
            buttonImage: o.btnImage,
            buttonText: o.btnText,
            fileObjName : o.fileObjName,
            fileTypeExts: o.exts,
            fileSizeLimit: o.fileSize,
            multi: o.multi,
            successTimeout: o.timeout,
            onUploadSuccess: function(file, data, response) {
                if(typeof(data) === 'string'){
                    try{
                        data = JSON.parse(data);//ie 89 ff ch
                    }catch(e){
                        data = eval('('+data+')'); //ie7
                    }
                }
                if(data.error == 1){
                        alert(uploads_Prompt);
                }else if(data.error == 2){
                        alert(uploads_login);
                        location.href = "user.php";
                }else{
                        $('.mslist').html(data.content);
                        $('.return_images').show();
                }
            }
        });
    }
}