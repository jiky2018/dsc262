<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCouponsUserTable extends Migration
{

    protected $tableName = 'coupons_user';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 判断字段是否存在 添加
        if (!Schema::hasColumn($this->tableName, 'cou_money')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->decimal('cou_money',10,2)->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 还原字段
        if (Schema::hasColumn($this->tableName, 'cou_money')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->dropColumn('cou_money');
            });
        }
    }
}
