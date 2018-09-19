<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeWechatUserTableSubscribes extends Migration
{
    protected $tableName = 'wechat_user';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 判断字段是否存在 添加
        if (!Schema::hasColumn($this->tableName, 'subscribe_scene')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->string('subscribe_scene')->default('')->comment('用户关注的渠道来源');
            });
        }
        if (!Schema::hasColumn($this->tableName, 'qr_scene')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->unsignedInteger('qr_scene')->default(0)->comment('二维码扫码场景');
            });
        }
        if (!Schema::hasColumn($this->tableName, 'qr_scene_str')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->string('qr_scene_str')->default('')->comment('二维码扫码场景描述');
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
        if (Schema::hasColumn($this->tableName, 'subscribe_scene')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->dropColumn('subscribe_scene');
            });
        }
        if (Schema::hasColumn($this->tableName, 'qr_scene')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->dropColumn('qr_scene');
            });
        }
        if (Schema::hasColumn($this->tableName, 'qr_scene_str')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->dropColumn('qr_scene_str');
            });
        }
    }
}
