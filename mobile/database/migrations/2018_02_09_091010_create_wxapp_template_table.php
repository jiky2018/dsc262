<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWxappTemplateTable extends Migration
{
    protected $tableName = 'wxapp_template';

    /**
     * 运行数据库迁移
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tableName)) {
            Schema::create($this->tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('wx_wechat_id')->index()->default(0)->comment('公众号id');
                $table->string('wx_template_id')->default('')->comment('小程序模板id');
                $table->string('wx_code')->default('')->comment('小程序模板消息标识');
                $table->string('wx_content')->default('')->comment('小程序自定义备注');
                $table->text('wx_template')->nullable()->default('')->comment('小程序模板消息模板');
                $table->string('wx_keyword_id')->default('')->comment('小程序关键词id');
                $table->string('wx_title')->default('')->comment('小程序模板消息标题');
                $table->unsignedInteger('add_time')->default(0)->comment('添加时间');
                $table->unsignedTinyInteger('status')->default(0)->comment('启用状态 0 禁止 1 开启');
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
        if (Schema::hasTable($this->tableName)) {
            Schema::drop($this->tableName);
        }
    }
}
