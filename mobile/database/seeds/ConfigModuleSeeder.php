<?php

use Illuminate\Database\Seeder;

class ConfigModuleSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->shopConfig();
    }

    private function shopConfig()
    {
        $result = DB::table('shop_config')->where('code', 'cashier_Settlement')->first();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'parent_id' => '938',
                    'code' => 'cashier_Settlement',
                    'type' => 'hidden',
                    'value' => '1'
                ]
            ];
            DB::table('shop_config')->insert($rows);
        }
    }
}