// JavaScript Document

window.onload = function(){
	
      var js_qx = document.getElementsByName('js_qx'); //全选
      var js_checkbox = document.getElementsByName('js_checkbox'); //单选
	  var js_fx = document.getElementsByName('js_fx'); //反选
	  
      var js_table = document.getElementById('js_table');
      var table_td = js_table.getElementsByTagName('td'); 
	  
	  var cValue = document.getElementById('cart_value'); //存储购物车ID
	 
	var t4 = '';  
	for(var k=0;k<js_checkbox.length;k++){
		js_checkbox[k].checked = true;
		t4 += js_checkbox[k].value + ",";
	}
	
	t4=t4.substring(0,t4.length-1);
	cValue.value = t4;
	  
      for(var i=0;i<js_qx.length;i++){
        js_qx[i].onclick=function(){
            if(this.checked == true){
               for(var k=0;k<js_checkbox.length;k++){
                js_checkbox[k].checked = true;
               }
               for(var i=0;i<js_qx.length;i++){
                js_qx[i].checked = true;
               }
               for(var n=0;n<(table_td.length-1);n++){
				   if(table_td[n].className != 'ru_list'){
					   table_td[n].style.backgroundColor = '#FFFDEE';   
					}
               }
            }else{
               for(var k=0;k<js_checkbox.length;k++){
                js_checkbox[k].checked = false;
               }
               for(var i=0;i<js_qx.length;i++){
                js_qx[i].checked = false;
               }
               for(var n=0;n<(table_td.length-1);n++){
                table_td[n].style.backgroundColor = '#FFFFFF';   
               } 
            }
			
			  //zhuo start
			  var t1 = "";
			  for(var k=0;k<js_checkbox.length;k++){  
				  if(js_checkbox[k].checked == true){
						t1 += js_checkbox[k].value + ",";
				  }
			  }
			  
			  t1=t1.substring(0,t1.length-1);
			  
			  cValue.value = t1;
			  //获取选择的购物车ID的商品信息
			  change_cart_goods_number(t1);
			  //zhuo end
        }
      }

      for(var i=0;i<js_checkbox.length;i++){
        js_checkbox[i].onclick = function(){
          if(this.checked == true){
            var tr_child = this.parentNode.parentNode.childNodes;
            for(var k=0;k<tr_child.length;k++){
                if(tr_child[k].nodeName == "#text" && !/\S/.test(tr_child[k].nodeValue)) { 
                   this.parentNode.parentNode.removeChild(tr_child[k])
                }
            }
            for(var n=0;n<tr_child.length;n++){
              tr_child[n].style.backgroundColor = '#FFFDEE';
            }
          }else{
            var tr_child = this.parentNode.parentNode.childNodes;
            for(var k=0;k<tr_child.length;k++){
                if(tr_child[k].nodeName == "#text" && !/\S/.test(tr_child[k].nodeValue)) { 
                   this.parentNode.parentNode.removeChild(tr_child[k])
                }
            }
            for(var n=0;n<tr_child.length;n++){
              tr_child[n].style.backgroundColor = '#FFFFFF';
            }  
          }

          for(var m=0;m<js_checkbox.length;m++){
            if(js_checkbox[m].checked == false){
              for(var n=0;n<js_qx.length;n++){
                js_qx[n].checked = false;
              }
            }
          }
		  
		  //zhuo start
		  var t2 = "";
		  for(var k=0;k<js_checkbox.length;k++){  
			  if(js_checkbox[k].checked == true){
					t2 += js_checkbox[k].value + ",";
			  }
		  }
		  
		  t2=t2.substring(0,t2.length-1);
		  
		  cValue.value = t2;
		  //获取选择的购物车ID的商品信息
		  change_cart_goods_number(t2);
		  //zhuo end
        }
      }
	  
      js_fx[0].onclick = function(){
          for(var i=0;i<js_qx.length;i++){
            if(js_qx[i].checked == true){
               js_qx[i].checked = false;
            }
          }
          for(var k=0;k<js_checkbox.length;k++){
            if(js_checkbox[k].checked == true){
               js_checkbox[k].checked = false;
               var t_td = js_checkbox[k].parentNode.parentNode.getElementsByTagName('td');
               for(var n=0;n<t_td.length;n++){
                t_td[n].style.backgroundColor = '#FFFFFF';
               }
            }else{
               js_checkbox[k].checked = true;
               var t_td = js_checkbox[k].parentNode.parentNode.getElementsByTagName('td');
               for(var n=0;n<t_td.length;n++){
                t_td[n].style.backgroundColor = '#FFFDEE';
               }
            }
          }
		  
		  //zhuo start
		  var t3 = "";
		  for(var k=0;k<js_checkbox.length;k++){  
			  if(js_checkbox[k].checked == true){
					t3 += js_checkbox[k].value + ",";
			  }
		  }
		  
		  t3=t3.substring(0,t3.length-1);
		  
		  cValue.value = t3;
		  //获取选择的购物车ID的商品信息
		  change_cart_goods_number(t3);
		  //zhuo end
      }

      var js_del = document.getElementById('js_del');
      js_del.onclick = function(){
        var n_id = '';
        for(var i=0;i<js_checkbox.length;i++){
          if(js_checkbox[i].checked == true){
             n_id += js_checkbox[i].value+'@';
          }
        }
        n_id = n_id.substr(0,n_id.length-1);

        if(confirm(confirm_cancel_cart)){
          window.location.href = 'flow.php?step=drop_goods&id='+n_id+'&sig=sig;'
        }

      }
	  
	function change_cart_goods_number(rec_id)
	{     
		Ajax.call('flow.php?step=ajax_cart_goods_amount', 'rec_id=' + rec_id, change_cart_goods_response, 'POST','JSON');                
	}
	function change_cart_goods_response(result)
	{  
		document.getElementById('cart_amount').innerHTML = result.goods_amount;
		
		document.getElementById('favourable_list').innerHTML = result.favourable_list_content;
		document.getElementById('your_discount').innerHTML = result.your_discount;
		if(result.discount){
			document.getElementById('cart_discount').style.display = '';
		}else{
			document.getElementById('cart_discount').style.display = 'none';
		}
	}
}
