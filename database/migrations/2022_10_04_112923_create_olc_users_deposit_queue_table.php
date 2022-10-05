<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOlcUsersDepositQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('olc_users_deposit_queue', function (Blueprint $table) {
            //$table->id();
            $table->integer('id')->autoIncrement();
            $table->integer('user_id')->comment('ユーザーID');
            $table->integer('requested')->comment('要請数');
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
        Schema::dropIfExists('olc_users_deposit_queue');
    }
}
