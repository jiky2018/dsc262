<?php

namespace App\Modules\Team\Controllers;

use App\Modules\Base\Controllers\FrontendController;
use App\Extensions\Scws4;

class IndexController extends FrontendController
{
    private $sort = 'last_update';
    private $order = 'ASC';

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        L(require(LANG_PATH . C('shop.lang') . '/team.php'));
        $files = array(
            'order',
            'clips',
            'payment',
            'transaction'
        );
        $this->load_helper($files);
        $this->user_id = $_SESSION['user_id'];
        $this->goods_id = I('id', 0, 'intval');
        $this->tc_id = I('request.tc_id', 0, 'intval');
        $this->page = 1;
        $this->size = 10;
    }

    /**
     * 拼团首页
     */
    public function actionIndex()
    {
        // 获取主频道
        $team_categories = team_categories();
        $this->assign('team_categories', $team_categories);

        // 验证首页下单提示轮播是否开启
        $sql = 'SELECT value FROM ' . $this->ecs->table('shop_config') . " WHERE code = 'virtual_order'";
        $is_open = $this->db->getRow($sql);
        if ($is_open['value'] == 1) {
            $this->assign('is_open', 1);
        }
        $this->assign('page_title', '全部');
        $this->display();
    }

     /**
     * 拼团频道
     */
    public function actionList()
    {
        $tc_id = I('tc_id', 0, 'intval');
        // 获取主频道
        $team_categories = team_categories();
        $this->assign('team_categories', $team_categories);
        if ($tc_id == 0) {
            foreach ($team_categories as $key => $var) {
                if ($key == 0) {
                    $tc_id = $var['id'];
                }
            }
        }
        // 获取子频道
        $this->assign('team_child', team_get_child_tree($tc_id));
        // 获取频道名称
        $shop = dao('team_category')->field('name')->where(array('id' => $tc_id))->find();

        // 验证首页下单提示轮播是否开启
        $sql = 'SELECT value FROM ' . $this->ecs->table('shop_config') . " WHERE code = 'virtual_order'";
        $is_open = $this->db->getRow($sql);
        if ($is_open['value'] == 1) {
            $this->assign('is_open', 1);
        }
        $this->assign('tc_id', $tc_id);
        $this->assign('page_title', $shop['name']);
        $this->display();
    }

    /**
     * ajax拼团频道商品列表
     */
    public function actionTeamlist()
    {
        $this->page = I('page', 1, 'intval');
        $tc_id = I('request.tc_id', 0, 'intval');
        if (IS_AJAX) {
            $where .= " g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.review_status>2 and tg.is_team = 1 and tg.is_audit = 2 ";
            if ($tc_id > 0) {
                $one = dao('team_category')->field('id')->where('id =' . $tc_id . ' or parent_id=' . $tc_id)->select();
                if ($one) {
                    foreach ($one as $key) {
                        $one_id[] = $key['id'];
                    }
                    $tc_id = implode(',', $one_id);
                    $where .= " and tg.tc_id in ($tc_id) ";
                }
            }
            $arr = array();
            $sql = 'SELECT g.goods_id,g.user_id, g.goods_name, g.shop_price, g.goods_name_style, g.comments_number, g.sales_volume, g.market_price, g.goods_thumb , g.goods_img, tg.team_price, tg.team_num FROM ' . $this->ecs->table('team_goods') . 'AS tg LEFT JOIN ' . $this->ecs->table('goods') . ' AS g ON tg.goods_id = g.goods_id ' .
                "WHERE $where ";
            $goods_list = $this->db->query($sql);
            $total = is_array($goods_list) ? count($goods_list) : 0;
            $res = $this->db->selectLimit($sql, $this->size, ($this->page - 1) * $this->size);
            foreach ($res as $key => $val) {
                $arr[$key]['goods_id'] = $val['goods_id'];
                $arr[$key]['user_id'] = $val['user_id'];
                $arr[$key]['goods_name'] = $val['goods_name'];
                $arr[$key]['shop_price'] = price_format($val['shop_price']);
                $arr[$key]['goods_img'] = get_image_path($val['goods_img']);
                $arr[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
                $arr[$key]['url'] = url('team/goods/index', array('id' => $val['goods_id']));
                $arr[$key]['team_price'] = price_format($val['team_price']);
                $arr[$key]['team_num'] = $val['team_num'];
            }

            exit(json_encode(array('list' => array_values($arr), 'totalPage' => ceil($total / $this->size))));
        }
    }

    //首页下单提示轮播
    public function actionvirtualorder()
    {
        //格式化返回数组
        $arr = array(
            'err_msg' => '',
            'name' => '',
            'avatar' => '',
            'seconds' => ''
        );
        $sql = 'SELECT value FROM ' . $this->ecs->table('shop_config') . " WHERE code = 'virtual_order'";
        $is_open = $this->db->getRow($sql);
        if ($is_open['value'] == 1) {
            $_SESSION['user_id'] = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $sql = 'SELECT user_name, user_id FROM ' . $this->ecs->table('users') . " WHERE user_id <> " . $_SESSION['user_id'] . " ORDER BY rand() LIMIT 1";
            $user = $this->db->getRow($sql);
            if ($user) {

                //用户名、头像
                $user_nick = get_user_default($user['user_id']);
                $arr['name'] = encrypt_username($user_nick['nick_name']);
                $arr['avatar'] = $user_nick['user_picture'];

                //随机秒数
                $arr['seconds'] = rand(1, 8) . "秒前";
            } else {
                $arr ['err_no'] = 1;
            }
        } else {
            $arr ['err_no'] = 1;
        }
        die(json_encode($arr));
    }

    /**
     * 拼团子频道商品列表
     */
    public function actionCategory()
    {
        $this->init_params();
        if (IS_AJAX) {
            $goods_list = team_category_goods($this->tc_id, $this->keywords, $this->size, $this->page, $this->intro, $this->sort, $this->order, $this->brand, $this->price_min, $this->price_max);
            exit(json_encode(array('list' => $goods_list['list'], 'totalPage' => $goods_list['totalpage'])));
        }
        // 获取频道名称
        if ($this->tc_id > 0) {
            $shop = dao('team_category')->field('name')->where(array('id' => $this->tc_id))->find();
            $this->assign('page_title', $shop['name']);
        } else {
            $this->assign('page_title', L('team_keywords_result'));
        }

        $this->display();
    }


    /**
     * 拼团排行
     */
    public function actionUserranking()
    {
        $this->page = I('page', 1, 'intval');
        $type = isset($_REQUEST ['type']) ? $_REQUEST ['type'] : 'limit_num';
        if (IS_AJAX) {
            $goods_list = team_goods($this->size, $this->page, $type);
            exit(json_encode(array('list' => $goods_list['list'], 'totalPage' => $goods_list['totalpage'])));
        }
        $this->assign('type', $type);
        $this->assign('page_title', L('ranking_list'));
        $this->display();
    }

    /**
     * 过滤参数
     */
    private function init_params()
    {
        //$page_size = C('shop.page_size');
        //关键词查询
        $keyword = I('request.keyword');
        if (!empty($keyword)) {
            //分词搜索
            $scws = new Scws4();
            $keyword_segmentation = $scws->segmentate($keyword, true);

            $keywordArr = explode(',', $keyword_segmentation);

            $this->keywords = 'AND (';
            $addAll = array();
            foreach ($keywordArr as $keywordKey => $keywordVal) {
                if ($keywordKey > 0) {
                    $this->keywords .= ' AND ';
                }

                $val = mysql_like_quote(trim($keywordVal));
                $this->keywords .= "(g.goods_name LIKE '%$val%' OR g.goods_sn LIKE '%$val%' OR g.keywords LIKE '%$val%')";

                //
                $valArr[] = $val;
                /**
                 * 处理关键字查询次数
                 */
                $data = array('date' => local_date('Y-m-d'), 'searchengine' => 'ECTouch', 'keyword' => addslashes(str_replace('%', '', $val)), 'count' => 1);
                $condition['date'] = local_date('Y-m-d');
                $condition['searchengine'] = 'ECTouch';
                $condition['keyword'] = addslashes(str_replace('%', '', $val));
                $set = $this->db->table('keywords')->where($condition)->find();
                //
                if (!empty($set)) {
                    $data['count'] = $set['count'] + 1;
                }
                $addAll[] = $data;
            }
            //处理关键字查询次数
            $this->db->addAll($addAll, array('table' => $this->ecs->table('keywords')), true);
            //
            $this->keywords .= ')';

            $goods_ids = array();
            $valArrWhere = ' 1';
            foreach ($valArr as $v) {
                $valArrWhere .= " OR tag_words LIKE '%$v%' ";
            }

            /*记录搜索历史记录*/
            $history = '';
            if (!empty($_COOKIE['ECS']['keywords'])) {
                $history = explode(',', $_COOKIE['ECS']['keywords']);
                array_unshift($history, $keyword); //在数组开头插入一个或多个元素
                $history = array_unique($history);  //移除数组中的重复的值，并返回结果数组。
                cookie('ECS[keywords]', implode(',', $history));
            } else {
                cookie('ECS[keywords]', $keyword);
            }
            $this->assign('history_keywords', $history);
        }
        //属性查询
        $filter_attr_str = I('request.filter_attr', 0);
        if ($filter_attr_str) {
            $filter_attr_str = trim(urldecode($filter_attr_str));
            $filter_attr_str = preg_match('/^[\d,\.,\-,\,]+$/', $filter_attr_str) ? $filter_attr_str : '';
            $filter_attr_reset = explode('.', $filter_attr_str);
            if ($filter_attr_reset) {
                foreach ($filter_attr_reset as $k => $v) {
                    $tmp_attr = explode('-', $v);
                    $this->filter_attr[$tmp_attr[0]] = $tmp_attr[1];
                }
            }
        }

        $this->size = 10;
        $asyn_last = I('request.last', 0, 'intval') + 1;
        $this->page = I('request.page', 1, 'intval');
        $this->brand = I('request.brand', 0, 'intval');
        $this->intro = I('request.intro');
        $this->price_min = I('request.price_min', 0, 'intval');
        $this->price_max = I('request.price_max', 0, 'intval');
        $this->isself = I('request.isself', 0, 'intval');
        $this->hasgoods = I('request.hasgoods', 0, 'intval');
        $this->promotion = I('request.promotion', 0, 'intval');

        /* 排序、显示方式以及类型 */
        $default_display_type = C('shop.show_order_type') == '0' ? 'list' : (C('shop.show_order_type') == '1' ? 'grid' : 'text');
        $default_sort_order_type = C('shop.sort_order_type') == '0' ? 'goods_id' : (C('shop.sort_order_type') == '1' ? 'team_price' : 'last_update');
        $default_sort_order_method = C('shop.sort_order_method') == '0' ? 'desc' : 'asc';
        $sort_array = array('goods_id', 'team_price', 'last_update', 'sales_volume');
        $order_array = array('asc', 'desc');
        $display_array = array('list', 'grid', 'text');
        $goods_sort = I('request.sort');
        $goods_order = I('request.order');
        $goods_display = I('request.display');
        $this->sort = in_array($goods_sort, $sort_array) ? $goods_sort : $default_sort_order_type;
        $this->order = in_array($goods_order, $order_array) ? $goods_order : $default_sort_order_method;
        $this->display = in_array($goods_display, $display_array) ? $goods_display : (isset($_COOKIE['ECS']['display']) ? $_COOKIE['ECS']['display'] : $default_display_type);
        cookie('ECS[display]', $this->display);
        //ecmoban模板堂 --zhuo start
        $sql = "select parent_id from " . $this->ecs->table('category') . " where cat_id = '$this->cat_id'";
        $parent_id = $this->db->getOne($sql);
        $sql = "select parent_id from " . $this->ecs->table('category') . " where cat_id = '$parent_id'";
        $parentCat = $this->db->getOne($sql);

        $province_id = isset($_COOKIE['province']) ? $_COOKIE['province'] : 0;
        $area_info = get_area_info($province_id);
        $this->area_id = $area_info['region_id'];

        $where = "regionId = '$province_id'";
        $date = array('parent_id');
        $this->region_id = get_table_date('region_warehouse', $where, $date, 2);

        if (isset($_COOKIE['region_id']) && !empty($_COOKIE['region_id'])) {
            $this->region_id = $_COOKIE['region_id'];
        }
        //ecmoban模板堂 --zhuo end
        //$this->children = get_children($this->cat_id);
        // 获得分类的相关信息
        //$this->cat = get_cat_info($this->cat_id);
        /* 获取价格分级 */
        if ($this->cat['grade'] == 0 && $this->cat['parent_id'] != 0) {
            $this->cat['grade'] = get_parent_grade($this->cat_id); //如果当前分类级别为空，取最近的上级分类
        }
        //ecmoban模板堂 --zhuo start
        $leftJoin = '';

        $tag_where = '';
        if (C('shop.open_area_goods') == 1) { //关联地区显示商品
            $leftJoin .= " left join " . $this->ecs->table('link_area_goods') . " as lag on g.goods_id = lag.goods_id ";
            $tag_where = " and lag.region_id = '$this->area_id' ";
        }

        //ecmoban模板堂 --zhuo end
        if ($this->cat['grade'] > 1) {
            /* 需要价格分级 */

            /*
                算法思路：
                    1、当分级大于1时，进行价格分级
                    2、取出该类下商品价格的最大值、最小值
                    3、根据商品价格的最大值来计算商品价格的分级数量级：
                            价格范围(不含最大值)    分级数量级
                            0-0.1                   0.001
                            0.1-1                   0.01
                            1-10                    0.1
                            10-100                  1
                            100-1000                10
                            1000-10000              100
                    4、计算价格跨度：
                            取整((最大值-最小值) / (价格分级数) / 数量级) * 数量级
                    5、根据价格跨度计算价格范围区间
                    6、查询数据库

                可能存在问题：
                    1、
                    由于价格跨度是由最大值、最小值计算出来的
                    然后再通过价格跨度来确定显示时的价格范围区间
                    所以可能会存在价格分级数量不正确的问题
                    该问题没有证明
                    2、
                    当价格=最大值时，分级会多出来，已被证明存在
             */

            //ecmoban模板堂 --zhuo start
            $mm_shop_price = "wg.warehouse_price, wg.warehouse_promote_price, wag.region_price, wag.region_promote_price, g.model_price, g.model_attr ";
            $leftJoin .= " left join " . $this->ecs->table('warehouse_goods') . " as wg on g.goods_id = wg.goods_id and wg.region_id = '$region_id' ";
            $leftJoin .= " left join " . $this->ecs->table('warehouse_area_goods') . " as wag on g.goods_id = wag.goods_id and wag.region_id = '$this->area_id' ";
            //ecmoban模板堂 --zhuo end

            $sql = "SELECT min(IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price))) AS min, " .
                " max(IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price))) as max " .
                " FROM " . $this->ecs->table('goods') . " AS g " .
                $leftJoin .
                " WHERE ($this->children OR " . get_extension_goods($this->children) . ') AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1' . $tag_where;
            //获得当前分类下商品价格的最大值、最小值
            $row = $this->db->getRow($sql);

            // 取得价格分级最小单位级数，比如，千元商品最小以100为级数
            $price_grade = 0.0001;
            for ($i = -2; $i <= log10($row['max']); $i++) {
                $price_grade *= 10;
            }

            //跨度
            $dx = ceil(($row['max'] - $row['min']) / ($this->cat['grade']) / $price_grade) * $price_grade;
            if ($dx == 0) {
                $dx = $price_grade;
            }

            for ($i = 1; $row['min'] > $dx * $i; $i++) ;

            for ($j = 1; $row['min'] > $dx * ($i - 1) + $price_grade * $j; $j++) ;
            $row['min'] = $dx * ($i - 1) + $price_grade * ($j - 1);

            for (; $row['max'] >= $dx * $i; $i++) ;
            $row['max'] = $dx * ($i) + $price_grade * ($j - 1);

            $sql = "SELECT (FLOOR((IF(g.model_price < 1, g.shop_price, IF(g.model_price < 2, wg.warehouse_price, wag.region_price)) - $row[min]) / $dx)) AS sn, COUNT(*) AS goods_num  " .
                " FROM " . $this->ecs->table('goods') . " AS g " . $leftJoin .
                " WHERE ($this->children OR " . get_extension_goods($this->children) . ') AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1' .
                " GROUP BY sn ";

            $price_grade = $this->db->getAll($sql);
            foreach ($price_grade as $key => $val) {
                if ($val['sn'] != '') {
                    $temp_key = $key;
                    $price_grade[$temp_key]['goods_num'] = $val['goods_num'];
                    $price_grade[$temp_key]['start'] = $row['min'] + round($dx * $val['sn']);
                    $price_grade[$temp_key]['end'] = $row['min'] + round($dx * ($val['sn'] + 1));
                    $price_grade[$temp_key]['price_range'] = $price_grade[$temp_key]['start'] . '&nbsp;-&nbsp;' . $price_grade[$temp_key]['end'];
                    $price_grade[$temp_key]['formated_start'] = price_format($price_grade[$temp_key]['start']);
                    $price_grade[$temp_key]['formated_end'] = price_format($price_grade[$temp_key]['end']);
                    $price_grade[$temp_key]['url'] = build_uri('category', array('id' => $this->cat_id, 'bid' => $this->brand, 'price_min' => $price_grade[$temp_key]['start'], 'price_max' => $price_grade[$temp_key]['end'], 'filter_attr' => $filter_attr_str), $this->cat['cat_name']);

                    /* 判断价格区间是否被选中 */
                    if (isset($_REQUEST['price_min']) && $price_grade[$temp_key]['start'] == $this->price_min && $price_grade[$temp_key]['end'] == $this->price_max) {
                        $price_grade[$temp_key]['selected'] = 1;
                    } else {
                        $price_grade[$temp_key]['selected'] = 0;
                    }
                }
            }

            //价格分级
            $this->assign('price_grade', $price_grade);
        }
        if (empty($row)) {
            $row['min'] = 0;
            $row['max'] = 10000;
        }
        //最大最小值范围
        $this->assign('price_range', $row);

        $brand_tag_where = '';
        $brand_leftJoin = '';
        if (C('shop.open_area_goods') == 1) {
            //关联地区显示商品
            $brand_select = " , ( SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('link_area_goods') . " as lag WHERE lag.goods_id = g.goods_id AND lag.region_id = '$this->area_id' LIMIT 1) AS area_goods_num ";
            $where_having = " AND area_goods_num > 0 ";
        }

        if (C('shop.review_goods') == 1) {
            $brand_tag_where .= ' AND g.review_status > 2 ';
        }
        /* 平台品牌筛选 */
        $sql = "SELECT b.brand_id, b.brand_name, b.brand_logo, COUNT(*) AS goods_num " . $brand_select .
            "FROM " . $GLOBALS['ecs']->table('brand') . "AS b " .
            " LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON g.brand_id = b.brand_id AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 $brand_tag_where " .
            " LEFT JOIN " . $GLOBALS['ecs']->table('team_goods') . " AS tg ON g.goods_id = tg.goods_id " .
            " WHERE tg.tc_id in ($this->tc_id)  AND b.is_show = 1 " .
            "GROUP BY b.brand_id HAVING goods_num > 0 $where_having ORDER BY b.sort_order, b.brand_id ASC";
        $brands = $GLOBALS['db']->getAll($sql);

        $brands_selected = explode(',', $this->brand);
        foreach ($brands as $key => $val) {
            $temp_key = $key + 1;
            $brands[$temp_key]['brand_id'] = $val['brand_id']; // 同步绑定品牌名称和品牌ID
            $brands[$temp_key]['brand_name'] = $val['brand_name'];
            $brands[$temp_key]['url'] = url('products', array(
                'id' => $this->cat_id,
                'bid' => $val['brand_id'],
                'price_min' => $this->price_min,
                'price_max' => $this->price_max,
                'filter_attr' => $this->filter_attr
            ));

            /* 判断品牌是否被选中 */
            if (in_array($val['brand_id'], $brands_selected)) {             // 修正当前品牌的ID
                $brands[$temp_key]['selected'] = 1;
            } else {
                $brands[$temp_key]['selected'] = 0;
            }
        }
        unset($brands[0]); // 清空索引为0的项目
        $brands[0]['brand_id'] = 0; // 新增默认值
        $brands[0]['brand_name'] = L('all_attribute');
        $brands[0]['url'] = url('products', array(
            'cid' => $this->cat_id,
            'bid' => 0,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'filter_attr' => $this->filter_attr
        ));
        $brands[0]['selected'] = empty($this->brand) ? 1 : 0;

        ksort($brands);
        $this->assign('brands', $brands);
        if (!empty($this->brand)) {
            $sql = "SELECT brand_name FROM " . $this->ecs->table('brand') . " WHERE brand_id in($this->brand)";
            $brand_name_arr = $this->db->getCol($sql);
            $brand_name = implode('、', $brand_name_arr);
        } else {
            $brand_name = L('all_attribute');
        }
        $this->assign('brand_name', $brand_name);
        $this->ubrand = I('request.ubrand', 0, 'intval');
        $this->assign('ubrand', $this->ubrand);

        /* 属性筛选 */
        $this->ext = ''; // 商品查询条件扩展
        if ($this->cat['filter_attr'] > 0) {
            // 提取出此分类的筛选属性
            $this->cat_filter_attr = explode(',', $this->cat['filter_attr']);
            $all_attr_list = array();
            foreach ($this->cat_filter_attr as $key => $value) {
                $sql = "SELECT a.attr_name, attr_cat_type FROM " . $this->ecs->table('attribute') . " AS a, " . $this->ecs->table('goods_attr') . " AS ga left join  " . $this->ecs->table('goods') . " AS g on g.goods_id = ga.goods_id " . $leftJoin . " WHERE ($this->children OR " . get_extension_goods($this->children) . ") AND a.attr_id = ga.attr_id AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND a.attr_id='$value'" . $tag_where;
                $attributeInfo = $this->db->getRow($sql);
                if ($attributeInfo) {
                    $all_attr_list[$key]['filter_attr_name'] = $attributeInfo['attr_name'];
                    $all_attr_list[$key]['attr_cat_type'] = $attributeInfo['attr_cat_type'];

                    $all_attr_list[$key]['filter_attr_id'] = $value; //by zhang

                    $sql = "SELECT a.attr_id, MIN(a.goods_attr_id ) AS goods_id, a.attr_value AS attr_value, a.color_value FROM " . $this->ecs->table('goods_attr') . " AS a, " . $this->ecs->table('goods') .
                        " AS g" .
                        " WHERE ($this->children OR " . get_extension_goods($this->children) . ') AND g.goods_id = a.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 ' .
                        " AND a.attr_id='$value' " .
                        " GROUP BY a.attr_value";

                    $attr_list = $this->db->getAll($sql);

                    $temp_arrt_url_arr = array();
                    //获取当前url中已选择属性的值，并保留在数组中
                    for ($i = 0; $i < count($this->cat_filter_attr); $i++) {
                        $temp_arrt_url_arr[$i] = !empty($this->filter_attr[$i]) ? $this->filter_attr[$i] : 0;
                    }

                    // “全部”的信息生成
                    $temp_arrt_url_arr[$key] = 0;
                    $temp_arrt_url = implode('.', $temp_arrt_url_arr);
                    // 默认数值
                    $all_attr_list[$key]['attr_list'][0]['attr_id'] = 0;
                    $all_attr_list[$key]['attr_list'][0]['attr_value'] = L('all_attribute');
                    $all_attr_list[$key]['attr_list'][0]['url'] = url('products', array(
                        'id' => $this->cat_id,
                        'bid' => $this->brand,
                        'price_min' => $this->price_min,
                        'price_max' => $this->price_max,
                        'filter_attr' => $temp_arrt_url
                    ));
                    $all_attr_list[$key]['attr_list'][0]['selected'] = empty($this->filter_attr[$key]) ? 1 : 0;
                    $all_attr_list[$key]['select_attr_name'] = L('all_attribute');

                    foreach ($attr_list as $k => $v) {
                        $temp_key = $k + 1;
                        // 为url中代表当前筛选属性的位置变量赋值,并生成以‘.’分隔的筛选属性字符串
                        $temp_arrt_url_arr[$key] = $v['goods_id'];
                        $temp_arrt_url = implode('.', $temp_arrt_url_arr);

                        $all_attr_list[$key]['attr_list'][$temp_key]['attr_id'] = $v['goods_id']; // 新增属性参数
                        $all_attr_list[$key]['attr_list'][$temp_key]['attr_value'] = $v['attr_value'];
                        $all_attr_list[$key]['attr_list'][$temp_key]['url'] = url('products', array(
                            'id' => $this->cat_id,
                            'bid' => $this->brand,
                            'price_min' => $this->price_min,
                            'price_max' => $this->price_max,
                            'filter_attr' => $temp_arrt_url
                        ));

                        if (!empty($this->filter_attr[$key]) && $this->filter_attr[$key] == $v['goods_id']) {
                            $all_attr_list[$key]['attr_list'][$temp_key]['selected'] = 1;
                            $all_attr_list[$key]['select_attr_name'] = $v['attr_value'];
                        } else {
                            $all_attr_list[$key]['attr_list'][$temp_key]['selected'] = 0;
                        }
                    }
                }
            }

            $this->assign('filter_attr_list', $all_attr_list);

            // 扩展商品查询条件
            if (!empty($this->filter_attr)) {
                $ext_sql = "SELECT DISTINCT(b.goods_id) as dis FROM " . $this->ecs->table('goods_attr') . " AS a, " . $this->ecs->table('goods_attr') . " AS b " . "WHERE ";
                $ext_group_goods = array();
                // 查出符合所有筛选属性条件的商品id
                foreach ($this->filter_attr as $k => $v) {
                    unset($ext_group_goods);
                    if (!empty($v) && isset($this->cat_filter_attr[$k])) {
                        $sql = $ext_sql . "b.attr_value = a.attr_value AND b.attr_id = " . $this->cat_filter_attr[$k] . " AND a.goods_attr_id in ($v)";
                        $res = $this->db->query($sql);
                        foreach ($res as $value) {
                            $ext_group_goods[] = $value['dis'];
                        }
                        $this->ext .= ' AND ' . db_create_in($ext_group_goods, 'g.goods_id');
                    }
                }
            }
        }

        //自营商品
        if ($this->isself) {
            $this->ext .= ' AND g.user_id = 0';
        }
        $this->assign('show_marketprice', C('shop.show_marketprice'));
        $this->assign('category', $this->tc_id);
        $this->assign('brand_id', $this->brand);
        $this->assign('price_min', $this->price_min);
        $this->assign('price_max', $this->price_max);
        $this->assign('isself', $this->isself);
        $this->assign('filter_attr', $filter_attr_str);
        $this->assign('parent_id', $parent_id);
        $this->assign('parentCat', $parentCat);
        $this->assign('region_id', $this->region_id);
        $this->assign('area_id', $this->area_id);
        $this->assign('page', $this->page);
        $this->assign('size', $this->size);
        $this->assign('sort', $this->sort);
        $this->assign('order', $this->order);
        $this->assign('keywords', $keyword);
        $this->assign('intro', $this->intro);
        $this->assign('hasgoods', $this->hasgoods);
        $this->assign('promotion', $this->promotion);
    }
}
