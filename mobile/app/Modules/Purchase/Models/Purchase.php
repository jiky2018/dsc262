<?php

namespace App\Modules\Purchase\Models;

use Think\Model;
use App\Extensions\Scws4;
use App\Models\WholesaleCat;

class Purchase extends Model {


    public static function get_banner ($id, $num){
        $time = gmtime();

        $arr = [
            'id' => $id,
            'num' => $num
        ];

        $sql = 'SELECT a.ad_id, a.position_id, a.media_type, a.ad_link, a.ad_code, a.ad_name, p.ad_width, ' .
            'p.ad_height, p.position_style, RAND() AS rnd ' .
            'FROM ' . $GLOBALS['ecs']->table('touch_ad') . ' AS a ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('touch_ad_position') . ' AS p ON a.position_id = p.position_id ' .
            "WHERE enabled = 1 AND start_time <= '" . $time . "' AND end_time >= '" . $time . "' " .
            "AND a.position_id = '" . $arr['id'] . "' " .
            'ORDER BY rnd LIMIT ' . $arr['num'];
        $res = $GLOBALS['db']->GetAll($sql);

        foreach ($res as $key=>$row) {
            if ($row['position_id'] != $arr['id']) {
                continue;
            }

            switch ($row['media_type']) {
                case 0: 
                    $src = (strpos($row['ad_code'], 'http://') === false && strpos($row['ad_code'], 'https://') === false) ?
                        get_data_path($row['ad_code'], 'afficheimg') : $row['ad_code'];

                    $ads[] = $src;
                    break;
            }
        }

        return $ads;
    }


    public static function get_wholesale_child_cat($cat_id = 0, $type = 0) {
        if ($cat_id > 0) {

            $parent_id = WholesaleCat::select('parent_id')
                ->where('cat_id', $cat_id)
                ->limit(1)
                ->first();
            if ( $parent_id != [] ) {
                $parent_id = $parent_id->toArray();
                $parent_id = $parent_id['parent_id'];
            }
        } else {
            $parent_id = 0;
        }

        
        $cat_id = WholesaleCat::select('cat_id')
            ->where('parent_id', $parent_id)
            ->where('is_show', 1)
            ->limit(1)
            ->first();
        if ( $cat_id != [] ) {
            $cat_id = $cat_id->toArray();
            $cat_id = $cat_id['cat_id'];
        }
        

        if ( !empty($cat_id) || $parent_id == 0) {
            
            $res = WholesaleCat::select('cat_id', 'cat_name', 'parent_id', 'is_show', 'style_icon')
                ->where('parent_id', $parent_id)
                ->where('is_show', 1)
                ->orderby('sort_order', "ASC")
                ->orderby('cat_id', "ASC")
                ->get()
                ->toArray();

            $cat_arr = [];
            foreach ($res AS $row) {
                $cat_arr[$row['cat_id']]['id'] = $row['cat_id'];
                $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
                $cat_arr[$row['cat_id']]['style_icon'] = $row['style_icon'];

                $cat_arr[$row['cat_id']]['url'] = url('purchase/index/list', ['id' => $row['cat_id']]);

                if (isset($row['cat_id']) != NULL) {
                    $cat_arr[$row['cat_id']]['cat_id'] = self::get_wholesale_child_tree($row['cat_id']);
                }
            }
        }
        return $cat_arr;
    }


    private static function get_wholesale_child_tree($tree_id = 0, $ru_id = 0) {
        $three_arr = [];
        $res = WholesaleCat::where('parent_id', $tree_id)
            ->where('is_show', 1)
            ->count();

        if ( !empty($res) || $tree_id == 0 ) {
            $res = WholesaleCat::select('cat_id', 'cat_name', 'parent_id', 'is_show')
                ->where('parent_id', $tree_id)
                ->where('is_show', 1)
                ->orderby('sort_order', "ASC")
                ->orderby('cat_id', "ASC")
                ->get()
                ->toArray();

            foreach ($res AS $row) {
                if ($row['is_show'])
                    $three_arr[$row['cat_id']]['id'] = $row['cat_id'];
                $three_arr[$row['cat_id']]['name'] = $row['cat_name'];

                if ($ru_id) {

                    $build_uri = [
                        'cid' => $row['cat_id'],
                        'urid' => $ru_id,
                        'append' => $row['cat_name']
                    ];

                    $domain_url = get_seller_domain_url($ru_id, $build_uri);

                    $three_arr[$row['cat_id']]['url'] = $domain_url['domain_name'];
                } else {
                    $three_arr[$row['cat_id']]['url'] = url('purchase/index/list', ['id' => $row['cat_id']]);
                }

                if (isset($row['cat_id']) != NULL) {
                    $three_arr[$row['cat_id']]['cat_id'] = self::get_wholesale_child_tree($row['cat_id']);
                }
            }
        }
        return $three_arr;
    }


    public static function get_wholesale_limit() {
        $now = gmtime();
        $sql = "SELECT w.*, g.goods_name, g.goods_thumb, g.goods_img, MIN(wvp.volume_number) AS volume_number, MAX(wvp.volume_price) AS volume_price, g.goods_unit FROM " . $GLOBALS['ecs']->table('wholesale') . " AS w"
            . " LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON w.goods_id = g.goods_id "
            . " LEFT JOIN " . $GLOBALS['ecs']->table('wholesale_volume_price') . " AS wvp ON wvp.goods_id = g.goods_id "
            . " WHERE w.enabled = 1 AND w.review_status = 3 AND w.is_promote = 1 AND '$now' BETWEEN w.start_time AND w.end_time GROUP BY goods_id";
        $res = $GLOBALS['db']->getAll($sql);

        foreach ($res as $key => $row) {
            $res[$key]['formated_end_date'] = local_date($GLOBALS['_CFG']['date_format'], $row['end_time']);
            $res[$key]['small_time'] = $row['end_time'] - $now;
            $res[$key]['goods_name'] = $row['goods_name'];
            $res[$key]['goods_price'] = $row['goods_price'];
            $res[$key]['moq'] = $row['moq'];
            $res[$key]['volume_number'] = empty($row['volume_number']) ? $row['moq'] : $row['volume_number'] ;
            $res[$key]['volume_price'] =empty($row['volume_price']) ? $row['goods_price'] : $row['volume_price'];
            $res[$key]['goods_unit'] = $row['goods_unit'];
            $res[$key]['thumb'] = get_image_path($row['goods_thumb'], true);
            $res[$key]['goods_thumb'] =get_image_path($row['goods_thumb']);
            $res[$key]['goods_img'] = get_image_path($row['goods_img']);
            $res[$key]['url'] = url('purchase/index/goods', ['id' => $row['act_id']]);
        }

        return $res;
    }


