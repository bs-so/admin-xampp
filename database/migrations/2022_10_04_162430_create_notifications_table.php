<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            //$table->id();
            $table->integer('id')->autoIncrement();
            $table->string('type',255)->comment('形態');
            $table->string('notifiable_type',255)->comment('通知形態');
            $table->bigInteger('notifiable_id')->comment('通知ID');
            $table->text('data')->comment('資料');
            $table->timestamp('read_at')->comment('読み込み時間')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
