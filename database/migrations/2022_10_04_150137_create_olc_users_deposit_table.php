<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOlcUsersDepositTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('olc_users_deposit', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            //$table->id();
            $table->integer('id')->autoIncrement();
            $table->integer('user_id')->comment('ユーザーID');
            $table->string('currency',12)->comment('暗号通貨');
            $table->integer('type')->length(1)->comment('入金形態');
            $table->string('wallet_addr',255)->comment('ウォレットアドレス');
            $table->decimal('amount',30,18)->comment('残高');
            $table->decimal('deposit_fee',30,18)->comment('入金手数料');
            $table->decimal('transfer_fee',30,18)->comment('送金手数料');
            $table->integer('status')->length(1)->comment('状態');
            $table->decimal('gas_price',30,18)->comment('ガス価格');
            $table->decimal('gas_used',30,18)->comment('ガス使用量');
            $table->string('tx_id',255)->comment('取引ID');
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
        Schema::dropIfExists('olc_users_deposit');
    }
}
