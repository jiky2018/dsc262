<?php

use Illuminate\Database\Seeder;

/**
 * Class WeappModuleSeeder
 */
class WeappModuleSeeder extends Seeder
{

	/**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->touchAdPosition();
        $this->touchAd();
        $this->adminAction();
        $this->wxappTemplate();
    }


    private function touchAdPosition()
    {
		$result_1 = DB::table('touch_ad_position')->whereBetween('position_id', [1022, 1035])->get();
        $result_1 = $result_1->toArray();
		if (empty($result_1)) {

			// 插入新数据
            $rows = [
				[
                    'position_id' => '1022',
                    'position_name' => '小程序砍价首页banner',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}<div class="swiper-slide">{$ad}</div>{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '0',
                    'tc_type' => '',
                    'ad_type' => 'wxapp'
                ],
                [
                    'position_id' => '1023',
                    'position_name' => '小程序拼团首页banner广告位',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}<div class="swiper-slide">{$ad}</div>{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '0',
                    'tc_type' => '',
                    'ad_type' => 'wxapp'
                ],
                [
                    'position_id' => '1024',
                    'position_name' => '小程序拼团首页banner下广告位',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}<div class="box-flex activity-list">{$ad}</div>{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '0',
                    'tc_type' => '',
                    'ad_type' => 'wxapp'
                ],
                [
                    'position_id' => '1025',
                    'position_name' => '小程序拼团首页热门活动广告位-left',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}{$ad}{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '0',
                    'tc_type' => '',
                    'ad_type' => 'wxapp'
                ],
                [
                    'position_id' => '1026',
                    'position_name' => '小程序拼团首页热门活动广告位-right',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}{$ad}{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '0',
                    'tc_type' => '',
                    'ad_type' => 'wxapp'
                ],
                [
                    'position_id' => '1027',
                    'position_name' => '小程序拼团首页热门活动下广告位',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}<div class="swiper-slide">{$ad}</div>{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '0',
                    'tc_type' => '',
                    'ad_type' => 'wxapp'
                ],
                [
                    'position_id' => '1028',
                    'position_name' => '小程序拼团首页精选商品广告位-left',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}{$ad}{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '0',
                    'tc_type' => '',
                    'ad_type' => 'wxapp'
                ],
                [
                    'position_id' => '1029',
                    'position_name' => '小程序拼团首页精选商品广告位-right',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}{$ad}{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '0',
                    'tc_type' => '',
                    'ad_type' => 'wxapp'
                ],
				[
                    'position_id' => '1030',
                    'position_name' => '小程序生鲜-banner',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}<div class="swiper-slide">{$ad}</div>{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '1',
                    'tc_type' => 'banner',
					'ad_type' => 'wxapp'
                ],
                [
                    'position_id' => '1031',
                    'position_name' => '小程序生鲜-left',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}' . "\n" . '{$ad}' . "\n" . '{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '1',
                    'tc_type' => 'left',
					'ad_type' => 'wxapp'
                ],
                [
                    'position_id' => '1032',
                    'position_name' => '小程序生鲜-right',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}' . "\n" . '{$ad}' . "\n" . '{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '1',
                    'tc_type' => 'right',
					'ad_type' => 'wxapp'
                ],
                [
                    'position_id' => '1033',
                    'position_name' => '小程序服装-banner',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}<div class="swiper-slide">{$ad}</div>{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '2',
                    'tc_type' => 'banner',
					'ad_type' => 'wxapp'
                ],
                [
                    'position_id' => '1034',
                    'position_name' => '小程序服装-left',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}' . "\n" . '{$ad}' . "\n" . '{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '2',
                    'tc_type' => 'left',
					'ad_type' => 'wxapp'
                ],
                [
                    'position_id' => '1035',
                    'position_name' => '小程序服装-right',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}' . "\n" . '{$ad}' . "\n" . '{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '2',
                    'tc_type' => 'right',
					'ad_type' => 'wxapp'
                ]
            ];
            DB::table('touch_ad_position')->insert($rows);

		}


    }

	private function touchAd()
    {
		$result = DB::table('touch_ad')->whereBetween('position_id', [1022, 1035])->get();
        $result = $result->toArray();
        if (empty($result)) {
			// 插入新数据
            $rows = [
				[
                    'position_id' => '1022',
                    'media_type' => '0',
                    'ad_name' => '小程序砍价首页banner-01',
                    'ad_link' => '',
                    'ad_code' => '1509663779787829146.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
                [
                    'position_id' => '1023',
                    'media_type' => '0',
                    'ad_name' => '小程序拼团首页banner广告位01',
                    'ad_link' => '',
                    'ad_code' => '1507669848213147425.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
                [
                    'position_id' => '1023',
                    'media_type' => '0',
                    'ad_name' => '小程序拼团首页banner广告位02',
                    'ad_link' => '',
                    'ad_code' => '1507669879525605636.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
                [
                    'position_id' => '1024',
                    'media_type' => '0',
                    'ad_name' => '小程序拼团首页banner下广告位01',
                    'ad_link' => '',
                    'ad_code' => '1507669959435249903.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
                [
                    'position_id' => '1024',
                    'media_type' => '0',
                    'ad_name' => '小程序拼团首页banner下广告位02',
                    'ad_link' => '',
                    'ad_code' => '1507669938960465353.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
                [
                    'position_id' => '1025',
                    'media_type' => '0',
                    'ad_name' => '小程序拼团首页热门活动广告位-left',
                    'ad_link' => '',
                    'ad_code' => '1507670016399880574.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
                [
                    'position_id' => '1026',
                    'media_type' => '0',
                    'ad_name' => '小程序拼团首页热门活动广告位-right-01',
                    'ad_link' => '',
                    'ad_code' => '1507670031544851536.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
                [
                    'position_id' => '1026',
                    'media_type' => '0',
                    'ad_name' => '小程序拼团首页热门活动广告位-right-02',
                    'ad_link' => '',
                    'ad_code' => '1507670047986472913.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
                [
                    'position_id' => '1027',
                    'media_type' => '0',
                    'ad_name' => '小程序拼团首页热门活动下广告位01',
                    'ad_link' => '',
                    'ad_code' => '1507670062152040722.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
                [
                    'position_id' => '1027',
                    'media_type' => '0',
                    'ad_name' => '小程序拼团首页热门活动下广告位02',
                    'ad_link' => '',
                    'ad_code' => '1507670081958072850.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
                [
                    'position_id' => '1028',
                    'media_type' => '0',
                    'ad_name' => '小程序拼团首页精选商品广告位-left',
                    'ad_link' => '',
                    'ad_code' => '1507670101759733225.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
                [
                    'position_id' => '1029',
                    'media_type' => '0',
                    'ad_name' => '小程序拼团首页精选商品广告位-right-01',
                    'ad_link' => '',
                    'ad_code' => '1507670114514808085.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
				[
                    'position_id' => '1029',
                    'media_type' => '0',
                    'ad_name' => '小程序拼团首页精选商品广告位-right-02',
                    'ad_link' => '',
                    'ad_code' => '1507670125320849024.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
				[
                    'position_id' => '1030',
                    'media_type' => '0',
                    'ad_name' => '小程序生鲜-banner001',
                    'ad_link' => '',
                    'ad_code' => '1481672349255154283.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
				[
                    'position_id' => '1030',
                    'media_type' => '0',
                    'ad_name' => '小程序生鲜-banner002',
                    'ad_link' => '',
                    'ad_code' => '1481672451859296675.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
				[
                    'position_id' => '1031',
                    'media_type' => '0',
                    'ad_name' => '小程序生鲜-left-01',
                    'ad_link' => '',
                    'ad_code' => '1507669579034546413.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
				[
                    'position_id' => '1031',
                    'media_type' => '0',
                    'ad_name' => '小程序生鲜-left-02',
                    'ad_link' => '',
                    'ad_code' => '1507669560679177877.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
				[
                    'position_id' => '1032',
                    'media_type' => '0',
                    'ad_name' => '小程序生鲜-right001',
                    'ad_link' => '',
                    'ad_code' => '1481672619284617438.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
				[
                    'position_id' => '1032',
                    'media_type' => '0',
                    'ad_name' => '小程序生鲜-right002',
                    'ad_link' => '',
                    'ad_code' => '1481672758685877435.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
				[
                    'position_id' => '1033',
                    'media_type' => '0',
                    'ad_name' => '小程序服装-banner',
                    'ad_link' => '',
                    'ad_code' => '1481844261695460726.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
				[
                    'position_id' => '1034',
                    'media_type' => '0',
                    'ad_name' => '小程序服装-right01',
                    'ad_link' => '',
                    'ad_code' => '1481844097616183068.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
				[
                    'position_id' => '1034',
                    'media_type' => '0',
                    'ad_name' => '小程序服装-right02',
                    'ad_link' => '',
                    'ad_code' => '1481844124397414040.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
				[
                    'position_id' => '1035',
                    'media_type' => '0',
                    'ad_name' => '小程序服装-left1',
                    'ad_link' => '',
                    'ad_code' => '1507669757244479441.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
				[
                    'position_id' => '1035',
                    'media_type' => '0',
                    'ad_name' => '小程序服装-left2',
                    'ad_link' => '',
                    'ad_code' => '1507670479474615150.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ]
            ];
            DB::table('touch_ad')->insert($rows);

		}

	}


	private function adminAction()
    {
        $result = DB::table('admin_action')->where('action_code', 'wxapp')->get();
        $result = $result->toArray();
        if (empty($result)) {
            // 默认数据
            $row = [
                'parent_id' => 0,
                'action_code' => 'wxapp',
                'seller_show' => 0
            ];
            $action_id = DB::table('admin_action')->insertGetId($row);

            // 默认数据
            $rows = [
                [
                    'parent_id' => $action_id,
                    'action_code' => 'wxapp_wechat_config',
                    'seller_show' => 0
                ],
                [
                    'parent_id' => $action_id,
                    'action_code' => 'wxapp_template',
                    'seller_show' => 0
                ]
            ];
            DB::table('admin_action')->insert($rows);
        }

		$result_1 = DB::table('admin_action')->where('action_code', 'wxapp_config')->get();
        $result_1 = $result_1->toArray();
		if (!empty($result_1)) {
			// 删除旧数据
			DB::table('admin_action')->where('action_code', 'wxapp_config')->delete();
		}

    }

	private function wxappTemplate()
    {
        $result = DB::table('wxapp_template')->where('wx_code', 'AT0541')->first();
		$result_1 = DB::table('wxapp_template')->where('wx_code', 'AT1173')->first();
		$result_2 = DB::table('wxapp_template')->where('wx_code', 'AT0933')->first();
        if (empty($result) && empty($result_1) && empty($result_2)) {
            // 默认数据
            $rows = [
                [
                    'wx_wechat_id' => 1,
                    'wx_code' => 'AT0541',
                    'wx_template' => '产品名称 {{keyword1.DATA}} 成团人数 {{keyword2.DATA}} 截至时间 {{keyword3.DATA}} 拼团价 {{keyword4.DATA}}',
					'wx_keyword_id' => '6,3,5,10',
                    'wx_title' => '开团成功提醒'
                ],
				[
                    'wx_wechat_id' => 1,
                    'wx_code' => 'AT1173',
                    'wx_template' => '商品名称 {{keyword1.DATA}} 底价 {{keyword2.DATA}} 砍掉价格 {{keyword3.DATA}}',
					'wx_keyword_id' => '1,6,7',
                    'wx_title' => '砍价成功通知'
                ],
				[
                    'wx_wechat_id' => 1,
                    'wx_code' => 'AT0933',
                    'wx_template' => '商品名称 {{keyword1.DATA}} 拼团价 {{keyword2.DATA}} 截止时间 {{keyword3.DATA}}',
					'wx_keyword_id' => '3,4,7',
                    'wx_title' => '参团成功提醒'
                ]
            ];
            DB::table('wxapp_template')->insert($rows);
        }
    }





}