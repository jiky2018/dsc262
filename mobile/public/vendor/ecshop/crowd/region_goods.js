/* $Id : region.js 4865 2007-01-31 14:04:10Z paulgao $ */

var region = new Object();

region.isAdmin = false;

region.loadRegions = function(parent, type, target, user_id)
{
  $.get(region.getFileName(), {type:type, target:target, parent:parent, user_id:user_id}, function(data){
    region.response(data, '');
  }, 'json');
  //Ajax.call(region.getFileName(), 'type=' + type + '&target=' + target + "&parent=" + parent , region.response, "GET", "JSON");
}

/* *
 * 载入指定的国家下所有的省份
 *
 * @country integer     国家的编号
 * @selName string      列表框的名称
 */
region.loadProvinces = function(country, selName)
{
  var objName = (typeof selName == "undefined") ? "selProvinces" : selName;

  region.loadRegions(country, 1, objName);
}

/* *
 * 载入指定的省份下所有的城市
 *
 * @province    integer 省份的编号
 * @selName     string  列表框的名称
 */
region.loadCities = function(province, selName)
{
  var objName = (typeof selName == "undefined") ? "selCities" : selName;

  region.loadRegions(province, 2, objName);
}

/* *
 * 载入指定的城市下的区 / 县
 *
 * @city    integer     城市的编号
 * @selName string      列表框的名称
 */
region.loadDistricts = function(city, selName)
{
  var objName = (typeof selName == "undefined") ? "selDistricts" : selName;

  region.loadRegions(city, 3, objName);
}

/* *
 * 处理下拉列表改变的函数
 *
 * @obj     object  下拉列表
 * @type    integer 类型
 * @selName string  目标列表框的名称
 */
region.getRegion = function(parent, type, user_id)
{
    //省级
    $("#province_id").val(parent);
    $.get(region.getFileName(), {parent:parent, type:type, user_id:user_id}, function(data){
        if(data.regions.length > 0){
            var str = "";
            var regions = data.regions;
            for(key in regions){
                if(regions[key]['district'].length > 0){
                    str += '<a class="select-title padding-all j-menu-select" ><label class="fl">'+regions[key]['region_name']+'</label><span class="fr t-jiantou j-t-jiantou" id="j-t-jiantou"><i class="iconfont icon-jiantou tf-180 ts-2"></i></span></a>';
                    if(typeof(regions[key]['district']) != undefined){
                        var district = regions[key]['district'];
                        str += '<ul class="padding-all j-sub-menu" style="display:none;">';
                        for(k in district){
                            str += ' <li class="ect-select"><label onclick="region.changedDis('+district[k]['region_id']+', '+regions[key]['region_id']+', '+data['user_id']+')" class="ts-1">'+district[k]['region_name']+'<i class="fr iconfont icon-gou ts-1"></i></label></li>';
                        }
                        str += '</ul>'
                    }
                }
                else{
                    str += '<a class="select-title padding-all j-menu-select" onclick="region.changedDis(0, '+regions[key]['region_id']+', '+data['user_id']+')"><label class="fl">'+regions[key]['region_name']+'</label><span class="fr t-jiantou j-t-jiantou" id="j-t-jiantou"><i class="iconfont icon-jiantou tf-180 ts-2"></i></span></a>';
                }
            }
            if(str){
                $(".j-city-right .j-get-city-one").html(str);
            }
        }
    }, 'json');
    return false;
}

region.getFileName = function()
{
  return ROOT_URL + "index.php?m=region";
}

//获取对应地区所在仓库
region.changedDis = function(district_id, city_id, user_id, d_null){

    var province_id = document.getElementById('province_id').value;
    var goods_id = document.getElementById('good_id').value;
    var url = ROOT_URL + 'index.php?m=goods&a=in_stock';

    if( city_id <= 0){
        return false;
    }
    if(d_null == 1){
        d_null = d_null;
    }else{
        d_null = '';
    }

    $("#city_id").val(city_id);
    //县级
    $("#district_id").val(district_id);

    $.get(url, {id:goods_id, province:province_id, city:city_id, district:district_id, user_id:user_id, d_null:d_null}, function(data){
    region.is_inStock(data);
    }, 'json');
}

region.is_inStock = function(res)
{
  if(res.isRegion == 0){

      if (confirm(res.message))
        {
          var district_id = document.getElementById('district_id');
          district_id.value = res.district;
          location.href = ROOT_URL + 'index.php?m=user&a=address_list';
        }
        else{
          //location.href = "index.php?m=default&c=goods&id="+ res.goods_id +"&t=" + parseInt(Math.random()*1000) + "#areaAddress";
          location.reload();
        }
  }else{
    //location.href = "index.php?m=default&c=goods&id="+ res.goods_id +"&t=" + parseInt(Math.random()*1000) + "#areaAddress";
      location.reload();
  }
  return false;
}


//仓库选择
function warehouse(region_id, goodsId){
    if(region_id && goodsId){
        var url = ROOT_URL + 'index.php?m=goods&a=in_warehouse';
        $.get(url, {pid:region_id, id:goodsId}, function(data){
            if(data.goods_id){
                location.href = ROOT_URL + 'index.php?m=goods&id='+data.goods_id;
            }
        }, 'json');
    }
}

