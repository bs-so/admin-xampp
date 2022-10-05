<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOlcStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('olc_staff', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            //$table->id();
            $table->integer('id')->autoIncrement();
            $table->string('login_id',64)->unique()->comment('ログインID');
            $table->string('name',64)->comment('名前');
            $table->string('password',255)->comment('パスワード');
            $table->string('email',128)->comment('メール');
            $table->integer('role')->length(2)->comment('権限');
            $table->string('avatar',255)->comment('プロフィール写真')->nullable();
            $table->string('remember_token',255)->comment('トークン')->nullable();
            $table->string('lang',5)->comment('言語');
            $table->integer('status')->length(2)->comment('状態');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            //timestampの別の書き方。どちらも同じ結果
            //$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            //$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            //これも同じ結果
            //$table->timestamp('updated_at');

            //※integerの桁数を指定しても「11」になる
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('olc_staff');
    }
}
