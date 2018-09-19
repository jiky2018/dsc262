/*!
 * jquery.validate.js 的扩展方法
 * 基于jquery.validate.js方法新增的扩展验证方法
 * sunle
 */
 
//----------------自定义验证方法 Start-------------------------------------------------------------------------------
 
/**
 * 验证是否是有效的手机号
 */
$.validator.addMethod("phoneZH", function(value, element) { 
    var tel = /^(((13[0-9]{1})|(14[0-9]{1})|(17[0]{1})|(15[0-3]{1})|(15[5-9]{1})|(18[0-9]{1}))+\d{8})$/; 
    return tel.test(value) || this.optional(element); 
}, "请输入正确的手机号码");
 
 
/**
 * 验证是否电话号码  //电话号码格式010-12345678
 */
$.validator.addMethod("isTel", function(value, element) {
   var tel = /^\d{3,4}-?\d{7,9}$/;   
   return this.optional(element) || (tel.test(value));
}, "请正确填写您的电话号码");
 
 
 
//联系电话(手机/电话皆可)验证
$.validator.addMethod("isPhone", function(value,element) {
	var length = value.length;
	var mobile = /^(((13[0-9]{1})|(14[0-9]{1})|(17[0]{1})|(15[0-3]{1})|(15[5-9]{1})|(18[0-9]{1}))+\d{8})$/;
	var tel = /^\d{3,4}-?\d{7,9}$/;
	return this.optional(element) || (tel.test(value) || mobile.test(value));
}, "请正确填写您的联系电话");
 
 
/**
 * 验证是否字母数字
 */
$.validator.addMethod("isStringAndNum", function(value, element) {
	var stringAndNum=/^[a-zA-Z0-9]+$/;
    return this.optional(element) || stringAndNum.test(value);
}, "只能包括英文字母和数字");
 
 
/**
 * 验证是否是汉字
 */
$.validator.addMethod("chcharacter", function(value, element) {
	var tel = /^[\u4e00-\u9fa5]+$/;
	return this.optional(element) || (tel.test(value));
}, "请输入汉字");
 
 
/**
 * 邮政编码验证
 */
$.validator.addMethod("isZipCode", function(value, element) {
var tel = /^[0-9]{6}$/;
return this.optional(element) || (tel.test(value));
}, "请正确填写您的邮政编码");

$.validator.addMethod("isphoneCode",function(value,element,params){
	var val = $(params).val();
    if(val == 0){
		return true;
	}else{
		return false;
	}
},"验证码不正确");
 
//----------------自定义验证方法 END-----------------------------------------------------------------------
