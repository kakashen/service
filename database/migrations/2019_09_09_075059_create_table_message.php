<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMessage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('from');
            $table->integer('to');
            $table->text('content');
            $table->tinyInteger('direction')->default(1)->comment('1客户发给客服, 2客服发给客户');
            $table->tinyInteger('type')->default(1)->comment('1文字, 2图片');
            $table->integer('communication_id')->comment('会话id');
            $table->tinyInteger('is_read')->default(0)->comment('0未读 1已读');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
