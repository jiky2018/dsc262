<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTouchAdPositionTableAdType extends Migration
{
    protected $tableName = 'touch_ad_position';
    /**
     * 运行数据库迁移
     *
     * @return void
     */
    public function up()
    {
        // 判断字段是否存在添加        
        if (!Schema::hasColumn($this->tableName, 'ad_type')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->string('ad_type')->default('')->comment('广告位所属');
            });
        }
    }

    /**
     * 回滚数据库迁移
     *
     * @return void
     */
    public function down()
    {
        // 删除字段
        if (Schema::hasColumn($this->tableName, 'ad_type')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->dropColumn('ad_type');
            });
        }
    }
}