/**
 * 商品列表地区筛选
 *
 * @obj     object  下拉列表
 * @type    integer 类型
 * @selName string  目标列表框的名称
 */
region.selectRegion = function(raId, parent, type)
{
    $.get(ROOT_URL + 'index.php?m=region&a=select_region_child', {raId:raId, parent:parent, type:type}, function(data){
        if(data.regions.length > 0){
            var str = "";
            var regions = data.regions;
            for(key in regions){
                if(regions[key]['district'].length > 0){
                    str += '<a class="select-title padding-all j-menu-select"><label class="fl">'+regions[key]['region_name']+'</label><span class="fr t-jiantou j-t-jiantou" id="j-t-jiantou"><i class="iconfont icon-jiantou tf-180 ts-2"></i></span></a>';
                    if(typeof(regions[key]['district']) != undefined){
                        var district = regions[key]['district'];
                        str += '<ul class="padding-all j-sub-menu" style="display:none;">';
                        for(k in district){
                            str += ' <li class="ect-select"><label onclick="region.selectDis('+district[k]['region_id']+', 1)" class="ts-1">'+district[k]['region_name']+'<i class="fr iconfont icon-gou ts-1"></i></label></li>';
                        }
                        str += '</ul>'
                    }
                }
                else{
                    str += '<a class="select-title padding-all" onclick="region.selectDis('+regions[key]['region_id']+', 0)"><label class="fl">'+regions[key]['region_name']+'</label><span class="fr t-jiantou j-t-jiantou" id="j-t-jiantou"><i class="iconfont icon-jiantou tf-180 ts-2"></i></span></a>';
                }
            }
            if(str){
                $(".j-city-right .j-get-city-one").html(str);
            }
        }
    }, 'json');
    return false;
}

/**
 * 商品列表地区筛选
 * @param region_id
 * @param type
 */
region.selectDis = function(region_id, type){
    $.get(ROOT_URL + 'index.php?m=region&a=select_district_list', {region_id: region_id, type: type}, function(data){
        if(data.error == 0){
            location.reload();
        }
    }, 'json');
}

//修改地址
//
region.cccDdd = function(district_id, city_id, user_id, d_null){

    var province_id = document.getElementById('province_id').value;
    var url = ROOT_URL + 'index.php?m=user&a=addaddress/in_stock';
    if( city_id <= 0){
        return false;
    }
    if(d_null == 1){
        d_null = d_null;
    }else{
        d_null = '';
    }
    $("#province_id").val(province_id);
    $("#city_id").val(city_id);
    //县级
    $("#district_id").val(district_id);
    $(".show-city-div").removeClass("show-city-div");
     var  province = $("input[name=province_region_id]").val();
     var  city = $("input[name=city_region_id]").val();
     var  district = $("input[name=district_region_id]").val();
   $.post(ROOT_URL + 'index.php?m=user&a=show_region_name',{province:province,city:city,district:district},function(obj){
        obj.district.region_name = (obj.district.region_name ? obj.district.region_name : '');
        $(".show-region").text(obj.province.region_name+obj.city.region_name+obj.district.region_name);
   }, 'json');
}
//地址
region.getBbb = function(parent, type, user_id)
{
    //省级
    $("#province_id").val(parent);
    $.get(region.getFileName(), {parent:parent, type:type, user_id:user_id}, function(data){
        if(data.regions.length > 0){
            var str = "";
            var regions = data.regions;
            for(key in regions){
                if(regions[key]['district'].length > 0){
                    str += '<a class="select-title padding-all j-menu-select" ><label class="fl">'+regions[key]['region_name']+'</label><span class="fr t-jiantou j-t-jiantou" id="j-t-jiantou"><i class="iconfont icon-jiantou tf-180 ts-2"></i></span></a>';
                    if(typeof(regions[key]['district']) != undefined){
                        var district = regions[key]['district'];
                        str += '<ul class="padding-all j-sub-menu" style="display:none;">';
                        for(k in district){
                            str += ' <li class="ect-select"><label onclick="region.cccDdd('+district[k]['region_id']+', '+regions[key]['region_id']+', '+data['user_id']+')" class="ts-1">'+district[k]['region_name']+'<i class="fr iconfont icon-gou ts-1"></i></label></li>';
                        }
                        str += '</ul>'
                    }
                }
                else{
                    str += '<a class="select-title padding-all j-menu-select" onclick="region.cccDdd(0, '+regions[key]['region_id']+', '+data['user_id']+')"><label class="fl">'+regions[key]['region_name']+'</label><span class="fr t-jiantou j-t-jiantou" id="j-t-jiantou"><i class="iconfont icon-jiantou tf-180 ts-2"></i></span></a>';
                }
            }
            if(str){
                $(".j-city-right .j-get-city-one").html(str);
            }
        }
    }, 'json');
    return false;
}
function showregionname(){
   var  province = $("input[name=province_region_id]").val();
   var  city = $("input[name=city_region_id]").val();
   var  district = $("input[name=district_region_id]").val();
   $.post(ROOT_URL + 'index.php?m=user&a=show_region_name',{province:province,city:city,district:district},function(obj){
       alert(obj.city);

   });
}
