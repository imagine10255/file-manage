<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SystemFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($table = 'system_files', function (Blueprint $table) {
            $table->increments('id')->comment('檔案ID');
            $table->string('related_table', 64)->comment('關聯資料表');
            $table->unsignedInteger('related_id')->comment('關聯資料序號');
            $table->text('related_prefix')->nullable()->comment('前綴(Json)');
            $table->string('real_name', 64)->comment('真實檔案名稱');
            $table->string('path', 255)->comment('基礎檔案路徑');
            $table->string('mime_type', 255)->comment('副檔案類型');
            $table->unsignedInteger('size')->comment('檔案大小');
            $table->string('original_name', 64)->comment('顯示名稱');
            $table->string('outline', 64)->nullable()->comment('檔案簡介');
            $table->unsignedTinyInteger('sort')->default(0)->comment('檔案順序');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->nullable();
        });
        DB::statement("ALTER TABLE " . $table . " COMMENT '系統/檔案資料表'");


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('system_files');
    }
}
