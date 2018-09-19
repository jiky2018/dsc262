<?php

use Illuminate\Database\Seeder;

class TeamModuleSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->shopConfig();
        $this->teamCategory();
        $this->touchAdPosition();
        $this->touchAd();
        $this->touchNav();
        $this->adminAction();
        $this->wechatTemplate();
    }

    private function shopConfig()
    {
        $result = DB::table('shop_config')->where('code', 'virtual_order')->first();
        $result_1 = DB::table('shop_config')->where('code', 'virtual_limit_nim')->first();
        if (empty($result) && empty($result_1)) {
            // 默认数据
            $rows = [
                [
                    'parent_id' => '2',
                    'code' => 'virtual_order',
                    'type' => 'select',
                    'store_range' => '0,1',
                    'sort_order' => '1',
                ],
                [
                    'parent_id' => '2',
                    'code' => 'virtual_limit_nim',
                    'type' => 'select',
                    'store_range' => '0,1',
                    'sort_order' => '1',
                ]
            ];
            DB::table('shop_config')->insert($rows);
        }
    }

    private function teamCategory()
    {
        $result = DB::table('team_category')->get();
        $result = $result->toArray();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'name' => '生鲜',
                    'parent_id' => '0',
                    'tc_img' => '',
                ],
                [
                    'name' => '服装',
                    'parent_id' => '0',
                    'tc_img' => '',
                ],
                [
                    'name' => '美妆',
                    'parent_id' => '0',
                    'tc_img' => '',
                ],
                [
                    'name' => '母婴',
                    'parent_id' => '0',
                    'tc_img' => '',
                ],
                [
                    'name' => '数码',
                    'parent_id' => '0',
                    'tc_img' => '',
                ],
                [
                    'name' => '电器',
                    'parent_id' => '0',
                    'tc_img' => '',
                ],
                [
                    'name' => '水果',
                    'parent_id' => '1',
                    'tc_img' => 'data/team_img/1477003670262146374.jpg',
                ],
                [
                    'name' => '海鲜',
                    'parent_id' => '1',
                    'tc_img' => 'data/team_img/1477003723558440986.jpg',
                ],
                [
                    'name' => '蔬菜',
                    'parent_id' => '1',
                    'tc_img' => 'data/team_img/1477003737543093730.jpg',
                ],
                [
                    'name' => '肉类',
                    'parent_id' => '1',
                    'tc_img' => 'data/team_img/1477003765554186648.jpg',
                ],
                [
                    'name' => '半身裙',
                    'parent_id' => '2',
                    'tc_img' => 'data/team_img/1476238976644478183.jpg',
                ],
                [
                    'name' => '小衫',
                    'parent_id' => '2',
                    'tc_img' => 'data/team_img/1476238818094280453.jpg',
                ],
                [
                    'name' => '裤子',
                    'parent_id' => '2',
                    'tc_img' => 'data/team_img/1476238847669867996.jpg',
                ],
                [
                    'name' => '套装',
                    'parent_id' => '2',
                    'tc_img' => 'data/team_img/1476238872975750020.jpg',
                ],
                [
                    'name' => '天然面膜',
                    'parent_id' => '3',
                    'tc_img' => '',
                ],
                [
                    'name' => '唇彩口红',
                    'parent_id' => '3',

                    'tc_img' => '',
                ],
                [
                    'name' => '保湿面乳',
                    'parent_id' => '3',

                    'tc_img' => '',
                ],
                [
                    'name' => '时尚香水',
                    'parent_id' => '3',

                    'tc_img' => '',
                ],
                [
                    'name' => '婴儿起居',
                    'parent_id' => '4',
                    'tc_img' => '',
                ],
                [
                    'name' => '妈咪护理',
                    'parent_id' => '4',
                    'tc_img' => '',
                ],
                [
                    'name' => '婴儿洗护',
                    'parent_id' => '4',
                    'tc_img' => '',
                ],
                [
                    'name' => '智力开发',
                    'parent_id' => '4',
                    'tc_img' => '',
                ],
                [
                    'name' => '数码相机',
                    'parent_id' => '5',
                    'tc_img' => '',
                ],
                [
                    'name' => '电脑配件',
                    'parent_id' => '5',
                    'tc_img' => '',
                ],
                [
                    'name' => '智能设备',
                    'parent_id' => '5',
                    'tc_img' => '',
                ],
                [
                    'name' => '智能配件',
                    'parent_id' => '5',
                    'tc_img' => '',
                ],
                [
                    'name' => '厨房电器',
                    'parent_id' => '6',
                    'tc_img' => '',
                ],
                [
                    'name' => '生活电器',
                    'parent_id' => '6',
                    'tc_img' => '',
                ],
                [
                    'name' => '个人护理',
                    'parent_id' => '6',
                    'tc_img' => '',
                ],
                [
                    'name' => '影音电器',
                    'parent_id' => '6',
                    'tc_img' => '',
                ]
            ];
            DB::table('team_category')->insert($rows);
        }
    }

    private function touchAdPosition()
    {
        $result = DB::table('touch_ad_position')->whereBetween('position_id', [1000, 1007])->get();
        $result = $result->toArray();
        $result_index = DB::table('touch_ad_position')->whereBetween('position_id', [1008, 1014])->get();
        $result_index = $result_index->toArray();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'position_id' => '1000',
                    'user_id' => '0',
                    'position_name' => '生鲜-banner',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}<div class="swiper-slide">{$ad}</div>{/foreach}' . "\n" . '',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '1',
                    'tc_type' => 'banner',
                ],
                [
                    'position_id' => '1001',
                    'user_id' => '0',
                    'position_name' => '生鲜-left',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}' . "\n" . '{$ad}' . "\n" . '{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '1',
                    'tc_type' => 'left',
                ],
                [
                    'position_id' => '1002',
                    'user_id' => '0',
                    'position_name' => '生鲜-right',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}' . "\n" . '{$ad}' . "\n" . '{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '1',
                    'tc_type' => 'right',
                ],
                [
                    'position_id' => '1003',
                    'user_id' => '0',
                    'position_name' => '生鲜-bottom',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}' . "\n" . '{$ad}' . "\n" . '{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '1',
                    'tc_type' => 'bottom',
                ],
                [
                    'position_id' => '1004',
                    'user_id' => '0',
                    'position_name' => '服装-banner',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}<div class="swiper-slide">{$ad}</div>{/foreach}' . "\n" . '',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '1',
                    'tc_type' => 'banner',
                ],
                [
                    'position_id' => '1005',
                    'user_id' => '0',
                    'position_name' => '服装-left',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}' . "\n" . '{$ad}' . "\n" . '{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '1',
                    'tc_type' => 'left',
                ],
                [
                    'position_id' => '1006',
                    'user_id' => '0',
                    'position_name' => '服装-right',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}' . "\n" . '{$ad}' . "\n" . '{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '1',
                    'tc_type' => 'right',
                ],
                [
                    'position_id' => '1007',
                    'user_id' => '0',
                    'position_name' => '服装-bottom',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}' . "\n" . '{$ad}' . "\n" . '{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '1',
                    'tc_type' => 'bottom',
                ]
            ];
            DB::table('touch_ad_position')->insert($rows);
        }
		
        if (empty($result_index)) {
            // 插入新数据
            $rows = [
                [
                    'position_id' => '1008',
                    'user_id' => '0',
                    'position_name' => '拼团首页banner广告位',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}<div class="swiper-slide">{$ad}</div>{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                ],
                [
                    'position_id' => '1009',
                    'user_id' => '0',
                    'position_name' => '拼团首页banner下广告位',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}<div class="box-flex activity-list">{$ad}</div>{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                ],
                [
                    'position_id' => '1010',
                    'user_id' => '0',
                    'position_name' => '拼团首页热门活动广告位-left',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}{$ad}{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                ],
                [
                    'position_id' => '1011',
                    'user_id' => '0',
                    'position_name' => '拼团首页热门活动广告位-right',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}{$ad}{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                ],
                [
                    'position_id' => '1012',
                    'user_id' => '0',
                    'position_name' => '拼团首页热门活动下广告位',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}<div class="swiper-slide">{$ad}</div>{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                ],
                [
                    'position_id' => '1013',
                    'user_id' => '0',
                    'position_name' => '拼团首页精选商品广告位-left',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}{$ad}{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                ],
                [
                    'position_id' => '1014',
                    'user_id' => '0',
                    'position_name' => '拼团首页精选商品广告位-right',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}{$ad}{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                ]
            ];
            DB::table('touch_ad_position')->insert($rows);
        }
    }

    private function touchAd()
    {
        $result = DB::table('touch_ad')->whereBetween('position_id', [1000, 1007])->get();
        $result = $result->toArray();
		$result_index = DB::table('touch_ad')->whereBetween('position_id', [1008, 1014])->get();
        $result_index = $result_index->toArray();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'position_id' => '1000',
                    'media_type' => '0',
                    'ad_name' => '生鲜-banner001',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1481672349255154283.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1000',
                    'media_type' => '0',
                    'ad_name' => '生鲜-banner002',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1481672451859296675.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1001',
                    'media_type' => '0',
                    'ad_name' => '生鲜-left',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1481672545602467804.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1002',
                    'media_type' => '0',
                    'ad_name' => '生鲜-right001',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1481672619284617438.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1002',
                    'media_type' => '0',
                    'ad_name' => '生鲜-right002',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1481672758685877435.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1003',
                    'media_type' => '0',
                    'ad_name' => '生鲜-bottom',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1481679017761272361.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1004',
                    'media_type' => '0',
                    'ad_name' => '服装-banner',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1481844261695460726.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1005',
                    'media_type' => '0',
                    'ad_name' => '服装-left',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1481844211194785256.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1006',
                    'media_type' => '0',
                    'ad_name' => '服装-right01',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1481844097616183068.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1006',
                    'media_type' => '0',
                    'ad_name' => '服装-right02',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1481844124397414040.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1007',
                    'media_type' => '0',
                    'ad_name' => '服装-bottom',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1481844007547575973.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ]
            ];
            DB::table('touch_ad')->insert($rows);
        }
		if (empty($result_index)) {
			// 删除旧数据
			DB::table('touch_ad')->whereIn('position_id', [1001,1005])->delete();
			
		}
		
		if (empty($result_index)) {
            // 插入新数据
            $rows = [
				[
                    'position_id' => '1001',
                    'media_type' => '0',
                    'ad_name' => '生鲜-left-01',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507669579034546413.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1001',
                    'media_type' => '0',
                    'ad_name' => '生鲜-left-02',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507669560679177877.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1005',
                    'media_type' => '0',
                    'ad_name' => '服装-left1',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507669757244479441.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1005',
                    'media_type' => '0',
                    'ad_name' => '服装-left2',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507670479474615150.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1008',
                    'media_type' => '0',
                    'ad_name' => '拼团首页banner广告位01',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507669848213147425.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1008',
                    'media_type' => '0',
                    'ad_name' => '拼团首页banner广告位02',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507669879525605636.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1009',
                    'media_type' => '0',
                    'ad_name' => '拼团首页banner下广告位01',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507669959435249903.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1009',
                    'media_type' => '0',
                    'ad_name' => '拼团首页banner下广告位02',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507669938960465353.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1010',
                    'media_type' => '0',
                    'ad_name' => '拼团首页热门活动广告位-left',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507670016399880574.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1011',
                    'media_type' => '0',
                    'ad_name' => '拼团首页热门活动广告位-right-01',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507670031544851536.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1011',
                    'media_type' => '0',
                    'ad_name' => '拼团首页热门活动广告位-right-02',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507670047986472913.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1012',
                    'media_type' => '0',
                    'ad_name' => '拼团首页热门活动下广告位01',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507670062152040722.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1012',
                    'media_type' => '0',
                    'ad_name' => '拼团首页热门活动下广告位02',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507670081958072850.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1013',
                    'media_type' => '0',
                    'ad_name' => '拼团首页精选商品广告位-left',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507670101759733225.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
                [
                    'position_id' => '1014',
                    'media_type' => '0',
                    'ad_name' => '拼团首页精选商品广告位-right-01',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507670114514808085.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ],
				[
                    'position_id' => '1014',
                    'media_type' => '0',
                    'ad_name' => '拼团首页精选商品广告位-right-02',
                    'ad_link' => '',
                    'link_color' => '',
                    'ad_code' => '1507670125320849024.jpg',
                    'start_time' => '1481585927',
                    'end_time' => '1577229853',
                    'link_man' => '',
                    'link_email' => '',
                    'link_phone' => '',
                    'click_count' => '0',
                    'enabled' => '1',
                    'is_new' => '0',
                    'is_hot' => '0',
                    'is_best' => '0',
                    'public_ruid' => '0',
                    'ad_type' => '0',
                    'goods_name' => '0',
                ]
            ];
            DB::table('touch_ad')->insert($rows);
        }
    }

    private function touchNav()
    {
        $result = DB::table('touch_nav')->where('name', '拼团')->get();
        $result = $result->toArray();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'ctype' => '',
                    'cid' => '0',
                    'name' => '拼团',
                    'ifshow' => '1',
                    'vieworder' => '11',
                    'opennew' => '',
                    'url' => 'index.php?m=team',
                    'pic' => 'pintuan.png',
                    'type' => 'top',
                ]
            ];
            DB::table('touch_nav')->insert($rows);
        }
    }

    private function adminAction()
    {
        $result = DB::table('admin_action')->where('action_code', 'team_manage')->get();
        $result = $result->toArray();
        if (empty($result)) {

            // 默认数据
            $rows = [
                [
                    'parent_id' => '7',
                    'action_code' => 'team_manage'
                ]
            ];
            DB::table('admin_action')->insert($rows);
        }

        $result1 = DB::table('admin_action')->where('action_code', 'team')->first();
        if(!empty($result1)){
            // 删除旧数据
            DB::table('admin_action')->where('action_id', $result1->action_id)->delete();
            DB::table('admin_action')->where('parent_id', $result1->action_id)->delete();
        }



    }

    private function wechatTemplate()
    {
        $result = DB::table('wechat_template')->where('code', 'OPENTM407307456')->first();
        $result_1 = DB::table('wechat_template')->where('code', 'OPENTM407307456')->first();
        $result_2 = DB::table('wechat_template')->where('code', 'OPENTM407307456')->first();
        $result_3 = DB::table('wechat_template')->where('code', 'OPENTM407307456')->first();
        if (empty($result) && empty($result_1) && empty($result_2) && empty($result_3)) {
            // 默认数据
            $rows = [
                [
                    'wechat_id' => 1,
                    'code' => 'OPENTM407307456',
                    'template' => '{{first.DATA}}\r\n商品名称：{{keyword1.DATA}}\r\n商品价格：{{keyword2.DATA}}\r\n组团人数：{{keyword3.DATA}}\r\n拼团类型：{{keyword4.DATA}}\r\n组团时间：{{keyword5.DATA}}\r\n{{remark.DATA}}',
                    'title' => '开团成功通知'
                ],
                [
                    'wechat_id' => 1,
                    'code' => 'OPENTM400048581',
                    'template' => '{{first.DATA}}\r\n拼团名：{{keyword1.DATA}}\r\n拼团价：{{keyword2.DATA}}\r\n有效期：{{keyword3.DATA}}\r\n{{remark.DATA}}',
                    'title' => '参团成功通知'
                ],
                [
                    'wechat_id' => 1,
                    'code' => 'OPENTM407456411',
                    'template' => '{{first.DATA}}\r\n订单编号：{{keyword1.DATA}}\r\n团购商品：{{keyword2.DATA}}\r\n{{remark.DATA}}',
                    'title' => '拼团成功通知'
                ],
                [
                    'wechat_id' => 1,
                    'code' => 'OPENTM400940587',
                    'template' => '{{first.DATA}}\r\n单号：{{keyword1.DATA}}\r\n商品：{{keyword2.DATA}}\r\n原因：{{keyword3.DATA}}\r\n退款：{{keyword4.DATA}}\r\n{{remark.DATA}}',
                    'title' => '拼团退款通知'
                ]
            ];
            DB::table('wechat_template')->insert($rows);
        }
    }

}