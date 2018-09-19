<?php

namespace App\Modules\Bargain\Controllers;

use App\Modules\Base\Controllers\BackendController;

class AdminController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        L(require(LANG_PATH . C('shop.lang') . '/team.php'));
        $this->assign('lang', array_change_key_case(L()));
        $files = [
            'order',
            'clips',
            'payment',
            'transaction',
            'ecmoban'
        ];
        $this->load_helper($files);

        $this->init_params();
    }

    private function init_params()
    {
        if (IS_POST) {

            $page_num = I('page_num', 0, 'intval');
            if ($page_num > 0) {
                cookie('ECSCP[page_size]', $page_num);
                exit(json_encode(['status' => 1]));
            }
        }

        $this->page_num = isset($_COOKIE['ECSCP']['page_size']) && !empty($_COOKIE['ECSCP']['page_size']) ? $_COOKIE['ECSCP']['page_size'] : 10;
        $this->assign('page_num', $this->page_num);
    }

    public function actionIndex()
    {
        $this->admin_priv('bargain_manage');
        $where .= " g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.review_status>2 and bg.is_delete = 0 ";
        $goods_name = I('post.keyword');
        $audit = I('is_audit', 3, 'intval');
        if (IS_POST) {
            if (!empty($goods_name)) {
                $where .= " AND (g.goods_name LIKE '%$goods_name%' OR g.goods_sn LIKE '%$goods_name%' OR g.keywords LIKE '%$goods_name%')";
            }
            if (!empty($audit)) {
                if ($audit == 3) {
                    $where .= " ";
                } else {
                    $where .= " AND bg.is_audit = $audit";
                }
            } else {
                $where .= " AND bg.is_audit = 0";
            }
        }
        $sql_count = "SELECT count(*) as count FROM {pre}bargain_goods as bg LEFT JOIN {pre}goods as g ON bg.goods_id = g.goods_id WHERE  " . $where . " ORDER BY bg.id DESC";
        $total = $this->model->getOne($sql_count);

        $offset = $this->pageLimit(url('index'), $this->page_num);
        $this->assign('page', $this->pageShow($total));


        $sql = "SELECT bg.*,g.user_id,g.goods_sn,g.goods_name,g.shop_price,g.goods_number,g.goods_img,g.goods_thumb FROM {pre}bargain_goods as bg LEFT JOIN {pre}goods as g ON bg.goods_id = g.goods_id WHERE " . $where . " ORDER BY bg.id DESC LIMIT " . $offset;
        $list = $this->model->query($sql);
        $time = gmtime();
        foreach ($list as $key => $val) {
            $list[$key]['goods_name'] = $val['goods_name'];
            $list[$key]['user_name'] = get_shop_name($val['user_id'], 1);
            $list[$key]['shop_price'] = price_format($val['shop_price']);
            $target_price = get_bargain_target_price($val['id']);
            if($target_price){
                $list[$key]['target_price'] = price_format($target_price);
            }else{
                $list[$key]['target_price'] = price_format($val['target_price']);
            }
            $list[$key]['goods_number'] = $val['goods_number'];
            $list[$key]['sales_volume'] = $val['sales_volume'];
            $list[$key]['goods_img'] = get_image_path($val['goods_img']);
            $list[$key]['goods_thumb'] = get_image_path($val['goods_thumb']);
            $list[$key]['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['start_time']);
            $list[$key]['end_time'] = local_date('Y-m-d H:i:s', $val['end_time']);

            if ($time >= $val['end_time']) {
                $status = '活动已过期';
            } else {
                $status = '活动进行中';
            }
            $list[$key]['is_status'] = $status;
            $list[$key]['status'] = $val['status'];
            if ($val['is_audit'] == 1) {
                $is_audit = '等待审核';
            } elseif ($val['is_audit'] == 2) {
                $is_audit = '审核已通过';
            } else {
                $is_audit = '未审核';
            }
            $list[$key]['is_audit'] = $is_audit;
        }
        $this->assign('audit', $audit);
        $this->assign('list', $list);

        $this->display();
    }

    public function actionAddgoods()
    {
        $this->admin_priv('bargain_manage');
        if (IS_POST) {
            $data = I('post.data');
            $data['start_time'] = local_strtotime($data['start_time']);
            $data['end_time'] = local_strtotime($data['end_time']);
            $data['add_time'] = gmtime();
            $id = I('id', '', 'intval');
            $target_price = I('target_price', '', '');
            $product_id = I('product_id', '', '');
            $activity_goods_attr = I('bargain_id', '', '');


            if (!$id) {
                $count = $this->model->table('bargain_goods')->where(['goods_id' => $data['goods_id'],'status' => '1'])->count();
                if ($count >= 1) {
                    exit(json_encode(['status' => 'n', 'info' => '该砍价商品活动结束之前，不可添加新的活动']));
                }

                if ($data['min_price'] > $data['max_price']) {
                    exit(json_encode(['status' => 'n', 'info' => '价格区间最小值不能大于最大值']));
                }

                $bargain_id = $this->model->table('bargain_goods')->data($data)->add();

                if ($bargain_id) {
                    foreach($product_id as $key => $value)
                    {
                        $attr_data['bargain_id'] = $bargain_id;
                        $attr_data['goods_id'] = $data['goods_id'];
                        $attr_data['product_id'] = $value;
                        $attr_data['target_price'] = $target_price[$key];
                        $attr_data['type'] = 'bargain';
                        $this->model->table('activity_goods_attr')->data($attr_data)->add();
                    }
                    exit(json_encode(['status' => 'y', 'info' => '添加成功', 'url' => url('index')]));
                } else {
                    exit(json_encode(['status' => 'n', 'info' => '添加失败']));
                }
            } else {
                if ($data['is_audit'] != 1) {
                    $data['isnot_aduit_reason'] = '';
                }
                if ($data['min_price'] > $data['max_price']) {
                    exit(json_encode(['status' => 'n', 'info' => '价格区间最小值不能大于最大值']));
                }
                $bargain = $this->model->table('bargain_goods')->data($data)->where(['id' => $id])->save();

                if ($bargain) {

                    foreach($product_id as $key => $value)
                    {
                        $attr_data['target_price'] = $target_price[$key];
                        $this->model->table('activity_goods_attr')->data($attr_data)->where(['id' => $activity_goods_attr[$key],'goods_id' => $data['goods_id'],'product_id' =>$value  ])->save();
                    }

                    exit(json_encode(['status' => 'y', 'info' => '修改成功', 'url' => url('index')]));
                } else {
                    exit(json_encode(['status' => 'n', 'info' => '修改失败']));
                }
            }
        }
        $nowtime = gmtime();
        $info = [];
        if (I('id')) {
            $id = I('id', '', 'intval');
            $info = $this->model->table('bargain_goods')->where(['id' => $id])->find();
            $goods = $this->model->table('goods')->field('goods_name,user_id')->where(['goods_id' => $info['goods_id']])->find();
            $info['goods_name'] = $goods['goods_name'];
            $info['ru_id'] = $goods['user_id'];
            $info['start_time'] = isset($info['start_time']) ? local_date('Y-m-d H:i:s', $info['start_time']) : local_date('Y-m-d H:i:s', $nowtime);
            $info['end_time'] = isset($info['end_time']) ? local_date('Y-m-d H:i:s', $info['end_time']) : local_date('Y-m-d H:i:s', local_strtotime("+1 months", $nowtime));
            $this->assign('info', $info);
        }else{

            $info = [
            'start_time'    => local_date('Y-m-d H:i:s', $nowtime),
            'end_time'      => local_date('Y-m-d H:i:s', local_strtotime("+1 months", $nowtime)),
            'min_price'      => 0,
            'max_price'      =>10
        ];
            $this->assign('info', $info);
        }

        set_default_filter(); 

        $this->display();
    }

    public function actionSearchgoods()
    {
        $this->cat_id = I('cat_id', 0, 'intval');
        $this->brand_id = I('brand_id', 0, 'intval');
        $this->keywords = I('request.keyword');
        $where = "  g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.review_status>2 and g.user_id =0 ";
        $where .= isset($this->cat_id) && $this->cat_id > 0 ? ' AND ' . get_children($this->cat_id) : '';
        $where .= isset($this->brand_id) && $this->brand_id > 0 ? " AND brand_id = '" . $this->brand_id . "'" : '';
        $where .= isset($this->keywords) && trim($this->keywords) != '' ?
            " AND (g.goods_name LIKE '%$this->keywords%' OR g.goods_sn LIKE '%$this->keywords%' OR g.keywords LIKE '%$this->keywords%')" : '';

        $sql = "SELECT goods_id, goods_name, shop_price FROM {pre}goods as g  where $where ";

        $row = $GLOBALS['db']->getAll($sql);
        exit(json_encode(['content' => array_values($row)]));
    }


    public function actionGoodsinfo()
    {
        $goods_id = I('goods_id', 0, 'intval');
        $where = "  g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.review_status>2 and g.goods_id = $goods_id ";

        $sql = "SELECT shop_price,goods_type,model_attr FROM {pre}goods as g  where $where ";
        $row = $GLOBALS['db']->getRow($sql);
        $goods_type =$row['goods_type'];
        $goods_model =$row['model_attr'];

        $sql = " SELECT a.attr_id, a.attr_name, a.attr_input_type, a.attr_type, a.attr_values " .
                " FROM " . $GLOBALS['ecs']->table('attribute') . " AS a " .
                " WHERE a.cat_id = " . intval($goods_type) . " AND a.cat_id <> 0 " .
                " ORDER BY a.sort_order, a.attr_type, a.attr_id ";
        $attribute_list = $GLOBALS['db']->getAll($sql);

        $sql = " SELECT v.goods_attr_id, v.attr_id, v.attr_value, v.attr_price, v.attr_sort, v.attr_checked, v.attr_img_flie, v.attr_gallery_flie  " .
                " FROM " . $GLOBALS['ecs']->table('goods_attr') . " AS v " .
                " WHERE v.goods_id = '$goods_id' ORDER BY v.attr_sort, v.goods_attr_id ";
        $attr_list = $GLOBALS['db']->getAll($sql);

        foreach ($attribute_list as $key => $val) {
            $is_selected = 0; 
            $this_value = ""; 

            if ($val['attr_type'] > 0) {
                if($val['attr_values']){
                    $attr_values = preg_replace("/\r\n/", ",", $val['attr_values']); 
                    $attr_values = explode(',', $attr_values);
                }else{
                    $sql = "SELECT attr_value FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE goods_id = '$goods_id' AND attr_id = '" . $val['attr_id'] . "' ORDER BY attr_sort, goods_attr_id";
                    $attr_values = $GLOBALS['db']->getAll($sql);
                    $attribute_list[$key]['attr_values'] = get_attr_values_arr($attr_values);
                    $attr_values = $attribute_list[$key]['attr_values'];
                }

                $attr_values_arr = [];
                for ($i = 0; $i < count($attr_values); $i++) {
                    $goods_attr = $GLOBALS['db']->getRow("SELECT goods_attr_id, attr_price, attr_sort FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE goods_id = '$goods_id' AND attr_value = '" . $attr_values[$i] . "' AND attr_id = '" . $val['attr_id'] . "' LIMIT 1");
                    $attr_values_arr[$i] = ['is_selected' => 0, 'goods_attr_id' => $goods_attr['goods_attr_id'], 'attr_value' => $attr_values[$i], 'attr_price' => $goods_attr['attr_price'], 'attr_sort' => $goods_attr['attr_sort']];
                }
                $attribute_list[$key]['attr_values_arr'] = $attr_values_arr;
            }

            foreach ($attr_list as $k => $v) {
                if ($val['attr_id'] == $v['attr_id']) {
                    $is_selected = 1;

                    if ($val['attr_type'] == 0) {
                        $this_value = $v['attr_value'];
                    } else {
                        foreach ($attribute_list[$key]['attr_values_arr'] as $a => $b) {
                            if ($goods_id) {
                                if ($b['attr_value'] == $v['attr_value']) {
                                    $attribute_list[$key]['attr_values_arr'][$a]['is_selected'] = 1;
                                }
                            } else {
                                if ($b['attr_value'] == $v['attr_value']) {
                                    $attribute_list[$key]['attr_values_arr'][$a]['is_selected'] = 1;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            $attribute_list[$key]['is_selected'] = $is_selected;
            $attribute_list[$key]['this_value'] = $this_value;
            if ($val['attr_input_type'] == 1) {
                $attribute_list[$key]['attr_values'] = preg_split('/\r\n/', $val['attr_values']);
            }
        }
        $attribute_list = get_new_goods_attr($attribute_list);

        $GLOBALS['smarty']->assign('goods_id', $goods_id);
        $GLOBALS['smarty']->assign('goods_model', $goods_model);
        $GLOBALS['smarty']->assign('attribute_list', $attribute_list['spec']);
        $goods_attribute = $this->fetch('bargain_goods_attribute');

        $result['goods_attribute'] = $goods_attribute;
        $result['goods_id'] = $goods_id;
        $result['shop_price'] = $row['shop_price'];

        exit(json_encode($result));
    }

    public function actionSetattributetable()
    {
        $bargain_id = empty($_REQUEST['bargain_id']) ? 0 : intval($_REQUEST['bargain_id']);
        $goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
        $goods_type = empty($_REQUEST['goods_type']) ? 0 : intval($_REQUEST['goods_type']);
        $attr_id_arr = empty($_REQUEST['attr_id']) ? [] : $_REQUEST['attr_id'];
        $attr_value_arr = empty($_REQUEST['attr_value']) ? [] : $_REQUEST['attr_value'];
        $goods_model = empty($_REQUEST['goods_model']) ? 0 : intval($_REQUEST['goods_model']); 
        $region_id = empty($_REQUEST['region_id']) ? 0 : intval($_REQUEST['region_id']);
        $result = ['error' => 0, 'message' => '', 'content' => ''];

        $group_attr = [
            'goods_id' => $goods_id,
            'goods_type' => $goods_type,
            'attr_id' => empty($attr_id_arr) ? '' : implode(',', $attr_id_arr),
            'attr_value' => empty($attr_value_arr) ? '' : implode(',', $attr_value_arr),
            'goods_model' => $goods_model,
            'region_id' => $region_id,
        ];

        if ($goods_model == 0) {
            $model_name = "";
        } elseif ($goods_model == 1) {
            $model_name = "仓库";
        } elseif ($goods_model == 2) {
            $model_name = "地区";
        }
        $region_name = $GLOBALS['db']->getOne(" SELECT region_name FROM " . $GLOBALS['ecs']->table('region_warehouse') . " WHERE region_id ='$region_id' ");
        $GLOBALS['smarty']->assign('region_name', $region_name);
        $GLOBALS['smarty']->assign('goods_model', $goods_model);
        $GLOBALS['smarty']->assign('model_name', $model_name);

        $goods_info = $GLOBALS['db']->getRow(" SELECT market_price, shop_price, model_attr FROM " . $GLOBALS['ecs']->table("goods") . " WHERE goods_id = '$goods_id' ");
        $GLOBALS['smarty']->assign('goods_info', $goods_info);

        foreach ($attr_id_arr as $key => $val) {
            $attr_arr[$val][] = $attr_value_arr[$key];
        }
        $attr_spec = [];
        $attribute_array = [];

        if (count($attr_arr) > 0) {

            $i = 0;
            foreach ($attr_arr as $key => $val) {

                $sql = "SELECT attr_name, attr_type FROM " . $GLOBALS['ecs']->table('attribute') . " WHERE attr_id ='$key' LIMIT 1";
                $attr_info = $GLOBALS['db']->getRow($sql);

                $attribute_array[$i]['attr_id'] = $key;
                $attribute_array[$i]['attr_name'] = $attr_info['attr_name'];
                $attribute_array[$i]['attr_value'] = $val;

                $attr_values_arr = [];
                foreach ($val as $k => $v) {
                    $data = bargain_get_goods_attr_id(['attr_id' => $key, 'attr_value' => $v, 'goods_id' => $goods_id], ['ga.*, a.attr_type'], [1, 2], 1);

                    $data['attr_id'] = $key;
                    $data['attr_value'] = $v;
                    $data['is_selected'] = 1;
                    $attr_values_arr[] = $data;
                }

                $attr_spec[$i] = $attribute_array[$i];
                $attr_spec[$i]['attr_values_arr'] = $attr_values_arr;

                $attribute_array[$i]['attr_values_arr'] = $attr_values_arr;

                if($attr_info['attr_type'] == 2){
                    unset($attribute_array[$i]);
                }

                $i++;
            }


            $new_attribute_array = [];
            foreach($attribute_array as $key=>$val){
                $new_attribute_array[] = $val;
            }
            $attribute_array = $new_attribute_array;

            $attr_arr = get_goods_unset_attr($goods_id, $attr_arr);

            if (count($attr_arr) == 1) {
                foreach (reset($attr_arr) as $key => $val) {
                    $attr_group[][] = $val;
                }
            } else {
                $attr_group = attr_group($attr_arr);
            }


            foreach ($attr_group as $key => $val) {
                $group = [];


                $product_info = get_product_info_by_attr($bargain_id,$goods_id, $val, $goods_model, $region_id);
                if (!empty($product_info)) {
                    $group = $product_info;
                }

                foreach ($val as $k => $v) {
                    if($v){
                        $group['attr_info'][$k]['attr_id'] = $attribute_array[$k]['attr_id'];
                        $group['attr_info'][$k]['attr_value'] = $v;
                    }
                }

                if($group){
                    $attr_group[$key] = $group;
                }else{
                    $attr_group = [];
                }
            }

            $GLOBALS['smarty']->assign('attr_group', $attr_group);
            $GLOBALS['smarty']->assign('attribute_array', $attribute_array);
        }

        $GLOBALS['smarty']->assign('group_attr', $result['group_attr']);
        $GLOBALS['smarty']->assign('goods_attr_price', $GLOBALS['_CFG']['goods_attr_price']);

        $GLOBALS['smarty']->assign('goods_id', $goods_id);
        $GLOBALS['smarty']->assign('goods_type', $goods_type);

        $result['content'] = $this->fetch('attribute_table');


        die(json_encode($result));

    }

    public function actionFiltercategory()
    {
        $cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
        $result = ['error' => 0, 'message' => '', 'content' => ''];


        $parent_cat_list = get_select_category($cat_id, 1, true);
        $filter_category_navigation = get_array_category_info($parent_cat_list);
        $cat_nav = "";
        if ($filter_category_navigation) {
            foreach ($filter_category_navigation as $key => $val) {
                if ($key == 0) {
                    $cat_nav .= $val['cat_name'];
                } elseif ($key > 0) {
                    $cat_nav .= " > " . $val['cat_name'];
                }
            }
        } else {
            $cat_nav = "请选择分类";
        }
        $result['cat_nav'] = $cat_nav;

        $cat_level = count($parent_cat_list);
        if ($cat_level <= 3) {
            $filter_category_list = get_category_list($cat_id, 2);
        } else {
            $filter_category_list = get_category_list($cat_id, 0);
            $cat_level -= 1;
        }
        $this->assign('filter_category_level', $cat_level); 
        $this->assign('filter_category_navigation', $filter_category_navigation);
        $this->assign('filter_category_list', $filter_category_list);
        $result['content'] = $this->fetch('filter_bargain_category');

        exit(json_encode($result));
    }


    public function actionSearchbrand()
    {
        $goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
        $result = ['error' => 0, 'message' => '', 'content' => ''];
        $this->assign('filter_brand_list', search_brand_list($goods_id));
        $result['content'] = $this->fetch('bargain_brand_list');
        exit(json_encode($result));
    }


    public function actionEditgoods()
    {
        $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
        $result = [
            'error' => 0,
            'message' => '',
            'content' => '修改失败'
        ];
        $sql = "SELECT is_hot FROM {pre}bargain_goods WHERE  id = $id";
        $res = $GLOBALS['db']->getOne($sql);
        if ($res == 1) {
            $sql = "UPDATE {pre}bargain_goods SET is_hot = 0 WHERE id = $id ";
        } else {
            $sql = "UPDATE {pre}bargain_goods SET is_hot = 1 WHERE id = $id ";
        }
        if ($this->model->query($sql)) {
            $result = [
                'error' => 2,
                'message' => '',
                'content' => '修改成功'
            ];
        }
        exit(json_encode($result));
    }


    public function actionRemovegoods()
    {
        $this->admin_priv('bargain_manage');

        $id = I('id', null, 'intval');
        $type = I('type', null, '');
        if (empty($id)) {
            $this->message(L('select_shop'), null, 2);
        }
        if($type == 'status'){
            $sql = 'UPDATE {pre}bargain_goods' . " SET status = 1 " . " WHERE id = $id ";
            $this->model->query($sql);
        }else{
            $sql = 'UPDATE {pre}bargain_goods' . " SET is_delete = 1 " . " WHERE id = $id ";
            $this->model->query($sql);
        }
        $this->redirect('index');

    }



    public function actionBargainlog()
    {
        $this->admin_priv('bargain_manage');
        $bargain_id = I('bargain_id', 1, 'intval');
        $status = I('status', 1, 'intval');

        switch ($status) {
            case '1':
                $where .= "";
                break;
            case '2':
                $where .= " and bs.status != 1 and '" . gmtime() . "'>= bg.start_time and '" . gmtime() . "'<= bg.end_time ";//活动进行中
                break;
            case '3':
                $where .= " and bs.status = 1 ";
                break;
            case '4':
                $where .=  " and bs.status != 1 and '" . gmtime() . "'> bg.end_time ";
                break;
            default:
                $where .= '';
        }

        $sql_count = "select count(*) as count from {pre}bargain_statistics_log as bs LEFT JOIN {pre}bargain_goods as bg ON bs.bargain_id =  bg.id where bg.id ='".$bargain_id."' $where ";
        $total = $this->model->getOne($sql_count);
        $offset = $this->pageLimit(url('teaminfo'), $this->page_num);
        $this->assign('page', $this->pageShow($total));

        $sql = "select bg.goods_id,bg.target_price,bg.start_time,bg.end_time,bs.id,bs.bargain_id,bs.goods_attr_id, bs.user_id,bs.final_price,bs.add_time,bs.count_num,bs.status from {pre}bargain_statistics_log as bs LEFT JOIN {pre}bargain_goods as bg ON  bs.bargain_id = bg.id  where bg.id ='".$bargain_id."'
        $where order by bs.add_time desc LIMIT " . $offset;
        $list = $this->model->query($sql);
        $time =  gmtime();
        foreach ($list as $key => $val) {
            $list[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['add_time']);

            if($val['goods_attr_id']){
                $spec = explode(",", $val['goods_attr_id']);
                $target_price = bargain_target_price($val['bargain_id'],$val['goods_id'],$spec);
                $list[$key]['target_price'] =  price_format($target_price);
            }else{
                $list[$key]['target_price'] = price_format($val['target_price']);
            }

            $list[$key]['final_price'] = price_format($val['final_price']);
            $user_nick = get_user_default($val['user_id']);

            $list[$key]['user_name'] = $user_nick['nick_name'];
            $list[$key]['count_num'] = $val['count_num'];

            if ($val['status'] == 1 ) {
                $list[$key]['status'] = '活动成功';
            } elseif($val['status'] != 1 and $time >= $val['start_time'] and $time <= $val['end_time'] ) {
                $list[$key]['status'] = '活动进行中';
            }elseif($val['status'] != 1 and $time > $val['end_time']) {
                $list[$key]['status'] = '活动失败';
            }

        }

        $this->assign('bargain_id', $bargain_id);
        $this->assign('list', $list);
        $this->assign('status', $status);

        $this->display();
    }

    public function actionBargainStatistics()
    {
        $this->admin_priv('bargain_manage');

        $id = I('id', 0, 'intval');

        $sql = "select * from {pre}bargain_statistics  where bs_id = $id order by add_time desc  " ;
        $list = $this->model->query($sql);
        foreach ($list as $key => $val) {
            $list[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['add_time']);
            $list[$key]['subtract_price'] = price_format($val['subtract_price']);
            $user_nick = get_user_default($val['user_id']);
            $list[$key]['user_name'] = $user_nick['nick_name'];
        }
        $this->assign('list', $list);
        $this->display();
    }



















}