    public static function get_wholesale_cat() {
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('wholesale_cat') . "WHERE parent_id = 0 ORDER BY sort_order ASC ";
        $cat_res = $GLOBALS['db']->getAll($sql);

        foreach ($cat_res as $key => $row) {
            $cat_res[$key]['goods'] = self::get_business_goods($row['cat_id']);
            $cat_res[$key]['count_goods'] = count(self::get_business_goods($row['cat_id']));
            $cat_res[$key]['cat_url'] = url('purchase/index/list', ['id' => $row['cat_id']]);
        }
        return $cat_res;
    }


    public static function get_business_goods($cat_id) {
        $table = 'wholesale_cat';
        $type = 4;
        $children = get_children($cat_id, $type, 0, $table);

        $sql = "SELECT w.*, g.goods_thumb, g.goods_img, MIN(wvp.volume_number) AS volume_number, MAX(wvp.volume_price) AS volume_price, g.goods_unit FROM " . $GLOBALS['ecs']->table('wholesale') . " AS w "
            . " LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON w.goods_id = g.goods_id "
            . " LEFT JOIN " . $GLOBALS['ecs']->table('wholesale_volume_price') . " AS wvp ON wvp.goods_id = g.goods_id "
            . " WHERE ($children OR " . self::get_wholesale_extension_goods($children, 'w.') . ") AND w.enabled = 1 AND w.review_status = 3 GROUP BY goods_id";
        $res = $GLOBALS['db']->getAll($sql);
        foreach ($res as $key => $row) {
            $res[$key]['goods_extend'] = self::get_wholesale_extend($row['goods_id']);
            $res[$key]['goods_sale'] = self::get_sale($row['goods_id']);
            $res[$key]['goods_price'] = $row['goods_price'];
            $res[$key]['moq'] = $row['moq'];
            $res[$key]['volume_number'] = $row['volume_number'];
            $res[$key]['volume_price'] = $row['volume_price'];
            $res[$key]['goods_unit'] = $row['goods_unit'];
            $res[$key]['goods_name'] = $row['goods_name'];
            $res[$key]['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);

            $res[$key]['goods_thumb'] = get_image_path($row['goods_thumb']);
            $res[$key]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
            $res[$key]['url'] = url('goods', ['id' => $row['act_id']]);
        }

        return $res;
    }


    public static function get_wholesale_extend($goods_id) {

        $extend_sql = "SELECT * FROM " . $GLOBALS['ecs']->table('wholesale_extend') . " WHERE goods_id = '$goods_id'";
        return $GLOBALS['db']->getRow($extend_sql);
    }

    public static function get_wholesale_extension_goods($cats, $alias = 'w.') {
        $extension_goods_array = '';
        $sql = 'SELECT goods_id FROM ' . $GLOBALS['ecs']->table('wholesale') . " AS w WHERE $cats";
        $extension_goods_array = $GLOBALS['db']->getCol($sql);

        return db_create_in($extension_goods_array, $alias . 'goods_id');
    }

    public static function get_sale($goods_id = 0) {
        $sql = "SELECT SUM(og.goods_number) FROM " . $GLOBALS['ecs']->table('wholesale_order_info') . " AS oi "
            . " LEFT JOIN " . $GLOBALS['ecs']->table('wholesale_order_goods') . " AS og ON og.order_id = oi.order_id "
            . " WHERE oi.main_order_id > 0 AND oi.is_delete = 0 AND oi.main_order_id > 0 AND og.goods_id=" . $goods_id;
        $count = $GLOBALS['db']->getOne($sql);
        return $count;
    }


    public static function getCatName ($cat_id) {
        $sql = " SELECT cat_name FROM " . $GLOBALS['ecs']->table('wholesale_cat') . " WHERE cat_id = '$cat_id' ";
        $res =  $GLOBALS['db']->getOne($sql);
        return $res;
    }


    public static function get_wholesale_list($cat_id, $size, $page) {
        $list = [];
        $where = " WHERE 1 ";
        $table = 'wholesale_cat';
        $type = 4;
        $children = get_children($cat_id, $type, 0, $table);
        if ($cat_id) {
            $where .= " AND ($children OR " . self::get_wholesale_extension_goods($children) . ") ";
        }

        $sqlFrom =  "FROM " . $GLOBALS['ecs']->table('wholesale') . " AS w, " .
            $GLOBALS['ecs']->table('goods') . " AS g "
            . " LEFT JOIN " . $GLOBALS['ecs']->table('wholesale_volume_price') . " AS wvp ON wvp.goods_id = g.goods_id "
            . $where
            . " AND w.goods_id = g.goods_id AND w.enabled = 1 AND w.review_status = 3";

        $sql = "SELECT w.*, g.goods_thumb, g.user_id,g.goods_name as goods_name, g.shop_price, market_price, MIN(wvp.volume_number) AS volume_number, MAX(wvp.volume_price) AS volume_price " . $sqlFrom . " GROUP BY g.goods_id ";

        $sqlCount = "SELECT COUNT('w') ".$sqlFrom;

        $count = $GLOBALS['db']->getOne($sqlCount);
        $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

        foreach ( $res as $row ) {

            $row['goods_thumb'] = get_image_path($row['goods_thumb']); 


            $shop_information = get_shop_name($row['user_id']); 
            $row['is_IM'] = empty($shop_information['is_im']) ? 0 : (int)$shop_information['is_im']; 

            if ($row['user_id'] == 0) {

                if ($GLOBALS['db']->getOne("SELECT kf_im_switch FROM " . $GLOBALS['ecs']->table('seller_shopinfo') . "WHERE ru_id = 0", true)) {
                    $row['is_dsc'] = true;
                } else {
                    $row['is_dsc'] = false;
                }
            } else {
                $row['is_dsc'] = false;
            }


            $row['goods_url'] = url('goods', ['id' => $row['act_id']]);
            $properties = get_goods_properties($row['goods_id']);
            $row['goods_attr'] = $properties['pro'];
            $row['goods_sale'] = get_sale($row['goods_id']);
            $row['goods_extend'] = get_wholesale_extend($row['goods_id']); 
            $row['rz_shopName'] = get_shop_name($row['user_id'], 1); 
            $build_uri = [
                'urid' 		=> $row['user_id'],
                'append' 	=> $row['rz_shopName']
            ];

            $domain_url = get_seller_domain_url($row['user_id'], $build_uri);
            $row['store_url'] = $domain_url['domain_name'];
            $row['shop_price'] = price_format($row['shop_price']);
            $row['market_price'] = price_format($row['market_price']);
            $list[] = $row;
        }
        return ['list' => $list, 'totalPage' => ceil($count/$size)];
    }



    public static function get_search_goods_list ($keyword, $page = 1, $size = 10) {

        $keywords = '';
        $tag_where = '';
        if (!empty($keyword)) {

            $scws = new Scws4();
            $scws_res = $scws->segmentate($_REQUEST['keywords'], true);
            $arr = explode(',', $scws_res);

            $goods_ids = [];

            foreach ($arr AS $key => $val) {
                if ($key > 0 && $key < count($arr) && count($arr) > 1) {
                    $keywords .= $operator;
                }
                $val = mysql_like_quote(trim($val));
                $keywords .= " AND w.goods_name LIKE '%$val%' OR w.goods_price LIKE '%$val%' ";

                $sql = 'SELECT DISTINCT goods_id FROM ' . $GLOBALS['ecs']->table('tag') . " WHERE tag_words LIKE '%$val%' ";
                $res =  $GLOBALS['db']->query($sql);
                foreach ($res as $row) {
                    $goods_ids[] = $row['goods_id'];
                }

                $GLOBALS['db']->autoReplace($GLOBALS['ecs']->table('keywords'), ['date' => local_date('Y-m-d'),
                    'searchengine' => 'ecshop', 'keyword' => addslashes(str_replace('%', '', $val)), 'count' => 1], ['count' => 1]);
            }

            $goods_ids = array_unique($goods_ids);
            $tag_where = implode(',', $goods_ids);
            if (!empty($tag_where)) {
                $tag_where = 'OR g.goods_id ' . db_create_in($tag_where);
            }
        }

        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('wholesale') . " AS w " .
            "WHERE w.enabled = 1 AND w.review_status = 3 " .
            $keywords . $tag_where;
        $count = $GLOBALS['db']->getOne($sql);
        $max_page = ($count > 0) ? ceil($count / $size) : 1;
        if ($page > $max_page) {
            $page = $max_page;
        }

        $sql = "SELECT w.*, g.goods_thumb, g.goods_img, MIN(wvp.volume_number) AS volume_number, wvp.volume_price " .
            "FROM " . $GLOBALS['ecs']->table('wholesale') . " AS w "
            . " LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON w.goods_id = g.goods_id "
            . " LEFT JOIN " . $GLOBALS['ecs']->table('wholesale_volume_price') . " AS wvp ON wvp.goods_id = g.goods_id "
            . "WHERE w.enabled = 1 AND w.review_status = 3 " .
            $keywords . $tag_where .
            " GROUP BY w.goods_id ORDER BY w.goods_id DESC ";
        $res = $GLOBALS['db']->SelectLimit($sql, $size, ($page - 1) * $size);
        $arr = [];

        foreach ( $res as $row ) {

            $watermark_img = '';

            if ($watermark_img != '') {
                $arr[$row['goods_id']]['watermark_img'] = $watermark_img;
            }

            $arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
            if ($display == 'grid') {
                $arr[$row['goods_id']]['goods_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            } else {
                $arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
            }

            $arr[$row['goods_id']]['goods_extend'] = get_wholesale_extend($row['goods_id']);
            $arr[$row]['goods_price'] = $row['goods_price'];
            $arr[$row]['goods_sale'] = get_sale($row['goods_id']);
            $arr[$row]['moq'] = $row['moq'];
            $arr[$row]['volume_number'] = $row['volume_number'];
            $arr[$row]['volume_price'] = $row['volume_price'];
            $arr[$row['goods_id']]['rz_shopName'] = get_shop_name($row['user_id'], 1); 
            $build_uri = [
                'urid' => $row['user_id'],
                'append' => $row['rz_shopName']
            ];

            $domain_url = get_seller_domain_url($row['user_id'], $build_uri);
            $arr[$row['goods_id']]['store_url'] = $domain_url['domain_name'];

            $arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
            $arr[$row['goods_id']]['goods_price'] = $row['goods_price'];
            $arr[$row['goods_id']]['moq'] = $row['moq'];
            $arr[$row['goods_id']]['volume_number'] = $row['volume_number'];
            $arr[$row['goods_id']]['volume_price'] = $row['volume_price'];
            $arr[$row['goods_id']]['goods_sale'] = get_sale($row['goods_id']);
            $arr[$row['goods_id']]['price_model'] = $row['price_model'];


            $arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb']);

            $arr[$row['goods_id']]['goods_img'] =  '../' . $row['goods_img'];
            $arr[$row['goods_id']]['url'] = url('goods', ['id' => $row['act_id']]);
        }
        return ['list' => $arr, 'totalPage' => ceil($count/$size)];
    }



    public static function get_wholesale_goods_info($act_id, $warehouse_id = 0, $area_id = 0, $select = []) {
        $left_join = '';
        $left_join .= " LEFT JOIN " . $GLOBALS['ecs']->table('wholesale_cat') . " AS wc ON w.wholesale_cat_id = wc.cat_id ";
        $left_join .= " LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON w.goods_id = g.goods_id ";

        $goods_price = " IF(w.price_model=0, w.goods_price, (SELECT MAX(vp.volume_price) FROM " . $GLOBALS['ecs']->table('wholesale_volume_price') . " AS vp WHERE vp.goods_id = w.goods_id)) AS goods_price ";
        $where = " WHERE 1 ";
        $where .= " AND w.act_id = '$act_id' ";
        $sql = " SELECT w.goods_id, w.goods_name, w.goods_type, w.rank_ids, w.price_model, $goods_price, w.goods_number, w.moq, " .
            " wc.cat_id, " .
            " g.user_id, g.market_price, g.shop_price, g.goods_desc, g.goods_img, g.brand_id, g.goods_sn, g.goods_weight, g.goods_unit FROM " .
            $GLOBALS['ecs']->table('wholesale') . " AS w " . $left_join . $where;

        $row = $GLOBALS['db']->getRow($sql);

        if ($row !== false) {
            $row['goods_price_formatted'] = price_format($row['goods_price']);
            $row['volume_price'] = self::get_wholesale_volume_price($row['goods_id']);


            if($GLOBALS['_CFG']['open_oss'] == 1){
                $bucket_info = get_bucket_info();
                if($row['goods_desc']){
                    $desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $row['goods_desc']);
                    $row['goods_desc'] = $desc_preg['goods_desc'];
                }
            }


            $row['goods_extend'] = self::get_wholesale_extend($row['goods_id']);
            $row['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
            $row['shopinfo'] = get_shop_name($row['user_id'], 2);
            $row['shopinfo']['logo_thumb'] = str_replace(['../'], '', $arr['shopinfo']['logo_thumb']);
            $row['goods_weight'] = (intval($row['goods_weight']) > 0) ?
                $row['goods_weight'] . $GLOBALS['_LANG']['kilogram'] :
                ($row['goods_weight'] * 1000) . $GLOBALS['_LANG']['gram'];

            $brand_info = self::get_brand_url($row['brand_id']);
            $row['goods_brand_url'] = !empty($brand_info) ? $brand_info['url'] : '';
            $row['brand_thumb'] = !empty($brand_info) ? $brand_info['brand_logo'] : '';

            $row['rz_shopName'] = get_shop_name($row['user_id'], 1); 
            $row['goods_unit'] = $row['goods_unit'];

            $build_uri = [
                'urid' => $row['user_id'],
                'append' => $arr['rz_shopName']
            ];

            $domain_url = get_seller_domain_url($row['user_id'], $build_uri);
            $row['store_url'] = $domain_url['domain_name'];

            if ($GLOBALS['_CFG']['open_oss'] == 1) {
                $bucket_info = get_bucket_info();
                $row['shopinfo']['brand_thumb'] = $bucket_info['endpoint'] . $row['shopinfo']['brand_thumb'];
            }

        }

        return $row;
    }

    public static function get_wholesale_goods_properties($goods_id, $warehouse_id = 0, $area_id = 0, $goods_attr_id = '', $attr_type = 0) {
        $attr_array = [];
        if (!empty($goods_attr_id)) {
            $attr_array = explode(',', $goods_attr_id);
        }


        $sql = "SELECT attr_group " .
            "FROM " . $GLOBALS['ecs']->table('goods_type') . " AS gt, " . $GLOBALS['ecs']->table('wholesale') . " AS g " .
            "WHERE g.goods_id='$goods_id' AND gt.cat_id=g.goods_type";
        $grp = $GLOBALS['db']->getOne($sql);

        if (!empty($grp)) {
            $groups = explode("\n", strtr($grp, "\r", ''));
        }


        $model_attr = get_table_date("goods", "goods_id = '$goods_id'", ['model_attr'], 2);
        $leftJoin = '';
        $select = '';


        $goodsAttr = '';
        if ($attr_type == 1 && !empty($goods_attr_id)) {
            $goodsAttr = " and g.goods_attr_id in($goods_attr_id) ";
        }


        $where = "";
        $goods_type = get_table_date('wholesale', "goods_id='$goods_id'", ['goods_type'], 2);
        $where .= " AND a.cat_id = '$goods_type' ";


        $sql = "SELECT a.attr_id, a.attr_name, a.attr_group, a.is_linked, a.attr_type, " .
            $select .
            "g.goods_attr_id, g.attr_value, g.attr_price, g.attr_img_flie, g.attr_img_site, g.attr_checked, g.attr_sort " .
            'FROM ' . $GLOBALS['ecs']->table('wholesale_goods_attr') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' AS a ON a.attr_id = g.attr_id ' .
            $leftJoin .
            "WHERE g.goods_id = '$goods_id' " . $goodsAttr . $where . " AND a.attr_type <> 2 " . 
            'ORDER BY a.sort_order, a.attr_id, g.goods_attr_id';

        $res = $GLOBALS['db']->getAll($sql);

        $arr['pro'] = [];   
        $arr['spe'] = [];   
        $arr['lnk'] = [];     

        foreach ($res AS $row) {
            $row['attr_value'] = str_replace("\n", '<br />', $row['attr_value']);

            if ($row['attr_type'] == 0) {
                $group = (isset($groups[$row['attr_group']])) ? $groups[$row['attr_group']] : $GLOBALS['_LANG']['goods_attr'];

                $arr['pro'][$group][$row['attr_id']]['name'] = $row['attr_name'];
                $arr['pro'][$group][$row['attr_id']]['value'] = $row['attr_value'];
            } else {

                if ($model_attr == 1) {
                    $attr_price = $row['warehouse_attr_price'];
                } elseif ($model_attr == 2) {
                    $attr_price = $row['area_attr_price'];
                } else {
                    $attr_price = $row['attr_price'];
                }


                $img_site = [
                    'attr_img_flie' => $row['attr_img_flie'],
                    'attr_img_site' => $row['attr_img_site']
                ];

                $attr_info = get_has_attr_info($row['attr_id'], $row['attr_value'], $img_site);
                $row['img_flie'] = !empty($attr_info['attr_img']) ? get_image_path($row['attr_id'], $attr_info['attr_img'], true) : '';
                $row['img_site'] = $attr_info['attr_site'];

                $arr['spe'][$row['attr_id']]['attr_type'] = $row['attr_type'];
                $arr['spe'][$row['attr_id']]['name'] = $row['attr_name'];
                $arr['spe'][$row['attr_id']]['values'][] = [
                    'label' => $row['attr_value'],

                    'img_flie' => $row['img_flie'],
                    'img_site' => $row['img_site'],
                    'checked' => $row['attr_checked'],
                    'attr_sort' => $row['attr_sort'],
                    'combo_checked' => get_combo_godos_attr($attr_array, $row['goods_attr_id']),

                    'price' => $attr_price,
                    'format_price' => price_format(abs($attr_price), false),
                    'id' => $row['goods_attr_id']
                ];
            }

            if ($row['is_linked'] == 1) {

                $arr['lnk'][$row['attr_id']]['name'] = $row['attr_name'];
                $arr['lnk'][$row['attr_id']]['value'] = $row['attr_value'];
            }


            $arr['spe'][$row['attr_id']]['values'] = get_array_sort($arr['spe'][$row['attr_id']]['values'], 'attr_sort');
            $arr['spe'][$row['attr_id']]['is_checked'] = get_attr_values($arr['spe'][$row['attr_id']]['values']);

        }

        return $arr;
    }


    public static function get_wholesale_volume_price($goods_id = 0, $goods_number = 0) {
        $sql = " SELECT price_model, goods_price FROM " . $GLOBALS['ecs']->table('wholesale') . " WHERE goods_id = '$goods_id' ";
        $res = $GLOBALS['db']->getRow($sql);
        if ($res['price_model']) {

            $sql = " SELECT volume_number, volume_price FROM " . $GLOBALS['ecs']->table('wholesale_volume_price') . " WHERE goods_id = '$goods_id' ORDER BY volume_number ASC ";
            $res['volume_price'] = $GLOBALS['db']->getAll($sql);

            foreach ($res['volume_price'] as $key => $val) {
                if ($key < count($res['volume_price']) - 1) {
                    $range_number = $res['volume_price'][$key + 1]['volume_number'] - 1;
                    $res['volume_price'][$key]['range_number'] = $range_number;
                }
                if ($goods_number >= $val['volume_number']) {
                    $res['volume_price'][$key]['is_reached'] = 1; 
                    if (isset($res['volume_price'][$key - 1]['is_reached'])) {
                        unset($res['volume_price'][$key - 1]['is_reached']);
                    }
                }
            }
        }

        return $res['volume_price'];
    }


    public static function isJurisdiction($goods){
        $is_jurisdiction = 0;

        if($_SESSION['user_id'] > 0){

            $sql = "SELECT user_id FROM " . $GLOBALS['ecs']->table('admin_user') . " WHERE ru_id = '" . $_SESSION['user_id'] . "'";
            $seller_id = $GLOBALS['db']->getOne($sql, true);
            if($seller_id > 0){
                $is_jurisdiction = 1;
            }else{

                if($goods['rank_ids']){
                    $rank_ids = explode(',', $goods['rank_ids']);
                    if(in_array($_SESSION['user_rank'], $rank_ids)){
                        $is_jurisdiction = 1;
                    }
                }
            }
        }else{
            $is_jurisdiction = 1;
        }

        return $is_jurisdiction;
    }


    public static function get_brand_url($brand_id = 0){
        $sql = "SELECT brand_id, brand_name, brand_logo FROM " .$GLOBALS['ecs']->table('brand'). " WHERE brand_id = '$brand_id'";
        $res = $GLOBALS['db']->getRow($sql);
        if ($res) {
            $res['url'] = build_uri('brand', ['bid' => $res['brand_id']], $res['brand_name']);
            $res['brand_logo'] = empty($res['brand_logo']) ? str_replace(['../'], '', $GLOBALS['_CFG']['no_brand']) : DATA_DIR . '/brandlogo/' . $res['brand_logo'];

            if ($GLOBALS['_CFG']['open_oss'] == 1) {
                $bucket_info = get_bucket_info();
                $res['brand_logo'] = $bucket_info['endpoint'] . $res['brand_logo'];
            }

        }

        return $res;
    }

    public static  function wholesale_cart_info($goods_id = 0, $rec_ids = '') {
        if (!empty($_SESSION['user_id'])) {
            $sess_id = " c.user_id = '" . $_SESSION['user_id'] . "' ";
            $sess = "";
        } else {
            $sess_id = " c.session_id = '" . real_cart_mac_ip() . "' ";
            $sess = real_cart_mac_ip();
        }

        if (!empty($goods_id)) {
            $sess_id .= " AND c.goods_id = '$goods_id' ";
        }

        if (!empty($rec_ids)) {
            $sess_id .= " AND c.rec_id IN ($rec_ids) ";
        }

        $cart_info = array(
            'rec_count' => 0,
            'total_number' => 0,
            'total_price' => 0.00,
            'total_price_formatted' => ''
        );
        $sql = " SELECT goods_price, goods_number FROM " . $GLOBALS['ecs']->table('wholesale_cart') . " AS c WHERE $sess_id ";
        $data = $GLOBALS['db']->getAll($sql);
        foreach ($data as $key => $val) {
            $cart_info['rec_count'] += 1;
            $cart_info['total_number'] += $val['goods_number'];
            $total_price = $val['goods_number'] * $val['goods_price'];
            $cart_info['total_price'] += $total_price;
            $cart_info['goods_price'] = $val['goods_price'];
        }
        $cart_info['total_price_formatted'] = price_format($cart_info['total_price']);
        return $cart_info;
    }


    public static function get_wholesale_cart_info (){
        if (!empty($_SESSION['user_id'])) {
            $sess_id = " user_id = '" . $_SESSION['user_id'] . "' ";
            $c_sess = " wc.user_id = '" . $_SESSION['user_id'] . "' ";
        } else {
            $sess_id = " session_id = '" . real_cart_mac_ip() . "' ";
            $c_sess = " wc.session_id = '" . real_cart_mac_ip() . "' ";
        }


        $sql = 'SELECT wc.rec_id, wc.goods_name, wc.goods_attr_id,wc.goods_price, g.goods_thumb,g.goods_id,w.act_id,wc.goods_number,wc.goods_price' .
            ' FROM ' . $GLOBALS['ecs']->table('wholesale_cart') . " AS wc " .
            " LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON g.goods_id=wc.goods_id " .
            " LEFT JOIN " . $GLOBALS['ecs']->table('wholesale') . " AS w ON w.goods_id=wc.goods_id " .
            " WHERE " . $c_sess;
        $row = $GLOBALS['db']->getAll($sql);
        $arr = [];
        $cart_value = '';
        foreach ($row AS $k => $v) {
            $arr[$k]['rec_id'] = $v['rec_id'];
            $arr[$k]['url'] = build_uri('wholesale_goods', ['aid' => $v['act_id']], $v['goods_name']);
            $arr[$k]['goods_thumb'] = get_image_path($v['goods_id'], $v['goods_thumb'], true);
            $arr[$k]['goods_number'] = $v['goods_number'];
            $arr[$k]['goods_price'] = $v['goods_price'];
            $arr[$k]['goods_name'] = $v['goods_name'];
            @$arr[$k]['goods_attr'] = array_values(get_wholesale_attr_array($v['goods_attr_id']));
            $cart_value = !empty($cart_value) ? $cart_value . ',' . $v['rec_id'] : $v['rec_id'];
        }
        $sql = 'SELECT COUNT(rec_id) AS cart_number, SUM(goods_number) AS number, SUM(goods_price * goods_number) AS amount' .
            ' FROM ' . $GLOBALS['ecs']->table('wholesale_cart') .
            " WHERE " . $sess_id;
        $row = $GLOBALS['db']->getRow($sql);
        if ($row) {
            $cart_number = intval($row['cart_number']);
            $number = intval($row['number']);
            $amount = price_format(floatval($row['amount']));
        } else {
            $cart_number = 0;
            $number = 0;
            $amount = 0;
        }

        return [
            'cart_number' => $cart_number,
            'cart_value' => $cart_value,
            'number' => $number,
            'amount' => $amount,
            'goods' => $arr
        ];

    }

    public static function get_wholesale_main_attr_list($goods_id = 0, $attr = []) {
        $goods_type = get_table_date('wholesale', "goods_id='$goods_id'", ['goods_type'], 2);

        $sql = " SELECT DISTINCT attr_id FROM " . $GLOBALS['ecs']->table('wholesale_goods_attr') . " WHERE goods_id = '$goods_id' ";
        $attr_ids = $GLOBALS['db']->getCol($sql);
        if (!empty($attr_ids)) {
            $attr_ids = implode(',', $attr_ids);

            $sort_order = " ORDER BY sort_order DESC, attr_id DESC ";
            $sql = " SELECT attr_id FROM " . $GLOBALS['ecs']->table('attribute') . " WHERE cat_id = '$goods_type' AND attr_id IN ($attr_ids) $sort_order LIMIT 1 ";
            $attr_id = $GLOBALS['db']->getOne($sql);
            $sql = " SELECT goods_attr_id, attr_value FROM " . $GLOBALS['ecs']->table('wholesale_goods_attr') . " WHERE goods_id = '$goods_id' AND attr_id = '$attr_id' ORDER BY goods_attr_id ";
            $data = $GLOBALS['db']->getAll($sql);


            if ($data) {
                foreach ($data as $key => $val) {
                    $new_arr = array_merge($attr, [$val['goods_attr_id']]);
                    $data[$key]['attr_group'] = implode(',', $new_arr); 
                    $set = get_find_in_set($new_arr);
                    $product_info = get_table_date('wholesale_products', "goods_id='$goods_id' $set", ['product_number']);
                    $data[$key] = array_merge($data[$key], $product_info);

                    if (empty($data[$key]) || empty($product_info)) {
                        unset($data[$key]);
                    }
                }
                return $data;
            }
        }

        return false;
    }


    public static function wholesale_cart_goods($goods_id = 0, $rec_ids = '') {
        if (!empty($_SESSION['user_id'])) {
            $sess_id = " c.user_id = '" . $_SESSION['user_id'] . "' ";
        } else {
            $sess_id = " c.session_id = '" . real_cart_mac_ip() . "' ";
        }

        if (!empty($goods_id)) {
            $sess_id .= " AND c.goods_id = '$goods_id' ";
        }

        if (!empty($rec_ids)) {
            $sess_id .= " AND c.rec_id IN ($rec_ids) ";
        }

        $cart_goods = [];

        $sql = " SELECT DISTINCT ru_id FROM " . $GLOBALS['ecs']->table('wholesale_cart') . " AS c WHERE $sess_id ";
        $ru_ids = $GLOBALS['db']->getCol($sql);
        foreach ($ru_ids as $key => $val) {
            $data = [];
            $data['ru_id'] = $val;
            $data['shop_name'] = get_shop_name($val, 1);

            $shop_information = get_shop_name($val); 
            $data['is_IM'] = $shop_information['is_IM']; 

            if ($val == 0) {

                if ($GLOBALS['db']->getOne("SELECT kf_im_switch FROM " . $GLOBALS['ecs']->table('seller_shopinfo') . "WHERE ru_id = 0", true)) {
                    $data['is_dsc'] = true;
                } else {
                    $data['is_dsc'] = false;
                }
            } else {
                $data['is_dsc'] = false;
            }

            $sql = "select * from " . $GLOBALS['ecs']->table('seller_shopinfo') . " where ru_id='" . $val . "'";
            $basic_info = $GLOBALS['db']->getRow($sql);
            $data['kf_type'] = $basic_info['kf_type'];


            if ($basic_info['kf_ww']) {
                $kf_ww = array_filter(preg_split('/\s+/', $basic_info['kf_ww']));
                $kf_ww = explode("|", $kf_ww[0]);
                if (!empty($kf_ww[1])) {
                    $data['kf_ww'] = $kf_ww[1];
                } else {
                    $data['kf_ww'] = "";
                }
            } else {
                $data['kf_ww'] = "";
            }

            if ($basic_info['kf_qq']) {
                $kf_qq = array_filter(preg_split('/\s+/', $basic_info['kf_qq']));
                $kf_qq = explode("|", $kf_qq[0]);
                if (!empty($kf_qq[1])) {
                    $data['kf_qq'] = $kf_qq[1];
                } else {
                    $data['kf_qq'] = "";
                }
            } else {
                $data['kf_qq'] = "";
            }



            $sql = " SELECT DISTINCT goods_id FROM " . $GLOBALS['ecs']->table('wholesale_cart') . " AS c WHERE $sess_id AND c.ru_id = '$val' ";
            $goods_ids = $GLOBALS['db']->getCol($sql);
            foreach ($goods_ids as $a => $g) {

                calculate_cart_goods_price($g, $rec_ids);

                $sql = " SELECT c.rec_id, c.goods_price, c.goods_number, c.goods_attr_id " .
                    " FROM " . $GLOBALS['ecs']->table('wholesale_cart') . " AS c " .
                    " WHERE $sess_id AND c.ru_id = '$val' AND c.goods_id = '$g' ORDER BY c.goods_attr_id"; 
                $res = $GLOBALS['db']->getAll($sql);

                $total_number = 0;
                $total_price = 0;
                foreach ($res as $k => $v) {
                    $res[$k]['goods_price_formatted'] = price_format($v['goods_price']);
                    $res[$k]['total_price'] = $v['goods_price'] * $v['goods_number'];
                    $res[$k]['total_price_formatted'] = price_format($res[$k]['total_price']);
                    $res[$k]['goods_attr'] = get_goods_attr_array($v['goods_attr_id']);

                    $total_number += $v['goods_number'];
                    $total_price += $res[$k]['total_price'];
                }

                $goods_data = get_table_date('wholesale', "goods_id='$g'", ['act_id', 'goods_id, goods_name, price_model, goods_price', 'moq', 'goods_number']);
                $sql=" select goods_thumb from " . $GLOBALS['ecs']->table('goods') . "  where goods_id='$goods_data[goods_id]'";
                $goods_thumb = $GLOBALS['db']->getOne($sql);
                $goods_data['goods_thumb'] = get_image_path($goods_thumb);
                $goods_data['total_number'] = $total_number;
                $goods_data['total_price'] = $total_price;
                $goods_data['goods_number'] = empty($goods_data['goods_number']) ? 1 : $goods_data['goods_number'];
                if (empty($goods_data['price_model'])) {
                    if ($total_number >= $goods_data['moq']) {
                        $goods_data['is_reached'] = 1;
                    }
                } else {
                    $goods_data['volume_price'] = get_wholesale_volume_price($g, $total_number);
                }

                $volume_number = [];
                foreach ( $goods_data['volume_price'] as $k => $v ) {
                    array_push($volume_number, $v['volume_number']);
                }
                sort($volume_number);

                $goods_data['list'] = $res;
                $goods_data['min_number'] = $goods_data['moq']; 
                $product_info = get_table_date('wholesale_products', "goods_id='$g'", ['product_number']);
                $goods_data['max_number'] = ($product_info['product_number'] > 0) ? $product_info['product_number'] : $goods_data['goods_number'];

                $goods_data['count'] = count($res);
                $data['goods_list'][] = $goods_data;
            }
            $cart_goods[] = $data;
        }

        return $cart_goods;
    }


    public static function cartInfo ($rec_id){
        if (!empty($_SESSION['user_id'])) {
            $sess_id = " c.user_id = '" . $_SESSION['user_id'] . "' ";
        } else {
            $sess_id = " c.session_id = '" . real_cart_mac_ip() . "' ";
        }
        if (!empty($goods_id)) {
            $sess_id .= " AND c.goods_id = '$goods_id' ";
        }

        if (!empty($rec_id)) {
            $sess_id .= " AND c.rec_id = {$rec_id} ";
        }


        $sql = " SELECT DISTINCT ru_id FROM " . $GLOBALS['ecs']->table('wholesale_cart') . " AS c WHERE $sess_id ";
        $ru_ids = $GLOBALS['db']->getCol($sql);

        foreach ($ru_ids as $key => $val) {

            $sql = " SELECT DISTINCT goods_id FROM " . $GLOBALS['ecs']->table('wholesale_cart') . " AS c WHERE $sess_id AND c.ru_id = '$val' ";
            $goods_ids = $GLOBALS['db']->getCol($sql);
            $goods_id = $goods_ids[0];


            $sql = " SELECT c.rec_id, c.goods_price, c.goods_number, c.goods_attr_id " .
                " FROM " . $GLOBALS['ecs']->table('wholesale_cart') . " AS c " .
                " WHERE $sess_id AND c.ru_id = '$val' AND c.goods_id = '$goods_id' ORDER BY c.goods_attr_id"; //按属性序号排序
            $res = $GLOBALS['db']->getAll($sql);


            $total_number = 0;
            $total_price = 0;
            foreach ($res as $k => $v) {
                $res[$k]['goods_price_formatted'] = price_format($v['goods_price']);
                $res[$k]['total_price'] = $v['goods_price'] * $v['goods_number'];
                $res[$k]['total_price_formatted'] = price_format($res[$k]['total_price']);
                $res[$k]['goods_attr'] = get_goods_attr_array($v['goods_attr_id']);

                $total_number += $v['goods_number'];
                $total_price += $res[$k]['total_price'];
            }

        }

        if (empty($res)) {
            $list = [];
        }else{
            $list = [
                'rec_id' => $res[0]['rec_id'],
                'total_price' => $res[0]['total_price'],
                'total_price_formatted' => $res[0]['total_price_formatted'],
            ];
        }

        return $list;
    }


    public static function get_count_cart () {
        if (!empty($_SESSION['user_id'])) {
            $sess_id = " c.user_id = '" . $_SESSION['user_id'] . "' ";
        } else {
            $sess_id = " c.session_id = '" . real_cart_mac_ip() . "' ";
        }

        $sql = " SELECT SUM(goods_number) " .
            " FROM " . $GLOBALS['ecs']->table('wholesale_cart') . " AS c " .
            " WHERE $sess_id  ORDER BY c.goods_attr_id"; 
        $res = $GLOBALS['db']->getOne($sql);
        return $res;
    }


    public static function get_purchase_list($filter = [], $size = 10, $page = 1, $sort = "add_time", $order = "DESC") {

        if (isset($filter['user_id'])) {
            $where .= " AND user_id = '$filter[user_id]' ";
        }

        if (isset($filter['status'])) {
            $where .= " AND status = '$filter[status]' ";
        }

        if (isset($filter['review_status'])) {
            $where .= " AND review_status = '$filter[review_status]' ";
        }

        if (isset($filter['is_finished'])) {
            $now = gmtime();
            if ($filter['is_finished'] == 0) {
                $where .= " AND end_time > '$now' ";
            } elseif ($filter['is_finished'] == 1) {
                $where .= " AND end_time < '$now' ";
            }
        }

        if (isset($filter['keyword'])) {
            $where .= " AND subject LIKE '%$filter[keyword]%' ";
        }

        if (isset($filter['start_date'])) {
            $where .= " AND add_time > '$filter[start_date]' ";
        }

        if (isset($filter['end_date'])) {
            $where .= " AND add_time < '$filter[end_date]' ";
        }

        $sql = " SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('wholesale_purchase') . " WHERE 1 $where";
        $record_count = $GLOBALS['db']->getOne($sql);
        $page_count = $record_count > 0 ? ceil($record_count / $size) : 1;

        $sql = " SELECT * FROM " . $GLOBALS['ecs']->table('wholesale_purchase') . " WHERE 1 $where ORDER BY $sort $order ";

        $start = ($page - 1) * $size;
        $res = $GLOBALS['db']->selectLimit($sql, $size, $start);

        $arr = [];
        foreach ( $res as $row ) {
            $add_time = $row['add_time'];
            $end_time = $row['end_time'];
            $row['left_day'] = floor(($end_time - gmtime()) / 86400);
            $row['left_day'] = $row['left_day'] > 0 ? $row['left_day'] : 0;
            $row['add_time'] = local_date('Y-m-d', $add_time);
            $row['add_time_complete'] = local_date('Y-m-d H:i:s', $add_time);
            $row['end_time_complete'] = local_date('Y-m-d H:i:s', $end_time);

            $row['goods_number'] = get_table_date('wholesale_purchase_goods', "purchase_id = '" .$row['purchase_id']. "'", ['SUM(goods_number)'], 2);

            $sql = " SELECT goods_img FROM " . $GLOBALS['ecs']->table('wholesale_purchase_goods') . " WHERE purchase_id = '" .$row['purchase_id']. "' AND goods_img != '' ORDER BY goods_id ASC LIMIT 1 ";
            $goods_img = $GLOBALS['db']->getOne($sql);
            if ($goods_img) {
                $goods_img = unserialize($goods_img);
                $row['img'] = get_image_path(reset($goods_img));
            }

            $row['shop_name'] = get_shop_name($row['user_id'], 1);

            $row['is_verified'] = check_users_real($row['user_id'], 1);

            $row['area_info'] = get_seller_area_info($row['user_id']);
            $row['url'] = url('showdetail', ['id' => $row['purchase_id']]);
            $arr[] = $row;
        }
        return ['purchase_list' => $arr, 'page_count' => $page_count, 'record_count' => $record_count];
    }


    public static function get_purchase_info($purchase_id = 0) {
        $sql = " SELECT * FROM " . $GLOBALS['ecs']->table('wholesale_purchase') . " WHERE purchase_id = '$purchase_id' ";
        $purchase_info = $GLOBALS['db']->getRow($sql);

        if ($purchase_info) {
            $sql = " SELECT * FROM " . $GLOBALS['ecs']->table('wholesale_purchase_goods') . " WHERE purchase_id = '$purchase_id' ";
            $goods_list = $GLOBALS['db']->getAll($sql);
            foreach ($goods_list as $key => $val) {
                $goodsImage = unserialize($val['goods_img']);
                $goods_list[$key]['goods_img'] = get_image_path($goodsImage[0]);
                $cat_info = get_cat_info($val['cat_id'], ['cat_name'], 'wholesale_cat');
                $goods_list[$key]['cat_name'] = $cat_info['cat_name'];
            }
            $purchase_info['goods_list'] = $goods_list;
            $purchase_info['left_day'] = floor(($purchase_info['end_time'] - gmtime()) / 86400);
            $purchase_info['left_day'] = $purchase_info['left_day'] > 0 ? $purchase_info['left_day'] : 0;
            $purchase_info['user_name'] = get_table_date('users', "user_id = '$purchase_info[user_id]'", ['user_name'], 2);

            $purchase_info['shop_name'] = get_shop_name($purchase_info['user_id'], 1);

            $purchase_info['is_verified'] = check_users_real($purchase_info['user_id'], 1);

            $purchase_info['area_info'] = get_seller_area_info($purchase_info['user_id']);

            $purchase_info['consignee_region'] = get_every_region_name($purchase_info['consignee_region']);
            $purchase_info['consignee_address'] =$purchase_info['consignee_address'];
        }

        return $purchase_info;
    }


}

