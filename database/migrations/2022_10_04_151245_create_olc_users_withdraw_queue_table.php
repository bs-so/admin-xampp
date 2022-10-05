<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOlcUsersWithdrawQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('olc_users_withdraw_queue', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            //$table->id();
            $table->integer('id')->autoIncrement();
            $table->integer('user_id')->comment('ユーザーID');
            $table->integer('withdraw_id')->comment('出金ID');
            $table->string('currency',12)->comment('暗号通貨');
            $table->integer('cold_wallet_id')->comment('コールドウォレットID');
            $table->string('to_address',255)->comment('出金アドレス');
            $table->decimal('amount',30,18)->comment('出金金額');
            $table->string('tx_id',255)->comment('取引ID');
            $table->integer('status')->length(1)->comment('状態');
            $table->string('remark',255)->comment('備考');
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
        Schema::dropIfExists('olc_users_withdraw_queue');
    }
}
